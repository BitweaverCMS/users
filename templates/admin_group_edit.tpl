{* $Header: /cvsroot/bitweaver/_bit_users/templates/admin_group_edit.tpl,v 1.18 2007/07/12 07:51:11 squareing Exp $ *}
{strip}

<div class="floaticon">
	<a href="{$smarty.const.USERS_PKG_URL}admin/edit_group.php">
		{biticon ipackage="icons" iname="system-users" iexplain="Group List"}
	</a>
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
						{formlabel label="Group home page" for="group_home"}
						{forminput}
							<input type="text" name="home" id="group_home" value="{$groupInfo.group_home|escape}" />
							{formhelp note="Here you can enter the content id of any page, the wiki page name or the absolute path of any page you wish to use as a group home page. For this to work set the site homepage to <strong>Group Home</strong>" link="kernel/admin/index.php?page=features/General Settings"}

							Search for Content:<br/>
							{html_options options=$contentTypes name=content_type_guid selected=$contentSelect}
							{literal}<script type="text/javascript">/* <![CDATA[ */
								var suggestOptions = {
									matchAnywhere: true,
									ignoreCase: true
								};
								function injectSuggestBehavior() {
									suggest = new TextSuggest(
										'group_home_lookup',
										'/liberty/list_content.php',
										suggestOptions
									);
								}
							/* ]]> */</script>{/literal}
							<input type="hidden" name="group_home_lookup_hidden" id="group_home_lookup_hidden" value="{$groupInfo.group_home|escape}" />
							<input type="text" id="group_home_lookup" name="group_home_name">
						{formhelp note="Enter the title of the content you are looking for to receive an auto-suggest list of possibilities."}
{*
							{html_options name="dummy" id="content-list" values=$contentList options=$contentList onchange="$('group_home').value=options[selectedIndex].value;"}
							<input type="text" size="30" name="find_objects" value="{$smarty.request.find_objects}" />
							<input type="submit" value="{tr}Apply filter{/tr}" name="search_objects" />
							<br />
*}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="After registration page" for="after_registration_page"}
						{forminput}
							<input type="text" name="after_registration_page" id="after_registration_page" value="{$groupInfo.after_registration_page|escape}" />
							{formhelp note="The same format than the Group home page. Used to redirect a user after his registration if other that the default after login page."}
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

					<div class="row">
						{formlabel label="Is public" for="is_public"}
						{forminput}
							<input type="checkbox" name="is_public" value="y" {if $groupInfo.is_public eq 'y'}checked="checked"{/if} id="is_public" />
							{formhelp note="A user will be able to select this group at registration."}
						{/forminput}
					</div>

					<div class="row submit">
						<input type="submit" name="cancel" value="{tr}Cancel{/tr}" />&nbsp;
						<input type="submit" name="save" value="{tr}Save Group{/tr}" />
					</div>
				{/form}
			{/jstab}

			{if $groupInfo.group_id}
				{jstab title="Assign Permissions"}
					{form legend="Assign permissions"}
						<input type="hidden" name="group_id" value="{$groupInfo.group_id}" />
						<input type="hidden" name="package" value="{$package|escape}" />
						<input type="hidden" name="perm_name[{$perms[user].perm_name}]" />

						<div class="row">
							{formlabel label="Display permissions of package"}
							{forminput}
								{smartlink ititle="All packages" group_id=$groupInfo.group_id}
								{foreach from=$permPackages key=i item=packageKey}
									{if $gBitSystem->isPackageActive($packageKey)}
										&nbsp;&bull; {smartlink ititle=$gBitSystem->mPackages.$packageKey.name|default:$packageKey group_id=$groupInfo.group_id package=$packageKey}
									{/if}
								{/foreach}
							{/forminput}
						</div>

						<div class="row">
							<table class="data">
								<tr>
									<th>&nbsp;</th>
									<th>{smartlink ititle="Name" isort="up.perm_name" group_id=$groupInfo.group_id offset=$offset package=$package}</th>
									<th>{smartlink ititle="Package" isort=package group_id=$groupInfo.group_id offset=$offset package=$package}</th>
									<th>{smartlink ititle="Description" isort="up.perm_desc" group_id=$groupInfo.group_id offset=$offset package=$package}</th>
								</tr>
								{foreach key=permName item=perm from=$allPerms}
									<tr class="{cycle values="even,odd"}">
										<td><input type="checkbox" id="{$permName}" name="perm[{$permName}]" {if $groupInfo.perms.$permName} checked="checked"{/if} /></td>
										<td><label for="{$permName}">{$permName}</label></td>
										<td>{tr}{$perm.package}{/tr}</td>
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
