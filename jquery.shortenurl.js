/*
Author: Ruslanas Balčiūnas
URL: http://code.ruslanas.com
jQuery plugin for url shortening uses bit.ly API
*/

jQuery.fn.shorten = function(func) {
    return this.each( function() {
        var elm = jQuery(this);
        jQuery.post('shorten.php', {
            'action' : 'shorten',
            'url' : elm.attr('href')
        }, func, 'json');
    });
}

jQuery.fn.stats = function(func) {
    return this.each( function() {
        var elm = jQuery(this);
        jQuery.post('shorten.php', {
            'action' : 'stats',
            'url' : elm.attr('href')
        }, func, 'json');
    });
}

jQuery.fn.info = function(func) {
    return this.each( function() {
        var elm = jQuery(this);
        jQuery.post('shorten.php', {
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
        jQuery.post('shorten.php', parameters, function(data, status) {
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
    preview.fadeIn();
}

jQuery.fn.addPreview = function() {
    var xOffset = 5;
    var yOffset = 5;
    jQuery(this).hover( function() {
        $('body').append('<div id="preview"/>');
        var elm = this;
        var parameters = {'url' : this.href, 'action' : 'info'};
        jQuery.post('shorten.php', parameters, infoHandler, 'json');
    }, function() {
        jQuery('#preview').fadeOut().remove();
    });
    
    jQuery(this).mousemove( function(e) {
        var left = e.pageX + xOffset;
        var top = e.pageY + yOffset;
        jQuery("#preview").css("top", top + "px").css("left", left + "px");
    });
}
