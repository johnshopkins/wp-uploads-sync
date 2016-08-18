<?php

namespace UploadsSync;

use \phpmock\phpunit\PHPMock;

class AttachmentTest extends \PHPUnit_Framework_TestCase
{
  use PHPMock;

  protected $attachment;

	public function setUp()
	{
    $getHomePath = $this->getFunctionMock(__NAMESPACE__, "home_url");
    $getHomePath->expects($this->any())
      ->willReturn("http://hub.jhu.edu");

    $pathinfo = $this->getFunctionMock(__NAMESPACE__, "pathinfo");
    $pathinfo->expects($this->any())
      ->willReturn(array("dirname" => "/var/www/html/hub/public/assets/uploads/2016/08"));

    $basename = $this->getFunctionMock(__NAMESPACE__, "basename");
    $basename->expects($this->any())
      ->willReturn("filename.jpg");
	}

  public function testNormalizePath()
  {
    // local
    $path = "/var/www/html/hub/public/assets/uploads/2016/08/filename.jpg";
    $attachment = new \UploadsSync\Attachment($path);
    $this->assertEquals($path, $attachment->path);

    // staging or production (with releases path)
    $path = "/var/www/html/hub/releases/20160816183633/public/assets/uploads/2016/08/filename.jpg";
    $attachment = new \UploadsSync\Attachment($path);
    $this->assertEquals("/var/www/html/hub/current/public/assets/uploads/2016/08/filename.jpg", $attachment->path);

    // staging or production (with current path)
    $path = "/var/www/html/hub/current/public/assets/uploads/2016/08/filename.jpg";
    $attachment = new \UploadsSync\Attachment($path);
    $this->assertEquals("/var/www/html/hub/current/public/assets/uploads/2016/08/filename.jpg", $attachment->path);
  }

  public function testNonImageFile()
  {
    $path = "/var/www/html/hub/public/assets/uploads/2016/08/filename.jpg";
    $attachment = new \UploadsSync\Attachment($path);

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

  public function testGetUrls()
  {
    $path = "/var/www/html/hub/current/public/assets/uploads/2016/08/filename.jpg";
    $attachment = new \UploadsSync\Attachment($path);

    $expected = array("http://hub.jhu.edu/assets/uploads/2016/08/filename.jpg");
    $this->assertEquals($expected, $attachment->getUrls());
  }
}
