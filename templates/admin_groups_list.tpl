<div class="listing users">
	<div class="header">
		<h1>{tr}List of existing groups{/tr}</h1>
	</div>

	<div class="body">
		{smartlink ititle="Add a new group" ipackage=users ifile="admin/edit_group.php" action=create}

		<div class="navbar">
			<ul>
				<li>{biticon ipackage=liberty iname=sort iexplain="sort by"}</li>
				<li>{smartlink ititle="Name" isort="group_name" offset=$offset idefault=1}</li>
				<li>{smartlink ititle="Description" isort="group_desc" offset=$offset}</li>
				<li>{smartlink ititle="Home Page" isort="group_home" offset=$offset}</li>
			</ul>
		</div><!-- end .navbar -->

		{formfeedback success=$successMsg error=$errorMsg}

		<ul class="clear data">
			{foreach from=$groups key=groupId item=group}
				<li class="item {cycle values='odd,even'}">
					<div class="floaticon">
						{smartlink ititle="Edit" ipackage="users" ifile="admin/edit_group.php" ibiticon="liberty/edit" group_id=$groupId}
						{smartlink ititle="Group Members" ipackage="users" ifile="admin/edit_group.php" ibiticon="users/users" members=$groupId}
						{if $groupId ne -1}{* sorry for hardcoding, really need php define ANONYMOUS_GROUP_ID - spiderr *}
							{smartlink ititle="Batch assign" ipackage="users" ifile="admin/edit_group.php" ibiticon="users/batch_assign" batch_assign=$groupId}
							{smartlink ititle="Remove" ipackage="users" ifile="admin/edit_group.php" ibiticon="liberty/delete" action=delete group_id=$groupId ionclick="return confirm( '{tr}Are you sure you want to remove this group?{/tr}' )"}
						{/if}
					</div>

					<h2>{$group.group_name}</h2>
					<div style="float:left;width:30%;">
						{$group.group_desc}<br />
						{if $group.is_default eq 'y'}<small class="warning"> *{tr}Default group{/tr}*</small><br/>{/if}
						{if $group.group_home}{tr}Home Page{/tr}:<strong> {$group.group_home}</strong><br />{/if}
						{if $group.included}
							<br />{tr}Included Groups{/tr}
							<ul class="data small">
								{foreach from=$group.included key=incGroupId item=incGroupName}
									<li class="{cycle values="odd,even"} item">{$incGroupName}</li>
								{/foreach}
							</ul>
						{/if}
					</div>

					<div style="float:right;width:70%;">
						{tr}Permissions{/tr}
						<ul class="small">
							{foreach from=$group.perms key=permName item=perm}
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
