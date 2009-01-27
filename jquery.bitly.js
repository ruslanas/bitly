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
}

bitlyDataHandler.prototype.onSuccess = function(data) {
    try {
        console.info(data);
    } catch(e) {
        // .. ignore
    }
}

bitlyDataHandler.prototype.onError = function(code, message) {
    try {
        console.info('Bitly error: ' + code + '\n' + message);
    } catch(e) {
        // .. ignore
    }
}

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

    jQuery.post( opts.url, {
        'action' : action,
        'url' : urls.join(',')
    }, function(data) { return dh.proccess(data);}, 'json');
}

jQuery.fn.bitly.defaults = {
    url: 'bitly.php'
}

jQuery.fn.shortenUrl = function() {
    return this.each( function(){
        var elm = jQuery(this);
        var long = elm.val();
        if( !long) {
            return false;
        }
        elm.bitly('shorten', function(data) {
            for(var url in data) {
                elm.val( elm.val().replace(url, data[url].shortUrl));
            }
        });
    });
}

jQuery.fn.addPreview = function(func) {
    var xOffset = 5;
    var yOffset = 5;

    jQuery(this).hover( function() {
        var p = jQuery('body').append('<div id="preview"/>');
        var elm = this;
        jQuery(this).bitly('info', func);
        p.fadeIn();
    }, function() {
        jQuery('#preview').fadeOut().remove();
    });
    
    jQuery(this).mousemove( function(e) {
        var left = e.pageX + xOffset;
        var top = e.pageY + yOffset;
        jQuery("#preview").css("top", top + "px").css("left", left + "px");
    });
}
