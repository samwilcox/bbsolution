<?php

/**
 * bbSolution
 * 
 * @package   bbSolution PHP Bulletin Board System
 * @author    Sam Wilcox <sam@bb-solution.org>
 * @copyright 2014 bbSolution. All Rights Reserved.
 * @license   http://www.bb-solution.org/license.php
 * @version   CSV: $Id:$
 */

// No hacking attempts are allowed
if ( ! defined( 'BBS' ) )
{
    echo '<h1>Access Denied</h1>You are not allowed to access this file directly.';
    exit();
}

/**
 * BBSolutionDatabase
 * 
 * @package   bbSolution PHP Bulletin Board System
 * @author    Sam Wilcox <sam@bb-solution.org>
 * @copyright 2014 bbSolution. All Rights Reserved.
 * @version   CSV: $Id:$
 * @access    public
 */
class BBSolutionDatabase
{
    /**
     * Master Class Object 
     * @var Object $BBS
     */
     public $BBS;
     
    /**
     * Database Hostname
     * @var String $_db_host
     */
     private $_db_host = 'localhost';
     
    /**
     * Database Port
     * @var Integer $_db_port
     */
     private $_db_port = 3306;
     
    /**
     * Database Name
     * @var String $_db_name
     */
     private $_db_name;
     
    /**
     * Database Username
     * @var String $_db_username
     */
     private $_db_username = 'root';
     
    /**
     * Database Password
     * @var String $_db_password
     */
     private $_db_password;
     
    /**
     * Persistant Connection
     * @var Boolean $_db_persistant
     */
     private $_db_persistant = false;
     
    /**
     * Debug Mode Enabled/Disabled
     * @var Boolean $_db_debug_mode
     */
     private $_db_debug_mode = false;
     
    /**
     * Debug Logs Directory Path
     * @var String $_db_debug_dir
     */
     private $_db_debug_dir = 'logs/db';
     
    /**
     * Database Handle Object
     * @var Object $_db_handle
     */
     private $_db_handle;
     
    /**
     * Last SQL Query Statement
     * @var String $_db_last_query
     */
     private $_db_last_query;
     
    /**
     * Error Message
     * @var String $_db_error_message
     */
     private $_db_error_message;
     
    /**
     * Error Number
     * @var Integer $_db_error_number
     */
     private $_db_error_number;
     
    /**
     * Total SQL Queries
     * @var Integer $db_total_queries
     */
     private $db_total_queries = 0;
     
    
    /**
     * BBSolutionDatabase::db_set_hostname()
     * 
     * @param mixed $v
     * @return
     */
    /**
     * BBSolutionDatabase::db_set_hostname()
     * 
     * @param mixed $v
     * @return
     */
    public function db_set_hostname( $v )
    {
        $this->_db_host = $v;
    }
    
    /**
     * BBSolutionDatabase::db_set_port()
     * 
     * @param mixed $v
     * @return
     */
    /**
     * BBSolutionDatabase::db_set_port()
     * 
     * @param mixed $v
     * @return
     */
    public function db_set_port( $v )
    {
        $this->_db_port = $v;
    }
    
    /**
     * BBSolutionDatabase::db_set_database_name()
     * 
     * @param mixed $v
     * @return
     */
    /**
     * BBSolutionDatabase::db_set_database_name()
     * 
     * @param mixed $v
     * @return
     */
    public function db_set_database_name( $v )
    {
        $this->_db_name = $v;
    }
    
    /**
     * BBSolutionDatabase::db_set_username()
     * 
     * @param mixed $v
     * @return
     */
    /**
     * BBSolutionDatabase::db_set_username()
     * 
     * @param mixed $v
     * @return
     */
    public function db_set_username( $v )
    {
        $this->_db_username = $v;
    }
    
    /**
     * BBSolutionDatabase::db_set_password()
     * 
     * @param mixed $v
     * @return
     */
    /**
     * BBSolutionDatabase::db_set_password()
     * 
     * @param mixed $v
     * @return
     */
    public function db_set_password( $v )
    {
        $this->_db_password = $v;
    }
    
    /**
     * BBSolutionDatabase::db_set_persistant()
     * 
     * @param mixed $v
     * @return
     */
    /**
     * BBSolutionDatabase::db_set_persistant()
     * 
     * @param mixed $v
     * @return
     */
    public function db_set_persistant( $v )
    {
        $this->_db_persistant = $v;
    }
    
    /**
     * BBSolutionDatabase::db_set_debug_mode()
     * 
     * @param mixed $v
     * @return
     */
    /**
     * BBSolutionDatabase::db_set_debug_mode()
     * 
     * @param mixed $v
     * @return
     */
    public function db_set_debug_mode( $v )
    {
        $this->_db_debug_mode = $v;
    }
    
