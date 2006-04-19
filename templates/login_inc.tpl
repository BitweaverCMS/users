{strip}
{assign var=force_secure value=$gBitSystem->isFeatureActive("site_https_login_required")}
{form legend="Sign in with your username or email to continue" ipackage=users ifile='validate.php' secure=$force_secure}
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
{/form}
{/strip}
