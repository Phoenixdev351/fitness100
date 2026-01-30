<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_4_5_2($object)
{
    $result = true;

    $product_tabs_style = (int)Configuration::get('STSN_PRODUCT_TABS_STYLE');

    $product_acc_style = 0;

    if($product_tabs_style==5 || $product_tabs_style==6 || $product_tabs_style==1)
    	$product_acc_style = 1;

    if($product_tabs_style==5)
    	$product_tabs_style = 0;
    elseif($product_tabs_style==4)
    	$product_tabs_style = 1;
    elseif($product_tabs_style==6)
    	$product_tabs_style = 2;

    $result &= Configuration::updateValue('STSN_PRODUCT_ACC_STYLE', $product_acc_style);
    $result &= Configuration::updateValue('STSN_PRODUCT_TABS_STYLE', $product_tabs_style);
    
	return $result;
}
