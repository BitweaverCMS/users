{strip}

{minifind}

{if $gBitUser->hasPermission( 'p_users_admin' ) && $gBitSystem->isFeatureActive('users_validate_email')}

{/if}
<div class="navbar">
	<ul>
		<li>{biticon ipackage="icons" iname="emblem-symbolic-link" iexplain="sort by"}</li>
		<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Username" isort="login"}</li>
		<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Real name" isort="real_name"}</li>
		<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Registration Date" isort="registration_date"}</li>
		<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Last Login" isort="current_login"}</li>
	</ul>

</div>


{formfeedback hash=$feedback}

{form id=checkform action=$smarty.server.REQUEST_URI}
	<ol class="clear data userslist" start="{$listInfo.offset+1}">
		{section name=user loop=$users}
			<li class="item {cycle values='even,odd'}">
				{if $gBitUser->hasPermission( 'p_users_admin' )}
					<div class="floaticon">
						{smartlink ipackage=users ifile="admin/index.php" assume_user=$users[user].user_id ititle="Assume User Identity" ibiticon="users/assume_user" iforce=icon}
						{smartlink ipackage=users ifile="preferences.php" view_user=$users[user].user_id ititle="Edit User Information" ibiticon="icons/accessories-text-editor" iforce=icon}
						{if $gBitSystem->isPackageActive('protector')}
							{smartlink ipackage=users ifile="admin/assign_role_user.php" assign_user=$users[user].user_id ititle="Assign Group" ibiticon="icons/emblem-shared" iforce=icon}
						{else}
							{smartlink ipackage=users ifile="admin/assign_user.php" assign_user=$users[user].user_id ititle="Assign Role" ibiticon="icons/emblem-shared" iforce=icon}
						{/if}
						{smartlink ipackage=users ifile="admin/user_activity.php" user_id=$users[user].user_id ititle="User Activity" ibiticon="icons/preferences-desktop-sound" iforce="icon"}
						{smartlink ipackage=liberty ifile="list_content.php" user_id=$users[user].user_id ititle="User Content" ibiticon="icons/format-justify-fill" iforce="icon"}
						{if $gBitUser->hasPermission( 'p_users_admin' )}
							<span title="{tr}Content Count{/tr}">{$users[user].user_id|get_user_content_count}</span>
						{/if}
						{if $users[user].user_id != $smarty.const.ANONYMOUS_USER_ID && $users[user].user_id != $smarty.const.ROOT_USER_ID && $users[user].user_id != $gBitUser->mUserId}
							{if $users[user].content_status_id > 0}
								{smartlink ipackage=users ifile="admin/index.php" user_id=$users[user].user_id action=ban ititle="Ban User" ibiticon="icons/dialog-cancel" iforce=icon}
							{else}
								{smartlink ipackage=users ifile="admin/index.php" user_id=$users[user].user_id action=unban ititle="Restore the User Account" ibiticon="icons/view-refresh" iforce=icon}
							{/if}
							{smartlink ipackage=users ifile="admin/index.php" user_id=$users[user].user_id action=delete ititle="Remove" ibiticon="icons/edit-delete" iforce=icon}
							<input type="checkbox" name="batch_user_ids[]" value="{$users[user].user_id}" />
						{/if}
					</div>
				{/if}

				<img alt="{tr}user portrait{/tr}" title="{$users[user].login} {tr}user portrait{/tr}" src="{$users[user].thumbnail_url|default:"`$smarty.const.USERS_PKG_URL`icons/silhouette_100.png"}" class="thumb" />

				<div class="usersinfo">
				{if $users[user].real_name}
					{if $gBitSystem->getConfig('users_display_name') == 'login'}
						<h2>{$users[user].real_name} <small>[ {displayname hash=$users[user]} ]</small></h2>
					{else}
						<h2>{displayname hash=$users[user]} <small>[ {$users[user].login} ]</small></h2>
					{/if}
				{else}
					<h2>{displayname hash=$users[user]}</h2>
				{/if}

				{if $gBitUser->hasPermission( 'p_users_admin' )}
					<strong>{tr}Email:{/tr}</strong> {if !empty($users[user].email)}{mailto address=$users[user].email encode="javascript"}{/if}<br/>
					<strong>{tr}User ID{/tr}:</strong> {$users[user].user_id}<br/>
					{if $users[user].referer_url}
						{if $gBitSystem->isPackageActive('stats')}
							<a href="{$users[user].referer_url}">{$users[user].referer_url|stats_referer_display_short}</a><br/>
						{else}
							<a href="{$users[user].referer_url}">{$users[user].referer_url}</a><br/>
						{/if}
					{/if}
				{/if}

				<strong>{tr}Member since{/tr}:</strong> {$users[user].registration_date|bit_short_date}<br/>

				{if $users[user].current_login }
					<strong>{tr}Last logged in on{/tr}:</strong> {$users[user].current_login|bit_short_date}<br/>
				{/if}

				</div>
				<div class="clear"></div>
			</li>
		{/section}
	</ol>

	{if $gBitUser->hasPermission( 'p_users_admin' )}
		<div style="text-align:right;">
			<script type="text/javascript">/* <![CDATA[ check / uncheck all */
				document.write("<label for=\"switcher\">{tr}Select All{/tr}</label> ");
				document.write("<input name=\"switcher\" id=\"switcher\" type=\"checkbox\" onclick=\"BitBase.switchCheckboxes(this.form.id,'batch_user_ids[]','switcher')\" />");
			/* ]]> */</script>
			<br />
			<select name="action" onchange="this.form.submit();">
				<option value="">{tr}with checked{/tr}:</option>
				<option value="delete">{tr}Remove{/tr}</option>
			</select>

			<noscript>
				<div><input type="submit" value="{tr}Submit{/tr}" /></div>
			</noscript>
		</div>
	{/if}
{/form}

<p class="clear total small">
	{tr}Total number of entries{/tr}: {$usercount}
</p>

{pagination}

{/strip}
