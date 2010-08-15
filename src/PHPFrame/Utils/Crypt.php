<?php
/**
 * PHPFrame/Utils/Crypt.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Utils
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * This class provides a set of cryptographic tools used for password encryption
 * and hash tokens.
 *
 * @category PHPFrame
 * @package  Utils
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_Crypt
{
    private $_secret;

    /**
     * Constructor.
     *
     * @param string $secret [Optional] A string to be used to create secure hashes.
     *
     * @return void
     * @since  1.0
     */
    public function __construct($secret=null)
    {
        if (is_null($secret)) {
            $secret = $this->genRandomPassword(32);
        }

        $this->_secret = trim((string) $secret);
    }

    /**
     * Provides a secure hash based on a seed
     *
     * @param string $seed Seed string.
     *
     * @return string
     * @since  1.0
     */
    public function getHash($seed)
    {
        return md5($this->_secret.$seed);
    }

    /**
     * Encrypt password using given salt and md5-hex algorithm.
     *
     * @param string $plaintext The plaintext password to encrypt.
     * @param string $salt      The salt to use to encrypt the password.
     *
     * @return string The encrypted password.
     * @since  1.0
     */
    public function encryptPassword($plaintext, $salt)
    {
        // Encrypt the password.
        return md5($plaintext.$salt);
    }

    /**
     * Generate a random password
     *
     * @param int $length Length of the password to generate
     *
     * @return string Random Password
     * @since  1.0
     */
    public function genRandomPassword($length=8)
    {
        if (function_exists("openssl_random_pseudo_bytes")) {
            do {
                $entropy = openssl_random_pseudo_bytes($length, $strong);
            } while ($strong === false);

        } elseif ($fp = @fopen("/dev/urandom", "rb")) {
            $entropy = fread($fp, $length);
            fclose($fp);
            $entropy .= uniqid(mt_rand(), true);

        } else {
            $salt    = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $salt   .= "0123456789";
            $len     = strlen($salt);
            $entropy = "";
            $stat    = @stat(__FILE__);

            if (empty($stat) || !is_array($stat)) {
                $stat = array(php_uname());
            }

            mt_srand(crc32(microtime() . implode("|", $stat)));

            for ($i=0; $i<$length; $i++) {
                $entropy .= $salt[mt_rand(0, $len-1)];
            }
        }

        return substr(sha1($entropy), 0, $length);
    }
}
