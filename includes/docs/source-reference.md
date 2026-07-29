# Users source reference

> Generated from the current checkout and then intended for human review.
> Paths are relative to the package root.

## Inventory summary

| Artifact | Count |
|---|---:|
| PHP files | 116 |
| Smarty templates | 51 |
| JavaScript files | 0 |
| CSS files | 0 |

## Bootstrap and schema artifacts

- `admin/schema_inc.php`
- `admin/upgrade_inc.php`
- `admin/upgrades/2.1.0.php`
- `admin/upgrades/2.1.1.php`
- `admin/upgrades/2.1.2.php`
- `includes/bit_setup_inc.php`

## First-party classes and interfaces

- `auth/bit/auth.php:14` — `class BitAuth extends BaseAuth {`
- `auth/imap/auth.php:14` — `class IMAPAuth extends BaseAuth {`
- `auth/ldap/auth.php:24` — `class LDAPAuth extends BaseAuth {`
- `auth/locate/auth.php:17` — `class LocateAuth extends BaseAuth {`
- `auth/multisites/auth.php:14` — `class MultisitesAuth extends BaseAuth {`
- `hauth/Hybrid/Auth.php:16` — `class Hybrid_Auth {`
- `hauth/Hybrid/Endpoint.php:14` — `class Hybrid_Endpoint {`
- `hauth/Hybrid/Error.php:14` — `class Hybrid_Error {`
- `hauth/Hybrid/Exception.php:15` — `class Hybrid_Exception extends Exception {`
- `hauth/Hybrid/Logger.php:12` — `class Hybrid_Logger {`
- `hauth/Hybrid/Provider_Adapter.php:19` — `class Hybrid_Provider_Adapter {`
- `hauth/Hybrid/Provider_Model.php:23` — `abstract class Hybrid_Provider_Model {`
- `hauth/Hybrid/Provider_Model_OAuth1.php:21` — `class Hybrid_Provider_Model_OAuth1 extends Hybrid_Provider_Model {`
- `hauth/Hybrid/Provider_Model_OAuth2.php:21` — `class Hybrid_Provider_Model_OAuth2 extends Hybrid_Provider_Model {`
- `hauth/Hybrid/Provider_Model_OpenID.php:19` — `class Hybrid_Provider_Model_OpenID extends Hybrid_Provider_Model {`
- `hauth/Hybrid/Providers/AOL.php:14` — `class Hybrid_Providers_AOL extends Hybrid_Provider_Model_OpenID {`
- `hauth/Hybrid/Providers/Amazon.php:21` — `class Hybrid_Providers_Amazon extends Hybrid_Provider_Model_OAuth2 {`
- `hauth/Hybrid/Providers/Dropbox.php:14` — `class Hybrid_Providers_Dropbox extends Hybrid_Provider_Model_OAuth2`
- `hauth/Hybrid/Providers/Facebook.php:17` — `class Hybrid_Providers_Facebook extends Hybrid_Provider_Model {`
- `hauth/Hybrid/Providers/Foursquare.php:28` — `class Hybrid_Providers_Foursquare extends Hybrid_Provider_Model_OAuth2 {`
- `hauth/Hybrid/Providers/Google.php:14` — `class Hybrid_Providers_Google extends Hybrid_Provider_Model_OAuth2 {`
- `hauth/Hybrid/Providers/Instagram.php:11` — `class Hybrid_Providers_Instagram extends Hybrid_Provider_Model_OAuth2`
- `hauth/Hybrid/Providers/LinkedIn.php:12` — `class Hybrid_Providers_LinkedIn extends Hybrid_Provider_Model_OAuth2 {`
- `hauth/Hybrid/Providers/Live.php:21` — `class Hybrid_Providers_Live extends Hybrid_Provider_Model_OAuth2 {`
- `hauth/Hybrid/Providers/OpenID.php:14` — `class Hybrid_Providers_OpenID extends Hybrid_Provider_Model_OpenID {`
- `hauth/Hybrid/Providers/Paypal.php:28` — `class Hybrid_Providers_Paypal extends Hybrid_Provider_Model`
- `hauth/Hybrid/Providers/PaypalOpenID.php:11` — `class Hybrid_Providers_PaypalOpenID extends Hybrid_Provider_Model_OpenID`
- `hauth/Hybrid/Providers/Twitter.php:12` — `class Hybrid_Providers_Twitter extends Hybrid_Provider_Model_OAuth1 {`
- `hauth/Hybrid/Providers/Yahoo.php:22` — `class Hybrid_Providers_Yahoo extends Hybrid_Provider_Model_OAuth2 {`
- `hauth/Hybrid/Storage.php:13` — `class Hybrid_Storage implements Hybrid_Storage_Interface {`
- `hauth/Hybrid/StorageInterface.php:12` — `interface Hybrid_Storage_Interface {`
- `hauth/Hybrid/User.php:12` — `class Hybrid_User {`
- `hauth/Hybrid/User_Activity.php:16` — `class Hybrid_User_Activity {`
- `hauth/Hybrid/User_Contact.php:16` — `class Hybrid_User_Contact {`
- `hauth/Hybrid/User_Profile.php:18` — `class Hybrid_User_Profile {`
- `hauth/Hybrid/thirdparty/Amazon/AmazonOAuth2Client.php:24` — `class AmazonOAuth2Client extends OAuth2Client {`
- `hauth/Hybrid/thirdparty/OAuth/OAuth.php:10` — `  class OAuthException extends Exception {`
- `hauth/Hybrid/thirdparty/OAuth/OAuth.php:120` — `class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod {`
- `hauth/Hybrid/thirdparty/OAuth/OAuth.php:146` — `class OAuthSignatureMethod_PLAINTEXT extends OAuthSignatureMethod {`
- `hauth/Hybrid/thirdparty/OAuth/OAuth.php:15` — `class OAuthConsumer {`
- `hauth/Hybrid/thirdparty/OAuth/OAuth.php:182` — `abstract class OAuthSignatureMethod_RSA_SHA1 extends OAuthSignatureMethod {`
- `hauth/Hybrid/thirdparty/OAuth/OAuth.php:241` — `class OAuthRequest {`
- `hauth/Hybrid/thirdparty/OAuth/OAuth.php:30` — `class OAuthToken {`
- `hauth/Hybrid/thirdparty/OAuth/OAuth.php:504` — `class OAuthServer {`
- `hauth/Hybrid/thirdparty/OAuth/OAuth.php:64` — `abstract class OAuthSignatureMethod {`
- `hauth/Hybrid/thirdparty/OAuth/OAuth.php:726` — `class OAuthDataStore {`
- `hauth/Hybrid/thirdparty/OAuth/OAuth.php:752` — `class OAuthUtil {`
- `hauth/Hybrid/thirdparty/OAuth/OAuth1Client.php:10` — `class OAuth1Client{`
- `hauth/Hybrid/thirdparty/OAuth/OAuth2Client.php:10` — `class OAuth2Client`
- `hauth/Hybrid/thirdparty/OpenID/LightOpenID.php:14` — `class LightOpenID`
- `includes/classes/BaseAuth.php:14` — `class BaseAuth {`
- `includes/classes/BitHybridAuthManager.php:16` — `class BitHybridAuthManager extends BitSingleton {`
- `includes/classes/BitPermUser.php:31` — `class BitPermUser extends BitUser {`
- `includes/classes/BitUser.php:45` — `class BitUser extends LibertyMime {`
- `includes/classes/CloudflareTurnstile.php:3` — `class CloudflareTurnstileValidator {`
- `includes/recaptcha/ReCaptcha/ReCaptcha.php:40` — `class ReCaptcha`
- `includes/recaptcha/ReCaptcha/RequestMethod.php:40` — `interface RequestMethod`
- `includes/recaptcha/ReCaptcha/RequestMethod/Curl.php:40` — `class Curl`
- `includes/recaptcha/ReCaptcha/RequestMethod/CurlPost.php:46` — `class CurlPost implements RequestMethod`
- `includes/recaptcha/ReCaptcha/RequestMethod/Post.php:44` — `class Post implements RequestMethod`
- `includes/recaptcha/ReCaptcha/RequestMethod/Socket.php:41` — `class Socket`
- `includes/recaptcha/ReCaptcha/RequestMethod/SocketPost.php:46` — `class SocketPost implements RequestMethod`
- `includes/recaptcha/ReCaptcha/RequestParameters.php:40` — `class RequestParameters`
- `includes/recaptcha/ReCaptcha/Response.php:40` — `class Response`
- `includes/solvemedialib.php:147` — `class SolveMediaResponse {`

