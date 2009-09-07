<?php
/**
 * PHPFrame/User/UserHelper.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   User
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * User Helper Class
 * 
 * @category PHPFrame
 * @package  User
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_UserHelper
{
    /**
     * Format full name to standard
     *
     * @param string $firstname
     * @param string $lastname
     * 
     * @static
     * @access public
     * @return string full name in format: [Uppercase first initial]"." [Surname]
     * @since  1.0
     */
    public static function fullname_format($firstname, $lastname) 
    {
        $str = strtoupper(substr($firstname,0,1)).". ".ucwords($lastname);
                
        return $str;
    }
    
    /**
     * Translate userid to username
     * 
     * @param int $id The ID to be translated
     * 
     * @static
     * @access public
     * @return string If no id is passed returns false, otherwise returns the username 
     *                as a string
     * @since  1.0
     */
    public static function id2name($id=0) 
    {
        if (!empty($id)) { // No user has been selected
            $db = PHPFrame::DB();
            $sql = "SELECT firstname, lastname FROM #__users WHERE id = :id";
            $row = $db->fetchObject($sql, array(":id"=>$id));
            if ($row === false) {
                return false;
            }
            
            return PHPFrame_UserHelper::fullname_format($row->firstname, $row->lastname);
        } else {
            return false;
        }
    }
    
    /**
     * Translate username to userid
     * 
     * @param string $username The username to be translated.
     * 
     * @static
     * @access public
     * @return int    If no username is passed returns false, otherwise returns 
     *                the user ID.
     * @since  1.0
     */
    public static function username2id($username='') 
    {
        if (!empty($username)) { // No user has been selected
            $db = PHPFrame::DB();
            $sql = "SELECT id FROM #__users WHERE username = :username";
            return $db->fetchColumn($sql, array(":username"=>$username));
        } else {
            return false;
        }
    }
    
    /**
     * Translate email to userid
     * 
     * @param string $email The email to be translated.
     * 
     * @static
     * @access public
     * @return mixed  If no email is passed returns FALSE, otherwise returns 
     *                the user ID.
     * @since  1.0
     */
    public static function email2id($email='') 
    {
        if (!empty($email)) { // No user has been selected
            $db = PHPFrame::DB();
            $sql = "SELECT id FROM #__users WHERE email = :email";
            return $db->fetchColumn($sql, array(":email"=>$email));
        } else {
            return false;
        }
    }
    
    /**
     * Translate id to email
     * 
     * @param int $id The userid to be translated.
     * 
     * @static
     * @access public
     * @return mixed A string with the email address or FALSE on fail.
     * @since  1.0
     */
    public static function id2email($id) 
    {
        if (!empty($id)) { // No user has been selected
            $db = PHPFrame::DB();
            $sql = "SELECT email FROM #__users WHERE id = :id";
            return $db->fetchColumn($sql, array(":id"=>$id));
        } else {
            return false;
        }
    }
    
    /**
     * Get user photo for given userid
     * 
     * @param int $id
     * 
     * @static
     * @access public
     * @return string
     * @since  1.0
     */
    public static function id2photo($id) 
    {
        if (!empty($id)) { // No user has been selected
            $db = PHPFrame::DB();
            $sql = "SELECT photo FROM #__users WHERE id = :id";
            $photo = $db->fetchColumn($sql, array(":id"=>$id));
            
            if (empty($photo)) { 
                $photo = 'default.png';
            }
            
            return $photo;
        }
        else {
            return false;
        }
    }
    
    /**
     * Function to build HTML select of users
     * 
     * @param int    $selected  The selected value if any
     * @param string $attribs   Attributes for the <select> tag
     * @param string $fieldname The field name to use for the select tag
     * 
     * @static
     * @access public
     * @return string A string with the HTML select
     * @since  1.0
     */
    public static function select($selected=0, $attribs='', $fieldname='userid') 
    {
        // assemble users to the array
        $options = array();
        $options[] = PHPFrame_HTMLUI::_('select.option', '0', PHPFrame_String::html( '-- Select a User --' ) );
        
        // get users from #__users
        $db = PHPFrame::DB();
        
        $sql = "SELECT u.id, u.firstname, u.lastname ";
        $sql .= " FROM #__users AS u ";
        $sql .= " AND (u.deleted = '0000-00-00 00:00:00' OR u.deleted IS NULL)";
        $sql .= " ORDER BY u.lastname";
        
        $rows = $db->fetchObjectList($sql);
        
        if (!is_array($rows) || count($rows) < 1) {
          return false;
        } else {
            foreach ($rows as $row) {
                $options[] = PHPFrame_HTMLUI::_('select.option', $row->id, PHPFrame_UserHelper::fullname_format($row->firstname, $row->lastname));
            }
        }
        
        $output = PHPFrame_HTMLUI::_('select.genericlist', $options, $fieldname, $attribs, $selected);
        
        return $output;        
    }
    
    /**
     * Build checkboxes with users to pick assignees.
     * 
     * @param mixed  $selected  Either a single userid or an array of ids.
     * @param string $attribs   A string with attributes to be printed in the input tags.
     * @param string $fieldname String tu use for the input tags name attribute.
     * @param int    $projectid This parameter is optional. If passed users will be 
     *                          filtered to the project members.
     * 
     * @static
     * @access public
     * @return string A string with the html code containing the checkboxes.
     * @since  1.0
     */
    public static function assignees($selected=0, $attribs='', $fieldname='assignees[]', $projectid=0) 
    {
        $db = PHPFrame::DB();
        
        $sql = "SELECT u.id, u.firstname, u.lastname ";
        $sql .= " FROM #__users AS u ";
        if (!empty($projectid)) {
            $sql .= " LEFT JOIN #__users_roles ur ON ur.userid = u.id ";
            $sql .= " WHERE ur.projectid = :projectid";
        }
        else {
            $sql .= " WHERE 0=0";
        }
        $sql .= " AND (u.deleted = '0000-00-00 00:00:00' OR u.deleted IS NULL)";
        $sql .= " ORDER BY u.lastname";
        
        if (!$rows = $db->fetchObjectList($sql, array(":projectid"=>$projectid))) {
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
            $output .= PHPFrame_UserHelper::fullname_format($rows[$i]->firstname, $rows[$i]->lastname).'&nbsp;&nbsp;';
            // Add line break every three entries (test using modulus)
            if ((($i+1) % 3) == 0) { $output .= '<br />'; }
        }
        
        return $output;        
    }
    
    /**
     * Build and display an input tag with username autocompleter
     * 
     * @param array $where An array with conditions to include in SQL query.
     * 
     * @static
     * @access public
     * @return void
     * @since  1.0
     */
    public static function autocompleteUsername($where=array()) 
    {
        $db = PHPFrame::DB();
        
        $where[] = "(u.deleted = '0000-00-00 00:00:00' OR u.deleted IS NULL)";
        
        $sql = "SELECT id, username, firstname, lastname FROM #__users ";
        $sql .= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
        $sql .= " ORDER BY username";
        
        $rows = $db->fetchObjectList($sql);
        
        if (!is_array($rows)) {
          return false;
        }
        
        // Organise rows into array of arrays instead of array of objects
        foreach ($rows as $row) {
            $tokens[] = array('id' => $row->id, 'name' => $row->firstname." ".$row->lastname." (".$row->username.")");
        }
        
        PHPFrame_HTMLUI::autocomplete('userids', 'cols="60" rows="2"', $tokens);
    }
    
    /**
     * Function to build HTML select of groups
     * 
     * @param int    $selected  The selected value if any
     * @param string $attribs   Attributes for the <select> tag
     * @param string $fieldname The name to use for the select tag
     * 
     * @static
     * @access public
     * @return string A string with the HTML select
     * @since  1.0
     */
    public static function selectGroup($selected=0, $attribs='', $fieldname='groupid') 
    {
        // assemble users to the array
        $options = array();
        //$options[] = PHPFrame_HTMLUI::_('select.option', '0', PHPFrame_String::html( '-- Select a Group --' ) );
        
        // get users from #__users
        $db = PHPFrame::DB();
        $sql = "SELECT id, name FROM #__groups ORDER BY id";
        $rows = $db->fetchObjectList($sql);
        
        if (!is_array($rows) || count($rows) < 1) {
          return false;
        } else {
            foreach ($rows as $row) {
                $options[] = PHPFrame_HTMLUI::_('select.option', $row->id, ucwords($row->name));
            }
        }
        
        $output = PHPFrame_HTMLUI::_('select.genericlist', $options, $fieldname, $attribs, $selected);
        
        return $output;        
    }
}
