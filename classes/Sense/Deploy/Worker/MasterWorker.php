<?php
namespace Sense\Deploy\Worker;

use Sense\Deploy\Shell;
use Zend\Config\Config;
use Zend\Config\Reader\Yaml as ConfigReader;

class LockException extends \Exception {

    public function __construct($file) {
        parent::__construct("Workspace is locked by $file.");
    }
}

class MasterWorker extends AbstractWorker {
    /**
     * @var \GearmanClient
     */
    private $client;

    /**
     * @var Shell
     */
    private $shell;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $project;

    /**
     * @var string
     */
    private $release;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var array
     */
    private $targets;

    /**
     * @var array
     */
    private $exceptions = array();


    /**
     * The list of tasks to be executed on deploy.
     *
     * @var array
     */
    private $tasks = array();

    public function __construct(Config $config) {
        parent::__construct($config);

        $this->addFunction('deploy', array($this, 'onDeploy'));
    }

    public function onDeploy(\GearmanJob $job) {
        if(($input = json_decode($job->workload())) === null) {
            throw new \Exception("Unable to decode workload: {$job->workload()}");
        }

        $this->project     = $input->project;
        $this->environment = $input->environment;
        $this->release     = $input->release;
        $this->targets     = array();

       try {
            $this->setup();
            $this->lock();
            $this->deploy();
            $this->release();
            $this->unlock();
        }
        catch(LockException $ex) {
            $job->sendException($ex->getMessage());
        }
        catch(\Exception $ex) {
            $job->sendException($ex->getMessage());
            $this->unlock();
        }
    }

    private function setup() {
        $this->setupProject();
        $this->setupClient();
        $this->setupShell();
    }

    private function setupClient() {
        $this->client = new \GearmanClient();
        $this->client->addServers($this->getServerList());

        $this->client->setExceptionCallback(function(\GearmanTask $task) {
            $this->output->writeln("<error>" . $task->data() . "</error>");
            $this->exceptions[$task->unique()] = $task->data();
        });
    }

    private function setupShell() {
        $this->shell = new Shell($this->output);
    }

    private function setupProject() {
        $yamlReader = new ConfigReader(array('Symfony\Component\Yaml\Yaml', 'parse'));
        $yamlConfig = new Config($yamlReader->fromFile(APPLICATION_ROOT . '/application/config/projects/' . $this->project . '.yaml'));

        $this->config = $yamlConfig;
    }

    private function getProjectLockFile() {
        $replace = array(
            '%project%'     => $this->project,
            '%environment%' => $this->environment
        );

        return str_replace(array_keys($replace), array_values($replace), $this->getLockFile());
    }

    private function lock() {
        if(!$this->shell->lock($this->getProjectLockFile())) {
            throw new LockException($this->getProjectLockFile());
        }
    }

    private function unlock() {
        $this->shell->unlock($this->getProjectLockFile());
    }

    private function deploy() {
        $this->output->writeln("<info>Deployment started.</info>");
        $this->command('deploy-package');
        $this->output->writeln("<info>Deployment successful.</info>");
    }

    private function release() {
        $this->output->writeln("<info>Releasing started.</info>");
        $this->command('release-package');
        $this->output->writeln("<info>Releasing successful.</info>");
    }

    private function command($name) {
        foreach($this->getTargets() as $target) {
            $this->output->writeln("  Adding <fg=cyan>{$target['target']}</fg=cyan> to target list.");
            $this->client->addTask($name, json_encode($target));
        }

        $this->client->runTasks();

        if(count($this->exceptions) > 0) {
            $failed = count($this->exceptions);
            $total  = count($this->getTargets());

            $this->exceptions = array();
            throw $this->createException("Command '$name' failed on $failed of $total targets.");
        }
    }

    public function getTargets() {
        if(empty($this->targets)) {
            foreach($this->config->get($this->environment) as $package) {
                /** @var Config $package */
                foreach($package->get('hosts') as $host) {
                    $this->targets[] = array(
                        'target' => $package['user'] . '@' . $host,
                        'directory' => $package['directory'],
                        'package' => str_replace('%release%', $this->release, $package['archive']),
                        'replace' => isset($package['replace']) ? $package['replace'] : array(),
                        'release' => $this->release
                    );
                }
            }
        }

        return $this->targets;
    }
}