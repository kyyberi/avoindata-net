<?php

/**
 * @author Q2A Market
 * @copyright 2013
 * @Website http://www.q2amarket.com
 * @version 1.00 
 * 
 * 
 * Description:
 * ------------
 * Get recent questions from Question2Anser website.
 * 
 * Usage:
 * ------
 * Define Q2A path to $qa_path=site_root('YOUR_Q2A_PATH'); where 'YOUR_Q2A_PATH' is your q2a 'qa-config.php' file path.
 * 
 * To list recent question you need to call 'qa_recent_questions($args=array())' function where you would like to dispaly.
 * $args parameter can helps to customize html structure and css class.
 * 
 * Parameters:
 * -----------
 * $args:-
 * (array)(optional) Query string will override the values in $defaults.
 * Default: None
 * 
 * $limit:-
 * (string)(optional) Must be an intiger value defined the number of question will be displayed.
 * Default: 10
 * 
 * $container:-
 * (string)(optional) Valid HTML tag to wrap the un-order list of questions. Required only tag e.g 'div' and not '<div>' no closing tag required.
 * Default: None
 * 
 * $container_class:-
 * (string)(optional) CSS class for container
 * Default: None
 * 
 * $list_class:-
 * (string)(optional) CSS class for UL un-order list
 * Default: None
 * 
 * Example:-
 * ---------
 * // set function parameter in array format
 * $args = array(
 *      'limit' => 25,
 *      'container' => 'div',
 *      'container_class' => 'recent-question',
 *      'list_class' => 'q-list-item'
 * );
 * 
 * Now you can pass $args into the function
 * qa_recent_questions($args);
 * 
 * Note: This is a beta version and may or may not work with your website. Q2A Market is not responsible for any losses.
 * 
 * 
 */
  
/*-----> define your questin&answer (qa) path (not url) <-------*/
$qa_path=site_root('/');

$qa_directory = basename($qa_path);    
               
require_once($qa_path.'qa-config.php');
include($qa_path.'qa-include/qa-base.php');


    //root path function
    //Usually you do not have to touch this part.
    function site_root($path=null){
        $root = $_SERVER['DOCUMENT_ROOT'];
        return $root.$path;
    }
    

// user count function.
// the function has everything include from databse to html output and can be modify as per your need.
    function qa_user_count(){
       
        $prefix = constant('QA_MYSQL_TABLE_PREFIX'); 
      
	/* postauksien määrä */
        $query = qa_db_query_sub("select userid, handle FROM qa_users; ") or die(mysql_error());
	$num_rows = mysql_num_rows($query);
	return $num_rows;    
    
    }

// user count function.
// the function has everything include from databse to html output and can be modify as per your need.
    function qa_question_count(){
       
        $prefix = constant('QA_MYSQL_TABLE_PREFIX'); 
      
	/* postauksien määrä */
        $query2 = qa_db_query_sub("select postid, acount from qa_posts where type='Q'; ") or die(mysql_error());
	$num_rows = mysql_num_rows($query2);
	
	return $num_rows;    
    
    }

// answer count
    function qa_answer_count(){
       
        $prefix = constant('QA_MYSQL_TABLE_PREFIX'); 
      
	/* postauksien määrä */
        $query2 = qa_db_query_sub("select postid, type from qa_posts where type='A'; ") or die(mysql_error());
	$num_rows = mysql_num_rows($query2);
	/* vastaus määrä */
	$asum = 0;
	while ($row = qa_db_read_one_assoc($query2, true)):        
	    
	    $acount    =   $row['type'];
	    //$asum = $asum + $acount;
	    $asum .= $acount;
        endwhile;
	return $num_rows;    
    
    }


// category count
    function qa_category_count(){
       
        $prefix = constant('QA_MYSQL_TABLE_PREFIX'); 
      	$query2 = qa_db_query_sub("select title from qa_categories;")  or die(mysql_error());
        //$query2 = qa_db_query_sub("select DISTINCT(categoryid) AS categoryid from qa_posts; ") or die(mysql_error());
	$num_rows = mysql_num_rows($query2);
	return $num_rows;   
 
    }

