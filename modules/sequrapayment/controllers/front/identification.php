<?php

class SequrapaymentIdentificationModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::initContent();
        $this->module->displayIdentificationPage($this);
    }

    // Override template path (would be '/views/templates/front/') so that we can use
    // same template as PS 1.4.
    // In 1.6, this function returns the full path including filename.
    public function getTemplatePath($template = '')
    {
        $theme_tpl = _PS_THEME_DIR_ . SEQURA_CORE . '/' . $template;
        if (file_exists($theme_tpl)) {
            return $theme_tpl;
        }
        return _PS_MODULE_DIR_ . SEQURA_CORE . '/' . $template;
    }
}
