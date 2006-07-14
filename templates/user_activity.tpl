{strip}
<div class="floaticon">{bithelp}</div>
<div class="listing useractivity">
	<div class="header">
		<h1>{tr}User Activity{/tr}</h1>
	</div>

	<div class="body">
		{form}
			<table class="panel">
				<caption>{tr}Active users{/tr}</caption>
				<tr>
					<th>{tr}Name{/tr} ({tr}ID{/tr})</th>
					<th>{tr}IP{/tr}</th>
					<th>{tr}Last Access{/tr}</th>
					<th>{tr}Browser{/tr}</th>
				</tr>

				{section name=ix loop=$userActivity}
					<tr class="{cycle values="odd,even"}">
						<td>{displayname hash=$userActivity[ix]} ({$userActivity[ix].user_id})</td>
						<td>{$userActivity[ix].ip|escape}</td>
						<td>{$userActivity[ix].last_get|bit_short_datetime}</td>
						<td>{$userActivity[ix].user_agent|escape}</td>
					</tr>
				{sectionelse}
					<tr>
						<td class="norecords" colspan="5">no records found</td>
					</tr>
				{/section}

				{if $watches}
					<tr>
						<td><input src="{biticon ipackage=liberty iname=delete iexplain=remove url=TRUE}" type="image" name="delete" value="{tr}delete{/tr}" /></td>
					</tr>
				{/if}
			</table>
		{/form}
	</div><!-- end .body -->
</div><!-- end .userwatches -->
{/strip}