// post counts by categories    
    function qa_postcount_categories(){
       
        
      	$tiedot = "data: [\n";
	$categories = qa_db_query_sub("select DISTINCT(categoryid) AS catid from qa_posts; ") or die(mysql_error());	
	$arr = array();
	$ret = "";
	while ($row = qa_db_read_one_assoc($categories, true)):        
	    	$cat    =   $row['catid'];
		$newq = "select title from qa_posts where categoryid='".$cat."' and NOT(type='Q_HIDDEN') and type='Q';";
	    	$query = qa_db_query_sub($newq) or die(mysql_error());
		$num_rows = mysql_num_rows($query);
		$arr[$cat] = $num_rows;

        endwhile;    
	$counter = 0;
	$arrsize = count($arr);
    	foreach ($arr as $key => $val)
	{
		$catname = "";
		$cats = qa_db_query_sub("select title from qa_categories where categoryid='".$key."'; ") or die(mysql_error());
		
			while ($row = qa_db_read_one_assoc($cats, true)):
				$catname = $row['title'];
			endwhile;
		
    		$tiedot .= "    ['".$catname."', ".$val."],\n";
		
		$counter++;
		
	}
	$endpos = strlen($tiedot)-2;
	$cout = substr($tiedot, 0, $endpos);
	$cout .= "\n]";
	return $cout;
    }






// post counts by categories    
    function qa_postcount_categories_table(){
       
        // $prefix = constant('QA_MYSQL_TABLE_PREFIX'); 
      	$rivit = "";
	$categories = qa_db_query_sub("select DISTINCT(categoryid) AS catid from qa_posts where NOT(type='Q_HIDDEN'); ") or die(mysql_error());	
	$arr = array();
	$ret = "";
	while ($row = qa_db_read_one_assoc($categories, true)):        
	    	$cat    =   $row['catid'];
		$newq = "select title from qa_posts where categoryid='".$cat."' and NOT(type='Q_HIDDEN') and type='Q';";
	    	$query = qa_db_query_sub($newq) or die(mysql_error());
		$num_rows = mysql_num_rows($query);
		$arr[$cat] = $num_rows;

        endwhile;    
	arsort($arr);
	
	/* need 
	<tr>
	       <td>name</td>
	       <td>% number</td>
	       <td>count</td>
	</tr>
	*/
	$counter = 0;
	$arrsize = count($arr);
	$totalc = array_sum($arr);
	$url = "http://www.avoindata.net";	
    	foreach ($arr as $key => $val)
	{
		$catname = "";
		$cats = qa_db_query_sub("select DISTINCT(title) AS title from qa_categories where categoryid='".$key."';") or die(mysql_error());
		
			while ($row = qa_db_read_one_assoc($cats, true)):
				$catname = $row['title'];
				if($catname == 'QA alusta'){
					$catname = 'Alusta';
				}
			endwhile;
		$percent = $val/$totalc*100;
		$perc = round($percent,2);
		$ntitle = normalize_str($catname);
		$curl = $url."/questions/".$ntitle;
		// http://www.avoindata.net/feed/questions/demokratia-ja-osallistuminen.rss
		$curlrss = $url."/feed/questions/".$ntitle.".rss";
    		$rivit .= "<tr><td><a target='new' href='".$curl."' title='Kategoriaan -".$catname."- liittyvät kysymykset avoindata.net palvelussa.'>".$catname."</a></td><td>".$perc." %</td><td>".$val."</td><td><a target='new' title='Kategoriaan -".$catname."- liittyvät kysymykset RSS syötteenä' href='".$curlrss."'><img src='http://www.avoindata.net/qa-theme/tovolt/images/rss.jpg'/></a></td></tr>\n";
			
	}
	return $rivit;
    }





// post counts by categories    
    function qa_postcount_categories_table_height(){
	$categories = qa_db_query_sub("select DISTINCT(categoryid) AS catid from qa_posts; ") or die(mysql_error());	
 	$num_rows = mysql_num_rows($categories);
	$row_height = 30;
	$height = 100 + $row_height * $num_rows;
	$height .= "px;";
	return $height;
    }

