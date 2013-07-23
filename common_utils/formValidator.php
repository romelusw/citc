<?php
define("EMPTY", "%s cannot be empty. Please fill in.<br/>");
define("LENGTH", "Password must be at least '8' characters long.<br/>");
define("NUMBER", "Password must contain at least one numeric value.<br/>");
define("SYMBOL", "Password must contain at least one symbol.<br/>");
define("unsupported", "%s is not supported for validation.");

/**
 * A object capable of handling the validation of form input types
 *
 * @author Woody Romelus
 */
class FormValidator {

    // Contains all error messages
    private $errMsg = array(); 

    /**
     * Loops the input validating each entry
     *
     * @param (Array) $entries inputs to validate with their respective types
     * @return (Boolean) Flag indicating if any errors were found
     */
    public function validate($entries) {
        foreach($entries as $fieldName => $fieldData) {
            $this->findTypeThenValidate($fieldName, $fieldData);
        }
        return count($this->errMsg) == 0;
    }

    /**
     * Finds the appropriate method to validate each form input type.
     *
     * @param (String) $fieldTitle the field name
     * @param (Array) $data the field data
     */
    private function findTypeThenValidate($fieldTitle, $data) {
        $key = strtolower(key($data));

        switch ($key) {
            case "email":
                $this->validateEmail($fieldTitle, $data[$key]);
                break;
            case "pass":
                $this->validatePassword($fieldTitle, $data[$key]);
                break;  
            case "non_empty_text":
                $this->validateNonEmptyText($fieldTitle, $data[$key]);
                break;
            default:
                error_log(sprintf(constant("unsupported"), $key));
        }
    }

    /**
     * A function to verify/validate non empty text fields
     *
     * @param (String) $field the field title.
     * @param (String) $name the name to validate.
     */
    private function validateNonEmptyText($field, $name) {
        if(strlen($name) == 0) {
            $this->setErrMsg($field, sprintf(constant("EMPTY"), $field));
        }
    }

    /**
     * A function to verify/validate passwords
     * RULES:
     *  * Must contain one numeric character
     *  * Must contain one symbol
     *  * Must be at least 8 characters long
     *
     * @param (String) $field the field title.
     * @param (String) $pass the password to validate.
     */
    private function validatePassword($field, $pass) {
        if(strlen($pass) == 0 || strlen($pass) < 8) {
            $this->setErrMsg($field, sprintf(constant("LENGTH"), $field));
        }
        
        if(!preg_match('@[0-9]@', $pass)) {
            $this->setErrMsg($field, $this->errMsg[$field] . constant("NUMBER"));
        }

        if(!preg_match("/[^A-Za-z0-9]+/", $pass)) {
            $this->setErrMsg($field, $this->errMsg[$field] . constant("SYMBOL"));
        }
    }

    /**
     * A function to verify/validate email addresses
     *
     * @param (String) $field the field title.
     * @param (String) $email the email to validate.
     */
    private function validateEmail($field, $email) {
        if(strlen($email) == 0) {
            $this->setErrMsg($field, sprintf(constant("EMPTY"), $field));
        } else if(!preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i', $email)) {
            $this->setErrMsg($field, "Invalid Email Address");
        }
    }

    /**
     * Assigns values to the error array based on its field name.
     *
     * @param (String) $field the field title.
     * @param (String) $val the value to assign for the field.
     */
    private function setErrMsg($field, $val) {
        $this->errMsg[$field] = $val; 
    }

    /**
     * Retrieves all messages from the error array.
     *
     * @return (Array) the errors generated.
     */
    public function getErrors() {
        return $this->errMsg;
    }
}
