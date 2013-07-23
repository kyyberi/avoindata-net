<?php
/* #########################################
   PHP esimerkki miten käyttäjänimet ja 
   kysymysmäärät luetaan.

   Jarkko Moilanen
   23.7.2013
   Lisenssi: http://creativecommons.org/licenses/by-sa/3.0/

*/


$json_url = "http://api.avoindata.net/users/questions";
$json = file_get_contents($json_url);

$obj = json_decode($json);
$users = $obj->{"users"};

foreach ($users as $user) {
  echo "\n".$user->userid.":";
  echo $user->handle.":";
  echo $user->question_count;
}

?>
