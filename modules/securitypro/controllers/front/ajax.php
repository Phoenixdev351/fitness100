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

class SecurityProAjaxModuleFrontController extends ModuleFrontController
{
    const DEMO_MODE = false;

    /** @var bool */
    public $ajax;
    /** @var bool */
    public $ssl = true;
    /** @var bool */
    public $errors = false;

    /**
     * Display ajax
     */
    public function displayAjax()
    {
        \header('Access-Control-Allow-Origin: *');

        $this->ajax = true;

        // Check token
        if (false === Tools::isPHPCLI()) {
            if ($this->module->encrypt('securitypro/ajax') !== Tools::getValue('token') || false === Module::isInstalled('securitypro')) {
                exit($this->module->l('Forbidden call.', 'ajax'));
            }
        }

        $msg = $this->runAjax();
        $this->ajaxDie(\json_encode($msg));
    }

    /**
     * Run cronjob
     *
     * @return array
     */
    private function runAjax()
    {
        if (true === self::DEMO_MODE) {
            return [
                'result' => $this->module->l('Configuration has been disabled in demo version.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }

        // Check if module is activated
        if (true === (bool) $this->module->active) {
            $name = Tools::getValue('name');
            switch ($name) {
                case 'clearCache':
                    return $this->clearCache();

                case 'downloadLog':
                    return $this->checkLog();

                case 'clearLog':
                    return $this->clearLog();

                case 'removeFiles':
                    return $this->removeFiles();

                case 'removeFilesAnalyze':
                    return $this->removeFilesAnalyze();

                case 'createIndexAnalyze':
                    return $this->createIndexAnalyze();

                case 'createIndex':
                    return $this->createIndex();

                case 'permissionsAnalyze':
                    return $this->permissionsAnalyze();

                case 'fixPermissions':
                    return $this->fixPermissions();

                case 'autoConfiguration':
                    return $this->autoConfiguration();

                case 'transDownload':
                    return $this->transDownload();

                case 'errorLogsDownload':
                    return $this->errorLogsDownload();

                case 'clearLogPageNotFound':
                    return $this->clearLogPageNotFound();

                case 'downloadLogPageNotFound':
                    return $this->downloadLogPageNotFound();

                case 'downloadLogFirewall':
                    return $this->downloadLogFirewall();

                case 'downloadLogBruteForce':
                    return $this->downloadLogBruteForce();

                case 'downloadLogMalwareScan':
                    return $this->downloadLogMalwareScan();

                case 'downloadLogFileChanges':
                    return $this->downloadLogFileChanges();

                case 'downloadLogLoginAttempts':
                    return $this->downloadLogLoginAttempts();

                case 'downloadLogCronjob':
                    return $this->downloadLogCronjob();

                case 'clearLogFirewall':
                    return $this->clearLogFirewall();

                case 'clearLogBruteForce':
                    return $this->clearLogBruteForce();

                case 'clearLogMalwareScan':
                    return $this->clearLogMalwareScan();

                case 'clearLogFileChanges':
                    return $this->clearLogFileChanges();

                case 'clearLogLoginAttempts':
                    return $this->clearLogLoginAttempts();

                case 'clearLogCronjob':
                    return $this->clearLogCronjob();

                default:
                    return [
                        'result' => $this->module->l('The function does not exists.', 'ajax'),
                        'refresh' => 0,
                        'reload' => 0,
                    ];
            }
        } else {
            return [
                'result' => $this->module->l('SecurityPro is not active.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }
    }

    /**
     * @return array
     */
    private function clearCache()
    {
        $this->module->clearCacheSecuritypro();

        return [
            'result' => $this->module->l('Cache cleared.', 'ajax'),
            'refresh' => 1,
            'reload' => 0,
        ];
    }

    /**
     * @return array
     */
    private function checkLog()
    {
        return [
            'result' => $this->module->l('Downloading log.', 'ajax'),
            'refresh' => 0,
            'reload' => 1,
        ];
    }

    /**
     * @return array
     */
    private function clearLog()
    {
        return [
            'result' => $this->module->l('Clearing log.', 'ajax'),
            'refresh' => 0,
            'reload' => 1,
        ];
    }

    /**
     * @return array
     */
    private function removeFiles()
    {
        if (true === $this->module->removeFiles()) {
            return [
                'result' => $this->module->l('Files successfully removed!', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }

        return [
            'result' => $this->module->l('No vulnerabilities was detected.', 'ajax'),
            'refresh' => 0,
            'reload' => 0,
        ];
    }

    /**
     * @return array
     */
    private function removeFilesAnalyze()
    {
        if (true === $this->module->checkRemoveFilesAnalyze()) {
            return [
                'result' => $this->module->l('Downloading report.', 'ajax'),
                'refresh' => 0,
                'reload' => 1,
            ];
        }

        return [
            'result' => $this->module->l('No insecure files were detected.', 'ajax'),
            'refresh' => 0,
            'reload' => 0,
        ];
    }

    /**
     * @return array
     */
    private function createIndexAnalyze()
    {
        if (true === $this->module->createIndex(true)) {
            return [
                'result' => $this->module->l('Downloading report.', 'ajax'),
                'refresh' => 0,
                'reload' => 1,
            ];
        }

        return [
            'result' => $this->module->l('The report is empty.', 'ajax'),
            'refresh' => 0,
            'reload' => 0,
        ];
    }

    /**
     * @return array
     */
    private function createIndex()
    {
        if (false === $this->module->createIndex(true)) {
            return [
                'result' => $this->module->l('No insecure file permissions were detected.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }

        $this->module->createIndex(false);

        return [
            'result' => $this->module->l('Insecure file permissions were solved.', 'ajax'),
            'refresh' => 0,
            'reload' => 0,
        ];
    }

    /**
     * @return array
     */
    private function permissionsAnalyze()
    {
        if (true === $this->module->isWindowsOs()) {
            return [
                'result' => $this->module->l('Windows is not using file permissions. No insecure file permissions were detected.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }

        if (true === $this->module->checkFolderPermissions()) {
            return [
                'result' => $this->module->l('Insecure file permissions have been solved.', 'ajax'),
                'refresh' => 0,
                'reload' => 1,
            ];
        }

        return [
            'result' => $this->module->l('No insecure file permissions have been detected.', 'ajax'),
            'refresh' => 0,
            'reload' => 0,
        ];
    }

    /**
     * @return array
     */
    private function fixPermissions()
    {
        if (true === $this->module->isWindowsOs()) {
            return [
                'result' => $this->module->l('Windows is not using file permissions. No insecure file permissions were detected.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }

        if (true === $this->module->chmodFileFolderFix()) {
            return [
                'result' => $this->module->l('File permissions have been fixed.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }

        return [
            'result' => $this->module->l('No vulnerabilities was detected.', 'ajax'),
            'refresh' => 0,
            'reload' => 0,
        ];
    }

    /**
     * @return array
     */
    private function autoConfiguration()
    {
        $this->module->autoConfiguration();

        return [
            'result' => $this->module->l('The module has been configured!', 'ajax'),
            'refresh' => 1,
            'reload' => 0,
        ];
    }

    /**
     * @return array
     */
    private function transDownload()
    {
        return [
            'result' => $this->module->l('Downloading translations.', 'ajax'),
            'refresh' => 0,
            'reload' => 1,
        ];
    }

    /**
     * @return array
     */
    private function errorLogsDownload()
    {
        return [
            'result' => $this->module->l('Downloading logs.', 'ajax'),
            'refresh' => 0,
            'reload' => 1,
        ];
    }

    /**
     * @return array
     */
    private function clearLogPageNotFound()
    {
        if (true === $this->module->checkLog('PageNotFound')) {
            return [
                'result' => $this->module->l('The log is empty.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }
        $this->module->clearLog('PageNotFound');

        return [
            'result' => $this->module->l('The log has been cleared.', 'ajax'),
            'refresh' => 1,
            'reload' => 0,
        ];
    }

    /**
     * @return array
     */
    private function downloadLogPageNotFound()
    {
        if (true === $this->module->checkLog('PageNotFound')) {
            return [
                'result' => $this->module->l('The log is empty.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }

        return [
            'result' => $this->module->l('Downloading log.', 'ajax'),
            'refresh' => 0,
            'reload' => 1,
        ];
    }

    /**
     * @return array
     */
    private function downloadLogFirewall()
    {
        if (true === $this->module->checkLog('Firewall')) {
            return [
                'result' => $this->module->l('The log is empty.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }

        return [
            'result' => $this->module->l('Downloading log.', 'ajax'),
            'refresh' => 0,
            'reload' => 1,
        ];
    }

    /**
     * @return array
     */
    private function downloadLogBruteForce()
    {
        if (true === $this->module->checkLog('BruteForce')) {
            return [
                'result' => $this->module->l('The log is empty.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }

        return [
            'result' => $this->module->l('Downloading log.', 'ajax'),
            'refresh' => 0,
            'reload' => 1,
        ];
    }

    /**
     * @return array
     */
    private function downloadLogMalwareScan()
    {
        if (true === $this->module->checkLog('MalwareScan')) {
            return [
                'result' => $this->module->l('The log is empty.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }

        return [
            'result' => $this->module->l('Downloading log.', 'ajax'),
            'refresh' => 0,
            'reload' => 1,
        ];
    }

    /**
     * @return array
     */
    private function downloadLogFileChanges()
    {
        if (true === $this->module->checkLog('FileChanges')) {
            return [
                'result' => $this->module->l('The log is empty.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }

        return [
            'result' => $this->module->l('Downloading log.', 'ajax'),
            'refresh' => 0,
            'reload' => 1,
        ];
    }

    /**
     * @return array
     */
    private function downloadLogLoginAttempts()
    {
        if (true === $this->module->checkLog('LoginAttempts')) {
            return [
                'result' => $this->module->l('The log is empty.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }

        return [
            'result' => $this->module->l('Downloading log.', 'ajax'),
            'refresh' => 0,
            'reload' => 1,
        ];
    }

    /**
     * @return array
     */
    private function downloadLogCronjob()
    {
        if (true === $this->module->checkLog('Cronjob')) {
            return [
                'result' => $this->module->l('The log is empty.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }

        return [
            'result' => $this->module->l('Downloading log.', 'ajax'),
            'refresh' => 0,
            'reload' => 1,
        ];
    }

    /**
     * @return array
     */
    private function clearLogFirewall()
    {
        if (true === $this->module->checkLog('Firewall')) {
            return [
                'result' => $this->module->l('The log is empty.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }
        $this->module->clearLog('Firewall');

        return [
            'result' => $this->module->l('The log has been cleared.', 'ajax'),
            'refresh' => 1,
            'reload' => 0,
        ];
    }

    /**
     * @return array
     */
    private function clearLogBruteForce()
    {
        if (true === $this->module->checkLog('BruteForce')) {
            return [
                'result' => $this->module->l('The log is empty.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }
        $this->module->clearLog('BruteForce');

        return [
            'result' => $this->module->l('The log has been cleared.', 'ajax'),
            'refresh' => 1,
            'reload' => 0,
        ];
    }

    /**
     * @return array
     */
    private function clearLogMalwareScan()
    {
        if (true === $this->module->checkLog('MalwareScan')) {
            return [
                'result' => $this->module->l('The log is empty.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }
        $this->module->clearLog('MalwareScan');

        return [
            'result' => $this->module->l('The log has been cleared.', 'ajax'),
            'refresh' => 1,
            'reload' => 0,
        ];
    }

    /**
     * @return array
     */
    private function clearLogFileChanges()
    {
        if (true === $this->module->checkLog('FileChanges')) {
            return [
                'result' => $this->module->l('The log is empty.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }
        $this->module->clearLog('FileChanges');

        return [
            'result' => $this->module->l('The log has been cleared.', 'ajax'),
            'refresh' => 1,
            'reload' => 0,
        ];
    }

    /**
     * @return array
     */
    private function clearLogLoginAttempts()
    {
        if (true === $this->module->checkLog('LoginAttempts')) {
            return [
                'result' => $this->module->l('The log is empty.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }
        $this->module->clearLog('LoginAttempts');

        return [
            'result' => $this->module->l('The log has been cleared.', 'ajax'),
            'refresh' => 1,
            'reload' => 0,
        ];
    }

    /**
     * @return array
     */
    private function clearLogCronjob()
    {
        if (true === $this->module->checkLog('Cronjob')) {
            return [
                'result' => $this->module->l('The log is empty.', 'ajax'),
                'refresh' => 0,
                'reload' => 0,
            ];
        }
        $this->module->clearLog('Cronjob');

        return [
            'result' => $this->module->l('The log has been cleared.', 'ajax'),
            'refresh' => 1,
            'reload' => 0,
        ];
    }
}
