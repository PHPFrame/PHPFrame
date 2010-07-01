<?php
/**
 * PHPFrame/Mapper/MySQLIdObject.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Mapper
 * @author    Chris McDonald <chris.mcdonald@sliderstudio.co.uk>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @since     1.0
 */

/**
 * SQL IdObject class
 *
 * This class encapsulates the selection of rows from the database.
 *
 * @category PHPFrame
 * @package  Mapper
 * @author   Chris McDonald <chris.mcdonald@sliderstudio.co.uk>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
 class PHPFrame_MySQLIdObject extends PHPFrame_SQLIdObject
 {
 	
    /**
     * Constructor
     *
     * @param array $options [Optional] An associative array with initialisation
     *                       options. For a list of available options call
     *                       {@link PHPFrame_IdObject::getOptions()}.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        parent::__construct($options);
    }
    
    /**
     * Get SELECT SQL, overrides SQLIdObject.getSelectSQL() in order to use 
     * backticks for any table names and fields.
     *
     * @return string
     * @since  1.0
     */
    public function getSelectSQL()
    {
        if (count($this->_select) < 1) {
            $msg = "Can not build query. No fields have been selected.";
            throw new LogicException($msg);
        }

        $sql = "SELECT ";

        for ($i=0; $i<count($this->_select); $i++) {
            if ($i>0) {
                $sql .= ", ";
            }

            if (!empty($this->_select[$i]["table_name"])) {
                $sql .= "`".$this->_select[$i]["table_name"]."`.";
            }

            $sql .= "`".$this->_select[$i]["field_name"]."`";

            if (!empty($this->_select[$i]["field_alias"])) {
                $sql .= " AS ".$this->_select[$i]["field_alias"];
            }
        }

        return $sql;
    }
    
    /**
     * Get FROM SQL, overrides SQLIdObject.getFromSQL() in order to use 
     * backticks for any table names.
     *
     * @return string
     * @since  1.0
     */
    public function getFromSQL()
    {
        if (empty($this->_from)) {
            $exception_msg = "Can not build query. No table to select from.";
            throw new LogicException($exception_msg);
        }

        if (is_array($this->_from)) {
        	$sql = "FROM `".$this->_from[0]."` AS ".$this->_from[1];
        } else {
            $sql = "FROM `".$this->_from."`";
        }

        return $sql;
    }
    
    /**
     * Get JOIN SQL, overrides SQLIdObject.getFromSQL() in order to use 
     * backticks for any table names and fields.
     *
     * @return string
     * @since  1.0
     */
    public function getJoinsSQL()
    {
        $sql = "";

        foreach ($this->_join as $join) {
            $sql .= " ".$join["type"]." `".$join["table_name"]."` ";
            if (isset($join["table_alias"])) {
                $sql .= $join["table_alias"]." ";
            }
            $sql .= "ON ".$join["on"][0]." ".$join["on"][1]." ".$join["on"][2];
        }

        return $sql;
    }
 }