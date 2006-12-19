{* $Header: /cvsroot/bitweaver/_bit_users/modules/mod_login_box.tpl,v 1.12 2006/12/19 16:20:01 squareing Exp $ *}
{strip}
{bitmodule title="$moduleTitle" name="login_box"}
	{if $gBitUser->IsRegistered()}
		{tr}Logged in as{/tr}: <strong>{displayname}</strong><br />
		<a href="{$smarty.const.USERS_PKG_URL}logout.php">{tr}Logout{/tr}</a>
		{if $gBitUser->hasPermission( 'p_users_admin' )}
		<div class="row">
			{form ipackage=users ifile="admin/index.php"}
				<input type="text" name="assume_user" value="{tr}Username{/tr}" id="assume_user" size="15" onfocus="this.value=''" /> <input type="submit" name="confirm" value="{tr}Assume{/tr}" />
			{/form}
		</div>
		{/if}
	{else}
		{assign var=force_secure value=$gBitSystem->isFeatureActive("site_https_login_required")}
		{form ipackage=users ifile='validate.php' secure=$force_secure}
			<div class="row">
				<input type="text" name="user" id="user" value="{tr}Username{/tr}" size="15" onfocus="this.value=''" />
			</div>

			<div class="row">
				<input type="password" name="pass" id="pass" value="password" size="15" onfocus="this.value=''" />
			</div>

			{if $gBitSystem->isFeatureActive('users_remember_me')}
				<div class="row">
					<label><input type="checkbox" name="rme" id="rme" value="on" checked="checked" /> {tr}Remember me{/tr}</label>
				</div>
			{/if}

			{if $gBitSystem->getConfig('http_login_url') or $gBitSystem->getConfig('https_login_url')}
				<div class="row">
					<a href="{$gBitSystem->getConfig('http_login_url')}" title="{tr}Click here to login using the default security protocol{/tr}">{tr}standard{/tr}</a> |
					<a href="{$gBitSystem->getConfig('https_login_url')}" title="{tr}Click here to login using a secure protocol{/tr}">{tr}secure{/tr}</a>
				</div>
			{/if}

			{if $gBitSystem->isFeatureActive('show_stay_in_ssl_mode')}
				<div class="row">
					<label>{tr}Stay in ssl mode{/tr} <input type="checkbox" name="stay_in_ssl_mode" id="stay_in_ssl_mode" {if $gBitSystem->isFeatureActive('stay_in_ssl_mode')}checked="checked"{/if} /></label>
				</div>
			{else}
				<input type="hidden" name="stay_in_ssl_mode" value="{$gBitSystem->getConfig('stay_in_ssl_mode')|escape}" />
			{/if}

			<div class="row submit">
				<input type="submit" name="login" value="{tr}Log in{/tr}" />
			</div>

			<div class="row">
				{if $gBitSystem->isFeatureActive('users_allow_register')}
					<a href="{$smarty.const.USERS_PKG_URL}register.php">{tr}Register{/tr}</a>
				{/if}

				{if $gBitSystem->isFeatureActive('users_forgot_pass')}
					<br /><a href="{$smarty.const.USERS_PKG_URL}remind_password.php">{tr}I forgot my password{/tr}</a>
				{/if}
			</div>
		{/form}
	{/if}

	{if $gBitSystem->isPackageActive( 'messages' ) && $unreadMsgs}
		<div class="row">
			<a href="{$smarty.const.MESSAGES_PKG_URL}message_box.php">{biticon ipackage=icons iname="mail-unread" iexplain="Unread Messages"} {$unreadMsgs}</a>
		</div>
	{/if}
{/bitmodule}
{/strip}
