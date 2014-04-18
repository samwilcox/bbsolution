<?php

class BBSolutionThemeIndex
{
public $BBS;
public $LANG = array();

public function html_index_navigation()
{
return <<<EOF
 <a href="{$this->BBS->T['index_url']}" title="{$this->BBS->LANG['forums']}">{$this->BBS->LANG['forums']}</a>
EOF;
}

public function html_news_link()
{
return <<<EOF
<a href="{$this->BBS->T['topic_url']}" title="{$this->BBS->T['topic_title']}">{$this->BBS->T['topic_title']}</a>
EOF;
}

public function html_latest_news()
{
return <<<EOF
		<div class="newsBar">{$this->BBS->T['lang_latest_news']}</div>
EOF;
}

public function html_small_seperator()
{
return <<<EOF
      </div>
      <div class="verticalSpacer"></div>
EOF;
}

public function html_category_header()
{
return <<<EOF
    	<div class="contentHeader">
        	<div class="contentHeaderLeft">{$this->BBS->T['category_title']}</div>
            <div class="contentHeaderRight" id="c_{$this->BBS->T['category_id']}_i"><a href="javascript:void();" title="{$this->BBS->LANG['collapse']}" onClick="toggleThis('c_{$this->BBS->T['category_id']}_b','c_{$this->BBS->T['category_id']}_i');"><img src="{$this->BBS->imageset_url}/icons/collapse.png" width="16" height="16" alt="*" class="imgNoBorder"></a></div>
            <div class="clear"></div>
      </div>
      <div id="c_{$this->BBS->T['category_id']}_b">
EOF;
}

public function html_forum_image()
{
return <<<EOF
<img src="{$this->BBS->T['forum_image_url']}" alt="*" class="imgAlign"> 
EOF;
}

public function html_redirect_forum_listing()
{
return <<<EOF
      <div class="contentContainer">
        <table class="forumListingTable">
          <tr>
            <td class="forumListingIcon"><img src="{$this->BBS->imageset_url}/icons/status/forum/redirect.png" alt="*" title="{$this->LANG['redirect_info']}"></td>
            <td class="forumListingForumInfo"><span class="forumTitleLinkTxt"><a href="{$this->BBS->T['forum_url']}" title="{$this->BBS->T['forum_title']}">{$this->BBS->T['forum_title']}</a></span><br>
            <span class="forumDescription">{$this->BBS->T['forum_description']}</span></td>
            <td class="forumListingStats">{$this->BBS->T['total_redirects']}</td>
            <td class="forumListingLastPost">---</td>
          </tr>
        </table>
      </div>
EOF;
}

public function html_forum_icon_archived()
{
return <<<EOF
<img src="{$this->BBS->imageset_url}/icons/status/forum/archived.png" alt="*" title="{$this->LANG['archived']}">
EOF;
}

public function html_forum_icon_new_posts()
{
return <<<EOF
<img src="{$this->BBS->imageset_url}/icons/status/forum/new-posts.png" alt="*" title="{$this->LANG['new_posts']}">
EOF;
}

public function html_forum_icon_no_new_posts()
{
return <<<EOF
<img src="{$this->BBS->imageset_url}/icons/status/forum/no-new-posts.png" alt="*" title="{$this->LANG['no_new_posts']}">
EOF;
}

public function html_sub_forum_start()
{
return <<<EOF
<br>
<span class="subForumTxt">
EOF;
}

public function html_sub_forum_listing()
{
return <<<EOF
{$this->BBS->T['seperator']}<a href="{$this->BBS->T['forum_url']}" title="{$this->BBS->T['forum_title']}">{$this->BBS->T['forum_title']}</a>
EOF;
}

public function html_sub_forum_end()
{
return <<<EOF
</span>
EOF;
}

public function html_forum_last_post_info()
{
return <<<EOF
<div class="forumListingLastPostLeft">{$this->BBS->T['user_photo']}</div>
<div class="forumListingLastPostRight"><a href="{$this->BBS->T['topic_url']}" title="{$this->BBS->T['topic_title']}">{$this->BBS->T['topic_title']}</a> <a href="{$this->BBS->T['last_post_url']}" title="{$this->LANG['go_to_lp']}"><img src="{$this->BBS->imageset_url}/icons/go-to-last-post.gif" width="16" height="16" alt="*" class="imgAlign"></a><br>
{$this->BBS->T['by_author']}<br>
<span class="timestampTxt">{$this->BBS->T['timestamp']}</span>
</div>
EOF;
}

public function html_forum_listing()
{
return <<<EOF
      <div class="contentContainer">
        <table class="forumListingTable">
          <tr>
            <td class="forumListingIcon">{$this->BBS->T['forum_icon']}</td>
            <td class="forumListingForumInfo"><span class="forumTitleLinkTxt">{$this->BBS->T['forum_image']}<a href="{$this->BBS->T['forum_url']}" title="{$this->BBS->T['forum_title']}">{$this->BBS->T['forum_title']}</a></span><br>
            <span class="forumDescription">{$this->BBS->T['forum_description']}</span>{$this->BBS->T['sub_forums']}</td>
            <td class="forumListingStats">{$this->BBS->T['total_topics']}<br>
            {$this->BBS->T['total_replies']}</td>
            <td class="forumListingLastPost">{$this->BBS->T['last_post_information']}</td>
          </tr>
        </table>
      </div>
EOF;
}

public function html_category_footer()
{
return <<<EOF
 </div>
EOF;
}

public function html_bbic_start()
{
return <<<EOF
      <div class="verticalSpacer"></div>
EOF;
}

public function html_bbic_whos_online()
{
return <<<EOF
      <div class="bbicTitle"><img src="{$this->BBS->imageset_url}/icons/bbic-online.png" width="22" height="22" alt="*" class="imgAlign"> {$this->LANG['whos_online_now']}</div>
      <div class="bbicContent">{$this->BBS->T['currently_online']}<br>
      {$this->BBS->T['online_list']}<br>
      <br>
      {$this->BBS->T['members_online']}<br>
      {$this->BBS->T['group_legend']}</div>
EOF;
}

public function html_timestamp_text()
{
return <<<EOF
<span class="timestampTxt">{$this->BBS->T['timestamp']}</span>
EOF;
}

public function html_bbic_statistics()
{
return <<<EOF
      <div class="bbicTitle"><img src="{$this->BBS->imageset_url}/icons/bbic_stats.png" width="22" height="22" alt="*" class="imgAlign"> {$this->LANG['statistics']}</div>
      <div class="bbicContent">{$this->BBS->T['newest_member']}<br>
      {$this->BBS->T['quick_stats']}<br>
      {$this->BBS->T['most_users']}</div>
EOF;
}

public function html_bbic_birthdays()
{
return <<<EOF
      <div class="bbicTitle"><img src="{$this->BBS->imageset_url}/icons/bbic_birthdays.png" width="22" height="22" alt="*" class="imgAlign"> {$this->LANG['member_birthdays']}</div>
      <div class="bbicContent">{$this->BBS->T['current_birthdays']}<br>
      {$this->BBS->T['birthday_list']}</div>
EOF;
}

}

?>