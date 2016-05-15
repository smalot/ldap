<?php

/**
 * @file
 *          PHP library which handle LDAP data. Can parse too LDIF file.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @license MIT
 * @url     <https://github.com/smalot/ldap>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Smalot\Ldap;

/**
 * Class Attribute
 *
 * @package Smalot\Ldap
 */
class Attribute
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $values;

    /**
     * @param string $name
     * @param mixed  $values
     */
    public function __construct($name, $values = array())
    {
        $this->name = $name;

        if (!is_array($values)) {
            $values = array($values);
        }

        $this->setValues($values);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param string $default
     *
     * @return string
     */
    public function getValue($default = '')
    {
        if ($value = implode(',', $this->values)) {
            return $value;
        } else {
            return $default;
        }
    }

    /**
     * @param array $values
     *
     * @return $this
     */
    public function setValues($values)
    {
        if (!$values) {
            $values = array();
        }

        $values       = array_unique($values);
        $this->values = array_values($values);

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        return $this->setValues(array($value));
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public function has($value)
    {
        return (array_search($value, $this->values) !== false);
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function add($value)
    {
        $values   = $this->values;
        $values[] = $value;

        $this->setValues($values);

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function remove($value)
    {
        if ($key = array_search($value, $this->values)) {
            unset($this->values[$key]);
            $this->values = array_values($this->values);
        }

        return $this;
    }
}
