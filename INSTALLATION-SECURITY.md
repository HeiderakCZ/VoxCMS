# Installer security and recovery

## Normal installation

The installer source is kept in `install/` for development and source control. Apache's `install/.htaccess` allows requests only from loopback addresses (`127.0.0.1` and `::1`). This protection does not depend on `mod_rewrite`; the PHP entry point also checks the source address and signed filesystem state. Requests for the SQL dump are denied by Apache even from localhost; the installer reads it directly from disk.

After configuration has been saved, `.voxcms-installing` records an HMAC-signed local installation-in-progress state. It is removed after the database import succeeds and the `.voxcms-installed` marker is atomically created. Both files are hidden and denied by the root `.htaccess` rule for dotfiles. The marker is signed with the `secret` from `config.php`.

After installation, the root `.htaccess` denies every URL under `/install/`, including direct PHP file requests, when `mod_rewrite` is available. Without that module, Apache still denies all non-local requests to the installer, the PHP entry point refuses to run even from localhost when an installed marker exists, and Apache denies the SQL dump. `SystemChecker` validates the marker on every request while the source directory is present; other system checks and the `core.check` plugin hook retain their cached schedule.

For production, deployment must omit the entire `install/` directory from the public DocumentRoot. This is an additional protection; do not rely on the installer folder being renamed.

Apache must apply per-directory authorization rules: `AllowOverride AuthConfig` (or `AllowOverride All`) and either Apache 2.4 `mod_authz_core` or the legacy host authorization module. The root URL lock rule additionally uses `mod_rewrite`/`AllowOverride FileInfo`; if these are unavailable, `Require local` still blocks remote installer requests and the SQL dump remains denied, while PHP blocks execution of `install/index.php`. Production deployments must exclude `install/` regardless of these optional rewrite features.

## Existing installation migration

For an already installed site that predates the marker, first inspect the project root and database host, port, and name printed by the command. Then run from the project directory, substituting those exact values:

```text
php tools/installation-lock.php seal "--confirm=seal|<host>:<port>/<database-name>|<project-root>"
```

The command is CLI-only. It connects using `config.php`, verifies all core tables, checks `dbversion`, and confirms the administrator account before creating the signed marker. It does not change the database. The confirmation binds the action to the displayed project root and database name.

## Deliberate reinstall

1. Back up the database, `config.php`, uploaded files, and site-specific plugins.
2. Stop Apache or restrict the virtual host to local access.
3. From the project directory, run `php tools/installation-lock.php unlock "--confirm=unlock|<host>:<port>/<database-name>|<project-root>"` and verify the displayed root and database endpoint before confirming.
4. Run the installer only at the local `http://localhost/.../install/` address. Confirm table overwrite only when the database is intentionally being replaced.
5. Complete the installer. Verify that `.voxcms-installed` is recreated and installer URLs return HTTP 403.
6. Restore normal Apache access only after those checks pass.

If an installation was interrupted after configuration was saved, run `php tools/installation-lock.php resume "--confirm=resume|<host>:<port>/<database-name>|<project-root>"`. The command is CLI-only, displays its target, and changes only filesystem markers. It refuses fresh projects, database connection failures, and databases that validate as complete. It does not create the completed marker; resume through localhost and let the installer finish.

For a complete database with a missing or invalid marker, use the explicitly confirmed `seal` action instead. For an invalid marker with an incomplete database, `resume` quarantines the invalid marker and creates an in-progress marker; the installer remains local-only. Do not delete markers to troubleshoot a live, publicly accessible site.
