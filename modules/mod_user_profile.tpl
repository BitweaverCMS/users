{if $userInfo.is_private neq 'true' or $gBitUser->mUserId eq $userInfo.user_id}
{strip}
{bitmodule}
<div style="text-align:center;">

	{if $userInfo.portrait_url}
	    <img src="{$userInfo.portrait_url}" class="userportrait img-responsive" title="{tr}Portrait{/tr}" alt="{tr}Portrait{/tr}" />
	{elseif $userInfo.avatar_url}
		<img src="{$userInfo.avatar_url}" class="userportrait img-responsive" title="{tr}Avatar{/tr}" alt="{tr}Avatar{/tr}"/>
	{else}
	    {biticon ipackage="users" iname="silhouette" iexplain="" class="userportrait"}
	{/if}

	<div class="floaticon">
		{if $gQueryUserId and $gBitSystem->isPackageActive( 'messages' ) and $gBitUser->hasPermission( 'p_messages_send' ) and $userPrefs.messages_allow_messages eq 'y'}
			&nbsp;<a href="{$smarty.const.MESSAGES_PKG_URL}compose.php?to={$userInfo.login}">{booticon iname="fa-envelope" iexplain="Send user a personal message"}</a>
		{/if}
		{if $gBitUser->hasPermission('p_users_edit_user_homepage')}
			<a href="{$smarty.const.USERS_PKG_URL}preferences.php?view_user={$userInfo.user_id}">{booticon iname="fa-pencil" iexplain="Edit your preferences"}</a>
		{/if}
	</div>

	<h2> {displayname hash=$userInfo} </h2>
</div>

	{if $gBitUser->hasPermission( 'p_users_admin' )}
		 <div>{$userInfo.email|default:'No Email'} ({$userInfo.user_id})</div>
	{/if}

{if $userInfo.publicEmail}
	{$userInfo.publicEmail}
{/if}

	<div>{tr}Joined{/tr}: {$userInfo.registration_date|bit_short_date}</div>
	<div>{tr}Last visit{/tr}: {$userInfo.last_login|bit_short_date}</div>

	{if $userPrefs.flag}{biticon iforce=icon ipackage=users ipath="flags/" iname=$userPrefs.flag iexplain=$userPrefs.flag}{/if} {assign var=langcode value=$userPrefs.bitlanguage|default:$gBitSystem->getConfig('bitlanguage','en')}{$gBitLanguage->mLanguageList.$langcode.native_name}
{/bitmodule}
{/strip}
{/if}
