'use strict';

const expect = require('chai').expect;
const jsend = require('../../../lib/helpers/jsend');

describe('jsend', () => {
    describe('generateResponse', () => {
        it('should throw an exception due to an invalid status', () => {
            let exceptionThrown = false;
            try {
                jsend.generateResponse('invalid status');
            } catch (e) {
                exceptionThrown = true;
            }

            expect(exceptionThrown).to.equal(true);
        });

        it('should throw an exception due to a missing payload', () => {
            let exceptionThrown = false;
            try {
                jsend.generateResponse('error');
            } catch (e) {
                exceptionThrown = true;
            }

            expect(exceptionThrown).to.equal(true);
        });

        it('should return a valid response object for status success', () => {
            const results = jsend.generateResponse('success');

            expect(results.statusCode).to.equal(200);
            expect(results.headers['Access-Control-Allow-Origin']).to.equal('*');
            expect(JSON.parse(results.body).status).to.equal('success');
        });
    });
});
