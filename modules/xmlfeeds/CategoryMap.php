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
    exit;
}

class CategoryMap
{
    private $errors = [];

    public function saveMapFile()
    {
        $this->errors = [];
        $title = Tools::getValue('category_map_name');

        if (empty($_FILES['map_file']['name']) || empty($title)) {
            $this->errors[] = 'Empty file or title';
            return false;
        }

        if ($_FILES['map_file']['type'] != 'text/plain') {
            $this->errors[] = 'Invalid file format, should be .txt';
            return false;
        }

        if (empty($_FILES['map_file']['size'])) {
            $this->errors[] = 'File is empty';

            return false;
        }

        if (Tools::getOctets(ini_get('upload_max_filesize')) <= $_FILES['map_file']['size']) {
            $this->errors[] = 'File to big, max size '.ini_get('upload_max_filesize');
            return false;
        }

        $name = ($this->getLastMapId()+1).'_'.$this->sanitizeName($_FILES['map_file']['name']);

        if (!move_uploaded_file($_FILES['map_file']['tmp_name'], $this->getFilePath($name))) {
            $this->errors[] = 'System error, please check "ga_categories" directory permissions';
            return false;
        }

        Db::getInstance()->insert(
            'blmod_xml_category_map',
            array(
                'title' => pSQL($title),
                'file_name' => pSQL($name),
            )
        );

        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function delete($id)
    {
        if (empty($id)) {
            return false;
        }

        $fileName = $this->getFileNameById($id);

        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_category_map WHERE id = "'.(int)$id.'"');

        if (empty($fileName)) {
            return false;
        }

        unlink($this->getFilePath($fileName));

        return true;
    }

    public function getFilePath($name = '')
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ga_categories' . DIRECTORY_SEPARATOR . $name;
    }

    public function sanitizeName($name)
    {
        return Tools::strtolower(htmlspecialchars(preg_replace('/[^a-zA-Z0-9_.-]/', '_', $name)));
    }

    public function getLastMapId()
    {
        return (int)Db::getInstance()->getValue('
			SELECT m.id
			FROM '._DB_PREFIX_.'blmod_xml_category_map m
			ORDER BY m.id DESC
		');
    }

    public function getList()
    {
        return Db::getInstance()->ExecuteS('
			SELECT m.id, m.title, m.file_name
			FROM '._DB_PREFIX_.'blmod_xml_category_map m
			ORDER BY m.title ASC
		');
    }

    public function getFileNameById($id)
    {
        return Db::getInstance()->getValue('
			SELECT m.file_name
			FROM '._DB_PREFIX_.'blmod_xml_category_map m
			WHERE m.id = "'.(int)$id.'"
		');
    }

    public function getIdByKey($key)
    {
        return Db::getInstance()->getValue('
			SELECT m.id
			FROM '._DB_PREFIX_.'blmod_xml_category_map m
			WHERE m.`key` = "'.pSQL($key).'"
		');
    }
}
