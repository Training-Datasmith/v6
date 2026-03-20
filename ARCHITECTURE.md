# Architecture: v6

## Purpose

CubeCart v6 — a PHP e-commerce platform for self-hosted online stores. Provides product catalog, shopping cart, checkout with multiple payment gateways, order management, customer accounts, and a plugin/skin extension system.

## Directory Structure

```
classes/              - Core domain classes: cubecart, cart, catalogue, config, db/, encryption, gui, etc.
controllers/          - Request handlers for storefront and admin sections
admin/                - Admin panel templates, JS, and CSS
modules/              - Plugin modules: payment gateways, shipping methods, social integrations
skins/                - Front-end themes (HTML templates, CSS, JS)
includes/             - Bootstrap and global utility includes
js/                   - Shared JavaScript assets
ini.inc.php           - Platform bootstrap: PHP version check, path setup, constants
index.php             - Front-office entry point
admin.php             - Back-office entry point
```

## Key Design Decisions

- **Single-file class architecture**: Core services (cart, catalogue, config, encryption) are each a single class file under `classes/`. The central `cubecart` class acts as a service locator/application object.
- **Database abstraction**: The `db/` directory contains a lightweight database abstraction layer, keeping SQL out of controllers and domain classes.
- **Plugin/gateway pattern**: Payment gateways and shipping carriers live under `modules/` and implement a known interface, allowing third-party extensions without modifying core files.
- **Skin-based theming**: Storefront templates are organized into skins. The active skin is configured in the admin panel; all HTML output is driven by skin templates.

## Extension Points

- **Modules**: Add a directory under `modules/` implementing the gateway or hook interface to add payment gateways, shipping providers, or feature plugins.
- **Skins**: Create a new directory under `skins/` with custom templates to change the storefront appearance.

## Dependency Flow

```
HTTP Request → index.php (or admin.php)
  └─> ini.inc.php — bootstrap, constants, session start
  └─> cubecart class — load config, init cart, catalogue, db
        └─> Controller dispatch — route request to handler
              └─> catalogue/cart/order classes — domain logic + DB queries
              └─> Skin template engine — render HTML response
```
