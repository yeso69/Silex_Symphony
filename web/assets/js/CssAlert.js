

$('#sondag').click(function () {
    alert("je suis la")
    $.ajax({
        type: "POST",
        url: "",
        data: {id: id, begin: goodDate,end:date_end,category:category,titre:title}
    }).done(function ( msg ) {
        alert("je passe dans done")
    });
    $('.prod').css( "background-color", "orange" );
});