<?php

class sfZfAutoload extends sfCoreAutoload
{
  protected $zendLoader = false;

  /**
   * Retrieves the singleton instance of this class.
   *
   * @return sfZfAutoload sfZfAutoload implementation instance.
   */
  static public function getInstance()
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfZfAutoload();
    }

    return self::$instance;
  }

  /**
   * Register sfZfAutoload in spl autoloader.
   *
   * @return void
   */
  static public function register()
  {
    if (self::$registered)
    {
      return;
    }

    ini_set('unserialize_callback_func', 'spl_autoload_call');
    if (false === spl_autoload_register(array(self::getInstance(), 'autoload')))
    {
      throw new sfException(sprintf('Unable to register %s::autoload as an autoloading method.',
        get_class(self::getInstance())));
    }

    self::$registered = true;
  }  

  /**
   * Handles autoloading of classes.
   *
   * @param  string  $class  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  public function autoload($class)
  {
    if ($path = $this->getClassPath($class))
    {
      require $path;

      return true;
    }

    if (false === $this->zendLoader)
    {
      $this->registerZend();
    }

    Zend_Loader_Autoloader::autoload($class);

    return false;
  }

  protected function registerZend()
  {
    set_include_path(sfConfig::get('sf_lib_dir').'/vendor'.PATH_SEPARATOR.get_include_path());
    require_once sfConfig::get('sf_lib_dir').'/vendor/Zend/Loader.php';
    require_once sfConfig::get('sf_lib_dir').'/vendor/Zend/Loader/Autoloader.php';
    $this->zendLoader = Zend_Loader_Autoloader::getInstance();
  }
}