'use strict';

const https = require('https');
const util = require('util');
const path = require('path');
const fs = require('fs');
const mustache = require('mustache');

const emailSubject = 'Bricebentler.com Email Contact Form Entry';

// Helper functions.
const debugAndCallbackNull = (exitMessage, exitVariable, exitCallback) => {
    console.log(exitMessage);
    console.log(util.inspect(exitVariable, {showHidden: true, depth: null}));

    exitCallback(null);
};

const loadConfig = () => {
    const configFile = path.join(__dirname, '/etc/config.json');

    if (!fs.existsSync(configFile)) {
        throw Error(`Missing config file at ${configFile}`);
    }

    return JSON.parse(fs.readFileSync(configFile, 'utf8'));
};

const callSendgrid = (postBody, config, lambdaCallback) => {

    // TODO : Change this to a single exception with all data, and move to a helper function.
    if (config.sendgrid_api_key === undefined) {
        throw Error('Missing sendgrid api key in config');
    }
    if (config.sendgrid_to_email === undefined) {
        throw Error('Missing sendgrid to email in config');
    }
    if (config.sendgrid_to_name === undefined) {
        throw Error('Missing sendgrid to name in config');
    }
    if (config.sendgrid_from_email === undefined) {
        throw Error('Missing sendgrid from email in config');
    }
    if (config.sendgrid_from_name === undefined) {
        throw Error('Missing sendgrid from name in config');
    }

    const apiKey = config.sendgrid_api_key;
    const emailTemplate = fs.readFileSync(path.join(__dirname, '/views/email.html'), 'utf8');
    // TODO : Confirm the postBody fields work.
    const emailHtml = mustache.render(emailTemplate, {
        email_address: postBody.email_address,
        name: postBody.name,
        phone: postBody.phone,
        message: postBody.message,
        date: postBody.date,
    });

    const postData = JSON.stringify({
        'personalizations': [{
            'to': [{'email': config.sendgrid_to_email, 'name': config.sendgrid_to_name}],
            'subject': emailSubject
        }],
        'from': {'email': config.sendgrid_from_email, 'name': config.sendgrid_from_name},
        'content': [{'type': 'text/html', 'value': emailHtml}]
    });

    const postOptions = {
        host: 'api.sendgrid.com',
        port: '443',
        path: '/v3/mail/send',
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${apiKey}`
        }
    };

    const postReq = https.request(postOptions, (res) => {
        // res.setEncoding('utf-8');

        let result = '';

        res.on('data', (chunk) => {
            result += chunk;
        });
        res.on('error', (err) => {
            console.log('Error in sendgrid response: ', err);
        });
    });

    postReq.on('error', (err) => {
        console.log('Error sending request to SendGrid: ', err);
    });

    // Send POST request to SendGrid.
    postReq.write(postData);
    postReq.end();

    lambdaCallback(null);
};

// Handler function called by AWS Lambda.
exports.handler = (event, context, callback) => {
    let config;

    try {
        config = loadConfig();
    } catch (e) {
        debugAndCallbackNull(e.message, e, callback);
        return;
    }

    // Validate event payload.
    //noinspection JSUnresolvedVariable
    if (event === undefined ||
        event.Records === undefined ||
        event.Records[0] === undefined ||
        event.Records[0].Sns === undefined ||
        event.Records[0].Sns.Message === undefined) {

        debugAndCallbackNull('Invalid event payload. Now dumping event object', event, callback);
        return;
    }

    // Message property exists as a JSON encoded string.
    //noinspection JSUnresolvedVariable
    const message = JSON.parse(event.Records[0].Sns.Message);
    const messageDataType = typeof message;

    if (messageDataType !== 'object') {
        debugAndCallbackNull(
            `Invalid Sns.Message payload, expected an object, got '${messageDataType}'. Now dumping message variable.`,
            message,
            callback);

        return;
    }

    const messageObjectKeys = Object.keys(message);

    if (messageObjectKeys.length < 1) {
        debugAndCallbackNull(
            'Invalid Sns.Message payload, no keys in the object. Now dumping message object (should be empty).',
            message,
            callback);

        return;
    }

    const messageFirstKey = messageObjectKeys[0];

    // Cloudwatch Alarm.
    // Validate message payload.
    //noinspection JSUnresolvedVariable
    if (message.AlarmName === undefined || message.AlarmDescription === undefined ||
        message.NewStateValue === undefined || message.NewStateReason === undefined) {

        debugAndCallbackNull('Invalid Sns.Message payload. Now dumping message object.', message, callback);
        return;
    }

    //noinspection JSUnresolvedVariable
    callSendgrid(postBody, config, callback);
};
