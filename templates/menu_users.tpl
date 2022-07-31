{strip}
{if !$icons_only}
	{assign var=location value=menu}
{/if}
{if $packageMenuTitle}<a class="dropdown-toggle" data-toggle="dropdown" href="#"> {tr}{$packageMenuTitle}{/tr} <b class="caret"></b></a>{/if}
<ul class="{$packageMenuClass}">
	{if $gBitUser->isRegistered()}
		<li><a class="item" href="{$gBitSystem->getConfig('users_login_homepage',"`$smarty.const.USERS_PKG_URL`my.php")}">{booticon iname="fa-circle-arrow-right" iexplain="My Personal Page"}</a></li>
		{if $gBitUser->hasPermission( 'p_users_view_user_homepage' )}
			<li><a class="item" href="{$gBitUser->getDisplayUrl()}">{booticon iname="fa-home" iexplain="My Profile"}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_users_edit_user_homepage' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}edit_personal_page.php">{booticon iname="fa-pen-to-square" iexplain="Edit My Homepage"}</a></li>
		{/if}
		{if $gBitSystem->isFeatureActive( 'users_preferences' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}preferences.php">{booticon iname="fa-inbox" iexplain=Preferences}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_users_create_personal_groups' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}my_groups.php">{booticon iname="fa-group" iexplain="My Groups"}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_users_create_personal_roles' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}my_roles.php">{booticon iname="fa-group" iexplain="My Roles"}</a></li>
		{/if}
		{if $gBitSystem->isFeatureActive( 'users_watches' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}watches.php">{booticon iname="fa-asterisk" iexplain="My Watches"}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'messages' ) && $gBitUser->hasPermission( 'p_messages_send' )}
			<li><a class="item" {if $unreadMsgs}title="{tr}You have unread messages{/tr}"{/if} href="{$smarty.const.MESSAGES_PKG_URL}message_box.php">{booticon iname="fa-inbox" iexplain="Message Box"}{if $unreadMsgs}<strong> [{$unreadMsgs}]</strong>{/if}</a></li>
			<li><a class="item" href="{$smarty.const.MESSAGES_PKG_URL}compose.php">{booticon iname="fa-envelope" iexplain="Compose Message"}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'gatekeeper' )}
			<li><a class="item" href="{$smarty.const.GATEKEEPER_PKG_URL}">{booticon iname="fa-lock" iexplain="Security Settings"}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'quota' )}
			<li><a class="item" href="{$smarty.const.QUOTA_PKG_URL}">{booticon iname="fa-hard-drive" iexplain="My quota and usage"}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_liberty_attach_attachments' )}
			<li><a class="item" href="{$smarty.const.LIBERTY_PKG_URL}attachments.php">{booticon iname="fa-paperclip" iexplain="My Files"}</a></li>
		{/if}
		{if $gBitUser->hasPermission('p_liberty_list_content')}
			<li><a class="item" href="{$smarty.const.LIBERTY_PKG_URL}list_content.php">{booticon iname="fa-list" iexplain="List Site Content"}</a></li>
		{/if}
		{if $gBitUser->hasPermission('p_users_view_user_list')}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}index.php">{booticon iname="fa-group" ipackage="icons" iexplain="List Site Users"}</a></li>
		{/if}
		<li><a class="item" href="{$smarty.const.USERS_PKG_URL}logout.php">{booticon iname="fa-signout" iexplain="Log out"}</a></li>
	{else}
		<li><a class="item" href="{$smarty.const.USERS_PKG_URL}signin.php">{booticon iname="fa-signin" iexplain="Login"}</a></li>
		{if $gBitSystem->isFeatureActive('users_allow_register')}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}register.php">{booticon iname="fa-user" iexplain="Register"}</a></li>
		{/if}
	{/if}
</ul>
{/strip}
