{strip}
<div class="admin users">
	<div class="header">
		<h1>{tr}My Roles{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback success=$successMsg error=$errorMsg}

		{jstabs}
			{jstab title="My System Roles"}
				<table class="data">
					<tr>
						<th>{tr}My Roles{/tr}</th>
						<th>{tr}Description{/tr}</th>
						{if $canRemovePublic}
							<th>{tr}Action{/tr}</th>
						{/if}
					</tr>
					{foreach from=$systemRoles key=roleId item=role}
						<tr class="{cycle values="odd,even"}">
							<td>{$role.role_name}</td>
							<td>{$role.role_desc}</td>
							{if $canRemovePublic}
								<td>
									{if $role.public eq 'y'}
										<a href="{$smarty.const.USERS_PKG_URL}my_roles.php?remove_public_role=y&amp;public_role_id={$roleId}" 
											onclick="return confirm('{tr}Are you sure you want to leave this role?{/tr}')">{booticon ipackage="icons" iname="icon-cut" iexplain="Leave Role"}</a>
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
							<th>{tr}Public Roles{/tr}</th>
							<th>{tr}Description{/tr}</th>
							<th>{tr}Action{/tr}</th>
						</tr>
						{foreach from=$publicRoles key=roleId item=role}
							{if $role.used ne 'y' and $role.is_default ne 'y'}			
								<tr class="{cycle values="odd,even"}">
									<td>{$role.role_name}</td>
									<td>{$role.role_desc}</td>
									<td><a href="{$smarty.const.USERS_PKG_URL}my_roles.php?add_public_role=y&amp;public_role_id={$role.role_id}"  title="{tr}Assign Role{/tr}">{booticon iname="icon-key" ipackage="icons" iexplain="join role"}</a></td>								
								</tr>
							{/if}
						{/foreach}
					</table>
				{/if}
			{/jstab}
				
			{if $gBitUser->hasPermission( 'p_users_create_personal_roles' )}

				{jstab title="My User Roles"}
					<a href="{$smarty.const.USERS_PKG_URL}my_roles.php?action=create">{tr}Add new role{/tr}</a>

					<table class="data">
						<tr>
							<th>
								<a href="{$smarty.const.USERS_PKG_URL}my_roles.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'role_name_desc'}role_name_asc{else}role_name_desc{/if}">{tr}Name{/tr}</a>, &amp; 
								{tr}Description{/tr}</a> 
							</th>
							{if $gBitUser->hasPermission( 'p_users_assign_role_members' )}
								<th>{tr}Members{/tr}</th>
							{/if}
							{if $gBitUser->hasPermission( 'p_users_assign_role_perms' )}
								<th>{tr}Permissions{/tr}</th>
							{/if}
							<th>{tr}Action{/tr}</th>
						</tr>

						{foreach from=$roles key=roleId item=role}
							<tr class="{cycle values="odd,even"}">
								<td>
									<strong>{$role.role_name}</strong>{if $role.is_default eq 'y'}<em class="warning"> *{tr}Default role{/tr}*</em>{/if}<br />
									{$role.role_desc}<br />
									{if $role.role_home}{tr}Home Page{/tr}:<strong> {$role.role_home}</strong><br />{/if}
									{if $role.included}
										{tr}Included Roles{/tr}
										<ul>
											{foreach from=$role.included key=incRoleId item=incRoleName}
												<li>{$incRoleName}</li>
											{/foreach}
										</ul>
									{/if}
								</td>

								{if $gBitUser->hasPermission( 'p_users_assign_role_members' )}
									<td>
										{foreach from=$roleUsers key=userId item=user}
											&nbsp;{displayname hash=$user}<br />
										{foreachelse}
											<strong>{tr}none{/tr}</strong>
										{/foreach}
									</td>
								{/if}

								{if $gBitUser->hasPermission( 'p_users_assign_role_perms' )}
									<td>
										{foreach from=$role.perms key=permName item=perm}
											&nbsp;{$perm.perm_desc}<br />
										{foreachelse}
											<strong>{tr}none{/tr}</strong>
										{/foreach}
									</td>
								{/if}

								<td class="actionicon">
									<a href="{$smarty.const.USERS_PKG_URL}my_roles.php?role_id={$roleId}">{booticon iname="icon-edit" ipackage="icons" iexplain="edit"}</a>
									{if $roleId ne -1}{* sorry for hardcoding, really need php define ANONYMOUS_TEAM_ID - spiderr *}
										<a href="{$smarty.const.USERS_PKG_URL}my_roles.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;action=delete&amp;role_id={$roleId}" 
										onclick="return confirm('{tr}Are you sure you want to delete this role?{/tr}')">{booticon iname="icon-trash" ipackage="icons" iexplain="Delete Role"}</a>
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
