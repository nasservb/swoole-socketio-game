<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

use Jenssegers\Mongodb\Eloquent\Model;

class Game extends Model
{
     /**
    * The collection name
    *
    * @var array
    */
    protected $collection = "games";

     /**
      * The attributes that are mass assignable.
      *
      * @var array
      */
     protected $fillable = [
         'user_req', 'user_res', 'with_bot','is_start','is_finish','is_wait','rand_user','rand_time_out','start_time','finish_time','game_time_out','result','timer_id','start_wait_time','bot_name','step'
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
         return $this->belongsTo(User::class, 'user_req');
     }
	 
	 
}
