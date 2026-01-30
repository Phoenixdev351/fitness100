<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL SMC
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe SMC
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL SMC est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 * ...........................................................................
 *
 * @author    Debusschere A.
 * @copyright Copyright (c) 2011-2022 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 */

/**
 * Class generating module tabs
 */
class CommonServicesTab extends Tab
{
    /**
     *
     */
    const ADD = 'a';
    /**
     *
     */
    const REMOVE = 'd';
    /**
     *
     */
    const UPDATE = 'u';

    /**
     * @param $action
     * @param $class
     * @param $name
     * @param $parent
     * @param bool|false $debug
     *
     * @return bool
     */
    public static function setup($action, $class, $name, $parent, $debug = false)
    {
        switch ($action) {
            case self::ADD:
                if (!Tab::getIdFromClassName($class)) {
                    if (!self::installModuleTab($class, $name, $parent)) {
                        if ($debug) {
                            printf('%s(#%d): Unable to install: %s', basename(__FILE__), __LINE__, $class);
                        }

                        return false;
                    }
                }
                break;

            case self::UPDATE:
                if (self::setup(self::REMOVE, $class, $name, $parent, $debug)) {
                    return (self::setup(self::ADD, $class, $name, $parent, $debug));
                }
                break;

            case self::REMOVE:
                if (Tab::getIdFromClassName($class)) {
                    if (!self::uninstallModuleTab($class)) {
                        if ($debug) {
                            printf('%s(#%d): Unable to uninstall: %s', basename(__FILE__), __LINE__, $class);
                        }

                        return false;
                    }
                }
                break;
        }

        return true;
    }

    /**
     * @param $tabClass
     * @param $tabName
     * @param $tabParent
     *
     * @return bool|int
     */
    private static function installModuleTab($tabClass, $tabName, $tabParent)
    {
        $module = self::getModuleName();
        $tabNameLang = array();

        foreach (Language::getLanguages() as $language) {
            $tabNameLang[$language['id_lang']] = $tabName;
        }

        $tab = new Tab();
        $tab->name = $tabNameLang;
        $tab->class_name = $tabClass;
        $tab->module = $module;
        $tab->id_parent = Tab::getIdFromClassName($tabParent);

        // For Prestashop 1.2
        if (version_compare(_PS_VERSION_, '1.3', '<')) {
            $pass = $tab->add();
        } else {
            $pass = $tab->save();
        }

        return ($pass);
    }

    /**
     * @return bool|string
     */
    public static function getModuleName()
    {
        $trace = debug_backtrace();
        $caller = $trace[4];

        if (!isset($caller['class'])) {
            return (false);
        } else {
            return (Tools::strtolower($caller['class']));
        }
    }

    /**
     * @param $tabClass
     *
     * @return bool
     */
    private static function uninstallModuleTab($tabClass)
    {
        $pass = true;
        $idTab = Tab::getIdFromClassName($tabClass);

        // Big Bug PS 1.4 - cached entry is not removed on delete() ...
        if (version_compare(_PS_VERSION_, '1.5.5', '<')) {
            if (isset(Tab::$_getIdFromClassName[Tools::strtolower($tabClass)])) {
                unset(Tab::$_getIdFromClassName[Tools::strtolower($tabClass)]);
            }
            if (isset(Tab::$_getIdFromClassName[($tabClass)])) {
                unset(Tab::$_getIdFromClassName[($tabClass)]);
            }
        }
        if ($idTab != 0) {
            $tab = new Tab($idTab);
            $pass = $tab->delete();
        }

        return ($pass);
    }
}
