{strip}
<div class="admin users">
	<div class="header">
		<h1>{tr}Assign Group Permissions{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback hash=$feedback}

		{form}
			<input type="hidden" name="package" value="{$smarty.request.package}" />

			<p>
				{smartlink ititle=All package=$packageKey}
				{foreach from=$permPackages key=i item=packageKey}
					{if $gBitSystem->isPackageActive($packageKey)}
						&nbsp;&bull; {smartlink ititle=$gBitSystem->mPackages.$packageKey.name|default:$packageKey package=$packageKey}
					{/if}
				{/foreach}
			</p>

			<table class="table data">
				<caption>{tr}Available Permissions{/tr}</caption>
				{foreach from=$allPerms item=perm key=p name=perms}
					{if $prev_package != $perm.package}
						<tr>
							<th class="width1p"></th>
							<th>{tr}Permission{/tr} - {$perm.package}</th>
							{foreach from=$allGroups item=group name=groups}
								<th class="width10p"{if $group.group_id lt 4} colspan="2"{/if}>
									<abbr title="{$group.group_name}">{if $smarty.foreach.groups.total > 8}{$group.group_id}{else}{$group.group_name}{/if}</abbr>
								</th>
							{/foreach}
						</tr>
						{assign var=prev_package value=$perm.package}
					{/if}
					<tr class="{cycle values="odd,even"}{if $unassignedPerms.$p} prio5{/if}">
						<td>{if $unassignedPerms.$p}{booticon iname="icon-warning-sign"   iexplain="Unassigned Permission"}{/if}</td>
						<td title="{$perm.perm_desc}"><abbr title="{$perm.perm_desc}">{$p}</abbr></td>
						{foreach from=$allGroups item=group}
							{if     $perm.perm_level == 'admin'     }{assign var=id value=1}
							{elseif $perm.perm_level == 'editors'   }{assign var=id value=2}
							{elseif $perm.perm_level == 'registered'}{assign var=id value=3}
							{elseif $perm.perm_level == 'basic'     }{assign var=id value=-1}{/if}

							{if $id == $group.group_id and !$group.perms.$p}
								{assign var=class value="prio5"}
							{elseif $id == $group.group_id and $group.perms.$p}
								{assign var=class value="prio1"}
							{elseif $id != $group.group_id and $group.perms.$p}
								{assign var=class value="prio5"}
							{else}
								{assign var=class value=""}
							{/if}

							<td class="{if $group.group_id lt 4}alignright{else}aligncenter{/if} {$class}">
								<input id="{$p}{$group.group_id}" type="checkbox" value="{$p}" name="perms[{$group.group_id}][{$p}]" title="{$group.group_name}" {if $group.perms.$p}checked="checked"{/if}/>
							</td>

							{if $group.group_id lt 4}
								<td class="alignleft {$class} width5p">
									{if $id == $group.group_id}<label for="{$p}{$group.group_id}">{booticon iname="icon-ok"   iexplain="Default"}</label>{/if}
								</td>
							{/if}
						{/foreach}
					</tr>
				{/foreach}
			</table>

			<p class="formhelp">{tr}Default permissions set after installation are marked with:{/tr} {booticon iname="icon-ok"   iexplain="Default"}</p>

			<div class="submit">
				<input type="submit" class="btn" name="save" value="{tr}Apply Changes{/tr}" />
			</div>
		{/form}

		{if $smarty.foreach.groups.total > 8}
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
					<li><em>{$gLibertySystem->getContentTypeName($content_type_guid)}</em>
						<ul>
							{foreach from=$content item=perms key=content_id}
								<li><a href="{$smarty.const.LIBERTY_PKG_URL}content_permissions.php?content_id={$content_id}">{$perms.0.title}</a>
									<ul>
										{foreach from=$perms item=perm}
											<li>
												{$perm.group_name}: {if $perm.is_revoked}
													{booticon iname="icon-minus-sign"   iexplain="Removed Permission"}
												{else}
													{booticon iname="icon-plus-sign"   iexplain="Added Permission"}
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
