{strip}
{if !$icons_only}
	{assign var=location value=menu}
{/if}
<ul>
	{if $gBitUser->isRegistered()}
		<li><a class="item" href="{$gBitSystem->getConfig('users_login_homepage',"`$smarty.const.USERS_PKG_URL`my.php")}">{biticon iname="emblem-symbolic-link" iexplain="My Personal Page" ilocation=$location}</a></li>
		{if $gBitUser->hasPermission( 'p_users_view_user_homepage' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}index.php?home={$gBitUser->mInfo.login}">{biticon iname="go-home" iexplain="My Homepage" ilocation=$location}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_users_edit_user_homepage' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}edit_personal_page.php">{biticon iname="accessories-text-editor" iexplain="Edit My Homepage" ilocation=$location}</a></li>
		{/if}
		{if $gBitSystem->isFeatureActive( 'users_preferences' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}preferences.php">{biticon iname="emblem-system" iexplain=Preferences ilocation=$location}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_users_create_personal_groups' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}my_groups.php">{biticon iname="preferences-desktop" iexplain="My Groups" ilocation=$location}</a></li>
		{/if}
		{if $gBitSystem->isFeatureActive( 'users_watches' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}watches.php">{biticon iname="weather-clear" iexplain="My Watches" ilocation=$location}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'messages' ) && $gBitUser->hasPermission( 'p_messages_send' )}
			<li><a class="item" {if $unreadMsgs}title="{tr}You have unread messages{/tr}"{/if} href="{$smarty.const.MESSAGES_PKG_URL}message_box.php">{biticon iname="emblem-mail" iexplain="Message Box" ilocation=$location}{if $unreadMsgs}<strong> [{$unreadMsgs}]</strong>{/if}</a></li>
			<li><a class="item" href="{$smarty.const.MESSAGES_PKG_URL}compose.php">{biticon iname="mail-send-receive" iexplain="Compose Message" ilocation=$location}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'gatekeeper' )}
			<li><a class="item" href="{$smarty.const.GATEKEEPER_PKG_URL}">{biticon iname="emblem-readonly" iexplain="Security Settings" ilocation=$location}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'quota' )}
			<li><a class="item" href="{$smarty.const.QUOTA_PKG_URL}">{biticon iname="drive-harddisk" iexplain="My quota and usage" ilocation=$location}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_liberty_attach_attachments' )}
			<li><a class="item" href="{$smarty.const.LIBERTY_PKG_URL}attachments.php">{biticon iname="mail-attachment" iexplain="My Files" ilocation=$location}</a></li>
		{/if}
		<li><a class="item" href="{$smarty.const.USERS_PKG_URL}logout.php">{biticon iname=system-log-out iexplain="Log out" ilocation=$location}</a></li>
	{else}
		<li><a class="item" href="{$smarty.const.USERS_PKG_URL}login.php">{biticon iname=go-next iexplain="Login" ilocation=$location}</a></li>
		{if $gBitSystem->isFeatureActive('users_allow_register')}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}register.php">{biticon iname=contact-new iexplain="Register" ilocation=$location}</a></li>
		{/if}
	{/if}
</ul>
{/strip}
