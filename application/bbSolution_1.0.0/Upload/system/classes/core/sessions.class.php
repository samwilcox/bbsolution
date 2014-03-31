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
 * BBSolutionSessions
 * 
 * @package   bbSolution PHP Bulletin Board System
 * @author    Sam Wilcox <sam@bb-solution.org>
 * @copyright 2014 bbSolution. All Rights Reserved.
 * @version   CVS: $Id:$
 * @access    public
 */
class BBSolutionSessions
{
    /**
     * Master Class Object
     * @var Object $BBS
     */
     public $BBS;
    
    /**
     * Session Duration
     * @var Integer $_session_duration
     */
     private $_session_duration = 15;
     
    /**
     * Session IP Matching
     * @var Boolean $_session_ip_matching
     */
     private $_session_ip_matching = false;
     
    /**
     * Session Lifetime
     * @var Integer $_session_lifetime
     */
     private $_session_lifetime = 0;
     
    /**
     * BBSolutionSessions::manage_sessions()
     * 
     * Manages both active sessions, and records them into the database for
     * improved security. Also sets up the session data storage routine,
     * depending on the application setting.
     * 
     * @return null
     */
    public function manage_sessions()
    {
        // Set class vars
        $this->_session_duration    = ( $this->BBS->CFG['session_timeout'] * 60 );
        $this->_session_ip_matching = $this->BBS->CFG['session_ip_match'];
        
        // Determine which session storage routine we are using
        switch ( $this->BBS->CFG['session_store_method'] )
        {
            case 'dbstore':
            // Saving session data into the database
            // Set the default session lifetime to the PHP default
            $this->_session_lifetime = get_cfg_var( 'session.gc_maxlifetime' );
            
            // Set the system session save handler
            session_set_save_handler( array( &$this, 'session_store_open' ),
                                      array( &$this, 'session_store_close' ),
                                      array( &$this, 'session_store_read' ),
                                      array( &$this, 'session_store_write' ),
                                      array( &$this, 'session_store_destroy' ),
                                      array( &$this, 'session_store_gc' ) );
                                      
            // Start the session
            session_start();
            break;
            
            case 'filestore':
            // Using the default PHP session storage routine
            // Start the session
            session_start();
            break;
        }
        
        // Get the session ID
        $this->BBS->SESSION['id'] = session_id();
        
        // Check to see if a member cookie exists with the member
        // login token. If one exists, we will log them in
        // automatically. If one does not exist, all session
        // data will be destroyed. Now if there is no member 
        // cookie, we will either create a new session or update
        // an existing one
        if ( isset( $_COOKIE['bbs_login_token'] ) )
        {
            // Member cookie exists
            $token = $_COOKIE['bbs_login_token'];
            
            $found = false;
            
            // Grab the member's information
            $r = $this->BBS->getData( 'members', 'member_id' );
            
            if ( $r != false )
            {
                foreach ( $r as $k => $v )
                {
                    if ( $v['member_token'] == $token )
                    {
                        $found               = true;
                        $member_id           = $v['member_id'];
                        $member_username     = $v['member_username'];
                        $member_password     = $v['member_password'];
                        $member_display_name = $v['member_display_name'];
                    }
                }
            }
            
            // Depending if the member was found, perform the
            // needed actions
            switch ( $found )
            {
                case true:
                // Member token found, grab data
                $this->BBS->TOSQL = array( 'member_id' => $member_id );
                
                $q = $this->BBS->DB->db_query( $this->BBS->SQL->sql_select_session_id() );
                
                $num = $this->BBS->DB->db_num_rows( $q );
                $r   = $this->BBS->DB->db_fetch_object( $q );
                
                $this->BBS->DB->db_num_rows( $q );
                
                // Was a session found?
                switch ( $num )
                {
                    case 1:
                    // Session found, are we ip matching?
                    switch ( $this->_session_ip_matching )
                    {
                        case true:
                        // We are ip matching. Compare the users ip and agent to what is in
                        // the database
                        if ( ( $r->session_ip_address != $this->BBS->AGENT['ip_address'] ) OR ( $r->session_user_agent != $this->BBS->AGENT['agent'] ) )
                        {
                            // IP and/or user agent doesnt match, destroy the session
                            session_unset();
                            session_destroy();
                            
                            if ( isset( $_COOKIE[session_name()] ) )
                            {
                                $this->BBS->delete_cookie( session_name(), true );
                            }
                            
                            $this->BBS->delete_cookie( 'bbs_login_token' );
                            $this->BBS->delete_cookie( 'bbs_anonymous' );
                            
                            // Update the members table, delete the login token
                            $this->BBS->TOSQL = array( 'member_id' => $member_id );
                            $this->BBS->DB->db_query( $this->BBS->SQL->sql_update_members_delete_token() );
                            
                            // Update the database cache
                            $this->BBS->update_database_cache( 'members', 'member_id' );
                            
                            // Remove the session from the database
                            $this->delete_user_session();
                            
                            unset( $_SESSION['bbs_username'] );
                            
                            // Regenerate to get a brand new session ID
                            session_regenerate_id( true );
                            
                            // Redirect the user to the main page
                            header( "Location: " . $this->BBS->seo_url( 'index' ) );
                            exit();
                        }
                        else
                        {
                            // IP's match, update the info
                            $this->BBS->SESSION['expires']     = ( time() + $this->_session_duration );
                            $this->BBS->SESSION['last_click']  = time();
                            $this->BBS->SESSION['location']    = $this->BBS->AGENT['uri'];
                            $this->BBS->MEMBER['id']           = $member_id;
                            $this->BBS->MEMBER['username']     = $member_username;
                            $this->BBS->MEMBER['display_name'] = $member_display_name;
                            
                            // Update the info in the database
                            $this->update_user_session();
                        }
                        break;
                        
                        case false:
                        // No IP matching, just update the info
                        $this->BBS->SESSION['expires']     = ( time() + $this->_session_duration );
                        $this->BBS->SESSION['last_click']  = time();
                        $this->BBS->SESSION['location']    = $this->BBS->AGENT['uri'];
                        $this->BBS->MEMBER['id']           = $member_id;
                        $this->BBS->MEMBER['username']     = $member_username;
                        $this->BBS->MEMBER['display_name'] = $member_display_name;
                        
                        // Update the info in the database
                        $this->update_user_session();
                        break;
                    }
                    break;
                    
                    case 0:
                    // Session isnt found in the database. Log the user into the
                    // system
                    $this->BBS->SESSION['expires']       = ( time() + $this->_session_duration );
                    $this->BBS->SESSION['last_click']    = time();
                    $this->BBS->SESSION['location']      = $this->BBS->AGENT['uri'];
                    $this->BBS->SESSION['admin_session'] = 0;
                    $this->BBS->MEMBER['id']             = $member_id;
                    $this->BBS->MEMBER['username']       = $member_username;
                    $this->BBS->MEMBER['display_name']   = $member_display_name;
                    
                    // Did the user want to be anonymous on the who's online list?
                    switch ( $_COOKIE['bbs_anonymous'] )
                    {
                        case true:
                        $this->BBS->MEMBER['anonymous'] = 1;
                        break;
                        
                        case false:
                        $this->BBS->MEMBER['anonymous'] = 0;
                        break;
                    }
                    
                    // Create the session in the database
                    $this->new_user_session();
                    
                    // Set the username in the session
                    $_SESSION['bbs_username'] = $member_username;
                    break;
                }
                break;
                
                case false:
                // Member not found, destroy the session
                session_unset();
                session_destroy();
                
                if ( isset( $_COOKIE['bbs_login_token'] ) )
                {
                    $this->BBS->delete_cookie( session_name(), true );
                }
                
                $this->BBS->delete_cookie( 'bbs_login_token' );
                $this->BBS->delete_cookie( 'bbs_anonymous' );
                
                // Delete the session from the database
                $this->delete_user_session();
                
                unset( $_SESSION['bbs_username'] );
                
                // Regenerate the session ID
                session_regenerate_id( true );
                
                // Redirect the user
                header( "Location: " . $this->BBS->seo_url( 'index' ) );
                exit();
                break;
            }
        }
        else
        {
            // No login token found, user is a guest
            // See if a session exists, if so update,
            // and if not, create
            $q = $this->BBS->DB->db_query( $this->BBS->SQL->sql_select_all_session_by_id() );
            
            $num = $this->BBS->DB->db_num_rows( $q );
            $r   = $this->BBS->DB->db_fetch_object( $q );
            
            $this->BBS->DB->db_free_result( $q );
            
            // Was a session found?
            switch ( $num )
            {
                case 1:
                // Session found, are we IP matching?
                switch ( $this->_session_ip_matching )
                {
                    case true:
                    // We are IP matching, compare
                    if ( ( $r->session_ip_address != $this->BBS->AGENT['ip_address'] ) OR ( $r->session_user_agent != $this->BBS->AGENT['agent'] ) )
                    {
                        // IP or agent does not match, destroy the session
                        session_unset();
                        session_destroy();
                        
                        if ( isset( $_COOKIE[session_name()] ) )
                        {
                            $this->BBS->delete_cookie( session_name(), true );
                        }
                        
                        // Remove the session from the database
                        $this->delete_user_session();
                        
                        unset( $_SESSION['bbs_username'] );
                        
                        // Regenerate the session ID
                        session_regenerate_id( true );
                        
                        // Redirect the user
                        header( "Location: " . $this->BBS->seo_url( 'index' ) );
                        exit();
                    }
                    else
                    {
                        // IP's match, update the info
                        $this->BBS->SESSION['expires']    = ( time() + $this->_session_duration );
                        $this->BBS->SESSION['last_click'] = time();
                        $this->BBS->SESSION['location']   = $this->BBS->AGENT['uri'];
                        
                        unset( $_SESSION['bbs_username'] );
                        
                        // Update the session in the database
                        $this->update_user_session();
                    }
                    break;
                    
                    case false:
                    // No IP matching, just update the info
                    $this->BBS->SESSION['expires']    = ( time() + $this->_session_duration );
                    $this->BBS->SESSION['last_click'] = time();
                    $this->BBS->SESSION['location']   = $this->BBS->AGENT['uri'];
                        
                    unset( $_SESSION['bbs_username'] );
                        
                    // Update the session in the database
                    $this->update_user_session();
                    break;
                }
                break;
                
                case 0:
                // No session found, create a brand new one
                $this->BBS->MEMBER['id']             = 0;
                $this->BBS->MEMBER['username']       = 'Guest';
                $this->BBS->MEMBER['display_name']   = 'Guest';
                $this->BBS->MEMBER['anonymous']      = 0;
                $this->BBS->SESSION['expires']       = ( time() + $this->_session_duration );
                $this->BBS->SESSION['last_click']    = time();
                $this->BBS->SESSION['location']      = $this->BBS->AGENT['uri'];
                $this->BBS->SESSION['admin_session'] = 0;
                
                unset( $_SESSION['bbs_username'] );
                
                // Create the new session in the database
                $this->new_user_session();
                break;
            }
        }
    }
    
