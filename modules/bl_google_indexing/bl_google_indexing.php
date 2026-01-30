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

class Bl_Google_Indexing extends Module
{
    const CSS_VERSION = 'v22';

    protected $langId = 0;
    protected $moduleImgPath = 0;

    /**
     * @var NotificationShort
     */
    protected $notification;

    public function __construct()
    {
        $this->tab = 'export';
        $this->name = 'bl_google_indexing';
        $this->version = '1.0.3';
        $this->module_key = 'c7492556c2cce459cb16d07ca8d2d606';
        $this->author = 'Bl Modules';
        $this->moduleImgPath = '../modules/' . $this->name . '/views/img/';

        parent::__construct();

        $this->displayName = $this->l('Google Indexing API');
        $this->description = $this->l('Indexing API allows directly notify Google when pages are updated or added');
        $this->confirmUninstall = $this->l('Are you sure you want to delete the module?');
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('actionAdminControllerSetMedia') ||
            !$this->registerHook('actionProductUpdate')) {
            return false;
        }

        $sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'blmod_indexing_api_log (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `url` VARCHAR(1000) CHARACTER SET utf8mb4 NOT NULL,
            `action` VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL,
            `response_phrase` VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL,
            `response_message` TEXT CHARACTER SET utf8mb4 NOT NULL,
            `created_at` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4';

        Db::getInstance()->Execute($sql);

        $settings = [
            'product_indexing' => 1,
            'requests_per_day' => 200,
        ];

        Configuration::updateValue('BLMOD_INDEXING_SETTINGS', htmlspecialchars(json_encode($settings), ENT_QUOTES));

        return true;
    }

    public function uninstall()
    {
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_indexing_api_log');
        Configuration::deleteByName('BLMOD_INDEXING_SETTINGS');

        return parent::uninstall();
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        if (Tools::getValue('configure') != 'bl_google_indexing' && Tools::getValue('controller') != 'AdminProducts') {
            return false;
        }

        $this->context->controller->addJS($this->_path.'views/js/admin_'.self::CSS_VERSION.'.js', 'all');
        $this->context->controller->addCSS($this->_path.'views/css/style_admin_'.self::CSS_VERSION.'.css', 'all');
        $this->context->controller->addCSS($this->_path.'views/css/bl_google_indexing_'.self::CSS_VERSION.'.css', 'all');

        if (Tools::getValue('configure') != 'bl_google_indexing') {
            return false;
        }

        if (_PS_VERSION_ < 1.7) {
            $this->context->controller->addCSS($this->_path.'views/css/ps16.css', 'all');
        }

        if (_PS_VERSION_ < 1.6) {
            $this->context->controller->addCSS($this->_path.'views/css/style_admin_ps_old.css', 'all');
            $this->context->controller->addCSS($this->_path.'views/css/admin-theme.css', 'all');
        }

        return true;
    }

    public function getContent()
    {
        include_once(dirname(__FILE__).'/NotificationIndexing.php');
        $this->notification = new NotificationIndexing();

        $this->loadClass();
        $this->catchSave();

        $this->smarty->assign([
            '_PS_VERSION_' => _PS_VERSION_,
            'version' => $this->version,
            'moduleImgPath' => $this->moduleImgPath,
            'notifications' => $this->notification->getMessages(),
            'displayName' => $this->displayName,
            'contentHtml' => $this->getSettingsPage(),
        ]);

        return $this->displaySmarty('views/templates/admin/body.tpl');
    }

    public function catchSave()
    {
        $updateSettingsAction = Tools::getValue('update_settings');

        if (empty($updateSettingsAction)) {
            return false;
        }

        $settings = [];
        $settings['product_indexing'] = (int)Tools::getValue('product_indexing');
        $settings['combination_indexing'] = (int)Tools::getValue('combination_indexing');
        $settings['product_lang_id'] = Tools::getValue('product_lang_id');
        $settings['json_api_key'] = !empty($_POST['json_api_key']) ? trim($_POST['json_api_key']) : ''; //Sorry, we cant use here Tools::getValue
        $settings['requests_per_day'] = Tools::getValue('requests_per_day');

        Configuration::updateValue('BLMOD_INDEXING_SETTINGS', htmlspecialchars(json_encode($settings), ENT_QUOTES));

        $this->notification->addConf($this->l('Settings successfully updated'));

        return true;
    }

    public function getSettingsPage()
    {
        $link = new Link();
        $indexingApiLog = new IndexingApiLog();
        $logPageUrl = htmlspecialchars(Tools::getValue('log_page_url'), ENT_QUOTES);

        $languages = Db::getInstance()->ExecuteS('SELECT l.id_lang, l.name 
			FROM '._DB_PREFIX_.'lang l');

        $this->smarty->assign([
            'requestUri' => $_SERVER['REQUEST_URI'],
            'languages' => $languages,
            'settings' => $this->getSettings(),
            'APIURL' => $link->getModuleLink($this->name, 'api'),
            'logs' => $indexingApiLog->getLogs($logPageUrl),
            'logsRowsLimit' => ($indexingApiLog->countLogsTotal() > IndexingApiLog::LOG_ROWS_LIMIT) ? IndexingApiLog::LOG_ROWS_LIMIT : 0,
            'logPageUrl' => $logPageUrl,
        ]);

        return $this->displaySmarty('views/templates/admin/page/settings.tpl');
    }

    public function getSettings()
    {
        $settings = json_decode(htmlspecialchars_decode(Configuration::get('BLMOD_INDEXING_SETTINGS')), true);

        $settings['product_lang_id'] = !empty($settings['product_lang_id']) ? $settings['product_lang_id'] : [];
        $settings['product_indexing'] = !empty($settings['product_indexing']) ? $settings['product_indexing'] : 0;
        $settings['combination_indexing'] = !empty($settings['combination_indexing']) ? $settings['combination_indexing'] : 0;
        $settings['json_api_key'] = !empty($settings['json_api_key']) ? $settings['json_api_key'] : '';
        $settings['requests_per_day'] = !empty($settings['requests_per_day']) ? $settings['requests_per_day'] : '';

        return $settings;
    }

    /**
     * After update product
     *
     * @param array $params
     */
    public function hookActionProductUpdate($params)
    {
        if (empty($params['id_product'])) {
            return;
        }

        $this->loadClass();
        $indexingApi = new IndexingApi();

        $settings = $this->getSettings();

        if (empty($settings['product_lang_id']) || empty($settings['product_indexing'])) {
            return;
        }

        foreach ($settings['product_lang_id'] as $langId) {
            $indexingApi->sendAfterProductUpdate($params['id_product'], $langId, $settings);
        }
    }

    public function displaySmarty($path)
    {
        $this->smarty->assign('tpl_dir', _PS_MODULE_DIR_.$this->name.'/');

        return $this->display(__FILE__, $path);
    }

    protected function loadClass()
    {
        $this->langId = (int)Configuration::get('PS_LANG_DEFAULT');

        include_once(dirname(__FILE__).'/vendor/google_api/autoload.php');
        include_once(dirname(__FILE__).'/IndexingApi.php');
        include_once(dirname(__FILE__).'/IndexingApiLog.php');
    }
}
