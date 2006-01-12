{* $Header: /cvsroot/bitweaver/_bit_users/modules/mod_login_box.tpl,v 1.1.1.1.2.9 2006/01/12 00:02:03 squareing Exp $ *}
{strip}
{bitmodule title="$moduleTitle" name="login_box"}
	{if $gBitUser->IsRegistered()}
		{tr}Logged in as <strong>{displayname}{/tr}</strong><br />
		<a href="{$smarty.const.USERS_PKG_URL}logout.php">{tr}Logout{/tr}</a>
		{if $gBitUser->hasPermission( 'bit_p_admin_users' )}
		<div class="row">
			{form ipackage=users ifile="admin/index.php"}
				<input type="text" name="assume_user" value="{tr}Username{/tr}" id="assume_user" size="20" onfocus="this.value=''" /> <input type="submit" name="confirm" value="{tr}Assume{/tr}" />
			{/form}
		</div>
		{/if}
	{else}
		{form ipackage=users ifile='validate.php'}
			<div class="row">
				<input type="text" name="user" id="user" value="{tr}Username{/tr}" size="20" onfocus="this.value=''" />
			</div>

			<div class="row">
				<input type="password" name="pass" id="pass" value="password" size="20" onfocus="this.value=''" />
			</div>

			{if $gBitSystem->isFeatureActive('rememberme')}
				<div class="row">
					{formlabel label="Remember me" for="rme"} <input type="checkbox" name="rme" id="rme" value="on" checked="checked" />
				</div>
			{/if}

			{if $http_login_url ne '' or $https_login_url ne ''}
				<div class="row">
					<a href="{$http_login_url}" title="{tr}Click here to login using the default security protocol{/tr}">{tr}standard{/tr}</a> |
					<a href="{$https_login_url}" title="{tr}Click here to login using a secure protocol{/tr}">{tr}secure{/tr}</a>
				</div>
			{/if}

			{if $show_stay_in_ssl_mode eq 'y'}
				<div class="row">
					{formlabel label="stay in ssl mode" for="stay_in_ssl_mode"} <input type="checkbox" name="stay_in_ssl_mode" id="stay_in_ssl_mode" {if $stay_in_ssl_mode eq 'y'}checked="checked"{/if} />
				</div>
			{else}
				<input type="hidden" name="stay_in_ssl_mode" value="{$stay_in_ssl_mode|escape}" />
			{/if}

			<div class="row submit">
				<input type="submit" name="login" value="{tr}Log in to {$siteName|default:"this site"}{/tr}" />
			</div>

			<div class="row">
				{if $allowRegister eq 'y'}
					<a href="{$smarty.const.USERS_PKG_URL}register.php">{tr}Register{/tr}</a>
				{/if}

				{if $forgotPass eq 'y'}
					<br /><a href="{$smarty.const.USERS_PKG_URL}remind_password.php">{tr}I forgot my password{/tr}</a>
				{/if}
			</div>
		{/form}
	{/if}
{/bitmodule}
{/strip}
