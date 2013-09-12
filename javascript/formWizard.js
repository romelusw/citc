/**
 * Plugin for making forms into Wizard like experiences
 * Takes a HTML form element looks for all occurrences of 'fieldset' acting as
 * the dividing element to create individual 'steps' within the wizard.
 *
 * Validates form elements within each fieldset according to its type. Elements
 * requiring validation will have a class of 'required'. Which disallows
 * progression of the wizard if the required fields are not filled and/or valid.
 *
 * @author Woody Romelus
 */;
(function ($, window, document, undefined) {
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

    /**
     * Builds the wizard for the form element
     *
     * @param formCtx the form context
     */
    function _buildWizard(formCtx) {
        // Validate we are working on form elements
        if (!$(formCtx).is("form")) {
            throw "Cannot build form wizard, because element is not a form!";
        }

        var wizard = $.fn.formWizard;
        var wizardOpt = wizard.options;
        var numSteps = $(formCtx).children(wizardOpt.transitionField).length - 1;

        // Store useful data into the form
        $(formCtx).data({
            numSteps: numSteps,
            currStep: 0
        });

        // Hide the non-zeroth(all elements aside from the first) elements
        $(formCtx).find(wizardOpt.transitionField + ":not(:first)").hide();

        // HTML Container
        var wizardHTML = $("<div></div>").attr('class', 'formWizard');

        // Add next/back buttons
        if (wizardOpt.allowBack) {
            $(wizardHTML).append("<button data-dir='-1' class='fw_transition\
                fw_back'><i class='icon-chevron-sign-left'></i></button>");
        }
        $(wizardHTML).append("<button data-dir='1' class='fw_transition\
            fw_next'><i class='icon-chevron-sign-right'></i></button>");
        $(formCtx).append(wizardHTML);

        _bindClickHandler(formCtx);
    }

    /**
     * Binds a event handler for the form buttons
     *
     * @param formCtx the form context
     */
    function _bindClickHandler(formCtx) {
        // Initially set the button states
        _updateStepButtons(formCtx);

        // Bind Event handler for click events onto the button(s)
        $(formCtx).find(".formWizard button.fw_transition").click(function(evt) {
            evt.preventDefault();
            var data = $(formCtx).data();
            var currStep = data.currStep;
            var numSteps = data.numSteps;
            var childElms = $(formCtx).children($.fn.formWizard.options.transitionField);
            var direction = parseInt($(this).attr("data-dir"));

            // Validate form elements
            if ($.fn.formWizard.options.ignoreValidation) {
                // Validates form input types that contain 'validateClass'
                // Checks for empty
                // Verifies by the type
                // TODO: Add Validation to form fields
            }

            // Update Step
            if ($.fn.formWizard.options.cycleSteps) {
                var newStep = (Math.abs(currStep) + direction) % (numSteps + 1);
                $(formCtx).data("currStep", newStep);

                childElms.eq(currStep).fadeOut(function () {
                    childElms.eq(newStep).fadeIn();
                });
            } else {
                $(formCtx).data("currStep", currStep + direction);

                childElms.eq(currStep).fadeOut(function () {
                    childElms.eq(currStep + direction).fadeIn();
                });
                _updateStepButtons(formCtx);
            }
        });
    }

    /**
     * Changes the state of the transition buttons based on the current step
     * 
     * @param formCtx the form context
     */
    function _updateStepButtons(formCtx) {
        var nxtBtn = $(formCtx).find($("button.fw_next"));
        var bckBtn = $(formCtx).find($("button.fw_back"));

        // Enable Buttons to start
        nxtBtn.prop("disabled", false);
        bckBtn.prop("disabled", false);

        // Update buttons
        switch ($(formCtx).data("currStep")) {
            // The last transition field
            case $(formCtx).data("numSteps"):
                nxtBtn.prop("disabled", true);
                if ($.fn.formWizard.options.hideDisabledStep) $(nxtBtn).hide();
                break;
            // The first transition field
            case 0:
                bckBtn.prop("disabled", true);
                if ($.fn.formWizard.options.hideDisabledStep) $(bckBtn).hide();
                break;
            default:
                $(nxtBtn).show();
                $(bckBtn).show();
                break;
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
        transitionField: "fieldset",
        ignoreValidation: false,
        hideDisabledStep: true,
        validateClass: "validate",
        showBreadCrumb: true,
        showErrors: true
    };
}(jQuery, window, document));