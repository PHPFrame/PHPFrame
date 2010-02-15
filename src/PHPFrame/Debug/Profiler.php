<?php
/**
 * PHPFrame/Debug/Profiler.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Debug
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * The profiler class offers functionality to measure app performance based on a 
 * number of "milestones" added by client code. By default there are a few points 
 * defined in the PHPFrame core.
 * 
 * @category PHPFrame
 * @package  Debug
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @since    1.0
 */
class PHPFrame_Profiler implements IteratorAggregate, Countable
{
    /**
     * An array containing user defined execution milestones
     * 
     * @var array
     */
    private $_milestones = array();
    
    /**
     * Constructor
     * 
     * @return void
     * @since  1.0
     */
    public function __construct()
    {
        $this->addMilestone();
    }
    
    /**
     * Magic method invoked whe trying to use an object of this class as a string.
     * 
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
        
        // Work out difference between first and last entries
        $keys       = array_keys($this->_milestones);
        $last_key   = $keys[(count($this->_milestones)-1)];
        $last_item  = $this->_milestones[$last_key];
        $first_item = $this->_milestones[$keys[0]];
        
        $str .= "Total => ";
        $str .= round($last_item - $first_item, 2);
        $str .= " msec\n";
        
        return $str;
    }
    
    /**
     * Get iterator
     * 
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
     * @return void
     * @since  1.0
     */
    public function addMilestone() 
    {
        $stack = array();
        
        // Filter out profiler's calls from the backtrace
        foreach (debug_backtrace() as $backtrace_call) {
            $isset = isset($backtrace_call["class"]);
            if ($isset && $backtrace_call["class"] != "PHPFrame_Profiler") {
                $key  = $backtrace_call["class"]."::";
                $key .= $backtrace_call["function"]."()";
                $this->_milestones[$key] = $this->_microtimeFloat();
                break;
            }
        }
    }
    
    /**
     * Calculate current microtime in miliseconds
     * 
     * @return float
     * @since  1.0
     */
    private function _microtimeFloat() 
    {
        list ($msec, $sec) = explode(" ", microtime());
        return ((float) $msec + (float) $sec) * 1000;
    }
}
