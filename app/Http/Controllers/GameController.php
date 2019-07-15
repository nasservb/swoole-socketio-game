<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameHistory;
use App\Models\User;
use App\Models\GameRequest;

use Illuminate\Http\Request;

use Swoole\Timer;

use SwooleTW\Http\Websocket\Facades\Websocket;
use SwooleTW\Http\Websocket\Facades\Room;

class GameController extends Controller
{

    /**
     * Create New Game.
     *	
	 * @param User $user 	
	 * @param RequestGame $match 	
	 * @param boolean $withBot 
	 *
     * @return void
     */
    public function create($user , $match,$withBot=false )
    {
        Game::where('user_req',$user->id)->update(['is_finish'=>1]); 
		
        //update request
        $match->update(['is_done'=>1]) ; 
        //create game 
		if ($withBot ==false)
		{
			Game::where('user_req',$match->user_id)
							->orwhere('user_req',$user->id)
							->orwhere('user_res',$match->user_id)
							->orwhere('user_res',$user->id)
							->update(['is_finish'=>1]);
			
			$game=Game::Create([
						'user_req'=>$match->user_id,
						'user_res'=>$user->id,
						'with_bot'=>0,
						'is_start'=>1,
						'is_finish'=>0,
						'is_wait'=>0,
						'step'=>0,
						'start_wait_time'=>0,
						'rand_user'=>$match->user_id,
						'rand_time_out'=>time()+15,
						'start_time'=>time(),
						'finish_time'=>time()+(60*2), //2 minute
						'game_time_out'=>0,
						'result'=>-1
			]) ; 
			
			 //create room and start match 
			$roomName = $match->user_id . '_'. $user->id ; 

			
			$members = Room::getClients($roomName);
			foreach($members as $member)
			{
				Room::delete($member,$roomName);
			}
			

			Room::add($match->user->id,$roomName);
			Room::add($user->id,$roomName);
			
			echo 'Mached users '.$match->user_id." , ".$user->id."\n";
			
			$word = (new BotController())->createWord($game) ; 
			
			GameHistory::Create(
				[
					'game_id'=>$game->id,
					'user_id'=>0,
					'word'=>$word, 
					'score'=>0,
					'is_time_out'=>0
				]
				);
			
			//PLAYER 1 (JAVAD split player1 and player2 because client need finde who is player one)
			
			Websocket::toUserId($match->user_id)->emit('startGame', [
				'room_name'=>$roomName , 
				'match_player2'=>['name'=>$user->full_name,'avatar'=>'','startWord'=>$word]			
			] );
			
			
			//PLAYER 2 (JAVAD split player1 and player2 because client need finde who is player one)

			Websocket::toUserId($user->id)->emit('startGame', [
				'room_name'=>$roomName , 
			   'match_player1'=>['name'=>$match->user->full_name,'avatar'=>'','startWord'=>$word]		
			] );

			//remove another requests
			
			GameRequest::query()
							->where('user_id',$user->id)
							->orwhere('user_id',$match->user_id)
							->update([
								'is_done'=>1
							]);
							
			User::find($user->id)
							->update([
								'active_game_id'=>$game->id
							]);


			User::find($match->user->id)
							->update([
								'active_game_id'=>$game->id
							]);

			
			
			Websocket::toUserId($match->user->id)->emit('startRand', ['timeout'=>time()+15]);//

			
			$game->update([ 
                        'rand_time_out'=> time()+15,
                        'rand_user'=>$match->user->id,
                        ]);
			return $game; 
		}
		else 
		{
			$botName = 'bot_'.rand(1565,999999);
			$game=Game::Create([
						'user_req'=>$match->user_id,
						'user_res'=>0,
						'with_bot'=>1,
						'bot_name'=> $botName ,
						'is_start'=>1,
						'is_finish'=>0,
						'is_wait'=>0,
						'step'=>0,
						'start_wait_time'=>0,
						'rand_user'=>$match->user_id,
						'rand_time_out'=>time()+15,
						'start_time'=>time(),
						'finish_time'=>time()+(60*2), //2 minute
						'game_time_out'=>0,
						'result'=>-1
			]) ;

			
			
			 //create room and start match 
			$roomName = $match->user_id . '_'. $botName ; 

			
			$members = Room::getClients($roomName);
			foreach($members as $member)
			{
				Room::delete($member,$roomName);
			}

			Room::add($match->user->id,$roomName);
			
			
			echo 'Mached users '.$match->user_id." , ".$botName."\n";
			
			 
			$word = (new BotController())->createWord($game) ; 
			
			GameHistory::Create(
				[
					'game_id'=>$game->id,
					'user_id'=>0,
					'word'=>$word, 
					'score'=>0,
					'is_time_out'=>0
				]
				);
			
			Websocket::toUserId($match->user_id)->emit('startGame', [
				'room_name'=>$roomName , 
				'match_player2'=>['name'=>$botName,'avatar'=>'','startWord'=>$word]			
			] );
			
			
			//remove another requests
			
			GameRequest::query()
							->where('user_id',$match->user->id)
							->update([
								'is_done'=>1
							]);
							
			

			$user->update([
								'active_game_id'=>$game->id
							]);

			
			
			Websocket::toUserId($match->user->id)->emit('startRand', ['timeout'=>time()+15]);//

			
		
			return $game; 
			
			
		}

    }

