# String Replacer

**Contributors:** baiatulutata  
**Tags:** string replace, translation, email filter, content filter, admin tool  
**Requires at least:** 5.0  
**Tested up to:** 6.8 
**Requires PHP:** 7.2  
**Stable tag:** 1.4  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Replace any string visible to site visitors or found in outgoing emails—titles, content, footers, and even a simple admin grid interface.

---

## Features

- Replace strings in:
    - Post **titles**
    - Post **content**
    - Site-wide output (e.g., **footer**, **widgets**, etc.)
    - Outgoing **WordPress emails** (`wp_mail()`)
- Simple **admin interface** to manage multiple string pairs
- **Dynamic grid** with:
    - Add/remove rows
    - Live search
    - Pagination
- Replaces email addresses and works inside `mailto:` links
- Fully works with multilingual strings or brand replacements

---

## Installation

1. Upload the plugin to the `/wp-content/plugins/` directory, or install it via the WordPress Plugin Directory.
2. Activate the plugin through the ‘Plugins’ screen in WordPress.
3. Go to **Settings → String Replacer** to manage your replacement strings.

---

## Usage

1. Add one or more rows for:
    - Original string (e.g., `Hello`)
    - Replacement string (e.g., `Hi`)
2. Save changes.
3. Strings will be replaced automatically throughout the site and outgoing emails.

✅ Example:
- Replace `support@oldsite.com` → `help@newbrand.com`
- Replace `Hello` → `Bonjour`

---

## Filters & Extensibility

This plugin uses:

- `the_title` and `the_content` filters
- `template_redirect` output buffer to catch footer/sidebar strings
- `wp_mail` filter to modify outgoing email subject, body, and headers

You can extend it using the `STRIRE_replace_strings()` function for custom replacements anywhere.

---

## Developer

Created by **Ionut Baldazar**  
Email: `contact@yourdomain.com` (replace with your actual contact)  
GitHub: https://github.com/baiatulutata/

---

## License

This plugin is licensed under the GPLv2 or later.  
You are free to modify and redistribute it under the same terms.