// most votes post    
    function qa_most_votes_post(){
	$title = "";
	$votes = "";
	$num_rows = mysql_num_rows($posts);
	$posts = qa_db_query_sub("select postid,title,netvotes from qa_posts where type='Q' and NOT(type='Q_HIDDEN') ORDER BY netvotes DESC LIMIT 0, 3;") or die(mysql_error());	
 	$num_rows = mysql_num_rows($posts);
	$i = 1;
	while ($row = qa_db_read_one_assoc($posts, true)):        
	    	$title    =   $row['title'];
		$ntitle = normalize_str($title);
		$votes    =   $row['netvotes'];
		$pid	= $row['postid'];
		$nhandle = $handle."/".$pid;
		$ret .= "    <div class='topnumber2'>".$votes."</div>\n";
		$ret .= "<div class='topname'><a target='top' href='".$nhandle."/".$ntitle."'>".$title."</a></div>\n";
		$i++;
        endwhile; 
	return $ret; 
    }

// most read post    
    function qa_most_views_post(){
	$title = "";
	$views = "";
	$ret = "";
	$handle = "http://www.avoindata.net";
	$posts = qa_db_query_sub("select postid,title,views from qa_posts where type='Q' and NOT(type='Q_HIDDEN') ORDER BY views DESC LIMIT 0, 3;") or die(mysql_error());	
	$i = 1;
	$handle = "http://www.avoindata.net";
	while ($row = qa_db_read_one_assoc($posts, true)):        
	    	$title    =   $row['title'];
		$ntitle = normalize_str($title);
		$views    =   $row['views'];
		$pid	= $row['postid'];
		$nhandle = $handle."/".$pid;
		$ret .= "    <div class='topnumber2'>".$views."</div>\n";
		$ret .= "<div class='topname'><a target='top' href='".$nhandle."/".$ntitle."'>".$title."</a></div>\n";
		$i++;
        endwhile; 
	return $ret; 
	    
    }

// most answers post    
    function qa_most_answers_post(){
	$title = "";
	$acount = "";
	$ret = "";
	$handle = "http://www.avoindata.net";
	$posts = qa_db_query_sub("select postid,title,acount from qa_posts where type='Q' and NOT(type='Q_HIDDEN') ORDER BY acount DESC LIMIT 0, 3;") or die(mysql_error());	
 	$num_rows = mysql_num_rows($posts);
	$i = 1;
	while ($row = qa_db_read_one_assoc($posts, true)):        
	    	$title    =   $row['title'];
		$ntitle = normalize_str($title);
		$acount    =   $row['acount'];
		$pid	= $row['postid'];
		$nhandle = $handle."/".$pid;
		$ret .= "    <div class='topnumber2'>".$acount."</div>\n";

		$ret .= "<div class='topname'><a target='top' href='".$nhandle."/".$ntitle."'>".$title."</a></div>\n";
		$i++;
        endwhile; 
	return $ret; 
    }


// get tags in table    
    function qa_tags_table_counts(){
	$title = "";
	$acount = "";
	$ret = "";
	$tags = qa_db_query_sub("SELECT word, tagcount FROM qa_words WHERE tagcount <> 0 ORDER BY tagcount DESC;") or die(mysql_error());	
 	$num_rows = mysql_num_rows($tags);
	$i = 1;
	while ($row = qa_db_read_one_assoc($tags, true)):        
	    	$title    =   $row['word'];
		$acount    =   $row['tagcount'];
		$url = "http://www.avoindata.net/tag/".$title;
		$urlrss = "http://www.avoindata.net/feed/tag/".$title.".rss";
		$ret .= "<tr><td><a href='".$url."' title='Tagiin -".$title."- liittyvät kysymykset avoindata.net palvelussa.'>".$title."</a></td><td>".$acount."</td><td><a target='new'  title='Tagiin -".$title."- liittyvien kysymysten RSS syöte.' href='".$urlrss."'><img src='http://www.avoindata.net/qa-theme/tovolt/images/rss.jpg'/></a></td></tr>\n";
		$i++;
        endwhile; 
	return $ret; 
    }

