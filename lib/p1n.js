/**
 * dapkdapk
 */
// BUG
ko.utils.stringStartsWith = function (string, startsWith) {
    string = string || "";
    if (startsWith.length > string.length) return false;
    return string.substring(0, startsWith.length) === startsWith;
};

sjcl.random.startCollectors();

var clickGenerateUrl = function () {
    if (v.rawUrlString() == undefined || v.rawUrlString() == "" || !ko.utils.stringStartsWith(v.rawUrlString(), "http")) {
        showError("Please enter a valid url!");
    } else {
        sendData(v.rawUrlString());
    }
};

var showError = function (errorText) {
    v.showNewUrl(false);
    v.errorBox(true);
    v.urlString(false);
    v.errorText(errorText);
    v.statusBox(false);
};

var showErrorUrl = function (errorText) {
    v.errorBox(true);
    v.errorText(errorText);
    v.statusBox(false);
};

var showUrl = function (urlString, deleteUrl) {
    v.showNewUrl(true);
    v.errorBox(false);
    v.urlString(urlString);
    v.deleteUrl(deleteUrl);
};
var showStatus = function (statusText) {
    showBox(statusText, false);
};
var showBox = function (infoText, spin) {
    if (infoText != "") {
        v.statusBox(true);
        if (spin) {
            v.infoTextSpin(true);
        } else {
            v.infoTextSpin(false);
        }
        v.infoText(infoText);
    }
};

var sendData = function (rawUrlString) {
    if (!sjcl.random.isReady()) {
        showBox("Sending url (Please move your mouse for more entropy)...", true);
        sjcl.random.addEventListener("seeded", function () {
            send_data();
        });
        return;
    }
    showBox("Please wait...", true);
    var randomkey = sjcl.codec.base64.fromBits(sjcl.random.randomWords(8, 0), 0);
    var cipherdata = zeroCipher(randomkey, rawUrlString);
    var data_to_send = {
        data: cipherdata,
        // expire: $('select#pasteExpiration').val(),
    };

    $.post(scriptLocation(), data_to_send, "json")
        .error(function () {
            showError("Url could not be sent (server error or not responding).");
        })
        .success(function (data) {
            if (data.status == 0) {
                var url = scriptLocation() + "?" + data.id + "#" + randomkey;
                var deleteUrl = scriptLocation() + "?pasteid=" + data.id + "&deletetoken=" + data.deletetoken;
                showUrl(url, deleteUrl);
                v.statusBox(false);

                v.shortUrlButton(true);
                v.shortUrlString("");
                v.shortUrlSpan(false);
            } else if (data.status == 1) {
                showError("Could not create url: " + data.message);
            } else {
                showError("Could not create url.");
            }
        });
};

var openUrl = function (key, comments) {
    try {
        var cleartext = zeroDecipher(key, comments[0].data);
        window.open(cleartext, "_self");
        return false;
    } catch (err) {
        showError("Could not decrypt url data (Wrong key ?)");
        return;
    }
};

$(function () {
    if ($("div#statusmessage").text().length > 0) {
        showStatus($("div#statusmessage").text());
        return;
    }
    $("div#statusmessage").html("&nbsp;"); // Keep line height even if content
    // empty.
    if ($("div#cipherdata").text().length > 1) {
        v.showForm(false);
        if (window.location.hash.length == 0) {
            showError(
                "Cannot decrypt url: Decryption key missing in URL (Did you use a redirector or an URL shortener which strips part of the URL ?)"
            );
            return;
        }
        var messages = jQuery.parseJSON($("div#cipherdata").text());
        showBox("One moment please.. try to open url.", true);
        openUrl(pageKey(), messages);
    } else if ($("div#errormessage").text().length > 1) {
        showError($("div#errormessage").text());
    }
});

this.enterKeyboardCmd = function (data, event) {
    ko.computed(function () {
        if (data.rawUrlString() != undefined) {
            var str = data.rawUrlString();
            if (str.substring(0, 1) != "h" && str.substring(1, 2) != "t" && str.substring(2, 3) != "t") {
                v.rawUrlString("http://" + v.rawUrlString());
            }
        }
    }, this);
    return true;
};

var clickGetShortUrl = function () {
    showBox("Please wait...", true);
    var data_to_send = {
        shorturl: v.urlString(),
    };
    $.post(scriptLocation(), data_to_send, "json")
        .error(function () {
            showErrorUrl("Shorturl couldn't sent (server error or not responding).");
        })
        .success(function (data) {
            if (data.status == 0) {
                v.shortUrlButton(false);
                v.shortUrlString(data.shorturl);
                v.shortUrlSpan(true);
                v.statusBox(false);
                v.errorBox(false);
            } else if (data.status == 1) {
                showErrorUrl("Could not create short url: " + data.message);
            } else {
                showErrorUrl("Could not create short url.");
            }
        });
};

var v = {
    showNewUrl: ko.observable(false),
    deleteUrl: ko.observable(false),
    generateUrl: clickGenerateUrl,
    urlString: ko.observable(),
    shortUrlString: ko.observable(),
    rawUrlString: ko.observable(),
    errorText: ko.observable(),
    errorBox: ko.observable(false),
    statusBox: ko.observable(false),
    infoText: ko.observable(false),
    infoTextSpin: ko.observable(false),
    showForm: ko.observable(true),
    enterText: ko.observable(),
    getShortUrl: clickGetShortUrl,
    shortUrlButton: ko.observable(true),
    shortUrlSpan: ko.observable(false),
};

ko.applyBindings(v);
