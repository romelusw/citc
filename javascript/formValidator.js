/**
 * An object capable of validating form elements according to its type.
 *
 * @author Woody Romelus
 */
function FormValidator() {
    // Be a good programmer now :p
    "use strict";

    FormValidator.prototype.supportedTypes = ["text", "email", "tel"];
};

/**
 * Function description
 *
 * @param (Type) Paramater description
 * @return (Type) Return description
 */
FormValidator.prototype.validate = function(field) {
    var retVal;
    var fieldType = $(field).attr("type");

    // Limit validation to supported types
    if(FormValidator.prototype.supportedTypes.indexOf(fieldType) != -1) {

        // Validate appropriately by type
        switch(fieldType) {
            case "text":
                retVal = validateTextField($(field).val());
            break;

            case "email":
                retVal = validateEmailField($(field).val());
            break;

            case "tel":
                retVal = validatePhoneField($(field).val());
            break;
        }
    }
    return retVal;
}

/**
 * Verifies the correctness of form text fields
 *
 * @param (string) the text input value
 * @return (boolean) indicating if the field qualifies
 */
function validateTextField(fieldValue) {
    return isNonEmpty(fieldValue);
}

/**
 * Verifies the correctness of form email fields
 *
 * @param (string) the email field input value
 * @return (boolean) indicating if the field qualifies
 */
function validateEmailField(fieldValue) {
    return isNonEmpty(fieldValue);
}

/**
 * Verifies the correctness of form phone fields
 *
 * @param (string) the phone field input value
 * @return (boolean) indicating if the field qualifies
 */
function validatePhoneField(fieldValue) {
    return isNonEmpty(fieldValue);
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