<?php
/**
 * @package axy\random
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\random;

use axy\random\helpers\Alg;
use axy\binary\Binary;

/**
 * Generation of random sequences
 */
class Random
{
    /**
     * Returns a binary random string
     *
     * @param int $length
     * @return string
     */
    public static function createString($length)
    {
        return Alg::random($length);
    }

    /**
     * Returns an array of bytes
     *
     * @param int $count
     * @return int[]
     */
    public static function createBytes($count)
    {
        return Binary::unpackBytes(self::createString($count));
    }
}
