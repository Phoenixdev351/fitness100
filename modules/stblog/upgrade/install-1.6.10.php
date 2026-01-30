<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_6_10($object)
{
    $v=array('filterStBlogContent','filterStBlogContent','This hook is called just before fetching content page',1);
    $id_hook = Hook::getIdByName($v[0]);
    if (!$id_hook)
    {
        $new_hook = new Hook();
        $new_hook->name = pSQL($v[0]);
        $new_hook->title = pSQL($v[1]);
        $new_hook->description = pSQL($v[2]);
        $new_hook->position = pSQL($v[3]);
        //$new_hook->live_edit  = 0;
        $new_hook->add();
        $id_hook = $new_hook->id;
        if (!$id_hook){
            $result = false;
        }else{
            $result = true;
        }
    }
    else
    {
        $result=Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'hook` set `title`="'.$v[1].'", `description`="'.$v[2].'", `position`="'.$v[3].'" where `id_hook`='.$id_hook);
    }
    return $result;
}
