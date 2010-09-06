<?php
/**
 * PHPFrame/Document/RSSDocument.php
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
 * RSS Document Class
 *
 * @category PHPFrame
 * @package  Document
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Document
 * @since    1.0
 */
class PHPFrame_RSSDocument extends PHPFrame_Document
{
    /**
     * The feed's link.
     *
     * @var string
     */
    private $_link;
    /**
     * The feed's description.
     *
     * @var string
     */
    private $_description;
    /**
     * The feed's image. This is an array with two keys:
     * - url
     * - link
     *
     * @var array
     */
    private $_image = array();
    /**
     * Array containing the feed items.
     *
     * @var array
     */
    private $_items = array();

    /**
     * Constructor.
     *
     * @return void
     * @since 1.0
     */
    public function __construct()
    {
        parent::__construct("application/rss+xml");
    }

    /**
     * Convert object to string.
     *
     * @return void
     * @since 1.0
     * @see PHPFrame/Document/PHPFrame_XMLDocument#__toString()
     */
    public function __toString()
    {
        $xml = new SimpleXMLElement("<rss version=\"2.0\"></rss>");
        $channel = $xml->addChild("channel");
        $channel->addChild("title", $this->title());
        $channel->addChild("link", $this->link());
        $channel->addChild("description", $this->description());

        $image_array = $this->image();
        if (count($image_array) > 0) {
            $image = $channel->addChild("image");
            $image->addChild("url", $image_array["url"]);
            $image->addChild("link", $image_array["link"]);
        }

        if (count($this->items()) > 0) {
            foreach ($this->items() as $item_array) {
                $item = $channel->addChild("item");
                $item->addChild("title", $item_array["title"]);

                if ($item_array["link"]) {
                    $item->addChild("link", $item_array["link"]);
                }

                if ($item_array["description"]) {
                    $item->addChild("description", $item_array["description"]);
                }

                if (isset($item_array["pub_date"])
                    && !empty($item_array["pub_date"])
                ) {
                    $item->addChild("pubDate", $item_array["pub_date"]);
                }

                if (isset($item_array["author"])
                    && !empty($item_array["author"])
                ) {
                    $item->addChild("author", $item_array["author"]);
                }
            }
        }

        return $xml->asXML();
    }

    /**
     * Get/set feed's link.
     *
     * @param string $str [Optional] URL to the feed title will point to.
     *
     * @return string
     * @since  1.0
     */
    public function link($str=null)
    {
        if (!is_null($str)) {
            $this->_link = (string) $str;
        }

        return $this->_link;
    }

    /**
     * Get/set the feed's description.
     *
     * @param string $str [Optional] The feed's description.
     *
     * @return string
     * @since  1.0
     */
    public function description($str=null)
    {
        if (!is_null($str)) {
            $this->_description = (string) $str;
        }

        return $this->_description;
    }

    /**
     * Get/set image.
     *
     * @param string $url  [Optional] URL to the image file.
     * @param string $link [Optional] Link the image should point to.
     *
     * @return array with image url and link.
     * @since  1.0
     */
    public function image($url=null, $link=null)
    {
        if (!is_null($url)) {
            if (is_null($link)) {
                $msg = "Image URL specified but no link passed.";
                throw new InvalidArgumentException($msg);
            }

            $this->_image = array(
                "url"  => (string) $url,
                "link" => (string) $link
            );
        }

        return $this->_image;
    }

    /**
     * Get/set feed items array.
     *
     * @param array $array [Optional] An array containig the feed items (see
     *                     {@link PHPFrame_RSSDocument::addItem()}).
     *
     * @return array
     * @since  1.0
     */
    public function items(array $array=null)
    {
        if (!is_null($array)) {
            $this->_items = $array;
        }

        return $this->_items;
    }

    /**
     * Add a feed item.
     *
     * @param string $title       The post title.
     * @param string $link        URL to the post.
     * @param string $description The post body or excerpt.
     * @param string $pub_date    Publish date.
     * @param string $author      The post author.
     *
     * @return void
     * @since  1.0
     */
    public function addItem(
        $title,
        $link,
        $description,
        $pub_date=null,
        $author=null
    ) {
        $this->_items[] = array(
            "title"       => (string) $title,
            "link"        => (string) $link,
            "description" => (string) $description,
            "pub_date"    => (string) $pub_date,
            "author"      => (string) $author
        );
    }

    /**
     * Load feed data from XML string in either RSS 2.0 or Atom formats.
     *
     * @param string $str The XML string.
     *
     * @return void
     * @since  1.0
     */
    public function loadXML($str)
    {
        $xml = new SimpleXMLElement($str);

        if ($xml->getName() == "feed") {
            $this->_parseAtom($xml);
        } else {
            $this->_parseRSS2($xml);
        }
    }

    /**
     * Parse Atom feed data from XML.
     *
     * @param SimpleXMLElement $xml Instance of SimpleXMLElement.
     *
     * @return void
     * @since  1.0
     */
    private function _parseAtom(SimpleXMLElement $xml)
    {
        foreach ($xml as $node) {
            switch ($node->getName()) {
            case "title" :
                $this->title((string) $node);
                break;

            case "link" :
                $is_self_link = false;
                $href         = "";

                foreach ($node->attributes() as $key=>$value) {
                    if ($key == "rel" && $value == "self") {
                        $is_self_link = true;
                    } elseif ($key == "href") {
                        $href = $value;
                    }
                }

                if ($is_self_link && $href) {
                    $this->link($href);
                }

                break;

            case "updated" :
                break;

            case "entry" :
                $title       = "";
                $link        = "";
                $description = "";
                $pub_date    = "";
                $author      = "";

                foreach ($node as $entry_node) {
                    switch ($entry_node->getName()) {
                    case "title" :
                        $title = (string) $entry_node;
                        break;
                    case "link" :
                        $href = "";
                        foreach ($entry_node->attributes() as $key=>$value) {
                            if ($key == "href") {
                                $link = $value;
                            }
                        }
                        break;
                    case "content" :
                        $description = (string) $entry_node;
                        break;
                    case "updated" :
                        $pub_date = (string) $entry_node;
                        break;
                    case "author" :
                        $author = (string) $entry_node->name;
                        break;
                    }
                }

                $this->addItem(
                    $title,
                    $link,
                    $description,
                    $pub_date,
                    $author
                );

                break;
            }
        }
    }

    /**
     * Parse RSS 2 feed data from XML.
     *
     * @param SimpleXMLElement $xml Instance of SimpleXMLElement.
     *
     * @return void
     * @since  1.0
     */
    private function _parseRSS2(SimpleXMLElement $xml)
    {
        $this->title($xml->channel->title);
        $this->link($xml->channel->link);
        $this->description($xml->channel->description);

        if (array_key_exists("a10", $xml->getDocNamespaces())) {
            $atom_ns = true;
        } else {
            $atom_ns = false;
        }

        if (count($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                if ($atom_ns) {
                    $a10_updated = @$item->xpath("a10:updated");
                    $pub_date = $a10_updated[0];
                } else {
                    $pub_date = $item->pubDate;
                }

                $this->addItem(
                    $item->title,
                    $item->link,
                    $item->description,
                    $pub_date,
                    $item->author
                );
            }
        }
    }
}
