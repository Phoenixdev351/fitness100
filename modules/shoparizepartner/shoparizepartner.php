<?php
/**
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    shoparize <contact@prestashop.com>
*  @copyright 2007-2022 shoparize
*  @license   http://www.gnu.org/licenses/gpl-3.0.html (GPLv3 or later License)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Shoparizepartner extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'shoparizepartner';
        $this->tab = 'analytics_stats';
        $this->version = '1.0.1';
        $this->author = 'Shoparize';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Shoparize Partner');
        $this->description = $this->l(
            'Shoparize’s superior customer service, with a merchant portal to feed and campaign optimization tools, easily adapts to merchant’s needs to drive incremental revenue, even while operating alongside another CSS.
        ');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall my module?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->module_key = 'f585f0c7c635e55e5702fdfda49ac693';
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        Configuration::updateValue(
            'SHOPARIZEPARTNER_SHOP_ID',
            '',
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );

        return parent::install() &&
            $this->registerHook('header');
    }

    public function uninstall()
    {
        Configuration::deleteByName('SHOPARIZEPARTNER_SHOP_ID');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitShoparizepartnerModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitShoparizepartnerModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'prefix' => $this->context->smarty->fetch($this->getLocalPath() . 'views/templates/admin/shop-icon.tpl'),
                        'desc' => $this->context->smarty->fetch($this->getLocalPath() . 'views/templates/admin/shop-id-desc.tpl'),
                        'hint' => $this->context->smarty->fetch($this->getLocalPath() . 'views/templates/admin/shop-id-desc.tpl'),
                        'name' => 'SHOPARIZEPARTNER_SHOP_ID',
                        'label' => $this->l('Shoparize Shop ID'),
                    ),
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
        return array(
            'SHOPARIZEPARTNER_SHOP_ID' => Configuration::get(
                'SHOPARIZEPARTNER_SHOP_ID',
                null,
                Context::getContext()->shop->id_shop_group,
                Context::getContext()->shop->id
            ),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        Configuration::updateValue(
            'SHOPARIZEPARTNER_SHOP_ID',
            Tools::getValue('SHOPARIZEPARTNER_SHOP_ID'),
            false,
            Context::getContext()->shop->id_shop_group,
            Context::getContext()->shop->id
        );

        if (empty($this->context->controller->errors)) {
            $this->context->controller->confirmations[] = $this->l('update successful');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        // force to only load on front office
        if (
            $this->context->controller->controller_type == 'front' ||
            $this->context->controller->controller_type == 'modulefront'
        ) {
            // get shop id
            $SHOPARIZEPARTNER_SHOP_ID = Configuration::get(
                'SHOPARIZEPARTNER_SHOP_ID',
                null,
                Context::getContext()->shop->id_shop_group,
                Context::getContext()->shop->id
            );
            // assign vars to js files
            Media::addJsDef(
                array(
                    'SHOPARIZEPARTNER_SHOP_ID' => $SHOPARIZEPARTNER_SHOP_ID
                )
            );
            // include js file to head of the page
            $this->context->controller->registerJavascript(
                'shoparize_script',
                'https://partner-cdn.shoparize.com/js/shoparize.js',
                array(
                    'server' => 'remote',
                    'position' => 'head',
                )
            );

            $this->context->controller->registerJavascript(
                'shoparize_script_all_front',
                'modules/' . $this->name . '/views/js/shoparizepartner_all_front.js'
            );

            if ('orderconfirmation' == Dispatcher::getInstance()->getController()) {
                // get order
                $id_order = Tools::getValue('id_order');
                $order = new Order($id_order, $this->context->language->id);
                // get order products
                $orderProducts = $order->getProductsDetail();
                $currencyIsoCode = (new Currency($order->id_currency, $this->context->language->id))->iso_code;

                // hold data for js object
                $orderDetail = array();
                $orderDetail['event'] = 'purchase';
                $orderDetail['ecommerce'] = array(
                    'transaction_id' => $order->id,
                    'value' => Tools::ps_round($order->total_paid_tax_incl, 2),
                    'tax' => Tools::ps_round(($order->total_paid_tax_incl - $order->total_paid_tax_excl), 2),
                    'shipping' => Tools::ps_round($order->total_shipping_tax_incl, 2),
                    'currency' => $currencyIsoCode,
                );
                // add products details
                foreach ($orderProducts as $item) {
                    $orderDetail['ecommerce']['items'][] = array(
                        'item_id' => $item['product_id'],
                        'item_name' => addslashes($item['product_name']),
                        'currency' => $currencyIsoCode,
                        'price' => Tools::ps_round($item['total_price_tax_excl'], 2),
                        'quantity' => $item['product_quantity']
                    );
                }

                // assign vars to js files
                Media::addJsDef(
                    array(
                        'order_details_object' => $orderDetail
                    )
                );

                $this->context->controller->registerJavascript(
                    'shoparizepartner_order_confirmation',
                    'modules/' . $this->name . '/views/js/shoparizepartner_order_confirmation.js'
                );
            }
        }
    }
}
