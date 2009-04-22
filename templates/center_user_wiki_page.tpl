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
				{smartlink ipackage=users ifile="preferences.php" view_user=$userInfo.user_id ititle="Edit User Information" ibiticon="icons/accessories-text-editor" iforce="icon"}
				{smartlink ipackage=users ifile="admin/assign_user.php" assign_user=$userInfo.user_id ititle="Assign Group" ibiticon="icons/emblem-shared" iforce="icon"}
				{smartlink ipackage=liberty ifile="admin/user_activity.php?user_id=`$gBitUser->mUserId`" user_id=$userInfo.user_id ititle="User Activity" ibiticon="icons/preferences-desktop-sound" iforce="icon"}
				{if $users[user].user_id != $smarty.const.ANONYMOUS_USER_ID}
					{smartlink ipackage=liberty ifile="list_content.php" user_id=$userInfo.user_id ititle="User Content" ibiticon="icons/format-justify-fill" iforce="icon"}
					{smartlink ipackage=users ifile="admin/index.php" action=delete user_id=$userInfo.user_id ititle="Remove" ibiticon="icons/edit-delete" iforce="icon"}
				{/if}
			{/if}

			{if $gBitUser->isRegistered() && $gBitUser->mUserId eq $gQueryUser->mUserId}
				{if $gBitSystem->isFeatureActive('users_preferences')}
					{smartlink ipackage=users ifile="preferences.php" ititle="Edit personal profile and images" ibiticon="icons/document-properties"}
				{/if}
				{if $gBitUser->hasPermission('p_users_edit_user_homepage')}
					{smartlink ipackage=users ifile="edit_personal_page.php" ititle="Edit personal wiki page" ibiticon="icons/accessories-text-editor"}
				{/if}
			{/if}
		</div>
	{/if}

	<div class="header">
		<h1 >{displayname hash=$userInfo nolink=true}</h1>
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
		{jstabs}
			{if $parsed}
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
			{/if}
		
			{jstab title="User Information"}
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
				{/if}
				{include file="bitpackage:users/user_information_inc.tpl" userData=$gQueryUser}
			{/jstab}

			{if $display_content_list}
				{jstab title="Content List"}
						{include file="bitpackage:liberty/list_content_inc.tpl"}
				{/jstab}
			{/if}

		{/jstabs}
	</div><!-- end .body -->
</div><!-- end .user -->
{/strip}
