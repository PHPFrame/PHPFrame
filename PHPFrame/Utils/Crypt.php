<?php
/**
 * @version		$Id$
 * @package		PHPFrame
 * @subpackage 	utils
 * @copyright	Copyright (C) 2009 E-noise.com Limited. All rights reserved.
 * @license		BSD revised. See LICENSE.
 */

/**
 * Crypt Class
 * 
 * This class provides a set of cryptographic tools used for password encryption 
 * and request tokens.
 * 
 * All methods in this class are static.
 * 
 * @package		PHPFrame
 * @subpackage 	utils
 * @since 		1.0
 */
class PHPFrame_Utils_Crypt {
	/**
	 * Provides a secure hash based on a seed
	 * 
	 * @access	public
	 * @param	string	Seed string
	 * @return	string
	 * @since	1.0
	 */
	static function getHash($seed) {
		return md5(config::SECRET.$seed);
    }

    /**
     * Method to determine a hash for anti-spoofing variable names
     * 
     * @access	public
     * @param	$forceNew	Optional parameter. Default value is FALSE. 
     * 						If set to TRUE it forces the session to generate a new token.
     * @return	string		Hashed var name
     * @since	1.0
     */
    static function getToken($forceNew = false) {
    	$user = PHPFrame::getUser();
    	$session = PHPFrame::getSession();
    	$hash = PHPFrame_Utils_Crypt::getHash($user->id.$session->getToken( $forceNew ));

    	return $hash;
    }
    
	/**
     * Checks for a form token in the request
     *
     * Use in conjuction with PHPFrame_HTML::_( 'form.token' )
     * 
     * @access	public
     * @return	bool	True if found and valid, false otherwise
     * @since	1.0
     */
    static function checkToken() {
    	$token = PHPFrame_Utils_Crypt::getToken();
    	if (!PHPFrame::getRequest()->get($token, '')) {
    		return false;
    		/*
    		$session = PHPFrame::getSession();
    		if ($session->isNew()) {
    			//Redirect to login screen
    		}
    		else {
    			return false;
    		}
    		*/
    	}
    	else {
    		return true;
    	}
    }
    
	/**
	 * Formats a password using the current encryption.
	 *
	 * @access	public
	 * @param	string	$plaintext	The plaintext password to encrypt.
	 * @param	string	$salt		The salt to use to encrypt the password. []
	 *								If not present, a new salt will be
	 *								generated.
	 * @param	string	$encryption	The kind of pasword encryption to use.
	 *								Defaults to md5-hex.
	 * @param	boolean	$show_encrypt  Some password systems prepend the kind of
	 *								encryption to the crypted password ({SHA},
	 *								etc). Defaults to false.
	 *
	 * @return string  The encrypted password.
	 * @since	1.0
	 */
	static function getCryptedPassword($plaintext, $salt = '', $encryption = 'md5-hex', $show_encrypt = false) {
		// Get the salt to use.
		$salt = PHPFrame_Utils_Crypt::getSalt($encryption, $salt, $plaintext);

		// Encrypt the password.
		switch ($encryption) {
			case 'plain' :
				return $plaintext;

			case 'sha' :
				$encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext));
				return ($show_encrypt) ? '{SHA}'.$encrypted : $encrypted;

			case 'crypt' :
			case 'crypt-des' :
			case 'crypt-md5' :
			case 'crypt-blowfish' :
				return ($show_encrypt ? '{crypt}' : '').crypt($plaintext, $salt);

			case 'md5-base64' :
				$encrypted = base64_encode(mhash(MHASH_MD5, $plaintext));
				return ($show_encrypt) ? '{MD5}'.$encrypted : $encrypted;

