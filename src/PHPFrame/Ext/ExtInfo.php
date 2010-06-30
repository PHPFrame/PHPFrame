<?php
/**
 * PHPFrame/Ext/ExtInfo.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Ext
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Extension Info Abstract Class
 *
 * @category PHPFrame
 * @package  Ext
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
abstract class PHPFrame_ExtInfo extends PHPFrame_PersistentObject
{
    /**
     * Boolean indicating whether addon is enabled
     *
     * @var bool
     */
    protected $enabled=false;

    /**
     * Constructor
     *
     * @param array $options [Optional]
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $options=null)
    {
        // before we construct the parent we add the necessary fields
        $this->addField(
            "name",
            null,
            false,
            new PHPFrame_RegexpFilter(array(
                "regexp"=>'/^[a-zA-Z\._]{3,50}$/',
                "min_length"=>3,
                "max_length"=>50
            ))
        );
        $this->addField(
            "channel",
            null,
            true,
            new PHPFrame_StringFilter()
        );
        $this->addField(
            "summary",
            null,
            true,
            new PHPFrame_StringFilter()
        );
        $this->addField(
            "description",
            null,
            true,
            new PHPFrame_StringFilter()
        );
        $this->addField(
            "author",
            null,
            true,
            new PHPFrame_StringFilter()
        );
        $this->addField(
            "date",
            null,
            true,
            new PHPFrame_StringFilter()
        );
        $this->addField(
            "version",
            null,
            true,
            new PHPFrame_StringFilter()
        );
        $this->addField(
            "license",
            null,
            true,
            new PHPFrame_StringFilter()
        );
        $this->addField(
            "license_url",
            null,
            true,
            new PHPFrame_StringFilter()
        );
        $this->addField(
            "enabled",
            false,
            false,
            new PHPFrame_BoolFilter()
        );

        parent::__construct($options);
    }

    /**
     * Get/set name.
     *
     * @param string $str [Optional] The extension name.
     *
     * @return string
     * @since  1.0
     */
    public function name($str=null)
    {
        if (!is_null($str)) {
            $this->fields["name"] = $this->validate("name", $str);
        }

        return $this->fields["name"];
    }

    /**
     * Get/set channel.
     *
     * @param string $str [Optional] The extenion's channel.
     *
     * @return string
     * @since  1.0
     */
    public function channel($str=null)
    {
        if (!is_null($str)) {
            $this->fields["channel"] = $this->validate("channel", $str);
        }

        return $this->fields["channel"];
    }

    /**
     * Get/set summary.
     *
     * @param string $str [Optional] The extenion's summary.
     *
     * @return string
     * @since  1.0
     */
    public function summary($str=null)
    {
        if (!is_null($str)) {
            $this->fields["summary"] = $this->validate("summary", $str);
        }

        return $this->fields["summary"];
    }

    /**
     * Get/set description.
     *
     * @param string $str [Optional] The extenion's description.
     *
     * @return string
     * @since  1.0
     */
    public function description($str=null)
    {
        if (!is_null($str)) {
            $this->fields["description"] = $this->validate("description", $str);
        }

        return $this->fields["description"];
    }

    /**
     * Get/set author.
     *
     * @param string $str [Optional] The extenion's author.
     *
     * @return string
     * @since  1.0
     */
    public function author($str=null)
    {
        if (!is_null($str)) {
            $this->fields["author"] = $this->validate("author", $str);
        }

        return $this->fields["author"];
    }

    /**
     * Get/set date.
     *
     * @param string $str [Optional] The extenion's build date.
     *
     * @return string
     * @since  1.0
     */
    public function date($str=null)
    {
        if (!is_null($str)) {
            $this->fields["date"] = $this->validate("date", $str);
        }

        return $this->fields["date"];
    }

    /**
     * Get/set version.
     *
     * @param string $str [Optional] The extenion's version.
     *
     * @return string
     * @since  1.0
     */
    public function version($str=null)
    {
        if (!is_null($str)) {
            $this->fields["version"] = $this->validate("version", $str);
        }

        return $this->fields["version"];
    }

    /**
     * Set license.
     *
     * @param string $str [Optional] The extenion's license.
     *
     * @return string
     * @since  1.0
     */
    public function license($str=null)
    {
        if (!is_null($str)) {
            $this->fields["license"] = $this->validate("license", $str);
        }

        return $this->fields["license"];
    }

    /**
     * Set license URL.
     *
     * @param string $str [Optional] The extenion's license URL.
     *
     * @return string
     * @since  1.0
     */
    public function licenseURL($str=null)
    {
        if (!is_null($str)) {
            $this->fields["license_url"] = $this->validate("license_url", $str);
        }

        return $this->fields["license_url"];
    }

    /**
     * Is enabled?
     *
     * @param bool $bool [Optional] Boolean indicating whether extension is
     *                              enabled.
     *
     * @return bool
     * @since  1.0
     */
    public function enabled($bool=null)
    {
        if (!is_null($bool)) {
            $this->fields["enabled"] = $this->validate("enabled", $bool);
        }

        return $this->fields["enabled"];
    }
}
