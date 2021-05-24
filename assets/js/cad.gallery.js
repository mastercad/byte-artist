createGallery = function () {

    // make own gallery in each gallery
    $('.cad-gallery').each(function() {
        popupImages = jQuery(this).find('img');

        popupImages.each(function() {
            prepareElement($(this));
        });

        makeGallery(popupImages);
    });

    // make also global gallery with all pics on current site
    var popupImages = $('.blog_pic');

    popupImages.each(function() {
        prepareElement($(this));
    });

    makeGallery(popupImages);
}

function makeGallery(images){
    images.magnificPopup({
        type: 'image',
        preloader: true,
        gallery: {
            enabled:true,
            navigateByImgClick: true,
            preload: [1,1],
            tCounter: '<span class="mpf-counter">%curr% von %total%</span>'
        },
        callbacks: {
          elementParse: function(item) {
              item.src = item.el.attr('data-src');
          }
        }
    });
}

function prepareElement(element) {
    // alle beschränkungen für den Thumbnailer raus schneiden!
    var regex = /(.*\/file\/.*?)\//;
    var src = element.attr('src');
    src = regex.exec(src);

    if (null === src
        || undefined === src
    ) {
        regex = /(.*\/file\/.*)/;
        src = element.attr('src');
        src = regex.exec(src);
        if (null !== src
            && undefined !== src
        ) {
            src = src[0];
        }
    }

    if (null === src
        || undefined === src
    ) {
        src = element.attr('src');
    }

    if (null !== src
        && undefined !== src
    ) {
        element.attr('data-mfp-src', src);
        element.addClass('mfp-fade mfp-image magnified');
    }
}