<?php
/**
 * Session object.
 *
 * @author Woody Romelus
 */
class Session {

    /**
     * Default Constructor.
     *
     * @param $sessionCookieName the name of the session identifier
     */
    function __construct($sessionCookieName) {
        // Session Cookie
        $httponly = true; // Disallow javascript from accessing the session cookies.
        $cookieParams = session_get_cookie_params(); // Gets current cookies params.
        session_set_cookie_params($cookieParams["lifetime"],
            $cookieParams["path"], $cookieParams["domain"], false, $httponly);

        session_name($sessionCookieName);
        session_start();

        if (!isset($_SESSION["created"])) {
            $_SESSION["created"] = date("Y-m-d h:i:s", time());
        }
    }

    /**
     * Setter for the session properties.
     *
     * @param $property  the property to set
     * @param $val the value of the property to set
     */
    function __set($property, $val) {
        $_SESSION[$property] = $val;
    }

    /**
     * Getter for session properties.
     *
     * @param $property the property to retrieve
     * @return mixed the value of the property
     */
    function __get($property) {
        return $_SESSION[$property];
    }
}