jQuery(function($) {
    $( document).ready(function() {
        var index = 0;

        var optionsType = [];
        var optionsAnswer = [];
        $('#optionType').find('option').each(function() {
            var optionValue = $(this).val(); 
            var optionText = $(this).text(); 
            var option = {
                value: optionValue,
                text: optionText
            };
            optionsType.push(option); 
        });

        $('#optionAnswer').find('option').each(function() {
            var optionValue = $(this).val(); 
            var optionText = $(this).text(); 
            var option = {
                value: optionValue,
                text: optionText
            };
            optionsAnswer.push(option);
        });


        $(".btnMore").click(function(){
            
            var type = $(this).data('type');
            index++;

            var divItem = $('<div class="quiz-item"></div>');

            if(type == 'quiz') {
                var itemType = $('<div class="item"></div>');
                var itemAnwser = $('<div class="item"></div>');
                var itemContent = `<div class="item">
                    <input type="text" name="quiz[${index}][quiz_text]">
                </div>`;
                var itemPosition = `<div class="item">
                    <input type="text" name="quiz[${index}][position]" value="0">
                </div>`;

                var itemBtn = $(`<button class="btnRemove" type="button" data-type="quiz" data-position="${index}">x</button>`);

                var selectType = `<select name="quiz[${index}][type_id]">`;
                var selectAnswer = `<select name="quiz[${index}][answer_id]">`;


                $.each(optionsType, function(index, item){
                    var optionType = `<option value="${item['value']}">${item['text']}</option>`;
                    selectType += optionType;
                }) 

                $.each(optionsAnswer, function(index, item){
                    var optionAnswer = `<option value="${item['value']}">${item['text']}</option>`;
                    selectAnswer += optionAnswer;
                }) 
                    
                itemType.append(selectType);
                itemAnwser.append(selectAnswer);

                divItem.append(itemType,itemAnwser,itemContent,itemPosition,itemBtn);
            }else if(type == 'type') {
                var itemContent = `<div class="item">
                    <input type="text" name="type[${index}][name]">
                </div>`;
                var itemPosition = `<div class="item">
                    <input type="text" name="type[${index}][code]">
                </div>`;

                var itemBtn = $(`<button class="btnRemove" type="button" data-type="type" data-position="${index}">x</button>`);
                    
                divItem.append(itemContent,itemPosition,itemBtn);
            }else {

            }
            
            $(".quiz").append(divItem);

        })

         $('.quiz').on('click', '.btnRemove', function() {
            index--;
            var type = $(this).data('type');

            $(this).closest('.quiz-item').remove();

            $('.quiz-item').each(function(index) {
                var newPosition = index -1;
                if(type == 'quiz') {
                    $(this).find('.item select').each(function() {
                        var nameAttr = $(this).attr('name');
                        var newNameAttr = nameAttr.replace(/\[\d+\]/, '[' + newPosition + ']');
                        $(this).attr('name', newNameAttr);
                    });
                }
                $(this).find('.item input[type="text"]').each(function() {
                    var nameAttr = $(this).attr('name');
                    var newNameAttr = nameAttr.replace(/\[\d+\]/, '[' + newPosition + ']');
                    $(this).attr('name', newNameAttr);
                });
                $(this).find('.btnRemove').data('position', newPosition);
            });
        });

        $('.close-popup').click(function() {
            $('.answer').hide();
        })

        $('.answer-input').on('change', function() {
            var hasValue = false;
            $('.answer-input').each(function() {
                if ($(this).val() !== '') {
                    hasValue = true;
                    return false; 
                }
            });
    
            if (hasValue) {
                $('#btnAddAnswer').prop('disabled', false); 
            } else {
                $('#btnAddAnswer').prop('disabled', true);
            }
        });

        $('.btnOpenPopup').click(function(e){
            e.preventDefault();
            $('.answer').show();
        })

        $('#btnAddAnswer').on('click', function() {
            var numInputsWithValue = 0;
    
            $('.answer-input').each(function() {
                if ($(this).val() !== '') {
                    numInputsWithValue++;
                }
            });
    
            if(numInputsWithValue > 0) {
                $('.number-options').text(numInputsWithValue + " Options");
            }

            $("input[name='answer[edit-option]']").val(true);

            $('.answer').hide();
        });
    });
})