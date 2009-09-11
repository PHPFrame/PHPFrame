<?php
/**
 * PHPFrame/Base/Collection.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Base
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Abstract Collection
 * 
 * @category PHPFrame
 * @package  Base
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      ArrayObject
 * @since    1.0
 */
abstract class PHPFrame_Collection implements Iterator, Countable
{
    /**
     * Get limit of entries per page
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    abstract public function getLimit();
    
    /**
     * Get position at which the current page starts
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    abstract public function getLimitstart();
    
    /**
     * Get total number of entries in superset
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    abstract public function getTotal();
    
    /**
     * Get number of pages
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getPages() 
    {
        if ($this->getLimit() > 0 && $this->getTotal() > 0) {
            // Calculate number of pages
            return (int) ceil($this->getTotal()/$this->getLimit());
        } else {
            return 0;
        }
    }
    
    /**
     * Get current page number
     * 
     * @access public
     * @return int
     * @since  1.0
     */
    public function getCurrentPage() 
    {
        // Calculate current page
        if ($this->getLimit() > 0) {
            return (int) (ceil($this->getLimitstart()/$this->getLimit())+1);
        } else {
            return 0;
        }
    }
}
