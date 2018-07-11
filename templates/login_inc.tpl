{strip}
{form class="col-md-6 col-xs-12 form-horizontal" name="login" legend="Please sign in to continue" ipackage=users ifile='validate' secure=$gBitSystem->isFeatureActive("site_https_login_required")}

{if $hybridProviders}
	<div class="form-group">
		{formlabel label="Sign in with" for="user"}
		{forminput}
			{foreach from=$hybridProviders key=providerKey item=providerHash}<a {if !$providerHash.image}class="btn btn-default"{/if} href="{$smarty.const.USERS_PKG_URL}validate?provider={$providerHash.provider}">{if $providerHash.image}<img src="{$providerHash.image}" alt="{tr}Sign in with {$providerHash.provider}{/tr}" style="max-height:40px">{else}{booticon iname=$providerHash.icon} {$providerHash.provider}{/if}</a> {/foreach}
			{formhelp note="Use one of the sites above to login. If you have previously logged in, we will connect your existing account."}
		{/forminput}
	</div>
	<hr>
{/if}

	{formfeedback error=$error}

	<div class="form-group">
		{formlabel label="Username or Email" for="user"}
		{forminput}
			<input class="form-control" type="text" name="user" id="user" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"/>
			{if $gBitSystem->isFeatureActive('users_allow_register')}
			{/if}
		{/forminput}
	</div>

	<div class="form-group">
		{formlabel label="Password" for="pass"}
		{forminput}
			<input class="form-control" type="password" name="pass" id="pass" />
			{if $gBitSystem->isFeatureActive('users_forgot_pass')}
				{formhelp note="<a href='`$smarty.const.USERS_PKG_URL`remind_password.php'>Forgot your password?</a> or <a href='`$smarty.const.USERS_PKG_URL`register.php'>Need to register?</a>"}
			{/if}
		{/forminput}
	</div>

	{if $gBitSystem->isFeatureActive('users_remember_me')}
		<div class="form-group">
			{forminput label="checkbox"}
				<input type="checkbox" name="rme" id="rme" value="on" checked="checked" />
				{tr}Remember Me{/tr}
			{/forminput}
		</div>
	{/if}

	
	{if !$gBitSystem->getConfig('site_https_login_required') && $gBitSystem->isFeatureActive('http_login_url') or $gBitSystem->isFeatureActive('https_login_url')}
		<div class="form-group">
			{forminput class="offset"}
				<a href="{$gBitSystem->getConfig('http_login_url')}" title="{tr}Login using the default security protocol{/tr}">{tr}Standard{/tr}</a> | <a href="{$gBitSystem->getConfig('https_login_url')}" title="{tr}Login using a secure protocol{/tr}">{tr}Secure{/tr}</a>
			{/forminput}
		</div>
	{/if}

	<div class="form-group">
		{forminput class="submit"}
			<input type="submit" class="btn btn-primary" name="login" value="{tr}Sign In{/tr}" />
			{if !$gBitSystem->isFeatureActive('site_https_login_required') && empty($smarty.server.HTTPS)} {booticon iname="icon-unlock" iexplain="Insecure" class="icon-large"}{/if}
		{/forminput}
	</div>
{/form}

<script type="text/javascript">
     document.getElementById("user").focus();
</script>
{/strip}
