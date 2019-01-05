# bricebentler.com

### Overview

This project contains 3 main directories:

* s3
    * This contains the static assets for my website hosted on AWS S3.
* lambda/email-send
    * This contains the AWS lambda function called by the contact form on my website.
* scripts
    * Scripts used for deploying the static website to S3 and the Lambda function to Lambda.

### Setting up

* vagrant up bricebentler_com
* Go to `192.168.35.11` in your browser
* You can ssh into the vagrant environment with: `vagrant ssh bricebentler_com`
* Log files are at: `/var/log/nginx`
* Copy `./lambda/email-send/etc/config.json.local` to `./lambda/email-send/etc/config.json` and replace with actual values.
* Copy `./scripts/config/secrets.sh.example` to `./scripts/config/secrets.sh` and replace with actual values.

### Testing the lambda function

* The lambda function can be run on your local machine by:
    * SSHing into the vagrant environment: `vagrant ssh bricebentler_com`
    * Going into the root of the lambda function directory: `cd /vagrant/lambda/email-send`
    * Ensuring you have the NPM packages installed: `npm install`
    * Running the test:lambda command: `npm run test:lambda`
    * Note that you will need to have copied the config file at `etc/config.json` prior to running ths command.
* Unit tests can be run on your local machine by:
    * SSHing into the vagrant environment: `vagrant ssh bricebentler_com`
    * Going into the root of the lambda function directory: `cd /vagrant/lambda/email-send`
    * Ensuring you have the NPM packages installed: `npm install`
    * Running the test command: `npm test`

### Deploy Scripts

* First, copy `./scripts/config/secrets.sh.example` to `./scripts/config/secrets.sh` and replace the contents with actual values.
* Deploy to lambda: `./scripts/deploy-lambda -h`
* Deploy to S3: `./scripts/deploy-s3 -h`

### TODO

* Document process for setting up S3, CloudFront, Lambda, IAM users, IAM roles.
* Move initial setup into infra as code (terraform/cloudformation or similar).
