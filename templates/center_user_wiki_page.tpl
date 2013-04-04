{strip}

<div class="display wiki user">
	{if $gBitUser->hasPermission( 'p_users_admin' ) || $gBitUser->mUserId eq $gQueryUser->mUserId}
		<div class="floaticon">
			{if $gBitUser->hasPermission( 'p_users_admin' )}
				{smartlink ipackage=users ifile="admin/index.php" assume_user=$userInfo.user_id ititle="Assume user identity" ibiticon="users/assume_user" iforce="icon"}
				{smartlink ipackage=users ifile="preferences.php" view_user=$userInfo.user_id ititle="Edit User Information" booticon="icon-edit" iforce="icon"}
				{smartlink ipackage=users ifile="admin/assign_user.php" assign_user=$userInfo.user_id ititle="Assign Group" booticon="icon-key" iforce="icon"}
				{smartlink ipackage=users ifile="admin/user_activity.php" user_id=$userInfo.user_id ititle="User Activity" booticon="icon-bolt" iforce="icon"}
				{if $users[user].user_id != $smarty.const.ANONYMOUS_USER_ID}
					{smartlink ipackage=liberty ifile="list_content.php" user_id=$userInfo.user_id ititle="User Content" booticon="icon-list-alt" iforce="icon"}
					{smartlink ipackage=users ifile="admin/index.php" action=delete user_id=$userInfo.user_id ititle="Remove" booticon="icon-trash" iforce="icon"}
				{/if}
			{/if}

			{if $gBitUser->isRegistered() && $gBitUser->mUserId eq $gQueryUser->mUserId}
				{if $gBitSystem->isFeatureActive('users_preferences')}
					{smartlink ipackage=users ifile="preferences.php" ititle="Edit personal profile and images" booticon="icon-file"}
				{/if}
				{if $gBitUser->hasPermission('p_users_edit_user_homepage')}
					{smartlink ipackage=users ifile="edit_personal_page.php" ititle="Edit personal wiki page" booticon="icon-edit"}
				{/if}
			{/if}
		</div>
	{/if}
	{if $userInfo.logo_url}
		<div style="text-align:center;">
			<img src="{$userInfo.logo_url}" class="icon" title="{tr}Logo{/tr}" alt="{$gBitUser->getDisplayName()} {tr}Logo{/tr}" />
		</div>
	{/if}
	<div class="clear"></div> {*moves boxes below admin icons*}

	{if $userInfo.last_modified ne $userInfo.last_modified}
		<div style="text-align:right">
			{tr}Updated{/tr} {$userInfo.created|bit_date_format}
		</div>
	{/if}

	<div>
		{if !$parsed}
			{if $gBitUser->mUserId ne $gQueryUser->mUserId}
				<p>{tr}This user has not entered any information yet.{/tr}</p>
			{elseif $gBitUser->hasPermission('p_users_edit_user_homepage')}
				<p>{tr}To enter some information here, please <a href="{$smarty.const.USERS_PKG_URL}edit_personal_page.php">edit your personal homepage</a>.{/tr}</p>
			{/if}
		{else}
			<div>
				<h1>{$userInfo.title|escape}</h1>
			</div>
			<div>
				{$parsed}
			</div>
		{/if}
	</div>
</div>
{/strip}

