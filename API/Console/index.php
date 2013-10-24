<?php require_once('../../../functions.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Avoindata.net - API konsoli</title>
<script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>
<link href="/qa-theme/tovolt/favicon.ico" rel="shortcut icon">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<link href="/dashboard/css/style2.css" rel="stylesheet" type="text/css" />
<link href="/dashboard/css/mod2.css" rel="stylesheet" type="text/css" />
<link href="/dashboard/css/layout2.css" rel="stylesheet" type="text/css" />

<script src="/dashboard/js/cufon-yui.js" type="text/javascript"></script>
<script src="/dashboard/js/cufon-replace.js" type="text/javascript"></script>
<script src="/dashboard/js/Myriad_Pro_400.font.js" type="text/javascript"></script>
<script src="/dashboard/js/Myriad_Pro_600.font.js" type="text/javascript"></script>
<script src="/js/emphit.js" type="text/javascript"></script>

<!-- <script src="/javascripts/jquery.hashchange.js" type="text/javascript"></script> -->
<script src="/dashboard/tabs/lib/jquery.easytabs.js" type="text/javascript"></script>
<!--[if lt IE 7]>
	<link href="ie_style2.css" rel="stylesheet" type="text/css" />
<![endif]-->


<style>
.metodi, .param{
margin-top:0px;
	margin-left:5px;
	margin-right:0px;
	margin-top:10px;
	background: #1d7745;
	color:#fff;
	padding:5px;
	min-width:300px;
	max-width:300px;
	border: 0px solid #8cbf26;
	-moz-border-radius: 2px;
        -webkit-border-radius: 2px;
        -khtml-border-radius: 2px;
        border-radius: 2px;
}
.button {
margin-top:0px;
	margin-left:5px;
	margin-right:0px;
	margin-top:10px;
	background: #1d7745;
	color:#fff;
	padding:5px;
	min-width:90px;
	max-width:250px;
	border: 0px solid #8cbf26;
	-moz-border-radius: 2px;
        -webkit-border-radius: 2px;
        -khtml-border-radius: 2px;
        border-radius: 2px;
}
.button:hover{
	background: #1ba1e2;
	color:#fff;
	border: 0px solid #7d7c7c;
box-shadow: 2px 2px 2px #888888;
-moz-box-shadow: 2px 2px 2px #888888;
-webkit-box-shadow: 2px 2px 2px #888888;
cursor:pointer;
}

#responsejson {
	max-width:920px;
	overflow:auto;
	max-height:480px;
}

#requestcmd {
background: #fff;
   width: 538px;
   padding: 5px;
   margin-left:20px;
   font-size: 16px;
   line-height: 1;
   border: 0;
   color:#000;
   border-radius: 0;
   height: 24px;
}
select {
background: #ddd;
   width: 338px;
   padding: 5px;
   font-size: 14px;
   line-height: 1;
   border: 1;
   color:#000;
   border-radius: 0;
   height: 32px;
   
}
.etabs { margin: 0; padding: 0; }
.tab { display: inline-block; zoom:1; *display:inline; background: #fff; border: solid 1px #ddd; border-bottom: none; -moz-border-radius: 4px 4px 0 0; -webkit-border-radius: 4px 4px 0 0; }
.tab a { font-size: 16px; line-height: 2em; display: block; padding: 0 10px; outline: none; text-decoration:none;}
.tab a:hover { text-decoration: underline; }
.tab.active { background: #eee; padding-top: 6px; position: relative; top: 1px; border-color: #ddd; text-decoration:none;}
.tab a.active { font-weight: bold; }
.tab-container .panel-container { background: #fff; border: solid #ddd 1px; padding: 10px; -moz-border-radius: 0 4px 4px 4px; -webkit-border-radius: 0 4px 4px 4px; }
.tab-container {
	width:640px;
}

#apidesc {
	background-color:#eee;
	min-height:170px;
	min-width:326px;
	max-width:326px;
	border:solid 1px #ddd; 
	padding:10px;
	margin-left:10px;
	float:left;
}	

#selectors {
	float:left;
	min-width:350px;
	max-width:350px;
	border: solid 1px #ddd;
	background-color:#eee;
	padding:10px; 
	min-height:170px;
}

#apidesc div {
	display:none;
}

