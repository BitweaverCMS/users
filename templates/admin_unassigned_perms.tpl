{* $Header: /cvsroot/bitweaver/_bit_users/templates/Attic/admin_unassigned_perms.tpl,v 1.1.2.3 2006/01/04 15:34:25 squareing Exp $ *}
{strip}

<div class="admin groups">
	<div class="header">
		<h1>{tr}Unassigned Permissions{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback hash=$feedback}
		{if $assignedPerms}
			<ul>
				{foreach from=$assignedPerms key=perm item=group}
					<li>{$perm} --&gt; {$group}</li>
				{/foreach}
			</ul>
		{/if}

		{if $unassignedPerms}
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
					{/foreach}
				</table>

				<div class="row submit">
					<input type="submit" name="assign_permissions" value="{tr}Assign Permissions{/tr}" />
				</div>
			{/form}
		{else}
			{formfeedback success="{tr}No unassigned permissions{/tr}"}
		{/if}
	</div><!-- end .body -->
</div><!-- end .users -->
{/strip}
