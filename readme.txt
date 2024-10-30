=== Last Used Items ===

Contributors: florianwetzl
Tags: last used items, productivity, developer
Requires at least: 4.6
Tested up to: 5.4
Stable tag: 5.4
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Date: 26.02.2020
Version: 2.15


This plugin lets you quickly view your last edited WordPress items, similar to the "last used" function in Office and Adobe Products.

== Description ==

This plugin works for posts, pages and custom post types, e.g. products.

After installation and activation a "Last Used" menu entry appears on top of the screen in the wp-admin area with the last edited items.

If your Site-URL changes during development, the log entries will have the wrong url. In this case use "Last Used Items Clear Log" in the settings options.

The plugin keeps database storage and traffic slim by only saving up to 1000 slim log-entries.
I have been testing the plugin with other major plugins, with no known issus.

== Screenshots ==

1. Hover over "Last Used" menu entry  

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress

== Frequently Asked Questions ==

None yet, feel free to ask.

== Changelog ==

= 2.15 =
*Release date: 12.06.2020*

* Fix: fixed update mechanic

= 2.14 =
*Release date: 01.06.2020*

* Feature: optimized "Last Used Items Clear Log" settings page.

= 2.13 =
*Release date: 31.05.2020*

* Feature: log entries are now user specific, so every user hast its own last used items.
* Feautre: there is a new option "Last Used Items Clear Log" in the settings options. Use ist if your site-url changes and log-entries do not match your site-url anymore.

= 2.12 =
*Release date: 30.05.2020*

* Fix: optimized database queries

= 2.11 =
*Release date: 26.02.2020*

* Fix: make sure 10 items are displayed, not less - if already available

= 2.1 =
*Release date: 26.02.2020*

* Fix: now supports https://

= 2 =
*Release date: 24.02.2020*

* Feature: button now on top screen (wp quicklinks toolbar), no forwarding anymore

= 1.2.2 =
*Release date: 15.02.2020*

* Fix: changed forwarding from php to javascript, since some hosts did not support php-forwarding

= 1.2.1 =
*Release date: 07.02.2020*

* Fix: new published custom post types are now correctly shown by the plugin
* Fix: i changed the last used icon in the wp-admin area

= 1.2 =
*Release date: 02.02.2020*

* Fix: sanitizing data in plugin

= 1.1 =
*Release date: 30.01.2020*

* Fix: new published pages are now shown by the plugin