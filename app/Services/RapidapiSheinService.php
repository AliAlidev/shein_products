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

    const DEFAULT_LANGUAGE = 'en';
    const DEFAULT_COUNTRY = 'US';
    const DEFAULT_CURRENCY = 'USD';

    public function __construct()
    {
        $this->apiHost = config('services.rapidapi_shein.host');
        $this->apiKey = config('services.rapidapi_shein.key');
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

    public function getProducts($catId, $adp, $country = self::DEFAULT_COUNTRY, $currency = self::DEFAULT_CURRENCY, $lang = self::DEFAULT_LANGUAGE, $limit = 20, $page = 1, $sort = null)
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

    function getProductsParallel($catId, $adp, $country = self::DEFAULT_COUNTRY, $currency = self::DEFAULT_CURRENCY, $lang = self::DEFAULT_LANGUAGE, $limit = 20, $page = 1, $sort = null)
    {
        $client = new Client();

        // Prepare the requests for both English and Arabic languages
        $languages = ['en', 'ar'];  // Add other languages if needed
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

    public function getProductsParallelDeprecated($catId, $adp, $country = self::DEFAULT_COUNTRY, $currency = self::DEFAULT_CURRENCY, $limit = 20, $page = 1, $sort = null)
    {
        $languages = ['en', 'ar'];

        $responses = Http::pool(
            fn($pool) =>
            array_map(
                fn($lang) =>
                $pool->withHeaders([
                    'x-rapidapi-host' => $this->apiHost,
                    'x-rapidapi-key' => $this->apiKey
                ])->get("https://unofficial-shein.p.rapidapi.com/products/list", [
                    'language' => $lang,
                    'country'  => $lang === 'ar' ? 'AE' : $country,
                    'cat_id'   => $catId,
                    'currency' => $currency,
                    'adp'      => $adp,
                    'limit'    => $limit,
                    'page'     => $page,
                    'sort'     => $sort,
                ]),
                $languages
            )
        );

        $result = [];
        $failedRequests = [];

        foreach ($languages as $index => $lang) {
            if ($responses[$index]->successful()) {
                $result[$lang] = $responses[$index]->json();
                if (count($result[$lang]['info']['products']) == 0)
                    dd($result[$lang]);
            } else {
                $failedRequests[$lang] = [
                    'status'  => $responses[$index]->status(),
                    'error'   => $responses[$index]->body(),
                ];
                Log::error("Failed request for language: {$lang}", [
                    'status' => $responses[$index]->status(),
                    'response' => $responses[$index]->body(),
                ]);
            }
        }
        Log::alert($result);
        return [
            'success' => $result,
            'failed'  => $failedRequests,
        ];
    }

    public function fetchProducts($country = self::DEFAULT_COUNTRY, $currency = self::DEFAULT_CURRENCY)
    {
        $productsCount = 0;
        // Step 1: Get Tabs
        $tabs = $this->getTabs($country);
        if ($tabs['code'] != 0) {
            return ['error' => 'Failed to fetch tabs'];
        } else {
            foreach ($tabs['info']['tabs'] as $key => $tab) {
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
                                            $products = $this->getProductsParallel($nodeCatId, $adp, $country, $currency);
                                            $arProducts = $products['success'][0]['data'];
                                            $enProducts = $products['success'][1]['data'];

                                            if ($enProducts['code'] == 0) {
                                                if (count($enProducts['info']['products']) > 0) {
                                                    foreach ($enProducts['info']['products'] as $key => $product) {
                                                        $productModel = Product::updateOrCreate(
                                                            [
                                                                'external_id' =>  $product['goods_id']
                                                            ],
                                                            [
                                                                'external_id' =>  $product['goods_id'] ?? null,
                                                                'en_name' =>  $product['goods_name'] ?? null,
                                                                'slug' =>  $product['goods_url_name'] ?? null,
                                                                'external_sku' =>  $product['goods_sn'] ?? null,
                                                                'price' =>  $product['retailPrice']['amount'] ?? 0,
                                                                'primary_image' => $product['goods_img'] ?? null,
                                                                'currency' =>  self::DEFAULT_CURRENCY,
                                                                'en_description' =>  $product['goods_name'] ?? null,
                                                                'brand_id' => $this->checkBrandAndCreateIfNotExists($product['premiumFlagNew']),
                                                                'category_id' => $this->checkCategoryAndCreateIfNotExists($product),
                                                                'store' => 'Shein',
                                                                'creation_date'  => Carbon::now()->format('Y-m-d'),
                                                                'images' => $product['detail_image'] ?? []
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
                                                                'featureSubscript' => $product['featureSubscript'] ?? []
                                                            ]);
                                                        }
                                                        $productsCount++;
                                                        Log::alert($productsCount);
                                                    }
                                                }
                                            }

                                            if ($arProducts['code'] == 0) {
                                                foreach ($arProducts['info']['products'] as $key => $product) {
                                                    Product::updateOrCreate(
                                                        [
                                                            'external_id' =>  $product['goods_id']
                                                        ],
                                                        [
                                                            'ar_name' =>  $product['goods_name'] ?? null,
                                                            'ar_description' =>  $product['goods_name'] ?? null,
                                                            'brand_id' => $this->checkBrandAndCreateIfNotExists($product['premiumFlagNew'], 'ar'),
                                                            'category_id' => $this->checkCategoryAndCreateIfNotExists($product, 'ar')
                                                        ]
                                                    );
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
        }
        dd("done");
    }

    function checkBrandAndCreateIfNotExists($brandObject, $lang = 'en')
    {
        $logos = [];
        if (isset($brandObject['series_logo_url_left']))
            $logos[] = $brandObject['series_logo_url_left'];
        if (isset($brandObject['series_logo_url_right']))
            $logos[] = $brandObject['series_logo_url_right'];

        if (isset($brandObject['brandId'])) {
            $brand = ProductBrand::updateOrCreate(['external_id' => $brandObject['brandId']], [
                'external_id' => $brandObject['brandId'] ?? null,
                'brand_name_' . $lang => $brandObject['brandName'] ?? null,
                'brand_badge_name_' . $lang => $brandObject['brand_badge_name'] ?? null,
                'brand_code' => $brandObject['brand_code'] ?? null,
                'series_badge_name_' . $lang => $brandObject['series_badge_name'] ?? null,
                'series_id' => $brandObject['seriesId'] ?? null,
                'series_logo' => $logos ?? []
            ]);
            return $brand->id;
        }
        return null;
    }

    function checkCategoryAndCreateIfNotExists($productObject, $lang = 'en')
    {
        $category = ProductCategory::updateOrCreate([
            'external_id' => $productObject['cat_id']
        ], [
            'external_id' => $productObject['cat_id'],
            'name_' . $lang => $productObject['cate_name']
        ]);
        return $category->id;
    }
}
