<?php
if (!defined('_PS_VERSION_')) { exit; }
class Media extends MediaCore
{
    /*
    * module: ets_superspeed
    * date: 2025-03-17 16:01:24
    * version: 2.0.3
    */
    public static function clearCache()
    {
        parent::clearCache();
        Module::getInstanceByName('ets_superspeed')->hookActionAdminPerformanceControllerSaveAfter();
    }
}