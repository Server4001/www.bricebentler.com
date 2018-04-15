# .bashrc

# Source global definitions
if [ -f /etc/bashrc ]; then
	. /etc/bashrc
fi

# Uncomment the following line if you don't like systemctl's auto-paging feature:
# export SYSTEMD_PAGER=

# Ansible auto-completion.
for f in $(ls /etc/bash_completion.d/ansible-*); do
    source ${f}
done

# User specific aliases and functions
alias ll="ls -lah"

diffcolor () {
    diff -u ${1} ${2} | colordiff
}
