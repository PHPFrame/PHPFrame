<?php
/**
 * PHPFrame/Documentor/ClassDoc.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Documentor
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @ignore
 */

/**
 * Class Documentor Class
 *
 * @category PHPFrame
 * @package  Documentor
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_ClassDoc extends ReflectionClass
{
    /**
     * Convert object to string.
     *
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str  = "Class: ".$this->getName()."\n";
        for ($i=0; $i<(strlen($this->getName())+7); $i++) {
            $str .= "-";
        }

        if (count($this->getConstants()) > 0) {
            $str .= "\n\n### Constants ###\n\n";
            $str .= implode("\n\n", $this->getConstants());
        }

        $prop_filter  = ReflectionProperty::IS_PUBLIC;
        $prop_filter += ReflectionProperty::IS_PROTECTED;
        if (count($this->getProperties($prop_filter)) > 0) {
            $str .= "\n\n### Properties ###\n\n";
            $str .= implode("\n\n", $this->getProperties());
        }

        if (count($this->getMethods()) > 0) {
            $str .= "\n\n### Methods ###\n\n";
            $str .= implode("\n\n", $this->getMethods());
        }

        return $str;
    }

    /**
     * Get class properties as objects of type PHPFrame_PropertyDoc.
     *
     * @param string $filter [Optional] Any combination of:
     *                       ReflectionProperty::IS_PUBLIC
     *                       ReflectionProperty::IS_PROTECTED
     *                       ReflectionProperty::IS_PRIVATE
     *                       To combine filters simply add them with the "+"
     *                       operator.
     *
     * @return array containing objects of type PHPFrame_PropertyDoc.
     * @since  1.0
     */
    public function getProperties($filter=null)
    {
        $props = array();

        if (!is_null($filter)) {
            $raw = parent::getProperties($filter);
        } else {
            $raw = parent::getProperties();
        }

        foreach ($raw as $prop) {
            $props[] = new PHPFrame_PropertyDoc(
                $this->getName(),
                $prop->getName()
            );
        }

        return $props;
    }

    /**
     * Get class methods as objects of type PHPFrame_MethodDoc.
     *
     * @param string $filter [Optional] Any combination of
     *                       ReflectionMethod::IS_STATIC,
     *                       ReflectionMethod::IS_PUBLIC,
     *                       ReflectionMethod::IS_PROTECTED,
     *                       ReflectionMethod::IS_PRIVATE,
     *                       ReflectionMethod::IS_ABSTRACT,
     *                       ReflectionMethod::IS_FINAL.
     *                       To combine filters simply add them with the "+"
     *                       operator.
     *
     * @return array containing objects of type PHPFrame_MethodDoc.
     * @since  1.0
     */
    public function getMethods($filter=null)
    {
        $methods = array();

        if (!is_null($filter)) {
            $raw = parent::getMethods($filter);
        } else {
            $raw = parent::getMethods();
        }

        foreach ($raw as $method) {
            $methods[] = new PHPFrame_MethodDoc(
                $this->getName(),
                $method->getName()
            );
        }

        return $methods;
    }
}
