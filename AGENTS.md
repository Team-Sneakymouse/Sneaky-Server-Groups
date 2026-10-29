# AGENTS.md

## Repo-specific rules

- **Plugin-only, no fork.** Don't patch Pelican panel or Wings; extend via plugin hooks only (ADR 0002). If impossible, document it and ask.
- **Membership lives in the plugin tables** (`sneaky_server_group_memberships`), never in deployment tags or Docker labels (ADR 0001).
- **Migrations are append-only.** Never edit `database/migrations/001_*`; add numbered files after it. Keep the `Schema::hasTable` guards (`SneakyServerGroupsServiceProvider`, `SneakyServerGroupsPlugin`) so an unmigrated install does nothing instead of crashing.
- **One group per server.** The migration enforces it with a unique index on `server_id`; preserve the invariant in the settings form save too.
- **`orderQueryUsing` interpolates table names into raw SQL**; keep columns table-qualified when editing it.
- **Keep the plugin `id` (`sneaky-server-groups`) equal to the folder name** and bump `plugin.json` `version` on change.
