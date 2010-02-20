[PHPFrame](http://www.phpframe.org/)
================================

PHPFrame is a minimal and object oriented PHP MVC framework written for PHP 
version 5. 

This project is still work in progress...

Project resources
------------------
* [Project home page](http://www.phpframe.org/)

* [Documentation](http://www.phpframe.org/doc/api/)

* [Bug tracker](http://github.com/PHPFrame/PHPFrame/issues)

* [Source](http://github.com/PHPFrame/PHPFrame)

* [Developers discussion group](http://groups.google.com/group/phpframe-dev)

* [Continuous Integration Server](http://ci.phpframe.org:8080/cruisecontrol)

How to install PHPFrame with the PEAR command line installer
-----------------------------

Tell the installer about our PEAR channel:

`pear channel-discover pear.phpframe.org`

Tell the PEAR installer that you want to install alpha packages:

`pear config-set preferred_state alpha`

Download and install the latest successful build:

`pear install phpframe/PHPFrame`

Run post installation scripts:

`pear run-scripts phpframe/PHPFrame`

Set your preferred state for packages back to stable:

`pear config-set preferred_state stable`

