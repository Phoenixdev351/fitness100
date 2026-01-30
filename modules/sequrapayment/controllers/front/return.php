<?php

class SequrapaymentReturnModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * Initialize order confirmation controller.
     *
     * @see FrontController::init()
     */
    public function init()
    {
        sleep(10);
        parent::init();
        $this->id_cart = (int) (Tools::getValue('id_cart', 0));

        $redirectLink = 'index.php?controller=order';

        $this->id_module = (int) (Tools::getValue('id_module', 0));
        $this->id_order = SequraTools::getOrderIdByCartId((int) ($this->id_cart));
        $this->secure_key = Tools::getValue('key', false);

        if (!$this->id_order || !$this->id_module || !$this->secure_key || empty($this->secure_key)) {
            //@todo: add error message
            Tools::redirect($redirectLink . (Tools::isSubmit('slowvalidation') ? '&slowvalidation' : ''));
        }
        Tools::redirect('index.php?' . http_build_query(array(
            'controller' => 'order-confirmation',
            'id_cart' => $this->id_cart,
            'id_module' => $this->id_module,
            'id_order' => $this->id_order,
            'key' => $this->secure_key,
            'sq_product'=> Tools::getValue('sq_product', 'i1'),
        )));
    }
}
