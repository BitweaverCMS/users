{strip}
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
			{if $gBitUser->hasPermission( 'bit_p_admin_users' )}
				<div class="floaticon">
					<a href="{$gBitLoc.USERS_PKG_URL}admin/index.php?assume_user={$users[user].user_id}" title="{tr}Assume User Identity{/tr}">{biticon ipackage=users iname="assume_user" iexplain="assume user identity"}</a>
					<a href="{$gBitLoc.USERS_PKG_URL}preferences.php?view_user={$users[user].user_id}" title="{tr}Edit User Information{/tr}">{biticon ipackage=liberty iname="edit" iexplain="Edit User Information"}</a>
				{if $gBitUser->isAdmin()}
					<a href="{$gBitLoc.USERS_PKG_URL}admin/assign_user.php?assign_user={$users[user].user_id}" title="{tr}Assign Group{/tr}">{biticon ipackage=liberty iname="permissions" iexplain="assign group"}</a>
					{if $users[user].user_id != -1}{* TODO: evil hardcoding *}
						<a href="{$gBitLoc.USERS_PKG_URL}admin/index.php?offset={$control.offset}&amp;numrows={$control.numrows}&amp;sort_mode={$control.sort_mode}&amp;action=delete&amp;user_id={$users[user].user_id}"  title="{tr}Remove{/tr}">{biticon ipackage=liberty iname="delete" iexplain="remove user"}</a>
					{/if}
				{/if}
				</div>
			{/if}
			{if $users[user].real_name}
				<h2><a href="{$gBitLoc.USERS_PKG_URL}index.php?home={$users[user].login}">{$users[user].real_name}</a> <small>({$users[user].login})</small></h2>
			{else}
				<h2><a href="{$gBitLoc.USERS_PKG_URL}index.php?home={$users[user].login}">{$users[user].login}</a></h2>
			{/if}
			{if $users[user].thumbnail_url}
				<img alt="{tr}user portrait{/tr}" title="{$users[user].login} {tr}user portrait{/tr}" src="{$users[user].thumbnail_url}" class="thumb" />
			{/if}
			{if $gBitUser->hasPermission( 'bit_p_admin_users' )}{mailto address=$users[user].email encode="javascript"} ({tr}User ID{/tr}: {$users[user].user_id})<br/>{/if}
			{tr}Member since{/tr}: {$users[user].registration_date|bit_short_date}<br/>
			{if $users[user].current_login }{tr}Last seen{/tr}: {$users[user].current_login|bit_short_date}<br/>{/if}
			<div class="clear"></div>
		</li>
	{/section}
</ul>
<div class="clear"></div>

<p class="total small">
	{tr}Total number of entries{/tr}: {$usercount}
</p>

{pagination_c numrows=$numrows}

{minifind}

{/strip}
