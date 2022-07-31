{strip}
<div class="floaticon">{bithelp}</div>
<div class="listing userwatches">
	<div class="header">
		<h1>{tr}User Watches{/tr}</h1>
	</div>

	<div class="body">
		{form id='formi' legend="Content type selection"}
			<div class="form-group">
				{formlabel label="List content type" for="event"}
				{forminput}
					<select name="event" id="event" onchange="javascript:document.getElementById('formi').submit();">
						<option value="" {if $smarty.request.event eq ''}selected="selected"{/if}>{tr}All{/tr}</option>
						{section name=ix loop=$events}
							<option value="{$events[ix]|escape}" {if $events[ix] eq $smarty.request.event}selected="selected"{/if}>{$events[ix]}</option>
						{/section}
					</select>
					{formhelp note=""}
				{/forminput}
			</div>
		{/form}

		{form}
			<table class="panel">
				<caption>{tr}Active watches{/tr}</caption>
				<tr>
					<th>{tr}Event{/tr}</th>
					<th>{tr}GUID{/tr}</th>
					<th>{tr}Object{/tr}</th>
				</tr>

				{section name=ix loop=$watches}
					<tr class="{cycle values="odd,even"}">
						<td><label><input type="checkbox" name="watch[{$watches[ix].hash}]" /> {$watches[ix].event}</label></td>
						<td>{$watches[ix].type}</td>
						<td><a href="{$watches[ix].url}">{$watches[ix].title|escape}</a></td>
					</tr>
				{sectionelse}
					<tr>
						<td class="norecords" colspan="3">no records found</td>
					</tr>
				{/section}

				{if $watches}
					<tr>
						<td><button class="btn btn-default btn-sm" name="delete" value="{tr}delete{/tr}">{booticon iname="fa-trash" iexplain=remove}</button></td>
					</tr>
				{/if}
			</table>
		{/form}
	</div><!-- end .body -->
</div><!-- end .userwatches -->
{/strip}
