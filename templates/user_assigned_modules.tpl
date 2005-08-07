{strip}
<div class="floaticon">{bithelp}</div>

<div class="admin user modules">
	<div class="header">
		<h1>{tr}Configure Homepage Layout{/tr}</h1>
	</div>

	{include file="bitpackage:users/my_bitweaver_bar.tpl"}

	<div class="body">

		{if $gBitSystem->isFeatureActive( 'feature_user_layout' ) or $gBitSystemPrefs.feature_user_layout eq 'h'}
			<table style="width:100%" cellpadding="5" cellspacing="0" border="0">
				<caption>{tr}Your HomePage Layout{/tr}</caption>
				<tr>
					{foreach from=$layoutAreas item=area key=colkey }
						<td style="width:33%" valign="top">
							<table class="data" style="width:100%">
								<caption>{tr}{$colkey} column{/tr}</caption>
								<tr>
									<th>{tr}Module{/tr}</th>
								</tr>
								{section name=ix loop=$modules.$area}
									<tr class="{cycle values="even,odd"}">
										<td>
											{tr}Position {$modules.$area[ix].ord}{/tr}<br />
											{$modules.$area[ix].name}
											<div style="text-align:center;">
												{smartlink ititle="Up" ibiticon="liberty/move_up" fMove=up fPackage=$fPackage fModule=`$modules.$area[ix].module_id`}
												{smartlink ititle="Down" ibiticon="liberty/move_down" fMove=down fPackage=$fPackage fModule=`$modules.$area[ix].module_id`}
												{if $colkey ne 'center'}
													{smartlink ititle="Move to Right" ibiticon="liberty/move_$colkey" fMove=$colkey fPackage=$fPackage fModule=`$modules.$area[ix].module_id`}
												{/if}
												{if $column[ix].type ne 'P'}
													{smartlink ititle="Unassign" ibiticon="liberty/delete_small" ionclick="return confirm('Are you sure you want to remove `$modules.$area[ix].name`?');" fMove=unassign fPackage=$fPackage fModule=`$modules.$area[ix].module_id`}
												{/if}
											</div>
										</td>
									</tr>
								{sectionelse}
									<tr class="{cycle}" >
										<td colspan="3" align="center">
											{if $colkey eq 'center'}{tr}Default{/tr}{else}{tr}None{/tr}{/if}
										</td>
									</tr>
								{/section}
							</table>
						</td>
					{/foreach}
				</tr>
			</table>
		{/if}

		{jstabs}
			{if $gBitSystem->isFeatureActive( 'feature_user_layout' ) or $gBitSystemPrefs.feature_user_layout eq 'h' and $canassign eq 'y'}
				{jstab title="Assign Side Piece"}
					{form legend="Assign Side Piece"}
						{if $fEdit && $fAssign.name}
							<input type="hidden" name="assign_name" value="{$fAssign.name}" />{$assign.name}
						{else}
							<div class="row">
								{formlabel label="Module" for="module_rsrc"}
								{forminput}
									<select name="fAssign[module_rsrc]">
										{section name=ix loop=$assignables.border}
											<option value="{$assignables.border[ix].module_rsrc|escape}">{$assignables.border[ix].name}</option>
										{sectionelse}
											<option>{tr}No records found{/tr}</option>
										{/section}
									</select>
								{/forminput}
							</div>
						{/if}

						<div class="row">
							{formlabel label="Position" for="pos"}
							{forminput}
								<select name="fAssign[pos]" id="pos">
									<option value="l" {if $fAssign.pos eq 'l'}selected="selected"{/if}>{tr}left column{/tr}</option>
									<option value="r" {if $fAssign.pos eq 'r'}selected="selected"{/if}>{tr}right column{/tr}</option>
								</select>
								{formhelp note="Select the column this module should be displayed in."}
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Order" for="ord"}
							{forminput}
								<select name="fAssign[ord]" id="ord">
									{section name=ix loop=$orders}
										<option value="{$orders[ix]|escape}" {if $fAssign.ord eq $orders[ix]}selected="selected"{/if}>{$orders[ix]}</option>
									{/section}
								</select>
								{formhelp note="Select where within the column the module should be displayed."}
							{/forminput}
						</div>

						<div class="row submit">
							<input type="submit" name="fSubmitAssign" value="{tr}Add Module{/tr}" />
						</div>
					{/form}
				{/jstab}

				{jstab title="Assign center piece"}
					{form legend="Assign center piece"}
						<input type="hidden" name="fAssign[pos]" value="c" />

						<div class="row">
							{formlabel label="Center Piece" for="module"}
							{forminput}
								<select name="fAssign[module_rsrc]">
									{section name=ix loop=$assignables.center}
										<option value="{$assignables.center[ix].module_rsrc|escape}">{$assignables.center[ix].name}</option>
									{sectionelse}
										<option>{tr}No records found{/tr}</option>
									{/section}
								</select>
								{formhelp note="Pick the center bit you want to display when accessing this package."}
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Position"}
							{forminput}
								{tr}Center{/tr}
							{/forminput}
						</div>

						<div class="row">
							{formlabel label="Order" for="c_ord"}
							{forminput}
								<select name="fAssign[ord]" id="c_ord">
									{section name=ix loop=$orders}
										<option value="{$orders[ix]|escape}" {if $assign_order eq $orders[ix]}selected="selected"{/if}>{$orders[ix]}</option>
									{/section}
								</select>
								{formhelp note="Select where within the column the module should be displayed."}
							{/forminput}
						</div>

						<div class="row submit">
							<input type="submit" name="fSubmitAssign" value="{tr}Add Module{/tr}" />
						</div>
					{/form}
				{/jstab}
			{/if}

			{if $gBitSystem->isFeatureActive( 'feature_user_theme' ) or $gBitSystemPrefs.feature_user_theme eq 'h'}
				{jstab title="Select Theme"}
					{form legend="Select Theme"}
						<div class="row">
							{formlabel label="Theme" for="style"}
							{forminput}
								<select name="style" id="style">
									{section name=ix loop=$styles}
										<option value="{$styles[ix]|escape}" {if $assignStyle eq $styles[ix]}selected="selected"{/if}>{$styles[ix]}</option>
									{/section}
								</select>
								{formhelp note="Pick the theme for your personal Homepage."}
							{/forminput}
						</div>

					<div class="row submit">
						<input type="submit" value="{tr}Apply Theme{/tr}" name="fSubmitSetTheme">
					</div>
					{/form}
				{/jstab}
			{/if}
		{/jstabs}
	</div> {* end .body *}
