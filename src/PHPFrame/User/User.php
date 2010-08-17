<?php
/**
 * PHPFrame/User/User.php
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
 * User Class
 *
 * @category PHPFrame
 * @package  User
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_User extends PHPFrame_PersistentObject
{
    /**
     * String representation of the groupid.
     *
     * @var string
     */
    protected $group_name;

    /**
     * Constructor
     *
     * @param array $options [Optional] An associative array containing the
     *                       following options:
     *                         - id (int)
     *                         - ctime (int)
     *                         - mtime (int)
     *                         - owner (int)
     *                         - group (int)
     *                         - perms (int)
     *                         - group_id (int)
     *                         - email (string)
     *                         - password (string)
     *                         - params (string|array)
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        // before we construct the parent we add the necessary fields
        $this->addField(
            "group_id",
            0,
            false,
            new PHPFrame_IntFilter()
        );
        $this->addField(
            "email",
            null,
            false,
            new PHPFrame_EmailFilter(array("min_length"=>7, "max_length"=>100))
        );
        $this->addField(
            "password",
            null,
            false,
            new PHPFrame_RegexpFilter(array(
                "regexp"=>'/^.{6,100}$/',
                "min_length"=>6,
                "max_length"=>100
            ))
        );
        $this->addField(
            "params",
            null,
            true,
            new PHPFrame_StringFilter()
        );

        parent::__construct($options);
    }

    /**
     * Magic method invoked when object is serialised.
     *
     * @return array
     * @since  1.0
     */
    public function __sleep()
    {
        return array_merge(parent::__sleep(), array("group_name"));
    }

    /**
     * Return object as associative array.
     *
     * @return array
     * @since  1.0
     */
    public function getIterator()
    {
        $array = array();

        foreach ($this->fields as $key=>$value) {
            if (($key == "params")) {
                if (is_array($value) && count($value) > 0) {
                    $value = serialize($value);
                } else {
                    $value = "";
                }
            }

            $array[$key] = $value;
        }

        return new ArrayIterator($array);
    }

    /**
     * Get/set groupid.
     *
     * @param int $int [Optional] The group ID.
     *
     * @return int
     * @since  1.0
     */
    public function groupId($int=null)
    {
        if (!is_null($int)) {
            $this->fields["group_id"] = $this->validate("group_id", $int);
        }

        return $this->fields["group_id"];
    }

    /**
     * Get/set groupname.
     *
     * @param string $str [Optional] The group name.
     *
     * @return string
     * @since  1.0
     */
    public function groupName($str=null)
    {
        if (!is_null($str)) {
            $this->group_name = trim((string) $str);
        }

        return $this->group_name;
    }

    /**
     * Get/set email.
     *
     * @param string $str [Optional] The email.
     *
     * @return string
     * @since  1.0
     */
    public function email($str=null)
    {
        if (!is_null($str)) {
            $this->fields["email"] = $this->validate("email", $str);
        }

        return $this->fields["email"];
    }

    /**
     * Get/set password.
     *
     * @param string $str [Optional] The password.
     *
     * @return string
     * @since  1.0
     */
    public function password($str=null)
    {
        if (!is_null($str)) {
            $this->fields["password"] = $this->validate("password", $str);
        }

        return $this->fields["password"];
    }

    /**
     * Get/set params.
     *
     * @param string|array $params [Optional] Either a serialised string or an
     *                             array.
     *
     * @return string
     * @since  1.0
     */
    public function params($params=null)
    {
        if (!is_null($params)) {
            if (is_string($params)) {
            	if (!empty($params))
                    $params = unserialize($params);
                else
                    $params = array();
            }

            if (!is_array($params)) {
                $msg  = "Argument \$params must be either a serialised string or ";
                $msg .= "an array";
                throw new InvalidArgumentException($msg);
            }

            $this->fields["params"] = $params;
        }

        return $this->fields["params"];
    }
}
