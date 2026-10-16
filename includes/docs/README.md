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

Index: [toc.md](toc.md).
