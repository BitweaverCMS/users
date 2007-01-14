{* $Header: /cvsroot/bitweaver/_bit_users/templates/Attic/admin_permission.tpl,v 1.3 2007/01/14 13:10:09 squareing Exp $ *}
{strip}

<div class="floaticon">{bithelp}</div>

<div class="admin groups">
	<div class="header">
		<h1>{tr}Assign permissions{/tr}</h1>
	</div>

	<p>{tr}{$group} permissions{/tr}</p>

	<div class="navbar">
	  <a href="{$smarty.const.USERS_PKG_URL}admin/admin_groups.php">{tr}Back to groups{/tr}</a>
	</div>

	<div class="body">

	</div><!-- end .body -->
</div><!-- end .users -->

{/strip}
