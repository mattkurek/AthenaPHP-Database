<?php

/*  
 *                              
 * ~~~ Class Methods
 *      
 *      - All functions have try-catches for error handling
 *      - All error responses contain a unique error code
 * 
 *     
 *          private function            Bind ($parameters)
 *          private function            Connect ()
 *          private static function     ConvertInputTypeToMySQLiType ($input_type)
 *          public function             Delete ($sql, $parameters)
 *          public function             Insert ($sql, $parameters)
 *          public function             Select ($sql, $parameters = false)
 *          public function             Update ($sql, $parameters)
 * 
 *  
 * 
 * 
 * 
 */

namespace MattKurek\AthenaPHP;

$GLOBALS['_ATHENA_DATABASE_'] = new Database();

class Database {

    // environmental variables that hold the database credentials
    const _HOST = 'ATHENA_MAIN_HOST';
    const _USER = 'ATHENA_MAIN_USER';
    const _PASS = 'ATHENA_MAIN_PASS';
    const _database = 'ATHENA_MAIN_DATABASE';

    private static $_charset = 'utf8mb4';

    // variables for sql connection objects
    private $mysqli = null;
    private $statement = null;
    private $result = null;
    private $connected = false;

    public ?int $number_deleted = null; // set after a DELETE query is ran
    public ?int $number_updated = null; // set after an UPDATE query is ran
    public ?int $insert_id = null; // set after an INSERT query is ran

    public $error_message = ''; // set default state

    // static functions for reading the database credentials from the environment variables
    private function _database() { 

        try {

            return getenv(static::_database); 

        } catch (\Exception $e) {

            // log any unexpected errors that may occur and return an error response
            error_log($e);

        }

    }

    private function _host() { 

        try {

            return getenv(static::_HOST); 

        } catch (\Exception $e) {

            // log any unexpected errors that may occur and return an error response
            error_log($e);

        }

    }

    private function _password() { 

        try {

            return getenv(static::_PASS); 

        } catch (\Exception $e) {

            // log any unexpected errors that may occur and return an error response
            error_log($e);

        }

    }

    private function _username() { 

        try {

            return getenv(static::_USER); 

        } catch (\Exception $e) {

            // log any unexpected errors that may occur and return an error response
            error_log($e);

        }

    }




     
    /*
     *
     *          Construct
     * 
     */
    public function __construct() {

        try {

            // initiate the database connection
            $this->connect();

        } catch (\Exception $e) {

            // log any unexpected errors which may occur and return an error response message
            error_log($e);

        }

    }



    public function __destruct() {

        try {

            // first close the connection between this server and the sql server
            $this->closeConnection();

            // set all property to null to minimize any potential memory usage
            foreach ($this as $key => $value) {
                $this->$key= null;
                unset($this->key);
            }
            unset($key);
            unset($value);

        } catch (\Exception $e) {

            // log any unexpected errors which may occur and return an error response message
            error_log($e);

        }

    }



    /*
     *
     *          Function that Binds the Input Parameters to the SQL Statement
     * 
     */
    private function bind($parameters) {

        // parameters should be array of arrays
        // [ [ value , type ] , [ $fred , 'string' ] ]

        try {

            $paramaters_array = array();
            $data_types = "";
    
            foreach ($parameters as $parameter) {
    
                // convert the boilerplate input to the proper inputs for binding
                $value = $parameter[0];
                $type = self::convertDataType($parameter[1]);            
    
                $paramaters_array[] = $value;
                $data_types .= $type;
    
            }
            // clean up memory
            unset($parameter);
            unset($value);
            unset($type);
    
        } catch (\Exception $e) {

            // log any unexpected errors which may occur and return an error response message
            error_log($e);

        }

        try {

            // try to bind the parameters
            $this->statement->bind_param($data_types, ...$paramaters_array);
            return true;

        } catch (\Exception $e) {

            // if it fails, record it to the error log, set an error message, and return false
            error_log($e);

        }

    }





    /*
     *
     *          Function to initiate database connection
     * 
     */
    private function connect() {


        try {

            // verify that the connection isn't already established
            if ($this->connected == true) {
                return;
            }

            // set the error reporting mode
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            // attempt to create a new database connection
            $this->mysqli = new \mysqli($this->_host(), $this->_username(), $this->_password(), $this->_database());
            $this->mysqli->set_charset(self::$_charset);

        } catch(\Exception $e) {

            // record the error info in the server's error log and display a kill message to the user
            error_log($e->getMessage());

        }


    }

    


    /*
     *
     *          Function to close the database connection
     * 
     */
    private function closeConnection() {


        try {

            // verify that the connection isn't already closed
            if ($this->connected == false) {
                return;
            }

            // close the database connection
            if ($this->mysqli->close() == true) {

                // set the object property for tracking connection status
                $this->connected = false;

                // 


            } else {
                error_log("Database ::: closeConnection() ::: Failed to close the connection");
            }

        } catch(\Exception $e) {

            // record the error info in the server's error log and display a kill message to the user
            error_log($e->getMessage());

        }


    }




