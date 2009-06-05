<?php
/**
 * @version		$Id$
 * @package		PHPFrame
 * @subpackage 	database
 * @copyright	Copyright (C) 2009 E-noise.com Limited. All rights reserved.
 * @license		BSD revised. See LICENSE.
 */

/**
 * Collection Filter Class
 * 
 * Collection filter objects are responsible for encapsulating SQL select filtering 
 * conditions (ORDER BY and LIMIT clauses).
 * 
 * "Collection filter" objects are used when creating "row collections".
 * 
 * This objects are also used by the pagination objects, as they will need to work 
 * with the "limit" properties (limit, limitstart, pages, current pages and so on).
 * 
 * Collection filter objects are normally instantiated using values coming 
 * from the request, so the natural place to instantiate them is in concrete "action 
 * controller" classes. Action controllers are responsible for brokering request data.
 * They use this data to create a list or collection filter that will be 
 * passed to a model method responsible for creating a "row collection".
 * 
 *  Example:
 *  
 *  <code>
 *  class usersController extends PHPFrame_Application_ActionController {
 *  // ...
 *  
 *  	public function get_users() {
 *  		// Get request data
 *  		$orderby = PHPFrame::getRequest()->get('orderby', 'u.lastname');
 *  		$orderdir = PHPFrame::getRequest()->get('orderdir', 'ASC');
 *  		$limit = PHPFrame::getRequest()->get('limit', 25);
 *  		$limitstart = PHPFrame::getRequest()->get('limitstart', 0);
 *  		$search = PHPFrame::getRequest()->get('search', '');
 *  
 *  		// Create list filter needed for getUsers()
 *  		$list_filter = new PHPFrame_Database_CollectionFilter($orderby, $orderdir, $limit, $limitstart, $search);
 *  		
 *  		// Get users using model
 *  		$users = $this->getModel('users')->getUsers($list_filter);
 *  
 *  		// Get view
 *  		$view = $this->getView('users', 'list');
 *  		// Set view data
 *  		$view->addData('rows', $users);
 *  		$view->addData('page_nav', new PHPFrame_HTML_Pagination($list_filter));
 *  		// Display view
 *  		$view->display();
 *  	}
 *  }
 *  </code>
 * 
 * @package		PHPFrame
 * @subpackage 	database
 * @see			PHPFrame_Database_RowCollection, PHPFrame_HTML_Pagination
 * @since 		1.0
 */
class PHPFrame_Database_CollectionFilter {
	/**
	 * Column to use for ordering
	 * 
	 * @var string
	 */
	private $_orderby=null;
	/**
	 * Order direction (either ASC or DESC)
	 * 
	 * @var string
	 */
	private $_orderdir=null;
	/**
	 * Number of rows per page
	 * 
	 * @var int
	 */
	private $_limit=null;
	/**
	 * Row number to start current page
	 * 
	 * @var int
	 */
	private $_limitstart=null;
	/**
	 * Search string
	 * 
	 * @var string
	 */
	private $_search=null;
	/**
	 * Total number of rows (in all pages)
	 * 
	 * @var int
	 */
	private $_total=null;
	
	/**
	 * Constructor
	 * 
	 * @param	string	$_orderby		Column to use for ordering.
	 * @param	string	$_orderdir		Order direction (either ASC or DESC).
	 * @param	int		$_limit			Number of rows per page.
	 * @param	int		$_limitstart	Row number to start current page.
	 * @param	string	$_search		Search string.
	 * @return	void
	 * @since 	1.0
	 */
	public function __construct($_orderby="", $_orderdir="ASC", $_limit=-1, $_limitstart=0, $_search="") {
		$this->_orderby = (string) $_orderby;
		$this->_orderdir = (string) $_orderdir;
		$this->_limit = (int) $_limit;
		$this->_limitstart = (int) $_limitstart;
		$this->_search = (string) $_search;
	}
	
	/**
	 * Set total number of records for the subset
	 * 
	 * @param	int		Total number of records in all pages.
	 * @return	void
	 * @since 	1.0
	 */
	public function setTotal($int) {
		$this->_total = (int) $int;
	}
	
	/**
	 * Get search string
	 * 
	 * @return	string
	 * @since 	1.0
	 */
	public function getSearchStr() {
		return $this->_search;
	}
	
	/**
	 * Get order by column name
	 * 
	 * @return	string
	 * @since 	1.0
	 */
	public function getOrderBy() {
		return $this->_orderby;
	}
	
	/**
	 * Get order direction
	 * 
	 * @return	string	Either ASC or DESC
	 * @since 	1.0
	 */
	public function getOrderDir() {
		return $this->_orderdir;
	}
	
	/**
	 * Get ORDER BY SQL statement
	 * 
	 * @return	string
	 * @since 	1.0
	 */
	public function getOrderByStmt() {
		$stmt = "";
		
		if (is_string($this->_orderby) && $this->_orderby != "") {
			$stmt .= " ORDER BY ".$this->_orderby." ";
			$stmt .= ($this->_orderdir == "DESC") ? $this->_orderdir : "ASC";
		}
		
		return $stmt;
	}
	
	/**
	 * Get limit
	 * 
	 * @return	int
	 * @since 	1.0
	 */
	public function getLimit() {
		return $this->_limit;
	}
	
	/**
	 * Get Limit start position
	 * 
	 * @return	int
	 * @since 	1.0
	 */
	public function getLimitStart() {
		return $this->_limitstart;
	}
	
	/**
	 * Get LIMIT SQL statement
	 * 
	 * @return	string
	 * @since 	1.0
	 */
	public function getLimitStmt() {
		$stmt = "";
		
		if ($this->_limit > 0) {
			$stmt .= " LIMIT ".$this->_limitstart.", ".$this->_limit;
		}
		
		return $stmt;
	}
	
	/**
	 * Get number of pages
	 * 
	 * @return int
	 * @since 	1.0
	 */
	public function getPages() {
		if ($this->_limit > 0 && !is_null($this->_total)) {
			// Calculate number of pages
			return (int) ceil($this->_total/$this->_limit);
		}
		else {
			return 0;
		}
	}
	
	/**
	 * Get current page number
	 * 
	 * @return int
	 * @since 	1.0
	 */
	public function getCurrentPage() {
		// Calculate current page
		if ($this->_limit > 0) {
			return (int) (ceil($this->_limitstart/$this->_limit)+1);
		}
		else {
			return 0;
		}
	}
}
