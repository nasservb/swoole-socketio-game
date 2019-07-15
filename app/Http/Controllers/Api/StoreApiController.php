<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Validator;

class StoreApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return [
			'code'=>200,
			'data'=>[
				['id'=>1,'price'=>300,'coin_count'=>20],
				['id'=>2,'price'=>400,'coin_count'=>40],
				['id'=>3,'price'=>500,'coin_count'=>70],
				['id'=>4,'price'=>700,'coin_count'=>100],
					]
		];
    }

    
}
