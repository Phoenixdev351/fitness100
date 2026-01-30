<?php
/**
 * @package axy\htpasswd
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\htpasswd\errors;

use axy\errors\Runtime;

/**
 * The htpasswd file has invalid format
 */
final class InvalidFileFormat extends Runtime implements Error
{
    /**
     * @var string
     */
    protected $defaultMessage = 'Htpasswd file {{ filename }} has invalid format';

    /**
     * The constructor
     *
     * @param string $filename [optional]
     */
    public function __construct($filename = null)
    {
        $this->filename = $filename;
        $message = [
            'filename' => $filename,
        ];
        parent::__construct($message);
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->filename;
    }

    /**
     * @var string
     */
    private $filename;
}