    /**
     * BBSolutionDatabase::db_set_debug_dir()
     * 
     * @param mixed $v
     * @return
     */
    /**
     * BBSolutionDatabase::db_set_debug_dir()
     * 
     * @param mixed $v
     * @return
     */
    public function db_set_debug_dir( $v )
    {
        $this->_db_debug_dir = $v;
    }
    
    /**
     * BBSolutionDatabase::db_connect_to_database()
     * 
     * Makes a connection to the database server via PDO
     * 
     * @return Object
     */
    public function db_connect_to_database()
    {
        // Make a connection to the MySQL server via PHP PDO
        try
        {
            $this->_db_handle = new PDO( 'mysql:host=' . $this->_db_host . ':' . $this->_db_port . ';dbname=' . $this->_db_name, $this->_db_username, $this->_db_password );
        }
        catch ( PDOException $e )
        {
            // Set the PDO exception to error mode
            $this->_db_handle->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            
            // Encountered an error, take proper action
            $this->_db_error_message = $e->getMessage();
            $this->db_fatal_error( 'Failed to make a valid connection to the MySQL database server. Please check your database connection settings and try again.' );
        }
        
        // Unset username and password
        unset( $this->_db_username );
        unset( $this->_db_password );
        
        // Return the PDO object
        return $this->_db_handle;
    }
    
    /**
     * BBSolutionDatabase::db_cleaner()
     * 
     * Usually cleans the SQL statement before executing it. Since
     * we are using PDO, that is already taken care of since we are
     * using prepared statements. So this function will just return
     * nothing.
     * 
     * @param String $s
     * @return null
     */
    public function db_cleaner( $s )
    {
        return $s;
    }
    
    /**
     * BBSolutionDatabase::db_query()
     * 
     * Executes SQL query on the database server. Also increments the
     * total SQL queries counter on success.
     * 
     * @param String $q
     * @return Object Resource
     */
    public function db_query( $q )
    {
        try
        {
            // Set the PDO exception to error mode
            $this->_db_handle->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            
            // Prepare the SQL statement
            $query = $this->_db_handle->prepare( $q );
            
            // Execute the query
            $query->execute();
            
            // Increment the total SQL queries counter
            $this->db_total_queries++;
        }
        catch ( PDOException $e )
        {
            // Record the SQL query statement
            $this->_db_last_query = $q;
            
            // Is database debug mode turned ON or OFF?
            switch ( $this->_db_debug_mode )
            {
                case true:
                $debug_info = 'Please check your database debug logs for further details regarding this error.';
                break;
                
                case false:
                $debug_info = 'Database debug mode is turned off. Enable database debug mode and try again to get further details regarding this error.';
                break;
            }
            
            // Handle the error
            $this->_db_error_message = $e->getMessage();
            $this->db_fatal_error( 'Failed to execute the desired SQL query statement. ' . $debug_info );
        }
        
        // Return the query
        return $query;
    }
    
    /**
     * BBSolutionDatabase::db_fetch_object()
     * 
     * Returns database data as an object.
     * 
     * @param Object $r
     * @return Object
     */
    public function db_fetch_object( $r )
    {
        // Set the fetch mode to objects
        $r->setFetchMode( PDO::FETCH_OBJ );
        
        // Return the objects
        return $r->fetch();
    }
    
    /**
     * BBSolutionDatabase::db_fetch_array()
     * 
     * Return database data as an array
     * 
     * @param Object $r
     * @return Object
     */
    public function db_fetch_array( $r )
    {
        // Set the fetch mode to array
        $r->setFetchMode( PDO::FETCH_ASSOC );
        
        // Return the objects
        return $r->fetch();
    }
    
    /**
     * BBSolutionDatabase::db_free_result()
     * 
     * Usually frees up memory. Since PDO doesnt need this, we will
     * just return nothing.
     * 
     * @param Object $r
     * @return null
     */
    public function db_free_result( $r )
    {
        // No need to free using PDO, return null
        return;
    }
    
    /**
     * BBSolutionDatabase::db_insert_id()
     * 
     * Gets the last primary key ID of the last data inserted
     * into the MySQL database.
     * 
     * @return Integer
     */
    public function db_insert_id()
    {
        // Return the last inserted id
        return $this->_db_handle->lastInsertId();
    }
    
    /**
     * BBSolutionDatabase::db_num_rows()
     * 
     * Returns the total number of rows in a select statement.
     * 
     * @param Object $r
     * @return Integer
     */
    public function db_num_rows( $r )
    {
        // Return the number of rows
        return $r->rowCount();
    }
    
    /**
     * BBSolutionDatabase::db_affected_rows()
     * 
     * Returns the total number of affected rows in the
     * database from the last database action.
     * 
     * @param Object $r
     * @return Integer
     */
    public function db_affected_rows( $r )
    {
        // Return the total affected rows
        return $r->rowCount();
    }
    
