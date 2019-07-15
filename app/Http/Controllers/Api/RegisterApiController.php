<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Validator;


class RegisterApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        $validator = Validator::make($request->all(),[
            'device_id'=>'required|string|min:10',
            'username'=>'required|string|min:8|unique:users',
            'password'=>'required|string|min:6|confirmed',
            'full_name'=>'required|string|min:4',
            'card_number'=>'required|string|min:16',
        ]);

        if($validator->fails()){
            return array('data'=>array('message'=> $validator->errors()->first()), 'status'=>403);
        }

        $input = $request->all() ; 

        $referrer = (isset($input['referrer_code'] ) ? $input['referrer_code']  : ''  );

        $number = mt_rand(10, 9999999999); // better than rand()

        $count  = User::query()->count(); 

        $user = User::Create([
            'seq'=>$count,
            'device_id'=>$input['device_id'] , 
            'full_name'=>$input['full_name'] , 
            'username'=> $input['username'] , 
            'password'=>  Hash::make($input['password'])  , 
            'is_register'=>1 , 
            'api_token'=> Hash::make($number.'p$%@^@#$@12#st_') , 
            'card_number'=> $input['card_number'],
            'referrer_code'=> $referrer,
            'coin_count'=> 0,
            'remember_token'=> '',
            'rank'=>0,
            'xp'=>10,
            'ip'=>$request->ip() , 

            ] ); 

        return array('data'=>array('token'=>$user->api_token , 'name'=>$input['full_name']),'status'=>200);      

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
