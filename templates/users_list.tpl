{strip}

{minifind}

<div class="navbar">
	<ul>
		<li>{biticon ipackage=liberty iname=sort iexplain="sort by"}</li>
		<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Username" isort="login"}</li>
		<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Real name" isort="real_name"}</li>
		<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Registration Date" isort="registration_date"}</li>
		<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Last Login" isort="current_login"}</li>
	</ul>
</div>

{formfeedback hash=$feedback}

<ul class="clear data">
	{section name=user loop=$users}
		<li class="item {cycle values='even,odd'}">
			{if $gBitUser->hasPermission( 'p_users_admin' )}
				<div class="floaticon">
					<a href="{$smarty.const.USERS_PKG_URL}admin/index.php?assume_user={$users[user].user_id}" title="{tr}Assume User Identity{/tr}">{biticon ipackage=users iname="assume_user" iexplain="assume user identity" iforce="icon"}</a>
					<a href="{$smarty.const.USERS_PKG_URL}preferences.php?view_user={$users[user].user_id}" title="{tr}Edit User Information{/tr}">{biticon ipackage=liberty iname="edit" iexplain="Edit User Information" iforce="icon"}</a>
					<a href="{$smarty.const.USERS_PKG_URL}admin/assign_user.php?assign_user={$users[user].user_id}" title="{tr}Assign Group{/tr}">{biticon ipackage=liberty iname="permissions" iexplain="assign group" iforce="icon"}</a>
					{if $users[user].user_id != -1}{* TODO: evil hardcoding *}
						<a href="{$smarty.const.USERS_PKG_URL}admin/index.php?offset={$control.offset}&amp;numrows={$control.numrows}&amp;sort_mode={$control.sort_mode}&amp;action=delete&amp;user_id={$users[user].user_id}"  title="{tr}Remove{/tr}">{biticon ipackage=liberty iname="delete" iexplain="remove user" iforce="icon"}</a>
					{/if}
				</div>
			{/if}

			{if $users[user].real_name}
				<h2>{displayname hash=$users[user]} <small>{$users[user].login}</small></h2>
			{else}
				<h2>{displayname hash=$users[user]}</h2>
			{/if}

			{if $users[user].thumbnail_url}
				<img alt="{tr}user portrait{/tr}" title="{$users[user].login} {tr}user portrait{/tr}" src="{$users[user].thumbnail_url}" class="thumb" />
			{/if}

			{if $gBitUser->hasPermission( 'p_users_admin' )}
				{mailto address=$users[user].email encode="javascript"} ({tr}User ID{/tr}: {$users[user].user_id})<br/>
			{/if}

			{tr}Member since{/tr}: {$users[user].registration_date|bit_short_date}<br/>

			{if $users[user].current_login }
				{tr}Last seen: {$users[user].current_login|bit_short_date}{/tr}<br/>
			{/if}

			<div class="clear"></div>
		</li>
	{/section}
</ul>

<p class="clear total small">
	{tr}Total number of entries{/tr}: {$usercount}
</p>

{pagination}

{/strip}
