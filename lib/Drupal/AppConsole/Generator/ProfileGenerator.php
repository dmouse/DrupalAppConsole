<?php
/**
*@file
* Contains \Drupal\AppConsole\Generator\PluginBlockGenerator.
*/

namespace Drupal\AppConsole\Generator;

use Drupal\Core\Config\FileStorage;

class ProfileGenerator extends Generator
{
	/**
	* Generator Plugin Block
	* @param  string $module   Module name
	* @param  string $class_name     class name for plugin block
	* @param  string $description
	* @param  array  $services list of services
	*/
	public function generate($name, $description, $themes, $dependencies)
	{
		$profiles_path = DRUPAL_ROOT.'/profiles/'.$name;

		$parameters = [
			'name'     => $name,
			'description' => $description,
		];

		$this->renderFile(
			'profile/profile.info.yml.twig',
			$profiles_path.'/'.$name.'.info.yml',
			$parameters + ['dependencies'=>$dependencies, 'themes'=>$themes]
		);

		$this->renderFile(
			'profile/profile.install.twig',
			$profiles_path.'/'.$name.'.install',
			$parameters
		);

		$this->renderFile(
			'profile/profile.profile.twig',
			$profiles_path.'/'.$name.'.profile',
			$parameters
		);

		$source_storage = \Drupal::service('config.storage');
		$profile_storage = new FileStorage($profiles_path.'/config/install');
  	foreach ($source_storage->listAll() as $name) {
    	$profile_storage->write($name, $source_storage->read($name));
  	}

	}
}
