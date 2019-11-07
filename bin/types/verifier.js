var resetUrl = function () {
    window.history.pushState({}, "", "/");
};

if (typeof window.whenQuiLoaded !== 'undefined') {
    window.whenQuiLoaded.then(resetUrl);
} else {
    document.addEvent('domready', resetUrl);
}