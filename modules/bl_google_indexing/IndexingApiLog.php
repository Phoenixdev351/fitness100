<?php
/**
 * 2010-2021 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2021 Bl Modules
 * @license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class IndexingApiLog
{
    const LOG_LIMIT_DAYS = 300;
    const LOG_ROWS_LIMIT = 250;

    public static function add($url, $action, $responsePhrase, $responseMessage)
    {
        self::deleteOld();

        Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'blmod_indexing_api_log
            (`url`, `action`, `response_phrase`, `response_message`, created_at)
            VALUES
            ("'.pSQL($url).'", "'.pSQL($action).'", "'.pSQL($responsePhrase).'", "'.pSQL($responseMessage).'", "'.pSQL(date('Y-m-d H:i:s')).'")
        ');
    }

    public static function deleteOld()
    {
        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_indexing_api_log 
            WHERE created_at < "'.pSQL(date('Y-m-d', strtotime(date('Y-m-d').'-'.self::LOG_LIMIT_DAYS.'days'))).' 23:59:59"');
    }

    public function getLogs($logPageUrl = '')
    {
        $where = '';

        if (!empty($logPageUrl)) {
            $where = 'WHERE l.url LIKE "%'.$logPageUrl.'%"';
        }

        $logs = Db::getInstance()->ExecuteS('SELECT * 
            FROM '._DB_PREFIX_.'blmod_indexing_api_log l
            '.$where.'
            ORDER BY l.id DESC
            LIMIT '.self::LOG_ROWS_LIMIT);

        if (empty($logs)) {
            return [];
        }

        foreach ($logs as $k => $l) {
            if (IndexingApi::RESPONSE_OK != $l['response_phrase']) {
                $logs[$k]['error'] = 'Error unknown';
                $message = json_decode($l['response_message']);

                if (!empty($message->error)) {
                    $logs[$k]['error'] = $message->error->message;
                }
            }
        }

        return $logs;
    }

    public function countLogsTotal()
    {
        return Db::getInstance()->getValue('SELECT COUNT(l.id) FROM '._DB_PREFIX_.'blmod_indexing_api_log l');
    }

    public function countCurrentDayLogsTotal()
    {
        $day = date('Y-m-d');

        return Db::getInstance()->getValue('SELECT COUNT(l.id) 
            FROM '._DB_PREFIX_.'blmod_indexing_api_log l 
            WHERE l.created_at >= "'.$day.' 00:00:00" AND l.created_at <= "'.$day.' 23:59:59" AND l.`action` = "'.IndexingApi::ACTION_UPDATE.'"');
    }
}
