# -*- mode: ruby -*-
# vi: set ft=ruby :

#
# Start VM with docker installed
#


Vagrant.configure("2") do |config|

  #
  # A local virtualbox
  #
  # Using a bridged network instead of NAT (the VM will apear to be on the same network as the host)
  #

  config.vm.define :ubuntu do |vb_config|
    vb_config.vm.box = "precise64"
    vb_config.vm.box_url = "http://files.vagrantup.com/precise64.box"

#    vb_config.vm.network :public_network
    vb_config.vm.network :forwarded_port, guest: 80, host: 8080, auto_correct: true
    vb_config.vm.network :forwarded_port, guest: 8081, host: 8081, auto_correct: true

    # OpenERP
    vb_config.vm.network :forwarded_port, guest: 8069, host: 8069, auto_correct: true

    # Bitcoin
    vb_config.vm.network :forwarded_port, guest: 8333, host: 8333, auto_correct: true
    vb_config.vm.network :forwarded_port, guest: 18333, host: 18333, auto_correct: true

    # Riak
    vb_config.vm.network :forwarded_port, guest: 8098, host: 8098, auto_correct: true

    vb_config.vm.provision :shell, :path => "bootstrap.sh"
    vb_config.vm.provision :shell, :path => "bootstrap2.sh"
  end


  config.vm.provider :virtualbox do |vb|
    vb.customize ["modifyvm", :id, "--memory", "2048", "--cpus", "2"]
#    vb.gui = true
  end



end
