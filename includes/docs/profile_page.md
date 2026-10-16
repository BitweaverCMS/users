# Profile Page

The public page for one user. Older code and stored keys still say "homepage". Those keys stay. The page itself is the Profile Page.

This page is the shell: identity, the About body, and whichever center modules the layout loads. A grid drawn by another package is that package's concern. This package does not order that grid.

## Request

`users/index.php?home=<login>` loads the page. `home` accepts a login, a numeric user id, or a `mailto:` address (`BitUser::lookupHomepage()`). `fHomepage` is the old name of the same parameter. A `content_id` or `user_id` on the request is resolved to `home` in `includes/lookup_user_inc.php`.

`BitUser::getDisplayUrlFromHash()` builds the public URL. With `pretty_urls` or `pretty_urls_extended` it is `users/<login>` (extended adds `view/` before the login). Otherwise it is `users/index.php?home=<login>`. An install may replace that with `override_user_url()` when that function exists.

The browser title is the `homepage_title` preference. When that preference is empty the title is the display name plus `'s Profile`. The content title rendered in the body is the Liberty title, which is separate. The body element id is `userhomepage`.

Viewing requires `p_users_view_user_homepage` or `p_users_admin`, or the viewer is the owner. Unless the viewer has `p_user_admin`, `users/index.php` then calls `verifyPermission( 'p_users_edit_user_homepage' )` on the profile owner. The page is refused when the owner lacks that permission. A private `users_information` preference returns not-found to anyone except the owner and a users admin.

## About body

The user's Liberty content is the About body. `edit_personal_page.php` stores it for the logged-in user and returns to `index.php?home=<login>`. The edit link is shown to the owner when they have `p_users_edit_user_homepage`.

## Layout

After services run, `users/index.php` loads a theme layout with:

1. `layout` = the user's login
2. `fallback_layout` = `home`
3. then the active package, then the site default layout (`BitThemes::getLayout()`)

Center-column rows are pushed onto `$gCenterPieces`. When that list is non-empty the center template is `kernel/dynamic.tpl`. When it is empty the center template is `users/center_user_wiki_page.tpl`, which prints the About body.

`users/assigned_modules.php` is the old per-user layout editor. It stops with an error before it reads or stores anything. `BitThemes::storeLayout()` is deprecated. Do not revive that page as the editor. `canCustomizeLayout()` is still the permission check for a layout editor: `p_tidbits_custom_home_layout`, or `users_layouts` set to `y`, `h`, or `u`. `h` means the profile only.

`homepage_header` is a preference, trimmed to 250 characters, written only by the dead assigner. `lookup_user_inc.php` still assigns it.

## Theme

When `canCustomizeTheme()` is true, a `theme` preference on the user replaces the style for this request and the stylesheet is resolved for that user id. The check is `p_tidbits_custom_home_theme`, or `users_themes` set to `y`, `h`, or `u`.

## Other uses of "home"

These are not the Profile Page:

| Stored name | What it is |
|---|---|
| `users_homepage` | External website URL on the user. The preferences and registration forms call it Website. |
| `users_login_homepage` | Where a user lands after sign-in. Default is `users/my.php`. |
| Group home | A content id, wiki page name, or path used as the site home for a group. It needs the site homepage set to Group Home. |

`display_users_content_list` can also append that user's Liberty content list when the viewer has `p_liberty_list_content`.
