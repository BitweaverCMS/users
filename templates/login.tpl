{* $Header: /cvsroot/bitweaver/_bit_users/templates/login.tpl,v 1.1.1.1.2.2 2005/10/23 12:32:24 squareing Exp $ *}

{strip}
<div class="display login">
	<div class="header">
		<h1>{tr}Login Page{/tr}</h1>
	</div>

	<div class="body">

		{include file=bitpackage:users/login_inc.tpl}

		<ul>
			{if $forgotPass eq 'y'}
				<li><a href="{$smarty.const.USERS_PKG_URL}remind_password.php">{tr}Click here to retrieve your password.{/tr}</a></li>
			{/if}

			{if $allowRegister eq 'y'}
				<li><a href="{$smarty.const.USERS_PKG_URL}register.php">{tr}Click here to register with us.{/tr}</a></li>
			{/if}
		</ul>
	</div><!-- end .body -->
</div><!-- end .login -->
{/strip}
