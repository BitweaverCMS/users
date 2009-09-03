{if $userInfo.is_private neq 'true' or $gBitUser->mUserId eq $userInfo.user_id}
{strip}
{bitmodule title="$moduleTitle"}
<div style="text-align:center;">

	{if $userInfo.portrait_url}
	    <img src="{$userInfo.portrait_url}" style="width:180px" class="icon" title="{tr}Portrait{/tr}" alt="{tr}Portrait{/tr}" />
	{elseif $userInfo.avatar_url}
		<img src="{$userInfo.avatar_url}" class="thumb" title="{tr}Avatar{/tr}" alt="{tr}Avatar{/tr}"/>
	{else}
	    {biticon ipackage="users" iname="unknown_user" iexplain=""}
	{/if}

	<div class="floaticon">
		{if $gQueryUserId and $gBitSystem->isPackageActive( 'messages' ) and $gBitUser->hasPermission( 'p_messages_send' ) and $userPrefs.messages_allow_messages eq 'y'}
			&nbsp;<a href="{$smarty.const.MESSAGES_PKG_URL}compose.php?to={$userInfo.login}">{biticon ipackage="icons" iname="mail-forward" iexplain="Send user a personal message" iforce="icon"}</a>
		{/if}
		{if $gBitUser->hasPermission('p_users_edit_user_homepage')}
			<a href="{$smarty.const.USERS_PKG_URL}preferences.php?view_user={$userInfo.user_id}">{biticon iname="accessories-text-editor" ipackage="icons" iexplain="Edit your preferences"}</a>
		{/if}
	</div>

	<h2> {displayname hash=$userInfo} </h2>
</div>

	{if $gBitUser->hasPermission( 'p_users_admin' )}
		 <div>{$userInfo.email} ({$userInfo.user_id})</div>
	{/if}

{if $userInfo.publicEmail}
	{$userInfo.publicEmail}
{/if}

	<div>{tr}Member Since{/tr}: {$userInfo.registration_date|bit_short_date}</div>
	<div>{tr}Last login{/tr}: {$userInfo.last_login|bit_short_date}</div>

	{biticon iforce=icon ipackage=users ipath=flags/ iname=$userPrefs.flag iexplain=$userPrefs.flag} {assign var=langcode value=$userInfo.lang_code|default:$gBitSystem->getConfig('bitlanguage','en')}{$gBitLanguage->mLanguageList.$langcode.native_name}
{/bitmodule}
{/strip}
{/if}
