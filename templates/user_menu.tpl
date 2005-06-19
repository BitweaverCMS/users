{assign var=opensec value='n'}

{if $menu_info.type eq 'e' or $menu_info.type eq 'd'}
	{foreach key=pos item=chdata from=$channels}
		{assign var=cname value=$menu_info.menuId|cat:'__'|cat:$chdata.position}
		{if $chdata.type eq 's'}
			{if $opensec eq 'y'}</div>{/if}

			{if $gBitSystemPrefs.feature_menusfolderstyle eq 'y'}
				<a class="menuhead" href="javascript:icntoggle('menu{$cname}');">{biticon ipackage=liberty iname="folder" name="menu`$cname`img" iexplain="folder"}
			{else}
				<a class="menuhead" href="javascript:toggle('menu{$cname}');">
			{/if} 
			{tr}{$chdata.name}{/tr}</a>
			{assign var=opensec value='y'}
			<div {if $menu_info.type eq 'd' and $smarty.cookies.$cname ne 'o'}style="display:none;"{else}style="display:block;"{/if} id="menu{$cname}">
		{else}
			<a class="menuoption" href="{$chdata.url|escape}">{tr}{$chdata.name}{/tr}</a>
		{/if}
		<script language="Javascript" type="text/javascript">
			{if $gBitSystemPrefs.feature_menusfolderstyle eq 'y'}
				setfoldericonstate('menu{$menu_info.menuId|cat:'__'|cat:$chdata.position}', '{$menu_info.type}');
			{/if}
		</script>
	{/foreach}

{else}
	{foreach key=pos item=chdata from=$channels}
		{if $chdata.type eq 's'}
			<a class="menuhead" href="{$chdata.url|escape}">{tr}{$chdata.name}{/tr}</a>
		{else}
			<a class="menuoption" href="{$chdata.url|escape}">{tr}{$chdata.name}{/tr}</a>
		{/if}
	{/foreach}
{/if}
