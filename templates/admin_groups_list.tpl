{strip}
<div class="listing users">
	<div class="header">
		<a class="btn btn-primary btn-mini pull-right" href="{$smarty.const.USERS_PKG_URL}admin/edit_group.php?action=create"><i class="icon-group"></i> {tr}Add a new group{/tr}</a>
		<h1>{tr}List of existing groups{/tr}</h1>
	</div>

	<div class="body">

		{formfeedback success=$successMsg error=$errorMsg}

		<ul class="inline navbar">
			<li>{booticon iname="icon-circle-arrow-right"  ipackage="icons"  iexplain="sort by"}</li>
			<li>{smartlink ititle="Name" isort="group_name" offset=$offset idefault=1}</li>
			<li>{smartlink ititle="Description" isort="group_desc" offset=$offset}</li>
			<li>{smartlink ititle="Home Page" isort="group_home" offset=$offset}</li>
		</ul>

		<ul class="clear data">
			{foreach from=$groupList key=groupId item=grp}
				<li class="item {cycle values='odd,even'}">
					<div class="floaticon">
						{smartlink ititle="Edit" ipackage="users" ifile="admin/edit_group.php" booticon="icon-edit" group_id=$groupId}
						{smartlink ititle="Group Members" ipackage="users" ifile="admin/edit_group.php" booticon="icon-group" members=$groupId}
						{if $groupId ne $smarty.const.ANONYMOUS_GROUP_ID}
							{smartlink ititle="Batch assign" ipackage="users" ifile="admin/edit_group.php" booticon="icon-cogs" batch_assign=$groupId}
						{/if}
					</div>

					<h2>{$grp.group_name}</h2>
					<div style="float:left;width:30%;">
						{$grp.group_desc}<br />
						{if $grp.is_default eq 'y'}<small class="warning">*{tr}Default group{/tr}*</small><br/>{/if}
						{if $grp.group_home}{tr}Home Page{/tr}:<strong> {$grp.group_home}</strong><br />{/if}
					</div>

					<div style="float:right;width:70%;">
						{tr}Permissions{/tr}
						<ul class="small">
							{foreach from=$grp.perms key=permName item=perm}
								<li>{$perm.perm_desc}</li>
							{foreachelse}
								<li>{tr}none{/tr}</li>
							{/foreach}
						</ul>
					</div>
					<div class="clear"></div>
				</li>
			{/foreach}
		</ul>
		{pagination}
	</div><!-- end .body -->
</div><!-- end .users -->
{/strip}
