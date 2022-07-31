{* $Header$ *}
{bitmodule title="$moduleTitle" name="who_is_there"}
	<div>
		{if $logged_users eq 0}
			{tr}No online users{/tr}
		{else}
		{$logged_users}
		{if $logged_users>1}
			{tr}online users{/tr}
		{elseif $logged_users>0}
			{tr}online user{/tr}
		{/if}
		{/if}
	</div>
	{section name=ix loop=$online_users}
		{if $user and $gBitSystem->isFeatureActive( 'feature_messages' ) and $gBitUser->hasPermission( 'p_messages_send' )}
			<a href="{$smarty.const.MESSAGES_PKG_URL}compose.php?to={$online_users[ix].user}" title="{tr}Send a message to{/tr} {$online_users[ix].user}">{booticon iname="fa-envelope" iexplain="send message"}</a>
		{/if}
		{if $online_users[ix].users_information eq 'public'}
			{math equation="x - y" x=$smarty.now y=$online_users[ix].last_get assign=idle}
			<a href="{$smarty.const.USERS_PKG_URL}index.php?home={$online_users[ix].user_id}" title="{tr}More info about{/tr} {$online_users[ix].login} ({tr}idle{/tr} {$idle} {tr}seconds{/tr})">{$online_users[ix].login}</a><br />
		{else}
			{$online_users[ix].login}<br />
		{/if}
	{/section}
{/bitmodule}
