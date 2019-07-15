<?php

require_once __DIR__ . "/vendor/autoload.php";

function isValid($word)
{
	$collection = (new MongoDB\Client)->db_game->words;

	$search =  $collection->find(['word'=> $word]); 
	
	return  ( iterator_count ($search  ) >0 ? true : false  ); 

}