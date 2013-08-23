/**
 * Plugin for creating/managing cookies
 *
 * @author Woody Romelus
 */
;(function($, window, document, undefined) {
    // Be a good programmer now :p
    "use strict";

    /**
     * Plugin construction.
     *
     * @param args the user options to configure to use within the plugin
     * @returns {*} result from the method invoked for the arguments given
     */
    $.cookie = function(args) {
        return Object.create(Cookie).init(args);
    };

    /**
     * Default settings for the plugin.
     *
     * @type {{name: string, value: string, path: string}}
     */
    $.cookie.options = {
        name: "cookieMonster",
        value: "monmonmonmon",
        path: "/"
    };

    /**
     * Wrapper object for "cookies".
     *
     * @type {{init: Function, _displayCookies: Function,
     *         _createCookie: Function, _contains: Function}}
     */
    var Cookie = {
        /**
         * Executes the method pertinent to the parameter.
         *
         * @param args the user options
         * @returns {*} result from the method invoked for the arguments given
         */
        init: function(args) {
            var result;

            switch (typeof(args)) {
                case "object":
                    // Merge the user options with defaults
                    $.cookie.options = $.extend({}, $.cookie.options, args);
                    result = this._createCookie();
                break;
                case "undefined":
                    result = this._displayCookies();
                break;
                case "string":
                    result = this._contains(args);
                break;
                default:
                    $.error("Argument not supported!");
                break;
            }
            return result;
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
         * Displays the cookies for the current domain.
         *
         * @returns {string} representation of all the cookies
         */
        _displayCookies: function() {
            var retVal = "";
            var cookies = document.cookie.split(";");

            if(cookies == "") {
                retVal = "No cookies set.";
            } else {
                cookies.forEach(function(elem) {
                    retVal += elem.split("=");
                });
            }
            return retVal;
        },

        /**
         * Determines if a cookie exists.
         *
         * @param name the cookie name to look for
         * @returns {boolean} indicating if the cookie exists
         */
        _contains: function(name) {
            var cookies = document.cookie.split(";");
            var cookieElements = {};

            cookies.forEach(function(elem) {
                var parts = elem.trim().split("=");
                cookieElements[parts[0]] = parts[1];
            });
            return (name in cookieElements);
        }
    };
}(jQuery, window, document));