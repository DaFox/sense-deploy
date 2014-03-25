<?php
namespace Sense\Deploy\Tasks;

class InitRemoteProjectTask {

    public function __construct(Shell $shell) {
        $this->shell = $shell;
    }

    public function run() {
        if(!$this->shell->exists()) {

        }
    }
}