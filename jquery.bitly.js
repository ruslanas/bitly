/*
Author: Ruslanas Balčiūnas
http://bilty.googlecode.com
*/

function bitlyDataHandler() {
    // .. void
}

bitlyDataHandler.prototype.proccess = function(data) {
    if( data.statusCode == 'OK') {
        this.onSuccess( data.results);
    } else {
        this.onError( data.errorCode, data.errorMessage);
    }
    return true;
};

bitlyDataHandler.prototype.onSuccess = function(data) {
    try {
        console.info(data);
    } catch(e) {
        // .. ignore
    }
};

bitlyDataHandler.prototype.onError = function(code, message) {
    try {
        console.info('Bitly error: ' + code + '\n' + message);
    } catch(e) {
        // .. ignore
    }
};

jQuery.fn.bitly = function(action, func, options) {
    var opts = jQuery.extend({}, jQuery.fn.bitly.defaults, options);
    var dh = new bitlyDataHandler();
    if( typeof(func) == 'function') {
        dh.onSuccess = func;
    }
    var urls = new Array();
    this.each( function() {
        var elm = jQuery(this);
        var message = elm.val();
        if( !message) {
            message = elm.attr('href');
        }
        urls.push(message);
    });
    var xhr = jQuery.post( opts.url, {
        'action' : action,
        'url' : urls.join(',')
    }, function(data) {
        return dh.proccess(data);
    }, 'json');
    return xhr;
};

jQuery.fn.bitly.defaults = {
    url: 'bitly.php'
};

jQuery.fn.shortenUrl = function(func) {
    return this.each( function(){
        var elm = jQuery(this);
        var long = elm.val();
        
        // quick and dirty workaround
        // handled on server side
        if( !long) {
            long = ' ';
        }
        elm.bitly('shorten', function(data) {
            var re = new RegExp("http://[^( |$|\\]|\")]+", 'g');
            var urls = elm.val().match(re);
            if(!urls) {
                urls = new Array();
            }
            for(var i=0;i<urls.length;i++) {
                var url = urls[i];
                try {
                    var shortUrl = data[url].shortUrl;
                    elm.val( elm.val().replace(url, shortUrl));
                } catch(e) {
                    // must be bitly URL, ignore
                }
            }
            if( typeof(func) == 'function') {
                func(data);
            }
        });
    });
};

jQuery.fn.addPreview = function(func, options) {
    var opts = jQuery.extend({}, jQuery.fn.addPreview.defaults, options);
    var xOffset = opts.xOffset;
    var yOffset = opts.yOffset;
    var xhr;
    jQuery(this).hover( function() {
        $('body').append('<div id="preview"/>');
        xhr = jQuery(this).bitly('info', func);
    }, function() {
        xhr.abort();
        jQuery('#preview').fadeOut().remove();
    })
    .mousemove( function(e) {
        var left = e.pageX + xOffset;
        var top = e.pageY + yOffset;
        jQuery("#preview").css("top", top + "px").css("left", left + "px");
    });
};

jQuery.fn.addPreview.defaults = {
    'xOffset': 10,
    'yOffset': 10
};

