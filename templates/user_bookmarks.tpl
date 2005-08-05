<div class="floaticon">{bithelp}</div>
<div class="display userbookmarks">
<div class="header">
<h1>{tr}User Bookmarks{/tr}</h1>
</div>

{include file="bitpackage:users/my_bitweaver_bar.tpl"}

<div class="body">

<div class="path">
  {tr}Current folder{/tr}: {if $parent_id>0}<a href="{$smarty.const.USERS_PKG_URL}bookmarks.php">{tr}top{/tr}</a>&gt;{/if}{$path}</h2>
</div>

<table class="panel">
<tr>
  <th>{tr}name{/tr}</td>
  <th>{tr}action{/tr}</td>
</tr>
{cycle values="even,odd" print=false}
{section name=ix loop=$folders}
<tr class="{cycle}">
  <td><a href="{$smarty.const.USERS_PKG_URL}bookmarks.php?parent_id={$folders[ix].folder_id}">{biticon ipackage=liberty iname="folder" iexplain="folder"}</a>&nbsp;{$folders[ix].name} ({$folders[ix].urls})</td>
  <td align="right" nowrap="nowrap">
    <a title="{tr}remove folder{/tr}" href="{$smarty.const.USERS_PKG_URL}bookmarks.php?parent_id={$parent_id}&amp;removefolder={$folders[ix].folder_id}">{biticon ipackage=liberty iname="delete" iexplain="remove"}</a>
    <a title="{tr}edit{/tr}" href="{$smarty.const.USERS_PKG_URL}bookmarks.php?parent_id={$parent_id}&amp;editfolder={$folders[ix].folder_id}">{biticon ipackage=liberty iname="edit" iexplain="edit"}</a>
  </td>
</tr>
{/section}
</table>

<h2>{tr}Bookmarks{/tr}</h2>
<table class="panel">
<tr>
  <th>{tr}name{/tr}</td>
  <th>{tr}url{/tr}</td>
  <th>{tr}action{/tr}</td>
</tr>
{cycle values="even,odd" print=false}
{section name=ix loop=$urls}
<tr class="{cycle}">
  <td><a href="{$urls[ix].url}">{$urls[ix].name}</a>
  {if $gBitUser->hasPermission( 'bit_p_cache_bookmarks' ) and $urls[ix].datalen > 0}
  (<a href="{$smarty.const.USERS_PKG_URL}cached_bookmark.php?urlid={$urls[ix].url_id}">{tr}cache{/tr}</a>)
  {/if}
  </td>
  <td>{$urls[ix].url|truncate:50}</td>
  <td>
    <a title="{tr}remove bookmark{/tr}" href="{$smarty.const.USERS_PKG_URL}bookmarks.php?parent_id={$parent_id}&amp;removeurl={$urls[ix].url_id}">{biticon ipackage=liberty iname="delete" iexplain="remove"}</a>
    <a title="{tr}edit{/tr}" href="{$smarty.const.USERS_PKG_URL}bookmarks.php?parent_id={$parent_id}&amp;editurl={$urls[ix].url_id}">{biticon ipackage=liberty iname="edit" iexplain="edit"}</a>
    {if $gBitUser->hasPermission( 'bit_p_cache_bookmarks' ) and $urls[ix].datalen > 0}
    <a title="{tr}refresh cache{/tr}" href="{$smarty.const.USERS_PKG_URL}bookmarks.php?parent_id={$parent_id}&amp;refreshurl={$urls[ix].url_id}">{biticon ipackage=liberty iname="refresh" iexplain="refresh cache"}</a>
    {/if}
  </td>
</tr>
{/section}
</table>

<br />

<h2>{tr}Admin folders and bookmarks{/tr}</h2>
{formfeedback error=$bookmarkError}
<table><tr><td width="50%" valign="top">
  <form action="{$smarty.const.USERS_PKG_URL}bookmarks.php" method="post">
    <table class="panel">
      <input type="hidden" name="editfolder" value="{$editfolder|escape}" />
      <input type="hidden" name="parent_id" value="{$parent_id|escape}" />
      <tr><th colspan="2">{tr}Add or edit a folder{/tr}</th></tr>
      <tr><td>{tr}Name{/tr}:</td>
          <td><input type="text" name="foldername" value="{$foldername|escape}" /></td>
      </tr>
      <tr class="panelsubmitrow">
          <td colspan="2"><input type="submit" name="addfolder" value="{tr}add{/tr}" /> <a href="{$smarty.const.USERS_PKG_URL}bookmarks.php?parent_id={$parent_id}&amp;editfolder=0">{tr}new{/tr}</a></td>
      </tr>
    </table>
  </form>
</td><td width="50%" valign="top">
  <form action="{$smarty.const.USERS_PKG_URL}bookmarks.php" method="post">
    <table class="panel">
      <input type="hidden" name="editurl" value="{$editurl|escape}" />
      <input type="hidden" name="parent_id" value="{$parent_id|escape}" />
      <tr><th colspan="2">{tr}Add or edit a URL{/tr}</th></tr>
      <tr><td>{tr}Name{/tr}:</td>
          <td><input type="text" name="urlname" value="{$urlname|escape}" /></td>
      </tr>
      <tr><td>{tr}URL{/tr}:</td>
          <td><input type="text" name="urlurl" value="{$urlurl|escape}" /></td>
      </tr>
      <tr class="panelsubmitrow">
          <td colspan="2"><input type="submit" name="addurl" value="{tr}add{/tr}" /> <a href="{$smarty.const.USERS_PKG_URL}bookmarks.php?parent_id={$parent_id}&amp;editurl=0">{tr}new{/tr}</a></td>
      </tr>
      </form>
    </table>
</td></tr></table>

</div> {* end .body *}
</div> {* end .bookmarks *}
