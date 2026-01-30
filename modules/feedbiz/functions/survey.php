<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.Biz, Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.Biz, Ltd. is strictly forbidden.
 * In order to obtain a license, please contact us: contact@feed.biz
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.Biz, Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Feed.Biz, Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Feed.Biz, Ltd. a l'adresse: contact@feed.biz
 * ...........................................................................
 *
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F,
 *            Tak Shing House - Theatre Lane - 20 des Voeux Road Central, -Central, Hong Kong
 * @license   Commercial license
 * Support by mail  :  support@feed.biz
 */

require_once(dirname(__FILE__).'/env.php');
require_once(dirname(__FILE__).'/../feedbiz.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.tools.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.webservice.class.php');

/**
 * Class FeedBizSurvey
 */
class FeedBizSurvey extends Feedbiz
{
    /**
     *
     */
    public function dispatch()
    {
        $data = array();
        $action = Tools::getValue('action');
        $all = Tools::getAllValues();

        if (isset($all['object'])) {
            $data = $all['object'];
        }

        switch ($action) {
            case 'customersurvey':
                $this->customerSurvey($data);
                break;
            case 'php-info':
                $this->phpInfo();
                break;
            case 'prestashop-info':
                $this->prestashopInfo();
                break;
            default:
                $this->dieAndAlert('Missing parameter, nothing to do !');
        }
    }

    /**
     * @param $msg
     */
    private function dieAndAlert($msg)
    {
        $json = Tools::jsonEncode(array(
            'alert' => $msg
        ));
        echo $json;
        die();
    }

    /**
     *
     */
    private function customerSurvey($data)
    {
        $username = '';
        $output = "Customer survey\n";
        $feedbiz_token = Configuration::get('FEEDBIZ_TOKEN');
        $preproduction = Tools::getValue('preprod');
        $debug = Tools::getValue('debug');
        $send = true;
        $error = false;
        $message = sprintf($this->l('Connection test successfull to FeedBiz'))."\n";

        if ($debug) {
            ob_start();
        }

        if (isset($data['is_feedbiz']) && $data['is_feedbiz'] == 1) {
            $send = false;
            $output = "Feedbiz\n";
        }

        $existing_survey = null;
        if (($existing_survey = FeedbizConfiguration::get('survey'))) {
            $serialized_survey = serialize($data);

            if (md5($existing_survey) != md5($serialized_survey)) {
                $output .= "Updating existing customer survey\n";
                FeedbizConfiguration::updateValue('survey', $serialized_survey);
            } else {
                $output .= "Customer is identical than the previous one\n";
                $send = false;
            }
        }

        if ($send) {
            $FeedBiz = new FeedBizWebService($username, $feedbiz_token, $preproduction, $debug);
            $result = $FeedBiz->setCustomerSurvey($data);
            $output .= "Sending customer survey\n".print_r($data, true);

            if ($result) {
                $error = false;
                $serialized_survey = serialize($data);
                FeedbizConfiguration::updateValue('survey', $serialized_survey);
                $output .= "Saving customer survey\n";
            } else {
                $message = $this->l('Failed to connect to FeedBiz');
                $error = true;
            }
        }
        if ($debug) {
            $output .= ob_get_clean();
        }
        $json = Tools::jsonEncode(array(
            'message' => $message,
            'error' => $error,
            'debug' => $output
        ));

        echo $json;
    }
}

$feedbiz_survey = new FeedBizSurvey();
$feedbiz_survey->dispatch();
