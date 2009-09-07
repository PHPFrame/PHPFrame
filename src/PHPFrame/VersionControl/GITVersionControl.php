<?php
/**
 * PHPFrame/SCM/GITVersionControl.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   SCM
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @ignore
 */

/**
 * GIT Class
 * 
 * @category PHPFrame
 * @package  SCM
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @todo     This class will not be implemented in version 1.0
 * @ignore
 */
class PHPFrame_GITVersionControl implements PHPFrame_IVersionControl
{   
    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct() {}
    
    public function checkout($url, $path, $username=null, $password=null) {}
    
    public function update($path) {}
    
    public function switchURL($url, $path) {}
    
    public function export($url, $path) {}
    
    public function commit() {}
}
