<?php
/**
 * Working with binary strings
 *
 * @package axy\binary
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 * @license https://raw.github.com/axypro/binary/master/LICENSE MIT
 * @link https://github.com/axypro/binary repository
 * @link https://packagist.org/packages/axy/binary composer package
 * @uses PHP5.4+
 */

namespace axy\binary;

if (!is_file(__DIR__.'/vendor/autoload.php')) {
    throw new \LogicException('Please: composer install');
}

require_once(__DIR__.'/vendor/autoload.php');
