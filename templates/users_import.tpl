{* $Header$ *}
{strip}
<div class="floaticon">{bithelp}</div>

<div class="admin users">
	<div class="header">
		<h1>{tr}Admin users{/tr}</h1>
	</div>

	<div class="body">
		{if (($added ne "") || ($discarded ne "")) }
			<h2>{tr}Batch Upload Results{/tr}</h2>
			{if $added}
				{formfeedback success="`$added` Users Added"}
			{/if}

			{if $discarded ne '' }
				{formfeedback error="`$discarded` Users Rejected"}
				<table class="table data" style="width:400px">
					<tr class="error"><th>{tr}Row{/tr}</th><th>{tr}Reason{/tr}</th></tr>
					{foreach key=row from=$discardlist item=reason}
						<tr class="{cycle values="odd,even"}"><td>{$row}</td><td>{$reason}</td></tr>
					{/foreach}
				</table>
				<br />
			{/if}
		{/if}

		{jstabs}
			{jstab title="Advanced"}
				{form legend="Batch user addition" enctype="multipart/form-data"}
					<div class="form-group">
						{formlabel label="Batch upload (CSV file)" for="csvlist"}
						{forminput}
							<input type="file" name="csvlist" id="csvlist" />
							{formhelp note="You can batch import users by uploading a CSV (comma-separated values) file. The file needs to have the column names in the first line. The column titles need to match with fields in 'users_users' table. Login, password and email are required fields, however if you leave the password field empty a new password will be auto generated. You can also import a MD5 hash as password, like from phpBB2, it need to be put in the 'hash' column. In such case it override other options and it's assumed the user or at least admin knows the password. Currently you can't import custom fields and if a non-existent field is specified, it's ignored."}
						{/forminput}
					</div>

					<div class="form-group">
						{formlabel label="Delimiter" for="delimiter"}
						{forminput}
							<input type="text" size="3" name="delimiter" id="delimiter" value="," />
							{formhelp note="Set the delimiter of the file. You can not use tab as delimiter."}
						{/forminput}
					</div>

					<div class="form-group">
						<label class="checkbox">
							<input type="checkbox" name="overwrite" id="overwrite" />Overwrite existing users
							{formhelp note=""}
						</label>
					</div>

					<div class="form-group">
						<label class="checkbox">
							<input type="checkbox" name="admin_verify_user" id="admin_verify_user" />Validate users by email
							{formhelp note="This will email the user a validation url with a temporary one time password. On validation the user is forced to choose a new password."}
						</label>
					</div>

					<div class="form-group">
						<label class="checkbox">
							<input type="checkbox" name="admin_verify_email" id="admin_verify_email" />Validate email address
							{formhelp note="This feature should be used only when you need the maximum security and should be used with discretion. If a visitor's email server is not responding, they will not be able to register. You also must have a valid sender email to use this feature."}
						</label>
					</div>

					<div class="form-group">
						<label class="checkbox">
							<input type="checkbox" name="admin_noemail_user" id="admin_noemail_user" />Don't email imported users
							{formhelp note="If you for some reason don't want to email imported users the login and password, or validation url."}
						</label>
					</div>

					<div class="form-group submit">
						<input type="submit" class="btn btn-default" name="batchimport" value="{tr}Import{/tr}" />
					</div>
				{/form}
			{/jstab}
		{/jstabs}
	</div><!-- end .body -->
</div><!-- end .users -->

{/strip}
