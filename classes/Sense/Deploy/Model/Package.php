<?php
namespace Sense\Build\Jenkins;

class Jenkins {
    public function __construct($directory) {
        $this->directory = $directory;
    }

    public function getProject($projectName) {
        if(!file_exists($this->directory . '/jobs/' . $projectName)) {
            return null;
        }

        $project = new Project($this->directory . '/jobs/' . $projectName);
        $project->setName($projectName);

        return $project;
    }

    public function getProjects() {

    }
}

class Project {
    private $directory;
    private $name;

    public function __construct($directory) {
        $this->directory = $directory;
    }

    public function getBuild($buildNumber) {
        if(!file_exists($this->directory . '/builds/' . $buildNumber)) {
            return null;
        }

        $build = new Build($this->directory . '/builds/' . $buildNumber);
        $build->setNumber($buildNumber);

        return $build;
    }

    public function getBuilds() {

    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }
}

class Build {
    private $directory;
    private $number;

    public function __construct($directory) {
        $this->directory = $directory;
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    public function getDirectory() {
        return $this->directory;
    }
}

class DeployClient extends \GearmanClient {

    public function __construct() {
        parent::__construct();
    }

    public function deployBuild(array $build) {
        return $this->doBackground('deploy-build', json_encode($build));
    }
}

class DeployHelper {
    public function deployBuild(array $build) {
        return $this->getClient()->deployBuild($build);
    }

    public function releaseBuild(array $build) {
        return $this->getClient()->releaseBuild($build);
    }

    public function getClient() {
        $client = new DeployClient();
        return $client;
    }
}

class DeployController extends AbstractController {

    public function deployAction() {
        $projectName = $this->getRequest()->getParam('project');
        $buildNumber = $this->getRequest()->getParam('build');
        $environment = $this->getRequest()->getParam('environment');

        # Create the Jenkins object
        $jenkins = new Jenkins('/data/jenkins');

        # Get the build
        $build = $jenkins->getProject($projectName)->getBuild($buildNumber);

        # Deploy the build
        $manager = new DeployHelper();

        echo $manager->deployBuild(array(
            'directory'   => $build->getDirectory(),
            'release'     => $build->getNumber(),
            'environment' => $environment
        ));
    }
}
