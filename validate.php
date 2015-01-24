<?php

define("DB_HOST", "localhost"); //DB HOST
define("DB_USER", "root");      //DB USER
define("DB_PASSWORD", "");      //DB PASS
define("DB_DATABASE", "aa");    //DB DB
define("DB_TABLE", "members");  //DB TABLE
define("FILE_NAME","http://localhost/validate.php"); //URL FOR THIS FILE
define("CLIENT_ID","SOMETHING_SOMETHING_CLIENT_ID"); // CHANGE THIS
define("SECRET_ID","YOUR SECRET ID")

$authcode     =  $_GET["code"];
$clientid     =  CLIENT_ID; // client id
$clientsecret =  SECRET_ID; //Secret id
$redirecturi  =  FILE_NAME; // redirect uri [path to your validate.php]


$fields=array(
	'code'=>  urlencode($authcode),
	'client_id'=>  urlencode($clientid),
	'client_secret'=>  urlencode($clientsecret),
	'redirect_uri'=>  urlencode($redirecturi),
	'grant_type'=>  urlencode('authorization_code')
);

?>
<html>
	<head>
		<meta name="robots" content="noindex" />
		<style>.green{color:green;}.red{color:red;}</style>
</head>
	<body style="font-family: tahoma; font-size: 12px;">
<?php


$fields_string='';
foreach($fields as $key=>$value)
{
	$fields_string .= $key.'='.$value.'&';
}
$fields_string=rtrim($fields_string,'&');

echo "INITALIZING...<br/>";
$ch = curl_init();
echo "INITIALIZED...<br />";

curl_setopt($ch,CURLOPT_URL,'https://accounts.google.com/o/oauth2/token');
curl_setopt($ch,CURLOPT_POST,5);
curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($ch);
$response=  json_decode($result);
$accesstoken= $response->access_token;


// ACCESSING ALL THE OLD CONTACTS, JUST IN CASE WE WANT TO AVOID DUPLICACY
$xmlresponse= file_get_contents('https://www.google.com/m8/feeds/contacts/default/full?&oauth_token='.$accesstoken);

$xml= new SimpleXMLElement($xmlresponse);
$xml->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');

// FETCH PHONE NUMBER PER RECORD
$result = $xml->xpath('//gd:phoneNumber');

$my_contacts = array();

foreach ($result as $title)
{
	// SAVE ALL PHONE NUMBERS IN AN ARRAY
	$my_contacts[]= $title;
}


// INTERVAL


// CONNECT TO DB
$con = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);

// Check connection
if (mysqli_connect_errno())
{
echo "Failed to connect to MySQL: " . mysqli_connect_error();
die();
}

// SELECT DB
$db_selected = mysql_select_db(DB_DATABASE, $con);


// YOUR QUERY
$query = "SELECT * FROM ".DB_TABLE;

// FETCH THE ROWS
$result = mysql_query($query);

if (!$result) {
	$message  = 'Invalid query: ' . mysql_error() . "\n";
	$message .= 'query: ' . $query;
	die($message);
}

// ADDING AUTH TOKEN, JUST IN CASE ;)
$header=array("Content-type: application/atom+xml","Authorization: GoogleLogin auth=$accesstoken");
$page = "https://www.google.com/m8/feeds/contacts/default/full?oauth_token=".$accesstoken;

// actually fetching the rows...
while ($row = mysql_fetch_assoc($result)) {

	$my_number =  $row['no_tel'];

	// only continue, if not in fetched array...
	if(in_array($my_number, $my_contacts)){
		echo '<hr />'.$my_number." . . . . . . . . . . <span class='red'>SKIPPED</span>";
		continue;
	}

	$my_name = $row['name'];
	$my_email = $row['email'];

	//the xml to create the contact
$post_string = '<atom:entry xmlns:atom="http://www.w3.org/2005/Atom" xmlns:gd="http://schemas.google.com/g/2005"><atom:category scheme="http://schemas.google.com/g/2005#kind" term="http://schemas.google.com/contact/2008#contact"/><title>'.$my_name.'</title><gd:name><gd:givenName>'.$my_name.'</gd:givenName><gd:familyName> </gd:familyName><gd:fullName>'.$my_name.'</gd:fullName></gd:name><gd:email rel="http://schemas.google.com/g/2005#work" primary="true" address="'.$my_email.'" displayName="'.$my_name.'"/><gd:phoneNumber rel="http://schemas.google.com/g/2005#work" primary="true">'.$my_number.'</gd:phoneNumber></atom:entry>';

	// TIME TO CURL...
	curl_setopt($ch, CURLOPT_URL, $page);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

	$response=curl_exec($ch);

	if(curl_errno($ch))
		echo curl_errno($ch);echo curl_error($ch);
	else
		echo '<hr />'.$my_number." . . . . . . . . . . <span class='green'>ADDED :)</span>";
}

// DISH-WASHING IS IMPORTANT TOO :/
curl_close($ch);
mysql_free_result($result);







?>
</body></html>


