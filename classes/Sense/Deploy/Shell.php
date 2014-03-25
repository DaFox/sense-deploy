<?php

namespace Sense\Deploy;

use Symfony\Component\Console\Output\ConsoleOutput;
use Zend\Config\Config;
use Zend\Config\Reader\Yaml;

class Shell {
    const LOCKFILE_ERROR = 69;
    const LOCKFILE_LOCKED = 73;

    public function __construct(ConsoleOutput $output) {
        $this->output = $output;
    }

    /**
     *
     */
    public function lock($lockFile) {
        $returnCode = $this->exec("lockfile -r0 " . escapeshellarg($lockFile) . " 2>&1", $output);

        if($returnCode > 0) {
            if($returnCode !== self::LOCKFILE_LOCKED) {
                $this->output->writeln("Error creating lock file (return=$returnCode).");
                $this->output->writeln($output);
            }

            return false;
        }

        return true;
    }


    public function unlock($lockFile) {
        return $this->exec("rm -f " . escapeshellarg($lockFile)) == 0;
    }

    public function exec($cmd, &$output = array()) {
        $this->output->writeln("<info>Executing <fg=white>$cmd</fg=white></info>");
        exec($cmd, $output, $return);
        return $return;
    }
}