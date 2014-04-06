<?php

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
     * @return string
     */
    public function getValue()
    {
        return implode(',', $this->values);
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
