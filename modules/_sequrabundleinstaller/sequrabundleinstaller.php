<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
use PrestaShopBundle\Command\ModuleCommand;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SequraBundleInstaller extends Module
{
    const TARGET = '';

    protected $config_form = false;

    protected $container = false;
    /**
     * ModuleManager
     *
     * @var PrestaShop\PrestaShop\Core\Addon\Module\ModuleManager
     */
    protected $moduleManager = false;

    public function __construct()
    {
        $this->name = 'sequrabundleinstaller';
        $this->tab = 'administration';
        $this->version = '1.0.2';
        $this->author = 'SeQura Tech';
        $this->need_instance = 1;
        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Sequra Installer');
        $this->description = $this->l('Makes SeQura installation easy');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->moduleManager = $this->getContainer()->get('prestashop.module.manager');

        $this->moduleNames = [ //Modules tu install
            'sequrapayment',
            'sequracheckout'
        ];
        //Add any other sequramodule if present
        $other_module_names = ['sequrapartpayment','sequrainvoice','sequracampaign','sequracard']; //Other modules to clean.
        foreach ($other_module_names as $moduleName) {
            if ($this->moduleManager->isInstalled($moduleName)) {
                $this->executeGenericModuleAction('removeModuleFromDisk', $moduleName);
            }
        }
        $this->filesystem = new Filesystem();
        $this->modulePath = _PS_ROOT_DIR_. DIRECTORY_SEPARATOR . basename(_PS_MODULE_DIR_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('SEQURAINSTALLER_LIVE_MODE', false);
        /**
         * @var \PrestaShop\PrestaShop\Core\Addon\Module\ModuleManager
         */

        $this->removeModuleFolders();

        return $this->installModules();
    }

    private function installModules()
    {
        foreach ($this->moduleNames as $moduleName) {
            $this->downLoadModule($moduleName);
            $this->extractDownloadedModule($moduleName);
            if ($this->executeGenericModuleAction('install', $moduleName)) {
                continue;
            };
            die('error installing '.$moduleName);
            return false;
        }
        return true;
    }

    private function getZipFileName($moduleName)
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'prestashop-' . $moduleName . '.zip';
    }

    private function downloadModule($moduleName)
    {
        $this->filesystem->copy(
            'https://engineering.sequra.es/plugins/Prestashop/prestashop-' . $moduleName . self::TARGET . '.zip',
            $this->getZipFileName($moduleName)
        );
    }

    private function extractDownloadedModule($moduleName)
    {
        /* Open the Zip file */
        $zip = new ZipArchive;
        if ($zip->open($this->getZipFileName($moduleName)) != "true") {
            echo "Error :- Unable to open the Zip File";
        }
        /* Extract Zip File */
        $zip->extractTo($this->modulePath);
        $zip->close();
    }

    private function removeModuleFolders()
    {
        foreach ($this->moduleNames as $moduleName) {
            $this->executeGenericModuleAction('removeModuleFromDisk', $moduleName);
        }
    }

    protected function getContainer()
    {
        if (!$this->container) {
            global $kernel;
            $this->container = $kernel->getContainer();
        }
        return  $this->container;
    }

    protected function executeGenericModuleAction($action, $moduleName)
    {
        if ($this->moduleManager->{$action}($moduleName)) {
            return true;
        }
        $error = $this->moduleManager->getError($moduleName);
        return false;
    }

    public static function isModuleActive($name)
    {
        $module = Module::getInstanceByName($name);
        if ($module && Module::isInstalled($name)) {
            if (method_exists('Module', 'isEnabled')) {
                if (Module::isEnabled($name)) {
                    return true;
                }
            } else {
                if ($module->active) {
                    return true;
                }
            }
        }
        return false;
    }
}
