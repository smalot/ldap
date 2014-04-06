<?php

namespace Smalot\Ldap;

use Smalot\Ldap\Proxy\SearchResultProxy;

/**
 * Class Repository
 *
 * @package Smalot\Ldap
 */
class Repository
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * @param string $base_dn
     * @param string $filter
     * @param array  $attributes
     * @param int    $attrsonly
     * @param int    $sizelimit
     * @param int    $timelimit
     * @param int    $deref
     *
     * @return SearchResultProxy
     */
    public function search(
        $base_dn,
        $filter = '(objectClass=*)',
        $attributes = array(),
        $attrsonly = 0,
        $sizelimit = 0,
        $timelimit = 0,
        $deref = LDAP_DEREF_NEVER
    ) {
        $args = func_get_args();
        array_unshift($args, $this->server->getResource());
        $results = call_user_func_array('ldap_search', $args);

        return new SearchResultProxy($this->server->getResource(), $results);
    }

    /**
     * @param string $base_dn
     * @param string $filter
     *
     * @return Object
     */
    public function searchDN(
        $base_dn,
        $filter = '(objectClass=*)'
    ) {
        $results = $this->search($base_dn, $filter, array(), 0, 1);

        return $results->fetchEntry();
    }

    /**
     * @param Object $object
     *
     * @return bool
     */
    public function save(Object $object)
    {
        var_dump($object->getDistinguisedName(), $object->getEntry());
        ldap_add($this->server->getResource(), $object->getDistinguisedName(), $object->getEntry());

        var_dump(ldap_error($this->server->getResource()));

        return true;
    }
}
