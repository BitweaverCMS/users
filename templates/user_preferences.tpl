{strip}

<div class="floaticon">{bithelp}</div>
<div class="display userpreferences">
	<div class="header">
		<h1>{tr}User Preferences{/tr}</h1>
	</div>

	{include file="bitpackage:users/my_bitweaver_bar.tpl"}

	<div class="body">
		{formfeedback warning=$warningMsg success=$successMsg error=$errorMsg}
		{jstabs}
			{jstab title="User Information"}
				{form legend="User Information"}
					<input type="hidden" name="view_user" value="{$editUser.user_id}" />

					<div class="row">
						{formlabel label="Real Name" for="real_name"}
						{forminput}
							<input type="text" name="real_name" id="real_name" value="{$editUser.real_name|escape}" />
							{if !$gBitSystem->getConfig('users_display_name') or $gBitSystem->getConfig('users_display_name') eq 'real_name'}
								{formhelp note="This is the name that is visible to other users when viewing information added by you."}
							{/if}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Username"}
						{forminput}
							{$editUser.login}
							{if $gBitSystem->getConfig('users_display_name') eq 'login'}
								{formhelp note="This is the name that is visible to other users when viewing information added by you."}
							{/if}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Last login"}
						{forminput}
							{$editUser.last_login|bit_long_datetime}
							{formhelp note=""}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Is email public?" for="users_email_display"}
						{forminput}
							<select name="users_email_display" id="users_email_display">
								{section name=ix loop=$scramblingMethods}
									<option value="{$scramblingMethods[ix]|escape}" {if $users_email_display eq $scramblingMethods[ix]}selected="selected"{/if}>{$scramblingEmails[ix]}</option>
								{/section}
							</select>
							{formhelp note="Pick the scrambling method to prevent spam."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Country" for="country"}
						{forminput}
							{if $userPrefs.flag}{biticon iforce=icon ipackage="users" ipath="flags/" iname="`$userPrefs.flag`" iexplain="`$userPrefs.flag`"}{/if}
							<select name="users_country" id="country">
								<option value="" />
								{sortlinks}
									{section name=ix loop=$flags}
										<option value="{$flags[ix]|escape}" {if $userPrefs.flag eq $flags[ix]}selected="selected"{/if}>{tr}{$flags[ix]|replace:'_':' '}{/tr}</option>
									{/section}
								{/sortlinks}
							</select>
							{formhelp note=""}
						{/forminput}
					</div>

					{if $users_change_language eq 'y'}
						<div class="row">
							{formlabel label="Language" for="language"}
							{forminput}
								<select name="language" id="language">
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

					{foreach from=$customFields key=i item=field}
						<div class="row">
							{formlabel label="$field}
							{forminput}
								<input type="text" name="CUSTOM[{$field}]" value="{$userPrefs.$field}" maxlength="250" />
							{/forminput}
						</div>
					{/foreach}

					<div class="row">
						{formlabel label="User information" for="users_information"}
						{forminput}
							<select name="users_information" id="users_information">
								<option value="public" {if $userPrefs.users_information eq 'public'}selected="selected"{/if}>{tr}public{/tr}</option>
								<option value="private" {if $userPrefs.users_information eq 'private'}selected="selected"{/if}>{tr}private{/tr}</option>
							</select>
							{formhelp note=""}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Number of visited pages to remember" for="users_bread_crumb"}
						{forminput}
							<select name="users_bread_crumb" id="users_bread_crumb">
								<option value="1" {if $editUser.users_bread_crumb eq 1}selected="selected"{/if}>{tr}1{/tr}</option>
								<option value="2" {if $editUser.users_bread_crumb eq 2}selected="selected"{/if}>{tr}2{/tr}</option>
								<option value="3" {if $editUser.users_bread_crumb eq 3}selected="selected"{/if}>{tr}3{/tr}</option>
								<option value="4" {if $editUser.users_bread_crumb eq 4}selected="selected"{/if}>{tr}4{/tr}</option>
								<option value="5" {if $editUser.users_bread_crumb eq 5}selected="selected"{/if}>{tr}5{/tr}</option>
								<option value="10" {if $editUser.users_bread_crumb eq 10}selected="selected"{/if}>{tr}10{/tr}</option>
							</select>
							{formhelp note=""}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="HomePage" for="users_homepage"}
						{forminput}
							<input size="50" type="text" name="users_homepage" id="users_homepage" value="{$editUser.users_homepage|escape}" />
							{formhelp note="If you have a personal or professional homepage, enter it here."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Displayed time zone"}
						{forminput}
							<label><input type="radio" name="site_display_timezone" value="UTC" {if $site_display_timezone eq 'UTC'}checked="checked"{/if} />{tr}UTC{/tr}</label>
							<br />
							<label><input type="radio" name="site_display_timezone" value="Local" {if $site_display_timezone ne 'UTC'}checked="checked"{/if} />{tr}Local{/tr}</label>
							{formhelp note=""}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Use double-click to edit pages" for="users_double_click"}
						{forminput}
							<input type="checkbox" name="users_double_click" id="users_double_click" {if $userPrefs.users_double_click eq 'y'}checked="checked"{/if} />
							{formhelp note="Enabling this feature will allow you to double click on any wiki page and it will automatically take you to the edit page. Note that this does not work in all browsers."}
						{/forminput}
					</div>

					<div class="row submit">
						<input type="submit" name="prefs" value="{tr}Change preferences{/tr}" />
					</div>
				{/form}

				{form legend="Change your email address"}
					<input type="hidden" name="view_user" value="{$editUser.user_id}" />
					<div class="row">
						{formlabel label="Email" for="email"}
						{forminput}
							<input size="50" type="text" name="email" id="email" value="{$editUser.email|escape}" />
							{formhelp note=""}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Password" for="pass"}
						{forminput}
							<input type="password" name="pass" id="pass" />
							{formhelp note=""}
						{/forminput}
					</div>

					<div class="row submit">
						<input type="submit" name="chgemail" value="{tr}Change email{/tr}" />
					</div>
				{/form}

				{form legend="Change your password"}
					<input type="hidden" name="view_user" value="{$editUser.user_id}" />

					{if !$view_user or ( $gBitUser->hasPermission('p_users_admin') and $view_user )}
						<div class="row">
							{formlabel label="Old password" for="old"}
							{forminput}
								<input type="password" name="old" id="old" />
								{formhelp note=""}
							{/forminput}
						</div>
					{else}
						<input type="hidden" name="old" value="" />
					{/if}

					<div class="row">
						{formlabel label="New password" for="pass1"}
						{forminput}
							<input type="password" name="pass1" id="pass1" />
							{formhelp note=""}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Again please" for="pass2"}
						{forminput}
							<input type="password" name="pass2" id="pass2" />
							{formhelp note=""}
						{/forminput}
					</div>

					<div class="row submit">
						<input type="submit" name="chgpswd" value="{tr}Change Password{/tr}" />
					</div>
				{/form}

				{if $gBitSystem->isFeatureActive( 'feature_tasks' )}
					{form legend="User Tasks"}
						<div class="row">
							{formlabel label="Tasks per page" for="tasks_max_records"}
							{forminput}
								<select name="tasks_max_records" id="tasks_max_records">
									<option value="2"  {if $userPrefs.tasks_max_records eq 2}selected="selected"{/if}>{tr}2{/tr}</option>
									<option value="5"  {if $userPrefs.tasks_max_records eq 5}selected="selected"{/if}>{tr}5{/tr}</option>
									<option value="10" {if $userPrefs.tasks_max_records eq 10}selected="selected"{/if}>{tr}10{/tr}</option>
									<option value="20" {if $userPrefs.tasks_max_records eq 20}selected="selected"{/if}>{tr}20{/tr}</option>
									<option value="30" {if $userPrefs.tasks_max_records eq 30}selected="selected"{/if}>{tr}30{/tr}</option>
									<option value="40" {if $userPrefs.tasks_max_records eq 40}selected="selected"{/if}>{tr}40{/tr}</option>
									<option value="50" {if $userPrefs.tasks_max_records eq 50}selected="selected"{/if}>{tr}50{/tr}</option>
								</select>
								{formhelp note=""}
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Use dates" for="tasks_use_dates"}
							{forminput}
								<input type="checkbox" name="tasks_use_dates" id="tasks_use_dates" {if $tasks_use_dates eq 'y'}checked="checked"{/if} />
								{formhelp note=""}
							{/forminput}
						</div>

						<div class="row submit">
							<input type="submit" name="tasksprefs" value="{tr}Change preferences{/tr}" />
						</div>
					{/form}
				{/if}
			{/jstab}

			{jstab title="Pictures and Icons"}
				{legend legend="Pictures and Icons"}
					<div class="row">
						{formlabel label="Pictures"}
						{forminput}
							<a href="{$smarty.const.USERS_PKG_URL}my_images.php">{tr}Upload new pictures{/tr}</a>
							{formhelp note=""}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Avatar"}
						{forminput}
							{if $editUser.avatar_url}
								<img src="{$editUser.avatar_url}" />
							{/if}
							{formhelp note="Small icon used for your posts or comments."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Self Portrait"} {forminput}
							{if $editUser.portrait_url}
								<img src="{$editUser.portrait_url}" />
							{/if}
							{formhelp note="Larger picture used on your bio page."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Logo" for=""}
						{forminput}
							{if $editUser.logo_url}
								<img src="{$editUser.logo_url}" /><br />
							{/if}
							{formhelp note="Image used for your organization."}
						{/forminput}
					</div>
				{/legend}
			{/jstab}
			
			{foreach item=package from=$packages}
				{jstab title=$package.name}
					{include file=$package.template settings=$userPrefs}
				{/jstab}
			{/foreach}
			
		{/jstabs}
	</div><!-- end .body -->
</div><!-- end .userpreferences -->

{/strip}