    private function checkConnectionStatus() {

        try {

            // if there is currently no connection, initiate one
            if ($this->connected == false) {
                $this->connect();
            }

        } catch (\Exception $e) {

        }


    }





    /*
     *
     *          Function to Convert the 'data type' from the user input to the proper type for mysqli parameter binding
     * 
     */
    private static function convertDataType($input_type) {

        try {

            switch($input_type) {
                // support for fully typed out names for types
                case('blob'): return 'b';
                case('double'): return 'd';
                case('int'): return 'i';
                case('integer'): return 'i';
                case('string'): return 's';
                case('text'): return 's';
                // support for default types
                case('b'): return 'b';
                case('d'): return 'd';
                case('i'): return 'i';
                case('s'): return 's';
                // in case somethings not in this list by some odd chance
                default: return $input_type;
            }

        } catch (\Exception $e) {

            error_log($e);

        }


    }





    /*
     *
     *          Function for Deleting Any Number of Rows From Database
     * 
     */
    public function delete($sql, $parameters) {

        try {

            // verify that an sql database connection has been established
            $this->checkConnectionStatus();

            // prepare the statement and bind parameters
            $this->statement = $this->mysqli->prepare($sql);
            $this->bind($parameters);

            // execute the query and get results
            $this->statement->execute();
            $this->result = $this->statement->get_result();

        } catch (\Exception $e) {

            // if the database query failed, save the error to the server's error log
            error_log($e);

        }

        try {

            // if no rows were deleted, set error and return false
            if ($this->statement->affected_rows === 0) {
                $this->error_message = 'NO_ROWS_DELETED';
                return false;
            } 

            // set number of affected rows
            $this->number_deleted = $this->statement->affected_rows;

            // free memory and return true
            $this->statement->close();
            return true;

        } catch (\Exception $e) {

            // if the database query failed, save the error to the server's error log
            error_log($e);

        }



    }





    /*
     *
     *          Function for inserting a Single Row Into Database
     * 
     */
    public function insert($sql, $parameters) {

        try {

            // verify that an sql database connection has been established
            $this->checkConnectionStatus();

            // prepare the statement and bind parameters
            $this->statement = $this->mysqli->prepare($sql);
            $this->bind($parameters);

            // execute the query and get results
            $this->statement->execute();
            $this->result = $this->statement->get_result();

        } catch (\Exception $e) {

            // if the database query failed, save the error to the server's error log
            error_log($e);

        }

        try {

            // if no rows were inserted, set error and return false
            if ($this->statement->affected_rows === 0) {
                $this->error_message = 'NO_ROWS_INSERTED';
                return false;
            } 

            // set the id for the last inserted row
            $this->insert_id = $this->mysqli->insert_id;

            // free memory and return true
            $this->statement->close();
            return true;

        } catch (\Exception $e) {

            // if the database query failed, save the error to the server's error log
            error_log($e);

        }


    }





    /*
     *
     *          Function For selecting Any Number of Rows From Database
     * 
     */
    public function select($sql, $parameters = false) {

        try {

            // verify that an sql database connection has been established
            $this->checkConnectionStatus();

            // prepare the statement and bind parameters
            $this->statement = $this->mysqli->prepare($sql);

            if (!is_bool($parameters)) {
                $this->bind($parameters);
            }

            // execute the query and get results
            $this->statement->execute();
            $this->result = $this->statement->get_result();

        } catch (\Exception $e) {

            // if the database query failed, save the error to the server's error log
            error_log($e);

        }

        try {

            // extract the data from the results
            $final_results = [];
            while($row = $this->result->fetch_assoc()) {
                $final_results[] = $row;
            }

            // if no results were found, set the error state and message
            if (sizeof($final_results) == 0) {
                $this->error_message = 'NO_RESULTS';
                return false;
            }

            // close the statement, free the memory, and return the final results
            $this->statement->close();
            return $final_results;

        } catch (\Exception $e) {

            // if the database query failed, save the error to the server's error log
            error_log($e);

        }

        


    }





    /*
     *
     *          Function to Update 
     * 
     */
    public function update($sql, $parameters) {

        try {

            // verify that an sql database connection has been established
            $this->checkConnectionStatus();

            // prepare the statement and bind parameters
            $this->statement = $this->mysqli->prepare($sql);
            $this->bind($parameters);

            // execute the query and get results
            $this->statement->execute();
            $this->result = $this->statement->get_result();

        } catch (\Exception $e) {

            // if the database query failed, save the error to the server's error log
            error_log($e);

        }

        try {

            // if no rows were updated, set error and return false
            if ($this->statement->affected_rows === 0) {
                $this->error_message = 'NO_ROWS_UPDATED';
                return false;
            } 

            // set number of affected rows
            $this->number_updated = $this->statement->affected_rows;

            // free memory and return true
            $this->statement->close();
            return true;

        } catch (\Exception $e) {

            // if the database query failed, save the error to the server's error log
            error_log($e);

        }



    }





}
