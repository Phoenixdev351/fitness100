<?php
/**
 * 2010-2022 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2022 Bl Modules
 * @license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductSettingsAdmin extends Xmlfeeds
{
    protected $full_address_no_t = '';
    protected $token = '';
    protected $imageClassName = 'ImageCore';
    protected $langId = 1;
    protected $currentPageUrl = '';

    public function getContent($full_address_no_t = '', $token = '')
    {
        $this->full_address_no_t = $full_address_no_t;
        $this->token = $token;
        $this->imageClassName = (!class_exists('ImageCore', false) || _PS_VERSION_ > '1.5.3') ?  'Image' : 'ImageCore';
        $this->langId = (int)Configuration::get('PS_LANG_DEFAULT');
        $this->currentPageUrl = str_replace('&page=', '&page_old=', $_SERVER['REQUEST_URI']);
        $this->currentPageUrl = str_replace('&delete_product_setting_package=', '&delete_product_setting_package_old=', $this->currentPageUrl);

        return '';
    }

    public function insertNewProductSettingsPackage()
    {
        $addNewList = Tools::getValue('add_product_settings_package');
        $listName = Tools::getValue('product_setting_package_name');

        if (empty($addNewList) || empty($listName)) {
            return false;
        }

        Db::getInstance()->Execute('
            INSERT INTO '._DB_PREFIX_.'blmod_xml_product_settings_package
            (`name`)
            VALUE
            ("'.pSQL($listName).'")
        ');

        $packageId = Db::getInstance()->Insert_ID();

        if (empty($packageId)) {
            return false;
        }

        Db::getInstance()->Execute('
            INSERT INTO '._DB_PREFIX_.'blmod_xml_product_settings
            (`product_id`, `package_id`)
            VALUE
            ("0", "'.pSQL($packageId).'")
        ');

        return $packageId;
    }

    public function deleteProductSettingsPackage()
    {
        $packageId = Tools::getValue('delete_product_setting_package');

        if (empty($packageId)) {
            return false;
        }

        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_product_settings WHERE package_id = "'.(int)$packageId.'"');
        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_product_settings_package WHERE id = "'.(int)$packageId.'"');

        return true;
    }

    public function getProductSettingsPackagesList()
    {
        return Db::getInstance()->executeS('SELECT l.id, l.name
			FROM `'._DB_PREFIX_.'blmod_xml_product_settings_package` l
			ORDER BY l.name ASC');
    }

    public function getProducts($whereParam, $page, $packageId)
    {
        $where = !empty($whereParam) ? 'WHERE '.pSQL(implode(' AND ', $whereParam)) : '';
        $limitFrom = ($page > 1) ? (($page - 1) * XmlFeedsTools::ITEM_IN_PAGE) : 0;

        return Db::getInstance()->ExecuteS('SELECT DISTINCT(p.id_product), pl.name, im.id_image, 
			s.total_budget, s.daily_budget, s.cpc, s.price_type, s.xml_custom
			FROM '._DB_PREFIX_.'product p
			LEFT JOIN '._DB_PREFIX_.'product_lang pl ON
			(pl.id_product = p.id_product and pl.id_lang = "'.(int)$this->langId.'")
			LEFT JOIN '._DB_PREFIX_.'image im ON
			(im.id_product = p.id_product and im.cover = 1)
			LEFT JOIN '._DB_PREFIX_.'blmod_xml_product_settings s ON
			(s.product_id = p.id_product AND s.package_id = "'.(int)$packageId.'")
			'.$where.'
			GROUP BY p.id_product
			ORDER BY p.id_product DESC
			LIMIT '.(int)$limitFrom.', '.(int)XmlFeedsTools::ITEM_IN_PAGE);
    }

    public function save()
    {
        $totalBudget = Tools::getValue('total_budget');
        $dailyBudget = Tools::getValue('daily_budget');
        $cpc = Tools::getValue('cpc');
        $priceType = Tools::getValue('price_type');
        $xmlCustom = Tools::getValue('xml_custom');
        $packageId = htmlspecialchars(Tools::getValue('product_setting_package_id'), ENT_QUOTES);

        foreach ($totalBudget as $id => $total) {
            $id = htmlspecialchars($id, ENT_QUOTES);
            Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_product_settings WHERE product_id = "'.(int)$id.'" AND package_id = "'.(int)$packageId.'"');

            Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'blmod_xml_product_settings
                (`product_id`, `package_id`, `total_budget`, `daily_budget`, `cpc`, `price_type`, `xml_custom`, `updated_at`)
                VALUES
                ("'.(int)$id.'", "'.(int)$packageId.'", "'.pSQL($total).'", "'.pSQL($dailyBudget[$id]).'", 
                "'.pSQL($cpc[$id]).'", "'.pSQL($priceType[$id]).'", "'.pSQL($xmlCustom[$id]).'", "'.pSQL(date('Y-m-d H:i:s')).'")');
        }

        return true;
    }
}
