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
class Ets_superspeed_browse_image extends ObjectModel
{
    public $image_name;
    public $image_dir;
    public $image_id;
    public $old_size;
    public $new_size;
    public $date_add;
    public static $definition = array(
        'table' => 'ets_superspeed_browse_image',
        'primary' => 'id_ets_superspeed_browse_image',
        'multilang' => false,
        'fields' => array(
            'image_name' => array('type' => self::TYPE_STRING),
            'image_dir' => array('type' => self::TYPE_STRING),
            'old_size' => array('type' => self::TYPE_FLOAT),
            'new_size' => array('type' => self::TYPE_FLOAT),
            'image_id' => array('type' => self::TYPE_STRING),
            'date_add' => array('type' => self::TYPE_DATE),
        )
    );
    public function download(){
        if(file_exists($this->image_dir))
        {
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . $this->image_name. "\"");
            readfile($this->image_dir);
            exit;
        }
    }
    public function restore(){
        if ($this->image_dir && file_exists($this->image_dir)) {
            $path = str_replace($this->image_name, '', $this->image_dir);
            Ets_superspeed_compressor_image::createBlogImage($path, $this->image_name, true);
        }
        return $this->delete();
    }
}