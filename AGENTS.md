# Agent guidance

- Before changing grouping behavior, read [CONTEXT.md](CONTEXT.md), [membership ownership](docs/adr/0001-plugin-owned-server-group-membership.md), and [plugin extension boundaries](docs/adr/0002-keep-server-groups-plugin-only.md).
- Keep migrations append-only: add a new numbered file in `database/migrations` for schema changes, preserving previously shipped migrations.
- Preserve the table-existence guards in `SneakyServerGroupsServiceProvider` and `SneakyServerGroupsPlugin` so an installation whose migrations have not run remains safe to load and configure.
