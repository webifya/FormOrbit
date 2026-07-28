=== Webform ===
Contributors: webifya
Tags: form, form builder, drag and drop, multi-step form, contact form
Requires at least: 6.2
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A visual drag-and-drop, multi-step form builder for WordPress.

== Description ==

Webform provides a native WordPress form-building experience:

* Drag-and-drop field ordering
* Multiple stages with progress indicators
* Text, email, textarea, dropdown, radio, checkbox, number, date, phone, URL, consent, secure file upload, and heading fields
* Conditional field visibility
* Required-field and email validation
* AJAX submission without page reload
* WordPress-native entry storage and admin entry browser
* Email notifications
* Entry filtering, pagination, deletion, and CSV export
* One-click form duplication
* Configurable submit labels and confirmation redirects
* JSON webhook delivery for integrations and automation
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

Webform is created by Mahfuzar Rahman at Web Ninja LLC.

== Installation ==

1. Upload the `Webform` folder to `/wp-content/plugins/`.
2. Activate Webform in the Plugins screen.
3. Open Webform > Add New.
4. Build and save your form.
5. Copy the generated `[webform id="123"]` shortcode into any post or page.

== Changelog ==

= 2.0.0 =
* Added desktop, tablet, and mobile builder preview widths.
* Added read-only Free visibility for reusable Pro themes and per-field styling.
* Added a licensed field-style schema and frontend extension path.
* Improved responsive field layouts and mobile fallbacks.

= 1.9.0 =
* Added a transparent Free/Pro preset selector with all premium preset names visible in read-only mode.
* Added read-only descriptions for Pro typography, color, layout, border, button, and custom CSS controls.
* Added frontend design variables for licensed typography, sizing, colors, width, spacing, and corner controls.
* Added a safe extension pipeline for scoped per-form Pro CSS.

= 1.8.0 =
* Added accurate visual builder previews for every standard field type.
* Added native-looking upload, consent, choice, date, time, rating, slider, CAPTCHA, HTML, hidden, and heading previews.
* Added dedicated calculation, field-group, and e-signature previews when Webform Pro is active.
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
* Added direct checkout links for annual single-site, annual 10-site bundle, and lifetime Webform Pro plans.
* Added read-only Pro capability information inside the form builder.
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
* Added asynchronous JSON webhooks for external integrations.

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

When Google reCAPTCHA is enabled, form pages load Google's reCAPTCHA script. Classic or migrated v2 submissions are sent to Google's SiteVerify API. Google Cloud mode sends the response token, configured site key and action, visitor IP address, and browser user agent to the reCAPTCHA Enterprise assessments API. Site owners are responsible for disclosing this service in their privacy policy and complying with Google's terms, privacy policy, quotas, and billing requirements.
