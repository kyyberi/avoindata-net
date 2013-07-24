<?php
/* #########################################
   PHP esimerkki API:n palauttaman headerin 
   'OK' tilan tarkistamiseksi. 

   Jarkko Moilanen
   24.7.2013
   Lisenssi: http://creativecommons.org/licenses/by-sa/3.0/

*/

$get1 = 'http://api.avoindata.net/license';
$get2 = 'http://api.avoindata.net/fails';

function get_http_response_code($domain) {
  $headers = get_headers($domain);
  return substr($headers[0], 9, 3);
}

$http_response_code = get_http_response_code($get1);

if ( $http_response_code == 200 ) {
  echo "OKAY!\n";
} else {
  echo "Nokay!\n";
}


$http_response_code = get_http_response_code($get2);

if ( $http_response_code == 200 ) {
  echo "OKAY!\n";
} else {
  echo "Nokay!\n";
}


?>


