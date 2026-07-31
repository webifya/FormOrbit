=== FormOrbit ===
Contributors: mahfuzar
Tags: form, form builder, drag and drop, multi-step form, contact form
Requires at least: 6.2
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 4.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A visual drag-and-drop, multi-step form builder for WordPress.

== Description ==

FormOrbit provides a native WordPress form-building experience:

* Drag-and-drop field ordering
* Multiple stages with progress indicators
* Name, text, email, textarea, dropdown, radio, checkbox, number, date, phone, URL, consent, secure file upload, and heading fields
* Conditional field visibility
* Required-field and email validation
* AJAX submission without page reload
* WordPress-native entry storage and admin entry browser
* Email notifications
* Entry filtering, pagination, deletion, and CSV export
* One-click form duplication
* Configurable submit labels and confirmation redirects
* Shortcode embedding
* Nonces, capability checks, sanitization, escaping, and a spam honeypot
* Login requirements and total submission limits
* Polls with live percentage results
* Scored quizzes with configurable answers and points
* 10 free ready-to-customize form templates
* Time, slider, rating, hidden, HTML, CAPTCHA, and page-break tools
* Modern, minimal, and rounded style presets with custom colors
* Optional Google reCAPTCHA v2 verification
* Import converters for WPForms, Gravity Forms, Fluent Forms, and Contact Form 7

FormOrbit is created by Mahfuzar Rahman. Advanced automation, payment, document, and marketing features are available in a separately distributed Pro add-on.

== Installation ==

1. Upload the `formorbit` folder to `/wp-content/plugins/`.
2. Activate FormOrbit in the Plugins screen.
3. Open FormOrbit > Add New.
4. Build and save your form.
5. Copy the generated `[formorbit id="123"]` shortcode into any post or page.

== Screenshots ==
1. Form Editor View
2. Available Fields on Free Mode
3. Start with free templates
4. Dashboard View
5. Formorbit Tools (Import/Export/Resources)
6. Email SMTP Configuration
7. Shortcode 
8. Templates Gallery 

== Changelog ==

= 4.6.0 =
* Added international phone entry with a configurable default country, visitor country selector, live formatting, and E.164-style server validation.
* Added customizable child-field widths inside Pro groups and repeaters, with responsive full-width defaults for newly added children.
* Added a compact live width control beside nested child actions.
* Improved live move-out and drag-out behavior for fields nested inside Pro containers.

= 4.5.0 =
* Kept the stage and Add field toolbar visible while scrolling long forms.
* Added responsive one-to-four-column option layouts for checkbox, radio, poll, and quiz fields.
* Expanded the native icon gallery with common business, communication, product, scheduling, accessibility, and social icons.
* Improved the live builder and real-form preview for option-column layouts.
* Added a guided advanced-calculation editor for FormOrbit Pro.

= 4.4.4 =
* Fixed the Group and Repeater child-field popup layout so controls remain organized and responsive.
* Added persistent live-canvas actions for moving or deleting child fields.
* Added drag-out support for moving a nested child back into the main form.
* Replaced the wide Pro duplicate-field button with a compact accessible icon action.

= 4.4.3 =
* Kept general product information in Free while moving licensed documentation, changelog, and support resources to Pro.

= 4.4.2 =
* Moved Field Group and Repeater child settings into a spacious responsive modal.
* Made child fields on the live canvas and in the sidebar open the same focused editor.
* Improved child-field labels, numeric controls, choices, content editors, and actions at every screen size.

= 4.4.1 =
* Added direct selection and focused editing for fields nested inside Pro groups and repeaters.
* Replaced the crowded advanced child-field layout with a compact field navigator and organized single-field editor.
* Improved nested-field action reliability and added a responsive baseline for every public form.

= 4.4.0 =
* Rebuilt Field Group and Repeater editing as an interactive live-builder experience.
* Added drag-and-drop movement of compatible top-level fields into groups and repeated rows.
* Added inline editing for group, repeater, and child-field labels directly on the canvas.
* Added drag handles for reordering fields inside containers and inline child removal.
* Added a one-click action for moving a child field back to the main form.
* Added automatic expansion of Name and Address fields into their editable component fields.
* Moved detailed child options into a compact Advanced settings panel.
* Preserved licensed container schemas when Pro is temporarily inactive.

