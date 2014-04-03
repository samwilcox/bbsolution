<?php

/**
 * bbSolution
 * 
 * @package   bbSolution PHP Bulletin Board System
 * @author    Sam Wilcox <sam@bb-solution.org>
 * @copyright 2014 bbSolution. All Rights Reserved.
 * @license   http://www.bb-solution.org/license.php
 * @version   CVS: $Id:$
 */

// No hacking attempts are allowed
if ( ! defined( 'BBS' ) )
{
    echo '<h1>Access Denied</h1>You are not allowed to access this file directly.';
    exit();
}

/**
 * BBSolutionMaster
 * 
 * @package   bbSolution PHP Bulletin Board System
 * @author    Sam Wilcox <sam@bb-solution.org>
 * @copyright 2014 bbSolution. All Rights Reserved.
 * @version   CVS: $Id:$
 * @access    public
 */

class BBSolutionMaster
{
    /**
     * Version Number
     * @var String $this_version
     */
     public $this_version = THIS_VERSION;
     
    /**
     * PHP Extension
     * @var String $php_ext
     */
     public $php_ext = 'php';
    
    /**
     * Application Configurations
     * @var Array $CFG
     */
     public $CFG = array();
     
    /**
     * Incoming Data
     * @var Array $INC
     */
     public $INC = array();
     
    /**
     * Member Information
     * @var Array $MEMBER
     */
     public $MEMBER = array();
     
    /**
     * User Agent Information
     * @var Array $AGENT
     */
     public $AGENT = array();
     
    /**
     * Session Information
     * @var Array $SESSION
     */
     public $SESSION = array();
     
    /**
     * Script Execution Timer Information
     * @var Array $TIMER
     */
     public $TIMER = array();
     
    /**
     * SQL Data Passing Array
     * @var Array $TOSQL
     */
     public $TOSQL = array();
     
    /**
     * Theme Passing Array
     * @var Array $T
     */
     public $T = array();
     
    /**
     * Database Cache
     * @var Array $CACHE
     */
     public $CACHE = array();
     
    /**
     * Language Words
     * @var Array $LANG
     */
     public $LANG = array();
     
    /**
     * Error Words
     * @var Array $ERRORS
     */
     public $ERRORS = array();
     
    /**
     * Application Base URL
     * @var String $base_url
     */
     public $base_url = '';
     
    /**
     * Application Script URL
     * @var String $script_url
     */
     public $script_url = '';
     
    /**
     * Application Imageset URL
     * @var String $imageset_url
     */
     public $imageset_url = '';
     
    /**
     * Theme Location URL
     * @var Strong $theme_url
     */
     public $theme_url = '';
     
    /**
     * Theme Location Path
     * @var String $theme_path
     */
     public $theme_path = '';
     
    /**
     * Language Location Path
     * @var String $lang_path
     */
     public $lang_path = '';
     
    /**
     * Database Prefix
     * @var String $db_prefix
     */
     public $db_prefix = '';
    
    public function hand_off()
    {
        // Load the application configuration settings
        $CFG = array();
        require_once( ROOT_PATH . 'config.inc.' . $this->php_ext );
        $this->CFG = $CFG;
        
        // Start the timer
        $this->start_script_execution_timer();
        
        // Fix Microsoft IIS server request_uri var
        $this->fix_iis_uri();
        
        // Get some details about the user
        $this->AGENT['ip_address'] = $this->get_server_var( 'REMOTE_ADDR' );
        $this->validate_ip_address( $this->AGENT['ip_address'] );
        $this->AGENT['hostname']   = gethostbyaddr( $this->AGENT['ip_address'] );
        $this->AGENT['agent']      = $this->get_server_var( 'HTTP_USER_AGENT' );
        $this->AGENT['uri']        = $this->get_server_var( 'REQUEST_URI' );
        
        // Grab the user's browser details
        $this->get_browser_details( $this->AGENT['agent'] );
        
        // Detect any search bots that may be paying us a visit
        $this->detect_search_bots( $this->AGENT['agent'] );
        
        // Filter incoming data for security purposes
        $this->filter_incoming_data();
        
        // Load our core classes, they are important!
        $this->initialize_core_classes();
        
        // Initialize the database and get it goin
        $this->initialize_database();
        
        // Since the database is up and going now, initialize the cache
        // (if turned on)
        $this->initialize_database_cache();
        
        // Setup a few urls
        $this->setup_urls();
        
        // Do a few session related tasks real quick
        $this->SESSIONS->session_garbage_collection();
        $this->SESSIONS->manage_sessions();
        $this->SESSIONS->check_online_record();
        
        // Check to see if a member is logged in
        if ( isset( $_SESSION['bbs_username'] ) )
        {
            $this->MEMBER['status']   = true;
            $this->MEMBER['username'] = $_SESSION['bbs_username'];
        }
        else
        {
            $this->MEMBER['status']   = false;
            $this->MEMBER['username'] = 'Guest';
        }
        
        // Configure some urls and some paths
        $this->configure_urls_paths();
        
        // Load the master class resources
        $this->load_language();
        $this->load_errors();
        $this->load_theme();
        
        // Now that we got that all done, what are we doing?
        if ( isset( $this->INC['cls'] ) )
        {
            $cls = $this->INC['cls'];
        }
        else
        {
            $cls = 'index';
        }
        
        // Now that we know what to do, require in the class and get
        // it goin!
        require_once( ROOT_PATH . 'system/classes/' . $cls . '.class.' . $this->php_ext );
        
        $run = new $cls;
        $run->BBS =& $this;
        $run->start_now();
        
        // Force a session write close
        session_write_close();
        
        // Disconnect from the database
        $this->DB->db_disconnect();
    }
     
    public function get_server_var( $v )
    {
        return trim( $_SERVER[$v] );
    }
    
    public function get_env_var( $v )
    {
        return trim( $_ENV[$v] );
    }
    
