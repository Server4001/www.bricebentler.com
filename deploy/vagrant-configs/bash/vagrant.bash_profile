# .bash_profile

# Get the aliases and functions
if [ -f ~/.bashrc ]; then
	. ~/.bashrc
fi

# User specific environment and startup programs

PATH=$PATH:$HOME/.local/bin:$HOME/bin

export PATH

export CLICOLOR=1
export LSCOLORS=Gxfxcxdxbxegedabagacad

RED="\[\033[0;31m\]"
YELLOW="\[\033[0;33m\]"
GREEN="\[\033[0;32m\]"
NO_COLOR="\[\033[0m\]"

PS1="$GREEN\u@\h$NO_COLOR:\n\$ "

export ANSIBLE_VAULT_PASSWORD_FILE="/scripts/ansible_vault"
source $HOME/aws_creds.sh
EC2_REGION='us-west-2'
