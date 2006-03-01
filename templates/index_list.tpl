<div class="display users listing">

{if $search_request ne ''}
	<div class="header">
		<h1>{tr}User{/tr} {$search_request} {tr}Not Found{/tr}</h1>
	</div>
{/if}

	<div class="header">
		<h1>{tr}{$gBitSystem->getConfig('site_title')} Members{/tr}</h1>
	</div>

	<div class="body">
		{include file="bitpackage:users/users_list.tpl"}
	</div><!-- end .body -->
</div><!-- end .users -->
