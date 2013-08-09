/**
 * Plugin for making forms into Wizard like experiences
 * Takes a HTML form element looks for all occurrences of 'fieldset' acting as
 * the dividing element to create individual 'steps' within the wizard.
 *
 * Validates form elements within each fieldset according to its type. Elements
 * requiring validation will have a class of 'required'. Which dissallows
 * progression of the wizard if the required fields are not valid.
 *
 * Multi-form support
 *
 * @author Woody Romelus
 */
;(function ($, window, document, undefined) {
    // Be a good programmer now :p
    "use strict";

    /**
     * Public methods that can be accessed.
     *
     * @type {{init: Function}}
     */
    var methods = {
        init: function (args) {
            _buildWizard(this);
        }
    };

    function _buildWizard(parent) {
        var wizard = $.fn.formWizard;
        var wizardOpt = wizard.options;
        var numSteps = $(parent).children(wizardOpt.childElem).length;

        // Store useful data onto the form
        $(parent).data({numSteps: numSteps, currStep: 0});

        // Hide the non-zeroth elements
        $(parent).find(wizardOpt.childElem + ":not(:first)").hide();

        if (wizardOpt.allowBack) {
            $(parent).append("<button data-dir='-1' type='button' class='fw_transition'>Back</button>");
        }

        // Add next/back buttons
        $(parent).append("<button data-dir='1' type='button' class='fw_transition'>Next</button>");

        // Bind Event handler for click events onto the button(s)
        $(parent).find("button.fw_transition").click(function() {
            var data = $(parent).data();
            var currStep = data.currStep;
            var numSteps = data.numSteps - 1;
            var childElms = $(parent).children(wizardOpt.childElem);
            var direction = parseInt($(this).attr("data-dir"));
            var transIn = $.fn.formWizard.options.transitionIn;
            var transOut = $.fn.formWizard.options.transitionOut;

            // Handle Validation
            if($.fn.formWizard.options.ignoreValidation) {
                // Validates form input types that contain 'requiredClass'
                // Checks for empty
                // Verifies by the type
                // TODO: Add Validation to form fields
            }

            // Update Step
            if($.fn.formWizard.options.cycleSteps) {
                var newStep = (Math.abs(currStep) + direction) % (numSteps + 1);
                $(parent).data("currStep", newStep);
                transOut.apply(childElms.eq(currStep));

//                childElms.eq(currStep).fadeOut(function () {
//                    childElms.eq(newStep).fadeIn();
//                });
            } else {
                $(parent).data("currStep", currStep + direction);
                childElms.eq(currStep).fadeOut(function () {
                    childElms.eq(currStep + direction).fadeIn();
                });
                updateStepButtons(parent);
            }
        });

        function updateStepButtons(parent) {
            var nxtBtn = $(parent).find($("button.fw_transition:contains('Next')"));
            var bckBtn = $(parent).find($("button.fw_transition:contains('Back')"));

            // Enable Buttons to start
            nxtBtn.prop("disabled", false);
            bckBtn.prop("disabled", false);

            // Update buttons
            switch ($(parent).data("currStep")) {
                case $(parent).data("numSteps") - 1:
                    nxtBtn.prop("disabled", true);
                    break;
                case 0:
                    bckBtn.prop("disabled", true);
                    break;
            }
        }
    }

    /**
     * Plugin construction.
     *
     * @param args the user options to configure to use within the plugin
     * @returns {*} result from the method invoked for the arguments given
     */
    $.fn.formWizard = function (args) {
        return this.each(function () {
            // Is the argument a plugin method?
            if (methods[args]) {
                return methods[args].apply(this, Array.prototype.slice.call(arguments));
            } else if (typeof args === 'object' || !args) {
                // Merge defaults with user specified configuration
                $.fn.formWizard.options = $.extend({}, $.fn.formWizard.options, args);
                return methods.init.call(this, args);
            } else {
                $.error(args + ": is unsupported within jQuery formWizard!");
            }
        });

        // Allow for jQuery chainability
        return this;
    };

    /**
     * Default settings for the plugin.
     *
     * @type {{allowBack: boolean, cycleSteps: boolean,
     *         displayStepPosition: boolean, ignoreValidation: boolean,
     *         requiredClass: string, showErrors: boolean,
     *         validateSteps: boolean}}
     */
    $.fn.formWizard.options = {
        allowBack: false,
        cycleSteps: false,
        childElem: "fieldset",
        ignoreValidation: false,
        requiredClass: "required",
        showBreadCrumb: true,
        showErrors: true,
        transitionIn: "fadeIn",
        transitionOut: "fadeOut"
    };
}(jQuery, window, document));