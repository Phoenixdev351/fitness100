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
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.Biz, Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Feed.Biz, Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Feed.Biz, Ltd. a l'adresse: contact@feed.biz
 * ...........................................................................
 *
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F, Tak Shing House - Theatre
 *     Lane - 20 des Voeux Road Central, -Central, Hong Kong
 * @license   Commercial license
 * Support by mail  :  support@feed.biz
 */

/**
 * Class Feedbiz
 */
class Feedbiz extends Module
{
    const MODULE_NAME = 'feedbiz';
    const ADD = 'a';
    const REMOVE = 'd';
    const UPDATE = 'u';
    const FIELD_DESCRIPTION_LONG = 1;
    const FIELD_DESCRIPTION_SHORT = 2;
    const DEFAULT_PRODUCTS_LIMIT = 500;
    const DEFAULT_OFFERS_LIMIT = 1000;
    const LENGTH_AMAZON_BULLET_POINT = 500;
    const TABLE_MARKETPLACE_ORDERS = 'marketplace_orders';
    const TABLE_FEEDBIZ_ORDERS = 'feedbiz_orders';
    const TABLE_FEEDBIZ_OPTIONS = 'feedbiz_product_option';
    const TABLE_FEEDBIZ_AMAZON = 'feedbiz_amazon_options';
    const TABLE_FEEDBIZ_EBAY = 'feedbiz_ebay_options';
    const TABLE_FEEDBIZ_CDISCOUNT = 'feedbiz_cdiscount_options';
    const TABLE_FEEDBIZ_FNAC = 'feedbiz_fnac_options';
    const TABLE_FEEDBIZ_MIRAKL = 'feedbiz_mirakl_options';
    const TABLE_FEEDBIZ_RAKUTEN = 'feedbiz_rakuten_options';
    const TABLE_FEEDBIZ_CONFIGURATION = 'feedbiz_configuration';
    /**
     * @var int
     */
    public $id_lang;
    /**
     * @var bool
     */
    public $ps16x;
    /**
     * @var string
     */
    public $path;
    /**
     * @var string
     */
    public $url;
    /**
     * @var string
     */
    public $js;
    /**
     * @var array
     */
    public static $amazon_regions = array(
        'amazon.de' => 'de',
        'amazon.fr' => 'fr',
        'amazon.es' => 'es',
        'amazon.it' => 'it',
        'amazon.co.uk' => 'uk',
        'amazon.com' => 'us',
        'amazon.com.mx' => 'mx',
        'amazon.co.jp' => 'jp',
        'amazon.cn' => 'cn',
        'amazon.ca' => 'ca',
        'amazon.in' => 'in',
        'amazon.nl' => 'nl',
        'amazon.se' => 'se',
        'amazon.pl' => 'pl',
        'amazon.be' => 'be'
    );
    /**
     * @var array
     */
    public static $amazon_features;
    /**
     * @var array
     */
    public static $ebay_regions = array(
        'ebay.fr' => 'fr',
        'ebay.co.uk' => 'uk',
        'ebay.de' => 'de',
        'ebay.it' => 'it',
        'ebay.es' => 'es',
        'ebay.ch' => 'ch',
        'ebay.at' => 'at',
        'befr.ebay.be' => 'be-fr',
        'benl.ebay.be' => 'be-nl',
        'ebay.nl' => 'nl',
        'ebay.ie' => 'ie',
        'ebay.pl' => 'pl',
        'ebay.se' => 'se',
        'ebay.ca' => 'ca',
        'cafr.ebay.ca' => 'ca-fr',
        'ebay.com' => 'us'
    );
    /**
     * @var array
     */
    public static $cdiscount_regions = array(
        'cdiscount.com' => 'fr'
    );
    /**
     * @var array
     */
    public static $fnac_regions = array(
        'fnac.com' => 'fr',
        'fnac.es' => 'es',
        'fnac.pt' => 'pt',
        'fr.fnac.be' => 'be'
    );
    public static $rakuten_regions = array(
        'fr.shopping.rakuten.com' => 'fr',
    );
    /**
     * @var array for cdiscount flux survey
     */
    public static $survey_marketplaces = array(
        'AliExpress',
        'Amazon',
        'Amazon.com',
        'Amazon.de',
        'Amazon.es',
        'Amazon.it',
        'Darty',
        'eBay',
        'FNAC',
        'RAKUTEN',
        'Galeries Lafayette',
        'La Redoute',
        'Pixmania',
        'Priceminister',
        'Rue du commerce',
        'Webmarchand',
        'Zalando',
    );

    /**
     * @var array
     */
    public static $mirakl_regions = array();
    /**
     * @var array
     */
    public static $channel_colors = array(
        '1' => 'navy',
        '2' => 'pink',
        '3' => 'green',
        '4' => 'silver',
        '5' => 'orange',
        '6' => 'blue',
        '9' => 'yellow'
    );
    /**
     * @var array
     */
    public static $channels = array(
        '1' => '',
        '2' => 'amazon',
        '3' => 'ebay',
        '4' => '',
        '5' => 'cdiscount',
        '6' => 'mirakl',
        '7' => '',
        '8' => 'rakuten',
        '9' => 'fnac'
    );
    /**
     * @var string
     */
    private $html = '';
    /**
     * @var array
     */
    private $post_errors = array();
    /**
     * @var array
     */
    private $vars = array(
        'username' => array(
            'name' => 'username',
            'required' => false,
            'configuration' => 'FEEDBIZ_USERNAME'
        ),
        'feedbiz_token' => array(
            'name' => 'feedbiz_token',
            'required' => true,
            'configuration' => 'FEEDBIZ_TOKEN'
        ),
        'preproduction' => array(
            'name' => 'preproduction',
            'required' => false,
            'configuration' => 'FEEDBIZ_PREPRODUCTION'
        ),
        'expert' => array(
            'name' => 'expert',
            'required' => false,
            'configuration' => 'FEEDBIZ_EXPERT'
        ),
        'debug' => array(
            'name' => 'debug',
            'required' => false,
            'configuration' => 'FEEDBIZ_DEBUG'
        ),
        'orderstate' => array(
            'name' => 'order state',
            'required' => true,
            'configuration' => 'FEEDBIZ_ORDERS_STATES'
        ),
        'forceimport' => array(
            'name' => 'forceimport',
            'required' => false,
            'configuration' => 'FEEDBIZ_FORCEIMPORT'
        ),
        'auto_create' => array(
            'name' => 'auto_create',
            'required' => false,
            'configuration' => 'FEEDBIZ_AUTO_CREATE'
        ),
        'taxes' => array(
            'name' => 'Use Taxes',
            'required' => false,
            'default' => true,
            'configuration' => 'FEEDBIZ_USE_TAXES'
        ),
        'specials' => array(
            'name' => 'Use Specials',
            'required' => false,
            'default' => true,
            'configuration' => 'FEEDBIZ_USE_SPECIALS'
        ),
        'image_type' => array(
            'name' => 'Image Type',
            'required' => false,
            'configuration' => 'FEEDBIZ_IMAGE_TYPE'
        ),
        'description_field' => array(
            'name' => 'Description Field',
            'required' => false,
            'configuration' => 'FEEDBIZ_DECRIPTION_FIELD'
        ),
        'export_limit_per_page' => array(
            'name' => 'Limit per page',
            'required' => false,
            'configuration' => 'FEEDBIZ_EXPORT_LIMIT_PER_PAGE',
            'pattern' => 'isUnsignedInt'
        ),
        'warehouse' => array(
            'name' => 'Warehouse',
            'required' => false,
            'configuration' => 'FEEDBIZ_WAREHOUSE'
        ),
        'carrier' => array(
            'name' => 'Carrier',
            'configuration' => 'FEEDBIZ_CARRIER',
            'required' => true,
        ),
    );
    /**
     * @var array
     */
    private $config = array(
        'FEEDBIZ_CONTEXT_DATA' => null,
        'FEEDBIZ_CURRENT_VERSION' => null,
        'FEEDBIZ_LAST_IMPORT' => null,
        'FEEDBIZ_LAST_EXPORT' => null,
        'FEEDBIZ_LAST_CREATE' => null,
        'FEEDBIZ_LAST_CREATE_CRON' => null,
        'FEEDBIZ_LAST_CREATE_URL' => null,
        'FEEDBIZ_LAST_UPDATE' => null,
        'FEEDBIZ_LAST_UPDATE_CRON' => null,
        'FEEDBIZ_LAST_UPDATE_URL' => null,
        'FEEDBIZ_PS_TOKEN' => null,
        'FEEDBIZ_PROFILES' => null,
        'FEEDBIZ_PROFILES_CATEGORIES' => null,
        'FEEDBIZ_CUSTOMER_ID' => null,
        'FEEDBIZ_FILTER_MANUFACTURERS' => null,
        'FEEDBIZ_FILTER_SUPPLIERS' => null,
        'FEEDBIZ_PRODUCTS_EXPORT_CONTEXT' => null,
        'FEEDBIZ_OFFERS_EXPORT_CONTEXT' => null,
        'FEEDBIZ_WAREHOUSE' => null,
        'FEEDBIZ_DOMAIN' => null,
        'FEEDBIZ_PRODUCT_OPTION_FIELDS' => null,
        'FEEDBIZ_OPTION_FIELDS_AMAZON' => null,
        'FEEDBIZ_OPTION_FIELDS_EBAY' => null,
        'FEEDBIZ_OPTION_FIELDS_CDISCOUNT' => null,
        'FEEDBIZ_OPTION_FIELDS_FNAC' => null,
        'FEEDBIZ_OPTION_FIELDS_MIRAKL' => null,
        'FEEDBIZ_OPTION_FIELDS_RAKUTEN' => null,
        'FEEDBIZ_AMAZON_ACTIVES' => null,
        'FEEDBIZ_EBAY_ACTIVES' => null,
        'FEEDBIZ_MARKETPLACE_TAB' => null,
        'FEEDBIZ_FEATURES' => null,
        'FEEDBIZ_ID_EMPLOYEE' => null,
        'FEEDBIZ_CUSTOMER_GROUP' => null
    );

    /**
     * @var null
     */
    private $is_cdiscount = false;

    /**
     * @var null
     */
    private $is_feedbiz = false;

    /**
     * @var null
     */
    private $categories;
    /**
     * @var null
     */
    public static $debug_mode;
    /**
     * @var array
     */
    public static $feedbiz_domains = array(
        'client.feed.biz',
        'qa.feed.biz',
        'dev.feed.test'
    );
    /**
     * @var
     */
    public $images;
    public $branded_module = false;

    /**
     * Feedbiz constructor.
     */
    public function __construct()
    {
        require_once(dirname(__FILE__).'/classes/feedbiz.configuration.class.php');
        require_once(dirname(__FILE__).'/classes/feedbiz.certificates.class.php');

        $this->name = 'feedbiz';
        $this->author = 'Feed.biz';
        $this->tab = 'market_place';
        $this->version = '1.2.93';

        $this->need_instance = 1;
        $this->bootstrap = true;

        $marketplace = null;
        if (file_exists(dirname(__FILE__).'/marketplace.cfg')) {
            $marketplace = Tools::file_get_contents(dirname(__FILE__).'/marketplace.cfg');
            $marketplace = str_replace(array("\r", "\n"), '', $marketplace);
        }
        $this->author_address = '0x96116FE33A6268AE9E878Dbc609A02BdCcc285E0';
        $this->module_key = '32605ba90d0d330d5ee562c87807b65f';
        //cdiscount key = '649659ee68c6a273fd0cd6fd0b9bb717'
        //cdiscount author address = '0x96116FE33A6268AE9E878Dbc609A02BdCcc285E0';

        if ($marketplace == "cdiscount") {
            $this->is_cdiscount = true;
        } else {
            $this->is_feedbiz = true;
        }

        $this->branded_module = Tools::strlen($marketplace) ? $marketplace : null;

        parent::__construct();

        $this->page = basename(__FILE__, '.php');

        if ($this->branded_module) {
            $branded_node = $this->branded_module == 'cdiscount' ? 'Flux' : $this->l('by Feed.Biz');

            $this->displayName = sprintf('%s %s', Tools::ucfirst($this->branded_module), $branded_node);
            $this->description = sprintf(
                $this->l('Manages products and offers of %s.'),
                Tools::ucfirst($this->branded_module)
            );
        } else {
            $this->displayName = $this->l('Feed.Biz');
            $this->description = $this->l('Manages products and offers on Feed.biz.');
        }

        $this->url = __PS_BASE_URI__.basename(_PS_MODULE_DIR_).'/'.$this->name.'/';
        $this->path = _PS_MODULE_DIR_.$this->name.'/';

        $this->images = $this->url.'views/img/';
        $this->js = $this->url.'views/js/';
        $this->css = $this->url.'views/css/';

        if ((defined('PS_ADMIN_DIR') || defined('_PS_ADMIN_DIR_'))) {
            require_once(_PS_MODULE_DIR_.'feedbiz/classes/feedbiz.tools.class.php');
            require_once(_PS_MODULE_DIR_.'feedbiz/classes/feedbiz.webservice.class.php');

            if (FeedbizTools::moduleIsInstalled($this->name)) {
                if (!$this->active) {
                    $this->warning = $this->l(
                        'Be careful, your module is inactive, this mode stops all pending operations for this module, '.
                        'please change the status to active in your module list'
                    );
                }

                if (!is_writeable($this->path)) {
                    $this->warning = sprintf(
                        '% (%s)',
                        $this->l('The export directory is not writeable... please fix permissions'),
                        $this->path
                    );
                }

                if (!function_exists('curl_init')) {
                    $this->warning = $this->l('PHP cURL must be installed for this module working...');
                }

                if (!Configuration::get('PS_SHOP_ENABLE')) {
                    $this->warning = $this->l(
                        'Be careful, your shop is in maintenance mode, the module could not work in that mode'
                    );
                }
            }
        }

        // Backward compatibility
//        if (_PS_VERSION_ < '1.5') {
//            require_once _PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php';
//        }

        $this->initContext();
    }

