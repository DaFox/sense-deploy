<?php
namespace Sense\Deploy\Worker;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Yaml\Yaml;
use Zend\Config\Config;

abstract class AbstractWorker extends \GearmanWorker {
    /**
     * @var ConsoleOutput
     */
    protected $output;

    private $servers = array();

    public function __construct(Config $config) {
        parent::__construct();

        $this->servers  = $config->get("servers");
        $this->lockFile = $config->get("lockfile");
        $this->output   = new ConsoleOutput();

        $this->addServers($this->servers);
    }

    public function getLockFile() {
        return $this->lockFile;
    }

    public function getServerList() {
        return $this->servers;
    }

    public function start() {
        $this->output->writeln("<comment>Starting worker with PID <fg=white>" . getmypid() . "</fg=white></comment>");

        while($this->work()) {
            if ($this->returnCode() != GEARMAN_SUCCESS)
            {
                echo "return_code: " . $this->returnCode() . "\n";
                break;
            }

            $this->output->writeln("<comment>Job finished. Waiting for new job.</comment>");
        }
    }

    public function execRemoteCommand($target, $cmd) {
        $this->output->writeln("<info>  Executing remote command <fg=cyan>$cmd</fg=cyan> on <fg=cyan>$target</fg=cyan></info>");
        $line = exec("ssh -o ConnectTimeout=2 -o BatchMode=yes -o StrictHostKeyChecking=no $target $cmd 2>&1;", $output, $return);

        if($return !== 0) {
            throw $this->createException("  Error executing command: $cmd on $target. Reason: " . $line . " ($return)", $return);
        }
    }

    protected function createException($message, $code = 0) {
        $this->output->writeln("<error>$message</error>");
        return new \Exception($message, $code);
    }
}