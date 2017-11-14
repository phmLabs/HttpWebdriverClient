"use strict";

const puppeteer = require("puppeteer");
const fs = require("fs");
const path = require("path");

const filterFile = path.resolve(__dirname, 'filter.yml');

//var text = fs.readFileSync("./mytext.txt").toString('utf-8');

async function collectData(browser, url) {
    return new Promise(async (resolve, reject) => {
        const page = await browser.newPage();
        await page.setRequestInterceptionEnabled(true);

        let result = {};
        result.url = url;
        result.pageSize = 0;
        result.request_total = 0;
        result.request_success = 0;
        result.request_failed = 0;
        result.requests = {};
        result.js_errors = [];

        page.on("error", async function (err) {
            console.log("ERROR with URL: " + err);
            return;
        });

        page.on("pageerror", async function (err) {
            result.js_errors.push(err.message);
            return;
        });

        // filter special urls like google analytics collect
        page.on('request', request => {
            filteredUrls.forEach((regex) => {
                if (request.url.match(new RegExp(regex))) {
                    request.abort();
                }
            });
        });

        // add cookies
        page.on('request', req => {
            if (req.url.startsWith(domain)) {
                let headers = req.headers;
                headers['referer'] = 'http://www.example.com/';
                headers['cookie'] = 'somekey=somevalue';
                req.continue({
                    headers: headers
                });
            }
        });

        page.on('request', request => {
            let ts = new Date().valueOf();
            result.requests[request.url] = {};
            result.requests[request.url].method = request.method;
            if (request.method === 'POST') {
                result.requests[request.url].postdata = request.postData;
            }
            result.requests[request.url].request_headers = request.headers;
            result.requests[request.url].time_start = ts;

            result.request_total++;
            //process.stdout.write('.');
            request.continue();
        });

        page.on('response', response => {
            let ts = new Date().valueOf();

            result.requests[response.url].time_tfb = ts;
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
                    console.log(error);
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

        // page.withExtraHeaders({'hallo': 'sebastian'});

        // await page.setViewport(viewport);
        await page.goto(url, {waitUntil: 'networkidle', 'networkIdleTimeout': 1000}).catch(function (err) {
            let errorObj = {};
            errorObj.type = 'ERROR';
            errorObj.message = err.message;
            console.log(JSON.stringify(errorObj));
            process.exit(0);
        });
        result.bodyHTML = await page.content();
        resolve(result);
    })
}

async function call(url, timeout, cookieString) {
    let browser;

    setTimeout(function () {
        console.log('TIMEOUT');
        browser.close();
        process.exit(1);
    }, timeout);

    try {
        (async () => {
            browser = await puppeteer.launch({'headless': false, "args": ['--no-sandbox', '--disable-setuid-sandbox']});
            let result = await collectData(browser, url);

            // console.log(JSON.stringify(result, null, 2));

            await browser.close();
            // process.exit(0);
        })();
    }
    catch (e) {
        console.log("error occured");
        if (browser) {
            await browser.close();
            process.exit(1);
        }
    }
}

let args = process.argv.slice(2);

let url = args[0];
let timeout = args[1];
let cookieString = args[2];
let urlArray = url.split("/");
let domain = urlArray[0] + "//" + urlArray[2];

let filteredUrls = fs.readFileSync(filterFile).toString('utf-8').split("\n");

call(url, timeout, cookieString);
