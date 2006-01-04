{* $Header: /cvsroot/bitweaver/_bit_users/templates/Attic/admin_unassigned_perms.tpl,v 1.1.2.1 2006/01/04 14:51:10 squareing Exp $ *}
{strip}

<div class="admin groups">
	<div class="header">
		<h1>{tr}Unassigned Permissions{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback hash=$feedback}

		{form legend="Assign Permissions"}
			<table class="data">
				<caption>{tr}Unassigned Permissions{/tr}</caption>
				<tr>
					<th>{tr}Permission{/tr}</th>
					<th>{tr}Package{/tr}</th>
					<th>{tr}Default Level{/tr}</th>
					<th style="width:1px;">{tr}Group{/tr}</th>
				</tr>
				{foreach from=$unassignedPerms item=perm}
					<tr class="{cycle values="odd,even"}">
						<td><strong>{$perm.perm_name}</strong><br />{$perm.perm_desc}</td>
						<td>{$perm.package}</td>
						<td>{$perm.level}</td>
						<td>{html_options name="assign[`$perm.perm_name`]" options=$groupDrop values=$groupDrop selected=$perm.suggestion}</td>
					</tr>
				{foreachelse}
					<tr class="norecords">
						<td colspan="4">{tr}All Permissions seem to be assigned to groups.{/tr}</td>
					</tr>
				{/foreach}
			</table>

			<div class="row submit">
				<input type="submit" name="assign_permissions" value="{tr}Assign Permissions{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .users -->
{/strip}
