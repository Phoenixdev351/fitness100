<?php
/**
 * 2010-2022 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2022 Bl Modules
 * @license
 */

if (!defined('_PS_VERSION_')) {
    die('Not Allowed, GoogleCategoryBlMod');
}

class GoogleCategoryBlMod
{
    public $path = '';

    public function __construct($fileName)
    {
        $this->path = _PS_MODULE_DIR_.'xmlfeeds/ga_categories/'.$fileName;
    }

    public function getList()
    {
        $file = $this->readFile();

        if (empty($file)) {
            return false;
        }

        return $file;
    }

    private function readFile()
    {
        $categories = array();

        if (is_file($this->path) && is_readable($this->path)) {
            $handle = fopen($this->path, 'r');
        }

        if (empty($handle)) {
            return false;
        }

        while (($data = fgetcsv($handle, 1000, '-')) !== false) {
            if (empty($data[1])) {
                continue;
            }

            $id = trim($data[0]);

            if (!empty($id) && Tools::substr($id, 0, 1) == '#') {
                continue;
            }

            unset($data[0]);
            $categories[$id] = trim(implode('-', $data));
        }

        return $categories;
    }
}
