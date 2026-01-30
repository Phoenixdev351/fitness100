<?php
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Dumper\XliffFileDumper AS dumper;
use Symfony\Component\Translation\MessageCatalogue;
class XliffFileDumper
{
    public $debug;
    public $save_path;
    public $theme;
    public $check_missing = 0;
    public $missing = array();
    public function __construct($save_path = null, $debug=false)
    {
        $this->save_path = $save_path ? $save_path : _PS_ROOT_DIR_.'/trans/';
        $this->debug = $debug;
    }
    public function dump($options = array())
    {
        $theme = $options['theme'] ? $options['theme'] : '';
        $locale = $options['locale'] ? $options['locale'] : 'en-US';
        $theme_module = $options['theme_module'] ? $options['theme_module'] : false;
        $modules = $options['modules'] ? $options['modules'] : array();
        $type = $options['type'] ? $options['type'] : 0;
        $tpl_path = isset($options['tpl_path']) ? $options['tpl_path'] : '';
        $this->check_missing = $options['check_missing'] ? $options['check_missing'] : 0;
        $this->theme = $theme;
        
        $translations = array();
        if ($theme && is_dir(_PS_ROOT_DIR_.'/themes/'.$theme)) {
            if ($result = $this->dumpFront($theme, $theme_module)) {
               $translations = array_merge_recursive($translations, $result);
            }
        }
        if (!is_array($modules)) {
            $modules = (array)$modules;
        }
        foreach($modules as $module) {
            if ($result = $this->dumpModule($module)) {
                $translations = array_merge_recursive($translations, $result);
            }
        }
        
        if ($this->check_missing) {
            if ($this->missing) {
                echo 'The following phrases missed in the domain:<br />';
                echo implode("<br />\r\n", $this->missing);
                die;
            } else {
                die('All are okay !');
            }
        }
        
        $catalogue = new MessageCatalogue($locale, array());
        foreach($translations AS $domain => &$messages) {
            // Skip global domain.
            if ($this->need_skip($domain, $theme, $modules, $type)) {
                continue;
            }
            $messages = array_map(function($val){
                if (is_array($val)){
                    return array_pop($val);
                } else {
                    return $val;
                }
            }, $messages);
            if (strtolower($domain) == 'shop.theme.'.$theme) {
                $this->createTranslationFile($messages, $domain, $tpl_path);
            }
            $domain = str_replace('.', '', $domain);
            $catalogue->add($messages, $domain);
            $messages = array();
        }
        if (!is_dir($this->save_path)) {
            (new Filesystem())->mkdir($this->save_path);
        }
        $dumper = new dumper;
        $dumper->dump($catalogue, array(
            'path' => $this->save_path,
            'default_locale' => 'en-US'
        ));
        if ($this->debug) {
            die('Done');
        }
        return true;
    }
    
    protected function dumpModule($module)
    {
        if (!$module) {
            return false;
        }
        $translations = array();
        if (is_dir($path = _PS_MODULE_DIR_.$module)) {
            foreach(array('php','tpl') AS $ext) {
                if($files = $this->getFiles($path, $ext)) {
                    foreach($files AS $file) {
                        if (strrpos($file, 'index.php') !== false) {
                            continue;
                        }
                        $content = file_get_contents($file);
                        if ($this->debug) {
                            echo $file."<br/>\r\n";
                        }
                        $result = $this->userParseFile($content, 'module', $ext);
                        $this->checkMissing($file, $result);
                        if ($result) {
                            if ($this->debug) {
                                print_r($result)."<br/>\r\n";
                            }
                            $translations = array_merge_recursive($translations, $result);
                        }
                    }
                }
            }
        }
        return $translations;
    }
    
    protected function dumpFront($theme, $theme_module=false)
    {
        if (!$theme) {
            return false;
        }
        $translations = array();
        if (is_dir(_PS_ROOT_DIR_.'/themes/'.$theme)) {
            if ($theme_module) {
                $path = _PS_ROOT_DIR_.'/themes/'.$theme;
            } else {
                $path = _PS_ROOT_DIR_.'/themes/'.$theme.'/templates';
            }
            $ext = 'tpl';
            if($files = $this->getFiles($path, $ext)) {
                foreach($files AS $file) {
                    if (strpos($file, '/translation.tpl') !== false) {
                        // Excluded generated file.
                        continue;
                    }
                    $content = file_get_contents($file);
                    if ($this->debug) {
                        echo $file."<br/>\r\n";
                    }
                    $result = $this->userParseFile($content, 'front', $ext);
                    $this->checkMissing($file, $result);
                    if ($result) {
                        if ($this->debug) {
                            print_r($result)."<br/>\r\n";
                        }
                        $translations = array_merge_recursive($translations, $result);
                    }
                }
            }
        }
        return $translations;
    }
    
    public function getFiles($path, $ext='tpl')
    {
        if (!is_dir($path)) {
            return false;
        }
        if (!$ext) {
            $ext = 'tpl';
        }
        $files = array();
        $finder = Finder::create()
            ->files()
            ->filter(function (\SplFileInfo $file) use($ext) {
                return  $ext == $file->getExtension();
            })
            ->in($path)
        ;
        foreach ($finder as $file) {
            $files[] = $file->getPath().'/'.$file->getBasename();
        }
        return $files;
    }
    
