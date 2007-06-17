{strip}
<div class="admin users">
	<div class="header">
		<h1>{tr}Assign Group Permissions{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback hash=$feedback}

			{form}
				<input type="hidden" name="package" value="{$smarty.request.package}" />

				{smartlink ititle=All package=$packageKey}
				{foreach from=$permPackages key=i item=packageKey}
					{if $gBitSystem->isPackageActive($packageKey)}
						&nbsp;&bull; {smartlink ititle=$gBitSystem->mPackages.$packageKey.name|default:$packageKey package=$packageKey}
					{/if}
				{/foreach}

				<table class="data">
					<caption>{tr}Available Permissions{/tr}</caption>
					<tr>
						<th style="width:1%"></th>
						<th>{tr}Permission{/tr}</th>
						<th>{tr}Package{/tr}</th>
						{foreach from=$allGroups item=group}
							<th><abbr title="{$group.group_name}">{$group.group_id}</abbr></th>
						{/foreach}
					</tr>
					{foreach from=$allPerms item=perm key=p}
					<tr class="{cycle values="odd,even"}{if $unassignedPerms.$p} warning{/if}">
						<td>{if $unassignedPerms.$p}{biticon iname=dialog-warning iexplian="Unassigned Permission"}{/if}</td>
						<td><span title="{$perm.perm_desc}">{$p}</span></td>
						<td>{$perm.package}</td>
							{foreach from=$allGroups item=group}
								<td style="text-align:center;">
									<input type="checkbox" value="{$p}" name="perms[{$group.group_id}][{$p}]" title="{$group.group_name}" {if $group.perms.$p}checked="checked"{/if}/>
								</td>
							{/foreach}
						</tr>
					{/foreach}
				</table>

				<div class="submit">
					<input type="submit" name="save" value="{tr}Apply Changes{/tr}" />
				</div>
			{/form}

			<dl>
				{foreach from=$allGroups item=group}
					<dt>{$group.group_id}</dt>
					<dd>{$group.group_name}: {$group.group_desc}</dd>
				{/foreach}
			</dl>

	</div><!-- end .body -->
</div><!-- end .users -->
{/strip}
