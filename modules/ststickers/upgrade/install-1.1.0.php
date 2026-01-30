<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_1_0($object)
{
    $result = true;
    
    if(!Configuration::get('STSN_HIDE_DISCOUNT'))
    {
        $result &= Db::getInstance()->insert('st_sticker', array(
                            'type' => 3,
                            'text_color' => '#ffffff',
                            'bg_color' => '#E54D26',
                            'sticker_position' => 13,
                        ));

        if($id = Db::getInstance()->Insert_ID())
        {
            $langs = Language::getLanguages(false);
            foreach ($langs as $l) {
                Db::getInstance()->insert('st_sticker_lang', array(
                            'id_st_sticker' => $id,
                            'id_lang' => $l['id_lang'],
                        ));
            }
        }
    }
    
    return $result;
}