    /**
     * Rejoin Game.
     *	
	 * @param Game $game 
	 * @param User $user 		
	 *
     * @return void
     */
    public function rejoin($game,  $user)
    {  
		if ($game->is_finish == 0  && ($game->finish_time<time()))
		{

			Websocket::loginUsingId($user->id);

			$roomName = $game->user_req  . '_'. $game->user_res ; 

			Room::add($user->id,$roomName);
			
			$history = GameHistory::query()
							->where('game_id',$game->id )
							->get()
							->toArray();
			
			Websocket::toUserId($user->id)->emit('rejoined',['history'=>$history]); 
			
			$player2 = ($game->user_req ==$user->id ? $game->user_res : $game->user_req ) ;
				  
			if ($game->with_bot == 0 )
			{
				Room::add($player2,$roomName);	  
				User::find($player2)->update(['active_game_id' => $game->id ]);
			}
				  
			//start game again
			if ($game->rand_user == $user->id )
			{
				Websocket::toUserId($user->id)->emit('startRand', ['timeout'=>time()+15]);
			}
			else 
			{
				//@todo if with bot  
				if ($game->with_bot == 0 )				
					Websocket::toUserId($player2)->emit('startRand', ['timeout'=>time()+15]);
			}
			
			
			
			$game->update([
				'is_wait'=>0,			
			]);
			
		}
		else 
		{
			
			$user->update(['active_game_id'=>0]); 
			
			$user2= null ; 
			
			if ($game->with_bot == 0 )
			{
				$player2 = ($game->user_req ==$user->id ? $game->user_res : $game->user_req ) ;
				$user2= User::find($player2); 
				$user2->update(['active_game_id'=>0]); 
			}
			
			
			$this->finishGame($game,$user,$user2 );
			
			
			Websocket::emit('connected', "you Are Connected" );
		}
			
    }

    /**
     * Timeout Game.
     *	
	 * @param Game $game 
	 * @param integer $timer 		
	 *
     * @return void
     */
    public function timeout($game , $timer)
    {
		echo 'timeout';
		if ($game->with_bot == 0 )
		{
			$user =User::query()->find($game->rand_user )  ; 
		
		 
			Websocket::toUserId($user->id )
							->emit( 'randTimeout',array(
													'err'=> 400 , 
													'msg'=>'time is finsih',
												)
							);
	 
			$player2 = ($game->user_req ==$user->id ? $game->user_res : $game->user_req ) ;
 
			Websocket::toUserId($player2)->emit('reciveWord', ['word'=>'','from'=>$user->full_name , 'score'=>0]);
			  
			GameHistory::addHistory($game ,$user,'', 0 ); 

			
			$game->update([
				'rand_time_out'=> time()+15,
				'rand_user'=>$player2,
				'timer_id'=>$timer
			]);
			
		}
		else 
		{
			//rand is always for user because bot never timeout 
			$word = (new BotController())->createWord($game) ; 
			$this->processWord($game , null , $word);
			
		}
		
		return ;
			
    }

    /**
     * ProcessWord in Game Rand.
     *	
	 * @param Game $game 
	 * @param User $user 		
	 * @param string $word 		
	 *
     * @return void
     */
    public function processWord($game,$user,$word)
    {
		
        $lastWord = GameHistory::query()->where('game_id' , $game->id)
							->where('is_time_out',0)
							->latest()
							->first(); 
      
        $score =5 ; 
		
        if ($lastWord && $lastWord != '' && mb_substr($lastWord->word,-1) != mb_substr($word,0,1) )
        {		
		
			if ($game->with_bot ==0 )  
			{
				
				Websocket::toUserId($user->id )
					->emit( 'rejectWord',['word'=> $word , 'reason'=>10,'message'=>'your word start not match with end of last words:'.$lastWord->word]); //[already use ]
				return;
			}
			else //rand for bot 
			{
				//lose bot 
				$score = 0 ; 
				$word = ''; //reject bot word
			}
        }  

        //a user already sent this words
        if(GameHistory::query()->where('game_id' , $game->id)->where('word', $word)->count() > 0 ) 
        {
            $score =2; 
        }
		
        $game->step++; 
        $game->save(); 
		
		if ($game->with_bot ==0 ) 
		{
			
			//tel user to acceptWord
			Websocket::toUserId($user->id )
                    ->emit( 'acceptWord',array(
                                            'word'=> $word , 
                                            'score'=>$score,
                                        )
                    );
			GameHistory::addHistory($game ,$user,$word, 10 ); 

			$player2 = ($game->user_req ==$user->id ? $game->user_res : $game->user_req ) ;
			Websocket::toUserId($player2)->emit('reciveWord', ['word'=>$word,'from'=>$user->full_name , 'score'=>$score ]);
			Websocket::toUserId($player2)->emit('startYourRand', ['timeout'=>time()+15000 ]);

        }
		else 
		{
			
			if($user ==null ) //bot send word
			{	
				GameHistory::Create(
					[
					'game_id'=>$game->id,
					'user_id'=>0,
					'word'=>$word, 
					'score'=>$score,
					'is_time_out'=>($word == '' ? 1 : 0)
					]
				);
							
				Websocket::toUserId($game->user_req)->emit('reciveWord', ['word'=>$word,'from'=>$game->bot_name , 'score'=> $score]);
				
				Websocket::toUserId($game->user_req)->emit('startYourRand', ['timeout'=>time()+15000 ]);

				
			}
			else 
			{
				//tel user to acceptWord
				Websocket::toUserId($user->id )
						->emit( 'acceptWord',array(
												'word'=> $word , 
												'score'=>$score,
											)
						);
				GameHistory::addHistory($game ,$user,$word, 10 ); 		

				Websocket::toUserId($user->id)->emit('reciveWord', ['word'=>$word,'from'=>$user->full_name , 'score'=> $score]);
				Websocket::toUserId($user->id)->emit('startYourRand', ['timeout'=>time()+15000 ]);

				echo 'request word from bot ';
				//rand is always for user because bot never timeout 
				$word = (new BotController())->createWord($game) ; 
				$this->processWord($game , null , $word);//recursive call
			}			
		}	
         
		
    }

