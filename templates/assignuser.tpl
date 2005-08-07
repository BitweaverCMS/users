{* $Header: /cvsroot/bitweaver/_bit_users/templates/Attic/assignuser.tpl,v 1.2 2005/08/07 17:46:48 squareing Exp $ *}
{strip}
<div class="floaticon">{bithelp}</div>
<div class="floaticon"><a href="{$smarty.const.USERS_PKG_URL}admin/index.php">{biticon ipackage=liberty iname=back iexplain="back to users"}</a></div>

<div class="admin users">
	<div class="header">
		<h1>{tr}Assign user to groups{/tr}</h1>
		<h2>Assign and remove groups for user {$assignUser->mInfo.login}</h2>
	</div>

	<div class="body">
		{form legend="User Information" action="`$smarty.const.USERS_PKG_URL`admin/assign_user.php?assign_user=$assign_user"}
			<input type="hidden" value="{$assignUser->mUserId}" name="assign_user" />

			<div class="row">
				{formlabel label="Username"}
				{forminput}
					{$assignUser->mInfo.login}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Email"}
				{forminput}
					{$assignUser->mInfo.email}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Groups"}
				{forminput}
					{foreach from=$assignUser->mGroups key=groupId item=group}
						{if $groupId eq $assignUser->mInfo.default_group_id}<strong>{/if}
						<a href="{$smarty.const.USERS_PKG_URL}admin/edit_group.php?group_id={$groupId}">{$group.group_name}
						{if $groupId eq $assignUser->mInfo.default_group_id}</strong>{/if}
						{if $groupId != -1}
							&nbsp;<a href="{$smarty.const.USERS_PKG_URL}admin/assign_user.php?action=removegroup&amp;group_id={$groupId}&amp;assign_user={$assignUser->mUserId}">{biticon ipackage="liberty" iname="delete_small" iexplain="remove group"}</a>
						{/if}
						<br />
					{/foreach}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Default Group" for="default_group"}
				{forminput}
					<select name="default_group" id="default_group">
						{foreach from=$assignUser->mGroups key=groupId item=group}
							<option value="{$groupId}" {if $groupId eq $assignUser->mInfo.default_group_id}selected="selected"{/if}>{$group.group_name}</option>
						{/foreach}
					</select>
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" value="{tr}set{/tr}" name="set_default" />
			</div>
		{/form}

		{minifind}

		<table class="data">
			<tr>
				<th><a href="{$smarty.const.USERS_PKG_URL}admin/assign_user.php?assign_user={$assignUser->mUserId}&amp;offset={$offset}&amp;sort_mode={if $sort_mode eq 'group_name_desc'}group_name_asc{else}group_name_desc{/if}">{tr}Group Name{/tr}</a></th>
				<th><a href="{$smarty.const.USERS_PKG_URL}admin/assign_user.php?assign_user={$assignUser->mUserId}&amp;offset={$offset}&amp;sort_mode={if $sort_mode eq 'group_desc_desc'}group_desc_asc{else}group_desc_desc{/if}">{tr}Description{/tr}</a></th>
				<th>{tr}action{/tr}</th>
			</tr>
			{cycle values="even,odd" print=false}
			{foreach from=$groups key=groupId item=group}
				{if !$assignUser->mGroups.$groupId && $groupId != -1}
					<tr class="{cycle}">
						<td>{$group.group_name}</td>
						<td>{$group.group_desc}</td>
						<td class="actionicon">
							<a href="{$smarty.const.USERS_PKG_URL}admin/assign_user.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;action=assign&amp;group_id={$groupId}&amp;assign_user={$assignUser->mUserId}">{biticon ipackage="liberty" iname="assign" iexplain="assign"}</a>
						</td>
					</tr>
				{/if}
			{/foreach}
		</table>

		{pagination assign_user=$assign_user}

	</div><!-- end .body -->
</div><!-- end .users -->
{/strip}
