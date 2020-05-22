{strip}
<div class="floaticon">{bithelp}</div>

<div class="admin users">
	<div class="header">
		<h1>{tr}Admin users{/tr}</h1>
	</div>

	<div class="body">
		{jstabs}
			{jstab title="List of Users"}
				{include file="bitpackage:users/users_list.tpl"}
			{/jstab}

			{jstab title="Add User"}
				{formfeedback success=$addSuccess}
				{form legend="Add a new user" secure=$gBitSystem->isFeatureActive("site_https_login_required")}
					<input type="hidden" name="tab" value="useradd" />
					<div class="form-group">
						{formlabel label="Real Name" for="real_name"}
						{forminput}
							<input class="form-control" type="text" name="real_name" id="real_name" value="{$newUser.real_name}" />
						{/forminput}
					</div>

					<div class="form-group">
						{formfeedback error=$errors.email}
						{formlabel label="Email" for="email"}
						{forminput}
							<input class="form-control" type="email" name="email" id="email" size="30" value="{$newUser.email}" />
						{/forminput}
					</div>

					<div class="form-group">
						{formfeedback error=$errors.login}
						{formlabel label="Username" for="login"}
						{forminput}
							<input class="form-control" type="text" name="login" id="login" value="{$newUser.login}"  />
						{/forminput}
					</div>

					<div class="form-group">
						{formfeedback error=$errors.password}
						{formlabel label="Password" for="password"}
						{forminput}
							<div class="input-group">
								<input class="form-control" type="password" name="password" id="password" value="{$newUser.password}"  />
								<span class="input-group-addon" onclick="BitBase.genPass('password','password','password2');$('#password').prop('type','text');">{tr}Generate{/tr}</span>
							</div>
						{/forminput}
						{forminput}
							
						{/forminput}
					</div>

					<div class="form-group">
						{formlabel label="Repeat Password" for="password2"}
						{forminput}
							<input class="form-control" type="password" name="password2" id="password2" value="{$newUser.password2}"  />
						{/forminput}
					</div>

					<div class="form-group">
						{formfeedback error=$errors.login}
						{formlabel label="User ID" for="user_id"}
						{forminput}
							<input class="form-control" type="number" min="1" step="1" name="user_id" id="user_id" value="{$newUser.user_id}"/>
							{formhelp note="Specify an exact user_id. This is not recommended. If you have no idea why you would need to do this, you do not need this."}
						{/forminput}
					</div>

					<div class="form-group">
						{if $gBitSystem->isPackageActive('protector')}
							{if $defaultRoleId eq ''}
								{formfeedback error="No default role is currently set. Please set one in the Administration --&gt; Users --&gt; <a href=\"`$smarty.const.USERS_PKG_URL`admin/edit_role.php\">Roles and Permissions</a> page"}
							{/if}
							{formlabel label="User will be added to the following role" for=""}
							{forminput}
								{$defaultRoleName} <a href="{$smarty.const.USERS_PKG_URL}admin/edit_role.php?role_id={$defaultRoleId}">{booticon iname="icon-edit" ipackage="icons" iexplain="change settings"}</a>
								{formhelp note="This is the role that is selected as the default role. If you would like to change the default role, please click on the edit icon and set a different role as default role."}
							{/forminput}
						{else}
							{if $defaultGroupId eq ''}
								{formfeedback error="No default group is currently set. Please set one in the Administration --&gt; Users --&gt; <a href=\"`$smarty.const.USERS_PKG_URL`admin/edit_group.php\">Groups and Permissions</a> page"}
							{/if}
							{formlabel label="User will be added to the following group" for=""}
							{forminput}
								{$defaultGroupName} <a href="{$smarty.const.USERS_PKG_URL}admin/edit_group.php?group_id={$defaultGroupId}">{booticon iname="icon-edit" ipackage="icons" iexplain="change settings"}</a>
								{formhelp note="This is the group that is selected as the default group. If you would like to change the default group, please click on the edit icon and set a different group as default group."}
							{/forminput}
						{/if}
					</div>

					<div class="form-group">
					</div>

					<div class="form-group">
						{forminput}
							{forminput label="checkbox"}
								<input type="checkbox" name="admin_verify_user" id="admin_verify_user" /> {tr}Validate user by email{/tr}
								{formhelp note="This will email the user a validation url with a temporary one time password. On validation the user is forced to choose a new password."}
							{/forminput}
						{/forminput}
					</div>

					<div class="form-group">
						{forminput label="checkbox"}
							<input type="checkbox" name="admin_verify_email" id="admin_verify_email" /> {tr}Validate email address{/tr}
							{formhelp note="This feature should be used only when you need the maximum security and should be used with discretion. If a visitor's email server is not responding, they will not be able to register. You also must have a valid sender email to use this feature."}
						{/forminput}
					</div>

					<div class="form-group">
						{forminput label="checkbox"}
							<input type="checkbox" name="admin_noemail_user" id="admin_noemail_user" /> {tr}Don't email new user{/tr}
							{formhelp note="Do not email the new user a registration confirmation with their login information."}
						{/forminput}
					</div>

					{*include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_mini_tpl"*}

					<div class="form-group submit">
						{if $gBitSystem->isPackageActive('protector')}
							<input type="submit" class="btn btn-default" name="newuser" value="{tr}Add User{/tr}"{if $defaultRoleId eq ''} disabled="disabled"{/if} />
						{else}
							<input type="submit" class="btn btn-default" name="newuser" value="{tr}Add User{/tr}"{if $defaultGroupId eq ''} disabled="disabled"{/if} />
						{/if}	
					</div>
				{/form}
			{/jstab}
		{/jstabs}
	</div><!-- end .body -->
</div><!-- end .users -->

{/strip}
