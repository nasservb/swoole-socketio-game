<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
//use Illuminate\Foundation\Auth\User as Authenticatable;
use Jenssegers\Mongodb\Auth\User as Authenticatable;



class User extends Authenticatable
{
    use Notifiable;
    protected $table = "users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'password','api_token','full_name','ip','seq',
        'device_id','coin_count','card_number',
        'rank','xp','referrer_code','active_game_id','avatar_url',
		'is_join_telegram','is_join_instagram','is_man' ,'is_register','timer_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','card_number', 'rank',
        'xp','device_id','coin_count',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /**
    * The collection name
    *
    * @var array
    */
    protected $collection = 'users';
    
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $dates = ['deleted_at'];


    public static function isValidToken( $token) {
    

        if (strlen($token) < 10 )
        {
            return  ['is_success'=> 0 ,'message'=>'error in token']; 
        } 
    
        $user = User::query()
                        ->where('api_token', $token )
                        ->first(); 
    
       if (!$user  )
       { 
            return ['is_success'=> 0 ,'message'=>'token not found']; 
       } 
    
       return ['is_success'=>1 , 'user'=>$user] ; 
    } 
	
	public static function addScore($userObject , $coinCount,$rank, $xp )
	{
		$newRank = $userObject->rank + $rank;
		$newRank = ($newRank <0 )? 0 : $newRank ; 

		User::query()
			->where('id',$userObject->id)
			->update(
				[
					'coin_count'=>($userObject->coin_count+$coinCount),
					'rank'=>($newRank),
					'xp'=>($userObject->xp+$xp),
					'active_game_id'=>0
				]
				);     

	}
}