#cmd {
	margin-top:10px;
	margin-bottom:10px;
	border: solid 1px #ddd;
	background-color:#eee;
	padding:10px; 
	width:708px;
}
</style>

<script>

function execSearch(){
	var querystr = $('#requestcmd').val();
	var urli = encodeURI("http://" + querystr);
	var sterm = $('#needle').val();
	var posting = $.post( urli, { term: sterm } );
	posting.done(function( data ) {
		$('#responsejson').empty();
		if(data.questions[0].userhandle){
			$("#responsejson").append(JSON.stringify(data, null, 4));
		}else {

		}
	}),
	posting.fail(function() {
		$("#responsejson").text("Ei tuloksia. Kokeile toista hakusanaa.");
		alert("Ei tuloksia. Kokeile toista hakusanaa.");
	});
}




function execQuery(){
var querystr = $('#requestcmd').val();
querystr = encodeURI("http://" + querystr);

$.ajax({
     	type: "GET",
     	url: querystr,
     	dataType: 'json',
     	cache: false,
     	success: function(data)
      	{			
		//alert(JSON.stringify(data));
		$("#responsejson").text(JSON.stringify(data, 'null','\t'));
      	}
  	});
}


function getQuestionsIds(){

$.ajax({
     	type: "GET",
     	url: "http://api.avoindata.net/questions",
     	dataType: 'json',
     	cache: false,
     	success: function(data)
      	{
        	// JSON sisältää kaksi objektia: tags ja rights
		$("#paramdiv select").remove();
		$("#needle").remove();
		$("#paramdiv").append("<select id='param'>");
        	$.each(data.questions, function (i, question) {
                // tee mitä haluat jokaiselle
			
                    	$("#paramdiv select").append("<option value='"+ question.id +"'>"+ question.id +"</option>");
			
        	});

      	}
  	});
}

function getMonths() {
	$("#paramdiv select").remove();
	$("#needle").remove();
	$("#paramdiv").append("<select id='param'>");
	for (var i = 1; i < 13; i ++) {
	    $("#paramdiv select").append("<option value='"+ i +"'>"+ i +"</option>");
	}
}

function showhide(showmeid) {
	$("#apidesc div").hide();
	$("#" + showmeid).show();
}

function getTags(){
$.ajax({
     	type: "GET",
     	url: "http://api.avoindata.net/tags",
     	dataType: 'json',
     	cache: false,
     	success: function(data)
      	{
        	// JSON sisältää kaksi objektia: tags ja rights
		$("#paramdiv select").remove();
		$("#needle").remove();
		$("#paramdiv").append("<select id='param'>");
        	$.each(data.tags, function (i, tag) {
                // tee mitä haluat jokaiselle
			
                    	$("#paramdiv select").append("<option value='"+ tag.wordid +"'>"+ tag.wordid +" (" + tag.title + ")</option>");
			
        	});

      	}
  	});
}


function getUserIDs(){
$.ajax({
     	type: "GET",
     	url: "http://api.avoindata.net/users",
     	dataType: 'json',
     	cache: false,
     	success: function(data)
      	{
        	// JSON sisältää kaksi objektia: tags ja rights
		$("#paramdiv select").remove();
		$("#needle").remove();
		$("#paramdiv").append("<select id='param'>");
        	$.each(data.users, function (i, user) {
                // tee mitä haluat jokaiselle
			
                    	$("#paramdiv select").append("<option value='"+ user.userid +"'>"+ user.userid +" (" + user.handle + ")</option>");
			
        	});

      	}
  	});
}

function getTagsNames(){
$.ajax({
     	type: "GET",
     	url: "http://api.avoindata.net/tags",
     	dataType: 'json',
     	cache: false,
     	success: function(data)
      	{
        	// JSON sisältää kaksi objektia: tags ja rights
		$("#paramdiv select").remove();
		$("#needle").remove();
		$("#paramdiv").append("<select id='param'>");
        	$.each(data.tags, function (i, tag) {
                // tee mitä haluat jokaiselle
			
                    	$("#paramdiv select").append("<option value='"+ tag.title +"'>"+ tag.title +"</option>");
			
        	});

      	}
  	});
}

