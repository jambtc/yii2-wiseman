$(window).on('load', function() {
    $('#staticBackdrop').modal({
      backdrop: 'static',
      keyboard: false
    });
});

$('#staticBackdrop').on('shown.bs.modal', function () {
  $('#message').trigger('focus')
})



$("form").submit(function(e) {
    e.stopPropagation();
    e.preventDefault();
    var message = $('#message').val();
    var is_interactive = $('#interactive').val();

    if (message == ''){
        return false;
    }


    $.ajax({
        url: `/index.php?r=wiseman`,
        type: "POST",
        data:{
            "driver": 'web',
            "userId": "1234",
            "message": message,
            "interactive": is_interactive,
        },
        beforeSend: function (){
             $("#ChatLog").append('<li class="ChatLog__entry ChatLog__entry_mine"><p class="ChatLog__message text-break">'+message+'</p></li>');
        },
        success:function(j){
            // console.log('reply is', j);

            var messages = j.messages;

            messages.forEach(showMessage);

            // scroll div
            var objDiv = document.getElementById("ChatScroll");
            objDiv.scrollTop = objDiv.scrollHeight;

            // document.getElementById( 'ChatScroll' ).scrollIntoView();

            // reset input field
            $('#message').val('');
        },
    });

    return false;
});

function showMessage(elemento, index) {
    console.log('[answer]',elemento,index);
    // $('#ChatLog')[0].innerHTML += '<p class="btn btn-info">'+ elemento.text + "</p></br>";

    if (elemento.type == 'text' && elemento.text != ''){
        $("#ChatLog").append('<li class="ChatLog__entry"><img src="/bundles/landing-page/assets/img/logo.png" class="ChatLog__avatar"><p class="ChatLog__message">'+elemento.text+'</p></li>');
        var audio = new Audio("css/sounds/button-15.mp3");
        audio.play();
    }

    if (elemento.attachment != null){
        console.log('[attachment esiste ed è di tipo]',elemento.attachment.type);
        if (elemento.attachment.type == 'image'){
            // $('#ChatLog')[0].innerHTML += '<img src="'+elemento.attachment.url+'" class="img-thumbnail rounded mx-auto d-block" alt="..."></br>';
            var image = '<img src="'+elemento.attachment.url+'" class="img-thumbnail rounded mx-auto d-block" alt="...">';
            $("#ChatLog").append('<li class="ChatLog__entry"><p class="ChatLog__message">'+image+'</p></li>');
        }
    }

    if (elemento.actions != null) {
        console.log('[azioni esistono]',elemento.actions);
        elemento.actions.forEach(function (el, idx){
            if (el.type == 'button'){
                // $('#ChatLog')[0].innerHTML += makeButton(el);
                $("#ChatLog").append('<li class="ChatLog__entry">'+makeButton(el)+'</li>');
            }
        });

    }
}

function makeButton(el)
{
    t=  '<a onclick="submitButton(`'+el.value+'`)">';
    t+= '<btn class="btn btn-block btn-success mb-1" />'+el.text+'</button></br>';
    t+= '</a>';
    return t;
}

function submitButton(value)
{
    $('#interactive').val(true),
    $("#message").val(value);
    $("form").submit();
}
