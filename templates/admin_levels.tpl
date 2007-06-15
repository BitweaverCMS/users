{strip}
<div class="admin groups">
	<div class="header">
		<h1>{tr}Edit Permission Levels{/tr}</h1>
	</div>

	<div class="body">
		<p class="help">{tr}Levels can be used to group certain permissions and thus easily assign a set of permissions to a group. Assinging a permission to a level has no outcome on the users or groups. It's merely a way to organise permissions.{/tr}</p>

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
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" name="createlevel" value="{tr}Create{/tr}" />
			</div>
		{/form}

		{form legend="Assign levels"}
			<table class="data">
				<tr>
					<th>{smartlink ititle="Name" isort="up.perm_name" group_id=$groupInfo.group_id offset=$offset package=$package}</th>
					<th>{tr}Level{/tr}</th>
					<th>{smartlink ititle="Package" isort=package group_id=$groupInfo.group_id offset=$offset package=$package}</th>
					<th>{smartlink ititle="Description" isort="up.perm_desc" group_id=$groupInfo.group_id offset=$offset package=$package}</th>
				</tr>
				{foreach key=permName item=perm from=$allPerms}
					<tr class="{cycle values="even,odd"}">
						<td><label for="{$permName}">{$permName}</label></td>
						<td>{html_options name="perm_level[$permName]" output=$levels values=$levels selected=$perm.perm_level}</td>
						<td>{tr}{$perm.package}{/tr}</td>
						<td>{tr}{$perm.perm_desc}{/tr}</td>
					</tr>
				{/foreach}
			</table>

			<div class="row submit">
				<input type="submit" name="updatelevels" value="{tr}Update{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .users -->

{/strip}
