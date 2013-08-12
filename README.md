# Introduction

This repo contains the bulk of of the Gizur Saas setup. Some parts of managed
through the nodejs_applications repo.

 * Install with `make install2`
 * Build documentation with `make docs2`
 * Run unit tests with `make tests2`
 * Check code quality with `make lint2`
 * Check test coverage with `make coverage2`


See the Wiki for more information: https://github.com/gizur/gizurcloud/wiki


NOTE: The old README file has been placed here:
https://github.com/gizur/gizurcloud/wiki/Development_Guide%23old_php_applications_readme


## Setup development environment

Pre-requisites:

 * Virtualbox
 * Vagrant (found at vagrantup.com)

Install and start a development envinment running `vagrant up vb`. Stop the virtual machine with `vagrant halt vb`.
The machine has docker.io and hipache installed. A sciprt is used for simplfying the management of docker and hipache, 
see https://github.com/colmsjo/jacc.


It is also possible to run the machine in AWS:

```
# Install AWS plusin
vagrant plugin install vagrant-aws
vagrant plugin list

# A dummy box is needed
vagrant box add dummy https://github.com/mitchellh/vagrant-aws/raw/master/dummy.box

# Set this variable to use AWS, it should bot be set to use a local Virtualbox instead
export VAGRANT_AWS='Yes'

# These environment variables need to be set, put in bashrc/bach_profile env 
# NOTE: Only the region us-east-1 seams to work at the moment.
export AWS_API_KEY=...
export AWS_API_SECRET=...
export AWS_PRIVATE_KEY_PATH=...
export AWS_KEYAIR_NAME=...
export AWS_REGION=...

vagrant up aws
```

