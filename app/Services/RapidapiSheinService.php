<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RapidapiSheinService
{
    protected $apiHost;
    protected $apiKey;
    protected $translationService;

    const DEFAULT_LANGUAGE = 'en';
    const DEFAULT_COUNTRY = 'US';
    const DEFAULT_CURRENCY = 'USD';



    public function __construct(ChatGPTTranslationService $translationService)
    {
        $this->apiHost = config('services.rapidapi_shein.host');
        $this->apiKey = config('services.rapidapi_shein.key');
        $this->translationService = $translationService;
    }

    private function request($endpoint, $params = [])
    {
        $response = Http::withHeaders([
            'x-rapidapi-host' => $this->apiHost,
            'x-rapidapi-key' => $this->apiKey,
        ])
            ->timeout(60)
            ->retry(3, 100) // Retry 3 times with a 100ms delay between retries
            ->get("https://{$this->apiHost}/$endpoint", $params);
        return $response->json();
    }

    public function getTabs($country = self::DEFAULT_COUNTRY)
    {
        return $this->request('navigations/get-tabs', ['country' => $country]);
    }

    public function getRoot($country = self::DEFAULT_COUNTRY, $channelType = 10)
    {
        return $this->request('navigations/get-root', [
            'country' => $country,
            'channelType' => $channelType,
        ]);
    }

    public function getNodeContent($catId, $id, $country = self::DEFAULT_COUNTRY, $currency = self::DEFAULT_CURRENCY)
    {
        return $this->request('navigations/get-node-content', [
            'language' => self::DEFAULT_LANGUAGE,
            'country'  => $country,
            'currency' => $currency,
            'cat_id'   => $catId,
            'id'       => $id,
        ]);
    }

    public function getProducts($catId, $adp, $country = self::DEFAULT_COUNTRY, $currency = self::DEFAULT_CURRENCY, $lang = self::DEFAULT_LANGUAGE, $limit = 19, $page = 1, $sort = null)
    {
        return $this->request('products/list', [
            'language' => $lang,
            'country'  => $country,
            'cat_id'   => $catId,
            'currency' => $currency,
            'adp'      => $adp,
            'sort'     => $sort,
            'limit'    => $limit,
            'page'     => $page,
        ]);
    }

    public function getProductsWitPagination($catId, $adp, $country = self::DEFAULT_COUNTRY, $currency = self::DEFAULT_CURRENCY, $lang = self::DEFAULT_LANGUAGE, $limit = 19, $sort = null)
    {
        $page = 1;
        $allProducts = [];
        $totalProducts = null;

        do {
            $response = $this->request('products/list', [
                'language' => $lang,
                'country'  => $country,
                'cat_id'   => $catId,
                'currency' => $currency,
                'adp'      => $adp,
                'sort'     => $sort,
                'limit'    => 19,
                'page'     => $page,
            ]);
            if (isset($response['code']) && $response['code'] == 0) {
                $data = $response['info'];
                $products = $data['products'];
                if (count($products) > 0) {
                    foreach ($products as $key => $product) {
                        $productTitleAndDescription = $this->extractProductDetails($product);
                        $productModel = Product::updateOrCreate(
                            [
                                'external_id' =>  $product['goods_id']
                            ],
                            [
                                'external_id' =>  $product['goods_id'] ?? null,
                                'normal_en_name' =>  $product['goods_name'] ?? null,
                                'en_name' =>  $productTitleAndDescription['title'] ?? null,
                                'ar_name' => $productTitleAndDescription['title'] ? translateToArabic($productTitleAndDescription['title'], $this->translationService) : null,
                                'slug' =>  $product['goods_url_name'] ?? null,
                                'external_sku' =>  $product['goods_sn'] ?? null,
                                'price' =>  $product['retailPrice']['amount'] ?? 0,
                                'primary_image' => $product['goods_img'] ?? null,
                                'is_lowest_price' => $product['is_lowest_price'] ?? 0,
                                'is_highest_sales' => $product['is_highest_sales'] ?? 0,
                                'video_url' => $product['video_url'] ?? null,
                                'mall_code' => $product['mall_code'] ?? null,
                                'currency' =>  self::DEFAULT_CURRENCY,
                                'en_description' =>  $productTitleAndDescription['description'] ?? null,
                                'ar_description' => $productTitleAndDescription['description'] ? translateToArabic($productTitleAndDescription['description'], $this->translationService) : null,
                                'brand_id' => $this->checkBrandAndCreateIfNotExists($product['premiumFlagNew']),
                                'category_id' => $this->checkCategoryAndCreateIfNotExists($product),
                                'store' => 'Shein',
                                'creation_date'  => Carbon::now()->format('Y-m-d'),
                                'images' => $product['detail_image'] ?? [],
                                'parent_categories' => $product['parentIds'] ?? [],
                                'rapidapi_cat_id' => $catId,
                                'rapidapi_adp' => $adp,
                                'rapidapi_country' => $country
                            ]
                        );
                        if (!$productModel->details()->exists()) {
                            $productModel->details()->create([
                                'product_relation_id' => $product['productRelationID'] ?? null,
                                'product_material' => $product['productMaterial'] ?? [],
                                'retail_price' => $product['retailPrice']['amount'] ?? 0,
                                'sale_price' => $product['salePrice']['amount'] ?? 0,
                                'discount_price' => $product['discountPrice']['amount'] ?? 0,
                                'retail_discount_price' => $product['retailDiscountPrice']['amount'] ?? 0,
                                'unit_discount' => $product['unit_discount'] ?? null,
                                'srp_discount' => $product['srpDiscount'] ?? null,
                                'promotion_info' => $product['promotionInfo'] ?? [],
                                'stock' => $product['stock'] ?? 0,
                                'sold_out_status' => $product['soldOutStatus'] ?? false,
                                'is_on_sale' => $product['is_on_sale'] ?? 0,
                                'related_color_new' => $product['relatedColorNew'] ?? [],
                                'feature_subscript' => $product['featureSubscript'] ?? [],
                                'coupon_prices' => $product['coupon_prices'] ?? []
                            ]);
                        }
                    }
                }
                $page++;
            }
        } while (count($allProducts) < $totalProducts);

        return $allProducts;
    }

    function extractProductDetails(array $product): array
    {
        try {
            $fullName = $product['goods_name'] ?? '';
            $fullNameTrimmed = trim($fullName);

            // Check if it ends with one dot or has no dot at all
            $dotCount = substr_count($fullNameTrimmed, '.');

            if (($dotCount === 1 && str_ends_with($fullNameTrimmed, '.')) || $dotCount === 0) {
                // Use original name as base description
                $base = $fullNameTrimmed;
            } else {
                // Clean the name: remove brand words
                $cleaned = preg_replace('/^(SHEIN\s)?(Manfinity\s)?(EMRG\s)?(Men\'s\s)?/i', '', $fullNameTrimmed);

                // Short name = first phrase
                $shortName = strtok($cleaned, ',');

                // Description = everything else
                $description = trim(str_replace($shortName, '', $cleaned), " ,.");

                // Build cleaned description
                $base = ucfirst($description);
                if (!str_ends_with($base, '.')) {
                    $base .= '.';
                }
            }

            // Extra info to append
            $extra = [];
            $extra[] = "Category: " . ($product['cate_name'] ?? 'N/A');
            $extra[] = "Rating: " . ($product['comment_rank_average'] ?? 'N/A') . " / 5 from " . ($product['comment_num'] ?? '0') . " reviews";

            return [
                'title' => ucfirst(trim(strtok($fullNameTrimmed, ','))), // Keep short name
                'description' => $base . ' ' . implode('. ', $extra) . '.',
            ];
        } catch (\Throwable $th) {
            return [
                'title' => $product['goods_name'] ?? null,
                'description' => $product['goods_name'] ?? null,
            ];
        }
    }

    function getProductsParallel($catId, $adp, $country = self::DEFAULT_COUNTRY, $currency = self::DEFAULT_CURRENCY, $lang = self::DEFAULT_LANGUAGE, $limit = 19, $page = 1, $sort = null)
    {
        $client = new Client();

        // Prepare the requests for both English and Arabic languages
        // $languages = ['en', 'ar'];  // Add other languages if needed
        $languages = ['en'];  // Add other languages if needed
        $requests = [];

        foreach ($languages as $lang) {
            $country = $lang === 'ar' ? 'AE' : 'US'; // Adjust country based on language

            // Prepare the request URL for each language
            $params = [
                'language' => $lang,
                'country'  => $country,
                'cat_id'   => $catId,
                'currency' => $currency,
                'adp'      => $adp,
                'sort'     => $sort,
                'limit'    => $limit,
                'page'     => $page,
            ];

            $requestUrl = "https://{$this->apiHost}/products/list?" . http_build_query($params);

            $requests[] = new Request('GET', $requestUrl, [
                'X-RapidAPI-Host' => $this->apiHost,
                'X-RapidAPI-Key'  => $this->apiKey
            ]);
        }

        $successfulResponses = [];
        $failedRequests = [];

        // Pool of requests for multiple languages
        $pool = new Pool($client, $requests, [
            'concurrency' => 5, // Adjust concurrency as needed
            'fulfilled' => function ($response, $index) use ($requests, &$successfulResponses) {
                $data = json_decode($response->getBody(), true);
                $requestUrl = (string) $requests[$index]->getUri();
                $successfulResponses[] = ['url' => $requestUrl, 'data' => $data]; // Store success data
                if (count($data['info']['products']) == 0)
                    Log::alert("❌ Failed - no products: " . $requestUrl . PHP_EOL);
            },
            'rejected' => function (RequestException $reason, $index) use ($requests, &$failedRequests) {
                $requestUrl = (string) $requests[$index]->getUri();
                Log::alert("❌ Failed: " . $requestUrl . PHP_EOL);
                $failedRequests[] = ['url' => $requestUrl, 'error' => $reason->getMessage()]; // Store failed request and error message
            }
        ]);

        // Wait for all requests to complete
        $promise = $pool->promise();
        $promise->wait();

        // Return both successful and failed requests
        return [
            'success' => $successfulResponses,
            'failures' => $failedRequests,
        ];
    }

    public function fetchProducts($country = self::DEFAULT_COUNTRY, $currency = self::DEFAULT_CURRENCY)
    {
        try {
            $tabs = $this->getTabs($country);
            if ($tabs['code'] != 0) {
                return ['error' => 'Failed to fetch tabs'];
            } else {
                foreach ($tabs['info']['tabs'] as $key => $tab) {
                    if(!$tab['isAllTab']){
                        $tabChannelType = $tab['id'];
                        usleep(200000);
                        $root = $this->getRoot($country, $tabChannelType);
                        if ($root['code'] == 0) {
                            // Step 2: Get Root id
                            $tabCatId = $tab['cat_id'];
                            foreach ($root['info']['content'] as $key => $rootItem) {
                                $rootId = $rootItem['id'];
                                // Step 3: Get Node Content
                                usleep(200000);
                                $nodeContent = $this->getNodeContent($tabCatId, $rootId, $country, $currency);
                                if ($nodeContent['code'] == 0 && isset($nodeContent['info']['content'])) {
                                    foreach ($nodeContent['info']['content'] as $key => $nodeItem) {
                                        if (isset($nodeItem['thumb'])) {
                                            foreach ($nodeItem['thumb'] as $key => $thumbItem) {
                                                if (isset($thumbItem['hrefTarget']) && $thumbItem['hrefTarget'] && isset($thumbItem['goodsId']) && $thumbItem['goodsId']) {
                                                    $nodeCatId = $thumbItem['hrefTarget'];
                                                    $adp = $thumbItem['goodsId'];
                                                    usleep(200000);
                                                    $this->getProductsWitPagination($nodeCatId, $adp, $country, $currency);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return ['success' => true];
        } catch (\Throwable $th) {
            Log::alert($th->getMessage());
        }
    }

    function checkBrandAndCreateIfNotExists($brandObject)
    {
        $logos = [];
        if (isset($brandObject['series_logo_url_left']))
            $logos[] = $brandObject['series_logo_url_left'];
        if (isset($brandObject['series_logo_url_right']))
            $logos[] = $brandObject['series_logo_url_right'];

        if (isset($brandObject['brandId']) && isset($brandObject['brandName'])) {
            $brand = ProductBrand::updateOrCreate(['external_id' => $brandObject['brandId']], [
                'external_id' => $brandObject['brandId'] ?? null,
                'brand_name_en' => $brandObject['brandName'] ?? null,
                'brand_name_ar' => $brandObject['brandName'] ? translateToArabic($brandObject['brandName'], $this->translationService) : null,
                'brand_badge_name_en' => $brandObject['brand_badge_name'] ?? null,
                'brand_badge_name_ar' => $brandObject['brand_badge_name'] ? translateToArabic($brandObject['brand_badge_name'], $this->translationService) : null,
                'brand_code' => $brandObject['brand_code'] ?? null,
                'series_badge_name_en' => $brandObject['series_badge_name'] ?? null,
                'series_id' => $brandObject['seriesId'] ?? null,
                'series_logo' => $logos ?? []
            ]);
            return $brand->id;
        }
        return null;
    }

    function checkCategoryAndCreateIfNotExists($productObject)
    {
        $category = ProductCategory::updateOrCreate([
            'external_id' => $productObject['cat_id']
        ], [
            'external_id' => $productObject['cat_id'],
            'name_en' => $productObject['cate_name'],
            'name_ar' => translateToArabic($productObject['cate_name'], $this->translationService)
        ]);
        return $category->external_id;
    }
}
