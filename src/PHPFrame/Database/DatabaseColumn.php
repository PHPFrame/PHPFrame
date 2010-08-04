<?php
/**
 * PHPFrame/Database/DatabaseColumn.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Database
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @since     1.0
 */

/**
 * This class encapsulates the definition of a column within a database table.
 *
 * @category PHPFrame
 * @package  Database
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_DatabaseColumn implements IteratorAggregate
{
    const TYPE_BOOL           = "boolean";
    const TYPE_TINYINT        = "tinyint";
    const TYPE_SMALLINT       = "smallint";
    const TYPE_MEDIUMINT      = "mediumint";
    const TYPE_INT            = "int";
    const TYPE_BIGINT         = "bigint";
    const TYPE_FLOAT          = "float";
    const TYPE_CHAR           = "char";
    const TYPE_VARCHAR        = "varchar";
    const TYPE_TEXT           = "text";
    const TYPE_BLOB           = "blob";
    const TYPE_DATETIME       = "datetime";
    const TYPE_DATE           = "date";
    const TYPE_TIME           = "time";
    const TYPE_YEAR           = "year";
    const TYPE_TIMESTAMP      = "timestamp";
    const TYPE_ENUM           = "enum";
    const TYPE_BINARY         = "binary";
    const EXTRA_AUTOINCREMENT = "auto_increment";
    const KEY_PRIMARY         = "PRI";
    const KEY_UNIQUE          = "UNI";
    const KEY_MULTIPLE        = "MUL";

    /**
     * The column name.
     *
     * @var string
     */
    private $_name;
    /**
     * The column type.
     *
     * @var string
     */
    private $_type;
    /**
     * The field length, used for TYPE VARCHAR and TYPE_CHAR.
     *
     * @var int
     */
    private $_length;
    /**
     * Whether the column allows NULL values.
     *
     * @var bool
     */
    private $_null;
    /**
     * The column key type if any.
     *
     * @var string|null
     */
    private $_key;
    /**
     * The column's default value.
     *
     * @var string|null
     */
    private $_default;
    /**
     * The column's extra attributes. So far only possible values are NULL or
     * "auto_increment".
     *
     * @var string|null
     */
    private $_extra;
    /**
     * Array containig possible values for columns of type 'enum'.
     *
     * @var array
     */
    private $_enums;
    /**
     * A reflection of object of this class used to check allowed values in
     * setters accoring to class constants.
     *
     * @var ReflectionClass
     */
    private $_reflection_obj;

    /**
     * Constructor.
     *
     * @param array $options An associative array with the column properties.
     *                       Option keys:
     *                       - name (required). See
     *                         {@link PHPFrame_DatabaseColumn::setName()}.
     *                       - type. See
     *                         {@link PHPFrame_DatabaseColumn::setType()}.
     *                       - null. See
     *                         {@link PHPFrame_DatabaseColumn::setNull()}.
     *                       - default. See
     *                         {@link PHPFrame_DatabaseColumn::setDefault()}.
     *                       - key. See
     *                         {@link PHPFrame_DatabaseColumn::setKey()}.
     *                       - extra. See
     *                         {@link PHPFrame_DatabaseColumn::setExtra()}.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        $this->_reflection_obj = new ReflectionClass($this);

        if (!is_null($options)) {
            foreach ($options as $key=>$value) {
                $setter = "set".ucwords(str_replace("_", " ", $key));
                $setter = str_replace(" ", "", $setter);

                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                }
            }
        }

        if (is_null($this->_name)) {
            $msg  = "Option 'name' is required in ";
            $msg .= get_class($this)."::".__FUNCTION__."().";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Implementation of IteratorAggregate interface.
     *
     * @return ArrayIterator
     * @since  1.0
     */
    public function getIterator()
    {
        $props = get_object_vars($this);
        $array = array();

        foreach ($props as $key=>$value) {
            if ($key == "_reflection_obj") {
                continue;
            }

            $array[substr($key, 1)] = $value;
        }

        return new ArrayIterator($array);
    }

    /**
     * Get column name.
     *
     * @return string
     * @since  1.0
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get column type. See class constants for known types.
     *
     * @return string
     * @since  1.0
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get the column length. Only applies for TYPE_CHAR or TYPE_VARCHAR.
     * The length relates to the maximum number of characters allowed for the
     * field, default is 100.
     *
     * @return int
     * @since 1.0
     */
    public function getLength()
    {
        if (isset($this->_length)) {
            return $this->_length;
        } else {
            return 100;
        }
    }

    /**
     * Get flag indicating whether the column allows NULL values.
     *
     * @return bool
     * @since  1.0
     */
    public function getNull()
    {
        return $this->_null;
    }

    /**
     * Get key type if any. Possible values are "PRI", "UNI" and "MUL". PRI
     * stands for primary key, UNI for UNIQUE and MUL for multiple.
     *
     * @return string|null Either "PRI", "UNI", "MUL" or NULL.
     * @since  1.0
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Get default value.
     *
     * @return string|null
     * @since  1.0
     */
    public function getDefault()
    {
        return $this->_default;
    }

    /**
     * Get "extra" flag. The only supported extra is "auto_increment".
     *
     * @return string|null
     * @since  1.0
     */
    public function getExtra()
    {
        return $this->_extra;
    }

    /**
     * Get enums array.
     *
     * @return array
     * @since  1.0
     */
    public function getEnums()
    {
        return $this->_enums;
    }

    /**
     * Set the column name.
     *
     * @param string $str The column name.
     *
     * @return void
     * @since  1.0
     */
    public function setName($str)
    {
        $this->_name = $str;
    }

    /**
     * Set the column type.
     *
     * @param string $str The column type. For allowed values see class
     *                    constants.
     *
     * @return void
     * @since  1.0
     */
    public function setType($str)
    {
        if (!is_string($str)) {
            $msg  = "Column type must be a string. See ";
            $msg .= get_class($this)." constants";
            throw new InvalidArgumentException($msg);
        }

        $types = array();
        foreach ($this->_reflection_obj->getConstants() as $key=>$value) {
            if (strpos($key, "TYPE_") === 0) {
                $types[] = $value;
            }
        }

        if (!in_array($str, $types)) {
            $msg  = "Wrong column type. See ";
            $msg .= get_class($this)." constants";
            throw new InvalidArgumentException($msg);
        }

        $this->_type = $str;
    }

    /**
     * Sets the length allowed for this column. This only applies if the
     * column type is set to TYPE_CHAR or TYPE_VARCHAR.
     *
     * @param int $int the maximum allowed character length for this column
     *
     * @return void
     * @since  1.0
     */
    public function setLength($int)
    {
        if ($int <= 0){
            $msg = "Invalid column length ('$int').";
            $msg .= "Length must be a positive integer.";
            throw new InvalidArgumentException($msg);
        }
        $this->_length = (int)$int;
    }

    /**
     * Set whether the column allows NULL values or not.
     *
     * @param bool $bool TRUE to allow NULL values and FALSE not to.
     *
     * @return void
     * @since  1.0
     */
    public function setNull($bool)
    {
        $this->_null = (bool) $bool;
    }

    /**
     * Set the column's key type.
     *
     * @param string|null $str Either "PRI", "UNI", "MUL" or NULL.
     *
     * @return void
     * @since  1.0
     */
    public function setKey($str)
    {
        $str = trim((string) $str);

        if (!in_array($str, array("PRI", "UNI", "MUL", null))) {
            $msg  = "Invalid column key ('".$str."'). Allowed keys are ";
            $msg .= "'PRI','UNI','MUL' or NULL";
            throw new InvalidArgumentException($msg);
        }

        $this->_key = $str;
    }

    /**
     * Set the column's default value.
     *
     * @param string $str The default value.
     *
     * @return void
     * @since  1.0
     */
    public function setDefault($str)
    {
        $this->_default = $str;
    }

    /**
     * Set the extra attribute. Only allowed values are NULL and
     * "auto_increment".
     *
     * @param string|null $str Either NULL or "auto_increment".
     *
     * @return void
     * @since  1.0
     */
    public function setExtra($str)
    {
        $str = trim((string) $str);

        if (!in_array($str, array("auto_increment", null))) {
            $msg  = "Invalid column extra attribute ('".$str."'). Allowed ";
            $msg .= "values are 'auto_increment' or NULL";
            throw new InvalidArgumentException($msg);
        }

        $this->_extra = $str;
    }

    /**
     * Set enums. This is only allowed in columns of type 'enum'.
     *
     * @param array $array Array containing enum values.
     *
     * @return void
     * @throws LogicException, InvalidArgumentException
     * @since  1.0
     */
    public function setEnums(array $array)
    {
        $type = $this->getType();
        if ($type != self::TYPE_ENUM) {
            $msg  = get_class($this)."::".__FUNCTION__."() called on a ";
            $msg .= "column object of type '".$type."' and should only be ";
            $msg .= "called when type is set to '".self::TYPE_ENUM."'.";
            throw new LogicException($msg);
        }

        $array_obj = new PHPFrame_Array($array);

        if ($array_obj->isAssoc() || $array_obj->depth() > 1) {
            $msg = "";
            throw new InvalidArgumentException($msg);
        }

        $this->_enums = $array;
    }
}
