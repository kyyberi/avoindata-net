<?php
require 'phpmailer/class.phpmailer.php';

include_once 'epi/Epi.php';
Epi::setPath('base', 'epi');
Epi::setSetting('exceptions', true);
Epi::init('route');
Epi::init('api');
Epi::init('base','cache','session');
//Epi::init('base','cache-apc','session-apc');
//Epi::init('base','cache-memcached','session-apc');

/*
 * This is a sample page whch uses EpiCode.
 * There is a .htaccess file which uses mod_rewrite to redirect all requests to index.php while preserving GET parameters.
 * The $_['routes'] array defines all uris which are handled by EpiCode.
 * EpiCode traverses back along the path until it finds a matching page.
 *  i.e. If the uri is /foo/bar and only 'foo' is defined then it will execute that route's action.
 * It is highly recommended to define a default route of '' for the home page or root of the site (yoursite.com/).
 */
//$router = new EpiRoute();

// GET METHODS
getRoute()->get('/', array('Api', 'MyMethod'));
getRoute()->get('/tags', array('Api', 'Tags'));
getRoute()->get('/tags/title/([\w\s%-]+)', array('Api', 'OneTag'));
getRoute()->get('/tags/id/(\d+)', array('Api', 'OneTagId'));
getRoute()->get('/categories', array('Api', 'Categories'));
getRoute()->get('/categories/id/(\d+)', array('Api', 'CategoryId'));
getRoute()->get('/answers/count', array('Api', 'AnswersCount'));
getRoute()->get('/users', array('Api', 'Users'));
getRoute()->get('/users/id/(\d+)', array('Api', 'UserDetails'));
getRoute()->get('/questions', array('Api', 'Questions'));
getRoute()->get('/questions/id/(\d+)', array('Api', 'OneQuestion'));
getRoute()->get('/questions/count', array('Api', 'QuestionsCount'));
getRoute()->get('/questions/year/(\d+)', array('Api', 'QuestionsYear'));
getRoute()->get('/questions/(\d+)/(\d+)', array('Api', 'QuestionsYearMonth'));
getRoute()->get('/questions/month/(\d+)', array('Api', 'QuestionsMonth'));
getRoute()->get('/users/questions', array('Api', 'getUserPostCounts'));
getRoute()->get('/users/id/(\d+)/questions', array('Api', 'QuestionsId'));
getRoute()->get('/users/id/(\d+)/answers', array('Api', 'AnswersId'));
getRoute()->get('/users/answers', array('Api', 'getUserAnswerCounts'));
getRoute()->get('/version', array('Api', 'showVersion'));
getRoute()->get('/questions', array('Api', 'Questions'));
getApi()->get('/version.json', array('Api', 'version'), EpiApi::external);
getRoute()->get('/api/stats', array('Api', 'ApiStats'));
getRoute()->get('/api/stats/daily', array('Api', 'ApiStatsDaily'));
getRoute()->get('/license', array('Api', 'showRights'));
getRoute()->get('/api/auth/remove/verify/(.*)', array('Api', 'apiRemoveVerify'));
getRoute()->get('/avatar/(.*)', array('Api', 'getGravatar'));
getRoute()->get('/api/auth/remove', array('Api', 'apiRemoveDomain'));

// POST METHODS
getRoute()->post('/search/questions', array('Api', 'apiSearchQuestions'));
getRoute()->post('/api/auth/new', array('Api', 'apiAuthNew'));


getRoute()->get('.*', array('ApiErrors', 'error404'));

getRoute()->run(); 


/*
 * ******************************************************************************************
 * Define functions and classes which are executed by EpiCode based on the $_['routes'] array
 * ******************************************************************************************
 */
class Api
{

 

  public function apiRemoveVerify($key) {
	$removekey = trim($key);
	$con = Api::getConnection();
		if (mysqli_connect_errno($con))
		{
			$errno = mysqli_connect_errno();
			$reason = mysqli_connect_error();
			ApiErrors::errorDbConnection($reason, $errno);

		}else{
			// hae key 
			$domains = mysqli_query($con,"SELECT api_id, api_domain, remove_key, api_email FROM api_domain WHERE remove_key='".$removekey."';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
			$domain_count = mysqli_num_rows($domains);
			echo $domain_count."\n\n";
			if($domain_count == 1){
				while ($row = mysqli_fetch_array($domains)):  
					$toremoveid = $row['api_id'];
					$toremovekey = $row['remove_key'];
					$toremoveemail = $row['api_email'];
					$toremovedomain = $row['api_domain'];
					// poista
					if($toremovekey == $removekey){
						$domains = mysqli_query($con,"DELETE FROM api_domain WHERE remove_key='".$removekey."';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
						echo "Removed - id: ".$toremoveid.", email: ".$toremoveemail;
						//$sent = api::sendApiRemoveEmail($roremoveemail, $toremovedomain);	
					}else{
					
					}
				endwhile;
			}else{
				echo "domain to remove not found";
			}
		}	
 }

  public function apiAuthNew() {

	$ret = "";
	$bot = false;
  	$loadtime = trim($_POST['loadtime']);
	$uemail = trim($_POST['email']);
	//$uemail = "email";
	$udomain = trim($_POST['domain']);
	//$udomain = "domain";
	$stime = time();
	$totaltime = $stime - $loadtime;
	/*
	if( $totaltime < 7 )
	{
   		
		$bot = true;
   		
	}
        
	$spam = $_POST['street'];
	if( strlen($spam) > 0 )
	{
		$bot = true;
	}
	*/
	if( $bot ){
		$tiedot = "{ \"apiauth\": [\n";
		$tiedot .= "    {\"domain\":\"bot\",";
		$tiedot .= "    \"email\":\"bot\",";
		$cout = substr($tiedot, 0, strlen($tiedot) -1);
		$cout .="}],\n";
	 	// lisää rights osuus
		$rights = Api::getRights();
		$cout .= $rights;
		$cout .= "\n}";
		// palauta JSON headerilla
		Api::outputJSON($cout); 
	}else{
	
		// generoi token
		$newtoken = api::token(20);
		$usertoken = api::token(20);
		// generoi poisto avain
		$removetoken = api::token(40);
		$con = Api::getConnection();
		if (mysqli_connect_errno($con))
		{
			$errno = mysqli_connect_errno();
			$reason = mysqli_connect_error();
			ApiErrors::errorDbConnection($reason, $errno);

		}else{

			// tarkista ettei domain ole jo 
			$domains = mysqli_query($con,"SELECT api_domain, register_date FROM api_domain WHERE api_domain='".$udomain."';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
			$domain_count = mysqli_num_rows($domains);
			while ($row = mysqli_fetch_array($domains)):  
				$regdate = $row['register_date'];
			endwhile;
			if($domain_count < 1){
				// lisää kantaan
				$sql = "INSERT INTO api_domain (api_domain, api_secret, register_date, user_token, api_email, remove_key) VALUES ('".$udomain."', '".$newtoken."', CURDATE() ,'".$usertoken."', '".$uemail."','".$removetoken."');";
				$tags = mysqli_query($con,$sql) or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));
				// hae nyt tiedot db:stä
				$newdomain = mysqli_query($con,"SELECT * FROM api_domain WHERE api_domain='".$udomain."';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
				while ($row = mysqli_fetch_array($newdomain)):  
			
					$udomain = $row['api_domain'];
					$uemail = $row['api_email'];
					$regdate = $row['register_date'];
					$removekey = $row['remove_key'];
					$secret = $row['api_secret'];
					$usertoken = $row['user_token'];
				endwhile;
				
			}else{
				$udomain = "exists";
			}

		}
		$con->close();

		$tiedot = "{ \"apiauth\": [\n";
		$tiedot .= "    {\"domain\":\"".$udomain."\",";
		$tiedot .= "    \"email\":\"".$uemail."\",";
		$tiedot .= "    \"regdate\":\"".$regdate."\",";
		
		
		if($domain_count < 1){
			$sent = api::sendApiDetails($uemail, $udomain, $removekey, $secret, $usertoken);	
		}
	
		$cout = substr($tiedot, 0, strlen($tiedot) -1);
		$cout .="}],\n";
	 	// lisää rights osuus
		$rights = Api::getRights();
		$cout .= $rights;
		$cout .= "\n}";
		// palauta JSON headerilla
		Api::outputJSON($cout);
		
	}
  }
  
public function sendApiDetails($uemail, $domain, $removekey, $secret, $usertoken){

	$subject = utf8_decode('Domain ('.$domain.') on rekisteröity onnistuneesti http://avoindata.net API:n (http://api.avoindata.net) tietokantaan.'); 
	$content = "Ohjeet miten lähetät kysymyksen avoindata.net palveluun API:n kautta löytyy alta.\n\r";
	$content .= "\n\r\n\r";
	$content .= "Käyttäjätunnus: ".$usertoken."\n\r";
	$content .= "Salainen token: ".$secret."\n\r";
	$content .= "\n\r\n\r";
	$content .= "--- DOMAININ POISTO REKISTERISTÄ ---\n\r";
	$content .= "Poista domain:" .$domain." avoindata.net API:n rekisteristä avaamalla alla oleva URL:\n\r";
	$content .= "http://api.avoindata.net/api/auth/remove/verify/".$removekey."\n\r";
	$content .= "HUOM! Poistamisen jälkeen et enää voi käyttää tunnuksia/API:n tiettyjä metodeja. \n\r";
	$content .= utf8_decode($content);
	$mail = new PHPMailer;

	$mail->IsSMTP();                                      // Set mailer to use SMTP
	$mail->Host = 'mail.avoindata.net';  // Specify main and backup server
	$mail->SMTPAuth = true;                               // Enable SMTP authentication
	$mail->Username = 'api@avoindata.net';                            // SMTP username
	$mail->Password = 'Zia5dekk';                           // SMTP password
	$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted

	$mail->From = 'api@avoindata.net';
	$mail->FromName = 'Avoindata API [no reply]';
	
	$mail->AddAddress($uemail);               // Name is optional
	
	$mail->AddCC('jarkko.moilanen@hermia.fi');
	
	$mail->WordWrap = 50;                                 // Set word wrap to 50 characters

	// $mail->IsHTML(true);                                  // Set email format to HTML

	$mail->Subject = $subject;
	$mail->Body    = $content;
	//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

	if(!$mail->Send()) {
	   //echo 'Message could not be sent.';
	   //echo 'Mailer Error: ' . $mail->ErrorInfo;
		return false;
	   
	}else{
		return true;	
	}
	
 
  }

public function token($length) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789';
    return substr(str_shuffle($characters), 0, $length);
}

  static public function apiSearchQuestions() {
	$needle = trim($_POST['term']);
	$needle = strip_tags($needle);
	$needle = strtolower($needle);
	$needle_arr = explode(" ", $needle);
	$tiedot = "{ \"questions\": [\n";
	$arr = array();
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
	{
		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);

	}else{
		$sql = "SELECT title, userid, postid, content, acount, views, tags, netvotes, UNIX_TIMESTAMP(updated) as updated, UNIX_TIMESTAMP(created) as created FROM qa_posts 
                WHERE type='Q' 
		AND NOT(type='Q_HIDDEN')
		AND NOT(type='A')
		ORDER BY created DESC;";
		$tags = mysqli_query($con,$sql) or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));
		
