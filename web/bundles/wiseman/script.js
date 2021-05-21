
$("form").submit(function(e) {
    e.stopPropagation();
    e.preventDefault();

    $.ajax({
        url: `/index.php?r=wiseman`,
        type: "POST",
        data:{
            "driver": 'web',
            "userId": "1234",
            "message": $('#message').val(),
            "interactive": $('#interactive').val(),
        },
        success:function(j){
            console.log('reply is', j);

            var messages = j.messages;

            messages.forEach(showMessage);
            var objDiv = document.getElementById("log");
            objDiv.scrollTop = objDiv.scrollHeight;
            $('#message').val('');
        },
    });

    return false;
});

function showMessage(elemento, index) {
    // console.log('[answer]',elemento,index);
    $('#log')[0].innerHTML += '<p class="btn btn-info">'+ elemento.text + "</p></br>";

    if (elemento.attachment != null){
        console.log('[attachment esiste ed è di tipo]',elemento.attachment.type);
        if (elemento.attachment.type == 'image'){
            $('#log')[0].innerHTML += '<img src="'+elemento.attachment.url+'" class="img-thumbnail rounded mx-auto d-block" alt="..."></br>';
        }
    }

    if (elemento.actions != null) {
        console.log('[azioni esistono]',elemento.actions);
        elemento.actions.forEach(function (el, idx){
            if (el.type == 'button'){
                $('#log')[0].innerHTML += makeButton(el);
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
