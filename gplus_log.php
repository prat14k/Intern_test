<?php
session_start(); //session start

require_once ('libraries/Google/autoload.php');

//Insert your cient ID and secret 
//You can get it from : https://console.developers.google.com/
$client_id = "19557556376-8bj61e45h79epvdnmbeetkmbgohe8cse.apps.googleusercontent.com";//'431657949073-0btes1ofin4l6v9glisq92kpf81b1lpk.apps.googleusercontent.com'; 
$client_secret = "HKIv5Bx5z7U2LqLgts-LIoFO";//'V8BCVK0rj62F_GG2e-9WM4nO';
$redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/gplus_log.php';


//incase of logout request, just unset the session var
if (isset($_GET['logout'])) {
  unset($_SESSION['access_token']);
header("Location: index.html");
}

/************************************************
  Make an API request on behalf of a user. In
  this case we need to have a valid OAuth 2.0
  token for the user, so we need to send them
  through a login flow. To do this we need some
  information from our API console project.
 ************************************************/
$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->addScope("email");
$client->addScope("profile");

/************************************************
  When we create the service here, we pass the
  client to it. The client then queries the service
  for the required scopes, and uses that when
  generating the authentication URL later.
 ************************************************/
$service = new Google_Service_Oauth2($client);

/************************************************
  If we have a code back from the OAuth 2.0 flow,
  we need to exchange that with the authenticate()
  function. We store the resultant access token
  bundle in the session, and redirect to ourself.
*/
  
if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
  exit;
}

/************************************************
  If we have an access token, we can make
  requests, else we generate an authentication URL.
 ************************************************/
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
} else {
  $authUrl = $client->createAuthUrl();
}


//Display user info or display login url as per the info we have.
echo '<div style="margin:20px">';
if (isset($authUrl)){ 
	//show login url

echo "<meta http-equiv=\"refresh\" content=\"0;url=$authUrl\">";
	
} else {
	
	$user = $service->userinfo->get(); //get user info 
	
	//show user picture
	echo '<img src="'.$user->picture.'" style="float: right;margin-top: 33px;" />';
	
	{ 
        echo 'Hi '.$user->name.', Thanks for Registering! [<a href="'.$redirect_uri.'?logout=1">Log Out</a>]';
		}
	
	//print user details
	echo '<pre>';
	print_r($user);
	echo '</pre>';
}
echo '</div>';


?>
	