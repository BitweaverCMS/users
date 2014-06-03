{strip}
<div class="listing users">
	<div class="header">
		<h1>{tr}List of existing roles{/tr}</h1>
	</div>

	<div class="body">
		{smartlink ititle="Add a new role" ipackage=users ifile="admin/edit_role.php" action=create}

		<ul class="list-inline navbar">
			<li>{booticon iname="icon-circle-arrow-right"  ipackage="icons"  iexplain="sort by"}</li>
			<li>{smartlink ititle="Name" isort="role_name" offset=$offset idefault=1}</li>
			<li>{smartlink ititle="Description" isort="role_desc" offset=$offset}</li>
			<li>{smartlink ititle="Home Page" isort="role_home" offset=$offset}</li>
		</ul>

		{formfeedback success=$successMsg error=$errorMsg}

		<ul class="clear data">
			{foreach from=$roleList key=roleId item=grp}
				<li class="item {cycle values='odd,even'}">
					<div class="floaticon">
						{smartlink ititle="Edit" ipackage="users" ifile="admin/edit_role.php" booticon="icon-edit" role_id=$roleId}
						{smartlink ititle="Role Members" ipackage="users" ifile="admin/edit_role.php" booticon="icon-group" members=$roleId}
						{if $roleId ne $smarty.const.ANONYMOUS_TEAM_ID}
							{smartlink ititle="Batch assign" ipackage="users" ifile="admin/edit_role.php" booticon="icon-cogs" batch_assign=$roleId}
							{smartlink ititle="Remove" ipackage="users" ifile="admin/edit_role.php" booticon="icon-trash" action=delete role_id=$roleId}
						{/if}
					</div>

					<h2>{$grp.role_name}</h2>
					<div style="float:left;width:30%;">
						{$grp.role_desc}<br />
						{if $grp.is_default eq 'y'}<small class="warning">*{tr}Default role{/tr}*</small><br/>{/if}
						{if $grp.role_home}{tr}Home Page{/tr}:<strong> {$grp.role_home}</strong><br />{/if}
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
