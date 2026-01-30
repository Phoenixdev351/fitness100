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
 * Class generating configuration tabs
 */
class ConfigureTab
{
    /**
     * @param $tab_list
     * @param string $module_name
     *
     * @return string
     */
    public static function generateTabs($tab_list, $module_name = '')
    {
        // $protocol = Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://';
        if (!Tools::strlen($module_name)) {
            $module_name = self::getModuleName();
        }

        // commented out by O.B. on 2015-03-10: cause cross domain redirection issue
        // $url = $protocol.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').
        // __PS_BASE_URI__.'modules/'.$module_name.'/';
        $url = __PS_BASE_URI__.'modules/'.$module_name.'/';

        $context = Context::getContext();
        $context->smarty->assign(array(
            'tab_list' => $tab_list,
            'img_dir' => $url.'views/img/shared/tab/',
            'module_url' => $url,
            'module_name' => $module_name,
            'ps16x' => version_compare(_PS_VERSION_, '1.6', '>='),
            'ps15x' => version_compare(_PS_VERSION_, '1.5', '>='),
            'has_line' => self::hasLine($tab_list),
            'line_number' => self::getLineNumber($tab_list)
        ));

        $html = $context->smarty->fetch(dirname(__FILE__).'/../../views/templates/admin/shared/tabs.tpl');

        return ($html);
    }


    /**
     * @param bool|false $addslash
     *
     * @return bool|string
     */
    public static function getModuleName($addslash = false)
    {
        $e = new Exception();
        $trace = $e->getTrace();
        $caller = $trace[2];

        if (!isset($caller['class'])) {
            return (false);
        }

        return (Tools::strtolower($caller['class']).($addslash ? '/' : ''));
    }


    /**
     * @param $tab_list
     *
     * @return bool
     */
    public static function hasLine($tab_list)
    {
        if (is_array($tab_list) && count($tab_list)) {
            foreach ($tab_list as $tab) {
                if (isset($tab['line']) && $tab['line']) {
                    return (true);
                }
            }
        }
        return (false);
    }


    /**
     * @param $tab_list
     *
     * @return int
     */
    public static function getLineNumber($tab_list)
    {
        $line_number = array();
        if (is_array($tab_list) && count($tab_list)) {
            foreach ($tab_list as $tab) {
                if (isset($tab['line']) && $tab['line']) {
                    $line_number[$tab['line']] = 1;
                }
            }
        }

        return (count($line_number));
    }
}
