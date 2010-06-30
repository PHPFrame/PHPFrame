<?php
/**
 * PHPFrame/Utils/Exec.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Utils
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * This is a very simple class that wraps around PHP's exec() function and
 * provides an Object Oriented interface to command execution.
 *
 * @category PHPFrame
 * @package  Utils
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_Exec
{
    private $_cmd, $_output, $_return_var;

    /**
     * Constructor. This method instantiates an object of this class and
     * executes the command passed in the $cmd argument.
     *
     * @param string $cmd The command we want to run.
     *
     * @return void
     * @since  1.0
     */
    public function __construct($cmd)
    {
        $this->_cmd = (string) $cmd;

        exec($this->_cmd, $this->_output, $this->_return_var);
    }

    /**
     * Get the command that was run when constructing this object.
     *
     * @return string
     * @since  1.0
     */
    public function getCmd()
    {
        return $this->_cmd;
    }

    /**
     * Get output as array of strings. Each entry in the array is a line in the
     * output produced by the command.
     *
     * @return array
     * @since  1.0
     */
    public function getOutput()
    {
        return $this->_output;
    }

    /**
     * Get return var. This method returns the exit status of the command.
     *
     * @return int|string
     * @since  1.0
     */
    public function getReturnVar()
    {
        return $this->_return_var;
    }
}