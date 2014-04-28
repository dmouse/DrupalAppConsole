<?php
/**
 * File content 
 * Drupal\AppConsole\Helper\DrupalBootstrapHelper
 */

namespace Drupal\AppConsole\Helper;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Drupal\Core\DrupalKernel;

class DrupalBootstrapHelper extends Helper 
{

  /**
   * @var boolean
   */
  protected $isBoot;

  /**
   * @param string $pathToBootstrapFile
   */
  public function bootstrapConfiguration($pathToBootstrapFile) 
  {
    if ($pathToBootstrapFile){
      require_once $pathToBootstrapFile;
      \drupal_bootstrap(DRUPAL_BOOTSTRAP_CONFIGURATION);
      $this->isBoot = true;
    }
  }

  // ToDo: Evaluate delete this function, becuase was replaced by bootstrapCode
  public function bootstrapFull() 
  {
    \drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
  }

  public function bootstrapCode() 
  {
    \drupal_bootstrap(DRUPAL_BOOTSTRAP_CODE);
    $this->isBoot = true;
  }

  public function getDrupalRoot()
  {
    if (defined('DRUPAL_ROOT')){
      return DRUPAL_ROOT;
    }
    else{
      return getcwd();
    }
  }

  public function isBoot(){
    return $this->isBoot ? true:false;
  }

  /**
   * @see \Symfony\Component\Console\Helper\HelperInterface::getName()
   */
  public function getName()
  {
    return 'bootstrap';
  }
}