		$num_rows = mysqli_num_rows($tags);
		while ($row = mysqli_fetch_array($tags)):  
			$userid = $row['userid'];
						
			$userdetails = mysqli_query($con,"select * FROM qa_users WHERE userid = '".$userid."';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
			while ($row2 = mysqli_fetch_array($userdetails)):
				$userhandle = $row2['handle'];
				$avatarblob = $row2['avatarblobid'];
				if($avatarblob != NULL){
					$blob = "http://avoindata.net/?qa=image&qa_blobid=".$avatarblob;
				}else{
					$uhash = md5( strtolower( trim( $row2['email'] ) ) );
					$blob = "http://api.avoindata.net/avatar/".$uhash;
				}


			endwhile;
			$str = $row['title'];
			$title = utf8_encode($str);
			$rawtitle = $title;
			$mytags = $row['tags'];
			$title = api::myreplace($title);
			$acount    =   $row['acount'];
			$content    =   $row['content'];
			$postid    =   $row['postid'];
			$vcount    =   $row['views'];
			$votes    =   $row['netvotes'];
			$created    =   $row['created'];
			//$created = strtotime('+1 month',$created);
			$normalized_title = api::normalize_str($title);
			$url = "http://avoindata.net/".$postid."/";
			$url .= strtolower($normalized_title);

			$mytags_arr = explode(",",$row['tags']);
			if(sizeof($mytags_arr) > 1){
				$tagsout = "\"tags\": [";
				foreach ($mytags_arr as $mytag) {
					$temp = utf8_encode($mytag);
					$tagsout .= '"'.$temp.'",';
				}
				$tagsout = substr($tagsout, 0, strlen($tagsout) -1);
	  			$tagsout .= "]";
  			}else{
				$tagsout = "\"tags\" : [\"NULL\"]";
			} 
			if(strlen($row['updated']) < 6){
					$updated    = 0;
				}else{
					$updated    =   $row['updated'];
			//		$updated = strtotime('+1 month',$updated);
				}
			if(strlen($title) > 4){
				$heystack = "";
				$heystack .= $rawtitle." ";
				$heystack .= $content." ";
				$heystack .= $tagsout;
		
				if(Api::contains($heystack, $needle_arr)){ 
				$found = true;
					$tiedot .= "    {\"title\":\"".$title."\",";
					$tiedot .= "\n     \"id\": ".$postid.",";
					if (is_numeric($userid)) {
						$tiedot .= "\n     \"userid\": ".$userid.",";
		    			} else {
						$tiedot .= "\n     \"userid\": \"".$userid."\",";
		    			}
					$tiedot .= "\n     \"userhandle\": \"".$userhandle."\",";
					$tiedot .= "\n     \"useravatar\": \"".$blob."\",";
					$tiedot .= "\n     \"viewcount\": ".$vcount.",";
					$tiedot .= "\n     \"votes\": ".$votes.",";
					$tiedot .= "\n     \"created\": ".$created.",";
					$tiedot .= "\n     \"updated\": ".$updated.",";
					$tiedot .= "\n     \"answercount\": ".$acount.",";
					$tiedot .= "\n     \"url\": \"".$url."\",";
					$tiedot .= "\n     ".$tagsout."},\n";
				}   
			} 
		endwhile; 
		if($found){
			$cout = substr($tiedot, 0, strlen($tiedot) -2);
			$cout .="],\n";
		 	// lisää rights osuus
			$rights = Api::getRights();
			$cout .= $rights;
			$cout .= "\n}";
			// palauta JSON headerilla
			Api::outputJSON($cout);

		}else{
			ApiErrors::errorEmpty();
		}

		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();

	}	

  }

  public function contains($str, $arr)
  {
    foreach($arr as $a) {
        if (stripos($str,$a) !== false) return true;
    }
    return false;
  }

  public function showVersion()
  {
    echo 'The version of this api is: ' . getApi()->invoke('/version.json');
  }

  public function version() {
    return '1.0';
  }

  static public function MyMethod()
  {
    echo '<h1>Avoindata.net API kuvaus ja esimerkit</h1>
	  <p>L&ouml;ytyy osoitteesta: <a href="http://avoindata.net/dashboard/api/v1/">http://avoindata.net/dashboard/api/v1/</a></p>';
  }


static public function ApiStats()
  {
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
  	{
 		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);
 	} else {

	    	$tiedot = "{ \"statistics\": [\n";
		$title = "";
		$acount = "";
		$tags = mysqli_query($con,"SELECT api_id, api_method, api_name, api_user_agent, api_ip_address, UNIX_TIMESTAMP(api_timestamp) as api_timestamp FROM api ORDER BY api_timestamp DESC;") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
	 	$num_rows = mysqli_num_rows($tags);
		while ($row = mysqli_fetch_array($tags)):  
			$id    =   $row['api_id'];
			$method    =   $row['api_method'];
			$name    =   $row['api_name'];
			$timestamp    =   $row['api_timestamp'];
			$referer    =   $row['api_referer'];
			$ip    =   $row['api_ip_address'];
			$useragent    =   $row['api_user_agent'];

			$tiedot .= "    {\"id\":".$id.",";
			$tiedot .= "\n     \"method\": \"".$method."\",";
			$tiedot .= "\n     \"name\": \"".$name."\",";
			$tiedot .= "\n     \"referer\": \"".$referer."\",";
			$tiedot .= "\n     \"request_ip_address\": \"".$ip."\",";
			$tiedot .= "\n     \"user_agent\": \"".$useragent."\",";
			$tiedot .= "\n     \"timestamp\": ".$timestamp."\n    },\n";
		endwhile; 
		$cout = substr($tiedot, 0, strlen($tiedot) -2);
		$cout .="],\n";
	 	// lisää rights osuus
		$rights = Api::getRights();
		$cout .= $rights;
		$cout .= "\n}";
		// palauta JSON headerilla
		Api::outputJSON($cout);



		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();
	}
  
  }

