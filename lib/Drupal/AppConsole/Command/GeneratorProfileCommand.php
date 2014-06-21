<?php
/**
*@file
* Contains \Drupal\AppConsole\Command\GeneratorProfileCommand.
*/

namespace Drupal\AppConsole\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\AppConsole\Generator\ProfileGenerator;

class GeneratorProfileCommand extends GeneratorCommand
{
	protected function configure()
	{
		$this
			->setDefinition(array(
				new InputOption('name','',InputOption::VALUE_REQUIRED, 'The name of the module'),
				new InputOption('description','',InputOption::VALUE_OPTIONAL, 'Description block'),
			))
		->setDescription('Generate profile for actual configuration')
		->setHelp('The <info>generate:profile</info> command helps you generate a new profile.')
		->setName('generate:profile');
	}

	/**
	* {@inheritdoc}
	*/
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$dialog = $this->getDialogHelper();

		if ($input->isInteractive()) {
			if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
				$output->writeln('<error>Command aborted</error>');

				return 1;
			}
		}

		$container = $this->getContainer();
		$theme_handler  = $container->get('theme_handler');
		$module_handler = $container->get('module_handler');

		$name = $input->getOption('name');
		$description = $input->getOption('description');
		$dependencies = $this->getModules(true);
		$themes = array_keys($theme_handler->listInfo());
		$dependencies = array_keys($module_handler->getModuleList());

		$this
			->getGenerator()
			->generate($name, $description, $themes, $dependencies)
		;
	}

	protected function interact(InputInterface $input, OutputInterface $output)
	{
		$dialog = $this->getDialogHelper();
		$dialog->writeSection($output, 'Welcome to the Drupal Plugin Block generator');

		$helper_set = $this->getHelperSet()->get('dialog');

		// --name option
		$name = $input->getOption('name');
		if (!$name) {
			$name = $dialog->ask($output, $dialog->getQuestion('Enter the profile name', ''), '');
			$input->setOption('name', $name);
		}
		$input->setOption('name', $name);

		$description = $input->getOption('description');
		if (!$description) {
			$description = $dialog->ask($output, $dialog->getQuestion('Description', 'My Awesome Profile'), 'My Awesome Profile');
		}
		$input->setOption('description', $description);
	}

	protected function createGenerator()
	{
		return new ProfileGenerator();
	}

}
