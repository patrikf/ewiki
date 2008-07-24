
Element.implement({
    'setClass': function (name, set)
    {
        if (set)
            return this.addClass(name);
        else
            return this.removeClass(name);
    }
});

function check_opts()
{
    $$('.form-opt').each(function (el)
    {
        $(el.id+"-target").setClass('hidden', !el.checked);

        /* do not upload a file if file box is hidden */
        if (el.id == 'file-opt' && !el.checked)
            $('file-input').value = '';
    });
}

window.addEvent('domready', function ()
{
    $$('.form-opt').addEvent('change', check_opts);
    check_opts();
});

