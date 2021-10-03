<?php

/*
 *
 * 
 *          this class's only purpose is to implement static functions for accessing the global database object
 * 
 * 
 */

namespace MattKurek\AthenaPHP;

class DB {





    /*
     *
     *      Runs a Delete query through the global database
     * 
     */
    public static function delete($sql, $parameters) {

        try {

            return $GLOBALS['_ATHENA_DATABASE_']->delete($sql, $parameters);

        } catch (\Exception $e) {

            // log any unexpected errors which may occur and return an error response
            error_log($e);

        }

    }





    /*
     *
     *      Runs a Delete query through the global database
     * 
     */
    public static function insert($sql, $parameters) {

        try {

            return $GLOBALS['_ATHENA_DATABASE_']->insert($sql, $parameters);

        } catch (\Exception $e) {

            // log any unexpected errors which may occur and return an error response
            error_log($e);

        }

    }





    /*
     *
     *      Returns the ID of the most recently inserted data entry in the global database
     * 
     */
    public static function InsertID() {

        try {

            return $GLOBALS['_ATHENA_DATABASE_']->insert_id;

        } catch (\Exception $e) {

            // log any unexpected errors which may occur and return an error response
            error_log($e);

        }

    }





    /*
     *
     *      Runs a Select query through the global database
     * 
     */
    public static function select($sql, $parameters = false) {

        try {

            return $GLOBALS['_ATHENA_DATABASE_']->select($sql, $parameters);

        } catch (\Exception $e) {

            // log any unexpected errors which may occur and return an error response
            error_log($e);

        }

    }





    /*
     *
     *      Runs an Update query through the global database
     * 
     */
    public static function update($sql, $parameters) {

        try {

            return $GLOBALS['_ATHENA_DATABASE_']->update($sql, $parameters);

        } catch (\Exception $e) {

            // log any unexpected errors which may occur and return an error response
            error_log($e);

        }

    }



}