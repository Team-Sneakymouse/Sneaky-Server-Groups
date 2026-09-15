# Use plugin-owned Server Group membership

Server Group membership is stored only in plugin-owned panel tables, not in Pelican deployment tags or Docker labels. Deployment tags select nodes during provisioning, and Docker labels are runtime container metadata that can become stale when a running server is moved between groups, while Server Groups are only an App Server List organization concern.
