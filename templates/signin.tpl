{strip}
	<div class="display login">
		{include file="bitpackage:users/login_inc.tpl"}
	</div>
	{if $gBitSystem->isFeatureActive('users_allow_register')}
	      <div class="display register">
		      {include file="bitpackage:users/register.tpl"}
	      </div>
	{/if}
{/strip}
