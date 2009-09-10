<?php
/**
 * PHPFrame/Lang/en-GB.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Lang
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

class PHPFrame_Lang
{
    // environment > request
    const SESSION_ERROR_NO_CLIENT_DETECTED="ERROR: No client could be found!";
    
    // application > sysevents
    const EVENT_TYPE_ERROR="Error";
    const EVENT_TYPE_WARNING="Warning";
    const EVENT_TYPE_NOTICE="Notice";
    const EVENT_TYPE_INFO="Info";
    const EVENT_TYPE_SUCCESS="Success";
    
    // utils > filesystem
    const UPLOAD_ERROR_PHP_UP_MAX_FILESIZE="ERROR: PHP upload maximum file size exceeded!";
    const UPLOAD_ERROR_PHP_MAX_FILESIZE="ERROR: PHP maximum file size exceeded!";
    const UPLOAD_ERROR_PARTIAL_UPLOAD="ERROR: Partial upload!";
    const UPLOAD_ERROR_NO_FILE="ERROR: No file submitted for upload!";
    const UPLOAD_ERROR_MAX_FILESIZE="ERROR: Maximum file size exceeded!";
    const UPLOAD_ERROR_FILETYPE="ERROR: File type not valid!";
    const UPLOAD_ERROR_MOVE="ERROR: Could not move file to destination directory!";
    const UPLOAD_ERROR_ATTACK="ERROR: Possible file attack!";
    
    // User
    const EMAIL_ALREADY_REGISTERED="Email is already registered";
}
