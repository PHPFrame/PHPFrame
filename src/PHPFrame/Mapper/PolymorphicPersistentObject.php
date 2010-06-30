<?php
/**
 * PHPFrame/Mapper/PolymorphicPersistentObject.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Mapper
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Specialised persistent object to handle families of objects that share the
 * same persistent object parent.
 *
 * @category PHPFrame
 * @package  Mapper
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
abstract class PHPFrame_PolymorphicPersistentObject
    extends PHPFrame_PersistentObject
{
    /**
     * Constructor
     *
     * @param array $options [Optional]
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        $this->addField(
            "type",
            get_class($this),
            false,
            new PHPFrame_StringFilter(array("min_length"=>6, "max_length"=>100))
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
     * Implementation of the IteratorAggregate interface. We override the
     * parent's getIterator() method to serialise the subtype parameters to
     * a string.
     *
     * @return ArrayIterator
     * @since  1.0
     */
    public function getIterator()
    {
        $it    = parent::getIterator();
        $array = array();

        foreach ($it as $key=>$value) {
            if ($key == "params") {
                if (is_array($value) && count($value) > 0) {
                    $array[$key] = serialize($value);
                } else {
                    $array[$key] = null;
                }
            } else {
                $array[$key] = $value;
            }
        }

        return new ArrayIterator($array);
    }

    /**
     * Get/set subtype parameters.
     *
     * @param string|array $mixed [Optional] The params array. It can be passed
     *                            as a serialised array.
     *
     * @return array
     * @throws InvalidArgumentException
     * @since  1.0
     */
    public function params($mixed=null)
    {
        if (!is_null($mixed)) {
            if (is_string($mixed)) {
                $mixed = @unserialize($mixed);
            }

            if (!is_array($mixed)) {
                $msg = "Params array is not valid.";
                throw new InvalidArgumentException($msg);
            }

            $params     = array();
            $param_keys = $this->getParamKeys();

            foreach ($param_keys as $key=>$value) {
                if (array_key_exists($key, $mixed)) {
                    $this->param($key, $mixed[$key]);
                } else {
                    if (!isset($this->fields["params"][$key])) {
                        $this->param($key, $value["def_value"]);
                    }
                }
            }
        }

        return $this->fields["params"];
    }

    /**
     * Get/set subtype parameter.
     *
     * @param string $key   The parameter key we want to set.
     * @param mixed  $value [Optional] The value to store in the given key.
     *
     * @return mixed
     * @throws InvalidArgumentException
     * @since  1.0
     */
    public function param($key, $value=null)
    {
        $param_keys = $this->getParamKeys();

        if (!is_null($value)) {
            if (!array_key_exists($key, $param_keys)) {
                $msg = "Unknown parameter.";
                throw new InvalidArgumentException($msg);
            }

            $filter      = $param_keys[$key]["filter"];
            $param_value = $filter->process($value);

            $this->fields["params"][$key] = $param_value;
        }

        if (!array_key_exists($key, $param_keys)) {
            $msg = "Unknown parameter.";
            throw new InvalidArgumentException($msg);
        }

        if (!is_array($this->fields["params"])
            || !array_key_exists($key, $this->fields["params"])
        ) {
            return null;
        }

        return $this->fields["params"][$key];
    }

    /**
     * Get array containing subtype parameter definition. This should return
     * an array containing associative arrays describing each parameter.
     * Parameters are defined using associative arrays with the following
     * structure:
     *
     * "myParam" => array(
     *       "def_value"  => null,
     *       "allow_null" => false,
     *       "filter"     => new PHPFrame_StringFilter()
     *   )
     *
     * @return array
     * @since  1.0
     */
    public function getParamKeys()
    {
        return array();
    }
}
