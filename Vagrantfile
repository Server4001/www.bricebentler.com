# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"

$bricebentler_com_script = <<SCRIPT
rpm -Uvh https://rpm.nodesource.com/pub_12.x/el/7/x86_64/nodesource-release-el7-1.noarch.rpm
yum install -y nodejs-12.16.3 epel-release
yum install -y nginx
systemctl start nginx
systemctl enable nginx
rm -rf /usr/share/nginx/html
ln -fs /vagrant/s3 /usr/share/nginx/html
chmod 0777 /var/log/nginx
SCRIPT

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

    config.vm.define :bricebentler_com, primary: true, autostart: true do |bricebentler_com|
        bricebentler_com.vm.box = "bento/centos-7.5"
        bricebentler_com.vm.box_version = "201808.24.0"

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
