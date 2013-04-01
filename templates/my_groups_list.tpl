{strip}
<div class="admin users">
	<div class="header">
		<h1>{tr}My Groups{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback success=$successMsg error=$errorMsg}

		{jstabs}
			{jstab title="My System Groups"}
				<table class="data">
					<tr>
						<th>{tr}My Groups{/tr}</th>
						<th>{tr}Description{/tr}</th>
						{if $canRemovePublic}
							<th>{tr}Action{/tr}</th>
						{/if}
					</tr>
					{foreach from=$systemGroups key=groupId item=group}
						<tr class="{cycle values="odd,even"}">
							<td>{$group.group_name}</td>
							<td>{$group.group_desc}</td>
							{if $canRemovePublic}
								<td>
									{if $group.public eq 'y'}
										<a href="{$smarty.const.USERS_PKG_URL}my_groups.php?remove_public_group=y&amp;public_group_id={$groupId}" 
											onclick="return confirm('{tr}Are you sure you want to leave this group?{/tr}')">{biticon ipackage="icons" iname="edit-cut" iexplain="Leave Group"}</a>
									{else}
										&nbsp;
									{/if}
								</td>
							{/if}
						</tr>
					{/foreach}
				</table>

				{if $canAddPublic}
					<br />
					<table class="data">
						<tr>
							<th>{tr}Public Groups{/tr}</th>
							<th>{tr}Description{/tr}</th>
							<th>{tr}Action{/tr}</th>
						</tr>
						{foreach from=$publicGroups key=groupId item=group}
							{if $group.used ne 'y' and $group.is_default ne 'y'}			
								<tr class="{cycle values="odd,even"}">
									<td>{$group.group_name}</td>
									<td>{$group.group_desc}</td>
									<td><a href="{$smarty.const.USERS_PKG_URL}my_groups.php?add_public_group=y&amp;public_group_id={$group.group_id}"  title="{tr}Assign Group{/tr}">{biticon ipackage="icons" iname="emblem-shared" iexplain="join group"}</a></td>								
								</tr>
							{/if}
						{/foreach}
					</table>
				{/if}
			{/jstab}
				
			{if $gBitUser->hasPermission( 'p_users_create_personal_groups' )}

				{jstab title="My User Groups"}
					<a href="{$smarty.const.USERS_PKG_URL}my_groups.php?action=create">{tr}Add new group{/tr}</a>

					<table class="data">
						<tr>
							<th>
								<a href="{$smarty.const.USERS_PKG_URL}my_groups.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'group_name_desc'}group_name_asc{else}group_name_desc{/if}">{tr}Name{/tr}</a>, &amp; 
								{tr}Description{/tr}</a> 
							</th>
							{if $gBitUser->hasPermission( 'p_users_assign_group_members' )}
								<th>{tr}Members{/tr}</th>
							{/if}
							{if $gBitUser->hasPermission( 'p_users_assign_group_perms' )}
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

								{if $gBitUser->hasPermission( 'p_users_assign_group_members' )}
									<td>
										{foreach from=$groupUsers key=userId item=user}
											&nbsp;{displayname hash=$user}<br />
										{foreachelse}
											<strong>{tr}none{/tr}</strong>
										{/foreach}
									</td>
								{/if}

								{if $gBitUser->hasPermission( 'p_users_assign_group_perms' )}
									<td>
										{foreach from=$group.perms key=permName item=perm}
											&nbsp;{$perm.perm_desc}<br />
										{foreachelse}
											<strong>{tr}none{/tr}</strong>
										{/foreach}
									</td>
								{/if}

								<td class="actionicon">
									<a href="{$smarty.const.USERS_PKG_URL}my_groups.php?group_id={$groupId}">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="edit"}</a>
									{if $groupId ne ANONYMOUS_GROUP_ID}
										<a href="{$smarty.const.USERS_PKG_URL}my_groups.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;action=delete&amp;group_id={$groupId}" 
										onclick="return confirm('{tr}Are you sure you want to delete this group?{/tr}')">{biticon ipackage="icons" iname="edit-delete" iexplain="Delete Group"}</a>
									{/if}
								</td>
							</tr>
						{/foreach}
					</table>

					{pagination}
				{/jstab}
			{/if}
		{/jstabs}
	</div><!-- end .body -->
</div><!-- end .users -->
{/strip}
