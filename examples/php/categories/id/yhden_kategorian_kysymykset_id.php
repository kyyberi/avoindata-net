<?php
/* #########################################
   PHP esimerkki miten yhden kategorian 
   kysymykset.

   Jarkko Moilanen
   21.7.2013
   Lisenssi: http://creativecommons.org/licenses/by-sa/3.0/

*/


$json_url = "http://api.avoindata.net/categories/id/11";
$json = file_get_contents($json_url);

$obj = json_decode($json);
if($questions = $obj->{"questions"}){

	foreach ($questions as $question) {
  		echo $question->title."\n";
  		echo $question->postid."\n";
		echo $question->url."\n";
		echo $question->created."\n";
		$tags = $question->tags;
		foreach ($tags as $tag) {
			echo "tag: ".$tag."\n";
		}
	}
}
?>
