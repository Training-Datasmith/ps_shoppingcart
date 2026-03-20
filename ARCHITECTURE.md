# Architecture: ps_shoppingcart

## Purpose

A PrestaShop front-office widget module that displays a persistent cart summary (item count and total) in the store header, with AJAX-powered cart updates and a dropdown preview.

## Directory Structure

```
ps_shoppingcart.php              - Module class; hook listeners and widget rendering
controllers/front/ajax.php       - Front controller handling AJAX cart-update responses
views/templates/hook/            - Smarty template for the cart widget
upgrade/                         - SQL/PHP migration scripts
tests/                           - PHPUnit test stubs and PHPStan bootstrap
translations/                    - Locale string overrides
```

## Key Design Decisions

- **WidgetInterface**: Implements `WidgetInterface` for theme-editor positioning.
- **AJAX controller**: Cart mutations trigger an AJAX request to `controllers/front/ajax.php` which returns a partial HTML response to update the widget without a full page reload.
- **Context-aware**: Uses the `Context` object to access the active cart, currency, and language without coupling to global state.

## Extension Points

- Override `getWidgetVariables()` to add extra cart data to the template.
- Extend the AJAX controller to return additional cart details (e.g., product thumbnails).

## Dependency Flow

```
ps_shoppingcart (Module + WidgetInterface)
  └─> renderWidget()          — renders the cart widget template
        └─> getWidgetVariables() — reads cart from Context
  └─> controllers/front/ajax.php — returns partial update HTML
```
