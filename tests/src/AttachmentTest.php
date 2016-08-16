<?php

namespace UploadsSync;

use \phpmock\phpunit\PHPMock;

class AttachmentTest extends \PHPUnit_Framework_TestCase
{
  use PHPMock;

  protected $attachment;

	public function setUp()
	{
    $url = "http://hub.jhu.edu/assets/uploads/2016/08/filename.jpg";

    // mock some WordPress function
    $getAttachedFile = $this->getFunctionMock(__NAMESPACE__, "wp_get_attachment_url");
    $getAttachedFile->expects($this->any())
      ->willReturn($url);

    $hoemUrl = $this->getFunctionMock(__NAMESPACE__, "home_url");
    $hoemUrl->expects($this->any())
      ->willReturn("http://hub.jhu.edu");

    $getHomePath = $this->getFunctionMock(__NAMESPACE__, "get_home_path");
    $getHomePath->expects($this->any())
      ->willReturn("http://hub.jhu.edu");

    $pathinfo = $this->getFunctionMock(__NAMESPACE__, "pathinfo");
    $pathinfo->expects($this->any())
      ->with($this->equalTo($url))
      ->willReturn(array("dirname" => "http://hub.jhu.edu/assets/uploads/2016/08"));

    $basename = $this->getFunctionMock(__NAMESPACE__, "basename");
    $basename->expects($this->any())
      ->willReturn("filename.jpg");
	}

  public function testNonImageFile()
  {
    $attachment = new \UploadsSync\Attachment(123, array());

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
}
