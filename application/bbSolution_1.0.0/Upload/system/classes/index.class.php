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
        // Get the top header
        $this->BBS->T = array( 'index_url' => $this->BBS->seo_url( 'index' ) );
        $this->BBS->top_header( '', $this->THEME->html_index_navigation(), 'forums' );
        
        // First of all is news enabled?
        // If so, is there even news to display?
        switch ( $this->BBS->CFG['display_news'] )
        {
            case true:
            // Go through all topics, find the topics marked as news.
            // Display the latest one, since its latest news
            $news_found = false;
            
            $r = $this->BBS->get_data( 'topics', 'topic_id' );
            
            if ( $r != false )
            {
                foreach ( $r as $k => $v )
                {
                    if ( $v['topic_news'] == 1 )
                    {
                        $news_found       = true;
                        $news_topic_id    = $v['topic_id'];
                        $news_topic_title = $v['topic_title'];
                        break;
                    }
                }
            }
            
            // If news was found, we need to display it.
            switch ( $news_found )
            {
                case true:
                $this->BBS->T = array( 'topic_title' => $news_topic_title,
                                       'topic_url'   => $this->BBS->seo_url( 'topic', $news_topic_id ) );
                
                $lang_latest_news = $this->LANG['latest_news'];
                $lang_latest_news = str_replace( '%%NEWSLINK%%', $this->THEME->html_news_link(), $lang_latest_news );
                
                $this->BBS->T = array( 'lang_latest_news' => $lang_latest_news );
                
                echo $this->THEME->html_latest_news();
                break;
            }
            break;
        }
        
        // Start the forum depth counter.
        $depth = 0;
        
        // Get the forums from the database.
        $r = $this->BBS->get_data( 'forums', 'forum_id' );
        
        if ( $r != false )
        {
            foreach ( $r as $k => $v )
            {
                // Be sure the forum isn't hidden.
                switch ( $v['forum_hidden'] )
                {
                    case 0:
                    // If depth is 0, it is a category.
                    if ( $v['depth'] == 0 )
                    {
                        // If this is the second or greater category, we need to
                        // output the seperator.
                        if ( $depth > 0 ) echo $this->THEME->html_small_seperator();
                                
                        // Add to the depth counter.
                        $depth++;
                                
                        // Output the category header.
                        $this->BBS->T = array( 'category_id'    => $v['forum_id'],
                                               'category_title' => $v['forum_title'] );
                                                       
                        echo $this->THEME->html_category_header();
                    }
                    elseif ( $v['depth'] == 1 ) // <-- 1 = forum
                    {
                        // Does this forum have an image?
                        if ( ( $v['forum_image'] != '' ) AND ( $this->BBS->url_exist( $v['forum_image'] ) ) )
                        {
                            $this->BBS->T = array( 'forum_image_url' => $v['forum_image'] );
                            $forum_image  = $this->THEME->html_forum_image();
                        }
                        else
                        {
                            $forum_image = '';
                        }
                            
                        // Is this a redirect forum?
                        switch ( $v['forum_redirect'] )
                        {
                            case 1:
                            // Redirect forum.
                            $lang_redirects = $this->LANG['redirects'];
                            $lang_redirects = str_replace( '%%TOTAL%%', number_format( $v['forum_redirect_hits'] ), $lang_redirects );
                                
                            $this->BBS->T = array( 'forum_url'         => $this->BBS->seo_url( 'forum', $v['forum_id'] ),
                                                   'forum_title'       => $v['forum_title'],
                                                   'forum_description' => $v['forum_description'],
                                                   'total_redirects'   => $lang_redirects );
                                                       
                            echo $this->THEME->html_redirect_forum_listing();
                            break;
                                
                            case 0:
                            // Normal forum.
                            // Is this forum archived?
                            switch ( $v['forum_archived'] )
                            {
                                case 1:
                                $forum_icon = $this->THEME->html_forum_icon_archived();
                                break;
                                    
                                case 0:
                                // Depending on if member is logged in or not, check the forum
                                // for any new posts since their last visit to this forum.
                                switch ( $this->BBS->MEMBER['status'] )
                                {
                                    case true:
                                    switch ( $this->BBS->check_for_unread_posts( 'forum', $v['forum_id'] ) )
                                    {
                                        case true:
                                        $forum_icon = $this->THEME->html_forum_icon_new_posts();
                                        break;
                                            
                                        case false:
                                        $forum_icon = $this->THEME->html_forum_icon_no_new_posts();
                                        break;
                                    }
                                    break;
                                        
                                    case false:
                                    $forum_icon = $this->THEME->html_forum_icon_no_new_posts();
                                    break;
                                }
                                break;
                            }
                                
                            $have_subs = false;
                                
                            // Check to see if this forum has any sub forums.
                            $r2 = $this->BBS->get_data( 'forums', 'forum_id' );
                                
                            if ( $r2 != false )
                            {
                                foreach ( $r2 as $key => $val )
                                {
                                    if ( ( ( $val['forum_parent_id'] == $v['forum_id'] ) AND ( $val['forum_hidden'] == 0 ) AND ( $v['forum_show_subs'] == 1 ) ) )
                                    {
                                        $have_subs = true;
                                    }
                                }
                            }
                                
                            switch ( $have_subs )
                            {
                                case true:
                                $x = 0;
                                    
                                // Start of the sub forum.
                                $sub_start = $this->THEME->html_sub_forum_start();
                                    
                                // Get the sub forums from the database.
                                $r2 = $this->BBS->get_data( 'forums', 'forum_id' );
                                    
                                if ( $r2 != false )
                                {
                                    foreach ( $r2 as $key => $val )
                                    {
                                        if ( ( ( $val['forum_parent_id'] == $v['forum_id'] ) AND ( $val['forum_hidden'] == 0 ) AND ( $v['forum_show_subs'] == 1 ) ) )
                                        {
                                            // List the sub forums.
                                            ( $x == 0 ) ? $sep = '' : $sep = ', '; $x++;
                                                
                                            $this->BBS->T = array( 'seperator' => $sep,
                                                                   'forum_title' => $val['forum_title'],
                                                                   'forum_url'   => $this->BBS->seo_url( 'forum', $val['forum_id'] ) );
                                                                       
                                            $subs .= $this->THEME->html_sub_forum_listing();
                                        }
                                    }
                                }
                                    
                                $lang_sub_forums = $this->LANG['sub_forums'];
                                $lang_sub_forums = str_replace( '%%SUBFORUMS%%', $subs, $lang_sub_forums );
                                    
                                $sub_forums = $sub_start . $lang_sub_forums . $this->THEME->html_sub_forum_end();
                                break;
                                    
                                case false:
                                // No sub forums to list.
                                $sub_forums = '';
                                break;
                            }
                                
                            // Get all the forum last post information.
                            if ( $v['forum_last_post_timestamp'] != 0 )
                            {
                                $last_post_timestamp = $this->BBS->parse_timestamp( $v['forum_last_post_timestamp'], true );
                                    
                                if ( $v['forum_last_post_member_id'] == 0 )
                                {
                                    $last_post_user = $this->LANG['guest'];
                                }
                                else
                                {
                                    $last_post_user = $this->BBS->get_member_link( $v['forum_last_post_member_id'] );
                                }
                                    
                                $last_post_user_photo = $this->BBS->get_member_photo( $v['forum_last_post_member_id'], 'thumb' );
                                    
                                // Get the topic information from the database.
                                $r2 = $this->BBS->get_data( 'topics', 'topic_id' );
                                    
                                if ( $r2 != false )
                                {
                                    foreach ( $r2 as $key => $val )
                                    {
                                        if ( $val['topic_id'] == $v['forum_last_post_topic_id'] )
                                        {
                                            $last_post_topic_id    = $val['topic_id'];
                                            $last_post_topic_title = $val['topic_title'];
                                        }
                                    }
                                }
                                    
                                $total_posts = 0;
                                    
                                // Get post information for the topic so we can give the
                                // user a link straight to the newest post.
                                $r2 = $this->BBS->get_data( 'posts', 'post_id' );
                                    
                                if ( $r2 != false )
                                {
                                    foreach ( $r2 as $key => $val )
                                    {
                                        if ( $val['post_topic_id'] == $last_post_topic_id )
                                        {
                                            $total_posts++;
                                        }
                                    }
                                }
                                    
                                $per_page    = $this->BBS->CFG['per_page_posts'];
                                $post_id     = $v['forum_last_post_post_id'];
                                $total_pages = ceil( $total_posts / $per_page );
                                    
                                if ( $total_pages == 0 ) $total_pages = 1;
                                    
                                $lang_by_author = $this->LANG['by_author'];
                                $lang_by_author = str_replace( '%%AUTHOR%%', $last_post_user, $lang_by_author );
                                    
                                // Got the latest post info, now assign it to a var.
                                $this->BBS->T = array( 'user_photo'    => $last_post_user_photo,
                                                       'topic_url'     => $this->BBS->seo_url( 'topic', $last_post_topic_id ),
                                                       'topic_title'   => $last_post_topic_title,
                                                       'last_post_url' => $this->BBS->seo_url( 'topic', $last_post_topic_id, $total_pages ) . '#' . $post_id,
                                                       'by_author'     => $lang_by_author,
                                                       'timestamp'     => $last_post_timestamp );
                                                           
                                $last_post = $this->THEME->html_forum_last_post_info();
                            }
                            else
                            {
                                $last_post = '---';
                            }
                                
                            // Format a few numbers and then output the forum details.
                            $total_topics  = $this->LANG['topics'];
                            $total_replies = $this->LANG['replies'];
                                
                            $total_topics  = str_replace( '%%TOTAL%%', number_format( $v['forum_total_topics'] ), $total_topics );
                            $total_replies = str_replace( '%%TOTAL%%', number_format( $v['forum_total_replies'] ), $total_replies );
                                
                            $this->BBS->T = array( 'forum_icon'            => $forum_icon,
                                                   'forum_image'           => $forum_image,
                                                   'forum_title'           => $v['forum_title'],
                                                   'forum_url'             => $this->BBS->seo_url( 'forum', $v['forum_id'] ),
                                                   'forum_description'     => $v['forum_description'],
                                                   'sub_forums'            => $sub_forums,
                                                   'total_topics'          => $total_topics,
                                                   'total_replies'         => $total_replies,
                                                   'last_post_information' => $last_post );
                                                       
                            echo $this->THEME->html_forum_listing();
                            break;
                        }
                    }
                    break;
                }
            }
            
            echo $this->THEME->html_category_footer();
        }
        
        // If the bulletin board information center enabled and does the
        // current user have permissions to see it?
        switch ( $this->BBS->check_feature_permissions( 'bbic' ) )
        {
            case true:
            // User has permission and its enabled.
            echo $this->THEME->html_bbic_start();
            
            // Permissions for Who's Online?
            switch ( $this->BBS->check_feature_permissions( 'bbic_whos_online' ) )
            {
                case true:
                // Let's find out who is currently online right now.
                $total_online    = 0;
                $total_members   = 0;
                $total_anonymous = 0;
                $total_guests    = 0;
                $total_bots      = 0;
                $x               = 0;
                $online_list     = '';
                
                // Grab session information.
                $q = $this->BBS->DB->db_query( $this->BBS->SQL->sql_select_sessions_online() );
                
                while ( $r = $this->BBS->DB->db_fetch_object( $q ) )
                {
                    // Determine what user, and update counts.
                    if ( ( $r->session_member_id == 0 ) AND ( $r->session_search_bot == 0 ) )
                    {
                        $total_online++;
                        $total_guests++;
                    }
                    elseif ( ( $r->session_member_id == 0 ) AND ( $r->session_search_bot == 1 ) )
                    {
                        $total_online++;
                        $total_bots++;
                        
                        if ( $x == 0 ) { $sep = ''; $x++; } else { $sep = ', '; }
                        
                        $online_list .= $sep . $r->session_search_bot_name;
                    }
                    elseif ( ( ( $r->session_member_id != 0 ) AND ( $r->session_search_bot == 0 ) AND ( $r->session_anonymous == 1 ) ) )
                    {
                        $total_online++;
                        $total_anonymous++;
                    }
                    else
                    {
                        $total_online++;
                        $total_members++;
                        
                        if ( $x == 0 ) { $sep = ''; $x++; } else { $sep = ', '; }
                        
                        $lang_last_activity = $this->LANG['last_activity'];
                        $lang_last_activity = str_replace( '%%TIMESTAMP%%', $this->BBS->parse_timestamp( $r->session_last_click, false, false, true ), $lang_last_activity );
                        
                        $online_list .= $this->BBS->get_member_link( $r->session_member_id, $lang_last_activity, $sep );
                    }
                }
                
                $this->BBS->DB->db_free_result( $q );
                
                if ( $online_list == '' ) $online_list = $this->LANG['no_members_online'];
                
                // Get the group legend.
                $group_legend = '';
                $x            = 0;
                
                $r = $this->BBS->get_data( 'groups', 'group_id' );
                
                if ( $r != false )
                {
                    foreach ( $r as $k => $v )
                    {
                        if ( $v['group_display'] == 1 )
                        {
                            if ( $x == 0 ) { $sep = ''; $x++; } else { $sep = ', '; }
                            
                            $group_legend .= $this->BBS->get_group_link( $v['group_id'], '', $sep );
                        }
                    }
                }
                
                if ( $group_legend == '' ) $group_legend = $this->LANG['no_groups'];
                
                // Format all the numbers.
                $total_online    = number_format( $total_online );
                $total_members   = number_format( $total_members );
                $total_guests    = number_format( $total_guests );
                $total_anonymous = number_format( $total_anonymous );
                $total_bots      = number_format( $total_bots );
                
                // Replace a few vars within our language files.
                $lang_currently_online = $this->LANG['currently_online'];
                $lang_online_list      = $this->LANG['online_list'];
                $lang_members_online   = $this->LANG['members_online'];
                $lang_group_legend     = $this->LANG['group_legend'];
                
                $lang_currently_online = str_replace( '%%TOTAL%%', $total_online, $lang_currently_online );
                $lang_currently_online = str_replace( '%%TIMEOUT%%', $this->BBS->CFG['session_timeout'], $lang_currently_online );
                $lang_online_list      = str_replace( '%%MEMBERS%%', $total_members, $lang_online_list );
                $lang_online_list      = str_replace( '%%ANONYMOUS%%', $total_anonymous, $lang_online_list );
                $lang_online_list      = str_replace( '%%GUESTS%%', $total_guests, $lang_online_list );
                $lang_online_list      = str_replace( '%%BOTS%%', $total_bots, $lang_online_list );
                $lang_group_legend     = str_replace( '%%LEGEND%%', $group_legend, $lang_group_legend );
                $lang_members_online   = str_replace( '%%ONLINELIST%%', $online_list, $lang_members_online );
                
                $this->BBS->T = array( 'currently_online' => $lang_currently_online,
                                       'online_list'      => $lang_online_list,
                                       'members_online'   => $lang_members_online,
                                       'group_legend'     => $lang_group_legend );
                                       
                echo $this->THEME->html_bbic_whos_online();
                break;
            }
            
            // Permission for Statistics?
            switch ( $this->BBS->check_feature_permissions( 'bbic_statistics' ) )
            {
                case true:
                // Get the statistic information from the database.
                $r = $this->BBS->get_data( 'statistics', 'statistic_id' );
                
                if ( $r != false )
                {
                    foreach ( $r as $k => $v )
                    {
                        if ( $v['statistic_id'] == 1 )
                        {                            
                            $lang_newest_member     = $this->LANG['newest_member'];
                            $lang_quick_stats       = $this->LANG['quick_stats'];
                            $lang_most_users_online = $this->LANG['most_users_online'];
                            
                            $lang_newest_member     = str_replace( '%%MEMBER%%', $this->BBS->get_member_link( $v['statistic_newest_member_id'] ), $lang_newest_member );
                            $lang_quick_stats       = str_replace( '%%MEMBERS%%', number_format( $v['statistic_total_members'] ), $lang_quick_stats );
                            $lang_quick_stats       = str_replace( '%%POSTS%%', number_format( $v['statistic_total_posts'] ), $lang_quick_stats );
                            $lang_quick_stats       = str_replace( '%%TOPICS%%', number_format( $v['statistic_total_topics'] ), $lang_quick_stats );
                            $lang_quick_stats       = str_replace( '%%REPLIES%%', number_format( $v['statistic_total_replies'] ), $lang_quick_stats );
                            $lang_most_users_online = str_replace( '%%TOTAL%%', number_format( $v['statistic_online_record'] ), $lang_most_users_online );
                            $this->BBS->T           = array( 'timestamp' => $this->BBS->parse_timestamp( $v['statistic_online_record_timestamp'], true ) );
                            $lang_most_users_online = str_replace( '%%TIMESTAMP%%', $this->THEME->html_timestamp_text(), $lang_most_users_online );
                            
                            // Output the statistics.
                            $this->BBS->T = array( 'newest_member' => $lang_newest_member,
                                                   'quick_stats'   => $lang_quick_stats,
                                                   'most_users'    => $lang_most_users_online );
                                                   
                            echo $this->THEME->html_bbic_statistics();
                        }
                     }
                }
                break;
            }
            
            // Permission for Member Birthdays?
            switch ( $this->BBS->check_feature_permissions( 'bbic_birthdays' ) )
            {
                case true:
                // Find out who is celebrating a birthday.
                $birthday_list   = '';
                $total_birthdays = 0;
                $x               = 0;
                
                $month = date( 'n', time() );
                $day   = date( 'j', time() );
                
                // Get the member's birthday information from the database.
                $r = $this->BBS->get_data( 'members', 'member_id' );
                
                if ( $r != false )
                {
                    foreach ( $r as $k => $v )
                    {
                        if ( ( $v['member_dob_month'] == $month ) AND ( $v['member_dob_day'] == $day ) )
                        {
                            // Get the member's current age.
                            ( $v['member_show_age'] == 1 ) ? $age = ' (' . $this->BBS->calculate_age( $v['member_dob_month'], $v['member_dob_day'], $v['member_dob_year'] ) . ')' : $age = '';
                            
                            if ( $x == 0 ) { $sep = ''; $x++; } else { $sep = ', '; }
                            
                            $birthday_list .= $this->BBS->get_member_link( $v['member_id'], '', $sep ) . $age;
                            
                            $total_birthdays++;
                        }
                    }
                }
                
                if ( $total_birthdays == 0 ) $birthday_list = $this->LANG['no_birthdays'];
                
                $lang_current_birthdays = $this->LANG['current_birthdays'];
                
                $lang_current_birthdays = str_replace( '%%TOTAL%%', number_format( $total_birthdays ), $lang_current_birthdays );
                
                $this->BBS->T = array( 'current_birthdays' => $lang_current_birthdays,
                                       'birthday_list'     => $birthday_list );
                                       
                echo $this->THEME->html_bbic_birthdays();
                break;
            }
            break;
        }
        
        // Bottom footer.
        $this->BBS->bottom_footer();
    }
}

?>