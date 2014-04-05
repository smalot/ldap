<?php

namespace Smalot\Ldap;

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
        $this->attributes       = $attributes;
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
     * @param string $name
     * @param bool   $create
     *
     * @return Attribute
     *
     * @throws NotFoundAttribute
     */
    public function get($name, $create = true)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        if ($create) {
            $attribute               = new Attribute($name);
            $this->attributes[$name] = $attribute;

            return $attribute;
        } else {
            return new NotFoundAttribute('Attribute not found');
        }
    }

    /**
     * @param Attribute $attribute
     *
     * @return $this
     */
    public function set(Attribute $attribute)
    {
        $name                    = $attribute->getName();
        $this->attributes[$name] = $attribute;

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
            $name = $attribute->getName();
            unset($this->attributes[$name]);
        } else {
            unset($this->attributes[$attribute]);
        }

        return $this;
    }
}
