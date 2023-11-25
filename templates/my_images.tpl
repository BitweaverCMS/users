{strip}
<div class="admin userimages">
	<div class="header">
		<h1>{tr}Upload Your Images{/tr}</h1>
	</div>

	{formfeedback error=$errorMsg success=$successMsg}

	<div class="body">
		{form enctype="multipart/form-data" legend="Upload personal Portrait, Avatar and Logo"}
			{if $gQueryUser->mUserId ne $gBitUser->mUserId}
				<input type="hidden" name="user_id" value="{$gQueryUser->mUserId}" />
			{/if}
			{if $fHomepage}
				<input type="hidden" name="fHomepage" value="{$fHomepage}"/>
			{/if}
			<div class="form-group">
				{formlabel label="Self Portrait" for="user_portrait_file"}
				{if $gQueryUser->mInfo.portrait_attachment_id}
					{forminput}
						<img src="{$gQueryUser->mInfo.portrait_url}?{php}print time();{/php}" alt="{tr}self portrait{/tr}" /><br />
						<input type="submit" class="btn btn-default" value="{tr}delete self portrait{/tr}" name="delete_portrait" id="delete_portrait"/>
					{/forminput}
				{/if}

				{forminput}
					<input name="user_portrait_file" id="user_portrait_file" type="file" />
					{formhelp note="Upload a personal photo to be displayed on your personal page."}
				{/forminput}
			</div>

			<div class="form-group" id="avatarfilerow">
				{formlabel label="Upload Avatar" for="avatarfile"}
				{forminput}
					<input name="user_avatar_file" type="file" id="avatarfile" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="form-group">
				{formlabel label="Avatar" for="user_auto_avatar"}
				{if $gQueryUser->mInfo.avatar_attachment_id}
					{forminput}
						<img src="{$gQueryUser->mInfo.avatar_url}?{php}print time();{/php}" alt="{tr}avatar{/tr}" /><br />
						<input type="submit" class="btn btn-default" value="{tr}delete avatar{/tr}" name="delete_avatar" />
					{/forminput}
				{/if}

				{forminput}
					<label><input type="checkbox" name="user_auto_avatar" checked="checked" id="user_auto_avatar" onclick="showHideAvatar(this.form );" /> {tr}Create avatar automatically from self portrait{/tr}</label>
					{formhelp note="Upload a small image or icon to be displayed next to your name on your forum and blog postings."}
				{/forminput}
			</div>

			<div class="form-group">
				{formlabel label="Logo" for="user_logo_file"}
				{if $gQueryUser->mInfo.logo_attachment_id}
					{forminput}
						<img src="{$gQueryUser->mInfo.logo_url}?{php}print time();{/php}" alt="{tr}logo{/tr}" /><br />
						<input type="submit" class="btn btn-default" value="{tr}delete logo{/tr}" id="delete_logo" name="delete_logo" />
					{/forminput}
				{/if}

				{forminput}
					<input name="user_logo_file" id="user_logo_file" type="file" />
					{formhelp note="Upload an image that will be shown on your personal page. This could possibly be a corporate image."}
				{/forminput}
			</div>

			<div class="form-group submit">
				<input type="submit" class="btn btn-default" name="store" value="{tr}Save Changes{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .userimages -->

{* this javascript MUST go after the picform above! xoxo spider *}
{literal}
<script>/* <![CDATA[ */
	function showHideAvatar() {
		var viz;
		if( document.getElementById( "user_auto_avatar" ).checked ) {
			vis = "hidden";
		} else {
			vis = "visible";
		}
		document.getElementById( "avatarfilerow" ).style.visibility = vis;
	}
	showHideAvatar();
/* ]]> */</script>
{/literal}
{/strip}
