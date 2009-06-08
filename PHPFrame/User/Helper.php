<?php
/**
 * @version 	$Id$
 * @package		PHPFrame
 * @subpackage 	user
 * @copyright	Copyright (C) 2009 E-noise.com Limited. All rights reserved.
 * @license		BSD revised. See LICENSE.
 */

/**
 * Users Helper Class
 *
 * @package		PHPFrame
 * @subpackage 	user
 * @since 		1.0
 */
class PHPFrame_User_Helper 
{
	/**
	 * Format full name to standard
	 *
	 * @param string $firstname
	 * @param string $lastname
	 * @return string full name in format: [Uppercase first initial]"." [Surname]  
	 */
	static function fullname_format($firstname, $lastname) 
	{
		
		$str = strtoupper(substr($firstname,0,1)).". ".ucwords($lastname);
				
		return $str;
	}
	
	/**
	 * Translate userid to username
	 * 
	 * @param 	int		The ID to be translated
	 * @return 	string	If no id is passed returns false, otherwise returns the username as a string
	 */
	static function id2name($id=0) 
	{
		if (!empty($id)) { // No user has been selected
			$db = PHPFrame::getDB();
			$query = "SELECT firstname, lastname FROM #__users WHERE id = '".$id."'";
			$db->setQuery($query);
			$row = $db->loadObject();
			if ($row === false) {
				return false;
			}
			
			return PHPFrame_User_Helper::fullname_format($row->firstname, $row->lastname);
		}
		else {
			return false;
		}
	}
	
	/**
	 * Translate username to userid
	 * 
	 * @param 	string	The username to be translated.
	 * @return 	int		If no username is passed returns false, otherwise returns the user ID.
	 */
	static function username2id($username='') 
	{
		if (!empty($username)) { // No user has been selected
			$db = PHPFrame::getDB();
			$query = "SELECT id FROM #__users WHERE username = '".$username."'";
			$db -> setQuery($query);
			return $db->loadResult();
		}
		else {
			return false;
		}
	}
	
	/**
	 * Translate email to userid
	 * 
	 * @param 	string	The email to be translated.
	 * @return 	mixed	If no email is passed returns FALSE, otherwise returns the user ID.
	 */
	static function email2id($email='') 
	{
		if (!empty($email)) { // No user has been selected
			$db = PHPFrame::getDB();
			$query = "SELECT id FROM #__users WHERE email = '".$email."'";
			$db -> setQuery($query);
			return $db->loadResult();
		}
		else {
			return false;
		}
	}
	
	/**
	 * Translate id to email
	 * 
	 * @param $id The userid to be translated.
	 * @return mixed A string with the email address or FALSE on fail.
	 */
	static function id2email($id) 
	{
		if (!empty($id)) { // No user has been selected
			$db = PHPFrame::getDB();
			$query = "SELECT email FROM #__users WHERE id = '".$id."'";
			$db -> setQuery($query);
			return $db->loadResult();
		}
		else {
			return false;
		}
	}
	
