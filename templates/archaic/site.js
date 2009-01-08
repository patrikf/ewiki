
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

    s = $$('#searchbox input');
    s_default = 'search (beta)';
    s.each(function (el)
    {
        el.inactive = (el.value == s_default);
        el.setClass('inactive', el.inactive);
    });
    s.addEvent('focus', function (event)
    {
        el = $(event.target);
        el.setClass('inactive', false);
        if (el.inactive)
            el.value = '';
    });
    s.addEvent('blur', function (event)
    {
        el = $(event.target);
        el.inactive = (el.value == '');
        el.setClass('inactive', el.inactive);
        if (el.inactive)
            el.value = s_default;
    });
});

