{strip}

<div class="floaticon">{bithelp}</div>
<div class="display my">
	<div class="header">
		<h1>{$gBitSystem->getConfig('site_title')} {tr}Dashboard{/tr} </h1>
	</div>

	<div class="body">

{*
	this is where we should add the following tabs:
	file manager
	quota manager - currently on separate page, but i believe this would be good in here.
	listing of submitted / edited stuff - similar to the old tiki personal tiki stuff.
*}

	<table class="large-100 menutable">
		<tr>
			{assign var="i" value="1"}
			{foreach key=key item=menu from=$gBitSystem->mAppMenu}
				{if $menu.menu_title && $menu.index_url && $menu.menu_template && !$menu.is_disabled}
					<td style="large-33;vertical-align:top;">
						<h1>{$menu.menu_title}</h1>
							{include file=$menu.menu_template packageMenuClass="unstyled"}
					</td>
					{if not ($i++ mod 3)}
						</tr><tr>
					{/if}
				{/if}
			{/foreach}
		</tr>
	</table>
	</div><!-- end .body -->
</div><!-- end .my -->

{/strip}
