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
 * index
 * 
 * @package   bbSolution PHP Bulletin Board System
 * @author    Sam Wilcox <sam@bb-solution.org>
 * @copyright 2014 bbSolution. All Rights Reserved.
 * @version   CVS: $Id:$
 * @access    public
 */
class index
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
        require( $this->BBS->lang_path . 'index.lang.' . $this->BBS->php_ext );
        $this->LANG = $LANG;
        
        // Load the theme for this class
        require_once( $this->BBS->theme_path . 'index.theme.' . $this->BBS->php_ext );
        $this->THEME       = new BBSolutionThemeIndex;
        $this->THEME->BBS  =& $this->BBS;
        $this->THEME->LANG = $this->LANG;
        
        // What are we doing?
        switch ( $this->BBS->INC['to_do'] )
        {
            default:
            $this->display_forums_list();
            break;
        }       
    }
    
    private function display_forums_list()
    {
        echo 'It is a workin!';
    }
}

?>