    /**
     * FinishGame and Calculate Game Result.
     *	
	 * @param Game $game 
	 * @param User $user1 		
	 * @param User|Null $user2 		
	 *
     * @return void
     */
    public function finishGame($game,$user1,$user2)
    {
		$historyCount = GameHistory::query()
					->where('game_id',$game->id)
					->count(); 
					
		//if ($historyCount % 2 == 0 )
		//{

			 //calculate result 
			$player1Score = GameHistory::query()
								->where('game_id',$game->id)
								->groupBy('user_id',$user1->id )
								->sum('score'); 

			$player2Id  =($game->with_bot ==1 ? 0 : $user2->id);

			$player2Score = GameHistory::query()
								->where('game_id',$game->id)
								->groupBy('user_id',$player2Id )
								->sum('score'); 
			
			$result = 0 ; //two player is equal 
			if ($player1Score > $player2Score )
			{
				$result = 1 ;

				// 'rank'=-4,'coin_count'=>10 , 'xp' => 4  
				if($game->with_bot ==0 )
				{
					User::addScore($user2,10,-4,4);
					websocket::to($user2->id)->emit('gameFinish', ['result'=>'lose','rank'=>$user2->rank-4,'xp'=>$user2->xp+4,'coin_count'=>$user2->coin_count+10]);
				}
				// 'rank'=5,'coin_count'=>100 , 'xp' => 10  
				User::addScore($user1,100,5,10);
				
				
				websocket::to($user1->id)->emit('gameFinish', ['result'=>'win','rank'=>$user1->rank+5,'xp'=>$user1->xp+10,'coin_count'=>$user1->coin_count+100]);
				
			} 
			else if ($player2Score > $player1Score)  
			{
				$result = 2 ;    

				// 'rank'=-4,'coin_count'=>10 , 'xp' => 4  
				User::addScore($user1,10,-4,4);

				
				
				if($game->with_bot ==0 )
				{
					// 'rank'=5,'coin_count'=>100 , 'xp' => 10  
					User::addScore($user2,100,5,10);
					
					websocket::to($user2->id)->emit('gameFinish', ['result'=>'win','rank'=>$user2->rank+5,'xp'=>$user2->xp+10,'coin_count'=>$user2->coin_count+100]);
				}
				websocket::to($user1->id)->emit('gameFinish', ['result'=>'lose','rank'=>$user1->rank-4,'xp'=>$user1->xp+4,'coin_count'=>$user1->coin_count+10]);
				


			}
			else 
			{
				$result =0 ; 


				// 'rank'=1,'coin_count'=>40 , 'xp' => 5  
				User::addScore($user1,40,1,5);

				
				if($game->with_bot ==0 )
				{
					// 'rank'=1,'coin_count'=>40 , 'xp' => 5  
					User::addScore($user2,40,1,5);
					
					websocket::to($user2->id)->emit('gameFinish', ['result'=>'equal','rank'=>$user2->rank+1,'xp'=>$user2->xp+5,'coin_count'=>$user2->coin_count+40]);

				}
				websocket::to($user1->id)->emit('gameFinish', ['result'=>'equal','rank'=>$user1->rank+1,'xp'=>$user1->xp+5,'coin_count'=>$user1->coin_count+40]);
				

			}
			
			$user1->active_game_id=0;
			$user1->save();
			
			if($game->with_bot ==0 )
			{
				$user2->active_game_id=0;
				$user2->save();
			}
			
			$game->Update(['game_time_out'=>1 , 'is_finish'=>1,'result'=>$result]); 
		   
		//}
		//else 
		//{
	//		$game->Update(['game_time_out'=>1 ]); 
			//time finish but game is continue 			
	//	}            
			
			
        
    }

  
}
