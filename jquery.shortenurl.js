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
        jQuery.post('shorten.php', {
            'action' : 'shorten',
            'url' : long
        }, function(data, status) {
                var dat = data.results;
                for(var key in dat) {
                    elm.val( elm.val().replace(key, dat[key].shortUrl));
                }
            }, 'json')
    });
}
jQuery.fn.addPreview = function() {
    xOffset = 5;
    yOffset = -5;
    $("body").append('<div id="preview"><div id="htmlTitle"/><img src=""/><div id="long"/></div>');
    jQuery(this).hover( function() {
        var elm = this;
        jQuery.post('shorten.php', {
            'url' : this.href,
            'action' : 'info'
        },
            function(data) {
                for(var key in data.results) {
                    var d = data.results[key];
                    var thumb = d.thumbnail.medium;
                    if(!thumb) {
                        thumb = 'no_image.png';
                    }
                    jQuery('img')[0].src = thumb;
                    jQuery('#long').html(d.longUrl.substring(0, 40) + '...');
                    jQuery('#htmlTitle').html(d.htmlTitle);
                }
            }, 'json');
        jQuery('#preview').fadeIn();
    }, function() {
        jQuery('#preview').fadeOut();
    });
    
    jQuery(this).mousemove( function(e) {
        jQuery("#preview").css("top",(e.pageY - yOffset) + "px")
            .css("left",(e.pageX + xOffset) + "px");
    });
}
