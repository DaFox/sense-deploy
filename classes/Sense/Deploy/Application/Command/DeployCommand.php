<?php
namespace Sense\Deploy\Application\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeployCommand extends AbstractCommand {
    protected function configure() {
        $this->setName('deploy');
        $this->setDescription('Deploy project.');
        $this->addArgument('project', InputArgument::REQUIRED, 'Start the worker as a master process.');
        $this->addArgument('environment', InputArgument::REQUIRED, 'Path to configuration file.');
        $this->addArgument('release', InputArgument::OPTIONAL, 'Path to configuration file.', 'head');

        // @todo: Refactor this
        $this->addOption('config', null, InputOption::VALUE_REQUIRED, 'Path to configuration file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output);

        # Validate options

        # Setup client
        $client = new \GearmanClient();
        $client->addServers($this->_config->get('servers'));

        $client->setExceptionCallback(function(\GearmanTask $task) use($output) {
            $output->writeln("<error>Error executing deploy task: " . $task->data() . "</error>");
        });

        $client->addTask('deploy', json_encode(array(
            'project'     => $input->getArgument('project'),
            'environment' => $input->getArgument('environment'),
            'release'     => $input->getArgument('release')
        )));

        # Start deployment
        $client->runTasks();
    }
}