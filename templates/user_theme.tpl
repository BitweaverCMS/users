{*** Javascript ***}
{literal}
<script LANGUAGE="JavaScript">
<!--//
function confirmform(text)
{
var agree=confirm(text);

if (agree)
return true;
else
return false;
}
// -->
</script>
{/literal}
{*** End Javascript ***}

<a class="pagetitle" href="{$smarty.const.USERS_PKG_URL}theme.php">{tr}Theme Configuration{/tr}</a><br /><br />
{section name=ix loop=$successMsg}
<div style="color: green">{$successMsg[ix]}</div>
{/section}
{section name=ix loop=$errorMsg}
<div style="color: red">{$errorMsg[ix]}</div>
{/section}

{if $usingCustomTheme}
{*** User is using a custom theme. We will display the form for editing CSS and uploading images ***}
<p>Use the form below to alter your custom theme.  You can edit the css directly by using the
text area. You can also manage the images used by your custom theme (for things like borders, backgrounds
and the like). If there is a built in theme which you would like to base your custom theme on, you can
do so by selecting it from the drop down list and clicking the "Reset CSS" button. If you want you can <a href="{$smarty.const.USERS_PKG_URL}assigned_modules.php">use one of the built in themes instead</a>.</p>

{*** CSS Editing textarea ***}
<div>
	<form method="post" action="{$PHP_SELF}">
	<div style="padding:4px;border-bottom:1px solid #c3b3a3;">
		<textarea name="textData" rows="42" cols="80" wrap="virtual" style="padding:7px;padding-right:0;">{$data|escape}</textarea>
	</div>
	<div style="">
		<br/>
		<span>
			<input type="submit" name="fSaveCSS" value="Save">
			<input type="submit" name="fCancelCSS" value="Cancel">
		</span>
		<span style="float: right">

				<input type="submit" name="fResetCSS" value="Reset CSS" onclick="return confirmform('Are you sure you want to reset your CSS back to the defaults? Any changes you have made will be lost.');">
				to the
				<select name="resetStyle">
				{section name=ix loop=$styles}
					<option value="{$styles[ix]|escape}" {if $assignStyle eq $styles[ix]}selected="selected"{/if}>{$styles[ix]}</option>
				{/section}
				</select>
				theme

		</span>
	</div>
	</form>
</div>
{*** End CSS Editing text area ***}

{*** Theme Image Management ***}
<div>
<br /><br />
<h3>Images Used By Your Custom Theme</h3>
<br />
<form enctype="multipart/form-data" method="post" action="{$PHP_SELF}">
<table cellpadding="3">
	{if $imagesCount > 0}
	<tr>
		<th>Image</th>
		<th>Action</th>
	</tr>
	{section name=ix loop=$themeImages}
	<tr bgcolor="{cycle values="#eeeeee,#dddddd"}">
		<td width="200px" cellpadding="3">{$themeImages[ix]}<input name="fDeleteImg[{$themeImages[ix]}]" class="icon" type="image" src="{$smarty.const.LIBERTY_PKG_URL}icons/delete_small.gif" title="{tr}Remove{/tr}" alt="{tr}Remove{/tr}" onclick="return confirm('Are you sure you want to delete {$themeImages[ix]}?');"/></td>
		<td cellpadding="3">
			<img class="icon" src="{$gQueryUser->getStorageURL('theme/images/',$gQueryUser->mUserId,'')}{$themeImages[ix]}" title="{tr}Preview{/tr}" alt="{tr}Preview{/tr}" onclick="javascript:popup('preview_image.php?fImg={$customCSSImageURL}/{$themeImages[ix]}')">
		</td>
	</tr>
	{/section}
	{else}
		<font color="red">No Images Used In This Theme</font>
	{/if}
</table>
<br />
<input type="hidden" name="MAX_FILE_SIZE" value="1024000">
Upload Image: <input type="file" name="fImgUpload"> <br /> <br/>
<input type="submit" value="Upload Image" name="fUpload">
</form>
</div>
{*** End Theme Image Management ***}

{/if}
