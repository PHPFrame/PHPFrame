<?php
/**
 * PHPFrame/Debug/Profiler.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Debug
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Profiler Class
 * 
 * @category PHPFrame
 * @package  Debug
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_Profiler implements IteratorAggregate, Countable
{
    /**
     * Reference to single instance of itself
     * 
     * @var PHPFrame_Profiler
     */
    private static $_instance = null;
    
    /**
     * An array containing user defined execution milestones
     * 
     * @var array
     */
    private $_milestones = array();
    
    /**
     * Constructor
     * 
     * The private constructor ensures this class is not instantiated and is alwas 
     * used statically.
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function __construct()
    {
        $this->addMilestone();
    }
    
    /**
     * Magic method invoked whe trying to use an object of this class as a string.
     * 
     * @access private
     * @return string
     * @since  1.0
     */
    public function __toString() 
    {
        $str   = "";
        $count = 0;
        
        foreach ($this->_milestones as $key=>$value) {
            if ($count > 0) {
                $value = round($value - $this->_milestones[$prev_key], 2);
                $str  .= $prev_key." => ".$value." msec\n";
            }
            
            $prev_key = $key;
            $count++;
        }
        
        $str .= "Total => ";
        $keys = array_keys($this->_milestones);
        $str .= round($this->_milestones[$keys[(count($this->_milestones)-1)]] - $this->_milestones[$keys[0]], 2);
        $str .= " msec\n";
        
        return $str;
    } 
    
    /**
     * Get singleton instance
     * 
     * @access public
     * @return PHPFrame_Profiler
     * @since  1.0
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        
        return self::$_instance;
    }
    
    /**
     * Get iterator
     * 
     * @access private
     * @return ArrayIterator
     * @since  1.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_milestones);
    }
    
    /**
     * Count elements in internal array
     * 
     * @access private
     * @return ArrayIterator
     * @since  1.0
     */
    public function count()
    {
        return count($this->_milestones);
    }
    
    /**
     * Set milestone in the profiler
     * 
     * @param string $name The milestone name
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function addMilestone() 
    {
        if (!PHPFrame::Config()->get("debug.enable")) {
            return;
        }
        
        $stack = array();
        
        // Filter out profiler's calls from the backtrace
        foreach (debug_backtrace() as $backtrace_call) {
            $isset = isset($backtrace_call["class"]);
            if ($isset && $backtrace_call["class"] != "PHPFrame_Profiler") {
                $key = $backtrace_call["class"]."::".$backtrace_call["function"]."()";
                $this->_milestones[$key] = $this->_microtime_float();
                break;
            }
        }
    }
    
    /**
     * Calculate current microtime in miliseconds
     * 
     * @access private
     * @return float
     * @since  1.0
     */
    private function _microtime_float() 
    {
        list ($msec, $sec) = explode(" ", microtime());
        return ((float) $msec + (float) $sec) * 1000;
    }
}
