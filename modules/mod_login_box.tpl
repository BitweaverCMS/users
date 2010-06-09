{* $Header$ *}
{strip}
{bitmodule title="$moduleTitle" name="login_box"}
	{if $gBitUser->IsRegistered()}
		{tr}Logged in as{/tr}: <strong>{displayname}</strong><br />
		<a href="{$smarty.const.USERS_PKG_URL}logout.php">{tr}Logout{/tr}</a>
		{if $gBitUser->hasPermission( 'p_users_admin' )}
		<div class="row">
			{form ipackage=users ifile="admin/index.php"}
				<input type="text" name="assume_user" value="{tr}Username{/tr}" id="assume_user" size="15" onblur="if (this.value == '') {ldelim}this.value = '{tr}Username{/tr}';{rdelim}" onfocus="if (this.value == '{tr}Username{/tr}') {ldelim}this.value = '';{rdelim}" /> <input type="submit" name="confirm" value="{tr}Assume{/tr}" />
			{/form}
		</div>
		{/if}
	{else}
		{assign var=force_secure value=$gBitSystem->isFeatureActive("site_https_login_required")}
		{form ipackage=users ifile='validate.php' secure=$force_secure}
			<div class="row">
				<input type="text" name="user" id="user" value="{tr}Username{/tr}" size="15" onblur="if (this.value == '') {ldelim}this.value = '{tr}Username{/tr}';{rdelim}" onfocus="if (this.value == '{tr}Username{/tr}') {ldelim}this.value = '';{rdelim}" />
			</div>

			<div class="row">
				<input type="password" name="pass" id="pass" value="password" size="15" onblur="if (this.value == '') {ldelim}this.value = 'password';{rdelim}" onfocus="if (this.value == 'password') {ldelim}this.value = '';{rdelim}" />
			</div>

			{if $gBitSystem->isFeatureActive('users_remember_me')}
				<div class="row">
					<label><input type="checkbox" name="rme" id="rme" value="on" checked="checked" /> {tr}Remember me{/tr}</label>
				</div>
			{/if}

			{if $gBitSystem->isFeatureActive('http_login_url') or $gBitSystem->isFeatureActive('https_login_url')}
				<div class="row">
					<a href="{$gBitSystem->getConfig('http_login_url')}" title="{tr}Click here to login using the default security protocol{/tr}">{tr}standard{/tr}</a> |
					<a href="{$gBitSystem->getConfig('https_login_url')}" title="{tr}Click here to login using a secure protocol{/tr}">{tr}secure{/tr}</a>
				</div>
			{/if}

			{if $smarty.server.HTTPS == 'on'}
				<div class="row">
					<label>{tr}Stay in ssl mode{/tr} <input type="checkbox" name="stay_in_ssl_mode" id="stay_in_ssl_mode" {if $smarty.server.HTTPS == 'on'}checked="checked"{/if} /></label>
				</div>
			{else}
				<input type="hidden" name="stay_in_ssl_mode" value="on" />
			{/if}

			<div class="row submit">
				<input type="submit" name="login" value="{tr}Log in{/tr}" />
			</div>

			<div class="row">
				{if $gBitSystem->isFeatureActive('users_allow_register')}
					<a title="{tr}Create your own user account{/tr}" href="{$smarty.const.USERS_PKG_URL}register.php">{tr}Register new account{/tr}</a>
				{/if}

				{if $gBitSystem->isFeatureActive('users_forgot_pass')}
					<br /><a title="{tr}Receive your password via email{/tr}" href="{$smarty.const.USERS_PKG_URL}remind_password.php">{tr}Reset password{/tr}</a>
				{/if}
			</div>
		{/form}
	{/if}
{/bitmodule}
{/strip}
