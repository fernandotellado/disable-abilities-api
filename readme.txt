=== Disable Abilities API ===
Contributors: fernandot, ayudawp
Tags: abilities, api, security, privacy, ai
Requires at least: 6.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Disable WordPress Abilities API completely or selectively. Control AI agent access to your site.

== Description ==

WordPress 6.9 introduced the Abilities API, a system that exposes your site's functionalities to AI agents and automation tools. While useful for some, you may want to disable it for privacy, security, or performance reasons.

**Disable Abilities API** gives you full control over this feature with multiple disable modes:

= Features =

* **REST Endpoints Only** - Block external REST API access while keeping internal PHP functionality
* **Selective Mode** - Choose exactly which abilities to disable from a visual interface
* **Complete Mode** - Disable the entire Abilities API with one click
* **Visual Settings Page** - Easy-to-use admin interface under Settings menu
* **Core Abilities Reference** - See what data each core ability exposes

= Why Disable Abilities API? =

* **Privacy** - Core abilities expose WordPress version, PHP version, admin email, environment type, and more
* **Security** - Reduce attack surface by removing endpoints you don't use
* **Control** - Follow the principle of minimal exposure
* **Performance** - Minor improvement by not loading unused API components

= Core Abilities Blocked =

WordPress 6.9 registers three abilities by default:

* `core/get-site-info` - Site title, URL, admin email, WP version
* `core/get-user-info` - Current user ID, name, roles, locale
* `core/get-environment-info` - Environment type, PHP version, database info

= Requirements =

* WordPress 6.9 or higher (Abilities API was introduced in 6.9)
* PHP 7.4 or higher

== Installation ==

1. Upload the `disable-abilities-api` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Disable Abilities API
4. Select your preferred disable mode and save

== Frequently Asked Questions ==

= Will this break my site? =

No. The Abilities API is optional and disabling it won't affect normal WordPress operation. However, if you use plugins that depend on the Abilities API (like MCP Adapter), they may stop working correctly.

= Can I disable only specific abilities? =

Yes. Use "Selective" mode to choose exactly which abilities to disable from a visual checklist.

= How do I verify it's working? =

Visit `your-site.com/wp-json/wp-abilities/v1/abilities` - you should see a 404 error or empty list depending on your settings.

= Will future WordPress updates override my settings? =

No. Your settings are stored in the database and will persist through updates.

== Screenshots ==

1. Settings page with disable mode options
2. Selective mode with ability checklist
3. Core abilities reference table

== Changelog ==

= 1.0.0 =
* Initial release
* REST endpoints disable mode
* Selective abilities disable mode
* Complete API disable mode
* Admin settings page
* Core abilities reference

== Upgrade Notice ==

= 1.0.0 =
Initial release.

== Support ==

Need help or have suggestions?

* [Official website](https://servicios.ayudawp.com)
* [WordPress support forum](https://wordpress.org/support/plugin/disable-abilities-api/)
* [YouTube channel](https://www.youtube.com/AyudaWordPressES)
* [Documentation and tutorials](https://ayudawp.com)

Love the plugin? Please leave us a 5-star review and help spread the word!

== About AyudaWP ==

We are specialists in WordPress security, SEO, and performance optimization plugins. We create tools that solve real problems for WordPress site owners while maintaining the highest coding standards and accessibility requirements.
