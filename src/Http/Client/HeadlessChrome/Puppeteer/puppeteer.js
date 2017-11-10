"use strict";

const puppeteer = require("puppeteer");

async function screenshot(browser, url) {
    return new Promise(async (resolve, reject) => {
        const page = await browser.newPage();
        await page.setRequestInterceptionEnabled(true);

        const resultFile = 'result.json';

        const viewport = {
            "width": 1000,
            "height": 800,
            "scale": 1,
            "isMobile": true,
            "hasTouch": false,
            "isLandscape": false
        };


        let result = {};
        result.url = url;
        result.pageSize = 0;
        result.request_total = 0;
        result.request_success = 0;
        result.request_failed = 0;
        result.requests = {};


        page.on("pageerror", async function (err) {
            console.log("ERROR with URL: " + err);
            return;
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
            //process.stdout.write('.');

            result.requests[response.url].time_tfb = ts;
            result.requests[response.url].http_status = response.status;
            result.requests[response.url].type = response.request().resourceType;

            result.requests[response.url].response_headers = response.headers;

            if(response.headers['content-length']) {
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


        await page.setViewport(viewport);
        await page.goto(url, {waitUntil: 'networkidle', 'networkIdleTimeout': 1000});

        result.bodyHTML = await page.content();

        //console.log(JSON.stringify(result,null,2));

        resolve(result);
    })
}


async function call() {
    let browser;

    setTimeout(function () {
        console.log('Timeout!!!!');
        browser.close();
        process.exit(1);
    }, 30000);

    try {
        (async () => {
            browser = await puppeteer.launch({'headless': false, "args": ['--no-sandbox', '--disable-setuid-sandbox']});
            let result = await screenshot(browser, "https://www.wunderweib.de");
            console.log(JSON.stringify(result,null,2));

            await browser.close();
            process.exit(0);
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

call();
