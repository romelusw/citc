<?php
// A Salt value for hashing
define("salt", "li:1or9_em8ip(s6um");

/**
 * A utility class that provides common functionality amongst classes.
 *
 * @author Woody Romelus
 */
class Utils {

    /**
     * Logs messages to a file.
     * The default location of the log file is: "romelus_debug.log" in the same 
     * directory as the file that is invoking the method.
     * If the file is not already created, the file will be created.
     *
     * @param (String) $message The message to write to file.
     * @param (String) $debug_file_location The location where the file should 
     *                 exist. 
     */
    public static function logMessage($message, $debug_file_location =
        "romelus_debug.log") {
        if(!file_exists($debug_file_location)) {
            fopen($debug_file_location, "a");
            fclose($debug_file_location);
        }
        error_log(date('Y/m/d h:i:s A') . " :: $message \r\n", 3, $debug_file_location); 
    }

    /**
     * Redirects the HTTP header to another location.
     *
     * @param (String) $address the new location to send the browser.
     */
    public static function redirect($address) {
        header("Location: $address");
        exit();
    }

    /**
     * Prints out code content, in a pretty format.
     *
     * @param (String) $content the text to print
     */
    public static function printCode($content) {
        echo "<pre>" . $content . "</pre>"; 
    }

    /**
     * Hashes a password using a salt for more protection.
     *
     * @param (String) $pass the password to hash
     * @return (String) the hashed password
     */
    public static function hashPassword($pass) {
        return hash("sha1", $pass . salt);
    }

    /**
     * Generates a unique key
     *
     * @param (String) $content the text used to create a unique key with
     * @return (String) the unique key
     */
    public static function genUniqKey($content) {
        return md5($content ."_". uniqid() ."_". salt);
    }

    /**
     * Normalizes user input into a valid format.
     *
     * @param (String) $text the string to normalize
     * @return (String) the normalized string
     */
    public static function normalize($text) {
        return htmlspecialchars(trim(strtolower($text)));
    }

    /**
     * Compares two strings for equality, ignoring case
     *
     * @param (String) $str1 string to compare
     * @param (String) $str2 string to compare
     * @return (Boolean) flag indicating equality check
     */
    function equalIgnoreCase($str1, $str2) {
        return strcasecmp($str1, $str2) == 0;
    }

    /**
     * Retrieves the info for a URL Request
     *
     * @return (Array) containing the prevalent info of the requested URL
     */
    public static function retrieveRequestInfo() {
        parse_str(parse_url($_SERVER["REQUEST_URI"])["query"], $result);
        $result["method"] = $_SERVER['REQUEST_METHOD'];
        $result["uri"] = $_SERVER["REQUEST_URI"];
        return $result;
    }
}
