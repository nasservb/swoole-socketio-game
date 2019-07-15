<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Validator;

class LoginApiController extends Controller
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
     * Store a newly created user and return token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		//
         $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            // Authentication passed...
			return array('data'=>array('token'=>Auth::user()->api_token , 'name'=>Auth::user()->full_name),'status'=>200);
            // return ;
        }
		else 
		{
			return array('data'=>array('message'=>'username or password incorrect'), 'status'=>303);
		}
    }

     /**
     * Store a newly guest request and return token .
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getGuestToken(Request $request ) 
    {
        
        $validator = Validator::make($request->all(),[
            'device_id'=>'required|string|min:10'
        ]);


        if($validator->fails()){
            
            return array('data'=>array('message'=>$validator), 'status'=>403);
             
        }

        $input = $request->all() ; 


        $number = 0 ; 
        do{
            $number = mt_rand(10, 9999999999); // better than rand()
        
        }while( 
                User::query()
                        ->where('username','guest_'.$number )
                        ->where('is_register',0)
                        ->count()>0 );

        $count  = User::query()->count(); 

        $user = User::Create([
                            'seq'=>$count,
                            'device_id'=>$input['device_id'] , 
                            'full_name'=>'guest_'.$number , 
                            'username'=>'gst_'.$number , 
                            'password'=>  Hash::make('p$%@#%#$%uest_'.$number) , 
                            'is_register'=>0  , 
                            'api_token'=> Hash::make($number.'p$%@^&*4!@#st_') , 
                            'ip'=>$request->ip() , 
                            'card_number'=> '', 
                            'coin_count'=> 0,
                            'remember_token'=> '',
                            'rank'=>0,
                            'xp'=>0,
                            ] ); 

        return array('data'=>array('token'=>$user->api_token , 'name'=>'guest_'.$number,'seq'=>$user->seq),'status'=>200);                   
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
