#!/usr/bin/env bash

#
# Varaibles used below
#

HOME=/home/vagrant

#
# Clone this repo, sharing folders don't always work
#

sudo su vagrant -c "cd $HOME && git clone https://github.com/colmsjo/docker.git"


#
# This script installs the necessary stuff for the docker host.
# Images for the containers are built with Dockerfiles (see at the bottom)
#

sudo apt-get update

#
# Install docker.io
#

sudo apt-get install -y python-software-properties software-properties-common python-pip python-dev libevent-dev
sudo add-apt-repository ppa:dotcloud/lxc-docker
sudo apt-get update
DEBIAN_FRONTEND=noninteractive sudo apt-get install -y lxc-docker

# Need to run docker with other flags, this file need to be updated once the machine is up
sudo cp $HOME/docker/etc/init/docker.conf /etc/init
sudo su vagrant -c "echo alias docker=\'docker -H=tcp://127.0.0.1:4243\' >> $HOME/.profile"
sudo service docker restart


#
# Nifty tools
#

sudo apt-get install -y git unzip s3cmd curl dkms postgresql-client-common postgresql-client-9.1 mysql-client 


#
# Install NodeJs, grunt and Coffeescript
#

sudo apt-get update -y
sudo apt-get install -y python g++ make software-properties-common
sudo add-apt-repository -y ppa:chris-lea/node.js
sudo apt-get update -y
sudo apt-get install -y nodejs

sudo npm install grunt grunt-cli -g
sudo npm install coffee-script -g production


#
# Install NodeJs Jacc and redis
#

# sudo apt-get install -y redis-server supervisor
# sudo npm install jacc -g


#
# Setup ubuntu env
#

sudo sh -c "cat $HOME/docker/etc/environment >> /etc/environment"
sudo sh -c "cat $HOME/docker/etc/sudoers >> /etc/sudoers"
