# -*- mode: ruby -*-
# vi: set ft=ruby :


Vagrant.configure(2) do |config|

    #config.vm.box = "centos67"
    config.vm.box = "CentOS67-httpd24-php56"
    config.vm.network "private_network", ip: "192.168.33.11"
    #config.vm.network "public_network"
    config.vm.hostname = "centos"

    config.vm.synced_folder "www", "/var/www/html", :mount_options => ["dmode=777", "fmode=666"]
    #config.vm.provision :shell, :inline => "ln -s /home/ad102m0tyl/html /var/www/public"
    
    config.hostsupdater.aliases = ["ja.dev.plusstyle", "en.dev.plusstyle"]

end
