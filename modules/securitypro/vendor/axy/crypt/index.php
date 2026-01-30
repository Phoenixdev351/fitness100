<?php
/**
 * Some crypt algorithms
 *
 * @package axy\crypt
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 * @license https://raw.github.com/axypro/crypt/master/LICENSE MIT
 * @link https://github.com/axypro/crypt repository
 * @link https://packagist.org/packages/axy/crypt composer package
 * @uses PHP5.4+
 */

namespace axy\crypt;

if (!is_file(__DIR__.'/vendor/autoload.php')) {
    throw new \LogicException('Please: composer install');
}

require_once(__DIR__.'/vendor/autoload.php');
