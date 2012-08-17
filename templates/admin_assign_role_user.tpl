{* $Header$ *}
{strip}
<div class="floaticon">{bithelp}</div>
<div class="floaticon"><a href="{$smarty.const.USERS_PKG_URL}admin/index.php">{biticon ipackage="icons" iname="go-previous" iexplain="back to users"}</a></div>

<div class="admin users">
	<div class="header">
		<h1>{tr}Assign user to roles{/tr}</h1>
		<p>{tr}Assign and remove roles for user {$assignUser->mInfo.login}{/tr}</p>
	</div>

	<div class="body">
		{form legend="User Information" action="`$smarty.const.USERS_PKG_URL`admin/assign_role_user.php"}
			<input type="hidden" value="{$assignUser->mUserId}" name="assign_user" />

			<div class="row">
				{formlabel label="Username"}
				{forminput}
					{$assignUser->getDisplayName(TRUE)}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Email"}
				{forminput}
					{$assignUser->mInfo.email}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="User ID"}
				{forminput}
					{$assignUser->mUserId}
				{/forminput}
			</div>

			{if $gBitSystem->isPackageActive('quota')}
			{include_php file="`$smarty.const.QUOTA_PKG_PATH`quota_inc.php"}
			<div class="row">
				{formlabel label="Quota"}
				{forminput}
					{$usage} / {$quota}MB ( {$quotaPercent}% )
				{/forminput}
			</div>
			{/if}

			<div class="row">
				{formlabel label="Roles"}
				{forminput}
					{foreach from=$assignUser->mRoles key=roleId item=role}
						{if $roleId eq $assignUser->mInfo.default_role_id}<strong>{/if}
						<a href="{$smarty.const.USERS_PKG_URL}admin/edit_role.php?role_id={$roleId}">{$role.role_name}</a>
						{if $roleId eq $assignUser->mInfo.default_role_id}</strong>{/if}
						{if $roleId != -1}
							&nbsp;<a href="{$smarty.const.USERS_PKG_URL}admin/assign_role_user.php?action=removerole&amp;role_id={$roleId}&amp;assign_user={$assignUser->mUserId}">{biticon ipackage="icons" iname="edit-delete" iexplain="remove from role" iforce="icon"}</a>
						{/if}
						<br />
					{/foreach}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Default Role" for="default_role"}
				{forminput}
					<select name="default_role" id="default_role">
						{foreach from=$assignUser->mRoles key=roleId item=role}
							<option value="{$roleId}" {if $roleId eq $assignUser->mInfo.default_role_id}selected="selected"{/if}>{$role.role_name}</option>
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
				<th><a href="{$smarty.const.USERS_PKG_URL}admin/assign_role_user.php?assign_user={$assignUser->mUserId}&amp;offset={$offset}&amp;sort_mode={if $sort_mode eq 'role_name_desc'}role_name_asc{else}role_name_desc{/if}">{tr}Role Name{/tr}</a></th>
				<th><a href="{$smarty.const.USERS_PKG_URL}admin/assign_role_user.php?assign_user={$assignUser->mUserId}&amp;offset={$offset}&amp;sort_mode={if $sort_mode eq 'role_desc_desc'}role_desc_asc{else}role_desc_desc{/if}">{tr}Description{/tr}</a></th>
				<th>{tr}action{/tr}</th>
			</tr>
			{cycle values="even,odd" print=false}
			{foreach from=$roles key=roleId item=role}
				{if !$assignUser->mRoles.$roleId && $roleId != -1}
					<tr class="{cycle}">
						<td>{$role.role_name}</td>
						<td>{$role.role_desc}</td>
						<td class="actionicon">
							<a href="{$smarty.const.USERS_PKG_URL}admin/assign_role_user.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;action=assign&amp;role_id={$roleId}&amp;assign_user={$assignUser->mUserId}">
								{biticon ipackage="icons" iname="emblem-shared" iexplain="assign" iforce="icon"}
							</a>
						</td>
					</tr>
				{/if}
			{/foreach}
		</table>

		{pagination assign_user=$assign_user}

	</div><!-- end .body -->
</div><!-- end .users -->
{/strip}