## Web-facing PHP controllers

- `admin/admin_login_inc.php`
- `admin/admin_userfiles_inc.php`
- `admin/admin_users_inc.php`
- `admin/api_help_inc.php`
- `admin/api_inc.php`
- `admin/assign_user.php`
- `admin/edit_group.php`
- `admin/index.php`
- `admin/permissions.php`
- `admin/schema_inc.php`
- `admin/upgrade_inc.php`
- `admin/upgrades/2.1.0.php`
- `admin/upgrades/2.1.1.php`
- `admin/upgrades/2.1.2.php`
- `admin/user_activity.php`
- `admin/users_import.php`
- `admin/verify_emails.php`
- `assigned_modules.php`
- `auth/bit/auth.php`
- `auth/imap/auth.php`
- `auth/ldap/auth.php`
- `auth/locate/auth.php`
- `auth/multisites/auth.php`
- `bookmark.php`
- `captcha_image.php`
- `change_password.php`
- `confirm.php`
- `custom_home.php`
- `edit_personal_page.php`
- `freecap/freecap.php`
- `freecap/freecap_wrap.php`
- `hauth/Hybrid/Auth.php`
- `hauth/Hybrid/Endpoint.php`
- `hauth/Hybrid/Error.php`
- `hauth/Hybrid/Exception.php`
- `hauth/Hybrid/Logger.php`
- `hauth/Hybrid/Provider_Adapter.php`
- `hauth/Hybrid/Provider_Model.php`
- `hauth/Hybrid/Provider_Model_OAuth1.php`
- `hauth/Hybrid/Provider_Model_OAuth2.php`
- `hauth/Hybrid/Provider_Model_OpenID.php`
- `hauth/Hybrid/Providers/AOL.php`
- `hauth/Hybrid/Providers/Amazon.php`
- `hauth/Hybrid/Providers/Dropbox.php`
- `hauth/Hybrid/Providers/Facebook.php`
- `hauth/Hybrid/Providers/Foursquare.php`
- `hauth/Hybrid/Providers/Google.php`
- `hauth/Hybrid/Providers/Instagram.php`
- `hauth/Hybrid/Providers/LinkedIn.php`
- `hauth/Hybrid/Providers/Live.php`
- `hauth/Hybrid/Providers/OpenID.php`
- `hauth/Hybrid/Providers/Paypal.php`
- `hauth/Hybrid/Providers/PaypalOpenID.php`
- `hauth/Hybrid/Providers/Twitter.php`
- `hauth/Hybrid/Providers/Yahoo.php`
- `hauth/Hybrid/Storage.php`
- `hauth/Hybrid/StorageInterface.php`
- `hauth/Hybrid/User.php`
- `hauth/Hybrid/User_Activity.php`
- `hauth/Hybrid/User_Contact.php`
- `hauth/Hybrid/User_Profile.php`
- `hauth/Hybrid/thirdparty/Amazon/AmazonOAuth2Client.php`
- `hauth/Hybrid/thirdparty/OAuth/OAuth.php`
- `hauth/Hybrid/thirdparty/OAuth/OAuth1Client.php`
- `hauth/Hybrid/thirdparty/OAuth/OAuth2Client.php`
- `hauth/Hybrid/thirdparty/OpenID/LightOpenID.php`
- `hauth/disconnect.php`
- `hauth/index.php`
- `hauth/live.php`
- `icons/flags/index.php`
- `icons/index.php`
- `index.php`
- `logout.php`
- `modules/index.php`
- `modules/mod_online_users.php`
- `modules/mod_since_last_visit.php`
- `modules/mod_user_pages.php`
- `modules/mod_user_profile.php`
- `modules/mod_who_is_there.php`
- `my.php`
- `my_groups.php`
- `my_images.php`
- `preferences.php`
- `register.php`
- `remind_password.php`
- `show_user_avatar.php`
- `signin.php`
- `users_rss.php`
- `validate.php`
- `watches.php`

