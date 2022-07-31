{* $Header$ *}
{strip}
<div class="floaticon">{bithelp}</div>
<div class="floaticon"><a href="{$smarty.const.USERS_PKG_URL}admin/index.php">{booticon iname="fa-arrow-left" iexplain="back to users"}</a></div>

<div class="admin users">
	<div class="header">
		<h1>{tr}Assign user to groups{/tr}</h1>
		<p>{tr}Assign and remove groups for user {$assignUser->mInfo.login}{/tr}</p>
	</div>

	<div class="body">
		<div class="row">
			<div class="col-xs-12 col-sm-6">
				{form legend="User Information" action="`$smarty.const.USERS_PKG_URL`admin/assign_user.php"}
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
					<div class="form-group">
						{formlabel label="Quota"}
						{forminput}
							{$usage} / {$quota}MB ( {$quotaPercent}% )
						{/forminput}
					</div>
					{/if}

					<div class="form-group">
						{formlabel label="Groups"}
						{forminput}
							<ul>
							{foreach from=$assignUser->mGroups key=groupId item=group}
								{if $groupId eq $assignUser->mInfo.default_group_id}<strong>{/if}
								{if $groupId eq $assignUser->mInfo.default_group_id}</strong>{/if}
								{if $groupId != -1}
									&nbsp;<a class="btn btn-xs btn-danger" href="{$smarty.const.USERS_PKG_URL}admin/assign_user.php?action=removegroup&amp;group_id={$groupId}&amp;assign_user={$assignUser->mUserId}&amp;tk={$gBitUser->mTicket}">{booticon iname="fa-trash" iexplain="remove from group"}</a>
								{/if}
								<a href="{$smarty.const.USERS_PKG_URL}admin/edit_group.php?group_id={$groupId}">{$group.group_name}</a>
								<br />
							{/foreach}
							</li>
						{/forminput}
					</div>

					<div class="form-group">
						{formlabel label="Default Group" for="default_group"}
						{forminput}
							<select name="default_group" id="default_group">
								{foreach from=$assignUser->mGroups key=groupId item=group}
									<option value="{$groupId}" {if $groupId eq $assignUser->mInfo.default_group_id}selected="selected"{/if}>{$group.group_name}</option>
								{/foreach}
							</select>
						{/forminput}
					</div>

					<div class="form-group submit">
						<input type="submit" class="btn btn-default" value="{tr}set{/tr}" name="set_default" />
					</div>
				{/form}
			</div>
			<div class="col-xs-12 col-sm-6">
				{legend legend="Add User to Group"}
				<dl  class="">
					{cycle values="even,odd" print=false}
					{foreach from=$groups key=groupId item=group}
						
						{if !$assignUser->mGroups.$groupId && $groupId != -1}
							<dt class="">
									<a href="{$smarty.const.USERS_PKG_URL}admin/assign_user.php?offset={$offset}&amp;sort_mode={$sort_mode}&amp;action=assign&amp;group_id={$groupId}&amp;assign_user={$assignUser->mUserId}&amp;{tk}" class="btn btn-xs btn-default mr-1">{booticon iname="fa-key" iexplain="assign"}</a>
								{$group.group_name|escape}
							</dt>
							<dd class="ml-3 pb-1">
		{$group.group_desc|escape}</dd>
						{/if}
					{/foreach}
				</dl>
				{/legend}
			</div>
		</div>
	</div><!-- end .body -->
</div><!-- end .users -->
{/strip}
