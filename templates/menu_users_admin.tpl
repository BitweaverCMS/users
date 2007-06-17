{strip}
<ul>
	<li><a class="item" href="{$smarty.const.KERNEL_PKG_URL}admin/index.php?page=users">{tr}Users Settings{/tr}</a></li>
	{if $gBitSystem->isPackageActive('tidbits')}
		<li><a class="item" href="{$smarty.const.KERNEL_PKG_URL}admin/index.php?page=tidbits">{tr}Users Tidbits{/tr}</a></li>
	{/if}
	<li><a class="item" href="{$smarty.const.KERNEL_PKG_URL}admin/index.php?page=login">{tr}Login Settings{/tr}</a></li>
	<li><a class="item" href="{$smarty.const.USERS_PKG_URL}admin/index.php">{tr}Edit Users{/tr}</a></li>
	<li><a class="item" href="{$smarty.const.USERS_PKG_URL}admin/edit_group.php">{tr}Groups &amp; Permissions{/tr}</a></li>
	<li><a class="item" href="{$smarty.const.USERS_PKG_URL}admin/permissions.php">{tr}Permission Maintenance{/tr}</a></li>
</ul>
{/strip}
