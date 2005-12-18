<div class="admin userfiles">
<div class="header">
	<h1>{tr}My Files{/tr}</h1>
</div>

<div class="body">
<div class="help box">
	<div class="boxcontent">
		{tr}These are all files you have uploaded{/tr}
	</div>
</div>
{if $numUserFiles < 1}No files found{/if}

<table class="panel">
	<tr><th>Thumbnail</th><th>Attachment Tag</th><th>Actions</th></tr>
	{foreach from=$userFiles item=userFile}
		<tr>
			<td><a href="{$userFile.source_url}"><img src="{$userFile.thumbnail_url.small}"/></a></td>
			<td>{$userFile.wiki_plugin_link}</td>
			<td><a href="javascript:return confirm('{tr}Are you sure you want to delete {$userFile.filename}? It will be removed from all content it is attached to.{/tr}','{$smarty.const.USERS_PKG_URL}my_files.php?deleteAttachment={$userFile.attachment_id}')">{biticon ipackage="liberty" iname="delete" iexplain="Delete"}</a></td>
		</tr>
		<tr><td colspan="3">{$userFile.filename}</td></tr>
		<tr><td colspan="3">&nbsp;</td></tr>
	{/foreach}
</table>
<h2>Total Disk Usage: {$diskUsage} bytes</h2>
</div>

</div>
