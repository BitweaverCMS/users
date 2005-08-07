{jstabs}
	{jstab title="User Registration and Login"}
		{form legend="User Registration and Login"}
			<input type="hidden" name="page" value="{$page}" />
			<div class="row">
				{formlabel label="Authentication method" for="auth_method"}
				{forminput}
					<select name="auth_method" id="auth_method">
						<option value="tiki" {if $auth_method eq 'tiki'} selected="selected"{/if}>{tr}Just bitweaver{/tr}</option>
						<option value="ws" {if $auth_method eq 'ws'} selected="selected"{/if}>{tr}Web Server{/tr}</option>
						<option value="auth" {if $auth_method eq 'auth'} selected="selected"{/if}>{tr}bitweaver and PEAR::Auth{/tr}</option>
					</select>
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Users can register" for="allowRegister"}
				{forminput}
					<input type="checkbox" name="allowRegister" id="allowRegister" {if $allowRegister eq 'y'}checked="checked"{/if}/>
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Send registration welcome email" for="send_welcome_email"}
				{forminput}
					<input type="checkbox" name="send_welcome_email" id="send_welcome_email" {if $gBitSystem->isFeatureActive( 'send_welcome_email' )}checked="checked"{/if}/>
					{formhelp note="Upon successful registration, this will send the user an email with login information, including their password."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Create a group for each user" for="eponymousGroups"}
				{forminput}
					<input type="checkbox" name="eponymousGroups" id="eponymousGroups" {if $eponymousGroups eq 'y'}checked="checked"{/if}/>
					{formhelp note="This will create a group for each user with the same name as the user. This might be useful if you want to assign different permission settings to every user."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Request passcode to register" for="useRegisterPasscode"}
				{forminput}
					<input type="checkbox" name="useRegisterPasscode" id="useRegisterPasscode" {if $useRegisterPasscode eq 'y'}checked="checked"{/if}/>
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Passcode" for="registerPasscode"}
				{forminput}
					<input type="text" name="registerPasscode" id="registerPasscode" value="{$registerPasscode|escape}"/>
					{formhelp note="Enter the Passcode that is required for users to register with your site."}
				{/forminput}
			</div>

			{php}
				if (!function_exists("gd_info"))
					$this->assign( 'warning','PHP GD library is required for this feature (not found on your system)' );
			{/php}

			<div class="row">
				{formfeedback warning=$warning}
				{formlabel label="Prevent automatic/robot registration" for="rnd_num_reg"}
				{forminput}
					<input type="checkbox" name="rnd_num_reg" id="rnd_num_reg" {if $rnd_num_reg eq 'y'}checked="checked"{/if}/>
					{formhelp note="This will generate a random number as an image, the user has to confirm during the registration step."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Validate users by email" for="validateUsers"}
				{forminput}
					<input type="checkbox" name="validateUsers" id="validateUsers" {if $validateUsers eq 'y'}checked="checked"{/if}/>
					{formhelp note="Send an email to the user, to validate registration."}
				{/forminput}
			</div>

			<div class="row">
				{if !$gBitSystem->hasValidSenderEmail()}
					{formfeedback error="Site <a href=\"`$smarty.const.BIT_ROOT_URL`kernel/admin/index.php?page=server\">emailer return address</a> is not valid!"}
				{/if}
				{formlabel label="Validate email address" for="validateEmail"}
				{forminput}
					<input type="checkbox" name="validateEmail" id="validateEmail" {if !$gBitSystem->hasValidSenderEmail()}disabled="disabled"{elseif $validateEmail eq 'y'}checked="checked"{/if} />
					{formhelp link="kernel/admin/index.php?page=server/General Settings" note="This feature should be used only when you need the maximum security and should be used with discretion. If a visitor's email server is not responding, they will not be able to register. You also must have a valid sender email to use this feature."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Remind passwords by email" for="forgotPass"}
				{forminput}
					<input type="checkbox" name="forgotPass" id="forgotPass" {if $forgotPass eq 'y'}checked="checked"{/if}/>
					{formhelp note="This will display a 'forgot password' link on the login page and allow users to have their password sent to their registered email address."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Password invalid after days" for="pass_due"}
				{forminput}
					<input type="text" name="pass_due" id="pass_due" value="{$pass_due|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Store plaintext passwords" for="feature_clear_passwords"}
				{forminput}
					<input type="checkbox" name="feature_clear_passwords" id="feature_clear_passwords" {if $gBitSystem->isFeatureActive( 'feature_clear_passwords' )}checked="checked"{/if}/>
					{formhelp note="Passwords will be visible in the database. If a user requests a password, their password will *not* be reset and simply emailed to them in plain text. This option is less secure, but better suited to sites with a wide variety of users."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Password generator" for="user_password_generator"}
				{forminput}
					<input type="checkbox" name="user_password_generator" id="user_password_generator" {if $gBitSystem->isFeatureActive( 'user_password_generator' )}checked="checked"{/if}/>
					{formhelp note="Display password generator on registration page that creates secure passwords."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Force to use characters <strong>and</strong> numbers in passwords" for="pass_chr_num"}
				{forminput}
					<input type="checkbox" name="pass_chr_num" id="pass_chr_num" {if $pass_chr_num eq 'y'}checked="checked"{/if}/>
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Minimum password length" for="min_pass_length"}
				{forminput}
					<input type="text" name="min_pass_length" id="min_pass_length" value="{$min_pass_length|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Remember me feature" for="rememberme"}
				{forminput}
					<input type="checkbox" name="rememberme" id="rememberme" {if $gBitSystem->isFeatureActive('rememberme')}checked="checked"{/if}/>
					{formhelp note="Registered users will stay logged even if they close their browser."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Duration of 'Remember me' feature" for="remembertime"}
				{forminput}
					<select name="remembertime" id="remembertime">
						<option value="300" {if $remembertime eq 300} selected="selected"{/if}>5 {tr}minutes{/tr}</option>
						<option value="900" {if $remembertime eq 900} selected="selected"{/if}>15 {tr}minutes{/tr}</option>
						<option value="1800" {if $remembertime eq 1800} selected="selected"{/if}>30 {tr}minutes{/tr}</option>
						<option value="3600" {if $remembertime eq 3600} selected="selected"{/if}>1 {tr}hour{/tr}</option>
						<option value="7200" {if $remembertime eq 7200} selected="selected"{/if}>2 {tr}hours{/tr}</option>
						<option value="43200" {if $remembertime eq 43200} selected="selected"{/if}>12 {tr}hours{/tr}</option>
						<option value="86400" {if $remembertime eq 86400} selected="selected"{/if}>1 {tr}day{/tr}</option>
						<option value="604800" {if $remembertime eq 604800} selected="selected"{/if}>1 {tr}week{/tr}</option>
						<option value="2592000" {if $remembertime eq 2592000} selected="selected"{/if}>1 {tr}month{/tr}</option>
						<option value="15724800" {if $remembertime eq 15724800} selected="selected"{/if}>6 {tr}months{/tr}</option>
						<option value="2147483648" {if $remembertime eq 2147483648} selected="selected"{/if}>{tr}Forever{/tr}</option>
					</select>
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Remember me domain" for="cookie_domain"}
				{forminput}
					<input type="text" name="cookie_domain" id="cookie_domain" value="{$cookie_domain|escape}" size="50" />
					{formhelp note="Remember to use a '.' wildcard prefix if you want domain wide cookies."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Remember me path" for="cookie_path"}
				{forminput}
					<input type="text" name="cookie_path" id="cookie_path" value="{$cookie_path|escape}" size="50" />
					{formhelp note="The path '/foo' would match '/foobar' and '/foo/bar.html'"}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" name="loginprefs" value="{tr}Change preferences{/tr}" />
			</div>
		{/form}
	{/jstab}

	{jstab title="HTTP Settings"}
		{form legend="HTTP Settings"}
			<input type="hidden" name="page" value="{$page}" />
			<div class="row">
				{formlabel label="Allow secure (https) login" for="https_login"}
				{forminput}
					<input type="checkbox" name="https_login" id="https_login" {if $https_login eq 'y'}checked="checked"{/if}/>
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Require secure (https) login" for="https_login_required"}
				{forminput}
					<input type="checkbox" name="https_login_required" id="https_login_required" {if $https_login_required eq 'y'}checked="checked"{/if}/>
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="HTTP server name" for="http_domain"}
				{forminput}
					<input type="text" name="http_domain" id="http_domain" value="{$http_domain|escape}" size="50" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="HTTP port" for="http_port"}
				{forminput}
					<input type="text" name="http_port" id="http_port" size="5" value="{$http_port|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="HTTP URL prefix" for="http_prefix"}
				{forminput}
					<input type="text" name="http_prefix" id="http_prefix" value="{$http_prefix|escape}" size="50" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="HTTPS server name" for="https_domain"}
				{forminput}
					<input type="text" name="https_domain" id="https_domain" value="{$https_domain|escape}" size="50" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="HTTPS port" for="https_port"}
				{forminput}
					<input type="text" name="https_port" id="https_port" size="5" value="{$https_port|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="HTTPS URL prefix" for="https_prefix"}
				{forminput}
					<input type="text" name="https_prefix" id="https_prefix" value="{$https_prefix|escape}" size="50" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" name="httpprefs" value="{tr}Change preferences{/tr}" />
			</div>
		{/form}
	{/jstab}

	{jstab title="PEAR::Auth"}
		{form legend="PEAR::Auth"}
			<input type="hidden" name="page" value="{$page}" />
			<div class="row">
				{formlabel label="Create user if not in bitweaver" for="auth_create_gBitDbUser"}
				{forminput}
					<input type="checkbox" name="auth_create_gBitDbUser" id="auth_create_gBitDbUser" {if $auth_create_gBitDbUser eq 'y'}checked="checked"{/if} />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Create user if not in Auth" for="auth_create_user_auth"}
				{forminput}
					<input type="checkbox" name="auth_create_user_auth" id="auth_create_user_auth" {if $auth_create_user_auth eq 'y'}checked="checked"{/if} />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Just use bitweaver auth for admin" for="auth_skip_admin"}
				{forminput}
					<input type="checkbox" name="auth_skip_admin" id="auth_skip_admin" {if $auth_skip_admin eq 'y'}checked="checked"{/if} />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="LDAP Host" for="auth_ldap_host"}
				{forminput}
					<input type="text" name="auth_ldap_host" id="auth_ldap_host" value="{$auth_ldap_host|escape}" size="50" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="LDAP Port" for="auth_ldap_port"}
				{forminput}
					<input type="text" name="auth_ldap_port" id="auth_ldap_port" value="{$auth_ldap_port|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="LDAP Scope" for="auth_ldap_scope"}
				{forminput}
					<select name="auth_ldap_scope" id="auth_ldap_scope">
						<option value="sub" {if $auth_ldap_scope eq "sub"} selected="selected"{/if}>sub</option>
						<option value="one" {if $auth_ldap_scope eq "one"} selected="selected"{/if}>one</option>
						<option value="base" {if $auth_ldap_scope eq "base"} selected="selected"{/if}>base</option>
					</select>
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="LDAP Base DN" for="auth_ldap_basedn"}
				{forminput}
					<input size="50" type="text" name="auth_ldap_basedn" id="auth_ldap_basedn" value="{$auth_ldap_basedn|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="LDAP User DN" for="auth_ldap_userdn"}
				{forminput}
					<input size="50" type="text" name="auth_ldap_userdn" id="auth_ldap_userdn" value="{$auth_ldap_userdn|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="LDAP User Attribute" for="auth_ldap_userattr"}
				{forminput}
					<input size="50" type="text" name="auth_ldap_userattr" id="auth_ldap_userattr" value="{$auth_ldap_userattr|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="LDAP User OC" for="auth_ldap_useroc"}
				{forminput}
					<input size="50" type="text" name="auth_ldap_useroc" id="auth_ldap_useroc" value="{$auth_ldap_useroc|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="LDAP Group DN" for="auth_ldap_groupdn"}
				{forminput}
					<input size="50" type="text" name="auth_ldap_groupdn" id="auth_ldap_groupdn" value="{$auth_ldap_groupdn|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="LDAP Group Atribute" for="auth_ldap_groupattr"}
				{forminput}
					<input size="50" type="text" name="auth_ldap_groupattr" id="auth_ldap_groupattr" value="{$auth_ldap_groupattr|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="LDAP Group OC" for="auth_ldap_groupoc"}
				{forminput}
					<input size="50" type="text" name="auth_ldap_groupoc" id="auth_ldap_groupoc" value="{$auth_ldap_groupoc|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="LDAP Member Attribute" for="auth_ldap_memberattr"}
				{forminput}
					<input size="50" type="text" name="auth_ldap_memberattr" id="auth_ldap_memberattr" value="{$auth_ldap_memberattr|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="LDAP Member Is DN" for="auth_ldap_memberisdn"}
				{forminput}
					<input size="50" type="text" name="auth_ldap_memberisdn" id="auth_ldap_memberisdn" value="{$auth_ldap_memberisdn|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="LDAP Admin User" for="auth_ldap_adminuser"}
				{forminput}
					<input size="50" type="text" name="auth_ldap_adminuser" id="auth_ldap_adminuser" value="{$auth_ldap_adminuser|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="LDAP Admin Pwd" for="auth_ldap_adminpass"}
				{forminput}
					<input size="50" type="password" name="auth_ldap_adminpass" id="auth_ldap_adminpass" value="{$auth_ldap_adminpass|escape}" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" name="auth_pear" value="{tr}Change preferences{/tr}" />
			</div>
		{/form}
	{/jstab}
{/jstabs}