function getCategories(){
$.ajax({
     	type: "GET",
     	url: "http://api.avoindata.net/categories",
     	dataType: 'json',
     	cache: false,
     	success: function(data)
      	{
        	// JSON sisältää kaksi objektia: tags ja rights
		$("#paramdiv select").remove();
		$("#needle").remove();
		$("#paramdiv").append("<select id='param'>");
		
        	$.each(data.categories, function (i, cate) {
                // tee mitä haluat jokaiselle
			//$("#paramdiv #param").hide();
			//$('#paramdiv #param').prop('disabled', true);
			
                    	$("#paramdiv select").append("<option value='"+ cate.catid +"'>"+ cate.catid +" (" + cate.title + ")</option>");
			
        	});

      	}
  	});
}

$(function(){
	var nonparammethods = ['api.avoindata.net/tags', 
				'api.avoindata.net/categories', 
				'api.avoindata.net/answers/count', 
				'api.avoindata.net/questions', 
				'api.avoindata.net/questions/count',
				'api.avoindata.net/users',
				'api.avoindata.net/users/questions',
				'api.avoindata.net/users/answers'];

	$("#submitq").hide();

	$("#metodit").change(function(){
		var selectedValue = $(this).find(":selected").val();
		if ( $.inArray(selectedValue, nonparammethods) > -1 ) {
    			$("#paramdiv").hide();
			$("#submitq").hide();
			if (selectedValue == 'api.avoindata.net/tags') {
  				$('#fiddle').empty().append("<iframe width='99%' height='500px' src='http://jsfiddle.net/kyyberi/x7nUP/embedded/' allowfullscreen='allowfullscreen' frameborder='0'></iframe>");
				var selectedValue = $('#metodit').val();
				$('#requestcmd').val(selectedValue);
				execQuery();
				showhide("apiavoindatanettags");
			} else if (selectedValue == 'api.avoindata.net/categories') {
  				$('#fiddle').empty().append("<iframe width='99%' height='500px' src='http://jsfiddle.net/kyyberi/Kk8d2/embedded/' allowfullscreen='allowfullscreen' frameborder='0'></iframe>");
				var selectedValue = $('#metodit').val();
				$('#requestcmd').val(selectedValue);
				execQuery();
				showhide("apiavoindatanetcategories");
			} else if (selectedValue == 'api.avoindata.net/questions/count') {
  				$('#fiddle').empty().append("<iframe width='99%' height='500px' src='http://jsfiddle.net/kyyberi/3jZaV/1/embedded/' allowfullscreen='allowfullscreen' frameborder='0'></iframe>");
				var selectedValue = $('#metodit').val();
				$('#requestcmd').val(selectedValue);
				execQuery();
				showhide("apiavoindatanetquestionscount");
			} else if (selectedValue == 'api.avoindata.net/questions') {
  				$('#fiddle').empty().append("<iframe width='99%' height='500px' src='http://jsfiddle.net/kyyberi/GsYPu/embedded/' allowfullscreen='allowfullscreen' frameborder='0'></iframe>");
				var selectedValue = $('#metodit').val();
				$('#requestcmd').val(selectedValue);
				execQuery();
				showhide("apiavoindatanetquestions");
			}else if (selectedValue == 'api.avoindata.net/users') {
  				$('#fiddle').empty().append("<iframe width='99%' height='500px' src='http://jsfiddle.net/kyyberi/mEm8c/embedded/' allowfullscreen='allowfullscreen' frameborder='0'></iframe>");
				var selectedValue = $('#metodit').val();
				$('#requestcmd').val(selectedValue);
				execQuery();
				showhide("apiavoindatanetusers");
			}else if (selectedValue == 'api.avoindata.net/users/questions') {
  				$('#fiddle').empty().append("<iframe width='99%' height='500px' src='http://jsfiddle.net/kyyberi/Dcpke/embedded/' allowfullscreen='allowfullscreen' frameborder='0'></iframe>");
				var selectedValue = $('#metodit').val();
				$('#requestcmd').val(selectedValue);
				execQuery();
				showhide("apiavoindatanetusersquestions");
			}else if (selectedValue == 'api.avoindata.net/users/answers') {
  				$('#fiddle').empty().append("<iframe width='99%' height='500px' src='http://jsfiddle.net/kyyberi/FZsHX/embedded/' allowfullscreen='allowfullscreen' frameborder='0'></iframe>");
				var selectedValue = $('#metodit').val();
				$('#requestcmd').val(selectedValue);
				execQuery();
				showhide("apiavoindatanetusersanswers");
			}else if (selectedValue == 'api.avoindata.net/answers/count') {
  				$('#fiddle').empty().append("<iframe width='99%' height='500px' src='http://jsfiddle.net/kyyberi/8Pxnm/embedded/' allowfullscreen='allowfullscreen' frameborder='0'></iframe>");
				var selectedValue = $('#metodit').val();
				$('#requestcmd').val(selectedValue);
				execQuery();
				showhide("apiavoindatanetanswerscount");
			} else{
				$('#fiddle').empty().append("<img src='/images/jsfiddle.png'></img><p>Ei esimerkkiä.</p>");
			}

			
		}else{
			$("#paramdiv").show();
			$("#submitq").show();
			
			// mikä valinta? osaan ladataan vaihtoehdot valmiiksi osaan laitetaan kenttä.
			if (selectedValue == 'api.avoindata.net/tags/id') {
  				// hae API:n kautta mahdolliset ID:t ja niiden nimet valikkoon
				$('#paramdiv #param').empty().val("api.avoindata.net/tags/id");
				getTags();
				showhide("apiavoindatanettagsid");
				$('#fiddle').empty().append("<iframe width='99%' height='500px' src='http://jsfiddle.net/kyyberi/jmgjz/embedded/' allowfullscreen='allowfullscreen' frameborder='0'></iframe>");

			} else if (selectedValue == 'api.avoindata.net/tags/title') {
				// hae API:n kautta tagien nimet valikkoon
  				getTagsNames();
				showhide("apiavoindatanettagstitle");
				$('#fiddle').empty().append("<iframe width='99%' height='500px' src='http://jsfiddle.net/kyyberi/8YhfP/embedded/' allowfullscreen='allowfullscreen' frameborder='0'></iframe>");

			} else if (selectedValue == 'api.avoindata.net/users/id') {
				// hae API:n kautta tagien nimet valikkoon
  				getUserIDs();
				showhide("apiavoindatanetusersid");
				$('#fiddle').empty().append("<iframe width='99%' height='500px' src='http://jsfiddle.net/kyyberi/eg7kB/embedded/' allowfullscreen='allowfullscreen' frameborder='0'></iframe>");

			} else if (selectedValue == 'api.avoindata.net/categories/id') {
  				// hae API:n kautta mahdolliset kategorioitten ID:t ja niiden nimet valikkoon
				getCategories();
				showhide("apiavoindatanetcategoriesid");
				$('#fiddle').empty().append("<iframe width='99%' height='500px' src='http://jsfiddle.net/kyyberi/fZ4xH/embedded/' allowfullscreen='allowfullscreen' frameborder='0'></iframe>");

			} else if (selectedValue == 'api.avoindata.net/questions/id') {
  				// hae API:n kautta mahdolliset kategorioitten ID:t ja niiden nimet valikkoon
				getQuestionsIds();
				showhide("apiavoindatanetquestionsid");
				$('#fiddle').empty().append("<iframe width='99%' height='500px' src='http://jsfiddle.net/kyyberi/QJxjV/embedded/' allowfullscreen='allowfullscreen' frameborder='0'></iframe>");

			}else if (selectedValue == 'api.avoindata.net/questions/month') {
  				// hae API:n kautta mahdolliset kategorioitten ID:t ja niiden nimet valikkoon
				getMonths();
				showhide("apiavoindatanetquestionsmonth");
				$('#fiddle').empty().append("<iframe width='99%' height='500px' src='http://jsfiddle.net/kyyberi/rfrdd/embedded//' allowfullscreen='allowfullscreen' frameborder='0'></iframe>");

			}else if (selectedValue == 'api.avoindata.net/search/questions') {
				$("#paramdiv select").remove();
				$("#paramdiv #needle").remove();
				$('#paramdiv').append("<input type='text' id='needle' value='hakutermit' name='s'></input>");
				$('#paramdiv').show();
				showhide("apiavoindatanetquestionssearch");
				//$('#fiddle').empty().append("<iframe width='99%' height='500px' src='http://jsfiddle.net/kyyberi/rfrdd/embedded//' allowfullscreen='allowfullscreen' frameborder='0'></iframe>");

			} else{
				$('#fiddle').empty().append("<img src='/images/jsfiddle.png'></img><p>Ei esimerkkiä.</p>");
			}

		}
	});
});



