jQuery(function($) {
    $( document).ready(function() {
        $("input[name='nickname']").change(function () {
            validateText('nickname');
        });

        $("input[name='title']").change(function () {
            validateText('title');
        });

        $("textarea[name='feedback']").change(function () {
            validateText('feedback');
        });

        $("input[name='rate']").on('click change', function(e) {
            buttonType();
        })

        $("#btnReview").on('click', function(){
            
        });
        
    });


})

function validateText(input)
{
    var element = (input == 'feedback') ? `textarea[name='${input}']` : `input[name='${input}']`;

    var text = $(element).val();
    
    if(text.match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g))
    {
        $(element).val('');
    }
    if(text.includes('porn') || text.includes('sex'))
    {
        $(element).val('');
    }

    buttonType();
}

function buttonType()
{
    if($("input[name='nickname']").val() && $("input[name='title']").val() && $("textarea[name='feedback']").val() && $("input[name='rate']:checked").val() != undefined)
    {
        $('#btnReview').attr('disabled', false);
    }else {
        $('#btnReview').attr('disabled', true);
    }
}