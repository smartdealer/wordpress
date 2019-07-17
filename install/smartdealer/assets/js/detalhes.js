jQuery(function () {
    jQuery("#box-images").owlCarousel({
        autoPlay: 3000,
        items: 4,
        itemsDesktop: [1199, 3],
        itemsDesktopSmall: [979, 3]
    });
    jQuery(document).delegate('*[data-toggle="lightbox"]', 'click', function (event) {
        event.preventDefault();
        jQuery(this).ekkoLightbox({
            gallery_parent_selector: '#box-images'
        });
    });
    jQuery('#form-lead').validate({
        submitHandler: function (form) {
            var btn = jQuery(form).find('[type=submit]');
            btn.prop('disabled', true);
            btn.val('Enviando..');
            jQuery(form).submit();
        }

    });
    if (typeof jQuery.fn.mask === 'function') {

        if (jQuery('[data-mask]').length) {
            jQuery('[data-mask]').each(function (a, b) {
                jQuery(b).mask(b.attributes['data-mask'].value);
            });
        }

        jQuery('[data-mask=phone]').mask('(99) 9999-9999?9');
        jQuery('[data-mask=uf]').mask('aa');
    }
    if (jQuery("#box-images").length) {
        jQuery("#box-images").owlCarousel({
            autoPlay: 3000,
            items: 4,
            itemsDesktop: [1199, 3],
            itemsDesktopSmall: [979, 3]
        });
    }

    jQuery(document).delegate('*[data-toggle="lightbox"]', 'click', function (event) {
        event.preventDefault();
        jQuery(this).ekkoLightbox({
            gallery_parent_selector: '#box-images'
        });
    });
    jQuery('head title').text(jQuery('[page-meta=title]').attr('meta-data'));
    jQuery('head').append('<meta name="description" content="' + jQuery('[page-meta=description]').attr('meta-data') + '"/>');
});                    