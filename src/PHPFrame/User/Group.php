<?php
/**
 * PHPFrame/User/Group.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   User
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Group Class
 *
 * @category PHPFrame
 * @package  User
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_Group extends PHPFrame_PersistentObject
{
    /**
     * Constructor.
     *
     * @param array $options [Optional]
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        // Create the filter for the group name
        $filter = new PHPFrame_StringFilter(array(
            "min_length" => 3,
            "max_length" => 50
        ));
        // Add the field in the PersistentObject
        $this->addField("name", null, false, $filter);

        parent::__construct($options);
    }

    /**
     * Get/set group name.
     *
     * @param string $str [Optional] The group name.
     *
     * @return string
     * @since  1.0
     */
    public function name($str=null)
    {
        if (!is_null($str)) {
            $this->fields["name"] = $this->validate("name", $str);
        }

        return $this->fields["name"];
    }
}
