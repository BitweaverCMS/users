{* $Header: /cvsroot/bitweaver/_bit_users/templates/admin_group_edit.tpl,v 1.1.1.1.2.2 2006/01/05 00:06:07 squareing Exp $ *}
{strip}

<div class="floaticon">
	<a href="{$smarty.const.USERS_PKG_URL}admin/edit_group.php">{tr}&laquo; Group List{/tr}</a> 
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
					<div class="row">
						{formlabel label="Group" for="groups_group"}
						{forminput}
							<input type="text" name="name" id="groups_group" size="30" maxlength="30" value="{$groupInfo.group_name}" />
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Description" for="group_desc"}
						{forminput}
							<textarea rows="5" cols="20" name="desc" id="group_desc">{$groupInfo.group_desc}</textarea>
						{/forminput}
					</div>

					<div class="row">
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

					<div class="row">
						{formlabel label="Group home page" for="groups_home"}
						{forminput}
							{html_options name="dummy" options=$contentList selected=`$groupInfo.group_home` onchange="document.getElementById('groups_home').value=options[selectedIndex].value;"}
							<br />
							<input type="text" name="home" id="groups_home" value="{$groupInfo.group_home|escape}" />
							{formhelp note="Here you can enter the content id of any page, the wiki page name or the absolute path of any page you wish to use as a group home page"}
						{/forminput}
					</div>

					<div class="row">
					{formlabel label="Default home page" for="default_home"}
						{forminput}
							<input type="checkbox" id="default_home" name="default_home_group" {if $groupInfo.group_id eq $defaultGroupId}checked="checked"{/if} value="y" />
							{formhelp note="This is the home page if a user belongs to many groups. Only one group may be the default home. If none is selected, users/my.php is the default."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Auto members" for="is_default"}
						{forminput}
							<input type="checkbox" name="is_default" value="y" {if $groupInfo.is_default eq 'y'}checked="checked"{/if} id="is_default" />
							{formhelp note="Users are automatically added to this group when registering at your site."}
						{/forminput}
					</div>

					<div class="row submit">
						<input type="submit" name="cancel" value="{tr}Cancel{/tr}" />&nbsp;
						<input type="submit" name="save" value="{tr}Save Group{/tr}" />
					</div>
				{/form}
			{/jstab}

			{if $groupInfo.group_id}
				{*jstab title="Current Permissions"}
					{form legend="Permissions currently assigned to this group"}
						<table class="data">
							<tr>
								<th>{tr}Permission{/tr}</th>
								<th>{tr}Description{/tr}</th>
							</tr>
							{foreach from=$groupInfo.perms key=permName item=perm}
							<tr class="{cycle values="odd,even"}">
								<td>
									{smartlink ititle="Remove" ibiticon="liberty/delete_small" package=$package group_id=$groupInfo.group_id action=remove permission=$permName}
									&nbsp;{$permName}
								</td>
								<td>{$perm.perm_desc}</td>
							</tr>
							{/foreach}
						</table>
					{/form}
				{/jstab*}

				{jstab title="Assign Permissions"}
					{form legend="Assign permissions and / or set level"}
						<input type="hidden" name="group_id" value="{$groupInfo.group_id}" />
						<input type="hidden" name="package" value="{$package|escape}" />
						<input type="hidden" name="tab" value="assign" />
						<input type="hidden" name="perm_name[{$perms[user].perm_name}]" />

						<div class="row">
							{formlabel label="Display permissions of package"}
							{forminput}
								{smartlink ititle="All packages" group_id=$groupInfo.group_id}
								{foreach from=$gBitSystem->mPackages key=packageKey item=packageItem}
									{if $packageItem.installed and $packageItem.defaults} 
										&nbsp;&bull; {smartlink ititle=$gBitSystem->mPackages.$packageKey.name group_id=$groupInfo.group_id package=$packageKey}
									{/if}
								{/foreach}
							{/forminput}
						</div>

						<div class="row">
							<table class="data">
								<tr>
									<th>&nbsp;</th>
									<th><abbr title="{tr}Inherited permissions{/tr}">*</abbr></th>
									<th>{smartlink ititle="Name" isort="up.perm_name" group_id=$groupInfo.group_id offset=$offset package=$package}</th>
									<th>{tr}Level{/tr}</th>
									<th>{smartlink ititle="Package" isort=package group_id=$groupInfo.group_id offset=$offset package=$package}</th>
									<th>{smartlink ititle="Description" isort="up.perm_desc" group_id=$groupInfo.group_id offset=$offset package=$package}</th>
								</tr>
								{foreach key=permName item=perm from=$allPerms}
									{if $package eq $perm.package or $package eq 'all'}
										<tr class="{cycle values="even,odd"}">
											<td><input type="checkbox" id="{$permName}" name="perm[{$permName}]" {if $groupInfo.perms.$permName} checked="checked"{/if} /></td>
											<td>
												{if $incPerms.$permName}
													<input type="checkbox" id="{$permName}" name="inherited[{$permName}]" checked="checked" disabled="disabled" title="{tr}Inherited from{/tr}: {$incPerms.$permName.group_name}" />
												{/if}
											</td>
											<td><label for="{$permName}">{$permName}</label></td>
											<td>{html_options name="level[$permName]" output=$levels values=$levels selected=$perm.level}</td>
											<td>{tr}{$perm.package}{/tr}</td>
											<td>{tr}{$perm.perm_desc}{/tr}</td>
										</tr>
									{/if}
								{/foreach}
							</table>
							* {formhelp note="Inherited permissions. Hover over the checkboxes to find out what group they are inherited from. Assigning them to a new level will remove them from the original group and insert them here."}
						</div>

						<div class="row submit">
							<input type="submit" name="updateperms" value="{tr}Update{/tr}" />
						</div>
					{/form}
				{/jstab}

				{jstab title="Advanced"}
					{form legend="Batch assign permissions"}
						<input type="hidden" name="group_id" value="{$groupInfo.group_id}" />
						<input type="hidden" name="package" value="{$package|escape}" />

						<div class="row">
							{formlabel label="Assign or Remove" for="oper"}
							{forminput}
								<select name="oper" id="oper">
									<option value="assign">{tr}Assign{/tr}</option>
									<option value="remove">{tr}Remove{/tr}</option>
								</select>
								<br />
								{tr}all permissions in level{/tr} 
								<br />
								<select name="level">
									{html_options output=$levels values=$levels selected=$perms[user].level}
								</select> 
								<br />
								{tr}to / from {$groupInfo.group_name}{/tr}
							{/forminput}
						</div>

						<div class="row submit">
							<input type="submit" name="allper" value="{tr}Update{/tr}" />
						</div>
					{/form}

					{form legend="Create a new level"}
						<input type="hidden" name="group_id" value="{$groupInfo.group_id}" />
						<input type="hidden" name="package" value="{$package|escape}" />
						<div class="row">
							{formlabel label="Level" for="level"}
							{forminput}
								<input type="text" name="level" id="level" />
								{formhelp note="Levels can be used to group certain permissions and thus easily assign a set of permissions to a group. Assinging a permission to a level has no outcome on the users or groups. It's merely a way to organise permissions."}
							{/forminput}
						</div>

						<div class="row submit">
							<input type="submit" name="createlevel" value="{tr}Create{/tr}" />
						</div>
					{/form}
				{/jstab}
			{/if}
		{/jstabs}
	</div><!-- end .body -->
</div><!-- end .users -->

{/strip}
