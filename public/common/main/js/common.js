/*
 * OKZoom by OKFocus v1.2
 * http://okfoc.us // @okfocus
 * Copyright 2012 OKFocus
 * Licensed under the MIT License
**/

$(function($){

  // Identify browser based on useragent string
  var browser = (function( ua ) {
    ua = ua.toLowerCase();
    var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
      /(webkit)[ \/]([\w.]+)/.exec( ua ) ||
      /(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
      /(msie) ([\w.]+)/.exec( ua ) ||
      ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
      [];
    var matched = {
      browser: match[ 1 ] || "",
      version: match[ 2 ] || "0"
    };
    browser = {};
    if ( matched.browser ) {
        browser[ matched.browser ] = true;
        browser.version = matched.version;
    }
    // Chrome is Webkit, but Webkit is also Safari.
    if ( browser.chrome ) {
      browser.webkit = true;
    } else if ( browser.webkit ) {
      browser.safari = true;
    }
    if (window.$) $.browser = browser;
    return browser;
  })( navigator.userAgent );

  var is_iphone = (navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i))
  var is_ipad = (navigator.userAgent.match(/iPad/i))
  var is_android = (navigator.userAgent.match(/Android/i))
  var is_mobile = is_iphone || is_ipad || is_android
  var is_desktop = ! is_mobile;
  var transitionProp = browser.safari ? "WebkitTransition" : "transition";
  var transformProp = browser.safari ? "WebkitTransform" : "transform";
  var longTransformProp = browser.safari ? "-webkit-transform" : "transform";
  var transformOriginProp = browser.safari ? "WebkitTransformOrigin" : "transformOrigin";

  $.fn.okzoom = function(options){
    options = $.extend({}, $.fn.okzoom.defaults, options);

    return this.each(function(){
      var base = {};
      var el = this;
      base.options = options;
      base.$el = $(el);
      base.el = el;

      base.listener = document.createElement('div');
      base.$listener = $(base.listener).addClass('ok-listener').css({
        position: 'absolute',
        zIndex: 10000
      });
      $('body').append(base.$listener);

      var loupe = document.createElement("div");
      loupe.id = "ok-loupe";
      loupe.style.position = "absolute";
      loupe.style.backgroundRepeat = "no-repeat";
      loupe.style.pointerEvents = "none";
      loupe.style.opacity = 0;
      loupe.style.zIndex = 7879;
      $('body').append(loupe);
      base.loupe = loupe;

      base.$el.data("okzoom", base);

      base.options = options;
      
      if (is_mobile) {
        base.$el.bind('touchstart', (function(b) {
          return function(e) {
            console.log("TS", e)
            e.preventDefault()
            $.fn.okzoom.build(b, e.originalEvent.touches[0]);
          };
        }(base)));

        base.$el.bind('touchmove', (function(b) {
          return function(e) {
            console.log("TM")
            e.preventDefault()
            $.fn.okzoom.mousemove(b, e.originalEvent.touches[0]);
          };
        }(base)));

        base.$el.bind('touchend', (function(b) {
          return function(e) {
            console.log("TE")
            e.preventDefault()
            $.fn.okzoom.mouseout(b, e);
          };
        }(base)));
      }
      else {
        $(base.el).bind('mouseover', (function(b) {
          return function(e) { $.fn.okzoom.build(b, e); };
        }(base)));

        base.$listener.bind('mousemove', (function(b) {
          return function(e) { $.fn.okzoom.mousemove(b, e); };
        }(base)));

        base.$listener.bind('mouseout', (function(b) {
          return function(e) { $.fn.okzoom.mouseout(b, e); };
        }(base)));
      }

      base.options.height = base.options.height || base.options.width;

      base.image_from_data = base.$el.data("okimage");
      base.has_data_image = typeof base.image_from_data !== "undefined";
      base.timeout = null

      if (base.has_data_image) {
        base.img = new Image ();
        base.img.src = base.image_from_data;
      }

      base.msie = -1; // Return value assumes failure.
      if (navigator.appName == 'Microsoft Internet Explorer') {
        var ua = navigator.userAgent;
        var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
        if (re.exec(ua) != null)
          base.msie = parseFloat(RegExp.$1);
      }
    });
  };

  $.fn.okzoom.defaults = {
    "width": 150,
    "height": null,
    "scaleWidth": null,
    "round": true,
    "background": "#fff",
    "backgroundRepeat": "no-repeat",
    "shadow": "0 0 5px #000",
    "inset": 0,
    "border": 0,
    "transform": is_mobile ? ["scale(0)","scale(1)"] : null,
    "transformOrigin": is_mobile ? "50% 100%" : "50% 50%",
    "transitionTime": 200,
    "transitionTimingFunction": "cubic-bezier(0,0,0,1)",
  };

  $.fn.okzoom.build = function(base, e){
    if (! base.has_data_image) {
      base.img = base.el;
    }
    else if (base.image_from_data != base.$el.attr('data-okimage')) {
      // data() returns cached values, whereas attr() returns from the dom.
      base.image_from_data = base.$el.attr('data-okimage');

      $(base.img).remove();
      base.img = new Image();
      base.img.src = base.image_from_data;
    }

    if (base.msie > -1 && base.msie < 9.0 && !base.img.naturalized) {
      var naturalize = function(img) {
        img = img || this;
        var io = new Image();

        io.el = img;
        io.src = img.src;

        img.naturalWidth = io.width;
        img.naturalHeight = io.height;
        img.naturalized = true;
      };
      if (base.img.complete) naturalize(base.img);
      else return;
    }

    base.offset = base.$el.offset();
    base.width = base.$el.width();
    base.height = base.$el.height();
    base.$listener.css({
      display: 'block',
      width: base.$el.outerWidth(),
      height: base.$el.outerHeight(),
      top: base.$el.offset().top,
      left: base.$el.offset().left
    });

    if (base.options.scaleWidth) {
      base.naturalWidth = base.options.scaleWidth;
      base.naturalHeight = Math.round( base.img.naturalHeight * base.options.scaleWidth / base.img.naturalWidth );
    } else {
      base.naturalWidth = base.img.naturalWidth;
      base.naturalHeight = base.img.naturalHeight;
    }

    base.widthRatio = base.naturalWidth / base.width;
    base.heightRatio = base.naturalHeight / base.height;

    base.loupe.style.width = base.options.width + "px";
    base.loupe.style.height = base.options.height + "px";
    base.loupe.style.border = base.options.border;
    base.loupe.style.background = base.options.background + " url(" + base.img.src + ")";
    base.loupe.style.backgroundRepeat = base.options.backgroundRepeat;
    base.loupe.style.backgroundSize = base.options.scaleWidth ?
        base.naturalWidth + "px " + base.naturalHeight + "px" : "auto";
    base.loupe.style.borderRadius =
    base.loupe.style.MozBorderRadius =
    base.loupe.style.WebkitBorderRadius = base.options.round ? "50%" : 0;
    base.loupe.style.boxShadow = base.options.shadow;
    base.loupe.style.opacity = 0;
    if (base.options.transform) {
      base.loupe.style[transformProp] = base.options.transform[0]
      base.loupe.style[transformOriginProp] = base.options.transformOrigin
      base.loupe.style[transitionProp] = longTransformProp + " " + base.options.transitionTime
    }
    base.initialized = true;
    $.fn.okzoom.mousemove(base, e);
  };

  $.fn.okzoom.mousemove = function (base, e) {
    if (!base.initialized) return;
    var shimLeft = base.options.width / 2;
    var shimTop = base.options.height / 2;
    var offsetTop = is_mobile ? base.options.height : shimTop
    var pageX = typeof e.pageX !== 'undefined' ? e.pageX :
        (e.clientX + document.documentElement.scrollLeft);
    var pageY = typeof e.pageY !== 'undefined' ? e.pageY :
        (e.clientY + document.documentElement.scrollTop);
    var scaleLeft = -1 * Math.floor( (pageX - base.offset.left) * base.widthRatio - shimLeft );
    var scaleTop  = -1 * Math.floor( (pageY - base.offset.top) * base.heightRatio - shimTop );

    document.body.style.cursor = "none";
    // base.loupe.style.display = "block";
    base.loupe.style.left = pageX - shimLeft + "px";
    base.loupe.style.top = pageY - offsetTop + "px";
    base.loupe.style.backgroundPosition = scaleLeft + "px " + scaleTop + "px";
    base.loupe.style.opacity = 1;
    if (base.options.transform) {
      base.loupe.style[transformProp] = base.options.transform[1]
      base.loupe.style[transformProp] = base.options.transform[1]
      base.loupe.style[transitionProp] = longTransformProp + " " + base.options.transitionTime + "ms " + base.options.transitionTimingFunction
    }
    clearTimeout(base.timeout)
  };

  $.fn.okzoom.mouseout = function (base, e) {
    // base.loupe.style.display = "none";
    if (base.options.transform) {
      base.loupe.style[transformProp] = base.options.transform[0]
      base.timeout = setTimeout(function(){
        base.loupe.style.opacity = 0;
      }, base.options.transitionTime);
    }
    else {
      base.loupe.style.opacity = 0;
    }
    base.loupe.style.background = "none";
    base.listener.style.display = "none";
    document.body.style.cursor = "auto";
  };

});
/*------ Main Menu Toggle at responsive --------*/
    var responsive_toggle = $('.responsive-toggle');
    responsive_toggle.on('click', function (e) {
        $("body").toggleClass('off-canvas-body');
        responsive_toggle.toggleClass("fa-bars fa-close");
        e.preventDefault();
    });

    /*------ Custom Scroll Style --------*/
    var scroll_js = $('.scroll-js');
    var $window = $(window);
    if ($window.width() < 1200) {
        if (scroll_js.length > 0) {
            scroll_js.mCustomScrollbar({
                theme: "dark-2",
                scrollButtons: {
                    enable: false
                }
            });
        }
    }
