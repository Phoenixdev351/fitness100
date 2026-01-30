<?php
/**
 * 2010-2021 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2021 Bl Modules
 * @license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class IndexingApi
{
    const ACTION_UPDATE = 'URL_UPDATED';
    const RESPONSE_OK = 'OK';

    protected $httpClient;
    protected $langId = 1;

    public function send($url, $settings)
    {
        if (!$this->isValidDayQuota($settings)) {
            return [
                'phrase' => 'none',
                'message' => [
                    'error' => [
                        'message' => 'Daily quota reached',
                    ],
                ],
            ];
        }

        $this->load($settings);

        $endpoint = 'https://indexing.googleapis.com/v3/urlNotifications:publish';

        $content = '{
                "url": "'.$url.'",
                "type": "'.self::ACTION_UPDATE.'"
            }';

        $response = $this->httpClient->post($endpoint, ['body' => $content,]);
        $body = json_decode($response->getBody()->getContents(), true);

        IndexingApiLog::add($url, self::ACTION_UPDATE, $response->getReasonPhrase(), json_encode($body));

        return ['phrase' => $response->getReasonPhrase(), 'message' => $body,];
    }

    public function get($url, $settings)
    {
        $this->load($settings);

        $response = $this->httpClient->get('https://indexing.googleapis.com/v3/urlNotifications/metadata?url='.urlencode($url));

        return ['phrase' => $response->getReasonPhrase(), 'message' => $response->getBody()->getContents(),];
    }

    public function sendAfterProductUpdate($productId, $langId, $settings)
    {
        $this->langId = (int)$langId;

        $link = new Link();
        $product = new Product($productId, false, $this->langId);
        $combinations = $this->getProductCombinations($product, $settings);
        $apiUrl = $link->getModuleLink('bl_google_indexing', 'api');

        foreach ($combinations as $c) {
            $pageUrl = $this->getProductURL($product, $link, $c);

            if (!$this->isNewUrl($pageUrl)) {
                continue;
            }

            $opts = ['http' =>
                [
                    'method'  => 'POST',
                    'header'  => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => http_build_query(
                        [
                            'url' => $pageUrl,
                        ]
                    ),
                ]
            ];

            $context = stream_context_create($opts);
            Tools::file_get_contents($apiUrl, false, $context);
        }
    }

    public function isNewUrl($pageUrl)
    {
        $r = Db::getInstance()->getValue('SELECT l.id
            FROM '._DB_PREFIX_.'blmod_indexing_api_log l 
            WHERE l.url = "'.pSQL($pageUrl).'" AND l.created_at > "'.pSQL(date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').'-30seconds'))).'"');
            
        return empty($r);
    }

    public function getProductCombinations(Product $product, $settings)
    {
        $combinations = $product->getAttributesResume($this->langId, ' ', ', ');

        if (empty($combinations) || empty($settings['combination_indexing'])) {
            $combinations = [];
            $combinations[0]['id_product_attribute'] = 0;

            return $combinations;
        }

        $combinationsEmpty = [];
        $combinationsEmpty[] = ['id_product_attribute' => 0,];

        return array_merge($combinationsEmpty, $combinations);
    }

    public function getProductURL(Product $product, Link $link, $combination)
    {
        if (empty($combination['id_product_attribute'])) {
            return $link->getProductLink($product, null, null, null, $this->langId);
        }

        return $link->getProductLink($product, null, null, null, $this->langId, null, $combination['id_product_attribute'], Configuration::get('PS_REWRITING_SETTINGS'), false, true);
    }

    protected function load($settings)
    {
        $client = new Google_Client();

        $client->setAuthConfig(json_decode($settings['json_api_key'], true));
        $client->addScope('https://www.googleapis.com/auth/indexing');

        $this->httpClient = $client->authorize();
    }

    protected function isValidDayQuota($settings)
    {
        if (empty($settings['requests_per_day'])) {
            return true;
        }

        $indexingApiLog = new IndexingApiLog();

        if ($indexingApiLog->countCurrentDayLogsTotal() >= $settings['requests_per_day']) {
            return false;
        }

        return true;
    }
}
