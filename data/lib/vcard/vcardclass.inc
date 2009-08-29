<?
/*
 * Class to manipulate with vCard information, according vCard v2.1 and vCard v3.0.
 * References: http://www.imc.org/pdi/
 * Author: Viatcheslav Ivanov, E-Witness Inc., Canada;
 * mail: ivanov@e-witness.ca, v_iv@hotmail.com;
 * web: www.e-witness.ca; www.coolwater.ca; www.strongpost.net;
 * version: 1.00 /09.20.2002
 *
 */
if ( !defined("VCARDCLASS_INC") ) {

    define("VCARDCLASS_INC",1);

    class VCARD {
        var $types = array();        // array vCard object
        var $lasterror_msg = "";     // last error message
        var $lasterror_num = 0;        // last error number
        var $mailer = "";             // specify MAILER property (ex. "StrongPost 2.0")

        // initialization
        function VCARD($version = "2.1") {
            $this->setVersion($version);
        }

        // reset vCard to begin new one
        function resetvCard($version = "2.1") {
            $this->types = array();
            $lasterror_msg = "";
            $lasterror_num = 0;
            $mailer = "";
            $this->setVersion($version);
        }

        // set vCard object by vCard formatted string (ex. after upload vCard file)
        function setvCard($input) {
            $vCardErr = 0;
            $lines_init = preg_split("/(?:\r\n|\r|\n)/", $input);
            $i = 0;
            $size_lines_init = sizeof($lines_init);
            for ($k = 0; $k < $size_lines_init; $k++) {
                if ($lines_init[$k]) {
                    if (!strstr($lines_init[$k], ":")) {
                        if ($i > 0) {
                            $lines[($i - 1)].= "\r\n".$lines_init[$k];
                        } else {
                            $this->lasterror_msg = "Wrong vCard format.";
                            $this->lasterror_num = 20;
                            $vCardErr++;
                        }
                    } else {
                        $lines[$i] = $lines_init[$k];
                        $i++;
                    }
                }
            }
            $size_lines = sizeof($lines);
            if ($size_lines < 3) {
                $this->lasterror_msg = "Wrong vCard format.";
                $this->lasterror_num = 21;
                $vCardErr++;
            } else {
                // always need to check first BEGIN, END, VERSION
                if ($this->_getTVP($lines[0]) != "BEGIN" || strtoupper($this->_getTVP($lines[0], "V")) != "VCARD" || $this->_getTVP($lines[0], "P")) {
                    $this->lasterror_msg = "Wrong vCard format.";
                    $this->lasterror_num = 22;
                    $vCardErr++;
                }
                if ($this->_getTVP($lines[($size_lines-1)]) != "END" || strtoupper($this->_getTVP($lines[($size_lines-1)], "V")) != "VCARD" || $this->_getTVP($lines[($size_lines-1)], "P")) {
                    $this->lasterror_msg = "Wrong vCard format.";
                    $this->lasterror_num = 23;
                    $vCardErr++;
                }
                for ($k = 0; $k < $size_lines; $k++) {
                    if ($this->_getTVP($lines[$k]) == "VERSION") {
                        $this->setVersion($this->_getTVP($lines[$k], "V"));
                        $version_flag = "OK";
                        break;
                    }
                }
                if (!$version_flag) {
                    $this->lasterror_msg = "Wrong vCard format.";
                    $this->lasterror_num = 24;
                    $vCardErr++;
                }
            }
            if (!$vCardErr) {
                for ($k = 0; $k < $size_lines; $k++) {
                    if ($this->_getTVP($lines[$k]) != "BEGIN" && $this->_getTVP($lines[$k]) != "END" && $this->_getTVP($lines[$k]) != "VERSION") {
                        if ($this->_getTVP($lines[$k], "P")) {
                            $arr_types = explode(";", $this->_getTVP($lines[$k], "TP"));
                            $this->types[$this->_setInternalType($arr_types)] = $this->decode($this->_getTVP($lines[$k], "V"));
                        } else {
                            $this->types[$this->_getTVP($lines[$k])] = $this->_getTVP($lines[$k], "V");
                        }
                    }
                }
            }
        }

        // get vCard formatted string according version number
        function getvCard($version = "") {
            if ($version) $this->setVersion($version);
            if ($this->mailer) $this->setMailer($this->mailer);
            $this->setRevision();
            $output = "";
            $output.= "BEGIN:VCARD\r\n";
            reset ($this->types);
            while (list($key, $value) = each($this->types)) {
                if ($value) {
                    $getkey = $this->_getInternalType($key);
                    $getkeyparam = $this->_getInternalType($key, "P");
                    $getvalue = $value;
                    if ($this->getVersion() == "3.0") {
                        if ($getkeyparam) {
                            if (strstr($getkeyparam,"ENCODING") || strstr($getkeyparam,"VALUE")) {
                                $arr_params = explode(";", $getkeyparam);
                                while (list(, $value) = each($arr_params))
                                    if ($value)
                                        $getkey.= (strstr($value,"ENCODING") || strstr($value,"VALUE"))? ";".$value : ";TYPE=".$value;
                                $getkey = str_replace("ENCODING=BASE64", "ENCODING=B", $getkey);
                                $getkey = str_replace("VALUE=URL", "VALUE=URI", $getkey);
                            } else {
                                $getkey.= ";TYPE=".str_replace(";", ",", $getkeyparam);
                            }
                        }
                    } else {
                        if ($getkeyparam)
                            $getkey.= ";".$getkeyparam;
                        if (!strstr($getkeyparam,"ENCODING") && $this->quoted_printable_encode($value) != $value) {
                            $getvalue = $this->quoted_printable_encode($value);
                            $getkey.= ";ENCODING=QUOTED-PRINTABLE";
                        }
                    }
                    $output.= (strstr($getkey,"ENCODING=B"))? $getkey.":\r\n".$getvalue."\r\n\r\n" : $getkey.":".$getvalue."\r\n";
                }
            }
            $output.= "END:VCARD\r\n";
            return $output;
        }

        // send vCard formatted string according version number to browser (download)
        // You need to control in your custom script, input by user First and/or Last name.
        function downloadvCard($version = "") {
            $output = $this->getvCard($version);
            $filename = trim($this->getName("FIRST")." ".$this->getName("LAST")).".vcf";
            Header("Content-Type: text/x-vCard; name=".$filename);
            Header("Content-Transfer-Encoding: quoted-printable");
            Header("Content-Disposition: attachment; filename=".$filename);
            Header("Content-Length: ".strlen($output));
            return $output;
        }

        // get entries contain value(s) specified in $input, return array entries
        // $param: "T"-type, "P"-parameter(s), "V"-value, "TP"- type and parameter(s)
        // $input - may be string or array of mix type and parameters
        // $condition: "AND", "OR" - to specify boolean logic for array $input
        function findTVP($input, $TVPattr = "T", $condition = "AND") {
            $output = array();
            reset ($this->types);
            if ($TVPattr == "T" || $TVPattr == "P" || $TVPattr == "TP") {
                while (list($key,) = each($this->types)) {
                    $arr_params = array();
                    if ($this->_getInternalType($key, $TVPattr)) $arr_params = explode(";", $this->_getInternalType($key, $TVPattr));
                    if (is_array($input)) {
                        if ($condition == "AND") {
                            $notfound_flag = 0;
                            reset($input);
                            while (list(,$value) = each($input)) {
                                if (!in_array(strtoupper($value), $arr_params)) {
                                    $notfound_flag++;
                                    break;
                                }
                            }
                            if (!$notfound_flag) $output[] = $key;
                        } else {
                            $found_flag = 0;
                            reset($input);
                            while (list(,$value) = each($input)) {
                                if (in_array(strtoupper($value), $arr_params)) {
                                    $found_flag++;
                                    break;
                                }
                            }
                            if ($found_flag) $output[] = $key;
                        }
                    } else {
                        if (strstr($this->_getInternalType($key, $TVPattr), strtoupper($input))) $output[] = $key;
                    }
                }
            } else {
                while (list($key, $value) = each($this->types)) {
                    if (is_array($input)) {
                        if ($condition == "AND") {
                            $notfound_flag = 0;
                            reset($input);
                            while (list(,$ivalue) = each($input)) {
                                if (!stristr($value, $ivalue)) {
                                    $notfound_flag++;
                                    break;
                                }
                            }
                            if (!$notfound_flag) $output[] = $key;
                        } else {
                            $found_flag = 0;
                            reset($input);
                            while (list(,$ivalue) = each($input)) {
                                if (stristr($value, $ivalue)) {
                                    $found_flag++;
                                    break;
                                }
                            }
                            if ($found_flag) $output[] = $key;
                        }
                    } else {
                        if (stristr($value, $input)) $output[] = $key;
                    }
                }
            }
            return $output;
        }

        //****** IDENTIFICATION TYPES ***************************************

        // set/get N type - To specify the components of the name of the object the vCard represents.
        function setName($lastName = "", $firstName = "", $middleNames = "", $prefixes = "", $suffixes = "") {
            $this->types["N"] = $lastName.";".$firstName.";".$middleNames.";".$prefixes.";".$suffixes;
            
            $formattedname = "";
            if ($prefixes) $formattedname = $prefixes." ";
            if ($firstName) $formattedname.= $firstName." ";
            if ($middleNames) $formattedname.= $middleNames." ";
            if ($lastName) $formattedname.= $lastName." ";
            if ($suffixes) $formattedname.= $suffixes;
            if (!$this->getFormattedName()) $this->setFormattedName(trim($formattedname));
            if (!$this->getSortString()) $this->setSortString($lastName);
        }
        // $attr: "LAST", "FIRST", "MIDDLE", "PREF", "SUFF"
        function getName($attr = "LAST") {
            $output = "";
            if (isset($this->types["N"]) && $this->types["N"]) {
                $arr_names = explode(";", $this->types["N"]);
                if (is_array($arr_names)) {
                    $size_arr_names = sizeof($arr_names);
                    switch (strtoupper($attr)) {
                        case "FIRST":
                            if ($size_arr_names > 1) $output = trim($arr_names[1]);
                            break;
                        case "MIDDLE":
                            if ($size_arr_names > 2) $output = trim($arr_names[2]);
                            break;
                        case "PREF":
                            if ($size_arr_names > 3) $output = trim($arr_names[3]);
                            break;
                        case "SUFF":
                            if ($size_arr_names > 4) $output = trim($arr_names[4]);
                            break;
                        default:
                            if ($size_arr_names > 0) $output = trim($arr_names[0]);
                    }
                }
            } else {
                if (isset($this->types["FN"]) && $this->types["FN"]) {
                    $arr_names = explode(" ", $this->types["FN"]); //try to parse FN
                    if (is_array($arr_names)) {
                        $size_arr_names = sizeof($arr_names);
                        switch (strtoupper($attr)) {
                            case "FIRST":
                                if ($size_arr_names < 4) $output = trim($arr_names[0]);
                                if ($size_arr_names > 3) $output = trim($arr_names[1]);
                                break;
                            case "MIDDLE":
                                if ($size_arr_names > 3) $output = trim($arr_names[2]);
                                if ($size_arr_names == 3) $output = trim($arr_names[1]);
                                break;
                            case "PREF":
                                if ($size_arr_names > 3) $output = trim($arr_names[0]);
                                break;
                            case "SUFF":
                                if ($size_arr_names == 5) $output = trim($arr_names[4]);
                                break;
                            default:
                                if ($size_arr_names == 2) $output = trim($arr_names[1]);
                                if ($size_arr_names == 3) $output = trim($arr_names[2]);
                                if ($size_arr_names > 3) $output = trim($arr_names[3]);
                        }
                    }
                }
            }
            return $output;
        }

        // set/get FN type - To specify the formatted text corresponding to the name
        // of the object the vCard represents.
        function setFormattedName($formattedName = "") {
            $this->types["FN"] = trim($formattedName);
        }
        function getFormattedName() {
            if (!isset($this->types["FN"])) {
                return null;
            }
            
            return $this->types["FN"];
        }

        // set/get NICKNAME type - To specify the text corresponding to the nickname
        // of the object the vCard represents.
        function setNickName($nickName = "") {
            $this->types["NICKNAME"] = trim($nickName);
        }
        function getNickName() {
            return $this->types["NICKNAME"];
        }

        // set/get BDAY type - To specify the birth date of the object the vCard represents.
        // internal date representation in ISO8601 format, ex. 2002-03-22
        function setBirthDate($BirthYear = "", $BirthMonth = "", $BirthDay = "") {
            if (checkdate($BirthMonth, $BirthDay, $BirthYear)) {
                $this->types["BDAY"] = $BirthYear."-".$BirthMonth."-".$BirthDay;
            } else {
                $this->lasterror_msg = "Wrong Birthday format.";
                $this->lasterror_num = 10;
            }
        }
        // $attr: "DAY", "MONTH", "YEAR", "BDATE"
        function getBirthDate($attr = "BDATE") {
            $output = "";
            if ($this->types["BDAY"]) {
                $BirthDay = str_replace("-", "", $this->types["BDAY"]);
                if (strlen($BirthDay) >= 8) {
                    $year = substr($BirthDay, 0, 4);
                    $month = substr($BirthDay, 4, 2);
                    $day = substr($BirthDay, 6, 2);
                    if (checkdate($month, $day, $year)) {
                        $this->types["BDAY"] = $year."-".$month."-".$day;
                        switch (strtoupper($attr)) {
                            case "DAY":
                                $output = $day;
                                break;
                            case "MONTH":
                                $output = $month;
                                break;
                            case "YEAR":
                                $output = $year;
                                break;
                            default:
                                $output = $this->types["BDAY"];
                        }
                    } else {
                        $this->lasterror_msg = "Wrong Birthday format.";
                        $this->lasterror_num = 11;
                    }
                } else {
                    $this->lasterror_msg = "Wrong Birthday format.";
                    $this->lasterror_num = 12;
                }

            }
            return $output;
        }

        //****** DELIVERY ADDRESSING TYPES ************************************

        // set/get ADR type - To specify the components of the delivery address for the vCard object.
        // $attr: (string or array) "DOM", "INTL", "HOME", "WORK", "POSTAL", "PARCEL"
        function setAdr($pobox = "", $extended = "", $street = "", $city = "", $province = "", $postal = "", $country = "", $attr = "") {
            if (is_array($attr)) {
                sort($attr);
                reset($attr);
                $str_attr = implode("|", $attr);
            } else {
                $str_attr = ($attr)? $attr : "DOM|HOME|POSTAL|WORK";
            }
            if ($pobox || $extended || $street || $city || $province || $postal || $country) {
                $this->types["ADR|".strtoupper($str_attr)] = $pobox.";".$extended.";".$street.";".$city.";".$province.";".$postal.";".$country;
                $this->setLabel($pobox, $extended, $street, $city, $province, $postal, $country, $attr);
            }
        }
        // return array of address components, meet your criteria if $condition: "AND"
        // return single address component if $condition: ""
        // $component: "POBOX", "EXTENDED", "STREET", "CITY", "PROVINCE", "POSTAL", "COUNTRY"
        function getAdr($component = "COUNTRY", $attr = "", $condition = "") {
            if (is_array($attr)) {
                sort($attr);
                reset($attr);
                $str_attr = implode("|", $attr);
                $arr_attr = $attr;
            } else {
                $str_attr = ($attr)? $attr : "DOM|HOME|POSTAL|WORK";
                $arr_attr = explode("|", $str_attr);
            }
            if ($condition == "AND" || $condition == "OR") {
                $arr_res = $this->findTVP($arr_attr, "P", $condition);
                $arr_ret = array();
                reset($arr_res);
                while (list(, $value) = each($arr_res)) {
                    if (stristr($value, "ADR")) {
                        $arr_addr = explode(";", $this->types[$value]);
                        $arr_ret[$value] = "";
                        if (is_array($arr_addr)) {
                            $size_arr_addr = sizeof($arr_addr);
                            switch (strtoupper($component)) {
                                case "POBOX":
                                    if ($size_arr_addr > 0) $arr_ret[$value] = trim($arr_addr[0]);
                                    break;
                                case "EXTENDED":
                                    if ($size_arr_addr > 1) $arr_ret[$value] = trim($arr_addr[1]);
                                    break;
                                case "STREET":
                                    if ($size_arr_addr > 2) $arr_ret[$value] = trim($arr_addr[2]);
                                    break;
                                case "CITY":
                                    if ($size_arr_addr > 3) $arr_ret[$value] = trim($arr_addr[3]);
                                    break;
                                case "PROVINCE":
                                    if ($size_arr_addr > 4) $arr_ret[$value] = trim($arr_addr[4]);
                                    break;
                                case "POSTAL":
                                    if ($size_arr_addr > 5) $arr_ret[$value] = trim($arr_addr[5]);
                                    break;
                                default:
                                    if ($size_arr_addr > 6) $arr_ret[$value] = trim($arr_addr[6]);
                            }
                        }
                    }
                }
                return $arr_ret;
            } else {
                $arr_addr = explode(";", $this->types["ADR|".strtoupper($str_attr)]);
                $output = "";
                if (is_array($arr_addr)) {
                    $size_arr_addr = sizeof($arr_addr);
                    switch (strtoupper($component)) {
                        case "POBOX":
                            if ($size_arr_addr > 0) $output = trim($arr_addr[0]);
                            break;
                        case "EXTENDED":
                            if ($size_arr_addr > 1) $output = trim($arr_addr[1]);
                            break;
                        case "STREET":
                            if ($size_arr_addr > 2) $output = trim($arr_addr[2]);
                            break;
                        case "CITY":
                            if ($size_arr_addr > 3) $output = trim($arr_addr[3]);
                            break;
                        case "PROVINCE":
                            if ($size_arr_addr > 4) $output = trim($arr_addr[4]);
                            break;
                        case "POSTAL":
                            if ($size_arr_addr > 5) $output = trim($arr_addr[5]);
                            break;
                        default:
                            if ($size_arr_addr > 6) $output = trim($arr_addr[6]);
                    }
                }
                return $output;
            }
        }

        // set/get LABEL type - To specify the formatted text corresponding to delivery
        // address of the object the vCard represents.
        // $attr: (string or array) "DOM", "INTL", "HOME", "WORK", "POSTAL", "PARCEL"
        function setLabel($pobox = "", $extended = "", $street = "", $city = "", $province = "", $postal = "", $country = "", $attr = "") {
            $label = "";
            if (is_array($attr)) {
                sort($attr);
                reset($attr);
                $str_attr = implode("|", $attr);
            } else {
                $str_attr = ($attr)? $attr : "DOM|HOME|POSTAL|WORK";
            }
            if ($pobox) $label.= $pobox."\r\n";
            if ($extended) $label.= $extended."\r\n";
            if ($street) $label.= $street."\r\n";
            if ($city) $label.= $city.", ";
            if ($province) $label.= $province." ";
            if ($postal) $label.= $postal."\r\n";
            if ($country) $label.= $country;
            $this->types["LABEL|".strtoupper($str_attr)] = $label;
        }
        // return array of address labels, meet your criteria if $condition: "AND", "OR"
        // return single address label if $condition: ""
        function getLabel($attr = "", $condition = "") {
            if (is_array($attr)) {
                sort($attr);
                reset($attr);
                $str_attr = implode("|", $attr);
                $arr_attr = $attr;
            } else {
                $str_attr = ($attr)? $attr : "DOM|HOME|POSTAL|WORK";
                $arr_attr = explode("|", $str_attr);
            }
            if ($condition == "AND" || $condition == "OR") {
                $arr_res = $this->findTVP($arr_attr, "P", $condition);
                $arr_ret = array();
                reset($arr_res);
                while (list(, $value) = each($arr_res))
                    if (stristr($value, "LABEL")) $arr_ret[$value] = $this->types[$value];
                return $arr_ret;
            } else {
                return $this->types["LABEL|".strtoupper($str_attr)];
            }
        }

        //****** TELECOMMUNICATIONS ADDRESSING TYPES *****************************

        // set/get TEL type - To specify the telephone number for telephony
        // communication with the object the vCard represents.
        // $attr: (string or array) "PREF", "WORK", "HOME", "VOICE", "FAX", "MSG", "CELL", "PAGER", "BBS", "CAR", "MODEM", "ISDN", "VIDEO", "PCS"
        function setTel($tel = "", $attr = "") {
            if (is_array($attr)) {
                sort($attr);
                reset($attr);
                $str_attr = implode("|", $attr);
            } else {
                $str_attr = ($attr)? $attr : "FAX|HOME|VOICE|WORK";
            }
            $this->types["TEL|".strtoupper($str_attr)] = trim($tel);
        }
        // return array of phone numbers, meet your criteria if $condition: "AND", "OR"
        // return single phone number if $condition: ""
        function getTel($attr = "", $condition = "") {
            if (is_array($attr)) {
                sort($attr);
                reset($attr);
                $str_attr = implode("|", $attr);
                $arr_attr = $attr;
            } else {
                $str_attr = ($attr)? $attr : "FAX|HOME|VOICE|WORK";
                $arr_attr = explode("|", $str_attr);
            }
            if ($condition == "AND" || $condition == "OR") {
                $arr_res = $this->findTVP($arr_attr, "P", $condition);
                $arr_ret = array();
                reset($arr_res);
                while (list(, $value) = each($arr_res))
                    if (stristr($value, "TEL")) $arr_ret[$value] = $this->types[$value];
                return $arr_ret;
            } else {
                return $this->types["TEL|".strtoupper($str_attr)];
            }
        }

        // set/get EMAIL type - To specify the electronic mail address for
        // communication with the object the vCard represents.
        // $attr: (string or array) "INTERNET", "X400", "PREF"(default e-mail), ...
        function setEmail($email = "", $attr = "INTERNET") {
            if (is_array($attr)) {
                sort($attr);
                reset($attr);
                $str_attr = implode("|", $attr);
            } else {
                $str_attr = ($attr)? $attr : "INTERNET|PREF";
            }
            $this->types["EMAIL|".strtoupper($str_attr)] = trim($email);
        }
        // return array of e-mails, meet your criteria if $condition: "AND", "OR"
        // return single e-mail if $condition: ""
        function getEmail($attr = "INTERNET", $condition = "") {
            if (is_array($attr)) {
                sort($attr);
                reset($attr);
                $str_attr = implode("|", $attr);
                $arr_attr = $attr;
            } else {
                $str_attr = ($attr)? $attr : "INTERNET|PREF";
                $arr_attr = explode("|", $str_attr);
            }
            if ($condition == "AND" || $condition == "OR") {
                $arr_res = $this->findTVP($arr_attr, "P", $condition);
                $arr_ret = array();
                reset($arr_res);
                while (list(, $value) = each($arr_res))
                    if (stristr($value, "EMAIL")) $arr_ret[$value] = $this->types[$value];
                return $arr_ret;
            } else {
                return $this->types["EMAIL|".strtoupper($str_attr)];
            }
        }

        // set/get MAILER type - To specify the type of electronic mail software
        // that is used by the individual associated with the vCard.
        function setMailer($new_mailer) {
            $this->types["MAILER"] = $new_mailer;
        }
        function getMailer() {
            return $this->types["MAILER"];
        }

        //****** GEOGRAPHICAL TYPES ******************************************

        // set/get TZ type - To specify information related to the time zone of the
        // object the vCard represents.
        function setTZ($tz = "") {
            $this->types["TZ"] = trim($tz);
        }
        function getTZ() {
            return $this->types["TZ"];
        }

        // set/get GEO type - To specify information related to the global
        // positioning of the object the vCard represents.
        function setGeo($geo = "") {
            $this->types["GEO"] = trim($geo);
        }
        function getGeo() {
            return $this->types["GEO"];
        }

        //****** ORGANIZATIONAL TYPES ***************************************

        // set/get TITLE type - To specify the job title, functional position or
        // function of the object the vCard represents.
        function setTitle($title = "") {
            $this->types["TITLE"] = trim($title);
        }
        function getTitle() {
            return $this->types["TITLE"];
        }

        // set/get ROLE type - To specify information concerning the role, occupation,
        // or business category of the object the vCard represents.
        function setRole($role = "") {
            $this->types["ROLE"] = trim($role);
        }
        function getRole() {
            return $this->types["ROLE"];
        }

        // set/get ORG type - To specify the organizational name and units associated with the vCard.
        function setOrg($orgName = "", $orgUnit = "", $orgUnits = "") {
            if ($orgName || $orgUnit || $orgUnits) {
                $org = trim($orgName).";";
                $org.= trim($orgUnit);
                $org.= (trim($orgUnits))? ";".trim($orgUnits) : "";
                $this->types["ORG"] = $org;
            }
        }
        // $attr: "ORGNAME", "ORGUNIT", "ORGUNITS", "ALL"
        function getOrg($attr = "ORGNAME") {
            $output = "";
            $arr_org = explode (";", $this->types["ORG"]);
            $size_arr_org = sizeof($arr_org);
            switch (strtoupper($attr)) {
                case "ORGNAME":
                    $output = ($size_arr_org)? $arr_org[0] : $this->types["ORG"];
                    break;
                case "ORGUNIT":
                    if ($size_arr_org > 1) $output = $arr_org[1];
                    break;
                case "ORGUNITS":
                    if ($size_arr_org > 2) {
                        for ($k = 2; $k < $size_arr_org; $k++)
                            $output.= $arr_org[$k].";";
                        $output = substr($output, 0, strlen($output) - 1);
                    }
                    break;
                default:
                    $output = $this->types["ORG"];
            }
            return trim($output);
        }

        //****** EXPLANATORY TYPES ********************************************

        // set/get CATEGORIES type - To specify application category information about the vCard.
        function setCategories($categories = "") {
            $this->types["CATEGORIES"] = trim($categories);
        }
        function getCategories() {
            return $this->types["CATEGORIES"];
        }

        // set/get NOTE type - To specify supplemental information or a comment that
        // is associated with the vCard.
        function setNote($note = "") {
            $this->types["NOTE"] = trim($note);
        }
        function getNote() {
            return $this->types["NOTE"];
        }

        // set/get PRODID type - To specify the identifier for the product that created the vCard object.
        function setProdID($prodID = "") {
            $this->types["PRODID"] = trim($prodID);
        }
        function getProdID() {
            return $this->types["PRODID"];
        }

        // set/get REV type - To specify revision information about the current vCard.
        function setRevision() {
            $this->types["REV"] = date("Y-m-d")."T".date("H:i:s")."Z";
        }
        function getRevision() {
            return $this->types["REV"];
        }

        // set/get SORT-STRING type - To specify the family name or given name text to be
        // used for national-language-specific sorting of the FN and N types.
        function setSortString($sortString = "") {
            $this->types["SORT-STRING"] = trim($sortString);
        }
        function getSortString() {
            if (!isset($this->types["SORT-STRING"])) {
                return null;
            }
            
            return $this->types["SORT-STRING"];
        }

        // set/get UID type - To specify a value that represents a globally unique identifier
        // corresponding to the individual or resource associated with the vCard.
        function setUID($UID = "") {
            $this->types["UID"] = trim($UID);
        }
        function getUID() {
            return $this->types["UID"];
        }

        // set/get URL type - To specify a uniform resource locator associated with
        // the object that the vCard refers to.
        // $attr: "", "WORK", "HOME" (assume "" is "WORK" url)
        function setUrl($url = "", $attr = "") {
            if (strtoupper($attr) == "HOME") {
                $this->types["URL|HOME"] = trim($url);
            } elseif (strtoupper($attr) == "WORK") {
                if ($this->types["URL|WORK"])
                    $this->types["URL|WORK"] = trim($url);
                elseif ($this->types["URL"])
                    $this->types["URL"] = trim($url);
                else
                    $this->types["URL"] = trim($url);
            } else {
                if ($this->types["URL"])
                    $this->types["URL"] = trim($url);
                elseif ($this->types["URL|WORK"])
                    $this->types["URL|WORK"] = trim($url);
                else
                    $this->types["URL"] = trim($url);
            }
        }
        function getUrl($attr = "") {
            if (strtoupper($attr) == "HOME")
                return $this->types["URL|HOME"];
            else
                return ($this->types["URL"])? $this->types["URL"] : $this->types["URL|WORK"];
        }

        // set/get VERSION type - To specify the version of the vCard specification
        // used to format this vCard.
        function setVersion($new_version) {
            if ($new_version == "2.1" || $new_version == "3.0") {
                $this->types["VERSION"] = $new_version;
            } else {
                $this->lasterror_msg = "Can't parse vCard version specified.";
                $this->lasterror_num = 1;
            }
        }
        function getVersion() {
            return $this->types["VERSION"];
        }

        //****** SECURITY TYPES ************************************************

        // set/get CLASS type - To specify the access classification for a vCard object.
        // ex. $class: "PUBLIC", "PRIVATE", "CONFIDENTIAL"
        function setClass($class = "") {
            $this->types["CLASS"] = trim($class);
        }
        function getClass() {
            return $this->types["CLASS"];
        }

        // set/get KEY type - To specify a public key or authentication certificate
        // associated with the object that the vCard represents.
        // $attr: "X509", "PGP"
        function setKey($key = "", $attr = "X509") {
            $arr_attr[] = "ENCODING=BASE64";
            if ($attr) $arr_attr[] = $attr;
            sort($arr_attr);
            reset($arr_attr);
            $params = implode("|", $arr_attr);
            $this->types["KEY|".strtoupper($params)] = trim($key);
        }
        function getKey($attr = "X509") {
            $arr_res = $this->findTVP($attr, "P");
            reset($arr_res);
            $ret_str = "";
            while (list(, $value) = each($arr_res)) {
                if (stristr($value, "KEY")) {
                    $ret_str = $this->types[$value];
                    break;
                }
            }
            return $ret_str;
        }

        //****** EXTENDED TYPES ************************************************

        // set/get X- type - To specify non-standard information related to the
        // object the vCard represents. (TripleX, heh)
        function setXXX($value = "", $attr = "") {
            $this->types["X-".strtoupper($attr)] = trim($value);
        }
        function getXXX($attr = "") {
            return $this->types["X-".strtoupper($attr)];
        }

        //****** BINARY TYPES **************************************************

        // set/get PHOTO, LOGO or SOUND types - see RFC for description
        // $type: "PHOTO", "LOGO", "SOUND"
        // if $value: base64(bynary data) - $attr: "GIF", "WMF", "BMP", "TIFF", "PDF", "JPEG", "MPEG", "AVI", "WAVE", "PCM", "AIFF" ...
        // if $value: URL - $attr: "URL"
        function setBinary($type = "PHOTO", $value = "", $attr = "JPEG") {
            if (strtoupper($attr) == "URL") {
                $this->types[strtoupper($type)."|VALUE=URL"] = trim($value);
            } else {
                $arr_attr[] = "ENCODING=BASE64";
                if ($attr) $arr_attr[] = strtoupper($attr);
                sort($arr_attr);
                reset($arr_attr);
                $params = implode("|", $arr_attr);
                $this->types[strtoupper($type)."|".$params] = trim($value);

            }
        }
        function getBinary($type = "PHOTO", $attr = "") {
            if (strtoupper($attr) == "URL") {
                return $this->types[strtoupper($type)."|VALUE=URL"];
            } else {
                $arr_res = $this->findTVP("ENCODING=BASE64", "P");
                reset($arr_res);
                $ret_str = "";
                while (list(, $value) = each($arr_res)) {
                    if (stristr($value, strtoupper($type))) {
                        $ret_str = $this->types[$value];
                        break;
                    }
                }
                return $ret_str;
            }
        }

        //****** END OF TYPES **************************************************

        // decode/encode any string according version number
        function decode($input) {
            if ($this->getVersion() == "3.0")
                return str_replace("\;", ";", str_replace("\,", ",", $input));
            if ($this->getVersion() == "2.1")
                return str_replace("\;", ";", quoted_printable_decode($input));
        }
        function encode($input) {
            if ($this->getVersion() == "3.0")
                return str_replace(";", "\;", str_replace(",", "\,", $input));
            if ($this->getVersion() == "2.1")
                return str_replace(";", "\;", $this->quoted_printable_encode($input));
        }

        // function source from - http://www.php.net/manual/en/function.quoted-printable-decode.php
        function quoted_printable_encode($input = "", $line_max = 76) {
            $hex = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
            $lines = preg_split("/(?:\r\n|\r|\n)/", $input);
            $output = "";
            $size_lines = sizeof($lines);
            for ($k = 0; $k < $size_lines; $k++) {
                $linlen = strlen($lines[$k]);
                $newline = "";
                for($i = 0; $i < $linlen; $i++) {
                    $c = substr($lines[$k], $i, 1);
                    $dec = ord($c);
                    if ( ($dec == 32) && ($i == ($linlen - 1)) ) { // convert space at new line only
                        $c = "=20";
                    } elseif ( ($dec == 61) || ($dec < 32 ) || ($dec > 126) ) {
                        $h2 = floor($dec/16);
                        $h1 = floor($dec%16);
                        $c = "=".$hex["$h2"].$hex["$h1"];
                    }
                    if ( (strlen($newline) + strlen($c)) >= $line_max ) {
                        $output.= $newline."=\r\n"; // soft line break; " =\r\n" is okay
                        $newline = "";
                    }
                    $newline .= $c;
                }
                $output.= ($k < $size_lines-1)? $newline."=0D=0A" : $newline;
            }
            return trim($output);
        }

        // get value of Type, Parameter(s) or it's Value from vCard line
        // $param: "T"-type, "P"-parameter(s), "V"-value, "TP"- type and parameter(s)
        function _getTVP($input, $TVPattr = "T") {
            if (strstr($input, ":")) {
                if ($TVPattr == "T" || $TVPattr == "P" || $TVPattr == "TP") {
                    $fulltype = strtoupper(substr($input, 0, strpos($input, ":")));
                    if ($TVPattr == "T")
                        return (strstr($fulltype, ";"))? substr($fulltype, 0, strpos($fulltype, ";")) : trim($fulltype);
                    elseif ($TVPattr == "P")
                        return (strstr($fulltype, ";"))? str_replace(",", ";", str_replace("TYPE=", "", substr(strstr($fulltype, ";"), 1))) : "";
                    else
                        return str_replace(",", ";", str_replace("TYPE=", "", $fulltype));
                } else {
                    return trim(substr(strstr($input, ":"), 1));
                }
            } else {
                $this->lasterror_msg = "Wrong vCard format.";
                $this->lasterror_num = 2;
                return false;
            }
        }

        // private function to set internal key representation of $types(), such as $type|$param1|$param2...
        // $input - may be $type or array of mix $type and parameters
        // Note: first element of input array MUST be $type
        function _setInternalType($input = "") {
            if (is_array($input)) {
                $type = $input[0];
                $size_input = sizeof($input);
                if ($size_input > 1) {
                    for ($k = 1; $k < $size_input; $k++)
                        if ($input[$k] != "ENCODING=QUOTED-PRINTABLE")
                            $arr_params[] = ($input[$k] == "ENCODING=B")? "ENCODING=BASE64" : $input[$k];
                    if (is_array($arr_params)) {
                        sort($arr_params); // sort in alphabetical order
                        reset($arr_params);
                        $params = implode("|", $arr_params);
                    }
                }
                if ($params) return $type."|".$params;
                else return $type;
            } else {
                return $input;
            }
        }

        // private function to get $type or $parameters by internal key representation
        // $input - string internal key representation, $attr - what retrive: "T"-type, "P"-parameter(s), "TP"- type and parameter(s)
        // Note: several parameters will be separated with ";"
        function _getInternalType($input = "", $attr = "T") {
            $pos = strpos($input, "|");
            if ($attr == "P")
                return ($pos)? str_replace("|", ";", substr($input, $pos + 1)) : "";
            elseif ($attr == "T")
                return ($pos)? substr($input, 0, $pos) : $input;
            else
                return str_replace("|", ";", $input);
        }
    }

} // if ( !defined("VCARDCLASS_INC") )