    private function initContext()
    {
//        AdminController::initShopContext();
        $this->ps16x = version_compare(_PS_VERSION_, '1.6', '>=');
        $this->context = Context::getContext();
        $this->id_lang = Tools::getValue('id_lang') ?: (isset(Context::getContext()->language->id) ?
            Context::getContext()->language->id : Configuration::get('PS_LANG_DEFAULT'));

        Feedbiz::$debug_mode = (bool)Tools::getValue('debug', Configuration::get('FEEDBIZ_DEBUG'));
    }

    /**
     * @return bool
     */
    public function install()
    {
        $pass = true;

        foreach ($this->config as $key => $value) {
            if ($value == null) {
                continue;
            }

            if (!Configuration::updateValue($key, (is_array($value) ? serialize($value) : $value))) {
                $this->post_errors[] = sprintf(
                    '%s - key: %s, value: %s',
                    $this->l('Unable to install : Some configuration values'),
                    $key,
                    nl2br(print_r($value, true))
                );
                $pass = false;
            }
        }

        foreach ($this->vars as $var) {
            Configuration::updateValue($var['configuration'], (!empty($var['default']) ? $var['default'] : ''));
        }

        Configuration::updateValue('FEEDBIZ_PS_TOKEN', md5(time() + rand()));
        Configuration::updateValue('FEEDBIZ_DOMAIN', 'https://client.feed.biz');
        Configuration::updateValue('FEEDBIZ_ID_EMPLOYEE', (int)$this->context->employee->id);
        Configuration::updateValue('FEEDBIZ_CUSTOMER_GROUP', (int)Configuration::get('PS_CUSTOMER_GROUP'));
        Configuration::updateValue('FEEDBIZ_CARRIER', (int)Db::getInstance()->getValue(
            'SELECT `id_carrier`
            FROM `'._DB_PREFIX_.'carrier`
            WHERE `active`  = 1
            AND `deleted` = 0'
        ));

        if (!parent::install()) {
            $this->post_errors[] = $this->l('Unable to install: parent()');
            $pass = false;
        }

        if (!$this->addTables()) {
            $this->post_errors[] = $this->l('Unable to install: _addTables()');
            $pass = false;
        }

        if (!$this->createCustomer()) {
            $this->post_errors[] = $this->l('Unable to install: createCustomer()');
            $pass = false;
        }

        FeedbizConfiguration::updateValue('FEEDBIZ_CATEGORIES', FeedbizTools::arrayColumn(
            Db::getInstance()->executeS('SELECT `id_category` FROM `'._DB_PREFIX_.'category`'),
            'id_category'
        ));

        $this->manageOrderStates();

        $this->hookSetup(self::ADD);

        if ($this->branded_module) {
            if (_PS_VERSION_ > '1.5') {
                Tools::copy(
                    dirname(__FILE__).'/views/img/marketplaces/cdiscount.png',
                    dirname(__FILE__).'/logo.png'
                );
            }
        }

        return $pass;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        $pass = true;

        if (!parent::uninstall()) {
            $pass = false;
        }

        // UnInstall Hooks
        $this->hookSetup(self::REMOVE);

        if (!$this->removeTables()) {
            $this->post_errors[] = $this->l('Unable to uninstall:  Tables') && $pass = false;
        }

        if (!$this->deleteCustomer()) {
            $this->post_errors[] = $this->l('Unable to install: deleteCustomer') && $pass = false;
        }

        foreach ($this->vars as $var) {
            Configuration::deleteByName($var['configuration']);
        }

        foreach (array_keys($this->config) as $key) {
            if (!Configuration::deleteByName($key)) {
                $pass = $pass && false;
            }
        }

        return ($pass);
    }

    public function versionCheck()
    {
        $currentVersion = Configuration::get('FEEDBIZ_CURRENT_VERSION');

        $this->context->smarty->assign(
            'feedbiz_current_version',
            $currentVersion
        );
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $this->context->smarty->caching = false;
        $this->context->smarty->force_compile = true;

        if (Tools::isSubmit('validateForm') && !$this->post_errors) {
            $this->postProcess();
        } elseif (Tools::getValue('is_export') != "") {
            $this->postProcessSwitchMode();
        }

        if (Tools::getValue('feedbiz_mode') != "") {
            $this->postProcessFeedbizMode();
        }

        $this->context->controller->addjqueryPlugin('validate');
        $this->context->controller->addJS(_PS_JS_DIR_.'jquery/plugins/validate/localization/messages_'.$this->context->language->iso_code.'.js');

        $this->displayForm();

        return $this->html;
    }

    private function postProcessSwitchMode()
    {
        /* Switch  mode, FEEDBIZ_EXPERT = 1 : export mode, FEEDBIZ_EXPERT = 0 : simple mode */
        /* must be cdiscount only */
        if (Tools::getValue('is_export') == 0 && $this->is_cdiscount) {
            Configuration::updateValue('FEEDBIZ_EXPERT', 0);
        } else {
            Configuration::updateValue('FEEDBIZ_EXPERT', 1);
        }
    }

    private function postProcessFeedbizMode()
    {
        /* switch mode, FEEDBIZ_MODE = 1 : export mode, FEEDBIZ_MODE = 0 : simple mode */
        if (Tools::getValue('feedbiz_mode') == 0 && $this->is_feedbiz) {
            Configuration::updateValue('FEEDBIZ_MODE', 0);
        } else {
            Configuration::updateValue('FEEDBIZ_MODE', 1);
        }
    }

    /**
     *
     */
    private function postProcess()
    {
        foreach ($this->vars as $key => $value) {
            $cfg = Tools::getValue($key);

            //only expert mode.
            if (in_array($key, array('feedbiz_token'))) {
                continue;
            }

            if ($key == 'expert') {
                continue;
            }

            if ($value['required'] && $cfg == null) {
                $this->post_errors[] = $this->l(ucwords($value['name'])).' '.$this->l(' is required');
            } elseif (!empty($value['pattern']) && !call_user_func(array('Validate', $value['pattern']), $cfg)) {
                $this->post_errors[] = $this->l(ucwords($value['name'])).' '.$this->l(' is not valid');
            } else {
                if (!empty($value['function'])) {
                    $this->{$value['function']}();
                }

                if (!is_array($cfg)) {
                    Configuration::updateValue($value['configuration'], $cfg);
                } else {
                    Configuration::updateValue($value['configuration'], serialize($cfg));
                }
            }
        }

        Configuration::updateValue('FEEDBIZ_ID_EMPLOYEE', Tools::getValue('employee', $this->context->employee->id));
        Configuration::updateValue('FEEDBIZ_CUSTOMER_GROUP', Tools::getValue('id_group'));

        $excluded_manufacturers = Tools::getValue('excluded-manufacturers');
        if (!is_array($excluded_manufacturers)) {
            FeedbizConfiguration::updateValue('FEEDBIZ_FILTER_MANUFACTURERS', $excluded_manufacturers);
        } else {
            FeedbizConfiguration::updateValue('FEEDBIZ_FILTER_MANUFACTURERS', serialize($excluded_manufacturers));
        }

        $selected_suppliers = Tools::getValue('selected-suppliers');
        if (!is_array($selected_suppliers)) {
            FeedbizConfiguration::updateValue('FEEDBIZ_FILTER_SUPPLIERS', $selected_suppliers);
        } else {
            FeedbizConfiguration::updateValue('FEEDBIZ_FILTER_SUPPLIERS', serialize($selected_suppliers));
        }

        FeedbizConfiguration::updateValue('FEEDBIZ_CATEGORIES', Tools::getValue('category'));

        require_once dirname(__FILE__).'/classes/feedbiz.context.class.php';

        FeedbizContext::save($this->context);

        if (!$this->addTables()) {
            $this->post_errors[] = $this->l('Unable to install: _addTables()');
        }

        // Install Hooks
        $this->hookSetup(self::ADD);

        if (!count($this->post_errors)) {
            $this->html .= $this->displayConfirmation($this->l('Configuration has been saved'));
        } else {
            foreach ($this->post_errors as $err) {
                $this->html .= $this->displayError($err);
            }
        }

        Configuration::updateValue('FEEDBIZ_CURRENT_VERSION', $this->version);

        if (Configuration::get('FEEDBIZ_DEBUG')) {
            $this->html .= sprintf(
                'Memory Peak: %.02f MB - Post Count: %s',
                memory_get_peak_usage() / 1024 / 1024,
                count($_POST, COUNT_RECURSIVE)
            );
        }

        // Customer Id
        $customer_id = Configuration::get('FEEDBIZ_CUSTOMER_ID');
        if (!isset($customer_id) || empty($customer_id) || ! $customer_id) {
            if (!$this->createCustomer()) {
                $this->post_errors[] = $this->l('Unable to install: createCustomer()');
            }
        }

        $customer = new Customer($customer_id);
        if (!Validate::isLoadedObject($customer)) {
            if (!$this->createCustomer()) {
                $this->post_errors[] = $this->l('Invalid Customer, Unable to install: createCustomer()');
            }
        }
    }

    /**
     * @return mixed|string
     */
    private function selectedTab()
    {
        return Tools::getValue('selected_tab', 'informations');
    }

    private function survey_categories()
    {
        $survey_categories = array();
        $survey_categories[83593] = $this->l('Accessoires Auto Moto');
        $survey_categories[83595] = $this->l('Accessoires électroménager');
        $survey_categories[83597] = $this->l('Accessoires High-Tech');
        $survey_categories[83599] = $this->l('Accessoires mode');
        $survey_categories[83601] = $this->l('Alcools & Boissons');
        $survey_categories[83603] = $this->l('Alimentaire');
        $survey_categories[83605] = $this->l('Aménagement maison');
        $survey_categories[83607] = $this->l('Animalerie');
        $survey_categories[83609] = $this->l('Art de la table');
        $survey_categories[83611] = $this->l('Articles de sécurité');
        $survey_categories[83613] = $this->l('Articles fumeurs');
        $survey_categories[83615] = $this->l('Articles funéraires');
        $survey_categories[83617] = $this->l('Bagages');
        $survey_categories[83619] = $this->l('Beaux-arts & Loisirs créatifs');
        $survey_categories[83621] = $this->l('Bijoux');
        $survey_categories[83623] = $this->l('Bricolage');
        $survey_categories[83625] = $this->l('CD de Musique');
        $survey_categories[83627] = $this->l('Chaussures');
        $survey_categories[83629] = $this->l('Cinéma (DVD, BD…)');
        $survey_categories[83631] = $this->l('Coffrets cadeaux');
        $survey_categories[83633] = $this->l('Compléments alimentaire');
        $survey_categories[83635] = $this->l('Construction');
        $survey_categories[83637] = $this->l('Cosmétique');
        $survey_categories[83639] = $this->l('Décoration');
        $survey_categories[83641] = $this->l('Déguisements');
        $survey_categories[83643] = $this->l('Drones & RC');
        $survey_categories[83645] = $this->l('Equipement puériculture');
        $survey_categories[83647] = $this->l('Equipements Auto & Moto');
        $survey_categories[83649] = $this->l('GPS & Electronique Embarquée');
        $survey_categories[83651] = $this->l('Gros électro');
        $survey_categories[83653] = $this->l('Image & Son');
        $survey_categories[83655] = $this->l('Informatique');
        $survey_categories[83657] = $this->l('Instruments de musique & DJ');
        $survey_categories[83659] = $this->l('Jardin');
        $survey_categories[83661] = $this->l('Jeux de bar');
        $survey_categories[83663] = $this->l('Jeux-vidéo');
        $survey_categories[83665] = $this->l('Jouets en bois');
        $survey_categories[83667] = $this->l('Librairie');
        $survey_categories[83669] = $this->l('Linge de maison');
        $survey_categories[83671] = $this->l('Lingerie');
        $survey_categories[83673] = $this->l('Literie - Matelas');
        $survey_categories[83675] = $this->l('Luminaire');
        $survey_categories[83677] = $this->l('Lunettes');
        $survey_categories[83679] = $this->l('Matériel & Equipement Sports');
        $survey_categories[83681] = $this->l('Matériel B2B');
        $survey_categories[83683] = $this->l('Matériel industriel');
        $survey_categories[83685] = $this->l('Matériel médical & Paramédical');
        $survey_categories[83687] = $this->l('Merchandising');
        $survey_categories[83689] = $this->l('Meubles');
        $survey_categories[83691] = $this->l('Montres');
        $survey_categories[83693] = $this->l('Nautisme');
        $survey_categories[83695] = $this->l('Objets connectés');
        $survey_categories[83697] = $this->l('Outillage Auto Moto');
        $survey_categories[83699] = $this->l('PAP');
        $survey_categories[83701] = $this->l('Papèterie');
        $survey_categories[83703] = $this->l('Parapharmacie');
        $survey_categories[83705] = $this->l('Parfumerie');
        $survey_categories[83707] = $this->l('Petit électro');
        $survey_categories[83709] = $this->l('Photo & Caméscope');
        $survey_categories[83711] = $this->l('Pièces détachées Auto & Moto');
        $survey_categories[83713] = $this->l('Plein air');
        $survey_categories[83715] = $this->l('Produits d\'hygiène');
        $survey_categories[83717] = $this->l('Produits de beauté');
        $survey_categories[83719] = $this->l('Sex toys');
        $survey_categories[83721] = $this->l('Téléphonie');
        $survey_categories[83723] = $this->l('Tous types jouets');
        $survey_categories[83725] = $this->l('Véhicules');
        $survey_categories[83727] = $this->l('Vêtements de sport');
        $survey_categories[83729] = $this->l('Vêtements puériculture');

        foreach ($survey_categories as $id => $value) {
            $survey_categories[$id] = html_entity_decode($value);
        }
        return $survey_categories;
    }

