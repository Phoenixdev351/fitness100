<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class RelatedOverrides
{
    public function __construct($af)
    {
        $this->af = $af;
        $module_path = _PS_MODULE_DIR_ . $this->af->name . '/';
        $this->custom_dir = $module_path . 'override_files/' . ($af->is_modern ? '17/' : '16/');
        $this->native_dir = $module_path . 'override/';
    }

    public function processAll($action)
    {
        $action .= 'Override';
        foreach ($this->getData() as $data) {
            $this->process($action, $data['path']);
        }
        $overrides_cache_file = _PS_CACHE_DIR_ . 'class_index.php';
        if (file_exists($overrides_cache_file)) {
            unlink($overrides_cache_file);
        }
    }

    public function process($action, $file_path)
    {
        if ($result = in_array($action, ['addOverride', 'removeOverride'])) {
            $custom_path = $this->custom_dir . $file_path;
            $tmp_native_path = $this->native_dir . $file_path;
            if ($result &= file_exists($custom_path)) {
                if ($result &= is_writable(dirname($tmp_native_path))) {
                    try {
                        $code = Tools::file_get_contents($custom_path);
                        $code = str_replace("if (!defined('_PS_VERSION_')) {\n    exit;\n}\n\n", '', $code);
                        file_put_contents($tmp_native_path, $code);
                        // Tools::copy($custom_path, $tmp_native_path); // copy to /override/ for native processing
                        $class_name = basename($custom_path, '.php');
                        $result &= $this->af->$action($class_name);
                    } catch (Exception $e) {
                        $result = $e->getMessage();
                    }
                    unlink($tmp_native_path);
                } else {
                    $dir_name = str_replace(_PS_ROOT_DIR_, '', dirname($tmp_native_path)) . '/';
                    $result = 'Make sure the following directory is writable: ' . $dir_name;
                }
            }
        }

        return $result;
    }

    public function getData($extended = false)
    {
        $data = [];
        $autoload = PrestaShopAutoload::getInstance();
        foreach (Tools::scandir($this->custom_dir, 'php', '', true) as $file) {
            $class_name = basename($file, '.php');
            if ($class_name != 'index') {
                $path = $autoload->getClassPath($class_name . 'Core');
                $data[$class_name] = ['path' => $path];
                if ($extended) {
                    $data[$class_name] += [
                        'note' => $this->getNote($path),
                        'installed' => $this->isInstalled($path),
                    ];
                }
            }
        }

        return $data;
    }

    public function getNote($file_path)
    {
        $code = Tools::file_get_contents($this->custom_dir . $file_path);
        $code = explode('*  INFO: ', $code);

        return isset($code[1]) ? trim(current(explode('*/', $code[1]))) : '';
    }

    public function isInstalled($file_path)
    {
        $shop_override_path = _PS_OVERRIDE_DIR_ . $file_path;
        $module_override_path = $this->custom_dir . $file_path;
        $methods_to_override = $already_overriden = [];
        if (file_exists($module_override_path)) {
            $lines = file($module_override_path);
            foreach ($lines as $line) {
                if (Tools::substr(trim($line), 0, 6) == 'public') { // NOTE: works only for public functions
                    $key = trim(current(explode('(', $line)));
                    $methods_to_override[$key] = 0;
                }
            }
        }
        $name_length = Tools::strlen($this->af->name);
        if (file_exists($shop_override_path)) {
            $lines = file($shop_override_path);
            foreach ($lines as $i => $line) {
                if (Tools::substr(trim($line), 0, 6) == 'public') {
                    $key = trim(current(explode('(', $line)));
                    if (isset($methods_to_override[$key])) {
                        unset($methods_to_override[$key]);
                        // if there is no comment about installed override
                        if (!isset($lines[$i - 4])
                            || Tools::substr(trim($lines[$i - 4]), -$name_length) !== $this->af->name) {
                            $key = explode('function ', $key);
                            if (isset($key[1])) {
                                $already_overriden[] = $key[1] . '()';
                            }
                        }
                    }
                }
            }
        }
        $result = (bool) !$methods_to_override;
        if ($already_overriden) {
            $result = implode(', ', $already_overriden);
        }

        return $result;
    }
}
