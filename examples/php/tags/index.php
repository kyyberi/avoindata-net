<?php
/* #########################################
   PHP esimerkki miten tagien nimet ja niihin
   liittyvien kysymysten määrät luetaan

   Jarkko Moilanen
   11.7.2013
   Lisenssi: http://creativecommons.org/licenses/by-sa/3.0/

*/


$json_url = "http://api.avoindata.net/tags";
$json = file_get_contents($json_url);

$obj = json_decode($json);
$tags = $obj->{"tags"};

foreach ($tags as $tag) {
  echo $tag->title;
  echo $tag->count;
}

?>
