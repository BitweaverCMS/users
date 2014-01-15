{strip}
<div class="floaticon">
	<a href="{$smarty.const.USERS_PKG_URL}admin/edit_group.php">
		{booticon iname="icon-group"  ipackage="icons"  iexplain="Group List"}
	</a>
	{bithelp}
</div>

<div class="listing users">
	<div class="header">
		<h1>{tr}Group Members{/tr}: {$groupInfo.group_name}</h1>
	</div>

	<div class="body">
		{formfeedback success=$successMsg error=$errorMsg}

		<table class="table table-hover">
			<tr>
				<th></th>
				<th>{tr}Real Name{/tr}</th>
				<th>{tr}Username{/tr}</th>
				<th></th>
			</tr>
			{foreach name=groupMembers from=$groupMembers key=userId item=member}
			<tr>
				<td>{$smarty.foreach.groupMembers.iteration}</td>
				<td>{displayname hash=$member}</td>
				<td>{$member.login}</td>
				<td>{if $member.user_id != $smarty.const.ANONYMOUS_USER_ID && $groupInfo.group_id != $smarty.const.ANONYMOUS_GROUP_ID}
						<a class="icon" href="{$smarty.const.USERS_PKG_URL}admin/assign_user.php?action=removegroup&amp;group_id={$groupInfo.group_id}&amp;assign_user={$member.user_id}&amp;tk={$gBitUser->mTicket}">{booticon iname="icon-trash" ipackage="icons" iexplain="remove from group"}</a>
					{/if}
				</td>
			</tr>
			{foreachelse}
			<tr>
				<td colspan="4">{tr}The group has no members.{/tr}</td>
			</tr>
			{/foreach}
		</table>
	</div><!-- end .body -->
</div>
{/strip}
