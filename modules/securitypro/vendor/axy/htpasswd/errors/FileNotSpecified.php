<?php
/**
 * @package axy\htpasswd
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\htpasswd\errors;

use axy\errors\Logic;

/**
 * The htpasswd file in not specified
 */
final class FileNotSpecified extends Logic implements Error
{
    /**
     * @var string
     */
    protected $defaultMessage = 'Htpasswd file is not specified';

    /**
     * The constructor
     */
    public function __construct()
    {
        parent::__construct([]);
    }
}
