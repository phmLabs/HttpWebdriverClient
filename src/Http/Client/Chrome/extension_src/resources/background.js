chrome.webRequest.onCompleted.addListener(
    function (info) {
        chrome.tabs.get(info.tabId, function (tab) {
            // console.log(info);
            // console.log(tab);
            // chrome.storage.local
            chrome.storage.local.set({ "phasersTo": "awesome" }, function(){
                //  Data's been saved boys and girls, go on home
            });
        });
    },
    {urls: ['<all_urls>']},
    ["responseHeaders"]
);

chrome.storage.local.set({
    "Name": "Your_variable",
    "Value": "Some Value"
}, function () {
    console.log("Storage Succesful");
});