<?php
/**
 * 2007-2019 ETS-Soft ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2019 ETS-Soft ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

class ExportHistory extends ObjectModel
{
    public $file_name;
    public $content;
    public $date_export;
    public static $definition = array(
        'table' => 'ets_export_history',
        'primary' => 'id_export_history',
        'fields' => array(
            'file_name' =>    array('type' => self::TYPE_STRING),
            'content' =>            array('type' => self::TYPE_HTML),
            'date_export' =>         array('type' => self::TYPE_STRING),
        ),
    );
    public	function __construct($id_item = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($id_item, $id_lang, $id_shop);
	}
    public function delete()
    {
        if(file_exists(dirname(__FILE__).'/../cache/export/'.$this->file_name))
                @unlink(dirname(__FILE__).'/../cache/export/'.$this->file_name);
        return parent::delete();
    }
}