= 4.3.1 =
* Reorganized the FormOrbit admin menu around common form-building workflows.
* Moved Import / Export into a consolidated Tools screen.
* Added a Resources area with direct links to the FormOrbit changelog and product page.
* Moved licensed Support & Feedback access into Tools to reduce sidebar clutter.
* Preserved old Import / Export bookmarks with an automatic redirect to Tools.

= 4.3.0 =
* Replaced the example-only style preview with the form currently open in the builder.
* Added a Preview button beside Save form for opening the current unsaved form in a same-page modal.
* Added desktop, tablet, and mobile viewport controls to the live preview.
* Added live multi-stage navigation and current appearance settings, field layouts, Pro fields, and safely scoped custom CSS to previews.
* Added shared Undo and Redo controls with keyboard shortcuts and a 60-step builder history.
* Added history coverage for fields, stages, nested containers, appearance settings, confirmations, and access controls.

= 4.2.0 =
* Added nested-field builder support for true Pro Field Group and Repeater containers.
* Added compact child-field editing with type, label, placeholder, choices, required status, and numeric controls.
* Added drag-to-reorder controls for fields inside Pro containers.
* Added an explicit compatibility conversion path for legacy groups and single-value repeaters.
* Improved builder previews so groups and repeaters display their actual child-field layouts.

= 4.1.2 =
* Replaced the large editor Embed panel with a compact Embed button beside Save form.
* Added an accessible, responsive Embed dialog for copying the WordPress shortcode or PHP template code.
* Added overlay and Escape-key closing with focus returned to the Embed button.

= 4.1.1 =
* Added a persistent Embed panel directly to the form editor.
* Added one-click copying for the WordPress shortcode.
* Added a ready-to-copy PHP template snippet for trusted theme and plugin templates.
* Revealed both embed formats immediately after a new form is saved for the first time.

= 4.1.0 =
* Added the advanced Divider design panel used by FormOrbit Pro.
* Added live Divider previews for labels, solid, dashed, dotted, and double rules.
* Expanded the searchable Pro field-icon gallery with 28 additional business, content, device, and workflow icons.
* Added a Pro-only one-click field duplication shortcut in the builder.

= 4.0.0 =
* Completed the FormOrbit filename transition by removing the legacy webform.php entry file.
* Standardized the distributable package on formorbit.php and FormOrbit-branded include filenames.
* Established a clean major-version baseline for future WordPress.org and direct updates.

= 3.2.0 =
* Renamed the primary plugin entry file to formorbit.php.
* Renamed all PHP include files from class-webform-* to class-formorbit-*.
* Added an automatic legacy entry-file migration so existing installations remain activated after updating.

= 3.1.1 =
* Renamed generated form-management, email-delivery, import, submission, and AJAX actions to FormOrbit.
* Kept legacy action handlers so existing saved links and integrations continue working.

= 3.1.0 =
* Renamed all user-facing admin page URLs from webform to formorbit, with safe redirects for saved legacy links.
* Added an accessible Hide label on public form option for supported fields.
* Added compatibility support for the new Pro Divider element.
* Fixed Pro field schema sanitization when premium field types are enabled.

= 3.0.3 =
* Changed the generated and displayed shortcode to `[formorbit id="123"]`.
* Kept the previous shortcode available only as a compatibility alias for existing embeds.
* Moved every upgrade action to the organized `/plugins/formorbit/` marketing page.

= 3.0.2 =
* Connected every Free upgrade action to the dedicated FormOrbit Pro marketing page.
* Updated product branding and campaign tracking for the new FormOrbit purchase journey.

= 3.0.1 =
* Renamed the plugin to FormOrbit with the unique `formorbit` slug and text domain.
* Updated Free and Pro upgrade messaging, menus, settings, and documentation to the FormOrbit product identity.
* Preserved the existing shortcode, stored forms, entries, hooks, and internal identifiers for backward compatibility.

