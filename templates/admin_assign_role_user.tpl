{* $Header$ *}
{strip}
<div class="floaticon">{bithelp}</div>
<div class="floaticon"><a href="{$smarty.const.USERS_PKG_URL}admin/index.php">{booticon iname="icon-arrow-left"  ipackage="icons"  iexplain="back to users"}</a></div>

<div class="admin users">
	<div class="header">
		<h1>{tr}Assign user to roles{/tr}</h1>
		<p>{tr}Assign and remove roles for user {$assignUser->mInfo.login}{/tr}</p>
	</div>

	<div class="body">
		{form legend="User Information" action="`$smarty.const.USERS_PKG_URL`admin/assign_role_user.php"}
			<input type="hidden" value="{$assignUser->mUserId}" name="assign_user" />

			<div class="form-group">
				{formlabel label="Username"}
				{forminput}
					{$assignUser->getDisplayName(TRUE)}
				{/forminput}
			</div>

			<div class="form-group">
				{formlabel label="Email"}
				{forminput}
					{$assignUser->mInfo.email}
				{/forminput}
			</div>

			<div class="form-group">
				{formlabel label="User ID"}
				{forminput}
					{$assignUser->mUserId}
				{/forminput}
			</div>

			{if $gBitSystem->isPackageActive('quota')}
			{include_php file="`$smarty.const.QUOTA_PKG_PATH`quota_inc.php"}
			<div class="form-group">
				{formlabel label="Quota"}
				{forminput}
					{$usage} / {$quota}MB ( {$quotaPercent}% )
				{/forminput}
			</div>
			{/if}

			<div class="form-group">
				{formlabel label="Roles"}
				{forminput}
					<ul>
					{foreach from=$assignUser->mRoles key=roleId item=role}
						{if $roleId eq $assignUser->mInfo.default_role_id}<strong>{/if}
						<a href="{$smarty.const.USERS_PKG_URL}admin/edit_role.php?role_id={$roleId}">{$role.role_name}</a>
						{if $roleId eq $assignUser->mInfo.default_role_id}</strong>{/if}
						{if $roleId != -1}
							&nbsp;<a class="btn btn-xs btn-danger" href="{$smarty.const.USERS_PKG_URL}admin/assign_role_user.php?action=removerole&amp;role_id={$roleId}&amp;assign_user={$assignUser->mUserId}&amp;tk={$gBitUser->mTicket}">{booticon iname="icon-trash" ipackage="icons" iexplain="remove from role" iforce="icon"}</a>
						{/if}
						<br />
					{/foreach}
					</ul>
				{/forminput}
			</div>

			<div class="form-group">
				{formlabel label="Default Role" for="default_role"}
				{forminput}
					<select name="default_role" id="default_role">
						{foreach from=$assignUser->mRoles key=roleId item=role}
							<option value="{$roleId}" {if $roleId eq $assignUser->mInfo.default_role_id}selected="selected"{/if}>{$role.role_name}</option>
						{/foreach}
					</select>
				{/forminput}
			</div>

			<div class="form-group submit">
				<input type="submit" class="btn btn-default" value="{tr}set{/tr}" name="set_default" />
			</div>
		{/form}

		{minifind}

		<table class="table data">
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
								{booticon iname="icon-key" ipackage="icons" iexplain="assign" iforce="icon"}
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
