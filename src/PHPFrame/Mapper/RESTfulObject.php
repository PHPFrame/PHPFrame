<?php
/**
 * PHPFrame/Mapper/RESTfulObject.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Mapper
 * @author    Chris McDonald <chris.mcdonald@sliderstudio.co.uk>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * The RESTfulObject interface defines an alternative way to describe an object
 * that has been requested using the RESTful sevice.
 *
 * This interface should be used to include fields on PersistantObjects which
 * are not included as part of the IteratorAggregate interface, but should be
 * included when sending an object for a REST API request.
 *
 * For example, a Note class could be made up of several Message objects stored
 * in a private field, in addition the note has its own persistant fields id,
 * creation date etc. By default only the persistant fields of the note
 * will be sent for a request. In order to send the messages field
 * as well, the Note class will need to implement the PHPFrame_RESTfulObject
 * interface and implement the getRESTfulRepresentation method to return an 
 * associative array of all fields for the object that should be sent. 
 * The renderer will choose to send the RESTful representational fields of a 
 * persistent object if it implements this interface.
 *
 * @category PHPFrame
 * @package  Mapper
 * @author   Chris McDonald <chris.mcdonald@sliderstudio.co.uk>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
interface PHPFrame_RESTfulObject
{
    /**
     * Gets the fields that should be used to represent the state of this
     * object when being sent via a REST API request.
     *
     * @return array an associative array containing the fields to be used to
     * represent this object
     */
    public function getRESTfulRepresentation();
}
