<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class SequraIdentification
{
    public $ssl = true;

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = $module->getContext();
    }

    public function start()
    {
        $this->abortIfUnavailable(null);
        $this->startModule();
    }

    public function startPartpayment()
    {
        $this->abortIfUnavailable('SEQURA_PP_ACTIVE');
        $this->startModule('part');
    }

    private function startModule()
    {
        if ($this->sequraIsReady()) {
            $linker = $this->context->link;
            if (_PS_VERSION_ >= 1.5) {
                $next = $linker->getModuleLink('sequrapayment', 'identification', array(), true);
            } else {
                $next = $linker->getPageLink('modules/sequrapayment/identification.php', true);
            }
            Tools::redirect($next, null); // the null is needed for 1.4
        } else {
            var_dump("I have no idea what happened: " . $this->client->curl_result);
        }
    }

    public function sequraIsReady($reuse = false)
    {
        if ($reuse && $this->recoverUri()) {
            return true;
        }
        $client = $this->client = $this->getSequraCore()->getClient();
        $builder = $this->module->getOrderBuilder();
        $client->startSolicitation($builder->build());
        $this->storeUri($client->getOrderUri());

        return $client->succeeded();
    }

    public function displayForStandardPurchase($controller)
    {
        if ($this->sequraIsReady()) {
            $client = $this->getSequraCore()->getClient();
            $options = array(
                'product' => $this->module->getProduct(),
                'ajax' => Tools::getValue('ajax', false),
                'campaign' => $this->module->getCampaign()
            );
            $identity_form = $client->getIdentificationForm($this->recoverUri(), $options);
        }
        $name = $this->module->getConfigName();
        $this->context->smarty->assign(array(
            'service_name'  => Configuration::get($name.'_NAME'),
            'identity_form' => $identity_form,
        ));
        $this->display($controller);
    }

    public function display($controller)
    {
        $this->abortIfUnavailableOrCookieMissing();

        $this->setVariables();
        $display = $controller ? $controller : new BWDisplay();
        $template = 'views/identification.tpl';
        if (version_compare(_PS_VERSION_, '1.7', '>=') == true) {
            $template = 'module:' . SEQURA_CORE . '/views/identification_17.tpl';
        }
        $display->setTemplate($template);
        if (!$controller) {
            $display->run();
        }
    }

    public function setVariables()
    {
        $this->context->smarty->assign($this->context->cart->getSummaryDetails());
        $this->module->setVariables($this->context->cart);
        $vars = array(
            'render_breadcrumbs_explicitly' => (_PS_VERSION_ < 1.6),
        );
        $this->context->smarty->assign($vars);
    }

    protected function storeUri($uri)
    {
        $name = $this->module->name . '_order';
        Context::getContext()->cookie->__set($name, $uri);
    }

    public function unsetUri()
    {
        $name = $this->module->name . '_order';
        unset(Context::getContext()->cookie->$name);
    }

    protected function recoverUri()
    {
        $name = $this->module->name . '_order';

        return Context::getContext()->cookie->$name;
    }

    private function abortIfUnavailableOrCookieMissing()
    {
        if (!$this->recoverUri()) {
            Tools::redirect('index.php?controller=order');
        }
        $this->abortIfUnavailable();
    }

    private function abortIfUnavailable($function = '')
    {
        $qualifier = $this->module->getQualifier($this->context->cart);
        if (!$qualifier->passes($function)) {
            Tools::redirect('index.php?controller=order');
        }
    }

    private function getAValidCurrency($currency)
    {
        if (trim($currency) === "") {
            return '€';
        } else {
            return '';
        }
    }

    protected function getSequraCore()
    {
        return Module::getInstanceByName(SEQURA_CORE);
    }
}
