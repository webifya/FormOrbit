=== FormOrbit ===
Contributors: mahfuzar
Tags: form, form builder, drag and drop, multi-step form, contact form
Requires at least: 6.2
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 4.9.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Build responsive forms visually with multi-step layouts, templates, SMTP delivery, imports, analytics, polls, quizzes, and secure entries.

== Description ==

FormOrbit is a visual WordPress form builder for contact forms, registrations, surveys, polls, quizzes, applications, feedback forms, and multi-step workflows. Build in WordPress, keep your entries in WordPress, and publish anywhere with a shortcode.

= A WordPress form builder for real business workflows =

Create responsive contact forms, lead-generation forms, quote requests, event registrations, customer surveys, scored quizzes, polls, job applications, booking requests, newsletter forms, and multi-step onboarding forms without writing code. FormOrbit keeps form management and submission data inside WordPress while giving administrators a focused visual builder.

FormOrbit Pro extends the same interface for payment forms, donation forms, order forms, contracts, electronic signatures, appointments, WordPress post submissions, CRM routing, calendar scheduling, PDF delivery, and automated email or SMS follow-ups.

= Build forms without code =

* Drag-and-drop field ordering with responsive live previews
* Multi-step forms with editable stage names and progress navigation
* Text, name, email, long text, dropdown, radio, checkbox, number, date, time, phone, website, consent, upload, rating, slider, hidden, HTML, heading, page-break, and CAPTCHA fields
* Safe field-type conversion while preserving compatible labels, choices, styling, and validation
* Conditional field visibility, required fields, validation, and AJAX submission
* 10 ready-to-customize templates for common business workflows
* Modern, minimal, and rounded style presets with configurable colors

= Collect, understand, and manage responses =

* WordPress-native entry storage with filtering, pagination, deletion, and CSV export
* Polls with live percentage results
* Scored quizzes with configurable answers and points
* Analytics for views, unique visitors, engagement, submissions, conversion, dates, and individual forms
* Admin email notifications and customizable success messages or redirects
* Login requirements, submission limits, honeypot protection, and Google reCAPTCHA support

= Email delivery and migration tools =

* Built-in SMTP presets for Mailgun, Brevo, SendGrid, Amazon SES, Postmark, Mailjet, MailerSend, Gmail, Google Workspace, Microsoft 365, Outlook, Zoho Mail, and custom SMTP
* Import tools for WPForms, Gravity Forms, Fluent Forms, Contact Form 7, Formidable Forms, and Forminator
* One-click form duplication and easy shortcode or PHP-template copying

= Upgrade to FormOrbit Pro =

