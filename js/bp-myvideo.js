jQuery("a[rel]").live('click', function (e) {
    jQuery(this).overlay({
        effect: 'default',
        expose: '#8E2323',
        api: true,
        load: true,

    });
    e.preventDefault();
});