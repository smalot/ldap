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

namespace Smalot\Ldap\Proxy;

use Smalot\Ldap\Object;

/**
 * Class SearchResultProxy
 *
 * @package Smalot\Ldap\Proxy
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
        $this->result_resource = $result_resource ? $result_resource : null;

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
     * @return Object|null resource
     */
    public function fetchEntry()
    {
        if (!$this->result_resource) {
            return null;
        }

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
