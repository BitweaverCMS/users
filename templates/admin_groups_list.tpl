{strip}
<div class="listing users">
	<div class="header">
		<h1>{tr}List of existing groups{/tr}</h1>
	</div>

	<div class="body">
		{smartlink ititle="Add a new group" ipackage=users ifile="admin/edit_group.php" action=create}
		<br />
		{smartlink ititle="Check for Unassigned Permissions" ipackage=users ifile="admin/unassigned_perms.php"}

		<div class="navbar">
			<ul>
				<li>{biticon ipackage="icons" iname="emblem-symbolic-link" iexplain="sort by"}</li>
				<li>{smartlink ititle="Name" isort="group_name" offset=$offset idefault=1}</li>
				<li>{smartlink ititle="Description" isort="group_desc" offset=$offset}</li>
				<li>{smartlink ititle="Home Page" isort="group_home" offset=$offset}</li>
			</ul>
		</div><!-- end .navbar -->

		{formfeedback success=$successMsg error=$errorMsg}

		<ul class="clear data">
			{foreach from=$groupList key=groupId item=grp}
				<li class="item {cycle values='odd,even'}">
					<div class="floaticon">
						{smartlink ititle="Edit" ipackage="users" ifile="admin/edit_group.php" ibiticon="icons/accessories-text-editor" group_id=$groupId}
						{smartlink ititle="Group Members" ipackage="users" ifile="admin/edit_group.php" ibiticon="icons/system-users" members=$groupId}
						{if $groupId ne $smarty.const.ANONYMOUS_GROUP_ID}
							{smartlink ititle="Batch assign" ipackage="users" ifile="admin/edit_group.php" ibiticon="users/batch_assign" batch_assign=$groupId}
							{smartlink ititle="Remove" ipackage="users" ifile="admin/edit_group.php" ibiticon="icons/edit-delete" action=delete group_id=$groupId}
						{/if}
					</div>

					<h2>{$grp.group_name}</h2>
					<div style="float:left;width:30%;">
						{$grp.group_desc}<br />
						{if $grp.is_default eq 'y'}<small class="warning"> *{tr}Default group{/tr}*</small><br/>{/if}
						{if $grp.group_home}{tr}Home Page{/tr}:<strong> {$grp.group_home}</strong><br />{/if}
						{if $grp.included}
							<br />{tr}Included Groups{/tr}
							<ul class="data small">
								{foreach from=$grp.included key=incGroupId item=incGroupName}
									<li class="{cycle values="odd,even"} item">{$incGroupName}</li>
								{/foreach}
							</ul>
						{/if}
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
