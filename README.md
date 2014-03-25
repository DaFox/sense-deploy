# SENSE7 Deploy #

### Installation ###

```bash
$ git clone https://github.com/DaFox/sense-deploy.git
$ cd sense-deploy
$ composer update
```

# Configuration #

* Gearman configuration is done in application/config/worker.yaml
* Project configuration goes to application/config/projects/$project.yaml

# Usage #

1. Start 1 to X master workers

```bash
$ bin/sense-deploy worker --master
```

2. Start 1 to X deploy workers

```bash
$ bin/sense-deploy worker
```

3. Run

```bash
$ bin/sense-deploy deploy <project> <environment> <release>
```
