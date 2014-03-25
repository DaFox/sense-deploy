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

- Start 1 to X master workers

```bash
$ bin/sense-deploy worker --master
```

- Start 1 to X deploy workers

```bash
$ bin/sense-deploy worker
```

- Run

```bash
$ bin/sense-deploy deploy <project> <environment> <release>
```
