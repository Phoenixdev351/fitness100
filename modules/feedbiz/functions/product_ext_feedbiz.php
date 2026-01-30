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
require_once(dirname(__FILE__).'/../classes/feedbiz.product.class.php');

/**
 * Class ProductFeedBizExtManager
 */
class ProductFeedBizExtManager extends Feedbiz
{
    /**
     *
     */
    public function doIt()
    {
        $id_lang = $this->id_lang;
        $view_params = array();
        $view_params["id_lang"] = $id_lang;
        $view_params["img"] = $this->images;
        $view_params["id_product"] = Tools::getValue('id_product');

        $defaults = FeedBizProduct::getProductOptions($view_params["id_product"], $id_lang);

        $view_params["forceUnavailableChecked"] = $defaults['disable'] ? 'checked="checked"' : '';
        $view_params["forceInStockChecked"] = $defaults['force'] ? 'checked="checked"' : '';
        $view_params["extraText"] = $defaults['text'];
        $view_params["extraTextCount"] = (200 - Tools::strlen($defaults['text']));
        $view_params["extraPrice"] = ((float)$defaults['price'] ? sprintf('%.02f', $defaults['price']) : '');
        $view_params["shippingDelay"] = ($defaults['shipping']);

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $view_params["PS14"] = "1";
        }

        $this->context->smarty->assign($view_params);
        echo $this->context->smarty->fetch($this->path.'views/catalog/product_ext_feedbiz.tpl');
    }
}

$productExtManager = new FeedBizProductTab();
$productExtManager->doIt();
