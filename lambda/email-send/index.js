'use strict';

const https = require('https');
const util = require('util');
const path = require('path');
const fs = require('fs');
const mustache = require('mustache');

const emailSubject = 'Bricebentler.com Email Contact Form Entry';

// Helper functions.
const debugLog = (logVariable, logMessage = null) => {
    if (logMessage !== null) {
        console.log(logMessage);
    }

    console.log(util.inspect(logVariable, {showHidden: true, depth: null}));
};

const loadConfig = () => {
    const configFile = path.join(__dirname, '/etc/config.json');

    if (!fs.existsSync(configFile)) {
        throw Error(`Missing config file at ${configFile}`);
    }

    return JSON.parse(fs.readFileSync(configFile, 'utf8'));
};

const callSendgrid = (templateData, config, callback) => {

    const apiKey = config.sendgrid_api_key;
    const emailTemplate = fs.readFileSync(path.join(__dirname, '/views/email.html'), 'utf8');
    // TODO : Confirm the templateData fields work.
    const emailHtml = mustache.render(emailTemplate, {
        email_address: templateData.email,
        name: templateData.name,
        phone: templateData.phone,
        message: templateData.message,
        date: new Date().toString(),
        ip_address: templateData.ip_address,
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

    callback(null, {
        statusCode: 200,
        headers: { 'Access-Control-Allow-Origin': '*' },
        body: JSON.stringify({status: 'success'}),
    }); // TODO : Consolidate.
};

// Handler function called by AWS Lambda.
exports.handler = async (event, context, callback) => {
    let config;

    // TODO : Switch from config file to ENV vars.
    try {
        config = loadConfig();
    } catch (e) {
        debugLog(e);
        callback(null, {
            statusCode: 500,
            headers: { 'Access-Control-Allow-Origin': '*' },
            body: JSON.stringify({status: 'error', message: 'Unable to send email due to missing/invalid config.'}),
        }); // TODO : Consolidate.
    }

    let configValidationErrors = [];
    ['sendgrid_api_key', 'sendgrid_to_email', 'sendgrid_to_name', 'sendgrid_from_email',
        'sendgrid_from_name'].forEach((item) => {
        if (config[item] === undefined) {
            configValidationErrors.push(`Missing ${item} in config file`);
        }
    });

    if (configValidationErrors.length > 0) {
        debugLog(configValidationErrors, 'Missing the following config values:');
        callback(null, {
            statusCode: 500,
            headers: { 'Access-Control-Allow-Origin': '*' },
            body: JSON.stringify({status: 'error', message: 'Unable to send email due to missing config values.'}),
        }); // TODO : Consolidate.
    }

    // Validate event payload.
    if (event === undefined || event.body === undefined) {
        debugLog(event, 'Invalid event payload.');
        callback(null, {
            statusCode: 500,
            headers: { 'Access-Control-Allow-Origin': '*' },
            body: JSON.stringify({status: 'error', message: 'Failed to send email due to invalid event object.'}),
        }); // TODO : Consolidate.
    }

    const requestBody = JSON.parse(event.body);

    // Validate request payload.
    let validationErrors = [];
    ['name', 'phone', 'email', 'message'].forEach((item) => {
        if (requestBody[item] === undefined) {
            validationErrors.push(`Missing ${item} in request body`);
        }
    });

    if (validationErrors.length > 0) {
        debugLog(requestBody, 'Invalid request body.');
        callback(null, {
            statusCode: 400,
            headers: { 'Access-Control-Allow-Origin': '*' },
            body: JSON.stringify({status: 'fail', data: {errors: validationErrors}}),
        }); // TODO : Consolidate.
    }

    let sourceIp;
    if (event.requestContext !== undefined &&
        event.requestContext.identity !== undefined &&
        event.requestContext.identity.sourceIp !== undefined) {
        sourceIp = event.requestContext.identity.sourceIp;
    }

    try {
        callSendgrid({
            name: requestBody.name,
            phone: requestBody.phone,
            email: requestBody.email,
            message: requestBody.message,
            ip_address: sourceIp
        }, config, callback);
    } catch (e) {
        debugLog(e);
        callback(null, {
            statusCode: 500,
            headers: { 'Access-Control-Allow-Origin': '*' },
            body: JSON.stringify({status: 'error', message: 'Failed to send email.'}),
        }); // TODO : Consolidate.
    }
};
