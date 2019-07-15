<?php



use SwooleTW\Http\Websocket\Facades\Room;

use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\UserCoin;
use App\Models\GameRequest;
use App\Models\GameWord;
use App\Models\Game;
use App\Models\GameHistory;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use SwooleTW\Http\Websocket\Facades\Websocket;

use Swoole\Timer;
/*
|--------------------------------------------------------------------------
| Websocket Routes
|--------------------------------------------------------------------------
|
| Here is where you can register websocket events for your application.
|
*/ 

/*********************** */

Websocket::on('connect', function ($websocket, Request $request) {   
  		 
			
    $input = $request->all(); 
	
    $user =User::isValidToken($input['token']); 
 			
   if (!$user['is_success'])  
   {
       Websocket::emit('error',array('code'=>171,'msg'=>'connection invalid token')); //
	   
       return ; 
   }

   Websocket::loginUsingId($user['user']->id );
   
   echo "\n".'connected to server '.$user['user']->id."\n";
	
	if ($user['user']->active_game_id >1)  
    {
		$game = Game::find( $user['user']->active_game_id ) ; 
		
		app('App\Http\Controllers\GameController')->rejoin($game ,  $user['user']);	
		
		if (($game->is_finish == 0) && ($game->finish_time<time()) ){
			
			if (Timer::exists($game->timer_id))
			{
				Timer::clear($game->timer_id); 
			} 
			
			$timer = Timer::after(15000, function () use ($game){gameTimeout($game->id , $game->step);},null);//15 sec
				
			$game->update([
						'rand_time_out'=> time()+15,
						'timer_id'=>$timer
					]);
		}
		
		
	}

 
});

Websocket::on('disconnect', function ($websocket) {
	
	$user = User::find(Websocket::getUserId()); 
	if (!$user['user'])
		return ; 
	
    echo "user ". Websocket::getUserId() ." disconnected"."\n";

});

Websocket::on('requestMatch', function ($websocket, $data) {
	


   $user =User::isValidToken($data['token']); 
   if (!$user['is_success'])  
   {
       Websocket::emit('error',array('code'=>173,'msg'=>'requestMatch invalid token')); 
       return ; 
   }
 
   $match =  GameRequest::findMatch($user['user'], $user['user']->rank,10);
 
    if(!$match) 
    {

        //create request

        $newMatch = GameRequest::Create([
            'user_id'=>$user['user']->id,
             'time'=>time(),
             'time_out'=>(time()+60 ) ,
             'rank'=>$user['user']->rank ,
             'is_done'=>0,
             'with_bot'=>0,
			 'try_count'=>1
        ]);

		$user['user']->active_game_id = 0 ; 
		$user['user']->save(); 
		
		//   Player with new rank
		$timer = Timer::after(2000, function () use($newMatch,$user) {findAnotherPlayer($newMatch,$user['user']->id); }  ,null );//2 sec
		//start tick to another
		
        Websocket::emit('searching', 'you are first user' ); 
		echo 'user '.$user['user']->full_name .' start searching ...'."\n";
		
    }  
    else 
    {
		$game = app('App\Http\Controllers\GameController')->create(  $user['user'],$match);	
		
		startGamePlay($match->user,$user['user'],$game);
		
		if (Timer::exists($user['user']->timer_id))
		{
		   Timer::clear($user['user']->timer_id); 	 
		} 
		
		$player2 = ($game->user_req ==$user['user']->id ? $game->user_res : $game->user_req ) ;
		
		$user2 = User::find($player2);
		
		if (Timer::exists($user2->timer_id))
		{
		   Timer::clear($user2->timer_id); 	 
		} 
		
		$timer = Timer::after(15000, function () use ($game){gameTimeout($game->id , $game->step);},null);
		
		$game->update(['timer_id'=>$timer ]);
		
    }


});

