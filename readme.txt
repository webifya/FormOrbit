=== Webform ===
Contributors: webifya
Tags: form, form builder, drag and drop, multi-step form, contact form
Requires at least: 6.2
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A visual drag-and-drop, multi-step form builder for WordPress.

== Description ==

Webform provides a native WordPress form-building experience:

* Drag-and-drop field ordering
* Multiple stages with progress indicators
* Text, email, textarea, dropdown, radio, checkbox, number, date, phone, and heading fields
* Required-field and email validation
* AJAX submission without page reload
* WordPress-native entry storage and admin entry browser
* Email notifications
* Entry filtering, pagination, deletion, and CSV export
* One-click form duplication
* Shortcode embedding
* Nonces, capability checks, sanitization, escaping, and a spam honeypot

== Installation ==

1. Upload the `Webform` folder to `/wp-content/plugins/`.
2. Activate Webform in the Plugins screen.
3. Open Webform > Add New.
4. Build and save your form.
5. Copy the generated `[webform id="123"]` shortcode into any post or page.

== Changelog ==

= 1.1.0 =
* Added entry filtering, pagination, deletion, and safe CSV export.
* Added one-click form duplication and unsaved-change protection.
* Prevented duplicate labels from overwriting submission data.
* Strengthened option validation, schema limits, field identifiers, and rate limiting.
* Improved multi-step accessibility and checkbox validation.
* Added backward compatibility for existing v1.0 submission data.

= 1.0.0 =
* Initial release.
