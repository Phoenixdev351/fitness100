<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class SequraCrontab
{
    public static function calcNextExecutionTime()
    {
        return strtotime("tomorrow") + 3600 * Configuration::get('SEQURA_AUTOCRON_H') + 60 * Configuration::get('SEQURA_AUTOCRON_M');
    }

    public static function poolCron()
    {
        if (Configuration::get('SEQURA_AUTOCRON') &&
            strpos($_SERVER['REQUEST_URI'], 'triggerreport') === false &&
            self::isTimeToSend()
        ) {
            //Avoid retry for 5 min at least.
            Configuration::updateGlobalValue('SEQURA_AUTOCRON_NEXT', strtotime("now")+300);

            $url = self::getTriggerReportUrl();
            $client = new SequraClient();
            $client->callCron($url);
        }
    }

    protected static function isTimeToSend() {
        $next_time = (int)Configuration::getGlobalValue("SEQURA_AUTOCRON_NEXT");
        //Test if nextime is not corrupted
        if ($next_time < strtotime("2 days ago")) {
            Configuration::deleteByName('SEQURA_AUTOCRON_NEXT');
            Configuration::updateGlobalValue('SEQURA_AUTOCRON_NEXT', strtotime("now")+300);
            return false;
        }
        return strtotime("now") > $next_time;
    }

    public static function getTriggerReportUrl()
    {
        if (_PS_VERSION_ >= 1.5) {
            return Context::getContext()->link->getModuleLink('sequrapayment', 'triggerreport');
        }
        return 'http://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'modules/sequrapayment/triggerreport.php';
    }
}
