<div class="admin userimages">
	<div class="header">
		<h1>{tr}Upload Your Images{/tr}</h1>
	</div>

	{formfeedback error=$errorMsg success=$successMsg}

	{include file="bitpackage:users/my_bitweaver_bar.tpl"}

	<div class="body">
		{form enctype="multipart/form-data" method="post" legend="Upload personal Portrait, Avatar and Logo"}
			{if $fHomepage}
				<input type="hidden" name="fHomepage" value="{$fHomepage}"/>
			{/if}
			<div class="row">
				{formlabel label="Self Portrait" for="fPortraitFile"}
				{if $gBitUser->mInfo.portrait_url}
					{forminput}
						<img src="{$gQueryUser->mInfo.portrait_url}?{php}print time();{/php}" alt="{tr}self portrait{/tr}" /><br />
						<input type="submit" value="{tr}delete self portrait{/tr}" name="fSubmitDeletePortait" id="fSubmitDeletePortait"/>
					{/forminput}
				{/if}
				{forminput}
					<input name="fPortraitFile" id="fPortraitFile" type="file" />
					{formhelp note="Upload a personal photo to be displayed on your personal page."}
				{/forminput}
			</div>

			<div class="row" id="avatarfilerow">
				{formlabel label="Upload Avatar" for="avatarfile"}
				{forminput}
					<input name="fAvatarFile" type="file" id="avatarfile" />
					{formhelp note=""}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Avatar" for="fAutoAvatar"}
				{if $gQueryUser->mInfo.avatar_url}
					{forminput}
						<img src="{$gQueryUser->mInfo.avatar_url}?{php}print time();{/php}" alt="{tr}avatar{/tr}" /><br />
						<input type="submit" value="{tr}delete avatar{/tr}" name="fSubmitDeleteAvatar" />
					{/forminput}
				{/if}
				{forminput}
					<label>{tr}Create avatar automatically from self portrait{/tr}&nbsp;
					<input type="checkbox" name="fAutoAvatar" checked="checked" id="fAutoAvatar" onclick="showHideAvatar(this.form );" /></label>
					{formhelp note="Upload a small image or icon to be displayed next to your name on your forum and blog postings."}
				{/forminput}
			</div>

			<div class="row">
				{formlabel label="Logo" for="fLogoFile"}
				{if $gBitUser->mInfo.logo_url}
					{forminput}
						<img src="{$gQueryUser->mInfo.logo_url}?{php}print time();{/php}" alt="{tr}logo{/tr}" /><br />
						<input type="submit" value="{tr}delete logo{/tr}" id="fSubmitDeleteLogo" name="fSubmitDeleteLogo" />
					{/forminput}
				{/if}
				{forminput}
					<input name="fLogoFile" id="fLogoFile" type="file" />
					{formhelp note="Upload an image that will be shown on your personal page. This could possibly be a corporate image."}
				{/forminput}
			</div>

			<div class="row submit">
				<input type="submit" name="fSubmitBio" value="{tr}Save Changes{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .userimages -->

{* this javascript MUST go after the picform above! xoxo spider *}
{literal}
<script type='text/javascript'>
<!--
    function showHideAvatar() {
        var viz;
        if( document.getElementById( "fAutoAvatar" ).checked ) {
            vis = "hidden";
        } else {
            vis = "visible";
        }
        document.getElementById( "avatarfilerow" ).style.visibility = vis;
    }
 	showHideAvatar();
-->
</script>
{/literal}
