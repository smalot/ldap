<?php

namespace Smalot\Ldap;

/**
 * Class SearchResultProxy
 *
 * @package Smalot\Ldap
 */
class SearchResultProxy
{

    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var resource
     */
    protected $result_resource;

    /**
     * @var resource
     */
    protected $entry_resource;

    /**
     * @param resource $resource
     * @param resource $result_resource
     */
    public function __construct($resource, $result_resource)
    {
        $this->resource        = $resource;
        $this->result_resource = $result_resource;

        $this->reset();
    }

    /**
     *
     */
    public function reset()
    {
        $this->entry_resource = null;
    }

    /**
     * @return mixed resource
     */
    public function fetchEntry()
    {
        if (null === $this->entry_resource) {
            $this->entry_resource = ldap_first_entry($this->resource, $this->result_resource);
        } else {
            $this->entry_resource = ldap_next_entry($this->resource, $this->entry_resource);
        }

        if (!$this->entry_resource) {
            return null;
        }

        $dn            = ldap_get_dn($this->resource, $this->entry_resource);
        $rawAttributes = ldap_get_attributes($this->resource, $this->entry_resource);
        $count         = $rawAttributes['count'];
        $attributes    = array();

        for ($i = 0; $i < $count; $i++) {
            $attribute = $rawAttributes[$i];
            $values    = array();
            $subCount  = $rawAttributes[$attribute]['count'];

            for ($j = 0; $j < $subCount; $j++) {
                $values[] = $rawAttributes[$attribute][$j];
            }

            $attributes[$attribute] = $values;
        }

        $object = new Object($dn, $attributes);

        return $object;
    }
}
