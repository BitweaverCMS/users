{* $Header: /cvsroot/bitweaver/_bit_users/templates/user_information_inc.tpl,v 1.18 2009/06/30 19:05:33 spiderr Exp $ *}
{strip}
{if $userData->getPreference('users_information') eq 'public' or $gBitUser->mUserId eq $userData->mUserId}
	<div class="row">
		{formlabel label="Login"}
		{forminput}
			{if $gBitSystem->getConfig('users_display_name') eq 'login'}<strong>{/if}
				{$userData->mInfo.login}
			{if $gBitSystem->getConfig('users_display_name') eq 'login'}</strong>{/if}
		{/forminput}
	</div>

	<div class="row">
		{formlabel label="Real Name"}
		{forminput}
			{if $gBitSystem->getConfig('users_display_name') eq 'real_name'}<strong>{/if}
				{$userData->mInfo.real_name}
			{if $gBitSystem->getConfig('users_display_name') eq 'real_name'}</strong>{/if}
		{/forminput}
	</div>

	{if $userData->getPreference('users_country')}
		<div class="row">
			{formlabel label="Country"}
			{forminput}
				{biticon ipackage="users" ipath="flags/" iname=$userData->getPreference('flag') iexplain=$userData->getPreference('flag') iforce="icon"} {$userData->getPreference('users_country')}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label="Language"}
			{forminput}
				{assign var=langcode value=$userData->getPreference('bitlanguage')}
				{$gBitLanguage->mLanguageList.$langcode.full_name}
			{/forminput}
		</div>
	{/if}

	{foreach from=$customFields key=i item=field}
		<div class="row">
			{formlabel label=$field}
			{forminput}
				{$userData->getPreference($field)}
			{/forminput}
		</div>
	{/foreach}

	{if $gBitUser->hasPermission( 'p_users_admin' )}
		<div class="row">
			{formlabel label="User ID"}
			{forminput}
				{$userData->mInfo.user_id}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label="Registered Email"}
			{forminput}
				{$userData->mInfo.email}
			{/forminput}
		</div>
	{/if}

	{if $userData->mInfo.publicEmail}
		<div class="row">
			{formlabel label="Email Address"}
			{forminput}
				{$userData->mInfo.publicEmail}
			{/forminput}
		</div>
	{/if}

	<div class="row">
		{formlabel label="Member since"}
		{forminput}
			{$userData->mInfo.registration_date|bit_long_date}
		{/forminput}
	</div>

	{if $userData->mUserId ne $smarty.const.ANONYMOUS_USER_ID}
		<div class="row">
			{formlabel label="Last Login"}
			{forminput}
				{$userData->mInfo.last_login|bit_long_datetime}
			{/forminput}
		</div>

		{if $gBitSystem->isPackageActive( 'messages' ) and $userData->getPreference('messages_allow_messages') ne 'n' and $gBitUser->mUserId ne $userData->mUserId}
			<div class="row">
				{formlabel label="Send Message"}
				{forminput}
					{tr}Send a <a href="{$smarty.const.MESSAGES_PKG_URL}compose.php?to={$userInfo.login}">personal message</a> to this user{/tr}
				{/forminput}
			</div>
		{/if}

		{if $gBitSystem->isPackageActive('stars') && $gBitSystem->isFeatureActive('stars_user_ratings')}
			{include file="bitpackage:stars/user_ratings.tpl"}
		{/if}
	{/if}
{else}
	<div class="norecords">
		{tr}This users information is not publicly viewable.{/tr}
	</div>
{/if}
{/strip}
