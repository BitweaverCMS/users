{form}
<input type="hidden" name="page" value="{$page}" />
{jstabs}
	{jstab title="User Registration and Login"}

		{foreach from=$loginSettings key=feature item=output}
			<div class="form-group">
				{if $feature == 'users_validate_email' && !$gBitSystem->hasValidSenderEmail()}
					{formfeedback error="Site <a href=\"`$smarty.const.BIT_ROOT_URL`kernel/admin/index.php?page=server\">emailer return address</a> is not valid!"}
				{/if}
				{if $feature == 'users_random_number_reg'}
					{formfeedback warning=$warning}
				{/if}
				{forminput}
					{if $output.type == 'text'}
						{formlabel label=$output.label for=$feature}
						{if $feature eq 'cookie_domain' && $gBitSystem->getConfig($feature) eq ''}
							<input type="text" class="form-control" name="{$feature}" id="{$feature}" value="{$smarty.server.SERVER_NAME}" />
						{elseif $feature eq 'cookie_path' && $gBitSystem->getConfig($feature) eq ''}
							<input type="text" class="form-control" name="{$feature}" id="{$feature}" value="{$smarty.const.BIT_ROOT_URL}" />
						{else}
							<input type="text" class="form-control" name="{$feature}" id="{$feature}" value="{$gBitSystem->getConfig($feature)|escape}" />
						{/if}
						{formhelp note=$output.note page=$output.page link=$output.link}
					{else}
						{forminput label="checkbox"}
							{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature} {tr}{$output.label}{/tr}
							{formhelp note=$output.note page=$output.page link=$output.link}
						{/forminput}
					{/if}
				{/forminput}
			</div>
		{/foreach}

		<div class="form-group">
			{formlabel label="Default group for users with verifiable emails" for="users_validate_email_group"}
			{forminput}

				<select name="users_validate_email_group" class="form-control" id="users_validate_email_group">
					<option value="" {if $gBitSystem->getConfig('users_validate_email_group') eq ''} selected="selected"{/if}>(none)</option>
					{foreach from=$groups item='group'}
						<option value="{$group.group_id}" {if $gBitSystem->getConfig('users_validate_email_group') eq $group.group_id} selected="selected"{/if}>{$group.group_name}</option>
					{/foreach}
				</select>
				<div class="formhelp">Selecting (none) will prevent the user from registering with a non responsive email. <a class="btn btn-default btn-xs" href='{$smarty.const.USERS_PKG_URL}admin/verify_emails.php?tk={$gBitUser->mTicket}'>{tr}Verify User Emails{/tr}</a></div>
			{/forminput}
		</div>
		<div class="form-group">
			{formlabel label="Duration of 'Remember me' feature" for="users_remember_time"}
			{forminput}
				<select class="form-control" name="users_remember_time" id="users_remember_time">
					<option value="300" {if $gBitSystem->getConfig('users_remember_time') eq 300} selected="selected"{/if}>5 {tr}minutes{/tr}</option>
					<option value="900" {if $gBitSystem->getConfig('users_remember_time') eq 900} selected="selected"{/if}>15 {tr}minutes{/tr}</option>
					<option value="1800" {if $gBitSystem->getConfig('users_remember_time') eq 1800} selected="selected"{/if}>30 {tr}minutes{/tr}</option>
					<option value="3600" {if $gBitSystem->getConfig('users_remember_time') eq 3600} selected="selected"{/if}>1 {tr}hour{/tr}</option>
					<option value="7200" {if $gBitSystem->getConfig('users_remember_time') eq 7200} selected="selected"{/if}>2 {tr}hours{/tr}</option>
					<option value="43200" {if $gBitSystem->getConfig('users_remember_time') eq 43200} selected="selected"{/if}>12 {tr}hours{/tr}</option>
					<option value="86400" {if $gBitSystem->getConfig('users_remember_time') eq 86400} selected="selected"{/if}>1 {tr}day{/tr}</option>
					<option value="604800" {if $gBitSystem->getConfig('users_remember_time') eq 604800} selected="selected"{/if}>1 {tr}week{/tr}</option>
					<option value="2592000" {if $gBitSystem->getConfig('users_remember_time') eq 2592000} selected="selected"{/if}>1 {tr}month{/tr}</option>
					<option value="15724800" {if $gBitSystem->getConfig('users_remember_time') eq 15724800} selected="selected"{/if}>6 {tr}months{/tr}</option>
					<option value="31449600" {if $gBitSystem->getConfig('users_remember_time') eq 31449600} selected="selected"{/if}>1 {tr}year{/tr}</option>
				</select>
				{formhelp note=""}
			{/forminput}
		</div>

		<div class="form-group">
			{if $roleList }
				{formlabel label="Roles choice at registration" for="registration_role_choice"}
				{forminput}
					<select class="form-control" name="registration_role_choice[]" multiple="multiple" size="5">
						<option value="">&nbsp;</option>
						{foreach key=r item=ro from=$roleList}
							{if $ro.role_id ne -1}
								<option value="{$ro.role_id}" {if $ro.is_public eq 'y'} selected="selected"{/if}>{$ro.role_name|truncate:"52":" ..."}</option>
							{/if}
						{/foreach}
					</select>
					{formhelp note="A user will be able to select one of the selected roles at registration. If you select the default role (Registered), he will not be obliged to select a role."}
				{/forminput}
			{else}
				{formlabel label="Groups choice at registration" for="registration_group_choice"}
				{forminput}
					<select class="form-control" name="registration_group_choice[]" multiple="multiple" size="5">
						<option value="">&nbsp;</option>
						{foreach key=g item=gr from=$groupList}
							{if $gr.group_id ne -1}
								<option value="{$gr.group_id}" {if $gr.is_public eq 'y'} selected="selected"{/if}>{$gr.group_name|truncate:"52":" ..."}</option>
							{/if}
						{/foreach}
					</select>
					{formhelp note="A user will be able to select one of the selected group at registration. If you select the default group (Registered), he will not be obliged to select a group."}
				{/forminput}
			{/if}
		</div>
	{/jstab}

	{jstab title="Single Sign On"}
		<div class="form-group">
			{formfeedback hash=$authSettings.err}

			{formlabel label="Authentication method"}
			{forminput}
				{foreach from=$hybridProviders key=providerKey item=providerHash}
					{assign var=providerActive value="users_ha_`$providerKey`_enabled"}
					<fieldset><legend class="checkbox"><label><input id="{$providerActive}_checkbox" type="checkbox" name="hybridauth[{$providerActive}]" {if $gBitSystem->getConfig($providerActive)}checked{/if}> {booticon iname="{$providerHash.icon}"} {$providerHash.provider}</label></legend>
					<script>
					$('#{$providerActive}_checkbox').change(function(){
						if($(this).prop("checked")) $('#{$providerActive}').show(); else $('#{$providerActive}').hide();
					});
					</script>
						<div class="form-group" id="{$providerActive}" style="{if !$gBitSystem->getConfig($providerActive)}display:none;{/if}">
						{foreach from=$providerHash.keys key=configKey item=configHelp}
							{assign var=featureKey value="users_ha_`$providerKey`_`$configKey`"}
							{forminput}
								<div class="input-group">
									<span class="input-group-addon">{$configKey|ucwords}</span>
									<input type="text" class="form-control" name="hybridauth[{$featureKey}]" id="{$featureKey}" value="{$gBitSystem->getConfig($featureKey)|escape}"/>
								</div>
								{if $configHelp}{formhelp note=$configHelp}{/if}
							{/forminput}
						{/foreach}
						</div>
					</fieldset>
				{/foreach}
			{/forminput}
{*
			{forminput}
				{foreach from=$authSettings.avail_method item='auth_method' key='iter'}
					<label>Method {$iter+1}
						<select name="users_auth_method_{$iter}">
							<option value="" {if $auth_method.value eq ''} selected="selected"{/if}>-</option>
							{foreach from=$authSettings.avail item='method' key='meth_name'}
								<option value="{$meth_name}" {if $auth_method.value eq $meth_name} selected="selected"{/if}>{$method.name}</option>
							{/foreach}
						</select>
					</label>
				{/foreach}
				{if $gBitSystem->getConfig("users_auth_method_`$smarty.section.auth_select_outer.iteration-1`") eq 'tiki'} selected="selected"{/if}
				<select name="users_auth_method" id="users_auth_method">
					<option value="tiki" {if $gBitSystem->getConfig('users_auth_method') eq 'tiki'} selected="selected"{/if}>{tr}Just bitweaver{/tr}</option>
					<option value="ws" {if $gBitSystem->getConfig('users_auth_method') eq 'ws'} selected="selected"{/if}>{tr}Web Server{/tr}</option>
					{if $ldapEnabled}<option value="auth" {if $gBitSystem->getConfig('users_auth_method') eq 'auth'} selected="selected"{/if}>{tr}bitweaver and PEAR::Auth{/tr}</option>{/if}
				</select>
			{/forminput}
*}
		</div>
	{/jstab}

	{jstab title="Registration Form"}
		<input type="hidden" name="page" value="{$page}" />

		<p class="formhelp">{tr}Here you can specify what the registration page should look like. All these settings will still be available from the users preferences page.{/tr}</p>

		{foreach from=$registerSettings key=feature item=output}
			<div class="form-group">
				{if $output.type == 'text'}
					{forminput}
						{formlabel label=$output.label for=$feature}
						<input type="text" class="form-control" name="{$feature}" id="{$feature}" value="{$gBitSystem->getConfig($feature)|escape}" />
						{formhelp note=$output.note page=$output.page link=$output.link}
					{/forminput}
				{else}
					{forminput label="checkbox"}
						{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature} {tr}{$output.label}{/tr}
						{formhelp note=$output.note page=$output.page link=$output.link}
					{/forminput}
				{/if}
			</div>
		{/foreach}
	{/jstab}

	{jstab title="HTTP Settings"}
		<input type="hidden" name="page" value="{$page}" />

		<div class="form-group warning">{tr}If you turn on any secure login features you must set the HTTP and HTTPS server name.{/tr}</div>

		{foreach from=$httpSettings key=feature item=output}
			<div class="form-group">
				{if $output.type == 'text'}
					{forminput}
						{formlabel label=$output.label for=$feature}
						<input type="text" class="form-control" name="{$feature}" id="{$feature}" value="{$gBitSystem->getConfig($feature)|escape}" />
						{formhelp note=$output.note page=$output.page link=$output.link}
					{/forminput}
				{else}
					{forminput label="checkbox"}
						{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature} {tr}{$output.label}{/tr}
						{formhelp note=$output.note page=$output.page link=$output.link}
					{/forminput}
				{/if}
			</div>
		{/foreach}
	{/jstab}
	{foreach from=$authSettings.avail item='method' key='meth_name'}
		{if count($method.options)>0}
			{jstab title=$method.name}
				<input type="hidden" name="page" value="{$page}" />
				{foreach from=$method.options item='output' key='op_id'}
					<div class="form-group">
						{formlabel label=$output.label for=$op_id}
						{forminput}
							{if $output.type == 'checkbox'}
								{html_checkboxes name="$op_id" values="y" selected=$output.value labels=false id=$op_id}
							{elseif $output.type == 'option'}
								<select name="{$op_id}" id="{$op_id}">
									{foreach from=$output.options item='op_text' key='op_value'}
										<option value="{$op_value}" {if $output.value eq $op_value} selected="selected"{/if}>{$op_text}</option>
									{/foreach}
								</select>
							{else}
								<input type="text" class="form-control" name="{$op_id}" id="{$op_id}" value="{$output.value|escape}" />
							{/if}
							{formhelp note=$output.note page=$output.page link=$output.link}
						{/forminput}
					</div>
				{/foreach}
			{/jstab}
		{/if}
	{/foreach}
{/jstabs}

<div class="form-group submit">
	<input type="submit" class="btn btn-default" name="httpprefs" value="{tr}Change preferences{/tr}" />
</div>
{/form}
