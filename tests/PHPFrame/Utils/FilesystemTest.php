<?php
// Include framework if not inculded yet
require_once preg_replace("/tests\/.*/", "src/PHPFrame.php", __FILE__);

class PHPFrame_FilesystemTest extends PHPUnit_Framework_TestCase
{
    private $_sys_tmp_dir;

    public function setUp()
    {
        $this->_sys_tmp_dir = PHPFrame_Filesystem::getSystemTempDir();
    }

    public function tearDown()
    {
        //...
    }

    public function test_cpFileToDir()
    {
        $dir  = $this->_sys_tmp_dir.DS."test-dir";
        $file = $this->_sys_tmp_dir.DS."file1.txt";

        if (is_dir($dir)) {
            PHPFrame_Filesystem::rm($dir, true);
        }

        if (is_file($file)) {
            unlink($file);
        }

        if (!is_dir($dir)) mkdir($dir);
        touch($file);

        $this->assertTrue(is_dir($dir));
        $this->assertTrue(is_file($file));

        PHPFrame_Filesystem::cp($file, $dir);

        $this->assertTrue(is_file($dir.DS."file1.txt"));

        unlink($dir.DS."file1.txt");
        rmdir($dir);
        unlink($file);
    }

    public function test_cpFileToFile()
    {
        $source = $this->_sys_tmp_dir.DS."file1.txt";
        $dest   = $this->_sys_tmp_dir.DS."file2.txt";

        touch($source);

        $this->assertTrue(is_file($source));

        PHPFrame_Filesystem::cp($source, $dest);

        $this->assertTrue(is_file($dest));

        unlink($source);
        unlink($dest);
    }

    public function test_cpDirIntoExisitingDir()
    {
        $source = $this->_sys_tmp_dir.DS."test-dir";
        $subdir = $source.DS."subdir";
        $dest   = $this->_sys_tmp_dir.DS."test-dir2";

        if (!is_dir($source)) mkdir($source);
        if (!is_dir($subdir)) mkdir($subdir);
        if (!is_dir($dest)) mkdir($dest);

        $file1 = $source.DS."file1.txt";
        $file2 = $source.DS."file2.txt";
        $file3 = $source.DS."file3.txt";
        $file4 = $subdir.DS."file4.txt";
        $file5 = $subdir.DS."file5.txt";

        touch($file1);
        touch($file2);
        touch($file3);
        touch($file4);
        touch($file5);

        $this->assertTrue(is_dir($source));
        $this->assertTrue(is_dir($subdir));
        $this->assertTrue(is_dir($dest));
        $this->assertTrue(is_file($file1));
        $this->assertTrue(is_file($file2));
        $this->assertTrue(is_file($file3));
        $this->assertTrue(is_file($file4));
        $this->assertTrue(is_file($file5));

        PHPFrame_Filesystem::cp($source, $dest, true);

        $this->assertTrue(is_dir($dest.DS."test-dir".DS."subdir"));
        $this->assertTrue(is_file($dest.DS."test-dir".DS."file1.txt"));
        $this->assertTrue(is_file($dest.DS."test-dir".DS."file2.txt"));
        $this->assertTrue(is_file($dest.DS."test-dir".DS."file3.txt"));
        $this->assertTrue(is_file($dest.DS."test-dir".DS."subdir".DS."file4.txt"));
        $this->assertTrue(is_file($dest.DS."test-dir".DS."subdir".DS."file5.txt"));

        // Cleanup
        PHPFrame_Filesystem::rm($source, true);
        PHPFrame_Filesystem::rm($dest, true);
    }

