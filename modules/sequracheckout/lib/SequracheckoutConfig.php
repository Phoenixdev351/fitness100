<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class SequracheckoutConfig {

    public static $MINIWIDGET_TYPES = array('CATEGORIES', 'CART', 'MINICART');

    public function __construct($module)
    {
        $this->module  = $module;
        $this->context = $this->module->getContext();
    }

    public function getContent()
    {
        SequracheckoutConfigData::updateActivePaymentMethods();
        $output = $this->saveSubmission();
        $this->renderForm();
        $this->context->smarty->assign('module_dir', $this->module->getPath());
        $this->context->smarty->assign('methods', SequracheckoutConfigData::getMerchantPaymentMethods());
        $output .= $this->context->smarty->fetch($this->module->getLocalPath().'views/templates/admin/configure.tpl');
        return $output.$this->renderForm();
    }

    /**
     * Save form data.
     */
    protected function saveSubmission()
    {
        if (Tools::isSubmit('submit')) {
            $form_values = $this->getConfigFormValues();
            array_walk(
                $form_values,
                static function ($value, $key) {
                    Configuration::updateValue($key, trim(Tools::getValue($key)));
                }
            );
            return $this->module->displayConfirmation($this->l('Los datos han sido grabados.'));
        }
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->module = $this->module;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        //$helper->identifier = $this->module->identifier;
        $helper->submit_action = 'submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->module->name.'&tab_module='.$this->module->tab.'&module_name='.$this->module->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        $forms = array_merge(
            array($this->getGeneralConfigForm()),
            array_map(
                array($this, 'getConfigForm'),
                SequracheckoutConfigData::getMerchantPaymentMethods()
            ),
            array($this->getMiniWidgetConfigForm())
        );
        return $helper->generateForm(
            array_filter($forms, static function ($var) {
                return $var !== null;
            })
        );
    }

    /**
     * Create the structure the for each payment method
     */
    protected function getConfigForm($method)
    {
        $product = SequracheckoutConfigData::buildUniqueProductCode($method);
        if (in_array($method['product'], array('fp1','pp10'))) {
            return null;
        }
        $ret = array(
            'form' => array(
                'legend' => array(
                'title' => sprintf(
                    $this->l('"%s" Widget settings'),
                    $method['title']
                ),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('CSS selector where the widget will be placed in product page'),
                        'name' => 'SEQURA_'.$product.'_CSS_SEL',
                        'label' => $this->l('Place CSS Sel.'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('L, R, minimal, legacy... or params in JSON format'),
                        'name' => 'SEQURA_'.$product.'_WIDGET_THEME',
                        'label' => $this->l('Widget visualization params'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show banner in home'),
                        'name' => 'SEQURA_'.$product.'_SHOW_BANNER',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ),
                        ),
                        'desc' => sprintf(
                            $this->l('Add "%s" banner to homepage hook'),
                            $method['title']
                        ),
                        'default' => 0,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                ),
            ),
        );
        return $ret;
    }

    protected function getMiniWidgetConfigForm() {
        $methods = array_filter(
            SequracheckoutConfigData::getMerchantPaymentMethods(),
            function ($method){
                return SequracheckoutConfigData::getFamilyFor($method) == 'PARTPAYMENT';
            }
        );
        if(count($methods) < 1){
            return [];
        }
        $ret = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('MiniWidget settings'),
                'icon' => 'icon-cogs',
                ),
            )
        );
        //MINIWIDGETS OPTIONS ONLY FORM PARTPAYMETNS
        $ret['form']['input'][] = array(
            'type' => 'select',
            'label' => $this->l('Product for miniwidgets'),
            'name' => 'SEQURA_PARTPAYMENT_PRODUCT',
            'options' => array(
                'query' => $options = array_map(
                    function ($method) {
                        return array(
                            'id_option' => $method['product'],
                            'name' => $method['title'],
                        );
                    },
                    $methods
                ),
                'id' => 'id_option',
                'name' => 'name',
            ),
        );
        //CATEGORIES
        $ret['form']['input'][] = array(
            'type' => 'switch',
            'label' => $this->l('Show installment amount in product listings'),
            'name' => 'SEQURA_PARTPAYMENT_CATEGORIES_SHOW',
            'is_bool' => true,
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('No')
                ),
            ),
            'default' => 0,
        );
        $ret['form']['input'][] = array(
            'col' => 3,
            'type' => 'text',
            'desc' => $this->l('Listings prices\' css selector used by widgets to read the price from'),
            'name' => 'SEQURA_PARTPAYMENT_CATEGORIES_CSS_SEL_PRICE',
            'label' => $this->l('Items prices\' css selector'),
        );
        $ret['form']['input'][] = array(
            'col' => 3,
            'type' => 'text',
            'desc' => $this->l('CSS selector where the widgets will be placed in the listing'),
            'name' => 'SEQURA_PARTPAYMENT_CATEGORIES_CSS_SEL',
            'label' => $this->l('Place CSS Sel.'),
        );
        $ret['form']['input'][] = array(
            'col' => 3,
            'type' => 'text',
            'desc' => $this->l('CSS selector where the widgets will be placed in the listing'),
            'name' => 'SEQURA_PARTPAYMENT_CATEGORIES_TEASER_MSG',
            'label' => $this->l('message'),
        );
        //CART
        $ret['form']['input'][] = array(
            'type' => 'switch',
            'label' => $this->l('Show installment amount in shoppingcart page'),
            'name' => 'SEQURA_PARTPAYMENT_CART_SHOW',
            'is_bool' => true,
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('No')
                ),
            ),
            'default' => 0,
        );
        $ret['form']['input'][] = array(
            'col' => 3,
            'type' => 'text',
            'desc' => $this->l('Cart\'s total amount\'s css selector used by widgets to read the price from'),
            'name' => 'SEQURA_PARTPAYMENT_CART_CSS_SEL_PRICE',
            'label' => $this->l('Cart\'s total amount\'s css selector'),
        );
        $ret['form']['input'][] = array(
            'col' => 3,
            'type' => 'text',
            'desc' => $this->l('CSS selector where the widgets will be placed in the shoppingcart page'),
            'name' => 'SEQURA_PARTPAYMENT_CART_CSS_SEL',
            'label' => $this->l('Place CSS Sel.'),
        );
        $ret['form']['input'][] = array(
            'col' => 3,
            'type' => 'text',
            'name' => 'SEQURA_PARTPAYMENT_CART_TEASER_MSG',
            'label' => $this->l('message'),
        );
        //MINICART
        $ret['form']['input'][] = array(
            'type' => 'switch',
            'label' => $this->l('Show installment amount in mini cart summary'),
            'name' => 'SEQURA_PARTPAYMENT_MINICART_SHOW',
            'is_bool' => true,
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('No')
                ),
            ),
            'default' => 0,
        );
        $ret['form']['input'][] = array(
            'col' => 3,
            'type' => 'text',
            'desc' => $this->l('Listings prices\' css selector used by widgets to read the price from'),
            'name' => 'SEQURA_PARTPAYMENT_MINICART_CSS_SEL_PRICE',
            'label' => $this->l('Items prices\' css selector'),
        );
        $ret['form']['input'][] = array(
            'col' => 3,
            'type' => 'text',
            'desc' => $this->l('CSS selector where the widgets will be placed in the listing'),
            'name' => 'SEQURA_PARTPAYMENT_MINICART_CSS_SEL',
            'label' => $this->l('Place CSS Sel.'),
        );
        $ret['form']['input'][] = array(
            'col' => 3,
            'type' => 'text',
            'name' => 'SEQURA_PARTPAYMENT_MINICART_TEASER_MSG',
            'label' => $this->l('message'),
        );
        $ret['form']['submit'] = array(
            'title' => $this->l('Save'),
            'class' => 'btn btn-default pull-right'
        );

        return $ret;
    }
    /**
     * Create the structure the general form
     */
    protected function getGeneralConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('General widget settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Open SeQura form in a new page'),
                        'desc' => $this->l('Some custom checkouts could need to open SeQura chekout in a new page.'),
                        'name' => 'SEQURA_FORCE_NEW_PAGE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            ),
                        ),
                        'default' => 0,
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'label' => $this->l('Price CSS Sel.'),
                        'name' => 'SEQURA_CSS_SEL_PRICE',
                        'desc' => $this->l('Product page\'s price css selector used by widgets to read the price from'),
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $ret = array_reduce(
            SequracheckoutConfigData::getMerchantPaymentMethods(),
            static function ($values, $method) {
                $product = SequracheckoutConfigData::buildUniqueProductCode($method);
                if (SequracheckoutConfigData::getFamilyFor($method) != 'CARD') {

                    $values['SEQURA_'.$product.'_SHOW_BANNER']  = self::getConfigValue(
                        'SEQURA_'.$product.'_SHOW_BANNER'
                    );
                    $values['SEQURA_'.$product.'_CSS_SEL']      = self::getConfigValue(
                        'SEQURA_'.$product.'_CSS_SEL',
                        self::getConfigValue('SEQURA_' . SequracheckoutConfigData::getFamilyFor($method) . '_CSS_SEL')
                    );
                    $values['SEQURA_'.$product.'_WIDGET_THEME'] = self::getConfigValue(
                        'SEQURA_'.$product.'_WIDGET_THEME',
                        self::getConfigValue('SEQURA_' . SequracheckoutConfigData::getFamilyFor($method) . '_WIDGET_THEME')
                    );
                    if (SequracheckoutConfigData::getFamilyFor($method) == 'PARTPAYMENT') {
                        $values['SEQURA_PARTPAYMENT_PRODUCT'] = self::getConfigValue(
                            'SEQURA_PARTPAYMENT_PRODUCT',
                            'pp3'
                        );
                        $values['SEQURA_PARTPAYMENT_CATEGORIES_SHOW'] = self::getConfigValue(
                            'SEQURA_PARTPAYMENT_CATEGORIES_SHOW',
                            0
                        );
                        $values['SEQURA_PARTPAYMENT_CART_SHOW']       = self::getConfigValue(
                            'SEQURA_PARTPAYMENT_CART_SHOW',
                            0
                        );
                        $values['SEQURA_PARTPAYMENT_MINICART_SHOW']   = self::getConfigValue(
                            'SEQURA_PARTPAYMENT_MINICART_SHOW',
                            0
                        );

                        $values['SEQURA_PARTPAYMENT_CATEGORIES_TEASER_MSG'] = self::getConfigValue(
                            'SEQURA_PARTPAYMENT_CATEGORIES_TEASER_MSG',
                            'Desde %s/mes'
                        );
                        $values['SEQURA_PARTPAYMENT_CART_TEASER_MSG']       = self::getConfigValue(
                            'SEQURA_PARTPAYMENT_CART_TEASER_MSG',
                            'Desde %s/mes'
                        );
                        $values['SEQURA_PARTPAYMENT_MINICART_TEASER_MSG']   = self::getConfigValue(
                            'SEQURA_PARTPAYMENT_MINICART_TEASER_MSG',
                            'Desde %s/mes'
                        );

                        $values['SEQURA_PARTPAYMENT_CATEGORIES_CSS_SEL'] = self::getConfigValue(
                            'SEQURA_PARTPAYMENT_CATEGORIES_CSS_SEL',
                            ''
                        );
                        $values['SEQURA_PARTPAYMENT_CART_CSS_SEL']       = self::getConfigValue(
                            'SEQURA_PARTPAYMENT_CART_CSS_SEL',
                            ''
                        );
                        $values['SEQURA_PARTPAYMENT_MINICART_CSS_SEL']   = self::getConfigValue(
                            'SEQURA_PARTPAYMENT_MINICART_CSS_SEL',
                            ''
                        );

                        $values['SEQURA_PARTPAYMENT_CATEGORIES_CSS_SEL_PRICE'] = self::getConfigValue(
                            'SEQURA_PARTPAYMENT_CATEGORIES_CSS_SEL_PRICE',
                            ''
                        );
                        $values['SEQURA_PARTPAYMENT_CART_CSS_SEL_PRICE']       = self::getConfigValue(
                            'SEQURA_PARTPAYMENT_CART_CSS_SEL_PRICE',
                            ''
                        );
                        $values['SEQURA_PARTPAYMENT_MINICART_CSS_SEL_PRICE']   = self::getConfigValue(
                            'SEQURA_PARTPAYMENT_MINICART_CSS_SEL_PRICE',
                            ''
                        );
                    }
                }
                return $values;
            },
            array(
                'SEQURA_CSS_SEL_PRICE'   => self::getConfigValue('SEQURA_CSS_SEL_PRICE'),
                'SEQURA_FORCE_NEW_PAGE'   => self::getConfigValue('SEQURA_FORCE_NEW_PAGE'),
            )
        );
        return $ret;
    }

    protected static function getConfigValue($key, $default = false, $id_lang = null, $id_shop_group = null, $id_shop = null)
    {
        if (Configuration::hasKey($key, $id_lang, $id_shop_group, $id_shop)) {
            return Configuration::get($key, $id_lang, $id_shop_group, $id_shop);
        }
        return $default;
    }

    ///////////////////////////
    protected function l($string)
    {
        return $this->module->l($string);
    }
}
