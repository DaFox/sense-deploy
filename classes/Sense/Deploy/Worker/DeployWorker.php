<?php
namespace Sense\Deploy\Worker;

use Zend\Config\Config;

class DeployWorker extends AbstractWorker {

    public function __construct(Config $config) {
        parent::__construct($config);

        $this->addFunction('deploy-package', array($this, 'deployPackage'));
        $this->addFunction('release-package', array($this, 'onReleasePackage'));
    }

    public function deployPackage(\GearmanJob $job) {
        try {

            if(($input = json_decode($job->workload())) === null) {
                throw new \Exception("Unable to decode workload: {$job->workload()}");
            }

            $local     = escapeshellarg($input->package);
            $target    = escapeshellarg($input->target);
            $directory = escapeshellarg($input->directory . '/releases/' . $input->release);

            $this->execRemoteCommand($target, "\"if [ ! -d $directory ]; then mkdir -p $directory; fi\"");
            $this->execRemoteCommand($target, "\"cd $directory && tar xzf -\" <$local");

            // Renaming files before releasing
            foreach($input->replace as $src => $dst) {
                $src = escapeshellarg($input->directory . '/releases/' . $input->release . '/' . $src);

                if($dst !== '/dev/null') {
                    $dst = escapeshellarg($input->directory . '/releases/' . $input->release . '/' . $dst);
                    $this->execRemoteCommand($target, "\"mv -f $src $dst\"");
                }
                else {
                    $this->execRemoteCommand($target, "\"rm -rf $src\"");
                }
            }

            $job->sendComplete("Package deployed on {$input->target}");
        }
        catch(\Exception $ex) {
            $job->sendException($ex->getMessage());
        }
    }


    public function onReleasePackage(\GearmanJob $job) {
        try {
            if(($input = json_decode($job->workload())) === null) {
                throw new \Exception("Unable to decode workload: {$job->workload()}");
            }

            $current   = escapeshellarg($input->directory . '/current');
            $directory = escapeshellarg($input->directory . '/releases/' . $input->release);
            $target    = escapeshellarg($input->target);

            $this->execRemoteCommand($target, "\"ln -fs -T $directory $current\"");
            $job->sendComplete("Package released on {$input->target}");
        }
        catch(\Exception $ex) {
           $job->sendComplete("Package deployed on {$input->target}");
        }
    }

}