<?php
/**
 * PHPFrame/Base/StdObject.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Base
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Standard Object Class
 * 
 * This class provides a standard object with some useful generic methods.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Base
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Base_StdObject
{   
    protected function throwException($msg, $code=PHPFrame_Exception::ERROR)
    {
        try {
            throw new PHPFrame_Exception($msg=null, $code);
        } catch (PHPFrame_Exception $e) {
            var_dump($e); exit;
            throw $e;
        }
    }
}
