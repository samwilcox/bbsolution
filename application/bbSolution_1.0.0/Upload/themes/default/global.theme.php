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

}

?>