= 3.0.0 =
* Moved webhook delivery completely into the separately distributed Pro add-on.
* Sanitized submitted field values before validation, conditions, and extension hooks.
* Hardened decoded builder settings and form schemas before storage.
* Expanded the Google reCAPTCHA external-service disclosure.
* Updated the WordPress.org contributor identity and removed the unavailable generic plugin URI.

= 2.7.1 =
* Added past-only, future-only, unrestricted, and custom ranges for Date fields.
* Added server-side date range validation using the WordPress site date.
* Added adjustable visible rows for Long Text fields with live builder previews.
* Added shared support for the expanded Pro field-icon gallery.

= 2.7.0 =
* Added a dedicated Name field with responsive first-name and last-name inputs.
* Added the Rich Text / Agreement field to the visible Pro field catalog.
* Added shared builder support for Pro rich-text editing, media, previews, and safe frontend rendering.
* Preserved Rich Text fields in locked mode when FormOrbit Pro is unavailable.

= 2.6.2 =
* Moved the secure Preview shortcut beside each active form title.
* Simplified hover actions by removing the duplicate Preview link.

= 2.6.1 =
* Redesigned the Forms screen as a unified, compact management dashboard.
* Added segmented All Forms and Trash navigation with clear counts.
* Added instant form-title and ID search.
* Added one-click shortcode copying with visual confirmation.
* Improved form metadata, action discoverability, entry links, status badges, dates, and responsive mobile cards.

= 2.6.0 =
* Added an All/Trash switch to the Forms screen with secure restore and permanent-delete actions.
* Permanently deleting a trashed form now also removes its stored entries.
* Consolidated confirmation-related extension controls into the Confirmation panel.
* Added discoverable previews for Pro visitor emails, Save & Continue, and PDF attachments.
* Clarified the difference between admin notification delivery and visitor confirmations.

= 2.5.1 =
* Fixed empty builder alignment on blank and failed-import forms.
* Improved Formidable XML field discovery and normalized JSON or serialized choice data into individual options.
* Prevented imports without a usable form structure from creating empty forms.

== Upgrade Notice ==

= 4.0.0 =
This release removes the historical webform.php compatibility loader. Sites upgrading directly from a version older than 3.2.0 may need to reactivate FormOrbit once after installing the update.
* Moved Email Delivery directly below Add New for faster access.
* Added the safe label markup extension used by licensed field icons.

= 2.5.0 =
* Added JSON, CSV, and XML form imports with automatic format detection.
* Added dedicated Formidable Forms and Forminator migration support.
* Added a unified Import / Export screen with licensed Pro export tools.
* Preserved stages, fields, settings, conditional rules, and Pro field data when moving FormOrbit exports.

= 2.4.1 =
* Upgraded the Forms screen with native hover actions for editing, settings, duplication, trash, and secure previews.
* Added entry, embed, status, creation date, and update date columns.
* Added responsive form-list behavior and direct editor settings links.

= 2.0.0 =
* Added desktop, tablet, and mobile builder preview widths.
* Added Free previews for reusable Pro themes and per-field styling.
* Added a licensed field-style schema and frontend extension path.
* Improved responsive field layouts and mobile fallbacks.

= 1.9.0 =
* Added a transparent Free/Pro preset selector with all premium preset names visible.
* Added previews of Pro typography, color, layout, border, button, and custom CSS controls.
* Added frontend design variables for licensed typography, sizing, colors, width, spacing, and corner controls.
* Added a safe extension pipeline for scoped per-form Pro CSS.

= 1.8.0 =
* Added accurate visual builder previews for every standard field type.
* Added native-looking upload, consent, choice, date, time, rating, slider, CAPTCHA, HTML, hidden, and heading previews.
* Added dedicated calculation, field-group, and e-signature previews when FormOrbit Pro is active.
* Improved field-card spacing, labels, responsive behavior, and selected-state clarity.

= 1.7.1 =
* Redesigned the reCAPTCHA settings screen and corrected checkbox sizing.
* Added Google Cloud reCAPTCHA checkbox support using Enterprise assessments.
* Kept classic and migrated v2 SiteVerify compatibility for existing keys.
* Added Site Key, Cloud project, restricted API key, and expected-action controls.
* Added Pro integration cards to the locked Pro feature section.

