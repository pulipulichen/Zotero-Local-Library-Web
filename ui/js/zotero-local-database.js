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
    
    adjust_attachment_link();
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

adjust_attachment_link = function () {
    $(".attachments .attachment").each(function (_key, _item) {
        console.log(_key);
        _item = $(_item);
        var _title = _item.attr("title");
        var _attachment_key = _item.attr("data-attachment-key");
        
        var _prefix = "https://drive.google.com/drive/u/0/search?q=type:folder%20";
        if (is_android()) {
            _prefix = "http://gdrive.search/";
        }
        var _href = _prefix + _attachment_key;
        
        _item.attr("href", _href); 
        
        /*
        if (_title.length > 20 || _title.split(" - ").length >= 2) {
            var _prefix = "https://drive.google.com/drive/u/0/search?q=";
            if (is_android()) {
                _prefix = "http://gdrive.search/";
            }
            var _href = _prefix + encodeURIComponent(_title);
            _item.attr("href", _href); 
        }
        */
    });
};

is_android = function () {
    var ua = navigator.userAgent.toLowerCase();
    return ua.indexOf("android") > -1; //&& ua.indexOf("mobile");
    //
    //if(isAndroid) {
      // Do something!
      // Redirect to Android-site?
      //window.location = 'http://android.davidwalsh.name';
    //}
};