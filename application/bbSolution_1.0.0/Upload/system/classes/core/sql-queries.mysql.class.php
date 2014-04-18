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
 * BBSolutionSQL
 * 
 * @package   bbSolution PHP Bulletin Board System
 * @author    Sam Wilcox <sam@bb-solution.org>
 * @copyright 2014 bbSolution. All Rights Reserved.
 * @version   CVS: $Id:$
 * @access    public
 */
 class BBSolutionSQL
 {
 
/**
 * Master Class Object
 * @var Object $BBS
 */
 public $BBS;

/**
 * BBSolutionSQL::sql_insert_new_session()
 * 
 * @return String
 */
public function sql_insert_new_session()
{
return <<<EOF
INSERT INTO {$this->BBS->db_prefix}sessions
VALUES (
'{$this->BBS->DB->db_cleaner( $this->BBS->SESSION['id'] )}',
'{$this->BBS->DB->db_cleaner( $this->BBS->MEMBER['id'] )}',
'{$this->BBS->DB->db_cleaner( $this->BBS->MEMBER['username'] )}',
'{$this->BBS->DB->db_cleaner( $this->BBS->SESSION['expires'] )}',
'{$this->BBS->DB->db_cleaner( $this->BBS->SESSION['last_click'] )}',
'{$this->BBS->DB->db_cleaner( $this->BBS->SESSION['location'] )}',
'{$this->BBS->DB->db_cleaner( $this->BBS->AGENT['ip_address'] )}',
'{$this->BBS->DB->db_cleaner( $this->BBS->AGENT['agent'] )}',
'{$this->BBS->DB->db_cleaner( $this->BBS->AGENT['hostname'] )}',
'{$this->BBS->DB->db_cleaner( $this->BBS->MEMBER['anonymous'] )}',
'{$this->BBS->DB->db_cleaner( $this->BBS->SESSION['search_bot'] )}',
'{$this->BBS->DB->db_cleaner( $this->BBS->SESSION['search_bot_name'] )}',
'{$this->BBS->DB->db_cleaner( $this->BBS->SESSION['admin_session'] )}'
)
EOF;
}

/**
 * BBSolutionSQL::sql_update_session()
 * 
 * @return String
 */
public function sql_update_session()
{
return <<<EOF
UPDATE {$this->BBS->db_prefix}sessions
SET
session_expires = '{$this->BBS->DB->db_cleaner( $this->BBS->SESSION['expires'] )}',
session_last_click = '{$this->BBS->DB->db_cleaner( $this->BBS->SESSION['last_click'] )}',
session_location = '{$this->BBS->DB->db_cleaner( $this->BBS->SESSION['location'] )}'
WHERE session_id = '{$this->BBS->DB->db_cleaner( $this->BBS->SESSION['id'] )}'
EOF;
}

/**
 * BBSolutionSQL::sql_delete_session()
 * 
 * @return String
 */
public function sql_delete_session()
{
return <<<EOF
DELETE FROM {$this->BBS->db_prefix}sessions
WHERE session_id = '{$this->BBS->DB->db_cleaner( $this->BBS->SESSION['id'] )}'
EOF;
}

/**
 * BBSolutionSQL::sql_delete_garbage_collection()
 * 
 * @return String
 */
public function sql_delete_garbage_collection()
{
return <<<EOF
DELETE FROM {$this->BBS->db_prefix}sessions
WHERE session_expires < '{$this->BBS->TOSQL['time']}'
EOF;
}

/**
 * BBSolutionSQL::sql_select_session_store_data()
 * 
 * @return String
 */
public function sql_select_session_store_data()
{
return <<<EOF
SELECT session_data FROM {$this->BBS->db_prefix}session_store
WHERE session_id = '{$this->BBS->DB->db_cleaner( $this->BBS->TOSQL['id'] )}'
AND session_expires > {$this->BBS->TOSQL['time']}
EOF;
}

/**
 * BBSolutionSQL::sql_select_session_store_id()
 * 
 * @return String
 */
public function sql_select_session_store_id()
{
return <<<EOF
SELECT session_id FROM {$this->BBS->db_prefix}session_store
WHERE session_id = '{$this->BBS->DB->db_cleaner( $this->BBS->TOSQL['id'] )}'
EOF;
}

/**
 * BBSolutionSQL::sql_insert_session_store()
 * 
 * @return String
 */
public function sql_insert_session_store()
{
return <<<EOF
INSERT INTO {$this->BBS->db_prefix}session_store
VALUES (
'{$this->BBS->DB->db_cleaner( $this->BBS->TOSQL['id'] )}',
'{$this->BBS->DB->db_cleaner( $this->BBS->TOSQL['data'] )}',
'{$this->BBS->DB->db_cleaner( $this->BBS->TOSQL['lifetime'] )}'
)
EOF;
}

/**
 * BBSolutionSQL::sql_update_session_store()
 * 
 * @return String
 */
public function sql_update_session_store()
{
return <<<EOF
UPDATE {$this->BBS->db_prefix}session_store
SET
session_id = '{$this->BBS->DB->db_cleaner( $this->BBS->TOSQL['id'] )}',
session_data = '{$this->BBS->DB->db_cleaner( $this->BBS->TOSQL['data'] )}',
session_expires = '{$this->BBS->DB->db_cleaner( $this->BBS->TOSQL['lifetime'] )}'
WHERE session_id = '{$this->BBS->DB->db_cleaner( $this->BBS->TOSQL['id'] )}'
EOF;
}

/**
 * BBSolutionSQL::sql_delete_session_store()
 * 
 * @return String
 */
public function sql_delete_session_store()
{
return <<<EOF
DELETE FROM {$this->BBS->db_prefix}session_store
WHERE session_id = '{$this->BBS->DB->db_cleaner( $this->BBS->TOSQL['id'] )}'
EOF;
}

/**
 * BBSolutionSQL::sql_delete_session_store_gc()
 * 
 * @return String
 */
public function sql_delete_session_store_gc()
{
return <<<EOF
DELETE FROM {$this->BBS->db_prefix}session_store
WHERE session_expires < UNIX_TIMESTAMP();
EOF;
}

/**
 * BBSolutionSQL::sql_select_all_sessions()
 * 
 * @return String
 */
public function sql_select_all_sessions()
{
return <<<EOF
SELECT * FROM {$this->BBS->db_prefix}sessions
EOF;
}

/**
 * BBSolutionSQL::sql_update_statistics_online_record()
 * 
 * @return String
 */
public function sql_update_statistics_online_record()
{
return <<<EOF
UPDATE {$this->BBS->db_prefix}statistics
SET
statistic_online_record = '{$this->BBS->TOSQL['online']}',
statistic_online_record_timestamp = '{$this->BBS->TOSQL['time']}'
WHERE statistic_id = '1'
EOF;
}

/**
 * BBSolutionSQL::sql_select_session_id()
 * 
 * @return String
 */
public function sql_select_session_id()
{
return <<<EOF
SELECT * FROM {$this->BBS->db_prefix}sessions
WHERE session_member_id = '{$this->BBS->TOSQL['member_id']}'
EOF;
}

/**
 * BBSolutionSQL::sql_update_members_delete_token()
 * 
 * @return String
 */
public function sql_update_members_delete_token()
{
return <<<EOF
UPDATE {$this->BBS->db_prefix}members
SET
member_token = '' 
WHERE member_id = '{$this->BBS->TOSQL['member_id']}'
EOF;
}

/**
 * BBSolutionSQL::sql_select_all_session_by_id()
 * 
 * @return String
 */
public function sql_select_all_session_by_id()
{
return <<<EOF
SELECT * FROM {$this->BBS->db_prefix}sessions 
WHERE session_id = '{$this->BBS->SESSION['id']}'
EOF;
}

public function sql_select_all_stored_cache()
{
return <<<EOF
SELECT * FROM {$this->BBS->db_prefix}stored_cache
EOF;
}

public function sql_select_all_forums_tree()
{
return <<<EOF
SELECT node.*, (COUNT(parent.forum_title) - 1) AS depth
FROM {$this->BBS->db_prefix}forums AS node, 
{$this->BBS->db_prefix}forums AS parent 
WHERE node.forum_lft BETWEEN parent.forum_lft AND parent.forum_rgt 
GROUP BY node.forum_title 
ORDER BY node.forum_lft
EOF;
}

public function sql_select_all_cache()
{
return <<<EOF
SELECT * FROM {$this->BBS->db_prefix}{$this->BBS->TOSQL['table']}{$this->BBS->TOSQL['sorting']}
EOF;
}

public function sql_update_cache()
{
return <<<EOF
UPDATE {$this->BBS->db_prefix}stored_cache
SET
cache_data = '{$this->BBS->TOSQL['to_cache']}' 
WHERE cache_title = '{$this->BBS->TOSQL['cache_title']}'
EOF;
}

public function sql_select_sessions_online()
{
return <<<EOF
SELECT * FROM {$this->BBS->db_prefix}sessions
ORDER BY session_last_click DESC
EOF;
}

public function sql_update_members_lockout()
{
return <<<EOF
UPDATE {$this->BBS->db_prefix}members
SET
member_login_attempts_locked = '1',
member_login_attempts_timestamp = '{$this->BBS->TOSQL['locked_duration']}' 
WHERE member_id = '{$this->BBS->TOSQL['member_id']}'
EOF;
}

public function sql_update_members_lockout_attempts()
{
return <<<EOF
UPDATE {$this->BBS->db_prefix}members
SET 
member_login_attempts = '{$this->BBS->TOSQL['login_attempts']}' 
WHERE member_id = '{$this->BBS->TOSQL['member_id']}'
EOF;
}

public function sql_update_members_clear_lockout()
{
return <<<EOF
UPDATE {$this->BBS->db_prefix}members 
SET 
member_login_attempts = '0', 
member_login_attempts_locked = '0', 
member_login_attempts_timestamp = '0' 
WHERE member_id = '{$this->BBS->TOSQL['member_id']}'
EOF;
}

public function sql_update_members_clear_login_attempts()
{
return <<<EOF
UPDATE {$this->BBS->db_prefix}members 
SET 
member_login_attempts = '0' 
WHERE member_id = '{$this->BBS->TOSQL['member_id']}'
EOF;
}

public function sql_update_members_login_token()
{
return <<<EOF
UPDATE {$this->BBS->db_prefix}members
SET 
member_token = '{$this->BBS->TOSQL['token']}',
member_last_visit = '{$this->BBS->TOSQL['time']}'
WHERE member_id = '{$this->BBS->TOSQL['member_id']}'
EOF;
}

public function sql_update_sessions_login()
{
return <<<EOF
UPDATE {$this->BBS->db_prefix}sessions 
SET 
session_member_id = '{$this->BBS->TOSQL['member_id']}',
session_member_username = '{$this->BBS->TOSQL['member_username']}',
session_expires = '{$this->BBS->TOSQL['expires']}',
session_last_click = '{$this->BBS->TOSQL['time']}',
session_location = '{$this->BBS->AGENT['location']}',
session_anonymous = '{$this->BBS->TOSQL['anonymous']}' 
WHERE session_id = '{$this->BBS->SESSION['id']}'
EOF;
}
 
 }

?>