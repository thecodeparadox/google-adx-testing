<?php

require_once "Google_AdX/autoload.php";

class Google_adx {

    protected $client;
    protected $adx_service;
    protected $adx_account_id;

    public function __construct()
    {
        $this->adx_account_id = 212286445; // ProData Google AdX A/C ID

        // make Google Client instance
        $this->client = new Google_Client();
        $this->client->setApplicationName("Prodata_Google_Adx_Testing");
        $this->client->setAuthConfig($key_file_location = "auth_config.json");
        $this->client->addScope('https://www.googleapis.com/auth/adexchange.buyer');

        // AdX Buyer service
        $this->adx_service = new Google_Service_AdExchangeBuyer($this->client);

        // refresh access token and set to session
        if ( isset($_SESSION['GOOGLE_ADX_API_SERVICE_TOKEN']) ) {
            $this->client->setAccessToken($_SESSION['GOOGLE_ADX_API_SERVICE_TOKEN']);
        }

        if ( $this->client->isAccessTokenExpired() ) {
            $this->client->refreshTokenWithAssertion();
        }

        $_SESSION['GOOGLE_ADX_API_SERVICE_TOKEN'] = $this->client->getAccessToken();
    }

    /**
     * Return list of required parameters
     * to create a creative successfully
     *
     * @return array
     */
    protected function get_reqired_params() {
        return array(
            /*array('name' => 'account_id',
                   'display' => 'Account id',
                   'required' => true),*/
            array('name' => 'buyer_creative_id',
                   'display' => 'Buyer creative id',
                   'required' => true),
            array('name' => 'advertiser_name',
                   'display' => 'Advertiser name',
                   'required' => true),
            array('name' => 'html_snippet',
                   'display' => 'HTML Snippet',
                   'required' => true),
            array('name' => 'click_through_urls',
                   'display' => 'Click through URLs',
                   'required' => true),
            array('name' => 'width',
                   'display' => 'Width',
                   'required' => true),
            array('name' => 'height',
                   'display' => 'Height',
                   'required' => true)
        );
    }

    /**
     * Validate New Creative Parameters
     *
     * @param  array $params [description]
     * @return mixed
     */
    private function creative_insert_parameters_validation(array $params)
    {
        $required_params = $this->get_reqired_params();
        $required_keys = array_column($required_params, 'name');
        $param_keys = array_keys($params);
        $missing_params = array_diff($required_keys, $param_keys);
        if ( !empty($missing_params) ) {
            print("Required parameters missing:");
            printf('<pre>');
            print_r($missing_params);
            printf('</pre>');
            exit;
        }

        return true;
    }

    /**
     * Get accounts list
     *
     * @return array
     */
    public function get_accounts_list()
    {
        $accounts = $this->adx_service->accounts->listAccounts();
        return (array)$accounts->toSimpleObject();
    }

    /**
     * Get AdX Account Detail by Account ID
     * @param  integer $account_id
     * @return mixed
     */
    public function get_account($account_id = null)
    {
        empty($account_id) && $account_id = $this->adx_account_id;
        $account = (array)$this->adx_service->accounts->get($account_id)->toSimpleObject();
        return $account;
    }

    /**
     * Insert Creative to Google AdX pipeline to verification
     *
     * @param  array $params
     * @return void
     */
    public function insert_creative(array $params)
    {
        // If not account Id set in params, then
        // set ProData Account's ID by default
        if ( empty($params['account_id']) ) {
            $params['account_id'] = $this->adx_account_id;
        }

        // validate new creative parameters
        $validity = $this->creative_insert_parameters_validation($params);
        if ( $validity !== true ) return $validity;

        // set creative config
        $creative_service = new Google_Service_AdExchangeBuyer_Creative($this->client);
        $creative_service->accountId = $params['account_id'];
        $creative_service->buyerCreativeId = $params['buyer_creative_id'];
        $creative_service->advertiserName = $params['advertiser_name'];
        $creative_service->width = $params['width'];
        $creative_service->height = $params['height'];
        $creative_service->clickThroughUrl = $params['click_through_urls'];
        $creative_service->HTMLSnippet = $params['html_snippet'];

        // create creatives
        $creative_resource = null;
        try {
            $new_creative = $this->adx_service->creatives->insert($creative_service);
            $creative_resource = (array)$new_creative->toSimpleObject();
            $creative_resource['HTMLSnippet'] = htmlspecialchars($creative_resource['HTMLSnippet']);
        } catch(Google_Service_Exception $e) {
            return [
                'status' => 'ERROR',
                'errors' => $e->getErrors()
            ];
        }

        return [
            'status' => 'SUCCESS',
            'creative' => $creative_resource
        ];
    }

