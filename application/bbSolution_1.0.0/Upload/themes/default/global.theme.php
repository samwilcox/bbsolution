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

}

?>