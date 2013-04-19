{strip}
{if $packagemenutitle}<a href="#" onclick="return(false);" tabindex="-1" class="sub-menu-root">{tr}{$smarty.const.users_pkg_dir|capitalize}{/tr}</a>{/if}
<ul class="{$packageMenuClass}">
	<li><a class="item" href="{$smarty.const.USERS_PKG_URL}admin/index.php">{tr}Edit Users{/tr}</a></li>
	<li><a class="item" href="{$smarty.const.KERNEL_PKG_URL}admin/index.php?page=users">{tr}Users{/tr}</a></li>
		{if $gBitSystem->isPackageActive('tidbits')}
			<li><a class="item" href="{$smarty.const.KERNEL_PKG_URL}admin/index.php?page=tidbits">{tr}Users Tidbits{/tr}</a></li>
		{/if}
	<li><a class="item" href="{$smarty.const.KERNEL_PKG_URL}admin/index.php?page=login">{tr}Login{/tr}</a></li>
	<li><a class="item" href="{$smarty.const.USERS_PKG_URL}admin/user_activity.php">{tr}User Activity{/tr}</a></li>
	<li><a class="item" href="{$smarty.const.USERS_PKG_URL}admin/users_import.php">{tr}Import Users{/tr}</a></li>
		{if $gBitSystem->isPackageActive('protector')}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}admin/edit_role.php">{tr}Role &amp; Permissions{/tr}</a></li>
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}admin/role_permissions.php">{tr}Permission Maintenance{/tr}</a></li>
		{else}
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}admin/edit_group.php">{tr}Groups &amp; Permissions{/tr}</a></li>
			<li><a class="item" href="{$smarty.const.USERS_PKG_URL}admin/permissions.php">{tr}Permission Maintenance{/tr}</a></li>
		{/if}
</ul>
{/strip}
