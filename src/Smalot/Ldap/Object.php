<?php

namespace Smalot\Ldap;

use Smalot\Ldap\Exception\NotFoundAttributeException;

/**
 * Class Object
 *
 * @package Smalot\Ldap
 */
class Object
{
    /**
     * @var string
     */
    protected $distinguisedName;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @param string $dn
     * @param array  $attributes
     */
    public function __construct($dn = null, $attributes = array())
    {
        $this->distinguisedName = $dn;

        foreach ($attributes as $name => $attribute) {
            if (!$attribute instanceof Attribute) {
                $attribute = new Attribute($name, $attribute);
            }

            $this->set($attribute);
        }
    }

    /**
     * @return string
     */
    public function getDistinguisedName()
    {
        return $this->distinguisedName;
    }

    /**
     * @param string $dn
     *
     * @return $this
     */
    public function setDistinguisedName($dn)
    {
        $this->distinguisedName = $dn;

        return $this;
    }

    /**
     * @return string
     */
    public function getParentDN()
    {
        $parts = ldap_explode_dn($this->distinguisedName, 0);
        unset($parts['count']);
        unset($parts[0]);

        return implode(',', $parts);
    }

    /**
     * @param string $name
     * @param bool   $create
     *
     * @return Attribute
     *
     * @throws NotFoundAttributeException
     */
    public function get($name, $create = true)
    {
        $name = strtolower($name);

        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        if ($create) {
            return ($this->attributes[$name] = new Attribute($name));
        }

        return new NotFoundAttributeException('Attribute not found');
    }

    /**
     * @param Attribute $attribute
     *
     * @return $this
     */
    public function set($attribute)
    {
        $this->attributes[strtolower($attribute->getName())] = $attribute;

        return $this;
    }

    /**
     * @param mixed $attribute
     *
     * @return $this
     */
    public function remove($attribute)
    {
        if ($attribute instanceof Attribute) {
            $name = strtolower($attribute->getName());
            unset($this->attributes[$name]);
        } else {
            unset($this->attributes[strtolower($attribute)]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getEntry()
    {
        $entry = array();

        /** @var Attribute $attribute */
        foreach ($this->attributes as $name => $attribute) {
            $values = $attribute->getValues();

            if (count($values) > 1) {
                foreach ($values as $value) {
                    $entry[$name][] = $value;
                }
            } else {
                $entry[$name] = $values[0];
            }
        }

        return $entry;
    }
}
