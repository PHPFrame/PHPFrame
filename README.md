[PHPFrame](http://www.phpframe.org/)
====================================

PHPFrame is a minimal and object oriented PHP MVC framework written for PHP
version 5.

This project is still work in progress...


Project resources
-----------------

* [Project home page](http://www.phpframe.org/)

* [Documentation](http://www.phpframe.org/doc/api/)

* [Bug tracker](http://github.com/PHPFrame/PHPFrame/issues)

* [Source](http://github.com/PHPFrame/PHPFrame)

* [Developers discussion group](http://groups.google.com/group/phpframe-dev)

* [Continuous Integration Server](http://ci.phpframe.org:8081/cruisecontrol)


Dependencies
------------

### Required


#### PHP

* php 5.2.3 or higher
* php-sqlite

#### PEAR

* pear 1.9.0 or higher
* pear/Config
* pear/XML_Beautifier
* pear/Console_CommandLine
* pear/Console_ProgressBar
* pear/Archive_Tar
* pear/HTTP_Request2

### Optional


#### PHP

* php-mysql
* php-imap
* php-gd
* php-json

#### PECL

* docblock


Installing PHPFrame with the PEAR command line installer
--------------------------------------------------------

1. `pear channel-discover pear.phpframe.org`
2. `pear config-set preferred_state alpha`
3. `pear install phpframe/PHPFrame`
4. `pear config-set preferred_state stable`


Command line tool examples
--------------------------

### Show command line utility manual

    phpframe

### Create a new app

    mkdir newapp
    cd newapp
    phpframe app create app_name="My cool app"

### Remove an app

This will delete all app files and database if any.

    cd myapp
    phpframe app remove

### Set a config param

In this example we set the base_url config parameter to 'CLI'. Note that the
base_url config parameter is required. If your app will only run on the command
line you can use 'CLI' instead of a valid URL.

    cd myapp
    phpframe config set key=base_url value=CLI

### Create a database table based on persistent object class

In this example we use the base User class.

    cd myapp
    phpframe scaffold table path=/Users/lupo/Documents/workspace/PHPFrame/src/PHPFrame/User/User.php

### Create an empty persistent object class

In this example the resulting class will be called 'Post' and will be stored
under src/models.

    cd myapp
    phpframe scaffold persistent name=Post

### Create an empty mapper class for a PersistentObject class

In this example we create a mapper for the Post class created in the previous
example. Note that the PersistentObject has to exist and has to extend
PHPFrame_PersistentObject. The resulting class will be called 'PostsMapper' and
will be stored under src/models.

    cd myapp
    phpframe scaffold mapper class=Post

### Create an empty action controller

The resulting class will be called 'BlogController' and will be stored under
src/controllers.

    cd myapp
    phpframe scaffold controller name=Blog

### Create an empty view helper class

The resulting class will be called 'BlogHelper' and will be stored under
src/helpers.

    cd myapp
    phpframe scaffold helper name=Blog

### Create a new plugin class called

The resulting class will be called 'BlogRouter' and will be stored under
src/plugins.

    cd myapp
    phpframe scaffold plugin name=BlogRouter
