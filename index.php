<?php

/*set_time_limit(0);
ini_set('default_socket_timeout', 300);
session_start();*/

/************* Instagram API keys *************/

define('clientID', 'b4ee6ce894264571a3bcf507ad06c44b');
define('clientSecret', 'c4b782eefd49496f998e7f11350ba38d');
define('redirectURI', 'http://localhost/appacad2/index.php');
define('imageDirectory','pics/');

//Connect with Instagram
function connectToInstagram($url) {
	$ch = curl_init();

	curl_setopt_array($ch, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => 2,
	));

	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

//Get Instagram userID
function getUserID($userName) {
	$url = "https://api.instagram.com/v1/users/search?q=".$userName."&client_id=".clientID;
	$instagramInfo = connectToInstagram($url);
	$results = json_decode($instagramInfo, true);
	echo $results['data'][0]['id'];
}

function printImages($accesstoken) {
    $url = 'https://api.instagram.com/v1/users/self/media/recent/?access_token='.$accesstoken;
    $instagramInfo = connectToInstagram($url);
    $results = json_decode($instagramInfo, true);
    foreach($results['data'] as $items) {
        $image_url = $items['images']['low_resolution']['url'];
        echo "<img src=".$image_url." /><br />";
        //savePicture($image_url);
    }
}

function savePicture($image_url) {
    $filename = basename($image_url);
    echo $filename."<br />";
    $destination = imageDirectory.$filename;
    //file_put_contents($destination, file_get_contents($image_url));
}

function getUserData($id, $token) {
    $url = "https://api.instagram.com/v1/users/".$id."/?access_token=".$token;
    $instagramInfo = connectToInstagram($url);
    $results = json_decode($instagramInfo, true);
    echo "Media: ".$results['data']['counts']['media']."<br />";
    echo "Followed by: ".$results['data']['counts']['followed_by']."<br />";
    echo "Follows: ".$results['data']['counts']['follows']."<br />";
}

function connect($code1) {
    //$code = $_GET['code'];
    $code = $code1;
    $url = "https://api.instagram.com/oauth/access_token";
    $access_token_settings = array(
        'client_id'		=>		clientID,
        'client_secret'	=>		clientSecret,
        'grant_type'	=>		'authorization_code',
        'redirect_uri'	=>		redirectURI,
        'code'			=>		$code
    );
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $access_token_settings);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($curl);
    curl_close($curl);

    $results = json_decode($result, true);
    echo "Access Token ".$results['access_token']."<br />";
    echo "Code ".$code."<br />";
    echo "Username: ".$results['user']['username']."<br />";
    echo "User ID: ".$results['user']['id']."<br />";
    printImages($results['access_token']);
    //getUserData($results['user']['id'], $results['access_token']);
}
$code = isset($_GET['code']);
if($code) {
    connect($_GET['code']);
}
else {
?>
<!doctype html>
<html>
<body>
	<a href="https://api.instagram.com/oauth/authorize/?client_id=<?php echo clientID; ?>&redirect_uri=<?php echo redirectURI; ?>&response_type=code">Login</a>
</body>
</html>
<?php
}
?>