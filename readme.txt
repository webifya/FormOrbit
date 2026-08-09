=== FormOrbit ===
Contributors: mahfuzar
Tags: form, form builder, drag and drop, multi-step form, contact form
Requires at least: 6.2
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 4.9.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Build responsive forms visually with multi-step layouts, templates, SMTP delivery, imports, analytics, polls, quizzes, and secure entries.

== Description ==

FormOrbit is a visual WordPress form builder for contact forms, registrations, surveys, polls, quizzes, applications, feedback forms, and multi-step workflows. Build in WordPress, keep your entries in WordPress, and publish anywhere with a shortcode.

= A WordPress form builder for real business workflows =

Create responsive contact forms, lead-generation forms, quote requests, event registrations, customer surveys, scored quizzes, polls, job applications, booking requests, newsletter forms, and multi-step onboarding forms without writing code. FormOrbit keeps form management and submission data inside WordPress while giving administrators a focused visual builder.

= Build forms without code =

* Drag-and-drop field ordering with responsive live previews
* Multi-step forms with editable stage names and progress navigation
* Text, name, email, long text, dropdown, radio, checkbox, number, date, time, phone, website, consent, upload, rating, slider, hidden, HTML, heading, page-break, and CAPTCHA fields
* Field-specific settings for choices, date ranges, phone countries, uploads, sliders, quizzes, and layout
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

= FormOrbit Pro =

[FormOrbit Pro](https://www.webninjallc.com/plugins/formorbit/) is a separately distributed plugin for advanced form workflows. It can operate independently without FormOrbit Free.

Pro includes advanced fields, hosted payment workflows, marketing and calendar connections, automated notifications, document tools, additional templates, and expanded design controls.

== Frequently Asked Questions ==

= Can I build multi-step forms in the free version? =

Yes. Add and rename stages, arrange fields, and publish the completed form with the generated shortcode.

= Where are submissions stored? =

Entries are stored in your WordPress database and managed from the FormOrbit Entries screen. FormOrbit does not require a third-party form-storage account.

= Can I send email through my own provider? =

Yes. Email Delivery includes presets for popular SMTP services and a custom SMTP option. Your provider may require an app password, SMTP credential, or verified sender domain.

= Does FormOrbit include spam protection? =

Yes. Forms use WordPress nonces, server-side validation, sanitization, a honeypot, rate protection, and optional Google reCAPTCHA.

= Can I import forms from another plugin? =

Yes. The Tools screen supports exports from several popular WordPress form builders.

= Does the free plugin collect payments? =

No. Payment workflows are available through the separately distributed FormOrbit Pro plugin.

= What happens to fields supplied by another plugin if that plugin is inactive? =

FormOrbit identifies unsupported fields in the editor and does not silently convert, display, or process them. Reactivate the plugin that supplied those fields, or convert or remove them before saving.

= Do I need FormOrbit Free when using Pro? =

No. FormOrbit Pro is distributed as a separate standalone plugin and can run without FormOrbit Free.

== Installation ==

1. Upload the `formorbit` folder to `/wp-content/plugins/`.
2. Activate FormOrbit in the Plugins screen.
3. Open FormOrbit > Add New.
4. Build and save your form.
5. Copy the generated `[formorbit id="123"]` shortcode into any post or page.

== Changelog ==

= 4.9.8 =
* Refreshed the visual builder with clearer, field-specific previews.
* Added convenient controls for date ranges, phone-country behavior, conditional visibility, and row layout.
* Opened Field settings automatically when a field is selected.
* Improved compatibility handling when a field is supplied by an inactive add-on.
* Restored responsive builder styling across desktop, tablet, and mobile layouts.
* Simplified product information and refreshed public documentation.

= 4.9.0 =
* Added analytics for form views, engagement, submissions, conversion, and date ranges.
* Improved form management, templates, settings, imports, and email delivery workflows.
* Added optional compatibility reporting with an administrator-controlled setting.

= 4.6.0 =
* Added international phone fields with configurable countries and normalized validation.
* Added responsive choice layouts and improved long-form editing.
* Expanded templates, field icons, and builder usability.

= 4.3.0 =
* Added responsive form previews, undo and redo history, and improved embed tools.
* Reorganized the administration screens around common form-building workflows.
* Improved multi-stage editing and responsive form layouts.

= 4.0.0 =
* Completed the FormOrbit naming and file-structure transition.
* Improved compatibility for existing forms, entries, shortcodes, and saved links.

= 3.0.0 =
* Introduced the FormOrbit product identity and shortcode.
* Strengthened schema validation, submission handling, and external-service documentation.
* Expanded form management and migration support.

= 2.0.0 =
* Added responsive builder widths, richer field previews, templates, analytics foundations, and enhanced entry management.
* Added conditional fields, secure uploads, confirmation redirects, and additional field types.

= 1.0.0 =
* Initial release.

== External services ==

This plugin can use Google reCAPTCHA to detect automated or abusive submissions. The service is contacted only when a site administrator enables reCAPTCHA, supplies credentials, and places a CAPTCHA field in a form.

On a page containing an enabled CAPTCHA field, the browser loads the Google reCAPTCHA script from `google.com`. When the form is submitted, classic or migrated v2 mode sends the reCAPTCHA response token and visitor IP address to Google's SiteVerify API. Google Cloud reCAPTCHA Enterprise mode sends the response token, configured site key and action, visitor IP address, and browser user agent to the reCAPTCHA Enterprise assessments API.

Google reCAPTCHA service information: https://www.google.com/recaptcha/about/

Google Terms of Service: https://policies.google.com/terms

Google Privacy Policy: https://policies.google.com/privacy

Google Cloud Service Terms: https://cloud.google.com/terms/service-terms

The optional Compatibility Profile connects to Web Ninja LLC to help improve FormOrbit updates, compatibility, and support. When enabled by an administrator, it shares the site name and URL, administrator contact email, WordPress and PHP versions, theme details, locale, environment type, and FormOrbit version. It does not share forms, entries, passwords, payment information, or visitor submission data. Administrators can disable it at any time.

FormOrbit service information and privacy details: https://www.webninjallc.com/plugins/formorbit/

Site owners are responsible for disclosing enabled services in their privacy policy and complying with applicable service terms.

== Source code and development ==

The complete, human-readable source for FormOrbit is publicly maintained at:
https://github.com/webifya/FormOrbit

The JavaScript and CSS files distributed in `assets/` are the maintained source files. They are not generated, transpiled, bundled, or minified, and no private source or build step is required to reproduce them. Contributors can edit these files directly. Release packages are created from tagged repository contents by the public GitHub Actions workflow in `.github/workflows/deploy.yml`.
