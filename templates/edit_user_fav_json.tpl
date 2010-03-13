{ldelim}
  "Status": {ldelim}
    "code": {$statusCode},
    "request": "setbookmark",
	"message": "{$msg}"
{if !$error}
  {rdelim},
  "Result": {ldelim}
  	"content_id": {$contentId},
	"bookmark_state": {$bookmarkState}
{/if}
  {rdelim}
{rdelim}