static public function ApiStatsDaily()
  {

	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
  	{
 		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);
 	} else {

	    	$tiedot = "{ \"daily\": [\n";
		$title = "";
		$acount = "";
		$arr = array();

		$postdates = mysqli_query($con,"SELECT DISTINCT DATE(api_timestamp) as api_timestamp, count(api_id) as lkm FROM api group by DATE(api_timestamp) ORDER BY api_timestamp;") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
		
		
	 	while ($row = mysqli_fetch_array($postdates)):
			$arr[$row['api_timestamp']] = $row['lkm'];
			//echo $row['api_timestamp']."\n";
		endwhile;
		
		
		$alldates = array();
		// Start date
		$date = key($arr);
		// End date
		$end_date = date('Y-m-d');
	 	//echo $end_date;
		while (strtotime($date) <= strtotime($end_date)) {
			array_push($alldates, $date);
			$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
			//echo $date;
		}

	
		foreach ($alldates as $val)
		{
			if (array_key_exists($val, $arr)) {
	    			$tiedot .= "    {\"".$val."\" : ".$arr[$val]."},\n";
			}else{
				$tiedot .= "0,";
			}
		}
		$cout = substr($tiedot, 0, strlen($tiedot) -2);
		$cout .="],\n";
	 	// lisää rights osuus
		$rights = Api::getRights();
		$cout .= $rights;
		$cout .= "\n}";
		// palauta JSON headerilla
		Api::outputJSON($cout);

		
		$con->close();
     
	}
  }
  



  static public function AnswersCount(){

        $tiedot = "{ \"answers\": [\n";
	$arr = array();
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
	{
		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);

	}else{

		$postdates = mysqli_query($con,"select DISTINCT DATE(created) as cdate, count(postid) as lkm from qa_posts where type='A' and NOT(type='A_HIDDEN')  group by DATE(created) ORDER BY created;") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));
		while ($row = mysqli_fetch_array($postdates)):
			$arr[$row['cdate']] = $row['lkm'];
		
		endwhile;

		$alldates = array();
		// Start date
		$date = key($arr);
		// End date
		$end_date = date('Y-m-d');
	 
		while (strtotime($date) <= strtotime($end_date)) {
			array_push($alldates, $date);
			$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
		}

	
		foreach ($alldates as $val)
		{
			if (array_key_exists($val, $arr)) {
	    			
				$tiedot .= "    {\"count\": ".$arr[$val].",\n     \"date\": \"".$val."\"\n     },\n";
				
			}else{
				
				$tiedot .= "    {\"count\": 0,\n     \"date\": \"".$val."\"\n     },\n";
			}
		}
		$cout = substr($tiedot, 0, strlen($tiedot) -2);
		$cout .="],\n";
	 	// lisää rights osuus
		$rights = Api::getRights();
		$cout .= $rights;
		$cout .= "\n}";
		// palauta JSON headerilla
		Api::outputJSON($cout);

		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();
	}	

  }

  static public function getUserPostCounts()
  {
	$tiedot = "{ \"users\": [\n";
	
	$handle = "http://www.avoindata.net/user/";
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
	{
		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);

	}else{
		
		$postdates = mysqli_query($con,"select points, qposts, userid from qa_userpoints order by qposts desc;") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));

		while ($row = mysqli_fetch_array($postdates)):
			$userid = $row['userid'];
			$pcount = $row['qposts'];
			$acount = $row['points'];
			
			$usernames = mysqli_query($con, "select handle from qa_users where userid='".$userid."';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));
			$username = "";
				while ($row2 = mysqli_fetch_array($usernames)):
					$username = $row2['handle'];	
				endwhile;
			$tiedot .= "    {\"userid\":".$userid.",";
			$username = utf8_encode($username);
			$tiedot .= "\n     \"handle\": \"".$username."\",";
			$user = str_replace(' ', '+', $username);
			$tiedot .= "\n     \"questioncount\": ".$pcount.",";
			$tiedot .= "\n     \"profileurl\": \"".$handle.$user."\"},\n";
			//$tiedot .= "\n     \"questioncount\": ".$pcount."\n    },\n";
			
		endwhile;
		
		$cout = substr($tiedot, 0, strlen($tiedot) -2);
		$cout .="],\n";
	 	// lisää rights osuus
		$rights = Api::getRights();
		$cout .= $rights;
		$cout .= "\n}";
		// palauta JSON headerilla
		Api::outputJSON($cout);



		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();

	}
	
  }


  static public function getUserAnswerCounts()
  {
	$tiedot = "{ \"users\": [\n";
	
	$handle = "http://www.avoindata.net/user/";
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
	{
		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);

	}else{
		
		$postdates = mysqli_query($con,"select points, aposts, userid from qa_userpoints order by aposts desc;") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));

		while ($row = mysqli_fetch_array($postdates)):
			$userid = $row['userid'];
			$acount = $row['aposts'];
			
			
			$usernames = mysqli_query($con, "select handle from qa_users where userid='".$userid."';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));
			$username = "";
				while ($row2 = mysqli_fetch_array($usernames)):
					$username = $row2['handle'];	
				endwhile;
			$tiedot .= "    {\"userid\": ".$userid.",";
			$username = utf8_encode($username);
			$tiedot .= "\n     \"handle\": \"".$username."\",";
			$user = str_replace(' ', '+', $username);
			$tiedot .= "\n     \"answercount\": ".$acount.",";
			$tiedot .= "\n     \"profileurl\": \"".$handle.$user."\"\n   },\n";
			
		endwhile;
		
		$cout = substr($tiedot, 0, strlen($tiedot) -2);
		$cout .="],\n";
	 	// lisää rights osuus
		$rights = Api::getRights();
		$cout .= $rights;
		$cout .= "\n}";
		// palauta JSON headerilla
		Api::outputJSON($cout);



		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();

	}
	
  }



  static public function Categories()
  {
    
	// Create connection
	$con = Api::getConnection();
	$tiedot = "data: [\n";
	$arr = array();
	$ret = "";
	
	if (mysqli_connect_errno($con))
  	{
 		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);
 	} else { 
 
      		$tiedot = "{ \"categories\": [\n";
		$categories = mysqli_query($con, "select DISTINCT(categoryid) AS catid from qa_posts; ") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
		$arr = array();
		$ret = "";
		while ($row = mysqli_fetch_array($categories)):        
		    	$cat    =   $row['catid'];
			$newq = "select title from qa_posts where categoryid='".$cat."' and NOT(type='Q_HIDDEN') and type='Q';";
		    	$query = mysqli_query($con, $newq) or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));
			$num_rows = mysqli_num_rows($query);
			$arr[$cat] = $num_rows;
        	endwhile;    
		$counter = 0;
		$arrsize = count($arr);

    		foreach ($arr as $key => $val)
		{
			$catname = "";
			$cats = mysqli_query($con, "select title from qa_categories where categoryid='".$key."'") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));
				while ($row = mysqli_fetch_array($cats)):
					$str = $row['title'];
					$catname = utf8_encode($str);
				endwhile;
	    		$tiedot .= "    {\"title\": \"".$catname."\",";
			$tiedot .= "\n     \"count\":".$val.",";
			$tiedot .= "\n     \"catid\": ".$key."},\n";
			$counter++;
		
		}

	
	$cout = substr($tiedot, 0, strlen($tiedot) -2);
	$cout .="],\n";
 	// lisää rights osuus
	$rights = Api::getRights();
	$cout .= $rights;
	$cout .= "\n}";
	// palauta JSON headerilla
	Api::outputJSON($cout);



	// lisää lokiin tieto
	Api::addToLog($con);
	$con->close();
	}

  }	


  static public function CategoryId($cid)
  {
	$limit = 100;
	if (is_numeric ($_REQUEST['limit'])) {
        	$limit = $_REQUEST['limit']; 
	}
	if($limit > 100){
	   $limit = 100;
	}
	
	$counter = 1;
	
	$con = Api::getConnection();
	$found = false;
	if (mysqli_connect_errno($con))
  	{
 		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);
 	} else {
		$tiedot = "{ \"questions\": [\n";
		$title = "";
		$acount = "";
		$posts = mysqli_query($con,"select categoryid, UNIX_TIMESTAMP(created) as created, postid, title, tags as tagit FROM qa_posts WHERE categoryid =".$cid." ORDER BY created DESC;") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
	 	$num_rows = mysqli_num_rows($posts);
		while ($row = mysqli_fetch_array($posts)):  
			$str = $row['title'];
			$title = utf8_encode($str);
			$title = api::myreplace($title);
			$postid = $row['postid'];
			$tagit    =  $row['tagit'];
			$mytags_arr = explode(",",$row['tagit']);
			if(sizeof($mytags_arr) > 1){
				$tagsout = "\"tags\": [";
				foreach ($mytags_arr as $mytag) {
					$temp = utf8_encode($mytag);
					$tagsout .= '"'.$temp.'",';
				}
				$tagsout = substr($tagsout, 0, strlen($tagsout) -1);
	  			$tagsout .= "]";
  			}else{
				$tagsout = "\"tags\" : [\"NULL\"]";
			} 	
			$created = $row['created'];
			if(strlen($title) > 7 && $counter <= $limit){
				$normalized_title = api::normalize_str($title);
				$url = "http://avoindata.net/".$postid."/";
				$url .= strtolower($normalized_title);
				$tiedot .= "    {\"postid\": ".$postid.",\n     \"title\": \"".$title."\",\n     \"url\": \"".$url."\",\n     \"created\": ".$created.",\n     ".$tagsout."\n     },\n";
				$found = true;
				$counter++;
			}
		endwhile; 
		if($found){
			$cout = substr($tiedot, 0, strlen($tiedot) -2);
			$cout .="],\n";
		 	// lisää rights osuus
			$rights = Api::getRights();
			$cout .= $rights;
			$cout .= "\n}";
			// palauta JSON headerilla
			Api::outputJSON($cout);


		}else{
			ApiErrors::errorEmpty();
		}
		
		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();
	}
  
  }



  static public function Tags()
  {
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
  	{
 		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);
 	} else {

	    	$tiedot = "{ \"tags\": [\n";
		$title = "";
		$acount = "";
		$tags = mysqli_query($con,"SELECT word, wordid, tagcount FROM qa_words WHERE tagcount <> 0 ORDER BY tagcount DESC;") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
	 	$num_rows = mysqli_num_rows($tags);
		while ($row = mysqli_fetch_array($tags)):  
			$str = $row['word'];
			$title = utf8_encode($str);
			$wordid = $row['wordid'];
			$acount    =   $row['tagcount'];
			$tiedot .= "    {\"title\":\"".$title."\",\"count\": ".$acount.",\"wordid\": ".$wordid."},\n";
		endwhile; 
			$cout = substr($tiedot, 0, strlen($tiedot) -2);
			$cout .="],\n";
		 	// lisää rights osuus
			$rights = Api::getRights();
			$cout .= $rights;
			$cout .= "\n}";
			// palauta JSON headerilla
			Api::outputJSON($cout);



		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();
	}
  
  }


  static public function Users()
  {
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
  	{
 		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);
 	} else {

	    	$tiedot = "{ \"users\": [\n";
		$title = "";
		$acount = "";
		$tags = mysqli_query($con,"select handle,userid,UNIX_TIMESTAMP(loggedin) as loggedin, UNIX_TIMESTAMP(created) as joined FROM qa_users;") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
	 	$num_rows = mysqli_num_rows($tags);
		while ($row = mysqli_fetch_array($tags)):  
			$str = $row['handle'];
			$handle = utf8_encode($str);
			$userid = $row['userid'];

			$userdetails = mysqli_query($con,"select * FROM qa_userpoints WHERE userid = '".$userid."';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
			while ($row2 = mysqli_fetch_array($userdetails)):
				$userpoints = $row2['points'];
				$questions = $row2['qposts'];
				$answers = $row2['aposts'];
			endwhile;

			
			$joined    =   $row['joined'];
			$lastlogin    =   $row['loggedin'];
			$tiedot .= "    {\"handle\":\"".$handle."\",";
			$tiedot .= "\n     \"userid\": ".$userid.",";
			$tiedot .= "\n     \"pointscount\": ".$userpoints.",";
			$tiedot .= "\n     \"questions_count\": ".$questions.",";
			$tiedot .= "\n     \"answerscount\": ".$answers.",";
			$tiedot .= "\n     \"created\": ".$joined.",";
			$tiedot .= "\n     \"lastlogin\": ".$lastlogin."\n     },\n";
		endwhile; 
		$cout = substr($tiedot, 0, strlen($tiedot) -2);
		$cout .="],\n";
	 	// lisää rights osuus
		$rights = Api::getRights();
		$cout .= $rights;
		$cout .= "\n}";
		// palauta JSON headerilla
		Api::outputJSON($cout);


		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();
	}
  
  }


