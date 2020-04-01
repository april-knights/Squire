require('./bootstrap');
require('./bootstrap-confirmation');

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})

$('[data-toggle=confirmation]').confirmation({
    rootSelector: '[data-toggle=confirmation]',
    popout: true,
});
