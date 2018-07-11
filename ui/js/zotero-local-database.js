$(function () {
    if ($(".top.menu .item.back").length > 0
            && history.length < 2) {
        $(".top.menu .item.back").hide();
        $(".top.menu .item.home").show();
    }
    
    $('.attachments.list a.item').click(function () {
        $(this).addClass("clicked");
    });
    
    $('.ui.dropdown').dropdown();
});

item_open_all = function () {
    if (window.confirm("You will open many windows. Are you sure?")) {
        $('.attachments.list a').each(function (_i, _ele) {
            setTimeout(function () {
                window.open(_ele.href);
            }, _i * 5000)
        });
    }
};