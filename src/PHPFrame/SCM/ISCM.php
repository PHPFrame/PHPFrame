<?php
/**
 * PHPFrame/SCM/ISCM.php
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
 */

/**
 * SCM Interface
 * 
 * @category PHPFrame
 * @package  SCM
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
interface PHPFrame_ISCM
{
    public function checkout($url, $path, $username=null, $password=null);
    
    public function update($path);
    
    public function switchURL($url, $path);
    
    public function export($url, $path);
    
    public function commit();
}
