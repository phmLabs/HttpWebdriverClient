chrome.webRequest.onBeforeRequest.addListener(
    function () {
        return {redirectUrl: 'https://www.example.com'};
    },
    {
        urls: [
            "*://*.google-analytics.com/collect*",
            "*://*.google-analytics.com/r/collect*",
            "*://*.google-analytics.com/__utm.gif*",
            "*://*.google-analytics.com/r/__utm.gif*",
            "*://stats.g.doubleclick.net/collect*",
            "*://stats.g.doubleclick.net/r/collect*",
            "https://*.webtrekk.net/*/wt*"
        ]
    },
    ["blocking"]
);
