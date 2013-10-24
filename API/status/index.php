<?php
$mainurl = "http://api.avoindata.net";
$urls = array(
	"/categories",
	"/categories/id/27?limit=5",
	"/questions/2013/5",
	"/categories/id/27",
	"/questions",
	"/questions/count",
	"/questions/month/4",
	"/answers",
	"/tags",
	"/tags/title/Tampere",
	"/tags/id/25",
	"/users",
	"/users/2",
	"/users/id/2/questions",
        "/users/id/2/answers",
	"/users/questions",
	"/users/answers",
	"/license"
);

?>
<html>
<head>
<style>
#resultsdiv {
	width:350px;
	min-height:800px;
}
.result{
	border-bottom:solid 1px #ddd;
	clear:both;
	min-height:32px;
}
.result img{
	float:right;
	width:30px;
}
.result span img{
	
}

body {
	font-size:0.9em;
}
h1,h3 {
	font-size:1em;
}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

	
	<?php
	$tests = new Tests();
	$tests->header();
	$tests->doTests($mainurl, $urls);
	$tests->footer();
	?>
	

<?php

class Tests {

	public function sendEmail($api){
		$message = "API: ".$api." failed\r\n";
		mail( 'jarkko.moilanen@uta.fi', "Avoindata.net API fail", $message, "From: jarkko.moilanen@hermia.fi" );
	}

	public function header(){
		echo "<body>";
		echo "<h1>Avoindata.net API (api.avoindata.net) metodien tila</h2>";
		$mdate = date("d.m.Y H:i:s");
		echo "<h3>Päiväys: ".$mdate."</h3>";
		echo "<div id='resultsdiv'>";
		
	}

	public function footer(){
		echo "</div></body></html>";
	}	

	public function doTests($mainurl, $urls){
	$fails = 0;
	$counter = 0;
	$testcount = sizeof($urls);
	
		foreach ($urls as $url) {
			$counter++;
			$status = Tests::checkMethod($url, $mainurl);
			if($status){
				echo "<div class='result'>".$counter.". <a href='".$mainurl.$url."'>".$url." </a><img src='http://avoindata.net/images/green.png'/></div>\n";
				//Tests::sendEmail($url);
		
			}else{
				echo "<div class='result'>".$counter.". ".$url." <img src='http://avoindata.net/images/red.png'/></div>\n";
				$fails++;
			}

		} 
	
	echo "<p>Failed test count: ".$fails."/".$testcount."</p>";
	}

	function getData($url){
		$data = json_decode(file_get_contents($url));
		return $data;
	}

	function checkMethod($url,$mainurl){
		$data = Tests::getData($mainurl.$url);
		if($data === null) {
	   		return false;
	    	}else{
		
			return true;
		
		}
	}

}


?>
