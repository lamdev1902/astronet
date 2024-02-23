jQuery(function($) {
    $( document).ready(function() {
        
        var data = ajax_object.data;    

        
        $("input[name='nickname']").change(function () {
            validateText('nickname',data);
        });

        $("input[name='title']").change(function () {
            validateText('title',data);
        });

        $("textarea[name='feedback']").change(function () {
            validateText('feedback',data);
        });

        $("input[name='rate']").on('click change', function(e) {
            buttonType();
        })

        $(".recaptcha-checkbox").on('attribute-change', function(){
            buttonType();
        })

    });
})

function validateText(input,data)
{

    var element = (input == 'feedback') ? `textarea[name='${input}']` : `input[name='${input}']`;

    var text = $(element).val();
    
    var containsKeyword = data.find(function(item) {
        return text.includes(item.keyword);
    });

    if(text.match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g))
    {
        $(element).val('');
        $('.' + input + '-error').text('');
        $('.' + input + '-error').text('Your input contains url');

    }else if(containsKeyword)
    {
        $(element).val('');
        $('.' + input + '-error').text('');
        $('.' + input + '-error').text(`Your input contains disallowed characters ( ${containsKeyword.keyword} )`);
    }
    else {
        $('.' + input + '-error').text('');
    }


    buttonType();
}

function buttonType()
{
    var response = grecaptcha.getResponse();
    if($("input[name='nickname']").val() && $("input[name='title']").val() && $("textarea[name='feedback']").val() && $("input[name='rate']:checked").val() != undefined && response)
    {
        $('#btnReview').attr('disabled', false);
    }else {
        $('#btnReview').attr('disabled', true);
    }
}