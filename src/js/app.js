jQuery(function($){

    // form validation init..
    var myLanguage = {
      errorTitle : 'Form submission failed!',
      requiredFields : 'Заполните поле',
      badTime : 'You have not given a correct time',
      badEmail : 'Введите корректный E-mail',
      badTelephone : 'You have not given a correct phone number',
      badSecurityAnswer : 'You have not given a correct answer to the security question',
      badDate : 'You have not given a correct date',
      lengthBadStart : 'You must give an answer between ',
      lengthBadEnd : ' символов',
      lengthTooLongStart : 'You have given an answer longer than ',
      lengthTooShortStart : 'Введите больше ',
      notConfirmed : 'Values could not be confirmed',
      badDomain : 'Incorrect domain value',
      badUrl : 'The answer you gave was not a correct URL',
      badCustomVal : 'Неправильно заполнено поле',
      badInt : 'Введите номер',
      badSecurityNumber : 'Your social security number was incorrect',
      badUKVatAnswer : 'Incorrect UK VAT Number',
      badStrength : 'The password isn\'t strong enough',
      badNumberOfSelectedOptionsStart : 'You have to choose at least ',
      badNumberOfSelectedOptionsEnd : ' answers',
      badAlphaNumeric : 'The answer you gave must contain only alphanumeric characters ',
      badAlphaNumericExtra: ' and ',
      wrongFileSize : 'The file you are trying to upload is too large',
      wrongFileType : 'The file you are trying to upload is of wrong type',
      groupCheckedRangeStart : 'Please choose between ',
      groupCheckedTooFewStart : 'Please choose at least ',
      groupCheckedTooManyStart : 'Please choose a maximum of ',
      groupCheckedEnd : ' item(s)'
    };

    $.validate({
      language : myLanguage,
      scrollToTopOnError : false
    });
    // ..form validation init

    // form uploader..
    $('.input-file').change(function() {
      var name = this.value;
      reWin = /.*\\(.*)/;
      var fileTitle = name.replace(reWin, "$1");
      reUnix = /.*\/(.*)/;
      fileTitle = fileTitle.replace(reUnix, "$1");
      textContainer = $(this).parents('.form-uploadfile').find('.flname');
      textContainer.text(fileTitle);
      textContainer.parent().show();
    });

    $('.remove-file-btn').on('click', function(event) {
        // event.preventDefault();
        var $formWrapper = $(this).parents('.form-uploadfile');
        var $control = $formWrapper.find('.upload-control');
        var $fileName = $formWrapper.find('.filename-wrapper');
        $control.replaceWith($control = $control.clone(true));
        $fileName.hide();
    });
    // ..form uploader


    // google map..
    // if ($('#map-wrapper').length){
    //     var map;
    //     function initialize() {
    //         var mapOptions = {
    //             zoom: 17,
    //             center: new google.maps.LatLng(55.242512, 61.420713),
    //             scrollwheel: false,
    //             zoomControl: true,
    //             streetViewControl: false,
    //             mapTypeId: google.maps.MapTypeId.ROADMAP
    //             };

    //             map = new google.maps.Map(document.getElementById('map-wrapper'),
    //               mapOptions);

    //             var markerLatlng = new google.maps.LatLng(55.242512, 61.420713);
    //             var myMarker = new google.maps.Marker({
    //               position: markerLatlng,
    //               map: map,
    //         });

    //         var contentString = '<div id="content">Хлебозаводская, 7А</div>';
    //         var infowindow = new google.maps.InfoWindow({
    //             content: contentString
    //         });
    //         google.maps.event.addListener(myMarker, 'click', function() {
    //             infowindow.open(map,myMarker);
    //         });

    //     }

    //     google.maps.event.addDomListener(window, 'load', initialize);
    // }
    // ..google map

    // form submit..
    $jbtn = $('.btn-jsend-submit');
    if($jbtn){
      $jbtn.on('click', function(e){
        e.preventDefault();
        $jform = $(this).parents('.mail-form');
        url = $jform.attr('action');
        if( $jform.attr('action').indexOf('?tmpl=send') === -1 ){
          url =  $jform.attr('action') + '?tmpl=send';
        }
        $jform.attr('action', url);
        $jform.submit();
      });
    }
    // ..form submit


    (function() {

        var initPhotoSwipeFromDOM = function(gallerySelector) {

            // parse slide data (url, title, size ...) from DOM elements
            // (children of gallerySelector)
            var parseThumbnailElements = function(el) {
                var thumbElements = el.childNodes,
                    numNodes = thumbElements.length,
                    items = [],
                    figureEl,
                    linkEl,
                    size,
                    item;

                for(var i = 0; i < numNodes; i++) {

                    figureEl = thumbElements[i]; // <figure> element

                    // include only element nodes
                    if(figureEl.nodeType !== 1) {
                        continue;
                    }

                    linkEl = figureEl.children[0]; // <a> element

                    size = linkEl.getAttribute('data-size').split('x');

                    // create slide object
                    item = {
                        src: linkEl.getAttribute('href'),
                        w: parseInt(size[0], 10),
                        h: parseInt(size[1], 10)
                    };

                    if(figureEl.children.length > 1) {
                        // <figcaption> content
                        item.title = figureEl.children[1].innerHTML;
                    }

                    if(linkEl.children.length > 0) {
                        // <img> thumbnail element, retrieving thumbnail url
                        item.msrc = linkEl.children[0].getAttribute('src');
                    }

                    item.el = figureEl; // save link to element for getThumbBoundsFn

                    var mediumSrc = el.getAttribute('data-med');
                    if(mediumSrc) {
                        size = el.getAttribute('data-med-size').split('x');
                        // "medium-sized" image
                        item.m = {
                            src: mediumSrc,
                            w: parseInt(size[0], 10),
                            h: parseInt(size[1], 10)
                        };
                    }
                    // original image
                    item.o = {
                        src: item.src,
                        w: item.w,
                        h: item.h
                    };
                    items.push(item);
                }

                return items;
            };

            // find nearest parent element
            var closest = function closest(el, fn) {
                return el && ( fn(el) ? el : closest(el.parentNode, fn) );
            };

            // triggers when user clicks on thumbnail
            var onThumbnailsClick = function(e) {
                e = e || window.event;
                e.preventDefault ? e.preventDefault() : e.returnValue = false;

                var eTarget = e.target || e.srcElement;

                // find root element of slide
                var clickedListItem = closest(eTarget, function(el) {
                    return (el.tagName && el.tagName.toUpperCase() === 'FIGURE');
                });

                if(!clickedListItem) {
                    return;
                }

                // find index of clicked item by looping through all child nodes
                // alternatively, you may define index via data- attribute
                var clickedGallery = clickedListItem.parentNode,
                    childNodes = clickedListItem.parentNode.childNodes,
                    numChildNodes = childNodes.length,
                    nodeIndex = 0,
                    index;

                for (var i = 0; i < numChildNodes; i++) {
                    if(childNodes[i].nodeType !== 1) {
                        continue;
                    }

                    if(childNodes[i] === clickedListItem) {
                        index = nodeIndex;
                        break;
                    }
                    nodeIndex++;
                }



                if(index >= 0) {
                    // open PhotoSwipe if valid index found
                    openPhotoSwipe( index, clickedGallery , 0);
                }
                return false;
            };


            var openPhotoSwipe = function(index, galleryElement, disableAnimation, fromURL) {
                var pswpElement = document.querySelectorAll('.pswp')[0],
                    gallery,
                    options,
                    items;

                items = parseThumbnailElements(galleryElement);

                // define options (if needed)
                options = {

                    // define gallery index (for URL)
                    galleryUID: galleryElement.getAttribute('data-pswp-uid'),

                    getThumbBoundsFn: function(index) {
                        // See Options -> getThumbBoundsFn section of documentation for more info
                        var thumbnail = items[index].el.getElementsByTagName('img')[0], // find thumbnail
                            pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
                            rect = thumbnail.getBoundingClientRect();

                        return {x:rect.left, y:rect.top + pageYScroll, w:rect.width};
                    }

                };

                options.index = parseInt(index, 10);


                // exit if index not found
                if( isNaN(options.index) ) {
                    return;
                }

                if(disableAnimation) {
                    options.showAnimationDuration = 0;
                }



                // Pass data to PhotoSwipe and initialize it
                gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);


                var realViewportWidth,
                    useLargeImages = true,
                    firstResize = true,
                    imageSrcWillChange;

                gallery.listen('beforeResize', function() {

                    var dpiRatio = window.devicePixelRatio ? window.devicePixelRatio : 1;
                    dpiRatio = Math.min(dpiRatio, 2.5);
                    realViewportWidth = gallery.viewportSize.x * dpiRatio;

                    if(realViewportWidth >= 1200 || (!gallery.likelyTouchDevice && realViewportWidth > 800) || screen.width > 1200 ) {
                        if(!useLargeImages) {
                            useLargeImages = true;
                            imageSrcWillChange = true;
                        }
                    } else {
                        if(useLargeImages) {
                            useLargeImages = false;
                            imageSrcWillChange = true;
                        }
                    }

                    if(imageSrcWillChange && !firstResize) {
                        gallery.invalidateCurrItems();
                    }

                    if(firstResize) {
                        firstResize = false;
                    }
                    imageSrcWillChange = false;
                });

                gallery.listen('gettingData', function(index, item) {
                    if( useLargeImages ) {
                        item.src = item.o.src;
                        item.w = item.o.w;
                        item.h = item.o.h;
                    } else {
                        item.src = item.m.src;
                        item.w = item.m.w;
                        item.h = item.m.h;
                    }
                });



                gallery.init();


            };

            // loop through all gallery elements and bind events
            var galleryElements = document.querySelectorAll( gallerySelector );

            for(var i = 0, l = galleryElements.length; i < l; i++) {
                galleryElements[i].setAttribute('data-pswp-uid', i+1);
                galleryElements[i].onclick = onThumbnailsClick;
            }


            var slick = jQuery('.slick').slick({
                slidesToShow: 3,
                slidesToScroll: 1,
                arrows: true,
                slide: 'figure'
            });
        };

        initPhotoSwipeFromDOM('.gallery');



    })();

    // slider init..
    $('img').on('dragstart', function(event) { event.preventDefault(); });
    $('a').on('dragstart', function(event) { event.preventDefault(); });


    // ..slider init


});
