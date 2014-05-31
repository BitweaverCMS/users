{strip}
{form legend="User Settings"}
	<div class="control-group column-group gutters">
		{formlabel label="Display" for="users_display_name"}
		{forminput}
			<select name="settings[users_display_name]" id="users_display_name">
				<option value="real_name" {if $gBitSystem->getConfig('users_display_name') eq 'real_name'}selected="selected"{/if}>{tr}Real Name{/tr}</option>
				<option value="login" {if $gBitSystem->getConfig('users_display_name') eq 'login'}selected="selected"{/if}>{tr}Login / Nick Name{/tr}</option>
			</select>
			{formhelp note="Decide what name should be displayed throughout your site, login name or real name"}
		{/forminput}
	</div>

	<input type="hidden" name="page" value="{$page}" />
	{foreach from=$formFeatures key=feature item=output}
		<div class="control-group column-group gutters">
			{formlabel label=$output.label for=$feature}
			{forminput}
				{html_checkboxes name="settings[$feature]" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
				{formhelp note=$output.note page=$output.link}
			{/forminput}
		</div>
	{/foreach}

	<div class="control-group column-group gutters">
		{formlabel label="Users Can Customize Their Layout" for="users_layouts"}
		{forminput}
			<select name="settings[users_layouts]" id="users_layouts">
				<option value="">Never</option>
				<option value="h" {if $gBitSystem->getConfig('users_layouts') eq 'h'}selected="selected"{/if}>{tr}Just For Their Homepage{/tr}</option>
			</select>
			{formhelp note="Allows users to position and display their own set of modules" page="UsersConfigureModules"}
		{/forminput}
	</div>

	<div class="control-group column-group gutters">
		{formlabel label="Users Can Change Their Theme" for="users_themes"}
		{forminput}
			<select name="settings[users_themes]" id="users_themes">
				<option value="">Never</option>
				<option value="h" {if $gBitSystem->getConfig('users_themes') eq 'h'}selected="selected"{/if}>{tr}Just For Their Homepage{/tr}</option>
				<option value="y" {if $gBitSystem->isFeatureActive( 'users_themes' )}selected="selected"{/if}>{tr}For the Entire Site{/tr}</option>
			</select>
			{formhelp note="Allows users to choose their own theme." page="UserTheme"}
		{/forminput}
	</div>

	<div class="control-group column-group gutters">
		{formlabel label="Custom User Fields" for="custom_user_fields"}
		{forminput}
			<textarea name="settings[custom_user_fields]" id="custom_user_fields" cols="50" rows="2">{$gBitSystem->getConfig('custom_user_fields')}</textarea>
			{formhelp note="Comma separated list of field names for custom user registration (maximum of 250 characters in total)." }
		{/forminput}
	</div>


	<div class="control-group submit">
		<input type="submit" class="ink-button" name="features" value="{tr}Change preferences{/tr}" />
	</div>
{/form}
{/strip}