</div> {* end .usermodules *}

{/strip}


{* old code. left here in case we need something... xing
for instance, i don't know where the page heading stuff is used.

<table width="100%">
{if $gBitSystem->isFeatureActive( 'feature_user_layout' ) or $gBitSystemPrefs.feature_user_layout eq 'h'}

{if $canassign eq 'y'}
<tr>
	<td valign="top">

<form action="{$smarty.const.USERS_PKG_URL}assigned_modules.php" method="post">
<table class="panel">
<caption>{tr}Assign Left Column{/tr}</caption>
<tr>
	<td>{tr}Module{/tr}:</td>
	<td>
		<select name="fAssign[module_rsrc]">
		{section name=ix loop=$assignables.border}
		<option value="{$assignables.border[ix].module_rsrc|escape}">{$assignables.border[ix].name}</option>
		{sectionelse}
			<option>{tr}No records found{/tr}</option>
		{/section}
		</select>
	</td>
</tr>
<tr>
	<td>
{tr}Column{/tr}:</td><td>
	<input type="hidden" name="fAssign[pos]" value="l" />
	{tr}left{/tr}
	</td>
</tr>
<tr>
	<td>{tr}Order{/tr}:</td>
	<td>
	<select name="fAssign[ord]">
	{section name=ix loop=$orders}
		<option value="{$orders[ix]|escape}">{$orders[ix]}</option>
	{/section}
	</select>
	</td>
</tr>
<tr class="panelsubmitrow">
	<td colspan="2"><input type="submit" name="fSubmitAssign" value="{tr}assign{/tr}" /></td>
</tr>
</table>
</form>

	</td>
	<td valign="top">

<form action="{$smarty.const.USERS_PKG_URL}assigned_modules.php" method="post">
<table class="panel">
<caption>{tr}Assign Center Column{/tr}</caption>
<tr>
	<td>{tr}Center Piece{/tr}:</td>
	<td>
		<select name="fAssign[module_rsrc]">
		{section name=ix loop=$assignables.center}
		<option value="{$assignables.center[ix].module_rsrc|escape}">{$assignables.center[ix].name}</option>
		{sectionelse}
			<option>{tr}No records found{/tr}</option>
		{/section}
		</select>
	</td>
</tr>
<tr>
	<td>{tr}Column{/tr}:</td>
	<td>{tr}Center{/tr}
		<input type="hidden" name="fAssign[pos]" value="c" />
	</td>
</tr>
<tr>
	<td>{tr}Order{/tr}:</td>
	<td>
	<select name="fAssign[ord]">
	{section name=ix loop=$orders}
		<option value="{$orders[ix]|escape}">{$orders[ix]}</option>
	{/section}
	</select>
	</td>
</tr>
<tr class="panelsubmitrow">
	<td colspan="2"><input type="submit" name="fSubmitAssign" value="{tr}assign{/tr}" /></td>
</tr>
</table>
</form>

	</td>
	<td valign="top">

<form action="{$smarty.const.USERS_PKG_URL}assigned_modules.php" method="post">
<table class="panel">
<caption>{tr}Assign Right Column{/tr}</caption>
<tr>
	<td>{tr}Module{/tr}:</td>
	<td>
		<select name="fAssign[module_rsrc]">
		{section name=ix loop=$assignables.border}
		<option value="{$assignables.border[ix].module_rsrc|escape}">{$assignables.border[ix].name}</option>
		{/section}
		</select>
	</td>
</tr>
<tr>
	<td>
{tr}Column{/tr}:</td><td>
	<input type="hidden" name="fAssign[pos]" value="r" />
{tr}right{/tr}
	</td>
</tr>
<tr>
	<td>{tr}Order{/tr}:</td>
	<td>
	<select name="fAssign[ord]">
	{section name=ix loop=$orders}
		<option value="{$orders[ix]|escape}">{$orders[ix]}</option>
	{sectionelse}
		<option>{tr}No records found{/tr}</option>
	{/section}
	</select>
	</td>
</tr>
<tr class="panelsubmitrow">
	<td colspan="2"><input type="submit" name="fSubmitAssign" value="{tr}assign{/tr}" /></td>
</tr>
</table>
</form>

{/if}

	</td>
</tr>
<tr><td colspan="3"><hr /></td></tr>
{/if}
<tr>
	<td colspan="2" valign="top">

	<form action="{$smarty.const.USERS_PKG_URL}assigned_modules.php" method="post">
<table class="panel">
<caption>{tr}Enter Homepage Heading{/tr}</caption>
<tr><td>{tr}Enter a maximum of 250 characters. You can include Wiki syntax.{/tr}</td></tr>
<tr>
	<td><textarea name="homeHeaderData" cols="60" rows="3">{$homeHeaderData}</textarea></td>
</tr>
<tr class="panelsubmitrow">
	<td><input type="submit" name="fSubmitSetHeading" value="{tr}Set Heading{/tr}" /></td>
</tr>
</table>
	</form>
</td>
<td style="vertical-align:top;">
{if $gBitSystem->isFeatureActive( 'feature_user_theme' ) || $gBitSystemPrefs.feature_user_theme eq 'h' }

	<form method="POST" action="{$smarty.const.USERS_PKG_URL}assigned_modules.php">
	<table class="panel">
		<caption>{tr}Select Theme for your Homepage{/tr}</caption>
		<tr><td>
		<select name="style">
		{section name=ix loop=$styles}
			<option value="{$styles[ix]|escape}" {if $assignStyle eq $styles[ix]}selected="selected"{/if}>{$styles[ix]}</option>
		{/section}
		</select>
		</td><td>
		<input type="submit" value="Change Theme" name="fSubmitSetTheme">
		</td></tr></table>
	</form>
{/if}
	</td>
</tr>
</table>

*}
