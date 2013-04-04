{strip}
{if !$icons_only}
	{assign var=location value=menu}
{/if}
<ul>
	{if $gBitUser->isRegistered()}
		<li><a class="item" href="{$gBitSystem->getConfig('users_login_homepage',"`$smarty.const.USERS_PKG_URL`my.php")}">{booticon iname="icon-circle-arrow-right"   iexplain="My Personal Page" ilocation=$location}</a></li>
		{if $gBitUser->hasPermission( 'p_users_view_user_homepage' )}
			<li><a class="item" href="{$gBitUser->getDisplayUrl()}">{booticon iname="icon-home" iexplain="My Profile" ilocation=$location}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_users_edit_user_homepage' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}edit_personal_page.php">{booticon iname="icon-edit" iexplain="Edit My Homepage" ilocation=$location}</a></li>
		{/if}
		{if $gBitSystem->isFeatureActive( 'users_preferences' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}preferences.php">{booticon iname="icon-cogs"   iexplain=Preferences ilocation=$location}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_users_create_personal_groups' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}my_groups.php">{booticon iname="icon-group"   iexplain="My Groups" ilocation=$location}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_users_create_personal_roles' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}my_roles.php">{booticon iname="icon-group"   iexplain="My Roles" ilocation=$location}</a></li>
		{/if}
		{if $gBitSystem->isFeatureActive( 'users_watches' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}watches.php">{booticon iname="icon-asterisk"   iexplain="My Watches" ilocation=$location}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'messages' ) && $gBitUser->hasPermission( 'p_messages_send' )}
			<li><a class="item" {if $unreadMsgs}title="{tr}You have unread messages{/tr}"{/if} href="{$smarty.const.MESSAGES_PKG_URL}message_box.php">{booticon iname="icon-inbox" iexplain="Message Box" ilocation=$location}{if $unreadMsgs}<strong> [{$unreadMsgs}]</strong>{/if}</a></li>
			<li><a class="item" href="{$smarty.const.MESSAGES_PKG_URL}compose.php">{booticon iname="icon-envelope" iexplain="Compose Message" ilocation=$location}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'gatekeeper' )}
			<li><a class="item" href="{$smarty.const.GATEKEEPER_PKG_URL}">{booticon iname="icon-lock" iexplain="Security Settings" ilocation=$location}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'quota' )}
			<li><a class="item" href="{$smarty.const.QUOTA_PKG_URL}">{booticon iname="icon-hdd"   iexplain="My quota and usage" ilocation=$location}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_liberty_attach_attachments' )}
			<li><a class="item" href="{$smarty.const.LIBERTY_PKG_URL}attachments.php">{booticon iname="icon-paper-clip" iexplain="My Files" ilocation=$location}</a></li>
		{/if}
		{if $gBitUser->hasPermission('p_liberty_list_content')}
			<li><a class="item" href="{$smarty.const.LIBERTY_PKG_URL}list_content.php">{booticon iname="icon-list" iexplain="List Site Content" ilocation=$location}</a></li>
		{/if}
		{if $gBitUser->hasPermission('p_users_view_user_list')}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}index.php">{booticon iname="icon-group" ipackage="icons" iexplain="List Site Users" ilocation=$location}</a></li>
		{/if}
		<li><a class="item" href="{$smarty.const.USERS_PKG_URL}logout.php">{booticon iname="icon-signout" iexplain="Log out" ilocation=$location}</a></li>
	{else}
		<li><a class="item" href="{$smarty.const.USERS_PKG_URL}login.php">{booticon iname="icon-signin" iexplain="Login" ilocation=$location}</a></li>
		{if $gBitSystem->isFeatureActive('users_allow_register')}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}register.php">{booticon iname="icon-user" iexplain="Register" ilocation=$location}</a></li>
		{/if}
	{/if}
</ul>
{/strip}
