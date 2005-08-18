{strip}
<ul>
	{if $gBitUser->isRegistered()}
		<li><a class="item" href="{$smarty.const.USERS_PKG_URL}my.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}My {$gBitSystemPrefs.site_menu_title|default:$siteTitle}{/tr}</a></li>
		<li><a class="item" href="{$smarty.const.USERS_PKG_URL}index.php?home={$gBitUser->mInfo.login}">{biticon ipackage=users iname=home iexplain="Home" iforce=icon} {tr}View My Homepage{/tr}</a></li>
		{if $gBitSystem->isPackageActive( 'wiki' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}edit_personal_page.php">{biticon ipackage=liberty iname=edit iforce=icon} {tr}Edit My Homepage{/tr}</a></li>
		{/if}
		{if $gBitSystemPrefs.feature_user_layout eq 'h'}
			{assign var="myLayoutConfig" value="My Homepage"}
		{else if $gBitSystem->isFeatureActive( 'feature_user_layout' )}
			{assign var="myLayoutConfig" value="My Site Layout"}
		{/if}
		{if $gBitSystem->isFeatureActive( 'feature_user_layout' ) or $gBitSystemPrefs.feature_user_layout eq 'h'
			or $gBitSystem->isFeatureActive( 'feature_user_theme' ) || $gBitSystemPrefs.feature_user_theme eq 'h' }
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}assigned_modules.php">{biticon ipackage=liberty iname=config iexplain=Configure iforce=icon} {tr}Configure {$myLayoutConfig}{/tr}</a></li>
		{/if}
		{if $gBitSystem->isFeatureActive( 'feature_userPreferences' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}preferences.php">{biticon ipackage=liberty iname=settings iexplain=Preferences iforce=icon} {tr}Preferences{/tr}</a></li>
		{/if}
		{if $gBitSystem->isFeatureActive( 'feature_user_bookmarks' ) and $gBitUser->hasPermission( 'bit_p_create_bookmarks' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}bookmarks.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Bookmarks{/tr}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'messages' ) and $gBitUser->hasPermission( 'bit_p_messages' )}
			<li><a class="item" href="{$smarty.const.MESSU_PKG_URL}message_box.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Messages{/tr}</a></li> 
		{/if}
		{if $gBitSystem->isPackageActive( 'minical' )}
			<li><a class="item" href="{$smarty.const.MINICAL_PKG_URL}index.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Mini calendar{/tr}</a></li>
		{/if}
		{if $gBitUser->isRegistered() && $gBitSystem->isPackageActive('gatekeeper')}
			<li><a class="item" href="{$smarty.const.GATEKEEPER_PKG_URL}">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Security{/tr}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'quota' )}
			<li><a class="item" href="{$smarty.const.QUOTA_PKG_URL}">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}My Quota and Usage{/tr}</a></li>
		{/if}
		{if $gBitSystem->isFeatureActive('feature_user_watches') }
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}watches.php">{biticon ipackage=users iname=watch iexplain="My Watches" iforce=icon} {tr}My watches{/tr}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'newsreader' ) and $gBitUser->hasPermission( 'bit_p_newsreader' )}
			<li><a class="item" href="{$smarty.const.NEWSREADER_PKG_URL}index.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Newsreader{/tr}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'notepad' ) and $gBitUser->hasPermission( 'bit_p_notepad' )}
			<li><a class="item" href="{$smarty.const.NOTEPAD_PKG_URL}index.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Notepad{/tr}</a></li>
		{/if}
		{if $gBitSystem->isFeatureActive( 'feature_tasks' ) and $gBitUser->hasPermission( 'bit_p_tasks' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}tasks.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Tasks{/tr}</a></li>
		{/if}
		{if $gBitSystem->isFeatureActive( 'feature_usermenu' )}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}menu.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}User menu{/tr}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'webmail' ) and $gBitUser->hasPermission( 'bit_p_use_webmail' )}
			<li><a class="item" href="{$smarty.const.WEBMAIL_PKG_URL}index.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Webmail{/tr}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'messu' )}
			<li><a class="item" href="{$smarty.const.MESSU_PKG_URL}message_box.php">{biticon ipackage=messu iname=recieve_mail iforce=icon} {tr}Message Box{/tr} {if $unreadMsgs}<strong>[ {$unreadMsgs} ]</strong>{/if}</a></li>
			<li><a class="item" href="{$smarty.const.MESSU_PKG_URL}compose.php">{biticon ipackage=messu iname=send_mail iforce=icon} {tr}Compose Message{/tr}</a></li>
		{/if}
		<li><a class="item" href="{$smarty.const.USERS_PKG_URL}my_groups.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}My User Groups{/tr}</a></li>
	{else}
		<li><a class="item" href="{$smarty.const.USERS_PKG_URL}login.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Login{/tr}</a></li>
	{/if}
</ul>
{/strip}
