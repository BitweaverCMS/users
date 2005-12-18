<div class="admin users">
	<div class="header">
		<h1>{tr}List of existing groups{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback success=$successMsg error=$errorMsg}

		<a href="{$smarty.const.USERS_PKG_URL}my_groups.php?action=create">{tr}Add new group{/tr}</a>

		<table class="data">
			<tr>
				<th>
					<a href="{$smarty.const.USERS_PKG_URL}my_groups.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'group_name_desc'}group_name_asc{else}group_name_desc{/if}">{tr}Name{/tr}</a>, &amp; 
					{tr}Description{/tr}</a> 
				</th>
			{if $gBitUser->hasPermission( 'bit_p_user_group_members' )}
				<th>{tr}Members{/tr}</th>
			{/if}
			{if $gBitUser->hasPermission( 'bit_p_user_group_perms' )}
				<th>{tr}Permissions{/tr}</th>
			{/if}
				<th>{tr}Action{/tr}</th>
			</tr>

			{foreach from=$groups key=groupId item=group}
				<tr class="{cycle values="odd,even"}">
					<td>
						<strong>{$group.group_name}</strong>{if $group.is_default eq 'y'}<em class="warning"> *{tr}Default group{/tr}*</em>{/if}<br />
						{$group.group_desc}<br />
						{if $group.group_home}{tr}Home Page{/tr}:<strong> {$group.group_home}</strong><br />{/if}
						{if $group.included}
							{tr}Included Groups{/tr}
							<ul>
								{foreach from=$group.included key=incGroupId item=incGroupName}
									<li>{$incGroupName}</li>
								{/foreach}
							</ul>
						{/if}
					</td>

					{if $gBitUser->hasPermission( 'bit_p_user_group_members' )}
						<td>
							{foreach from=$groupUsers key=userId item=user}
								&nbsp;{displayname hash=$user}<br />
							{foreachelse}
								<strong>{tr}none{/tr}</strong>
							{/foreach}
						</td>
					{/if}

					{if $gBitUser->hasPermission( 'bit_p_user_group_perms' )}
						<td>
							{foreach from=$group.perms key=permName item=perm}
								&nbsp;{$perm.perm_desc}<br />
							{foreachelse}
								<strong>{tr}none{/tr}</strong>
							{/foreach}
						</td>
					{/if}

					<td class="actionicon">
						<a href="{$smarty.const.USERS_PKG_URL}my_groups.php?group_id={$groupId}">{biticon ipackage=liberty iname="edit" iexplain="edit"}</a>
						{if $groupId ne -1}{* sorry for hardcoding, really need php define ANONYMOUS_GROUP_ID - spiderr *}
							<a href="{$smarty.const.USERS_PKG_URL}my_groups.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;action=delete&amp;group_id={$groupId}" 
							onclick="return confirm('{tr}Are you sure you want to delete this group?{/tr}')">{biticon ipackage=liberty iname="delete" iexplain="remove"}</a>
						{/if}
					</td>
				</tr>
			{/foreach}
		</table>

		{pagination}
	</div><!-- end .body -->
</div><!-- end .users -->
