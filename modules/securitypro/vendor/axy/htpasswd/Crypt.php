<?php
/**
 * @package axy\htpasswd
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\htpasswd;

use axy\crypt\APR1;
use axy\crypt\BCrypt;

/**
 * Hash/verify password
 */
class Crypt
{
    /**
     * Hash a password
     *
     * @param string $password
     * @param string $algorithm [optional]
     * @param array $options [optional]
     * @return string
     */
    public static function hash($password, $algorithm = PasswordFile::ALG_MD5, array $options = null)
    {
        if ($options === null) {
            $options = [];
        }
        switch ($algorithm) {
            case PasswordFile::ALG_MD5:
                return APR1::hash($password);
            case PasswordFile::ALG_BCRYPT:
                $cost = isset($options['cost']) ? $options['cost'] : null;
                return BCrypt::hash($password, $cost);
            case PasswordFile::ALG_SHA1:
                return self::sha1($password);
            case PasswordFile::ALG_CRYPT:
                return self::cryptHash($password);
            case PasswordFile::ALG_PLAIN:
                return $password;
        }
        return null;
    }

    /**
     * Checks if a password matches a hash
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verify($password, $hash)
    {
        if ($password === $hash) {
            return true;
        }
        if (APR1::verify($password, $hash)) {
            return true;
        }
        if ($hash === self::sha1($password)) {
            return true;
        }
        if (self::cryptVerify($password, $hash)) {
            return true;
        }
        if (substr($hash, 0, 2) === '$2') {
            if (BCrypt::verify($password, $hash)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Hash by SHA-1 (Apache version)
     *
     * @param string $password
     * @return string
     */
    public static function sha1($password)
    {
        return '{SHA}'.base64_encode(sha1($password, true));
    }

    /**
     * Verifies a hash of CRYPT algorithm
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function cryptVerify($password, $hash)
    {
        $salt = substr($hash, 0, 2);
        try {
            // PHP 7 throws exception for invalid salt
            $actual = crypt($password, $salt);
        } catch (\Exception $e) {
            return false;
        }
        return ($actual === $hash);
    }

    /**
     * Hash a password using CRYPT algorithm
     *
     * @param string $password
     * @return string
     */
    public static function cryptHash($password)
    {
        $salt = substr(base64_encode(chr(mt_rand(0, 255))), 0, 2);
        $salt = str_replace('+', '.', $salt);
        return crypt($password, $salt);
    }
}