static public function UserDetails($uid)
  {
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
  	{
 		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);
 	} else {

	    	$tiedot = "{ \"user\": [\n    {";
		
		$profile = mysqli_query($con,"select title,content FROM qa_userprofile WHERE userid = '".$uid."';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
	 	$num_rows = mysqli_num_rows($profile);
		while ($row = mysqli_fetch_array($profile)):  
			$title = $row['title'];
			$content = api::myreplace(utf8_encode($row['content']));
			$tiedot .= "\n     \"".$title."\":\"".$content."\",";
		endwhile;
	
		$userdetails = mysqli_query($con,"select * FROM qa_userpoints WHERE userid = '".$uid."';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
		while ($row = mysqli_fetch_array($userdetails)):
			$userpoints = $row['points'];
			$questions = $row['qposts'];
			$answers = $row['aposts'];
		endwhile;
			

		$user = mysqli_query($con,"select handle, UNIX_TIMESTAMP(created) as created, UNIX_TIMESTAMP(loggedin) as login  FROM qa_users WHERE userid = '".$uid."';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
		while ($row = mysqli_fetch_array($user)):
			$handle = utf8_encode($row['handle']);
			$lastlogin = $row['login'];
			$created = $row['created'];
			$psuffix = str_replace(" ", "+",$handle);
			$purl = "http://avoindata.net/user/".$psuffix;
	
		endwhile;

		$posts = mysqli_query($con,"select postid FROM qa_posts WHERE userid = '".$uid."' and type='Q';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
		$num_rows = mysqli_num_rows($posts);
		$postids = "[";
		if($num_rows > 0){
			while ($row = mysqli_fetch_array($posts)):
				$postid = $row['postid'];
				$postids .= "\"".$postid."\",";
			endwhile;
		}else{
			$postids .= "\"NULL\" ";
		}
		
		$postids = substr($postids, 0, strlen($postids) -1);
		$postids .= "]";


		$ach = mysqli_query($con,"select questions_read, total_days_visited FROM qa_achievements WHERE user_id = '".$uid."';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	

		while ($row = mysqli_fetch_array($ach)):
			$questions_read = $row['questions_read'];
		endwhile;
		
		$tiedot .= "\n     \"handle\": \"".$handle."\",";
		$tiedot .= "\n     \"profileurl\": \"".utf8_encode($purl)."\",";
		$tiedot .= "\n     \"joined\": ".$created.",";
		$tiedot .= "\n     \"lastlogin\": ".$lastlogin.",";
		$tiedot .= "\n     \"points\": ".$userpoints.",";
		$tiedot .= "\n     \"questions_read\": ".$questions_read.",";
		$tiedot .= "\n     \"question_asked\": ".$questions.",";
		$tiedot .= "\n     \"answer_provided\": ".$answers.",";
		$tiedot .= "\n     \"question_ids\": ".$postids."\n    },\n";
 
		if(strlen($handle) > 2){
			$cout = substr($tiedot, 0, strlen($tiedot) -2);
			$cout .="],\n";
	 		// lisää rights osuus
			$rights = Api::getRights();
			$cout .= $rights;
			$cout .= "\n}";
			// palauta JSON headerilla
			Api::outputJSON($cout);


		}else{
			ApiErrors::errorEmpty();
		}

		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();
	}
  
  }




static public function Questions(){
    
        $tiedot = "{ \"questions\": [\n";
	$arr = array();
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
	{
		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);

	}else{
		$sql = "SELECT title, userid, postid, acount, views, content, tags, netvotes, UNIX_TIMESTAMP(updated) as updated, UNIX_TIMESTAMP(created) as created FROM qa_posts WHERE type='Q' and NOT(type='Q_HIDDEN') ORDER BY created DESC LIMIT 20;";
		
		$posts = mysqli_query($con,$sql) or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));
		
		$num_rows = mysqli_num_rows($posts);
		while ($row = mysqli_fetch_array($posts)):  
			$userid = $row['userid'];
						
			$userdetails = mysqli_query($con,"select * FROM qa_users WHERE userid = '".$userid."';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
			while ($row2 = mysqli_fetch_array($userdetails)):
				$userhandle = $row2['handle'];
				$avatarblob = $row2['avatarblobid'];
				if($avatarblob != NULL){
					$blob = "http://avoindata.net/?qa=image&qa_blobid=".$avatarblob;
				}else{
					$uhash = md5( strtolower( trim( $row2['email'] ) ) );
					$blob = "http://api.avoindata.net/avatar/".$uhash;
				}

			endwhile;
			$content    =   utf8_encode($row['content']);
			// hiukan regexpia
			$content = api::bbcode_to_html($content);
			$content = api::escapeJsonString($content);


			$found = true;
			$str = $row['title'];
			$title = utf8_encode($str);
			$title = api::myreplace($title);
			$acount    =   $row['acount'];
			$postid    =   $row['postid'];
			$vcount    =   $row['views'];
			$votes    =   $row['netvotes'];
			$created    =   $row['created'];
			//$created = strtotime('+1 month',$created);
			$normalized_title = api::normalize_str($title);
			$url = "http://avoindata.net/".$postid."/";
			$url .= strtolower($normalized_title);

			$mytags_arr = explode(",",$row['tags']);
			if(sizeof($mytags_arr) > 1){
				$tagsout = "\"tags\": [";
				foreach ($mytags_arr as $mytag) {
					$temp = utf8_encode($mytag);
					$tagsout .= '"'.$temp.'",';
				}
				$tagsout = substr($tagsout, 0, strlen($tagsout) -1);
	  			$tagsout .= "]";
  			}else{
				$tagsout = "\"tags\" : [\"NULL\"]";
			} 
			if(strlen($row['updated']) < 6){
					$updated    = 0;
				}else{
					$updated    =   $row['updated'];
					// $updated = strtotime('+1 month',$updated);
				}
			$tiedot .= "    {\"title\":\"".$title."\",";
			$tiedot .= "\n     \"id\": ".$postid.",";
			if (is_numeric($userid)) {
        			$tiedot .= "\n     \"userid\": ".$userid.",";
    			} else {
        			$tiedot .= "\n     \"userid\": \"".$userid."\",";
    			}
			$userhandle = utf8_encode($userhandle);
			$tiedot .= "\n     \"userhandle\": \"".$userhandle."\",";
			$tiedot .= "\n     \"usergravatar\": \"".$blob."\",";
			$tiedot .= "\n     \"content\": \"".$content."\",";
			$tiedot .= "\n     \"viewcount\": ".$vcount.",";
			$tiedot .= "\n     \"votes\": ".$votes.",";
			$tiedot .= "\n     \"created\": ".$created.",";
			$tiedot .= "\n     \"updated\": ".$updated.",";
			$tiedot .= "\n     \"answercount\": ".$acount.",";
			$tiedot .= "\n     \"url\": \"".$url."\",";
			$tiedot .= "\n     ".$tagsout."},\n";    
		endwhile; 
		if($found){
			$cout = substr($tiedot, 0, strlen($tiedot) -2);
			$cout .="],\n";
		 	// lisää rights osuus
			$rights = Api::getRights();
			$cout .= $rights;
			$cout .= "\n}";
			// palauta JSON headerilla
			Api::outputJSON($cout);

		}else{
			ApiErrors::errorEmpty();
		}

		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();

	}	

  }


public function getGravatar($hash){

	$gravemail = $hash;
	$gravsrc = "http://www.gravatar.com/avatar/".$gravemail."?d=404";
	$gravcheck = "http://www.gravatar.com/avatar/".$gravemail."?d=404";
	$response = get_headers($gravcheck);
	//echo $response[0];
	$status = $response[0];
//	if ($response[0] != "HTTP/1.1 404 Not Found404 Not Found"){
	if (strpos($status, '404') === false){
	    	$gravatar = $gravsrc;
		
	}else{
	    $gravatar = "/home/avoindat/public_html/images/avatar.jpg";
			
	}
	if(false !== ($data = file_get_contents($gravatar))){
	  	header('Content-type: image/jpeg');
	  	echo $data;
	} 	

}


static public function OneQuestion($id){
    
        $tiedot = "{ \"question\": [\n";
	$arr = array();
	$pid = trim($id);
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
	{
		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);

	}else{
		$sql = "SELECT title, postid, acount, views, content, tags, netvotes, UNIX_TIMESTAMP(updated) as updated, UNIX_TIMESTAMP(created) as created FROM qa_posts WHERE type='Q' and NOT(type='Q_HIDDEN') and postid='".$pid."' LIMIT 1;";
		
		$tags = mysqli_query($con,$sql) or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));
		
		$num_rows = mysqli_num_rows($tags);
		while ($row = mysqli_fetch_array($tags)):  
			$found = true;
			$str = $row['title'];
			$title = utf8_encode($str);
			$title = api::myreplace($title);
			$content    =   utf8_encode($row['content']);
			

			// hiukan regexpia
			$content = api::bbcode_to_html($content);

			$content = api::escapeJsonString($content);	

			$acount    =   $row['acount'];
			$postid    =   $row['postid'];
			$vcount    =   $row['views'];
			$votes    =   $row['netvotes'];
			$created    =   $row['created'];
			//$created = strtotime('+1 month',$created);
			$normalized_title = api::normalize_str($title);
			$url = "http://avoindata.net/".$postid."/";
			$url .= strtolower($normalized_title);

			$mytags_arr = explode(",",$row['tags']);
			if(sizeof($mytags_arr) > 1){
				$tagsout = "\"tags\": [";
				foreach ($mytags_arr as $mytag) {
					$temp = utf8_encode($mytag);
					$tagsout .= '"'.$temp.'",';
				}
				$tagsout = substr($tagsout, 0, strlen($tagsout) -1);
	  			$tagsout .= "]";
  			}else{
				$tagsout = "\"tags\" : [\"NULL\"]";
			} 
			if(strlen($row['updated']) < 6){
					$updated    = 0;
				}else{
					$updated    =   $row['updated'];
					//$updated = strtotime('+1 month',$updated);
				}
			$tiedot .= "    {\"title\":\"".$title."\",";
			$tiedot .= "\n     \"id\": ".$postid.",";
			$tiedot .= "\n     \"content\": \"".$content."\",";
			$tiedot .= "\n     \"viewcount\": ".$vcount.",";
			$tiedot .= "\n     \"votes\": ".$votes.",";
			$tiedot .= "\n     \"created\": ".$created.",";
			$tiedot .= "\n     \"updated\": ".$updated.",";
			$tiedot .= "\n     \"answercount\": ".$acount.",";
			$tiedot .= "\n     \"url\": \"".$url."\",";
			$tiedot .= "\n     ".$tagsout."}\n],\n";
			


			// hae vastaukset
			$sql = "SELECT title, postid, acount, views, tags, content, netvotes, UNIX_TIMESTAMP(updated) as updated, UNIX_TIMESTAMP(created) as created FROM qa_posts WHERE type='A' and parentid='".$pid."' ORDER BY created DESC LIMIT 20;";
			$answers = mysqli_query($con,$sql) or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));
			$tiedot .= " \"answers\": [\n";
			while ($row = mysqli_fetch_array($answers)):  
				$apostid    =   $row['postid'];
				$avotes    =   $row['netvotes'];
				$acreated    =   $row['created'];
				//$acreated = strtotime('+1 month',$created);
				$answercontent    =   utf8_encode($row['content']);
				
				$answercontent = api::bbcode_to_html($answercontent);
				$answercontent = api::escapeJsonString($answercontent);	

				$tiedot .= "    {\n    \"postid\":\"".$apostid."\",";
				$tiedot .= "\n    \"votes\":\"".$avotes."\",";
				$tiedot .= "\n    \"content\":\"".$answercontent."\",";
				$tiedot .= "\n    \"created\":".$acreated."\n    },\n";
				

			endwhile;
			
		
		endwhile; 
		if($found){
			$cout = substr($tiedot, 0, strlen($tiedot) -2);
			$cout .="\n],\n";
		 	// lisää rights osuus
			$rights = Api::getRights();
			$cout .= $rights;
			$cout .= "\n}";
			// palauta JSON headerilla
			Api::outputJSON($cout);

		}else{
			ApiErrors::errorEmpty();
		}

		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();

	}	

  }

  static public function escapeJsonString($value) { 
    $escapers = array("\\", "\"", "\n", "\r", "\t", "\x08", "\x0c");
    $replacements = array("\\\\", "'", "\\n", "\\r", "\\t", "\\f", "\\b");
    $result = str_replace($escapers, $replacements, $value);
    return $result;
  }

  public function bbcode_to_html($bbtext){
	  $bbtags = array(
	    '[heading1]' => '<h1>','[/heading1]' => '</h1>',
	    '[heading2]' => '<h2>','[/heading2]' => '</h2>',
	    '[heading3]' => '<h3>','[/heading3]' => '</h3>',
	    '[h1]' => '<h1>','[/h1]' => '</h1>',
	    '[h2]' => '<h2>','[/h2]' => '</h2>',
	    '[h3]' => '<h3>','[/h3]' => '</h3>',

	    '[paragraph]' => '<p>','[/paragraph]' => '</p>',
	    '[para]' => '<p>','[/para]' => '</p>',
	    '[p]' => '<p>','[/p]' => '</p>',
	    '[left]' => '<p style="text-align:left;">','[/left]' => '</p>',
	    '[right]' => '<p style="text-align:right;">','[/right]' => '</p>',
	    '[center]' => '<p style="text-align:center;">','[/center]' => '</p>',
	    '[justify]' => '<p style="text-align:justify;">','[/justify]' => '</p>',

	    '[bold]' => '<span style="font-weight:bold;">','[/bold]' => '</span>',
	    '[italic]' => '<span style="font-weight:bold;">','[/italic]' => '</span>',
	    '[underline]' => '<span style="text-decoration:underline;">','[/underline]' => '</span>',
	    '[b]' => '<span style="font-weight:bold;">','[/b]' => '</span>',
	    '[i]' => '<span style="font-weight:bold;">','[/i]' => '</span>',
	    '[u]' => '<span style="text-decoration:underline;">','[/u]' => '</span>',
	    '[break]' => '<br>',
	    '[br]' => '<br>',
	    '[newline]' => '<br>',
	    '[nl]' => '<br>',
	    
	    '[unordered_list]' => '<ul>','[/unordered_list]' => '</ul>',
	    '[list]' => '<ul>','[/list]' => '</ul>',
	    '[ul]' => '<ul>','[/ul]' => '</ul>',

	    '[ordered_list]' => '<ol>','[/ordered_list]' => '</ol>',
	    '[ol]' => '<ol>','[/ol]' => '</ol>',
	    '[list_item]' => '<li>','[/list_item]' => '</li>',
	    '[li]' => '<li>','[/li]' => '</li>',
	    
	    '[*]' => '<li>','[/*]' => '</li>',
	    '[code]' => '<code>','[/code]' => '</code>',
	    '[preformatted]' => '<pre>','[/preformatted]' => '</pre>',
	    '[pre]' => '<pre>','[/pre]' => '</pre>',     
	  );

	  $bbtext = str_ireplace(array_keys($bbtags), array_values($bbtags), $bbtext);

	  $bbextended = array(
	    "/\[url](.*?)\[\/url]/i" => "<a href=\"http://$1\" title=\"$1\">$1</a>",
	    "/\[url=(.*?)\](.*?)\[\/url\]/i" => "<a href=\"$1\" title=\"$1\">$2</a>",
	    "/\[email=(.*?)\](.*?)\[\/email\]/i" => "<a href=\"mailto:$1\">$2</a>",
	    "/\[mail=(.*?)\](.*?)\[\/mail\]/i" => "<a href=\"mailto:$1\">$2</a>",
	    "/\[img\]([^[]*)\[\/img\]/i" => "<img src=\"$1\" alt=\" \" />",
	    "/\[image\]([^[]*)\[\/image\]/i" => "<img src=\"$1\" alt=\" \" />",
	    "/\[image_left\]([^[]*)\[\/image_left\]/i" => "<img src=\"$1\" alt=\" \" class=\"img_left\" />",
	    "/\[image_right\]([^[]*)\[\/image_right\]/i" => "<img src=\"$1\" alt=\" \" class=\"img_right\" />",
	  );

	  foreach($bbextended as $match=>$replacement){
	    $bbtext = preg_replace($match, $replacement, $bbtext);
	  }
	  return $bbtext;
	}


  static public function QuestionsCount(){
    
        $tiedot = "{ \"questions\": [\n";
	$arr = array();
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
	{
		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);

	}else{

		$postdates = mysqli_query($con,"select DISTINCT DATE(created) as cdate, count(postid) as lkm from qa_posts where type='Q' and NOT(type='Q_HIDDEN')  group by DATE(created) ORDER BY created;") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));
		while ($row = mysqli_fetch_array($postdates)):
			$arr[$row['cdate']] = $row['lkm'];
		
		endwhile;

		$alldates = array();
		// Start date
		$date = key($arr);
		// End date
		$end_date = date('Y-m-d');
	 
		while (strtotime($date) <= strtotime($end_date)) {
			array_push($alldates, $date);
			$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
		}

		foreach ($alldates as $val)
		{
			if (array_key_exists($val, $arr)) {
				$tiedot .= "    {\"count\": ".$arr[$val].",\n     \"date\": \"".$val."\"\n     },\n";
			}else{
				$tiedot .= "    {\"count\": 0,\n     \"date\": \"".$val."\"\n     },\n";
			}
		}
		$cout = substr($tiedot, 0, strlen($tiedot) -2);
		$cout .="],\n";
	 	// lisää rights osuus
		$rights = Api::getRights();
		$cout .= $rights;
		$cout .= "\n}";
		// palauta JSON headerilla
		Api::outputJSON($cout);

		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();

	}	

  }


  static public function QuestionsYear($year){
    
	$cyear = date("Y");
	if (is_numeric($year) && ($year < $cyear) && (strlen($year) == 4)) {
        	$cyear = $year; 
	}
	$found = false;
        
        $tiedot = "{ \"questions\": [\n";
	$arr = array();
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
	{
		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);

	}else{
		$sql = "SELECT title, postid, acount, views, tags, netvotes, UNIX_TIMESTAMP(updated) as updated, UNIX_TIMESTAMP(created) as created FROM qa_posts WHERE YEAR( created ) = ".$cyear." AND type='Q' and NOT(type='Q_HIDDEN') ORDER BY created DESC;";
		
		$tags = mysqli_query($con,$sql) or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));
		
		$num_rows = mysqli_num_rows($tags);
		while ($row = mysqli_fetch_array($tags)):  
			$found = true;
			$str = $row['title'];
			$title = utf8_encode($str);
			$title = api::myreplace($title);
			$acount    =   $row['acount'];
			$postid    =   $row['postid'];
			$vcount    =   $row['views'];
			$votes    =   $row['netvotes'];
			$created    =   $row['created'];
			$normalized_title = api::normalize_str($title);
			$url = "http://avoindata.net/".$postid."/";
			$url .= strtolower($normalized_title);

			$mytags_arr = explode(",",$row['tags']);
			if(sizeof($mytags_arr) > 1){
				$tagsout = "\"tags\": [";
				foreach ($mytags_arr as $mytag) {
					$temp = utf8_encode($mytag);
					$tagsout .= '"'.$temp.'",';
				}
				$tagsout = substr($tagsout, 0, strlen($tagsout) -1);
	  			$tagsout .= "]";
  			}else{
				$tagsout = "\"tags\" : [\"NULL\"]";
			} 
			if(strlen($row['updated']) < 6){
					$updated    = 0;
				}else{
					$updated    =   $row['updated'];
				}
			$tiedot .= "    {\"title\":\"".$title."\",";
			$tiedot .= "\n     \"id\": ".$postid.",";
			$tiedot .= "\n     \"viewcount\": ".$vcount.",";
			$tiedot .= "\n     \"votes\": ".$votes.",";
			$tiedot .= "\n     \"created\": ".$created.",";
			$tiedot .= "\n     \"updated\": ".$updated.",";
			$tiedot .= "\n     \"answercount\": ".$acount.",";
			$tiedot .= "\n     \"url\": \"".$url."\",";
			$tiedot .= "\n     ".$tagsout."},\n";    
		endwhile; 
		if($found){
			$cout = substr($tiedot, 0, strlen($tiedot) -2);
			$cout .="],\n";
		 	// lisää rights osuus
			$rights = Api::getRights();
			$cout .= $rights;
			$cout .= "\n}";
			// palauta JSON headerilla
			Api::outputJSON($cout);

		}else{
			ApiErrors::errorEmpty();
		}

		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();

	}	

  }


  static public function QuestionsYearMonth($year, $month){
   	
	
	$cyear = date("Y");
	
	if (is_numeric($year) && ($year < $cyear)) {
        	$cyear = $year; 
	}
	
	$found = false;
	$cmonth = date("n");
	// tarkista ettei vaan ole annettu etunollan kanssa...
	if (is_numeric($month)) {
		if (strlen($month) == 2) {
			if(substr($month, 0,1) == '0'){
				$cmonth = substr($month,1,1);
			}else{
				$cmonth = $month;
			}
		}else{		
        		$cmonth = $month;
		}
	}
	
	//echo $cmonth."\n";
        $tiedot = "{ \"questions\": [\n";
	$arr = array();
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
	{
		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);

	}else{
	
		$sql = "SELECT title, postid, acount, views, tags, netvotes, UNIX_TIMESTAMP(updated) as updated, UNIX_TIMESTAMP(created) as created FROM qa_posts WHERE MONTH( created ) = ".$cmonth." AND YEAR( created ) = ".$cyear." AND type='Q' and NOT(type='Q_HIDDEN') ORDER BY created DESC;";
		
		$tags = mysqli_query($con,$sql) or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));
		
		$num_rows = mysqli_num_rows($tags);
		while ($row = mysqli_fetch_array($tags)):  
			$found = true;
			$str = $row['title'];
			$title = utf8_encode($str);
			$title = api::myreplace($title);
			$acount    =   $row['acount'];
			$postid    =   $row['postid'];
			$vcount    =   $row['views'];
			$votes    =   $row['netvotes'];
			$created    =   $row['created'];
			$normalized_title = api::normalize_str($title);
			$url = "http://avoindata.net/".$postid."/";
			$url .= strtolower($normalized_title);

			$mytags_arr = explode(",",$row['tags']);
			if(sizeof($mytags_arr) > 1){
				$tagsout = "\"tags\": [";
				foreach ($mytags_arr as $mytag) {
					$temp = utf8_encode($mytag);
					$tagsout .= '"'.$temp.'",';
				}
				$tagsout = substr($tagsout, 0, strlen($tagsout) -1);
	  			$tagsout .= "]";
  			}else{
				$tagsout = "\"tags\" : [\"NULL\"]";
			} 
			if(strlen($row['updated']) < 6){
					$updated    = 0;
				}else{
					$updated    =   $row['updated'];
				}
			$tiedot .= "    {\"title\":\"".$title."\",";
			$tiedot .= "\n     \"id\": ".$postid.",";
			$tiedot .= "\n     \"viewcount\": ".$vcount.",";
			$tiedot .= "\n     \"votes\": ".$votes.",";
			$tiedot .= "\n     \"created\": ".$created.",";
			$tiedot .= "\n     \"updated\": ".$updated.",";
			$tiedot .= "\n     \"answercount\": ".$acount.",";
			$tiedot .= "\n     \"url\": \"".$url."\",";
			$tiedot .= "\n     ".$tagsout."},\n";    
		endwhile; 
		if($found){
			$cout = substr($tiedot, 0, strlen($tiedot) -2);
			$cout .="],\n";
		 	// lisää rights osuus
			$rights = Api::getRights();
			$cout .= $rights;
			$cout .= "\n}";
			// palauta JSON headerilla
			Api::outputJSON($cout);

		}else{
			ApiErrors::errorEmpty();
		}

		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();
	}	
	

  }



  static public function QuestionsMonth($month){
    
	$cyear = date("Y");
	if (is_numeric($_REQUEST['year']) && ($_REQUEST['year'] < $cyear) && (strlen($_REQUEST['year']) == 4)) {
        	$cyear = $_REQUEST['year']; 
	}
	$found = false;
	$cmonth = date('n');
	// tarkista ettei vaan ole annettu etunollan kanssa...
	if (is_numeric($month)) {
		if (strlen($month) == 2) {
			if(substr($month, 0,1) == '0'){
				$cmonth = substr($month,1,1);
			}else{
				$cmonth = $month;
			}
		}else{		
        		$cmonth = $month;
		}
	}
        
        $tiedot = "{ \"questions\": [\n";
	$arr = array();
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
	{
		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);

	}else{
		$sql = "SELECT title, postid, acount, views, tags, netvotes, UNIX_TIMESTAMP(updated) as updated, UNIX_TIMESTAMP(created) as created FROM qa_posts WHERE MONTH( created ) = ".$cmonth." AND YEAR( created ) = ".$cyear." AND type='Q' and NOT(type='Q_HIDDEN') ORDER BY created DESC;";
		
		$tags = mysqli_query($con,$sql) or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));
		
		$num_rows = mysqli_num_rows($tags);
		while ($row = mysqli_fetch_array($tags)):  
			$found = true;
			$str = $row['title'];
			$title = utf8_encode($str);
			$title = api::myreplace($title);
			$acount    =   $row['acount'];
			$postid    =   $row['postid'];
			$vcount    =   $row['views'];
			$votes    =   $row['netvotes'];
			$created    =   $row['created'];
			$normalized_title = api::normalize_str($title);
			$url = "http://avoindata.net/".$postid."/";
			$url .= strtolower($normalized_title);

			$mytags_arr = explode(",",$row['tags']);
			if(sizeof($mytags_arr) > 1){
				$tagsout = "\"tags\": [";
				foreach ($mytags_arr as $mytag) {
					$temp = utf8_encode($mytag);
					$tagsout .= '"'.$temp.'",';
				}
				$tagsout = substr($tagsout, 0, strlen($tagsout) -1);
	  			$tagsout .= "]";
  			}else{
				$tagsout = "\"tags\" : [\"NULL\"]";
			} 
			if(strlen($row['updated']) < 6){
					$updated    = 0;
				}else{
					$updated    =   $row['updated'];
				}
			$tiedot .= "    {\"title\":\"".$title."\",";
			$tiedot .= "\n     \"id\": ".$postid.",";
			$tiedot .= "\n     \"viewcount\": ".$vcount.",";
			$tiedot .= "\n     \"votes\": ".$votes.",";
			$tiedot .= "\n     \"created\": ".$created.",";
			$tiedot .= "\n     \"updated\": ".$updated.",";
			$tiedot .= "\n     \"answercount\": ".$acount.",";
			$tiedot .= "\n     \"url\": \"".$url."\",";
			$tiedot .= "\n     ".$tagsout."},\n";    
		endwhile; 
		if($found){
			$cout = substr($tiedot, 0, strlen($tiedot) -2);
			$cout .="],\n";
		 	// lisää rights osuus
			$rights = Api::getRights();
			$cout .= $rights;
			$cout .= "\n}";
			// palauta JSON headerilla
			Api::outputJSON($cout);


		}else{
			ApiErrors::errorEmpty();
		}

		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();

	}	

  }




  static public function QuestionsId($uid){

	$limit = 100;
	if (is_numeric ($_REQUEST['limit'])) {
        	$limit = $_REQUEST['limit']; 
	}
	if($limit > 100){
	   $limit = 100;
	}
	

 	$tag = $uid;
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
  	{
 		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);
 	} else {

	    	$tiedot = "{ \"questions\": [\n";
		$title = "";
		$acount = "";
		$cout = "";
		$found = false;
		$counter = 1;
		$posts = mysqli_query($con,"select postid,title,views,netvotes, UNIX_TIMESTAMP(created) as created,views,tags,acount,UNIX_TIMESTAMP(updated) as updated from qa_posts where type='Q' and NOT(type='Q_HIDDEN') and userid = '".$uid."' ORDER BY created DESC") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
	 	$num_rows = mysqli_num_rows($posts);
	
		
		while ($row = mysqli_fetch_array($posts) and $counter <= $limit): 
			$userdetails = mysqli_query($con,"select * FROM qa_userpoints WHERE userid = '".$uid."';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
			while ($row2 = mysqli_fetch_array($userdetails)):
				$userpoints = $row2['points'];
				$questions = $row2['qposts'];
				$answers = $row2['aposts'];
				
			endwhile;
			$found = true;
			$str = $row['title'];
			$title = utf8_encode($str);
			$title = api::myreplace($title);
			$acount    =   $row['acount'];
			$postid    =   $row['postid'];
			$vcount    =   $row['views'];
			$votes    =   $row['netvotes'];
			$created    =   $row['created'];
			$normalized_title = api::normalize_str($title);
			$url = "http://avoindata.net/".$postid."/";
			$url .= strtolower($normalized_title);
			$tiedot .= "    {\"title\":\"".$title."\",";
			$tiedot .= "\n     \"id\": ".$postid.",";
			$tiedot .= "\n     \"viewcount\": ".$vcount.",";
			$tiedot .= "\n     \"votes\": ".$votes.",";
			$tiedot .= "\n     \"created\": ".$created.",";
			$tiedot .= "\n     \"answercount\": ".$acount.",";
			$tiedot .= "\n     \"url\": \"".$url."\"\n    },\n";
			$counter++;
			
		endwhile; 
		if($found){
			$cout = substr($tiedot, 0, strlen($tiedot) -2);
			$cout .="],\n";
		 	// lisää rights osuus
			$rights = Api::getRights();
			$cout .= $rights;
			$cout .= "\n}";
			// palauta JSON headerilla
			Api::outputJSON($cout);


		}else{
			ApiErrors::errorEmpty();
		}
		
		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();
		
	}
 
  }


