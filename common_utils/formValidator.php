<?php
define("EMPTY", "%s cannot be empty. Please fill in.");
define("LENGTH", "Password must be at least '8' characters long.");
define("NUMBER", "Password must contain at least one numeric value.");
define("SYMBOL", "Password must contain at least one symbol.");
define("unsupported", "%s is not supported for validation.");

/**
 * A validation object for HTML form input types.
 *
 * @author Woody Romelus
 */
class FormValidator {

    // Contains all error messages
    private $errorMessages = array();

    /**
     * Loops the input validating each field.
     *
     * @param $fields inputs to validate
     * @return bool indicating if any errors were found
     */
    public function validate($fields) {
        foreach ($fields as $fieldName => $fieldValue) {
            $this->validateField($fieldName, $fieldValue);
        }
        return count($this->errorMessages) == 0;
    }

    /**
     * Finds the appropriate method to validate each form input type.
     *
     * @param $fieldTitle the field name
     * @param $fieldValue the field data
     */
    private function validateField($fieldTitle, $fieldValue) {
        $key = strtolower(key($fieldValue));

        switch ($key) {
            case "email":
                $this->validateEmail($fieldTitle, $fieldValue[$key]);
                break;
            case "pass":
            case "password":
                $this->validatePassword($fieldTitle, $fieldValue[$key]);
                break;
            case "non_empty_text":
            case "net":
                $this->validateNonEmptyText($fieldTitle, $fieldValue[$key]);
                break;
            default:
                error_log(sprintf(constant("unsupported"), $key));
        }
    }

    /**
     * Verifies/validates non empty text fields.
     *
     * @param $field the field title
     * @param $data the data of the field
     */
    private function validateNonEmptyText($field, $data) {
        if (strlen($data) == 0) {
            $this->setErrorMessages($field, sprintf(constant("EMPTY"), $field));
        }
    }

    /**
     * Verifies/validates password fields.
     * RULES:
     *  - Must contain one numeric character
     *  - Must contain one symbol
     *  - Must be at least 8 characters long
     * @param $field the field title
     * @param $pass the password to validate
     */
    private function validatePassword($field, $pass) {
        // Check for length requirements
        if (strlen($pass) == 0 || strlen($pass) < 8) {
            $this->setErrorMessages($field, sprintf(constant("LENGTH"), $field));
        }

        // Check for a numeric
        if (!preg_match('@[0-9]@', $pass)) {
            $this->setErrorMessages($field, $this->errorMessages[$field]
                . constant("NUMBER"));
        }

        // Check for symbols
        if (!preg_match("/[^A-Za-z0-9]+/", $pass)) {
            $this->setErrorMessages($field, $this->errorMessages[$field]
                . constant("SYMBOL"));
        }
    }

    /**
     * Verifies/validates email addresses.
     *
     * @param $field the field title
     * @param $email the email to validate
     */
    private function validateEmail($field, $email) {
        $emailRegex = "/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i";

        if (strlen($email) == 0) {
            $this->setErrorMessages($field, sprintf(constant("EMPTY"), $field));
        } else if (!preg_match($emailRegex, $email)) {
            $this->setErrorMessages($field, "Invalid Email Address");
        }
    }

    /**
     * Sets the error messages for specific input fields.
     *
     * @param $fieldName the field title
     * @param $message the error message to set
     */
    private function setErrorMessages($fieldName, $message) {
        $this->errorMessages[$fieldName] = $message . "<br>";
    }

    /**
     * Retrieves all messages from the error array.
     *
     * @return array the errors generated
     */
    public function getErrors() {
        return $this->errorMessages;
    }
}