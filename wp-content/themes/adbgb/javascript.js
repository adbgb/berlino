var sidebar_starting_height = $('div#sidebar').offset().top;

$(window).scroll(function() {
    if (document.body.scrollTop <= sidebar_starting_height)
        $('div#sidebar').css('margin-top', '1%');
    else
        $('div#sidebar').css('margin-top', document.body.scrollTop - sidebar_starting_height + 20);
});

$('div#sidebar > span.sub-title:not(:first-child)').click(function() {
    $('div#sidebar > div:visible').not($(this).next().next()).toggle('500');
    $(this).next().next().toggle('500');
});
