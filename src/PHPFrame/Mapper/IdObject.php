<?php
/**
 * PHPFrame/Mapper/IdObject.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Mapper
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @since     1.0
 */

/**
 * Identity Object abstract class
 *
 * This class encapsulates the selection of persistent objects when using a mapper.
 *
 * @category PHPFrame
 * @package  Mapper
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
abstract class PHPFrame_IdObject
{
    /**
     * Constructor
     *
     * @param array $options An associative array with initialisation options.
     *                       For a list of available options invoke
     *                       PHPFrame_IdObject::getOptions().
     *
     * @return void
     * @since  1.0
     */
    public function __construct($options=null)
    {
        // Process input options
        if (!is_null($options)) {
            $options = new PHPFrame_Array($options);
            if (!$options->isAssoc()) {
                $msg = "Options passed in wrong format.";
                $msg .= " Options should be passed as an associative";
                $msg .= " array with key value pairs.";
                throw new InvalidArgumentException($msg);
            }

            // Options is an array
            foreach ($options as $key=>$val) {
                if (method_exists($this, $key)) {
                    call_user_func_array(array($this, $key), array($val));
                }
            }
        }
    }

    /**
     * Magic method invoked when trying to use object as string.
     *
     * @return string
     * @since  1.0
     */
    abstract public function __toString();

    /**
     * Return an array with the list of available options in this object.
     *
     * @return array
     * @since  1.0
     */
    public function getOptions()
    {
        $raw_keys = array_keys(get_object_vars($this));

        // Remove preceding underscore from property names
        foreach ($raw_keys as $key) {
            $keys[] = substr($key, 1);
        }

        return $keys;
    }

    /**
     * Set the fields array used in select statement
     *
     * @param string|array $fields a string or array of strings with field names
     *
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    abstract public function select($fields);

    /**
     * Set the table from which to select rows
     *
     * @param string $table A string with the table name
     *
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    abstract public function from($table);

    /**
     * Add "where" condition. For example:
     *
     * <code>
     * $id_obj->where("id", "=", $id);
     * </code>
     *
     * @param string $left     The left operand.
     * @param string $operator The comparison operator. ie: "=" or "<".
     * @param string $right    The right operand.
     *
     * @return PHPFrame_IdObject
     * @since  1.0
     */
    abstract public function where($left, $operator, $right);

    /**
     * Get the an array with the fields in the SELECT query
     *
     * @return array
     * @since  1.0
     */
    abstract public function getObjectFields();

    /**
     * Get the table name in the FROM part of the query
     *
     * @return string
     * @since  1.0
     */
    abstract public function getTableName();
}