    /**
     *
     */
    private function displayForm()
    {
        require_once dirname(__FILE__).'/classes/shared/configure_tab.class.php';

        if ($this->categories == null) {
            $categories = Category::getCategories((int)$this->id_lang, false, true, '', 'ORDER BY c.id_category ASC');
            $this->categories = $categories;
        }

        self::$amazon_features = unserialize(Configuration::get('FEEDBIZ_AMAZON_FEATURES'));

        $url = $_SERVER['REQUEST_URI'];
        if ($this->is_cdiscount) {
            $ex_url = explode("&is_export", $url);
        } else {
            $ex_url = explode("&feedbiz_mode", $url);
        }

        $amazon_features = Configuration::get('FEEDBIZ_AMAZON_FEATURES');

        if (Tools::strlen($amazon_features)) {
            self::$amazon_features = unserialize($amazon_features);
        }

        $view_params = array();
        $view_params['request_uri'] = Tools::htmlentitiesUTF8($ex_url[0]);
        $view_params['images_url'] = $this->images;
        $view_params['js_url'] = $this->js;
        $view_params['module_url'] = $this->url;
        $view_params['id_lang'] = $this->id_lang;
        $view_params['module_display_name'] = $this->displayName;
        $view_params['module_description'] = $this->description;
        $view_params['version'] = $this->version;
        $view_params['ps_version'] = _PS_VERSION_;
        $view_params['module_path'] = dirname(__FILE__);
        //tabs params
        $view_params['selected_tab'] = $this->selectedTab();
        $view_params['selected_tab_informations'] = $view_params['selected_tab'] == 'informations' ? 'selected' : '';
        $view_params['selected_tab_credentials'] = $view_params['selected_tab'] == 'credentials' ? 'selected' : '';
        $view_params['selected_tab_categories'] = $view_params['selected_tab'] == 'categories' ? 'selected' : '';
        $view_params['selected_tab_mapping'] = $view_params['selected_tab'] == 'mapping' ? 'selected' : '';
        $view_params['selected_tab_orders'] = $view_params['selected_tab'] == 'orders' ? 'selected' : '';
        $view_params['selected_tab_settings'] = $view_params['selected_tab'] == 'settings' ? 'selected' : '';
        $view_params['selected_tab_filters'] = $view_params['selected_tab'] == 'filters' ? 'selected' : '';
        $view_params['selected_tab_cron'] = $view_params['selected_tab'] == 'cron' ? 'selected' : '';

        $view_params['feedbiz_informations'] = $this->informations();
        $view_params['feedbiz_categories'] = $this->categories();
        $view_params['feedbiz_orders'] = $this->orders();
        $view_params['feedbiz_settings'] = $this->settings();
        $view_params['feedbiz_filters'] = $this->filters();
        $view_params['feedbiz_version'] = $this->version;

        $alert_class = array();
        $alert_class['danger'] = $this->ps16x ? 'alert alert-danger' : 'error alert';
        $alert_class['warn'] = $this->ps16x ? 'alert alert-warning' : 'warn warning';
        $alert_class['success'] = $this->ps16x ? 'alert alert-success' : 'conf';
        $alert_class['info'] = $this->ps16x ? 'alert alert-info' : 'info';
        $view_params['alert_class'] = $alert_class;
        $view_params['ps16x'] = $this->ps16x;
        $view_params['filter_sep_class'] = $this->ps16x ? 'ps16sep' : 'sep';
        $view_params['ps_token'] = Configuration::get('FEEDBIZ_PS_TOKEN');

        //Debug config
        if (Configuration::get('FEEDBIZ_DEBUG')) {
            $view_params['debug'] = "TRUE";
            $view_params['feedbiz_memory_peak_usage'] = memory_get_peak_usage() / 1024 / 1024;
        }

        //Credentials
        $view_params['feedbiz_username'] = Configuration::get('FEEDBIZ_USERNAME');
        $view_params['feedbiz_feedbiz_token'] = Configuration::get('FEEDBIZ_TOKEN');

        if (($preproduction = Configuration::get('FEEDBIZ_PREPRODUCTION'))) {
            $view_params['feedbiz_preproduction_check'] = $preproduction ? ' checked="checked"' : '';
        } else {
            $view_params['feedbiz_preproduction_check'] = '';
        }

        $debug = (bool)Configuration::get('FEEDBIZ_DEBUG');
        $expert = (bool)Configuration::get('FEEDBIZ_EXPERT');
        $get_mode = (bool)Configuration::get('FEEDBIZ_MODE');
        $feedbiz_mode = ($get_mode == 1) ? 'EXPERT' : 'SIMPLE';

        if (!$this->is_cdiscount && !$expert) {
            $expert = 1;
            Configuration::updateValue('FEEDBIZ_EXPERT', $expert);
        }

        $view_params['medebug'] = $debug ? 'checked="checked"' : '';
        $view_params['feedbiz_expert'] = $expert ? 'checked="checked"' : '';
        $view_params['medebug_style'] = $debug ? ' style="color:red" ' : '';

        $module_node = $this->branded_module == 'cdiscount' ? 'Flux' : 'by Feed.biz';

        $view_params['branded_module'] = $this->branded_module;
        $view_params['branded_module_node'] = false;

        // Support widget in Modules
        $shop_name = Configuration::get('PS_SHOP_EMAIL');

        $feedbiz_xml_url = urlencode($view_params['feedbiz_informations']['feedbiz_xml_url']);

        if ($this->is_cdiscount) {
            $view_params['branded_module_node'] = ' '.$module_node;
            $view_params['url_fb_login'] = "https://cdiscount.feed.biz/users/login?email=$shop_name";
            $view_params['url_fb_register'] = "https://cdiscount.feed.biz/users/register?email=$shop_name";
            $view_params['url_fb_connector'] = "https://cdiscount.feed.biz/my_shop/configuration?connector=$feedbiz_xml_url";
            $view_params['url_fb_dahsboard'] = "https://cdiscount.feed.biz/dashboard";
        } else {
            $view_params['url_fb_login'] = "https://client.feed.biz/users/login?email=$shop_name";
            $view_params['url_fb_register'] = "https://client.feed.biz/users/register?email=$shop_name";
            $view_params['url_fb_connector'] = "https://client.feed.biz/my_shop/configuration?connector=$feedbiz_xml_url";
            $view_params['url_fb_dahsboard'] = "https://client.feed.biz/dashboard";
        }
        $view_params['url_fb_register_cdiscount'] = "https://marketplace.cdiscount.com/cdiscountflux";
        $view_params['url_fb_customer_survey'] = "https://client.feed.biz";
        $view_params['mode_use'] = $expert ? 'EXPORT' : 'SIMPLE';
        $view_params['feedbiz_mode'] = $feedbiz_mode;
        $view_params['is_cdiscount'] = $this->is_cdiscount;
        $view_params['is_feedbiz'] = $this->is_feedbiz;

        $num_product = Db::getInstance()->getValue('SELECT count(`id_product`) as count FROM `'._DB_PREFIX_.'product`');
        $num_product_attr = Db::getInstance()->getValue('SELECT count(`id_product`) as count FROM `'._DB_PREFIX_.'product_attribute`');

        $revenues = array();
        //$revenues['1'] = $this->l('No sales yet'); /* no collects */
        $revenues[1] = $this->l('Less than 10,000');
        $revenues[2] = $this->l('Between 10,000 and 50,000');
        $revenues[3] = $this->l('Between 50,000 and 100,000');
        $revenues[4] = $this->l('More than 100,000');
        $revenues[5] = $this->l('More than 1,000,000');

        $shop_domain = Configuration::get('PS_SHOP_DOMAIN');

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            if (Shop::isFeatureActive()) {
                $shop = $this->context->shop;

                if ($_SERVER['HTTP_HOST'] != $shop->domain && $_SERVER['HTTP_HOST'] != $shop->domain_ssl) {
                    $shop_domain = $shop->domain;
                }
            } else {
                $url = ShopUrl::getShopUrls($this->context->shop->id)->where('main', '=', 1)->getFirst();
                $shop_domain = $url->domain;
            }
        }


        $view_params['info'] = array();
        $view_params['info']['last_name'] = $this->context->employee->lastname;
        $view_params['info']['first_name'] = $this->context->employee->firstname;
        $view_params['info']['telephone'] = Configuration::get('PS_SHOP_PHONE');
        $view_params['info']['email'] = Configuration::get('PS_SHOP_EMAIL');
        $view_params['info']['languages'] = $languages = Language::getLanguages();
        $view_params['info']['company'] = Configuration::get('PS_SHOP_NAME');
        $view_params['info']['company_num'] = Configuration::get('PS_SHOP_DETAILS');
        $view_params['info']['web_site'] = $shop_domain;
        $view_params['info']['product_num'] = $num_product.'/'.$num_product_attr;
        $view_params['info']['country_default'] = $this->context->country->iso_code;
        $view_params['info']['countries'] = $countries = Country::getCountries($this->context->language->id);
        $view_params['info']['revenues'] = $revenues;
        $view_params['info']['revenue_default'] = null;
        $view_params['info']['categories'] = $this->survey_categories();
        $view_params['info']["category_def"] = null;
        $view_params['info']['marketplaces'] = self::$survey_marketplaces;
        $view_params['info']['marketplaces_default'] = array();
        $view_params['info']['language_default'] = null;

        if (($customer_survey_raw = FeedbizConfiguration::get('survey'))) {
            $customer_survey = unserialize($customer_survey_raw);

            if ((!$this->is_feedbiz) && is_array($customer_survey) && count($customer_survey)) {
                $view_params['info']['first_name'] = $customer_survey['survey_first_name'];
                $view_params['info']['last_name'] = $customer_survey['survey_last_name'];
                $view_params['info']['company'] = $customer_survey['survey_company'];
                $view_params['info']['company_num'] = $customer_survey['survey_company_num'];
                $view_params['info']['telephone'] = $customer_survey['survey_telephone'];
                $view_params['info']['web_site'] = $customer_survey['survey_web_site'];
                $view_params['info']['email'] = $customer_survey['survey_email'];
                $view_params['info']['product_num'] = $customer_survey['survey_product_num'];
                $view_params['info']['revenue_default'] = $customer_survey['survey_sales'];
                $view_params['info']["category_def"] = $customer_survey['survey_category'];
                $view_params['info']['marketplaces_default'] = is_array($customer_survey['survey_marketplaces']) && count($customer_survey['survey_marketplaces']) ? array_flip($customer_survey['survey_marketplaces']) : array();

                foreach ($languages as $language) {
                    if ($customer_survey['survey_language'] == $language['name']) {
                        $view_params['info']['language_default'] = $language['id_lang'];
                    }
                }
                foreach ($countries as $country) {
                    if ($customer_survey['survey_country'] == $country['id_country']) {
                        $view_params['info']['country_default'] = $country['id_country'];
                    }
                }
            }
        }

        $view_params['header']['support_requester'] = $shop_name;
        $view_params['header']['support_ps_version'] = _PS_VERSION_;
        $view_params['header']['support_module_version'] = $this->version;
        $view_params['header']['support_site'] = $_SERVER['HTTP_HOST'];
        $view_params['header']['support_subject'] = sprintf('%s %s v%s %s %s', $this->l('Support Request for'), $this->displayName, $this->version, $this->l('from'), $shop_name);
        $view_params['header']['support_product'] = '5000007082';

        $this->context->smarty->assign($view_params);

        $tabList = array();

        if ($this->is_feedbiz && $feedbiz_mode == 'SIMPLE') {
            /* feedbiz & simple mode */
            $tabList[] = array(
                'id' => 'import',
                'img' => 'connect',
                'name' => sprintf("%s %s", '1.', $this->l('Connect')),
                'selected' => true,
                'send_survey' => 1
            );
            $tabList[] = array(
                'id' => 'support',
                'img' => 'support',
                'name' => sprintf("%s %s", '2.', $this->l('Support')),
                'selected' => false,
                'send_survey' => 1
            );
        } elseif ($expert) {
            $tabList[] = array(
              'id' => 'feedbiz',
              'img' => $this->is_cdiscount ? 'cdiscount' : 'feedbiz',
              'name' => ($this->branded_module ? sprintf(
                  '%s %s',
                  Tools::ucfirst($this->branded_module),
                  $module_node
              ) : 'Feed.biz'),
              'selected' => true
          );
            $tabList[] = array(
              'id' => 'informations',
              'img' => 'information',
              'name' => 'Informations',
              'selected' => false
          );
            $tabList[] = array(
              'id' => 'categories',
              'img' => 'categories',
              'name' => $this->l('Categories'),
              'selected' => false
          );
            $tabList[] = array('id' => 'orders', 'img' => 'calculator', 'name' => $this->l('Orders'), 'selected' => false);
            $tabList[] = array(
              'id' => 'settings',
              'img' => 'cog_edit',
              'name' => $this->l('Settings'),
              'selected' => false
          );
            $tabList[] = array('id' => 'filters', 'img' => 'filter', 'name' => $this->l('Filters'), 'selected' => false);
        } elseif ($this->is_cdiscount) {
            /* simple mode */
            $tabList[] = array(
                'id' => 'info_account',
                'img' => 'info',
                'name' => sprintf("%s %s", '1.', $this->l('Informations')),
                'selected' => true
            );
            $tabList[] = array(
                'id' => 'account',
                'img' => 'account',
                'name' => sprintf("%s %s", '2.', $this->l('Cdiscount Marketplace')),
                'selected' => false,
                'send_survey' => 1
            );
            $tabList[] = array(
                'id' => 'cdiscount_flux',
                'img' => 'account2',
                'name' => sprintf("%s %s", '3.', $this->l('Cdiscount Flux')),
                'selected' => false,
                'send_survey' => 1
            );

            $tabList[] = array(
                'id' => 'import',
                'img' => 'connect',
                'name' => sprintf("%s %s", '4.', $this->l('Connect')),
                'selected' => false,
                'send_survey' => 1
            );
            $tabList[] = array(
                'id' => 'support',
                'img' => 'support',
                'name' => sprintf("%s %s", '5.', $this->l('Support')),
                'selected' => false,
                'send_survey' => 1
            );
        }

