

<?php
/* #########################################
   PHP esimerkki 

   Jarkko Moilanen
   11.8.2013
   Lisenssi: http://creativecommons.org/licenses/by-sa/3.0/

*/
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
</head>
<body>
<div>
<div style="width:100px;font-weight:bold;float:left;">Kannatus</div>
<div style="float:left;font-weight:bold;">Otsikko/Url</div>
</div>
<?php
$json_url = "https://www.kansalaisaloite.fi/api/v1/initiatives";
$json = file_get_contents($json_url);

$json_obj = json_decode($json);

	foreach ($json_obj as $obj) {
  		$tila 	= $obj->state;
		$url  	= $obj->id;
		$humanurl = str_replace("api/v1/initiatives", "fi/aloite", $url);
		$otsikko_fi = $obj->name->fi;
		$kannatus = $obj->supportCount;


		echo "<div style='clear:both;'>";
		echo "<div style='width:100px;display:block;float:left;'>".$kannatus."</div> - <div style='float:left;'><a href='".$humanurl."'>".$otsikko_fi."</a></div>";

		echo "</div>";
	}

?>
</body>
</html>