// get tags data array    
    function qa_tags_data(){
	$tiedot = "data: [\n";
	$title = "";
	$acount = "";
	$tags = qa_db_query_sub("SELECT word, tagcount FROM qa_words WHERE tagcount <> 0 ORDER BY tagcount DESC;") or die(mysql_error());	
 	$num_rows = mysql_num_rows($tags);
	while ($row = qa_db_read_one_assoc($tags, true)):        
	    	$title    =   $row['word'];
		$acount    =   $row['tagcount'];
		$tiedot .= "    ['".$title."', ".$acount."],\n";
        endwhile; 
	$cout = substr($tiedot, 0, strlen($tiedot) -2);
	$cout .= "\n]";
	return $cout;
  
  }


// get tags   
    function qa_tags_with_counts(){
	$title = "";
	$acount = "";
	$ret = "";
	$tags = qa_db_query_sub("SELECT word, tagcount FROM qa_words WHERE tagcount <> 0 ORDER BY tagcount DESC;") or die(mysql_error());	
 	$num_rows = mysql_num_rows($tags);
	$i = 1;
	while ($row = qa_db_read_one_assoc($tags, true)):        
	    	$title    =   $row['word'];
		$acount    =   $row['tagcount'];
		$ret .= "    <div class='topnumber'>".$i."</div>\n";
		$ret .= "<div class='topname'>".$title."<span class='toppoints'>".$acount." esiintymistä</span></div>\n";
		$i++;
        endwhile; 
	return $ret; 
    }

// get top tags    
    function qa_top_tags_with_counts(){
	$title = "";
	$acount = "";
	$maxcount = 0;
	$divider = 0;
	$url = "http://www.avoindata.net/tag/";
	$ret = "var word_list = [\n";
	$tags = qa_db_query_sub("SELECT word, tagcount FROM qa_words WHERE tagcount <> 0 ORDER BY tagcount DESC;") or die(mysql_error());	
 	$num_rows = mysql_num_rows($tags);
	while ($row = qa_db_read_one_assoc($tags, true)): 	
		$maxcount    =   $row['tagcount'];
		break;
	endwhile;
	if($maxcount > 10){
		$divider = 10/$maxcount;
	}

	while ($row = qa_db_read_one_assoc($tags, true)):        
		$i = 1;
	    	$title    =   $row['word'];
		$acount    =   $row['tagcount'];
		if($maxcount > 10){
			$acount = ceil($acount/$devider);
		}
		$tagurl = $url . $title;
		$ret .= "    {text: '".$title."', weight: ".$acount.", link: '".$tagurl."'},\n";
		$i++;
        endwhile; 
	//$ret .= "    {text: '".$maxcount."', weight:4},\n";
	$reto = substr( $ret, 0, strlen($ret) - 2 ); 
	$reto .= "];";
	return $reto;
    }

// get tags count   
    function qa_tags_count(){
	$tags = qa_db_query_sub("SELECT DISTINCT(word) as word, tagcount FROM qa_words WHERE tagcount <> 0 ORDER BY tagcount DESC;") or die(mysql_error());	
 	$num_rows = mysql_num_rows($tags);

	return $num_rows; 
    }


function normalize_str($str)
{
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
"~" => "", "–" => "-", "’" => "'");
 
$str = str_replace(array_keys($invalid), array_values($invalid), $str);
 
return $str;
}

// answer counts by categories    
    function qa_answercount_categories(){
       
        
      	$tiedot = "data: [\n";
	$categories = qa_db_query_sub("select DISTINCT(categoryid) AS catid from qa_posts; ") or die(mysql_error());	
	$arr = array();
	$ret = "";
	while ($row = qa_db_read_one_assoc($categories, true)):        
	    	$cat    =   $row['catid'];
		$newq = "select title from qa_posts where categoryid='".$cat."' and type='A';";
	    	$query = qa_db_query_sub($newq) or die(mysql_error());
		$num_rows = mysql_num_rows($query);
		$arr[$cat] = $num_rows;

        endwhile;    
	$counter = 0;
	$arrsize = count($arr);
    	foreach ($arr as $key => $val)
	{
		$catname = "";
		$cats = qa_db_query_sub("select title from qa_categories where categoryid='".$key."'; ") or die(mysql_error());
		
			while ($row = qa_db_read_one_assoc($cats, true)):
				$catname = $row['title'];
			endwhile;
		
    		$tiedot .= "    ['".$catname."', ".$val."],\n";
		
		$counter++;
		
	}
	$endpos = strlen($tiedot)-2;
	$cout = substr($tiedot, 0, $endpos);
	$cout .= "\n]";
	return $cout;
    }



