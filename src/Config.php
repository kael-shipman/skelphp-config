<?php
namespace Skel;

class Config implements Interfaces\Config {
  //Execution Profiles
  const PROFILE_PROD = 1;
  const PROFILE_BETA = 2;
  const PROFILE_TEST = 4;

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
        $testParams = $this->getTestParams();
        if (!array_key_exists($m, $testParams)) $this->$m();
        else {
          if (!is_array($testParams[$m])) $testParams[$m] = array($testParams[$m]);
          call_user_func_array(array($this, $m), $testParams[$m]);
        }
      } catch (NonexistentConfigException $e) {
        $errors[] = $e->getMessage();
      }
    }
    
    if (count($errors) > 0) throw new \RuntimeException("Your configuration is incomplete:\n\n".implode("\n  ", $errors));
  }

  public function __construct(string $baseFilename) {
    $global = "$baseFilename.php";
    $local = "$baseFilename.local.php";
    if (!is_file($global)) throw new NonexistentFileException("You must have a global configurations file named `".basename("$baseFilename.php")."` in the expected location (even if it's empty).");
    if (!is_file($local)) throw new NonexistentFileException("You must have a local configurations file named `".basename("$baseFilename.local.php")."` (even if it's empty). Configurations in this file will overrided configurations in the global configurations file.");

    $global = require $global;
    $local = require $local;

    if (!is_array($global) || !is_array($local)) throw new \RuntimeException("Configuration files should return an array of configurations");
    if (count(array_diff(array_keys($local), array_keys($global))) > 0) throw new \RuntimeException("Your local configuration file contains configurations that are not defined globally! This is risky because local config files are not version-controlled, and the program may not throw errors for missing config until running in a production environment. Please define ALL configuration keys in the global file first, then use the local file to override them.");

    $this->config = array_replace($global, $local);
    if ($this->getExecutionProfile() != static::PROFILE_PROD) $this->checkConfig();
  }

  protected function get(string $key) {
    if (!isset($this->config[$key])) throw new NonexistentConfigException("Your configuration doesn't have a value for the key `$key`");
    return $this->config[$key];
  }

  public function getExecutionProfile() {
    return $this->get('exec-profile');
  }

  public function dump() {
    $dump = array();
    foreach ($this->config as $k => $v) $dump[] = "$k: `$v`;";
    return implode("\n", $dump);
  }
}


