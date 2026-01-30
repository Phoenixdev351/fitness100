<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.Biz, Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.Biz, Ltd. is strictly forbidden.
 * In order to obtain a license, please contact us: contact@feed.biz
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.Biz, Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Feed.Biz, Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Feed.Biz, Ltd. a l'adresse: contact@feed.biz
 * ...........................................................................
 *
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F,
 *            Tak Shing House - Theatre Lane - 20 des Voeux Road Central, -Central, Hong Kong
 * @license   Commercial license
 * Support by mail  :  support@feed.biz
 */

require_once(dirname(__FILE__).'/env.php');

/**
 * Class ModuleEnvCheck
 */
class ModuleEnvCheck extends Module
{
    /**
     *
     */
    public function dispatch()
    {
        switch (Tools::getValue('action')) {
            case 'ajax':
                $this->ajaxCheck();
                break;
            case 'miv':
                $this->maxInputVarsCheck();
                break;
        }
    }

    /**
     *
     */
    public function ajaxCheck()
    {
        $callback = Tools::getValue('callback');

        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        header('Content-Type: application/json');

        die((string)$callback.'({"pass": true})');
    }

    /**
     *
     */
    public function maxInputVarsCheck()
    {
        $max_input_vars = ini_get('max_input_vars');
        $pass = true;

        $count = Tools::getValue('count');

        if ($max_input_vars && $count && $count >= $max_input_vars) {
            $pass = false;
        }

        $callback = Tools::getValue('callback');
        if ($callback == '?') {
            $callback = 'jsonp_'.time();
        }

        header('Content-Type: application/json');

        die((string)$callback.'({"pass": '.($pass ? 'true' : 'false').'})');
    }
}

$envCheck = new ModuleEnvCheck();
$envCheck->dispatch();
