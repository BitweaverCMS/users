<div class="floaticon">{bithelp}</div>
<div class="display userfiles">
<div class="header">
<h1>{tr}User Files{/tr}</h1>
</div>

{include file="bitpackage:users/my_bitweaver_bar.tpl"}

<div class="body">

<h2>{tr}User Files{/tr}</h2>

<table class="data">
	<tr>
		<th colspan='2'>
			<small>{tr}quota{/tr}</small>
		</th>
	</tr>
	<tr>
		<td>
			<table class="icon" height="20" 
			       width="200" style='background-color:#666666;'>
				<tr>
					<td style='background-color:red;' width="{$cellsize}">&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</table>
		</td>
		<td>
			<small>{$percentage}%</small>
		</td>
	</tr>
</table>

<br />

<form action="{$gBitLoc.USERS_PKG_URL}files.php" method="post">
<table class="panel">
<tr>
<th>&nbsp;</th>
<th><a href="{$gBitLoc.USERS_PKG_URL}files.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'filename_desc'}filename_asc{else}filename_desc{/if}">{tr}name{/tr}</a></th>
<th><a href="{$gBitLoc.USERS_PKG_URL}files.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'created_desc'}created_asc{else}created_desc{/if}">{tr}created{/tr}</a></th>
<th><a href="{$gBitLoc.USERS_PKG_URL}files.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'filesize_desc'}filesize_asc{else}filesize_desc{/if}">{tr}size{/tr}</a></th>
</tr>
{cycle values="even,odd" print=false}
{section name=user loop=$channels}
<tr class="{cycle}">
<td style="text-align:center;">
<input type="checkbox" name="userfile[{$channels[user].file_id}]" />
</td>
<td>{$channels[user].filename|iconify}<a href="{$gBitLoc.USERS_PKG_URL}download_userfile.php?file_id={$channels[user].file_id}">{$channels[user].filename}</a></td>
<td>{$channels[user].created|bit_short_datetime}</td>
<td style="text-align:right;">{$channels[user].filesize|kbsize}</td>
</tr>
{sectionelse}
<tr class="panelsubmitrow">
<td class="norecords" colspan="4">no records found</td>
</tr>
{/section}
<tr class="panelsubmitrow">
<td colspan="4"><input type="submit" name="delete" value="{tr}delete{/tr}" /></td>
</tr>
</table>
</form>

</div> {* end .body *}

<div class="pagination">
{if $prev_offset >= 0}
[<a href="{$gBitLoc.USERS_PKG_URL}files.php?find={$find}&amp;offset={$prev_offset}&amp;sort_mode={$sort_mode}">{tr}prev{/tr}</a>]&nbsp;
{/if}
{tr}Page{/tr}: {$actual_page}/{$cant_pages}
{if $next_offset >= 0}
&nbsp;[<a href="{$gBitLoc.USERS_PKG_URL}files.php?find={$find}&amp;offset={$next_offset}&amp;sort_mode={$sort_mode}">{tr}next{/tr}</a>]
{/if}
{if $direct_pagination eq 'y'}
<br />
{section loop=$cant_pages name=foo}
{assign var=selector_offset value=$smarty.section.foo.index|times:$maxRecords}
<a href="{$gBitLoc.USERS_PKG_URL}files.php?find={$find}&amp;offset={$selector_offset}&amp;sort_mode={$sort_mode}">
{$smarty.section.foo.index_next}</a>&nbsp;
{/section}
{/if}
</div>

<div class="body">

<h3>{tr}Upload file{/tr}</h3>
<form enctype="multipart/form-data" action="{$gBitLoc.USERS_PKG_URL}files.php" method="post">
<table class="panel">
<!--
<tr>
  <td>{tr}Name{/tr}:</td><td><input type="text" name="name" /></td>
</tr>
-->
 <tr>
  <td rowspan="3">{tr}Upload file{/tr}:</td><td>
  <input type="hidden" name="MAX_FILE_SIZE" value="10000000000000" /><input size="25" name="userfile1" type="file" />
 </td><td>
  <input type="hidden" name="MAX_FILE_SIZE" value="10000000000000" /><input size="25" name="userfile2" type="file" />
 </td></tr><tr><td>
  <input type="hidden" name="MAX_FILE_SIZE" value="10000000000000" /><input size="25" name="userfile3" type="file" />
 </td><td>
  <input type="hidden" name="MAX_FILE_SIZE" value="10000000000000" /><input size="25" name="userfile4" type="file" />
 </td></tr><tr><td colspan="2">
  <input type="hidden" name="MAX_FILE_SIZE" value="10000000000000" /><input size="25" name="userfile5" type="file" />
  </td></tr><tr class="panelsubmitrow"><td colspan="3">
    <input type="submit" name="upload" value="{tr}upload{/tr}" />
  </td>
</tr>
</table>
</form>

</div> {* end .body *}
</div> {* end .userfiles *}
