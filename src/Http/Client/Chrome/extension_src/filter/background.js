chrome.webRequest.onBeforeRequest.addListener(
    function () {
        return {redirectUrl: 'https://www.example.com'};
        return {cancel: true};
    },
    {
        urls: [
            "*://*.google-analytics.com/collect*",
            "*://*.google-analytics.com/r/collect*",
            "*://*.google-analytics.com/__utm.gif*",
            "*://*.google-analytics.com/r/__utm.gif*"
        ]
    },
    ["blocking"]
);
