chrome.webRequest.onHeadersReceived.addListener(
    function (info) {
        if (info.type == 'main_frame') {
            headerString = btoa(JSON.stringify(info.responseHeaders));

            chrome.cookies.set({
                "name": "__leankoala_headers",
                "url": info.url,
                "value": headerString
            });
        }
    },
    {urls: ['<all_urls>']},
    ["blocking", "responseHeaders"]
);
