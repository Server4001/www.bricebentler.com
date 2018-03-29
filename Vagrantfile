# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

$script = <<SCRIPT
rm -rf /var/www/html
ln -fs /vagrant/docroot /var/www/html
SCRIPT

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    config.vm.box = "server4001/php71-centos"
    config.vm.box_version = "0.1.0"

    config.vm.network :private_network, ip: "192.168.35.11"

    config.vm.hostname = "www.bricebentler.loc"
    config.vm.synced_folder "./", "/vagrant", mount_options: ["dmode=777,fmode=777"]

    config.vm.provider "virtualbox" do |vb|
      vb.customize ["modifyvm", :id, "--memory", "512"]
      vb.customize ["modifyvm", :id, "--cpus", "1"]
      vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    end

      config.vm.provision :shell, privileged: true, inline: $script
end
