{strip}
<div class="display wiki user">
	{if $gBitUser->hasPermission( 'bit_p_admin_users' )}
		<div class="floaticon">
			{if $gBitUser->mUserId ne $gQueryUser->mUserId}
				{smartlink ipackage=users ifile="admin/index.php" assume_user=$userInfo.user_id ititle="Assume user identity" ibiticon="users/assume_user"}
			{else}
				{smartlink ipackage=users ifile="preferences.php" ititle="Edit your personal profile" ibiticon="liberty/edit"}
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

	{legend legend="User Information"}
		{include file="bitpackage:users/user_information_inc.tpl" userData=$gQueryUser}
	{/legend}

	<div class="content">
		<div class="body">
			{if $parsed}
				{if $gBitUser->mUserId eq $gQueryUser->mUserId}
					<div class="floaticon">
						{smartlink ipackage=users ifile="edit_personal_page.php" ititle="Edit your personal wiki page" ibiticon="liberty/edit"}
					</div>
				{/if}

				<div class="header">
					<h1>{$userInfo.title}</h1>
				</div>
			{else}
				<p>
					{if $gBitUser->mUserId ne $gQueryUser->mUserId}
						{tr}This user has not entered any information yet.{/tr}
					{else}
						{tr}To enter some information here, please <a href="{$gBitLoc.USERS_PKG_URL}edit_personal_page.php">edit your personal homepage</a>.{/tr}
					{/if}
				</p>
			{/if}

			{if $userInfo.portrait_url}
				<img src="{$userInfo.portrait_url}" class="portrait" title="{tr}Portrait{/tr}" alt="{tr}Portrait{/tr}" />
			{/if}

			{$parsed}
		</div><!-- end .body -->
	</div><!-- end .content -->
</div><!-- end .user -->
{/strip}
