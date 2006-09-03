{* $Header: /cvsroot/bitweaver/_bit_users/templates/users_admin.tpl,v 1.6 2006/09/03 20:14:58 squareing Exp $ *}
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
				<table class="data" style="width:400px">
					<tr class="error"><th>{tr}Row{/tr}</th><th>{tr}Reason{/tr}</th></tr>
					{foreach key=row from=$discardlist item=reason}
						<tr class="{cycle values="odd,even"}"><td>{$row}</td><td>{$reason}</td></tr>
					{/foreach}
				</table>
				<br />
			{/if}
		{/if}

		{jstabs}
			{jstab title="List of Users"}
				{include file="bitpackage:users/users_list.tpl"}
			{/jstab}

			{jstab title="Add User"}
				{formfeedback success=$addSuccess}
				{form legend="Add a new user"}
					<input type="hidden" name="tab" value="useradd" />
					<div class="row">
						{formlabel label="Real Name" for="real_name"}
						{forminput}
							<input type="text" name="real_name" id="real_name" value="{$newUser.real_name}" />
						{/forminput}
					</div>

					<div class="row">
						{formfeedback error=$errors.login}
						{formlabel label="User" for="login"}
						{forminput}
							<input type="text" name="login" id="login" value="{$newUser.login}"  />
						{/forminput}
					</div>

					<div class="row">
						{formfeedback error=$errors.password}
						{formlabel label="Password" for="password"}
						{forminput}
							<input type="password" name="password" id="password" value="{$newUser.password}"  />
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="Repeat Password" for="password2"}
						{forminput}
							<input type="password" name="password2" id="password2" value="{$newUser.password2}"  />
						{/forminput}
					</div>

					<div class="row">
						{formfeedback error=$errors.email}
						{formlabel label="Email" for="email"}
						{forminput}
							<input type="text" name="email" id="email" size="30" value="{$newUser.email}" />
						{/forminput}
					</div>

					<div class="row">
						{if $defaultGroupId eq ''}
							{formfeedback error="No default group is currently set. Please set one in the Administration --&gt; Users --&gt; <a href=\"`$smarty.const.USERS_PKG_URL`admin/edit_group.php\">Groups and Permissions</a> page"}
						{/if}
						{formlabel label="User will be added to the following group" for=""}
						{forminput}
							{$defaultGroupName} <a href="{$smarty.const.USERS_PKG_URL}admin/edit_group.php?group_id={$defaultGroupId}">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="change settings"}</a>
							{formhelp note="This is the group that is selected as the default group. If you would like to change the default group, please click on the edit icon and set a different group as default group."}
						{/forminput}
					</div>

					<div class="row">
						{formlabel label="<a href=\"javascript:genPass('genepass','password','password2');\">{tr}Generate a password{/tr}</a>" for="email"}
						{forminput}
							<input id="genepass" type="text" />
							{formhelp note="You can use this link to create a random password. Make sure you pass the information on to the user."}
						{/forminput}
					</div>

					{*include file="bitpackage:liberty/edit_services_inc.tpl serviceFile=content_edit_mini_tpl*}

					<div class="row submit">
						<input type="submit" name="newuser" value="{tr}Add User{/tr}"{if $defaultGroupId eq ''} disabled="disabled"{/if} />
					</div>
				{/form}
			{/jstab}

			{jstab title="Advanced"}
				{form legend="Batch user addition" enctype="multipart/form-data"}
					<div class="row">
						{formlabel label="Batch upload (CSV file)" for="csvlist"}
						{forminput}
							<input type="file" name="csvlist" id="csvlist" /><br />
							<label><input type="checkbox" name="overwrite" checked="checked" /> {tr}Overwrite existing users{/tr}</label>
							{formhelp note="You can batch import users by uploading a CSV (comma-separated values) file. The CSV file needs to have the column names in the first record. The column titles must match with fields in 'users_users' table. Login, password and email are required fields. If an unexistant field is specified, it's ignored."}
						{/forminput}
					</div>

					<div class="row submit">
						<input type="submit" name="batchimport" value="{tr}Import{/tr}" />
					</div>
				{/form}
			{/jstab}
		{/jstabs}
	</div><!-- end .body -->
</div><!-- end .users -->

{/strip}
