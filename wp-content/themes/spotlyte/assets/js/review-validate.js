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

    if(input == 'title'){
        var regex = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;;

        if(!regex.test(text)) {
            $(element).val('');
            $('.' + input + '-error').text('');
            $('.' + input + '-error').text('Please check your email!');
        }else {
            $('.' + input + '-error').text('');
        }
        
    }else if(/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/|www\.)[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/.test(text))
    {
        $(element).val('');
        $('.' + input + '-error').text('');
        $('.' + input + '-error').text('Your input contains url');

    }else if(containsKeyword)
    {
        $(element).val('');
        $('.' + input + '-error').text('');
        $('.' + input + '-error').text(`Your input contains disallowed characters ( ${containsKeyword.keyword} )`);
    }else if(text.match(/<[^>]*>?/)){
        $(element).val('');
        $('.' + input + '-error').text('');
        $('.' + input + '-error').text('Your input contains disallowed characters');
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