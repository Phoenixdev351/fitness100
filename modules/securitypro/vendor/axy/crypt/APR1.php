<?php
/**
 * @package axy\crypt
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crypt;

/**
 * Apache APR1-MD5 algorithm
 */
class APR1
{
    const ALPHABET = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const BASE64_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    const SALT_LENGTH = 8;
    const PREFIX = '$apr1$';
    const COUNT_STEPS = 1000;
    const HASH_COUNT_STEPS = 5;

    /**
     * Hash a string
     *
     * @param string $string
     * @return string
     */
    public static function hash($string)
    {
        $salt = self::createSalt();
        return self::PREFIX.$salt.'$'.self::createSubHash($string, $salt);
    }

    /**
     * Verifies a string hash
     *
     * @param string $string
     * @param string $hash
     * @return string
     */
    public static function verify($string, $hash)
    {
        $pattern = '~^'.preg_quote(self::PREFIX).'(?<salt>[A-Za-z0-9\./]{8})\$(?<sub>[A-Za-z0-9\./]+)$~is';
        if (!preg_match($pattern, $hash, $matches)) {
            return false;
        }
        return ($matches['sub'] === self::createSubHash($string, $matches['salt']));
    }

    /**
     * Creates a random salt
     *
     * @return string
     */
    public static function createSalt()
    {
        $alphabet = self::ALPHABET;
        $length = strlen($alphabet);
        $salt = [];
        for ($i = 0; $i < self::SALT_LENGTH; $i++) {
            $index = mt_rand(0, $length - 1);
            $salt[] = substr($alphabet, $index, 1);
        }
        return implode('', $salt);
    }

    /**
     * Creates a hash for a string and a salt
     *
     * @param string $string
     * @param string $salt
     * @return string
     */
    public static function createSubHash($string, $salt)
    {
        $context = self::createContext($string, $salt);
        $null = chr(0);
        $hash = '';
        for ($i = 0; $i < self::HASH_COUNT_STEPS; $i++) {
            $k = $i + 6;
            $j = $i + 12;
            if ($j === 16) {
                $j = 5;
            }
            $hash = $context[$i].$context[$k].$context[$j].$hash;
        }
        $hash = $null.$null.$context[11].$hash;
        $hash = base64_encode($hash);
        $hash = substr($hash, 2);
        $hash = strrev($hash);
        $hash = strtr($hash, self::BASE64_ALPHABET, self::ALPHABET);
        return $hash;
    }

    /**
     * Verifies that a string matches a subHash + salt
     *
     * @param string $string
     * @param string $subHash
     * @param string $salt
     * @return bool
     */
    public static function verifySubHash($string, $subHash, $salt)
    {
        return ($subHash === self::createSubHash($string, $salt));
    }

    /**
     * @param string $string
     * @param string $salt
     * @return string
     */
    private static function createContext($string, $salt)
    {
        $len = strlen($string);
        $null = chr(0);
        $context = $string.self::PREFIX.$salt;
        $binary = pack('H32', md5($string.$salt.$string));
        for ($i = $len; $i > 0; $i -= 16) {
            $context .= substr($binary, 0, min(16, $i));
        }
        for ($i = $len; $i > 0; $i >>= 1) {
            $context .= ($i & 1) ? $null : $string[0];
        }
        $context = pack('H32', md5($context));
        return self::iterateContext($string, $salt, $context);
    }

    /**
     * @param string $string
     * @param string $salt
     * @param string $context
     * @return string
     */
    private static function iterateContext($string, $salt, $context)
    {
        for ($i = 0; $i < self::COUNT_STEPS; $i++) {
            $value = [];
            if ($i % 2) {
                $value[] = $string;
            } else {
                $value[] = $context;
            }
            if ($i % 3) {
                $value[] = $salt;
            }
            if ($i % 7) {
                $value[] = $string;
            }
            if ($i % 2) {
                $value[] = $context;
            } else {
                $value[] = $string;
            }
            $context = pack('H32', md5(implode('', $value)));
        }
        return $context;
    }
}
