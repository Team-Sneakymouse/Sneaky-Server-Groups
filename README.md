# Sneaky Server Groups

Panel-admin-managed server groups for Pelican's app server list.

## Behavior

- Server groups are global and configured from the plugin settings page.
- Each server can belong to at most one group.
- Users only see servers they can already access.
- Servers without a group appear under `Ungrouped`.
- Group order and server order inside each group are managed by admins.

## Repository

https://github.com/Team-Sneakymouse/Sneaky-Server-Groups

## Installation

Install the plugin into Pelican as `plugins/sneaky-server-groups`, then run Pelican's plugin install/migration flow. The plugin id and folder name must match.
