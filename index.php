<?php
require_once "vendor/autoload.php";

$client = new Google_Client();
$client->setApplicationName("Prodata_Google_Adx_Testing");

$service = new Google_Service_AdExchangeBuyer($client);

if (isset($_SESSION['service_token'])) {
    $client->setAccessToken($_SESSION['service_token']);
}

$client->setAuthConfig($key_file_location = "Prodata AdX Testing-19888b24757e.json");
$client->addScope('https://www.googleapis.com/auth/adexchange.buyer');

if ($client->isAccessTokenExpired()) {
    $client->refreshTokenWithAssertion();
}

print_r($client->getAccessToken());
$_SESSION['service_token'] = $client->getAccessToken();

$service = new Google_Service_AdExchangeBuyer($client);


// Insert Creative to Google AdX pipeline to verification
$creative_service = new Google_Service_AdExchangeBuyer_Creative($client);
$creative_service->accountId = 1111;
$creative_service->buyerCreativeId = 2222;
$creative_service->width = 720;
$creative_service->height = 250;
$creative_service->clickThroughUrl = [
	'http://reporting.prodata.media/c2/2161/2515'
];
$creative_status = $service->creatives->insert($creative_service);

var_dump($creative_status);

/*$result = $service->accounts->listAccounts();
print '<h2>Listing of user associated accounts</h2>';
if ( ! isset($result['items']) || ! count($result['items']) ) {
    print '<p>No accounts found</p>';
    return;
} else {
    foreach ($result['items'] as $account) {
        printf('<pre>');
        print_r($account);
        printf('</pre>');
    }
}*/