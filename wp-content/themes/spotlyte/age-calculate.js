jQuery(function($){

    $(document).ready( function() {

        var date = new Date();

        var day = date.getDate();
        var month = date.getMonth() + 1;
        var year = date.getFullYear();

        if (month < 10) month = "0" + month;
        if (day < 10) day = "0" + day;

        var today = year + "-" + month + "-" + day;   

        $("[name=dayOfBirth]").attr("value", today);
        $("[name=ageOfTheDate]").attr("value", today);


        var optionMonDate = '.dateMonthInput option:eq(' + (month - 1) + ')';
        var optionMonAge = '.ageMonthInput option:eq(' + (month - 1) + ')';
        $(optionMonDate).attr('selected', 'selected');
        $(optionMonAge).attr('selected', 'selected');

        var lastDayOfMonth = new Date(year, month, 0);

        var numberOfDays = lastDayOfMonth.getDate();

       
        for(var i = 1; i <= numberOfDays; i++)
        {
            var optionDate = $('<option></option>');
            var optionAge = $('<option></option>');

            optionDate.attr('value', i);
            optionDate.text(i);
            $('.dayDateInput').append(optionDate);

            optionAge.attr('value', i);
            optionAge.text(i);
            $('.ageDateInput').append(optionAge);
            
        }
        var optionDayDate = '.dayDateInput option:eq(' + (day - 1) + ')';
        var optionDayAge = '.ageDateInput option:eq(' + (day - 1) + ')';
        $(optionDayDate).attr('selected', 'selected');
        $(optionDayAge).attr('selected', 'selected');

        $("[name=year-birth]").val(year);
        $("[name=year-age]").val(year);

        $('[name=date-birth]').change(function(){
            var result = getTime(1);
    
            changeTime(result, "dayOfBirth");
        })
        $('[name=mon-birth]').change(function(){
            var result = getTime(1);
    
            changeTime(result, "dayOfBirth");
            
        })
        $('[name=year-birth]').change(function(){
            var result = getTime(1);
    
            changeTime(result, "dayOfBirth");
            
        })
        $('[name=date-age]').change(function(){
            var result = getTime(2);
    
            changeTime(result, "ageOfTheDate");
            
        })
        $('[name=mon-age]').change(function(){
            var result = getTime(2);
    
            changeTime(result, "ageOfTheDate");
            
        })
    
        $('[name=year-age]').change(function(){
            var result = getTime(2);
    
            changeTime(result, "ageOfTheDate");
            
        })

        $("#btnAge").on('click', function(){

            $('#spinner').show();

            var formDataArray = $('.form.age-calculate').serializeArray();
    
            var jsonData = {
                    'from_date': null,
                    'to_date': null
            };
            var dateValue = '';

            var check = 0;
            formDataArray.forEach(function(e) {
                var fieldName = e.name;
                var fieldValue = e.value;
    
    
                if (fieldName === 'dayOfBirth' ) {
                    jsonData['from_date'] = fieldValue;
                    dateValue = e.value;
                } else if (fieldName === 'ageOfTheDate') {
                    jsonData['to_date'] = fieldValue;

                    var date1 = new Date(dateValue);
                    var date2 = new Date(e.value);

                    // So sánh các đối tượng Date
                    if (date1 >= date2) {
                        $('#spinner').hide();

                        var paragraph = $("<p style='color:red'>").text('Date of birth needs to be earlier than the age at date.');
                        
                        $(".content-top").removeClass('inactive');
                        $(".content-top .result").empty();
                        $(".content-top .result").append(paragraph);
                        check++
                    }
                }
            });
    
            if(!check)
            {
                $.ajax({
                    url: 'http://wp-astronet.local/wp-json/api/v1/age/',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(jsonData),
                    success: function(response) {
                        // Xử lý phản hồi từ server nếu cần
                        if(response['status'] === 200)
                        {
                            var result = response['result']['age'];
    
    
                            $('.content-top').removeClass('inactive');
                            $(".content-top .result").empty();
                            $.each(result, function (key, value) {
                                // Tạo thẻ <p> với văn bản tương ứng
                                var paragraph = $("<p>").text(key + ": " + value);
                        
                                // Thêm thẻ <p> vào container
                                $(".content-top .result").append(paragraph);
                            });
                        }
                        $('#spinner').hide();
                    },
                    error: function(error) {
                        // Xử lý lỗi nếu có
                        console.error('Error:', error);
                    }
                });
            }
        });

    });
})

function changeTime(time, element)
{
    var day = time[0].day;
    var month = time[0].month;
    var year = time[0].year;

    var today = year + "-" + month + "-" + day;   
    
    var elementName = "[name="+element+"]";
    $(elementName).attr("value", today);
}

function getTime($type)
{
    var result = [];
    if($type == 1)
    {
        var day = $('[name=date-birth]').val();
        var mon = $('[name=mon-birth]').val();
        var year = $('[name=year-birth]').val();
    }else {
        var day = $('[name=date-age]').val();
        var mon = $('[name=mon-age]').val();
        var year = $('[name=year-age]').val();
    }
    
    result.push(
        {
            "day": day,
            "month": mon,
            "year": year
        }
    )
    return result;
}