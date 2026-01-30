<?php
/**
 * @package axy\htpasswd
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\htpasswd\io;

/**
 * Htpasswd file mock for tests
 */
class Test implements IFile
{
    /**
     * The constructor
     *
     * @param string|string[] $content [optional]
     */
    public function __construct($content = null)
    {
        if ($content === null) {
            $content = '';
        } elseif (is_array($content)) {
            $content = implode(PHP_EOL, $content);
        }
        $this->content = $content;
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function save($content)
    {
        $this->content = $content;
    }

    /**
     * {@inheritdoc}
     */
    public function setFileName($content)
    {
    }

    /**
     * Returns the content as an array of lines
     */
    public function getLines()
    {
        return explode("\n", str_replace("\r", '', $this->content));
    }

    /**
     * @var string
     */
    private $content;
}
