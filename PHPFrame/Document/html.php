<?php
/**
 * @version		$Id$
 * @package		PHPFrame
 * @subpackage 	document
 * @copyright	Copyright (C) 2009 E-noise.com Limited. All rights reserved.
 * @license		BSD revised. See LICENSE.
 */

/**
 * HTML Document Class
 * 
 * @package		PHPFrame
 * @subpackage 	document
 * @since 		1.0
 */
class PHPFrame_Document_HTML extends PHPFrame_Document {
	/**
	 * Array of linked scripts
	 *
	 * @var		array
	 * @access	private
	 */
	var $_scripts_linked = array();

	/**
	 * Array of scripts placed in the header
	 *
	 * @var		array
	 * @access	private
	 */
	var $_scripts_inline = array();

	 /**
	 * Array of linked style sheets
	 *
	 * @var		array
	 * @access	private
	 */
	var $_styles_linked = array();

	/**
	 * Array of included style declarations
	 *
	 * @var		array
	 * @access	private
	 */
	var $_styles_inline = array();

	/**
	 * Array of meta tags
	 *
	 * @var		array
	 * @access	private
	 */
	var $_metaTags = array();

	/**
	 * The rendering engine
	 *
	 * @var		object
	 * @access	private
	 */
	var $_engine = null;

	/**
	 * The document type
	 *
	 * @var		string
	 * @access	private
	 */
	var $_type = null;
	
	/**
	 * Array for different tag types to be printed in <head></head>
	 *
	 * @var		string
	 * @access	private
	 */
	var $_tagTypes = array("_metaTags","_scripts_linked","_styles_linked");
	
	/**
	 * Constructor
	 *
	 * @return	void
	 * @access	public
	 * @since	0.1 
	 */
	function __construct(){
		parent::__construct();
		$this->_type = $this->_mime."; charset=".$this->_charset;
	}
	
	/**
	 * Add meta tag
	 * 
	 * @param	string	$name
	 * @param	string	$content
	 * @return	void
	 * @since 1.0
	 */
	function addMetaTag($name, $content) {
		$this->_metaTags[] = '<meta name="'.$name.'" content="'.$content.'" />';
	}
	
	/**
	 * Add linked scrip in document head
	 * 
	 * It takes both relative and absolute values.
	 * 
	 * @param	string	$src	The relative or absolute URL to the script source.
	 * @param	string	$type	The script type. Default is text/javascript.
	 * @return	void
	 * @since	1.0
	 */
	function addScript($src, $type='text/javascript') {
		// Make source absolute URL
		$this->_makeAbsolute($src);
		
		// Build HTML <script> Tag
		$script_tag = '<script type="'.$type.'" src="'.$src.'"></script>';
		
		// Add to linked scripts array if not in array already
		if (!in_array($script_tag, $this->_scripts_linked)) {
			$this->_scripts_linked[] = $script_tag;	
		}
	}
	
	/**
	 * Attach external stylesheet
	 * 
	 * @param	string	$href	The relative or absolute URL to the link source.
	 * @param	string	$type	The link type. Default is text/css.
	 * @return	void
	 * @since 	1.0
	 */
	function addStyleSheet($href, $type='text/css') {
		// Make source absolute URL
		$this->_makeAbsolute($href);
		
		// Build HTML <script> Tag
		$link_tag = '<link rel="stylesheet" href="'.$href.'" type="'.$type.'" />';
		
		// Add to linked scripts array if not in array already
		if (!in_array($link_tag, $this->_styles_linked)) {
			$this->_styles_linked[] = $link_tag;	
		}
	}
	
	/**
	 * Print the head tags
	 * 
	 * @return	void
	 * @since	1.0
	 */
	function printHead() {
		
		// add meta tags
		$this->_metaTags[] = '<meta name="generator" content="Extranet Office" />';
		$this->_metaTags[] = '<meta http-equiv="Content-Type" content="'.$this->_type.'" />';
		
		// print base url
		echo '<base href="'.$this->base.'" />'.$this->_lineEnd;
		
		// For each tag type
		foreach($this->_tagTypes as $tagType){
			if (is_array($this->$tagType) && count($this->$tagType) > 0) {
				echo implode($this->_lineEnd, $this->$tagType).$this->_lineEnd;
			}
		}
	}
	
	/**
	 * Make path absolute
	 * 
	 * @param	string	$path
	 * @return	void
	 * @since	1.0
	 */
	private function _makeAbsolute(&$path) {
		// Add the document base if a relative path
		if (substr($path, 0, 4) != 'http') {
			$path = $this->base.$path;
		}
	}
}
?>