<?php

/*
*  @author SeQura <info@sequra.es>
*  @copyright  SeQura Worldwide S.L.
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class SequraOrderConfirmer
{
    private $module;
    private $context;
    private $cart;
    private $customer;
    private $client;
    private $ipn;
    private $sq_state;
    private $sequra;
    private $order;
    private $merchant_id;

    public function __construct($module, $context_or_ipn)
    {
        $this->module = $module;
        if ("ipn" == $context_or_ipn) {
            $this->initFromIpn();
        } else {
            $this->initFromContext($context_or_ipn);
        }
    }

    public function initFromIpn()
    {
        $cart_id = Tools::getValue('cart_id');
        $this->merchant_id = Tools::getValue('merchant_id');
        if (SequraTools::sign($cart_id) != Tools::getValue('signed')) {
            echo "La firma del carrito no concuerda.";
            exit;
        }
        $this->context = Context::getContext();
        $this->cart = new Cart($cart_id);
        $this->context->cart = $this->cart;
        $this->context->customer = new Customer($this->cart->id_customer);
        $address = new Address($this->context->cart->id_address_delivery);
        $this->context->country = new Country($address->id_country);
        $this->context->cookie->id_country = $address->id_country;
        $this->context->cookie->iso_code_country = $this->context->country->iso_code;
        $this->context->language = new Language((int)$this->cart->id_lang);
        $this->context->currency = new Currency((int)$this->cart->id_currency);
        // Not a full URI but the client lib will add the rest:
        $this->sequra_order_uri = Tools::getValue('order_ref');
        $this->ipn = true;
    }

    public function initFromContext($context)
    {
        $this->context = $context;
        $this->cart = $context->cart;
        $this->sequra_order_uri = $context->cookie->sequra_order;
    }

    public function run()
    {
        $this->sq_state = Tools::getValue('sq_state', null);
        $this->order = new Order(
            SequraTools::getOrderIdByCartId($this->cart->id)
        );
        $this->module->currentOrder = !is_null($this->order)?$this->order->id:null;
        $this->module->currentOrderReference = !is_null($this->order)?$this->order->reference:null;
        switch ($this->sq_state) {
            case 'needs_review':
                $this->runSetOnHold();
                break;
            case 'confirmed-without-number':
                $this->runResendOderNumber();
                break;
            default://approved set as default for backward compat
                $this->runConfirm();
        }
    }

    protected function runSetOnHold()
    {
        $this->abortIfOnHoldPreconditionsAreMissing();
        $this->validateWithSequra();
        if (!$this->ipn) {
            $this->redirectToOrderConfirmation();
        }
    }

    protected function runConfirm()
    {
        $this->abortIfConfirmationPreconditionsAreMissing();
        $this->validateWithSequra();
        if (!$this->ipn) {
            $this->redirectToOrderConfirmation();
        }
    }

    protected function runResendOderNumber()
    {
        $this->prepareSequra();
        if (!$this->approvedBySequra()) {
            return;
        }
        if (!$this->module->currentOrder) {
            $this->registerCartAsOrder();
        }
        $this->registerOrderForDelayedReporting();
        $this->sendOrderRefToSequra();
        $this->clearSequraEnvironment();
        die();
    }

    protected function abortIfOnHoldPreconditionsAreMissing()
    {
        if (!$this->module->active ||
            $this->orderIsCreated() ||
            $this->cartIsAbandoned() ||
            $this->customerHasDisappeared()
        ) {
            $this->raise410Error("Raised from abortIfOnHoldPreconditionsAreMissing");
        }
    }

    protected function abortIfConfirmationPreconditionsAreMissing()
    {
        if (!$this->module->active ||
            $this->orderIsCompleted() ||
            $this->cartIsAbandoned() ||
            $this->customerHasDisappeared()
        ) {
            $this->raise410Error('Raised from abortIfConfirmationPreconditionsAreMissing');
        }
    }

    public function orderIsCompleted()
    {
        if ($this->orderIsCreated() && !$this->orderIsPending()) {
            return true;
        }
        return false;
    }

    private function orderIsCreated()
    {
        return !!SequraTools::getOrderIdByCartId($this->cart->id);
    }

    private function orderIsPending()
    {
        if ($this->order->getCurrentState() == Configuration::get('SEQURA_OS_NEEDS_REVIEW') ||
            $this->isOutOfStockState($this->order)) {
            return true;
        }
        return false;
    }

    public function cartIsAbandoned()
    {
        $cart = $this->cart;

        return $cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0;
    }

    public function customerHasDisappeared()
    {
        $cart = $this->cart;
        $this->customer = new Customer($cart->id_customer);

        return !Validate::isLoadedObject($this->customer);
    }
    /**
     * Undocumented function
     *
     * @param boolean $onhold set the order onhold in sequra
     * @return void
     */
    public function validateWithSequra()
    {
        $this->prepareSequra();
        if (!$this->approvedBySequra()) {
            return;
        }
        if ($this->sq_state != 'needs_review' && $this->orderIsCreated()) {
            $this->updateCartsOrderState();
        } else {
            $this->registerCartAsOrder();
        }
        $this->sendOrderRefToSequra();
        if ($this->sq_state != 'needs_review') {
            $this->registerOrderForDelayedReporting();
            $this->clearSequraEnvironment();
        }
    }

    public function prepareSequra()
    {
        $this->client = $this->getSequraCore()->getClient();
        $builder = $this->module->getOrderBuilder($this->merchant_id);
        $this->sequra_order = $builder->build(
            $this->sq_state == 'needs_review'? 'on_hold':'confirmed'
        );
    }

    public function approvedBySequra()
    {
        $this->client->updateOrder($this->sequra_order_uri, $this->sequra_order);
        if ($this->client->succeeded()) {
            return true;
        }
        if ($this->client->cartHasChanged()) {
            if ($this->ipn) {
                $this->raise410Error('Raised from approvedBySequra due to cartHasChanged');
            } else {
                $this->redirectToCartChanged();
            }
        } else {
            die($this->client->dump());
        }

        return false;
    }

    public function restart()
    {
        $linker = $this->context->link;
        if ($this->ipn) {
            $step2 = SequraOrderConfirmer::ipnUrl();
        } else {
            if (_PS_VERSION_ >= 1.5) {
                $step2 = $linker->getModuleLink('sequrapayment', 'confirmation', array('added_fee' => 1), true);
            } else {
                $step2 = $linker->getPageLink('modules/sequrapayment/confirm.php', true);
            }
        }
        Tools::redirect($step2);
    }

    public static function ipnUrl($cart_id = null)
    {
        $linker = Context::getContext()->link;
        $params = array();
        if ($cart_id) {
            $params = array_merge($params, self::ipnParams($cart_id));
        }
        if (_PS_VERSION_ >= 1.5) {
            return $linker->getModuleLink('sequrapayment', 'ipn', $params, true);
        }

        return $linker->getPageLink('modules/sequrapayment/ipn.php', true);
    }

    public static function ipnParams($cart_id)
    {
        $params = array('cart_id' => "" . $cart_id, 'signed' => SequraTools::sign($cart_id));
        if (_PS_VERSION_ >= 1.5) {
            $params['id_shop'] = "" . Context::getContext()->shop->id;
            $params['id_lang'] = "" . Context::getContext()->language->id;
        } else {
            global $cookie;
            $params['id_lang'] = "" . $cookie->id_lang;
        }

        return $params;
    }

    private function isOutOfStockState($order){
        return in_array($order->getCurrentState(),array(
            Configuration::get('PS_OS_OUTOFSTOCK'),
            Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')
        ));
    }

    private function getConfiguredState()
    {
        $state_name = 'SEQURA_OS_' . strtoupper($this->sq_state);
        return (int)Configuration::get($state_name);
    }

    private function getValidOrderState()
    {
        $configured_state = $this->getConfiguredState();
        $order_status = new OrderState(
            $configured_state,
            (int)$this->context->language->id
        );
        if (!Validate::isLoadedObject($order_status)) {
            if ($this->sq_state != 'needs_review') {
                //FAll back to PS_OS_PAYMENT y SEQURA_OS_APPROVED isn't defined
                return (int)Configuration::get('PS_OS_PAYMENT');
            } else { //Just in case State wasn't created plugin wasn't upgraded properly
                $installer = new SequraInstaller($this->module);
                $installer->install();
                $configured_state = $this->getConfiguredState();
            }
        }
        return $configured_state;
    }

    protected function updateCartsOrderState()
    {
        foreach (Order::getByReference($this->order->reference) as $order) {
            $outofstock = $this->isOutOfStockState($order);
            $new_history  = new OrderHistory();
            $new_history->id_order = (int)$order->id;
            $new_history->changeIdOrderState(
                $this->getConfiguredState(),
                (int)$order->id
            );
            $new_history->addWithemail();
            if ($outofstock) {
                $history  = new OrderHistory();
                $history->id_order = (int)$order->id;
                $history->changeIdOrderState(Configuration::get('PS_OS_OUTOFSTOCK_PAID'), $order, true);
                $history->addWithemail();
            }
        }
    }

    protected function registerCartAsOrder()
    {
        $cart = $this->cart;
        $this->module->secret_handshake = true;
        try {
            $this->module->validateOrder(
                (int)$cart->id,
                $this->getValidOrderState(),
                $cart->getOrderTotal(true, Cart::BOTH),
                $this->module->getDisplayName(),
                null,
                array('transaction_id' => preg_replace('/.*\//', '', $this->sequra_order_uri)),
                (int)$this->context->currency->id,
                false,
                $this->context->customer->secure_key
            );
        } catch (Exception $e) {
            $logger = _PS_VERSION_ >= 1.6 ? new PrestaShopLogger() : new Logger();
            $logger->addLog("Exception at order validation: " . $e->getMessage());
            if (!$this->orderIsCompleted()) {
                $this->raise410Error('Raised from registerCartAsOrder due to: ' . $e->getMessage());
            }
            //If the order is completed despite the Exception lets continue
        }
        $this->module->secret_handshake = false;
    }

    public function registerOrderForDelayedReporting()
    {
        $this->module->reporter()->registerSequraOrder($this->module->currentOrder, $this->merchant_id);
    }

    public function sendOrderRefToSequra()
    {
        $order_ref = array(
            'order_ref_1' => $this->module->currentOrder,
        );
        if (_PS_VERSION_ >= 1.5 && 0 == Configuration::get('SEQURA_ORDER_ID_FIELD')) {
            $order_ref = array(
                'order_ref_1' => $this->module->currentOrderReference,
                'order_ref_2' => $this->module->currentOrder,
            );
        }
        $extra = array('merchant_reference' => $order_ref);
        $order = array_merge($this->sequra_order, $extra);
        $this->client->updateOrder($this->sequra_order_uri, $order);
        if (!$this->client->succeeded()) {
            // TODO: find a way to log internally
        }
    }

    public function clearSequraEnvironment()
    {
        $this->context->cookie->sequra_order_invoice = '';
        $this->context->cookie->sequra_order_part = '';
    }

    public function redirectToOrderConfirmation()
    {
        Tools::redirect(
            (_PS_VERSION_ >= 1.5 ? 'index.php?controller=order-confirmation&' : 'order-confirmation.php?')
            . 'id_cart=' . (int)$this->cart->id
            . '&id_module=' . (int)$this->module->id
            . '&id_order=' . $this->module->currentOrder
            . '&key=' . $this->customer->secure_key .
            (Tools::getValue('sq_product') ? '&sq_product=' . Tools::getValue('sq_product') : '')
        );
    }

    public function redirectToCartChanged()
    {
        $linker = Context::getContext()->link;
        if (_PS_VERSION_ >= 1.5) {
            $abort_url = $linker->getPageLink(
                'order',
                true,
                null,
                'step=3&sequra_error=' . SEQURA_ERROR_CART_CHANGED
            );
        } else {
            $abort_url = $linker->getPageLink(
                'order.php',
                true
            ) . '?step=3&sequra_error=' . SEQURA_ERROR_CART_CHANGED;
        }
        Tools::redirect($abort_url);
    }

    public function raise410Error($msg = null)
    {
        if ($this->ipn) {
            http_response_code(410);
            die($msg);
        } else {
            Tools::redirect('index.php?controller=order&step=1'); // FIXME
        }
    }

    protected function getSequraCore()
    {
        if (is_null($this->sequra)) {
            $sequra_class = ucfirst(SEQURA_CORE);
            $this->sequra = new $sequra_class();
        }

        return $this->sequra;
    }
}