## Declared schema tables

- `users_auth_map`
- `users_cnxn`
- `users_favorites_map`
- `users_group_permissions`
- `users_groups`
- `users_groups_map`
- `users_permissions`
- `users_users`
- `users_watches`

## Plugin and module directories

- `auth/`
- `liberty_plugins/`
- `modules/`
- `smartyplugins/`

## Templates

- `modules/help_mod_online_users.tpl`
- `modules/help_mod_since_last_visit.tpl`
- `modules/help_mod_user_pages.tpl`
- `modules/help_mod_user_profile.tpl`
- `modules/mod_login_box.tpl`
- `modules/mod_online_users.tpl`
- `modules/mod_since_last_visit.tpl`
- `modules/mod_user_pages.tpl`
- `modules/mod_user_profile.tpl`
- `modules/mod_who_is_there.tpl`
- `modules/user_module.tpl`
- `templates/admin_assign_user.tpl`
- `templates/admin_group_edit.tpl`
- `templates/admin_groups_list.tpl`
- `templates/admin_list_users.tpl`
- `templates/admin_login.tpl`
- `templates/admin_permissions.tpl`
- `templates/admin_users.tpl`
- `templates/admin_validation_mail.tpl`
- `templates/admin_welcome_mail.tpl`
- `templates/captcha.tpl`
- `templates/center_user_wiki_page.tpl`
- `templates/change_password.tpl`
- `templates/edit_personal_page.tpl`
- `templates/edit_user_fav_json.tpl`
- `templates/group_list_members.tpl`
- `templates/index_list.tpl`
- `templates/login_inc.tpl`
- `templates/menu_users.tpl`
- `templates/menu_users_admin.tpl`
- `templates/my_bitweaver.tpl`
- `templates/my_bitweaver_bar.tpl`
- `templates/my_group_edit.tpl`
- `templates/my_groups_list.tpl`
- `templates/my_images.tpl`
- `templates/new_user_notification.tpl`
- `templates/password_reminder.tpl`
- `templates/register.tpl`
- `templates/remind_password.tpl`
- `templates/signin.tpl`
- `templates/user_activity.tpl`
- `templates/user_assigned_modules.tpl`
- `templates/user_favs_service_icon_inc.tpl`
- `templates/user_information_inc.tpl`
- `templates/user_preferences.tpl`
- `templates/user_validation_mail.tpl`
- `templates/user_watches.tpl`
- `templates/users_import.tpl`
- `templates/users_list.tpl`
- `templates/validate_auth.tpl`
- `templates/welcome_mail.tpl`

## Reading cautions

- Presence in this inventory does not make a file a supported public API.
- Bundled third-party libraries must be distinguished from package-owned code.
- Base schema files do not prove the migration state of a deployed database.
- Controllers may rely on include files, globals, services, and template callbacks not visible from their filename alone.