	/**
	 * @todo	This method needs to be rewritten when we add custom user fields
	 * 
	 * @param	int	$id
	 * @return string
	 */
	static function id2photo($id) 
	{
		if (!empty($id)) { // No user has been selected
			$db = PHPFrame::getDB();
			$query = "SELECT photo FROM #__users WHERE id = '".$id."'";
			$db -> setQuery($query);
			$photo = $db->loadResult();
			if (empty($photo)) { $photo = 'default.png'; }
			return $photo;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Function to build HTML select of users
	 * 
	 * @param	int		The selected value if any
	 * @param 	string	Attributes for the <select> tag
	 * @return 	string	A string with the HTML select
	 */
	static function select($selected=0, $attribs='', $fieldname='userid', $projectid=0) 
	{
		// assemble users to the array
		$options = array();
		$options[] = PHPFrame_HTML::_('select.option', '0', PHPFrame_HTML_Text::_( '-- Select a User --' ) );
		
		// get users from #__users
		$db = PHPFrame::getDB();
		$query = "SELECT u.id, u.firstname, u.lastname ";
		$query .= " FROM #__users AS u ";
		if (!empty($projectid)) {
			$query .= " LEFT JOIN #__users_roles ur ON ur.userid = u.id ";
			$query .= " WHERE ur.projectid = ".$projectid;
		}
		else {
			$query .= " WHERE 0=0";
		}
		$query .= " AND (u.deleted = '0000-00-00 00:00:00' OR u.deleted IS NULL)";
		$query .= " ORDER BY u.lastname";
		//echo $query; exit;
		$db -> setQuery($query);
		if (!$rows = $db->loadObjectList()) {
		  return false;
		}
		
		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {
				$options[] = PHPFrame_HTML::_('select.option', $row->id, PHPFrame_User_Helper::fullname_format($row->firstname, $row->lastname));
			}
		}
		
		$output = PHPFrame_HTML::_('select.genericlist', $options, $fieldname, $attribs, $selected);
		return $output;		
	}
	
	/**
	 * Build checkboxes with users to pick assignees.
	 * 
	 * @param	mixed	$selected	Either a single userid or an array of ids.
	 * @param	string	$attribs	A string with attributes to be printed in the input tags.
	 * @param	string	$fieldname	String tu use for the input tags name attribute.
	 * @param	int		$projectid	This parameter is optional. If passed users will be filtered to the project members.
	 * @return	string	A string with the html code containing the checkboxes.
	 */
	static function assignees($selected=0, $attribs='', $fieldname='assignees[]', $projectid=0) 
	{
		$db = PHPFrame::getDB();
		$query = "SELECT u.id, u.firstname, u.lastname ";
		$query .= " FROM #__users AS u ";
		if (!empty($projectid)) {
			$query .= " LEFT JOIN #__users_roles ur ON ur.userid = u.id ";
			$query .= " WHERE ur.projectid = ".$projectid;
		}
		else {
			$query .= " WHERE 0=0";
		}
		$query .= " AND (u.deleted = '0000-00-00 00:00:00' OR u.deleted IS NULL)";
		$query .= " ORDER BY u.lastname";
		//echo $query; exit;
		$db -> setQuery($query);
		if (!$rows = $db->loadObjectList()) {
		  return false;
		}
		
		// organise assignees in array for checking selected users
		$assignees = array();
		if (is_array($selected)) {
			foreach ($selected as $assignee) {
				$assignees[] = $assignee['id'];
			}
		}
		elseif (!empty($selected)) {
			$assignees[] = $selected;
		}

		$output = '';
		for ($i=0; $i<count($rows); $i++) {
			$output .= '<input type="checkbox" name="'.$fieldname.'" ';
			$output .= ' value="'.$rows[$i]->id.'" '.$attribs;
			if (in_array($rows[$i]->id, $assignees)) { $output .= 'checked'; }
			$output .= ' /> ';
			$output .= PHPFrame_User_Helper::fullname_format($rows[$i]->firstname, $rows[$i]->lastname).'&nbsp;&nbsp;';
			// Add line break every three entries (test using modulus)
			if ((($i+1) % 3) == 0) { $output .= '<br />'; }
		}
		
		return $output;		
	}
	
	/**
	 * Build and display an input tag with username autocompleter
	 * 
	 * @param	array	$where		An array with conditions to include in SQL query.
	 * @return	void
	 */
	static function autocompleteUsername($where=array()) 
	{
		$db = PHPFrame::getDB();
		
		$where[] = "(u.deleted = '0000-00-00 00:00:00' OR u.deleted IS NULL)";
		
		$query = "SELECT id, username, firstname, lastname FROM #__users ";
		$query .= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
		$query .= " ORDER BY username";
		$db -> setQuery($query);
		if (!$rows = $db->loadObjectList()) {
		  return false;
		}
		
		// Organise rows into array of arrays instead of array of objects
		foreach ($rows as $row) {
			$tokens[] = array('id' => $row->id, 'name' => $row->firstname." ".$row->lastname." (".$row->username.")");
		}
		
		PHPFrame_HTML::autocomplete('userids', 'cols="60" rows="2"', $tokens);
	}
	
	/**
	 * Function to build HTML select of groups
	 * 
	 * @param	int		The selected value if any
	 * @param 	string	Attributes for the <select> tag
	 * @return 	string	A string with the HTML select
	 */
	static function selectGroup($selected=0, $attribs='', $fieldname='groupid') 
	{
		// assemble users to the array
		$options = array();
		//$options[] = PHPFrame_HTML::_('select.option', '0', PHPFrame_HTML_Text::_( '-- Select a Group --' ) );
		
		// get users from #__users
		$db = PHPFrame::getDB();
		$query = "SELECT id, name FROM #__groups ORDER BY id";
		//echo $query; exit;
		$db -> setQuery($query);
		if (!$rows = $db->loadObjectList()) {
		  return false;
		}
		
		if (is_array($rows) && count($rows) > 0) {
			foreach ($rows as $row) {
				$options[] = PHPFrame_HTML::_('select.option', $row->id, ucwords($row->name));
			}
		}
		
		$output = PHPFrame_HTML::_('select.genericlist', $options, $fieldname, $attribs, $selected);
		return $output;		
	}
}
