{strip}
	{bitmodule title="$moduleTitle"}
		<h2 style="text-align:center;">
			{displayname hash=$userInfo}
			{if $gQueryUserId and $gBitSystem->isPackageActive( 'messu' ) and $gBitUser->hasPermission( 'bit_p_messages' ) and $userPrefs.allowMsgs eq 'y'}
				&nbsp;<a href="{$gBitLoc.MESSU_PKG_URL}compose.php?to={$userInfo.login}">{biticon ipackage="messu" iname="send_mail" iexplain="Send user a personal message"}</a>
			{/if}
		</h2>
		<p style="text-align:center;">
			{if $userInfo.avatar_url}
				<img src="{$userInfo.avatar_url}" class="thumb" title="{tr}Avatar{/tr}" alt="{tr}Avatar{/tr}"/>
			{else}
				{biticon ipackage=users iname='unknown_user' class='thumb' iexplain='no user avatar uploaded'}
			{/if}
			<br />
			{tr}Last login{/tr}: {$userInfo.last_login|bit_short_date}
		</p>
	{/bitmodule}
{/strip}
