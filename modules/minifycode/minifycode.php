<?php

/**
 * 2017 Keshva Thakur
 * @author Keshva Thakur
 * @copyright Keshva Thakur
 * @license   https://www.prestashop.com/en/osl-license
 * @version   2.1.4
 */
if (!defined('_PS_VERSION_'))
    exit;

class MINIFYCODE extends Module {

    public function __construct() {
        $this->name = 'minifycode';
        $this->description = 'Minify HTML CSS JS';
        $this->tab = 'front_office_features';
        $this->version = '3.1.2';
        $this->author = 'Keshva Thakur';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->module_key = '600c149930fb6d0da957b6c9bc6fb288';
        $this->author_address = '0xD04CFFC02eCb7ea7Aa8D92c47607fD79f2C5c901';
        $this->_html = '';
        $this->unique_string = '';
        $this->valid = '';
        $this->simple_content_files_location = $this->_path . 'views/';
        $this->ignore_changes_content_changes = false;


        parent::__construct();

        $this->displayName = $this->l('Minify HTML CSS JS ');
        $this->description = $this->l('Minify HTML CSS JS is a very small and efficient module');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');



        if (Configuration::get('html_minify_ps')) {
            if (!function_exists('smartyMinifyHTMLCustomCode')) {

                $this->context->smarty->registerFilter(
                        'output', __CLASS__ . '::smartyMinifyHTMLCustomCode'
                );

            }
        }


        if (Configuration::get('js_minify_ps')) {
            if (!function_exists('smartyPackJSinHTMLCustom')) {
                 $this->context->smarty->registerFilter(
                        'output', __CLASS__ . '::smartyPackJSinHTMLCustom'
                );
            }
        }
    }
    
    public static  function smartyMinifyHTMLCustomCode($tpl_output, Smarty_Internal_Template $template) {

                    $context = Context::getContext();
                    if (isset($context->controller) && in_array($context->controller->php_self, array('pdf-invoice', 'pdf-order-return', 'pdf-order-slip'))) {
                        return $tpl_output;
                    }
                    $class = new MINIFYCODE();
                    $tpl_output = $class->minifyHTML($tpl_output);
                    return $tpl_output;
                }
    
    
    public static  function smartyPackJSinHTMLCustom($tpl_output, Smarty_Internal_Template $template) {
                    $context = Context::getContext();
                    if (isset($context->controller) && in_array($context->controller->php_self, array('pdf-invoice', 'pdf-order-return', 'pdf-order-slip'))) {
                        return $tpl_output;
                    }
                    $class = new MINIFYCODE();
                    $tpl_output = $class->minifyJS($tpl_output);
                    return $tpl_output;
   }
    
   public function minifyHTML($html_content) {
        if (strlen($html_content) > 0) {

            require_once('views/templates/tools/minify_html/minify_html.class.php');
            $html_content = str_replace(chr(194) . chr(160), '&nbsp;', $html_content);
            if (trim($minified_content = Minify_HTML::minify($html_content, array('cssMinifier', 'jsMinifier'))) != '') {
                $html_content = $minified_content;
            }

            return $html_content;
        }
        return false;
    }

    public function minifyJS($tpl_output) {
        $context = Context::getContext();
        if (isset($context->controller) && in_array($context->controller->php_self, array('pdf-invoice', 'pdf-order-return', 'pdf-order-slip'))) {
            return $tpl_output;
        }
        $class = new MINIFYCODE();
        $tpl_output = $class->packJSinHTML($tpl_output);
        return $tpl_output;
    }

    public static $pattern_js = '/(<\s*script(?:\s+[^>]*(?:javascript|src)[^>]*)?\s*>)(.*)(<\s*\/script\s*[^>]*>)/Uims';
    protected static $pattern_keepinline = 'data-keepinline';

