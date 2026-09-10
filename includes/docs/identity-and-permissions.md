# Users identity and permission model

## Class hierarchy

```text
LibertyContent
└── LibertyMime
    └── BitUser
        └── BitPermUser
```

`BitUser` is both an identity/domain object and Liberty content. `BitPermUser`
adds effective group, role, and permission behavior. Authentication adapters
extend `BaseAuth`; social/third-party providers are coordinated by
`BitHybridAuthManager`.

## Request initialization

`users/includes/bit_setup_inc.php` selects and loads the configured user class,
starts/continues the session, and establishes `$gBitUser`. Three cases must
remain distinct:

1. A registered identity restored from session.
2. A login/authentication attempt that resolves credentials.
3. The anonymous user with anonymous group/role permissions.

Post-load setup derives effective groups, permissions, preferences, locale, and
template-visible identity state. Code must not assume that an object in
`$gBitUser` is registered; call `isRegistered()`.

## Authentication adapters

Adapters under `auth/*/auth.php` extend `BaseAuth`. The checkout includes native,
LDAP, IMAP, locate, and multisite-oriented adapters. Configuration selects the
active mechanism.

Authentication answers who supplied valid credentials. It does not grant
package or content authorization. Keep password verification, account lookup,
and permission evaluation separate.

Do not log passwords, reset tokens, OAuth secrets, session identifiers, or
complete authentication responses.

## Sessions

The session carries identity continuity, not authoritative user data. Reload
mutable account/permission state through the established user path. On login,
logout, privilege changes, and sensitive recovery transitions:

- Regenerate or terminate the session as appropriate.
- Clear stale identity and permission state.
- Preserve only explicitly safe return URLs/state.
- Prevent session fixation and cross-user cache reuse.

Kernel closes the session before final display in normal response flow to
reduce session-lock contention. Long-running/background work must not retain
the session lock.

## Groups, roles, and permissions

`BitPermUser` computes effective permission state from membership and registered
permission definitions. Anonymous membership is real permission context, not
the absence of a user object.

Use:

- `hasPermission()` for a boolean decision.
- `verifyPermission()` when the established failure path is desired.
- Kernel's `verifyPermission()`/`fatalPermission()` wrappers when controller
  presentation should follow system conventions.

Liberty content permissioning can narrow or extend object access. A global
package permission is not sufficient when the object has content-specific
policy.

Roles and groups can also provide home-page/layout behavior. Do not treat a
role identifier as interchangeable with a group identifier.

## Registration and confirmation

`register.php` and `includes/register_inc.php` coordinate account creation.
Confirmation and validation controllers complete email or administrator-driven
activation flows. Registration must validate:

- Login uniqueness and normalization.
- Email syntax and policy.
- Password policy and confirmation.
- CAPTCHA/challenge state when enabled.
- Terms or site-required fields.
- Activation/approval state.

Error messages should not disclose whether a sensitive account exists beyond
the product's intentional account-recovery policy.

## Password recovery and changes

`remind_password.php`, `change_password.php`, `confirm.php`, and `validate.php`
participate in recovery/validation paths. Tokens must be single-purpose,
time-bounded where supported, unpredictable, and invalidated after use.

Password changes require current identity or a valid recovery capability and
must invalidate stale authentication/session state as established by the
implementation.

## Hybrid authentication

`BitHybridAuthManager` loads enabled providers and connects external provider
identity to local accounts. Treat provider profile fields as untrusted input.
State/nonce validation and redirect URI restrictions are security boundaries.

Disconnect must not leave an account without an intended usable authentication
method.

## User content and preferences

Because `BitUser` extends `LibertyMime`, profiles have shared content identity,
permissions, and optional attachment/avatar behavior. User preferences are
separate from system/package configuration; callers must use the user
preference APIs and respect whose preferences are being changed.

## Controller checklist

1. Determine registered versus anonymous state explicitly.
2. Verify global permission and Liberty object permission where applicable.
3. Verify challenge/CSRF state for mutations.
4. Normalize login/email once, consistently.
5. Avoid account enumeration in public errors.
6. Rebuild permission/session state after membership changes.
7. Never trust a return URL without local-target validation.