    public function fix_iis_uri()
    {
        if ( ! isset( $_SERVER['REQUEST_URI'] ) )
        {
            $_SERVER['REQUEST_URI'] = substr( $_SERVER['PHP_SELF'], 1 );
            if ( isset( $_SERVER['QUERY_STRING'] ) ) { $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING']; }
        }
    }
    
    public function validate_ip_address( $ip )
    {
        // Match the IP pattern, and then validate each IP address
        // octet
        if ( preg_match( "/^((1?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(1?\d{1,2}|2[0-4]\d|25[0-5])$/", $ip ) )
        {
            $octs = explode( '.', $ip );
            
            foreach ( $octs as $octet )
            {
                if ( ( intval( $octet ) > 255 ) OR ( intval( $octet ) < 0 ) )
                {
                    $valid = false;
                }
            }
            
            $valid = true;
        }
        else
        {
            $valid = false;
        }
        
        // Are we valid or not?
        switch ( $this->CFG['validate_ip_address'] )
        {
            case true:
            switch ( $valid )
            {
                case false:
                echo '<h1>bbSolution Error</h1>Your IP address could not be determined. You must have a valid IP address in order to access or site.';
                exit();
                break;
            }
            break;
        }
    }
    
    public function start_script_execution_timer()
    {
        $this->TIMER['start'] = explode( ' ', microtime() );
        $this->TIMER['start'] = $this->TIMER['start'][1] + $this->TIMER['start'][1];
    }   
    
    public function stop_script_execution_timer()
    {
        $this->TIMER['stop'] = explode( ' ', microtime() );
        $this->TIMER['stop'] = $this->TIMER['stop'][1] + $this->TIMER['stop'][0];
        $this->TIMER['stop'] = round( ( $this->TIMER['stop'] - $this->TIMER['start'] ) );
    }
    
    public function initialize_gzip_compression()
    {
        // Only start Gzip compression if enabled
        switch ( $this->CFG['gzip_compression'] )
        {
            case true:
            ob_start( 'ob_gzhandler' );
            break;
        }
    }
    
    public function get_browser_details( $agent )
    {
        // Setup initial vars
        $name  = 'Unknown';
        $agent = strtolower( $agent );
        
        // Create our current browser listing
        $browsers = array( 'Windows Mobile'       => 'IEMobile',
                           'Android Mobile'       => 'Android',
                           'iPhone Mobile'        => 'iPhone',
                           'Blackberry'           => 'Blackberry',
                           'Blazer'               => 'Blazer',
                           'Bolt'                 => 'BOLT',
                           'Opera Mini'           => 'Opera Mini',
                           'Opera Mobile'         => 'Opera Mobi',
                           'Skyfire'              => 'Skyfire',
                           'Firefox'              => 'Firefox',
                           'Google Chrome'        => 'Chrome',
                           'Internet Explorer'    => 'MSIE',
                           'Internet Explorer v1' => 'microsoft internet explorer',
                           'Opera'                => 'Opera',
                           'Apple Safari'         => 'Safari',
                           'Konqueror'            => 'Konqueror',
                           'America Online'       => 'America Online Browser',
                           'AOL'                  => 'AOL',
                           'Netscape'             => 'Navigator' );
                           
        // Go through and match against the user agent, see if we
        // find the browser the user is using
        $found = false;
        
        foreach ( $browsers as $k => $v )
        {
            if ( preg_match( "/$v/", $agent ) )
            {
                $name = $k;
            }
        }
        
        // Set the browser name
        $this->AGENT['browser_name'] = $name;
    }
    
    public function detect_search_bots( $agent )
    {
        // Depending if this feature is enabled, we compare the user agent to
        // the different search bot strings
        switch ( $this->CFG['detect_search_bots'] )
        {
            case true:
            $search_bots = explode( ',', $this->CFG['search_bot_list'] );
/*            
            if ( count( $search_bots ) > 0 )
            {
                foreach ( $search_bots as $bot )
                {
                    if ( strpos( ' ' . strtolower( $agent ), strtolower( $bot ) ) != false )
                    {
                        $name = $bot;
                    }
                }
            }
*/            
            // Determine if found or not
            if ( isset( $name ) )
            {
                $this->SESSION['search_bot']      = 1;
                $this->SESSION['search_bot_name'] = $name;
            }
            else
            {
                $this->SESSION['search_bot']      = 0;
                $this->SESSION['search_bot_name'] = '';
            }
            break;
            
            case false:
            // Not detecting bots, just clear out the var values
            $this->SESSION['search_bot']      = 0;
            $this->SESSION['search_bot_name'] = '';
            break;
        }
    }
    
    public function filter_incoming_data()
    {
        foreach ( $_GET as $k => $v )
        {
            $this->INC[$k] = filter_var( $v, FILTER_SANITIZE_STRING );
        }
        
        foreach ( $_POST as $k => $v )
        {
            $this->INC[$k] = filter_var( $v, FILTER_SANITIZE_STRING );
        }
    }
    
    public function initialize_database()
    {
        // Use the correct driver depending on what was configured
        require_once( ROOT_PATH . 'system/drivers/' . strtolower( $this->CFG['db_driver'] ) . '.driver.class.' . $this->php_ext );
        
        // Create the database object, and pass in needed info
        $this->DB = new BBSolutionDatabase;
        $this->DB->BBS =& $this;
        $this->DB->db_set_hostname( $this->CFG['db_host'] );
        $this->DB->db_set_port( $this->CFG['db_port'] );
        $this->DB->db_set_database_name( $this->CFG['db_name'] );
        $this->DB->db_set_username( $this->CFG['db_username'] );
        $this->DB->db_set_password( $this->CFG['db_password'] );
        $this->DB->db_set_persistant( $this->CFG['db_persistant'] );
        $this->DB->db_set_debug_mode( $this->CFG['db_debug_mode'] );
        $this->DB->db_set_debug_dir( $this->CFG['db_debug_dir'] );
        $this->DB->db_connect_to_database();
        
        // Determine the prefix
        if ( $this->CFG['db_prefix'] != '' )
        {
            $this->db_prefix = $this->CFG['db_prefix'];
        } 
    }
    
    public function initialize_core_classes()
    {
        // List database references
        $db_ref = array( 'mysql'     => 'mysql',
                         'mysqli'    => 'mysql',
                         'pdo_mysql' => 'mysql' );
        
        // Require the needed core class files
        require_once( ROOT_PATH . 'system/classes/core/sessions.class.' . $this->php_ext );
        require_once( ROOT_PATH . 'system/classes/core/sql-queries.' . $db_ref[$this->CFG['db_driver']] . '.class.' . $this->php_ext );
        
        // Create the objects
        $this->SQL      = new BBSolutionSQL;
        $this->SQL->BBS =& $this;
        
        $this->SESSIONS      = new BBSolutionSessions;
        $this->SESSIONS->BBS =& $this;
    }
    
    public function initialize_database_cache()
    {
        // Is database caching enabled?
        switch ( $this->CFG['cache_db'] )
        {
            case true:
            // Build the table caching list
            $cache_list = array( 'members'             => 'member_id',
                                 'forums'              => 'forum_id',
                                 'installed_themes'    => 'theme_id',
                                 'installed_languages' => 'language_id',
                                 'statistics'          => 'statistic_id',
                                 'forums_read'         => 'read_id',
                                 'groups'              => 'group_id' );
                                 
            // Determine which cache method we are using
            switch ( $this->CFG['cache_db_method'] )
            {
                case 'dbcache':
                // Caching to the database
                $q = $this->DB->db_query( $this->SQL->sql_select_all_stored_cache() );
                
                // Go throug the records
                while ( $r = $this->DB->db_fetch_array( $q ) )
                {
                    // Unset $records to make sure its fresh
                    unset( $records );
                    
                    // Go thrugh the cache list, and cache whats needed
                    foreach ( $cache_list as $k => $v )
                    {
                        // Depending on the table, may need to do some special sorting
                        if ( $k == 'groups' )
                        {
                            $sorting = " ORDER BY group_sorting ASC";
                        }
                        else
                        {
                            $sorting = " ORDER BY " . $v . " ASC";
                        }
                        
                        if ( $r['cache_title'] != '' )
                        {
                            // Cache data does exist, populate it
                            $this->CACHE[$k] = unserialize( $r['cache_data'] );
                        }
                        else
                        {
                            // If table is forums, we need do perform a special query
                            if ( $k == 'forums' )
                            {
                                $q2 = $this->DB->db_query( $this->SQL->sql_select_all_forums_tree() );
                            }
                            else
                            {
                                $this->TOSQL = array( 'table'   => $k,
                                                      'sorting' => $sorting );
                                                      
                                $q2 = $this->DB->db_query( $this->SQL->sql_select_all_cache() );
                            }
                            
                            // Grab it all
                            while ( $record = $this->DB->db_fetch_array( $q2 ) )
                            {
                                $records[] = $record;
                            }
                            
                            $this->DB->db_free_result( $q2 );
                            
                            $to_cache = serialize( $records );
                            
                            // Update the cache in the database
                            $this->TOSQL = array( 'to_cache'    => $to_cache,
                                                  'cache_title' => $k );
                            
                            $this->DB->db_query( $this->SQL->sql_update_cache() );
                            
                            $this->CACHE[$k] = unserialize( $to_cache );
                        }
                    }
                }
                
                // Free up memory
                $this->DB->db_free_result( $q );
                break;
                
                case 'filecache':
                // Check for backslashes at the end and the beginning of the cache dir path
                if ( substr( $this->CFG['cache_dir'], ( strlen( $this->CFG['cache_dir'] ) - 1 ), strlen( $this->CFG['cache_dir'] ) ) == '/' )
                {
                    $cache_dir = substr( $this->CFG['cache_dir'], 0, ( strlen( $this->CFG['cache_dir'] ) - 1 ) );
                }
                else
                {
                    $cache_dir = $this->CFG['cache_dir'];
                }
                
                if ( substr( $cache_dir, 0, 1 ) == '/' )
                {
                    $cache_dir = substr( $cache_dir, 1, strlen( $cache_dir ) );
                }
                
                if ( substr( $this->CFG['cache_db_dir'], ( strlen( $this->CFG['cache_db_dir'] ) - 1 ), strlen( $this->CFG['cache_db_dir'] ) ) == '/' )
                {
                    $cache_db_dir = substr( $this->CFG['cache_db_dir'], 0, ( strlen( $this->CFG['cache_db_dir'] ) - 1 ) );
                }
                else
                {
                    $cache_db_dir = $this->CFG['cache_db_dir'];
                }
                
                // Create the full cache dir path
                $cache_dir = ROOT_PATH . $cache_dir . '/' . $cache_db_dir . '/';
                
                // Check to see if the file exists or not, and for permissions
                if ( ! file_exists( $cache_dir ) )
                {
                    die ( '<h1>bbSolution Error</h1>The database cache directory does not exist. Please check your cache settings and verify that the database cache directory does exist and try again.' );
                }
                
                if ( ! is_readable( $cache_dir ) )
                {
                    die ( '<h1>bbSolution Error</h1>The database cache directory is not readable. Please make sure valid permissions are set on this directory.' );
                }
                
                if ( ! is_writable( $cache_dir ) )
                {
                    die ( '<h1>bbSolution Error</h1>The database cache directory is not writable. Please make sure valid permissions are set on this directory.' );
                }
                
                // Create the needed files
                foreach ( $cache_list as $k => $v )
                {
                    if ( ! file_exists( $cache_dir . $k . '.cache.' . $this->php_ext ) )
                    {
                        if ( ! touch( $cache_dir . $k . '.cache.' . $this->php_ext ) )
                        {
                            die ( '<h1>bbSolution Error</h1>Failed to create the database cache file.' );
                        }
                        
                        if ( ! chmod( $cache_dir . $k . '.cache.' . $this->php_ext, 0666 ) )
                        {
                            die ( '<h1>bbSolution Error</h1>Failed to set valid permissions on the database cache file.' );
                        }
                    }
                }
                
                // Create or get records from the database cache files
                foreach ( $cache_list as $k => $v )
                {
                    // Make sure we start out with a fresh $records var
                    unset( $records );
                    
                    // If filesize is 0, then the file contains no data
                    if ( filesize( $cache_dir . $k . '.cache.' . $this->php_ext ) == 0 )
                    {
                        // Special sorting may be needed
                        if ( $k == 'groups' )
                        {
                            $sorting = " ORDER BY group_sorting ASC";
                        }
                        else
                        {
                            $sorting = " ORDER BY " . $v . " ASC";
                        }
                        
                        // If table is forums, need to do a special query
                        if ( $k == 'forums' )
                        {
                            $q = $this->DB->db_query( $this->SQL->sql_select_all_forums_tree() );
                        }
                        else
                        {
                            $this->TOSQL = array( 'table'   => $k,
                                                  'sorting' => $sorting );
                                                  
                            $q = $this->DB->db_query( $this->SQL->sql_select_all_cache() );
                        }
                        
                        // Go through all records
                        while ( $record = $this->DB->db_fetch_array( $q ) )
                        {
                            $records[] = $record;
                        }
                        
                        $this->DB->db_free_result( $q );
                        
                        // Serialize data
                        $to_cache = serialize( $records );
                        
                        // Write the data to file
                        if ( $fh = @fopen( $cache_dir . $k . '.cache.' . $this->php_ext, 'w' ) )
                        {
                            @fwrite( $fh, $to_cache );
                            @fclose( $fh );
                        }
                        else
                        {
                            die ( '<h1>bbSolution Error</h1>Failed to write data to the cache file.' );
                        }
                        
                        // Popoluate cache
                        $this->CACHE[$k] = unserialize( $to_cache );
                    }
                    else
                    {
                        // Populate from file
                        $this->CACHE[$k] = unserialize( implode( '', file( $cache_dir . $k . '.cache.' . $this->php_ext ) ) );
                    }
                }
                break;
            }
            break;
        }
    }
    
    public function update_database_cache( $table, $id )
    {
        // Determine if database caching is enabled or not
        switch ( $this->CFG['cache_db'] )
        {
            case true:
            // Database caching is turned on
            // Some special sorting may be needed
            if ( $table == 'groups' )
            {
                $sorting = " ORDER BY group_sorting ASC";
            }
            else
            {
                $sorting = " ORDER BY " . $id . " ASC";
            }
            
            // Re-cache data depending on the method
            switch ( $this->CFG['cache_db_method'] )
            {
                case 'dbcache':
                // If table is forums, we need do perform a special query
                if ( $table == 'forums' )
                {
                    $q2 = $this->DB->db_query( $this->SQL->sql_select_all_forums_tree() );
                }
                else
                {
                    $this->TOSQL = array( 'table'   => $table,
                                          'sorting' => $sorting );
                                                      
                    $q2 = $this->DB->db_query( $this->SQL->sql_select_all_cache() );
                }
                            
                // Grab it all
                while ( $record = $this->DB->db_fetch_array( $q2 ) )
                {
                    $records[] = $record;
                }
                            
                $this->DB->db_free_result( $q2 );
                            
                $to_cache = serialize( $records );
                            
                // Update the cache in the database
                $this->TOSQL = array( 'to_cache'    => $to_cache,
                                      'cache_title' => $table );
                            
                $this->DB->db_query( $this->SQL->sql_update_cache() );
                            
                $this->CACHE[$k] = unserialize( $to_cache );
                break;
                
                case 'filecache':
                // Check for backslashes at the end and the beginning of the cache dir path
                if ( substr( $this->CFG['cache_dir'], ( strlen( $this->CFG['cache_dir'] ) - 1 ), strlen( $this->CFG['cache_dir'] ) ) == '/' )
                {
                    $cache_dir = substr( $this->CFG['cache_dir'], 0, ( strlen( $this->CFG['cache_dir'] ) - 1 ) );
                }
                else
                {
                    $cache_dir = $this->CFG['cache_dir'];
                }
                
                if ( substr( $cache_dir, 0, 1 ) == '/' )
                {
                    $cache_dir = substr( $cache_dir, 1, strlen( $cache_dir ) );
                }
                
                if ( substr( $this->CFG['cache_db_dir'], ( strlen( $this->CFG['cache_db_dir'] ) - 1 ), strlen( $this->CFG['cache_db_dir'] ) ) == '/' )
                {
                    $cache_db_dir = substr( $this->CFG['cache_db_dir'], 0, ( strlen( $this->CFG['cache_db_dir'] ) - 1 ) );
                }
                else
                {
                    $cache_db_dir = $this->CFG['cache_db_dir'];
                }
                
                // Create the full cache dir path
                $cache_dir = ROOT_PATH . $cache_dir . '/' . $cache_db_dir . '/';
                
                // Check to see if the file exists or not, and for permissions
                if ( ! file_exists( $cache_dir ) )
                {
                    die ( '<h1>bbSolution Error</h1>The database cache directory does not exist. Please check your cache settings and verify that the database cache directory does exist and try again.' );
                }
                
                if ( ! is_readable( $cache_dir ) )
                {
                    die ( '<h1>bbSolution Error</h1>The database cache directory is not readable. Please make sure valid permissions are set on this directory.' );
                }
                
                if ( ! is_writable( $cache_dir ) )
                {
                    die ( '<h1>bbSolution Error</h1>The database cache directory is not writable. Please make sure valid permissions are set on this directory.' );
                }
                
                // If table is forums, need to do a special query
                if ( $table == 'forums' )
                {
                    $q = $this->DB->db_query( $this->SQL->sql_select_all_forums_tree() );
                }
                else
                {
                    $this->TOSQL = array( 'table'   => $table,
                                          'sorting' => $sorting );
                                                  
                $q = $this->DB->db_query( $this->SQL->sql_select_all_cache() );
                }
                        
                // Go through all records
                while ( $record = $this->DB->db_fetch_array( $q ) )
                {
                    $records[] = $record;
                }
                        
                $this->DB->db_free_result( $q );
                        
                // Serialize data
                $to_cache = serialize( $records );
                        
                // Write the data to file
                if ( $fh = @fopen( $cache_dir . $k . '.cache.' . $this->php_ext, 'w' ) )
                {
                    @fwrite( $fh, $to_cache );
                    @fclose( $fh );
                }
                else
                {
                    die ( '<h1>bbSolution Error</h1>Failed to write data to the cache file.' );
                }
                        
                // Popoluate cache
                $this->CACHE[$k] = unserialize( $to_cache );
                break;
            }
            break;
        }
    }
    
    public function get_data( $table, $id )
    {
        // Is database caching enabled?
        switch ( $this->CFG['cache_db'] )
        {
            case true:
            // Check if the cache already exists or not
            if ( count( $this->CACHE[$table] ) > 0 )
            {
                return $this->CACHE[$table];
            }
            else
            {
                return false;
            }
            break;
            
            case false:
            // Some special sorting is needed possibly
            if ( $table == 'groups' )
            {
                $sorting = " ORDER BY group_sorting ASC";
            }
            else
            {
                $sorting = " ORDER BY " . $id . " ASC";
            }
            
            // If the table is forums, some special query is needed,
            // and if not, then just select all
            if ( $table == 'forums' )
            {
                $q = $this->DB->db_query( $this->SQL->sql_select_all_forums_tree() );
            }
            else
            {
                $this->TOSQL = array( 'table'   => $table,
                                      'sorting' => $sorting );
                
                $q = $this->DB->db_query( $this->SQL->sql_select_all_cache() );
            }
            
            // Go throug the records
            while ( $record = $this->DB->db_fetch_array( $q ) )
            {
                $records[] = $record;
            }
            
            $this->DB->db_free_result( $q );
            
            // Serialize the records
            $result = serialize( $records );
            
            // Unserialize and populate the cache var
            $this->CACHE[$table] = unserialize( $result );
            
            // If no results, then we will return false
            if ( count( $this->CACHE[$table] ) > 0 )
            {
                return $this->CACHE[$table];
            }
            else
            {
                return false;
            }
            break;
        }
    }
    
    public function setup_urls()
    {
        // Check if there is a trailing slash or not
        if ( substr( $this->CFG['application_url'], strlen( $this->CFG['application_url'] ) - 1, strlen( $this->CFG['application_url'] ) ) == '/' )
        {
            $this->base_url = substr( $this->CFG['application_url'], 0, strlen( $this->CFG['application_url'] ) - 1 );
        }
        else
        {
            $this->base_url = $this->CFG['application_url'];
        }
        
        $this->script_url = $this->base_url . '/' . $this->CFG['application_script_filename'];
    }
    
    public function seo_url( $a = '', $b ='', $c = '' )
    {
        $url = '';
        
        // Is search engine optimization enabled?
        switch ( $this->CFG['search_optimize'] )
        {
            case true:
            // Search engine optimization is enabled, create the URL depending
            // on the passed in vars
            ( $a != '' ) ? $url .= $a : '';
            ( $b != '' ) ? $url .= '/' . $b : '';
            ( $c != '' ) ? $url .= '/' . $c : '';
            
            return $this->base_url . '/' . $url;
            break;
            
            case false:
            // Search engine optimization is disabled, create the URL depending
            // on the passed in vars
            ( $a != '' ) ? $url .= '?cls=' . $a : '';
            ( $b != '' ) ? $url .= '&amp;to_do=' . $b : '';
            ( $c != '' ) ? $url .= '&amp;page=' . $c : '';
            
            return $this->script_url . $url;
            break;
        }
    }
    
    public function delete_cookie( $name, $php_cookie = false )
    {
        // Is this a PHP session cookie?
        // If so, we delete a different way
        switch ( $php_cookie )
        {
            case true:
            // It is a PHP cookie
            setcookie( session_name(), '', time() - 3600 );
            break;
            
            case false:
            // A normal cookie
            setcookie( $name, '', time() - 3600, $this->CFG['cookie_path'], $this->CFG['cookie_domain'] );
            break;
        }
    }
    
    public function new_cookie( $name, $value, $expire )
    {
        setcookie( $name, $value, $expire, $this->CFG['cookie_path'], $this->CFG['cookie_domain'] );
    }
    
    public function configure_urls_paths()
    {
        // Is a member logged in?
        switch ( $this->MEMBER['status'] )
        {
            case false:
            // Grab the installed themes data
            $r = $this->get_data( 'installed_themes', 'theme_id' );
            
            if ( $r != false )
            {
                foreach ( $r as $k => $v )
                {
                    // Set the selected theme
                    if ( $v['theme_id'] == $this->CFG['theme_id'] )
                    {
                        $theme_folder          = $v['theme_install_folder'];
                        $theme_imageset_folder = $v['theme_imageset_install_folder'];
                    }
                }
            }
            
            // Grab the installed languages data
            $r = $this->get_data( 'installed_languages', 'language_id' );
            
            if ( $r != false )
            {
                foreach ( $r as $k => $v )
                {
                    // Set the selected language
                    if ( $v['language_id'] == $this->CFG['language_id'] )
                    {
                        $language_folder = $v['language_install_folder'];
                    }
                }
            }
            
            // Set the default timezone for PHP to use
            date_default_timezone_set( $this->CFG['dt_timezone'] );
            
            // Setup a few urls and paths
            $this->theme_path   = ROOT_PATH . 'themes/' . $theme_folder . '/';
            $this->theme_url    = $this->base_url . '/themes/' . $theme_folder;
            $this->imageset_url = $this->base_url . '/public/imagesets/' . $theme_imageset_folder;
            $this->lang_path    = ROOT_PATH . 'languages/' . $language_folder . '/';
            break;
            
            case true:
            // Member stuff to go here...
            break;
        }
    }
    
    public function load_language()
    {
        $LANG = array();
        require( $this->lang_path . 'global.lang.' . $this->php_ext );
        $this->LANG = $LANG;        
    }
    
    public function load_errors()
    {
        $ERRORS = array();
        require( $this->lang_path . 'errors.lang.' . $this->php_ext );
        $this->ERRORS = $ERRORS;
    }
    
    public function load_theme()
    {
        require_once( $this->theme_path . 'global.theme.' . $this->php_ext );
        
        $this->THEME       = new BBSolutionThemeGlobal;
        $this->THEME->BBS  =& $this;
        $this->THEME->LANG = $this->LANG;
    }
    
    public function build_forum_nav_links( $forum_id )
    {
        // Setup a few vars first
        $parent_id = 0;
        $depth     = 0;
        $next      = false;
        
        // Go throug the forums
        $r = $this->get_data( 'forums', 'forum_id' );
        
        if ( $r != false )
        {
            foreach ( $r as $k => $v )
            {
                // Match the passed in forum id
                if ( $v['forum_id'] == $forum_id )
                {
                    $parent_id   = $v['forum_parent_id'];
                    $depth       = $v['depth'];
                    $forum_title = $v['forum_title'];
                }
            }
        }
        
        // Initialize the build array
        $build = array();
        
        // Add the first link above to the nav tree
        $this->T = array( 'forum_title' => $forum_title,
                          'forum_url'   => $this->seo_url( 'forum', $forum_id ) );
                          
        $build[] = $this->THEME->html_forum_nav_tree_add();
        
        // Parse through all the child forums, etc
        if ( ( $parent_id != 0 ) AND ( $depth != 0 ) )
        {
            $current_depth = $depth;
            
            while ( $current_depth >= 0 )
            {
                if ( $next ) break;
                
                $r = $this->get_data( 'forums', 'forum_id' );
                
                if ( $r != false )
                {
                    foreach ( $r as $k => $v )
                    {
                        if ( ( $v['forum_id'] == $parent_id ) AND ( $v['depth'] == $current_depth ) )
                        {
                            if ( $v['forum_parent_id'] != 0 )
                            {
                                $parent_id = $v['forum_parent_id'];
                            }
                            elseif ( ( $v['forum_parent_id'] == 0 ) AND ( $v['depth'] == 0 ) )
                            {
                                $next = true;
                            }
                            
                            // Add to the build array
                            $this->T = array( 'forum_title' => $v['forum_title'],
                                              'forum_url'   => $this->seo_url( 'forum', $v['forum_id'] ) );
                                              
                            $build[] = $this->THEME->html_forum_nav_tree_add();
                        }
                    }
                    
                    $current_depth = ( $current_depth - 1 );
                }
            }
        }
        
        // Reverse the array to get all contents in the correct order
        array_reverse( $build );
        $nav = '';
        
        // Append each forum to the $nav var
        foreach ( $build as $forum )
        {
            $nav .= $forum;
        }
        
        // Return the nav links
        return $nav;
    }
    
    public function calculate_age( $m, $d, $y )
    {
        $age_time = mktime( 0, 0, 0, $m, $d, $y );
        $age      = ( $age_time < 0 ) ? ( time() + ( $age_time * -1 ) ) : time() - $age_time;
        $yr       = 60 * 60 * 24 * 365;
        
        // Return the age
        return floor( $age / $yr ); 
    }
    
    public function parse_timestamp( $timestamp, $today = false, $r_date = false, $r_time = false )
    {
        // Get all our application date/time settings
        $time_format = $this->CFG['dt_time_format'];
        $date_format = $this->CFG['dt_date_format'];
        $show_today  = $this->CFG['dt_show_today'];
        
        // Form the full date and time format
        $full_format = $date_format . ' ' . $time_format;
        
        // Are we just returning the date?
        if ( $r_date ) return date( $date_format, $timestamp );
        
        // Are we just returning the time?
        if ( $r_time ) return date( $time_format, $timestamp );
        
        // Check to see if we are going to display the "Today at 12:23:32PM"
        // Of course, if the timestamp is todays
        switch ( $today )
        {
            case true:
            switch ( $show_today )
            {
                case true:
                // We will check to see if the timestamp is todays
                $todays_date = date( 'm/d/y', mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) ) );
                
                if ( date( 'm/d/y', $timestamp ) == $todays_date )
                {
                    $timestr    = date( $time_format, $timestamp );
                    $lang_today = $this->LANG['dt_today'];
                    $lang_today = str_replace( '%%TIME%%', $timestr, $lang_today );
                    
                    // Return the time
                    return $lang_today;
                }
                else
                {
                    // Looks like its not todays date, return a full format
                    return date( $full_format, $timestamp );
                }
                break;
                
                case false:
                // Not going to check for today, just return a full format
                return date( $full_format, $timestamp );
                break;
            }
            break;
            
            case false:
            // Not going to check for today, just return a full format
            return date( $full_format, $timestamp );
            break;
        }
    }
    
    public function check_for_unread_posts( $method, $forum_id = '', $topic_id = '' )
    {
        // Which method are we doing?
        switch ( $method )
        {
            case 'forum':
            $unread = true;
            
            // Go through the forum read table
            $r = $this->get_data( 'forums_read', 'read_id' );
            
            if ( $r != false )
            {
                foreach ( $r as $k => $v )
                {
                    if ( ( $v['read_forum_id'] == $forum_id ) AND ( $v['read_member_id'] == $this->MEMBER['id'] ) )
                    {
                        // Get the topics
                        $r2 = $this->get_data( 'topics', 'topic_id' );
                        
                        if ( $r2 != false )
                        {
                            foreach ( $r2 as $key => $val )
                            {
                                if ( $val['topic_id'] == $v['read_topic_id'] )
                                {
                                    $last_post = $val['topic_lastpost_timestamp'];
                                }
                            }
                        }
                        
                        // Compare to see if the post has been read or not
                        if ( $last_post <= $v['read_last_read_timestamp'] ) $unread = false;
                    }
                }
            }
            
            // Return the result
            return $unread;
            break;
            
            case 'topic':
            $unread = true;
            
            // Go thrugh the forum read table
            $r = $this->get_data( 'forums_read', 'read_id' );
            
            if ( $r != false )
            {
                foreach ( $r as $k => $v )
                {
                    if ( ( $v['read_topic_id'] == $topic_id ) AND ( $v['read_member_id'] == $this->MEMBER['id'] ) )
                    {
                        // Go through the topics
                        $r2 = $this->get_data( 'topics', 'topic_id' );
                        
                        if ( $r2 != false )
                        {
                            foreach ( $r2 as $key => $val )
                            {
                                if ( $val['topic_id'] == $topic_id )
                                {
                                    $last_post = $val['topic_lastpost_timestamp'];
                                }
                            }
                        }
                        
                        // Compare to see if the post has been read or not
                        if ( $last_post <= $v['read_last_read_timestamp'] ) $unread = false;
                    }
                }
            }
            
            // Return the result
            return $unread;
            break;
        }
    }
    
    public function generate_form_token()
    {
        $chars = substr( str_shuffle( "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZacdefghijkmopqrstuvwxy" ), 0, 32 );
        $chars = sha1( md5( $chars ) );
        
        // Return the token
        return md5( $chars . $md5( $this->AGENT['agent'] ) );
    }
    
    public function generate_login_token( $username )
    {
        $chars = substr( str_shuffle( "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZacdefghijkmopqrstuvwxy" ), 0, 32 );
        $chars = sha1( md5( $chars ) );
        
        // Return the token
        return md5( $chars . md5( $username ) );
    }
    
    public function generate_pagination( $pre_url, $items, $perpage, $page )
    {
        // Figure out what we need to append to the URL
        ( $this->CFG['search_optimize'] ) ? $append = '/' : $append = '&amp;page=';
        
        // Form the pre-url
        $pre_url . $append;
        
        // How many total pages do we got goin?
        $total_pages = ceil( $items / $perpage );
        
        // If total pages = 0, no need to return any links
        if ( $total_pages == 0 ) return '';
        
        if ( $total_pages < 5 )
        {
            if ( $page > 1 )
            {
                $prev = ( $page - 1 );
                $previous = '<a href="' . $pre_url . $prev . '" title="' . $this->LANG['next_page'] . '">&#171;</a>';
            }
            
            for ( $i = 1; $i <= $total_pages; $i++ )
            {
                if ( $page == $i )
                {
                    $pages .= ' <strong>' . $i . '</strong>';
                }
                else
                {
                    $pages .= ' <a href="' . $pre_url . $i . '" title="' . $i . '">' . $i . '</a>';
                }
            }
            
            if ( $page < $total_pages )
            {
                $next_page = ( $page + 1 );
                $next      = ' <a href="' . $pre_url . $next_page . '" title="' . $this->LANG['previous_page'] . '">&#187;</a> ';
            }
            
            // Return the links
            return $previous . $pages . $next;
        }
        
        // Determine if we need to list a go to first or last page
        ( $page > 1 ) ? $go_first = '<a href="' . $pre_url . '1" title="' . $this->LANG['first_page'] . '">1</a>' : $go_first = '';    
        ( ( $total_pages - $page ) > 5 ) ? $go_last = ' <a href="' . $pre_url . $total_pages . '" title="' . $this->LANG['last_page'] . '">' . $total_pages . '</a> ' : $go_last = '';
        
        $end  = ( $total_pages - 5 );
        $more = ( $page + 5 );
        
        if ( $page < $end )
        {
            if ( $page > 1 )
            {
                $prev = ( $page - 1 );
                $previous = '<a href="' . $pre_url . $prev . '" title="' . $this->LANG['previous_page'] . '">&#171;</a>';
            }
            
            for ( $i = $page; $i <= $more; $i++ )
            {
                if ( $page == $i )
                {
                    $pages .= ' <strong>' . $i . '</strong>';
                }
                else
                {
                    $pages .= ' <a href="' . $pre_url . $i . '" title="' . $i . '">' . $i . '</a>';
                }
            }
            
            if ( $page < $end )
            {
                $next_page = ( $page + 1 );
                $next      = ' <a href="' . $pre_url . $next_page . '" title="' . $this->LANG['next_page'] . '">&#187;</a>';
            }
        }
        else
        {
            if ( $page == $total_pages )
            {
                $prev     = ( $total_pages - 1 );
                $previous = '<a href="' . $pre_url . $prev . '" title="' . $this->LANG['previous_page'] . '">&#171;</a>';
            }
            else
            {
                $prev     = ( $page - 1 );
                $previous = '<a href="' . $pre_url . $prev . '" title="' . $this->LANG['previous_page'] . '">&#171;</a>';
            }
            
            for ( $i = $end; $i <= $total_pages; $i++ )
            {
                if ( $page == $i )
                {
                    $pages .= ' <strong>' . $i . '</strong>';
                }
                else
                {
                    $pages .= ' <a href="' . $pre_url . $i . '" title="' . $i . '">' . $i . '</a>';
                }
            }
            
            if ( $page < $total_pages )
            {
                $next_page = ( $page + 1 );
                $next      = ' <a href="' . $pre_url . $next_page . '" title="' . $this->LANG['next_page'] . '">&#187;</a>';
            }
        }
        
        // Return the page links
        return $go_first . $previous . $pages . $next . $go_last;
    }
    
    public function get_member_link( $member_id, $title = '', $sep = '' )
    {
        // Find the member a get some info
        $found = false;
        
        $r = $this->get_data( 'members', 'member_id' );
        
        if ( $r != false )
        {
            foreach ( $r as $k => $v )
            {
                if ( $v['member_id'] == $member_id )
                {
                    $found               = true;
                    $member_display_name = $v['member_display_name'];
                    $group_id            = $v['member_primary_group_id'];
                }
            }
        }
        
        // Was the member found?
        // If not, return unknown
        if ( ! $found ) return $this->LANG['unknown'];
        
        $found = false;
        
        // Get the group information
        $r = $this->get_data( 'groups', 'group_id' );
        
        if ( $r != false )
        {
            foreach ( $r as $k => $v )
            {
                if ( $v['group_id'] == $group_id )
                {
                    $found = true;
                    $group_color     = $v['group_color'];
                    $group_important = $v['group_important'];
                }
            }
        }
        
        // Was a group found? If not, return no styling
        if ( ! $found )
        {
            $this->T = array( 'seperator'   => $sep,
                              'title'       => $title,
                              'member_name' => $member_display_name,
                              'member_link' => $this->seo_url( 'members', $member_id ) );
                          
            return $this->THEME->html_member_link_no_style();
        } 
        
        // A group was found?
        // Is the group marked important? If so, use strong HTML tags
        switch ( $group_important )
        {
            case 1:
            $this->T = array( 'seperator'   => $sep,
                              'title'       => $title,
                              'member_name' => $member_display_name,
                              'member_link' => $this->seo_url( 'members', $member_id ),
                              'group_color' => $group_color );
                              
            return $this->THEME->html_member_link_important();
            break;
            
            case 0:
            $this->T = array( 'seperator'   => $sep,
                              'title'       => $title,
                              'member_name' => $member_display_name,
                              'member_link' => $this->seo_url( 'members', $member_id ),
                              'group_color' => $group_color );
                              
            return $this->THEME->html_member_link_normal();
            break;
        }     
    }
    
    public function get_group_link( $group_id, $title = '', $sep = '' )
    {        
        // Get some info on the group
        $found = false;
        
        $r = $this->get_data( 'groups', 'group_id' );
        
        if ( $r != false )
        {
            foreach ( $r as $k => $v )
            {
                if ( $v['group_id'] == $group_id )
                {
                    $found           = true;
                    $group_title     = $v['group_title'];
                    $group_about     = $v['group_about'];
                    $group_color     = $v['group_color'];
                    $group_important = $v['group_important'];
                }
            }
        }
        
        // Was the group found? If not, return the unknown string
        if ( ! $found ) return $this->LANG['unknown'];
        
        // Was there a title specified?
        ( $title != '' ) ? $title = $title : $title = $group_about;
        
        // Depending if the group is important or not, we will return
        // the proper linkage
        switch ( $group_important )
        {
            case 1:
            $this->T = array( 'seperator'   => $sep,
                              'title'       => $title,
                              'group_name'  => $group_title,
                              'group_link'  => $this->seo_url( 'groups', $group_id ),
                              'group_color' => $group_color );
                              
            return $this->THEME->html_group_link_important();
            break;
            
            case 0:
            $this->T = array( 'seperator'   => $sep,
                              'title'       => $title,
                              'group_name'  => $group_title,
                              'group_link'  => $this->seo_url( 'groups', $group_id ),
                              'group_color' => $group_color );
                              
            return $this->THEME->html_group_link_normal();
            break;
        }
    }
    
    public function url_exist( $url )
    {
        // Use curl to see if a specified URL exists
        $res_url = curl_init();
        
        curl_setopt( $res_url, CURLOPT_URL, $url );
        curl_setopt( $res_url, CURLOPT_BINARYTRANSFER, 1 );
        curl_setopt( $res_url, CURLOPT_HEADERFUNCTION, 'curlHeaderCallback' );
        curl_setopt( $res_url, CURLOPT_FAILONERROR, 1 );
        curl_exec( $res_url );
        
        $return_code = curl_getinfo( $res_url, CURLINFO_HTTP_CODE );
        
        // Close
        curl_close( $res_url );
        
        // Return the result, depending on...
        if ( $return_code != 200 && $return_code != 302 && $return_code != 304 ) { return false; } else { return true; }
    }
    
    public function check_feature_permissions( $feature )
    {
        // Check to see if a member is currently logged in
        switch ( $this->MEMBER['status'] )
        {
            case true:
            // Member logged in, obtain needed information
            $found = false;
            
            $r = $this->get_data( 'members', 'member_id' );
            
            if ( $r != false )
            {
                foreach ( $r as $k => $v )
                {
                    if ( $v['member_id'] == $this->MEMBER['id'] )
                    {
                        $found            = true;
                        $primary_group    = $v['member_primary_group_id'];
                        $secondary_groups = explode( ',', $v['member_secondary_group_ids'] );
                    }
                }
            }
            
            // Was the member information found?
            if ( ! $found ) return false;
            break;
            
            case false:
            // Member isnt logged in, place in the "hard-coded" guests group
            $primary_group = 6;
            break;
        }
        
        $found = false;
            
        // Go through the feature permission sets, see if the user/member
        // has permission or not
        $r = $this->get_data( 'feature_permissions', 'feature_id' );
            
        if ( $r != false )
        {
            foreach ( $r as $k => $v )
            {
                if ( strtolower( $v['feature_title'] ) == strtolower( $feature ) )
                {
                    $found          = true;
                    $enabled        = $v['feature_enabled'];
                    $allowed_users  = explode( ',', $v['feature_allowed_users'] );
                    $allowed_groups = explode( ',', $v['feature_allowed_groups'] );
                }
            }
        }
            
        // Is the feature found?
        // If not, return false
        if ( ! $found ) return false;
            
        // Is the feature enabled?
        // If not, return false
        if ( $enabled == 0 ) return false;
            
        // If the primary group is 1 (Administrators), they automatically
        // get permission
        if ( $primary_group == 1 ) return true;
            
        // Go through the users secondary groups
        if ( count( $secondary_groups ) > 0 )
        {
            foreach ( $secondary_groups as $group )
            {
                if ( $group != '' )
                {
                    if ( $group == 1 ) return true;
                }
            }
        }
            
        if ( count( $allowed_groups ) > 0 )
        {
            foreach ( $allowed_groups as $group )
            {
                if ( $group != '' )
                {
                    if ( $group == $primary_group ) return true;
                        
                    if ( count( $secondary_groups ) > 0 )
                    {
                        foreach ( $secondary_groups as $sgroup )
                        {
                            if ( $sgroup != '' )
                            {
                                if ( $group == $sgroup ) return true;
                            }
                        }
                    }
                }
            }
        }
            
        // Go through the allowed users and see if there is permission
        if ( count( $allowed_users ) > 0 )
        {
            foreach ( $allowed_users as $user )
            {
                if ( $user != '' )
                {
                    if ( $user == $this->MEMBER['id'] ) return true;
                }
            }
        }
            
        // We got this far, not good, since access is denied
        return false;
    }
    
    public function get_member_photo( $member_id, $type )
    {
        // Get the member's link
        $found = false;
        
        $r = $this->get_data( 'members', 'member_id' );
        
        if ( $r != false )
        {
            foreach ( $r as $k => $v )
            {
                if ( $v['member_id'] == $member_id )
                {
                    $found               = true;
                    $member_display_name = $v['member_display_name'];
                }
            }
        }
        
        // If member isn't found, link wont exist
        switch ( $found )
        {
            case true:
            $title_lang = str_replace( '%%MEMBERNAME%%', $member_display_name, $this->LANG['view_profile'] );
            
            $this->T = array( 'title' => $title_lang,
                              'member_link' => $this->seo_url( 'members', $member_id ) );
                              
            $member_link_start = $this->THEME->html_member_photo_link_start();
            $member_link_end   = $this->THEME->html_member_photo_link_end();
            break;
            
            case false:
            $member_link_start = '';
            $member_link_end   = '';
            break;
        }
        
        // Do we have permissions?
        switch ( $this->check_feature_permissions( 'member_photos' ) )
        {
            case false:
            // No permissions
            // What type of method are we using?
            switch ( $type )
            {
                case 'photo':
                // Returning a "no photo"...
                $this->T = array( 'link_start' => $member_link_start,
                                  'link_end'   => $member_link_end );
                                  
                return $this->THEME->html_no_photo();
                break;
                
                case 'thumb':
                // Returning a "no photo" thumbnail...
                $this->T = array( 'link_start' => $member_link_start,
                                  'link_end'   => $member_link_end );
                                  
                return $this->THEME->html_no_photo_thumb();
                break;
            }
            break;
        }
        
        // Sort out the directory structure
        if ( substr( $this->CFG['upload_dir'], ( strlen( $this->CFG['upload_dir'] ) ), strlen( $this->CFG['upload_dir'] ) ) == '/' )
        {
            $upload_dir = substr( $this->CFG['upload_dir'], 0, ( strlen( $this->CFG['upload_dir'] ) - 1 ) );
        }
        else
        {
            $upload_dir = $this->CFG['upload_dir'];
        }
        
        if ( substr( $upload_dir, 0, 1 ) == '/' )
        {
            $upload_dir = substr( $upload_dir, 1, strlen( $upload_dir ) );
        }
        
        if ( substr( $this->CFG['upload_photo_dir'], ( strlen( $this->CFG['upload_photo_dir'] ) ), strlen( $this->CFG['upload_photo_dir'] ) ) == '/' )
        {
            $upload_photo_dir = substr( $this->CFG['upload_photo_dir'], 0, ( strlen( $this->CFG['upload_photo_dir'] ) - 1 ) );
        }
        else
        {
            $upload_photo_dir = $this->CFG['upload_photo_dir'];
        }
        
        if ( substr( $upload_photo_dir, 0, 1 ) == '/' )
        {
            $upload_photo_dir = substr( $upload_photo_dir, 1, strlen( $upload_photo_dir ) );
        }
        
        // Put the complete URL together
        $photo_dir = $this->base_url . '/' . $upload_dir . '/' . $upload_photo_dir . '/';
        
        // Put a few settings into easy to use vars for later use
        $photo_max_width  = $this->CFG['member_photo_max_width'];
        $photo_max_height = $this->CFG['member_photo_max_height'];
        $thumb_max_width  = $this->CFG['member_photo_thumb_max_width'];
        $thumb_max_height = $this->CFG['member_photo_thumb_max_height'];
        
        // Which type method are we using?
        switch ( $type )
        {
            case 'photo':
            // Are we resizing photos if they are too large?
            switch ( $this->CFG['member_photo_enable_resizing'] )
            {
                case true:
                // Grab the member's photo from the database
                $display_photo = false;
                
                $r = $this->get_data( 'member_photos', 'photo_id' );
                
                if ( $r != false )
                {
                    foreach ( $r as $k => $v )
                    {
                        if ( $v['photo_member_id'] == $member_id )
                        {
                            // Does this member want to display the photo?
                            switch ( $v['photo_display'] )
                            {
                                case 1:
                                $display_photo  = true;
                                $photo_filename = $v['photo_filename'];
                                break;
                                
                                case 0:
                                $display_photo = false;
                                break;
                            }
                        }
                    }
                }
                
                // Does the user want to display their photo?
                switch ( $display_photo )
                {
                    case true:
                    // We are going to display the photo, but we need to do a few
                    // things first
                    $photo = $photo_dir . $photo;
                    
                    // Get the image sizes
                    list( $photo_width, $photo_height ) = getimagesize( $photo );
                    
                    // Check to make sure that the photo doesnt exceed the max dimensions
                    if ( ( $photo_width > $photo_max_width ) OR ( $photo_height > $photo_max_height ) )
                    {
                        // We need to resize the photo
                        $photo_height = ( $photo_height / $photo_width ) * $photo_max_width;
                        $photo_width  = $photo_max_width;
                    }
                    
                    // Return the photo
                    $this->T = array( 'link_start'   => $member_link_start,
                                      'link_end'     => $member_link_end,
                                      'width'        => $photo_width,
                                      'height'       => $photo_height,
                                      'member_photo' => $photo );
                                      
                    return $this->THEME->html_member_photo();
                    break;
                    
                    case false:
                    // Dont display the photo
                    $this->T = array( 'link_start' => $member_link_start,
                                      'link_end'   => $member_link_end );
                                  
                    return $this->THEME->html_no_photo();
                    break;
                }              
                break;
                
                case false:
                // Image resizing is disabled, just return the image with its
                // original dimensions
                
                break;
            }
            break;
            
            case 'thumb':
            
            break;
        }
    }
}

?>