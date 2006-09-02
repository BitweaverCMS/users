{strip}
{assign var=opensec value='y'}
{assign var=submenus value='n'}
<div class="menu"><ul>
{if $menu_info.type eq 'e' or $menu_info.type eq 'd'}
	{foreach key=pos item=chdata from=$moptions}
		{assign var=cname value='menu'|cat:$menu_info.menu_id|cat:'__'|cat:$chdata.position}
		{if $chdata.type eq 's'}
			{if $opensec eq 'y'}
				</ul></div>
				{if $submenus eq 'n'}
					<ul class="menu">
					{assign var=submenus value='y'}
				{else}
					</li>
				{/if}
			{/if}
			{if $gBitSystem->isFeatureActive( 'feature_menusfolderstyle' )}
				<li><a class="head" href="javascript:flipIcon('{$cname}');">{biticon ipackage=liberty iname="collapsed" id="`$cname`img" iexplain="folder"}{tr}{$chdata.name}{/tr}</a>
			{else}
				<li><a class="head" href="javascript:flipWithSign('{$cname}');">{tr}{$chdata.name}{/tr}</a><span id="flipper{$cname}">&nbsp;</span>
			{/if}
			{assign var=opensec value='y'}
			<div id="{$cname}"><ul>
		{else}
			<li><a class="item" href="{$chdata.url|escape}">{tr}{$chdata.name}{/tr}</a></li>
		{/if}
	{/foreach}
	<script type="text/javascript">
		{if $gBitSystem->isFeatureActive( 'feature_menusfolderstyle' )}
			setFlipIcon('{$cname}');
		{else}
			setFlipWithSign('{$cname}');
		{/if}
	</script>
{else}
	{foreach key=pos item=chdata from=$moptions}
		{if $chdata.type eq 's'}
			<li><a class="head" href="{$chdata.url|escape}">{tr}{$chdata.name}{/tr}</a></li>
		{else}
			<li><a class="item" href="{$chdata.url|escape}">{tr}{$chdata.name}{/tr}</a></li>
		{/if}
	{/foreach}
{/if}
</ul></div>
</li></ul>
{/strip}
