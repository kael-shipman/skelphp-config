<?php

use PHPUnit\Framework\TestCase;

class UiManagerTest extends TestCase {
  public function testInvalidRootDirectoryThrowsException() {
    try {
      $tm = new \Skel\InterfaceManager('invalidDir');
      $this->fail("InterfaceManager should throw a NonexistentFileException");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
      throw $e;
    } catch (\Skel\NonexistentFileException $e) {
      // Arriving here is correct, so there's nothing to do
    }
  }

  public function testAcceptsValidRootDirectory() {
    try {
      $tm = new \Skel\InterfaceManager('tests/content');
      $this->assertTrue(true);
    } catch (Exception $e) {
      $this->fail("Shouldn't have thrown an exception for a valid root directory");
    }
  }

  public function testAutoregisterResources() {
    $tm = new \Skel\InterfaceManager('tests/content');
    $tm->autoregisterResources('template');
    $this->assertEquals("tests/content/templates/_testTemplate.php", $tm->getResourcePath('template', 'testTemplate'), "Autoload doesn't appear to be loading the right templates", 0.0, 20, true);
    try {
      $tm->getResourcePath('css', 'test');
      $this->fail("Should have thrown an InvalidArgumentException when trying to get nonregistered css path");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
      throw $e;
    } catch (InvalidArgumentException $e) {
      // This is correct. Nothing to do
    }

    $tm = new \Skel\InterfaceManager('tests/content');
    $tm->autoregisterResources();
    $this->assertEquals("tests/content/templates/_testTemplate.php", $tm->getResourcePath('template', 'testTemplate'), "Autoload doesn't appear to be loading the right templates", 0.0, 20, true);
    $this->assertEquals("tests/content/assets/css/_test.css", $tm->getResourcePath('css', 'test'));
  }

  public function testRendersNamedTemplate() {
    $tm = new \Skel\InterfaceManager('tests/content');
    $tm->registerResource('template', 'testTemplate');
    $content = $tm->renderTemplate('testTemplate', array('test' => 'test'));
    $this->assertEquals('**test**', trim($content), "Didn't render the template correctly");
  }

  public function testSetAndRetrieveAttributes() {
    $tm = new \Skel\InterfaceManager('tests/content');
    $key = 'test-attribute';
    $str = 'This is a test';
    $tm->setAttribute($key, $str);
    $this->assertEquals($str, $tm->getAttribute($key), 'Should have returned `'.$str.'`');

    $newStr = "A different test";
    $tm->setAttribute($key, $newStr);
    $this->assertEquals($newStr, $tm->getAttribute($key), "Should have overwritten the attribute with `$newStr`");
  }

  public function testCanSetTypesWithCustomPaths() {
    $tm = new \Skel\InterfaceManager('tests/content');

    // Test nonexistent paths
    try {
      $tm->setType('template', 'nonexistent');
      $this->fail("Should have thrown error setting template path to nonexistent directory");
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
      throw $e;
    } catch (InvalidArgumentException $e) {
      // This is correct. Nothing to do
    }

    // Test valid paths
    @mkdir('tests/content/my-templates', 0777, true);
    @touch('tests/content/my-templates/_test.php');
    $tm->setType('template', 'my-templates');
    $tm->registerResource('template', 'test');
    $this->assertEquals('tests/content/my-templates/_test.php', $tm->getResourcePath('template', 'test'), "Incorrect resource path was returned after changing resource directory");
  }

  public function testCanSetLinkedTypesWithCustomTemplates() {
  }

  public function testRetrieveResourceLinks() {
    $tm = new \Skel\InterfaceManager('tests/content');

    // Non aggregated links
    $tm->registerResource('css', 'testInit', 100);
    $tm->registerResource('css', 'test', 10);
    $links = $tm->getResourceLinks('css');
    $expected = array(
      '<link type="text/css" rel="stylesheet" href="/assets/css/test.css">',
      '<link type="text/css" rel="stylesheet" href="/assets/css/testInit.css">'
    );
    $this->assertEquals($expected, $links, 'Resources not retrieved correctly', 0.0, 20, true);

    // Aggregated
    $links = $tm->getResourceLinks('css',true);
    $expected = array('<link type="text/css" rel="stylesheet" href="/assets/css/aggregate.css?m[10]=test&m[100]=testInit">');
    $this->assertEquals($expected, $links, 'Aggregated resources not retrieved correctly', 0.0, 20, true);
  }
}

?>
