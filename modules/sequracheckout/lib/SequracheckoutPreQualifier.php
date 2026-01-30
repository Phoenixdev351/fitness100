<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class SequracheckoutPreQualifier extends SequraPreQualifier
{
    protected static $MODULE_NAME = 'sequracheckout';
    protected static $SERVICE_COMPATIBLE = true;

    public static function canDisplayInfo($price = null)
    {
        //For this plugin widgets are added on page footer (footer.tpl)
        return false;
    }

    public static function canDisplayWidgetInProductPage($id_product)
    {
        if(!self::availableForIP() || !$id_product){
            return false;
        }
        $sq_product_extra = new SequraProductExtra($id_product);
        if($sq_product_extra->getProductIsBanned()){
            return false;
        }
        if(
            !Configuration::get('SEQURA_FOR_SERVICES') &&
            $sq_product_extra->getProductIsVirtual()
        ){
            return false;
        }
        return true;
    }

    public static function isPriceWithinMethodRange($method, $price, $check_min = true)
    {
        $max = $method['max_amount']/100;
        $min = $method['min_amount']/100;
        $too_much = is_numeric($max) && $max > 0 && $price > $max;
        $too_low = (is_numeric($min) && $min > 0 && $price < $min) && $check_min;

        return !$too_much && !$too_low;
    }

    public static function isDateInRange($method)
    {
        $to_date = isset($method['ends_at'])?strtotime($method['ends_at']):0;
        $from_date = isset($method['starts_at'])?strtotime($method['starts_at']):0;
        return (!$from_date || time() >= $from_date) && 
               (!$to_date || time() <= $to_date);
    }
}