// answer counts by categories    
    function qa_answercount_categories_table(){
       
        // $prefix = constant('QA_MYSQL_TABLE_PREFIX'); 
      	$rivit = "";
	$categories = qa_db_query_sub("select DISTINCT(categoryid) AS catid from qa_posts where type='A'; ") or die(mysql_error());	
	$arr = array();
	$ret = "";
	while ($row = qa_db_read_one_assoc($categories, true)):        
	    	$cat    =   $row['catid'];
		$newq = "select title from qa_posts where categoryid='".$cat."' and type='A' and NOT(type='A_HIDDEN');";
	    	$query = qa_db_query_sub($newq) or die(mysql_error());
		$num_rows = mysql_num_rows($query);
		$arr[$cat] = $num_rows;

        endwhile;    

	/* need 
	<tr>
	       <td>name</td>
	       <td>% number</td>
	       <td>count</td>
	</tr>
	*/
	$counter = 0;
	$arrsize = count($arr);
	$totalc = array_sum($arr);

    	foreach ($arr as $key => $val)
	{
		$catname = "";
		$cats = qa_db_query_sub("select DISTINCT(title) AS title from qa_categories where categoryid='".$key."';") or die(mysql_error());
		
			while ($row = qa_db_read_one_assoc($cats, true)):
				$catname = $row['title'];
			endwhile;
		$percent = $val/$totalc*100;
    		$rivit .= "<tr><td>".$catname."</td><td>".$percent." %</td><td>".$val."</td></tr>\n";
			
	}
	return $rivit;
    }









// post counts by dates    
    function qa_postcount_days(){
       
        $ret = "data: [";
	$arr = array();
	$postdates = qa_db_query_sub("select DISTINCT DATE(created) as cdate, count(postid) as lkm from qa_posts where type='Q' and NOT(type='Q_HIDDEN')  group by DATE(created) ORDER BY created;") or die(mysql_error());
	while ($row = qa_db_read_one_assoc($postdates, true)):
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
    			$ret .= $arr[$val].",";
		}else{
			$ret .= "0,";
		}
	}
	$endpos = strlen($ret)-1;
	$cout = substr($ret, 0, $endpos);
	$cout .= "\n]";
	return $cout;
	
    }






// post counts by dates cumulative   
    function qa_cumulative_postcount_days(){
       
        $ret = "data: [";
	$arr = array();
	$postdates = qa_db_query_sub("select DISTINCT DATE(created) as cdate, count(postid) as lkm from qa_posts where type='Q' and NOT(type='Q_HIDDEN')  group by DATE(created) ORDER BY created;") or die(mysql_error());
	while ($row = qa_db_read_one_assoc($postdates, true)):
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

	$cumulat = 0;
	foreach ($alldates as $val)
	{
		if (array_key_exists($val, $arr)) {
			$cumulat = $cumulat + $arr[$val];
    			$ret .= $cumulat.",";
		}else{
			$ret .= $cumulat.",";
		}
	}
	$endpos = strlen($ret)-1;
	$cout = substr($ret, 0, $endpos);
	$cout .= "\n]";
	return $cout;
	
    }







// post counts by dates    
    function qa_answercount_days(){
       
        $ret = "data: [";
	$arr = array();
	$postdates = qa_db_query_sub("select DISTINCT DATE(created) as cdate, count(postid) as lkm from qa_posts where type='A' and NOT(type='A_HIDDEN')  group by DATE(created) ORDER BY created;") or die(mysql_error());
	while ($row = qa_db_read_one_assoc($postdates, true)):
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
    			$ret .= $arr[$val].",";
		}else{
			$ret .= "0,";
		}
	}
	$endpos = strlen($ret)-1;
	$cout = substr($ret, 0, $endpos);
	$cout .= "\n]";
	return $cout;
	
    }



