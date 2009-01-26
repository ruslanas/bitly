/*
Author: Ruslanas Balčiūnas
URL: http://code.ruslanas.com
jQuery plugin for url shortening uses bit.ly API
*/

function dataHandler() {
}

dataHandler.prototype.proccess = function(data) {
    if( data.statusCode == 'OK') {
        this.onSucc( data.results);
    } else {
        this.onError( data.errorMessage);
    }
    return true;
}

dataHandler.prototype.onError = function(message) {
    alert(message);
}

jQuery.fn.bitly = function(action, func) {
    var dh = new dataHandler();
    dh.onSucc = func;
    var urls = new Array();
    this.each( function() {
        var elm = jQuery(this);
        urls.push( elm.attr('href').split('/').reverse().shift());
    });
    jQuery.post('bitly.php', {
        'action' : action,
        'url' : urls.join(',')
    }, function(data) { return dh.proccess(data);}, 'json');
}

jQuery.fn.shorten = function(func) {
    return this.each( function() {
        var elm = jQuery(this);
        jQuery.post('bitly.php', {
            'action' : 'shorten',
            'url' : elm.attr('href')
        }, func, 'json');
    });
}

jQuery.fn.stats = function(func) {
    return this.each( function() {
        var elm = jQuery(this);
        jQuery.post('bitly.php', {
            'action' : 'stats',
            'url' : elm.attr('href')
        }, func, 'json');
    });
}

jQuery.fn.info = function(func) {
    return this.each( function() {
        var elm = jQuery(this);
        jQuery.post('bitly.php', {
            'action' : 'info',
            'url' : elm.attr('href')
        }, func, 'json');
    });
}

jQuery.fn.shortenUrl = function() {
    return this.each(function(){
        var elm = jQuery(this);
        var long = elm.val();
        if( !long) {
            return false;
        }
        var parameters = {'action' : 'shorten', 'url' : long};
        jQuery.post('bitly.php', parameters, function(data, status) {
                var dat = data.results;
                for(var key in dat) {
                    elm.val( elm.val().replace(key, dat[key].shortUrl));
                }
            }, 'json')
    });
}

function infoHandler(data) {
    jQuery('#preview *').remove();
    var preview = jQuery('#preview');
    for(var key in data.results) {
        var d = data.results[key];
        var thumb = d.thumbnail.medium;
        preview.append('<div>' + d.htmlTitle + '</div>');
        if(thumb) {
            preview.append('<img src="' + thumb + '"/>');
        }
        var longUrl = d.longUrl.replace('http://', '');
        if( longUrl.length > 25) {
            longUrl = longUrl.substring(0, 25) + '&raquo;';
        }
        preview.append('<div>' + longUrl + '</div>');
    }
}

jQuery.fn.addPreview = function() {
    var xOffset = 5;
    var yOffset = 5;
    jQuery(this).hover( function() {
        $('body').append('<div id="preview"/>');
        var elm = this;
        var parameters = {'url' : this.href, 'action' : 'info'};
        jQuery.post('bitly.php', parameters, infoHandler, 'json');
        jQuery('#preview').fadeIn();
    }, function() {
        jQuery('#preview').fadeOut().remove();
    });
    
    jQuery(this).mousemove( function(e) {
        var left = e.pageX + xOffset;
        var top = e.pageY + yOffset;
        jQuery("#preview").css("top", top + "px").css("left", left + "px");
    });
}
