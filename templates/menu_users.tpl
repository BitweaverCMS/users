{strip}
<ul>
	{if $gBitUser->isRegistered()}
		<li>
				<a class="item" href="{$smarty.const.USERS_PKG_URL}my.php">
					{biticon ipackage=users iname=home iexplain="My Personal Homepage" iforce=icon}{if !$icons_only} {tr}My {$gBitSystem->getConfig('site_menu_title')|default:$gBitSystem->getConfig('site_title')}{/tr}{/if}
				</a>
			</li>
		{if $gBitUser->hasPermission( 'p_users_view_user_homepage' )}
		<li>
				<a class="item" href="{$smarty.const.USERS_PKG_URL}index.php?home={$gBitUser->mInfo.login}">
					{biticon ipackage=users iname=home iexplain="Home" iforce=icon}{if !$icons_only} {tr}View My Homepage{/tr}{/if}
				</a>
			</li>
		{/if}
		{if $gBitUser->hasPermission( 'p_users_edit_user_homepage' )}
			<li>
				<a class="item" href="{$smarty.const.USERS_PKG_URL}edit_personal_page.php">
					{biticon ipackage=liberty iname=edit iexplain="Edit My Homepage" iforce=icon}{if !$icons_only} {tr}Edit My Homepage{/tr}{/if}
				</a>
			</li>
		{/if}
		{if $gBitSystem->getConfig('users_layouts') eq 'h'}
			{assign var="myLayoutConfig" value="My Homepage"}
		{else if $gBitSystem->isFeatureActive( 'users_layouts' )}
			{assign var="myLayoutConfig" value="My Site Layout"}
		{/if}
		{if $gBitUser->canCustomizeTheme() || $gBitUser->canCustomizeLayout() }
			<li>
				<a class="item" href="{$smarty.const.USERS_PKG_URL}assigned_modules.php">
					{biticon ipackage=liberty iname=config iexplain=Configure iforce=icon}{if !$icons_only} {tr}Configure {$myLayoutConfig}{/tr}{/if}
				</a>
			</li>
		{/if}
		{if $gBitSystem->isFeatureActive( 'users_preferences' )}
			<li>
				<a class="item" href="{$smarty.const.USERS_PKG_URL}preferences.php">
					{biticon ipackage=liberty iname=settings iexplain=Preferences iforce=icon}{if !$icons_only} {tr}Preferences{/tr}{/if}
				</a>
			</li>
		{/if}
		{if $gBitUser->hasPermission('p_users_create_personal_groups' )}
			<li>
				<a class="item" href="{$smarty.const.USERS_PKG_URL}my_groups.php">
					{biticon ipackage=users iname=groups iexplain="My Groups" iforce=icon}{if !$icons_only} {tr}My Groups{/tr}{/if}
				</a>
			</li>
		{/if}
		{if $gBitSystem->isPackageActive( 'tidbits' ) and $gBitSystem->isFeatureActive( 'user_bookmarks' ) and $gBitUser->hasPermission( 'p_tidbits_create_bookmarks' )}
			<li>
				<a class="item" href="{$smarty.const.TIDBITS_PKG_URL}bookmarks.php">
					{biticon ipackage=users iname=bookmarks iexplain="Links to my favourite pages" iforce=icon}{if !$icons_only} {tr}Bookmarks{/tr}{/if}
				</a>
			</li>
		{/if}
		{if $gBitSystem->isPackageActive( 'minical' )}
			<li>
				<a class="item" href="{$smarty.const.MINICAL_PKG_URL}index.php">
					{biticon ipackage=liberty iname=spacer iexplain="Mini Calendar" iforce=icon}{if !$icons_only} {tr}Mini calendar{/tr}{/if}
				</a>
			</li>
		{/if}
		{if $gBitSystem->isFeatureActive('users_watches') }
			<li>
				<a class="item" href="{$smarty.const.USERS_PKG_URL}watches.php">
					{biticon ipackage=users iname=watch iexplain="My Watches" iforce=icon}{if !$icons_only} {tr}My watches{/tr}{/if}
				</a>
			</li>
		{/if}
		{if $gBitSystem->isPackageActive( 'newsreader' ) and $gBitUser->hasPermission( 'bit_p_newsreader' )}
			<li>
				<a class="item" href="{$smarty.const.NEWSREADER_PKG_URL}index.php">
					{biticon ipackage=liberty iname=spacer iexplain="Newsreader" iforce=icon}{if !$icons_only} {tr}Newsreader{/tr}{/if}
				</a>
			</li>
		{/if}
		{if $gBitSystem->isPackageActive( 'notepad' ) and $gBitUser->hasPermission( 'bit_p_notepad' )}
			<li>
				<a class="item" href="{$smarty.const.NOTEPAD_PKG_URL}index.php">
					{biticon ipackage=liberty iname=spacer iexplain="Notepad" iforce=icon}{if !$icons_only} {tr}Notepad{/tr}{/if}
				</a>
			</li>
		{/if}
		{if $gBitSystem->isPackageActive( 'tidbits' ) and $gBitSystem->isFeatureActive( 'feature_tasks' ) and $gBitUser->hasPermission( 'p_tidbits_use_tasks' )}
			<li>
				<a class="item" href="{$smarty.const.TIDBITS_PKG_URL}tasks.php">
					{biticon ipackage=users iname=tasks iexplain="Tasks" iforce=icon}{if !$icons_only} {tr}Tasks{/tr}{/if}
				</a>
			</li>
		{/if}
		{if $gBitSystem->isPackageActive( 'tidbits' ) and $gBitSystem->isFeatureActive( 'usermenu' )}
			<li>
				<a class="item" href="{$smarty.const.TIDBITS_PKG_URL}menu.php">
					{biticon ipackage=liberty iname=tree iexplain="User Mneu" iforce=icon}{if !$icons_only} {tr}User menu{/tr}{/if}
				</a>
			</li>
		{/if}
		{if $gBitSystem->isPackageActive( 'webmail' ) and $gBitUser->hasPermission( 'bit_p_use_webmail' )}
			<li>
				<a class="item" href="{$smarty.const.WEBMAIL_PKG_URL}index.php">
					{biticon ipackage=liberty iname=spacer iexplain="Webmail" iforce=icon}{if !$icons_only} {tr}Webmail{/tr}{/if}
				</a>
			</li>
		{/if}
		{if $gBitSystem->isPackageActive( 'messages' )}
			<li>
				<a class="item" {if $unreadMsgs}title="{tr}You have unread messages{/tr}"{/if} href="{$smarty.const.MESSAGES_PKG_URL}message_box.php">
					{biticon ipackage=messages iname=recieve_mail iexplain="Send and recieve personal messages" iforce=icon}{if !$icons_only} {tr}Message Box{/tr} {/if}{if $unreadMsgs}<strong>[ {$unreadMsgs} ]</strong>{/if}
				</a>
			</li>
			<li>
				<a class="item" href="{$smarty.const.MESSAGES_PKG_URL}compose.php">
					{biticon ipackage=messages iname=send_mail iexplain="Send a personal messages to a user" iforce=icon}{if !$icons_only} {tr}Compose Message{/tr}{/if}
				</a>
			</li>
		{/if}
		{if $gBitSystem->isPackageActive('gatekeeper')}
			<li>
				<a class="item" href="{$smarty.const.GATEKEEPER_PKG_URL}">
					{biticon ipackage=liberty iname=security iexplain="Administer personal security settings" iforce=icon}{if !$icons_only} {tr}Security{/tr}{/if}
				</a>
			</li>
		{/if}
		{if $gBitSystem->isPackageActive( 'quota' )}
			<li>
				<a class="item" href="{$smarty.const.QUOTA_PKG_URL}">
					{biticon ipackage=quota iname=quota iexplain="My quota and usage" iforce=icon}{if !$icons_only} {tr}My Quota and Usage{/tr}{/if}
				</a>
			</li>
		{/if}
	{else}
		<li>
			<a class="item" href="{$smarty.const.USERS_PKG_URL}login.php">
				{biticon ipackage=liberty iname=spacer iexplain="Login" iforce=icon}{if !$icons_only} {tr}Login{/tr}{/if}
			</a>
		</li>
	{/if}
</ul>
{/strip}
