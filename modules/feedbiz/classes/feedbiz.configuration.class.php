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

class FeedbizConfiguration
{
    /**
     * Stores huges configuration sets into marketplace_configuration table instead of ps_configuration table,
     * which is limited
     *
     * @param $configuration_key
     * @param $data
     * @return bool
     */
    public static function updateValue($configuration_key, $data)
    {
        $marketplace_configuration_key = Tools::strtolower($configuration_key);

        $context = Context::getContext();

        if (FeedbizTools::tableExists(_DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_CONFIGURATION)) {
            // Ok the table is present, new method :)

            return Db::getInstance()->execute(
                'REPLACE INTO `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_CONFIGURATION.'` (`configuration`, `id_shop`, `value`)
                VALUES ("'.pSQL($marketplace_configuration_key).'",
                    '.(int)$context->shop->id.', "'.pSQL(serialize(self::filter($data))).'"); '
            );
        } else {
            return (false);
        }
    }

    /**
     * Retrieves configuration from ps_marketplace
     * @param $configuration_key
     * @return bool|mixed
     */
    public static function get($configuration_key)
    {
        $marketplace_configuration_key = Tools::strtolower($configuration_key);

        $context = Context::getContext();

        if (FeedbizTools::tableExists(_DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_CONFIGURATION)) {
            $result = Db::getInstance()->getRow(
                'SELECT `value` FROM `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_CONFIGURATION.'`
                    WHERE `configuration`="'.pSQL($marketplace_configuration_key).'"
                    AND `id_shop`='.(int)$context->shop->id
            );
            if (is_array($result) && FeedbizTools::isSerialized($result['value'])) {
                return(unserialize($result['value']));
            }else{
                $result = Db::getInstance()->getRow(
                    'SELECT `value` FROM `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_CONFIGURATION.'`
                        WHERE `configuration`="'.pSQL($marketplace_configuration_key).'"'
                );
                if (is_array($result) && FeedbizTools::isSerialized($result['value'])) {
                    return(unserialize($result['value']));
                }
            }
        }
        return(null);
    }

    /**
     * As per her sister: Configuration::deleteByName
     * @param $configuration_key
     * @return bool
     */
    public static function deleteKey($configuration_key)
    {
        $marketplace_configuration_key = Tools::strtolower($configuration_key);
        $context = Context::getContext();

        if (FeedbizTools::tableExists(_DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_CONFIGURATION)) {
            return Db::getInstance()->execute(
                'DELETE FROM `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_CONFIGURATION.'`
                    WHERE `configuration`="'.pSQL($marketplace_configuration_key).'"
                    AND `id_shop`='.(int)$context->shop->id
            );
        }
        return (false);
    }


    public static function filter($obj)
    {
        return ($obj);//TODO: filter SimpleXMLElements
    }

    public static function createTable()
    {
        $pass = true;

        if (!FeedbizTools::tableExists(_DB_PREFIX_.'feedbiz_configuration')) {
            $sql = '
                    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'feedbiz_configuration` (
                    `configuration` VARCHAR( 32 ) NULL DEFAULT NULL ,
                    `id_shop` INT( 11 ) NULL DEFAULT 1 ,
                    `value` LONGTEXT NOT NULL,
                    UNIQUE KEY `configuration` (`configuration`)
                    ) ;';

            if (!Db::getInstance()->execute($sql)) {
                $pass = false;
            }
        }
        return ($pass);
    }
}
