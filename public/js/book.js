$(document).ready(function() {
    $("[class*='js-delete-']").on('click', function(e) {
        e.preventDefault();

        var $link = $(e.currentTarget);

        $.ajax({
            method: 'POST',
            url: $link.attr('href'),
        }).done(function(data) {
            if ($link.attr('class').indexOf("book") === -1) {
                location.reload();
            } else {
                location.href = "/";
            }
        });
    });
});