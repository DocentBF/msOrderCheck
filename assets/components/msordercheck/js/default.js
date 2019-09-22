msOrderCheck = {
    defaults: {
        form: ".msOrderCheck",
        resultsBlock: '.msOrderCheckResult',
        actionUrl: "/assets/components/msordercheck/action.php"
    },
    config: {},
    init: function (config) {
        if (typeof(jQuery) == "undefined") {
            document.write('<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>');
        }
        this.config = $.extend(this.defaults, config);
        var $form = $(this.config.form);

        $form.on('submit', function (e) {
            e.preventDefault();

            var formData = $form.serialize();
            var $resultsBlock = $(msOrderCheck.config.resultsBlock);
            $form.addClass('loading');
            $resultsBlock.html('');

            $.ajax({
                url: msOrderCheck.config['actionUrl'],
                data: formData,
                method: 'POST',
                context: this,
                dataType: "json",
                cache: false,
            })
                .done(function (response) {
                    if (response['success']) {
                        var content = response.data.rows;
                        if(response.data.total == 0)
                            content = response.message;
                        $resultsBlock.html(content);
                    } else {
                        console.log(response);
                    }
                    msOrderCheck.scrollToResults();
                })
                .fail(function (e) {
                    console.log(e);
                })
                .always(function () {
                    $form.removeClass('loading');
                });
            return true;
        });
    },
    scrollToResults: function () {
        var $results = $(this.config.resultBlock);
        $("html, body").animate({
            scrollTop: $results.offset().top || 0
        }, "fast")
    }
};