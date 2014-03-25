<?php




class DeployProjectTask {









    public function onDeployProject(\GearmanJob $job) {
        try {
            if(($input = json_decode($job->workload())) === null) {
                throw new \Exception("Unable to decode workload: {$job->workload()}");
            }

            $targets = $this->getTargetsFromConfig($input->directory, $input->release);

            # Create a client connected to the same servers as the worker
            $client = new \GearmanClient();
            $client->addServer('127.0.0.1', 4730);

            $this->output->writeln("<info>Deploying project to X targets.</info>");

            foreach($targets as $target) {
                $this->output->writeln("<info>    Adding target <fg=white>{$target['target']}</fg=white></info>");
                $client->addTask('deployPackage', json_encode($target));
            }

            $client->runTasks();
            $this->output->writeln("<info>Deployment to X/Y targets successful.</info>");
            $this->output->writeln("");
            $this->output->writeln("<info>Releasing project to X targets.</info>");

            foreach($targets as $target) {
                $client->addTask('releasePackage', json_encode($target));
            }

            $client->runTasks();
            $this->output->writeln("<info>Releasing to X/Y targets successful.</info>");
        }
        catch(\Exception $ex) {
            throw $ex;
        }
    }
}