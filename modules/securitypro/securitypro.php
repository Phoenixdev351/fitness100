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

$autoloadPath = _PS_MODULE_DIR_ . 'securitypro/vendor/autoload.php';
if (\file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

/**
 * Class SecurityPro
 */
class SecurityPro extends Module
{
    const DEMO_MODE = false;

    const LOG_BRUTE_FORCE = 'sp_bruteforce.log';
    const LOG_PAGE_NOT_FOUND = 'sp_pagenotfound.log';
    const LOG_FIREWALL = 'sp_firewall.log';
    const LOG_MALWARE_SCAN = 'sp_malwarescan.log';
    const LOG_FILE_CHANGES = 'sp_filechanges.log';
    const LOG_LOGIN_ATTEMPTS = 'sp_loginattempts.log';
    const LOG_CRONJOB = 'sp_cron.log';

    const DIR_BACKUP_DATABASE = '/backup/database/';
    const DIR_BACKUP_FILES = '/backup/files/';
    const DIR_ANTI_FLOOD = '/antiflood';
    const DIR_DATA = '/bin';

    const REPORT_PORT_SCANNER = 'sp_report_port_scanner.txt';
    const REPORT_CREATE_INDEX = 'sp_report_create_index.txt';
    const REPORT_PERMISSIONS = 'sp_report_permissions.txt';
    const REPORT_REMOVE_FILES = 'sp_report_remove_files.txt';

    const TEST_DOMAIN = 'google.com';
    const TEST_SERVER_IP = '172.217.21.142';
    const TEST_URL = 'https://google.com';

    const COLOR_GREEN = '#78d07d';
    const COLOR_RED = '#e08f95';
    const COLOR_BLUE = '#4ac7e0';

    const USER_AGENT = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Safari/537.36';

    /** @var int */
    public $cron = false;

    /**
     * Construct module.
     */
    public function __construct()
    {
        $this->name = 'securitypro';
        $this->tab = 'administration';
        $this->version = '8.8.10';
        $this->author = 'Mathias Reker';
        $this->module_key = '71a0dda36237f958642fb61a15ccc695';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6.1',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Security Pro');
        $this->description = $this->l('This module increases the overall security of your PrestaShop website.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    /**
     * Install module, database table and set default values.
     *
     * @throws PrestaShopException
     *
     * @return bool
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $sqlInstall = [];

        $sqlInstall[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'securitypro` (
            `id_securitypro` int(11) NOT NULL,
            `email` varchar(64) NOT NULL,
            `access_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `ip` varchar(64) NOT NULL,
            `banned` int(1) NOT NULL
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
            ALTER TABLE `' . _DB_PREFIX_ . 'securitypro`
              ADD PRIMARY KEY (`id_securitypro`);
            ALTER TABLE `' . _DB_PREFIX_ . 'securitypro`
              MODIFY `id_securitypro` int(11) NOT NULL AUTO_INCREMENT;';

        $sqlInstall[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'securitypro_tfa` (
            `enabled` int(1) NOT NULL,
            `secret` varchar(32) NOT NULL
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
            ';

        $sqlInstall[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'securitypro_af` (
            `id_order` INT(10) UNSIGNED NOT NULL,
            `ip` varchar(64) NOT NULL,
            `ua` varchar(512) NOT NULL,
            `proxy` INT(1) UNSIGNED NULL DEFAULT NULL
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
            ';

        foreach ($sqlInstall as $query) {
            if (false === Db::getInstance()->execute($query)) {
                return false;
            }
        }

        if (Module::isInstalled('securitylite')) {
            $oldModule = Module::getInstanceByName('securitylite');
            if (true === (bool) $oldModule) {
                $oldModule->uninstall();
            }
        }

        $this->installTab();

        Configuration::updateGlobalValue('PRO_BAN_TIME', 30);
        Configuration::updateGlobalValue('PRO_MAX_RETRY', 5);
        Configuration::updateGlobalValue('PRO_FIND_TIME', 10);
        Configuration::updateGlobalValue('PRO_GENERAL_EMAIL', Configuration::get('PS_SHOP_EMAIL'));
        Configuration::updateGlobalValue('PRO_ADMIN_DIRECTORY_NAME', \basename(_PS_ADMIN_DIR_));
        Configuration::updateGlobalValue('PRO_ANTI_MAX_REQUESTS', 100);
        Configuration::updateGlobalValue('PRO_ANTI_REQ_TIMEOUT', 5);
        Configuration::updateGlobalValue('PRO_ANTI_BAN_TIME', 600);
        Configuration::updateGlobalValue('PRO_DELETE_OLD_CARTS_DAYS', 14);
        Configuration::updateGlobalValue('PRO_BACKUP_DB_SAVED', 7);
        Configuration::updateGlobalValue('PRO_BACKUP_FILE_SAVED', 1);
        Configuration::updateGlobalValue('PRO_ANTI_FRAUD_UNIT', 'km');
        Configuration::updateGlobalValue('PRO_ANTI_FRAUD_HOOK', 'left');

        $hookCommon = [
            'actionDispatcher',
            'actionValidateOrder',
            'displayAdminLogin',
            'displayBackOfficeTop',
            'displayCustomerAccountForm',
            'displayFooter',
            'displayHeader',
        ];

        if (Tools::version_compare(_PS_VERSION_, '1.7.1.0', '>=')) {
            $hookAccount = [
                'actionSubmitAccountBefore',
            ];
        } else {
            $hookAccount = [
                'actionBeforeSubmitAccount',
            ];
        }

        if (Tools::version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            $hookLogin = [
                'actionAdminLoginControllerBefore',
            ];
        } else {
            $hookLogin = [];
        }

        $hooks = \array_merge($hookCommon, $hookAccount, $hookLogin);

        if (false === parent::install() || false === $this->registerHook($hooks)) {
            return false;
        }

        return true;
    }

    /**
     * Install tab.
     */
    public function installTab()
    {
        $tab = new Tab();

        $tab->module = $this->name;

        $languages = Language::getLanguages(false);
        $tabName = [];
        foreach ($languages as $language) {
            $tabName[$language['id_lang']] = 'Security Pro';
        }

        $tab->name = $tabName;
        $tab->class_name = 'AdminSecurityPro';

        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $tab->icon = 'security';
            $tab->id_parent = (int) Tab::getIdFromClassName('IMPROVE');
            $tab->save();
        } else {
            $tab->id_parent = 0;
            $tab->add();
        }
    }

    /**
     * Uninstall the module, reverse any changes and delete database table.
     *
     * @throws PrestaShopException
     *
     * @return bool
     */
    public function uninstall()
    {
        // Force default group
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $this->uninstallTab();

        $sqlUninstall = [];

        $sqlUninstall[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'securitypro`';
        $sqlUninstall[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'securitypro_tfa`';
        $sqlUninstall[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'securitypro_af`';

        foreach ($sqlUninstall as $query) {
            if (false === Db::getInstance()->execute($query)) {
                return false;
            }
        }

        $this->removeHtaccessContent();

        foreach (\array_keys($this->getAllLogs()) as $key) {
            Tools::deleteFile($this->getLogFile($key));
        }

        foreach ($this->getConfigFormValues() as $key) {
            Configuration::deleteByName($key);
        }

        $this->clearCacheSecuritypro(false);

        return parent::uninstall();
    }

    /**
     * Uninstall tab.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @return bool
     */
    public function uninstallTab()
    {
        $tabId = Tab::getIdFromClassName('AdminSecurityPro');

        if (false === $tabId) {
            return true;
        }

        $tab = new Tab((int) $tabId);

        return $tab->delete();
    }

    /**
     * Remove generated content from .htaccess file.
     */
    public function removeHtaccessContent()
    {
        Tools::deleteFile(_PS_ROOT_DIR_ . \DIRECTORY_SEPARATOR . '.htpasswd');
        $filePath = _PS_ADMIN_DIR_ . \DIRECTORY_SEPARATOR . '.htaccess';

        if (false === \file_exists($filePath)) {
            return;
        }

        $htaccessContent = Tools::file_get_contents($filePath);

        if (\preg_match('/# ~security_pro~(.*?)# ~security_pro_end~/s', $htaccessContent, $m)) {
            $contentToRemove = $m[0];
            $htaccessContent = \str_replace($contentToRemove, '', $htaccessContent);
        }
        \file_put_contents($filePath, $htaccessContent);

        if (0 === \filesize($filePath)) {
            Tools::deleteFile($filePath);
        }
    }

    /**
     * Clear cache of securitypro.
     *
     * @param bool $clearTable
     */
    public function clearCacheSecuritypro($clearTable = true)
    {
        // Clear cache of folders
        $cacheDir = _PS_CACHE_DIR_ . $this->name;

        if (\is_dir($cacheDir)) {
            Tools::deleteDirectory($cacheDir);
        }

        // Regenerate XML
        ModuleCore::generateTrustedXml();

        // Clear table
        if (true === $clearTable) {
            $query = 'TRUNCATE TABLE `' . _DB_PREFIX_ . 'securitypro`';
            Db::getInstance()->execute($query);
        }
    }

    /**
     * Hook stuff before submit account. PS 1.6.
     *
     * @param array $params
     *
     * @return bool
     */
    public function hookActionBeforeSubmitAccount($params)
    {
        if (true === (bool) Configuration::get('PRO_FAKE_ACCOUNTS')) {
            if (false === $this->validateCookie()) {
                return false;
            }
        }

        if (true === (bool) Configuration::get('PRO_DISALLOW_URL_CUSTOMER_NAME')) {
            if ($this->validateCustomerName('customer_firstname', 'customer_lastname')) {
                return false;
            }
        }

        if (false === $this->displayRegistrationFormErrors()) {
            return false;
        }

        return true;
    }

    /**
     * Encrypt data.
     *
     * @param string $data
     *
     * @return string
     */
    public function encrypt($data)
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return Tools::hashIV($data);
        }

        return Tools::encryptIV($data);
    }

    /**
     * Hook stuff before submit account. PS 1.7.
     *
     * @param array $params
     *
     * @return bool
     */
    public function hookActionSubmitAccountBefore($params)
    {
        if (true === (bool) Configuration::get('PRO_FAKE_ACCOUNTS')) {
            if (false === $this->validateCookie()) {
                return false;
            }
        }

        if (true === (bool) Configuration::get('PRO_DISALLOW_URL_CUSTOMER_NAME')) {
            if ($this->validateCustomerName('firstname', 'lastname')) {
                return false;
            }
        }

        if (false === $this->displayRegistrationFormErrors()) {
            return false;
        }

        return true;
    }

    /**
     * Display anti-fraud in PS 1.7.
     *
     * @param array $params
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @return bool
     */
    public function hookDisplayAdminOrderSideBottom($params)
    {
        if (true === (bool) Configuration::get('PRO_ANTI_FRAUD')) {
            $this->adminOrderInformation($params['id_order'], 6);

            return $this->display(__FILE__, 'antiFraud.tpl');
        }
    }

    /**
     * Display anti-fraud in PS 1.7.
     *
     * @param array $params
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @return bool
     */
    public function hookDisplayAdminOrderMainBottom($params)
    {
        if (true === (bool) Configuration::get('PRO_ANTI_FRAUD')) {
            $this->adminOrderInformation($params['id_order'], 4);

            return $this->display(__FILE__, 'antiFraud.tpl');
        }
    }

    /**
     * Display anti-fraud in PS 1.6.
     *
     * @param array $params
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @return bool
     */
    public function hookDisplayAdminOrderRight($params)
    {
        if (true === (bool) Configuration::get('PRO_ANTI_FRAUD')) {
            $this->adminOrderInformation($params['id_order'], 6);

            return $this->display(__FILE__, 'antiFraud16.tpl');
        }
    }

    /**
     * Display anti-fraud in PS 1.6.
     *
     * @param array $params
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @return bool
     */
    public function hookDisplayAdminOrderLeft($params)
    {
        if (true === (bool) Configuration::get('PRO_ANTI_FRAUD')) {
            $this->adminOrderInformation($params['id_order'], 4);

            return $this->display(__FILE__, 'antiFraud16.tpl');
        }
    }

    /**
     * Run scripts depending on configuration. Display warnings and confirmations.
     *
     * @throws \Google\Exception
     * @throws \RobThree\Auth\TwoFactorAuthException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @return array|false|void
     */
    public function getContent()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $result = [];

        $result[] = '<div id="overlay"> <img style="width: 60px; padding-bottom: 10px" src="' . $this->_path . 'logo.png" alt="" loading="eager"><p><strong>' . $this->l('Security Pro') . '</strong></p></div>';

        $clientIp = $this->getClientIP();

        if (true === (bool) $this->checkMaintenanceMode()) {
            $result[] = $this->displayModernError($this->l('Your shop is in maintenance mode. You must add your IP address to the whitelist, otherwise, some features of the module will do not work.') . ' ' . $this->generateLink($this->adminLink('AdminMaintenance'), $this->l('Add your IP to the whitelist.')));
        }

        $result[] = '<style>#overlay{background:#fff;color:#666;position:fixed;height:100%;width:100%;z-index:5000;top:0;left:0;float:left;text-align:center;padding-top:20%}.textarea-autosize{min-height:80px}.btn,.list-group-item,a,button:focus,input[type=checkbox]:focus{outline:0!important}.securitypro-divider{width:10px;height:auto;display:inline-block}.form-wrapper{max-width:100%!important}</style>';

        if (true === (bool) $this->context->language->is_rtl) {
            $result[] = '<style>.securitypro-position{float:left;white-space:nowrap;}</style>'; // RTL
        } else {
            $result[] = '<style>.securitypro-position{float:right;white-space:nowrap;}</style>'; // LTR
        }

        $result[] = '<script>window.addEventListener("load",function(){$("#overlay").fadeOut()});</script>';

        if (true === (bool) Configuration::get('PS_DISABLE_NON_NATIVE_MODULE')) {
            $result[] = $this->displayModernError($this->l('You must enable non PrestaShop modules at') . ' ' . $this->generateLink($this->adminLink('AdminPerformance'), $this->l('\'Advanced Parameters\' > \'Performance\'')) . '.');
        }

        if (true === (bool) Tools::isSubmit('tab_reset')) {
            $result[] = '<script>localStorage.setItem(\'lastTab\',\'fieldset_0_securitypro\');</script>';
        }

        if (false === self::DEMO_MODE) {
            $localBackups = [
                [
                    'BackupDatabaseDownload',
                    self::DIR_BACKUP_DATABASE,
                ],
                [
                    'BackupFilesDownload',
                    self::DIR_BACKUP_FILES,
                ],
            ];

            foreach ($localBackups as $localBackup) {
                if (true === (bool) Tools::isSubmit($localBackup[0])) {
                    $backupDir = _PS_MODULE_DIR_ . $this->name . $localBackup[1];
                    $backupFile = Tools::getValue('file');
                    $this->downloadFile($backupDir . $backupFile);
                }
            }

            $deleteFile = false;

            $dropboxDeleteBackups = [
                [
                    'DropboxBackupFilesDelete',
                    self::DIR_BACKUP_FILES,
                ],
                [
                    'DropboxBackupDatabaseDelete',
                    self::DIR_BACKUP_DATABASE,
                ],
            ];

            // Btn: Delete Dropbox files backup
            foreach ($dropboxDeleteBackups as $dropboxDeleteBackup) {
                if (true === (bool) Tools::isSubmit($dropboxDeleteBackup[0])) {
                    $client = $this->dropboxGetClient();
                    $backupDir = $dropboxDeleteBackup[1];
                    $backupFile = Tools::getValue('file');
                    $client->files->delete_v2($backupDir . $backupFile);
                    $deleteFile = true;
                }
            }

            $dropboxDownloadBackups = [
                [
                    'DropboxBackupFilesDownload',
                    self::DIR_BACKUP_FILES,
                ],
                [
                    'DropboxBackupDatabaseDownload',
                    self::DIR_BACKUP_DATABASE,
                ],
            ];

            // Btn: Download Dropbox files backup
            foreach ($dropboxDownloadBackups as $dropboxDownloadBackup) {
                if (true === (bool) Tools::isSubmit($dropboxDownloadBackup[0])) {
                    $client = $this->dropboxGetClient();
                    $backupFile = Tools::getValue('file');
                    $tmpPath = _PS_MODULE_DIR_ . 'securitypro/backup/' . $backupFile;
                    $client->files->download($dropboxDownloadBackup[1] . $backupFile, $tmpPath);
                    $this->downloadFile($tmpPath, true);
                }
            }

            $deleteGoogleDriveBackups = [
                [
                    'GoogleDriveBackupFilesDelete',
                ],
                [
                    'GoogleDriveBackupDatabaseDelete',
                ],
            ];

            // Btn: Delete Google Drive files backup
            foreach ($deleteGoogleDriveBackups as $deleteGoogleDriveBackup) {
                if (true === (bool) Tools::isSubmit($deleteGoogleDriveBackup[0])) {
                    $fileId = Tools::getValue('FileId');
                    $this->googleDriveDeleteFile($fileId);
                    $this->googleDriveEmptyTrash();
                }
            }

            $downloadGoogleDriveBackups = [
                [
                    'GoogleDriveBackupFilesDownload',
                ],
                [
                    'GoogleDriveBackupDatabaseDownload',
                ],
            ];

            // Btn: Download Google Drive files backup
            foreach ($downloadGoogleDriveBackups as $downloadGoogleDriveBackup) {
                if (true === (bool) Tools::isSubmit($downloadGoogleDriveBackup[0])) {
                    $fileId = Tools::getValue('FileId');
                    $fileName = Tools::getValue('FileName');
                    $tmpPath = _PS_MODULE_DIR_ . 'securitypro/backup/';
                    $this->googleDriveDownloadFile($fileId, $fileName, $tmpPath);
                    $this->downloadFile($tmpPath . $fileName, true);
                }
            }

            $deleteLocalBackups = [
                [
                    'BackupDatabaseDelete',
                    self::DIR_BACKUP_DATABASE,
                ],
                [
                    'BackupFilesDelete',
                    self::DIR_BACKUP_FILES,
                ],
            ];

            foreach ($deleteLocalBackups as $deleteLocalBackup) {
                if (true === (bool) Tools::isSubmit($deleteLocalBackup[0])) {
                    $backupDir = _PS_MODULE_DIR_ . $this->name . $deleteLocalBackup[1];
                    $backupFile = Tools::getValue('file');
                    Tools::deleteFile($backupDir . $backupFile);
                    $deleteFile = true;
                }
            }

            if (true === $deleteFile) {
                if (\file_exists($backupFile)) {
                    $result[] = $this->displayModernConfirmation($this->l('File') . ' <strong>' . $backupDir . $backupFile . '</strong> ' . $this->l('has been deleted.'));
                }
            }
        }

        // Logs
        foreach ($this->getAllLogs() as $key => $log) {
            if (true === (bool) Tools::isSubmit('downloadLog' . $key)) {
                if (true === self::DEMO_MODE) {
                    return false;
                }

                $this->downloadFile($this->getLogFile($log));
            }
        }

        // Validate input: Permissions
        if (true === (bool) Tools::isSubmit('PermissionsAnalyze')) {
            if (true === self::DEMO_MODE) {
                return false;
            }

            if (true === $this->isWindowsOs()) {
                return;
            }

            $permissionsReportPath = _PS_MODULE_DIR_ . self::REPORT_PERMISSIONS;

            $this->chmodFileFolderAnalyze(_PS_ROOT_DIR_);

            // Delete file if empty
            \clearstatcache();
            if (0 === \filesize($permissionsReportPath)) {
                Tools::deleteFile($permissionsReportPath);
            }

            // Download
            if (\file_exists($permissionsReportPath)) {
                $this->downloadFile($permissionsReportPath, true);
            }
        }

        // Validate and create Index
        if (true === (bool) Tools::isSubmit('CreateIndexAnalyze')) {
            if (true === self::DEMO_MODE) {
                return false;
            }

            $createIndexPath = _PS_MODULE_DIR_ . self::REPORT_CREATE_INDEX;

            if (\file_exists($createIndexPath)) {
                $this->downloadFile($createIndexPath, true);
            }
        }

        if (true === (bool) Tools::isSubmit('RemoveFilesAnalyze')) {
            $this->removeFilesAnalyze();
        }

        // Download translations
        if (true === (bool) Tools::isSubmit('TransDownload')) {
            if (true === self::DEMO_MODE) {
                return false;
            }

            $this->exportTranslation();
        }

        // Download translations
        if (true === (bool) Tools::isSubmit('ErrorLogsDownload')) {
            if (true === self::DEMO_MODE) {
                return false;
            }

            $this->exportErrorLogs();
        }

        // Submit save
        if (true === (bool) Tools::isSubmit('SubmitSecurityProModule')) {
            if (true === self::DEMO_MODE) {
                $result[] = $this->displayModernConfirmation($this->l('Configuration has been disabled in demo version.'));
            } else {
                $this->postProcess();

                $result[] = $this->displayModernConfirmation($this->l('Settings updated!'));

                // Validate force 2FA
                if (true === (bool) Configuration::get('PRO_TWO_FACTOR_AUTH') && true === (bool) Configuration::get('PRO_TWO_FACTOR_AUTH_FORCE')) {
                    $this->deleteTFALoginToken();
                }

                // Validate db backups to save
                $this->validateIsMinInt('PRO_BACKUP_DB_SAVED', 0);

                // Validate file backups to save
                $this->validateIsMinInt('PRO_BACKUP_FILE_SAVED', 0);

                // Validate HSTS and CT
                if (false === (bool) Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) {
                    Configuration::updateGlobalValue('PRO_STRICT_TRANSPORT_SECURITY', false);
                    Configuration::updateGlobalValue('PRO_EXPECT_CT', false);
                }

                // Validate TLS certificate notification option
                if (false === (bool) Configuration::get('PS_SSL_ENABLED')) {
                    Configuration::updateGlobalValue('PRO_TLS_EXPIRE', false);
                }

                // Validate input: Disable contact form features, if the contact form is disabled.
                if (true === (bool) Configuration::get('PRO_DISABLE_CONTACT_FORM')) {
                    Configuration::updateGlobalValue('PRO_RECAPTCHA_V3_CONTACT_ACTIVATE', false);
                    Configuration::updateGlobalValue('PRO_GOOGLE_SAFE_BROWSING_V4_ACTIVATE', false);
                    Configuration::updateGlobalValue('PRO_MESSAGE_CHECKER_ACTIVATE', false);
                    Configuration::updateGlobalValue('PRO_DISPOSABLE_EMAIL_PROVIDERS_ACTIVATE', false);
                    Configuration::updateGlobalValue('PRO_EMAIL_CHECKER_ACTIVATE', false);
                    Configuration::updateGlobalValue('PRO_BLOCK_EMAILS', false);
                }

                // Validate input: Block custom words on contact form
                if (true === (bool) Configuration::get('PRO_MESSAGE_CHECKER_ACTIVATE')) {
                    if (false === (bool) Configuration::get('PRO_MESSAGE_CHECKER_CUSTOM_LIST')) {
                        Configuration::updateGlobalValue('PRO_MESSAGE_CHECKER_ACTIVATE', false);
                    }
                }

                // Validate input: Block custom TLD's on contact form
                if (true === (bool) Configuration::get('PRO_EMAIL_CHECKER_ACTIVATE')) {
                    if (false === (bool) Configuration::get('PRO_BLOCK_EMAILS_CUSTOM_LIST')) {
                        Configuration::updateGlobalValue('PRO_EMAIL_CHECKER_ACTIVATE', false);
                    }
                }

                // Validate input: Block custom TLD's on contact form
                if (true === (bool) Configuration::get('PRO_BLOCK_EMAILS')) {
                    if (false === (bool) Configuration::get('PRO_EMAIL_CHECKER_CUSTOM_LIST')) {
                        Configuration::updateGlobalValue('PRO_BLOCK_EMAILS', false);
                    }
                }

                // Validate input: Block custom TLD's on registration form
                if (true === (bool) Configuration::get('PRO_EMAIL_CHECKER_REGISTRATION_ACTIVATE')) {
                    if (false === (bool) Configuration::get('PRO_EMAIL_CHECKER_CUSTOM_LIST_REGISTRATION')) {
                        Configuration::updateGlobalValue('PRO_EMAIL_CHECKER_REGISTRATION_ACTIVATE', false);
                    }
                }

                // Validate input: Stealth Login
                if (true === (bool) Configuration::get('PRO_STEALTH_LOGIN')) {
                    if (false === (bool) Configuration::get('PRO_STEALTH_LOGIN_WHITELIST')) {
                        Configuration::updateGlobalValue('PRO_STEALTH_LOGIN', false);
                        $result[] = $this->displayModernWarning($this->l('You must enter minimum one IP address to the whitelist, before you can activate "Admin stealth login".'));
                    }
                }

                // Validate input: Google Safe Browsing
                if (true === (bool) Configuration::get('PRO_GOOGLE_SAFE_BROWSING_V4_ACTIVATE')) {
                    if (false === (bool) Configuration::get('PRO_GOOGLE_SAFE_BROWSING_V4_API')) {
                        Configuration::updateGlobalValue('PRO_GOOGLE_SAFE_BROWSING_V4_ACTIVATE', false);
                        $result[] = $this->displayModernWarning($this->l('You must enter an API key for "Google safe browsing", before you can enable this feature.'));
                    }
                }

                // Validate DDoS protection
                $this->validateIsMinInt('PRO_ANTI_MAX_REQUESTS', 10);
                $this->validateIsMinInt('PRO_ANTI_REQ_TIMEOUT', 1);
                $this->validateIsMinInt('PRO_ANTI_BAN_TIME', 1);

                // Validate input: Brute force protection
                $this->validateIsMinInt('PRO_MAX_RETRY', 1);
                $this->validateIsMinInt('PRO_FIND_TIME', 1);
                $this->validateIsMinInt('PRO_BAN_TIME', 1);

                // Validate email
                if (false === (bool) Configuration::get('PRO_GENERAL_EMAIL')) {
                    Configuration::updateGlobalValue('PRO_GENERAL_EMAIL', Configuration::get('PS_SHOP_EMAIL'));
                }

                // Validate input: Second login
                if (true === (bool) Configuration::get('PRO_HTPASSWD')) {
                    if (false === (bool) $this->validateHtaccessName(Configuration::get('PRO_HTPASSWD_USER')) &&
                        false === (bool) $this->validateHtaccessName(Configuration::get('PRO_HTPASSWD_PASS'))) {
                        $result[] = $this->displayModernWarning($this->l('Avoid use of') . ': ');
                    }
                }

                // Validate honeypot
                if (true === (bool) Configuration::get('PRO_FIREWALL_CHECK_BOT')) {
                    if (false === $this->validateHoneyPotApi(Configuration::get('PRO_HONEYPOT_API'))) {
                        $result[] = $this->displayModernWarning($this->l('You must specify a valid Honeypot API key, before you can activate bot check.'));
                        Configuration::updateGlobalValue('PRO_FIREWALL_CHECK_BOT', false);
                    }
                }

                // Validate HTTP Headers -> Validate in case of API in use.
                if (true === (bool) Configuration::get('PRO_ACCESS_CONTROL_ALLOW_METHODS')) {
                    if (true === (bool) Configuration::get('PS_WEBSERVICE')) {
                        $result[] = $this->displayModernWarning($this->l('You cannot enable this mode, because your are using PrestaShop webservice / API.'));
                        Configuration::updateGlobalValue('PRO_ACCESS_CONTROL_ALLOW_METHODS', false);
                    }
                }

                // Validate input: Admin directory
                if (false === \defined('_TB_VERSION_')) {
                    if (false === (bool) Validate::isFileName(Configuration::get('PRO_ADMIN_DIRECTORY_NAME'))) {
                        $result[] = $this->displayModernWarning($this->l('Name of directory is not correct'));
                    }
                }

                // Validate input: Dropbox files/database backup
                if (true === (bool) Configuration::get('PRO_BACKUP_FILE_DROPBOX') || true === (bool) Configuration::get('PRO_BACKUP_DB_DROPBOX')) {
                    if (false === (bool) Configuration::get('PRO_BACKUP_DB_TOKEN') || false === $this->dropboxValidateToken()) {
                        Configuration::updateGlobalValue('PRO_BACKUP_FILE_DROPBOX', false);
                        Configuration::updateGlobalValue('PRO_BACKUP_DB_DROPBOX', false);

                        $result[] = $this->displayModernWarning($this->l('Dropbox token is invalid.'));
                    }
                }

                // Validate input: Google Drive files/database backup
                if (true === (bool) Configuration::get('PRO_BACKUP_FILES_GOOGLE_DRIVE') || true === (bool) Configuration::get('PRO_BACKUP_DB_GOOGLE_DRIVE')) {
                    if (false === (bool) Configuration::get('PRO_GOOGLE_DRIVE_AUTH') || false === $this->googleDriveTestAccess()) {
                        Configuration::updateGlobalValue('PRO_BACKUP_FILES_GOOGLE_DRIVE', false);
                        Configuration::updateGlobalValue('PRO_BACKUP_DB_GOOGLE_DRIVE', false);

                        $result[] = $this->displayModernWarning($this->l('Google authentication is invalid.'));
                    }
                }

                // Validate delete old carts
                $this->validateIsMinInt('PRO_DELETE_OLD_CARTS_DAYS', 2);

                // Run scripts
                if (true === (bool) Configuration::get('PRO_HTPASSWD')) {
                    $this->secureBackOffice();
                } else {
                    $this->removeHtaccessContent();
                }

                // Validate IP addresses
                $fieldIps = [
                    'PRO_BAN_IP',
                    'PRO_FIREWALL_WHITELIST',
                    'PRO_STEALTH_LOGIN_WHITELIST',
                    'PRO_TWO_FACTOR_AUTH_WHITELIST',
                    'PRO_WHITELIST_IPS',
                    'PRO_WHITELIST_PROTECT_CONTENT',
                ];

                foreach ($fieldIps as $fieldIp) {
                    $this->validateIps($fieldIp);
                }

                $emails = [
                    'PRO_GENERAL_EMAIL',
                    'PRO_BLOCK_EMAILS_CUSTOM_LIST',
                ];

                foreach ($emails as $email) {
                    $this->validateEmail($email);
                }

                // Validate user agents and other lists
                $fieldStrings = [
                    'PRO_BLOCK_USER_AGENT',
                    'PRO_EMAIL_CHECKER_CUSTOM_LIST',
                    'PRO_EMAIL_CHECKER_CUSTOM_LIST_REGISTRATION',
                    'PRO_MESSAGE_CHECKER_CUSTOM_LIST',
                ];

                foreach ($fieldStrings as $fieldString) {
                    $this->validateCommaSeparatedString($fieldString);
                }

                $this->validateCommaSeparatedSha1('PRO_WHITELIST_MALWARE');

                if (true === (bool) Configuration::get('PRO_TWO_FACTOR_AUTH') && ('0' === $this->twoFactorAuthDb('enabled'))) {
                    $twoFactorAuth = new \RobThree\Auth\TwoFactorAuth(Configuration::get('PS_SHOP_NAME'), 6, 30, 'sha1');
                    if ($twoFactorAuth->verifyCode($this->twoFactorsecret(), \str_replace(' ', '', (int) Configuration::get('PRO_TWO_FACTOR_AUTH_CODE')))) {
                        $this->updateTwoFactorAuthDB('enabled', 1);
                        $result[] = $this->displayModernConfirmation($this->l('Two-Factor Authentication successfully activated!'));
                    } else {
                        $this->updateTwoFactorAuthDB('enabled', 0);
                        Configuration::updateGlobalValue('PRO_TWO_FACTOR_AUTH', false);
                        $result[] = $this->displayModernWarning($this->l('Two-Factor Authentication is not activated. The code did not match.'));
                    }
                }

                if (false === (bool) Configuration::get('PRO_TWO_FACTOR_AUTH')) {
                    $this->updateTwoFactorAuthDB('enabled', 0);
                }

                if (false === (bool) Configuration::get('PRO_ANTI_FLOOD')) {
                    $dirs = [
                        _PS_MODULE_DIR_ . $this->name . self::DIR_ANTI_FLOOD . '/lock',
                        _PS_MODULE_DIR_ . $this->name . self::DIR_ANTI_FLOOD,
                    ];

                    foreach ($dirs as $backupDir) {
                        if (\is_dir($backupDir)) {
                            Tools::deleteDirectory($backupDir);
                        }
                    }
                }

                // Set hooks
                $hookName = Configuration::get('PRO_ANTI_FRAUD_HOOK');
                if (Validate::isHookName($hookName)) {
                    $this->antiFraudHook($hookName);
                }

                // Empty password
                Configuration::updateGlobalValue('PRO_PASSWORD_GENERATOR', null);
            }
        }

        // Load JS
        $this->context->controller->addJS($this->_path . 'views/js/menu.js');
        $this->context->controller->addJS($this->_path . 'views/js/secure-random-password.js');

        if (\defined('_TB_VERSION_')) {
            $cmsName = 'Thirty bees';
            $cmsVersion = _TB_VERSION_;
        } else {
            $cmsName = 'PrestaShop';
            $cmsVersion = _PS_VERSION_;
        }
        $moduleVersion = Module::getInstanceByName('securitypro')->version;

        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0')) {
            $ps16 = 1;
        } else {
            $ps16 = 0;
        }

        Media::addJsDef([
            'securitypro' => [
                'currentIndex' => $this->currentAdminIndex(),
                'sslEnabled' => (int) Configuration::get('PS_SSL_ENABLED_EVERYWHERE'),
                'clientIp' => $clientIp,
                'moduleVersion' => $moduleVersion,
                'cmsName' => $cmsName,
                'cmsVersion' => $cmsVersion,
                'ps16' => $ps16,
                'text1' => $this->l('The page is reloading, please wait.'),
                'text2' => $this->l('Canceled.'),
                'text3' => $this->l('Password generated.'),
                'text4' => $this->l('Folder name generated.'),
                'text5' => $this->l('Username generated.'),
                'text6' => $this->l('Password generated.'),
                'text7' => $this->l('IP added.'),
                'text8' => $this->l('Copied to clipboard.'),
                'text9' => $this->l('Version'),
            ],
        ]);
        $this->context->controller->addJS($this->_path . 'views/js/backend.js');

        // Reset URL
        $parseUrl = \parse_url($this->currentAdminIndex());
        $parseUrlPath = $parseUrl['path'];
        $parseUrlQuery = $parseUrl['query'];
        $finalUrl = $parseUrlPath . '?' . $parseUrlQuery;
        $resetUrl = '<script>window.history.replaceState({}, document.title, "' . $finalUrl . '");</script>';

        if (false === $this->renameAdminDirectory()) {
            $result[] = $this->displayModernWarning($this->l('The server did not allow the module to rename the admin folder. You must do this manually.'));
        }

        // Return the output
        return \implode('', $result) . $this->renderForm() . $resetUrl;
    }

    /**
     * Check if OS is windows.
     *
     * @return bool
     */
    public function isWindowsOs()
    {
        return 0 === \mb_stripos(\PHP_OS, 'WIN');
    }

    /**
     * Check if access to Google Drive
     */
    public function googleDriveTestAccess()
    {
        try {
            $this->googleDriveGetClient()->getAccessToken();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Build form.
     *
     * @throws \Google\Exception
     * @throws \RobThree\Auth\TwoFactorAuthException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @return string
     */
    public function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = (bool) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', false);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'SubmitSecurityProModule';
        $helper->currentIndex = $this->currentAdminIndex();
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->configFormFieldsValue(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        $dashboard = [
            $this->fieldsFormDashboard(),
        ];

        $generalSettings = [
            $this->fieldsFormGeneralSettings(),
        ];

        $loginProtection = [
            $this->fieldsFormBruteForceProtection(),
            $this->fieldsFormTwoFactorAuth(),
            $this->fieldsFormSecondLogin(),
            $this->fieldsFormAdminStealthLogin(),
        ];

        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0')) {
            $passwordStrength = [];
        } else {
            $passwordStrength = [
                $this->fieldsFormPasswordStrength(),
            ];
        }

        $httpHeaders = [
            $this->fieldsFormHttpSecurityHeaders(),
        ];

        $firewall = [
            $this->fieldsFormFirewall(),
            $this->fieldsFormAntiSpam(),
        ];

        $malwareScanner = [
            $this->fieldsFormMalwareScan(),
        ];

        $cartProtection = [
            $this->fieldsFormAntiFakeCarts(),
        ];

        $monitoring = [
            $this->fieldsFormWebsiteMonitoringService(),
            $this->fieldsFormMonitoringChanges(),
        ];

        $antiFraud = [
            $this->fieldsFormAntiFraud(),
        ];

        $contentProtection = [
            $this->fieldsFormProtectContent(),
        ];

        $backup = [
            $this->fieldsFormBackup(),
        ];

        $tools = [
            $this->fieldsFormAdminDir(),
            $this->fieldsFormTools(),
            $this->fieldsFormPasswdGen(),
        ];

        $analysis = [
            $this->fieldsFormAnalyzeSystem(),
            $this->fieldsFormAnalyzeDomain(),
            $this->fieldsFormAnalyzeServerConfig(),
            $this->fieldsFormAnalyzeSsl(),
            $this->fieldsFormAnalyzeModules(),
        ];

        $help = [
            $this->fieldsFormAutoConfig(),
            $this->fieldsFormHelp(),
        ];

        $displayForms = \array_merge($dashboard, $generalSettings, $loginProtection, $httpHeaders, $passwordStrength, $firewall, $malwareScanner, $cartProtection, $monitoring, $antiFraud, $contentProtection, $backup, $tools, $analysis, $help);

        return $helper->generateForm($displayForms);
    }

    /**
     * Hook reCAPTCHA to footer.
     *
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayFooter($params)
    {
        if (true === (bool) Configuration::get('PRO_RECAPTCHA_V3_CONTACT_ACTIVATE') || true === (bool) Configuration::get('PRO_RECAPTCHA_V3_REGISTRATION_ACTIVATE')) {
            if ($this->context->controller instanceof ContactController || $this->context->controller instanceof AuthController) {
                return $this->display(__FILE__, 'inlineBadge.tpl');
            }
        }
    }

    /**
     * Hook stuff in front office header.
     *
     * @param array $params
     *
     * @throws PrestaShopException
     *
     * @return string
     */
    public function hookDisplayHeader($params)
    {
        // Firewall for Prestashop 1.6
        if (Tools::version_compare(_PS_VERSION_, '1.7.7.0')) {
            $this->getSecurityHeaders();

            $this->getFirewall();
        }

        // Disable browser features with JavaScript
        $this->protectContent();

        // Disable contact form
        if (true === (bool) Configuration::get('PRO_DISABLE_CONTACT_FORM')) {
            if ($this->context->controller instanceof ContactController) {
                Tools::redirect('pagenotfound');
            }
            $this->context->controller->addCSS($this->_path . '/views/css/disable-contact-form.css');
        }

        // Show password strengthbar
        if ($this->context->controller instanceof AuthController || $this->context->controller instanceof OrderController) {
            if (true === (bool) Configuration::get('PRO_PASSWORD_STRENGTHBAR')) {
                $this->context->controller->addJS($this->_path . 'views/js/passwordstrength.js');
                $this->context->controller->addCSS($this->_path . 'views/css/passwordstrength.css');
            }
        }

        if (true === (bool) Configuration::get('PRO_SECURE_EXTERNAL_LINKS')) {
            $this->context->controller->addJS($this->_path . 'views/js/external-links.js');
        }

        // Block add to cart
        if ((bool) Configuration::get('PRO_BLOCK_ADD_TO_CART')) {
            if ($this->context->controller instanceof ProductController) {
                $userAgent = $this->getUserAgent();
                if (false !== (bool) $userAgent) {
                    $crawlerDetect = new \Jaybizzle\CrawlerDetect\CrawlerDetect();
                    if (true === $crawlerDetect->isCrawler($userAgent)) {
                        $this->context->cart->delete();
                        $this->context->cookie->id_cart = 0;
                    }
                }
            }
        }

        $result = [];

        // Hook checks at registration form 1.7
        if ($this->context->controller instanceof AuthController) {
            if (Configuration::get('PRO_RECAPTCHA_V3_REGISTRATION_ACTIVATE')) {
                if (Tools::version_compare(_PS_VERSION_, '1.7.0.0')) {
                    $result[] = $this->displayGoogleRecaptchaV3('submitAccount');
                } else {
                    $result[] = $this->displayGoogleRecaptchaV3('submitCreate');
                }
            }
        }

        // Hook checks at contact form 1.7
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            if ($this->context->controller instanceof contactController) {
                if (Configuration::get('PRO_RECAPTCHA_V3_CONTACT_ACTIVATE')) {
                    $result[] = $this->displayGoogleRecaptchaV3('submitMessage');
                }
                $result[] = $this->displayContactFormErrors();
            }
        }

        return \implode('', $result);
    }

    /**
     * Hook stuff in customer account.
     *
     * @param array $params
     *
     * @return false|string
     */
    public function hookDisplayCustomerAccountForm($params)
    {
        if (true === (bool) Configuration::get('PRO_PASSWORD_STRENGTHBAR')) {
            return $this->display(__FILE__, 'passwordstrength.tpl');
        }
    }

    /**
     * Hook stuff in admin login.
     *
     * @param array $params
     *
     * @throws PrestaShopDatabaseException
     *
     * @return false|string
     */
    public function hookDisplayAdminLogin($params)
    {
        if (Module::isEnabled('securitypro')) {
            if (true === (bool) Configuration::get('PRO_TWO_FACTOR_AUTH') && false === $this->checkWhitelist('PRO_TWO_FACTOR_AUTH_WHITELIST')) {
                if (false === \in_array(Tools::getValue('2fa'), $this->getTfaToken(), true)) {
                    $this->context->smarty->assign([
                        'shopName' => Configuration::get('PS_SHOP_NAME'),
                        'baseUrl' => $this->getBaseURL(),
                    ]);

                    return $this->display(__FILE__, 'twoFactorAuth.tpl');
                }
            }
        }
    }

    /**
     * Disable admin login features.
     */
    public function adminLoginUnlock()
    {
        if (true === self::DEMO_MODE) {
            return false;
        }

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        Configuration::updateGlobalValue('PRO_STEALTH_LOGIN', false);
        Configuration::updateGlobalValue('PRO_TWO_FACTOR_AUTH', false);
        Configuration::updateGlobalValue('PRO_FAIL2BAN', false);
    }

    /**
     * Hook stuff in back office header.
     *
     * @param array $params
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws \RobThree\Auth\TwoFactorAuthException
     */
    public function hookDisplayBackOfficeTop($params)
    {
        if (true === self::DEMO_MODE) {
            if ('demo@demo.com' === $this->context->employee->email) {
                if (false !== Tools::strpos($_SERVER['REQUEST_URI'], 'improve/modules/manage') || false !== Tools::strpos($_SERVER['REQUEST_URI'], 'configure/advanced/employees')) {
                    Tools::redirectAdmin($this->currentAdminIndex());
                }
            }
        }

        if (true === (bool) Configuration::get('PRO_STEALTH_LOGIN')) {
            if (false !== (bool) Configuration::get('PRO_STEALTH_LOGIN_WHITELIST')) {
                if (false === $this->checkWhitelist('PRO_STEALTH_LOGIN_WHITELIST')) {
                    Tools::redirect('pagenotfound');
                }
            }
        }

        // Menu icon on PrestaShop 1.6
        $this->context->controller->addCss($this->_path . 'views/css/menuTabIcon.css');

        // Two-Factor auth
        $enabled2fa = (bool) Configuration::get('PRO_TWO_FACTOR_AUTH');
        $whitelist2fa = $this->checkWhitelist('PRO_TWO_FACTOR_AUTH_WHITELIST');
        $token2fa = \in_array(Tools::getValue('2fa'), $this->getTfaToken(), true);

        if (true === $enabled2fa && false === $whitelist2fa && 'AdminLogin' === Tools::getValue('controller')) {
            $this->context->controller->addCSS($this->_path . 'views/css/step-1.css');

            $twoFactorAuth = new \RobThree\Auth\TwoFactorAuth(Configuration::get('PS_SHOP_NAME'), 6, 30, 'sha1');

            $cookieName = '2FA';

            if ($_COOKIE[$this->cookieName($cookieName)] === $this->getCookieToken($cookieName, true)) {
                $cookieAccess = true;
            } else {
                $cookieAccess = false;
            }

            $verifyCode = $twoFactorAuth->verifyCode($this->twoFactorsecret(), \str_replace(' ', '', Tools::getValue('google')));
            $recoveryCode = Tools::substr(Tools::strtoupper($this->encrypt('2fa-recovery')), 0, 12);
            $inputRecoverCode = Tools::strtoupper(\str_replace(' ', '', Tools::getValue('google')));

            $serverIp = $this->serverIP();
            if (true === empty($serverIp) || '::1' === $serverIp || '127.0.0.1' === $serverIp) {
                $domain = ''; // Disable for localhost
            } else {
                $domain = $this->domain();
            }

            if (true === $verifyCode || $recoveryCode === $inputRecoverCode || true === $cookieAccess || true === $token2fa) {
                \setcookie(
                    $this->cookieName($cookieName),
                    $this->getCookieToken($cookieName, true),
                    \time() + 2147483647,
                    __PS_BASE_URI__,
                    $domain,
                    true === (bool) Configuration::get('PS_SSL_ENABLED_EVERYWHERE'),
                    true
                );

                if (false === isset($_COOKIE[$this->cookieName($cookieName)])) {
                    \header('Refresh:0'); // Fix for IE and Edge
                }

                $this->context->controller->addCSS($this->_path . 'views/css/step-2.css');
            }
        }

        if (Tools::version_compare(_PS_VERSION_, '1.7.7.0')) {
            if (true === (bool) Configuration::get('PRO_TWO_FACTOR_AUTH')) {
                $this->validate2fa();
            }
            $this->logBackOfficeLogins();
            $this->backofficeLoginBruteforce();
            $this->twoFactorAuthForce();
        }
    }

    /**
     * Hook stuff in dispatcher. This is the first hook that run.
     *
     * @param array $params
     */
    public function hookActionDispatcher($params)
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            $this->getSecurityHeaders();

            if ('front' === $this->context->controller->controller_type) {
                $this->getFirewall();
            }
        }
    }

    /**
     * Malware scanner.
     *
     * @throws PrestaShopException
     *
     * @return bool
     */
    public function checkMalware()
    {
        if (true === self::DEMO_MODE) {
            return false;
        }

        if (true === (bool) Configuration::get('PRO_MALWARE_SCAN_EMAIL') || true === (bool) Configuration::get('PRO_MALWARE_SCAN_LOG')) {
            // SHA1
            $globalWhitelist = [
                \hash('sha1', Tools::file_get_contents(_PS_MODULE_DIR_ . 'securitypro/securitypro.php')),
                '098885bc82924e75760fcbb310eb02ed2059b6dc',
                '1354a6014082aa0b674abaa19e3eff6075988ddb',
                '144ec6cee1da0289d82b3c17aba4c39203035a66',
                '1e3e21257ff40dd7f494c0335c1700feb97ecb36',
                '1e82e5ef40d2d7dea3a51bf7426f25b84073d7dd',
                '1ef3a4855bdfae4913bafac976c165dd7f1706ae',
                '219c9b6462a75229c84045d7ac12f6c1d417c764',
                '22c66f6b36bf7b9631b3a5602166c7ffb6aacec5',
                '26090323ab2b0892b292f4da41b96c306dc50155',
                '26e35630ba7c6df1d0b88e2bea63e624cf41ff34',
                '270c3fd08cfbe4b58a0d344e2938110e85b00179',
                '271689f0a4e25e627432f07b1e688977dc7a2a57',
                '305cd6917a730e0fe5a2323c302a3c7716fddd1b',
                '33fbe43c869585af04c4a3de036bacec85c3d417',
                '34a45c8efae509a445d9a1a89b1880f88e88ce19',
                '35d26fdef2e039d9f9cecb13e68d9d36867d1c24',
                '3aa98e29421be6377f89750f28747a11b07493a3',
                '3b85fdc0179e16aa5396a11491019815fbd0446b',
                '3c533a3d12bab28098ec654bb0b364e22d400cd1',
                '42475caf245eb104760edcfef17b552d8038a7ac',
                '45d1ba59a9dc0a9c2467f62e45a08c171faa1136',
                '47ab283f546dc2fbe8106b089338f38dda00c304',
                '4df8ccb38baf44015bb8530d8123581a237c0caf',
                '53e28d96b378b4f0add0432722939ffd1bc8ce47',
                '544c32642f54aa7b5b647756a1b5149a98487c94',
                '57c8ff46086fea338dcc5279fc0fe8d3ff887592',
                '5adfc97652c23cb31392c3723148169754252f54',
                '5b9b45b1537841845767bee9bd9f45b63120c77f',
                '5f7a6922b701f59944c47b2f99e09fc97dbbef24',
                '61cf85ebd50e66052fe0ff7ca42b4b40ec12dc24',
                '64692cc9587c4c49f3c122b4b7f8818e63e08d5b',
                '64fe281c6febb7c25eb19fa7ea8b1388d2e4986a',
                '6af01ba1ce89c02190c9a7baf2c5a87454863a31',
                '6bdad9c6874595bd2a52749628951d1ec05b2e8b',
                '6beeef4404445b0e727ec7923d15a61f719ea946',
                '6e1731132f0273fcfbab674e8b8ee44b4738deb8',
                '6f4df343813c040f0bf665d50edc91d3a260d133',
                '6fcd84e8e4e1334b4d2349387cdc6423586d696a',
                '703219d00b04f342440c512f292faf485880950c',
                '76c80d938a2132419834243b9511712726422224',
                '7b918b22940cc193e7fed71f4c2afd41ac9b3374',
                '7d20db2ccadb2559b5ea5cd2f760682572727b4c',
                '7e1ca6852d41628ce9edb45c210a3a4ccbade7f7',
                '7fa3135ee9d2c35d28eb726fa48a59a5a9c436f3',
                '813d79594b19defc4537154039a781159063a743',
                '89a3db0fe25ce58fb38dc4030439b2b5ff805c55',
                '8efd7517d398c7837d0c168366711cbdb47b07cd',
                '90ae2258dd0ecba587cc15823c50595a084044e5',
                '90ebf41014204e68615b5991f4d7c86de1acea1b',
                '9404785e26bac31d66df25a544a54ec86be6a59a',
                '97e1a2bd45f9eca2473c682f5d4ceb06b600a832',
                'a28c39290a656f103f18159dafd8a6a73cf7c457',
                'a52f2347526bdbb0e301abe94a9218b1fc73bc97',
                'a8dfc36a7ee54f54c4708aee3aba922837d20015',
                'a91f3152945346ea7dcd199f93a99c3db61a33fb',
                'ac63319068e94a7d361037deda36b828d278923a',
                'ae3c650edf34fdd4fedd0d78d790c16209872f01',
                'af2aa4dba95b6e5e1433b8cc4d20dc610d76ba1e',
                'aff52e62613ecc5bbc7aff231ab78ba7efa49e1f',
                'b0ee8a7f3b13652be224c8ebc84b2af00aa4930e',
                'b326e01a415e557447b42c9238a0745f6e11f46f',
                'b349e395f39a610fddae373f25b77106717420f5',
                'b594aeefb9e8e048e791eb70c63fd68b765f7fbf',
                'b95da1319fb65803de0983cd54da9b203d94c626',
                'bbbce9239322c4e1ee422975dc5bcd31666c9b30',
                'c6c60706c64d53c5dbdd32f819342fd2f2c37108',
                'cab4c2352b6d03bbcea4fd63915774c46c2f8987',
                'ceef4479ccae23cbecd79b668592b120b6a9c999',
                'd12d250a36f659f9e114e2ae97d295d15015ff3c',
                'd16c4094dacce681c4478feb1ac2e22cd960f712',
                'd35e6bf947536dc977f2cb17bb43978ad86a8606',
                'd6cd6d5e8add2d7f24095bc42c4964380346a647',
                'da0cdc1f0e0f43ffbb9ed80b174169355c36a3c4',
                'e1f39c8372175354cb9c4656345f5ce6fab37d04',
                'e2c5739095880f7547565f959cb77dd2f0e4f378',
                'e3f9a370e72b1e963e7465b669862cf27fd90a5c',
                'e982307086f737ac3637d914606f3b3b95fcb291',
                'ecab9cad7eee420a6c64c9a62ce267bf164f4b41',
                'f45e72bd4530f379c0ad99a0370a5428a312fedd',
                'f4bd85ea31d6331648041432a4028aa139a94421',
                'f74d3eb3608e51056afe2f887bf387cec323aeeb',
                'fc49466439327c867dd53794005955f5c2c3301b',
                'fce0a9eb052c9e736ffd7136e1872cf8cad4d6e6',
                'fcecd5d7997126224d235d19496a5e240b6dc2f5',
                'ff9e50696808fe8112389401a6e3bf9b9b4807f0',
                'b09d2bff9027c26dd6cebd10172ab011895b6d97',
                '802e55ee2bced9e4bded91bd2640ba607ab64170',
                '24a53ff265f4c5c0b7aab7e9455fe783b4d6b7d0',
                'b34327295ccc7fb73eab5411b6a48d9b43a26e14',
                'dc60ee6152fd8cfacad322d4d33112128c8b3c56',
            ];

            if (Tools::version_compare(_PS_VERSION_, '1.7.0.0')) {
                $excludeDirs = [
                    'cache',
                    'log',
                    'vendor',
                ];
            } elseif (Tools::version_compare(_PS_VERSION_, '1.7.3.0')) {
                $excludeDirs = [
                    'app',
                    'vendor',
                ];
            } else {
                $excludeDirs = [
                    'var',
                    'vendor',
                ];
            }

            $includeExt = [
                'php',
                'php3',
                'ph3',
                'php4',
                'ph4',
                'php5',
                'ph5',
                'php7',
                'ph7',
                'phtm',
                'phtml',
                'ico',
            ];

            if (true === (bool) Configuration::get('PRO_WHITELIST_MALWARE')) {
                $customWhitelist = \explode(',', Configuration::get('PRO_WHITELIST_MALWARE'));
                $totalWhitelist = \array_merge($globalWhitelist, $customWhitelist);
            } else {
                $totalWhitelist = $globalWhitelist;
            }

            $dirIter = new RecursiveDirectoryIterator(
                _PS_ROOT_DIR_,
                RecursiveDirectoryIterator::SKIP_DOTS
            );

            $files = new RecursiveCallbackFilterIterator($dirIter, static function ($file, $key, $iterator) use ($excludeDirs) {
                if ($iterator->hasChildren() && false === \in_array($file->getFilename(), $excludeDirs, true)) {
                    return true;
                }

                return $file->isFile();
            });

            $urls = [];
            foreach (new RecursiveIteratorIterator($files) as $file) {
                if ($file->isDir()) {
                    continue;
                }
                if (false === \in_array($file->getExtension(), $includeExt, true)) {
                    continue;
                }
                if (false === $file->isReadable()) {
                    continue;
                }
                $hash = \hash('sha1', Tools::file_get_contents($file->getPathname()));

                if (false === \in_array($hash, $totalWhitelist, true)) {
                    try {
                        if (true === $this->isInfectedFile($file->getPathname())) {
                            $urls[] = [
                                $file->getPathname(),
                                $hash,
                            ];
                        }
                    } catch (Exception $e) {
                        continue; // In case of an unknown error
                    }
                }
            }

            if (false === empty($urls)) {
                // Send e-mail
                if (true === (bool) Configuration::get('PRO_MALWARE_SCAN_EMAIL')) {
                    $header = [];
                    $header[] = $this->l('Malicious code found on your server. See the full path to the files below:') . \PHP_EOL;
                    $subject = $this->l('Security warning') . ': ' . $this->l('Malware scan');

                    $bodyLinks = [];
                    foreach ($urls as $url) {
                        $bodyLinks[] = '"' . $url[0] . '" (' . $url[1] . ')';
                    }

                    $body = \implode(\PHP_EOL, \array_merge($header, $bodyLinks));

                    $this->sendMail($subject, $body);
                }

                // Log
                if (true === (bool) Configuration::get('PRO_MALWARE_SCAN_LOG')) {
                    foreach ($urls as $url) {
                        $date = Tools::displayDate(\date('Y-m-d H:i:s', \filemtime($url[0])), null, true);
                        $this->logMalwareScanner(
                            $date,
                            '"' . $url[0] . '" (' . $url[1] . ')',
                            self::LOG_MALWARE_SCAN
                        );
                    }
                }
            }
        }
    }

    /**
     * Check if any files have changed.
     */
    public function checkDiff()
    {
        if (true === self::DEMO_MODE) {
            return false;
        }

        $logFilePath = _PS_MODULE_DIR_ . $this->name . self::DIR_DATA;
        if (false === \is_dir($logFilePath)) {
            \mkdir($logFilePath, 0755, true);
            $this->addIndexRecursively($logFilePath);
            \file_put_contents($logFilePath . '/.htaccess', $this->getHtaccessContent());
        }

        if (true === (bool) Configuration::get('PRO_FILE_CHANGES_EMAIL') || true === (bool) Configuration::get('PRO_FILE_CHANGES_LOG')) {
            $logFile = $logFilePath . \DIRECTORY_SEPARATOR . $this->encrypt('sp_filechanges') . '.json';

            if (Tools::version_compare(_PS_VERSION_, '1.7.0.0')) {
                $excludeDirs = [
                    'cache',
                    'img',
                    'log',
                    'stats',
                    'vendor',
                ];
            } elseif (Tools::version_compare(_PS_VERSION_, '1.7.3.0')) {
                $excludeDirs = [
                    'app',
                    'img',
                    'stats',
                    'vendor',
                ];
            } else {
                $excludeDirs = [
                    'img',
                    'stats',
                    'var',
                    'vendor',
                ];
            }

            $excludeExt = [
                'cache',
                'csv',
                'gif',
                'jpeg',
                'jpg',
                'json',
                'pdf',
                'png',
                'txt',
                'webp',
                'xls',
                'xlsx',
                'xml',
                'log',
            ];

            $dir = new RecursiveDirectoryIterator(
                _PS_ROOT_DIR_,
                RecursiveDirectoryIterator::SKIP_DOTS
            );

            $files = new RecursiveCallbackFilterIterator($dir, static function ($file, $key, $iterator) use ($excludeDirs) {
                if ($iterator->hasChildren() && !\in_array($file->getFilename(), $excludeDirs, true)) {
                    return true;
                }

                return $file->isFile();
            });

            $result = [];
            foreach (new RecursiveIteratorIterator($files) as $file) {
                if ($file->isDir()) {
                    continue;
                }

                if (\in_array($file->getExtension(), $excludeExt, true)) {
                    continue;
                }

                $realPath = \realpath(_PS_ROOT_DIR_ . '/modules/ntbackupandrestore/backup');
                if ($realPath && false !== \strpos($file->getPathname(), $realPath)) {
                    continue;
                }

                $result[$file->getPathname()] = \sprintf(
                    '%u|%u',
                    $file->getSize(),
                    \filemtime($file->getPathname())
                );
            }

            if (false === \is_file($logFile)) {
                \file_put_contents($logFile, \json_encode($result), \LOCK_EX);
            }

            $differance = \array_diff($result, \json_decode(Tools::file_get_contents($logFile), true));

            $fileNames = [];
            foreach ($differance as $filename => $key) {
                $fileNames[] = $filename;
            }

            if (false === empty($fileNames)) {
                // Send e-mail
                if (true === (bool) Configuration::get('PRO_FILE_CHANGES_EMAIL')) {
                    if (true === (bool) Configuration::get('PRO_FILE_CHANGES_EMAIL')) {
                        $subject = $this->l('Security notice') . ': ' . $this->l('File changes');
                        $header = [];
                        $header[] = $this->l('Some files have changed on your server. See the full path to the files below:') . \PHP_EOL;
                        $body = \implode(\PHP_EOL, \array_merge($header, $fileNames));

                        $this->sendMail($subject, $body);
                    }
                }

                // Log files
                if (true === (bool) Configuration::get('PRO_FILE_CHANGES_LOG')) {
                    foreach ($fileNames as $fileName) {
                        $lastModified = Tools::displayDate(\date('Y-m-d H:i:s', \filemtime($fileName)), null, true);
                        $this->logMalwareScanner(
                            $lastModified,
                            \sprintf('"%s"', $fileName),
                            self::LOG_FILE_CHANGES
                        );
                    }
                }
            }

            \file_put_contents($logFile, \json_encode($result), \LOCK_EX);
        }
    }

    /**
     * Creates a new backup file. Return true on successful backup.
     *
     * @throws \PhpZip\Exception\ZipException
     * @throws PrestaShopException
     * @throws \Google\Exception
     *
     * @return bool
     */
    public function backupDatabase()
    {
        if (true === self::DEMO_MODE) {
            return false;
        }

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $this->makeBackupDir(['database']);

        if (false === (bool) Configuration::get('PRO_BACKUP_DB') && false === (bool) Configuration::get('PRO_BACKUP_DB_DROPBOX') && false === (bool) Configuration::get('PRO_BACKUP_DB_GOOGLE_DRIVE')) {
            return false;
        }

        if (Tools::version_compare(_PS_VERSION_, '1.7.7.0')) {
            $defaultCharset = \Ifsnop\Mysqldump\Mysqldump::UTF8;
        } else {
            $defaultCharset = \Ifsnop\Mysqldump\Mysqldump::UTF8MB4;
        }

        $ignoreInsertTable = [
            _DB_PREFIX_ . 'connections',
            _DB_PREFIX_ . 'connections_page',
            _DB_PREFIX_ . 'connections_source',
            _DB_PREFIX_ . 'guest',
            _DB_PREFIX_ . 'statssearch',
            _DB_PREFIX_ . 'pagenotfound',
            _DB_PREFIX_ . 'mail',
            _DB_PREFIX_ . 'search_index',
            _DB_PREFIX_ . 'page_viewed',
            _DB_PREFIX_ . 'log',
            _DB_PREFIX_ . 'jm_pagecache',
            _DB_PREFIX_ . 'jm_pagecache_bl',
            _DB_PREFIX_ . 'jm_pagecache_mods',
            _DB_PREFIX_ . 'jm_pagecache_prof',
        ];

        $dumpSettings = [
            'include-tables' => [],
            'exclude-tables' => [],
            'compress' => \Ifsnop\Mysqldump\Mysqldump::NONE,
            'init_commands' => [],
            'no-data' => $ignoreInsertTable,
            'reset-auto-increment' => false,
            'add-drop-database' => false,
            'add-drop-table' => false,
            'add-drop-trigger' => false,
            'add-locks' => true,
            'complete-insert' => false,
            'databases' => false,
            'default-character-set' => $defaultCharset,
            'disable-keys' => true,
            'extended-insert' => true,
            'events' => false,
            'hex-blob' => true,
            'insert-ignore' => false,
            'no-autocommit' => true,
            'no-create-info' => false,
            'lock-tables' => true,
            'routines' => false,
            'single-transaction' => true,
            'skip-triggers' => false,
            'skip-tz-utc' => false,
            'skip-comments' => true,
            'skip-dump-date' => false,
            'skip-definer' => false,
        ];

        // Generate random number, to make it extra hard to guess backup file names
        $random = Tools::strtolower(Tools::passwdGen(16));
        $date = \time();

        $filePath = _PS_MODULE_DIR_ . $this->name . self::DIR_BACKUP_DATABASE;
        $fileName = $date . '-' . $random;

        $mySQLDump = new \Ifsnop\Mysqldump\Mysqldump(
            'mysql:host=' . _DB_SERVER_ . ';dbname=' . _DB_NAME_,
            _DB_USER_,
            _DB_PASSWD_,
            $dumpSettings
        );
        $mySQLDump->start($filePath . $fileName . '.sql');

        if (\function_exists('bzopen')) {
            $compressionMethod = \PhpZip\Constants\ZipCompressionMethod::BZIP2;
            $compressExt = '.bz2';
        } else {
            $compressionMethod = \PhpZip\Constants\ZipCompressionMethod::DEFLATED;
            $compressExt = '.zip';
        }

        $zipFile = new \PhpZip\ZipFile();
        $zipFile->addFile($filePath . $fileName . '.sql', $fileName . '.sql', $compressionMethod);

        $password = Configuration::get('PRO_BACKUP_DB_PASSWORD');

        if (true === (bool) $password) {
            $zipFile->setPassword($password);
        }

        $compressedFileName = $fileName . $compressExt;

        $zipFile->saveAsFile($filePath . $compressedFileName);

        Tools::deleteFile($filePath . $fileName . '.sql');

        $backupSaved = (int) Configuration::get('PRO_BACKUP_DB_SAVED');

        // Dropbox
        if (true === (bool) Configuration::get('PRO_BACKUP_DB_DROPBOX')) {
            $this->dropboxGenerateBackup(
                $backupSaved,
                $compressedFileName,
                $filePath,
                self::DIR_BACKUP_DATABASE
            );
        }

        // Google Drive
        if (true === (bool) Configuration::get('PRO_BACKUP_DB_GOOGLE_DRIVE')) {
            $this->googleDriveGenerateBackup(
                $backupSaved,
                $compressedFileName,
                $filePath,
                'database'
            );
        }

        // Local
        if (true === (bool) Configuration::get('PRO_BACKUP_DB')) {
            $this->localDeleteBackup($backupSaved, $filePath);
        } else {
            Tools::deleteFile($filePath . $compressedFileName); // It's only temporary
        }

        return true;
    }

    /**
     * Backup files to Dropbox and local.
     *
     * @throws \PhpZip\Exception\ZipException
     * @throws PrestaShopException
     * @throws \Google\Exception
     *
     * @return bool
     */
    public function backupFiles()
    {
        if (true === self::DEMO_MODE) {
            return false;
        }

        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $this->makeBackupDir(['files']);

        if (false === (bool) Configuration::get('PRO_BACKUP_FILE') && false === (bool) Configuration::get('PRO_BACKUP_FILE_DROPBOX') && false === (bool) Configuration::get('PRO_BACKUP_FILES_GOOGLE_DRIVE')) {
            return false;
        }

        // Generate some random number, to make it extra hard to guess backup file names
        $random = Tools::strtolower(Tools::passwdGen(16));
        $date = \time();

        $filePath = _PS_MODULE_DIR_ . $this->name . self::DIR_BACKUP_FILES;
        $fileName = $date . '-' . $random;

        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0')) {
            $tmpFiles = [
                'cache',
                'log',
            ];
        } elseif (Tools::version_compare(_PS_VERSION_, '1.7.3.0', '<=')) {
            $tmpFiles = [
                'app/cache',
                'app/logs',
            ];
        } else {
            $tmpFiles = [
                'var/cache',
                'var/logs',
            ];
        }

        $backupFiles = [
            'modules/securitypro/backup',
        ];

        $ignoreFiles = \array_merge($tmpFiles, $backupFiles);

        $directoryIterator = new RecursiveDirectoryIterator(_PS_ROOT_DIR_); // with subdirectories

        $ignoreIterator = new \PhpZip\Util\Iterator\IgnoreFilesRecursiveFilterIterator(
            $directoryIterator,
            $ignoreFiles
        );

        if (\function_exists('bzopen')) {
            $compressionMethod = \PhpZip\Constants\ZipCompressionMethod::BZIP2;
            $compressExt = '.bz2';
        } else {
            $compressionMethod = \PhpZip\Constants\ZipCompressionMethod::DEFLATED;
            $compressExt = '.zip';
        }

        $zipFile = new \PhpZip\ZipFile();

        $zipFile->addFilesFromIterator($ignoreIterator, '/', $compressionMethod);

        $compressedFileName = $fileName . $compressExt;

        $zipFile->saveAsFile($filePath . $compressedFileName);

        $backupSaved = (int) Configuration::get('PRO_BACKUP_FILE_SAVED');

        if (0 === $backupSaved) {
            return true;
        }

        if (true === (bool) Configuration::get('PRO_BACKUP_FILE_DROPBOX')) {
            $this->dropboxGenerateBackup(
                $backupSaved,
                $compressedFileName,
                $filePath,
                self::DIR_BACKUP_FILES
            );
        }

        // Google Drive
        if (true === (bool) Configuration::get('PRO_BACKUP_FILES_GOOGLE_DRIVE')) {
            $this->googleDriveGenerateBackup(
                $backupSaved,
                $compressedFileName,
                $filePath,
                'files'
            );
        }

        // Local
        if (true === (bool) Configuration::get('PRO_BACKUP_FILE')) {
            $this->localDeleteBackup($backupSaved, $filePath);
        } else {
            Tools::deleteFile($filePath . $compressedFileName); // It's only temporary
        }
    }

    /**
     * Monitoring changes.
     *
     * @return false|void
     */
    public function checkMonitoringChanges()
    {
        if (true === self::DEMO_MODE) {
            return false;
        }

        $serverIp = $this->serverIP();
        $domain = $this->domain();

        if (true === empty($serverIp) || '::1' === $serverIp || '127.0.0.1' === $serverIp) {
            return; // Disable for localhost
        }

        $url = 'https://www.iplocate.io/api/lookup/' . $serverIp;

        $content = $this->remoteContent($url);

        if (false !== $content) {
            $ipLookup = \json_decode($content, true);
        } else {
            $ipLookup = null;
        }

        $logFilePath = _PS_MODULE_DIR_ . $this->name . self::DIR_DATA;

        if (false === \is_dir($logFilePath)) {
            \mkdir($logFilePath, 0755, true);
            $this->addIndexRecursively($logFilePath);
        }

        $fileLog = $logFilePath . \DIRECTORY_SEPARATOR . $this->encrypt('sp_serverchanges') . '.json';

        $certInfo = $this->getCertInfo();

        if (\file_exists($fileLog)) {
            $logDataOld = \json_decode(Tools::file_get_contents($fileLog), true);

            $warning = [];

            // Server IP
            if (true === (bool) Configuration::get('PRO_SERVER_IP')) {
                $oldServerIp = $logDataOld['serverIp'];
                if (false === empty($oldServerIp)) {
                    if ($oldServerIp !== $serverIp) {
                        $warning[] = $this->l('Server IP for') . ' \'' . $domain . '\' ' . $this->l('changed from') . ' \'' . $oldServerIp . '\' ' . $this->l('to') . ' \'' . $serverIp . '\'.';
                    }
                }
            }

            // Server location
            if (true === (bool) Configuration::get('PRO_SERVER_LOCATION')) {
                $oldServerLocation = $logDataOld['serverLocated'];
                if (false === empty($oldServerLocation)) {
                    $newServerLocation = $ipLookup['country'];
                    if ($oldServerLocation !== $newServerLocation) {
                        $warning[] = $this->l('Server location for') . ' \'' . $domain . '\' ' . $this->l('changed from') . ' \'' . $oldServerLocation . '\' ' . $this->l('to') . ' \'' . $newServerLocation . '\'.';
                    }
                }
            }

            // ISP
            if (true === (bool) Configuration::get('PRO_SERVER_ISP')) {
                $oldServerCountry = $logDataOld['ISP'];
                if (false === empty($oldServerCountry)) {
                    $newServerCountry = $ipLookup['org'];
                    if ($oldServerCountry !== $newServerCountry) {
                        $warning[] = $this->l('ISP for') . ' \'' . $domain . '\' ' . $this->l('changed from') . ' \'' . $oldServerCountry . '\' ' . $this->l('to') . ' \'' . $newServerCountry . '\'.';
                    }
                }
            }

            // TLS expire in
            if (false !== $certInfo) {
                if (true === (bool) Configuration::get('PRO_TLS_EXPIRE')) {
                    $sslExpireIn = \round(($certInfo['validTo_time_t'] - \time()) / (86400));
                    if ($sslExpireIn <= 7) {
                        $warning[] = $this->l('SSL/TLS certificate for') . ' \'' . $domain . '\' ' . $this->l('expires in') . ' ' . $sslExpireIn . ' ' . $this->l('days') . '.';
                    }
                }
            }

            // Output
            if (false === empty($warning)) {
                $subject = $this->l('Security warning') . ': ' . $this->l('Server notification');
                $body = \implode(\PHP_EOL, $warning);
                $this->sendMail($subject, $body);
            }
        }

        // Update file
        $logDataNew = [
            'serverIp' => $serverIp,
            'serverLocated' => $ipLookup['country'],
            'ISP' => $ipLookup['org'],
        ];

        \file_put_contents($fileLog, \json_encode($logDataNew));
    }

    /**
     * Delete old carts.
     */
    public function deleteOldCarts()
    {
        if (true === self::DEMO_MODE) {
            return false;
        }

        if (Configuration::get('PRO_DELETE_OLD_CARTS')) {
            $query = 'DELETE FROM `' . _DB_PREFIX_ . 'cart`
            WHERE id_cart NOT IN (SELECT id_cart FROM `' . _DB_PREFIX_ . 'orders`)
            AND date_add < "' . pSQL(\date('Y-m-d', \strtotime('-' . Configuration::get('PRO_DELETE_OLD_CARTS_DAYS') . ' day'))) . '"';

            Db::getInstance()->execute($query);
        }
    }

    /**
     * Log cronjob.
     *
     * @param string $fileName
     * @param string $response
     *
     * @throws PrestaShopException
     */
    public function logCron($fileName, $response)
    {
        $lastModified = Tools::displayDate(\date('Y-m-d H:i:s'), null, true);

        $content = [];
        $content[] = $this->getClientIP();
        $content[] = '- -';
        $content[] = '[' . $lastModified . ']';
        $content[] = '[' . $fileName . ']';
        $content[] = '"' . $response . '"';
        \file_put_contents($this->getLogFile(self::LOG_CRONJOB), \implode(' ', $content) . \PHP_EOL, \FILE_APPEND);
    }

    /**
     * Hook validate order.
     *
     * @param array $params
     *
     * @return bool
     */
    public function hookActionValidateOrder($params)
    {
        $proxyHeaders = [
            'CLIENT-IP',
            'CLIENT_IP',
            'FORWARDED',
            'FORWARDED_FOR',
            'FORWARDED_FOR_IP',
            'HTTP_AKAMAI_ORIGIN_HOP',
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_FASTLY_CLIENT_IP',
            'HTTP_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED_FOR_IP',
            'HTTP_INCAP_CLIENT_IP',
            'HTTP_PROXY_CONNECTION',
            'HTTP_TRUE_CLIENT_IP',
            'HTTP_VIA',
            'HTTP_X_CLIENTIP',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_X_FORWARDED',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_IP_TRAIL',
            'HTTP_X_REAL_IP',
            'HTTP_X_VARNISH',
            'MT-PROXY-ID',
            'PROXY-AGENT',
            'PROXY-CONNECTION',
            'VIA',
            'X-PROXY-ID',
            'X-TINYPROXY',
            'X_FORWARDED',
            'X_FORWARDED_FOR',
        ];

        $proxy = 0;

        foreach ($proxyHeaders as $proxyHeader) {
            if (isset($_SERVER[$proxyHeader])) {
                $proxy = 1;
            }
        }

        $userAgent = $this->getUserAgent();

        $clientUserAgent = (true === (bool) $userAgent) ? $userAgent : '';

        $clientData = [
            (int) $params['order']->id, // Order ID
            pSQL($this->getClientIP()), // Client IP
            pSQL($clientUserAgent), // Client UA
            (int) $proxy, // Check if Proxy
        ];

        return Db::getInstance()->execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'securitypro_af` (`id_order`, `ip`, `ua`, `proxy`) VALUES (\'' . \implode('\', \'', $clientData) . '\')');
    }

    /**
     * Hook for 1.7.7+.
     *
     * @param array $params
     */
    public function hookActionAdminLoginControllerBefore($params)
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            if (true === (bool) Configuration::get('PRO_TWO_FACTOR_AUTH')) {
                $this->validate2fa();
            }
            $this->logBackOfficeLogins();
            $this->backofficeLoginBruteforce();
            $this->twoFactorAuthForce();
        }
    }

    /**
     * Auto configure the module.
     */
    public function autoConfiguration()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (true === (bool) Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) {
            $sslEnabled = true;
        } else {
            $sslEnabled = false;
        }

        $clientIP = $this->getClientIP();

        $email = Configuration::get('PS_SHOP_EMAIL');

        Configuration::updateGlobalValue('PRO_GENERAL_EMAIL', $email);
        Configuration::updateGlobalValue('PRO_CLICK_JACKING', true);
        Configuration::updateGlobalValue('PRO_X_XSS_PROTECTION', true);
        Configuration::updateGlobalValue('PRO_X_CONTENT_TYPE_OPTIONS', true);
        if (true === $sslEnabled) {
            Configuration::updateGlobalValue('PRO_STRICT_TRANSPORT_SECURITY', true);
            Configuration::updateGlobalValue('PRO_EXPECT_CT', true);
        }
        Configuration::updateGlobalValue('PRO_HSTS_SETTINGS_0', false);
        Configuration::updateGlobalValue('PRO_HSTS_SETTINGS_1', false);
        Configuration::updateGlobalValue('PRO_ACCESS_CONTROL_ALLOW_METHODS', false);
        Configuration::updateGlobalValue('PRO_REFERRER_POLICY', true);
        Configuration::updateGlobalValue('PRO_X_PERMITTED_CROSS_DOMAIN_POLICY', false);
        Configuration::updateGlobalValue('PRO_X_DOWNLOAD_OPTIONS', false);
        Configuration::updateGlobalValue('PRO_UNSET_HEADERS', true);
        Configuration::updateGlobalValue('PRO_HTPASSWD', false);
        Configuration::updateGlobalValue('PRO_HTPASSWD_USER', '');
        Configuration::updateGlobalValue('PRO_HTPASSWD_PASS', '');
        Configuration::updateGlobalValue('PRO_BAN_IP', '');
        Configuration::updateGlobalValue('PRO_BAN_IP_ACTIVATE', false);
        Configuration::updateGlobalValue('PRO_FAIL2BAN', true);
        Configuration::updateGlobalValue('PRO_FAIL2BAN_LOG', true);
        Configuration::updateGlobalValue('PRO_BAN_TIME', 30);
        Configuration::updateGlobalValue('PRO_MAX_RETRY', 5);
        Configuration::updateGlobalValue('PRO_FIND_TIME', 10);
        Configuration::updateGlobalValue('PRO_SEND_MAIL', true);
        Configuration::updateGlobalValue('PRO_SEND_MAIL_LOGIN', false);
        Configuration::updateGlobalValue('PRO_WHITELIST_IPS', $clientIP);
        Configuration::updateGlobalValue('PRO_FILE_CHANGES_EMAIL', false);
        Configuration::updateGlobalValue('PRO_FILE_CHANGES_LOG', true);
        Configuration::updateGlobalValue('PRO_LOGIN_ATTEMPTS_LOG', true);
        Configuration::updateGlobalValue('PRO_MALWARE_SCAN_EMAIL', false);
        Configuration::updateGlobalValue('PRO_MALWARE_SCAN_LOG', true);
        Configuration::updateGlobalValue('PRO_WHITELIST_MALWARE', '');
        Configuration::updateGlobalValue('PRO_DISABLE_RIGHT_CLICK', 2);
        Configuration::updateGlobalValue('PRO_DISABLE_DRAG', true);
        Configuration::updateGlobalValue('PRO_DISABLE_COPY', false);
        Configuration::updateGlobalValue('PRO_DISABLE_CUT', false);
        Configuration::updateGlobalValue('PRO_DISABLE_PRINT', false);
        Configuration::updateGlobalValue('PRO_DISABLE_SAVE', false);
        Configuration::updateGlobalValue('PRO_DISABLE_VIEW_PAGE_SOURCE', true);
        Configuration::updateGlobalValue('PRO_DISABLE_CONSOLE', false);
        Configuration::updateGlobalValue('PRO_DISABLE_TEXT_SELECTION', false);
        Configuration::updateGlobalValue('PRO_ADMIN_DIRECTORY', false);
        Configuration::updateGlobalValue('PRO_BLOCK_ADD_TO_CART', false);
        Configuration::updateGlobalValue('PRO_DELETE_OLD_CARTS', true);
        Configuration::updateGlobalValue('PRO_DELETE_OLD_CARTS_DAYS', 30);
        Configuration::updateGlobalValue('PRO_ANTI_FLOOD', true);
        Configuration::updateGlobalValue('PRO_ANTI_MAX_REQUESTS', 100);
        Configuration::updateGlobalValue('PRO_ANTI_REQ_TIMEOUT', 5);
        Configuration::updateGlobalValue('PRO_ANTI_BAN_TIME', 600);
        Configuration::updateGlobalValue('PRO_DISPLAY_RECAPTCHA_V3', 'bottomleft');
        Configuration::updateGlobalValue('PRO_GOOGLE_SAFE_BROWSING_V4_ACTIVATE', false);
        Configuration::updateGlobalValue('PRO_DISPOSABLE_EMAIL_PROVIDERS_ACTIVATE', false);
        Configuration::updateGlobalValue('PRO_DISPOSABLE_EMAIL_PROVIDERS_REGISTRATION_ACTIVATE', false);
        Configuration::updateGlobalValue('PRO_EMAIL_CHECKER_REGISTRATION_ACTIVATE', false);
        Configuration::updateGlobalValue('PRO_EMAIL_CHECKER_CUSTOM_LIST_REGISTRATION', '');
        Configuration::updateGlobalValue('PRO_EMAIL_CHECKER_ACTIVATE', false);
        Configuration::updateGlobalValue('PRO_EMAIL_CHECKER_CUSTOM_LIST', '');
        Configuration::updateGlobalValue('PRO_MESSAGE_CHECKER_ACTIVATE', false);
        Configuration::updateGlobalValue('PRO_MESSAGE_CHECKER_CUSTOM_LIST', '');
        Configuration::updateGlobalValue('PRO_HONEYPOT_API', false);
        Configuration::updateGlobalValue('PRO_MONTASTIC_API', false);
        if (true === $this->validateHoneyPotApi(Configuration::get('PRO_HONEYPOT_API'))) {
            Configuration::updateGlobalValue('PRO_FIREWALL_CHECK_BOT', true);
        } else {
            Configuration::updateGlobalValue('PRO_FIREWALL_CHECK_BOT', false);
        }
        Configuration::updateGlobalValue('PRO_FIREWALL_SQL_CHECK', 1);
        Configuration::updateGlobalValue('PRO_FIREWALL_XXS_CHECK', 1);
        Configuration::updateGlobalValue('PRO_FIREWALL_SHELL_CHECK', 1);
        Configuration::updateGlobalValue('PRO_FIREWALL_XST_CHECK', true);
        Configuration::updateGlobalValue('PRO_DIR_TRAVERSAL', true);
        Configuration::updateGlobalValue('PRO_FIREWALL_RFI_CHECK', false);
        Configuration::updateGlobalValue('PRO_BLOCK_FILE_UPLOAD', false);
        Configuration::updateGlobalValue('PRO_BLOCK_SCAN_FILE_UPLOAD', true);
        Configuration::updateGlobalValue('PRO_FIREWALL_LOG', true);
        Configuration::updateGlobalValue('PRO_BACKUP_DB', false);
        Configuration::updateGlobalValue('PRO_BACKUP_DB_DROPBOX', false);
        Configuration::updateGlobalValue('PRO_BACKUP_DB_SAVED', 7);
        Configuration::updateGlobalValue('PRO_BACKUP_FILE_SAVED', 7);
        Configuration::updateGlobalValue('PRO_BACKUP_FILE', false);
        Configuration::updateGlobalValue('PRO_BACKUP_FILE_DROPBOX', false);
        Configuration::updateGlobalValue('PRO_TWO_FACTOR_AUTH', false);
        Configuration::updateGlobalValue('PRO_TWO_FACTOR_AUTH_FORCE', false);
        Configuration::updateGlobalValue('PRO_TWO_FACTOR_AUTH_WHITELIST', '');
        Configuration::updateGlobalValue('PRO_FIREWALL_WHITELIST', '');
        Configuration::updateGlobalValue('PRO_FAKE_ACCOUNTS', false);
        Configuration::updateGlobalValue('PRO_DISALLOW_URL_CUSTOMER_NAME', false);
        Configuration::updateGlobalValue('PRO_WHITELIST_PROTECT_CONTENT', $clientIP);
        Configuration::updateGlobalValue('PRO_BLOCK_USER_AGENT_ACTIVATE', 0);
        Configuration::updateGlobalValue('PRO_BLOCK_USER_AGENT', '');
        Configuration::updateGlobalValue('PRO_BLOCK_TOR', false);
        Configuration::updateGlobalValue('PRO_DISABLE_CONTACT_FORM', false);
        Configuration::updateGlobalValue('PRO_RECAPTCHA_V3_CONTACT_ACTIVATE', false);
        Configuration::updateGlobalValue('PRO_RECAPTCHA_V3_THEME', 'light');
        Configuration::updateGlobalValue('PRO_PAGE_NOT_FOUND_LOG', true);
        Configuration::updateGlobalValue('PRO_PASSWORD_STRENGTHBAR', false);
        Configuration::updateGlobalValue('PRO_SECURE_EXTERNAL_LINKS', true);
        Configuration::updateGlobalValue('PRO_ANTI_FRAUD', false);
        Configuration::updateGlobalValue('PRO_ANTI_FRAUD_UNIT', 'km');
        Configuration::updateGlobalValue('PRO_ANTI_FRAUD_HOOK', 'left');
        Configuration::updateGlobalValue('PRO_SERVER_IP', false);
        Configuration::updateGlobalValue('PRO_SERVER_LOCATION', false);
        Configuration::updateGlobalValue('PRO_SERVER_ISP', false);
        Configuration::updateGlobalValue('PRO_TLS_EXPIRE', false);
        Configuration::updateGlobalValue('PRO_STEALTH_LOGIN', false);
        Configuration::updateGlobalValue('PRO_STEALTH_LOGIN_WHITELIST', $clientIP);
        Configuration::updateGlobalValue('PRO_DEBUG_CRON', false);
    }

    /**
     * @return bool
     */
    public function removeFiles()
    {
        $elements = \array_merge($this->blacklistedFilesRoot(), $this->checkFilesCVE20179841(), $this->getFilePathExt(_PS_MODULE_DIR_), $this->getFilePathExt(_PS_ROOT_DIR_));

        if (false === empty($elements)) {
            // Dirs
            foreach ($this->checkFilesCVE20179841() as $checkedDir) {
                if (\is_dir($checkedDir)) {
                    Tools::deleteDirectory($checkedDir);
                }
            }

            // Files
            foreach (\array_merge($this->blacklistedFilesRoot(), $this->getFilePathExt(_PS_MODULE_DIR_), $this->getFilePathExt(_PS_ROOT_DIR_)) as $checkedFile) {
                Tools::deleteFile($checkedFile);
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function checkRemoveFilesAnalyze()
    {
        $elements = \array_merge($this->blacklistedFilesRoot(), $this->checkFilesCVE20179841(), $this->getFilePathExt(_PS_MODULE_DIR_), $this->getFilePathExt(_PS_ROOT_DIR_));
        if (false === empty($elements)) {
            return true;
        }

        return false;
    }

    /**
     * @param false $analyse
     *
     * @return bool
     */
    public function createIndex($analyse = false)
    {
        $dirsIndex = [
            _PS_MODULE_DIR_,
            _PS_ALL_THEMES_DIR_,
            _PS_ROOT_DIR_ . \DIRECTORY_SEPARATOR . 'upload',
        ];

        foreach ($dirsIndex as $dirIndex) {
            $this->addIndexRecursively($dirIndex, $analyse);
        }

        $createIndexPath = _PS_MODULE_DIR_ . self::REPORT_CREATE_INDEX;

        if (true === \file_exists($createIndexPath)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function checkFolderPermissions()
    {
        if (true === $this->isWindowsOs()) {
            return;
        }

        $permissionsReportPath = _PS_MODULE_DIR_ . self::REPORT_PERMISSIONS;

        $this->chmodFileFolderAnalyze(_PS_ROOT_DIR_);

        // Delete file if empty
        \clearstatcache();
        if (0 === \filesize($permissionsReportPath)) {
            Tools::deleteFile($permissionsReportPath);
        }

        // Download
        if (\file_exists($permissionsReportPath)) {
            return true;
        }

        return false;
    }

    /**
     * @param $name
     */
    public function clearLog($name)
    {
        foreach ($this->getAllLogs() as $key => $log) {
            if ($key === $name) {
                \file_put_contents($this->getLogFile($log), '');
            }
        }
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function checkLog($name)
    {
        foreach ($this->getAllLogs() as $key => $log) {
            if ($key === $name) {
                if (0 === \filesize($this->getLogFile($log))) {
                    return true;
                }

                return false;
            }
        }
    }

    /**
     * Post configure form values.
     */
    protected function postProcess()
    {
        foreach ($this->getConfigFormValues() as $key) {
            Configuration::updateGlobalValue($key, Tools::getValue($key));
        }
    }

    /**
     * Display dashboard.
     *
     * @throws \Google\Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @return array
     */
    protected function fieldsFormDashboard()
    {
        $this->makeBackupDir();

        $cronJobs = [];
        $cronjobLink = '<i class="icon icon-link"></i> ' . $this->l('Run cronjob');

        if (true === (bool) Configuration::get('PRO_MALWARE_SCAN_EMAIL') || true === (bool) Configuration::get('PRO_MALWARE_SCAN_LOG')) {
            $cronJobs[] = [
                $this->l('Title') => $this->l('Malware scan'),
                $this->l('Cronjob') => '<kbd>' . $this->generateCronLink('MalwareScanner') . '</kbd> <a href="javascript:void(0)" onclick="copyToClipboard(\'' . $this->generateCronLink('MalwareScanner') . '\')"><i class="icon icon-clipboard"></i></a>',
                null => '<span class="securitypro-position">' . $this->generateBtnLink($cronjobLink, $this->generateCronLink('MalwareScanner', true)) . '</span>',
            ];
        }

        if (true === (bool) Configuration::get('PRO_DELETE_OLD_CARTS')) {
            $cronJobs[] = [
                $this->l('Title') => $this->l('Delete old carts'),
                $this->l('Cronjob') => '<kbd>' . $this->generateCronLink('DeleteOldCarts') . '</kbd> <a href="javascript:void(0)" onclick="copyToClipboard(\'' . $this->generateCronLink('DeleteOldCarts') . '\')"><i class="icon icon-clipboard"></i></a>',
                null => '<span class="securitypro-position">' . $this->generateBtnLink($cronjobLink, $this->generateCronLink('DeleteOldCarts', true)) . '</span>',
            ];
        }

        // Cronjobs
        if (true === (bool) Configuration::get('PRO_FILE_CHANGES_EMAIL')
            || true === (bool) Configuration::get('PRO_FILE_CHANGES_LOG')
            || true === (bool) Configuration::get('PRO_SERVER_IP')
            || true === (bool) Configuration::get('PRO_SERVER_LOCATION')
            || true === (bool) Configuration::get('PRO_SERVER_ISP')
            || true === (bool) Configuration::get('PRO_TLS_EXPIRE')
        ) {
            $cronJobs[] = [
                $this->l('Title') => $this->l('Monitoring'),
                $this->l('Cronjob') => '<kbd>' . $this->generateCronLink('Monitoring') . '</kbd> <a href="javascript:void(0)" onclick="copyToClipboard(\'' . $this->generateCronLink('Monitoring') . '\')"><i class="icon icon-clipboard"></i></a>',
                null => '<span class="securitypro-position">' . $this->generateBtnLink($cronjobLink, $this->generateCronLink('Monitoring', true)) . '</span>',
            ];
        }

        // Validate input: Database backup
        if (true === (bool) Configuration::get('PRO_BACKUP_DB') || true === (bool) Configuration::get('PRO_BACKUP_DB_DROPBOX') || true === (bool) configuration::get('PRO_BACKUP_DB_GOOGLE_DRIVE')) {
            $cronJobs[] = [
                $this->l('Title') => $this->l('Backup database'),
                $this->l('Cronjob') => '<kbd>' . $this->generateCronLink('BackupDatabase') . '</kbd> <a href="javascript:void(0)" onclick="copyToClipboard(\'' . $this->generateCronLink('BackupDatabase') . '\')"><i class="icon icon-clipboard"></i></a>',
                null => '<span class="securitypro-position">' . $this->generateBtnLink($cronjobLink, $this->generateCronLink('BackupDatabase', true)) . '</span>',
            ];
        }

        // Validate input: Files backup
        if (true === (bool) Configuration::get('PRO_BACKUP_FILE') || true === (bool) Configuration::get('PRO_BACKUP_FILE_DROPBOX') || true === (bool) configuration::get('PRO_BACKUP_FILES_GOOGLE_DRIVE')) {
            $cronJobs[] = [
                $this->l('Title') => $this->l('Backup files'),
                $this->l('Cronjob') => '<kbd>' . $this->generateCronLink('BackupFiles') . '</kbd> <a href="javascript:void(0)" onclick="copyToClipboard(\'' . $this->generateCronLink('BackupFiles') . '\')"><i class="icon icon-clipboard"></i></a>',
                null => '<span class="securitypro-position">' . $this->generateBtnLink($cronjobLink, $this->generateCronLink('BackupFiles', true)) . '</span>',
            ];
        }

        if (true === (bool) Configuration::get('PRO_BACKUP_FILE_DROPBOX') || true === (bool) Configuration::get('PRO_BACKUP_DB_DROPBOX')) {
            $client = $this->dropboxGetClient();

            if (isset($client->files->list_folder(self::DIR_BACKUP_DATABASE)['entries'])) {
                $featuredDatabase = $client->files->list_folder(self::DIR_BACKUP_DATABASE)['entries'];
            } else {
                $featuredDatabase = [];
            }

            if (isset($client->files->list_folder(self::DIR_BACKUP_FILES)['entries'])) {
                $featuredFiles = $client->files->list_folder(self::DIR_BACKUP_FILES)['entries'];
            } else {
                $featuredFiles = [];
            }

            $dropboxBackupTotal = [
                [
                    $featuredFiles,
                    'DropboxBackupFilesDelete',
                    'DropboxBackupFilesDownload',
                    $this->l('Filesystem'),
                ],
                [
                    $featuredDatabase,
                    'DropboxBackupDatabaseDelete',
                    'DropboxBackupDatabaseDownload',
                    $this->l('Database'),
                ],
            ];

            $dropboxBackup = [];
            foreach ($dropboxBackupTotal as $dropboxBackupSingle) {
                if (false === empty($dropboxBackupSingle)) {
                    foreach ($dropboxBackupSingle[0] as $valueDatabase) {
                        $lastModified = Tools::substr($valueDatabase['server_modified'], 0, 10);
                        $dropboxBackup[] = [
                            $this->l('Type') => $dropboxBackupSingle[3],
                            $this->l('Size') => Tools::formatBytes($valueDatabase['size'], 1) . 'B',
                            $this->l('Date') => $lastModified,
                            $this->l('Path') => $valueDatabase['path_lower'],
                            null => '<span class="securitypro-position"><a class="btn btn-default" onclick="return confirm(\'' . $this->l('Are you sure, you want to delete') . ' ' . $valueDatabase['name'] . '?\')" href="' . $this->currentAdminIndex() . '&' . $dropboxBackupSingle[1] . '=1&file=' . $valueDatabase['name'] . '"><i class="icon icon-trash-o"></i> ' . $this->l('Delete') . '</a><span class="securitypro-divider"></span><a class="btn btn-default" href="' . $this->currentAdminIndex() . '&' . $dropboxBackupSingle[2] . '=1&file=' . $valueDatabase['name'] . '"><i class="icon icon-download"></i> ' . $this->l('Download') . '</a></span>',
                        ];
                    }
                }
            }
            $keys = \array_column($dropboxBackup, $this->l('Date'));
            \array_multisort($keys, \SORT_DESC, $dropboxBackup);
        }

        if (true === (bool) Configuration::get('PRO_BACKUP_FILES_GOOGLE_DRIVE') || true === (bool) Configuration::get('PRO_BACKUP_DB_GOOGLE_DRIVE')) {
            $googleDriveBackup = [];
            if (false !== $this->googleDriveGetFileNames()) {
                if (null !== ($this->googleDriveGetFileNames($this->googleDriveGenerateFolders()['files']))) {
                    $featuredFiles = $this->googleDriveGetFileNames($this->googleDriveGenerateFolders()['files']);
                } else {
                    $featuredFiles = [];
                }

                if (null !== ($this->googleDriveGetFileNames($this->googleDriveGenerateFolders()['database']))) {
                    $featuredDatabase = $this->googleDriveGetFileNames($this->googleDriveGenerateFolders()['database']);
                } else {
                    $featuredDatabase = [];
                }

                $backupGoogleDriveTotal = [
                    [
                        $featuredFiles,
                        'GoogleDriveBackupFilesDelete',
                        'GoogleDriveBackupFilesDownload',
                        $this->l('Filesystem'),
                        self::DIR_BACKUP_FILES,
                    ],
                    [
                        $featuredDatabase,
                        'GoogleDriveBackupDatabaseDelete',
                        'GoogleDriveBackupDatabaseDownload',
                        $this->l('Database'),
                        self::DIR_BACKUP_DATABASE,
                    ],
                ];

                foreach ($backupGoogleDriveTotal as $backupGoogleDriveSingle) {
                    if (false === empty($backupGoogleDriveSingle)) {
                        foreach ($backupGoogleDriveSingle[0] as $valueDatabase) {
                            $lastModified = Tools::displayDate(\date('Y-m-d H:i:s', (int) Tools::substr($valueDatabase['name'], 0, 10)));

                            $googleDriveBackup[] = [
                                $this->l('Type') => $backupGoogleDriveSingle[3],
                                $this->l('Size') => Tools::formatBytes($this->googleDriveGetFileSize($valueDatabase['id'])['size'], 1) . 'B',
                                $this->l('Date') => $lastModified,
                                $this->l('Path') => $backupGoogleDriveSingle[4] . $valueDatabase['name'],
                                null => '<span class="securitypro-position"><a class="btn btn-default" onclick="return confirm(\'' . $this->l('Are you sure, you want to delete') . ' ' . $valueDatabase['name'] . '?\')" href="' . $this->currentAdminIndex() . '&' . $backupGoogleDriveSingle[1] . '=1&FileId=' . $valueDatabase['id'] . '"><i class="icon icon-trash-o"></i> ' . $this->l('Delete') . '</a><span class="securitypro-divider"></span><a class="btn btn-default" href="' . $this->currentAdminIndex() . '&' . $backupGoogleDriveSingle[2] . '=1&FileId=' . $valueDatabase['id'] . '&FileName=' . $valueDatabase['name'] . '"><i class="icon icon-download"></i> ' . $this->l('Download') . '</a></span>',
                            ];
                        }
                    }
                }
            }
            $keys = \array_column($googleDriveBackup, $this->l('Date'));
            \array_multisort($keys, \SORT_DESC, $googleDriveBackup);
        }

        if (true === (bool) Configuration::get('PRO_BACKUP_FILE') || true === (bool) Configuration::get('PRO_BACKUP_DB')) {
            $extensions = [
                'bz2',
                'zip',
            ];

            $fileBackupTotal = [
                [
                    self::DIR_BACKUP_FILES,
                    'BackupFilesDownload',
                    'BackupFilesDelete',
                    $this->l('Filesystem'),
                ],
                [
                    self::DIR_BACKUP_DATABASE,
                    'BackupDatabaseDownload',
                    'BackupDatabaseDelete',
                    $this->l('Database'),
                ],
            ];

            $localBackup = [];
            foreach ($fileBackupTotal as $fileBackupSingle) {
                if ($handle = \opendir(_PS_MODULE_DIR_ . $this->name . $fileBackupSingle[0])) {
                    while (false !== ($entry = \readdir($handle))) {
                        if ('.' !== $entry && '..' !== $entry) {
                            if (\in_array(\pathinfo(\basename($entry), \PATHINFO_EXTENSION), $extensions, true)) {
                                $lastModified = Tools::displayDate(\date('Y-m-d H:i:s', (int) Tools::substr(\basename($entry), 0, 10)));
                                $pathToFile = \realpath(_PS_MODULE_DIR_ . $this->name . $fileBackupSingle[0] . $entry);
                                $localBackup[] = [
                                    $this->l('Type') => $fileBackupSingle[3],
                                    $this->l('Size') => Tools::formatBytes(\filesize($pathToFile), 1) . 'B',
                                    $this->l('Date') => $lastModified,
                                    $this->l('Path') => $pathToFile,
                                    null => '<span class="securitypro-position"><a class="btn btn-default" onclick="return confirm(\'' . $this->l('Are you sure, you want to delete') . ' ' . $entry . '?\')" href="' . $this->currentAdminIndex() . '&' . $fileBackupSingle[2] . '=1&file=' . $entry . '"><i class="icon icon-trash-o"></i> ' . $this->l('Delete') . '</a><span class="securitypro-divider"></span><a class="btn btn-default" href="' . $this->currentAdminIndex() . '&' . $fileBackupSingle[1] . '=1&file=' . $entry . '"><i class="icon icon-download"></i> ' . $this->l('Download') . '</a></span>',
                                ];
                            }
                        }
                    }
                }
            }
            $keys = \array_column($localBackup, $this->l('Date'));
            \array_multisort($keys, \SORT_DESC, $localBackup);
        }
        $enabled = '<i class="icon icon-check" style="color: ' . self::COLOR_GREEN . '"></i>';
        $disabled = '<i class="icon icon-times" style="color: ' . self::COLOR_RED . '"></i>';

        // Logs
        $logTotal = [
            [
                $this->l('Brute force'),
                self::LOG_BRUTE_FORCE,
                'BruteForce',
                (true === (bool) Configuration::get('PRO_FAIL2BAN_LOG')) ? $enabled : $disabled,
            ],
            [
                $this->l('Firewall'),
                self::LOG_FIREWALL,
                'Firewall',
                (true === (bool) Configuration::get('PRO_FIREWALL_LOG')) ? $enabled : $disabled,
            ],
            [
                $this->l('Page not found'),
                self::LOG_PAGE_NOT_FOUND,
                'PageNotFound',
                (true === (bool) Configuration::get('PRO_PAGE_NOT_FOUND_LOG')) ? $enabled : $disabled,
            ],
            [
                $this->l('Malware scan'),
                self::LOG_MALWARE_SCAN,
                'MalwareScan',
                (true === (bool) Configuration::get('PRO_MALWARE_SCAN_LOG')) ? $enabled : $disabled,
            ],
            [
                $this->l('File changes'),
                self::LOG_FILE_CHANGES,
                'FileChanges',
                (true === (bool) Configuration::get('PRO_FILE_CHANGES_LOG')) ? $enabled : $disabled,
            ],
            [
                $this->l('Login attempts'),
                self::LOG_LOGIN_ATTEMPTS,
                'LoginAttempts',
                (true === (bool) Configuration::get('PRO_LOGIN_ATTEMPTS_LOG')) ? $enabled : $disabled,
            ],
            [
                $this->l('Cronjobs'),
                self::LOG_CRONJOB,
                'Cronjob',
                (false === empty($cronJobs)) ? $enabled : $disabled,
            ],
        ];

        $logInfo = [];
        foreach ($logTotal as $logSingle) {
            $lastModified = Tools::displayDate(\date('Y-m-d H:i:s', \filemtime($this->getLogFile($logSingle[1]))));

            $logInfo[] = [
                $this->l('Title') => $logSingle[0],
                $this->l('Size') => Tools::formatBytes(\filesize($this->getLogFile($logSingle[1])), 1) . 'B',
                $this->l('Last modified') => '<span style="white-space:nowrap;">' . $lastModified . '</span>',
                $this->l('Path') => \realpath($this->getLogFile($logSingle[1])),
                $this->l('Activated') => $logSingle[3],
                null => '<span class="securitypro-position">' .
                    $this->btnAjax('clearLog' . $logSingle[2], '<i class="icon icon-eraser"></i> ' . $this->l('Clear'), $this->l('Are you sure you want to clear') . ' ' . $logSingle[1])
                    . '<span class="securitypro-divider"></span>' .
                    $this->btnAjax('downloadLog' . $logSingle[2], '<i class="icon icon-download"></i> ' . $this->l('Download'), 0, 'downloadLog' . $logSingle[2])
                    . '</span>',
            ];
        }

        $antiFloodPath = _PS_CACHE_DIR_ . $this->name . self::DIR_ANTI_FLOOD;
        $cachePath = _PS_CACHE_DIR_ . $this->name;
        $cache = [];
        $cache[] = [
            $this->l('Title') => $this->l('Cache'),
            $this->l('Size') => Tools::formatBytes($this->getDirectorySize([$antiFloodPath, $cachePath]), 1) . 'B',
            $this->l('Description') => $this->l('Clear cache and statistics generated by this module.'),
            null => '<span class="securitypro-position">' . $this->btnAjax('clearCache', '<i class="icon icon-eraser"></i> ' . $this->l('Clear Cache')) . '</span>',
        ];

        $employeeData = $this->getEmployees(false);

        $employee = [];
        foreach ($employeeData as $data) {
            $inLastPasswordGen = $data['last_passwd_gen'];
            if (false === empty($inLastPasswordGen) && true === Validate::isDate($inLastPasswordGen)) {
                $outLastPasswordGen = Tools::displayDate(\date('Y-m-d H:i:s', \strtotime($inLastPasswordGen)), null, true);
            } else {
                $outLastPasswordGen = '--';
            }

            $inLastConnectionDate = $data['last_connection_date'];
            if (false === empty($inLastConnectionDate) && true === Validate::isDate($inLastConnectionDate)) {
                $outLastConnectionDate = Tools::displayDate(\date('Y-m-d H:i:s', \strtotime($inLastConnectionDate)));
            } else {
                $outLastConnectionDate = '--';
            }

            $employee[] = [
                $this->l('Name') => \htmlspecialchars($data['firstname']) . ' ' . \htmlspecialchars($data['lastname']),
                $this->l('E-mail') => \htmlspecialchars($data['email']),
                $this->l('Last password generated') => $outLastPasswordGen,
                $this->l('Last connection') => $outLastConnectionDate,
                $this->l('Activated') => (bool) $data['active'] ? $enabled : $disabled,
                null => '<span class="securitypro-position">' . $this->generateBtnLink('<i class="icon icon-pencil"></i> ' . $this->l('Edit'), $this->getEmployeeAdminLink((int) $data['id_employee'])) . '</span>',
            ];
        }

        $result = [];

        $result[] = $this->addHeading($this->l('Logs'), true) . $this->arrayToTable($logInfo);

        $result[] = $this->addHeading($this->l('Employee statistics')) . $this->arrayToTable($employee);

        $outCron = [];
        if (false === empty($cronJobs)) {
            $maintenanceLink = $this->adminLink('AdminMaintenance');

            $outCron[] = $this->addHeading($this->l('Cronjobs'));

            $outCron[] = $this->addAlertInfo($this->l('Please set up below cronjobs. It is recommended to run the cronjobs once a day') . ': <kbd>' . \htmlentities('0 3 * * * {' . $this->l('cronjob') . '}') . '</kbd><br>' . $this->l('If your host does not allow you to set up cronjobs, you can use this service instead') . ': ' . $this->generateLink('https://cron-job.org/en/members/jobs/add/') . '<br>' . $this->l('Learn more about cronjobs') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Cron', $this->l('here')) . '.');

            if (false === (bool) Configuration::get('PS_SHOP_ENABLE')) {
                $outCron[] = $this->addAlertWarning($this->l('Information: By default, you cannot run cronjobs while your shop is in maintenance mode. To do so, you must whitelist your IP address at the maintenance IP whitelist') . ' ' . $this->generateLink($maintenanceLink, $this->l('here')) . '.');
            }

            $outCron[] = $this->arrayToTable($cronJobs);
        }

        $result[] = \implode('', $outCron);

        if (false === empty($localBackup)) {
            $result[] = $this->addHeading($this->l('Local backups')) . $this->arrayToTable($localBackup);
        }

        if (false === empty($dropboxBackup)) {
            $result[] = $this->addHeading($this->l('Dropbox backups')) . $this->arrayToTable($dropboxBackup);
        }

        if (false === empty($googleDriveBackup)) {
            $result[] = $this->addHeading($this->l('Google Drive backups')) . $this->arrayToTable($googleDriveBackup);
        }

        $result[] = $this->addHeading($this->l('Cache')) . $this->arrayToTable($cache);

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Dashboard'),
                    'icon' => 'icon-dashboard',
                ],
                'input' => [
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => \implode('<br>', $result),
                        'col' => 12,
                        'name' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @throws PrestaShopException
     * @throws \Google\Exception
     *
     * @return array
     */
    protected function fieldsFormGeneralSettings()
    {
        $clientToken = [
            $this->l('Access') . ' ' . $this->generateLink('https://www.dropbox.com/developers/apps/create') . ' ' . $this->l('from your browser.'), // TODO: translate
            $this->l('Log on to your Dropbox account. Sign up if you do not have one yet.'),
            $this->l('Choose \'Scoped access\' API on the first step.'),
            $this->l('Choose \'App folder\' in the second step.'),
            $this->l('Give your app a name. That name will become a folder in your Dropbox account.'),
            $this->l('Click the \'Create app\' button.'),
            $this->l('Go to the \'Permissions\' tab. Scroll down to \'Files and folders\' and enable following: \'files.metadata.write\', \'files.content.write\', \'files.content.read\'. Then submit the changes.'),
            $this->l('Go to the \'Settings\' tab. Scroll down to the \'OAuth 2\' block and select \'No expiration\' from the dropdown near the \'Access token expiration\' text.'),
            $this->l('Then generate a token by hitting the \'Generate\' button near the \'Generated access token\' text.'),
            $this->l('After the token is generated, you\'ll see a string of letters and numbers. This is your Dropbox API access token. You should now copy this token into the field above.'),
        ];

        $googleApiV2 = [
            $this->l('Access') . ' ' . $this->generateLink('https://www.google.com/recaptcha/admin/create') . ' ' . $this->l('from your browser.'),
            $this->l('Log on to your Google account. Sign up if you do not have one yet.'),
            $this->l('Select the reCAPTCHA v2 radio button.'),
            $this->l('Register your domain.'),
            $this->l('Copy your Site key and your Secret key into the fields above.'),
        ];

        $googleApiV3 = [
            $this->l('Access') . ' ' . $this->generateLink('https://www.google.com/recaptcha/admin/create') . ' ' . $this->l('from your browser.'),
            $this->l('Log on to your Google account. Sign up if you do not have one yet.'),
            $this->l('Select the reCAPTCHA v3 radio button.'),
            $this->l('Register your domain.'),
            $this->l('Copy your Site key and your Secret key into the fields above.'),
        ];

        $googleSafeBrowsingApiV4 = [
            $this->l('Access') . ' ' . $this->generateLink('https://console.developers.google.com/apis/library/safebrowsing.googleapis.com') . ' ' . $this->l('from your browser.'),
            $this->l('Log on to your Google account. Sign up if you do not have one yet.'),
            $this->l('Enable Safe Browsing API.'),
            $this->l('Select a project or create a new one.'),
            $this->l('Click Credentials.'),
            $this->l('Click Create credentials.'),
            $this->l('Copy your API key into the field above.'),
        ];

        $honeypotApi = [
            $this->l('Access') . ' ' . $this->generateLink('https://www.projecthoneypot.org/account_login.php') . ' ' . $this->l('from your browser.'),
            $this->l('Log on to your Honeypot Project account. Sign up if you do not have one yet.'),
            $this->l('Your API key is found on the top left of your Project Honey Pot Dashboard. It will be the first line under \'Your Stats\'.'),
            $this->l('Copy your Honeypot API key into the field above.'),
        ];

        $montasticApi = [
            $this->l('Access') . ' ' . $this->generateLink('https://montastic.com/me?tab=form_profile') . ' ' . $this->l('from your browser.'),
            $this->l('Log on to your Montastic account. Sign up if you do not have one yet.'),
            $this->l('Go to the Profile menu.'),
            $this->l('Click on \'Developer Information\''),
            $this->l('Copy your REST API key into the field above.'),
        ];

        $timeZoneLink = $this->adminLink('AdminLocalization');

        $timeZoneText = $this->l('As some of the modules in this module are based on time, you must choose the correct time zone. Your current chosen time zone is') . ' <strong>' . Configuration::get('PS_TIMEZONE') . '</strong>. ' . $this->generateLink($timeZoneLink, $this->l('Change the time zone.'));

        if (false === \function_exists('mail')) {
            $errorMessage = $this->l('PHP mail function is disabled on your system. You must enable the PHP mail function to use the e-mail notification features.');
        } else {
            $errorMessage = null;
        }

        if (false === (bool) Configuration::get('PRO_GOOGLE_DRIVE_PROJECT_ID')
            || false === (bool) Configuration::get('PRO_GOOGLE_DRIVE_CLIENT_ID')
            || false === (bool) Configuration::get('PRO_GOOGLE_DRIVE_CLIENT_SECRET')) {
            $googleDriveAuthDisabled = true;
            $googleDriveAuthLink = '(' . $this->l('the link is generated once the connection to Google Drive is established') . ')';
        } else {
            $googleDriveAuthDisabled = false;
            $googleDriveAuthLink = $this->generateLink($this->googleDriveGetAuthUrl());
        }

        $googleDriveGeneral = [
            $this->l('Access') . ' ' . $this->generateLink('https://console.cloud.google.com/apis/library/drive.googleapis.com') . ' ' . $this->l('from your browser.'),
            $this->l('Log on to your Google account. Sign up if you do not have one yet.'),
            $this->l('Enable Google Drive API.'),
            $this->l('Select a project or create a new one.'),
            $this->l('Copy your product ID into the field above.'),
            $this->l('Access') . ' ' . $this->generateLink('https://console.cloud.google.com/apis/credentials/consent') . ' ' . $this->l('from your browser.'),
            $this->l('Click \'Configure Consent Screen\'.'),
            $this->l('Select \'External\' and click Create.'),
            $this->l('Enter \'Security Pro\' in the App name.'),
            $this->l('Enter your e-mail in \'User Support email\'.'),
            $this->l('Scroll down to developer contact information and enter the same e-mail address that you used above.'),
            $this->l('Once the app is created, click \'Set in production\'.'),
            $this->l('Click \'Save and Continue\' until you have finished all the steps.'),
            $this->l('Access') . ' ' . $this->generateLink('https://console.developers.google.com/apis/credentials/oauthclient') . ' ' . $this->l('from your browser.'),
            $this->l('Click \'Create credentials\' and select \'OAuth client ID\' from the list.'),
            $this->l('Select \'Desktop app\' from the dropdown.'),
            $this->l('Enter \'Security Pro\' as name and continue by hitting \'create\'.'),
            $this->l('Copy your client ID and your secret ID into the fields above.'),
            $this->l('Save module settings by hitting \'Save\'.'),
        ];

        $googleDriveClientAuth = [
            $this->l('Access') . ' ' . $googleDriveAuthLink . ' ' . $this->l('from your browser.'),
            $this->l('Select your Google account from the list.'),
            $this->l('Now a warning will appear. Click on advanced settings and click on the continue link.'),
            $this->l('Grant permission to the app.'),
            $this->l('Now a code for your application should be displayed. Copy that code into \'Client Auth\' above.'),
        ];

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('General Settings'),
                    'icon' => 'icon-cog',
                ],
                'description' => $this->l('Some features in this module use external free services (no paid subscription is required for any services). To use these features, you must get an API key/token from the services.') . '<br>' . $timeZoneText,
                'warning' => $this->l('Please save the following link to a safe place') . ': ' . $this->generateUnlockLink() . '<br>' . $this->l('Running this link will disable brute force protection, two-factor authentication and admin stealth login. This link can be useful if you get locked out from your back office.'),
                'error' => $errorMessage,
                'input' => [
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('Google Drive'), true),
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'col' => 8,
                        'type' => 'text',
                        'name' => 'PRO_GOOGLE_DRIVE_PROJECT_ID',
                        'label' => $this->l('Project ID'),
                        'prefix' => '<i class="icon-key"></i>',
                        'hint' => $this->l('Project ID'),
                    ],
                    [
                        'col' => 8,
                        'type' => 'text',
                        'name' => 'PRO_GOOGLE_DRIVE_CLIENT_ID',
                        'label' => $this->l('Client ID'),
                        'prefix' => '<i class="icon-key"></i>',
                        'hint' => $this->l('Client ID'),
                    ],
                    [
                        'col' => 8,
                        'type' => 'text',
                        'name' => 'PRO_GOOGLE_DRIVE_CLIENT_SECRET',
                        'label' => $this->l('Client secret'),
                        'prefix' => '<i class="icon-key"></i>',
                        'hint' => $this->l('Client secret'),
                        'desc' => '</p><ol class="help-block"><li>' . \implode('</li><li>', $googleDriveGeneral) . '</li></ol><p>',
                    ],
                    [
                        'col' => 8,
                        'type' => 'text',
                        'name' => 'PRO_GOOGLE_DRIVE_AUTH',
                        'label' => $this->l('Client Auth'),
                        'prefix' => '<i class="icon-key"></i>',
                        'hint' => $this->l('Client Auth'),
                        'desc' => '</p><ol class="help-block" start="20"><li>' . \implode('</li><li>', $googleDriveClientAuth) . '</li></ol><p>',
                        'disabled' => $googleDriveAuthDisabled,
                    ],
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('Dropbox')),
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'col' => 8,
                        'type' => 'text',
                        'desc' => '</p><ol class="help-block"><li>' . \implode('</li><li>', $clientToken) . '</li></ol><p>',
                        'name' => 'PRO_BACKUP_DB_TOKEN',
                        'label' => $this->l('Dropbox access token'),
                        'prefix' => '<i class="icon-dropbox"></i>',
                        'hint' => $this->l('Your Dropbox token'),
                    ],
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('Google reCAPTCHA')),
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'col' => 8,
                        'type' => 'text',
                        'prefix' => '<i class="icon-key"></i>',
                        'name' => 'PRO_FIREWALL_RECAPTCHA_SITE_KEY',
                        'label' => 'Site key (reCAPTCHA v2)',
                        'hint' => $this->l('Your reCAPTCHA v2 site key'),
                    ],
                    [
                        'col' => 8,
                        'type' => 'text',
                        'prefix' => '<i class="icon-key"></i>',
                        'desc' => '</p><ol class="help-block"><li>' . \implode('</li><li>', $googleApiV2) . '</li></ol><p>',
                        'name' => 'PRO_FIREWALL_RECAPTCHA_SECRET',
                        'label' => 'Secret key (reCAPTCHA v2)',
                        'hint' => $this->l('Your reCAPTCHA v2 secret key'),
                    ],
                    [
                        'col' => 8,
                        'type' => 'text',
                        'prefix' => '<i class="icon-key"></i>',
                        'name' => 'PRO_RECAPTCHA_V3_SITE_KEY',
                        'label' => 'Site key (reCAPTCHA v3)',
                        'hint' => $this->l('Your reCAPTCHA v3 site key'),
                    ],
                    [
                        'col' => 8,
                        'type' => 'text',
                        'prefix' => '<i class="icon-key"></i>',
                        'desc' => '</p><ol class="help-block"><li>' . \implode('</li><li>', $googleApiV3) . '</li></ol><p>',
                        'name' => 'PRO_RECAPTCHA_V3_SECRET',
                        'label' => 'Secret key (reCAPTCHA v3)',
                        'hint' => $this->l('Your reCAPTCHA v3 secret key'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Display') . ' (reCAPTCHA v3)',
                        'desc' => $this->l('Choose where to show the badge.'),
                        'name' => 'PRO_DISPLAY_RECAPTCHA_V3',
                        'options' => [
                            'query' => [
                                [
                                    'id_option' => 'bottomleft',
                                    'name' => $this->l('Bottom left'),
                                ],
                                [
                                    'id_option' => 'bottomright',
                                    'name' => $this->l('Bottom right'),
                                ],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Theme') . ' (reCAPTCHA v3)',
                        'desc' => $this->l('Choose the color theme of the badge.'),
                        'name' => 'PRO_RECAPTCHA_V3_THEME',
                        'options' => [
                            'query' => [
                                [
                                    'id_option' => 'light',
                                    'name' => $this->l('Light'),
                                ],
                                [
                                    'id_option' => 'dark',
                                    'name' => $this->l('Dark'),
                                ],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('Google Safe Browsing')),
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'col' => 8,
                        'type' => 'text',
                        'prefix' => '<i class="icon-key"></i>',
                        'desc' => '</p><ol class="help-block"><li>' . \implode('</li><li>', $googleSafeBrowsingApiV4) . '</li></ol><p>',
                        'name' => 'PRO_GOOGLE_SAFE_BROWSING_V4_API',
                        'label' => 'Google Safe Browsing v4',
                        'hint' => $this->l('Your Google Safe Browsing v4 API key'),
                    ],
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('Honeypot')),
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'col' => 8,
                        'type' => 'text',
                        'prefix' => '<i class="icon-key"></i>',
                        'desc' => '</p><ol class="help-block"><li>' . \implode('</li><li>', $honeypotApi) . '</li></ol><p>',
                        'name' => 'PRO_HONEYPOT_API',
                        'label' => $this->l('Honeypot API'),
                        'hint' => $this->l('Access keys are 12-alpha characters (no numbers). They are lower-case.'),
                    ],
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('Montastic')),
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'col' => 8,
                        'type' => 'text',
                        'prefix' => '<i class="icon-key"></i>',
                        'desc' => '</p><ol class="help-block"><li>' . \implode('</li><li>', $montasticApi) . '</li></ol><p>',
                        'name' => 'PRO_MONTASTIC_API',
                        'label' => 'Montastic API',
                        'hint' => $this->l('Access keys are 40 characters.'),
                    ],
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('General'), true),
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'col' => 8,
                        'type' => 'text',
                        'desc' => $this->l('You can enable e-mail notifications on some of the features. To use these features, you must enter your e-mail in the above field.') . ' ' . $this->l('You can add multiple e-mails.') . ' ' . $this->l('Separate the e-mail addresses by a comma') . ' (\',\') ' . $this->l('without space.'),
                        'name' => 'PRO_GENERAL_EMAIL',
                        'prefix' => '<i class="icon-envelope-o"></i>',
                        'label' => $this->l('E-mail'),
                        'hint' => $this->l('Must be a valid e-mail address'),
                        'required' => true,
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Debug cronjobs'),
                        'name' => 'PRO_DEBUG_CRON',
                        'is_bool' => true,
                        'desc' => $this->l('If one of your cronjobs fails, you can enable this option to find the problem. Run your cronjob manually in your browser to see the error.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormBruteForceProtection()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Admin Brute Force Protection'),
                    'icon' => 'icon-lock',
                ],
                'description' => $this->l('A brute force attack is one of the simplest methods to gain access to a website. The hacker tries various combinations of usernames and passwords again and again until he gets in. The module can limit the tries to protect you from the attack.') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Brute-force_attack', $this->l('Read more')) . '.',
                'input' => [
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('Brute force protection'), true),
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'col' => 8,
                        'type' => 'switch',
                        'label' => $this->l('Brute force protection'),
                        'name' => 'PRO_FAIL2BAN',
                        'is_bool' => true,
                        'desc' => $this->l('Enable brute force protection to limits the greatest amount of login tries to your back office.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 4,
                        'type' => 'text',
                        'desc' => $this->l('Wrong answers before the ban.') . ' ' . $this->l('The default value is') . ' 5.',
                        'name' => 'PRO_MAX_RETRY',
                        'prefix' => '<i class="icon-repeat"></i>',
                        'suffix' => $this->l('times'),
                        'label' => $this->l('Max retries'),
                        'hint' => $this->l('Must be an integer'),
                        'required' => true,
                    ],
                    [
                        'col' => 4,
                        'type' => 'text',
                        'desc' => $this->l('A host is banned if it has generated') . ' \'' . $this->l('Max retry') . '\' ' . $this->l('during the last') . ' \'' . $this->l('Request timeout') . '\'. ' . $this->l('Enter time in minutes') . '. ' . $this->l('The default value is') . ' 10.',
                        'name' => 'PRO_FIND_TIME',
                        'prefix' => '<i class="icon-clock-o"></i>',
                        'suffix' => $this->l('minutes'),
                        'label' => $this->l('Request timeout'),
                        'hint' => $this->l('Must be an integer'),
                        'required' => true,
                    ],
                    [
                        'col' => 4,
                        'type' => 'text',
                        'desc' => $this->l('Time a host is banned. Enter time in minutes.') . ' ' . $this->l('The default value is') . ' 30.',
                        'name' => 'PRO_BAN_TIME',
                        'prefix' => '<i class="icon-clock-o"></i>',
                        'suffix' => $this->l('minutes'),
                        'label' => $this->l('Ban time'),
                        'hint' => $this->l('Must be an integer'),
                        'required' => true,
                    ],
                    [
                        'type' => 'textbutton',
                        'col' => 8,
                        'desc' => $this->l('You can list your IP addresses to avoid getting an e-mail if you write the password wrong. You can still get banned for some time if you fail to login according to your own rules above.') . '<br>' . $this->l('The module can handle IPv4 and IPv6 addresses and IP ranges, in CIDR formats like') . ' <code>::1/128</code> ' . $this->l('or') . ' <code>127.0.0.1/32</code> ' . $this->l('and pattern format like') . ' <code>::*:*</code> ' . $this->l('or') . ' <code>127.0.*.*</code>. ' . $this->l('Separates by a comma') . ' (\',\') ' . $this->l('without space.'),
                        'name' => 'PRO_WHITELIST_IPS',
                        'button' => [
                            'label' => '<i class="icon-plus"></i> ' . $this->l('Add my IP'),
                            'attributes' => [
                                'onclick' => 'addMyIp("#PRO_WHITELIST_IPS");',
                            ],
                        ],
                        'label' => $this->l('Whitelist IP addresses'),
                        'hint' => $this->l('E.g.') . ' 123.456.789,123.456.*,123.*,...',
                    ],
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('Monitoring')),
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Receive e-mail on failing to login'),
                        'name' => 'PRO_SEND_MAIL',
                        'is_bool' => true,
                        'desc' => $this->l('Receive an e-mail if someone inputs a wrong password. This setting can only be enabled if brute force protection is activated.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Receive e-mail on successful login'),
                        'name' => 'PRO_SEND_MAIL_LOGIN',
                        'is_bool' => true,
                        'desc' => $this->l('Receive an e-mail in case someone inputs the correct password. This feature is great to give you the information if anyone else got access. This setting can only be enabled if brute force protection is activated.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Log banned users'),
                        'name' => 'PRO_FAIL2BAN_LOG',
                        'is_bool' => true,
                        'desc' => $this->l('Record banned users into a log file.') . ' ' . $this->l('The log can be found on your dashboard.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Log admin login attempts'),
                        'name' => 'PRO_LOGIN_ATTEMPTS_LOG',
                        'is_bool' => true,
                        'desc' => $this->l('Log admin login attempts with IP address, timestamp, e-mail and information about whether the user was logged in.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Build forms.
     *
     * @throws \RobThree\Auth\TwoFactorAuthException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @return array
     */
    protected function fieldsFormTwoFactorAuth()
    {
        $tfa = new \RobThree\Auth\TwoFactorAuth(Configuration::get('PS_SHOP_NAME'), 6, 30, 'sha1');

        $twoFactorAuth = [
            $this->l('Download a 2FA app on your phone') . ': <strong>Google Authenticator</strong>, <strong>Microsoft Authenticator</strong>, ' . $this->l('or any app supporting the TOTP algorithm.'),
            $this->l('Open the app and scan the QR code below') . ':<br><img src="' . $tfa->getQRCodeImageAsDataUri('Admin', $this->twoFactorsecret()) . '" alt="" loading="lazy">',
            $this->l('If you for some reason cannot scan the QR-code, you can use this code for manual input instead') . ': <strong>' . Tools::substr(\chunk_split($this->twoFactorsecret(), 4, ' '), 0, -1) . '</strong>',
            $this->l('Insert the code you see on your phone in the code field below to verify that everything is working.'),
            $this->l('Save settings in the module before the code expires.'),
        ];

        $employees = $this->getEmployees();

        $tfaLinks = [];
        foreach ($employees as $employee) {
            $link = \htmlentities($this->adminLink('AdminLogin') . '&2fa=' . $this->encrypt($employee['id_employee'] . $employee['email'] . $employee['passwd']));
            $tfaLinks[] = [
                $this->l('Name') => \htmlspecialchars($employee['firstname']) . ' ' . \htmlspecialchars($employee['lastname']),
                $this->l('E-mail') => \htmlspecialchars($employee['email']),
                $this->l('Link') => '<kbd>' . $link . '</kbd> <a href="javascript:void(0)" onclick="copyToClipboard(\'' . $link . '\')"><i class="icon icon-clipboard"></i></a>',
            ];
        }

        $result = $this->addAlertInfo($this->l('If any of your employees need the ability to skip the two-factor authentication, they can use the links below. These links have an extra parameter in the login URL. When accessing this link, the two-factor authentication is skipped.')) . '<strong>' . $this->addAlertWarning($this->l('Important information for the webmaster:') . '</strong> ' . $this->l('The 2FA-token is linked to the e-mail and the password of the employee, so if the employee changes his e-mail or resets his login-password, the 2FA-token will change as well due to security reasons.')) . $this->arrayToTable($tfaLinks);

        $code = Tools::substr(Tools::strtoupper($this->encrypt('2fa-recovery')), 0, 12);

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Two-Factor Authentication'),
                    'icon' => 'icon-key',
                ],
                'description' => $this->l('Two-factor authentication is an extra layer of security for your PrestaShop admin panel, designed to make sure that you are the only person who can get access to your back office, even if someone knows your password.') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Multi-factor_authentication', $this->l('Read more')) . '.',
                'warning' => $this->l('Please write down and store this 12-character recovery code somewhere safe. In case you lose access to your device, you can use this code to pass the 2FA-step') . ': <kbd>' . Tools::substr(\chunk_split($code, 4, ' '), 0, -1) . '</kbd> <a href="javascript:void(0)" onclick="copyToClipboard(\'' . $code . '\')"><i class="icon icon-clipboard"></i></a>',
                'input' => [
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Two-Factor Authentication'),
                        'name' => 'PRO_TWO_FACTOR_AUTH',
                        'is_bool' => true,
                        'desc' => '</p><ol class="help-block"><li>' . \implode('</li><li>', $twoFactorAuth) . '</li></ol><p>',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon-key"></i>',
                        'desc' => $this->l('To confirm that everything is correct, you must enter your code from your app before you save settings.'),
                        'name' => 'PRO_TWO_FACTOR_AUTH_CODE',
                        'label' => $this->l('Code'),
                        'hint' => $this->l('Must be 6 digitals'),
                        'disabled' => $this->twoFactorAuthDb('enabled') ? true : false,
                        'required' => true,
                    ],
                    [
                        'type' => 'textbutton',
                        'col' => 8,
                        'desc' => $this->l('You can list your IP addresses if you want to skip the Two-Factor Authentication when you are on a specific network.') . '<br>' . $this->l('The module can handle IPv4 and IPv6 addresses and IP ranges, in CIDR formats like') . ' <code>::1/128</code> ' . $this->l('or') . ' <code>127.0.0.1/32</code> ' . $this->l('and pattern format like') . ' <code>::*:*</code> ' . $this->l('or') . ' <code>127.0.*.*</code>. ' . $this->l('Separates by a comma') . ' (\',\') ' . $this->l('without space.'),
                        'name' => 'PRO_TWO_FACTOR_AUTH_WHITELIST',
                        'button' => [
                            'label' => '<i class="icon-plus"></i> ' . $this->l('Add my IP'),
                            'attributes' => [
                                'onclick' => 'addMyIp("#PRO_TWO_FACTOR_AUTH_WHITELIST");',
                            ],
                        ],
                        'label' => $this->l('Whitelist IP addresses'),
                        'hint' => $this->l('E.g.') . ' 123.456.789,123.456.*,123.*,...',
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Force Two-Factor Authentication'),
                        'name' => 'PRO_TWO_FACTOR_AUTH_FORCE',
                        'is_bool' => true,
                        'desc' => $this->l('By default, if you sign in from the same network, same browser and same computer, and you have recently solved the 2FA you will not have to solve the 2FA again. However, if you want to force the second verification step every single time you sign in on your back office even though your device is trusted, you can enable this feature.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $result,
                        'col' => 12,
                        'name' => '',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormSecondLogin()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Second Login'),
                    'icon' => 'icon-sign-in',
                ],
                'description' => $this->l('PrestaShop\'s login already secures your shop, but you can add another layer of security by adding a second login from your web server itself. This is done using .htpasswd (Apache-servers only). The second login is the same for each employee, as this is set on the server level.'),
                'warning' => $this->l('This feature is for advanced users only. It is recommended to leave this feature off in most cases.'),
                'input' => [
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Second login'),
                        'name' => 'PRO_HTPASSWD',
                        'is_bool' => true,
                        'desc' => $this->l('Activate a second login from your web server itself.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon-user"></i>',
                        'desc' => $this->l('It would help if you used another username then you do for your regular back office login.') . ' <a onclick="generateHtpasswdUser()" href="javascript:void(0)">' . $this->l('Generate a secure username') . '</a>.',
                        'name' => 'PRO_HTPASSWD_USER',
                        'label' => $this->l('Username'),
                        'hint' => $this->l('Invalid character') . ': \':\'',
                    ],
                    [
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon-key"></i>',
                        'desc' => $this->l('It would help if you used another password than you do for your regular back office login.') . ' <a onclick="generateHtpasswdPass()" href="javascript:void(0)">' . $this->l('Generate a secure password') . '</a>.',
                        'name' => 'PRO_HTPASSWD_PASS',
                        'label' => $this->l('Password'),
                        'hint' => $this->l('Invalid character') . ': \':\'',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormAdminStealthLogin()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Admin Stealth Login'),
                    'icon' => 'icon-eye-slash',
                ],
                'description' => $this->l('Admin Stealth Login makes your admin directory invisible for hosts with unknown IP addresses.'),
                'warning' => $this->l('This feature is for advanced users only. It is recommended to leave this feature off in most cases.'),
                'input' => [
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Admin stealth login'),
                        'name' => 'PRO_STEALTH_LOGIN',
                        'is_bool' => true,
                        'desc' => $this->l('Block access to the back office for everyone except the IP addresses on the list below.') . ' <strong>' . $this->l('You must have a static IP address') . '.</strong> ' . $this->generateLink('https://en.wikipedia.org/wiki/IP_address#Static_IP', $this->l('Read more')) . '.',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 8,
                        'type' => 'textbutton',
                        'label' => $this->l('Whitelist'),
                        'hint' => $this->l('E.g.') . ' 123.456.789,123.456.*,123.*,...',
                        'desc' => $this->l('List all the IP addresses that should have access to back office.') . '<br>' . $this->l('The module can handle IPv4 and IPv6 addresses and IP ranges, in CIDR formats like') . ' <code>::1/128</code> ' . $this->l('or') . ' <code>127.0.0.1/32</code> ' . $this->l('and pattern format like') . ' <code>::*:*</code> ' . $this->l('or') . ' <code>127.0.*.*</code>. ' . $this->l('Separates by a comma') . ' (\',\') ' . $this->l('without space.'),
                        'name' => 'PRO_STEALTH_LOGIN_WHITELIST',
                        'button' => [
                            'label' => '<i class="icon-plus"></i> ' . $this->l('Add my IP'),
                            'attributes' => [
                                'onclick' => 'addMyIp("#PRO_STEALTH_LOGIN_WHITELIST");',
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormPasswordStrength()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Password Strength'),
                    'icon' => 'icon-tasks',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Password strength meter'),
                        'name' => 'PRO_PASSWORD_STRENGTHBAR',
                        'is_bool' => true,
                        'desc' => $this->l('Add a meter under the password field giving your customers instant feedback on the strength of their passwords, thus giving your customers a more secure shopping experience.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormHttpSecurityHeaders()
    {
        if (true === (bool) Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) {
            $disabledSsl = false;
        } else {
            $disabledSsl = true;
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('HTTP Security Headers'),
                    'icon' => 'icon-shield',
                ],
                'description' => $this->l('Security headers are HTTP response headers that your application can use to increase the security of your application. Once set, these HTTP response headers can restrict browsers from running into easily preventable vulnerabilities.') . ' ' . $this->l('This module makes the configuration of these security headers easy.'),
                'input' => [
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Click-jack protection'),
                        'name' => 'PRO_CLICK_JACKING',
                        'is_bool' => true,
                        'desc' => $this->l('Prevent browsers from framing your site. This will defend you against attacks like click-jacking.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('XSS protection'),
                        'name' => 'PRO_X_XSS_PROTECTION',
                        'is_bool' => true,
                        'desc' => $this->l('Set secure configuration for the cross-site scripting filters built into most browsers.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Disable content sniffing'),
                        'name' => 'PRO_X_CONTENT_TYPE_OPTIONS',
                        'is_bool' => false,
                        'desc' => $this->l('Stop browsers from trying to MIME-sniff the content type and forces it to stick with the declared content-type.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Force secure connection with HSTS'),
                        'name' => 'PRO_STRICT_TRANSPORT_SECURITY',
                        'is_bool' => true,
                        'desc' => $this->l('Strengthens your implementation of TLS by getting the user agent to enforce the use of HTTPS.'),
                        'disabled' => $disabledSsl,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'checkbox',
                        'desc' => $this->l('Please follow this link to understand these settings') . ': ' . $this->generateLink('https://hstspreload.org/?domain=' . $this->getShopUrl()) . '.',
                        'label' => $this->l('HSTS settings'),
                        'name' => 'PRO_HSTS_SETTINGS',
                        'values' => [
                            'query' => [
                                [
                                    'id_option' => 0,
                                    'name' => 'Preload',
                                    'value' => 0,
                                ],
                                [
                                    'id_option' => 1,
                                    'name' => 'Include subdomains',
                                    'value' => 1,
                                ],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                            'value' => 'value',
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Expect CT'),
                        'name' => 'PRO_EXPECT_CT',
                        'is_bool' => true,
                        'desc' => $this->l('Signals to the user agent that compliance with the certificate transparency policy should be enforced.'),
                        'disabled' => $disabledSsl,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Referrer policy'),
                        'name' => 'PRO_REFERRER_POLICY',
                        'is_bool' => true,
                        'desc' => $this->l('The browser will send a full URL along with requests from a TLS-protected environment settings object to a potentially trustworthy URL and requests from clients which are not TLS-protected to any origin.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Access control allows methods'),
                        'name' => 'PRO_ACCESS_CONTROL_ALLOW_METHODS',
                        'is_bool' => true,
                        'desc' => $this->l('The server responds and says that only POST, GET, OPTIONS are viable methods to query the resource in question.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Permitted cross-domain policies'),
                        'name' => 'PRO_X_PERMITTED_CROSS_DOMAIN_POLICY',
                        'is_bool' => true,
                        'desc' => $this->l('Prevent Adobe Flash and Adobe Acrobat from loading content on your site. This protects against cross-domain middleware.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Download options'),
                        'name' => 'PRO_X_DOWNLOAD_OPTIONS',
                        'is_bool' => true,
                        'desc' => $this->l('This disables the option to open a file directly on download. Internet Explorer only supports this header. Other browsers will ignore it.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Hide server information'),
                        'name' => 'PRO_UNSET_HEADERS',
                        'is_bool' => true,
                        'desc' => $this->l('Remove all') . ' \'Powered-by\' ' . $this->l('HTTP headers and hide server information.') .
                            '<br><br><br><a class="btn btn-default" style="font-style: normal; margin-bottom: 4px;" href="https://securityheaders.com/?q=' . $this->getShopUrl() .
                            '&amp;hide=on&amp;followRedirects=on" target="_blank" rel="noopener noreferrer nofollow">' . $this->l('Analyze security HTTP headers') .
                            '</a><br>' . \sprintf(
                                $this->l('Security Pro can fix all warnings and errors reported by %1$s, helping you get a %2$s score!'),
                                $this->generateLink('https://securityheaders.com'),
                                '<strong style="color: ' . self::COLOR_GREEN . ';">A+</strong> '
                            ),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @throws PrestaShopException
     *
     * @return array
     */
    protected function fieldsFormFirewall()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Web Application Firewall'),
                    'icon' => 'icon-repeat',
                ],
                'description' => $this->l('This web application firewall helps to protect your web applications against common web exploits that may affect availability, compromise security, or consume excessive resources. It makes your applications secure by enabling security rules that block common attack patterns, such as SQL injection, cross-site scripting, etc.') . ' ' . $this->l('Once you have configured the firewall, remember to test that everything normally works in your front office.') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Web_application_firewall', $this->l('Read more')) . '.',
                'input' => [
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('Firewall rules'), true),
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('DDoS protection'),
                        'name' => 'PRO_ANTI_FLOOD',
                        'is_bool' => true,
                        'desc' => $this->l('Anti-flood/DDoS protection. This feature is great for preventing most DDoS attacks and automatic multiple requests.') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Denial-of-service_attack', $this->l('Read more')) . '.',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 4,
                        'type' => 'text',
                        'suffix' => $this->l('requests'),
                        'prefix' => '<i class="icon-repeat"></i>',
                        'desc' => $this->l('Allowed page requests for the user.') . ' ' . $this->l('The default value is') . ' 100.',
                        'name' => 'PRO_ANTI_MAX_REQUESTS',
                        'label' => $this->l('Max requests'),
                        'hint' => $this->l('Must be an integer'),
                        'required' => true,
                    ],
                    [
                        'col' => 4,
                        'type' => 'text',
                        'suffix' => $this->l('seconds'),
                        'prefix' => '<i class="icon-clock-o"></i>',
                        'desc' => $this->l('Time interval to start counting page requests.') . ' ' . $this->l('The default value is') . ' 5.',
                        'name' => 'PRO_ANTI_REQ_TIMEOUT',
                        'label' => $this->l('Request timeout'),
                        'hint' => $this->l('Must be an integer'),
                        'required' => true,
                    ],
                    [
                        'col' => 4,
                        'type' => 'text',
                        'suffix' => $this->l('seconds'),
                        'prefix' => '<i class="icon-clock-o"></i>',
                        'desc' => $this->l('The duration of the ban.') . ' ' . $this->l('The default value is') . ' 600.',
                        'name' => 'PRO_ANTI_BAN_TIME',
                        'label' => $this->l('Ban time'),
                        'hint' => $this->l('Must be an integer'),
                        'required' => true,
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Challenge hosts listed in Honeypot Project'),
                        'name' => 'PRO_FIREWALL_CHECK_BOT',
                        'is_bool' => true,
                        'desc' => $this->l('The honeypot project has a big database of bad bots/spammers. If this feature is enabled, the module will look up the IP of clients accessing your site against this database. If there is a match, the client will need to solve a reCAPTCHA to continue using the website. Search engines are excluded from this check.') . ' ' . $this->generateLink('https://www.projecthoneypot.org/about_us.php', $this->l('Read more')) . '.',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Block TOR IPv4 and IPv6 addresses'),
                        'name' => 'PRO_BLOCK_TOR',
                        'is_bool' => true,
                        'desc' => $this->l('In some cases, TOR browsers are used by criminals to hide while buying from a stolen credit card. If you are having this problem, you can block TOR IPv4 and IPv6 addresses with this feature.') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Tor_(anonymity_network)', $this->l('Read more.')) . ' ' . $this->l('It is recommended to leave the feature off in most cases.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'col' => 8,
                        'label' => $this->l('Anti-SQL injection'),
                        'name' => 'PRO_FIREWALL_SQL_CHECK',
                        'desc' => $this->l('SQL injection is a web security vulnerability that allows an attacker to interfere with the queries that an application makes to its database.') . ' ' . $this->l('If the request looks like an attack, choose whether the client can proceed after solving a challenge (reCAPTCHA v2), get blocked (403) or get redirected to \'page not found\' (404).') . ' ' . $this->generateLink('https://owasp.org/www-community/attacks/SQL_Injection', $this->l('Read more')) . '.',
                        'options' => [
                            'query' => [
                                [
                                    'id_option' => 0,
                                    'name' => $this->l('Disabled'),
                                ],
                                [
                                    'id_option' => 1,
                                    'name' => $this->l('Block request (403)'),
                                ],
                                [
                                    'id_option' => 2,
                                    'name' => $this->l('Page-not-found (404)'),
                                ],
                                [
                                    'id_option' => 3,
                                    'name' => $this->l('Challenge (reCAPTCHA v2)'),
                                ],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'col' => 8,
                        'label' => $this->l('Anti XXS injection'),
                        'name' => 'PRO_FIREWALL_XXS_CHECK',
                        'desc' => $this->l('XSS (Cross-Site Scripting) injection is a web security vulnerability that allows an attacker to inject code (basically client-side scripting) to the remote server.') . ' ' . $this->l('If the request looks like an attack, choose whether the client can proceed after solving a challenge (reCAPTCHA v2), get blocked (403) or get redirected to \'page not found\' (404).') . ' ' . $this->generateLink('https://owasp.org/www-community/attacks/xss/', $this->l('Read more')) . '.',
                        'options' => [
                            'query' => [
                                [
                                    'id_option' => 0,
                                    'name' => $this->l('Disabled'),
                                ],
                                [
                                    'id_option' => 1,
                                    'name' => $this->l('Block request (403)'),
                                ],
                                [
                                    'id_option' => 2,
                                    'name' => $this->l('Page-not-found (404)'),
                                ],
                                [
                                    'id_option' => 3,
                                    'name' => $this->l('Challenge (reCAPTCHA v2)'),
                                ],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'col' => 8,
                        'label' => $this->l('Anti command injection'),
                        'name' => 'PRO_FIREWALL_SHELL_CHECK',
                        'desc' => $this->l('Command injection is a web security vulnerability that allows an attacker to inject code into the remote server.') . ' ' . $this->l('If the request looks like an attack, choose whether the client can proceed after solving a challenge (reCAPTCHA v2), get blocked (403) or get redirected to \'page not found\' (404).') . ' ' . $this->generateLink('https://owasp.org/www-community/attacks/Command_Injection', $this->l('Read more')) . '.',
                        'options' => [
                            'query' => [
                                [
                                    'id_option' => 0,
                                    'name' => $this->l('Disabled'),
                                ],
                                [
                                    'id_option' => 1,
                                    'name' => $this->l('Block request (403)'),
                                ],
                                [
                                    'id_option' => 2,
                                    'name' => $this->l('Page-not-found (404)'),
                                ],
                                [
                                    'id_option' => 3,
                                    'name' => $this->l('Challenge (reCAPTCHA v2)'),
                                ],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('RFI protection'),
                        'name' => 'PRO_FIREWALL_RFI_CHECK',
                        'is_bool' => true,
                        'desc' => $this->l('Remote file inclusion (RFI) is an attack targeting vulnerabilities in web applications that dynamically reference external scripts. Block the request if the request looks like an RFI attack.') . ' ' . $this->l('This feature is for advanced users. Watch the firewall log if you enable this feature, in case you have installed a third-party module that gets blocked by this feature due to the design of the request.') . ' ' . $this->generateLink('https://owasp.org/www-community/vulnerabilities/PHP_File_Inclusion', $this->l('Read more')) . '.',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('XST protection'),
                        'name' => 'PRO_FIREWALL_XST_CHECK',
                        'is_bool' => true,
                        'desc' => $this->l('Cross-Site Tracing (XST) is a network security vulnerability exploiting the HTTP TRACE method. Enable this option to block HTTP TRACK and HTTP TRACE requests.') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Cross-site_tracing', $this->l('Read more')) . '.',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Block directory traversal'),
                        'name' => 'PRO_DIR_TRAVERSAL',
                        'is_bool' => true,
                        'desc' => $this->l('Directory traversal attacks use the webserver software to exploit inadequate security mechanisms and access directories and files stored outside of the webroot folder. This option protects against traversal attacks.') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Directory_traversal_attack', $this->l('Read more')) . '.',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Block file-upload'),
                        'name' => 'PRO_BLOCK_FILE_UPLOAD',
                        'is_bool' => true,
                        'desc' => $this->l('Block the ability to upload files in the front office. Do not enable this if you are using the contact form or another front office module with a file transfer function.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Scan file on upload'),
                        'name' => 'PRO_BLOCK_SCAN_FILE_UPLOAD',
                        'is_bool' => true,
                        'desc' => $this->l('Scan uploaded files in the front office for trojans, viruses, malware and, other threats and block the request if the file is suspicious.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'textbutton',
                        'col' => 8,
                        'desc' => $this->l('Whitelisted IP addresses that should not be should be blocked by the firewall.') . '<br>' . $this->l('The module can handle IPv4 and IPv6 addresses and IP ranges, in CIDR formats like') . ' <code>::1/128</code> ' . $this->l('or') . ' <code>127.0.0.1/32</code> ' . $this->l('and pattern format like') . ' <code>::*:*</code> ' . $this->l('or') . ' <code>127.0.*.*</code>. ' . $this->l('Separates by a comma') . ' (\',\') ' . $this->l('without space.'),
                        'name' => 'PRO_FIREWALL_WHITELIST',
                        'button' => [
                            'label' => '<i class="icon-plus"></i> ' . $this->l('Add my IP'),
                            'attributes' => [
                                'onclick' => 'addMyIp("#PRO_FIREWALL_WHITELIST");',
                            ],
                        ],
                        'label' => $this->l('Whitelist IP addresses'),
                        'hint' => $this->l('E.g.') . ' 123.456.789,123.456.*,123.*,...',
                    ],
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('Custom rules')),
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'type' => 'select',
                        'col' => 8,
                        'label' => $this->l('Block custom list of IP addresses'),
                        'name' => 'PRO_BAN_IP_ACTIVATE',
                        'desc' => $this->l('Block hosts with below IP addresses from your website. You cannot block hosts that are already on this') . ' ' . $this->generateLink($this->adminLink('AdminGeolocation'), $this->l('whitelist')) . '. ' . $this->l('If you want to ban a country, please use this built-in PrestaShop feature') . ': ' . $this->generateLink($this->adminLink('AdminGeolocation'), $this->l('Ban countries')) . '. ' . $this->l('It is generally not recommended to block countries. Blocking countries could lockout customers that are using a VPN or customers that are on vacation, etc.') . ' ' . $this->l('If the client is on the blacklist, choose whether the client can proceed after solving a challenge (reCAPTCHA v2) or get blocked (403).'),
                        'options' => [
                            'query' => [
                                [
                                    'id_option' => 0,
                                    'name' => $this->l('Disabled'),
                                ],
                                [
                                    'id_option' => 1,
                                    'name' => $this->l('Block request (403)'),
                                ],
                                [
                                    'id_option' => 3,
                                    'name' => $this->l('Challenge (reCAPTCHA v2)'),
                                ],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'col' => 8,
                        'desc' => $this->l('The module can handle IPv4 and IPv6 addresses and IP ranges, in CIDR formats like') . ' <code>::1/128</code> ' . $this->l('or') . ' <code>127.0.0.1/32</code> ' . $this->l('and pattern format like') . ' <code>::*:*</code> ' . $this->l('or') . ' <code>127.0.*.*</code>. ' . $this->l('Separates by a comma') . ' (\',\') ' . $this->l('without space.'),
                        'name' => 'PRO_BAN_IP',
                        'label' => $this->l('Custom list of IP addresses'),
                        'hint' => $this->l('E.g.') . ' 123.456.789,123.456.*,123.*,...',
                    ],
                    [
                        'type' => 'select',
                        'col' => 8,
                        'label' => $this->l('Block custom list of user agents'),
                        'name' => 'PRO_BLOCK_USER_AGENT_ACTIVATE',
                        'is_bool' => true,
                        'desc' => $this->l('Block user agents with the below names from your website.'),
                        'options' => [
                            'query' => [
                                [
                                    'id_option' => 0,
                                    'name' => $this->l('Disabled'),
                                ],
                                [
                                    'id_option' => 1,
                                    'name' => $this->l('Block request (403)'),
                                ],
                                [
                                    'id_option' => 3,
                                    'name' => $this->l('Challenge (reCAPTCHA v2)'),
                                ],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'col' => 8,
                        'desc' => $this->l('Separates by a comma') . ' (\',\') ' . $this->l('without space.'),
                        'name' => 'PRO_BLOCK_USER_AGENT',
                        'label' => $this->l('Custom list of User agents'),
                        'hint' => $this->l('E.g.') . ' 360Spider,Alexibot,BackWeb,...',
                    ],
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('Monitoring')),
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Log hacking attempts'),
                        'name' => 'PRO_FIREWALL_LOG',
                        'is_bool' => true,
                        'desc' => $this->l('Record hacking attempts into a log file.') . ' ' . $this->l('This is recommended.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormAntiSpam()
    {
        $linkRegistrationForm = $this->context->link->getPageLink('authentication') . '?create_account=1';
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0')) {
            $disabled = true;
            $warning = $this->l('It is not possible to extend the contact form checks in PrestaShop 1.6 without using overrides. As this module is not allowed to use overrides, some options are not available. These options are available for PrestaShop 1.7 as PrestaShop 1.7 allows the module to hook these checks without using overrides.');
        } else { // PS 1.7
            $disabled = false;
            $warning = null;
        }

        if (Module::isEnabled('onepagecheckoutps')) {
            $warningOPC = $this->addAlertWarning($this->l('It is not possible to protect the registration form when the OPC module is installed, as the OPC module overrides the native registration form.'));
        } else {
            $warningOPC = null;
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Anti-SPAM'),
                    'icon' => 'icon-ban',
                ],
                'description' => $this->l('SPAM (Shit Posing As Mail) is a problem for most businesses. There are still people who fall victim to cyber-attacks, such as') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Spamming', $this->l('spamming')) . ' ' . $this->l('and') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Phishing', $this->l('phishing')) . '.',
                'warning' => $warning,
                'input' => [
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('Contact form'), true),
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Disable contact form'),
                        'name' => 'PRO_DISABLE_CONTACT_FORM',
                        'is_bool' => true,
                        'desc' => $this->l('If you want to disable the contact form, you can enable this feature.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Enable') . ' reCAPTCHA v3',
                        'name' => 'PRO_RECAPTCHA_V3_CONTACT_ACTIVATE',
                        'is_bool' => true,
                        'desc' => $this->l('reCAPTCHA v3 returns a risk score for each request without user friction. The module uses this risk-score to decide whether the user is a bot or a human. Bots will be prevented from sending e-mails.') . ' ' . $this->generateLink('https://www.google.com/recaptcha/about/', $this->l('Read more')) . '.',
                        'disabled' => $disabled,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Block vulnerable messages'),
                        'name' => 'PRO_GOOGLE_SAFE_BROWSING_V4_ACTIVATE',
                        'is_bool' => true,
                        'desc' => $this->l('Prevent users from sending e-mails with links to known phishing and deceptive sites using Google safe browsing API. The safe browsing API automatically checks the URLs in the message against Google\'s constantly updated lists of unsafe web resources. If any URL in the message is found on the safe browsing list, the message will be not be sent.') . ' ' . $this->generateLink('https://developers.google.com/safe-browsing', $this->l('Read more')) . '.',
                        'disabled' => $disabled,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Block custom words'),
                        'name' => 'PRO_MESSAGE_CHECKER_ACTIVATE',
                        'is_bool' => true,
                        'desc' => $this->l('Block message if it contains at least one word from your custom list of blacklisted words.'),
                        'disabled' => $disabled,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'col' => 8,
                        'desc' => $this->l('Custom list of bad words') . ' ' . $this->l('Separates by a comma') . ' (\',\') ' . $this->l('without space.'),
                        'name' => 'PRO_MESSAGE_CHECKER_CUSTOM_LIST',
                        'label' => $this->l('Blacklisted words'),
                        'hint' => $this->l('E.g.') . ' viagra,cialis,poker,casino',
                        'disabled' => $disabled,
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Block disposable e-mails'),
                        'name' => 'PRO_DISPOSABLE_EMAIL_PROVIDERS_ACTIVATE',
                        'is_bool' => true,
                        'desc' => $this->l('Block e-mails from disposable providers.'),
                        'disabled' => $disabled,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Block custom list of TLD\'s'),
                        'name' => 'PRO_EMAIL_CHECKER_ACTIVATE',
                        'is_bool' => true,
                        'desc' => $this->l('Block e-mails with specific top-level domains.'),
                        'disabled' => $disabled,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'col' => 8,
                        'name' => 'PRO_EMAIL_CHECKER_CUSTOM_LIST',
                        'label' => $this->l('Custom list of TLD\'s'),
                        'desc' => $this->l('Custom blacklist of top-level domains.') . ' ' . $this->l('Separates by a comma') . ' (\',\') ' . $this->l('without space.'),
                        'hint' => $this->l('E.g.') . ' ru,qq.com,vn',
                        'disabled' => $disabled,
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Block custom list of e-mail addresses'),
                        'name' => 'PRO_BLOCK_EMAILS',
                        'is_bool' => true,
                        'desc' => $this->l('Block specific e-mails addresses.'),
                        'disabled' => $disabled,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'col' => 8,
                        'name' => 'PRO_BLOCK_EMAILS_CUSTOM_LIST',
                        'label' => $this->l('Custom list of e-mail addresses'),
                        'desc' => $this->l('Custom blacklist of email addresses.') . ' ' . $this->l('Separates by a comma') . ' (\',\') ' . $this->l('without space.'),
                        'hint' => $this->l('E.g.') . ' foo@bar.com,baz@qux.com',
                        'disabled' => $disabled,
                    ],
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('Registration form')) . $this->addAlertInfo($this->l('This module does not use overrides. Therefore, it is not possible to add these checks on the registration at the checkout process. These checks are limited to this registration form') . ': ' . $this->generateLink($linkRegistrationForm)) . $warningOPC,
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Prevent fake accounts'),
                        'name' => 'PRO_FAKE_ACCOUNTS',
                        'is_bool' => true,
                        'desc' => $this->l('Prevent bots from making fake accounts and secure against CSRF (Cross-site request forgery) attacks.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Disallow URL in customer name'),
                        'name' => 'PRO_DISALLOW_URL_CUSTOMER_NAME',
                        'is_bool' => true,
                        'desc' => $this->l('Prevent bots from making fake accounts by verifying that first name and last name is not a URL.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Enable') . ' reCAPTCHA v3',
                        'name' => 'PRO_RECAPTCHA_V3_REGISTRATION_ACTIVATE',
                        'is_bool' => true,
                        'desc' => $this->l('reCAPTCHA v3 returns a risk score for each request without user friction. The module uses this risk-score to determine whether the user is a bot or a human. Bots will be prevented from register accounts.') . ' ' . $this->generateLink('https://www.google.com/recaptcha/about/', $this->l('Read more')) . '.',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Block disposable e-mails'),
                        'name' => 'PRO_DISPOSABLE_EMAIL_PROVIDERS_REGISTRATION_ACTIVATE',
                        'is_bool' => true,
                        'desc' => $this->l('Block e-mails from disposable providers.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Block custom list of TLD\'s'),
                        'name' => 'PRO_EMAIL_CHECKER_REGISTRATION_ACTIVATE',
                        'is_bool' => true,
                        'desc' => $this->l('Block e-mails with a custom list of top-level domains.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'col' => 8,
                        'name' => 'PRO_EMAIL_CHECKER_CUSTOM_LIST_REGISTRATION',
                        'label' => $this->l('Custom list of TLD\'s'),
                        'desc' => $this->l('Custom blacklist of top-level domains.') . ' ' . $this->l('Separates by a comma') . ' (\',\') ' . $this->l('without space.'),
                        'hint' => $this->l('E.g.') . ' ru,qq.com,vn',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormMalwareScan()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Anti Malware'),
                    'icon' => 'icon-user-secret',
                ],
                'description' => $this->l('The term malware refers to software that damages devices, steal data, and causes chaos. There are many types of malware — viruses, trojans, spyware, ransomware, and more.') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Malware', $this->l('Read more')) . '.',
                'input' => [
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Get an e-mail if malware is detected'),
                        'name' => 'PRO_MALWARE_SCAN_EMAIL',
                        'is_bool' => true,
                        'desc' => $this->l('Scan all your directories for malware and let you know by e-mail if something was found.') . ' ' . $this->l('Once this option is enabled, a cronjob will appear in your dashboard that you need to set up.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Log malware'),
                        'name' => 'PRO_MALWARE_SCAN_LOG',
                        'is_bool' => true,
                        'desc' => $this->l('Scan all your directories for malware and log it if something was found.') . ' ' . $this->l('The log can be found on your dashboard.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'col' => 8,
                        'desc' => $this->l('Whitelist false positives, caused by custom modules, etc. You must enter the unique ID of 40 characters in parentheses after the malware report\'s file name. This ID is a SHA1 hash of the content of the file. If the file changes, this ID will change as well.') . ' ' . $this->l('Separate files by a comma') . ' (\',\') ' . $this->l('without space.'),
                        'name' => 'PRO_WHITELIST_MALWARE',
                        'label' => $this->l('Whitelist filter for malware'),
                        'hint' => $this->l('E.g.') . ' aee730f56df09fe6527e41b89c54f55cc5446ec8',
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Log error 404'),
                        'name' => 'PRO_PAGE_NOT_FOUND_LOG',
                        'is_bool' => true,
                        'desc' => $this->l('Track every \'page not found\' (error 404) and log them into a log file. This is very useful to detect hacking attempts.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Secure external links'),
                        'name' => 'PRO_SECURE_EXTERNAL_LINKS',
                        'is_bool' => true,
                        'desc' => \sprintf(
                            $this->l('When you link to another site, you can expose your site to security issues. Enabling this feature will add the following tags to all external links on your website: %1$s. %2$s.'),
                            'rel="noopener", rel="noreferrer", rel="nofollow", target="_blank"',
                            $this->generateLink(
                                'https://web.dev/external-anchors-use-rel-noopener/',
                                $this->l('Read more')
                            )
                        ),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormAntiFakeCarts()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Anti-Fake Carts'),
                    'icon' => 'icon-shopping-cart',
                ],
                'description' => $this->l('The module can automatically delete abandoned carts. Abandoned carts can be generated both by users and by crawlers, resulting in a massive amount of useless data that severely affects your shop database\'s performances.'),
                'input' => [
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Delete old carts'),
                        'name' => 'PRO_DELETE_OLD_CARTS',
                        'is_bool' => true,
                        'desc' => $this->l('Delete unused carts after a certain number of days.') . ' ' . $this->l('Once this option is enabled, a cronjob will appear in your dashboard that you need to set up.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 4,
                        'type' => 'text',
                        'suffix' => $this->l('days'),
                        'desc' => $this->l('Allowed days a cart must be saved before it is automatically deleted.') . ' ' . $this->l('14 days is recommended.'),
                        'name' => 'PRO_DELETE_OLD_CARTS_DAYS',
                        'label' => $this->l('Max days'),
                        'hint' => $this->l('Must be an integer'),
                        'required' => true,
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Prevent crawlers from adding to the cart'),
                        'name' => 'PRO_BLOCK_ADD_TO_CART',
                        'is_bool' => true,
                        'desc' => $this->l('Crawlers that do not respect your robot.txt rules might click the add to cart button. This can lead to a lot of unused carts that will slow down your site. This feature will block crawlers from adding to cart.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormWebsiteMonitoringService()
    {
        $apiKey = (bool) Configuration::get('PRO_MONTASTIC_API');
        $result = null;
        if (true === ($apiKey)) {
            $montasticIds = $this->getMontasticIds();
            if (false === empty($montasticIds)) {
                $enabled = '<i class="icon icon-check" style="color: ' . self::COLOR_GREEN . '"></i>';
                $disabled = '<i class="icon icon-times" style="color: ' . self::COLOR_RED . '"></i>';
                $table = [];

                foreach ($montasticIds as $id) {
                    $montasticData = $this->getMontasticData($id);
                    $table[] = [
                        $this->l('Checkpoint') => $montasticData['url'],
                        $this->l('Enabled') => ($montasticData['is_monitoring_enabled']) ? $enabled : $disabled,
                        $this->l('Monitoring interval') => $montasticData['check_interval_id'] . ' ' . $this->l('min.'),
                        $this->l('Status') => (-1 !== $montasticData['status']) ? $enabled : $disabled,
                    ];
                }

                $result = $this->arrayToTable($table);
            }
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Website Monitoring Service'),
                    'icon' => 'icon-clock-o',
                ],
                'description' => $this->l('By connecting up with Montastic, you can be notified by e-mail if your website is down. The free plan allows you to ping your website(s) every 30 min. You can have up to 9 checkpoints at the same time. Try it out!') . '<br>' . $this->l('You can manage your checkpoints here') . ': ' . $this->generateLink('https://montastic.com/checkpoints') . '.',
                'warning' => (false === $apiKey) ? $this->l('You need to add your API key in \'General Settings\' to get access to this content. You can get an API key here at') . ' ' . $this->generateLink('https://montastic.com/me?tab=form_profile') . ' (' . $this->l('You can choose the free plan') . ').' : null,
                'input' => [
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => (null !== ($result)) ? $result : '',
                        'col' => 12,
                        'name' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * Display form for monitoring changes.
     *
     * @return array
     */
    protected function fieldsFormMonitoringChanges()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Change Monitoring'),
                    'icon' => 'icon-bell-o',
                ],
                'description' => $this->l('If you cannot monitor changes, you cannot manage them. To control your environment, you need the ability to analyze and respond to changes. The module allows you to monitor some important changes, like file changes.'),
                'input' => [
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Get an e-mail if file changes'),
                        'name' => 'PRO_FILE_CHANGES_EMAIL',
                        'is_bool' => true,
                        'desc' => $this->l('Track every file change on your server and let you know by e-mail if something has changed.') . ' ' . $this->l('The module also does reports file changes during PrestaShop update, module update, theme update, etc.') . ' ' . $this->l('Once this option is enabled, a cronjob will appear in your dashboard that you need to set up.') . ' ' . $this->l('Cache files, images etc. are excluded.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Log file changes'),
                        'name' => 'PRO_FILE_CHANGES_LOG',
                        'is_bool' => true,
                        'desc' => $this->l('Track every file change on your server and log it if something has changed.') . ' ' . $this->l('The module does also reports file changes during PrestaShop update, module update, theme update, etc.') . ' ' . $this->l('The log can be found on your dashboard.') . ' ' . $this->l('Cache files, images etc. are excluded.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Get an e-mail if the server IP changes'),
                        'name' => 'PRO_SERVER_IP',
                        'is_bool' => true,
                        'desc' => $this->l('Get notified if the server IP changes.') . ' ' . $this->l('Once this option is enabled, a cronjob will appear in your dashboard that you need to set up.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Get an e-mail if the country of the server changes'),
                        'name' => 'PRO_SERVER_LOCATION',
                        'is_bool' => true,
                        'desc' => $this->l('Get notified if the location of the server country changes.') . ' ' . $this->l('Once this option is enabled, a cronjob will appear in your dashboard that you need to set up.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Get an e-mail if your ISP changes'),
                        'name' => 'PRO_SERVER_ISP',
                        'is_bool' => true,
                        'desc' => $this->l('Get notified if the name of your ISP changes.') . ' ' . $this->l('Once this option is enabled, a cronjob will appear in your dashboard that you need to set up.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Get an e-mail if your TLS certificate is about to expire'),
                        'name' => 'PRO_TLS_EXPIRE',
                        'disabled' => false === (bool) Configuration::get('PS_SSL_ENABLED'),
                        'is_bool' => true,
                        'desc' => $this->l('Get notified if your TLS certificate is about to expire.') . ' ' . $this->l('Once this option is enabled, a cronjob will appear in your dashboard that you need to set up.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormAntiFraud()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Fraud Detection'),
                    'icon' => 'icon-user-times',
                ],
                'description' => $this->l('The module can analyze your orders on different criteria. A score is established to determine whether the order looks suspicious or not.'),
                'input' => [
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Fraud detection'),
                        'name' => 'PRO_ANTI_FRAUD',
                        'is_bool' => true,
                        'desc' => $this->l('Display a section on each order that tells if the order looks suspicious.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Distance unit'),
                        'desc' => $this->l('Choose your default distance unit. \'km\' for kilometre, \'mi\' for mile.'),
                        'name' => 'PRO_ANTI_FRAUD_UNIT',
                        'options' => [
                            'query' => [
                                [
                                    'id_option' => 'km',
                                    'name' => 'km',
                                ],
                                [
                                    'id_option' => 'mi',
                                    'name' => 'mi',
                                ],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Display'),
                        'desc' => $this->l('Choose where you want to display the section at the admin order page.'),
                        'name' => 'PRO_ANTI_FRAUD_HOOK',
                        'options' => [
                            'query' => [
                                [
                                    'id_option' => 'left',
                                    'name' => $this->l('Left column'),
                                ],
                                [
                                    'id_option' => 'right',
                                    'name' => $this->l('Right column'),
                                ],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormProtectContent()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Protect Content'),
                    'icon' => 'icon-hand-o-up',
                ],
                'description' => $this->l('The module allows you to disable a list of mouse- and key-events. These settings make it harder for users that manually try to steal your content. These settings will affect the front office only.'),
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Disable right-click'),
                        'desc' => $this->l('Disable right-click mouse event.') . ' ' . $this->l('Input and Textarea fields are excluded from this rule.'),
                        'name' => 'PRO_DISABLE_RIGHT_CLICK',
                        'options' => [
                            'query' => [
                                [
                                    'id_option' => 0,
                                    'name' => $this->l('No'),
                                ],
                                [
                                    'id_option' => 1,
                                    'name' => $this->l('Yes'),
                                ],
                                [
                                    'id_option' => 2,
                                    'name' => $this->l('Images only'),
                                ],
                            ],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Disable drag and drop'),
                        'name' => 'PRO_DISABLE_DRAG',
                        'is_bool' => true,
                        'desc' => $this->l('Disable drag and drop mouse event.') . ' ' . $this->l('Input and Textarea fields are excluded from this rule.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Disable copy shortcut'),
                        'name' => 'PRO_DISABLE_COPY',
                        'is_bool' => true,
                        'desc' => $this->l('Disable copy with the keyboard shortcut (Ctrl + c / ⌘ + c).') . ' ' . $this->l('Input and Textarea fields are excluded from this rule.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Disable cut shortcut'),
                        'name' => 'PRO_DISABLE_CUT',
                        'is_bool' => true,
                        'desc' => $this->l('Disable cut with the keyboard shortcut (Ctrl + x / ⌘ + x).') . ' ' . $this->l('Input and Textarea fields are excluded from this rule.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Disable text selection'),
                        'name' => 'PRO_DISABLE_TEXT_SELECTION',
                        'is_bool' => true,
                        'desc' => $this->l('Disable text selection with the mouse and keyboard shortcut (Ctrl + a / ⌘ + a).') . ' ' . $this->l('Input and Textarea fields are excluded from this rule.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Disable print shortcut'),
                        'name' => 'PRO_DISABLE_PRINT',
                        'is_bool' => true,
                        'desc' => $this->l('Disable print with the keyboard shortcut (Ctrl + p / ⌘ + p).'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Disable save shortcut'),
                        'name' => 'PRO_DISABLE_SAVE',
                        'is_bool' => true,
                        'desc' => $this->l('Disable save keyboard shortcut. (Ctrl + s / ⌘ + s).'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Disable developer tool shortcuts'),
                        'name' => 'PRO_DISABLE_VIEW_PAGE_SOURCE',
                        'is_bool' => true,
                        'desc' => $this->l('Disable developer tool shortcuts.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Disable console'),
                        'name' => 'PRO_DISABLE_CONSOLE',
                        'is_bool' => true,
                        'desc' => $this->l('This feature will clear the console when something is displayed.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 8,
                        'type' => 'textbutton',
                        'label' => $this->l('Whitelist'),
                        'hint' => $this->l('E.g.') . ' 123.456.789,123.456.*,123.*,...',
                        'desc' => $this->l('You can list your IP addresses if you want to bypass your rules above.') . '<br>' . $this->l('The module can handle IPv4 and IPv6 addresses and IP ranges, in CIDR formats like') . ' <code>::1/128</code> ' . $this->l('or') . ' <code>127.0.0.1/32</code> ' . $this->l('and pattern format like') . ' <code>::*:*</code> ' . $this->l('or') . ' <code>127.0.*.*</code>. ' . $this->l('Separates by a comma') . ' (\',\') ' . $this->l('without space.'),
                        'name' => 'PRO_WHITELIST_PROTECT_CONTENT',
                        'button' => [
                            'label' => '<i class="icon-plus"></i> ' . $this->l('Add my IP'),
                            'attributes' => [
                                'onclick' => 'addMyIp("#PRO_WHITELIST_PROTECT_CONTENT");',
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @throws PrestaShopException
     *
     * @return array
     */
    protected function fieldsFormBackup()
    {
        $client = $this->dropboxGetClient();

        if (isset($client->files->list_folder(self::DIR_BACKUP_DATABASE)['entries'])) {
            $featuredDatabase = $client->files->list_folder(self::DIR_BACKUP_DATABASE)['entries'];
        } else {
            $featuredDatabase = [];
        }

        if (isset($client->files->list_folder(self::DIR_BACKUP_FILES)['entries'])) {
            $featuredFiles = $client->files->list_folder(self::DIR_BACKUP_FILES)['entries'];
        } else {
            $featuredFiles = [];
        }

        if (false === empty($featuredDatabase)) {
            $listDatabase = [];
            foreach ($featuredDatabase as $valueDatabase) {
                $date = Tools::substr($valueDatabase['server_modified'], 0, 10);
                $listDatabase[] = '<a onclick="return confirm(\'' . $this->l('Are you sure, you want to delete') . ' ' . $valueDatabase['name'] . '?\')" href="' . $this->currentAdminIndex() . '&DropboxBackupDatabaseDelete=1&file=' . $valueDatabase['name'] . '"><i style="color: ' . self::COLOR_RED . ';" class="icon icon-trash-o"></i></a> <a href="' . $this->currentAdminIndex() . '&DropboxBackupDatabaseDownload=1&file=' . $valueDatabase['name'] . '"><i style="color: ' . self::COLOR_GREEN . ';" class="icon icon-download"></i></a> ' . Tools::formatBytes($valueDatabase['size'], 1) . 'B | ' . $valueDatabase['path_lower'] . ' (' . $date . ')';
            }
        }

        if (false === empty($featuredFiles)) {
            $listFiles = [];
            foreach ($featuredFiles as $valueFiles) {
                $date = Tools::substr($valueFiles['server_modified'], 0, 10);
                $listFiles[] = '<a onclick="return confirm(\'' . $this->l('Are you sure, you want to delete') . ' ' . $valueFiles['name'] . '?\')" href="' . $this->currentAdminIndex() . '&DropboxBackupFilesDelete=1&file=' . $valueFiles['name'] . '"><i style="color: ' . self::COLOR_RED . ';" class="icon icon-trash-o"></i></a> <a href="' . $this->currentAdminIndex() . '&DropboxBackupFilesDownload=1&file=' . $valueFiles['name'] . '"><i style="color: ' . self::COLOR_GREEN . ';" class="icon icon-download"></i></a> ' . Tools::formatBytes($valueFiles['size'], 1) . 'B | ' . $valueFiles['path_lower'] . ' (' . $date . ')';
            }
        }

        $extension = [
            'zip',
            'bz2',
        ];

        if ($handle = \opendir(_PS_MODULE_DIR_ . $this->name . self::DIR_BACKUP_DATABASE)) {
            $dirPath = [];
            while (false !== ($entry = \readdir($handle))) {
                if ('.' !== $entry && '..' !== $entry) {
                    if (\in_array(\pathinfo(\basename($entry), \PATHINFO_EXTENSION), $extension, true)) {
                        $pathToFile = \realpath(_PS_MODULE_DIR_ . $this->name . self::DIR_BACKUP_DATABASE . $entry);
                        $date = Tools::displayDate(\date('Y-m-d H:i:s', (int) Tools::substr(\basename($entry), 0, 10)));
                        $dirPath[] = '<a onclick="return confirm(\'' . $this->l('Are you sure, you want to delete') . ' ' . $entry . '?\')" href="' . $this->currentAdminIndex() . '&BackupDatabaseDelete=1&file=' . $entry . '"><i style="color: ' . self::COLOR_RED . ';" class="icon icon-trash-o"></i></a> <a href="' . $this->currentAdminIndex() . '&BackupDatabaseDownload=1&file=' . $entry . '"><i style="color: ' . self::COLOR_GREEN . ';" class="icon icon-download"></i></a> ' . Tools::formatBytes(\filesize($pathToFile), 1) . 'B | ' . $pathToFile . ' (' . $date . ')';
                    }
                }
            }
        }

        if ($handle = \opendir(_PS_MODULE_DIR_ . $this->name . self::DIR_BACKUP_FILES)) {
            $filePath = [];
            while (false !== ($entry = \readdir($handle))) {
                if ('.' !== $entry && '..' !== $entry) {
                    if ('zip' === \pathinfo(\basename($entry), \PATHINFO_EXTENSION)) {
                        $pathToFile = \realpath(_PS_MODULE_DIR_ . $this->name . self::DIR_BACKUP_FILES . $entry);
                        $date = Tools::displayDate(\date('Y-m-d H:i:s', (int) Tools::substr(\basename($entry), 0, 10)));
                        $filePath[] = '<a onclick="return confirm(\'' . $this->l('Are you sure, you want to delete') . ' ' . $entry . '?\')" href="' . $this->currentAdminIndex() . '&BackupFilesDelete=1&file=' . $entry . '"><i style="color: ' . self::COLOR_RED . ';" class="icon icon-trash-o"></i></a> <a href="' . $this->currentAdminIndex() . '&BackupFilesDownload=1&file=' . $entry . '"><i style="color: ' . self::COLOR_GREEN . ';" class="icon icon-download"></i></a> ' . Tools::formatBytes(\filesize($pathToFile), 1) . 'B | ' . $pathToFile . ' (' . $date . ')';
                    }
                }
            }
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Automatic Backups'),
                    'icon' => 'icon-files-o',
                ],
                'description' => $this->l('Keeping a backup may be your easiest and best protection; allowing you to turn back the clock after an attack. While this does not prevent attacks, it does cure them when needed.') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Backup', $this->l('Read more')) . '.',
                'warning' => $this->l('Security Pro is not responsible for your database/files, its backups, and/or recovery.') . '<br>' .
                    $this->l('You should back up your data regularly (both files and databases).') . '<br>' .
                    $this->l('Security Pro can back up your database and files and save it locally, to Google Drive and Dropbox.') . '<br>' .
                    $this->l('Always verify the quality and integrity of your backup files!') . '<br>' .
                    $this->l('Always verify that your backup files are complete, up-to-date, and valid, even if you had a success message appear during the backup process.') . '<br>' .
                    $this->l('Always check your data.') . '<br>' .
                    $this->l('Never restore a backup on a live site.'),
                'input' => [
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('Backup database'), true),
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Backup database to Google Drive'),
                        'name' => 'PRO_BACKUP_DB_GOOGLE_DRIVE',
                        'is_bool' => true,
                        'desc' => $this->l('Save a backup of your database to your Google Drive. Statistical data are excluded.') . ' ' . $this->l('Once this option is enabled, a cronjob will appear in your dashboard that you need to set up.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Backup database to Dropbox'),
                        'name' => 'PRO_BACKUP_DB_DROPBOX',
                        'is_bool' => true,
                        'desc' => $this->l('Save a backup of your database to your Dropbox. Statistical data are excluded.') . ' ' . $this->l('Once this option is enabled, a cronjob will appear in your dashboard that you need to set up.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Backup database to local'),
                        'name' => 'PRO_BACKUP_DB',
                        'is_bool' => true,
                        'desc' => $this->l('Save a local backup of your database. Statistical data are excluded.') . ' ' . $this->l('Once this option is enabled, a cronjob will appear in your dashboard that you need to set up.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon-floppy-o"></i>',
                        'desc' => $this->l('Old backups will be deleted when a newer one is generated. How many backups do you want to keep at the time? Write, \'0\' for unlimited backups.'),
                        'name' => 'PRO_BACKUP_DB_SAVED',
                        'label' => $this->l('Database backups to save'),
                        'suffix' => $this->l('backups'),
                        'hint' => $this->l('Must be an integer'),
                        'required' => true,
                    ],
                    [
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon-key"></i>',
                        'desc' => $this->l('Protect the compressed database with a password using AES (Advanced Encryption Standard) with a 256-bit key.'),
                        'name' => 'PRO_BACKUP_DB_PASSWORD',
                        'label' => $this->l('Encrypt with a password'),
                    ],
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->addHeading($this->l('Backup files')),
                        'col' => 12,
                        'name' => '',
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Backup files to Google Drive'),
                        'name' => 'PRO_BACKUP_FILES_GOOGLE_DRIVE',
                        'is_bool' => true,
                        'desc' => $this->l('Save a backup of your database to your Google Drive. Statistical data are excluded.') . ' ' . $this->l('Once this option is enabled, a cronjob will appear in your dashboard that you need to set up.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Backup files to Dropbox'),
                        'name' => 'PRO_BACKUP_FILE_DROPBOX',
                        'is_bool' => true,
                        'desc' => $this->l('Save a full backup of your files to your Dropbox. Cache and log files are excluded.') . ' ' . $this->l('Once this option is enabled, a cronjob will appear in your dashboard that you need to set up.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Backup files to local'),
                        'name' => 'PRO_BACKUP_FILE',
                        'is_bool' => true,
                        'desc' => $this->l('Save a full backup of your files on your PrestaShop installation.') . ' ' . $this->l('Once this option is enabled, a cronjob will appear in your dashboard that you need to set up.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon-floppy-o"></i>',
                        'desc' => $this->l('Old backups will be deleted when a new one is generated. How many backups do you want to keep at the time? Write \'0\' for unlimited backups.'),
                        'name' => 'PRO_BACKUP_FILE_SAVED',
                        'label' => $this->l('File backups to save'),
                        'suffix' => $this->l('backups'),
                        'hint' => $this->l('Must be an integer'),
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormAdminDir()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Admin Folder'),
                    'icon' => 'icon-folder-o',
                ],
                'description' => $this->l('It would be best if you always kept the path to your admin login secret. If you need to change it, you can change it with this tool.'),
                'input' => [
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Are you sure, you want to change the name of your admin folder?'),
                        'name' => 'PRO_ADMIN_DIRECTORY',
                        'is_bool' => true,
                        'desc' => $this->l('You will be redirected to the new URL once you click') . ' \'' . $this->l('Save') . '\' ' . $this->l('if this option is set to') . ' \'' . $this->l('Yes') . '\'.',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 6,
                        'type' => 'text',
                        'prefix' => $this->getBaseURL(),
                        'desc' => $this->l('Your admin folder name should include both letters and numbers. Make it hard to guess; do not use admin123, administrator, backoffice, etc.') . ' <a onclick="generateFolderName()" href="javascript:void(0)">' . $this->l('Generate a secure folder name') . '</a>.',
                        'name' => 'PRO_ADMIN_DIRECTORY_NAME',
                        'label' => $this->l('Directory name'),
                        'hint' => $this->l('Accepted characters') . ': \'a-z A-Z 0-9 _ . -\'',
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormTools()
    {
        $buttons = [
            [
                $this->l('File permissions'),
                $this->l('Check the systems file- and folder permissions. This tool can fix insecure file- and folder permissions.') . ' ' . $this->l('File permission must be 644 and folder permissions must be 755.') . '<br>' . $this->l('Generate a report to see permissions that must be changed.') . ' ' . $this->l('Start by generating a report to see the consequence.'),
                '<span>' .
                $this->btnAjax(
                    'fixPermissions',
                    '<i class="icon icon-check"></i> ' . $this->l('Fix vulnerability'),
                    $this->l('Are you sure you want to change file permissions to 644 and folder permissions to 755?')
                )
                . '<span class="securitypro-divider"></span>' .
                $this->btnAjax(
                    'permissionsAnalyze',
                    '<i class="icon icon-file-text-o"></i> ' . $this->l('Generate report'),
                    0,
                    'CreateIndexAnalyze=1'
                )
                . '</span>',
            ],
            [
                $this->l('Directory traversal'),
                $this->l('Check the system for directory traversal security vulnerability.') . ' ' . $this->l('This tool can add missing index.php files to the theme- and module directories.') . '<br>' . $this->l('Generate a report to see which paths are missing the index.php file.'),
                '<span>' .
                $this->btnAjax(
                    'createIndex',
                    '<i class="icon icon-check"></i> ' . $this->l('Fix vulnerability'),
                    $this->l('Are you sure you want to add the missing index.php files?')
                )
                . '<span class="securitypro-divider"></span>' .
                $this->btnAjax(
                    'createIndexAnalyze',
                    '<i class="icon icon-file-text-o"></i> ' . $this->l('Generate report'),
                    0,
                    'CreateIndexAnalyze=1'
                )
                . '</span>',
            ],
            [
                $this->l('Delete files'),
                $this->l('Check the system for files that should be removed due to security reasons.') . ' ' . $this->l('This tool can remove these files. These files could be files leftover from the installation.') . '<br>' . $this->l('Generate a report to see which files should be deleted.') . ' ' . $this->l('Deleting files is permanent. Start by generating a report to see the consequence.'),
                '<span>' .
                $this->btnAjax(
                    'removeFiles',
                    '<i class="icon icon-check"></i> ' . $this->l('Fix vulnerability'),
                    $this->l('Are you sure you want to delete the files?')
                )
                . '<span class="securitypro-divider"></span>' .
                $this->btnAjax(
                    'removeFilesAnalyze',
                    '<i class="icon icon-file-text-o"></i> ' . $this->l('Generate report'),
                    0,
                    'RemoveFilesAnalyze=1'
                )
                . '</span>',
            ],
        ];

        $table = [];
        foreach ($buttons as $button) {
            $table[] = [
                $this->l('Title') => $button[0],
                $this->l('Description') => $button[1],
                null => '<span class="securitypro-position" style="padding: 8px 0 8px 0;">' . $button[2] . '</span>',
            ];
        }

        $result = $this->arrayToTable($table);

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Tools'),
                    'icon' => 'icon-wrench',
                ],
                'description' => $this->l('These tools can fix some known vulnerabilities. Some of these tools need up to 2 min. to run. Please wait until the page has finished loading.'),
                'input' => [
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $result,
                        'col' => 12,
                        'name' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormPasswdGen()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Password Generator'),
                    'icon' => 'icon-refresh',
                ],
                'description' => $this->l('It would be best to use a strong and unique password for each of MySQL database, FTP, hosting panel/cPanel, SSH access, and back office. You can use this tool to generate passwords.') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Password_strength', $this->l('Read more')) . '.',
                'input' => [
                    [
                        'col' => 6,
                        'type' => 'textbutton',
                        'label' => $this->l('Generate a strong password'),
                        'desc' => $this->l('The password is not saved anywhere by this module.'),
                        'name' => 'PRO_PASSWORD_GENERATOR',
                        'button' => [
                            'label' => $this->l('Generate'),
                            'attributes' => [
                                'onclick' => 'generatePassword();',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Display analyze of system table.
     *
     * @throws PrestaShopException
     *
     * @return array
     */
    protected function fieldsFormAnalyzeSystem()
    {
        $checkCves = [
            $this->checkCve202143789(),
            $this->checkCve202121398(),
            $this->checkCve202121308(),
            $this->checkCve202121302(),
            $this->checkCve202026224(),
            $this->checkCve202015162(),
            $this->checkCve202015161(),
            $this->checkCve202015160(),
            $this->checkCve202015083(),
            $this->checkCve202015082(),
            $this->checkCve202015081(),
            $this->checkCve202015080(),
            $this->checkCve202015079(),
            $this->checkCve20205293(),
            $this->checkCve20205288(),
            $this->checkCve20205287(),
            $this->checkCve20205286(),
            $this->checkCve20205285(),
            $this->checkCve20205279(),
            $this->checkCve20205278(),
            $this->checkCve20205276(),
            $this->checkCve20205272(),
            $this->checkCve20205271(),
            $this->checkCve20205270(),
            $this->checkCve20205269(),
            $this->checkCve20205265(),
            $this->checkCve20205264(),
            $this->checkCve20205250(),
            $this->checkCve20204074(),
            $this->checkCve201913461(),
            $this->checkCve201911876(),
            $this->checkCve20188824(),
            $this->checkCve20188823(),
            $this->checkCve201819355(),
            $this->checkCve201819125(),
            $this->checkCve201819126(),
            $this->checkCve201819124(),
            $this->checkCve201813784(),
            $this->checkCve20187491(),
            $this->checkCve20179841(),
        ];

        $checkSettings = [
            $this->checkPrestaShopVersion(),
            $this->checkPhpVersion(),
            $this->checkTlsEnabled(),
            $this->checkTlsEnabledEverywhere(),
            $this->checkPrestashopToken(),
            $this->checkModSecurity(),
            $this->checkAdminDirectoryName(),
            $this->checkCookieIpAddress(),
            $this->checkUseHtmlPurifier(),
            $this->checkPrestashopDevMode(),
        ];

        $check = '<i class="icon icon-check" style="color: ' . self::COLOR_GREEN . '"></i>';
        $vulnerable = '<i class="icon icon-times" style="color: ' . self::COLOR_RED . '"></i>';
        $possible = '<i class="icon icon-question-circle" style="color: ' . self::COLOR_BLUE . '"></i>';
        $empty = '--';

        $cveResult = [];
        foreach ($checkCves as $checkCve) {
            (true === $checkCve[1]) ? $nvdNist = $this->cachedJsonDecodedContent('https://services.nvd.nist.gov/rest/json/cve/1.0/' . $checkCve[0], null, $checkCve[0], 604800)['result']['CVE_Items'][0] : $nvdNist = null;

            if (isset($nvdNist['impact']['baseMetricV3']['cvssV3'])) {
                $baseScore = (float) $nvdNist['impact']['baseMetricV3']['cvssV3']['baseScore'] . ' ' . \htmlspecialchars($nvdNist['impact']['baseMetricV3']['cvssV3']['baseSeverity']);
            } else {
                $baseScore = $empty;
            }

            if (isset($nvdNist['cve']['description']['description_data'][0]['value'])) {
                $value = \htmlspecialchars($nvdNist['cve']['description']['description_data'][0]['value']);
            } else {
                $value = $empty;
            }

            $cveResult[] = [
                'CVE' => $this->generateLink('https://nvd.nist.gov/vuln/detail/' . $checkCve[0], $checkCve[0]),
                $this->l('Status') => (true === $checkCve[1]) ? $possible : $check,
                $this->l('Base score') => (true === $checkCve[1]) ? $baseScore : $empty,
                $this->l('Description') => (true === $checkCve[1]) ? $value : $empty,
                $this->l('How to fix') => (true === $checkCve[1]) ? $checkCve[2] : $empty,
            ];
        }

        $prestaResult = [];
        foreach ($checkSettings as $checkSetting) {
            $prestaResult[] = [
                $this->l('Check') => $checkSetting[0],
                $this->l('Status') => $checkSetting[1] ? $vulnerable : $check,
                $this->l('Description') => $checkSetting[1] ? $checkSetting[3] : $empty,
                $this->l('How to fix') => $checkSetting[1] ? $checkSetting[2] : $empty,
            ];
        }

        $googlePageSpeed = $this->getGooglePageSpeed();
        $vulnContent = [];
        if (false !== $googlePageSpeed) {
            foreach ($googlePageSpeed as $vuln) {
                $vulnContent[] = [
                    $this->l('Library version') => $this->generateLink($vuln['detectedLib']['url'], $vuln['detectedLib']['text']),
                    $this->l('Vulnerability count') => $vuln['vulnCount'],
                    $this->l('Highest severity') => $vuln['highestSeverity'],
                ];
            }
        }

        if (false === empty($vulnContent)) {
            $jsVulnResult = $this->addHeading($this->l('Includes front-end JavaScript libraries with known security vulnerabilities')) .
                $this->addAlertInfo($this->l('Some third-party scripts may contain known security vulnerabilities that are easily identified and exploited by attackers. These vulnerabilities must be solved by the developer of the theme you are using.')) .
                $this->arrayToTable($vulnContent);
        } else {
            $jsVulnResult = null;
        }

        $result = $this->addHeading($this->l('Check for insecure PrestaShop settings'), true) .
            $this->addAlertInfo($this->l('Recommend more secure options for your installation.')) .
            $this->arrayToTable($prestaResult) . '<br>' .
            $this->addHeading($this->l('Check for common vulnerabilities and exposures')) .
            $this->addAlertInfo($this->l('Scan your PrestaShop website for common vulnerabilities and exposures.')) .
            $this->arrayToTable($cveResult) .
            $jsVulnResult;

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Analyze System'),
                    'icon' => 'icon-list',
                ],
                'input' => [
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $result,
                        'col' => 12,
                        'name' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * Display analyze of domain security.
     *
     * @return array
     */
    protected function fieldsFormAnalyzeDomain()
    {
        if ('localhost' === $this->domain()) {
            $domain = self::TEST_DOMAIN;
            $serverIp = self::TEST_SERVER_IP;
        } else {
            $domain = $this->domain();
            $serverIp = $this->serverIP();
        }

        $empty = '--';
        $getServerInfo = $this->getDomainInfo($serverIp);

        $check = '<i class="icon icon-check" style="color: ' . self::COLOR_GREEN . '"></i>';
        $vulnerable = '<i class="icon icon-times" style="color: ' . self::COLOR_RED . '"></i>';

        $testSpf = $this->testSpf($domain);

        if (true === $testSpf) {
            $spfText = $this->l('There are two essential reasons to have SPF (Sender Policy Framework) record for the domain. Security – an attacker may send spoofed e-mail from the vulnerable domain if SPF record does not exist. Most of the security scanner will consider this as \'Missing SPF Record Vulnerability.\' E-mail Delivery – not having SPF record may cause an e-mail to land in the SPAM box.') . $this->l('You have already implemented this record') . ': <strong>' . $this->testSpf($domain) . '</strong>';
        } else {
            $spfText = $this->l('There are two essential reasons to have SPF (Sender Policy Framework) record for the domain. Security – an attacker may send spoofed e-mail from the vulnerable domain if SPF record does not exist. Most of the security scanner will consider this as \'Missing SPF Record Vulnerability.\' E-mail Delivery – not having SPF record may cause an e-mail to land in the SPAM box.');
        }

        $checkSettings = [
            [
                $this->l('Domain'),
                ($domain === $serverIp) ? $serverIp : $domain . ' (' . $serverIp . ')',
            ],
            [
                $this->l('Server country'),
                (false === empty($getServerInfo['country'])) ? \htmlspecialchars($getServerInfo['country']) : $empty,
            ],
            [
                $this->l('ISP (Internet Service Provider)'),
                (false === empty($getServerInfo['org'])) ? \htmlspecialchars($getServerInfo['org']) : $empty,
            ],
            [
                $this->l('ASN (Autonomous System Number)'),
                (false === empty($getServerInfo['asn'])) ? \htmlspecialchars($getServerInfo['asn']) : $empty,
            ],
            [
                $this->l('Name servers'),
                (false === empty($this->getNameServers($domain))) ? \implode(', ', $this->getNameServers($domain)) : $empty,
            ],
        ];

        $checkSupports = [
            [
                $this->l('SPF'),
                (true === $testSpf) ? $check : $vulnerable,
                (true === $testSpf) ? $empty : $spfText,
                (true === $testSpf) ? $empty : $this->l('Ask your host to set an SPF record for your domain.'),
            ],
        ];

        $prestaSupport = [];
        foreach ($checkSupports as $checkSupport) {
            $prestaSupport[] = [
                $this->l('Check') => $checkSupport[0],
                $this->l('Status') => $checkSupport[1],
                $this->l('Description') => $checkSupport[2],
                $this->l('How to fix') => $checkSupport[3],
            ];
        }

        $prestaResult = [];
        foreach ($checkSettings as $checkSetting) {
            $prestaResult[] = [
                $this->l('Check') => $checkSetting[0],
                $this->l('Result') => $checkSetting[1],
            ];
        }

        $checkDomains = [
            [
                'Google',
                'https://transparencyreport.google.com/safe-browsing/search?url=' . $domain,
            ],
            [
                'Yandex',
                'https://yandex.com/safety/?url=' . $domain,
            ],
            [
                'McAfee',
                'https://www.siteadvisor.com/sitereport.html?url=' . $domain,
            ],
            [
                'Securi',
                'https://labs.sucuri.net/blacklist/info/?domain=' . $domain,
            ],
            [
                'VirusTotal',
                'https://www.virustotal.com/gui/domain/' . $domain . '/detection',
            ],
            [
                'Green Snow',
                'https://greensnow.co/view/' . $serverIp,
            ],
            [
                'Spam Rats',
                'https://www.spamrats.com/lookup.php?ip=' . $serverIp,
            ],
            [
                'Is it hacked?',
                'https://isithacked.com/check/' . $domain,
            ],
        ];

        $domainBlacklistStatus = [];
        foreach ($checkDomains as $checkDomain) {
            $domainBlacklistStatus[] = [
                $this->l('Name') => $checkDomain[0],
                null => '<span class="securitypro-position">' . $this->generateBtnLink($this->l('Check status'), $checkDomain[1]) . '</span>',
            ];
        }

        $result = $this->addHeading($this->l('Domain information'), true) .
            $this->arrayToTable($prestaResult) .
            $this->addHeading($this->l('Domain security checks')) .
            $this->arrayToTable($prestaSupport) .
            $this->addHeading($this->l('Global blacklists')) .
            $this->addAlertInfo($this->l('Check if your website is blacklisted somewhere.')) .
            $this->arrayToTable($domainBlacklistStatus);

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Analyze Domain'),
                    'icon' => 'icon-list',
                ],
                'input' => [
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $result,
                        'col' => 12,
                        'name' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormAnalyzeServerConfig()
    {
        $checkGrids = [
            $this->checkSessionAutoStart(),
            $this->checkSessionUseCookies(),
            $this->checkSessionUseOnlyCookies(),
            $this->checkSessionCookieHttponly(),
            $this->checkPhpUseTransSid(),
            $this->checkCookieSecure(),
            $this->checkUseStrictMode(),
            $this->checkCookieLifetime(),
            $this->checkLazyWrite(),
            $this->checkSidLength(),
            $this->checkSessionGcProbability(),
            $this->checkSessionGcDivisor(),
            $this->checkSidBitsPerCharacter(),
            $this->checkUrlFopen(),
            $this->checkUrlInclude(),
            $this->checkDisplayErrors(),
            $this->checkLogErrors(),
            $this->checkErrorReporting(),
            $this->checkDisplayStartupErrors(),
            $this->checkExposePhp(),
            $this->checkRegisterArgcArgv(),
            $this->checkShortOpenTag(),
            $this->checkFileUploads(),
            $this->checkUploadMaxFileSize(),
            $this->checkPostMaxSize(),
            $this->checkMaxInputVars(),
            $this->checkMaxInputTime(),
            $this->checkMemoryLimit(),
            $this->checkMaxExecutionTime(),
            $this->checkDefaultCharset(),
        ];

        $check = '<i class="icon icon-check" style="color: ' . self::COLOR_GREEN . '"></i>';
        $vulnerable = '<i class="icon icon-times" style="color: ' . self::COLOR_RED . '"></i>';
        $empty = '--';

        $result = [];
        foreach ($checkGrids as $checkGrid) {
            if (false !== $checkGrid[1]) {
                $result[] = [
                    $this->l('Current setting') => '<span style="white-space:nowrap;"><kbd>' . $checkGrid[0] . ' = ' . $checkGrid[1] . '</kbd></span>',
                    $this->l('Recommended setting') => '<span style="white-space:nowrap;"><kbd>' . $checkGrid[0] . ' = ' . $checkGrid[2] . '</kbd> <a href="javascript:void(0)" onclick="copyToClipboard(\'' . $checkGrid[0] . ' = ' . $checkGrid[2] . '\')"><i class="icon icon-clipboard"></i></a></span>',
                    $this->l('Status') => $checkGrid[3] ? $vulnerable : $check,
                    $this->l('Description') => $checkGrid[3] ? $checkGrid[4] : $empty,
                ];
            }
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Analyze Server Configuration'),
                    'icon' => 'icon-list',
                ],
                'description' => $this->l('Here are some advanced tips to secure your PHP configuration file. Your PHP configuration file is named php.ini. This file could be stored in different locations according to your setup. If you are not familiar with php.ini, you can ask your host for help.') . '<br>' . $this->l('According to your system, the loaded php.ini file is located here') . ': <strong>' . \php_ini_loaded_file() . '</strong>, ' . $this->l('but keep in mind that this php.ini file could be overridden somewhere, depending on your setup.'),
                'input' => [
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $this->arrayToTable($result),
                        'col' => 12,
                        'name' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @throws PrestaShopException
     *
     * @return array
     */
    protected function fieldsFormAnalyzeSsl()
    {
        if ('localhost' === $this->domain()) {
            $host = self::TEST_URL;
            $domain = self::TEST_DOMAIN;
        } else {
            $host = $this->getBaseURL();
            $domain = $this->domain();
        }

        $url = 'https://www.howsmyssl.com/a/check';

        $ssl = $this->getCertInfo();

        $data = $this->cachedJsonDecodedContent($url, null, 'certificate', 604800);

        $check = '<i class="icon icon-check" style="color: ' . self::COLOR_GREEN . '"></i>';
        $vulnerable = '<i class="icon icon-times" style="color: ' . self::COLOR_RED . '"></i>';
        $possible = '<i class="icon icon-question-circle" style="color: ' . self::COLOR_BLUE . '"></i>';

        if (isset($ssl['validTo_time_t']) && ($ssl['validTo_time_t'] - \time() > 0)) {
            $isValid = $check;
        } else {
            $isValid = $vulnerable;
        }

        if (false !== $ssl) {
            $dateFrom = Tools::displayDate(\date('Y-m-d H:i:s', $ssl['validFrom_time_t']));
            $dateTo = Tools::displayDate(\date('Y-m-d H:i:s', $ssl['validTo_time_t']));
            $certInfos = [
                $this->l('Is valid') => $isValid,
                $this->l('Common name') => $ssl['subject']['CN'],
                $this->l('Alternative names') => \str_replace('DNS:', '', $ssl['extensions']['subjectAltName']),
                $this->l('Issuer') => $ssl['issuer']['CN'],
                $this->l('Valid from') => $dateFrom,
                $this->l('Valid to') => $dateTo,
                $this->l('Expires in') => \round(($ssl['validTo_time_t'] - \time()) / (86400)) . ' ' . $this->l('days'),
            ];

            $tlsVersion = [];
            $tlsVersion['name'] = $this->l('Version') . ' (' . $data['tls_version'] . ')';
            if ('TLS 1.2' === $data['tls_version'] || 'TLS 1.3' === $data['tls_version']) {
                $tlsVersion['description'] = $this->l('Your client uses') . ' ' . $data['tls_version'] . ', ' . $this->l('the most modern version of the encryption protocol. It gives you access to the fastest, most secure encryption possible on the web.');
                $tlsVersion['check'] = $check;
            } elseif ('TLS 1.1' === $data['tls_version']) {
                $tlsVersion['description'] = $this->l('Your client is using TLS 1.1. It would be better to be TLS 1.2, but at least it isn\'t susceptible to the BEAST attack. But, it also does not have the AES-GCM cipher suite available.');
                $tlsVersion['check'] = $vulnerable;
            } else {
                $tlsVersion['description'] = $this->l('Your client is using') . ' ' . $data['tls_version'] . ', ' . $this->l('which is very old, possibly susceptible to the BEAST attack, and does not have the best cipher suites available on it. Additions like AES-GCM, and SHA256 to replace MD5-SHA-1 are unavailable to a TLS 1.0 client and many more modern cipher suites.');
                $tlsVersion['check'] = $vulnerable;
            }
            $tlsVersion['btn'] = $this->generateBtnLink($this->l('Learn more'), 'https://www.howsmyssl.com/s/about.html#version');

            $ephemeralKeysSupported = [];
            $ephemeralKeysSupported['name'] = $this->l('Ephemeral Key Support');
            if (true === $data['ephemeral_keys_supported']) {
                $ephemeralKeysSupported['description'] = $this->l('Ephemeral keys are used in some of the cipher suites your client supports. This means your client may be used to provide') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Forward_secrecy', $this->l('forward secrecy')) . '. ' . $this->l('If the server supports it. This greatly increases your protection against snoopers, including global passive adversaries who scoop up large amounts of encrypted traffic and store them until their attacks (or their computers) improve.');
                $ephemeralKeysSupported['check'] = $check;
            } else {
                $ephemeralKeysSupported['description'] = $this->l('Ephemeral keys are not used in any of the cipher suites your client supports. This means your client cannot be used to provide') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Forward_secrecy', $this->l('forward secrecy')) . '. ' . $this->l('Without it, global passive adversaries will be able to scoop up all of your encrypted traffic and decode it when their attacks or their computers are faster. This is actually happening.');
                $ephemeralKeysSupported['check'] = $vulnerable;
            }
            $ephemeralKeysSupported['btn'] = $this->generateBtnLink($this->l('Learn more'), 'https://www.howsmyssl.com/s/about.html#ephemeral-key-support');

            $sessionTicketSupported = [];
            $sessionTicketSupported['name'] = $this->l('Session Ticket Support');
            if (false === $data['session_ticket_supported']) {
                $sessionTicketSupported['description'] = $this->l('Session tickets are supported in your client. Services you use will be able to scale out their TLS connections more easily with this feature.');
                $sessionTicketSupported['check'] = $check;
            } else {
                $sessionTicketSupported['description'] = $this->l('Session tickets are not supported in your client. Without them, services will have a harder time making your client\'s connections fast. Generally, clients with ephemeral key support get this for free.');
                $sessionTicketSupported['check'] = $vulnerable;
            }
            $sessionTicketSupported['btn'] = $this->generateBtnLink($this->l('Learn more'), 'https://www.howsmyssl.com/s/about.html#session-ticket-support');

            $tlsCompressionSupported = [];
            $tlsCompressionSupported['name'] = $this->l('TLS Compression');
            if (true === $data['tls_compression_supported']) {
                $tlsCompressionSupported['description'] = $this->l('Your TLS client supports compressing the settings that encrypt your connection. This is really not good. It makes your TLS connections susceptible to the') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/CRIME', $this->l('CRIME attack')) . ' ' . $this->l('and your encrypted data could be leaked!');
                $tlsCompressionSupported['check'] = $vulnerable;
            } else {
                $tlsCompressionSupported['description'] = $this->l('Your TLS client does not attempt to compress the settings that encrypt your connection, avoiding information leaks from the') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/CRIME', $this->l('CRIME attack')) . '.';
                $tlsCompressionSupported['check'] = $check;
            }
            $tlsCompressionSupported['btn'] = $this->generateBtnLink($this->l('Learn more'), 'https://www.howsmyssl.com/s/about.html#tls-compression');

            $beastVuln = [];
            $beastVuln['name'] = $this->l('BEAST Vulnerability');
            if (true === $data['beast_vuln']) {
                if (true === $data['able_to_detect_n_minus_one_splitting']) {
                    $beastVuln['description'] = $this->l('Your client is open to the') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Transport_Layer_Security#BEAST_attack', $this->l('BEAST attack')) . '. ' . $this->l('It uses TLS 1.0 or earlier while also supporting a cipher suite that uses') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Block_cipher_mode_of_operation', $this->l('Cipher-Block Chaining')) . ' ' . $this->l('and does not implement the 1/n-1 record splitting mitigation. That combination will leak information.');
                    $beastVuln['check'] = $vulnerable;
                } else {
                    $beastVuln['description'] = $this->l('Your client is probably open to the') . $this->generateBtnLink('https://en.wikipedia.org/wiki/Transport_Layer_Security#BEAST_attack', $this->l('BEAST attack')) . ' ' . $this->l('because it uses TLS 1.0 or earlier while also supporting a cipher suite that uses') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Block_cipher_mode_of_operation', $this->l('Cipher-Block Chaining')) . '. ' . $this->l('However, the CBC cipher suites your client supports is not one How\'s My SSL is able to use, so it was unable to determine if your client implements the 1/n-1 record splitting mitigation. Clients with that uncommon of cipher suite selection rarely implement it, however, so it is best to assume the worst.');
                }
                $beastVuln['check'] = $check;
            } elseif (true === $data['able_to_detect_n_minus_one_splitting']) {
                $beastVuln['description'] = $this->l('Your client is not vulnerable to the') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Transport_Layer_Security#BEAST_attack', $this->l('BEAST attack')) . ' ' . $this->l('While it uses TLS 1.0 in conjunction with') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Block_cipher_mode_of_operation', $this->l('Cipher-Block Chaining')) . ' ' . $this->l('cipher suites, it has implemented the 1/n-1 record splitting mitigation.');
                $beastVuln['check'] = $check;
            } else {
                $beastVuln['description'] = $this->l('Your client is not vulnerable to the') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Transport_Layer_Security#BEAST_attack', $this->l('BEAST attack')) . ' ' . $this->l('because it uses a TLS protocol newer than TLS 1.0. The BEAST attack is only possible against clients using TLS 1.0 or earlier using') . ' ' . $this->generateLink('https://en.wikipedia.org/wiki/Block_cipher_mode_of_operation', $this->l('Cipher-Block Chaining')) . ' ' . $this->l('cipher suites that do not implement the 1/n-1 record splitting mitigation.');
                $beastVuln['check'] = $check;
            }
            $beastVuln['btn'] = $this->generateBtnLink($this->l('Learn more'), 'https://www.howsmyssl.com/s/about.html#beast-vulnerability');

            $insecureCipherSuites = [];
            $insecureCipherSuites['name'] = $this->l('Insecure Cipher Suites');
            if (false === empty($data['insecure_cipher_suites'])) {
                $insecureCipherSuitesKeys = $data['insecure_cipher_suites'];
                $resultInsecureCipherSuitesKeys = [];
                foreach ($insecureCipherSuitesKeys as $value => $key) {
                    $resultInsecureCipherSuitesKeys[] = $value . ' ' . $key[0] . '.';
                }
                $insecureCipherSuites['description'] = $this->l('Your client supports cipher suites that are known to be insecure') . ':<br>' . \implode('<br>', $resultInsecureCipherSuitesKeys);
                $insecureCipherSuites['check'] = $vulnerable;
            } else {
                $insecureCipherSuites['description'] = $this->l('Your client does not use any cipher suites that are known to be insecure.');
                $insecureCipherSuites['check'] = $check;
            }
            $insecureCipherSuites['btn'] = $this->generateBtnLink($this->l('Learn more'), 'https://www.howsmyssl.com/s/about.html#insecure-cipher-suites');

            $heartbleedVersionCheck = [];
            $heartbleedVersionCheck['name'] = $this->l('Heartbleed Vulnerability');
            $heartbleedVersionCheck['description'] = $this->l('The Heartbleed Bug is a serious vulnerability in the popular OpenSSL cryptographic software library. This weakness allows stealing the information protected, under normal conditions, by the SSL/TLS encryption used to secure the Internet.');
            if (true === $this->heartbleed()) {
                $heartbleedVersionCheck['check'] = $vulnerable;
            } else {
                $heartbleedVersionCheck['check'] = $check;
            }
            $heartbleedVersionCheck['btn'] = $this->generateBtnLink($this->l('Learn more'), 'https://heartbleed.com/');

            $cssInjectionVersionCheck = [];
            $cssInjectionVersionCheck['name'] = $this->l('CCS Injection Vulnerability');
            $cssInjectionVersionCheck['description'] = $this->l('OpenSSL\'s ChangeCipherSpec processing has a serious vulnerability. This vulnerability allows malicious intermediate nodes to intercept encrypted data and decrypt them while forcing SSL clients to use weak keys exposed to the malicious nodes.');
            if (true === $this->ccsInjection()) {
                $cssInjectionVersionCheck['check'] = $vulnerable;
            } else {
                $cssInjectionVersionCheck['check'] = $check;
            }
            $cssInjectionVersionCheck['btn'] = $this->generateBtnLink($this->l('Learn more'), 'http://ccsinjection.lepidum.co.jp/');

            $drownVersionCheck = [];
            $drownVersionCheck['name'] = $this->l('DROWN Vulnerability');
            $drownVersionCheck['description'] = $this->l('DROWN is a serious vulnerability that affects HTTPS and other services that rely on SSL and TLS, some of the essential cryptographic protocols for Internet security. These protocols allow everyone on the Internet to browse the web, use e-mail, shop online and send instant messages without third-parties being able to read the communication.');
            if (true === $this->sslv2Support($domain)) {
                $drownVersionCheck['check'] = $vulnerable;
            } else {
                $drownVersionCheck['check'] = $check;
            }
            $drownVersionCheck['btn'] = $this->generateBtnLink($this->l('Learn more'), 'https://drownattack.com/');

            $poodleVersionCheck = [];
            $poodleVersionCheck['name'] = $this->l('POODLE Vulnerability');
            $poodleVersionCheck['description'] = $this->l('The POODLE attack (Padding Oracle on Downgraded Legacy Encryption) exploits a vulnerability in the SSL 3.0 protocol. This vulnerability lets an attacker eavesdrop on communication encrypted using SSLv3. The vulnerability is no longer present in the Transport Layer Security protocol (TLS), the successor to SSL.');

            if (true === $this->sslv3Support($domain)) {
                $poodleVersionCheck['check'] = $vulnerable;
            } else {
                $poodleVersionCheck['check'] = $check;
            }
            $poodleVersionCheck['btn'] = $this->generateBtnLink($this->l('Learn more'), 'https://en.wikipedia.org/wiki/POODLE');

            $givenCipherSuites = [];
            $givenCipherSuites['name'] = $this->l('Given cipher suites');
            if (false === empty($data['given_cipher_suites'])) {
                $givenCipherSuites['description'] = $this->l('The cipher suites your client said it supports, in the order it sent them, are') . ':<br>' . \implode('<br>', $data['given_cipher_suites']);
            } else {
                $givenCipherSuites['description'] = $this->l('Your client does not use any cipher suites that are known to be insecure.');
            }
            $givenCipherSuites['check'] = $possible;
            $givenCipherSuites['btn'] = $this->generateBtnLink($this->l('Learn more'), 'https://www.howsmyssl.com/s/about.html#given-cipher-suites');

            $mixedContent = [];
            $mixedContent['name'] = $this->l('Mixed content');
            $mixedContent['description'] = $this->l('Mixed content occurs when initial HTML is loaded over a secure HTTPS connection. However, other resources (such as images, videos, stylesheets, scripts) are loaded over an insecure HTTP connection. This is called mixed content because both HTTP and HTTPS content are being loaded to display the same page, and the initial request was secure over HTTPS. Modern browsers display warnings about this type of content to indicate that this page contains insecure resources.');
            $mixedContent['check'] = $possible;
            $mixedContent['btn'] = $this->generateBtnLink($this->l('Scan for mixed content'), 'https://www.jitbit.com/sslcheck/#url=' . $host);

            $sslAnalyze = [];
            $sslAnalyze['name'] = $this->l('Analyze SSL/TLS');
            $sslAnalyze['description'] = $this->l('Scan your website with SSL Labs. It can give you a better understanding of how your SSL/TLS is deployed.');
            $sslAnalyze['check'] = $possible;
            $sslAnalyze['btn'] = $this->generateBtnLink($this->l('Analyze SSL/TLS'), 'https://www.ssllabs.com/ssltest/analyze?d=' . $domain . '&hideResults=on&ignoreMismatch=on&latest=yes');

            $certChecks = [
                [
                    $tlsVersion['name'],
                    $tlsVersion['description'],
                    $tlsVersion['check'],
                    $tlsVersion['btn'],
                ],
                [
                    $ephemeralKeysSupported['name'],
                    $ephemeralKeysSupported['description'],
                    $ephemeralKeysSupported['check'],
                    $ephemeralKeysSupported['btn'],
                ],
                [
                    $sessionTicketSupported['name'],
                    $sessionTicketSupported['description'],
                    $sessionTicketSupported['check'],
                    $sessionTicketSupported['btn'],
                ],
                [
                    $tlsCompressionSupported['name'],
                    $tlsCompressionSupported['description'],
                    $tlsCompressionSupported['check'],
                    $tlsCompressionSupported['btn'],
                ],
                [
                    $heartbleedVersionCheck['name'],
                    $heartbleedVersionCheck['description'],
                    $heartbleedVersionCheck['check'],
                    $heartbleedVersionCheck['btn'],
                ],
                [
                    $cssInjectionVersionCheck['name'],
                    $cssInjectionVersionCheck['description'],
                    $cssInjectionVersionCheck['check'],
                    $cssInjectionVersionCheck['btn'],
                ],
                [
                    $drownVersionCheck['name'],
                    $drownVersionCheck['description'],
                    $drownVersionCheck['check'],
                    $drownVersionCheck['btn'],
                ],
                [
                    $poodleVersionCheck['name'],
                    $poodleVersionCheck['description'],
                    $poodleVersionCheck['check'],
                    $poodleVersionCheck['btn'],
                ],
                [
                    $beastVuln['name'],
                    $beastVuln['description'],
                    $beastVuln['check'],
                    $beastVuln['btn'],
                ],
                [
                    $insecureCipherSuites['name'],
                    $insecureCipherSuites['description'],
                    $insecureCipherSuites['check'],
                    $insecureCipherSuites['btn'],
                ],
                [
                    $givenCipherSuites['name'],
                    $givenCipherSuites['description'],
                    $givenCipherSuites['check'],
                    $givenCipherSuites['btn'],
                ],
                [
                    $mixedContent['name'],
                    $mixedContent['description'],
                    $mixedContent['check'],
                    $mixedContent['btn'],
                ],
                [
                    $sslAnalyze['name'],
                    $sslAnalyze['description'],
                    $sslAnalyze['check'],
                    $sslAnalyze['btn'],
                ],
            ];

            $certResult = [];
            foreach ($certInfos as $certInfo => $key) {
                $certResult[] = [
                    $this->l('Title') => $certInfo,
                    $this->l('Description') => $key,
                ];
            }

            $checkResult = [];
            foreach ($certChecks as $certCheck) {
                $checkResult[] = [
                    $this->l('Title') => $certCheck[0],
                    $this->l('Check') => $certCheck[2],
                    $this->l('Description') => $certCheck[1],
                    null => '<span class="securitypro-position" style="padding: 10px 0 10px 0;">' . $certCheck[3] . '</span>',
                ];
            }

            $hstsPreloadApi = $this->cachedJsonDecodedContent('https://hstspreload.com/api/v1/status/' . $domain, null, 'HTSTS')['chrome'];

            if ($hstsPreloadApi === null) {
                $hstsPreload = false;
            } else {
                $hstsPreload = (bool) $hstsPreloadApi['present'];
            }

            $testTlsSettings = [
                [
                    $this->l('HSTS header'),
                    (true === $this->isSetHSTS($host)) ? $check : $vulnerable,
                    $this->l('HSTS (HTTP Strict Transport Security) protects from protocol downgrade attack and cookie hijacking. HSTS is a way for sites to elect always to use HTTPS. You can enable this feature in') . ' <a id="linkHttpHeaders" href="javascript:void(0)">' . $this->l('HSTS Security headers') . '</a>.',
                ],
                [
                    $this->l('HSTS listed'),
                    (true === $hstsPreload) ? $check : $vulnerable,
                    $this->l('Check if your site is listed on the preload list. It is best practice to be on the preload list. You need to submit your site to hstspreload.org to ensure that it is successfully preloaded, to get the full protection of the intended configuration.'),
                ],
                [
                    $this->l('Redirecting to HTTPS'),
                    (true === $this->isRedirectedToHttps()) ? $check : $vulnerable,
                    $this->l('Check if your web server automatically redirects visitors from HTTP to HTTPS. HTTPS gives your users a safe and secure connection to your website. It is recognizable by the padlock in your web browser.'),
                ],
                [
                    $this->l('Secure cookies'),
                    (false === $this->checkCookieSecure()[3]) ? $check : $vulnerable,
                    $this->l('Cookie secure specifies whether cookies should only be sent over secure connections. This setting requires SSL/TLS to be enabled.'),
                ],
            ];

            $testTls = [];
            foreach ($testTlsSettings as $testTlsSetting) {
                $testTls[] = [
                    $this->l('Title') => $testTlsSetting[0],
                    $this->l('Check') => $testTlsSetting[1],
                    $this->l('Description') => $testTlsSetting[2],
                ];
            }

            $result = $this->addHeading($this->l('Check implementation'), true) .
                $this->arrayToTable($testTls) . '<br>' .
                $this->addHeading($this->l('Basic information')) .
                $this->arrayToTable($certResult) . '<br>' .
                $this->addHeading($this->l('Analysis')) .
                $this->arrayToTable($checkResult);
        } else {
            $result = $this->addAlertWarning($this->l('You must install a TLS certificate and') . ' ' . $this->generateLink($this->adminLink('AdminPreferences'), $this->l('enable SSL everywhere')) . ' ' . $this->l('before the analysis can be performed.'));
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Analyze SSL/TLS'),
                    'icon' => 'icon-list',
                ],
                'description' => $this->l('All sites should be protected with HTTPS, even ones that do not handle sensitive data. This includes avoiding mixed content, where some resources are loaded over HTTP despite the initial request being served over HTTPS. HTTPS prevents intruders from tampering with or passively listening in on the communications between your app and your users and is a prerequisite for HTTP/2 and many new web platform APIs.'),
                'input' => [
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $result,
                        'col' => 12,
                        'name' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function fieldsFormAnalyzeModules()
    {
        $check = '<i class="icon icon-check" style="color: ' . self::COLOR_GREEN . '"></i>';
        $vulnerable = '<i class="icon icon-times" style="color: ' . self::COLOR_RED . '"></i>';

        $trusted = [];
        $result = null;

        // Not trusted modules
        if (null !== $this->getModules(false)) {
            foreach ($this->getModules(false) as $notTrustedModule) {
                $trusted[] = [
                    $this->l('Technical module name') => $notTrustedModule,
                    $this->l('Display module name') => Module::getModuleName($notTrustedModule),
                    $this->l('Trusted') => $vulnerable,
                ];
            }
        }

        // Trusted modules
        if (null !== $this->getModules(true)) {
            foreach ($this->getModules(true) as $trustedModule) {
                $trusted[] = [
                    $this->l('Technical module name') => $trustedModule,
                    $this->l('Display module name') => Module::getModuleName($trustedModule),
                    $this->l('Trusted') => $check,
                ];
            }
        }

        if (false === empty($trusted)) {
            $result = $this->arrayToTable($trusted);
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Analyze Modules'),
                    'icon' => 'icon-list',
                ],
                'description' => $this->l('Modules that are nonnative PrestaShop modules or are not bought from PrestaShop Addons are untrusted. This means that PrestaShop does not verify them. These modules can be safe even though PrestaShop does not verify them, but be careful - in some cases, these modules do not follow PrestaShop guidance and can be insecure. Generally, third party modules provide additional security risks. Sometimes websites are hacked though insecure third-party modules. If there are any modules that you do not need, it is recommended to uninstall them.'),
                'input' => [
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => $result,
                        'col' => 12,
                        'name' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * Display form for help.
     *
     * @return array
     */
    protected function fieldsFormAutoConfig()
    {
        $result = [];

        $result[] = $this->addHeading($this->l('Step 1: Configuration of Security Pro'), true);

        $result[] = $this->addParagraph($this->l('It is recommended doing a manual configuration of the module. However, if the many features seem overwhelming, you can run a basic auto-configuration of the module. Then you can afterwards fine-tune the settings depending on your needs.'));

        $result[] = $this->addParagraph($this->l('Before we go on it is highly recommended, to add the following keys at') . ' <a id="linkGeneralSettings" href="javascript:void(0)">' . $this->l('General Settings') . '</a>.');

        $accessAPI = [
            $this->l('Site key (reCAPTCHA v2)'),
            $this->l('Secret key (reCAPTCHA v2)'),
            $this->l('Honeypot API'),
        ];

        $result[] = '<ol style="font-size: 13px;"><li>' . \implode('</li><li>', $accessAPI) . '</li></ol>';

        $result[] = $this->btnAjax(
            'autoConfiguration',
            '<i class="icon icon-cog"></i> ' . $this->l('Run auto-configuration'),
            $this->l('Are you sure you want to run the auto-configuration? Your current settings of the module will be overridden.')
        );

        $result[] = $this->addHeading($this->l('Step 2: Fix vulnerabilities on your system'));

        $result[] = $this->addParagraph($this->l('Go to') . ' <a id="linkTools" href="javascript:void(0)">' . $this->l('Tools') . '</a>. ' . $this->l('There you will find tools to fix insecure file permissions, directory traversal vulnerability, and a tool to delete files that make your shop vulnerable. It is possible to generate a report, to understand what changes the tools will do.'));

        $result[] = $this->addHeading($this->l('Step 3: Analyze your system'));

        $result[] = $this->addParagraph($this->l('Go to') . ' <a id="linkAnalyzeSystem" href="javascript:void(0)">' . $this->l('Analyze System') . '</a> ' . $this->l('and fix as many vulnerabilities as possible.'));

        $result[] = $this->addHeading($this->l('Step 4: Analyze your server configuration'));

        $result[] = $this->addParagraph($this->l('Go to') . ' <a id="linkAnalyzeServerConfiguration" href="javascript:void(0)">' . $this->l('Analyze Server Configuration') . '</a> ' . $this->l('and have a look at the analysis. Here you will see some advanced tips to improve your PHP configuration file. If you are not familiar with this kind of configuration, you can ask your host for help.'));

        $result[] = $this->addHeading($this->l('Step 5: Analyze your modules'));

        $result[] = $this->addParagraph($this->l('Go to') . ' <a id="linkAnalyzeModules" href="javascript:void(0)">' . $this->l('Analyze Modules') . '</a>. ' . $this->l('Here you will see all modules installed in your shop. If you are not using some of the modules, it is recommended to uninstall them, especially if those modules are not trusted.'));

        $result[] = $this->addHeading($this->l('Step 6: Test your shop'));

        $result[] = $this->addParagraph($this->l('Now test your website to confirm that everything is running:'));

        $testWebsite = [
            $this->l('Register a new customer'),
            $this->l('Make a test order'),
            $this->l('Navigate to different products'),
            $this->l('Navigate to different categories'),
        ];

        $result[] = '<ol style="font-size: 13px;"><li>' . \implode('</li><li>', $testWebsite) . '</li></ol>';

        $result[] = $this->addHeading($this->l('Step 7: Setup cronjobs'));

        $result[] = $this->addParagraph($this->l('Go to the') . ' <a id="linkDashboard" href="javascript:void(0)">' . $this->l('Dashboard') . '</a>. ' . $this->l('There you will see a section named \'Cronjobs\'. Cronjobs are time-based job scheduler in Unix-like computer operating systems. The cronjobs are used to run features like the malware scanner, the monitoring service, backups, etc. It is recommended to set up these cronjobs to run once a day. If you are not familiar with cronjobs, you can ask your host for help.'));

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Documentation'),
                    'icon' => 'icon-book',
                ],
                'input' => [
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => \implode('', $result),
                        'col' => 12,
                        'name' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * Display form for help.
     *
     * @return array
     */
    protected function fieldsFormHelp()
    {
        $result = [];
        $result[] = $this->addParagraph($this->l('Thanks for using Security Pro! Questions, issues, or feature requests?'));

        $url = 'https://addons.prestashop.com/contact-form.php?id_product=44413';
        $result[] = $this->generateBtnLink('<i class="icon icon-envelope-o"></i> ' . $this->l('Contact module developer'), $url) . '<br><br>';

        $result[] = $this->addParagraph($this->l('Would you like to translate this module into your language or improve the wording?'));

        $translateLang = [
            $this->l('Click \'Translate\' (flag icon) in the upper right corner.'),
            $this->l('Choose language.'),
            $this->l('Make your changes and save.'),
        ];

        $result[] = '<ol style="font-size: 13px;"><li>' . \implode('</li><li>', $translateLang) . '</li></ol>';

        $result[] = $this->addParagraph($this->l('If you improve the wording, please export your translation and send it to the module developer. Your improvements will be merged into the next release. Your contribution is appreciated!'));

        $result[] = $this->btnAjax(
            'transDownload',
            '<i class="icon icon-download"></i> ' . $this->l('Export translations'),
            0,
            'TransDownload=1'
        );

        $result[] = '<br><br>';

        $result[] = $this->addParagraph($this->l('PrestaShop logs all errors in a folder along with some other logs. These logs can be useful for the developer if you have a problem with the module.'));

        $result[] = $this->btnAjax(
            'errorLogsDownload',
            '<i class="icon icon-download"></i> ' . $this->l('Export logs'),
            0,
            'ErrorLogsDownload=1'
        );

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Help'),
                    'icon' => 'icon-question-circle',
                ],
                'input' => [
                    [
                        'type' => 'html',
                        'label' => '',
                        'html_content' => \implode('', $result),
                        'col' => 12,
                        'name' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getAllLogs()
    {
        return [
            'PageNotFound' => self::LOG_PAGE_NOT_FOUND,
            'Firewall' => self::LOG_FIREWALL,
            'BruteForce' => self::LOG_BRUTE_FORCE,
            'MalwareScan' => self::LOG_MALWARE_SCAN,
            'FileChanges' => self::LOG_FILE_CHANGES,
            'LoginAttempts' => self::LOG_LOGIN_ATTEMPTS,
            'Cronjob' => self::LOG_CRONJOB,
        ];
    }

    /**
     * Get path to log file.
     *
     * @param string $fileName
     *
     * @return string
     */
    private function getLogFile($fileName)
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0')) {
            $filePath = '/log/';
        } elseif (Tools::version_compare(_PS_VERSION_, '1.7.3.0', '<=')) {
            $filePath = '/app/logs/';
        } else {
            $filePath = '/var/logs/';
        }

        $logPath = _PS_CORE_DIR_ . $filePath . $fileName;

        if (false === \is_dir(_PS_CORE_DIR_ . $filePath)) {
            \mkdir(_PS_CORE_DIR_ . $filePath, 0755, true);
        }

        if (false === \file_exists($logPath)) {
            \file_put_contents($logPath, '');
        }

        return $logPath;
    }

    /**
     * Configure form values.
     *
     * @return array
     */
    private function getConfigFormValues()
    {
        return [
            'PRO_GENERAL_EMAIL',
            'PRO_CLICK_JACKING',
            'PRO_X_XSS_PROTECTION',
            'PRO_X_CONTENT_TYPE_OPTIONS',
            'PRO_STRICT_TRANSPORT_SECURITY',
            'PRO_HSTS_SETTINGS_0',
            'PRO_HSTS_SETTINGS_1',
            'PRO_EXPECT_CT',
            'PRO_ACCESS_CONTROL_ALLOW_METHODS',
            'PRO_REFERRER_POLICY',
            'PRO_X_PERMITTED_CROSS_DOMAIN_POLICY',
            'PRO_X_DOWNLOAD_OPTIONS',
            'PRO_UNSET_HEADERS',
            'PRO_HTPASSWD',
            'PRO_HTPASSWD_USER',
            'PRO_HTPASSWD_PASS',
            'PRO_BAN_IP',
            'PRO_BAN_IP_ACTIVATE',
            'PRO_FAIL2BAN',
            'PRO_FAIL2BAN_LOG',
            'PRO_BAN_TIME',
            'PRO_MAX_RETRY',
            'PRO_FIND_TIME',
            'PRO_SEND_MAIL',
            'PRO_SEND_MAIL_LOGIN',
            'PRO_WHITELIST_IPS',
            'PRO_FILE_CHANGES_EMAIL',
            'PRO_FILE_CHANGES_LOG',
            'PRO_LOGIN_ATTEMPTS_LOG',
            'PRO_MALWARE_SCAN_EMAIL',
            'PRO_MALWARE_SCAN_LOG',
            'PRO_WHITELIST_MALWARE',
            'PRO_DISABLE_RIGHT_CLICK',
            'PRO_DISABLE_DRAG',
            'PRO_DISABLE_COPY',
            'PRO_DISABLE_CUT',
            'PRO_DISABLE_PRINT',
            'PRO_DISABLE_SAVE',
            'PRO_DISABLE_VIEW_PAGE_SOURCE',
            'PRO_DISABLE_CONSOLE',
            'PRO_DISABLE_TEXT_SELECTION',
            'PRO_ADMIN_DIRECTORY',
            'PRO_ADMIN_DIRECTORY_NAME',
            'PRO_BACKUP_DB_TOKEN',
            'PRO_BLOCK_ADD_TO_CART',
            'PRO_DELETE_OLD_CARTS',
            'PRO_DELETE_OLD_CARTS_DAYS',
            'PRO_ANTI_FLOOD',
            'PRO_ANTI_MAX_REQUESTS',
            'PRO_ANTI_REQ_TIMEOUT',
            'PRO_ANTI_BAN_TIME',
            'PRO_FIREWALL_RECAPTCHA_SECRET',
            'PRO_FIREWALL_RECAPTCHA_SITE_KEY',
            'PRO_RECAPTCHA_V3_SECRET',
            'PRO_RECAPTCHA_V3_SITE_KEY',
            'PRO_DISPLAY_RECAPTCHA_V3',
            'PRO_GOOGLE_SAFE_BROWSING_V4_API',
            'PRO_GOOGLE_SAFE_BROWSING_V4_ACTIVATE',
            'PRO_DISPOSABLE_EMAIL_PROVIDERS_ACTIVATE',
            'PRO_DISPOSABLE_EMAIL_PROVIDERS_REGISTRATION_ACTIVATE',
            'PRO_EMAIL_CHECKER_REGISTRATION_ACTIVATE',
            'PRO_EMAIL_CHECKER_CUSTOM_LIST_REGISTRATION',
            'PRO_EMAIL_CHECKER_ACTIVATE',
            'PRO_EMAIL_CHECKER_CUSTOM_LIST',
            'PRO_MESSAGE_CHECKER_ACTIVATE',
            'PRO_MESSAGE_CHECKER_CUSTOM_LIST',
            'PRO_HONEYPOT_API',
            'PRO_MONTASTIC_API',
            'PRO_FIREWALL_CHECK_BOT',
            'PRO_FIREWALL_SQL_CHECK',
            'PRO_FIREWALL_XXS_CHECK',
            'PRO_FIREWALL_SHELL_CHECK',
            'PRO_FIREWALL_XST_CHECK',
            'PRO_DIR_TRAVERSAL',
            'PRO_FIREWALL_RFI_CHECK',
            'PRO_BLOCK_FILE_UPLOAD',
            'PRO_BLOCK_SCAN_FILE_UPLOAD',
            'PRO_FIREWALL_LOG',
            'PRO_PASSWORD_GENERATOR',
            'PRO_BACKUP_DB',
            'PRO_BACKUP_DB_DROPBOX',
            'PRO_BACKUP_DB_GOOGLE_DRIVE',
            'PRO_BACKUP_FILES_GOOGLE_DRIVE',
            'PRO_GOOGLE_DRIVE_PROJECT_ID',
            'PRO_GOOGLE_DRIVE_CLIENT_ID',
            'PRO_GOOGLE_DRIVE_CLIENT_SECRET',
            'PRO_GOOGLE_DRIVE_AUTH',
            'PRO_BACKUP_DB_SAVED',
            'PRO_BACKUP_DB_PASSWORD',
            'PRO_BACKUP_FILE_SAVED',
            'PRO_BACKUP_FILE',
            'PRO_BACKUP_FILE_DROPBOX',
            'PRO_TWO_FACTOR_AUTH',
            'PRO_TWO_FACTOR_AUTH_FORCE',
            'PRO_TWO_FACTOR_AUTH_CODE',
            'PRO_TWO_FACTOR_AUTH_WHITELIST',
            'PRO_FIREWALL_WHITELIST',
            'PRO_FAKE_ACCOUNTS',
            'PRO_DISALLOW_URL_CUSTOMER_NAME',
            'PRO_WHITELIST_PROTECT_CONTENT',
            'PRO_BLOCK_USER_AGENT_ACTIVATE',
            'PRO_BLOCK_USER_AGENT',
            'PRO_BLOCK_TOR',
            'PRO_DISABLE_CONTACT_FORM',
            'PRO_RECAPTCHA_V3_CONTACT_ACTIVATE',
            'PRO_RECAPTCHA_V3_REGISTRATION_ACTIVATE',
            'PRO_RECAPTCHA_V3_THEME',
            'PRO_PAGE_NOT_FOUND_LOG',
            'PRO_PASSWORD_STRENGTHBAR',
            'PRO_SECURE_EXTERNAL_LINKS',
            'PRO_ANTI_FRAUD',
            'PRO_ANTI_FRAUD_UNIT',
            'PRO_ANTI_FRAUD_HOOK',
            'PRO_SERVER_IP',
            'PRO_SERVER_LOCATION',
            'PRO_SERVER_ISP',
            'PRO_TLS_EXPIRE',
            'PRO_STEALTH_LOGIN',
            'PRO_STEALTH_LOGIN_WHITELIST',
            'PRO_DEBUG_CRON',
            'PRO_BLOCK_EMAILS',
            'PRO_BLOCK_EMAILS_CUSTOM_LIST',
        ];
    }

    /**
     * Make a token/cookie check to prevent SPAM.
     *
     * @return bool
     */
    private function validateCookie()
    {
        if ($this->getCookieToken('CSRF') !== $this->context->cookie->__get('SecurityProCSRF')) {
            $this->context->controller->errors[] = $this->l('Invalid token.');

            return false;
        }

        return true;
    }

    /**
     * Generate a secure token for the cookie to ensure the cookie is not misused.
     *
     * @param string $value
     * @param bool $hash
     *
     * @return string
     */
    private function getCookieToken($value, $hash = false)
    {
        $string = $this->encrypt($value . (string) $this->getUserAgent() . (string) $this->getClientIP() . (string) \gethostname());

        if (true === $hash) {
            return \hash('sha512', $string);
        }

        return $string;
    }

    /**
     * Get user agent of client.
     *
     * @return string|false
     */
    private function getUserAgent()
    {
        return (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : false;
    }

    /**
     * Get client IP address.
     *
     * @return string
     */
    private function getClientIP()
    {
        return Tools::getRemoteAddr();
    }

    /**
     * Block request if customer name is an URL.
     *
     * @param string $firstname
     * @param string $lastname
     *
     * @return false|null
     */
    private function validateCustomerName($firstname, $lastname)
    {
        if (true === (bool) Validate::isAbsoluteUrl($firstname) || true === (bool) Validate::isAbsoluteUrl($lastname)) {
            $this->context->controller->errors[] = $this->l('Invalid account credentials.');

            return false;
        }
    }

    /**
     * Display errors for registration form.
     *
     * @return bool
     */
    private function displayRegistrationFormErrors()
    {
        $email = Tools::getValue('email');

        // Validate Google reCAPTCHA v3
        if (true === (bool) Configuration::get('PRO_RECAPTCHA_V3_REGISTRATION_ACTIVATE')) {
            if (false === empty(Configuration::get('PRO_RECAPTCHA_V3_SECRET'))) {
                $secretKey = Configuration::get('PRO_RECAPTCHA_V3_SECRET');
            } else {
                $secretKey = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';
            }

            $params = [
                'secret' => $secretKey,
                'response' => Tools::getValue('g-token'),
                'remoteip' => $this->getClientIP(),
            ];

            $ch = \curl_init();
            \curl_setopt($ch, \CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
            \curl_setopt($ch, \CURLOPT_POST, true);
            \curl_setopt($ch, \CURLOPT_POSTFIELDS, \http_build_query($params));
            \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
            \curl_setopt($ch, \CURLOPT_USERAGENT, self::USER_AGENT);
            $response = \curl_exec($ch);
            \curl_close($ch);

            $decode = \json_decode($response, true);

            if (isset($decode['success'])) {
                if (true !== (bool) $decode['success']) {
                    switch ($decode['error-codes'][0]) {
                        case 'missing-input-secret':
                            $this->context->controller->errors[] = $this->l('The secret parameter is missing.');
                            break;
                        case 'invalid-input-secret':
                            $this->context->controller->errors[] = $this->l('The secret parameter is invalid or malformed.');
                            break;
                        case 'missing-input-response':
                            $this->context->controller->errors[] = $this->l('The response parameter is missing.');
                            break;
                        case 'invalid-input-response':
                            $this->context->controller->errors[] = $this->l('The response parameter is invalid or malformed.');
                            break;
                        case 'bad-request':
                            $this->context->controller->errors[] = $this->l('The request is invalid or malformed.');
                            break;
                        case 'timeout-or-duplicate':
                            $this->context->controller->errors[] = $this->l('The response is no longer valid. Either is too old or has been used previously.');
                            break;

                        default:
                            $this->context->controller->errors[] = $this->l('The response is missing.');
                            break;
                    }
                }
            }

            if (isset($decode['score'])) {
                if ((float) $decode['score'] < (float) 0.6) {
                    $this->context->controller->errors[] = $this->l('The security trust score is too low.');
                }
            }
        }

        // Check disposable e-mail provider
        if (true === (bool) Configuration::get('PRO_DISPOSABLE_EMAIL_PROVIDERS_REGISTRATION_ACTIVATE')) {
            $checker = new \EmailChecker\EmailChecker();
            if (false === (bool) $checker->isValid($email)) {
                $this->context->controller->errors[] = $this->l('The e-mail address is not allowed.');
            }
        }

        // Check custom list of banned TLD
        if (true === (bool) Configuration::get('PRO_EMAIL_CHECKER_REGISTRATION_ACTIVATE')) {
            $domain = \explode('@', $email);
            $domain = \explode('.', $domain[1]);
            \array_shift($domain);
            $domainTld = \implode('.', $domain);
            $blacklist = \explode(',', Configuration::get('PRO_EMAIL_CHECKER_CUSTOM_LIST_REGISTRATION'));
            if (true === \in_array($domainTld, $blacklist, true)) {
                $this->context->controller->errors[] = $this->l('The e-mail address is not allowed.');
            }
        }

        if (false === empty($this->context->controller->errors)) {
            return false;
        }

        return true;
    }

    /**
     * Get admin order information and prepare them for templates.
     *
     * @param int $idOrder
     * @param int $column
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @return Smarty
     */
    private function adminOrderInformation($idOrder, $column)
    {
        $order = new Order($idOrder);
        $orderId = $order->id;

        $sql = new DbQuery();
        $sql->select('ip, ua, proxy');
        $sql->from('securitypro_af');
        $sql->where('id_order = ' . (int) $orderId);
        $row = Db::getInstance()->executeS($sql);

        if (false === empty($row)) {
            $ip = $row[0]['ip']; // Get Order IP
            $userAgent = $row[0]['ua']; // Get Order UA
            $proxy = $row[0]['proxy']; // Check if IP is a proxy
        } else {
            $ip = null;
            $userAgent = null;
            $proxy = null;
        }

        $addressDelivery = new Address($order->id_address_delivery);
        $cityDelivery = $addressDelivery->city; // Get Order City
        $zipDelivery = $addressDelivery->postcode; // Get Order Postcode
        $countryDelivery = $addressDelivery->country; // Get Order Country
        $streetDelivery = $addressDelivery->address1; // Get Order Address

        $addressInvoice = new Address($order->id_address_invoice);
        $cityInvoice = $addressInvoice->city; // Get Order City
        $zipInvoice = $addressInvoice->postcode; // Get Order Postcode
        $countryInvoice = $addressInvoice->country; // Get Order Country
        $streetInvoice = $addressInvoice->address1;  // Get Order Address

        $customer = new Customer($order->id_customer);
        $email = $customer->email; // Get Customer Email

        if (false === empty($row)) {
            $url = 'https://www.iplocate.io/api/lookup/' . $ip; // Lookup IP
            $ipLookup = $this->cachedJsonDecodedContent($url, null, $ip, 2629746);
        } else {
            $ipLookup = false;
        }

        if (false !== $ipLookup) {
            $ipCountry = $ipLookup['country'];
            $ipLatitude = $ipLookup['latitude'];
            $ipLongitude = $ipLookup['longitude'];
        } else {
            $ipCountry = null;
            $ipLatitude = null;
            $ipLongitude = null;
        }

        // Get distance
        $deliveryCoordinates = $this->coordinates($streetDelivery, $zipDelivery, $cityDelivery, $countryDelivery);

        $invoiceCoordinates = $this->coordinates($streetInvoice, $zipInvoice, $cityInvoice, $countryInvoice);

        $distanceUnit = Configuration::get('PRO_ANTI_FRAUD_UNIT');

        $distanceDeliveryInvoice = $this->distance($deliveryCoordinates['lat'], $deliveryCoordinates['lon'], $invoiceCoordinates['lat'], $invoiceCoordinates['lon'], $distanceUnit) . ' ' . $distanceUnit;

        if (null !== $ipCountry) {
            $distanceDeliveryIp = $this->distance($deliveryCoordinates['lat'], $deliveryCoordinates['lon'], $ipLatitude, $ipLongitude, $distanceUnit) . ' ' . $distanceUnit;
        } else {
            $distanceDeliveryIp = null;
        }

        // Check disposable e-mail provider
        $checker = new \EmailChecker\EmailChecker();

        if (true === (bool) $checker->isValid($email)) {
            $checkEmail = false;
        } else {
            $checkEmail = true;
        }

        // Check if proxy
        if (1 === (int) $proxy) {
            $isProxy = $this->l('Yes');
        } elseif (0 === (int) $proxy) {
            $isProxy = $this->l('No');
        } else {
            $isProxy = $this->l('Unknown');
        }

        // Check if bot
        if (null !== $ip) {
            if (true === $this->isBot($ip)) {
                $isRobot = $this->l('Yes');
            } elseif (false === $this->isBot($ip)) {
                $isRobot = $this->l('No');
            }
        } else {
            $isRobot = $this->l('Unknown');
        }

        // Check if TOR
        if (null !== $ip) {
            if (true === $this->isTorExitPoint($ip)) {
                $isTor = $this->l('Yes');
                $checkTor = true;
            } elseif (false === $this->isTorExitPoint($ip)) {
                $isTor = $this->l('No');
                $checkTor = false;
            }
        } else {
            $isTor = $this->l('Unknown');
        }

        // Check if high risk country
        $isHighRiskCountry = [
            'AF',
            'AL',
            'AO',
            'AS',
            'BB',
            'BI',
            'BS',
            'BW',
            'CF',
            'CG',
            'CU',
            'ER',
            'FJ',
            'GH',
            'GU',
            'GW',
            'IQ',
            'IR',
            'IS',
            'JM',
            'KH',
            'KP',
            'KY',
            'LB',
            'LR',
            'LY',
            'ML',
            'MM',
            'MN',
            'MU',
            'NI',
            'OM',
            'PA',
            'PK',
            'PS',
            'PW',
            'SC',
            'SD',
            'SL',
            'SO',
            'SY',
            'TT',
            'UG',
            'VE',
            'VI',
            'VU',
            'WS',
            'YE',
            'ZW',
        ];

        if (\in_array($this->countryCodeByCountry($countryDelivery), $isHighRiskCountry, true)) {
            $isRisky = true;
        } else {
            $isRisky = false;
        }

        $customersWithSameIp = null;
        $countOrders = 0;

        if (null !== $ip) {
            // Check if the IP is used elsewhere
            $sql2 = new DbQuery();
            $sql2->select('id_order');
            $sql2->from('securitypro_af');
            $sql2->where('ip = "' . pSQL($ip) . '"');
            $row2 = Db::getInstance()->executeS($sql2);
            $idOrder = [];
            foreach ($row2 as $key) {
                $idOrder[] = $key['id_order'];
            }

            $customers = [];
            $orders = [];
            $countOrders = 1;

            // Get token customer
            $adminLinkCustomer = $this->adminLink('AdminCustomers');
            $parseUrlCustomer = \parse_url($adminLinkCustomer, \PHP_URL_QUERY);

            \parse_str($parseUrlCustomer, $getTokenCustomer);

            if (isset($getTokenCustomer['_token'])) {
                $tokenCustomer = $getTokenCustomer['_token'];
            } elseif (isset($getTokenCustomer['token'])) {
                $tokenCustomer = $getTokenCustomer['token'];
            } else {
                $tokenCustomer = '';
            }

            // Get token orders
            $adminLinkOrders = $this->adminLink('AdminOrders');
            $parseUrlOrders = \parse_url($adminLinkOrders, \PHP_URL_QUERY);

            \parse_str($parseUrlOrders, $getTokenOrders);

            if (isset($getTokenOrders['_token'])) {
                $tokenOrders = $getTokenOrders['_token'];
            } elseif (isset($getTokenOrders['token'])) {
                $tokenOrders = $getTokenOrders['token'];
            } else {
                $tokenOrders = '';
            }

            foreach ($idOrder as $value) {
                $order = new Order((int) ($value));
                $address = new Address((int) ($order->id_address_delivery));
                $customer = new Customer((int) ($address->id_customer));

                if ((int) $customer->id !== (int) $customer->id) {
                    $urlCustomers = \strtok($adminLinkCustomer, '?');
                    $customerId = $customer->id;

                    if (Tools::version_compare(_PS_VERSION_, '1.7.0.0')) { // PS 1.6
                        $customers[] = $this->generateLink($adminLinkCustomer . '&viewcustomer&id_customer=' . $customerId, '#' . $customerId);
                    } else { // PS 1.7
                        $customers[] = $this->generateLink($urlCustomers . $customerId . '/view?_token=' . $tokenCustomer, '#' . $customerId);
                    }
                }

                if ((int) $order->id !== (int) $order->id) {
                    $urlOrders = \strtok($adminLinkOrders, '?');
                    $ordersId = $order->id;

                    if (Tools::version_compare(_PS_VERSION_, '1.7.7.0')) { // PS 1.6
                        $orders[] = $this->generateLink($adminLinkOrders . '&vieworder&id_order=' . $ordersId, '#' . $ordersId);
                    } else { // PS 1.7
                        $orders[] = $this->generateLink($urlOrders . $ordersId . '/view?_token=' . $tokenOrders, '#' . $ordersId);
                    }

                    ++$countOrders;
                }
            }

            if (false === empty($customers)) {
                $customersWithSameIp = \implode(', ', \array_unique($customers));
            } else {
                $customersWithSameIp = null;
            }

            if (false === empty($orders)) {
                $ordersWithSameIp = \implode(', ', \array_unique($orders));
            } else {
                $ordersWithSameIp = null;
            }
        }

        // Count score
        $score = 0;

        if ($ipCountry !== $countryDelivery) {
            ++$score;
        }

        if ($countryDelivery !== $countryInvoice) {
            $score += 5;
        }

        if (true === $checkEmail) {
            $score += 5;
        }

        if (1 === $proxy) {
            ++$score;
        }

        if (false === empty($checkTor)) {
            if (true === $checkTor) {
                $score += 4;
            }
        }

        if (true === $isRisky) {
            $score += 6;
        }

        if ($customersWithSameIp > 1) {
            ++$score;
        }

        if ((int) $distanceDeliveryInvoice > 50) {
            ++$score;
        }

        if (null !== $ipCountry) {
            if ((int) $distanceDeliveryIp > 1000) {
                ++$score;
            }
        }

        if ($score < 4) { // 0-3
            $scoreText = $this->l('low risk');
        } elseif ($score < 6) { // 4-5
            $scoreText = $this->l('medium risk');
        } else { // 6+
            $scoreText = $this->l('high risk');
        }

        return $this->context->smarty->assign([
            'sp_score' => $scoreText,
            'sp_ip' => (false === empty($ip)) ? $ip : $this->l('Unknown'),
            'sp_ipCountry' => (false === empty($ipCountry)) ? $ipCountry : $this->l('Unknown'),
            'sp_validateEmail' => (true === $checkEmail) ? $this->l('Yes') : $this->l('No'),
            'sp_isProxy' => $isProxy,
            'sp_os' => $this->getPlatform($userAgent),
            'sp_browser' => $this->getBrowser($userAgent),
            'sp_type' => $this->getDevice($userAgent),
            'sp_isBot' => $isRobot,
            'sp_isTor' => $isTor,
            'sp_risky' => (true === $isRisky) ? $this->l('Yes') : $this->l('No'),
            'sp_distanceDeliveryInvoice' => $distanceDeliveryInvoice,
            'sp_distanceDeliveryIp' => (false === empty($distanceDeliveryIp)) ? $distanceDeliveryIp : $this->l('Unknown'),
            'sp_countOrders' => ($countOrders > 0) ? $countOrders : $this->l('Unknown'),
            'sp_customersWithSameIp' => (null !== $customersWithSameIp) ? $customersWithSameIp : $this->l('No'),
            'sp_ordersWithSameIP' => (false === empty($ordersWithSameIp)) ? $ordersWithSameIp : $this->l('No'),
            'sp_column' => $column,
        ]);
    }

    /**
     * Cache a json request. Default cache-time is 24 hours.
     *
     * @param string $url
     * @param array $params
     * @param string $name
     * @param int $cacheTime
     *
     * @return array|false
     */
    private function cachedJsonDecodedContent($url, $params, $name, $cacheTime = 86400)
    {
        $cachePath = _PS_CACHE_DIR_ . $this->name;
        $filename = $cachePath . \DIRECTORY_SEPARATOR . $this->encrypt($name) . '.json';

        if (\file_exists($filename) && (\time() - $cacheTime < \filemtime($filename))) {
            return \json_decode(Tools::file_get_contents($filename), true);
        }

        $content = $this->remoteContent($url, $params);

        if (false !== $content) {
            if (false === \is_dir($cachePath)) {
                \mkdir($cachePath, 0755, true);
            }
            \file_put_contents($filename, $content); // Save cache

            return \json_decode($content, true);
        }

        if (\file_exists($filename)) {
            return \json_decode(Tools::file_get_contents($filename), true); // If the response is false, we want to use the cached version even though it is outdated.
        }

        return false; // If the connection fails to the API and no cache is stored.
    }

    /**
     * Download content with cURL.
     *
     * @param string $url
     * @param array|null $params
     *
     * @return false|string
     */
    private function remoteContent($url, $params = null)
    {
        $options = [
            \CURLOPT_URL => (null !== $params) ? \sprintf('%s?%s', $url, \http_build_query($params)) : $url,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_HEADER => false,
            \CURLOPT_FOLLOWLOCATION => true,
            \CURLOPT_ENCODING => '',
            \CURLOPT_REFERER => '',
            \CURLOPT_CONNECTTIMEOUT => 5,
            \CURLOPT_TIMEOUT => 60,
            \CURLOPT_MAXREDIRS => 5,
            \CURLOPT_SSL_VERIFYPEER => false,
            \CURLOPT_USERAGENT => self::USER_AGENT,
        ];

        $ch = \curl_init();
        \curl_setopt_array($ch, $options);
        $result = \curl_exec($ch);
        $error = \curl_error($ch);
        \curl_close($ch);

        if (true === (bool) $error) {
            return false; // Error
        }

        return $result;
    }

    /**
     * Get coordinates.
     *
     * @param string $street
     * @param string $zip
     * @param string $city
     * @param string $country
     *
     * @return array
     */
    private function coordinates($street, $zip, $city, $country)
    {
        $url = 'https://geocode.xyz';

        $params = [
            'scantext' => \implode('+', [$street, $zip, $city]),
            'region' => $this->countryCodeByCountry($country),
            'geojson' => '1',
        ];

        $content = $this->cachedJsonDecodedContent($url, $params, $params['scantext'], 3600)['features'][0]['geometry']['coordinates'];

        return [
            'lat' => $content[1],
            'lon' => $content[0],
        ];
    }

    /**
     * Get country-code by country.
     *
     * @param string $country
     *
     * @return array|false|string
     */
    private function countryCodeByCountry($country)
    {
        $sqlId = new DbQuery();
        $sqlId->select('id_country');
        $sqlId->from('country_lang');
        $sqlId->where('id_lang = 1');
        $sqlId->where('name = "' . pSQL($country) . '"');
        $idCountry = Db::getInstance()->getValue($sqlId);

        $sqlIso = new DbQuery();
        $sqlIso->select('iso_code');
        $sqlIso->from('country');
        $sqlIso->where('id_country = ' . (int) $idCountry);

        return Db::getInstance()->getValue($sqlIso);
    }

    /**
     * Return distance in km.
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @param string $unit
     *
     * @return float
     */
    private function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        if (($lat1 === $lat2) && ($lon1 === $lon2)) {
            return 0;
        }

        $theta = $lon1 - $lon2;
        $distance = \sin(\deg2rad($lat1)) * \sin(\deg2rad($lat2)) + \cos(\deg2rad($lat1)) * \cos(\deg2rad($lat2)) * \cos(\deg2rad($theta));
        $distance = \acos(\rad2deg($distance));

        if ('mi' === $unit) {
            $factor = 0.621371;
        } else {
            $factor = 1; // km
        }

        return (float) \round($distance * 60 * 1.1515 * 1.609344 * $factor, 1);
    }

    /**
     * Check if the client is a bot (Honeypot API).
     *
     * @param string $ip
     *
     * @return bool
     */
    private function isBot($ip)
    {
        $honeypotQuery = $this->honeypotQuery($ip);
        if (false !== $honeypotQuery) {
            if (0 === $honeypotQuery) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Make the Honeypot query (Honeypot API).
     *
     * @param string $ip
     *
     * @return int
     */
    private function honeypotQuery($ip)
    {
        $apiKey = Configuration::get('PRO_HONEYPOT_API');

        $result = \explode('.', \gethostbyname($apiKey . '.' . \implode('.', \array_reverse(\explode('.', $ip))) . '.dnsbl.httpbl.org'));

        if ('127' !== (string) $result[0]) {
            return false; // Not a threat
        }

        return (int) $result[3];
    }

    /**
     * Check if the IP is a TOR.
     *
     * @param string $ip
     *
     * @return bool
     */
    private function isTorExitPoint($ip)
    {
        $url = 'https://check.torproject.org/torbulkexitlist';

        $content = $this->cachedRemoteContent($url, null, 'tor');

        if (false !== Tools::strpos($content, $ip)) {
            return true; // Is tor
        }

        return false; // Is not tor
    }

    /**
     * Cache a request. Default cache-time is 24 hours.
     *
     * @param string $url
     * @param array $params
     * @param string $name
     * @param int $cacheTime
     *
     * @return string
     */
    private function cachedRemoteContent($url, $params, $name, $cacheTime = 86400)
    {
        $cachePath = _PS_CACHE_DIR_ . $this->name;
        $filename = $cachePath . \DIRECTORY_SEPARATOR . $this->encrypt($name) . '.txt';

        if (\file_exists($filename) && (\time() - $cacheTime < \filemtime($filename))) {
            return Tools::file_get_contents($filename);
        }

        $content = $this->remoteContent($url, $params);

        if (false !== $content) {
            if (false === \is_dir($cachePath)) {
                \mkdir($cachePath, 0755, true);
            }

            // Remove empty lines
            $content = \preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $content);

            \file_put_contents($filename, $content); // Save cache

            return $content;
        }

        if (\file_exists($filename)) {
            return Tools::file_get_contents($filename); // If the response is false, we want to use the cached version even though it is outdated.
        }

        return false; // If the connection fails to the API and no cache is stored.
    }

    /**
     * Get admin link.
     *
     * @param string $controller
     * @param bool $withToken
     *
     * @throws PrestaShopException
     *
     * @return string
     */
    private function adminLink($controller, $withToken = true)
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return $this->context->link->getAdminLink($controller, $withToken);
        }

        return $this->getBaseURL() . \basename(_PS_ADMIN_DIR_) . '/' . $this->context->link->getAdminLink($controller, $withToken);
    }

    /**
     * Get base URL. (Eg. https://google.com/uri/)
     *
     * @return string
     */
    private function getBaseURL()
    {
        return $this->getUrl() . __PS_BASE_URI__;
    }

    /**
     * Get URL. Eg. (https://google.com)
     *
     * @return string
     */
    private function getUrl()
    {
        return Tools::getHttpHost(true, true, true);
    }

    /**
     * @param string $link
     * @param bool|null $target
     * @param bool $blank
     *
     * @return string
     */
    private function generateLink($link, $target = null, $blank = true)
    {
        if (null === $target) {
            $target = $link;
        }

        if (true === $blank) {
            return '<a href="' . $link . '" target="_blank" rel="noopener noreferrer nofollow">' . $target . '</a>';
        }

        return '<a href="' . $link . '" rel="noopener noreferrer nofollow">' . $link . '</a>';
    }

    /**
     * Get platform by user agent.
     *
     * @param string $userAgent
     *
     * @return string
     */
    private function getPlatform($userAgent)
    {
        if (false !== (bool) $userAgent) {
            $agent = new \Jenssegers\Agent\Agent();
            $agent->setUserAgent($userAgent);

            $platform = $agent->platform();
            $version = $agent->version($platform);

            if (true === (bool) $platform) {
                return $platform . ' ' . $version;
            }
        }

        return $this->l('Unknown');
    }

    /**
     * Get browser by user agent.
     *
     * @param string $userAgent
     *
     * @return string
     */
    private function getBrowser($userAgent)
    {
        if (false !== (bool) $userAgent) {
            $agent = new \Jenssegers\Agent\Agent();
            $agent->setUserAgent($userAgent);

            $browser = $agent->browser();
            $version = $agent->version($browser);

            if (true === (bool) $browser) {
                return $browser . ' ' . $version;
            }
        }

        return $this->l('Unknown');
    }

    /**
     * Get device by user agent.
     *
     * @param string $userAgent
     *
     * @return string
     */
    private function getDevice($userAgent)
    {
        if (false !== (bool) $userAgent) {
            $agent = new \Jenssegers\Agent\Agent();
            $agent->setUserAgent($userAgent);

            if (true === (bool) $agent->isMobile()) {
                return $this->l('Mobile');
            }

            if (true === (bool) $agent->isTablet()) {
                return $this->l('Tablet');
            }

            if (true === (bool) $agent->isDesktop()) {
                return $this->l('Desktop');
            }
        }

        return $this->l('Unknown');
    }

    /**
     * @return bool
     */
    private function checkMaintenanceMode()
    {
        if (true === (bool) Configuration::get('PS_SHOP_ENABLE')) {
            return false;
        }

        $input = \preg_replace('/\s+/', '', Configuration::get('PS_MAINTENANCE_IP'));
        $input = \preg_replace('/,,+/', ',', $input);
        $input = \explode(',', $input);

        if (\in_array($this->getClientIP(), $input, true)) {
            return false;
        }

        return true;
    }

    /**
     * @param $msg
     *
     * @return string
     */
    private function displayModernError($msg)
    {
        return '<script>$.growl.error({duration: 120000, title: "", message: "' . $msg . '" });</script>';
    }

    /**
     * Download file.
     *
     * @param string $filePath
     * @param bool $deleteFile
     *
     * @return bool|null
     */
    private function downloadFile($filePath, $deleteFile = false)
    {
        if (true === self::DEMO_MODE) {
            return false;
        }

        if (\file_exists($filePath)) {
            \header('Content-Description: File Transfer');
            \header('Content-Type: application/x-octet-stream');
            \header('Content-Disposition: attachment; filename="' . \basename($filePath) . '"');
            \header('Expires: 0');
            \header('Cache-Control: must-revalidate');
            \header('Pragma: public');
            \header('Content-Length: ' . \filesize($filePath));
            \flush(); // Flush system output buffer
            \readfile($filePath);

            if (true === $deleteFile) {
                Tools::deleteFile($filePath);
            }

            exit;
        }

        return false;
    }

    /**
     * @return \Dropbox\Dropbox
     */
    private function dropboxGetClient()
    {
        return new \Dropbox\Dropbox(Configuration::get('PRO_BACKUP_DB_TOKEN'));
    }

    /**
     * @param $id
     *
     * @throws \Google\Exception
     *
     * @return false
     */
    private function googleDriveDeleteFile($id)
    {
        if (false === $this->googleDriveGetClient()) {
            return false;
        }
        $client = $this->googleDriveGetClient();

        $googleServiceDrive = new Google_Service_Drive($client);

        try {
            $googleServiceDrive->files->delete($id);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @throws \Google\Exception
     *
     * @return false|Google_Client
     */
    private function googleDriveGetClient()
    {
        if (false === $this->googleDriveCreateCredentials()) {
            return false;
        }

        $client = new Google_Client();
        $client->setApplicationName('Security Pro');
        $client->setScopes(Google_Service_Drive::DRIVE);
        $client->setAuthConfig(_PS_MODULE_DIR_ . $this->name . self::DIR_DATA . \DIRECTORY_SEPARATOR . $this->encrypt('credentials') . '.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $tokenPath = _PS_MODULE_DIR_ . $this->name . self::DIR_DATA . \DIRECTORY_SEPARATOR . $this->encrypt('token') . '.json';
        if (true === \file_exists($tokenPath)) {
            $accessToken = \json_decode(Tools::file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                $authCode = Configuration::get('PRO_GOOGLE_DRIVE_AUTH');

                try {
                    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                    $client->setAccessToken($accessToken);
                } catch (Exception $e) {
                    return false;
                }
            }
            // Save the token to a file.
            \file_put_contents($tokenPath, \json_encode($client->getAccessToken()));
        }

        if (\file_exists($tokenPath)) {
            return $client;
        }
    }

    /**
     * @return false
     */
    private function googleDriveCreateCredentials()
    {
        $clientId = Configuration::get('PRO_GOOGLE_DRIVE_CLIENT_ID');
        $projectId = Configuration::get('PRO_GOOGLE_DRIVE_PROJECT_ID');
        $clientSecret = configuration::get('PRO_GOOGLE_DRIVE_CLIENT_SECRET');

        if (true === empty($clientId)
            || true === empty($projectId)
            || true === empty($clientSecret)) {
            return false;
        }

        $config = [
            'installed' => [
                'client_id' => $clientId,
                'project_id' => $projectId,
                'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
                'token_uri' => 'https://oauth2.googleapis.com/token',
                'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
                'client_secret' => $clientSecret,
                'redirect_uris' => [
                    'urn:ietf:wg:oauth:2.0:oob',
                    $this->getBaseURL(),
                ],
            ],
        ];

        $jsonConfig = \json_encode($config);

        $configFilePath = _PS_MODULE_DIR_ . $this->name . self::DIR_DATA;
        if (false === \is_dir($configFilePath)) {
            \mkdir($configFilePath, 0755, true);
            $this->addIndexRecursively($configFilePath);
            \file_put_contents($configFilePath . '/.htaccess', $this->getHtaccessContent());
        }

        \file_put_contents(_PS_MODULE_DIR_ . $this->name . self::DIR_DATA . \DIRECTORY_SEPARATOR . $this->encrypt('credentials') . '.json', $jsonConfig);
    }

    /**
     * Add missing index.php files.
     *
     * @param string $path
     * @param bool $analyze
     */
    private function addIndexRecursively($path, $analyze = false)
    {
        if (0 === Tools::strpos(\basename($path), '.')) {
            return;
        }

        $indexFilePath = $path . \DIRECTORY_SEPARATOR . 'index.php';

        if (false === \file_exists($indexFilePath)) {
            if (true === $analyze) {
                $reportPath = _PS_MODULE_DIR_;
                if (false === \is_dir($reportPath)) {
                    \mkdir($reportPath, 0755, true);
                }
                \file_put_contents($reportPath . \DIRECTORY_SEPARATOR . self::REPORT_CREATE_INDEX, \realpath($path) . \PHP_EOL, \FILE_APPEND | \LOCK_EX);
            } else {
                \file_put_contents($indexFilePath, Tools::getDefaultIndexContent());
            }
        }

        $dirs = \glob($path . \DIRECTORY_SEPARATOR . '*', \GLOB_ONLYDIR);

        if (false === $dirs) {
            return;
        }

        foreach ($dirs as $dir) {
            if (true === $analyze) {
                $this->addIndexRecursively($dir, true);
            } else {
                $this->addIndexRecursively($dir);
            }
        }
    }

    /**
     * @return string
     */
    private function getHtaccessContent()
    {
        return '# Apache 2.2
<IfModule !mod_authz_core.c>
    Order deny,allow
    Deny from all
</IfModule>

# Apache 2.4
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
';
    }

    /**
     * @throws \Google\Exception
     *
     * @return false|\GuzzleHttp\Psr7\Request
     */
    private function googleDriveEmptyTrash()
    {
        if (false === $this->googleDriveGetClient()) {
            return false;
        }
        $client = $this->googleDriveGetClient();

        $googleServiceDrive = new Google_Service_Drive($client);

        try {
            return $googleServiceDrive->files->emptyTrash();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $id
     * @param $name
     * @param $output
     *
     * @throws \Google\Exception
     *
     * @return false
     */
    private function googleDriveDownloadFile($id, $name, $output)
    {
        if (false === $this->googleDriveGetClient()) {
            return false;
        }
        $client = $this->googleDriveGetClient();

        $api = new Google_Service_Drive($client);

        $optParams = [
            'alt' => 'media',
        ];

        try {
            $content = $api->files->get($id, $optParams);
        } catch (Exception $e) {
            return false;
        }

        $outHandle = \fopen($output . $name, 'w+b');

        while (false === $content->getBody()->eof()) {
            \fwrite($outHandle, $content->getBody()->read(1024));
        }

        \fclose($outHandle);
    }

    /**
     * @param $msg
     *
     * @return string
     */
    private function displayModernConfirmation($msg)
    {
        return '<script>$.growl.notice({duration: 5000, title: "", message: "' . $msg . '" });</script>';
    }

    /**
     * Analyze file- and directory permissions.
     *
     * @param string $dir
     */
    private function chmodFileFolderAnalyze($dir)
    {
        $perms = [];
        $perms['file'] = 0644;
        $perms['folder'] = 0755;
        $dh = @\opendir($dir);

        $reportPath = _PS_MODULE_DIR_ . self::REPORT_PERMISSIONS;

        if ($dh) {
            $myfile = \fopen($reportPath, 'ab');
            while (false !== ($file = \readdir($dh))) {
                if ('.' !== $file && '..' !== $file) {
                    $fullpath = $dir . '/' . $file;
                    if (false === \is_dir($fullpath)) {
                        if (Tools::substr(\sprintf('%o', \fileperms($fullpath)), -3) !== \decoct($perms['file'])) {
                            \fwrite($myfile, '[' . Tools::substr(\decoct(\fileperms($fullpath)), -3) . '] ' . $fullpath . \PHP_EOL);
                        }
                    } elseif (Tools::substr(\sprintf('%o', \fileperms($fullpath)), -3) !== \decoct($perms['folder'])) {
                        \fwrite($myfile, '[' . Tools::substr(\decoct(\fileperms($fullpath)), -3) . '] ' . $fullpath . \PHP_EOL);

                        $this->chmodFileFolderAnalyze($fullpath);
                    }
                }
            }
            \fclose($myfile);
            \closedir($dh);
        }
    }

    /**
     * Fix file- and directory permissions.
     *
     * @param string $dir
     */
    public function chmodFileFolderFix($dir)
    {
        $perms = [];
        $perms['file'] = 0644;
        $perms['folder'] = 0755;

        $dh = @opendir($dir);

        if ($dh) {
            while (false !== ($file = readdir($dh))) {
                if ('.' != $file && '..' != $file) {
                    $fullpath = $dir . '/' . $file;
                    if (!is_dir($fullpath)) {
                        chmod($fullpath, $perms['file']);
                    } else {
                        if (chmod($fullpath, $perms['folder'])) {
                            $this->chmodFileFolderFix($fullpath);
                        }
                    }
                }
            }
            closedir($dh);
        }
    }

    private function removeFilesAnalyze()
    {
        $elements = \array_merge(
            $this->blacklistedFilesRoot(),
            $this->checkFilesCVE20179841(),
            $this->getFilePathExt(_PS_MODULE_DIR_),
            $this->getFilePathExt(_PS_ROOT_DIR_)
        );

        if (false === empty($elements)) {
            $reportPath = _PS_MODULE_DIR_ . self::REPORT_REMOVE_FILES;
            \file_put_contents($reportPath, \implode(\PHP_EOL, $elements), \FILE_APPEND | \LOCK_EX);

            $this->downloadFile($reportPath, true);
        }
    }

    /**
     * Return array of files that should be deleted.
     *
     * @return array
     */
    private function blacklistedFilesRoot()
    {
        // Files that must be deleted
        $files = [
            'README.md',
            'Sh3ll.php',
            'XsamXadoo_Bot.php',
            'XsamXadoo_Bot_All.php',
            'XsamXadoo_deface.php',
            'Xsam_Xadoo.html',
            'Xsam_Xadoo_00Bot.php',
            'anonsha1a0.php',
            'atx_bot.php',
            'azzoulshell.php',
            'b374k.php',
            'bajatax_xsam.php',
            'bigdump.php',
            'bypass.php',
            'c100.php',
            'c99.php',
            'cPanelCracker.php',
            'composer.json',
            'database.php',
            'docker-compose.yml',
            'docs/CHANGELOG.txt',
            'docs/readme_de.txt',
            'docs/readme_en.txt',
            'docs/readme_es.txt',
            'docs/readme_fr.txt',
            'docs/readme_it.txt',
            'efi.php',
            'f.php',
            'hacked.php',
            'httptest.php',
            'info.php',
            'infophp.php',
            'kill.php',
            'lfishell.php',
            'olux.php',
            'perlinfo.php',
            'php.php',
            'phpinfo.php',
            'phppsinfo.php',
            'phpversion.php',
            'prestashop.zip',
            'proshell.php',
            'r00t.php',
            'r57.php',
            'sado.php',
            'shellwow.php',
            'simulasi.php',
            'soldes.php',
            'sssp.php',
            'test.php',
            'testproxy.php',
            'upload.php',
            'wawa.php',
            'wolfm.php',
            'wso.php',
            'xGSx.php',
            'xaishell.php',
            'xcontact182.php',
            'xsam_xadoo_bot.php',
            'xsambot.php',
            'xsambot2.php',
            'xsamxadoo.php',
            'xsamxadoo101.php',
            'xsamxadoo102.php',
            'xsamxadoo95.php',
            '0x666.php',
        ];

        $getFilesRoot = [];

        foreach ($files as $file) {
            $filePath = _PS_ROOT_DIR_ . \DIRECTORY_SEPARATOR . $file;
            if (\file_exists($filePath)) {
                $getFilesRoot[] = \realpath($filePath);
            }
        }

        return $getFilesRoot;
    }

    /**
     * Check CVE-2017-9841.
     *
     * @return array
     */
    private function checkFilesCVE20179841()
    {
        $result = [];

        $rootPath = _PS_CORE_DIR_ . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR . 'phpunit';
        if (\is_dir($rootPath)) {
            $result[] = $rootPath;
        }

        $modulePath = _PS_MODULE_DIR_;

        $recursiveIter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modulePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($recursiveIter as $dir) {
            if ($dir->isDir()) {
                if ('phpunit' === $dir->getFilename()) {
                    $result[] = $dir->getRealpath();
                }
            }
        }

        return $result;
    }

    /**
     * @param $dir
     *
     * @return array
     */
    private function getFilePathExt($dir)
    {
        $files = \glob($dir . \DIRECTORY_SEPARATOR . '*.{7z,bz2,gz,rar,sql,tar,tgz,zip}', \GLOB_BRACE);
        $result = [];
        foreach ($files as $file) {
            $result[] = \realpath($file);
        }

        return $result;
    }

    /**
     * Download a compressed zip-file with all translations of the module.
     */
    private function exportTranslation()
    {
        $date = \time();
        $backupFile = 'securitypro-trans-' . $date;

        $ignoreFiles = [
            'index.php',
        ];

        $translationDir = _PS_MODULE_DIR_ . 'securitypro/translations';

        $dirIter = new RecursiveDirectoryIterator($translationDir);

        $ignoreIterator = new \PhpZip\Util\Iterator\IgnoreFilesRecursiveFilterIterator(
            $dirIter,
            $ignoreFiles
        );

        if (\function_exists('bzopen')) {
            $compressionMethod = \PhpZip\Constants\ZipCompressionMethod::BZIP2;
            $compressExt = '.bz2';
        } else {
            $compressionMethod = \PhpZip\Constants\ZipCompressionMethod::DEFLATED;
            $compressExt = '.zip';
        }

        $zipFile = new \PhpZip\ZipFile();
        $zipFile->addFilesFromIterator($ignoreIterator, '/', $compressionMethod);
        $zipFile->outputAsAttachment($backupFile . $compressExt);
        exit;
    }

    private function exportErrorLogs()
    {
        $date = \time();
        $backupFile = 'securitypro-logs-' . $date;

        $ignoreFiles = [
            'index.php',
            '.htaccess',
        ];

        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0')) {
            $path = '/log';
        } elseif (Tools::version_compare(_PS_VERSION_, '1.7.3.0', '<=')) {
            $path = '/app/logs';
        } else {
            $path = '/var/logs';
        }

        $dir = _PS_ROOT_DIR_ . $path;

        $dirIter = new RecursiveDirectoryIterator($dir);

        $ignoreIterator = new \PhpZip\Util\Iterator\IgnoreFilesRecursiveFilterIterator(
            $dirIter,
            $ignoreFiles
        );

        if (\function_exists('bzopen')) {
            $compressionMethod = \PhpZip\Constants\ZipCompressionMethod::BZIP2;
            $compressExt = '.bz2';
        } else {
            $compressionMethod = \PhpZip\Constants\ZipCompressionMethod::DEFLATED;
            $compressExt = '.zip';
        }

        $zipFile = new \PhpZip\ZipFile();
        $zipFile->addFilesFromIterator($ignoreIterator, '/', $compressionMethod);
        $zipFile->outputAsAttachment($backupFile . $compressExt);
        exit;
    }

    private function deleteTFALoginToken()
    {
        $serverIp = $this->serverIP();
        if (true === empty($serverIp) || '::1' === $serverIp || '127.0.0.1' === $serverIp) {
            $domain = ''; // Disable for localhost
        } else {
            $domain = $this->domain();
        }

        $cookieName = '2FA';
        if (isset($_COOKIE[$this->cookieName($cookieName)])) {
            unset($_COOKIE[$this->cookieName($cookieName)]);
            \setcookie(
                $this->cookieName($cookieName),
                '',
                1,
                __PS_BASE_URI__,
                $domain,
                true === (bool) Configuration::get('PS_SSL_ENABLED_EVERYWHERE'),
                true
            );
        }
    }

    /**
     * Get server IP address. (Eg. 172.217.21.142)
     *
     * @return string
     */
    private function serverIP()
    {
        return \gethostbyname($this->domain());
    }

    /**
     * Get server domain name. (Eg. google.com)
     *
     * @return string
     */
    private function domain()
    {
        return Tools::getHttpHost(false, true, true);
    }

    /**
     * Generate a unic cookie name.
     *
     * @param string $value
     *
     * @return string
     */
    private function cookieName($value)
    {
        return 'SecurityPro-' . $this->encrypt($value);
    }

    private function validateIsMinInt($key, $min)
    {
        if ((int) Configuration::get($key) < $min) {
            Configuration::updateGlobalValue($key, $min);
        }
    }

    /**
     * Display mordern warning.
     *
     * @param $msg
     *
     * @return string
     */
    private function displayModernWarning($msg)
    {
        return '<script>$.growl.warning({duration: 20000, title: "", message: "' . $msg . '" });</script>';
    }

    /**
     * Validate input name.
     *
     * @param string $name
     *
     * @return bool
     */
    private function validateHtaccessName($name)
    {
        return empty($name) || \preg_match('/^[^:]*$/ui', $name);
    }

    /**
     * Validate honeypot API key.
     *
     * @param string $apiKey
     *
     * @return bool
     */
    private function validateHoneyPotApi($apiKey)
    {
        if (false === (bool) $apiKey) {
            return false;
        }

        if (\preg_match('/^[a-z]{12}$/', $apiKey)) {
            return true;
        }

        return false;
    }

    /**
     * Validate Dropbox token.
     *
     * @return bool
     */
    private function dropboxValidateToken()
    {
        $client = $this->dropboxGetClient();

        $request = $client->users->get_current_account();

        if (null === $request || isset($request['error'])) {
            return false;
        }

        return true;
    }

    /**
     * Secure back office with rules in .htaccess.
     */
    private function secureBackOffice()
    {
        $pathHtaccess = _PS_ADMIN_DIR_ . \DIRECTORY_SEPARATOR . '.htaccess';
        $pathHtpasswd = _PS_ROOT_DIR_ . \DIRECTORY_SEPARATOR . '.htpasswd';

        Tools::deleteFile($pathHtpasswd);
        $passwordFile = new \axy\htpasswd\PasswordFile($pathHtpasswd);
        $passwordFile->setPassword(Configuration::get('PRO_HTPASSWD_USER'), Configuration::get('PRO_HTPASSWD_PASS'));
        $passwordFile->save();

        $protectedPlace = $this->l('Secured by Security Pro');

        $content = [];
        $content[] = '# ~security_pro~';
        $content[] = '<IfModule mod_authn_file.c>';
        $content[] = '    AuthType Basic';
        $content[] = '    AuthName "' . $protectedPlace . '"';
        $content[] = '    AuthBasicProvider file';
        $content[] = '    AuthUserFile "' . $pathHtpasswd . '"';
        $content[] = '    Require valid-user';
        $content[] = '</IfModule>';
        $content[] = '<IfModule mod_auth.c>';
        $content[] = '    AuthType Basic';
        $content[] = '    AuthName "' . $protectedPlace . '"';
        $content[] = '    AuthUserFile "' . $pathHtpasswd . '"';
        $content[] = '    Require valid-user';
        $content[] = '</IfModule>';
        $content[] = '# ~security_pro_end~';

        $htaccessContent = Tools::file_get_contents($pathHtaccess);
        $contentToAdd = \implode(\PHP_EOL, $content);

        if (\preg_match('/# ~security_pro~(.*?)# ~security_pro_end~/s', $htaccessContent, $m)) {
            $contentToRemove = $m[0];
            $htaccessContent = \str_replace($contentToRemove, $contentToAdd, $htaccessContent);
        } else {
            $htaccessContent = $htaccessContent . \PHP_EOL . $contentToAdd . \PHP_EOL;
        }

        \file_put_contents($pathHtaccess, $htaccessContent);
    }

    /**
     * Validate IP addresses.
     *
     * @param string $field
     */
    private function validateIps($field)
    {
        if (false === (bool) Configuration::get($field)) {
            return;
        }

        $input = \preg_replace('/\s+/', '', Configuration::get($field));
        $input = \preg_replace('/,,+/', ',', $input);
        if (',' === Tools::substr($input, -1)) {
            $input = Tools::substr($input, 0, -1);
        }
        $input = \implode(',', Tools::arrayUnique(\explode(',', $input)));

        $ips = \explode(',', $input);
        $result = [];
        foreach ($ips as $ip) {
            if (false === empty(\IPLib\Factory::rangeFromString($ip))) {
                if ('PRO_BAN_IP' === $field) {
                    if (false === $this->isInWhitelistForGeolocation($ip)) {
                        $result[] = $ip;
                    }
                } else {
                    $result[] = $ip;
                }
            }
        }

        Configuration::updateGlobalValue($field, \implode(',', $result));
    }

    /**
     * @param string $userIp
     *
     * @return bool|null
     */
    private function isInWhitelistForGeolocation($userIp)
    {
        $ips = \explode(';', Configuration::get('PS_GEOLOCATION_WHITELIST'));
        if (\is_array($ips) && \count($ips)) {
            foreach ($ips as $ip) {
                if (false === empty($ip) && 0 === Tools::strpos($userIp, $ip)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Validate IP addresses.
     *
     * @param string $field
     */
    private function validateEmail($field)
    {
        if (false === (bool) Configuration::get($field)) {
            return;
        }

        $input = \preg_replace('/\s+/', '', Configuration::get($field));
        $input = \preg_replace('/,,+/', ',', $input);
        if (',' === Tools::substr($input, -1)) {
            $input = Tools::substr($input, 0, -1);
        }
        $input = \implode(',', Tools::arrayUnique(\explode(',', $input)));

        $emails = \explode(',', $input);
        $result = [];
        foreach ($emails as $email) {
            if (true === Validate::isEmail($email)) {
                $result[] = $email;
            }
        }

        Configuration::updateGlobalValue($field, \implode(',', $result));
    }

    /**
     * Validate comma separated string.
     *
     * @param string $field
     */
    private function validateCommaSeparatedString($field)
    {
        if (false === (bool) Configuration::get($field)) {
            return;
        }

        $input = \preg_replace('/\s+/', '', Configuration::get($field));
        $input = \preg_replace('/,,+/', ',', $input);
        if (',' === Tools::substr($input, -1)) {
            $input = Tools::substr($input, 0, -1);
        }
        if (',' === Tools::substr($input, 0, 1)) {
            $input = Tools::substr($input, 1);
        }
        $input = \implode(',', Tools::arrayUnique(\explode(',', $input)));

        Configuration::updateGlobalValue($field, $input);
    }

    /**
     * @param $field
     */
    private function validateCommaSeparatedSha1($field)
    {
        if (false === (bool) Configuration::get($field)) {
            return;
        }

        $input = \preg_replace('/\s+/', '', Configuration::get($field));
        $input = \preg_replace('/,,+/', ',', $input);
        if (',' === Tools::substr($input, -1)) {
            $input = Tools::substr($input, 0, -1);
        }
        $input = \implode(',', Tools::arrayUnique(\explode(',', $input)));

        $strings = \explode(',', $input);
        $result = [];
        foreach ($strings as $string) {
            $string = Tools::strtolower($string);
            if (\preg_match('/^[a-z0-9]{40}$/', $string)) {
                $result[] = $string;
            }
        }

        Configuration::updateGlobalValue($field, \implode(',', $result));
    }

    /**
     * Get two-factor authentication database value.
     *
     * @param string $column
     *
     * @return false|string
     */
    private function twoFactorAuthDb($column)
    {
        $sql = new DbQuery();
        $sql->from('securitypro_tfa');
        $sql->select($column);

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Get secret TwoFactorAuth.
     *
     * @throws PrestaShopDatabaseException
     * @throws \RobThree\Auth\TwoFactorAuthException
     *
     * @return array|false|string
     */
    private function twoFactorsecret()
    {
        if (true === empty($this->twoFactorAuthDb('secret'))) {
            Db::getInstance()->insert('securitypro_tfa', [
                'enabled' => '0',
                'secret' => '',
            ]);
            $tfa = new \RobThree\Auth\TwoFactorAuth(Configuration::get('PS_SHOP_NAME'), 6, 30, 'sha1');
            $this->updateTwoFactorAuthDB('secret', $tfa->createSecret(160, true));
        }

        return $this->twoFactorAuthDb('secret');
    }

    /**
     * Update two-factor authentication in database.
     *
     * @param string $column
     * @param int $value
     *
     * @return bool
     */
    private function updateTwoFactorAuthDB($column, $value)
    {
        $query = 'UPDATE `' . _DB_PREFIX_ . 'securitypro_tfa` SET ' . pSQL($column) . '="' . pSQL($value) . '"';

        return Db::getInstance()->execute($query);
    }

    /**
     * Set anti-fraud hook.
     *
     * @param string $opt
     */
    private function antiFraudHook($opt)
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7.7.0')) {
            $hooks = [
                'displayAdminOrderRight',
                'displayAdminOrderLeft',
            ];
        } else {
            $hooks = [
                'displayAdminOrderSideBottom',
                'displayAdminOrderMainBottom',
            ];
        }

        foreach ($hooks as $hook) {
            $this->unregisterHook($hook);
        }

        if (Tools::version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            switch ($opt) {
                case 'left':
                    $newHook = 'displayAdminOrderSideBottom';
                    break;
                case 'right':
                    $newHook = 'displayAdminOrderMainBottom';
                    break;
            }
        } else {
            switch ($opt) {
                case 'left':
                    $newHook = 'displayAdminOrderLeft';
                    break;
                case 'right':
                    $newHook = 'displayAdminOrderRight';
                    break;
            }
        }

        $this->registerHook($newHook);
    }

    /**
     * Return current admin index.
     *
     * @throws PrestaShopException
     *
     * @return string
     */
    private function currentAdminIndex()
    {
        return $this->adminLink('AdminModules') . '&configure=securitypro';
    }

    /**
     * Rename admin folder.
     *
     * @return false|void|null
     */
    private function renameAdminDirectory()
    {
        $oldName = \basename(_PS_ADMIN_DIR_);

        if (false === (bool) Configuration::get('PRO_ADMIN_DIRECTORY')) {
            // Reset admin dir
            Configuration::updateGlobalValue('PRO_ADMIN_DIRECTORY_NAME', $oldName);

            return;
        }

        Configuration::updateGlobalValue('PRO_ADMIN_DIRECTORY', false);

        $fileName = Configuration::get('PRO_ADMIN_DIRECTORY_NAME');

        $notAllowedChars = \array_merge(
            \array_map('chr', \range(0, 31)),
            ['<', '>', ':', '"', '/', '\\', '|', '?', '*']
        );
        $newName = \str_replace($notAllowedChars, '', $fileName);

        $oldPath = _PS_ADMIN_DIR_ . \DIRECTORY_SEPARATOR;
        $newPath = _PS_ROOT_DIR_ . \DIRECTORY_SEPARATOR . $newName;
        $success = null;

        if ($oldName === $newName
            || false === \file_exists($oldPath)
            || false === \is_writable($oldPath)
            || empty($newName)) {
            Configuration::updateGlobalValue('PRO_ADMIN_DIRECTORY_NAME', $oldName);

            return;
        }

        // Remove settings in .htaccess
        $this->removeHtaccessContent();

        \sleep(1);
        if (true === \rename($oldPath, $newPath)) {
            $success = true;
        } else {
            $success = false;
        }

        // Reinsert settings in .htaccess
        if (true === (bool) Configuration::get('PRO_HTPASSWD')) {
            if (true === (bool) $this->validateHtaccessName(Configuration::get('PRO_HTPASSWD_USER')) && true === (bool) $this->validateHtaccessName(Configuration::get('PRO_HTPASSWD_PASS'))) {
                $this->secureBackOffice();
            }
        }

        if (true === $success) {
            // Redirect to new path. We need a client-side language for this task, because the server is temporary unavailable.
            echo '<script>window.location.replace("' . $this->getBaseURL() . $newName . '/index.php?controller=AdminModules&configure=securitypro&token=' . Tools::getAdminTokenLite('AdminModules') . '")</script>';
        } else {
            Configuration::updateGlobalValue('PRO_ADMIN_DIRECTORY_NAME', $oldName);

            return false;
        }
    }

    /**
     * Configure form values.
     *
     * @return array
     */
    private function configFormFieldsValue()
    {
        $result = [];
        foreach ($this->getConfigFormValues() as $key) {
            $result[$key] = Configuration::get($key);
        }

        return $result;
    }

    /**
     * @param string[] $subDirs
     */
    private function makeBackupDir($subDirs = ['database', 'files'])
    {
        $backupDir = _PS_MODULE_DIR_ . 'securitypro/backup';

        foreach ($subDirs as $subDir) {
            if (false === \is_dir($backupDir . \DIRECTORY_SEPARATOR . $subDir . \DIRECTORY_SEPARATOR)) {
                \mkdir($backupDir . \DIRECTORY_SEPARATOR . $subDir, 0755, true);
                $this->addIndexRecursively($backupDir);
                \file_put_contents($backupDir . \DIRECTORY_SEPARATOR . '.htaccess', $this->getHtaccessContent());
            }
        }
    }

    /**
     * Generate link for cronjob.
     *
     * @param string $name
     * @param bool $simple
     *
     * @return string
     */
    private function generateCronLink($name, $simple = false)
    {
        $token = $this->encrypt('securitypro/cron');
        $link = $this->context->link->getModuleLink('securitypro', 'cron', ['name' => $name, 'token' => $token]);

        if (true === $simple) {
            return $link;
        }

        $content = 'wget -q -O - "' . $link . '" >/dev/null 2>&1';

        return \htmlentities($content);
    }

    /**
     * Generate button for links.
     *
     * @param string $text
     * @param string $url
     *
     * @return string
     */
    private function generateBtnLink($text, $url)
    {
        return '<a class="btn btn-default" href="' . $url . '" target="_blank" rel="noopener noreferrer nofollow">' . $text . '</a>';
    }

    /**
     * @param null $folderId
     *
     * @throws \Google\Exception
     *
     * @return array|false|void
     */
    private function googleDriveGetFileNames($folderId = null)
    {
        if (false === $this->googleDriveGetClient()) {
            return false;
        }
        $client = $this->googleDriveGetClient();

        $api = new Google_Service_Drive($client);

        if (null !== $folderId) {
            $opt = [
                'q' => "'" . $folderId . "' in parents",
                'orderBy' => 'modifiedTime',
            ];
        } else {
            $opt = [];
        }

        try {
            $files = $api->files->listFiles($opt)->getFiles();
        } catch (Exception $e) {
            return false;
        }

        if (true === empty($files)) {
            return;
        }

        $result = [];
        foreach ($files as $key) {
            if ('application/vnd.google-apps.folder' !== $key['mimeType']) {
                $result[] = [
                    'name' => $key->getName(),
                    'id' => $key->getId(),
                ];
            }
        }

        return $result;
    }

    /**
     * @throws \Google\Exception
     *
     * @return array|void|null
     */
    private function googleDriveGenerateFolders()
    {
        $this->googleDriveEmptyTrash();

        $rootFolderId = null;
        $databaseFolderId = null;
        $fileFolderId = null;

        if (false === $this->googleDriveGetFolderNames()) {
            return;
        }

        // Get all folders and check if backup folder exist
        foreach ($this->googleDriveGetFolderNames() as $key) {
            if ('backup' === $key['name']) {
                $rootFolderId = $key['id'];
            }
        }

        // If the backup folder does not exist, then make it
        if (null === $rootFolderId) {
            $this->googleDriveCreateFolder('backup');

            // Refresh the key
            foreach ($this->googleDriveGetFolderNames() as $key) {
                if ('backup' === $key['name']) {
                    $rootFolderId = $key['id'];
                }
            }
        }

        // Check if database folder exist
        foreach ($this->googleDriveGetFolderNames($rootFolderId) as $key) {
            if ('database' === $key['name']) {
                $databaseFolderId = $key['id'];
            }
        }

        // If the database folder does not exist, then make it
        if (null === $databaseFolderId) {
            $this->googleDriveCreateFolder('database', $rootFolderId);

            // Refresh the key
            foreach ($this->googleDriveGetFolderNames($rootFolderId) as $key) {
                if ('database' === $key['name']) {
                    $databaseFolderId = $key['id'];
                }
            }
        }

        // Check if file folder exist
        foreach ($this->googleDriveGetFolderNames($rootFolderId) as $key) {
            if ('files' === $key['name']) {
                $fileFolderId = $key['id'];
            }
        }

        // If the files folder does not exist, then make it
        if (null === $fileFolderId) {
            $this->googleDriveCreateFolder('files', $rootFolderId);

            // Refresh the key
            foreach ($this->googleDriveGetFolderNames($rootFolderId) as $key) {
                if ('files' === $key['name']) {
                    $fileFolderId = $key['id'];
                }
            }
        }

        return [
            'database' => $databaseFolderId,
            'files' => $fileFolderId,
        ];
    }

    /**
     * @param null $folderId
     *
     * @throws \Google\Exception
     *
     * @return array|false
     */
    private function googleDriveGetFolderNames($folderId = null)
    {
        if (false === $this->googleDriveGetClient()) {
            return false;
        }
        $client = $this->googleDriveGetClient();

        $api = new Google_Service_Drive($client);

        if (null !== $folderId) {
            $opt = [
                'q' => "'" . $folderId . "' in parents",
            ];
        } else {
            $opt = [];
        }

        try {
            $listFiles = $api->files->listFiles($opt);
        } catch (Exception $e) {
            return false;
        }

        $result = [];
        foreach ($listFiles as $key) {
            if ('application/vnd.google-apps.folder' === $key['mimeType']) {
                $result[] = [
                    'name' => $key->getName(),
                    'id' => $key->getId(),
                ];
            }
        }

        return $result;
    }

    /**
     * @param $name
     * @param null $parent
     *
     * @throws \Google\Exception
     *
     * @return false|string
     */
    private function googleDriveCreateFolder($name, $parent = null)
    {
        if (false === $this->googleDriveGetClient()) {
            return false;
        }
        $client = $this->googleDriveGetClient();

        $api = new Google_Service_Drive($client);

        $file = new Google_Service_Drive_DriveFile();
        $file->setName($name);
        $file->setMimeType('application/vnd.google-apps.folder');

        if (null !== $parent) {
            $file->setParents([$parent]);
        }

        try {
            return $api->files->create($file);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $fileId
     *
     * @throws \Google\Exception
     *
     * @return false|Google_Service_Drive_DriveFile
     */
    private function googleDriveGetFileSize($fileId)
    {
        if (false === $this->googleDriveGetClient()) {
            return false;
        }
        $client = $this->googleDriveGetClient();

        $api = new Google_Service_Drive($client);

        $opt = [
            'fields' => 'size',
        ];

        try {
            $result = $api->files->get($fileId, $opt);
        } catch (Exception $e) {
            return false;
        }

        return $result;
    }

    /**
     * @param $technicalName
     * @param $displayName
     * @param int $confMsg
     * @param int $reload
     *
     * @return string
     */
    private function btnAjax($technicalName, $displayName, $confMsg = 0, $reload = 0)
    {
        $token = $this->encrypt('securitypro/ajax');
        $link = $this->context->link->getModuleLink('securitypro', 'ajax', ['name' => $technicalName, 'token' => $token, 'ajax' => true]);
        $id = $this->encrypt($technicalName . $reload);

        return '<button id="ajaxCall-' . $id . '" class="btn btn-default" onclick="callAjax(\'' . $link . '\', \'' . $id . '\', \'' . $confMsg . '\', \'' . $reload . '\'); return false;" id="' . $id . '">' . $displayName . '</button>';
    }

    /**
     * Get size of directories.
     *
     * @param array $paths
     *
     * @return int
     */
    private function getDirectorySize($paths)
    {
        $result = 0;
        foreach ($paths as $path) {
            $path = \realpath($path);
            if (false === empty($path) && \file_exists($path)) {
                foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
                    if ('index.php' !== $object->getFilename() && '.htaccess' !== $object->getFilename()) {
                        $result += $object->getSize();
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get array of employees information.
     *
     * @param bool $activeOnly
     *
     * @throws PrestaShopDatabaseException
     *
     * @return array
     */
    private function getEmployees($activeOnly = true)
    {
        return Db::getInstance()->executeS('
            SELECT `id_employee`, `firstname`, `lastname`, `email`, `passwd`, `last_passwd_gen`, `last_connection_date`, `active` 
            FROM `' . _DB_PREFIX_ . 'employee`
            ' . ($activeOnly ? ' WHERE `active` = 1' : '') . '
            ORDER BY `lastname` ASC
        ');
    }

    /**
     * Get employee admin link.
     *
     * @param string $id
     *
     * @throws PrestaShopException
     *
     * @return string
     */
    private function getEmployeeAdminLink($id)
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7.6.0')) { // < 1.7.6
            $url = $this->adminLink('AdminEmployees') . '&id_employee=' . $id . '&updateemployee';
        } else { // >= 1.7.6
            $explode = \explode('?', $this->adminLink('AdminEmployees'));
            $url = $explode[0] . $id . '/edit?' . $explode[1];
        }

        return $url;
    }

    /**
     * Generate heading.
     *
     * @param string $content
     * @param bool $noTop
     *
     * @return string
     */
    private function addHeading($content, $noTop = false)
    {
        if (true === $noTop) {
            return '<h2 style="margin-top: -10px">' . $content . '</h2>';
        }

        return '<h2>' . $content . '</h2>';
    }

    /**
     * @param array $array
     * @param bool $table
     *
     * @return string
     */
    private function arrayToTable($array, $table = true)
    {
        $result = [];
        $tableHeader = null;
        foreach ($array as $value) {
            if (\is_array($value)) {
                if (null === ($tableHeader)) {
                    $tableHeader = '<th><strong>' . \implode('</strong></th><th><strong>', \array_keys($value)) . '</strong></th>';
                }
                $result[] = '<tr>' . $this->arrayToTable($value, false) . '</tr>';
            } else {
                $result[] = '<td height="30">' . $value . '</td>';
            }
        }

        if (true === $table) {
            return '<table class="table"><thead><tr>' . $tableHeader . '</tr></thead>' . \implode('', $result) . '</table>';
        }

        return \implode('', $result);
    }

    /**
     * Generate alert info.
     *
     * @param string $content
     *
     * @return string
     */
    private function addAlertInfo($content)
    {
        return '<div class="alert alert-info">' . $content . '</div>';
    }

    /**
     * Generate alert warning.
     *
     * @param string $content
     *
     * @return string
     */
    private function addAlertWarning($content)
    {
        return '<div class="alert alert-warning">' . $content . '</div>';
    }

    /**
     * @throws \Google\Exception
     *
     * @return false|string
     */
    private function googleDriveGetAuthUrl()
    {
        if (false === $this->googleDriveCreateCredentials()) {
            return false;
        }

        $client = new Google_Client();
        $client->setApplicationName('Security Pro');
        $client->setScopes(Google_Service_Drive::DRIVE);
        $client->setAuthConfig(_PS_MODULE_DIR_ . $this->name . self::DIR_DATA . \DIRECTORY_SEPARATOR . $this->encrypt('credentials') . '.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        return $client->createAuthUrl();
    }

    /**
     * Generate unlock link for Admin Stealth Login.
     *
     * @return string
     */
    private function generateUnlockLink()
    {
        $token = $this->encrypt('securitypro/unlock');
        $link = \htmlentities($this->context->link->getModuleLink('securitypro', 'unlock', ['token' => $token]));

        return '<kbd>' . $link . '</kbd> <a href="javascript:void(0)" onclick="copyToClipboard(\'' . $link . '\')"><i class="icon icon-clipboard"></i></a>';
    }

    /**
     * Get URL of shop.
     *
     * @return string
     */
    private function getShopUrl()
    {
        if (Language::countActiveLanguages() > 1) {
            return $this->getBaseURL() . $this->context->language->iso_code . '/';
        }

        return $this->getBaseURL();
    }

    /**
     * Get montastic ids.
     *
     * @return array
     */
    private function getMontasticIds()
    {
        $ch = \curl_init();

        \curl_setopt($ch, \CURLOPT_URL, 'https://montastic.com/checkpoints');
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, 'GET');
        \curl_setopt($ch, \CURLOPT_USERAGENT, self::USER_AGENT);

        $headers = [];
        $headers[] = 'X-Api-Key: ' . Configuration::get('PRO_MONTASTIC_API');
        $headers[] = 'Accept: application/json';
        \curl_setopt($ch, \CURLOPT_HTTPHEADER, $headers);

        $content = \curl_exec($ch);
        \curl_close($ch);

        $ids = \json_decode($content, true);

        $result = [];
        foreach ($ids as $id) {
            $result[] = $id['id'];
        }

        return $result;
    }

    /**
     * Get monastic data.
     *
     * @param int $id
     *
     * @return array
     */
    private function getMontasticData($id)
    {
        $ch = \curl_init();

        \curl_setopt($ch, \CURLOPT_URL, 'https://montastic.com/checkpoints/' . $id);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, 'GET');
        \curl_setopt($ch, \CURLOPT_USERAGENT, self::USER_AGENT);

        $headers = [];
        $headers[] = 'X-Api-Key: ' . Configuration::get('PRO_MONTASTIC_API');
        $headers[] = 'Accept: application/json';
        \curl_setopt($ch, \CURLOPT_HTTPHEADER, $headers);

        $result = \curl_exec($ch);
        \curl_close($ch);

        return \json_decode($result, true);
    }

    /**
     * Check CVE-2021-21398.
     *
     * @return array
     */
    private function checkCve202121398()
    {
        $check = 'CVE-2021-21398';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.7.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.7.3')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2021-43789.
     *
     * @return array
     */
    private function checkCve202143789()
    {
        $check = 'CVE-2021-43789';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.5.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.8.2')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2021-21308.
     *
     * @return array
     */
    private function checkCve202121308()
    {
        $check = 'CVE-2021-21308';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.7.2')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2021-21302.
     *
     * @return array
     */
    private function checkCve202121302()
    {
        $check = 'CVE-2021-21302';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.7.2')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-6224.
     *
     * @return array
     */
    private function checkCve202026224()
    {
        $check = 'CVE-2020-26224';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.6.9')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-15162.
     *
     * @return array
     */
    private function checkCve202015162()
    {
        $check = 'CVE-2020-15162';

        if (Tools::version_compare(_PS_VERSION_, '1.7.6.8', '>=') || true === (bool) Configuration::get('PRO_DISABLE_CONTACT_FORM') || true === (bool) Configuration::get('PRO_BLOCK_FILE_UPLOAD')) {
            $status = false;
        } else {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-15161.
     *
     * @return array
     */
    private function checkCve202015161()
    {
        $check = 'CVE-2020-15161';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.6.8')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-15160.
     *
     * @return array
     */
    private function checkCve202015160()
    {
        $check = 'CVE-2020-15160';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.6.8')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-15083.
     *
     * @return array
     */
    private function checkCve202015083()
    {
        $check = 'CVE-2020-15083';
        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.6')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-15082.
     *
     * @return array
     */
    private function checkCve202015082()
    {
        $check = 'CVE-2020-15082';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.6.6')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-15081.
     *
     * @return array
     */
    private function checkCve202015081()
    {
        $check = 'CVE-2020-15081';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.6.6')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-15080.
     *
     * @return array
     */
    private function checkCve202015080()
    {
        $check = 'CVE-2020-15080';

        $files = [
            'composer.json',
            'docker-compose.yml',
        ];

        $root = _PS_CORE_DIR_ . \DIRECTORY_SEPARATOR;
        $result = [];
        foreach ($files as $file) {
            if (\file_exists($root . $file)) {
                $result[] = $root . $file;
            }
        }

        if (false === empty($result)) {
            $status = true;
        } else {
            $status = false;
        }

        $fix = $this->l('Delete following files') . ':<br>' . \implode('<br>', $result);

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-15079.
     *
     * @return array
     */
    private function checkCve202015079()
    {
        $check = 'CVE-2020-15079';
        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.6.6')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-5293.
     *
     * @return array
     */
    private function checkCve20205293()
    {
        $check = 'CVE-2020-5293';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.5')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-5288.
     *
     * @return array
     */
    private function checkCve20205288()
    {
        $check = 'CVE-2020-5288';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.5')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-5287.
     *
     * @return array
     */
    private function checkCve20205287()
    {
        $check = 'CVE-2020-5287';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.5.5.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.5')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-5286.
     *
     * @return array
     */
    private function checkCve20205286()
    {
        $check = 'CVE-2020-5286';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.4.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.5')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-5270.
     *
     * @return array
     */
    private function checkCve20205285()
    {
        $check = 'CVE-2020-5285';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.6.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.5')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-5270.
     *
     * @return array
     */
    private function checkCve20205279()
    {
        $check = 'CVE-2020-5279';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.5.0.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.5')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-5270.
     *
     * @return array
     */
    private function checkCve20205278()
    {
        $check = 'CVE-2020-5278';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.5.4.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.5')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-5270.
     *
     * @return array
     */
    private function checkCve20205276()
    {
        $check = 'CVE-2020-5276';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.1.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.5')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-5270.
     *
     * @return array
     */
    private function checkCve20205272()
    {
        $check = 'CVE-2020-5272';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.5.5.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.5')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-5271.
     *
     * @return array
     */
    private function checkCve20205271()
    {
        $check = 'CVE-2020-5271';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.6.0.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.5')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-5270.
     *
     * @return array
     */
    private function checkCve20205270()
    {
        $check = 'CVE-2020-5270';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.6.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.5')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-5269.
     *
     * @return array
     */
    private function checkCve20205269()
    {
        $check = 'CVE-2020-5269';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.6.1', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.5')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-5265.
     *
     * @return array
     */
    private function checkCve20205265()
    {
        $check = 'CVE-2020-5265';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.6.1', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.5')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-5264.
     *
     * @return array
     */
    private function checkCve20205264()
    {
        $check = 'CVE-2020-5264';

        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.5')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-5250.
     *
     * @return array
     */
    private function checkCve20205250()
    {
        $check = 'CVE-2020-5250';
        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=') && Tools::version_compare(_PS_VERSION_, '1.7.6.4')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2020-4074.
     *
     * @return array
     */
    private function checkCve20204074()
    {
        $check = 'CVE-2020-4074';
        $status = false;
        if (Tools::version_compare(_PS_VERSION_, '1.7.6.6')) {
            $status = true;
        }

        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2019-13461.
     *
     * @return array
     */
    private function checkCve201913461()
    {
        $check = 'CVE-2019-13461';
        $status = Tools::version_compare(_PS_VERSION_, '1.7.6.0', '<=');
        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2019-11876.
     *
     * @return array
     */
    private function checkCve201911876()
    {
        $check = 'CVE-2019-11876';

        $path = _PS_ROOT_DIR_ . \DIRECTORY_SEPARATOR . 'install';
        $status = \is_dir($path);
        $fix = $this->l('Delete folder') . ': ' . $path;

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2018-8824.
     *
     * @return array
     */
    private function checkCve20188824()
    {
        $check = 'CVE-2018-8824';
        $status = false;

        if (\file_exists(_PS_MODULE_DIR_ . 'bamegamenu/ajax_phpcode.php')) {
            $moduleVersion = Module::getInstanceByName('bamegamenu')->version;
            if (false === empty($moduleVersion)) {
                if (Tools::version_compare($moduleVersion, '1.0.32', '<=')) {
                    $status = true;
                }
            }
        }
        $fix = $this->l('Update module') . '\' Responsive Mega Menu (Horizontal+Vertical+Dropdown) Pro\'';

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2018-8823.
     *
     * @return array
     */
    private function checkCve20188823()
    {
        $check = 'CVE-2018-8823';
        $status = false;

        if (\file_exists(_PS_MODULE_DIR_ . 'bamegamenu/ajax_phpcode.php')) {
            $moduleVersion = Module::getInstanceByName('bamegamenu')->version;
            if (false === empty($moduleVersion)) {
                if (Tools::version_compare($moduleVersion, '1.0.32', '<=')) {
                    $status = true;
                }
            }
        }
        $fix = $this->l('Update module') . '\' Responsive Mega Menu (Horizontal+Vertical+Dropdown) Pro\'';

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * @return array
     */
    private function checkCve201819355()
    {
        $check = 'CVE-2018-19355';

        $status = \file_exists(_PS_MODULE_DIR_ . 'orderfiles/upload.php');

        $fix = $this->l('Update') . ' \'orderfiles\' ' . $this->l('module to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2018-19125.
     *
     * @return array
     */
    private function checkCve201819125()
    {
        $check = 'CVE-2018-19125';

        $status = false;

        if (Tools::version_compare((float) _PS_VERSION_, '1.6', '==') && Tools::version_compare(_PS_VERSION_, '1.6.1.23')) {
            if (\extension_loaded('phar') && false === \ini_get('phar.readonly')) {
                $status = true;
            }
        } elseif (Tools::version_compare((float) _PS_VERSION_, '1.7', '==') && Tools::version_compare(_PS_VERSION_, '1.7.4.4')) {
            if (\extension_loaded('phar') && false === \ini_get('phar.readonly')) {
                $status = true;
            }
        }

        $fix = $this->l('Set') . ' "phar.readonly = 0" ' . $this->l('in your php.ini file.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2018-19126.
     *
     * @return array
     */
    private function checkCve201819126()
    {
        $check = 'CVE-2018-19126';

        $status = false;

        if (Tools::version_compare((float) _PS_VERSION_, '1.6', '==') && Tools::version_compare(_PS_VERSION_, '1.6.1.23')) {
            if (\extension_loaded('phar') && false === \ini_get('phar.readonly')) {
                $status = true;
            }
        } elseif (Tools::version_compare((float) _PS_VERSION_, '1.7', '==') && Tools::version_compare(_PS_VERSION_, '1.7.4.4')) {
            if (\extension_loaded('phar') && false === \ini_get('phar.readonly')) {
                $status = true;
            }
        }

        $fix = $this->l('Set') . ' "phar.readonly = 0" ' . $this->l('in your php.ini file.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2018-19124.
     *
     * @return array
     */
    private function checkCve201819124()
    {
        $check = 'CVE-2018-19124';

        $status = false;

        if (Tools::version_compare((float) _PS_VERSION_, '1.6', '==') && Tools::version_compare(_PS_VERSION_, '1.6.1.23')) {
            if (\extension_loaded('phar') && false === \ini_get('phar.readonly')) {
                $status = true;
            }
        } elseif (Tools::version_compare((float) _PS_VERSION_, '1.7', '==') && Tools::version_compare(_PS_VERSION_, '1.7.4.4')) {
            if (\extension_loaded('phar') && false === \ini_get('phar.readonly')) {
                $status = true;
            }
        }

        $fix = $this->l('Set') . ' "phar.readonly = 0" ' . $this->l('in your php.ini file.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2018-13784.
     *
     * @return array
     */
    private function checkCve201813784()
    {
        $check = 'CVE-2018-13784';

        $status = false;

        if (Tools::version_compare((float) _PS_VERSION_, '1.6', '==') && Tools::version_compare(_PS_VERSION_, '1.6.1.20')) {
            $status = true;
        } elseif (Tools::version_compare((float) _PS_VERSION_, '1.7', '==') && Tools::version_compare(_PS_VERSION_, '1.7.3.4')) {
            $status = true;
        }
        $fix = $this->l('Update PrestaShop to the latest version.');

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2018-7491.
     *
     * @return array
     */
    private function checkCve20187491()
    {
        $check = 'CVE-2018-7491';

        \stream_context_set_default([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $headers = @\get_headers($this->getShopUrl(), 1);

        $status = false;

        if (isset($headers['X-Frame-Options']) && 'sameorigin' === $headers['X-Frame-Options']) {
            $status = true;
        }

        if (true === Configuration::get('PRO_CLICK_JACKING')) {
            $status = true;
        }

        $fix = $this->l('Enable') . ' ' . $this->l('Click-jack protection') . ' ' . $this->l('in') . ' \'' . $this->l('HTTP Security Headers') . '\'.';

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check CVE-2017-9841.
     *
     * @return array
     */
    private function checkCve20179841()
    {
        $check = 'CVE-2017-9841';

        $status = false;

        if (false === empty($this->checkFilesCVE20179841())) {
            $status = true;
        }

        $fix = $this->l('Delete') . ' phpunit ' . $this->l('folders') . ':<br>' . \implode('<br>', $this->checkFilesCVE20179841());

        return [
            $check,
            $status,
            $fix,
        ];
    }

    /**
     * Check if PrestaShop version is up to date.
     *
     * @return array
     */
    private function checkPrestaShopVersion()
    {
        // Add Version tab
        if (\defined('_TB_VERSION_')) {
            $cmsName = 'Thirty bees';
            $cmsVersion = _TB_VERSION_;
            $status = false;
        } else {
            $cmsName = 'PrestaShop';
            $cmsVersion = _PS_VERSION_;
            $status = $this->checkPrestaShopUpToDate();
        }

        $check = $cmsName . ' ' . $this->l('version') . ' (' . $cmsVersion . ')';

        $fix = $this->l('Update PrestaShop to the latest version') . ' (' . $this->getPrestashopLatestVersion() . ')';

        $desc = $this->l('It is strongly recommended to upgrade the store to the latest PrestaShop as new versions include bug fixes and security fixes.');

        return [
            $check,
            $status,
            $fix,
            $desc,
        ];
    }

    /**
     * Check if PrestaShop is up to date.
     *
     * @return bool
     */
    private function checkPrestaShopUpToDate()
    {
        $currentVersion = _PS_VERSION_;
        $newVersion = $this->getPrestashopLatestVersion();

        if (Tools::version_compare($currentVersion, $newVersion)) {
            return true; // Not up to date
        }

        return false; // Up to date
    }

    /**
     * Get PrestaShop latest version.
     *
     * @return string
     */
    private function getPrestashopLatestVersion()
    {
        $url = 'https://api.prestashop.com/xml/channel.xml';

        $content = $this->cachedRemoteContent($url, null, 'latest_ps_version');
        if (false === $content) {
            return false; // Error
        }

        $xml = \simplexml_load_string($content);

        return $xml->channel->branch[3]->num[0];
    }

    /**
     * Check if PHP version is up to date.
     *
     * @return array
     */
    private function checkPhpVersion()
    {
        $check = $this->l('PHP version') . ' (' . Tools::checkPhpVersion() . ')';

        if (Tools::version_compare(_PS_VERSION_, '1.7.5.0')) {
            $status = Tools::version_compare(Tools::checkPhpVersion(), $this->getNewestPhpVersion('7.1'));
            $fix = $this->l('Update the PHP version to') . ' ' . $this->getNewestPhpVersion('7.1') . '.';
        } elseif (Tools::version_compare(_PS_VERSION_, '1.7.7.0')) {
            $status = Tools::version_compare(Tools::checkPhpVersion(), $this->getNewestPhpVersion('7.2'));
            $fix = $this->l('Update the PHP version to') . ' ' . $this->getNewestPhpVersion('7.2') . '.';
        } elseif (Tools::version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            $status = Tools::version_compare(Tools::checkPhpVersion(), $this->getNewestPhpVersion('7.3'));
            $fix = $this->l('Update the PHP version to') . ' ' . $this->getNewestPhpVersion('7.3') . '.';
        } elseif (Tools::version_compare(_PS_VERSION_, '1.7.8.0', '>=')) {
            $status = Tools::version_compare(Tools::checkPhpVersion(), $this->getNewestPhpVersion('7.4'));
            $fix = $this->l('Update the PHP version to') . ' ' . $this->getNewestPhpVersion('7.4') . '.';
        } else {
            $status = Tools::version_compare(Tools::checkPhpVersion(), $this->getNewestPhpVersion('8.0'));
            $fix = $this->l('Update the PHP version to') . ' ' . $this->getNewestPhpVersion('8.0') . '.';
        }

        $desc = $this->l('The most obvious reason to update PHP is security. Newer versions are better at countering hackers, but the performance is also better in the newer PHP versions.');

        return [
            $check,
            $status,
            $fix,
            $desc,
        ];
    }

    /**
     * Get newest PHP version.
     *
     * @param string $currentVersion
     *
     * @return string
     */
    private function getNewestPhpVersion($currentVersion)
    {
        $url = 'https://www.php.net/releases/';

        $params = [
            'json' => '1',
            'version' => $currentVersion,
        ];

        $content = $this->cachedJsonDecodedContent($url, $params, $currentVersion, 3600);

        return $content['version'];
    }

    /**
     * Check if PrestaShop TLS is enabled.
     *
     * @throws PrestaShopException
     *
     * @return array
     */
    private function checkTlsEnabled()
    {
        $check = $this->l('SSL enabled');

        $status = false === (bool) Configuration::get('PS_SSL_ENABLED');

        $fix = $this->l('Enable SSL in') . ' ' . $this->generateLink($this->adminLink('AdminPreferences'), $this->l('\'Shop Parameters\' > \'General\''));

        $desc = $this->l('If you own an SSL certificate for your shop\'s domain name, you can activate SSL encryption (https://) for customer account identification and order processing.');

        return [
            $check,
            $status,
            $fix,
            $desc,
        ];
    }

    /**
     * Check if PrestaShop TLS everywhere is enabled.
     *
     * @throws PrestaShopException
     *
     * @return array
     */
    private function checkTlsEnabledEverywhere()
    {
        $check = $this->l('SSL enabled everywhere');

        $status = false === (bool) Configuration::get('PS_SSL_ENABLED_EVERYWHERE');

        $fix = $this->l('Enable SSL everywhere in') . ' ' . $this->generateLink($this->adminLink('AdminPreferences'), $this->l('\'Shop Parameters\' > \'General\''));

        $desc = $this->l('When enabled, all the pages of your shop will be SSL-secured.');

        return [
            $check,
            $status,
            $fix,
            $desc,
        ];
    }

    /**
     * Check if PrestaShop token is activated.
     *
     * @throws PrestaShopException
     *
     * @return array
     */
    private function checkPrestashopToken()
    {
        $check = $this->l('Security token');

        $status = false === (bool) Configuration::get('PS_TOKEN_ENABLE');

        $fix = $this->l('Enable Increase front office security in') . ' ' . $this->generateLink($this->adminLink('AdminPreferences'), $this->l('\'Shop Parameters\' > \'General\''));

        $desc = $this->l('Enable token in the front office to improve PrestaShop\'s security.');

        return [
            $check,
            $status,
            $fix,
            $desc,
        ];
    }

    /**
     * Check if Mod Secure is active.
     *
     * @throws PrestaShopException
     *
     * @return array
     */
    private function checkModSecurity()
    {
        $check = 'ModSecurity';

        $status = (bool) Configuration::get('PS_HTACCESS_DISABLE_MODSEC');

        $fix = $this->l('Enable Apache\'s \'mod_security\' module in') . ' ' . $this->generateLink($this->adminLink('AdminMeta'), $this->l('\'Shop Parameters\' > \'Traffic and SEO\''));

        $desc = $this->l('Enable Apache\'s mod_security module to harden the security of your shop.');

        return [
            $check,
            $status,
            $fix,
            $desc,
        ];
    }

    /**
     * Check if PrestaShop admin directory name is secure.
     *
     * @return array
     */
    private function checkAdminDirectoryName()
    {
        $check = $this->l('Admin folder name');

        $status = false === \preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', \basename(_PS_ADMIN_DIR_));

        $fix = $this->l('Use both letters and numbers in the name of your admin folder.');

        $desc = $this->l('To make it harder for attackers to guess the URL, use both letters and numbers in the name of your admin folder.');

        return [
            $check,
            $status,
            $fix,
            $desc,
        ];
    }

    /**
     * Check the cookie's IP address.
     *
     * @throws PrestaShopException
     *
     * @return array
     */
    private function checkCookieIpAddress()
    {
        $check = $this->l('Cookie\'s IP address');

        $status = false === (bool) Configuration::get('PS_COOKIE_CHECKIP');

        $fix = $this->l('Enable check of cookie IP address at') . ' ' . $this->generateLink($this->adminLink('AdminAdminPreferences'), $this->l('\'Advanced Parameters\' > \'Administration\''));

        $desc = $this->l('Check the IP address of the cookie to prevent your cookie from being stolen.');

        return [
            $check,
            $status,
            $fix,
            $desc,
        ];
    }

    /**
     * Check if PrestaShop develop mode is active.
     *
     * @throws PrestaShopException
     *
     * @return array
     */
    private function checkUseHtmlPurifier()
    {
        $check = $this->l('HTML Purifier');

        $status = false === Configuration::get('PS_USE_HTMLPURIFIER');

        $fix = $this->l('Enable HTMLPurifier library in') . ' ' . $this->generateLink($this->adminLink('AdminPreferences'), $this->l('\'Shop Parameters\' > \'General\''));

        $desc = $this->l('HTML Purifier defeats XSS with an audited whitelist.') . ' ' . $this->generateLink('http://htmlpurifier.org/', $this->l('Read more')) . '.';

        return [
            $check,
            $status,
            $fix,
            $desc,
        ];
    }

    /**
     * Check if PrestaShop develop mode is active.
     *
     * @throws PrestaShopException
     *
     * @return array
     */
    private function checkPrestashopDevMode()
    {
        $check = $this->l('Debug mode');

        $status = _PS_MODE_DEV_;

        $fix = $this->l('Disabling the debug mode at') . ' ' . $this->generateLink($this->adminLink('AdminPerformance'), $this->l('\'Advanced Parameters\' > \'Performance\''));

        $desc = $this->l('Once your shop is in production; you must disable the debug mode. It can leak pieces of information that a hacker can use.');

        return [
            $check,
            $status,
            $fix,
            $desc,
        ];
    }

    /**
     * @return false|string
     */
    private function getGooglePageSpeed()
    {
        $serverIp = $this->serverIP();
        if (true === empty($serverIp) || '::1' === $serverIp || '127.0.0.1' === $serverIp) {
            return false; // Disable for localhost
        }

        $googleUrl = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

        $params = [
            'url' => $this->getBaseURL(),
            'category' => 'best-practices',
        ];

        $name = 'lighthouseResult';
        $content = $this->cachedJsonDecodedContent($googleUrl, $params, $name, 604800);

        if (false === $content) {
            return false;
        }

        if (true === isset($content['error'])) {
            $cachePath = _PS_CACHE_DIR_ . $this->name;
            $filename = $cachePath . \DIRECTORY_SEPARATOR . $this->encrypt($name) . '.json';

            Tools::deleteFile($filename);

            return false;
        }

        return $content['lighthouseResult']['audits']['no-vulnerable-libraries']['details']['items'];
    }

    /**
     * Get domain information.
     *
     * @param string $ip
     *
     * @return bool
     */
    private function getDomainInfo($ip)
    {
        $url = 'https://www.iplocate.io/api/lookup/' . $ip;

        return $this->cachedJsonDecodedContent($url, null, $ip, 2629746);
    }

    /**
     * Test SPF for domain.
     *
     * @param string $domain
     *
     * @return bool
     */
    private function testSpf($domain)
    {
        $records = @\dns_get_record($domain, \DNS_TXT | \DNS_SOA);

        if (false === $records) {
            return false;
        }

        foreach ($records as $record) {
            if ('TXT' === $record['type']) {
                $txt = Tools::strtolower($record['txt']);
                // An SPF record can be empty (no mechanism)
                if ('v=spf1' === $txt || 0 === \stripos($txt, 'v=spf1 ')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get name servers of domain.
     *
     * @param string $domain
     *
     * @return array|bool|void
     */
    private function getNameServers($domain)
    {
        $records = @\dns_get_record($domain, \DNS_NS);
        if (false === $records) {
            return;
        }

        $nsRecords = [];
        foreach ($records as $current) {
            $nsRecords[] = \htmlspecialchars($current['target']);
        }

        return $nsRecords;
    }

    /**
     * Check php.ini conf: session.use_cookies.
     *
     * @return array
     */
    private function checkSessionAutoStart()
    {
        $key = 'session.auto_start';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'Off';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('It is considered to bad practice to autostart sessions.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Normalize php ini value.
     *
     * @param string $string
     *
     * @return string
     */
    private function isOn($string)
    {
        if ('0' === $string || 'off' === Tools::strtolower($string) || '' === $string) {
            return false;
        }

        return true;
    }

    /**
     * Check php.ini conf: session.use_cookies.
     *
     * @return array
     */
    private function checkSessionUseCookies()
    {
        $key = 'session.use_cookies';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'On';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Accepts cookies to manage sessions.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: session.use_only_cookies.
     *
     * @return array
     */
    private function checkSessionUseOnlyCookies()
    {
        $key = 'session.use_only_cookies';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'On';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Must use cookies to manage sessions, do not accept session-ids in a link.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: session.cookie_httponly.
     *
     * @return array
     */
    private function checkSessionCookieHttponly()
    {
        $key = 'session.cookie_httponly';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'On';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Setting session cookies to \'HTTP only\' makes them only readable by the browser.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: session.use_trans_sid.
     *
     * @return array
     */
    private function checkPhpUseTransSid()
    {
        $key = 'session.use_trans_sid';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'Off';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('If used') . ' \'use_trans_sid\' ' . $this->l('setting puts the session ID on the URL, making it easier to hijack.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: session.cookie_secure.
     *
     * @return array
     */
    private function checkCookieSecure()
    {
        if ('localhost' === $this->domain()) {
            $url = self::TEST_URL;
        } else {
            $url = $this->getBaseURL();
        }

        $key = 'session.cookie_secure';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        if (true === $this->hasSsl($url)) {
            $recommended = 'On';
        } else {
            $recommended = 'Off';
        }

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Cookie secure specifies whether cookies should only be sent over secure connections.') . ' ' . $this->l('This setting requires SSL/TLS to be enabled.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check if HTTPS is up.
     *
     * @param string $url
     *
     * @return bool
     */
    private function hasSsl($url)
    {
        $ch = \curl_init($url);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, 'HEAD');
        \curl_setopt($ch, \CURLOPT_NOBODY, true);
        \curl_setopt($ch, \CURLOPT_FOLLOWLOCATION, true);
        \curl_setopt($ch, \CURLOPT_VERBOSE, false);
        \curl_setopt($ch, \CURLOPT_HEADER, true);
        \curl_setopt($ch, \CURLOPT_CONNECTTIMEOUT, 10);
        \curl_setopt($ch, \CURLOPT_USERAGENT, self::USER_AGENT);

        \curl_exec($ch);
        $header = \curl_getinfo($ch, \CURLINFO_HTTP_CODE);

        if (200 === $header || 503 === $header) {
            return true;
        }

        return false;
    }

    /**
     * Check php.ini conf: session.use_strict_mode.
     *
     * @return array
     */
    private function checkUseStrictMode()
    {
        $key = 'session.use_strict_mode';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'On';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Strict mode prevents uninitialized session ID\'s in the built-in session handling.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: session.cookie_lifetime.
     *
     * @return array
     */
    private function checkCookieLifetime()
    {
        $key = 'session.cookie_lifetime';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'Off';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('It tells browsers not to store the session cookie to permanent storage. Therefore, when the browser is terminated, the session ID cookie is deleted immediately.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: session.lazy_write.
     *
     * @return array
     */
    private function checkLazyWrite()
    {
        $key = 'session.lazy_write';

        if (Tools::version_compare(Tools::checkPhpVersion(), '7.0.0', '>=')) {
            if (true === $this->isOn(\ini_get($key))) {
                $current = 'On';
            } else {
                $current = 'Off';
            }

            $recommended = 'On';

            $desc = $this->l('Lazy session writes only when the session data has been modified. This should be enabled to prevent potential information exposure.');
        } else {
            $current = false;
            $recommended = false;
            $desc = $this->l('The INI setting') . ' \'' . $key . '\' ' . $this->l('was added in') . ' PHP 7.0.0.';
        }

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: session.sid_length.
     *
     * @return array
     */
    private function checkSidLength()
    {
        $key = 'session.sid_length';

        if (Tools::version_compare(Tools::checkPhpVersion(), '7.1.0', '>=')) {
            if (false !== \ini_get($key)) {
                $current = \ini_get($key);
            } else {
                $current = '32';
            }
            $recommended = '128';
            $desc = $this->l('Increasing the session ID length will make it harder for an attacker to guess it (via brute force or more likely side-channel attacks).');
            if ((int) $current >= (int) $recommended) {
                $status = false;
            } else {
                $status = true;
            }
        } else {
            $current = false;
            $recommended = false;
            $status = false;
            $desc = $this->l('The INI setting') . ' \'' . $key . '\' ' . $this->l('was added in') . ' PHP 7.1.0.';
        }

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: session.gc_probability.
     *
     * @return array
     */
    private function checkSessionGcProbability()
    {
        $key = 'session.gc_probability';

        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'On';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Defines the probability that the \'garbage collection\' process is started on every session initialization.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: session.gc_divisor.
     *
     * @return array
     */
    private function checkSessionGcDivisor()
    {
        $key = 'session.gc_divisor';
        if (false !== \ini_get($key)) {
            $current = \ini_get($key);
        } else {
            $current = '100';
        }
        $recommended = '1000';
        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Defines the probability that the \'garbage collection\' process is started on every session initialization.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: session.sid_bits_per_character.
     *
     * @return array
     */
    private function checkSidBitsPerCharacter()
    {
        $key = 'session.sid_bits_per_character';

        if (Tools::version_compare(Tools::checkPhpVersion(), '7.1.0', '>=')) {
            if (false !== \ini_get($key)) {
                $current = \ini_get($key);
            } else {
                $current = '4';
            }
            $recommended = '6';
            $desc = $this->l('The more bits result in stronger session ID.');
            if ((int) $current >= (int) $recommended) {
                $status = false;
            } else {
                $status = true;
            }
        } else {
            $current = false;
            $recommended = false;
            $status = false;
            $desc = $this->l('The INI setting') . ' \'' . $key . '\' ' . $this->l('was added in') . ' PHP 7.1.0.';
        }

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: allow_url_fopen.
     *
     * @return array
     */
    private function checkUrlFopen()
    {
        $key = 'allow_url_fopen';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'On';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('This directive enables PrestaShop to access remote files, which is an essential part of the payment process, among other things. It is therefore imperative to have it enabled.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: allow_url_include.
     *
     * @return array
     */
    private function checkUrlInclude()
    {
        $key = 'allow_url_include';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'Off';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Do not allow the inclusion of remote file resources.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: display_errors.
     *
     * @return array
     */
    private function checkDisplayErrors()
    {
        $key = 'display_errors';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'Off';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Do not show errors in production.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: log_errors.
     *
     * @return array
     */
    private function checkLogErrors()
    {
        $key = 'log_errors';

        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'On';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Log errors in production.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: error_reporting.
     *
     * @return array
     */
    private function checkErrorReporting()
    {
        $key = 'error_reporting';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'Off';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Error reporting should be different based on context.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: display_startup_errors.
     *
     * @return array
     */
    private function checkDisplayStartupErrors()
    {
        $key = 'display_startup_errors';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'Off';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Showing startup errors could provide extra information to potential attackers.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: expose_php.
     *
     * @return array
     */
    private function checkExposePhp()
    {
        $key = 'expose_php';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'Off';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Showing the PHP signature exposes additional information.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: register_argc_argv.
     *
     * @return array
     */
    private function checkRegisterArgcArgv()
    {
        $key = 'register_argc_argv';

        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'Off';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Whether to declare the argv & argc variables (that would contain the GET information).');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: short_open_tag.
     *
     * @return array
     */
    private function checkShortOpenTag()
    {
        $key = 'short_open_tag';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'Off';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Not a direct security vulnerability, but it could become one given the proper conditions.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: file_uploads.
     *
     * @return array
     */
    private function checkFileUploads()
    {
        $key = 'file_uploads';
        if (true === $this->isOn(\ini_get($key))) {
            $current = 'On';
        } else {
            $current = 'Off';
        }

        $recommended = 'On';

        if ($current === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('PrestaShop require HTTP file uploads.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: upload_max_filesize.
     *
     * @return array
     */
    private function checkUploadMaxFileSize()
    {
        $key = 'upload_max_filesize';
        if (false !== \ini_get($key)) {
            $current = \ini_get($key);
        } else {
            $current = '2M';
        }
        $recommended = '20M';

        if ($this->convertToBytes($current) <= $this->convertToBytes($recommended)) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('A maximum upload size should be defined to prevent server overload from large requests.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Convert size to bytes.
     *
     * @param string $sizeStr
     *
     * @return int
     */
    private function convertToBytes($sizeStr)
    {
        $type = Tools::substr(Tools::strtolower($sizeStr), -1);
        switch ($type) {
            case 'm':
                return (int) $sizeStr * 1048576;
            case 'k':
                return (int) $sizeStr * 1024;
            case 'g':
                return (int) $sizeStr * 1073741824;

            default:
                return (int) $sizeStr;
        }
    }

    /**
     * Check php.ini conf: post_max_size.
     *
     * @return array
     */
    private function checkPostMaxSize()
    {
        $key = 'post_max_size';
        if (false !== \ini_get($key)) {
            $current = \ini_get($key);
        } else {
            $current = '8M';
        }
        $recommended = '22M';
        if ($this->convertToBytes($current) <= $this->convertToBytes($recommended)) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Unless necessary, a maximum post size of') . ' ' . $current . ' ' . $this->l('is too large.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: max_input_vars.
     *
     * @return array
     */
    private function checkMaxInputVars()
    {
        $key = 'max_input_vars';
        if (false !== \ini_get($key)) {
            $current = \ini_get($key);
        } else {
            $current = '1000';
        }
        $recommended = '20000';
        if ((int) $current <= (int) $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('A maximum number of input variables should be defined to prevent performance issues.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: max_input_vars.
     *
     * @return array
     */
    private function checkMaxInputTime()
    {
        $key = 'max_input_time';
        if (false !== \ini_get($key)) {
            $current = \ini_get($key);
        } else {
            $current = '-1';
        }
        $recommended = '300';
        if ((int) $current <= (int) $recommended && '-1' !== $current && 0 !== (int) $current) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Maximum amount of time each script may spend parsing request data. It is a good idea to limit this time on productions servers to eliminate unexpectedly long-running scripts.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: memory_limit.
     *
     * @return array
     */
    private function checkMemoryLimit()
    {
        $key = 'memory_limit';
        if (false !== \ini_get($key)) {
            $current = \ini_get($key);
        } else {
            $current = '128M';
        }
        $recommended = '512M';
        if ($this->convertToBytes($current) <= $this->convertToBytes($recommended) && '-1' !== $current && '0' !== $current) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('The standard memory limit should not be too high if you need more memory for a single script, you can adjust that during runtime') . ' ini_set().';

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: max_execution_time.
     *
     * @return array
     */
    private function checkMaxExecutionTime()
    {
        $key = 'max_execution_time';
        if (false !== \ini_get($key)) {
            $current = \ini_get($key);
        } else {
            $current = '30';
        }
        $recommended = '300';
        if ($current <= $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('To prevent denial-of-service attacks where an attacker tries to keep your server\'s CPU busy, this value should be set to the lowest possible value.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Check php.ini conf: default_charset.
     *
     * @return array
     */
    private function checkDefaultCharset()
    {
        $key = 'default_charset';
        if (false !== \ini_get($key)) {
            $current = \ini_get($key);
        } else {
            $current = 'utf-8';
        }
        $recommended = 'utf-8';
        if (Tools::strtolower($current) === $recommended) {
            $status = false;
        } else {
            $status = true;
        }

        $desc = $this->l('Ensure that a default character set is defined, utf-8 is preferred.');

        return [
            $key,
            $current,
            $recommended,
            $status,
            $desc,
        ];
    }

    /**
     * Get information about TLS certificate.
     *
     * @return string
     */
    private function getCertInfo()
    {
        // Check if SSL is enabled
        if (false === (bool) Configuration::get('PS_SSL_ENABLED')) {
            return false;
        }

        // Check if port 433 is open
        if (isset($_SERVER['SERVER_PORT']) && '443' !== $_SERVER['SERVER_PORT']) {
            return false;
        }

        if ('localhost' === $this->domain()) {
            $host = self::TEST_DOMAIN;
        } else {
            $host = $this->domain();
        }

        $context = \stream_context_create(
            [
                'ssl' => [
                    'capture_peer_cert' => true,
                ],
            ]
        );

        @$readStreamSsl = \stream_socket_client(
            'ssl://' . $host . ':443',
            $errno,
            $errstr,
            30,
            \STREAM_CLIENT_CONNECT,
            $context
        );

        if (false === $readStreamSsl) {
            return false;
        }

        $cert = \stream_context_get_params($readStreamSsl);

        return \openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
    }

    /**
     * Check if heartbleed vulnerability exists.
     *
     * @return bool
     */
    private function heartbleed()
    {
        if (\defined('OPENSSL_VERSION_NUMBER')
            && 0x100010F < \OPENSSL_VERSION_NUMBER
            && \OPENSSL_VERSION_NUMBER < 0x1000106F) {
            return true;
        }

        return false;
    }

    /**
     * Check if CCS injection vulnerability exists.
     *
     * @return bool
     */
    private function ccsInjection()
    {
        if (\defined('OPENSSL_VERSION_NUMBER')
            && (\OPENSSL_VERSION_NUMBER > 0x100000F && \OPENSSL_VERSION_NUMBER < 0x100010F)
            && (\OPENSSL_VERSION_NUMBER > 0x100010F && \OPENSSL_VERSION_NUMBER < 0x1000107F)
            && \OPENSSL_VERSION_NUMBER < 0x00908025F) {
            return true;
        }

        return false;
    }

    /**
     * Check if SSLV2 is supported.
     *
     * @param string $domain
     *
     * @return bool
     */
    private function sslv2Support($domain)
    {
        $streamSslv2 = \stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'capture_session_meta' => true,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
                'crypto_method' => \STREAM_CRYPTO_METHOD_SSLv2_CLIENT,
                'sni_enabled' => true,
            ],
        ]);
        @$readStreamSslv2 = \stream_socket_client('sslv2://' . $domain . ':443', $errno, $errstr, 2, \STREAM_CLIENT_CONNECT, $streamSslv2);

        if (false === $readStreamSslv2) {
            return false;
        }

        return true;
    }

    /**
     * Check if SSLV3 is supported.
     *
     * @param string $domain
     *
     * @return bool
     */
    private function sslv3Support($domain)
    {
        $streamSslv3 = \stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'capture_session_meta' => true,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
                'crypto_method' => \STREAM_CRYPTO_METHOD_SSLv3_CLIENT,
                'sni_enabled' => true,
            ],
        ]);

        @$readStreamSslv3 = \stream_socket_client(
            'sslv3://' . $domain . ':443',
            $errno,
            $errstr,
            2,
            \STREAM_CLIENT_CONNECT,
            $streamSslv3
        );

        if (false === $readStreamSslv3) {
            return false;
        }

        return true;
    }

    /**
     * Check if HSTS header is set.
     *
     * @param string $url
     *
     * @return bool
     */
    private function isSetHSTS($url)
    {
        $headers = @\get_headers($url, 1);

        if (isset($headers['Strict-Transport-Security'])) {
            return true;
        }

        return false;
    }

    /**
     * Test if the URL was redirected to HTTPS.
     *
     * @return bool
     */
    private function isRedirectedToHttps()
    {
        $url = 'http://' . $this->domain() . __PS_BASE_URI__;

        $result = $this->getRedirectedUrl($url);

        return 'https' === Tools::substr($result, 0, 5);
    }

    /**
     * Get the redirected URL.
     *
     * @param string $url
     *
     * @return bool
     */
    private function getRedirectedUrl($url)
    {
        $ch = \curl_init();
        \curl_setopt($ch, \CURLOPT_URL, $url);
        \curl_setopt($ch, \CURLOPT_HEADER, true);
        \curl_setopt($ch, \CURLOPT_NOBODY, true);
        \curl_setopt($ch, \CURLOPT_FOLLOWLOCATION, false);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, \CURLOPT_CONNECTTIMEOUT, 10);
        \curl_setopt($ch, \CURLOPT_USERAGENT, self::USER_AGENT);

        $result = \curl_exec($ch);
        $httpStatus = \curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        \curl_close($ch);

        // If it's not a redirection (3XX), move along
        if ($httpStatus < 300 || $httpStatus >= 400) {
            return $url;
        }

        // Look for a location: header to find the target URL
        if (\preg_match('/location: (.*)/i', $result, $r)) {
            $location = \trim($r[1]);

            // If the location is a relative URL, attempt to make it absolute
            if (\preg_match('/^\/(.*)/', $location)) {
                $urlParts = \parse_url($url);

                $baseUrl = [];
                if ($urlParts['scheme']) {
                    $baseUrl[] = $urlParts['scheme'] . '://';
                }

                if ($urlParts['host']) {
                    $baseUrl[] = $urlParts['host'];
                }

                if ($urlParts['port']) {
                    $baseUrl[] = ':' . $urlParts['port'];
                }

                return \implode('', $baseUrl) . $location;
            }

            return $location;
        }
    }

    /**
     * Get an array of trusted / untrusted modules.
     *
     * @param bool $trusted
     *
     * @return array|false|void
     */
    private function getModules($trusted)
    {
        if (true === $trusted) {
            $path = _PS_ROOT_DIR_ . '/config/xml/trusted_modules_list.xml';
        } else {
            $path = _PS_ROOT_DIR_ . '/config/xml/untrusted_modules_list.xml';
        }
        if (false === \file_exists($path)) {
            ModuleCore::generateTrustedXml();
        }

        if (false === \file_exists($path)) {
            return;
        }

        $xml = \simplexml_load_string(Tools::file_get_contents($path));

        if (false === empty($xml->modules)) {
            $modules = [];
            foreach ($xml->modules->module as $module) {
                if (Module::isInstalled($module['name'])) {
                    $modules[] = $module['name'];
                }
            }

            return \array_unique($modules);
        }
    }

    /**
     * Generate paragraph.
     *
     * @param string $text
     * @param bool $italic
     *
     * @return string
     */
    private function addParagraph($text, $italic = false)
    {
        if (true === $italic) {
            return '<p style="font-size: 13px; font-style: italic;">' . $text . '</p>';
        }

        return '<p style="font-size: 13px;">' . $text . '</p>';
    }

    /**
     * Load protect content section.
     */
    private function protectContent()
    {
        // Protect content
        if (false === $this->checkWhitelist('PRO_WHITELIST_PROTECT_CONTENT')) {
            // Disable browser features
            if (1 === (int) Configuration::get('PRO_DISABLE_RIGHT_CLICK')) {
                $this->context->controller->addJS($this->_path . 'views/js/contextmenu.js');
            } elseif (2 === (int) Configuration::get('PRO_DISABLE_RIGHT_CLICK')) {
                $this->context->controller->addJS($this->_path . 'views/js/contextmenu-img.js');
            }

            if (true === (bool) Configuration::get('PRO_DISABLE_DRAG')) {
                $this->context->controller->addJS($this->_path . 'views/js/dragstart.js');
            }

            if (true === (bool) Configuration::get('PRO_DISABLE_COPY')) {
                $this->context->controller->addJS($this->_path . 'views/js/copy.js');
            }

            if (true === (bool) Configuration::get('PRO_DISABLE_CUT')) {
                $this->context->controller->addJS($this->_path . 'views/js/cut.js');
            }

            if (true === (bool) Configuration::get('PRO_DISABLE_PRINT')) {
                $this->context->controller->addJS($this->_path . 'views/js/print.js');
            }

            if (true === (bool) Configuration::get('PRO_DISABLE_SAVE')) {
                $this->context->controller->addJS($this->_path . 'views/js/save.js');
            }

            if (true === (bool) Configuration::get('PRO_DISABLE_VIEW_PAGE_SOURCE')) {
                $this->context->controller->addJS($this->_path . 'views/js/view-page-source.js');
            }

            if (true === (bool) Configuration::get('PRO_DISABLE_CONSOLE')) {
                $this->context->controller->addJS($this->_path . 'views/js/console.js');
            }

            if (true === (bool) Configuration::get('PRO_DISABLE_TEXT_SELECTION')) {
                $this->context->controller->addJS($this->_path . 'views/js/selectstart.js');
            }
        }
    }

    /**
     * Whitelist IP addresses.
     *
     * @param $field string
     *
     * @return bool
     */
    private function checkWhitelist($field)
    {
        if (false === (bool) Configuration::get($field)) {
            return false;
        }

        $whitelist = \explode(',', Configuration::get($field));
        foreach ($whitelist as $list) {
            $range = \IPLib\Factory::rangeFromString($list);
            if ($range->contains(\IPLib\Factory::addressFromString($this->getClientIP()))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Display Google reCAPTCHA V3
     *
     * @param string $submitButton
     *
     * @return string
     */
    private function displayGoogleRecaptchaV3($submitButton)
    {
        if (true === (bool) Configuration::get('PRO_RECAPTCHA_V3_SITE_KEY')) {
            $siteKey = Configuration::get('PRO_RECAPTCHA_V3_SITE_KEY');
        } else {
            $siteKey = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';
        }

        if (true === (bool) Configuration::get('PRO_DISPLAY_RECAPTCHA_V3')) {
            $display = Configuration::get('PRO_DISPLAY_RECAPTCHA_V3');
        } else {
            $display = 'bottomright';
        }

        if (true === (bool) Configuration::get('PRO_RECAPTCHA_V3_THEME')) {
            $theme = Configuration::get('PRO_RECAPTCHA_V3_THEME');
        } else {
            $theme = 'light';
        }

        $this->context->smarty->assign([
            'sp_lang' => $this->context->language->iso_code,
            'sp_siteKey' => $siteKey,
            'sp_display' => $display,
            'sp_theme' => $theme,
            'sp_submitButton' => $submitButton,
        ]);

        return $this->display(__FILE__, 'displayRecaptcha.tpl');
    }

    /**
     * Display contact form errors for PrestaShop 1.7.
     *
     * @return string
     */
    private function displayContactFormErrors()
    {
        // Contact form
        if ($this->context->controller instanceof ContactController) {
            // Return false if the contact form is disabled
            if (true === (bool) Configuration::get('PRO_DISABLE_CONTACT_FORM')) {
                return false;
            }

            $from = Tools::getValue('from');
            $message = Tools::getValue('message');

            if (false === empty($from) && false === empty($message)) {
                if (Tools::isSubmit('submitMessage')) {
                    // Validate Google reCAPTCHA v3
                    if (true === (bool) Configuration::get('PRO_RECAPTCHA_V3_CONTACT_ACTIVATE')) {
                        if (false === empty(Configuration::get('PRO_RECAPTCHA_V3_SECRET'))) {
                            $secretKey = Configuration::get('PRO_RECAPTCHA_V3_SECRET');
                        } else {
                            $secretKey = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';
                        }

                        $data = [
                            'secret' => $secretKey,
                            'response' => Tools::getValue('g-token'),
                            'remoteip' => $this->getClientIP(),
                        ];

                        $ch = \curl_init();
                        \curl_setopt($ch, \CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
                        \curl_setopt($ch, \CURLOPT_POST, true);
                        \curl_setopt($ch, \CURLOPT_POSTFIELDS, \http_build_query($data));
                        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
                        \curl_setopt($ch, \CURLOPT_USERAGENT, self::USER_AGENT);

                        $response = \curl_exec($ch);
                        \curl_close($ch);

                        $decode = \json_decode($response, true);

                        $error = [];

                        if (isset($decode['success'])) {
                            if (true !== (bool) $decode['success']) {
                                switch ($decode['error-codes'][0]) {
                                    case 'missing-input-secret':
                                        $error[] = 'a0';
                                        break;
                                    case 'invalid-input-secret':
                                        $error[] = 'a1';
                                        break;
                                    case 'missing-input-response':
                                        $error[] = 'a2';
                                        break;
                                    case 'invalid-input-response':
                                        $error[] = 'a3';
                                        break;
                                    case 'bad-request':
                                        $error[] = 'a4';
                                        break;
                                    case 'timeout-or-duplicate':
                                        $error[] = 'a5';
                                        break;

                                    default:
                                        $error[] = 'a6';
                                        break;
                                }
                            }
                        }

                        if (isset($decode['score'])) {
                            if ((float) $decode['score'] < (float) 0.6) {
                                $error[] = 'a7';
                            }
                        }
                    }

                    // Check disposable e-mail provider
                    if (true === (bool) Configuration::get('PRO_DISPOSABLE_EMAIL_PROVIDERS_ACTIVATE')) {
                        $checker = new \EmailChecker\EmailChecker();
                        if (false === (bool) $checker->isValid($from)) {
                            $error[] = 'b0';
                        }
                    }

                    // Check custom list of banned TLD
                    if (true === (bool) Configuration::get('PRO_EMAIL_CHECKER_ACTIVATE')) {
                        $domain = \explode('@', $from);
                        $domain = \explode('.', $domain[1]);
                        \array_shift($domain);
                        $domainTld = \implode('.', $domain);
                        $blacklist = \explode(',', Configuration::get('PRO_EMAIL_CHECKER_CUSTOM_LIST'));
                        if (\in_array($domainTld, $blacklist, true)) {
                            $error[] = 'b1';
                        }
                    }

                    // Check custom list of banned e-mails
                    if (true === (bool) Configuration::get('PRO_BLOCK_EMAILS')) {
                        $blacklist = \explode(',', Configuration::get('PRO_BLOCK_EMAILS_CUSTOM_LIST'));
                        if (\in_array($from, $blacklist, true)) {
                            $error[] = 'b2';
                        }
                    }

                    // Scan URL's in message
                    if (true === (bool) Configuration::get('PRO_GOOGLE_SAFE_BROWSING_V4_ACTIVATE')) {
                        \preg_match_all('~[a-z]+://\S+~', $message, $url);
                        if (false === empty($url[0])) {
                            if (true === $this->lookupGoogleSafeBrowsingV4($url[0])) {
                                $error[] = 'c0';
                            }
                        }
                    }

                    // Check custom list of words
                    if (Configuration::get('PRO_MESSAGE_CHECKER_ACTIVATE')) {
                        if (true === (bool) Configuration::get('PRO_MESSAGE_CHECKER_CUSTOM_LIST')) {
                            $bannedContent = \explode(',', Configuration::get('PRO_MESSAGE_CHECKER_CUSTOM_LIST'));
                            $banned = [];
                            foreach ($bannedContent as $string) {
                                if (\mb_strstr($message, $string)) {
                                    $banned[] = $string;
                                }
                            }
                            if (false === empty($banned)) {
                                $error[] = 'c1';
                            }
                        }
                    }

                    if (false === empty($error)) {
                        $baseUrl = $this->getUrl() . $_SERVER['REQUEST_URI'];
                        $params = (\parse_url($baseUrl, \PHP_URL_QUERY) ? '&' : '?') . 'error=' . \implode('', $error);
                        exit(\header('Refresh: 0; url=' . $baseUrl . $params));
                    }
                }
            }

            $errorCode = Tools::getValue('error');

            if (false === empty($errorCode)) {
                $errors = \str_split($errorCode, 2);

                $codes = [
                    'a0' => $this->l('The secret parameter is missing.'),
                    'a1' => $this->l('The secret parameter is invalid or malformed.'),
                    'a2' => $this->l('The response parameter is missing.'),
                    'a3' => $this->l('The response parameter is invalid or malformed.'),
                    'a4' => $this->l('The request is invalid or malformed.'),
                    'a5' => $this->l('The response is no longer valid. Either is too old or has been used previously.'),
                    'a6' => $this->l('The response is missing.'),
                    'a7' => $this->l('The security trust score is too low.'),
                    'b0' => $this->l('The e-mail address is not allowed.'),
                    'b1' => $this->l('The e-mail address is not allowed.'),
                    'b2' => $this->l('The e-mail address is not allowed.'),
                    'c0' => $this->l('The message includes links that are not allowed.'),
                    'c1' => $this->l('The message includes content that is not allowed.'),
                ];

                $errorMessage = [];
                foreach ($codes as $code => $key) {
                    if (\in_array($code, $errors, true)) {
                        $errorMessage[] = $key;
                    }
                }

                $this->context->smarty->assign([
                    'sp_errorMessage' => '<ol><li>' . \implode('</li><li>', $errorMessage) . '</li></ol>',
                ]);

                return $this->display(__FILE__, 'errorMessage.tpl');
            }
        }
    }

    /**
     * @param array $urls
     *
     * @return bool
     */
    private function lookupGoogleSafeBrowsingV4($urls)
    {
        $apiKey = Configuration::get('PRO_GOOGLE_SAFE_BROWSING_V4_API');

        if (false === (bool) $apiKey) {
            return false;
        }

        $urlValidate = [];
        foreach ($urls as $url) {
            if (true === Validate::isUrl($url)) {
                $urlValidate[] = $url;
            }
        }

        if (false === empty($urlValidate)) {
            return false;
        }
        $data = '{"client":{"clientId": "' . Tools::strtolower(Configuration::get('PS_SHOP_NAME')) . '", "clientVersion": "' . $this->version . '"}, "threatInfo": {"threatTypes": ["MALWARE", "SOCIAL_ENGINEERING"], "platformTypes": ["ALL_PLATFORMS"], "threatEntryTypes": ["URL"], "threatEntries": [{"url": "' . \implode('"}, {"url": "', $urlValidate) . '"}]}}';

        $ch = \curl_init();
        \curl_setopt($ch, \CURLOPT_URL, 'https://safebrowsing.googleapis.com/v4/threatMatches:find?key=' . $apiKey);
        \curl_setopt($ch, \CURLOPT_TIMEOUT, 10);
        \curl_setopt($ch, \CURLOPT_CONNECTTIMEOUT, 5);
        \curl_setopt($ch, \CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . Tools::strlen($data),
        ]);
        \curl_setopt($ch, \CURLOPT_POST, true);
        \curl_setopt($ch, \CURLOPT_POSTFIELDS, $data);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, \CURLOPT_SSL_VERIFYPEER, false);
        \curl_setopt($ch, \CURLOPT_SSL_VERIFYHOST, false);
        \curl_setopt($ch, \CURLOPT_USERAGENT, self::USER_AGENT);

        $response = \json_decode(\curl_exec($ch), true);
        \curl_close($ch);

        if (false === empty($response)) {
            return true;
        }

        return false;
    }

    /**
     * Generate TFA token.
     *
     * @throws PrestaShopDatabaseException
     *
     * @return array|string
     */
    private function getTfaToken()
    {
        $employees = $this->getEmployees();

        $tfaToken = [];
        foreach ($employees as $employee) {
            $tfaToken[] = $this->encrypt($employee['id_employee'] . $employee['email'] . $employee['passwd']);
        }

        return $tfaToken;
    }

    private function validate2fa()
    {
        if (true === $this->checkWhitelist('PRO_TWO_FACTOR_AUTH_WHITELIST')) {
            return;
        }

        $email = Tools::getValue('email');
        $passwd = Tools::getValue('passwd');
        if (true === (bool) Tools::isSubmit('submitLogin') && true === (bool) $email && true === (bool) $passwd) {
            $cookieName = '2FA';

            if ($this->getCookieToken($cookieName, true) !== $_COOKIE[$this->cookieName($cookieName)]) {
                $this->blockRequest(403);
            }
        }
    }

    /**
     * Response HTTP header 403 and block the request.
     *
     * @param int $code
     */
    private function blockRequest($code)
    {
        \http_response_code($code);
        \header('Connection: Close');
        \header('Cache-Control: max-age=0, private, no-store, no-cache, must-revalidate');

        $favicon = Configuration::get('PS_FAVICON');

        if (true === (bool) $favicon) {
            $faviconUpdateTime = Configuration::get('PS_IMG_UPDATE_TIME');
            $faviconLink = _PS_IMG_ . $favicon . '?' . $faviconUpdateTime;
        } else {
            $faviconLink = '';
        }

        $this->context->smarty->assign([
            'sp_faviconLink' => $faviconLink,
            'sp_code' => $code,
            'sp_lang' => $this->context->language->iso_code,
        ]);

        echo $this->display(__FILE__, 'blockRequest.tpl');

        exit;
    }

    /**
     * Log backoffice logins.
     */
    private function logBackOfficeLogins()
    {
        if (true === (bool) Configuration::get('PRO_LOGIN_ATTEMPTS_LOG')) {
            $email = Tools::getValue('email');
            $passwd = Tools::getValue('passwd');

            if (Tools::isSubmit('submitLogin') && true === (bool) $email && true === (bool) $passwd) {
                // Validate password issue
                if (Tools::strlen($passwd) < 5) {
                    return;
                }

                $employee = new Employee();
                $isLoaded = $employee->getByEmail($email, $passwd);

                if (true === (bool) $isLoaded) {
                    $msg = $this->l('New login');
                } else {
                    $msg = $this->l('Login failed');
                }
                $this->logVuln(
                    $email,
                    $msg,
                    self::LOG_LOGIN_ATTEMPTS,
                    null
                );
            }
        }
    }

    /**
     * Log vulnerabilities.
     *
     * @param string $value
     * @param string $typeVuln
     * @param string $fileName
     * @param string $type
     *
     * @throws PrestaShopException
     */
    private function logVuln($value, $typeVuln, $fileName, $type)
    {
        $date = Tools::displayDate(\date('Y-m-d H:i:s'), null, true);
        $data = [];
        $data[] = $this->getClientIP();
        $data[] = '- -';
        $data[] = '[' . $date . ']';

        if (null !== $typeVuln) {
            $data[] = '[' . $typeVuln . ']';
        }

        if (null !== $type) {
            $data[] = '"' . $type . ' ' . \rawurldecode($this->getUrl() . $_SERVER['REQUEST_URI']) . '"';
        }

        if (null !== $value) {
            $value = \str_replace(["\r", "\n"], '', $value);
            $data[] = '"' . \htmlentities($value) . '"';
        }

        \file_put_contents($this->getLogFile($fileName), \implode(' ', $data) . \PHP_EOL, \FILE_APPEND);
    }

    /**
     * Secure back office against brute force attacks.
     */
    private function backofficeLoginBruteforce()
    {
        if (true === (bool) Configuration::get('PRO_FAIL2BAN')) {
            $email = Tools::getValue('email');
            $passwd = Tools::getValue('passwd');
            $findTime = (int) Configuration::get('PRO_FIND_TIME') * 60;
            $eldestAccessTime = $this->getEldestAccessTry($email);
            $now = \time();
            $ip = $this->getClientIP();

            if (true === (bool) Tools::isSubmit('submitLogin') && true === (bool) $email && true === (bool) $passwd) {
                $banTime = (int) Configuration::get('PRO_BAN_TIME') * 60;
                $employeeBanTime = $this->getBanTime($email);

                if (($now - $employeeBanTime) <= $banTime) {
                    $this->ban();
                }

                // Validate password issue
                if (Tools::strlen($passwd) < 5) {
                    return;
                }

                $employee = new Employee();
                $isLoaded = $employee->getByEmail($email, $passwd);
                $userAgent = $this->getUserAgent();

                if (false === (bool) $isLoaded) {
                    if (true === (bool) $eldestAccessTime && ($now - $eldestAccessTime) <= $findTime) {
                        $this->addRecordTime($email, $ip, 1);

                        $this->ban(); // Exit
                    }
                    $this->addRecordTime($email, $ip, 0);

                    // Send e-mail
                    if (true === (bool) Configuration::get('PRO_SEND_MAIL') && false === $this->checkWhitelist('PRO_WHITELIST_IPS')) {
                        $date = Tools::displayDate(\date('Y-m-d H:i:s'), null, true);
                        $subject = $this->l('Security notice') . ': ' . $this->l('Login failed');
                        $body = [];
                        $body[] = $this->l('Unsuccessfully attempt to login. If this was not you, you should change the name of your admin directory.') . \PHP_EOL;
                        $body[] = $this->l('E-mail') . ': ' . $email;
                        $body[] = $this->l('Time') . ': ' . $date;
                        $body[] = $this->l('IP') . ': ' . $ip;
                        if (null !== $this->getCountry($ip)) {
                            $body[] = $this->l('Country') . ': ' . $this->getCountry($ip);
                        }
                        $body[] = $this->l('Device') . ': ' . $this->getDevice($userAgent);
                        $body[] = $this->l('Browser') . ': ' . $this->getBrowser($userAgent);
                        $body[] = $this->l('Platform') . ': ' . $this->getPlatform($userAgent);

                        // Only send mail if the user is not banned.
                        if (true === (bool) $eldestAccessTime && ($now - $eldestAccessTime) > $findTime) {
                            $this->sendMail($subject, \implode(\PHP_EOL, $body));
                        }
                    }
                } elseif (true === (bool) Configuration::get('PRO_SEND_MAIL_LOGIN') && false === $this->checkWhitelist('PRO_WHITELIST_IPS')) {
                    // Send e-mail
                    $subject = $this->l('Security notice') . ': ' . $this->l('New login');
                    $date = Tools::displayDate(\date('Y-m-d H:i:s'), null, true);
                    $body = [];
                    $body[] = $this->l('A new login was detected') . \PHP_EOL;
                    $body[] = $this->l('E-mail') . ': ' . $email;
                    $body[] = $this->l('Time') . ': ' . $date;
                    $body[] = $this->l('IP') . ': ' . $ip;
                    if (null !== $this->getCountry($ip)) {
                        $body[] = $this->l('Country') . ': ' . $this->getCountry($ip);
                    }
                    $body[] = $this->l('Device') . ': ' . $this->getDevice($userAgent);
                    $body[] = $this->l('Browser') . ': ' . $this->getBrowser($userAgent);
                    $body[] = $this->l('Platform') . ': ' . $this->getPlatform($userAgent);
                    // Only send mail if the user is not banned.
                    if (true === (bool) $eldestAccessTime && ($now - $eldestAccessTime) > $findTime) {
                        if (true === $employee::employeeExists($email)) {
                            $this->sendMail($subject, \implode(\PHP_EOL, $body));
                        }
                    }
                }

                if (true === (bool) $eldestAccessTime && ($now - $eldestAccessTime) <= $findTime) {
                    $this->addRecordTime($email, $ip, 1);

                    $this->ban(); // Exit
                }
            }
        }
    }

    /**
     * Lookup eldest access try by specific e-mail in database.
     *
     * @param string $email
     *
     * @return int
     */
    private function getEldestAccessTry($email)
    {
        $maxRetry = (int) Configuration::get('PRO_MAX_RETRY');

        $query = 'SELECT IF(COUNT(*) = ' . (int) $maxRetry . ', MIN(access_time), \'0000-00-00 00:00:00\') AS access_time FROM (SELECT access_time FROM ' . _DB_PREFIX_ . 'securitypro WHERE banned = 0 AND email = "' . pSQL($email) . '" ORDER BY access_time DESC LIMIT ' . (int) $maxRetry . ') tmp';
        $accessStats = Db::getInstance()->getRow($query);

        return $accessStats ? \strtotime($accessStats['access_time']) : 0;
    }

    /**
     * Lookup ban time for specific e-mail in database.
     *
     * @param string $email
     *
     * @throws PrestaShopDatabaseException
     *
     * @return int
     */
    private function getBanTime($email)
    {
        $sql = new DbQuery();
        $sql->select('MAX(access_time) AS access_time');
        $sql->from('securitypro');
        $sql->where('banned = 1');
        $sql->where('email = "' . pSQL($email) . '"');
        $result = Db::getInstance()->executeS($sql);

        if (null !== ($result[0]['access_time'])) {
            return \strtotime($result[0]['access_time']);
        }

        return 0;
    }

    /**
     * Ban user.
     */
    private function ban()
    {
        if (true === (bool) Configuration::get('PRO_FAIL2BAN_LOG')) {
            $this->logVuln(
                Tools::getValue('email'),
                $this->l('Brute force attack'),
                self::LOG_BRUTE_FORCE,
                null
            );
        }

        $this->context->employee->logout();
        exit;
    }

    /**
     * Add record time to database.
     *
     * @param string $email
     * @param string $ip
     * @param string $ban
     *
     * @throws PrestaShopDatabaseException
     */
    private function addRecordTime($email, $ip, $ban)
    {
        Db::getInstance()->insert('securitypro', [
            'email' => pSQL($email),
            'ip' => pSQL($ip),
            'banned' => (int) $ban,
        ]);
    }

    /**
     * Lookup country by IP addres.
     *
     * @param string $ip
     *
     * @return string|null
     */
    private function getCountry($ip)
    {
        $url = 'https://www.iplocate.io/api/lookup/' . $ip;

        $content = $this->cachedJsonDecodedContent($url, null, $ip, 2629746);

        if (false !== $content) {
            $country = $content['country'];
        } else {
            $country = null;
        }

        return $country;
    }

    /**
     * Send e-mail.
     *
     * @param string $subject
     * @param string $body
     */
    private function sendMail($subject, $body)
    {
        $emails = \explode(',', Configuration::get('PRO_GENERAL_EMAIL'));

        $domain = $this->domain();

        foreach ($emails as $email) {
            $header = [];
            $header[] = 'MIME-Version: 1.0';
            $header[] = 'Content-Type: text/plain; charset=utf-8';
            $header[] = 'Content-Transfer-Encoding: 8bit';
            $header[] = 'From: no-reply@' . $domain;
            $header[] = 'Reply-to: no-reply@' . $domain;
            $header[] = 'Importance: High';
            $header[] = 'X-Priority: 1';
            $header[] = 'X-Sender: no-reply@' . $domain;

            $headers = \implode(\PHP_EOL, $header);

            \mail($email, $subject, $body, $headers);
        }
    }

    /**
     * Secure back office against brute force attacks.
     */
    private function twoFactorAuthForce()
    {
        if (true === (bool) Configuration::get('PRO_TWO_FACTOR_AUTH_FORCE')) {
            $email = Tools::getValue('email');
            $passwd = Tools::getValue('passwd');

            if (Tools::isSubmit('submitLogin') && true === (bool) $email && true === (bool) $passwd) {
                // Validate password issue
                if (Tools::strlen($passwd) < 5) {
                    return;
                }

                $employee = new Employee();
                $isLoaded = $employee->getByEmail($email, $passwd);

                if (true === (bool) $isLoaded) {
                    $this->deleteTFALoginToken();
                }
            }
        }
    }

    /**
     * Display security HTTP headers.
     */
    private function getSecurityHeaders()
    {
        if (true === (bool) Configuration::get('PRO_CLICK_JACKING')) {
            \header('X-Frame-Options: SAMEORIGIN');
            \header('Content-Security-Policy: frame-ancestors \'self\'');
            \header('Permissions-Policy: sync-xhr=(self "' . $this->getShopUrl() . '")');
        }
        if (true === (bool) Configuration::get('PRO_X_XSS_PROTECTION')) {
            \header('X-XSS-Protection: 1; mode=block');
        }
        if (true === (bool) Configuration::get('PRO_X_CONTENT_TYPE_OPTIONS')) {
            \header('X-Content-Type-Options: nosniff');
        }
        if (true === (bool) Configuration::get('PRO_STRICT_TRANSPORT_SECURITY')) {
            $string = [];
            $string[] = 'Strict-Transport-Security: max-age=63072000';
            if ('on' === Configuration::get('PRO_HSTS_SETTINGS_1')) {
                $string[] = 'includeSubDomains';
            }
            if ('on' === Configuration::get('PRO_HSTS_SETTINGS_0')) {
                $string[] = 'preload';
            }
            \header(\implode('; ', $string));
        }
        if (true === (bool) Configuration::get('PRO_EXPECT_CT')) {
            \header('Expect-CT: max-age=7776000');
        }
        if (true === (bool) Configuration::get('PRO_REFERRER_POLICY')) {
            \header('Referrer-Policy: no-referrer-when-downgrade');
        }
        if (true === (bool) Configuration::get('PRO_X_PERMITTED_CROSS_DOMAIN_POLICY')) {
            \header('X-Permitted-Cross-Domain-Policies: master-only');
        }
        if (true === (bool) Configuration::get('PRO_X_DOWNLOAD_OPTIONS')) {
            \header('X-Download-Options: noopen');
        }
        if (true === (bool) Configuration::get('PRO_ACCESS_CONTROL_ALLOW_METHODS')) {
            if (false === (bool) Configuration::get('PS_WEBSERVICE')) {
                \header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            }
        }
        if (true === (bool) Configuration::get('PRO_UNSET_HEADERS')) {
            \header_remove('Powered-By');
            \header_remove('X-Powered-By');
            \header_remove('Server');
        }
    }

    /**
     * Get Firewall.
     */
    private function getFirewall()
    {
        if (false === $this->validateGoogleBotIp()) {
            $ip = $this->getClientIP();
            $blocked = false;

            if (false === $this->checkWhitelist('PRO_FIREWALL_WHITELIST') && '54.243.46.120' !== $ip) {
                $this->googleRecaptchaCheck();

                // Firewall rule: Bot check
                if (true === (bool) Configuration::get('PRO_FIREWALL_CHECK_BOT')) {
                    $this->checkIfBot($ip);
                }

                // Firewall rules: SQLi, XXS, SHELL, HTML
                if (0 !== (int) Configuration::get('PRO_FIREWALL_SQL_CHECK')
                    || 0 !== (int) Configuration::get('PRO_FIREWALL_XXS_CHECK')
                    || 0 !== (int) Configuration::get('PRO_FIREWALL_SHELL_CHECK')
                    || 0 !== (int) Configuration::get('PRO_FIREWALL_HTML_CHECK')) {
                    $this->checkGet();
                    $this->checkPost();
                }

                // Firewall rule: RFI
                if (true === (bool) Configuration::get('PRO_FIREWALL_RFI_CHECK')) {
                    if (true === $this->remoteFileInclusion()) {
                        if (true === (bool) Configuration::get('PRO_FIREWALL_LOG')) {
                            $this->logVuln(
                                null,
                                'RFI',
                                self::LOG_FIREWALL,
                                null
                            );
                        }
                        $blocked = true;
                        $this->blockRequest(403);
                    }
                }

                // Firewall rule: Dir traversal
                if (true === (bool) Configuration::get('PRO_DIR_TRAVERSAL')) {
                    if (true === $this->dirTraversal()) {
                        if (true === (bool) Configuration::get('PRO_FIREWALL_LOG')) {
                            $this->logVuln(
                                null,
                                $this->l('Directory traversal'),
                                self::LOG_FIREWALL,
                                null
                            );
                        }
                        $blocked = true;
                        $this->blockRequest(403);
                    }
                }

                // Firewall rule: XST
                if (true === (bool) Configuration::get('PRO_FIREWALL_XST_CHECK')) {
                    if (true === $this->checkTrackTrace()) {
                        if (true === (bool) Configuration::get('PRO_FIREWALL_LOG')) {
                            $this->logVuln(
                                null,
                                'XST',
                                self::LOG_FIREWALL,
                                null
                            );
                        }
                        $blocked = true;
                        $this->blockRequest(405);
                    }
                }

                // Anti-SPAM: Block TOR network
                if (true === (bool) Configuration::get('PRO_BLOCK_TOR')) {
                    if (false === $this->context->cookie->__get('SecurityProTor')) {
                        if (true === $this->isTorExitPoint($this->getClientIP())) {
                            $this->context->cookie->__set('SecurityProTor', $this->getCookieToken('TOR')); // is TOR
                        } else {
                            $this->context->cookie->__set('SecurityProTor', '0'); // is not TOR
                        }
                        $this->context->cookie->write();
                    }

                    // Block if TOR
                    if ($this->getCookieToken('TOR') === $this->context->cookie->__get('SecurityProTor')) {
                        $blocked = true;
                        $this->blockRequest(403);
                    }
                }

                // Firewall rule: Anti-flood
                if (true === (bool) Configuration::get('PRO_ANTI_FLOOD')) {
                    if ($this->context->controller instanceof CartController) {
                        $blocked = true;
                        $this->antiFlood($ip);
                    }
                }

                // Block file upload
                if (true === (bool) Configuration::get('PRO_BLOCK_FILE_UPLOAD')) {
                    if (true === $this->blockFileUpload()) {
                        if (true === (bool) Configuration::get('PRO_FIREWALL_LOG')) {
                            $this->logVuln(
                                null,
                                $this->l('Block file-upload'),
                                self::LOG_FIREWALL,
                                null
                            );
                        }
                        $blocked = true;
                        $this->blockRequest(403);
                    }
                }

                // Block file upload
                if (true === (bool) Configuration::get('PRO_BLOCK_SCAN_FILE_UPLOAD')) {
                    if (true === $this->checkFileOnUpload()) {
                        $blocked = true;
                        $this->blockRequest(403);
                    }
                }

                // Ban IP addresses
                if (0 !== (int) Configuration::get('PRO_BAN_IP_ACTIVATE') && true === (bool) Configuration::get('PRO_BAN_IP')) {
                    if (true === $this->blockIp()) {
                        $blocked = true;
                    }
                }

                // Block user agents
                if (0 !== (int) Configuration::get('PRO_BLOCK_USER_AGENT_ACTIVATE') && true === (bool) Configuration::get('PRO_BLOCK_USER_AGENT')) {
                    $blocked = true;
                    $this->blockUserAgent();
                }

                // Anti-SPAM: Log 404 requests
                if (true === (bool) Configuration::get('PRO_PAGE_NOT_FOUND_LOG') && (false === $blocked)) {
                    if ($this->context->controller instanceof PageNotFoundControllerCore) {
                        if (false === Tools::strpos($_SERVER['REQUEST_URI'], 'index.php?controller=404')) {
                            $this->logPageNotFound(self::LOG_PAGE_NOT_FOUND);
                        }
                    }
                }
            }
        }

        // Anti-SPAM: Token check
        if (true === (bool) Configuration::get('PRO_FAKE_ACCOUNTS')) {
            $this->context->cookie->__set('SecurityProCSRF', $this->getCookieToken('CSRF'));
            $this->context->cookie->write();
        }
    }

    /**
     * Check if Google IP.
     *
     * @return bool
     */
    private function validateGoogleBotIp()
    {
        $userAgent = $this->getUserAgent();

        if (false !== (bool) $userAgent) {
            if (\preg_match('/Google/', $userAgent)) {
                $host = \gethostbyaddr($this->getClientIP());

                return \preg_match('/\.googlebot\.com$/i', $host); // True if Google
            }
        }

        return false; // Not Google
    }

    /**
     * Google reCAPTCHA check.
     */
    private function googleRecaptchaCheck()
    {
        if (null !== Tools::getValue('g-recaptcha-submit')) {
            // Validate reCAPTCHA box
            if (null !== Tools::getValue('g-recaptcha-response') && false === empty(Tools::getValue('g-recaptcha-response'))) {
                // Google reCAPTCHA API secret key
                if (true === (bool) Configuration::get('PRO_FIREWALL_RECAPTCHA_SECRET')) {
                    $secretKey = Configuration::get('PRO_FIREWALL_RECAPTCHA_SECRET');
                } else {
                    $secretKey = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';
                }
                // Verify the reCAPTCHA response

                $url = 'https://www.google.com/recaptcha/api/siteverify';
                $params = [
                    'secret' => $secretKey,
                    'response' => Tools::getValue('g-recaptcha-response'),
                ];

                $content = $this->remoteContent($url, $params);

                if (false !== $content) {
                    // Decode json data
                    $responseData = \json_decode($content, true);
                    // If reCAPTCHA response is valid
                    if ($responseData['success']) {
                        // Posted form data

                        // Unlock security vulnerability
                        $this->context->cookie->__set('SecurityProRecaptcha', $this->getCookieToken('RECAPTCHA'));

                        // Unlock honeypot
                        $this->context->cookie->__set('SecurityProHoneypot', '0');
                        $this->context->cookie->write();

                        // Reset ddos
                        if (true === (bool) Configuration::get('PRO_ANTI_FLOOD')) {
                            $ip = $this->getClientIP();
                            $this->antiFlood($ip, true);
                        }
                    }
                } else {
                    return;
                }
            }
        }
    }

    /**
     * Handle the anti-flood feature.
     *
     * @param string $ip
     * @param bool $clear
     *
     * @throws PrestaShopException
     */
    private function antiFlood($ip, $clear = false)
    {
        $scriptTmpDir = _PS_CACHE_DIR_ . $this->name . self::DIR_ANTI_FLOOD;
        $ctrl = $this->encrypt('ctrl') . '.json';
        $controlDb = $scriptTmpDir . \DIRECTORY_SEPARATOR . $ctrl;
        $controlLockDir = $scriptTmpDir . \DIRECTORY_SEPARATOR . 'lock';
        $controlLockFile = $controlLockDir . \DIRECTORY_SEPARATOR . $this->encrypt($ip) . '.json';
        $control = [];

        // Make sure the paths exists
        if (false === \is_dir($scriptTmpDir)) {
            \mkdir($scriptTmpDir, 0755, true);
        }

        if (false === \is_dir($controlLockDir)) {
            \mkdir($controlLockDir, 0755, true);
        }

        if (true === $clear) {
            $control[$ip]['c'] = 1;
            $control[$ip]['t'] = 0;
            Tools::deleteFile($controlLockFile);
        } else {
            if (\file_exists($controlLockFile)) {
                if (\time() - \filemtime($controlLockFile) > (int) Configuration::get('PRO_ANTI_BAN_TIME')) {
                    // This user has complete his punishment
                    Tools::deleteFile($controlLockFile);
                } else {
                    // Too many request
                    $this->vulnDetectedHtml(
                        null,
                        'DDoS',
                        3,
                        null
                    );
                    \touch($controlLockFile);
                }
            }

            $continue = true;
            // Counting requests and last access time
            if (\file_exists($controlDb) && \filesize($controlDb) > 0) {
                $fh = \fopen($controlDb, 'rb');
                $dimFile = \filesize($controlDb);
                $dataFile = \json_decode(\fread($fh, $dimFile), true);
                \fclose($fh);
                // If json_decode fails
                if (false === $dataFile || null === $dataFile) {
                    $continue = false;
                } else {
                    $control = \array_merge($control, $dataFile);
                }
                // If file is too big
                $limitSize = 20480;
                if ($dimFile > $limitSize) {
                    $control = [];
                }
            }

            if (true === $continue) {
                if (false === empty($control[$ip])) {
                    if ((\time() - $control[$ip]['t']) < (int) Configuration::get('PRO_ANTI_REQ_TIMEOUT')) {
                        ++$control[$ip]['c'];
                    } else {
                        $control[$ip]['c'] = 1;
                    }
                } else {
                    $control[$ip]['c'] = 1;
                }
                $control[$ip]['t'] = \time();

                if ($control[$ip]['c'] >= (int) Configuration::get('PRO_ANTI_MAX_REQUESTS')) {
                    // This user did too many requests within a very short period of time
                    $fh = \fopen($controlLockFile, 'wb');
                    \fwrite($fh, $ip);
                    \fclose($fh);
                }
            }
        }

        // Writing updated control table
        $fh = \fopen($controlDb, 'wb');
        \fwrite($fh, \json_encode($control));
        \fclose($fh);
    }

    /**
     * Vuln detected HTML.
     *
     * @param string|null $value
     * @param string $typeVuln
     * @param int $conf
     * @param string $type
     *
     * @throws PrestaShopException
     *
     * @return bool|string
     */
    private function vulnDetectedHtml($value, $typeVuln, $conf, $type)
    {
        // PrestaShop core whitelist
        if ($this->isInWhitelistForGeolocation($this->getClientIP())) {
            return false;
        }

        if (true === (bool) Configuration::get('PRO_FIREWALL_LOG')) {
            $this->logVuln(
                $value,
                $typeVuln,
                self::LOG_FIREWALL,
                $type
            );
        }

        switch ($conf) {
            case 1:
                $this->blockRequest(403);
                break;
            case 2:
                Tools::redirect('pagenotfound');
                break;
            case 3:
                $this->displayRecaptcha();
                break;
        }
    }

    /**
     * Display reCAPTCHA and set headers.
     */
    private function displayRecaptcha()
    {
        \http_response_code(403);

        \header('Connection: Close');
        \header('Cache-Control: max-age=0, private, no-store, no-cache, must-revalidate');

        if (true === (bool) Configuration::get('PRO_FIREWALL_RECAPTCHA_SITE_KEY')) {
            $siteKey = Configuration::get('PRO_FIREWALL_RECAPTCHA_SITE_KEY');
        } else {
            $siteKey = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';
        }

        $favicon = Configuration::get('PS_FAVICON');

        if (true === (bool) $favicon) {
            $faviconUpdateTime = Configuration::get('PS_IMG_UPDATE_TIME');
            $faviconLink = _PS_IMG_ . $favicon . '?' . $faviconUpdateTime;
        } else {
            $faviconLink = '';
        }

        $this->context->smarty->assign([
            'sp_shopName' => Configuration::get('PS_SHOP_NAME'),
            'sp_getfavicon' => $faviconLink,
            'sp_lang' => $this->context->language->iso_code,
            'sp_imgPath' => $this->_path,
            'sp_ip' => $this->getClientIP(),
            'sp_siteKey' => $siteKey,
            'sp_faviconLink' => $faviconLink,
        ]);

        echo $this->display(__FILE__, 'displayFirewall.tpl');

        exit;
    }

    /**
     * Check if bot.
     *
     * @param string $ip
     *
     * @throws PrestaShopException
     */
    private function checkIfBot($ip)
    {
        // New visitor
        if (false === $this->context->cookie->__get('SecurityProHoneypot')) {
            $this->context->cookie->__set('SecurityProHoneypot', '0');

            // Check if this host is a bad bot
            if (true === $this->isBot($ip)) {
                $this->context->cookie->__set('SecurityProHoneypot', $this->getCookieToken('HONEYPOT'));
            }

            $this->context->cookie->write();
        }

        // Old visitor
        if ($this->getCookieToken('HONEYPOT') === $this->context->cookie->__get('SecurityProHoneypot')) {
            if (true === $this->isBot($ip)) {
                $this->vulnDetectedHtml(
                    null,
                    $this->getVisitorType($ip),
                    3,
                    null
                );
            }
        }
    }

    /**
     * Get visitor type from Honeypot API.
     *
     * @param string $ip
     *
     * @return string
     */
    private function getVisitorType($ip)
    {
        $searchEngine = $this->l('Search Engine');
        $suspicious = $this->l('Suspicious');
        $harvester = $this->l('Harvester');
        $commentSpammer = $this->l('Comment Spammer');

        $visitorType = [
            0 => $searchEngine,
            1 => $suspicious,
            2 => $harvester,
            3 => $suspicious . ' & ' . $harvester,
            4 => $commentSpammer,
            5 => $suspicious . ' & ' . $commentSpammer,
            6 => $harvester . ' & ' . $commentSpammer,
            7 => $suspicious . ' & ' . $harvester . ' & ' . $commentSpammer,
        ];

        $honeypotQuery = $this->honeypotQuery($ip);
        if ($honeypotQuery < 8) {
            return $visitorType[$honeypotQuery];
        }

        return $this->l('Unknown');
    }

    /**
     * Check GET request firewall.
     */
    private function checkGet()
    {
        foreach ($_GET as $value) {
            if (\is_array($value)) {
                $flattened = $this->arrayFlatten($value);
                foreach ($flattened as $subValue) {
                    if (0 !== (int) Configuration::get('PRO_FIREWALL_SQL_CHECK')) {
                        $this->sqlCheck($subValue, 'GET');
                    }
                    if (0 !== (int) Configuration::get('PRO_FIREWALL_XXS_CHECK')) {
                        $this->xssCheck($subValue, 'GET');
                    }
                    if (0 !== (int) Configuration::get('PRO_FIREWALL_SHELL_CHECK')) {
                        $this->shellCheck($subValue, 'GET');
                    }
                }
            } else {
                if (0 !== (int) Configuration::get('PRO_FIREWALL_SQL_CHECK')) {
                    $this->sqlCheck($value, 'GET');
                }
                if (0 !== (int) Configuration::get('PRO_FIREWALL_XXS_CHECK')) {
                    $this->xssCheck($value, 'GET');
                }
                if (0 !== (int) Configuration::get('PRO_FIREWALL_SHELL_CHECK')) {
                    $this->shellCheck($value, 'GET');
                }
            }
        }
    }

    /**
     * Array flatten.
     *
     * @return array
     */
    private function arrayFlatten(array $array)
    {
        $flatten = [];
        \array_walk_recursive($array, static function ($value) use (&$flatten) {
            $flatten[] = $value;
        });

        return $flatten;
    }

    /**
     * Check for SQLi.
     *
     * @param string $value
     * @param string $type
     *
     * @throws PrestaShopException
     *
     * @return bool|string
     */
    private function sqlCheck($value, $type)
    {
        $conf = (int) Configuration::get('PRO_FIREWALL_SQL_CHECK');
        if (3 === $conf) {
            if ($this->getCookieToken('RECAPTCHA') === $this->context->cookie->__get('SecurityProRecaptcha')) {
                return false;
            }
        }

        $badWords = [
            "OR '1",
            'DROP TABLE',
            'OR boolean',
            'SELECT * FROM',
            'SELECT FROM',
            'Table_schema,0x3e,',
            'UDPATE users SET',
            'WHERE username',
            'concat(',
            'mid((select',
            'or HAVING',
            'unhex(hex(Concat(',
            'union(',
        ];

        foreach ($badWords as $badWord) {
            if (false !== Tools::strpos(Tools::strtolower($value), Tools::strtolower($badWord))) {
                // String contains some vuln.
                return $this->vulnDetectedHtml(
                    $value,
                    'SQLi',
                    $conf,
                    $type
                );
            }
        }
    }

    /**
     * Check for XSS.
     *
     * @param string $value
     * @param string $type
     *
     * @throws PrestaShopException
     *
     * @return bool|string
     */
    private function xssCheck($value, $type)
    {
        $conf = (int) Configuration::get('PRO_FIREWALL_XXS_CHECK');
        if (3 === $conf) {
            if ($this->getCookieToken('RECAPTCHA') === $this->context->cookie->__get('SecurityProRecaptcha')) {
                return false;
            }
        }

        $badWords = [
            '<image',
            '<img',
            '<script',
            '<style',
            '<svg',
            'String.fromCharCode(',
            'alert(',
            'cmd=',
            'document.cookie',
            'img>',
            'javascript:',
            'onerror()',
            'onmouseover="',
            'script>',
            'svg onload',
            'window.',
            '<BODY onload',
        ];

        foreach ($badWords as $badWord) {
            if (false !== Tools::strpos(Tools::strtolower($value), Tools::strtolower($badWord))) {
                // String contains some Vuln.
                return $this->vulnDetectedHtml(
                    $value,
                    'XSS',
                    $conf,
                    $type
                );
            }
        }
    }

    /**
     * Check for SHELL injections.
     *
     * @param string $value
     * @param string $type
     *
     * @throws PrestaShopException
     *
     * @return bool|null
     */
    private function shellCheck($value, $type)
    {
        $conf = (int) Configuration::get('PRO_FIREWALL_SHELL_CHECK');
        if (3 === $conf) {
            if ($this->getCookieToken('RECAPTCHA') === $this->context->cookie->__get('SecurityProRecaptcha')) {
                return false;
            }
        }

        $badWords = [
            '& id',
            '&& ls',
            '/bin/bash',
            '/bin/ls',
            '/etc/passwd',
            '/usr/bin/id',
            '; id',
            '; ls',
            ';id\n',
            ';id|',
            '\nid|',
            '`id`',
            'db_query',
            'download_file',
            'edit_file',
            'eval(',
            'find_text',
            'ftp_brute',
            'ftp_file_up',
            'logname',
            'mail_file',
            'mkmodule',
            'mysql_dump',
            'search_text',
            'umask',
            '| id',
            '| ls',
            '|id;',
            '|id|',
            '|nid\n',
        ];

        foreach ($badWords as $badWord) {
            if (false !== Tools::strpos(Tools::strtolower($value), Tools::strtolower($badWord))) {
                // String contains some Vuln.
                return $this->vulnDetectedHtml(
                    $value,
                    'SHELL',
                    $conf,
                    $type
                );
            }
        }
    }

    /**
     * Check POST request firewall.
     */
    private function checkPost()
    {
        foreach ($_POST as $value) {
            if (\is_array($value)) {
                $flattened = $this->arrayFlatten($value);
                foreach ($flattened as $subValue) {
                    if (0 !== (int) Configuration::get('PRO_FIREWALL_SQL_CHECK')) {
                        $this->sqlCheck($subValue, 'POST');
                    }
                    if (0 !== (int) Configuration::get('PRO_FIREWALL_XXS_CHECK')) {
                        $this->xssCheck($subValue, 'POST');
                    }
                    if (0 !== (int) Configuration::get('PRO_FIREWALL_SHELL_CHECK')) {
                        $this->shellCheck($subValue, 'POST');
                    }
                }
            } else {
                if (0 !== (int) Configuration::get('PRO_FIREWALL_SQL_CHECK')) {
                    $this->sqlCheck($value, 'POST');
                }
                if (0 !== (int) Configuration::get('PRO_FIREWALL_XXS_CHECK')) {
                    $this->xssCheck($value, 'POST');
                }
                if (0 !== (int) Configuration::get('PRO_FIREWALL_SHELL_CHECK')) {
                    $this->shellCheck($value, 'POST');
                }
            }
        }
    }

    /**
     * Check for remote file inclusion.
     *
     * @return bool
     */
    private function remoteFileInclusion()
    {
        $whitelist = [
            'back',
            'scope',
        ];

        foreach ($_GET as $value => $key) {
            if (false === \in_array($value, $whitelist, true) && \is_string($key)) { // The key can somehow be an array.
                $pattern = "%00|(?:((?:ht|f)tp(?:s?)|file|webdav)\:\/\/|~\/|\/).*\.\w{2,3}|(?:((?:ht|f)tp(?:s?)|file|webdav)%3a%2f%2f|%7e%2f%2f).*\.\w{2,3}";
                if (\preg_match('/^.*(' . $pattern . ').*/i', $key)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check for directory traversal attack.
     *
     * @return bool
     */
    private function dirTraversal()
    {
        $pattern = "\.\.\/|\.\.\\|%2e%2e%2f|%2e%2e\/|\.\.%2f|%2e%2e%5c";

        if (\preg_match('/^.*(' . $pattern . ').*/i', $_SERVER['QUERY_STRING'])) {
            return true;
        }

        return false;
    }

    /**
     * Check if the HTTP request is TRACK or TRACE.
     *
     * @return bool
     */
    private function checkTrackTrace()
    {
        if ('TRACK' === $_SERVER['REQUEST_METHOD'] || 'TRACE' === $_SERVER['REQUEST_METHOD']) {
            return true;
        }

        return false;
    }

    /**
     * Log page not found.
     *
     * @param string $fileName
     *
     * @throws PrestaShopException
     */
    private function logPageNotFound($fileName)
    {
        $ip = $this->getClientIP();

        // Get type
        if (true === $this->validateHoneyPotApi(Configuration::get('PRO_HONEYPOT_API'))) {
            if (false !== $this->honeypotQuery($ip)) {
                $visitor = $this->getVisitorType($ip);
            } else {
                $visitor = null;
            }
        } else {
            $visitor = null;
        }
        $date = Tools::displayDate(\date('Y-m-d H:i:s'), null, true);
        $content = [];
        $content[] = $ip;
        $content[] = '- -';
        $content[] = '[' . $date . ']';
        if (null !== $visitor) {
            $content[] = '[' . $visitor . ']';
        }
        $content[] = '[404] "GET ' . \rawurldecode($this->getUrl() . $_SERVER['REQUEST_URI']) . '"';

        \file_put_contents($this->getLogFile($fileName), \implode(' ', $content) . \PHP_EOL, \FILE_APPEND);
    }

    /**
     * Block upload of files.
     *
     * @return bool
     */
    private function blockFileUpload()
    {
        if (isset($_FILES) && false === empty($_FILES)) {
            foreach ($_FILES as $file) {
                if (0 === $file['error']) {
                    $tmpName = $file['tmp_name'];
                    if (false === empty($tmpName)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check file for malware on upload.
     *
     * @return bool
     */
    private function checkFileOnUpload()
    {
        if (isset($_FILES) && false === empty($_FILES)) {
            foreach ($_FILES as $file) {
                if (0 === $file['error']) {
                    $tmpName = $file['tmp_name'];
                    if (false === empty($tmpName)) {
                        if (true === $this->isInfectedFile($tmpName)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Scan file for malware.
     *
     * @param string $file
     *
     * @return bool
     */
    private function isInfectedFile($file)
    {
        if ($this->isInfectedFavicon($file)) {
            return true;
        }

        if ($this->isInfectedGif($file)) {
            return true;
        }

        $scanExploits = [
            '/%\(\d+\-\d+\+\d+\)==\(\-\d+\+\d+\+\d+\)/si',
            '/(\$([a-zA-Z0-9]+)(\{|\[)([0-9]+)(\}|\])[\s\r\n]*\.[\s\r\n]*){6}.*?(?=\})\}/i',
            '/(\$([a-zA-Z0-9]+)[\s\r\n]*\.[\s\r\n]*){6}/',
            '/(\$[\w\[\]\\\'\"]+\\.[\n\r]*){10}/i',
            '/(\$\w+=[^;]*)*;\$\w+=@?\$\w+\((?<=\().*?(?=\))\)/si',
            '/(\/)*etc\/+passwd\/*/si',
            '/(\/)*etc\/+shadow\/*/si',
            '/(\\\'|\")ev(\\\'|\")\.(\\\'|\")al(\\\'|\")\.(\\\'|\")\(\"\?>/i',
            '/(\\\\x[0-9abcdef]{2}[a-z0-9.-\/]{1,4}){4,}/i',
            '/(chr\([\d]+\)\.){8}/i',
            '/(eval|preg_replace|system|assert|passthru|(pcntl_)?exec|shell_exec|call_user_func(_array)?)\/\*[^\*]*\*\/\((?<=\().*?(?=\))\)/',
            '/(function|return|base64_decode).{,256}[^\\x00-\\x1F\\x7F-\\xFF]{3}/i',
            '/(noitcnuf\_etaerc|metsys|urhtssap|edulcni|etucexe\_llehs|ecalper\_rts|ecalper_rts)/i',
            '/(preg_replace(_callback)?|mb_ereg_replace|preg_filter)[\s\r\n]*\(.+(\/|\\\\x2f)(e|\\\\x65)[\\\'\"].*?(?=\))\)/i',
            '/;\$\w+\(\$\w+(,\s?\$\w+)+\);/i',
            '/@(include|include_once|require|require_once)[\s\r\n]+([\s\r\n]*\()?("|\\\')([^"\\\']*)(\\[0-9]{3,}.*?){2,}([^"\\\']*)("|\\\')[\s\r\n]*((?=\))\))?/si',
            '/@(include|include_once|require|require_once)[\s\r\n]+([\s\r\n]*\()?("|\\\')([^"\\\']*)(\\\\x[0-9a-f]{2,}.*?){2,}([^"\\\']*)("|\\\')[\s\r\n]*((?=\))\))?/si',
            '/@?include[\s\r\n]*(\([\s\r\n]*)?("|\\\')([^"\\\']*)(\.|\\\\056\\\\046\\\\2E)(\i|\\\\151|\\\\x69|\\\\105)(c|\\\\143\\\\099\\\\x63)(o|\\\\157\\\\111|\\\\x6f)(\"|\\\')((?=\))\))?/mi',
            '/@?include[\s\r\n]*(\([\s\r\n]*)?("|\\\')[\s\r\n]*[^\.]+\.(png|jpe?g|gif|bmp|ico).*?("|\\\')[\s\r\n]*((?=\))\))?/i',
            '/@eval[\s\r\n]*\((?<=\().*?(?=\))\)/',
            '/AddType\s+application\/x-httpd-(php|cgi)/i',
            '/GIF89/si',
            '/IIS\:\/\/localhost\/w3svc/i',
            '/IonCube\_loader/i',
            '/SetHandler[\s\r\n]*application\/x\-httpd\-php/i',
            '/Xsam[\s\r\n]*Xadoo/i',
            '/[\\\'\"][A-Za-z0-9+\/]{260,}={0,3}[\\\'\"]/',
            '/\$GLOBALS\[[\s\r\n]*\$GLOBALS[\\\'[a-z0-9]{4,}\\\'\]/i',
            '/\$GLOBALS\[\\\'[a-z0-9]{5,}\\\'\][\s\r\n]*=[\s\r\n]*\$[a-z]+\d+\[\d+\]\.\$[a-z]+\d+\[\d+\]\.\$[a-z]+\d+\[\d+\]\.\$[a-z]+\d+\[\d+\]\./i',
            '/\$[a-z0-9-_]+\[[^]]+\]\((?<=\().*?(?=\))\)/',
            '/\$\w=\$[a-zA-Z]\(\'\',\$\w\);\$\w\(\);/i',
            '/\$\{[\s\r\n]*(\\\'|\")\\\\x.*?(?=\})\}/i',
            '/\$\{[\s\r\n]*(\\\'|\")_(GET|POST|COOKIE|REQUEST|SERVER)(\\\'|\")[\s\r\n]*\}/i',
            '/\$_(GET|POST|COOKIE|REQUEST|SERVER)[\s\r\n]*\[[^\]]+\][\s\r\n]*\((?<=\().*?(?=\))\)/i',
            '/\$md5[\s\r\n]*=[\s\r\n]*.*create_function[\s\r\n]*\(.*?\);[\s\r\n]*\$.*?\)[\s\r\n]*;/si',
            '/\${[\s\r\n]*\${.*?}(.*)?}/i',
            '/\${\$[0-9a-zA-z]+}/i',
            '/\(\$[a-zA-Z0-9]+%\d==\(\d+\-\d+\+\d+\)/si',
            '/\(\)[\s\r\n]*{[\s\r\n]*[a-z:][\s\r\n]*;[\s\r\n]*}[\s\r\n]*;/',
            '/\/\*[a-z0-9]{5}\*\//i',
            '/\/\*god_mode_on\*\/eval\(base64_decode\([\"\\\'][^\"\\\']{255,}[\"\\\']\)\);[\s\r\n]*\/\*god_mode_off\*\//si',
            '/\<\false === -\-\#exec[\s\r\n]*cmd\=/i',
            '/\@serialize[\s\r\n]*\([\s\r\n]*(Array\(|\[)(\\\'|\")php(\\\'|\")[\s\r\n]*\=\>[\s\r\n]*\@phpversion[\s\r\n]*\((?<=\().*?(?=\))\)/si',
            '/\[\s\r\n]*=[\s\r\n]*\$GLOBALS[\s\r\n]*\;[\s\r\n]*\$[\s\r\n]*\{/i',
            '/\\\\[Xx](5[Ff])/i',
            '/\b(array_(diff|intersect)_u(key|assoc)|array_udiff)[\s\r\n]*\([\s\r\n]*([^,]+[\s\r\n]*,?)+[\s\r\n]*(base64_decode|php:\/\/input|str_rot13|gz(inflate|uncompress)|getenv|pack|\\\\?@?\$_(GET|REQUEST|POST|COOKIE|SERVER))[\s\r\n]*\[[^]]+\][\s\r\n]*\)+[\s\r\n]*;/',
            '/\b(array_filter|array_reduce|array_walk(_recursive)?|array_walk|assert_options|uasort|uksort|usort|preg_replace_callback|iterator_apply)[\s\r\n]*\([\s\r\n]*[^,]+,[\s\r\n]*(base64_decode|php:\/\/input|str_rot13|gz(inflate|uncompress)|getenv|pack|\\\\?@?\$_(GET|REQUEST|POST|COOKIE|SERVER)).*?(?=\))\)/',
            '/\b(eval|assert|passthru|exec|include|system|pcntl_exec|shell_exec|base64_decode|`|array_map|ob_start|call_user_func(_array)?)[\s\r\n]*\([\s\r\n]*(base64_decode|php:\/\/input|str_rot13|gz(inflate|uncompress)|getenv|pack|\\\\?@?\$_(GET|REQUEST|POST|COOKIE|SERVER)).*?(?=\))\)/',
            '/\x00\/\.\.\/|LD_PRELOAD/i',
            '/base64_decode[^;]+getallheaders/',
            '/chr[\s\r\n]*\([\s\r\n]*101[\s\r\n]*\)[\s\r\n]*\.[\s\r\n]*chr[\s\r\n]*\([\s\r\n]*118[\s\r\n]*\)[\s\r\n]*\.[\s\r\n]*chr[\s\r\n]*\([\s\r\n]*97[\s\r\n]*\)[\s\r\n]*\.[\s\r\n]*chr[\s\r\n]*\([\s\r\n]*108[\s\r\n]*\)/i',
            '/curl_init[\s\r\n]*\([\s\r\n]*[\"\\\']file:\/\/.*?(?=\))\)/i',
            '/eva1fYlbakBcVSir|bajatax|own3d|h4cked|0wned|0wn3d|hack3d|h4ck3d|pwned|pwn3d|r00ted/i',
            '/eval[\s\r\n]*\([\s\r\n]*base64_decode[\s\r\n]*\((?<=\().*?(?=\))\)/i',
            '/eval\((base64|eval|\$_|\$\$|\$[A-Za-z_0-9\{]*(\(|\{|\[))/i',
            '/explode[\s\r\n]*\(chr[\s\r\n]*\([\s\r\n]*\(?\d{3}([\s\r\n]*-[\s\r\n]*\d{3})?[\s\r\n]*\).*?(?=\))\)/si',
            '/extract\([\s\r\n]*\$_(GET|POST|COOKIE|REQUEST|SERVER).*?(?=\))\)/i',
            '/file\:file\:\/\//i',
            '/file_get_contents[\s\r\n]*\([\s\r\n]*base64_url_decode[\s\r\n]*\([\s\r\n]*@*\$_(GET|POST|SERVER|COOKIE|REQUEST).*?(?=\))\)/i',
            '/fwrite[\s\r\n]*(\(\w+\((?<=\().*?(?=\))\))?[^\)]*\$_(GET|POST|SERVER|COOKIE|REQUEST).*?(?=\))\)/si',
            '/hacked[\s\r\n]*by/i',
            '/killall[\s\r\n]*\-9/i',
            '/md5[\s\r\n]*\([\s\r\n]*@?\$_(GET|REQUEST|POST|COOKIE|SERVER)[^)]+\)[\s\r\n]*===?[\s\r\n]*[\\\'\"][0-9a-f]{32}[\\\'\"]/si',
            '/php_uname\(["\'asrvm]+\)/si',
            '/php_value[\s\r\n]*auto_prepend_file/i',
            '/rawurldecode[\s\r\n]*\(str_rot13[\s\r\n]*\((?<=\().*?(?=\))\)/i',
            '/register_[a-z]+_function[\s\r\n]*\([\s\r\n]*[\\\'\"][\s\r\n]*(eval|assert|passthru|exec|include|system|shell_exec|`).*?(?=\))\)/i',
            '/sha1[\s\r\n]*\([\s\r\n]*@?\$_(GET|REQUEST|POST|COOKIE|SERVER)[^)]+\)[\s\r\n]*===?[\s\r\n]*[\\\'\"][0-9a-f]{40}[\\\'\"]/si',
        ];

        $mimeType = 'text';
        if (\function_exists('mime_content_type')) {
            $mimeType = \mime_content_type($file);
        } elseif (\function_exists('finfo_open')) {
            $fileInfo = \finfo_open(\FILEINFO_MIME);
            $mimeType = \finfo_file($fileInfo, $file);
            \finfo_close($fileInfo);
        }

        $fc = @\php_strip_whitespace($file);

        if (0 === \mb_stripos($mimeType, 'text')) {
            foreach ($scanExploits as $pattern) {
                if (\preg_match($pattern, $fc, $match, \PREG_OFFSET_CAPTURE)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Detect infected favicon.
     *
     * @param string $file
     *
     * @return bool
     */
    private function isInfectedFavicon($file)
    {
        $filename = \pathinfo($file, \PATHINFO_FILENAME);
        $extension = \pathinfo($file, \PATHINFO_EXTENSION);

        return ((0 === Tools::strpos($filename, 'favicon_')) && ('ico' === $extension) && (Tools::strlen($filename) > 12)) || \preg_match('/^\.[\w]+\.ico/i', \trim($filename));
    }

    /**
     * Detect infected file.
     *
     * @param string $file
     *
     * @return bool
     */
    private function isInfectedGif($file)
    {
        if (false === \file_exists($file)) {
            return false;
        }

        $fp = \fopen($file, 'rb');
        $content = \fread($fp, 5);
        \fclose($fp);

        return 'GIF89' === $content;
    }

    /**
     * Block custom list of IP addresses.
     *
     * @throws PrestaShopException
     *
     * @return bool
     */
    private function blockIp()
    {
        $conf = (int) Configuration::get('PRO_BAN_IP_ACTIVATE');
        if (3 === $conf) {
            if ($this->getCookieToken('RECAPTCHA') === $this->context->cookie->__get('SecurityProRecaptcha')) {
                return false;
            }
        }

        if (false === (bool) Configuration::get('PRO_BAN_IP')) {
            return false;
        }

        $blacklist = \explode(',', Configuration::get('PRO_BAN_IP'));
        foreach ($blacklist as $list) {
            $range = \IPLib\Factory::rangeFromString($list);
            if ($range->contains(\IPLib\Factory::addressFromString($this->getClientIP()))) {
                $this->vulnDetectedHtml(
                    null,
                    $this->l('Block IP'),
                    $conf,
                    null
                );
            }
        }

        return false;
    }

    /**
     * Block custom list of User agents.
     *
     * @throws PrestaShopException
     *
     * @return bool
     */
    private function blockUserAgent()
    {
        $conf = (int) Configuration::get('PRO_BLOCK_USER_AGENT_ACTIVATE');
        if (3 === $conf) {
            if ($this->getCookieToken('RECAPTCHA') === $this->context->cookie->__get('SecurityProRecaptcha')) {
                return false;
            }
        }

        $userAgent = $this->getUserAgent();

        if (false !== (bool) $userAgent) {
            $blacklist = \explode(',', Configuration::get('PRO_BLOCK_USER_AGENT'));
            foreach ($blacklist as $list) {
                if (false !== Tools::strpos($userAgent, $list)) {
                    $this->vulnDetectedHtml(
                        null,
                        $this->l('Block UA'),
                        $conf,
                        null
                    );
                }
            }
        }

        return false;
    }

    /**
     * Log result from malware scanner.
     *
     * @param string $timeStamp
     * @param string $fileChanged
     * @param string $fileName
     */
    private function logMalwareScanner($timeStamp, $fileChanged, $fileName)
    {
        $content = [];
        $content[] = '[' . $timeStamp . ']';
        $content[] = $fileChanged;

        \file_put_contents($this->getLogFile($fileName), \implode(' ', $content) . \PHP_EOL, \FILE_APPEND);
    }

    /**
     * Delete old backups from Dropbox.
     *
     * @param int $backupSaved
     * @param string $fileName
     * @param string $inPath
     * @param string $outPath
     */
    private function dropboxGenerateBackup($backupSaved, $fileName, $inPath, $outPath)
    {
        $this->dropboxUploadFile($outPath, $inPath, $fileName);

        if (0 === $backupSaved) {
            return;
        }

        $client = $this->dropboxGetClient();
        if (isset($client->files->list_folder($outPath)['entries'])) {
            $value = $client->files->list_folder($outPath)['entries'];
        } else {
            $value = [];
        }

        if (false === empty($value)) {
            $i = \count($value);
            $j = 0;
            while ($i > $backupSaved) {
                $client->files->delete($value[$j]['path_lower']);
                --$i;
                ++$j;
            }
        }
    }

    /**
     * @param string $outPath
     * @param string $inPath
     * @param string $fileName
     */
    private function dropboxUploadFile($outPath, $inPath, $fileName)
    {
        $client = $this->dropboxGetClient();
        $client->files->upload($outPath . $fileName, $inPath . $fileName, 'overwrite');
    }

    /**
     * @param int $backupSaved
     * @param string $fileName
     * @param string $filePath
     * @param string $type
     *
     * @throws \Google\Exception
     */
    private function googleDriveGenerateBackup($backupSaved, $fileName, $filePath, $type)
    {
        $folderId = $this->googleDriveGenerateFolders()[$type];

        $this->googleDriveUploadFile($folderId, $filePath, $fileName);

        if (null !== ($this->googleDriveGetFileNames($folderId))) {
            $value = $this->googleDriveGetFileNames($folderId);
        } else {
            $value = [];
        }

        if (0 === $backupSaved) {
            return;
        }

        if (false === empty($value)) {
            $i = \count($value);
            $j = 0;
            while ($i > $backupSaved) {
                $this->googleDriveDeleteFile($value[$j]['id']);
                --$i;
                ++$j;
            }
            $this->googleDriveEmptyTrash();
        }
    }

    /**
     * @param int $folderId
     * @param string $path
     * @param string $fileName
     *
     * @throws \Google\Exception
     */
    private function googleDriveUploadFile($folderId, $path, $fileName)
    {
        $file = new Google_Service_Drive_DriveFile();
        $file->setParents([$folderId]);
        $file->setName($fileName);

        $chunkSizeBytes = 1048576;

        $client = $this->googleDriveGetClient();
        $api = new Google_Service_Drive($client);
        // Call the API with the media upload, defer so it does not immediately return.
        $client->setDefer(true);
        $request = $api->files->create($file);

        // Create a media file upload to represent our upload process.
        $media = new Google\Http\MediaFileUpload(
            $client,
            $request,
            'application/octet-stream',
            null,
            true,
            $chunkSizeBytes
        );
        $media->setFileSize(\filesize($path . $fileName));

        // Upload the various chunks. $status will be false until the process is complete.
        $status = false;
        $handle = \fopen($path . $fileName, 'rb');
        while (false === $status && false === \feof($handle)) {
            $chunk = $this->googleDriveReadChunk($handle, $chunkSizeBytes);
            $status = $media->nextChunk($chunk);
        }

        \fclose($handle);
    }

    /**
     * @param string $handle
     * @param int $chunkSize
     *
     * @return string
     */
    private function googleDriveReadChunk($handle, $chunkSize)
    {
        $byteCount = 0;
        $giantChunk = '';
        while (false === \feof($handle)) {
            $chunk = \fread($handle, 8192);
            $byteCount += Tools::strlen($chunk);
            $giantChunk .= $chunk;
            if ($byteCount >= $chunkSize) {
                return $giantChunk;
            }
        }

        return $giantChunk;
    }

    /**
     * Delete old backups from local.
     *
     * @param int $backupSaved
     * @param string $localDir
     */
    private function localDeleteBackup($backupSaved, $localDir)
    {
        if (0 === $backupSaved) {
            return;
        }

        $files = \glob($localDir . '*.{zip,bz2}', \GLOB_BRACE);
        $backupFile = [];
        foreach ($files as $file) {
            $backupFile[] = \realpath($file);
        }

        if (false === empty($backupFile)) {
            $i = \count($backupFile);
            $j = 0;
            while ($i > $backupSaved) {
                Tools::deleteFile($backupFile[$j]);
                --$i;
                ++$j;
            }
        }
    }
}
