{strip}

{form class="col-md-6 col-xs-12 form-horizontal" action="`$smarty.const.USERS_PKG_URL`register.php" enctype="multipart/form-data" legend="Register as a new user" secure=$gBitSystem->isFeatureActive("site_https_login_required")}
	{foreach from=$reg.CUSTOM item='custom' key='custom_name'}
		<input type="hidden" name="CUSTOM[{$custom_name}]" value="{$custom}"/>
	{/foreach}
	{foreach from=$reg.auth item='auth' key='auth_name'}
		<input type="hidden" name="auth[{$auth_name}]" value="{$auth}"/>
	{/foreach}
	{formfeedback error=$errors.create}
	{*if $hybridProviders}
		<div class="form-group">
			{formlabel label="Click to Register" for="user"}
			{forminput}
				{foreach from=$hybridProviders key=providerKey item=providerHash}<a class="btn btn-default" href="{$smarty.const.USERS_PKG_URL}validate?provider={$providerHash.provider}">{booticon iname=$providerHash.icon} {$providerHash.provider}</a> {/foreach}
				{formhelp note="1-Click registration with any of the sites above."}
			{/forminput}
		</div>
		<hr>
	{/if*}

	{if $gBitSystem->isFeatureActive('users_register_passcode')}
		<div class="form-group">
			{formfeedback error=$errors.passcode}
			{formlabel label="Passcode to register" for="passcode"}
			{forminput}
				<input class="form-control" type="password" name="passcode" value="{$reg.passcode}" id="passcode" required />
				{formhelp note="This is not your user password. It is a code required for registration. Contact your site administrator for details."}
			{/forminput}
		</div>
	{/if}

	{if $gBitSystem->isFeatureActive( 'reg_real_name' )}
		<div class="form-group">
			{formlabel label="Your Name" for="real_name"}
			{forminput}
				<input class="form-control" type="text" name="real_name" id="real_name" value="{$smarty.request.real_name}" />
				{formhelp note="This will be displayed in links to your information."}
			{/forminput}
		</div>
	{/if}

	<div class="form-group">
		{formfeedback error=$errors.login}
		{formlabel label="Username" for="login"}
		{forminput}
			<input class="form-control" type="text" name="login" id="login" value="{$reg.login}" onkeyup="BitUser.updateUserUrl();" required />
			{formhelp note="This will be used in links to your profile. Your username can only contain numbers, characters, and underscores."}
			<div class="alert alert-info nomargin" id="loginurl">{$smarty.const.BIT_ROOT_URI}</div>
		{/forminput}
		<script>/* <![CDATA[ */ {literal}
		BitUser = {
			"updateUserUrl": function(){
				var loginEle = document.getElementById('login');
				stripUsername = loginEle.value.replace( /[^_a-zA-Z0-9]/g, '' );
				if( stripUsername ) {
					var baseUrl = "{/literal}{$smarty.const.BIT_ROOT_URI}{literal}";
					document.getElementById('loginurl').innerHTML = baseUrl + stripUsername;
				}
			}
		};
		BitUser.updateUserUrl();
		{/literal} /* ]]> */</script>
	</div>

	{if $gBitSystem->isFeatureActive( 'users_validate_user' )}
		{formfeedback warning="{tr}A confirmation email will be sent to you with instructions on how to login{/tr}"}
	{/if}

	<div class="form-group">
		{formfeedback error=$errors.email}
		{formlabel label="Email" for="email"}
		{forminput}
			<input class="form-control" type="email" name="email" id="email" value="{$reg.email}" required  autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"/>
		{/forminput}
	</div>

	{if !$gBitSystem->isFeatureActive( 'users_validate_user' )}
		<div class="form-group">
			{formfeedback error=$errors.password}
			{formlabel label="Password" for="pass"}
			{forminput}
				<input class="form-control" type="password" id="pass1" name="password" required />
			{/forminput}
		</div>

		<div class="form-group">
			{formfeedback error=$errors.password2}
			{formlabel label="Repeat password" for="password2"}
			{forminput}
				<input class="form-control" type="password" id="password2" name="password2" required />
			{/forminput}
		</div>

		{if $gBitSystem->isFeatureActive( 'user_password_generator' )}
			<div class="form-group">
				{formlabel label="<a href=\"javascript:BitBase.genPass('genepass','pass1','pass2');\">{tr}Generate a password{/tr}</a>" for="email"}
				{forminput}
					<input id="genepass" type="text" />
					{formhelp note="You can use this link to create a random password. Make sure you make a note of it somewhere to log in to this site in the future."}
				{/forminput}
			</div>
		{/if}
	{/if}
	{if $gBitUser->hasPermission( 'p_users_view_user_homepage' ) }
	<div class="form-group">
		{formlabel label="User information" for="users_information"}
		{forminput}
			<select class="form-control" name="users_information" id="users_information">
				<option value="private" selected="selected">{tr}Private{/tr}</option>
				<option value="public">{tr}Public{/tr}</option>
			</select>
			{formhelp note="Please select whether you would like to be a public or private user (you can change this later)"}
		{/forminput}
	</div>
	{/if}
	{if $gBitSystem->isFeatureActive( 'reg_real_name' ) or $gBitSystem->isFeatureActive( 'reg_homepage' ) or $gBitSystem->isFeatureActive( 'reg_country' ) or $gBitSystem->isFeatureActive( 'reg_language' ) or $gBitSystem->isFeatureActive( 'reg_portrait' )}
		{if $gBitSystem->isFeatureActive( 'reg_homepage' )}
			<div class="form-group">
				{formlabel label="HomePage" for="users_homepage"}
				{forminput}
					<input class="form-control" type="text" name="prefs[users_homepage]" id="users_homepage" value="{$smarty.request.prefs.users_homepage}" />
					{formhelp note="If you have a personal or professional homepage, enter it here."}
				{/forminput}
			</div>
		{/if}

		{if $gBitSystem->isFeatureActive( 'reg_country' )}
			<div class="form-group">
				{formlabel label="Country" for="country"}
				{forminput}
					<select name="prefs[users_country]" id="country">
						<option value="" />
							{section name=ix loop=$flags}
								<option value="{$flags[ix]|escape}" {if $smarty.request.prefs.users_country eq $flags[ix]}selected="selected"{/if}>{tr}{$flags[ix]|replace:'_':' '}{/tr}</option>
							{/section}
					</select>
					{formhelp note=""}
				{/forminput}
			</div>
		{/if}

		{if $gBitSystem->isFeatureActive( 'reg_language' )}
			<div class="form-group">
				{formlabel label="Language" for="language"}
				{forminput}
					<select name="prefs[bitlanguage]" id="language">
						{foreach from=$languages key=langCode item=lang}
							<option value="{$langCode}"{if $gBitLanguage->mLanguage eq $langCode} selected="selected"{/if}>
								{$lang.full_name}
							</option>
						{/foreach}
					</select>
					{formhelp note="Pick your preferred site language."}
				{/forminput}
			</div>
		{/if}

		{if $gBitSystem->isFeatureActive( 'reg_portrait' )}
			<div class="form-group">
				{formlabel label="Profile Picture" for="user_portrait_file"}
				{forminput}
					<input type="file" class="form-control" name="user_portrait_file" id="user_portrait_file" />
					{formhelp note="Upload a personal photo to be displayed on your personal page."}
				{/forminput}
			</div>
		{/if}
	{/if}

	{section name=f loop=$customFields}
		<div class="form-group">
			{formlabel label=$customFields[f]}
			{forminput}
				<input class="form-control" type="text" name="CUSTOM[{$customFields[f]|escape}]" value="{$smarty.request.CUSTOM.$customFields[f]}" />
			{/forminput}
		</div>
	{/section}

	{foreach from=$auth_reg_fields item='output' key='op_id'}
	{assign var=op_name value="auth[`$op_id`]"}
		<div class="form-group">
			{formlabel label=$output.label for=$op_id}
			{forminput}
				{if $output.type == 'checkbox'}
					{forminput label="checkbox"}
						{html_checkboxes name="$op_name" values="y" selected=$output.value labels=false id=$op_id}
					{/forminput}
				{elseif $output.type == 'option'}
					<select name="{$op_name}" id="{$op_id}">
						{foreach from=$output.options item='op_text' key='op_value'}
							<option value="{$op_value}" {if $output.value eq $op_value} selected="selected"{/if}>{$op_text}</option>
						{/foreach}
					</select>
				{else}
					<input class="form-control" type="text" name="{$op_name}" id="{$op_id}" value="{$output.value|escape}" />
				{/if}
				{formhelp note=$output.note page=$output.page link=$output.link}
			{/forminput}
		</div>
	{/foreach}

	{if $groupList && count($groupList) > 1}
		<hr />
		{formlabel label="Group" for="group"}
		{forminput}
			{foreach item=gr from=$groupList name=group}
				<input class="form-control" type="radio" name="group" value="{$gr.group_id|escape}"{if ($reg.group eq '' and $smarty.foreach.group.last) or $reg.group eq $gr.group_id} checked="checked"{/if}>
					{if $gr.is_default eq "y"}
						{tr}None{/tr}
					{elseif $gr.group_desc}
						{$gr.group_desc}
					{else}
						{$gr.group_name}
					{/if}
				</input>
				{if !$smarty.foreach.group.last}<br />{/if}
			{/foreach}
			{formhelp note="Choose the group you belong to."}
		{/forminput}
	{/if}

	{foreach item=package from=$packages}
		{include file=$package.template }
	{/foreach}

	{captcha force=true variant=row}

	<div class="form-group">
		{formlabel label="" for=""}
		{forminput class="submit"}
			<input type="submit" class="btn btn-primary" name="register" value="{tr}Register{/tr}" />
		{/forminput}
	</div>
{/form}

{/strip}