    /**
     * BBSolutionSessions::new_user_session()
     * 
     * Creates a brand new record in the sessions table
     * 
     * @return null
     */
    private function new_user_session()
    {
        $this->BBS->DB->db_query( $this->BBS->SQL->sql_insert_new_session() );
    }
    
    /**
     * BBSolutionSessions::update_user_session()
     * 
     * Updates a record in the sessions table
     * 
     * @return null
     */
    private function update_user_session()
    {
        $this->BBS->DB->db_query( $this->BBS->SQL->sql_update_session() );
    }
    
    /**
     * BBSolutionSessions::delete_user_session()
     * 
     * Deletes a record in the sessions table
     * 
     * @return null
     */
    private function delete_user_session()
    {
        $this->BBS->DB->db_query( $this->BBS->SQL->sql_delete_session() );
    }
    
    /**
     * BBSolutionSessions::session_garbage_collection()
     * 
     * Checks to see if there is any old sessions that have expired
     * and removes them from the database
     * 
     * @return null
     */
    public function session_garbage_collection()
    {
        $this->BBS->TOSQL = array( 'time' => time() );
        $this->BBS->DB->db_query( $this->BBS->SQL->sql_delete_garbage_collection() );
    }
    
    /**
     * BBSolutionSessions::session_store_open()
     * 
     * Called by the PHP session system on session open
     * 
     * @return Boolean
     */
    public function session_store_open()
    {
        return true;
    }
    
