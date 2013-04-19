{strip}
<div class="span4">
{form class="form-horizontal" name="login" legend="Please sign in to continue" ipackage=users ifile='validate.php' secure=$gBitSystem->isFeatureActive("site_https_login_required")}
	<div class="control-group">
		{formfeedback error="$error"}
		{formlabel label="Username or Email" for="user"}
		{forminput}
			<input type="text" name="user" id="user" size="25" />
			{formhelp note=""}
		{/forminput}
	</div>

	<div class="control-group">
		{formlabel label="Password" for="pass"}
		{forminput}
			<input type="password" name="pass" id="pass" size="25" />
			{if $gBitSystem->isFeatureActive('users_forgot_pass')}
				{formhelp note="<a href='`$smarty.const.USERS_PKG_URL`remind_password.php'>Forgot your password?</a>"}
			{/if}
		{/forminput}
	</div>

	{if $gBitSystem->isFeatureActive('users_remember_me')}
		<div class="control-group">
			{forminput}
				<input type="checkbox" name="rme" id="rme" value="on" checked="checked" />
				{tr}Remember Me{/tr}
			{/forminput}
		</div>
	{/if}

	
	{if !$gBitSystem->getConfig('site_https_login_required') && $gBitSystem->isFeatureActive('http_login_url') or $gBitSystem->isFeatureActive('https_login_url')}
		<div class="control-group">
			{formlabel label="" for=""}
			{forminput}
				<a href="{$gBitSystem->getConfig('http_login_url')}" title="{tr}Login using the default security protocol{/tr}">{tr}Standard{/tr}</a> | <a href="{$gBitSystem->getConfig('https_login_url')}" title="{tr}Login using a secure protocol{/tr}">{tr}Secure{/tr}</a>
			{/forminput}
		</div>
	{/if}

	{if $smarty.server.HTTPS == 'on'}
		<div class="control-group">
			{forminput}
				<input type="checkbox" name="stay_in_ssl_mode" id="stay_in_ssl_mode" {if $smarty.server.HTTPS == 'on'}checked="checked"{/if} />{tr}Stay in Secure mode{/tr}
			{/forminput}
		</div>
	{else}
		<input type="hidden" name="stay_in_ssl_mode" value="on" />
	{/if}

	<div class="control-group submit">
		{forminput}
			<input type="submit" class="btn btn-primary" name="login" value="{tr}Sign In{/tr}" />
			{if $gBitSystem->isFeatureActive('site_https_login_required') || $smarty.server.HTTPS=='on'} {booticon iname="icon-lock" ipackage="icons" iexplain="Secure Login" class="icon-large"}{/if}
		{/forminput}
	</div>
{/form}
</div>
<script type="text/javascript">
     document.getElementById("user").focus();
</script>
{/strip}
