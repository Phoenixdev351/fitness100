<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Common-Services Co., Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Common-Services Co., Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 * ...........................................................................
 *
 * @author    Alexandre D.
 * @copyright Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F, Tak Shing House - Theatre
 *     Lane - 20 des Voeux Road Central, -Central, Hong Kong
 * @license   Commercial license
 * Support by mail  :  support@feed.biz
 */

/**
 * Class FeedbizPickUpCarrier
 */
class FeedbizPickUpCarrier
{
    const MONDIAL_RELAY_TABLE = 'mr_selected';
    const SO_COLISSIMO_TABLE = 'socolissimo_delivery_info';

    /* TODO 1 globale function to save in Db */
    /* TODO Carrier functions where to prepare the data to insert, instead of doing it in orders_import.php */

    public static function saveMondialRelayInformations($params)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            return (Db::getInstance()->insert(self::MONDIAL_RELAY_TABLE, $params));
        }

        return (Db::getInstance()->autoExecute(_DB_PREFIX_.self::MONDIAL_RELAY_TABLE, $params, 'INSERT'));
    }

    public static function saveColissimoInformations($params, $table = self::SO_COLISSIMO_TABLE)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            return (Db::getInstance()->insert($table, $params));
        }

        return (Db::getInstance()->autoExecute(_DB_PREFIX_.$table, $params, 'INSERT'));
    }
}
