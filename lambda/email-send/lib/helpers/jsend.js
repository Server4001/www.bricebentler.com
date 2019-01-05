'use strict';

const responseObject = (statusCode, body) => {
    return {
        statusCode: statusCode,
        headers: { 'Access-Control-Allow-Origin': '*' },
        body: JSON.stringify(body),
    };
};

module.exports = (function() {
    return {
        jsendStatuses: {
            success: {
                statusCode: 200,
                responseBody: () => {
                    return {status: 'success'};
                },
            },
            fail: {
                statusCode: 400,
                responseBody: (payload = null) => {
                    return {status: 'fail', data: payload};
                },
            },
            error: {
                statusCode: 500,
                responseBody: (payload = null) => {
                    return {status: 'error', message: payload};
                },
            },
        },

        generateResponse(status, payload = null) {
            const statusObject = this.jsendStatuses[status];

            if (statusObject === undefined) {
                throw Error(`Invalid status: "${status}". Must be one of success,fail,error.`);
            }
            if (payload === null && status !== 'success') {
                throw Error(`Payload is required for status "${status}".`);
            }

            return responseObject(statusObject.statusCode, statusObject.responseBody(payload))
        },
    };
})();
