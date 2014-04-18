<?php

class BBSolutionThemeAuthenticate
{

public $BBS;
public $LANG = array();

public function html_facebook_connect_button()
{
return <<<EOF
<fb:login-button show-faces="false" auto-logout-link="true" size="small" max-rows="1" scope="email,user_birthday">{$this->BBS->T['button_text']}</fb:login-button><br>
      	  <br>
EOF;
}

public function html_create_account_link()
{
return <<<EOF
<a href="{$this->BBS->script_url}?cls=createaccount" title="{$this->LANG['create_account']}">{$this->LANG['create_account']}</a>
EOF;
}

public function html_login_form()
{
return <<<EOF
	  <div class="contentHeader">{$this->LANG['login']}</div>
      <div class="contentContainer">
      	<div class="loginPageLeftIsland">{$this->BBS->T['facebook_connect']}{$this->BBS->T['error_box']}
        <form name="lform" id="lform" method="post" action="{$this->BBS->script_url}">
   	      <div class="loginPageFormLeft">{$this->LANG['username_email']}</div>
            <div class="loginPageFormRight">
              <input name="username" type="text" id="username" size="32">
            </div>
          <div class="clear"></div>
          <div class="loginPageFormLeft">{$this->LANG['password']}</div>
            <div class="loginPageFormRight">
              <input name="password" type="password" id="password" size="32">
              <br>
              <br>
              <input type="checkbox" name="rememberme" id="checkbox" value="1"{$this->BBS->T['auto']}> 
              {$this->LANG['auto_login']}
              <br>
              <input type="checkbox" name="anonymous" id="checkbox2" value="1"{$this->BBS->T['anon']}> 
              {$this->LANG['anonymous']}<br>
              <br>
              <input type="hidden" name="cls" value="authenticate">
              <input type="hidden" name="to_do" value="plogin">
              {$this->BBS->T['form_token']}
              <input type="submit" name="button" id="button" value="{$this->LANG['login_button']}">
            </div>
            <div class="clear"></div>
      	  </form>
      	</div>
        <div class="loginPageRightIsland"><img src="{$this->BBS->imageset_url}/icons/create-account-info.png" width="32" height="32" alt="*" class="imgAlignCenter"> <span class="loginInfoTitle">{$this->BBS->T['not_member']}</span><br>
          {$this->LANG['not_member_info']}<br>
          <br>
        <img src="{$this->BBS->imageset_url}/icons/account-recovery-info.png" width="32" height="32" alt="*" class="imgAlignCenter"> <span class="loginInfoTitle"><a href="{$this->BBS->script_url}?cls=authenticate&amp;to_do=recovery" title="{$this->LANG['account_recovery']}">{$this->LANG['account_recovery']}</a></span><br>
        {$this->LANG['account_recovery_info']}</div>
        <div class="clear"></div>
      </div>
EOF;
}

public function html_login_form_error_box()
{
return <<<EOF
<div class="formErrorBox">{$this->BBS->T['error_message']}</div>
EOF;
}

public function html_login_form_checked()
{
return <<<EOF
 checked="checked"
EOF;
}

}

?>