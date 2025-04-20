<?php

namespace App\Services;

use App\Models\FetchIngProductTrackers;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\RapidApiSheinRequestTrackers;
use App\Models\SheinNode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class RapidapiSheinNewService
{

    private $apiHost;
    private $apiKey;
    private $maxAllowedPages = 1;
    private $productsTrackerFile;

    public function __construct()
    {
        $this->apiHost = config('services.rapidapi_shein.host');
        $this->apiKey = config('services.rapidapi_shein.key');

        // $jsonPath = storage_path('app/product_trackers.json');
        // $jsonString = file_get_contents($jsonPath);
        // $this->productsTrackerFile = json_decode($jsonString, true);
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

    public function fetchAndStoreNodesDeprecated($includedTabs = [])
    {
        //// all options - 'Women', 'Men', 'Kids', 'Beauty', 'Curve', 'Home', 'All'
        // $includedTabs = ['Men'];
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

    function getStaticTabs()
    {
        return [
            [
                "id" => "2",
                "name" => "Women",
                "channelName" => "Women",
                "cat_id" => "4436,2041,2042,3644,7469,4816,4815,4813,4811,4808,4806,4804,3863,3694,3686,2475,4451,4801,4802,2031,2032,4314,4295,4293,4292,3812,3627,3626,3625,2347,2346,2208,2273,2297,2400,1894,2491,4329,4331,4335,4350,4366,4379,4396,4409,4413,4422,4424,5106,5247,5290,5301,5348,5796,5797,6376,7152,3224,1765,5806,5826,5906,5907,6219,6217,4656,4438,4219,3632,3035,3029,1745,4803,5928,2043,4099,4258,4268,4458,3650,3657,4083,4327,4328,2462,2463,2464,2465",
                "tspNodeIds" => null,
                "abt_pos" => null,
                "crowdId" => "0",
                "is_default" => "0",
                "recommendAbtPos" => null,
                "isAllTab" => "0",
                "isNew" => "0",
                "tabData" => null,
                "newTabData" => null
            ],
            [
                "id" => "4",
                "name" => "Kids",
                "channelName" => "Kids",
                "cat_id" => "2031,3224,4437,3624,2378,4299,2986,4291,2379,2380",
                "tspNodeIds" => null,
                "abt_pos" => null,
                "crowdId" => "0",
                "is_default" => "0",
                "recommendAbtPos" => null,
                "isAllTab" => "0",
                "isNew" => "0",
                "tabData" => null,
                "newTabData" => null
            ],
            [
                "id" => "3",
                "name" => "Men",
                "channelName" => "Men",
                "cat_id" => "2026,2089,2090,2443,2027,4323,4318,4307,4294,1972,3792",
                "tspNodeIds" => null,
                "abt_pos" => null,
                "crowdId" => "0",
                "is_default" => "0",
                "recommendAbtPos" => null,
                "isAllTab" => "0",
                "isNew" => "0",
                "tabData" => null,
                "newTabData" => null
            ],
            [
                "id" => "6",
                "name" => "Curve",
                "channelName" => "PlusSize",
                "cat_id" => "1888,3734,2346,2347,2491,3613",
                "tspNodeIds" => null,
                "abt_pos" => null,
                "crowdId" => "0",
                "is_default" => "0",
                "recommendAbtPos" => null,
                "isAllTab" => "0",
                "isNew" => "0",
                "tabData" => null,
                "newTabData" => null
            ],
            [
                "id" => "9",
                "name" => "Beauty",
                "channelName" => "Beauty",
                "cat_id" => "1864",
                "tspNodeIds" => null,
                "abt_pos" => null,
                "crowdId" => "0",
                "is_default" => "0",
                "recommendAbtPos" => null,
                "isAllTab" => "0",
                "isNew" => "0",
                "tabData" => null,
                "newTabData" => null
            ]
        ];
    }

    function getStaticMenSectionTypes()
    {
        return [
            [
                "color" => "",
                "level" => "1",
                "name" => "New In",
                "id" => "33436800001",
                "type" => "1",
                "navNodeId" => "33436800001",
                "enName" => "newin",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Trends",
                "id" => "33436800037",
                "type" => "1",
                "navNodeId" => "33436800037",
                "enName" => "winter",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Fall & Winter",
                "id" => "33436800073",
                "type" => "1",
                "navNodeId" => "33436800073",
                "enName" => "FallWinter",
                "is_recommend" => "0",
                "relativeUrl" => "/trends/Fall-and-Winter-sc-006175271.html",
                "selectTypeId" => "8",
                "hrefType" => "itemPicking",
                "hrefTarget" => "006175271",
                "cateTreeNodeId" => "2724496",
                "autoRecommend" => [
                    "includeIds" => [
                        "2724496"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Sale",
                "id" => "33436800119",
                "type" => "1",
                "navNodeId" => "33436800119",
                "enName" => "sale",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Clothing",
                "id" => "33436800160",
                "type" => "1",
                "navNodeId" => "33436800160",
                "enName" => "clothing",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Tops",
                "id" => "33436800213",
                "type" => "1",
                "navNodeId" => "33436800213",
                "enName" => "tops",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Bottoms",
                "id" => "33436800252",
                "type" => "1",
                "navNodeId" => "33436800252",
                "enName" => "bottoms",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Extended Sizes",
                "id" => "33436800279",
                "type" => "1",
                "navNodeId" => "33436800279",
                "enName" => "EXTENDEDSIZES",
                "is_recommend" => "0",
                "relativeUrl" => "/Men-Plus-Size-Clothing-c-6279.html",
                "selectTypeId" => "8",
                "hrefType" => "real",
                "hrefTarget" => "6279",
                "cateTreeNodeId" => "2721172",
                "autoRecommend" => [
                    "includeIds" => [
                        "2721172"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Swimwear",
                "id" => "33436800324",
                "type" => "1",
                "navNodeId" => "33436800324",
                "enName" => "Swimwear",
                "is_recommend" => "0",
                "relativeUrl" => "/recommend/SWIMWEAR-sc-100157289.html",
                "selectTypeId" => "21",
                "hrefType" => "itemPicking",
                "hrefTarget" => "100157289",
                "cateTreeNodeId" => "2710777",
                "autoRecommend" => [
                    "includeIds" => [
                        "2710777"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Sports & Outdoor",
                "id" => "33436800345",
                "type" => "1",
                "navNodeId" => "33436800345",
                "enName" => "activewear",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Shoes & Accs",
                "id" => "33436800360",
                "type" => "1",
                "navNodeId" => "33436800360",
                "enName" => "shoesaccs",
                "is_recommend" => "0"
            ]
        ];
    }

    function getStaticWomenSectionTypes()
    {
        return [
            [
                "color" => "",
                "level" => "1",
                "name" => "New In",
                "id" => "33530300001",
                "type" => "1",
                "navNodeId" => "33530300001",
                "enName" => "new",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "#sheinfw23",
                "id" => "33530300068",
                "type" => "1",
                "navNodeId" => "33530300068",
                "enName" => "trends",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Sale",
                "id" => "33530300115",
                "type" => "1",
                "navNodeId" => "33530300115",
                "enName" => "sale",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Clothing",
                "id" => "33530300158",
                "type" => "1",
                "navNodeId" => "33530300158",
                "enName" => "Clothing",
                "is_recommend" => "0",
                "relativeUrl" => "/style/Women-Clothing-sc-001121425.html",
                "selectTypeId" => "1",
                "hrefType" => "itemPicking",
                "hrefTarget" => "001121425",
                "cateTreeNodeId" => "2700814",
                "autoRecommend" => [
                    "includeIds" => [
                        "2700814"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Shein X Designers",
                "id" => "33530300280",
                "type" => "1",
                "navNodeId" => "33530300280",
                "enName" => "SHEINX",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Dresses",
                "id" => "33530300331",
                "type" => "1",
                "navNodeId" => "33530300331",
                "enName" => "Dresses",
                "is_recommend" => "0",
                "relativeUrl" => "/style/Dresses-sc-001148338.html",
                "selectTypeId" => "1",
                "hrefType" => "itemPicking",
                "hrefTarget" => "001148338",
                "cateTreeNodeId" => "2700824",
                "autoRecommend" => [
                    "includeIds" => [
                        "2700824"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Beachwear",
                "id" => "33530300376",
                "type" => "1",
                "navNodeId" => "33530300376",
                "enName" => "Beachwear",
                "is_recommend" => "0",
                "relativeUrl" => "/Women-Beachwear-c-2039.html",
                "selectTypeId" => "",
                "hrefType" => "real",
                "hrefTarget" => "2039",
                "cateTreeNodeId" => "2700960",
                "autoRecommend" => [
                    "includeIds" => [
                        "2700960"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Lingerie & Lounge",
                "id" => "33530300429",
                "type" => "1",
                "navNodeId" => "33530300429",
                "enName" => "LingerieLoungewear",
                "is_recommend" => "0",
                "relativeUrl" => "/recommend/Women-Lingerie-and-Sleepwear-sc-100116093.html",
                "selectTypeId" => "21",
                "hrefType" => "itemPicking",
                "hrefTarget" => "100116093",
                "cateTreeNodeId" => "2700929",
                "autoRecommend" => [
                    "includeIds" => [
                        "2700929"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Denim & Jeans",
                "id" => "33530300501",
                "type" => "1",
                "navNodeId" => "33530300501",
                "enName" => "Denim",
                "is_recommend" => "0",
                "relativeUrl" => "/Women-Denim-c-1930.html",
                "selectTypeId" => "",
                "hrefType" => "real",
                "hrefTarget" => "1930",
                "cateTreeNodeId" => "2700844",
                "autoRecommend" => [
                    "includeIds" => [
                        "2700844"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Sports & Outdoors",
                "id" => "33530300549",
                "type" => "1",
                "navNodeId" => "33530300549",
                "enName" => "Activewear",
                "is_recommend" => "0",
                "relativeUrl" => "/recommend/Sports-Activewear-sc-100155345.html",
                "selectTypeId" => "21",
                "hrefType" => "itemPicking",
                "hrefTarget" => "100155345",
                "cateTreeNodeId" => "2701018",
                "autoRecommend" => [
                    "includeIds" => [
                        "2701018"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Shoes",
                "id" => "33530300589",
                "type" => "1",
                "navNodeId" => "33530300589",
                "enName" => "Shoes",
                "is_recommend" => "0",
                "relativeUrl" => "/Women-Shoes-c-1745.html",
                "selectTypeId" => "",
                "hrefType" => "real",
                "hrefTarget" => "1745",
                "cateTreeNodeId" => "2700981",
                "autoRecommend" => [
                    "includeIds" => [
                        "2700981"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Brands",
                "id" => "33530301080",
                "type" => "1",
                "navNodeId" => "33530301080",
                "enName" => "BRANDS",
                "is_recommend" => "0",
                "relativeUrl" => "",
                "selectTypeId" => "",
                "hrefType" => "noJump",
                "hrefTarget" => "",
                "cateTreeNodeId" => "2715724",
                "autoRecommend" => [
                    "includeIds" => [
                        "2715724"
                    ]
                ]
            ]
        ];
    }

    function getStaticKidsSectionTypes()
    {
        return [
            [
                "color" => "",
                "level" => "1",
                "name" => "New In",
                "id" => "33484800001",
                "type" => "1",
                "navNodeId" => "33484800001",
                "enName" => "KIDS",
                "is_recommend" => "0",
                "relativeUrl" => "/new/New-in-Kids-sc-00212554.html",
                "selectTypeId" => "2",
                "hrefType" => "itemPicking",
                "hrefTarget" => "00212554",
                "cateTreeNodeId" => "2707715",
                "autoRecommend" => [
                    "includeIds" => [
                        "2707715"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Trends",
                "id" => "33484800022",
                "type" => "1",
                "navNodeId" => "33484800022",
                "enName" => "TRENDS",
                "is_recommend" => "0",
                "relativeUrl" => "/recommend/TRENDS-sc-100172837.html",
                "selectTypeId" => "21",
                "hrefType" => "itemPicking",
                "hrefTarget" => "100172837",
                "cateTreeNodeId" => "2701540",
                "autoRecommend" => [
                    "includeIds" => [
                        "2701540"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Fall & Winter",
                "id" => "33484800055",
                "type" => "1",
                "navNodeId" => "33484800055",
                "enName" => "FallWinter",
                "is_recommend" => "0",
                "relativeUrl" => "/style/Fall-And-Winter-View-All-sc-001196018.html",
                "selectTypeId" => "1",
                "hrefType" => "itemPicking",
                "hrefTarget" => "001196018",
                "cateTreeNodeId" => "2724863",
                "autoRecommend" => [
                    "includeIds" => [
                        "2724863"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Sale",
                "id" => "33484800095",
                "type" => "1",
                "navNodeId" => "33484800095",
                "enName" => "Sale",
                "is_recommend" => "0",
                "relativeUrl" => "/sale/Kids-All-Sale-sc-00510252.html",
                "selectTypeId" => "7",
                "hrefType" => "itemPicking",
                "hrefTarget" => "00510252",
                "cateTreeNodeId" => "2701507",
                "autoRecommend" => [
                    "includeIds" => [
                        "2701507"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Baby 0-3Yrs",
                "id" => "33484800151",
                "type" => "1",
                "navNodeId" => "33484800151",
                "enName" => "BABY",
                "is_recommend" => "0",
                "relativeUrl" => "/Baby-c-3224.html",
                "selectTypeId" => "",
                "hrefType" => "real",
                "hrefTarget" => "3224",
                "cateTreeNodeId" => "2701279",
                "autoRecommend" => [
                    "includeIds" => [
                        "2701279"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Young Boys 3-7Yrs",
                "id" => "33484800215",
                "type" => "1",
                "navNodeId" => "33484800215",
                "enName" => "TODDLERBOYS",
                "is_recommend" => "0",
                "relativeUrl" => "/Young-Boys-Clothing-c-2059.html",
                "selectTypeId" => "",
                "hrefType" => "real",
                "hrefTarget" => "2059",
                "cateTreeNodeId" => "2709954",
                "autoRecommend" => [
                    "includeIds" => [
                        "2709954"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Young Girls 3-7Yrs",
                "id" => "33484800257",
                "type" => "1",
                "navNodeId" => "33484800257",
                "enName" => "TODDLERGIRLS",
                "is_recommend" => "0",
                "relativeUrl" => "/Young-Girls-Clothing-c-2058.html",
                "selectTypeId" => "",
                "hrefType" => "real",
                "hrefTarget" => "2058",
                "cateTreeNodeId" => "2709897",
                "autoRecommend" => [
                    "includeIds" => [
                        "2709897"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Tween Boys 8-12Yrs",
                "id" => "33484800306",
                "type" => "1",
                "navNodeId" => "33484800306",
                "enName" => "BOYSCLOTHING",
                "is_recommend" => "0",
                "relativeUrl" => "/Tween-Boys-Clothing-c-1990.html",
                "selectTypeId" => "",
                "hrefType" => "real",
                "hrefTarget" => "1990",
                "cateTreeNodeId" => "2709859",
                "autoRecommend" => [
                    "includeIds" => [
                        "2709859"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Tween Girls 8-12Yrs",
                "id" => "33484800347",
                "type" => "1",
                "navNodeId" => "33484800347",
                "enName" => "GirlsClothing",
                "is_recommend" => "0",
                "relativeUrl" => "/Tween-Girls-Clothing-c-1991.html",
                "selectTypeId" => "",
                "hrefType" => "real",
                "hrefTarget" => "1991",
                "cateTreeNodeId" => "2709796",
                "autoRecommend" => [
                    "includeIds" => [
                        "2709796"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Teen Girls 13-16Yrs",
                "id" => "33484800396",
                "type" => "1",
                "navNodeId" => "33484800396",
                "enName" => "TeenGirls1316Yrs",
                "is_recommend" => "0",
                "relativeUrl" => "/Teen-Girls-Clothing-c-6760.html",
                "selectTypeId" => "",
                "hrefType" => "real",
                "hrefTarget" => "6760",
                "cateTreeNodeId" => "2724335",
                "autoRecommend" => [
                    "includeIds" => [
                        "2724335"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Teen Boys 13-16Yrs",
                "id" => "33484800420",
                "type" => "1",
                "navNodeId" => "33484800420",
                "enName" => "TeenBoys1316Yrs",
                "is_recommend" => "0",
                "relativeUrl" => "/Teen-Boys-Clothing-c-6678.html",
                "selectTypeId" => "",
                "hrefType" => "real",
                "hrefTarget" => "6678",
                "cateTreeNodeId" => "2725005",
                "autoRecommend" => [
                    "includeIds" => [
                        "2725005"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Shoes & Accessories",
                "id" => "33484800455",
                "type" => "1",
                "navNodeId" => "33484800455",
                "enName" => "SHOESACCESSORIES",
                "is_recommend" => "0",
                "relativeUrl" => "/category/Kids-Accs-Shoes-sc-00825263.html",
                "selectTypeId" => "10",
                "hrefType" => "itemPicking",
                "hrefTarget" => "00825263",
                "cateTreeNodeId" => "2710328",
                "autoRecommend" => [
                    "includeIds" => [
                        "2710328"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Maternity & Nursing",
                "id" => "33484800499",
                "type" => "1",
                "navNodeId" => "33484800499",
                "enName" => "Maternity",
                "is_recommend" => "0",
                "relativeUrl" => "/style/Maternity-Clothing-sc-001121430.html",
                "selectTypeId" => "1",
                "hrefType" => "itemPicking",
                "hrefTarget" => "001121430",
                "cateTreeNodeId" => "2705556",
                "autoRecommend" => [
                    "includeIds" => [
                        "2705556"
                    ]
                ]
            ]
        ];
    }

    function getStaticCurveSectionTypes()
    {
        return [
            [
                "color" => "",
                "level" => "1",
                "name" => "New In",
                "id" => "33414300001",
                "type" => "1",
                "navNodeId" => "33414300001",
                "enName" => "NEW IN",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "#SHEINfw23",
                "id" => "33414300061",
                "type" => "1",
                "navNodeId" => "33414300061",
                "enName" => "TRENDS",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Sale",
                "id" => "33414300103",
                "type" => "1",
                "navNodeId" => "33414300103",
                "enName" => "Sale",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Clothing",
                "id" => "33414300157",
                "type" => "1",
                "navNodeId" => "33414300157",
                "enName" => "CLOTHING",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Dresses",
                "id" => "33414300216",
                "type" => "1",
                "navNodeId" => "33414300216",
                "enName" => "DRESSES",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Tops",
                "id" => "33414300270",
                "type" => "1",
                "navNodeId" => "33414300270",
                "enName" => "TOPS",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Bottoms",
                "id" => "33414300316",
                "type" => "1",
                "navNodeId" => "33414300316",
                "enName" => "BOTTOMS",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Denim",
                "id" => "33414300354",
                "type" => "1",
                "navNodeId" => "33414300354",
                "enName" => "DENIM",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Beachwear",
                "id" => "33414300389",
                "type" => "1",
                "navNodeId" => "33414300389",
                "enName" => "BEACHWEAR",
                "is_recommend" => "0",
                "relativeUrl" => "/Women-Plus-Beachwear-c-3613.html",
                "selectTypeId" => "10",
                "hrefType" => "real",
                "hrefTarget" => "3613",
                "cateTreeNodeId" => "2708910",
                "autoRecommend" => [
                    "includeIds" => [
                        "2708910"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Lingerie & Loungewear",
                "id" => "33414300418",
                "type" => "1",
                "navNodeId" => "33414300418",
                "enName" => "LINGERIELOUNGEWEAR",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Sports & Outdoor",
                "id" => "33414300479",
                "type" => "1",
                "navNodeId" => "33414300479",
                "enName" => "ACTIVEWEAR",
                "is_recommend" => "0"
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Shoes",
                "id" => "33414300511",
                "type" => "1",
                "navNodeId" => "33414300511",
                "enName" => "Shoes",
                "is_recommend" => "0"
            ]
        ];
    }

    function getStaticBeautySectionTypes()
    {
        return  [
            [
                "color" => "",
                "level" => "1",
                "name" => "New In",
                "id" => "33421800001",
                "type" => "1",
                "navNodeId" => "33421800001",
                "enName" => "NEWIN",
                "is_recommend" => "0",
                "relativeUrl" => "/new/Beauty-sc-00202571.html",
                "selectTypeId" => "2",
                "hrefType" => "itemPicking",
                "hrefTarget" => "00202571",
                "cateTreeNodeId" => "2723201",
                "autoRecommend" => [
                    "includeIds" => [
                        "2723201"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Nail Hand & Foot Care",
                "id" => "33421800028",
                "type" => "1",
                "navNodeId" => "33421800028",
                "enName" => "NailHandFootCare",
                "is_recommend" => "0",
                "relativeUrl" => "/recommend/Nail-Hand-and-Foot-Care-sc-100148856.html",
                "selectTypeId" => "21",
                "hrefType" => "itemPicking",
                "hrefTarget" => "100148856",
                "cateTreeNodeId" => "2724623",
                "autoRecommend" => [
                    "includeIds" => [
                        "2724623"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Makeup",
                "id" => "33421800055",
                "type" => "1",
                "navNodeId" => "33421800055",
                "enName" => "Makeup",
                "is_recommend" => "0",
                "relativeUrl" => "/Makeup-c-2042.html",
                "selectTypeId" => "",
                "hrefType" => "real",
                "hrefTarget" => "2042",
                "cateTreeNodeId" => "2722773",
                "autoRecommend" => [
                    "includeIds" => [
                        "2722773"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Beauty Tools",
                "id" => "33421800112",
                "type" => "1",
                "navNodeId" => "33421800112",
                "enName" => "Beautytools",
                "is_recommend" => "0",
                "relativeUrl" => "/Beauty-Tools-c-2041.html",
                "selectTypeId" => "",
                "hrefType" => "real",
                "hrefTarget" => "2041",
                "cateTreeNodeId" => "2722823",
                "autoRecommend" => [
                    "includeIds" => [
                        "2722823"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Hair",
                "id" => "33421800166",
                "type" => "1",
                "navNodeId" => "33421800166",
                "enName" => "Hair",
                "is_recommend" => "0",
                "relativeUrl" => "/category/Hair-sc-008140422.html",
                "selectTypeId" => "10",
                "hrefType" => "itemPicking",
                "hrefTarget" => "008140422",
                "cateTreeNodeId" => "2723308",
                "autoRecommend" => [
                    "includeIds" => [
                        "2723308"
                    ]
                ]
            ],
            [
                "color" => "",
                "level" => "1",
                "name" => "Personal Care",
                "id" => "33421800208",
                "type" => "1",
                "navNodeId" => "33421800208",
                "enName" => "Personalcare",
                "is_recommend" => "0",
                "relativeUrl" => "/recommend/Personal-Care-sc-100181782.html",
                "selectTypeId" => "21",
                "hrefType" => "itemPicking",
                "hrefTarget" => "100181782",
                "cateTreeNodeId" => "2722955",
                "autoRecommend" => [
                    "includeIds" => [
                        "2722955"
                    ]
                ]
            ]
        ];
    }

    public function fetchAndStoreNodes()
    {
        $counter = 0;
        foreach ($this->getStaticTabs() as $tab) {
            $channel = $tab['channelName'] ?? null;
            $catIdString = $tab['cat_id'] ?? '';
            if (!$channel || !$catIdString) continue;
            $catIds = explode(',', $catIdString);
            if ($channel == 'Kids')
                $roots = $this->getStaticKidsSectionTypes();
            else if ($channel == 'Women')
                $roots = $this->getStaticWomenSectionTypes();
            else if ($channel == 'Men')
                $roots = $this->getStaticMenSectionTypes();
            else if ($channel == 'Curve')
                $roots = $this->getStaticCurveSectionTypes();
            else if ($channel == 'Beauty')
                $roots = $this->getStaticBeautySectionTypes();
            foreach ($roots as $root) {
                $rootName = $root['name'] ?? '';
                $rootId = $root['id'] ?? null;
                if (!$rootId) continue;
                foreach ($catIds as $catId) {
                    $nodes = $this->getNodeContent($catId, $rootId);
                    $counter++;
                    Log::info("[fetchAndStoreNodes] counter: $counter, channel: $channel, rootName: $rootName, catId: $catId, rootId: $rootId");
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
        $currentValue = Session::get('request_counter', 0);
        $currentValue++;
        Session::put('request_counter', $currentValue);
        // Log::info("Request counter: " . Session::get('request_counter'));
        return $final;
    }


    function updateTrackersTable($nodeId, $page, $productsCount)
    {
        FetchIngProductTrackers::updateOrCreate(
            [
                'node_id' => $nodeId,
                'last_updated' => Carbon::now()->format('Y-m-d')
            ],
            [
                'node_id' => $nodeId,
                'total_products' => $productsCount,
                'last_page' => $page,
                'last_updated' => Carbon::now()->format('Y-m-d')
            ]
        );
    }


    public function insertProductsWitPagination($catId, $adp, $nodeId)
    {
        $page = 1;
        $totalProducts = 0;
        if ($this->checkIfAllNodeProductsUpdated($nodeId))
            return true;
        $trackers = FetchIngProductTrackers::where('node_id', $nodeId)->where('last_updated', Carbon::now()->format('Y-m-d'))->first();
        $trackerCollection = collect($this->productsTrackerFile)->where('node_id', $nodeId)->first();
        $this->maxAllowedPages = $trackerCollection ? $trackerCollection['last_page'] : 1;
        if ($trackers) {
            if ($trackers->is_finished) return;
            if ($trackers->total_products > 19) {
                $final_total_pages = ceil($trackers->total_products / 19);
                $page = isset($trackers->last_page) ? ($trackers->last_page + 1) : 1;
                if ($page > $final_total_pages || $page > $this->maxAllowedPages) {
                    $this->updateTrackerISFinishedStatus($trackers, $nodeId);
                    return;
                }
            }
        }
        do {
            if ($page > $this->maxAllowedPages) {
                $this->updateTrackerISFinishedStatus($trackers, $nodeId);
                break;
            }
            Log::info("node id: " . $nodeId);
            $products = $this->getProducts($catId, $adp, $page);
            $this->updateRequestCounter($nodeId);
            $this->updateTrackersTable($nodeId, $page, $products['total']);
            if ($products['current_count'] > 0) {
                foreach ($products['products'] as $key => $product) {
                    $productTitleAndDescription = $this->extractProductDetails($product);
                    $productModel = Product::where('external_id', $product['goods_id'])->first();
                    if (!$productModel) {
                        continue;
                        $productModel = Product::create(
                            [
                                'external_id' =>  $product['goods_id'] ?? null,
                                'normal_en_name' =>  $product['goods_name'] ?? null,
                                'en_name' =>  $productTitleAndDescription['title'] ?? null,
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
                                'brand_id' => $this->checkBrandAndCreateIfNotExists($product['premiumFlagNew']),
                                'category_id' => $this->checkCategoryAndCreateIfNotExists($product),
                                'store' => 'Shein',
                                'creation_date'  => Carbon::now()->format('Y-m-d'),
                                'last_updated'  => Carbon::now()->format('Y-m-d'),
                                'images' => $product['detail_image'] ?? [],
                                'parent_categories' => $product['parentIds'] ?? [],
                                'node_id' => $nodeId
                            ]
                        );
                    } else {
                        $productModel->price = $product['retailPrice']['amount'] ?? 0;
                        $productModel->primary_image = $product['goods_img'] ?? null;
                        $productModel->is_lowest_price = $product['is_lowest_price'] ?? 0;
                        $productModel->is_highest_sales = $product['is_highest_sales'] ?? 0;
                        $productModel->video_url = $product['video_url'] ?? null;
                        $productModel->mall_code = $product['mall_code'] ?? null;
                        $productModel->images = $product['detail_image'] ?? [];
                        $productModel->parent_categories = $product['parentIds'] ?? [];
                        $productModel->last_updated = Carbon::now()->format('Y-m-d');
                        $productModel->save();
                    }
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
                    } else {
                        $productModel->details()->update([
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
        $this->updateTrackerISFinishedStatus($trackers, $nodeId);
    }

    function checkIfAllNodeProductsUpdated($nodeId)
    {
        $updatedProducts =  Product::where('node_id', $nodeId)->whereDate('last_updated', Carbon::now()->format('Y-m-d'))->count();
        $totalProducts = Product::where('node_id', $nodeId)->count();
        return $totalProducts == $updatedProducts ? true : false;
    }

    function updateRequestCounter($nodeId)
    {
        $requestCounter = RapidApiSheinRequestTrackers::where('node_id', $nodeId)->where('last_updated', Carbon::now()->format('Y-m-d'))->first();
        $finalCounter = ($requestCounter?->request_count ?? 0) + Session::get('request_counter', 0);
        Session::put('request_counter', 0);
        RapidApiSheinRequestTrackers::updateOrCreate([
            'node_id' => $nodeId,
            'last_updated' => Carbon::now()->format('Y-m-d')
        ], [
            'node_id' => $nodeId,
            'last_updated' => Carbon::now()->format('Y-m-d'),
            'request_count' => $finalCounter
        ]);
    }

    function updateTrackerISFinishedStatus($trackers, $nodeId)
    {
        if (!$trackers)
            $trackers = FetchIngProductTrackers::where('node_id', $nodeId)->where('last_updated', Carbon::now()->format('Y-m-d'))->first();
        $trackers->is_finished = 1;
        $trackers->save();
        $this->updateRequestCounter($nodeId);
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
                'brand_badge_name_en' => $brandObject['brand_badge_name'] ?? null,
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
            'name_en' => $productObject['cate_name']
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
