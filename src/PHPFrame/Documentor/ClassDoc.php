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

        if (count($this->getProperties(ReflectionProperty::IS_PUBLIC)) > 0) {
            $str .= "\n\n### Properties ###\n\n";
            $str .= implode("\n\n", $this->getProperties());
        }

        if (count($this->getMethods()) > 0) {
            $str .= "\n\n### Methods ###\n\n";
            $str .= implode("\n\n", $this->getMethods());
        }

        return $str;
    }

    public function getProperties($filter=null)
    {
        $props = array();

        foreach (parent::getProperties($filter) as $prop) {
            $props[] = new PHPFrame_PropertyDoc(
                $this->getName(),
                $prop->getName()
            );
        }

        return $props;
    }

    public function getMethods()
    {
        $methods = array();

        foreach (parent::getMethods() as $method) {
            $methods[] = new PHPFrame_MethodDoc(
                $this->getName(),
                $method->getName()
            );
        }

        return $methods;
    }
}
