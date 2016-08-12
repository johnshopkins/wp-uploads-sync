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
    $getAttachedFile->expects($this->any())->willReturn(strtotime("/var/www/html/hub/public/assets/uploads/2016/08/filename.jpg"));

    $getHomePath = $this->getFunctionMock(__NAMESPACE__, "get_home_path");
    $getHomePath->expects($this->any())->willReturn(strtotime("/var/www/html/hub/public/"));

    $this->attachment = new \UploadsSync\Attachment();
    parent::setUp();
	}

  public function testGetDate()
  {
    $expected = "/var/www/html/hub/public/assets/uploads/2016/08";
    $this->assertEquals($expected, $this->attachment->directory);
  }
}
