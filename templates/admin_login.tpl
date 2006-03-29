{jstabs}
	{jstab title="User Registration and Login"}
		{form legend="User Registration and Login"}
			<input type="hidden" name="page" value="{$page}" />

			<div class="row">
				{formlabel label="Authentication method" for="auth_method"}
				{forminput}
					<select name="auth_method" id="auth_method">
						<option value="tiki" {if $auth_method eq 'tiki'} selected="selected"{/if}>{tr}Just bitweaver{/tr}</option>
						<option value="ws" {if $auth_method eq 'ws'} selected="selected"{/if}>{tr}Web Server{/tr}</option>
						<option value="auth" {if $auth_method eq 'auth'} selected="selected"{/if}>{tr}bitweaver and PEAR::Auth{/tr}</option>
					</select>
					{formhelp note=""}
				{/forminput}
			</div>

			{foreach from=$loginSettings key=feature item=output}
				<div class="row">
					{if $feature == 'validate_email' && !$gBitSystem->hasValidSenderEmail()}
						{formfeedback error="Site <a href=\"`$smarty.const.BIT_ROOT_URL`kernel/admin/index.php?page=server\">emailer return address</a> is not valid!"}
					{/if}
					{if $feature == 'rnd_num_reg'}
						{formfeedback warning=$warning}
					{/if}
					{formlabel label=`$output.label` for=$feature}
					{forminput}
						{if $output.type == 'text'}
							<input type="text" size="50" name="{$feature}" id="{$feature}" value="{$gBitSystem->getConfig($feature)|escape}" />
						{else}
							{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
						{/if}
						{formhelp note=`$output.note` page=`$output.page` link=`$output.link`}
					{/forminput}
				</div>
			{/foreach}

			<div class="row">
				{formlabel label="Duration of 'Remember me' feature" for="remembertime"}
				{forminput}
					<select name="remembertime" id="remembertime">
						<option value="300" {if $remembertime eq 300} selected="selected"{/if}>5 {tr}minutes{/tr}</option>
						<option value="900" {if $remembertime eq 900} selected="selected"{/if}>15 {tr}minutes{/tr}</option>
						<option value="1800" {if $remembertime eq 1800} selected="selected"{/if}>30 {tr}minutes{/tr}</option>
						<option value="3600" {if $remembertime eq 3600} selected="selected"{/if}>1 {tr}hour{/tr}</option>
						<option value="7200" {if $remembertime eq 7200} selected="selected"{/if}>2 {tr}hours{/tr}</option>
						<option value="43200" {if $remembertime eq 43200} selected="selected"{/if}>12 {tr}hours{/tr}</option>
						<option value="86400" {if $remembertime eq 86400} selected="selected"{/if}>1 {tr}day{/tr}</option>
						<option value="604800" {if $remembertime eq 604800} selected="selected"{/if}>1 {tr}week{/tr}</option>
						<option value="2592000" {if $remembertime eq 2592000} selected="selected"{/if}>1 {tr}month{/tr}</option>
						<option value="15724800" {if $remembertime eq 15724800} selected="selected"{/if}>6 {tr}months{/tr}</option>
						<option value="31449600" {if $remembertime eq 31449600} selected="selected"{/if}>1 {tr}year{/tr}</option>
					</select>
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Groups choice at registration" for="registration_group_choice"}
				{forminput}
					<select name="registration_group_choice[]" multiple="multiple" size="5">
						<option value="">&nbsp;</option>
						{foreach key=g item=gr from=$groupList}
							{if $gr.group_id ne -1} 
								<option value="{$gr.group_id}" {if $gr.registration_choice eq 'y'} selected="selected"{/if}>{$gr.group_name|truncate:"52":" ..."}</option>
							{/if}
						{/foreach}
					</select>
					{formhelp note="A user will be able to select one of the selected group at registration. If you select the default group (Registered), he will not be obliged to select a group."}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" name="loginprefs" value="{tr}Change preferences{/tr}" />
			</div>
		{/form}
	{/jstab}

	{jstab title="Registration Form"}
		{form legend="Registration Form"}
			<input type="hidden" name="page" value="{$page}" />

			<p class="formhelp">{tr}Here you can specify what the registration page should look like. All these settings will still be available from the users preferences page.{/tr}</p>

			{foreach from=$registerSettings key=feature item=output}
				<div class="row">
					{formlabel label=`$output.label` for=$feature}
					{forminput}
						{if $output.type == 'text'}
							<input type="text" size="50" name="{$feature}" id="{$feature}" value="{$gBitSystem->getConfig($feature)|escape}" />
						{else}
							{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
						{/if}
						{formhelp note=`$output.note` page=`$output.page` link=`$output.link`}
					{/forminput}
				</div>
			{/foreach}

			<div class="row submit">
				<input type="submit" name="registerprefs" value="{tr}Change preferences{/tr}" />
			</div>
		{/form}
	{/jstab}

	{jstab title="HTTP Settings"}
		{form legend="HTTP Settings"}
			<input type="hidden" name="page" value="{$page}" />

			{foreach from=$httpSettings key=feature item=output}
				<div class="row">
					{formlabel label=`$output.label` for=$feature}
					{forminput}
						{if $output.type == 'text'}
							<input type="text" size="50" name="{$feature}" id="{$feature}" value="{$gBitSystem->getConfig($feature)|escape}" />
						{else}
							{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
						{/if}
						{formhelp note=`$output.note` page=`$output.page` link=`$output.link`}
					{/forminput}
				</div>
			{/foreach}

			<div class="row submit">
				<input type="submit" name="httpprefs" value="{tr}Change preferences{/tr}" />
			</div>
		{/form}
	{/jstab}

	{jstab title="PEAR::Auth"}
		{form legend="PEAR::Auth"}
			<input type="hidden" name="page" value="{$page}" />

			{foreach from=$ldapSettings key=feature item=output}
				<div class="row">
					{formlabel label=`$output.label` for=$feature}
					{forminput}
						{if $output.type == 'text'}
							<input type="text" size="50" name="{$feature}" id="{$feature}" value="{$gBitSystem->getConfig($feature)|escape}" />
						{else}
							{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
						{/if}
						{formhelp note=`$output.note` page=`$output.page` link=`$output.link`}
					{/forminput}
				</div>
			{/foreach}

			<div class="row">
				{formlabel label="LDAP Scope" for="auth_ldap_scope"}
				{forminput}
					<select name="auth_ldap_scope" id="auth_ldap_scope">
						<option value="sub" {if $auth_ldap_scope eq "sub"} selected="selected"{/if}>sub</option>
						<option value="one" {if $auth_ldap_scope eq "one"} selected="selected"{/if}>one</option>
						<option value="base" {if $auth_ldap_scope eq "base"} selected="selected"{/if}>base</option>
					</select>
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" name="auth_pear" value="{tr}Change preferences{/tr}" />
			</div>
		{/form}
	{/jstab}
{/jstabs}
