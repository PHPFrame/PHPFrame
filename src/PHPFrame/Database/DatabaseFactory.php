<?php
/**
 * PHPFrame/Database/DatabaseFactory.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Database
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @since     1.0
 */

/**
 * Database factory class.
 *
 * @category PHPFrame
 * @package  Database
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Database
 * @since    1.0
 */
class PHPFrame_DatabaseFactory
{
    /**
     * Get instance of DB object based on options array.
     *
     * @param array $options [Optional] An associative array containing the
     *                       following options:
     *                         - driver (required)
     *                         - name (required)
     *                         - host
     *                         - user
     *                         - pass
     *                         - prefix
     *                         - mysql_unix_socket
     *
     * @return PHPFrame_Database
     * @since  1.0
     */
    public static function getDB(array $options)
    {
        if (!array_key_exists("driver", $options)
            || !array_key_exists("name", $options)
        ) {
            $msg  = "'driver' and 'name' are required in options array";
            throw new InvalidArgumentException($msg);
        }

        $dsn = strtolower($options["driver"]);
        if ($dsn == "sqlite") {
            $dsn .= ":".$options["name"];
        } elseif ($dsn == "mysql") {
            $dsn .= ":dbname=".$options["name"];
            if (isset($options["host"]) && !empty($options["host"])) {
                $dsn .= ";host=".$options["host"];
            }
            if (isset($options["mysql_unix_socket"])
                && !empty($options["mysql_unix_socket"])
            ) {
                $dsn .= ";unix_socket=".$options["mysql_unix_socket"];
            } else {
                $dsn .= ";unix_socket=".ini_get('mysql.default_socket');
            }
        } else {
            $msg = "Database driver not supported.";
            throw new Exception($msg);
        }

        if (isset($options["user"]) && !empty($options["user"])) {
            $db_user = $options["user"];
        } else {
            $db_user = null;
        }

        if (isset($options["pass"]) && !empty($options["pass"])) {
            $db_pass = $options["pass"];
        } else {
            $db_pass = null;
        }

        if (isset($options["prefix"]) && !empty($options["prefix"])) {
            $db_prefix = $options["prefix"];
        } else {
            $db_prefix = null;
        }

        return PHPFrame_Database::getInstance(
            $dsn,
            $db_user,
            $db_pass,
            $db_prefix
        );
    }
}
