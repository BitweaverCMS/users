{strip}

<div class="display login">
	<div class="header">
		<h1>{tr}Register as a new user{/tr}</h1>
		{if $showmsg eq 'y'}<p>{$msg}</p>{/if}
	</div>

{if $showmsg ne 'y'}
	<div class="body">
		<p>{tr}If you are already registered, please{/tr} <a href="{$smarty.const.USERS_PKG_URL}login.php">{tr}login{/tr}</a></p>
		{form enctype="multipart/form-data" legend="Please fill in the following details"}
			{foreach from=$reg.CUSTOM item='custom' key='custom_name'}
				<input type="hidden" name="CUSTOM[{$custom_name}]" value="{$custom}"/>
			{/foreach}
			{foreach from=$reg.auth item='auth' key='auth_name'}
				<input type="hidden" name="auth[{$auth_name}]" value="{$auth}"/>
			{/foreach}
			{formfeedback error=$errors.create}
			{if $notrecognized eq 'y'}
				<input type="hidden" name="login" value="{$reg.login}"/>
				<input type="hidden" name="password" value="{$reg.password}"/>
				<input type="hidden" name="novalidation" value="yes"/>

				<div class="row">
					{formfeedback error=$errors.validate}
					{formlabel label="Username" for="email"}
					{forminput}
						<input type="text" size="50" name="email" id="email" value="{$reg.email}"/>
					{/forminput}
				</div>

				<div class="row submit">
					<input type="submit" name="register" value="{tr}register{/tr}" />
				</div>
			{elseif $showmsg ne 'y'}
				{if $gBitSystem->isFeatureActive('users_register_passcode')}
					<div class="row">
						{formfeedback error=$errors.passcode}
						{formlabel label="Passcode to register" for="passcode"}
						{forminput}
							<input type="password" name="passcode" id="passcode" />{required}
							{formhelp note="This is not your user password. It is a code required for registration. Contact your site administrator for details."}
						{/forminput}
					</div>
				{/if}

				{if $gBitSystem->isFeatureActive( 'reg_real_name' )}
					<div class="row">
						{formlabel label="Real name" for="real_name"}
						{forminput}
							<input type="text" name="real_name" id="real_name" value="{$smarty.request.real_name}" />
						{/forminput}
					</div>
				{/if}

				<div class="row">
					{formfeedback error=$errors.login}
					{formlabel label="Username" for="login"}
					{forminput}
						<input type="text" name="login" id="login" value="{$reg.login}" />{required}
						{formhelp note="Your username can only contain numbers, characters, and underscores."}
					{/forminput}
				</div>

				{if $gBitSystem->isFeatureActive( 'users_validate_user' )}
					{formfeedback warning="{tr}A confirmation email will be sent to you with instructions on how to login{/tr}"}
				{/if}

				<div class="row">
					{formfeedback error=$errors.email}
					{formlabel label="Email" for="email"}
					{forminput}
						<input type="text" size="50" name="email" id="email" value="{$reg.email}" />{required}
					{/forminput}
				</div>

				{if !$gBitSystem->isFeatureActive( 'users_validate_user' )}
					<div class="row">
						{formfeedback error=$errors.password}
						{formlabel label="Password" for="pass"}
						{forminput}
							<input id="pass1" type="password" name="password" />{required}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Repeat password" for="password2"}
						{forminput}
							<input id="password2" type="password" name="password2" />{required}
						{/forminput}
					</div>

					{if $gBitSystem->isFeatureActive( 'user_password_generator' )}
						<div class="row">
							{formlabel label="<a href=\"javascript:genPass('genepass','pass1','pass2');\">{tr}Generate a password{/tr}</a>" for="email"}
							{forminput}
								<input id="genepass" type="text" />
								{formhelp note="You can use this link to create a random password. Make sure you make a note of it somewhere to log in to this site in the future."}
							{/forminput}
						</div>
					{/if}
				{/if}
				{if $gBitUser->hasPermission( 'p_users_view_user_homepage' ) }
				{*For public sites, get user approval to display information*}
				<div class="row">
					{formlabel label="User information" for="users_information"}
					{forminput}
						<select name="users_information" id="users_information">
							<option value="public" selected="selected">{tr}public{/tr}</option>
							<option value="private">{tr}private{/tr}</option>
						</select>
						{formhelp note="Please select whether you would like to be a public or private user (you can change this later)"}
					{/forminput}
				</div>
				{/if}
				{if $gBitSystem->isFeatureActive( 'reg_real_name' ) or $gBitSystem->isFeatureActive( 'reg_homepage' ) or $gBitSystem->isFeatureActive( 'reg_country' ) or $gBitSystem->isFeatureActive( 'reg_language' ) or $gBitSystem->isFeatureActive( 'reg_portrait' )}
					{if $gBitSystem->isFeatureActive( 'reg_homepage' )}
						<div class="row">
							{formlabel label="HomePage" for="users_homepage"}
							{forminput}
								<input size="50" type="text" name="prefs[users_homepage]" id="users_homepage" value="{$smarty.request.prefs.users_homepage}" />
								{formhelp note="If you have a personal or professional homepage, enter it here."}
							{/forminput}
						</div>
					{/if}

					{if $gBitSystem->isFeatureActive( 'reg_country' )}
						<div class="row">
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
						<div class="row">
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
						<div class="row">
							{formlabel label="Self Portrait" for="user_portrait_file"}
							{forminput}
								<input name="user_portrait_file" id="user_portrait_file" type="file" />
								{formhelp note="Upload a personal photo to be displayed on your personal page."}
							{/forminput}
						</div>
					{/if}
				{/if}

				{section name=f loop=$customFields}
					<div class="row">
						{formlabel label="$customFields[f]}
						{forminput}
							<input type="text" name="CUSTOM[{$customFields[f]|escape}]" value="{$smarty.request.CUSTOM.$customFields[f]}" />
						{/forminput}
					</div>
				{/section}

				{foreach from=$auth_reg_fields item='output' key='op_id'}
				{assign var=op_name value="auth[$op_id]"}
					<div class="row">
						{formlabel label=$output.label for=$op_id}
						{forminput}
							{if $output.type == 'checkbox'}
								{html_checkboxes name="$op_name" values="y" selected=$output.value labels=false id=$op_id}
							{elseif $output.type == 'option'}
								<select name="{$op_name}" id="{$op_id}">
									{foreach from=$output.options item='op_text' key='op_value'}
										<option value="{$op_value}" {if $output.value eq $op_value} selected="selected"{/if}>{$op_text}</option>
									{/foreach}
								</select>
							{else}
								<input type="text" size="50" name="{$op_name}" id="{$op_id}" value="{$output.value|escape}" />
							{/if}
							{formhelp note=`$output.note` page=`$output.page` link=`$output.link`}
						{/forminput}
					</div>
				{/foreach}

				{if $gBitSystem->isFeatureActive('users_random_number_reg')}
					<hr />
					{formfeedback error=$errors.captcha}
					{captcha force=true variant=row}
				{/if}

				{if $groupList}
					<hr />
					{formlabel label="Group" for="group"}
					{forminput}
						{foreach item=gr from=$groupList name=group}
							<input type="radio" name="group" value="{$gr.group_id|escape}"{if ($reg.group eq '' and $smarty.foreach.group.last) or $reg.group eq $gr.group_id} checked="checked"{/if}>
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

				<div class="row submit">
					<input type="submit" name="register" value="{tr}Register{/tr}" />
				</div>

				{required legend=1}
			{/if}
		{/form}
	</div><!-- end .body -->

{/if}

</div><!-- end .login -->

{/strip}
