<div class="edit themes">
	<div class="header">
		<h1>{tr}Theme Configuration{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback success=$successMsg error=$errorMsg}

		{if $usingCustomTheme}
			<p>
				{tr}Use the form below to alter your custom theme. You can edit the css directly by using the
				text area. You can also manage the images used by your custom theme (for things like borders, backgrounds
				and the like). If there is a built in theme which you would like to base your custom theme on, you can
				do so by selecting it from the drop down list and clicking the "Reset CSS" button. If you want you can
				<a href="{$smarty.const.USERS_PKG_URL}assigned_modules.php">use one of the built in themes instead</a>.{/tr}
			</p>

			{form legend="CSS File Data"}
				<div class="control-group">
					{formlabel label="Load CSS File" for="reset"}
					{forminput}
						<select name="resetStyle">
							{section name=ix loop=$styles}
								<option value="{$styles[ix]|escape}" {if $assignStyle eq $styles[ix]}selected="selected"{/if}>{$styles[ix]}</option>
							{/section}
						</select>
						{formhelp note="Loading a CSS file will erase any changes you have made so far and will replace it with the contents of the selected css file."}
					{/forminput}
				</div>

				<div class="control-group submit">
					<input type="submit" class="btn" name="fResetCSS" id="reset" value="Reset CSS File" onclick="return confirm('{tr}Are you sure you want to reset your CSS back to the defaults? Any changes you have made will be lost.{/tr}');" />
				</div>

				<div class="control-group">
					<textarea name="textData" rows="42" cols="50">{$data|escape}</textarea>
				</div>

				<div class="control-group submit">
					<input type="submit" class="btn" name="fSaveCSS" value="Save" />
					<input type="submit" class="btn" name="fCancelCSS" value="Cancel" />
				</div>
			{/form}

			{form enctype="multipart/form-data"}
				{legend legend="Upload new Image"}
					<div class="control-group">
						{formlabel label="Upload Image" for="upload"}
						{forminput}
							<input type="hidden" name="MAX_FILE_SIZE" value="1024000" />
							<input type="file" name="fImgUpload" />
							{formhelp note=""}
						{/forminput}
					</div>

					<div class="control-group submit">
						<input type="submit" class="btn" value="Upload Image" name="fUpload" id="upload" />
					</div>
				{/legend}

				{if $imagesCount > 0}
					{legend legend="Images Used By Your Custom Theme"}
						{section name=ix loop=$themeImages}
							<div class="{cycle values='odd,even'}">
								{$themeImages[ix]} <input name="fDeleteImg[{$themeImages[ix]}]" class="icon" type="image" src="{$smarty.const.LIBERTY_PKG_URL}icons/delete_small.gif" title="{tr}Remove{/tr}" alt="{tr}Remove{/tr}" onclick="return confirm('Are you sure you want to delete {$themeImages[ix]}?');" />
								<br />
								<img class="icon" src="{$gQueryUser->getStorageURL('theme/images/',$gQueryUser->mUserId,'')}{$themeImages[ix]}" title="{$themeImages[ix]}" alt="{tr}Preview Image{/tr}" />
							</div>
						{/section}
					{/legend}
				{/if}
			{/form}
		{/if}
	</div><!-- end .body -->
</div><!-- end .themes -->
