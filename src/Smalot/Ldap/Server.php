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

use Smalot\Ldap\Exception\BindingException;
use Smalot\Ldap\Exception\ConnectException;
use Smalot\Ldap\Proxy\SearchResultProxy;

/**
 * Class Server
 *
 * @package Smalot\Ldap
 */
class Server
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
     * @var Repository
     */
    protected $repository;

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
     * @throws ConnectException
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
                throw new ConnectException('Failed to set option value');
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
     * @return Repository
     */
    public function getDefaultRepository()
    {
        if (null === $this->repository) {
            $this->repository = new Repository($this);
        }

        return $this->repository;
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
        if (!ldap_bind($this->resource, $rdn, $password)) {
            throw new BindingException('Can\'t bind server');
        }

        return true;
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