$(function(){
	var nonparammethods = ['api.avoindata.net/tags', 
			'api.avoindata.net/categories', 
			'api.avoindata.net/answers/count', 
			'api.avoindata.net/questions', 
			'api.avoindata.net/questions/count',
			'api.avoindata.net/users',
			'api.avoindata.net/users/questions',
			'api.avoindata.net/users/answers',
			];
	$("#submitq").click(function(){
		var selectedValue = $('#metodit').val();
		if ( $.inArray(selectedValue, nonparammethods) > -1 ) {
    			$('#requestcmd').val(selectedValue);
			execQuery();
		}else if (selectedValue == 'api.avoindata.net/search/questions'){
			
			var params = $('#needle').val();
			$('#requestcmd').val(selectedValue);
			execSearch();
		}else{
			var params = $('#param').val();
			$('#requestcmd').val(selectedValue + "/" + params);
			execQuery();
		}
	});

});
</script>

<script type="text/javascript">
$(document).ready(function() {
	$('#tab-container').easytabs();

});
</script>

<!-- MUST BE THE LAST SCRIPT IN <HEAD></HEAD></HEAD> png fix -->
<script src="../js/jquery/jquery.pngFix.pack.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function(){
$("#paramdiv").hide();
$(document).pngFix( );
});
</script>