			case 'ssha' :
				$encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext.$salt).$salt);
				return ($show_encrypt) ? '{SSHA}'.$encrypted : $encrypted;

			case 'smd5' :
				$encrypted = base64_encode(mhash(MHASH_MD5, $plaintext.$salt).$salt);
				return ($show_encrypt) ? '{SMD5}'.$encrypted : $encrypted;

			case 'aprmd5' :
				$length = strlen($plaintext);
				$context = $plaintext.'$apr1$'.$salt;
				$binary = PHPFrame_Utils_Crypt::_bin(md5($plaintext.$salt.$plaintext));

				for ($i = $length; $i > 0; $i -= 16) {
					$context .= substr($binary, 0, ($i > 16 ? 16 : $i));
				}
				for ($i = $length; $i > 0; $i >>= 1) {
					$context .= ($i & 1) ? chr(0) : $plaintext[0];
				}

				$binary = PHPFrame_Utils_Crypt::_bin(md5($context));

				for ($i = 0; $i < 1000; $i ++) {
					$new = ($i & 1) ? $plaintext : substr($binary, 0, 16);
					if ($i % 3) {
						$new .= $salt;
					}
					if ($i % 7) {
						$new .= $plaintext;
					}
					$new .= ($i & 1) ? substr($binary, 0, 16) : $plaintext;
					$binary = PHPFrame_Utils_Crypt::_bin(md5($new));
				}

				$p = array ();
				for ($i = 0; $i < 5; $i ++) {
					$k = $i +6;
					$j = $i +12;
					if ($j == 16) {
						$j = 5;
					}
					$p[] = PHPFrame_Utils_Crypt::_toAPRMD5((ord($binary[$i]) << 16) | (ord($binary[$k]) << 8) | (ord($binary[$j])), 5);
				}

				return '$apr1$'.$salt.'$'.implode('', $p).PHPFrame_Utils_Crypt::_toAPRMD5(ord($binary[11]), 3);

			case 'md5-hex' :
			default :
				$encrypted = ($salt) ? md5($plaintext.$salt) : md5($plaintext);
				return ($show_encrypt) ? '{MD5}'.$encrypted : $encrypted;
		}
	}

	/**
	 * Returns a salt for the appropriate kind of password encryption.
	 * Optionally takes a seed and a plaintext password, to extract the seed
	 * of an existing password, or for encryption types that use the plaintext
	 * in the generation of the salt.
	 *
	 * @access public
	 * @param string $encryption  The kind of pasword encryption to use.
	 *							Defaults to md5-hex.
	 * @param string $seed		The seed to get the salt from (probably a
	 *							previously generated password). Defaults to
	 *							generating a new seed.
	 * @param string $plaintext   The plaintext password that we're generating
	 *							a salt for. Defaults to none.
	 *
	 * @return string  The generated or extracted salt.
	 * @since	1.0
	 */
	static function getSalt($encryption = 'md5-hex', $seed = '', $plaintext = '') {
		// Encrypt the password.
		switch ($encryption) {
			case 'crypt' :
			case 'crypt-des' :
				if ($seed) {
					return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 2);
				} else {
					return substr(md5(mt_rand()), 0, 2);
				}
				break;

			case 'crypt-md5' :
				if ($seed) {
					return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 12);
				} else {
					return '$1$'.substr(md5(mt_rand()), 0, 8).'$';
				}
				break;

			case 'crypt-blowfish' :
				if ($seed) {
					return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 16);
				} else {
					return '$2$'.substr(md5(mt_rand()), 0, 12).'$';
				}
				break;

			case 'ssha' :
				if ($seed) {
					return substr(preg_replace('|^{SSHA}|', '', $seed), -20);
				} else {
					return mhash_keygen_s2k(MHASH_SHA1, $plaintext, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
				}
				break;

			case 'smd5' :
				if ($seed) {
					return substr(preg_replace('|^{SMD5}|', '', $seed), -16);
				} else {
					return mhash_keygen_s2k(MHASH_MD5, $plaintext, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
				}
				break;

			case 'aprmd5' :
				/* 64 characters that are valid for APRMD5 passwords. */
				$APRMD5 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

				if ($seed) {
					return substr(preg_replace('/^\$apr1\$(.{8}).*/', '\\1', $seed), 0, 8);
				} else {
					$salt = '';
					for ($i = 0; $i < 8; $i ++) {
						$salt .= $APRMD5 {
							rand(0, 63)
							};
					}
					return $salt;
				}
				break;

			default :
				$salt = '';
				if ($seed) {
					$salt = $seed;
				}
				return $salt;
				break;
		}
	}

	/**
	 * Generate a random password
	 *
	 * @access	public
	 * @param	int		$length	Length of the password to generate
	 * @return	string			Random Password
	 * @since	1.0
	 */
	static function genRandomPassword($length = 8) {
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len = strlen($salt);
		$makepass = '';

		$stat = @stat(__FILE__);
		if(empty($stat) || !is_array($stat)) $stat = array(php_uname());

		mt_srand(crc32(microtime() . implode('|', $stat)));

		for ($i = 0; $i < $length; $i ++) {
			$makepass .= $salt[mt_rand(0, $len -1)];
		}

		return $makepass;
	}

	/**
	 * Converts to allowed 64 characters for APRMD5 passwords.
	 *
	 * @access	private
	 * @param	string	$value
	 * @param	integer	$count
	 * @return	string  $value converted to the 64 MD5 characters.
	 * @since	1.0
	 */
	private function _toAPRMD5($value, $count) {
		/* 64 characters that are valid for APRMD5 passwords. */
		$APRMD5 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

		$aprmd5 = '';
		$count = abs($count);
		while (-- $count) {
			$aprmd5 .= $APRMD5[$value & 0x3f];
			$value >>= 6;
		}
		return $aprmd5;
	}

	/**
	 * Converts hexadecimal string to binary data.
	 *
	 * @access	private
	 * @param	string	$hex  Hex data.
	 * @return	string  Binary data.
	 * @since	1.0
	 */
	private function _bin($hex) {
		$bin = '';
		$length = strlen($hex);
		for ($i = 0; $i < $length; $i += 2) {
			$tmp = sscanf(substr($hex, $i, 2), '%x');
			$bin .= chr(array_shift($tmp));
		}
		return $bin;
	}
}
