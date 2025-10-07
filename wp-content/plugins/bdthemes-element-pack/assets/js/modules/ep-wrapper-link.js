jQuery(document).ready(function () {
    jQuery('body').on('click', '.bdt-element-link', function () {
        var $el = jQuery(this)
          , settings = $el.data("ep-wrapper-link");
        if (settings && settings.url && (/^https?:\/\//.test(settings.url) || settings.url.startsWith("#"))) {
            var id = "bdt-element-link-" + $el.data("id");
            0 === jQuery("#" + id).length && jQuery("body").append(jQuery("<a/>").prop({
                target: settings.is_external ? "_blank" : "_self",
                href: settings.url,
                class: "bdt-hidden",
                id: id,
                rel: settings.is_external ? "noopener noreferrer" : ""
            })),
            jQuery("#" + id)[0].click()
        }
    });
});
