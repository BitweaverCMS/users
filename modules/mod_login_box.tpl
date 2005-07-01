{* $Header: /cvsroot/bitweaver/_bit_users/modules/mod_login_box.tpl,v 1.1.1.1.2.2 2005/07/01 20:07:48 squareing Exp $ *}
{bitmodule title="$moduleTitle" name="login_box"}
   {if $gBitUser->IsRegistered()}
     {tr}Logged in as{/tr}: {$gBitUser->getDisplayName()}<br />
      <a href="{$gBitLoc.USERS_PKG_URL}logout.php">{tr}Logout{/tr}</a><br />
      {if $gBitUser->isAdmin()}
        {form action=$login_url}
        <label for="login-switchuser">{tr}user{/tr}:</label>
        <input type="text" name="username" id="login-switchuser" size="8" />
        <input type="submit" name="su" value="{tr}set{/tr}" />
        {/form}
      {/if}
    {else}
     <form name="loginbox" action="{$login_url}" method="post" {if $gBitSystemPrefs.feature_challenge eq 'y'}onsubmit="doChallengeResponse()"{/if}>
     {if $gBitSystemPrefs.feature_challenge eq 'y'}
     <script language="javascript" type="text/javascript" src="lib/md5.js"></script>
     {literal}
     <script language="Javascript" type="text/javascript">
     <!--
     function doChallengeResponse() {
       hashstr = document.loginbox.user.value +
       document.loginbox.pass.value +
       document.loginbox.email.value;
       str = document.loginbox.user.value +
       MD5(hashstr) +
       document.loginbox.challenge.value;
       document.loginbox.response.value = MD5(str);
       document.loginbox.pass.value='';
       /*
       document.login.password.value = "";
       document.logintrue.username.value = document.login.username.value;
       document.logintrue.response.value = MD5(str);
       document.logintrue.submit();
       */
       document.loginbox.submit();
       return false;
     }
	// -->
    </script>
    {/literal}
     <input type="hidden" name="challenge" value="{$challenge|escape}" />
     <input type="hidden" name="response" value="" />
     {/if}

		<div class="row">
			{forminput}
				<input type="text" name="user" alt="username" size="15" value="username" onFocus="this.value=''" />
			{/forminput}
		</div>

		{if $gBitSystemPrefs.feature_challenge eq 'y'}
			<div class="row">
				{forminput}
					<input type="text" name="email" alt="email address" size="15" value="email" onFocus="this.value=''" />
				{/forminput}
			</div>
		{/if}

		<div class="row">
			{forminput}
				<input type="password" name="pass" alt="password" size="15" value="password" onFocus="this.value=''" />
				{if $forgotPass eq 'y'}
					<br /><a href="{$gBitLoc.USERS_PKG_URL}remind_password.php">I forgot my password</a>
				{/if}
			{/forminput}
		</div>

		{if $rememberme ne 'disabled'}
			<div class="row">
				{forminput}
					<input type="checkbox" name="rme" value="on" checked=checked/>
				{/forminput}
			</div>
		{/if}

		{if $http_login_url ne '' or $https_login_url ne ''}
			<div class="row">
				{forminput}
					<a href="{$http_login_url}" title="{tr}Click here to login using the default security protocol{/tr}">{tr}standard{/tr}</a> |
					<a href="{$https_login_url}" title="{tr}Click here to login using a secure protocol{/tr}">{tr}secure{/tr}</a>
				{/forminput}
			</div>
		{/if}

		{if $show_stay_in_ssl_mode eq 'y'}
			<div class="row">
				{formlabel label="stay in ssl mode" for="stay_in_ssl_mode"}
				{forminput}
					<input type="checkbox" name="stay_in_ssl_mode" id="stay_in_ssl_mode" {if $stay_in_ssl_mode eq 'y'}checked="checked"{/if} />
				{/forminput}
			</div>
		{else}
			<input type="hidden" name="stay_in_ssl_mode" value="{$stay_in_ssl_mode|escape}" />
		{/if}

		<div class="row submit">
			<input type="submit" name="login" value="{tr}login{/tr}" />
			{if $allowRegister eq 'y'}
				<a href="{$gBitLoc.USERS_PKG_URL}register.php">{tr}register{/tr}</a>
			{/if}
		</div>
	</form>
{/if}
{/bitmodule}
