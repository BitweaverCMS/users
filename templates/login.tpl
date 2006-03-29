{* $Header: /cvsroot/bitweaver/_bit_users/templates/login.tpl,v 1.5 2006/03/29 19:38:40 sylvieg Exp $ *}

{strip}
<div class="display login">
	<div class="header">
		<h1>{tr}Login Page{/tr}</h1>
	</div>

	<div class="body">

		{include file=bitpackage:users/login_inc.tpl}

		<ul>
			{if $gBitSystem->isFeatureActive('forgot_pass')}
				<li><a href="{$smarty.const.USERS_PKG_URL}remind_password.php">{tr}Click here to retrieve your password.{/tr}</a></li>
			{/if}

			{if $gBitSystem->isFeatureActive('allow_register')}
				<li><a href="{$smarty.const.USERS_PKG_URL}register.php">{tr}Click here to register with us.{/tr}</a></li>
			{/if}
		</ul>
	</div><!-- end .body -->
</div><!-- end .login -->
{/strip}
