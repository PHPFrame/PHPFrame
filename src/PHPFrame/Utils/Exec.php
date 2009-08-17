<?php
/**
 * PHPFrame/Utils/Exec.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Utils
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Exec Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Utils
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Utils_Exec
{
    private $_cmd=null;
    private $_output=null;
    private $_return_var=null;
    
    public function __construct($cmd)
    {
        $this->_cmd = (string) $cmd;
        
        exec($this->_cmd, $this->_output, $this->_return_var);

        return $this;
    }
    
    public function getCmd()
    {
        return $this->_cmd;
    }
    
    public function getOutput()
    {
        return $this->_output;
    }
    
    public function getReturnVar()
    {
        return $this->_return_var;
    }
}