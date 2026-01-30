<?php
/**
 * @package axy\htpasswd
 * @author Oleg Grigoriev <go.vasac@gmail.com>
 */

namespace axy\htpasswd;

/**
 * User in htpasswd file
 */
class User
{
    /**
     * The constructor
     *
     * @param string $name
     * @param string $hash [optional]
     */
    public function __construct($name, $hash = null)
    {
        $this->name = $name;
        $this->hash = $hash;
    }

    /**
     * Returns the user name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the content of a htpasswd file line
     *
     * @return string
     */
    public function getFileLine()
    {
        return $this->name.':'.$this->hash;
    }

    /**
     * Checks a password for the user
     *
     * @param string $password
     * @return bool
     */
    public function verify($password)
    {
        return Crypt::verify($password, $this->hash);
    }

    /**
     * Sets the password for the user
     *
     * @param string $password
     * @param string $algorithm
     * @param array $options [optional]
     */
    public function setPassword($password, $algorithm = PasswordFile::ALG_MD5, array $options = null)
    {
        $this->hash = Crypt::hash($password, $algorithm, $options);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFileLine();
    }

    /**
     * Load an user from the file line
     *
     * @param string $line
     * @return \axy\htpasswd\User
     */
    public static function loadFromFileLine($line)
    {
        $line = explode(':', $line, 2);
        if (count($line) !== 2) {
            return null;
        }
        return new self($line[0], $line[1]);
    }

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $hash;
}
