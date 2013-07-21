<?php
/* #########################################
   PHP esimerkki miten kategorioiden nimet 
   ja niihin liittyvien kysymysten määrät 
   luetaan

   Jarkko Moilanen
   11.7.2013
   Lisenssi: http://creativecommons.org/licenses/by-sa/3.0/

*/


$json_url = "http://api.avoindata.net/categories";
$json = file_get_contents($json_url);

$obj = json_decode($json);
$categories = $obj->{"categories"};

foreach ($categories as $category) {
  echo $category->title;
  echo $category->count;
}

?>
