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
 * authenticate
 * 
 * @package   bbSolution PHP Bulletin Board System
 * @author    Sam Wilcox <sam@bb-solution.org>
 * @copyright 2014 bbSolution. All Rights Reserved.
 * @version   CVS: $Id:$
 * @access    public
 */
 class authenticate
 {
    /**
     * Master Class Object
     * @var Object $BBS
     */
     public $BBS;
     
    /**
     * Language Words
     * @var Array $LANG
     */
     public $LANG = array();
     
    public function start_now()
    {
        // Load the language file for this class
        $LANG = array();
        require( $this->BBS->lang_path . 'authenticate.lang.' . $this->BBS->php_ext );
        $this->LANG = $LANG;
        
        // Load the theme for this class
        require_once( $this->BBS->theme_path . 'authenticate.theme.' . $this->BBS->php_ext );
        $this->THEME       = new BBSolutionThemeAuthenticate;
        $this->THEME->BBS  =& $this->BBS;
        $this->THEME->LANG = $this->LANG;
        
        // What are we doing?
        switch ( $this->BBS->INC['to_do'] )
        {
            default:
            $this->member_login_form();
            break;
            
            case 'plogin':
            $this->process_member_login();
            break;
        }       
    }
    
    private function member_login_form( $error = '', $username = '', $auto = '', $anon = '' )
    {
        // Get the top header.
        $this->BBS->top_header( $this->LANG['login'], $this->LANG['login'], '' );
        
        // Get the form token.
        $form_token = $this->BBS->get_form_token();
        
        // Is Facebook Connect enabled?
        switch ( $this->BBS->check_feature_permissions( 'facebook_api' ) )
        {
            case true:
            $this->BBS->T = array( 'button_text' => $this->LANG['facebook_connect'] );
            
            $facebook_connect_link = $this->THEME->html_facebook_connect_button();
            break;
            
            case false:
            $facebook_connect_link = '';
            break;
        }
        
        // Did we get called on error?
        // If so, we need to handle it correctly.        
        if ( $error != '' )
        {
            $this->BBS->T = array( 'error_message' => $error );
            
            $error_box = $this->THEME->html_login_form_error_box();
            
            if ( $auto != '' )
            {
                ( $auto == 1 ) ? $auto_checked = $this->THEME->html_login_form_checked() : $auto_checked = '';
            }
            else
            {
                $auto_checked = '';
            }
            
            if ( $anon != '' )
            {
                ( $anon == 1 ) ? $anon_checked = $this->THEME->html_login_form_checked() : $anon_checked = '';
            }
            else
            {
                $anon_checked = '';
            }
        }
        else
        {
            $error_box    = '';
            $auto_checked = '';
            $anon_checked = '';
        }
        
        // Replace a var within our language file.
        $lang_not_member = $this->LANG['not_member'];
        $lang_not_member = str_replace( '%%CREATELINK%%', $this->THEME->html_create_account_link(), $lang_not_member );
        
        // Output the login form to the user.
        $this->BBS->T = array( 'facebook_connect' => $facebook_connect_link,
                               'form_token'       => $form_token,
                               'not_member'       => $lang_not_member,
                               'error_box'        => $error_box,
                               'auto'             => $auto_checked,
                               'anon'             => $anon_checked );
                               
        echo $this->THEME->html_login_form();
        
        // Get the footer.
        $this->BBS->bottom_footer();
    }
    
    private function process_member_login()
    {
        // Get our incoming data.
        $username   = $this->BBS->INC['username'];
        $password   = $this->BBS->INC['password'];
        $auto_login = $this->BBS->INC['rememberme'];
        $anonymous  = $this->BBS->INC['anonymous'];
        
        // First, validate the form token.
        switch ( $this->BBS->validate_form_token( true ) )
        {
            case false:
            $this->member_login_form( $this->BBS->ERRORS['invalid_form_token'], $username, $auto_login, $anonymous );
            exit();
            break;
        }
        
        // Get data from the database and validate the user.
        $found = false;
        
        $r = $this->BBS->get_data( 'members', 'member_id' );
        
        if ( $r != false )
        {
            foreach ( $r as $k => $v )
            {
                if ( ( $v['member_username'] == $username ) OR ( $v['member_email_address'] == $username ) )
                {
                    $found = true;
                    $member_id           = $v['member_id'];
                    $member_username     = $v['member_username'];
                    $member_display_name = $v['member_display_name'];
                    $member_password     = $v['member_password'];
                    $member_salt         = $v['member_salt'];
                    $login_attempts      = $v['member_login_attempts'];
                    $account_locked      = $v['member_login_attempts_locked'];
                    $locked_timestamp    = $v['member_login_attempts_timestamp'];
                    break;
                }
            }
        }
        
        // Was the member found?
        // If not, throw an error.
        switch ( $found )
        {
            case false:
            $this->member_login_form( $this->BBS->ERRORS['login_invalid_username'], '', $auto_login, $anonymous );
            exit();
            break;
        }
        
        // Check the member's password.
        $found = false;
        
        $enc_password = md5( $password . $member_salt );
        
        if ( $enc_password != $member_password )
        {
            // Checking for total login attempts if enabled.            
            switch ( $this->BBS->CFG['monitor_login_attempts'] )
            {
                case true:
                $locked_duration = ( time() + ( $this->BBS->CFG['login_lockout_period'] * 60 ) );
                
                if ( $account_locked == 1 )
                {
                    if ( $locked_timestamp > time() )
                    {
                        $time_now   = time();
                        $difference = $locked_timestamp - $time_now;
                        $minutes    = floor( ( $difference % 3600 ) / 60 );
                        
                        $lang_account_locked = $this->BBS->ERRORS['login_account_locked'];
                        $lang_account_locked = str_replace( '%%MINUTES%%', $minutes, $lang_account_locked );
                        
                        $this->member_login_form( $lang_account_locked, $username, $auto_login, $anonymous );
                        exit();
                    }
                }
                else
                {
                    // Check to see if we need to lock the account.
                    $login_attempts++;
                    
                    if ( $login_attempts > $this->BBS->CFG['max_login_attempts'] )
                    {
                        // Exceeded the max logina attempts, lock out the account.
                        $this->BBS->TOSQL = array( 'member_id'       => $member_id,
                                                   'locked_duration' => $locked_duration );
                        
                        $this->BBS->DB->db_query( $this->BBS->SQL->sql_update_members_lockout() );
                        
                        // Update the cache.
                        $this->BBS->update_database_cache( 'members', 'member_id' );
                    }
                    else
                    {
                        // Havent reached the max login attempts yet,
                        // just increment the total attempts.
                        $this->BBS->TOSQL = array( 'member_id'      => $member_id,
                                                   'login_attempts' => $login_attempts );
                        
                        $this->BBS->DB->db_query( $this->BBS->SQL->sql_update_members_lockout_attempts() );
                        
                        // Update the cache.
                        $this->BBS->update_database_cache( 'members', 'member_id' );
                    }
                }
                
                // Throw an error.
                $lang_invalid_password = $this->BBS->ERRORS['login_invalid_password_la'];
                $lang_invalid_password = str_replace( '%%CURRENTTOTAL%%', $login_attempts, $lang_invalid_password );
                $lang_invalid_password = str_replace( '%%TOTAL%%', $this->BBS->CFG['max_login_attempts'], $lang_invalid_password );
                
                $this->member_login_form( $lang_invalid_password, $username, $auto_login, $anonymous );
                exit();
                break;
                
                case false:
                // Throw an error.
                $this->member_login_form( $this->BBS->ERRORS['login_invalid_password'], $username, $auto_login, $anonymous );
                exit();
                break;
            }
        }
        
        // See if we need to clear any locked out accounts.
        switch ( $this->BBS->CFG['monitor_login_attempts'] )
        {
            case true:
            // Is the account locked?
            if ( $account_locked == 1 )
            {
                // Check it against the current time.
                if ( $locked_timestamp <= time() )
                {
                    // Unlock the account.
                    $this->BBS->TOSQL = array( 'member_id' => $member_id );
                    
                    $this->BBS->DB->db_query( $this->BBS->SQL->sql_update_members_clear_lockout() );
                    
                    // Update the cache.
                    $this->BBS->update_database_cache( 'members', 'member_id' );
                }
                else
                {
                    // Account still locked.
                    $time_now   = time();
                    $difference = $locked_timestamp - $time_now;
                    $minutes    = floor( ( $difference % 3600) / 60 );
                    
                    $lang_account_locked = $this->BBS->ERRORS['login_account_locked'];
                    $lang_account_locked = str_replace( '%%MINUTES%%', $minutes, $lang_account_locked );
                        
                    $this->member_login_form( $lang_account_locked, $username, $auto_login, $anonymous );
                    exit();
                }
            }
            else
            {
                if ( $login_attempts > 0 )
                {
                    $this->BBS->TOSQL = array( 'member_id' => $member_id );
                    
                    $this->BBS->DB->db_query( $this->BBS->SQL->sql_update_members_clear_login_attempts() );
                    
                    // Update the cache.
                    $this->BBS->update_database_cache( 'members', 'member_id' ); 
                }
            }
            break;
        }
        
        // Member is authenticated!
        // Set the session details.
        $_SESSION['bbs_username'] = $member_username;
        
        $this->BBS->MEMBER['id']           = $member_id;
        $this->BBS->MEMBER['username']     = $member_username;
        $this->BBS->MEMBER['display_name'] = $member_display_name;
        
        // Setup session durations, tokens, etc.
        $dur             = $this->BBS->CFG['session_timeout'];
        $dur             = ( $dur * 60 );
        $session_expires = ( time() + $dur );
        
        // Does the member want to automatically login on their next visit?
        if ( $auto_login == 1 )
        {
            $login_token = $this->BBS->generate_login_token( $member_username );
            $expires     = ( time() + 60 * 60 * 24 * 365 );
            $this->BBS->new_cookie( 'bbs_login_token', $login_token, $expires );
            
            // Update the members table.
            $this->BBS->TOSQL = array( 'member_id' => $member_id,
                                       'token'     => $login_token,
                                       'time'      => time() );
                                       
            $this->BBS->DB->db_query( $this->BBS->SQL->sql_update_members_login_token() );
            
            // Update the cache.
            $this->BBS->update_database_cache( 'members', 'member_id' );
            
            // Is the user logging in as anonymous?
            if ( $anonymous == 1 )
            {
                $this->BBS->new_cookie( 'bbs_anonymous', true, $expires );
                $anonymous = 1;
            }
            else
            {
                $anonymous = 0;
            }
        }
        else
        {
            $login_token = $this->BBS->generate_login_token( $member_username );
            $expires     = $session_expires;
            $this->BBS->new_cookie( 'bbs_login_token', $login_token, $expires );
            
            $this->BBS->TOSQL = array( 'member_id' => $member_id,
                                       'token'     => $login_token,
                                       'time'      => time() );
                                       
            $this->BBS->DB->db_query( $this->BBS->SQL->sql_update_members_login_token() );
            
            // Update the cache.
            $this->BBS->update_database_cache( 'members', 'member_id' );
            
            // Is the user logging in as anonymous?
            if ( $anonymous == 1 )
            {
                $this->BBS->new_cookie( 'bbs_anonymous', true, $expires );
                $anonymous = 1;
            }
            else
            {
                $anonymous = 0;
            }
        }
        
        // Update the sessions table with this new information.
        $this->BBS->TOSQL = array( 'member_id'       => $member_id,
                                   'member_username' => $member_username,
                                   'expires'         => $expires,
                                   'time'            => time(),
                                   'anonymous'       => $anonymous );
                                   
        $this->BBS->DB->db_query( $this->BBS->SQL->sql_update_sessions_login() );
        
        header( "Location: " . $this->BBS->script_url );
        exit();
    }
 }

?>