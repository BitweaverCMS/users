{* $Header: /cvsroot/bitweaver/_bit_users/modules/mod_who_is_there.tpl,v 1.4 2006/01/14 19:56:10 squareing Exp $ *}
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
		{if $user and $gBitSystem->isFeatureActive( 'feature_messages' ) and $gBitUser->hasPermission( 'bit_p_messages' )}
			<a href="{$smarty.const.MESSU_PKG_URL}compose.php?to={$online_users[ix].user}" title="{tr}Send a message to{/tr} {$online_users[ix].user}">{biticon ipackage="users" iname="send_msg_small" iexplain="send message"}</a>
		{/if}
		{if $online_users[ix].user_information eq 'public'}
			{math equation="x - y" x=$smarty.now y=$online_users[ix].timestamp assign=idle}
			<a href="{$smarty.const.USERS_PKG_URL}index.php?home={$online_users[ix].user}" title="{tr}More info about{/tr} {$online_users[ix].user} ({tr}idle{/tr} {$idle} {tr}seconds{/tr})">{$online_users[ix].user}</a><br />
		{else}
			{$online_users[ix].user}<br />
		{/if}
	{/section}
{/bitmodule}
