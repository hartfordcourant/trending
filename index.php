<?php
require_once 'Unirest/File.php';
require_once 'Unirest/Method.php';
require_once 'Unirest/Response.php';
require_once 'Unirest/Request.php';

//
$host = 'courant.com';
//
$apiKey = '7777fc99317eee064293f89cd14aa9a1';
//
$sections = ['news' => 'news,breaking-news,politics','sports' => 'sports','business' => 'business','entertainment' => 'entertainment'];
//
$all_sections = ['news'=>[],'sports'=>[],'business'=>[],'entertainment'=>[]];
//
$collection = ['news'=>'hc_trending_news','sports'=>'hc_trending_sports','business'=>'hc_trending_business','entertainment'=>'hc_trending_entertainment'];

$MAX_RESULTS = 10;

//
foreach($sections as $key => $value){
	getTrends($key, $value);
}

/********************************************************/
/*** Insert into p2p collection**************************/
/********************************************************/

// populate each collections
foreach($collection as $key => $value){

	// set url to the appropriate collection
	$P2Purl = "http://content-api.p2p.tribuneinteractive.com/collections/override_layout.json?id=" . $value;
	//
	$data["items"] = array();
	//
	$i = 0;
	foreach($all_sections[$key] as $slugs){
		if($i < $MAX_RESULTS){
			$arr = array("slug" => $slugs);
			array_push($data["items"], $arr);
		}
		$i++;
	}
	updateCollection($P2Purl, json_encode($data));
}

/*
 *
 */
function updateCollection($url,$data){

	$P2Paccesstoken = "874ai9840kqvuyojkyqp4k49o6q56yyfa35";

	$headr = array();
	$headr[] = 'Authorization: Bearer '. $P2Paccesstoken;
	$headr[] = 'Content-type: application/json';
	// initiate cURL.
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($ch, CURLOPT_HTTPHEADER,$headr);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
	 
	$response = curl_exec($ch);
	
	//
	if((string)$response == ""){
		echo "Successfully submitted " . $url . "<br><br>";
		echo $data . "<br><br>";
	}
	else{
		echo $response . "<br><br>";
	}
	// close the curl connection
	curl_close($ch);
}

/********************************************************/
/********************************************************/
/********************************************************/

/*
 *
 */
function getTrends($index, $section){

    global $host;
    global $apiKey;
	global $all_sections;
	
	//
	$request = "http://api.chartbeat.com/live/toppages/v3/?apikey={$apiKey}&host={$host}&section={$section}&limit=100";
	//
	$response = Unirest\Request::get($request,array( "Accept" => "application/json"));
	//
	$json = $response->raw_body;
	//
	$arr = json_decode($json);
	//
	$data = $arr->pages;
	//echo var_dump($data);
	foreach($data as $key => $value){
		if($value->authors != NULL){
			//
			array_push($all_sections[$index], getSlug($value->path));
		}
	}
}

/*
 *
 */
function getSlug($slug){
	//
	$paths = ['-column.html','-story.html','-photogallery.html','-htmlstory.html'];
	//
	$slug = substr_replace($slug,"",0,strripos($slug, "/")+1);
	//
	foreach($paths as $path){
		$slug = str_replace($path,"",$slug);	
	}
	//
	if(strlen($slug) != 0){
		return $slug;	
	}
}

//"X-Mashape-Key" => "jgajJAHqz8mshkdScsB2ygKXIAgVp12qOqyjsni3yvSaOy4mBF",

?>