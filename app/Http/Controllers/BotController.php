<?php

namespace App\Http\Controllers;

use App\Game;
use App\Models\GameHistory;
use App\Models\GameWord;

use Illuminate\Http\Request;
 

class BotController extends Controller
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
    public function createWord($game)
    {
		sleep(1);
		
		$lastWord = GameHistory::query()->where('game_id' , $game->id)
									->where('is_time_out',0)
									->latest()
									->first(); 
       
        
		
        if (!$lastWord )
		{
			//first word 
			//get random word
			/*****not work in mongo db 
			$word = GameWord::inRandomOrder()
						->limit(1)
						->first();
						*/
						
			$chars =[' ا','ب','پ','ج','چ','ح','خ','د','ر','ز','س','ش','ص','ع','غ','ف','ق','ک','ل','گ','م','ن','و','ه','ی'] ; 		
			$word =  GameWord::where('word', 'regexp', '/^'.$chars[rand(0,count($chars)-1)].'/i')
								->limit(1)
								->first();
			
			if ($word )
				return $word->word;
			else 					
				return 'آب';		
			
		}
		else 
		{
			$historyRows = GameHistory::query()
						->where('game_id' , $game->id)
						->where('is_time_out',0)
						->select('word')
						->get()
						->toArray(); 
		
			$lastChar  =   mb_substr($lastWord->word,-1)  ; 
			$history=array($lastChar ); 
		 
			foreach($historyRows as $row  )
			{
				$history[] = $row['word'];
			}
		
            //get word start with lastChar 
			$word =  GameWord::where('word', 'regexp', '/^'.$lastChar.'/i')
								->whereNotIn('word',$history)
								->limit(1)
								->first();
								
		
		
			if ($word )
				return $word->word;
			else 
				return '';	
        } 
		
		return '';
		
         
    }

    
}
