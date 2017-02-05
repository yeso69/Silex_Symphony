function createNoty(message, type) {
    var html = '<div class="alert alert-' + type + ' alert-dismissable page-alert">';
    html += '<button type="button" class="close"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>';
    html += message;
    html += '</div>';
    $(html).hide().prependTo('#noty-holder').slideDown();
};

$(function(){
    // createNoty('Hi! This is my message', 'info');
    // createNoty('success', 'success');
    // createNoty('warning', 'warning');
    // createNoty('danger', 'danger');
    // createNoty('info', 'info');
    $('.page-alert .close').click(function(e) {
        e.preventDefault();
        $(this).closest('.page-alert').slideUp();
    });
});