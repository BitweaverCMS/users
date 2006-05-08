{strip}
<div class="display login">
	<div class="header">
		<h1>{tr}Retrieve Password{/tr}</h1>
	</div>

	<div class="body">
		{if $msg}
			{formfeedback hash=$msg}
			{include file="bitpackage:users/login.tpl"}
		{else}
			{form legend="Please send me my password"}
				<div class="row">
					{formfeedback warning=$msg.error}
					{formlabel label="Username or email" for="username"}
					{forminput}
						<input type="text" name="username" id="username" />
					{/forminput}
				</div>

				<div class="row submit">
					<input type="submit" name="remind" id="remind" value="{tr}Send me my password{/tr}" />
				</div>
			{/form}
		{/if}
	</div><!-- end .body -->
</div><!-- end .login -->
{/strip}
