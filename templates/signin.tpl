{strip}
<div class="row">
	{include file="bitpackage:users/login_inc.tpl"}
	{if $gBitSystem->isFeatureActive( 'users_allow_register' )}
		{include file="bitpackage:users/register.tpl"}
	{/if}
</div>
{/strip}
