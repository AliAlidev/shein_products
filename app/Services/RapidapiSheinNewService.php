<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\SheinNode;
use App\Models\SheinRootNode;
use App\Models\SheinRootNodeContent;
use App\Models\SheinTab;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RapidapiSheinNewService
{

    private $apiHost;
    private $apiKey;
    protected $translationService;

    public function __construct(ChatGPTTranslationService $translationService)
    {
        $this->apiHost = config('services.rapidapi_shein.host');
        $this->apiKey = config('services.rapidapi_shein.key');
        $this->translationService = $translationService;
    }

    function getTabs()
    {
        $result = $this->callApi('navigations/get-tabs', ['platform' => '1', 'country' => 'US']);
        return $result['code'] == 0 ? $result['info']['tabs'] ?? [] : [];
    }

    public function getRoot($country = 'US', $channelType = 1)
    {
        $result = $this->callApi('navigations/get-root', [
            'country' => $country,
            'channelType' => $channelType,
        ]);
        return $result['code'] == 0 ? $result['info']['content'] ?? [] : [];
    }

    public function getNodeContent($catId, $id)
    {
        $result = $this->callApi('navigations/get-node-content', [
            'language' => 'en',
            'country'  => 'US',
            'currency' => 'USD',
            'cat_id'   => $catId,
            'id'       => $id,
        ]);
        return $result['code'] == 0 ? $result['info']['content'] ?? [] : [];
    }

    public function fetchAndStoreNodes()
    {
        //// all options - 'Women', 'Men', 'Kids', 'Beauty', 'Curve', 'Home', 'All'
        $includedTabs = ['Men'];
        $tabs = $this->getTabs();
        foreach ($tabs as $tab) {
            $channel = $tab['channelName'] ?? null;
            $catIdString = $tab['cat_id'] ?? '';
            if (!$channel || !$catIdString) {
                continue;
            }
            if (!in_array($channel, $includedTabs)) {
                continue;
            }
            $catIds = explode(',', $catIdString);
            $roots = $this->getRoot('US', $tab['id']);
            foreach ($roots as $root) {
                $rootName = $root['name'] ?? '';
                $rootId = $root['id'] ?? null;
                if (!$rootId) {
                    continue;
                }
                foreach ($catIds as $catId) {
                    $nodes = $this->getNodeContent($catId, $rootId);
                    $this->processNodes($nodes, $channel, $rootName);
                }
            }
        }
    }

    private function processNodes($nodes, $channel, $rootName)
    {
        foreach ($nodes as $node) {
            if (isset($node['thumb'])) {
                foreach ($node['thumb'] as $key => $thumbItem) {
                    if (isset($thumbItem['hrefTarget']) && isset($thumbItem['goodsId']) && $thumbItem['hrefTarget'] && $thumbItem['goodsId']) {
                        $hrefTarget = $thumbItem['hrefTarget'];
                        $goodsId = $thumbItem['goodsId'];
                        SheinNode::updateOrCreate(
                            [
                                'href_target' => $hrefTarget,
                                'goods_id' => $goodsId
                            ],
                            [
                                'channel' => $channel,
                                'root_name' => $rootName,
                                'node_name' => $node['name'] ?? '',
                                'nav_node_id' => $node['navNodeId'] ?? null,
                                'cate_tree_node_id' => $node['cateTreeNodeId'] ?? null,
                                'href_type' => $node['hrefType'] ?? null,
                                'href_target' => $hrefTarget,
                                'goods_id' => $goodsId ?? null,
                                'image_url' => $node['target'] ?? null,
                            ]
                        );
                    }
                }
            }
        }
    }

    function getProducts($catId, $adp, $page)
    {
        $result = $this->callApi('products/list', [
            'language' => 'en',
            'country'  => 'US',
            'cat_id'   => $catId,
            'currency' => 'USD',
            'adp'      => $adp,
            'sort'     => null,
            'limit'    => 19,
            'page'     => $page,
        ]);
        $final = [
            'products' => [],
            'total' => 0,
            'current_count' => 0
        ];
        if ($result['code'] == 0) {
            $final = [
                'products' => $result['info']['products'] ?? [],
                'total' => $result['info']['num'] ?? 0,
                'current_count' => count($result['info']['products'] ?? [])
            ];
        }
        return $final;
    }

    public function insertProductsWitPagination($catId, $adp, $nodeId)
    {
        $page = 1;
        $totalProducts = 0;

        do {
            $products = $this->getProducts($catId, $adp, $page);
            if ($products['current_count'] > 0) {
                foreach ($products['products'] as $key => $product) {
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
                            'currency' =>  'USD',
                            'en_description' =>  $productTitleAndDescription['description'] ?? null,
                            'ar_description' => $productTitleAndDescription['description'] ? translateToArabic($productTitleAndDescription['description'], $this->translationService) : null,
                            'brand_id' => $this->checkBrandAndCreateIfNotExists($product['premiumFlagNew']),
                            'category_id' => $this->checkCategoryAndCreateIfNotExists($product),
                            'store' => 'Shein',
                            'creation_date'  => Carbon::now()->format('Y-m-d'),
                            'images' => $product['detail_image'] ?? [],
                            'parent_categories' => $product['parentIds'] ?? [],
                            'node_id' => $nodeId
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
                    $totalProducts++;
                }
            }
            $page++;
        } while ($totalProducts < $products['total']);
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

    private function callApi($endpoint, $params)
    {
        try {
            $response = Http::withHeaders([
                'x-rapidapi-host' => $this->apiHost,
                'x-rapidapi-key' => $this->apiKey,
            ])
                ->timeout(60)
                ->retry(3, 100)
                ->get("https://{$this->apiHost}/$endpoint", $params);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error("Shein API error ($endpoint): " . $e->getMessage());
            return [];
        }
    }
}
