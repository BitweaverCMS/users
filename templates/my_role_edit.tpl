{* $Header$ *}
{strip}
<div class="floaticon">
	<a href="{$smarty.const.USERS_PKG_URL}my_roles.php">{tr}&laquo; Role List{/tr}</a> 
	{bithelp}
</div>

<div class="admin roles">
	<div class="header">
		<h1>{if $roleInfo.role_name}{tr}Administer Role{/tr}: {$roleInfo.role_name}{else}{tr}Create New Role{/tr}{/if}</h1>
	</div>

	<div class="body">
		{formfeedback success=$successMsg error=$errorMsg}

		{jstabs}
			{jstab title="Edit Role"}
				{form legend="Add or Edit a Role"}
					<input type="hidden" name="role_id" value="{$roleInfo.role_id}" />
					<div class="control-group">
						{formlabel label="Role" for="roles_role"}
						{forminput}
							<input type="text" name="name" id="roles_role" value="{$roleInfo.role_name}" />
						{/forminput}
					</div>

					<div class="control-group">
						{formlabel label="Description" for="role_desc"}
						{forminput}
							<textarea rows="5" cols="20" name="desc" id="role_desc">{$roleInfo.role_desc}</textarea>
						{/forminput}
					</div>
					
					{if $roles && $gBitUser->hasPermission( 'p_users_role_subroles' )}
						<div class="control-group">
							{formlabel label="Include" for="roles_inc"}
							{forminput}
								<select name="include_roles[]" id="roles_inc" multiple="multiple" size="4">
								{foreach from=$roles key=roleId item=role}
									{if $roleId != $roleInfo.role_id}
										<option value="{$roleId}" {if $role.included eq 'y'} selected="selected"{/if}>{$role.role_name}</option>
									{/if}
								{/foreach}
								</select>
								{formhelp note="If you include a role, this role will inherit all permissions of the included role."}
							{/forminput}
						</div>
					{/if}

					<div class="control-group submit">
						<input type="submit" class="btn" name="cancel" value="{tr}Cancel{/tr}" />&nbsp;
						<input type="submit" class="btn" name="save" value="{tr}Save Role{/tr}" />
					</div>
				{/form}
			{/jstab}

			{if $gBitUser->hasPermission( 'p_users_assign_role_members' ) && !empty($roleInfo.role_id)}
				{jstab title="Members"}
					<ul>
						{foreach from=$roleUsers key=userId item=userHash}
							<li>{displayname hash=$userHash}</li>
						{foreachelse}
							<li><strong>{tr}none{/tr}</strong> - {tr}You are the only user.{/tr}</li>
						{/foreach}
					</ul>

					<div>
						{form legend="User Search"}
							<input type="hidden" name="role_id" value="{$roleInfo.role_id}" />
							<input type="hidden" name="tab" value="members" />
							<div class="control-group">
								{formlabel label="Username" for="username"}
								{forminput}
									<input type="text" id="username" name="find" value="{$find}"/>
								{/forminput}
							</div>
								
							<div class="control-group submit">
								<input type="submit" class="btn" name="submitUserSearch" value="Search"/>
							</div>
						{/form}
					</div>
					
					{if $foundUsers}
						<table>
							<caption>{tr}Search Results{/tr}</caption>
							<tr>
								<th>{tr}Username{/tr}</th>
								<th>{tr}Real Name{/tr}</th>
								<th>{tr}User Id{/tr}</th>
								<th>{tr}Actions{/tr}</th>
							</tr>
							{section name=ix loop=$foundUsers}
								<tr class="{cycle values='odd,even'}">
									<td>{$foundUsers[ix].login}</td>
									<td>{$foundUsers[ix].real_name}</td>
									<td>{$foundUsers[ix].user_id}</td>
									<td class="actionicon">{smartlink ititle="Select User" role_id=$roleInfo.role_id assignuser=$foundUsers[ix].user_id}</td>
								</tr>
							{/section}
						</table>
					{/if}
				{/jstab}
			{/if}

			{if $gBitUser->hasPermission( 'p_users_assign_role_perms' )}
				{if $roleInfo.role_id}
					{if $roleInfo.perms}
						{jstab title="Permissions"}
							{form legend="Permissions currently assigned to `$roleInfo.role_name`"}
								<table class="table data">
									<tr>
										<th>{tr}Permission{/tr}</th>
										<th>{tr}Description{/tr}</th>
									</tr>
									{foreach from=$roleInfo.perms key=permName item=perm}
										<tr class="{cycle values="odd,even"}">
											<td>
												{smartlink ititle="Remove" booticon="icon-trash" package=$package role_id=$roleInfo.role_id action=remove permission=$permName}
												&nbsp;{$permName}
											</td>
											<td>{$perm.perm_desc}</td>
										</tr>
									{/foreach}
								</table>
							{/form}
						{/jstab}
					{/if}

					{jstab title="Assign Permissions"}
						{form legend="Assign permissions"}
							<input type="hidden" name="role_id" value="{$roleInfo.role_id}" />
							<input type="hidden" name="package" value="{$package|escape}" />
							<input type="hidden" name="tab" value="assign" />
							<input type="hidden" name="perm_name[{$perms[user].perm_name}]" />

							<div class="control-group">
								{formlabel label="Display permissions of package"}
								{forminput}
									{smartlink ititle="All packages" role_id=$roleInfo.role_id}
									{foreach from=$gBitSystem->mPackages key=packageKey item=packageItem}
										{if $packageItem.installed} 
											&nbsp;&bull; {smartlink ititle=$gBitSystem->mPackages.$packageKey.name role_id=$roleInfo.role_id package=$packageKey}
										{/if}
									{/foreach}
								{/forminput}
							</div>

							<div class="control-group">
								<table class="table data">
									<tr>
										<th>&nbsp;</th>
										<th>{smartlink ititle="Name" isort=perm_name role_id=$roleInfo.role_id offset=$offset package=$package}</th>
										<th>{smartlink ititle="Package" isort=package role_id=$roleInfo.role_id offset=$offset package=$package}</th>
										<th>{smartlink ititle="Description" isort=perm_desc role_id=$roleInfo.role_id offset=$offset package=$package}</th>
										<th>&nbsp;</th>
									</tr>
									{foreach key=permName item=perm from=$allPerms}
										{if $package eq $perm.package or $package eq 'all'}
											<tr class="{cycle values="even,odd"}">
												<td><input type="checkbox" id="{$permName}" name="perm[{$permName}]"{if $roleInfo.perms.$permName} checked="checked"{/if} /></td>
												<td><label for="{$permName}">{$permName}</label></td>
												<td>{tr}{$perm.package}{/tr}</td>
												<td>{tr}{$perm.perm_desc}{/tr}</td>
											</tr>
										{/if}
									{/foreach}
								</table>
							</div>

							<div class="control-group submit">
								<input type="submit" class="btn" name="updateperms" value="{tr}Update{/tr}" />
							</div>
						{/form}
					{/jstab}
				{/if}
			{/if}
		{/jstabs}
	</div><!-- end .body -->
</div><!-- end .users -->

{/strip}
