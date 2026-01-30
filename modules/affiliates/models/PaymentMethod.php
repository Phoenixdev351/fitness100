<?php
/**
 * Affiliates
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @author    FMM Modules
 * @copyright © Copyright 2021 - All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @category  FMM Modules
 * @package   affiliates
 */

class PaymentMethod extends ObjectModel
{
    public $payment_name;

    public $payment_description;

    public $date_add;

    public $date_upd;

    public static $definition = array(
        'table' => 'payment_method',
        'primary' => 'id_payment_method',
        'multilang' => true,
        'fields' => array(
            'date_add' => array('type' => self::TYPE_DATE),
            'date_upd' => array('type' => self::TYPE_DATE),
            'payment_name' => array('type' => self::TYPE_STRING, 'validate' => 'isName'),
            'payment_description' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'),
        ),
    );

    public static function getPaymenMethods($id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        $sql = new DbQuery();
        $sql->select('pm.*, pml.payment_description');
        $sql->from(self::$definition['table'], 'pm');
        $sql->leftJoin(
            self::$definition['table'].'_lang',
            'pml',
            'pm.'.self::$definition['primary'] .'= pml.'.self::$definition['primary'].' AND pml.`id_lang` = '.(int)$id_lang
        );

        return Db::getInstance()->executeS($sql);
    }

    public static function isPmExists($id_payment_method, $return_data = false)
    {
        if (!$id_payment_method) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where(self::$definition['primary'] .'='.(int)$id_payment_method);

        if ($return_data) {
            return Db::getInstance()->getRow($sql);
        }
        return (bool)Db::getInstance()->getRow($sql);
    }
}
