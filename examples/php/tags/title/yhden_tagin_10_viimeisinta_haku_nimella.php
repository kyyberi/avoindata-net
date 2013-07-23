<?php
/* #########################################
   PHP esimerkki miten yhden tagien 10
   viimeisimmän kysymyksen tiedot

   Jarkko Moilanen
   23.7.2013
   Lisenssi: http://creativecommons.org/licenses/by-sa/3.0/

*/

$stitle = "Tampere";
$json_url = "http://api.avoindata.net/tags/title/".$stitle;
$json = file_get_contents($json_url);

$obj = json_decode($json);
$questions = $obj->{$stitle};

foreach ($questions as $question) {
  echo "<a href='".$question->url."'>".$question->title."</a>\n";
}

?>