    /**
     * BBSolutionSessions::session_store_close()
     * 
     * Called by the PHP session system on session close
     * 
     * @return Boolean
     */
    public function session_store_close()
    {
        return true;
    }
    
    /**
     * BBSolutionSessions::session_store_read()
     * 
     * Called by the PHP session system on session read
     * 
     * @param Integer $id
     * @return Boolean
     */
    public function session_store_read( $id )
    {
        // A few initial vars
        $data = '';
        $time = time();
        
        // Select session data from the database
        $this->BBS->TOSQL = array( 'id'   => $id,
                                   'time' => time() );
                                   
        $q = $this->BBS->DB->db_query( $this->BBS->SQL->sql_select_session_store_data() );
        
        // Grab the data and get it ready to be returned to the system
        if ( $this->BBS->DB->db_num_rows( $q ) > 0 )
        {
            $r    = $this->BBS->DB->db_fetch_array( $q );
            $data = $r['session_data'];
        }
        
        // Free up memory
        $this->BBS->DB->db_free_result( $q );
        
        // Return data to the system
        return $data;
    }
    
    /**
     * BBSolutionSessions::session_store_write()
     * 
     * Called by the PHP session system on session write
     * 
     * @param Integer $id
     * @param String $data
     * @return Boolean
     */
    public function session_store_write( $id, $data )
    {
        // Determine the session expiration
        $time = time() + $this->_session_lifetime;
        
        // Get the session ID from the database
        $this->BBS->TOSQL = array( 'id' => $id );
        
        $q = $this->BBS->DB->db_query( $this->BBS->SQL->sql_select_session_store_id() );
        
        // Get the number of rows, if > 0, we already have an
        // existing record
        $num = $this->BBS->DB->db_num_rows( $q );
        $this->BBS->DB->db_free_result( $q );
        
        // If a record already exists, update it. If no record
        // exists, create a brand new one
        if ( $num == 0 )
        {
            // Record does not exist, insert a new one
            $this->BBS->TOSQL = array( 'id'       => $id,
                                       'data'     => $data,
                                       'lifetime' => $this->_session_lifetime );
                                       
            $this->BBS->DB->db_query( $this->BBS->SQL->sql_insert_session_store() );
        }
        else
        {
            // Record does exist, just update the existing record
            $this->BBS->TOSQL = array( 'id'       => $id,
                                       'data'     => $data,
                                       'lifetime' => $this->_session_lifetime );
                                       
            $this->BBS->DB->db_query( $this->BBS->SQL->sql_update_session_store() );
        }
        
        // Everything went good if we got here, let the
        // system know everything is good
        return true;
    }
    
