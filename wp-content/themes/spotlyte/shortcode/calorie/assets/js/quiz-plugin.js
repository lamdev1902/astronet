jQuery(function($) {
    $(document).ready(function() {
        $('.quiz-option .option p').click(function() {
            $(this).closest('.quiz-item').find('.option').removeClass('checked');

            $(this).closest('.option').addClass('checked');
        });

        $('#quizAction').click(function(){
            var value = 0;

            var totalValue = $("[name=total]").val(); 
            $('.quiz-item .option.checked').each(function() {
                var dataValue = $(this).find('p').attr('data-value');
                value += parseInt(dataValue)
            });

            $('.result').show();
            $('.result .bottom p').text("Your Total Score: "+ value + " out of " + totalValue);
        })
    });
})