/**
 * An object capable of validating form elements according to its type.
 *
 * @author Woody Romelus
 */
function FormValidator() {
    // Be a good programmer now :p
    "use strict";

    FormValidator.prototype.validate = validateField;
}

/**
 * Function description
 *
 * @param field Parameter description
 * @return (Type) Return description
 */
function validateField(field) {
    var retVal;
    var htmlTag = $(field).get(0).tagName.toLowerCase();

    // Limit validation to supported types
    if (htmlTag == "input") {
        var fieldType = $(field).attr("type");

        // Validate appropriately by type
        switch (fieldType) {
            case "text":
            case "hidden":
                retVal = validateTextField($(field).val());
                break;

            case "email":
                retVal = validateEmailField($(field).val());
                break;

            case "tel":
                retVal = validatePhoneField($(field).val(),
                    $(field).attr("pattern"));
                break;

            default:
                throw "Unsupported form input field type. [" + fieldType + "]";
                break;
        }
    } else if (htmlTag == "select") {
        retVal = validateSelectField(field);
    }
    return retVal;
}

/**
 * Verifies the correctness of form text fields
 *
 * @param fieldValue the text input value
 * @return (boolean) indicating if the field qualifies
 */
function validateTextField(fieldValue) {
    return isNonEmpty(fieldValue);
}

/**
 * Verifies the correctness of form email fields
 *
 * @param fieldValue the email field input value
 * @return (boolean) indicating if the field qualifies
 */
function validateEmailField(fieldValue) {
    var regExp = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
    return isNonEmpty(fieldValue) && fieldValue.match(regExp);
}

/**
 * Verifies the correctness of form phone fields
 *
 * @param fieldValue the phone field input value
 * @param phonePattern the phone pattern to search match against
 * @return (boolean) indicating if the field qualifies
 */
function validatePhoneField(fieldValue, phonePattern) {
    return isNonEmpty(fieldValue) && fieldValue.match(phonePattern);
}

/**
 * Verifies that an option from a select field has been chosen
 *
 * @param selectedField the select html element
 * @return (boolean) indicating if the field qualifies
 */
function validateSelectField(selectedField) {
    return $(selectedField).find("option:not(:disabled)").filter(":selected").size() == 1;
}

/**
 * Determines if a string is empty
 *
 * @param (string) the string to check
 * @return (boolean) indicating if the string is empty or not
 */
function isNonEmpty(string) {
    return string.length != 0;
}