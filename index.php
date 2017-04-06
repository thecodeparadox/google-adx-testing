<?php
require_once "vendor/autoload.php";

$client = new Google_Client();
$client->setApplicationName("Prodata_Google_Adx_Testing");

$service = new Google_Service_AdExchangeBuyer($client);

if (isset($_SESSION['service_token'])) {
    $client->setAccessToken($_SESSION['service_token']);
}

$client->setAuthConfig($key_file_location = "auth_config.json");
$client->addScope('https://www.googleapis.com/auth/adexchange.buyer');

if ($client->isAccessTokenExpired()) {
    $client->refreshTokenWithAssertion();
}

//print_r($client->getAccessToken());
$_SESSION['service_token'] = $client->getAccessToken();


// list creatives
$creative_listing_service = new Google_Service_AdExchangeBuyer_CreativesList($client);
$list_creatives = $service->creatives->listCreatives();
foreach ($list_creatives->getItems() as $item) {
    echo '<pre>';
    print_r($item->getServingRestrictions());
    echo '</pre>';
}
exit;

// Insert Creative to Google AdX pipeline to verification
$creative_service = new Google_Service_AdExchangeBuyer_Creative($client);
$creative_service->accountId = 212286445;
$creative_service->advertiserName = "ProData Media";
$creative_service->buyerCreativeId = "ProData Test1 - AY";
$creative_service->width = 720;
$creative_service->height = 250;
$creative_service->clickThroughUrl = [
	'www.example.com'
];
$creative_service->HTMLSnippet = "<html><body><a href='http://www.example.com'>Hi there!</a></body></html>";
$creative_status = $service->creatives->insert($creative_service);

var_dump($creative_status->toSimpleObject());

/*
$service = new Google_Service_AdExchangeBuyer($client);
$result = $service->accounts->listAccounts();
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