static public function AnswersId($uid){

	$limit = 100;
	if (is_numeric ($_REQUEST['limit'])) {
        	$limit = $_REQUEST['limit']; 
	}
	if($limit > 100){
	   $limit = 100;
	}
	

 	$tag = $uid;
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
  	{
 		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);
 	} else {

	    	$tiedot = "{ \"answers\": [\n";
		$title = "";
		$acount = "";
		$cout = "";
		$found = false;
		$counter = 1;
		
		$posts = mysqli_query($con,"select postid,title,views,netvotes,parentid, UNIX_TIMESTAMP(created) as created,views,tags,acount,UNIX_TIMESTAMP(updated) as updated from qa_posts where type='A' and userid = '".$uid."' ORDER BY created DESC;") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
	 	$num_rows = mysqli_num_rows($posts);
	
		
		while ($row = mysqli_fetch_array($posts) and $counter <= $limit):
			$parentid = $row['parentid']; 
			
			$parentdetails = mysqli_query($con,"select title FROM qa_posts WHERE postid = '".$parentid."';") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
			while ($row2 = mysqli_fetch_array($parentdetails)):
				$parenttitle = $row2['title'];
				
			endwhile;
			$found = true;
			
			$parenttitle = utf8_encode($parenttitle);
			$parenttitle = api::myreplace($parenttitle);
			$acount    =   $row['acount'];
			$postid    =   $row['postid'];
			$vcount    =   $row['views'];
			$votes    =   $row['netvotes'];
			$created    =   $row['created'];
			$normalized_title = api::normalize_str($parenttitle);
			$url = "http://avoindata.net/".$parentid."/";
			$url .= strtolower($normalized_title);
			$tiedot .= "    {\"parent_title\":\"".$parenttitle."\",";
			$tiedot .= "\n     \"parentid\": ".$parentid.",";
			$tiedot .= "\n     \"id\": ".$postid.",";
			$tiedot .= "\n     \"viewcount\": ".$vcount.",";
			$tiedot .= "\n     \"votes\": ".$votes.",";
			$tiedot .= "\n     \"created\": ".$created.",";
			$tiedot .= "\n     \"parenturl\": \"".$url."\"\n    },\n";
			$counter++;
			
		endwhile; 
		if($found){
			$cout = substr($tiedot, 0, strlen($tiedot) -2);
			$cout .="],\n";
		 	// lisää rights osuus
			$rights = Api::getRights();
			$cout .= $rights;
			$cout .= "\n}";
			// palauta JSON headerilla
			Api::outputJSON($cout);


		}else{
			ApiErrors::errorEmpty();
		}
		
		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();
		
	}
 
  }



  static public function OneTag($tag)
  {
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
  	{
 		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);
 	} else {

	    	$tiedot = "{ \"".$tag."\": [\n";
		$title = "";
		$acount = "";
		$cout = "";
		$found = false;
		$limit = 10;
		$counter = 1;
		$posts = mysqli_query($con,"select postid,title,views,netvotes,views,tags,acount,UNIX_TIMESTAMP(updated) as updated,UNIX_TIMESTAMP(created) as created from qa_posts where type='Q' and NOT(type='Q_HIDDEN') ORDER BY created DESC") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
	 	$num_rows = mysqli_num_rows($posts);
	
		
		while ($row = mysqli_fetch_array($posts) and $counter <= $limit): 
			$tags = strtolower($row['tags']); 
			$stag = strtolower($tag);
			$arr_tags = explode(',',$tags);
			
			if (in_array($stag, $arr_tags)) {
				$found = true;
				$str = $row['title'];
				$title = utf8_encode($str);
				$acount    =   $row['acount'];
				$postid    =   $row['postid'];
				$vcount    =   $row['views'];
				$votes    =   $row['netvotes'];
				$created    =   $row['created'];
				//$created = strtotime('+1 month',$created); 
				if(strlen($row['updated']) < 6){
					$updated    = 0;
				}else{
					$updated    =   $row['updated'];
					//$updated = strtotime('+1 month',$updated);
				}
				$normalized_title = api::normalize_str($title);
				$url = "http://avoindata.net/".$postid."/";
				$url .= strtolower($normalized_title);
				$tiedot .= "    { \"title\":\"".$title."\",";
				$tiedot .= "\n     \"id\": ".$postid.",";
				$tiedot .= "\n     \"viewcount\": ".$vcount.",";
				$tiedot .= "\n     \"votes\": ".$votes.",";
				$tiedot .= "\n     \"created\": ".$created.",";
				$tiedot .= "\n     \"updated\": ".$updated.",";
				$tiedot .= "\n     \"answercount\": ".$acount.",";
				$tiedot .= "\n     \"url\": \"".$url."\"},\n";
				$counter++;
			}
		endwhile; 
		if($found){
			$cout = substr($tiedot, 0, strlen($tiedot) -2);
			$cout .="],\n";
		 	// lisää rights osuus
			$rights = Api::getRights();
			$cout .= $rights;
			$cout .= "\n}";
			// palauta JSON headerilla
			Api::outputJSON($cout);


		}else{
			ApiErrors::errorEmpty();
		}
		
		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();
		
	}
 
  }
 

  static public function OneTagId($tid)
  {
        $uri = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$con = Api::getConnection();
	if (mysqli_connect_errno($con))
  	{
		$errno = mysqli_connect_errno();
		$reason = mysqli_connect_error();
		ApiErrors::errorDbConnection($reason, $errno);
 	} else {

	    	$tiedot = "{ \"questions\": [\n";
		$title = "";
		$acount = "";
		$cout = "";
		$found = false;
		$limit = 10;
		$counter = 1;
		$tagname = "";
		$tagnames = mysqli_query($con,"select word from qa_words where wordid='".$tid."'") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));
		while($row = mysqli_fetch_array($tagnames))
			  {
				$tagname    =   $row['word'];	
			  }
		$posts = mysqli_query($con,"select postid,title,views,netvotes,views,tags,acount,UNIX_TIMESTAMP(updated) as updated,UNIX_TIMESTAMP(created) as created from qa_posts where type='Q' and NOT(type='Q_HIDDEN') ORDER BY created desc") or die(ApiErrors::errorDbQuery(mysqli_error($con), mysqli_errno($con), __FUNCTION__));	
	 	$num_rows = mysqli_num_rows($posts);
	
		
		while ($row = mysqli_fetch_array($posts) and $counter <= $limit): 
			$tags = strtolower($row['tags']); 
			$arr_tags = explode(',',$tags);
			
			if (in_array($tagname, $arr_tags)) {
				$found = true;
				$str = $row['title'];
				$title = utf8_encode($str);
				$acount    =   $row['acount'];
				$postid    =   $row['postid'];
				$vcount    =   $row['views'];
				$votes    =   $row['netvotes'];
				$created    =   $row['created'];	
				if(strlen($row['updated']) < 6){
					$updated    = 0;
				}else{
					$updated    =   $row['updated'];
				}
				$normalized_title = api::normalize_str($title);
				$url = "http://avoindata.net/".$postid."/";
				$url .= strtolower($normalized_title);
				$tiedot .= "    { \"title\":\"".$title."\",";
				$tiedot .= "\n     \"id\": ".$postid.",";
				$tiedot .= "\n     \"viewcount\": ".$vcount.",";
				$tiedot .= "\n     \"votes\": ".$votes.",";
				$tiedot .= "\n     \"created\": ".$created.",";
				$tiedot .= "\n     \"updated\": ".$updated.",";
				$tiedot .= "\n     \"answercount\": ".$acount.",";
				$tiedot .= "\n     \"url\": \"".$url."\"},\n";
				$counter++;
			}
		endwhile; 
		if($found){
			$cout = substr($tiedot, 0, strlen($tiedot) -2);
			$cout .="],\n";
		 	// lisää rights osuus
			$rights = Api::getRights();
			$cout .= $rights;
			$cout .= "\n}";
			// palauta JSON headerilla
			Api::outputJSON($cout);


		}else{
			ApiErrors::errorEmpty();
		}
		
		// lisää lokiin tieto
		Api::addToLog($con);
		$con->close();
		
		
	}
 
  }

  public function addToLog($conn){
		$table_columns = api::getLogColNames();
		$val = api::getConnDetails('GET');
		$query = "INSERT INTO api (".$table_columns.") VALUES(".$val.");";
		$insert = mysqli_query($conn,$query) or die(mysql_error());
  }


  public function normalize_str($str){
	$invalid = array(' '=>'-', 'Š'=>'S','?'=>'','!'=>'', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z',
	'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A',
	'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E',
	'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
	'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y',
	'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a',
	'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e',  'ë'=>'e', 'ì'=>'i', 'í'=>'i',
	'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
	'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y',  'ý'=>'y', 'þ'=>'b',
	'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', "`" => "'", "´" => "'", "„" => ",", "`" => "'",
	"´" => "'", "“" => "\"", "”" => "\"", "´" => "'", "&acirc;€™" => "'", "{" => "",
	"~" => "", "–" => "-", "’" => "'", "\"" => "");
	 
	$str = str_replace(array_keys($invalid), array_values($invalid), $str); 
	return $str;
  }


  public function outputJSON($content)
  {
  
	header("HTTP/1.1 200 OK");

// Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Expose-Headers: Origin, Expires, Content-Type, Content-Language, Access-Control-Allow-Origin');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
	header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        header("Access-Control-Allow-Headers: *");
    }
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-Type: application/json; charset=utf-8');
	header('Access-Token: 124321421345546dfgdgshfg345345');
	header('Link: <http://creativecommons.org/licenses/by-sa/3.0/>; rel="license"');
	echo $content;

  }
  public function myreplace($str){
	$invalid = array('"'=>' ');
	$str = str_replace(array_keys($invalid), array_values($invalid), $str);
	 
	return $str;
  }

  public function getLogColNames() 
   {
	$vars = "api_method, api_name, api_referer, api_ip_address, api_user_agent";
        return $vars;
   } 

  public function getConnDetails($method, $name) 
   {
	
	$ret = "";
	$ret .="'".$method."',";
	$ret .="'".$_SERVER['REQUEST_URI']."',";
	$ret .="'".$_SERVER['HTTP_REFERER']."',";
	$ret .="'".$_SERVER['REMOTE_ADDR']."',";
	$ret .="'".$_SERVER['HTTP_USER_AGENT']."'";
	return $ret;
   }	

 public function getConnection(){
	$con = mysqli_connect("127.0.0.1","username","passwd","dbname");
	return $con;
 }

 public function showRights(){
        $ret .=" {\"rights\": [{";
        $ret .="\n    \"contentLicense\": \"http://creativecommons.org/licenses/by-sa/3.0/\",";
        $ret .="\n    \"dataLicense\": \"http://creativecommons.org/licenses/by-sa/3.0/\",";
        $ret .="\n    \"copyrightNotice\": \"copyright avoindata.net ".date('Y')."\",";
        $ret .="\n    \"attributionText\": \"Sisältö on Suomen avoimen datan yhteisön tuottamaa.\",";
        $ret .="\n    \"attributionURL\": \"http://avoindata.net\"";
        $ret .="\n }]";
	$ret .= "}";
	// palauta JSON headerilla
	Api::outputJSON($ret);

 }

 public function getRights(){
	$ret = "\n \"rights\": [{";
        $ret .="\n    \"contentLicense\": \"http://creativecommons.org/licenses/by-sa/3.0/\",";
        $ret .="\n    \"dataLicense\": \"http://creativecommons.org/licenses/by-sa/3.0/\",";
        $ret .="\n    \"copyrightNotice\": \"copyright avoindata.net ".date('Y')."\",";
        $ret .="\n    \"attributionText\": \"Sisältö on Suomen avoimen datan yhteisön tuottamaa.\",";
        $ret .="\n    \"attributionURL\": \"http://avoindata.net\"";
        $ret .="\n }]";
	return $ret;
 }



}

