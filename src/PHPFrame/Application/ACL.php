<?php
/**
 * PHPFrame/Application/ACL.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Application
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Access Level List Class
 * 
 * @category PHPFrame
 * @package  Application
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 * @internal
 */
class PHPFrame_ACL extends PHPFrame_PersistentObject
{
    /**
     * The constructor can optionally take an associative array as its only 
     * argument. If an associative array is passed the object will look for the 
     * following keys to populate the ACL object:
     * 
     *  - groupid
     *  - controller
     *  - action
     *  - value
     * 
     * @param array $options [Optional] An associative array with the options 
     *                                  as described above.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        $this->addField(
            "groupid", 
            null, 
            false,  
            new PHPFrame_IntFilter()
        );
        $this->addField(
            "controller", 
            null, 
            false,  
            new PHPFrame_RegexpFilter(array(
                "regexp"=>"/^[a-zA-Z]{1,50}$/", 
                "min_length"=>1, 
                "max_length"=>50
            ))
        );
        $this->addField(
            "action", 
            "*", 
            false,  
            new PHPFrame_RegexpFilter(array(
                "regexp"=>"/^[a-zA-Z\*]{1,50}$/",
                "min_length"=>1, 
                "max_length"=>50
            ))
        );
        $this->addField(
            "value", 
            "own", 
            false,  
            new PHPFrame_RegexpFilter(array(
                "regexp"=>"/^(own|all)$/",
                "min_length"=>3, 
                "max_length"=>3
            ))
        );
        
        parent::__construct($options);
    }
    
    /**
     * Get the group ID.
     * 
     * @return int
     * @since  1.0
     */
    public function getGroupId()
    {
        return $this->fields["groupid"];
    }
    
    /**
     * Set the group ID
     * 
     * @param int $int An integer representing the group ID.
     * 
     * @return void
     * @since  1.0
     */
    public function setGroupId($int)
    {
        $this->fields["groupid"] = $this->validate("groupid", $int);
    }
    
    /**
     * Get controller name for which this access level applies.
     * 
     * @return string
     * @since  1.0
     */
    public function getController()
    {
        return $this->fields["controller"];
    }
    
    /**
     * Set controller name for which this access level applies.
     * 
     * @param string $str The controller name.
     * 
     * @return void
     * @since  1.0
     */
    public function setController($str)
    {
        $this->fields["controller"] = $this->validate("controller", $str);
    }
    
    /**
     * Get action.
     * 
     * @return string
     * @since  1.0
     */
    public function getAction()
    {
        return $this->fields["action"];
    }
    
    /**
     * Set action.
     * 
     * @param string $str The action name.
     * 
     * @return void
     * @since  1.0
     */
    public function setAction($str)
    {
        $this->fields["action"] = $this->validate("action", $str);
    }
    
    /**
     * Get value.
     * 
     * @return string
     * @since  1.0
     */
    public function getValue()
    {
        return $this->fields["value"];
    }
    
    /**
     * Set value.
     * 
     * @param string $str The value for the access level. Values can be either 
     *                    "own" or "all".
     *                    
     * @return string
     * @since  1.0
     */
    public function setValue($str)
    {
        $this->fields["value"] = $this->validate("value", $str);
    }
}
