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
 * Class ConfigureMessage
 */
class ConfigureMessage
{
    /**
     *
     */
    const _MESSAGE_ = '<div class="%s">%s</div>';
    /**
     *
     */
    const _DEBUG_ = '<div class="%s"><pre>%s</pre></div>';

    /**
     *
     */
    const _CLASS_ERROR_15_ = 'error';
    /**
     *
     */
    const _CLASS_WARNING_15_ = 'warn';
    /**
     *
     */
    const _CLASS_SUCCESS_15_ = 'conf';
    /**
     *
     */
    const _CLASS_INFO_15_ = 'info hint';

    /**
     *
     */
    const _CLASS_ERROR_16_ = 'alert alert-danger';
    /**
     *
     */
    const _CLASS_WARNING_16_ = 'alert alert-warning';
    /**
     *
     */
    const _CLASS_SUCCESS_16_ = 'alert alert-success';
    /**
     *
     */
    const _CLASS_INFO_16_ = 'alert alert-info';

    /**
     * @var array
     */
    protected static $msg_list        = array();
    /**
     * @var bool
     */
    protected static $has_error_msg   = false;
    /**
     * @var bool
     */
    protected static $has_warning_msg = false;
    /**
     * @var bool
     */
    protected static $has_success_msg = false;
    /**
     * @var bool
     */
    protected static $has_info_msg    = false;


    /**
     * @param string $msg
     */
    public static function error($msg = 'Error')
    {
        $error_msg = null;

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $error_msg = sprintf(self::_MESSAGE_, self::_CLASS_ERROR_15_, $msg);
        } else {
            $error_msg = '<div class="bootstrap">'.sprintf(self::_MESSAGE_, self::_CLASS_ERROR_16_, $msg).'</div>';
        }

        self::$has_error_msg = true;

        self::$msg_list[] = $error_msg;
    }


    /**
     * @param string $msg
     */
    public static function warning($msg = 'Warning')
    {
        $warning_msg = null;

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $warning_msg = sprintf(self::_MESSAGE_, self::_CLASS_WARNING_15_, $msg);
        } else {
            $warning_msg = '<div class="bootstrap">'.sprintf(self::_MESSAGE_, self::_CLASS_WARNING_16_, $msg).'</div>';
        }

        self::$has_warning_msg = true;

        self::$msg_list[] = $warning_msg;
    }


    /**
     * @param string $msg
     */
    public static function success($msg = 'Success')
    {
        $success_msg = null;

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $success_msg = sprintf(self::_MESSAGE_, self::_CLASS_SUCCESS_15_, $msg);
        } else {
            $success_msg = '<div class="bootstrap">'.sprintf(self::_MESSAGE_, self::_CLASS_SUCCESS_16_, $msg).'</div>';
        }

        self::$has_success_msg = true;

        self::$msg_list[] = $success_msg;
    }


    /**
     * @param string $msg
     */
    public static function info($msg = 'Success')
    {
        $info_msg = null;

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $info_msg = sprintf(self::_MESSAGE_, self::_CLASS_INFO_15_, $msg);
        } else {
            $info_msg = '<div class="bootstrap">'.sprintf(self::_MESSAGE_, self::_CLASS_INFO_16_, $msg).'</div>';
        }

        self::$has_info_msg = true;

        self::$msg_list[] = $info_msg;
    }


    /**
     * @param string $msg
     */
    public static function debug($msg = 'Debug')
    {
        $info_msg = null;

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $info_msg = sprintf(self::_DEBUG_, self::_CLASS_INFO_15_, $msg);
        } else {
            $info_msg = '<div class="bootstrap">'.sprintf(self::_DEBUG_, self::_CLASS_INFO_16_, $msg).'</div>';
        }

        self::$msg_list[] = $info_msg;
    }


    /**
     * @return string
     */
    public static function display()
    {
        $html = '';

        if (count(self::$msg_list)) {
            foreach (self::$msg_list as $msg) {
                $html .= $msg;
            }
        }

        return ($html);
    }


    /**
     * @return array
     */
    public static function getMessageList()
    {
        return (self::$msg_list);
    }


    /**
     * @return bool
     */
    public static function hasErrorMessage()
    {
        return (self::$has_error_msg);
    }


    /**
     * @return bool
     */
    public static function hasWarningMessage()
    {
        return (self::$has_warning_msg);
    }


    /**
     * @return bool
     */
    public static function hasSuccessMessage()
    {
        return (self::$has_success_msg);
    }


    /**
     * @return bool
     */
    public static function hasInfoMessage()
    {
        return (self::$has_info_msg);
    }
}
