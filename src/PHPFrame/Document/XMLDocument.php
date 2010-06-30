<?php
/**
 * PHPFrame/Document/XMLDocument.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Document
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * XML Document Class
 *
 * @category PHPFrame
 * @package  Document
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_XMLDocument extends PHPFrame_Document
{
    /**
     * DOM Document object
     *
     * @var DOMDocument
     */
    private $_dom = null;
    /**
     * A boolean indicating whether or not to use the XML beautifier when
     * converting to string.
     *
     * @var bool
     */
    private $_use_beautifier=true;

    /**
     * Constructor.
     *
     * @param string $mime    [Optional] The document's MIME type. The default
     *                        value is 'text/xml'.
     * @param string $charset [Optional] The document's character set. Default
     *                        value is 'UTF-8'.
     *
     * @return void
     * @uses   DOMImplementation
     * @since  1.0
     */
    public function __construct($mime="text/xml", $charset=null)
    {
        // Call parent's constructor to set mime type
        parent::__construct($mime, $charset);

        // Acquire DOM object of HTML type
        $this->dom(new DOMDocument("1.0", $this->charset()));
    }

    /**
     * Covert object to string.
     *
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        if ($this->useBeautifier()) {
            $xml_beautifier = new XML_Beautifier();
            return $xml_beautifier->formatString($this->dom()->saveXML());
        }

        $this->dom()->formatOutput = true;
        return $this->dom()->saveXML();
    }

    /**
     * Get/set the document body.
     *
     * @param string $str String containing the document body in XML format.
     *
     * @return string
     * @throws InvalidArgumentException if XML string is not valid.
     * @since  1.0
     */
    public function body($str=null)
    {
        if (!is_null($str)) {
            // Acquire a new instance of DOM
            $this->dom(new DOMDocument("1.0", $this->charset()));
            if (!@$this->dom()->loadXML((string) $str)) {
                $msg  = "XML string passed to ".get_class($this)."::";
                $msg .= __FUNCTION__."() is not valid.";
                throw new InvalidArgumentException($msg);
            }
        }

        return (string) $this;
    }

    /**
     * Method not supported. XML documents can only have one root node so
     * appending would not make sense. Use the {@link PHPFrame_XMLDocument::dom()}
     * method to get a reference to the DOMDocument object or use the
     * {@link PHPFrame_XMLDocument::addNode()},
     * {@link PHPFrame_XMLDocument::addNodeAttr()} or
     * {@link PHPFrame_XMLDocument::addNodeContent()} methods.
     *
     * @param string $str String to append the document body.
     *
     * @return void
     * @throws LogicException always.
     * @since  1.0
     */
    public function appendBody($str)
    {
        $msg  = __FUNCTION__."() not supported by ".get_class($this).". XML ";
        $msg .= "documents can only have one root node so appending would ";
        $msg .= "not make sense. Use the dom() method to get a reference to ";
        $msg .= "the DOMDocument object or use the addNode() method.";
        throw new LogicException($msg);
    }

    /**
     * Method not supported. XML documents can only have one root node so
     * prepending would not make sense. Use the {@link PHPFrame_XMLDocument::dom()}
     * method to get a reference to the DOMDocument object or use the
     * {@link PHPFrame_XMLDocument::addNode()},
     * {@link PHPFrame_XMLDocument::addNodeAttr()} or
     * {@link PHPFrame_XMLDocument::addNodeContent()} methods.
     *
     * @param string $str String to prepend the document body.
     *
     * @return void
     * @throws LogicException always.
     * @since  1.0
     */
    public function prependBody($str)
    {
        $msg  = __FUNCTION__."() not supported by ".get_class($this).". XML ";
        $msg .= "documents can only have one root node so prepending would ";
        $msg .= "not make sense. Use the dom() method to get a reference to ";
        $msg .= "the DOMDocument object or use the addNode() method.";
        throw new LogicException($msg);
    }

    /**
     * Set whether or not to use the XML beautifier when converting to string.
     *
     * @param bool $bool [Optional] Boolean indicating whether or not to use
     *                   the beautifier. If not passed this method simply
     *                   returns the current value.
     *
     * @return bool
     * @since  1.0
     */
    public function useBeautifier($bool=null)
    {
        if (!is_null($bool)) {
            $this->_use_beautifier = (bool) $bool;
        }

        return $this->_use_beautifier;
    }

    /**
     * Get reference to DOM object.
     *
     * @param DOMDocument $dom [Optional]
     *
     * @return DOMDocument
     * @since  1.0
     */
    public function dom(DOMDocument $dom=null)
    {
        if (!is_null($dom)) {
            $this->_dom = $dom;
        }

        return $this->_dom;
    }

    /**
     * Add node/tag.
     *
     * @param string  $name        The name of the new node or tag.
     * @param DOMNode $parent      [Optional] The parent object to which we
     *                             want to add the new node.
     * @param array   $attrs       [Optional] An assoc array containing
     *                             attributes key/value pairs.
     * @param string  $content     [Optional] Text content of the node.
     * @param bool    $xml_content [Optional] Default value is TRUE. If set to
     *                             FALSE the passed content will be treated as
     *                             text and XML entities will be replaced.
     *
     * @return DOMNode Returns a reference to the newly created node.
     * @since  1.0
     */
    public function addNode(
        $name,
        DOMNode $parent=null,
        array $attrs=null,
        $content=null,
        $xml_content=true
    ) {
        $new_node = $this->dom()->createElement($name);

        if ($parent instanceof DOMNode) {
            $parent->appendChild($new_node);
        } else {
            $this->dom()->appendChild($new_node);
        }

        // Add attributes if any
        if (is_array($attrs) && count($attrs) > 0) {
            foreach ($attrs as $key=>$value) {
                $this->addNodeAttr($new_node, $key, $value);
            }
        }

        // Add text content if any
        if (!is_null($content)) {
            $this->addNodeContent($new_node, $content, $xml_content);
        }

        return $new_node;
    }

    /**
     * Add an attribute to a given node.
     *
     * @param DOMNode $node       The node we want to add the attributes to.
     * @param string  $attr_name  The attribute name.
     * @param string  $attr_value The value for the attribute if any.
     *
     * @return void
     * @since  1.0
     */
    public function addNodeAttr(DOMNode $node, $attr_name, $attr_value)
    {
        // Create attribute
        $attr = $this->dom()->createAttribute($attr_name);

        // Add attribute value
        $value = $this->dom()->createTextNode($attr_value);
        $attr->appendChild($value);

        // Append attribute to node
        $node->appendChild($attr);
    }

    /**
     * Add content to given node.
     *
     * @param DOMNode $node The node where to add the content text.
     * @param string  $str  The text to add to the node
     * @param bool    $xml  [Optional] Default value is TRUE. If set to FALSE
     *                      the passed srting will be treated as text content
     *                      and XML entities will be replaced.
     *
     * @return void
     * @since  1.0
     */
    public function addNodeContent(DOMNode $node, $str, $xml=true)
    {
        if ($xml) {
            // Create XML fragment object
            $fragment = $this->dom()->createDocumentFragment();

            // Try to append the XML fragment, catch error and throw exception
            // if it fails
            if (@$fragment->appendXML($str) === false) {
                $msg  = "Invalid xml string passed to ".get_class($this);
                $msg .= "::".__FUNCTION__."()";
                throw new RuntimeException($msg);
            }

            $node->appendChild($fragment);

        } else {
            $text_node = $this->dom()->createTextNode($str);
            $node->appendChild($text_node);
        }
    }
}
