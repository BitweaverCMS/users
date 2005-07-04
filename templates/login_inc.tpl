{strip}
<form name="loginbox" action="{$gBitLoc.USERS_PKG_URL}validate.php" method="post" {if $gBitSystemPrefs.feature_challenge eq 'y'}onsubmit="doChallengeResponse()"{/if}>
	{if $gBitSystemPrefs.feature_challenge eq 'y'}
	{literal}
		<script language="javascript" type="text/javascript" src="lib/md5.js"></script>
		<script language="Javascript" type="text/javascript">
			<!--
			function doChallengeResponse() {
				hashstr = document.loginbox.user.value +
				document.loginbox.pass.value +
				document.loginbox.email.value;
				str = document.loginbox.user.value +
				MD5(hashstr) +
				document.loginbox.challenge.value;
				document.loginbox.response.value = MD5(str);
				document.loginbox.pass.value='';
				/*
				document.login.password.value = "";
				document.logintrue.username.value = document.login.username.value;
				document.logintrue.response.value = MD5(str);
				document.logintrue.submit();
				*/
				document.loginbox.submit();
				return false;
			}
			// -->
		</script>
	{/literal}
	<input type="hidden" name="challenge" value="{$challenge|escape}" />
	<input type="hidden" name="response" value="" />
	{/if}

	{legend legend="Sign in with your username or email to continue"}
		<div class="row">
			{formfeedback success=$msg.success}
			{formfeedback error="$error"}
			{formlabel label="Username or Email" for="user"}
			{forminput}
				<input type="text" name="user" id="user" size="25" />
				{formhelp note=""}
			{/forminput}
		</div>

		{if $gBitSystemPrefs.feature_challenge eq 'y'}
			<div class="row">
				{formlabel label="email" for="email"}
				{forminput}
					<input type="text" name="email" id="email" size="25" />
					{formhelp note=""}
				{/forminput}
			</div>
		{/if}

		<div class="row">
			{formlabel label="Password" for="pass"}
			{forminput}
				<input type="password" name="pass" id="pass" size="25" />
			{/forminput}
		</div>

		{if $gBitSystem->isFeatureActive('rememberme')}
			<div class="row">
				{formlabel label="Remember me" for="rme"}
				{forminput}
					<input type="checkbox" name="rme" id="rme" value="on" checked=checked/>
					{formhelp note=""}
				{/forminput}
			</div>
		{/if}

		{if $http_login_url ne '' or $https_login_url ne ''}
			<div class="row">
				{formlabel label="" for=""}
				{forminput}
					<a href="{$http_login_url}" title="{tr}Click here to login using the default security protocol{/tr}">{tr}standard{/tr}</a> |
					<a href="{$https_login_url}" title="{tr}Click here to login using a secure protocol{/tr}">{tr}secure{/tr}</a>
					{formhelp note=""}
				{/forminput}
			</div>
		{/if}

		{if $show_stay_in_ssl_mode eq 'y'}
			<div class="row">
				{formlabel label="stay in ssl mode" for="stay_in_ssl_mode"}
				{forminput}
					<input type="checkbox" name="stay_in_ssl_mode" id="stay_in_ssl_mode" {if $stay_in_ssl_mode eq 'y'}checked="checked"{/if} />
					{formhelp note=""}
				{/forminput}
			</div>
		{else}
			<input type="hidden" name="stay_in_ssl_mode" value="{$stay_in_ssl_mode|escape}" />
		{/if}

		<div class="row submit">
			<input type="submit" name="login" value="{tr}Log in to {$siteName|default:"this site"}{/tr}" />
		</div>
	{/legend}
</form>
{/strip}
