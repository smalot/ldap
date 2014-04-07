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
        $sizelimit = 1000,
        $timelimit = 0,
        $deref = LDAP_DEREF_NEVER
    ) {
        // Call function by this way to keep default arguments
        $args = func_get_args();
        array_unshift($args, $this->server->getResource());
        $results = @call_user_func_array('ldap_search', $args);

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
     * @param string $dn
     * @param bool   $createParent
     *
     * @return bool
     */
    public function createOrganizationalUnit($dn, $createParent = false)
    {
        $parts = ldap_explode_dn($dn, 0);
        unset($parts['count']);

        // Doesn't support anything else than 'ou'
        if (stripos($parts[0], 'ou=') !== 0) {
            return false;
        }

        // Previously create parents
        if ($createParent) {
            $parentParts = $parts;
            unset($parentParts[0]);

            $parent = implode(',', $parentParts);
            $found  = $this->searchDN($parent);

            if (!$found) {
                $this->createOrganizationalUnit($parent, true);
            }
        }

        // Already exists ?
        $found = $this->searchDN($dn);

        if (!$found) {
            list(, $name) = explode('=', $parts[0]);

            $object = new Object($dn);
            $object->get('objectClass')->add('top');
            $object->get('objectClass')->add('organizationalUnit');
            $object->get('ou')->add($name);

            return $this->add($object, false);
        }

        return true;
    }

    /**
     * @param Object $object
     * @param bool   $deleteBeforeIfExists
     * @param bool   $throwsExceptionIfExists
     *
     * @return bool
     * @throws \Exception
     */
    public function add(Object $object, $deleteBeforeIfExists = false, $throwsExceptionIfExists = true)
    {
        $dn    = $object->getDistinguisedName();
        $found = $this->searchDN($dn);

        if ($found && $deleteBeforeIfExists) {
            if (strcasecmp($found->getDistinguisedName(), $dn) === 0) {
                $this->remove($dn);
            }
        }

        if (!@ldap_add($this->server->getResource(), $dn, $object->getEntry()) && $throwsExceptionIfExists) {
            throw new \Exception('Unable to save specified DN: ' . ldap_error(
                    $this->server->getResource()
                ) . ' (' . $dn . ')');
        }

        return true;
    }

    /**
     * @param Object $object
     * @param string $action
     * @param array  $entry
     *
     * @return bool
     */
    public function modify(Object $object, $action, $entry)
    {
        switch ($action) {
            case 'add':
                @ldap_mod_add($this->server->getResource(), $object->getDistinguisedName(), $entry);
                break;
            case 'del':
                @ldap_mod_del($this->server->getResource(), $object->getDistinguisedName(), $entry);
                break;
            case 'replace':
                @ldap_mod_replace($this->server->getResource(), $object->getDistinguisedName(), $entry);
                break;
        }

        return true;
    }

    /**
     * @param string $dn
     *
     * @return bool
     * @throws \Exception
     */
    public function remove($dn)
    {
        if (!ldap_delete($this->server->getResource(), $dn)) {
            throw new \Exception('Unable to delete specified DN: ' . ldap_error($this->server->getResource()));
        }

        return true;
    }
}
