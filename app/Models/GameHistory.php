<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

use Jenssegers\Mongodb\Eloquent\Model;

class GameHistory extends Model
{
     /**
    * The collection name
    *
    * @var array
    */
    protected $collection = "game_histories";

     /**
      * The attributes that are mass assignable.
      *
      * @var array
      */
     protected $fillable = [
         'id','user_id', 'word', 'is_time_out','score','game_id','is_time_out'
     ];
 
			
     /**
      * The attributes that should be hidden for arrays.
      *
      * @var array
      */
     protected $hidden = [
          
     ];

    
    public static function addHistory($game , $userObject,$word,$score )
	{
		GameHistory::Create(
        [
            'game_id'=>$game->id,
            'user_id'=>$userObject->id,
            'word'=>$word, 
            'score'=>$score,
            'is_time_out'=>($word == '' ? 1 : 0)
        ]
        );
	}
}