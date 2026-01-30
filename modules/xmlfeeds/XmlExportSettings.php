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

class XmlExportSettings
{
    private $version = '';
    private $name = '';

    public function isExportAction($version = '', $name = '')
    {
        $isExportSettings = Tools::getValue('export_settings');

        if (empty($isExportSettings)) {
            return false;
        }

        $this->version = $version;
        $this->name = $name;

        $this->downloadSettings();

        return true;
    }

    private function downloadSettings()
    {
        $mysqlInfo = Db::getInstance()->ExecuteS('SHOW VARIABLES LIKE "version"');

        $file = $this->name.' '.$this->version."\n";
        $file .= 'PrestaShop '._PS_VERSION_."\n";
        $file .= 'PHP '.PHP_VERSION."\n";
        $file .= 'MySQL '.(!empty($mysqlInfo[0]['Value']) ? $mysqlInfo[0]['Value'] : 'none')."\n";
        $file .= date('Y-m-d H:i:s');

        $tables = Db::getInstance()->ExecuteS('SELECT TABLE_NAME
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_NAME LIKE "'. _DB_PREFIX_.'blmod_xml%" AND TABLE_SCHEMA = "'.pSQL(_DB_NAME_).'"
            ORDER BY TABLE_NAME ASC');

        if (!empty($tables)) {
            foreach ($tables as $t) {
                $file .= "\n\n".'------------------------------------------------------'."\n\n".$t['TABLE_NAME']."\n\n";
                $fields = Db::getInstance()->ExecuteS('SHOW FULL COLUMNS FROM '.pSQL($t['TABLE_NAME']));

                $file .= serialize($fields) . "\n\n";

                if ($t['TABLE_NAME'] == _DB_PREFIX_.'blmod_xml_access_log' || $t['TABLE_NAME'] == _DB_PREFIX_.'blmod_xml_statistics') {
                    $values = Db::getInstance()->ExecuteS('SELECT *
                        FROM '.pSQL($t['TABLE_NAME']).'
                        ORDER BY id DESC
                        LIMIT 200');
                } else {
                    $values = Db::getInstance()->ExecuteS('SELECT *
                        FROM '.pSQL($t['TABLE_NAME']));
                }

                $file .= serialize($values);
            }
        }

        header('Content-Disposition: attachment; filename="xmlfeedspro_settings_'.date('Ymd_His').'.txt"');
        header('Content-Type: text/plain; charset:UTF-8');
        header('Connection: close');
        echo $file;
        die();
    }
}