</head>
<body id="page1">
<!-- header -->
<div id="header">
	<div class="container">
		<ul class="nav">
			<li><a href="/dashboard/"><span>Etusivu</span></a></li>
			<li><a href="/dashboard/kayttajat/"><span>Käyttäjät</span></a></li>
			<li><a href="/dashboard/kysymykset/"><span>Kysymykset</span></a></li>
			<li><a href="/dashboard/vastaukset/"><span>Vastaukset</span></a></li>
			<li><a href="/dashboard/kategoriat/"><span>Kategoriat</span></a></li>
			<li><a href="/dashboard/tagit/"><span>Tagit</span></a></li>
			<li><a href="/dashboard/api/v1" class="current"><span>API</span></a></li>
			<!-- <li><a href="/dashboard/yhdistelmat/"><span>Yhdistelmät</span></a></li> -->
		</ul>
	</div>
</div>
<!-- content -->
<div id="content">
	<div class="container">
<!-- .intro-text -->
		<div class="boxsome">
				
		<h3 style="color:#000;">Jaa someen</h3>
		<div style="margin-top:10px;">
				<!--
					<span class='st_twitter_vcount' displayText='Tweet'></span>
					<span class='st_facebook_vcount' displayText='Facebook'></span>
					<span class='st_linkedin_vcount' displayText='LinkedIn'></span>
					<span class='st_googleplus_vcount' displayText='Google +'></span>
				-->
				<!-- AddThis Button BEGIN -->
<div class="addthis_toolbox addthis_default_style addthis_32x32_style">
<a class="addthis_button_linkedin"></a>
<a class="addthis_button_facebook"></a>
<a class="addthis_button_twitter"></a>
<a class="addthis_button_google_plusone_share"></a>
</div>
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=undefined"></script>
<!-- AddThis Button END -->
				<h3 style="color:#000;padding-top:40px;margin-bottom:10px;">Osallistu!</h3>
				<p style="margin-top:0px;padding-top:0px;color:#000;">Osoita ja jaa osaamisesi <a href="http://avoindata.net">avoindata.net</a> <br/>palvelussa!. <br/><br/><b>Emme edellytä rekisteröitymistä.</b></p>	
				</div> 
		</div>
 
		<div class="intro-text" style="min-height:800px; border:solid 0px #ddd;">
		<div style="float:right;border: dotted 1px #ddd;padding:10px; margin:10px;-moz-box-shadow: 2px 2px 5px #888;
