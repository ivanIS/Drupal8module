(function($) {
    Drupal.behaviors.message_read = {
        attach: function (context) {
            $(context).find('.d1').once().click(function () {
                var currentId = $(this).attr('id');
                $.ajax({
                    type: "POST",
                    url: "/056qht17",
                    data: {
                        'id': currentId
                    }
                })
                    .done(function (data) {
                        var fromResponse = data;
                        console.log(fromResponse);
                        $( "#"+fromResponse[0]+"" )
                            .replaceWith( "<td id='#"+fromResponse+"'>"+fromResponse[1]+"</td>"
                            );
                        $( "#countMessage" )
                            .replaceWith( "<div id='countMessage'><p>"+fromResponse[2]+"</p></div>"
                            );
                    });
            });
            $('#edit-allusersubmit').click(function () {
                $("#messenger-form").trigger('reset');
            });
            $('#edit-usersubmit').click(function () {
                $("#karakum-form").trigger('reset');
            });
            $('#hand').click(function () {
                var url = $('#hand').attr('class');
                $(location).attr('href',url);
            });
            $('.row-h').hover(
                function() {
               $('.row-h').css({"background-color": "#B2D641"});
            },
                function() {
                    $('.row-h').css({"background-color": "white"});
                }
            )
        }
    }
})(jQuery);