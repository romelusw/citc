<?php
/** 
 * Establishes a connection to a database, and is capable of performing
 * transactions against it.
 *
 * @author Woody Romelus
 */
class DatabaseConnector {

    public $connection;

    /**
     * Establishes a connection to the database.
     *
     * @param (String) $host the IP address or hostname of the DB server
     * @param (String) $username the username
     * @param (String) $passwd the password
     * @param (String) $db the database to use
     */ 
    public function connect($host, $username, $password, $db = null) {
        $this->connection = new mysqli($host, $username, $password, $db);

        if(!$this->connection) {
            die("Could not connect to database:" . $this->connection->connect_error());
        }
    }

    /**
     * Executes a SQL Query against the '$connection' database resource
     * 
     * @param (String) $query the statement to execute
     * @return (mysqli_result) resultant object
     */
    public function runQuery($query) {
        return $result = $this->connection->query($query);
    }

    /**
     * Closes the database connection.
     *
     * @return (Boolean) flag depending if it was successful or not.
     */
    public function close() {
        return mysqli_close($this->connection);
    }

    /**
     * Protects against MySQL injection by escaping special
     * characters known within the MySQL charset
     *
     * @param (String) $datum the data to sanitize
     * @return (Array) the espcaped string
     */
    public function cleanSQLInputs($datum) {
        $value = $this->connection->real_escape_string($datum);
        return $value;
    }

    /** 
     * Retrieves the property values from the connection object
     *
     * @param (String) $prop the property to retrieve
     * @return (String) the attribute value
     */
    public function __get($prop) {
        return $this->connection->$prop;
    }

    /**
     * Checks if a database table exists.
     *
     * @param (String) $table the table to look for
     * @return (Boolean) flag indicating if table was found
     */
    public function table_exists($table){
        $res = $this->runQuery("SHOW TABLES LIKE '$table'");
        return ($res->num_rows > 0);
    }
}
