; (function ($) {

    $(document).ready(function () {


        $('body').on('click', "#ai-curd-notice .notice-dismiss", function () {
            createCookie('notice_closed', 'true', 20); // The cookie will expire after 20 days

        });


        // Function to create a cookie
        function createCookie(name, value, days) {
            var expires;

            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 1000));   // seconds 
                // date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); // days
                expires = "; expires=" + date.toGMTString();
            } else {
                expires = "";
            }

            document.cookie = name + "=" + value + expires + "; path=/";
        };

    });

})(jQuery);