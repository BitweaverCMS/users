{strip}

{if $userInfo.logo_url}
	<p style="text-align:center;">
		<img src="{$userInfo.logo_url}" class="icon" title="{tr}Logo{/tr}" alt="{$gBitUser->getDisplayName()} {tr}Logo{/tr}" />
	</p>
{/if}

<div class="display wiki user">
	{if $gBitUser->hasPermission( 'p_users_admin' ) || $gBitUser->mUserId eq $gQueryUser->mUserId}
		<div class="floaticon">
			{if $gBitUser->hasPermission( 'p_users_admin' )}
				{smartlink ipackage=users ifile="admin/index.php" assume_user=$userInfo.user_id ititle="Assume user identity" ibiticon="users/assume_user" iforce="icon"}
				{smartlink ipackage=users ifile="preferences.php" view_user=$userInfo.user_id ititle="Edit User Information" ibiticon="liberty/edit" iforce="icon"}
				{smartlink ipackage=users ifile="admin/assign_user.php" assign_user=$userInfo.user_id ititle="Assign Group" ibiticon="liberty/permissions" iforce="icon"}
				{if $users[user].user_id != -1}{* TODO: evil hardcoding *}
					{smartlink ipackage=users ifile="admin/index.php" action=delete user_id=$userInfo.user_id ititle="Remove" ibiticon="liberty/delete" iforce="icon"}
				{/if}
			{/if}

			{if $gBitUser->mUserId eq $gQueryUser->mUserId}
				{if $gBitSystem->isFeatureActive('users_preferences')}
					{smartlink ipackage=users ifile="preferences.php" ititle="Edit personal profile and images" ibiticon="liberty/config"}
				{/if}
				{if $gBitUser->hasPermission('p_users_edit_user_homepage')}
					{smartlink ipackage=users ifile="edit_personal_page.php" ititle="Edit personal wiki page" ibiticon="liberty/edit"}
				{/if}
			{/if}
		</div>
	{/if}

	<div class="header">
		<h1 >{displayname hash=$userInfo nolink=true}</h1>
		{if $gBitSystem->isPackageActive('stars') && $gBitSystem->isFeatureActive('stars_user_ratings')}
			{include file="bitpackage:stars/user_ratings.tpl"}
		{/if}
	</div>

	{if $userInfo.last_modified ne $userInfo.last_modified}
		<div style="text-align:right">
			{tr}Updated{/tr} {$userInfo.created|bit_date_format}
		</div>
	{/if}

	{if $gBitUser->mUserId == $gQueryUser->mUserId}
		{include file="bitpackage:users/my_bitweaver_bar.tpl"}
	{/if}

	<div class="body">
		{if !$parsed}
			{if $gBitUser->mUserId ne $gQueryUser->mUserId}
				<p>{tr}This user has not entered any information yet.{/tr}</p>
			{elseif $gBitUser->hasPermission('p_users_edit_user_homepage')}
				<p>{tr}To enter some information here, please <a href="{$smarty.const.USERS_PKG_URL}edit_personal_page.php">edit your personal homepage</a>.{/tr}</p>
			{/if}

			{if $userInfo.portrait_url}
				<p style="text-align:center;">
					<img src="{$userInfo.portrait_url}" class="icon" title="{tr}Portrait{/tr}" alt="{tr}Portrait{/tr}" />
				</p>
			{/if}

			{include file="bitpackage:users/user_information_inc.tpl" userData=$gQueryUser}
		{else}
			{jstabs}
				{jstab title="User Page"}
					<div class="header">
						<h1>{$userInfo.title|escape}</h1>
					</div>

					<div class="body">
						<div class="content">
							{if $userInfo.portrait_url}
								<img src="{$userInfo.portrait_url}" class="portrait" title="{tr}Portrait{/tr}" alt="{tr}Portrait{/tr}" />
							{/if}

							{$parsed}
							<div style="clear:both"></div>
						</div><!-- end .content -->
					</div><!-- end .body -->
				{/jstab}

				{jstab title="User Information"}
					<div class="header">
						<h1 >{displayname hash=$userInfo nolink=true}</h1>
					</div>

					{include file="bitpackage:users/user_information_inc.tpl" userData=$gQueryUser}
				{/jstab}
			{/jstabs}
		{/if}
	</div><!-- end .body -->
</div><!-- end .user -->
{/strip}
