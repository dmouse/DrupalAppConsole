<?php

namespace Drupal\AppConsole\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

abstract class ContainerAwareCommand extends Command implements ContainerAwareInterface {

  /**
   * @var Symfony\Component\DependencyInjection\ContainerInterface
   */
  private $container;

  /**
   * @return ContainerInterface
   */
  protected function getContainer() {
    $boostrap = $this->getHelperSet()->get('bootstrap');
    if (null === $this->container && $boostrap->isBoot()) {
      $this->container = $this->getApplication()->getKernel()->getContainer();
    }

    return $this->container;
  }

  /**
   * {@inheritdoc}
   */
  public function setContainer(ContainerInterface $container = null) {
    $this->container = $container;
  }

  /**
   * [getModules description]
   * @param  boolean $core Return core modules
   * @return array list of modules
   */
  public function getModules($core = false) {
    // modules collection
    $modules = array();

    $boostrap = $this->getHelperSet()->get('bootstrap');
    if ($boostrap->isBoot()){
      //get all modules
      $all_modules = \system_rebuild_module_data();

      // Filter modules
      foreach ($all_modules as $name => $filename) {
        if ( !preg_match('/^core/',$filename->uri) && !$core){
          array_push($modules, $name);
        }
        else if ($core){
          array_push($modules, $name);
        }
      }
      return $modules;
    }
    return [];
  }

  public function getServices() {
    return $this->getContainer()->getServiceIds();
  }

}
