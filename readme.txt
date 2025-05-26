=== String Replacer ===
Contributors: ionutbaldazar
Tags: string replace, translation, email filter, content filter, admin tool
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.2
Stable tag: 1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Plugin URI: https://github.com/baiatulutata/string-replacer

Replace any string visible to site visitors or found in outgoing emails—titles, content, footers, and more. Comes with a dynamic admin interface.

== Description ==

String Replacer lets you define pairs of strings to search and replace across your WordPress site — including content, post titles, footer text, and outgoing emails. It works instantly and includes a simple admin interface for managing replacements.

== Features ==

- Replace strings in:
  - Post **titles**
  - Post **content**
  - Site-wide output (e.g., **footer**, **widgets**, etc.)
  - Outgoing **emails** (`wp_mail()`)
- Simple **admin UI** with:
  - Add/remove rows
  - Live search
  - Pagination
- Replaces email addresses and works inside `mailto:` links
- Supports multilingual and branding replacement use cases
- Fully local, compliant with WordPress plugin guidelines

== Installation ==

1. Upload the plugin to `/wp-content/plugins/`, or install via the WordPress Plugin Directory.
2. Activate it via the 'Plugins' screen.
3. Navigate to **Settings → String Replacer** to add your string pairs.

== Usage ==

1. In the admin screen, add one or more rows:
   - "Original String" (e.g., `Hello`)
   - "Replacement String" (e.g., `Bonjour`)
2. Save your changes.
3. The plugin will handle replacements in frontend output and emails automatically.

== Examples ==

- Replace `support@oldsite.com` → `help@newbrand.com`
- Replace `Hello` → `Bonjour`
- Replace `ACME Inc.` → `NewCorp`

== Filters & Extensibility ==

This plugin hooks into:
- `the_title` and `the_content`
- `template_redirect` output buffering
- `wp_mail` filter (subject, message, and headers)

Developers can use `sr_replace_strings( $text )` to apply replacements manually in custom contexts.

== Frequently Asked Questions ==

= Will it work with custom post types or WooCommerce? =
Yes, any output that uses `the_title` or `the_content` filters, or appears in final HTML output, will be processed. This includes many plugins.

= Will it replace strings in dynamic JavaScript or AJAX responses? =
No, it only replaces visible strings rendered in HTML or passed through known filters.

== Changelog ==

= 1.2 =
* Added `wp_mail` support for replacing strings in emails
* Added search, pagination, and better sanitization in the admin grid
* Localized all scripts and removed CDN dependencies for WP.org compliance

== Author ==

Created by **Ionut Baldazar**
GitHub: https://github.com/baiatulutata
Email: contact@yourdomain.com (update before release)
