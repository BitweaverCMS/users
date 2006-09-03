{strip}
	{bitmodule title="$moduleTitle"}
		<h2 style="text-align:center;">
			{displayname hash=$userInfo}
			{if $gQueryUserId and $gBitSystem->isPackageActive( 'messages' ) and $gBitUser->hasPermission( 'p_messages_send' ) and $userPrefs.messages_allow_messages eq 'y'}
				&nbsp;<a href="{$smarty.const.MESSAGES_PKG_URL}compose.php?to={$userInfo.login}">{biticon ipackage="icons" iname="mail-send-receive" iexplain="Send user a personal message" iforce="icon"}</a>
			{/if}
		</h2>
		<p style="text-align:center;">
			{if $userInfo.avatar_url}
				<img src="{$userInfo.avatar_url}" class="thumb" title="{tr}Avatar{/tr}" alt="{tr}Avatar{/tr}"/>
			{else}
				{biticon ipackage="icons" iname="user-offline" class="thumb" iexplain="no user avatar uploaded" iforce="icon"}
			{/if}
			<br />
			{tr}Last login{/tr}: {$userInfo.last_login|bit_short_date}
		</p>
	{/bitmodule}
{/strip}
