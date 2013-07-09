<?php
/**
 * An object providing support for SESSION creation and handling.
 *
 * @author Woody Romelus
 */
class Session {

    /**
     * Default Constructor.
     *
     * @param (String) $name the name of the session identifier 
     */
    function __construct($name) {
        // Session Cookie
        $httponly = true; // Stop javascript from accessing the session id. 
        $cookieParams = session_get_cookie_params(); // Gets current cookies params.
        session_set_cookie_params($cookieParams["lifetime"],
            $cookieParams["path"], $cookieParams["domain"], false, $httponly); 

        session_name($name); // Session name changed
        session_start(); // Start or resume a session

        if (!isset($_SESSION["created"])) {
            $_SESSION["created"] = date("Y-m-d h:i:s", time());
        }
    }

    /**
     * Setter for the session properties.
     *
     * @param (String) $prop the property to set.
     * @param (String) $val the value to assign with the property.
     */
    function __set($prop, $val) {
        $_SESSION[$prop] = $val; 
    }

    /**
     * Getter for session properties.
     *
     * @param (String) $prop the property to retrieve.
     * @return (String) the value of the session property
     */
    function __get($prop) {
        return $_SESSION[$prop]; 
    }
}