    /**
     * BBSolutionDatabase::db_disconnect()
     * 
     * Disconnects from the MySQL database server.
     * 
     * @return null
     */
    public function db_disconnect()
    {
        // Check if the PDO handle is defined
        if ( $this->_db_handle )
        {
            // Set to null to disconnect
            $this->_db_handle = null;
            
            // Return nothin
            return;
        }
    }
    
    /**
     * BBSolutionDatabase::db_get_total_queries()
     * 
     * Returns the total number of SQL queries executed on
     * the database server.
     * 
     * @return Integer
     */
    public function db_get_total_queries()
    {
        // Return the total SQL queries executed
        return $this->db_total_queries;
    }
    
    /**
     * BBSolutionDatabase::db_fatal_error()
     * 
     * Handles any database errors thrown. Depending on the database
     * debug setting, records data into an error log. Outputs an error
     * to the end-user.
     * 
     * @param String $error
     * @return HTML
     */
    private function db_fatal_error( $error )
    {
        // If debug mode is enabled, need to do a few things
        switch ( $this->_db_debug_mode )
        {
            case true:
            // Anything in the $this->_db_last_query variable?
            if ( $this->_db_last_query != '' ) $this->_db_last_query = "\n" . $this->_db_last_query;
            
            // Check to see there is a trailing slash at the end of the
            // debug directory
            if ( substr( $this->_db_debug_dir, ( strlen( $this->_db_debug_dir ) - 1 ), strlen( $this->_db_debug_dir ) ) != '/' )
            {
                $this->_db_debug_dir = $this->_db_debug_dir . '/';
            }
            
            // At the beginning?
            if ( substr( $this->_db_debug_dir, 0, 1 ) =='/' )
            {
                $this->_db_debug_dir = substr( $this->_db_debug_dir, 1, strlen( $this->_db_debug_dir ) );
            }
            
            // Make sure the debug directory exists
            if ( ! file_exists( $this->_db_debug_dir ) )
            {
                die ( '<h1>bbSolution Error</h1>The configured database debug log directory does not exist.' );
            }
            
            // Make sure the directory is writable
            if ( ! is_writable( $this->_db_debug_dir ) )
            {
                die ( '<h1>bbSolution Error</h1>The configured database debug log directory does not have valid write permissions. Be sure to set valid write permissions on the database debug directory to rectify this error.' );
            }
            
            // Form the complete debug log file path
            $this->_db_debug_dir = ROOT_PATH . $this->_db_debug_dir . 'bbSolution-DBError-' . str_replace( ' ', '', date( 'r' ) ) . '.log.' . $this->BBS->php_ext;
            
            // Create the file. Throw an error if failure occurs
            if ( ! touch( $this->_db_debug_dir ) )
            {
                die ( '<h1>bbSolution Error</h1>Failed to create the database debug error log file.' );
            }
            
            // Set valid permissions on the log file
            if ( ! chmod( $this->_db_debug_dir, 0666 ) )
            {
                die ( '<h1>bbSolution Error</h1>Failed to set valid permissions on the database debug error log file.' );
            }
            
            // Form the log file data
            $log  = "<?php\n\n";
            $log .= "if ( ! defined( 'BBS' ) )\n";
            $log .= "{\n";
            $log .= "echo '<h1>Access Denied</h1>You are not allowed to access this file directly.';\n";
            $log .= "exit();\n";
            $log .= "}\n\n";
            $log .= "==========================================================================\n";
            $log .= "bbSolution : Database Error Entry\n";
            $log .= "==========================================================================\n\n";
            $log .= "Date/Time Of Error: " . date( 'r' ) . "\n";
            $log .= "Error: " . $error . "\n";
            $log .= "MySQL PDO Error Message: " . $this->_db_error_message . "\n";
            $log .= "User IP Address: " . $this->BBS->AGENT['ip_address'] . "\n";
            $log .= "User Hostname: " . $this->BBS->AGENT['hostname'] . "\n";
            $log .= "User Location: " . $this->BBS->AGENT['uri'];
            $log .= $this->_db_last_query;
            $log .= "\n\n";
            $log .= "=========================================================================\n\n";
            $log .= "?>";
            
            // Write the data to the debug file
            if ( $fh = @fopen( $this->_db_debug_dir, 'w' ) )
            {
                @fwrite( $fh, $log );
                @fclose( $fh );
            }
            else
            {
                // Failed to write to the debug file
                die ( '<h1>bbSolution Error</h1>Failed to write the debug data to the database debug file.' );
            }
            break;
        }
        
        // Get error messages ready for use in HTML
        $error                   = htmlspecialchars( $error );
        $this->_db_error_message = htmlspecialchars( $this->_db_error_message );
        
        echo ( 'Database error: ' . $error );
        
        die ( '' );
    }
}

?>