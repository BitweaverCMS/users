{* $Header: /cvsroot/bitweaver/_bit_users/modules/mod_login_box.tpl,v 1.3 2005/08/07 17:46:47 squareing Exp $ *}
{strip}
{bitmodule title="$moduleTitle" name="login_box"}
	{if $gBitUser->IsRegistered()}
		{tr}Logged in as <strong>{displayname}{/tr}</strong><br />
		<a href="{$smarty.const.USERS_PKG_URL}logout.php">{tr}Logout{/tr}</a>
	{else}
		{form ipackage=users ifile='validate.php'}
			<div class="row">
				<input type="text" name="user" alt="username" size="15" value="username" onfocus="this.value=''" />
			</div>

			<div class="row">
				<input type="password" name="pass" alt="password" size="15" value="password" onfocus="this.value=''" />
			</div>

			{if $rememberme ne 'disabled'}
				<div class="row">
					<label><input type="checkbox" name="rme" value="on" checked=checked/> {tr}Remember Me{/tr}</label>
				</div>
			{/if}

			{if $http_login_url ne '' or $https_login_url ne ''}
				<div class="row">
					<a href="{$http_login_url}" title="{tr}Click here to login using the default security protocol{/tr}">{tr}Standard{/tr}</a> |
					<a href="{$https_login_url}" title="{tr}Click here to login using a secure protocol{/tr}">{tr}Secure{/tr}</a>
				</div>
			{/if}

			{if $show_stay_in_ssl_mode eq 'y'}
				<div class="row">
					<label><input type="checkbox" name="stay_in_ssl_mode" id="stay_in_ssl_mode" {if $stay_in_ssl_mode eq 'y'}checked="checked"{/if} /> {tr}Stay in SSL mode{/tr}</label>
				</div>
			{else}
				<input type="hidden" name="stay_in_ssl_mode" value="{$stay_in_ssl_mode|escape}" />
			{/if}

			<div class="row submit">
				<input type="submit" name="login" value="{tr}Login{/tr}" />
			</div>

			<div class="row">
				{if $allowRegister eq 'y'}
					<br /><a href="{$smarty.const.USERS_PKG_URL}register.php">{tr}Register{/tr}</a>
				{/if}

				{if $forgotPass eq 'y'}
					<br /><a href="{$smarty.const.USERS_PKG_URL}remind_password.php">{tr}I forgot my password{/tr}</a>
				{/if}
			</div>
		{/form}
	{/if}
{/bitmodule}
{/strip}
