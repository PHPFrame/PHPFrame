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
class PHPFrame_RSSDocument extends PHPFrame_XMLDocument
{
    /**
     * The feed title.
     * 
     * @var string
     */
    private $_title;
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
    private $_image;
    /**
     * Array containing the feed items.
     * 
     * @var array
     */
    private $_items;
    
    /**
     * Constructor.
     * 
     * @return void
     * @since 1.0
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->_image = array();
        $this->_items = array();
        
        $this->useBeautifier(false);
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
        $rss         = $this->addNode("rss", null, array("version"=>"2.0"));
        $channel     = $this->addNode("channel", $rss);
        $title       = $this->addNode("title", $channel, null, $this->title());
        $link        = $this->addNode("link", $channel, null, $this->link());
        $description = $this->addNode(
            "description",
            $channel, 
            null, 
            $this->description()
        );
        
        $image_array = $this->image();
        if (count($image_array)> 0) {
            $image     = $this->addNode("image", $channel);
            $image_url = $this->addNode(
                "url",
                $image,  
                null, 
                $image_array["url"]
            );
            $image_link = $this->addNode(
                "link",
                $image,  
                null, 
                $image_array["link"]
            );
        }
        
        if (count($this->items()) > 0) {
            foreach ($this->items() as $item_array) {
                $item = $this->addNode("item", $channel);
                $this->addNode("title", $item, null, $item_array["title"]);
                
                if ($item_array["link"]) {
                    $this->addNode(
                        "link", 
                        $item, 
                        null, 
                        htmlentities($item_array["link"])
                    );
                }
                
                if ($item_array["description"]) {
                    $this->addNode(
                        "description", 
                        $item, 
                        null, 
                        $item_array["description"],
                        false
                    );
                }
                
                
                if (isset($item_array["pub_date"]) 
                    && !empty($item_array["pub_date"])
                ) {
                    $this->addNode(
                        "pubDate", 
                        $item, 
                        null, 
                        $item_array["pub_date"]
                    );
                }
                
                if (isset($item_array["author"]) 
                    && !empty($item_array["author"])
                ) {
                    $this->addNode(
                        "author", 
                        $item, 
                        null, 
                        $item_array["author"]
                    );
                }
            }
        }
        
        return parent::__toString();
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
}
