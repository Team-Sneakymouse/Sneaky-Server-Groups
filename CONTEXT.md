# Pelican Server Organization

This context describes the language for organizing Pelican servers into host-managed views for end users.

## Language

**App Server List**:
The non-admin server list shown to users for servers they can access. By default, it shows all servers the user can access regardless of ownership.
_Avoid_: Admin server list, dashboard, client list

**Server Group**:
A panel-admin-managed category that contains zero or more servers and determines how those servers are grouped on the app server list. Each server belongs to at most one Server Group.
_Avoid_: Folder, sorter, category

**Server Group Membership**:
The panel-only association between a server and its Server Group.
_Avoid_: Docker label, deployment tag

**Ungrouped**:
The App Server List group for servers that do not belong to any Server Group.
_Avoid_: Unassigned, uncategorized
