$(function () {
    if ($(".top.menu .item.back").length > 0
            && history.length < 2) {
        $(".top.menu .item.back").hide();
        $(".top.menu .item.home").show();
    }
});