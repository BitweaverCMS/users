{* $Header$ *}
{strip}

<div class="floaticon">
	<a href="{$smarty.const.USERS_PKG_URL}admin/edit_role.php">
		{biticon ipackage="icons" iname="system-users" iexplain="Role List"}
	</a>
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
					<div class="row">
						{formlabel label="Role" for="roles_role"}
						{forminput}
							<input type="text" name="name" id="roles_role" size="30" maxlength="30" value="{$roleInfo.role_name}" />
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Description" for="role_desc"}
						{forminput}
							<textarea rows="5" cols="20" name="desc" id="role_desc">{$roleInfo.role_desc}</textarea>
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Role home page" for="role_home"}
						{forminput}
							<input type="text" name="home" id="role_home" value="{$roleInfo.role_home|escape}" />
							{formhelp note="Here you can enter the content id of any page, the wiki page name or the absolute path of any page you wish to use as a role home page. For this to work set the site homepage to <strong>Role Home</strong>" link="kernel/admin/index.php?page=features/General Settings"}

							Search for Content:<br/>
							{html_options options=$contentTypes name=content_type_guid selected=$contentSelect}
							<input type="hidden" name="role_home_lookup_hidden" id="role_home_lookup_hidden" value="{$roleInfo.role_home|escape}" />
							<input type="text" id="role_home_lookup" name="role_home_name">
						{formhelp note="Enter the title of the content you are looking for to receive an auto-suggest list of possibilities."}
{*
							{html_options name="dummy" id="content-list" values=$contentList options=$contentList onchange="document.getElementById('role_home').value=options[selectedIndex].value;"}
							<input type="text" size="30" name="find" value="{$smarty.request.find}" />
							<input type="submit" value="{tr}Apply filter{/tr}" name="search_objects" />
							<br />
*}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="After registration page" for="after_registration_page"}
						{forminput}
							<input type="text" name="after_registration_page" id="after_registration_page" value="{$roleInfo.after_registration_page|escape}" />
							{formhelp note="The same format than the Role home page. Used to redirect a user after his registration if other that the default after login page."}
						{/forminput}
					</div>

					<div class="row">
					{formlabel label="Default home page" for="default_home"}
						{forminput}
							<input type="checkbox" id="default_home" name="default_home_role" {if $roleInfo.role_id eq $defaultRoleId}checked="checked"{/if} value="y" />
							{formhelp note="This is the home page if a user belongs to many roles. Only one role may be the default home. If none is selected, users/my.php is the default."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Auto members" for="is_default"}
						{forminput}
							<input type="checkbox" name="is_default" value="y" {if $roleInfo.is_default eq 'y'}checked="checked"{/if} id="is_default" />
							{formhelp note="Users are automatically added to this role when registering at your site."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Is public" for="is_public"}
						{forminput}
							<input type="checkbox" name="is_public" value="y" {if $roleInfo.is_public eq 'y'}checked="checked"{/if} id="is_public" />
							{formhelp note="A user will be able to select this role at registration."}
						{/forminput}
					</div>

					<div class="row submit">
						<input type="submit" name="cancel" value="{tr}Cancel{/tr}" />&nbsp;
						<input type="submit" name="save" value="{tr}Save Role{/tr}" />
					</div>
				{/form}
			{/jstab}

			{if $roleInfo.role_id}
				{jstab title="Assign Permissions"}
					{form legend="Assign permissions"}
						<input type="hidden" name="role_id" value="{$roleInfo.role_id}" />
						<input type="hidden" name="package" value="{$smarty.request.package|escape}" />
						<input type="hidden" name="perm_name[{$perms[user].perm_name}]" />

						<div class="row">
							{formlabel label="Display permissions of package"}
							{forminput}
								{smartlink ititle="All packages" role_id=$roleInfo.role_id}
								{foreach from=$permPackages key=i item=packageKey}
									{if $gBitSystem->isPackageActive($packageKey)}
										&nbsp;&bull; {smartlink ititle=$gBitSystem->mPackages.$packageKey.name|default:$packageKey role_id=$roleInfo.role_id package=$packageKey}
									{/if}
								{/foreach}
							{/forminput}
						</div>

						<div class="row">
							<table class="data">
								<tr>
									<th>&nbsp;</th>
									<th>{smartlink ititle="Name" isort="up.perm_name" role_id=$roleInfo.role_id offset=$offset package=$smarty.request.package}</th>
									<th>{smartlink ititle="Package" isort=package role_id=$roleInfo.role_id offset=$offset package=$smarty.request.package}</th>
									<th>{smartlink ititle="User Class" isort=perm_level role_id=$roleInfo.level offset=$offset package=$smarty.request.level}</th>
									<th>{smartlink ititle="Description" isort="up.perm_desc" role_id=$roleInfo.role_id offset=$offset package=$smarty.request.package}</th>
								</tr>
								{foreach key=permName item=perm from=$allPerms}
									<tr class="{cycle values="even,odd"}">
										<td><input type="checkbox" id="{$permName}" name="perm[{$permName}]" {if $roleInfo.perms.$permName} checked="checked"{/if} /></td>
										<td><label for="{$permName}">{$permName}</label></td>
										<td>{tr}{$perm.package}{/tr}</td>
										<td>{tr}{$perm.perm_level}{/tr}</td>
										<td>{tr}{$perm.perm_desc}{/tr}</td>
									</tr>
								{/foreach}
							</table>
						</div>

						<div class="row submit">
							<input type="submit" name="updateperms" value="{tr}Update{/tr}" />
						</div>
					{/form}
				{/jstab}
			{/if}
		{/jstabs}
	</div><!-- end .body -->
</div><!-- end .users -->

{/strip}
