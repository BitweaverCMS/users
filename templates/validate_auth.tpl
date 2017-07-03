{strip}
<div class="display login">
	<div class="header">
		<h1>{tr}Connect Social Account{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback error=$authError}
		{if $authProfile}
		<div class="row">	
			<div class="col-xs-12 col-sm-8 col-md-6">
			{form}
			<input type="hidden" name="provider" value="{$smarty.request.provider}">
			<div class="well">
				<div class="row">
					{if $authProfile->photoURL}<div class="col-xs-4"><img class="img-responsive img-thumbnail" src="{$authProfile->photoURL}"></div>{/if}
					<div class="col-xs-8">
{$gBitHybridAuthManager->getProviderFile}
					{if $authProfile->displayName}<h2 class="no-margin">{$authProfile->displayName}</h2>{/if}
					{if $authProfile->displayName != "`$authProfile->firstName` `$authProfile->lastName`"}{$authProfile->firstName} {$authProfile->lastName}{/if}
					{if $authProfile->email}{$authProfile->email}{/if}
					</div>
				</div>
			</div>

		<p class="alert alert-success">{booticon iname="icon-check"} {tr}Congratulations you have authenticated this account!{/tr}</p>

			<p>{tr}We do not have an existing user for this account from{/tr} {$smarty.request.provider}.</p> <p>{tr}Have you previously logged into{/tr} {$gBitSystem->getSiteTitle()}? <span class="btn btn-info btn-sm" onclick="BitBase.showById('auth-local-login');BitBase.hideById('auth-local-new');">{tr}Yes{/tr}</span> <span class="btn btn-info btn-sm" onclick="BitBase.showById('auth-local-new');BitBase.hideById('auth-local-login');">{tr}No{/tr}</span></p>

			<div style="display:none" id="auth-local-login">
				<p>Great! Please enter your email and password used to previously login and we will connect your accounts.</p>
				<div class="form-group">
					<input name="user" value="" class="form-control" placeholder="{tr}Email{/tr}">
				</div>
				<div class="form-group">
					<input type="password" name="pass" value="" class="form-control" placeholder="{tr}Password{/tr}">
				</div>
				<input type="submit" name="auth_login" class="btn btn-primary" value="{tr}Continue{/tr}">
			</div>

			{if $authProfile->email}
			<div style="display:none" id="auth-local-new">
				<p>Ok, no problem! Confirm your email below and we will connect the account above.</p>
				<div class="form-group">
					<label>We will create a new account using this email:</label>
					<div class="alert alert-info">{$authProfile->email}</div>
				</div>
			<div class="form-group">
				{formfeedback error=$errors.password}
				{formlabel label="Password" for="pass"}
				{forminput}
					<input class="form-control" type="password" id="pass1" name="password" required />
					{formhelp note="This password will be used to confirm account changes and recovery."}
				{/forminput}
			</div>

			<div class="form-group">
				{formfeedback error=$errors.password2}
				{formlabel label="Confirm Password" for="password2"}
				{forminput}
					<input class="form-control" type="password" id="password2" name="password2" required />
				{/forminput}
			</div>
				<input type="submit" name="auth_new" class="btn btn-primary" value="{tr}Continue{/tr}">
			</div>
			{/if}
	{*$authProfile|vd*}
			{/form}
		</div>
		{/if}
	</div><!-- end .body -->
</div><!-- end .login -->
{/strip}

