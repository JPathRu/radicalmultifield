jQuery(document).ready(function()
{
    jQuery(document).on('subform-row-add', function(event, row)
    {
        jQuery(row).find('.btn-group').each(function (i, el)
        {
            jQuery(el).find("label:first").addClass("btn");
            jQuery(el).find("label:last").addClass("btn active btn-danger");
        });

        jQuery(row).on('click', '.btn-group label:last-of-type', function ()
        {
            jQuery(this).addClass("active btn-danger");
            jQuery(this).parent().find("label:first-of-type").removeClass("active btn-success");
        });

        jQuery(row).on('click', '.btn-group label:first-of-type', function ()
        {
            jQuery(this).addClass("active btn-success");
            jQuery(this).parent().find("label:last-of-type").removeClass("active btn-danger");
        });
    });
});
