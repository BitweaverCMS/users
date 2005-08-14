{strip}
{assign var=opensec value='y'}
{assign var=submenus value='n'}
<div class="menu"><ul>
{if $menu_info.type eq 'e' or $menu_info.type eq 'd'}
	{foreach key=pos item=chdata from=$channels}
		{assign var=cname value=$menu_info.menuId|cat:'__'|cat:$chdata.position}
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
				<li><a class="head" href="javascript:icntoggle('menu{$cname}');">{biticon ipackage="liberty" iname="collapsed" name="menu`$cname`img" iexplain="folder"}{tr}{$chdata.name}{/tr}</a>
			{else}
				<li><a class="head" href="javascript:toggle('menu{$cname}');">{tr}{$chdata.name}{/tr}</a>
			{/if} 
			{assign var=opensec value='y'}
			<div {if $menu_info.type eq 'd' and $smarty.cookies.$cname ne 'o'}style="display:none;"{else}style="display:block;"{/if} id="menu{$cname}"><ul>
		{else}
			<li><a class="item" href="{$chdata.url|escape}">{tr}{$chdata.name}{/tr}</a></li>
		{/if}
		{if $gBitSystem->isFeatureActive( 'feature_menusfolderstyle' )}
			<script language="Javascript" type="text/javascript">
				setfoldericonstate('menu{$cname}', '{$menu_info.type}');
			</script>
		{/if}
	{/foreach}
{else}
	{foreach key=pos item=chdata from=$channels}
		{if $chdata.type eq 's'}
			<li><a class="item" href="{$chdata.url|escape}">{tr}{$chdata.name}{/tr}</a></li>
		{else}
			<li><a class="item" href="{$chdata.url|escape}">{tr}{$chdata.name}{/tr}</a></li>
		{/if}
	{/foreach}
{/if}
</ul></div>
</li></ul>
{/strip}
