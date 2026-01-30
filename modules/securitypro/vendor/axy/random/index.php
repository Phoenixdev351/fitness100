<?php
/**
 * Generates pseudo-random
 *
 * @package axy\random
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 * @license https://raw.github.com/axypro/random/master/LICENSE MIT
 * @link https://github.com/axypro/random repository
 * @link https://packagist.org/packages/axy/random composer package
 * @uses PHP5.4+
 */

namespace axy\random;

if (!is_file(__DIR__.'/vendor/autoload.php')) {
    throw new \LogicException('Please: composer install');
}

require_once(__DIR__.'/vendor/autoload.php');