/* ########## ApiErrors Class ################ */

class ApiErrors
{
  static public function error404() {
	$uri = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$tiedot = "{ \"errors\": [\n";
	$tiedot .= "     {\"code\" : 404, \n";
	$tiedot .= "      \"reason\" : \"Page Does Not Exist\", \n";
	$tiedot .= "      \"uri\" : \"".$uri."\" \n";
        $tiedot .= "     }";
	$tiedot .= "\n]}"; 
	header('HTTP/1.1 404 Not Found');
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-Type: application/json; charset=utf-8');
	echo $tiedot;
  }

  static public function errorEmpty() {
	$uri = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$tiedot = "{ \"errors\": [\n";
	$tiedot .= "     {\"code\" : 204, \n";
	$tiedot .= "      \"reason\" : \"No Content\", \n";
	$tiedot .= "      \"uri\" : \"".$uri."\" \n";
        $tiedot .= "     }";
	$tiedot .= "\n]}"; 
	header('HTTP/1.1 204 No Content');
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-Type: application/json; charset=utf-8');
	echo $tiedot;
  }

  static public function errorDbConnection($reason, $errno) {
	$uri = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$tiedot = "{ \"errors\": [\n";
	$tiedot .= "     {\"code\" : ".$errno.", \n";
	$tiedot .= "      \"reason\" : \"".$reason."\", \n";
	$tiedot .= "      \"uri\" : \"".$uri."\" \n";
        $tiedot .= "     }";
	$tiedot .= "\n]}"; 
	header('HTTP/1.1 500 Internal Server Error');
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-Type: application/json; charset=utf-8');
	echo $tiedot;
  }
  static public function errorDbQuery($reason, $errno, $met) {
	$uri = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$tiedot = "{ \"errors\": [\n";
	$tiedot .= "     {\"code\" : ".$errno.", \n";
	$tiedot .= "      \"reason\" : \"".$reason."\", \n";
	$tiedot .= "      \"method\" : \"".$met."\", \n";
	$tiedot .= "      \"uri\" : \"".$uri."\" \n";
        $tiedot .= "     }";
	$tiedot .= "\n]}"; 
	header('HTTP/1.1 500 Internal Server Error');
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-Type: application/json; charset=utf-8');
	echo $tiedot;
  }



}