        $this->versionCheck();
        $this->html .= $this->context->smarty->fetch($this->path.'views/templates/admin/configure/header.tpl');
        $this->html .= ConfigureTab::generateTabs($tabList);
        $this->html .= $this->context->smarty->fetch($this->path.'views/templates/admin/configure/feedbiz.tpl');
    }

    /**
     * @return mixed
     * @throws PrestaShopException
     */
    public function informations()
    {
        $lang = Language::getIsoById($this->id_lang);

        // Display only if the module seems to be configured
        $display = true;

        $php_infos = array();
        $prestashop_infos = array();
        $env_infos = array();

        // PHP Configuration Check
        if (!function_exists('curl_init')) {
            $php_infos['curl'] = array();
            $php_infos['curl']['message'] = $this->l(
                'PHP cURL must be installed on this server. The module require the cURL library and can\'t work without'
            );
            $php_infos['curl']['level'] = 'error';
            $php_infos['curl']['link'] = 'http://php.net/manual/'.$lang.'/book.curl.php';
        }

        if (is_dir(_PS_MODULE_DIR_.'feedbiz/export') && !is_writable(_PS_MODULE_DIR_.'feedbiz/export')) {
            $php_infos['export_permissions']['message'] = sprintf(
                $this->l('You have to set write permissions to the %s directory and its subsequents files'),
                _PS_MODULE_DIR_.'feedbiz/export'
            );
            $php_infos['export_permissions']['level'] = 'danger';
        }

        // AJAX Checker
        $env_infos['ajax'] = array();
        $env_infos['ajax']['message'] = $this->l(
            'AJAX execution failed. Please, verify first your module configuration. '.
            'If the problem persists please send a screenshot of this page to the support.'
        );
        $env_infos['ajax']['level'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
        $env_infos['ajax']['display'] = false;
        $env_infos['ajax']['script'] = array(
            'name' => 'env_check_url',
            'url' => $this->url.'functions/check_env.php?action=ajax&pstoken='.Configuration::get('FEEDBIZ_PS_TOKEN')
        );

        // max_input_var Checker
        $env_infos['miv'] = array();
        $env_infos['miv']['message'] = sprintf(
            $this->l(
                'Your PHP configuration limits the maximum number of fields to post in a form : %s '.
                'for max_input_vars. Please ask your hosting provider to increase this limit.'
            ),
            ini_get('max_input_vars')
        );
        $env_infos['miv']['level'] = $this->ps16x ? 'alert alert-danger' : 'error';
        $env_infos['miv']['display'] = false;
        $env_infos['miv']['script'] = array('name' => 'max_input_vars');

        // URL issues
        $pass = true;

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            if (Shop::isFeatureActive()) {
                $shop = Context::getContext()->shop;

                if ($_SERVER['HTTP_HOST'] != $shop->domain && $_SERVER['HTTP_HOST'] != $shop->domain_ssl) {
                    $pass = false;
                }
            } else {
                $url = ShopUrl::getShopUrls($this->context->shop->id)->where('main', '=', 1)->getFirst();

                if ($_SERVER['HTTP_HOST'] != $url->domain && $_SERVER['HTTP_HOST'] != $url->domain_ssl) {
                    $pass = false;
                }
            }
        } elseif ($_SERVER['HTTP_HOST'] != Configuration::get('PS_SHOP_DOMAIN') &&
            $_SERVER['HTTP_HOST'] != Configuration::get('PS_SHOP_DOMAIN_SSL')
        ) {
            $pass = false;
        }

        if (!$pass) {
            $php_infos['wrong_domain']['message'] = $this->l(
                'Your are currently connected with the following domain name:'
            ).' <span style="color:navy">'.$_SERVER['HTTP_HOST'].'</span><br />'.$this->l(
                'This one is different from the main shop domain name set in "Preferences > SEO & URLs":'
            ).' <span style="color:green">'.Configuration::get('PS_SHOP_DOMAIN').'</span>';
            $php_infos['wrong_domain']['level'] = 'danger';
        }

        if (($max_execution_time = ini_get('max_execution_time')) && $max_execution_time < 120) {
            $php_infos['maintenance']['message'] = sprintf(
                $this->l(
                    'PHP value: max_execution_time recommended value is at least 120. your limit is currently set to %d'
                ),
                $max_execution_time
            );
            $php_infos['maintenance']['level'] = 'warn';
        }

        if (($php_sapi_name = php_sapi_name()) != 'apache2handler') {
            $php_infos['phphandler'] = array();
            $php_infos['phphandler']['message'] = sprintf(
                '%s: %s',
                $this->l('Unsupported PHP Handler, support will not cover PHP environment issues'),
                $php_sapi_name
            );
            $php_infos['phphandler']['level'] = 'warn';
            $php_infos['phphandler']['display'] = true;
            $php_infos['phphandler']['script'] = array('name' => 'phphandler');
        }

        if (!method_exists('Tools', 'getMemoryLimit')) {
            $memory_limit = ini_get('memory_limit');
            $unit = Tools::strtolower(Tools::substr($memory_limit, -1));
            $val = preg_replace('[^0-9]', '', $memory_limit);

            switch ($unit) {
                case 'g':
                    $val = $val * 1024 * 1024 * 1024;
                    break;
                case 'm':
                    $val = $val * 1024 * 1024;
                    break;
                case 'k':
                    $val = $val * 1024;
                    break;
                default:
                    $val = false;
            }
        } else {
            $val = Tools::getMemoryLimit();
        }

        if ($val <= 0) {
            $memory_limit = $this->l('Unknown');
        } else {
            // Switch to MB
            $memory_limit = (int)$val / (1024 * 1024);
        }

        $recommended_memory_limit = 128;

        if ($memory_limit < $recommended_memory_limit) {
            $php_infos['memory']['message'] = sprintf(
                $this->l(
                    'PHP value: memory_limit recommended value is at least %sMB. your limit is currently set to %sMB'
                ),
                $recommended_memory_limit,
                $memory_limit
            );
            $php_infos['memory']['level'] = 'warn';
        }

        if ((ini_get('suhosin.post.max_vars') && ini_get('suhosin.post.max_vars') < 10000) ||
            (ini_get('suhosin.request.max_vars') && ini_get('suhosin.request.max_vars') < 10000)
        ) {
            $php_infos['suhosin']['message'] = $this->l(
                'PHP value: suhosin/max_vars could trouble your module configuration'
            );
            $php_infos['suhosin']['level'] = 'warn';
        }

        // Prestashop Configuration Check
        if (!(int)Configuration::get('PS_SHOP_ENABLE')) {
            $prestashop_infos['maintenance']['message'] = $this->l(
                'Be careful, your shop is in maintenance mode, the module might not work in that mode'
            );
            $prestashop_infos['maintenance']['level'] = 'warn';
        }


        $filespermissions = array();

        $dirs = array(
            $this->path . 'cert',
            $this->path . 'logs',
        );

        foreach ($dirs as $dir) {
            if (!FeedbizTools::isDirWriteable($dir)) {
                $filespermissions[] = sprintf($this->l('You have to set write permissions to the %s directory'), $dir);
            }
        }

        $view_params = array();
        $view_params['max_input_vars'] = @ini_get('max_input_vars');
        $view_params['img'] = $this->images;
        $view_params['display'] = $display;
        $view_params['env_infos'] = $env_infos;
        $view_params['php_infos'] = $php_infos;
        $view_params['php_info_ok'] = !count($php_infos);
        $view_params['prestashop_infos'] = $prestashop_infos;
        $view_params['prestashop_info_ok'] = !count($prestashop_infos);
        $view_params['shop_overrides'] = FeedbizTools::getShopOverrides();
        $view_params['filespermissions'] = $filespermissions;
        $view_params['mode_dev'] = defined('_PS_MODE_DEV_') && _PS_MODE_DEV_;
        $view_params['support_informations_url'] = $this->url.'functions/check.php?id_lang='.$this->id_lang.
            '&pstoken='.Configuration::get('FEEDBIZ_PS_TOKEN');

        $channel = $this->branded_module ? $this->branded_module : 'addons';
        $module_url = FeedbizTools::getHttpHost(true, true).__PS_BASE_URI__.basename(_PS_MODULE_DIR_).'/'.$this->name;
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $view_params['products_url'] = $module_url.'/export/'.Tools::strtolower(
                FeedbizTools::getFriendlyUrl(Configuration::get('PS_SHOP_NAME'))
            ).'-products.xml';
            $view_params['feedbiz_xml_url'] = $module_url.'/functions/connector.php?channel='.$channel;
            $view_params['offers_url'] = $module_url.'/export/'.Tools::strtolower(
                FeedbizTools::getFriendlyUrl(Configuration::get('PS_SHOP_NAME'))
            ).'-offers.xml';
        } else {
            $view_params['products_url'] = $module_url.'/export/'.Tools::strtolower(
                (string)$this->context->shop->name
            ).'-products.xml';
            $view_params['feedbiz_xml_url'] = $module_url.'/functions/connector.php?channel='.$channel;
            $view_params['offers_url'] = $module_url.'/export/'.Tools::strtolower(
                (string)$this->context->shop->name
            ).'-offers.xml';
        }

        $view_params['url'] = $this->url;

        return $view_params;
    }

    /**
     * @return array
     */
    public function orders()
    {
        $this->manageOrderStates();

        $view_params = array();
        $order_states = unserialize(Configuration::get('FEEDBIZ_ORDERS_STATES'));

        $order_state_accepted = null;
        $order_state_fba = null;
        $order_state_multichannel = null;
        $order_state_sent = null;
        $order_state_delivered = null;
        $order_state_canceled = null;
        $order_state_urgent = null;

        if (is_array($order_states)) {
            $order_state_accepted = isset($order_states['FEEDBIZ_CA']) ? $order_states['FEEDBIZ_CA'] : null;
            $order_state_fba = isset($order_states['FEEDBIZ_FBA']) ? $order_states['FEEDBIZ_FBA'] : null;
            $order_state_multichannel = isset($order_states['FEEDBIZ_MC']) ? $order_states['FEEDBIZ_MC'] : null;
            $order_state_sent = isset($order_states['FEEDBIZ_CE']) ? $order_states['FEEDBIZ_CE'] : null;
            $order_state_delivered = isset($order_states['FEEDBIZ_CL']) ? $order_states['FEEDBIZ_CL'] : null;
            $order_state_canceled = isset($order_states['FEEDBIZ_CR']) ? $order_states['FEEDBIZ_CR'] : null;
            $order_state_urgent = isset($order_states['FEEDBIZ_UR']) ? $order_states['FEEDBIZ_UR'] : null;
        }

        if (!$order_state_accepted) {
            $order_state_accepted = defined('_PS_OS_PAYMENT_') ?
                _PS_OS_PAYMENT_ : (int)Configuration::get('PS_OS_PAYMENT');
        }
        if (!$order_state_fba) {
            $order_state_fba = defined('_PS_OS_PAYMENT_') ?
                _PS_OS_PAYMENT_ : (int)Configuration::get('PS_OS_PAYMENT');
        }
        if (!$order_state_multichannel) {
            $order_state_multichannel = defined('_PS_OS_PAYMENT_') ?
                _PS_OS_PAYMENT_ : (int)Configuration::get('PS_OS_PAYMENT');
        }
        if (!$order_state_sent) {
            $order_state_sent = defined('_PS_OS_SHIPPING_') ?
                _PS_OS_SHIPPING_ : (int)Configuration::get('PS_OS_SHIPPING');
        }
        if (!$order_state_delivered) {
            $order_state_delivered = defined('_PS_OS_DELIVERED_') ?
                _PS_OS_DELIVERED_ : (int)Configuration::get('PS_OS_DELIVERED');
        }
        if (!$order_state_canceled) {
            $order_state_canceled = defined('_PS_OS_CANCELED_') ?
                _PS_OS_CANCELED_ : (int)Configuration::get('PS_OS_CANCELED');
        }

        $orderStates = OrderState::getOrderStates($this->id_lang);

        $view_params['feedbiz_mapping_order_states_01'] = array();

        foreach ($orderStates as $orderState) {
            if (!(int)$orderState['id_order_state']) {
                continue;
            }
            $states_array = array();

            if ((int)$orderState['id_order_state'] == $order_state_accepted) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }

            $states_array['value'] = (int)$orderState['id_order_state'];
            $states_array['selected'] = $selected;
            $states_array['desc'] = $orderState['name'];

            $view_params['feedbiz_mapping_order_states_01'][] = $states_array;
        }

        $view_params['feedbiz_mapping_order_states_02'] = array();

        foreach ($orderStates as $orderState) {
            if (!(int)$orderState['id_order_state']) {
                continue;
            }
            if ((int)$orderState['id_order_state'] == $order_state_sent) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }
            $states_array = array();

            $states_array['value'] = (int)$orderState['id_order_state'];
            $states_array['selected'] = $selected;
            $states_array['desc'] = $orderState['name'];
            $view_params['feedbiz_mapping_order_states_02'][] = $states_array;
        }

        // Order Statuses - Delivered
        $view_params['feedbiz_mapping_order_states_03'] = array();

        foreach ($orderStates as $orderState) {
            if (!(int)$orderState['id_order_state']) {
                continue;
            }
            if ((int)$orderState['id_order_state'] == $order_state_delivered) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }
            $states_array = array();
            $states_array['value'] = (int)$orderState['id_order_state'];
            $states_array['selected'] = $selected;
            $states_array['desc'] = $orderState['name'];
            $view_params['feedbiz_mapping_order_states_03'][] = $states_array;
        }

        // Order Statuses - Cancelled
        $view_params['feedbiz_mapping_order_states_04'] = array();

        foreach ($orderStates as $orderState) {
            if (!(int)$orderState['id_order_state']) {
                continue;
            }
            if ((int)$orderState['id_order_state'] == $order_state_canceled) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }
            $states_array = array();
            $states_array['value'] = (int)$orderState['id_order_state'];
            $states_array['selected'] = $selected;
            $states_array['desc'] = $orderState['name'];
            $view_params['feedbiz_mapping_order_states_04'][] = $states_array;
        }

        // Order Statuses - Urgent
        $view_params['feedbiz_mapping_order_states_ur'] = array();

        foreach ($orderStates as $orderState) {
            if (!(int)$orderState['id_order_state']) {
                continue;
            }
            if ((int)$orderState['id_order_state'] == $order_state_urgent) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }
            $states_array = array();
            $states_array['value'] = (int)$orderState['id_order_state'];
            $states_array['selected'] = $selected;
            $states_array['desc'] = $orderState['name'];
            $view_params['feedbiz_mapping_order_states_ur'][] = $states_array;
        }

        $has_fba = self::$amazon_features instanceof stdClass && property_exists(self::$amazon_features, 'has_fba')
            && self::$amazon_features->has_fba == true;
        $has_fba_multichannel = $has_fba && property_exists(self::$amazon_features, 'has_fba_multichannel')
            && self::$amazon_features->has_fba_multichannel == true;

        if ($has_fba_multichannel) {
            // Order Statuses - Multichannel
            $view_params['feedbiz_mapping_order_states_mc'] = array();

            foreach ($orderStates as $orderState) {
                if (!(int)$orderState['id_order_state']) {
                    continue;
                }
                if ((int)$orderState['id_order_state'] == $order_state_multichannel) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
                $states_array = array();
                $states_array['value'] = (int)$orderState['id_order_state'];
                $states_array['selected'] = $selected;
                $states_array['desc'] = $orderState['name'];
                $view_params['feedbiz_mapping_order_states_mc'][] = $states_array;
            }
        } else {
            $view_params['feedbiz_mapping_order_states_mc'] = null;
        }

        $marketplace_tab = Configuration::get('FEEDBIZ_MARKETPLACE_TAB');
        if ($marketplace_tab) {
            $marketplace_tab_config = unserialize($marketplace_tab);

            if (is_array($marketplace_tab_config) && count($marketplace_tab_config)) {
                if (array_key_exists('cdiscount', $marketplace_tab_config) &&
                    Tools::strlen($marketplace_tab_config['cdiscount'])
                ) {
                    $has_fba = true;
                }
            }
        }

        if ($has_fba) {
            // Order Statuses - FBA
            $view_params['feedbiz_mapping_order_states_fba'] = array();

            foreach ($orderStates as $orderState) {
                if (!(int)$orderState['id_order_state']) {
                    continue;
                }
                if ((int)$orderState['id_order_state'] == $order_state_fba) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
                $states_array = array();
                $states_array['value'] = (int)$orderState['id_order_state'];
                $states_array['selected'] = $selected;
                $states_array['desc'] = $orderState['name'];
                $view_params['feedbiz_mapping_order_states_fba'][] = $states_array;
            }
        } else {
            $view_params['feedbiz_mapping_order_states_fba'] = null;
        }
        // Bulk Mode
        $view_params['feedbiz_forceimport'] = (int)Configuration::get('FEEDBIZ_FORCEIMPORT') ? ' checked="checked"' : '';
        // Auto create product
        $view_params['feedbiz_auto_create'] = (int)Configuration::get('FEEDBIZ_AUTO_CREATE') ? ' checked="checked"' : '';

        return $view_params;
    }

    /**
     * @return array
     */
    public function settings()
    {
        $view_params = array();
        $view_params['specials'] = Configuration::get('FEEDBIZ_USE_SPECIALS') ? 'checked="checked"' : '';
        $view_params['taxes'] = Configuration::get('FEEDBIZ_USE_TAXES') ? 'checked="checked"' : '';

        $decription_field_pre = Configuration::get('FEEDBIZ_DECRIPTION_FIELD');
        $decription_field = ($decription_field_pre ? $decription_field_pre : self::FIELD_DESCRIPTION_LONG);
        $view_params['long_description'] = self::FIELD_DESCRIPTION_LONG;
        $view_params['long_description_checked'] = $decription_field == self::FIELD_DESCRIPTION_LONG ?
            'checked="checked"' : '';
        $view_params['short_description'] = self::FIELD_DESCRIPTION_SHORT;
        $view_params['short_description_checked'] = $decription_field == self::FIELD_DESCRIPTION_SHORT ?
            'checked="checked"' : '';

        // Image Type (PS 1.5.3.1+)
        if (method_exists('ImageType', 'getImagesTypes')) {
            $image_type = Configuration::get('FEEDBIZ_IMAGE_TYPE');

            foreach (ImageType::getImagesTypes() as $imageType) {
                $image_type_option = array();

                if (!(bool)$imageType['products']) {
                    continue;
                }

                if ($imageType['name'] == $image_type) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
                $image_type_option['selected'] = $selected;
                $image_type_option['value'] = $imageType['name'];
                $image_type_option['desc'] = $imageType['name'];
                $view_params['image_types'][] = $image_type_option;
            }
        }

        // Shop Configuration
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $view_params['ps_version_gt_15_or_equal'] = '1';

            // Warehouse (PS 1.5 with Stock Management)
            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                $view_params['ps_advanced_stock_management'] = '1';
                $view_params['warehouse_options'] = array();

                foreach (Warehouse::getWarehouses(true) as $warehouse) {
                    $warehouse_array = array();
                    $selected = '';

                    if ((int)$warehouse['id_warehouse'] == (int)Configuration::get('FEEDBIZ_WAREHOUSE')) {
                        $selected = 'selected="selected"';
                    }

                    $warehouse_array['value'] = (int)$warehouse['id_warehouse'];
                    $warehouse_array['selected'] = $selected;
                    $warehouse_array['desc'] = $warehouse['name'];
                    $view_params['warehouse_options'][] = $warehouse_array;
                }
            }
        }

        //Limit per page
        $view_params['export_limit_per_page'] = Configuration::get('FEEDBIZ_EXPORT_LIMIT_PER_PAGE') ?
            Configuration::get('FEEDBIZ_EXPORT_LIMIT_PER_PAGE') : self::DEFAULT_PRODUCTS_LIMIT;
        $view_params['expert'] = Configuration::get('FEEDBIZ_EXPERT') ?
            Configuration::get('FEEDBIZ_EXPERT') : '1';

        $view_params['employee'] = array();

        $config_id_employee = (int)Configuration::get('FEEDBIZ_ID_EMPLOYEE');

        // Employee::getEmployees is displayed as deprecated in PS 1.4 ... but not in PS 1.5
        $no_employee_selected = true;
        foreach (@Employee::getEmployees() as $employee) {
            $id_employee = (int)$employee['id_employee'];

            if ($id_employee == $config_id_employee) {
                $selected = true;
                $no_employee_selected = false;
            } else {
                $selected = false;
            }

            $view_params['employee'][$id_employee] = array();
            $view_params['employee'][$id_employee]['name'] = (isset($employee['name']) ?
                $employee['name'] : sprintf('%s %s', $employee['firstname'], $employee['lastname']));
            $view_params['employee'][$id_employee]['selected'] = $selected;
        }

        if ($no_employee_selected) {
            reset($view_params['employee']);
            $first_key = key($view_params['employee']);
            $view_params['employee'][$first_key]['selected'] = 1;
        }

        // Customer groups
        $view_params['customer_groups'] = array();

        foreach (Group::getGroups($this->context->language->id, true) as $customer_group) {
            $id_group = (int)$customer_group['id_group'];
            $selected = false;

            if ($id_group == (int)Configuration::get('FEEDBIZ_CUSTOMER_GROUP')) {
                $selected = true;
            }

            $view_params['customer_groups'][$id_group]['name'] = $customer_group['name'];
            $view_params['customer_groups'][$id_group]['selected'] = $selected;
        }

        // Carriers
        $selected = (int) Configuration::get('FEEDBIZ_CARRIER');
        $carriers = Carrier::getCarriers(
            $this->context->language->id,
            false,
            false,
            false,
            null,
            version_compare(_PS_VERSION_, '1.5', '>=') ? Carrier::ALL_CARRIERS : 5
        );

        $view_params['std_carriers'] = array();

        foreach ($carriers as $carrier) {
            $carrier_array = array();
            $carrier_array['value'] = (int)$carrier['id_carrier'];
            $carrier_array['selected'] = ((int)$carrier['id_carrier'] == $selected) ? 'selected="selected"' : '';
            $carrier_array['desc'] = $carrier['name'];
            $view_params['std_carriers'][] = $carrier_array;
        }
        return $view_params;
    }

    /**
     * @return array
     */
    public function categories()
    {
        $view_params = array();

        if ($this->categories == null) {
            $categories = Category::getCategories((int)$this->id_lang, false, true, '', 'ORDER BY c.id_category ASC');
            $this->categories = $categories;
        } else {
            $categories = $this->categories;
        }

        $index = array();
        $default_categories = FeedbizConfiguration::get('FEEDBIZ_CATEGORIES');
        $all = false;

        if ($default_categories == 'all' || !isset($default_categories) || empty($default_categories) || $default_categories == '') {
            $all = true;
        }

        $categories_array = reset($categories);
        $first1 = key($categories);

        $first2 = key($categories_array);

        $first = $categories[$first1][$first2];

        $default_category = 1;

        $view_params['list'] = self::recurseCategoryForInclude(
            $index,
            $categories,
            $first,
            $default_category,
            null,
            $default_categories,
            true,
            $all
        );

        return $view_params;
    }

    /**
     * @param $indexedCategories
     * @param $categories
     * @param $current
     * @param int $id_category
     * @param null $id_category_default
     * @param array $default_categories
     * @param bool|false $init
     * @param bool|false $all
     * @return array
     */
    public function recurseCategoryForInclude(
        $indexedCategories,
        $categories,
        $current,
        $id_category = 1,
        $id_category_default = null,
        $default_categories = array(),
        $init = false,
        $all = false
    ) {
        static $done;
        static $irow;
        static $categories_table;

        $categories_table = isset($categories_table) ? $categories_table : array();

        if ((is_array($default_categories) && in_array($id_category, $default_categories)) || $all) {
            $checked = ' checked="checked"';
        } else {
            $checked = '';
        }

        if (!isset($done[$current['infos']['id_parent']])) {
            $done[$current['infos']['id_parent']] = 0;
        }
        $done[$current['infos']['id_parent']] += 1;

        $todo = sizeof($categories[$current['infos']['id_parent']]);
        $doneC = $done[$current['infos']['id_parent']];

        $level = $current['infos']['level_depth'] + 1;
        $img = ($init == true) ? 'lv1.gif' : 'lv'.$level.'_'.($todo == $doneC ? 'f' : 'b').'.gif';

        $categories_table[$id_category] = array(
            'level' => $level,
            'img_level' => $this->images.$img,
            'alt_row' => $irow++ % 2,
            'id_category_default' => $id_category_default == $id_category,
            'checked' => $checked,
            'disabled' => $init,
            'name' => Tools::stripslashes($current['infos']['name']),
            'id_parent' => isset($current['infos']['id_parent']) ? $current['infos']['id_parent'] : null //parent
        );

        if (isset($categories[$id_category])) {
            if ($categories[$id_category]) {
                foreach (array_keys($categories[$id_category]) as $key) {
                    if ($key != 'infos') {
                        self::recurseCategoryForInclude(
                            $indexedCategories,
                            $categories,
                            $categories[$id_category][$key],
                            $key,
                            $id_category_default,
                            $default_categories,
                            false,
                            $all
                        );
                    }
                }
            }
        }

        return ($categories_table);
    }

    /**
     * @return array
     */
    private function filters()
    {
        $view_params = array();
        $view_params['selected_tab'] = $this->selectedTab();
        $view_params['selected_tab_filters'] = $view_params['selected_tab'] == 'filters' ? 'selected' : '';
        $view_params['img'] = $this->images;
        $view_params['url'] = $this->url;

        $selected_manufacturers = unserialize(FeedbizConfiguration::get('FEEDBIZ_FILTER_MANUFACTURERS'));
        $selected_suppliers = unserialize(FeedbizConfiguration::get('FEEDBIZ_FILTER_SUPPLIERS'));

        // Manufacturers Filtering
        $manufacturers = Manufacturer::getManufacturers(false, $this->id_lang);

        $filtered_manufacturers = array();
        $available_manufacturers = array();

        if (is_array($manufacturers) && count($manufacturers)) {
            foreach ($manufacturers as $manufacturer) {
                if (is_array($selected_manufacturers) &&
                    in_array((string)$manufacturer['id_manufacturer'], $selected_manufacturers)
                ) {
                    continue;
                }

                $available_manufacturers[$manufacturer['id_manufacturer']] = $manufacturer['name'];
            }
            if (is_array($selected_manufacturers) && count($selected_manufacturers)) {
                foreach ($manufacturers as $manufacturer) {
                    if (is_array($selected_manufacturers) &&
                        !in_array((string)$manufacturer['id_manufacturer'], $selected_manufacturers)
                    ) {
                        continue;
                    }

                    $filtered_manufacturers[$manufacturer['id_manufacturer']] = $manufacturer['name'];
                }
            }
        }
        $view_params['manufacturers'] = array();
        $view_params['manufacturers']['available'] = $available_manufacturers;
        $view_params['manufacturers']['filtered'] = $filtered_manufacturers;

        // Suppliers Filtering
        $suppliers = Supplier::getSuppliers(false, $this->id_lang);

        $filtered_suppliers = array();
        $available_suppliers = array();

        if (is_array($suppliers) && count($suppliers)) {
            foreach ($suppliers as $supplier) {
                if (is_array($selected_suppliers) && in_array((string)$supplier['id_supplier'], $selected_suppliers)) {
                    continue;
                }

                $available_suppliers[$supplier['id_supplier']] = $supplier['name'];
            }
            if (is_array($selected_suppliers) && count($selected_suppliers)) {
                foreach ($suppliers as $supplier) {
                    if (is_array($selected_suppliers) &&
                        !in_array((string)$supplier['id_supplier'], $selected_suppliers)
                    ) {
                        continue;
                    }

                    $filtered_suppliers[$supplier['id_supplier']] = $supplier['name'];
                }
            }
        }

        $view_params['suppliers'] = array();
        $view_params['suppliers']['available'] = $available_suppliers;
        $view_params['suppliers']['filtered'] = $filtered_suppliers;

        return ($view_params);
    }

    private function manageOrderStates()
    {
        $order_states = unserialize(Configuration::get('FEEDBIZ_ORDERS_STATES'));

        $order_state_accepted = null;
        $order_state_fba = null;
        $order_state_multichannel = null;
        $order_state_sent = null;
        $order_state_delivered = null;
        $order_state_canceled = null;
        $order_state_urgent = null;

        if (is_array($order_states)) {
            $order_state_accepted = isset($order_states['FEEDBIZ_CA']) ? $order_states['FEEDBIZ_CA'] : null;
            $order_state_fba = isset($order_states['FEEDBIZ_FBA']) ? $order_states['FEEDBIZ_FBA'] : null;
            $order_state_multichannel = isset($order_states['FEEDBIZ_MC']) ? $order_states['FEEDBIZ_MC'] : null;
            $order_state_sent = isset($order_states['FEEDBIZ_CE']) ? $order_states['FEEDBIZ_CE'] : null;
            $order_state_delivered = isset($order_states['FEEDBIZ_CL']) ? $order_states['FEEDBIZ_CL'] : null;
            $order_state_canceled = isset($order_states['FEEDBIZ_CR']) ? $order_states['FEEDBIZ_CR'] : null;
            $order_state_urgent = isset($order_states['FEEDBIZ_UR']) ? $order_states['FEEDBIZ_UR'] : null;
        }

        if (!$order_state_accepted) {
            $order_state_accepted = defined('_PS_OS_PAYMENT_') ?
                _PS_OS_PAYMENT_ : (int)Configuration::get('PS_OS_PAYMENT');
        }
        if (!$order_state_fba) {
            $order_state_fba = defined('_PS_OS_PAYMENT_') ?
                _PS_OS_PAYMENT_ : (int)Configuration::get('PS_OS_PAYMENT');
        }
        if (!$order_state_multichannel) {
            $order_state_multichannel = defined('_PS_OS_PAYMENT_') ?
                _PS_OS_PAYMENT_ : (int)Configuration::get('PS_OS_PAYMENT');
        }
        if (!$order_state_sent) {
            $order_state_sent = defined('_PS_OS_SHIPPING_') ?
                _PS_OS_SHIPPING_ : (int)Configuration::get('PS_OS_SHIPPING');
        }
        if (!$order_state_delivered) {
            $order_state_delivered = defined('_PS_OS_DELIVERED_') ?
                _PS_OS_DELIVERED_ : (int)Configuration::get('PS_OS_DELIVERED');
        }
        if (!$order_state_canceled) {
            $order_state_canceled = defined('_PS_OS_CANCELED_') ?
                _PS_OS_CANCELED_ : (int)Configuration::get('PS_OS_CANCELED');
        }

        $update_order_state = serialize(
            array(
                'FEEDBIZ_CA'   => $order_state_accepted,
                'FEEDBIZ_FBA'  => $order_state_fba,
                'FEEDBIZ_MC'   => $order_state_multichannel,
                'FEEDBIZ_CE'   => $order_state_sent,
                'FEEDBIZ_CL'   => $order_state_delivered,
                'FEEDBIZ_CR'   => $order_state_canceled,
                'FEEDBIZ_UR'   => $order_state_urgent,
            )
        );

        Configuration::updateValue('FEEDBIZ_ORDERS_STATES', $update_order_state);
    }

    /*
     * HOOKS
     */

    /**
     * HOOKs SETUP for all Prestashop releases
     *
     * @param $action
     * @return bool
     * @throws PrestaShopException
     */
    private function hookSetup($action)
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $expectedHooks = array('backOfficeHeader', 'adminOrder', 'updateQuantity');
        } else {
            $expectedHooks = array(
                'DisplayBackOfficeHeader',
                'displayAdminOrder',
                'displayAdminProductsExtra',
                'actionOrderHistoryAddAfter',
                'actionEmailAddAfterContent',
                'PostUpdateOrderStatus',
                'actionUpdateQuantity'
            );
        }
        $pass = true;

        if ($action == self::ADD) {
            foreach ($expectedHooks as $expectedHook) {
                if (!$this->isRegisteredInHook($expectedHook)) {
                    if (!$this->registerHook($expectedHook)) {
                        $this->post_errors[] = $this->l('Unable to Register Hook').':'.$expectedHook;
                        $pass = false;
                    }
                }
            }
        }
        if ($action == self::REMOVE) {
            foreach ($expectedHooks as $expectedHook) {
                if ($this->isRegisteredInHook($expectedHook)) {
                    if (!$this->unregisterHook($expectedHook)) {
                        $this->post_errors[] = $this->l('Unable to Unregister Hook').':'.$expectedHook;
                        $pass = false;
                    }
                }
            }
        }

        return ($pass);
    }

    /**
     * @param $hook
     * @return bool|false|null|string
     * @see Module::isRegisteredInHook()
     */
    public function isRegisteredInHook($hook)
    {
        if (method_exists('Module', 'isRegisteredInHook')) {
            return (parent::isRegisteredInHook($hook));
        } else {
            return Db::getInstance()->getValue(
                'SELECT COUNT(*)
                FROM `'._DB_PREFIX_.'hook_module` hm
                LEFT JOIN `'._DB_PREFIX_.'hook` h ON (h.`id_hook` = hm.`id_hook`)
                WHERE h.`name` = "'.pSQL($hook).'"
                AND hm.`id_module` = '.(int)$this->id
            );
        }
    }

    /**
     * @return string
     */
    public function hookBackOfficeHeader()
    {
        return ($this->hookDisplayBackOfficeHeader());
    }

    /**
     * @param $params
     * @return string|void
     */
    public function hookAdminOrder($params)
    {
        return ($this->hookDisplayAdminOrder($params));
    }

    /*
     * HOOKS
     */

    /**
     * @return string
     */
    public function hookDisplayBackOfficeHeader()
    {
        $html = '';

        $tab = Tools::strtolower(Tools::getValue('tab'));
        $updateproduct = (Tools::getValue('addproduct') !== false || Tools::getValue('updateproduct') !== false) &&
            Tools::getValue('id_product') !== false;

        if ((version_compare(_PS_VERSION_, '1.5', '<') && $tab == 'admincatalog' && $updateproduct)) {
            $html .= '<meta name="feedbiz-options" content="'.$this->url.'functions/product_ext_feedbiz.php" />'."\n";
            $html .= '<meta name="feedbiz-options-json" content="'.$this->url.'functions/product_ext.json.php" />'."\n";

            $html .= $this->autoAddCSS($this->url.'css/product_ext.css');
            $html .= $this->autoAddJS($this->url.'js/product_extme.js');
        }

        if (version_compare(_PS_VERSION_, '1.4', '<')) {
            print($html);

            return (true);
        } else {
            return ($html);
        }
    }

    /**
     * @param $params
     * @return string|bool
     */
    public function hookDisplayAdminOrder($params)
    {
        require_once(_PS_MODULE_DIR_.$this->name.'/classes/feedbiz.order.class.php');
        require_once(_PS_MODULE_DIR_.$this->name.'/classes/feedbiz.tools.class.php');

        $id_order = (int)$params['id_order'];
        $order = new FeedBizOrder($id_order);

        // Not a FeedBiz order
        if (Tools::strtolower($order->module) != Tools::strtolower($this->name) && !isset($order->Feedbiz)) {
            return (false);
        }

        require_once(_PS_MODULE_DIR_.$this->name.'/classes/'.self::MODULE_NAME.'.admin_order.class.php');

        $adminOrder = new FeedbizAdminOrder();
        $this->html = $adminOrder->marketplaceOrderDisplay($params);

        return ($this->html);
    }

    /**
     * @param $params
     * @return void
     */
    public function hookActionOrderHistoryAddAfter($params)
    {
        // Manage MultiChannel Orders
        if (!isset($params['order_history'])) {
            return;
        }

        if (!isset($params['order_history']->id_order)) {
            if (Feedbiz::$debug_mode) {
                echo Tools::displayError(sprintf(
                    '%s: %s::%s(%d) - Empty id_order',
                    basename(__FILE__),
                    __CLASS__,
                    __FUNCTION__,
                    __LINE__
                ));
            }

            return;
        }

        $ps_os_payment = array(Configuration::get('PS_OS_PAYMENT'), Configuration::get('PS_OS_WS_PAYMENT'));
        if (!in_array($params['order_history']->id_order_state, $ps_os_payment)) {
            if (Feedbiz::$debug_mode) {
                echo Tools::displayError(sprintf(
                    '%s: %s::%s(%d) - Order not paid',
                    basename(__FILE__),
                    __CLASS__,
                    __FUNCTION__,
                    __LINE__
                ));
            }

            return;
        }

        $id_order = (int)$params['order_history']->id_order;

        require_once(dirname(__FILE__).'/classes/feedbiz.multichannel.class.php');

        $feedbizMultichannelOrder = new FeedBizMultichannel();
        $feedbizMultichannelOrderResult = $feedbizMultichannelOrder->generateOrder(
            $id_order,
            $this->context->shop->id,
            Feedbiz::$debug_mode
        );

        if ($feedbizMultichannelOrderResult instanceof DOMDocument) {
            require_once(dirname(__FILE__).'/classes/feedbiz.webservice.class.php');

            $username = Configuration::get('FEEDBIZ_USERNAME');
            $token = Configuration::get('FEEDBIZ_TOKEN');
            $preproduction = Configuration::get('FEEDBIZ_PREPRODUCTION') ? true : false;

            $FeedBizWS = new FeedBizWebService($username, $token, $preproduction, Feedbiz::$debug_mode);
            $FeedBizWS->sendOrder($feedbizMultichannelOrderResult->saveXML());
        } else {
            if (Feedbiz::$debug_mode) {
                echo Tools::displayError(sprintf(
                    '%s: %s::%s(%d) - Order is not eligible or an error occured',
                    basename(__FILE__),
                    __CLASS__,
                    __FUNCTION__,
                    __LINE__
                ));
            }
        }
    }

    public function hookActionEmailAddAfterContent($params)
    {
        if (!isset($params['id_lang'])) {
            return false;
        }

        $id_lang = $params['id_lang'];

        $feedbiz_features = unserialize(Configuration::get('FEEDBIZ_FEATURES'));

        if (!$feedbiz_features) {
            if (Feedbiz::$debug_mode) {
                echo Tools::displayError(sprintf(
                    '%s: %s::%s(%d) - Unavailable: Features',
                    basename(__FILE__),
                    __CLASS__,
                    __FUNCTION__,
                    __LINE__
                ));
            }

            return (false);
        }

        $feedbiz_features = Tools::jsonDecode(Tools::jsonEncode($feedbiz_features));

        if (!(property_exists($feedbiz_features, 'messaging'))) {
            if (Feedbiz::$debug_mode) {
                echo Tools::displayError(sprintf(
                    '%s: %s::%s(%d) - Messaging is not active',
                    basename(__FILE__),
                    __CLASS__,
                    __FUNCTION__,
                    __LINE__
                ));
            }

            return (false);
        }

        $messaging = explode(';', $feedbiz_features->messaging); // TODO

        if (!(in_array($id_lang, $messaging))) {
            if (Feedbiz::$debug_mode) {
                echo Tools::displayError(sprintf(
                    '%s: %s::%s(%d) - Messaging is not active for id lang: %s',
                    basename(__FILE__),
                    __CLASS__,
                    __FUNCTION__,
                    __LINE__
                ));
            }

            return (false);
        }

        require_once(dirname(__FILE__).'/classes/feedbiz.webservice.class.php');

        $username = Configuration::get('FEEDBIZ_USERNAME');
        $token = Configuration::get('FEEDBIZ_TOKEN');
        $preproduction = Configuration::get('FEEDBIZ_PREPRODUCTION') ? true : false;

        $fb_params = array(
            'token' => $token,
            'id_lang' => $params['id_lang'],
            'template' => $params['template'],
        );

        $FeedBizWS = new FeedBizWebService($username, $token, $preproduction, Feedbiz::$debug_mode);
        $templates = $FeedBizWS->getTemplates($fb_params, 'GET');

        if (!empty($templates)) {
            if ((property_exists($templates, 'html')) && Tools::strlen($templates->html)) {
                $params['template_html'] = (string)$templates->html;
            }

            if ((property_exists($templates, 'txt')) && Tools::strlen($templates->txt)) {
                $params['template_txt'] = (string)$templates->txt;
            }
        }
    }

    public function hookPostUpdateOrderStatus($params)
    {
        $this->hookActionOrderStatusPostUpdate($params);
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        $this->manageOrderCancelation($params);

        if (Feedbiz::$debug_mode && Tools::getValue('id_order')) {
            echo "<pre>";
            printf(
                '%s:#%d hookActionOrderStatusPostUpdate - module is in debug mode, operation stopped'."\n",
                basename(__FILE__),
                __LINE__
            );
            echo "</pre>";
            die;
        }
    }

    public function hookUpdateQuantity($params)
    {
        return ($this->hookActionUpdateQuantity($params));
    }

    public function hookActionUpdateQuantity($params)
    {
        require_once dirname(__FILE__).'/classes/feedbiz.product.class.php';

        if (isset($params['product']) && is_object($params['product'])) {
            $id_product = (int)$params['product']->id;
        } elseif (isset($params['id_product'])) {
            $id_product = (int)$params['id_product'];
        } elseif (isset($params['product']['id_product'])) {
            $id_product = (int)$params['product']['id_product'];
        } else {
            return false;
        }

        FeedBizProduct::updateProductDate($id_product);
    }

    protected function manageOrderCancelation($params)
    {
        $id_order = (int)$params['id_order'];
        $order_states = unserialize(Configuration::get('FEEDBIZ_ORDERS_STATES'));
        $canceled_state = $order_states ['FEEDBIZ_CR'];

        // Matching Order Status
        //
        if (!$canceled_state || (int)$params['newOrderStatus']->id != (int)$canceled_state) {
            return (false);
        }

        require_once(dirname(__FILE__).'/classes/feedbiz.order.class.php');

        $order_cancel = new FeedbizOrder($id_order);

        // Not an Feedbiz order
        //
        if (Tools::strtolower($order_cancel->module) != Tools::strtolower($this->name)) {
            if (Feedbiz::$debug_mode) {
                echo '<pre>';
                printf('%s - %s::%s - line #%d : ', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                echo 'Not an Feedbiz order, id: '.$id_order;
                echo '</pre>';
            }

            return (false);
        }

        $result = $order_cancel->changeOrderStatus($id_order, FeedBizOrder::TO_CANCEL);

        if (!$result && Feedbiz::$debug_mode) {
            printf(
                '%s:#%d AmazonMessaging::manageOrderCancelation(%d) failed'."\n",
                basename(__FILE__),
                __LINE__,
                $id_order
            );
        }

        return ($result);
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    protected function addTables()
    {
        $output = '';
        $pass = true;

        if (!FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_OPTIONS)) {
            $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_OPTIONS.'` (
                  `id_product` INT NOT NULL ,
                  `id_product_attribute` INT NOT NULL ,
                  `id_lang` INT NOT NULL ,
                  `force` INT NULL DEFAULT NULL,
                  `disable` TINYINT NULL DEFAULT NULL,
                  `price` FLOAT NULL DEFAULT NULL,
                  `shipping` FLOAT NULL DEFAULT NULL,
                  `text` VARCHAR(200) NULL DEFAULT NULL,
                   UNIQUE KEY `id_product` (`id_product`,`id_product_attribute`,`id_lang`)
                  ) ;';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
                $output .= 'Error on sql : ' . $sql . '<br> Error Info : <pre>' . print_r(array(
                // Simluate PDO::errorInfo()
                // @see https://www.php.net/manual/fr/pdo.errorinfo.php
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getMsgError()
            ), true) . '</pre><br>' ;
            }
        }

        if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_OPTIONS, 'id_product_attribute')) {
            $this->post_errors[] = sprintf(
                'Table %s version is wrong, you shoud backup data from this table, delete it, '.
                'the module will recreate it. aferward you will restore your data into this table',
                _DB_PREFIX_.self::TABLE_FEEDBIZ_OPTIONS
            );
        }

        // Product Options - Save available fields
        $fields = array();
        $query = Db::getInstance()->executeS('SHOW COLUMNS FROM `'._DB_PREFIX_.self::TABLE_FEEDBIZ_OPTIONS.'`');
        if ($query) {
            foreach ($query as $row) {
                $fields[] = $row['Field'];
            }
        }

        if (count($fields)) {
            Configuration::updateValue('FEEDBIZ_PRODUCT_OPTION_FIELDS', implode(',', $fields), false, 0, 0);
        }

        if (!FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS)) {
            $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS.'` (
					`id_order` INT NOT NULL ,
					`mp_order_id` VARCHAR( 32 ) NOT NULL,
					`channel_id` INT NULL DEFAULT "1",
					`channel_name` VARCHAR( 32 ) NULL DEFAULT NULL,
					`mp_reference` VARCHAR( 64 ) NOT NULL,
					`mp_number` VARCHAR( 32 ) NOT NULL,
					`mp_status` INT NULL DEFAULT NULL,
					`multichannel` VARCHAR( 32 ) NULL DEFAULT NULL,
					`shipping_type` VARCHAR( 255 ) NULL DEFAULT NULL,
					`is_prime` TINYINT(4) NULL DEFAULT NULL,
					`is_premium` TINYINT(4) NULL DEFAULT NULL,
					`is_business` TINYINT(4) NULL DEFAULT NULL,
					`earliest_ship_date` DATETIME NULL DEFAULT NULL,
					`latest_ship_date` DATETIME NULL DEFAULT NULL,
					`cancelled_reason` VARCHAR(200) NULL DEFAULT NULL,
					`fulfillment_center_id` VARCHAR(32) NULL DEFAULT NULL,
					PRIMARY KEY (  `id_order` ) ,
					UNIQUE (`mp_order_id`)
					) ;';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
                $output .= 'Error on sql : ' . $sql . '<br> Error Info : <pre>' . print_r(array(
                // Simluate PDO::errorInfo()
                // @see https://www.php.net/manual/fr/pdo.errorinfo.php
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getMsgError()
            ), true) . '</pre><br>' ;
            }
        }

        $sqls = array();

        // Alter
        if (FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS, 'mp_reference')) {
            $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS.'` MODIFY COLUMN `mp_reference` VARCHAR( 64 ) ';
        }

        // Adding new fields
        if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS, 'mp_number')) {
            $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS.'` ADD  `mp_number` VARCHAR( 32 ) NULL AFTER `mp_reference`';
        }
        if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS, 'multichannel')) {
            $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS.'` ADD  `multichannel` VARCHAR( 32 ) NULL DEFAULT NULL AFTER `mp_number`';
        }
        if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS, 'shipping_type')) {
            $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS.'` ADD  `shipping_type` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `multichannel`';
        }
        if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS, 'is_prime')) {
            $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS.'` ADD  `is_prime` TINYINT(4) NULL DEFAULT NULL AFTER `shipping_type`';
        }
        if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS, 'is_premium')) {
            $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS.'` ADD  `is_premium` TINYINT(4) NULL DEFAULT NULL AFTER `is_prime`';
        }
        if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS, 'is_business')) {
            $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS.'` ADD  `is_business` TINYINT(4) NULL DEFAULT NULL AFTER `is_premium`';
        }
        if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS, 'earliest_ship_date')) {
            $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS.'` ADD  `earliest_ship_date` DATETIME NULL DEFAULT NULL AFTER `is_prime`';
        }
        if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS, 'latest_ship_date')) {
            $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS.'` ADD  `latest_ship_date` DATETIME NULL DEFAULT NULL AFTER `earliest_ship_date`';
        }
        if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS, 'mp_status')) {
            $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS.'` ADD  `mp_status` INT NULL DEFAULT NULL AFTER `mp_number`';
        }
        if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS, 'cancelled_reason')) {
            $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS.'` ADD  `cancelled_reason` VARCHAR(200) NULL DEFAULT NULL AFTER `latest_ship_date`';
        }
        if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS, 'fulfillment_center_id')) {
            $sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS.'` ADD  `fulfillment_center_id` VARCHAR(32) NULL DEFAULT NULL AFTER `cancelled_reason`';
        }
        foreach ($sqls as $sql) {
            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
                $output .= 'Error on sql : ' . $sql . '<br> Error Info : <pre>' . print_r(array(
                // Simluate PDO::errorInfo()
                // @see https://www.php.net/manual/fr/pdo.errorinfo.php
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getMsgError()
            ), true) . '</pre><br>' ;
            }
        }

        $marketplace_tab = Configuration::get('FEEDBIZ_MARKETPLACE_TAB');

        $add_amazon_table = false;
        $add_ebay_table = false;
        $add_cdiscount_table = false;
        $add_fnac_table = false;
        $add_rakuten_table = false;
        $add_mirakl = false;

        if ($marketplace_tab) {
            $marketplace_tab_config = unserialize($marketplace_tab);

            if (is_array($marketplace_tab_config) && count($marketplace_tab_config)) {
                if (array_key_exists('amazon', $marketplace_tab_config) &&
                    Tools::strlen($marketplace_tab_config['amazon'])
                ) {
                    $add_amazon_table = true;
                }
                if (array_key_exists('ebay', $marketplace_tab_config) &&
                    Tools::strlen($marketplace_tab_config['ebay'])
                ) {
                    $add_ebay_table = true;
                }
                if (array_key_exists('cdiscount', $marketplace_tab_config) &&
                    Tools::strlen($marketplace_tab_config['cdiscount'])
                ) {
                    $add_cdiscount_table = true;
                }
                if (array_key_exists('fnac', $marketplace_tab_config) &&
                    Tools::strlen($marketplace_tab_config['fnac'])
                ) {
                    $add_fnac_table = true;
                }
                if (array_key_exists('mirakl', $marketplace_tab_config) &&
                    Tools::strlen($marketplace_tab_config['mirakl'])
                ) {
                    $add_mirakl = true;
                }
                if (array_key_exists('rakuten', $marketplace_tab_config) &&
                    Tools::strlen($marketplace_tab_config['rakuten'])
                ) {
                    $add_rakuten_table = true;
                }
            }
        }

        if ($add_amazon_table && !FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_AMAZON)) {
            $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_AMAZON.'` (
                  `id_product` int(11) NOT NULL,
                  `region` varchar(2) NOT NULL,
                  `id_product_attribute` int(11) NOT NULL DEFAULT 0,
                  `force` int(11) DEFAULT NULL,
                  `nopexport` tinyint(4) DEFAULT NULL,
                  `noqexport` tinyint(4) DEFAULT NULL,
                  `fba` tinyint(4) DEFAULT NULL,
                  `fba_value` FLOAT DEFAULT NULL,
                  `latency` tinyint(4) DEFAULT NULL,
                  `disable` tinyint(4) DEFAULT NULL,
                  `price` float DEFAULT NULL,
                  `asin1` varchar(16) DEFAULT NULL,
                  `asin2` varchar(16) DEFAULT NULL,
                  `asin3` varchar(16) DEFAULT NULL,
                  `text` varchar(256) DEFAULT NULL,
                  `bullet_point1` varchar('.self::LENGTH_AMAZON_BULLET_POINT.') DEFAULT NULL,
                  `bullet_point2` varchar('.self::LENGTH_AMAZON_BULLET_POINT.') DEFAULT NULL,
                  `bullet_point3` varchar('.self::LENGTH_AMAZON_BULLET_POINT.') DEFAULT NULL,
                  `bullet_point4` varchar('.self::LENGTH_AMAZON_BULLET_POINT.') DEFAULT NULL,
                  `bullet_point5` varchar('.self::LENGTH_AMAZON_BULLET_POINT.') DEFAULT NULL,
                  `shipping` float DEFAULT NULL,
                  `shipping_type` tinyint(4) DEFAULT NULL,
                  `gift_wrap` tinyint(4) DEFAULT NULL,
                  `gift_message` tinyint(4) DEFAULT NULL,
                  `browsenode` varchar(16) DEFAULT NULL,
                  `repricing_min` FLOAT DEFAULT NULL,
                  `repricing_max` FLOAT DEFAULT NULL,
                  `repricing_gap` FLOAT DEFAULT NULL,
                  `shipping_group` VARCHAR(200) DEFAULT NULL,
                  PRIMARY KEY `product_index` (`id_product`, `id_product_attribute`, `region`),
                  KEY `ASIN` (`asin1`)
                ) ;';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
                $add_amazon_table = false;
                $output .= 'Error on sql : ' . $sql . '<br> Error Info : <pre>' . print_r(array(
                // Simluate PDO::errorInfo()
                // @see https://www.php.net/manual/fr/pdo.errorinfo.php
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getMsgError()
            ), true) . '</pre><br>' ;
            }
        } elseif (FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_AMAZON)) {
            $amazon_sqls = array();

            // Add new field
            if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_AMAZON, 'shipping_group')) {
                $amazon_sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_AMAZON.'` ADD `shipping_group` VARCHAR(200) DEFAULT NULL AFTER `repricing_gap`';
            }
            foreach ($amazon_sqls as $sql) {
                if (!Db::getInstance()->execute($sql)) {
                    $pass = false;
                    $output .= 'Error on sql : ' . $sql . '<br> Error Info : <pre>' . print_r(array(
                // Simluate PDO::errorInfo()
                // @see https://www.php.net/manual/fr/pdo.errorinfo.php
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getMsgError()
            ), true) . '</pre><br>' ;
                }
            }
        }

        if ($add_ebay_table && !FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_EBAY)) {
            $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_EBAY.'` (
                  `id_product` int(11) NOT NULL,
                  `region` varchar(5) NOT NULL,
                  `id_product_attribute` int(11) NOT NULL DEFAULT "0",
                  `force` int(11) DEFAULT NULL,
                  `disable` tinyint(4) DEFAULT NULL,
                  `price` float DEFAULT NULL,
                  PRIMARY KEY `product_index` (`id_product`, `id_product_attribute`, `region`)
                ) ;';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
                $add_ebay_table = false;
                $output .= 'Error on sql : ' . $sql . '<br> Error Info : <pre>' . print_r(array(
                // Simluate PDO::errorInfo()
                // @see https://www.php.net/manual/fr/pdo.errorinfo.php
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getMsgError()
            ), true) . '</pre><br>' ;
            }
        }

        if ($add_cdiscount_table && !FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_CDISCOUNT)) {
            $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_CDISCOUNT.'` (
                  `id_product` int(11) NOT NULL,
                  `region` varchar(5) NOT NULL,
                  `id_product_attribute` int(11) NOT NULL DEFAULT "0",
                  `force` TINYINT NOT NULL DEFAULT "0",
                  `disable` TINYINT NULL DEFAULT NULL,
                  `price` FLOAT NULL DEFAULT NULL,
                  `price_up` FLOAT NULL DEFAULT NULL,
                  `price_down` FLOAT NULL DEFAULT NULL,
                  `shipping` VARCHAR(32) NULL DEFAULT NULL,
                  `shipping_delay` FLOAT NULL DEFAULT NULL,
                  `clogistique` TINYINT NOT NULL DEFAULT "0",
                  `valueadded` FLOAT NULL DEFAULT NULL,
                  `text` VARCHAR(128) NULL DEFAULT NULL,
                  `force_public` VARCHAR(16) NULL DEFAULT NULL,
                  `force_gender` VARCHAR(16) NULL DEFAULT NULL,
                  `shipping_tracked_override` VARCHAR(32) NULL DEFAULT NULL,
                  `shipping_registered_override` VARCHAR(32) NULL DEFAULT NULL,
                  PRIMARY KEY `product_index` (`id_product`, `id_product_attribute`, `region`)
                ) ;';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
                $add_cdiscount_table = false;
                $output .= 'Error on sql : ' . $sql . '<br> Error Info : <pre>' . print_r(array(
                // Simluate PDO::errorInfo()
                // @see https://www.php.net/manual/fr/pdo.errorinfo.php
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getMsgError()
            ), true) . '</pre><br>' ;
            }
        } elseif (FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_CDISCOUNT)) {
            $cdiscount_sqls = array();

            // Add new field
            if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_CDISCOUNT, 'force_public')) {
                $cdiscount_sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_CDISCOUNT.'` ADD `force_public` VARCHAR(16) DEFAULT NULL AFTER `text`';
            }
            if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_CDISCOUNT, 'force_gender')) {
                $cdiscount_sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_CDISCOUNT.'` ADD `force_gender` VARCHAR(16) DEFAULT NULL AFTER `force_public`';
            }
            if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_CDISCOUNT, 'shipping_tracked_override')) {
                $cdiscount_sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_CDISCOUNT.'` ADD `shipping_tracked_override` VARCHAR(16) DEFAULT NULL AFTER `force_gender`';
            }
            if (!FeedbizTools::fieldExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_CDISCOUNT, 'shipping_registered_override')) {
                $cdiscount_sqls[] = 'ALTER TABLE `'._DB_PREFIX_.self::TABLE_FEEDBIZ_CDISCOUNT.'` ADD `shipping_registered_override` VARCHAR(16) DEFAULT NULL AFTER `shipping_tracked_override`';
            }
            foreach ($cdiscount_sqls as $sql) {
                if (!Db::getInstance()->execute($sql)) {
                    $pass = false;
                    $output .= 'Error on sql : ' . $sql . '<br> Error Info : <pre>' . print_r(array(
                // Simluate PDO::errorInfo()
                // @see https://www.php.net/manual/fr/pdo.errorinfo.php
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getMsgError()
            ), true) . '</pre><br>' ;
                }
            }
        }

        if ($add_fnac_table && !FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_FNAC)) {
            $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_FNAC.'` (
                  `id_product` int(11) NOT NULL,
                  `id_product_attribute` int(11) NOT NULL DEFAULT "0",
                  `region` varchar(5) NOT NULL,
                  `force` int(11) DEFAULT NULL,
                  `disable` tinyint(4) DEFAULT NULL,
                  `price` float DEFAULT NULL,
                  `price_up` FLOAT NULL DEFAULT NULL,
                  `price_down` FLOAT NULL DEFAULT NULL,
                  `shipping` VARCHAR(32) NULL DEFAULT NULL,
                  `text` VARCHAR(128) NULL DEFAULT NULL,
                  `logistics_class` VARCHAR(256) NULL DEFAULT NULL,
                  PRIMARY KEY `product_index` (`id_product`, `id_product_attribute`, `region`)
                ) ;';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
                $add_fnac_table = false;
                $output .= 'Error on sql : ' . $sql . '<br> Error Info : <pre>' . print_r(array(
                // Simluate PDO::errorInfo()
                // @see https://www.php.net/manual/fr/pdo.errorinfo.php
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getMsgError()
            ), true) . '</pre><br>' ;
            }
        }

        if ($add_mirakl && !FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_MIRAKL)) {
            $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_MIRAKL.'` (
                  `id_product` int(11) NOT NULL,
                  `id_product_attribute` int(11) NOT NULL DEFAULT "0",
                  `sub_marketplace` int(11) NOT NULL,
                  `region` varchar(5) NOT NULL,
                  `force` int(11) DEFAULT NULL,
                  `nopexport` tinyint(4) DEFAULT NULL,
                  `noqexport` tinyint(4) DEFAULT NULL,
                  `latency` tinyint(4) DEFAULT NULL,
                  `disable` tinyint(4) DEFAULT NULL,
                  `price` float DEFAULT NULL,                  
                  `shipping` VARCHAR(32) NULL DEFAULT NULL,
                  `shipping_type` tinyint(4) DEFAULT NULL,
                  `logistics_class` VARCHAR(256) NULL DEFAULT NULL,
                  `leadtime_ship` tinyint(4) DEFAULT NULL,
                  `text` VARCHAR(128) NULL DEFAULT NULL,
                  PRIMARY KEY `product_index` (`id_product`, `id_product_attribute`, `region`, `sub_marketplace`)
                ) ;';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
                $add_mirakl = false;
                $output .= 'Error on sql : ' . $sql . '<br> Error Info : <pre>' . print_r(array(
                // Simluate PDO::errorInfo()
                // @see https://www.php.net/manual/fr/pdo.errorinfo.php
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getMsgError()
            ), true) . '</pre><br>' ;
            }
        }

        if ($add_rakuten_table && !FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_RAKUTEN)) {
            $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_RAKUTEN.'` (
                  `id_product` int(11) NOT NULL,
                  `id_product_attribute` int(11) NOT NULL DEFAULT "0",
                  `region` varchar(5) NOT NULL,
                  `force` int(11) DEFAULT NULL,
                  `disable` tinyint(4) DEFAULT NULL,
                  `price` float DEFAULT NULL,
                  `price_up` FLOAT NULL DEFAULT NULL,
                  `price_down` FLOAT NULL DEFAULT NULL,
                  `shipping` VARCHAR(32) NULL DEFAULT NULL,
                  `text` VARCHAR(128) NULL DEFAULT NULL,
                  `logistics_class` VARCHAR(256) NULL DEFAULT NULL,
                  PRIMARY KEY `product_index` (`id_product`, `id_product_attribute`, `region`)
                ) ;';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
                $add_rakuten_table = false;
                $output .= 'Error on sql : ' . $sql . '<br> Error Info : <pre>' . print_r(array(
                // Simluate PDO::errorInfo()
                // @see https://www.php.net/manual/fr/pdo.errorinfo.php
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getMsgError()
            ), true) . '</pre><br>' ;
            }
        }

        if ($add_amazon_table) {
            // Product Options for Amazon - Save available fields
            $fields = array();
            $query = Db::getInstance()->executeS('SHOW COLUMNS FROM `'._DB_PREFIX_.self::TABLE_FEEDBIZ_AMAZON.'`');
            if ($query) {
                foreach ($query as $row) {
                    $fields[] = $row['Field'];
                }
            }

            if (count($fields)) {
                Configuration::updateValue('FEEDBIZ_OPTION_FIELDS_AMAZON', implode(',', $fields), false, 0, 0);
            }
        }

        if ($add_ebay_table) {
            // Product Options for Ebay - Save available fields
            $fields = array();
            $query = Db::getInstance()->executeS('SHOW COLUMNS FROM `'._DB_PREFIX_.self::TABLE_FEEDBIZ_EBAY.'`');
            if ($query) {
                foreach ($query as $row) {
                    $fields[] = $row['Field'];
                }
            }

            if (count($fields)) {
                Configuration::updateValue('FEEDBIZ_OPTION_FIELDS_EBAY', implode(',', $fields), false, 0, 0);
            }
        }

        if ($add_cdiscount_table) {
            // Product Options for Cdiscount - Save available fields
            $fields = array();
            $query = Db::getInstance()->executeS('SHOW COLUMNS FROM `'._DB_PREFIX_.self::TABLE_FEEDBIZ_CDISCOUNT.'`');
            if ($query) {
                foreach ($query as $row) {
                    $fields[] = $row['Field'];
                }
            }

            if (count($fields)) {
                Configuration::updateValue('FEEDBIZ_OPTION_FIELDS_CDISCOUNT', implode(',', $fields), false, 0, 0);
            }
        }

        if ($add_fnac_table) {
            // Product Options for Fnac - Save available fields
            $fields = array();
            $query = Db::getInstance()->executeS('SHOW COLUMNS FROM `'._DB_PREFIX_.self::TABLE_FEEDBIZ_FNAC.'`');
            if ($query) {
                foreach ($query as $row) {
                    $fields[] = $row['Field'];
                }
            }

            if (count($fields)) {
                Configuration::updateValue('FEEDBIZ_OPTION_FIELDS_FNAC', implode(',', $fields), false, 0, 0);
            }
        }

        if ($add_mirakl) {
            // Product Options for Mirakl - Save available fields
            $fields = array();
            $query = Db::getInstance()->executeS('SHOW COLUMNS FROM `'._DB_PREFIX_.self::TABLE_FEEDBIZ_MIRAKL.'`');
            if ($query) {
                foreach ($query as $row) {
                    $fields[] = $row['Field'];
                }
            }

            if (count($fields)) {
                Configuration::updateValue('FEEDBIZ_OPTION_FIELDS_MIRAKL', implode(',', $fields), false, 0, 0);
            }
        }

        if ($add_rakuten_table) {
            // Product Options for Fnac - Save available fields
            $fields = array();
            $query = Db::getInstance()->executeS('SHOW COLUMNS FROM `'._DB_PREFIX_.self::TABLE_FEEDBIZ_RAKUTEN.'`');
            if ($query) {
                foreach ($query as $row) {
                    $fields[] = $row['Field'];
                }
            }

            if (count($fields)) {
                Configuration::updateValue('FEEDBIZ_OPTION_FIELDS_RAKUTEN', implode(',', $fields), false, 0, 0);
            }
        }

        $pass = FeedbizConfiguration::createTable() && $pass;

        // So that it is marked as "existing" and we can set all categories by default on installation
        FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_CONFIGURATION, false);

        if (!$pass && isset($output)) {
            $this->post_errors[] = $output . '<br/>' ;
        }

        return ($pass);
    }

    /**
     * @return bool
     */
    private function removeTables()
    {
        $pass = true;

        if (FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_OPTIONS)) {
            $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_OPTIONS.'` ; ';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
            }
        }

        if (FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS)) {
            $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS.'` ; ';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
            }
        }

        if (FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_CONFIGURATION)) {
            $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_CONFIGURATION.'` ; ';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
            }
        }

        if (FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_EBAY)) {
            $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_EBAY.'` ; ';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
            }
        }

        if (FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_AMAZON)) {
            $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_AMAZON.'` ; ';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
            }
        }

        if (FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_CDISCOUNT)) {
            $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_CDISCOUNT.'` ; ';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
            }
        }

        if (FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_FNAC)) {
            $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_FNAC.'` ; ';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
            }
        }

        if (FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_MIRAKL)) {
            $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_MIRAKL.'` ; ';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
            }
        }

        if (FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_RAKUTEN)) {
            $sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.self::TABLE_FEEDBIZ_RAKUTEN.'` ; ';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
            }
        }

        return ($pass);
    }

    /**
     * Add a customer / it will hold the market place orders
     * The customer is fake and has a random id, and random feedbiz_token
     *
     * @return bool
     */
    protected function createCustomer()
    {
        $pass = true;
        // Fakemail
        $var = explode('@', Configuration::get('PS_SHOP_EMAIL'));
        $email = 'no-reply-'.rand(500, 9999999999).'@'.$var[1];

        $customer = new Customer();
        $customer->firstname = 'FeedBiz';
        $customer->lastname = 'FeedBiz';
        $customer->email = $email;
        $customer->newsletter = false;
        $customer->optin = false;
        $customer->passwd = md5(rand(50000000, 9999999999));
        $customer->active = true;
        $customer->add();

        if (!Validate::isLoadedObject($customer)) {
            return (false);
        }
        Configuration::updateValue('FEEDBIZ_CUSTOMER_ID', $customer->id);

        return ($pass);
    }

    /**
     * @return bool
     */
    private function deleteCustomer()
    {
        $customer = new Customer();
        $customer->id = Configuration::get('FEEDBIZ_CUSTOMER_ID');

        return ($customer->delete());
    }

    /**
     * @param $url
     * @return bool|string
     */
    private function autoAddJS($url)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            if (isset($this->context->controller) && method_exists($this->context->controller, 'addJquery')) {
                $this->context->controller->addJquery();
            }

            return ($this->context->controller->addJS($url) && '');
        } else {
            return (sprintf('<script type="text/javascript" src="%s"></script>', $url));
        }
    }

    /**
     * @param $url
     * @param string $media
     * @return bool|string
     */
    private function autoAddCSS($url, $media = 'all')
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            return ($this->context->controller->addCSS($url, $media) && '');
        } else {
            return (sprintf('<link rel="stylesheet" type="text/css" href="%s">', $url));
        }
    }

    /**
     * @return string
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        require_once(_PS_MODULE_DIR_.$this->name.'/classes/feedbiz.product_tab.class.php');

        $this->html = '<meta name="'.self::MODULE_NAME.'-options-json" content="'.
            $this->url.'functions/product_ext.json.php" />'."\n";

        $productExtManager = new FeedBizProductTab();
        $this->html .= $productExtManager->doIt($params);

        return $this->html;
    }
}
