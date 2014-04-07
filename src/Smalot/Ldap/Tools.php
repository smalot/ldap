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

/**
 * Class Tools
 *
 * @package Smalot\Ldap
 */
class Tools
{
    const PASSWORD_HASH_PLAIN = 'plain-text';

    const PASSWORD_HASH_CRYPT = 'crypt';

    const PASSWORD_HASH_MD5 = 'MD5';

    const PASSWORD_HASH_SHA = 'SHA';

    const PASSWORD_HASH_SSHA = 'SSHA';

    /**
     * @param string $password
     * @param string $algo
     *
     * @return string
     * @throws \Exception
     */
    public static function encodePassword($password, $algo = self::PASSWORD_HASH_SSHA)
    {
        switch ($algo) {
            case self::PASSWORD_HASH_PLAIN:
                return $password;

            case self::PASSWORD_HASH_CRYPT:
                return '{CRYPT}' . crypt($password, '$6$' . self::generateSalt());

            case self::PASSWORD_HASH_MD5:
                return '{MD5}' . base64_encode(md5($password, true));

            case self::PASSWORD_HASH_SHA:
                return '{SHA}' . base64_encode(sha1($password, true));

            case self::PASSWORD_HASH_SSHA:
                $salt = self::generateSalt();

                return '{SSHA}' . base64_encode(sha1($password . $salt, true) . $salt);

            default:
                throw new \Exception('Hash not supported');
        }
    }

    /**
     * source: http://wp.me/pyhuE-47
     *
     * @param string $password
     * @param string $passwordEncoded
     *
     * @return string
     * @throws \Exception
     */
    public static function checkPassword($password, $passwordEncoded)
    {
        if ($passwordEncoded == '') {
            return false;
        }

        // Plain text
        if ($passwordEncoded{0} != '{') {
            if ($password == $passwordEncoded) {
                return true;
            }

            return false;
        }

        if (stripos($passwordEncoded, '{CRYPT}') === 0) {
            if (crypt($password, substr($passwordEncoded, 7)) == substr($passwordEncoded, 7)) {
                return true;
            }

            return false;
        } elseif (stripos($passwordEncoded, '{MD5}') === 0) {
            $encrypted_password = substr($passwordEncoded, 0, 5) . base64_encode(md5($password, true));
        } elseif (stripos($passwordEncoded, '{SHA}') === 0) {
            $encrypted_password = substr($passwordEncoded, 0, 5) . base64_encode(sha1($password, true));
        } elseif (stripos($passwordEncoded, '{SSHA}') === 0) {
            $salt               = substr(base64_decode(substr($passwordEncoded, 6)), 20);
            $encrypted_password = substr($passwordEncoded, 0, 6) . base64_encode(sha1($password . $salt, true) . $salt);
        } else {
            throw new \Exception('Hash not supported');
        }

        if ($passwordEncoded == $encrypted_password) {
            return true;
        }

        return false;
    }

    /**
     * @param int    $length
     * @param string $algo
     *
     * @return string
     */
    public static function generateRandomPassword($length = 20, $algo = self::PASSWORD_HASH_SSHA)
    {
        $password = self::generateSalt($length);

        return self::encodePassword($password, $algo);
    }

    /**
     * @param int $length
     *
     * @return string
     */
    public static function generateSalt($length = 8)
    {
        $salt = substr(
            str_shuffle(
                str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789=)-("&+-*/', $length * 2)
            ),
            0,
            $length
        );

        return $salt;
    }
}
