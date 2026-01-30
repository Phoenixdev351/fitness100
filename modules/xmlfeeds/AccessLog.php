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

class AccessLog
{
    public static function save($feedId, $action, $sessionId, $isCron = 0, $getParam = [], $argParam = [])
    {
        Db::getInstance()->Execute('
            INSERT INTO '._DB_PREFIX_.'blmod_xml_access_log
            (`feed_id`, `is_cron`, `action`, session_id, get_param, argv_param, created_at)
            VALUES
            ("'.(int)$feedId.'", "'.(int)$isCron.'", "'.pSQL($action).'", "'.pSQL($sessionId).'", "'.pSQL(serialize($getParam)).'", "'.pSQL(serialize($argParam)).'", "'.pSQL(date('Y-m-d H:i:s')).'")
        ');
    }

    public static function deleteOld()
    {
        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_access_log WHERE created_at < "'.XmlFeedsTools::dateMinusDays(180).'"');
    }
}
