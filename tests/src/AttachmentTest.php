<?php

namespace UploadsSync;

use \phpmock\phpunit\PHPMock;

class AttachmentTest extends \PHPUnit_Framework_TestCase
{
  use PHPMock;

  protected $attachment;

	public function setUp()
	{
    // mock some WordPress function
    $getAttachedFile = $this->getFunctionMock(__NAMESPACE__, "get_attached_file");
    $getAttachedFile->expects($this->any())->willReturn("/var/www/html/hub/public/assets/uploads/2016/08/filename.jpg");

    $getHomePath = $this->getFunctionMock(__NAMESPACE__, "get_home_path");
    $getHomePath->expects($this->any())->willReturn("/var/www/html/hub/public/");

    $pathinfo = $this->getFunctionMock(__NAMESPACE__, "pathinfo");
    $pathinfo->expects($this->any())->willReturn(array("dirname" => "/var/www/html/hub/public/assets/uploads/2016/08"));

    $basename = $this->getFunctionMock(__NAMESPACE__, "basename");
    $basename->expects($this->any())->willReturn("filename.jpg");
	}

  public function testNonImageFile()
  {
    $attachment = new \UploadsSync\Attachment(123, array());

    $expected = array("/var/www/html/hub/public/assets/uploads/2016/08/filename.jpg");
    $this->assertEquals($expected, $attachment->paths);

    $expected = "assets/uploads/2016/08";
    $this->assertEquals($expected, $attachment->akamaiPath);

    $expected = array("filename.jpg");
    $this->assertEquals($expected, $attachment->filenames);
  }

  public function testImageFile()
  {
    $attachment = new \UploadsSync\Attachment(123, array(
      "sizes" => array(
        "thumbnail" => array("file" => "filename-200x200.jpg"),
        "medium" => array("file" => "filename-400x400.jpg")
      )
    ));

    $expected = array(
      "/var/www/html/hub/public/assets/uploads/2016/08/filename.jpg",
      "/var/www/html/hub/public/assets/uploads/2016/08/filename-200x200.jpg",
      "/var/www/html/hub/public/assets/uploads/2016/08/filename-400x400.jpg"
    );
    $this->assertEquals($expected, $attachment->paths);

    $expected = array("filename.jpg", "filename-200x200.jpg", "filename-400x400.jpg");
    $this->assertEquals($expected, $attachment->filenames);
  }
}
