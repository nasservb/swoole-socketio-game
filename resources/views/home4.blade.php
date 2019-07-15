@extends('layouts.app')

@section('content') 

<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.1.1/socket.io.js"></script>
        <script>
        var token='$2y$10$FOFDCTK/aRItDPgCNDwXz.6DjPcyaiFQEO13KrORjngvTg475a0s6';

            var socket  = io.connect('ws://185.116.163.236:1215',{
                    transports:['websocket'],
                    query: {token: token}}); 
           
            
       </script> 
        <script>

            socket.on('acceptWord',function (data){
                console.log(data);
            }); 

           

            socket.on('rejectWord',function (data){
                console.log(data);
            }); 



            socket.on('message',function (data){
                console.log(data);
            });
			
			socket.on('connected',function (data){
                console.log(data);
            });
			
			socket.on('error',function (data){
                console.log(data);
            });
								
			socket.on('randTimeout',function (data){
                console.log(data);
            });		
		 					
			socket.on('searching',function (data){
                console.log(data);
            });	
				
			socket.on('startGame',function (data){
                console.log(data);
            });	
									
			socket.on('startRand',function (data){
                console.log(data);
            });	

			socket.on('rejoin_error',function (data){
                console.log(data);
            });
												
			socket.on('reciveWord',function (data){
                console.log(data);
            });
			
            function request()
            {             
                socket.emit('requestMatch',{token: token }); 
            }			


            function send(){
                socket.emit('sendWord',{word: document.getElementById('txt').value,token:token }); 
            } 



            function rejoin(){
                socket.emit('rejoin',{word: document.getElementById('txt').value,token:token }); 
            } 

        </script>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

       <br>             You are logged in!<br>

       request :  
                    <a href="javascript:void(0)" onclick="request()">request match</a><br> 


       rejoin:      <a href="javascript:void(0)" onclick="rejoin()">rejoin</a><br> 
       <hr> 



  text: 
                    <input type="text" id="txt" />
                    <a href="javascript:void(0)" onclick="send()">send</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
