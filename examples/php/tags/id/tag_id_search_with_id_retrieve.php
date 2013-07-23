<?php
/* #########################################
   PHP esimerkki miten käsitellään yhden 
   tagin kysymykset jotka on haettu ID:llä.

   Jarkko Moilanen
   13.7.2013
   Lisenssi: http://creativecommons.org/licenses/by-sa/3.0/

*/

// hae id listaus ja valitse haluttu
$json_url = "http://api.avoindata.net/tags";
$json = file_get_contents($json_url);
$obj = json_decode($json);

$tags = $obj->{"tags"};
$myid = $tags[1]->wordid; // esimerkissä otetaan toisen objektin id

// hae kyseisen id:n kysymykset
$json_url = "http://api.avoindata.net/tags/id/".$myid;
$json = file_get_contents($json_url);
$obj = json_decode($json);
$tags = $obj->{$myid};

foreach ($tags as $tag) {
  echo $tag->title;
  echo $tag->updated;
}

?>
