{strip}

{if $gBitSystem->isFeatureActive('users_clear_passwords')}
	{assign var=passVerb value='Send me'}
{else}
	{assign var=passVerb value='Reset'}
{/if}
<div class="display login">
	<div class="header">
		<h1>{tr}Forgot Password{/tr}</h1>
	</div>

	<div class="body">
		{if $msg}
			{formfeedback hash=$msg}
			{tr}Please follow the instructions in the email.{/tr}
		{else}
			{form legend="`$passVerb` my password"}
				<div class="control-group">
					{formfeedback warning=$msg.error}
					{formlabel label="Username or email" for="username"}
					{forminput}
						<input type="text" name="username" id="username" value="{$smarty.request.username}"/>
					{/forminput}
				</div>

				<div class="control-group submit">
					<input type="submit" class="btn btn-default" name="remind" id="remind" value="{tr}{$passVerb}{/tr} ({tr}password{/tr})" />
				</div>
			{/form}
		{/if}
	</div><!-- end .body -->
</div><!-- end .login -->
{/strip}
