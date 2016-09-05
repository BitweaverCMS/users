{strip}

<div class="display login">
	<div class="header">
		<h1>{tr}Forgot Password{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback hash=$msg}
		{if $msg.success}
			<p>{tr}Please follow the instructions in the email.{/tr}</p>
			<a href="{$smarty.const.USERS_PKG_URL}signin.php" class="btn btn-default">{tr}Sign In{/tr}</a>
		{else}
			{form legend="Reset my password"}
				<div class="form-group">
					{formlabel label="Username or email" for="username"}
					{forminput}
						<input type="text" name="username" id="username" value="{$smarty.request.username}" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" class="form-control"/>
					{/forminput}
				</div>

				<div class="form-group submit">
					<input type="submit" class="btn btn-default" name="remind" id="remind" value="{tr}Send Reset Instructions{/tr}" />
				</div>
			{/form}
		{/if}
	</div><!-- end .body -->
</div><!-- end .login -->
{/strip}
