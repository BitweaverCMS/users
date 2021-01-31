{strip}

<div class="display wiki user">
	{if $gBitUser->hasPermission( 'p_users_admin' ) || $gBitUser->mUserId eq $gQueryUser->mUserId}
		<div class="floaticon">
			{if $gBitUser->hasPermission( 'p_users_admin' )}
				{smartlink ipackage=users ifile="admin/index.php" assume_user=$userInfo.user_id ititle="Assume user identity" booticon="icon-user-md" iforce="icon"}
				{smartlink ipackage=users ifile="admin/assign_user.php" assign_user=$userInfo.user_id ititle="Assign Group" booticon="icon-key" iforce="icon"}
				{smartlink ipackage=users ifile="admin/user_activity.php" user_id=$userInfo.user_id ititle="User Activity" booticon="icon-bolt" iforce="icon"}
				{if $users[user].user_id != $smarty.const.ANONYMOUS_USER_ID}
					{smartlink ipackage=liberty ifile="list_content.php" user_id=$userInfo.user_id ititle="User Content" booticon="icon-list-alt" iforce="icon"}
					{smartlink ipackage=users ifile="admin/index.php" action=delete user_id=$userInfo.user_id ititle="Remove" booticon="icon-trash" iforce="icon"}
				{/if}
				{if $gBitUser->mUserId != $gQueryUser->mUserId}
					{smartlink ipackage=users ifile="preferences.php" view_user=$userInfo.user_id ititle="Edit User Information" booticon="icon-check" iforce="icon"}
				{/if}
			{/if}
			{if $gBitUser->isRegistered() && $gBitUser->mUserId eq $gQueryUser->mUserId}
				{if $gBitSystem->isFeatureActive('users_preferences')}
					{smartlink ipackage=users ifile="preferences.php" ititle="Edit personal profile and images" booticon="icon-check"}
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
		<header>
			<h1 class="page-header">{$userInfo.title|escape}</h1>
		</header>
		<article>
			{$gQueryUser->getParsedData()}
		</article>
	</div>
</div>
{/strip}

