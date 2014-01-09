#!/bin/bash

# Add the Docker repository key to your local keychain
sudo sh -c "curl https://get.docker.io/gpg | apt-key add -"

# Add the Docker repository to your apt sources list.
sudo sh -c "echo deb https://get.docker.io/ubuntu docker main > /etc/apt/sources.list.d/docker.list"

# update your sources list
sudo apt-get update

# install the latest
sudo apt-get install -y lxc-docker