    public function get_creative($buyer_creative_id, $account_id = null)
    {
        empty($account_id) && $account_id = $this->adx_account_id;
        $creative_resource = null;
        try {
            $creative = $this->adx_service->creatives->get($account_id, $buyer_creative_id);
            $creative_resource = (array)$creative->toSimpleObject();
            $creative_resource['HTMLSnippet'] = htmlspecialchars($creative_resource['HTMLSnippet']);
        } catch (Google_Service_Exception $e) {
            return $e->getErrors();
        }

        return $creative_resource;
    }

    /**
     * List All Creatives
     * By default, it's showing all creatives
     *
     * @param array $opt_params
     * @param array $creatives
     * @return void
     */
    public function get_creatives_list(array $opt_params = [], array $creatives = [])
    {
        $query_params = [
            //'openAuctionStatusFilter' => 'disapproved',
            'maxResults' => 10
        ];
        $query_params = array_merge($query_params, $opt_params);

        // pull creatives
        $list_creatives = $this->adx_service->creatives->listCreatives($query_params);
        $items = $list_creatives->getItems();
        $next_page_token = $list_creatives->getNextPageToken();

        if ( !empty( $items ) ) {

            foreach ( $items as $item ) {
                $item = (array)$item->toSimpleObject();
                $item['HTMLSnippet'] = htmlspecialchars($item['HTMLSnippet']);
                $creatives[] = $item;

                /*$cc = [];

                $cc['account_id'] = $item->getAccountId();
                $cc['advertiser_name'] = $item->getAdvertiserName();
                $cc['advertiser_id'] = $item->getAdvertiserId();
                $cc['buyer_creative_id'] = $item->getBuyerCreativeId();
                $cc['api_upload_timestamp'] = $item->getApiUploadTimestamp();
                $cc['click_through_urls'] = $item->getClickThroughUrl();

                $cc['html_snippet'] = htmlspecialchars($item->getHtmlSnippet());
                $cc['attributes'] = $item->getAttribute();
                $cc['imp_url'] = $item->getImpressionTrackingUrl();
                $cc['deal_status'] = $item->getDealsStatus();
                $cc['open_auction_status'] = $item->getOpenAuctionStatus();
                $cc['width'] = $item->getWidth();
                $cc['height'] = $item->getHeight();

                $corrections = $item->getCorrections();

                $restrictions = $item->getServingRestrictions();
                $cc['restrictions'] = [];

                foreach ( $restrictions as $restriction ) {
                    $issue = [];
                    $issue['reason'] = $restriction->getReason();

                    // extract contexts
                    $contexts = $restriction->getContexts();
                    $issue['contexts'] = [];
                    foreach ( $contexts as $context ) {
                        $context_detail = [
                            'auctiion_type' => $context->getAuctionType(),
                            'context_type' => $context->getContextType(),
                            'platform' => $context->getPlatform()
                        ];
                        $issue['contexts'][] = $context_detail;
                    }

                    // extract disapproval reasons
                    $disapproval_reasons = $restriction->getDisapprovalReasons();
                    $issue['disapproval_reasons'] = [];
                    foreach ( $disapproval_reasons as $disapprove_reason ) {
                        $disapprove_detail = [
                            'details' => $disapprove_reason->getDetails(),
                            'reason' => $disapprove_reason->getReason()
                        ];
                        $issue['disapproval_reasons'][] = $disapprove_detail;
                    }

                    $cc['restrictions'][] = $issue;
                }

                $creatives[] = $cc;*/
            }
        }

        // check if next page available
        if ( !empty($next_page_token) ) {
            $this->get_creatives_list([
                'pageToken' => $next_page_token
            ], $creatives);
        }

        return $creatives;
    }
}