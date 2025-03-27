<?php

namespace App\Models;

use App\Enums\BalanceType;
use App\Models\Address;
use App\Models\Balance;
use App\Models\Bank;
use App\Models\DeliveryBoyAccount;
use App\Models\Order;
use App\Models\OrderHandler;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\UserDeposit;
use App\Models\Waiter;
use App\Presenters\CustomerPresenter;
use App\Presenters\InvoicePresenter;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Notifications\Notifiable;
use Shipu\Watchable\Traits\HasModelEvents;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable
{
    use Notifiable;
    protected $guard_name = 'web';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime'
    ];
}