    public static function packJSinHTML($html_content) {
        if (strlen($html_content) > 0) {
            $html_content_copy = $html_content;
            $class = new MINIFYCODE();
            if (!preg_match('/' . MINIFYCODE::$pattern_keepinline . '/', $html_content)) {
                $html_content = preg_replace_callback(
                        MINIFYCODE::$pattern_js, array('MINIFYCODE', 'packJSinHTMLpregCallback'), $html_content, $class->getBackTrackLimit());

                // If the string is too big preg_replace return an error
                // In this case, we don't compress the content
                if (function_exists('preg_last_error') && preg_last_error() == PREG_BACKTRACK_LIMIT_ERROR) {
                    if (_PS_MODE_DEV_) {
                        Tools::error_log('ERROR: PREG_BACKTRACK_LIMIT_ERROR in function packJSinHTML');
                    }
                    return $html_content_copy;
                }
            }
            return $html_content;
        }
        return false;
    }

    public static function getBackTrackLimit() {
        static $limit = null;
        if ($limit === null) {
            $limit = @ini_get('pcre.backtrack_limit');
            if (!$limit) {
                $limit = -1;
            }
        }

        return $limit;
    }

    public static function packJSinHTMLpregCallback($preg_matches) {
        if (!(trim($preg_matches[2]))) {
            return $preg_matches[0];
        }
        $preg_matches[1] = $preg_matches[1] . '/* <![CDATA[ */';
        $class = new MINIFYCODE();
        $preg_matches[2] = $class->packJS($preg_matches[2]);
        $preg_matches[count($preg_matches) - 1] = '/* ]]> */' . $preg_matches[count($preg_matches) - 1];
        unset($preg_matches[0]);
        $output = implode('', $preg_matches);
        return $output;
    }

    public static function packJS($js_content) {
        if (!empty($js_content)) {
            require_once('views/templates/tools/js_minify/jsmin.php');
            try {
                $js_content = JSMin::minify($js_content);
            } catch (Exception $e) {
                if (_PS_MODE_DEV_) {
                    echo $e->getMessage();
                }
                return ';' . trim($js_content, ';') . ';';
            }
        }
        return ';' . trim($js_content, ';') . ';';
    }

    public function install() {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        if (!parent::install() || !$this->registerHook('header') || !$this->EnableHTMLCompress()) {
            Configuration::updateValue('html_minify_ps', 0);
            Configuration::updateValue('js_minify_ps', 0);
            return false;
        }
        return true;
    }

    public function uninstall() {

        if (!parent::uninstall() || !$this->DisableHTMLCompress()) {
            Configuration::deleteByName('html_minify_ps');
            Configuration::deleteByName('js_minify_ps');
            return false;
        }
        return true;
    }

    public function customCopy($source, $dest, $options = array('folderPermission' => '0755', 'filePermission' => '0644')) {
        $result = false;

        if (is_file($source)) {
            if ($dest[strlen($dest) - 1] == '/') {
                if (!file_exists($dest)) {
                    cmfcDirectory::makeAll($dest, $options['folderPermission'], true);
                }
                $__dest = $dest . "/" . basename($source);
            } else {
                $__dest = $dest;
            }
            $result = copy($source, $__dest);
            @chmod($__dest, $options['filePermission']);
        } elseif (is_dir($source)) {
            if ($dest[strlen($dest) - 1] == '/') {
                if ($source[strlen($source) - 1] == '/') {
                    //Copy only contents
                } else {
                    //Change parent itself and its contents
                    $dest = $dest . basename($source);
                    if (!file_exists($dest))
                        mkdir($dest);
                    @chmod($dest, $options['filePermission']);
                }
            } else {
                if ($source[strlen($source) - 1] == '/') {
                    //Copy parent directory with new name and all its content
                    if (!file_exists($dest))
                        mkdir($dest, $options['folderPermission']);
                    @chmod($dest, $options['filePermission']);
                } else {
                    //Copy parent directory with new name and all its content
                    if (!file_exists($dest))
                        mkdir($dest, $options['folderPermission']);
                    @chmod($dest, $options['filePermission']);
                }
            }

            $dirHandle = opendir($source);
            while ($file = readdir($dirHandle)) {
                if ($file != "." && $file != "..") {
                    if (!is_dir($source . "/" . $file)) {
                        $__dest = $dest . "/" . $file;
                    } else {
                        $__dest = $dest . "/" . $file;
                    }
                    //echo "$source/$file ||| $__dest<br />";
                    $result = $this->customCopy($source . "/" . $file, $__dest, $options);
                }
            }
            closedir($dirHandle);
        } else {
            $result = false;
        }
        return $result;
    }

