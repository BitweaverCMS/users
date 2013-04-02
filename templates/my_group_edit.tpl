{* $Header$ *}
{strip}
<div class="floaticon">
	<a href="{$smarty.const.USERS_PKG_URL}my_groups.php">{tr}&laquo; Group List{/tr}</a> 
	{bithelp}
</div>

<div class="admin groups">
	<div class="header">
		<h1>{if $groupInfo.group_name}{tr}Administer Group{/tr}: {$groupInfo.group_name}{else}{tr}Create New Group{/tr}{/if}</h1>
	</div>

	<div class="body">
		{formfeedback success=$successMsg error=$errorMsg}

		{jstabs}
			{jstab title="Edit Group"}
				{form legend="Add or Edit a Group"}
					<input type="hidden" name="group_id" value="{$groupInfo.group_id}" />
					<div class="control-group">
						{formlabel label="Group" for="groups_group"}
						{forminput}
							<input type="text" name="name" id="groups_group" value="{$groupInfo.group_name}" />
						{/forminput}
					</div>

					<div class="control-group">
						{formlabel label="Description" for="group_desc"}
						{forminput}
							<textarea rows="5" cols="20" name="desc" id="group_desc">{$groupInfo.group_desc}</textarea>
						{/forminput}
					</div>
					
					{if $groups && $gBitUser->hasPermission( 'p_users_group_subgroups' )}
						<div class="control-group">
							{formlabel label="Include" for="groups_inc"}
							{forminput}
								<select name="include_groups[]" id="groups_inc" multiple="multiple" size="4">
								{foreach from=$groups key=groupId item=group}
									{if $groupId != $groupInfo.group_id}
										<option value="{$groupId}" {if $group.included eq 'y'} selected="selected"{/if}>{$group.group_name}</option>
									{/if}
								{/foreach}
								</select>
								{formhelp note="If you include a group, this group will inherit all permissions of the included group."}
							{/forminput}
						</div>
					{/if}

					<div class="control-group submit">
						<input type="submit" name="cancel" value="{tr}Cancel{/tr}" />&nbsp;
						<input type="submit" name="save" value="{tr}Save Group{/tr}" />
					</div>
				{/form}
			{/jstab}

			{if $gBitUser->hasPermission( 'p_users_assign_group_members' ) && !empty($groupInfo.group_id)}
				{jstab title="Members"}
					<ul>
						{foreach from=$groupUsers key=userId item=userHash}
							<li>{displayname hash=$userHash}</li>
						{foreachelse}
							<li><strong>{tr}none{/tr}</strong> - {tr}You are the only user.{/tr}</li>
						{/foreach}
					</ul>

					<div>
						{form legend="User Search"}
							<input type="hidden" name="group_id" value="{$groupInfo.group_id}" />
							<input type="hidden" name="tab" value="members" />
							<div class="control-group">
								{formlabel label="Username" for="username"}
								{forminput}
									<input type="text" id="username" name="find" value="{$find}"/>
								{/forminput}
							</div>
								
							<div class="control-group submit">
								<input type="submit" name="submitUserSearch" value="Search"/>
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
									<td class="actionicon">{smartlink ititle="Select User" group_id=`$groupInfo.group_id` assignuser=`$foundUsers[ix].user_id`}</td>
								</tr>
							{/section}
						</table>
					{/if}
				{/jstab}
			{/if}

			{if $gBitUser->hasPermission( 'p_users_assign_group_perms' )}
				{if $groupInfo.group_id}
					{if $groupInfo.perms}
						{jstab title="Permissions"}
							{form legend="Permissions currently assigned to `$groupInfo.group_name`"}
								<table class="data">
									<tr>
										<th>{tr}Permission{/tr}</th>
										<th>{tr}Description{/tr}</th>
									</tr>
									{foreach from=$groupInfo.perms key=permName item=perm}
										<tr class="{cycle values="odd,even"}">
											<td>
												{smartlink ititle="Remove" booticon="icon-trash" package=$package group_id=$groupInfo.group_id action=remove permission=$permName}
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
							<input type="hidden" name="group_id" value="{$groupInfo.group_id}" />
							<input type="hidden" name="package" value="{$package|escape}" />
							<input type="hidden" name="tab" value="assign" />
							<input type="hidden" name="perm_name[{$perms[user].perm_name}]" />

							<div class="control-group">
								{formlabel label="Display permissions of package"}
								{forminput}
									{smartlink ititle="All packages" group_id=$groupInfo.group_id}
									{foreach from=$gBitSystem->mPackages key=packageKey item=packageItem}
										{if $packageItem.installed} 
											&nbsp;&bull; {smartlink ititle=$gBitSystem->mPackages.$packageKey.name group_id=$groupInfo.group_id package=$packageKey}
										{/if}
									{/foreach}
								{/forminput}
							</div>

							<div class="control-group">
								<table class="data">
									<tr>
										<th>&nbsp;</th>
										<th>{smartlink ititle="Name" isort=perm_name group_id=$groupInfo.group_id offset=$offset package=$package}</th>
										<th>{smartlink ititle="Package" isort=package group_id=$groupInfo.group_id offset=$offset package=$package}</th>
										<th>{smartlink ititle="Description" isort=perm_desc group_id=$groupInfo.group_id offset=$offset package=$package}</th>
										<th>&nbsp;</th>
									</tr>
									{foreach key=permName item=perm from=$allPerms}
										{if $package eq $perm.package or $package eq 'all'}
											<tr class="{cycle values="even,odd"}">
												<td><input type="checkbox" id="{$permName}" name="perm[{$permName}]"{if $groupInfo.perms.$permName} checked="checked"{/if} /></td>
												<td><label for="{$permName}">{$permName}</label></td>
												<td>{tr}{$perm.package}{/tr}</td>
												<td>{tr}{$perm.perm_desc}{/tr}</td>
											</tr>
										{/if}
									{/foreach}
								</table>
							</div>

							<div class="control-group submit">
								<input type="submit" name="updateperms" value="{tr}Update{/tr}" />
							</div>
						{/form}
					{/jstab}
				{/if}
			{/if}
		{/jstabs}
	</div><!-- end .body -->
</div><!-- end .users -->

{/strip}
