{jstabs}
	{jstab title="User Registration and Login"}
		{form legend="User Registration and Login"}
			<input type="hidden" name="page" value="{$page}" />

			<div class="row">
				{formfeedback hash=$authSettings.err}

				{formlabel label="Authentication method"}

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
						<br />
					{/foreach}
					{*
					{if $gBitSystem->getConfig("users_auth_method_`$smarty.section.auth_select_outer.iteration-1`") eq 'tiki'} selected="selected"{/if}
					<select name="users_auth_method" id="users_auth_method">
						<option value="tiki" {if $gBitSystem->getConfig('users_auth_method') eq 'tiki'} selected="selected"{/if}>{tr}Just bitweaver{/tr}</option>
						<option value="ws" {if $gBitSystem->getConfig('users_auth_method') eq 'ws'} selected="selected"{/if}>{tr}Web Server{/tr}</option>
						{if $ldapEnabled}<option value="auth" {if $gBitSystem->getConfig('users_auth_method') eq 'auth'} selected="selected"{/if}>{tr}bitweaver and PEAR::Auth{/tr}</option>{/if}
					</select>
					*}
					{*formhelp note="Registration requrires that Bitweaver Auth be in the Method List"*}
				{/forminput}
			</div>

			{foreach from=$loginSettings key=feature item=output}
				<div class="row">
					{if $feature == 'users_validate_email' && !$gBitSystem->hasValidSenderEmail()}
						{formfeedback error="Site <a href=\"`$smarty.const.BIT_ROOT_URL`kernel/admin/index.php?page=server\">emailer return address</a> is not valid!"}
					{/if}
					{if $feature == 'users_random_number_reg'}
						{formfeedback warning=$warning}
					{/if}
					{formlabel label=`$output.label` for=$feature}
					{forminput}
						{if $output.type == 'text'}
							{if $feature eq 'cookie_domain' && $gBitSystem->getConfig($feature) eq ''}
								<input type="text" size="50" name="{$feature}" id="{$feature}" value="{$smarty.server.SERVER_NAME}" />
							{elseif $feature eq 'cookie_path' && $gBitSystem->getConfig($feature) eq ''}
								<input type="text" size="50" name="{$feature}" id="{$feature}" value="{$smarty.const.BIT_ROOT_URL}" />
							{else}
								<input type="text" size="50" name="{$feature}" id="{$feature}" value="{$gBitSystem->getConfig($feature)|escape}" />
							{/if}
						{else}
							{html_checkboxes name="$feature" values="y" checked=$gBitSystem->getConfig($feature) labels=false id=$feature}
						{/if}
						{formhelp note=`$output.note` page=`$output.page` link=`$output.link`}
					{/forminput}
				</div>
			{/foreach}

			<div class="row">
				{formlabel label="Duration of 'Remember me' feature" for="users_remember_time"}
				{forminput}
					<select name="users_remember_time" id="users_remember_time">
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

			<div class="row">
				{formlabel label="Groups choice at registration" for="registration_group_choice"}
				{forminput}
					<select name="registration_group_choice[]" multiple="multiple" size="5">
						<option value="">&nbsp;</option>
						{foreach key=g item=gr from=$groupList}
							{if $gr.group_id ne -1}
								<option value="{$gr.group_id}" {if $gr.is_public eq 'y'} selected="selected"{/if}>{$gr.group_name|truncate:"52":" ..."}</option>
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
	{foreach from=$authSettings.avail item='method' key='meth_name'}
		{if count($method.options)>0}
			{jstab title=$method.name}
				{form legend=$method.name}
					<input type="hidden" name="page" value="{$page}" />
					{foreach from=$method.options item='output' key='op_id'}
						<div class="row">
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
									<input type="text" size="50" name="{$op_id}" id="{$op_id}" value="{$output.value|escape}" />
								{/if}
								{formhelp note=`$output.note` page=`$output.page` link=`$output.link`}
							{/forminput}
						</div>
					{/foreach}
					<div class="row submit">
						<input type="submit" name="auth_{$meth_name}" value="{tr}Change {$method.name} preferences{/tr}" />
					</div>
				{/form}
			{/jstab}
		{/if}
	{/foreach}

{/jstabs}
