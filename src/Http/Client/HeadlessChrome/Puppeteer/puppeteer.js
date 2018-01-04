"use strict";

const puppeteer = require("puppeteer");
const fs = require("fs");
const path = require("path");
const {URL} = require('url');

const filterFile = path.resolve(__dirname, 'filter.yml');

function exitError(msg) {
    let errorObj = {};
    errorObj.type = 'error';
    errorObj.message = msg;
    console.log(JSON.stringify(errorObj));
    process.exit(0);
}

function exitSuccess(result) {
    result.status = "success";
    console.log(JSON.stringify(result, null, 2));
    //  process.exit(0);
}


function exitTimeout(result) {
    result.status = "timeout";
    console.log(JSON.stringify(result, null, 2));
    process.exit(0);
}

async function collectData(browser, url) {
    return new Promise(async (resolve, reject) => {
        const page = await browser.newPage();

        let firstResponse = true;
        let firstNetwork = true;

        /**
         * Exit on error
         */
        page.on("error", async function (err) {
            exitError(err.msg);
        });

        /**
         * Log all page errors to result file
         */
        page.on("pageerror", async function (err) {
            if (result.js_errors.indexOf(err.message) === -1) {
                result.js_errors.push(err.message);
            }
        });

        page.on('request', request => {
            const ts = new Date().valueOf();
            let headers = request.headers;

            result.requests[request.url] = {};
            result.requests[request.url].time_start = ts;

            result.request_total++;

            // filter special urls like google analytics collect
            result.requests[request.url].abort = false;
            filteredUrls.forEach(regex => {
                if (request.url.match(new RegExp(regex))) {
                    result.requests[request.url].abort = true;
                }
            });

            // only set cookies, if the domain of the request is the same domain of the main request
            let originDomain = request.url.split('/');
            if (originDomain[2] === domain && cookieString !== "") {
                headers['cookie'] = cookieString;
            }
            result.requests[request.url].request_headers = request.headers;

            result.requests[request.url].method = request.method;
            if (request.method === 'POST') {
                result.requests[request.url].postdata = request.postData;
            }

            if (result.requests[request.url].abort) {
                request.abort();
            } else {
                request.continue({"headers": headers});
            }
        });

        page.on('response', response => {

            // store the response content in case a timeout occurs
            if (firstResponse) {
                if (parseInt(response.status) !== 301 && parseInt(response.status) !== 302) {
                    firstResponse = false;

                    if (response.headers['content-type']) {
                        result.contentType = response.headers['content-type'];
                    }

                    response.buffer().then(buffer => {
                        result.bodyHTML += buffer.toString('utf-8');
                    }).catch(function (error) {
                        // console.error(error);
                    });
                }
            }

            result.requests[response.url].time_tfb = new Date().valueOf();
            result.requests[response.url].http_status = response.status;
            result.requests[response.url].type = response.request().resourceType;

            result.requests[response.url].response_headers = response.headers;

            if (response.headers['content-length']) {
                result.requests[response.url].size = response.headers['content-length'];
                result.pageSize += parseInt(response.headers['content-length']);
            } else {
                response.buffer().then(buffer => {
                    result.requests[response.url].size = buffer.length;
                    result.pageSize += buffer.length;
                }).catch(function (error) {
                    // console.log(error);
                });
            }
        });

        page.on('requestfinished', request => {
            result.requests[request.url].time_finished = new Date().valueOf();
            result.requests[request.url].success = true;
            result.request_success++;
        });

        page.on('requestfailed', request => {
            result.requests[request.url].success = false;
            result.request_failed++;
        });

        page.on('load', async () => {
            result.timing.load = new Date().valueOf();

            // store cookies from all frames
            let frames = page.frames();
            frames.forEach(async frame => {
                let url = frame.url();
                let cookies = await page.cookies(url);

                let uri = new URL(url);
                if (uri.hostname) {
                    let domain = uri.protocol + '//' + uri.hostname;
                    result.cookies[domain] = [];
                    cookies.forEach(cookie => {
                        result.cookies[domain].push(cookie);
                    });
                }
            });
        });

        // see https://github.com/GoogleChrome/puppeteer/issues/1274
        page._client.on('Network.dataReceived', async event => {
            const request = await page._networkManager._requestIdToRequest.get(event.requestId);
            if (firstNetwork) {
                firstNetwork = false;
                result.timing.data_received = new Date().valueOf();
            }
        });

        result.timing.start = new Date().valueOf();

        await page.setRequestInterception(true);
        await page.setUserAgent(userAgent);
        await page.setViewport(viewport);

        await page.goto(url, {'timeout': pageTimeout, 'waitUntil': 'load'}).catch(function (err) {
            exitError(err.message);
        });

        await page.waitFor(parseInt(timeout * 0.1));

        if (result.contentType.indexOf('xml') === -1 && result.contentType.indexOf('json') === -1) {
            result.bodyDOM = await page.content();
        }

        result.timing.navigation = await page.evaluate(() => {
            const result = {};
            for (const key of Object.keys(window.performance.timing.__proto__))
                result[key] = window.performance.timing[key];
            return result;
        });

        let screenshotFile = screenshotDir + Math.round(Math.random() * 1000000000) + '.png';
        await page.screenshot({path: screenshotFile});
        result.screenshot = screenshotFile;

        resolve(result);
    })
}

async function call(url, timeout) {
    let browser;

    setTimeout(function () {
        exitTimeout(result);
    }, timeout);

    try {
        (async () => {
            browser = await puppeteer.launch({
                'headless': true,
                "args": ['--no-sandbox', '--disable-setuid-sandbox'],
                'ignoreHTTPSErrors': true
            });
            await collectData(browser, url);
            await browser.close();
            exitSuccess(result);
            process.exit(0);
        })();
    }
    catch (err) {

        exitError(err.message);

        if (browser) {
            await browser.close();
            process.exit(1);
        }
    }
}

const args = process.argv.slice(2);

const url = args[0];
const timeout = parseInt(args[1] || 29000);
const cookieString = args[2] || "";

const pageTimeout = parseInt(timeout) + 5000;

const urlArray = url.split("/");
const domain = urlArray[2];

const userAgent = args[3];
const viewport = JSON.parse(args[4]);

const screenshotDir = '/tmp/';

const filteredUrls = fs.readFileSync(filterFile).toString('utf-8').split("\n");

let result = {};
result.url = url;
result.pageSize = 0;
result.request_total = 0;
result.request_success = 0;
result.request_failed = 0;
result.requests = {};
result.js_errors = [];
result.bodyHTML = '';
result.bodyDOM = '';
result.contentType = '';
result.screenshot = '';
result.cookies = {};
result.timing = {};

call(url, timeout, cookieString);
