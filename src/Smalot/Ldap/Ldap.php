<?php

namespace Smalot\Ldap;

/**
 * Class Ldap
 *
 * @package Smalot\Ldap
 */
class Ldap
{

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var string
     */
    protected $baseDN;

    /**
     * @param string $hostname
     * @param int    $port
     * @param array  $options
     * @param bool   $autoConnect
     */
    public function __construct($hostname = null, $port = 389, $options = array(), $autoConnect = true)
    {
        $this->hostname = $hostname;
        $this->port     = $port;
        $this->options  = $options;

        if ($autoConnect) {
            $this->connect();
        }
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function connect()
    {
        // Check if not already connected
        if (!$this->resource) {
            if (!($this->resource = ldap_connect($this->hostname, $this->port))) {
                throw new ConnectException('Can\'t connect to server');
            }
        }

        foreach ($this->options as $option => $value) {
            if (!ldap_set_option($this->resource, $option, $value)) {
                throw new \Exception('Failed to set option value');
            }
        }

        return $this;
    }

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param string $rdn
     * @param string $password
     *
     * @return bool
     * @throws BindingException
     */
    public function bind($rdn = null, $password = null)
    {

        $ldap = $this->resource;
        ldap_bind($ldap, $rdn, $password);

//    $result = ldap_search($ldap, 'dc=nodomain', '(objectClass=top)');
//    $info   = ldap_get_entries($ldap, $result);
//    var_dump($info);
//    die();

//    if (!ldap_bind($this->resource, $rdn, $password)) {
//      throw new BindingException('Can\'t bind server');
//    }

        return true;
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
        $filter,
        $attributes = array(),
        $attrsonly = 0,
        $sizelimit = 0,
        $timelimit = 0,
        $deref = LDAP_DEREF_NEVER
    ) {

        $args = func_get_args();
        array_unshift($args, $this->resource);
        $results = call_user_func_array('ldap_search', $args);

        return new SearchResultProxy($this->resource, $results);
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
     * @return bool
     */
    public function close()
    {
        if ($this->resource) {
            ldap_close($this->resource);
            $this->resource = null;
        }

        return true;
    }
}
