$(document).ready(function() {
    $('.js-book-delete').on('click', function(e) {
        e.preventDefault();

        var $link = $(e.currentTarget);

        $.ajax({
            method: 'POST',
            url: $link.attr('href'),
        }).done(function(data) {
            location.href = "/";
        });
    });

    $('.js-file-delete').on('click', function(e) {
        e.preventDefault();

        var $link = $(e.currentTarget);

        $.ajax({
            method: 'POST',
            url: $link.attr('href'),
        }).done(function(data) {
            location.reload();
        });
    });

    $('.js-image-delete').on('click', function(e) {
        e.preventDefault();

        var $link = $(e.currentTarget);

        $.ajax({
            method: 'POST',
            url: $link.attr('href'),
        }).done(function(data) {
            location.reload();
        });
    });
});