//-------------------------------------------------------------------------
function getURLVar(key) {
  var value = [];

  var query = String(document.location).split('?');

  if (query[1]) {
    var part = query[1].split('&');

    for (i = 0; i < part.length; i++) {
      var data = part[i].split('=');

      if (data[0] && data[1]) {
        value[data[0]] = data[1];
      }
    }

    if (value[key]) {
      return value[key];
    } else {
      return '';
    }
  }
}

$(document).ready(function() {

  // sidebar-qrcode
  $('.sidebar-qrcode').hover(function() {
    $(this).find('.qrcode-box').stop().slideToggle(250);
  })

  // 新增模块 tab 切换
  $('.module-product-two').each(function() {
    $(this).find('.right-tab li:eq(0)').addClass('color');
    $(this).find(".tab").hide().first().show();

    $(this).find('.right-tab li').each(function(i) {
      $(this).on('click',function() {
        $(this).addClass('color').siblings('li').removeClass('color');
        $(this).parents('.module-product-two').find('.tab:eq(' + i + ')').fadeIn(250).siblings('.tab').hide();
       })
    })
  });

$(window).scroll(function () {

    /*--------- Sticky Header ---------*/
    var main_header = $('.main-header');
    if ($(this).scrollTop() > 5) {
        main_header.addClass('is-sticky');
//      alert("gondun");
    }
    else {
        main_header.removeClass('is-sticky');
//      alert("wugundong");
    }
		/*top*/
		if ($(this).scrollTop() > 100) {
      $('.scroll-top1').fadeIn();
    } else {
      $('.scroll-top1').fadeOut();
    }
    /*--------- Page to top ---------*/
    var to_top_mb = $('.to-top.mb');
    if ($(this).scrollTop() > 100) {
        to_top_mb.fadeIn();
//      alert("hhhhh");
    } else {
        to_top_mb.fadeOut();
    }

    var $window = $(window);
    if ($window.scrollTop() + $window.height() > $(document).height() - 200) {
        to_top_mb.fadeOut();
    }

});
  /*scroll-to-top animate
  $(window).scroll(function(){
    if ($(this).scrollTop() > 100) {
      $('.scroll-top1').fadeIn();
    } else {
      $('.scroll-top1').fadeOut();
    }
  });*/

  $('.scroll-top1').click(function(){
    $("html, body").animate({ scrollTop: 0 }, 600);
      return false;
  });

  //qty add/minus action on product detail page
//$('.qty-buttons .add-action').click(function(){
//  if( $(this).hasClass('add-up') ) {
//    $("[name=quantity]",'.quantity-input').val( parseInt($("[name=quantity]",'.quantity-input').val()) + 1 );
//  } else {
//    if( parseInt($("[name=quantity]",'.quantity-input').val())  > 1 ) {
//        $("input",'.quantity-input').val( parseInt($("[name=quantity]",'.quantity-input').val()) - 1 );
//    }
//  }
//});
//
//// Highlight any found errors
//$('.text-danger').each(function() {
//  var element = $(this).parent().parent();
//
//  if (element.hasClass('form-group')) {
//    element.addClass('has-error');
//  }
//});
//
//// Currency
//$('#currency .currency-select').on('click', function(e) {
//  e.preventDefault();
//
//  $('#currency input[name=\'code\']').attr('value', $(this).attr('name'));
//
//  $('#currency').submit();
//});
//
//// Language
//$('#language a').on('click', function(e) {
//  e.preventDefault();
//
//  $('#language input[name=\'code\']').attr('value', $(this).attr('href'));
//
//  $('#language').submit();
//});

  //top-link
    $("#top .dropdown").hover(function(){

      $(this).addClass("open");

     },function(){

      $(this).removeClass("open");

    });

  /* Search */
  $('#search input[name=\'search\']').parent().find('button').on('click', function() {
    url = $('base').attr('href') + 'index.php?route=product/search';

    var value = $('header input[name=\'search\']').val();

    if (value) {
      url += '&search=' + encodeURIComponent(value);
    }

    location = url;
  });

  $('#search input[name=\'search\']').on('keydown', function(e) {
    if (e.keyCode == 13) {
      $('header input[name=\'search\']').parent().find('button').trigger('click');
    }
  });

// Menu
  $('.main-menu-mobile').click(function() {
    if ($('.main-menu').hasClass('open')) {
      $('.main-menu').removeClass('open');
    } else {
      $('.main-menu').addClass('open');
    }
//  if ($('.vertical-menu-list').hasClass('open')) {
//    $('.vertical-menu-list').removeClass('open');
//  } else {
//    $('.vertical-menu-list').addClass('open');
//  }
  });

  $('.open-submenu').click(function() {
    $(this).siblings('.submenu').toggle('fast');
  });

  // Product List
  $('#list-view').click(function() {
    $('#content .product-grid > .clearfix').remove();

    $('#content .row > .product-grid').attr('class', 'product-layout product-list col-xs-12');

    localStorage.setItem('display', 'list');
  });
//// Product Grid
//$('#grid-view').click(function() {
//  // What a shame bootstrap does not take into account dynamically loaded columns
//  cols = $('#column-right, #column-left').length;
//
//  if (cols == 2) {
//    $('#content .product-list').attr('class', 'product-layout product-grid col-lg-6 col-md-6 col-sm-12 col-xs-12');
//  } else if (cols == 1) {
//    $('#content .product-list').attr('class', 'product-layout product-grid col-lg-4 col-md-4 col-sm-6 col-xs-12');
//  } else {
//    $('#content .product-list').attr('class', 'product-layout product-grid col-lg-3 col-md-3 col-sm-6 col-xs-12');
//  }
//
//  localStorage.setItem('display', 'grid');
//});

//if (localStorage.getItem('display') == 'list') {
//  $('#list-view').trigger('click');
//} else {
//  $('#grid-view').trigger('click');
//}

  // tooltips on hover
//$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});
//
//// Makes tooltips work on ajax generated content
//$(document).ajaxStop(function() {
//  $('[data-toggle=\'tooltip\']').tooltip({container: 'body'});
//});
});

