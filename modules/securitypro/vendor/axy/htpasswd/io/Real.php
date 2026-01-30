<?php
/**
 * @package axy\htpasswd
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\htpasswd\io;

use axy\htpasswd\errors\FileNotSpecified;

/**
 * Real file
 */
class Real implements IFile
{
    /**
     * The constructor
     *
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        if (($this->filename === null) || (!is_file($this->filename))) {
            return '';
        }
        return file_get_contents($this->filename);
    }

    /**
     * {@inheritdoc}
     */
    public function save($content)
    {
        if ($this->filename === null) {
            throw new FileNotSpecified();
        }
        file_put_contents($this->filename, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function setFileName($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @var string
     */
    private $filename;
}