    /**
     * BBSolutionSessions::session_store_destroy()
     * 
     * Called by the PHP session system on session destroy
     * 
     * @param Integer $id
     * @return Boolean
     */
    public function session_store_destroy( $id )
    {
        // Delete the record
        $this->BBS->TOSQL = array( 'id' => $id );
        $this->BBS->DB->db_query( $this->BBS->SQL->sql_delete_session_store() );
        
        // Return the result
        return true;
    }
    
    /**
     * BBSolutionSessions::session_store_gc()
     * 
     * Called by the PHP session system on sesson garbage collection
     * 
     * @return Boolean
     */
    public function session_store_gc()
    {
        // Clean up any lingering session data
        $this->BBS->DB->db_query( $this->BBS->SQL->sql_delete_session_store_gc() );
        
        // Return the result
        return true;
    }
    
    /**
     * BBSolutionSessions::check_online_record()
     * 
     * Checks the online record number of users compared to the current
     * online users and updates the record is the number of online
     * users is greater than the current record
     * 
     * @return null
     */
    public function check_online_record()
    {
        // Grab our statistic information from the database
        $r = $this->BBS->get_data( 'statistics', 'statistic_id' );
        
        if ( $r != false )
        {
            foreach ( $r as $k => $v )
            {
                // Get the current record number
                $current_record = $v['statistic_online_record'];
            }
        }
        
        // Grab the current total sessions and then compare it to our
        // current online record to see if we have a new record or
        // not
        $q = $this->BBS->DB->db_query( $this->BBS->SQL->sql_select_all_sessions() );
        
        $online = $this->BBS->DB->db_num_rows( $q );
        
        $this->BBS->DB->db_free_result( $q );
        
        // Compare them against each other, who will win ya think?
        if ( $online > $current_record )
        {
            // Got a new record, update the info
            $this->BBS->DB->db_query( $this->BBS->SQL->sql_update_statistics_online_record() );
            
            // Update the database cache
            $this->BBS->update_database_cache( 'statistics', 'statistic_id' );
        }
    }
}

?>