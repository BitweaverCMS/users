{strip}
<div class="floaticon">{bithelp}</div>
<div class="listing useractivity">
	<div class="header">
		<h1>{tr}User Activity{/tr}</h1>
	</div>

	<div class="body">
		<table class="table data">
			<caption>{tr}Active users{/tr}</caption>
			<tr>
				<th class="item">{tr}Name{/tr} ({tr}ID{/tr})</th>
				<th class="item">{tr}Last Access{/tr} / {tr}IP{/tr}</th>
				<th class="item">{tr}Browser{/tr}</th>
			</tr>

			{section name=ix loop=$userActivity}
				<tr class="{cycle values="odd,even"}">
					<td class="item" style="width:150px;">{displayname hash=$userActivity[ix]}<br/>(<a href="{$smarty.server.SCRIPT_NAME}?user_id={$userActivity[ix].user_id}">{$userActivity[ix].user_id}</a>)</td>
					<td class="item" style="width:150px;">{$userActivity[ix].last_get|bit_short_datetime}<br/><a href="{$smarty.server.SCRIPT_NAME}?ip={$userActivity[ix].ip}">{$userActivity[ix].ip|escape}</a></td>
					<td class="item">{$userActivity[ix].user_agent|escape}</td>
				</tr>
			{sectionelse}
				<tr>
					<td class="norecords" colspan="5">{tr}No user activity found{/tr}</td>
				</tr>
			{/section}
			{if $smarty.request.user_id}
				<tr class="{cycle values="odd,even"}">
					<td class="item" style="width:150px;">{displayname hash=$userInfo}<br/>(<a href="{$smarty.server.SCRIPT_NAME}?user_id={$userInfo.user_id}">{$userInfo.user_id}</a>)</td>
					<td class="item" style="width:150px;">{$userInfo.registration_date|bit_short_datetime}</td>
					<td class="item">{tr}Registered{/tr}</td>
				</tr>
			{/if}

			{if $watches}
				<tr>
					<td><input src="{booticon iname="icon-trash" ipackage="icons" iexplain=remove url=TRUE}" type="image" name="delete" value="{tr}delete{/tr}" /></td>
				</tr>
			{/if}
		</table>
		{pagination}
	</div><!-- end .body -->
</div><!-- end .useractivity -->
{/strip}
