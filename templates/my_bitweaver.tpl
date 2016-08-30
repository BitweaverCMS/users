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

		<div class="panel-group row">
			{assign var="i" value="0"}
			{foreach key=key item=menu from=$gBitSystem->mAppMenu.bar}
				{if $menu.menu_title && $menu.index_url && $menu.menu_template && !$menu.is_disabled}
					{assign var="i" value="`$i+1`"}
					<div class="col-md-3 col-sm-6 col-xs-12">
						<div class="panel panel-default">
							<div class="panel-heading {$key}-menu">{biticon iname="pkg_`$key`" ipackage=$key class="menuicon" style="height:24px"} {$menu.menu_title|capitalize}</div>
							<div class="panel-body">{include file=$menu.menu_template packageMenuClass="unstyled"}</div>
						</div>
					</div>
					{if $i%4==0}
						{* Add the extra clearfix for only the required viewport *}
						<div class="clearfix visible-md"></div>
						<div class="clearfix visible-lg"></div>
					{/if}
					{if $i%2==0}
						{* Add the extra clearfix for only the required viewport *}
						<div class="clearfix visible-sm"></div>
					{/if}
				{/if}
			{/foreach}
		</div>
	</div><!-- end .body -->
</div><!-- end .my -->

{/strip}
