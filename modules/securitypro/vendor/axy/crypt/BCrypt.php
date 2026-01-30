<?php
/**
 * @package axy\crypt
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\crypt;

use axy\random\Random;
use axy\binary\Binary;

/**
 * Blowfish
 */
class BCrypt
{
    const ALPHABET = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const BASE64_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    const SALT_START_LENGTH = 16;
    const SALT_FINAL_LENGTH = 22;
    const PREFIX = "2y";
    const DEFAULT_COST = 5;

    /**
     * Hash a password
     *
     * @param string $password
     * @param int $cost [optional]
     * @return string
     */
    public static function hash($password, $cost = null)
    {
        if ($cost === null) {
            $cost = self::DEFAULT_COST;
        }
        $prefix = sprintf('$%s$%02d$', self::PREFIX, $cost);
        $salt = self::createSalt();
        return crypt($password, $prefix.$salt);
    }

    /**
     * Verifies a password
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verify($password, $hash)
    {
        $salt = Binary::getSlice($hash, 0, self::SALT_FINAL_LENGTH + 7);
        return (crypt($password, $salt) === $hash);
    }

    /**
     * Convert Base64 string to BCrypt string
     *
     * @param string $base64
     * @return string
     */
    public static function convertAlphabets($base64)
    {
        $base64 = rtrim($base64, '=');
        return strtr($base64, self::BASE64_ALPHABET, self::ALPHABET);
    }

    /**
     * Creates a salt
     *
     * @return string
     */
    public static function createSalt()
    {
        $salt = Random::createString(self::SALT_START_LENGTH);
        $base64 = base64_encode($salt);
        $salt = self::convertAlphabets($base64);
        return Binary::getSlice($salt, 0, self::SALT_FINAL_LENGTH);
    }
}
