<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\User;

use App\Models\Game;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Validator;

class UserApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		$user = User::find(Auth::user()->id); 
        return [
			'code'=>200,
			'data'=>[
				 'username'=>$user->username,
				 'is_register'=>intval($user->is_register),
				 'full_name'=>$user->full_name,
				 'coin_count'=>$user->coin_count ,
				 'avatar_url'=>(is_null($user->avatar_url) ? '' : $user->avatar_url),
				 'rank'=>$user->rank,
				 'xp'=>$user->xp,
				 'is_man'=>intval($user->is_man),
				 'card_number'=>$user->card_number,
				 'referrer_code'=>$user->referrer_code,
				 'created_at'=>$user->created_at,
				 'is_join_instagram'=>intval($user->is_join_instagram),
				 'is_join_telegram'=>intval($user->is_join_telegram),
				 
					]
		];
    }

    /**
     * Show the user data.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request)
    {
		/*
		$win =   Game::query()
						->where('user_id',Auth::user()->id )
						->where('result',2 )
						->count();
		
        $game = Game::query()->select('result',\DB::select('count(result)as res'))
						->groupBy('result')
						->where('user_id',Auth::user()->id )
						; 
						return $game; 
						*/
		return [
			'code'=>200,
			'data'=>[
				 'win_count'=>1,
				 'lose_count'=>3,
				 'equal_count'=>5,
				 
					]
		];			
    }

    /**
     * profileUpdate for user and return token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function profileUpdate(Request $request)
    {
		$input = $request->all() ; 
		$user = User::find(Auth::user()->id); 

		if ($user->is_register == 1 )
		{

			if (
				(isset($input['card_number']) && $input['card_number']!= '' ) || 
				(isset($input['full_name']) && $input['full_name']!= '')|| 
				(isset($input['is_man']) && $input['is_man']!= '')
			)
			{
				$validator = Validator::make($request->all(),[
					'card_number'=>'required|string|min:16|max:16',
					'full_name'=>'required|string|min:5',
					'is_man'=>'required',
				]);

				if($validator->fails()){
					 return [
						'code'=>403,
						'data'=>[ 
							
							],
						'message'=> $validator->errors()->first()
						];		
				}

				$user->full_name =  $input['full_name'] ;
				$user->card_number =  $input['card_number'] ;
				$user->is_man =  intval($input['is_man']) ;
				$user->save();



			}

			
			if (
				(isset($input['password']) && $input['password']!= '' ) || 
				(isset($input['now_password']) && $input['now_password']!= '')|| 
				(isset($input['password_confirmation']) && $input['password_confirmation']!= '')
			)
			{
				$validator = Validator::make($request->all(),[
					'now_password'=>'required|string|min:6',
					'password'=>'required|string|min:6|confirmed|different:now_password',
					 
				]);

				if($validator->fails()){
					 return [
						'code'=>403,
						'data'=>[ 
								
								],
						'message'=> $validator->errors()->first()
						];		
				}
				
				/*
				$credentials = [
					'username' => $user->username,
					'password' => $input['now_password'],
				];
				*/

				if (Hash::check($input['now_password'], $user->password)){
				//if (Auth::guard('user')->attempt($credentials)) {
					
					$user->password =  Hash::make($input['password']) ;
					$user->save();
					
				}
				else 
				{
					return array(
								'data'=>array('message'=>'username or password incorrect'), 
								'status'=>303);
				} 
            
			}	
			
			
			
			return [
				'code'=>200,
				'data'=>$user
			];
			
		}
		else 
		{
			
			
			$validator = Validator::make($request->all(),[
				'card_number'=>'required|string|min:16',
				'full_name'=>'required|string|min:5',
				'is_man'=>'required',
				'password'=>'required|string|min:6|confirmed',
				'username'=>'required|string|min:8|unique:users',
				
			]);

			if($validator->fails()){
				 return [
					'code'=>403,
					'data'=>[ 
							
							],
					'message'=> $validator->errors()->first()
					];		
			} 
			
			
			$user->password =  Hash::make($input['password']) ;;
			$user->username =  $input['username'] ;
			$user->full_name =  $input['full_name'] ;
			$user->card_number =  $input['card_number'] ;
			$user->is_man =  intval($input['is_man']) ;
			$user->is_register = 1;
			$user->save();
			
			return [
				'code'=>200,
				'data'=>$user
			];
			
		} 
		
		//@todo updating profile
        	
    }

     /**
     * Store a newly guest request and return token .
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function joinTelegram(Request $request ) 
    {
		$user = User::find(Auth::user()->id); 
		if (intval($user->is_join_telegram) == 0)
		{
			$user->coin_count += 99; 
			$user->is_join_telegram  = 1; 
			$user->save();
			
			return [
				'code'=>200,
				'data'=>[
						'new_coin_count'=>$user->coin_count				
						]
				];			
		}
		else
		{
			
			return [
				'code'=>130,
				'data'=>[
						'msg'=>'already added'				
						]
				];
		}		
        
                    
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function joinInstagram(Request $request)
    {
        
		$user = User::find(Auth::user()->id); 
		if (intval($user->is_join_instagram) == 0)
		{
			$user->coin_count += 99; 
			$user->is_join_instagram  = 1; 
			$user->save();
			
			return [
				'code'=>200,
				'data'=>[
						'new_coin_count'=>$user->coin_count				
						]
				];			
		}
		else
		{
			
			return [
				'code'=>130,
				'data'=>[
						'msg'=>'already added'				
						]
				];
		}		
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function watchVideo(Request $request)
    {
        //@todo consider just one call in day
		$user = User::find(Auth::user()->id); 
		 
		$user->coin_count += 99;  
		$user->save();
		
		return [
			'code'=>200,
			'data'=>[
					'new_coin_count'=>$user->coin_count				
					]
			];			
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function topList(Request $request)
    {
		$user = User::find(Auth::user()->id); 
		
        return [
			'code'=>200,
			'data'=>[ 
			[
				'username'=>$user->username,				
				 'full_name'=>$user->full_name,
				 'coin_count'=>$user->coin_count ,
				 'avatar_url'=>(is_null($user->avatar_url) ? '' : $user->avatar_url),
				 'rank'=>$user->rank,
				 'xp'=>$user->xp,
				 'is_man'=>intval($user->is_man),				
			],
			[
				'username'=>$user->username,				
				 'full_name'=>$user->full_name,
				 'coin_count'=>$user->coin_count ,
				 'avatar_url'=>(is_null($user->avatar_url) ? '' : $user->avatar_url),
				 'rank'=>$user->rank,
				 'xp'=>$user->xp,
				 'is_man'=>intval($user->is_man),				
			],
			[
				'username'=>$user->username,				
				 'full_name'=>$user->full_name,
				 'coin_count'=>$user->coin_count ,
				 'avatar_url'=>(is_null($user->avatar_url) ? '' : $user->avatar_url),
				 'rank'=>$user->rank,
				 'xp'=>$user->xp,
				 'is_man'=>intval($user->is_man),				
			],
			
					]
		];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function activeList(Request $request)
    {
        $user = User::find(Auth::user()->id); 
		
        return [
			'code'=>200,
			'data'=>[ 
			[ 			
				 'full_name'=>$user->full_name, 
				 'avatar_url'=>(is_null($user->avatar_url) ? '' : $user->avatar_url),
				 'rank'=>$user->rank, 
				 'is_man'=>intval($user->is_man),				
			],
			[
				 'full_name'=>$user->full_name, 
				 'avatar_url'=>(is_null($user->avatar_url) ? '' : $user->avatar_url),
				 'rank'=>$user->rank, 
				 'is_man'=>intval($user->is_man),			
			],
			[
				 'full_name'=>$user->full_name, 
				 'avatar_url'=>(is_null($user->avatar_url) ? '' : $user->avatar_url),
				 'rank'=>$user->rank, 
				 'is_man'=>intval($user->is_man),				
			],
			
					]
		];
    }
  
}
