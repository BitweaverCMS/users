{strip}

{if $gBitUser->hasPermission( 'p_users_admin' )}
{form class=""}
	<div class="form-group col-xs-12 col-sm-6">
		{forminput}
			<input type="text" class="form-control" name="find" value="{$smarty.request.find}"  autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"/>
		{/forminput}
		{formhelp note="Search full name, username, or email"}
	</div>
	{if $gBitSystem->isPackageActive('stats')}
	<div class="form-group col-xs-12 col-sm-6">
		<input type="text" name="referer" class="form-control" value="{$smarty.request.referer}"/>
		{formhelp note="Referrer. Enter partial URL or 'none'"}
	</div>
	{/if}
	<div class="form-group col-xs-4 col-md-2">
		<input class="form-control" type="number" name="max_records" value="{$smarty.request.max_records}"/>
		{formhelp note="# / Page"}
	</div>
	<div class="form-group col-xs-4 col-md-2">
		<input type="number" min="0" step="1" class="form-control" name="max_content_count" value="{$smarty.request.max_content_count}" />
		{formhelp note="Max # objects created"}
	</div>
	<div class="form-group col-xs-4 col-md-2">
		<input type="number" min="0" step="1" class="form-control" name="min_content_count" value="{$smarty.request.min_content_count}"/>
		{formhelp note="Min # objects created"}
	</div>
	<div class="form-group col-xs-6 col-md-4">
		<textarea rows="1" name="ip" class="form-control">{$smarty.request.ip}</textarea>
		{formhelp note="IP. Comma separated list"}
	</div>
	<div class="form-group submit">
		<input class="btn btn-xs" type="submit" name="search" value="{tr}Find{/tr}"> <input class="btn btn-xs" type="reset" name="reset" value="{tr}Reset{/tr}">
	</div>
{/form}
{else}
	{minifind}
{/if}
<nav class="clear">
	<ul class="list-inline navbar">
		<li>{booticon iname="icon-circle-arrow-right"  ipackage="icons"  iexplain="sort by"}</li>
		<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Username" isort="login"}</li>
		<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Real name" isort="real_name"}</li>
		<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Registration Date" isort="registration_date"}</li>
		<li>{smartlink iurl=$control.URL offset=$control.offset numrows=$control.numrows ititle="Last Login" isort="current_login"}</li>
	</ul>
</nav>

{formfeedback hash=$feedback}

{form id=checkform action=$smarty.server.REQUEST_URI}
	<ul class="clear data inline userslist media-grid" start="{$listInfo.offset+1}">
		{foreach from=$users item=userHash key=userId}
			<li class="item {cycle values='even,odd'} pull-left" style="width:31%;padding:0 0 0 1%;background:url('{$userHash.thumbnail_url|default:"`$smarty.const.USERS_PKG_URL`icons/silhouette_100.png"|escape}') no-repeat scroll top right transparent;">
					{if $gBitUser->hasPermission( 'p_users_admin' )}
					{forminput label="checkbox"}
						<input type="checkbox" name="batch_user_ids[]" value="{$userHash.user_id}" /> 
