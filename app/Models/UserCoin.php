<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

use Jenssegers\Mongodb\Eloquent\Model;

class UserCoin extends Model
{
   
    /**
    * The collection name
    *
    * @var array
    */
    protected $collection = 'user_coins';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'coin_count','is_inc','type' 
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

}
