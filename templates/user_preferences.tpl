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
				{form}
					{legend legend="User Information"}
						<input type="hidden" name="view_user" value="{$editUser->mUserId}" />

						<div class="row">
							{formlabel label="Real Name" for="real_name"}
							{forminput}
								<input type="text" name="real_name" id="real_name" value="{$editUser->mInfo.real_name|escape}" />
								{if !$gBitSystem->getConfig('users_display_name') or $gBitSystem->getConfig('users_display_name') eq 'real_name'}
									{formhelp note="This is the name that is visible to other users when viewing information added by you."}
								{/if}
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Username"}
							{forminput}
								{$editUser->mInfo.login}
								{if $gBitSystem->getConfig('users_display_name') eq 'login'}
									{formhelp note="This is the name that is visible to other users when viewing information added by you."}
								{/if}
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Last login"}
							{forminput}
								{$editUser->mInfo.last_login|bit_long_datetime}
								{formhelp note=""}
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Is email public?" for="users_email_display"}
							{forminput}
								<select name="users_email_display" id="users_email_display">
									{section name=ix loop=$scramblingMethods}
										<option value="{$scramblingMethods[ix]|escape}" {if $editUser->mPrefs.users_email_display eq $scramblingMethods[ix]}selected="selected"{/if}>{$scramblingEmails[ix]}</option>
									{/section}
								</select>
								{formhelp note="Pick the scrambling method to prevent spam."}
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Country" for="country"}
							{forminput}
								{if $editUser->mPrefs.flag}{biticon iforce=icon ipackage=users ipath=flags/ iname=$editUser->mPrefs.flag iexplain=$editUser->mPrefs.flag}{/if}
								<select name="users_country" id="country">
									<option value="" />
									{sortlinks}
										{section name=ix loop=$flags}
											<option value="{$flags[ix]|escape}" {if $editUser->mPrefs.flag eq $flags[ix]}selected="selected"{/if}>{tr}{$flags[ix]|replace:'_':' '}{/tr}</option>
										{/section}
									{/sortlinks}
								</select>
								{formhelp note=""}
							{/forminput}
						</div>

						{if $gBitSystem->isFeatureActive('users_change_language')}
							<div class="row">
								{formlabel label="Language" for="language"}
								{forminput}
									<select name="bitlanguage" id="bitlanguage">
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
									<input type="text" name="CUSTOM[{$field}]" value="{$editUser->mPrefs.$field}" maxlength="250" />
								{/forminput}
							</div>
						{/foreach}

						<div class="row">
							{formlabel label="User information" for="users_information"}
							{forminput}
								<select name="users_information" id="users_information">
									<option value="public" {if $editUser->mPrefs.users_information eq 'public'}selected="selected"{/if}>{tr}public{/tr}</option>
									<option value="private" {if $editUser->mPrefs.users_information eq 'private'}selected="selected"{/if}>{tr}private{/tr}</option>
								</select>
								{formhelp note=""}
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="HomePage" for="users_homepage"}
							{forminput}
								<input size="50" type="text" name="users_homepage" id="users_homepage" value="{$editUser->mInfo.users_homepage|escape}" />
								{formhelp note="If you have a personal or professional homepage, enter it here."}
							{/forminput}
						</div>

						<div class="row submit">
							<input type="submit" name="prefs" value="{tr}Change preferences{/tr}" />
						</div>
					{/legend}

					{legend legend="User Preferences"}
						<div class="row">
							{formlabel label="Number of visited pages to remember" for="users_bread_crumb"}
							{forminput}
								<select name="users_bread_crumb" id="users_bread_crumb">
									<option value="1" {if $editUser->mInfo.users_bread_crumb eq 1}selected="selected"{/if}>{tr}1{/tr}</option>
									<option value="2" {if $editUser->mInfo.users_bread_crumb eq 2}selected="selected"{/if}>{tr}2{/tr}</option>
									<option value="3" {if $editUser->mInfo.users_bread_crumb eq 3}selected="selected"{/if}>{tr}3{/tr}</option>
									<option value="4" {if $editUser->mInfo.users_bread_crumb eq 4}selected="selected"{/if}>{tr}4{/tr}</option>
									<option value="5" {if $editUser->mInfo.users_bread_crumb eq 5}selected="selected"{/if}>{tr}5{/tr}</option>
									<option value="10" {if $editUser->mInfo.users_bread_crumb eq 10}selected="selected"{/if}>{tr}10{/tr}</option>
								</select>
								{formhelp note=""}
							{/forminput}
						</div>

						{if $gBitUser->canCustomizeTheme()}
							<div class="row">
								{formlabel label="Theme" for="style"}
								{forminput}
									<select name="style" id="style">
										{section name=ix loop=$styles}
											<option value="{$styles[ix]|escape}" {if $assignStyle eq $styles[ix]}selected="selected"{/if}>{$styles[ix]}</option>
										{/section}
									</select>
									{formhelp note="Pick the theme for your personal Homepage."}
								{/forminput}
							</div>
						{/if}

						<div class="row">
							{formlabel label="Displayed time zone"}
							{forminput}
								<label><input type="radio" name="site_display_utc" value="UTC" {if $editUser->mPrefs.site_display_utc eq 'UTC'}checked="checked"{/if} />{tr}UTC{/tr}</label>
								<br />
								<label><input type="radio" name="site_display_utc" value="Local" {if $editUser->mPrefs.site_display_utc eq 'Local'}checked="checked"{/if} />{tr}Local{/tr}</label>
								<br />
								<label><input type="radio" name="site_display_utc" value="Fixed" {if $editUser->mPrefs.site_display_utc eq 'Fixed'}checked="checked"{/if} />{tr}Fixed{/tr}</label>
								<br />
								<select name="site_display_timezone" id="site_display_timezone">
									<option value="-5"  {if $editUser->mPrefs.site_display_timezone eq -5}selected="selected"{/if}>{tr}-5{/tr}</option>
									<option value="-4"  {if $editUser->mPrefs.site_display_timezone eq -4}selected="selected"{/if}>{tr}-4{/tr}</option>
									<option value="-3" {if $editUser->mPrefs.site_display_timezone eq -3}selected="selected"{/if}>{tr}-3{/tr}</option>
									<option value="-2" {if $editUser->mPrefs.site_display_timezone eq -2}selected="selected"{/if}>{tr}-2{/tr}</option>
									<option value="-1" {if $editUser->mPrefs.site_display_timezone eq -1}selected="selected"{/if}>{tr}-1{/tr}</option>
									<option value="0" {if $editUser->mPrefs.site_display_timezone eq 0}selected="selected"{/if}>{tr}0{/tr}</option>
									<option value="1" {if $editUser->mPrefs.site_display_timezone eq 1}selected="selected"{/if}>{tr}1{/tr}</option>
									<option value="2" {if $editUser->mPrefs.site_display_timezone eq 1}selected="selected"{/if}>{tr}2{/tr}</option>
									<option value="3" {if $editUser->mPrefs.site_display_timezone eq 1}selected="selected"{/if}>{tr}3{/tr}</option>
									<option value="4" {if $editUser->mPrefs.site_display_timezone eq 1}selected="selected"{/if}>{tr}4{/tr}</option>
									<option value="5" {if $editUser->mPrefs.site_display_timezone eq 1}selected="selected"{/if}>{tr}5{/tr}</option>
								</select>
								{formhelp note="Internal data is stored using UTC time stamps, these can then be displayed using your browser timezone offset, or a fixed timezone which will also manage the correct daylight saving"}
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Use double-click to edit pages" for="users_double_click"}
							{forminput}
								<input type="checkbox" name="users_double_click" id="users_double_click" {if $editUser->mPrefs.users_double_click eq 'y'}checked="checked"{/if} />
								{formhelp note="Enabling this feature will allow you to double click on any wiki page and it will automatically take you to the edit page. Note that this does not work in all browsers."}
							{/forminput}
						</div>

						<div class="row submit">
							<input type="submit" name="prefs" value="{tr}Change preferences{/tr}" />
						</div>
					{/legend}
				{/form}

				{form legend="Change your email address"}
					<input type="hidden" name="view_user" value="{$editUser->mUserId}" />
					<div class="row">
						{formlabel label="Email" for="email"}
						{forminput}
							<input size="50" type="text" name="email" id="email" value="{$editUser->mInfo.email|escape}" />
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
					<input type="hidden" name="view_user" value="{$editUser->mUserId}" />
					{* Users with admin priv can change password without knowing the old one *}
					{if !$view_user or ( !$gBitUser->hasPermission('p_users_admin') and $view_user )}
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

				{* this should go in tidbits *}
				{if $gBitSystem->isFeatureActive( 'feature_tasks' )}
					{form legend="User Tasks"}
						<div class="row">
							{formlabel label="Tasks per page" for="tasks_max_records"}
							{forminput}
								<select name="tasks_max_records" id="tasks_max_records">
									<option value="2"  {if $editUser->mPrefs.tasks_max_records eq 2}selected="selected"{/if}>{tr}2{/tr}</option>
									<option value="5"  {if $editUser->mPrefs.tasks_max_records eq 5}selected="selected"{/if}>{tr}5{/tr}</option>
									<option value="10" {if $editUser->mPrefs.tasks_max_records eq 10}selected="selected"{/if}>{tr}10{/tr}</option>
									<option value="20" {if $editUser->mPrefs.tasks_max_records eq 20}selected="selected"{/if}>{tr}20{/tr}</option>
									<option value="30" {if $editUser->mPrefs.tasks_max_records eq 30}selected="selected"{/if}>{tr}30{/tr}</option>
									<option value="40" {if $editUser->mPrefs.tasks_max_records eq 40}selected="selected"{/if}>{tr}40{/tr}</option>
									<option value="50" {if $editUser->mPrefs.tasks_max_records eq 50}selected="selected"{/if}>{tr}50{/tr}</option>
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
							{if $editUser->mInfo.avatar_url}
								<img src="{$editUser->mInfo.avatar_url}" />
							{/if}
							{formhelp note="Small icon used for your posts or comments."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Self Portrait"} {forminput}
							{if $editUser->mInfo.portrait_url}
								<img src="{$editUser->mInfo.portrait_url}" />
							{/if}
							{formhelp note="Larger picture used on your bio page."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Logo" for=""}
						{forminput}
							{if $editUser->mInfo.logo_url}
								<img src="{$editUser->mInfo.logo_url}" /><br />
							{/if}
							{formhelp note="Image used for your organization."}
						{/forminput}
					</div>
				{/legend}
			{/jstab}

			{if $watches}
				{jstab title="Watches"}
					<table class="data">
						<caption>{tr}Watches{/tr}</caption>
						<tr>
							<th>Event</th>
							<th>Title</th>
						</tr>
						{foreach item=watch from=$watches}
							<tr class="{cycle vlaues="odd,even"}">
								<td>{$watch.event|escape}</td>
								<td>{$watch.title|escape}</td>
							</tr>
						{/foreach}
					</table>
				{/jstab}
			{/if}

			{foreach item=package from=$packages}
				{include file=$package.template settings=$editUser->mPrefs}
			{/foreach}

		{/jstabs}
	</div><!-- end .body -->
</div><!-- end .userpreferences -->

{/strip}
