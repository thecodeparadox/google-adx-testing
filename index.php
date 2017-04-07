<?php

require_once 'helper.php';
require_once "Google_adx.php";

// Google AdX Class Instance
$Google_adx = new Google_adx();


/**
 * New Creative Insert
 */
/*$new_creative = [
	'buyer_creative_id' => 'ay-test-creative-2',
	'advertiser_name' => 'google',
	'width' => 250,
	'height' => 75,
	'html_snippet' => '<html><body><a href="%%CLICK_URL_UNESC%%http://www.google.com">google.com</a></body></html>',
	'click_through_urls' => ['www.google.com']
];
$creative_response = $Google_adx->insert_creative($new_creative);
debug($creative_response);*/



/**
 * Get Creative Details by Buyer Creative ID
 */
/*$creative = $Google_adx->get_creative(
	$buyer_creative_id = 'ay-test-creative-1'
);
debug($creative);*/



/**
 * List all creatives
 */
/*$creatives = $Google_adx->get_creatives_list();
debug($creatives);
*/



/**
 * Get AdX Accounts list
 */
/*$accounts = $Google_adx->get_accounts_list();
debug($accounts);
*/



/**
 * Get AdX Account Detail by Account ID
 */
/*$account = $Google_adx->get_account($account_id = 212286445);
debug($account);*/