Websocket::on('sendWord', function ($websocket, $data) {

    echo 'data received  ' . $data['word']."\n" ;
	
    $user =User::isValidToken($data['token']); 

    if (!$user['is_success'])  
    {
        Websocket::emit('error',array('code'=>174,'msg'=>'sendWord invalid token')); 
        return ; 
    }

	
	if ($user['user']->active_game_id==0 )
	{
		echo 'no active game found on '.$user['user']->active_game_id ;
		return;
	}
	 
    $game = Game::find( $user['user']->active_game_id ) ; 
    	 
    if ($game->rand_user != $user['user']->id )
    {  
       Websocket::emit( 'rejectWord',array(
                                        'err'=> 500 , 
                                        'msg'=>'rand is not for you'
                                    )
                );
        return ; 

    } 

  

    if(GameWord::isValid($data['word']))
    { 

        app('App\Http\Controllers\GameController')->processWord(   $game,$user['user'],$data['word']);	
		
		if ($game->game_time_out ==1 || $game->finish_time< time())
		{
			$user2=null ; 
			
			if ($game->with_bot ==0 )
			{				
				$user2=User::find($game->user_seq);
			}	
			
			 
			app('App\Http\Controllers\GameController')->finishGame(   $game,$user['user'],$user2 );	
			
			if (Timer::exists($game->timer_id))
			{
			   Timer::clear($game->timer_id);
			}
			
			$game->Update(['is_finish'=>1 ]); 
			
			return ; 

		}  
		
		
        
        $timer = 0 ; 

        if (Timer::exists($game->timer_id))
        {
           Timer::clear($game->timer_id); 
        } 
		
		$timer = Timer::after(15000, function () use ($game){gameTimeout($game->id , $game->step);},null);
		
		$player2 =$user['user']->id;
		if ($game->with_bot == 0 ) 
			$player2 = ($game->user_req ==$user['user']->id ? $game->user_res : $game->user_req ) ;
		else 
			
		
		
        $game->update([
            'rand_time_out'=> time()+15,
            'rand_user'=>(($game->rand_user == $user['user']->id) ? $player2 :$user['user']->id ),
            'timer_id'=>$timer
        ]); 


    }
    else 
    {

        Websocket::emit( 'rejectWord',['word'=> $data['word'] , 'reason'=>1,'message'=>'invalid word']); //[already use ]

    }
});


/********************functions***************************/
/*														*/
/*														*/
/********************************************************/
function findAnotherPlayer(  $newMatch,$userId )
{ 
	$user = User::find($userId) ; 
	
	if ($user->active_game_id  > 0  )
	{
		echo 'user find another game '; 
		
		return ; 
	} 
	
	if ($newMatch->try_count >= 3 )
	{
		//play with bot 
		//@todo play with bot
		$withBot = true; 
		$game=app('App\Http\Controllers\GameController')->create(  $user,$newMatch,$withBot);	
			
		startGamePlay($newMatch->user,$user,$game);
		
		$timer = Timer::after(15000, function () use ($game){gameTimeout($game->id , $game->step);},null);
		
		$game->update(['timer_id'=>$timer]); 
	}
	else 
	{ 
		$rankArea = $newMatch->rank * $newMatch->try_count *  10 ; //generate rank step 100 , 1000, 10000
		
		$match = GameRequest::findMatch($user, $user->rank,$rankArea);
                
		if(!$match) 
		{
			$newMatch->try_count ++ ; 
			$newMatch->save(); 
			
			//   Player with new rank
			$timer = Timer::after(2000, function () use($newMatch,$user) {findAnotherPlayer($newMatch,$user->id); }  ,null);//2 sec
			//start tick to another
			
			$user->timer_id = $timer;
			$user->save();
		}
		else 
		{
			$game=app('App\Http\Controllers\GameController')->create(  $user,$match);	
			
			startGamePlay($match->user,$user['user'],$game);
			
			$timer =Timer::after(15000, function () use ($game){gameTimeout($game->id , $game->step);},null);
			
			$game->update(['timer_id'=>$timer]); 
			
			$player2 = ($game->user_req ==$user->id ? $game->user_res : $game->user_req ) ;
			$user2 = User::find($player2);
		
			if (Timer::exists($user2->timer_id))
			{
			   Timer::clear($user2->timer_id); 	 
			} 
		
			
		}
	
	}
}

function gameTimeout($gameId,$step )
{
    $game = Game::find($gameId); 
	
	if ($game->step == $step && $game->is_finish ==0 )
	{
		$timer =0;
		if (Timer::exists($game->timer_id))
		{
		   Timer::clear($game->timer_id);

	 
		} 
		
		$timer = Timer::after(15000,  function () use ($game){gameTimeout($game->id , $game->step);},null);//15 sec
		app('App\Http\Controllers\GameController')->timeout(   $game,$timer);	
		
	}
	
	
}

function startGamePlay($user1,$user2,$game){
		
    //Timer::tick( $game->finish_time - time() , function ()use($user1,$user2,$game){
    Timer::tick( 1000*(60*2) , function ()use($user1,$user2,$game){
		  
			app('App\Http\Controllers\GameController')->finishGame(   $game,$user1,$user2 );	
			
			if (Timer::exists($game->timer_id))
			{
			   Timer::clear($game->timer_id);
			}
			
			 
        }
    );
} 



 
