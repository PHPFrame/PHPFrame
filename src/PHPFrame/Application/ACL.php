<?php
/**
 * PHPFrame/Application/ACL.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Application
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Access Level List Class
 * 
 * @category PHPFrame
 * @package  Application
 * @author   Lupo Montero <lupo@e-noise.com>
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
     * Get/set the group ID.
     * 
     * @param int $int [Optional] An integer representing the group ID.
     * 
     * @return int
     * @since  1.0
     */
    public function groupId($int=null)
    {
        if (!is_null($int)) {
            $this->fields["groupid"] = $this->validate("groupid", $int);
        }
        
        return $this->fields["groupid"];
    }
    
    /**
     * Get/set controller name for which this access level applies.
     * 
     * @param string $str [Optional] The controller name.
     * 
     * @return string
     * @since  1.0
     */
    public function controller($str=null)
    {
        if (!is_null($str)) {
            $this->fields["controller"] = $this->validate("controller", $str);
        }
        
        return $this->fields["controller"];
    }
    
    /**
     * Get/set action.
     * 
     * @param string $str [Optional] The action name.
     * 
     * @return string
     * @since  1.0
     */
    public function action($str=null)
    {
        if (!is_null($str)) {
            $this->fields["action"] = $this->validate("action", $str);
        }
        
        return $this->fields["action"];
    }
    
    /**
     * Get/set value.
     * 
     * @param string $str [Optional] The value for the access level. Values can 
     *                    be either "own" or "all".
     * 
     * @return string
     * @since  1.0
     */
    public function value($str=null)
    {
        if (!is_null($str)) {
            $this->fields["value"] = $this->validate("value", $str);
        }
        
        return $this->fields["value"];
    }
}
