<?php
/**
 * PHPFrame/Document/RSSDocument.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Document
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * RSS Document Class
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
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
        $link        = $this->addNode("link", $channel, null, $this->getLink());
        $description = $this->addNode(
            "description",
            $channel, 
            null, 
            $this->getDescription()
        );
        
        $image_array = $this->getImage();
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
        
        if (count($this->getItems()) > 0) {
            foreach ($this->getItems() as $item_array) {
                $item = $this->addNode("item", $channel);
                $this->addNode("title", $item, null, $item_array["title"]);
                $this->addNode("link", $item, null, $item_array["link"]);
                $this->addNode(
                    "description", 
                    $item, 
                    null, 
                    $item_array["description"]
                );
                
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
     * Set feed's link.
     * 
     * @param string $str URL to the feed title will point to.
     * 
     * @return void
     * @since  1.0
     */
    public function setLink($str)
    {
        $this->_link = (string) $str;
    }
    
    /**
     * Get feed's link.
     * 
     * @return string
     * @since  1.0
     */
    public function getLink()
    {
        return $this->_link;
    }
    
    /**
     * Set the feed's description.
     * 
     * @param string $str The feed's description.
     * 
     * @return void
     * @since  1.0
     */
    public function setDescription($str)
    {
        $this->_description = (string) $str;
    }
    
    /**
     * Get the feed's description.
     * 
     * @return void
     * @since  1.0
     */
    public function getDescription()
    {
        return $this->_description;
    }
    
    /**
     * Set image.
     * 
     * @param string $url  URL to the image file.
     * @param string $link Link the image should point to.
     * 
     * @return void
     * @since  1.0
     */
    public function setImage($url, $link)
    {
        $this->_image = array("url"=>$url, "link"=>$link);
    }
    
    /**
     * Get array with image url and link.
     * 
     * @return array
     * @since  1.0
     */
    public function getImage()
    {
        return $this->_image;
    }
    
    /**
     * Set feed items array.
     * 
     * @param array $array An array containig the feed items (see 
     * {@link PHPFrame_RSSDocument::addItem()}).
     * 
     * @return void
     * @since  1.0
     */
    public function setItems(array $array)
    {
        $this->_items = $array;
    }
    
    /**
     * Get post items.
     * 
     * @return array
     * @since  1.0
     */
    public function getItems()
    {
        return $this->_items;
    }
    
    /**
     * Add a feed item.
     * 
     * @param string $title       The post title.
     * @param string $link        URL to the post.
     * @param string $description The post body or excerpt.
     * @param string $pub_date    Publish date.
     * @param string $author      The post author
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
            "title"       => $title, 
            "link"        => $link, 
            "description" => $description,
            "pub_date"    => $pub_date,
            "author"      => $author
        );
    }
}