//function addToCart(product_id) {
//event.preventDefault();
//return cart.add(product_id);
//}

//// Cart add remove functions
//var cart = {
//'add': function(product_id, quantity) {
//  $.ajax({
//    url: 'index.php?route=checkout/cart/add',
//    type: 'post',
//    data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
//    dataType: 'json',
//    beforeSend: function() {
//      $('#cart #cart-total').button('loading');
//    },
//    complete: function() {
//      $('#cart #cart-total').button('reset');
//    },
//    success: function(json) {
//      $('.alert, .text-danger').remove();
//
//      if (json['redirect']) {
//        location = json['redirect'];
//      }
//
//      if (json['success']) {
//        $('header').next().before('<div class="alert alert-success container"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
//
//        // Need to set timeout otherwise it wont update the total
//        setTimeout(function () {
//          $('#cart #cart-total').html(json['total']);
//        }, 100);
//
//        $('html, body').animate({ scrollTop: 0 }, 'slow');
//
//        $('#cart > ul').load('index.php?route=common/cart/info ul li');
//      }
//    },
//        error: function(xhr, ajaxOptions, thrownError) {
//            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
//        }
//  });
//},
//'update': function(key, quantity) {
//  $.ajax({
//    url: 'index.php?route=checkout/cart/edit',
//    type: 'post',
//    data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
//    dataType: 'json',
//    beforeSend: function() {
//      $('#cart #cart-total').button('loading');
//    },
//    complete: function() {
//      $('#cart #cart-total').button('reset');
//    },
//    success: function(json) {
//      // Need to set timeout otherwise it wont update the total
//      setTimeout(function () {
//        $('#cart #cart-total').html(json['total']);
//      }, 100);
//
//      if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
//        location = 'index.php?route=checkout/cart';
//      } else {
//        $('#cart > ul').load('index.php?route=common/cart/info ul li');
//      }
//    },
//        error: function(xhr, ajaxOptions, thrownError) {
//            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
//        }
//  });
//},
//'remove': function(key) {
//  $.ajax({
//    url: 'index.php?route=checkout/cart/remove',
//    type: 'post',
//    data: 'key=' + key,
//    dataType: 'json',
//    beforeSend: function() {
//      $('#cart #cart-total').button('loading');
//    },
//    complete: function() {
//      $('#cart #cart-total').button('reset');
//    },
//    success: function(json) {
//      // Need to set timeout otherwise it wont update the total
//      setTimeout(function () {
//        $('#cart #cart-total').html(json['total']);
//      }, 100);
//
//      if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
//        location = 'index.php?route=checkout/cart';
//      } else {
//        $('#cart > ul').load('index.php?route=common/cart/info ul li');
//      }
//    },
//        error: function(xhr, ajaxOptions, thrownError) {
//            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
//        }
//  });
//}
//}
//
//var voucher = {
//'add': function() {
//
//},
//'remove': function(key) {
//  $.ajax({
//    url: 'index.php?route=checkout/cart/remove',
//    type: 'post',
//    data: 'key=' + key,
//    dataType: 'json',
//    beforeSend: function() {
//      $('#cart #cart-total').button('loading');
//    },
//    complete: function() {
//      $('#cart #cart-total').button('reset');
//    },
//    success: function(json) {
//      // Need to set timeout otherwise it wont update the total
//      setTimeout(function () {
//        $('#cart #cart-total').html(json['total']);
//      }, 100);
//
//      if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
//        location = 'index.php?route=checkout/cart';
//      } else {
//        $('#cart > ul').load('index.php?route=common/cart/info ul li');
//      }
//    }
//  });
//}
//}
//
//var wishlist = {
//'add': function(product_id) {
//  $.ajax({
//    url: 'index.php?route=account/wishlist/add',
//    type: 'post',
//    data: 'product_id=' + product_id,
//    dataType: 'json',
//    success: function(json) {
//      $('.alert').remove();
//
//      if (json['success']) {
//        $('header').next().before('<div class="alert alert-success container"><i class="fa fa-check-circle"></i> ' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
//      }
//
//      if (json['info']) {
//        $('header').next().before('<div class="alert alert-info container"><i class="fa fa-info-circle"></i> ' + json['info'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
//      }
//
//      $('#wishlist-total span').html(json['total']);
//      $('#wishlist-total').attr('title', json['total']);
//
//      $('html, body').animate({ scrollTop: 0 }, 'slow');
//    }
//  });
//},
//'remove': function() {
//
//}
//}
//
//var compare = {
//'add': function(product_id) {
//  $.ajax({
//    url: 'index.php?route=product/compare/add',
//    type: 'post',
//    data: 'product_id=' + product_id,
//    dataType: 'json',
//    success: function(json) {
//      $('.alert').remove();
//
//      if (json['success']) {
//        $('header').next().before('<div class="alert alert-success container"><i class="fa fa-check-circle"></i> ' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
//
//        $('#compare-total').html(json['total']);
//
//        $('html, body').animate({ scrollTop: 0 }, 'slow');
//      }
//    }
//  });
//},
//'remove': function() {
//
//}
//}

