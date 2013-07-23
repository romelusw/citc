<?php
/** 
 * Establishes a connection to a database & is capable of performing
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
     * Executes a Prepared SQL Query against the '$connection' database resource
     * 
     * @param (String) $query the statement to execute
     * @param (String) $params the paramaters to bind within the query
     * @return (mysqli_result) resultant object
     */
    public function runPreparedQuery($query, $params) {
        // Create Prepared Statement
        $pStatement = $this->connection->prepare($query);
        // Place the datatypes string in the beginning of the array
        array_unshift($params, $this->resolveTypes($params));
        // On the prepare statement, invoke 'bind_param' using 
        // the referenced parameters
        call_user_func_array(array($pStatement, 'bind_param'),
            $this->referenceArrayValues($params));

        return $pStatement->execute();
    }

    /**
     * Determines the datatypes {i => "Integer", s => "String",
     * d => "Double", b => "Blob"} for the given data parameters.
     *
     * @param (Array) $datum the data to inspect and find the datatype
     * @return (String) containing all the types found
     */
    private function resolveTypes($datum) {
        $types = '';
        foreach($datum as $para) {
            if(is_int($para)) {
                $types .= 'i';
            } elseif (is_float($para)) {
                $types .= 'd';
            } elseif (is_string($para)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
        }
        return $types;
    }

    /**
     * Creates a reference array of the values within an array
     *
     * @param (Array) $arr the array to reference
     * @return (Array) the referenced array.
     */
    private function referenceArrayValues(&$arr) {
        $refs = array();
        foreach($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        return $refs;
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
        return $this->connection->real_escape_string($datum);
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
        return $this->runQuery("SHOW TABLES LIKE '$table'")->num_rows > 0;
    }
}
