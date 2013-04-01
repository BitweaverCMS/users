{strip}

{if $gBitUser->hasPermission( 'p_users_admin' )}
<div class="width100p">
{form}
	<div class="control-group floatleft clearnone width20p">
		{formlabel label="Search"}
		{forminput}
			<input type="text" name="find" value="{$smarty.request.find}"/>
		{/forminput}
		{formhelp note="Full name, username, and email"}
	</div>
	<div class="control-group floatleft clearnone width10p">
		{formlabel label="# Results"}
		<input type="text" name="max_records" value="{$smarty.request.max_records}" style="width:3em"/>
		{formhelp note="Per page"}
	</div>
	<div class="control-group floatleft clearnone width10p">
		{formlabel label="Max Content"}
		<input type="text" name="max_content_count" value="{$smarty.request.max_content_count}" style="width:3em"/>
		{formhelp note="# objects created"}
	</div>
	<div class="control-group floatleft clearnone width10p">
		{formlabel label="Min Content"}
		<input type="text" name="min_content_count" value="{$smarty.request.min_content_count}" style="width:3em"/>
		{formhelp note="# objects created"}
	</div>
	{if $gBitSystem->isPackageActive('stats')}
	<div class="control-group floatleft clearnone width20p">
		{formlabel label="Registration Referer"}
		<input type="text" name="referer" value="{$smarty.request.referer}" class="width90p"/>
		{formhelp note="Enter partial URL or 'none'"}
	</div>
	{/if}
	<div class="control-group floatleft clearnone width15p">
		{formlabel label="IP"}
		<textarea rows="1" name="ip" style="height:15px">{$smarty.request.ip}</textarea>
		{formhelp note="Comma separated list"}
	</div>
	<div class="control-group submit">
		<input type="submit" name="search" value="{tr}Find{/tr}">
		<input type="reset" name="reset" value="{tr}Reset{/tr}">
	</div>
{/form}
</div>
{else}
	{minifind}
{/if}
<ul class="inline navbar">
	<li>{biticon ipackage="icons" iname="emblem-symbolic-link" iexplain="sort by"}</li>
	<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Username" isort="login"}</li>
	<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Real name" isort="real_name"}</li>
	<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Registration Date" isort="registration_date"}</li>
	<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Last Login" isort="current_login"}</li>
</ul>


{formfeedback hash=$feedback}

{form id=checkform action=$smarty.server.REQUEST_URI}
	<ol class="clear data userslist" start="{$listInfo.offset+1}">
		{foreach from=$users item=userHash key=userId}
			<li class="item {cycle values='even,odd'}">
				{if $gBitUser->hasPermission( 'p_users_admin' )}
					<div class="floaticon">
						{smartlink ipackage=users ifile="admin/index.php" assume_user=$userHash.user_id ititle="Assume User Identity" ibiticon="users/assume_user" iforce=icon}
						{smartlink ipackage=users ifile="preferences.php" view_user=$userHash.user_id ititle="Edit User Information" ibiticon="icons/accessories-text-editor" iforce=icon}
						{if $gBitSystem->isPackageActive('protector')}
							{smartlink ipackage=users ifile="admin/assign_role_user.php" assign_user=$userHash.user_id ititle="Assign Group" ibiticon="icons/emblem-shared" iforce=icon}
						{else}
							{smartlink ipackage=users ifile="admin/assign_user.php" assign_user=$userHash.user_id ititle="Assign Role" ibiticon="icons/emblem-shared" iforce=icon}
						{/if}
						{smartlink ipackage=users ifile="admin/user_activity.php" user_id=$userHash.user_id ititle="User Activity" ibiticon="icons/preferences-desktop-sound" iforce="icon"}
						{smartlink ipackage=liberty ifile="list_content.php" user_id=$userHash.user_id ititle="User Content" ibiticon="icons/format-justify-fill" iforce="icon"}
						{if $gBitUser->hasPermission( 'p_users_admin' )}
							<span title="{tr}Content Count{/tr}">{$userHash.user_id|get_user_content_count}</span>
						{/if}
						{if $userHash.user_id != $smarty.const.ANONYMOUS_USER_ID && $userHash.user_id != $smarty.const.ROOT_USER_ID && $userHash.user_id != $gBitUser->mUserId}
							{if $userHash.content_status_id > 0}
								{smartlink ipackage=users ifile="admin/index.php" user_id=$userHash.user_id action=ban ititle="Ban User" ibiticon="icons/dialog-cancel" iforce=icon}
							{else}
								{smartlink ipackage=users ifile="admin/index.php" user_id=$userHash.user_id action=unban ititle="Restore the User Account" ibiticon="icons/view-refresh" iforce=icon}
							{/if}
							{smartlink ipackage=users ifile="admin/index.php" user_id=$userHash.user_id action=delete ititle="Remove" ibiticon="icons/edit-delete" iforce=icon}
							<input type="checkbox" name="batch_user_ids[]" value="{$userHash.user_id}" />
						{/if}
					</div>
				{/if}

				<img alt="{tr}user portrait{/tr}" title="{$userHash.login} {tr}user portrait{/tr}" src="{$userHash.thumbnail_url|default:"`$smarty.const.USERS_PKG_URL`icons/silhouette_100.png"}" class="thumb" />

				<div class="usersinfo">
				{if $userHash.real_name}
					{if $gBitSystem->getConfig('users_display_name') == 'login'}
						<h2>{$userHash.real_name} <small>[ {displayname hash=$userHash} ]</small></h2>
					{else}
						<h2>{displayname hash=$userHash} <small>[ {$userHash.login} ]</small></h2>
					{/if}
				{else}
					<h2>{displayname hash=$userHash}</h2>
				{/if}

				{if $gBitUser->hasPermission( 'p_users_admin' )}
					<strong>{tr}Email:{/tr}</strong> {if !empty($userHash.email)}{mailto address=$userHash.email encode="javascript"}{/if}<br/>
					<strong>{tr}User ID{/tr}:</strong> {$userHash.user_id}<br/>
					{if $userHash.referer_url}
					<strong>{tr}Referrer{/tr}:</strong>	<a href="{$userHash.referer_url}">{$userHash.short_referer_url}</a><br/>
					{/if}
				{/if}

				<strong>{tr}Member since{/tr}:</strong> {$userHash.registration_date|bit_short_date}<br/>

				{if $userHash.current_login }
					<strong>{tr}Last logged in on{/tr}:</strong> {$userHash.current_login|bit_short_date}<br/>
				{/if}

				</div>
				<div class="clear"></div>
			</li>
		{/foreach}
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
				<option value="export">{tr}Export List{/tr}</option>
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
