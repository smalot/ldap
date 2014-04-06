<?php

namespace Smalot\Ldap;

/**
 * Class Ldif
 *
 * @package Smalot\Ldap
 */
class Ldif
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * @param string   $filename
     * @param callable $callback
     *
     * @return bool
     */
    public function importFile($filename, $callback = null)
    {
        $content = file_get_contents($filename);

        return $this->importContent($content, $callback);
    }

    /**
     * @param string   $content
     * @param callable $callback
     *
     * @return bool
     */
    public function importContent($content, $callback = null)
    {
        $content = trim(str_replace("\r", '', $content));

        // Split full content into small blocs
        $blocs = explode("\n\n", $content);

        $this->repository = $this->server->getDefaultRepository();

        foreach ($blocs as $bloc) {
            $this->handleObject($bloc, $callback);
        }

        return true;
    }

    /**
     * @param          $bloc
     * @param callable $callback
     *
     * @return bool
     */
    public function handleObject($bloc, $callback = null)
    {
        $parts = preg_split('/\n\-\n/s', $bloc);

        $version      = '1';
        $dn           = '';
        $control      = '';
        $changeType   = 'add';
        $deleteoldrdn = false;
        $actions      = array();

        foreach ($parts as $part) {
            // Fix auto wrapped lines
            $part = rtrim(str_replace("\n ", '', $part), '- ');

            preg_match_all('/([a-z_0-9]+):([^\n]*)/si', $part, $values);

            $action        = array();
            $currentAction = 'add';

            foreach ($values[1] as $pos => $property) {
                $value = $values[2][$pos];

                // Decode base64 encoded and url values
                if (strpos($value, ':') === 0) {
                    $value = base64_decode(ltrim($value, ': '));
                } elseif (strpos($value, '<') === 0) {
                    $value = ltrim($value, '< ');
                } else {
                    $value = ltrim($value);
                }

                switch (strtolower($property)) {
                    case 'version':
                        $version = (int) $value;
                        break;

                    case 'dn':
                        $dn = $value;
                        break;

                    case 'control':
                        $control = $value;
                        break;

                    case 'changetype':
                        $changeType = $value;
                        break;

                    case 'deleteoldrdn':
                        $deleteoldrdn = $value ? true : false;
                        break;

                    case 'add': // rfc2849
                    case 'delete':
                    case 'replace':
                    case 'increment': // rfc4525
                        $currentAction                  = $property;
                        $action[$currentAction][$value] = array();
                        break;

                    default:
                        $action[$currentAction][$property][] = $value;
                }

//                echo $property . ' = ' . $value . "\n";
            }

            $actions[] = $action;
        }

        if (is_callable($callback)) {
            return $callback($version, $dn, $control, $changeType, $deleteoldrdn, $actions);
        } else {
            return $this->handleActions($version, $dn, $control, $changeType, $deleteoldrdn, $actions);
        }
    }

    /**
     * @param string   $version
     * @param string   $dn
     * @param string   $control
     * @param string   $changeType
     * @param bool     $deleteoldrdn
     * @param array    $actions
     *
     * @return bool
     */
    public function handleActions($version, $dn, $control, $changeType, $deleteoldrdn, $actions)
    {
        if ($changeType == 'add') {
            $attributes = $actions[0]['add'];
            $object     = new Object($dn, $attributes);

            return $this->repository->save($object);
        }

        // Others are not currently supported
        return false;

//        switch ($changeType) {
//            case 'add':
//                break;
//
//            case 'modify':
//                break;
//
//            case 'delete':
//                break;
//
//            case 'modrdn':
//            case 'moddn':
//                break;
//        }
//
//        return true;
    }
}
