<?php
/**
 * This file is part of the securitypro package.
 *
 * @author Mathias Reker
 * @copyright Mathias Reker
 * @license Commercial Software License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (false === \defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class SecurityProCronModuleFrontController
 */
class SecurityProCronModuleFrontController extends ModuleFrontController
{
    /** @var bool */
    public $ajax;
    /** @var bool */
    public $ssl = true;
    /** @var bool */
    public $errors = false;

    /**
     * Display content
     */
    public function display()
    {
        @\ini_set('memory_limit', '8192M');
        @\set_time_limit(3600);
        @\ignore_user_abort(true);

        if (true === (bool) Configuration::get('PRO_DEBUG_CRON')) {
            @\ini_set('display_errors', 1);
            @\ini_set('display_startup_errors', 1);
            @\error_reporting(\E_ALL);
        }

        \header('Access-Control-Allow-Origin: *');

        $this->ajax = true;

        // Check token
        if (false === Tools::isPHPCLI()) {
            if ($this->module->encrypt('securitypro/cron') !== Tools::getValue('token') || false === Module::isInstalled('securitypro')) {
                $response = $this->module->l('Forbidden call.', 'cron');
                $this->getResponse($response);
            }
        }

        $response = null;
        // Try to run cronjob
        try {
            $response = $this->runCronjob();
        } catch (Exception $e) {
            if (true === (bool)Configuration::get('PRO_DEBUG_CRON')) {
                $response = $e;
            } else {
                $response = $this->module->l('Something went wrong.', 'cron');
            }
        }

        $this->getResponse($response);
    }

    /**
     * Write to log and display response
     *
     * @param string $response
     *
     * @throws PrestaShopException
     */
    private function getResponse($response)
    {
        $name = Tools::getValue('name');

        $this->module->logCron($name, $response);

        $this->ajaxDie($response);
    }

    /**
     * Run cronjob
     *
     * @return string
     */
    private function runCronjob()
    {
        // Check if module is activated
        if (true === (bool) $this->module->active) {
            $this->module->cron = true;

            // Run the cronjob
            $name = Tools::getValue('name');
            switch ($name) {
                case 'DeleteOldCarts':
                    $this->module->deleteOldCarts();
                    break;
                case 'BackupDatabase':
                    $this->module->backupDatabase();
                    break;
                case 'BackupFiles':
                    $this->module->backupFiles();
                    break;
                case 'MalwareScanner':
                    $this->module->checkMalware();
                    break;
                case 'Monitoring':
                    $this->module->checkDiff();
                    $this->module->checkMonitoringChanges();
                    break;
                default:
                    return $this->module->l('Cronjob does not exist.', 'cron');
            }
        } else {
            return $this->module->l('SecurityPro is not active.', 'cron');
        }

        return $this->module->l('Success!', 'cron');
    }
}