    function mycopy($s1, $s2) {
        $path = pathinfo($s2);
        if (!file_exists($path['dirname'])) {
            mkdir($path['dirname'], 0777, true);
        }
        if (!copy($s1, $s2)) {
            echo "copy failed \n";
        }
    }

    public function DisableHTMLCompress() {
        return true;
    }

    public function EnableHTMLCompress() {
        if (!file_exists(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'minify_html' . DIRECTORY_SEPARATOR . 'minify_html.class.php')) {
//            $code_files = _PS_MODULE_DIR_ . $this->name . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR;
//            $dest = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR;
//            $this->customCopy($code_files, $dest);
        }
        return true;
    }

    public function DisableCSSCompress() {
        return true;
    }

    public function EnableCSSCompress() {
        return true;
    }

    public function DisableJSCompress() {
        return true;
    }

    public function EnableJSCompress() {
        if (!file_exists(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'minify_html' . DIRECTORY_SEPARATOR . 'jsmin.php')) {
//            $code_files = _PS_MODULE_DIR_ . $this->name . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR;
//            $dest = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR;
//            $this->customCopy($code_files, $dest);
        }
        return true;
    }

    public function getContent() {
        $this->processSubmit();
        return $this->displayForm();
    }

    public function processSubmit() {

        if (Tools::isSubmit('submit' . $this->name)) {
            $on_off_option = Tools::getValue('html_minify_ps');
            if ($on_off_option == '1') {
                $this->EnableHTMLCompress();
                Configuration::updateValue('html_minify_ps', $on_off_option);
                $this->_html .= $this->displayConfirmation("Minify HTML enable sucessfully");
            }
            if ($on_off_option == '0') {
                $this->DisableHTMLCompress();
                Configuration::updateValue('html_minify_ps', $on_off_option);
                $this->_html .= $this->displayConfirmation("Minify HTML disable sucessfully");
            }


            $on_off_css = Tools::getValue('css_minify_ps');
            if ($on_off_css == '1') {
                $this->EnableCSSCompress();
                Configuration::updateValue('PS_CSS_THEME_CACHE', $on_off_css);
                $this->_html .= $this->displayConfirmation("Minify CSS enable sucessfully");
            }
            if ($on_off_css == '0') {
                $this->DisableCSSCompress();
                Configuration::updateValue('PS_CSS_THEME_CACHE', $on_off_css);
                $this->_html .= $this->displayConfirmation("Minify CSS disable sucessfully");
            }


            $on_off_js = Tools::getValue('js_minify_ps');
            if ($on_off_js == '1') {
                $this->EnableJSCompress();
                Configuration::updateValue('js_minify_ps', $on_off_js);
                $this->_html .= $this->displayConfirmation("Minify JS enable sucessfully");
            }
            if ($on_off_js == '0') {
                $this->DisableJSCompress();
                Configuration::updateValue('js_minify_ps', $on_off_js);
                $this->_html .= $this->displayConfirmation("Minify JS disable sucessfully");
            }
        }
    }

    public function displayForm() {


        $fields_form = array();
        $html_minify_ps = Configuration::get('html_minify_ps');
        $css_minify_ps = Configuration::get('PS_CSS_THEME_CACHE');
        $js_minify_ps = Configuration::get('js_minify_ps');

        $fields_form[]['form'] = array(
            'input' => array(
                array(
                    'name' => 'topform',
                    'type' => 'topform',
                    'html_minify_ps' => $html_minify_ps,
                    'css_minify_ps' => $css_minify_ps,
                    'js_minify_ps' => $js_minify_ps,
                ),
            ),
        );


        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        return $this->_html . $helper->generateForm($fields_form);
    }

}
