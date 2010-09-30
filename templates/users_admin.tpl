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
					<div class="row">
						{formlabel label="Real Name" for="real_name"}
						{forminput}
							<input type="text" name="real_name" id="real_name" value="{$newUser.real_name}" />
						{/forminput}
					</div>

					<div class="row">
						{formfeedback error=$errors.login}
						{formlabel label="User" for="login"}
						{forminput}
							<input type="text" name="login" id="login" value="{$newUser.login}"  />
						{/forminput}
					</div>

					<div class="row">
						{formfeedback error=$errors.password}
						{formlabel label="Password" for="password"}
						{forminput}
							<input type="password" name="password" id="password" value="{$newUser.password}"  />
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Repeat Password" for="password2"}
						{forminput}
							<input type="password" name="password2" id="password2" value="{$newUser.password2}"  />
						{/forminput}
					</div>

					<div class="row">
						{formfeedback error=$errors.email}
						{formlabel label="Email" for="email"}
						{forminput}
							<input type="text" name="email" id="email" size="30" value="{$newUser.email}" />
						{/forminput}
					</div>

					<div class="row">
						{if $defaultGroupId eq ''}
							{formfeedback error="No default group is currently set. Please set one in the Administration --&gt; Users --&gt; <a href=\"`$smarty.const.USERS_PKG_URL`admin/edit_group.php\">Groups and Permissions</a> page"}
						{/if}
						{formlabel label="User will be added to the following group" for=""}
						{forminput}
							{$defaultGroupName} <a href="{$smarty.const.USERS_PKG_URL}admin/edit_group.php?group_id={$defaultGroupId}">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="change settings"}</a>
							{formhelp note="This is the group that is selected as the default group. If you would like to change the default group, please click on the edit icon and set a different group as default group."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="<a href=\"javascript:BitBase.genPass('genepass','password','password2');\">{tr}Generate a password{/tr}</a>" for="email"}
						{forminput}
							<input id="genepass" type="text" />
							{formhelp note="You can use this link to create a random password. Make sure you pass the information on to the user."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Validate user by email" for="admin_verify_user"}
						{forminput}
							<input type="checkbox" name="admin_verify_user" id="admin_verify_user" />
							{formhelp note="This will email the user a validation url with a temporary one time password. On validation the user is forced to choose a new password."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Validate email address" for="admin_verify_email"}
						{forminput}
							<input type="checkbox" name="admin_verify_email" id="admin_verify_email" />
							{formhelp note="This feature should be used only when you need the maximum security and should be used with discretion. If a visitor's email server is not responding, they will not be able to register. You also must have a valid sender email to use this feature."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Don't email added user" for="admin_noemail_user"}
						{forminput}
							<input type="checkbox" name="admin_noemail_user" id="admin_noemail_user" />
							{formhelp note="If you for some reason don't want to email the added user the login and password, or validation url."}
						{/forminput}
					</div>

					{*include file="bitpackage:liberty/edit_services_inc.tpl" serviceFile="content_edit_mini_tpl"*}

					<div class="row submit">
						<input type="submit" name="newuser" value="{tr}Add User{/tr}"{if $defaultGroupId eq ''} disabled="disabled"{/if} />
					</div>
				{/form}
			{/jstab}
		{/jstabs}
	</div><!-- end .body -->
</div><!-- end .users -->

{/strip}
