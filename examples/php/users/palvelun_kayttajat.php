<?php
/* #########################################
   PHP esimerkki miten käyttäjänimet 
   luetaan. Sekä miten lisenssitiedot
   luetaan. 

   Jarkko Moilanen
   23.7.2013
   Lisenssi: http://creativecommons.org/licenses/by-sa/3.0/

*/


$json_url = "http://api.avoindata.net/users";
$json = file_get_contents($json_url);

$obj = json_decode($json);
$users = $obj->{"users"};

foreach ($users as $user) {
  echo "\n".$user->userid.":";
  echo $user->handle.":";
}

echo "\n RIGHTS: \n";
$rights = $obj->{"rights"};

echo $rights[0]->contentLicense; 
echo $rights[0]->dataLicense; 
echo $rights[0]->copyrightNotice; 
echo $rights[0]->attributionText;
echo $rights[0]->attributionURL;  
?>
