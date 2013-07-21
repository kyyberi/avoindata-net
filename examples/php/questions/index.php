<?php
/* #########################################
   PHP esimerkki miten kuluvan vuoden
   yhden kuukauden kysymykset luetaan 

   Jarkko Moilanen
   21.7.2013
   Lisenssi: http://creativecommons.org/licenses/by-sa/3.0/

*/


$json_url = "http://api.avoindata.net/questions/month/4";
$json = file_get_contents($json_url);

$obj = json_decode($json);

if($questions = $obj->{"questions"}){
	foreach ($questions as $question) {
  		echo "\n".$question->title;
		echo "\n".$question->id;
		$date = $question->created;
		echo "\n".gmdate('d.m.Y', $date);
		$tags = $question->tags;
		foreach ($tags as $tag) {
			echo "\n".$tag;
		}
	}
}

?>
