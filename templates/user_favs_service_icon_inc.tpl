{if $gContent && $gContent->isValid() && $gBitUser->isRegistered() && $gBitUser->hasPermission('p_users_create_bookmarks') && $gContent->hasService($smarty.const.CONTENT_SERVICE_USERS_FAVS)}
{strip}
{assign var=isBookmarked value='false'}
{foreach from=$gBitUser->getFavorites() item=fav}
	{if $fav.content_id eq $gContent->mContentId}
		{assign var=isBookmarked value='true'}
	{/if}
{/foreach}
<a title="{if $isBookmarked eq 'true'}{tr}Remove from your favorites{/tr}{else}{tr}Add to your favorites{/tr}{/if}" onclick="BitUser.toggleBookmark({$gContent->mContentId});" href="javascript:void(0); {* {$smarty.const.USERS_PKG_URL}bookmark.php?content_id={$gContent->mContentId} *}" >
	{if $isBookmarked eq 'true'}
		{booticon iname="fa-bookmark-empty" ipackage="icons" iexplain="Remove Bookmark"}
	{else}
		{booticon iname="fa-bookmark" ipackage="icons" iexplain="Bookmark"}
	{/if}
</a>
	<script type="text/javascript">/* <![CDATA[ */
		if( typeof( BitUser ) == 'undefined' ){ldelim} BitUser = {ldelim}{rdelim} {rdelim};
		BitUser.bookmarkUrl = "{$smarty.const.USERS_PKG_URL}bookmark.php";
		BitUser.isBookmarked = {$isBookmarked}; 
	{literal}
		BitUser.toggleBookmark = function( contentId ){
			var ajax = new BitBase.SimpleAjax();
			var query = 'content_id='+contentId+'&action='+(BitUser.isBookmarked?'remove':'add');
			ajax.connect( BitUser.bookmarkUrl, query, BitUser.postBookmark, "GET" );
		};
		BitUser.postBookmark = function( rslt ){
			var obj = eval( "(" + rslt.responseText + ")" );
			switch( obj.Status.code ){
			case 205:
				BitUser.isBookmarked = obj.Result.bookmark_state;
			case 400:
			case 401:
			default:
				break;
			}
			alert( obj.Status.message );
		};
	{/literal} /* ]]> */</script>
{/strip}
{/if}
