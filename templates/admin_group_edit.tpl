{* $Header: /cvsroot/bitweaver/_bit_users/templates/admin_group_edit.tpl,v 1.11 2006/09/03 20:14:58 squareing Exp $ *}
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
							{foreach from=$groupList key=groupId item=group}
								{if $groupId != $groupInfo.group_id}
									<option value="{$groupId}" {if $group.included eq 'y'} selected="selected"{/if}>{$group.group_name}</option>
								{/if}
							{/foreach}
							</select>
							{formhelp note="If you include a group, this group will inherit all permissions of the included group."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Group home page" for="group_home"}
						{forminput}
							<input type="text" name="home" id="group_home" value="{$groupInfo.group_home|escape}" />
							{formhelp note="Here you can enter the content id of any page, the wiki page name or the absolute path of any page you wish to use as a group home page. For this to work set the site homepage to <strong>Group Home</strong>" link="kernel/admin/index.php?page=general/General Settings"}

							Search for Content:<br/>
							{html_options options=$contentTypes name=content_type_guid selected=$contentSelect}
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/libs/suggest/suggest.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/libs/rico.js"></script>
<script type="text/javascript">
{literal}   
      var suggestOptions = { 
        matchAnywhere      : true,
        ignoreCase         : true
      };

      function injectSuggestBehavior() {                          
         suggest = new TextSuggest( 
			'group_home_lookup', 
			'/liberty/list_content.php', 
			suggestOptions
	 );
      } 
{/literal}	  
</script>
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
									{smartlink ititle="Remove" ibiticon="icons/edit-delete" package=$package group_id=$groupInfo.group_id action=remove permission=$permName}
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
								{foreach from=$permPackages key=i item=packageKey}
									&nbsp;&bull; {smartlink ititle=$gBitSystem->mPackages.$packageKey.name group_id=$groupInfo.group_id package=$packageKey}
								{/foreach}
							{/forminput}
						</div>

						<div class="row">
							<table class="data">
								<tr>
									<th>&nbsp;</th>
									{if $incPerms}
										<th><abbr title="{tr}Inherited permissions{/tr}">*</abbr></th>
									{/if}
									<th>{smartlink ititle="Name" isort="up.perm_name" group_id=$groupInfo.group_id offset=$offset package=$package}</th>
									<th>{tr}Level{/tr}</th>
									<th>{smartlink ititle="Package" isort=package group_id=$groupInfo.group_id offset=$offset package=$package}</th>
									<th>{smartlink ititle="Description" isort="up.perm_desc" group_id=$groupInfo.group_id offset=$offset package=$package}</th>
								</tr>
								{foreach key=permName item=perm from=$allPerms}
									{if $package eq $perm.package or $package eq 'all'}
										<tr class="{cycle values="even,odd"}">
											<td><input type="checkbox" id="{$permName}" name="perm[{$permName}]" {if $groupInfo.perms.$permName} checked="checked"{/if} /></td>
											{if $incPerms}
												<td>
													{if $incPerms.$permName}
														<input type="checkbox" id="{$permName}" name="inherited[{$permName}]" checked="checked" disabled="disabled" title="{tr}Inherited from{/tr}: {$incPerms.$permName.group_name}" />
													{/if}
												</td>
											{/if}
											<td><label for="{$permName}">{$permName}</label></td>
											<td>{html_options name="perm_level[$permName]" output=$levels values=$levels selected=$perm.perm_level}</td>
											<td>{tr}{$perm.package}{/tr}</td>
											<td>{tr}{$perm.perm_desc}{/tr}</td>
										</tr>
									{/if}
								{/foreach}
							</table>
							{if $incPerms}
								* {formhelp note="Inherited permissions. Hover over the checkboxes to find out what group they are inherited from. Assigning them to a new level will remove them from the original group and insert them here."}
							{/if}
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
								<select name="perm_level">
									{html_options output=$levels values=$levels selected=$perms[user].perm_level}
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
								<input type="text" name="perm_level" id="level" />
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
