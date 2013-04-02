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

		<ol class="data">
			{foreach from=$groupMembers key=userId item=member}
				<li>{displayname hash=$member}
					{if $member.user_id != $smarty.const.ANONYMOUS_USER_ID && $groupInfo.group_id != $smarty.const.ANONYMOUS_GROUP_ID}
						&nbsp;<a href="{$smarty.const.USERS_PKG_URL}admin/assign_user.php?action=removegroup&amp;group_id={$groupInfo.group_id}&amp;assign_user={$member.user_id}">{booticon iname="icon-trash" ipackage="icons" iexplain="remove from group"}</a>
					{/if}
				</li>
			{foreachelse}
				<li>{tr}The group has no members.{/tr}</li>
			{/foreach}
		</ol>
	</div><!-- end .body -->
</div>
{/strip}
