<?php
/**
 * @version		$Id$
 * @package		PHPFrame
 * @subpackage 	user
 * @copyright	Copyright (C) 2009 E-noise.com Limited. All rights reserved.
 * @license		BSD revised. See LICENSE.
 */

/**
 * userOpenID Class
 * 
 * @package		PHPFrame
 * @subpackage 	user
 * @since 		1.0
 */
class PHPFrame_User_OpenID {
	# GetUserId(openid_url)
	//select user_id from user_openids where openid_url = openid_url
	
	# GetOpenIDsByUser(user_id)
	//select openid_url from user_openids where user_id = user_id
	
	# AttachOpenID(openid_url, user_id)
	//insert into user_openids values (openid_url, user_id)
	
	# DetachOpenID(openid_url, user_id)
	//delete from user_openids where openid_url = openid_url and user_id = user_id
	
	# DetachOpenIDsByUser(user_id)
	//delete from user_openids where user_id = user_id
}
