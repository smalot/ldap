LDAP
====

PHP library which handle LDAP data. Can parse too LDIF file.

## Prerequisites

You need at least PHP 5.3.3 and LDAP php module enabled.

## Installation

### Download project using composer

Add LDAP in your composer.json:

```js
{
    "require": {
        "smalot/ldap": "*"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update smalot/ldap
```

Composer will install the bundle to your project's `smalot/ldap` directory.

## Documentation

### Connect to LDAP server (simple)

``` php
<?php

require 'vendor/autoload.php';

$server  = new \Smalot\Ldap\Server();
$server->bind('cn=admin,dc=nodomain', 'S3CR3T');

```

### Connect to LDAP server (with options)

``` php
<?php

require 'vendor/autoload.php';

$options = array(
    LDAP_OPT_PROTOCOL_VERSION => 3,
);

$server  = new \Smalot\Ldap\Server('127.0.0.1', 389, $options);
$server->bind('cn=admin,dc=nodomain', 'S3CR3T');

```

### Find objects

``` php
<?php

require 'vendor/autoload.php';

$server  = new \Smalot\Ldap\Server();
$server->bind('cn=admin,dc=nodomain', 'S3CR3T');

$repository = $server->getDefaultRepository();
$results    = $repository->search('ou=Users,dc=nodomain', '(objectClass=*)');

while ($result = $results->fetchEntry()) {
    // Handle result object acording to your needs
}

```

### Find object using its distinguished named

``` php
<?php

require 'vendor/autoload.php';

$server  = new \Smalot\Ldap\Server();
$server->bind('cn=admin,dc=nodomain', 'S3CR3T');

$repository = $server->getDefaultRepository();

// Returns null if nothing found
$object = $repository->searchDN('uid=User1,ou=Users,dc=nodomain');

```

### Create object (and add it)

``` php
<?php

require 'vendor/autoload.php';

$server  = new \Smalot\Ldap\Server();
$server->bind('cn=admin,dc=nodomain', 'S3CR3T');

$repository = $server->getDefaultRepository();

// Create a new user
$dn   = 'uid=User1,ou=Users,dc=nodomain';
$user = new \Smalot\Ldap\Object($dn);
$user->get('objectClass')->add('top');
$user->get('objectClass')->add('inetOrgPerson');
$user->get('objectClass')->add('posixAccount');
$user->get('displayName')->setValue('Username');
$user->get('mail')->set('username@example.org');
$user->get('cn')->set('Username');
$user->get('sn')->set('Username');
$user->get('givenName')->set('Username');
$user->get('displayName')->set('Username');
$user->get('gidNumber')->set(0);
$user->get('uidNumber')->set(1001);
$user->get('homeDirectory')->set('/home/false');
$user->get('loginShell')->set('/sbin/nologin');

$password = \Smalot\Ldap\Tools::generateRandomPassword();
$user->get('userPassword')->set(\Smalot\Ldap\Tools::encodePassword($password));

// ...

// Store the new user
$repository->add($user);


// Create a new group
$dn    = 'cn=Group1,ou=Groups,dc=nodomain';
$group = new \Smalot\Ldap\Object($dn);
$group->get('objectClass')->add('top');
$group->get('objectClass')->add('posixGroup');
$group->get('gidNumber')->set(2001);

// Store the new group
$repository->add($group);


// Link user to the group
$repository->modify($group, 'add', array('memberUid' => $user->get('uid')->getValue()));

```






