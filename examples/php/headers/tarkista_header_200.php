<?php
/* #########################################
   PHP esimerkki API:n palauttaman headerin 
   'OK' tilan tarkistamiseksi. 

   Jarkko Moilanen
   24.7.2013
   Lisenssi: http://creativecommons.org/licenses/by-sa/3.0/

*/

$domain1 = 'http://api.avoindata.net/license';
$domain2 = 'http://api.avoindata.net/fails';

function get_http_response_code($domain) {
  $headers = get_headers($domain);
  return substr($headers[0], 9, 3);
}

$http_response_code = get_http_response_code($domain1);

if ( $http_response_code == 200 ) {
  echo "OKAY!\n";
} else {
  echo "Nokay!\n";
}


$http_response_code = get_http_response_code($domain2);

if ( $http_response_code == 200 ) {
  echo "OKAY!\n";
} else {
  echo "Nokay!\n";
}


?>


