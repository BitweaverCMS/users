{strip}
<div class="floaticon">
	<a href="{$smarty.const.USERS_PKG_URL}admin/edit_role.php">
		{biticon ipackage="icons" iname="system-users" iexplain="Role List"}
	</a>
	{bithelp}
</div>

<div class="listing users">
	<div class="header">
		<h1>{tr}Role Members{/tr}: {$roleInfo.role_name}</h1>
	</div>

	<div class="body">
		{formfeedback success=$successMsg error=$errorMsg}

		<ol class="data">
			{foreach from=$roleMembers key=userId item=member}
				<li>{displayname hash=$member}
					{if $member.user_id != $smarty.const.ANONYMOUS_USER_ID && $roleInfo.role_id != $smarty.const.ANONYMOUS_TEAM_ID}
						&nbsp;<a href="{$smarty.const.USERS_PKG_URL}admin/assign_user.php?action=removerole&amp;role_id={$roleInfo.role_id}&amp;assign_user={$member.user_id}">{booticon iname="icon-trash" ipackage="icons" iexplain="remove from role"}</a>
					{/if}
				</li>
			{foreachelse}
				<li>{tr}The role has no members.{/tr}</li>
			{/foreach}
		</ol>
	</div><!-- end .body -->
</div>
{/strip}
