#!/bin/bash

ANSIBLE_VERSION=2.5.0

VAGRANT_BASHRC=/home/vagrant/.bashrc
VAGRANT_BASH_PROFILE=/home/vagrant/.bash_profile
ROOT_BASHRC=/root/.bashrc
VAGRANT_AWS_CREDS_FILE=/home/vagrant/aws_creds.sh
VAGRANT_SSH_DIR=/home/vagrant/.ssh
ANSIBLE_CONFIG_FILE="/home/vagrant/.ansible.cfg"

# Add EPEL, man pages, and pre-reqs.
yum install -y epel-release 2>/dev/null
yum install -y man man-pages
yum install -y vim \
    git \
    screen \
    tree \
    colordiff \
    python-pip \
    bash-completion \
    nc \
    socat \
    telnet \
    unzip \
    gcc \
    gcc-c++ \
    autoconf \
    automake \
    python-devel \
    libselinux-python 2>/dev/null

# Update cURL and wget to get man pages.
yum update -y curl wget

# Upgrade PIP version.
pip install --upgrade pip

if [ ! -f /usr/bin/ansible ] || [ `ansible --version | grep -c "ansible ${ANSIBLE_VERSION}"` -lt 1 ]; then
    # Install Ansible.
    pip install ansible==${ANSIBLE_VERSION}
fi

# Install boto so I can use the AWS API.
pip install boto boto3

# AWS requires a sync'd clock, so install NTPD.
yum install -y ntp
systemctl start ntpd
systemctl enable ntpd

if [ ! -f /usr/local/aws/bin/aws ]; then
    # Install AWS CLI.
    curl -s "https://s3.amazonaws.com/aws-cli/awscli-bundle.zip" -o "awscli-bundle.zip"
    unzip awscli-bundle.zip
    ./awscli-bundle/install -i /usr/local/aws -b /usr/local/bin/aws
    ln -s /usr/local/bin/aws /usr/bin/aws
    rm awscli-bundle.zip
    rm -rf awscli-bundle
fi

# Create the AWS CLI creds script.
AWS_ACCESS=`cat /scripts/aws_cli_creds | cut -d ':' -f 1`
AWS_SECRET=`cat /scripts/aws_cli_creds | cut -d ':' -f 2`
cat > ${VAGRANT_AWS_CREDS_FILE} <<EOF
export AWS_ACCESS_KEY_ID='${AWS_ACCESS}'
export AWS_SECRET_ACCESS_KEY='${AWS_SECRET}'
EOF

chown vagrant:vagrant ${VAGRANT_AWS_CREDS_FILE}
chmod 0600 ${VAGRANT_AWS_CREDS_FILE}

# Custom bash files.
cp /vagrant/deploy/vagrant-configs/bash/vagrant.bashrc ${VAGRANT_BASHRC}
chown vagrant:vagrant ${VAGRANT_BASHRC}

cp /vagrant/deploy/vagrant-configs/bash/root.bashrc ${ROOT_BASHRC}
source ${ROOT_BASHRC}

# Custom .bash_profile.
cp /vagrant/deploy/vagrant-configs/bash/vagrant.bash_profile ${VAGRANT_BASH_PROFILE}
chown vagrant:vagrant ${VAGRANT_BASH_PROFILE}

# Create vagrant .ssh directory.
mkdir -p ${VAGRANT_SSH_DIR}
chown vagrant:vagrant ${VAGRANT_SSH_DIR}
chmod 0700 ${VAGRANT_SSH_DIR}

# Copy over SSH keys.
cp /vagrant/deploy/vagrant-configs/ssh/vagrant.rsa ${VAGRANT_SSH_DIR}/id_rsa
cp /vagrant/deploy/vagrant-configs/ssh/vagrant.rsa.pub ${VAGRANT_SSH_DIR}/id_rsa.pub
ansible-vault decrypt ${VAGRANT_SSH_DIR}/id_rsa ${VAGRANT_SSH_DIR}/id_rsa.pub
chown vagrant:vagrant ${VAGRANT_SSH_DIR}/id_rsa ${VAGRANT_SSH_DIR}/id_rsa.pub
chmod 0600 ${VAGRANT_SSH_DIR}/id_rsa ${VAGRANT_SSH_DIR}/id_rsa.pub

if [ ! `pgrep -f 'ssh-agent'` ]; then
    # Start up the SSH agent.
    eval "$(ssh-agent -s)"
    ssh-add ${VAGRANT_SSH_DIR}/id_rsa 2>/dev/null
fi

if [ ! -f ${ANSIBLE_CONFIG_FILE} ]; then
    # Add the Ansible config file.
    ln -s /vagrant/deploy/ansible/ansible.cfg ${ANSIBLE_CONFIG_FILE}
fi

if [ ! -f /etc/bash_completion.d/ansible-completion.bash ]; then
    # Auto-completions for Ansible.
    wget -q -O ./ansible-completions.zip https://github.com/dysosmus/ansible-completion/archive/master.zip
    unzip ./ansible-completions.zip
    rm ./ansible-completions.zip
    mv ./ansible-completion-master/*.bash /etc/bash_completion.d/
    rm -rf ./ansible-completion-master
fi
