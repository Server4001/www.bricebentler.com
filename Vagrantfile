# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

$bricebentler_com_script = <<SCRIPT
rpm -Uvh https://rpm.nodesource.com/pub_8.x/el/6/x86_64/nodesource-release-el6-1.noarch.rpm
yum install -y nodejs-8.10.0
rm -rf /var/www/html
ln -fs /vagrant/s3 /var/www/html
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
end
