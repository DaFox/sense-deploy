<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 21.03.14
 * Time: 20:58
 */

namespace Sense\Deploy\Application\Command;

use Sense\Deploy\Worker\DeployWorker;
use Sense\Deploy\Worker\MasterWorker;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class WorkerCommand extends AbstractCommand {
    /**
     * @var DeployWorker
     */
    protected $_worker;

    protected function configure() {
        $this->setName('worker');
        $this->setDescription('Start the deploy worker.');
        $this->addOption('master', null, InputOption::VALUE_NONE, 'Start the worker as a master process.');
        $this->addOption('config', null, InputOption::VALUE_REQUIRED, 'Path to configuration file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        parent::execute($input, $output);

        $this->_setupWorker();
        $this->_startWorker();
    }

    protected function _setupWorker() {
        $this->_worker = $this->_input->getOption('master') ?
            new MasterWorker($this->_config) :
            new DeployWorker($this->_config);
    }

    protected function _startWorker() {
        $this->_worker->start();
    }
} 