/* Agree to Terms */
//$(document).delegate('.agree', 'click', function(e) {
//e.preventDefault();
//
//$('#modal-agree').remove();
//
//var element = this;
//
//$.ajax({
//  url: $(element).attr('href'),
//  type: 'get',
//  dataType: 'html',
//  success: function(data) {
//    html  = '<div id="modal-agree" class="modal">';
//    html += '  <div class="modal-dialog">';
//    html += '    <div class="modal-content">';
//    html += '      <div class="modal-header">';
//    html += '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
//    html += '        <h4 class="modal-title">' + $(element).text() + '</h4>';
//    html += '      </div>';
//    html += '      <div class="modal-body">' + data + '</div>';
//    html += '    </div';
//    html += '  </div>';
//    html += '</div>';
//
//    $('body').append(html);
//
//    $('#modal-agree').modal('show');
//  }
//});
//});
//
//// Autocomplete */
//(function($) {
//$.fn.autocomplete = function(option) {
//  return this.each(function() {
//    this.timer = null;
//    this.items = new Array();
//
//    $.extend(this, option);
//
//    $(this).attr('autocomplete', 'off');
//
//    // Focus
//    $(this).on('focus', function() {
//      this.request();
//    });
//
//    // Blur
//    $(this).on('blur', function() {
//      setTimeout(function(object) {
//        object.hide();
//      }, 200, this);
//    });
//
//    // Keydown
//    $(this).on('keydown', function(event) {
//      switch(event.keyCode) {
//        case 27: // escape
//          this.hide();
//          break;
//        default:
//          this.request();
//          break;
//      }
//    });
//
//    // Click
//    this.click = function(event) {
//      event.preventDefault();
//
//      value = $(event.target).parent().attr('data-value');
//
//      if (value && this.items[value]) {
//        this.select(this.items[value]);
//      }
//    }
//
//    // Show
//    this.show = function() {
//      var pos = $(this).position();
//
//      $(this).siblings('ul.dropdown-menu').css({
//        top: pos.top + $(this).outerHeight(),
//        left: pos.left
//      });
//
//      $(this).siblings('ul.dropdown-menu').show();
//    }
//
//    // Hide
//    this.hide = function() {
//      $(this).siblings('ul.dropdown-menu').hide();
//    }
//
//    // Request
//    this.request = function() {
//      clearTimeout(this.timer);
//
//      this.timer = setTimeout(function(object) {
//        object.source($(object).val(), $.proxy(object.response, object));
//      }, 200, this);
//    }
//
//    // Response
//    this.response = function(json) {
//      html = '';
//
//      if (json.length) {
//        for (i = 0; i < json.length; i++) {
//          this.items[json[i]['value']] = json[i];
//        }
//
//        for (i = 0; i < json.length; i++) {
//          if (!json[i]['category']) {
//            html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
//          }
//        }
//
//        // Get all the ones with a categories
//        var category = new Array();
//
//        for (i = 0; i < json.length; i++) {
//          if (json[i]['category']) {
//            if (!category[json[i]['category']]) {
//              category[json[i]['category']] = new Array();
//              category[json[i]['category']]['name'] = json[i]['category'];
//              category[json[i]['category']]['item'] = new Array();
//            }
//
//            category[json[i]['category']]['item'].push(json[i]);
//          }
//        }
//
//        for (i in category) {
//          html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';
//
//          for (j = 0; j < category[i]['item'].length; j++) {
//            html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
//          }
//        }
//      }
//
//      if (html) {
//        this.show();
//      } else {
//        this.hide();
//      }
//
//      $(this).siblings('ul.dropdown-menu').html(html);
//    }
//
//    $(this).after('<ul class="dropdown-menu"></ul>');
//    $(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));
//
//  });
//}
//})(window.jQuery);


