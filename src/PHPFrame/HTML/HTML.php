<?php
/**
 * PHPFrame/HTML/HTML.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage HTML
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * HTML Class
 * 
 * This class provides a number of static methods to be used for generating useful HTML elements and Javascript.
 * This class is mostly used in the views tmpl layer for quickly building buttons, calendars, autocomleters, and so on.
 * 
 * All methods in this class are static.
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage HTML
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_HTML
{
    /**
     * Build an select option object
     * 
     * @param    string    $value The option value
     * @param    string    $label The option label
     * @return     object    A standard object with the passed label and value as properties.
     * @since     1.0
     */
    public static function selectOption($value, $label) 
    {
        $option = new PHPFrame_Base_StdObject();
        $option->value = $value;
        $option->label = $label;
        
        return $option;
    }
    
    /**
     * Build a generic select tag.
     * 
     * @param    array    $options    An array of option objects
     * @param    string    $name        A string to use in the name attribute of the select tag.
     * @param    string    $attribs    A string containing standard HTML attributes for the select tag. ie: 'class="myClass" multiple="multiple"'
     * @param     string    $selected    The selected value. This parameter is optional.
     * @return    void
     * @since     1.0
     */
    public static function selectGenericlist($options, $name, $attribs, $selected=NULL) 
    {
        $html = '<select name="'.$name.'" '.$attribs.'>';
        foreach ($options as $option) {
            $html .= '<option value="'.$option->value.'"';
            if ($option->value == $selected) {
                $html .= ' selected';
            }
            $html .= '>';
            $html .= $option->label;
            $html .= '</option>';
        }
        $html .= '</select>';
        
        echo $html;
    }
    
    /**
     * Add jQuery validation behaviour to a given form
     * 
     * @access    public
     * @param    string    $formid    The form's id attribute.
     * @return    void
     * @since    1.0
     */
    public static function validate($formid) 
    {
        ?>
        
        <script type="text/javascript">  
        $(function() {
            $('#<?php echo $formid; ?>').validate({
                rules: {
                    password2: {
                        equalTo: "#password"
                    }
                },

                highlight: function(element, errorClass) {
                    $(element).fadeOut(function() {
                        $(element).fadeIn()
                    })
                    $(element).addClass('error');
                }
            });
        });
        </script>
        
        <?php
    }
    
    /**
     * Build and display a jQuery UI dialog box with content loaded via AJAX.
     * 
     * @param    string    $label    A string to print inside de link tag.
     * @param    string    $target The target URL to load via AJAX.
     * @param    int        $width    The dialog box width
     * @param    int        $height    The dialog box height
     * @param    bool    $form    A boolean to indicate whether the dialog contains a form in order to include
     *                             submit buton.
     * @param    string    $ajax_container    A jQuery selector string to select the HTML element where to load 
     *                                     the AJAX response. This parameter is optional, if omitted the browser 
     *                                     window will be redirected to the link's href instead of using an AJAX request.
     * @return    void
     * @since     1.0
     */
    public static function dialog($label, 
                                  $target, 
                                  $width=600, 
                                  $height=560, 
                                  $form=false, 
                                  $ajax_container='') 
    {
        $uid = uniqid();
        ?>
        
        <script type="text/javascript">
        $(document).ready(function() {

            // Dynamically add an HTML element at the end of the body to show the dialog
            $("body").append('<div style="position: absolute" id="dialog_<?php echo $uid; ?>" title="<?php echo $label; ?>"></div>');
            // Add the loading div inside the newly created dialog box
            $("#dialog_<?php echo $uid; ?>").html('<div class="loading"></div>');
            
            // Add dialog beaviour to new dialog box
            $("#dialog_<?php echo $uid; ?>").dialog({
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
                            <?php if (!empty($ajax_container)) : ?>
                            var ajax_container = $("<?php echo $ajax_container; ?>");
                            // Add the loading div inside the ajax container
                            ajax_container.html('<div class="loading"></div>');
                            // bind form using 'ajaxForm'
                            form.ajaxForm({ target: ajax_container });
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

            <?php if (!empty($ajax_container)) : ?>
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
                $("#dialog_<?php echo $uid; ?>").css({ "position" : "relative" });
                $("#dialog_<?php echo $uid; ?>").load("<?php echo $target; ?>&tmpl=component");
                $("#dialog_<?php echo $uid; ?>").dialog('open');
            });
        });
        </script>
        
        <a id="dialog_trigger_<?php echo $uid; ?>" href="<?php echo $target; ?>"><?php echo $label; ?></a>
        
        <?php
    }
    
    /**
     * Build and display a jQuery UI confirm box
     * 
     * To use the confirm behaviour you will need to create anchor tags and give them a class, title and href attributes.
     * 
     * For example:
     * 
     * <code>
     * <?php PHPFrame_HTML::confirm('delete_entry', 'Delete entry', 'Are you sure you want to delete entry'); ?>
     * 
     * <a class="delete_entry" title="The name of the entry we are deleting" href="The URL to go if user confirms action">
     * </code>
     * 
     * @access    public
     * @param    string    $a_class        The class attribute used to select the delete links.
     * @param    string    $title            A string to use in the dialog box title bar.
     * @param    string    $msg            A string with the message to display in the confirm box.
     * @param    string    $ajax_container    A jQuery selector string to select the HTML element where to load 
     *                                     the AJAX response. This parameter is optional, if omitted the browser 
     *                                     window will be redirected to the link's href instead of using an AJAX request.
     * @return    void
     * @since    1.0
     */
    public static function confirm($a_class, $title, $msg, $ajax_container='') 
    {
        $uid = uniqid();
        ?>
        
        <script language="javascript" type="text/javascript">
        // Declare confirm_href and confirm_title.
        // We declare this variables in global scope so that they are available to all functions below.
        var confirm_href_<?php echo $uid; ?>;
        var confirm_title_<?php echo $uid; ?>;
        <?php if (!empty($ajax_container)) : ?>
        // This variable is used to identify the element where to load the AJAX request.
        var confirm_response_container_id_<?php echo $uid; ?>; 
        <?php endif; ?>
        
        $(function() {
            //Dinamically add an HTML element to show the confirmation dialog at the end of the body
            $("body").append('<div id="confirm_dialog_<?php echo $uid; ?>" title="<?php echo $title; ?>"></div>');
            
            // Add dialog behaviour to the confirm box
            $("#confirm_dialog_<?php echo $uid; ?>").dialog({
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
                        <?php if (!empty($ajax_container)) : ?>
                        // Add the loading div inside the ajax container
                        $("#"+confirm_response_container_id_<?php echo $uid; ?>).html('<div class="loading"></div>');
                        $("#"+confirm_response_container_id_<?php echo $uid; ?>).load(confirm_href_<?php echo $uid; ?> + '&tmpl=component');
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

            <?php if (!empty($ajax_container)) : ?>
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
        
                // Get href from current link and add tmpl var
                confirm_href_<?php echo $uid; ?> = $(this).attr("href");
                confirm_title_<?php echo $uid; ?> = $(this).attr("title");
                
                // Get row id from href
                var pattern = /id=(.*)$/;
                var id = confirm_href_<?php echo $uid; ?>.match(pattern)[1];

                <?php if (!empty($ajax_container)) : ?>
                // Find the element where we want to load the AJAX response
                confirm_response_container_id_<?php echo $uid; ?> = $("<?php echo $ajax_container; ?>").attr('id');
                <?php endif; ?>
                
                $("#confirm_dialog_<?php echo $uid; ?>").html('<?php echo $msg; ?> "' + confirm_title_<?php echo $uid; ?> + '"?');
                $("#confirm_dialog_<?php echo $uid; ?>").dialog('open');
            });
        });
        </script>
        
        <?php
    }
    
    /**
     * Build a date picker using jQuery UI Calendar and display it
     * 
     * This method will generate two input tags, one is shown to the user and it triggers 
     * the date picker, and the other one holding the date value in MySQL date format to 
     * be used for storing.
     * 
     * @todo    add jQuery tooltip: display format hint in tooltip
     * @param    string    $name        The name attribute for the input tag. 
     * @param    string    $id            The id of the input tag
     * @param    string    $selected    The selected value if any. In YYYY-MM-DD.
     * @param    string    $format        Format in which to present the date to the user. Possible values 'dd/mm/yy', 'mm/dd/yy', 'yy/mm/dd'. 
     *                                 This doesn't affect the hidden input value with the MySQL date.
     * @param     array    $attribs    An array containing attributes for the input tag
     * @param    bool    $show_format_hint    Show/hide date format hint.
     * @return    void
     * @since     1.0
     */
    public static function calendar($name, 
                                    $id='', 
                                    $selected='', 
                                    $format='dd/mm/yy', 
                                    $attribs=array(), 
                                    $show_format_hint=false) 
    {
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
            
        //invoke datepicker via jquery
        ?>
        <script type="text/javascript">
        $(function(){
            $('#<?php echo $id; ?>_datePicker').datepicker({
                inline: true,
                <?php if ($show_format_hint) : ?>appendText: '(dd/mm/yyyy)',<?php endif; ?>
                dateFormat: '<?php echo $format; ?>',
                altField: '#<?php echo $id; ?>',
                altFormat: 'yy-mm-dd'
            });
        });    
        </script>
        <input id="<?php echo $id; ?>_datePicker" type="text" name="<?php echo $name; ?>_datePicker" <?php echo $attribs_str; ?> value="<?php echo $formatted_date; ?>" />
        <input id="<?php echo $id; ?>" type="hidden" name="<?php echo $name; ?>" value="<?php echo $selected; ?>" />
        <?php    
    }
    
    /**
     * Function to build input with autocomplete and display it
     * 
     * @param     string    $field_name        The name attribute fot the input tag
     * @param    string    $attribs         A string containing attributes for the input tag
     * @param    array    $tokens            An array with the key/value pairs used to build the list of options
     * @param    bool    $matchContains    Optional parameter (default: TRUE). If TRUE search matches inside string, 
     *                                     if FALSE only at the beginning.
     * @return     void
     * @since    1.0
     */
    public static function autocomplete($field_name, $attribs, $tokens, $matchContains=true) 
    {
        $document = PHPFrame::Response()->getDocument();
        $document->addScript('lib/jquery/plugins/autocomplete/jquery.autocomplete.pack.js');
        $document->addStyleSheet('lib/jquery/plugins/autocomplete/jquery.autocomplete.css');
        
        $users_string = '';
        for ($i=0; $i<count($tokens); $i++) {
            if ($i>0) $users_string .= ";";
            $users_string .= $tokens[$i]['id']."|".$tokens[$i]['name'];
        }
        ?>
        
        <textarea name="<?php echo $field_name; ?>_autocomplete" id="<?php echo $field_name; ?>_autocomplete" <?php echo $attribs; ?>></textarea>
        <input name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" type="hidden" />

        <script type="text/javascript">
            $(document).ready(function() {
                var data_string = "<?php echo $users_string; ?>".split(";");
                var data = new Array();

                for (count = 0; count < data_string.length; count++) {
                    data[count] = data_string[count].split("|");
                }

                function formatItem(row) {
                    return row[1];
                }
                
                function formatResult(row) {
                    return row[1].replace(/(<.+?>)/gi, '');
                }
                            
                
                $("#<?php echo $field_name; ?>_autocomplete").autocomplete(data, {
                    multiple: true,
                    matchContains: <?php echo $matchContains ? 'true' : 'false'; ?>,
                    formatItem: formatItem,
                    formatResult: formatResult
                });

                $("#<?php echo $field_name; ?>_autocomplete").result(function(event, data, formatted) {
                    var hidden = $("#<?php echo $field_name; ?>");
                    hidden.val( (hidden.val() ? hidden.val() + "," : hidden.val()) + data[0]);
                });        
                            
            });
        </script>
        
        <?php
    }
    
    public function dragAndDrop() 
    {
        ?>
        
        <?php
    }
    
    public static function upload($data=array(), 
                                  $name='userfile', 
                                  $onComplete='', 
                                  $action='index.php') 
    {
        $document = PHPFrame::getDocument('html');
        $document->addScript('lib/jquery/plugins/ajax-upload/jquery.ajax-upload-2.6.js');
        
        $token = PHPFrame_Utils_Crypt::getToken();
        $data[$token] = '1';
        $data['tmpl'] = 'component';
        
        $uid = uniqid();
        ?>
        
        <script type= "text/javascript">/*<![CDATA[*/
        $(document).ready(function(){
            var button = $('#upload_file_<?php echo $uid; ?>'), interval;
            new Ajax_upload(button,{
                action: '<?php echo $action; ?>', 
                name: '<?php echo $name; ?>',
                data: {
                    <?php 
                    $i=0;
                    foreach ($data as $key=>$value) {
                        if ($i>0) { echo ",\n"; } 
                        echo "'".$key."' : '".$value."'";
                        $i++;
                    }
                    ?>
                    
                },
                onSubmit : function(file, ext){        
                    button.text('Uploading');

                    button.after('<div style="float:left;" class="loading"></div>');
                    
                    // Allow uploading only 1 file at time
                    this.disable();
                    
                    // Uploding -> Uploading. -> Uploading...
                    interval = window.setInterval(function(){
                        var text = button.text();
                        if (text.length < 13){
                            button.text(text + '.');                    
                        } else {
                            button.text('Uploading');                
                        }
                    }, 200);
                },
                onComplete: function(file, response){
                    button.text('Upload');
                    button.next().remove();
                            
                    window.clearInterval(interval);
                                
                    // enable upload button
                    this.enable();

                    if (response == '0') {
                        alert('Error uploading file');
                        return false;
                    }
                    
                    // add file to the list
                    <?php echo $onComplete; ?>
                                        
                }
            });    
        });/*]]>*/</script>
        
        <div style="float:left;" class="button" id="upload_file_<?php echo $uid; ?>">Upload</div>
        <br style="clear: left;" />
        <?php
    }
    
    /**
     * Displays a hidden token field to reduce the risk of CSRF exploits.
     * 
     * Use in conjuction with PHPFrame_Utils_Crypt::checkToken
     * 
     * @return     void
     * @since     1.0
     */
    public static function formToken() 
    {
        ?><input type="hidden" name="<?php echo PHPFrame::Session()->getToken(); ?>" value="1" /><?php
    }
    
    /**
     * Build an html button tag and echo it.
     * 
     * @param    string    $type        The button type. Possible values are 'button', 'submit', 'reset'
     * @param    string    $label        A string to use as the button's label.
     * @param     string    $onclick    A string to be printed in the onclick attribute of the button tag.
     * @return    void
     * @since     1.0
     */
    public static function button($type='button', $label='', $onclick='') 
    {
        ?><button type="<?php echo $type; ?>" onclick="<?php echo $onclick; ?>"><?php echo PHPFrame_Base_String::html( $label ); ?></button><?php
    }
    
    /**
     * Build an html 'back' button tag and echo it.
     * 
     * @return    void
     * @since     1.0
     */
    public static function buttonBack() 
    {
        ?><button type="button" onclick="Javascript:window.history.back();"><?php echo PHPFrame_Base_String::html( _LANG_BACK ); ?></button>     <?php
    }
    
    /**
     * Redirects to previous page using Javascript window.history.back()
     * 
     * @return    void
     * @since     1.0
     */
    public static function historyBack() 
    {
        ?>
        <script type="text/javascript">
            window.history.back();
        </script>
        <?php  
    }
    
    /**
     * Outputs message in Javascript alert box
     *
     * @param    string    $msg    A string containing the message to show in the alert box.
     * @return    void
     * @since     1.0
     */
    public static function alert($msg) 
    {
        ?>
        <script type="text/javascript">
            alert('<?php echo $msg; ?>');
        </script>
        <?php  
    }
    
    /**
     * Loader method.
     * 
     * Use this method to create html elements by using keywords to invoke the methods.
     * 
     * For example: 
     * 
     * <code>
     * $options[] = PHPFrame_HTML::_('select.option', $row->id, $row->name );
     * $output = PHPFrame_HTML::_('select.genericlist', $options, 'projectid', $attribs, $selected);
     * </code>
     * 
     * @param    string    $str
     * @return     void
     * @since     1.0
     */
    public static function _($str) 
    {
        
        $array = explode('.', $str);
        if (isset($array[1])) {
            $function_name = $array[0].ucfirst($array[1]);
        } else {
            $function_name = $array[0];
        }
        
        
        if (is_callable( array( 'PHPFrame_HTML', $function_name) )) {
            $args = func_get_args();
            array_shift( $args );
            return call_user_func_array( array( 'PHPFrame_HTML', $function_name ), $args );
        }
        else {
            throw new PHPFrame_Exception('PHPFrame_HTML::'.$function_name.' not supported.');
        }
        
    }
}
