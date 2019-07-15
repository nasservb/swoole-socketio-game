<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

use Jenssegers\Mongodb\Eloquent\Model;

class GameWord extends Model
{
     /**
    * The collection name
    *
    * @var array
    */
    protected $collection = "game_words";

     /**
      * The attributes that are mass assignable.
      *
      * @var array
      */
     protected $fillable = [
         'id', 'word' 
     ];
 
   
 
     /**
      * The attributes that should be hidden for arrays.
      *
      * @var array
      */
     protected $hidden = [
          
     ];

    /**
     * consider word exist in db or not 
     * 
     * @param string $word
     * @return boolean is exist or not
     * 
     *  */ 
    public static function isValid($word)
    {

        $search =  GameWord::query()->where('word', $word)->count(); 
        
        return  ( $search  >0 ? true : false  ); 

    }
}
