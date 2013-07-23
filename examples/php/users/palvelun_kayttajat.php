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
$arr = json_decode($json, true);

echo $arr['rights']['contentLicense']; 
echo $arr['rights']['dataLicense']; 
echo $arr['rights']['copyrightNotice']; 
echo $arr['rights']['attributionText'];
echo $arr['rights']['attributionURL'];  
?>
