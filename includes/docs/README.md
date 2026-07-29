# Users package documentation

> Engineering documentation derived from the source in this package. The
> package's `includes/` directory must be denied to direct HTTP requests.

## Purpose

Users manages identities, sessions, authentication, groups, permissions, preferences, profiles, and registration.

## Responsibility

Owns the active user object and the permission model consumed by Kernel and Liberty.

## Dependencies

kernel, liberty, languages, themes, util.

Dependency direction matters: this package may depend on the packages above;
the dependencies do not thereby depend on this package.

## Boundary

Does not own content-specific access rules implemented by Liberty services.

## Documentation map

- [Architecture](architecture.md) — initialization, components, and request flow.
- [Source reference](source-reference.md) — source-derived files, classes,
  controllers, schema artifacts, plugins, and templates.
- [Development guide](development.md) — safe change workflow, extension points,
  validation, and maintenance guidance.
- [Security](security.md) — trust boundaries and direct-HTTP access requirements.
- [Identity and permission model](identity-and-permissions.md) — bootstrap,
  authentication, sessions, groups, roles, permissions, and recovery flows.
