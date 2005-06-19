{strip}
<div class="floaticon">{bithelp}</div>

<div class="display usertasks">
	<div class="header">
		<h1>{tr}Tasks{/tr}</h1>
	</div>

	{include file="bitpackage:users/my_bitweaver_bar.tpl"}

	<div class="body">
		<div class="navbar">
			<ul>
				<li><a href="{$gBitLoc.USERS_PKG_URL}tasks.php?tasks_use_dates=y">{tr}Use dates{/tr}</a></li>
				<li><a href="{$gBitLoc.USERS_PKG_URL}tasks.php?tasks_use_dates=n">{tr}All tasks{/tr}</a></li>
			</ul>
		</div>

		<div class="clear"></div>

		{form}
			<table class="data">
				<caption>{tr}Your Tasks{/tr}</caption>
				<tr>
					<th style="width:2%;">&nbsp;</th>
					<th style="width:55%;">{smartlink ititle="Title" isort=title offset=$offset tasks_use_dates=$tasks_use_dates}</th>
					<th style="width:15%;">{smartlink ititle="Start" isort=date offset=$offset tasks_use_dates=$tasks_use_dates}</th>
					<th style="width:3%;">{smartlink ititle="Priority" isort=priority offset=$offset tasks_use_dates=$tasks_use_dates idefault=1 iorder=desc}</th>
					<th style="width:25%;">{smartlink ititle="Percentage Done" isort=percentage offset=$offset tasks_use_dates=$tasks_use_dates}</th>
				</tr>

				{section name=user loop=$channels}
					<tr {if $channels[user].status eq 'c'}style="text-decoration:line-through;"{/if} class="{cycle values='odd,even'} prio{$channels[user].priority}">
						<td><input type="checkbox" name="task[{$channels[user].task_id}]" /></td>
						<td><a href="{$gBitLoc.USERS_PKG_URL}tasks.php?task_use_dates={$task_use_dates}&amp;task_id={$channels[user].task_id}&amp;offset={$offset}&amp;sort_mode={$sort_mode}&amp;find={$find}#tasks">{$channels[user].title}</a></td>
						<td {if $channels[user].status eq 'c'}style="text-decoration:line-through;"{/if} class="prio{$channels[user].priority}">{$channels[user].date|bit_short_date}</td>
						<td style="text-align:right;{if $channels[user].status eq 'c'}text-decoration:line-through;{/if}" class="prio{$channels[user].priority}">{$channels[user].priority}</td>
						<td style="text-align:right;{if $channels[user].status eq 'c'}text-decoration:line-through;{/if}" class="prio{$channels[user].priority}">
							<select name="task_perc[{$channels[user].task_id}]">
								{section name=zz loop=$percs}
									<option value="{$percs[zz]|escape}" {if $channels[user].percentage eq $percs[zz]}selected="selected"{/if}>{$percs[zz]}%</option>	
								{/section}
							</select>
						</td>
					</tr>
				{sectionelse}
					<tr class="norecords">
						<td colspan="6">{tr}No tasks entered{/tr}</td>
					</tr>
				{/section}

				<tr>
					<td colspan="4">
						<input type="submit" name="delete" value="{tr}Delete{/tr}" />
						<input type="submit" name="complete" value="{tr}Done{/tr}" />
						<input type="submit" name="open" value="{tr}Not Done{/tr}" />
					</td>
					<td align="right">
						<input type="submit" name="update" value="{tr}Update{/tr}" />
					</td>
				</tr>
			</table>
		{/form}

		{pagination}

		{minifind}

		<a name="tasks"></a>
		{form legend="Add or Edit a Task"}
			<input type="hidden" name="task_id" value="{$task_id}" />
			<input type="hidden" name="tasks_use_dates" value="{$tasks_use_dates}" />
			<input type="hidden" name="Date_Day" value="{$Date_Day}" />
			<input type="hidden" name="Date_Month" value="{$Date_Month}" />
			<input type="hidden" name="Date_Year" value="{$Date_Year}" />


			<div class="row">
				{formlabel label="Title" for="title"}
				{forminput}
					<input type="text" name="title" id="title" value="{$info.title|escape}" />
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Description" for="description"}
				{forminput}
					<textarea rows="10" cols="80" id="description" name="description">{$info.description|escape}</textarea>
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Start Date"}
				{forminput}
					{html_select_date time=$info.date end_year="+1"}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Status" for="status"}
				{forminput}
					<select name="status" id="status">
						<option value="o" {if $info.status eq 'o'}selected="selected"{/if}>{tr}open{/tr}</option>
						<option value="c" {if $info.status eq 'c'}selected="selected"{/if}>{tr}completed{/tr}</option>
					</select>
					{if $info.status eq 'c'}
						{$info.completed|bit_short_date}
					{/if}
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Priority" for="priority"}
				{forminput}
					<select name="priority" id="priority">
						<option value="1" {if $info.priority eq 1}selected="selected"{/if}>{tr}1{/tr}</option>
						<option value="2" {if $info.priority eq 2}selected="selected"{/if}>{tr}2{/tr}</option>
						<option value="3" {if $info.priority eq 3}selected="selected"{/if}>{tr}3{/tr}</option>
						<option value="4" {if $info.priority eq 4}selected="selected"{/if}>{tr}4{/tr}</option>
						<option value="5" {if $info.priority eq 5}selected="selected"{/if}>{tr}5{/tr}</option>
					</select>
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Percentage Completed" for="percentage"}
				{forminput}
					{html_options values="$comp_array" output="$comp_array_p" name=percentage selected="$info.percentage" id=percentage}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" name="save" value="{tr}Save{/tr}" />
			</div>
		{/form}

	</div><!-- end .body -->
</div><!-- end .tasks -->
{/strip}
