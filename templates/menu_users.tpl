{strip}
<ul>
	{if $gBitUser->isRegistered()}
		<li><a class="item" href="{$gBitLoc.USERS_PKG_URL}my.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}My {$siteTitle|default:Site}{/tr}</a></li>
		<li><a class="item" href="{$gBitLoc.USERS_PKG_URL}index.php?home={$gBitUser->mInfo.login}">{biticon ipackage=users iname=home iexplain="Home" iforce=icon} {tr}View My Homepage{/tr}</a></li>
		{if $gBitSystem->isPackageActive( 'wiki' )}
			<li><a class="item" href="{$gBitLoc.USERS_PKG_URL}edit_personal_page.php">{biticon ipackage=liberty iname=edit iforce=icon} {tr}Edit My Homepage{/tr}</a></li>
		{/if}
		<li><a class="item" href="{$gBitLoc.LIBERTY_PKG_URL}list_content.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}All available Content{/tr}</a></li>
		{if $gBitSystemPrefs.feature_user_layout eq 'h'}
			{assign var="myLayoutConfig" value="My Homepage"}
		{else if $gBitSystemPrefs.feature_user_layout eq 'y'}
			{assign var="myLayoutConfig" value="My Site Layout"}
		{/if}
		{if $gBitSystemPrefs.feature_user_layout eq 'y' or $gBitSystemPrefs.feature_user_layout eq 'h'
			or $gBitSystemPrefs.feature_user_theme eq 'y' || $gBitSystemPrefs.feature_user_theme eq 'h' }
			<li><a class="item" href="{$gBitLoc.USERS_PKG_URL}assigned_modules.php">{biticon ipackage=liberty iname=config iexplain=Configure iforce=icon} {tr}Configure {$myLayoutConfig}{/tr}</a></li>
		{/if}
		{if $gBitSystemPrefs.feature_userPreferences eq 'y'}
			<li><a class="item" href="{$gBitLoc.USERS_PKG_URL}preferences.php">{biticon ipackage=liberty iname=settings iexplain=Preferences iforce=icon} {tr}Preferences{/tr}</a></li>
		{/if}
		{if $gBitSystemPrefs.feature_user_bookmarks eq 'y' and $gBitUser->hasPermission( 'bit_p_create_bookmarks' )}
			<li><a class="item" href="{$gBitLoc.USERS_PKG_URL}bookmarks.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Bookmarks{/tr}</a></li>
		{/if}
		{if $gBitSystemPrefs.package_messages eq 'y' and $gBitUser->hasPermission( 'bit_p_messages' )}
			<li><a class="item" href="{$gBitLoc.MESSU_PKG_URL}message_box.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Messages{/tr}</a></li> 
		{/if}
		{if $gBitSystemPrefs.package_minical eq 'y'}
			<li><a class="item" href="{$gBitLoc.MINICAL_PKG_URL}index.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Mini calendar{/tr}</a></li>
		{/if}
		{if $gBitUser->isRegistered() && $gBitSystem->isPackageActive('gatekeeper')}
			<li><a class="item" href="{$gBitLoc.GATEKEEPER_PKG_URL}">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Security{/tr}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'quota' )}
			<li><a class="item" href="{$gBitLoc.QUOTA_PKG_URL}">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}My Quota and Usage{/tr}</a></li>
		{/if}
		{if $gBitSystem->isFeatureActive('feature_user_watches') }
			<li><a class="item" href="{$gBitLoc.USERS_PKG_URL}watches.php">{biticon ipackage=users iname=watch iexplain="My Watches" iforce=icon} {tr}My watches{/tr}</a></li>
		{/if}
		{if $gBitSystemPrefs.package_newsreader eq 'y' and $gBitUser->hasPermission( 'bit_p_newsreader' )}
			<li><a class="item" href="{$gBitLoc.NEWSREADER_PKG_URL}index.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Newsreader{/tr}</a></li>
		{/if}
		{if $gBitSystemPrefs.package_notepad eq 'y' and $gBitUser->hasPermission( 'bit_p_notepad' )}
			<li><a class="item" href="{$gBitLoc.NOTEPAD_PKG_URL}index.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Notepad{/tr}</a></li>
		{/if}
		{if $gBitSystemPrefs.feature_tasks eq 'y' and $gBitUser->hasPermission( 'bit_p_tasks' )}
			<li><a class="item" href="{$gBitLoc.USERS_PKG_URL}tasks.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Tasks{/tr}</a></li>
		{/if}
		{if $gBitSystemPrefs.feature_usermenu eq 'y'}
			<li><a class="item" href="{$gBitLoc.USERS_PKG_URL}menu.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}User menu{/tr}</a></li>
		{/if}
		{if $gBitSystemPrefs.package_webmail eq 'y' and $gBitUser->hasPermission( 'bit_p_use_webmail' )}
			<li><a class="item" href="{$gBitLoc.WEBMAIL_PKG_URL}index.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Webmail{/tr}</a></li>
		{/if}
		{if $gBitSystem->isPackageActive( 'messu' )}
			<li><a class="item" href="{$gBitLoc.MESSU_PKG_URL}message_box.php">{biticon ipackage=messu iname=recieve_mail iforce=icon} {tr}Message Box{/tr} {if $unreadMsgs}<strong>[ {$unreadMsgs} ]</strong>{/if}</a></li>
			<li><a class="item" href="{$gBitLoc.MESSU_PKG_URL}compose.php">{biticon ipackage=messu iname=send_mail iforce=icon} {tr}Compose Message{/tr}</a></li>
		{/if}
		<li><a class="item" href="{$gBitLoc.USERS_PKG_URL}my_groups.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}My User Groups{/tr}</a></li>
	{else}
		<li><a class="item" href="{$gBitLoc.USERS_PKG_URL}login.php">{biticon ipackage=liberty iname=spacer iforce=icon} {tr}Login{/tr}</a></li>
	{/if}
</ul>
{/strip}
