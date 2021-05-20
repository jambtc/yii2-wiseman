
$("form").submit(function(e) {
    e.stopPropagation();
    e.preventDefault();

    var data =
        {
            "driver": 'web',
            "userId": "1234",
            "message": $('#message').val()
        }
    ;

    $.ajax({
        url: `/index.php?r=wiseman`,
        type: "POST",
        data:{
            "driver": 'web',
            "userId": "1234",
            "message": $('#message').val()
        },
        success:function(j){
            console.log('reply is', j);
            $.each( j, function( key, value ) {
              console.log( key + ": " + value );
              $('#log')[0].innerHTML += '<p class="btn btn-info">' + value.text + "</p></br>";
            });


            $('#message').val('');
        },
    });

    return false;
});
