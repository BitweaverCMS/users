{strip}
<div class="floaticon">
	<a href="{$smarty.const.USERS_PKG_URL}admin/edit_group.php">
		{booticon iname="fa-group" iexplain="Group List"}
	</a>
	{bithelp}
</div>

<div class="listing users">
	<div class="header">
		<h1>{tr}Group Members{/tr}: {$groupInfo.group_name}</h1>
	</div>

	<div class="body">
		{formfeedback success=$successMsg error=$errorMsg}

		<table class="table">
		<thead>
			<tr>
				<td class="text-right">#</td>
				<td>{tr}Name{/tr}</td>
				<td>{tr}Email{/tr}</td>
				<td class="text-right">{tr}Registered{/tr}</td>
				<td class="text-right">{tr}Last Login{/tr}</td>
				<td></td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$groupMembers key=userId item=member name=groupMembers}
				<tr>
					<td class="text-right">{$smarty.foreach.groupMembers.iteration}</td>
					<td>{displayname hash=$member}</td>
					<td>{$member.email}</td>
					<td class="text-right">{$member.registration_date|bit_short_date}</td>
					<td class="text-right">{$member.last_login|bit_short_date}</td>
					<td>
					{if $gBitSystem->isPackageActive('bitcommerce')} &nbsp;<a class="icon" href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/list_orders.php?user_id={$userId}">{booticon iname="fa-shopping-cart"}</a>{/if}
&nbsp;<a class="icon" href="{$smarty.const.LIBERTY_PKG_URL}list_content.php?user_id={$userId}{if $gBitSystem->isPackageActive('bitcommerce')}&amp;content_type_guid=bitproduct{/if}">{booticon iname="fa-square-list"}</a>
&nbsp;<a class="icon" href="{$smarty.const.USERS_PKG_URL}admin/user_activity.php?user_id={$userId}">{booticon iname="fa-bolt"}</a>
					{if $member.user_id != $smarty.const.ANONYMOUS_USER_ID && $groupInfo.group_id != $smarty.const.ANONYMOUS_GROUP_ID}
						&nbsp;<a class="icon" href="{$smarty.const.USERS_PKG_URL}admin/assign_user.php?action=removegroup&amp;group_id={$groupInfo.group_id}&amp;assign_user={$member.user_id}">{booticon iname="fa-remove-circle" iexplain="Remove From Group"}</a>
					{/if}
					</td>
				</tr>
			{foreachelse}
				<tr>{tr}The group has no members.{/tr}</tr>
			{/foreach}
		</tbody>
		</table>
	</div><!-- end .body -->
</div>
{/strip}
