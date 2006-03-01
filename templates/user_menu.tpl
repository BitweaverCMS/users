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
				<li><a class="head" href="javascript:toggle('{$cname}');">{tr}{$chdata.name}{/tr}</a>
			{/if}
			{assign var=opensec value='y'}
			{if $gBitSystem->isFeatureActive( 'feature_menusfolderstyle' )}
				<script type="text/javascript">flipIcon('{$cname}');</script>
			{/if}
			<div {if $smarty.cookies.$cname eq 'o' or $menu_info.type eq 'e'}style="display:block;"{elseif $smarty.cookies.$cname eq 'c' or $menu_info.type eq 'd'}style="display:none;"{/if} id="{$cname}"><ul>
		{else}
			<li><a class="item" href="{$chdata.url|escape}">{tr}{$chdata.name}{/tr}</a></li>
		{/if}
	{/foreach}
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
