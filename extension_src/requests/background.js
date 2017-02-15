chrome.webRequest.onBeforeRequest.addListener(
    function (info) {
        chrome.tabs.executeScript(
            info.tabId,
            {"code": 'document.body.insertAdjacentHTML( \'afterbegin\', \'<div class="request">' + info.url + '</div>\');'}
        );
    },
    {urls: ['<all_urls>']},
    ["blocking"]
);