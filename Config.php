<?php
namespace Skel;

class Config implements  {
  protected $config;

  public function checkConfig() {
    $ref = new \ReflectionClass($this);
    $interfaces = $ref->getInterfaces();
    $check = array();
    foreach($interfaces as $name => $i) {
      if ($name == 'Skel\Interfaces\Config') continue;
      $methods = $i->getMethods();
      foreach($methods as $m) {
        $m = $m->getName();
        if (substr($m,0,3) == 'get' && strlen($m) > 3) $check[$m] = $m;
      }
    }

    $errors = array();
    foreach($check as $m) {
      try {
        $this->$m();
      } catch (NonexistentConfigException $e) {
        $errors[] = $e->getMessage();
      }
    }
    
    if (count($errors) > 0) throw new RuntimeException("Your configuration is incomplete:\n\n".implode("\n  ", $errors));
  }

  public function __construct(string $baseFilename bool $checkConfig=true) {
    $global = "$baseFilename.php";
    $local = "$baseFilename.local.php";
    if (!is_file($global)) throw new NonexistentFileException("You must have a global configurations file named `".basename("$baseFilename.php")."` in the expected location (even if it's empty).");
    if (!is_file($local)) throw new NonexistentFileException("You must have a local configurations file named `".basename("$baseFilename.local.php")."` (even if it's empty). Configurations in this file will overrided configurations in the global configurations file.");

    $global = require $global;
    $local = require $local;

    if (!is_array($global) || !is_array($local)) throw new \RuntimeException("Configuration files should return an array of configurations");

    $this->config = array_replace($global, $local);
    if ($checkConfig) $this->configCheck();
  }

  public function get(string $key) {
    if (!isset($this->config[$key])) throw new NonexistentConfigException("Your configuration doesn't have a value for the key `$key`");
    return $this->config[$key];
  }

  public function dump() {
    $dump = array();
    foreach ($this->config as $k => $v) $dump[] = "$k: `$v`;";
    return implode("\n", $dump);
  }

  public function set(string $key, $val) {
    $this->config[$key] = $val;
    return $this;
  }
}

