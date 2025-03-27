<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\MenuItemStatus;
use App\Enums\OrderStatus;
use App\Enums\RatingStatus;
use App\Http\Services\PushNotificationService;
use App\Http\Services\RatingsService;
use App\Models\BackendMenu;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\RestaurantRating;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use App\Models\Coupon;
use App\Models\Cuisine;
use App\Models\Discount;
use App\Models\Restaurant;
use App\Enums\CuisinesStatus;
use App\Enums\RestaurantStatus;
use App\Http\Controllers\FrontendController;
use Sopamo\LaravelFilepond\Filepond;
use Request;

class WebController extends FrontendController
{
    public function __construct()
    {
        parent::__construct();
        if (file_exists(storage_path('installed'))) {
            $this->data['site_title'] = '';
        } else {
            return redirect('/install');
        }
    }

    public function index()
    {
        return view('frontend.home-page', $this->data);
    }

    public function test()
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        Artisan::call('permission:cache-reset');

        dd('Update');

        //
//
//        $order   = Order::where(['id' => 1])->with('items')->first();
//        $user   = User::where(['id' => 21])->first();
// app(PushNotificationService::class)->sendNotificationOrder($order, $user, 'restaurant');
    }
    public $blockNodes = [];
    private function backendMenu()
    {
        $backendMenus = BackendMenu::where(['status' => 1])->get()->toArray();

        $myMenu = '';
        $nodes = $this->menuTree($backendMenus, $this->blockNodes);

        $this->frontendMenu($nodes, $myMenu);

        return $myMenu;
    }

    private function menuTree(array $nodes, array $blockNodes = null)
    {
        $tree = [];
        foreach ($nodes as $id => $node) {
            if (isset($node['link']) && !isset($blockNodes[$node['link']])) {
                if ($id == 50) {
                    dd($blockNodes);
                }
                if (in_array($node['link'], $blockNodes)) {
                    continue;
                }

                if (($node['link'] != '#') && !blank(auth()->user()) && !auth()->user()->can($node['link'])) {
                    continue;
                }

                if ($node['parent_id'] == 0) {
                    $tree[$node['id']] = $node;
                } else {
                    if (!isset($tree[$node['parent_id']]['child'])) {
                        $tree[$node['parent_id']]['child'] = [];
                    }
                    $tree[$node['parent_id']]['child'][$node['id']] = $node;
                }
            }

        }

        return $tree;
    }

    private function frontendMenu(array $nodes, string &$menu)
    {
        foreach ($nodes as $node) {
            if (isset($node['link'])) {

                $f = 0;
                $dropdown = 'nav-item dropdown ';
                $dropdownToggle = 'has-dropdown';
                $active = '';

                if ($node['link'] == '#' && !isset($node['child'])) {
                    continue;
                }

                if (isset($node['child'])) {
                    $f = 1;
                    $childArray = collect($node['child'])->pluck('link')->toArray();

                    $segmentLink = Request::segment(2);

                    if (in_array($segmentLink, $childArray)) {
                        $active = 'active';
                    }
                }

                if (Request::segment(2) == $node['link']) {
                    $active = 'active';
                }

                $menu .= '<li class="' . ($f ? $dropdown : '') . $active . '">';
                $menu .= '<a class="nav-link ' . ($f ? $dropdownToggle : '') . '" href="' . url('admin/' . $node['link']) . '" >' .
                    '<i class="' . ($node['icon'] != null ? $node['icon'] : 'fa-home') . '"></i> <span>' . (trans('menu.' . $node['name'])) . '</span></a>';

                if ($f) {
                    $menu .= '<ul class="dropdown-menu">';
                    $this->frontendMenu($node['child'], $menu);
                    $menu .= "</ul>";
                }
                $menu .= "</li>";
            }
        }
    }
}
