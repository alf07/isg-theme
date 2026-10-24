# Plugin module map

This is the home for functionality that should remain available independently of the active theme.

- `content/` — post types, taxonomies and content-data registration after the model is agreed.
- `admin/` — admin screens, settings and field validation that are specific to this site.
- `integrations/` — external-provider adapters and orchestration for demo/real integrations.
- `tests/` — focused tests as non-trivial behavior is implemented.

The scaffold intentionally does not register arbitrary CPTs or invent fields before the data model is agreed. Add each module to `includes/bootstrap.php` with an explicit `require_once` when it contains working code.
