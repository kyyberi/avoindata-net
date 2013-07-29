<?php
/* #########################################
   PHP esimerkki miten vastausmäärät päivän
   tarkkuudella luetaan

   Jarkko Moilanen
   23.7.2013
   Lisenssi: http://creativecommons.org/licenses/by-sa/3.0/

*/


$json_url = "http://api.avoindata.net/answers/count";
$json = file_get_contents($json_url);

$obj = json_decode($json);
$answers = $obj->{"answers"};

foreach ($answers as $day) {
  echo "\n".$day->date.":";
  echo $day->count;
}

/* käänteisessä järjestyksessä  */
$reversed = array_reverse($answers);
echo "\nJa sama käänteisessä järjestyksessä\n";

foreach ($reversed as $day) {
  echo "\n".$day->date.":";
  echo $day->count;
}

?>
