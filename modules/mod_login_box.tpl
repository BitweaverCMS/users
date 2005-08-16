{* $Header: /cvsroot/bitweaver/_bit_users/modules/mod_login_box.tpl,v 1.1.1.1.2.6 2005/08/16 11:57:41 wolff_borg Exp $ *}
{strip}
{bitmodule title="$moduleTitle" name="login_box"}
	{if $gBitUser->IsRegistered()}
		{tr}Logged in as <strong>{displayname}{/tr}</strong><br />
		<a href="{$smarty.const.USERS_PKG_URL}logout.php">{tr}Logout{/tr}</a>
		{if $gBitUser->hasPermission( 'bit_p_assume_users' )}
		<div class="row">
			{form ipackage=users ifile="admin/index.php"}
			{forminput}
				{formlabel label="User" for="assume_user"}
				<input type="text" name="assume_user" id="assume_user" size="8" />
				{formhelp note=""}
			{/forminput}
			<input type="submit" name="confirm" value="{tr}Assume{/tr}" />
			{/form}
		</div>
		{/if}
	{else}
		{include file="bitpackage:users/login_inc.tpl"}
		<div class="row">
			{if $allowRegister eq 'y'}
				<br /><a href="{$smarty.const.USERS_PKG_URL}register.php">{tr}Register{/tr}</a>
			{/if}

			{if $forgotPass eq 'y'}
				<br /><a href="{$smarty.const.USERS_PKG_URL}remind_password.php">{tr}I forgot my password{/tr}</a>
			{/if}
		</div>
	{/if}
{/bitmodule}
{/strip}
