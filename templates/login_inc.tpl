{strip}
{assign var=force_secure value=$gBitSystem->isFeatureActive("site_https_login_required")}
{form name="login" legend="Sign in with your username or email to continue" ipackage=users ifile='validate.php' secure=$force_secure}
	<div class="row">
		{formfeedback error="$error"}
		{formlabel label="Username or Email" for="user"}
		{forminput}
			<input type="text" name="user" id="user" size="25" />
			{formhelp note=""}
		{/forminput}
	</div>

	<div class="row">
		{formlabel label="Password" for="pass"}
		{forminput}
			<input type="password" name="pass" id="pass" size="25" />
		{/forminput}
	</div>

	{if $gBitSystem->isFeatureActive('users_remember_me')}
		<div class="row">
			{formlabel label="Remember me" for="rme"}
			{forminput}
				<input type="checkbox" name="rme" id="rme" value="on" checked="checked" />
				{formhelp note=""}
			{/forminput}
		</div>
	{/if}

	
	{if !$gBitSystem->getConfig('site_https_login_required') && $gBitSystem->isFeatureActive('http_login_url') or $gBitSystem->isFeatureActive('https_login_url')}
		<div class="row">
			{formlabel label="" for=""}
			{forminput}
				<a href="{$gBitSystem->getConfig('http_login_url')}" title="{tr}Click here to login using the default security protocol{/tr}">{tr}Standard{/tr}</a> | <a href="{$gBitSystem->getConfig('https_login_url')}" title="{tr}Click here to login using a secure protocol{/tr}">{tr}Secure{/tr}</a>
				{formhelp note=""}
			{/forminput}
		</div>
	{/if}

	{if $smarty.server.HTTPS == 'on'}
		<div class="row">
			{formlabel label="stay in ssl mode" for="stay_in_ssl_mode"}
			{forminput}
				<input type="checkbox" name="stay_in_ssl_mode" id="stay_in_ssl_mode" {if $smarty.server.HTTPS == 'on'}checked="checked"{/if} />
				{formhelp note=""}
			{/forminput}
		</div>
	{else}
		<input type="hidden" name="stay_in_ssl_mode" value="on" />
	{/if}

	<div class="row submit">
		<input type="submit" name="login" value="{tr}Log in to {$gBitSystem->getConfig('site_title')|default:"this site"}{/tr}" />
		{if $gBitSystem->isFeatureActive('site_https_login_required') || $smarty.server.HTTPS=='on'}
			{biticon iname="emblem-readonly" ipackage="icons" iexplain="Secure Login"}
		{/if}
	</div>
{/form}
<script type="text/javascript">
     document.getElementById("user").focus();
</script>
{/strip}
