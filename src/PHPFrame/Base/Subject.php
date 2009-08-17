<?php
/**
 * PHPFrame/Base/Subject.php
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
 * @since      1.0
 */

/**
 * Observable subject class
 * 
 * This class provides an abstract implementation of the SplSubject interface.
 * 
 * This class is designed to work together with the Observer class.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Base
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_Base_Observer
 * @since      1.0
 */
abstract class PHPFrame_Base_Subject implements SplSubject
{
    /**
     * An "storage" object to store observers
     *  
     * @var SplObjectStorage
     */
    private $_obs=null;
    
    /**
     * Attach an observer to this subject
     * 
     * @param PHPFrame_Base_Observer $observer The object to attach to this subject
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function attach(SplObserver $observer)
    {
        if (!$this->_obs instanceof SplObjectStorage) {
            $this->_obs = new SplObjectStorage();
        }
        
        $this->_obs->attach($observer);
    }

    /**
     * Detach an object from the subject
     * 
     * @param SplObserver $observer The observer object to detach
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function detach(SplObserver $observer)
    {
        $this->_obs->detach($observer);
    }
    
    /**
     * Notify observers
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function notify()
    {
        if ($this->_obs instanceof SplObjectStorage) {
            foreach ($this->_obs as $obs) {
                $obs->update($this);
            }
        }
    }
}