// post counts by dates cumulative   
    function qa_cumulative_answercount_days(){
       
        $ret = "data: [";
	$arr = array();
	$postdates = qa_db_query_sub("select DISTINCT DATE(created) as cdate, count(postid) as lkm from qa_posts where type='A' and NOT(type='A_HIDDEN')  group by DATE(created) ORDER BY created;") or die(mysql_error());
	while ($row = qa_db_read_one_assoc($postdates, true)):
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

	$cumulat = 0;
	foreach ($alldates as $val)
	{
		if (array_key_exists($val, $arr)) {
			$cumulat = $cumulat + $arr[$val];
    			$ret .= $cumulat.",";
		}else{
			$ret .= $cumulat.",";
		}
	}
	$endpos = strlen($ret)-1;
	$cout = substr($ret, 0, $endpos);
	$cout .= "\n]";
	return $cout;
	
    }



// post counts by dates    
    function qa_start_date(){
       /* Need to have:
	Date.UTC(2009, 9, 6, 0, 0, 0)

	*/
        $ret = "";
	$arr = array();
	$postdates = qa_db_query_sub("select DATE(created) as cdate, count(postid) as lkm from qa_posts group by DATE(created) ORDER BY created;") or die(mysql_error());
	while ($row = qa_db_read_one_assoc($postdates, true)):
		$arr[$row['cdate']] = $row['lkm'];
	endwhile;
	$first_key = key($arr);
	$pieces = explode("-", $first_key);
  	$mon = $pieces[1]-1; // need to start -1 months for some reason, don't know why but works...
	$ret = $pieces[0].",".$mon.",".$pieces[2].",0,0,0";
	
	return $ret;
	
    }


// post counts by dates    
    function qa_start_date_simple(){
       
        $ret = "";
	$arr = array();
	$postdates = qa_db_query_sub("select DATE(created) as cdate, count(postid) as lkm from qa_posts group by DATE(created) ORDER BY created;") or die(mysql_error());
	while ($row = qa_db_read_one_assoc($postdates, true)):
		$arr[$row['cdate']] = $row['lkm'];
	endwhile;
	$first_key = key($arr);
	$pieces = explode("-", $first_key);
	$now = date('Y-m-d');
	$now2 = date('d.m.Y');
	$ret = $pieces[2].".".$pieces[1].".".$pieces[0]." - ".$now2;

	$days = dateDiff ($first_key, $now);
	$ret = $ret.", ".$days. " päivän jakso";
	return $ret;
    }

function dateDiff ($d1, $d2) {
// Return the number of days between the two dates:

  return round(abs(strtotime($d1)-strtotime($d2))/86400);

}  // end function dateDiff




function collectdata() {


}

function time_elapsed_string($ptime) {
    $etime = time() - $ptime;
    
    if ($etime < 1) {
        return '0 seconds';
    }
    
    $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'päivä',
                60 * 60                 =>  'hour',
                60                      =>  'minute',
                1                       =>  'second'
                );
    
    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . ' ' . $str . ($r > 1 ? 'ä' : '');
        }
    }
}
    
/* USERS page related */

// post counts by dates    
    function qa_userscount_days(){
       
        $ret = "data: [";
	$arr = array();
	$postdates = qa_db_query_sub("select DISTINCT DATE(created) as cdate, count(userid) as lkm from qa_users group by DATE(created) ORDER BY created;") or die(mysql_error());
	while ($row = qa_db_read_one_assoc($postdates, true)):
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

	$temp = 0;
	foreach ($alldates as $val)
	{
	
		if (array_key_exists($val, $arr)) {
			$temp = $temp + $arr[$val];
    			$ret .= $temp.",";
		}else{
			$ret .= $temp.",";
		}
	}
	$endpos = strlen($ret)-1;
	$cout = substr($ret, 0, $endpos);
	$cout .= "\n]";
	return $cout;
	
    }

