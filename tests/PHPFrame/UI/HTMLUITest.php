<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_HTMLUITest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //...
    }

    public function tearDown()
    {
        //...
    }

    public function test_input()
    {
        $this->assertRegExp(
            "/<input type=\"text\" name=\"email\" value=\"\" \/>/",
            PHPFrame_HTMLUI::input("email")
        );
    }

    public function test_inputWithOptionalArgs()
    {
        $this->assertRegExp(
            "/<input type=\"password\" name=\"password\" value=\"S0m3th1ngR4nd0m\" size=\"20\" maxlength=\"30\" \/>/",
            PHPFrame_HTMLUI::input("password", "S0m3th1ngR4nd0m", "password", 20, 30)
        );
    }

    public function test_textarea()
    {
        $this->assertRegExp(
            "/<textarea name=\"some_text\" cols=\"50\" rows=\"10\"><\/textarea>/",
            PHPFrame_HTMLUI::textarea("some_text")
        );
    }

    public function test_textareaWithOptionalArgs()
    {
        $this->assertRegExp(
            "/<textarea name=\"some_text\" cols=\"40\" rows=\"5\">Some text\.\.\.<\/textarea>/",
            PHPFrame_HTMLUI::textarea("some_text", "Some text...", 40, 5)
        );
    }

    public function test_radio()
    {
        $this->assertRegExp(
            "/<label for=\"some_radio0\">key1<\/label>\n"
           ."<input type=\"radio\" name=\"some_radio\" id=\"some_radio0\" value=\"value1\" \/>\n"
           ."<label for=\"some_radio1\">key2<\/label>\n"
           ."<input type=\"radio\" name=\"some_radio\" id=\"some_radio1\" value=\"value2\" \/>/",
            PHPFrame_HTMLUI::radio(
                "some_radio",
                array("key1"=>"value1", "key2"=>"value2")
            )
        );
    }

    public function test_radioWithOptionalArgs()
    {
        $this->assertRegExp(
            "/<label for=\"some_radio0\">key1<\/label>\n"
            ."<input type=\"radio\" name=\"some_radio\" id=\"some_radio0\" value=\"value1\" \/>\n"
            ."<label for=\"some_radio1\">key2<\/label>\n"
            ."<input type=\"radio\" name=\"some_radio\" id=\"some_radio1\" value=\"value2\" checked \/>/",
            PHPFrame_HTMLUI::radio(
                "some_radio",
                array("key1"=>"value1", "key2"=>"value2"),
                "value2"
            )
        );
    }

    public function test_select()
    {
        $this->assertRegExp(
            "/<select name=\"some_select\">\n"
            ."<option value=\"1\">Yes<\/option>\n"
            ."<option value=\"0\">No<\/option>\n"
            ."<\/select>/",
            PHPFrame_HTMLUI::select("some_select", array("Yes"=>1, "No"=>0))
        );
    }

    public function test_selectWithOptionalArgs()
    {
        $this->assertRegExp(
            "/<select name=\"some_select\">\n"
            ."<option value=\"1\">Yes<\/option>\n"
            ."<option value=\"0\" selected>No<\/option>\n"
            ."<\/select>/",
            PHPFrame_HTMLUI::select(
                "some_select",
                array("Yes"=>1, "No"=>0),
                0
            )
        );
    }

    public function test_dialog()
    {
        $this->assertRegExp(
            "/var dialog_div = '<div style=\"position: absolute\" ';/",
            PHPFrame_HTMLUI::dialog("Open a dialog", "index.php")
        );
    }

    public function test_confirm()
    {
        $this->assertRegExp(
            "/var dialog_html = '<div id=\"confirm_dialog_([a-zA-Z0-9]+)\" ';/",
            PHPFrame_HTMLUI::confirm("confirm_link", "Confirm box", "Some message...")
        );
    }

    public function test_calendar()
    {
        $this->assertRegExp("/\('#date_datePicker'\)\.datepicker\(\{/", PHPFrame_HTMLUI::calendar("date"));
    }

    public function test_formToken()
    {
        $this->assertRegExp(
            "/<input type=\"hidden\" name=\"([a-zA-Z0-9]+)\" value=\"1\" \/>/",
            PHPFrame_HTMLUI::formToken()
        );
    }

    public function test_persistentObjectToForm()
    {
        $this->assertRegExp(
            "/<form action=\"index\.php\?controller=user&action=save\" method=\"post\">\n"
            ."<div>\n"
            ."<label for=\"group_id\">group_id<\/label>\n"
            ."<input type=\"text\" name=\"group_id\" value=\"0\" size=\"12\" maxlength=\"12\" \/>\n"
            ."<\/div>\n"
            ."<div>\n"
            ."<label for=\"email\">email<\/label>\n"
            ."<input type=\"text\" name=\"email\" value=\"\" size=\"100\" maxlength=\"100\" \/>\n"
            ."<\/div>\n"
            ."<div>\n"
            ."<label for=\"password\">password<\/label>\n"
            ."<input type=\"text\" name=\"password\" value=\"\" size=\"100\" maxlength=\"100\" \/>\n"
            ."<\/div>\n"
            ."<div>\n"
            ."<label for=\"params\">params<\/label>\n"
            ."<textarea name=\"params\" cols=\"50\" rows=\"10\"><\/textarea>\n"
            ."<\/div>\n"
            ."<br \/>\n"
            ."<input type=\"submit\" value=\"Submit\" \/>\n"
            ."<input type=\"hidden\" name=\"id\" value=\"\" \/>\n"
            ."<\/form>/",
            PHPFrame_HTMLUI::persistentObjectToForm(
                new PHPFrame_User,
                "index.php?controller=user&action=save"
            )
        );
    }
}
