<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }
class Ets_superspeed_upload_image extends ObjectModel
{
    public $image_name;
    public $old_size;
    public $new_size;
    public $image_name_new;
    public $date_add;
    public static $definition = array(
        'table' => 'ets_superspeed_upload_image',
        'primary' => 'id_ets_superspeed_upload_image',
        'multilang' => false,
        'fields' => array(
            'image_name' => array('type' => self::TYPE_STRING),
            'old_size' => array('type' => self::TYPE_FLOAT),
            'new_size' => array('type' => self::TYPE_FLOAT),
            'image_name_new' => array('type' => self::TYPE_STRING),
            'date_add' => array('type' => self::TYPE_DATE),
        )
    );
    public function delete()
    {
        if(parent::delete())
        {
            if (@file_exists(_ETS_SPEED_CACHE_DIR_IMAGES . $this->image_name_new)) {
                @unlink(_ETS_SPEED_CACHE_DIR_IMAGES . $this->image_name_new);
            }
            return true;
        }
        return false;
    }
    public function download()
    {
        if(file_exists(_ETS_SPEED_CACHE_DIR_IMAGES . $this->image_name_new))
        {
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . $this->image_name . "\"");
            readfile(_ETS_SPEED_CACHE_DIR_IMAGES . $this->image_name_new);
            exit;
        }
    }
}