-webkit-box-shadow: 2px 2px 5px #888;
box-shadow: 2px 2px 5px #888;"><img src="/images/konsoli.png"/></div>
			<h1>Avoindata.net - API konsoli</h1>
			<div>
			<ul class="list1" style="margin-bottom:20px;">
			<li>Valitse alta valikosta metodi. Oikealla puolella näkyy kuvaus metodista ja sen mahdollisista parametreista. Kyselyn suorittamisen jälkeen JSON vastaus ja JSFiddle esimerkki tulee alle.</li>
			<li>Konsoli ei (vielä) sisällä ihan jokaista metodia.</li>
			</ul>
			<div>
				
				<div id="selectors" style="">
				<label for="metodit"><b>1. Valitse valikosta metodi:</b></label>
				<select name="metodit" id="metodit">
				<option value="">Tee valintasi</option>
				<option value="api.avoindata.net/tags">[GET] api.avoindata.net/tags</option>
				<option value="api.avoindata.net/tags/title">[GET] api.avoindata.net/tags/title/:tagin_nimi</option>
				<option value="api.avoindata.net/tags/id">[GET] api.avoindata.net/tags/id/:id</option>
				<option value="api.avoindata.net/categories">[GET] api.avoindata.net/categories</option>
				<option value="api.avoindata.net/categories/id">[GET] api.avoindata.net/categories/id/:id</option>
				<option value="api.avoindata.net/answers/count">[GET] api.avoindata.net/answers/count</option>
				<option value="api.avoindata.net/questions">[GET] api.avoindata.net/questions</option>
				<option value="api.avoindata.net/questions/id">[GET] api.avoindata.net/questions/id/:id</option>
				<option value="api.avoindata.net/questions/count">[GET] api.avoindata.net/questions/count</option>
				<option value="api.avoindata.net/questions/month">[GET] api.avoindata.net/questions/month/:nro</option>
				<option value="api.avoindata.net/users">[GET] api.avoindata.net/users</option>
				<option value="api.avoindata.net/users/id">[GET] api.avoindata.net/users/id/:id</option>
				<option value="api.avoindata.net/users/questions">[GET] api.avoindata.net/users/questions</option>
				<option value="api.avoindata.net/users/answers">[GET] api.avoindata.net/users/answers</option>
				<option value="api.avoindata.net/search/questions">[POST] api.avoindata.net/search/questions</option>	
				
				</select>
				
					<div id="paramdiv" style="margin-top:10px;"><label for="param"><b>2. Anna parametri:</b></label></div>
						<input type="submit" class="button" id="submitq" value="Tee kysely"/>
					</div>
				
				<div id="apidesc" style="">
					<div id="apiavoindatanettags">
						<ul class="list1" style="margin-left:5px;">
							<li>Palauttaa tagit ja niiden kysymysmäärät.</li>
							<li>Parametrit: ei parametreja.</li>
						</ul>
					</div>
					<div id="apiavoindatanettagstitle">
						<ul class="list1" style="margin-left:5px;">
							<li>Palauttaa halutun tagin 10 kysymyksen tiedot.</li>
							<li>Päivämäärä annetaan epoch muodossa.</li>
							<li>Parametrit: tagin nimi.</li>
							<li>HUOM! Valinta nimellä saattaa epäonnistua, mikäli tagissa on skandinaavisia kirjaimia. Näissä tapauksissa voit käyttää ID valintaa</li>
						</ul>
					</div>
					<div id="apiavoindatanetcategoriesid">
						<ul class="list1" style="margin-left:5px;">
							<li>Palauttaa halutun kategorian kysymykset, max 100 kpl.</li>
							<li>Parametri: categorian ID.</li>
							<li>Parametri: limit, eli montako maksimissaan palautetaan, esim. http://api.avoindata.net/categories/id/27?limit=5.</li>
						</ul>
					</div>
					<div id="apiavoindatanetquestionsid">
						<ul class="list1" style="margin-left:5px;">
							<li>Palauttaa kysymyksen tiedot.</li>
							<li>Parametri: kysymyksen ID.</li>
						</ul>
					</div>
					<div id="apiavoindatanetusersid">
						<ul class="list1" style="margin-left:5px;">
							<li>Palauttaa käyttäjän profiilin tiedot.</li>
							<li>Parametri: käyttäjän ID.</li>
						</ul>
					</div>
					<div id="apiavoindatanetquestions">
						<ul class="list1" style="margin-left:5px;">
							<li>Palauttaa 20 viimeisintä kysymystä.</li>
							<li>Päivämäärä annetaan epoch muodossa.</li>
							<li>Parametrit: ei parametreja.</li>
							<li>Uusin on ensin.</li>
						</ul>
					</div>
					<div id="apiavoindatanetusers">
						<ul class="list1" style="margin-left:5px;">
							<li>Palauttaa käyttäjälistauksen.</li>
							<li>Päivämäärät (created ja lastlogin) annetaan epoch muodossa. </li>
							<li>Parametrit: ei parametreja.</li>
						</ul>
					</div>
					<div id="apiavoindatanetusersquestions">
						<ul class="list1" style="margin-left:5px;">
							<li>Palauttaa mm. käyttäjänimet ja kysymysmäärät.</li>
							<li>questioncount = kysymysmäärät</li>
							<li>profileurl = linkki käyttäjän profiiliin</li>
							<li>Parametrit: ei parametreja.</li>
						</ul>
					</div>
					<div id="apiavoindatanetusersanswers">
						<ul class="list1" style="margin-left:5px;">
							<li>Palauttaa mm. käyttäjänimet ja vastausmäärät.</li>
							<li>answercount = vastausmäärät</li>
							<li>profileurl = linkki käyttäjän profiiliin</li>
							<li>Parametrit: ei parametreja.</li>
						</ul>
					</div>
					<div id="apiavoindatanetquestionscount">
						<ul class="list1" style="margin-left:5px;">
							<li>Palauttaa kysymysmäärät päivän tarkkuudella.</li>
							<li>Päivämäärä (date) annetaan [YYYY-MM-DD] formaatissa.</li>
							<li>Parametrit: ei parametreja.</li>
							<li>Vanhin on ensin.</li>
						</ul>
					</div>
					<div id="apiavoindatanetanswerscount">
						<ul class="list1" style="margin-left:5px;">
							<li>Palauttaa vastausmäärät päivän tarkkuudella.</li>
							<li>Päivämäärä (date) annetaan [YYYY-MM-DD] formaatissa.</li>
							<li>Parametrit: ei parametreja.</li>
							<li>Vanhin on ensin.</li>
						</ul>
					</div>
					<div id="apiavoindatanetquestionsmonth">
						<ul class="list1" style="margin-left:5px;">
							<li>Palauttaa halutun kuukauden kysymyslistauksen.</li>
							<li>Parametrit: kuukausi numerona ilman etunollaa.</li>
							<li>Vastauksessa: Mikäli updated arvo on '0', ei kysymystä ole päivitetty.</li>
							<li>Uusin on ensin.</li>
						</ul>
					</div>
					<div id="apiavoindatanettagsid">
						<ul class="list1" style="margin-left:5px;">
							<li>Palauttaa yhden tagin 10 kysymyksen tiedot. Tagin ID, esim. http://api.avoindata.net/tags/id/25 (tagi 'Tampere'). </li>
							<li>JSON aikaleimat  annetaan epoch muodossa. Esimerkiksi epoch: 1372069271 == ISO 8601: 2013-06-24T10:21:11Z </li>
						</ul>
					</div>
					<div id="apiavoindatanetcategories">
						<ul class="list1" style="margin-left:5px;">
							<li>Palauttaa kategoriat, niiden kysymysmäärät ja ID:t.</li>
							<li>Parametrit: ei parametreja.</li>
						</ul>
					</div>
				</div>
				<div style="clear:both;"></div>
				<div id="cmd" style=""><b>Suoritettu pyyntö:</b><input id="requestcmd" size="55" value=""></input></div>
				<div id="tab-container" class="tab-container-github gittab">
						  <ul class='etabs'>
							<li class='tab'><a href="#tabsjson">JSON</a></li>
							<li class='tab'><a href="#tabsjquery">JSFiddle</a></li>
							
						  </ul>
							<div id="tabsjson" class="questions_php cont">
								<div id="response" style="border:dotted 1px #eee; padding:10px;margin-left:0px;clear:both;width:930px;padding:10px;background-color:#eee;margin-bottom:50px;min-height:500px;max-height:500px;">
									<pre id="responsejson" class=""></pre>
								</div>
							</div>
							<div id="tabsjquery" class="questions_ruby cont">
								<div id="fiddle" style="border:dotted 1px #eee; padding:10px;margin-left:0px;clear:both;width:930px;padding:10px;background-color:#eee;margin-bottom:50px;max-height:500px;min-height:500px;">
									
								</div>
							</div>
							
							
				</div>
				
				
			</div>


			</div>
		</div>

		<div style="clear:both; height:50px;"></div>
<!-- /.intro-text -->

		
	</div>
</div>
<!-- footer -->

<script type="text/javascript"> Cufon.now(); </script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-41588104-1', 'avoindata.net');
  ga('send', 'pageview');

</script>
</body>
</html>
