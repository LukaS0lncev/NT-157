jQuery(document).ready(function($) {
    $('.tcode').textillate({
        loop: true,
        minDisplayTime: 5000,
        initialDelay: 800,
        autoStart: true,
        inEffects: [],
        outEffects: [],
        in: {
            effect: 'rollIn',
            delayScale: 1.5,
            delay: 50,
            sync: false,
            shuffle: true,
            reverse: false,
            callback: function() {}
        },
        out: {
            effect: 'fadeOut',
            delayScale: 1.5,
            delay: 50,
            sync: false,
            shuffle: true,
            reverse: false,
            callback: function() {}
        },
        callback: function() {}
    });
})

jQuery(document).ready(function($) {
    var thumb = jQuery('#yzthumbnail');
    var select = this.value;

    if (jQuery('#yzthumbnail').is(":checked")) {
        jQuery('.yzselectthumbtr').show();
    } else {
        jQuery('.yzselectthumbtr').hide();
    }

    thumb.change(function() {
        if (jQuery('#yzthumbnail').is(":checked")) {
            jQuery('.yzselectthumbtr').fadeIn();
        } else {
            jQuery('.yzselectthumbtr').hide();
        }
    });


    var seo = jQuery('#yzseodesc');

    if (jQuery('#yzseodesc').is(":checked")) {
        jQuery('.yzseoplugintr').show();
    } else {
        jQuery('.yzseoplugintr').hide();
    }

    seo.change(function() {
        if (jQuery('#yzseodesc').is(":checked")) {
            jQuery('.yzseoplugintr').fadeIn();
        } else {
            jQuery('.yzseoplugintr').hide();
        }
    });

    var tags = jQuery('#yzexcludetags');

    if (jQuery('#yzexcludetags').is(":checked")) {
        jQuery('.yzexcludetagslisttr').show();
    } else {
        jQuery('.yzexcludetagslisttr').hide();
    }

    tags.change(function() {
        if (jQuery('#yzexcludetags').is(":checked")) {
            jQuery('.yzexcludetagslisttr').fadeIn();
        } else {
            jQuery('.yzexcludetagslisttr').hide();
        }
    });

    var tags2 = jQuery('#yzexcludetags2');

    if (jQuery('#yzexcludetags2').is(":checked")) {
        jQuery('.yzexcludetagslist2tr').show();
    } else {
        jQuery('.yzexcludetagslist2tr').hide();
    }

    tags2.change(function() {
        if (jQuery('#yzexcludetags2').is(":checked")) {
            jQuery('.yzexcludetagslist2tr').fadeIn();
        } else {
            jQuery('.yzexcludetagslist2tr').hide();
        }
    });

    var rcont = jQuery('#yzexcludecontent');

    if (jQuery('#yzexcludecontent').is(":checked")) {
        jQuery('.yzexcludecontentlisttr').show();
    } else {
        jQuery('.yzexcludecontentlisttr').hide();
    }

    rcont.change(function() {
        if (jQuery('#yzexcludecontent').is(":checked")) {
            jQuery('.yzexcludecontentlisttr').fadeIn();
        } else {
            jQuery('.yzexcludecontentlisttr').hide();
        }
    });


    var imgselect = jQuery('#imgselect');
    if (jQuery('#imgselect option:selected').val() == "Указать автора") {
        jQuery('#ownname').fadeIn();
    } else {
        jQuery('#ownname').hide();
    }
    imgselect.change(function() {
        if (jQuery('#imgselect option:selected').val() == "Указать автора") {
            jQuery('#ownname').fadeIn();
        } else {
            jQuery('#ownname').hide();
        }
    });
    var capalt = jQuery('#capalt');
    if (jQuery('#capalt option:selected').val() == "Использовать alt по возможности") {
        jQuery('#altimg').fadeIn();
    } else {
        jQuery('#altimg').hide();
    }
    capalt.change(function() {
        if (jQuery('#capalt option:selected').val() == "Использовать alt по возможности") {
            jQuery('#altimg').fadeIn();
        } else {
            jQuery('#altimg').hide();
        }
    });

    
        if (jQuery('#yzqueryselect option:selected').val() == "Все таксономии, кроме исключенных") {
            jQuery('.yztaxlisttr').fadeIn();
            jQuery('#excludespan').fadeIn();
        } else {
            jQuery('.yztaxlisttr').hide();
            jQuery('#excludespan').hide();
        }
        if (jQuery('#yzqueryselect option:selected').val() == "Только указанные таксономии") {
            jQuery('.yzaddtaxlisttr').fadeIn();
            jQuery('#includespan').fadeIn();
        } else {
            jQuery('.yzaddtaxlisttr').hide();
            jQuery('#includespan').hide();
        }
    
    var yzqueryselect = jQuery('#yzqueryselect');
    yzqueryselect.change(function() {
        if (jQuery('#yzqueryselect option:selected').val() == "Все таксономии, кроме исключенных") {
            jQuery('.yztaxlisttr').fadeIn();
            jQuery('#excludespan').fadeIn();
        } else {
            jQuery('.yztaxlisttr').hide();
            jQuery('#excludespan').hide();
        }
        if (jQuery('#yzqueryselect option:selected').val() == "Только указанные таксономии") {
            jQuery('.yzaddtaxlisttr').fadeIn();
            jQuery('#includespan').fadeIn();
        } else {
            jQuery('.yzaddtaxlisttr').hide();
            jQuery('#includespan').hide();
        }
    });


})