[FormOrbit Pro](https://www.webninjallc.com/plugins/formorbit/) extends the same builder with:

* Calculations, signatures, agreements, repeaters, field groups, appointment, commerce, donation, order-total, and WordPress post fields
* Stripe, PayPal, and Square hosted checkout plus bank-transfer workflows
* Mailchimp, AWeber, Brevo, ActiveCampaign, Kit, GetResponse, GoHighLevel, calendars, Zapier, and webhooks
* Twilio, Vonage, MessageBird, and Telnyx SMS notifications
* Scheduled email and SMS follow-ups, visitor email templates, PDF attachments, and Save & Continue
* Public website form importing, premium templates, Google Fonts, advanced styling, saved themes, and custom CSS

Plans:

* [Single-site annual — $19.99/year](https://www.webninjallc.com/product/formorbit-pro/)
* [Up to 10 sites — $99.99/year](https://www.webninjallc.com/product/formorbit-pro-bundle/)
* [Single-site lifetime — $249.99](https://www.webninjallc.com/product/formorbit-lifetime/)

FormOrbit is created by Mahfuzar Rahman and maintained by Web Ninja LLC.

== Frequently Asked Questions ==

= Can I build multi-step forms in the free version? =

Yes. Add and rename stages, move fields between them, and publish the completed form with the generated shortcode.

= Where are submissions stored? =

Entries are stored in your WordPress database and managed from the FormOrbit Entries screen. FormOrbit does not require a third-party form-storage account.

= Can I send email through my own provider? =

Yes. Email Delivery includes presets for popular SMTP services and a custom SMTP option. Your provider may require an app password, SMTP credential, or verified sender domain.

= Does FormOrbit include spam protection? =

Yes. Forms use WordPress nonces, server-side validation, sanitization, a honeypot, rate protection, and optional Google reCAPTCHA.

= Can I import forms from another plugin? =

Yes. The Tools screen supports several popular WordPress form-builder exports. FormOrbit Pro can also inspect a supported public HTML form from a website URL and convert its fields into an editable form.

= Does the free plugin collect payments? =

Payment and commerce fields are part of FormOrbit Pro. Pro sends customers to supported providers' secure hosted checkout pages so FormOrbit does not store card details.

= Will my forms disappear if FormOrbit Pro is deactivated? =

No. Form structures and entries remain stored. Features supplied by the separately distributed add-on require that add-on to remain installed and active.

= Do I need FormOrbit Free when using Pro? =

No. FormOrbit Pro is distributed as a separate standalone plugin and can run without FormOrbit Free.

== Installation ==

1. Upload the `formorbit` folder to `/wp-content/plugins/`.
2. Activate FormOrbit in the Plugins screen.
3. Open FormOrbit > Add New.
4. Build and save your form.
5. Copy the generated `[formorbit id="123"]` shortcode into any post or page.

== Changelog ==

= 4.9.7 =
* Updated release metadata after the FormOrbit 4.9.6 package was published.
* Expanded the public changelog to accurately document the completed Free builder, compatibility, and WordPress.org compliance work.

= 4.9.6 =
* Removed all Pro field implementations, schemas, previews, sanitizers, and license-dependent field gates from the free plugin.
* Kept only generic extension hooks for separately distributed add-ons.
* Removed paid-feature previews and disabled upgrade controls from operational Free screens.
* Restored the responsive form-builder layout and distinct previews for supported Free field types.
* Added editor controls for date ranges, phone-country behavior, conditional visibility, and row layout.
* Opened the Field settings tab automatically when a field is selected.
* Added safe compatibility notices for unsupported add-on fields instead of rendering them as text fields.
* Prevented unsupported fields from being submitted or silently converted when their providing add-on is inactive.
* Made Compatibility Profile sharing optional in every Free installation.
* Removed license-related values from the optional Free compatibility payload.
* Documented the public human-readable source and development process.

= 4.9.5 =
* Renamed Usage Insights to Compatibility Profile and replaced the technical explanation with concise customer-friendly guidance.
* Clarified that compatibility reporting never includes forms, entries, passwords, payment information, or visitor data.
* Expanded the WordPress.org description with practical form use cases and Pro workflow examples.

= 4.9.4 =
* Replaced the style-preview Dashicon with a centered, self-contained preview symbol.
* Matched the preview button height and alignment to the adjacent Style Preset selector.

= 4.9.3 =
* Replaced the Create Form Dashicon with a self-contained SVG so the symbol remains visible with FormOrbit Pro and custom admin styles.
* Normalized the Create Form icon size, alignment, and contrast across supported WordPress admin themes.

= 4.9.2 =
* Improved site-profile compatibility and surfaced server validation messages.
* Rebuilt the Upgrade to Pro screen with a complete categorized feature showcase.

= 4.9.1 =
* Added support for the reorganized Pro form Settings workspace.
* Improved direct links to editor settings and document options.

= 4.9.0 =
* Added optional site-profile reporting for Free and Pro installations.
* Added transparent usage-sharing controls and disclosure in FormOrbit Settings.
* Added daily profile refreshes with a stable installation identifier.

= 4.8.3 =
* Replaced the Plugins screen Settings shortcut with a direct Add New Form action.
* Corrected vertical alignment for icons in create, preview, field, embed, copy, and resource buttons.
* Improved consistent button spacing across the form manager, builder, and Tools screens.

= 4.8.2 =
* Improved the installed-plugin description to communicate FormOrbit's main use cases and capabilities.
* Added a prominent Go Pro action beside Settings and Deactivate; it disappears automatically when Pro is active.

= 4.8.1 =
* Rebuilt the WordPress.org description around clear use cases, Free features, migration tools, security, and data ownership.
* Added a complete FAQ and transparent FormOrbit Pro feature and pricing information.

= 4.8.0 =
* Added builder compatibility and upgrade previews for Pro commerce fields, SMS providers, scheduled follow-ups, and public URL importing.
* Extended safe field-type conversion for the new Pro field families.

= 4.7.1 =
* Merged the standard and recommended Pro field catalogs into one field picker.
* Added provider presets for Mailgun, Brevo, SendGrid, Amazon SES, Postmark, Mailjet, MailerSend, Gmail, Microsoft 365, Zoho Mail, and custom SMTP.
* Clarified safe field-type conversion in the builder.

= 4.7.0 =
* Added Analytics & Reporting with form views, session-based unique visitors, engaged visitors, submissions, conversion, daily activity, form filters, and date ranges.
* Added safe field-type conversion to the field settings panel.
* Added a Settings action beside Deactivate on the WordPress Plugins screen.
* Added a polite, dismissible WordPress.org review request for established Free installations.
* Corrected editor field-icon vertical alignment.
* Improved Pro integration empty states and prevented audience/list controls from being cut off.
* Added the Free-side compatibility layer and upgrade preview for Pro WordPress Post Fields.

= 4.6.3 =
* Added WordPress.org plugin banners, icons, and screenshots.
* Improved plugin branding, directory listing presentation, and readme formatting.
* Fixed minor interface inconsistencies.

= 4.6.1 =
* Added directory screenshots, plugin icons, and banner assets.
* Fixed minor presentation issues.

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

The optional Compatibility Profile connects to Web Ninja LLC to help improve FormOrbit updates, compatibility, and support. It shares basic website, administrator contact, WordPress, PHP, theme, locale, environment, and FormOrbit version details. It never shares forms, entries, passwords, payment information, or visitor data. Administrators can enable or disable it at any time.

FormOrbit service information and privacy details: https://www.webninjallc.com/plugins/formorbit/

Site owners are responsible for disclosing this service in their own privacy policy and complying with Google's terms, privacy policy, quotas, and billing requirements.

== Source code and development ==

The complete, human-readable source for FormOrbit is publicly maintained at:
https://github.com/webifya/FormOrbit

The JavaScript and CSS files distributed in `assets/` are the maintained source files. They are not generated, transpiled, bundled, or minified, and no private source or build step is required to reproduce them. Contributors can edit these files directly. Release packages are created from the tagged repository contents by the public GitHub Actions workflow in `.github/workflows/deploy.yml`.