= 1.7.0 =
* Replaced the permanent field sidebar with a spacious, icon-based Add Field picker.
* Added an Integrations tab inside the form editor with clear Free and Pro states.
* Improved the Pro feature recommendations and expanded the main form canvas.
* Added accessible keyboard and backdrop controls for the field picker.

= 1.6.1 =
* Added template and style-preset extension APIs for licensed Pro releases.

= 1.6.0 =
* Reorganized form settings into Field, Confirmation, Access, and Style panels.
* Made the field palette more compact and professional.
* Added locked Pro field previews to the free builder.
* Added global Google reCAPTCHA v2 configuration and server verification.
* Added import conversion for WPForms, Gravity Forms, Fluent Forms, and Contact Form 7 exports.
* Suppressed unrelated plugin notices only inside the form editor.
* Improved color controls and settings-page styling.

= 1.5.0 =
* Added visible stage rename controls.
* Added an automatic template chooser when creating a new form.
* Added time, slider, rating, hidden, safe HTML, math CAPTCHA, and page-break tools.
* Added Modern, Minimal, and Rounded presets with configurable colors.
* Added extension APIs for Pro calculations, grouped layouts, and signatures.

= 1.4.1 =
* Added a filtered submission response for licensed payment and automation add-ons.

= 1.4.0 =
* Changed the author URI to Mahfuzar Rahman's WordPress.org profile.
* Added poll fields with live aggregate results after submission.
* Added scored quiz questions with configurable correct answers and points.
* Added 10 free starter templates plus a blank-form option.
* Added a template library page and one-click template loading.
* Updated the Pro offer to include 20 additional premium templates.

= 1.3.0 =
* Added direct checkout links for annual single-site, annual 10-site bundle, and lifetime FormOrbit Pro plans.
* Added Pro capability previews inside the form builder.
* Added login requirements, submission limits, and custom closed-form messages.
* Added submission timing checks to strengthen automated spam protection.
* Prevented file uploads before non-file validation succeeds and cleaned up partial multi-file failures.
* Improved upload error handling and extension points for licensed add-ons.

= 1.2.1 =
* Updated plugin branding for Mahfuzar Rahman and Web Ninja LLC.
* Added complete GPL-compatible license headers.
* Updated WordPress compatibility metadata through version 7.0.
* Corrected field-label internationalization for WordPress.org validation.
* Added documented extension hooks for optional add-ons and integrations.
* Removed development-only hidden files from the distribution.

= 1.2.0 =
* Added visual conditional logic for showing fields from earlier answers.
* Added secure file uploads with extension and size restrictions.
* Added URL and consent fields.
* Added configurable submit labels and confirmation redirects.

= 1.1.0 =
* Added entry filtering, pagination, deletion, and safe CSV export.
* Added one-click form duplication and unsaved-change protection.
* Prevented duplicate labels from overwriting submission data.
* Strengthened option validation, schema limits, field identifiers, and rate limiting.
* Improved multi-step accessibility and checkbox validation.
* Added backward compatibility for existing v1.0 submission data.

= 1.0.0 =
* Initial release.

== External services ==

This plugin can use Google reCAPTCHA to detect automated or abusive submissions. The service is contacted only when a site administrator enables reCAPTCHA, supplies credentials, and places a CAPTCHA field in a form.

On a page containing an enabled CAPTCHA field, the browser loads the Google reCAPTCHA script from `google.com`. When the form is submitted, classic or migrated v2 mode sends the reCAPTCHA response token and visitor IP address to Google's SiteVerify API. Google Cloud reCAPTCHA Enterprise mode sends the response token, configured site key and action, visitor IP address, and browser user agent to the reCAPTCHA Enterprise assessments API.

Google reCAPTCHA service information: https://www.google.com/recaptcha/about/

Google Terms of Service: https://policies.google.com/terms

Google Privacy Policy: https://policies.google.com/privacy

Google Cloud Service Terms: https://cloud.google.com/terms/service-terms

Site owners are responsible for disclosing this service in their own privacy policy and complying with Google's terms, privacy policy, quotas, and billing requirements.