    public function test_cpDirContentsToDir()
    {
        $source = $this->_sys_tmp_dir.DS."test-dir";
        $subdir = $source.DS."subdir";
        $dest   = $this->_sys_tmp_dir.DS."test-dir2";

        if (!is_dir($source)) mkdir($source);
        if (!is_dir($subdir)) mkdir($subdir);
        if (!is_dir($dest)) mkdir($dest);

        $file1 = $source.DS."file1.txt";
        $file2 = $source.DS."file2.txt";
        $file3 = $source.DS."file3.txt";
        $file4 = $subdir.DS."file4.txt";
        $file5 = $subdir.DS."file5.txt";

        touch($file1);
        touch($file2);
        touch($file3);
        touch($file4);
        touch($file5);

        $this->assertTrue(is_dir($source));
        $this->assertTrue(is_dir($subdir));
        $this->assertTrue(is_dir($dest));
        $this->assertTrue(is_file($file1));
        $this->assertTrue(is_file($file2));
        $this->assertTrue(is_file($file3));
        $this->assertTrue(is_file($file4));
        $this->assertTrue(is_file($file5));

        PHPFrame_Filesystem::cp($source.DS, $dest, true);

        $this->assertTrue(is_dir($dest.DS."subdir"));
        $this->assertTrue(is_file($dest.DS."file1.txt"));
        $this->assertTrue(is_file($dest.DS."file2.txt"));
        $this->assertTrue(is_file($dest.DS."file3.txt"));
        $this->assertTrue(is_file($dest.DS."subdir".DS."file4.txt"));
        $this->assertTrue(is_file($dest.DS."subdir".DS."file5.txt"));

        // Cleanup
        PHPFrame_Filesystem::rm($source, true);
        PHPFrame_Filesystem::rm($dest, true);
    }

    public function test_cpDirToNewDir()
    {

    }

    public function test_cpSourceNotReadableFailure()
    {
        $source = "/i/do/not/exist";
        $dest   = $this->_sys_tmp_dir.DS."test-dir";

        $this->setExpectedException("RuntimeException");

        PHPFrame_Filesystem::cp($source, $dest, true);
    }

    public function test_cpDestinationNotWriteableFailure()
    {
        $source = $this->_sys_tmp_dir;
        $dest   = "/i/do/not/exist";

        $this->setExpectedException("RuntimeException");

        PHPFrame_Filesystem::cp($source, $dest, true);
    }

    public function test_cpDirectoryNonRecursiveFailure()
    {
        $source = $this->_sys_tmp_dir;
        $dest   = $this->_sys_tmp_dir.DS."test-dir";

        $this->setExpectedException("LogicException");

        PHPFrame_Filesystem::cp($source, $dest);
    }

    public function test_rm()
    {
        $file = $this->_sys_tmp_dir.DS."file1.txt";

        touch($file);

        $this->assertTrue(is_file($file));

        PHPFrame_Filesystem::rm($file);

        $this->assertFalse(is_file($file));
    }

    public function test_rmRecursive()
    {
        $test_dir = $this->_sys_tmp_dir.DS."test-dir";
        $file1    = $test_dir.DS."file1.txt";
        $file2    = $test_dir.DS."file2.txt";
        $file3    = $test_dir.DS."file3.txt";
        $subdir   = $test_dir.DS."subdir";
        $file4    = $subdir.DS."file4.txt";
        $file5    = $subdir.DS."file5.txt";

        if (!is_dir($test_dir)) mkdir($test_dir);
        touch($file1);
        touch($file2);
        touch($file3);
        if (!is_dir($subdir)) mkdir($subdir);
        touch($file4);
        touch($file5);

        $this->assertTrue(is_dir($test_dir));
        $this->assertTrue(is_file($file1));
        $this->assertTrue(is_file($file2));
        $this->assertTrue(is_file($file3));
        $this->assertTrue(is_dir($subdir));
        $this->assertTrue(is_file($file4));
        $this->assertTrue(is_file($file5));

        PHPFrame_Filesystem::rm($test_dir, true);

        $this->assertFalse(is_dir($test_dir));
        $this->assertFalse(is_file($file1));
        $this->assertFalse(is_file($file2));
        $this->assertFalse(is_file($file3));
        $this->assertFalse(is_dir($subdir));
        $this->assertFalse(is_file($file4));
        $this->assertFalse(is_file($file5));
    }

    public function test_ensureWritableDir()
    {
        $dir    = $this->_sys_tmp_dir.DS."test-dir";
        $subdir = $dir.DS."subdir";

        PHPFrame_Filesystem::ensureWritableDir($subdir);

        $this->assertTrue(is_dir($subdir));
        $this->assertTrue(is_writable($subdir));

        PHPFrame_Filesystem::rm($dir, true);
    }

