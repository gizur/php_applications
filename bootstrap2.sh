#!/usr/bin/env bash

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
sudo apt-get install -y lxc-docker


#
# Nifty tools
#

sudo apt-get install -y git unzip s3cmd curl


#
# Install NodeJs
#

sudo apt-get update -y
sudo apt-get install -y python g++ make software-properties-common
sudo add-apt-repository -y ppa:chris-lea/node.js
sudo apt-get update -y
sudo apt-get install -y nodejs


#
# Install CoffeeScript
#

sudo apt-get install -y coffeescript


#
# Install PHP
#

sudo apt-get install php5-cli php5-curl -y


#
# Install grunt, used for nodejs development
#

sudo npm install grunt grunt-cli -g


#
# Install redis, used by hipache
#

sudo apt-get install -y redis-server


#
# Install Jacc
#

sudo npm install jacc -g
