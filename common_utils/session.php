<?php
/**
 * An object providing support for SESSION creation/managing.
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
        $httponly = false; // Dissallow javascript from accessing the session. 
        $cookieParams = session_get_cookie_params(); // Gets current cookies params.
        session_set_cookie_params($cookieParams["lifetime"],
            $cookieParams["path"], $cookieParams["domain"], false, $httponly); 

        session_name($name);
        session_start();

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