    public function test_isEmptyDir()
    {
        $dir = $this->_sys_tmp_dir.DS."test-dir";

        if (is_dir($dir)) PHPFrame_Filesystem::rm($dir, true);

        mkdir($dir);

        $this->assertTrue(PHPFrame_Filesystem::isEmptyDir($dir));

        touch($dir.DS."file1.txt");
        touch($dir.DS."file2.txt");

        $this->assertFalse(PHPFrame_Filesystem::isEmptyDir($dir));

        PHPFrame_Filesystem::rm($dir, true);
    }

    public function test_upload()
    {
        $tmp_file = $this->_sys_tmp_dir.DS."uploadedfile";

        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }

        touch($tmp_file);
        file_put_contents($tmp_file, "Lorem ipsum...");

        $this->assertTrue(is_file($tmp_file));

        $file_array = array(
            "name" => "MyUploadedFile.txt",
            "type" => "text/plain",
            "tmp_name" => $tmp_file,
            "error" => UPLOAD_ERR_OK,
            "size" => filesize($tmp_file)
        );

        PHPFrame_Filesystem::testMode(true);
        $array = PHPFrame_Filesystem::upload($file_array, $this->_sys_tmp_dir);
        PHPFrame_Filesystem::testMode(false);

        $this->assertType("array", $array);
        $this->assertArrayHasKey("finfo", $array);
        $this->assertArrayHasKey("mimetype", $array);
        $this->assertType("SplFileInfo", $array["finfo"]);
        $this->assertEquals("text/plain", $array["mimetype"]);

