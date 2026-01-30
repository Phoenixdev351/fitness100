<?php
/**
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitutes a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2020 idnovate.com
*  @license   See above
*/

if (!defined('_PS_VERSION_') || !defined('_CAN_LOAD_FILES_')) {
    exit;
}

include_once(_PS_MODULE_DIR_.'advancedpopupcreator/classes/AdvancedPopup.php');

class AdvancedPopupCreator extends Module
{
    public static $image_dir = 'advancedpopupcreator/views/img/popup_images/';
    public static $image_dir_front = '/advancedpopupcreator/views/img/popup_images/';

    public function __construct()
    {
        $this->name = 'advancedpopupcreator';
        $this->tab = 'front_office_features';
        $this->version = '1.1.18';
        $this->author = 'idnovate';
        $this->module_key = 'c5b68e4bc36b781f698da8607a515f18';
        //$this->author_address = '0xd89bcCAeb29b2E6342a74Bc0e9C82718Ac702160';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Advanced Popup Creator');
        $this->description = $this->l('Create as many popups as you can imagine: like loading the page, leaving the store, or when a product is added. Announce sales, new products, etc. and show it on any page: in categories, products, manufacturers... and divided up by clients.');

        $this->tabs[] = array(
            'class_name' => 'AdminAdvancedPopupCreator',
            'name' => 'Popups',
            'parent_class_name' => version_compare(_PS_VERSION_, '1.7', '>=') ? 'AdminParentThemes' : 'AdminParentModules',
            'module' => $this->name
        );
    }

    public function install()
    {
        $result = true;

        $result &= parent::install();
        $result &= $this->copyFolder();
        $result &= include(dirname(__FILE__).'/sql/install.php');
        $result &= $this->registerHook('displayHeader');
		$result &= (version_compare(_PS_VERSION_, '1.7', '>') || $this->registerHook('footer'));
        $result &= (version_compare(_PS_VERSION_, '1.7', '<') || $this->registerHook('displayBeforeBodyClosingTag'));
        $result &= (version_compare(_PS_VERSION_, '1.5', '<') || $this->registerHook('displayPopups'));
        $result &= Configuration::updateValue('APC_HOOK_POSITION', 0);
        $result &= $this->installTabs();

        return (bool)$result;
    }

    public function uninstall()
    {
        $result = true;

        $result &= parent::uninstall();
        $result &= include(dirname(__FILE__).'/sql/uninstall.php');
        $result &= $this->uninstallTabs();
        $result &= Configuration::deleteByName('APC_HOOK_POSITION');
        $result &= Configuration::deleteByName('APC_HOOK_EXECUTED');
        $result &= $this->cleanDir(_PS_MODULE_DIR_.self::$image_dir);

        return (bool)$result;
    }

    public function copyFolder()
    {
        $folderTo = _PS_ROOT_DIR_.'/js/tiny_mce/plugins/codemirror/';
        $folderFrom = _PS_MODULE_DIR_.$this->name.'/lib/tiny_mce/codemirror';

        return $this->copyDir($folderFrom, $folderTo);
    }

