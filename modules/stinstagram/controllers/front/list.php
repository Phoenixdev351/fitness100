<?php
/**
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
require_once _PS_MODULE_DIR_.'stinstagram/classes/StInstagramClass.php';

class StinstagramlistModuleFrontController extends ModuleFrontController
{
    private $_prefix_st = 'ST_INSTAGRAM_';

    public function __construct()
    {
        // $cache_time=Configuration::get($this->_prefix_st.'CACHE_IMG_DATE');
        // $time_len=Configuration::get($this->_prefix_st.'CACHE_IMG_DATE_LEN');
        // if($time_len<time()){
        //     Configuration::updateValue($this->_prefix_st.'CACHE_IMG_DATE_LEN',time()+$cache_time*60);
        //     (new StInstagramClass())->getApidata1000id();
        // }
        parent::__construct();
        $this->context = Context::getContext();
        $this->name = substr(strtolower(basename(__FILE__)), 0, -4);
    }

    public function initContent()
    {
        if(!Db::getInstance()->executeS('select * 
        from information_schema.TABLES 
        where TABLE_SCHEMA=\''._DB_NAME_.'\' and TABLE_NAME like \'%st_instagram_bind\'')){
            $redata["status"]=1;
            $redata["info"]="";
            die(Tools::jsonEncode($redata));
        }

        $cache_time=Configuration::get($this->_prefix_st.'CACHE_IMG_DATE');
        $time_len=Configuration::get($this->_prefix_st.'CACHE_IMG_DATE_LEN');

        $id_shop = $this->context->shop->id;

        $inst_acc=Db::getInstance()->getRow('select * from '._DB_PREFIX_.'st_instagram_bind where FIND_IN_SET('.$id_shop.',`shop_ids`)');
        if(!$inst_acc)
            return false;
        $access_token = $inst_acc['utoken'];
        $user_name = $inst_acc['username'];
        $user_id = $inst_acc['userid'];

        if($access_token==""){
                $redata["status"]=1;
                $redata["info"]="";
                die(Tools::jsonEncode($redata));
        }

        $limit=Tools::getValue("limit");
        $page=Tools::getValue("page",1);
        $id_ins=Tools::getValue("id_ins");
        $pai="";
        $bao="";
        $paiid="";
        $inst=new StInstagramClass($id_ins);
        if((int)$id_ins>0 && $inst->id!=null){
            $conData=StInstagramClass::getInstagram($id_ins,3);
            // if(!empty($conData) && isset($conData[0])){
            //     if($conData[0]['excluded_words']!=""){
            //      $pai=preg_replace('/,/','|',$conData[0]['excluded_words']);   
            //     }
            //     if($conData[0]['included_words']!=""){
            //      $bao=preg_replace('/,/','|',$conData[0]['included_words']);
            //     }
            //     $paiid=$conData[0]['img_excluded'];
            // }
        }else{
            $inst->pro_per_xxl = Tools::getValue('pro_per_xxl',7);
            $inst->grid = Tools::getValue('grid',0);
            $inst->pro_per_xl = Tools::getValue('pro_per_xl',6);
            $inst->pro_per_lg = Tools::getValue('pro_per_lg',5);
            $inst->pro_per_md = Tools::getValue('pro_per_md',5);
            $inst->pro_per_sm = Tools::getValue('pro_per_sm',4);
            $inst->pro_per_xs = Tools::getValue('pro_per_xs',3);
            $inst->click_action = Tools::getValue('click_action',0);
            $inst->time_format = Tools::getValue('time_format',0);
            $inst->hover_effect = Tools::getValue('hover_effect',0);
            $inst->show_username = Tools::getValue('show_username',0);
            $inst->show_timestamp = Tools::getValue('show_timestamp',0);
            $inst->show_caption = Tools::getValue('show_caption',0);
            $inst->force_square = Tools::getValue('force_square',0);
            $inst->lenght_of_caption = Tools::getValue('lenght_of_caption',0);
        }
        
       
        $tmpc=Db::getInstance()->getRow((new DbQuery())->select("count(id) as c")->from("st_instagram_img")->where('st_instagram_uid='.(int)$user_id));

        if($tmpc["c"]<=0){
            $inst->getApidata1000id($user_id,1);
        }


        $redata=["status"=>0,"info"=>[],"msg"=>"","np"=>-1];
        $csql=(new DbQuery())->select("count(id) as c")->from("st_instagram_img")->where('st_instagram_uid='.(int)$user_id);
        // if($pai!=""){
        //     $csql=$csql->where("`caption` not REGEXP '".$pai."'");
        // }
        // if($bao!=""){
        //     $csql=$csql->where("caption REGEXP '".$bao."'");
        // }
        // if($paiid!=""){
        //     $csql=$csql->where("instagram_id not in(".$paiid.")");
        // }
        // $sqlCount=(new DbQuery())->select("count(id) as c")->from("st_instagram_img");
        $c=Db::getInstance()->getRow($csql);
        $pageList=$this->pageCountList($c,$limit,$page);
        $redata["np"]=$pageList["np"];
        

        $sql=(new DbQuery())->select("*")->from("st_instagram_img")->where('st_instagram_uid='.(int)$user_id)->orderBy("id desc")->limit((int)$limit,$pageList["offset"]);
        // if($pai!=""){
        //     $sql=$sql->where("`caption` not REGEXP '".$pai."'");
        // }
        // if($bao!=""){
        //     $sql=$sql->where("caption REGEXP '".$bao."'");
        // }
        // if($paiid!=""){ 
        //     $sql=$sql->where("instagram_id not in(".$paiid.")");
        // }
        $tmpData=Db::getInstance()->executeS($sql);
        $_html='';
        
        if($tmpData && !empty($tmpData)){
            foreach($tmpData as $key=>&$val){
                if(is_null($val["media_type"])){
                    $tmp=$this->getImgInfo($val['instagram_id'],$access_token);
                    if(!$tmp){
                        unset($tmpData[$key]);
                    }elseif($tmp==2){
                        unset($tmpData[$key]);
                        $sql=(new DbQuery())->type("DELETE")->from("st_instagram_img")->where("id=".$val['id']);
                        Db::getInstance()->execute($sql);
                    }else{

                        $val["caption"]=isset($tmp["caption"])?$tmp["caption"]:"";
                        $val["media_type"]=$tmp["media_type"];
                        $val["media_url"]=isset($tmp["media_url"])?$tmp["media_url"]:"";
                        $val["permalink"]=$tmp["permalink"];
                        $val['username']=$tmp['username'];
                        $val["thumbnail_url"]=isset($tmp["thumbnail_url"])?$tmp["thumbnail_url"]:"";
                        $val["inst_time"]=$tmp["timestamp"];
                        $val["ctime"]=time();
                        $val["utime"]=time();
                        $sql="
                            UPDATE `"._DB_PREFIX_."st_instagram_img` 
                            SET `media_type`='".pSQL($val['media_type'])."' ,
                            `inst_time`='".pSQL($val['inst_time'])."' ,
                            `caption`='".pSQL($val['caption'])."' ,
                            `media_url`='".pSQL($val['media_url'])."' ,
                            `permalink`='".pSQL($val['permalink'])."' ,
                            `username`='".pSQL($val['username'])."' ,
                            `thumbnail_url`='".pSQL($val['thumbnail_url'])."' ,
                            `utime`=".time()." 
                            WHERE `id`='".$val['id']."'; 
                         ";
                        Db::getInstance()->execute($sql);
                        if(isset($tmp["caption"])){
                            if($pai!="" && preg_match('/'.$pai.'/',$tmp["caption"])){
                                unset($tmpData[$key]);
                                continue;
                            }
                        }
                    }
                }
                $val['inst_time']=date_format(date_create($val['inst_time']),'Y-m-d');
                $val['id']=$val['instagram_id'];
                $val['ago_time']=$this->module->format_date($val['inst_time']);
            }

            if(!empty($tmpData)){
                $footer_op=Tools::getValue('footer_op',0);
                $ins_placeholder=\Context::getContext()->link->getMediaLink('/modules/stinstagram/views/img/resolution_2.png');
                foreach($tmpData as $key=>$item){
                    if($footer_op==1){
                        $_html.=$this->context->smarty->fetch('module:stinstagram/views/templates/hook/list-footer.tpl',['in'=>(array)$inst,'ajax_list'=>1,'key'=>$key,'item'=>$item,'ins_placeholder' => $ins_placeholder,]);
                    }else{
                        $_html.=$this->context->smarty->fetch('module:stinstagram/views/templates/hook/list.tpl',['in'=>(array)$inst,'key'=>$key,'ajax_list'=>1,'item'=>$item,'ins_placeholder' => $ins_placeholder,'grid_list'=>1]);
                    }
                }

                $redata["status"]=1;
                $redata["info"]=$tmpData;
                $redata["html"]=$_html;
            }
        }
        die(Tools::jsonEncode($redata));
    }

    private function pageCountList($cs,$l,$p){
        $c=$cs["c"];
        if($l>=$c){
            return["offset"=>0,"np"=>-1];
        }
        $pz=ceil($c/$l);
        if($p>=$pz){
            return array(
                "offset"=>($p-1)*$l,
                "np"=>"-1"
            );
        }
        return array(
            "offset"=>($p-1)*$l,
            "np"=>$p+1
        );
    }

    private function getImgInfo($id,$token){
        $files='caption,id,media_type,media_url,permalink,thumbnail_url,timestamp,username';
        $url_img_info='https://graph.instagram.com/'.$id.'?fields='.$files.'&access_token='.$token;
        $data=Tools::file_get_contents($url_img_info,false,null,10,false);
        if($data && !empty($data)){
            $data=json_decode($data,true);
            if(isset($data["error"])){
                return 2;
            }
            return $data;
        }
        return false;
    }

}
