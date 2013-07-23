/**
 * Plugin for creating/managing cookies
 *
 * @author Woody Romelus
 */

;(function($, window, document, undefined) {

    var Cookie = {
        /**
         * Initialization & executes the method relevant to the parameter.
         *
         * @param (Object) argument to handle
         * @return (Object) result of the object method
         */
        init: function(options, elem) {
            var result;

            switch (typeof(options)) {
                case "object":
                    // Merge the user options with defaults
                    $.cookie.options = $.extend({}, $.cookie.options, options);
                    result = elem._createCookie();
                break;
                case "undefined":
                    result = elem._displayCookies();
                break;
                case "string":
                    result = elem._contains(options);
                break;
                default:
                    throw "Argument not supported!";
                break;
            }
            return result;
        },

        /**
         * Displays the cookies for the current domain.
         *
         * @return (String) String representation of the cookies
         */
        _displayCookies: function() {
            var cookies = document.cookie.split(";");
            var retVal = "";

            if(cookies == "") {
                retVal = "No cookies set.";
            } else {
                cookies.forEach(function(e, i, a) {
                    var parts = e.split("=");
                    retVal += parts;
                });
            }
            return retVal;
        },

        /**
         * Creates a browser cookie.
         */
        _createCookie: function() {
            document.cookie = $.cookie.options.name
                              + "=" + $.cookie.options.value
                              + "; expires=" + $.cookie.options.expires
                              + "; path=" + $.cookie.options.path;
        },

        /**
         * Determines if a cookie exists.
         *
         * @param (String) the cookie name to look for
         * @return (Boolean) Flag indicating if the cookie exists
         */
        _contains: function(name) {
            var cookies = document.cookie.split(";");
            var cookieElements = {};

            cookies.forEach(function(e, i, a) {
                var parts = e.trim().split("=");
                cookieElements[parts[0]] = parts[1];
            });
            return (name in cookieElements) ? cookieElements[name] : false;
        }
    };

    /**
     * Plugin construction
     *
     * @param (Object) arguments to provide
     * @return (Object) Results of the arguments passed into the object
     */
    $.cookie = function(options) {
        return Object.create(Cookie).init(options, this);
    };

    // Plugin defaults
    $.cookie.options = {
        name: "cookieMonster",
        value: "monmonmonmon",
        path: "/"
    };
}(jQuery, window, document));