<?php
/**
 * PHPFrame/UI/HTMLUI.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   UI
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * This class provides a number of static methods to be used for generating
 * useful HTML elements and Javascript.
 *
 * This class is mostly used in the views tmpl layer for quickly building
 * dialogs, form validation, calendars, and so on.
 *
 * @category PHPFrame
 * @package  UI
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_HTMLUI
{
    /**
     * Create an HTML input field.
     *
     * @param string $name      The input name.
     * @param string $value     [Optional] The initial value for the field.
     * @param string $type      [Optional] The input type.
     * @param int    $size      [Optional] The field's display width.
     * @param int    $maxlength [Optional] The maximum length allowed by field.
     *
     * @return string
     * @since  1.0
     */
    public static function input(
        $name,
        $value=null,
        $type="text",
        $size=null,
        $maxlength=null
    ) {
        $str  = '<input type="'.$type.'" name="'.$name.'"';
        $str .= ' value="'.$value.'"';

        if (!is_null($size)) {
            $str .= ' size="'.$size.'"';
        }

        if (!is_null($maxlength)) {
            $str .= ' maxlength="'.$maxlength.'"';
        } elseif (!is_null($size)) {
            $str .= ' maxlength="'.$size.'"';
        }

        $str .= " />\n";

        return $str;
    }

    /**
     * Create an HTML text area.
     *
     * @param string $name  The name for the text area.
     * @param string $value [Optional]
     * @param int    $cols  [Optional]
     * @param int    $rows  [Optional]
     *
     * @return string
     * @since  1.0
     */
    public static function textarea(
        $name,
        $value=null,
        $cols=50,
        $rows=10
    ) {
        $str  = '<textarea name="'.$name.'"';
        $str .= ' cols="'.(int) $cols.'"';
        $str .= ' rows="'.(int) $rows.'"';
        $str .= ">".$value."</textarea>\n";

        return $str;
    }

    /**
     * Create an HTML radio button.
     *
     * @param string $name    The name for the radio button.
     * @param array  $options An associative array with the key value pairs for
     *                        the different radio tags.
     * @param string $value   [Optional]
     *
     * @return string
     * @since  1.0
     */
    public static function radio(
        $name,
        array $options,
        $value=null
    ) {
        $str = "";

        $i = 0;
        foreach ($options as $k=>$v) {
            $str .= "<label for=\"".$name.$i."\">".$k."</label>\n";
            $str .= "<input type=\"radio\" name=\"".$name."\" id=\"".$name.$i."\"";
            $str .= " value=\"".$v."\"";

            if (!is_null($value) && $v == $value) {
                $str .= " checked";
            }

            $str .= " />\n";
            $i++;
        }

        return $str;
    }

    /**
     * Create an HTML select tag.
     *
     * @param string $name    The name for the select field.
     * @param array  $options An associative array with the key value pairs for
      *                       the different option tags.
     * @param string $value   [Optional]
     *
     * @return string
     * @since  1.0
     */
    public static function select(
        $name,
        array $options,
        $value=null
    ) {
        $str  = "<select name=\"".$name."\">\n";

        foreach ($options as $k=>$v) {
            $str .= "<option value=\"".$v."\"";

            if (!is_null($value) && $v == $value) {
                $str .= " selected";
            }

            $str .= ">".$k."</option>\n";
        }

        $str .= "</select>";

        return $str;
    }

    /**
     * Build a jQuery UI dialog box with content loaded via AJAX.
     *
     * @param string $label     A string to print inside de link tag.
     * @param string $target    The target URL to load via AJAX.
     * @param int    $width     The dialog box width
     * @param int    $height    The dialog box height
     * @param bool   $form      A boolean to indicate whether the dialog
     *                          contains a form in order to include submit
     *                          button.
     * @param string $ajax_cont A jQuery selector string to select the HTML
     *                          element where to load the AJAX response. This
     *                          parameter is optional, if omitted the browser
     *                          window will be redirected to the link's href
     *                          instead of using an AJAX request.
     *
     * @return string
     * @since  1.0
     */
    public static function dialog(
        $label,
        $target,
        $width=600,
        $height=560,
        $form=false,
        $ajax_cont=''
    ) {
        $uid = uniqid();

        // Start buffering
        ob_start();
        ?>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Add a div element at the end of the body to show dialog
            var dialog_div = '<div style="position: absolute" ';
            dialog_div += 'id="dialog_<?php echo $uid; ?>"></div>';
            $("body").append(dialog_div);

            // Selet the dialog div with jQuery and cache it in dialog_div
            dialog_div = $("#dialog_<?php echo $uid; ?>");

            // Add the loading div inside the newly created dialog box
            var loading_html = '<div class="loading">Loading...</div>';
            dialog_div.html(loading_html);

            // Add dialog beaviour to new dialog box
            dialog_div.dialog({
                autoOpen: false,
                bgiframe: false,
                width: <?php echo $width; ?>,
                height: <?php echo $height; ?>,
                modal: true,
                resizable: false,
                buttons: {
                    <?php if ($form) : ?>
                    "Save" : function() {
                        var form = $(this).find("form");
                        // Submit form and close the dialog if form is valid
                        if (form.valid()) {
                            <?php if (!empty($ajax_cont)) : ?>
                            var ajax_cont = $("<?php echo $ajax_cont; ?>");
                            // Add the loading div inside the ajax container
                            ajax_cont.html(loading_html);
                            // bind form using 'ajaxForm'
                            form.ajaxForm({ target: ajax_cont });
                            <?php endif; ?>
                            form.submit();
                            $(this).dialog('close');
                            $(this).empty();
                        }
                        else {
                            return false;
                        }
                    },
                    <?php endif; ?>
                    "Close" : function() {
                        $(this).dialog('close');
                        $(this).empty();
                    }
                }

            });

            <?php if (!empty($ajax_cont)) : ?>
            // Bind AJAX events to loading div to show/hide animation
            $(".loading").bind("ajaxSend", function() {
                $(this).show();
            })
            .bind("ajaxComplete", function() {
                $(this).hide();
            });
            <?php endif; ?>

            // Set up the onclick trigger for the dialog box
            $('#dialog_trigger_<?php echo $uid; ?>').click(function(e) {
                e.preventDefault();
                dialog_div.css({ "position" : "relative" });
                dialog_div.load("<?php echo $target; ?>", {ajax: 1});
                dialog_div.dialog('open');
            });
        });
        </script>

        <a id="dialog_trigger_<?php echo $uid; ?>"
           href="<?php echo $target; ?>">
            <?php echo $label; ?>
        </a>

        <?php
        // Save buffer
        $str = ob_get_contents();
        // Clear buffer
        ob_end_clean();

        return $str;
    }

    /**
     * Build and display a jQuery UI confirm box
     *
     * To use the confirm behaviour you will need to create anchor tags and
     * give them a class, title and href attributes.
     *
     * For example:
     *
     * <code>
     * <?php
     * echo PHPFrame_HTMLUI::confirm(
     *     'delete_entry',
     *     'Delete entry',
     *     'Are you sure you want to delete entry'
     * );
     * ?>
     *
     * <a class="delete_entry"
     *    title="The name of the entry we are deleting"
     *    href="The URL to go if user confirms action">
     * </code>
     *
     * @param string $a_class   The class attribute used to select the delete
     *                          links.
     * @param string $title     A string to use in the dialog box title bar.
     * @param string $msg       A string with the message to display in the
     *                          confirm box.
     * @param string $ajax_cont A jQuery selector string to select the HTML
     *                          element where to load the AJAX response. This
     *                          parameter is optional, if omitted the browser
     *                          window will be redirected to the link's href
     *                          instead of using an AJAX request.
     *
     * @return string
     * @since  1.0
     */
    public static function confirm($a_class, $title, $msg, $ajax_cont='')
    {
        $uid = uniqid();

        // Start buffering
        ob_start();
        ?>

        <script language="javascript" type="text/javascript">
        // Declare confirm_href and confirm_title.
        var confirm_href_<?php echo $uid; ?>;
        var confirm_title_<?php echo $uid; ?>;
        <?php if (!empty($ajax_cont)) : ?>

        var confirm_response_container_id_<?php echo $uid; ?>;
        <?php endif; ?>

        jQuery(document).ready(function($) {
            // Dinamically add an HTML element to show the confirmation dialog
            // at the end of the body
            var dialog_html = '<div id="confirm_dialog_<?php echo $uid; ?>" ';
            dialog_html += 'title="<?php echo $title; ?>"></div>'
            $("body").append(dialog_html);

            // Selet the dialog div with jQuery and cache it in dialog_div
            confirm_dialog_div = $("#confirm_dialog_<?php echo $uid; ?>");

            // Add dialog behaviour to the confirm box
            confirm_dialog_div.dialog({
                autoOpen: false,
                bgiframe: true,
                resizable: false,
                height:140,
                modal: true,
                overlay: {
                    backgroundColor: '#000',
                    opacity: 0.5
                },
                buttons: {
                    'Ok': function() {
                        <?php if (!empty($ajax_cont)) : ?>
                        // Add the loading div inside the ajax container
                        $("#"+confirm_response_container_id_<?php echo $uid; ?>)
                        .html('<div class="loading"></div>')
                        .load(confirm_href_<?php echo $uid; ?> + '&ajax=1');
                        <?php else : ?>
                        window.location = confirm_href_<?php echo $uid; ?>;
                        <?php endif; ?>
                        $(this).dialog('close');
                    },
                    Cancel: function() {
                        $(this).dialog('close');
                    }
                }
            });

            <?php if (!empty($ajax_cont)) : ?>
            // Bind AJAX events to loading div to show/hide animation
            $(".loading").bind("ajaxSend", function() {
                $(this).show();
            })
            .bind("ajaxComplete", function() {
                   $(this).hide();
            });
            <?php endif; ?>

            // Override onclick trigger for delete links
            $("a.<?php echo $a_class; ?>").click(function(e) {
                // Prevent element's default onclick
                e.preventDefault();

                // Get href from current link
                confirm_href_<?php echo $uid; ?>  = $(this).attr("href");
                confirm_title_<?php echo $uid; ?> = $(this).attr("title");

                // Get row id from href
                var pattern = /id=(.*)$/;
                var id = confirm_href_<?php echo $uid; ?>.match(pattern)[1];

                <?php if (!empty($ajax_cont)) : ?>
                // Find the element where we want to load the AJAX response
                confirm_response_container_id_<?php echo $uid; ?> =
                    $("<?php echo $ajax_cont; ?>").attr('id');
                <?php endif; ?>

                var confirm_dialog_div_html = '<?php echo $msg; ?> "';
                confirm_dialog_div_html += confirm_title_<?php echo $uid; ?>;
                confirm_dialog_div_html +=  '"?';

                confirm_dialog_div.html(confirm_dialog_div_html).dialog('open');
            });
        });
        </script>

        <?php
        // Save buffer
        $str = ob_get_contents();
        // Clear buffer
        ob_end_clean();

        return $str;
    }

    /**
     * Build a date picker using jQuery UI Calendar and display it
     *
     * This method will generate two input tags, one is shown to the user and
     * it triggers the date picker, and the other one holding the date value in
     * MySQL date format to be used for storing.
     *
     * @param string $name             The name attribute for the input tag.
     * @param string $id               The id of the input tag
     * @param string $selected         The selected value if any. In YYYY-MM-DD.
     * @param string $format           Format in which to present the date to
     *                                 the user. Possible values 'dd/mm/yy',
     *                                 'mm/dd/yy', 'yy/mm/dd'. This doesn't
     *                                 affect the hidden input value with the
     *                                 MySQL date.
     * @param array  $attribs          An array containing attributes for the
     *                                 input tag.
     * @param bool   $show_format_hint Show/hide date format hint.
     *
     * @return string
     * @since  1.0
     */
    public static function calendar(
        $name,
        $id="",
        $selected="",
        $format="dd/mm/yy",
        $attribs=array(),
        $show_format_hint=false
    ) {
        //set $id to $name if empty
        if (empty($id)) {
            $id = $name;
        }

        // Convert attributes array to string
        $attribs_str = '';
        if (is_array($attribs) && count($attribs) > 0) {
            foreach ($attribs as $key=>$value) {
                $attribs_str .= $key.'="'.$value.'" ';
            }
        }

        // Format selected value using format parameter
        $formatted_date = '';
        if ($selected) {
            $formatted_date_array = explode('-', $selected);
            $format_array = explode('/', $format);
            foreach ($format_array as $format_item) {
                switch ($format_item) {
                case 'yy' :
                    $key = 0;
                    break;
                case 'mm' :
                    $key = 1;
                    break;
                case 'dd' :
                    $key = 2;
                    break;
                }

                if (!empty($formatted_date)) {
                    $formatted_date .= '/';
                }

                $formatted_date .= $formatted_date_array[$key];
            }
        }

        // Start buffering
        ob_start();
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#<?php echo $id; ?>_datePicker').datepicker({
                inline: true,
                <?php if ($show_format_hint) : ?>
                appendText: '(dd/mm/yyyy)',
                <?php endif; ?>
                dateFormat: '<?php echo $format; ?>',
                altField: '#<?php echo $id; ?>',
                altFormat: 'yy-mm-dd'
            });
        });
        </script>
        <input
            id="<?php echo $id; ?>_datePicker"
            type="text"
            name="<?php echo $name; ?>_datePicker" <?php echo $attribs_str; ?>
            value="<?php echo $formatted_date; ?>"
            autocomplete="off"
            size="10"
            maxlength="10"
        />
        <input
            id="<?php echo $id; ?>"
            type="hidden"
            name="<?php echo $name; ?>"
            value="<?php echo $selected; ?>"
        />
        <?php
        // Save buffer
        $str = ob_get_contents();
        // Clear buffer
        ob_end_clean();

        return $str;
    }

    /**
     * Displays a hidden token field to reduce the risk of CSRF exploits.
     *
     * @return string
     * @since  1.0
     */
    public static function formToken()
    {
        $token = PHPFrame::getSession()->getToken();
        return '<input type="hidden" name="'.$token.'" value="1" />';
    }

    /**
     * Build an HTML form to be used with the given persistent object.
     *
     * @param PHPFrame_PersistentObject $obj          Instance of a persistent
     *                                                object.
     * @param string                    $action       Form 'action' attribute.
     * @param string                    $submit_label [Optional] Label for
     *                                                submit button. Default
     *                                                value is 'Submit'.
     * @param array                     $exclude      [Optional] Array of field
     *                                                names to exclude.
     *
     * @return string
     * @since  1.0
     */
    public static function persistentObjectToForm(
        PHPFrame_PersistentObject $obj,
        $action,
        $submit_label="Submit",
        array $exclude=array()
    ) {
        $str = "<form action=\"".$action."\" method=\"post\">\n";

        $filters = $obj->getFilters();
        $values  = iterator_to_array($obj);

        foreach ($filters as $key=>$value) {
            $ignore = array(
              "id",
              "ctime",
              "mtime",
              "owner",
              "group",
              "perms"
            );

            if (in_array($key, array_merge($exclude, $ignore))) {
                continue;
            }

            $str .= "<div>\n";
            $str .= "<label for=\"".$key."\">".$key."</label>\n";

            if ($value instanceof PHPFrame_BoolFilter) {

                $str .= self::radio(
                    $key,
                    array("Yes"=>1, "No"=>0),
                    $values[$key]
                );

            } elseif ($value instanceof PHPFrame_IntFilter) {

                $max_range = $value->getOption("max_range");
                $min_range = $value->getOption("min_range");

                if ($max_range <= 0) {
                    $max_range = null;
                } else {
                    $max_length = (strlen($max_range) > strlen($min_range))
                                  ? (strlen($max_range)+1)
                                  : (strlen($min_range)+1);
                }

                $str .= self::input($key, $values[$key], "text", $max_length);

            } elseif ($value instanceof PHPFrame_FloatFilter) {

                $str .= self::input($key, $values[$key], "text");

            } elseif ($value instanceof PHPFrame_StringFilter) {

                $max_length = $value->getOption("max_length");

                if ($max_length <= 0) {
                    $str .= self::textarea($key, $values[$key]);
                } else {
                    $str .= self::input($key, $values[$key], "text", $max_length);
                }

            } elseif ($value instanceof PHPFrame_EnumFilter) {

                $enums = $value->getOption('enums');
                $str  .= self::select($key, $enums, $values[$key]);

            }

            $str .= "</div>\n";
        }

        $str .= "<br />\n";
        $str .= "<input type=\"submit\" value=\"".$submit_label."\" />\n";
        $str .= "<input type=\"hidden\" name=\"id\" ";
        $str .= "value=\"".$obj->id()."\" />\n";
        $str .= "</form>\n";

        return $str;
    }
}
