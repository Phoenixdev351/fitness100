<?php
class CategoryController extends CategoryControllerCore
{
    /*
    * module: stoverride
    * date: 2020-08-15 13:21:46
    * version: 1.0.0
    */
    protected function doProductSearch($template, $params = array(), $locale = null)
    {
        if ($this->ajax) {
            ob_end_clean();
            header('Content-Type: application/json');
            $variables = $this->getAjaxProductSearchVariables();
            if(!Configuration::get('STSN_REMOVE_PRODUCTS_VARIABLE'))
                unset($variables['products']);
            $this->ajaxDie(json_encode($variables));
        } else {
            $variables = $this->getProductSearchVariables();
            $this->context->smarty->assign(array(
                'listing' => $variables,
            ));
            $this->setTemplate($template, $params, $locale);
        }
    }
	/*
    * module: sturls
    * date: 2020-08-24 09:48:22
    * version: 1.1.11
    */
    public function canonicalRedirection($canonicalURL = '')
    {
        return;
    }
}