    protected function copyDir($src, $dst)
    {
        if (is_dir($src)) {
            $dir = opendir($src);
            @mkdir($dst);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src.'/'.$file)) {
                        $this->copyDir($src.'/'.$file, $dst.'/'.$file);
                    } else {
                        copy($src.'/'.$file, $dst.'/'.$file);
                    }
                }
            }
            closedir($dir);
        }

        return true;
    }

    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('APC_HOOK_EXECUTED', Tools::getValue('APC_HOOK_EXECUTED'));
            Configuration::updateValue('APC_HOOK_POSITION', Tools::getValue('APC_HOOK_POSITION'));
            Configuration::updateValue('APC_OVERRIDE_LIBRARY', Tools::getValue('APC_OVERRIDE_LIBRARY'));
            Configuration::updateValue('APC_VERSION_LIBRARY', Tools::getValue('APC_VERSION_LIBRARY'));
            Configuration::updateValue('APC_LIGHT_MODE', Tools::getValue('APC_LIGHT_MODE'));
            Configuration::updateValue('APC_COOKIE', Tools::getValue('APC_COOKIE'));
            Configuration::updateValue('APC_IFRAMES', Tools::getValue('APC_IFRAMES'));
        }

        if (Tools::getValue('magic')) {
            return $this->renderForm();
        }

        Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminAdvancedPopupCreator'));
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => 'Configuration',
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => 'APC_HOOK_EXECUTED',
                        'name' => 'APC_HOOK_EXECUTED',
                        'col' => 1
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'APC_HOOK_POSITION',
                        'name' => 'APC_HOOK_POSITION',
                        'col' => 1
                    ),
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'label' => 'Override Fancybox libraries',
                        'name' => 'APC_OVERRIDE_LIBRARY',
                        'values' => array(
                            array(
                                'id' => 'APC_OVERRIDE_LIBRARY_on',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id' => 'APC_OVERRIDE_LIBRARY_off',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'label' => 'Template uses different Fancybox library. Don\'t include PS plugin',
                        'name' => 'APC_VERSION_LIBRARY',
                        'values' => array(
                            array(
                                'id' => 'APC_VERSION_LIBRARY_on',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id' => 'APC_VERSION_LIBRARY_off',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Light mode',
                        'name' => 'APC_LIGHT_MODE',
                        'col' => 1
                    ),
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'label' => 'Use $_COOKIE',
                        'name' => 'APC_COOKIE',
                        'values' => array(
                            array(
                                'id' => 'APC_COOKIE_on',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id' => 'APC_COOKIE_off',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'label' => 'Use iframes',
                        'name' => 'APC_IFRAMES',
                        'values' => array(
                            array(
                                'id' => 'APC_IFRAMES_on',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id' => 'APC_IFRAMES_off',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&magic=1';
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'APC_HOOK_EXECUTED' => Tools::getValue('APC_HOOK_EXECUTED', Configuration::get('APC_HOOK_EXECUTED')),
            'APC_HOOK_POSITION' => Tools::getValue('APC_HOOK_POSITION', Configuration::get('APC_HOOK_POSITION')),
            'APC_OVERRIDE_LIBRARY' => Tools::getValue('APC_OVERRIDE_LIBRARY', Configuration::get('APC_OVERRIDE_LIBRARY')),
            'APC_VERSION_LIBRARY' => Tools::getValue('APC_VERSION_LIBRARY', Configuration::get('APC_VERSION_LIBRARY')),
            'APC_LIGHT_MODE' => Tools::getValue('APC_LIGHT_MODE', Configuration::get('APC_LIGHT_MODE')),
            'APC_COOKIE' => Tools::getValue('APC_COOKIE', Configuration::get('APC_COOKIE')),
            'APC_IFRAMES' => Tools::getValue('APC_IFRAMES', Configuration::get('APC_IFRAMES')),
        );
    }

    public function hookDisplayHeader()
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            //Check if executed
            if (!Configuration::get('APC_HOOK_EXECUTED')) {
                Configuration::updateValue('APC_HOOK_EXECUTED', 1);
            } elseif (Configuration::get('APC_HOOK_EXECUTED') == 1) {
                if (!Configuration::get('APC_HOOK_POSITION')) {
                    Configuration::updateValue('APC_HOOK_POSITION', 0);
                }

                $availableHooks = array(
                    '1' => 'footer',
                    '2' => 'top',
                    '3' => 'displayBeforeBodyClosingTag',
                    '4' => 'displayNavFullWidth',
                    '5' => 'displayFooterLinks',
                    '6' => 'displayFooterLinks2',
                    '7' => 'displayFooterBuilder',
                    '8' => 'jxMegaLayoutFooter',
                    '9' => 'tmMegaLayoutFooter'
                );

                if (isset($availableHooks[(int)Configuration::get('APC_HOOK_POSITION') + 1])) {
                    if ((int)Configuration::get('APC_HOOK_POSITION')) {
                        $this->unregisterHook($availableHooks[(int)Configuration::get('APC_HOOK_POSITION')]);
                    }
                    $this->registerHook($availableHooks[(int)Configuration::get('APC_HOOK_POSITION') + 1]);

                    $cache_id = Hook::MODULE_LIST_BY_HOOK_KEY.(isset($this->context->shop->id) ? '_'.$this->context->shop->id : '').((isset($this->context->customer)) ? '_'.$this->context->customer->id : '');
                    Cache::clean($cache_id);

                    Configuration::updateValue('APC_HOOK_POSITION', (int)Configuration::get('APC_HOOK_POSITION')+1);
                } else {
                    Configuration::updateValue('APC_HOOK_EXECUTED', 2);
                }
            }
        }

        //Don't display popups in quick preview
        if ((int)Tools::getValue('content_only')) {
            return false;
        }

        if (Configuration::get('APC_OVERRIDE_LIBRARY')) {
            $this->context->controller->addCSS($this->_path.'lib/fancybox/jquery.fancybox.css');
            $this->context->controller->addJS($this->_path.'lib/fancybox/jquery.fancybox.js');
        }

        if (!Configuration::get('APC_VERSION_LIBRARY')) {
            $this->context->controller->addJqueryPlugin('fancybox');
            $this->context->controller->addCSS($this->_path.'views/css/advancedpopup-front.css');
            $this->context->controller->addCSS($this->_path.'lib/fancybox/jquery.fancybox-transitions.css');

            if (version_compare(_PS_VERSION_, '1.7', '>=')) {
               $this->context->controller->registerJavascript('modules-advancedpopupcreator', 'modules/'.$this->name.'/lib/fancybox/jquery.fancybox.transitions.js', array('position' => 'bottom', 'priority' => 150));
            } else {
                $this->context->controller->addJS($this->_path.'lib/fancybox/jquery.fancybox.transitions.js');
            }
        }

        $this->context->controller->addJS($this->_path.'views/js/advancedpopup-front.js');

        $this->context->smarty->assign(array(
            'apc_link'  => $this->context->link->getModuleLink('advancedpopupcreator', 'popup', array(), Tools::usingSecureMode()),
            'apc_token' => Tools::getToken(false),
            'apc_product' => Tools::getValue('id_product'),
            'apc_category' => Tools::getValue('id_category'),
            'apc_supplier' => Tools::getValue('id_supplier'),
            'apc_manufacturer' => Tools::getValue('id_manufacturer'),
            'apc_cms' => Tools::getValue('id_cms'),
            'apc_controller' => self::getController()
        ));

        return $this->display(__FILE__, 'views/templates/hook/apc_functions_front.tpl');
    }

    public function hookTop()
    {
        return $this->hookFooter();
    }

    public function hookDisplayBeforeBodyClosingTag()
    {
        return $this->hookFooter();
    }

    public function hookDisplayNavFullWidth()
    {
        return $this->hookFooter();
    }

    public function hookDisplayPopups()
    {
        return $this->hookFooter();
    }

    public function hookDisplayFooterLinks()
    {
        return $this->hookFooter();
    }

    public function hookDisplayFooterLinks2()
    {
        return $this->hookFooter();
    }

    public function hookDisplayFooterBuilder()
    {
        return $this->hookFooter();
    }

    public function hookJxMegaLayoutFooter()
    {
        return $this->hookFooter();
    }

    public function hookTmMegaLayoutFooter()
    {
        return $this->hookFooter();
    }

    public function hookDisplayFooterAfter()
    {
        return $this->hookFooter();
    }

    public function hookFooter()
    {
        Configuration::updateValue('APC_HOOK_EXECUTED', 2);

        $object = new AdvancedPopup();
        $popups = $object->getPopups(Tools::getValue('previewPopup') ? true : false);

        $tpl = '';
        foreach ($popups as $popup) {
            if (!$popup['content'] && !$popup['image'] && !$popup['image_background']) {
                continue;
            }

            if ($popup['image_link'] && $popup['image']) {
                $imageLink = '<a target="'.$popup['image_link_target'].'" href="'.$popup['image_link'].'"><img class="modal-img" src="'.__PS_BASE_URI__.'modules/'.$this->name.'/views/img/popup_images/'.$popup['image'].'?t='.time().'"></a>';
            } elseif ($popup['image']) {
                $imageLink = '<img class="modal-img" src="'._MODULE_DIR_.AdvancedPopupCreator::$image_dir_front.$popup['image'].'?t='.time().'">';
            } else {
                $imageLink = '';
            }

            $laDataVars = array();
            $laDataVars['lfSecsToDisplay']      = ((int)$popup['secs_to_display'] && $popup['display_on_load']) ? (int)$popup['secs_to_display'] * 1000 : 0;
            $laDataVars['lfSecsToDisplayCart']  = ((int)$popup['secs_to_display_cart'] && $popup['display_after_cart']) ? (int)$popup['secs_to_display_cart'] * 1000 : 0;
            $laDataVars['lfSecsToClose']        = (int)$popup['secs_to_close'] ? (int)$popup['secs_to_close'] * 1000 : 0;
            $laDataVars['lsBackOpacityValue']   = $popup['back_opacity_value'] ? (float)$popup['back_opacity_value'] : 0;
            $laDataVars['lsHeight']             = $popup['popup_height'];
            $laDataVars['lsWidth']              = $popup['popup_width'];
            $laDataVars['lsPadding']            = $popup['popup_padding'] ? (int)$popup['popup_padding'] : 0;
            $laDataVars['lbLocked']             = $popup['locked'] ? !$popup['locked'] : '1';
            $laDataVars['lbCloseOnBackground']  = $popup['close_on_background'] ? $popup['close_on_background'] : '0';
            $laDataVars['lsPopupCss']           = $popup['css'] ? $popup['css'] : '';
            $laDataVars['lsCssClass']           = $popup['css_class'] ? $popup['css_class'] : '';
            $laDataVars['lsBlurBackground']     = $popup['blur_background'] ? $popup['blur_background'] : 0;
            $laDataVars['popupId']              = $popup['id_advancedpopup'];
            $laDataVars['imageBackground']      = $popup['image_background'] ? _MODULE_DIR_.AdvancedPopupCreator::$image_dir_front.$popup['image_background']: '';
            $laDataVars['openEffect']           = $popup['open_effect'];
            $laDataVars['position']             = $popup['position'];
            $laDataVars['dontDisplayAgain']     = $popup['dont_display_again'];
            $laDataVars['colorBackground']      = $popup['color_background'];
            $laDataVars['lsContent']            = '';

            if (preg_match_all('/\{powerfulform\:[(0-9\,)]+\}/i', $popup['content'], $matches)) {
                require_once(_PS_MODULE_DIR_.'powerfulformgenerator/classes/PFGRenderer.php');
                foreach ($matches[0] as $match) {
                    $explode = explode(":", $match);
                    $popup['content'] = str_replace($match, $this->generatePFG(str_replace("}", "", $explode[1])), $popup['content']);
                }

                $laDataVars['lsContent'] = $popup['content'] ? $popup['content'].$imageLink : $imageLink;
            } else {
                $laDataVars['lsContent'] = $popup['content'] ? $popup['content'].$imageLink : $imageLink;
            }

            // Assign vars
            $this->context->smarty->assign($laDataVars);

            $tpl .= $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/hook/apc_popup.tpl');
        }

        return $tpl;
    }

    public function hookDisplayOrderLeftColumn()
    {
        return $this->hookDisplayHeader();
    }

    public function installTabs()
    {
        $languages = Language::getLanguages(false);

        foreach ($this->tabs as $moduleTab) {
            if (!Tab::getIdFromClassName($moduleTab['class_name'])) {
                $tab = new Tab();
                $tab->class_name = $moduleTab['class_name'];
                $tab->module = $moduleTab['module'];
                $tab->active = 1;

                foreach ($languages as $language) {
                    if (is_array($moduleTab['name'])) {
                        if (isset($moduleTab['name'][$language['iso_code']])) {
                            $tab->name[$language['id_lang']] = $moduleTab['name'][$language['iso_code']];
                        } else {
                            $tab->name[$language['id_lang']] = $moduleTab['name']['en'];
                        }
                    } else {
                        $tab->name[$language['id_lang']] = $moduleTab['name'];
                    }
                }

                if (isset($moduleTab['parent_class_name']) && is_string($moduleTab['parent_class_name'])) {
                    $tab->id_parent = Tab::getIdFromClassName($moduleTab['parent_class_name']);
                } elseif (isset($moduleTab['id_parent'])) {
                    $tab->id_parent = $moduleTab['id_parent'];
                } else {
                    $tab->id_parent = -1;
                }

                if (isset($moduleTab['icon'])) {
                    $tab->icon = $moduleTab['icon'];
                }

                $tab->add();
                if (!$tab->id) {
                    return false;
                }
            }
        }

        return true;
    }

    public function uninstallTabs()
    {
        /*if (version_compare(_PS_VERSION_, '1.7.1', '>=')) {
            return true;
        }*/

        foreach ($this->tabs as $moduleTab) {
            $idTab = Tab::getIdFromClassName($moduleTab['class_name']);
            if ($idTab) {
                $tab = new Tab($idTab);
                $tab->delete();
            }
        }

        return true;
    }

    public static function cleanDir($lsDir, $removeDir = false)
    {
        if (is_dir($lsDir)) {
            $laFiles = scandir($lsDir);
            if (!empty($laFiles)) {
                unset($laFiles[0], $laFiles[1]);
                if (!empty($laFiles)) {
                    // Remove files
                    foreach ($laFiles as $lsFile) {
                        if ($lsFile != 'index.php' && $lsFile != 'noimage.gif') {
                            @unlink($lsDir.$lsFile);
                        }
                    }
                }
            }

            // Remove directory
            if ($removeDir) {
                @rmdir($lsDir);
            }
        }

        return true;
    }

    public static function uploadImage($path, $field, $langId, $object)
    {
        if (isset($_FILES[$field]) && !empty($_FILES[$field])) {
            $extension = '';

            if (!file_exists($path)) {
                mkdir($path);
            }

            // Get extension
            $fileName = explode('.', $_FILES[$field]['name']);
            if (!empty($fileName)) {
                $extension = $fileName[count($fileName) - 1];
            }

            // Remove the file if exists
            $newFilename = get_class($object).$object->id.'_'.$field.'_'.$langId.'.'.$extension;
            if (file_exists($path.$newFilename)) {
                unlink($path.$newFilename);
            }

            if (move_uploaded_file($_FILES[$field]['tmp_name'], $path.$newFilename)) {
                return $newFilename;
            } else {
                return false;
            }
        }

        return false;
    }

    public static function deleteImage($path)
    {
        if (is_dir($path)) {
            return false;
        }

        if (!file_exists($path)) {
            return false;
        }

        if (!unlink($path)) {
            return false;
        }

        return true;
    }

    public static function convertPriceFull($amount, Currency $currency_from = null, Currency $currency_to = null, $round = true)
    {
        if ($currency_from === $currency_to) {
            return $amount;
        }

        if ($currency_from === null) {
            $currency_from = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }

        if ($currency_to === null) {
            $currency_to = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }

        if ($currency_from->id == Configuration::get('PS_CURRENCY_DEFAULT')) {
            $amount *= $currency_to->conversion_rate;
        } else {
            $conversion_rate = ($currency_from->conversion_rate == 0 ? 1 : $currency_from->conversion_rate);
            // Convert amount to default currency (using the old currency rate)
            $amount = $amount / $conversion_rate;
            // Convert to new currency
            $amount *= $currency_to->conversion_rate;
        }
        if ($round) {
            $amount = Tools::ps_round($amount, _PS_PRICE_DISPLAY_PRECISION_);
        }

        return $amount;
    }

    public static function clearCache()
    {
        if (method_exists('Tools', 'clearAllCache')) {
            Tools::clearAllCache();
        }

        if (method_exists('Tools', 'clearSmartyCache')) {
            Tools::clearSmartyCache();
        }

        if (method_exists('Tools', 'clearSf2Cache')) {
            Tools::clearSf2Cache();
        }

        if (method_exists('Tools', 'clearCache')) {
            Tools::clearCache();
        }

        if (method_exists('Media', 'clearCache')) {
            Media::clearCache();
        }
    }

    public static function getController()
    {
        $replace = array("-", "–");
        $module_name = '';
        if (Validate::isModuleName(Tools::getValue('module'))) {
            $module_name = Tools::getValue('module');
        }

        if (isset(Context::getContext()->controller->page_name)) {
            $page_name = Context::getContext()->controller->page_name;
        } else {
            if (isset(Context::getContext()->controller->php_self)) {
                $page_name = Context::getContext()->controller->php_self;
            } elseif (Tools::getValue('fc') === 'module' && $module_name != '' && (Module::getInstanceByName($module_name) instanceof PaymentModule)) {
                $page_name = 'module-payment-submit';
            } elseif (preg_match('#^'.preg_quote(Context::getContext()->shop->physical_uri, '#').'modules/([a-zA-Z0-9_-]+?)/(.*)$#', $_SERVER['REQUEST_URI'], $m)) {
                // @retrocompatibility Are we in a module ?
                $page_name = 'module-'.$m[1].'-'.str_replace(array('.php', '/'), array('', '-'), $m[2]);
            } else {
                $page_name = Dispatcher::getInstance()->getController();
                $page_name = (preg_match('/^[0-9]/', $page_name)) ? 'page_'.$page_name : $page_name;
            }
        }

        // Exceptions
        //Backward compatibility of controller names
        if ($page_name === 'authentication') {
            $page_name = 'auth';
        } elseif ($page_name === 'productscomparison') {
            $page_name = 'compare';
        } elseif ($page_name === 'checkout') {
            //onepagecheckoutps
            $page_name = 'order';
        } elseif ($page_name === 'stores') {
            $page_name = 'cms';
        }

        $controller = str_replace($replace, '', $page_name);

        return $controller;
    }
}