function qa_top_question_points() {

	$arr = array();
	$handle = "http://www.avoindata.net/user/";
	$postdates = qa_db_query_sub("select points as points, qposts, userid from qa_userpoints order by qposts desc;") or die(mysql_error());
	while ($row = qa_db_read_one_assoc($postdates, true)):
		$userid = $row['userid'];
		$usernames = qa_db_query_sub("select handle from qa_users where userid='".$userid."';") or die(mysql_error());
		$username = "";
			while ($row2 = qa_db_read_one_assoc($usernames, true)):
				$username = $row2['handle'];
			endwhile;
		$arr[$username] = $row['qposts'];
	endwhile;
	$i = 1;
	echo "<h2>Top 5 kysyjät</h2>\n";
	echo "<div class='box2'>\n";

	foreach ($arr as $key => $val)
	{
		$handleurl = str_replace(' ', '+', $key);
		$url = $handle.$handleurl;
		if($i <= 5){
			echo "<div class='topuser'>\n";
			echo "    <div class='topnumber'>".$i."</div>\n";
			if($val > 1 or $val == 0){
				echo "    <div class='topname'><a target='top' href='".$url."'>".$key."</a><span class='toppoints'>".$val." kysymystä</span></div>\n";
			}else{
				echo "    <div class='topname'><a target='top' href='".$url."'>".$key."</a><span class='toppoints'>".$val." kysymys</span></div>\n";
			}
			echo "</div>\n";
			//echo "<p>".$key.":".$val."</p>";
		}
		$i++;
	}
	echo "</div>\n";
}

function qa_top_answer_points() {

	$arr = array();
	$handle = "http://www.avoindata.net/user/";
	$postdates = qa_db_query_sub("select points as points, aposts, userid from qa_userpoints order by aposts desc;") or die(mysql_error());
	while ($row = qa_db_read_one_assoc($postdates, true)):
		$userid = $row['userid'];
		$usernames = qa_db_query_sub("select handle from qa_users where userid='".$userid."';") or die(mysql_error());
		$username = "";
			while ($row2 = qa_db_read_one_assoc($usernames, true)):
				$username = $row2['handle'];
			endwhile;
		$arr[$username] = $row['aposts'];
	endwhile;
	$i = 1;
	echo "<h2>Top 5 vastaajat</h2>\n";
	echo "<div class='box2'>\n";
	foreach ($arr as $key => $val)
	{
		$handleurl = str_replace(' ', '+', $key);
		$url = $handle.$handleurl;
		if($i <= 5){
			echo "<div class='topuser'>\n";
			echo "    <div class='topnumber'>".$i."</div>\n";
			if($val > 1 or $val == 0){
				echo "    <div class='topname'><a target='top' href='".$url."'>".$key."</a><span class='toppoints'>".$val." vastausta</span></div>\n";
			}else{
				echo "    <div class='topname'><a target='top' href='".$url."'>".$key."</a><span class='toppoints'>".$val." vastaus</span></div>\n";
			}
			echo "</div>\n";
			//echo "<p>".$key.":".$val."</p>";
		}
		$i++;
	}
	echo "</div>\n";
}


function qa_top_points() {

	$arr = array();
	$pcount = array();
	$acount = array();
	$handle = "http://www.avoindata.net/user/";
	$postdates = qa_db_query_sub("select points, qposts, aposts, userid from qa_userpoints order by points desc;") or die(mysql_error());
	while ($row = qa_db_read_one_assoc($postdates, true)):
		$userid = $row['userid'];
		$usernames = qa_db_query_sub("select handle from qa_users where userid='".$userid."';") or die(mysql_error());
		$username = "";
			while ($row2 = qa_db_read_one_assoc($usernames, true)):
				$username = $row2['handle'];
				$pcount[] = $row['qposts'];
				$acount[] = $row['aposts'];
				
			endwhile;
		$arr[$username] = $row['points'];
	endwhile;
	$i = 1;
	foreach ($arr as $key => $val)
	{
		if($i <= 1){
			$handleurl = str_replace(' ', '+', $key);
			$url = $handle.$handleurl;
			echo "<h2>Avoindata.net guru</h2>\n";
			echo "<div class='box2'>\n";
			/* echo "    <div class='gurunumber'><img src='/dashboard/images/guru.png'/></div>\n"; */
			echo "    <div class='guruname'><a target='top' href='".$url."'>".$key."</a></div>\n";
			echo "    <div class='gurupoints'>".$val." pistettä</div>\n";
			echo "    <div class='gurupoints'>".$pcount[0]." kysymystä</div>\n";
			echo "    <div class='gurupoints'>".$acount[0]." vastausta</div>\n";
			echo "</div>\n";
			
		}
		$i++;
	}
}



?>
