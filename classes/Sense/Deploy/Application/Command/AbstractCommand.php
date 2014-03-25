<?php
/**
 * Created by PhpStorm.
 * User: Thomas
 * Date: 21.03.14
 * Time: 20:58
 */

namespace Sense\Deploy\Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Zend\Config\Config;
use Zend\Config\Reader\Yaml as ConfigReader;


class AbstractCommand extends Command {
    /**
     * @var InputInterface
     */
    protected $_input;

    /**
     * @var OutputInterface
     */
    protected $_output;

    /**
     * @var Config
     */
    protected $_config;

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->_input = $input;
        $this->_output = $output;

        $this->_setupConfig();
    }

    protected function _setupConfig() {
        $this->_config = new Config(array(
            'servers'  => '127.0.0.1:4730',
            'lockfile' => APPLICATION_ROOT . '/application/locks/%project%.lock'
        ), true);

        if(($config = realpath(APPLICATION_ROOT . "/application/config/worker.yaml")) !== false) {
            $yamlReader = new ConfigReader(array('Symfony\Component\Yaml\Yaml', 'parse'));
            $yamlConfig = new Config($yamlReader->fromFile($config));

            $this->_config->merge($yamlConfig);
        }

        $this->_config->setReadOnly();
    }
}