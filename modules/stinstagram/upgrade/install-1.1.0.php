<?php

if (!defined('_PS_VERSION_'))
	exit;

    function upgrade_module_1_1_0($object)
    {
        $result = Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'st_instagram_bind` (
            `id_st_instagram_bind` INT(10) NOT NULL AUTO_INCREMENT,
            `utoken` VARCHAR(255) DEFAULT NULL,
            `userid` VARCHAR(60) DEFAULT NULL,
            `username` VARCHAR(255) DEFAULT NULL,
            `shop_ids` VARCHAR(255) DEFAULT NULL,
            `tk_time` INT(11) DEFAULT NULL,
            `tk_state` TINYINT(1) DEFAULT NULL,
            PRIMARY KEY (`id_st_instagram_bind`)
          ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');
          $result &= Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'st_instagram_img` ADD COLUMN `st_instagram_uid` VARCHAR(30) NULL AFTER `utime`');
          $result &= Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'st_instagram_img` CHANGE `id_shop` `id_shop` INT(11) DEFAULT \'0\' NULL;');
          
        if($result){
            //celect yijinyou de tonken 
            if($slist=Shop::getShops()){
                if(count($slist)>0){
                    $inster=[];
                    foreach($slist as $val){
                        $token=Configuration::get('ST_INSTAGRAM_ACCESS_TOKEN',null,null,$val['id_shop']);
                        $username=Configuration::get('ST_INSTAGRAM_USER_NAME',null,null,$val['id_shop']);
                        $userid=Configuration::get('ST_INSTAGRAM_USER_ID',null,null,$val['id_shop']);
                        $tk_time=Configuration::get('ST_INSTAGRAM_AC_LAST_TIME',null,null,$val['id_shop']);
                        

                        if($token && $token!='' && $username && $username!='' && $userid && $userid!=''){
                            if(!isset($inster[$userid])){
                                $inster[$userid]=['utoken'=>$token,'username'=>$username,'userid'=>$userid,'tk_time'=>$tk_time,'shop_ids'=>[$val['id_shop']]];
                            }else{
                                $inster[$userid]['shop_ids'][]=$val['id_shop'];
                                $inster[$userid]['tk_time'][]=$tk_time;
                                
                            }
                        }
                    }

                    if(!empty($inster) && count($inster)>0){
                        foreach($inster as $ival){
                            $idshop='';
                            if(!empty($ival['shop_ids'])){
                                $idshop=implode(',',$ival['shop_ids']);
                            }
                            Db::getInstance()->insert('st_instagram_bind',['utoken'=>$ival['utoken'],'username'=>$ival['username'],'userid'=>$ival['userid'],'shop_ids'=>$idshop]);
                            // Db::getInstance()->insert('st_instagram_bind',['utoken'=>$token,'username'=>$username,'userid'=>$userid,'shop_ids'=>$val['id_shop']]);
                            //zhe li bu zuo shuju tongbu pa wangluo lianjie wenti  daozi shengji shibai 
                            //xiugai  xianyou shuju biao 
                            if($idshop!=''){
                                Db::getInstance()->update('st_instagram_img',['st_instagram_uid'=>$userid],'id_shop in('.$idshop.')');
                            }
                        }
                    }
                }
            }
            

        }
        return $result;
    }