    protected function userParseFile($content, $type_translation, $type_file = 'php')
    {
        switch ($type_translation) {
            case 'front':
                // Parsing file in Front office
                if ($type_file == 'php') {
                    $regex = '/->trans\(([\'\"])' . _PS_TRANS_PATTERN_ . '([\'\"])(,\s*?[\[|array\(](.*)[\]|\)])(,\s*?([\'\"])(.*)([\'\"]))?\)/Us';
                } else {
                    $regex = '/\{l\s*s=\s*([\'\"])'._PS_TRANS_PATTERN_.'([\'\"])?(\s*sprintf=\s*\[.*\])?\s+d\s*=\s*[\'\"](.+)[\'\"]\}?/Us';
                }
                break;
            
            case 'module':
                if ($type_file == 'php') {
                    $regex = '/->trans\(([\'\"])' . _PS_TRANS_PATTERN_ . '([\'\"])(,\s*?[\[|array\(](.*)[\]|\)])(,\s*?([\'\"])(.*)([\'\"]))?\)/Us';
                } else {
                    $regex = '/\{l\s*s=\s*([\'\"])'._PS_TRANS_PATTERN_.'([\'\"])?(\s*sprintf=\s*\[.*\])?\s+d\s*=\s*[\'\"](.+)[\'\"]\}?/Us';
                }
                break;
        }

        if (!is_array($regex)) {
            $regex = array($regex);
        }

        $strings = array();
        foreach ($regex as $regex_row) {
            $matches = array();
            $n = preg_match_all($regex_row, $content, $matches);
            if ($type_file == 'php') {
                foreach ($matches[0] as $key => $match) {
                    if (strpos($match, 'trans(') !== false) {
                        $stringToTranslate = trim($matches[2][$key]);
                        if (!$stringToTranslate || $stringToTranslate == '\'' || $stringToTranslate == '"') {
                            continue;
                        }
                        $prefix_key = trim($matches[8][$key]);
                        $strings[$prefix_key][$stringToTranslate] = $stringToTranslate; 
                    }
                }
            } else {
                foreach ($matches[0] as $key => $match) {
                    if ($match) {
                        $stringToTranslate = trim($matches[2][$key]);
                        if (!$stringToTranslate || $stringToTranslate == '\'' || $stringToTranslate == '"') {
                            continue;
                        }
                        $prefix_key = trim($matches[5][$key]);
                        $strings[$prefix_key][$stringToTranslate] = $stringToTranslate; 
                    }
                } 
            }
        }

        return $strings;
    }
    
    protected function need_skip($domain, $theme, $modules, $type=1)
    {
        if (!$domain) {
            return true;
        }
        // Front only.
        if (!$type && strpos(strtolower($domain), 'shop.theme.'.$theme) === false) {
            return true;
        }
        if (strpos(strtolower($domain), $theme) !== false) {
            return false;
        }
        foreach($modules AS $module) {
            if (strpos(strtolower($domain), $module) !== false) {
                return false;
            }
        }
        return true;
    }
    
    public function renameXliffFile($locale='en-US', $ext = 'xlf')
    {
        $extension = $locale.'.'.$ext;
        foreach($files = $this->getFiles($this->save_path, $ext) AS $file) {
            $path = dirname($file);
            $filename = str_replace($extension,'', basename($file));
            $filename = str_replace('.','', $filename);
            @rename($file, $path.'/'.$filename.'.'.$extension);
        }
    }
    
    public function clearFiles($path, $filter='')
    {
        if (!is_dir($path)) {
            return false;
        }
        $finder = Finder::create()
            ->files()
            ->filter(function (\SplFileInfo $file) use($filter) {
                if ($filter) {
                    return strpos($file->getBasename(), $filter) !== false;
                }
                return true;
            })
            ->depth(0)
            ->in($path)
        ;
        foreach ($finder as $file) {
            @unlink(rtrim($file->getPath(), '/').'/'.$file->getBasename());
        }
        return true;
    }
    
    public function CopyFiles($src, $des, $filter='', $locale='en-US', $trim=true)
    {
        if (!is_dir($src) || !is_dir($des)) {
            return false;
        }
        $finder = Finder::create()
            ->files()
            ->filter(function (\SplFileInfo $file) use($filter) {
                if ($filter) {
                    return strpos($file->getBasename(), $filter) !== false;
                }
                return true;
            })
            ->depth(0)
            ->in($src)
        ;
        foreach ($finder as $file) {
            $file_src = rtrim($file->getPath(), '/').'/'.$file->getBasename();
            if ($trim) {
                $file_name = str_replace('.'.$locale, '', $file->getBasename());
            } else {
                $file_name = $file->getBasename();
            }
            @copy($file_src, $des.$file_name);
        }
        return true;
    }
    
    public function checkMissing($file, $translations=array())
    {
        if (!$this->check_missing || !$file || !$translations) {
            return false;
        }
        $file = str_replace(_PS_ROOT_DIR_, '', $file);
        foreach($translations AS $domain => &$messages) {
            $domain2 = str_replace('.', '', $domain);
            if (strpos(strtolower($domain2), $this->theme) !== false || strpos($domain2, 'ModulesSt') !== false) {
                continue;
            }
            $messages = array_map(function($val){
                if (is_array($val)){
                    return array_pop($val);
                } else {
                    return $val;
                }
            }, $messages);
            $message_all = Context::getContext()->getTranslator()->getCatalogue('en-US')->all($domain2);
            foreach($messages AS $message) {
                $message = stripslashes($message);
                if (!key_exists($message, $message_all)) {
                    $this->missing[] = '['.$domain.']=>['.$message. '] in file: '.$file;
                }
            }
        }
    }
    
    public function createTranslationFile($translations = array(), $domain, $save_path)
    {
        if (!$domain || !$save_path) {
            return false;
        }
        $html = '{* Generated by the theme, don\'t modify it manually. *}'."\r\n";
        foreach($translations as $translation) {
            $html .= '{l s=\''.$translation.'\' d=\''.$domain.'\'}'."\r\n";
        }
        @file_put_contents($save_path.'translation.tpl', $html);
    }
}
