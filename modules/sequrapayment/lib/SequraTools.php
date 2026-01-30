<?php
if (! defined('_PS_VERSION_')) {
    exit;
}

class SequraTools
{

    const ISO8601_PATTERN = '^((\d{4})-([0-1]\d)-([0-3]\d))+$|P(\d+Y)?(\d+M)?(\d+W)?(\d+D)?(T(\d+H)?(\d+M)?(\d+S)?)?$';
    static $centsPerWhole = 100;

    public static function getUname()
    {
        if (! function_exists('php_uname')) {
            return "uname unavailable";
        }
        $uname = php_uname();

        return isset($uname) ? utf8_encode($uname) : "uname returns null";
    }

    public static function removeProtectedKeys(&$data, $keys)
    {
        foreach ($keys as $key) {
            unset($data[ $key ]);
        }
    }

    public static function removeUnnecessaryKeys(&$data, $necessaryKeys)
    {
        foreach ($data as $key => $value) {
            if (!in_array($key, $necessaryKeys)) {
                unset($data[ $key ]);
            }
        }
    }

    public static function makeIntegerPrices(&$data, $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                $data[ $key ] = self::integerPrice($data[ $key ]);
            }
        }
    }

    public static function integerPrice($price)
    {
        return intval(round(self::$centsPerWhole * $price));
    }

    public static function notNull($value1)
    {
        return is_null($value1) ? '' : $value1;
    }

    public static function isInt($value1)
    {
        return (int) $value1 > 0;
    }

    public static function translateKeys(&$data, $keys, $object = null)
    {
        foreach ($keys as $api => $my) {
            if ($object) {
                unset($data[ $my ]);
                $data[ $api ] = '' . $object->{$my};
            } elseif ($api != $my && array_key_exists($my, $data)) {
                $data[ $api ] = is_null($data[ $my ]) ? '' : $data[ $my ];
                unset($data[ $my ]);
            }
        }
    }

    public static function truncateKeys(&$data, $keys)
    {
        foreach ($keys as $key => $length) {
            if ($length > 0 && isset($data[ $key ])) {
                $data[ $key ] = mb_substr($data[ $key ], 0, $length);
            } else {
                unset($data[ $key ]);
            }
        }
    }

    public static function dieObject($object)
    {
        return;
        /*echo '<xmp style="text-align: left;">';
        print_r($object);
        echo '</xmp><br />';
        die('END');*/
    }

    public static function sign($value)
    {
        $signature = hash_hmac('sha256', $value, Configuration::get('SEQURA_PASS'));

        return $signature ? $signature : sha1($value . Configuration::get('SEQURA_PASS'));
    }

    public static function getOrderStatus($orderstate, $name)
    {
        $ret = 'processing';
        if ('shipped' == strtolower($name)) {
            $ret = 'shipped';
        }
        if (( $orderstate instanceof OrderState ) && $orderstate->shipped) {
            $ret = 'shipped';
        }
        if (false !== strpos(strtolower($name), 'cancel')) { //Just a guess
            $ret = 'cancelled';
        }

        return $ret;
    }

    public static function getPaymentMethod($module)
    {
        switch ($module) {
            case 'servired':
            case 'redsys':
            case 'cc':
            case 'iupay':
            case 'cecatpv':
            case 'ceca':
            case 'bbva':
            case 'paytpv':
            case 'rurlavia':
            case 'univia':
            case 'banesto':
            case 'stripe':
            case 'stripejs':
            case 'banc_sabadell':
            case 'InnovaCommerceTPV':
                return 'CC';
            case 'paypal':
            case 'paypalwithfee':
                return 'PP';
            case 'bankwire':
            case 'cheque':
                return 'TR';
            case 'cod':
            case 'codfee':
            case 'cashondelivery':
            case 'megareembolso':
            case 'seurcashondelivery':
                return 'COD';
            default:
                if (strpos($module, 'sequra') === 0) {
                    return 'SQ';
                }

                return 'O/' . $module;
        }
    }

    //@todo stuff below should be in a separate class

    public static function totals($cart)
    {
        $total_without_tax = $total_with_tax = 0;
        foreach ($cart['items'] as $item) {
            $total_without_tax += isset($item['total_without_tax']) ? $item['total_without_tax'] : 0;
            $total_with_tax    += isset($item['total_with_tax']) ? $item['total_with_tax'] : 0;
        }

        return array( 'without_tax' => $total_without_tax, 'with_tax' => $total_with_tax );
    }

    public static function isModuleActive($name)
    {
        $module = Module::getInstanceByName($name);
        if ($module && Module::isInstalled($name)) {
            if (method_exists('Module', 'isEnabled')) {
                if (Module::isEnabled($name)) {
                    return true;
                }
            } else {
                if ($module->active) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function curl_get_contents($url)
    {
        $ch = curl_init();
        $timeout = 5;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        $data = curl_exec($ch);

        curl_close($ch);

        return $data;
    }

    public static function stripHTML($item)
    {
        array_walk(
            $item,
            function (&$value) {
                if (is_string($value)) {
                    $value = html_entity_decode(strip_tags($value));
                }
            }
        );
        return $item;
    }

    public static function getOrderIdByCartId($id_cart)
    {
        if (method_exists(Order::class, 'getIdByCartId')) { //PS >= 1.7
            return Order::getIdByCartId((int) ($id_cart));
        } else { //PS < 1.7
            return Order::getOrderByCartId((int) ($id_cart));
        }
    }

    public static function getOrderByCartId($id_cart)
    {
        if (method_exists(Order::class, 'getByCartId')) { //PS >= 1.7
            return Order::getByCartId((int) ($id_cart));
        } else { //PS < 1.7
            return new Order(
                Order::getOrderByCartId((int) ($id_cart))
            );
        }
    }

    public static function getSequraCore()
    {
        return Module::getInstanceByName(SEQURA_CORE);
    }
}
