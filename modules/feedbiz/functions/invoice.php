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
require_once(dirname(__FILE__).'/../classes/feedbiz.order.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.tools.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.context.class.php');

/**
 * Class FeedBizInvoice
 */
class FeedBizInvoice extends Feedbiz
{
    private $debug;

    /**
     * FeedBizInvoice constructor.
    */
    public function __construct()
    {
        parent::__construct();

        FeedbizContext::restore($this->context);

        $this->debug = (bool)Configuration::get('FEEDBIZ_DEBUG') || (bool)Tools::getValue('debug');
    }

    public function dispatch()
    {
        FeedbizTools::securityCheck();

        $fborder = Tools::getValue('fborder');

        ob_start();

        $return_data = $this->getInvoice($fborder);

        ob_end_clean();

        if ($return_data) {
            header("Content-type:application/pdf");
            echo $return_data;
        }
    }

    /**
    * @param int $id_order
    * @return mixed
    */
    private function getInvoice($id_order)
    {
        $order = new FeedBizOrder($id_order);

        if (!Validate::isLoadedObject($order) || !$order->id_lang || $order->module != "feedbiz") {
            if ($this->debug) {
                printf('%s:#%d Invalid Order (%d)'."<br />\n", basename(__FILE__), __LINE__, $id_order);
            }
            return (false);
        }

        if (!$order->invoice_number) {
            if ($this->debug) {
                printf(
                    '%s:#%d Invalid processing for Order (%d) - Order has no invoice number'."<br />\n",
                    basename(__FILE__),
                    __LINE__,
                    $id_order
                );
            }

            return (false);
        }

        $customer = new Customer($order->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            if ($this->debug) {
                printf('%s:#%d Invalid Customer (%d)'."<br />\n", basename(__FILE__), __LINE__, $order->id_customer);
            }

            return (false);
        }

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $pdf = new PDF($order->getInvoicesCollection(), PDF::TEMPLATE_INVOICE, Context::getContext()->smarty);
            $file_attachement = $pdf->render(false);
        } else {
            $cookie = Context::getContext()->cookie;
            $id_employee = (int)Configuration::get('FEEDBIZ_ID_EMPLOYEE');

            if (!$cookie->id_employee) {
                $cookie->id_employee = $id_employee ? $id_employee : 1;
            }

            $file_attachement = PDF::invoice($order, 'S');
        }

        return ($file_attachement);
    }
}

$feedbizinvoice = new FeedBizInvoice();
$feedbizinvoice->dispatch();
