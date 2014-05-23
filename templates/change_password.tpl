{strip}

<div class="display login">
	<div class="header">
		<h1>{tr}Change password enforced{/tr}</h1>
	</div>

	<div class="body">
		{form ipackage=users ifile="change_password.php" secure=$gBitSystem->isFeatureActive("site_https_login_required")}
			<input type="hidden" name="user_id" value="{$userInfo.user_id}" />
			{if $userInfo.provpass}
				<input type="hidden" name="provpass" value="{$userInfo.provpass|escape}" />
			{/if}

			<div class="control-group">
				{formlabel label="User" for="user"}
				{forminput}
					{$userInfo.login}
				{/forminput}
			</div>

			{if !$userInfo.provpass}
				<div class="control-group">
					{formlabel label="Old Password" for="oldpass"}
					{forminput}
						<input type="password" name="oldpass" id="oldpass" />
					{/forminput}
				</div>
			{/if}

			<div class="control-group">
				{formlabel label="New Password" for="pass"}
				{forminput}
					<input type="password" name="pass" id="pass" />
				{/forminput}
			</div>

			<div class="control-group">
				{formlabel label="Again Please" for="pass2"}
				{forminput}
					<input type="password" name="pass2" id="pass2" />
				{/forminput}
			</div>

			<div class="submit">
				<input type="submit" class="btn btn-default" name="change" value="{tr}Change Password{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .login -->
{/strip}