<small>{$listInfo.offset+$userHash@iteration}.</small> <strong>{$userHash.real_name|default:$userHash.login|escape}</strong> 
					{/forminput}
						<div><a href="/{$userHash.login}">{$userHash.login}</a></div>
					{else}
					<h4>{displayname hash=$userHash}</h4>
					{/if}

				<div class="usersinfo">
				{if $gBitUser->hasPermission( 'p_users_admin' )}
					<div>{if !empty($userHash.email)}{mailto address=$userHash.email encode="javascript"}{else}&nbsp;{/if}</div>
				{/if}


				<strong>{tr}Registered{/tr}:</strong> {$userHash.registration_date|bit_short_date}<br/>
				<div><strong>{tr}Last Login{/tr}:</strong> {$userHash.current_login|bit_short_date}</div>

				{if $gBitUser->hasPermission( 'p_users_admin' )}
					<div class="icon pull-right">
						{assign var=contentCount value=$userHash.user_id|get_user_content_count}
						{if $gBitUser->hasPermission( 'p_users_admin' ) && $contentCount}
							<strong title="{tr}Content Count{/tr}">{$contentCount} </strong>
						{/if}
						{if $gBitUser->hasPermission( 'p_commerce_admin' )}
							{smartlink ipackage=bitcommerce ifile="admin/list_orders.php" user_id=$userHash.user_id ititle="Orders" booticon="icon-shopping-cart" iforce="icon"}
						{/if}
						{smartlink ipackage=liberty ifile="list_content.php" user_id=$userHash.user_id ititle="User Content" booticon="icon-list" iforce="icon"}
						{smartlink ipackage=users ifile="admin/index.php" assume_user=$userHash.user_id ititle="Assume User Identity" booticon="icon-user-md" iforce=icon}
						{smartlink ipackage=users ifile="preferences.php" view_user=$userHash.user_id ititle="Edit User Information" booticon="icon-edit" iforce=icon}
						{if $gBitSystem->isPackageActive('protector')}
							{smartlink ipackage=users ifile="admin/assign_role_user.php" assign_user=$userHash.user_id ititle="Assign Group" booticon="icon-key" iforce=icon}
						{else}
							{smartlink ipackage=users ifile="admin/assign_user.php" assign_user=$userHash.user_id ititle="Assign Role" booticon="icon-key" iforce=icon}
						{/if}
						{smartlink ipackage=users ifile="admin/user_activity.php" user_id=$userHash.user_id ititle="User Activity" booticon="icon-bolt" iforce="icon"}
						{if $userHash.user_id != $smarty.const.ANONYMOUS_USER_ID && $userHash.user_id != $smarty.const.ROOT_USER_ID && $userHash.user_id != $gBitUser->mUserId}
							{if $userHash.content_status_id > 0}
								{smartlink ipackage=users ifile="admin/index.php" user_id=$userHash.user_id action=ban ititle="Ban User" booticon="icon-minus-sign" iforce=icon}
							{else}
								{smartlink ipackage=users ifile="admin/index.php" user_id=$userHash.user_id action=unban ititle="Restore the User Account" booticon="icon-recycle" iforce=icon}
							{/if}
							{smartlink ipackage=users ifile="admin/index.php" user_id=$userHash.user_id action=delete ititle="Remove" booticon="icon-trash" iforce=icon}
						{/if}
					</div>
					<div>{tr}User ID{/tr}: {$userHash.user_id}</div>
					<div class="small clear">
					{if $userHash.referer_url}
						<a href="{$userHash.referer_url}" title="{$userHash.short_referer_url|escape}">{$userHash.short_referer_url|truncate:50}</a>
					{else}
						&nbsp;
					{/if}
					</div>
				{/if}

				</div>
			</li>
		{/foreach}
	</ul>

	{if $gBitUser->hasPermission( 'p_users_admin' )}
		<div class="clear">
			<div class="form-inline">
				<div class="form-group">
					{forminput label="checkbox"}
						<input name="switcher" id="switcher" type="checkbox" onclick="BitBase.switchCheckboxes(this.form.id,'batch_user_ids[]','switcher')" /> {tr}Select All{/tr}
					{/forminput}
					{forminput}
						<select class="form-control input-xs" name="action">
							<option value="">{tr}with checked{/tr}:</option>
							<option value="delete">{tr}Remove{/tr}</option>
							<option value="export">{tr}Export List{/tr}</option>
						</select>
					{/forminput}
					{forminput}
						<input type="submit" class="btn btn-xs btn-default" name="" value="Submit"/>
					{/forminput}
				</div>
			</div>
		</div>
	{/if}
{/form}

<p class="clear total small">
	{tr}Total number of entries{/tr}: {$usercount}
</p>

{pagination}

{/strip}
