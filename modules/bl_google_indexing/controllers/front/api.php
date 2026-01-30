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

/**
 * Class Bl_Google_IndexingApiModuleFrontController
 *
 * /index.php?fc=module&module=bl_google_indexing&controller=api
 * /module/bl_google_indexing/api
 */
class Bl_Google_IndexingApiModuleFrontController extends ModuleFrontController
{
    protected $fullURL = '';

    public function postProcess()
    {
        $this->fullURL = Tools::getValue('url');
    }

    public function initContent()
    {
        include_once(dirname(__FILE__).'/../../vendor/google_api/autoload.php');
        include_once(dirname(__FILE__).'/../../IndexingApiLog.php');
        include_once(dirname(__FILE__).'/../../IndexingApi.php');

        $settings = json_decode(htmlspecialchars_decode(Configuration::get('BLMOD_INDEXING_SETTINGS')), true);

        if (empty($settings['json_api_key'])) {
            $this->response(false, 'Invalid JSON API Key');
        }

        if (empty($this->fullURL)) {
            $this->response(false, 'Empty URL');
        }

        $indexingApi = new IndexingApi();
        $response = $indexingApi->send($this->fullURL, $settings);
        $success = true;
        $message = 'Indexing request sent successfully to Google';

        if ($response['phrase'] != IndexingApi::RESPONSE_OK) {
            $success = false;
            $message = !empty($response['message']['error']) ? $response['message']['error']['message'] : 'Google API error';
        }

        $this->response($success, $message);
    }

    public function response($success, $message)
    {
        if (!method_exists('ModuleFrontController', 'ajaxRender')) {
            $this->ajaxDie(json_encode(
                [
                    'success' => $success,
                    'message' => $message,
                ]
            ));
            die;
        }

        $this->ajaxRender(
            json_encode(
                [
                    'success' => $success,
                    'message' => $message,
                ]
            )
        );
        die;
    }
}
