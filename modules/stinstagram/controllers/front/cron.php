<?php
class StinstagramCronModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{

		$module = Module::getInstanceByName('stinstagram');
        if (Tools::getValue('token') != $module->getToken()) {
            die('Access denied');
        }
        $inst_acc=Db::getInstance()->executeS('select * from '._DB_PREFIX_.'st_instagram_bind');
        if($inst_acc && count($inst_acc)>0){
            foreach($inst_acc as $val){
                if(time()-$val['tk_time']>4320000){
                    $new_token=$module->refreshAccessToken($val['utoken']);
                    $updata=[];
                    if(isset($new_token['access_token'])){
                        $updata['utoken']=$new_token['access_token'];
                        $updata['tk_time']=time();
                        Db::getInstance()->update('st_instagram_bind',$updata,'id_st_instagram_bind='.$val['id_st_instagram_bind']);
                    }
                }
                (new StInstagramClass())->getApidata1000id($val['id_st_instagram_bind'],1);
            }
        }
        //clear smarty cache
        $module->clearCache();
        
        die('Okay');
	}
}
