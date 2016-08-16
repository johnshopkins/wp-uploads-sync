<?php

namespace UploadsSync;

use \phpmock\phpunit\PHPMock;

class AttachmentTest extends \PHPUnit_Framework_TestCase
{
  use PHPMock;

  protected $attachment;

	public function setUp()
	{
    $getHomePath = $this->getFunctionMock(__NAMESPACE__, "get_home_path");
    $getHomePath->expects($this->any())
      ->willReturn("/var/www/html/hub/public/");

    $pathinfo = $this->getFunctionMock(__NAMESPACE__, "pathinfo");
    $pathinfo->expects($this->any())
      ->willReturn(array("dirname" => "/var/www/html/hub/public/assets/uploads/2016/08"));

    $basename = $this->getFunctionMock(__NAMESPACE__, "basename");
    $basename->expects($this->any())
      ->willReturn("filename.jpg");
	}

  public function testNonImageFile()
  {
    $path = "/var/www/html/hub/public/assets/uploads/2016/08/filename.jpg";
    $attachment = new \UploadsSync\Attachment($path, array());

    $expected = "assets/uploads/2016/08";
    $this->assertEquals($expected, $attachment->source);

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

    $expected = array("filename.jpg", "filename-200x200.jpg", "filename-400x400.jpg");
    $this->assertEquals($expected, $attachment->filenames);
  }

  public function testStagingEnv()
  {
    $path = "/var/www/html/hub/releases/20160816140117/public/assets/uploads/2016/08/filename.jpg";
    $attachment = new \UploadsSync\Attachment($path, array());

    $expected = "assets/uploads/2016/08";
    $this->assertEquals($expected, $attachment->source);
  }
}
