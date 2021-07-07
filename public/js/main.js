const IS_COOKIE_CONSENTED = "isCookieConsented";

$(document).ready(function () {
    if (!getCookie(IS_COOKIE_CONSENTED)) {
        $(".cookie-baner").removeClass("disabled");
        $(".overlay").addClass("active");
        $("body").css({ "overflow-y": "hidden" });
    }

    $(".overlay").on("click", function () {
        $("#sidebar").removeClass("active");

        if (!getCookie(IS_COOKIE_CONSENTED)) {
            return;
        }

        $(".overlay").removeClass("active");

        $("body").css({ "overflow-y": "visible" });
    });

    $("#sidebarCollapse").on("click", function () {
        $("#sidebar").addClass("active");
        $(".overlay").addClass("active");

        $("body").css({ "overflow-y": "hidden" });

        $(".collapse.in").toggleClass("in");
        $("a[aria-expanded=true]").attr("aria-expanded", "false");
    });

    $("#cookieConsentBtn").on("click", function () {
        document.cookie = "isCookieConsented=true;";

        $(".overlay").removeClass("active");
        $("body").css({ "overflow-y": "visible" });
        $(".cookie-baner").addClass("disabled");
    });
});

function getCookie(name) {
    const re = new RegExp(name + "=([^;]+)");
    const value = re.exec(document.cookie);

    return value != null ? unescape(value[1]) : null;
}
