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

class FeedBizLog
{
    /**
     *
     */
    const PATH_LOG_DIR = '../logs/';
    /**
     *
     */
    const FILE_LOG_STOCK_MOVEMENT = 'log_stockmovement.txt';
    /**
     *
     */
    const FILE_LOG_ORDER_IMPORT = 'log_orderimport.txt';
    /**
     *
     */
    const FILE_LOG_STOCK_MOVEMENT_FBA = 'log_stockmovement_fba.txt';
    /**
     * @param $log_file
     */
    public static function clear($log_file)
    {
        $content = "[".date('Y-m-d H:i:s')."]Start Log\n";
        file_put_contents(self::PATH_LOG_DIR.$log_file, $content);
    }

    /**
     * @param $log_file
     * @param $datas
     */
    public static function log($log_file, $datas)
    {
        $content = "[".date('Y-m-d H:i:s')."]".Tools::jsonEncode($datas)."\n";
        file_put_contents(self::PATH_LOG_DIR.$log_file, $content, FILE_APPEND);
    }
}
