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

class AdminAdvancedPopupCreatorController extends ModuleAdminController
{
    protected $_defaultOrderBy = 'date_add';
    protected $_defaultOrderWay = 'DESC';
    protected $orderBy = 'id_advancedpopup';
    protected $orderWay = 'ASC';

    protected $isShopSelected = true;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'advancedpopup';
        $this->className = 'AdvancedPopup';
        $this->tabClassName = 'AdminAdvancedPopupCreator';
        $this->lang = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->_orderWay = $this->_defaultOrderWay;

        parent::__construct();

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );

        $this->context = Context::getContext();

        $this->default_form_language = $this->context->language->id;

        $this->fields_list = array(
            'id_advancedpopup' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'align' => 'text-center',
                'active' => 'status',
                'type' => 'bool',
                'callback' => 'printActiveIcon'
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'filter_key' => 'a!name'
            ),
            'date_init' => array(
                'title' => $this->l('From'),
                'align' => 'text-center',
                'callback' => 'printValidDate',
            ),
            'date_end' => array(
                'title' => $this->l('To'),
                'align' => 'text-center',
                'callback' => 'printValidDate',
            ),
            'date_add' => array(
                'title' => $this->l('Valid'),
                'align' => 'text-center',
                'callback' => 'printValidIcon',
            ),
            'show_on_view_page_nbr' => array(
                'title' => $this->l('Display when viewed X pages'),
                'align' => 'text-center'
            ),
            'show_every_nbr_hours' => array(
                'title' => $this->l('Display every X minutes'),
                'align' => 'text-center'
            ),
            'priority' => array(
                'title' => $this->l('Priority'),
                'align' => 'text-center'
            ),
        );

        if (Shop::isFeatureActive() && (Shop::getContext() == Shop::CONTEXT_ALL || Shop::getContext() == Shop::CONTEXT_GROUP)) {
            $this->isShopSelected = false;
        }

        if (!Shop::isFeatureActive()) {
            $this->shopLinkType = '';
        } else {
            $this->shopLinkType = 'shop';
        }

        $this->nbItemsLightMode = (Configuration::get('APC_LIGHT_MODE') ? Configuration::get('APC_LIGHT_MODE') : 15000);
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        if ($this->display) {
            $this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/lib/CodeMirror/lib/codemirror.css');

            $this->addJqueryPlugin(array('typewatch', 'fancybox', 'autocomplete'));
            $this->addJqueryUI(array('ui.datepicker', 'ui.button', 'ui.sortable', 'ui.droppable'));

            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                $this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/views/js/advancedpopup-admin.js', false);
            } else {
                //$this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/views/js/bootstrap-tab.js');
                $this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/views/js/advancedpopup-admin15.js', false);
            }

            $this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/views/js/tabs.js', false);
            $this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/lib/CodeMirror/lib/codemirror.js', false);
            $this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/lib/CodeMirror/addon/display/autorefresh.js', false);
            $this->context->controller->addJS(_MODULE_DIR_.$this->module->name.'/lib/CodeMirror/mode/css/css.js', false);
        }

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/advancedpopup-admin.css');
        } else {
            $this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/advancedpopup-admin15.css');
            $this->context->controller->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/tabs.css');
        }
    }

    public function initContent()
    {
        if ($this->action == 'select_delete') {
            $this->context->smarty->assign(array(
                'delete_form' => true,
                'url_delete' => htmlentities($_SERVER['REQUEST_URI']),
                'boxes' => $this->boxes,
            ));
        }

        parent::initContent();

        if (!$this->display) {
            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                $this->context->smarty->assign(array(
                    'this_path' => $this->module->getPathUri(),
                    'support_id' => '23773',
                    'doc_url' => 'https://docs.idnovate.com/docs/popup/'
                ));

                $available_lang_codes = array('en', 'es', 'fr', 'it', 'de');
                $default_lang_code = 'en';
                $template_iso_suffix = in_array(strtok($this->context->language->language_code, '-'), $available_lang_codes) ? strtok($this->context->language->language_code, '-') : $default_lang_code;
                $this->content .= $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/company/information_' . $template_iso_suffix . '.tpl');
            }

            $this->context->smarty->assign(array(
                'content' => $this->content,
            ));
        }
    }

    public function initToolbarTitle()
    {
        parent::initToolbarTitle();

        switch ($this->display) {
            case '':
            case 'list':
                array_pop($this->toolbar_title);
                $this->toolbar_title[] = $this->l('Manage Advanced Popup Creator Configuration');
                break;
            case 'view':
                if (($object = $this->loadObject(true)) && Validate::isLoadedObject($object)) {
                    array_pop($this->toolbar_title);
                    $this->toolbar_title[] = sprintf($this->l('Configuration: %s'), $object->name);
                }
                break;
            case 'add':
            case 'edit':
                array_pop($this->toolbar_title);
                if (($object = $this->loadObject(true)) && Validate::isLoadedObject($object)) {
                    $this->toolbar_title[] = sprintf($this->l('Editing popup: %s'), $object->name);
                } else {
                    $this->toolbar_title[] = $this->l('New popup');
                }
                break;
        }
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        if (empty($this->display)) {
            $this->page_header_toolbar_btn['desc-module-new'] = array(
                'href' => 'index.php?controller='.$this->tabClassName.'&add'.$this->table.'&token='.Tools::getAdminTokenLite($this->tabClassName),
                'desc' => $this->l('New'),
                'icon' => 'process-icon-new'
            );

            $this->page_header_toolbar_btn['desc-module-translate'] = array(
                'href' => '#',
                'desc' => $this->l('Translate'),
                'modal_target' => '#moduleTradLangSelect',
                'icon' => 'process-icon-flag'
            );

            $this->page_header_toolbar_btn['desc-module-hook'] = array(
                'href' => 'index.php?tab=AdminModulesPositions&token='.Tools::getAdminTokenLite('AdminModulesPositions').'&show_modules='.Module::getModuleIdByName($this->module->name),
                'desc' => $this->l('Manage hooks'),
                'icon' => 'process-icon-anchor'
            );
        }

        $this->context->smarty->clearAssign('help_link', '');
    }

    public function initModal()
    {
        parent::initModal();

        $languages = Language::getLanguages(false);
        $translateLinks = array();

        if (version_compare(_PS_VERSION_, '1.7.2.1', '>=')) {
            $module = Module::getInstanceByName($this->module->name);
            $isNewTranslateSystem = $module->isUsingNewTranslationSystem();
            $link = Context::getContext()->link;
            foreach ($languages as $lang) {
                if ($isNewTranslateSystem) {
                    $translateLinks[$lang['iso_code']] = $link->getAdminLink('AdminTranslationSf', true, array(
                        'lang' => $lang['iso_code'],
                        'type' => 'modules',
                        'selected' => $module->name,
                        'locale' => $lang['locale'],
                    ));
                } else {
                    $translateLinks[$lang['iso_code']] = $link->getAdminLink('AdminTranslations', true, array(), array(
                        'type' => 'modules',
                        'module' => $module->name,
                        'lang' => $lang['iso_code'],
                    ));
                }
            }
        }

        $this->context->smarty->assign(array(
            'trad_link' => 'index.php?tab=AdminTranslations&token='.Tools::getAdminTokenLite('AdminTranslations').'&type=modules&module='.$this->module->name.'&lang=',
            'module_languages' => $languages,
            'module_name' => $this->module->name,
            'translateLinks' => $translateLinks,
        ));

        $modal_content = $this->context->smarty->fetch('controllers/modules/modal_translation.tpl');

        $this->modals[] = array(
            'modal_id' => 'moduleTradLangSelect',
            'modal_class' => 'modal-sm',
            'modal_title' => $this->l('Translate this module'),
            'modal_content' => $modal_content
        );
    }

    public function initProcess()
    {
        parent::initProcess();

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            if (Tools::getIsset('duplicate'.$this->table)) {
                if ($this->tabAccess['add'] === '1') {
                    $this->action = 'duplicate';
                } else {
                    $this->errors[] = Tools::displayError('You do not have permission to add this.');
                }
            }
        } else {
            if (Tools::getIsset('duplicate'.$this->table)) {
                if ($this->access('add')) {
                    $this->action = 'duplicate';
                } else {
                    $this->errors[] = Tools::displayError('You do not have permission to add this.');
                }
            }
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAdd'.$this->table)) {
            if (($object = $this->loadObject(true)) || Validate::isLoadedObject($object)) {
                if (!(int)Tools::getValue('display_on_load')
                    && !(int)Tools::getValue('display_on_exit')
                    && !(int)Tools::getValue('display_after_cart')
                    && !(int)Tools::getValue('display_on_click')) {
                    $this->errors[] = $this->l("You must define when do you want to display the popup");
                }

                if ((int)Tools::getValue('display_on_click')
                    && !Tools::getValue('display_on_click_selector')) {
                    $this->errors[] = $this->l("You must define which selector fires the popup");
                }

                //These fields are int or empty, but we can't define them as INT because it sets 0 by default
                $stringFields = array('nb_products', 'show_every_nbr_hours', 'secs_to_display', 'secs_to_display_cart', 'secs_to_close');

                foreach ($stringFields as $stringField) {
                    if (!Validate::isInt(Tools::getValue($stringField)) && Tools::getValue($stringField)) {
                        $this->errors[] = sprintf('The %s field is invalid', $object->displayFieldName($stringField, get_class($object)));
                    }
                }

                //Content
                $content = false;
                foreach (Language::getLanguages(false) as $language) {
                    if (strpos(Tools::getValue('content_'.$language['id_lang']), 'mojo/signup-forms/Loader') !== false
                        || (strpos(Tools::getValue('content_'.$language['id_lang']), '<iframe') !== false
                            && !Configuration::get('APC_IFRAMES')
                            && !Configuration::get('PS_ALLOW_HTML_IFRAME'))
                        ) {
                        $this->errors[] = $this->l("Content is not valid");
                    }

                    if (Tools::getValue('content_'.$language['id_lang'])) {
                        $content = true;
                        break;
                    }

                    if (Tools::fileAttachment('image_'.$language['id_lang'])
                        || Tools::fileAttachment('image_background_'.$language['id_lang'])) {
                        $content = true;
                        break;
                    }

                    if ($object->image[$language['id_lang']]
                        || $object->image_background[$language['id_lang']]) {
                        $content = true;
                        break;
                    }
                }

                if (!$content) {
                    $this->errors[] = $this->l("You must set some content at least in one language");
                }

                //Date from
                if (!Tools::getValue('date_init')) {
                    $_POST['date_init'] = date('Y-m-d H:i:s');
                }

                // Secs
                if ((int)Tools::getValue('secs_to_close') < 0) {
                    $this->errors[] = $this->l("Field 'Secs to close' can not be less than 0.");
                }

                // Priority
                if ((int)Tools::getValue('priority') < 0) {
                    $this->errors[] = $this->l("Field 'Priority' can not be less than 0.");
                }

                // Opacity
                if ((float)Tools::getValue('back_opacity_value') < 0 || (float)Tools::getValue('back_opacity_value') > 1) {
                    $this->errors[] = $this->l("Field 'Opacity' must be a number between 0 and 1.");
                }

                // Nbr of pages
                if ((int)Tools::getValue('show_on_view_page_nbr') < 0) {
                    $this->errors[] = $this->l("Field 'Nbr of pages before show' can not be less than 1.");
                }

                // Padding
                foreach (Language::getLanguages(false) as $language) {
                    if ((int)Tools::getValue('popup_padding_'.$language['id_lang']) < 0) {
                        $this->errors[] = $this->l("Field 'Popup padding' can not be less than 0.");
                        break;
                    }
                }

                // Minutes
                if ((int)Tools::getValue('show_every_nbr_hours') < 0) {
                    $this->errors[] = $this->l("Field 'Show every minutes' can not be less than 0.");
                }

                //Responsive
                foreach (Language::getLanguages(false) as $language) {
                    if ((int)Tools::getValue('responsive_min_'.$language['id_lang']) != 0
                        && (int)Tools::getValue('responsive_max_'.$language['id_lang']) != 0
                        && (int)Tools::getValue('responsive_min_'.$language['id_lang']) > (int)Tools::getValue('responsive_max_'.$language['id_lang'])) {
                        $this->errors[] = $this->l("Minimum screen size can not be higher than maximum");
                        break;
                    }
                }

                // Newsletter
                if ((int)Tools::getValue('show_customer_newsletter')
                    && (int)Tools::getValue('show_customer_not_newsletter')) {
                    $this->errors[] = $this->l("You can not enable 'Display popup only to customers subscribed to newsletter' and 'Display popup only to customers NOT subscribed to newsletter' at the same time");
                }

                //Jquery selector
                if (Tools::getValue('display_on_click_selector') && Tools::substr(Tools::getValue('display_on_click_selector'), 0, 2) != '$(') {
                    $this->errors[] = $this->l("Incorrect jQuery selector format");
                }

                //Cart amount
                if ((float)Tools::getValue('cart_amount_from') > (float)Tools::getValue('cart_amount_to')) {
                    $this->errors[] = $this->l('"Cart amount from" can not be higher than "Cart amount to"');
                }

                //String in URL
                if ((int)Tools::getValue('display_url_string')) {
                    $value = false;
                    foreach (Language::getLanguages(false) as $language) {
                        if (Tools::getValue('display_url_string_'.$language['id_lang'])) {
                            $value = true;
                        }
                    }

                    if (!$value) {
                        $this->errors[] = $this->l('You need to define a string in URL');
                    }
                }

                if ((int)Tools::getValue('display_referrer_string')) {
                    $value = false;
                    foreach (Language::getLanguages(false) as $language) {
                        if (Tools::getValue('display_referrer_string_'.$language['id_lang'])) {
                            $value = true;
                        }
                    }

                    if (!$value) {
                        $this->errors[] = $this->l('You need to define a referrer');
                    }
                }

                //IPs
                if ((int)Tools::getValue('display_ip_string')) {
                    if (!Tools::getValue('display_ip_string')) {
                        $this->errors[] = $this->l('You need to define an IP');
                    }

                    foreach (explode(',', Tools::getValue('display_ip_string')) as $ip) {
                        if ($ip && !filter_var($ip, FILTER_VALIDATE_IP)) {
                            $this->errors[] = $ip.' '.$this->l('is not a valid IP');
                        }
                    }
                }

                //Product stock
                if ((int)Tools::getValue('product_stock_from')
                    && (int)Tools::getValue('product_stock_to')
                    && (int)Tools::getValue('product_stock_from') > (int)Tools::getValue('product_stock_to')) {
                    $this->errors[] = $this->l('"Product stock from" can not be higher than "Product stock to"');
                }
            }

            $switchFields = array(
                'switch_controller_exceptions',
                'switch_groups',
                'switch_genders',
                'switch_customers',
                'switch_categories',
                'switch_manufacturers',
                'switch_suppliers',
                'switch_products',
                'switch_countries',
                'switch_zones',
                'switch_cms',
                'switch_categories_selected',
                'switch_languages',
                'switch_attributes',
                'switch_features',
                'switch_display_url_string',
                'switch_display_referrer_string',
                'switch_display_ip_string'
            );

            // Reset fields with selector to NO but selected values remain
            foreach ($switchFields as $switchField) {
                if (!(int)Tools::getValue($switchField)) {
                    //Set value in $_POST, can't use Tools::getValue()
                    if ($switchField === 'switch_display_url_string') {
                        foreach (Language::getLanguages(false) as $language) {
                            $_POST['display_url_string_'.$language['id_lang']] = '';
                        }
                    } elseif ($switchField === 'switch_display_referrer_string') {
                        foreach (Language::getLanguages(false) as $language) {
                            $_POST['display_referrer_string_'.$language['id_lang']] = '';
                        }
                    } elseif ($switchField === 'switch_display_ip_string') {
                        $_POST['display_ip_string'] = '';
                    } else {
                        $_POST[str_replace("switch_", "", $switchField)] = array();
                        if ($switchField === 'switch_categories_selected') {
                            $_POST['nb_products'] = '';
                        }
                    }
                }
            }
        }

        if (Tools::getValue('deleteImage') && Tools::getValue('id_language') && Tools::getValue('type')) {
            if (($object = $this->loadObject(true)) || Validate::isLoadedObject($object)) {
                $type = Tools::getValue('type');
                $image = $object->{$type}[(int)Tools::getValue('id_language')];
                if ($this->module->deleteImage(_PS_MODULE_DIR_.AdvancedPopupCreator::$image_dir.$image)) {
                    $object->{$type}[(int)Tools::getValue('id_language')] = '';
                    $object->save();
                    Tools::redirectAdmin(self::$currentIndex.'&add'.$this->table.'&'.$this->identifier.'='.Tools::getValue($this->identifier).'&conf=7&token='.Tools::getAdminTokenLite($this->tabClassName));
                }
            }
            $this->errors[] = $this->l('An error occurred during image deletion (cannot load object).');
        }

        parent::postProcess();

        if (!empty($this->errors)) {
            // if we have errors, we stay on the form instead of going back to the list
            $this->display = 'edit';

            return false;
        }
    }

    public function renderList()
    {
        if (Tools::getValue('magic')) {
            return $this->module->getContent();
        }

        //Redirect if no popup created
        if (!$this->errors && $this->isShopSelected && !AdvancedPopup::getNbObjects()) {
            $this->redirect_after = 'index.php?controller='.$this->tabClassName.'&add'.$this->table.'&token='.Tools::getAdminTokenLite($this->tabClassName);
            $this->redirect();
        }

        if ($this->isShopSelected &&
            ((version_compare(_PS_VERSION_, '1.5.0.13', '<') && !Module::isInstalled($this->module->name))
             || (version_compare(_PS_VERSION_, '1.5.0.13', '>=') && !Module::isEnabled($this->module->name)))) {
            $this->warnings[] = $this->l('Module is not enabled in this shop.');
        }

        $this->addRowAction('duplicate');

        return parent::renderList();
    }

    public function renderForm()
    {
        if (Tools::getValue('magic')) {
            return $this->module->getContent();
        }

        if (!$this->isShopSelected && $this->display == 'add') {
            $this->errors[] = $this->l('You have to select a shop if you want to create a new popup.');
            return;
        }

        if ($this->isShopSelected &&
            ((version_compare(_PS_VERSION_, '1.5.0.13', '<') && !Module::isInstalled($this->module->name))
             || (version_compare(_PS_VERSION_, '1.5.0.13', '>=') && !Module::isEnabled($this->module->name)))) {
            $this->warnings[] = $this->l('Module is not enabled in this shop.');
        }

        if (!($object = $this->loadObject(true))) {
            return;
        }

        // Controller exceptions
        $controllers = Dispatcher::getControllers(_PS_FRONT_CONTROLLER_DIR_);
        $module_controllers = $this->getModuleControllers();
        $exceptions_controllers = array_merge($controllers, $module_controllers);
        $list_controllers = $this->getControllersExceptions($exceptions_controllers);

        //Get lists data
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $id_lang = (int)$this->context->cookie->id_lang;
        } else {
            $id_lang = (int)$this->context->language->id;
        }

        $groups = Group::getGroups($id_lang, true);

        $categories = Category::getCategories($id_lang, false, false, 'AND c.`level_depth` > 1', 'ORDER BY cl.`name` ASC');
        foreach ($categories as &$category) {
            $category['display_name'] = $category['name'].' (ID: '.$category['id_category'].')';
        }
        $categories_selected = $categories;

        $countries = Country::getCountries($id_lang);

        $this->multiple_fieldsets = true;
        $this->default_form_language = $this->context->language->id;

        $fieldsFormIndex = 0;
        $this->fields_form[$fieldsFormIndex]['form'] = array(
            'legend' => array(
                'title' => $this->l('Popup configuration'),
                'icon' => 'icon-wrench'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'name',
                    'required' => true,
                    'size' => 20,
                    'col' => '3',
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Active'),
                    'name' => 'active',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'datetime' : 'date',
                    'label' => $this->l('Date from'),
                    'name' => 'date_init',
                    'size' => 10
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'datetime' : 'date',
                    'label' => $this->l('Date to'),
                    'name' => 'date_end',
                    'size' => 10
                ),
                array(
                    'type' => 'free',
                    'label' => $this->l('Schedule'),
                    'name' => 'schedule',
                    'hint' => $this->l('Select days of week and hours to display the popup (Click on the box to enable or disable the day and define the time range)')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Priority'),
                    'name' => 'priority',
                    'class' => 'fixed-width-sm',
                    'required' => true,
                    'col' => '1',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'type' => 'submit',
            ),
            'buttons' => array(
                    'save-and-stay' => array(
                    'title' => $this->l('Save and stay'),
                    'name' => 'submitAdd'.$this->table.'AndStay',
                    'type' => 'submit',
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-save'
                ),
            )
        );

        $fieldsFormIndex++;
        $this->fields_form[$fieldsFormIndex]['form'] = array(
            'legend' => array(
                'title' => $this->l('Popup triggers'),
                'icon' => 'icon-wrench'
            ),
            'input' => array(
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display when page loads'),
                    'name' => 'display_on_load',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'display_on_load_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'display_on_load_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Wait X seconds after page loads to display popup'),
                    'name' => 'secs_to_display',
                    'class' => 'fixed-width-sm',
                    'col' => '9',
                    'suffix' => $this->l('seconds'),
                    'desc' => $this->l('Leave empty if you want to display popup as soon as page loads'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display after product is added to cart'),
                    'name' => 'display_after_cart',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'display_after_cart_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'display_after_cart_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Wait X seconds after product is added to the cart'),
                    'name' => 'secs_to_display_cart',
                    'class' => 'fixed-width-sm',
                    'col' => '9',
                    'suffix' => $this->l('seconds'),
                    'desc' => $this->l('Leave empty if you want to display popup immediately after product is added to the cart'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display when user leaves the window (exit popup)'),
                    'name' => 'display_on_exit',
                    'class' => 't',
                    'col' => '9',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'display_on_exit_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'display_on_exit_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('This event is not triggered in touch devices.'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display when user clicks on an element'),
                    'name' => 'display_on_click',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'display_on_click_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'display_on_click_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('jQuery element selector'),
                    'name' => 'display_on_click_selector',
                    'class' => 'fixed-width-xxl',
                    'col' => '9',
                    'desc' => $this->l('Example: $("footer .header1")').'</br>'.$this->l('You can use extension "jQuery Unique Selector" to get the correct jQuery selector'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Display after customer has viewed X pages'),
                    'name' => 'show_on_view_page_nbr',
                    'col' => '9',
                    'class' => 'fixed-width-sm',
                    'desc' => $this->l('Popup will be displayed after customer has seen this number of pages'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Display again after X minutes from last view'),
                    'name' => 'show_every_nbr_hours',
                    'class' => 'fixed-width-sm',
                    'col' => '9',
                    'suffix' => $this->l('minutes'),
                    'desc' => $this->l('Leave empty if you want to display it each time that page loads or event is fired').'</br>'.$this->l('Set "99999" if you only want to display it once'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'type' => 'submit',
            ),
            'buttons' => array(
                    'save-and-stay' => array(
                    'title' => $this->l('Save and stay'),
                    'name' => 'submitAdd'.$this->table.'AndStay',
                    'type' => 'submit',
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-save'
                ),
            )
        );

        $fieldsFormIndex++;
        $this->fields_form[$fieldsFormIndex]['form'] = array(
            'legend' => array(
                'title' => $this->l('Content and appearance'),
                'icon' => 'icon-magic'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Position'),
                    'name' => 'position',
                    'class' => 't',
                    'col' => '2',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => '1',
                                'name' => $this->l('Top left'),
                            ),
                            array(
                                'id' => '2',
                                'name' => $this->l('Top center'),
                            ),
                            array(
                                'id' => '3',
                                'name' => $this->l('Top right'),
                            ),
                            array(
                                'id' => '4',
                                'name' => $this->l('Center left'),
                            ),
                            array(
                                'id' => '5',
                                'name' => $this->l('Center'),
                            ),
                            array(
                                'id' => '6',
                                'name' => $this->l('Center right'),
                            ),
                            array(
                                'id' => '7',
                                'name' => $this->l('Bottom left'),
                            ),
                            array(
                                'id' => '8',
                                'name' => $this->l('Bottom center'),
                            ),
                            array(
                                'id' => '9',
                                'name' => $this->l('Bottom right'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display "Don\'t show this message again" option'),
                    'name' => 'dont_display_again',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'dont_display_again_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'dont_display_again_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Content'),
                    'name' => 'content',
                    'id' => 'html_content',
                    'lang' => true,
                    'autoload_rte' => version_compare(_PS_VERSION_, '1.6', '>=') ? '' : true,
                    'cols' => 100,
                    'rows' => 10,
                    'class' => version_compare(_PS_VERSION_, '1.6', '>=') ? 'apc_tiny' : '',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Background color'),
                    'name' => 'color_background',
                    'size' => '5'
                ),
                array(
                    'type' => 'free',
                    'label' => $this->l('Background image'),
                    'name' => 'image_background',
                ),
                array(
                    'type' => 'free',
                    'name' => 'image_background_preview',
                ),
                array(
                    'type' => 'free',
                    'label' => $this->l('Content image'),
                    'name' => 'image',
                ),
                array(
                    'type' => 'free',
                    'name' => 'image_preview',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Image link'),
                    'name' => 'image_link',
                    'lang' => true,
                    'col' => '9',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Image link target'),
                    'name' => 'image_link_target',
                    'class' => 't',
                    'col' => '2',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => '_self',
                                'name' => $this->l('In the same page'),
                            ),
                            array(
                                'id' => '_blank',
                                'name' => $this->l('In a new page'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Background opacity'),
                    'name' => 'back_opacity_value',
                    'class' => 't',
                    'col' => '2',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => '1',
                                'name' => '1'
                            ),
                            array(
                                'id' => '0.9',
                                'name' => '0.9',
                            ),
                            array(
                                'id' => '0.8',
                                'name' => '0.8',
                            ),
                            array(
                                'id' => '0.7',
                                'name' => '0.7',
                            ),
                            array(
                                'id' => '0.6',
                                'name' => '0.6',
                            ),
                            array(
                                'id' => '0.5',
                                'name' => '0.5',
                            ),
                            array(
                                'id' => '0.4',
                                'name' => '0.4',
                            ),
                            array(
                                'id' => '0.3',
                                'name' => '0.3',
                            ),
                            array(
                                'id' => '0.2',
                                'name' => '0.2',
                            ),
                            array(
                                'id' => '0.1',
                                'name' => '0.1',
                            ),
                            array(
                                'id' => '0',
                                'name' => '0',
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Popup height (in px or in %)'),
                    'name' => 'popup_height',
                    'lang' => true,
                    'col' => '9',
                    'class' => 'fixed-width-sm',
                    'desc' => $this->l('Default is px').'<br>'.$this->l('Leave empty to calculate size automatically'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Popup width (in px or in %)'),
                    'name' => 'popup_width',
                    'lang' => true,
                    'col' => '9',
                    'class' => 'fixed-width-sm',
                    'desc' => $this->l('Default is px').'<br>'.$this->l('Leave empty to calculate size automatically'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Popup padding'),
                    'name' => 'popup_padding',
                    'suffix' => 'px',
                    'lang' => true,
                    'col' => '9',
                    'class' => 'fixed-width-sm',
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display scroll in the content page'),
                    'name' => 'locked',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'locked_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'locked_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Close popup automatically after X seconds'),
                    'name' => 'secs_to_close',
                    'class' => 'fixed-width-sm',
                    'col' => '5',
                    'suffix' => $this->l('seconds'),
                    'desc' => $this->l('Leave empty if you don\'t want to close it automatically'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Close popup when background is clicked'),
                    'name' => 'close_on_background',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'close_on_background_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'close_on_background_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Blur background'),
                    'name' => 'blur_background',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'blur_background_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'blur_background_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Open effect'),
                    'name' => 'open_effect',
                    'class' => 't',
                    'col' => '2',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => 'zoom',
                                'name' => $this->l('Zoom'),
                            ),
                            array(
                                'id' => 'drop',
                                'name' => $this->l('Drop'),
                            ),
                            array(
                                'id' => 'superscale',
                                'name' => $this->l('Super scaled'),
                            ),
                            array(
                                'id' => 'fadescale',
                                'name' => $this->l('Fade in and scale up'),
                            ),
                            array(
                                'id' => 'slideright',
                                'name' => $this->l('Slide in from the right'),
                            ),
                            array(
                                'id' => 'slidebottom',
                                'name' => $this->l('Slide in from the bottom'),
                            ),
                            array(
                                'id' => 'newspaper',
                                'name' => $this->l('Newspaper (twirl in)'),
                            ),
                            array(
                                'id' => 'fall',
                                'name' => $this->l('Fall'),
                            ),
                            array(
                                'id' => 'sidefall',
                                'name' => $this->l('Fall from the side'),
                            ),
                            array(
                                'id' => 'stickyup',
                                'name' => $this->l('Slide from the top'),
                            ),
                            array(
                                'id' => 'horizontalflip',
                                'name' => $this->l('Horizontal 3D flip'),
                            ),
                            array(
                                'id' => 'verticalflip',
                                'name' => $this->l('Vertical 3D flip'),
                            ),
                            array(
                                'id' => 'sign',
                                'name' => $this->l('3D swinging sign'),
                            ),
                            array(
                                'id' => 'slit',
                                'name' => $this->l('3D growing slit'),
                            ),
                            array(
                                'id' => 'rotatebottom',
                                'name' => $this->l('3D rotate from bottom'),
                            ),
                            array(
                                'id' => 'rotateleft',
                                'name' => $this->l('3D rotate from left'),
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Add a CSS class to layout'),
                    'name' => 'css_class',
                    'lang' => true,
                    'col' => '9',
                    'class' => 'fixed-width-xxl'
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('CSS Rules'),
                    'name' => 'css',
                    'class' => 'css_content',

                    'lang' => true,
                    'cols' => 100,
                    'rows' => 10,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'type' => 'submit',
            ),
            'buttons' => array(
                    'save-and-stay' => array(
                    'title' => $this->l('Save and stay'),
                    'name' => 'submitAdd'.$this->table.'AndStay',
                    'type' => 'submit',
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-save'
                ),
            )
        );

        $fieldsFormIndex++;
        $this->fields_form[$fieldsFormIndex]['form'] = array(
            'legend' => array(
                'title' => $this->l('Who to display popup'),
                'icon' => 'icon-check'
            ),
            'buttons' => array(
                'save-and-stay' => array(
                    'title' => $this->l('Save and stay'),
                    'name' => 'submitAdd'.$this->table.'AndStay',
                    'type' => 'submit',
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-save'
                ),
            ),
            'input' => array(
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display in mobile'),
                    'name' => 'display_mobile',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'display_mobile_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'display_mobile_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display in tablet'),
                    'name' => 'display_tablet',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'display_tablet_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'display_tablet_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display in desktop'),
                    'name' => 'display_desktop',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'display_desktop_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'display_desktop_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display only to customers subscribed to newsletter'),
                    'name' => 'show_customer_newsletter',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'show_customer_newsletter_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'show_customer_newsletter_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display only to customers NOT subscribed to newsletter'),
                    'name' => 'show_customer_not_newsletter',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'show_customer_not_newsletter_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'show_customer_not_newsletter_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display only if string in URL'),
                    'name' => 'switch_display_url_string',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'display_url_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'display_url_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('String'),
                    'name' => 'display_url_string',
                    'class' => 'switch_display_url_string',
                    'lang' => true,
                    'col' => '5',
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display only if customer comes from this referrer'),
                    'name' => 'switch_display_referrer_string',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'display_referrer_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'display_referrer_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Referrer URL'),
                    'name' => 'display_referrer_string',
                    'class' => 'switch_display_referrer_string',
                    'lang' => true,
                    'col' => '5',
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display only to these IPs'),
                    'name' => 'switch_display_ip_string',
                    'class' => 't',
                    'col' => '1',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'display_ip_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'display_ip_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('IPs (separated by \',\' character)'),
                    'name' => 'display_ip_string',
                    'class' => 'switch_display_ip_string',
                    'col' => '5',
                ),
            ),
        );

        $gendersCollection = Gender::getGenders((int)$this->context->language->id);
        if ($gendersCollection->count() > 1) {
            foreach ($gendersCollection as $gendersKey => $gender) {
                $genders[$gendersKey]['id_gender'] = $gender->id;
                $genders[$gendersKey]['name'] = $gender->name;
            }

            $genderFieldSwitch = array(
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Filter by gender'),
                'name' => 'switch_genders',
                'class' => 't',
                'col' => '1',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'switch_genders_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'switch_genders_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                ),
            );
            $genderField = array(
                'type' => 'swap-custom',
                'label' => $this->l('Display only to the selected genders'),
                'name' => 'genders[]',
                'class' => 'switch_genders',
                'search' => true,
                'options' => array(
                    'query' => $genders,
                    'id' => 'id_gender',
                    'name' => 'name'
                )
            );

            array_push($this->fields_form[$fieldsFormIndex]['form']['input'], $genderFieldSwitch, $genderField);
        }

        if ($this->getNbCustomers() && ($this->getNbCustomers() < $this->nbItemsLightMode)) {
            $customers = Customer::getCustomers(true);
            $customerFieldSwitch = array(
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Filter by customer'),
                'name' => 'switch_customers',
                'class' => 't',
                'col' => '1',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'switch_customers_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'switch_customers_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                ),
            );
            $customerField = array(
                'type' => 'swap-custom',
                'label' => $this->l('Display only to the selected customers'),
                'name' => 'customers[]',
                'class' => 'switch_customers',
                'search' => true,
                'options' => array(
                    'query' => $customers,
                    'id' => 'id_customer',
                    'name' => 'email'
                )
            );

            array_push($this->fields_form[$fieldsFormIndex]['form']['input'], $customerFieldSwitch, $customerField);
        }

        if (count($groups) && Group::isFeatureActive()) {
            $groupFieldSwitch = array(
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Filter by customers group'),
                'name' => 'switch_groups',
                'class' => 't',
                'col' => '1',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'switch_groups_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'switch_groups_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                )
            );

            $groupField = array(
                'type' => 'swap-custom',
                'label' => $this->l('Display only to the selected customer group(s)'),
                'name' => 'groups[]',
                'class' => 'switch_groups',
                'search' => true,
                'options' => array(
                    'query' => $groups,
                    'id' => 'id_group',
                    'name' => 'name'
                )
            );

            array_push($this->fields_form[$fieldsFormIndex]['form']['input'], $groupFieldSwitch, $groupField);
        }

        array_push(
            $this->fields_form[$fieldsFormIndex]['form']['input'],
            array(
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Filter by country'),
                'name' => 'switch_countries',
                'class' => 't',
                'col' => '1',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'switch_countries_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'switch_countries_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'swap-custom',
                'label' => $this->l('Display popup in these countries'),
                'name' => 'countries[]',
                'class' => 'switch_countries',
                'search' => true,
                'options' => array(
                    'query' => $countries,
                    'id' => 'id_country',
                    'name' => 'name'
                )
            ),
            array(
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Filter by zone'),
                'name' => 'switch_zones',
                'class' => 't',
                'col' => '1',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'switch_zones_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'switch_zones_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'swap-custom',
                'label' => $this->l('Display only in the selected zones'),
                'name' => 'zones[]',
                'class' => 'switch_zones',
                'search' => true,
                'options' => array(
                    'query' => Zone::getZones(),
                    'id' => 'id_zone',
                    'name' => 'name'
                )
            )
        );

        array_push(
            $this->fields_form[$fieldsFormIndex]['form']['input'],
            array(
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Filter by cart amount'),
                'name' => 'cart_amount',
                'class' => 't',
                'col' => '1',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'cart_amount_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'cart_amount_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'text',
                'label' => $this->l('From'),
                'name' => 'cart_amount_from',
                'suffix' => $this->context->currency->sign,
                'col' => '2',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('To'),
                'name' => 'cart_amount_to',
                'suffix' => $this->context->currency->sign,
                'col' => '2',
            ),
            array(
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Filter by products in the cart'),
                'name' => 'switch_categories_selected',
                'class' => 't',
                'col' => '1',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'switch_categories_selected_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'switch_categories_selected_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'free',
                'label' => $this->l('Display only if the cart has this number of products of the selected categories'),
                'name' => 'nb_products_comparator',
            ),
            array(
                'type' => 'swap-custom',
                'class' => 'switch_categories_selected',
                'name' => 'categories_selected[]',
                'search' => true,
                'options' => array(
                    'query' => $categories_selected,
                    'id' => 'id_category',
                    'name' => 'display_name'
                ),
            )
        );

        array_push(
            $this->fields_form[$fieldsFormIndex]['form']['input'],
            array(
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Filter by language'),
                'name' => 'switch_languages',
                'class' => 't',
                'col' => '1',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'switch_languages_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'switch_languages_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'swap-custom',
                'label' => $this->l('Display only in the selected languages'),
                'name' => 'languages[]',
                'class' => 'switch_languages',
                'search' => true,
                'options' => array(
                    'query' => Language::getLanguages(false),
                    'id' => 'id_lang',
                    'name' => 'name'
                )
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Display only if screen size is higher than X px'),
                'name' => 'responsive_min',
                'lang' => true,
                'col' => '2',
                'suffix' => $this->l('px'),
            ),
            array(
                'type' => 'text',
                'label' => $this->l('Display only if screen size is lower than X px'),
                'name' => 'responsive_max',
                'lang' => true,
                'col' => '2',
                'suffix' => $this->l('px'),
            )
        );

        $this->fields_form[$fieldsFormIndex]['form']['submit'] = array(
            'title' => $this->l('Save'),
            'type' => 'submit',
        );

        $fieldsFormIndex++;
        $this->fields_form[$fieldsFormIndex]['form'] = array(
            'legend' => array(
                'title' => $this->l('Where to display popup'),
                'icon' => 'icon-check'
            ),
            'buttons' => array(
                'save-and-stay' => array(
                    'title' => $this->l('Save and stay'),
                    'name' => 'submitAdd'.$this->table.'AndStay',
                    'type' => 'submit',
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-save'
                ),
            ),
            'input' => array(),
        );

        if ($this->getNbProducts() && $this->getNbProducts() < $this->nbItemsLightMode) {
            $products = $this->getProductsLite($id_lang, true, true);
            foreach ($products as &$product) {
                $product['display_name'] = $product['name'] . ' (ID: ' . $product['id_product'] . ')';
            }
            unset($product);
            $productFieldSwitch = array(
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Filter by product'),
                'name' => 'switch_products',
                'class' => 't',
                'col' => '1',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'switch_products_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'switch_products_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                ),
            );
            $productField = array(
                'type' => 'swap-custom',
                'label' => $this->l('Display popup in the selected products'),
                'name' => 'products[]',
                'class' => 'switch_products',
                'search' => true,
                'options' => array(
                    'query' => $products,
                    'id' => 'id_product',
                    'name' => 'display_name'
                )
            );

            array_push($this->fields_form[$fieldsFormIndex]['form']['input'], $productFieldSwitch, $productField);
        }

        array_push(
            $this->fields_form[$fieldsFormIndex]['form']['input'],
            array(
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Filter by product stock'),
                'name' => 'product_stock',
                'class' => 't',
                'col' => '1',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'product_stock_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'product_stock_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'text',
                'label' => $this->l('From'),
                'name' => 'product_stock_from',
                'prefix' => '>=',
                'col' => '2',
            ),
            array(
                'type' => 'text',
                'label' => $this->l('To'),
                'name' => 'product_stock_to',
                'prefix' => '<=',
                'col' => '2',
            )
        );

        if (Combination::isFeatureActive()) {
            $attributes = Attribute::getAttributes((int)$this->context->language->id);
            if ($attributes) {
                foreach ($attributes as &$attribute) {
                    $attribute['display_name'] = $attribute['public_name'] . ' - ' . $attribute['name'] . ' (ID: ' . $attribute['id_attribute'] . ')';
                }
                unset($attribute);

                if (count($attributes) < $this->nbItemsLightMode) {
                    array_push(
                        $this->fields_form[$fieldsFormIndex]['form']['input'],
                        array(
                            'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                            'label' => $this->l('Filter by attribute'),
                            'name' => 'switch_attributes',
                            'class' => 't',
                            'col' => '1',
                            'is_bool' => true,
                            'values' => array(
                                array(
                                    'id' => 'switch_attribute_on',
                                    'value' => 1,
                                    'label' => $this->l('Enabled')
                                ),
                                array(
                                    'id' => 'switch_attribute_off',
                                    'value' => 0,
                                    'label' => $this->l('Disabled')
                                )
                            ),
                        ),
                        array(
                            'type' => 'swap-custom',
                            'label' => $this->l('Select attributes'),
                            'name' => 'attributes[]',
                            'class' => 'switch_attributes if_value_switch_attributes',
                            'search' => true,
                            'sort' => 'display_name',
                            'options' => array(
                                'query' => $attributes,
                                'id' => 'id_attribute',
                                'name' => 'display_name'
                            ),
                            'desc' => $this->l('Select the products where the popup will be displayed. If you don\'t select any value, the popup will be displayed to all products'),
                        )
                    );
                }
            }
        }

        if (Feature::isFeatureActive()) {
            $features = array();
            $featureGroups = Feature::getFeatures((int)$this->context->language->id);
            foreach ($featureGroups as $featureGroup) {
                $featuresValue = FeatureValue::getFeatureValuesWithLang((int)$this->context->language->id, $featureGroup['id_feature']);
                foreach ($featuresValue as $featureValue) {
                    $featureValue['display_name'] = $featureGroup['name'] . ' - ' . $featureValue['value'] . ' (ID: ' . $featureValue['id_feature_value'] . ')';
                    array_push($features, $featureValue);
                }
            }

            if (count($features) < $this->nbItemsLightMode) {
                array_push(
                    $this->fields_form[$fieldsFormIndex]['form']['input'],
                    array(
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'label' => $this->l('Filter by feature'),
                        'name' => 'switch_features',
                        'class' => 't',
                        'col' => '1',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'switch_features_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'switch_features_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'swap-custom',
                        'label' => $this->l('Select features'),
                        'name' => 'features[]',
                        'class' => 'switch_features',
                        'search' => true,
                        'sort' => 'display_name',
                        'options' => array(
                            'query' => $features,
                            'id' => 'id_feature_value',
                            'name' => 'display_name'
                        ),
                        'desc' => $this->l('Select the products where the popup will be displayed. If you don\'t select any value, the popup will be displayed to all products'),
                    )
                );
            }
        }

        array_push(
            $this->fields_form[$fieldsFormIndex]['form']['input'],
            array(
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Filter by category'),
                'name' => 'switch_categories',
                'class' => 't',
                'col' => '1',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'switch_categories_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'switch_categories_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'swap-custom',
                'label' => $this->l('Display only in the selected categories or to the products that belong to the selected categories'),
                'name' => 'categories[]',
                'class' => 'switch_categories',
                'search' => true,
                'options' => array(
                    'query' => $categories,
                    'id' => 'id_category',
                    'name' => 'display_name'
                )
            )
        );

        $manufacturers = Manufacturer::getManufacturers(false, $id_lang, false);
        if ($manufacturers) {
            $manufacturerFieldSwitch = array(
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Filter by manufacturer'),
                'name' => 'switch_manufacturers',
                'class' => 't',
                'col' => '1',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'switch_manufacturers_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'switch_manufacturers_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                ),
            );

            $manufacturerField = array(
                'type' => 'swap-custom',
                'label' => $this->l('Display only in the selected manufacturers or to the products that belong to the selected manufacturers'),
                'name' => 'manufacturers[]',
                'class' => 'switch_manufacturers',
                'search' => true,
                'options' => array(
                    'query' => $manufacturers,
                    'id' => 'id_manufacturer',
                    'name' => 'name'
                )
            );

            array_push($this->fields_form[$fieldsFormIndex]['form']['input'], $manufacturerFieldSwitch, $manufacturerField);
        }

        $suppliers = Supplier::getSuppliers(false, $id_lang, false);
        if (count($suppliers)) {
            $supplierFieldSwitch = array(
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Filter by supplier'),
                'name' => 'switch_suppliers',
                'class' => 't',
                'col' => '1',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'switch_suppliers_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'switch_suppliers_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                ),
            );

            $supplierField = array(
                'type' => 'swap-custom',
                'label' => $this->l('Display popup in the selected suppliers or to the products that belong to the selected suppliers'),
                'name' => 'suppliers[]',
                'class' => 'switch_suppliers',
                'search' => true,
                'options' => array(
                    'query' => $suppliers,
                    'id' => 'id_supplier',
                    'name' => 'name'
                )
            );

            array_push($this->fields_form[$fieldsFormIndex]['form']['input'], $supplierFieldSwitch, $supplierField);
        }

        array_push(
            $this->fields_form[$fieldsFormIndex]['form']['input'],
            array(
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Filter by page/controller'),
                'name' => 'switch_controller_exceptions',
                'class' => 't',
                'col' => '1',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'switch_controller_exceptions_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'switch_controller_exceptions_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'swap-custom',
                'label' => $this->l('Display only in the selected pages/controllers'),
                'name' => 'controller_exceptions[]',
                'class' => 'switch_controller_exceptions',
                'search' => true,
                'options' => array(
                    'query' => $list_controllers,
                    'id' => 'id',
                    'name' => 'name'
                )
            ),
            array(
                'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                'label' => $this->l('Filter by CMS'),
                'name' => 'switch_cms',
                'class' => 't',
                'col' => '1',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'switch_cms_on',
                        'value' => 1,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'switch_cms_off',
                        'value' => 0,
                        'label' => $this->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'swap-custom',
                'label' => $this->l('Display only in the selected CMS'),
                'name' => 'cms[]',
                'class' => 'switch_cms',
                'search' => true,
                'options' => array(
                    'query' => CMS::getCMSPages($this->context->language->id, null, false, $this->context->shop->id),
                    'id' => 'id_cms',
                    'name' => 'meta_title'
                )
            )
        );

        $this->fields_form[$fieldsFormIndex]['form']['submit'] = array(
            'title' => $this->l('Save'),
            'type' => 'submit',
        );

        $fieldsFormIndex++;
        if (Validate::isLoadedObject($object)) {
            $this->fields_form[$fieldsFormIndex]['form'] = array(
                'legend' => array(
                    'title' => $this->l('Preview'),
                    'icon' => 'icon-photo',
                ),
                'input' => array(
                    array(
                        'col' => 12,
                        'type' => 'free',
                        'label' => '',
                        'name' => 'preview_notice',
                        'class' => 't',
                        'lang' => true,
                    ),
                    array(
                        'col' => 12,
                        'type' => 'free',
                        'label' => '',
                        'name' => 'preview_button',
                        'class' => 't',
                        'lang' => true,
                    ),
                )
            );
        }

        $this->context->smarty->assign(array(
            'products_selected' => $object->products,
            'products_available' => '',//$products,
            'object' => $object,
            'id_image' => 'image',
            'id_image_background' => 'image_background',
            'languages' => Language::getLanguages(false),
            'default_form_language' => $this->default_form_language,
            'image_dir' => _MODULE_DIR_.AdvancedPopupCreator::$image_dir_front,
            'images' => $object->image,
            'images_background' => $object->image_background,
            'delete_url' => self::$currentIndex.'&'.$this->identifier.'='.$object->id_advancedpopup.'&token='.Tools::getAdminTokenLite($this->tabClassName).'&deleteImage=1',
            'schedule' => $object->schedule,
        ));

        //Load db values for select inputs
        $this->fields_value = array(
            'image_background' => $this->context->smarty->fetch(_PS_MODULE_DIR_.'advancedpopupcreator/views/templates/admin/image-background.tpl'),
            'image_background_preview' => $this->context->smarty->fetch(_PS_MODULE_DIR_.'advancedpopupcreator/views/templates/admin/image-background-preview.tpl'),
            'image' => $this->context->smarty->fetch(_PS_MODULE_DIR_.'advancedpopupcreator/views/templates/admin/image.tpl'),
            'image_preview' => $this->context->smarty->fetch(_PS_MODULE_DIR_.'advancedpopupcreator/views/templates/admin/image-preview.tpl'),
            'nb_products_comparator' => $this->context->smarty->fetch(_PS_MODULE_DIR_.'advancedpopupcreator/views/templates/admin/nb_products_template.tpl'),
            'preview_notice' => '<div class="alert alert-warning">'.$this->l('Save changes before previewing popup.').'</div>',
            'preview_button' => '<a target="_blank" class="btn btn-default" href="'. $this->context->link->getPageLink('index').'?previewPopup=1&popupId='.(int)$object->id.'"><i class="process-icon-expand"></i>Preview</a>',
            'schedule' =>  $this->context->smarty->fetch($this->module->getLocalPath().'views/templates/admin/schedule.tpl')
        );

        // Multiselects
        $multiselects = array(
            'controller_exceptions',
            'groups',
            'zones',
            'countries',
            'categories',
            'categories_selected',
            'manufacturers',
            'products',
            'genders',
            'customers',
            'suppliers',
            'cms',
            'languages',
            'attributes',
            'features'
        );
        foreach ($multiselects as $multiselect) {
            $this->fields_value[$multiselect.'[]'] = explode(',', Tools::isSubmit('submitAdd'.$this->table) ? Tools::getValue($multiselect) : $object->$multiselect);
        }

        //Format dates
        if ($object->id) {
            if (strtotime($object->date_init) == 0) {
                $object->date_init = '';
            }

            if (strtotime($object->date_end) == 0) {
                $object->date_end = '';
            }
        }

        $this->content .= parent::renderForm();
    }

    public function processSave()
    {
        // Multiselects
        $multiselects = array(
            'controller_exceptions',
            'groups',
            'zones',
            'countries',
            'categories',
            'categories_selected',
            'manufacturers',
            'products',
            'genders',
            'customers',
            'suppliers',
            'cms',
            'languages',
            'attributes',
            'features'
        );

        foreach ($multiselects as $multiselect) {
            $_POST[$multiselect] = Tools::getValue($multiselect) ? implode(',', Tools::getValue($multiselect)) : '';
        }

        $_POST['date_init'] = ((Tools::getValue('date_init') == '') ? date('Y-m-d H:i:s', 0) : Tools::getValue('date_init'));
        $_POST['date_end'] = ((Tools::getValue('date_end') == '') ? date('Y-m-d H:i:s', 0) : Tools::getValue('date_end'));

        parent::processSave();
    }

    protected function afterAdd($object)
    {
        return $this->afterUpdate($object);
    }

    protected function afterUpdate($object)
    {
        if (Validate::isLoadedObject($object)) {
            // Save file for each language
            foreach (Language::getLanguages(false) as $language) {
                if ($result = $this->module->uploadImage(_PS_MODULE_DIR_.AdvancedPopupCreator::$image_dir, 'image_'.$language['id_lang'], $language['id_lang'], $object)) {
                    $object->image[$language['id_lang']] = $result;
                }

                if ($result = $this->module->uploadImage(_PS_MODULE_DIR_.AdvancedPopupCreator::$image_dir, 'image_background_'.$language['id_lang'], $language['id_lang'], $object)) {
                    $object->image_background[$language['id_lang']] = $result;
                }
            }

            // Save
            $object->save();
        }

        return parent::afterUpdate($object);
    }

    protected function getProductsLite($id_lang, $only_active = false, $front = false)
    {
        $sql = 'SELECT p.`id_product`, pl.`name`, product_shop.`id_shop`
                FROM `'._DB_PREFIX_.'product` p
                '.Shop::addSqlAssociation('product', 'p').'
                LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` '.Shop::addSqlRestrictionOnLang('pl').')
                WHERE pl.`id_lang` = '.(int)$id_lang.
                    ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').
                    ($only_active ? ' AND product_shop.`active` = 1' : '');

        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $rq;
    }

    protected function getNbProducts()
    {
        $sql = 'SELECT count(*)
                FROM `'._DB_PREFIX_.'product` p
                '.Shop::addSqlAssociation('product', 'p');

        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    protected function getNbCustomers()
    {
        $sql = 'SELECT count(*)
                FROM `'._DB_PREFIX_.'customer`
                WHERE 1 '.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER);

        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    private function _createTemplate($tpl_name)
    {
        if ($this->override_folder) {
            if ($this->context->controller instanceof ModuleAdminController) {
                $override_tpl_path = $this->context->controller->getTemplatePath().$tpl_name;
            } elseif ($this->module) {
                $override_tpl_path = _PS_MODULE_DIR_.$this->module_name.'/views/templates/admin/'.$tpl_name;
            } else {
                if (file_exists($this->context->smarty->getTemplateDir(1).DIRECTORY_SEPARATOR.$this->override_folder.$this->base_folder.$tpl_name)) {
                    $override_tpl_path = $this->context->smarty->getTemplateDir(1).DIRECTORY_SEPARATOR.$this->override_folder.$this->base_folder.$tpl_name;
                } elseif (file_exists($this->context->smarty->getTemplateDir(0).DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$this->override_folder.$this->base_folder.$tpl_name)) {
                    $override_tpl_path = $this->context->smarty->getTemplateDir(0).'controllers'.DIRECTORY_SEPARATOR.$this->override_folder.$this->base_folder.$tpl_name;
                }
            }
        } else if ($this->module) {
            $override_tpl_path = _PS_MODULE_DIR_.$this->module_name.'/views/templates/admin/'.$tpl_name;
        }
        if (isset($override_tpl_path) && file_exists($override_tpl_path)) {
            return $this->context->smarty->createTemplate($override_tpl_path, $this->context->smarty);
        } else {
            return $this->context->smarty->createTemplate($tpl_name, $this->context->smarty);
        }
    }

    protected function getControllersExceptions($array)
    {
        $list_controllers = array();
        $i = 0;

        foreach ($array as $key => $value) {
            $meta = Meta::getMetaByPage($key, (int)$this->context->cookie->id_lang);

            $list_controllers[$i]['id'] = $key;
            $list_controllers[$i]['value'] = $key;
            if ($meta && $meta['title']) {
                $list_controllers[$i]['name'] = $key.' - '.$meta['title'].' ('.$meta['url_rewrite'].')';
            } elseif ($key == 'auth') {
                $list_controllers[$i]['name'] = $key.' - '.$this->l('Authentication');
            } elseif ($key == 'category') {
                $list_controllers[$i]['name'] = $key.' - '.$this->l('Category page');
            } elseif ($key == 'category') {
                $list_controllers[$i]['name'] = $key.' - '.$this->l('CMS page');
            } elseif ($key == 'index') {
                $list_controllers[$i]['name'] = $key.' - '.$this->l('Home page');
            } elseif ($key == 'myaccount') {
                $list_controllers[$i]['name'] = $key.' - '.$this->l('My account page');
            } elseif ($key == 'orderopc') {
                $list_controllers[$i]['name'] = $key.' - '.$this->l('Checkout');
            } elseif ($key == 'product') {
                $list_controllers[$i]['name'] = $key.' - '.$this->l('Product page');
            } else {
                $list_controllers[$i]['name'] = $value;
            }

            $i++;
        }

        return $list_controllers;
    }

    public static function getModuleControllers($type = 'all')
    {
        $modules_controllers = array();
        $modules = Module::getModulesDirOnDisk();

        foreach ($modules as $module) {
            foreach (Dispatcher::getControllersInDirectory(_PS_MODULE_DIR_.$module.'/controllers/') as $controller) {
                if ($type == 'front') {
                    if (strpos($controller, 'Admin') === false) {
                        $modules_controllers['module'.$module.$controller] = $controller.' - Module: '.$module;
                    }
                } else {
                    $modules_controllers['module'.$module.$controller] = $controller.' - Module: '.$module;
                }
            }
        }

        return $modules_controllers;
    }

    public function printValidDate($value)
    {
        if (strtotime($value)) {
            return $value;
        }

        return '∞';
    }

    public function printValidIcon($value, $conf)
    {
        $today = date("Y-m-d H:i:s");

        if ($conf['date_init'] > $today) {
            $date_title = $this->l("Future rule");
            if (strtotime($conf['date_init']) > 0) {
                $date_title = $date_title.'. '.$this->l("Begins in:").' '.$conf['date_init'];
            }
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                return '<span class="time-column future-date" title="'.$date_title.'"></span>';
            } else {
                return '<span class="time-column future-date-icon" title="'.$date_title.'"><i class="icon-time"></i></span>';
            }
        }

        if (strtotime($conf['date_end']) == 0 || $today < $conf['date_end']) {
            $date_title = $this->l("Valid rule");
            if (strtotime($conf['date_init']) > 0 && strtotime($conf['date_end']) > 0) {
                $date_title = $date_title.'. '.$this->l("From:").' '.$conf['date_init'].'. '.$this->l("Until:").' '.$conf['date_end'];
            } else if (strtotime($conf['date_init']) > 0 && strtotime($conf['date_end']) == 0) {
                $date_title = $date_title.'. '.$this->l("From:").' '.$conf['date_init'].' ('.$this->l("no expires").')';
            } else if (strtotime($conf['date_init']) == 0 && strtotime($conf['date_end']) > 0) {
                $date_title = $date_title.'. '.$this->l("Until:").' '.$conf['date_end'];
            } else {
                $date_title = $date_title.' ('.$this->l("no expires").')';
            }

            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                return '<span class="time-column valid-date" title="'.$date_title.'"></span>';
            } else {
                return '<span class="time-column valid-date-icon" title="'.$date_title.'"><i class="icon-time"></i></span>';
            }
        } else {
            $date_title = $this->l("Expired rule");
            if (strtotime($conf['date_init']) > 0 && strtotime($conf['date_end']) > 0) {
                $date_title = $date_title.'. '.$this->l("Between:").' '.$conf['date_init'].' '.$this->l("and:").' '.$conf['date_end'];
            } else {
                $date_title = $date_title.'. '.$this->l("From:").' '.$conf['date_end'];
            }
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                return '<span class="time-column expired-date" title="'.$date_title.'"></span>';
            } else {
                return '<span class="time-column expired-date-icon" title="'.$date_title.'"><i class="icon-time"></i></span>';
            }
        }
    }

    public function displayDuplicateLink($token = null, $id = null)
    {
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $tpl = $this->createTemplate('list_action_duplicate_15.tpl');

            $tpl->assign(array(
                'href' => self::$currentIndex.'&'.$this->identifier.'='.$id.'&duplicate'.$this->table.'&token='.($token != null ? $token : $this->token),
                'action' => $this->l('Duplicate'),
            ));
        } else {
            $tpl = $this->createTemplate('list_action_duplicate.tpl');
            if (!array_key_exists('Bad SQL query', self::$cache_lang)) {
                self::$cache_lang['Duplicate'] = $this->l('Duplicate', 'Helper');
            }

            $duplicate = self::$currentIndex.'&'.$this->identifier.'='.$id.'&duplicate'.$this->table;

            $tpl->assign(array(
                'href' => self::$currentIndex.'&'.$this->identifier.'='.$id.'&duplicate'.$this->table.'&token='.($token != null ? $token : $this->token),
                'action' => self::$cache_lang['Duplicate'],
                'location' => $duplicate.'&token='.($token != null ? $token : $this->token),
            ));
        }

        return $tpl->fetch();
    }

    public function processDuplicate()
    {
        $advancedPopup = new AdvancedPopup((int)Tools::getValue('id_advancedpopup'));
        if (Validate::isLoadedObject($advancedPopup)) {
            unset($advancedPopup->id);
            $advancedPopup->active = 0;

            if ($advancedPopup->add()) {
                //Copy files
                foreach (Language::getLanguages(false) as $language) {
                    if ($advancedPopup->image[$language['id_lang']]) {
                        $oldImg = _PS_MODULE_DIR_.AdvancedPopupCreator::$image_dir.$advancedPopup->image[$language['id_lang']];
                        $pathInfo = pathinfo($oldImg);
                        $newImgFileName = Tools::substr(str_shuffle(MD5(microtime())), 0, 15).'.'.$pathInfo['extension'];
                        $newImg = _PS_MODULE_DIR_.AdvancedPopupCreator::$image_dir.$newImgFileName;

                        if (!copy($oldImg, $newImg)) {
                            echo "failed to copy";
                        }

                        $advancedPopup->image[$language['id_lang']] = $newImgFileName;
                    }

                    if ($advancedPopup->image_background[$language['id_lang']]) {
                        $oldImg = _PS_MODULE_DIR_.AdvancedPopupCreator::$image_dir.$advancedPopup->image_background[$language['id_lang']];
                        $pathInfo = pathinfo($oldImg);
                        $newImgFileName = Tools::substr(str_shuffle(MD5(microtime())), 0, 15).'.'.$pathInfo['extension'];
                        $newImg = _PS_MODULE_DIR_.AdvancedPopupCreator::$image_dir.$newImgFileName;

                        if (!copy($oldImg, $newImg)) {
                            echo "failed to copy";
                        }

                        $advancedPopup->image_background[$language['id_lang']] = $newImgFileName;
                    }
                }

                $advancedPopup->save();

                $this->redirect_after = self::$currentIndex.(Tools::getIsset('id_advancedpopup') ? '&id_advancedpopup='.(int)Tools::getValue('id_advancedpopup') : '').'&conf=19&token='.$this->token;
            } else {
                $this->errors[] = Tools::displayError('An error occurred while creating an object.');
            }
        }
    }
}
