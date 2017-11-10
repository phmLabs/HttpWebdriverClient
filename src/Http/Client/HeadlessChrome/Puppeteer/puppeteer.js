const puppeteer = require('puppeteer');
const fs = require('fs');

puppeteer.launch({'headless': false, "args": ['--no-sandbox', '--disable-setuid-sandbox']}).then(async browser => {

    const startUrl = 'http://www.bravo.de';

    const viewport = {
        "width": 1000,
        "height": 800,
        "scale": 1,
        "isMobile": true,
        "hasTouch": false,
        "isLandscape": false
    };

    const resultFile = 'result.json';

    const page = await browser.newPage();
    await page.setRequestInterceptionEnabled(true);

    let result = {};
    result.url = startUrl;
    result.pageSize = 0;
    result.request_total = 0;
    result.request_success = 0;
    result.request_failed = 0;
    result.requests = {};

    browser.version().then(function (browserVersion) {
        console.log(browserVersion);
    }).catch(function (error) {
        console.log(error)
    });

    page.on('request', request => {
        let ts = new Date().valueOf();
    result.requests[request.url] = {};
    result.requests[request.url].method = request.method;
    if (request.method === 'POST') {
        result.requests[request.url].postdata = request.postData;
    }
    result.requests[request.url].headers = JSON.stringify(request.headers);
    result.requests[request.url].time_start = ts;

    result.request_total++;
    request.continue();
});

    page.on('response', response => {
        let ts = new Date().valueOf();
    process.stdout.write('.');

    result.requests[response.url].time_tfb = ts;
    result.requests[response.url].http_status = response.status;
    result.requests[response.url].type = response.request().resourceType;

    response.buffer().then(buffer => {
        result.requests[response.url].size = buffer.length;
    result.pageSize += buffer.length;
}).catch(function (error) {
        console.log(error);
    });
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

    //
    //     response.text().then(function (text) {
    //         requests[response.url] = text.length;
    //     }).catch(function (error) {
    //         console.log(error.message);
    //     })


    await page.setViewport(viewport);
    await page.goto(startUrl, {waitUntil: 'networkidle', 'networkIdleTimeout': 15000});
    await page.screenshot({path: 'full.png', fullPage: true});

    await browser.close();

    console.log();
    fs.writeFile(resultFile, JSON.stringify(result, null, 2), function (err) {
        if (err) {
            return console.log(err);
        }

        console.log("Results saved in " + resultFile);
    });

}).catch(function (error) {
    console.error(error);
});