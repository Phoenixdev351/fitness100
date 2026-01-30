<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace af;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Toolkit
{
    public static $sep = [
        'all' => ['dec' => '.', 'tho' => '', 'range' => ' - ', 'replacements' => []],
        'url' => ['dec' => '.', 'tho' => '', 'range' => '-', 'replacements' => []],
    ];

    public static function adjustSeparators($dec, $tho)
    {
        self::$sep['all']['dec'] = $dec;
        self::$sep['all']['tho'] = $tho != $dec ? $tho : ($dec == '.' ? ',' : '.');
        if (self::$sep['all']['tho']) {
            self::$sep['all']['replacements'][self::$sep['all']['tho']] = '';
        }
        if (self::$sep['all']['dec'] != '.') {
            self::$sep['all']['replacements'][self::$sep['all']['dec']] = '.';
        }
    }

    public static function defineRange($value, $implode = false, $range_sep = null, $math_ready = false, $url = false)
    {
        if ($range_sep) {
            $numbers = explode($range_sep, $value);
        } else {
            $numbers = self::extractNumbers($value, $math_ready, $url) ?: [0];
        }
        $range = [$numbers[0], isset($numbers[1]) ? $numbers[1] : $numbers[0]];

        return $implode && $range_sep ? implode($range_sep, $range) : $range;
    }

    public static function withinRanges($value, $ranges, $return_key = false)
    {
        foreach ($ranges as $key => $range) {
            if ($value >= $range[0] && $value <= $range[1]) {
                return $return_key ? $key : true;
            }
        }
    }

    public static function extractNumbers($str, $math_ready = false, $url = false)
    {
        $sep = $url ? self::$sep['url'] : self::$sep['all'];
        $thousand_check = $sep['tho'] ? '(?:' . preg_quote($sep['tho'], '/') . '?\d{3})*(?=\D|$)' : '';
        $pattern = '/((?<=\W|^)-)?\d+' . $thousand_check . '(' . preg_quote($sep['dec'], '/') . '\d+)?/';
        // $pattern matches positive and negative numbers, with/without decimals
        // negative numbers should be preceded by a non-word character or the beginning of the string
        preg_match_all($pattern, $str, $matches);
        if ($math_ready && $sep['replacements']) {
            foreach ($matches[0] as $key => $match) {
                $matches[0][$key] = str_replace(array_keys($sep['replacements']), $sep['replacements'], $match);
            }
        }

        return $matches[0];
    }

    public static function removeScientificNotation($number)
    {
        return rtrim(rtrim(number_format($number, 12, '.', ''), 0), '.');
    }

    public static function isBrightColor($color)
    {
        $hex_code = str_split(trim($color, '#'));
        if (count($hex_code) != 6) {
            $is_bright = false;
        } else {
            $r = hexdec($hex_code[0] . $hex_code[1]);
            $g = hexdec($hex_code[2] . $hex_code[3]);
            $b = hexdec($hex_code[4] . $hex_code[5]);
            $is_bright = $r + $g + $b > 700;
        }

        return $is_bright;
    }
}