        unlink($array["finfo"]->getRealPath());
        unlink($tmp_file);
    }

    public function test_uploadFileArrayFailure()
    {
        $tmp_file = $this->_sys_tmp_dir.DS."uploadedfile";
        $file_array = array(
            "name" => "MyUploadedFile.txt",
            "type" => "text/plain",
            "tmp_name" => $tmp_file,
            "error" => UPLOAD_ERR_OK
        );

        $caught = false;
        try {
            PHPFrame_Filesystem::upload($file_array, $this->_sys_tmp_dir);
        } catch (InvalidArgumentException $e) {
            $this->assertRegExp("/file_array/", $e->getMessage());
            $caught = true;
        }

        $this->assertTrue($caught);
    }

    public function test_uploadUploadErrorFailure()
    {
        $tmp_file = $this->_sys_tmp_dir.DS."uploadedfile";
        $errors = array(
            UPLOAD_ERR_INI_SIZE => "PHP upload maximum file size exceeded!",
            UPLOAD_ERR_FORM_SIZE => "PHP maximum post size exceeded!",
            UPLOAD_ERR_PARTIAL => "Partial upload!",
            UPLOAD_ERR_NO_FILE => "No file submitted for upload!",
            UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder!",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk!",
            UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload!"
        );

        $caught = 0;
        foreach ($errors as $code=>$msg) {
            $file_array = array(
                "name" => "MyUploadedFile.txt",
                "type" => "text/plain",
                "tmp_name" => $tmp_file,
                "error" => $code,
                "size" => 0
            );

            try {
                PHPFrame_Filesystem::upload($file_array, $this->_sys_tmp_dir);
            } catch (RuntimeException $e) {
                $this->assertEquals($msg, $e->getMessage());
                $caught++;
            }
        }

        $this->assertEquals(count($errors), $caught);
    }

    public function test_uploadMimeTypeFailure()
    {
        $tmp_file = $this->_sys_tmp_dir.DS."uploadedfile";

        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }

        touch($tmp_file);
        file_put_contents($tmp_file, "Lorem ipsum...");

        $this->assertTrue(is_file($tmp_file));

        $file_array = array(
            "name" => "MyUploadedFile.txt",
            "type" => "text/plain",
            "tmp_name" => $tmp_file,
            "error" => UPLOAD_ERR_OK,
            "size" => filesize($tmp_file)
        );

        $caught = false;
        try {
            PHPFrame_Filesystem::upload($file_array, $this->_sys_tmp_dir, "image/png");
        } catch (RuntimeException $e) {
            $this->assertRegExp("/File type not valid/", $e->getMessage());
            $caught = true;
        }

        $this->assertTrue($caught);

        unlink($tmp_file);
    }

    public function test_uploadMaliciousScriptFailure()
    {
        $tmp_file = $this->_sys_tmp_dir.DS."uploadedfile";

        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }

        touch($tmp_file);
        file_put_contents($tmp_file, "Lorem ipsum...");

        $this->assertTrue(is_file($tmp_file));

        $file_array = array(
            "name" => "MyUploadedFile.jpg.php",
            "type" => "image/jpg",
            "tmp_name" => $tmp_file,
            "error" => UPLOAD_ERR_OK,
            "size" => filesize($tmp_file)
        );

        $caught = false;
        try {
            PHPFrame_Filesystem::upload($file_array, $this->_sys_tmp_dir);
        } catch (RuntimeException $e) {
            $this->assertRegExp("/file attack/", $e->getMessage());
            $caught = true;
        }

        $this->assertTrue($caught);

        unlink($tmp_file);
    }

    public function test_uploadMaxFileSizeFailure()
    {
        $tmp_file = $this->_sys_tmp_dir.DS."uploadedfile";

        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }

        touch($tmp_file);
        file_put_contents($tmp_file, "Lorem ipsum...");

        $this->assertTrue(is_file($tmp_file));

        $file_array = array(
            "name" => "MyUploadedFile.txt",
            "type" => "text/plain",
            "tmp_name" => $tmp_file,
            "error" => UPLOAD_ERR_OK,
            "size" => (2*1024*1024)
        );

        $caught = false;
        try {
            PHPFrame_Filesystem::upload($file_array, $this->_sys_tmp_dir, "*", 1);
        } catch (RuntimeException $e) {
            $this->assertRegExp("/Maximum file size exceeded/", $e->getMessage());
            $caught = true;
        }

        $this->assertTrue($caught);

        unlink($tmp_file);
    }

    public function test_uploadDestinationDirDoesntExistsFailure()
    {
        $tmp_file = $this->_sys_tmp_dir.DS."uploadedfile";

        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }

        touch($tmp_file);
        file_put_contents($tmp_file, "Lorem ipsum...");

        $this->assertTrue(is_file($tmp_file));

        $file_array = array(
            "name" => "MyUploadedFile.txt",
            "type" => "text/plain",
            "tmp_name" => $tmp_file,
            "error" => UPLOAD_ERR_OK,
            "size" => filesize($tmp_file)
        );

        $caught = false;
        try {
            PHPFrame_Filesystem::testMode(true);
            PHPFrame_Filesystem::upload($file_array, $this->_sys_tmp_dir.DS."ghostdir");
        } catch (InvalidArgumentException $e) {
            $this->assertRegExp("/Destination directory/", $e->getMessage());
            $caught = true;
        }

        $this->assertTrue($caught);

        unlink($tmp_file);
    }

    public function test_uploadFileAttackFailure()
    {
        $tmp_file = $this->_sys_tmp_dir.DS."uploadedfile";

        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }

        touch($tmp_file);
        file_put_contents($tmp_file, "Lorem ipsum...");

        $this->assertTrue(is_file($tmp_file));

        $file_array = array(
            "name" => "MyUploadedFile.txt",
            "type" => "text/plain",
            "tmp_name" => $tmp_file,
            "error" => UPLOAD_ERR_OK,
            "size" => filesize($tmp_file)
        );

        $caught = false;
        try {
            PHPFrame_Filesystem::testMode(false);
            PHPFrame_Filesystem::upload($file_array, $this->_sys_tmp_dir);
        } catch (RuntimeException $e) {
            $caught = true;
        }

        $this->assertTrue($caught);

        unlink($tmp_file);
    }

    // public function test_uploadMoveFailure()
    // {
    //     $tmp_file = $this->_sys_tmp_dir.DS."uploadedfile";
    //
    //     if (is_file($tmp_file)) {
    //         unlink($tmp_file);
    //     }
    //
    //     touch($tmp_file);
    //     file_put_contents($tmp_file, "Lorem ipsum...");
    //
    //     $this->assertTrue(is_file($tmp_file));
    //
    //     $file_array = array(
    //         "name" => "MyUploadedFile.txt",
    //         "type" => "text/plain",
    //         "tmp_name" => $tmp_file,
    //         "error" => UPLOAD_ERR_OK,
    //         "size" => filesize($tmp_file)
    //     );
    //
    //     $ghost_dir = $this->_sys_tmp_dir.DS."ghostdir";
    //     if (is_dir($ghost_dir)) {
    //         rmdir($ghost_dir);
    //     }
    //
    //     mkdir($ghost_dir, 0555);
    //
    //     $this->assertTrue(is_dir($ghost_dir));
    //     $this->assertFalse(is_writable($ghost_dir));
    //
    //     $caught = false;
    //     PHPFrame_Filesystem::testMode(true);
    //
    //     try {
    //         PHPFrame_Filesystem::upload($file_array, $ghost_dir);
    //     } catch (RuntimeException $e) {
    //         $this->assertRegExp("/Could not move file/", $e->getMessage());
    //         $caught = true;
    //     }
    //
    //     $this->assertTrue($caught);
    //     PHPFrame_Filesystem::testMode(false);
    //
    //     unlink($tmp_file);
    //     chmod($ghost_dir, 0777);
    //     rmdir($ghost_dir);
    // }

    public function test_filterFilename()
    {
        $data = array(
            "foo" => "foo",
            "foo%*" => "foo",
            "some weird file n^me @" => "some weird file nme "
        );

        foreach ($data as $key=>$value) {
            $this->assertEquals($value, PHPFrame_Filesystem::filterFilename($key, true));
        }
    }

    public function test_filterFilenameFailure()
    {
        $data = array(".", "/tmp", ".ssh", "~/Desktop");
        $caught = 0;
        foreach ($data as $value) {
            try {
                PHPFrame_Filesystem::filterFilename($value);
            } catch (InvalidArgumentException $e) {
                $this->assertEquals("Invalid file or directory name.", $e->getMessage());
                $caught++;
            }
        }

        $this->assertEquals(count($data), $caught);
    }

    public function test_getMimeType()
    {
        $tmp_file = $this->_sys_tmp_dir.DS."uploadedfile";

        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }

        touch($tmp_file);
        file_put_contents($tmp_file, "Lorem ipsum...");

        $this->assertTrue(is_file($tmp_file));

        $mime = PHPFrame_Filesystem::getMimeType($tmp_file);
        $this->assertEquals("text/plain", $mime);

        unlink($tmp_file);
    }

    public function test_getMimeTypeFileDoesntExistFailure()
    {
        $tmp_file = $this->_sys_tmp_dir.DS."uploadedfile";

        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }

        $this->assertTrue(!is_file($tmp_file));

        try {
            $mime = PHPFrame_Filesystem::getMimeType($tmp_file);
        } catch (RuntimeException $e) {
            $this->assertRegExp("/File '.*' doesn't exist\./", $e->getMessage());
        }

    }

    public function test_getSystemTempDir()
    {
        $this->assertTrue(is_dir(PHPFrame_Filesystem::getSystemTempDir()));
        $this->assertTrue(is_writable(PHPFrame_Filesystem::getSystemTempDir()));
    }

    public function test_getUserHomeDir()
    {
        if (array_key_exists("HOME", $_SERVER)) {
            $this->assertEquals($_SERVER["HOME"], PHPFrame_Filesystem::getUserHomeDir());
        }
    }

    public function test_getWindowsUserHomeDir()
    {
        if (array_key_exists("HOME", $_SERVER)) {
            $_SERVER["HOMEPATH"] = $_SERVER["HOME"];
            unset($_SERVER["HOME"]);

            $this->assertEquals($_SERVER["HOMEPATH"], PHPFrame_Filesystem::getUserHomeDir());
            $_SERVER["HOME"] = $_SERVER["HOMEPATH"];
            unset($_SERVER["HOMEPATH"]);
        }
    }

    public function test_getUserHomeDirFailure()
    {
        if (array_key_exists("HOME", $_SERVER)) {
            $home_bk = $_SERVER["HOME"];
            unset($_SERVER["HOME"]);
            $caught = false;

            try {
                PHPFrame_Filesystem::getUserHomeDir();
            } catch (RuntimeException $e) {
                $caught = true;
                $_SERVER["HOME"] = $home_bk;
            }

            $this->assertTrue($caught);
        }
    }
}
