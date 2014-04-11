<?php

class BBSolutionThemeGlobal
{
public $BBS;
public $LANG = array();

public function html_forum_nav_tree_add()
{
return <<<EOF
 >> <a href="{$this->BBS->T['forum_url']}" title="{$this->BBS->T['forum_title']}">{$this->BBS->T['forum_title']}</a>
EOF;
}

public function html_member_link_no_style()
{
return <<<EOF
{$this->BBS->T['seperator']}<a href="{$this->BBS->T['member_link']}" title="{$this->BBS->T['title']}">{$this->BBS->T['member_name']}</a>
EOF;
}

public function html_member_link_important()
{
return <<<EOF
{$this->BBS->T['seperator']}<a href="{$this->BBS->T['member_link']}" title="{$this->BBS->T['title']}" style="color:{$this->BBS->T['group_color']};"><strong>{$this->BBS->T['member_name']}</strong></a>
EOF;
}

public function html_member_link_normal()
{
return <<<EOF
{$this->BBS->T['seperator']}<a href="{$this->BBS->T['member_link']}" title="{$this->BBS->T['title']}" style="color:{$this->BBS->T['group_color']};">{$this->BBS->T['member_name']}</a>
EOF;
}

public function html_group_link_important()
{
return <<<EOF
{$this->BBS->T['seperator']}<a href="{$this->BBS->T['group_link']}" title="{$this->BBS->T['title']}" style="color:{$this->BBS->T['group_color']};"><strong>{$this->BBS->T['group_name']}</strong></a>
EOF;
}

public function html_group_link_normal()
{
return <<<EOF
{$this->BBS->T['seperator']}<a href="{$this->BBS->T['group_link']}" title="{$this->BBS->T['title']}" style="color:{$this->BBS->T['group_color']};">{$this->BBS->T['group_name']}</a>
EOF;
}

public function html_member_photo_link_start()
{
return <<<EOF
<a href="{$this->BBS->T['member_link']}" title="{$this->BBS->T['title']}">
EOF;
}

public function html_member_photo_link_end()
{
return <<<EOF
</a>
EOF;
}

public function html_no_photo()
{
return <<<EOF
{$this->BBS->T['link_start']}<img src="{$this->BBS->imageset_url}/images/{$this->CFG['no_photo_filename']}" alt="*" class="userPhoto">{$this->BBS->T['link_end']}
EOF;
}

public function html_no_photo_thumb()
{
return <<<EOF
{$this->BBS->T['link_start']}<img src="{$this->BBS->imageset_url}/images/{$this->CFG['no_photo_thumb_filename']}" alt="*" class="userPhoto">{$this->BBS->T['link_end']}
EOF;
}

public function html_member_photo()
{
return <<<EOF
{$this->BBS->T['link_start']}<img src="{$this->BBS->T['member_photo']}" width="{$this->BBS->T['width']}" height="{$this->BBS->T['height']}" alt="*" class="userPhoto">{$this->BBS->T['link_end']}
EOF;
}

public function html_tab_selected_start()
{
return <<<EOF
<span class="selectedMenuItem">
EOF;
}

public function html_tab_select_end()
{
return <<<EOF
</span>
EOF;
}

public function html_top_header_guest()
{
return <<<EOF
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>{$this->BBS->T['title']}bbSolution Development</title>
<link href="{$this->BBS->theme_url}/css/index.css" rel="stylesheet" type="text/css" media="screen">

<script language="javascript" type="text/javascript">
function toggleThis( eb,ei )
{
	var elementBody = document.getElementById( eb );
	var elementImg  = document.getElementById( ei );
	
	if ( elementBody.className == "hideMe" )
	{
		elementBody.className = "";
		elementImg.innerHTML  = '<a href="javascript:void();" title="{$this->BBS->LANG['collapse']}" onClick="toggleThis(\'' + eb + '\',\'' + ei + '\');"><img src="{$this->BBS->imageset_url}/icons/collapse.png" alt="*" class="imgNoBorder"></a>';
	}
	else
	{
		elementBody.className = "hideMe";
		elementImg.innerHTML  = '<a href="javascript:void();" title="{$this->BBS->LANG['expand']}" onClick="toggleThis(\'' + eb + '\',\'' + ei + '\');"><img src="{$this->BBS->imageset_url}/icons/expand.png" alt="*" class="imgNoBorder"></a>';
	}
}
</script>

</head>

<body>
<div class="topWrapper">
	<div class="topLogoBar">
	  <table class="topLogoBarTable" cellspacing="0" cellpadding="0">
	    <tr>
	      <td class="topLogoBarLeft"><a href="{$this->BBS->T['index_url']}" title="{$this->BBS->CFG['application_title']}"><img src="{$this->BBS->imageset_url}/images/bb-logo.png" alt="{$this->BBS->CFG['application_title']}" class="imgNoBorder"></a></td>
	      <td class="topLogoBarRight"><ul>
	        <li>{$this->BBS->T['greeting']}</li>
	        <li>|</li>
	        <li><img src="{$this->BBS->imageset_url}/icons/login.png" width="16" height="16" alt="*" class="imgAlign"> <a href="{$this->BBS->script_url}?cls=authenticate" title="{$this->BBS->LANG['login']}">{$this->BBS->LANG['login']}</a></li>
	        <li><img src="{$this->BBS->imageset_url}/icons/create-account.png" width="16" height="16" alt="*" class="imgAlign"> <a href="{$this->BBS->script_url}?cls=createaccount" title="{$this->BBS->LANG['create_account']}">{$this->BBS->LANG['create_account']}</a></li>
          </ul></td>
        </tr>
      </table>
	</div>
</div>
<div class="menuBarWrapper">
	<div class="menuBar">{$this->BBS->T['tstart']['forums']}<a href="{$this->BBS->T['index_url']}" title="{$this->BBS->LANG['forums']}">{$this->BBS->LANG['forums']}</a>{$this->BBS->T['tend']['forums']} {$this->BBS->T['tstart']['members']}<a href="{$this->BBS->T['members_url']}" title="{$this->BBS->LANG['members']}">{$this->BBS->LANG['members']}</a>{$this->BBS->T['tend']['members']} {$this->BBS->T['tstart']['search']}<a href="{$this->BBS->script_url}?cls=search" title="{$this->BBS->LANG['search']}">{$this->BBS->LANG['search']}</a>{$this->BBS->T['tend']['search']} {$this->BBS->T['tstart']['whosonline']}<a href="{$this->BBS->T['online_url']}" title="{$this->BBS->LANG['whos_online']}">{$this->BBS->LANG['whos_online']}</a>{$this->BBS->T['tend']['whosonline']} {$this->BBS->T['tstart']['help']}<a href="{$this->BBS->script_url}?cls=help" title="{$this->BBS->LANG['help']}">{$this->BBS->LANG['help']}</a>{$this->BBS->T['tend']['help']}</div>
</div>
<div class="navBarWrapper">
	<div class="navBar"><img src="{$this->BBS->imageset_url}/icons/nav_folder.png" width="16" height="16" alt="*" class="imgAlign"> {$this->BBS->T['nav']}</div>
</div>
<div class="wrapper">
	<div class="insideWrapper">
EOF;
}

public function html_resend_activation_email_link()
{
return <<<EOF
<li class="sendActivationLink">[ <a href="{$this->BBS->script_url}?cls=createaccount&amp;to_do=resend" title="{$this->LANG['resend_activation']}">{$this->LANG['resend_activation']}</a> ]</li>
EOF;
}

public function html_last_visit_timestamp()
{
return <<<EOF
<span class="timestampTxt">{$this->BBS->T['timestamp']}</span>
EOF;
}

public function html_top_header_member()
{
return <<<EOF
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>{$this->BBS->T['title']}bbSolution Development</title>
<link href="{$this->BBS->theme_url}/css/index.css" rel="stylesheet" type="text/css" media="screen">

<script language="javascript" type="text/javascript">
function toggleThis( eb,ei )
{
	var elementBody = document.getElementById( eb );
	var elementImg  = document.getElementById( ei );
	
	if ( elementBody.className == "hideMe" )
	{
		elementBody.className = "";
		elementImg.innerHTML  = '<a href="javascript:void();" title="{$this->BBS->LANG['collapse']}" onClick="toggleThis(\'' + eb + '\',\'' + ei + '\');"><img src="{$this->BBS->imageset_url}/icons/collapse.png" alt="*" class="imgNoBorder"></a>';
	}
	else
	{
		elementBody.className = "hideMe";
		elementImg.innerHTML  = '<a href="javascript:void();" title="{$this->BBS->LANG['expand']}" onClick="toggleThis(\'' + eb + '\',\'' + ei + '\');"><img src="{$this->BBS->imageset_url}/icons/expand.png" alt="*" class="imgNoBorder"></a>';
	}
}
</script>

</head>

<body>
<div class="topWrapper">
	<div class="topLogoBar">
	  <table class="topLogoBarTable" cellspacing="0" cellpadding="0">
	    <tr>
	      <td class="topLogoBarLeft"><a href="{$this->BBS->T['index_url']}" title="{$this->BBS->CFG['application_title']}"><img src="{$this->BBS->imageset_url}/images/bb-logo.png" alt="{$this->BBS->CFG['application_title']}" class="imgNoBorder"></a></td>
	      <td class="topLogoBarRight"><ul>
	        <li>{$this->BBS->T['greeting']}</li>
	        <li>|</li>
	        <li><img src="{$this->BBS->imageset_url}/icons/logout.png" width="16" height="16" alt="*" class="imgAlign"> <a href="{$this->BBS->script_url}?cls=authenticate&amp;to_do=logout" title="{$this->LANG['logout']}" onClick="if(window.confirm('{$this->LANG['logout_confirm']}')) { location = '{$this->BBS->script_url}?cls=authenticate&amp;to_do=logout'; } return false;">{$this->LANG['logout']}</a></li>
	        <li><img src="{$this->BBS->imageset_url}/icons/usercp.png" width="16" height="16" alt="*" class="imgAlign"> <a href="{$this->BBS->script_url}?cls=usercp" title="{$this->LANG['usercp']}">{$this->LANG['usercp']}</a></li>
	        <li><img src="{$this->BBS->imageset_url}/icons/messenger.png" width="16" height="16" alt="*" class="imgAlign"> <a href="{$this->BBS->script_url}?cls=messenger" title="{$this->LANG['messenger']}">{$this->LANG['messenger']}</a> (<strong>{$this->BBS->T['unread']}</strong>)</li>
            <br>
            <ul>
            <li>{$this->BBS->T['last_visit']}</li>{$this->BBS->T['resend']}
            </ul>
	      </ul></td>
        </tr>
      </table>
	</div>
</div>
<div class="menuBarWrapper">
	<div class="menuBar">{$this->BBS->T['tstart']['forums']}<a href="{$this->BBS->T['index_url']}" title="{$this->BBS->LANG['forums']}">{$this->BBS->LANG['forums']}</a>{$this->BBS->T['tend']['forums']} {$this->BBS->T['tstart']['members']}<a href="{$this->BBS->T['members_url']}" title="{$this->BBS->LANG['members']}">{$this->BBS->LANG['members']}</a>{$this->BBS->T['tend']['members']} {$this->BBS->T['tstart']['search']}<a href="{$this->BBS->script_url}?cls=search" title="{$this->BBS->LANG['search']}">{$this->BBS->LANG['search']}</a>{$this->BBS->T['tend']['search']} {$this->BBS->T['tstart']['whosonline']}<a href="{$this->BBS->T['online_url']}" title="{$this->BBS->LANG['whos_online']}">{$this->BBS->LANG['whos_online']}</a>{$this->BBS->T['tend']['whosonline']} {$this->BBS->T['tstart']['help']}<a href="{$this->BBS->script_url}?cls=help" title="{$this->BBS->LANG['help']}">{$this->BBS->LANG['help']}</a>{$this->BBS->T['tend']['help']}</div>
</div>
<div class="navBarWrapper">
	<div class="navBar"><img src="{$this->BBS->imageset_url}/icons/nav_folder.png" width="16" height="16" alt="*" class="imgAlign"> {$this->BBS->T['nav']}</div>
</div>
<div class="wrapper">
	<div class="insideWrapper">
EOF;
}

}

?>