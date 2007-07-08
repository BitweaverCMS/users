{strip}
<div class="admin users">
	<div class="header">
		<h1>{tr}Assign Group Permissions{/tr}</h1>
	</div>

	<div class="body">
		<p class="help">{tr}Hover your mouse over the permissions and a descirption of the permission will appear{/tr}</p>
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
				{capture assign=th}
					<tr>
						<th style="width:1%"></th>
						<th>{tr}Permission{/tr}</th>
						<th>{tr}Package{/tr}</th>
						{foreach from=$allGroups item=group name=groups}
							<th><abbr title="{$group.group_name}">{if $smarty.foreach.groups.total > 5}{$group.group_id}{else}{$group.group_name}{/if}</abbr></th>
						{/foreach}
					</tr>
				{/capture}
				{$th}
				{foreach from=$allPerms item=perm key=p name=perms}
					{* insert headers every 20 lines *}
					{if ($smarty.foreach.perms.iteration % 20) eq 0 and ($smarty.foreach.perms.total - $smarty.foreach.perms.iteration) gt 15}{$th}{/if}
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

		{if $smarty.foreach.groups.total > 5}
			<dl>
				{foreach from=$allGroups item=group}
					<dt>{$group.group_id}</dt>
					<dd>{$group.group_name}: {$group.group_desc}</dd>
				{/foreach}
			</dl>
		{/if}

		{if $contentWithPermissions}
			<h2>{tr}Content with individual Permissions{/tr}</h2>
			<ul>
				{foreach from=$contentWithPermissions item=content key=content_type_guid}
					<li><em>{$gLibertySystem->getContentTypeDescription($content_type_guid)}</em>
						<ul>
							{foreach from=$content item=perms key=content_id}
								<li><a href="{$smarty.const.LIBERTY_PKG_URL}content_permissions.php?content_id={$content_id}">{$perms.0.title}</a>
									<ul>
										{foreach from=$perms item=perm}
											<li>
												{$perm.group_name}: {if $perm.is_excluded}
													{biticon iname=list-remove iexplain="Removed Permission"}
												{else}
													{biticon iname=list-add iexplain="Added Permission"}
												{/if} {$perm.perm_name}
											</li>
										{/foreach}
									</ul>
								</li>
							{/foreach}
						</ul>
					</li>
				{/foreach}
			</ul>
		{/if}
	</div><!-- end .body -->
</div><!-- end .users -->
{/strip}
