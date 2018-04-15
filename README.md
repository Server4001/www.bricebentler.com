# bricebentler.com

## My website.

### Setting up the bricebentler.loc local environment

* vagrant up bricebentler_com
* Go to `bricebentler.loc` in your browser
* You can ssh into the vagrant environment with: `vagrant ssh bricebentler_com`
* Log files are at: `/var/log/nginx/www.log`

### Setting up the deploy environment

* Copy the vault password to the following file: `./deploy/ansible/scripts/ansible_vault`
* This file should look like the following:
```bash
#!/bin/bash
echo 'this-is-soooo-secret-its-not-even-funny'
```
* Copy the AWS CLI creds to the following file: `./deploy/ansible/scripts/aws_cli_creds`
* This file should look like the following:
```
aws_access_key:aws_secret_key
```
* Run: `vagrant up deploy`
