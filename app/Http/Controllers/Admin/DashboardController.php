<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Order;
use App\Enums\UserStatus;
use App\Enums\OrderStatus;
use App\Models\Restaurant;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Enums\RestaurantStatus;
use App\Enums\DeliveryHistoryStatus;
use App\Models\DeliveryStatusHistories;
use App\Http\Controllers\BackendController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;

class DashboardController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['siteTitle'] = 'Dashboard';
    }

    public function index()
    {
        return view('admin.dashboard.index', $this->data);
    }
}
