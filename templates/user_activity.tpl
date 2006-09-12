{strip}
<div class="floaticon">{bithelp}</div>
<div class="listing useractivity">
	<div class="header">
		<h1>{tr}User Activity{/tr}</h1>
	</div>

	<div class="body">
		{form}
			<table class="data">
				<caption>{tr}Active users{/tr}</caption>
				<tr>
					<th class="item">{tr}Name{/tr} ({tr}ID{/tr})</th>
					<th class="item">{tr}Last Access{/tr} / {tr}IP{/tr}</th>
					<th class="item">{tr}Browser{/tr}</th>
				</tr>

				{section name=ix loop=$userActivity}
					<tr class="{cycle values="odd,even"}">
						<td class="item" style="width:150px;">{displayname hash=$userActivity[ix] nolink=1}<br/>(<a href="{$smarty.server.PHP_SELF}?user_id={$userActivity[ix].user_id}">{$userActivity[ix].user_id}</a>)</td>
						<td class="item" style="width:150px;">{$userActivity[ix].last_get|bit_short_datetime}<br/><a href="{$smarty.server.PHP_SELF}?ip={$userActivity[ix].ip}">{$userActivity[ix].ip|escape}</a></td>
						<td class="item">{$userActivity[ix].user_agent|escape}</td>
					</tr>
				{sectionelse}
					<tr>
						<td class="norecords" colspan="5">no records found</td>
					</tr>
				{/section}

				{if $watches}
					<tr>
						<td><input src="{biticon ipackage="icons" iname="edit-delete" iexplain=remove url=TRUE}" type="image" name="delete" value="{tr}delete{/tr}" /></td>
					</tr>
				{/if}
			</table>
			{pagination}
		{/form}
	</div><!-- end .body -->
</div><!-- end .userwatches -->
{/strip}
