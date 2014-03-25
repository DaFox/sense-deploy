<?php

class DeployBuildJob {

}

class ReleaseBuildJob {

}

class DeployCommand {

    public function execute() {
        $manager = new DeployManager();
        $manager->deploy()
    }
}

class DeployManager {

    /**
     * Deploy the project
     *
     * @param Build $build
     * @param bool $environment
     */
    public function deploy(Build $build, $environment) {
        if(!$build->getProject()->hasEnvironment($environment)) {
            # geht net
        }

        $job = new DeployBuildJob();
        $job->setBuild($build);
        $job->setEnvironment($environment);
        $job->setUser($this->getUser());
        $job->save();

        $handle = $this->client->doBackground('deploy-build', serialize($job));

        $job->setHandle($handle);
        $job->save();
    }

    /**
     * @param Build $build
     * @param $environment
     */
    public function release(Build $build, $environment) {
        if(!$build->getProject()->hasEnvironment($environment)) {
            # geht net
        }
    }
}