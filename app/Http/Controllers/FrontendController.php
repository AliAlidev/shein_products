<?php

namespace App\Http\Controllers;

class FrontendController extends Controller
{
    public $data = [];

    public function __construct()
    {
        $this->data['site_title'] = 'Home';
    }
}
