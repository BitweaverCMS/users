{strip}
{form legend="User Settings"}
	<input type="hidden" name="page" value="{$page}" />
	{foreach from=$formFeatures key=feature item=output}
		<div class="row">
			{formlabel label=`$output.label` for=$feature}
			{forminput}
				{html_checkboxes name="settings[$feature]" values="y" checked=`$gBitSystemPrefs.$feature` labels=false id=$feature}
				{formhelp note=`$output.note` page=`$output.link`}
			{/forminput}
		</div>
	{/foreach}

	<div class="row">
		{formlabel label="Display" for="display_name"}
		{forminput}
			<select name="settings[display_name]" id="display_name">
				<option value="real_name" {if $gBitSystemPrefs.display_name eq 'real_name'}selected="selected"{/if}>{tr}Real Name{/tr}</option>
				<option value="login" {if $gBitSystemPrefs.display_name eq 'login'}selected="selected"{/if}>{tr}Login / Nick Name{/tr}</option>
			</select>
			{formhelp note="Decide what name should be displayed throughout your site, login name or real name"}
		{/forminput}
	</div>

	<div class="row">
		{formlabel label="Users Can Customize Their Layout" for="feature_user_layout"}
		{forminput}
			<select name="settings[feature_user_layout]" id="feature_user_layout">
				<option value="">Never</option>
				<option value="h" {if $gBitSystemPrefs.feature_user_layout eq 'h'}selected="selected"{/if}>{tr}Just For Their Homepage{/tr}</option>
			</select>
			{formhelp note="Allows users to position and display their own set of modules" page="UsersConfigureModules"}
		{/forminput}
	</div>

	<div class="row">
		{formlabel label="Users Can Change Their Theme" for="feature_user_theme"}
		{forminput}
			<select name="settings[feature_user_theme]" id="feature_user_theme">
				<option value="">Never</option>
				<option value="h" {if $gBitSystemPrefs.feature_user_theme eq 'h'}selected="selected"{/if}>{tr}Just For Their Homepage{/tr}</option>
				<option value="y" {if $gBitSystem->isFeatureActive( 'feature_user_theme' )}selected="selected"{/if}>{tr}For the Entire Site{/tr}</option>
			</select>
			{formhelp note="Allows users to choose their own theme." page="UserTheme"}
		{/forminput}
	</div>

	<div class="row">
		{formlabel label="Custom User Fields" for="custom_user_fields"}
		{forminput}
			<textarea name="settings[custom_user_fields]" id="custom_user_fields" cols="50" rows="2">{$gBitSystemPrefs.custom_user_fields}</textarea>
			{formhelp note="Comma separated list of field names for custom user registration (maximum of 250 characters in total)." }
		{/forminput}
	</div>


	<div class="row submit">
		<input type="submit" name="features" value="{tr}Change preferences{/tr}" />
	</div>
{/form}
{/strip}
