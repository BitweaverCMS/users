{*Smarty template*}
<a class="pagetitle" href="{$smarty.const.USERS_PKG_URL}menu.php">{tr}User Menu{/tr}</a><br /><br />
{include file="bitpackage:users/my_bitweaver_bar.tpl"}
<br />
{if $gBitSystem->isFeatureActive( 'feature_user_bookmarks' ) and $gBitUser->hasPermission( 'bit_p_create_bookmarks' )}
<a title="({tr}May need to refresh twice to see changes{/tr})" href="{$smarty.const.USERS_PKG_URL}menu.php?addbk=1">{tr}Add top level bookmarks to menu{/tr}</a> 
{/if}
<br /><br />
<table class="find">
<tr><td>{tr}Find{/tr}</td>
   <td>
   <form method="get" action="{$smarty.const.USERS_PKG_URL}menu.php">
     <input type="text" name="find" value="{$find|escape}" />
     <input type="submit" value="{tr}find{/tr}" name="search" />
     <input type="hidden" name="sort_mode" value="{$sort_mode|escape}" />
   </form>
   </td>
</tr>
</table>
<form action="{$smarty.const.USERS_PKG_URL}menu.php" method="post">
<table class="panel">
<tr>
<td  class="heading"><input type="submit" name="delete" value="Delete" title="{tr}delete selected{/tr}" /></td>
<td class="heading"><a href="{$smarty.const.USERS_PKG_URL}menu.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'position_desc'}position_asc{else}position_desc{/if}">{tr}Pos{/tr}</a></td>
<td class="heading"><a href="{$smarty.const.USERS_PKG_URL}menu.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'name_desc'}name_asc{else}name_desc{/if}">{tr}Name{/tr}</a></td>
<td class="heading"><a href="{$smarty.const.USERS_PKG_URL}menu.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'url_desc'}url_asc{else}url_desc{/if}">{tr}URL{/tr}</a></td>
<td class="heading"><a href="{$smarty.const.USERS_PKG_URL}menu.php?offset={$offset}&amp;sort_mode={if $sort_mode eq 'mode_desc'}mode_asc{else}mode_desc{/if}">{tr}Mode{/tr}</a></td>
</tr>
{cycle values="even,odd" print=false}
{section name=user loop=$channels}
<tr>
<td style="text-align:center;" class="{cycle advance=false}">
<input type="checkbox" name="menu[{$channels[user].menu_id}]" />
</td>
<td class="{cycle advance=false}">{$channels[user].position}</td>
<td class="{cycle advance=false}"><a href="{$smarty.const.USERS_PKG_URL}menu.php?menu_id={$channels[user].menu_id}&amp;offset={$offset}&amp;sort_mode={$sort_mode}&amp;find={$find}">{$channels[user].name}</a></td>
<td class="{cycle advance=false}">{$channels[user].url|truncate:40:"...":true}</td>
<td style="text-align:center;" class="{cycle advance=false}">{$channels[user].mode}</td>
</tr>
{/section}
</table>
</form>

<div class="pagination">
{if $prev_offset >= 0}
[<a href="{$smarty.const.USERS_PKG_URL}menu.php?find={$find}&amp;offset={$prev_offset}&amp;sort_mode={$sort_mode}">{tr}prev{/tr}</a>]&nbsp;
{/if}
{tr}Page{/tr}: {$actual_page}/{$cant_pages}
{if $next_offset >= 0}
&nbsp;[<a href="{$smarty.const.USERS_PKG_URL}menu.php?find={$find}&amp;offset={$next_offset}&amp;sort_mode={$sort_mode}">{tr}next{/tr}</a>]
{/if}
{if $direct_pagination eq 'y'}
<br />
{section loop=$cant_pages name=foo}
{assign var=selector_offset value=$smarty.section.foo.index|times:$maxRecords}
<a href="{$smarty.const.USERS_PKG_URL}menu.php?find={$find}&amp;offset={$selector_offset}&amp;sort_mode={$sort_mode}">
{$smarty.section.foo.index_next}</a>&nbsp;
{/section}
{/if}
</div>

<h3>{tr}Add or edit an item{/tr}</h3>
<form action="{$smarty.const.USERS_PKG_URL}menu.php" method="post">
<input type="hidden" name="menu_id" value="{$menu_id|escape}" />
<table class="panel">
  <tr><td>{tr}Name{/tr}</td>
      <td><input type="text" name="name" value="{$info.name|escape}" /></td>
  </tr>
  <tr><td>{tr}URL{/tr}</td>
      <td><input type="text" name="url" value="{$info.url|escape}" /></td>  </tr>
  <tr><td>{tr}Position{/tr}</td>
      <td><input type="text" name="position" value="{$info.position|escape}" /></td>
  </tr>
  <tr><td>{tr}Mode{/tr}</td>
      <td>
        <select name="mode">
          <option value="n" {if $info.mode eq 'n'}selected="selected"{/if}>{tr}new window{/tr}</option>
          <option value="w" {if $info.mode eq 'w'}selected="selected"{/if}>{tr}replace window{/tr}</option>
        </select>
      </td>
  </tr>
  <tr class="panelsubmitrow">
    <td colspan="2"><input type="submit" name="save" value="{tr}save{/tr}" /></td>
  </tr>
</table>
</form>
