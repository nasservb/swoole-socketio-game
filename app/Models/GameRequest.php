<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;

class GameRequest extends Model
{
    /**
    * The collection name
    *
    * @var array
    */
    protected $collection = "game_requests";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'time','time_out','rank' ,'is_done','with_bot','try_count',
    ];

  
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         
    ];
 

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
	
	public static function findMatch($user,$rank,$rankArea )
	{
		return GameRequest::where('time_out' , '>=' ,  time() )
                    ->whereBetween('rank', [($rank -$rankArea) , ($rank+$rankArea) ])
                    ->where('is_done', 0) 
                    ->where('user_id','<>', $user->id ) 
                    ->with('user')
                    ->first(); 
	}

 
}
