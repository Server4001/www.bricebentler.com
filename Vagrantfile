# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

$bricebentler_com_script = <<SCRIPT
rm -rf /var/www/html
ln -fs /vagrant/docroot /var/www/html
chmod 0777 /var/log/nginx
SCRIPT

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

    config.vm.define :bricebentler_com, primary: true, autostart: true do |bricebentler_com|
        bricebentler_com.vm.box = "server4001/php71-centos"
        bricebentler_com.vm.box_version = "0.1.0"

        bricebentler_com.vm.network :private_network, ip: "192.168.35.11"

        bricebentler_com.vm.hostname = "www.bricebentler.loc"
        bricebentler_com.vm.synced_folder "./", "/vagrant", mount_options: ["dmode=777,fmode=777"]

        bricebentler_com.vm.provider "virtualbox" do |vb|
            vb.customize ["modifyvm", :id, "--memory", "512"]
            vb.customize ["modifyvm", :id, "--cpus", "1"]
            vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
        end

        bricebentler_com.vm.provision :shell, privileged: true, inline: $bricebentler_com_script
    end

    config.vm.define :deploy, primary: false, autostart: false do |deploy|
        deploy.vm.box = "bento/centos-7.2"
        deploy.vm.box_version = "2.2.7"

        deploy.vm.network :private_network, ip: "192.168.35.12"

        deploy.vm.hostname = "deploy-bricebentler"

        # Ansible gets mad when the config files are executable, so we must use 0644.
        deploy.vm.synced_folder "./", "/vagrant", mount_options: ["dmode=755,fmode=644"]
        # However we also have executable script, so create a second mount for those.
        deploy.vm.synced_folder "./deploy/ansible/scripts", "/scripts", mount_options: ["dmode=777,fmode=777"]

        deploy.vm.provider "virtualbox" do |vb|
            vb.customize ["modifyvm", :id, "--cpuexecutioncap", "90"]
            vb.customize ["modifyvm", :id, "--memory", "1024"]
            vb.customize ["modifyvm", :id, "--cpus", "2"]
            vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
        end

        deploy.vm.provision :shell, path: "./deploy/vagrant-provision.sh", privileged: true
    end
end
