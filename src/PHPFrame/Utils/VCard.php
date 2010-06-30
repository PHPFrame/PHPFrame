<?php
/**
 * PHPFrame/Utils/vCard.php
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
 * vCard Class
 *
 * @category PHPFrame
 * @package  Utils
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_VCard
{
    /**
     * Array containing vCard data
     *
     * @var array
     */
    private $_data = array(
        "N" => array(
            "FAMILY"     => null,
            "GIVEN"      => null,
            "ADDITIONAL" => null,
            "PREFIXES"   => null,
            "SUFFIXES"   => null
        ),
        "FN"    => null,
        "EMAIL" => array(),
        "PHOTO" => null
    );

    /**
     * Constructor.
     *
     * @param array $name_array [Optional] An array containing the different
     *                          parts of the "name":
     *                          - "FAMILY"
     *                          - "GIVEN"
     *                          - "ADDITIONAL"
     *                          - "PREFIXES"
     *                          - "SUFFIXES"
     *
     * @return void
     * @since  1.0
     */
    public function __construct(array $name_array=array())
    {
        $optional_keys = array(
            "FAMILY",
            "GIVEN",
            "ADDITIONAL",
            "PREFIXES",
            "SUFFIXES"
        );

        foreach ($optional_keys as $optional_key) {
            if (!array_key_exists($optional_key, $name_array)) {
                $name_array[$optional_key] = null;
            }
        }

        $this->setName(
            $name_array["FAMILY"],
            $name_array["GIVEN"],
            $name_array["ADDITIONAL"],
            $name_array["PREFIXES"],
            $name_array["SUFFIXES"]
        );
    }

    /**
     * Convert vCard object to string
     *
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        $str  = "BEGIN:VCARD\n";
        $str .= "VERSION:3.0\n";


        $str .= "N:".$this->getName()."\n";

        $fn = $this->_data["FN"];
        if (empty($fn)) {
            $fn = $this->getName();
        }
        $str .= "FN:".$fn."\n";

        foreach ($this->_data["EMAIL"] as $email) {
            $str .= "EMAIL;TYPE=".$email[1].":".$email[0]."\n";
        }

        if (!empty($this->_data["PHOTO"])) {
            $str .= "PHOTO;VALUE=URL:".$this->_data["PHOTO"]."\n";
        }

        $str .= "END:VCARD";

        return $str;
    }

    /**
     * Set name properties.
     *
     * @param string $last_name    The last name.
     * @param string $first_name   The first name.
     * @param string $middle_names [Optional] Any middle names.
     * @param string $prefixes     [Optional] Name prefixes.
     * @param string $suffixes     [Optional] Name suffixes.
     *
     * @return void
     * @since  1.0
     */
    public function setName(
        $last_name,
        $first_name,
        $middle_names="",
        $prefixes="",
        $suffixes=""
    ) {
        $this->_data["N"]["FAMILY"]     = (string) $last_name;
        $this->_data["N"]["GIVEN"]      = (string) $first_name;
        $this->_data["N"]["ADDITIONAL"] = (string) $middle_names;
        $this->_data["N"]["PREFIXES"]   = (string) $prefixes;
        $this->_data["N"]["SUFFIXES"]   = (string) $suffixes;

        // remove special chars
        foreach ($this->_data["N"] as $key=>$value) {
            $value = str_replace(array(";", ":", "\n"), "", $value);
            $this->_data["N"][$key] = $value;
        }
    }

    /**
     * Get name.
     *
     * @return string
     * @since  1.0
     */
    public function getName()
    {
        $str = "";

        if (!empty($this->_data["N"]["PREFIXES"])) {
            $str .= $this->_data["N"]["PREFIXES"]." ";
        }

        $str .= $this->_data["N"]["GIVEN"];

        if (!empty($this->_data["N"]["ADDITIONAL"])) {
            $str .= " ".$this->_data["N"]["ADDITIONAL"];
        }

        $str .= " ".$this->_data["N"]["FAMILY"];

        if (!empty($this->_data["N"]["SUFFIXES"])) {
            $str .= " ".$this->_data["N"]["SUFFIXES"]." ";
        }

        return $str;
    }

    /**
     * Set formatted name
     *
     * @param string $formatted_name A string with the formatted name.
     *
     * @return void
     * @since  1.0
     */
    public function setFormattedName($formatted_name)
    {
        $this->_data["FN"] = (string) $formatted_name;
    }

    /**
     * Get formatted name.
     *
     * @return string
     * @since  1.0
     */
    public function getFormattedName()
    {
        return $this->_data["FN"];
    }

    /**
     * Set email.
     *
     * @param string $email The email address.
     * @param string $type  [Optional] Email type ('PREF', 'HOME', 'WORK'). The
     *                      default value is 'PREF'.
     *
     * @return void
     * @since  1.0
     */
    public function addEmail($email, $type="PREF")
    {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);

        if ($email === false) {
            $msg  = "Email argument passed to ".get_class($this)."::";
            $msg .= __FUNCTION__."() is not a valid email address.";
            throw new InvalidArgumentException($email);
        }

        $type = str_replace(array(";", ":", "\n"), "", trim($type));

        // if email to be added/modified is to be set as "preferred" we make
        // sure no other email is also set to "PREF"
        if (stripos($type, "PREF") !== false) {
            for ($i=0; $i<count($this->_data["EMAIL"]); $i++) {
                $t = explode(",", $this->_data["EMAIL"][$i][1]);
                if (in_array("PREF", $t)) {
                    $t_without_pref = "";
                    for ($j=0; $j<count($t); $j++) {
                        if (strtoupper($t[$j]) != "PREF") {
                            if (!empty($t_without_pref)) {
                                $t_without_pref .= ",";
                            }
                            $t_without_pref .= $t[$j];
                        }
                    }
                    $this->_data["EMAIL"][$i][1] = $t_without_pref;
                }
            }
        }

        // If email address already exists in vCard we simply update its "type"
        for ($i=0; $i<count($this->_data["EMAIL"]); $i++) {
            if ($this->_data["EMAIL"][$i][0] == $email) {
                $this->_data["EMAIL"][$i][1] = $type;
                return;
            }
        }

        $this->_data["EMAIL"][] = array((string) $email, $type);
    }

    /**
     * Remove email address.
     *
     * @param string $email The email address we want to remove.
     *
     * @return void
     * @since  1.0
     */
    public function removeEmail($email)
    {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);

        if ($email === false) {
            $msg  = "Email argument passed to ".get_class($this)."::";
            $msg .= __FUNCTION__."() is not a valid email address.";
            throw new InvalidArgumentException($email);
        }

        for ($i=0; $i<count($this->_data["EMAIL"]); $i++) {
            if ($this->_data["EMAIL"][$i][0] == $email) {
                unset($this->_data["EMAIL"][$i]);
            }
        }

        // Re-index array
        $this->_data["EMAIL"] = array_values($this->_data["EMAIL"]);
    }

    /**
     * Get email addresses.
     *
     * @return array
     * @since  1.0
     */
    public function getEmailAddresses()
    {
        return $this->_data["EMAIL"];
    }

    /**
     * Get preferred email address.
     *
     * @return string
     * @since  1.0
     */
    public function getPreferredEmail()
    {
        foreach ($this->_data["EMAIL"] as $email) {
            if (stripos($email[1], "PREF") !== false) {
                return $email[0];
            }
        }

        // If no email is set as preferred return the first entry if vCard
        // has at least one email
        if (isset($this->_data["EMAIL"][0][0])) {
            return $this->_data["EMAIL"][0][0];
        }
    }

    /**
     * Set photo.
     *
     * @param string $photo_url URL to the photo.
     *
     * @return void
     * @since  1.0
     */
    public function setPhoto($photo_url)
    {
        $this->_data["PHOTO"] = (string) $photo_url;
    }

    /**
     * Get photo URL.
     *
     * @return string
     * @since  1.0
     */
    public function getPhoto()
    {
        return $this->_data["PHOTO"];
    }
}
