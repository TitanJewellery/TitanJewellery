<?php
/**
 * Appt Booker
 *
 * File:        appt-booker-titan-v11-7
 * Version:     1.16.7
 * Snippet ID:  61
 * Scope:       Global (front-end + admin)
 *
 * NOTE: this header, the Appt_Booker_Final::VERSION constant and the plugin
 * header in tj-appt-booker.php must always agree. VERSION drives front-end
 * asset cache-busting, the version shown on the admin screen and the schema
 * upgrade gate, so leaving it behind ships stale CSS/JS to returning visitors
 * and misreports the build during support.
 *
 * CHANGELOG
 *   1.0    Baseline Titan build: security (honeypot, IP throttle, optional
 *          Turnstile), per-service interest checklists, ARIA improvements,
 *          customer self-cancellation.
 *   1.0.1  Cancellation page styling fix (init -> template_redirect).
 *   1.2.0  Shared-phone slot logic (only one call at a time across all staff),
 *          cross-staff buffer between calls, per-staff and global daily caps,
 *          "Location" repurposed as communication method with per-method email
 *          wording, dd-mm-yyyy dates throughout, hidden quantity field,
 *          branded HTML emails with plain-text fallback.
 *   1.2.1  Interest-checklist checkbox alignment fix (defensive CSS so the
 *          Shoptimizer theme cannot detach checkboxes from their labels).
 *   1.2.2  Booking form wrapper centred on the page.
 *   1.2.4  Email footer is now editable in Settings (free-text, one line each).
 *   1.2.5  Email footer is centred, and URLs / email addresses in it are
 *          turned into clickable links automatically.
 *   1.3.0  Customer can add an optional message at booking (shown to them and
 *          to staff). Post-conversation notes surfaced in admin emails. Editable
 *          booking privacy notice on the form and in customer emails. Dashboard
 *          widget listing upcoming appointments with who is assigned. Combined
 *          09:00 daily summary email to admin + staff (skipped on empty days).
 *          URLs and email addresses in email bodies are now clickable.
 *   1.4.0  Data retention: two-stage lifecycle (minimise contact details after
 *          N months, delete records after M months), off by default. Per-email
 *          customer-data export (CSV) and delete tools under a new Customer
 *          Data admin tab. Minimal retention activity log. "All times UK time"
 *          shown on the form, in emails and on the booking summary.
 *   1.5.0  All emails now use the one branded HTML template, including the
 *          manual "Email Booking" / "Email This Session" actions which were
 *          previously plain text. Address removed throughout (privacy notice,
 *          retention logic, CSV export, admin emails) - only name, email and
 *          phone are collected.
 *   1.5.1  Removed the square brackets around the site name in email subject
 *          lines (now "Titan Jewellery: Booking made - ...").
 *   1.6.0  Post-conversation notes restructured into four labelled prompts
 *          (topics / concerns / advice / follow-up / general) on the admin
 *          booking screen. New Content Research admin tab: downloads a ZIP
 *          containing a CSV of consultation notes (no personal data) plus an
 *          editable analysis prompt, designed to be pasted into ChatGPT or
 *          Claude to identify content patterns.
 *   1.7.0  New read-only admin booking detail view. Clicking a booking's
 *          reference or customer name in the admin list now opens a clean
 *          read-only page showing every field at a glance (contact details,
 *          pre-call message, interests, structured notes and timestamps)
 *          with action buttons at the top. The previous edit-form route is
 *          unchanged and reachable via Edit Booking from the new view.
 *   1.7.1  Reverted the four-prompt structured-notes UI back to a single
 *          textarea - matches the "paste a summary" workflow better. Defensive
 *          guard for old records where admin_notes was accidentally saved as
 *          a zero-valued number ("0.000000") - now ignored everywhere.
 *   1.16.7 Shared-resource capacity fix (follow-up to the 1.16.6 audit pass).
 *          The 1.16.6 de-duplication stopped a booking being counted twice
 *          against slot capacity, but left a second, opposite defect: a
 *          booking on a DIFFERENT service, location or team member was still
 *          counted as one unit of the target slot's capacity. With Capacity
 *          per Time Slot above 1 that left the slot open, so a second call
 *          could be booked while the shared phone line was already busy.
 *          Capacity now applies only to people joining the SAME call: any
 *          overlapping booking that is not the same session closes the slot
 *          outright when the shared-resource setting is on. This also
 *          restores the cross-staff buffer, which a capacity above 1 had
 *          been absorbing in the same way. build_slots_with_status() mirrors
 *          the rule so the displayed grid cannot advertise a slot the
 *          booking path would refuse. Behaviour with the shared-resource
 *          setting off is unchanged.
 *          Version references in both READMEs and the two bootstrap comments
 *          that still described the engine as v1.15.0 were realigned.
 *   1.16.6 Audit fixes.
 *          - Saving the Settings tab no longer replaces the whole settings
 *            option, which had been silently resetting all 58 Form Style
 *            values to their defaults on every save.
 *          - Shared-resource slot building de-duplicates the two booking
 *            lists by id instead of concatenating them, so a booking is no
 *            longer counted twice against slot capacity (only visible where
 *            Capacity per Time Slot is above 1). The true quantity and the
 *            cross-staff buffer padding are both preserved.
 *          - Availability cut-off logic reads the wall clock via
 *            current_datetime() instead of passing an already-offset
 *            current_time('timestamp') into wp_date(), which double-applied
 *            the GMT offset and ran the cut-off an hour early all through BST.
 *          - VERSION constant realigned with the plugin header.
 *          - table_exists() is memoised and maybe_upgrade_schema() short
 *            -circuits once the stored schema version is current, removing
 *            ~9 SHOW TABLES / SHOW COLUMNS probes from every request.
 *          - Bank holidays are computed (Easter via Meeus/Jones/Butcher plus
 *            UK substitute-day rules) on a rolling window and exposed through
 *            the tj_appt_booker_bank_holidays filter. The old hardcoded table
 *            stopped at 2029 and would have started offering Christmas Day.
 *          - CF-Connecting-IP is only trusted when the peer is a published
 *            Cloudflare edge address, closing an IP-throttle bypass.
 *          - The minimum time-to-submit check now fails closed and the render
 *            timestamp is signed, so omitting or forging the field no longer
 *            skips it.
 *          - The public form refreshes its nonce and signed timestamp on load
 *            and on bfcache restore, so a cached page cannot submit stale
 *            credentials and fail silently.
 *          - Cloudflare Turnstile's script is enqueued by the plugin; enabling
 *            the setting previously rejected every booking until the site
 *            owner hand-added the tag.
 *          - Available-dates lookup is clamped to the real booking window and
 *            cached for a minute, cutting the cost of an unauthenticated
 *            endpoint that ran to hundreds of queries per call.
 *          - Retention sweep deletes in batches; its comments now state that
 *            it is enabled by default and irreversible.
 *          - Smaller: removed a dead plain-text email body, dropped a
 *            duplicated condition in the date-exception test, stopped
 *            double-unslashing the locations JSON payload, validated the
 *            Content Research date range before it reaches a filename, and
 *            made the admin sort whitelist reject non-string input.
 *   1.7.2  Critical fix: missing PHP endif from the v1.7.0 admin view branch
 *          meant Settings, Form Style, Locations, Staff, Services, Customer
 *          Data and Content Research tabs all rendered empty. Now restored.
 *          Admin heading now uses VERSION constant (was hardcoded "V.1.0").
 *          Per-service "What's discussed on the call" description (shown on
 *          the booking form when that service is selected) and per-service
 *          customer-message placeholder (e.g. engraving prompts the customer
 *          to email fonts to engraving&#64;titanjewellery.co.uk in advance).
 *   1.7.3  Service description box now preserves line breaks. Per-service
 *          "Email for quick questions" admin field - when set, the phrase
 *          "email us" in the service description becomes a clickable mailto
 *          link pointing to the right address per service (e.g. engraving
 *          service points to engraving@titanjewellery.co.uk, others to
 *          sales@titanjewellery.co.uk).
 *   1.8.0  Availability rule engine (Pass 1A of 2): customers can only book
 *          on Mon-Fri working days, never same-day, never the next working
 *          day. UK bank holidays auto-blocked, plus the first working day
 *          after any 3+ day non-working break (e.g. Tuesday after Easter
 *          Monday). Cut-off hour rolls "next working day" forward at the
 *          configured time. Booking window is "current calendar week +
 *          configurable number of following weeks", resets every Friday
 *          at the cut-off hour. All settings editable under Settings.
 *   1.9.0  Slot model (Pass 1B): customer-facing time picker replaced with
 *          a visible time-button grid. Booked / past / blocked / day-full
 *          slots are shown greyed out for context rather than hidden, so
 *          the day's shape is immediately readable. Two new per-service
 *          fields: "Slot offer interval" (stride between offers, e.g. 30)
 *          and "Customer-facing duration" (what the customer sees in
 *          emails and the calendar invite, e.g. 15 even though the
 *          internal footprint is 60). Admin booking screen keeps its
 *          dropdown for power-user speed.
 *   1.10.0 Anti-abuse pass: per-email rate limit (max N bookings in M days,
 *          defaults 3/30, both editable). Manual approval mode toggle - when
 *          on, new customer bookings sit as "pending_approval" until you
 *          click the approve link in the admin email; customer gets a
 *          "Booking received" confirmation but no calendar invite yet.
 *          Pending bookings still hold the slot. New status badge in admin.
 *          Email wording updated to mention reschedule alongside cancel.
 *   1.10.1 Reverted the customer-facing time grid back to the original
 *          dropdown. The grid did not register taps reliably on iPhone
 *          Safari. All engine work (30-min interval, 60-min footprint,
 *          customer-facing duration, availability rules, anti-abuse) is
 *          retained. The build_slots_with_status method remains dormant
 *          for any future re-attempt.
 *   1.10.2 Security: approve/reject email links no longer act on the GET
 *          request. The link opens a confirmation page that requires a
 *          deliberate button click (POST) with both the original signed
 *          token and a fresh WP nonce. This stops email-link scanners
 *          (Microsoft Safe Links, Office 365 link rewriting, mail-client
 *          previewers) from accidentally approving bookings when they
 *          pre-fetch URLs for malware scanning. Two taps instead of one
 *          for legitimate admin use; the safety gain is worth it.
 *   1.10.3 Booking form no longer auto-selects the first service / location
 *          / team member on load when no default is configured AND there is
 *          more than one to choose from. The empty placeholder shows and
 *          the customer makes a deliberate choice (also means per-service
 *          descriptions render correctly when they pick one). When there is
 *          only one option, it is still auto-selected as before.
 *   1.10.4 Privacy notice extended to mention call recording (consent-based).
 *          Privacy notice now renders multi-paragraph on the form and in
 *          emails. Form inputs/selects given proper line-height and min-height
 *          so descender characters (e.g. "y" in "Phone (UK only)") are no
 *          longer clipped at the bottom. The "email us" link in service
 *          descriptions is now visibly underlined.
 *   1.10.5 Cumulative layout shift on the booking form fixed: the service
 *          description box is now reserved from page load with a placeholder
 *          message ("Select what you would like to discuss above..."), so
 *          the contact-method field never gets bumped to a new row when a
 *          service is picked. Service field and description box wrapped in
 *          a single grid cell so the description always sits beneath the
 *          service in the same column. Time-status hint promoted from
 *          screen-reader-only to visible so customers see "Checking
 *          availability..." during AJAX. Default form_intro updated to
 *          "Choose a service, date and available time." (no team member).
 *   1.10.6 Service description box now spans the full width of the form on
 *          desktop (sits on its own row between the service/contact row
 *          and the date/time row), giving longer descriptions room to
 *          breathe. Text inside is capped at 680px so it doesn't sprawl
 *          on very wide monitors. On mobile (single-column layout) it
 *          continues to flow naturally beneath the service dropdown.
 *   1.10.7 Two follow-ups: the description box is now genuinely full-width
 *          (the previous 680px cap on the box was too aggressive and made
 *          it look short next to the other form fields). And the
 *          appearance:auto rule on selects added in 1.10.4 has been
 *          removed - it caused some themes (Shoptimizer) to render two
 *          dropdown arrows because the theme already supplies one via a
 *          background image. Native arrow now hidden as the theme intends.
 *   1.10.8 Fixed "No locations available" appearing when the customer
 *          changed service for the second time. Cause: the on-change
 *          handler was preserving the hidden .rgl-staff value across
 *          service changes, so fetchLocations sent service_id=NEW plus a
 *          stale staff_id that wasn't valid for the new service. Server
 *          correctly returned an empty list. Now the live value of staff
 *          and location is cleared on every service change regardless of
 *          whether the field is a <select> or hidden <input>; the
 *          data-default-value attribute is preserved so applyPreferredValue
 *          can still pick up the right default on rebuild.
 *   1.10.9 Logged-in admins (manage_options) now bypass all customer-facing
 *          anti-abuse on the public form: honeypot, minimum submit time,
 *          IP throttle, Turnstile, and the per-email rate limit. So admins
 *          can use the front-end form to take real bookings by phone, and
 *          repeated testing no longer trips the IP throttle. Customers
 *          (anyone not logged in as admin) are still fully protected.
 *   1.10.10 Admin bookings list simplified for consultation bookings.
 *          Added a Status column with the same coloured badge used on
 *          the detail page (Confirmed / Pending approval / Cancelled /
 *          Held). Sortable by status.
 *   1.10.11 Defensive double-submit guard on the public booking form.
 *          Sets a form-level rgl-submitting flag on first submit so any
 *          second submit attempt (Enter key while in flight, double-tap
 *          on submit, race conditions on slow connections) is ignored.
 *          On successful save, the submit button stays disabled for an
 *          extra 2 seconds even after the success message shows so an
 *          over-eager second tap can't fire a duplicate booking against
 *          the just-reset form state. Targets the symptom where the
 *          success message appeared to flicker behind a server-side
 *          "that time is not available" error caused by the second
 *          submission hitting the slot now that the first booking had
 *          just claimed it.
 *   1.10.12 Three things: (a) customer emails restored to a friendlier
 *          tone - "Your booking is confirmed" headline, intro line
 *          including who will be in touch, what they will discuss, when,
 *          and how, plus a warmer footer. booking_approved (manual
 *          approval flow) now produces the same friendly content as a
 *          regular booking_made. (b) Mark as Spam action added to the
 *          admin booking actions dropdown - cancels the booking with a
 *          tailored "this is for purchase questions, not a sales channel"
 *          customer email, and adds the customer's email to a blocked
 *          list maintained in Settings. Blocked emails are silently
 *          rejected on future submission attempts. Admins bypass the
 *          block. (c) Admin actions dropdown no longer hijacks the
 *          page scroll on mouse-wheel inside it (overscroll-behavior:
 *          contain, plus a max-width and max-height so it can't
 *          visually stretch).
 *   1.10.13 Bug fix: Mark as Spam did not actually cancel the booking
 *          because the UPDATE query referenced an updated_at column that
 *          doesn't exist in the bookings table schema. On Kinsta's
 *          strict MySQL the whole statement failed silently. The
 *          approve/reject email-link handlers from 1.10.2 had the same
 *          dormant bug. Removed updated_at from all three update sites.
 *   1.10.14 Bug fix follow-up: the Mark as Spam customer email was being
 *          silently dropped because notification_enabled() requires an
 *          admin-toggleable setting key, and no setting was registered
 *          for booking_marked_spam. The spam-cancel email is the whole
 *          point of the action and should not be toggle-able, so it is
 *          now always sent regardless of notification settings.
 *   1.11.0 No-payment security build: removed PayPal and all payment/deposit
 *          routes, settings, fields, emails and customer-supplied financial
 *          values. Added neutral signed booking-access tokens, CSV formula
 *          injection protection, an atomic MySQL booking-write lock, and
 *          replaced runtime-generated dynamic-tag classes with one configurable class.
 *   1.11.1 Review fixes: restored Elementor dynamic image rendering using a
 *          shared base class plus 18 fixed concrete tag classes (no eval),
 *          extended the booking-write lock to admin add/edit/duplicate paths,
 *          preserved pending approval during ordinary edits and routed explicit
 *          approval through the correct notification, fixed dbDelta primary-key
 *          formatting, unified blocked/rate-limit messages, and cached schema
 *          column checks for the duration of each request.
 *   1.12.0 Appointment communications: configurable one-time customer reminder
 *          emails (24 hours before by default) with a prominent secure cancellation
 *          button, plus dedicated consultant-reassignment emails to the customer,
 *          replacement consultant and previous consultant. Reminders are tracked in
 *          the booking table and reset when the appointment date or time changes.
 *   1.12.1 Required contact details: name, email address and phone number are
 *          now always displayed and required on public and admin booking forms.
 *          Server-side validation also rejects direct or modified submissions that
 *          omit any of the three fields. The old option to hide the phone field has
 *          been retired while its stored setting is retained for compatibility.
 *   1.12.2 Defensive reminder-schema guards: approval-from-email and the admin
 *          quick status action now reset reminder_sent_at only when that column
 *          exists. This prevents the entire status update from failing if a host
 *          ever rejects the additive schema upgrade.
 *   1.13.0 Customer appointment management: secure self-rescheduling keeps the
 *          existing booking record and reference while changing only the date
 *          and time under the booking lock. Added Completed and No-show outcomes,
 *          reminder status visibility, and a manual Send reminder action. Customer,
 *          staff and admin appointment-change notifications are configurable.
 *   1.13.1 Email action layout: customer email buttons now appear in a
 *          consistent single-column layout. Booking summary and calendar actions
 *          come first, followed by Change appointment and Cancel booking last.
 *   1.13.2 Booking summary action layout: customer-facing summary buttons use
 *          the same order as emails and stack cleanly on mobile.
 *   1.13.3 Unified public action buttons across booking summary, appointment
 *          changes, cancellation and approval pages. Sizing, spacing and mobile
 *          stacking are now consistent, with destructive actions last.
 *   1.13.4 Responsive email detail layout: labels stack above values on narrow
 *          screens, with reduced mobile padding for more usable width.
 *   1.13.5 Reliable email details: booking detail rows now use a true
 *          single-column structure in every email client, avoiding squeezed
 *          two-column layouts when mobile media queries are ignored.
 *   1.13.6 Safari redirect transition: a full-screen confirmation cover hides
 *          transient theme and date-picker rendering during the redirect.
 *   1.13.7 Redirect transition follow-up: wording changed to "Just confirming
 *          your booking" and the destination summary page keeps a critical
 *          white cover until the theme and page CSS have fully loaded.
 *   1.13.8 Outcome and reschedule safeguards: Completed and No-show can only be
 *          applied once the appointment has started. Reschedule tokens now bind
 *          to the current date and time, so a successful change invalidates the
 *          previously issued change link.
 *   1.14.0 No-show safeguards: recent no-shows can place a new public booking into
 *          manual approval rather than rejecting it. Admins see the recent count
 *          and can apply or remove a time-limited future-booking block. Manual
 *          blocks store only a salted email hash, expire automatically, appear in
 *          customer-data tools, and are removed on a deletion request. The public
 *          privacy notice explains the attendance-history review and human decision.
 *   1.14.1 Retention activation: automatic retention is enabled for this existing
 *          pre-launch installation as a one-time upgrade, using the existing
 *          6-month contact minimisation and 30-month record deletion periods.
 *   1.14.2 Staff help: replaced the legacy Instructions page with an extensive
 *          Titan Jewellery staff guide covering daily workflows, reminders,
 *          rescheduling, consultant changes, outcomes, no-show safeguards,
 *          retention, troubleshooting and advanced setup. Added a privacy-aware
 *          AI support prompt with one-click copy of the prompt plus current guide.
 *          Plugin credit updated to Jason Beer of Titan Jewellery.
 *   1.14.3 Admin menu count: the Appt-Booker menu now shows a WordPress-style
 *          red count bubble for future Confirmed and Pending approval bookings.
 *          Past, cancelled, completed and no-show records are excluded.
 *   1.14.4 Upcoming count alignment: the booking summary card now uses the same
 *          future Confirmed/Pending approval count as the admin menu badge and
 *          is labelled Upcoming bookings, so the two figures always agree.
 *   1.15.0 Shared contact availability and customer contact guidance: each
 *          service/team member now has one weekly availability schedule shared
 *          across every allowed contact method. Existing method-specific hours
 *          are migrated once. Settings can show method-specific customer notes,
 *          including withheld caller ID or the WhatsApp number used. The long
 *          no-show paragraph is no longer appended to public privacy wording.
 *
 * ---------------------------------------------------------------------------
 * SHORTCODES
 * ---------------------------------------------------------------------------
 *
 * [appt_booker]
 *   Renders the customer-facing booking form. Without attributes it shows all
 *   active services, locations, and team members. Attributes let you preselect
 *   or lock specific choices for context-aware placement.
 *
 *   Attributes:
 *     service / service_id      Preselect a specific service.
 *     services                  Restrict to a comma-separated list of services.
 *     location / location_id    Preselect a location.
 *     locations                 Restrict to a comma-separated list of locations.
 *     staff / staff_id          Preselect a team member.
 *     team_member / team_member_id   Aliases for staff / staff_id.
 *     lock_service              "1" hides the service dropdown and locks it.
 *     lock_location             "1" hides the location dropdown and locks it.
 *     lock_staff                "1" hides the staff dropdown and locks it.
 *     lock                      "1" smart-locks any preselected field.
 *                               "all" locks every field.
 *     dynamic                   "1" auto-fills from the current Elementor
 *                               single template (team/service/location CPT).
 *
 *   Examples:
 *     [appt_booker]
 *     [appt_booker service="metal-comparison" lock_service="1"]
 *     [appt_booker service="bespoke-enquiry" lock_service="1"]
 *     [appt_booker dynamic="1" lock="1"]
 *
 *   Per-service interest checkboxes (metals to compare, engraving styles, etc)
 *   appear automatically when enabled for the chosen service in admin. No
 *   shortcode attribute needed.
 *
 *
 * [appt_tax_field]
 *   Outputs the value of a custom field attached to an Appt Booker taxonomy
 *   (services, locations, team members). Useful inside Elementor templates.
 *
 *   Attributes:
 *     taxonomy    Taxonomy slug (auto-detected if omitted).
 *     field       Custom field key (required).
 *     term_id     Specific term ID (optional).
 *     post_id     Specific post ID to resolve term from (optional).
 *     before      HTML to prepend when value is non-empty.
 *     after       HTML to append when value is non-empty.
 *     label       "1" prefixes the output with the field label.
 *
 *   Example:
 *     [appt_tax_field field="metal_type" before="<em>" after="</em>"]
 *
 *
 * [appt_term_field]
 *   Alias of [appt_tax_field]. Identical attributes; named for term/archive
 *   contexts where it reads more naturally.
 *
 *
 * [appt_loopgrid_debug]
 *   Admin diagnostic. Shows Elementor Loop Grid integration debug info.
 *   Visible only to logged-in administrators. No attributes.
 *
 *
 * [appt_rebuild_loopgrid_indexes]
 *   Admin maintenance. Rebuilds Elementor Loop Grid index tables.
 *   Visible and actionable only to logged-in administrators. No attributes.
 *
 *
 * ---------------------------------------------------------------------------
 * NOT SHORTCODES
 * ---------------------------------------------------------------------------
 *
 * Some features are surfaced via URLs rather than shortcodes:
 *   - Cancellation page  - reached from the signed link in confirmation emails.
 *   - Booking summary    - reached via the post-booking URL.
 *   - Admin              - WordPress admin sidebar, "Appt-Booker".
 *
 *
 * ---------------------------------------------------------------------------
 * CONFIGURATION
 * ---------------------------------------------------------------------------
 *
 * All behaviour-level settings live under WP Admin -> Appt-Booker:
 *   - Services       Duration, hours, interest checklist (20 options each).
 *   - Locations      e.g. Phone, WhatsApp.
 *   - Team Members   e.g. Jason.
 *   - Settings       Labels, anti-abuse (honeypot, IP throttle, optional
 *                    Cloudflare Turnstile), self-cancellation, email routing.
 *   - Bookings       List, filter, edit, cancel, duplicate, export.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Appt_Booker_Final' ) ) {

	class Appt_Booker_Final {

		const VERSION         = '1.16.7';
		const OPTION_DB_VERSION = 'rgl_booking_final_db_version';
		const OPTION_STAFF      = 'rgl_booking_final_staff';
		const OPTION_SERVICES   = 'rgl_booking_final_services';
		const OPTION_LOCATIONS  = 'rgl_booking_final_locations';
		const OPTION_SETTINGS   = 'rgl_booking_final_settings';
		const OPTION_TAXONOMY_FIELDS = 'rgl_booking_final_taxonomy_fields';
		const OPTION_ELEMENTOR_SYNC_HASH = 'rgl_booking_final_elementor_sync_hash';
		const OPTION_NO_PAYMENT_MIGRATED = 'rgl_booking_final_no_payment_migrated';
		const OPTION_RETENTION_ENABLED_MIGRATED = 'rgl_booking_final_retention_enabled_migrated';
		const OPTION_SHARED_CONTACT_AVAILABILITY_MIGRATED = 'rgl_booking_final_shared_contact_availability_migrated';
		const OPTION_BOOKING_BLOCKS = 'rgl_booking_final_booking_blocks';
		const OPTION_RESTRICTED_DATES = 'rgl_booking_final_licence_restricted_dates';
		const TRANSIENT_ELEMENTOR_SYNC_LOCK = 'rgl_booking_final_elementor_sync_lock';
		const STATUS_CONFIRMED  = 'confirmed';
		const STATUS_HELD       = 'held';
		const STATUS_CANCELLED  = 'cancelled';
		const STATUS_PENDING_APPROVAL = 'pending_approval';
		const STATUS_COMPLETED  = 'completed';
		const STATUS_NO_SHOW    = 'no_show';
		const CPT_LOCATION      = 'appt_booker_location';
		const CPT_SERVICE       = 'appt_booker_service';
		const CPT_TEAM          = 'appt_booker_team';
		const TAX_REL_LOCATION  = 'appt_booker_rel_location';
		const TAX_REL_SERVICE   = 'appt_booker_rel_service';
		const TAX_REL_TEAM      = 'appt_booker_rel_team';
		const EXTRA_IMAGE_SLOTS = 5;

		private $table = '';
		private $column_exists_cache = array();
		private $table_exists_cache = null;

		public function __construct() {
			global $wpdb;
			$this->table = $wpdb->prefix . 'rgl_booking_final';
			$this->maybe_upgrade_schema();
			$this->remove_legacy_payment_configuration();
			$this->enable_retention_for_existing_installation();
			$this->migrate_shared_contact_method_availability();

			add_action( 'init', array( $this, 'register_elementor_content_types' ), 0 );
			add_action( 'init', array( $this, 'maybe_sync_elementor_content_records' ), 20 );
			add_action( 'elementor/dynamic_tags/register', array( $this, 'register_elementor_dynamic_image_tags' ) );
			add_action( 'elementor/dynamic_tags/register_tags', array( $this, 'register_elementor_dynamic_image_tags' ) );

			// Elementor Single Post Template Loop Grid filters.
			// These Query IDs read pre-saved related post IDs only.
			// No relationship scanning happens while Elementor renders the template.
			add_action( 'elementor/query/appt_related_services', array( $this, 'elementor_query_related_services' ), 999 );
			add_action( 'elementor/query/appt_related_locations', array( $this, 'elementor_query_related_locations' ), 999 );
			add_action( 'elementor/query/appt_related_team', array( $this, 'elementor_query_related_team' ), 999 );

			// Elementor Pro has used different custom-query hooks across Posts, Portfolio, Loop Grid and Carousel.
			// Register all known hook variants so the same Query ID works regardless of which Elementor query runner is used.
			add_action( 'elementor_pro/posts/query/appt_related_services', array( $this, 'elementor_query_related_services' ), 999 );
			add_action( 'elementor_pro/posts/query/appt_related_locations', array( $this, 'elementor_query_related_locations' ), 999 );
			add_action( 'elementor_pro/posts/query/appt_related_team', array( $this, 'elementor_query_related_team' ), 999 );
			add_action( 'elementor_pro/query/appt_related_services', array( $this, 'elementor_query_related_services' ), 999 );
			add_action( 'elementor_pro/query/appt_related_locations', array( $this, 'elementor_query_related_locations' ), 999 );
			add_action( 'elementor_pro/query/appt_related_team', array( $this, 'elementor_query_related_team' ), 999 );

			// Final safe guard for Elementor Loop Grid queries.
			// When Elementor keeps extra hidden query constraints, return the exact saved related posts directly.
			add_filter( 'posts_pre_query', array( $this, 'filter_loopgrid_posts_pre_query' ), 20, 2 );

			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_head', array( $this, 'admin_menu_booking_badge_styles' ) );
			add_action( 'admin_init', array( $this, 'handle_admin_postbacks' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
			add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );
			add_action( 'rgl_booking_daily_summary', array( $this, 'send_daily_summary_email' ) );
			add_action( 'init', array( $this, 'maybe_schedule_daily_summary' ) );
			add_action( 'rgl_booking_reminder_sweep', array( $this, 'send_due_appointment_reminders' ) );
			add_action( 'init', array( $this, 'maybe_schedule_appointment_reminders' ) );
			add_action( 'rgl_booking_retention_sweep', array( $this, 'run_retention_sweep' ) );
			add_action( 'init', array( $this, 'maybe_schedule_retention_sweep' ) );
			add_action( 'rgl_booking_send_notification_event', array( $this, 'send_queued_booking_notification' ), 10, 3 );
			add_action( 'rgl_booking_send_consultant_change_event', array( $this, 'send_queued_consultant_change_notification' ), 10, 6 );
			add_action( 'wp_mail_failed', array( $this, 'log_wp_mail_failed' ), 10, 1 );
			add_action( 'template_redirect', array( $this, 'render_booking_summary_page' ) );
			add_action( 'template_redirect', array( $this, 'render_booking_ics_file' ) );
			add_action( 'template_redirect', array( $this, 'handle_cancellation_request' ) );
			add_action( 'template_redirect', array( $this, 'handle_reschedule_request' ) );
			add_action( 'template_redirect', array( $this, 'handle_approval_request' ) );

			add_shortcode( 'appt_booker', array( $this, 'render_shortcode' ) );
			add_shortcode( 'appt_tax_field', array( $this, 'shortcode_taxonomy_field' ) );
			add_shortcode( 'appt_term_field', array( $this, 'shortcode_taxonomy_field' ) );
			add_shortcode( 'appt_loopgrid_debug', array( $this, 'shortcode_loopgrid_debug' ) );
			add_shortcode( 'appt_rebuild_loopgrid_indexes', array( $this, 'shortcode_rebuild_loopgrid_indexes' ) );
			add_filter( 'get_post_metadata', array( $this, 'filter_taxonomy_custom_field_post_meta_display_value' ), 10, 5 );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

			add_action( 'wp_ajax_rgl_booking_submit', array( $this, 'ajax_submit' ) );
			add_action( 'wp_ajax_nopriv_rgl_booking_submit', array( $this, 'ajax_submit' ) );
			add_action( 'wp_ajax_rgl_booking_get_staff', array( $this, 'ajax_get_staff' ) );
			add_action( 'wp_ajax_nopriv_rgl_booking_get_staff', array( $this, 'ajax_get_staff' ) );
			add_action( 'wp_ajax_rgl_booking_get_services', array( $this, 'ajax_get_services' ) );
			add_action( 'wp_ajax_nopriv_rgl_booking_get_services', array( $this, 'ajax_get_services' ) );
			add_action( 'wp_ajax_rgl_booking_get_locations', array( $this, 'ajax_get_locations' ) );
			add_action( 'wp_ajax_nopriv_rgl_booking_get_locations', array( $this, 'ajax_get_locations' ) );
			add_action( 'wp_ajax_rgl_booking_get_slots', array( $this, 'ajax_get_slots' ) );
			add_action( 'wp_ajax_nopriv_rgl_booking_get_slots', array( $this, 'ajax_get_slots' ) );
			add_action( 'wp_ajax_rgl_booking_get_available_dates', array( $this, 'ajax_get_available_dates' ) );
			add_action( 'wp_ajax_nopriv_rgl_booking_get_available_dates', array( $this, 'ajax_get_available_dates' ) );
			add_action( 'wp_ajax_rgl_booking_get_interests', array( $this, 'ajax_get_interests' ) );
			add_action( 'wp_ajax_nopriv_rgl_booking_get_interests', array( $this, 'ajax_get_interests' ) );
			add_action( 'wp_ajax_rgl_booking_reschedule_slots', array( $this, 'ajax_reschedule_slots' ) );
			add_action( 'wp_ajax_nopriv_rgl_booking_reschedule_slots', array( $this, 'ajax_reschedule_slots' ) );
			add_action( 'wp_ajax_rgl_booking_refresh_token', array( $this, 'ajax_refresh_form_token' ) );
			add_action( 'wp_ajax_nopriv_rgl_booking_refresh_token', array( $this, 'ajax_refresh_form_token' ) );
			$this->register_taxonomy_custom_field_hooks();
		}

		private function get_current_appt_single_post_id() {
			$post_id = absint( get_queried_object_id() );

			if ( ! $post_id ) {
				global $post;
				$post_id = ( $post instanceof WP_Post ) ? absint( $post->ID ) : 0;
			}

			if ( ! $post_id ) {
				return 0;
			}

			$post_type = get_post_type( $post_id );
			if ( ! in_array( $post_type, array( self::CPT_LOCATION, self::CPT_SERVICE, self::CPT_TEAM ), true ) ) {
				return 0;
			}

			return $post_id;
		}

		private function related_post_ids_meta_key_for_target( $target_post_type ) {
			if ( self::CPT_SERVICE === $target_post_type ) {
				return '_appt_related_service_post_ids';
			}

			if ( self::CPT_LOCATION === $target_post_type ) {
				return '_appt_related_location_post_ids';
			}

			if ( self::CPT_TEAM === $target_post_type ) {
				return '_appt_related_team_post_ids';
			}

			return '';
		}

		private function related_item_ids_meta_key_for_target( $target_post_type ) {
			if ( self::CPT_SERVICE === $target_post_type ) {
				return '_appt_booker_service_ids';
			}

			if ( self::CPT_LOCATION === $target_post_type ) {
				return '_appt_booker_location_ids';
			}

			if ( self::CPT_TEAM === $target_post_type ) {
				return '_appt_booker_team_ids';
			}

			return '';
		}

		private function normalise_post_id_list( $ids ) {
			$ids = array_map( 'absint', (array) $ids );
			$ids = array_values( array_unique( array_filter( $ids ) ) );

			return $ids;
		}

		private function normalise_item_id_list_for_loopgrid( $ids ) {
			$out = array();
			foreach ( (array) $ids as $id ) {
				$id = sanitize_key( $id );
				if ( '' !== $id ) {
					$out[] = $id;
				}
			}
			return array_values( array_unique( $out ) );
		}

		private function get_elementor_record_ids_by_item_ids_for_loopgrid( $target_post_type, $item_ids ) {
			$item_ids = $this->normalise_item_id_list_for_loopgrid( $item_ids );
			if ( empty( $item_ids ) ) {
				return array();
			}

			$posts = get_posts(
				array(
					'post_type'              => $target_post_type,
					'post_status'            => 'publish',
					'posts_per_page'         => -1,
					'fields'                 => 'ids',
					'orderby'                => 'title',
					'order'                  => 'ASC',
					'no_found_rows'          => true,
					'suppress_filters'       => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'meta_query'             => array(
						'relation' => 'AND',
						array(
							'key'   => '_appt_booker_source',
							'value' => 'appt_booker',
						),
						array(
							'key'     => '_appt_booker_item_id',
							'value'   => $item_ids,
							'compare' => 'IN',
						),
					),
				)
			);

			return $this->normalise_post_id_list( $posts );
		}

		private function get_elementor_record_ids_that_include_current_item_for_loopgrid( $target_post_type, $relationship_meta_key, $current_item_id ) {
			$current_item_id = sanitize_key( $current_item_id );
			if ( '' === $current_item_id || '' === $relationship_meta_key ) {
				return array();
			}

			$posts = get_posts(
				array(
					'post_type'              => $target_post_type,
					'post_status'            => 'publish',
					'posts_per_page'         => -1,
					'fields'                 => 'ids',
					'orderby'                => 'title',
					'order'                  => 'ASC',
					'no_found_rows'          => true,
					'suppress_filters'       => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'meta_query'             => array(
						'relation' => 'AND',
						array(
							'key'   => '_appt_booker_source',
							'value' => 'appt_booker',
						),
						array(
							'key'     => $relationship_meta_key,
							'value'   => '"' . $current_item_id . '"',
							'compare' => 'LIKE',
						),
					),
				)
			);

			return $this->normalise_post_id_list( $posts );
		}

		private function get_related_elementor_loop_post_ids( $current_post_id, $target_post_type ) {
			$current_post_id   = absint( $current_post_id );
			$current_post_type = $current_post_id ? get_post_type( $current_post_id ) : '';
			$current_item_id   = $current_post_id ? sanitize_key( get_post_meta( $current_post_id, '_appt_booker_item_id', true ) ) : '';

			if ( ! $current_post_id || '' === $current_item_id ) {
				return array();
			}

			/* First try the prebuilt post-id index. */
			$indexed_key = $this->related_post_ids_meta_key_for_target( $target_post_type );
			$indexed_ids = '' !== $indexed_key ? $this->normalise_post_id_list( get_post_meta( $current_post_id, $indexed_key, true ) ) : array();
			if ( ! empty( $indexed_ids ) ) {
				return $indexed_ids;
			}

			/* Fallback: use the already-synced lightweight item-id relationship meta. */
			if ( self::CPT_SERVICE === $current_post_type ) {
				if ( self::CPT_LOCATION === $target_post_type || self::CPT_TEAM === $target_post_type ) {
					$item_key = $this->related_item_ids_meta_key_for_target( $target_post_type );
					return $this->get_elementor_record_ids_by_item_ids_for_loopgrid( $target_post_type, get_post_meta( $current_post_id, $item_key, true ) );
				}
			}

			if ( self::CPT_TEAM === $current_post_type ) {
				if ( self::CPT_LOCATION === $target_post_type ) {
					return $this->get_elementor_record_ids_by_item_ids_for_loopgrid( $target_post_type, get_post_meta( $current_post_id, '_appt_booker_location_ids', true ) );
				}
				if ( self::CPT_SERVICE === $target_post_type ) {
					return $this->get_elementor_record_ids_that_include_current_item_for_loopgrid( self::CPT_SERVICE, '_appt_booker_team_ids', $current_item_id );
				}
			}

			if ( self::CPT_LOCATION === $current_post_type ) {
				if ( self::CPT_SERVICE === $target_post_type ) {
					return $this->get_elementor_record_ids_that_include_current_item_for_loopgrid( self::CPT_SERVICE, '_appt_booker_location_ids', $current_item_id );
				}
				if ( self::CPT_TEAM === $target_post_type ) {
					return $this->get_elementor_record_ids_that_include_current_item_for_loopgrid( self::CPT_TEAM, '_appt_booker_location_ids', $current_item_id );
				}
			}

			return array();
		}


		private function get_loop_query_post_type( $query ) {
			if ( ! ( $query instanceof WP_Query ) ) {
				return '';
			}

			$post_type = $query->get( 'post_type' );
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}

			return sanitize_key( (string) $post_type );
		}

		private function return_loopgrid_posts_directly( $ids, $target_post_type, $debug_reason, $query ) {
			$ids = $this->normalise_post_id_list( $ids );
			$target_post_type = sanitize_key( $target_post_type );

			$out = array();
			foreach ( $ids as $id ) {
				$post_obj = get_post( $id );
				if ( $post_obj instanceof WP_Post && $target_post_type === $post_obj->post_type && 'publish' === $post_obj->post_status ) {
					$out[] = $post_obj;
				}
			}

			if ( $query instanceof WP_Query ) {
				$query->found_posts   = count( $out );
				$query->post_count    = count( $out );
				$query->max_num_pages = 1;
			}

			$debug = get_option( 'appt_booker_loopgrid_query_debug', array() );
			if ( ! is_array( $debug ) ) {
				$debug = array();
			}
			$debug[] = array(
				'time'             => current_time( 'mysql' ),
				'posts_pre_query'  => $debug_reason,
				'target_type'      => $target_post_type,
				'forced_ids'       => $ids,
				'returned_count'   => count( $out ),
			);
			$debug = array_slice( $debug, -10 );
			update_option( 'appt_booker_loopgrid_query_debug', $debug, false );

			return $out;
		}

		public function filter_loopgrid_posts_pre_query( $posts, $query ) {
			if ( is_admin() || ! ( $query instanceof WP_Query ) ) {
				return $posts;
			}

			$valid_types = array( self::CPT_SERVICE, self::CPT_LOCATION, self::CPT_TEAM );

			$ids = $this->normalise_post_id_list( $query->get( 'appt_booker_force_post_ids' ) );
			$target_post_type = sanitize_key( $query->get( 'appt_booker_force_post_type' ) );

			if ( ! empty( $ids ) && in_array( $target_post_type, $valid_types, true ) ) {
				return $this->return_loopgrid_posts_directly( $ids, $target_post_type, 'returned_posts_directly_from_query_id', $query );
			}

			/*
			 * Fallback for Elementor Loop Grid cases where the Query ID hook is missed.
			 * This only runs on Appt Booker single templates and only for Appt Booker content post types.
			 * It keeps the working Query ID method intact, but prevents a related Loop Grid from falling back to "show all".
			 */
			$current_post_id = $this->get_current_appt_single_post_id();
			$target_post_type = $this->get_loop_query_post_type( $query );

			if ( ! $current_post_id || ! in_array( $target_post_type, $valid_types, true ) ) {
				return $posts;
			}

			if ( get_post_type( $current_post_id ) === $target_post_type ) {
				return $posts;
			}

			$ids = $this->get_related_elementor_loop_post_ids( $current_post_id, $target_post_type );
			$ids = $this->normalise_post_id_list( $ids );

			if ( empty( $ids ) ) {
				return $posts;
			}

			return $this->return_loopgrid_posts_directly( $ids, $target_post_type, 'returned_posts_directly_from_single_template_fallback', $query );
		}


		private function apply_related_elementor_loop_query( $query, $target_post_type ) {
			static $running = false;

			$debug = get_option( 'appt_booker_loopgrid_query_debug', array() );
			if ( ! is_array( $debug ) ) {
				$debug = array();
			}
			$debug[] = array(
				'time'        => current_time( 'mysql' ),
				'target_type' => $target_post_type,
				'queried_id'  => absint( get_queried_object_id() ),
				'global_id'   => ( isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post ) ? absint( $GLOBALS['post']->ID ) : 0,
			);
			$debug = array_slice( $debug, -10 );
			update_option( 'appt_booker_loopgrid_query_debug', $debug, false );

			if ( $running ) {
				$query->set( 'post__in', array( 0 ) );
				return;
			}

			$running         = true;
			$current_post_id = $this->get_current_appt_single_post_id();
			$related_ids     = $this->get_related_elementor_loop_post_ids( $current_post_id, $target_post_type );
			$running         = false;

			$related_ids = $this->normalise_post_id_list( $related_ids );

			// Store the IDs in private query vars so posts_pre_query can return the exact posts if Elementor adds hidden constraints.
			$query->set( 'appt_booker_force_post_ids', $related_ids );
			$query->set( 'appt_booker_force_post_type', $target_post_type );

			/*
			 * Force a clean WP_Query for Elementor Loop Grid.
			 * Elementor can carry Include/Exclude, current object, taxonomy, search, or meta vars
			 * into the query. Those extra vars can override post__in and make valid IDs display nothing.
			 */
			$query->set( 'post_type', $target_post_type );
			$query->set( 'post_status', 'publish' );
			$query->set( 'ignore_sticky_posts', true );
			$query->set( 'no_found_rows', true );
			$query->set( 'suppress_filters', false );

			/* Clear common query vars that can silently conflict with post__in. */
			$query->set( 'p', 0 );
			$query->set( 'page_id', 0 );
			$query->set( 'name', '' );
			$query->set( 'pagename', '' );
			$query->set( 's', '' );
			$query->set( 'author', '' );
			$query->set( 'author_name', '' );
			$query->set( 'post_parent', '' );
			$query->set( 'post__not_in', array() );
			$query->set( 'tax_query', array() );
			$query->set( 'meta_query', array() );
			$query->set( 'date_query', array() );
			$query->set( 'paged', 1 );
			$query->set( 'page', 1 );
			$query->set( 'offset', 0 );

			if ( empty( $related_ids ) ) {
				$query->set( 'post__in', array( 0 ) );
				$query->set( 'posts_per_page', 1 );
				return;
			}

			$query->set( 'post__in', $related_ids );
			$query->set( 'orderby', 'post__in' );
			$query->set( 'order', 'ASC' );
			$query->set( 'posts_per_page', count( $related_ids ) );
		}

		public function elementor_query_related_services( $query ) {
			$this->apply_related_elementor_loop_query( $query, self::CPT_SERVICE );
		}

		public function elementor_query_related_locations( $query ) {
			$this->apply_related_elementor_loop_query( $query, self::CPT_LOCATION );
		}

		public function elementor_query_related_team( $query ) {
			$this->apply_related_elementor_loop_query( $query, self::CPT_TEAM );
		}


		public function shortcode_rebuild_loopgrid_indexes() {
			if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
				return '';
			}

			if ( ! $this->elementor_content_types_are_registered() ) {
				return '<pre>Appt Booker Loop Grid: content post types are not registered yet.</pre>';
			}

			$this->sync_elementor_content_records( $this->get_elementor_content_source_hash() );
			$this->rebuild_elementor_related_post_id_indexes();

			return '<pre>Appt Booker Loop Grid indexes rebuilt. Remove this shortcode after testing.</pre>';
		}

		public function shortcode_loopgrid_debug() {
			if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
				return '';
			}

			$current_post_id = $this->get_current_appt_single_post_id();
			$queried_id      = absint( get_queried_object_id() );
			global $post;
			$global_post_id  = ( $post instanceof WP_Post ) ? absint( $post->ID ) : 0;

			$rows = array();
			$rows['queried_object_id'] = $queried_id;
			$rows['queried_post_type'] = $queried_id ? get_post_type( $queried_id ) : '';
			$rows['global_post_id'] = $global_post_id;
			$rows['global_post_type'] = $global_post_id ? get_post_type( $global_post_id ) : '';
			$rows['detected_appt_single_post_id'] = $current_post_id;
			$rows['detected_appt_single_post_type'] = $current_post_id ? get_post_type( $current_post_id ) : '';
			$rows['_appt_booker_item_id'] = $current_post_id ? get_post_meta( $current_post_id, '_appt_booker_item_id', true ) : '';
			$rows['_appt_booker_location_ids'] = $current_post_id ? get_post_meta( $current_post_id, '_appt_booker_location_ids', true ) : array();
			$rows['_appt_booker_service_ids'] = $current_post_id ? get_post_meta( $current_post_id, '_appt_booker_service_ids', true ) : array();
			$rows['_appt_booker_team_ids'] = $current_post_id ? get_post_meta( $current_post_id, '_appt_booker_team_ids', true ) : array();
			$rows['_appt_related_location_post_ids'] = $current_post_id ? get_post_meta( $current_post_id, '_appt_related_location_post_ids', true ) : array();
			$rows['_appt_related_service_post_ids'] = $current_post_id ? get_post_meta( $current_post_id, '_appt_related_service_post_ids', true ) : array();
			$rows['_appt_related_team_post_ids'] = $current_post_id ? get_post_meta( $current_post_id, '_appt_related_team_post_ids', true ) : array();
			$rows['query_appt_related_services_ids'] = $current_post_id ? $this->get_related_elementor_loop_post_ids( $current_post_id, self::CPT_SERVICE ) : array();
			$rows['query_appt_related_locations_ids'] = $current_post_id ? $this->get_related_elementor_loop_post_ids( $current_post_id, self::CPT_LOCATION ) : array();
			$rows['query_appt_related_team_ids'] = $current_post_id ? $this->get_related_elementor_loop_post_ids( $current_post_id, self::CPT_TEAM ) : array();
			$rows['elementor_query_hook_fired_log'] = get_option( 'appt_booker_loopgrid_query_debug', array() );

			return '<pre style="white-space:pre-wrap;background:#fff;border:1px solid #ccd0d4;padding:12px;max-width:100%;overflow:auto;">' . esc_html( print_r( $rows, true ) ) . '</pre>';
		}

		private function days_map() {
			return array(
				'mon' => 'Mon',
				'tue' => 'Tue',
				'wed' => 'Wed',
				'thu' => 'Thu',
				'fri' => 'Fri',
				'sat' => 'Sat',
				'sun' => 'Sun',
			);
		}

		private function default_hours() {
			return array(
				'mon' => array( 'enabled' => 1, 'start' => '09:00', 'end' => '17:00', 'break_enabled' => 0, 'break_start' => '12:00', 'break_end' => '13:00' ),
				'tue' => array( 'enabled' => 1, 'start' => '09:00', 'end' => '17:00', 'break_enabled' => 0, 'break_start' => '12:00', 'break_end' => '13:00' ),
				'wed' => array( 'enabled' => 1, 'start' => '09:00', 'end' => '17:00', 'break_enabled' => 0, 'break_start' => '12:00', 'break_end' => '13:00' ),
				'thu' => array( 'enabled' => 1, 'start' => '09:00', 'end' => '17:00', 'break_enabled' => 0, 'break_start' => '12:00', 'break_end' => '13:00' ),
				'fri' => array( 'enabled' => 1, 'start' => '09:00', 'end' => '17:00', 'break_enabled' => 0, 'break_start' => '12:00', 'break_end' => '13:00' ),
				'sat' => array( 'enabled' => 0, 'start' => '09:00', 'end' => '13:00', 'break_enabled' => 0, 'break_start' => '12:00', 'break_end' => '13:00' ),
				'sun' => array( 'enabled' => 0, 'start' => '09:00', 'end' => '13:00', 'break_enabled' => 0, 'break_start' => '12:00', 'break_end' => '13:00' ),
			);
		}


		public function register_elementor_content_types() {
			$common_args = array(
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => true,
				'show_in_rest'        => true,
				'exclude_from_search' => false,
				'hierarchical'        => false,
				'query_var'           => true,
				'capability_type'     => 'page',
				'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
				'can_export'          => true,
			);

			register_post_type(
				self::CPT_LOCATION,
				array_merge(
					$common_args,
					array(
						'labels'      => array(
							'name'          => 'Appt-Booker Locations',
							'singular_name' => 'Appt-Booker Location',
						),
						'rewrite'     => array( 'slug' => 'location', 'with_front' => false ),
						'has_archive' => 'locations',
						'menu_icon'   => 'dashicons-location-alt',
					)
				)
			);

			register_post_type(
				self::CPT_SERVICE,
				array_merge(
					$common_args,
					array(
						'labels'      => array(
							'name'          => 'Appt-Booker Services',
							'singular_name' => 'Appt-Booker Service',
						),
						'rewrite'     => array( 'slug' => 'service', 'with_front' => false ),
						'has_archive' => 'services',
						'menu_icon'   => 'dashicons-calendar-alt',
					)
				)
			);

			register_post_type(
				self::CPT_TEAM,
				array_merge(
					$common_args,
					array(
						'labels'      => array(
							'name'          => 'Appt-Booker Team Members',
							'singular_name' => 'Appt-Booker Team Member',
						),
						'rewrite'     => array( 'slug' => 'team', 'with_front' => false ),
						'has_archive' => 'team',
						'menu_icon'   => 'dashicons-groups',
					)
				)
			);

			$this->register_elementor_relationship_taxonomies();
		}

		private function register_elementor_relationship_taxonomies() {
			$post_types = array( self::CPT_LOCATION, self::CPT_SERVICE, self::CPT_TEAM );
			$common_args = array(
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_admin_column'  => true,
				'show_in_rest'       => true,
				'hierarchical'       => true,
				'query_var'          => true,
			);

			register_taxonomy(
				self::TAX_REL_LOCATION,
				$post_types,
				array_merge(
					$common_args,
					array(
						'labels'  => array(
							'name'          => 'Appt-Booker Related Locations',
							'singular_name' => 'Appt-Booker Related Location',
						),
						'rewrite' => array( 'slug' => 'booking-location', 'with_front' => false ),
					)
				)
			);

			register_taxonomy(
				self::TAX_REL_SERVICE,
				$post_types,
				array_merge(
					$common_args,
					array(
						'labels'  => array(
							'name'          => 'Appt-Booker Related Services',
							'singular_name' => 'Appt-Booker Related Service',
						),
						'rewrite' => array( 'slug' => 'booking-service', 'with_front' => false ),
					)
				)
			);

			register_taxonomy(
				self::TAX_REL_TEAM,
				$post_types,
				array_merge(
					$common_args,
					array(
						'labels'  => array(
							'name'          => 'Appt-Booker Related Team Members',
							'singular_name' => 'Appt-Booker Related Team Member',
						),
						'rewrite' => array( 'slug' => 'booking-team', 'with_front' => false ),
					)
				)
			);
		}


		private function relationship_taxonomies() {
			return array(
				self::TAX_REL_LOCATION => 'Locations',
				self::TAX_REL_SERVICE  => 'Services',
				self::TAX_REL_TEAM     => 'Staff / Team',
			);
		}

		private function normalize_relationship_taxonomy( $taxonomy ) {
			$taxonomy = sanitize_key( $taxonomy );
			$aliases = array(
				'location' => self::TAX_REL_LOCATION,
				'locations' => self::TAX_REL_LOCATION,
				self::TAX_REL_LOCATION => self::TAX_REL_LOCATION,
				'service' => self::TAX_REL_SERVICE,
				'services' => self::TAX_REL_SERVICE,
				self::TAX_REL_SERVICE => self::TAX_REL_SERVICE,
				'staff' => self::TAX_REL_TEAM,
				'team' => self::TAX_REL_TEAM,
				'teams' => self::TAX_REL_TEAM,
				self::TAX_REL_TEAM => self::TAX_REL_TEAM,
			);
			return isset( $aliases[ $taxonomy ] ) ? $aliases[ $taxonomy ] : '';
		}

		private function taxonomy_custom_field_types() {
			return array(
				'text' => 'Text',
				'textarea' => 'Textarea',
				'url' => 'URL',
				'email' => 'Email',
				'number' => 'Number',
				'select' => 'Select',
				'checkbox' => 'Checkbox / Yes-No',
				'checkboxes' => 'Checkboxes / Multiple Options',
			);
		}

		private function get_taxonomy_custom_fields() {
			$fields = get_option( self::OPTION_TAXONOMY_FIELDS, array() );
			return is_array( $fields ) ? $fields : array();
		}

		private function get_taxonomy_custom_fields_for( $taxonomy ) {
			$taxonomy = $this->normalize_relationship_taxonomy( $taxonomy );
			$fields = $this->get_taxonomy_custom_fields();
			return ( $taxonomy && isset( $fields[ $taxonomy ] ) && is_array( $fields[ $taxonomy ] ) ) ? $fields[ $taxonomy ] : array();
		}

		private function taxonomy_custom_field_meta_key( $field_key ) {
			return '_appt_tax_field_' . sanitize_key( $field_key );
		}

		private function taxonomy_custom_field_post_meta_key( $field_key ) {
			return 'appt_tax_field_' . sanitize_key( $field_key );
		}

		private function taxonomy_custom_field_post_meta_value( $value, $field ) {
			$value = $this->sanitize_taxonomy_custom_field_value( $value, $field );
			$type  = isset( $field['type'] ) ? sanitize_key( $field['type'] ) : 'text';

			if ( is_array( $value ) ) {
				return implode( ', ', array_map( 'sanitize_text_field', $value ) );
			}

			if ( 'checkbox' === $type ) {
				return empty( $value ) || '0' === (string) $value ? '' : 'Yes';
			}

			return is_scalar( $value ) ? (string) $value : '';
		}

		private function taxonomy_custom_field_value_is_empty( $value ) {
			if ( is_array( $value ) ) {
				return empty( array_filter( array_map( 'strlen', array_map( 'strval', $value ) ) ) );
			}
			return '' === (string) $value || '0' === (string) $value;
		}

		private function taxonomy_custom_field_post_meta_parts( $value, $field ) {
			$value = $this->sanitize_taxonomy_custom_field_value( $value, $field );
			$type  = isset( $field['type'] ) ? sanitize_key( $field['type'] ) : 'text';
			$parts = array();

			if ( is_array( $value ) ) {
				$parts = array_map( 'sanitize_text_field', $value );
			} elseif ( 'checkbox' === $type ) {
				if ( ! empty( $value ) && '0' !== (string) $value ) {
					$parts[] = 'Yes';
				}
			} elseif ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
				$parts[] = sanitize_text_field( (string) $value );
			}

			$parts = array_values( array_unique( array_filter( array_map( 'trim', $parts ), 'strlen' ) ) );
			return $parts;
		}

		private function update_taxonomy_custom_field_filterable_post_meta( $post_id, $meta_key, $display_value, $parts = array() ) {
			$post_id  = absint( $post_id );
			$meta_key = sanitize_key( $meta_key );
			if ( ! $post_id || '' === $meta_key ) { return; }

			$display_value = is_scalar( $display_value ) ? sanitize_text_field( (string) $display_value ) : '';
			$parts = array_values( array_unique( array_filter( array_map( 'trim', array_map( 'sanitize_text_field', (array) $parts ) ), 'strlen' ) ) );

			delete_post_meta( $post_id, $meta_key );

			if ( empty( $parts ) && '' !== $display_value ) {
				$parts = array( $display_value );
			}

			if ( empty( $parts ) ) { return; }

			/*
			 * Store each selected value as its own post meta row. This is the most
			 * compatible format for filtering plugins because they can match Beginner
			 * or Mobility independently. The get_post_metadata filter below joins the
			 * rows for Elementor display only, so headings still show: Beginner, Mobility.
			 */
			foreach ( $parts as $part ) {
				add_post_meta( $post_id, $meta_key, $part, false );
			}
		}

		public function filter_taxonomy_custom_field_post_meta_display_value( $value, $object_id, $meta_key, $single, $meta_type ) {
			$joinable_meta_keys = array(
				'appt_booker_location_names',
				'appt_booker_service_names',
				'appt_booker_team_names',
			);
			$is_joinable_custom_field = is_string( $meta_key ) && 0 === strpos( $meta_key, 'appt_tax_field_' );
			$is_joinable_relationship = is_string( $meta_key ) && in_array( $meta_key, $joinable_meta_keys, true );
			if ( 'post' !== $meta_type || ! $single || ( ! $is_joinable_custom_field && ! $is_joinable_relationship ) ) {
				return $value;
			}

			$object_id = absint( $object_id );
			if ( ! $object_id ) { return $value; }

			remove_filter( 'get_post_metadata', array( $this, 'filter_taxonomy_custom_field_post_meta_display_value' ), 10 );
			$raw_values = get_post_meta( $object_id, $meta_key, false );
			add_filter( 'get_post_metadata', array( $this, 'filter_taxonomy_custom_field_post_meta_display_value' ), 10, 5 );

			$parts = array();
			foreach ( (array) $raw_values as $raw ) {
				if ( is_array( $raw ) ) {
					foreach ( $raw as $nested ) {
						if ( is_scalar( $nested ) ) {
							$parts[] = sanitize_text_field( (string) $nested );
						}
					}
				} elseif ( is_scalar( $raw ) ) {
					$parts[] = sanitize_text_field( (string) $raw );
				}
			}

			$parts = array_values( array_unique( array_filter( array_map( 'trim', $parts ), 'strlen' ) ) );

			if ( count( $parts ) > 1 ) {
				return implode( ', ', $parts );
			}

			return isset( $parts[0] ) ? $parts[0] : $value;
		}
		private function sync_all_assigned_taxonomy_custom_fields_to_post_meta( $post_id ) {
			$post_id = absint( $post_id );
			if ( ! $post_id ) { return; }

			foreach ( array_keys( $this->relationship_taxonomies() ) as $taxonomy ) {
				$fields = $this->get_taxonomy_custom_fields_for( $taxonomy );
				if ( empty( $fields ) ) { continue; }

				$terms = wp_get_post_terms( $post_id, $taxonomy );
				if ( is_wp_error( $terms ) ) { $terms = array(); }

				foreach ( $fields as $field_key => $field ) {
					$parts = array();
					foreach ( $terms as $term ) {
						$value = get_term_meta( $term->term_id, $this->taxonomy_custom_field_meta_key( $field_key ), true );
						if ( $this->taxonomy_custom_field_value_is_empty( $value ) ) { continue; }
						foreach ( $this->taxonomy_custom_field_post_meta_parts( $value, $field ) as $part ) {
							$parts[] = $part;
						}
					}

					$meta_key = $this->taxonomy_custom_field_post_meta_key( $field_key );
					$parts = array_values( array_unique( array_filter( array_map( 'trim', $parts ), 'strlen' ) ) );
					$this->update_taxonomy_custom_field_filterable_post_meta( $post_id, $meta_key, implode( ', ', $parts ), $parts );
				}
			}
		}

		private function relationship_taxonomy_for_post_type( $post_type ) {
			if ( self::CPT_LOCATION === $post_type ) { return self::TAX_REL_LOCATION; }
			if ( self::CPT_SERVICE === $post_type ) { return self::TAX_REL_SERVICE; }
			if ( self::CPT_TEAM === $post_type ) { return self::TAX_REL_TEAM; }
			return '';
		}

		private function post_type_for_relationship_taxonomy( $taxonomy ) {
			$taxonomy = $this->normalize_relationship_taxonomy( $taxonomy );
			if ( self::TAX_REL_LOCATION === $taxonomy ) { return self::CPT_LOCATION; }
			if ( self::TAX_REL_SERVICE === $taxonomy ) { return self::CPT_SERVICE; }
			if ( self::TAX_REL_TEAM === $taxonomy ) { return self::CPT_TEAM; }
			return '';
		}

		private function sync_taxonomy_custom_fields_to_post_meta( $post_id, $post_type, $item ) {
			$post_id  = absint( $post_id );
			$taxonomy = $this->relationship_taxonomy_for_post_type( $post_type );
			if ( ! $post_id || ! $taxonomy ) { return; }

			$fields = $this->get_taxonomy_custom_fields_for( $taxonomy );
			$values = isset( $item['taxonomy_fields'] ) && is_array( $item['taxonomy_fields'] ) ? $item['taxonomy_fields'] : array();

			foreach ( $fields as $field_key => $field ) {
				$meta_key   = $this->taxonomy_custom_field_post_meta_key( $field_key );
				$raw_value  = isset( $values[ $field_key ] ) ? $values[ $field_key ] : '';
				$parts      = $this->taxonomy_custom_field_post_meta_parts( $raw_value, $field );
				$this->update_taxonomy_custom_field_filterable_post_meta( $post_id, $meta_key, implode( ', ', $parts ), $parts );
			}
		}

		private function sync_taxonomy_custom_fields_from_term_to_post_meta( $term_id, $taxonomy ) {
			$term_id   = absint( $term_id );
			$taxonomy  = $this->normalize_relationship_taxonomy( $taxonomy );
			$post_type = $this->post_type_for_relationship_taxonomy( $taxonomy );
			if ( ! $term_id || ! $taxonomy || ! $post_type ) { return; }

			$post_ids = get_objects_in_term( $term_id, $taxonomy );
			if ( is_wp_error( $post_ids ) ) { $post_ids = array(); }

			$item_id = sanitize_key( get_term_meta( $term_id, '_appt_booker_item_id', true ) );
			if ( '' !== $item_id ) {
				$linked_post_id = $this->find_elementor_content_record_id( $post_type, $item_id );
				if ( $linked_post_id ) { $post_ids[] = $linked_post_id; }
			}

			$post_ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $post_ids ) ) ) );
			foreach ( $post_ids as $post_id ) {
				$this->sync_all_assigned_taxonomy_custom_fields_to_post_meta( $post_id );
			}
		}

		private function delete_taxonomy_custom_field_post_meta_for_taxonomy( $taxonomy, $field_key ) {
			$post_type = $this->post_type_for_relationship_taxonomy( $taxonomy );
			$field_key = sanitize_key( $field_key );
			if ( ! $post_type || '' === $field_key ) { return; }
			$posts = get_posts( array(
				'post_type'      => $post_type,
				'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			) );
			foreach ( $posts as $post_id ) {
				delete_post_meta( absint( $post_id ), $this->taxonomy_custom_field_post_meta_key( $field_key ) );
			}
		}

		private function sanitize_taxonomy_custom_field_value( $value, $field ) {
			$type = isset( $field['type'] ) ? sanitize_key( $field['type'] ) : 'text';
			$options = isset( $field['options'] ) && is_array( $field['options'] ) ? array_map( 'sanitize_text_field', $field['options'] ) : array();

			if ( 'checkboxes' === $type || ( 'checkbox' === $type && ! empty( $options ) ) ) {
				$values = is_array( $value ) ? $value : ( '' !== (string) $value ? array( $value ) : array() );
				$clean  = array();
				foreach ( $values as $single_value ) {
					$single_value = sanitize_text_field( wp_unslash( $single_value ) );
					if ( '' !== $single_value && ( empty( $options ) || in_array( $single_value, $options, true ) ) ) {
						$clean[] = $single_value;
					}
				}
				return array_values( array_unique( $clean ) );
			}

			if ( 'checkbox' === $type ) {
				return empty( $value ) ? '0' : '1';
			}
			$value = is_scalar( $value ) ? wp_unslash( $value ) : '';
			if ( 'textarea' === $type ) { return sanitize_textarea_field( $value ); }
			if ( 'url' === $type ) { return esc_url_raw( $value ); }
			if ( 'email' === $type ) { return sanitize_email( $value ); }
			if ( 'number' === $type ) { return is_numeric( $value ) ? (string) $value : ''; }
			return sanitize_text_field( $value );
		}

		private function sanitize_taxonomy_custom_field_values_for_taxonomy( $taxonomy, $raw_values ) {
			$clean = array();
			$fields = $this->get_taxonomy_custom_fields_for( $taxonomy );
			$raw_values = is_array( $raw_values ) ? $raw_values : array();
			foreach ( $fields as $field_key => $field ) {
				$raw = isset( $raw_values[ $field_key ] ) ? $raw_values[ $field_key ] : '';
				$clean[ $field_key ] = $this->sanitize_taxonomy_custom_field_value( $raw, $field );
			}
			return $clean;
		}

		private function register_taxonomy_custom_field_hooks() {
			foreach ( array_keys( $this->relationship_taxonomies() ) as $taxonomy ) {
				add_action( $taxonomy . '_add_form_fields', array( $this, 'render_taxonomy_custom_fields_add' ) );
				add_action( $taxonomy . '_edit_form_fields', array( $this, 'render_taxonomy_custom_fields_edit' ), 10, 2 );
				add_action( 'created_' . $taxonomy, array( $this, 'save_taxonomy_custom_fields' ), 10, 2 );
				add_action( 'edited_' . $taxonomy, array( $this, 'save_taxonomy_custom_fields' ), 10, 2 );
			}
		}

		public function render_taxonomy_custom_fields_add( $taxonomy ) {
			$fields = $this->get_taxonomy_custom_fields_for( $taxonomy );
			foreach ( $fields as $field_key => $field ) {
				echo '<div class="form-field"><label for="appt_tax_field_' . esc_attr( $field_key ) . '">' . esc_html( $field['label'] ) . '</label>';
				$this->render_taxonomy_custom_field_control( $field_key, $field, '' );
				echo '</div>';
			}
		}

		public function render_taxonomy_custom_fields_edit( $term, $taxonomy ) {
			$fields = $this->get_taxonomy_custom_fields_for( $taxonomy );
			foreach ( $fields as $field_key => $field ) {
				$value = get_term_meta( $term->term_id, $this->taxonomy_custom_field_meta_key( $field_key ), true );
				echo '<tr class="form-field"><th scope="row"><label for="appt_tax_field_' . esc_attr( $field_key ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';
				$this->render_taxonomy_custom_field_control( $field_key, $field, $value );
				echo '</td></tr>';
			}
		}

		private function render_taxonomy_custom_field_control( $field_key, $field, $value = '' ) {
			$name = 'appt_tax_fields[' . sanitize_key( $field_key ) . ']';
			$this->render_taxonomy_custom_field_control_named( $field_key, $field, $value, $name );
		}

		private function render_taxonomy_custom_field_control_named( $field_key, $field, $value = '', $name = '' ) {
			$type = isset( $field['type'] ) ? sanitize_key( $field['type'] ) : 'text';
			$name = '' !== $name ? $name : 'appt_tax_fields[' . sanitize_key( $field_key ) . ']';
			$id = 'appt_tax_field_' . sanitize_key( $field_key ) . '_' . md5( $name );
			$options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
			if ( 'textarea' === $type ) {
				echo '<textarea id="' . esc_attr( $id ) . '" data-name="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" rows="4" cols="40">' . esc_textarea( is_array( $value ) ? implode( "\n", $value ) : $value ) . '</textarea>';
				return;
			}
			if ( 'select' === $type ) {
				echo '<select id="' . esc_attr( $id ) . '" data-name="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '"><option value="">— Select —</option>';
				foreach ( $options as $option ) {
					echo '<option value="' . esc_attr( $option ) . '" ' . selected( $value, $option, false ) . '>' . esc_html( $option ) . '</option>';
				}
				echo '</select>';
				return;
			}
			if ( 'checkboxes' === $type || ( 'checkbox' === $type && ! empty( $options ) ) ) {
				$values = is_array( $value ) ? $value : array_filter( array( (string) $value ) );
				if ( empty( $options ) ) { $options = array( 'Yes' ); }
				echo '<div class="rgl-checkbox-list">';
				foreach ( $options as $option ) {
					echo '<label style="display:block;margin:4px 0;"><input type="checkbox" data-name="' . esc_attr( $name . '[]' ) . '" name="' . esc_attr( $name . '[]' ) . '" value="' . esc_attr( $option ) . '" ' . checked( in_array( $option, $values, true ), true, false ) . '> ' . esc_html( $option ) . '</label>';
				}
				echo '</div>';
				return;
			}
			if ( 'checkbox' === $type ) {
				echo '<label><input type="checkbox" id="' . esc_attr( $id ) . '" data-name="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="1" ' . checked( $value, '1', false ) . '> Yes</label>';
				return;
			}
			$input_type = in_array( $type, array( 'url', 'email', 'number' ), true ) ? $type : 'text';
			echo '<input type="' . esc_attr( $input_type ) . '" id="' . esc_attr( $id ) . '" data-name="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( is_array( $value ) ? implode( ', ', $value ) : $value ) . '" class="regular-text">';
		}

		private function render_admin_item_taxonomy_custom_fields( $taxonomy, $base_name, $row_index, $values = array() ) {
			$fields = $this->get_taxonomy_custom_fields_for( $taxonomy );
			if ( empty( $fields ) ) { return; }
			$values = is_array( $values ) ? $values : array();
			echo '<div class="rgl-taxonomy-custom-fields rgl-full" style="margin-top:12px;padding:12px;border:1px solid #dcdcde;background:#fff;">';
			echo '<h4 style="margin:0 0 8px;">Custom Fields</h4>';
			echo '<div class="rgl-grid-admin rgl-grid-admin--2">';
			foreach ( $fields as $field_key => $field ) {
				$value = isset( $values[ $field_key ] ) ? $values[ $field_key ] : '';
				$name = $base_name . '[' . $row_index . '][taxonomy_fields][' . sanitize_key( $field_key ) . ']';
				echo '<p><label>' . esc_html( $field['label'] ) . '<br>';
				$this->render_taxonomy_custom_field_control_named( $field_key, $field, $value, $name );
				echo '</label></p>';
			}
			echo '</div></div>';
		}

		public function save_taxonomy_custom_fields( $term_id, $tt_id = 0 ) {
			$term = get_term( $term_id );
			if ( ! $term || is_wp_error( $term ) ) { return; }
			$fields = $this->get_taxonomy_custom_fields_for( $term->taxonomy );
			$posted = isset( $_POST['appt_tax_fields'] ) && is_array( $_POST['appt_tax_fields'] ) ? wp_unslash( $_POST['appt_tax_fields'] ) : array();
			foreach ( $fields as $field_key => $field ) {
				$raw = isset( $posted[ $field_key ] ) ? $posted[ $field_key ] : '';
				$value = $this->sanitize_taxonomy_custom_field_value( $raw, $field );
				update_term_meta( $term_id, $this->taxonomy_custom_field_meta_key( $field_key ), $value );
			}
			$this->sync_taxonomy_custom_fields_from_term_to_post_meta( $term_id, $term->taxonomy );
		}

		private function resolve_taxonomy_custom_field_term( $taxonomy = '', $term_id = 0, $post_id = 0 ) {
			$taxonomy = $this->normalize_relationship_taxonomy( $taxonomy );
			$term_id = absint( $term_id );
			$post_id = absint( $post_id );
			if ( $term_id && $taxonomy ) {
				$term = get_term( $term_id, $taxonomy );
				return ( $term && ! is_wp_error( $term ) ) ? $term : false;
			}
			$queried = get_queried_object();
			if ( $queried instanceof WP_Term && ( ! $taxonomy || $queried->taxonomy === $taxonomy ) ) { return $queried; }
			if ( ! $post_id ) { $post_id = get_the_ID(); }
			if ( $post_id ) {
				if ( ! $taxonomy ) {
					$post_type = get_post_type( $post_id );
					if ( self::CPT_LOCATION === $post_type ) { $taxonomy = self::TAX_REL_LOCATION; }
					if ( self::CPT_SERVICE === $post_type ) { $taxonomy = self::TAX_REL_SERVICE; }
					if ( self::CPT_TEAM === $post_type ) { $taxonomy = self::TAX_REL_TEAM; }
				}
				if ( $taxonomy ) {
					$terms = wp_get_post_terms( $post_id, $taxonomy );
					if ( ! is_wp_error( $terms ) && ! empty( $terms[0] ) ) { return $terms[0]; }
				}
			}
			return false;
		}

		public function shortcode_taxonomy_field( $atts ) {
			$atts = shortcode_atts( array( 'taxonomy' => '', 'field' => '', 'term_id' => 0, 'post_id' => 0, 'before' => '', 'after' => '', 'label' => '0' ), $atts, 'appt_tax_field' );
			$field_key = sanitize_key( $atts['field'] );
			if ( '' === $field_key ) { return ''; }
			$term = $this->resolve_taxonomy_custom_field_term( $atts['taxonomy'], $atts['term_id'], $atts['post_id'] );
			if ( ! $term ) { return ''; }
			$fields = $this->get_taxonomy_custom_fields_for( $term->taxonomy );
			if ( empty( $fields[ $field_key ] ) ) { return ''; }
			$field = $fields[ $field_key ];
			$value = get_term_meta( $term->term_id, $this->taxonomy_custom_field_meta_key( $field_key ), true );
			if ( ( is_array( $value ) && empty( $value ) ) || '' === $value || '0' === $value ) { return ''; }
			$type = isset( $field['type'] ) ? sanitize_key( $field['type'] ) : 'text';
			if ( 'url' === $type ) { $output = '<a href="' . esc_url( $value ) . '">' . esc_html( $value ) . '</a>'; }
			elseif ( 'email' === $type ) { $output = '<a href="mailto:' . esc_attr( antispambot( $value ) ) . '">' . esc_html( antispambot( $value ) ) . '</a>'; }
			elseif ( is_array( $value ) ) { $output = esc_html( implode( ', ', $value ) ); }
			elseif ( 'checkbox' === $type ) { $output = 'Yes'; }
			elseif ( 'textarea' === $type ) { $output = nl2br( esc_html( $value ) ); }
			else { $output = esc_html( $value ); }
			if ( ! empty( $atts['label'] ) && '0' !== $atts['label'] && ! empty( $field['label'] ) ) { $output = '<strong>' . esc_html( $field['label'] ) . ':</strong> ' . $output; }
			return wp_kses_post( $atts['before'] ) . $output . wp_kses_post( $atts['after'] );
		}
		public function maybe_sync_elementor_content_records() {
			if ( ! $this->elementor_content_types_are_registered() ) {
				return;
			}

			if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
				return;
			}

			$source_hash = $this->get_elementor_content_source_hash();
			$stored_hash = (string) get_option( self::OPTION_ELEMENTOR_SYNC_HASH, '' );

			if ( '' !== $stored_hash && hash_equals( $stored_hash, $source_hash ) ) {
				return;
			}

			if ( get_transient( self::TRANSIENT_ELEMENTOR_SYNC_LOCK ) ) {
				return;
			}

			set_transient( self::TRANSIENT_ELEMENTOR_SYNC_LOCK, 1, 5 * MINUTE_IN_SECONDS );
			$this->sync_elementor_content_records( $source_hash );
			delete_transient( self::TRANSIENT_ELEMENTOR_SYNC_LOCK );
		}

		public function sync_elementor_content_records( $source_hash = '' ) {
			if ( ! $this->elementor_content_types_are_registered() ) {
				return;
			}

			$this->sync_elementor_content_group( self::CPT_LOCATION, $this->get_active_locations(), 'location_id' );
			$this->sync_elementor_content_group( self::CPT_SERVICE, $this->get_active_services(), 'service_id' );
			$this->sync_elementor_content_group( self::CPT_TEAM, $this->get_active_staff(), 'staff_id' );

			// Build the real post ID indexes after all three content groups exist.
			// Elementor Query IDs only read these saved indexes on the frontend.
			$this->rebuild_elementor_related_post_id_indexes();

			if ( '' === $source_hash ) {
				$source_hash = $this->get_elementor_content_source_hash();
			}

			update_option( self::OPTION_ELEMENTOR_SYNC_HASH, $source_hash, false );
		}

		private function get_elementor_content_posts_for_indexing() {
			$posts = get_posts(
				array(
					'post_type'              => array( self::CPT_LOCATION, self::CPT_SERVICE, self::CPT_TEAM ),
					'post_status'            => array( 'publish', 'draft', 'private', 'pending' ),
					'posts_per_page'         => -1,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_meta_cache' => true,
					'update_post_term_cache' => false,
					'meta_query'             => array(
						array(
							'key'   => '_appt_booker_source',
							'value' => 'appt_booker',
						),
					),
				)
			);

			return array_map( 'absint', (array) $posts );
		}

		private function build_elementor_item_id_to_post_id_map( $post_ids ) {
			$map = array(
				self::CPT_LOCATION => array(),
				self::CPT_SERVICE  => array(),
				self::CPT_TEAM     => array(),
			);

			foreach ( (array) $post_ids as $post_id ) {
				$post_id   = absint( $post_id );
				$post_type = get_post_type( $post_id );
				$item_id   = sanitize_key( get_post_meta( $post_id, '_appt_booker_item_id', true ) );

				if ( ! $post_id || '' === $item_id || ! isset( $map[ $post_type ] ) ) {
					continue;
				}

				$map[ $post_type ][ $item_id ] = $post_id;
			}

			return $map;
		}

		private function item_ids_to_post_ids_for_index( $item_ids, $target_post_type, $map ) {
			$post_ids = array();

			foreach ( (array) $item_ids as $item_id ) {
				$item_id = sanitize_key( $item_id );
				if ( '' === $item_id || empty( $map[ $target_post_type ][ $item_id ] ) ) {
					continue;
				}

				$post_ids[] = absint( $map[ $target_post_type ][ $item_id ] );
			}

			return $this->normalise_post_id_list( $post_ids );
		}

		private function rebuild_elementor_related_post_id_indexes() {
			$post_ids = $this->get_elementor_content_posts_for_indexing();
			if ( empty( $post_ids ) ) {
				return;
			}

			$map = $this->build_elementor_item_id_to_post_id_map( $post_ids );

			foreach ( $post_ids as $post_id ) {
				$post_id = absint( $post_id );
				if ( ! $post_id ) {
					continue;
				}

				$location_item_ids = get_post_meta( $post_id, '_appt_booker_location_ids', true );
				$service_item_ids  = get_post_meta( $post_id, '_appt_booker_service_ids', true );
				$team_item_ids     = get_post_meta( $post_id, '_appt_booker_team_ids', true );

				update_post_meta( $post_id, '_appt_related_location_post_ids', $this->item_ids_to_post_ids_for_index( $location_item_ids, self::CPT_LOCATION, $map ) );
				update_post_meta( $post_id, '_appt_related_service_post_ids', $this->item_ids_to_post_ids_for_index( $service_item_ids, self::CPT_SERVICE, $map ) );
				update_post_meta( $post_id, '_appt_related_team_post_ids', $this->item_ids_to_post_ids_for_index( $team_item_ids, self::CPT_TEAM, $map ) );
			}
		}

		private function elementor_content_types_are_registered() {
			return post_type_exists( self::CPT_LOCATION ) && post_type_exists( self::CPT_SERVICE ) && post_type_exists( self::CPT_TEAM );
		}

		private function get_elementor_content_source_hash() {
			$source = array(
				'relationship_filter_meta_version' => '2026-05-07-location-team-index-v2',
				'locations' => $this->get_active_locations(),
				'services'  => $this->get_active_services(),
				'staff'     => $this->get_active_staff(),
			);

			return md5( wp_json_encode( $source ) );
		}

		private function sync_elementor_content_group( $post_type, $items, $meta_key ) {
			$active_ids = array();

			foreach ( (array) $items as $item ) {
				$item_id = isset( $item['id'] ) ? sanitize_key( $item['id'] ) : '';
				$name    = isset( $item['name'] ) ? sanitize_text_field( $item['name'] ) : '';
				if ( '' === $item_id || '' === $name ) {
					continue;
				}

				$active_ids[] = $item_id;
				$post_id      = $this->find_elementor_content_record_id( $post_type, $item_id );
				$post_name    = $this->normalize_lookup_key( $name );
				if ( '' === $post_name ) {
					$post_name = $this->normalize_lookup_key( $item_id );
				}

				$post_data = array(
					'post_type'    => $post_type,
					'post_status'  => 'publish',
					'post_title'   => $name,
					'post_name'    => $post_name,
					'post_content' => $this->build_elementor_content_record_body( $post_type, $item ),
					'post_excerpt' => $this->build_elementor_content_record_excerpt( $post_type, $item ),
				);

				if ( $post_id ) {
					$post_data['ID'] = $post_id;
					wp_update_post( wp_slash( $post_data ) );
				} else {
					$post_id = wp_insert_post( wp_slash( $post_data ), true );
				}

				if ( ! is_wp_error( $post_id ) && $post_id ) {
					update_post_meta( $post_id, '_appt_booker_source', 'appt_booker' );
					update_post_meta( $post_id, '_appt_booker_item_id', $item_id );
					update_post_meta( $post_id, '_appt_booker_dynamic_key', $meta_key );
					update_post_meta( $post_id, '_appt_booker_item_data', wp_json_encode( $item ) );
					$this->sync_elementor_friendly_meta_keys( $post_id, $post_type, $item );
					$this->sync_taxonomy_custom_fields_to_post_meta( $post_id, $post_type, $item );

					if ( self::CPT_LOCATION === $post_type ) {
						$location_address  = isset( $item['address'] ) ? sanitize_textarea_field( $item['address'] ) : '';
						$location_postcode = isset( $item['postcode'] ) ? sanitize_text_field( $item['postcode'] ) : '';

						update_post_meta( $post_id, '_appt_booker_address', $location_address );
						update_post_meta( $post_id, '_appt_booker_postcode', $location_postcode );
					}

					$this->sync_elementor_content_record_image( $post_id, $item );
					$this->sync_elementor_content_record_relationships( $post_id, $post_type, $item );
				}
			}

			$this->draft_elementor_content_records_not_in_source( $post_type, $active_ids );
		}

		private function find_elementor_content_record_id( $post_type, $item_id ) {
			$posts = get_posts(
				array(
					'post_type'              => $post_type,
					'post_status'            => array( 'publish', 'draft', 'private', 'pending' ),
					'posts_per_page'         => 1,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'meta_query'             => array(
						array(
							'key'   => '_appt_booker_item_id',
							'value' => sanitize_key( $item_id ),
						),
					),
				)
			);

			return ! empty( $posts[0] ) ? absint( $posts[0] ) : 0;
		}

		private function draft_elementor_content_records_not_in_source( $post_type, $active_ids ) {
			$active_ids = array_values( array_unique( array_filter( array_map( 'sanitize_key', (array) $active_ids ) ) ) );
			$posts      = get_posts(
				array(
					'post_type'              => $post_type,
					'post_status'            => array( 'publish', 'private', 'pending' ),
					'posts_per_page'         => -1,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'meta_query'             => array(
						array(
							'key'   => '_appt_booker_source',
							'value' => 'appt_booker',
						),
					),
				)
			);

			foreach ( $posts as $post_id ) {
				$item_id = sanitize_key( get_post_meta( $post_id, '_appt_booker_item_id', true ) );
				if ( '' !== $item_id && ! in_array( $item_id, $active_ids, true ) ) {
					wp_update_post(
						array(
							'ID'          => absint( $post_id ),
							'post_status' => 'draft',
						)
					);
				}
			}
		}

		private function get_item_description( $item ) {
			return isset( $item['description'] ) ? sanitize_textarea_field( $item['description'] ) : '';
		}

		private function sanitize_extra_image_fields( $row ) {
			$extra_images = array();
			if ( ! is_array( $row ) ) {
				return $extra_images;
			}

			for ( $i = 1; $i <= self::EXTRA_IMAGE_SLOTS; $i++ ) {
				$id_key  = 'extra_image_' . $i . '_id';
				$url_key = 'extra_image_' . $i . '_url';

				$extra_images[ $id_key ]  = absint( isset( $row[ $id_key ] ) ? $row[ $id_key ] : 0 );
				$extra_images[ $url_key ] = esc_url_raw( wp_unslash( isset( $row[ $url_key ] ) ? $row[ $url_key ] : '' ) );
			}

			return $extra_images;
		}

		private function get_item_extra_image_id( $item, $slot ) {
			$slot = absint( $slot );
			$key  = 'extra_image_' . $slot . '_id';
			return isset( $item[ $key ] ) ? absint( $item[ $key ] ) : 0;
		}

		private function get_item_extra_image_url( $item, $slot ) {
			$slot = absint( $slot );
			$key  = 'extra_image_' . $slot . '_url';
			return isset( $item[ $key ] ) ? esc_url_raw( $item[ $key ] ) : '';
		}

		private function sync_extra_image_meta_keys( $post_id, $prefix, $item ) {
			$post_id = absint( $post_id );
			if ( ! $post_id || '' === $prefix || ! is_array( $item ) ) {
				return;
			}

			for ( $i = 1; $i <= self::EXTRA_IMAGE_SLOTS; $i++ ) {
				$image_id  = $this->get_item_extra_image_id( $item, $i );
				$image_url = $this->get_item_extra_image_url( $item, $i );

				update_post_meta( $post_id, $prefix . '_extra_image_' . $i . '_id', $image_id );
				update_post_meta( $post_id, $prefix . '_extra_image_' . $i . '_url', $image_url );
			}
		}

		private function sync_internal_extra_image_meta_keys( $post_id, $item ) {
			$post_id = absint( $post_id );
			if ( ! $post_id || ! is_array( $item ) ) {
				return;
			}

			for ( $i = 1; $i <= self::EXTRA_IMAGE_SLOTS; $i++ ) {
				$image_id  = $this->get_item_extra_image_id( $item, $i );
				$image_url = $this->get_item_extra_image_url( $item, $i );

				update_post_meta( $post_id, '_appt_booker_extra_image_' . $i . '_id', $image_id );
				update_post_meta( $post_id, '_appt_booker_extra_image_' . $i . '_url', $image_url );
			}
		}

		private function get_item_image_id( $item ) {
			return isset( $item['image_id'] ) ? absint( $item['image_id'] ) : 0;
		}

		private function get_item_image_url( $item ) {
			return isset( $item['image_url'] ) ? esc_url_raw( $item['image_url'] ) : '';
		}

		private function sync_elementor_friendly_meta_keys( $post_id, $post_type, $item ) {
			$post_id = absint( $post_id );
			if ( ! $post_id || ! is_array( $item ) ) {
				return;
			}

			$name        = isset( $item['name'] ) ? sanitize_text_field( $item['name'] ) : '';
			$description = $this->get_item_description( $item );
			$image_id    = $this->get_item_image_id( $item );
			$image_url   = $this->get_item_image_url( $item );

			if ( self::CPT_LOCATION === $post_type ) {
				$address  = isset( $item['address'] ) ? sanitize_textarea_field( $item['address'] ) : '';
				$postcode = isset( $item['postcode'] ) ? sanitize_text_field( $item['postcode'] ) : '';

				update_post_meta( $post_id, 'appt_location_name', $name );
				update_post_meta( $post_id, 'appt_location_postcode', $postcode );
				update_post_meta( $post_id, 'appt_location_address', $address );
				update_post_meta( $post_id, 'appt_location_description', $description );
				update_post_meta( $post_id, 'appt_location_image_id', $image_id );
				update_post_meta( $post_id, 'appt_location_image_url', $image_url );
				$this->sync_extra_image_meta_keys( $post_id, 'appt_location', $item );
			}

			if ( self::CPT_TEAM === $post_type ) {
				$email = isset( $item['email'] ) ? sanitize_email( $item['email'] ) : '';

				update_post_meta( $post_id, 'appt_staff_name', $name );
				update_post_meta( $post_id, 'appt_staff_email', $email );
				update_post_meta( $post_id, 'appt_staff_description', $description );
				update_post_meta( $post_id, 'appt_staff_image_id', $image_id );
				update_post_meta( $post_id, 'appt_staff_image_url', $image_url );
				$this->sync_extra_image_meta_keys( $post_id, 'appt_staff', $item );
			}

			if ( self::CPT_SERVICE === $post_type ) {
				$duration = isset( $item['duration'] ) ? absint( $item['duration'] ) : 0;

				update_post_meta( $post_id, 'appt_service_name', $name );
				update_post_meta( $post_id, 'appt_service_duration', $duration );
				update_post_meta( $post_id, 'appt_service_description', $description );
				update_post_meta( $post_id, 'appt_service_image_id', $image_id );
				update_post_meta( $post_id, 'appt_service_image_url', $image_url );
				$this->sync_extra_image_meta_keys( $post_id, 'appt_service', $item );
			}
		}

		private function sync_elementor_content_record_image( $post_id, $item ) {
			$post_id   = absint( $post_id );
			$image_id  = $this->get_item_image_id( $item );
			$image_url = $this->get_item_image_url( $item );

			update_post_meta( $post_id, '_appt_booker_image_id', $image_id );
			update_post_meta( $post_id, '_appt_booker_image_url', $image_url );
			$this->sync_internal_extra_image_meta_keys( $post_id, $item );

			if ( $image_id && 'attachment' === get_post_type( $image_id ) ) {
				set_post_thumbnail( $post_id, $image_id );
			} else {
				delete_post_thumbnail( $post_id );
			}
		}

		private function build_elementor_content_record_body( $post_type, $item ) {
			$description = $this->get_item_description( $item );
			if ( '' !== $description ) {
				return $description;
			}

			if ( self::CPT_LOCATION === $post_type ) {
				$parts = array();
				if ( ! empty( $item['address'] ) ) {
					$parts[] = sanitize_textarea_field( $item['address'] );
				}
				if ( ! empty( $item['postcode'] ) ) {
					$parts[] = sanitize_text_field( $item['postcode'] );
				}
				return implode( "\n", $parts );
			}

			if ( self::CPT_SERVICE === $post_type ) {
				$duration = isset( $item['duration'] ) ? absint( $item['duration'] ) : 0;
				$parts    = array();
				if ( $duration ) { $parts[] = 'Duration: ' . $duration . ' minutes'; }
				return implode( "\n", $parts );
			}

			if ( self::CPT_TEAM === $post_type ) {
				$email = isset( $item['email'] ) ? sanitize_email( $item['email'] ) : '';
				return $email ? 'Email: ' . $email : '';
			}

			return isset( $item['name'] ) ? sanitize_text_field( $item['name'] ) : '';
		}

		private function build_elementor_content_record_excerpt( $post_type, $item ) {
			$description = $this->get_item_description( $item );
			if ( '' !== $description ) {
				return wp_trim_words( $description, 32 );
			}
			if ( self::CPT_SERVICE === $post_type && ! empty( $item['duration'] ) ) {
				return absint( $item['duration'] ) . ' minute service';
			}
			if ( self::CPT_LOCATION === $post_type ) {
				$parts = array();
				if ( ! empty( $item['address'] ) ) {
					$parts[] = sanitize_textarea_field( $item['address'] );
				}
				if ( ! empty( $item['postcode'] ) ) {
					$parts[] = sanitize_text_field( $item['postcode'] );
				}
				if ( ! empty( $parts ) ) {
					return wp_trim_words( implode( ', ', $parts ), 24 );
				}
			}
			if ( self::CPT_TEAM === $post_type && ! empty( $item['email'] ) ) {
				return sanitize_email( $item['email'] );
			}
			return isset( $item['name'] ) ? sanitize_text_field( $item['name'] ) : '';
		}

		private function get_active_item_by_id( $items, $item_id ) {
			$item_id = sanitize_key( $item_id );
			foreach ( (array) $items as $item ) {
				if ( isset( $item['id'] ) && sanitize_key( $item['id'] ) === $item_id ) {
					return $item;
				}
			}
			return false;
		}

		private function get_active_item_names_by_ids( $items, $ids ) {
			$names = array();
			$ids   = array_values( array_unique( array_filter( array_map( 'sanitize_key', (array) $ids ) ) ) );

			foreach ( $ids as $item_id ) {
				$item = $this->get_active_item_by_id( $items, $item_id );
				if ( ! $item || empty( $item['name'] ) ) {
					continue;
				}
				$names[] = sanitize_text_field( $item['name'] );
			}

			return array_values( array_unique( array_filter( array_map( 'trim', $names ), 'strlen' ) ) );
		}

		private function update_filterable_name_post_meta( $post_id, $meta_key, $names ) {
			$post_id  = absint( $post_id );
			$meta_key = sanitize_key( $meta_key );
			if ( ! $post_id || '' === $meta_key ) {
				return;
			}

			$names = array_values( array_unique( array_filter( array_map( 'trim', array_map( 'sanitize_text_field', (array) $names ) ), 'strlen' ) ) );
			delete_post_meta( $post_id, $meta_key );

			foreach ( $names as $name ) {
				add_post_meta( $post_id, $meta_key, $name, false );
			}
		}

		private function ensure_relationship_terms_for_items( $taxonomy, $items, $ids ) {
			$term_ids = array();
			if ( ! taxonomy_exists( $taxonomy ) ) {
				return $term_ids;
			}

			$ids = array_values( array_unique( array_filter( array_map( 'sanitize_key', (array) $ids ) ) ) );
			foreach ( $ids as $item_id ) {
				$item = $this->get_active_item_by_id( $items, $item_id );
				if ( ! $item || empty( $item['name'] ) ) {
					continue;
				}

				$name = sanitize_text_field( $item['name'] );
				$slug = $this->normalize_lookup_key( $name );
				if ( '' === $slug ) {
					$slug = $this->normalize_lookup_key( $item_id );
				}

				$term = get_term_by( 'slug', $slug, $taxonomy );
				if ( ! $term ) {
					$created = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
					if ( is_wp_error( $created ) ) {
						continue;
					}
					$term_id = absint( $created['term_id'] );
				} else {
					$term_id = absint( $term->term_id );
					if ( $term->name !== $name ) {
						wp_update_term( $term_id, $taxonomy, array( 'name' => $name, 'slug' => $slug ) );
					}
				}

				if ( $term_id ) {
					update_term_meta( $term_id, '_appt_booker_item_id', $item_id );
					if ( ! empty( $item['taxonomy_fields'] ) && is_array( $item['taxonomy_fields'] ) ) {
						$custom_fields = $this->get_taxonomy_custom_fields_for( $taxonomy );
						foreach ( $custom_fields as $custom_field_key => $custom_field ) {
							$custom_value = isset( $item['taxonomy_fields'][ $custom_field_key ] ) ? $item['taxonomy_fields'][ $custom_field_key ] : '';
							update_term_meta( $term_id, $this->taxonomy_custom_field_meta_key( $custom_field_key ), $this->sanitize_taxonomy_custom_field_value( $custom_value, $custom_field ) );
						}
					}
					$term_ids[] = $term_id;
				}
			}

			return array_values( array_unique( array_filter( $term_ids ) ) );
		}

		private function get_related_service_ids_for_location( $location_id ) {
			$ids = array();
			$location_id = sanitize_key( $location_id );
			foreach ( $this->get_active_services() as $service ) {
				if ( $this->service_matches_location( $service, $location_id ) ) {
					$ids[] = sanitize_key( $service['id'] );
				}
			}
			return array_values( array_unique( array_filter( $ids ) ) );
		}

		private function get_related_staff_ids_for_location( $location_id ) {
			$ids = array();
			$location_id = sanitize_key( $location_id );
			if ( '' === $location_id ) {
				return array();
			}

			/*
			 * A Location -> Team relationship must respect the same Service/Location/Team
			 * matrix used by the booking form. Do not rely only on staff location settings,
			 * because a staff member may be globally allowed at a location but not actually
			 * assigned to any service at that location.
			 */
			foreach ( $this->get_active_staff() as $staff ) {
				if ( empty( $staff['id'] ) ) {
					continue;
				}

				foreach ( $this->get_active_services() as $service ) {
					if ( $this->staff_can_deliver_service_at_location( $service, $staff, $location_id ) ) {
						$ids[] = sanitize_key( $staff['id'] );
						break;
					}
				}
			}

			return array_values( array_unique( array_filter( $ids ) ) );
		}

		private function get_related_service_ids_for_staff( $staff_id ) {
			$ids = array();
			$staff_id = sanitize_key( $staff_id );
			$staff = $this->find_staff( $staff_id );
			foreach ( $this->get_active_services() as $service ) {
				$service_staff_ids = isset( $service['staff_ids'] ) ? array_map( 'sanitize_key', (array) $service['staff_ids'] ) : array();
				if ( ! in_array( $staff_id, $service_staff_ids, true ) ) {
					continue;
				}
				if ( $staff ) {
					$shared = $this->get_shared_locations_for_service_and_staff( $service, $staff );
					if ( empty( $shared ) ) {
						continue;
					}
				}
				$ids[] = sanitize_key( $service['id'] );
			}
			return array_values( array_unique( array_filter( $ids ) ) );
		}

		private function get_related_location_ids_for_staff( $staff_id ) {
			$ids = array();
			$staff_id = sanitize_key( $staff_id );
			$staff = $this->find_staff( $staff_id );
			if ( ! $staff ) {
				return array();
			}

			foreach ( $this->get_active_services() as $service ) {
				foreach ( $this->get_shared_locations_for_service_and_staff( $service, $staff ) as $location ) {
					if ( ! empty( $location['id'] ) ) {
						$ids[] = sanitize_key( $location['id'] );
					}
				}
			}

			return array_values( array_unique( array_filter( $ids ) ) );
		}

		private function sync_elementor_content_record_relationships( $post_id, $post_type, $item ) {
			$post_id = absint( $post_id );
			$item_id = isset( $item['id'] ) ? sanitize_key( $item['id'] ) : '';
			if ( ! $post_id || '' === $item_id ) {
				return;
			}

			$location_ids = array();
			$service_ids  = array();
			$team_ids     = array();

			if ( self::CPT_LOCATION === $post_type ) {
				$location_ids = array( $item_id );
				$service_ids  = $this->get_related_service_ids_for_location( $item_id );
				$team_ids     = $this->get_related_staff_ids_for_location( $item_id );
			} elseif ( self::CPT_SERVICE === $post_type ) {
				$service_ids  = array( $item_id );
				$location_ids = $this->get_effective_location_ids_from_row( $item );
				$team_ids     = isset( $item['staff_ids'] ) ? array_values( array_filter( array_map( 'sanitize_key', (array) $item['staff_ids'] ) ) ) : array();
			} elseif ( self::CPT_TEAM === $post_type ) {
				$team_ids     = array( $item_id );
				/*
				 * Team singles need locations calculated from real service delivery rules,
				 * not just the team's broad location setting. This keeps the Team -> Locations
				 * Loop Grid aligned with the same rules used by the booking form.
				 */
				$location_ids = $this->get_related_location_ids_for_staff( $item_id );
				$service_ids  = $this->get_related_service_ids_for_staff( $item_id );
			}

			$location_ids = array_values( array_unique( array_filter( $location_ids ) ) );
			$service_ids  = array_values( array_unique( array_filter( $service_ids ) ) );
			$team_ids     = array_values( array_unique( array_filter( $team_ids ) ) );

			update_post_meta( $post_id, '_appt_booker_location_ids', $location_ids );
			update_post_meta( $post_id, '_appt_booker_service_ids', $service_ids );
			update_post_meta( $post_id, '_appt_booker_team_ids', $team_ids );

			/* Human-readable relationship meta for Elementor and Filter Builder. */
			$this->update_filterable_name_post_meta( $post_id, 'appt_booker_location_names', $this->get_active_item_names_by_ids( $this->get_active_locations(), $location_ids ) );
			$this->update_filterable_name_post_meta( $post_id, 'appt_booker_service_names', $this->get_active_item_names_by_ids( $this->get_active_services(), $service_ids ) );
			$this->update_filterable_name_post_meta( $post_id, 'appt_booker_team_names', $this->get_active_item_names_by_ids( $this->get_active_staff(), $team_ids ) );

			$location_terms = $this->ensure_relationship_terms_for_items( self::TAX_REL_LOCATION, $this->get_active_locations(), $location_ids );

			$service_terms  = $this->ensure_relationship_terms_for_items( self::TAX_REL_SERVICE, $this->get_active_services(), $service_ids );
			$team_terms     = $this->ensure_relationship_terms_for_items( self::TAX_REL_TEAM, $this->get_active_staff(), $team_ids );

			wp_set_object_terms( $post_id, $location_terms, self::TAX_REL_LOCATION, false );
			wp_set_object_terms( $post_id, $service_terms, self::TAX_REL_SERVICE, false );
			wp_set_object_terms( $post_id, $team_terms, self::TAX_REL_TEAM, false );
			$this->sync_all_assigned_taxonomy_custom_fields_to_post_meta( $post_id );
		}

		private function get_current_elementor_content_defaults() {
			if ( ! is_singular() ) {
				return array();
			}

			$post_id   = get_queried_object_id();
			$post_type = get_post_type( $post_id );
			$item_id   = sanitize_key( get_post_meta( $post_id, '_appt_booker_item_id', true ) );

			if ( '' === $item_id ) {
				return array();
			}

			if ( self::CPT_LOCATION === $post_type ) {
				return array( 'location_id' => $item_id );
			}
			if ( self::CPT_SERVICE === $post_type ) {
				return array( 'service_id' => $item_id );
			}
			if ( self::CPT_TEAM === $post_type ) {
				return array( 'staff_id' => $item_id );
			}

			return array();
		}


		private function default_settings() {
			return array(
				'form_title'            => 'Book an Appointment',
				'form_intro'            => 'Choose a service, date and available time.',
				'success_message'       => 'Your booking has been saved.',
				'booking_prefix'        => 'APPT',
				'email_from_address'    => get_option( 'admin_email' ),
				'show_location_field'   => 1,
				'show_service_field'    => 1,
				'show_staff_field'      => 1,
				'show_address_field'    => 0,
				'show_phone_field'      => 1,
				'label_location'         => 'Location',
				'label_service'          => 'Service',
				'label_staff'            => 'Team Member',
				'label_address'          => 'Address',
				'label_phone'            => 'Phone Number',
				'default_location_id'   => '',
				'default_service_id'    => '',
				'default_staff_id'      => '',
				'notify_admin_booking_made'              => 1,
				'notify_admin_booking_edited'            => 1,
				'notify_admin_booking_cancelled'         => 1,
				'notify_staff_booking_made'              => 1,
				'notify_staff_booking_edited'            => 1,
				'notify_staff_booking_cancelled'         => 1,
				'notify_customer_booking_made'           => 1,
				'notify_customer_booking_edited'         => 1,
				'notify_customer_booking_cancelled'      => 1,
				'notify_admin_booking_rescheduled'           => 1,
				'notify_staff_booking_rescheduled'           => 1,
				'notify_customer_booking_rescheduled'        => 1,
				'notify_staff_consultant_changed'         => 1,
				'notify_customer_consultant_changed'      => 1,
				'reminder_enabled'                        => 1,
				'reminder_hours_before'                   => 24,
				'reschedule_enabled'                         => 1,
				'reschedule_min_hours_notice'                => 1,

				// Front-end form style settings.
				'style_form_layout'                    => 'two_date_last',
				'style_form_title_padding'             => '0 0 12px 0',
				'style_form_title_color'               => '#111111',
				'style_form_title_background'          => '#ffffff',
				'style_form_title_font_size'           => '24px',
				'style_form_title_font_weight'         => '600',
				'style_form_title_letter_spacing'      => '0',
				'style_form_intro_font_size'           => '16px',
				'style_form_intro_font_weight'         => '400',
				'style_form_intro_letter_spacing'      => '0',
				'style_form_border_radius'             => '12px',
				'style_form_border_width'              => '1px',
				'style_form_border_color'              => '#cccccc',
				'style_form_padding'                   => '20px',
				'style_form_column_gap'                => '16px',
				'style_form_row_gap'                   => '16px',
				'style_field_label_padding'            => '0',
				'style_field_label_color'              => '#111111',
				'style_field_label_background'         => 'transparent',
				'style_label_border_radius'            => '0',
				'style_label_border_width'             => '0',
				'style_label_border_color'             => 'transparent',
				'style_field_padding'                  => '10px',
				'style_field_border_radius'            => '4px',
				'style_field_border_width'             => '1px',
				'style_field_border_color'             => '#8c8f94',
				'style_label_font_size'                => '16px',
				'style_label_font_weight'              => '600',
				'style_label_letter_spacing'           => '0',
				'style_field_font_size'                => '16px',
				'style_field_font_weight'              => '400',
				'style_field_letter_spacing'           => '0',
				'style_button_text'                    => 'Book Now',
				'style_button_text_color'              => '#ffffff',
				'style_button_background'              => '#111111',
				'style_button_font_size'               => '16px',
				'style_button_font_weight'             => '600',
				'style_button_letter_spacing'          => '0',
				'style_button_hover_text_color'        => '#ffffff',
				'style_button_hover_background'        => '#333333',
				'style_button_border_radius'           => '8px',
				'style_button_border_width'            => '0',
				'style_button_border_color'            => 'transparent',
				'style_button_padding'                 => '12px 16px',
				'style_button_margin'                  => '16px 0 0 0',
				'style_reset_button_text'              => 'Reset Form',
				'style_reset_button_text_color'        => '#111111',
				'style_reset_button_background'        => '#ffffff',
				'style_reset_button_hover_text_color'  => '#111111',
				'style_reset_button_hover_background'  => '#f3f4f6',
				'style_reset_button_font_size'         => '16px',
				'style_reset_button_font_weight'       => '600',
				'style_reset_button_letter_spacing'    => '0',
				'style_reset_button_border_radius'     => '8px',
				'style_reset_button_border_width'      => '1px',
				'style_reset_button_border_color'      => '#111111',
				'style_reset_button_padding'           => '12px 16px',
				'style_typography_font_family'         => '',

				// Anti-abuse / security (added).
				'security_min_seconds_to_submit'       => 3,
				'security_throttle_seconds'            => 60,
				'security_turnstile_enabled'           => 0,
				'security_turnstile_site_key'          => '',
				'security_turnstile_secret_key'        => '',
				// Customer self-cancellation (added).
				'cancellation_enabled'                 => 1,
				'cancellation_min_hours_notice'        => 1,
				'cancellation_ask_for_reason'          => 1,
				// Shared resource + capacity (added).
				'shared_resource_enabled'              => 1,
				'shared_resource_buffer'               => 15,
				'daily_booking_cap'                    => 0,
				// Communication-method wording (added). Keyed by location id at runtime.
				'comm_method_verbs'                    => array(),
				'comm_method_customer_notes'           => array(),
				// Email footer (added) - free-text, shown at the bottom of all booking emails.
				'email_footer_text'                    => "Titan Jewellery Ltd\nConfidence working with precious metals since 1988 and alternative metals since 2002.\nwww.titanjewellery.co.uk",
				// Customer message + privacy notice (added).
				'customer_message_label'               => 'Anything you would like us to know before the call? (Optional)',
				'booking_privacy_notice'               => "Your details, your name, email address and phone number, are used only to arrange and carry out this booking. We will not add you to any marketing lists, and we will not pass your information to any third party. We will only contact you in connection with this appointment. We remove your email address and phone number from our records after six months, and we keep a minimal record (your name and what was discussed) to support our product guarantees before deleting it entirely.\n\nConsultation calls may be recorded with your consent. When recorded, the call is summarised into anonymised notes covering the topics, questions and advice given. The summary helps us refine our service and identify common questions, and is retained against your booking for the period described above. The original recording is deleted within seven working days. We'll always ask before recording.",
				// Data retention (added). ON by default, and also force-enabled on
				// upgrade by enable_retention_for_existing_installation(). The
				// daily sweep BLANKS customer_email and customer_phone on records
				// older than retention_minimise_months, and PERMANENTLY DELETES
				// records older than retention_delete_months. Both are
				// irreversible. Turn retention_enabled off in Settings if that is
				// not what you want.
				'retention_enabled'                    => 1,
				'retention_minimise_months'            => 6,
				'retention_delete_months'              => 30,
				// Availability rules (added). All Mon-Fri only; never same-day; never
				// next working day; cut-off advances "next working day" after this time.
				// Booking window is "current calendar week + next calendar week" -
				// resets every Friday at the cut-off time. Bank holidays auto-blocked,
				// plus the first working day after any 3+ consecutive non-working days.
				'availability_cutoff_hour'             => 16,
				'availability_weeks_ahead'             => 2,
				'availability_block_after_long_break'  => 1,
				// Anti-abuse (added).
				'rate_limit_per_email_enabled'         => 1,
				'rate_limit_per_email_max'             => 3,
				'rate_limit_per_email_days'            => 30,
				'manual_approval_enabled'              => 0,
				'no_show_safeguard_enabled'            => 1,
				'no_show_threshold'                    => 2,
				'no_show_window_days'                  => 180,
				'no_show_block_days'                   => 365,
				'blocked_emails'                       => '',
				// Content Research export (added).
				'content_research_prompt'              => "I have attached a CSV of anonymised consultation notes from my UK alternative-metals wedding ring business. Each row is one customer call: the service they booked, what topics they ticked as interests, any message they sent before the call, and my structured notes from the conversation (topics discussed, their concerns, what we advised, follow-up needed).\n\nAnalyse the data and identify:\n\n1. Recurring questions or concerns that come up across multiple calls.\n2. Common misconceptions customers seem to have.\n3. Topics that aren't well covered by typical wedding-ring buyer's guides.\n4. Comparisons customers asked us to make (X vs Y).\n5. Unexpected interests I might want to address proactively.\n\nFor each pattern you identify, tell me:\n- Roughly how many calls mentioned it.\n- A representative phrase or framing in customers' own words if useful.\n- Whether it would best suit a blog post, a buyer's guide section, an FAQ entry, or a help page.\n- A suggested working title.\n\nDo not write any content yourself. Just identify what's worth writing. I will write it in my own voice.",
			);
		}

		/**
		 * Anti-abuse helpers (added).
		 *
		 * Provides honeypot detection, minimum form-fill time enforcement,
		 * per-IP throttling, and optional Cloudflare Turnstile verification
		 * for the public booking submit endpoint.
		 */
		/**
		 * Published Cloudflare edge ranges.
		 *
		 * Source: https://www.cloudflare.com/ips-v4 and /ips-v6. These change
		 * rarely, but they do change - the list is filterable so it can be
		 * refreshed without editing the plugin.
		 */
		private function cloudflare_ip_ranges() {
			$ranges = array(
				'173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
				'141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
				'197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
				'104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
				'2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
				'2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
			);

			/**
			 * Filter the CIDR ranges treated as trusted Cloudflare edge nodes.
			 *
			 * @param string[] $ranges CIDR notation, IPv4 and IPv6.
			 */
			return (array) apply_filters( 'tj_appt_booker_cloudflare_ip_ranges', $ranges );
		}

		/**
		 * Is $ip inside $cidr? Handles both IPv4 and IPv6 by comparing the
		 * leading bits of the packed binary representations.
		 */
		private function ip_in_cidr( $ip, $cidr ) {
			if ( false === strpos( $cidr, '/' ) ) {
				return $ip === $cidr;
			}
			list( $subnet, $bits ) = explode( '/', $cidr, 2 );
			$bits = (int) $bits;

			$ip_packed     = @inet_pton( $ip );
			$subnet_packed = @inet_pton( $subnet );
			if ( false === $ip_packed || false === $subnet_packed ) {
				return false;
			}
			// Different address families can never match.
			if ( strlen( $ip_packed ) !== strlen( $subnet_packed ) ) {
				return false;
			}
			if ( $bits < 0 || $bits > strlen( $ip_packed ) * 8 ) {
				return false;
			}

			$whole_bytes = intdiv( $bits, 8 );
			$rest_bits   = $bits % 8;

			if ( $whole_bytes > 0 && 0 !== substr_compare( $ip_packed, substr( $subnet_packed, 0, $whole_bytes ), 0, $whole_bytes ) ) {
				return false;
			}
			if ( 0 === $rest_bits ) {
				return true;
			}
			$mask = ~( ( 1 << ( 8 - $rest_bits ) ) - 1 ) & 0xFF;
			return ( ord( $ip_packed[ $whole_bytes ] ) & $mask ) === ( ord( $subnet_packed[ $whole_bytes ] ) & $mask );
		}

		private function request_is_from_cloudflare( $remote_ip ) {
			if ( '' === $remote_ip || ! filter_var( $remote_ip, FILTER_VALIDATE_IP ) ) {
				return false;
			}
			foreach ( $this->cloudflare_ip_ranges() as $range ) {
				if ( $this->ip_in_cidr( $remote_ip, $range ) ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * The client IP used for anti-abuse throttling.
		 *
		 * CF-Connecting-IP is only honoured when the request genuinely arrived
		 * from a Cloudflare edge node. It is an ordinary client-supplied header:
		 * Cloudflare overwrites it for traffic passing through the edge, but
		 * anything that reaches the origin directly - via the origin IP, a
		 * leaked hostname, a stale DNS record or an unproxied subdomain - can
		 * set it to whatever it likes. Trusting it unconditionally let an
		 * attacker defeat the per-IP submission throttle simply by rotating a
		 * fabricated header value.
		 */
		private function get_client_ip_for_throttle() {
			$remote = isset( $_SERVER['REMOTE_ADDR'] ) ? trim( (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

			if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) && $this->request_is_from_cloudflare( $remote ) ) {
				$forwarded = trim( (string) wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
				if ( '' !== $forwarded && filter_var( $forwarded, FILTER_VALIDATE_IP ) ) {
					return $forwarded;
				}
			}

			return ( '' !== $remote && filter_var( $remote, FILTER_VALIDATE_IP ) ) ? $remote : '';
		}

		private function security_throttle_transient_key( $ip ) {
			return 'rgl_booking_throttle_' . md5( $ip );
		}

		/**
		 * Signature binding a form-render timestamp to this site.
		 *
		 * Stops a bot fabricating its own "form loaded N seconds ago" value to
		 * satisfy the minimum time-to-submit check.
		 */
		private function form_timestamp_signature( $timestamp ) {
			return wp_hash( 'rgl_booking_form_ts|' . absint( $timestamp ) );
		}

		/**
		 * A fresh nonce plus signed render timestamp for the public booking form.
		 *
		 * Served to the front end on page load so that a page coming out of a
		 * CDN, page cache or the browser's back-forward cache replaces the stale
		 * values baked into its HTML. Without this, a cached page older than the
		 * 12-24h nonce lifetime fails check_ajax_referer() and the form silently
		 * stops working with nothing logged anywhere.
		 */
		private function get_public_form_credentials() {
			$now = time();
			return array(
				'nonce'     => wp_create_nonce( 'appt_booker_nonce' ),
				'loadedAt'  => $now,
				'signature' => $this->form_timestamp_signature( $now ),
			);
		}

		/**
		 * Public endpoint issuing fresh form credentials. Deliberately has no
		 * nonce check of its own - it hands out exactly what rendering the page
		 * would have handed out, so requiring a (possibly expired) nonce to
		 * refresh an expired nonce would defeat the purpose.
		 */
		public function ajax_refresh_form_token() {
			wp_send_json_success( $this->get_public_form_credentials() );
		}

		/**
		 * Returns true if the request should be blocked.
		 * Sets $reason to a customer-safe message describing why.
		 */
		/**
		 * True if the current request is from a logged-in user who can
		 * manage options (an admin). Used to bypass customer-facing
		 * anti-abuse so admins can take real bookings on the public form
		 * by phone, and so testing isn't caught by the IP throttle.
		 */
		private function current_request_is_admin() {
			return is_user_logged_in() && current_user_can( 'manage_options' );
		}

		private function request_is_abusive( &$reason ) {
			// Logged-in admins always bypass customer-facing protections so they
			// can use the public form to book real customers in by phone, and
			// so testing doesn't get caught by IP throttle / honeypot etc.
			if ( $this->current_request_is_admin() ) {
				return false;
			}

			$settings = $this->get_settings();

			// 1. Honeypot — a hidden field real users never fill in.
			if ( isset( $_POST['rgl_booking_company'] ) && '' !== trim( (string) wp_unslash( $_POST['rgl_booking_company'] ) ) ) {
				$reason = 'Your submission was blocked. If you believe this is in error, please contact us directly.';
				return true;
			}

			// 2. Minimum time-to-submit. The form sets a hidden timestamp on render;
			// real humans take more than a few seconds to fill the form.
			//
			// This must fail CLOSED. The old version only ran the comparison when
			// the field was present and non-zero, so any scripted submission that
			// simply omitted rgl_booking_form_loaded_at (or sent 0) skipped the
			// check entirely. The timestamp is now signed as well, so a bot cannot
			// mint a plausible "loaded a minute ago" value of its own.
			$min_seconds = max( 0, absint( isset( $settings['security_min_seconds_to_submit'] ) ? $settings['security_min_seconds_to_submit'] : 3 ) );
			if ( $min_seconds > 0 ) {
				$loaded_at = isset( $_POST['rgl_booking_form_loaded_at'] ) ? absint( wp_unslash( $_POST['rgl_booking_form_loaded_at'] ) ) : 0;
				$signature = isset( $_POST['rgl_booking_form_sig'] ) ? sanitize_text_field( wp_unslash( $_POST['rgl_booking_form_sig'] ) ) : '';

				if ( $loaded_at <= 0 || '' === $signature || ! hash_equals( $this->form_timestamp_signature( $loaded_at ), $signature ) ) {
					$reason = 'Your session has expired. Please reload the page and try again.';
					return true;
				}

				$elapsed = time() - $loaded_at;
				if ( $elapsed < $min_seconds ) {
					$reason = 'Please take a moment to review your booking before submitting.';
					return true;
				}
				// Bound how long one harvested timestamp can be replayed. Generous,
				// because a page served from cache may carry an old value and the
				// front end refreshes it on load anyway.
				if ( $elapsed > 30 * DAY_IN_SECONDS ) {
					$reason = 'Your session has expired. Please reload the page and try again.';
					return true;
				}
			}

			// 3. Per-IP throttle via transient.
			$throttle_seconds = max( 0, absint( isset( $settings['security_throttle_seconds'] ) ? $settings['security_throttle_seconds'] : 60 ) );
			if ( $throttle_seconds > 0 ) {
				$ip = $this->get_client_ip_for_throttle();
				if ( '' !== $ip ) {
					$key  = $this->security_throttle_transient_key( $ip );
					$last = get_transient( $key );
					if ( false !== $last ) {
						$reason = 'You have submitted a booking very recently. Please wait a moment and try again.';
						return true;
					}
					set_transient( $key, time(), $throttle_seconds );
				}
			}

			// 4. Optional Cloudflare Turnstile verification.
			if ( ! empty( $settings['security_turnstile_enabled'] ) && ! empty( $settings['security_turnstile_secret_key'] ) ) {
				$token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';
				if ( '' === $token || ! $this->verify_turnstile_token( $token, $settings['security_turnstile_secret_key'] ) ) {
					$reason = 'Security challenge failed. Please reload the page and try again.';
					return true;
				}
			}

			return false;
		}

		private function verify_turnstile_token( $token, $secret ) {
			$body = array(
				'secret'   => (string) $secret,
				'response' => (string) $token,
			);
			$ip = $this->get_client_ip_for_throttle();
			if ( '' !== $ip ) {
				$body['remoteip'] = $ip;
			}
			$response = wp_remote_post(
				'https://challenges.cloudflare.com/turnstile/v0/siteverify',
				array(
					'timeout' => 5,
					'body'    => $body,
				)
			);
			if ( is_wp_error( $response ) ) {
				return false;
			}
			$code = (int) wp_remote_retrieve_response_code( $response );
			if ( 200 !== $code ) {
				return false;
			}
			$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
			return is_array( $decoded ) && ! empty( $decoded['success'] );
		}

		/**
		 * One-time upgrade cleanup for sites that previously used the combined
		 * booking/payment build. Existing booking-table columns are left alone to
		 * avoid a destructive schema migration, but all stored configuration and
		 * credentials for the removed subsystem are deleted.
		 */
		private function remove_legacy_payment_configuration() {
			if ( '1' === (string) get_option( self::OPTION_NO_PAYMENT_MIGRATED, '' ) ) {
				return;
			}

			$legacy_keys = array(
				'payment_mode', 'payment_total', 'payment_currency',
				'deposit_type', 'deposit_percent', 'deposit_value',
				'paypal_email', 'paypal_return_url', 'paypal_enabled', 'paypal_sandbox',
			);

			$services = get_option( self::OPTION_SERVICES, array() );
			if ( is_array( $services ) ) {
				$services_changed = false;
				foreach ( $services as &$service ) {
					if ( ! is_array( $service ) ) {
						continue;
					}
					foreach ( $legacy_keys as $legacy_key ) {
						if ( array_key_exists( $legacy_key, $service ) ) {
							unset( $service[ $legacy_key ] );
							$services_changed = true;
						}
					}
					if ( ! empty( $service['staff_rules'] ) && is_array( $service['staff_rules'] ) ) {
						foreach ( $service['staff_rules'] as &$staff_rule ) {
							if ( ! is_array( $staff_rule ) ) {
								continue;
							}
							foreach ( $legacy_keys as $legacy_key ) {
								if ( array_key_exists( $legacy_key, $staff_rule ) ) {
									unset( $staff_rule[ $legacy_key ] );
									$services_changed = true;
								}
							}
						}
						unset( $staff_rule );
					}
				}
				unset( $service );
				if ( $services_changed ) {
					update_option( self::OPTION_SERVICES, $services );
				}
			}

			$settings = get_option( self::OPTION_SETTINGS, array() );
			if ( is_array( $settings ) ) {
				$settings_changed = false;
				foreach ( array_keys( $settings ) as $setting_key ) {
					if ( false !== strpos( $setting_key, 'payment' ) || false !== strpos( $setting_key, 'paypal' ) || false !== strpos( $setting_key, 'deposit' ) ) {
						unset( $settings[ $setting_key ] );
						$settings_changed = true;
					}
				}
				if ( $settings_changed ) {
					update_option( self::OPTION_SETTINGS, $settings );
				}
			}

			update_option( self::OPTION_NO_PAYMENT_MIGRATED, '1', false );
		}


		/**
		 * One-time upgrade for this pre-launch installation: enable the automatic
		 * retention policy using the existing 6-month minimisation and 30-month
		 * deletion defaults. The migration runs once, so an administrator can still
		 * change or disable the setting deliberately afterwards.
		 */
		private function enable_retention_for_existing_installation() {
			if ( '1' === (string) get_option( self::OPTION_RETENTION_ENABLED_MIGRATED, '' ) ) {
				return;
			}

			$settings = get_option( self::OPTION_SETTINGS, array() );
			$settings = is_array( $settings ) ? $settings : array();

			$minimise_months = max( 1, absint( isset( $settings['retention_minimise_months'] ) ? $settings['retention_minimise_months'] : 6 ) );
			$delete_months   = max( $minimise_months + 1, absint( isset( $settings['retention_delete_months'] ) ? $settings['retention_delete_months'] : 30 ) );

			$settings['retention_enabled']         = 1;
			$settings['retention_minimise_months'] = $minimise_months;
			$settings['retention_delete_months']   = $delete_months;

			update_option( self::OPTION_SETTINGS, $settings );
			update_option( self::OPTION_RETENTION_ENABLED_MIGRATED, '1', false );
		}

		/**
		 * Move legacy service/location/team schedules into one service/team
		 * schedule shared by every allowed contact method.
		 */
		private function migrate_shared_contact_method_availability() {
			if ( '1' === (string) get_option( self::OPTION_SHARED_CONTACT_AVAILABILITY_MIGRATED, '' ) ) {
				return;
			}
			$services = get_option( self::OPTION_SERVICES, array() );
			if ( ! is_array( $services ) ) {
				update_option( self::OPTION_SHARED_CONTACT_AVAILABILITY_MIGRATED, '1', false );
				return;
			}
			$changed = false;
			foreach ( $services as &$service ) {
				if ( ! is_array( $service ) ) { continue; }
				$staff_ids = isset( $service['staff_ids'] ) && is_array( $service['staff_ids'] ) ? array_values( array_unique( array_filter( array_map( 'sanitize_key', $service['staff_ids'] ) ) ) ) : array();
				if ( empty( $staff_ids ) ) { continue; }
				if ( empty( $service['staff_rules'] ) || ! is_array( $service['staff_rules'] ) ) { $service['staff_rules'] = array(); }
				foreach ( $staff_ids as $staff_id ) {
					if ( ! empty( $service['staff_rules'][ $staff_id ]['hours'] ) && is_array( $service['staff_rules'][ $staff_id ]['hours'] ) ) { continue; }
					$legacy_hours = $this->get_legacy_location_staff_hours_for_service( $service, $staff_id );
					if ( false === $legacy_hours ) {
						$staff = $this->find_staff( $staff_id );
						$legacy_hours = $staff ? $this->derive_shared_hours_from_service_and_staff( $service, $staff ) : false;
					}
					if ( false === $legacy_hours ) { continue; }
					if ( empty( $service['staff_rules'][ $staff_id ] ) || ! is_array( $service['staff_rules'][ $staff_id ] ) ) { $service['staff_rules'][ $staff_id ] = array(); }
					$service['staff_rules'][ $staff_id ]['hours'] = $legacy_hours;
					$changed = true;
				}
			}
			unset( $service );
			if ( $changed ) { update_option( self::OPTION_SERVICES, $services ); }
			update_option( self::OPTION_SHARED_CONTACT_AVAILABILITY_MIGRATED, '1', false );
		}

		private function hours_schedule_has_enabled_day( $hours ) {
			if ( ! is_array( $hours ) ) { return false; }
			foreach ( array_keys( $this->days_map() ) as $day_key ) {
				if ( ! empty( $hours[ $day_key ]['enabled'] ) ) { return true; }
			}
			return false;
		}

		private function get_legacy_location_staff_hours_for_service( $service, $staff_id ) {
			$staff_id = sanitize_key( $staff_id );
			if ( '' === $staff_id || empty( $service['location_staff_hours'] ) || ! is_array( $service['location_staff_hours'] ) ) { return false; }
			$location_ids = isset( $service['location_ids'] ) && is_array( $service['location_ids'] ) ? array_values( array_unique( array_filter( array_map( 'sanitize_key', $service['location_ids'] ) ) ) ) : array_keys( $service['location_staff_hours'] );
			foreach ( array_keys( $service['location_staff_hours'] ) as $location_id ) {
				$location_id = sanitize_key( $location_id );
				if ( '' !== $location_id && ! in_array( $location_id, $location_ids, true ) ) { $location_ids[] = $location_id; }
			}
			$fallback = false;
			foreach ( $location_ids as $location_id ) {
				if ( $this->service_has_location_staff_matrix( $service ) ) {
					$allowed = isset( $service['location_staff_ids'][ $location_id ] ) && is_array( $service['location_staff_ids'][ $location_id ] ) ? array_map( 'sanitize_key', $service['location_staff_ids'][ $location_id ] ) : array();
					if ( ! in_array( $staff_id, $allowed, true ) ) { continue; }
				}
				if ( empty( $service['location_staff_hours'][ $location_id ][ $staff_id ] ) || ! is_array( $service['location_staff_hours'][ $location_id ][ $staff_id ] ) ) { continue; }
				$hours = $this->sanitize_hours_rows( $service['location_staff_hours'][ $location_id ][ $staff_id ] );
				if ( false === $fallback ) { $fallback = $hours; }
				if ( $this->hours_schedule_has_enabled_day( $hours ) ) { return $hours; }
			}
			return $fallback;
		}

		private function get_shared_staff_hours_for_service( $service, $staff_id ) {
			$staff_id = sanitize_key( $staff_id );
			if ( '' === $staff_id || ! is_array( $service ) ) { return false; }
			if ( ! empty( $service['staff_rules'][ $staff_id ]['hours'] ) && is_array( $service['staff_rules'][ $staff_id ]['hours'] ) ) {
				return $this->sanitize_hours_rows( $service['staff_rules'][ $staff_id ]['hours'] );
			}
			return $this->get_legacy_location_staff_hours_for_service( $service, $staff_id );
		}


		private function derive_shared_hours_from_service_and_staff( $service, $staff ) {
			if ( ! is_array( $service ) || ! is_array( $staff ) ) { return false; }
			$service_hours = isset( $service['hours'] ) && is_array( $service['hours'] ) ? $service['hours'] : $this->default_hours();
			$staff_hours   = isset( $staff['hours'] ) && is_array( $staff['hours'] ) ? $staff['hours'] : $this->default_hours();
			$out = array();
			foreach ( array_keys( $this->days_map() ) as $day_key ) {
				$service_windows = $this->hours_row_to_windows( isset( $service_hours[ $day_key ] ) ? $service_hours[ $day_key ] : array() );
				$staff_windows   = $this->hours_row_to_windows( isset( $staff_hours[ $day_key ] ) ? $staff_hours[ $day_key ] : array() );
				$windows         = $this->intersect_availability_windows( $service_windows, $staff_windows );
				usort( $windows, static function( $a, $b ) { return absint( $a['start'] ) <=> absint( $b['start'] ); } );
				if ( empty( $windows ) ) {
					$out[ $day_key ] = array( 'enabled' => 0, 'start' => '09:00', 'end' => '17:00', 'break_enabled' => 0, 'break_start' => '12:00', 'break_end' => '13:00' );
					continue;
				}
				$first = reset( $windows );
				$last  = end( $windows );
				$row = array(
					'enabled'       => 1,
					'start'         => $this->minutes_to_time( absint( $first['start'] ) ),
					'end'           => $this->minutes_to_time( absint( $last['end'] ) ),
					'break_enabled' => count( $windows ) > 1 ? 1 : 0,
					'break_start'   => count( $windows ) > 1 ? $this->minutes_to_time( absint( $windows[0]['end'] ) ) : '12:00',
					'break_end'     => count( $windows ) > 1 ? $this->minutes_to_time( absint( $windows[1]['start'] ) ) : '13:00',
				);
				$out[ $day_key ] = $row;
			}
			return $this->sanitize_hours_rows( $out );
		}

		private function get_admin_shared_hours_for_service_staff( $service, $staff ) {
			if ( ! is_array( $staff ) || empty( $staff['id'] ) ) { return $this->default_hours(); }
			$shared = $this->get_shared_staff_hours_for_service( $service, $staff['id'] );
			if ( is_array( $shared ) && ! empty( $shared ) ) { return $shared; }
			$derived = $this->derive_shared_hours_from_service_and_staff( $service, $staff );
			return is_array( $derived ) && ! empty( $derived ) ? $derived : $this->default_hours();
		}

		private function maybe_upgrade_schema() {
			$stored_version = (string) get_option( self::OPTION_DB_VERSION, '' );

			// Fast path: the schema is already at the current version, so there
			// is nothing to add. Returning here avoids four SHOW COLUMNS probes
			// on every admin page, cron run, AJAX call and booking-form render.
			// install_schema() is what advances the stored version, and it always
			// runs the ensure_* adds itself, so skipping them here is safe.
			if ( version_compare( $stored_version, self::VERSION, '>=' ) && $this->table_exists() ) {
				return;
			}

			if ( ! $this->table_exists() || version_compare( $stored_version, self::VERSION, '<' ) ) {
				$this->install_schema();
				return; // install_schema() has already run every ensure_* add.
			}

			// Idempotent column adds for additive upgrades.
			$this->ensure_booking_quantity_column();
			$this->ensure_customer_interests_column();
			$this->ensure_cancellation_reason_column();
			$this->ensure_reminder_sent_column();
		}

		private function is_field_enabled( $settings, $key, $default = true ) {
			if ( ! is_array( $settings ) ) {
				return (bool) $default;
			}
			if ( ! array_key_exists( $key, $settings ) ) {
				return (bool) $default;
			}
			return ! empty( $settings[ $key ] );
		}

		private function get_booking_prefix() {
			$settings = $this->get_settings();
			$prefix = isset( $settings['booking_prefix'] ) ? strtoupper( preg_replace( '/[^A-Z0-9-]/', '', (string) $settings['booking_prefix'] ) ) : 'APPT';
			return '' !== $prefix ? $prefix : 'APPT';
		}

		private function generate_booking_reference() {
			return $this->get_booking_prefix() . '-' . strtoupper( wp_generate_password( 8, false, false ) );
		}
		private function get_structured_note_fields() {
			return array(
				'topics'    => 'Topics discussed',
				'concerns'  => 'Their concerns',
				'advice'    => 'What we advised',
				'followup'  => 'Follow-up needed',
				'general'   => 'General notes',
			);
		}

		private function parse_structured_admin_notes( $raw ) {
			$raw    = (string) $raw;
			$fields = $this->get_structured_note_fields();
			$result = array_fill_keys( array_keys( $fields ), '' );
			if ( '' === trim( $raw ) ) {
				return $result;
			}
			// Defensive: a few old records were saved with a raw numeric value
			// in admin_notes (e.g. "0.000000") because of an earlier rendering
			// bug. Treat any value that is purely a zero-valued number as empty
			// so it doesn't pollute the edit screen or the export.
			if ( is_numeric( trim( $raw ) ) && 0.0 === (float) $raw ) {
				return $result;
			}

			// Try to detect the labelled format. A labelled block looks like:
			//   Topics discussed:
			//   ... text ...
			//
			//   Their concerns:
			//   ... text ...
			$labels_pattern = array();
			foreach ( $fields as $key => $label ) {
				$labels_pattern[ $key ] = preg_quote( $label, '/' );
			}
			$any_label = '(' . implode( '|', $labels_pattern ) . ')\s*:';

			if ( ! preg_match( '/^\s*(' . implode( '|', $labels_pattern ) . ')\s*:/m', $raw ) ) {
				// No labels found - assume legacy free-text, put it all in "general".
				$result['general'] = trim( $raw );
				return $result;
			}

			// Split on label headings, keeping which label each section follows.
			$parts = preg_split( '/^\s*(' . implode( '|', $labels_pattern ) . ')\s*:\s*\R?/m', $raw, -1, PREG_SPLIT_DELIM_CAPTURE );
			// $parts is [pre-text, label1, content1, label2, content2, ...]
			$pre = array_shift( $parts );
			if ( '' !== trim( (string) $pre ) ) {
				$result['general'] = trim( (string) $pre );
			}
			while ( count( $parts ) >= 2 ) {
				$label_text = array_shift( $parts );
				$content    = array_shift( $parts );
				$key        = array_search( $label_text, $fields, true );
				if ( false !== $key ) {
					$existing = $result[ $key ];
					$content  = rtrim( (string) $content );
					$result[ $key ] = '' === $existing ? $content : $existing . "\n\n" . $content;
				}
			}
			return $result;
		}

		private function build_structured_admin_notes( $values ) {
			$fields = $this->get_structured_note_fields();
			$out    = array();
			foreach ( $fields as $key => $label ) {
				$value = isset( $values[ $key ] ) ? trim( (string) $values[ $key ] ) : '';
				if ( '' !== $value ) {
					$out[] = $label . ':' . "\n" . $value;
				}
			}
			return implode( "\n\n", $out );
		}

		/**
		 * Decide how to build admin_notes from a POST payload.
		 *
		 * Prefers the plain "admin_notes" textarea (the current UI). If that
		 * field is missing from the payload entirely, falls back to the older
		 * structured fields (admin_notes_topics / _concerns / etc.) which were
		 * used by a previous version of the admin UI - this means any cached
		 * old form on a customer's screen continues to save correctly.
		 */
		private function sanitize_admin_notes_input( $source ) {
			if ( ! is_array( $source ) ) {
				return '';
			}
			// Current path: plain single textarea wins when present.
			if ( isset( $source['admin_notes'] ) ) {
				return sanitize_textarea_field( wp_unslash( (string) $source['admin_notes'] ) );
			}
			// Legacy fallback: assemble from any structured fields that may exist.
			$fields = $this->get_structured_note_fields();
			$has_structured = false;
			$values = array();
			foreach ( $fields as $key => $label ) {
				$post_key = 'admin_notes_' . $key;
				if ( isset( $source[ $post_key ] ) ) {
					$has_structured = true;
					$values[ $key ] = sanitize_textarea_field( wp_unslash( (string) $source[ $post_key ] ) );
				}
			}
			if ( $has_structured ) {
				return $this->build_structured_admin_notes( $values );
			}
			return '';
		}
		private function get_booking_status_label( $status ) {
			$status = strtolower( trim( (string) $status ) );
			$labels = array(
				'confirmed'        => 'Confirmed',
				'held'             => 'Held',
				'cancelled'        => 'Cancelled',
				'pending_approval' => 'Pending approval',
				'completed'        => 'Completed',
				'no_show'          => 'No-show',
			);
			if ( isset( $labels[ $status ] ) ) {
				return $labels[ $status ];
			}
			return ucfirst( str_replace( '_', ' ', $status ) );
		}

		private function get_booking_access_token_for_booking( $booking ) {
			if ( ! $booking || empty( $booking->id ) ) { return ''; }
			return wp_hash(
				'rgl_booking_access|' .
				(int) $booking->id . '|' .
				(string) $booking->booking_reference . '|' .
				(string) $booking->customer_email
			);
		}

		private function get_cancellation_token_for_booking( $booking ) {
			if ( ! $booking || empty( $booking->id ) ) { return ''; }
			return wp_hash( 'rgl_cancel|' . (int) $booking->id . '|' . (string) $booking->booking_reference . '|' . (string) $booking->customer_email );
		}

		/**
		 * Approval action token + URL (added).
		 *
		 * One-click GET endpoint for admin to approve or reject a
		 * pending-approval booking from the notification email. The token
		 * is bound to the booking id + reference + action so an approve
		 * link cannot be reused to reject and vice versa.
		 */
		private function get_approval_token_for_booking( $booking, $action ) {
			if ( ! $booking || empty( $booking->id ) ) { return ''; }
			$action = ( 'approve' === $action ) ? 'approve' : 'reject';
			return wp_hash( 'rgl_approve|' . (int) $booking->id . '|' . (string) $booking->booking_reference . '|' . $action );
		}

		private function get_approval_action_url( $booking, $action ) {
			if ( ! $booking || empty( $booking->id ) ) { return ''; }
			$action = ( 'approve' === $action ) ? 'approve' : 'reject';
			return add_query_arg(
				array(
					'rgl_booking_approve' => 1,
					'booking_id'          => (int) $booking->id,
					'action'              => $action,
					'token'               => $this->get_approval_token_for_booking( $booking, $action ),
				),
				home_url( '/' )
			);
		}

		private function get_cancellation_url( $booking ) {
			if ( ! $booking || empty( $booking->id ) ) { return ''; }
			$settings = $this->get_settings();
			if ( empty( $settings['cancellation_enabled'] ) ) { return ''; }
			return add_query_arg(
				array(
					'rgl_booking_cancel' => 1,
					'booking_id'         => (int) $booking->id,
					'token'              => $this->get_cancellation_token_for_booking( $booking ),
				),
				home_url( '/' )
			);
		}


		private function get_reschedule_token_for_booking( $booking ) {
			if ( ! $booking || empty( $booking->id ) ) { return ''; }
			return wp_hash( 'rgl_reschedule|' . (int) $booking->id . '|' . (string) $booking->booking_reference . '|' . (string) $booking->customer_email . '|' . (string) $booking->booking_date . '|' . (string) $booking->booking_time );
		}

		private function get_reschedule_url( $booking ) {
			if ( ! $booking || empty( $booking->id ) ) { return ''; }
			$settings = $this->get_settings();
			if ( empty( $settings['reschedule_enabled'] ) ) { return ''; }
			return add_query_arg(
				array(
					'rgl_booking_reschedule' => 1,
					'booking_id'             => (int) $booking->id,
					'token'                  => $this->get_reschedule_token_for_booking( $booking ),
				),
				home_url( '/' )
			);
		}

		private function booking_is_self_reschedulable( $booking, &$reason ) {
			$reason = '';
			$settings = $this->get_settings();
			if ( empty( $settings['reschedule_enabled'] ) ) {
				$reason = 'Online appointment changes are not available. Please contact us for help.';
				return false;
			}
			if ( ! $booking || empty( $booking->id ) ) {
				$reason = 'Booking not found.';
				return false;
			}
			if ( ! isset( $booking->booking_status ) || self::STATUS_CONFIRMED !== (string) $booking->booking_status ) {
				$reason = 'This appointment can no longer be changed online. Please contact us for help.';
				return false;
			}
			if ( empty( $booking->customer_email ) || ! is_email( $booking->customer_email ) ) {
				$reason = 'This appointment cannot be changed online. Please contact us for help.';
				return false;
			}
			$start_ts = $this->get_booking_start_timestamp( $booking );
			if ( $start_ts <= current_datetime()->getTimestamp() ) {
				$reason = 'This appointment has already started or passed. Please contact us for help.';
				return false;
			}
			$min_hours = max( 0, absint( isset( $settings['reschedule_min_hours_notice'] ) ? $settings['reschedule_min_hours_notice'] : 1 ) );
			if ( $min_hours > 0 ) {
				if ( current_datetime()->getTimestamp() > ( $start_ts - ( $min_hours * HOUR_IN_SECONDS ) ) ) {
					$reason = sprintf( 'Online appointment changes close %d hour%s before the appointment. Please contact us for help.', $min_hours, 1 === $min_hours ? '' : 's' );
					return false;
				}
			}
			return true;
		}

		/**
		 * Whether the given booking is currently eligible for self-cancellation.
		 * Returns true/false; sets $reason to a customer-safe message when false.
		 */
		private function booking_is_self_cancellable( $booking, &$reason ) {
			$reason = '';
			$settings = $this->get_settings();
			if ( empty( $settings['cancellation_enabled'] ) ) {
				$reason = 'Online cancellation is not available. Please contact us to cancel.';
				return false;
			}
			if ( ! $booking || empty( $booking->id ) ) {
				$reason = 'Booking not found.';
				return false;
			}
			$status = isset( $booking->booking_status ) ? (string) $booking->booking_status : '';
			if ( ! in_array( $status, array( self::STATUS_CONFIRMED, self::STATUS_PENDING_APPROVAL ), true ) ) {
				$reason = self::STATUS_CANCELLED === $status
					? 'This booking has already been cancelled.'
					: 'This appointment can no longer be cancelled online. Please contact us for help.';
				return false;
			}
			$start_ts = $this->get_booking_start_timestamp( $booking );
			if ( $start_ts <= current_datetime()->getTimestamp() ) {
				$reason = 'This appointment has already started or passed. Please contact us for help.';
				return false;
			}
			$min_hours = max( 0, absint( isset( $settings['cancellation_min_hours_notice'] ) ? $settings['cancellation_min_hours_notice'] : 1 ) );
			if ( $min_hours > 0 ) {
				$cutoff_ts = $start_ts - ( $min_hours * HOUR_IN_SECONDS );
					if ( current_datetime()->getTimestamp() > $cutoff_ts ) {
						$reason = sprintf( 'Online cancellation closes %d hour%s before the appointment. Please contact us to cancel.', $min_hours, 1 === $min_hours ? '' : 's' );
					return false;
				}
			}
			return true;
		}

		private function get_booking_start_timestamp( $booking ) {
			if ( ! $booking ) { return 0; }
			$date = isset( $booking->booking_date ) ? (string) $booking->booking_date : '';
			$time = isset( $booking->booking_time ) ? (string) $booking->booking_time : '';
			if ( '' === $date || '' === $time ) { return 0; }
			try {
				$start = new DateTimeImmutable( $date . ' ' . $time, wp_timezone() );
				return $start->getTimestamp();
			} catch ( Exception $e ) {
				return 0;
			}
		}

		/**
		 * Completed and No-show are attendance outcomes, not advance statuses.
		 * They may only be applied once the appointment start time has arrived.
		 */
		private function validate_outcome_status_timing( $status, $booking_date, $booking_time ) {
			$status = sanitize_key( (string) $status );
			if ( ! in_array( $status, array( self::STATUS_COMPLETED, self::STATUS_NO_SHOW ), true ) ) {
				return true;
			}

			$booking = (object) array(
				'booking_date' => sanitize_text_field( (string) $booking_date ),
				'booking_time' => sanitize_text_field( (string) $booking_time ),
			);
			$start_ts = $this->get_booking_start_timestamp( $booking );
			if ( $start_ts <= 0 ) {
				return new WP_Error( 'invalid_outcome_time', 'The appointment date or time is invalid.' );
			}
			if ( $start_ts > current_datetime()->getTimestamp() ) {
				return new WP_Error( 'future_outcome_status', 'Completed and No-show can only be selected after the appointment has started.' );
			}

			return true;
		}

		/**
		 * Communication-method wording (added).
		 *
		 * Each location (Phone, WhatsApp call, WhatsApp chat, Zoom) can have a
		 * verb phrase used in customer emails, e.g. "will call you" or
		 * "will message you on WhatsApp". Stored keyed by location id.
		 */
		private function sanitize_comm_method_verbs( $raw ) {
			$clean = array();
			if ( ! is_array( $raw ) ) {
				return $clean;
			}
			foreach ( $raw as $location_id => $verb ) {
				$location_id = sanitize_key( $location_id );
				$verb        = sanitize_text_field( $verb );
				if ( '' !== $location_id && '' !== $verb ) {
					$clean[ $location_id ] = $verb;
				}
			}
			return $clean;
		}

		private function get_comm_method_verb( $location_id, $fallback = 'will contact you' ) {
			$settings = $this->get_settings();
			$verbs    = isset( $settings['comm_method_verbs'] ) && is_array( $settings['comm_method_verbs'] ) ? $settings['comm_method_verbs'] : array();
			$location_id = sanitize_key( (string) $location_id );
			if ( '' !== $location_id && ! empty( $verbs[ $location_id ] ) ) {
				return (string) $verbs[ $location_id ];
			}
			return $fallback;
		}


		private function sanitize_comm_method_customer_notes( $raw ) {
			$clean = array();
			if ( ! is_array( $raw ) ) { return $clean; }
			foreach ( $raw as $location_id => $note ) {
				$location_id = sanitize_key( $location_id );
				$note = sanitize_textarea_field( wp_unslash( $note ) );
				if ( '' !== $location_id && '' !== trim( $note ) ) { $clean[ $location_id ] = trim( $note ); }
			}
			return $clean;
		}

		private function get_comm_method_customer_note( $location_id ) {
			$settings = $this->get_settings();
			$notes = isset( $settings['comm_method_customer_notes'] ) && is_array( $settings['comm_method_customer_notes'] ) ? $settings['comm_method_customer_notes'] : array();
			$location_id = sanitize_key( (string) $location_id );
			if ( '' !== $location_id && ! empty( $notes[ $location_id ] ) ) { return trim( (string) $notes[ $location_id ] ); }
			$location = '' !== $location_id ? $this->find_location( $location_id ) : false;
			$name = $location && ! empty( $location['name'] ) ? strtolower( (string) $location['name'] ) : '';
			if ( '' !== $name && false !== strpos( $name, 'phone' ) && false === strpos( $name, 'whatsapp' ) ) {
				return 'Your call will come from a withheld number.';
			}
			return '';
		}

		/**
		 * Turn plain text into HTML with clickable links (added).
		 *
		 * Escapes the text first, then linkifies full URLs, bare www. domains
		 * and email addresses. Because escaping happens before linkifying, the
		 * input itself can never inject HTML - only the links this method
		 * builds are added. Used for the email footer and the email body
		 * sections (customer message, notes). $link_colour controls the link
		 * colour so it can match the surrounding text.
		 */
		private function linkify_text( $text, $link_colour = '#2b2b2b' ) {
			$safe = esc_html( (string) $text );
			$style = ' style="color:' . esc_attr( $link_colour ) . ';text-decoration:underline;"';
			// Full URLs (http/https).
			$safe = preg_replace_callback(
				'#(https?://[^\s<]+)#i',
				function ( $m ) use ( $style ) {
					return '<a href="' . esc_url( $m[1] ) . '"' . $style . '>' . esc_html( $m[1] ) . '</a>';
				},
				$safe
			);
			// Bare domains beginning with www.
			$safe = preg_replace_callback(
				'#(^|\s)(www\.[^\s<]+)#i',
				function ( $m ) use ( $style ) {
					return $m[1] . '<a href="' . esc_url( 'https://' . $m[2] ) . '"' . $style . '>' . esc_html( $m[2] ) . '</a>';
				},
				$safe
			);
			// Email addresses.
			$safe = preg_replace_callback(
				'#([A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,})#',
				function ( $m ) use ( $style ) {
					return '<a href="mailto:' . esc_attr( $m[1] ) . '"' . $style . '>' . esc_html( $m[1] ) . '</a>';
				},
				$safe
			);
			return $safe;
		}

		public function handle_cancellation_request() {
			if ( empty( $_GET['rgl_booking_cancel'] ) ) { return; }

			$settings = $this->get_settings();
			if ( empty( $settings['cancellation_enabled'] ) ) {
				return;
			}

			$booking_id = isset( $_GET['booking_id'] ) ? absint( $_GET['booking_id'] ) : 0;
			$token      = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
			$booking    = $booking_id ? $this->get_booking_by_id( $booking_id ) : false;

			if ( ! $booking || '' === $token || ! hash_equals( $this->get_cancellation_token_for_booking( $booking ), $token ) ) {
				$this->render_cancellation_page( false, 'This cancellation link is invalid or has expired.', null );
				return;
			}

			// Process the cancellation if the form was submitted.
			if ( isset( $_POST['rgl_booking_cancel_confirm'] ) ) {
				if ( ! isset( $_POST['rgl_booking_cancel_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rgl_booking_cancel_nonce'] ) ), 'rgl_booking_cancel_' . (int) $booking->id ) ) {
					$this->render_cancellation_page( false, 'Security check failed. Please reload the page and try again.', $booking );
					return;
				}
				$elig_reason = '';
				if ( ! $this->booking_is_self_cancellable( $booking, $elig_reason ) ) {
					$this->render_cancellation_page( false, $elig_reason, $booking );
					return;
				}
				$reason = isset( $_POST['cancellation_reason'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cancellation_reason'] ) ) : '';
				$this->ensure_cancellation_reason_column();
				global $wpdb;
				$wpdb->update(
					$this->table,
					array(
						'booking_status'      => self::STATUS_CANCELLED,
						'cancellation_reason' => $reason,
					),
					array( 'id' => (int) $booking->id ),
					array( '%s', '%s' ),
					array( '%d' )
				);
				$updated = $this->get_booking_by_id( (int) $booking->id );
				if ( $updated ) {
					$this->send_booking_notifications( 'booking_cancelled', $updated );
				}
				$this->render_cancellation_page( true, 'Your booking has been cancelled. We have sent a confirmation by email.', $updated );
				return;
			}

			// Otherwise show the confirm-cancel page.
			$elig_reason = '';
			$eligible    = $this->booking_is_self_cancellable( $booking, $elig_reason );
			$this->render_cancellation_page( null, $eligible ? '' : $elig_reason, $booking, $eligible );
		}


		public function ajax_reschedule_slots() {
			check_ajax_referer( 'appt_booker_nonce', 'nonce' );
			$booking_id = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0;
			$token      = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
			$date       = isset( $_POST['booking_date'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_date'] ) ) : '';
			$booking    = $booking_id ? $this->get_booking_by_id( $booking_id ) : false;
			if ( ! $booking || '' === $token || ! hash_equals( $this->get_reschedule_token_for_booking( $booking ), $token ) ) {
				wp_send_json_error( array( 'message' => 'This appointment-change link is invalid or has expired.' ) );
			}
			$reason = '';
			if ( ! $this->booking_is_self_reschedulable( $booking, $reason ) || ! $this->is_valid_date( $date ) ) {
				wp_send_json_error( array( 'message' => $reason ? $reason : 'Please choose a valid date.' ) );
			}
			$location = $this->find_location( $booking->location_id );
			$service  = $this->find_service( $booking->service_id );
			$staff    = ! empty( $booking->staff_id ) ? $this->find_staff( $booking->staff_id ) : false;
			if ( ! $location || ! $service || ( ! empty( $booking->staff_id ) && ! $staff ) ) {
				wp_send_json_error( array( 'message' => 'This appointment can no longer be changed online. Please contact us.' ) );
			}
			wp_send_json_success( array( 'slots' => $this->build_slots( $service, $staff, $date, (int) $booking->id, $location ) ) );
		}

		public function handle_reschedule_request() {
			if ( empty( $_GET['rgl_booking_reschedule'] ) && empty( $_POST['rgl_booking_reschedule'] ) ) { return; }
			$is_post    = ! empty( $_POST['rgl_booking_reschedule'] );
			$source     = $is_post ? $_POST : $_GET;
			$booking_id = isset( $source['booking_id'] ) ? absint( $source['booking_id'] ) : 0;
			$token      = isset( $source['token'] ) ? sanitize_text_field( wp_unslash( $source['token'] ) ) : '';
			$booking    = $booking_id ? $this->get_booking_by_id( $booking_id ) : false;

			if ( ! $booking || '' === $token || ! hash_equals( $this->get_reschedule_token_for_booking( $booking ), $token ) ) {
				$this->render_reschedule_page( false, 'This appointment-change link is invalid or has expired.', null );
				return;
			}

			$reason = '';
			$eligible = $this->booking_is_self_reschedulable( $booking, $reason );
			if ( ! $is_post ) {
				$this->render_reschedule_page( null, $eligible ? '' : $reason, $booking, $eligible, $token );
				return;
			}

			$nonce = isset( $_POST['rgl_booking_reschedule_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['rgl_booking_reschedule_nonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'rgl_booking_reschedule_' . (int) $booking->id ) ) {
				$this->render_reschedule_page( false, 'Security check failed. Please open the appointment-change link again.', $booking );
				return;
			}
			if ( ! $eligible ) {
				$this->render_reschedule_page( false, $reason, $booking );
				return;
			}

			$new_date = isset( $_POST['booking_date'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_date'] ) ) : '';
			$new_time = isset( $_POST['booking_time'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_time'] ) ) : '';
			if ( ! $this->acquire_booking_write_lock() ) {
				$this->render_reschedule_page( false, 'Another booking is being processed. Please wait a moment and try again.', $booking );
				return;
			}
			register_shutdown_function( function () { $this->release_booking_write_lock(); } );

			$latest = $this->get_booking_by_id( (int) $booking->id );
			$latest_reason = '';
			if ( ! $latest || ! hash_equals( $this->get_reschedule_token_for_booking( $latest ), $token ) || ! $this->booking_is_self_reschedulable( $latest, $latest_reason ) ) {
				$this->release_booking_write_lock();
				$this->render_reschedule_page( false, $latest_reason ? $latest_reason : 'This appointment can no longer be changed online.', $latest ? $latest : $booking );
				return;
			}

			$payload = $this->sanitize_booking_payload(
				array(
					'location_id'        => $latest->location_id,
					'service_id'         => $latest->service_id,
					'staff_id'           => $latest->staff_id,
					'customer_name'      => $latest->customer_name,
					'customer_email'     => $latest->customer_email,
					'customer_phone'     => $latest->customer_phone,
					'customer_address'   => $latest->customer_address,
					'customer_notes'     => $latest->customer_notes,
					'customer_interests' => $latest->customer_interests,
					'admin_notes'        => $latest->admin_notes,
					'booking_date'       => $new_date,
					'booking_time'       => $new_time,
					'booking_quantity'   => isset( $latest->spaces_booked ) ? max( 1, absint( $latest->spaces_booked ) ) : 1,
					'booking_status'     => self::STATUS_CONFIRMED,
				)
			);
			$result = $this->validate_booking_payload( $payload, (int) $latest->id );
			if ( is_wp_error( $result ) ) {
				$this->release_booking_write_lock();
				$this->render_reschedule_page( null, $result->get_error_message(), $latest, true, $token );
				return;
			}

			if ( $payload['booking_date'] === (string) $latest->booking_date && substr( (string) $latest->booking_time, 0, 5 ) === $payload['booking_time'] ) {
				$this->release_booking_write_lock();
				$this->render_reschedule_page( null, 'Please choose a different date or time.', $latest, true, $token );
				return;
			}

			global $wpdb;
			$update_data = array(
				'booking_date'     => $payload['booking_date'],
				'booking_time'     => $payload['booking_time'] . ':00',
				'booking_end_time' => $result['end_time'] . ':00',
			);
			$formats = array( '%s', '%s', '%s' );
			if ( $this->reminder_sent_column_exists() ) {
				$update_data['reminder_sent_at'] = null;
				$formats[] = '%s';
			}
			$updated_ok = $wpdb->update( $this->table, $update_data, array( 'id' => (int) $latest->id ), $formats, array( '%d' ) );
			$updated = false === $updated_ok ? false : $this->get_booking_by_id( (int) $latest->id );
			if ( $updated ) {
				$this->mark_restricted_date_consumed( $payload['booking_date'], (int) $latest->id );
			}
			$this->release_booking_write_lock();
			if ( ! $updated ) {
				$this->render_reschedule_page( null, 'The appointment could not be changed. Please try again or contact us.', $latest, true, $token );
				return;
			}
			$this->send_booking_notifications( 'booking_rescheduled', $updated );
			$this->render_reschedule_page( true, 'Your appointment has been changed. We have emailed the updated details.', $updated );
		}

		private function get_public_action_button_css() {
			return '.rgl-public-actions{margin-top:24px;display:grid;grid-template-columns:minmax(0,1fr);gap:12px;width:100%;max-width:430px}.rgl-public-actions form{margin:0;width:100%}.rgl-public-actions .rgl-public-action{appearance:none;-webkit-appearance:none;display:flex;align-items:center;justify-content:center;width:100%;min-height:52px;margin:0;padding:13px 18px;border-radius:12px;box-sizing:border-box;font:inherit;font-size:16px;font-weight:700;line-height:1.25;text-align:center;text-decoration:none;white-space:normal;cursor:pointer;box-shadow:none}.rgl-public-actions .rgl-public-action:hover,.rgl-public-actions .rgl-public-action:focus{text-decoration:none}.rgl-public-actions .rgl-public-action--primary{background:#232323;border:1px solid #232323;color:#fff}.rgl-public-actions .rgl-public-action--primary:hover,.rgl-public-actions .rgl-public-action--primary:focus{background:#111;border-color:#111;color:#fff}.rgl-public-actions .rgl-public-action--secondary{background:#fff;border:1px solid #cfc7ba;color:#232323}.rgl-public-actions .rgl-public-action--secondary:hover,.rgl-public-actions .rgl-public-action--secondary:focus{background:#f8f5ef;border-color:#bfb5a6;color:#111}.rgl-public-actions .rgl-public-action--success{background:#067647;border:1px solid #067647;color:#fff}.rgl-public-actions .rgl-public-action--success:hover,.rgl-public-actions .rgl-public-action--success:focus{background:#05603a;border-color:#05603a;color:#fff}.rgl-public-actions .rgl-public-action--danger{background:#fff;border:1px solid #d7b4b0;color:#9b241b}.rgl-public-actions .rgl-public-action--danger:hover,.rgl-public-actions .rgl-public-action--danger:focus{background:#fef3f2;border-color:#c98f89;color:#7a1b14}.rgl-public-actions .rgl-public-action--danger-solid{background:#b42318;border:1px solid #b42318;color:#fff}.rgl-public-actions .rgl-public-action--danger-solid:hover,.rgl-public-actions .rgl-public-action--danger-solid:focus{background:#8e1c12;border-color:#8e1c12;color:#fff}.rgl-public-actions .rgl-public-action:disabled,.rgl-public-actions .rgl-public-action[aria-disabled="true"]{opacity:.55;cursor:not-allowed}';
		}

		private function render_reschedule_page( $changed, $message, $booking, $eligible = false, $token = '' ) {
			nocache_headers();
			$display_labels = $this->get_booking_display_labels();
			$display_date = $booking ? $this->format_booking_display_date( $booking ) : '';
			$display_time = $booking ? $this->format_booking_display_time( $booking ) : '';
			$available_dates = array();
			if ( $booking && $eligible ) {
				$available_dates = $this->build_available_dates_for_filters(
					(string) $booking->location_id,
					(string) $booking->service_id,
					(string) $booking->staff_id,
					array( (string) $booking->location_id ),
					array( (string) $booking->service_id ),
					empty( $booking->staff_id ) ? array() : array( (string) $booking->staff_id ),
					(int) $booking->id
				);
			}
			get_header();
			?>
			<style>
				<?php echo $this->get_public_action_button_css(); ?>
				.rgl-reschedule-page{padding:40px 20px}.rgl-reschedule-wrap{max-width:700px;margin:0 auto;padding:28px;border:1px solid #d0d5dd;border-radius:16px;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#fff}.rgl-reschedule-summary{background:#f9fafb;border:1px solid #eaecf0;border-radius:12px;padding:14px 18px;margin:18px 0}.rgl-reschedule-summary p{margin:4px 0}.rgl-reschedule-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.rgl-reschedule-grid label{display:block;font-weight:600}.rgl-reschedule-grid select{width:100%;margin-top:6px;min-height:44px}.rgl-reschedule-error{color:#b42318;font-weight:600}.rgl-reschedule-success{color:#067647;font-weight:600}.rgl-reschedule-status{margin-top:8px;color:#667085;font-size:14px}@media(max-width:650px){.rgl-reschedule-page{padding:24px 14px}.rgl-reschedule-wrap{padding:20px}.rgl-reschedule-grid{grid-template-columns:1fr}}
			</style>
			<main id="primary" class="site-main rgl-reschedule-page"><div class="rgl-reschedule-wrap">
				<?php if ( true === $changed ) : ?>
					<h1>Appointment changed</h1><p class="rgl-reschedule-success"><?php echo esc_html( $message ); ?></p>
				<?php elseif ( false === $changed ) : ?>
					<h1>Appointment change not completed</h1><p class="rgl-reschedule-error"><?php echo esc_html( $message ); ?></p>
				<?php else : ?>
					<h1>Change your appointment</h1>
					<?php if ( '' !== $message ) : ?><p class="rgl-reschedule-error"><?php echo esc_html( $message ); ?></p><?php endif; ?>
				<?php endif; ?>
				<?php if ( $booking ) : ?>
					<div class="rgl-reschedule-summary">
						<p><strong>Reference:</strong> <?php echo esc_html( $booking->booking_reference ); ?></p>
						<p><strong><?php echo esc_html( $display_labels['service'] ); ?>:</strong> <?php echo esc_html( $booking->service_name ); ?></p>
						<?php if ( ! empty( $booking->staff_name ) ) : ?><p><strong><?php echo esc_html( $display_labels['staff'] ); ?>:</strong> <?php echo esc_html( $booking->staff_name ); ?></p><?php endif; ?>
						<p><strong>Current appointment:</strong> <?php echo esc_html( $display_date . ' at ' . $display_time ); ?> (UK time)</p>
					</div>
				<?php endif; ?>
				<?php if ( null === $changed && $eligible && $booking ) : ?>
					<?php if ( empty( $available_dates ) ) : ?>
						<p class="rgl-reschedule-error">There are currently no alternative appointments available. Please contact us for help.</p>
					<?php else : ?>
						<form method="post" id="rgl-reschedule-form">
							<input type="hidden" name="rgl_booking_reschedule" value="1"><input type="hidden" name="booking_id" value="<?php echo (int) $booking->id; ?>"><input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>">
							<?php wp_nonce_field( 'rgl_booking_reschedule_' . (int) $booking->id, 'rgl_booking_reschedule_nonce' ); ?>
							<div class="rgl-reschedule-grid">
								<label>New date<select name="booking_date" id="rgl-reschedule-date" required><option value="">Select a date</option><?php foreach ( $available_dates as $date ) : ?><option value="<?php echo esc_attr( $date ); ?>"><?php echo esc_html( wp_date( 'l d-m-Y', strtotime( $date . ' 00:00:00' ) ) ); ?></option><?php endforeach; ?></select></label>
								<label>New time<select name="booking_time" id="rgl-reschedule-time" required disabled><option value="">Choose a date first</option></select></label>
							</div>
							<p class="rgl-reschedule-status" id="rgl-reschedule-status" aria-live="polite"></p>
							<div class="rgl-public-actions">
								<button type="submit" class="rgl-public-action rgl-public-action--primary" id="rgl-reschedule-submit" disabled>Confirm new appointment</button>
								<a class="rgl-public-action rgl-public-action--secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Return to website</a>
							</div>
						</form>
						<script>
						(function(){
							var dateEl=document.getElementById('rgl-reschedule-date'),timeEl=document.getElementById('rgl-reschedule-time'),submitEl=document.getElementById('rgl-reschedule-submit'),statusEl=document.getElementById('rgl-reschedule-status');
							if(!dateEl||!timeEl){return;}
							dateEl.addEventListener('change',function(){
								timeEl.innerHTML='<option value="">Checking times…</option>';timeEl.disabled=true;submitEl.disabled=true;statusEl.textContent='Checking availability…';
								if(!dateEl.value){timeEl.innerHTML='<option value="">Choose a date first</option>';statusEl.textContent='';return;}
								var body=new URLSearchParams();body.append('action','rgl_booking_reschedule_slots');body.append('nonce','<?php echo esc_js( wp_create_nonce( 'appt_booker_nonce' ) ); ?>');body.append('booking_id','<?php echo (int) $booking->id; ?>');body.append('token','<?php echo esc_js( $token ); ?>');body.append('booking_date',dateEl.value);
								fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:body.toString(),credentials:'same-origin'}).then(function(r){return r.json();}).then(function(resp){
									var slots=resp&&resp.success&&resp.data?resp.data.slots||[]:[];timeEl.innerHTML='';
									var first=document.createElement('option');first.value='';first.textContent=slots.length?'Select a time':'No available times';timeEl.appendChild(first);
									slots.forEach(function(slot){var o=document.createElement('option');o.value=slot.value;o.textContent=slot.label||slot.value;timeEl.appendChild(o);});
									timeEl.disabled=!slots.length;statusEl.textContent=slots.length?'':'No times are available on that date.';
								}).catch(function(){timeEl.innerHTML='<option value="">Could not load times</option>';statusEl.textContent='Please reload the page and try again.';});
							});
							timeEl.addEventListener('change',function(){submitEl.disabled=!timeEl.value;});
						})();
						</script>
					<?php endif; ?>
				<?php endif; ?>
				<?php if ( ! ( null === $changed && $eligible && $booking && ! empty( $available_dates ) ) ) : ?>
					<div class="rgl-public-actions"><a class="rgl-public-action rgl-public-action--secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Return to website</a></div>
				<?php endif; ?>
			</div></main>
			<?php
			get_footer();
			exit;
		}

		/**
		 * Approve / reject handler (added) - admin clicks a token-signed link
		 * from the pending-booking notification email. The link opens a
		 * confirmation page (GET), and only a deliberate button-click (POST,
		 * with both the original token and a fresh WP nonce) actually approves
		 * or rejects the booking. This stops email-link scanners (e.g.
		 * Microsoft Safe Links, Office 365 link rewriting, mail-client
		 * previewers) from accidentally approving bookings when they pre-fetch
		 * URLs to scan them.
		 */
		public function handle_approval_request() {
			if ( empty( $_GET['rgl_booking_approve'] ) && empty( $_POST['rgl_booking_approve'] ) ) { return; }

			$is_post    = ! empty( $_POST['rgl_booking_approve'] );
			$source     = $is_post ? $_POST : $_GET;
			$booking_id = isset( $source['booking_id'] ) ? absint( $source['booking_id'] ) : 0;
			$action     = isset( $source['action'] ) ? sanitize_key( wp_unslash( $source['action'] ) ) : '';
			$token      = isset( $source['token'] ) ? sanitize_text_field( wp_unslash( $source['token'] ) ) : '';
			$booking    = $booking_id ? $this->get_booking_by_id( $booking_id ) : false;

			if ( ! $booking || ! in_array( $action, array( 'approve', 'reject' ), true ) || '' === $token ) {
				$this->render_approval_page( false, 'This approval link is invalid or has expired.', null );
				return;
			}
			$expected = $this->get_approval_token_for_booking( $booking, $action );
			if ( '' === $expected || ! hash_equals( $expected, $token ) ) {
				$this->render_approval_page( false, 'This approval link is invalid or has expired.', $booking );
				return;
			}
			if ( ! isset( $booking->booking_status ) || self::STATUS_PENDING_APPROVAL !== $booking->booking_status ) {
				$this->render_approval_page( false, 'This booking is no longer pending approval (it may already have been actioned).', $booking );
				return;
			}

			// GET: show the confirmation page. No state change yet.
			if ( ! $is_post ) {
				$this->render_approval_confirm_page( $booking, $action, $token );
				return;
			}

			// POST: verify a fresh nonce and only then act. The nonce is set
			// on the confirmation page and only known to a real browser user.
			$nonce = isset( $_POST['rgl_approval_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['rgl_approval_nonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'rgl_approval_action_' . (int) $booking->id ) ) {
				$this->render_approval_page( false, 'This approval action could not be confirmed. Please open the link again.', $booking );
				return;
			}

			global $wpdb;
			if ( 'approve' === $action ) {
				$approval_data    = array( 'booking_status' => self::STATUS_CONFIRMED );
				$approval_formats = array( '%s' );
				if ( $this->reminder_sent_column_exists() ) {
					$approval_data['reminder_sent_at'] = null;
					$approval_formats[]                = '%s';
				}
				$wpdb->update(
					$this->table,
					$approval_data,
					array( 'id' => (int) $booking->id ),
					$approval_formats,
					array( '%d' )
				);
				$updated = $this->get_booking_by_id( (int) $booking->id );
				if ( $updated ) {
					// Now the booking is confirmed - fire the standard notifications
					// so customer gets their calendar invite and full confirmation.
					$this->send_booking_notifications( 'booking_approved', $updated, array( 'customer', 'staff' ) );
				}
				$this->render_approval_page( true, 'Booking approved. The customer has been notified and their calendar invite has been sent.', $updated );
			} else {
				$wpdb->update(
					$this->table,
					array(
						'booking_status'      => self::STATUS_CANCELLED,
						'cancellation_reason' => 'Rejected on review.',
					),
					array( 'id' => (int) $booking->id ),
					array( '%s', '%s' ),
					array( '%d' )
				);
				$updated = $this->get_booking_by_id( (int) $booking->id );
				if ( $updated ) {
					$this->send_booking_notifications( 'booking_cancelled', $updated, array( 'customer', 'staff' ) );
				}
				$this->render_approval_page( true, 'Booking rejected. The customer has been notified.', $updated );
			}
		}

		/**
		 * Confirm page shown on GET (no state change). The action only fires
		 * when the admin clicks the button below, which POSTs back to the
		 * same endpoint with the original token plus a fresh WP nonce.
		 */
		private function render_approval_confirm_page( $booking, $action, $token ) {
			nocache_headers();
			get_header();
			$is_approve  = ( 'approve' === $action );
			$verb        = $is_approve ? 'Approve' : 'Reject';
			$confirm_q   = $is_approve ? 'Approve this booking?' : 'Reject this booking?';
			$lede        = $is_approve
				? 'The customer will be notified and their calendar invite will be sent.'
				: 'The booking will be cancelled and the customer will be notified.';
			$display_date = $this->format_booking_display_date( $booking );
			$display_time = $this->format_booking_display_time( $booking );
			?>
			<style><?php echo $this->get_public_action_button_css(); ?></style>
			<div style="padding:40px 20px;">
				<div style="max-width:680px;margin:0 auto;padding:28px;border:1px solid #d0d5dd;border-radius:16px;font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#fff;">
					<h1 style="margin-top:0;"><?php echo esc_html( $confirm_q ); ?></h1>
					<p><?php echo esc_html( $lede ); ?></p>
					<table style="margin:20px 0;border-collapse:collapse;width:100%;">
						<?php foreach ( array(
							'Reference' => isset( $booking->booking_reference ) ? $booking->booking_reference : '',
							'Customer'  => isset( $booking->customer_name ) ? $booking->customer_name : '',
							'Email'     => isset( $booking->customer_email ) ? $booking->customer_email : '',
							'Service'   => isset( $booking->service_name ) ? $booking->service_name : '',
							'Team'      => isset( $booking->staff_name ) ? $booking->staff_name : '',
							'Date'      => $display_date,
							'Time'      => $display_time,
						) as $k => $v ) :
							if ( '' === (string) $v ) { continue; }
						?>
							<tr>
								<td style="padding:6px 12px 6px 0;color:#6b7280;white-space:nowrap;vertical-align:top;"><?php echo esc_html( $k ); ?></td>
								<td style="padding:6px 0;color:#111;font-weight:600;"><?php echo esc_html( $v ); ?></td>
							</tr>
						<?php endforeach; ?>
					</table>
					<form method="post" class="rgl-public-actions">
						<input type="hidden" name="rgl_booking_approve" value="1">
						<input type="hidden" name="booking_id" value="<?php echo (int) $booking->id; ?>">
						<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
						<input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>">
						<input type="hidden" name="rgl_approval_nonce" value="<?php echo esc_attr( wp_create_nonce( 'rgl_approval_action_' . (int) $booking->id ) ); ?>">
						<button type="submit" class="rgl-public-action <?php echo $is_approve ? 'rgl-public-action--success' : 'rgl-public-action--danger-solid'; ?>">Yes, <?php echo esc_html( strtolower( $verb ) ); ?> booking</button>
					</form>
					<p style="margin:18px 0 0;color:#6b7280;font-size:13px;">If you did not mean to open this, just close this window. Nothing has been changed.</p>
				</div>
			</div>
			<?php
			get_footer();
			exit;
		}

		private function render_approval_page( $ok, $message, $booking ) {
			nocache_headers();
			get_header();
			?>
			<style><?php echo $this->get_public_action_button_css(); ?></style>
			<div style="padding:40px 20px;">
				<div style="max-width:680px;margin:0 auto;padding:28px;border:1px solid #d0d5dd;border-radius:16px;font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#fff;">
					<h1 style="margin-top:0;"><?php echo $ok ? 'Done' : 'Could not action this booking'; ?></h1>
					<p><?php echo esc_html( $message ); ?></p>
					<?php if ( $booking && ! empty( $booking->booking_reference ) ) : ?>
						<p style="color:#6b7280;">Reference: <strong><?php echo esc_html( $booking->booking_reference ); ?></strong></p>
					<?php endif; ?>
					<div class="rgl-public-actions"><a class="rgl-public-action rgl-public-action--secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=appt-booker&tab=bookings' ) ); ?>">Open bookings admin</a></div>
				</div>
			</div>
			<?php
			get_footer();
			exit;
		}

		/**
		 * Render the cancellation page inside the active WordPress theme.
		 *
		 * @param bool|null $cancelled  true = already cancelled this request, false = error, null = show confirm form.
		 * @param string    $message    Status message to display.
		 * @param object|null $booking  Booking row (or null if not found).
		 * @param bool       $eligible  Whether the cancel button should be shown (only used when $cancelled is null).
		 */
		private function render_cancellation_page( $cancelled, $message, $booking, $eligible = false ) {
			nocache_headers();
			$settings = $this->get_settings();
			$ask_reason = ! empty( $settings['cancellation_ask_for_reason'] );
			$display_labels = $this->get_booking_display_labels();
			$display_date = $booking ? $this->format_booking_display_date( $booking ) : '';
			$display_time = $booking ? $this->format_booking_display_time( $booking ) : '';

			get_header();
			?>
			<style>
				<?php echo $this->get_public_action_button_css(); ?>
				.rgl-booking-cancel-page{padding:40px 20px}
				.rgl-cancel-wrap{max-width:680px;margin:0 auto;padding:28px;border:1px solid #d0d5dd;border-radius:16px;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#fff}
				.rgl-cancel-wrap h1{margin-top:0}
				.rgl-cancel-summary{background:#f9fafb;border:1px solid #eaecf0;border-radius:12px;padding:14px 18px;margin:18px 0}
				.rgl-cancel-summary p{margin:4px 0}
				.rgl-cancel-form{margin-top:18px}
				.rgl-cancel-form textarea{width:100%;padding:10px;border-radius:8px;border:1px solid #cbd5e1;font:inherit;box-sizing:border-box}
				.rgl-cancel-success{color:#067647;font-weight:600}
				.rgl-cancel-error{color:#b42318;font-weight:600}
				@media(max-width:650px){.rgl-booking-cancel-page{padding:24px 14px}.rgl-cancel-wrap{padding:20px}}
			</style>
			<main id="primary" class="site-main rgl-booking-cancel-page">
				<div class="rgl-cancel-wrap">
					<?php if ( true === $cancelled ) : ?>
						<h1>Booking Cancelled</h1>
						<p class="rgl-cancel-success"><?php echo esc_html( $message ); ?></p>
						<?php if ( $booking ) : ?>
							<div class="rgl-cancel-summary">
								<p><strong>Reference:</strong> <?php echo esc_html( $booking->booking_reference ); ?></p>
								<p><strong><?php echo esc_html( $display_labels['service'] ); ?>:</strong> <?php echo esc_html( $booking->service_name ); ?></p>
								<?php if ( '' !== $display_date ) : ?><p><strong>Date:</strong> <?php echo esc_html( $display_date ); ?></p><?php endif; ?>
								<?php if ( '' !== $display_time ) : ?><p><strong>Time:</strong> <?php echo esc_html( $display_time ); ?></p><?php endif; ?>
							</div>
						<?php endif; ?>
						<div class="rgl-public-actions"><a class="rgl-public-action rgl-public-action--secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Return to website</a></div>
					<?php elseif ( false === $cancelled ) : ?>
						<h1>Cancellation Not Available</h1>
						<p class="rgl-cancel-error"><?php echo esc_html( $message ); ?></p>
						<div class="rgl-public-actions"><a class="rgl-public-action rgl-public-action--secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Return to website</a></div>
					<?php else : ?>
						<h1>Cancel Your Booking</h1>
						<?php if ( $booking ) : ?>
							<p>Please confirm you would like to cancel the booking below. Once cancelled it cannot be reinstated; you would need to make a new booking.</p>
							<div class="rgl-cancel-summary">
								<p><strong>Reference:</strong> <?php echo esc_html( $booking->booking_reference ); ?></p>
								<p><strong><?php echo esc_html( $display_labels['service'] ); ?>:</strong> <?php echo esc_html( $booking->service_name ); ?></p>
								<?php if ( ! empty( $booking->staff_name ) ) : ?><p><strong><?php echo esc_html( $display_labels['staff'] ); ?>:</strong> <?php echo esc_html( $booking->staff_name ); ?></p><?php endif; ?>
								<?php if ( '' !== $display_date ) : ?><p><strong>Date:</strong> <?php echo esc_html( $display_date ); ?></p><?php endif; ?>
								<?php if ( '' !== $display_time ) : ?><p><strong>Time:</strong> <?php echo esc_html( $display_time ); ?></p><?php endif; ?>
							</div>
							<?php if ( $eligible ) : ?>
								<form method="post" class="rgl-cancel-form">
									<?php wp_nonce_field( 'rgl_booking_cancel_' . (int) $booking->id, 'rgl_booking_cancel_nonce' ); ?>
									<?php if ( $ask_reason ) : ?>
										<p><label for="rgl-cancel-reason"><strong>Could you let us know why? (Optional)</strong></label><br>
											<textarea id="rgl-cancel-reason" name="cancellation_reason" rows="3" maxlength="500" placeholder="A short reason helps us improve."></textarea></p>
									<?php endif; ?>
									<div class="rgl-public-actions">
										<a class="rgl-public-action rgl-public-action--secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Keep my booking</a>
										<button type="submit" name="rgl_booking_cancel_confirm" value="1" class="rgl-public-action rgl-public-action--danger-solid">Yes, cancel this booking</button>
									</div>
								</form>
							<?php else : ?>
								<p class="rgl-cancel-error"><?php echo esc_html( $message ); ?></p>
								<div class="rgl-public-actions"><a class="rgl-public-action rgl-public-action--secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Return to website</a></div>
							<?php endif; ?>
						<?php else : ?>
							<p class="rgl-cancel-error">Booking not found.</p>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</main>
			<?php
			get_footer();
			exit;
		}

		private function get_booking_summary_url( $booking ) {
			if ( ! $booking || empty( $booking->id ) ) { return ''; }
			return add_query_arg( array( 'rgl_booking_summary' => 1, 'booking_id' => (int) $booking->id, 'token' => $this->get_booking_access_token_for_booking( $booking ) ), home_url( '/' ) );
		}

		private function get_effective_booking_summary_notes( $booking ) {
			if ( ! $booking ) { return ''; }
			$service = ! empty( $booking->service_id ) ? $this->find_service( $booking->service_id ) : false;
			$staff   = ! empty( $booking->staff_id ) ? $this->find_staff( $booking->staff_id ) : false;
			$notes   = ( is_array( $service ) && isset( $service['booking_summary_notes'] ) )
				? wp_kses_post( $service['booking_summary_notes'] )
				: '';

			if (
				is_array( $service ) &&
				is_array( $staff ) &&
				! empty( $service['staff_rules'][ $staff['id'] ]['booking_summary_notes'] )
			) {
				$notes = wp_kses_post( $service['staff_rules'][ $staff['id'] ]['booking_summary_notes'] );
			}

			return $notes;
		}


		private function get_admin_reminder_status( $booking ) {
			$settings = $this->get_settings();
			if ( ! $booking || ! isset( $booking->booking_status ) || self::STATUS_CONFIRMED !== (string) $booking->booking_status ) {
				return array( 'label' => 'Not scheduled', 'detail' => 'Reminders are only sent for confirmed appointments.' );
			}
			if ( ! $this->reminder_sent_column_exists() ) {
				return array( 'label' => 'Unavailable', 'detail' => 'The reminder database column is missing.' );
			}
			if ( ! empty( $booking->reminder_sent_at ) ) {
				try {
					$sent = new DateTimeImmutable( (string) $booking->reminder_sent_at, wp_timezone() );
					return array( 'label' => 'Sent', 'detail' => wp_date( 'd-m-Y H:i', $sent->getTimestamp() ) . ' (UK time)' );
				} catch ( Exception $e ) {
					return array( 'label' => 'Sent', 'detail' => (string) $booking->reminder_sent_at );
				}
			}
			if ( empty( $settings['reminder_enabled'] ) ) {
				return array( 'label' => 'Automatic reminders disabled', 'detail' => 'You can still send a reminder manually.' );
			}
			$start_ts = $this->get_booking_start_timestamp( $booking );
			if ( $start_ts <= current_datetime()->getTimestamp() ) {
				return array( 'label' => 'Not sent', 'detail' => 'The appointment time has passed.' );
			}
			$hours = max( 1, min( 168, absint( isset( $settings['reminder_hours_before'] ) ? $settings['reminder_hours_before'] : 24 ) ) );
			$point_ts = $start_ts - ( $hours * HOUR_IN_SECONDS );
			$created_ts = 0;
			try { $created_ts = ( new DateTimeImmutable( (string) $booking->created_at, wp_timezone() ) )->getTimestamp(); } catch ( Exception $e ) { $created_ts = 0; }
			if ( $created_ts > $point_ts ) {
				return array( 'label' => 'Not scheduled automatically', 'detail' => 'The booking was made inside the reminder window.' );
			}
			if ( current_datetime()->getTimestamp() >= $point_ts ) {
				return array( 'label' => 'Due / retry pending', 'detail' => 'The next hourly reminder sweep will try to send it.' );
			}
			return array( 'label' => 'Scheduled', 'detail' => wp_date( 'd-m-Y H:i', $point_ts ) . ' (approximately; cron runs hourly)' );
		}

		private function render_shared_staff_availability_fields( $service_index, $staff_id, $hours ) {
			$service_index = (string) $service_index;
			$staff_id      = sanitize_key( $staff_id );
			$hours         = is_array( $hours ) ? $this->sanitize_hours_rows( $hours ) : $this->default_hours();
			?>
			<div class="rgl-shared-staff-hours" style="margin-top:14px;">
				<strong>Availability across all contact methods</strong>
				<p style="margin:6px 0 10px;color:#666;">Set this once for the team member. The same days and times apply to Phone, WhatsApp call, WhatsApp message and every other allowed contact method for this service.</p>
				<div class="rgl-days">
					<?php foreach ( $this->days_map() as $day_key => $day_label ) :
						$day_data = isset( $hours[ $day_key ] ) ? $hours[ $day_key ] : array( 'enabled' => 0, 'start' => '09:00', 'end' => '17:00' );
					?>
						<div class="rgl-day">
							<strong><?php echo esc_html( $day_label ); ?></strong>
							<p><label><input type="checkbox" data-name="services[%d][staff_rules][<?php echo esc_attr( $staff_id ); ?>][hours][<?php echo esc_attr( $day_key ); ?>][enabled]" name="services[<?php echo esc_attr( $service_index ); ?>][staff_rules][<?php echo esc_attr( $staff_id ); ?>][hours][<?php echo esc_attr( $day_key ); ?>][enabled]" value="1" <?php checked( ! empty( $day_data['enabled'] ) ); ?>> Available</label></p>
							<p><label>Start<br><input type="time" data-name="services[%d][staff_rules][<?php echo esc_attr( $staff_id ); ?>][hours][<?php echo esc_attr( $day_key ); ?>][start]" name="services[<?php echo esc_attr( $service_index ); ?>][staff_rules][<?php echo esc_attr( $staff_id ); ?>][hours][<?php echo esc_attr( $day_key ); ?>][start]" value="<?php echo esc_attr( $day_data['start'] ); ?>"></label></p>
							<p><label>End<br><input type="time" data-name="services[%d][staff_rules][<?php echo esc_attr( $staff_id ); ?>][hours][<?php echo esc_attr( $day_key ); ?>][end]" name="services[<?php echo esc_attr( $service_index ); ?>][staff_rules][<?php echo esc_attr( $staff_id ); ?>][hours][<?php echo esc_attr( $day_key ); ?>][end]" value="<?php echo esc_attr( $day_data['end'] ); ?>"></label></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php
		}

		private function render_admin_booking_view( $booking_id ) {
			$booking = $this->get_booking_by_id( $booking_id );
			if ( ! $booking ) {
				echo '<div class="notice notice-error"><p>Booking not found.</p></div>';
				echo '<p><a class="button" href="' . esc_url( admin_url( 'admin.php?page=appt-booker&tab=bookings' ) ) . '">Back to bookings</a></p>';
				return;
			}

			$display_labels = $this->get_booking_display_labels();
			$display_date   = $this->format_booking_display_date( $booking );
			$display_time   = $this->format_booking_display_time( $booking );
			$status         = isset( $booking->booking_status ) ? (string) $booking->booking_status : '';
			$summary_url    = $this->get_booking_summary_url( $booking );
			$google_url     = $this->get_google_calendar_url( $booking );
			$back_url       = admin_url( 'admin.php?page=appt-booker&tab=bookings' );
			$edit_url       = $back_url . '&edit=' . absint( $booking->id );
			$reminder_status = $this->get_admin_reminder_status( $booking );
			$no_show_settings = $this->get_settings();
			$recent_no_show_count = ! empty( $booking->customer_email ) ? $this->get_recent_no_show_count( $booking->customer_email ) : 0;
			$no_show_window_days = max( 1, absint( isset( $no_show_settings['no_show_window_days'] ) ? $no_show_settings['no_show_window_days'] : 180 ) );
			$no_show_threshold = max( 1, absint( isset( $no_show_settings['no_show_threshold'] ) ? $no_show_settings['no_show_threshold'] : 2 ) );
			$timed_booking_block = ! empty( $booking->customer_email ) ? $this->get_booking_block_for_email( $booking->customer_email ) : false;
			$legacy_booking_block = ! empty( $booking->customer_email ) ? $this->email_is_legacy_blocked( $booking->customer_email ) : false;

			$status_colour = array(
				'confirmed' => array( 'bg' => '#ecfdf3', 'fg' => '#067647', 'border' => '#abefc6' ),
				'held'      => array( 'bg' => '#fff7ed', 'fg' => '#b54708', 'border' => '#fed7aa' ),
				'cancelled' => array( 'bg' => '#fef3f2', 'fg' => '#b42318', 'border' => '#fecdca' ),
				'pending_approval' => array( 'bg' => '#fffbeb', 'fg' => '#b45309', 'border' => '#fde68a' ),
				'completed' => array( 'bg' => '#eff8ff', 'fg' => '#175cd3', 'border' => '#b2ddff' ),
				'no_show'   => array( 'bg' => '#f4f3ff', 'fg' => '#5925dc', 'border' => '#d9d6fe' ),
			);
			$sc = isset( $status_colour[ $status ] ) ? $status_colour[ $status ] : array( 'bg' => '#f3f4f6', 'fg' => '#374151', 'border' => '#e5e7eb' );
			?>
			<style>
				.rgl-bview{max-width:920px;font-family:system-ui,-apple-system,'Segoe UI',sans-serif}
				.rgl-bview-head{display:flex;align-items:center;flex-wrap:wrap;gap:10px;margin:14px 0 18px}
				.rgl-bview-status{display:inline-block;padding:3px 12px;border-radius:999px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.3px}
				.rgl-bview-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;margin:0 0 14px}
				.rgl-bview-card h3{margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.4px;color:#6b7280}
				.rgl-bview-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px}
				.rgl-bview-field strong{display:block;font-size:12px;color:#6b7280;text-transform:uppercase;letter-spacing:.3px;margin-bottom:2px}
				.rgl-bview-field span,.rgl-bview-field a{font-size:15px;color:#111}
				.rgl-bview-block{white-space:pre-wrap;font-size:14px;line-height:1.55;color:#1f2937;background:#fafaf7;border:1px solid #e6e6e2;border-radius:6px;padding:12px 14px}
				.rgl-bview-empty{color:#9ca3af;font-style:italic}
				.rgl-bview-actions{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 14px}
				.rgl-bview-note-label{font-weight:600;margin:8px 0 4px;font-size:13px;color:#374151}
			</style>
			<div class="rgl-bview">
				<p style="margin:14px 0;"><a href="<?php echo esc_url( $back_url ); ?>">&larr; Back to bookings</a></p>

				<div class="rgl-bview-head">
					<h1 style="margin:0;"><?php echo esc_html( $booking->booking_reference ); ?></h1>
					<span class="rgl-bview-status" style="background:<?php echo esc_attr( $sc['bg'] ); ?>;color:<?php echo esc_attr( $sc['fg'] ); ?>;border:1px solid <?php echo esc_attr( $sc['border'] ); ?>;"><?php echo esc_html( $this->get_booking_status_label( $status ) ); ?></span>
				</div>

				<div class="rgl-bview-actions">
					<a class="button button-primary" href="<?php echo esc_url( $edit_url ); ?>">Edit Booking</a>
					<?php if ( $summary_url ) : ?><a class="button" href="<?php echo esc_url( $summary_url ); ?>" target="_blank" rel="noopener">Customer Summary &nearr;</a><?php endif; ?>
					<?php if ( $google_url ) : ?><a class="button" href="<?php echo esc_url( $google_url ); ?>" target="_blank" rel="noopener">Add to Google Calendar</a><?php endif; ?>
					<?php if ( self::STATUS_CONFIRMED === $status && $this->get_booking_start_timestamp( $booking ) > current_datetime()->getTimestamp() ) : ?><form method="post" style="display:inline;"><?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?><input type="hidden" name="rgl_booking_action" value="send_reminder_now"><input type="hidden" name="booking_id" value="<?php echo (int) $booking->id; ?>"><button class="button" type="submit"><?php echo ! empty( $booking->reminder_sent_at ) ? 'Send reminder again' : 'Send reminder now'; ?></button></form><?php endif; ?>
				</div>

				<div class="rgl-bview-card">
					<h3>Appointment</h3>
					<div class="rgl-bview-grid">
						<div class="rgl-bview-field"><strong><?php echo esc_html( $display_labels['service'] ); ?></strong><span><?php echo esc_html( $booking->service_name ); ?></span></div>
						<?php if ( ! empty( $booking->staff_name ) ) : ?><div class="rgl-bview-field"><strong><?php echo esc_html( $display_labels['staff'] ); ?></strong><span><?php echo esc_html( $booking->staff_name ); ?></span></div><?php endif; ?>
						<?php if ( ! empty( $booking->location_name ) ) : ?><div class="rgl-bview-field"><strong><?php echo esc_html( $display_labels['location'] ); ?></strong><span><?php echo esc_html( $booking->location_name ); ?></span></div><?php endif; ?>
						<div class="rgl-bview-field"><strong>Date</strong><span><?php echo esc_html( $display_date ); ?></span></div>
						<div class="rgl-bview-field"><strong>Time</strong><span><?php echo esc_html( $display_time ); ?> (UK)</span></div>
					</div>
				</div>

				<div class="rgl-bview-card">
					<h3>Reminder</h3>
					<div class="rgl-bview-grid"><div class="rgl-bview-field"><strong>Status</strong><span><?php echo esc_html( $reminder_status['label'] ); ?></span></div><div class="rgl-bview-field"><strong>Details</strong><span><?php echo esc_html( $reminder_status['detail'] ); ?></span></div></div>
				</div>

				<div class="rgl-bview-card">
					<h3>Customer</h3>
					<div class="rgl-bview-grid">
						<div class="rgl-bview-field"><strong>Name</strong><span><?php echo esc_html( $booking->customer_name ? $booking->customer_name : '—' ); ?></span></div>
						<div class="rgl-bview-field"><strong>Email</strong><span><?php if ( ! empty( $booking->customer_email ) ) : ?><a href="mailto:<?php echo esc_attr( $booking->customer_email ); ?>"><?php echo esc_html( $booking->customer_email ); ?></a><?php else : ?><em class="rgl-bview-empty">Removed by retention</em><?php endif; ?></span></div>
						<div class="rgl-bview-field"><strong>Phone</strong><span><?php if ( ! empty( $booking->customer_phone ) ) : ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $booking->customer_phone ) ); ?>"><?php echo esc_html( $booking->customer_phone ); ?></a><?php else : ?><em class="rgl-bview-empty">Not provided</em><?php endif; ?></span></div>
					</div>
					<?php if ( ! empty( $booking->customer_interests ) ) : ?>
						<p class="rgl-bview-note-label">Topics they ticked at booking</p>
						<div class="rgl-bview-block"><?php echo esc_html( $booking->customer_interests ); ?></div>
					<?php endif; ?>
					<?php if ( ! empty( $booking->customer_notes ) ) : ?>
						<p class="rgl-bview-note-label">Their pre-call message</p>
						<div class="rgl-bview-block"><?php echo esc_html( $booking->customer_notes ); ?></div>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $booking->customer_email ) ) : ?>
				<div class="rgl-bview-card">
					<h3>Attendance safeguards</h3>
					<div class="rgl-bview-grid">
						<div class="rgl-bview-field"><strong>Recent no-shows</strong><span><?php echo esc_html( $recent_no_show_count . ' in the last ' . $no_show_window_days . ' days' ); ?></span></div>
						<div class="rgl-bview-field"><strong>Automatic review point</strong><span><?php echo esc_html( $no_show_threshold . ' no-show' . ( 1 === $no_show_threshold ? '' : 's' ) ); ?></span></div>
						<div class="rgl-bview-field"><strong>Future booking block</strong><span><?php if ( $timed_booking_block ) { echo esc_html( 'Active until ' . wp_date( 'd-m-Y', absint( $timed_booking_block['expires_at'] ) ) ); } elseif ( $legacy_booking_block ) { echo esc_html( 'Active (legacy/spam list, no automatic expiry)' ); } else { echo 'Not blocked'; } ?></span></div>
					</div>
					<?php if ( $recent_no_show_count >= $no_show_threshold && ! empty( $no_show_settings['no_show_safeguard_enabled'] ) ) : ?><p style="margin:14px 0;color:#92400e;font-weight:600;">New public bookings from this email will be held for human review, not rejected automatically.</p><?php endif; ?>
					<form method="post" style="margin-top:14px;" onsubmit="return confirm('<?php echo ( $timed_booking_block || $legacy_booking_block ) ? 'Allow future online bookings from this email again?' : 'Block future online bookings from this email for the configured period? No customer email will be sent.'; ?>');"><?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?><input type="hidden" name="rgl_booking_action" value="<?php echo ( $timed_booking_block || $legacy_booking_block ) ? 'unblock_future_bookings' : 'block_future_bookings'; ?>"><input type="hidden" name="booking_id" value="<?php echo (int) $booking->id; ?>"><button class="button" type="submit"><?php echo ( $timed_booking_block || $legacy_booking_block ) ? 'Allow future bookings' : 'Block future bookings'; ?></button></form>
				</div>
				<?php endif; ?>

				<div class="rgl-bview-card">
					<h3>Post-conversation notes</h3>
					<?php
					$view_admin_notes = isset( $booking->admin_notes ) ? (string) $booking->admin_notes : '';
					// Defensive: ignore legacy zero-valued numeric corruption.
					if ( is_numeric( trim( $view_admin_notes ) ) && 0.0 === (float) $view_admin_notes ) {
						$view_admin_notes = '';
					}
					if ( '' === trim( $view_admin_notes ) ) : ?>
						<p class="rgl-bview-empty">No notes yet. Use Edit Booking to add what was discussed, what you advised, and any follow-up.</p>
					<?php else : ?>
						<div class="rgl-bview-block"><?php echo esc_html( $view_admin_notes ); ?></div>
					<?php endif; ?>
				</div>

				<?php if ( 'cancelled' === $status && ! empty( $booking->cancellation_reason ) ) : ?>
					<div class="rgl-bview-card">
						<h3>Cancellation reason</h3>
						<div class="rgl-bview-block"><?php echo esc_html( $booking->cancellation_reason ); ?></div>
					</div>
				<?php endif; ?>

				<div class="rgl-bview-card">
					<h3>Record</h3>
					<div class="rgl-bview-grid">
						<div class="rgl-bview-field"><strong>Booking ID</strong><span>#<?php echo (int) $booking->id; ?></span></div>
						<?php if ( ! empty( $booking->created_at ) ) : ?><div class="rgl-bview-field"><strong>Booked on</strong><span><?php echo esc_html( $booking->created_at ); ?></span></div><?php endif; ?>
						<?php if ( ! empty( $booking->updated_at ) ) : ?><div class="rgl-bview-field"><strong>Last updated</strong><span><?php echo esc_html( $booking->updated_at ); ?></span></div><?php endif; ?>
					</div>
				</div>
			</div>
			<?php
		}


		private function get_booking_display_labels() {
			$settings = $this->get_settings();
			return array(
				'location' => $this->get_form_label( $settings, 'label_location', 'Location' ),
				'service'  => $this->get_form_label( $settings, 'label_service', 'Service' ),
				'staff'    => $this->get_form_label( $settings, 'label_staff', 'Team Member' ),
			);
		}

		private function format_booking_display_date( $booking ) {
			$date = isset( $booking->booking_date ) ? trim( (string) $booking->booking_date ) : '';
			if ( '' === $date ) { return ''; }
			try {
				$dt = new DateTime( $date . ' 00:00:00', wp_timezone() );
				// UK format: weekday name plus dd-mm-yyyy, e.g. "Monday 14-05-2026".
				return wp_date( 'l d-m-Y', $dt->getTimestamp() );
			} catch ( Exception $e ) {
				return $date;
			}
		}

		/**
		 * Plain dd-mm-yyyy with no weekday, for compact contexts like the
		 * admin bookings list and calendar summaries (added).
		 */
		private function format_booking_short_date( $booking ) {
			$date = is_object( $booking ) && isset( $booking->booking_date ) ? trim( (string) $booking->booking_date ) : trim( (string) $booking );
			if ( '' === $date ) { return ''; }
			try {
				$dt = new DateTime( $date . ' 00:00:00', wp_timezone() );
				return wp_date( 'd-m-Y', $dt->getTimestamp() );
			} catch ( Exception $e ) {
				return $date;
			}
		}

		private function get_booking_display_duration_minutes( $booking ) {
			// Honour customer-facing duration when the service has it set
			// (e.g. footprint 60 mins but the customer's call is only 15).
			$service = ! empty( $booking->service_id ) ? $this->find_service( $booking->service_id ) : false;
			$staff   = ! empty( $booking->staff_id ) ? $this->find_staff( $booking->staff_id ) : false;
			if ( $service ) {
				$rules = $this->resolve_effective_rules( $service, $staff );
				$cfd = absint( isset( $rules['customer_facing_duration'] ) ? $rules['customer_facing_duration'] : 0 );
				if ( $cfd > 0 ) {
					return $cfd;
				}
			}

			$start_time = substr( (string) ( isset( $booking->booking_time ) ? $booking->booking_time : '' ), 0, 5 );
			$end_time   = substr( (string) ( isset( $booking->booking_end_time ) ? $booking->booking_end_time : '' ), 0, 5 );
			if ( $this->is_valid_time( $start_time ) && $this->is_valid_time( $end_time ) ) {
				$start_minutes = $this->time_to_minutes( $start_time );
				$end_minutes   = $this->time_to_minutes( $end_time );
				if ( $end_minutes > $start_minutes ) { return $end_minutes - $start_minutes; }
			}

			if ( $service ) {
				$rules = $this->resolve_effective_rules( $service, $staff );
				return max( 1, absint( isset( $rules['duration'] ) ? $rules['duration'] : 30 ) );
			}

			return 0;
		}

		private function format_booking_display_time( $booking ) {
			$start_time = substr( (string) ( isset( $booking->booking_time ) ? $booking->booking_time : '' ), 0, 5 );
			if ( '' === $start_time ) { return ''; }
			$duration = $this->get_booking_display_duration_minutes( $booking );
			// If a customer-facing duration is set on the service, compute the
			// display end from start + CFD rather than using the stored
			// booking_end_time (which is the internal footprint).
			$end_time = '';
			$service = ! empty( $booking->service_id ) ? $this->find_service( $booking->service_id ) : false;
			$staff   = ! empty( $booking->staff_id ) ? $this->find_staff( $booking->staff_id ) : false;
			if ( $service ) {
				$rules_for_cfd = $this->resolve_effective_rules( $service, $staff );
				$cfd_local = absint( isset( $rules_for_cfd['customer_facing_duration'] ) ? $rules_for_cfd['customer_facing_duration'] : 0 );
				if ( $cfd_local > 0 && $this->is_valid_time( $start_time ) ) {
					$end_time = $this->minutes_to_time( $this->time_to_minutes( $start_time ) + $cfd_local );
				}
			}
			if ( '' === $end_time ) {
				$end_time = substr( (string) ( isset( $booking->booking_end_time ) ? $booking->booking_end_time : '' ), 0, 5 );
			}
			if ( '' === $end_time && $duration > 0 && $this->is_valid_time( $start_time ) ) {
				$end_time = $this->minutes_to_time( $this->time_to_minutes( $start_time ) + $duration );
			}
			if ( '' !== $end_time && $duration > 0 ) {
				return $start_time . ' to ' . $end_time . ' (' . $duration . ' ' . ( 1 === $duration ? 'min' : 'mins' ) . ')';
			}
			return $start_time;
		}

		private function get_booking_datetime_strings_for_calendar( $booking ) {
			$tz = wp_timezone();
			$date = isset( $booking->booking_date ) ? (string) $booking->booking_date : '';
			$start_time = substr( (string) ( isset( $booking->booking_time ) ? $booking->booking_time : '00:00' ), 0, 5 );

			// Prefer customer-facing duration for the calendar end (don't block
			// the customer's diary for the full internal footprint).
			$end_time = '';
			$service = ! empty( $booking->service_id ) ? $this->find_service( $booking->service_id ) : false;
			$staff   = ! empty( $booking->staff_id ) ? $this->find_staff( $booking->staff_id ) : false;
			if ( $service ) {
				$rules_for_cfd = $this->resolve_effective_rules( $service, $staff );
				$cfd_local = absint( isset( $rules_for_cfd['customer_facing_duration'] ) ? $rules_for_cfd['customer_facing_duration'] : 0 );
				if ( $cfd_local > 0 && $this->is_valid_time( $start_time ) ) {
					$end_time = $this->minutes_to_time( $this->time_to_minutes( $start_time ) + $cfd_local );
				}
			}
			if ( '' === $end_time ) {
				$end_time = substr( (string) ( isset( $booking->booking_end_time ) ? $booking->booking_end_time : '' ), 0, 5 );
			}

			try {
				$start = new DateTime( $date . ' ' . $start_time, $tz );
				$end = '' !== $end_time ? new DateTime( $date . ' ' . $end_time, $tz ) : clone $start;
				if ( '' === $end_time ) { $end->modify( '+30 minutes' ); }
			} catch ( Exception $e ) { return array( '', '' ); }
			$start->setTimezone( new DateTimeZone( 'UTC' ) );
			$end->setTimezone( new DateTimeZone( 'UTC' ) );
			return array( $start->format( 'Ymd\THis\Z' ), $end->format( 'Ymd\THis\Z' ) );
		}

		private function get_google_calendar_url( $booking ) {
			list( $start, $end ) = $this->get_booking_datetime_strings_for_calendar( $booking );
			if ( '' === $start || '' === $end ) { return ''; }

			$title       = 'Booking: ' . ( isset( $booking->service_name ) ? $booking->service_name : '' );
			$summary_url = $this->get_booking_summary_url( $booking );
			$details     = 'Reference: ' . ( isset( $booking->booking_reference ) ? $booking->booking_reference : '' );
			if ( ! empty( $booking->customer_interests ) ) {
				$details .= "\n\nTopics: " . $booking->customer_interests;
			}
			$details .= "\n\nBooking summary:\n" . $summary_url;
			$location    = isset( $booking->location_name ) ? $booking->location_name : '';

			return 'https://calendar.google.com/calendar/render?' . http_build_query(
				array(
					'action'   => 'TEMPLATE',
					'text'     => $title,
					'dates'    => $start . '/' . $end,
					'details'  => $details,
					'location' => $location,
				),
				'',
				'&',
				PHP_QUERY_RFC3986
			);
		}

		private function build_icalendar_content( $booking ) {
			list( $start, $end ) = $this->get_booking_datetime_strings_for_calendar( $booking );
			if ( '' === $start || '' === $end ) { return ''; }
			$host = wp_parse_url( home_url(), PHP_URL_HOST );
			$uid = ( ! empty( $booking->booking_reference ) ? $booking->booking_reference : ( 'booking-' . (int) $booking->id ) ) . '@' . $host;
			$summary = 'Booking: ' . ( isset( $booking->service_name ) ? $booking->service_name : '' );
			$summary_url = $this->get_booking_summary_url( $booking );
			$description = 'Reference: ' . ( isset( $booking->booking_reference ) ? $booking->booking_reference : '' );
			if ( ! empty( $booking->customer_interests ) ) {
				$description .= "\nTopics: " . $booking->customer_interests;
			}
			$description .= "\nBooking summary: " . $summary_url;
			$location = isset( $booking->location_name ) ? $booking->location_name : '';
			$escape = function( $text ) { return str_replace( array( "\r", "\n", ',', ';' ), array( '', '\n', '\,', '\;' ), (string) $text ); };
			return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//" . wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) . "//Booking//EN\r\nBEGIN:VEVENT\r\nUID:" . $escape( $uid ) . "\r\nDTSTAMP:" . gmdate( 'Ymd\THis\Z' ) . "\r\nDTSTART:" . $start . "\r\nDTEND:" . $end . "\r\nSUMMARY:" . $escape( $summary ) . "\r\nDESCRIPTION:" . $escape( $description ) . "\r\nURL:" . $escape( $summary_url ) . "\r\nLOCATION:" . $escape( $location ) . "\r\nEND:VEVENT\r\nEND:VCALENDAR";
		}

		private function get_icalendar_data_url( $booking ) {
			if ( ! $booking || empty( $booking->id ) ) { return ''; }
			return add_query_arg( array( 'rgl_booking_ics' => 1, 'booking_id' => (int) $booking->id, 'token' => $this->get_booking_access_token_for_booking( $booking ) ), home_url( '/' ) );
		}

		private function get_mail_from_name() {
			return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		}

		private function get_mail_from_address() {
			$settings = $this->get_settings();
			$email = isset( $settings['email_from_address'] ) ? sanitize_email( $settings['email_from_address'] ) : '';
			if ( ! is_email( $email ) ) { $email = get_option( 'admin_email' ); }
			if ( ! is_email( $email ) ) { $email = 'wordpress@' . wp_parse_url( home_url(), PHP_URL_HOST ); }
			return $email;
		}

		private function get_mail_headers() {
			$from_name  = $this->get_mail_from_name();
			$from_email = $this->get_mail_from_address();
			return array(
				'From: "' . str_replace( '"', '\\"', $from_name ) . '" <' . $from_email . '>',
				'Reply-To: "' . str_replace( '"', '\\"', $from_name ) . '" <' . $from_email . '>',
				'Content-Type: text/html; charset=UTF-8',
			);
		}

		private function format_email_html( $message ) {
			$message = str_replace( array( "\r\n", "\r" ), "\n", (string) $message );
			$message = nl2br( $message, false );
			return '<div style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.55; color: #222;">' . $message . '</div>';
		}

		private function get_mail_log_option_key() {
			return 'rgl_booking_final_mail_log';
		}

		private function add_mail_log_entry( $message, $context = array() ) {
			$log = get_option( $this->get_mail_log_option_key(), array() );
			if ( ! is_array( $log ) ) { $log = array(); }
			$entry = array(
				'time'    => current_time( 'mysql' ),
				'message' => sanitize_text_field( (string) $message ),
				'context' => array(),
			);
			foreach ( (array) $context as $key => $value ) {
				if ( is_scalar( $value ) ) {
					$entry['context'][ sanitize_key( $key ) ] = sanitize_text_field( (string) $value );
				}
			}
			$log[] = $entry;
			$log = array_slice( $log, -50 );
			update_option( $this->get_mail_log_option_key(), $log, false );
		}

		public function log_wp_mail_failed( $wp_error ) {
			if ( ! is_wp_error( $wp_error ) ) { return; }
			$this->add_mail_log_entry(
				'wp_mail failed: ' . $wp_error->get_error_message(),
				array( 'error_code' => $wp_error->get_error_code() )
			);
		}

		private function send_mail( $to, $subject, $message, $headers = array(), $is_html = false, $audience = 'internal' ) {
			$from_name  = $this->get_mail_from_name();
			$from_email = $this->get_mail_from_address();
			// If the caller has already produced a complete HTML document, send it
			// as-is. Otherwise wrap the plain-text body in the basic HTML shell.
			$message    = $is_html ? (string) $message : $this->format_email_html( $message );
			if ( function_exists( 'tj_appt_licence_email_message' ) ) {
				$message = tj_appt_licence_email_message( $message, true, $audience );
			}
			$headers    = empty( $headers ) ? $this->get_mail_headers() : $headers;

			/*
			 * SMTP plugin compatibility:
			 * - Use wp_mail() only.
			 * - Do not touch PHPMailer->From, PHPMailer->Sender, Host, SMTPAuth, etc.
			 * - Let Post SMTP, WP Mail SMTP, FluentSMTP, or the server mail transport own delivery.
			 */
			$name_filter  = function() use ( $from_name ) { return $from_name; };
			$email_filter = function() use ( $from_email ) { return $from_email; };

			add_filter( 'wp_mail_from_name', $name_filter, 50 );
			add_filter( 'wp_mail_from', $email_filter, 50 );

			$sent = wp_mail( $to, $subject, $message, $headers );

			remove_filter( 'wp_mail_from_name', $name_filter, 50 );
			remove_filter( 'wp_mail_from', $email_filter, 50 );

			if ( ! $sent ) {
				$this->add_mail_log_entry(
					'wp_mail returned false.',
					array( 'to' => $to, 'subject' => $subject )
				);
			}

			return $sent;
		}

		public function render_booking_ics_file() {
			if ( empty( $_GET['rgl_booking_ics'] ) ) { return; }
			$booking_id = isset( $_GET['booking_id'] ) ? absint( $_GET['booking_id'] ) : 0;
			$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
			$booking = $booking_id ? $this->get_booking_by_id( $booking_id ) : false;
			if ( ! $booking || '' === $token || ! hash_equals( $this->get_booking_access_token_for_booking( $booking ), $token ) ) {
				status_header( 404 );
				exit;
			}
			$ics = $this->build_icalendar_content( $booking );
			if ( '' === $ics ) { status_header( 404 ); exit; }
			nocache_headers();
			header( 'Content-Type: text/calendar; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="booking-' . sanitize_file_name( (string) $booking->booking_reference ) . '.ics"' );
			echo $ics;
			exit;
		}

		public function render_booking_summary_page() {
			if ( empty( $_GET['rgl_booking_summary'] ) ) { return; }

			$booking_id = isset( $_GET['booking_id'] ) ? absint( $_GET['booking_id'] ) : 0;
			$token      = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
			$booking    = $booking_id ? $this->get_booking_by_id( $booking_id ) : false;

			if (
				! $booking ||
				'' === $token ||
				! hash_equals( $this->get_booking_access_token_for_booking( $booking ), $token )
			) {
				status_header( 404 );
				wp_die( esc_html__( 'Booking summary not found.', 'rgl-booking' ) );
			}

			$notes          = $this->get_effective_booking_summary_notes( $booking );
			$display_labels = $this->get_booking_display_labels();
			$display_date   = $this->format_booking_display_date( $booking );
			$display_time   = $this->format_booking_display_time( $booking );
			$status         = isset( $booking->booking_status ) ? (string) $booking->booking_status : self::STATUS_CONFIRMED;
			$google_url     = self::STATUS_CONFIRMED === $status ? $this->get_google_calendar_url( $booking ) : '';
			$ics_url        = self::STATUS_CONFIRMED === $status ? $this->get_icalendar_data_url( $booking ) : '';
			$reschedule_reason = '';
			$reschedule_url = $this->booking_is_self_reschedulable( $booking, $reschedule_reason ) ? $this->get_reschedule_url( $booking ) : '';
			$cancel_reason = '';
			$cancel_url = $this->booking_is_self_cancellable( $booking, $cancel_reason ) ? $this->get_cancellation_url( $booking ) : '';
			$contact_note  = $this->get_comm_method_customer_note( isset( $booking->location_id ) ? $booking->location_id : '' );
			$status_label   = $this->get_booking_status_label( $status );

			$show_arrival_cover = ! empty( $_GET['rgl_booking_transition'] );
			if ( $show_arrival_cover ) {
				add_action(
					'wp_head',
					static function() {
						?>
						<style id="rgl-booking-arrival-cover-css">
							html.rgl-booking-arriving body{overflow:hidden!important;background:#fff!important}
							html.rgl-booking-arriving body>*{visibility:hidden!important}
							html.rgl-booking-arriving body::before{content:""!important;display:block!important;visibility:visible!important;position:fixed!important;inset:0!important;z-index:2147483646!important;background:#fff!important}
							html.rgl-booking-arriving body::after{content:"Just confirming your booking"!important;display:block!important;visibility:visible!important;position:fixed!important;left:50%!important;top:50%!important;z-index:2147483647!important;transform:translate(-50%,-50%)!important;width:min(360px,calc(100vw - 48px))!important;color:#111!important;font:700 18px/1.35 system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif!important;text-align:center!important;white-space:normal!important}
						</style>
						<script>
						(function(){
							var root=document.documentElement;
							root.classList.add('rgl-booking-arriving');
							var revealed=false;
							function reveal(){
								if(revealed){return;}
								revealed=true;
								window.requestAnimationFrame(function(){
									root.classList.remove('rgl-booking-arriving');
									try{
										var clean=new URL(window.location.href);
										clean.searchParams.delete('rgl_booking_transition');
										window.history.replaceState({},document.title,clean.toString());
									}catch(ignore){}
								});
							}
							if(document.readyState==='complete'){
								window.setTimeout(reveal,80);
							}else{
								window.addEventListener('load',function(){window.setTimeout(reveal,80);},{once:true});
							}
							window.setTimeout(reveal,5000);
						})();
						</script>
						<?php
					},
					-999
				);
			}

			nocache_headers();
			get_header();
			?>
			<style>
				<?php echo $this->get_public_action_button_css(); ?>
				.rgl-booking-summary-page{padding:40px 20px}.rgl-summary-wrap{max-width:860px;margin:0 auto;padding:28px;border:1px solid #d0d5dd;border-radius:16px;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#fff}.rgl-summary-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:20px 0}.rgl-summary-card{background:#f9fafb;border:1px solid #eaecf0;border-radius:12px;padding:14px}.rgl-summary-notes{margin-top:22px;padding:18px;background:#fff7ed;border:1px solid #fed7aa;border-radius:12px}@media(max-width:700px){.rgl-booking-summary-page{padding:24px 14px}.rgl-summary-grid{grid-template-columns:1fr}.rgl-summary-wrap{padding:20px}}
			</style>
			<main id="primary" class="site-main rgl-booking-summary-page">
				<div class="rgl-summary-wrap">
					<h1>Booking Summary</h1>
					<p><strong>Reference:</strong> <?php echo esc_html( $booking->booking_reference ); ?></p>

					<div class="rgl-summary-grid">
						<div class="rgl-summary-card"><strong>Status</strong><br><?php echo esc_html( $status_label ); ?></div>
						<div class="rgl-summary-card"><strong><?php echo esc_html( $display_labels['service'] ); ?></strong><br><?php echo esc_html( $booking->service_name ); ?></div>
						<?php if ( ! empty( $booking->staff_name ) ) : ?><div class="rgl-summary-card"><strong><?php echo esc_html( $display_labels['staff'] ); ?></strong><br><?php echo esc_html( $booking->staff_name ); ?></div><?php endif; ?>
						<div class="rgl-summary-card"><strong>Date</strong><br><?php echo esc_html( $display_date ); ?></div>
						<div class="rgl-summary-card"><strong>Time</strong><br><?php echo esc_html( $display_time ); ?> (UK time)</div>
						<?php if ( ! empty( $booking->location_name ) ) : ?><div class="rgl-summary-card"><strong><?php echo esc_html( $display_labels['location'] ); ?></strong><br><?php echo esc_html( $booking->location_name ); ?></div><?php endif; ?>
						<?php if ( '' !== $contact_note ) : ?><div class="rgl-summary-card"><strong>Contact details</strong><br><?php echo nl2br( esc_html( $contact_note ) ); ?></div><?php endif; ?>
					</div>

					<?php
					$summary_customer_message = isset( $booking->customer_notes ) ? trim( (string) $booking->customer_notes ) : '';
					if ( '' !== $summary_customer_message ) : ?>
						<div class="rgl-summary-notes">
							<h2>Your message</h2>
							<p><?php echo nl2br( esc_html( $summary_customer_message ) ); ?></p>
						</div>
					<?php endif; ?>

					<?php
					$summary_interests = isset( $booking->customer_interests ) ? trim( (string) $booking->customer_interests ) : '';
					if ( '' !== $summary_interests ) : ?>
						<div class="rgl-summary-notes">
							<h2>You asked about</h2>
							<p><?php echo esc_html( $summary_interests ); ?></p>
						</div>
					<?php endif; ?>

					<?php if ( '' !== trim( wp_strip_all_tags( $notes ) ) ) : ?>
						<div class="rgl-summary-notes">
							<h2>Additional notes</h2>
							<?php echo wpautop( wp_kses_post( $notes ) ); ?>
						</div>
					<?php endif; ?>

					<div class="rgl-summary-actions rgl-public-actions">
						<?php if ( $google_url ) : ?><a class="rgl-public-action rgl-public-action--secondary" href="<?php echo esc_url( $google_url ); ?>" target="_blank" rel="noopener">Add to Google Calendar</a><?php endif; ?>
						<?php if ( $ics_url ) : ?><a class="rgl-public-action rgl-public-action--secondary" href="<?php echo esc_url( $ics_url ); ?>">Add to iCalendar</a><?php endif; ?>
						<?php if ( $reschedule_url ) : ?><a class="rgl-public-action rgl-public-action--primary" href="<?php echo esc_url( $reschedule_url ); ?>">Change appointment</a><?php endif; ?>
						<?php if ( $cancel_url ) : ?><a class="rgl-public-action rgl-public-action--danger" href="<?php echo esc_url( $cancel_url ); ?>">Cancel booking</a><?php endif; ?>
					</div>
				</div>
			</main>
			<?php
			get_footer();
			exit;
		}


		private function style_variable_map() {
			return array(
				'style_typography_font_family'         => '--bb-font-family',
				'style_form_padding'                   => '--bb-form-padding',
				'style_form_column_gap'                => '--bb-form-column-gap',
				'style_form_row_gap'                   => '--bb-form-row-gap',
				'style_form_border_radius'             => '--bb-form-radius',
				'style_form_border_width'              => '--bb-form-border-width',
				'style_form_border_color'              => '--bb-form-border-color',
				'style_form_title_padding'             => '--bb-title-padding',
				'style_form_title_color'               => '--bb-title-color',
				'style_form_title_background'          => '--bb-title-bg',
				'style_form_title_font_size'           => '--bb-title-font-size',
				'style_form_title_font_weight'         => '--bb-title-font-weight',
				'style_form_title_letter_spacing'      => '--bb-title-letter-spacing',
				'style_form_intro_font_size'           => '--bb-intro-font-size',
				'style_form_intro_font_weight'         => '--bb-intro-font-weight',
				'style_form_intro_letter_spacing'      => '--bb-intro-letter-spacing',
				'style_field_label_padding'            => '--bb-label-padding',
				'style_field_label_color'              => '--bb-label-color',
				'style_field_label_background'         => '--bb-label-bg',
				'style_label_border_radius'            => '--bb-label-radius',
				'style_label_border_width'             => '--bb-label-border-width',
				'style_label_border_color'             => '--bb-label-border-color',
				'style_label_font_size'                => '--bb-label-font-size',
				'style_label_font_weight'              => '--bb-label-font-weight',
				'style_label_letter_spacing'           => '--bb-label-letter-spacing',
				'style_field_padding'                  => '--bb-field-padding',
				'style_field_border_radius'            => '--bb-field-radius',
				'style_field_border_width'             => '--bb-field-border-width',
				'style_field_border_color'             => '--bb-field-border-color',
				'style_field_font_size'                => '--bb-field-font-size',
				'style_field_font_weight'              => '--bb-field-font-weight',
				'style_field_letter_spacing'           => '--bb-field-letter-spacing',
				'style_button_text_color'              => '--bb-button-color',
				'style_button_background'              => '--bb-button-bg',
				'style_button_hover_text_color'        => '--bb-button-hover-color',
				'style_button_hover_background'        => '--bb-button-hover-bg',
				'style_button_font_size'               => '--bb-button-font-size',
				'style_button_font_weight'             => '--bb-button-font-weight',
				'style_button_letter_spacing'          => '--bb-button-letter-spacing',
				'style_button_border_radius'           => '--bb-button-radius',
				'style_button_border_width'            => '--bb-button-border-width',
				'style_button_border_color'            => '--bb-button-border-color',
				'style_button_padding'                 => '--bb-button-padding',
				'style_button_margin'                  => '--bb-button-margin',
				'style_reset_button_text_color'        => '--bb-reset-button-color',
				'style_reset_button_background'        => '--bb-reset-button-bg',
				'style_reset_button_hover_text_color'  => '--bb-reset-button-hover-color',
				'style_reset_button_hover_background'  => '--bb-reset-button-hover-bg',
				'style_reset_button_font_size'         => '--bb-reset-button-font-size',
				'style_reset_button_font_weight'       => '--bb-reset-button-font-weight',
				'style_reset_button_letter_spacing'    => '--bb-reset-button-letter-spacing',
				'style_reset_button_border_radius'     => '--bb-reset-button-radius',
				'style_reset_button_border_width'      => '--bb-reset-button-border-width',
				'style_reset_button_border_color'      => '--bb-reset-button-border-color',
				'style_reset_button_padding'           => '--bb-reset-button-padding',
			);
		}


		private function normalize_form_layout_mode( $layout ) {
			$layout = sanitize_key( (string) $layout );

			// Backwards compatibility for sites already saved with the old values.
			if ( 'one' === $layout ) {
				return 'one_date_last';
			}
			if ( 'two' === $layout || '' === $layout ) {
				return 'two_date_last';
			}

			$allowed = array(
				'one_date_last',
				'two_date_last',
			);

			return in_array( $layout, $allowed, true ) ? $layout : 'two_date_last';
		}

		private function is_one_column_form_layout( $layout ) {
			return 0 === strpos( $this->normalize_form_layout_mode( $layout ), 'one_' );
		}

		private function form_layout_options() {
			return array(
				'one_date_last' => 'One Column — Date Last',
				'two_date_last' => 'Two Column — Date Last',
			);
		}

		private function style_setting_keys() {
			return array_merge( array( 'style_form_layout', 'style_button_text', 'style_reset_button_text' ), array_keys( $this->style_variable_map() ) );
		}

		private function sanitize_style_css_value( $value ) {
			$value = sanitize_text_field( wp_unslash( $value ) );
			$value = str_replace( array( ';', '{', '}', '<', '>' ), '', $value );
			return trim( $value );
		}

		private function sanitize_style_settings( $posted, $existing = array() ) {
			$defaults = $this->default_settings();
			$settings = wp_parse_args( is_array( $existing ) ? $existing : array(), $defaults );

			if ( array_key_exists( 'form_title', $posted ) ) {
				$settings['form_title'] = sanitize_text_field( wp_unslash( $posted['form_title'] ) );
			}
			if ( array_key_exists( 'form_intro', $posted ) ) {
				$settings['form_intro'] = sanitize_text_field( wp_unslash( $posted['form_intro'] ) );
			}

			foreach ( $this->style_setting_keys() as $key ) {
				if ( ! array_key_exists( $key, $posted ) ) {
					continue;
				}
				$value = $this->sanitize_style_css_value( $posted[ $key ] );
				if ( 'style_form_layout' === $key ) {
					$value = $this->normalize_form_layout_mode( $value );
				}
				if ( '' === $value && isset( $defaults[ $key ] ) ) {
					$value = $defaults[ $key ];
				}
				$settings[ $key ] = $value;
			}

			return $settings;
		}

		private function css_prop( $property, $value ) {
			$value = $this->sanitize_style_css_value( $value );
			if ( '' === $value ) {
				return '';
			}
			return $property . ':' . $value . ';';
		}

		private function build_style_variable_css( $settings ) {
			$settings = wp_parse_args( is_array( $settings ) ? $settings : array(), $this->default_settings() );
			$css = '';
			foreach ( $this->style_variable_map() as $setting_key => $css_variable ) {
				if ( empty( $settings[ $setting_key ] ) ) {
					continue;
				}
				$css .= $this->css_prop( $css_variable, $settings[ $setting_key ] );
			}
			$grid_columns = $this->is_one_column_form_layout( isset( $settings['style_form_layout'] ) ? $settings['style_form_layout'] : '' ) ? '1fr' : 'repeat(2,minmax(0,1fr))';
			$css .= $this->css_prop( '--bb-grid-columns', $grid_columns );
			return $css;
		}

		private function build_frontend_style_css( $settings ) {
			$settings = wp_parse_args( is_array( $settings ) ? $settings : array(), $this->default_settings() );

			$css  = '.rgl-booking-form-wrap{' . $this->build_style_variable_css( $settings ) . '}';
			$css .= '.rgl-booking-form-wrap{box-sizing:border-box;max-width:820px;margin-left:auto;margin-right:auto;background:#fff;font-family:var(--bb-font-family,inherit);padding:var(--bb-form-padding,20px);border-radius:var(--bb-form-radius,12px);border-width:var(--bb-form-border-width,1px);border-color:var(--bb-form-border-color,#cccccc);border-style:solid;}';
			$css .= '.rgl-booking-form-wrap h3{margin:0;padding:var(--bb-title-padding,0 0 12px 0);color:var(--bb-title-color,#111111);background:var(--bb-title-bg,#ffffff);font-size:var(--bb-title-font-size,24px);font-weight:var(--bb-title-font-weight,600);letter-spacing:var(--bb-title-letter-spacing,0);}';
			$css .= '.rgl-booking-form-wrap .rgl-booking-intro{font-size:var(--bb-intro-font-size,16px);font-weight:var(--bb-intro-font-weight,400);letter-spacing:var(--bb-intro-letter-spacing,0);}';
			$css .= '.rgl-booking-form-wrap label{display:block;padding:var(--bb-label-padding,0);color:var(--bb-label-color,#111111);background:var(--bb-label-bg,transparent);border-radius:var(--bb-label-radius,0);border-width:var(--bb-label-border-width,0);border-color:var(--bb-label-border-color,transparent);font-size:var(--bb-label-font-size,16px);font-weight:var(--bb-label-font-weight,600);letter-spacing:var(--bb-label-letter-spacing,0);border-style:solid;}';
			$css .= '.rgl-booking-form-wrap input,.rgl-booking-form-wrap select,.rgl-booking-form-wrap textarea{box-sizing:border-box;width:100%;margin-top:6px;padding:var(--bb-field-padding,10px);line-height:1.4;border-radius:var(--bb-field-radius,4px);border-width:var(--bb-field-border-width,1px);border-color:var(--bb-field-border-color,#8c8f94);font-size:var(--bb-field-font-size,16px);font-weight:var(--bb-field-font-weight,400);letter-spacing:var(--bb-field-letter-spacing,0);border-style:solid;}';
			$css .= '.rgl-booking-form-wrap select{min-height:44px;}';
			$css .= '.rgl-booking-form-wrap button,.rgl-booking-form-wrap .rgl-booking-submit{cursor:pointer;color:var(--bb-button-color,#ffffff) !important;background:var(--bb-button-bg,#111111) !important;font-size:var(--bb-button-font-size,16px);font-weight:var(--bb-button-font-weight,600);letter-spacing:var(--bb-button-letter-spacing,0);border-radius:var(--bb-button-radius,8px);border-width:var(--bb-button-border-width,0);border-color:var(--bb-button-border-color,transparent);padding:var(--bb-button-padding,12px 16px);border-style:solid;transition:background-color .18s ease,color .18s ease,border-color .18s ease;}';
			$css .= '.rgl-booking-form-wrap button:hover,.rgl-booking-form-wrap button:focus,.rgl-booking-form-wrap .rgl-booking-submit:hover,.rgl-booking-form-wrap .rgl-booking-submit:focus{color:var(--bb-button-hover-color,#ffffff) !important;background:var(--bb-button-hover-bg,#333333) !important;}';
			$css .= '.rgl-booking-form-wrap .rgl-booking-reset{cursor:pointer;color:var(--bb-reset-button-color,#111111) !important;background:var(--bb-reset-button-bg,#ffffff) !important;font-size:var(--bb-reset-button-font-size,16px);font-weight:var(--bb-reset-button-font-weight,600);letter-spacing:var(--bb-reset-button-letter-spacing,0);border-radius:var(--bb-reset-button-radius,8px);border-width:var(--bb-reset-button-border-width,1px);border-color:var(--bb-reset-button-border-color,#111111);padding:var(--bb-reset-button-padding,12px 16px);border-style:solid;transition:background-color .18s ease,color .18s ease,border-color .18s ease;}';
			$css .= '.rgl-booking-form-wrap .rgl-booking-reset:hover,.rgl-booking-form-wrap .rgl-booking-reset:focus{color:var(--bb-reset-button-hover-color,#111111) !important;background:var(--bb-reset-button-hover-bg,#f3f4f6) !important;}';
			$css .= '.rgl-booking-submit-wrap{margin:var(--bb-button-margin,16px 0 0 0);display:flex;gap:10px;align-items:center;flex-wrap:wrap;}';
			$css .= '.rgl-booking-grid{display:grid;grid-template-columns:var(--bb-grid-columns,repeat(2,minmax(0,1fr)));column-gap:var(--bb-form-column-gap,16px);row-gap:var(--bb-form-row-gap,16px)}.rgl-booking-grid .rgl-full{grid-column:1/-1}';
			$css .= '.rgl-booking-notice{margin-top:12px;font-weight:600}.rgl-booking-success{color:#067647}.rgl-booking-error{color:#b42318}.rgl-staff-wrap.is-hidden{display:none}.rgl-quantity-hint{display:block;margin-top:6px;font-size:13px;opacity:.8}.rgl-sr-only{position:absolute!important;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}.rgl-interests-wrap{grid-column:1/-1}.rgl-interests-wrap[hidden]{display:none}.rgl-interests-fieldset{border:1px solid #d0d5dd;border-radius:8px;padding:12px 14px;margin:0}.rgl-interests-legend{padding:0 6px;font-weight:600}.rgl-interests-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px 18px;margin-top:8px}.rgl-booking-form .rgl-interest-option{display:flex!important;flex-direction:row!important;align-items:center!important;gap:8px!important;font-weight:400!important;cursor:pointer;margin:0!important;padding:0!important;line-height:1.4}.rgl-booking-form .rgl-interest-option input[type=checkbox]{margin:0!important;padding:0!important;width:18px!important;height:18px!important;min-width:18px!important;min-height:18px!important;flex:0 0 18px!important;display:inline-block!important;vertical-align:middle!important;position:static!important;float:none!important;-webkit-appearance:checkbox!important;appearance:checkbox!important}.rgl-booking-form .rgl-interest-option span{display:inline-block;vertical-align:middle}.rgl-interests-hint{margin-top:8px;font-size:13px;opacity:.85;min-height:1em}.rgl-booking-privacy{margin:16px 0 0;font-size:13px;line-height:1.55;color:#555;background:#f7f7f5;border:1px solid #e6e6e2;border-radius:8px;padding:10px 14px}.rgl-booking-redirect-overlay{position:fixed!important;inset:0!important;z-index:2147483647!important;display:flex!important;align-items:center!important;justify-content:center!important;background:#fff!important;color:#111!important;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif!important;text-align:center!important;padding:24px!important;box-sizing:border-box!important}.rgl-booking-redirect-card{display:flex;flex-direction:column;align-items:center;gap:14px;max-width:360px}.rgl-booking-redirect-spinner{display:block;width:34px;height:34px;border:4px solid #dedede;border-top-color:#111;border-radius:50%;animation:rgl-booking-spin .75s linear infinite}.rgl-booking-redirect-text{font-size:18px;font-weight:700;line-height:1.35}@keyframes rgl-booking-spin{to{transform:rotate(360deg)}}@media(max-width:767px){.rgl-booking-grid{grid-template-columns:1fr}.rgl-interests-options{grid-template-columns:1fr}}';

			return $css;
		}


		private function get_form_label( $settings, $key, $fallback ) {
			$value = isset( $settings[ $key ] ) ? trim( (string) $settings[ $key ] ) : '';
			return '' !== $value ? $value : $fallback;
		}

		private function singularize_label_for_placeholder( $label ) {
			$label = trim( wp_strip_all_tags( (string) $label ) );
			if ( '' === $label ) {
				return '';
			}
			$words = preg_split( '/\s+/', $label );
			$last_index = count( $words ) - 1;
			$last = $words[ $last_index ];
			$lower = strtolower( $last );
			if ( strlen( $last ) > 3 && preg_match( '/ies$/i', $last ) ) {
				$last = substr( $last, 0, -3 ) . 'y';
			} elseif ( strlen( $last ) > 3 && preg_match( '/s$/i', $last ) && ! preg_match( '/ss$/i', $last ) ) {
				$last = substr( $last, 0, -1 );
			}
			$words[ $last_index ] = $last;
			return implode( ' ', $words );
		}

		private function select_placeholder_for_label( $label ) {
			$singular = $this->singularize_label_for_placeholder( $label );
			return '' !== $singular ? 'Select ' . lcfirst( $singular ) : 'Select';
		}

		private function get_settings() {
			$settings = wp_parse_args(
				get_option( self::OPTION_SETTINGS, array() ),
				$this->default_settings()
			);

			// Name, email and phone are mandatory booking contact details.
			// Force the legacy visibility setting on so an older saved value cannot
			// hide the phone field after upgrading to 1.12.1.
			$settings['show_phone_field'] = 1;

			return $settings;
		}

		/**
		 * Does the booking table exist?
		 *
		 * Memoised for the life of the request. This is called dozens of times
		 * per request (every slot build, every admin list, every AJAX handler)
		 * and SHOW TABLES is not a cheap statement to repeat. install_schema()
		 * resets the cache so a freshly created table is seen immediately.
		 */
		private function table_exists() {
			if ( null !== $this->table_exists_cache ) {
				return $this->table_exists_cache;
			}
			global $wpdb;
			$table = $this->table;
			$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
			$this->table_exists_cache = ( $found === $table );
			return $this->table_exists_cache;
		}

		private function booking_quantity_column_exists() {
			$column = 'spaces_booked';
			if ( array_key_exists( $column, $this->column_exists_cache ) ) {
				return $this->column_exists_cache[ $column ];
			}
			global $wpdb;
			if ( ! $this->table_exists() ) {
				$this->column_exists_cache[ $column ] = false;
				return false;
			}
			$found = $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM ' . $this->table . ' LIKE %s', $column ) );
			$this->column_exists_cache[ $column ] = ( $column === $found );
			return $this->column_exists_cache[ $column ];
		}

		private function ensure_booking_quantity_column() {
			global $wpdb;
			if ( $this->booking_quantity_column_exists() || ! $this->table_exists() ) {
				return;
			}
			$changed = $wpdb->query( 'ALTER TABLE ' . $this->table . ' ADD spaces_booked INT UNSIGNED NOT NULL DEFAULT 1 AFTER booking_reference' );
			if ( false !== $changed ) {
				$this->column_exists_cache['spaces_booked'] = true;
			}
		}

		private function customer_interests_column_exists() {
			$column = 'customer_interests';
			if ( array_key_exists( $column, $this->column_exists_cache ) ) {
				return $this->column_exists_cache[ $column ];
			}
			global $wpdb;
			if ( ! $this->table_exists() ) {
				$this->column_exists_cache[ $column ] = false;
				return false;
			}
			$found = $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM ' . $this->table . ' LIKE %s', $column ) );
			$this->column_exists_cache[ $column ] = ( $column === $found );
			return $this->column_exists_cache[ $column ];
		}

		private function ensure_customer_interests_column() {
			global $wpdb;
			if ( $this->customer_interests_column_exists() || ! $this->table_exists() ) {
				return;
			}
			$changed = $wpdb->query( 'ALTER TABLE ' . $this->table . ' ADD customer_interests LONGTEXT NULL AFTER customer_notes' );
			if ( false !== $changed ) {
				$this->column_exists_cache['customer_interests'] = true;
			}
		}

		private function cancellation_reason_column_exists() {
			$column = 'cancellation_reason';
			if ( array_key_exists( $column, $this->column_exists_cache ) ) {
				return $this->column_exists_cache[ $column ];
			}
			global $wpdb;
			if ( ! $this->table_exists() ) {
				$this->column_exists_cache[ $column ] = false;
				return false;
			}
			$found = $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM ' . $this->table . ' LIKE %s', $column ) );
			$this->column_exists_cache[ $column ] = ( $column === $found );
			return $this->column_exists_cache[ $column ];
		}

		private function ensure_cancellation_reason_column() {
			global $wpdb;
			if ( $this->cancellation_reason_column_exists() || ! $this->table_exists() ) {
				return;
			}
			$changed = $wpdb->query( 'ALTER TABLE ' . $this->table . ' ADD cancellation_reason LONGTEXT NULL AFTER customer_interests' );
			if ( false !== $changed ) {
				$this->column_exists_cache['cancellation_reason'] = true;
			}
		}

		private function reminder_sent_column_exists() {
			$column = 'reminder_sent_at';
			if ( array_key_exists( $column, $this->column_exists_cache ) ) {
				return $this->column_exists_cache[ $column ];
			}
			global $wpdb;
			if ( ! $this->table_exists() ) {
				$this->column_exists_cache[ $column ] = false;
				return false;
			}
			$found = $wpdb->get_var( $wpdb->prepare( 'SHOW COLUMNS FROM ' . $this->table . ' LIKE %s', $column ) );
			$this->column_exists_cache[ $column ] = ( $column === $found );
			return $this->column_exists_cache[ $column ];
		}

		private function ensure_reminder_sent_column() {
			global $wpdb;
			if ( $this->reminder_sent_column_exists() || ! $this->table_exists() ) {
				return;
			}
			$changed = $wpdb->query( 'ALTER TABLE ' . $this->table . ' ADD reminder_sent_at DATETIME NULL DEFAULT NULL AFTER booking_status' );
			if ( false !== $changed ) {
				$this->column_exists_cache['reminder_sent_at'] = true;
			}
		}

		/**
		 * Service interest-options accessors (added).
		 */
		public function get_service_interest_options( $service ) {
			if ( ! is_array( $service ) || empty( $service['interests_enabled'] ) ) {
				return array();
			}
			$opts = isset( $service['interest_options'] ) && is_array( $service['interest_options'] ) ? $service['interest_options'] : array();
			$clean = array();
			foreach ( $opts as $label ) {
				$label = is_string( $label ) ? trim( $label ) : '';
				if ( '' !== $label ) { $clean[] = $label; }
			}
			return $clean;
		}

		public function get_service_interests_headline( $service ) {
			if ( ! is_array( $service ) ) { return ''; }
			return isset( $service['interests_headline'] ) ? (string) $service['interests_headline'] : '';
		}

		public function get_service_interests_max( $service ) {
			if ( ! is_array( $service ) ) { return 0; }
			return max( 0, min( 20, absint( isset( $service['interests_max'] ) ? $service['interests_max'] : 0 ) ) );
		}

		private function install_schema() {
			global $wpdb;
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$charset = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE {$this->table} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				created_at DATETIME NOT NULL,
				booking_reference VARCHAR(50) NOT NULL,
				spaces_booked INT UNSIGNED NOT NULL DEFAULT 1,
				location_id VARCHAR(100) NOT NULL DEFAULT '',
				location_name VARCHAR(190) NOT NULL DEFAULT '',
				service_id VARCHAR(100) NOT NULL,
				service_name VARCHAR(190) NOT NULL,
				staff_id VARCHAR(100) NOT NULL DEFAULT '',
				staff_name VARCHAR(190) NOT NULL DEFAULT '',
				customer_name VARCHAR(190) NOT NULL,
				customer_email VARCHAR(190) NOT NULL,
				customer_phone VARCHAR(50) NOT NULL DEFAULT '',
				customer_address LONGTEXT NULL,
				customer_notes LONGTEXT NULL,
				customer_interests LONGTEXT NULL,
				cancellation_reason LONGTEXT NULL,
				admin_notes LONGTEXT NULL,
				reminder_sent_at DATETIME NULL DEFAULT NULL,
				booking_date DATE NOT NULL,
				booking_time TIME NOT NULL,
				booking_end_time TIME NOT NULL,
				booking_status VARCHAR(50) NOT NULL DEFAULT 'confirmed',
				PRIMARY KEY  (id),
				KEY booking_status (booking_status),
				KEY booking_date (booking_date),
				KEY location_id (location_id),
				KEY service_id (service_id),
				KEY staff_id (staff_id)
			) {$charset};";

			dbDelta( $sql );
			// dbDelta may have just created the table - drop the memoised answer
			// so the ensure_* calls below see current reality.
			$this->table_exists_cache = null;
			$this->column_exists_cache = array();
			$this->ensure_booking_quantity_column();
			$this->ensure_customer_interests_column();
			$this->ensure_cancellation_reason_column();
			$this->ensure_reminder_sent_column();
			update_option( self::OPTION_DB_VERSION, self::VERSION );
		}

		private function maybe_seed_defaults() {
			if ( ! get_option( self::OPTION_LOCATIONS ) ) {
				update_option(
					self::OPTION_LOCATIONS,
					array(
						array(
							'id'     => 'location_1',
							'name'   => 'Main Venue',
							'active' => 1,
						),
					)
				);
			}

			if ( ! get_option( self::OPTION_STAFF ) ) {
				update_option(
					self::OPTION_STAFF,
					array(
						array(
							'id'     => 'staff_1',
							'name'   => 'Team Member 1',
							'email'        => '',
							'active'       => 1,
							'location_ids' => array( 'location_1' ),
							'hours'        => $this->default_hours(),
							'date_exceptions' => array(),
						),
						array(
							'id'     => 'staff_2',
							'name'   => 'Team Member 2',
							'email'        => '',
							'active'       => 1,
							'location_ids' => array( 'location_1' ),
							'hours'        => $this->default_hours(),
							'date_exceptions' => array(),
						),
					)
				);
			}

			if ( ! get_option( self::OPTION_SERVICES ) ) {
				update_option(
					self::OPTION_SERVICES,
					array(
						array(
							'id'                => 'service_1',
							'name'              => 'Consultation',
							'duration'          => 30,
							'buffer'            => 0,
							'min_notice_hours'  => 2,
							'max_days_ahead'    => 60,
							'active'            => 1,
							'hours'             => $this->default_hours(),
							'location_ids'      => array( 'location_1' ),
							'staff_ids'         => array( 'staff_1', 'staff_2' ),
							'location_staff_ids'=> array(),
									'location_staff_hours'=> array(),
							'staff_rules'       => array(),
							'booking_summary_notes' => '',
						),
					)
				);
			}

			if ( ! get_option( self::OPTION_SETTINGS ) ) {
				update_option( self::OPTION_SETTINGS, $this->default_settings() );
			}
		}

		private function generate_stable_item_id( $prefix, $index = 0 ) {
			return sanitize_key( $prefix . '_' . ( absint( $index ) + 1 ) . '_' . wp_generate_password( 6, false, false ) );
		}

		private function sanitize_time( $value, $fallback = '09:00' ) {
			$value = sanitize_text_field( (string) $value );
			if ( preg_match( '/^(2[0-3]|[01]\d):([0-5]\d)$/', $value ) ) {
				return $value;
			}
			return $fallback;
		}

		private function sanitize_optional_int( $value ) {
			if ( '' === (string) $value ) {
				return '';
			}
			return absint( $value );
		}

		private function time_to_minutes( $time ) {
			$parts = explode( ':', (string) $time );
			$hour  = isset( $parts[0] ) ? absint( $parts[0] ) : 0;
			$min   = isset( $parts[1] ) ? absint( $parts[1] ) : 0;
			return ( $hour * 60 ) + $min;
		}

		private function minutes_to_time( $minutes ) {
			$minutes = max( 0, absint( $minutes ) );
			return sprintf( '%02d:%02d', floor( $minutes / 60 ), $minutes % 60 );
		}

		private function is_valid_date( $date ) {
			return (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $date );
		}

		private function is_valid_time( $time ) {
			return (bool) preg_match( '/^(2[0-3]|[01]\d):([0-5]\d)$/', (string) $time );
		}

		private function get_all_staff() {
			$rows = get_option( self::OPTION_STAFF, array() );
			return is_array( $rows ) ? $rows : array();
		}

		private function get_active_staff() {
			return array_values(
				array_filter(
					$this->get_all_staff(),
					function ( $row ) {
						return ! empty( $row['active'] ) && ! empty( $row['id'] ) && ! empty( $row['name'] );
					}
				)
			);
		}

		private function get_all_locations() {
			$rows = get_option( self::OPTION_LOCATIONS, array() );
			return is_array( $rows ) ? $rows : array();
		}

		private function get_active_locations() {
			return array_values(
				array_filter(
					$this->get_all_locations(),
					function ( $row ) {
						return ! empty( $row['active'] ) && ! empty( $row['id'] ) && ! empty( $row['name'] );
					}
				)
			);
		}

		private function find_location( $location_id ) {
			foreach ( $this->get_active_locations() as $location ) {
				if ( $location['id'] === $location_id ) {
					return $location;
				}
			}
			return false;
		}

		private function get_all_location_ids() {
			return wp_list_pluck( $this->get_active_locations(), 'id' );
		}

		private function get_effective_location_ids_from_row( $row ) {
			$all_location_ids = $this->get_all_location_ids();
			$row_location_ids = isset( $row['location_ids'] ) && is_array( $row['location_ids'] ) ? array_values( array_filter( array_map( 'sanitize_key', $row['location_ids'] ) ) ) : array();
			if ( empty( $all_location_ids ) ) {
				return array();
			}
			if ( empty( $row_location_ids ) ) {
				return $all_location_ids;
			}
			return array_values( array_intersect( $row_location_ids, $all_location_ids ) );
		}

		private function service_matches_location( $service, $location_id ) {
			if ( '' === (string) $location_id ) {
				return true;
			}
			return in_array( $location_id, $this->get_effective_location_ids_from_row( $service ), true );
		}

		private function staff_matches_location( $staff, $location_id ) {
			if ( '' === (string) $location_id ) {
				return true;
			}
			return in_array( $location_id, $this->get_effective_location_ids_from_row( $staff ), true );
		}

		private function service_has_location_staff_matrix( $service ) {
			if ( empty( $service['location_staff_ids'] ) || ! is_array( $service['location_staff_ids'] ) ) {
				return false;
			}
			foreach ( $service['location_staff_ids'] as $location_id => $staff_ids ) {
				if ( '' !== sanitize_key( $location_id ) && is_array( $staff_ids ) ) {
					return true;
				}
			}
			return false;
		}

		private function staff_can_deliver_service_at_location( $service, $staff, $location_id = '' ) {
			if ( ! $service || ! $staff || empty( $staff['id'] ) ) {
				return false;
			}
			$staff_id = sanitize_key( $staff['id'] );
			$service_staff_ids = isset( $service['staff_ids'] ) && is_array( $service['staff_ids'] ) ? array_map( 'sanitize_key', $service['staff_ids'] ) : array();
			if ( ! in_array( $staff_id, $service_staff_ids, true ) ) {
				return false;
			}
			if ( '' !== (string) $location_id && ! $this->service_matches_location( $service, $location_id ) ) {
				return false;
			}
			if ( '' !== (string) $location_id && ! $this->staff_matches_location( $staff, $location_id ) ) {
				return false;
			}

			// Backwards compatibility: if no per-location staff matrix has been configured,
			// fall back to the old Service -> Staff plus Staff -> Location rules.
			if ( ! $this->service_has_location_staff_matrix( $service ) ) {
				return true;
			}

			if ( '' === (string) $location_id ) {
				foreach ( (array) $service['location_staff_ids'] as $matrix_staff_ids ) {
					$matrix_staff_ids = is_array( $matrix_staff_ids ) ? array_map( 'sanitize_key', $matrix_staff_ids ) : array();
					if ( in_array( $staff_id, $matrix_staff_ids, true ) ) {
						return true;
					}
				}
				return false;
			}

			$location_id = sanitize_key( $location_id );
			$matrix_staff_ids = isset( $service['location_staff_ids'][ $location_id ] ) && is_array( $service['location_staff_ids'][ $location_id ] ) ? array_map( 'sanitize_key', $service['location_staff_ids'][ $location_id ] ) : array();
			return in_array( $staff_id, $matrix_staff_ids, true );
		}

		private function get_locations_for_service( $service ) {
			if ( ! $service ) {
				return array();
			}
			$ids = $this->get_effective_location_ids_from_row( $service );
			$out = array();
			foreach ( $this->get_active_locations() as $location ) {
				if ( in_array( $location['id'], $ids, true ) ) {
					$out[] = $location;
				}
			}
			return $out;
		}

		private function get_services_for_location( $location_id ) {
			$out = array();
			foreach ( $this->get_active_services() as $service ) {
				if ( $this->service_matches_location( $service, $location_id ) ) {
					$out[] = $service;
				}
			}
			return $out;
		}

		private function get_services_for_filters( $location_id = '', $staff_id = '' ) {
			$out = array();
			foreach ( $this->get_active_services() as $service ) {
				if ( '' !== (string) $location_id && ! $this->service_matches_location( $service, $location_id ) ) {
					continue;
				}
				if ( '' !== (string) $staff_id ) {
					$staff = $this->find_staff( $staff_id );
					if ( ! $staff || ! $this->staff_can_deliver_service_at_location( $service, $staff, $location_id ) ) {
						continue;
					}
				}
				$out[] = $service;
			}
			return $out;
		}

		private function get_allowed_staff_for_service_location( $service, $location_id = '' ) {
			$out = array();
			if ( ! $service ) {
				return $out;
			}
			foreach ( $this->get_active_staff() as $staff ) {
				if ( ! $this->staff_can_deliver_service_at_location( $service, $staff, $location_id ) ) {
					continue;
				}
				$out[] = $staff;
			}
			return $out;
		}

		private function get_shared_locations_for_service_and_staff( $service, $staff ) {
			$out = array();
			foreach ( $this->get_active_locations() as $location ) {
				if ( $this->staff_can_deliver_service_at_location( $service, $staff, $location['id'] ) ) {
					$out[] = $location;
				}
			}
			return $out;
		}

		private function get_all_services() {
			$rows = get_option( self::OPTION_SERVICES, array() );
			return is_array( $rows ) ? $rows : array();
		}

		private function get_active_services() {
			return array_values(
				array_filter(
					$this->get_all_services(),
					function ( $row ) {
						return ! empty( $row['active'] ) && ! empty( $row['id'] ) && ! empty( $row['name'] );
					}
				)
			);
		}

		private function get_locations_for_filters( $service_id = '', $staff_id = '' ) {
			$out = array();
			foreach ( $this->get_active_locations() as $location ) {
				if ( '' !== (string) $service_id ) {
					$service = $this->find_service( $service_id );
					if ( ! $service || ! $this->service_matches_location( $service, $location['id'] ) ) {
						continue;
					}
				}
				if ( '' !== (string) $staff_id ) {
					$staff = $this->find_staff( $staff_id );
					if ( ! $staff ) {
						continue;
					}
					if ( '' !== (string) $service_id ) {
						$service = isset( $service ) && $service ? $service : $this->find_service( $service_id );
						if ( ! $service || ! $this->staff_can_deliver_service_at_location( $service, $staff, $location['id'] ) ) {
							continue;
						}
					} elseif ( ! $this->staff_matches_location( $staff, $location['id'] ) ) {
						continue;
					}
				}
				$out[] = $location;
			}
			return $out;
		}

		private function get_default_item_id( $settings, $key ) {
			return sanitize_key( isset( $settings[ $key ] ) ? $settings[ $key ] : '' );
		}

		private function choose_default_or_single_id( $items, $preferred_id = '' ) {
			$preferred_id = sanitize_key( $preferred_id );
			if ( ! empty( $preferred_id ) ) {
				foreach ( (array) $items as $item ) {
					$item_id = isset( $item['id'] ) ? sanitize_key( $item['id'] ) : '';
					if ( $item_id && $item_id === $preferred_id ) {
						return $item_id;
					}
				}
			}
			return 1 === count( $items ) && ! empty( $items[0]['id'] ) ? sanitize_key( $items[0]['id'] ) : '';
		}

		private function resolve_booking_payload_selections( $payload, $settings = null ) {
			$settings = is_array( $settings ) ? $settings : $this->get_settings();
			$payload['location_id'] = sanitize_key( isset( $payload['location_id'] ) ? $payload['location_id'] : '' );
			$payload['service_id']  = sanitize_key( isset( $payload['service_id'] ) ? $payload['service_id'] : '' );
			$payload['staff_id']    = sanitize_key( isset( $payload['staff_id'] ) ? $payload['staff_id'] : '' );

			for ( $i = 0; $i < 3; $i++ ) {
				if ( '' === $payload['location_id'] ) {
					$payload['location_id'] = $this->choose_default_or_single_id(
						$this->get_locations_for_filters( $payload['service_id'], $payload['staff_id'] ),
						$this->get_default_item_id( $settings, 'default_location_id' )
					);
				}
				if ( '' === $payload['service_id'] ) {
					$payload['service_id'] = $this->choose_default_or_single_id(
						$this->get_services_for_filters( $payload['location_id'], $payload['staff_id'] ),
						$this->get_default_item_id( $settings, 'default_service_id' )
					);
				}
				if ( '' === $payload['staff_id'] && '' !== $payload['service_id'] ) {
					$service = $this->find_service( $payload['service_id'] );
					if ( $service ) {
						$payload['staff_id'] = $this->choose_default_or_single_id(
							$this->get_allowed_staff_for_service_location( $service, $payload['location_id'] ),
							$this->get_default_item_id( $settings, 'default_staff_id' )
						);
					}
				}
			}

			return $payload;
		}

		private function get_frontend_default_values( $settings ) {
			return $this->resolve_booking_payload_selections(
				array(
					'location_id' => '',
					'service_id'  => '',
					'staff_id'    => '',
				),
				$settings
			);
		}

		private function find_service( $service_id ) {
			foreach ( $this->get_active_services() as $service ) {
				if ( $service['id'] === $service_id ) {
					return $service;
				}
			}
			return false;
		}

		private function find_staff( $staff_id ) {
			foreach ( $this->get_active_staff() as $staff ) {
				if ( $staff['id'] === $staff_id ) {
					return $staff;
				}
			}
			return false;
		}

		private function normalize_location_staff_matrix( $matrix, $allowed_location_ids = array(), $allowed_staff_ids = array() ) {
			$out = array();
			$allowed_location_ids = array_map( 'sanitize_key', (array) $allowed_location_ids );
			$allowed_staff_ids    = array_map( 'sanitize_key', (array) $allowed_staff_ids );
			if ( ! is_array( $matrix ) ) {
				return $out;
			}

			foreach ( $matrix as $location_id => $staff_ids ) {
				$location_id = sanitize_key( $location_id );
				if ( '' === $location_id ) {
					continue;
				}
				if ( ! empty( $allowed_location_ids ) && ! in_array( $location_id, $allowed_location_ids, true ) ) {
					continue;
				}
				$clean_staff_ids = array();
				if ( is_array( $staff_ids ) ) {
					foreach ( $staff_ids as $staff_id ) {
						$staff_id = sanitize_key( $staff_id );
						if ( '' === $staff_id ) {
							continue;
						}
						if ( ! empty( $allowed_staff_ids ) && ! in_array( $staff_id, $allowed_staff_ids, true ) ) {
							continue;
						}
						$clean_staff_ids[] = $staff_id;
					}
				}
				$out[ $location_id ] = array_values( array_unique( $clean_staff_ids ) );
			}

			return $out;
		}

		private function normalize_location_staff_hours_matrix( $matrix, $allowed_location_ids = array(), $allowed_staff_ids = array() ) {
			$out = array();
			$allowed_location_ids = array_map( 'sanitize_key', (array) $allowed_location_ids );
			$allowed_staff_ids    = array_map( 'sanitize_key', (array) $allowed_staff_ids );
			if ( ! is_array( $matrix ) ) { return $out; }
			foreach ( $matrix as $location_id => $staff_rows ) {
				$location_id = sanitize_key( $location_id );
				if ( '' === $location_id || ( ! empty( $allowed_location_ids ) && ! in_array( $location_id, $allowed_location_ids, true ) ) || ! is_array( $staff_rows ) ) { continue; }
				foreach ( $staff_rows as $staff_id => $hours ) {
					$staff_id = sanitize_key( $staff_id );
					if ( '' === $staff_id || ( ! empty( $allowed_staff_ids ) && ! in_array( $staff_id, $allowed_staff_ids, true ) ) ) { continue; }
					$out[ $location_id ][ $staff_id ] = $this->sanitize_hours_rows( is_array( $hours ) ? $hours : array() );
				}
			}
			return $out;
		}

		private function get_location_staff_hours_for_service( $service, $location_id, $staff_id ) {
			$location_id = sanitize_key( $location_id );
			$staff_id    = sanitize_key( $staff_id );
			if ( '' === $location_id || '' === $staff_id || empty( $service['location_staff_hours'] ) || ! is_array( $service['location_staff_hours'] ) ) {
				return array();
			}
			if ( empty( $service['location_staff_ids'][ $location_id ] ) || ! is_array( $service['location_staff_ids'][ $location_id ] ) || ! in_array( $staff_id, array_map( 'sanitize_key', $service['location_staff_ids'][ $location_id ] ), true ) ) {
				return array();
			}
			return isset( $service['location_staff_hours'][ $location_id ][ $staff_id ] ) && is_array( $service['location_staff_hours'][ $location_id ][ $staff_id ] ) ? $service['location_staff_hours'][ $location_id ][ $staff_id ] : array();
		}

		private function normalize_staff_rules( $rules ) {
			$out = array();
			foreach ( (array) $rules as $staff_id => $rule ) {
				$staff_id = sanitize_key( $staff_id );
				if ( '' === $staff_id || ! is_array( $rule ) ) {
					continue;
				}
				$out[ $staff_id ] = array(
					'duration'         => $this->sanitize_optional_int( isset( $rule['duration'] ) ? $rule['duration'] : '' ),
					'buffer'           => $this->sanitize_optional_int( isset( $rule['buffer'] ) ? $rule['buffer'] : '' ),
					'min_notice_hours' => $this->sanitize_optional_int( isset( $rule['min_notice_hours'] ) ? $rule['min_notice_hours'] : '' ),
					'max_days_ahead'   => $this->sanitize_optional_int( isset( $rule['max_days_ahead'] ) ? $rule['max_days_ahead'] : '' ),
					'slot_capacity'    => max( 1, absint( isset( $rule['slot_capacity'] ) ? $rule['slot_capacity'] : 1 ) ),
					'hours'            => isset( $rule['hours'] ) && is_array( $rule['hours'] ) ? $this->sanitize_hours_rows( $rule['hours'] ) : array(),
					'booking_summary_notes' => wp_kses_post( wp_unslash( isset( $rule['booking_summary_notes'] ) ? $rule['booking_summary_notes'] : '' ) ),
				);
			}

			return $out;
		}


		private function sanitize_hours_rows( $raw_hours ) {
			$days  = $this->days_map();
			$hours = array();
			foreach ( $days as $day_key => $day_label ) {
				$day_row = isset( $raw_hours[ $day_key ] ) && is_array( $raw_hours[ $day_key ] ) ? $raw_hours[ $day_key ] : array();
				$hours[ $day_key ] = array(
					'enabled'       => empty( $day_row['enabled'] ) ? 0 : 1,
					'start'         => $this->sanitize_time( isset( $day_row['start'] ) ? $day_row['start'] : '09:00', '09:00' ),
					'end'           => $this->sanitize_time( isset( $day_row['end'] ) ? $day_row['end'] : '17:00', '17:00' ),
					'break_enabled' => empty( $day_row['break_enabled'] ) ? 0 : 1,
					'break_start'   => $this->sanitize_time( isset( $day_row['break_start'] ) ? $day_row['break_start'] : '12:00', '12:00' ),
					'break_end'     => $this->sanitize_time( isset( $day_row['break_end'] ) ? $day_row['break_end'] : '13:00', '13:00' ),
				);
			}
			return $hours;
		}

		private function normalize_date_exceptions( $raw_rows ) {
			$rows = array();
			if ( ! is_array( $raw_rows ) ) {
				return $rows;
			}

			foreach ( $raw_rows as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$from_date = sanitize_text_field( wp_unslash( isset( $row['from_date'] ) ? $row['from_date'] : ( isset( $row['date'] ) ? $row['date'] : '' ) ) );
				$to_date   = sanitize_text_field( wp_unslash( isset( $row['to_date'] ) ? $row['to_date'] : $from_date ) );
				if ( ! $this->is_valid_date( $from_date ) || ! $this->is_valid_date( $to_date ) ) {
					continue;
				}
				if ( $to_date < $from_date ) {
					$tmp = $from_date;
					$from_date = $to_date;
					$to_date = $tmp;
				}
				$mode = sanitize_key( isset( $row['mode'] ) ? $row['mode'] : 'off' );
				if ( 'custom' === $mode ) {
					$mode = 'block';
				}
				if ( ! in_array( $mode, array( 'off', 'block' ), true ) ) {
					$mode = 'off';
				}
				$start = 'block' === $mode ? $this->sanitize_time( isset( $row['start'] ) ? $row['start'] : '09:00', '09:00' ) : '';
				$end   = 'block' === $mode ? $this->sanitize_time( isset( $row['end'] ) ? $row['end'] : '17:00', '17:00' ) : '';
				if ( 'block' === $mode && $this->time_to_minutes( $end ) <= $this->time_to_minutes( $start ) ) {
					continue;
				}
				$rows[] = array(
					'from_date' => $from_date,
					'to_date'   => $to_date,
					'mode'      => $mode,
					'start'     => $start,
					'end'       => $end,
					'note'      => sanitize_text_field( wp_unslash( isset( $row['note'] ) ? $row['note'] : '' ) ),
				);
			}

			usort( $rows, function( $a, $b ) {
				return strcmp( $a['from_date'], $b['from_date'] );
			} );

			return array_values( $rows );
		}

		private function get_staff_date_exceptions_for_date( $staff, $date ) {
			$exceptions = isset( $staff['date_exceptions'] ) && is_array( $staff['date_exceptions'] ) ? $staff['date_exceptions'] : array();
			$matched = array();
			foreach ( $exceptions as $row ) {
				$from_date = isset( $row['from_date'] ) ? $row['from_date'] : ( isset( $row['date'] ) ? $row['date'] : '' );
				$to_date   = isset( $row['to_date'] ) ? $row['to_date'] : $from_date;
				if ( $from_date && $to_date && $date >= $from_date && $date <= $to_date ) {
					$matched[] = $row;
				}
			}
			if ( empty( $matched ) ) {
				return array();
			}
			usort( $matched, function( $a, $b ) {
				$cmp = strcmp( isset( $a['from_date'] ) ? $a['from_date'] : '', isset( $b['from_date'] ) ? $b['from_date'] : '' );
				if ( 0 !== $cmp ) {
					return $cmp;
				}
				return strcmp( isset( $a['start'] ) ? $a['start'] : '', isset( $b['start'] ) ? $b['start'] : '' );
			} );
			return $matched;
		}


		private function hours_row_to_windows( $hours ) {
			if ( ! is_array( $hours ) || empty( $hours['enabled'] ) ) {
				return array();
			}

			$start = $this->time_to_minutes( isset( $hours['start'] ) ? $hours['start'] : '09:00' );
			$end   = $this->time_to_minutes( isset( $hours['end'] ) ? $hours['end'] : '17:00' );
			if ( $end <= $start ) {
				return array();
			}

			if ( ! empty( $hours['break_enabled'] ) ) {
				$break_start = $this->time_to_minutes( isset( $hours['break_start'] ) ? $hours['break_start'] : '12:00' );
				$break_end   = $this->time_to_minutes( isset( $hours['break_end'] ) ? $hours['break_end'] : '13:00' );

				if ( $break_end > $break_start && $break_start < $end && $break_end > $start ) {
					$windows = array();
					$first_end = max( $start, min( $break_start, $end ) );
					$second_start = min( $end, max( $break_end, $start ) );
					if ( $first_end > $start ) {
						$windows[] = array( 'start' => $start, 'end' => $first_end );
					}
					if ( $end > $second_start ) {
						$windows[] = array( 'start' => $second_start, 'end' => $end );
					}
					return $windows;
				}
			}

			return array( array( 'start' => $start, 'end' => $end ) );
		}

		private function intersect_availability_windows( $primary_windows, $secondary_windows ) {
			$out = array();
			foreach ( (array) $primary_windows as $primary ) {
				foreach ( (array) $secondary_windows as $secondary ) {
					$start = max( absint( $primary['start'] ), absint( $secondary['start'] ) );
					$end   = min( absint( $primary['end'] ), absint( $secondary['end'] ) );
					if ( $end > $start ) {
						$out[] = array( 'start' => $start, 'end' => $end );
					}
				}
			}
			return $out;
		}

		/**
		 * Is this existing booking the very same session as the slot being built?
		 *
		 * Only people joining one call share a slot's capacity. That means the
		 * booking must belong to the combination being built (blocks_shared not
		 * set, i.e. it came from get_existing_bookings_for_day()) AND start at
		 * the same time. A booking in the same combination that merely overlaps -
		 * an earlier call whose cross-staff buffer runs into this slot, or a
		 * staggered start produced by a custom offer interval - is a separate
		 * call and cannot share the line.
		 */
		private function booking_is_same_session( $booking, $slot_start ) {
			if ( ! empty( $booking['blocks_shared'] ) ) {
				return false;
			}
			return (int) $booking['start'] === (int) $slot_start;
		}

		private function slot_overlaps_windows( $slot_start, $slot_end, $windows ) {
			if ( empty( $windows ) || ! is_array( $windows ) ) {
				return false;
			}
			foreach ( $windows as $window ) {
				if ( ! isset( $window['start'], $window['end'] ) ) {
					continue;
				}
				if ( $slot_start < $window['end'] && $slot_end > $window['start'] ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * @param bool $shared_resource_mode When the single shared phone line is
		 *                                   in force, an overlapping booking that
		 *                                   is not part of this exact session
		 *                                   closes the slot outright instead of
		 *                                   consuming one unit of its capacity.
		 */
		private function add_slots_from_window( &$slots, $date, $start_minutes, $end_minutes, $duration, $slot_step, $existing, $min_ts, $blocked_windows = array(), $slot_capacity = 1, $shared_resource_mode = false ) {
			if ( $end_minutes <= $start_minutes || $slot_step <= 0 ) {
				return;
			}
			$slot_capacity = max( 1, absint( $slot_capacity ) );
			$seen = array();
			foreach ( $slots as $slot ) {
				$seen[ $slot['value'] ] = true;
			}
			for ( $slot_start = $start_minutes; $slot_start + $duration <= $end_minutes; $slot_start += $slot_step ) {
				$slot_end = $slot_start + $duration;
				$slot_value = $this->minutes_to_time( $slot_start );
				$slot_ts  = strtotime( $date . ' ' . $slot_value . ':00' );
				if ( $slot_ts < $min_ts ) {
					continue;
				}

				if ( $this->slot_overlaps_windows( $slot_start, $slot_end, $blocked_windows ) || isset( $seen[ $slot_value ] ) ) {
					continue;
				}

				$booked_spaces = 0;
				$phone_in_use  = false;
				foreach ( $existing as $booking ) {
					if ( $slot_start >= $booking['end'] || $slot_end <= $booking['start'] ) {
						continue;
					}
					if ( $shared_resource_mode && ! $this->booking_is_same_session( $booking, $slot_start ) ) {
						// A different call is already on the shared line, or we are
						// inside the cross-staff buffer that follows one. Capacity
						// is irrelevant: there is only one phone.
						$phone_in_use = true;
						break;
					}
					$booked_spaces += max( 1, absint( isset( $booking['spaces_booked'] ) ? $booking['spaces_booked'] : 1 ) );
				}
				if ( $phone_in_use ) {
					continue;
				}

				$remaining_spaces = max( 0, $slot_capacity - $booked_spaces );
				if ( $remaining_spaces <= 0 ) {
					continue;
				}

				$slot_label = $slot_value;
				if ( $slot_capacity > 1 ) {
					$slot_label .= ' - ' . $remaining_spaces . ' space' . ( 1 === $remaining_spaces ? ' left' : 's left' );
				}

				$slots[] = array(
					'value'            => $slot_value,
					'label'            => $slot_label,
					'slot_capacity'    => $slot_capacity,
					'spaces_booked'     => $booked_spaces,
					'spaces_remaining'  => $remaining_spaces,
				);
				$seen[ $slot_value ] = true;
			}
		}

		private function sanitize_staff_rows( $raw_rows ) {
			$rows = array();
			if ( ! is_array( $raw_rows ) ) {
				return $rows;
			}

			foreach ( $raw_rows as $index => $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$name = sanitize_text_field( wp_unslash( isset( $row['name'] ) ? $row['name'] : '' ) );
				if ( '' === $name ) {
					continue;
				}
				$id = sanitize_key( isset( $row['id'] ) ? $row['id'] : '' );
				if ( '' === $id ) {
					$id = $this->generate_stable_item_id( 'staff', $index );
				}
				$location_ids = array();
				if ( isset( $row['location_ids'] ) && is_array( $row['location_ids'] ) ) {
					foreach ( $row['location_ids'] as $location_id ) {
						$location_id = sanitize_key( $location_id );
						if ( '' !== $location_id ) {
							$location_ids[] = $location_id;
						}
					}
				}
				$location_ids = array_values( array_unique( $location_ids ) );

				$rows[] = array(
					'id'           => $id,
					'name'         => $name,
					'email'        => sanitize_email( wp_unslash( isset( $row['email'] ) ? $row['email'] : '' ) ),
					'description'  => sanitize_textarea_field( wp_unslash( isset( $row['description'] ) ? $row['description'] : '' ) ),
					'image_id'     => absint( isset( $row['image_id'] ) ? $row['image_id'] : 0 ),
					'image_url'    => esc_url_raw( wp_unslash( isset( $row['image_url'] ) ? $row['image_url'] : '' ) ),
				) + $this->sanitize_extra_image_fields( $row ) + array(
					'active'       => empty( $row['active'] ) ? 0 : 1,
					'location_ids' => $location_ids,
					'hours'        => $this->sanitize_hours_rows( isset( $row['hours'] ) ? $row['hours'] : array() ),
					'date_exceptions' => $this->normalize_date_exceptions( isset( $row['date_exceptions'] ) ? $row['date_exceptions'] : array() ),
					'taxonomy_fields' => $this->sanitize_taxonomy_custom_field_values_for_taxonomy( self::TAX_REL_TEAM, isset( $row['taxonomy_fields'] ) ? $row['taxonomy_fields'] : array() ),
					'daily_cap'    => max( 0, absint( isset( $row['daily_cap'] ) ? $row['daily_cap'] : 0 ) ),
				);
			}
			return array_values( $rows );
		}

		private function sanitize_location_rows( $raw_rows ) {
			$rows = array();
			if ( ! is_array( $raw_rows ) ) {
				return $rows;
			}

			foreach ( $raw_rows as $index => $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$name = sanitize_text_field( wp_unslash( isset( $row['name'] ) ? $row['name'] : '' ) );
				if ( '' === $name ) {
					continue;
				}
				$id = sanitize_key( isset( $row['id'] ) ? $row['id'] : '' );
				if ( '' === $id ) {
					$id = $this->generate_stable_item_id( 'location', $index );
				}
				$rows[] = array(
					'id'          => $id,
					'name'        => $name,
					'address'     => sanitize_textarea_field( wp_unslash( isset( $row['address'] ) ? $row['address'] : '' ) ),
					'postcode'    => sanitize_text_field( wp_unslash( isset( $row['postcode'] ) ? $row['postcode'] : '' ) ),
					'description' => sanitize_textarea_field( wp_unslash( isset( $row['description'] ) ? $row['description'] : '' ) ),
					'image_id'    => absint( isset( $row['image_id'] ) ? $row['image_id'] : 0 ),
					'image_url'   => esc_url_raw( wp_unslash( isset( $row['image_url'] ) ? $row['image_url'] : '' ) ),
				) + $this->sanitize_extra_image_fields( $row ) + array(
					'active'      => empty( $row['active'] ) ? 0 : 1,
					'taxonomy_fields' => $this->sanitize_taxonomy_custom_field_values_for_taxonomy( self::TAX_REL_LOCATION, isset( $row['taxonomy_fields'] ) ? $row['taxonomy_fields'] : array() ),
				);
			}

			return array_values( $rows );
		}

		private function sanitize_service_rows( $raw_rows ) {
			$rows = array();
			if ( ! is_array( $raw_rows ) ) {
				return $rows;
			}

			foreach ( $raw_rows as $index => $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$name = sanitize_text_field( wp_unslash( isset( $row['name'] ) ? $row['name'] : '' ) );
				if ( '' === $name ) {
					continue;
				}
				$id = sanitize_key( isset( $row['id'] ) ? $row['id'] : '' );
				if ( '' === $id ) {
					$id = $this->generate_stable_item_id( 'service', $index );
				}

				$staff_ids = array();
				if ( isset( $row['staff_ids'] ) && is_array( $row['staff_ids'] ) ) {
					foreach ( $row['staff_ids'] as $staff_id ) {
						$staff_id = sanitize_key( $staff_id );
						if ( '' !== $staff_id ) {
							$staff_ids[] = $staff_id;
						}
					}
				}
				$staff_ids   = array_values( array_unique( $staff_ids ) );

				$location_ids = array();
				if ( isset( $row['location_ids'] ) && is_array( $row['location_ids'] ) ) {
					foreach ( $row['location_ids'] as $location_id ) {
						$location_id = sanitize_key( $location_id );
						if ( '' !== $location_id ) {
							$location_ids[] = $location_id;
						}
					}
				}
				$location_ids = array_values( array_unique( $location_ids ) );

				$location_staff_ids = $this->normalize_location_staff_matrix( isset( $row['location_staff_ids'] ) ? $row['location_staff_ids'] : array(), $location_ids, $staff_ids );
				$location_staff_hours = $this->normalize_location_staff_hours_matrix( isset( $row['location_staff_hours'] ) ? $row['location_staff_hours'] : array(), $location_ids, $staff_ids );

				$staff_rules = $this->normalize_staff_rules( isset( $row['staff_rules'] ) ? $row['staff_rules'] : array() );
				// keep only selected staff member rules
				$staff_rules = array_intersect_key( $staff_rules, array_flip( $staff_ids ) );


				// Interest options (added). Up to 20 free-text labels admin can fill in.
				$interest_options_raw = isset( $row['interest_options'] ) && is_array( $row['interest_options'] ) ? $row['interest_options'] : array();
				$interest_options = array();
				$seen_labels = array();
				$slot = 0;
				foreach ( $interest_options_raw as $opt_raw ) {
					$slot++;
					if ( $slot > 20 ) { break; }
					$opt_label = '';
					if ( is_array( $opt_raw ) ) {
						$opt_label = isset( $opt_raw['label'] ) ? sanitize_text_field( wp_unslash( $opt_raw['label'] ) ) : '';
					} elseif ( is_string( $opt_raw ) ) {
						$opt_label = sanitize_text_field( wp_unslash( $opt_raw ) );
					}
					$opt_label = trim( $opt_label );
					if ( '' === $opt_label ) { continue; }
					$lc = strtolower( $opt_label );
					if ( isset( $seen_labels[ $lc ] ) ) { continue; }
					$seen_labels[ $lc ] = true;
					$interest_options[] = $opt_label;
				}
				$interests_headline = sanitize_text_field( wp_unslash( isset( $row['interests_headline'] ) ? $row['interests_headline'] : '' ) );
				$interests_max = max( 0, absint( isset( $row['interests_max'] ) ? $row['interests_max'] : 0 ) );
				if ( $interests_max > 20 ) { $interests_max = 20; }
				$interests_enabled = empty( $row['interests_enabled'] ) ? 0 : 1;

				$rows[] = array(
					'id'               => $id,
					'name'             => $name,
					'description'      => sanitize_textarea_field( wp_unslash( isset( $row['description'] ) ? $row['description'] : '' ) ),
					'image_id'         => absint( isset( $row['image_id'] ) ? $row['image_id'] : 0 ),
					'image_url'        => esc_url_raw( wp_unslash( isset( $row['image_url'] ) ? $row['image_url'] : '' ) ),
				) + $this->sanitize_extra_image_fields( $row ) + array(
					'duration'         => max( 1, absint( isset( $row['duration'] ) ? $row['duration'] : 30 ) ),
					'buffer'           => max( 0, absint( isset( $row['buffer'] ) ? $row['buffer'] : 0 ) ),
					'min_notice_hours' => max( 0, absint( isset( $row['min_notice_hours'] ) ? $row['min_notice_hours'] : 0 ) ),
					'max_days_ahead'   => max( 1, absint( isset( $row['max_days_ahead'] ) ? $row['max_days_ahead'] : 60 ) ),
					'active'           => empty( $row['active'] ) ? 0 : 1,
					'hours'            => $this->sanitize_hours_rows( isset( $row['hours'] ) ? $row['hours'] : array() ),
					'location_ids'       => $location_ids,
					'staff_ids'          => $staff_ids,
					'location_staff_ids'   => $location_staff_ids,
					'location_staff_hours' => $location_staff_hours,
					'staff_rules'        => $staff_rules,
					'taxonomy_fields'  => $this->sanitize_taxonomy_custom_field_values_for_taxonomy( self::TAX_REL_SERVICE, isset( $row['taxonomy_fields'] ) ? $row['taxonomy_fields'] : array() ),
					'interests_enabled' => $interests_enabled,
					'interests_headline' => $interests_headline,
					'interests_max'     => $interests_max,
					'interest_options'  => $interest_options,
					'customer_description' => sanitize_textarea_field( wp_unslash( isset( $row['customer_description'] ) ? $row['customer_description'] : '' ) ),
					'message_placeholder' => sanitize_textarea_field( wp_unslash( isset( $row['message_placeholder'] ) ? $row['message_placeholder'] : '' ) ),
					'quick_question_email' => sanitize_email( wp_unslash( isset( $row['quick_question_email'] ) ? $row['quick_question_email'] : '' ) ),
					'slot_offer_interval' => max( 0, absint( isset( $row['slot_offer_interval'] ) ? $row['slot_offer_interval'] : 0 ) ),
					'customer_facing_duration' => max( 0, absint( isset( $row['customer_facing_duration'] ) ? $row['customer_facing_duration'] : 0 ) ),
					'booking_summary_notes' => wp_kses_post( wp_unslash( isset( $row['booking_summary_notes'] ) ? $row['booking_summary_notes'] : '' ) ),
				);
			}

			return array_values( $rows );
		}

		private function resolve_effective_rules( $service, $staff = false ) {
			$rules = array(
				'duration'         => max( 1, absint( isset( $service['duration'] ) ? $service['duration'] : 30 ) ),
				'buffer'           => max( 0, absint( isset( $service['buffer'] ) ? $service['buffer'] : 0 ) ),
				'min_notice_hours' => max( 0, absint( isset( $service['min_notice_hours'] ) ? $service['min_notice_hours'] : 0 ) ),
				'max_days_ahead'   => max( 1, absint( isset( $service['max_days_ahead'] ) ? $service['max_days_ahead'] : 60 ) ),
				'slot_capacity'    => 1,
				'slot_offer_interval' => max( 0, absint( isset( $service['slot_offer_interval'] ) ? $service['slot_offer_interval'] : 0 ) ),
				'customer_facing_duration' => max( 0, absint( isset( $service['customer_facing_duration'] ) ? $service['customer_facing_duration'] : 0 ) ),
				'hours'            => isset( $service['hours'] ) && is_array( $service['hours'] ) ? $service['hours'] : $this->default_hours(),
			);

			if ( $staff && ! empty( $service['staff_rules'][ $staff['id'] ] ) ) {
				$override = $service['staff_rules'][ $staff['id'] ];

				if ( '' !== $override['duration'] && null !== $override['duration'] ) {
					$rules['duration'] = max( 1, absint( $override['duration'] ) );
				}
				if ( '' !== $override['buffer'] && null !== $override['buffer'] ) {
					$rules['buffer'] = max( 0, absint( $override['buffer'] ) );
				}
				if ( '' !== $override['min_notice_hours'] && null !== $override['min_notice_hours'] ) {
					$rules['min_notice_hours'] = max( 0, absint( $override['min_notice_hours'] ) );
				}
				if ( '' !== $override['max_days_ahead'] && null !== $override['max_days_ahead'] ) {
					$rules['max_days_ahead'] = max( 1, absint( $override['max_days_ahead'] ) );
				}
				if ( isset( $override['slot_capacity'] ) && '' !== $override['slot_capacity'] && null !== $override['slot_capacity'] ) {
					$rules['slot_capacity'] = max( 1, absint( $override['slot_capacity'] ) );
				}
				// Availability is intentionally NOT overridden here.
				// Bookable days/times come from the shared service/team member schedule.
			}

			return $rules;
		}

		/**
		 * Easter Sunday for a given year, as Y-m-d.
		 *
		 * Anonymous Gregorian algorithm (Meeus/Jones/Butcher). Implemented inline
		 * rather than using easter_date(), which needs ext-calendar and is not
		 * guaranteed on shared hosting.
		 */
		private function easter_sunday( $year ) {
			$year = (int) $year;
			$a = $year % 19;
			$b = intdiv( $year, 100 );
			$c = $year % 100;
			$d = intdiv( $b, 4 );
			$e = $b % 4;
			$f = intdiv( $b + 8, 25 );
			$g = intdiv( $b - $f + 1, 3 );
			$h = ( 19 * $a + $b - $d - $g + 15 ) % 30;
			$i = intdiv( $c, 4 );
			$k = $c % 4;
			$l = ( 32 + 2 * $e + 2 * $i - $h - $k ) % 7;
			$m = intdiv( $a + 11 * $h + 22 * $l, 451 );
			$month = intdiv( $h + $l - 7 * $m + 114, 31 );
			$day   = ( ( $h + $l - 7 * $m + 114 ) % 31 ) + 1;
			return sprintf( '%04d-%02d-%02d', $year, $month, $day );
		}

		/**
		 * Roll a holiday forward to the next weekday that is not already taken.
		 *
		 * Implements the UK substitute-day rule: a bank holiday falling on a
		 * weekend moves to the next working day, and if that day is already a
		 * bank holiday it moves on again (Christmas Day on a Saturday takes
		 * Monday, so Boxing Day takes Tuesday).
		 */
		private function uk_bank_holiday_substitute( $date, $taken ) {
			$ts = strtotime( $date );
			while ( (int) gmdate( 'N', $ts ) >= 6 || in_array( gmdate( 'Y-m-d', $ts ), $taken, true ) ) {
				$ts = strtotime( '+1 day', $ts );
			}
			return gmdate( 'Y-m-d', $ts );
		}

		/**
		 * UK (England + Wales) bank holidays.
		 *
		 * Computed for a rolling window around the current year rather than
		 * hardcoded, so the list can never silently run out and start offering
		 * consultations on Christmas Day. The previous hardcoded table stopped at
		 * 2029-12-26; from 2030 every bank holiday would have been treated as an
		 * ordinary working day, and is_post_long_break_day() would have stopped
		 * detecting long breaks too.
		 *
		 * One-off holidays (coronations, state funerals, jubilees) are announced
		 * with only a few weeks' notice and cannot wait for a plugin release, so
		 * the result is filterable:
		 *
		 *   add_filter( 'tj_appt_booker_bank_holidays', function ( $dates ) {
		 *       $dates[] = '2035-06-04';
		 *       return $dates;
		 *   } );
		 */
		private function get_uk_bank_holidays() {
			static $cache = array();

			$this_year = (int) current_datetime()->format( 'Y' );
			$cache_key = (string) $this_year;
			if ( isset( $cache[ $cache_key ] ) ) {
				return $cache[ $cache_key ];
			}

			$dates = array();

			// One year back (so recently passed dates still register) through
			// three years ahead, which comfortably covers any booking horizon.
			for ( $year = $this_year - 1; $year <= $this_year + 3; $year++ ) {
				$easter = strtotime( $this->easter_sunday( $year ) );

				// Fixed-date holidays observe the substitute rule; the movable
				// Monday holidays are always weekdays already.
				$dates[] = $this->uk_bank_holiday_substitute( $year . '-01-01', $dates );
				$dates[] = gmdate( 'Y-m-d', strtotime( '-2 days', $easter ) ); // Good Friday
				$dates[] = gmdate( 'Y-m-d', strtotime( '+1 day', $easter ) );  // Easter Monday
				$dates[] = gmdate( 'Y-m-d', strtotime( "first monday of may $year" ) );
				$dates[] = gmdate( 'Y-m-d', strtotime( "last monday of may $year" ) );
				$dates[] = gmdate( 'Y-m-d', strtotime( "last monday of august $year" ) );
				$dates[] = $this->uk_bank_holiday_substitute( $year . '-12-25', $dates );
				$dates[] = $this->uk_bank_holiday_substitute( $year . '-12-26', $dates );
			}

			$dates = array_values( array_unique( $dates ) );
			sort( $dates );

			/**
			 * Filter the UK bank holiday dates used to block booking availability.
			 *
			 * @param string[] $dates Y-m-d dates, sorted and de-duplicated.
			 */
			$dates = apply_filters( 'tj_appt_booker_bank_holidays', $dates );
			$dates = array_values( array_filter( array_map( 'strval', (array) $dates ), array( $this, 'is_valid_date' ) ) );

			$cache[ $cache_key ] = $dates;
			return $dates;
		}

		/**
		 * Is this date a working day? (Mon-Fri, not a bank holiday.)
		 */
		private function is_working_day( $date ) {
			if ( ! $this->is_valid_date( $date ) ) { return false; }
			$dow = (int) gmdate( 'N', strtotime( $date ) ); // 1=Mon, 7=Sun
			if ( $dow >= 6 ) { return false; }
			$holidays = $this->get_uk_bank_holidays();
			return ! in_array( $date, $holidays, true );
		}

		/**
		 * Should this date be blocked because it's the first working day after
		 * a 3+ consecutive non-working day stretch? (Tuesday after a Mon bank
		 * holiday, Wednesday after Christmas + Boxing Day, etc.)
		 */
		private function is_post_long_break_day( $date ) {
			if ( ! $this->is_working_day( $date ) ) { return false; }
			$cursor = strtotime( $date . ' -1 day' );
			$run = 0;
			// Walk back through consecutive non-working days.
			while ( ! $this->is_working_day( gmdate( 'Y-m-d', $cursor ) ) ) {
				$run++;
				$cursor = strtotime( gmdate( 'Y-m-d', $cursor ) . ' -1 day' );
				if ( $run > 14 ) { break; } // safety
			}
			return $run >= 3;
		}

		/**
		 * The earliest date a customer is allowed to book - that is, the first
		 * working day strictly after "next working day". Per the agreed rule:
		 * never same-day, never the next working day. If the cut-off hour has
		 * been reached today, "next working day" advances by one further.
		 */
		private function earliest_bookable_date() {
			$settings = $this->get_settings();
			$cutoff_hour = absint( isset( $settings['availability_cutoff_hour'] ) ? $settings['availability_cutoff_hour'] : 16 );

			// current_time('timestamp') is already shifted by the site's GMT
			// offset, so it must not be handed to wp_date() - that would apply
			// the offset a second time and run the cut-off an hour early for
			// the whole of British Summer Time. current_datetime() gives a real
			// DateTimeImmutable already in the site timezone.
			$site_now = current_datetime();
			$now_ts   = current_time( 'timestamp' ); // paired with gmdate() below, which is correct.
			$now_hour = (int) $site_now->format( 'G' );

			// Start from today.
			$cursor = $now_ts;
			// Step past today first (never same-day).
			$cursor = strtotime( gmdate( 'Y-m-d', $cursor ) . ' +1 day' );
			// Step past the first working day we hit (never next-working-day).
			while ( ! $this->is_working_day( gmdate( 'Y-m-d', $cursor ) ) ) {
				$cursor = strtotime( gmdate( 'Y-m-d', $cursor ) . ' +1 day' );
			}
			$cursor = strtotime( gmdate( 'Y-m-d', $cursor ) . ' +1 day' );
			// If we've passed the cut-off hour today, advance one further working day.
			if ( $now_hour >= $cutoff_hour ) {
				while ( ! $this->is_working_day( gmdate( 'Y-m-d', $cursor ) ) ) {
					$cursor = strtotime( gmdate( 'Y-m-d', $cursor ) . ' +1 day' );
				}
				$cursor = strtotime( gmdate( 'Y-m-d', $cursor ) . ' +1 day' );
			}
			// Land on the first working day at or after $cursor.
			while ( ! $this->is_working_day( gmdate( 'Y-m-d', $cursor ) ) ) {
				$cursor = strtotime( gmdate( 'Y-m-d', $cursor ) . ' +1 day' );
			}
			return gmdate( 'Y-m-d', $cursor );
		}

		/**
		 * Latest date a customer can book. "Current calendar week + next week"
		 * (configurable to N weeks). Resets each Friday at the cut-off hour:
		 * before Fri cut-off, "next week" is Mon-Fri ahead; after Fri cut-off,
		 * "current week" effectively skips to the following Mon-Fri.
		 */
		private function latest_bookable_date() {
			$settings = $this->get_settings();
			$weeks_ahead = max( 1, absint( isset( $settings['availability_weeks_ahead'] ) ? $settings['availability_weeks_ahead'] : 2 ) );
			$cutoff_hour = absint( isset( $settings['availability_cutoff_hour'] ) ? $settings['availability_cutoff_hour'] : 16 );

			// As in earliest_bookable_date(): read the wall-clock day and hour from
			// current_datetime(), never by passing current_time('timestamp') into
			// wp_date(). The double offset previously rolled the Friday cut-off a
			// day early between 23:00 and midnight during BST.
			$site_now = current_datetime();
			$now_ts   = current_time( 'timestamp' ); // paired with gmdate() below, which is correct.
			$dow  = (int) $site_now->format( 'N' ); // 1=Mon
			$hour = (int) $site_now->format( 'G' );

			// Find the Sunday that ends "this week" from the customer's perspective.
			// If today is Fri after cut-off, Sat, or Sun, our "this week" is the
			// week of the upcoming Monday (i.e. effectively next Mon-Fri).
			$advance_weeks = 0;
			if ( $dow >= 6 || ( 5 === $dow && $hour >= $cutoff_hour ) ) {
				$advance_weeks = 1;
			}

			// Days until the next Friday (inclusive of today if today is Friday).
			$days_to_friday = ( 5 - $dow + 7 ) % 7;
			$end_ts = strtotime( gmdate( 'Y-m-d', $now_ts ) . ' +' . ( $days_to_friday + ( $advance_weeks + $weeks_ahead - 1 ) * 7 ) . ' days' );
			return gmdate( 'Y-m-d', $end_ts );
		}

		/**
		 * Master check: is this date offerable to a customer under all the
		 * global availability rules? (Working day, not a bank holiday, not
		 * a post-long-break Tuesday, inside the booking window.)
		 */
		private function date_is_globally_offerable( $date ) {
			if ( ! $this->is_valid_date( $date ) ) { return false; }
			if ( ! $this->is_working_day( $date ) ) { return false; }
			$settings = $this->get_settings();
			if ( ! empty( $settings['availability_block_after_long_break'] ) && $this->is_post_long_break_day( $date ) ) {
				return false;
			}
			$earliest = $this->earliest_bookable_date();
			$latest   = $this->latest_bookable_date();
			if ( $date < $earliest ) { return false; }
			if ( $date > $latest )   { return false; }
			return true;
		}

		private function date_is_within_limits_for_rules( $date, $rules ) {
			if ( ! $this->is_valid_date( $date ) ) {
				return false;
			}
			// Apply global availability rules first (Mon-Fri, bank holidays,
			// post-long-break blocking, never same-day, never next working day,
			// booking-window cap). These trump any per-service rule.
			if ( ! $this->date_is_globally_offerable( $date ) ) {
				return false;
			}
			$today = current_time( 'Y-m-d' );
			if ( $date < $today ) {
				return false;
			}
			$max_allowed = gmdate( 'Y-m-d', strtotime( '+' . absint( $rules['max_days_ahead'] ) . ' days', strtotime( $today ) ) );
			return $date <= $max_allowed;
		}

		private function get_existing_bookings_for_day( $location_id, $service_id, $staff_id, $date, $exclude_booking_id = 0 ) {
			global $wpdb;
			if ( ! $this->table_exists() ) {
				return array();
			}
			$this->ensure_booking_quantity_column();

			$exclude_sql  = '';
			$query_params = array();
			if ( $exclude_booking_id > 0 ) {
				$exclude_sql    = ' AND id != %d';
				$query_params[] = $exclude_booking_id;
			}

			if ( '' === (string) $staff_id ) {
				$sql = "SELECT id, booking_time, booking_end_time, spaces_booked FROM {$this->table} WHERE location_id = %s AND service_id = %s AND staff_id = '' AND booking_date = %s AND booking_status IN (%s, %s){$exclude_sql}";
				$params = array_merge( array( $location_id, $service_id, $date, self::STATUS_CONFIRMED, self::STATUS_PENDING_APPROVAL ), $query_params );
			} else {
				$sql = "SELECT id, booking_time, booking_end_time, spaces_booked FROM {$this->table} WHERE location_id = %s AND service_id = %s AND staff_id = %s AND booking_date = %s AND booking_status IN (%s, %s){$exclude_sql}";
				$params = array_merge( array( $location_id, $service_id, $staff_id, $date, self::STATUS_CONFIRMED, self::STATUS_PENDING_APPROVAL ), $query_params );
			}

			$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

			$bookings = array();
			foreach ( $rows as $row ) {
				$bookings[] = array(
					'id'            => absint( isset( $row['id'] ) ? $row['id'] : 0 ),
					'start'         => $this->time_to_minutes( substr( (string) $row['booking_time'], 0, 5 ) ),
					'end'           => $this->time_to_minutes( substr( (string) $row['booking_end_time'], 0, 5 ) ),
					'spaces_booked' => max( 1, absint( isset( $row['spaces_booked'] ) ? $row['spaces_booked'] : 1 ) ),
				);
			}
			return $bookings;
		}

		/**
		 * Merge the shared-resource booking list into the per-combination list
		 * without counting any booking twice.
		 *
		 * get_existing_bookings_for_day() is filtered to one location/service/
		 * staff combination; get_all_bookings_for_day() returns every booking on
		 * the date. A booking matching the current combination therefore appears
		 * in both lists. The occupancy test in add_slots_from_window() SUMS
		 * spaces_booked, so a duplicate silently consumed capacity twice - which
		 * only showed up once slot_capacity rose above 1 (classes and workshops).
		 *
		 * Rows are keyed on booking id and the two views are combined rather than
		 * one simply winning, because each contributes something the other lacks:
		 *   - the per-combination row carries the true spaces_booked (the shared
		 *     list reports 1 per booking, since it only exists to block the
		 *     shared phone line);
		 *   - the shared row carries the end time padded by the cross-staff
		 *     buffer, which must still be honoured for a booking that happens to
		 *     belong to the combination being built.
		 * So: keep the larger space count and the later end.
		 *
		 * Rows are also tagged with blocks_shared. A booking that appears ONLY in
		 * the shared list belongs to some other service, location or team member
		 * - it is a different call on the one phone line, so it must close the
		 * slot outright rather than consume a share of its capacity. Capacity is
		 * for people joining the SAME call; it must never be able to outvote the
		 * single-phone rule. See add_slots_from_window().
		 */
		private function merge_booking_occupancy( $primary, $shared ) {
			$merged = array();

			foreach ( array( 'primary' => $primary, 'shared' => $shared ) as $source => $list ) {
				foreach ( (array) $list as $booking ) {
					$id  = isset( $booking['id'] ) ? absint( $booking['id'] ) : 0;
					// No id (defensive): fall back to a start/end key so an
					// unidentified row still cannot be counted twice.
					$key = $id > 0 ? 'id:' . $id : 'se:' . (int) $booking['start'] . '-' . (int) $booking['end'];

					if ( ! isset( $merged[ $key ] ) ) {
						// Provisionally blocking if first seen in the shared list;
						// cleared below if the per-combination list also has it.
						$booking['blocks_shared'] = ( 'shared' === $source );
						$merged[ $key ] = $booking;
						continue;
					}

					$merged[ $key ]['end'] = max( (int) $merged[ $key ]['end'], (int) $booking['end'] );
					$merged[ $key ]['spaces_booked'] = max(
						(int) $merged[ $key ]['spaces_booked'],
						(int) $booking['spaces_booked']
					);
					// Present in the per-combination list too, so it is part of
					// the session being built rather than a foreign call.
					if ( 'primary' === $source ) {
						$merged[ $key ]['blocks_shared'] = false;
					}
				}
			}

			return array_values( $merged );
		}

		/**
		 * Shared-resource booking lookup (added).
		 *
		 * Titan operates a single shared phone, so only one team member can be
		 * on a call at any moment. This returns every confirmed booking on the
		 * given date regardless of which service, staff member or communication
		 * method it belongs to, so the slot generator can treat them all as
		 * blocking the shared resource. Each booking's end is padded by the
		 * global cross-staff buffer so there is a guaranteed gap between calls.
		 */
		private function get_all_bookings_for_day( $date, $exclude_booking_id = 0 ) {
			global $wpdb;
			if ( ! $this->table_exists() ) {
				return array();
			}
			$this->ensure_booking_quantity_column();

			$settings    = $this->get_settings();
			$cross_buffer = max( 0, absint( isset( $settings['shared_resource_buffer'] ) ? $settings['shared_resource_buffer'] : 0 ) );

			$exclude_sql  = '';
			$query_params = array( $date, self::STATUS_CONFIRMED, self::STATUS_HELD, self::STATUS_PENDING_APPROVAL );
			if ( $exclude_booking_id > 0 ) {
				$exclude_sql    = ' AND id != %d';
				$query_params[] = $exclude_booking_id;
			}

			$sql = "SELECT id, booking_time, booking_end_time, spaces_booked FROM {$this->table} WHERE booking_date = %s AND booking_status IN (%s, %s, %s){$exclude_sql}";
			$rows = $wpdb->get_results( $wpdb->prepare( $sql, $query_params ), ARRAY_A );

			$bookings = array();
			foreach ( $rows as $row ) {
				$start = $this->time_to_minutes( substr( (string) $row['booking_time'], 0, 5 ) );
				$end   = $this->time_to_minutes( substr( (string) $row['booking_end_time'], 0, 5 ) );
				// Pad the end with the cross-staff buffer so the next call cannot
				// start until the gap has elapsed. spaces_booked is deliberately 1
				// here: this list models "the shared phone is busy", not the
				// capacity of any one session. merge_booking_occupancy() restores
				// the true quantity for bookings that also appear in the
				// per-combination list.
				$bookings[] = array(
					'id'            => absint( isset( $row['id'] ) ? $row['id'] : 0 ),
					'start'         => $start,
					'end'           => $end + $cross_buffer,
					'spaces_booked' => 1,
				);
			}
			return $bookings;
		}

		/**
		 * Daily booking cap (added).
		 *
		 * Returns true when bookings for the given date have reached the cap.
		 * The cap is read per staff member first (Staff -> Max bookings per day
		 * in admin); if that is zero/blank, the global cap in Settings is used;
		 * if that is also zero, there is no cap.
		 */

		private function licence_booking_form_allowed() {
			return function_exists( 'tj_appt_licence_booking_form_allowed' ) && tj_appt_licence_booking_form_allowed();
		}

		private function licence_booking_date_allowed( $date ) {
			return function_exists( 'tj_appt_licence_booking_date_allowed' ) && tj_appt_licence_booking_date_allowed( $date );
		}

		private function licence_booking_date_restricted( $date ) {
			return function_exists( 'tj_appt_licence_booking_date_restricted' ) && tj_appt_licence_booking_date_restricted( $date );
		}

		/**
		 * Has the one-booking allowance for a restricted date already been used?
		 *
		 * The option log deliberately survives cancellation, rescheduling and
		 * booking deletion. Existing rows also count so appointments accepted while
		 * licensed cannot later leave an extra restricted slot available.
		 */
		private function restricted_date_is_consumed( $date, $exclude_booking_id = 0 ) {
			if ( ! $this->licence_booking_date_restricted( $date ) ) {
				return false;
			}

			$used = get_option( self::OPTION_RESTRICTED_DATES, array() );
			if ( ! is_array( $used ) ) {
				$used = array();
			}
			if ( isset( $used[ $date ] ) ) {
				$owner_id = absint( $used[ $date ] );
				if ( 0 === $exclude_booking_id || $owner_id !== $exclude_booking_id ) {
					return true;
				}
			}

			if ( ! $this->table_exists() ) {
				return false;
			}

			global $wpdb;
			$sql = "SELECT id FROM {$this->table} WHERE booking_date = %s";
			$params = array( $date );
			if ( $exclude_booking_id > 0 ) {
				$sql .= ' AND id != %d';
				$params[] = $exclude_booking_id;
			}
			$sql .= ' LIMIT 1';
			return (bool) $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
		}

		private function mark_restricted_date_consumed( $date, $booking_id ) {
			if ( ! $this->licence_booking_date_restricted( $date ) || $booking_id <= 0 ) {
				return;
			}
			$used = get_option( self::OPTION_RESTRICTED_DATES, array() );
			if ( ! is_array( $used ) ) {
				$used = array();
			}
			if ( ! isset( $used[ $date ] ) ) {
				$used[ $date ] = absint( $booking_id );
				update_option( self::OPTION_RESTRICTED_DATES, $used, false );
			}
		}

		private function daily_cap_reached( $staff, $date, $exclude_booking_id = 0 ) {
			global $wpdb;
			if ( ! $this->table_exists() || '' === (string) $date ) {
				return false;
			}

			$settings   = $this->get_settings();
			$global_cap = max( 0, absint( isset( $settings['daily_booking_cap'] ) ? $settings['daily_booking_cap'] : 0 ) );

			$staff_cap = 0;
			if ( is_array( $staff ) && isset( $staff['daily_cap'] ) ) {
				$staff_cap = max( 0, absint( $staff['daily_cap'] ) );
			}

			$cap = $staff_cap > 0 ? $staff_cap : $global_cap;
			if ( $cap <= 0 ) {
				return false; // No cap configured.
			}

			$exclude_sql  = '';
			$query_params = array( $date, self::STATUS_CONFIRMED, self::STATUS_HELD, self::STATUS_PENDING_APPROVAL );

			// A per-staff cap only counts that staff member's bookings; a global
			// cap counts every booking on the date.
			if ( $staff_cap > 0 && is_array( $staff ) && ! empty( $staff['id'] ) ) {
				$staff_sql      = ' AND staff_id = %s';
				$query_params[] = $staff['id'];
			} else {
				$staff_sql = '';
			}

			if ( $exclude_booking_id > 0 ) {
				$exclude_sql    = ' AND id != %d';
				$query_params[] = $exclude_booking_id;
			}

			$sql   = "SELECT COUNT(*) FROM {$this->table} WHERE booking_date = %s AND booking_status IN (%s, %s, %s){$staff_sql}{$exclude_sql}";
			$count = (int) $wpdb->get_var( $wpdb->prepare( $sql, $query_params ) );

			return $count >= $cap;
		}


		private function build_slots( $service, $staff, $date, $exclude_booking_id = 0, $location = false ) {
			$slots = array();
			if ( ! $this->licence_booking_date_allowed( $date ) ) {
				return $slots;
			}
			if ( $this->restricted_date_is_consumed( $date, $exclude_booking_id ) ) {
				return $slots;
			}
			if ( ! $service ) {
				return $slots;
			}
			if ( $staff ) {
				$location_id = $location && ! empty( $location['id'] ) ? $location['id'] : '';
				if ( ! $this->staff_can_deliver_service_at_location( $service, $staff, $location_id ) ) {
					return $slots;
				}
			}

			$rules = $this->resolve_effective_rules( $service, $staff );
			if ( ! $this->date_is_within_limits_for_rules( $date, $rules ) ) {
				return $slots;
			}

			$duration      = max( 1, absint( $rules['duration'] ) );
			$buffer        = max( 0, absint( $rules['buffer'] ) );
			$slot_capacity = $this->licence_booking_date_restricted( $date ) ? 1 : max( 1, absint( isset( $rules['slot_capacity'] ) ? $rules['slot_capacity'] : 1 ) );
			// If the admin set a custom offer interval (e.g. offer every 30 min
			// even though the slot footprint is 60 min), use that as the stride
			// between slot start times. Otherwise fall back to the legacy
			// duration + buffer behaviour.
			$offer_interval = max( 0, absint( isset( $rules['slot_offer_interval'] ) ? $rules['slot_offer_interval'] : 0 ) );
			$slot_step     = $offer_interval > 0 ? $offer_interval : ( $duration + $buffer );
			$existing  = $this->get_existing_bookings_for_day( $location ? $location['id'] : '', $service['id'], $staff ? $staff['id'] : '', $date, $exclude_booking_id );

			// Shared phone: merge in every other booking on this date so no two
			// calls (whoever they are assigned to) can overlap, and so the
			// cross-staff buffer is enforced. The merge must de-duplicate by
			// booking id - add_slots_from_window() sums spaces_booked, so a
			// booking present in both lists would consume capacity twice - and it
			// tags foreign bookings so they close the slot outright rather than
			// taking one unit out of its capacity.
			$settings_for_slots = $this->get_settings();
			$shared_resource_mode = ! empty( $settings_for_slots['shared_resource_enabled'] );
			if ( $shared_resource_mode ) {
				$shared = $this->get_all_bookings_for_day( $date, $exclude_booking_id );
				if ( ! empty( $shared ) ) {
					$existing = $this->merge_booking_occupancy( $existing, $shared );
				}
			}

			// Daily cap: if the whole-day booking count has reached the cap for
			// the assigned staff member (or the global cap), no slots are offered.
			if ( $this->daily_cap_reached( $staff, $date, $exclude_booking_id ) ) {
				return $slots;
			}

			$min_ts    = current_time( 'timestamp' ) + ( absint( $rules['min_notice_hours'] ) * HOUR_IN_SECONDS );

			$day_key = strtolower( gmdate( 'D', strtotime( $date ) ) );
			$shared_staff_hours = $staff ? $this->get_shared_staff_hours_for_service( $service, $staff['id'] ) : false;

			if ( is_array( $shared_staff_hours ) && ! empty( $shared_staff_hours ) ) {
				// One service/team schedule is shared across every allowed contact method.
				$shared_day_hours = isset( $shared_staff_hours[ $day_key ] ) ? $shared_staff_hours[ $day_key ] : false;
				$windows = $this->hours_row_to_windows( $shared_day_hours );
				if ( empty( $windows ) ) { return $slots; }
			} else {
				$service_hours = isset( $rules['hours'][ $day_key ] ) ? $rules['hours'][ $day_key ] : false;
				$windows = $this->hours_row_to_windows( $service_hours );
				if ( empty( $windows ) ) { return $slots; }
				if ( $staff ) {
					$staff_hours = isset( $staff['hours'][ $day_key ] ) ? $staff['hours'][ $day_key ] : false;
					$staff_windows = $this->hours_row_to_windows( $staff_hours );
					if ( empty( $staff_windows ) ) { return $slots; }
					$windows = $this->intersect_availability_windows( $windows, $staff_windows );
					if ( empty( $windows ) ) { return $slots; }
				}
			}

			$blocked_windows = array();
			if ( $staff ) {
				$exceptions = $this->get_staff_date_exceptions_for_date( $staff, $date );
				if ( ! empty( $exceptions ) ) {
					foreach ( $exceptions as $exception ) {
						if ( 'off' === $exception['mode'] ) {
							return array();
						}
						if ( 'block' === $exception['mode'] || 'custom' === $exception['mode'] ) {
							$blocked_windows[] = array(
								'start' => $this->time_to_minutes( $exception['start'] ),
								'end'   => $this->time_to_minutes( $exception['end'] ),
							);
						}
					}
				}
			}

			if ( $slot_step <= 0 ) {
				return $slots;
			}

			foreach ( $windows as $window ) {
				$this->add_slots_from_window( $slots, $date, $window['start'], $window['end'], $duration, $slot_step, $existing, $min_ts, $blocked_windows, $slot_capacity, $shared_resource_mode );
			}

			usort( $slots, function( $a, $b ) {
				return strcmp( $a['value'], $b['value'] );
			} );

			return array_values( $slots );
		}

		/**
		 * Build the full set of potential slots for a date, with a status
		 * per slot ('available', 'booked', 'past', 'blocked', 'capped').
		 *
		 * Used by the customer-facing time-grid display so it can render
		 * every working slot and grey out the ones that can't be taken.
		 * Mirrors build_slots() exactly for shared inputs (windows, hours,
		 * step, footprint) so the available subset matches one-to-one.
		 */
		private function build_slots_with_status( $service, $staff, $date, $exclude_booking_id = 0, $location = false ) {
			$result = array();
			if ( ! $this->licence_booking_date_allowed( $date ) || $this->restricted_date_is_consumed( $date, $exclude_booking_id ) ) { return $result; }
			if ( ! $service ) { return $result; }
			if ( $staff ) {
				$location_id = $location && ! empty( $location['id'] ) ? $location['id'] : '';
				if ( ! $this->staff_can_deliver_service_at_location( $service, $staff, $location_id ) ) {
					return $result;
				}
			}
			$rules = $this->resolve_effective_rules( $service, $staff );
			if ( ! $this->date_is_within_limits_for_rules( $date, $rules ) ) {
				return $result;
			}

			$duration       = max( 1, absint( $rules['duration'] ) );
			$buffer         = max( 0, absint( $rules['buffer'] ) );
			$slot_capacity  = $this->licence_booking_date_restricted( $date ) ? 1 : max( 1, absint( isset( $rules['slot_capacity'] ) ? $rules['slot_capacity'] : 1 ) );
			$offer_interval = max( 0, absint( isset( $rules['slot_offer_interval'] ) ? $rules['slot_offer_interval'] : 0 ) );
			$slot_step      = $offer_interval > 0 ? $offer_interval : ( $duration + $buffer );
			if ( $slot_step <= 0 ) { return $result; }

			$existing = $this->get_existing_bookings_for_day( $location ? $location['id'] : '', $service['id'], $staff ? $staff['id'] : '', $date, $exclude_booking_id );
			$settings_for_slots = $this->get_settings();
			$shared_resource_mode = ! empty( $settings_for_slots['shared_resource_enabled'] );
			if ( $shared_resource_mode ) {
				$shared = $this->get_all_bookings_for_day( $date, $exclude_booking_id );
				if ( ! empty( $shared ) ) {
					// De-duplicate by booking id - see merge_booking_occupancy().
					$existing = $this->merge_booking_occupancy( $existing, $shared );
				}
			}
			$day_capped = $this->daily_cap_reached( $staff, $date, $exclude_booking_id );

			$min_ts = current_time( 'timestamp' ) + ( absint( $rules['min_notice_hours'] ) * HOUR_IN_SECONDS );

			$day_key = strtolower( gmdate( 'D', strtotime( $date ) ) );
			$shared_staff_hours = $staff ? $this->get_shared_staff_hours_for_service( $service, $staff['id'] ) : false;

			if ( is_array( $shared_staff_hours ) && ! empty( $shared_staff_hours ) ) {
				$shared_day_hours = isset( $shared_staff_hours[ $day_key ] ) ? $shared_staff_hours[ $day_key ] : false;
				$windows = $this->hours_row_to_windows( $shared_day_hours );
				if ( empty( $windows ) ) { return $result; }
			} else {
				$service_hours = isset( $rules['hours'][ $day_key ] ) ? $rules['hours'][ $day_key ] : false;
				$windows = $this->hours_row_to_windows( $service_hours );
				if ( empty( $windows ) ) { return $result; }
				if ( $staff ) {
					$staff_hours = isset( $staff['hours'][ $day_key ] ) ? $staff['hours'][ $day_key ] : false;
					$staff_windows = $this->hours_row_to_windows( $staff_hours );
					if ( empty( $staff_windows ) ) { return $result; }
					$windows = $this->intersect_availability_windows( $windows, $staff_windows );
					if ( empty( $windows ) ) { return $result; }
				}
			}

			$blocked_windows = array();
			if ( $staff ) {
				$exceptions = $this->get_staff_date_exceptions_for_date( $staff, $date );
				if ( ! empty( $exceptions ) ) {
					foreach ( $exceptions as $exception ) {
						if ( 'off' === $exception['mode'] ) {
							return array();
						}
						if ( 'block' === $exception['mode'] || 'custom' === $exception['mode'] ) {
							$blocked_windows[] = array(
								'start' => $this->time_to_minutes( $exception['start'] ),
								'end'   => $this->time_to_minutes( $exception['end'] ),
							);
						}
					}
				}
			}

			// Walk every window, emit one entry per stride. Status reflects
			// why a slot is unavailable, in priority order: past, blocked,
			// booked, capped, available.
			foreach ( $windows as $window ) {
				for ( $slot_start = $window['start']; $slot_start + $duration <= $window['end']; $slot_start += $slot_step ) {
					$slot_end   = $slot_start + $duration;
					$slot_value = $this->minutes_to_time( $slot_start );
					$slot_ts    = strtotime( $date . ' ' . $slot_value . ':00' );

					$status = 'available';
					if ( $slot_ts < $min_ts ) {
						$status = 'past';
					} elseif ( $this->slot_overlaps_windows( $slot_start, $slot_end, $blocked_windows ) ) {
						$status = 'blocked';
					} else {
						// Mirrors add_slots_from_window() exactly, so the grid can
						// never show a slot as available that build_slots() would
						// refuse to sell.
						$booked_spaces = 0;
						$phone_in_use  = false;
						foreach ( $existing as $booking ) {
							if ( $slot_start >= $booking['end'] || $slot_end <= $booking['start'] ) {
								continue;
							}
							if ( $shared_resource_mode && ! $this->booking_is_same_session( $booking, $slot_start ) ) {
								$phone_in_use = true;
								break;
							}
							$booked_spaces += max( 1, absint( isset( $booking['spaces_booked'] ) ? $booking['spaces_booked'] : 1 ) );
						}
						if ( $phone_in_use || $booked_spaces >= $slot_capacity ) {
							$status = 'booked';
						} elseif ( $day_capped ) {
							$status = 'capped';
						}
					}

					$result[] = array(
						'value'  => $slot_value,
						'label'  => $slot_value,
						'status' => $status,
					);
				}
			}

			usort( $result, function( $a, $b ) {
				return strcmp( $a['value'], $b['value'] );
			} );
			return array_values( $result );
		}


		private function get_admin_locations_for_select() {
			return $this->get_active_locations();
		}

		private function get_admin_services_for_select( $location_id = '' ) {
			if ( '' === (string) $location_id ) {
				return $this->get_active_services();
			}
			return $this->get_services_for_location( $location_id );
		}

		private function get_admin_staff_for_select( $service_id = '', $location_id = '' ) {
			if ( '' !== (string) $service_id ) {
				$service = $this->find_service( $service_id );
				if ( $service ) {
					return $this->get_allowed_staff_for_service_location( $service, $location_id );
				}
			}
			$staff_rows = $this->get_active_staff();
			if ( '' === (string) $location_id ) {
				return $staff_rows;
			}
			return array_values(
				array_filter(
					$staff_rows,
					function ( $staff ) use ( $location_id ) {
						return $this->staff_matches_location( $staff, $location_id );
					}
				)
			);
		}

		private function get_booking_by_id( $booking_id ) {
			global $wpdb;
			if ( ! $this->table_exists() || $booking_id <= 0 ) {
				return null;
			}
			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $booking_id ) );
		}

		/* ===================================================================
		 * Dashboard widget + morning summary email (added).
		 * =================================================================== */

		/**
		 * Fetch confirmed bookings on or after a given date, soonest first.
		 */
		private function get_upcoming_bookings( $from_date = '', $limit = 7 ) {
			global $wpdb;
			if ( ! $this->table_exists() ) {
				return array();
			}
			$from_date = '' !== $from_date ? $from_date : current_time( 'Y-m-d' );
			$limit     = max( 1, absint( $limit ) );
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$this->table}
					WHERE booking_date >= %s AND booking_status = %s
					ORDER BY booking_date ASC, booking_time ASC
					LIMIT %d",
					$from_date,
					self::STATUS_CONFIRMED,
					$limit
				)
			);
		}

		/**
		 * Fetch confirmed bookings for one specific date.
		 */
		private function get_bookings_for_single_date( $date ) {
			global $wpdb;
			if ( ! $this->table_exists() || '' === (string) $date ) {
				return array();
			}
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$this->table}
					WHERE booking_date = %s AND booking_status = %s
					ORDER BY booking_time ASC",
					$date,
					self::STATUS_CONFIRMED
				)
			);
		}

		public function register_dashboard_widget() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			wp_add_dashboard_widget(
				'rgl_booking_upcoming',
				'Upcoming Appointments',
				array( $this, 'render_dashboard_widget' )
			);
		}

		public function render_dashboard_widget() {
			$bookings = $this->get_upcoming_bookings( '', 8 );
			$today    = current_time( 'Y-m-d' );

			if ( empty( $bookings ) ) {
				echo '<p style="margin:0;color:#666;">No upcoming appointments.</p>';
				return;
			}

			$today_count = 0;
			foreach ( $bookings as $b ) {
				if ( $b->booking_date === $today ) { $today_count++; }
			}

			echo '<p style="margin:0 0 10px;font-weight:600;">';
			echo esc_html(
				$today_count > 0
					? sprintf( '%d appointment%s today, %d upcoming in total.', $today_count, 1 === $today_count ? '' : 's', count( $bookings ) )
					: sprintf( '%d upcoming appointment%s.', count( $bookings ), 1 === count( $bookings ) ? '' : 's' )
			);
			echo '</p>';

			echo '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
			foreach ( $bookings as $b ) {
				$is_today  = ( $b->booking_date === $today );
				$row_style = $is_today
					? 'border-left:3px solid #b42318;background:#fcf3f2;'
					: 'border-left:3px solid #e0e0e0;';
				$time      = substr( (string) $b->booking_time, 0, 5 );
				$datelabel = $is_today ? 'Today' : $this->format_booking_short_date( $b );
				$staff     = ! empty( $b->staff_name ) ? $b->staff_name : 'Unassigned';
				$method    = ! empty( $b->location_name ) ? $b->location_name : '';
				$has_msg   = ! empty( $b->customer_notes );

				echo '<tr style="' . esc_attr( $row_style ) . '">';
				echo '<td style="padding:6px 8px;white-space:nowrap;vertical-align:top;"><strong>' . esc_html( $datelabel ) . '</strong><br>' . esc_html( $time ) . '</td>';
				echo '<td style="padding:6px 8px;vertical-align:top;">' . esc_html( $b->customer_name );
				echo '<br><span style="color:#666;">' . esc_html( $b->service_name );
				if ( '' !== $method ) { echo ' &middot; ' . esc_html( $method ); }
				echo '</span>';
				if ( $has_msg ) { echo '<br><span style="color:#b42318;font-size:12px;">Has a message</span>'; }
				echo '</td>';
				echo '<td style="padding:6px 8px;white-space:nowrap;vertical-align:top;text-align:right;"><strong>' . esc_html( $staff ) . '</strong></td>';
				echo '</tr>';
			}
			echo '</table>';

			$list_url = admin_url( 'admin.php?page=appt-booker' );
			echo '<p style="margin:10px 0 0;"><a href="' . esc_url( $list_url ) . '">View all bookings</a></p>';
		}

		/**
		 * Ensure the daily summary email is scheduled for ~09:00 site time.
		 */
		public function maybe_schedule_daily_summary() {
			if ( wp_next_scheduled( 'rgl_booking_daily_summary' ) ) {
				return;
			}
			// Next 09:00 in site timezone.
			$tz   = wp_timezone();
			$now  = new DateTime( 'now', $tz );
			$nine = new DateTime( 'today 09:00', $tz );
			if ( $now >= $nine ) {
				$nine->modify( '+1 day' );
			}
			wp_schedule_event( $nine->getTimestamp(), 'daily', 'rgl_booking_daily_summary' );
		}

		/**
		 * Send the combined 09:00 summary of the day's appointments to admin
		 * and all active staff. Sends nothing at all if there are no bookings.
		 */
		public function send_daily_summary_email() {
			$today    = current_time( 'Y-m-d' );
			$bookings = $this->get_bookings_for_single_date( $today );
			if ( empty( $bookings ) ) {
				return; // No email on empty days.
			}

			// Recipients: site admin + every active staff member with an email.
			$recipients = array();
			$admin_email = get_option( 'admin_email' );
			if ( is_email( $admin_email ) ) {
				$recipients[] = $admin_email;
			}
			foreach ( $this->get_active_staff() as $staff_row ) {
				if ( ! empty( $staff_row['email'] ) && is_email( $staff_row['email'] ) ) {
					$recipients[] = $staff_row['email'];
				}
			}
			$recipients = array_values( array_unique( $recipients ) );
			if ( empty( $recipients ) ) {
				return;
			}

			$display_date = $this->format_booking_short_date( $today );
			$count        = count( $bookings );
			$subject      = sprintf( 'Appointments today (%s) - %d booking%s', $display_date, $count, 1 === $count ? '' : 's' );

			// Build rows. The summary is sent as HTML only; an unused plain-text
			// body used to be assembled here and silently thrown away.
			$rows_html  = '';
			foreach ( $bookings as $b ) {
				$time   = substr( (string) $b->booking_time, 0, 5 );
				$staff  = ! empty( $b->staff_name ) ? $b->staff_name : 'Unassigned';
				$method = ! empty( $b->location_name ) ? $b->location_name : '';
				$msg    = ! empty( $b->customer_notes ) ? trim( wp_strip_all_tags( $b->customer_notes ) ) : '';
				$notes  = ! empty( $b->admin_notes ) ? trim( wp_strip_all_tags( $b->admin_notes ) ) : '';
				if ( is_numeric( $notes ) && 0.0 === (float) $notes ) { $notes = ''; }
				$interests = ! empty( $b->customer_interests ) ? trim( (string) $b->customer_interests ) : '';

				$detail = $b->service_name;
				if ( '' !== $method ) { $detail .= ' &middot; ' . $method; }

				$rows_html .= '<tr>'
					. '<td style="padding:8px 12px 8px 0;font-weight:700;white-space:nowrap;vertical-align:top;">' . esc_html( $time ) . '</td>'
					. '<td style="padding:8px 0;vertical-align:top;">'
					. '<strong>' . esc_html( $b->customer_name ) . '</strong> &mdash; ' . esc_html( $staff ) . '<br>'
					. '<span style="color:#555;">' . esc_html( wp_strip_all_tags( $detail ) ) . '</span>';
				if ( '' !== $interests ) {
					$rows_html .= '<br><span style="color:#555;">Topics: ' . esc_html( $interests ) . '</span>';
				}
				if ( '' !== $msg ) {
					$rows_html .= '<br><span style="color:#2b2b2b;">Their message: ' . $this->linkify_text( $msg, '#2b2b2b' ) . '</span>';
				}
				if ( '' !== $notes ) {
					$rows_html .= '<br><span style="color:#6b6257;">Notes: ' . $this->linkify_text( $notes, '#6b6257' ) . '</span>';
				}
				$rows_html .= '</td></tr>';
			}

			$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
			$html = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>@media only screen and (max-width:620px){.rgl-email-shell{padding:12px 8px!important}.rgl-email-header{padding:22px 20px 16px!important}.rgl-email-body{padding:22px 20px 8px!important}.rgl-email-footer{padding:16px 20px 22px!important}.rgl-email-details{padding:6px 14px!important}.rgl-email-detail-row,.rgl-email-detail-label,.rgl-email-detail-value{display:block!important;width:100%!important;max-width:100%!important;box-sizing:border-box!important}.rgl-email-detail-label{padding:10px 0 2px!important;white-space:normal!important;font-weight:700!important;color:#2b2b2b!important}.rgl-email-detail-value{padding:0 0 10px!important;font-weight:400!important;overflow-wrap:anywhere!important}.rgl-email-detail-row:first-child .rgl-email-detail-label{padding-top:6px!important}}</style></head>'
				. '<body style="margin:0;padding:0;background:#f3ede1;">'
				. '<table class="rgl-email-shell" role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3ede1;padding:24px 12px;"><tr><td align="center">'
				. '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fffdf8;border:1px solid #e6dcc8;border-radius:12px;overflow:hidden;">'
				. '<tr><td style="padding:24px 32px 14px;border-bottom:1px solid #ece3d2;font-family:Arial,Helvetica,sans-serif;">'
				. '<span style="font-family:Georgia,serif;font-size:20px;font-weight:700;color:#2b2b2b;">' . esc_html( $site ) . '</span>'
				. '<br><span style="font-size:14px;color:#6b6257;">Appointments for ' . esc_html( $display_date ) . '</span>'
				. '</td></tr>'
				. '<tr><td style="padding:18px 32px 24px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#2b2b2b;">'
				. '<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;">' . $rows_html . '</table>'
				. '</td></tr>'
				. '</table></td></tr></table></body></html>';

			// Send HTML to each recipient. send_mail handles From name/address.
			foreach ( $recipients as $to ) {
				$this->send_mail( $to, $subject, $html, array(), true );
			}
		}

		/**
		 * Schedule an hourly sweep for customer appointment reminders. The sweep
		 * itself reads the current settings, so changing the lead time does not
		 * require rescheduling individual bookings.
		 */
		public function maybe_schedule_appointment_reminders() {
			$settings = $this->get_settings();
			if ( empty( $settings['reminder_enabled'] ) ) {
				wp_clear_scheduled_hook( 'rgl_booking_reminder_sweep' );
				return;
			}
			if ( ! wp_next_scheduled( 'rgl_booking_reminder_sweep' ) ) {
				wp_schedule_event( time() + 5 * MINUTE_IN_SECONDS, 'hourly', 'rgl_booking_reminder_sweep' );
			}
		}

		/**
		 * Send each confirmed booking one reminder when it enters the configured
		 * lead-time window. Bookings made after their nominal reminder point are
		 * deliberately skipped rather than receiving an immediate duplicate of the
		 * confirmation email.
		 */
		public function send_due_appointment_reminders() {
			$settings = $this->get_settings();
			if ( empty( $settings['reminder_enabled'] ) || ! $this->table_exists() ) {
				return;
			}
			$this->ensure_reminder_sent_column();
			if ( ! $this->reminder_sent_column_exists() ) {
				return;
			}

			$hours = max( 1, min( 168, absint( isset( $settings['reminder_hours_before'] ) ? $settings['reminder_hours_before'] : 24 ) ) );
			$now   = current_datetime();
			$end   = $now->modify( '+' . $hours . ' hours' );

			global $wpdb;
			$bookings = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$this->table}
					 WHERE booking_status = %s
					   AND reminder_sent_at IS NULL
					   AND customer_email <> ''
					   AND booking_date BETWEEN %s AND %s
					 ORDER BY booking_date ASC, booking_time ASC",
					self::STATUS_CONFIRMED,
					$now->format( 'Y-m-d' ),
					$end->format( 'Y-m-d' )
				)
			);

			foreach ( (array) $bookings as $booking ) {
				if ( empty( $booking->id ) || empty( $booking->customer_email ) || ! is_email( $booking->customer_email ) ) {
					continue;
				}
				try {
					$start = new DateTimeImmutable( (string) $booking->booking_date . ' ' . (string) $booking->booking_time, wp_timezone() );
					$created = new DateTimeImmutable( (string) $booking->created_at, wp_timezone() );
				} catch ( Exception $e ) {
					continue;
				}
				$reminder_point = $start->modify( '-' . $hours . ' hours' );
				if ( $start <= $now || $start > $end || $now < $reminder_point ) {
					continue;
				}
				if ( $created > $reminder_point ) {
					continue;
				}

				$claimed_at = current_time( 'mysql' );
				$claimed = $wpdb->query(
					$wpdb->prepare(
						"UPDATE {$this->table} SET reminder_sent_at = %s WHERE id = %d AND reminder_sent_at IS NULL AND booking_status = %s",
						$claimed_at,
						(int) $booking->id,
						self::STATUS_CONFIRMED
					)
				);
				if ( 1 !== (int) $claimed ) {
					continue;
				}

				$latest = $this->get_booking_by_id( (int) $booking->id );
				if ( ! $latest || self::STATUS_CONFIRMED !== (string) $latest->booking_status || (string) $latest->reminder_sent_at !== (string) $claimed_at ) {
					continue;
				}
				if ( ! $this->send_booking_notification( 'booking_reminder', 'customer', $latest ) ) {
					$wpdb->query( $wpdb->prepare( "UPDATE {$this->table} SET reminder_sent_at = NULL WHERE id = %d AND reminder_sent_at = %s", (int) $latest->id, $claimed_at ) );
				}
			}
		}

		/* ===================================================================
		 * Data retention (added).
		 *
		 * Two-stage lifecycle, both off by default:
		 *   Stage 1 - minimise: after N months, strip email / phone / address
		 *             but keep name, dates, service, notes etc. The record is
		 *             still personal data (it has a name) but holds nothing a
		 *             breach could exploit.
		 *   Stage 2 - delete: after M months, remove the row entirely.
		 * Applies to confirmed and cancelled bookings alike. Age is measured
		 * from created_at.
		 * =================================================================== */

		public function maybe_schedule_retention_sweep() {
			if ( wp_next_scheduled( 'rgl_booking_retention_sweep' ) ) {
				return;
			}
			// Run daily, a little after 03:00 site time (quiet hours).
			$tz    = wp_timezone();
			$now   = new DateTime( 'now', $tz );
			$three = new DateTime( 'today 03:15', $tz );
			if ( $now >= $three ) {
				$three->modify( '+1 day' );
			}
			wp_schedule_event( $three->getTimestamp(), 'daily', 'rgl_booking_retention_sweep' );
		}

		/**
		 * The daily retention sweep. Does nothing unless retention is enabled -
		 * but note that it IS enabled by default, and is force-enabled on upgrade
		 * for existing installations. Both stages are irreversible: stage 2
		 * deletes rows outright, stage 1 blanks contact details.
		 *
		 * Blanking customer_email also invalidates every previously issued
		 * booking-summary, ICS, cancellation and reschedule link for that record,
		 * because get_booking_access_token_for_booking() hashes the email.
		 */
		public function run_retention_sweep() {
			global $wpdb;
			if ( ! $this->table_exists() ) {
				return;
			}
			$settings = $this->get_settings();
			if ( empty( $settings['retention_enabled'] ) ) {
				return; // Off by default - never acts until switched on.
			}

			$minimise_months = max( 1, absint( isset( $settings['retention_minimise_months'] ) ? $settings['retention_minimise_months'] : 6 ) );
			$delete_months   = max( 1, absint( isset( $settings['retention_delete_months'] ) ? $settings['retention_delete_months'] : 30 ) );

			// Stage 2 first: delete anything past the delete threshold.
			$delete_before = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $delete_months . ' months', current_time( 'timestamp' ) ) );
			// Batched so a large first sweep cannot hold a long write lock on the
			// table (or time out) on shared hosting.
			$deleted = 0;
			do {
				$batch = (int) $wpdb->query(
					$wpdb->prepare( "DELETE FROM {$this->table} WHERE created_at < %s LIMIT 500", $delete_before )
				);
				$deleted += max( 0, $batch );
			} while ( $batch >= 500 );

			// Stage 1: minimise anything past the minimise threshold that still
			// has contact details. Use a sentinel in admin_notes is unnecessary;
			// instead we only target rows that still have an email or phone, so
			// re-running is naturally idempotent.
			$minimise_before = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $minimise_months . ' months', current_time( 'timestamp' ) ) );
			$minimised = (int) $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$this->table}
					SET customer_email = '', customer_phone = ''
					WHERE created_at < %s
					AND ( customer_email <> '' OR customer_phone <> '' )",
					$minimise_before
				)
			);

			if ( $deleted > 0 || $minimised > 0 ) {
				$this->log_retention_action( sprintf( 'Retention sweep: %d minimised, %d deleted.', $minimised, $deleted ) );
			}
		}

		/**
		 * Minimal retention log - a short rolling list of actions, no copies of
		 * the data that was removed. Kept to the most recent 50 lines.
		 */
		private function log_retention_action( $line ) {
			$log = get_option( 'rgl_booking_retention_log', array() );
			if ( ! is_array( $log ) ) {
				$log = array();
			}
			$log[] = array(
				'time' => current_time( 'mysql' ),
				'note' => sanitize_text_field( $line ),
			);
			if ( count( $log ) > 50 ) {
				$log = array_slice( $log, -50 );
			}
			update_option( 'rgl_booking_retention_log', $log, false );
		}

		/**
		 * Sanitise the blocked-emails textarea: normalise whitespace, lowercase,
		 * de-duplicate, drop entries that are not valid emails.
		 */
		private function sanitize_blocked_emails( $raw ) {
			$raw = (string) $raw;
			$lines = preg_split( '/[\r\n,;]+/', $raw );
			$clean = array();
			foreach ( $lines as $line ) {
				$email = strtolower( trim( $line ) );
				if ( '' === $email ) { continue; }
				if ( ! is_email( $email ) ) { continue; }
				if ( in_array( $email, $clean, true ) ) { continue; }
				$clean[] = $email;
			}
			return implode( "\n", $clean );
		}

		/**
		 * True if the email is on the blocked list. Admins are not blocked.
		 */
		private function email_is_blocked( $email ) {
			if ( $this->current_request_is_admin() ) { return false; }
			$email = strtolower( trim( (string) sanitize_email( $email ) ) );
			if ( '' === $email ) { return false; }
			return false !== $this->get_booking_block_for_email( $email ) || $this->email_is_legacy_blocked( $email );
		}

		/**
		 * Add an email to the blocked list (idempotent). Used by the Mark as Spam action.
		 */
		private function add_blocked_email( $email ) {
			$email = strtolower( trim( (string) sanitize_email( $email ) ) );
			if ( '' === $email || ! is_email( $email ) ) { return false; }
			$settings = $this->get_settings();
			$raw = isset( $settings['blocked_emails'] ) ? (string) $settings['blocked_emails'] : '';
			$list = array_filter( array_map( 'trim', preg_split( '/[\r\n,;]+/', $raw ) ) );
			$list = array_map( 'strtolower', $list );
			if ( in_array( $email, $list, true ) ) { return true; }
			$list[] = $email;
			$settings['blocked_emails'] = implode( "\n", $list );
			update_option( self::OPTION_SETTINGS, $settings );
			return true;
		}


		private function email_is_legacy_blocked( $email ) {
			$email = strtolower( trim( (string) sanitize_email( $email ) ) );
			if ( '' === $email ) { return false; }
			$settings = $this->get_settings();
			$raw = isset( $settings['blocked_emails'] ) ? (string) $settings['blocked_emails'] : '';
			foreach ( preg_split( '/[\r\n,;]+/', $raw ) as $entry ) {
				if ( strtolower( trim( $entry ) ) === $email ) { return true; }
			}
			return false;
		}

		private function remove_legacy_blocked_email( $email ) {
			$email = strtolower( trim( (string) sanitize_email( $email ) ) );
			if ( '' === $email ) { return false; }
			$settings = $this->get_settings();
			$raw = isset( $settings['blocked_emails'] ) ? (string) $settings['blocked_emails'] : '';
			$list = array();
			foreach ( preg_split( '/[\r\n,;]+/', $raw ) as $entry ) {
				$entry = strtolower( trim( $entry ) );
				if ( '' !== $entry && $entry !== $email && is_email( $entry ) ) { $list[] = $entry; }
			}
			$settings['blocked_emails'] = implode( "\n", array_values( array_unique( $list ) ) );
			update_option( self::OPTION_SETTINGS, $settings );
			return true;
		}

		private function booking_block_hash( $email ) {
			$email = strtolower( trim( (string) sanitize_email( $email ) ) );
			if ( '' === $email ) { return ''; }
			return hash_hmac( 'sha256', $email, wp_salt( 'auth' ) );
		}

		private function get_active_booking_blocks() {
			$blocks = get_option( self::OPTION_BOOKING_BLOCKS, array() );
			$blocks = is_array( $blocks ) ? $blocks : array();
			$now = time();
			$changed = false;
			foreach ( $blocks as $hash => $block ) {
				$expires = is_array( $block ) && isset( $block['expires_at'] ) ? absint( $block['expires_at'] ) : 0;
				if ( ! is_string( $hash ) || 64 !== strlen( $hash ) || $expires <= $now ) {
					unset( $blocks[ $hash ] );
					$changed = true;
				}
			}
			if ( $changed ) { update_option( self::OPTION_BOOKING_BLOCKS, $blocks, false ); }
			return $blocks;
		}

		private function get_booking_block_for_email( $email ) {
			$hash = $this->booking_block_hash( $email );
			if ( '' === $hash ) { return false; }
			$blocks = $this->get_active_booking_blocks();
			return isset( $blocks[ $hash ] ) && is_array( $blocks[ $hash ] ) ? $blocks[ $hash ] : false;
		}

		private function add_timed_booking_block( $email, $reason = 'manual_no_show' ) {
			$hash = $this->booking_block_hash( $email );
			if ( '' === $hash ) { return false; }
			$settings = $this->get_settings();
			$days = max( 1, min( 730, absint( isset( $settings['no_show_block_days'] ) ? $settings['no_show_block_days'] : 365 ) ) );
			$blocks = $this->get_active_booking_blocks();
			$blocks[ $hash ] = array(
				'created_at' => time(),
				'expires_at' => time() + ( $days * DAY_IN_SECONDS ),
				'reason'     => sanitize_key( $reason ),
			);
			update_option( self::OPTION_BOOKING_BLOCKS, $blocks, false );
			return $blocks[ $hash ];
		}

		private function remove_timed_booking_block( $email ) {
			$hash = $this->booking_block_hash( $email );
			if ( '' === $hash ) { return false; }
			$blocks = $this->get_active_booking_blocks();
			if ( isset( $blocks[ $hash ] ) ) {
				unset( $blocks[ $hash ] );
				update_option( self::OPTION_BOOKING_BLOCKS, $blocks, false );
			}
			return true;
		}

		private function remove_all_booking_blocks_for_email( $email ) {
			$this->remove_timed_booking_block( $email );
			$this->remove_legacy_blocked_email( $email );
		}

		private function get_recent_no_show_count( $email ) {
			global $wpdb;
			$email = strtolower( sanitize_email( $email ) );
			if ( '' === $email || ! $this->table_exists() ) { return 0; }
			$settings = $this->get_settings();
			$days = max( 1, min( 730, absint( isset( $settings['no_show_window_days'] ) ? $settings['no_show_window_days'] : 180 ) ) );
			$since = wp_date( 'Y-m-d', current_datetime()->getTimestamp() - ( $days * DAY_IN_SECONDS ) );
			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$this->table} WHERE customer_email = %s AND booking_status = %s AND booking_date >= %s",
					$email,
					self::STATUS_NO_SHOW,
					$since
				)
			);
		}

		private function email_requires_no_show_review( $email ) {
			$settings = $this->get_settings();
			if ( empty( $settings['no_show_safeguard_enabled'] ) ) { return false; }
			$threshold = max( 1, min( 10, absint( isset( $settings['no_show_threshold'] ) ? $settings['no_show_threshold'] : 2 ) ) );
			return $this->get_recent_no_show_count( $email ) >= $threshold;
		}

		private function get_combined_booking_privacy_notice( $settings = null ) {
			$settings = is_array( $settings ) ? $settings : $this->get_settings();
			return isset( $settings['booking_privacy_notice'] ) ? trim( (string) $settings['booking_privacy_notice'] ) : '';
		}

		/**
		 * Find all bookings for a given email address (for export / delete).
		 */
		/**
		 * Per-email rate limit check (added).
		 *
		 * Returns true if the supplied email has placed more bookings in the
		 * configured window than the configured maximum. Cancelled bookings
		 * still count - someone repeatedly booking and cancelling to lock
		 * the calendar is exactly the abuse this targets. Disabled when the
		 * rate_limit_per_email_enabled setting is off.
		 */
		private function email_rate_limit_exceeded( $email ) {
			global $wpdb;
			// Admins are never rate limited.
			if ( $this->current_request_is_admin() ) { return false; }
			if ( ! $this->table_exists() ) { return false; }
			$settings = $this->get_settings();
			if ( empty( $settings['rate_limit_per_email_enabled'] ) ) { return false; }
			$email = sanitize_email( $email );
			if ( '' === $email ) { return false; }
			// Lowercased to match get_recent_no_show_count() and the blocked-email
			// list. Harmless under the default case-insensitive collation, but it
			// keeps the three lookups consistent if that ever changes.
			$email = strtolower( $email );
			$max  = max( 1, absint( isset( $settings['rate_limit_per_email_max'] ) ? $settings['rate_limit_per_email_max'] : 3 ) );
			$days = max( 1, absint( isset( $settings['rate_limit_per_email_days'] ) ? $settings['rate_limit_per_email_days'] : 30 ) );
			$since = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $days . ' days', current_time( 'timestamp' ) ) );
			$count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$this->table} WHERE customer_email = %s AND created_at >= %s",
					$email,
					$since
				)
			);
			return $count >= $max;
		}

		private function get_bookings_for_email( $email ) {
			global $wpdb;
			$email = sanitize_email( $email );
			if ( ! $this->table_exists() || '' === $email ) {
				return array();
			}
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$this->table} WHERE customer_email = %s ORDER BY booking_date DESC, booking_time DESC",
					$email
				)
			);
		}

		/**
		 * Count bookings in a date range, used to preview the Content Research
		 * export size before the user clicks download.
		 */
		private function count_bookings_in_range( $from, $to ) {
			global $wpdb;
			if ( ! $this->table_exists() ) {
				return 0;
			}
			$from = sanitize_text_field( $from );
			$to   = sanitize_text_field( $to );
			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$this->table} WHERE booking_date >= %s AND booking_date <= %s",
					$from,
					$to
				)
			);
		}

		/**
		 * Fetch the bookings to include in the Content Research export.
		 * Returns only the content-relevant fields - no personal data.
		 */
		private function get_bookings_for_content_research( $from, $to ) {
			global $wpdb;
			if ( ! $this->table_exists() ) {
				return array();
			}
			$from = sanitize_text_field( $from );
			$to   = sanitize_text_field( $to );
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT booking_date, service_name, location_name, customer_interests, customer_notes, admin_notes
					FROM {$this->table}
					WHERE booking_date >= %s AND booking_date <= %s
					ORDER BY booking_date ASC, booking_time ASC",
					$from,
					$to
				)
			);
		}

		private function get_session_bookings_for_booking( $booking ) {
			global $wpdb;
			if ( ! $booking || empty( $booking->id ) || ! $this->table_exists() ) { return array(); }
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$this->table}
					WHERE location_id = %s AND service_id = %s AND staff_id = %s AND booking_date = %s AND booking_time = %s
					AND booking_status IN (%s, %s)
					ORDER BY customer_name ASC, id ASC",
					isset( $booking->location_id ) ? (string) $booking->location_id : '',
					isset( $booking->service_id ) ? (string) $booking->service_id : '',
					isset( $booking->staff_id ) ? (string) $booking->staff_id : '',
					isset( $booking->booking_date ) ? (string) $booking->booking_date : '',
					isset( $booking->booking_time ) ? (string) $booking->booking_time : '',
					self::STATUS_CONFIRMED,
					self::STATUS_HELD
				)
			);
		}


		private $booking_write_lock_name = '';

		/**
		 * Serialise booking availability checks and inserts. Booking traffic is
		 * low, so one short global lock is safer than allowing two simultaneous
		 * requests to claim the same slot between the check and INSERT.
		 */
		private function acquire_booking_write_lock() {
			global $wpdb;
			$name = 'rgl_booking_write_' . md5( DB_NAME . '|' . $wpdb->prefix );
			$got  = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', $name, 5 ) );
			if ( '1' !== (string) $got ) {
				return false;
			}
			$this->booking_write_lock_name = $name;
			return true;
		}

		private function release_booking_write_lock() {
			if ( '' === $this->booking_write_lock_name ) {
				return;
			}
			global $wpdb;
			$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $this->booking_write_lock_name ) );
			$this->booking_write_lock_name = '';
		}

		private function sanitize_booking_payload( $source ) {
			return array(
				'location_id'     => sanitize_key( wp_unslash( isset( $source['location_id'] ) ? $source['location_id'] : '' ) ),
				'service_id'      => sanitize_key( wp_unslash( isset( $source['service_id'] ) ? $source['service_id'] : '' ) ),
				'staff_id'        => sanitize_key( wp_unslash( isset( $source['staff_id'] ) ? $source['staff_id'] : '' ) ),
				'customer_name'   => sanitize_text_field( wp_unslash( isset( $source['customer_name'] ) ? $source['customer_name'] : '' ) ),
				'customer_email'  => sanitize_email( wp_unslash( isset( $source['customer_email'] ) ? $source['customer_email'] : '' ) ),
				'customer_phone'  => sanitize_text_field( wp_unslash( isset( $source['customer_phone'] ) ? $source['customer_phone'] : '' ) ),
				'customer_address'=> sanitize_textarea_field( wp_unslash( isset( $source['customer_address'] ) ? $source['customer_address'] : '' ) ),
				'customer_notes'  => sanitize_textarea_field( wp_unslash( isset( $source['customer_notes'] ) ? $source['customer_notes'] : '' ) ),
				'customer_interests' => sanitize_textarea_field( wp_unslash( isset( $source['customer_interests'] ) ? $source['customer_interests'] : '' ) ),
				'cancellation_reason' => sanitize_textarea_field( wp_unslash( isset( $source['cancellation_reason'] ) ? $source['cancellation_reason'] : '' ) ),
				'admin_notes'     => $this->sanitize_admin_notes_input( $source ),
				'booking_date'    => sanitize_text_field( wp_unslash( isset( $source['booking_date'] ) ? $source['booking_date'] : '' ) ),
				'booking_time'    => sanitize_text_field( wp_unslash( isset( $source['booking_time'] ) ? $source['booking_time'] : '' ) ),
				'booking_quantity'=> max( 1, absint( wp_unslash( isset( $source['booking_quantity'] ) ? $source['booking_quantity'] : 1 ) ) ),
				'booking_status'  => sanitize_text_field( wp_unslash( isset( $source['booking_status'] ) ? $source['booking_status'] : self::STATUS_CONFIRMED ) ),
			);
		}


		private function validate_unchanged_admin_booking_payload( $payload, $previous_booking ) {
			if ( ! $previous_booking || '' === $payload['customer_name'] || '' === $payload['customer_email'] || '' === $payload['customer_phone'] ) {
				return new WP_Error( 'missing_fields', 'Please complete all required booking fields.' );
			}
			if ( ! is_email( $payload['customer_email'] ) ) {
				return new WP_Error( 'bad_email', 'Please enter a valid email address.' );
			}
			$staff = '' !== (string) $previous_booking->staff_id
				? array( 'id' => (string) $previous_booking->staff_id, 'name' => (string) $previous_booking->staff_name )
				: false;
			return array(
				'location' => array( 'id' => (string) $previous_booking->location_id, 'name' => (string) $previous_booking->location_name ),
				'service'  => array( 'id' => (string) $previous_booking->service_id, 'name' => (string) $previous_booking->service_name ),
				'staff'    => $staff,
				'end_time' => substr( (string) $previous_booking->booking_end_time, 0, 5 ),
			);
		}

		private function validate_booking_payload( $payload, $exclude_booking_id = 0 ) {
			$payload = $this->resolve_booking_payload_selections( $payload );
			if ( ! $this->licence_booking_date_allowed( isset( $payload['booking_date'] ) ? $payload['booking_date'] : '' ) ) {
				return new WP_Error( 'licence_blocked', 'The booking system is not licensed to accept an appointment on that date.' );
			}
			if ( $this->licence_booking_date_restricted( isset( $payload['booking_date'] ) ? $payload['booking_date'] : '' ) ) {
				$payload['booking_quantity'] = 1;
			}
			if ( '' === $payload['location_id'] || '' === $payload['service_id'] || '' === $payload['customer_name'] || '' === $payload['customer_email'] || '' === $payload['customer_phone'] || '' === $payload['booking_date'] || '' === $payload['booking_time'] ) {
				return new WP_Error( 'missing_fields', 'Please complete all required booking fields.' );
			}
			if ( ! is_email( $payload['customer_email'] ) ) {
				return new WP_Error( 'bad_email', 'Please enter a valid email address.' );
			}
			if ( ! $this->is_valid_date( $payload['booking_date'] ) || ! $this->is_valid_time( $payload['booking_time'] ) ) {
				return new WP_Error( 'bad_datetime', 'Please enter a valid booking date and time.' );
			}
			$location = $this->find_location( $payload['location_id'] );
			$service  = $this->find_service( $payload['service_id'] );
			$staff    = '' !== $payload['staff_id'] ? $this->find_staff( $payload['staff_id'] ) : false;
			if ( ! $location ) {
				return new WP_Error( 'bad_location', 'Invalid location.' );
			}
			if ( ! $service ) {
				return new WP_Error( 'bad_service', 'Invalid service.' );
			}
			if ( ! $staff ) {
				$allowed_staff = $this->get_allowed_staff_for_service_location( $service, $payload['location_id'] );
				if ( 1 === count( $allowed_staff ) ) {
					$staff = $allowed_staff[0];
					$payload['staff_id'] = $staff['id'];
				}
			}
			if ( ! $this->service_matches_location( $service, $location['id'] ) ) {
				return new WP_Error( 'bad_service_location', 'That service is not linked to that location.' );
			}
			if ( $staff && ! $this->staff_can_deliver_service_at_location( $service, $staff, $location['id'] ) ) {
				return new WP_Error( 'bad_staff_service_location', 'That team member is not assigned to deliver this service at that location.' );
			}
			if ( ! $staff && ! empty( $service['staff_ids'] ) ) {
				return new WP_Error( 'staff_required', 'Please choose a team member for this service.' );
			}
			$available_slots = $this->build_slots( $service, $staff, $payload['booking_date'], $exclude_booking_id, $location );
			$selected_slot = null;
			foreach ( $available_slots as $slot ) {
				if ( isset( $slot['value'] ) && $payload['booking_time'] === $slot['value'] ) {
					$selected_slot = $slot;
					break;
				}
			}
			if ( ! $selected_slot ) {
				return new WP_Error( 'bad_slot', 'That booking time is not available for the selected location, service, and team member.' );
			}
			$spaces_remaining = max( 1, absint( isset( $selected_slot['spaces_remaining'] ) ? $selected_slot['spaces_remaining'] : 1 ) );
			if ( $payload['booking_quantity'] > $spaces_remaining ) {
				return new WP_Error( 'not_enough_spaces', sprintf( 'Only %d space%s remain for that time.', $spaces_remaining, 1 === $spaces_remaining ? '' : 's' ) );
			}
			$rules = $this->resolve_effective_rules( $service, $staff );
			$start_minutes = $this->time_to_minutes( $payload['booking_time'] );
			$end_minutes   = $start_minutes + absint( $rules['duration'] );
			$end_time      = $this->minutes_to_time( $end_minutes );
			return array(
				'location' => $location,
				'service'  => $service,
				'staff'    => $staff,
				'end_time' => $end_time,
			);
		}

		private function notification_toggle_key( $recipient, $event ) {
			return 'notify_' . $recipient . '_' . $event;
		}


		private function notification_enabled( $recipient, $event ) {
			$settings = $this->get_settings();
			if ( 'customer' === $recipient && 'booking_reminder' === $event ) {
				return ! empty( $settings['reminder_enabled'] );
			}
			// The spam-cancel email is the whole point of the Mark as Spam
			// admin action - it doesn't make sense to toggle it off, and a
			// missing toggle would silently break the action.
			if ( 'customer' === $recipient && 'booking_marked_spam' === $event ) {
				return true;
			}
			$key      = $this->notification_toggle_key( $recipient, $event );
			return ! empty( $settings[ $key ] );
		}

		private function get_booking_staff_email( $booking ) {
			if ( ! $booking || empty( $booking->staff_id ) ) {
				return '';
			}
			$staff = $this->find_staff( $booking->staff_id );
			if ( ! $staff || empty( $staff['email'] ) || ! is_email( $staff['email'] ) ) {
				return '';
			}
			return $staff['email'];
		}

		private function get_staff_email_by_id_including_inactive( $staff_id ) {
			$staff_id = sanitize_key( $staff_id );
			if ( '' === $staff_id ) {
				return '';
			}
			foreach ( $this->get_all_staff() as $staff ) {
				if ( empty( $staff['id'] ) || sanitize_key( $staff['id'] ) !== $staff_id ) {
					continue;
				}
				$email = isset( $staff['email'] ) ? sanitize_email( $staff['email'] ) : '';
				return is_email( $email ) ? $email : '';
			}
			return '';
		}

		private function get_booking_notification_recipient_email( $recipient, $booking ) {
			if ( 'admin' === $recipient ) {
				$email = get_option( 'admin_email' );
				return is_email( $email ) ? $email : '';
			}
			if ( 'staff' === $recipient ) {
				return $this->get_booking_staff_email( $booking );
			}
			if ( 'customer' === $recipient && ! empty( $booking->customer_email ) && is_email( $booking->customer_email ) ) {
				return $booking->customer_email;
			}
			return '';
		}

		private function get_booking_event_label( $event, $recipient = '' ) {
			$labels = array(
				'booking_made'          => 'Booking made',
				'booking_pending_approval' => 'Booking awaiting approval',
				'booking_approved'      => 'Booking approved',
				'booking_edited'        => 'Booking edited',
				'booking_cancelled'     => 'Booking cancelled',
				'booking_marked_spam'   => 'Booking cancelled',
				'booking_reminder'       => 'Appointment reminder',
				'booking_rescheduled'    => 'Appointment changed',
				'consultant_changed'     => 'Consultant changed',
			);
			if ( 'customer' === $recipient && 'booking_reminder' === $event ) {
				return 'Reminder: your consultation is coming up';
			}
			if ( 'customer' === $recipient && 'booking_rescheduled' === $event ) {
				return 'Your appointment has been changed';
			}
			if ( 'customer' === $recipient && 'consultant_changed' === $event ) {
				return 'Change to your consultation';
			}
			if ( 'staff_new' === $recipient && 'consultant_changed' === $event ) {
				return 'Consultation reassigned to you';
			}
			if ( 'staff_previous' === $recipient && 'consultant_changed' === $event ) {
				return 'Consultation reassigned';
			}
			if ( 'customer' === $recipient && 'booking_pending_approval' === $event ) {
				return 'Booking received';
			}
			if ( 'customer' === $recipient && in_array( $event, array( 'booking_made', 'booking_approved' ), true ) ) {
				return 'Your booking is confirmed';
			}
			if ( 'customer' === $recipient && 'booking_cancelled' === $event ) {
				return 'Your booking has been cancelled';
			}
			if ( 'customer' === $recipient && 'booking_marked_spam' === $event ) {
				return 'Your booking has been cancelled';
			}
			if ( 'customer' === $recipient && 'booking_edited' === $event ) {
				return 'Your booking has been updated';
			}
			return isset( $labels[ $event ] ) ? $labels[ $event ] : 'Booking update';
		}

		private function build_booking_notification_subject( $event, $recipient, $booking ) {
			$site  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
			$label = $this->get_booking_event_label( $event, $recipient );
			$ref   = ! empty( $booking->booking_reference ) ? ' - ' . $booking->booking_reference : '';
			return sprintf( '%s: %s%s', $site, $label, $ref );
		}

		/**
		 * Build the notification as structured data (added).
		 *
		 * Returning structured data rather than a pre-formatted string lets the
		 * same booking be rendered as branded HTML and as a plain-text fallback
		 * without duplicating the field logic.
		 */
		private function build_booking_notification_data( $event, $recipient, $booking ) {
			$label = $this->get_booking_event_label( $event, $recipient );
			$hello = 'Hello,';
			if ( 'customer' === $recipient && ! empty( $booking->customer_name ) ) {
				$hello = 'Hello ' . $booking->customer_name . ',';
			} elseif ( 'staff_new' === $recipient && ! empty( $booking->_new_staff_name ) ) {
				$hello = 'Hello ' . $booking->_new_staff_name . ',';
			} elseif ( 'staff_previous' === $recipient && ! empty( $booking->_previous_staff_name ) ) {
				$hello = 'Hello ' . $booking->_previous_staff_name . ',';
			}

			$display_labels = $this->get_booking_display_labels();
			$display_date   = $this->format_booking_display_date( $booking );
			$display_time   = $this->format_booking_display_time( $booking );

			$data = array(
				'hello'    => $hello,
				'headline' => $label . '.',
				'intro'    => '',
				'rows'     => array(),
				'sections' => array(),
				'buttons'  => array(),
				'footer'   => ( 'customer' === $recipient )
					? 'We look forward to speaking with you. If anything changes in the meantime, just reply to this email and we will help.'
					: 'Please contact us if you have any questions.',
			);

			// Friendly intro line for the customer, using the communication method verb.
			if ( 'customer' === $recipient && in_array( $event, array( 'booking_made', 'booking_approved' ), true ) ) {
				$verb = $this->get_comm_method_verb( isset( $booking->location_id ) ? $booking->location_id : '', 'will be in touch' );
				$who  = ! empty( $booking->staff_name ) ? $booking->staff_name : 'We';
				$topic = ! empty( $booking->service_name ) ? $booking->service_name : '';
				$data['intro'] = sprintf(
					'%s %s on %s at %s%s. Thank you for booking with us.',
					esc_html( $who ),
					esc_html( $verb ),
					esc_html( $display_date ),
					esc_html( $display_time ),
					'' !== $topic ? esc_html( ' to discuss ' . strtolower( $topic ) ) : ''
				);
			} elseif ( 'customer' === $recipient && 'booking_reminder' === $event ) {
				$verb = $this->get_comm_method_verb( isset( $booking->location_id ) ? $booking->location_id : '', 'will be in touch' );
				$who  = ! empty( $booking->staff_name ) ? $booking->staff_name : 'We';
				$data['intro'] = sprintf(
					'This is a reminder that %s %s on %s at %s. If you can no longer attend, please let us know as soon as possible so the time can be made available to someone else.',
					esc_html( $who ),
					esc_html( $verb ),
					esc_html( $display_date ),
					esc_html( $display_time )
				);
				$data['footer'] = 'We look forward to speaking with you.';
			} elseif ( 'customer' === $recipient && 'booking_rescheduled' === $event ) {
				$data['intro'] = sprintf(
					'Your appointment has been changed to %s at %s. The updated details are shown below.',
					esc_html( $display_date ),
					esc_html( $display_time )
				);
			} elseif ( 'customer' === $recipient && 'consultant_changed' === $event ) {
				$verb = $this->get_comm_method_verb( isset( $booking->location_id ) ? $booking->location_id : '', 'will be in touch' );
				$who  = ! empty( $booking->staff_name ) ? $booking->staff_name : 'Another member of our team';
				$data['intro'] = sprintf(
					'There has been a change to the person handling your consultation. %s %s on %s at %s. The current appointment details are shown below.',
					esc_html( $who ),
					esc_html( $verb ),
					esc_html( $display_date ),
					esc_html( $display_time )
				);
			} elseif ( 'staff_new' === $recipient && 'consultant_changed' === $event ) {
				$previous_name = ! empty( $booking->_previous_staff_name ) ? $booking->_previous_staff_name : 'the previous consultant';
				$data['intro'] = sprintf( 'This consultation has been reassigned to you from %s. The full booking details are below.', esc_html( $previous_name ) );
			} elseif ( 'staff_previous' === $recipient && 'consultant_changed' === $event ) {
				$new_name = ! empty( $booking->_new_staff_name ) ? $booking->_new_staff_name : 'another team member';
				$data['intro'] = sprintf( 'This consultation has been reassigned from you to %s. No action is needed, but please remove it from your calendar if you had added it.', esc_html( $new_name ) );
				$data['footer'] = '';
			} elseif ( 'customer' === $recipient && 'booking_marked_spam' === $event ) {
				$data['intro'] = 'Thank you for reaching out, however we have had to cancel this booking. Our consultation service is for customers with pre-purchase questions about our rings, not a channel for supplier or sales enquiries. If you are a customer and believe this has been cancelled in error, please reply to this email and we will help.';
				$data['footer'] = '';
			} elseif ( 'customer' === $recipient && 'booking_pending_approval' === $event ) {
				$data['intro'] = sprintf(
					'Thank you for your booking. We will review it shortly and email you again once it is confirmed. The slot is held for you in the meantime. Reference: %s.',
					esc_html( isset( $booking->booking_reference ) ? $booking->booking_reference : '' )
				);
			} elseif ( 'admin' === $recipient && 'booking_pending_approval' === $event ) {
				$recent_no_shows = ! empty( $booking->customer_email ) ? $this->get_recent_no_show_count( $booking->customer_email ) : 0;
				$review_settings = $this->get_settings();
				$review_threshold = max( 1, absint( isset( $review_settings['no_show_threshold'] ) ? $review_settings['no_show_threshold'] : 2 ) );
				$review_days = max( 1, absint( isset( $review_settings['no_show_window_days'] ) ? $review_settings['no_show_window_days'] : 180 ) );
				if ( ! empty( $review_settings['no_show_safeguard_enabled'] ) && $recent_no_shows >= $review_threshold ) {
					$data['intro'] = sprintf( 'This booking is waiting for human review because the email address has %d recorded no-show%s within the last %d days. Review the circumstances before approving or rejecting it.', $recent_no_shows, 1 === $recent_no_shows ? '' : 's', $review_days );
				} else {
					$data['intro'] = 'This booking is waiting for approval. Use the link below to approve or reject it.';
				}
			} elseif ( 'staff' === $recipient && 'booking_pending_approval' === $event ) {
				$data['intro'] = 'A new booking is waiting for admin approval. You will be notified once it is approved.';
			}

			// Detail rows. For the spam-rejection email to the customer, skip
			// these entirely - we don't want to echo back what the spammer
			// entered, and we don't want them to think the booking has any
			// legitimacy.
			$skip_rows_for_spam = ( 'customer' === $recipient && 'booking_marked_spam' === $event );
			$compact_customer_email = ( 'customer' === $recipient && in_array( $event, array( 'booking_reminder', 'consultant_changed', 'booking_rescheduled' ), true ) );
			if ( ! $skip_rows_for_spam ) {
				$data['rows']['Reference'] = isset( $booking->booking_reference ) ? $booking->booking_reference : '';
				if ( ! empty( $booking->location_name ) ) {
					$data['rows'][ $display_labels['location'] ] = $booking->location_name;
				}
				if ( 'customer' === $recipient ) {
					$contact_note = $this->get_comm_method_customer_note( isset( $booking->location_id ) ? $booking->location_id : '' );
					if ( '' !== $contact_note ) { $data['rows']['Contact details'] = $contact_note; }
				}
				$data['rows'][ $display_labels['service'] ] = isset( $booking->service_name ) ? $booking->service_name : '';
				if ( ! empty( $booking->staff_name ) ) {
					$data['rows'][ $display_labels['staff'] ] = $booking->staff_name;
				}
				if ( 'consultant_changed' === $event && 'customer' !== $recipient && ! empty( $booking->_previous_staff_name ) ) {
					$data['rows']['Previous ' . $display_labels['staff']] = $booking->_previous_staff_name;
				}
				$data['rows']['Date'] = $display_date;
				$data['rows']['Time'] = $display_time . ' (UK time)';
			}

			// Customer contact details - only shown to admin/staff, not echoed back to the customer.
			if ( in_array( $recipient, array( 'admin', 'staff', 'staff_new' ), true ) ) {
				$data['rows']['Customer'] = isset( $booking->customer_name ) ? $booking->customer_name : '';
				$data['rows']['Email']    = isset( $booking->customer_email ) ? $booking->customer_email : '';
				if ( ! empty( $booking->customer_phone ) ) {
					$data['rows']['Phone'] = $booking->customer_phone;
				}
			} elseif ( 'staff_previous' === $recipient ) {
				$data['rows']['Customer'] = isset( $booking->customer_name ) ? $booking->customer_name : '';
			}

			if ( ! $compact_customer_email && 'staff_previous' !== $recipient && ! empty( $booking->customer_interests ) ) {
				$data['rows']['Topics of interest'] = $booking->customer_interests;
			}
			if ( in_array( $recipient, array( 'admin', 'staff', 'staff_new' ), true ) && ! empty( $booking->cancellation_reason ) ) {
				$data['rows']['Cancellation reason'] = $booking->cancellation_reason;
			}

			// Customer's own message (what they told us at booking) - shown to everyone.
			if ( ! $compact_customer_email && 'staff_previous' !== $recipient && ! empty( $booking->customer_notes ) ) {
				$message_label = ( 'customer' === $recipient ) ? 'Your message' : 'Customer message';
				$data['sections'][ $message_label ] = trim( wp_strip_all_tags( $booking->customer_notes ) );
			}

			// Post-conversation notes - admin/staff only, never shown to the customer.
			if ( in_array( $recipient, array( 'admin', 'staff', 'staff_new' ), true ) && ! empty( $booking->admin_notes ) ) {
				$notes_for_email = trim( wp_strip_all_tags( $booking->admin_notes ) );
				if ( ! ( is_numeric( $notes_for_email ) && 0.0 === (float) $notes_for_email ) && '' !== $notes_for_email ) {
					$data['sections']['Conversation notes'] = $notes_for_email;
				}
			}

			// Additional notes section.
			$notes = $this->get_effective_booking_summary_notes( $booking );
			if ( ! $compact_customer_email && 'staff_previous' !== $recipient && '' !== trim( wp_strip_all_tags( $notes ) ) ) {
				$data['sections']['Additional notes'] = trim( wp_strip_all_tags( $notes ) );
			}

			// Buttons / links. Reminders and consultant-change emails put the
			// cancellation action first and make it visually prominent.
			$is_priority_cancel_event = ( 'customer' === $recipient && in_array( $event, array( 'booking_reminder', 'consultant_changed', 'booking_rescheduled' ), true ) );
			$has_priority_cancel_button = false;
			$has_priority_reschedule_button = false;
			if ( $is_priority_cancel_event && isset( $booking->booking_status ) && self::STATUS_CONFIRMED === $booking->booking_status ) {
				$reschedule_reason = '';
				if ( $this->booking_is_self_reschedulable( $booking, $reschedule_reason ) ) {
					$reschedule_url = $this->get_reschedule_url( $booking );
					if ( '' !== $reschedule_url ) {
						$data['buttons'][] = array( 'label' => 'Change appointment', 'url' => $reschedule_url, 'primary' => true );
						$has_priority_reschedule_button = true;
					}
				}
			}
			if ( $is_priority_cancel_event && isset( $booking->booking_status ) && self::STATUS_CONFIRMED === $booking->booking_status ) {
				$cancel_reason = '';
				if ( $this->booking_is_self_cancellable( $booking, $cancel_reason ) ) {
					$cancel_url = $this->get_cancellation_url( $booking );
					if ( '' !== $cancel_url ) {
						$data['buttons'][] = array( 'label' => 'Cancel booking', 'url' => $cancel_url, 'primary' => ! $has_priority_reschedule_button );
					$has_priority_cancel_button = true;
					}
				} elseif ( '' !== $cancel_reason ) {
					$data['cancel_note'] = $cancel_reason;
				}
			}

			$summary_url = $this->get_booking_summary_url( $booking );
			if ( 'staff_previous' !== $recipient && '' !== $summary_url ) {
				$data['buttons'][] = array( 'label' => 'View booking summary', 'url' => $summary_url, 'primary' => ! $has_priority_cancel_button && ! $has_priority_reschedule_button );
			}

			// Calendar buttons only for ordinary confirmed/edited emails. A reminder
			// or consultant-only change does not require another calendar import.
			$is_pending = ( 'booking_pending_approval' === $event ) || ( isset( $booking->booking_status ) && self::STATUS_PENDING_APPROVAL === $booking->booking_status );
			$is_terminal = in_array( $event, array( 'booking_cancelled', 'booking_marked_spam' ), true );
			$skip_calendar = ( 'booking_reminder' === $event ) || ( 'consultant_changed' === $event && in_array( $recipient, array( 'customer', 'staff_previous' ), true ) );
			if ( ! $is_pending && ! $is_terminal && ! $skip_calendar ) {
				$google_url = $this->get_google_calendar_url( $booking );
				if ( '' !== $google_url ) {
					$data['buttons'][] = array( 'label' => 'Add to Google Calendar', 'url' => $google_url, 'primary' => false );
				}
				$ics_url = $this->get_icalendar_data_url( $booking );
				if ( '' !== $ics_url ) {
					$data['buttons'][] = array( 'label' => 'Add to iCalendar', 'url' => $ics_url, 'primary' => false );
				}
			}

			// Approve / reject button for admin on pending bookings.
			if ( 'admin' === $recipient && 'booking_pending_approval' === $event ) {
				$approve_url = $this->get_approval_action_url( $booking, 'approve' );
				$reject_url  = $this->get_approval_action_url( $booking, 'reject' );
				if ( '' !== $approve_url ) {
					$data['buttons'][] = array( 'label' => 'Approve booking', 'url' => $approve_url, 'primary' => true );
				}
				if ( '' !== $reject_url ) {
					$data['buttons'][] = array( 'label' => 'Reject booking', 'url' => $reject_url, 'primary' => false );
				}
			}

			// Ordinary confirmation emails include secure change and cancellation links.
			if ( 'customer' === $recipient && in_array( $event, array( 'booking_made', 'booking_approved' ), true ) && isset( $booking->booking_status ) && self::STATUS_CONFIRMED === $booking->booking_status ) {
				$reschedule_reason = '';
				if ( $this->booking_is_self_reschedulable( $booking, $reschedule_reason ) ) {
					$reschedule_url = $this->get_reschedule_url( $booking );
					if ( '' !== $reschedule_url ) {
						$data['buttons'][] = array( 'label' => 'Change appointment', 'url' => $reschedule_url, 'primary' => false );
					}
				}
				$cancel_settings = $this->get_settings();
				if ( ! empty( $cancel_settings['cancellation_enabled'] ) ) {
					$cancel_url = $this->get_cancellation_url( $booking );
					if ( '' !== $cancel_url ) {
						$note = 'Use the Cancel booking button above if you can no longer attend.';
						$min_hours = max( 0, absint( isset( $cancel_settings['cancellation_min_hours_notice'] ) ? $cancel_settings['cancellation_min_hours_notice'] : 0 ) );
						if ( $min_hours > 0 ) {
							$note .= ' Online cancellation closes ' . $min_hours . ' hour' . ( 1 === $min_hours ? '' : 's' ) . ' before the appointment.';
						}
						$data['cancel_note'] = $note;
						$data['buttons'][] = array( 'label' => 'Cancel booking', 'url' => $cancel_url, 'primary' => false );
					}
				}
			}

			// Keep customer email actions in a predictable order on every event.
			// Unknown future actions remain before the change/cancel pair rather than
			// being allowed to push the cancellation action away from the end.
			if ( 'customer' === $recipient && ! empty( $data['buttons'] ) ) {
				$button_order = array(
					'View booking summary'   => 10,
					'Add to Google Calendar' => 20,
					'Add to iCalendar'       => 30,
					'Change appointment'     => 90,
					'Cancel booking'         => 100,
				);
				$indexed_buttons = array();
				foreach ( $data['buttons'] as $button_index => $button ) {
					$label = isset( $button['label'] ) ? (string) $button['label'] : '';
					$indexed_buttons[] = array(
						'button' => $button,
						'order'  => isset( $button_order[ $label ] ) ? $button_order[ $label ] : 50,
						'index'  => (int) $button_index,
					);
				}
				usort(
					$indexed_buttons,
					static function ( $left, $right ) {
						if ( $left['order'] === $right['order'] ) {
							return $left['index'] <=> $right['index'];
						}
						return $left['order'] <=> $right['order'];
					}
				);
				$data['buttons'] = array_column( $indexed_buttons, 'button' );
			}

			// Privacy reassurance - customer emails only.
			if ( 'customer' === $recipient && ! $compact_customer_email ) {
				$privacy_settings = $this->get_settings();
				$privacy_text     = $this->get_combined_booking_privacy_notice( $privacy_settings );
				if ( '' !== $privacy_text ) {
					$data['privacy_note'] = $privacy_text;
				}
			}

			return $data;
		}

		/**
		 * Render notification data as a plain-text body (fallback / legacy).
		 */
		private function build_booking_notification_message( $event, $recipient, $booking ) {
			$data = $this->build_booking_notification_data( $event, $recipient, $booking );

			$lines   = array();
			$lines[] = $data['hello'];
			$lines[] = '';
			$lines[] = $data['headline'];
			if ( '' !== $data['intro'] ) {
				$lines[] = '';
				$lines[] = wp_strip_all_tags( $data['intro'] );
			}
			$lines[] = '';
			foreach ( $data['rows'] as $key => $value ) {
				if ( '' === (string) $value ) { continue; }
				$lines[] = $key . ': ' . $value;
			}
			foreach ( $data['sections'] as $title => $body ) {
				$lines[] = '';
				$lines[] = $title . ':';
				$lines[] = $body;
			}
			if ( ! empty( $data['buttons'] ) ) {
				$lines[] = '';
				foreach ( $data['buttons'] as $btn ) {
					$lines[] = $btn['label'] . ': ' . $btn['url'];
				}
			}
			if ( ! empty( $data['cancel_note'] ) ) {
				$lines[] = '';
				$lines[] = $data['cancel_note'];
				if ( ! empty( $data['cancel_url'] ) ) {
					$lines[] = $data['cancel_url'];
				}
			}
			if ( ! empty( $data['privacy_note'] ) ) {
				$lines[] = '';
				$lines[] = $data['privacy_note'];
			}
			$lines[] = '';
			$lines[] = $data['footer'];

			return implode( "\n", $lines );
		}

		/**
		 * Render notification data as branded HTML (added).
		 */
		private function render_booking_email_html( $event, $recipient, $booking, $custom_intro = '' ) {
			$data = $this->build_booking_notification_data( $event, $recipient, $booking );
			$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

			// An admin-typed custom message (from the "Email Booking" action)
			// replaces the standard intro line when supplied.
			if ( '' !== trim( (string) $custom_intro ) ) {
				$data['intro'] = nl2br( esc_html( trim( wp_strip_all_tags( (string) $custom_intro ) ) ) );
			}

			// Booking details use a true single-column structure. Email clients can
			// ignore media queries and force table cells to remain side by side, so
			// each label and value live in one cell and stack reliably everywhere.
			$rows_html = '';
			$visible_rows = array();
			foreach ( $data['rows'] as $key => $value ) {
				if ( '' === (string) $value ) { continue; }
				$visible_rows[ $key ] = $value;
			}
			$row_index = 0;
			$row_count = count( $visible_rows );
			foreach ( $visible_rows as $key => $value ) {
				$row_index++;
				$border = $row_index < $row_count ? 'border-bottom:1px solid #ece3d2;' : '';
				$rows_html .= '<tr>'
					. '<td style="padding:12px 0;' . $border . 'vertical-align:top;">'
					. '<div style="margin:0 0 4px;color:#6b6257;font-size:13px;line-height:1.35;font-weight:600;">' . esc_html( $key ) . '</div>'
					. '<div style="margin:0;color:#2b2b2b;font-size:15px;line-height:1.45;font-weight:600;word-break:normal;overflow-wrap:anywhere;">' . esc_html( $value ) . '</div>'
					. '</td>'
					. '</tr>';
			}

			// Sections.
			$sections_html = '';
			foreach ( $data['sections'] as $title => $body ) {
				// nl2br on the linkified (already-escaped) text so line breaks survive.
				$sections_html .= '<p style="margin:18px 0 4px;font-size:14px;font-weight:700;color:#2b2b2b;">' . esc_html( $title ) . '</p>'
					. '<p style="margin:0;font-size:14px;color:#2b2b2b;line-height:1.55;">' . nl2br( $this->linkify_text( $body, '#2b2b2b' ) ) . '</p>';
			}

			// Buttons. A table row per action is more reliable than wrapping
			// inline links across Gmail, Apple Mail and Outlook, and stays tidy on
			// narrow mobile screens.
			$buttons_html = '';
			if ( ! empty( $data['buttons'] ) ) {
				$buttons_html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;max-width:360px;margin:18px 0 4px;">';
				foreach ( $data['buttons'] as $btn ) {
					$is_primary = ! empty( $btn['primary'] );
					$bg     = $is_primary ? '#2b2b2b' : '#ffffff';
					$colour = $is_primary ? '#ffffff' : '#2b2b2b';
					$border = $is_primary ? '#2b2b2b' : '#c9bfae';
					$buttons_html .= '<tr><td style="padding:0 0 10px;">'
						. '<a href="' . esc_url( $btn['url'] ) . '" style="display:block;width:100%;box-sizing:border-box;padding:12px 18px;background:' . $bg . ';color:' . $colour . ';border:1px solid ' . $border . ';border-radius:6px;text-align:center;text-decoration:none;font-size:14px;font-weight:600;line-height:1.3;">' . esc_html( $btn['label'] ) . '</a>'
						. '</td></tr>';
				}
				$buttons_html .= '</table>';
			}

			// Cancellation note (subtle text link, not a button).
			$cancel_html = '';
			if ( ! empty( $data['cancel_note'] ) ) {
				$cancel_html = '<p style="margin:20px 0 0;font-size:13px;color:#6b6257;line-height:1.55;">' . esc_html( $data['cancel_note'] );
				if ( ! empty( $data['cancel_url'] ) ) {
					$cancel_html .= ' <a href="' . esc_url( $data['cancel_url'] ) . '" style="color:#6b6257;text-decoration:underline;">Cancel this booking</a>.';
				}
				$cancel_html .= '</p>';
			}

			$intro_html = '' !== $data['intro'] ? '<p style="margin:0 0 16px;font-size:15px;color:#2b2b2b;line-height:1.6;">' . $data['intro'] . '</p>' : '';

			// Privacy reassurance block (customer emails only).
			$privacy_html = '';
			if ( ! empty( $data['privacy_note'] ) ) {
				$privacy_paragraphs = preg_split( '/\n\s*\n/', $data['privacy_note'] );
				$privacy_html_inner = '';
				foreach ( $privacy_paragraphs as $idx => $privacy_para ) {
					$margin = ( 0 === $idx ) ? '0' : '10px 0 0';
					$privacy_html_inner .= '<p style="margin:' . $margin . ';font-size:12px;color:#6b6257;line-height:1.6;">' . nl2br( esc_html( trim( $privacy_para ) ) ) . '</p>';
				}
				$privacy_html = '<div style="margin:18px 0 0;background:#faf6ed;border:1px solid #ece3d2;border-radius:8px;padding:10px 14px;">' . $privacy_html_inner . '</div>';
			}

			// Footer text comes from Settings - free-text, one detail per line.
			// Each line is escaped, then anything that looks like a URL or email
			// address is turned into a clickable link. No HTML is accepted from
			// the setting itself, so the footer cannot break the email layout.
			$footer_settings = $this->get_settings();
			$footer_raw      = isset( $footer_settings['email_footer_text'] ) ? (string) $footer_settings['email_footer_text'] : '';
			$footer_lines    = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $footer_raw ) ), 'strlen' );
			$footer_html     = '';
			foreach ( $footer_lines as $fline ) {
				$footer_html .= ( '' === $footer_html ? '' : '<br>' ) . $this->linkify_text( $fline, '#8a8072' );
			}

			$html = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
				. '<body style="margin:0;padding:0;background:#f3ede1;">'
				. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3ede1;padding:24px 12px;">'
				. '<tr><td align="center">'
				. '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fffdf8;border:1px solid #e6dcc8;border-radius:12px;overflow:hidden;">'
				// Header
				. '<tr><td class="rgl-email-header" style="padding:26px 32px 18px;border-bottom:1px solid #ece3d2;">'
				. '<span style="font-family:Georgia,\'Times New Roman\',serif;font-size:22px;letter-spacing:.5px;color:#2b2b2b;font-weight:700;">' . esc_html( $site ) . '</span>'
				. '</td></tr>'
				// Body
				. '<tr><td class="rgl-email-body" style="padding:26px 32px 8px;font-family:Arial,Helvetica,sans-serif;">'
				. '<p style="margin:0 0 14px;font-size:15px;color:#2b2b2b;">' . esc_html( $data['hello'] ) . '</p>'
				. '<p style="margin:0 0 16px;font-size:16px;font-weight:700;color:#2b2b2b;">' . esc_html( $data['headline'] ) . '</p>'
				. $intro_html
				. '<table class="rgl-email-details" role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;background:#faf6ed;border:1px solid #ece3d2;border-radius:8px;padding:4px 16px;margin:4px 0 8px;">'
				. $rows_html
				. '</table>'
				. $sections_html
				. $buttons_html
				. $cancel_html
				. $privacy_html
				. '<p style="margin:22px 0 0;font-size:14px;color:#2b2b2b;">' . esc_html( $data['footer'] ) . '</p>'
				. '</td></tr>'
				// Footer
				. ( '' !== $footer_html
					? '<tr><td class="rgl-email-footer" style="padding:18px 32px 24px;border-top:1px solid #ece3d2;font-family:Arial,Helvetica,sans-serif;">'
						. '<p style="margin:0;font-size:12px;color:#8a8072;line-height:1.7;text-align:center;">' . $footer_html . '</p>'
						. '</td></tr>'
					: '' )
				. '</table>'
				. '</td></tr></table>'
				. '</body></html>';

			return $html;
		}

		private function send_booking_notification( $event, $recipient, $booking, $force = false ) {
			if ( ! $booking || ( ! $force && ! $this->notification_enabled( $recipient, $event ) ) ) {
				return false;
			}
			$to = $this->get_booking_notification_recipient_email( $recipient, $booking );
			if ( '' === $to ) {
				return false;
			}
			$subject = $this->build_booking_notification_subject( $event, $recipient, $booking );
			$message = $this->render_booking_email_html( $event, $recipient, $booking );
			$audience = ( 'customer' === $recipient ) ? 'customer' : 'internal';
			$sent = $this->send_mail( $to, $subject, $message, array(), true, $audience );
			$this->add_mail_log_entry(
				$sent ? 'Booking email sent.' : 'Booking email failed.',
				array(
					'booking_id' => isset( $booking->id ) ? (int) $booking->id : 0,
					'event'      => $event,
					'recipient'  => $recipient,
					'to'         => $to,
				)
			);
			return $sent;
		}

		public function send_queued_booking_notification( $event, $recipient, $booking_id ) {
			$booking = $this->get_booking_by_id( absint( $booking_id ) );
			if ( ! $booking ) {
				$this->add_mail_log_entry( 'Queued booking email skipped: booking not found.', array( 'booking_id' => $booking_id, 'event' => $event, 'recipient' => $recipient ) );
				return false;
			}
			return $this->send_booking_notification( sanitize_key( $event ), sanitize_key( $recipient ), $booking );
		}

		private function queue_booking_notification( $event, $recipient, $booking, $delay = 10 ) {
			if ( ! $booking || empty( $booking->id ) || ! $this->notification_enabled( $recipient, $event ) ) { return false; }
			$to = $this->get_booking_notification_recipient_email( $recipient, $booking );
			if ( '' === $to ) { return false; }

			$args = array( sanitize_key( $event ), sanitize_key( $recipient ), absint( $booking->id ) );
			if ( wp_next_scheduled( 'rgl_booking_send_notification_event', $args ) ) { return true; }

			$scheduled = wp_schedule_single_event( time() + max( 1, absint( $delay ) ), 'rgl_booking_send_notification_event', $args );
			if ( false === $scheduled ) {
				$this->add_mail_log_entry( 'Email queue failed; sending immediately as fallback.', array( 'booking_id' => $booking->id, 'event' => $event, 'recipient' => $recipient ) );
				return $this->send_booking_notification( $event, $recipient, $booking );
			}
			$this->add_mail_log_entry( 'Booking email queued.', array( 'booking_id' => $booking->id, 'event' => $event, 'recipient' => $recipient, 'to' => $to ) );
			return true;
		}

		public function send_queued_consultant_change_notification( $audience, $booking_id, $previous_staff_email, $previous_staff_name, $new_staff_name, $expected_new_staff_id ) {
			$booking = $this->get_booking_by_id( absint( $booking_id ) );
			if ( ! $booking || self::STATUS_CONFIRMED !== (string) $booking->booking_status ) {
				return false;
			}
			if ( '' !== sanitize_key( $expected_new_staff_id ) && sanitize_key( $booking->staff_id ) !== sanitize_key( $expected_new_staff_id ) ) {
				return false; // Booking was reassigned again before this queued email ran.
			}

			$booking->_previous_staff_name = sanitize_text_field( $previous_staff_name );
			$booking->_new_staff_name      = sanitize_text_field( $new_staff_name );
			$audience = sanitize_key( $audience );
			$recipient = '';
			$to = '';

			if ( 'customer' === $audience && $this->notification_enabled( 'customer', 'consultant_changed' ) ) {
				$recipient = 'customer';
				$to = ! empty( $booking->customer_email ) ? sanitize_email( $booking->customer_email ) : '';
			} elseif ( 'new_staff' === $audience && $this->notification_enabled( 'staff', 'consultant_changed' ) ) {
				$recipient = 'staff_new';
				$to = $this->get_booking_staff_email( $booking );
			} elseif ( 'previous_staff' === $audience && $this->notification_enabled( 'staff', 'consultant_changed' ) ) {
				$recipient = 'staff_previous';
				$to = sanitize_email( $previous_staff_email );
			}
			if ( '' === $recipient || ! is_email( $to ) ) {
				return false;
			}

			$subject = $this->build_booking_notification_subject( 'consultant_changed', $recipient, $booking );
			$message = $this->render_booking_email_html( 'consultant_changed', $recipient, $booking );
			$email_audience = ( 'customer' === $audience ) ? 'customer' : 'internal';
			$sent = $this->send_mail( $to, $subject, $message, array(), true, $email_audience );
			$this->add_mail_log_entry(
				$sent ? 'Consultant-change email sent.' : 'Consultant-change email failed.',
				array( 'booking_id' => (int) $booking->id, 'audience' => $audience, 'to' => $to )
			);
			return $sent;
		}

		private function queue_consultant_change_notifications( $previous_booking, $updated_booking, $audiences = array( 'customer', 'new_staff', 'previous_staff' ) ) {
			if ( ! $previous_booking || ! $updated_booking || empty( $updated_booking->id ) ) {
				return;
			}
			$previous_staff_email = $this->get_staff_email_by_id_including_inactive( isset( $previous_booking->staff_id ) ? $previous_booking->staff_id : '' );
			$previous_staff_name  = isset( $previous_booking->staff_name ) ? (string) $previous_booking->staff_name : '';
			$new_staff_name       = isset( $updated_booking->staff_name ) ? (string) $updated_booking->staff_name : '';
			$expected_new_staff_id = isset( $updated_booking->staff_id ) ? (string) $updated_booking->staff_id : '';
			$new_staff_email = $this->get_booking_staff_email( $updated_booking );

			foreach ( $audiences as $audience ) {
				if ( 'previous_staff' === $audience && '' !== $previous_staff_email && $previous_staff_email === $new_staff_email ) {
					continue;
				}
				$args = array(
					sanitize_key( $audience ),
					absint( $updated_booking->id ),
					$previous_staff_email,
					$previous_staff_name,
					$new_staff_name,
					$expected_new_staff_id,
				);
				if ( wp_next_scheduled( 'rgl_booking_send_consultant_change_event', $args ) ) {
					continue;
				}
				if ( false === wp_schedule_single_event( time() + 10, 'rgl_booking_send_consultant_change_event', $args ) ) {
					$this->send_queued_consultant_change_notification( ...$args );
				}
			}
		}

		private function send_booking_notifications( $event, $booking, $recipients = array( 'admin', 'staff', 'customer' ) ) {
			foreach ( $recipients as $recipient ) {
				$this->queue_booking_notification( $event, $recipient, $booking, 10 );
			}
		}

		private function build_admin_custom_booking_message( $booking, $custom_message ) {
			// Render the standard branded booking email, with the admin's typed
			// message in place of the usual intro line, for visual consistency
			// with every other email the system sends.
			return $this->render_booking_email_html( 'booking_made', 'customer', $booking, $custom_message );
		}

		private function send_admin_custom_message_to_bookings( $bookings, $subject, $custom_message ) {
			$subject = sanitize_text_field( $subject );
			$custom_message = trim( (string) $custom_message );
			if ( '' === $subject ) { return new WP_Error( 'missing_subject', 'Please enter an email subject.' ); }
			if ( '' === $custom_message ) { return new WP_Error( 'missing_message', 'Please enter an email message.' ); }
			if ( empty( $bookings ) || ! is_array( $bookings ) ) { return new WP_Error( 'missing_recipients', 'No matching bookings could be found.' ); }
			$sent = 0; $failed = 0;
			foreach ( $bookings as $booking ) {
				if ( ! $booking || empty( $booking->customer_email ) || ! is_email( $booking->customer_email ) ) { $failed++; continue; }
				$message = $this->build_admin_custom_booking_message( $booking, $custom_message );
				if ( $this->send_mail( $booking->customer_email, $subject, $message, array(), true, 'customer' ) ) { $sent++; } else { $failed++; }
			}
			if ( 0 === $sent ) { return new WP_Error( 'mail_failed', 'WordPress could not send the message to any matching bookings.' ); }
			return array( 'sent' => $sent, 'failed' => $failed );
		}

		private function resend_customer_confirmation_email_for_booking( $booking ) {
			if ( ! $booking ) {
				return new WP_Error( 'missing_booking', 'Booking could not be found.' );
			}

			$to = isset( $booking->customer_email ) ? sanitize_email( $booking->customer_email ) : '';
			if ( ! is_email( $to ) ) {
				return new WP_Error( 'missing_email', 'This booking has no valid customer email address.' );
			}

			$subject = $this->build_booking_notification_subject( 'booking_made', 'customer', $booking );
			$message = $this->render_booking_email_html( 'booking_made', 'customer', $booking );

			if ( ! $this->send_mail( $to, $subject, $message, array(), true, 'customer' ) ) {
				return new WP_Error( 'mail_failed', 'WordPress could not send the confirmation email.' );
			}

			return true;
		}

		private function get_admin_bookings() {
			global $wpdb;
			if ( ! $this->table_exists() ) {
				return array();
			}
			$sortable = array(
				'booking_reference' => 'booking_reference',
				'location_name'     => 'location_name',
				'service_name'      => 'service_name',
				'staff_name'        => 'staff_name',
				'booking_date'      => 'booking_date',
				'customer_name'     => 'customer_name',
				'booking_status'    => 'booking_status',
			);
			$requested_orderby = isset( $_GET['orderby'] ) && is_string( $_GET['orderby'] ) ? wp_unslash( $_GET['orderby'] ) : '';
			$orderby = isset( $sortable[ $requested_orderby ] ) ? $sortable[ $requested_orderby ] : 'booking_date';
			$order = isset( $_GET['order'] ) && is_string( $_GET['order'] ) && 'asc' === strtolower( (string) wp_unslash( $_GET['order'] ) ) ? 'ASC' : 'DESC';
			$ref_filter = isset( $_GET['filter_ref'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_ref'] ) ) : '';
			$customer_filter = isset( $_GET['filter_customer'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_customer'] ) ) : '';
			$location_filter = isset( $_GET['filter_location'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_location'] ) ) : '';
			$service_filter = isset( $_GET['filter_service'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_service'] ) ) : '';
			$staff_filter = isset( $_GET['filter_staff'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_staff'] ) ) : '';
			$date_filter = isset( $_GET['filter_date'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_date'] ) ) : '';
			if ( '' !== $date_filter && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_filter ) ) {
				$date_filter = '';
			}
			$time_filter = isset( $_GET['filter_time'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_time'] ) ) : '';
			if ( '' !== $time_filter && ! preg_match( '/^\d{2}:\d{2}$/', $time_filter ) ) {
				$time_filter = '';
			}
			$where = array();
			$args = array();
			if ( '' !== $ref_filter ) {
				$where[] = 'booking_reference LIKE %s';
				$args[] = '%' . $wpdb->esc_like( $ref_filter ) . '%';
			}
			if ( '' !== $customer_filter ) {
				$where[] = 'customer_name LIKE %s';
				$args[] = '%' . $wpdb->esc_like( $customer_filter ) . '%';
			}
			if ( '' !== $location_filter ) {
				$where[] = 'location_name LIKE %s';
				$args[] = '%' . $wpdb->esc_like( $location_filter ) . '%';
			}
			if ( '' !== $service_filter ) {
				$where[] = 'service_name LIKE %s';
				$args[] = '%' . $wpdb->esc_like( $service_filter ) . '%';
			}
			if ( '' !== $staff_filter ) {
				$where[] = 'staff_name LIKE %s';
				$args[] = '%' . $wpdb->esc_like( $staff_filter ) . '%';
			}
			if ( '' !== $date_filter ) {
				$where[] = 'booking_date = %s';
				$args[] = $date_filter;
			}
			if ( '' !== $time_filter ) {
				$where[] = 'booking_time LIKE %s';
				$args[] = $wpdb->esc_like( $time_filter ) . '%';
			}
			$sql = "SELECT * FROM {$this->table}";
			if ( ! empty( $where ) ) {
				$sql .= ' WHERE ' . implode( ' AND ', $where );
			}
			$sql .= " ORDER BY {$orderby} {$order}, id DESC LIMIT 200";
			if ( ! empty( $args ) ) {
				$sql = $wpdb->prepare( $sql, ...$args );
			}
			return $wpdb->get_results( $sql );
		}


		private function get_admin_calendar_bookings( $start_date, $end_date ) {
			global $wpdb;
			if ( ! $this->table_exists() ) {
				return array();
			}

			if ( ! $this->is_valid_date( $start_date ) || ! $this->is_valid_date( $end_date ) ) {
				return array();
			}

			$location_filter = isset( $_GET['filter_location'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_location'] ) ) : '';
			$service_filter  = isset( $_GET['filter_service'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_service'] ) ) : '';
			$staff_filter    = isset( $_GET['filter_staff'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_staff'] ) ) : '';

			$where = array( 'booking_date >= %s', 'booking_date <= %s' );
			$args  = array( $start_date, $end_date );

			if ( '' !== $location_filter ) {
				$where[] = 'location_name LIKE %s';
				$args[]  = '%' . $wpdb->esc_like( $location_filter ) . '%';
			}
			if ( '' !== $service_filter ) {
				$where[] = 'service_name LIKE %s';
				$args[]  = '%' . $wpdb->esc_like( $service_filter ) . '%';
			}
			if ( '' !== $staff_filter ) {
				$where[] = 'staff_name LIKE %s';
				$args[]  = '%' . $wpdb->esc_like( $staff_filter ) . '%';
			}

			$sql = "SELECT * FROM {$this->table} WHERE " . implode( ' AND ', $where ) . ' ORDER BY booking_date ASC, booking_time ASC, service_name ASC, staff_name ASC, id ASC LIMIT 5000';
			$sql = $wpdb->prepare( $sql, ...$args );

			return $wpdb->get_results( $sql );
		}

		private function get_booking_summary_counts( $bookings ) {
			$summary = array(
				'total'     => 0,
				'confirmed' => 0,
				'cancelled' => 0,
				'completed' => 0,
				'no_show'   => 0,
			);

			foreach ( (array) $bookings as $booking ) {
				$summary['total']++;
				$status = isset( $booking->booking_status ) ? (string) $booking->booking_status : self::STATUS_CONFIRMED;
				if ( self::STATUS_CANCELLED === $status ) { $summary['cancelled']++; }
				elseif ( self::STATUS_COMPLETED === $status ) { $summary['completed']++; }
				elseif ( self::STATUS_NO_SHOW === $status ) { $summary['no_show']++; }
				else { $summary['confirmed']++; }
			}

			return $summary;
		}


		private function get_booking_calendar_map( $bookings ) {
			$map = array();

			foreach ( (array) $bookings as $booking ) {
				$date = isset( $booking->booking_date ) ? (string) $booking->booking_date : '';
				if ( ! $this->is_valid_date( $date ) ) {
					continue;
				}

				$time     = isset( $booking->booking_time ) ? substr( (string) $booking->booking_time, 0, 5 ) : '';
				$location = isset( $booking->location_name ) ? (string) $booking->location_name : '';
				$service  = isset( $booking->service_name ) ? (string) $booking->service_name : '';
				$staff    = isset( $booking->staff_name ) ? (string) $booking->staff_name : '';
				$status   = isset( $booking->booking_status ) ? (string) $booking->booking_status : self::STATUS_CONFIRMED;
				$spaces   = isset( $booking->spaces_booked ) ? max( 1, absint( $booking->spaces_booked ) ) : 1;
				$key      = md5( $date . '|' . $time . '|' . $location . '|' . $service . '|' . $staff );

				if ( ! isset( $map[ $date ] ) ) {
					$map[ $date ] = array();
				}

				if ( ! isset( $map[ $date ][ $key ] ) ) {
					$map[ $date ][ $key ] = array(
						'time'            => $time,
						'location'        => $location,
						'service'         => $service,
						'staff'           => $staff,
						'booking_count'   => 0,
						'spaces_booked'   => 0,
						'cancelled_count' => 0,
						'status'          => self::STATUS_CONFIRMED,
					);
				}

				$map[ $date ][ $key ]['booking_count']++;
				if ( self::STATUS_CANCELLED === $status ) {
					$map[ $date ][ $key ]['cancelled_count']++;
				} else {
					$map[ $date ][ $key ]['spaces_booked'] += $spaces;
				}
			}

			foreach ( $map as $date => $items ) {
				$items = array_values( $items );
				foreach ( $items as $index => $item ) {
					if ( $item['booking_count'] > 0 && $item['cancelled_count'] >= $item['booking_count'] ) {
						$items[ $index ]['status'] = self::STATUS_CANCELLED;
					}
				}
				usort(
					$items,
					function( $a, $b ) {
						$time_compare = strcmp( $a['time'], $b['time'] );
						if ( 0 !== $time_compare ) {
							return $time_compare;
						}
						return strcmp( $a['service'] . $a['staff'] . $a['location'], $b['service'] . $b['staff'] . $b['location'] );
					}
				);
				$map[ $date ] = $items;
			}

			return $map;
		}

		/**
		 * Count future bookings that still need to be fulfilled.
		 *
		 * Only Confirmed and Pending approval bookings are included. Past,
		 * cancelled, completed and no-show records never appear in the menu badge.
		 */
		private function get_upcoming_admin_menu_summary() {
			static $summary = null;

			if ( null !== $summary ) {
				return $summary;
			}

			$summary = array(
				'count'     => 0,
				'next_date' => '',
			);

			if ( ! $this->table_exists() ) {
				return $summary;
			}

			$now   = current_datetime();
			$today = $now->format( 'Y-m-d' );
			$time  = $now->format( 'H:i:s' );

			global $wpdb;
			$sql = $wpdb->prepare(
				"SELECT COUNT(*) AS booking_count, MIN(booking_date) AS next_date
				 FROM {$this->table}
				 WHERE booking_status IN (%s, %s)
				   AND (booking_date > %s OR (booking_date = %s AND booking_time > %s))",
				self::STATUS_CONFIRMED,
				self::STATUS_PENDING_APPROVAL,
				$today,
				$today,
				$time
			);

			$row = $wpdb->get_row( $sql, ARRAY_A );
			if ( is_array( $row ) ) {
				$summary['count']     = max( 0, absint( $row['booking_count'] ?? 0 ) );
				$summary['next_date'] = sanitize_text_field( $row['next_date'] ?? '' );
			}

			return $summary;
		}

		private function get_upcoming_admin_menu_count() {
			$summary = $this->get_upcoming_admin_menu_summary();
			return $summary['count'];
		}

		private function get_admin_menu_booking_badge_class( $next_date ) {
			if ( '' === $next_date ) {
				return 'rgl-booking-badge-future';
			}

			$now      = current_datetime();
			$today    = $now->format( 'Y-m-d' );
			$tomorrow = $now->modify( '+1 day' )->format( 'Y-m-d' );

			if ( $today === $next_date ) {
				return 'rgl-booking-badge-today';
			}
			if ( $tomorrow === $next_date ) {
				return 'rgl-booking-badge-tomorrow';
			}

			return 'rgl-booking-badge-future';
		}

		public function admin_menu_booking_badge_styles() {
			echo '<style id="rgl-booking-menu-badge-colours">'
				. '#adminmenu .rgl-booking-badge-today{background-color:#00a32a!important;color:#fff!important;}'
				. '#adminmenu .rgl-booking-badge-tomorrow{background-color:#dba617!important;color:#1d2327!important;}'
				. '#adminmenu .rgl-booking-badge-future{background-color:#d63638!important;color:#fff!important;}'
				. '</style>';
		}

		public function admin_menu() {
			$menu_title = 'Appt-Booker';
			$summary    = $this->get_upcoming_admin_menu_summary();
			$count      = $summary['count'];

			if ( $count > 0 ) {
				$badge_class = $this->get_admin_menu_booking_badge_class( $summary['next_date'] );
				$menu_title .= sprintf(
					' <span class="awaiting-mod count-%1$d %2$s"><span class="pending-count">%3$s</span></span>',
					$count,
					esc_attr( $badge_class ),
					number_format_i18n( $count )
				);
			}

			add_menu_page(
				'Appt-Booker',
				$menu_title,
				'manage_options',
				'appt-booker',
				array( $this, 'render_admin_page' ),
				'dashicons-calendar-alt',
				58
			);
		}

		/**
		 * Make a value safe to open in spreadsheet software.
		 *
		 * Excel and similar applications may execute cells beginning with =, +,
		 * -, @, a tab or a carriage return as formulae. Prefixing an apostrophe
		 * keeps customer-supplied CSV content as plain text.
		 */
		private function csv_safe_value( $value ) {
			if ( is_array( $value ) || is_object( $value ) ) {
				$value = wp_json_encode( $value );
			}
			$value = (string) $value;
			if ( preg_match( '/^(?:[=+\-@]|[\t\r\n]|[ \t\r\n]+[=+\-@])/', $value ) ) {
				return "'" . $value;
			}
			return $value;
		}

		private function csv_safe_row( $row ) {
			return array_map( array( $this, 'csv_safe_value' ), (array) $row );
		}

		public function handle_admin_postbacks() {
			if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
				return;
			}
			if ( empty( $_POST['rgl_booking_action'] ) ) {
				return;
			}
			if ( ! isset( $_POST['rgl_booking_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rgl_booking_nonce'] ) ), 'rgl_booking_admin_action' ) ) {
				return;
			}
			$action = sanitize_text_field( wp_unslash( $_POST['rgl_booking_action'] ) );

			if ( 'run_install' === $action ) {
				$this->install_schema();
				$this->maybe_seed_defaults();
				$this->redirect_admin( 'installed', 'bookings' );
			}

			// Customer data tools (added) - GDPR export / delete by email.
			if ( 'export_customer_data' === $action ) {
				$cd_email = isset( $_POST['cd_email'] ) ? sanitize_email( wp_unslash( $_POST['cd_email'] ) ) : '';
				$rows     = '' !== $cd_email ? $this->get_bookings_for_email( $cd_email ) : array();
				$timed_block = '' !== $cd_email ? $this->get_booking_block_for_email( $cd_email ) : false;
				$legacy_block = '' !== $cd_email ? $this->email_is_legacy_blocked( $cd_email ) : false;
				if ( empty( $rows ) && ! $timed_block && ! $legacy_block ) {
					$this->redirect_admin_with_message( 'cd_none', 'customer-data', 'No bookings or future-booking blocks found for that email address.' );
				}
				// Stream a CSV download. Runs on admin_init, before any output.
				nocache_headers();
				header( 'Content-Type: text/csv; charset=utf-8' );
				header( 'Content-Disposition: attachment; filename="customer-data-' . sanitize_file_name( $cd_email ) . '.csv"' );
				$out = fopen( 'php://output', 'w' );
				fputcsv( $out, array( 'Reference', 'Status', 'Date', 'Time', 'Service', 'Team Member', 'Communication Method', 'Name', 'Email', 'Phone', 'Interests', 'Customer Message', 'Conversation Notes', 'Cancellation Reason', 'Created', 'Future booking block active', 'Block expires', 'Block reason' ) );
				foreach ( $rows as $r ) {
					fputcsv( $out, $this->csv_safe_row( array(
						isset( $r->booking_reference ) ? $r->booking_reference : '',
						isset( $r->booking_status ) ? $r->booking_status : '',
						isset( $r->booking_date ) ? $r->booking_date : '',
						isset( $r->booking_time ) ? $r->booking_time : '',
						isset( $r->service_name ) ? $r->service_name : '',
						isset( $r->staff_name ) ? $r->staff_name : '',
						isset( $r->location_name ) ? $r->location_name : '',
						isset( $r->customer_name ) ? $r->customer_name : '',
						isset( $r->customer_email ) ? $r->customer_email : '',
						isset( $r->customer_phone ) ? $r->customer_phone : '',
						isset( $r->customer_interests ) ? $r->customer_interests : '',
						isset( $r->customer_notes ) ? $r->customer_notes : '',
						isset( $r->admin_notes ) ? $r->admin_notes : '',
						isset( $r->cancellation_reason ) ? $r->cancellation_reason : '',
						isset( $r->created_at ) ? $r->created_at : '',
						( $timed_block || $legacy_block ) ? 'Yes' : 'No',
						$timed_block ? wp_date( 'Y-m-d H:i:s', absint( $timed_block['expires_at'] ) ) : ( $legacy_block ? 'No automatic expiry (legacy list)' : '' ),
						$timed_block ? sanitize_key( isset( $timed_block['reason'] ) ? $timed_block['reason'] : '' ) : ( $legacy_block ? 'legacy_or_spam_list' : '' ),
					) ) );
				}
				if ( empty( $rows ) && ( $timed_block || $legacy_block ) ) {
					fputcsv( $out, $this->csv_safe_row( array( '', '', '', '', '', '', '', '', $cd_email, '', '', '', '', '', '', 'Yes', $timed_block ? wp_date( 'Y-m-d H:i:s', absint( $timed_block['expires_at'] ) ) : 'No automatic expiry (legacy list)', $timed_block ? sanitize_key( isset( $timed_block['reason'] ) ? $timed_block['reason'] : '' ) : 'legacy_or_spam_list' ) ) );
				}
				fclose( $out );
				exit;
			}

			if ( 'delete_customer_data' === $action ) {
				global $wpdb;
				$cd_email = isset( $_POST['cd_email'] ) ? sanitize_email( wp_unslash( $_POST['cd_email'] ) ) : '';
				if ( '' === $cd_email ) {
					$this->redirect_admin_with_message( 'cd_none', 'customer-data', 'No email address supplied.' );
				}
				$count = (int) $wpdb->query(
					$wpdb->prepare( "DELETE FROM {$this->table} WHERE customer_email = %s", $cd_email )
				);
				$this->remove_all_booking_blocks_for_email( $cd_email );
				$this->log_retention_action( sprintf( 'Manual deletion: %d record(s) removed for a customer email request.', $count ) );
				$this->redirect_admin_with_message( 'cd_deleted', 'customer-data', sprintf( '%d booking record(s) permanently deleted.', $count ) );
			}

			// Content Research export (added) - ZIP of CSV + analysis prompt.
			if ( 'export_content_research' === $action ) {
				$cr_from = isset( $_POST['cr_from'] ) ? sanitize_text_field( wp_unslash( $_POST['cr_from'] ) ) : '';
				$cr_to   = isset( $_POST['cr_to'] ) ? sanitize_text_field( wp_unslash( $_POST['cr_to'] ) ) : '';
				if ( '' === $cr_from || '' === $cr_to ) {
					$this->redirect_admin_with_message( 'cr_dates', 'content-research', 'Please choose a from and to date.' );
				}
				// Both values are interpolated into the download filename and the
				// ZIP entry names below, so validate the shape before using them.
				if ( ! $this->is_valid_date( $cr_from ) || ! $this->is_valid_date( $cr_to ) ) {
					$this->redirect_admin_with_message( 'cr_dates', 'content-research', 'Please choose valid from and to dates.' );
				}
				if ( $cr_from > $cr_to ) {
					$this->redirect_admin_with_message( 'cr_dates', 'content-research', 'The from date must not be after the to date.' );
				}
				$rows = $this->get_bookings_for_content_research( $cr_from, $cr_to );
				if ( empty( $rows ) ) {
					$this->redirect_admin_with_message( 'cr_empty', 'content-research', 'No consultations found in that range.' );
				}

				// Build CSV content in memory.
				$csv_handle = fopen( 'php://temp', 'r+' );
				fputcsv( $csv_handle, array( 'Date', 'Service', 'Communication Method', 'Topics of Interest', 'Customer Message', 'Conversation Notes' ) );
				foreach ( $rows as $r ) {
					fputcsv( $csv_handle, $this->csv_safe_row( array(
						isset( $r->booking_date ) ? $r->booking_date : '',
						isset( $r->service_name ) ? $r->service_name : '',
						isset( $r->location_name ) ? $r->location_name : '',
						isset( $r->customer_interests ) ? $r->customer_interests : '',
						isset( $r->customer_notes ) ? $r->customer_notes : '',
						isset( $r->admin_notes ) ? $r->admin_notes : '',
					) ) );
				}
				rewind( $csv_handle );
				$csv_content = stream_get_contents( $csv_handle );
				fclose( $csv_handle );

				// Analysis prompt from settings, with a small header noting the data range.
				$settings = $this->get_settings();
				$prompt_text = isset( $settings['content_research_prompt'] ) ? (string) $settings['content_research_prompt'] : '';
				$prompt_full = sprintf(
					"Data range: %s to %s\nTotal consultations: %d\n\n%s\n",
					$cr_from, $cr_to, count( $rows ), trim( $prompt_text )
				);

				// Build ZIP using WordPress's bundled PclZip if ZipArchive is unavailable.
				$tmp_zip = wp_tempnam( 'content-research.zip' );
				if ( class_exists( 'ZipArchive' ) ) {
					$zip = new ZipArchive();
					if ( true === $zip->open( $tmp_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
						$zip->addFromString( 'consultations-' . $cr_from . '-to-' . $cr_to . '.csv', $csv_content );
						$zip->addFromString( 'analysis-prompt.txt', $prompt_full );
						$zip->close();
					}
				} else {
					require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
					$pclzip = new PclZip( $tmp_zip );
					$pclzip->create(
						array(
							array( PCLZIP_ATT_FILE_NAME => 'consultations-' . $cr_from . '-to-' . $cr_to . '.csv', PCLZIP_ATT_FILE_CONTENT => $csv_content ),
							array( PCLZIP_ATT_FILE_NAME => 'analysis-prompt.txt', PCLZIP_ATT_FILE_CONTENT => $prompt_full ),
						),
						PCLZIP_OPT_NO_COMPRESSION
					);
				}

				if ( ! file_exists( $tmp_zip ) || filesize( $tmp_zip ) === 0 ) {
					$this->redirect_admin_with_message( 'cr_fail', 'content-research', 'Could not generate the export file.' );
				}

				nocache_headers();
				header( 'Content-Type: application/zip' );
				header( 'Content-Disposition: attachment; filename="content-research-' . $cr_from . '-to-' . $cr_to . '.zip"' );
				header( 'Content-Length: ' . filesize( $tmp_zip ) );
				readfile( $tmp_zip );
				@unlink( $tmp_zip );
				exit;
			}

			if ( 'save_settings' === $action ) {
				// General settings and Form Style settings share one option row.
				// Build the posted values on top of what is already stored, or
				// every style_* key would be dropped from the option and silently
				// fall back to its default the next time get_settings() runs.
				$settings = $this->get_settings();
				$settings = array_merge( $settings, array(
					'form_title'          => sanitize_text_field( wp_unslash( isset( $_POST['form_title'] ) ? $_POST['form_title'] : '' ) ),
					'form_intro'          => sanitize_textarea_field( wp_unslash( isset( $_POST['form_intro'] ) ? $_POST['form_intro'] : '' ) ),
					'success_message'     => sanitize_text_field( wp_unslash( isset( $_POST['success_message'] ) ? $_POST['success_message'] : '' ) ),
					'booking_prefix'      => sanitize_text_field( wp_unslash( isset( $_POST['booking_prefix'] ) ? $_POST['booking_prefix'] : 'APPT' ) ),
					'email_from_address'  => sanitize_email( wp_unslash( isset( $_POST['email_from_address'] ) ? $_POST['email_from_address'] : get_option( 'admin_email' ) ) ),
					'show_location_field' => empty( $_POST['show_location_field'] ) ? 0 : 1,
					'show_service_field'  => empty( $_POST['show_service_field'] ) ? 0 : 1,
					'show_staff_field'    => empty( $_POST['show_staff_field'] ) ? 0 : 1,
					'show_address_field'  => empty( $_POST['show_address_field'] ) ? 0 : 1,
					'show_phone_field'    => 1,
					'label_location'       => sanitize_text_field( wp_unslash( isset( $_POST['label_location'] ) ? $_POST['label_location'] : 'Location' ) ),
					'label_service'        => sanitize_text_field( wp_unslash( isset( $_POST['label_service'] ) ? $_POST['label_service'] : 'Service' ) ),
					'label_staff'          => sanitize_text_field( wp_unslash( isset( $_POST['label_staff'] ) ? $_POST['label_staff'] : 'Team Member' ) ),
					'label_address'        => sanitize_text_field( wp_unslash( isset( $_POST['label_address'] ) ? $_POST['label_address'] : 'Address' ) ),
					'label_phone'          => sanitize_text_field( wp_unslash( isset( $_POST['label_phone'] ) ? $_POST['label_phone'] : 'Phone Number' ) ),
					'default_location_id' => sanitize_key( wp_unslash( isset( $_POST['default_location_id'] ) ? $_POST['default_location_id'] : '' ) ),
					'default_service_id'  => sanitize_key( wp_unslash( isset( $_POST['default_service_id'] ) ? $_POST['default_service_id'] : '' ) ),
					'default_staff_id'    => sanitize_key( wp_unslash( isset( $_POST['default_staff_id'] ) ? $_POST['default_staff_id'] : '' ) ),
					// Security / anti-abuse (added).
					'security_min_seconds_to_submit' => max( 0, absint( wp_unslash( isset( $_POST['security_min_seconds_to_submit'] ) ? $_POST['security_min_seconds_to_submit'] : 3 ) ) ),
					'security_throttle_seconds'      => max( 0, absint( wp_unslash( isset( $_POST['security_throttle_seconds'] ) ? $_POST['security_throttle_seconds'] : 60 ) ) ),
					'security_turnstile_enabled'     => empty( $_POST['security_turnstile_enabled'] ) ? 0 : 1,
					'security_turnstile_site_key'    => sanitize_text_field( wp_unslash( isset( $_POST['security_turnstile_site_key'] ) ? $_POST['security_turnstile_site_key'] : '' ) ),
					'security_turnstile_secret_key'  => sanitize_text_field( wp_unslash( isset( $_POST['security_turnstile_secret_key'] ) ? $_POST['security_turnstile_secret_key'] : '' ) ),
					// Customer self-cancellation (added).
					'cancellation_enabled'           => empty( $_POST['cancellation_enabled'] ) ? 0 : 1,
					'cancellation_min_hours_notice'  => max( 0, absint( wp_unslash( isset( $_POST['cancellation_min_hours_notice'] ) ? $_POST['cancellation_min_hours_notice'] : 1 ) ) ),
					'cancellation_ask_for_reason'    => empty( $_POST['cancellation_ask_for_reason'] ) ? 0 : 1,
					// Shared resource + capacity (added).
					'shared_resource_enabled'        => empty( $_POST['shared_resource_enabled'] ) ? 0 : 1,
					'shared_resource_buffer'         => max( 0, absint( wp_unslash( isset( $_POST['shared_resource_buffer'] ) ? $_POST['shared_resource_buffer'] : 15 ) ) ),
					'daily_booking_cap'              => max( 0, absint( wp_unslash( isset( $_POST['daily_booking_cap'] ) ? $_POST['daily_booking_cap'] : 0 ) ) ),
					'comm_method_verbs'              => $this->sanitize_comm_method_verbs( isset( $_POST['comm_method_verbs'] ) ? wp_unslash( $_POST['comm_method_verbs'] ) : array() ),
					'comm_method_customer_notes'     => $this->sanitize_comm_method_customer_notes( isset( $_POST['comm_method_customer_notes'] ) ? $_POST['comm_method_customer_notes'] : array() ),
					'email_footer_text'              => sanitize_textarea_field( wp_unslash( isset( $_POST['email_footer_text'] ) ? $_POST['email_footer_text'] : '' ) ),
					'customer_message_label'         => sanitize_text_field( wp_unslash( isset( $_POST['customer_message_label'] ) ? $_POST['customer_message_label'] : '' ) ),
					'booking_privacy_notice'         => sanitize_textarea_field( wp_unslash( isset( $_POST['booking_privacy_notice'] ) ? $_POST['booking_privacy_notice'] : '' ) ),
					'retention_enabled'              => empty( $_POST['retention_enabled'] ) ? 0 : 1,
					'retention_minimise_months'      => max( 1, absint( wp_unslash( isset( $_POST['retention_minimise_months'] ) ? $_POST['retention_minimise_months'] : 6 ) ) ),
					'retention_delete_months'        => max( 1, absint( wp_unslash( isset( $_POST['retention_delete_months'] ) ? $_POST['retention_delete_months'] : 30 ) ) ),
					'availability_cutoff_hour'       => max( 0, min( 23, absint( wp_unslash( isset( $_POST['availability_cutoff_hour'] ) ? $_POST['availability_cutoff_hour'] : 16 ) ) ) ),
					'availability_weeks_ahead'       => max( 1, min( 12, absint( wp_unslash( isset( $_POST['availability_weeks_ahead'] ) ? $_POST['availability_weeks_ahead'] : 2 ) ) ) ),
					'availability_block_after_long_break' => empty( $_POST['availability_block_after_long_break'] ) ? 0 : 1,
					'rate_limit_per_email_enabled'   => empty( $_POST['rate_limit_per_email_enabled'] ) ? 0 : 1,
					'rate_limit_per_email_max'       => max( 1, min( 50, absint( wp_unslash( isset( $_POST['rate_limit_per_email_max'] ) ? $_POST['rate_limit_per_email_max'] : 3 ) ) ) ),
					'rate_limit_per_email_days'      => max( 1, min( 365, absint( wp_unslash( isset( $_POST['rate_limit_per_email_days'] ) ? $_POST['rate_limit_per_email_days'] : 30 ) ) ) ),
					'manual_approval_enabled'        => empty( $_POST['manual_approval_enabled'] ) ? 0 : 1,
					'no_show_safeguard_enabled'      => empty( $_POST['no_show_safeguard_enabled'] ) ? 0 : 1,
					'no_show_threshold'              => max( 1, min( 10, absint( wp_unslash( isset( $_POST['no_show_threshold'] ) ? $_POST['no_show_threshold'] : 2 ) ) ) ),
					'no_show_window_days'            => max( 1, min( 730, absint( wp_unslash( isset( $_POST['no_show_window_days'] ) ? $_POST['no_show_window_days'] : 180 ) ) ) ),
					'no_show_block_days'             => max( 1, min( 730, absint( wp_unslash( isset( $_POST['no_show_block_days'] ) ? $_POST['no_show_block_days'] : 365 ) ) ) ),
					'reminder_enabled'               => empty( $_POST['reminder_enabled'] ) ? 0 : 1,
					'reminder_hours_before'          => max( 1, min( 168, absint( wp_unslash( isset( $_POST['reminder_hours_before'] ) ? $_POST['reminder_hours_before'] : 24 ) ) ) ),
					'reschedule_enabled'               => empty( $_POST['reschedule_enabled'] ) ? 0 : 1,
					'reschedule_min_hours_notice'      => max( 0, min( 168, absint( wp_unslash( isset( $_POST['reschedule_min_hours_notice'] ) ? $_POST['reschedule_min_hours_notice'] : 1 ) ) ) ),
					'blocked_emails'                 => $this->sanitize_blocked_emails( wp_unslash( isset( $_POST['blocked_emails'] ) ? $_POST['blocked_emails'] : '' ) ),
					'content_research_prompt'        => sanitize_textarea_field( wp_unslash( isset( $_POST['content_research_prompt'] ) ? $_POST['content_research_prompt'] : '' ) ),
				) );
				$notification_keys = array(
					'notify_admin_booking_made',
					'notify_admin_booking_edited',
					'notify_admin_booking_cancelled',
					'notify_staff_booking_made',
					'notify_staff_booking_edited',
					'notify_staff_booking_cancelled',
					'notify_customer_booking_made',
					'notify_customer_booking_edited',
					'notify_customer_booking_cancelled',
					'notify_admin_booking_rescheduled',
					'notify_staff_booking_rescheduled',
					'notify_customer_booking_rescheduled',
					'notify_staff_consultant_changed',
					'notify_customer_consultant_changed',
				);
				foreach ( $notification_keys as $notification_key ) {
					$settings[ $notification_key ] = empty( $_POST[ $notification_key ] ) ? 0 : 1;
				}
				update_option( self::OPTION_SETTINGS, $settings );
				$this->redirect_admin( 'settings_saved', 'settings' );
			}


			if ( 'save_taxonomy_field' === $action ) {
				$taxonomy = $this->normalize_relationship_taxonomy( isset( $_POST['taxonomy'] ) ? wp_unslash( $_POST['taxonomy'] ) : '' );
				$original_key = sanitize_key( wp_unslash( isset( $_POST['original_field_key'] ) ? $_POST['original_field_key'] : '' ) );
				$label = sanitize_text_field( wp_unslash( isset( $_POST['field_label'] ) ? $_POST['field_label'] : '' ) );
				$key = sanitize_key( wp_unslash( isset( $_POST['field_key'] ) ? $_POST['field_key'] : '' ) );
				$type = sanitize_key( wp_unslash( isset( $_POST['field_type'] ) ? $_POST['field_type'] : 'text' ) );
				$options_raw = sanitize_textarea_field( wp_unslash( isset( $_POST['field_options'] ) ? $_POST['field_options'] : '' ) );
				if ( '' === $key && '' !== $label ) { $key = sanitize_key( str_replace( ' ', '_', strtolower( $label ) ) ); }
				$types = $this->taxonomy_custom_field_types();
				if ( $taxonomy && '' !== $label && '' !== $key && isset( $types[ $type ] ) ) {
					$fields = $this->get_taxonomy_custom_fields();
					if ( empty( $fields[ $taxonomy ] ) || ! is_array( $fields[ $taxonomy ] ) ) { $fields[ $taxonomy ] = array(); }
					$options = array();
					if ( in_array( $type, array( 'select', 'checkboxes', 'checkbox' ), true ) && '' !== $options_raw ) {
						foreach ( preg_split( '/\r\n|\r|\n/', $options_raw ) as $option ) {
							$option = sanitize_text_field( $option );
							if ( '' !== $option ) { $options[] = $option; }
						}
					}
					if ( $original_key && $original_key !== $key && isset( $fields[ $taxonomy ][ $original_key ] ) ) {
						unset( $fields[ $taxonomy ][ $original_key ] );
						$this->delete_taxonomy_custom_field_post_meta_for_taxonomy( $taxonomy, $original_key );
					}
					$fields[ $taxonomy ][ $key ] = array( 'label' => $label, 'type' => $type, 'options' => array_values( array_unique( $options ) ) );
					update_option( self::OPTION_TAXONOMY_FIELDS, $fields );
					$this->sync_elementor_content_records();
				}
				$this->redirect_admin( 'taxonomy_fields_saved', 'taxonomy-fields' );
			}

			if ( 'delete_taxonomy_field' === $action ) {
				$taxonomy = $this->normalize_relationship_taxonomy( isset( $_POST['taxonomy'] ) ? wp_unslash( $_POST['taxonomy'] ) : '' );
				$key = sanitize_key( wp_unslash( isset( $_POST['field_key'] ) ? $_POST['field_key'] : '' ) );
				$fields = $this->get_taxonomy_custom_fields();
				if ( $taxonomy && $key && isset( $fields[ $taxonomy ][ $key ] ) ) {
					unset( $fields[ $taxonomy ][ $key ] );
					update_option( self::OPTION_TAXONOMY_FIELDS, $fields );
					$this->delete_taxonomy_custom_field_post_meta_for_taxonomy( $taxonomy, $key );
				}
				$this->redirect_admin( 'taxonomy_fields_saved', 'taxonomy-fields' );
			}
			if ( 'save_style_settings' === $action ) {
				$settings = $this->sanitize_style_settings( $_POST, $this->get_settings() );
				update_option( self::OPTION_SETTINGS, $settings );
				$this->redirect_admin( 'settings_saved', 'style' );
			}

			if ( 'reset_style_settings' === $action ) {
				$defaults = $this->default_settings();
				$settings = $this->get_settings();
				foreach ( array_merge( array( 'form_title', 'form_intro' ), $this->style_setting_keys() ) as $style_key ) {
					if ( array_key_exists( $style_key, $defaults ) ) {
						$settings[ $style_key ] = $defaults[ $style_key ];
					}
				}
				update_option( self::OPTION_SETTINGS, $settings );
				$this->redirect_admin( 'settings_saved', 'style' );
			}

			if ( 'save_locations' === $action ) {
				$raw_locations = array();

				if ( isset( $_POST['locations'] ) && is_array( $_POST['locations'] ) ) {
					$raw_locations = $_POST['locations'];
				}

				// Note: values decoded from the JSON payload have already been
				// unslashed here, and sanitize_location_rows() unslashes each
				// field again. Re-slash them so that second pass is a no-op and a
				// legitimate backslash in an address or description survives.
				if ( empty( $raw_locations ) && isset( $_POST['locations_payload'] ) ) {
					$decoded_locations = json_decode( wp_unslash( $_POST['locations_payload'] ), true );
					if ( is_array( $decoded_locations ) ) {
						$decoded_locations = wp_slash( $decoded_locations );
						$raw_locations = $decoded_locations;
					}
				}

				update_option( self::OPTION_LOCATIONS, $this->sanitize_location_rows( $raw_locations ) );
				$this->sync_elementor_content_records();
				$this->redirect_admin( 'locations_saved', 'locations' );
			}

			if ( 'save_staff' === $action ) {
				update_option( self::OPTION_STAFF, $this->sanitize_staff_rows( isset( $_POST['staff'] ) ? $_POST['staff'] : array() ) );
				$this->sync_elementor_content_records();
				$this->redirect_admin( 'staff_saved', 'staff' );
			}

			if ( 'save_services' === $action ) {
				update_option( self::OPTION_SERVICES, $this->sanitize_service_rows( isset( $_POST['services'] ) ? $_POST['services'] : array() ) );
				$this->sync_elementor_content_records();
				$this->redirect_admin( 'services_saved', 'services' );
			}

			if ( 'add_booking' === $action ) {
				$payload = $this->sanitize_booking_payload( $_POST );
				if ( ! $this->acquire_booking_write_lock() ) {
					$this->redirect_admin_with_message( 'booking_error', 'bookings', 'The booking system is busy. Please try again.' );
				}
				register_shutdown_function( function () { $this->release_booking_write_lock(); } );

				$result = $this->validate_booking_payload( $payload, 0 );
				if ( is_wp_error( $result ) ) {
					$this->release_booking_write_lock();
					$this->redirect_admin_with_message( 'booking_error', 'bookings', $result->get_error_message() );
				}

				global $wpdb;
				$this->ensure_booking_quantity_column();
				$reference = $this->generate_booking_reference();
				$status    = in_array( $payload['booking_status'], array( self::STATUS_CONFIRMED, self::STATUS_CANCELLED, self::STATUS_PENDING_APPROVAL, self::STATUS_COMPLETED, self::STATUS_NO_SHOW ), true ) ? $payload['booking_status'] : self::STATUS_CONFIRMED;
				$outcome_timing = $this->validate_outcome_status_timing( $status, $payload['booking_date'], $payload['booking_time'] );
				if ( is_wp_error( $outcome_timing ) ) {
					$this->release_booking_write_lock();
					$this->redirect_admin_with_message( 'booking_error', 'bookings', $outcome_timing->get_error_message() );
				}
				$inserted  = $wpdb->insert(
					$this->table,
					array(
						'created_at'         => current_time( 'mysql' ),
						'booking_reference'  => $reference,
						'spaces_booked'      => $payload['booking_quantity'],
						'location_id'        => $result['location']['id'],
						'location_name'      => $result['location']['name'],
						'service_id'         => $result['service']['id'],
						'service_name'       => $result['service']['name'],
						'staff_id'           => $result['staff'] ? $result['staff']['id'] : '',
						'staff_name'         => $result['staff'] ? $result['staff']['name'] : '',
						'customer_name'      => $payload['customer_name'],
						'customer_email'     => $payload['customer_email'],
						'customer_phone'     => $payload['customer_phone'],
						'customer_address'   => $payload['customer_address'],
						'customer_notes'     => $payload['customer_notes'],
						'customer_interests' => $payload['customer_interests'],
						'admin_notes'        => $payload['admin_notes'],
						'booking_date'       => $payload['booking_date'],
						'booking_time'       => $payload['booking_time'] . ':00',
						'booking_end_time'   => $result['end_time'] . ':00',
						'booking_status'     => $status,
					)
				);
				if ( false === $inserted ) {
					$this->release_booking_write_lock();
					$this->redirect_admin_with_message( 'booking_error', 'bookings', 'Booking could not be added. Please choose a valid available slot.' );
				}
				$new_booking = $this->get_booking_by_id( (int) $wpdb->insert_id );
				$this->mark_restricted_date_consumed( $payload['booking_date'], (int) $wpdb->insert_id );
				$this->release_booking_write_lock();
				if ( $new_booking && ! in_array( $status, array( self::STATUS_COMPLETED, self::STATUS_NO_SHOW ), true ) ) {
					$event = self::STATUS_PENDING_APPROVAL === $status ? 'booking_pending_approval' : ( self::STATUS_CANCELLED === $status ? 'booking_cancelled' : 'booking_made' );
					$this->send_booking_notifications( $event, $new_booking );
				}
				$this->redirect_admin( 'booking_added', 'bookings' );
			}

			if ( 'save_booking' === $action ) {
				$booking_id = absint( isset( $_POST['booking_id'] ) ? $_POST['booking_id'] : 0 );
				if ( ! $booking_id ) {
					$this->redirect_admin_with_message( 'booking_error', 'bookings', 'Booking could not be saved.' );
				}
				$previous_booking = $this->get_booking_by_id( $booking_id );
				if ( ! $previous_booking ) {
					$this->redirect_admin_with_message( 'booking_error', 'bookings', 'Booking could not be found.' );
				}
				if ( ! $this->acquire_booking_write_lock() ) {
					$this->redirect_admin_with_message( 'booking_error', 'bookings', 'The booking system is busy. Please try again.' );
				}
				register_shutdown_function( function () { $this->release_booking_write_lock(); } );

				$payload = $this->sanitize_booking_payload( $_POST );
				$schedule_unchanged = (string) $payload['location_id'] === (string) $previous_booking->location_id
					&& (string) $payload['service_id'] === (string) $previous_booking->service_id
					&& (string) $payload['staff_id'] === (string) $previous_booking->staff_id
					&& (string) $payload['booking_date'] === (string) $previous_booking->booking_date
					&& (string) $payload['booking_time'] === substr( (string) $previous_booking->booking_time, 0, 5 )
					&& (int) $payload['booking_quantity'] === (int) ( isset( $previous_booking->spaces_booked ) ? $previous_booking->spaces_booked : 1 );
				$result = $schedule_unchanged
					? $this->validate_unchanged_admin_booking_payload( $payload, $previous_booking )
					: $this->validate_booking_payload( $payload, $booking_id );
				if ( is_wp_error( $result ) ) {
					$this->release_booking_write_lock();
					$this->redirect_admin_with_message( 'booking_error', 'bookings', $result->get_error_message() );
				}

				$current_status = isset( $previous_booking->booking_status ) ? (string) $previous_booking->booking_status : self::STATUS_CONFIRMED;
				$previous_staff_id = isset( $previous_booking->staff_id ) ? sanitize_key( $previous_booking->staff_id ) : '';
				$posted_status  = isset( $_POST['booking_status'] ) ? $payload['booking_status'] : $current_status;
				$allowed_statuses = array( self::STATUS_CONFIRMED, self::STATUS_CANCELLED, self::STATUS_PENDING_APPROVAL, self::STATUS_COMPLETED, self::STATUS_NO_SHOW );
				$new_status = in_array( $posted_status, $allowed_statuses, true ) ? $posted_status : $current_status;
				$outcome_timing = $this->validate_outcome_status_timing( $new_status, $payload['booking_date'], $payload['booking_time'] );
				if ( is_wp_error( $outcome_timing ) ) {
					$this->release_booking_write_lock();
					$this->redirect_admin_with_message( 'booking_error', 'bookings', $outcome_timing->get_error_message() );
				}
				$new_staff_id = $result['staff'] ? sanitize_key( $result['staff']['id'] ) : '';
				$staff_changed = $previous_staff_id !== $new_staff_id;
				$appointment_changed = (string) $previous_booking->booking_date !== (string) $payload['booking_date']
					|| substr( (string) $previous_booking->booking_time, 0, 5 ) !== (string) $payload['booking_time'];
				$customer_email_changed = strtolower( trim( (string) $previous_booking->customer_email ) ) !== strtolower( trim( (string) $payload['customer_email'] ) );

				global $wpdb;
				$this->ensure_booking_quantity_column();
				$update_data = array(
					'location_id'        => $result['location']['id'],
					'location_name'      => $result['location']['name'],
					'service_id'         => $result['service']['id'],
					'service_name'       => $result['service']['name'],
					'staff_id'           => $new_staff_id,
					'staff_name'         => $result['staff'] ? $result['staff']['name'] : '',
					'spaces_booked'      => $payload['booking_quantity'],
					'customer_name'      => $payload['customer_name'],
					'customer_email'     => $payload['customer_email'],
					'customer_phone'     => $payload['customer_phone'],
					'customer_address'   => $payload['customer_address'],
					'customer_notes'     => $payload['customer_notes'],
					'customer_interests' => $payload['customer_interests'],
					'admin_notes'        => $payload['admin_notes'],
					'booking_date'       => $payload['booking_date'],
					'booking_time'       => $payload['booking_time'] . ':00',
					'booking_end_time'   => $result['end_time'] . ':00',
					'booking_status'     => $new_status,
				);
				if ( $this->reminder_sent_column_exists() && ( $appointment_changed || $customer_email_changed || ( self::STATUS_CONFIRMED === $new_status && self::STATUS_CONFIRMED !== $current_status ) ) ) {
					$update_data['reminder_sent_at'] = null;
				}
				$updated = $wpdb->update( $this->table, $update_data, array( 'id' => $booking_id ) );
				if ( false === $updated ) {
					$this->release_booking_write_lock();
					$this->redirect_admin_with_message( 'booking_error', 'bookings', 'Booking could not be saved. Please choose a valid available slot.' );
				}

				$updated_booking = $this->get_booking_by_id( $booking_id );
				$this->release_booking_write_lock();
				if ( $updated_booking && ! in_array( (string) $updated_booking->booking_status, array( self::STATUS_COMPLETED, self::STATUS_NO_SHOW ), true ) ) {
					$was_cancelled = self::STATUS_CANCELLED === $current_status;
					$is_cancelled  = self::STATUS_CANCELLED === $updated_booking->booking_status;
					$was_pending   = self::STATUS_PENDING_APPROVAL === $current_status;
					$is_confirmed = self::STATUS_CONFIRMED === $updated_booking->booking_status;
					if ( $was_pending && $is_confirmed ) {
						$this->send_booking_notifications( 'booking_approved', $updated_booking, array( 'customer', 'staff' ) );
						if ( $staff_changed ) {
							$this->queue_consultant_change_notifications( $previous_booking, $updated_booking, array( 'previous_staff' ) );
						}
					} elseif ( $is_cancelled && ! $was_cancelled ) {
						$this->send_booking_notifications( 'booking_cancelled', $updated_booking );
					} elseif ( $staff_changed && $is_confirmed && self::STATUS_CONFIRMED === $current_status ) {
						$this->queue_consultant_change_notifications( $previous_booking, $updated_booking );
						$this->send_booking_notifications( 'booking_edited', $updated_booking, array( 'admin' ) );
					} else {
						$this->send_booking_notifications( 'booking_edited', $updated_booking );
					}
				}
				$this->redirect_admin( 'booking_saved', 'bookings' );
			}

			if ( 'duplicate_booking' === $action ) {
				$booking_id = absint( isset( $_POST['booking_id'] ) ? $_POST['booking_id'] : 0 );
				$booking    = $this->get_booking_by_id( $booking_id );
				if ( ! $booking ) {
					$this->redirect_admin_with_message( 'booking_error', 'bookings', 'Booking could not be duplicated.' );
				}
				if ( ! $this->acquire_booking_write_lock() ) {
					$this->redirect_admin_with_message( 'booking_error', 'bookings', 'The booking system is busy. Please try again.' );
				}
				register_shutdown_function( function () { $this->release_booking_write_lock(); } );

				$duplicate_source = array(
					'location_id'        => isset( $booking->location_id ) ? $booking->location_id : '',
					'service_id'         => isset( $booking->service_id ) ? $booking->service_id : '',
					'staff_id'           => isset( $booking->staff_id ) ? $booking->staff_id : '',
					'customer_name'      => isset( $booking->customer_name ) ? $booking->customer_name : '',
					'customer_email'     => isset( $booking->customer_email ) ? $booking->customer_email : '',
					'customer_phone'     => isset( $booking->customer_phone ) ? $booking->customer_phone : '',
					'customer_address'   => isset( $booking->customer_address ) ? $booking->customer_address : '',
					'customer_notes'     => isset( $booking->customer_notes ) ? $booking->customer_notes : '',
					'customer_interests' => isset( $booking->customer_interests ) ? $booking->customer_interests : '',
					'admin_notes'        => isset( $booking->admin_notes ) ? $booking->admin_notes : '',
					'booking_date'       => isset( $booking->booking_date ) ? $booking->booking_date : '',
					'booking_time'       => isset( $booking->booking_time ) ? substr( (string) $booking->booking_time, 0, 5 ) : '',
					'booking_quantity'   => isset( $booking->spaces_booked ) ? max( 1, absint( $booking->spaces_booked ) ) : 1,
					'booking_status'     => isset( $booking->booking_status ) ? $booking->booking_status : self::STATUS_CONFIRMED,
				);
				$payload = $this->sanitize_booking_payload( $duplicate_source );
				$result  = $this->validate_booking_payload( $payload, 0 );
				if ( is_wp_error( $result ) ) {
					$this->release_booking_write_lock();
					$this->redirect_admin_with_message( 'booking_error', 'bookings', 'Booking could not be duplicated: ' . $result->get_error_message() );
				}

				$status = in_array( $payload['booking_status'], array( self::STATUS_CONFIRMED, self::STATUS_CANCELLED, self::STATUS_PENDING_APPROVAL, self::STATUS_COMPLETED, self::STATUS_NO_SHOW ), true ) ? $payload['booking_status'] : self::STATUS_CONFIRMED;
				$outcome_timing = $this->validate_outcome_status_timing( $status, $payload['booking_date'], $payload['booking_time'] );
				if ( is_wp_error( $outcome_timing ) ) {
					$this->release_booking_write_lock();
					$this->redirect_admin_with_message( 'booking_error', 'bookings', $outcome_timing->get_error_message() );
				}
				global $wpdb;
				$this->ensure_booking_quantity_column();
				$duplicated = $wpdb->insert(
					$this->table,
					array(
						'created_at'         => current_time( 'mysql' ),
						'booking_reference'  => $this->generate_booking_reference(),
						'spaces_booked'      => $payload['booking_quantity'],
						'location_id'        => $result['location']['id'],
						'location_name'      => $result['location']['name'],
						'service_id'         => $result['service']['id'],
						'service_name'       => $result['service']['name'],
						'staff_id'           => $result['staff'] ? $result['staff']['id'] : '',
						'staff_name'         => $result['staff'] ? $result['staff']['name'] : '',
						'customer_name'      => $payload['customer_name'],
						'customer_email'     => $payload['customer_email'],
						'customer_phone'     => $payload['customer_phone'],
						'customer_address'   => $payload['customer_address'],
						'customer_notes'     => $payload['customer_notes'],
						'customer_interests' => $payload['customer_interests'],
						'admin_notes'        => $payload['admin_notes'],
						'booking_date'       => $payload['booking_date'],
						'booking_time'       => $payload['booking_time'] . ':00',
						'booking_end_time'   => $result['end_time'] . ':00',
						'booking_status'     => $status,
					)
				);
				if ( false === $duplicated ) {
					$this->release_booking_write_lock();
					$this->redirect_admin_with_message( 'booking_error', 'bookings', 'Booking could not be duplicated.' );
				}
				$new_booking = $this->get_booking_by_id( (int) $wpdb->insert_id );
				$this->mark_restricted_date_consumed( $payload['booking_date'], (int) $wpdb->insert_id );
				$this->release_booking_write_lock();
				if ( $new_booking && ! in_array( $status, array( self::STATUS_COMPLETED, self::STATUS_NO_SHOW ), true ) ) {
					$event = self::STATUS_PENDING_APPROVAL === $status ? 'booking_pending_approval' : ( self::STATUS_CANCELLED === $status ? 'booking_cancelled' : 'booking_made' );
					$this->send_booking_notifications( $event, $new_booking );
				}
				$this->redirect_admin( 'booking_duplicated', 'bookings' );
			}

			if ( 'delete_booking' === $action ) {
				$booking_id = absint( isset( $_POST['booking_id'] ) ? $_POST['booking_id'] : 0 );
				if ( $booking_id && $this->table_exists() ) {
					$booking_to_delete = $this->get_booking_by_id( $booking_id );
					$this->send_booking_notifications( 'booking_cancelled', $booking_to_delete );
					global $wpdb;
					$wpdb->delete( $this->table, array( 'id' => $booking_id ), array( '%d' ) );
				}
				$this->redirect_admin( 'booking_deleted', 'bookings' );
			}

			if ( 'send_booking_message' === $action ) {
				$booking_id = absint( isset( $_POST['booking_id'] ) ? $_POST['booking_id'] : 0 );
				$target     = sanitize_key( wp_unslash( isset( $_POST['message_target'] ) ? $_POST['message_target'] : 'single' ) );
				$subject    = sanitize_text_field( wp_unslash( isset( $_POST['message_subject'] ) ? $_POST['message_subject'] : '' ) );
				$message    = sanitize_textarea_field( wp_unslash( isset( $_POST['message_body'] ) ? $_POST['message_body'] : '' ) );
				$booking    = $this->get_booking_by_id( $booking_id );
				if ( ! $booking ) { $this->redirect_admin_with_message( 'booking_message_error', 'bookings', 'Booking could not be found.' ); }
				$recipients = 'session' === $target ? $this->get_session_bookings_for_booking( $booking ) : array( $booking );
				$result = $this->send_admin_custom_message_to_bookings( $recipients, $subject, $message );
				if ( is_wp_error( $result ) ) { $this->redirect_admin_with_message( 'booking_message_error', 'bookings', $result->get_error_message() ); }
				$count_message = sprintf( 'Message sent to %d booking%s.', (int) $result['sent'], 1 === (int) $result['sent'] ? '' : 's' );
				if ( ! empty( $result['failed'] ) ) { $count_message .= sprintf( ' %d failed.', (int) $result['failed'] ); }
				$this->redirect_admin_with_message( 'booking_message_sent', 'bookings', $count_message );
			}

			if ( 'resend_confirmation' === $action ) {
				$booking_id = absint( isset( $_POST['booking_id'] ) ? $_POST['booking_id'] : 0 );
				$booking = $this->get_booking_by_id( $booking_id );
				$result = $this->resend_customer_confirmation_email_for_booking( $booking );

				if ( is_wp_error( $result ) ) {
					$this->redirect_admin_with_message( 'confirmation_email_error', 'bookings', $result->get_error_message() );
				}

				$this->redirect_admin_with_message( 'confirmation_email_sent', 'bookings', 'Confirmation email resent to ' . $booking->customer_email . '.' );
			}


			if ( 'send_reminder_now' === $action ) {
				$booking_id = absint( isset( $_POST['booking_id'] ) ? $_POST['booking_id'] : 0 );
				$booking = $this->get_booking_by_id( $booking_id );
				if ( ! $booking ) {
					$this->redirect_admin_with_message( 'reminder_email_error', 'bookings', 'Booking could not be found.' );
				}
				if ( self::STATUS_CONFIRMED !== (string) $booking->booking_status || $this->get_booking_start_timestamp( $booking ) <= current_datetime()->getTimestamp() ) {
					$this->redirect_admin_with_message( 'reminder_email_error', 'bookings', 'A reminder can only be sent for a future confirmed appointment.' );
				}
				$this->ensure_reminder_sent_column();
				if ( ! $this->reminder_sent_column_exists() ) {
					$this->redirect_admin_with_message( 'reminder_email_error', 'bookings', 'The reminder database field is unavailable.' );
				}
				if ( ! $this->send_booking_notification( 'booking_reminder', 'customer', $booking, true ) ) {
					$this->redirect_admin_with_message( 'reminder_email_error', 'bookings', 'WordPress could not send the reminder email.' );
				}
				global $wpdb;
				$recorded = $wpdb->update( $this->table, array( 'reminder_sent_at' => current_time( 'mysql' ) ), array( 'id' => $booking_id ), array( '%s' ), array( '%d' ) );
				if ( false === $recorded ) {
					$this->redirect_admin_with_message( 'reminder_email_error', 'bookings', 'The reminder email was sent, but its sent time could not be recorded. Check the mail log before sending again.' );
				}
				$this->redirect_admin_with_message( 'reminder_email_sent', 'bookings', 'Reminder sent to ' . $booking->customer_email . '.' );
			}

			if ( 'update_booking_status' === $action ) {
				$booking_id = absint( isset( $_POST['booking_id'] ) ? $_POST['booking_id'] : 0 );
				$status     = sanitize_text_field( wp_unslash( isset( $_POST['booking_status'] ) ? $_POST['booking_status'] : '' ) );

				if ( $booking_id && in_array( $status, array( self::STATUS_CONFIRMED, self::STATUS_CANCELLED, self::STATUS_COMPLETED, self::STATUS_NO_SHOW ), true ) && $this->table_exists() ) {
					$previous_booking = $this->get_booking_by_id( $booking_id );
					if ( $previous_booking ) {
						$outcome_timing = $this->validate_outcome_status_timing( $status, $previous_booking->booking_date, $previous_booking->booking_time );
						if ( is_wp_error( $outcome_timing ) ) {
							$this->redirect_admin_with_message( 'booking_error', 'bookings', $outcome_timing->get_error_message() );
						}
					}
					global $wpdb;
					$status_data    = array( 'booking_status' => $status );
					$status_formats = array( '%s' );
					if ( self::STATUS_CONFIRMED === $status && $this->reminder_sent_column_exists() ) {
						$status_data['reminder_sent_at'] = null;
						$status_formats[]                = '%s';
					}
					$wpdb->update(
						$this->table,
						$status_data,
						array( 'id' => $booking_id ),
						$status_formats,
						array( '%d' )
					);
					$updated_booking = $this->get_booking_by_id( $booking_id );
					if ( $updated_booking && ! in_array( $status, array( self::STATUS_COMPLETED, self::STATUS_NO_SHOW ), true ) ) {
						if ( self::STATUS_CANCELLED === $status && ( ! $previous_booking || self::STATUS_CANCELLED !== $previous_booking->booking_status ) ) {
							$this->send_booking_notifications( 'booking_cancelled', $updated_booking );
						} else {
							$this->send_booking_notifications( 'booking_edited', $updated_booking );
						}
					}
				}
				$this->redirect_admin( 'booking_saved', 'bookings' );
			}

			if ( in_array( $action, array( 'block_future_bookings', 'unblock_future_bookings' ), true ) ) {
				$booking_id = absint( isset( $_POST['booking_id'] ) ? $_POST['booking_id'] : 0 );
				$booking = $booking_id ? $this->get_booking_by_id( $booking_id ) : false;
				if ( ! $booking || empty( $booking->customer_email ) ) {
					$this->redirect_admin_with_message( 'booking_error', 'bookings', 'This booking has no customer email address to block or unblock.' );
				}
				if ( 'block_future_bookings' === $action ) {
					$block = $this->add_timed_booking_block( $booking->customer_email, 'manual_attendance_review' );
					if ( ! $block ) { $this->redirect_admin_with_message( 'booking_error', 'bookings', 'The future-booking block could not be saved.' ); }
					$this->redirect_admin_with_message( 'future_bookings_blocked', 'bookings', 'Future online bookings from this email are blocked until ' . wp_date( 'd-m-Y', absint( $block['expires_at'] ) ) . '. No customer email was sent.' );
				}
				$this->remove_all_booking_blocks_for_email( $booking->customer_email );
				$this->redirect_admin_with_message( 'future_bookings_unblocked', 'bookings', 'Future online bookings from this email are allowed again.' );
			}

			// Mark as Spam (added). Cancels the booking with a tailored
			// "this is for purchase questions, not a sales channel" customer
			// email, and adds the customer's email to the blocked list so
			// they cannot book again. Admin-only.
			if ( 'mark_as_spam' === $action ) {
				$booking_id = absint( isset( $_POST['booking_id'] ) ? $_POST['booking_id'] : 0 );
				if ( $booking_id && $this->table_exists() ) {
					$booking = $this->get_booking_by_id( $booking_id );
					if ( $booking ) {
						global $wpdb;
						$wpdb->update(
							$this->table,
							array(
								'booking_status'      => self::STATUS_CANCELLED,
								'cancellation_reason' => 'Marked as spam: this booking form is for customer purchase questions, not a sales channel.',
							),
							array( 'id' => $booking_id ),
							array( '%s', '%s' ),
							array( '%d' )
						);
						$updated = $this->get_booking_by_id( $booking_id );
						if ( $updated && ! empty( $updated->customer_email ) ) {
							$this->add_blocked_email( $updated->customer_email );
							$this->send_booking_notifications( 'booking_marked_spam', $updated, array( 'customer' ) );
						}
					}
				}
				$this->redirect_admin( 'booking_marked_spam', 'bookings' );
			}
		}

		private function redirect_admin( $notice, $tab = 'bookings' ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'   => 'appt-booker',
						'tab'    => $tab,
						'notice' => $notice,
					),
					admin_url( 'admin.php' )
				)
			);
			exit;
		}

		private function redirect_admin_with_message( $notice, $tab = 'bookings', $message = '' ) {
			$args = array(
				'page'   => 'appt-booker',
				'tab'    => $tab,
				'notice' => $notice,
			);
			if ( '' !== $message ) {
				$args['message'] = rawurlencode( $message );
			}
			wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
			exit;
		}

		public function enqueue_admin_assets( $hook = '' ) {
			if ( false === strpos( (string) $hook, 'appt-booker' ) ) {
				return;
			}
			if ( function_exists( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
			}
		}

		public function enqueue_assets() {
			if ( is_admin() ) {
				return;
			}

			$today = current_time( 'Y-m-d' );

			wp_register_style( 'appt-booker', false, array(), self::VERSION );
			wp_enqueue_style( 'appt-booker' );
			wp_add_inline_style(
				'appt-booker',
				$this->build_frontend_style_css( $this->get_settings() )
			);
			wp_add_inline_style(
				'appt-booker',
				'.rgl-booking-form .rgl-date[readonly]{background:#fff;cursor:pointer}#ui-datepicker-div{display:none;}.ui-datepicker{background:#fff;border:1px solid #ccd0d4;border-radius:6px;box-shadow:0 8px 24px rgba(0,0,0,.14);padding:10px;z-index:999999!important}.ui-datepicker-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}.ui-datepicker-title{font-weight:700;text-align:center;order:2;flex:1}.ui-datepicker-prev,.ui-datepicker-next{cursor:pointer;text-decoration:none;color:#2271b1;font-weight:700;padding:2px 6px}.ui-datepicker-prev{order:1}.ui-datepicker-next{order:3}.ui-datepicker-calendar{border-collapse:collapse;width:100%;font-size:13px}.ui-datepicker-calendar th,.ui-datepicker-calendar td{text-align:center;padding:2px}.ui-datepicker-calendar a{display:block;padding:6px 8px;border-radius:4px;text-decoration:none}.ui-datepicker-calendar .ui-state-default{background:#f6f7f7;color:#1d2327}.ui-datepicker-calendar .ui-state-active{background:#2271b1;color:#fff}.ui-datepicker-calendar .ui-datepicker-unselectable span{display:block;padding:6px 8px;color:#a7aaad}.ui-datepicker-calendar .rgl-date-available a{font-weight:700}.rgl-date-status{display:block;margin-top:6px;font-size:12px;color:#646970}.rgl-date-status.is-error{color:#b32d2e}'
			);

			wp_register_script( 'appt-booker', false, array( 'jquery', 'jquery-ui-datepicker' ), self::VERSION, true );
			wp_enqueue_script( 'appt-booker' );

			// Cloudflare Turnstile. The widget container is rendered by the
			// shortcode, but without this script it never initialises, no
			// cf-turnstile-response is produced, and request_is_abusive() then
			// rejects EVERY booking with "Security challenge failed". Enqueue it
			// here rather than asking the site owner to paste a tag into their
			// theme header, so ticking the setting cannot silently take the
			// booking form offline.
			$turnstile_settings = $this->get_settings();
			if ( ! empty( $turnstile_settings['security_turnstile_enabled'] ) && ! empty( $turnstile_settings['security_turnstile_site_key'] ) ) {
				wp_enqueue_script(
					'cf-turnstile',
					'https://challenges.cloudflare.com/turnstile/v0/api.js',
					array(),
					null,
					true
				);
			}
			$booking_labels = array(
				'service'  => $this->get_form_label( $this->get_settings(), 'label_service', 'Service' ),
				'location' => $this->get_form_label( $this->get_settings(), 'label_location', 'Location' ),
				'staff'    => $this->get_form_label( $this->get_settings(), 'label_staff', 'Team Member' ),
			);
			wp_localize_script(
				'appt-booker',
				'RGLBookingSystem',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'appt_booker_nonce' ),
					'today'   => $today,
					'buttonText' => ! empty( $this->get_settings()['style_button_text'] ) ? $this->get_settings()['style_button_text'] : 'Book Now',
					'labels' => array(
						'service' => $booking_labels['service'],
						'location' => $booking_labels['location'],
						'staff' => $booking_labels['staff'],
					),
					'placeholders' => array(
						'service' => $this->select_placeholder_for_label( $booking_labels['service'] ),
						'location' => $this->select_placeholder_for_label( $booking_labels['location'] ),
						'staff' => $this->select_placeholder_for_label( $booking_labels['staff'] ),
					),
				)
			);

			wp_add_inline_script(
				'appt-booker',
				<<<'JS'
jQuery(function($){
	/*
	 * Replace the nonce and signed form timestamp baked into the page HTML.
	 *
	 * A page served from a CDN, a page-cache plugin or the browser's
	 * back-forward cache carries whatever nonce was current when it was
	 * generated. WordPress nonces roll every 12 hours and expire at 24, so a
	 * cached booking page quietly stops working: check_ajax_referer() answers
	 * 403/-1 and nothing is logged anywhere. Refreshing on load costs one small
	 * request and makes the form immune to how aggressively the page is cached.
	 */
	function refreshBookingCredentials(){
		if(!window.RGLBookingSystem || !RGLBookingSystem.ajaxUrl){ return; }
		$.post(RGLBookingSystem.ajaxUrl, { action: 'rgl_booking_refresh_token' })
			.done(function(resp){
				if(!resp || !resp.success || !resp.data){ return; }
				if(resp.data.nonce){ RGLBookingSystem.nonce = resp.data.nonce; }
				if(resp.data.loadedAt){ $('.rgl-booking-form .rgl-form-loaded-at').val(resp.data.loadedAt); }
				if(resp.data.signature){ $('.rgl-booking-form .rgl-form-sig').val(resp.data.signature); }
			});
	}

	if($('.rgl-booking-form').length){
		refreshBookingCredentials();
		// bfcache restores fire pageshow with persisted=true and can hand back a
		// page that has been sitting in memory for days.
		window.addEventListener('pageshow', function(event){
			if(event.persisted){ refreshBookingCredentials(); }
		});
	}

	function fillSelect(select, items, placeholder, formatter){
		select.empty();
		select.append($('<option>').val('').text(placeholder || 'Select'));
		$.each(items || [], function(_, item){
			var label = formatter ? formatter(item) : item.name;
			select.append($('<option>').val(item.id || item.value).text(label));
		});
		select.prop('disabled', false);
	}

	function fillDisabledSelect(select, placeholder){
		if(!select.length || select.is('input[type="hidden"]')){ return; }
		select.empty().append($('<option>').val('').text(placeholder || 'Select a service first')).val('').prop('disabled', true);
	}

	function isVisibleChoiceField(field){
		return field.length && !field.is('input[type="hidden"]');
	}

	function getChosenServiceId(form){
		return form.find('.rgl-service').val() || '';
	}

	function needsServiceBeforeDependentChoices(form){
		var serviceSel = form.find('.rgl-service');
		return isVisibleChoiceField(serviceSel) && !getChosenServiceId(form);
	}

	function filterItemsByAllowed(select, items){
		var allowed = (select.attr('data-allowed-values') || '').split(',').map(function(value){ return $.trim(value); }).filter(Boolean);
		if(!allowed.length){ return items || []; }
		return $.grep(items || [], function(item){ return $.inArray(String(item.id || item.value || ''), allowed) !== -1; });
	}

	function fillStaff(select, items){
		fillSelect(select, items, items.length ? ((RGLBookingSystem.placeholders || {}).staff || 'Select team member') : 'No team members available');
	}

	function fillServices(select, items){
		fillSelect(select, items, items.length ? ((RGLBookingSystem.placeholders || {}).service || 'Select service') : 'No services available', function(item){
			return item.duration ? (item.name + ' (' + item.duration + ' mins)') : item.name;
		});
	}

	function fillLocations(select, items){
		fillSelect(select, items, items.length ? ((RGLBookingSystem.placeholders || {}).location || 'Select location') : 'No locations available');
	}

	function fillSlots(select, items){
		select.empty();
		select.append($('<option>').val('').text(items.length ? 'Select time' : 'No slots available'));
		$.each(items, function(_, item){
			var option = $('<option>').val(item.value).text(item.label);
			if (item.slot_capacity) {
				option.attr('data-slot-capacity', item.slot_capacity);
			}
			if (item.spaces_remaining) {
				option.attr('data-spaces-remaining', item.spaces_remaining);
			}
			if (item.spaces_booked || item.spaces_booked === 0) {
				option.attr('data-spaces-booked', item.spaces_booked);
			}
			select.append(option);
		});
		select.prop('disabled', items.length === 0);
		// Announce slot count to screen readers (added).
		var status = select.closest('form').find('.rgl-time-status');
		if(status.length){
			var msg = items.length === 0 ? 'No times available for the selected date.'
				: (items.length === 1 ? '1 time slot available.' : items.length + ' time slots available.');
			status.text(msg);
		}
		updateQuantityField(select.closest('form'));
	}


	function ymdFromDateObject(date){
		var yyyy = date.getFullYear();
		var mm = String(date.getMonth() + 1).padStart(2, '0');
		var dd = String(date.getDate()).padStart(2, '0');
		return yyyy + '-' + mm + '-' + dd;
	}

	function getAllowedValues(field){
		return (field.attr('data-allowed-values') || '').split(',').map(function(value){ return $.trim(value); }).filter(Boolean).join(',');
	}

	function getEffectiveFieldValue(form, selector){
		var field = form.find(selector);
		return field.val() || field.attr('data-default-value') || '';
	}

	function setDateStatus(form, message, isError){
		var status = form.find('.rgl-date-status');
		if(!status.length){ return; }
		status.text(message || '').toggleClass('is-error', !!isError);
	}

	function refreshDatepicker(form){
		var dateInput = form.find('.rgl-date');
		if(dateInput.length && dateInput.hasClass('hasDatepicker')){
			dateInput.datepicker('refresh');
		}
	}

	function applyAvailableDates(form, dates){
		dates = $.map(dates || [], function(date){ return String(date || ''); });
		var map = {};
		$.each(dates, function(_, date){ if(date){ map[date] = true; } });
		form.data('availableDates', dates);
		form.data('availableDateMap', map);
		form.data('availableDatesLoaded', true);

		var dateInput = form.find('.rgl-date');
		var current = dateInput.val();
		dateInput.prop('disabled', dates.length === 0).attr('min', RGLBookingSystem.today);
		if(current && !map[current]){
			dateInput.val('');
			fillSlots(form.find('.rgl-time'), []);
		}
		if(dates.length){
			setDateStatus(form, 'Only available dates can be selected. All times are UK time.', false);
		} else {
			setDateStatus(form, 'No available dates match the current choices.', true);
		}
		refreshDatepicker(form);
	}

	function initDatepicker(form){
		var dateInput = form.find('.rgl-date');
		if(!dateInput.length || dateInput.hasClass('hasDatepicker')){ return; }
		dateInput.datepicker({
			dateFormat: 'dd-mm-yy',
			altField: dateInput.siblings('.rgl-date-alt'),
			altFormat: 'yy-mm-dd',
			minDate: 0,
			beforeShowDay: function(date){
				var dateString = ymdFromDateObject(date);
				var loaded = !!form.data('availableDatesLoaded');
				var map = form.data('availableDateMap') || {};
				if(!loaded){ return [false, 'rgl-date-loading', 'Checking availability']; }
				if(map[dateString]){ return [true, 'rgl-date-available', 'Available']; }
				return [false, 'rgl-date-unavailable', 'No available slots'];
			},
			onSelect: function(){
				$(this).trigger('change');
			}
		});
	}

	function refreshAvailableDates(form){
		var dateInput = form.find('.rgl-date');
		if(!dateInput.length){ return $.Deferred().resolve().promise(); }
		initDatepicker(form);
		var requestId = (form.data('availableDatesRequestId') || 0) + 1;
		form.data('availableDatesRequestId', requestId);
		form.data('availableDatesLoaded', false);
		setDateStatus(form, 'Checking available dates...', false);
		refreshDatepicker(form);

		return $.post(RGLBookingSystem.ajaxUrl, {
			action: 'rgl_booking_get_available_dates',
			nonce: RGLBookingSystem.nonce,
			location_id: getEffectiveFieldValue(form, '.rgl-location'),
			service_id: getEffectiveFieldValue(form, '.rgl-service'),
			staff_id: getEffectiveFieldValue(form, '.rgl-staff'),
			allowed_location_ids: getAllowedValues(form.find('.rgl-location')),
			allowed_service_ids: getAllowedValues(form.find('.rgl-service')),
			allowed_staff_ids: getAllowedValues(form.find('.rgl-staff'))
		}).done(function(resp){
			if(requestId !== form.data('availableDatesRequestId')){ return; }
			if(resp && resp.success && resp.data && resp.data.dates){
				applyAvailableDates(form, resp.data.dates);
			} else {
				applyAvailableDates(form, []);
			}
		}).fail(function(){
			if(requestId !== form.data('availableDatesRequestId')){ return; }
			applyAvailableDates(form, []);
			setDateStatus(form, 'Could not check available dates. Please refresh and try again.', true);
		});
	}

	function updateQuantityField(form){
		var qty = form.find('.rgl-quantity');
		var hint = form.find('.rgl-quantity-hint');
		var timeSel = form.find('.rgl-time');
		if(!qty.length){ return; }
		var selected = timeSel.find('option:selected');
		var remaining = parseInt(selected.attr('data-spaces-remaining') || selected.attr('data-slot-capacity') || '1', 10);
		if(!timeSel.val() || isNaN(remaining) || remaining < 1){
			remaining = 1;
			qty.val(1).attr('max', 1).prop('disabled', true);
			hint.text('Choose a time first.');
			return;
		}
		qty.prop('disabled', false).attr('min', 1).attr('max', remaining);
		var current = parseInt(qty.val() || '1', 10);
		if(isNaN(current) || current < 1){ current = 1; }
		if(current > remaining){ current = remaining; }
		qty.val(current);
		hint.text(remaining === 1 ? '1 space available for this time.' : remaining + ' spaces available for this time.');
	}

	function fetchServices(form){
		return $.post(RGLBookingSystem.ajaxUrl, {
			action: 'rgl_booking_get_services',
			nonce: RGLBookingSystem.nonce,
			location_id: form.find('.rgl-location').val(),
			staff_id: form.find('.rgl-staff').val()
		});
	}

	function fetchLocations(form, ignoreStaff){
		return $.post(RGLBookingSystem.ajaxUrl, {
			action: 'rgl_booking_get_locations',
			nonce: RGLBookingSystem.nonce,
			service_id: form.find('.rgl-service').val(),
			staff_id: ignoreStaff ? '' : form.find('.rgl-staff').val()
		});
	}

	function fetchStaff(form){
		return $.post(RGLBookingSystem.ajaxUrl, {
			action: 'rgl_booking_get_staff',
			nonce: RGLBookingSystem.nonce,
			service_id: form.find('.rgl-service').val(),
			location_id: form.find('.rgl-location').val()
		});
	}


	function itemListContainsId(items, id){
		id = String(id || '');
		if(!id){ return false; }
		return (items || []).some(function(item){ return String(item.id || item.value || '') === id; });
	}

	function rememberManualChoice(field){
		if(!field || !field.length || field.is('input[type="hidden"]')){ return; }
		field.attr('data-user-value', field.val() || '');
	}

	function preserveOrClear(select, items){
		var current = select.data('preserve');
		select.removeData('preserve');
		if(current && itemListContainsId(items, current)){
			select.val(current);
		} else if(current) {
			select.val('');
		}
	}

	function applyPreferredValue(select, items){
		if(!select || !select.length){ return; }
		var userValue = select.attr('data-user-value') || '';
		var preferred = select.attr('data-default-value') || '';
		var current = select.val() || '';

		// A manual customer choice must win over the shortcode/template default.
		// If that manual choice is no longer valid for the rebuilt list, clear the field
		// instead of falling back to the original/default team member.
		if(userValue){
			if(itemListContainsId(items, userValue)){
				select.val(userValue);
			} else {
				select.val('');
			}
			return;
		}
		if(current && itemListContainsId(items, current)){
			return;
		}
		if(preferred && itemListContainsId(items, preferred)){
			select.val(preferred);
			return;
		}
		if((items || []).length === 1){
			select.val(items[0].id || items[0].value || '');
		}
	}

	function syncAfterManualStaffChange(form){
		var locationSel = form.find('.rgl-location');
		var serviceSel  = form.find('.rgl-service');
		var staffSel    = form.find('.rgl-staff');
		var timeSel     = form.find('.rgl-time');
		var serviceId   = getChosenServiceId(form);
		var staffId     = staffSel.val() || staffSel.attr('data-user-value') || '';
		var requestId   = (form.data('selectorRequestId') || 0) + 1;
		form.data('selectorRequestId', requestId);
		form.data('dateOptionRequestId', (form.data('dateOptionRequestId') || 0) + 1);

		if(!staffId || needsServiceBeforeDependentChoices(form)){
			syncSelectors(form);
			return;
		}

		fillSlots(timeSel, []);

		/*
		 * When the customer manually changes the Team Member, do not rebuild the
		 * Staff dropdown from the old Location. First let the selected Staff drive
		 * the valid Location list, then rebuild Staff from that new Location.
		 * This stops the form jumping Ravi -> blank -> Daniel after Location changes.
		 */
		fetchLocations(form).done(function(locationResp){
			if(requestId !== form.data('selectorRequestId')){ return; }
			var staffLocations = (locationResp && locationResp.success && locationResp.data && locationResp.data.locations) ? locationResp.data.locations : [];
			staffLocations = filterItemsByAllowed(locationSel, staffLocations);

			// Choose a valid location for the newly selected staff member, but do not
			// permanently shrink the Location dropdown to only that staff member. On a
			// Service SPT the user still needs to switch Location later, which should
			// then reveal the relevant team member for that location.
			var chosenLocationId = locationSel.val() || '';
			if(!itemListContainsId(staffLocations, chosenLocationId)){
				chosenLocationId = staffLocations.length ? (staffLocations[0].id || staffLocations[0].value || '') : '';
			}

			fetchLocations(form, true).done(function(allLocationResp){
				if(requestId !== form.data('selectorRequestId')){ return; }
				var locations = (allLocationResp && allLocationResp.success && allLocationResp.data && allLocationResp.data.locations) ? allLocationResp.data.locations : [];
				locations = filterItemsByAllowed(locationSel, locations);
				fillLocations(locationSel, locations);
				if(chosenLocationId && itemListContainsId(locations, chosenLocationId)){
					locationSel.val(chosenLocationId);
				} else {
					preserveOrClear(locationSel, locations);
					applyPreferredValue(locationSel, locations);
				}
				serviceSel.val(serviceId);
				staffSel.val(staffId);

			fetchStaff(form).done(function(staffResp){
				if(requestId !== form.data('selectorRequestId')){ return; }
				var staff = (staffResp && staffResp.success && staffResp.data && staffResp.data.staff) ? staffResp.data.staff : [];
				staff = filterItemsByAllowed(staffSel, staff);

				fillStaff(staffSel, staff);
				if(itemListContainsId(staff, staffId)){
					staffSel.val(staffId);
					staffSel.attr('data-user-value', staffId);
				} else {
					staffSel.val('');
					staffSel.attr('data-user-value', '');
				}
				serviceSel.val(serviceId);

				refreshAvailableDates(form).always(function(){
					loadSlots(form);
				});
			}).fail(function(){
				if(requestId !== form.data('selectorRequestId')){ return; }
				fillStaff(staffSel, []);
			});
			}).fail(function(){
				if(requestId !== form.data('selectorRequestId')){ return; }
				fillLocations(locationSel, staffLocations);
				if(chosenLocationId){ locationSel.val(chosenLocationId); }
			});
		}).fail(function(){
			if(requestId !== form.data('selectorRequestId')){ return; }
			fillLocations(locationSel, []);
		});
	}

	function syncSelectors(form){
		var locationSel = form.find('.rgl-location');
		var serviceSel  = form.find('.rgl-service');
		var staffSel    = form.find('.rgl-staff');
		var timeSel     = form.find('.rgl-time');
		var serviceId   = getChosenServiceId(form);
		var requestId   = (form.data('selectorRequestId') || 0) + 1;
		form.data('selectorRequestId', requestId);

		locationSel.data('preserve', locationSel.val());
		serviceSel.data('preserve', serviceSel.val());
		staffSel.data('preserve', staffSel.val());
		fillSlots(timeSel, []);

		/*
		 * Service is the primary dependency for the booking form.
		 * Visible Location/Staff selectors must not become active filters until
		 * a service has been chosen.
		 */
		if(needsServiceBeforeDependentChoices(form)){
			staffSel.val('');
			locationSel.val('');
			fillDisabledSelect(locationSel, 'Select a service first');
			fillDisabledSelect(staffSel, 'Select a service first');
			refreshAvailableDates(form);
			loadSlots(form);
			return;
		}

		$.when(fetchLocations(form), fetchStaff(form)).done(function(locationResp, staffResp){
			if(requestId !== form.data('selectorRequestId')){ return; }
			var locations = (locationResp[0] && locationResp[0].success && locationResp[0].data && locationResp[0].data.locations) ? locationResp[0].data.locations : [];
			var staff     = (staffResp[0] && staffResp[0].success && staffResp[0].data && staffResp[0].data.staff) ? staffResp[0].data.staff : [];

			locations = filterItemsByAllowed(locationSel, locations);
			staff = filterItemsByAllowed(staffSel, staff);

			fillLocations(locationSel, locations);
			fillStaff(staffSel, staff);

			preserveOrClear(locationSel, locations);
			preserveOrClear(staffSel, staff);
			applyPreferredValue(locationSel, locations);
			applyPreferredValue(staffSel, staff);

			// Keep the selected service fixed. It defines the valid location/staff choices.
			serviceSel.val(serviceId);

			refreshAvailableDates(form).always(function(){
				loadSlots(form);
			});
		}).fail(function(){
			if(requestId !== form.data('selectorRequestId')){ return; }
			fillLocations(locationSel, []);
			fillStaff(staffSel, []);
		});
	}
	function selectHasRealChoices(select){
		if(!select.length || select.is('input[type="hidden"]')){ return false; }
		return select.find('option').filter(function(){ return !!String($(this).val() || ''); }).length > 0;
	}

	function finalSlotCombinationStatus(form){
		var locationSel = form.find('.rgl-location');
		var serviceSel  = form.find('.rgl-service');
		var staffSel    = form.find('.rgl-staff');
		var locationId  = locationSel.val() || '';
		var serviceId   = serviceSel.val() || '';
		var staffId     = staffSel.val() || '';
		var date        = form.find('.rgl-date-alt').val() || '';
		var dateMap     = form.data('availableDateMap') || {};

		if(!date){ return { ready:false, message:'Choose an available date.' }; }
		if(form.data('availableDatesLoaded') && !dateMap[date]){ return { ready:false, message:'Choose an available date.' }; }
		if(!locationId){ return { ready:false, message:'Choose a location to see times.' }; }
		if(!serviceId){ return { ready:false, message:'Choose a service to see times.' }; }

		// If the form is showing specific team-member choices, wait until one is chosen.
		// This prevents a Date Last form showing times from a loose/default staff match instead of the final filtered combination.
		if(selectHasRealChoices(staffSel) && !staffId){
			return { ready:false, message:'Choose a team member to see times.' };
		}

		return {
			ready: true,
			locationId: locationId,
			serviceId: serviceId,
			staffId: staffId,
			date: date
		};
	}

	function loadSlots(form){
		var timeSel = form.find('.rgl-time');
		var statusEl = form.find('.rgl-time-status');
		var status  = finalSlotCombinationStatus(form);
		var requestId = (form.data('slotRequestId') || 0) + 1;
		form.data('slotRequestId', requestId);

		fillSlots(timeSel, []);
		timeSel.empty().append($('<option>').val('').text(status.ready ? 'Checking times...' : status.message)).prop('disabled', true);
		if(statusEl.length){
			statusEl.text(status.ready ? 'Checking availability...' : '');
		}
		updateQuantityField(form);

		if(!status.ready){
			return;
		}

		$.post(RGLBookingSystem.ajaxUrl, {
			action: 'rgl_booking_get_slots',
			nonce: RGLBookingSystem.nonce,
			location_id: status.locationId,
			service_id: status.serviceId,
			staff_id: status.staffId,
			booking_date: status.date,
			allowed_location_ids: getAllowedValues(form.find('.rgl-location')),
			allowed_service_ids: getAllowedValues(form.find('.rgl-service')),
			allowed_staff_ids: getAllowedValues(form.find('.rgl-staff'))
		}).done(function(resp){
			if(requestId !== form.data('slotRequestId')){ return; }
			if(resp && resp.success && resp.data && resp.data.slots){
				fillSlots(timeSel, resp.data.slots);
			} else {
				fillSlots(timeSel, []);
			}
		}).fail(function(){
			if(requestId !== form.data('slotRequestId')){ return; }
			fillSlots(timeSel, []);
		});
	}

	$(document).on('change', '.rgl-booking-form .rgl-location, .rgl-booking-form .rgl-service, .rgl-booking-form .rgl-staff', function(){
		var form = $(this).closest('form');
		rememberManualChoice($(this));
		if(!$(this).hasClass('rgl-service') && needsServiceBeforeDependentChoices(form)){
			$(this).val('');
			syncSelectors(form);
			return;
		}
		if($(this).hasClass('rgl-service')){
			// Clear the live value of staff/location regardless of whether the
			// field is a visible <select> or a hidden <input> - the previously
			// chosen staff/location may not be valid for the new service.
			// Leaving stale values causes fetchLocations to filter by an
			// invalid staff and return "No locations available". The
			// data-default-value attribute is preserved for applyPreferredValue.
			form.find('.rgl-location, .rgl-staff').val('').removeAttr('data-user-value');
			loadInterestsForService(form);
			// Per-service description box + textarea placeholder.
			var selectedOpt = $(this).find('option:selected');
			var descText = selectedOpt.data('customer-description') || '';
			var msgPlaceholder = selectedOpt.data('message-placeholder') || '';
			var qqEmail = selectedOpt.data('quick-question-email') || '';
			var descBox = form.find('.rgl-service-description');
			if(descText){
				// Escape the description as HTML, then if an email is set,
				// replace the phrase "email us" with a real mailto anchor.
				var escaped = String(descText)
					.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
					.replace(/"/g,'&quot;').replace(/'/g,'&#39;');
				if(qqEmail){
					var safeEmail = String(qqEmail)
						.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
						.replace(/"/g,'&quot;').replace(/'/g,'&#39;');
					var anchor = '<a href="mailto:' + safeEmail + '" style="text-decoration:underline;font-weight:600;">email us</a>';
					// Case-insensitive single-occurrence replacement of "email us".
					escaped = escaped.replace(/email us/i, anchor);
				}
				descBox.html(escaped).css('color', '#3f3a30');
			} else {
				// No service picked - show the placeholder text in a muted colour.
				var placeholder = descBox.attr('data-placeholder') || '';
				descBox.text(placeholder).css('color', '#6b6257');
			}
			var msgBox = form.find('textarea[name="customer_notes"]');
			if(msgBox.length){
				msgBox.attr('placeholder', msgPlaceholder || msgBox.attr('data-default-placeholder') || '');
			}
		}
		if($(this).hasClass('rgl-location')){
			// A location change means the previous manually selected staff member may no
			// longer be relevant. Clear it so the Staff dropdown can rebuild for the new
			// Location instead of dragging the old staff member's location back again.
			form.find('.rgl-staff').not('input[type="hidden"]').val('').removeAttr('data-user-value');
		}
		if($(this).hasClass('rgl-staff')){
			syncAfterManualStaffChange(form);
			return;
		}
		syncSelectors(form);
	});

	// Fetch and render the per-service interests checklist (added).
	function loadInterestsForService(form){
		var wrap = form.find('[data-rgl-interests-wrap]');
		if(!wrap.length){ return; }
		var serviceId = form.find('.rgl-service').val() || '';
		if(!serviceId){
			renderInterestsUI(wrap, { enabled:false, headline:'', options:[], max:0 });
			return;
		}
		$.post(RGLBookingSystem.ajaxUrl, {
			action: 'rgl_booking_get_interests',
			nonce: RGLBookingSystem.nonce,
			service_id: serviceId
		}).done(function(resp){
			if(resp && resp.success && resp.data){
				renderInterestsUI(wrap, resp.data);
			} else {
				renderInterestsUI(wrap, { enabled:false, headline:'', options:[], max:0 });
			}
		}).fail(function(){
			renderInterestsUI(wrap, { enabled:false, headline:'', options:[], max:0 });
		});
	}

	function renderInterestsUI(wrap, data){
		var enabled  = !!(data && data.enabled);
		var options  = (data && data.options) || [];
		var headline = (data && data.headline) || 'What would you like to discuss? (Optional, select any)';
		var maxSel   = parseInt((data && data.max) || 0, 10) || 0;

		if(!enabled || !options.length){
			wrap.empty().attr('hidden', 'hidden').addClass('is-empty');
			return;
		}
		wrap.removeAttr('hidden').removeClass('is-empty');

		var fs = $('<fieldset class="rgl-interests-fieldset" data-rgl-interests-fieldset></fieldset>')
			.attr('data-max-selections', maxSel);
		fs.append($('<legend class="rgl-interests-legend" data-rgl-interests-legend></legend>').text(headline));
		var optsWrap = $('<div class="rgl-interests-options" data-rgl-interests-options></div>');
		$.each(options, function(_, label){
			var lbl = $('<label class="rgl-interest-option"></label>');
			lbl.append($('<input type="checkbox" name="customer_interests[]">').val(label));
			lbl.append(' ').append($('<span></span>').text(label));
			optsWrap.append(lbl);
		});
		fs.append(optsWrap);
		fs.append($('<div class="rgl-interests-hint" data-rgl-interests-hint aria-live="polite"></div>'));

		wrap.empty().append(fs);
	}

	// Enforce max selections client-side (server still authoritative).
	$(document).on('change', '.rgl-booking-form [data-rgl-interests-fieldset] input[type="checkbox"]', function(){
		var fs = $(this).closest('[data-rgl-interests-fieldset]');
		if(!fs.length){ return; }
		var maxSel = parseInt(fs.attr('data-max-selections') || '0', 10) || 0;
		var checks = fs.find('input[type="checkbox"]');
		var checked = checks.filter(':checked').length;
		var hint = fs.find('[data-rgl-interests-hint]');
		if(maxSel > 0 && checked >= maxSel){
			checks.not(':checked').prop('disabled', true);
			if(hint.length){ hint.text('Maximum of ' + maxSel + ' selection' + (maxSel === 1 ? '' : 's') + ' reached.'); }
		} else {
			checks.prop('disabled', false);
			if(hint.length){ hint.text(maxSel > 0 ? ('Select up to ' + maxSel + '.') : ''); }
		}
	});

	$(document).on('change', '.rgl-booking-form .rgl-time', function(){
		var form = $(this).closest('form');
		updateQuantityField(form);
	});

	$(document).on('input change', '.rgl-booking-form .rgl-quantity', function(){
		var form = $(this).closest('form');
		updateQuantityField(form);
	});

	$(document).on('change', '.rgl-booking-form .rgl-date', function(){
		var form = $(this).closest('form');
		var selectedDate = form.find('.rgl-date-alt').val();
		var map = form.data('availableDateMap') || {};
		if(selectedDate && form.data('availableDatesLoaded') && !map[selectedDate]){
			$(this).val('');
			form.find('.rgl-date-alt').val('');
			fillSlots(form.find('.rgl-time'), []);
			setDateStatus(form, 'That date has no available slots for the current choices.', true);
			return;
		}
		loadSlots(form);
	});

	$(document).on('click', '.rgl-booking-reset', function(){
		var form = $(this).closest('form');
		if (!form.length || !form[0]) { return; }
		form[0].reset();
		form.find('.rgl-date-alt').val('');
		form.find('.rgl-booking-notice').removeClass('rgl-booking-success rgl-booking-error').text('');
		form.find('.rgl-quantity').val(1).attr('max', 1).prop('disabled', true);
		form.find('.rgl-quantity-hint').text('Choose a time first.');
		form.find('.rgl-booking-submit').text(RGLBookingSystem.buttonText || 'Book Now').prop('disabled', false);
		form.find('.rgl-location, .rgl-service, .rgl-staff').removeAttr('data-user-value');
		// Reset interests checklist (added).
		var wrap = form.find('[data-rgl-interests-wrap]');
		if(wrap.length){ wrap.empty().attr('hidden', 'hidden').addClass('is-empty'); }
		syncSelectors(form);
	});

	function showBookingRedirectOverlay(message){
		// Close and hide jQuery UI's datepicker before navigation. Some themes or
		// mail/browser transitions briefly expose its close/navigation icon at an
		// incorrect scale while the current document is being unloaded.
		try {
			$('.rgl-date.hasDatepicker').datepicker('hide');
		} catch (ignore) {}
		$('#ui-datepicker-div').hide();

		$('.rgl-booking-redirect-overlay').remove();
		var overlay = $('<div class="rgl-booking-redirect-overlay" role="status" aria-live="polite"><div class="rgl-booking-redirect-card"><span class="rgl-booking-redirect-spinner" aria-hidden="true"></span><span class="rgl-booking-redirect-text"></span></div></div>');
		overlay.find('.rgl-booking-redirect-text').text(message || 'Just confirming your booking');
		$('body').append(overlay);
		// Force a paint before assigning the new URL so Safari displays the clean
		// handoff instead of a transient theme element from the outgoing page.
		if (overlay[0]) { void overlay[0].offsetHeight; }
	}

	function bookingRedirectUrl(url){
		try {
			var target = new URL(url, window.location.href);
			target.searchParams.set('rgl_booking_transition', '1');
			return target.toString();
		} catch (ignore) {
			return url + (url.indexOf('?') === -1 ? '?' : '&') + 'rgl_booking_transition=1';
		}
	}

	// Safari can restore the outgoing page from its back-forward cache. Never
	// leave the full-screen handoff covering the form if the customer goes back.
	$(window).on('pageshow', function(){
		$('.rgl-booking-redirect-overlay').remove();
	});

	$(document).on('submit', '.rgl-booking-form', function(e){
		e.preventDefault();
		var form = $(this);
		// Defensive double-submit guard: if a submit is already in flight,
		// refuse to start another. The button is disabled below as well,
		// but a form-level flag catches any path that bypasses the button
		// (e.g. pressing Enter inside a field while button is mid-state).
		if(form.data('rgl-submitting')){ return; }
		form.data('rgl-submitting', true);
		var notice = form.find('.rgl-booking-notice');

		var button = form.find('.rgl-booking-submit');
		button.prop('disabled', true);
		notice.removeClass('rgl-booking-success rgl-booking-error').text('Saving...');

		// Build POST data, including any selected customer_interests (added).
		var postData = {
			action: 'rgl_booking_submit',
			nonce: RGLBookingSystem.nonce,
			location_id: form.find('[name="location_id"]').val(),
			service_id: form.find('[name="service_id"]').val(),
			staff_id: form.find('[name="staff_id"]').val(),
			customer_name: form.find('[name="customer_name"]').val(),
			customer_email: form.find('[name="customer_email"]').val(),
			customer_phone: form.find('[name="customer_phone"]').val(),
			customer_address: form.find('[name="customer_address"]').val(),
			customer_notes: form.find('[name="customer_notes"]').val() || '',
			booking_date: form.find('[name="booking_date"]').val(),
			booking_time: form.find('[name="booking_time"]').val(),
			booking_quantity: form.find('[name="booking_quantity"]').val() || 1,
			rgl_booking_company: form.find('[name="rgl_booking_company"]').val() || '',
			rgl_booking_form_loaded_at: form.find('[name="rgl_booking_form_loaded_at"]').val() || '',
			rgl_booking_form_sig: form.find('[name="rgl_booking_form_sig"]').val() || ''
		};
		var interests = [];
		form.find('input[name="customer_interests[]"]:checked').each(function(){ interests.push($(this).val()); });
		postData['customer_interests'] = interests;
		var turnstileToken = form.find('[name="cf-turnstile-response"]').val();
		if(turnstileToken){ postData['cf-turnstile-response'] = turnstileToken; }

		$.post(RGLBookingSystem.ajaxUrl, postData).done(function(resp){
			if(resp.success){
				if(resp.data && resp.data.redirect_url){
					var redirectMessage = resp.data.message || 'Just confirming your booking';
					notice.addClass('rgl-booking-success').text(redirectMessage);
					showBookingRedirectOverlay('Just confirming your booking');
					window.setTimeout(function(){
						window.location.assign(bookingRedirectUrl(resp.data.redirect_url));
					}, 40);
					return;
				}
				notice.addClass('rgl-booking-success').text(resp.data.message);
				form[0].reset();
				form.find('.rgl-date').attr('min', RGLBookingSystem.today);
				form.find('.rgl-quantity').val(1).attr('max', 1).prop('disabled', true);
				form.find('.rgl-quantity-hint').text('Choose a time first.');
				syncSelectors(form);
				// Keep the submit button disabled for a moment so an over-eager
				// second click can't fire a duplicate submission against the
				// newly-reset (or rebuilt) form state.
				setTimeout(function(){
					button.prop('disabled', false);
					form.removeData('rgl-submitting');
				}, 2000);
			}else{
				notice.addClass('rgl-booking-error').text(resp.data && resp.data.message ? resp.data.message : 'AJAX failed.');
				button.prop('disabled', false);
				form.removeData('rgl-submitting');
			}
		}).fail(function(){
			notice.addClass('rgl-booking-error').text('Request failed.');
			button.prop('disabled', false);
			form.removeData('rgl-submitting');
		});
	});

	$('.rgl-date').attr('min', RGLBookingSystem.today);
	$('.rgl-booking-form').each(function(){
		var form = $(this);
		initDatepicker(form);
		updateQuantityField(form);
		syncSelectors(form);
	});
});
JS
			);
		}


		private function rgl_truthy_shortcode_value( $value ) {
			$value = strtolower( trim( (string) $value ) );
			return in_array( $value, array( '1', 'true', 'yes', 'y', 'on', 'lock', 'locked' ), true );
		}

		private function normalize_lookup_key( $value ) {
			$value = trim( (string) $value );
			if ( '' === $value ) {
				return '';
			}

			/*
			 * Shortcode values should be forgiving.
			 * Example: a team member named "Henry Smith" should match:
			 * henry-smith, henry_smith, Henry Smith, or Henry-Smith.
			 */
			$value = str_replace( '_', ' ', $value );
			$value = sanitize_title( $value );
			$value = str_replace( '_', '-', $value );
			$value = preg_replace( '/-+/', '-', $value );

			return trim( $value, '-' );
		}

		private function resolve_item_id_from_shortcode_value( $value, $items ) {
			$value = trim( (string) $value );
			if ( '' === $value ) { return ''; }
			$sanitized_id = sanitize_key( $value );
			$lookup_key = $this->normalize_lookup_key( $value );
			foreach ( (array) $items as $item ) {
				$item_id = isset( $item['id'] ) ? sanitize_key( $item['id'] ) : '';
				$item_name = isset( $item['name'] ) ? (string) $item['name'] : '';
				if ( '' !== $item_id && $item_id === $sanitized_id ) { return $item_id; }
				if ( '' !== $item_id && $this->normalize_lookup_key( $item_id ) === $lookup_key ) { return $item_id; }
				if ( '' !== $item_name && $this->normalize_lookup_key( $item_name ) === $lookup_key ) { return $item_id; }
			}
			return '';
		}

		private function resolve_item_ids_from_shortcode_value( $value, $items ) {
			$raw_values = is_array( $value ) ? $value : preg_split( '/[,|;]/', (string) $value );
			$ids = array();
			foreach ( (array) $raw_values as $raw_value ) {
				$id = $this->resolve_item_id_from_shortcode_value( $raw_value, $items );
				if ( '' !== $id ) { $ids[] = $id; }
			}
			return array_values( array_unique( array_filter( $ids ) ) );
		}

		private function filter_items_by_allowed_ids( $items, $allowed_ids ) {
			$allowed_ids = array_values( array_unique( array_filter( array_map( 'sanitize_key', (array) $allowed_ids ) ) ) );
			if ( empty( $allowed_ids ) ) { return array_values( (array) $items ); }
			return array_values( array_filter( (array) $items, function ( $item ) use ( $allowed_ids ) {
				$item_id = isset( $item['id'] ) ? sanitize_key( $item['id'] ) : '';
				return '' !== $item_id && in_array( $item_id, $allowed_ids, true );
			} ) );
		}

		private function get_item_label_by_id( $items, $id, $fallback = 'Selected option' ) {
			$id = sanitize_key( $id );
			foreach ( (array) $items as $item ) {
				if ( isset( $item['id'] ) && sanitize_key( $item['id'] ) === $id ) {
					return isset( $item['name'] ) ? (string) $item['name'] : $fallback;
				}
			}
			return $fallback;
		}

		private function build_frontend_shortcode_context( $atts, $settings, $locations, $services, $staff_rows ) {
			$atts = shortcode_atts( array(
				'location_id' => '', 'location' => '', 'locations' => '',
				'service_id' => '', 'service' => '', 'services' => '',
				'staff_id' => '', 'staff' => '', 'team_member' => '', 'team_member_id' => '',
				'lock_location' => '', 'lock_service' => '', 'lock_staff' => '', 'lock' => '',
				'dynamic' => '',
			), (array) $atts, 'appt_booker' );

			/*
			 * Dynamic single-template behaviour:
			 * - A team page should preselect/lock the team member only.
			 * - A service page should preselect/lock the service only.
			 * - A location page should preselect/lock the location only.
			 *
			 * Previously lock="1" locked every selector, which made single team/service/location
			 * templates unusable because the other two selectors became read-only placeholders.
			 */
			$dynamic_keys = array();
			if ( $this->rgl_truthy_shortcode_value( $atts['dynamic'] ) ) {
				$dynamic_defaults = $this->get_current_elementor_content_defaults();
				foreach ( array( 'location_id', 'service_id', 'staff_id' ) as $dynamic_key ) {
					if ( empty( $atts[ $dynamic_key ] ) && ! empty( $dynamic_defaults[ $dynamic_key ] ) ) {
						$atts[ $dynamic_key ] = $dynamic_defaults[ $dynamic_key ];
						$dynamic_keys[] = $dynamic_key;
					}
				}
			}

			$location_value = '' !== (string) $atts['location_id'] ? $atts['location_id'] : $atts['location'];
			$service_value  = '' !== (string) $atts['service_id'] ? $atts['service_id'] : $atts['service'];
			$staff_value    = '' !== (string) $atts['staff_id'] ? $atts['staff_id'] : ( '' !== (string) $atts['team_member_id'] ? $atts['team_member_id'] : ( '' !== (string) $atts['staff'] ? $atts['staff'] : $atts['team_member'] ) );

			$preferred_location_id = $this->resolve_item_id_from_shortcode_value( $location_value, $locations );
			$preferred_service_id  = $this->resolve_item_id_from_shortcode_value( $service_value, $services );
			$preferred_staff_id    = $this->resolve_item_id_from_shortcode_value( $staff_value, $staff_rows );

			$lock_value = strtolower( trim( (string) $atts['lock'] ) );
			$legacy_lock_everything = in_array( $lock_value, array( 'all', 'everything', '*' ), true );
			$smart_lock = $this->rgl_truthy_shortcode_value( $atts['lock'] ) && ! $legacy_lock_everything;

			$lock_location = $legacy_lock_everything || $this->rgl_truthy_shortcode_value( $atts['lock_location'] ) || ( $smart_lock && '' !== $preferred_location_id );
			$lock_service  = $legacy_lock_everything || $this->rgl_truthy_shortcode_value( $atts['lock_service'] ) || ( $smart_lock && '' !== $preferred_service_id );
			$lock_staff    = $legacy_lock_everything || $this->rgl_truthy_shortcode_value( $atts['lock_staff'] ) || ( $smart_lock && '' !== $preferred_staff_id );

			/*
			 * Plural shortcode attributes are hard filters.
			 * Single shortcode/dynamic values are defaults unless their field is locked.
			 * This means [appt_booker dynamic="1" lock="1"] on a team page locks the team
			 * member but still lets the user choose any compatible location/service.
			 */
			$allowed_location_ids = $this->resolve_item_ids_from_shortcode_value( $atts['locations'], $locations );
			$allowed_service_ids  = $this->resolve_item_ids_from_shortcode_value( $atts['services'], $services );

			$staff_plural_source = '';
			if ( '' !== (string) $atts['staff'] && false !== strpos( (string) $atts['staff'], ',' ) ) {
				$staff_plural_source = $atts['staff'];
			} elseif ( '' !== (string) $atts['team_member'] && false !== strpos( (string) $atts['team_member'], ',' ) ) {
				$staff_plural_source = $atts['team_member'];
			}
			$allowed_staff_ids = $this->resolve_item_ids_from_shortcode_value( $staff_plural_source, $staff_rows );

			if ( $lock_location && '' !== $preferred_location_id && empty( $allowed_location_ids ) ) {
				$allowed_location_ids = array( $preferred_location_id );
			}
			if ( $lock_service && '' !== $preferred_service_id && empty( $allowed_service_ids ) ) {
				$allowed_service_ids = array( $preferred_service_id );
			}
			if ( $lock_staff && '' !== $preferred_staff_id && empty( $allowed_staff_ids ) ) {
				$allowed_staff_ids = array( $preferred_staff_id );
			}

			$team_spt_staff_filter_active = $lock_staff && '' !== $preferred_staff_id;
			if ( $team_spt_staff_filter_active ) {
				$staff_service_ids  = $this->get_related_service_ids_for_staff( $preferred_staff_id );
				$staff_location_ids = $this->get_related_location_ids_for_staff( $preferred_staff_id );

				$allowed_service_ids = empty( $allowed_service_ids ) ? $staff_service_ids : array_values( array_intersect( $allowed_service_ids, $staff_service_ids ) );
				$allowed_location_ids = empty( $allowed_location_ids ) ? $staff_location_ids : array_values( array_intersect( $allowed_location_ids, $staff_location_ids ) );
			}

			if ( '' === $preferred_location_id && ! empty( $allowed_location_ids ) ) { $preferred_location_id = $allowed_location_ids[0]; }
			if ( '' === $preferred_service_id && ! empty( $allowed_service_ids ) && ! $team_spt_staff_filter_active ) { $preferred_service_id = $allowed_service_ids[0]; }
			if ( '' === $preferred_staff_id && ! empty( $allowed_staff_ids ) ) { $preferred_staff_id = $allowed_staff_ids[0]; }

			$resolved_defaults = $this->resolve_booking_payload_selections( array(
				'location_id' => $preferred_location_id,
				'service_id'  => $preferred_service_id,
				'staff_id'    => $preferred_staff_id,
			), $settings );

			// If a default was configured but the configured value is not in
			// the allowed set, fall back to a valid value. But if no default
			// was set at all AND there is more than one allowed option, leave
			// the field empty so the customer makes a deliberate choice (and
			// so per-option descriptions / placeholders render correctly).
			if ( ! empty( $allowed_location_ids ) && ! in_array( $resolved_defaults['location_id'], $allowed_location_ids, true ) ) {
				$had_default = '' !== (string) $resolved_defaults['location_id'];
				if ( $had_default || count( $allowed_location_ids ) === 1 ) {
					$resolved_defaults['location_id'] = $allowed_location_ids[0];
				} else {
					$resolved_defaults['location_id'] = '';
				}
			}
			if ( ! empty( $allowed_service_ids ) && ! in_array( $resolved_defaults['service_id'], $allowed_service_ids, true ) ) {
				$had_default = '' !== (string) $resolved_defaults['service_id'];
				if ( $had_default || count( $allowed_service_ids ) === 1 ) {
					$resolved_defaults['service_id'] = $team_spt_staff_filter_active ? '' : $allowed_service_ids[0];
				} else {
					$resolved_defaults['service_id'] = '';
				}
			}
			if ( $team_spt_staff_filter_active && '' !== $resolved_defaults['service_id'] && ! in_array( $resolved_defaults['service_id'], $allowed_service_ids, true ) ) { $resolved_defaults['service_id'] = ''; }
			if ( ! empty( $allowed_staff_ids ) && ! in_array( $resolved_defaults['staff_id'], $allowed_staff_ids, true ) ) {
				$had_default = '' !== (string) $resolved_defaults['staff_id'];
				if ( $had_default || count( $allowed_staff_ids ) === 1 ) {
					$resolved_defaults['staff_id'] = $allowed_staff_ids[0];
				} else {
					$resolved_defaults['staff_id'] = '';
				}
			}

			return array(
				'defaults'             => $resolved_defaults,
				'allowed_location_ids' => $allowed_location_ids,
				'allowed_service_ids'  => $allowed_service_ids,
				'allowed_staff_ids'    => $allowed_staff_ids,
				'lock_location'        => $lock_location,
				'lock_service'         => $lock_service,
				'lock_staff'           => $lock_staff,
			);
		}
		public function render_shortcode( $atts = array(), $content = null, $shortcode_tag = '' ) {
			if ( ! $this->licence_booking_form_allowed() ) {
				return function_exists( 'tj_appt_licence_public_notice' ) ? tj_appt_licence_public_notice() : '';
			}
			$this->maybe_seed_defaults();
			$services   = $this->get_active_services();
			$locations  = $this->get_active_locations();
			$staff_rows = $this->get_active_staff();
			$settings   = $this->get_settings();
			$show_location_field = $this->is_field_enabled( $settings, 'show_location_field', true );
			$show_service_field  = $this->is_field_enabled( $settings, 'show_service_field', true );
			$show_staff_field    = $this->is_field_enabled( $settings, 'show_staff_field', true );
			$show_address_field  = $this->is_field_enabled( $settings, 'show_address_field', false );
			$label_service       = $this->get_form_label( $settings, 'label_service', 'Service' );
			$label_location      = $this->get_form_label( $settings, 'label_location', 'Location' );
			$label_staff         = $this->get_form_label( $settings, 'label_staff', 'Team Member' );
			$label_address       = $this->get_form_label( $settings, 'label_address', 'Address' );
			$label_phone         = $this->get_form_label( $settings, 'label_phone', 'Phone Number' );
			$placeholder_service = $this->select_placeholder_for_label( $label_service );
			$placeholder_location = $this->select_placeholder_for_label( $label_location );
			$placeholder_staff   = $this->select_placeholder_for_label( $label_staff );

			$shortcode_context = $this->build_frontend_shortcode_context( $atts, $settings, $locations, $services, $staff_rows );
			$resolved_defaults = $shortcode_context['defaults'];
			$locations_for_form = $this->filter_items_by_allowed_ids( $locations, $shortcode_context['allowed_location_ids'] );
			$services_for_form  = $this->filter_items_by_allowed_ids( $services, $shortcode_context['allowed_service_ids'] );
			$staff_for_form     = $this->filter_items_by_allowed_ids( $staff_rows, $shortcode_context['allowed_staff_ids'] );
			$allowed_location_attr = implode( ',', $shortcode_context['allowed_location_ids'] );
			$allowed_service_attr  = implode( ',', $shortcode_context['allowed_service_ids'] );
			$allowed_staff_attr    = implode( ',', $shortcode_context['allowed_staff_ids'] );
			$today = current_time( 'Y-m-d' );
			$form_layout = $this->normalize_form_layout_mode( isset( $settings['style_form_layout'] ) ? $settings['style_form_layout'] : '' );
			$service_first_waiting = $show_service_field && ! $shortcode_context['lock_service'] && empty( $resolved_defaults['service_id'] );

			// Unique per-render form ID so label-for / aria-describedby work
			// even when multiple booking forms appear on the same page.
			static $rgl_form_instance = 0;
			$rgl_form_instance++;
			$fid = 'rgl-bk-' . absint( $rgl_form_instance );

			// Resolve interest options for the currently selected service, if any.
			$initial_service        = '' !== $resolved_defaults['service_id'] ? $this->find_service( $resolved_defaults['service_id'] ) : false;
			$initial_interests      = $initial_service ? $this->get_service_interest_options( $initial_service ) : array();
			$initial_interests_head = $initial_service ? $this->get_service_interests_headline( $initial_service ) : '';
			$initial_interests_max  = $initial_service ? $this->get_service_interests_max( $initial_service ) : 0;

			ob_start();
			?>
			<div class="rgl-booking-form-wrap">
				<h3><?php echo esc_html( $settings['form_title'] ); ?></h3>
				<?php if ( ! empty( $settings['form_intro'] ) ) : ?><p class="rgl-booking-intro"><?php echo esc_html( $settings['form_intro'] ); ?></p><?php endif; ?>
				<form class="rgl-booking-form rgl-layout-<?php echo esc_attr( $form_layout ); ?>" id="<?php echo esc_attr( $fid ); ?>">
					<div class="rgl-booking-grid">

						<?php if ( $show_service_field && ! $shortcode_context['lock_service'] ) : ?>
						<p><label for="<?php echo esc_attr( $fid ); ?>-service"><?php echo esc_html( $label_service ); ?></label><br><select id="<?php echo esc_attr( $fid ); ?>-service" name="service_id" class="rgl-service" required data-default-value="<?php echo esc_attr( $resolved_defaults['service_id'] ); ?>" data-allowed-values="<?php echo esc_attr( $allowed_service_attr ); ?>"><option value=""><?php echo esc_html( $placeholder_service ); ?></option><?php foreach ( $services_for_form as $service ) : ?><option value="<?php echo esc_attr( $service['id'] ); ?>" data-customer-description="<?php echo esc_attr( isset( $service['customer_description'] ) ? $service['customer_description'] : '' ); ?>" data-message-placeholder="<?php echo esc_attr( isset( $service['message_placeholder'] ) ? $service['message_placeholder'] : '' ); ?>" data-quick-question-email="<?php echo esc_attr( isset( $service['quick_question_email'] ) ? $service['quick_question_email'] : '' ); ?>" <?php selected( $service['id'], $resolved_defaults['service_id'] ); ?>><?php echo esc_html( $service['name'] ); ?> (<?php echo esc_html( $service['duration'] ); ?> mins)</option><?php endforeach; ?></select></p>
						<?php else : ?>
						<input type="hidden" name="service_id" class="rgl-service" value="<?php echo esc_attr( $resolved_defaults['service_id'] ); ?>" data-default-value="<?php echo esc_attr( $resolved_defaults['service_id'] ); ?>" data-allowed-values="<?php echo esc_attr( $allowed_service_attr ); ?>">
						<?php if ( $show_service_field && $shortcode_context['lock_service'] ) : ?><p><strong><?php echo esc_html( $label_service ); ?></strong><br><?php echo esc_html( $this->get_item_label_by_id( $services, $resolved_defaults['service_id'], $placeholder_service ) ); ?></p><?php endif; ?>
						<?php endif; ?>

						<?php if ( $show_location_field && ! $shortcode_context['lock_location'] ) : ?>
						<p><label for="<?php echo esc_attr( $fid ); ?>-location"><?php echo esc_html( $label_location ); ?></label><br><select id="<?php echo esc_attr( $fid ); ?>-location" name="location_id" class="rgl-location" required data-default-value="<?php echo esc_attr( $resolved_defaults['location_id'] ); ?>" data-allowed-values="<?php echo esc_attr( $allowed_location_attr ); ?>" <?php disabled( $service_first_waiting ); ?>><option value="">Select a service first</option><?php foreach ( $locations_for_form as $location ) : ?><option value="<?php echo esc_attr( $location['id'] ); ?>" <?php selected( $location['id'], $resolved_defaults['location_id'] ); ?>><?php echo esc_html( $location['name'] ); ?></option><?php endforeach; ?></select></p>
						<?php else : ?>
						<input type="hidden" name="location_id" class="rgl-location" value="<?php echo esc_attr( $resolved_defaults['location_id'] ); ?>" data-default-value="<?php echo esc_attr( $resolved_defaults['location_id'] ); ?>" data-allowed-values="<?php echo esc_attr( $allowed_location_attr ); ?>">
						<?php if ( $show_location_field && $shortcode_context['lock_location'] ) : ?><p><strong><?php echo esc_html( $label_location ); ?></strong><br><?php echo esc_html( $this->get_item_label_by_id( $locations, $resolved_defaults['location_id'], $placeholder_location ) ); ?></p><?php endif; ?>
						<?php endif; ?>

						<?php if ( $show_staff_field && ! $shortcode_context['lock_staff'] ) : ?>
						<p class="rgl-staff-wrap"><label for="<?php echo esc_attr( $fid ); ?>-staff"><?php echo esc_html( $label_staff ); ?></label><br><select id="<?php echo esc_attr( $fid ); ?>-staff" name="staff_id" class="rgl-staff" data-default-value="<?php echo esc_attr( $resolved_defaults['staff_id'] ); ?>" data-allowed-values="<?php echo esc_attr( $allowed_staff_attr ); ?>" <?php disabled( $service_first_waiting ); ?>><option value="">Select a service first</option><?php foreach ( $staff_for_form as $staff_row ) : ?><option value="<?php echo esc_attr( $staff_row['id'] ); ?>" <?php selected( $staff_row['id'], $resolved_defaults['staff_id'] ); ?>><?php echo esc_html( $staff_row['name'] ); ?></option><?php endforeach; ?></select></p>
						<?php else : ?>
						<input type="hidden" name="staff_id" class="rgl-staff" value="<?php echo esc_attr( $resolved_defaults['staff_id'] ); ?>" data-default-value="<?php echo esc_attr( $resolved_defaults['staff_id'] ); ?>" data-allowed-values="<?php echo esc_attr( $allowed_staff_attr ); ?>">
						<?php if ( $show_staff_field && $shortcode_context['lock_staff'] ) : ?><p><strong><?php echo esc_html( $label_staff ); ?></strong><br><?php echo esc_html( $this->get_item_label_by_id( $staff_rows, $resolved_defaults['staff_id'], $placeholder_staff ) ); ?></p><?php endif; ?>
						<?php endif; ?>

						<?php if ( $show_service_field && ! $shortcode_context['lock_service'] ) : ?>
						<div class="rgl-full rgl-service-description-wrap">
							<div class="rgl-service-description" data-fid="<?php echo esc_attr( $fid ); ?>" data-placeholder="Select what you would like to discuss above. We will show what each consultation covers and how to get in touch." style="background:#faf6ed;border:1px solid #ece3d2;border-radius:8px;padding:14px 18px;font-size:14px;color:#6b6257;line-height:1.55;white-space:pre-line;min-height:64px;width:100%;box-sizing:border-box;">Select what you would like to discuss above. We will show what each consultation covers and how to get in touch.</div>
						</div>
						<?php endif; ?>

						<p><label for="<?php echo esc_attr( $fid ); ?>-date">Date</label><br><input type="text" id="<?php echo esc_attr( $fid ); ?>-date" class="rgl-date" required readonly autocomplete="off" placeholder="DD-MM-YYYY" aria-describedby="<?php echo esc_attr( $fid ); ?>-date-status"><input type="hidden" name="booking_date" class="rgl-date-alt" value=""><span id="<?php echo esc_attr( $fid ); ?>-date-status" class="rgl-date-status" aria-live="polite"></span></p>
						<p><label for="<?php echo esc_attr( $fid ); ?>-time">Available Time</label><br><select id="<?php echo esc_attr( $fid ); ?>-time" name="booking_time" class="rgl-time" required disabled aria-describedby="<?php echo esc_attr( $fid ); ?>-time-status"><option value="">Choose service and date</option></select><span id="<?php echo esc_attr( $fid ); ?>-time-status" class="rgl-time-status" aria-live="polite" style="display:block;margin-top:6px;font-size:13px;color:#6b6257;min-height:1.4em;"></span></p>
						<?php /* Number of Spaces is hidden for Titan - all bookings are 1-to-1 calls. The field is kept as a hidden input so server-side logic is unchanged. */ ?>
					<input type="hidden" id="<?php echo esc_attr( $fid ); ?>-quantity" name="booking_quantity" class="rgl-quantity" value="1">
					<span id="<?php echo esc_attr( $fid ); ?>-quantity-hint" class="rgl-quantity-hint rgl-sr-only"></span>

						<div class="rgl-interests-wrap<?php echo empty( $initial_interests ) ? ' is-empty' : ''; ?>" data-rgl-interests-wrap<?php echo empty( $initial_interests ) ? ' hidden' : ''; ?>>
							<?php if ( ! empty( $initial_interests ) ) : ?>
								<fieldset class="rgl-interests-fieldset" data-rgl-interests-fieldset data-max-selections="<?php echo esc_attr( $initial_interests_max ); ?>">
									<legend class="rgl-interests-legend" data-rgl-interests-legend><?php echo esc_html( '' !== $initial_interests_head ? $initial_interests_head : 'What would you like to discuss? (Optional, select any)' ); ?></legend>
									<div class="rgl-interests-options" data-rgl-interests-options>
										<?php foreach ( $initial_interests as $opt_index => $opt_label ) : ?>
											<label class="rgl-interest-option"><input type="checkbox" name="customer_interests[]" value="<?php echo esc_attr( $opt_label ); ?>"> <span><?php echo esc_html( $opt_label ); ?></span></label>
										<?php endforeach; ?>
									</div>
									<div class="rgl-interests-hint" data-rgl-interests-hint aria-live="polite"></div>
								</fieldset>
							<?php endif; ?>
						</div>

						<p><label for="<?php echo esc_attr( $fid ); ?>-name">Name</label><br><input type="text" id="<?php echo esc_attr( $fid ); ?>-name" name="customer_name" required autocomplete="name"></p>
						<p><label for="<?php echo esc_attr( $fid ); ?>-email">Email</label><br><input type="email" id="<?php echo esc_attr( $fid ); ?>-email" name="customer_email" required autocomplete="email"></p>
						<p><label for="<?php echo esc_attr( $fid ); ?>-phone"><?php echo esc_html( $label_phone ); ?></label><br><input type="tel" id="<?php echo esc_attr( $fid ); ?>-phone" name="customer_phone" required autocomplete="tel"></p>
						<?php if ( $show_address_field ) : ?><p class="rgl-full"><label for="<?php echo esc_attr( $fid ); ?>-address"><?php echo esc_html( $label_address ); ?></label><br><textarea id="<?php echo esc_attr( $fid ); ?>-address" name="customer_address" rows="3" autocomplete="street-address"></textarea></p><?php endif; ?>
						<p class="rgl-full"><label for="<?php echo esc_attr( $fid ); ?>-message"><?php echo esc_html( ! empty( $settings['customer_message_label'] ) ? $settings['customer_message_label'] : 'Anything you would like us to know before the call? (Optional)' ); ?></label><br><textarea id="<?php echo esc_attr( $fid ); ?>-message" name="customer_notes" rows="3" maxlength="2000" data-default-placeholder=""></textarea></p>
					</div>

					<?php
					$privacy_notice = $this->get_combined_booking_privacy_notice( $settings );
					if ( '' !== $privacy_notice ) :
						// Split into paragraphs on blank lines so multi-paragraph
						// notices render naturally.
						$privacy_paragraphs = preg_split( '/\n\s*\n/', $privacy_notice );
					?>
						<div class="rgl-booking-privacy">
							<?php foreach ( $privacy_paragraphs as $privacy_para ) : ?>
								<p><?php echo nl2br( esc_html( trim( $privacy_para ) ) ); ?></p>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php /* Honeypot: hidden from real users, off-screen rather than display:none so some bots still see it. */ ?>
					<div class="rgl-hp" aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;height:0;width:0;overflow:hidden;">
						<label>Company<input type="text" name="rgl_booking_company" tabindex="-1" autocomplete="off" value=""></label>
					</div>
					<?php
					// Signed render timestamp. The signature is what makes the
					// minimum time-to-submit check meaningful - see
					// request_is_abusive(). Both values are refreshed client-side
					// on load so a cached page does not submit stale ones.
					$form_credentials = $this->get_public_form_credentials();
					?>
					<input type="hidden" name="rgl_booking_form_loaded_at" class="rgl-form-loaded-at" value="<?php echo esc_attr( $form_credentials['loadedAt'] ); ?>">
					<input type="hidden" name="rgl_booking_form_sig" class="rgl-form-sig" value="<?php echo esc_attr( $form_credentials['signature'] ); ?>">
					<?php if ( ! empty( $settings['security_turnstile_enabled'] ) && ! empty( $settings['security_turnstile_site_key'] ) ) : ?>
						<div class="cf-turnstile" data-sitekey="<?php echo esc_attr( $settings['security_turnstile_site_key'] ); ?>"></div>
					<?php endif; ?>
					<p class="rgl-booking-submit-wrap"><button type="submit" class="rgl-booking-submit"><?php echo esc_html( $settings['style_button_text'] ); ?></button><button type="button" class="rgl-booking-reset"><?php echo esc_html( $settings['style_reset_button_text'] ); ?></button></p>
					<div class="rgl-booking-notice" role="status" aria-live="polite"></div>
				</form>
			</div>
			<?php
			return ob_get_clean();
		}

		public function ajax_get_staff() {
			check_ajax_referer( 'appt_booker_nonce', 'nonce' );
			$service_id  = sanitize_key( wp_unslash( isset( $_POST['service_id'] ) ? $_POST['service_id'] : '' ) );
			$location_id = sanitize_key( wp_unslash( isset( $_POST['location_id'] ) ? $_POST['location_id'] : '' ) );
			$service     = '' !== $service_id ? $this->find_service( $service_id ) : false;

			if ( '' !== $service_id && ! $service ) {
				wp_send_json_error( array( 'message' => 'Invalid service.' ) );
			}

			if ( $service ) {
				$staff_items = $this->get_allowed_staff_for_service_location( $service, $location_id );
			} else {
				$staff_items = $this->get_active_staff();
				if ( '' !== (string) $location_id ) {
					$staff_items = array_values(
						array_filter(
							$staff_items,
							function ( $staff ) use ( $location_id ) {
								return $this->staff_matches_location( $staff, $location_id );
							}
						)
					);
				}
			}

			$staff_rows = array();
			foreach ( $staff_items as $staff ) {
				$staff_rows[] = array(
					'id'   => $staff['id'],
					'name' => $staff['name'],
				);
			}

			wp_send_json_success(
				array(
					'staff'         => $staff_rows,
					'require_staff' => $service ? ! empty( $staff_rows ) : false,
				)
			);
		}

		public function ajax_get_services() {
			check_ajax_referer( 'appt_booker_nonce', 'nonce' );
			$location_id = sanitize_key( wp_unslash( isset( $_POST['location_id'] ) ? $_POST['location_id'] : '' ) );
			$staff_id    = sanitize_key( wp_unslash( isset( $_POST['staff_id'] ) ? $_POST['staff_id'] : '' ) );

			$service_rows = array();
			foreach ( $this->get_services_for_filters( $location_id, $staff_id ) as $service ) {
				$service_rows[] = array(
					'id'       => $service['id'],
					'name'     => $service['name'],
					'duration' => isset( $service['duration'] ) ? absint( $service['duration'] ) : 0,
				);
			}

			wp_send_json_success(
				array(
					'services' => $service_rows,
				)
			);
		}

		public function ajax_get_locations() {
			check_ajax_referer( 'appt_booker_nonce', 'nonce' );
			$service_id = sanitize_key( wp_unslash( isset( $_POST['service_id'] ) ? $_POST['service_id'] : '' ) );
			$staff_id   = sanitize_key( wp_unslash( isset( $_POST['staff_id'] ) ? $_POST['staff_id'] : '' ) );

			$service = '' !== $service_id ? $this->find_service( $service_id ) : false;
			$staff   = '' !== $staff_id ? $this->find_staff( $staff_id ) : false;

			if ( ! $service && ! $staff ) {
				$locations = $this->get_active_locations();
			} elseif ( $service && $staff ) {
				$locations = $this->get_shared_locations_for_service_and_staff( $service, $staff );
			} elseif ( $service ) {
				$locations = $this->get_locations_for_service( $service );
			} else {
				$locations = array_values(
					array_filter(
						$this->get_active_locations(),
						function ( $location ) use ( $staff ) {
							return $this->staff_matches_location( $staff, $location['id'] );
						}
					)
				);
			}

			$location_rows = array();
			foreach ( $locations as $location ) {
				$location_rows[] = array(
					'id'   => $location['id'],
					'name' => $location['name'],
				);
			}

			wp_send_json_success(
				array(
					'locations' => $location_rows,
				)
			);
		}


		private function sanitize_booking_id_list_from_request( $value ) {
			if ( is_array( $value ) ) {
				$raw_items = $value;
			} else {
				$raw_items = explode( ',', (string) $value );
			}

			$ids = array();
			foreach ( $raw_items as $item ) {
				$id = sanitize_key( wp_unslash( $item ) );
				if ( '' !== $id ) {
					$ids[] = $id;
				}
			}

			return array_values( array_unique( $ids ) );
		}

		private function filter_rows_by_allowed_ids( $rows, $allowed_ids ) {
			if ( empty( $allowed_ids ) ) {
				return array_values( $rows );
			}

			return array_values(
				array_filter(
					$rows,
					function ( $row ) use ( $allowed_ids ) {
						return ! empty( $row['id'] ) && in_array( $row['id'], $allowed_ids, true );
					}
				)
			);
		}

		private function get_available_date_candidates_for_filters( $location_id = '', $service_id = '', $staff_id = '', $allowed_location_ids = array(), $allowed_service_ids = array(), $allowed_staff_ids = array() ) {
			$locations = $this->filter_rows_by_allowed_ids( $this->get_active_locations(), $allowed_location_ids );
			$services  = $this->filter_rows_by_allowed_ids( $this->get_active_services(), $allowed_service_ids );
			$staff_rows = $this->filter_rows_by_allowed_ids( $this->get_active_staff(), $allowed_staff_ids );

			if ( '' !== (string) $location_id ) {
				$locations = array_values( array_filter( $locations, function ( $location ) use ( $location_id ) { return isset( $location['id'] ) && $location['id'] === $location_id; } ) );
			}
			if ( '' !== (string) $service_id ) {
				$services = array_values( array_filter( $services, function ( $service ) use ( $service_id ) { return isset( $service['id'] ) && $service['id'] === $service_id; } ) );
			}
			if ( '' !== (string) $staff_id ) {
				$staff_rows = array_values( array_filter( $staff_rows, function ( $staff ) use ( $staff_id ) { return isset( $staff['id'] ) && $staff['id'] === $staff_id; } ) );
			}

			$candidates = array();
			$max_days_ahead = 1;

			foreach ( $services as $service ) {
				foreach ( $locations as $location ) {
					if ( ! $this->service_matches_location( $service, $location['id'] ) ) {
						continue;
					}

					$service_staff_ids = isset( $service['staff_ids'] ) && is_array( $service['staff_ids'] ) ? $service['staff_ids'] : array();
					$matching_staff = array_values(
						array_filter(
							$staff_rows,
							function ( $staff ) use ( $service, $location ) {
								return $this->staff_can_deliver_service_at_location( $service, $staff, $location['id'] );
							}
						)
					);

					if ( empty( $service_staff_ids ) ) {
						$rules = $this->resolve_effective_rules( $service, false );
						$max_days_ahead = max( $max_days_ahead, absint( $rules['max_days_ahead'] ) );
						$candidates[] = array(
							'location' => $location,
							'service'  => $service,
							'staff'    => false,
						);
						continue;
					}

					foreach ( $matching_staff as $staff ) {
						$rules = $this->resolve_effective_rules( $service, $staff );
						$max_days_ahead = max( $max_days_ahead, absint( $rules['max_days_ahead'] ) );
						$candidates[] = array(
							'location' => $location,
							'service'  => $service,
							'staff'    => $staff,
						);
					}
				}
			}

			return array(
				'candidates'     => $candidates,
				'max_days_ahead' => max( 1, absint( $max_days_ahead ) ),
			);
		}

		private function build_available_dates_for_filters( $location_id = '', $service_id = '', $staff_id = '', $allowed_location_ids = array(), $allowed_service_ids = array(), $allowed_staff_ids = array(), $exclude_booking_id = 0 ) {
			$candidate_data = $this->get_available_date_candidates_for_filters( $location_id, $service_id, $staff_id, $allowed_location_ids, $allowed_service_ids, $allowed_staff_ids );
			$candidates = $candidate_data['candidates'];
			if ( empty( $candidates ) ) {
				return array();
			}

			$today = current_time( 'Y-m-d' );
			$max_days = max( 1, absint( $candidate_data['max_days_ahead'] ) );

			// Clamp the walk to the actual booking window. max_days_ahead
			// defaults to 60, but latest_bookable_date() usually closes the
			// window about two weeks out, so the tail of that loop was building
			// slots for dates that date_is_within_limits_for_rules() was always
			// going to reject - pure wasted work on an unauthenticated endpoint.
			$latest = $this->latest_bookable_date();
			if ( $this->is_valid_date( $latest ) ) {
				$window_days = (int) floor( ( strtotime( $latest ) - strtotime( $today ) ) / DAY_IN_SECONDS );
				$max_days    = max( 1, min( $max_days, $window_days ) );
			}

			$dates = array();

			for ( $offset = 0; $offset <= $max_days; $offset++ ) {
				$date = gmdate( 'Y-m-d', strtotime( '+' . $offset . ' days', strtotime( $today ) ) );
				// Cheap global gate before touching the database at all: weekends,
				// bank holidays and out-of-window dates can never yield a slot.
				if ( ! $this->date_is_globally_offerable( $date ) ) {
					continue;
				}
				foreach ( $candidates as $candidate ) {
					$slots = $this->build_slots( $candidate['service'], $candidate['staff'], $date, absint( $exclude_booking_id ), $candidate['location'] );
					if ( ! empty( $slots ) ) {
						$dates[] = $date;
						break;
					}
				}
			}

			return array_values( array_unique( $dates ) );
		}


		public function ajax_get_available_dates() {
			check_ajax_referer( 'appt_booker_nonce', 'nonce' );

			$location_id = sanitize_key( wp_unslash( isset( $_POST['location_id'] ) ? $_POST['location_id'] : '' ) );
			$service_id  = sanitize_key( wp_unslash( isset( $_POST['service_id'] ) ? $_POST['service_id'] : '' ) );
			$staff_id    = sanitize_key( wp_unslash( isset( $_POST['staff_id'] ) ? $_POST['staff_id'] : '' ) );

			$allowed_location_ids = $this->sanitize_booking_id_list_from_request( isset( $_POST['allowed_location_ids'] ) ? $_POST['allowed_location_ids'] : '' );
			$allowed_service_ids  = $this->sanitize_booking_id_list_from_request( isset( $_POST['allowed_service_ids'] ) ? $_POST['allowed_service_ids'] : '' );
			$allowed_staff_ids    = $this->sanitize_booking_id_list_from_request( isset( $_POST['allowed_staff_ids'] ) ? $_POST['allowed_staff_ids'] : '' );

			if ( '' !== $location_id && ! $this->find_location( $location_id ) ) {
				wp_send_json_error( array( 'message' => 'Invalid location.' ) );
			}
			if ( '' !== $service_id && ! $this->find_service( $service_id ) ) {
				wp_send_json_error( array( 'message' => 'Invalid service.' ) );
			}
			if ( '' !== $staff_id && ! $this->find_staff( $staff_id ) ) {
				wp_send_json_error( array( 'message' => 'Invalid team member.' ) );
			}

			// Short-lived cache. This endpoint is public (wp_ajax_nopriv) and its
			// nonce is printed on every page carrying the booking form, so it is
			// effectively unauthenticated. Uncached, one call walks the booking
			// window building slots for every service/location/staff combination,
			// which runs to hundreds of queries - a cheap way for anyone to load
			// the database. Availability moves slowly enough that a minute of
			// staleness in the datepicker is invisible: ajax_get_slots() is
			// authoritative per date, and ajax_submit() re-validates the chosen
			// slot under the write lock before inserting.
			$cache_key = 'rgl_bk_dates_' . md5(
				implode(
					'|',
					array(
						$location_id,
						$service_id,
						$staff_id,
						implode( ',', $allowed_location_ids ),
						implode( ',', $allowed_service_ids ),
						implode( ',', $allowed_staff_ids ),
						current_time( 'Y-m-d-H' ),
					)
				)
			);

			$dates = get_transient( $cache_key );
			if ( false === $dates ) {
				$dates = $this->build_available_dates_for_filters( $location_id, $service_id, $staff_id, $allowed_location_ids, $allowed_service_ids, $allowed_staff_ids );
				set_transient( $cache_key, $dates, MINUTE_IN_SECONDS );
			}

			wp_send_json_success(
				array(
					'dates' => $dates,
				)
			);
		}

		public function ajax_get_slots() {
			check_ajax_referer( 'appt_booker_nonce', 'nonce' );
			$location_id = sanitize_key( wp_unslash( isset( $_POST['location_id'] ) ? $_POST['location_id'] : '' ) );
			$service_id  = sanitize_key( wp_unslash( isset( $_POST['service_id'] ) ? $_POST['service_id'] : '' ) );
			$staff_id    = sanitize_key( wp_unslash( isset( $_POST['staff_id'] ) ? $_POST['staff_id'] : '' ) );
			$date        = sanitize_text_field( wp_unslash( isset( $_POST['booking_date'] ) ? $_POST['booking_date'] : '' ) );
			$exclude_booking_id = absint( wp_unslash( isset( $_POST['exclude_booking_id'] ) ? $_POST['exclude_booking_id'] : 0 ) );

			$allowed_location_ids = $this->sanitize_booking_id_list_from_request( isset( $_POST['allowed_location_ids'] ) ? $_POST['allowed_location_ids'] : '' );
			$allowed_service_ids  = $this->sanitize_booking_id_list_from_request( isset( $_POST['allowed_service_ids'] ) ? $_POST['allowed_service_ids'] : '' );
			$allowed_staff_ids    = $this->sanitize_booking_id_list_from_request( isset( $_POST['allowed_staff_ids'] ) ? $_POST['allowed_staff_ids'] : '' );

			// Stage 4: slot lookup must be based on the final filtered combination.
			// Do not auto-resolve an empty service/location/team here, otherwise the frontend can show times for a default
			// combination that the customer has not actually selected.
			if ( '' === $location_id || '' === $service_id || ! $this->is_valid_date( $date ) ) {
				wp_send_json_success( array( 'slots' => array() ) );
			}

			if ( ! empty( $allowed_location_ids ) && ! in_array( $location_id, $allowed_location_ids, true ) ) {
				wp_send_json_success( array( 'slots' => array() ) );
			}
			if ( ! empty( $allowed_service_ids ) && ! in_array( $service_id, $allowed_service_ids, true ) ) {
				wp_send_json_success( array( 'slots' => array() ) );
			}
			if ( '' !== $staff_id && ! empty( $allowed_staff_ids ) && ! in_array( $staff_id, $allowed_staff_ids, true ) ) {
				wp_send_json_success( array( 'slots' => array() ) );
			}

			$location = $this->find_location( $location_id );
			$service  = $this->find_service( $service_id );
			$staff    = '' !== $staff_id ? $this->find_staff( $staff_id ) : false;

			if ( ! $location || ! $service ) {
				wp_send_json_success( array( 'slots' => array() ) );
			}
			if ( '' !== $staff_id && ! $staff ) {
				wp_send_json_success( array( 'slots' => array() ) );
			}
			if ( ! $this->service_matches_location( $service, $location_id ) ) {
				wp_send_json_success( array( 'slots' => array() ) );
			}
			if ( $staff && ( ! in_array( $staff_id, isset( $service['staff_ids'] ) && is_array( $service['staff_ids'] ) ? $service['staff_ids'] : array(), true ) || ! $this->staff_matches_location( $staff, $location_id ) ) ) {
				wp_send_json_success( array( 'slots' => array() ) );
			}
			if ( '' === $staff_id && ! empty( $service['staff_ids'] ) ) {
				wp_send_json_success( array( 'slots' => array() ) );
			}

			wp_send_json_success( array( 'slots' => $this->build_slots( $service, $staff, $date, $exclude_booking_id, $location ) ) );
		}
		public function ajax_get_interests() {
			check_ajax_referer( 'appt_booker_nonce', 'nonce' );
			$service_id = sanitize_key( wp_unslash( isset( $_POST['service_id'] ) ? $_POST['service_id'] : '' ) );
			if ( '' === $service_id ) {
				wp_send_json_success( array( 'enabled' => false, 'headline' => '', 'options' => array(), 'max' => 0 ) );
			}
			$service = $this->find_service( $service_id );
			if ( ! $service ) {
				wp_send_json_success( array( 'enabled' => false, 'headline' => '', 'options' => array(), 'max' => 0 ) );
			}
			$options = $this->get_service_interest_options( $service );
			wp_send_json_success(
				array(
					'enabled'  => ! empty( $options ),
					'headline' => $this->get_service_interests_headline( $service ),
					'options'  => array_values( $options ),
					'max'      => $this->get_service_interests_max( $service ),
				)
			);
		}

		public function ajax_submit() {
			check_ajax_referer( 'appt_booker_nonce', 'nonce' );
			if ( ! $this->licence_booking_form_allowed() ) {
				wp_send_json_error( array( 'message' => 'This booking system is not currently licensed to accept bookings.' ) );
			}

			// Anti-abuse checks (honeypot, time-to-submit, IP throttle, optional Turnstile).
			$abuse_reason = '';
			if ( $this->request_is_abusive( $abuse_reason ) ) {
				wp_send_json_error( array( 'message' => $abuse_reason ) );
			}

			if ( ! $this->table_exists() ) {
				wp_send_json_error( array( 'message' => 'Booking table does not exist yet. Open the admin page and create it first.' ) );
			}

			$settings      = $this->get_settings();
			$resolved_post  = $this->resolve_booking_payload_selections(
				array(
					'location_id' => isset( $_POST['location_id'] ) ? wp_unslash( $_POST['location_id'] ) : '',
					'service_id'  => isset( $_POST['service_id'] ) ? wp_unslash( $_POST['service_id'] ) : '',
					'staff_id'    => isset( $_POST['staff_id'] ) ? wp_unslash( $_POST['staff_id'] ) : '',
				),
				$settings
			);
			$location_id   = $resolved_post['location_id'];
			$service_id    = $resolved_post['service_id'];
			$staff_id      = $resolved_post['staff_id'];
			$name          = sanitize_text_field( wp_unslash( isset( $_POST['customer_name'] ) ? $_POST['customer_name'] : '' ) );
			$email         = sanitize_email( wp_unslash( isset( $_POST['customer_email'] ) ? $_POST['customer_email'] : '' ) );
			$phone         = sanitize_text_field( wp_unslash( isset( $_POST['customer_phone'] ) ? $_POST['customer_phone'] : '' ) );
			$address       = sanitize_textarea_field( wp_unslash( isset( $_POST['customer_address'] ) ? $_POST['customer_address'] : '' ) );
			$customer_message = sanitize_textarea_field( wp_unslash( isset( $_POST['customer_notes'] ) ? $_POST['customer_notes'] : '' ) );
			$booking_date  = sanitize_text_field( wp_unslash( isset( $_POST['booking_date'] ) ? $_POST['booking_date'] : '' ) );
			$booking_time  = sanitize_text_field( wp_unslash( isset( $_POST['booking_time'] ) ? $_POST['booking_time'] : '' ) );
			$booking_quantity = max( 1, absint( wp_unslash( isset( $_POST['booking_quantity'] ) ? $_POST['booking_quantity'] : 1 ) ) );
			if ( $this->licence_booking_date_restricted( $booking_date ) ) {
				$booking_quantity = 1;
			}

			// Customer interests (added): collected here, validated against the
			// service's configured options once the service is resolved below.
			$customer_interests_raw = array();
			if ( isset( $_POST['customer_interests'] ) ) {
				$ci_in = wp_unslash( $_POST['customer_interests'] );
				if ( is_array( $ci_in ) ) {
					foreach ( $ci_in as $val ) {
						$val = sanitize_text_field( $val );
						if ( '' !== $val ) { $customer_interests_raw[] = $val; }
					}
				} elseif ( is_string( $ci_in ) && '' !== $ci_in ) {
					$customer_interests_raw[] = sanitize_text_field( $ci_in );
				}
			}
			$customer_interests_raw = array_values( array_unique( $customer_interests_raw ) );

			if ( '' === $location_id || '' === $service_id || '' === $name || '' === $email || '' === $phone || '' === $booking_date || '' === $booking_time ) {
				wp_send_json_error( array( 'message' => 'Please complete all required fields.' ) );
			}
			if ( ! is_email( $email ) ) {
				wp_send_json_error( array( 'message' => 'Please enter a valid email address.' ) );
			}
			// Per-email rate limit (added). Stops the same email creating an
			// unreasonable number of bookings in a short window - a defence
			// against competitor lock-out attempts and naive spam.
			// Blocked-email list check (added). Same generic message as the
			// rate limit so we don't telegraph the block back to the sender.
			if ( $this->email_is_blocked( $email ) ) {
				wp_send_json_error( array( 'message' => 'We are unable to accept this booking online. Please contact us if you need help with a booking.' ) );
			}
			if ( $this->email_rate_limit_exceeded( $email ) ) {
				wp_send_json_error( array( 'message' => 'We are unable to accept this booking online. Please contact us if you need help with a booking.' ) );
			}
			if ( ! $this->is_valid_date( $booking_date ) || ! $this->is_valid_time( $booking_time ) ) {
				wp_send_json_error( array( 'message' => 'Please choose a valid date and time.' ) );
			}

			$location = $this->find_location( $location_id );
			$service  = $this->find_service( $service_id );
			$staff    = '' !== $staff_id ? $this->find_staff( $staff_id ) : false;

			if ( ! $location ) {
				wp_send_json_error( array( 'message' => 'Invalid location.' ) );
			}
			if ( ! $service ) {
				wp_send_json_error( array( 'message' => 'Invalid service.' ) );
			}
			if ( ! $this->service_matches_location( $service, $location['id'] ) ) {
				wp_send_json_error( array( 'message' => 'That service is not linked to that location.' ) );
			}
			if ( $staff && ! in_array( $staff['id'], $service['staff_ids'], true ) ) {
				wp_send_json_error( array( 'message' => 'That team member is not linked to that service.' ) );
			}
			if ( $staff && ! $this->staff_matches_location( $staff, $location['id'] ) ) {
				wp_send_json_error( array( 'message' => 'That team member is not linked to that location.' ) );
			}
			if ( ! $staff && ! empty( $service['staff_ids'] ) ) {
				wp_send_json_error( array( 'message' => 'Please choose a team member for this service.' ) );
			}

			if ( ! $this->acquire_booking_write_lock() ) {
				wp_send_json_error( array( 'message' => 'The booking system is busy. Please try again.' ) );
			}
			register_shutdown_function( function () { $this->release_booking_write_lock(); } );

			$available_slots = $this->build_slots( $service, $staff, $booking_date, 0, $location );
			$selected_slot = null;
			foreach ( $available_slots as $slot ) {
				if ( isset( $slot['value'] ) && $booking_time === $slot['value'] ) {
					$selected_slot = $slot;
					break;
				}
			}
			if ( ! $selected_slot ) {
				wp_send_json_error( array( 'message' => 'That time is not available for the selected options.' ) );
			}
			$spaces_remaining = max( 1, absint( isset( $selected_slot['spaces_remaining'] ) ? $selected_slot['spaces_remaining'] : 1 ) );
			if ( $booking_quantity > $spaces_remaining ) {
				wp_send_json_error( array( 'message' => sprintf( 'Only %d space%s remain for that time.', $spaces_remaining, 1 === $spaces_remaining ? '' : 's' ) ) );
			}

			$requires_no_show_review = $this->email_requires_no_show_review( $email );
			$booking_status = ( ! empty( $settings['manual_approval_enabled'] ) || $requires_no_show_review ) ? self::STATUS_PENDING_APPROVAL : self::STATUS_CONFIRMED;

			$rules         = $this->resolve_effective_rules( $service, $staff );
			$start_minutes = $this->time_to_minutes( $booking_time );
			$end_minutes   = $start_minutes + absint( $rules['duration'] );
			$end_time      = $this->minutes_to_time( $end_minutes );
			$reference     = $this->generate_booking_reference();

			// Validate customer interests against the service's allowed options.
			// Anything not in the allow-list is silently dropped (don't fail the booking).
			$allowed_interest_options = $this->get_service_interest_options( $service );
			$customer_interests_clean = array();
			if ( ! empty( $allowed_interest_options ) ) {
				$allowed_lc = array_map( 'strtolower', $allowed_interest_options );
				$allowed_map = array_combine( $allowed_lc, $allowed_interest_options );
				foreach ( $customer_interests_raw as $sel ) {
					$key = strtolower( $sel );
					if ( isset( $allowed_map[ $key ] ) && ! in_array( $allowed_map[ $key ], $customer_interests_clean, true ) ) {
						$customer_interests_clean[] = $allowed_map[ $key ];
					}
				}
				$max_sel = $this->get_service_interests_max( $service );
				if ( $max_sel > 0 && count( $customer_interests_clean ) > $max_sel ) {
					wp_send_json_error( array( 'message' => sprintf( 'Please select no more than %d option%s.', $max_sel, 1 === $max_sel ? '' : 's' ) ) );
				}
			}
			$customer_interests_stored = implode( ', ', $customer_interests_clean );

			global $wpdb;
			$this->ensure_booking_quantity_column();
			$this->ensure_customer_interests_column();
			$inserted = $wpdb->insert(
				$this->table,
				array(
					'created_at'                       => current_time( 'mysql' ),
					'booking_reference'                => $reference,
					'spaces_booked'                    => $booking_quantity,
					'location_id'                      => $location['id'],
					'location_name'                    => $location['name'],
					'service_id'                       => $service['id'],
					'service_name'                     => $service['name'],
					'staff_id'                         => $staff ? $staff['id'] : '',
					'staff_name'                       => $staff ? $staff['name'] : '',
					'customer_name'                    => $name,
					'customer_email'                   => $email,
					'customer_phone'                   => $phone,
					'customer_address'                 => $address,
					'customer_notes'                   => $customer_message,
					'customer_interests'               => $customer_interests_stored,
					'admin_notes'                      => '',
					'booking_date'                     => $booking_date,
					'booking_time'                     => $booking_time . ':00',
					'booking_end_time'                 => $end_time . ':00',
					'booking_status'                   => $booking_status,
				),
			);

			if ( false === $inserted ) {
				$this->release_booking_write_lock();
				wp_send_json_error( array( 'message' => 'Could not save booking.' ) );
			}

			$new_booking = $this->get_booking_by_id( (int) $wpdb->insert_id );
			$this->mark_restricted_date_consumed( $booking_date, (int) $wpdb->insert_id );
			$this->release_booking_write_lock();

			if ( $new_booking ) {
				$this->send_booking_notifications(
					self::STATUS_PENDING_APPROVAL === $booking_status ? 'booking_pending_approval' : 'booking_made',
					$new_booking
				);
			}

			$message     = trim( $settings['success_message'] ) . ' Reference: ' . $reference;
			$summary_url = $new_booking ? $this->get_booking_summary_url( $new_booking ) : '';
			if ( '' !== $summary_url ) {
				wp_send_json_success( array( 'message' => $message . ' Redirecting to your booking summary.', 'redirect_url' => $summary_url ) );
			}
			wp_send_json_success( array( 'message' => $message ) );
		}

		public static function get_elementor_image_dynamic_tag_definitions() {
			$definitions = array(
				array(
					'class'   => 'Appt_Booker_Elementor_Location_Main_Image_Tag',
					'name'    => 'location-main-image',
					'title'   => 'Location Main Image',
					'id_key'  => 'appt_location_image_id',
					'url_key' => 'appt_location_image_url',
				),
				array(
					'class'   => 'Appt_Booker_Elementor_Staff_Main_Image_Tag',
					'name'    => 'staff-main-image',
					'title'   => 'Staff Main Image',
					'id_key'  => 'appt_staff_image_id',
					'url_key' => 'appt_staff_image_url',
				),
				array(
					'class'   => 'Appt_Booker_Elementor_Service_Main_Image_Tag',
					'name'    => 'service-main-image',
					'title'   => 'Service Main Image',
					'id_key'  => 'appt_service_image_id',
					'url_key' => 'appt_service_image_url',
				),
			);

			$groups = array(
				'location' => 'Location',
				'staff'    => 'Staff',
				'service'  => 'Service',
			);

			foreach ( $groups as $prefix => $label ) {
				for ( $i = 1; $i <= self::EXTRA_IMAGE_SLOTS; $i++ ) {
					$definitions[] = array(
						'class'   => 'Appt_Booker_Elementor_' . ucfirst( $prefix ) . '_Extra_Image_' . $i . '_Tag',
						'name'    => $prefix . '-extra-image-' . $i,
						'title'   => $label . ' Extra Image ' . $i,
						'id_key'  => 'appt_' . $prefix . '_extra_image_' . $i . '_id',
						'url_key' => 'appt_' . $prefix . '_extra_image_' . $i . '_url',
					);
				}
			}

			return $definitions;
		}


		public function register_elementor_dynamic_image_tags( $dynamic_tags ) {
			static $registered = false;

			if ( $registered || ! is_object( $dynamic_tags ) || ! class_exists( '\\Elementor\\Core\\DynamicTags\\Data_Tag' ) ) {
				return;
			}

			if ( function_exists( 'appt_booker_define_elementor_image_dynamic_tag_classes' ) ) {
				appt_booker_define_elementor_image_dynamic_tag_classes();
			}

			if ( ! class_exists( 'Appt_Booker_Elementor_Image_Dynamic_Tag_Base' ) ) {
				return;
			}

			if ( method_exists( $dynamic_tags, 'register_group' ) ) {
				$dynamic_tags->register_group(
					'appt-booker',
					array(
						'title' => 'Appt Booker',
					)
				);
			} elseif ( method_exists( $dynamic_tags, 'add_group' ) ) {
				$dynamic_tags->add_group(
					'appt-booker',
					array(
						'title' => 'Appt Booker',
					)
				);
			}

			foreach ( self::get_elementor_image_dynamic_tag_definitions() as $definition ) {
				// Use the literal internal class name only after confirming it contains
				// class-safe characters.
				$class_name = isset( $definition['class'] ) && preg_match( '/^[A-Za-z_][A-Za-z0-9_]*$/', $definition['class'] ) ? $definition['class'] : '';
				if ( '' === $class_name || ! class_exists( $class_name ) ) {
					continue;
				}
				$tag = new $class_name();

				if ( method_exists( $dynamic_tags, 'register' ) ) {
					$dynamic_tags->register( $tag );
				} elseif ( method_exists( $dynamic_tags, 'register_tag' ) ) {
					$dynamic_tags->register_tag( $tag );
				}
			}

			$registered = true;
		}

		private function render_admin_extra_image_pickers( $collection, $index, $row ) {
			$collection = sanitize_key( $collection );
			$index      = absint( $index );
			if ( ! is_array( $row ) ) {
				$row = array();
			}

			for ( $i = 1; $i <= self::EXTRA_IMAGE_SLOTS; $i++ ) {
				$id_key    = 'extra_image_' . $i . '_id';
				$url_key   = 'extra_image_' . $i . '_url';
				$image_id  = isset( $row[ $id_key ] ) ? absint( $row[ $id_key ] ) : 0;
				$image_url = isset( $row[ $url_key ] ) ? esc_url_raw( $row[ $url_key ] ) : '';
				$thumb_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : $image_url;
				?>
				<div class="rgl-image-picker rgl-extra-image-picker">
					<label>Extra Image <?php echo esc_html( $i ); ?><br>
						<input type="hidden" class="rgl-image-id" data-name="<?php echo esc_attr( $collection ); ?>[%d][<?php echo esc_attr( $id_key ); ?>]" name="<?php echo esc_attr( $collection ); ?>[<?php echo esc_attr( $index ); ?>][<?php echo esc_attr( $id_key ); ?>]" value="<?php echo esc_attr( $image_id ); ?>">
						<input type="hidden" class="rgl-image-url" data-name="<?php echo esc_attr( $collection ); ?>[%d][<?php echo esc_attr( $url_key ); ?>]" name="<?php echo esc_attr( $collection ); ?>[<?php echo esc_attr( $index ); ?>][<?php echo esc_attr( $url_key ); ?>]" value="<?php echo esc_attr( $image_url ); ?>">
					</label>
					<div class="rgl-image-preview"><?php if ( $thumb_url ) : ?><img src="<?php echo esc_url( $thumb_url ); ?>" alt="" style="max-width:120px;height:auto;display:block;margin-bottom:8px;"><?php endif; ?></div>
					<button class="button rgl-select-image" type="button">Choose Image</button>
					<button class="button rgl-remove-image" type="button">Remove</button>
				</div>
				<?php
			}
		}

		private function render_admin_extra_image_pickers_template( $collection ) {
			$collection = sanitize_key( $collection );
			for ( $i = 1; $i <= self::EXTRA_IMAGE_SLOTS; $i++ ) {
				$id_key  = 'extra_image_' . $i . '_id';
				$url_key = 'extra_image_' . $i . '_url';
				?>
				<div class="rgl-image-picker rgl-extra-image-picker">
					<label>Extra Image <?php echo esc_html( $i ); ?><br>
						<input type="hidden" class="rgl-image-id" data-name="<?php echo esc_attr( $collection ); ?>[%d][<?php echo esc_attr( $id_key ); ?>]" name="<?php echo esc_attr( $collection ); ?>[__INDEX__][<?php echo esc_attr( $id_key ); ?>]" value="0">
						<input type="hidden" class="rgl-image-url" data-name="<?php echo esc_attr( $collection ); ?>[%d][<?php echo esc_attr( $url_key ); ?>]" name="<?php echo esc_attr( $collection ); ?>[__INDEX__][<?php echo esc_attr( $url_key ); ?>]" value="">
					</label>
					<div class="rgl-image-preview"></div>
					<button class="button rgl-select-image" type="button">Choose Image</button>
					<button class="button rgl-remove-image" type="button">Remove</button>
				</div>
				<?php
			}
		}

		public function render_admin_page() {
			$this->maybe_seed_defaults();

			$installed = $this->table_exists();
			$notice    = sanitize_text_field( wp_unslash( isset( $_GET['notice'] ) ? $_GET['notice'] : '' ) );
			$message   = sanitize_text_field( rawurldecode( wp_unslash( isset( $_GET['message'] ) ? $_GET['message'] : '' ) ) );
			$tab       = sanitize_key( isset( $_GET['tab'] ) ? $_GET['tab'] : 'bookings' );
			$bookings   = $this->get_admin_bookings();
			$locations  = $this->get_all_locations();
			$staff      = $this->get_all_staff();
			$services   = $this->get_all_services();
			$days       = $this->days_map();
			$settings  = $this->get_settings();
			$notice_map = array(
				'installed' => 'Booking table updated successfully.',
				'settings_saved' => 'Settings saved.',
				'taxonomy_fields_saved' => 'Taxonomy fields saved.',
				'locations_saved' => 'Locations saved.',
				'staff_saved' => 'Staff saved.',
				'services_saved' => 'Services saved.',
				'booking_added' => 'Booking added.',
				'booking_saved' => 'Booking saved.',
				'booking_deleted' => 'Booking deleted.',
				'booking_duplicated' => 'Booking duplicated.',
				'booking_marked_spam' => 'Booking cancelled, customer notified, and email address blocked from future bookings.',
				'future_bookings_blocked' => '' !== $message ? $message : 'Future online bookings blocked.',
				'future_bookings_unblocked' => '' !== $message ? $message : 'Future online bookings allowed again.',
				'confirmation_email_sent' => '' !== $message ? $message : 'Confirmation email resent.',
				'confirmation_email_error' => '' !== $message ? $message : 'Confirmation email could not be resent.',
				'reminder_email_sent' => '' !== $message ? $message : 'Reminder email sent.',
				'reminder_email_error' => '' !== $message ? $message : 'Reminder email could not be sent.',
				'booking_message_sent' => '' !== $message ? $message : 'Message sent.',
				'booking_message_error' => '' !== $message ? $message : 'Message could not be sent.',
				'booking_error' => '' !== $message ? $message : 'The booking could not be saved. Please choose a valid available slot.',
			);
			$notice_text = isset( $notice_map[ $notice ] ) ? $notice_map[ $notice ] : '';
			?>
			<div class="wrap" data-rgl-admin-booking="1" data-rgl-admin-nonce="<?php echo esc_attr( wp_create_nonce( 'appt_booker_nonce' ) ); ?>">
				<h1>Appt-Booker V.<?php echo esc_html( self::VERSION ); ?></h1>
				<p style="margin:4px 0 12px;color:#646970;">Built by Jason Beer of Titan Jewellery.</p>

				<?php if ( $notice_text ) : ?>
					<div class="notice <?php echo in_array( $notice, array( 'booking_error', 'booking_message_error', 'reminder_email_error' ), true ) ? 'notice-error' : 'notice-success'; ?> is-dismissible"><p><?php echo esc_html( $notice_text ); ?></p></div>
				<?php endif; ?>

				<p>Table exists: <strong><?php echo $installed ? 'Yes' : 'No'; ?></strong></p>

				<form method="post" style="margin:16px 0;">
					<?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?>
					<input type="hidden" name="rgl_booking_action" value="run_install">
					<button class="button button-primary" type="submit">Create Booking Table</button>
				</form>

				<h2 class="nav-tab-wrapper">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=appt-booker&tab=bookings' ) ); ?>" class="nav-tab <?php echo 'bookings' === $tab ? 'nav-tab-active' : ''; ?>">Bookings</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=appt-booker&tab=settings' ) ); ?>" class="nav-tab <?php echo 'settings' === $tab ? 'nav-tab-active' : ''; ?>">Settings</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=appt-booker&tab=style' ) ); ?>" class="nav-tab <?php echo 'style' === $tab ? 'nav-tab-active' : ''; ?>">Form Style</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=appt-booker&tab=taxonomy-fields' ) ); ?>" class="nav-tab <?php echo 'taxonomy-fields' === $tab ? 'nav-tab-active' : ''; ?>">Taxonomy Fields</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=appt-booker&tab=instructions' ) ); ?>" class="nav-tab <?php echo 'instructions' === $tab ? 'nav-tab-active' : ''; ?>">Help</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=appt-booker&tab=locations' ) ); ?>" class="nav-tab <?php echo 'locations' === $tab ? 'nav-tab-active' : ''; ?>">Locations</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=appt-booker&tab=staff' ) ); ?>" class="nav-tab <?php echo 'staff' === $tab ? 'nav-tab-active' : ''; ?>">Staff</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=appt-booker&tab=services' ) ); ?>" class="nav-tab <?php echo 'services' === $tab ? 'nav-tab-active' : ''; ?>">Services</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=appt-booker&tab=customer-data' ) ); ?>" class="nav-tab <?php echo 'customer-data' === $tab ? 'nav-tab-active' : ''; ?>">Customer Data</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=appt-booker&tab=content-research' ) ); ?>" class="nav-tab <?php echo 'content-research' === $tab ? 'nav-tab-active' : ''; ?>">Content Research</a>
				</h2>

				<style>
				.rgl-card{background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:24px;margin-top:16px;max-width:none;width:100%;box-sizing:border-box;box-shadow:0 10px 20px rgba(0,0,0,.03)}
				.rgl-table{width:100%;border-collapse:collapse}
				.rgl-table th,.rgl-table td{border:1px solid #ddd;padding:8px;text-align:left;vertical-align:top}
				.rgl-grid-admin{display:grid;grid-template-columns:repeat(2,minmax(260px,1fr));gap:16px;align-items:start}
				.rgl-grid-admin.rgl-grid-admin--2{grid-template-columns:repeat(2,minmax(260px,1fr))}
				.rgl-grid-admin.rgl-grid-admin--3{grid-template-columns:minmax(260px,2fr) minmax(160px,1fr) auto;align-items:end}
				.rgl-grid-admin > p,.rgl-grid-admin > div{margin:0}
				.rgl-grid-admin label{display:block;font-weight:600;color:#1d2327}
				.rgl-grid-admin input,.rgl-grid-admin select,.rgl-grid-admin textarea{box-sizing:border-box;margin-top:5px}
				.rgl-grid-admin input[type=text],.rgl-grid-admin input[type=email],.rgl-grid-admin input[type=url],.rgl-grid-admin input[type=search],.rgl-grid-admin select{width:100%;max-width:520px}
				.rgl-grid-admin textarea{width:100%;max-width:760px;min-height:86px;resize:vertical}
				.rgl-grid-admin input[type=number]{width:110px;max-width:110px}
				.rgl-grid-admin input[type=date]{width:155px;max-width:155px}
				.rgl-grid-admin input[type=time]{width:110px;max-width:110px}
				.rgl-grid-admin input[type=color]{width:52px;min-width:52px;height:34px;padding:2px}
				.rgl-grid-admin input[type=checkbox],.rgl-grid-admin input[type=radio]{width:16px;min-width:16px;height:16px;margin:0 6px 0 0;vertical-align:middle}
				.rgl-grid-admin input[type=hidden]{display:none}
				.rgl-grid-admin .button{width:auto;max-width:max-content}
				.rgl-style-section{border:1px solid #e5e7eb;border-radius:14px;padding:16px;background:#fbfbfc;margin:0 0 16px}
				.rgl-style-section h3{margin:0 0 12px}
				.rgl-style-help{margin:4px 0 0;color:#666;font-size:12px}
				.rgl-full{grid-column:1/-1}
				.rgl-days{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:8px}
				.rgl-day{border:1px solid #eee;padding:10px;background:#fafafa;border-radius:10px}
				.rgl-exceptions{margin-top:18px;padding-top:18px;border-top:1px dashed #ddd}
				.rgl-exception-list{display:grid;gap:10px}
				.rgl-exception-row{border:1px solid #e5e7eb;background:#fbfbfc;border-radius:12px;padding:12px}
				.rgl-exception-grid{display:grid;grid-template-columns:140px 140px 170px 130px 130px minmax(160px,1fr) auto;gap:10px;align-items:end}
				.rgl-exception-grid > p,.rgl-exception-grid > div{margin:0}
				.rgl-exception-actions{display:flex;gap:8px;align-items:end;justify-content:flex-end;flex-wrap:nowrap}
				.rgl-exception-time-row.is-hidden{display:none}
				@media(max-width:1200px){.rgl-exception-grid{grid-template-columns:repeat(3,minmax(0,1fr)) auto}}
				@media(max-width:1100px){.rgl-bookings-filters-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:900px){.rgl-bookings-filters-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.rgl-booking-summary-grid{grid-template-columns:1fr}.rgl-booking-months{grid-template-columns:1fr}}
				@media(max-width:782px){.rgl-bookings-filters-grid{grid-template-columns:1fr}.rgl-exception-grid{grid-template-columns:1fr 1fr}.rgl-exception-actions{grid-column:1/-1;justify-content:flex-start}}
				.rgl-admin-toolbar{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:18px}
				.rgl-toolbar-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
				.rgl-repeater-list{display:grid;gap:16px}
				.rgl-repeater-item,.rgl-accordion-item{border:1px solid #dcdcde;border-radius:14px;background:linear-gradient(180deg,#fff,#fcfcfd);overflow:hidden}
				.rgl-accordion-header{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:16px 18px;cursor:pointer;background:#fff}
				.rgl-accordion-title-wrap{display:flex;flex-direction:column;gap:4px}
				.rgl-accordion-title{font-size:15px;font-weight:700}
				.rgl-accordion-subtitle{font-size:12px;color:#666}
				.rgl-accordion-icon{font-size:18px;line-height:1;transition:transform .2s ease}
				.rgl-accordion-item.is-open .rgl-accordion-icon{transform:rotate(45deg)}
				.rgl-accordion-content{display:none;padding:18px;border-top:1px solid #eee}
				.rgl-item-actions{display:flex;gap:8px;flex-wrap:wrap}
				.rgl-chip-list{display:flex;gap:10px;flex-wrap:wrap}
				.rgl-chip{display:inline-flex;align-items:center;gap:8px;padding:8px 10px;border:1px solid #ddd;border-radius:999px;background:#fff;width:auto;max-width:max-content;line-height:1.2}
				.rgl-chip input[type=checkbox]{width:16px;min-width:16px;height:16px;margin:0;flex:0 0 16px}
				.rgl-chip.is-hidden-by-service-staff{display:none!important}
				.rgl-inline-toggle{display:inline-flex;align-items:center;gap:8px;white-space:nowrap;width:auto;max-width:max-content;margin-top:6px}
				.rgl-inline-toggle input[type=checkbox]{width:16px;min-width:16px;height:16px;margin:0;flex:0 0 16px}
				.rgl-linked-summary{font-size:12px;color:#555;margin-top:6px}
				.rgl-rule-card{border:1px solid #e7e7e7;border-radius:12px;background:#fff;overflow:hidden}
				.rgl-rule-card + .rgl-rule-card{margin-top:12px}
				.rgl-rule-header{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 14px;background:#fbfbfc;cursor:pointer}
				.rgl-rule-title{font-weight:600}
				.rgl-rule-content{display:none;padding:14px;border-top:1px solid #eee}
				.rgl-rule-card.is-open .rgl-rule-content{display:block}
				.rgl-rule-card .rgl-accordion-icon{font-size:16px}
				.rgl-save-row{margin-top:18px}.rgl-bookings-filters{margin:16px 0}.rgl-bookings-filters-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px;align-items:end}.rgl-bookings-table-wrap{overflow-x:auto;overflow-y:visible;margin-top:12px;padding-bottom:18px}.rgl-bookings-table-wrap.has-open-actions{padding-bottom:260px}.rgl-bookings-table{width:100%;border-collapse:collapse}.rgl-bookings-table th,.rgl-bookings-table td{padding:12px 10px;border-bottom:1px solid #e6e6e6;text-align:left;vertical-align:top}.rgl-bookings-table th a{text-decoration:none}.rgl-booking-row-actions{position:relative;display:inline-block}.rgl-booking-actions-menu{position:relative;display:inline-block}.rgl-booking-actions-menu summary{list-style:none;cursor:pointer}.rgl-booking-actions-menu summary::-webkit-details-marker{display:none}.rgl-booking-actions-menu summary:after{content:"\25BE";display:inline-block;margin-left:8px;font-size:10px;vertical-align:middle}.rgl-booking-actions-menu[open] summary{background:#f6f7f7}.rgl-booking-actions-dropdown{position:absolute;right:0;top:calc(100% + 6px);z-index:100000;min-width:230px;max-width:320px;max-height:min(420px,80vh);overflow:auto;overscroll-behavior:contain;padding:8px;border:1px solid #c3c4c7;border-radius:10px;background:#fff;box-shadow:0 12px 28px rgba(0,0,0,.16)}.rgl-booking-actions-close{display:flex;align-items:center;justify-content:space-between;width:100%;margin:0 0 8px 0;padding:8px 10px;border:1px solid #dcdcde;border-radius:8px;background:#f6f7f7;color:#1d2327;font-weight:600;cursor:pointer;text-align:left}.rgl-booking-actions-close:after{content:"\00d7";font-size:18px;line-height:1;margin-left:12px}.rgl-booking-actions-close:hover,.rgl-booking-actions-close:focus{background:#fff;border-color:#2271b1;color:#2271b1}.rgl-booking-actions-menu.is-fixed .rgl-booking-actions-dropdown{position:fixed;right:auto;top:auto;bottom:auto}.rgl-booking-actions-dropdown form{margin:0}.rgl-booking-actions-dropdown .button{display:block;width:100%;max-width:none;margin:0 0 6px 0;text-align:left;white-space:nowrap}.rgl-booking-actions-dropdown form:last-child .button,.rgl-booking-actions-dropdown .button:last-child{margin-bottom:0}.rgl-inline-edit-row{display:none}.rgl-inline-edit-row.is-open{display:table-row}.rgl-collapsible-panel{display:none}.rgl-collapsible-panel.is-open{display:block}.rgl-bookings-table .rgl-bview-status{display:inline-block;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
				.rgl-booking-section{margin-top:16px;border:1px solid #dcdcde;border-radius:14px;background:#fff;overflow:hidden}
				.rgl-booking-section:first-of-type{margin-top:0}
				.rgl-booking-section-toggle{display:flex;align-items:center;justify-content:space-between;gap:16px;width:100%;padding:16px 18px;border:0;background:#fff;cursor:pointer;text-align:left}
				.rgl-booking-section-title{font-size:15px;font-weight:700}
				.rgl-booking-section-subtitle{display:block;margin-top:4px;font-size:12px;color:#666}
				.rgl-booking-section-icon{font-size:18px;line-height:1;transition:transform .2s ease}
				.rgl-booking-section.is-open .rgl-booking-section-icon{transform:rotate(45deg)}
				.rgl-booking-section-body{display:none;padding:18px;border-top:1px solid #eee}
				.rgl-booking-section.is-open .rgl-booking-section-body{display:block}
				.rgl-booking-summary-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:18px}
				.rgl-booking-summary-card{padding:14px;border:1px solid #e5e7eb;border-radius:12px;background:#fafbfc}
				.rgl-booking-summary-card strong{display:block;font-size:22px;line-height:1.2}
				.rgl-booking-summary-card span{display:block;margin-top:6px;color:#666}
				.rgl-booking-calendar-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px}.rgl-booking-calendar-nav{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.rgl-booking-calendar-nav-label{font-weight:600}.rgl-booking-months{display:grid;grid-template-columns:minmax(0,1fr);gap:16px;width:100%;max-width:100%}.rgl-booking-day{padding:0}.rgl-booking-day-link{display:block;min-height:160px;padding:12px;text-decoration:none;color:inherit;height:100%}.rgl-booking-day-link:hover{background:#f8fbff}.rgl-booking-day-link:focus-within{outline:2px solid #2271b1;outline-offset:-2px}.rgl-booking-day.is-selected .rgl-booking-day-link{background:#eef6ff}.rgl-booking-active-filter{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid #c3d9ee;border-radius:999px;background:#f5faff}.rgl-booking-active-filter strong{font-weight:700}
				.rgl-booking-month-card{border:1px solid #e5e7eb;border-radius:14px;background:#fff;overflow:hidden}
				.rgl-booking-month-head{padding:14px 16px;border-bottom:1px solid #eee;background:#fafbfc}
				.rgl-booking-month-head h4{margin:0;font-size:16px}
				.rgl-booking-month-table-wrap{overflow:auto}
				.rgl-booking-month-table{width:100%;border-collapse:collapse;table-layout:fixed}
				.rgl-booking-month-table th{padding:10px 8px;background:#fff;border-bottom:1px solid #eee;font-size:12px;text-transform:uppercase;color:#666}
				.rgl-booking-month-table td{height:120px;padding:8px;border-right:1px solid #f0f0f0;border-bottom:1px solid #f0f0f0;vertical-align:top;background:#fff}
				.rgl-booking-month-table td:last-child,.rgl-booking-month-table th:last-child{border-right:0}
				.rgl-booking-day.is-other-month{background:#fafafa}
				.rgl-booking-day.is-today{background:#f5faff}
				.rgl-booking-day-number{display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border-radius:999px;font-size:12px;font-weight:700;background:#f3f4f6}
				.rgl-booking-day.is-today .rgl-booking-day-number{background:#2271b1;color:#fff}
				.rgl-booking-day-bookings{display:grid;gap:6px;margin-top:8px}
				.rgl-booking-day-item{display:block;padding:6px 8px;border-radius:10px;background:#f6f7f7;border:1px solid #e5e7eb;font-size:11px;line-height:1.35;text-decoration:none;color:inherit}
				.rgl-booking-day-item:hover{border-color:#2271b1;background:#f8fbff}.rgl-booking-day-item.is-cancelled{background:#fff1f0;border-color:#f3c5bf}
				.rgl-booking-day-item strong{display:block;font-size:11px}
				.rgl-booking-day-more{margin-top:4px;font-size:11px;color:#666}
				.rgl-chip-success{background:#ecfdf3;border-color:#a6f4c5;color:#067647}
				.rgl-chip-error{background:#fef3f2;border-color:#fecdca;color:#b42318}
				.rgl-settings-toggle-grid{display:flex;flex-wrap:wrap;align-items:center;gap:10px 12px;margin-top:10px;max-width:760px}
				.rgl-settings-toggle{display:inline-flex;align-items:center;gap:8px;width:auto;max-width:max-content;margin:0;padding:7px 10px;border:1px solid #dcdcde;border-radius:999px;background:#fff;white-space:nowrap;font-weight:500;line-height:1.2}
				.rgl-settings-toggle:hover{border-color:#8c8f94;background:#f6f7f7}
				.rgl-grid-admin .rgl-settings-toggle input[type=checkbox]{width:16px;min-width:16px;height:16px;margin:0;flex:0 0 16px}
				.rgl-notification-table{width:auto;max-width:720px;border-collapse:separate;border-spacing:0 8px;margin-top:10px}
				.rgl-notification-table th,.rgl-notification-table td{padding:0;text-align:center;vertical-align:middle}
				.rgl-notification-table th:first-child,.rgl-notification-table td:first-child{text-align:left;padding-right:18px;white-space:nowrap}
				.rgl-notification-table th:not(:first-child),.rgl-notification-table td:not(:first-child){width:58px;min-width:58px}
				.rgl-notification-table th:not(:first-child)+th,.rgl-notification-table td:not(:first-child)+td{padding-left:10px}
				.rgl-grid-admin .rgl-notification-table input[type=checkbox]{width:16px;min-width:16px;height:16px;margin:0}
				.rgl-image-picker{max-width:420px}
				.rgl-image-picker .button{margin-right:6px;margin-top:4px}
				.rgl-repeater-item input[type=number],.rgl-accordion-item input[type=number]{max-width:110px}
				.rgl-repeater-item input[type=time],.rgl-accordion-item input[type=time]{max-width:110px}
				.rgl-repeater-item input[type=date],.rgl-accordion-item input[type=date]{max-width:155px}
				.rgl-repeater-item input[type=checkbox],.rgl-accordion-item input[type=checkbox]{width:16px;min-width:16px;height:16px}
				.rgl-rule-content .rgl-grid-admin{grid-template-columns:repeat(2,minmax(220px,1fr))}
				.rgl-bookings-filters-grid input[type=date],.rgl-bookings-filters-grid select{width:100%;max-width:220px}
				.rgl-instructions-hero{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(260px,.55fr);gap:18px;align-items:stretch;margin-bottom:18px}
				.rgl-instructions-panel{border:1px solid #e5e7eb;border-radius:14px;background:#fbfbfc;padding:18px}
				.rgl-instructions-panel h4{margin:0 0 8px;font-size:15px}.rgl-instructions-panel p{margin:0 0 10px;color:#50575e}.rgl-instructions-panel p:last-child{margin-bottom:0}
				.rgl-instructions-steps{counter-reset:rglsteps;display:grid;gap:12px;margin:14px 0 0}
				.rgl-instructions-step{position:relative;padding:14px 14px 14px 52px;border:1px solid #e5e7eb;border-radius:12px;background:#fff}
				.rgl-instructions-step:before{counter-increment:rglsteps;content:counter(rglsteps);position:absolute;left:14px;top:14px;width:26px;height:26px;border-radius:999px;background:#2271b1;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700}
				.rgl-instructions-step strong{display:block;margin-bottom:4px}.rgl-instructions-step span{display:block;color:#50575e}
				.rgl-shortcode-grid{display:grid;grid-template-columns:repeat(2,minmax(260px,1fr));gap:14px;margin-top:14px}
				.rgl-shortcode-card{border:1px solid #e5e7eb;border-radius:14px;background:#fff;padding:16px}
				.rgl-shortcode-card h4{margin:0 0 8px}.rgl-shortcode-card code,.rgl-code-line{display:block;white-space:normal;word-break:break-word;background:#f6f7f7;border:1px solid #dcdcde;border-radius:8px;padding:9px 10px;margin:8px 0;color:#1d2327}
				.rgl-instructions-list{margin:8px 0 0 18px;color:#50575e}.rgl-instructions-list li{margin-bottom:6px}
				.rgl-location-staff-hours{display:none!important}
				.rgl-good-bad{display:grid;grid-template-columns:repeat(2,minmax(260px,1fr));gap:14px;margin-top:14px}.rgl-good-bad .rgl-instructions-panel{background:#fff}
				@media(max-width:1000px){.rgl-instructions-hero,.rgl-shortcode-grid,.rgl-good-bad{grid-template-columns:1fr}}
				#wpfooter{display:none}
				@media(max-width:1100px){.rgl-grid-admin,.rgl-grid-admin.rgl-grid-admin--2,.rgl-grid-admin.rgl-grid-admin--3,.rgl-rule-content .rgl-grid-admin{grid-template-columns:1fr}.rgl-days{grid-template-columns:repeat(2,minmax(0,1fr))}.rgl-admin-toolbar,.rgl-accordion-header,.rgl-rule-header{flex-direction:column;align-items:flex-start}.rgl-grid-admin input[type=text],.rgl-grid-admin input[type=email],.rgl-grid-admin input[type=url],.rgl-grid-admin select{max-width:100%}}
				</style>

				<script>
				document.addEventListener('DOMContentLoaded', function(){

					function makeStableId(prefix){
						return prefix + '_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 8);
					}

					function ensureIds(listEl, type){
						listEl.querySelectorAll('[data-item="'+type+'"]').forEach(function(item){
							var hiddenId = item.querySelector('input[type="hidden"][data-name*="[id]"]');
							if(hiddenId && !hiddenId.value.trim()){
								var idPrefix = 'service';
							if(type === 'staff'){
								idPrefix = 'staff';
							} else if(type === 'locations'){
								idPrefix = 'location';
							}
							hiddenId.value = makeStableId(idPrefix);
							}
						});
					}

					function refreshServiceRuleVisibility(serviceItem){
						var allowedStaff = {};
						serviceItem.querySelectorAll('input.rgl-service-staff-checkbox').forEach(function(cb){
							if(cb.checked){ allowedStaff[cb.value] = true; }
						});
						var allowedLocations = {};
						serviceItem.querySelectorAll('input.rgl-service-location-checkbox').forEach(function(cb){
							if(cb.checked){ allowedLocations[cb.value] = true; }
						});

						serviceItem.querySelectorAll('.rgl-staff-rule').forEach(function(ruleCard){
							var staffId = ruleCard.getAttribute('data-staff-id');
							var isLinked = !!allowedStaff[staffId];
							ruleCard.style.display = isLinked ? '' : 'none';
							ruleCard.querySelectorAll('input, select, textarea').forEach(function(field){ field.disabled = !isLinked; });
						});

						serviceItem.querySelectorAll('.rgl-location-staff-card').forEach(function(card){
							var locationId = card.getAttribute('data-location-id');
							var isAllowedLocation = !!allowedLocations[locationId];
							card.classList.toggle('is-hidden-by-service-location', !isAllowedLocation);
							card.style.display = isAllowedLocation ? '' : 'none';
							card.querySelectorAll('.rgl-location-staff-pair').forEach(function(pair){
								var toggle = pair.querySelector('input.rgl-location-staff-toggle');
								var staffId = pair.getAttribute('data-staff-id') || (toggle ? toggle.value : '');
								var isAllowedStaff = !!allowedStaff[staffId];
								pair.classList.toggle('is-hidden-by-service-staff', !isAllowedStaff);
								pair.style.display = isAllowedStaff ? '' : 'none';
								if(toggle){
									toggle.disabled = !isAllowedLocation || !isAllowedStaff;
									if(!isAllowedLocation || !isAllowedStaff){ toggle.checked = false; }
								}
								var hours = pair.querySelector('.rgl-location-staff-hours');
								if(hours){
									var showHours = !!(toggle && toggle.checked && isAllowedLocation && isAllowedStaff);
									hours.classList.toggle('is-not-needed', !showHours);
									hours.querySelectorAll('input, select, textarea').forEach(function(field){ field.disabled = !showHours; });
								}
							});
						});

						var checked = [];
						serviceItem.querySelectorAll('input.rgl-service-staff-checkbox:checked').forEach(function(cb){
							var label = cb.closest('.rgl-chip');
							if(label){ var span = label.querySelector('span'); if(span){ checked.push(span.textContent.trim()); } }
						});
						var summary = serviceItem.querySelector('.rgl-linked-summary');
						if(summary){ summary.textContent = checked.length ? 'Linked team members: ' + checked.join(', ') : 'No team members linked.'; }
					}

					function refreshExceptionModes(scope){
						scope.querySelectorAll('.rgl-exception-row').forEach(function(row){
							var mode = row.querySelector('.rgl-exception-mode');
							if(!mode){ return; }
							row.querySelectorAll('.rgl-exception-time-row').forEach(function(timeWrap){
								timeWrap.classList.toggle('is-hidden', mode.value !== 'block');
							});
						});
					}

					function refreshRepeater(listEl, type){
						ensureIds(listEl, type);
						var items = listEl.querySelectorAll('[data-item="'+type+'"]');

						items.forEach(function(item, index){
							item.querySelectorAll('[data-name]').forEach(function(field){
								field.name = field.getAttribute('data-name').replace(/%d/g, index);
							});

							item.querySelectorAll('[data-exception-row]').forEach(function(row, exIndex){
								row.querySelectorAll('[data-ex-name]').forEach(function(field){
									field.name = field.getAttribute('data-ex-name').replace(/%d/g, index).replace(/%e/g, exIndex);
								});
							});

							var nameField = item.querySelector('.rgl-item-name');
							var titleEl = item.querySelector('.rgl-accordion-title');
							if(nameField && titleEl){
								var defaultTitles = {
								locations: 'New Location',
								staff: 'New Staff Member',
								services: 'New Service'
							};
							titleEl.textContent = nameField.value.trim() || (defaultTitles[type] || 'New Item');
							}

							refreshExceptionModes(item);

							if(type === 'services'){
								refreshServiceRuleVisibility(item);
							}
						});
					}

					document.addEventListener('change', function(e){
						if(e.target.matches('input.rgl-service-location-checkbox, input.rgl-service-staff-checkbox, input.rgl-location-staff-toggle')){
							var item = e.target.closest('[data-item="services"]');
							if(item){ refreshServiceRuleVisibility(item); }
						}
					});

					document.querySelectorAll('.rgl-repeater-form').forEach(function(form){
						var type = form.getAttribute('data-repeater-type');
						var list = form.querySelector('[data-list="'+type+'"]');
						var template = document.getElementById('rgl-' + type + '-template');

						if(!list || !template){ return; }

						function keepSaveRowOutsideRepeater(){
							form.querySelectorAll('.rgl-save-row').forEach(function(saveRow){
								if(list.contains(saveRow)){ form.appendChild(saveRow); }
								saveRow.style.display = '';
							});
						}
						keepSaveRowOutsideRepeater();

						refreshRepeater(list, type);

						form.addEventListener('click', function(e){
							var btn = e.target.closest('button');
							if(!btn){ return; }

							if(btn.classList.contains('rgl-add-row')){
								e.preventDefault();
								list.insertAdjacentHTML('beforeend', template.innerHTML);
								refreshRepeater(list, type);
								var newItem = list.querySelector('[data-item="'+type+'"]:last-child');
								if(newItem){
									list.querySelectorAll('[data-item="'+type+'"]').forEach(function(i){
										i.classList.remove('is-open');
										var c = i.querySelector('.rgl-accordion-content');
										if(c){ c.style.display = 'none'; }
									});
									newItem.classList.add('is-open');
									var newContent = newItem.querySelector('.rgl-accordion-content');
									if(newContent){ newContent.style.display = 'block'; }
									var newName = newItem.querySelector('.rgl-item-name');
									if(newName){ newName.focus(); }
								}
							}

							if(btn.classList.contains('rgl-delete-row')){
								e.preventDefault();
								var item = btn.closest('[data-item="'+type+'"]');
								if(item && list.contains(item)){
									item.remove();
									if(!list.querySelector('[data-item="'+type+'"]') && template){
										list.insertAdjacentHTML('beforeend', template.innerHTML);
									}
									refreshRepeater(list, type);
									keepSaveRowOutsideRepeater();
									var saveRow = form.querySelector('.rgl-save-row');
									if(saveRow){ saveRow.style.display = ''; }
								}
							}

							if(btn.classList.contains('rgl-duplicate-row')){
								e.preventDefault();
								var item = btn.closest('[data-item="'+type+'"]');
								if(item){
									var clone = item.cloneNode(true);
									clone.querySelectorAll('input').forEach(function(input){
										if(input.type === 'hidden' && input.getAttribute('data-name') && input.getAttribute('data-name').indexOf('[id]') !== -1){
											input.value = '';
										}
									});
									item.insertAdjacentElement('afterend', clone);
									refreshRepeater(list, type);
								}
							}

							if(btn.classList.contains('rgl-add-exception')){
								e.preventDefault();
								var staffItem = btn.closest('[data-item="staff"]');
								var exList = staffItem ? staffItem.querySelector('.rgl-exception-list') : null;
								var exTemplate = document.getElementById('rgl-staff-exception-template');
								if(exList && exTemplate){
									exList.insertAdjacentHTML('beforeend', exTemplate.innerHTML);
									refreshRepeater(list, type);
								}
							}

							if(btn.classList.contains('rgl-delete-exception')){
								e.preventDefault();
								var row = btn.closest('[data-exception-row]');
								if(row){
									row.remove();
									refreshRepeater(list, type);
								}
							}

							if(btn.classList.contains('rgl-duplicate-exception')){
								e.preventDefault();
								var row = btn.closest('[data-exception-row]');
								if(row){
									var clone = row.cloneNode(true);
									row.insertAdjacentElement('afterend', clone);
									refreshRepeater(list, type);
								}
							}

							if(btn.classList.contains('rgl-move-up')){
								e.preventDefault();
								var item = btn.closest('[data-item="'+type+'"]');
								if(item && item.previousElementSibling){
									list.insertBefore(item, item.previousElementSibling);
									refreshRepeater(list, type);
								}
							}

							if(btn.classList.contains('rgl-move-down')){
								e.preventDefault();
								var item = btn.closest('[data-item="'+type+'"]');
								if(item && item.nextElementSibling){
									list.insertBefore(item.nextElementSibling, item);
									refreshRepeater(list, type);
								}
							}
						});

						form.addEventListener('input', function(e){
							if(e.target.classList.contains('rgl-item-name')){
								refreshRepeater(list, type);
							}
						});

						form.addEventListener('change', function(e){
							if(type === 'services' && e.target.closest('.rgl-chip')){
								var item = e.target.closest('[data-item="services"]');
								if(item){ refreshServiceRuleVisibility(item); }
							}
							if(type === 'staff' && e.target.classList.contains('rgl-exception-mode')){
								var item = e.target.closest('[data-item="staff"]');
								if(item){ refreshExceptionModes(item); }
							}
						});

						form.addEventListener('submit', function(){
							if(type !== 'locations'){ return; }
							refreshRepeater(list, type);
							var payloadField = form.querySelector('input[name="locations_payload"]');
							if(!payloadField){ return; }
							var payload = [];
							list.querySelectorAll('[data-item="locations"]').forEach(function(item){
								var idField = item.querySelector('input[data-name*="[id]"]');
								var nameField = item.querySelector('input[data-name*="[name]"]');
								var activeField = item.querySelector('input[data-name*="[active]"]');
								var addressField = item.querySelector('textarea[data-name*="[address]"]');
								var postcodeField = item.querySelector('input[data-name*="[postcode]"]');
								var descriptionField = item.querySelector('textarea[data-name*="[description]"]');
								var imageIdField = item.querySelector('input[data-name*="[image_id]"]');
								var imageUrlField = item.querySelector('input[data-name*="[image_url]"]');
								var name = nameField ? (nameField.value || '').trim() : '';
								if(!name){ return; }
								var rowPayload = {
									id: idField ? (idField.value || '').trim() : '',
									name: name,
									address: addressField ? (addressField.value || '').trim() : '',
									postcode: postcodeField ? (postcodeField.value || '').trim() : '',
									description: descriptionField ? (descriptionField.value || '').trim() : '',
									image_id: imageIdField ? (imageIdField.value || '0').trim() : '0',
									image_url: imageUrlField ? (imageUrlField.value || '').trim() : '',
									active: activeField && activeField.checked ? 1 : 0
								};
								for (var extraIndex = 1; extraIndex <= 5; extraIndex++) {
									var extraIdField = item.querySelector('input[data-name*="[extra_image_' + extraIndex + '_id]"]');
									var extraUrlField = item.querySelector('input[data-name*="[extra_image_' + extraIndex + '_url]"]');
									rowPayload['extra_image_' + extraIndex + '_id'] = extraIdField ? (extraIdField.value || '0').trim() : '0';
									rowPayload['extra_image_' + extraIndex + '_url'] = extraUrlField ? (extraUrlField.value || '').trim() : '';
								}
								payload.push(rowPayload);
							});
							payloadField.value = JSON.stringify(payload);
						});
					});

var rglAdminTab = (new URLSearchParams(window.location.search).get('tab') || 'bookings');
					var rglOpenStateKey = 'rgl_booking_admin_open_state_' + rglAdminTab;

					function rglGetItemStableId(item, type){
						var hiddenId = item.querySelector('input[type="hidden"][data-name*="[id]"]');
						if(hiddenId){
							var stableId = (hiddenId.value || '').trim();
							if(stableId){
								return type + '-' + stableId;
							}
						}
						var all = Array.prototype.slice.call(document.querySelectorAll('[data-item="'+type+'"]'));
						var idx = all.indexOf(item);
						return type + '-index-' + idx;
					}

					function rglAssignOpenKeys(){
						document.querySelectorAll('.rgl-booking-editor').forEach(function(el){
							if(!el.getAttribute('data-open-key')){
								var summary = el.querySelector('summary');
								var text = summary ? summary.textContent.trim().toLowerCase().replace(/[^a-z0-9]+/g,'-') : 'details';
								el.setAttribute('data-open-key', 'details-' + text);
							}
						});

						document.querySelectorAll('.rgl-accordion-item[data-item="staff"]').forEach(function(item){
							item.setAttribute('data-open-key', 'accordion-' + rglGetItemStableId(item, 'staff'));
						});
						document.querySelectorAll('.rgl-accordion-item[data-item="services"]').forEach(function(item){
							item.setAttribute('data-open-key', 'accordion-' + rglGetItemStableId(item, 'services'));
						});

						document.querySelectorAll('.rgl-accordion-item[data-item="services"]').forEach(function(serviceItem){
							var serviceKey = serviceItem.getAttribute('data-open-key') || '';
							serviceItem.querySelectorAll('.rgl-rule-card').forEach(function(card){
								var staffId = card.getAttribute('data-staff-id') || '';
								card.setAttribute('data-open-key', 'rule-' + serviceKey + '-' + staffId);
							});
						});
					}

					function rglSaveOpenState(){
						var openKeys = [];

						document.querySelectorAll('.rgl-booking-editor[open][data-open-key]').forEach(function(el){
							openKeys.push(el.getAttribute('data-open-key'));
						});
						document.querySelectorAll('.rgl-accordion-item.is-open[data-open-key]').forEach(function(el){
							openKeys.push(el.getAttribute('data-open-key'));
						});
						document.querySelectorAll('.rgl-rule-card.is-open[data-open-key]').forEach(function(el){
							openKeys.push(el.getAttribute('data-open-key'));
						});

						try {
							localStorage.setItem(rglOpenStateKey, JSON.stringify(openKeys));
						} catch(err) {}
					}

					function rglLoadOpenState(){
						var openKeys = [];
						try {
							openKeys = JSON.parse(localStorage.getItem(rglOpenStateKey) || '[]');
						} catch(err) {
							openKeys = [];
						}

						if(!Array.isArray(openKeys) || !openKeys.length){
							return;
						}

						openKeys.forEach(function(key){
							var detailsEl = document.querySelector('.rgl-booking-editor[data-open-key="'+key+'"]');
							if(detailsEl){
								detailsEl.open = true;
							}

							var accEl = document.querySelector('.rgl-accordion-item[data-open-key="'+key+'"]');
							if(accEl){
								accEl.classList.add('is-open');
								var content = accEl.querySelector('.rgl-accordion-content');
								if(content){ content.style.display = 'block'; }
							}

							var ruleEl = document.querySelector('.rgl-rule-card[data-open-key="'+key+'"]');
							if(ruleEl){
								ruleEl.classList.add('is-open');
								var rcontent = ruleEl.querySelector('.rgl-rule-content');
								if(rcontent){ rcontent.style.display = 'block'; }
							}
						});
					}


					// close all accordions by default
					document.querySelectorAll('.rgl-accordion-item').forEach(function(item){
						item.classList.remove('is-open');
						var content = item.querySelector('.rgl-accordion-content');
						if(content){ content.style.display = 'none'; }
					});

					document.querySelectorAll('.rgl-rule-card').forEach(function(card){
						card.classList.remove('is-open');
						var content = card.querySelector('.rgl-rule-content');
						if(content){ content.style.display = 'none'; }
					});

					rglAssignOpenKeys();
					rglLoadOpenState();

					document.querySelectorAll('.rgl-booking-editor').forEach(function(detailsEl){
						detailsEl.addEventListener('toggle', function(){
							rglSaveOpenState();
						});
					});

					document.addEventListener('click', function(e){
						var header = e.target.closest('.rgl-accordion-header');
						if(!header){ return; }
						if(e.target.closest('button')){ return; }
						var item = header.closest('.rgl-accordion-item');
						if(!item){ return; }
						var content = item.querySelector('.rgl-accordion-content');
						var isOpen = item.classList.contains('is-open');

						document.querySelectorAll('.rgl-accordion-item').forEach(function(i){
							i.classList.remove('is-open');
							var c = i.querySelector('.rgl-accordion-content');
							if(c){ c.style.display = 'none'; }
						});

						if(!isOpen){
							item.classList.add('is-open');
							if(content){ content.style.display = 'block'; }
						}
						rglSaveOpenState();
					});

					document.querySelectorAll('.rgl-rule-header').forEach(function(header){
						header.addEventListener('click', function(e){
							if(e.target.closest('button')){ return; }
							var card = header.closest('.rgl-rule-card');
							var wrap = card.parentNode;
							var content = card.querySelector('.rgl-rule-content');
							var isOpen = card.classList.contains('is-open');

							wrap.querySelectorAll('.rgl-rule-card').forEach(function(i){
								i.classList.remove('is-open');
								var c = i.querySelector('.rgl-rule-content');
								if(c){ c.style.display = 'none'; }
							});

							if(!isOpen){
								card.classList.add('is-open');
								if(content){ content.style.display = 'block'; }
							}
						});
					});


					var adminWrap = document.querySelector('.wrap[data-rgl-admin-booking="1"]');
					if(adminWrap){
						var adminNonce = adminWrap.getAttribute('data-rgl-admin-nonce') || '';

						function adminPositionBookingActions(menu){
							if(!menu || !menu.open){ return; }
							var summary = menu.querySelector('summary');
							var dropdown = menu.querySelector('.rgl-booking-actions-dropdown');
							if(!summary || !dropdown){ return; }
							var rect = summary.getBoundingClientRect();
							var dropdownWidth = Math.max(230, dropdown.offsetWidth || 230);
							var dropdownHeight = dropdown.offsetHeight || 260;
							var gap = 8;
							var left = rect.right - dropdownWidth;
							if(left < 8){ left = 8; }
							if(left + dropdownWidth > window.innerWidth - 8){ left = window.innerWidth - dropdownWidth - 8; }
							var top = rect.bottom + gap;
							if(top + dropdownHeight > window.innerHeight - 12){
								top = rect.top - dropdownHeight - gap;
							}
							if(top < 8){ top = 8; }
							menu.classList.add('is-fixed');
							dropdown.style.left = left + 'px';
							dropdown.style.top = top + 'px';
							dropdown.style.width = dropdownWidth + 'px';
						}

						function adminCloseOtherBookingActions(activeMenu){
							document.querySelectorAll('.rgl-booking-actions-menu[open]').forEach(function(menu){
								if(menu !== activeMenu){ menu.removeAttribute('open'); menu.classList.remove('is-fixed'); }
							});
						}

						document.addEventListener('toggle', function(e){
							var menu = e.target && e.target.classList && e.target.classList.contains('rgl-booking-actions-menu') ? e.target : null;
							if(!menu){ return; }
							var wrap = menu.closest('.rgl-bookings-table-wrap');
							if(menu.open){
								adminCloseOtherBookingActions(menu);
								if(wrap){ wrap.classList.add('has-open-actions'); }
								window.requestAnimationFrame(function(){ adminPositionBookingActions(menu); });
							}else{
								menu.classList.remove('is-fixed');
								var dropdown = menu.querySelector('.rgl-booking-actions-dropdown');
								if(dropdown){ dropdown.style.left = ''; dropdown.style.top = ''; dropdown.style.width = ''; }
								if(wrap && !wrap.querySelector('.rgl-booking-actions-menu[open]')){ wrap.classList.remove('has-open-actions'); }
							}
						}, true);

						window.addEventListener('resize', function(){
							document.querySelectorAll('.rgl-booking-actions-menu[open]').forEach(adminPositionBookingActions);
						});
						window.addEventListener('scroll', function(){
							document.querySelectorAll('.rgl-booking-actions-menu[open]').forEach(adminPositionBookingActions);
						}, true);

						document.addEventListener('keydown', function(e){
							if(e.key !== 'Escape'){ return; }
							document.querySelectorAll('.rgl-booking-actions-menu[open]').forEach(function(menu){
								menu.removeAttribute('open');
								menu.classList.remove('is-fixed');
								var dropdown = menu.querySelector('.rgl-booking-actions-dropdown');
								if(dropdown){ dropdown.style.left = ''; dropdown.style.top = ''; dropdown.style.width = ''; }
							});
							document.querySelectorAll('.rgl-bookings-table-wrap.has-open-actions').forEach(function(wrap){
								if(!wrap.querySelector('.rgl-booking-actions-menu[open]')){ wrap.classList.remove('has-open-actions'); }
							});
						});
						function adminFillSelect(select, items, placeholder, formatter){
							if(!select){ return; }
							select.innerHTML = '';
							var first = document.createElement('option');
							first.value = '';
							first.textContent = placeholder || 'Select';
							select.appendChild(first);
							(items || []).forEach(function(item){
								var opt = document.createElement('option');
								opt.value = item.value || item.id || '';
								opt.textContent = formatter ? formatter(item) : (item.label || item.name || '');
								if(typeof item.spaces_remaining !== 'undefined'){ opt.setAttribute('data-spaces-remaining', item.spaces_remaining); }
								if(typeof item.slot_capacity !== 'undefined'){ opt.setAttribute('data-slot-capacity', item.slot_capacity); }
								select.appendChild(opt);
							});
							select.disabled = !items || !items.length;
						}
						function adminApplyPreferredValue(select, items){
							if(!select){ return; }
							var preferred = select.getAttribute('data-default-value') || '';
							var current = select.value || '';
							if(current && (items || []).some(function(item){ return (item.id || item.value || '') === current; })){
								return;
							}
							if(preferred && (items || []).some(function(item){ return (item.id || item.value || '') === preferred; })){
								select.value = preferred;
								return;
							}
							if((items || []).length === 1){
								select.value = items[0].id || items[0].value || '';
							}
						}

						function adminUpdateQuantityLimit(form){
							var timeSelect = form.querySelector('.rgl-admin-booking-time');
							var qty = form.querySelector('.rgl-admin-booking-quantity');
							var hint = form.querySelector('.rgl-admin-spaces-hint');
							if(!qty){ return; }
							var opt = timeSelect && timeSelect.options ? timeSelect.options[timeSelect.selectedIndex] : null;
							var hasRemaining = !!(opt && opt.hasAttribute && opt.hasAttribute('data-spaces-remaining'));
							if(!hasRemaining){
								qty.min = '1';
								qty.removeAttribute('max');
								var fallback = parseInt(qty.value || qty.getAttribute('data-current-value') || '1', 10);
								if(!fallback || fallback < 1){ fallback = 1; }
								qty.value = String(fallback);
								if(hint){ hint.textContent = 'Choose a time to see available spaces.'; }
								return;
							}
							var remaining = parseInt(opt.getAttribute('data-spaces-remaining') || '0', 10);
							if(!remaining || remaining < 1){ remaining = 1; }
							qty.min = '1'; qty.max = String(remaining);
							var current = parseInt(qty.value || qty.getAttribute('data-current-value') || '1', 10);
							if(!current || current < 1){ current = 1; }
							if(current > remaining){ current = remaining; }
							qty.value = String(current);
							if(hint){ hint.textContent = remaining + ' space' + (remaining === 1 ? '' : 's') + ' available for this time.'; }
						}

						function adminLoadServices(form, preserveValue){
							var locationId = (form.querySelector('.rgl-admin-location')||{}).value || '';
							var serviceSelect = form.querySelector('.rgl-admin-service');
							var current = preserveValue || (serviceSelect ? serviceSelect.value : '') || '';
							var body = new URLSearchParams();
							body.append('action','rgl_booking_get_services');
							body.append('nonce',adminNonce);
							body.append('location_id',locationId);
							fetch(ajaxurl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:body.toString()}).then(function(r){return r.json();}).then(function(resp){
								var items = (resp && resp.success && resp.data) ? (resp.data.services || []) : [];
								adminFillSelect(serviceSelect, items, items.length ? 'Select' : 'No services available', function(item){
									return item.duration ? (item.name + ' (' + item.duration + ' mins)') : item.name;
								});
								if(current && items.some(function(item){ return item.id === current; })){
									serviceSelect.value = current;
								}
								adminApplyPreferredValue(serviceSelect, items);
								adminLoadStaff(form);
							});
						}
						function adminLoadLocations(form, preserveValue){
							var serviceId = (form.querySelector('.rgl-admin-service')||{}).value || '';
							var staffId = (form.querySelector('.rgl-admin-staff')||{}).value || '';
							var locationSelect = form.querySelector('.rgl-admin-location');
							var current = preserveValue || (locationSelect ? locationSelect.value : '') || '';
							var body = new URLSearchParams();
							body.append('action','rgl_booking_get_locations');
							body.append('nonce',adminNonce);
							body.append('service_id',serviceId);
							body.append('staff_id',staffId);
							fetch(ajaxurl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:body.toString()}).then(function(r){return r.json();}).then(function(resp){
								var items = (resp && resp.success && resp.data) ? (resp.data.locations || []) : [];
								adminFillSelect(locationSelect, items, items.length ? 'Select' : 'No locations available');
								if(current && items.some(function(item){ return item.id === current; })){
									locationSelect.value = current;
								}
								adminApplyPreferredValue(locationSelect, items);
							});
						}
						function adminLoadStaff(form){
							var serviceSelect = form.querySelector('.rgl-admin-service');
							var locationSelect = form.querySelector('.rgl-admin-location');
							var staffWrap = form.querySelector('.rgl-admin-staff-wrap');
							var staffSelect = form.querySelector('.rgl-admin-staff');
							var serviceId = serviceSelect ? serviceSelect.value : '';
							var locationId = locationSelect ? locationSelect.value : '';
							adminFillSelect(staffSelect, [], 'Loading…');
							adminFillSelect(form.querySelector('.rgl-admin-booking-time'), [], 'Choose location, service, team member, and date');
							if(!serviceId){
								if(staffWrap){ staffWrap.classList.remove('is-hidden'); }
								adminFillSelect(staffSelect, [], 'None / not needed');
								return;
							}
							var body = new URLSearchParams();
							body.append('action','rgl_booking_get_staff');
							body.append('nonce',adminNonce);
							body.append('service_id',serviceId);
							body.append('location_id',locationId);
							fetch(ajaxurl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:body.toString()}).then(function(r){return r.json();}).then(function(resp){
								if(resp && resp.success && resp.data){
									var items = resp.data.staff || [];
									if(items.length){
										if(staffWrap){ staffWrap.classList.remove('is-hidden'); }
										adminFillSelect(staffSelect, items, 'Select');
										adminApplyPreferredValue(staffSelect, items);
									}else{
										if(staffWrap){ staffWrap.classList.add('is-hidden'); }
										if(staffSelect){ staffSelect.value=''; }
									}
								}
							});
						}
						function adminLoadSlots(form){
							var locationId = (form.querySelector('.rgl-admin-location')||{}).value || '';
							var serviceId = (form.querySelector('.rgl-admin-service')||{}).value || '';
							var staffWrap = form.querySelector('.rgl-admin-staff-wrap');
							var staffId = staffWrap && staffWrap.classList.contains('is-hidden') ? '' : ((form.querySelector('.rgl-admin-staff')||{}).value || '');
							var dateVal = (form.querySelector('.rgl-admin-booking-date')||{}).value || '';
							var timeSelect = form.querySelector('.rgl-admin-booking-time');
							adminFillSelect(timeSelect, [], 'Loading…');
							if(!locationId || !serviceId || !dateVal || (!staffId && !(staffWrap && staffWrap.classList.contains('is-hidden')))){
								adminFillSelect(timeSelect, [], 'Choose location, service, team member, and date');
								return;
							}
							var body = new URLSearchParams();
							body.append('action','rgl_booking_get_slots');
							body.append('nonce',adminNonce);
							body.append('location_id',locationId);
							body.append('service_id',serviceId);
							body.append('staff_id',staffId);
							body.append('booking_date',dateVal);
							var bookingIdField = form.querySelector('input[name="booking_id"]');
							if(bookingIdField && bookingIdField.value){ body.append('exclude_booking_id', bookingIdField.value); }
							fetch(ajaxurl,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:body.toString()}).then(function(r){return r.json();}).then(function(resp){
								if(resp && resp.success && resp.data){
									adminFillSelect(timeSelect, resp.data.slots || [], (resp.data.slots && resp.data.slots.length) ? 'Select' : 'No available times');
									var currentVal = timeSelect.getAttribute('data-current-value');
									if(currentVal){ timeSelect.value = currentVal; }
									adminUpdateQuantityLimit(form);
								}else{
									adminFillSelect(timeSelect, [], 'No available times');
								}
							});
						}
						document.querySelectorAll('.rgl-admin-booking-form').forEach(function(form){
							var location = form.querySelector('.rgl-admin-location');
							var service = form.querySelector('.rgl-admin-service');
							var staff = form.querySelector('.rgl-admin-staff');
							var date = form.querySelector('.rgl-admin-booking-date');
							var time = form.querySelector('.rgl-admin-booking-time');
							var qty = form.querySelector('.rgl-admin-booking-quantity');
							if(location){ location.addEventListener('change', function(){ adminLoadServices(form, (form.querySelector('.rgl-admin-service')||{}).value || ''); setTimeout(function(){ adminLoadSlots(form); }, 50); }); }
							if(service){ service.addEventListener('change', function(){ adminLoadLocations(form, (form.querySelector('.rgl-admin-location')||{}).value || ''); adminLoadStaff(form); setTimeout(function(){ adminLoadSlots(form); }, 50); }); }
							if(staff){ staff.addEventListener('change', function(){ adminLoadSlots(form); }); }
							if(date){ date.addEventListener('change', function(){ adminLoadSlots(form); }); }
							if(time){ time.addEventListener('change', function(){ adminUpdateQuantityLimit(form); }); }
							if(qty){ qty.addEventListener('input', function(){ adminUpdateQuantityLimit(form); }); qty.addEventListener('change', function(){ adminUpdateQuantityLimit(form); }); }
							form.addEventListener('submit', function(e){ adminUpdateQuantityLimit(form); var q = form.querySelector('.rgl-admin-booking-quantity'); if(q && q.max && parseInt(q.value || '1', 10) > parseInt(q.max, 10)){ e.preventDefault(); alert('Spaces booked cannot exceed the remaining spaces for the selected time.'); } });
							var hasExistingBooking = !!form.querySelector('input[name="booking_id"]');
							if(hasExistingBooking && location && location.value && service && service.value && date && date.value){
								adminLoadSlots(form);
							}else{
								adminUpdateQuantityLimit(form);
							}
						});
					}
				});
				</script>


				<?php if ( 'taxonomy-fields' === $tab ) : $tax_fields = $this->get_taxonomy_custom_fields(); $taxonomies = $this->relationship_taxonomies(); $field_types = $this->taxonomy_custom_field_types(); ?>
					<div class="rgl-card">
						<h2>Custom Taxonomy Fields</h2>
						<p>Create flexible fields for Locations, Services, or Staff/Team. These are saved against the taxonomy terms, so the same fields can be used in Elementor loop grids, single templates, and taxonomy archives.</p>
						<form method="post" style="margin-top:18px;">
							<?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?>
							<input type="hidden" name="rgl_booking_action" value="save_taxonomy_field">
							<input type="hidden" name="original_field_key" value="" id="rgl-tax-original-key">
							<div class="rgl-grid-admin rgl-grid-admin--2">
								<p><label>Apply this field to<br><select name="taxonomy" id="rgl-tax-taxonomy" required>
									<?php foreach ( $taxonomies as $taxonomy_key => $taxonomy_label ) : ?>
										<option value="<?php echo esc_attr( $taxonomy_key ); ?>"><?php echo esc_html( $taxonomy_label ); ?></option>
									<?php endforeach; ?>
								</select></label></p>
								<p><label>Field Type<br><select name="field_type" id="rgl-tax-type" required>
									<?php foreach ( $field_types as $type_key => $type_label ) : ?>
										<option value="<?php echo esc_attr( $type_key ); ?>"><?php echo esc_html( $type_label ); ?></option>
									<?php endforeach; ?>
								</select></label></p>
								<p><label>Field Label<br><input type="text" name="field_label" id="rgl-tax-label" required placeholder="Example: Parking Info"></label></p>
								<p><label>Field Key<br><input type="text" name="field_key" id="rgl-tax-key" placeholder="Auto-created if left empty"><span class="rgl-style-help">Example: <code>parking_info</code>. Changing this later changes the shortcode key.</span></label></p>
								<p class="rgl-full"><label>Select Options<br><textarea name="field_options" id="rgl-tax-options" rows="4" placeholder="Needed for Select or multiple Checkbox fields. Add one option per line."></textarea></label></p>
							</div>
							<p><button class="button button-primary" type="submit" id="rgl-tax-submit">Add / Update Field</button> <button class="button" type="button" id="rgl-tax-cancel-edit" style="display:none;">Cancel Edit</button></p>
						</form>
					</div>

					<div class="rgl-card">
						<h2>Existing Fields</h2>
						<?php foreach ( $taxonomies as $taxonomy_key => $taxonomy_label ) : $fields_for_tax = isset( $tax_fields[ $taxonomy_key ] ) && is_array( $tax_fields[ $taxonomy_key ] ) ? $tax_fields[ $taxonomy_key ] : array(); ?>
							<h3><?php echo esc_html( $taxonomy_label ); ?></h3>
							<?php if ( empty( $fields_for_tax ) ) : ?>
								<p>No custom fields yet.</p>
							<?php else : ?>
								<table class="rgl-table">
									<thead><tr><th>Label</th><th>Key</th><th>Type</th><th>Post Custom Field</th><th>Shortcode</th><th>Action</th></tr></thead>
									<tbody>
									<?php foreach ( $fields_for_tax as $field_key => $field ) : ?>
										<tr>
											<td><?php echo esc_html( $field['label'] ); ?></td>
											<td><code><?php echo esc_html( $field_key ); ?></code></td>
											<td><?php echo esc_html( isset( $field_types[ $field['type'] ] ) ? $field_types[ $field['type'] ] : $field['type'] ); ?></td>
											<td><code>appt_tax_field_<?php echo esc_html( $field_key ); ?></code> <button class="button button-small rgl-copy-tax-meta-key" type="button" data-copy="appt_tax_field_<?php echo esc_attr( $field_key ); ?>">Copy</button></td>
											<td><code>[appt_tax_field taxonomy=&quot;<?php echo esc_attr( $taxonomy_key ); ?>&quot; field=&quot;<?php echo esc_attr( $field_key ); ?>&quot;]</code></td>
											<td>
												<button class="button button-small rgl-edit-tax-field" type="button" data-taxonomy="<?php echo esc_attr( $taxonomy_key ); ?>" data-key="<?php echo esc_attr( $field_key ); ?>" data-label="<?php echo esc_attr( $field['label'] ); ?>" data-type="<?php echo esc_attr( $field['type'] ); ?>" data-options="<?php echo esc_attr( isset( $field['options'] ) && is_array( $field['options'] ) ? implode( "\n", $field['options'] ) : '' ); ?>">Edit</button>
												<form method="post" style="display:inline-block;margin-left:6px;" onsubmit="return confirm('Delete this field definition? Existing term values will remain in the database, but the field will no longer display.');">
													<?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?>
													<input type="hidden" name="rgl_booking_action" value="delete_taxonomy_field">
													<input type="hidden" name="taxonomy" value="<?php echo esc_attr( $taxonomy_key ); ?>">
													<input type="hidden" name="field_key" value="<?php echo esc_attr( $field_key ); ?>">
													<button class="button button-small" type="submit">Delete</button>
												</form>
											</td>
										</tr>
									<?php endforeach; ?>
									</tbody>
								</table>
							<?php endif; ?>
						<?php endforeach; ?>
						<script>
						(function(){
							var original = document.getElementById('rgl-tax-original-key');
							var taxonomy = document.getElementById('rgl-tax-taxonomy');
							var label = document.getElementById('rgl-tax-label');
							var key = document.getElementById('rgl-tax-key');
							var type = document.getElementById('rgl-tax-type');
							var options = document.getElementById('rgl-tax-options');
							var submit = document.getElementById('rgl-tax-submit');
							var cancel = document.getElementById('rgl-tax-cancel-edit');
							document.querySelectorAll('.rgl-copy-tax-meta-key').forEach(function(btn){
								btn.addEventListener('click', function(){
									var text = btn.getAttribute('data-copy') || '';
									if (!text) { return; }
									if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(text); }
									else { var tmp=document.createElement('input'); tmp.value=text; document.body.appendChild(tmp); tmp.select(); document.execCommand('copy'); document.body.removeChild(tmp); }
									btn.textContent = 'Copied';
									setTimeout(function(){ btn.textContent = 'Copy'; }, 1200);
								});
							});
							document.querySelectorAll('.rgl-edit-tax-field').forEach(function(btn){
								btn.addEventListener('click', function(){
									original.value = btn.getAttribute('data-key') || '';
									taxonomy.value = btn.getAttribute('data-taxonomy') || '';
									label.value = btn.getAttribute('data-label') || '';
									key.value = btn.getAttribute('data-key') || '';
									type.value = btn.getAttribute('data-type') || 'text';
									options.value = btn.getAttribute('data-options') || '';
									submit.textContent = 'Save Field Changes';
									cancel.style.display = '';
									label.focus();
								});
							});
							if(cancel){ cancel.addEventListener('click', function(){ original.value=''; label.value=''; key.value=''; options.value=''; type.value='text'; submit.textContent='Add / Update Field'; cancel.style.display='none'; }); }
						})();
						</script>
						<h3>Elementor Post Custom Field keys</h3>
						<p>Use <code>appt_tax_field_YOUR_FIELD_KEY</code> in Elementor’s <strong>Post Custom Field</strong> dynamic tag. Checkbox/multiple-option fields are saved as a readable comma-separated list. If a post has more than one matching term, values are combined and duplicates are removed.</p>
						<h3>Shortcode examples</h3>
						<p><code>[appt_tax_field field="parking_info"]</code> detects the current Appt-Booker post or taxonomy archive automatically.</p>
						<p><code>[appt_tax_field taxonomy="location" field="parking_info" label="1"]</code> outputs the value with its label.</p>
						<p><code>[appt_tax_field taxonomy="service" field="difficulty" before="&lt;div class='appt-meta'&gt;" after="&lt;/div&gt;"]</code> wraps the output for styling.</p>
					</div>
				<?php endif; ?>

				<?php if ( 'instructions' === $tab ) : ?>
					<?php
					$ai_help_prompt = <<<'TJAPPTAIPROMPT'
You are giving guided help to a member of Titan Jewellery staff using TJ Appt Booker. The plugin was built by Jason Beer of Titan Jewellery.

Use the current staff guide pasted below as the source of truth. Do not invent menu items, settings or behaviour that are not in the guide. If the guide does not answer something, say so clearly and ask for a screenshot or the exact wording shown on screen.

How to help:
1. Give the exact WordPress click path first, for example: WordPress Admin > Appt-Booker > Bookings.
2. Use short, numbered steps.
3. Ask no more than one focused clarifying question at a time.
4. Start with safe, non-destructive checks.
5. Warn clearly before cancellation, deletion, blocking, marking spam, changing retention periods or any other action that affects customer records.
6. Distinguish between a customer-facing action and an admin-only action.
7. Do not suggest editing PHP, the database or plugin files to ordinary staff. Say that the issue should be escalated to Jason.
8. Protect customer privacy. Tell the user to remove or replace customer names, email addresses, phone numbers, booking references and private notes before pasting screenshots or logs into AI.
9. Never ask for passwords, API secrets, WordPress nonces, licence keys or full database exports.
10. Keep the answer specific to the task the staff member is trying to complete.

Begin by asking: “What are you trying to do, and what do you currently see on screen?”
TJAPPTAIPROMPT;
					?>
					<style>
						.rgl-help-hero{display:flex;justify-content:space-between;gap:24px;align-items:flex-start;flex-wrap:wrap}
						.rgl-help-hero h2{margin:0 0 8px;font-size:24px}
						.rgl-help-hero p{margin:0;max-width:850px;color:#50575e;font-size:14px;line-height:1.55}
						.rgl-help-badge{display:inline-block;padding:5px 10px;border:1px solid #c3c4c7;border-radius:999px;background:#f6f7f7;color:#3c434a;font-size:12px;font-weight:600}
						.rgl-help-quick{display:grid;grid-template-columns:repeat(3,minmax(180px,1fr));gap:12px;margin:20px 0}
						.rgl-help-quick div{padding:14px;border:1px solid #dcdcde;border-radius:10px;background:#f6f7f7}
						.rgl-help-quick strong{display:block;margin-bottom:5px;color:#1d2327}
						.rgl-help-section{border:1px solid #dcdcde;border-radius:10px;background:#fff;margin:12px 0;overflow:hidden}
						.rgl-help-section summary{cursor:pointer;padding:15px 18px;font-weight:700;font-size:15px;background:#f6f7f7;list-style:none;position:relative;padding-right:48px}
						.rgl-help-section summary::-webkit-details-marker{display:none}
						.rgl-help-section summary:after{content:'+';position:absolute;right:18px;top:50%;transform:translateY(-50%);font-size:22px;font-weight:400}
						.rgl-help-section[open] summary:after{content:'−'}
						.rgl-help-body{padding:17px 20px;line-height:1.58;max-width:1050px}
						.rgl-help-body h3{margin:18px 0 7px;font-size:15px}
						.rgl-help-body h3:first-child{margin-top:0}
						.rgl-help-body p{margin:8px 0}
						.rgl-help-body ol,.rgl-help-body ul{margin:8px 0 12px 22px}
						.rgl-help-body li{margin:5px 0}
						.rgl-help-table{width:100%;border-collapse:collapse;margin:12px 0}
						.rgl-help-table th,.rgl-help-table td{padding:10px;border:1px solid #dcdcde;text-align:left;vertical-align:top}
						.rgl-help-table th{background:#f6f7f7}
						.rgl-help-note{padding:11px 13px;border-left:4px solid #2271b1;background:#f0f6fc;margin:12px 0}
						.rgl-help-warning{padding:11px 13px;border-left:4px solid #d63638;background:#fcf0f1;margin:12px 0}
						.rgl-help-ai{margin-top:24px;padding:20px;border:1px solid #72aee6;border-radius:12px;background:#f0f6fc}
						.rgl-help-ai h2{margin:0 0 8px}
						.rgl-help-ai textarea{width:100%;min-height:280px;box-sizing:border-box;font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:12px;line-height:1.45;background:#fff}
						.rgl-help-ai-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:10px}
						.rgl-help-copy-status{font-weight:600;color:#067647}
						@media(max-width:900px){.rgl-help-quick{grid-template-columns:1fr}.rgl-help-table{font-size:13px}}
					</style>

					<div class="rgl-card">
						<div class="rgl-help-hero">
							<div>
								<h2>TJ Appt Booker staff help</h2>
								<p>This guide covers the day-to-day booking workflow used by Titan Jewellery staff. It also includes setup, reminders, customer changes, consultant reassignment, no-show safeguards, privacy, exports and troubleshooting.</p>
							</div>
							<span class="rgl-help-badge">Version <?php echo esc_html( self::VERSION ); ?></span>
						</div>

						<div class="rgl-help-quick">
							<div><strong>Manage appointments</strong>Appt-Booker &gt; Bookings</div>
							<div><strong>Change booking rules</strong>Appt-Booker &gt; Settings</div>
							<div><strong>Change available calls</strong>Appt-Booker &gt; Services / Staff</div>
						</div>

						<div id="tj-appt-help-guide">
							<details class="rgl-help-section" open>
								<summary>1. Daily staff workflow</summary>
								<div class="rgl-help-body">
									<ol>
										<li>Open <strong>WordPress Admin &gt; Appt-Booker &gt; Bookings</strong>.</li>
										<li>Check the calendar, today’s bookings and any <strong>Pending approval</strong> records.</li>
										<li>Open a booking by clicking its reference or customer name.</li>
										<li>After the consultation, add post-conversation notes and mark it <strong>Completed</strong>. If the customer did not attend, mark it <strong>No-show</strong>.</li>
										<li>Use <strong>Email Booking</strong> only when a separate manual message is needed.</li>
									</ol>
									<div class="rgl-help-note"><strong>Morning summary:</strong> the system sends staff/admin a summary of that day’s appointments when there are bookings. Customer reminders are separate.</div>
								</div>
							</details>

							<details class="rgl-help-section">
								<summary>2. Booking statuses and what they mean</summary>
								<div class="rgl-help-body">
									<table class="rgl-help-table">
										<tr><th>Status</th><th>Meaning</th><th>Customer effect</th></tr>
										<tr><td><strong>Confirmed</strong></td><td>The appointment is accepted and holds the slot.</td><td>Customer can receive confirmation, calendar links, reminder, change and cancellation options.</td></tr>
										<tr><td><strong>Pending approval</strong></td><td>The slot is held while staff review the booking.</td><td>No final calendar invite until approved. Customer can still cancel.</td></tr>
										<tr><td><strong>Cancelled</strong></td><td>The appointment is cancelled and the slot is released.</td><td>Old change and cancellation links stop working.</td></tr>
										<tr><td><strong>Completed</strong></td><td>The consultation took place.</td><td>No customer email is sent when this status is selected.</td></tr>
										<tr><td><strong>No-show</strong></td><td>The customer did not attend.</td><td>No customer email is sent. Their history may affect how a later booking is reviewed.</td></tr>
									</table>
									<p>Completed and No-show are only available after the appointment has started. This prevents a future slot being released by mistake.</p>
								</div>
							</details>

							<details class="rgl-help-section">
								<summary>3. Add, edit, approve or cancel a booking</summary>
								<div class="rgl-help-body">
									<h3>Add a booking for a customer</h3>
									<ol><li>Go to <strong>Bookings</strong> and select <strong>Add Booking</strong>.</li><li>Choose location/contact method, service, consultant, date and an available time.</li><li>Enter name, email and phone. All 3 are required.</li><li>Choose Confirmed or Pending approval, then save.</li></ol>
									<h3>Edit an existing booking</h3>
									<p>Open the booking and choose <strong>Edit Booking</strong>. Changing a pending booking’s details does not confirm it automatically. Use the explicit approval action when it is ready.</p>
									<h3>Approve a pending booking</h3>
									<p>Use the signed approval link in the admin email or change the status deliberately in admin. Approval sends the proper confirmation and calendar attachment.</p>
									<h3>Cancel a booking</h3>
									<p>Use <strong>Cancel Booking</strong> from the booking actions. Customers can also cancel through their signed link before the configured deadline.</p>
									<div class="rgl-help-warning"><strong>Do not use Mark as Spam for an ordinary cancellation or no-show.</strong> Mark as Spam cancels the booking and adds the email address to the blocked list.</div>
								</div>
							</details>

							<details class="rgl-help-section">
								<summary>4. Reminders, customer changes and consultant reassignment</summary>
								<div class="rgl-help-body">
									<h3>Automatic reminder</h3>
									<p>Confirmed bookings receive one reminder at the configured lead time, normally 24 hours before. A booking made after its reminder point is not sent an immediate duplicate email.</p>
									<h3>Send a reminder manually</h3>
									<p>Open a future confirmed booking and choose <strong>Send reminder now</strong> or <strong>Send reminder again</strong>. The booking page shows whether the reminder is scheduled, sent or awaiting retry.</p>
									<h3>Customer changes the appointment</h3>
									<p>The customer can choose another available date and time from their signed Change appointment link. The same booking reference is retained. After a successful change, the old link is invalid and a fresh link is included in the updated email.</p>
									<h3>Change the consultant</h3>
									<p>Edit the booking and select the replacement consultant. The customer is told who will now contact them. The new consultant receives the handover; the previous consultant receives a limited removal notice without private customer notes.</p>
								</div>
							</details>

							<details class="rgl-help-section">
								<summary>5. No-shows, review and future-booking blocks</summary>
								<div class="rgl-help-body">
									<ul>
										<li>One no-show does not block the customer.</li>
										<li>At the configured threshold, normally 2 no-shows within 180 days, the next booking is placed into Pending approval for human review.</li>
										<li>The plugin does not automatically reject the booking.</li>
										<li>A deliberate <strong>Block future bookings</strong> action is separate and time-limited.</li>
										<li>Use <strong>Allow future bookings</strong> to remove that block.</li>
									</ul>
									<div class="rgl-help-note">A no-show is not the same as spam. Review the circumstances before applying a future-booking block.</div>
								</div>
							</details>

							<details class="rgl-help-section">
								<summary>6. Services, staff, contact methods and availability</summary>
								<div class="rgl-help-body">
									<p>The system needs at least 1 active location/contact method, 1 staff member and 1 service.</p>
									<h3>Locations</h3><p>For Titan Jewellery these are communication methods such as Phone, WhatsApp call and WhatsApp message. Choose which team members can use each method, but set each team member’s weekly availability only once per service. Customer email wording and caller/WhatsApp details are configured in Settings.</p>
									<h3>Staff</h3><p>Add the consultant’s name, email, availability, allowed locations and any days/times off. Staff email is required if they should receive booking notifications.</p>
									<h3>Services</h3><p>Set duration, slot offer interval, internal footprint/buffer, customer-facing duration, minimum notice, availability, interest checklist and eligible staff/location combinations.</p>
									<h3>Capacity</h3><p>Capacity per time slot is set through the service’s team-member rules. Public bookings currently reserve 1 place each; capacity allows multiple separate customers to book the same session until full.</p>
									<div class="rgl-help-warning"><strong>After structural changes:</strong> save the relevant tab and check the booking form. If generated service/staff/location pages are used, save WordPress &gt; Settings &gt; Permalinks once.</div>
								</div>
							</details>

							<details class="rgl-help-section">
								<summary>7. Settings staff should understand</summary>
								<div class="rgl-help-body">
									<ul>
										<li><strong>Notifications:</strong> controls which booking emails go to admin, staff and customer.</li>
										<li><strong>Cancellation/change notice:</strong> how close to the appointment customers may cancel or change online.</li>
										<li><strong>Automatic reminders:</strong> on/off and lead time.</li>
										<li><strong>Manual approval:</strong> holds every new public booking until staff approve it.</li>
										<li><strong>Per-email rate limit:</strong> restricts excessive bookings from the same email address.</li>
										<li><strong>No-show safeguard:</strong> sends repeat no-show bookings for human review.</li>
										<li><strong>Retention:</strong> currently minimises contact details after 6 months and deletes the full booking after 30 months.</li>
									</ul>
									<div class="rgl-help-warning">Do not shorten retention periods or turn retention off without Jason’s approval. These settings delete or retain customer records.</div>
								</div>
							</details>

							<details class="rgl-help-section">
								<summary>8. Customer data, privacy and research exports</summary>
								<div class="rgl-help-body">
									<h3>Customer Data tab</h3><p>Search by email to export or delete that customer’s booking data. Deletion also removes a matching future-booking block. Treat deletion as irreversible.</p>
									<h3>Content Research tab</h3><p>Exports anonymised consultation topics and notes for identifying common questions and useful content ideas. Review notes before sharing them externally.</p>
									<h3>What not to paste into AI</h3><p>Remove names, email addresses, phone numbers, booking references and any details that could identify the customer. Do not paste raw customer-data exports into a public AI chat.</p>
									<div class="rgl-help-note">The plugin provides privacy and retention controls, but staff decisions and Titan Jewellery’s main privacy notice remain part of compliance.</div>
								</div>
							</details>

							<details class="rgl-help-section">
								<summary>9. Troubleshooting</summary>
								<div class="rgl-help-body">
									<h3>No dates or times appear</h3><ol><li>Confirm the service, contact method and consultant combination is valid.</li><li>Check the shared service/team member availability, time off, notice rules, capacity and daily limits.</li><li>Check that the date is not blocked by the working-day/bank-holiday rules.</li><li>Reload the page without being logged in as admin and try again.</li></ol>
									<h3>Email did not arrive</h3><ol><li>Check the booking email address and status.</li><li>Use Resend Confirmation or Send reminder again where appropriate.</li><li>Check the WordPress mail log and spam folder.</li><li>If repeated sends fail, escalate to Jason rather than repeatedly clicking send.</li></ol>
									<h3>Customer’s old link no longer works</h3><p>This can be expected after an appointment is changed, cancelled, completed, marked no-show, the customer email is edited, or contact details are removed under retention.</p>
									<h3>Booking page looks old after a change</h3><p>Clear the Kinsta/page cache and reload. Keep the booking page out of long full-page caching because its security nonce expires.</p>
									<h3>Something appears technically broken</h3><p>Take a screenshot, note the booking reference and exact time, then escalate to Jason. Do not edit PHP, database tables or plugin files.</p>
								</div>
							</details>

							<details class="rgl-help-section">
								<summary>10. Shortcodes and advanced Elementor use</summary>
								<div class="rgl-help-body">
									<p><code>[appt_booker]</code> displays the normal booking form and is the recommended shortcode.</p>
									<p>Examples:</p>
									<ul><li><code>[appt_booker service="Consultation" lock_service="1"]</code></li><li><code>[appt_booker location="Phone" staff="Jason" lock="all"]</code></li><li><code>[appt_booker dynamic="1" lock="1"]</code> for an Appt-Booker single template.</li></ul>
									<p>Related Elementor Loop Grid Query IDs: <code>appt_related_team</code>, <code>appt_related_locations</code>, <code>appt_related_services</code>.</p>
									<p>Taxonomy custom fields can be displayed with <code>[appt_tax_field field="FIELD_KEY"]</code> or Elementor’s Post Custom Field dynamic tag using <code>appt_tax_field_FIELD_KEY</code>.</p>
									<div class="rgl-help-note">Advanced templates and code changes should be handled by Jason. Staff should normally use the existing booking page and admin tabs.</div>
								</div>
							</details>
						</div>

						<div class="rgl-help-ai">
							<h2>AI guided help</h2>
							<p>Copy the support pack below into ChatGPT or Claude. It includes a privacy-aware support prompt and the current staff guide shown above. Remove customer personal data from any follow-up screenshots or text.</p>
							<textarea id="tj-appt-ai-prompt" readonly><?php echo esc_textarea( $ai_help_prompt ); ?></textarea>
							<div class="rgl-help-ai-actions">
								<button type="button" class="button button-primary" id="tj-appt-copy-ai-pack">Copy prompt + staff guide</button>
								<button type="button" class="button" id="tj-appt-copy-ai-prompt">Copy prompt only</button>
								<span class="rgl-help-copy-status" id="tj-appt-copy-status" aria-live="polite"></span>
							</div>
						</div>

						<p style="margin:22px 0 0;color:#646970;"><strong>TJ Appt Booker</strong> was built by Jason Beer of Titan Jewellery.</p>
					</div>
					<script>
					(function(){
						var promptEl=document.getElementById('tj-appt-ai-prompt');
						var guideEl=document.getElementById('tj-appt-help-guide');
						var statusEl=document.getElementById('tj-appt-copy-status');
						function copyText(value,label){
							function done(){if(statusEl){statusEl.textContent=label+' copied';window.setTimeout(function(){statusEl.textContent='';},2500);}}
							if(navigator.clipboard&&window.isSecureContext){navigator.clipboard.writeText(value).then(done).catch(function(){fallback(value);done();});return;}
							fallback(value);done();
						}
						function fallback(value){var ta=document.createElement('textarea');ta.value=value;ta.setAttribute('readonly','');ta.style.position='fixed';ta.style.opacity='0';document.body.appendChild(ta);ta.select();document.execCommand('copy');document.body.removeChild(ta);}
						var promptBtn=document.getElementById('tj-appt-copy-ai-prompt');
						var packBtn=document.getElementById('tj-appt-copy-ai-pack');
						if(promptBtn){promptBtn.addEventListener('click',function(){copyText(promptEl?promptEl.value:'','AI prompt');});}
						if(packBtn){packBtn.addEventListener('click',function(){var guide=guideEl?guideEl.textContent:'';copyText((promptEl?promptEl.value:'')+'\n\nCURRENT STAFF GUIDE\n===================\n'+guide,'AI support pack');});}
					})();
					</script>
				<?php endif; ?>
				<?php if ( 'bookings' === $tab ) : ?>
					<?php
					$view_booking_id = isset( $_GET['view'] ) ? absint( $_GET['view'] ) : 0;
					?>
					<?php if ( $view_booking_id > 0 ) : ?>
						<?php $this->render_admin_booking_view( $view_booking_id ); ?>
					<?php else : ?>
					<?php
					$orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'booking_date';
					$order   = isset( $_GET['order'] ) ? strtolower( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : 'desc';
					$filter_ref = isset( $_GET['filter_ref'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_ref'] ) ) : '';
					$filter_customer = isset( $_GET['filter_customer'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_customer'] ) ) : '';
					$filter_location = isset( $_GET['filter_location'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_location'] ) ) : '';
					$filter_service = isset( $_GET['filter_service'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_service'] ) ) : '';
					$filter_staff = isset( $_GET['filter_staff'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_staff'] ) ) : '';
					$filter_date = isset( $_GET['filter_date'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_date'] ) ) : '';
					if ( '' !== $filter_date && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $filter_date ) ) {
						$filter_date = '';
					}
					$filter_time = isset( $_GET['filter_time'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_time'] ) ) : '';
					if ( '' !== $filter_time && ! preg_match( '/^\d{2}:\d{2}$/', $filter_time ) ) {
						$filter_time = '';
					}
					$calendar_offset = isset( $_GET['calendar_offset'] ) ? intval( $_GET['calendar_offset'] ) : 0;
					$calendar_offset = max( -24, min( 24, $calendar_offset ) );
					$base_args = array( 'page' => 'appt-booker', 'tab' => 'bookings', 'filter_ref' => $filter_ref, 'filter_customer' => $filter_customer, 'filter_location' => $filter_location, 'filter_service' => $filter_service, 'filter_staff' => $filter_staff, 'filter_date' => $filter_date, 'filter_time' => $filter_time, 'calendar_offset' => $calendar_offset );
					$calendar_prev_url = esc_url( add_query_arg( array_merge( $base_args, array( 'calendar_offset' => $calendar_offset - 1 ) ), admin_url( 'admin.php' ) ) );
					$calendar_next_url = esc_url( add_query_arg( array_merge( $base_args, array( 'calendar_offset' => $calendar_offset + 1 ) ), admin_url( 'admin.php' ) ) );
					$sort_link = function( $col ) use ( $base_args, $orderby, $order ) {
						$next = ( $orderby === $col && 'asc' === $order ) ? 'desc' : 'asc';
						return esc_url( add_query_arg( array_merge( $base_args, array( 'orderby' => $col, 'order' => $next ) ), admin_url( 'admin.php' ) ) );
					};
					?>
					<div class="rgl-card">
						<div class="rgl-admin-toolbar">
							<div>
								<h3 style="margin:0;">Bookings</h3>
								<p style="margin:6px 0 0;color:#666;">Manage bookings in a table view with filters, quick actions, and a 1 month visual calendar.</p>
							</div>
							<button class="button button-primary" type="button" onclick="var wrap=document.getElementById('appt-booking-add-wrap'); if(wrap){wrap.classList.toggle('is-open');}">Add Booking</button>
						</div>

						<div id="appt-booking-add-wrap" class="rgl-collapsible-panel">
							<?php
							$admin_resolved_defaults = array(
								'location_id' => '',
								'service_id'  => '',
								'staff_id'    => '',
							);
							$admin_locations_for_select = $this->get_admin_locations_for_select();
							$admin_services_for_select  = $this->get_admin_services_for_select();
							$admin_staff_for_select     = $this->get_admin_staff_for_select();
							?>
							<form method="post" class="rgl-card rgl-admin-booking-form" style="margin-top:12px;max-width:none;">
								<?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?>
								<input type="hidden" name="rgl_booking_action" value="add_booking">
								<div class="rgl-grid-admin">
									<?php $admin_show_address_field = $this->is_field_enabled( $settings, 'show_address_field', false ); ?>
									<p><label>Location<br><select name="location_id" class="rgl-admin-location" required data-default-value="<?php echo esc_attr( $admin_resolved_defaults['location_id'] ); ?>">
										<option value="">Select</option>
										<?php foreach ( $admin_locations_for_select as $location_row ) : ?>
											<option value="<?php echo esc_attr( $location_row['id'] ); ?>" <?php selected( $location_row['id'], $admin_resolved_defaults['location_id'] ); ?>><?php echo esc_html( $location_row['name'] ); ?></option>
										<?php endforeach; ?>
									</select></label></p>
									<p><label>Service<br><select name="service_id" class="rgl-admin-service" required data-default-value="<?php echo esc_attr( $admin_resolved_defaults['service_id'] ); ?>">
										<option value="">Select</option>
										<?php foreach ( $admin_services_for_select as $service_row ) : ?>
											<option value="<?php echo esc_attr( $service_row['id'] ); ?>" <?php selected( $service_row['id'], $admin_resolved_defaults['service_id'] ); ?>><?php echo esc_html( $service_row['name'] ); ?></option>
										<?php endforeach; ?>
									</select></label></p>
									<p class="rgl-admin-staff-wrap"><label>Team Member<br><select name="staff_id" class="rgl-admin-staff" data-default-value="<?php echo esc_attr( $admin_resolved_defaults['staff_id'] ); ?>"><option value="">None / not needed</option><?php foreach ( $admin_staff_for_select as $staff_row ) : ?><option value="<?php echo esc_attr( $staff_row['id'] ); ?>" <?php selected( $staff_row['id'], $admin_resolved_defaults['staff_id'] ); ?>><?php echo esc_html( $staff_row['name'] ); ?></option><?php endforeach; ?></select></label></p>
									<p><label>Date<br><input type="date" name="booking_date" class="rgl-admin-booking-date" required></label></p>
									<p><label>Available Time<br><select name="booking_time" class="rgl-admin-booking-time" required disabled><option value="">Choose service, team member, and date</option></select></label></p>
									<p><label>Spaces Booked<br><input type="number" name="booking_quantity" class="rgl-admin-booking-quantity" value="1" min="1" step="1"><br><small class="rgl-admin-spaces-hint" style="color:#666;">Choose a time to see available spaces.</small></label></p>
									<p><label>Customer Name<br><input type="text" name="customer_name" required></label></p>
									<p><label>Email<br><input type="email" name="customer_email" required></label></p>
									<p><label>Phone Number<br><input type="tel" name="customer_phone" required autocomplete="tel"></label></p>
									<?php if ( $admin_show_address_field ) : ?><p class="rgl-full"><label>Address<br><textarea name="customer_address" rows="3"></textarea></label></p><?php endif; ?>
									<p><label>Status<br><select name="booking_status"><option value="confirmed">Confirmed</option><option value="pending_approval">Pending approval</option><option value="cancelled">Cancelled</option></select><br><small>Completed and No-show become available after the appointment starts.</small></label></p>
									<p class="rgl-full"><label>Booking Notes<br><textarea name="customer_notes" rows="3"></textarea></label></p>
									<p class="rgl-full"><label>Post-conversation notes<br><textarea name="admin_notes" rows="6" placeholder="Paste your post-call summary here. What was discussed, their concerns, what you advised, any follow-up. Visible only to you and Helen. Feeds into the morning email and Content Research export."></textarea></label></p>
								</div>
								<p><button class="button button-primary" type="submit">Save Booking</button> <button class="button" type="button" onclick="var row=this.closest('.rgl-inline-edit-row'); if(row){row.classList.remove('is-open');}">Cancel Editing</button></p>
							</form>
						</div>

						<?php
						$booking_summary      = $this->get_booking_summary_counts( $bookings );
						$booking_summary['confirmed'] = $this->get_upcoming_admin_menu_count();
						$today_date           = current_time( 'Y-m-d' );
						$current_month_ts     = strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) );
						$view_month_ts        = strtotime( sprintf( '%+d month', $calendar_offset ), $current_month_ts );
						$month_timestamps     = array( $view_month_ts );
						$calendar_month_start = gmdate( 'Y-m-01', $view_month_ts );
						$calendar_month_end   = gmdate( 'Y-m-t', $view_month_ts );
						$calendar_bookings    = $this->get_admin_calendar_bookings( $calendar_month_start, $calendar_month_end );
						$booking_calendar_map = $this->get_booking_calendar_map( $calendar_bookings );
						?>

						<div class="rgl-booking-section is-open" id="appt-booking-summary-section">
							<button class="rgl-booking-section-toggle" type="button" onclick="this.setAttribute(&#39;aria-expanded&#39;, this.getAttribute(&#39;aria-expanded&#39;) === &#39;true&#39; ? &#39;false&#39; : &#39;true&#39;); this.parentNode.classList.toggle(&#39;is-open&#39;);" aria-expanded="true">
								<span>
									<span class="rgl-booking-section-title">Booking Summary & Table</span>
									<span class="rgl-booking-section-subtitle">Filters, sortable booking table, summaries, and row actions.</span>
								</span>
								<span class="rgl-booking-section-icon">+</span>
							</button>
							<div class="rgl-booking-section-body">
								<div class="rgl-booking-summary-grid">
									<div class="rgl-booking-summary-card">
										<strong><?php echo esc_html( number_format_i18n( $booking_summary['total'] ) ); ?></strong>
										<span>Total bookings</span>
									</div>
									<div class="rgl-booking-summary-card">
										<strong><?php echo esc_html( number_format_i18n( $booking_summary['confirmed'] ) ); ?></strong>
										<span>Upcoming bookings</span>
									</div>
									<div class="rgl-booking-summary-card">
										<strong><?php echo esc_html( number_format_i18n( $booking_summary['cancelled'] ) ); ?></strong>
										<span>Cancelled</span>
									</div>
									<div class="rgl-booking-summary-card">
										<strong><?php echo esc_html( number_format_i18n( $booking_summary['completed'] ) ); ?></strong>
										<span>Completed</span>
									</div>
									<div class="rgl-booking-summary-card">
										<strong><?php echo esc_html( number_format_i18n( $booking_summary['no_show'] ) ); ?></strong>
										<span>No-shows</span>
									</div>
								</div>

								<form method="get" class="rgl-bookings-filters">
									<input type="hidden" name="page" value="appt-booker">
									<input type="hidden" name="tab" value="bookings">
									<input type="hidden" name="calendar_offset" value="<?php echo esc_attr( $calendar_offset ); ?>">
									<div class="rgl-bookings-filters-grid">
										<p><label>Ref Number<br><input type="text" name="filter_ref" value="<?php echo esc_attr( $filter_ref ); ?>"></label></p>
										<p><label>Customer Name<br><input type="text" name="filter_customer" value="<?php echo esc_attr( $filter_customer ); ?>"></label></p>
										<p><label>Location<br><input type="text" name="filter_location" value="<?php echo esc_attr( $filter_location ); ?>"></label></p>
										<p><label>Service<br><input type="text" name="filter_service" value="<?php echo esc_attr( $filter_service ); ?>"></label></p>
										<p><label>Team Member<br><input type="text" name="filter_staff" value="<?php echo esc_attr( $filter_staff ); ?>"></label></p>
										<p><label>Date<br><input type="date" name="filter_date" value="<?php echo esc_attr( $filter_date ); ?>"></label></p>
										<p><label>Time<br><input type="time" name="filter_time" value="<?php echo esc_attr( $filter_time ); ?>"></label></p>
										<p style="align-self:end;display:flex;gap:8px;flex-wrap:wrap;"><button class="button" type="submit">Filter</button><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=appt-booker&tab=bookings' ) ); ?>">Reset</a></p>
									</div>
								</form>
								<?php if ( '' !== $filter_date || '' !== $filter_time ) : ?>
									<div class="rgl-booking-active-filter">
										<span>Viewing bookings for</span>
										<strong><?php echo esc_html( trim( $filter_date . ( $filter_time ? ' at ' . $filter_time : '' ) ) ); ?></strong>
										<a href="<?php echo esc_url( add_query_arg( array_merge( $base_args, array( 'filter_date' => '', 'filter_time' => '' ) ), admin_url( 'admin.php' ) ) ); ?>">Clear date/time</a>
									</div>
								<?php endif; ?>

								<div class="rgl-bookings-table-wrap">
									<?php
									// Status badge colours (added). Mirrors the booking detail view.
									$status_colour = array(
										'confirmed'        => array( 'bg' => '#ecfdf3', 'fg' => '#067647', 'border' => '#abefc6' ),
										'held'             => array( 'bg' => '#fff7ed', 'fg' => '#b54708', 'border' => '#fed7aa' ),
										'cancelled'        => array( 'bg' => '#fef3f2', 'fg' => '#b42318', 'border' => '#fecdca' ),
										'pending_approval' => array( 'bg' => '#fffbeb', 'fg' => '#b45309', 'border' => '#fde68a' ),
				'completed' => array( 'bg' => '#eff8ff', 'fg' => '#175cd3', 'border' => '#b2ddff' ),
				'no_show'   => array( 'bg' => '#f4f3ff', 'fg' => '#5925dc', 'border' => '#d9d6fe' ),
									);
									?>
									<table class="rgl-bookings-table">
										<thead>
											<tr>
												<th><a href="<?php echo $sort_link( 'booking_reference' ); ?>">Ref Number</a></th>
												<th><a href="<?php echo $sort_link( 'location_name' ); ?>">Location</a></th>
												<th><a href="<?php echo $sort_link( 'service_name' ); ?>">Service Name</a></th>
												<th><a href="<?php echo $sort_link( 'staff_name' ); ?>">Staff Member Name</a></th>
												<th><a href="<?php echo $sort_link( 'booking_date' ); ?>">Date</a></th>
												<th><a href="<?php echo $sort_link( 'customer_name' ); ?>">Customer Name</a></th>
												<th><a href="<?php echo $sort_link( 'booking_status' ); ?>">Status</a></th>
												<th>Actions</th>
											</tr>
										</thead>
										<tbody>
										<?php if ( empty( $bookings ) ) : ?>
											<tr><td colspan="8">No bookings found.</td></tr>
										<?php else : ?>
											<?php foreach ( $bookings as $booking ) :
												$view_url = admin_url( 'admin.php?page=appt-booker&tab=bookings&view=' . absint( $booking->id ) );
											?>
											<tr>
												<td><a href="<?php echo esc_url( $view_url ); ?>"><strong><?php echo esc_html( $booking->booking_reference ); ?></strong></a></td>
												<td><?php echo esc_html( isset( $booking->location_name ) && $booking->location_name ? $booking->location_name : '—' ); ?></td>
												<td><?php echo esc_html( $booking->service_name ); ?></td>
												<td><?php echo esc_html( $booking->staff_name ? $booking->staff_name : '—' ); ?></td>
												<td><?php echo esc_html( trim( $this->format_booking_short_date( $booking ) . ' ' . substr( (string) $booking->booking_time, 0, 5 ) ) ); ?></td>
												<td><a href="<?php echo esc_url( $view_url ); ?>"><?php echo esc_html( $booking->customer_name ); ?></a></td>
												<td><?php $row_status = isset( $booking->booking_status ) ? (string) $booking->booking_status : 'confirmed'; $row_sc = isset( $status_colour[ $row_status ] ) ? $status_colour[ $row_status ] : array( 'bg' => '#f3f4f6', 'fg' => '#374151', 'border' => '#e5e7eb' ); ?><span class="rgl-bview-status" style="background:<?php echo esc_attr( $row_sc['bg'] ); ?>;color:<?php echo esc_attr( $row_sc['fg'] ); ?>;border:1px solid <?php echo esc_attr( $row_sc['border'] ); ?>;"><?php echo esc_html( $this->get_booking_status_label( $row_status ) ); ?></span></td>
												<td>
													<div class="rgl-booking-row-actions">
												<details class="rgl-booking-actions-menu">
													<summary class="button">Actions</summary>
													<div class="rgl-booking-actions-dropdown">
														<button class="rgl-booking-actions-close" type="button" aria-label="Close booking actions" onclick="var d=this.closest('details'); if(d){d.removeAttribute('open'); d.classList.remove('is-fixed'); var w=d.closest('.rgl-bookings-table-wrap'); if(w && !w.querySelector('.rgl-booking-actions-menu[open]')){w.classList.remove('has-open-actions');}}">Close Actions</button>
														<a class="button" href="<?php echo esc_url( $this->get_booking_summary_url( $booking ) ); ?>" target="_blank" rel="noopener">Booking Summary</a>
														<button class="button" type="button" onclick="var el=document.getElementById('appt-edit-booking-<?php echo esc_attr( $booking->id ); ?>'); if(el){el.classList.toggle('is-open');} this.closest('details').removeAttribute('open');">Edit Booking</button>
														<button class="button" type="button" onclick="var el=document.getElementById('appt-message-booking-<?php echo esc_attr( $booking->id ); ?>'); if(el){el.classList.toggle('is-open'); var t=el.querySelector('[name=message_target]'); if(t){t.value='single';} var h=el.querySelector('.rgl-message-target-label'); if(h){h.textContent='Email this booking only';}} this.closest('details').removeAttribute('open');">Email Booking</button>
														<button class="button" type="button" onclick="var el=document.getElementById('appt-message-booking-<?php echo esc_attr( $booking->id ); ?>'); if(el){el.classList.toggle('is-open'); var t=el.querySelector('[name=message_target]'); if(t){t.value='session';} var h=el.querySelector('.rgl-message-target-label'); if(h){h.textContent='Email everyone in this same session';}} this.closest('details').removeAttribute('open');">Email This Session</button>
														<form method="post"><?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?><input type="hidden" name="rgl_booking_action" value="duplicate_booking"><input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>"><button class="button" type="submit">Duplicate Booking</button></form>
														<form method="post" onsubmit="return confirm('Cancel this booking?');"><?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?><input type="hidden" name="rgl_booking_action" value="update_booking_status"><input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>"><input type="hidden" name="booking_status" value="cancelled"><button class="button" type="submit">Cancel Booking</button></form>
															<?php $booking_start_ts = $this->get_booking_start_timestamp( $booking ); $appointment_has_started = $booking_start_ts > 0 && $booking_start_ts <= current_datetime()->getTimestamp(); ?>
															<?php if ( $appointment_has_started && self::STATUS_COMPLETED !== (string) $booking->booking_status ) : ?><form method="post" onsubmit="return confirm('Mark this appointment as completed? No customer email will be sent.');"><?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?><input type="hidden" name="rgl_booking_action" value="update_booking_status"><input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>"><input type="hidden" name="booking_status" value="completed"><button class="button" type="submit">Mark Completed</button></form><?php endif; ?>
															<?php if ( $appointment_has_started && self::STATUS_NO_SHOW !== (string) $booking->booking_status ) : ?><form method="post" onsubmit="return confirm('Mark this appointment as a no-show? No customer email will be sent.');"><?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?><input type="hidden" name="rgl_booking_action" value="update_booking_status"><input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>"><input type="hidden" name="booking_status" value="no_show"><button class="button" type="submit">Mark No-show</button></form><?php endif; ?>
														<form method="post" onsubmit="return confirm('Mark this as spam? The customer will be sent a polite cancellation email saying this is for purchase questions only, and their email address will be blocked from future bookings.');"><?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?><input type="hidden" name="rgl_booking_action" value="mark_as_spam"><input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>"><button class="button" type="submit">Mark as Spam</button></form>
														<form method="post" onsubmit="return confirm('Delete this booking?');"><?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?><input type="hidden" name="rgl_booking_action" value="delete_booking"><input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>"><button class="button button-link-delete" type="submit">Delete Booking</button></form>
														<form method="post"><?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?><input type="hidden" name="rgl_booking_action" value="resend_confirmation"><input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>"><button class="button" type="submit">Resend Confirmation</button></form><?php if ( self::STATUS_CONFIRMED === (string) $booking->booking_status && $this->get_booking_start_timestamp( $booking ) > current_datetime()->getTimestamp() ) : ?><form method="post"><?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?><input type="hidden" name="rgl_booking_action" value="send_reminder_now"><input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>"><button class="button" type="submit">Send Reminder</button></form><?php endif; ?>
													</div>
												</details>
											</div>
												</td>
											</tr>
											<tr id="appt-edit-booking-<?php echo esc_attr( $booking->id ); ?>" class="rgl-inline-edit-row">
												<td colspan="10">
													<form method="post" class="rgl-card rgl-admin-booking-form" style="max-width:none; margin:8px 0;">
														<?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?>
														<input type="hidden" name="rgl_booking_action" value="save_booking">
														<input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>">
														<div class="rgl-grid-admin">
										<p><label>Location<br><select name="location_id" class="rgl-admin-location" required data-default-value="<?php echo esc_attr( isset( $booking->location_id ) ? $booking->location_id : '' ); ?>"><option value="">Select</option><?php foreach ( $this->get_admin_locations_for_select() as $location_row ) : ?><option value="<?php echo esc_attr( $location_row['id'] ); ?>" <?php selected( $location_row['id'], isset( $booking->location_id ) ? $booking->location_id : '' ); ?>><?php echo esc_html( $location_row['name'] ); ?></option><?php endforeach; ?></select></label></p>
										<p><label>Service<br><select name="service_id" class="rgl-admin-service" required data-default-value="<?php echo esc_attr( isset( $booking->service_id ) ? $booking->service_id : '' ); ?>"><option value="">Select</option><?php foreach ( $this->get_admin_services_for_select() as $service_row ) : ?><option value="<?php echo esc_attr( $service_row['id'] ); ?>" <?php selected( $service_row['id'], $booking->service_id ); ?>><?php echo esc_html( $service_row['name'] ); ?></option><?php endforeach; ?></select></label></p>
										<p class="rgl-admin-staff-wrap"><label>Team Member<br><select name="staff_id" class="rgl-admin-staff" data-default-value="<?php echo esc_attr( isset( $booking->staff_id ) ? $booking->staff_id : '' ); ?>"><option value="">None / not needed</option><?php foreach ( $this->get_admin_staff_for_select() as $staff_row ) : ?><option value="<?php echo esc_attr( $staff_row['id'] ); ?>" <?php selected( $staff_row['id'], $booking->staff_id ); ?>><?php echo esc_html( $staff_row['name'] ); ?></option><?php endforeach; ?></select></label></p>
										<p><label>Date<br><input type="date" name="booking_date" class="rgl-admin-booking-date" value="<?php echo esc_attr( $booking->booking_date ); ?>" required></label></p>
										<p><label>Available Time<br><select name="booking_time" class="rgl-admin-booking-time" data-current-value="<?php echo esc_attr( substr( $booking->booking_time, 0, 5 ) ); ?>" required><option value="<?php echo esc_attr( substr( $booking->booking_time, 0, 5 ) ); ?>"><?php echo esc_html( substr( $booking->booking_time, 0, 5 ) ); ?></option></select></label></p>
										<p><label>Spaces Booked<br><input type="number" name="booking_quantity" class="rgl-admin-booking-quantity" value="<?php echo esc_attr( isset( $booking->spaces_booked ) ? max( 1, absint( $booking->spaces_booked ) ) : 1 ); ?>" data-current-value="<?php echo esc_attr( isset( $booking->spaces_booked ) ? max( 1, absint( $booking->spaces_booked ) ) : 1 ); ?>" min="1" step="1"><br><small class="rgl-admin-spaces-hint" style="color:#666;">Choose a time to see available spaces.</small></label></p>
										<p><label>Customer Name<br><input type="text" name="customer_name" value="<?php echo esc_attr( $booking->customer_name ); ?>" required></label></p>
										<p><label>Email<br><input type="email" name="customer_email" value="<?php echo esc_attr( $booking->customer_email ); ?>" required></label></p>
										<p><label>Phone Number<br><input type="tel" name="customer_phone" value="<?php echo esc_attr( isset( $booking->customer_phone ) ? $booking->customer_phone : '' ); ?>" required autocomplete="tel"></label></p>
										<p class="rgl-full"><label>Address<br><textarea name="customer_address" rows="3"><?php echo esc_textarea( isset( $booking->customer_address ) ? $booking->customer_address : '' ); ?></textarea></label></p>
										<p><label>Status<br><select name="booking_status"><?php if ( self::STATUS_PENDING_APPROVAL === $booking->booking_status ) : ?><option value="pending_approval" selected>Pending approval</option><?php endif; ?><option value="confirmed" <?php selected( 'confirmed', $booking->booking_status ); ?>>Confirmed</option><option value="cancelled" <?php selected( 'cancelled', $booking->booking_status ); ?>>Cancelled</option><?php $edit_booking_start_ts = $this->get_booking_start_timestamp( $booking ); $can_set_outcome = ( $edit_booking_start_ts > 0 && $edit_booking_start_ts <= current_datetime()->getTimestamp() ) || in_array( (string) $booking->booking_status, array( self::STATUS_COMPLETED, self::STATUS_NO_SHOW ), true ); if ( $can_set_outcome ) : ?><option value="completed" <?php selected( 'completed', $booking->booking_status ); ?>>Completed</option><option value="no_show" <?php selected( 'no_show', $booking->booking_status ); ?>>No-show</option><?php endif; ?></select></label></p>
										<p class="rgl-full"><label>Booking Notes<br><textarea name="customer_notes" rows="3"><?php echo esc_textarea( isset( $booking->customer_notes ) ? $booking->customer_notes : '' ); ?></textarea></label></p>
										<?php
										// Display admin_notes as a single textarea. Defensively clean any
										// legacy zero-valued numeric content so the edit screen doesn't
										// show "0.000000" or similar.
										$admin_notes_raw = isset( $booking->admin_notes ) ? (string) $booking->admin_notes : '';
										if ( is_numeric( trim( $admin_notes_raw ) ) && 0.0 === (float) $admin_notes_raw ) {
											$admin_notes_raw = '';
										}
										?>
										<p class="rgl-full"><label>Post-conversation notes<br><textarea name="admin_notes" rows="6" placeholder="Paste your post-call summary here. What was discussed, their concerns, what you advised, any follow-up. Visible only to you and Helen. Feeds into the morning email and Content Research export."><?php echo esc_textarea( $admin_notes_raw ); ?></textarea></label></p>
										<p><button class="button button-primary" type="submit">Save Booking</button> <button class="button" type="button" onclick="var row=this.closest('.rgl-inline-edit-row'); if(row){row.classList.remove('is-open');}">Cancel Editing</button></p>
													</form>
												</td>
											</tr>
											<tr id="appt-message-booking-<?php echo esc_attr( $booking->id ); ?>" class="rgl-inline-edit-row">
												<td colspan="10">
													<form method="post" class="rgl-card" style="max-width:none; margin:8px 0;">
														<?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?>
														<input type="hidden" name="rgl_booking_action" value="send_booking_message">
														<input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>">
														<input type="hidden" name="message_target" value="single">
														<h3 class="rgl-message-target-label" style="margin-top:0;">Email this booking only</h3>
														<p style="margin-top:0;color:#666;">Session: <?php echo esc_html( trim( ( ! empty( $booking->location_name ) ? $booking->location_name . ' — ' : '' ) . $booking->service_name . ( ! empty( $booking->staff_name ) ? ' — ' . $booking->staff_name : '' ) . ' — ' . $this->format_booking_short_date( $booking ) . ' ' . substr( (string) $booking->booking_time, 0, 5 ) ) ); ?></p>
														<p><label>Subject<br><input type="text" name="message_subject" value="<?php echo esc_attr( 'Update about your booking ' . $booking->booking_reference ); ?>" required style="width:100%;max-width:680px;"></label></p>
														<p><label>Message<br><textarea name="message_body" rows="6" required style="width:100%;max-width:680px;"></textarea></label></p>
														<p><button class="button button-primary" type="submit" onclick="return confirm('Send this message now?');">Send Message</button> <button class="button" type="button" onclick="var row=this.closest('.rgl-inline-edit-row'); if(row){row.classList.remove('is-open');}">Cancel Message</button></p>
													</form>
												</td>
											</tr>
											<?php endforeach; ?>
										<?php endif; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>

						<div class="rgl-booking-section is-open" id="appt-booking-calendar-section">
							<button class="rgl-booking-section-toggle" type="button" onclick="this.setAttribute(&#39;aria-expanded&#39;, this.getAttribute(&#39;aria-expanded&#39;) === &#39;true&#39; ? &#39;false&#39; : &#39;true&#39;); this.parentNode.classList.toggle(&#39;is-open&#39;);" aria-expanded="true">
								<span>
									<span class="rgl-booking-section-title">1 Month Session View</span>
									<span class="rgl-booking-section-subtitle">Grouped by date, time, location, service, and team member so busy days stay readable.</span>
								</span>
								<span class="rgl-booking-section-icon">+</span>
							</button>
							<div class="rgl-booking-section-body">
								<div class="rgl-booking-calendar-toolbar">
									<div class="rgl-booking-calendar-nav">
										<a class="button" href="<?php echo $calendar_prev_url; ?>">← Previous</a>
										<span class="rgl-booking-calendar-nav-label"><?php echo esc_html( wp_date( 'F Y', $month_timestamps[0] ) ); ?></span>
										<a class="button" href="<?php echo $calendar_next_url; ?>">Next →</a>
									</div>
									<div style="color:#666;">Click a session to filter the table to that date, time, service, and team member. Click the day number to filter by date only.</div>
								</div>
								<div class="rgl-booking-months">
									<?php foreach ( $month_timestamps as $month_ts ) : ?>
										<?php
										$month_start     = gmdate( 'Y-m-01', $month_ts );
										$month_end       = gmdate( 'Y-m-t', $month_ts );
										$month_name      = wp_date( 'F Y', $month_ts );
										$grid_start_ts   = strtotime( 'monday this week', strtotime( $month_start . ' 12:00:00' ) );
										$grid_end_ts     = strtotime( 'sunday this week', strtotime( $month_end . ' 12:00:00' ) );
										?>
										<div class="rgl-booking-month-card">
											<div class="rgl-booking-month-head">
												<h4><?php echo esc_html( $month_name ); ?></h4>
											</div>
											<div class="rgl-booking-month-table-wrap">
												<table class="rgl-booking-month-table">
													<thead>
														<tr>
															<th>Mon</th>
															<th>Tue</th>
															<th>Wed</th>
															<th>Thu</th>
															<th>Fri</th>
															<th>Sat</th>
															<th>Sun</th>
														</tr>
													</thead>
													<tbody>
														<?php for ( $week_ts = $grid_start_ts; $week_ts <= $grid_end_ts; $week_ts = strtotime( '+7 days', $week_ts ) ) : ?>
															<tr>
																<?php for ( $day_offset = 0; $day_offset < 7; $day_offset++ ) : ?>
																	<?php
																	$day_ts       = strtotime( '+' . $day_offset . ' days', $week_ts );
																	$day_date     = gmdate( 'Y-m-d', $day_ts );
																	$is_other     = gmdate( 'Y-m', $day_ts ) !== gmdate( 'Y-m', $month_ts );
																	$is_today     = $day_date === $today_date;
																	$day_bookings = isset( $booking_calendar_map[ $day_date ] ) ? $booking_calendar_map[ $day_date ] : array();
																	?>
							<?php $day_filter_url = esc_url( add_query_arg( array_merge( $base_args, array( 'filter_ref' => '', 'filter_customer' => '', 'filter_date' => $day_date, 'filter_time' => '' ) ), admin_url( 'admin.php' ) ) ); ?>
							<td class="rgl-booking-day<?php echo $is_other ? ' is-other-month' : ''; ?><?php echo $is_today ? ' is-today' : ''; ?><?php echo $filter_date === $day_date ? ' is-selected' : ''; ?>">
								<div class="rgl-booking-day-link" aria-label="Sessions for <?php echo esc_attr( $day_date ); ?>">
									<a class="rgl-booking-day-number" href="<?php echo $day_filter_url; ?>" aria-label="View all bookings for <?php echo esc_attr( $day_date ); ?>"><?php echo esc_html( gmdate( 'j', $day_ts ) ); ?></a>
									<?php if ( ! empty( $day_bookings ) ) : ?>
										<div class="rgl-booking-day-bookings">
											<?php foreach ( array_slice( $day_bookings, 0, 4 ) as $day_booking ) : ?>
												<?php
												$session_filter_url = esc_url(
													add_query_arg(
														array_merge(
															$base_args,
															array(
																'filter_ref'      => '',
																'filter_customer' => '',
																'filter_location' => $day_booking['location'],
																'filter_service'  => $day_booking['service'],
																'filter_staff'    => $day_booking['staff'],
																'filter_date'     => $day_date,
																'filter_time'     => $day_booking['time'],
															)
														),
														admin_url( 'admin.php' )
													)
												);
												$spaces_label = 1 === (int) $day_booking['spaces_booked'] ? '1 booked' : number_format_i18n( (int) $day_booking['spaces_booked'] ) . ' booked';
												?>
												<a class="rgl-booking-day-item<?php echo self::STATUS_CANCELLED === $day_booking['status'] ? ' is-cancelled' : ''; ?>" href="<?php echo $session_filter_url; ?>" aria-label="View <?php echo esc_attr( $day_booking['service'] ); ?> with <?php echo esc_attr( $day_booking['staff'] ); ?> at <?php echo esc_attr( $day_booking['time'] ); ?> on <?php echo esc_attr( $day_date ); ?>">
													<strong><?php echo esc_html( $day_booking['time'] ); ?><?php echo $day_booking['service'] ? ' · ' . esc_html( $day_booking['service'] ) : ''; ?></strong>
													<span><?php echo esc_html( trim( $day_booking['staff'] . ( $day_booking['location'] ? ' · ' . $day_booking['location'] : '' ) ) ); ?></span>
													<span><?php echo esc_html( $spaces_label ); ?></span>
												</a>
											<?php endforeach; ?>
											<?php if ( count( $day_bookings ) > 4 ) : ?>
												<a class="rgl-booking-day-more" href="<?php echo $day_filter_url; ?>">+<?php echo esc_html( count( $day_bookings ) - 4 ); ?> more sessions</a>
											<?php endif; ?>
										</div>
									<?php else : ?>
										<div class="rgl-booking-day-bookings"><div class="rgl-booking-day-more">No sessions</div></div>
									<?php endif; ?>
								</div>
							</td>
																<?php endfor; ?>
															</tr>
														<?php endfor; ?>
													</tbody>
												</table>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>

					<?php endif; /* end of view_booking_id else */ ?>
				<?php endif; /* end of 'bookings' === $tab */ ?>

				<?php if ( 'settings' === $tab ) : ?>
					<form method="post" class="rgl-card">
						<?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?>
						<input type="hidden" name="rgl_booking_action" value="save_settings">
						<div class="rgl-grid-admin">
							<p><label>Form Title<br><input type="text" name="form_title" value="<?php echo esc_attr( $settings['form_title'] ); ?>"></label></p>
							<p><label>Success Message<br><input type="text" name="success_message" value="<?php echo esc_attr( $settings['success_message'] ); ?>"></label></p><p><label>Reference Prefix<br><input type="text" name="booking_prefix" value="<?php echo esc_attr( isset( $settings['booking_prefix'] ) ? $settings['booking_prefix'] : 'APPT' ); ?>"></label></p><p><label>Email From Address<br><input type="email" name="email_from_address" value="<?php echo esc_attr( isset( $settings['email_from_address'] ) ? $settings['email_from_address'] : get_option( 'admin_email' ) ); ?>"></label></p>
							<p class="rgl-full"><label>Form Intro<br><textarea name="form_intro" rows="3"><?php echo esc_textarea( $settings['form_intro'] ); ?></textarea></label></p>
							<div class="rgl-full">
								<strong>Front-end / manual booking fields</strong>
								<div class="rgl-settings-toggle-grid">
									<label class="rgl-settings-toggle"><input type="checkbox" name="show_location_field" value="1" <?php checked( ! empty( $settings['show_location_field'] ) ); ?>> <?php echo esc_html( $this->get_form_label( $settings, 'label_location', 'Location' ) ); ?></label>
									<label class="rgl-settings-toggle"><input type="checkbox" name="show_service_field" value="1" <?php checked( ! empty( $settings['show_service_field'] ) ); ?>> <?php echo esc_html( $this->get_form_label( $settings, 'label_service', 'Service' ) ); ?></label>
									<label class="rgl-settings-toggle"><input type="checkbox" name="show_staff_field" value="1" <?php checked( ! empty( $settings['show_staff_field'] ) ); ?>> <?php echo esc_html( $this->get_form_label( $settings, 'label_staff', 'Team Member' ) ); ?></label>
									<label class="rgl-settings-toggle"><input type="checkbox" name="show_address_field" value="1" <?php checked( ! empty( $settings['show_address_field'] ) ); ?>> <?php echo esc_html( $this->get_form_label( $settings, 'label_address', 'Address' ) ); ?></label>
									<span class="rgl-settings-toggle"><strong><?php echo esc_html( $this->get_form_label( $settings, 'label_phone', 'Phone Number' ) ); ?></strong> (always shown and required)</span>
								</div>
								<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;margin-top:14px;">
									<p><label>Service Label<br><input type="text" name="label_service" value="<?php echo esc_attr( $this->get_form_label( $settings, 'label_service', 'Service' ) ); ?>" placeholder="Service"></label></p>
									<p><label>Location Label<br><input type="text" name="label_location" value="<?php echo esc_attr( $this->get_form_label( $settings, 'label_location', 'Location' ) ); ?>" placeholder="Location"></label></p>
									<p><label>Team Member Label<br><input type="text" name="label_staff" value="<?php echo esc_attr( $this->get_form_label( $settings, 'label_staff', 'Team Member' ) ); ?>" placeholder="Team Member"></label></p>
									<p><label>Address Label<br><input type="text" name="label_address" value="<?php echo esc_attr( $this->get_form_label( $settings, 'label_address', 'Address' ) ); ?>" placeholder="Address"></label></p>
									<p><label>Phone Number Label<br><input type="text" name="label_phone" value="<?php echo esc_attr( $this->get_form_label( $settings, 'label_phone', 'Phone Number' ) ); ?>" placeholder="Phone Number"></label></p>
								</div>
								<p style="margin:8px 0 0;color:#666;">These labels control the front-end and manual booking field names. Select placeholders are generated automatically, for example Services becomes Select service and Treatments becomes Select treatment.</p>
								<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;margin-top:14px;">
									<p><label>Default Location when hidden / auto-resolved<br><select name="default_location_id"><option value="">Auto if only one valid option</option><?php foreach ( $locations as $location_row ) : ?><option value="<?php echo esc_attr( $location_row['id'] ); ?>" <?php selected( $location_row['id'], isset( $settings['default_location_id'] ) ? $settings['default_location_id'] : '' ); ?>><?php echo esc_html( $location_row['name'] ); ?></option><?php endforeach; ?></select></label></p>
									<p><label>Default Service when hidden / auto-resolved<br><select name="default_service_id"><option value="">Auto if only one valid option</option><?php foreach ( $services as $service_row ) : ?><option value="<?php echo esc_attr( $service_row['id'] ); ?>" <?php selected( $service_row['id'], isset( $settings['default_service_id'] ) ? $settings['default_service_id'] : '' ); ?>><?php echo esc_html( $service_row['name'] ); ?></option><?php endforeach; ?></select></label></p>
									<p><label>Default Team Member when hidden / auto-resolved<br><select name="default_staff_id"><option value="">Auto if only one valid option</option><?php foreach ( $staff as $staff_row ) : if ( empty( $staff_row['id'] ) || empty( $staff_row['name'] ) ) { continue; } ?><option value="<?php echo esc_attr( $staff_row['id'] ); ?>" <?php selected( $staff_row['id'], isset( $settings['default_staff_id'] ) ? $settings['default_staff_id'] : '' ); ?>><?php echo esc_html( $staff_row['name'] ); ?><?php echo empty( $staff_row['active'] ) ? ' (inactive)' : ''; ?></option><?php endforeach; ?></select></label></p>
								</div>
								<p style="margin:10px 0 0;color:#666;">These toggles control the front-end booking form and the manual add-booking form in admin. When a field is hidden, the snippet now tries the matching default first and otherwise auto-uses the only valid option if there is just one.</p>
							</div>
							<div class="rgl-full" style="margin-top:18px;">
								<strong>Anti-abuse / Security</strong>
								<p style="margin:6px 0 10px;color:#666;">Protects the public booking form from bots and rapid-fire abuse. A honeypot and a minimum form-fill time are always on. Cloudflare Turnstile is optional.</p>
								<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;">
									<p><label>Minimum seconds before submit<br><input type="number" min="0" max="120" step="1" name="security_min_seconds_to_submit" value="<?php echo esc_attr( isset( $settings['security_min_seconds_to_submit'] ) ? absint( $settings['security_min_seconds_to_submit'] ) : 3 ); ?>"></label><br><span style="color:#666;font-size:12px;">3 is a sensible default. Set to 0 to disable.</span></p>
									<p><label>Per-IP throttle (seconds between bookings)<br><input type="number" min="0" max="3600" step="1" name="security_throttle_seconds" value="<?php echo esc_attr( isset( $settings['security_throttle_seconds'] ) ? absint( $settings['security_throttle_seconds'] ) : 60 ); ?>"></label><br><span style="color:#666;font-size:12px;">60 = one booking per IP per minute. Set to 0 to disable.</span></p>
									<p><label class="rgl-inline-toggle">Enable Cloudflare Turnstile <input type="checkbox" name="security_turnstile_enabled" value="1" <?php checked( ! empty( $settings['security_turnstile_enabled'] ) ); ?>></label></p>
								</div>
								<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:10px;margin-top:10px;">
									<p><label>Turnstile Site Key<br><input type="text" name="security_turnstile_site_key" value="<?php echo esc_attr( isset( $settings['security_turnstile_site_key'] ) ? $settings['security_turnstile_site_key'] : '' ); ?>" placeholder="0x4AAAAAAA..."></label></p>
									<p><label>Turnstile Secret Key<br><input type="text" name="security_turnstile_secret_key" value="<?php echo esc_attr( isset( $settings['security_turnstile_secret_key'] ) ? $settings['security_turnstile_secret_key'] : '' ); ?>" placeholder="0x4AAAAAAA..."></label></p>
								</div>
								<p style="margin:8px 0 0;color:#666;font-size:12px;">If Turnstile is enabled, add &lt;script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer&gt;&lt;/script&gt; to your theme header. Site key from your Cloudflare Turnstile dashboard.</p>
							</div>

							<div class="rgl-full" style="margin-top:18px;">
								<strong>Customer self-cancellation</strong>
								<p style="margin:6px 0 10px;color:#666;">Adds a signed cancellation link to confirmation emails so customers can cancel without contacting you. Paid bookings always show a "contact us for a refund" message instead of the self-cancel link.</p>
								<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;">
									<p><label class="rgl-inline-toggle">Allow online cancellation <input type="checkbox" name="cancellation_enabled" value="1" <?php checked( ! empty( $settings['cancellation_enabled'] ) ); ?>></label></p>
									<p><label>Minimum hours notice<br><input type="number" min="0" max="168" step="1" name="cancellation_min_hours_notice" value="<?php echo esc_attr( isset( $settings['cancellation_min_hours_notice'] ) ? absint( $settings['cancellation_min_hours_notice'] ) : 1 ); ?>"></label><br><span style="color:#666;font-size:12px;">0 = until the appointment starts. 1 = up to one hour before.</span></p>
									<p><label class="rgl-inline-toggle">Ask for cancellation reason (optional) <input type="checkbox" name="cancellation_ask_for_reason" value="1" <?php checked( ! empty( $settings['cancellation_ask_for_reason'] ) ); ?>></label></p>
								</div>
							</div>

							<div class="rgl-full" style="margin-top:18px;">
								<strong>Shared phone &amp; daily capacity</strong>
								<p style="margin:6px 0 10px;color:#666;">Titan shares one phone, so only one team member can be on a call at a time. With this on, any booking blocks the slot for everyone, and the buffer guarantees a gap between calls even if one overruns.</p>
								<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;">
									<p><label class="rgl-inline-toggle">Treat the phone as a shared resource <input type="checkbox" name="shared_resource_enabled" value="1" <?php checked( ! empty( $settings['shared_resource_enabled'] ) ); ?>></label></p>
									<p><label>Gap between calls (minutes)<br><input type="number" min="0" max="240" step="1" name="shared_resource_buffer" value="<?php echo esc_attr( isset( $settings['shared_resource_buffer'] ) ? absint( $settings['shared_resource_buffer'] ) : 15 ); ?>"></label><br><span style="color:#666;font-size:12px;">Buffer added after every call before the next can start.</span></p>
									<p><label>Global maximum bookings per day<br><input type="number" min="0" max="100" step="1" name="daily_booking_cap" value="<?php echo esc_attr( isset( $settings['daily_booking_cap'] ) ? absint( $settings['daily_booking_cap'] ) : 0 ); ?>"></label><br><span style="color:#666;font-size:12px;">0 = no limit. A per-team-member cap (set on each Team Member) overrides this.</span></p>
								</div>
							</div>

							<div class="rgl-full" style="margin-top:18px;">
								<strong>Communication method wording and contact details</strong>
								<p style="margin:6px 0 10px;color:#666;">Set the phrase used in customer emails and an optional note explaining how contact will arrive. Ordinary Phone methods default to “Your call will come from a withheld number.” For WhatsApp, enter the exact number that will contact the customer.</p>
								<?php
								$comm_verbs = isset( $settings['comm_method_verbs'] ) && is_array( $settings['comm_method_verbs'] ) ? $settings['comm_method_verbs'] : array();
								$comm_notes = isset( $settings['comm_method_customer_notes'] ) && is_array( $settings['comm_method_customer_notes'] ) ? $settings['comm_method_customer_notes'] : array();
								if ( empty( $locations ) ) :
								?>
									<p style="color:#666;">No Locations set up yet. Add your communication methods under the Locations tab first, then return here.</p>
								<?php else : ?>
									<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:12px;">
										<?php foreach ( $locations as $location_row ) :
											$location_name_lower = strtolower( (string) $location_row['name'] );
											$is_whatsapp = false !== strpos( $location_name_lower, 'whatsapp' );
											$note_placeholder = $is_whatsapp ? 'e.g. We will call you on WhatsApp from 07xxx xxxxxx.' : 'e.g. Your call will come from a withheld number.';
										?>
											<div style="padding:12px;border:1px solid #dcdcde;border-radius:8px;background:#fff;">
												<strong><?php echo esc_html( $location_row['name'] ); ?></strong>
												<p><label>Email phrase<br><input type="text" name="comm_method_verbs[<?php echo esc_attr( $location_row['id'] ); ?>]" value="<?php echo esc_attr( isset( $comm_verbs[ $location_row['id'] ] ) ? $comm_verbs[ $location_row['id'] ] : '' ); ?>" placeholder="e.g. will call you" style="width:100%;box-sizing:border-box;"></label></p>
												<p><label>Customer contact note<br><textarea name="comm_method_customer_notes[<?php echo esc_attr( $location_row['id'] ); ?>]" rows="2" placeholder="<?php echo esc_attr( $note_placeholder ); ?>" style="width:100%;box-sizing:border-box;"><?php echo esc_textarea( isset( $comm_notes[ $location_row['id'] ] ) ? $comm_notes[ $location_row['id'] ] : '' ); ?></textarea></label></p>
											</div>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>

							<div class="rgl-full" style="margin-top:18px;">
								<strong>Email notifications</strong>
								<p style="margin:6px 0 0;color:#666;">Admin emails go to the site admin email. Staff emails go to the selected team member's email address. Customer emails go to the booking customer's email address. The sender name and sender email are controlled by the Email From fields above.</p>
								<p style="margin:14px 0 4px;"><label for="rgl-email-footer-text"><strong>Email footer</strong></label></p>
								<p style="margin:0 0 6px;color:#666;font-size:13px;">Shown at the bottom of every booking email. One detail per line - for example your registered company name, a line about your experience, and your website address. Leave blank to hide the footer entirely.</p>
								<textarea id="rgl-email-footer-text" name="email_footer_text" rows="4" style="width:100%;box-sizing:border-box;" placeholder="Titan Jewellery Ltd&#10;Confidence working with precious metals since 1988 and alternative metals since 2002.&#10;www.titanjewellery.co.uk"><?php echo esc_textarea( isset( $settings['email_footer_text'] ) ? $settings['email_footer_text'] : '' ); ?></textarea>
								<p style="margin:14px 0 4px;"><label for="rgl-customer-message-label"><strong>Customer message field label</strong></label></p>
								<p style="margin:0 0 6px;color:#666;font-size:13px;">The label above the optional message box on the booking form, where the customer can tell you what the call is about.</p>
								<input type="text" id="rgl-customer-message-label" name="customer_message_label" style="width:100%;box-sizing:border-box;" value="<?php echo esc_attr( isset( $settings['customer_message_label'] ) ? $settings['customer_message_label'] : '' ); ?>" placeholder="Anything you would like us to know before the call? (Optional)">
								<p style="margin:14px 0 4px;"><label for="rgl-booking-privacy-notice"><strong>Booking privacy notice</strong></label></p>
								<p style="margin:0 0 6px;color:#666;font-size:13px;">Shown on the booking form and in the customer's confirmation email. Reassures customers how their details are used. Leave blank to hide it.</p>
								<textarea id="rgl-booking-privacy-notice" name="booking_privacy_notice" rows="3" style="width:100%;box-sizing:border-box;" placeholder="Your details are used only to arrange and carry out this booking..."><?php echo esc_textarea( isset( $settings['booking_privacy_notice'] ) ? $settings['booking_privacy_notice'] : '' ); ?></textarea>
								<table class="rgl-notification-table" style="margin-top:16px;">
									<thead>
										<tr>
											<th>Event</th>
											<th>Admin</th>
											<th>Staff</th>
											<th>Customer</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Booking made</td>
											<td><input type="checkbox" name="notify_admin_booking_made" value="1" <?php checked( ! empty( $settings['notify_admin_booking_made'] ) ); ?>></td>
											<td><input type="checkbox" name="notify_staff_booking_made" value="1" <?php checked( ! empty( $settings['notify_staff_booking_made'] ) ); ?>></td>
											<td><input type="checkbox" name="notify_customer_booking_made" value="1" <?php checked( ! empty( $settings['notify_customer_booking_made'] ) ); ?>></td>
										</tr>
										<tr>
											<td>Booking edited</td>
											<td><input type="checkbox" name="notify_admin_booking_edited" value="1" <?php checked( ! empty( $settings['notify_admin_booking_edited'] ) ); ?>></td>
											<td><input type="checkbox" name="notify_staff_booking_edited" value="1" <?php checked( ! empty( $settings['notify_staff_booking_edited'] ) ); ?>></td>
											<td><input type="checkbox" name="notify_customer_booking_edited" value="1" <?php checked( ! empty( $settings['notify_customer_booking_edited'] ) ); ?>></td>
										</tr>
										<tr>
											<td>Booking cancelled / deleted</td>
											<td><input type="checkbox" name="notify_admin_booking_cancelled" value="1" <?php checked( ! empty( $settings['notify_admin_booking_cancelled'] ) ); ?>></td>
											<td><input type="checkbox" name="notify_staff_booking_cancelled" value="1" <?php checked( ! empty( $settings['notify_staff_booking_cancelled'] ) ); ?>></td>
											<td><input type="checkbox" name="notify_customer_booking_cancelled" value="1" <?php checked( ! empty( $settings['notify_customer_booking_cancelled'] ) ); ?>></td>
										</tr>
										<tr>
											<td>Customer changed appointment</td>
											<td><input type="checkbox" name="notify_admin_booking_rescheduled" value="1" <?php checked( ! empty( $settings['notify_admin_booking_rescheduled'] ) ); ?>></td>
											<td><input type="checkbox" name="notify_staff_booking_rescheduled" value="1" <?php checked( ! empty( $settings['notify_staff_booking_rescheduled'] ) ); ?>></td>
											<td><input type="checkbox" name="notify_customer_booking_rescheduled" value="1" <?php checked( ! empty( $settings['notify_customer_booking_rescheduled'] ) ); ?>></td>
										</tr>
										<tr>
											<td>Consultant changed</td>
											<td>&mdash;</td>
											<td><input type="checkbox" name="notify_staff_consultant_changed" value="1" <?php checked( ! empty( $settings['notify_staff_consultant_changed'] ) ); ?>></td>
											<td><input type="checkbox" name="notify_customer_consultant_changed" value="1" <?php checked( ! empty( $settings['notify_customer_consultant_changed'] ) ); ?>></td>
										</tr>
									</tbody>
								</table>
								<div style="margin-top:18px;padding:14px;border:1px solid #dcdcde;background:#fff;">
									<strong>Automatic customer reminder</strong>
									<p style="margin:6px 0 10px;color:#666;">Sends one reminder to confirmed bookings before the appointment. The email repeats the current consultant and appointment details and shows a prominent cancellation button while online cancellation is still available. Bookings made after their reminder point are not sent an immediate duplicate.</p>
									<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;">
										<p><label class="rgl-inline-toggle">Send automatic reminders <input type="checkbox" name="reminder_enabled" value="1" <?php checked( ! empty( $settings['reminder_enabled'] ) ); ?>></label></p>
										<p><label>Hours before appointment<br><input type="number" min="1" max="168" step="1" name="reminder_hours_before" value="<?php echo esc_attr( isset( $settings['reminder_hours_before'] ) ? absint( $settings['reminder_hours_before'] ) : 24 ); ?>"></label><br><span style="color:#666;font-size:12px;">24 = approximately the same time on the previous day. WordPress cron runs hourly, so delivery may be slightly later.</span></p>
									</div>
								</div>
							</div>

							<div class="rgl-full" style="margin-top:18px;padding:14px;border:1px solid #dcdcde;background:#fff;">
								<strong>Customer appointment changes</strong>
								<p style="margin:6px 0 10px;color:#666;">Adds a signed Change appointment link to customer emails and the booking summary. The customer can choose another available date and time without creating a second booking. Service, communication method and consultant stay unchanged.</p>
								<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;">
									<p><label class="rgl-inline-toggle">Allow customers to change appointments online <input type="checkbox" name="reschedule_enabled" value="1" <?php checked( ! empty( $settings['reschedule_enabled'] ) ); ?>></label></p>
									<p><label>Changes close this many hours before<br><input type="number" min="0" max="168" step="1" name="reschedule_min_hours_notice" value="<?php echo esc_attr( isset( $settings['reschedule_min_hours_notice'] ) ? absint( $settings['reschedule_min_hours_notice'] ) : 1 ); ?>"></label></p>
								</div>
							</div>

							<div class="rgl-full" style="margin-top:18px;">
								<strong>Data retention</strong>
								<p style="margin:6px 0 10px;color:#666;">Automatically tidies old booking data. <strong>Off by default</strong> - nothing is removed until you switch it on. Two stages: first the contact details (email, phone, address) are stripped while the name and consultation record are kept; later the whole record is deleted. Applies to completed and cancelled bookings, measured from when the booking was made.</p>
								<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:10px;">
									<p><label class="rgl-inline-toggle">Enable automatic retention <input type="checkbox" name="retention_enabled" value="1" <?php checked( ! empty( $settings['retention_enabled'] ) ); ?>></label></p>
									<p><label>Remove contact details after (months)<br><input type="number" min="1" max="120" step="1" name="retention_minimise_months" value="<?php echo esc_attr( isset( $settings['retention_minimise_months'] ) ? absint( $settings['retention_minimise_months'] ) : 6 ); ?>"></label><br><span style="color:#666;font-size:12px;">Strips email, phone and address. Keeps name, dates, service and notes.</span></p>
									<p><label>Delete whole record after (months)<br><input type="number" min="1" max="240" step="1" name="retention_delete_months" value="<?php echo esc_attr( isset( $settings['retention_delete_months'] ) ? absint( $settings['retention_delete_months'] ) : 30 ); ?>"></label><br><span style="color:#666;font-size:12px;">Must be longer than the figure above. 30 = two and a half years.</span></p>
								</div>
								<?php
								$retention_log = get_option( 'rgl_booking_retention_log', array() );
								if ( is_array( $retention_log ) && ! empty( $retention_log ) ) :
									$recent_log = array_slice( $retention_log, -8 );
								?>
									<p style="margin:12px 0 4px;color:#666;font-size:12px;"><strong>Recent retention activity</strong></p>
									<ul style="margin:0;padding-left:18px;color:#666;font-size:12px;">
										<?php foreach ( array_reverse( $recent_log ) as $log_row ) : ?>
											<li><?php echo esc_html( $log_row['time'] ); ?> &mdash; <?php echo esc_html( $log_row['note'] ); ?></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>

							<div class="rgl-full" style="margin-top:18px;">
								<strong>Booking availability rules</strong>
								<p style="margin:6px 0 10px;color:#666;">When customers can book. Working days are Monday-Friday only. Same-day and next-working-day bookings are never offered. UK bank holidays are auto-blocked. The cut-off hour controls when "next working day" advances: at this time on any working day, the picker rolls forward by one day.</p>
								<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;">
									<p><label>Daily cut-off hour (24h)<br><input type="number" min="0" max="23" step="1" name="availability_cutoff_hour" value="<?php echo esc_attr( isset( $settings['availability_cutoff_hour'] ) ? absint( $settings['availability_cutoff_hour'] ) : 16 ); ?>"></label><br><span style="color:#666;font-size:12px;">At this hour, "next working day" advances. 16 = 4pm.</span></p>
									<p><label>Booking window (weeks ahead)<br><input type="number" min="1" max="12" step="1" name="availability_weeks_ahead" value="<?php echo esc_attr( isset( $settings['availability_weeks_ahead'] ) ? absint( $settings['availability_weeks_ahead'] ) : 2 ); ?>"></label><br><span style="color:#666;font-size:12px;">2 = current week + next week. Rolls every Friday at cut-off.</span></p>
									<p style="grid-column:1/-1;"><label class="rgl-inline-toggle">Block the first working day after a 3+ day break (e.g. Tuesday after Easter Monday) <input type="checkbox" name="availability_block_after_long_break" value="1" <?php checked( ! empty( $settings['availability_block_after_long_break'] ) ); ?>></label></p>
								</div>
							</div>

							<div class="rgl-full" style="margin-top:18px;">
								<strong>Anti-abuse</strong>
								<p style="margin:6px 0 10px;color:#666;">Defences against competitor lock-out attempts and fake bookings. The per-email rate limit blocks the same email address from making an unreasonable number of bookings in a short window. Manual approval mode pauses all new bookings as "pending approval" until you click the approve link in the admin email - useful when you're seeing suspicious activity. Both apply only to customer-facing bookings; admin-created bookings are never rate-limited or held.</p>
								<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;">
									<p style="grid-column:1/-1;"><label class="rgl-inline-toggle">Enable per-email rate limit <input type="checkbox" name="rate_limit_per_email_enabled" value="1" <?php checked( ! empty( $settings['rate_limit_per_email_enabled'] ) ); ?>></label></p>
									<p><label>Maximum bookings per email<br><input type="number" min="1" max="50" step="1" name="rate_limit_per_email_max" value="<?php echo esc_attr( isset( $settings['rate_limit_per_email_max'] ) ? absint( $settings['rate_limit_per_email_max'] ) : 3 ); ?>"></label></p>
									<p><label>Within this many days<br><input type="number" min="1" max="365" step="1" name="rate_limit_per_email_days" value="<?php echo esc_attr( isset( $settings['rate_limit_per_email_days'] ) ? absint( $settings['rate_limit_per_email_days'] ) : 30 ); ?>"></label></p>
									<p style="grid-column:1/-1;"><label class="rgl-inline-toggle">Manual approval required for new bookings <input type="checkbox" name="manual_approval_enabled" value="1" <?php checked( ! empty( $settings['manual_approval_enabled'] ) ); ?>></label><br><span style="color:#666;font-size:12px;">When on, new customer bookings sit as "pending approval" until you click the approve link in the admin email. Customers see a "Booking received" confirmation, but their calendar invite is held until you approve.</span></p>
									<p style="grid-column:1/-1;margin-bottom:0;"><strong>No-show safeguard</strong></p>
									<p style="grid-column:1/-1;"><label class="rgl-inline-toggle">Hold repeat no-show bookings for human review <input type="checkbox" name="no_show_safeguard_enabled" value="1" <?php checked( ! empty( $settings['no_show_safeguard_enabled'] ) ); ?>></label><br><span style="color:#666;font-size:12px;">This never rejects a booking automatically. It changes the new booking to Pending approval so an administrator makes the final decision.</span></p>
									<p><label>No-shows before review<br><input type="number" min="1" max="10" step="1" name="no_show_threshold" value="<?php echo esc_attr( isset( $settings['no_show_threshold'] ) ? absint( $settings['no_show_threshold'] ) : 2 ); ?>"></label></p>
									<p><label>Look-back period (days)<br><input type="number" min="1" max="730" step="1" name="no_show_window_days" value="<?php echo esc_attr( isset( $settings['no_show_window_days'] ) ? absint( $settings['no_show_window_days'] ) : 180 ); ?>"></label></p>
									<p><label>Manual block duration (days)<br><input type="number" min="1" max="730" step="1" name="no_show_block_days" value="<?php echo esc_attr( isset( $settings['no_show_block_days'] ) ? absint( $settings['no_show_block_days'] ) : 365 ); ?>"></label><br><span style="color:#666;font-size:12px;">Used only when an administrator deliberately clicks Block future bookings. The stored block is a salted email hash and expires automatically.</span></p>
									<p style="grid-column:1/-1;color:#666;font-size:12px;">This safeguard is managed internally and is not added to the booking form or customer emails. Keep the main website privacy notice and internal records appropriate for how attendance history is used.</p>
									<?php if ( empty( $settings['retention_enabled'] ) ) : ?><p style="grid-column:1/-1;margin:0;padding:10px 12px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;border-radius:6px;"><strong>Privacy warning:</strong> automatic retention is currently disabled. Enable it above, or document and operate another deletion process, so attendance records are not kept longer than necessary.</p><?php endif; ?>
									<p style="grid-column:1/-1;"><label>Blocked email addresses (one per line)<br><textarea name="blocked_emails" rows="4" style="width:100%;font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:13px;" placeholder="sales@example.com&#10;hello@spammy-supplier.co.uk"><?php echo esc_textarea( isset( $settings['blocked_emails'] ) ? $settings['blocked_emails'] : '' ); ?></textarea></label><br><span style="color:#666;font-size:12px;">Customers using these addresses will be silently rejected. Using the "Mark as Spam" action on a booking adds the address here automatically. Invalid lines are dropped on save.</span></p>
								</div>
							</div>

							<div class="rgl-full" style="margin-top:18px;">
								<strong>Content Research export</strong>
								<p style="margin:6px 0 10px;color:#666;">The prompt below is downloaded alongside the CSV when you use the Content Research export. Paste both into ChatGPT or Claude to get an analysis of conversation patterns. Edit the prompt to refine the analysis style over time.</p>
								<textarea name="content_research_prompt" rows="10" style="width:100%;box-sizing:border-box;font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:13px;"><?php echo esc_textarea( isset( $settings['content_research_prompt'] ) ? $settings['content_research_prompt'] : '' ); ?></textarea>
							</div>
						</div>
						<p><button class="button button-primary" type="submit">Save Settings</button></p>
					</form>
				<?php endif; ?>


				<?php if ( 'style' === $tab ) : ?>
					<form method="post" class="rgl-card rgl-style-engine-form">
						<?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?>
						<input type="hidden" name="rgl_booking_action" value="save_style_settings">
						<div class="rgl-admin-toolbar">
							<div>
								<h3 style="margin:0;">Form Style</h3>
								<p style="margin:6px 0 0;color:#666;">Control the front-end booking form layout, colours, spacing, borders, typography and buttons.</p>
							</div>
							<p style="display:flex;gap:8px;margin:0;flex-wrap:wrap;"><button class="button button-primary" type="submit" onclick="this.form.querySelector('[name=rgl_booking_action]').value='save_style_settings';">Save Form Style</button><button class="button button-secondary" type="submit" onclick="this.form.querySelector('[name=rgl_booking_action]').value='reset_style_settings'; return confirm('Reset all Form Style tab values to their defaults?');">Reset Form Styles</button></p>
						</div>


						<div class="rgl-style-section">
							<h3>Layout & Typography</h3>
							<div class="rgl-grid-admin rgl-grid-admin--2">
								<p><label>Form Layout<br><select name="style_form_layout"><?php $current_form_layout = $this->normalize_form_layout_mode( isset( $settings['style_form_layout'] ) ? $settings['style_form_layout'] : '' ); foreach ( $this->form_layout_options() as $layout_value => $layout_label ) : ?><option value="<?php echo esc_attr( $layout_value ); ?>" <?php selected( $current_form_layout, $layout_value ); ?>><?php echo esc_html( $layout_label ); ?></option><?php endforeach; ?></select><span class="rgl-style-help">Choose whether the form uses one column or two columns. The booking fields always use Date Last order: Service, Location, Team Member, Date.</span></label></p>
								<p><label>Typography Font Family<br><input type="text" name="style_typography_font_family" value="<?php echo esc_attr( $settings['style_typography_font_family'] ); ?>" placeholder="inherit, Inter, Arial, sans-serif"><span class="rgl-style-help">Leave blank to inherit from your theme.</span></label></p>
								<p><label>Top Title Text<br><input type="text" name="form_title" value="<?php echo esc_attr( $settings['form_title'] ); ?>" placeholder="Book an Appointment"></label></p>
								<p><label>Top Intro Text<br><input type="text" name="form_intro" value="<?php echo esc_attr( $settings['form_intro'] ); ?>" placeholder="Choose a service, date and available time."></label></p>
							</div>
						</div>

						<div class="rgl-style-section">
							<h3>Form Shell & Title</h3>
							<div class="rgl-grid-admin rgl-grid-admin--2">
								<p><label>Title Padding<br><input type="text" name="style_form_title_padding" value="<?php echo esc_attr( $settings['style_form_title_padding'] ); ?>" placeholder="0 0 12px 0"></label></p>
								<p><label>Form Title Colour<br><input type="color" name="style_form_title_color" value="<?php echo esc_attr( $settings['style_form_title_color'] ); ?>"></label></p>
								<p><label>Form Title Background Colour<br><input type="text" name="style_form_title_background" value="<?php echo esc_attr( $settings['style_form_title_background'] ); ?>" placeholder="#ffffff or transparent"></label></p>
								<p><label>Title Font Size<br><input type="text" name="style_form_title_font_size" value="<?php echo esc_attr( $settings['style_form_title_font_size'] ); ?>" placeholder="24px or clamp(22px, 3vw, 36px)"></label></p>
								<p><label>Title Font Weight<br><input type="text" name="style_form_title_font_weight" value="<?php echo esc_attr( $settings['style_form_title_font_weight'] ); ?>" placeholder="400, 600, 700"></label></p>
								<p><label>Title Letter Spacing<br><input type="text" name="style_form_title_letter_spacing" value="<?php echo esc_attr( $settings['style_form_title_letter_spacing'] ); ?>" placeholder="0 or 0.02em"></label></p>
								<p><label>Intro Font Size<br><input type="text" name="style_form_intro_font_size" value="<?php echo esc_attr( $settings['style_form_intro_font_size'] ); ?>" placeholder="16px or clamp(14px, 1vw, 18px)"></label></p>
								<p><label>Intro Font Weight<br><input type="text" name="style_form_intro_font_weight" value="<?php echo esc_attr( $settings['style_form_intro_font_weight'] ); ?>" placeholder="400"></label></p>
								<p><label>Intro Letter Spacing<br><input type="text" name="style_form_intro_letter_spacing" value="<?php echo esc_attr( $settings['style_form_intro_letter_spacing'] ); ?>" placeholder="0 or 0.02em"></label></p>
								<p><label>Form Padding<br><input type="text" name="style_form_padding" value="<?php echo esc_attr( $settings['style_form_padding'] ); ?>" placeholder="20px"></label></p>
								<p><label>Column Gap<br><input type="text" name="style_form_column_gap" value="<?php echo esc_attr( $settings['style_form_column_gap'] ); ?>" placeholder="16px"><span class="rgl-style-help">Horizontal spacing between form fields in two-column layouts.</span></label></p>
								<p><label>Row Gap<br><input type="text" name="style_form_row_gap" value="<?php echo esc_attr( $settings['style_form_row_gap'] ); ?>" placeholder="16px"><span class="rgl-style-help">Vertical spacing between rows of form fields.</span></label></p>
								<p><label>Form Border Radius<br><input type="text" name="style_form_border_radius" value="<?php echo esc_attr( $settings['style_form_border_radius'] ); ?>" placeholder="12px"></label></p>
								<p><label>Form Border Width<br><input type="text" name="style_form_border_width" value="<?php echo esc_attr( $settings['style_form_border_width'] ); ?>" placeholder="1px"></label></p>
								<p><label>Form Border Colour<br><input type="color" name="style_form_border_color" value="<?php echo esc_attr( $settings['style_form_border_color'] ); ?>"></label></p>
							</div>
						</div>

						<div class="rgl-style-section">
							<h3>Labels & Fields</h3>
							<div class="rgl-grid-admin rgl-grid-admin--2">
								<p><label>Field Label Padding<br><input type="text" name="style_field_label_padding" value="<?php echo esc_attr( $settings['style_field_label_padding'] ); ?>" placeholder="0"></label></p>
								<p><label>Field Label Colour<br><input type="color" name="style_field_label_color" value="<?php echo esc_attr( $settings['style_field_label_color'] ); ?>"></label></p>
								<p><label>Field Label Background Colour<br><input type="text" name="style_field_label_background" value="<?php echo esc_attr( $settings['style_field_label_background'] ); ?>" placeholder="transparent"></label></p>
								<p><label>Label Font Size<br><input type="text" name="style_label_font_size" value="<?php echo esc_attr( $settings['style_label_font_size'] ); ?>" placeholder="16px or clamp(14px, 1vw, 18px)"><span class="rgl-style-help">CSS clamp() values are supported.</span></label></p>
								<p><label>Label Font Weight<br><input type="text" name="style_label_font_weight" value="<?php echo esc_attr( $settings['style_label_font_weight'] ); ?>" placeholder="400, 600, 700"></label></p>
								<p><label>Label Letter Spacing<br><input type="text" name="style_label_letter_spacing" value="<?php echo esc_attr( $settings['style_label_letter_spacing'] ); ?>" placeholder="0 or 0.02em"></label></p>
								<p><label>Label Border Radius<br><input type="text" name="style_label_border_radius" value="<?php echo esc_attr( $settings['style_label_border_radius'] ); ?>" placeholder="0"></label></p>
								<p><label>Label Border Width<br><input type="text" name="style_label_border_width" value="<?php echo esc_attr( $settings['style_label_border_width'] ); ?>" placeholder="0"></label></p>
								<p><label>Label Border Colour<br><input type="text" name="style_label_border_color" value="<?php echo esc_attr( $settings['style_label_border_color'] ); ?>" placeholder="transparent"></label></p>
								<p><label>Field Padding<br><input type="text" name="style_field_padding" value="<?php echo esc_attr( $settings['style_field_padding'] ); ?>" placeholder="10px"></label></p>
								<p><label>Field Font Size<br><input type="text" name="style_field_font_size" value="<?php echo esc_attr( $settings['style_field_font_size'] ); ?>" placeholder="16px or clamp(14px, 1vw, 18px)"><span class="rgl-style-help">CSS clamp() values are supported.</span></label></p>
								<p><label>Field Font Weight<br><input type="text" name="style_field_font_weight" value="<?php echo esc_attr( $settings['style_field_font_weight'] ); ?>" placeholder="400"></label></p>
								<p><label>Field Letter Spacing<br><input type="text" name="style_field_letter_spacing" value="<?php echo esc_attr( $settings['style_field_letter_spacing'] ); ?>" placeholder="0 or 0.02em"></label></p>
								<p><label>Field Border Radius<br><input type="text" name="style_field_border_radius" value="<?php echo esc_attr( $settings['style_field_border_radius'] ); ?>" placeholder="4px"></label></p>
								<p><label>Field Border Width<br><input type="text" name="style_field_border_width" value="<?php echo esc_attr( $settings['style_field_border_width'] ); ?>" placeholder="1px"></label></p>
								<p><label>Field Border Colour<br><input type="color" name="style_field_border_color" value="<?php echo esc_attr( $settings['style_field_border_color'] ); ?>"></label></p>
							</div>
						</div>

						<div class="rgl-style-section">
							<h3>Button</h3>
							<div class="rgl-grid-admin rgl-grid-admin--2">
								<p><label>Button Text<br><input type="text" name="style_button_text" value="<?php echo esc_attr( $settings['style_button_text'] ); ?>"></label></p>
								<p><label>Button Font Size<br><input type="text" name="style_button_font_size" value="<?php echo esc_attr( $settings['style_button_font_size'] ); ?>" placeholder="16px or clamp(14px, 1vw, 18px)"></label></p>
								<p><label>Button Font Weight<br><input type="text" name="style_button_font_weight" value="<?php echo esc_attr( $settings['style_button_font_weight'] ); ?>" placeholder="400, 600, 700"></label></p>
								<p><label>Button Letter Spacing<br><input type="text" name="style_button_letter_spacing" value="<?php echo esc_attr( $settings['style_button_letter_spacing'] ); ?>" placeholder="0 or 0.04em"></label></p>
								<p><label>Button Text Colour<br><input type="color" name="style_button_text_color" value="<?php echo esc_attr( $settings['style_button_text_color'] ); ?>"></label></p>
								<p><label>Button Background Colour<br><input type="color" name="style_button_background" value="<?php echo esc_attr( $settings['style_button_background'] ); ?>"></label></p>
								<p><label>Button Hover Text Colour<br><input type="color" name="style_button_hover_text_color" value="<?php echo esc_attr( $settings['style_button_hover_text_color'] ); ?>"></label></p>
								<p><label>Button Hover Background Colour<br><input type="color" name="style_button_hover_background" value="<?php echo esc_attr( $settings['style_button_hover_background'] ); ?>"></label></p>
								<p><label>Button Border Radius<br><input type="text" name="style_button_border_radius" value="<?php echo esc_attr( $settings['style_button_border_radius'] ); ?>" placeholder="8px"></label></p>
								<p><label>Button Border Width<br><input type="text" name="style_button_border_width" value="<?php echo esc_attr( $settings['style_button_border_width'] ); ?>" placeholder="0"></label></p>
								<p><label>Button Border Colour<br><input type="text" name="style_button_border_color" value="<?php echo esc_attr( $settings['style_button_border_color'] ); ?>" placeholder="transparent"></label></p>
								<p><label>Button Padding<br><input type="text" name="style_button_padding" value="<?php echo esc_attr( $settings['style_button_padding'] ); ?>" placeholder="12px 16px"></label></p>
								<p><label>Button Margin<br><input type="text" name="style_button_margin" value="<?php echo esc_attr( $settings['style_button_margin'] ); ?>" placeholder="16px 0 0 0"></label></p>
							</div>
						</div>

						<div class="rgl-style-section">
							<h3>Reset Form Button</h3>
							<div class="rgl-grid-admin rgl-grid-admin--2">
								<p><label>Reset Button Text<br><input type="text" name="style_reset_button_text" value="<?php echo esc_attr( $settings['style_reset_button_text'] ); ?>"></label></p>
								<p><label>Reset Button Font Size<br><input type="text" name="style_reset_button_font_size" value="<?php echo esc_attr( $settings['style_reset_button_font_size'] ); ?>" placeholder="16px or clamp(14px, 1vw, 18px)"></label></p>
								<p><label>Reset Button Font Weight<br><input type="text" name="style_reset_button_font_weight" value="<?php echo esc_attr( $settings['style_reset_button_font_weight'] ); ?>" placeholder="400, 600, 700"></label></p>
								<p><label>Reset Button Letter Spacing<br><input type="text" name="style_reset_button_letter_spacing" value="<?php echo esc_attr( $settings['style_reset_button_letter_spacing'] ); ?>" placeholder="0 or 0.04em"></label></p>
								<p><label>Reset Button Text Colour<br><input type="color" name="style_reset_button_text_color" value="<?php echo esc_attr( $settings['style_reset_button_text_color'] ); ?>"></label></p>
								<p><label>Reset Button Background Colour<br><input type="color" name="style_reset_button_background" value="<?php echo esc_attr( $settings['style_reset_button_background'] ); ?>"></label></p>
								<p><label>Reset Button Hover Text Colour<br><input type="color" name="style_reset_button_hover_text_color" value="<?php echo esc_attr( $settings['style_reset_button_hover_text_color'] ); ?>"></label></p>
								<p><label>Reset Button Hover Background Colour<br><input type="color" name="style_reset_button_hover_background" value="<?php echo esc_attr( $settings['style_reset_button_hover_background'] ); ?>"></label></p>
								<p><label>Reset Button Border Radius<br><input type="text" name="style_reset_button_border_radius" value="<?php echo esc_attr( $settings['style_reset_button_border_radius'] ); ?>" placeholder="8px"></label></p>
								<p><label>Reset Button Border Weight<br><input type="text" name="style_reset_button_border_width" value="<?php echo esc_attr( $settings['style_reset_button_border_width'] ); ?>" placeholder="1px"></label></p>
								<p><label>Reset Button Border Colour<br><input type="color" name="style_reset_button_border_color" value="<?php echo esc_attr( $settings['style_reset_button_border_color'] ); ?>"></label></p>
								<p><label>Reset Button Padding<br><input type="text" name="style_reset_button_padding" value="<?php echo esc_attr( $settings['style_reset_button_padding'] ); ?>" placeholder="12px 16px"></label></p>
							</div>
						</div>


						<p class="rgl-save-row"><button class="button button-primary" type="submit" onclick="this.form.querySelector('[name=rgl_booking_action]').value='save_style_settings';">Save Form Style</button></p>
					</form>
				<?php endif; ?>

				<?php if ( 'locations' === $tab ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=appt-booker&tab=locations' ) ); ?>" class="rgl-card rgl-repeater-form" data-repeater-type="locations">
						<?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?>
						<input type="hidden" name="rgl_booking_action" value="save_locations">
						<input type="hidden" name="locations_payload" value="">
						<div class="rgl-admin-toolbar">
							<div>
								<h3 style="margin:0;">Locations</h3>
								<p style="margin:6px 0 0;color:#666;">Create places such as venues, clinics, offices, or rooms. Services and team members can then be linked to these.</p>
							</div>
							<button class="button button-secondary rgl-add-row" type="button">+ Add Location</button>
						</div>
						<div class="rgl-repeater-list" data-list="locations">
							<?php
							$location_count = max( 1, count( $locations ) );
							for ( $i = 0; $i < $location_count; $i++ ) :
								$row = isset( $locations[ $i ] ) ? $locations[ $i ] : array(
									'id'       => '',
									'name'     => '',
									'address'  => '',
									'postcode' => '',
									'active'   => 1,
								);
							?>
							<div class="rgl-accordion-item <?php echo '' === $row['name'] ? 'is-open' : ''; ?>" data-item="locations">
								<div class="rgl-accordion-header">
									<div class="rgl-accordion-title-wrap">
										<strong class="rgl-accordion-title"><?php echo esc_html( '' !== $row['name'] ? $row['name'] : 'New Location' ); ?></strong>
										<span class="rgl-accordion-subtitle">Venue, office, room, branch, or other booking place</span>
									</div>
									<div class="rgl-item-actions">
										<button class="button rgl-move-up" type="button">↑</button>
										<button class="button rgl-move-down" type="button">↓</button>
										<button class="button rgl-duplicate-row" type="button">Duplicate</button>
										<button class="button rgl-delete-row" type="button">Delete</button>
										<span class="rgl-accordion-icon">+</span>
									</div>
								</div>
								<div class="rgl-accordion-content">
									<input type="hidden" data-name="locations[%d][id]" name="locations[<?php echo esc_attr( $i ); ?>][id]" value="<?php echo esc_attr( $row['id'] ); ?>">
									<div class="rgl-grid-admin rgl-grid-admin--3">
										<p><label>Location Name<br><input class="rgl-item-name" type="text" data-name="locations[%d][name]" name="locations[<?php echo esc_attr( $i ); ?>][name]" value="<?php echo esc_attr( $row['name'] ); ?>"></label></p>
										<p><label>Postcode<br><input type="text" data-name="locations[%d][postcode]" name="locations[<?php echo esc_attr( $i ); ?>][postcode]" value="<?php echo esc_attr( isset( $row['postcode'] ) ? $row['postcode'] : '' ); ?>" placeholder="e.g. LE7 2BU"></label></p>
										<p><label class="rgl-inline-toggle">Active <input type="checkbox" data-name="locations[%d][active]" name="locations[<?php echo esc_attr( $i ); ?>][active]" value="1" <?php checked( ! empty( $row['active'] ) ); ?>></label></p>
									</div>
									<div class="rgl-grid-admin rgl-grid-admin--2 rgl-content-fields" style="margin-top:12px;">
										<p class="rgl-full"><label>Address<br><textarea rows="3" data-name="locations[%d][address]" name="locations[<?php echo esc_attr( $i ); ?>][address]" placeholder="Street address, building, room, or venue details."><?php echo esc_textarea( isset( $row['address'] ) ? $row['address'] : '' ); ?></textarea></label></p>
										<p class="rgl-full"><label>Description<br><textarea rows="4" data-name="locations[%d][description]" name="locations[<?php echo esc_attr( $i ); ?>][description]" placeholder="Shown in Elementor Loop Grids and Single Templates."><?php echo esc_textarea( isset( $row['description'] ) ? $row['description'] : '' ); ?></textarea></label></p>
										<div class="rgl-image-picker">
											<label>Image<br>
												<input type="hidden" class="rgl-image-id" data-name="locations[%d][image_id]" name="locations[<?php echo esc_attr( $i ); ?>][image_id]" value="<?php echo esc_attr( isset( $row['image_id'] ) ? absint( $row['image_id'] ) : 0 ); ?>">
												<input type="hidden" class="rgl-image-url" data-name="locations[%d][image_url]" name="locations[<?php echo esc_attr( $i ); ?>][image_url]" value="<?php echo esc_attr( isset( $row['image_url'] ) ? $row['image_url'] : '' ); ?>">
											</label>
											<div class="rgl-image-preview"><?php $img_url = ! empty( $row['image_id'] ) ? wp_get_attachment_image_url( absint( $row['image_id'] ), 'thumbnail' ) : ( isset( $row['image_url'] ) ? $row['image_url'] : '' ); if ( $img_url ) : ?><img src="<?php echo esc_url( $img_url ); ?>" alt="" style="max-width:120px;height:auto;display:block;margin-bottom:8px;"><?php endif; ?></div>
											<button class="button rgl-select-image" type="button">Choose Image</button>
											<button class="button rgl-remove-image" type="button">Remove</button>
										</div>
										<?php $this->render_admin_extra_image_pickers( 'locations', $i, $row ); ?>
									</div>
									<?php $this->render_admin_item_taxonomy_custom_fields( self::TAX_REL_LOCATION, 'locations', $i, isset( $row['taxonomy_fields'] ) ? $row['taxonomy_fields'] : array() ); ?>
								</div>
							</div>
							<?php endfor; ?>
						</div>
						<script type="text/template" id="rgl-locations-template">
							<div class="rgl-accordion-item" data-item="locations">
								<div class="rgl-accordion-header">
									<div class="rgl-accordion-title-wrap">
										<strong class="rgl-accordion-title">New Location</strong>
										<span class="rgl-accordion-subtitle">Venue, office, room, branch, or other booking place</span>
									</div>
									<div class="rgl-item-actions">
										<button class="button rgl-move-up" type="button">↑</button>
										<button class="button rgl-move-down" type="button">↓</button>
										<button class="button rgl-duplicate-row" type="button">Duplicate</button>
										<button class="button rgl-delete-row" type="button">Delete</button>
										<span class="rgl-accordion-icon">+</span>
									</div>
								</div>
								<div class="rgl-accordion-content">
									<input type="hidden" data-name="locations[%d][id]" name="locations[__INDEX__][id]" value="">
									<div class="rgl-grid-admin rgl-grid-admin--3">
										<p><label>Location Name<br><input class="rgl-item-name" type="text" data-name="locations[%d][name]" name="locations[__INDEX__][name]" value=""></label></p>
										<p><label>Postcode<br><input type="text" data-name="locations[%d][postcode]" name="locations[__INDEX__][postcode]" value="" placeholder="e.g. LE7 2BU"></label></p>
										<p><label class="rgl-inline-toggle">Active <input type="checkbox" data-name="locations[%d][active]" name="locations[__INDEX__][active]" value="1" checked></label></p>
									</div>
									<div class="rgl-grid-admin rgl-grid-admin--2 rgl-content-fields" style="margin-top:12px;">
										<p class="rgl-full"><label>Address<br><textarea rows="3" data-name="locations[%d][address]" name="locations[__INDEX__][address]" placeholder="Street address, building, room, or venue details."></textarea></label></p>
										<p class="rgl-full"><label>Description<br><textarea rows="4" data-name="locations[%d][description]" name="locations[__INDEX__][description]" placeholder="Shown in Elementor Loop Grids and Single Templates."></textarea></label></p>
										<div class="rgl-image-picker">
											<label>Image<br>
												<input type="hidden" class="rgl-image-id" data-name="locations[%d][image_id]" name="locations[__INDEX__][image_id]" value="0">
												<input type="hidden" class="rgl-image-url" data-name="locations[%d][image_url]" name="locations[__INDEX__][image_url]" value="">
											</label>
											<div class="rgl-image-preview"></div>
											<button class="button rgl-select-image" type="button">Choose Image</button>
											<button class="button rgl-remove-image" type="button">Remove</button>
										</div>
										<?php $this->render_admin_extra_image_pickers_template( 'locations' ); ?>
									</div>
								</div>
							</div>
						</script>
						<p class="rgl-save-row"><button class="button button-primary" type="submit">Save Locations</button></p>
					</form>
				<?php endif; ?>

				<?php if ( 'staff' === $tab ) : ?>
					<form method="post" class="rgl-card rgl-repeater-form" data-repeater-type="staff">
						<?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?>
						<input type="hidden" name="rgl_booking_action" value="save_staff">
						<div class="rgl-admin-toolbar">
							<div>
								<h3 style="margin:0;">Staff</h3>
								<p style="margin:6px 0 0;color:#666;">Add, duplicate, remove and reorder staff members.</p>
							</div>
							<button class="button button-secondary rgl-add-row" type="button">+ Add Staff Member</button>
						</div>
						<div class="rgl-repeater-list" data-list="staff">
							<?php
							$count = max( 1, count( $staff ) );
							for ( $i = 0; $i < $count; $i++ ) :
								$row = isset( $staff[ $i ] ) ? $staff[ $i ] : array( 'id' => '', 'name' => '', 'email' => '', 'active' => 1, 'hours' => $this->default_hours(), 'date_exceptions' => array() );
								$row_hours = isset( $row['hours'] ) && is_array( $row['hours'] ) ? $row['hours'] : $this->default_hours();
								?>
								<div class="rgl-accordion-item" data-item="staff">
									<div class="rgl-accordion-header">
										<div class="rgl-accordion-title-wrap">
											<strong class="rgl-accordion-title"><?php echo esc_html( isset( $row['name'] ) && '' !== $row['name'] ? $row['name'] : 'New Staff Member' ); ?></strong>
											<span class="rgl-accordion-subtitle">Staff member details and general weekly availability</span>
										</div>
										<div class="rgl-item-actions">
											<button class="button rgl-move-up" type="button">↑</button>
											<button class="button rgl-move-down" type="button">↓</button>
											<button class="button rgl-duplicate-row" type="button">Duplicate</button>
											<button class="button rgl-delete-row" type="button">Delete</button>
											<span class="rgl-accordion-icon">+</span>
										</div>
									</div>
									<div class="rgl-accordion-content">
										<input type="hidden" data-name="staff[%d][id]" name="staff[<?php echo esc_attr( $i ); ?>][id]" value="<?php echo esc_attr( isset( $row['id'] ) ? $row['id'] : '' ); ?>">
										<div class="rgl-grid-admin rgl-grid-admin--3">
											<p><label>Name<br><input class="rgl-item-name" type="text" data-name="staff[%d][name]" name="staff[<?php echo esc_attr( $i ); ?>][name]" value="<?php echo esc_attr( isset( $row['name'] ) ? $row['name'] : '' ); ?>"></label></p>
											<p><label>Email<br><input type="email" data-name="staff[%d][email]" name="staff[<?php echo esc_attr( $i ); ?>][email]" value="<?php echo esc_attr( isset( $row['email'] ) ? $row['email'] : '' ); ?>"></label></p>
											<p><label>Max bookings per day<br><input type="number" min="0" step="1" data-name="staff[%d][daily_cap]" name="staff[<?php echo esc_attr( $i ); ?>][daily_cap]" value="<?php echo esc_attr( isset( $row['daily_cap'] ) ? absint( $row['daily_cap'] ) : 0 ); ?>"></label><br><span style="color:#666;font-size:12px;">0 = no limit. Overrides the global cap in Settings.</span></p>
											<p><label class="rgl-inline-toggle">Active <input type="checkbox" data-name="staff[%d][active]" name="staff[<?php echo esc_attr( $i ); ?>][active]" value="1" <?php checked( ! isset( $row['active'] ) || ! empty( $row['active'] ) ); ?>></label></p>
										</div>
									<div class="rgl-grid-admin rgl-grid-admin--2 rgl-content-fields" style="margin-top:12px;">
										<p class="rgl-full"><label>Description<br><textarea rows="4" data-name="staff[%d][description]" name="staff[<?php echo esc_attr( $i ); ?>][description]" placeholder="Shown in Elementor Loop Grids and Single Templates."><?php echo esc_textarea( isset( $row['description'] ) ? $row['description'] : '' ); ?></textarea></label></p>
										<?php $this->render_admin_item_taxonomy_custom_fields( self::TAX_REL_TEAM, 'staff', $i, isset( $row['taxonomy_fields'] ) ? $row['taxonomy_fields'] : array() ); ?>
										<div class="rgl-image-picker">
											<label>Image<br>
												<input type="hidden" class="rgl-image-id" data-name="staff[%d][image_id]" name="staff[<?php echo esc_attr( $i ); ?>][image_id]" value="<?php echo esc_attr( isset( $row['image_id'] ) ? absint( $row['image_id'] ) : 0 ); ?>">
												<input type="hidden" class="rgl-image-url" data-name="staff[%d][image_url]" name="staff[<?php echo esc_attr( $i ); ?>][image_url]" value="<?php echo esc_attr( isset( $row['image_url'] ) ? $row['image_url'] : '' ); ?>">
											</label>
											<div class="rgl-image-preview"><?php $img_url = ! empty( $row['image_id'] ) ? wp_get_attachment_image_url( absint( $row['image_id'] ), 'thumbnail' ) : ( isset( $row['image_url'] ) ? $row['image_url'] : '' ); if ( $img_url ) : ?><img src="<?php echo esc_url( $img_url ); ?>" alt="" style="max-width:120px;height:auto;display:block;margin-bottom:8px;"><?php endif; ?></div>
											<button class="button rgl-select-image" type="button">Choose Image</button>
											<button class="button rgl-remove-image" type="button">Remove</button>
										</div>
										<?php $this->render_admin_extra_image_pickers( 'staff', $i, $row ); ?>
									</div>

										<div class="rgl-days">
											<?php foreach ( $days as $day_key => $day_label ) :
												$day_data = isset( $row_hours[ $day_key ] ) ? $row_hours[ $day_key ] : array( 'enabled' => 0, 'start' => '09:00', 'end' => '17:00' );
												?>
												<div class="rgl-day">
													<strong><?php echo esc_html( $day_label ); ?></strong>
													<p><label><input type="checkbox" data-name="staff[%d][hours][<?php echo esc_attr( $day_key ); ?>][enabled]" name="staff[<?php echo esc_attr( $i ); ?>][hours][<?php echo esc_attr( $day_key ); ?>][enabled]" value="1" <?php checked( ! empty( $day_data['enabled'] ) ); ?>> Available</label></p>
													<p><label>Start<br><input type="time" data-name="staff[%d][hours][<?php echo esc_attr( $day_key ); ?>][start]" name="staff[<?php echo esc_attr( $i ); ?>][hours][<?php echo esc_attr( $day_key ); ?>][start]" value="<?php echo esc_attr( $day_data['start'] ); ?>"></label></p>
													<p><label>End<br><input type="time" data-name="staff[%d][hours][<?php echo esc_attr( $day_key ); ?>][end]" name="staff[<?php echo esc_attr( $i ); ?>][hours][<?php echo esc_attr( $day_key ); ?>][end]" value="<?php echo esc_attr( $day_data['end'] ); ?>"></label></p>
													<p><label><input type="checkbox" data-name="staff[%d][hours][<?php echo esc_attr( $day_key ); ?>][break_enabled]" name="staff[<?php echo esc_attr( $i ); ?>][hours][<?php echo esc_attr( $day_key ); ?>][break_enabled]" value="1" <?php checked( ! empty( $day_data['break_enabled'] ) ); ?>> Break</label></p>
													<p><label>Break Start<br><input type="time" data-name="staff[%d][hours][<?php echo esc_attr( $day_key ); ?>][break_start]" name="staff[<?php echo esc_attr( $i ); ?>][hours][<?php echo esc_attr( $day_key ); ?>][break_start]" value="<?php echo esc_attr( isset( $day_data['break_start'] ) ? $day_data['break_start'] : '12:00' ); ?>"></label></p>
													<p><label>Break End<br><input type="time" data-name="staff[%d][hours][<?php echo esc_attr( $day_key ); ?>][break_end]" name="staff[<?php echo esc_attr( $i ); ?>][hours][<?php echo esc_attr( $day_key ); ?>][break_end]" value="<?php echo esc_attr( isset( $day_data['break_end'] ) ? $day_data['break_end'] : '13:00' ); ?>"></label></p>
												</div>
											<?php endforeach; ?>
										</div>

										<div class="rgl-full" style="margin-top:14px;">
										<strong>Allowed Locations</strong>
										<div class="rgl-chip-list" style="margin-top:10px;">
											<?php foreach ( $this->get_all_locations() as $location_row ) : 
												$staff_location_ids = $this->get_effective_location_ids_from_row( $row ); ?>
											<label class="rgl-chip">
												<input type="checkbox" data-name="staff[%d][location_ids][]" name="staff[<?php echo esc_attr( $i ); ?>][location_ids][]" value="<?php echo esc_attr( $location_row['id'] ); ?>" <?php checked( in_array( $location_row['id'], $staff_location_ids, true ) ); ?>>
												<span><?php echo esc_html( $location_row['name'] ); ?></span>
											</label>
											<?php endforeach; ?>
										</div>
									</div>
									<div class="rgl-exceptions">
											<div class="rgl-admin-toolbar" style="margin-top:18px;margin-bottom:12px;">
												<div>
													<strong>Days Off / Unavailable Times</strong>
													<p style="margin:4px 0 0;color:#666;">Add blocked dates or blocked time ranges. These remove availability from the normal recurring schedule.</p>
												</div>
												<button class="button rgl-add-exception" type="button">+ Add Time Off</button>
											</div>
											<div class="rgl-exception-list">
											<?php $exceptions = isset( $row['date_exceptions'] ) && is_array( $row['date_exceptions'] ) ? $row['date_exceptions'] : array(); ?>
											<?php foreach ( $exceptions as $ex_i => $exception ) : ?>
												<div class="rgl-exception-row" data-exception-row>
													<div class="rgl-exception-grid">
														<p><label>From<br><input type="date" data-ex-name="staff[%d][date_exceptions][%e][from_date]" name="staff[<?php echo esc_attr( $i ); ?>][date_exceptions][<?php echo esc_attr( $ex_i ); ?>][from_date]" value="<?php echo esc_attr( isset( $exception['from_date'] ) ? $exception['from_date'] : ( isset( $exception['date'] ) ? $exception['date'] : '' ) ); ?>"></label></p>
														<p><label>To<br><input type="date" data-ex-name="staff[%d][date_exceptions][%e][to_date]" name="staff[<?php echo esc_attr( $i ); ?>][date_exceptions][<?php echo esc_attr( $ex_i ); ?>][to_date]" value="<?php echo esc_attr( isset( $exception['to_date'] ) ? $exception['to_date'] : ( isset( $exception['date'] ) ? $exception['date'] : '' ) ); ?>"></label></p>
														<p><label>Mode<br><select class="rgl-exception-mode" data-ex-name="staff[%d][date_exceptions][%e][mode]" name="staff[<?php echo esc_attr( $i ); ?>][date_exceptions][<?php echo esc_attr( $ex_i ); ?>][mode]"><option value="off" <?php selected( isset( $exception['mode'] ) ? $exception['mode'] : 'off', 'off' ); ?>>Off all day</option><option value="block" <?php selected( ( isset( $exception['mode'] ) && 'custom' === $exception['mode'] ) ? 'block' : ( isset( $exception['mode'] ) ? $exception['mode'] : '' ), 'block' ); ?>>Blocked time range</option></select></label></p>
														<p class="rgl-exception-time-row <?php echo ( isset( $exception['mode'] ) && 'block' === $exception['mode'] || 'custom' === $exception['mode'] ) ? '' : 'is-hidden'; ?>"><label>Start<br><input type="time" data-ex-name="staff[%d][date_exceptions][%e][start]" name="staff[<?php echo esc_attr( $i ); ?>][date_exceptions][<?php echo esc_attr( $ex_i ); ?>][start]" value="<?php echo esc_attr( isset( $exception['start'] ) ? $exception['start'] : '' ); ?>"></label></p>
														<p class="rgl-exception-time-row <?php echo ( isset( $exception['mode'] ) && 'block' === $exception['mode'] || 'custom' === $exception['mode'] ) ? '' : 'is-hidden'; ?>"><label>End<br><input type="time" data-ex-name="staff[%d][date_exceptions][%e][end]" name="staff[<?php echo esc_attr( $i ); ?>][date_exceptions][<?php echo esc_attr( $ex_i ); ?>][end]" value="<?php echo esc_attr( isset( $exception['end'] ) ? $exception['end'] : '' ); ?>"></label></p>
														<p class="rgl-field--note"><label>Note<br><input type="text" data-ex-name="staff[%d][date_exceptions][%e][note]" name="staff[<?php echo esc_attr( $i ); ?>][date_exceptions][<?php echo esc_attr( $ex_i ); ?>][note]" value="<?php echo esc_attr( isset( $exception['note'] ) ? $exception['note'] : '' ); ?>" placeholder="Optional"></label></p>
														<div class="rgl-exception-actions"><button class="button rgl-duplicate-exception" type="button">Duplicate</button><button class="button rgl-delete-exception" type="button">Delete</button></div>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
										</div>
									</div>
								</div>
							<?php endfor; ?>
						</div>
						<script type="text/template" id="rgl-staff-template">
							<div class="rgl-accordion-item" data-item="staff">
								<div class="rgl-accordion-header">
									<div class="rgl-accordion-title-wrap">
										<strong class="rgl-accordion-title">New Staff Member</strong>
										<span class="rgl-accordion-subtitle">Staff member details and general weekly availability</span>
									</div>
									<div class="rgl-item-actions">
										<button class="button rgl-move-up" type="button">↑</button>
										<button class="button rgl-move-down" type="button">↓</button>
										<button class="button rgl-duplicate-row" type="button">Duplicate</button>
										<button class="button rgl-delete-row" type="button">Delete</button>
										<span class="rgl-accordion-icon">+</span>
									</div>
								</div>
								<div class="rgl-accordion-content">
									<input type="hidden" data-name="staff[%d][id]" name="staff[__INDEX__][id]" value="">
									<div class="rgl-grid-admin rgl-grid-admin--3">
										<p><label>Name<br><input class="rgl-item-name" type="text" data-name="staff[%d][name]" name="staff[__INDEX__][name]" value=""></label></p>
										<p><label>Email<br><input type="email" data-name="staff[%d][email]" name="staff[__INDEX__][email]" value=""></label></p>
										<p><label>Max bookings per day<br><input type="number" min="0" step="1" data-name="staff[%d][daily_cap]" name="staff[__INDEX__][daily_cap]" value="0"></label><br><span style="color:#666;font-size:12px;">0 = no limit. Overrides the global cap in Settings.</span></p>
										<p><label class="rgl-inline-toggle">Active <input type="checkbox" data-name="staff[%d][active]" name="staff[__INDEX__][active]" value="1" checked></label></p>
									</div>
									<div class="rgl-grid-admin rgl-grid-admin--2 rgl-content-fields" style="margin-top:12px;">
										<p class="rgl-full"><label>Description<br><textarea rows="4" data-name="staff[%d][description]" name="staff[__INDEX__][description]" placeholder="Shown in Elementor Loop Grids and Single Templates."></textarea></label></p>
										<div class="rgl-image-picker">
											<label>Image<br>
												<input type="hidden" class="rgl-image-id" data-name="staff[%d][image_id]" name="staff[__INDEX__][image_id]" value="0">
												<input type="hidden" class="rgl-image-url" data-name="staff[%d][image_url]" name="staff[__INDEX__][image_url]" value="">
											</label>
											<div class="rgl-image-preview"></div>
											<button class="button rgl-select-image" type="button">Choose Image</button>
											<button class="button rgl-remove-image" type="button">Remove</button>
										</div>
										<?php $this->render_admin_extra_image_pickers_template( 'staff' ); ?>
									</div>
									<div class="rgl-days">
										<?php foreach ( $days as $day_key => $day_label ) : ?>
										<div class="rgl-day">
											<strong><?php echo esc_html( $day_label ); ?></strong>
											<p><label><input type="checkbox" data-name="staff[%d][hours][<?php echo esc_attr( $day_key ); ?>][enabled]" name="staff[__INDEX__][hours][<?php echo esc_attr( $day_key ); ?>][enabled]" value="1" <?php checked( in_array( $day_key, array( 'mon','tue','wed','thu','fri' ), true ) ); ?>> Available</label></p>
											<p><label>Start<br><input type="time" data-name="staff[%d][hours][<?php echo esc_attr( $day_key ); ?>][start]" name="staff[__INDEX__][hours][<?php echo esc_attr( $day_key ); ?>][start]" value="09:00"></label></p>
											<p><label>End<br><input type="time" data-name="staff[%d][hours][<?php echo esc_attr( $day_key ); ?>][end]" name="staff[__INDEX__][hours][<?php echo esc_attr( $day_key ); ?>][end]" value="<?php echo esc_attr( in_array( $day_key, array( 'sat','sun' ), true ) ? '13:00' : '17:00' ); ?>"></label></p>
											<p><label><input type="checkbox" data-name="staff[%d][hours][<?php echo esc_attr( $day_key ); ?>][break_enabled]" name="staff[__INDEX__][hours][<?php echo esc_attr( $day_key ); ?>][break_enabled]" value="1"> Break</label></p>
											<p><label>Break Start<br><input type="time" data-name="staff[%d][hours][<?php echo esc_attr( $day_key ); ?>][break_start]" name="staff[__INDEX__][hours][<?php echo esc_attr( $day_key ); ?>][break_start]" value="12:00"></label></p>
											<p><label>Break End<br><input type="time" data-name="staff[%d][hours][<?php echo esc_attr( $day_key ); ?>][break_end]" name="staff[__INDEX__][hours][<?php echo esc_attr( $day_key ); ?>][break_end]" value="13:00"></label></p>
										</div>
										<?php endforeach; ?>
									</div>
									<div class="rgl-full" style="margin-top:14px;">
										<strong>Allowed Locations</strong>
										<div class="rgl-chip-list" style="margin-top:10px;">
											<?php foreach ( $this->get_all_locations() as $location_row ) : ?>
											<label class="rgl-chip">
												<input type="checkbox" data-name="staff[%d][location_ids][]" name="staff[__INDEX__][location_ids][]" value="<?php echo esc_attr( $location_row['id'] ); ?>">
												<span><?php echo esc_html( $location_row['name'] ); ?></span>
											</label>
											<?php endforeach; ?>
										</div>
									</div>
									<div class="rgl-exceptions">
										<div class="rgl-admin-toolbar" style="margin-top:18px;margin-bottom:12px;">
											<div>
												<strong>Days Off / Unavailable Times</strong>
												<p style="margin:4px 0 0;color:#666;">Add blocked dates or blocked time ranges. These remove availability from the normal recurring schedule.</p>
											</div>
											<button class="button rgl-add-exception" type="button">+ Add Time Off</button>
										</div>
										<div class="rgl-exception-list"></div>
									</div>
								</div>
							</div>
						</script>
						<script type="text/template" id="rgl-staff-exception-template">
							<div class="rgl-exception-row" data-exception-row>
								<div class="rgl-exception-grid">
									<p><label>From<br><input type="date" data-ex-name="staff[%d][date_exceptions][%e][from_date]" name="staff[__INDEX__][date_exceptions][__EX__][from_date]" value=""></label></p>
									<p><label>To<br><input type="date" data-ex-name="staff[%d][date_exceptions][%e][to_date]" name="staff[__INDEX__][date_exceptions][__EX__][to_date]" value=""></label></p>
									<p><label>Mode<br><select class="rgl-exception-mode" data-ex-name="staff[%d][date_exceptions][%e][mode]" name="staff[__INDEX__][date_exceptions][__EX__][mode]"><option value="off">Off all day</option><option value="block">Blocked time range</option></select></label></p>
									<p class="rgl-exception-time-row is-hidden"><label>Start<br><input type="time" data-ex-name="staff[%d][date_exceptions][%e][start]" name="staff[__INDEX__][date_exceptions][__EX__][start]" value="09:00"></label></p>
									<p class="rgl-exception-time-row is-hidden"><label>End<br><input type="time" data-ex-name="staff[%d][date_exceptions][%e][end]" name="staff[__INDEX__][date_exceptions][__EX__][end]" value="17:00"></label></p>
									<p><label>Note<br><input type="text" data-ex-name="staff[%d][date_exceptions][%e][note]" name="staff[__INDEX__][date_exceptions][__EX__][note]" value="" placeholder="Optional"></label></p>
									<div class="rgl-exception-actions">
										<button class="button rgl-duplicate-exception" type="button">Duplicate</button>
										<button class="button rgl-delete-exception" type="button">Delete</button>
									</div>
								</div>
							</div>
						</script>
						<p class="rgl-save-row"><button class="button button-primary" type="submit">Save Staff</button></p>
					</form>
				<?php endif; ?>

				<?php if ( 'services' === $tab ) : $all_staff = $this->get_all_staff(); ?>
					<form method="post" class="rgl-card rgl-repeater-form" data-repeater-type="services">
						<?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?>
						<input type="hidden" name="rgl_booking_action" value="save_services">
						<div class="rgl-admin-toolbar">
							<div>
								<h3 style="margin:0;">Services</h3>
								<p style="margin:6px 0 0;color:#666;">Services define their own duration, buffer, notice, max days and availability. Team members within a service can override those values.</p>
							</div>
							<div class="rgl-toolbar-actions">
								<button class="button button-secondary rgl-add-row" type="button">+ Add Service</button>
								<button class="button button-primary" type="submit">Save Services</button>
							</div>
						</div>
						<div class="rgl-repeater-list" data-list="services">
							<?php
							$count = max( 1, count( $services ) );
							for ( $i = 0; $i < $count; $i++ ) :
								$row = isset( $services[ $i ] ) ? $services[ $i ] : array(
									'id'               => '',
									'name'             => '',
									'duration'         => 30,
									'buffer'           => 0,
									'min_notice_hours' => 2,
									'max_days_ahead'   => 60,
									'active'           => 1,
									'hours'            => $this->default_hours(),
									'staff_ids'        => array(),
									'location_staff_ids' => array(),
									'staff_rules'      => array(),
									'booking_summary_notes' => '',
								);
								$row_hours = isset( $row['hours'] ) && is_array( $row['hours'] ) ? $row['hours'] : $this->default_hours();
								$row_rules = isset( $row['staff_rules'] ) && is_array( $row['staff_rules'] ) ? $row['staff_rules'] : array();
								?>
								<div class="rgl-accordion-item" data-item="services">
									<div class="rgl-accordion-header">
										<div class="rgl-accordion-title-wrap">
											<strong class="rgl-accordion-title"><?php echo esc_html( '' !== $row['name'] ? $row['name'] : 'New Service' ); ?></strong>
											<span class="rgl-accordion-subtitle">Service defaults plus optional team member scheduling overrides</span>
											<div class="rgl-linked-summary"><?php
												$linked_names = array();
												foreach ( $all_staff as $staff_row ) {
													if ( in_array( $staff_row['id'], $row['staff_ids'], true ) ) {
														$linked_names[] = $staff_row['name'];
													}
												}
												echo ! empty( $linked_names ) ? esc_html( 'Linked team members: ' . implode( ', ', $linked_names ) ) : 'No team members linked.';
											?></div>
										</div>
										<div class="rgl-item-actions">
											<button class="button rgl-move-up" type="button">↑</button>
											<button class="button rgl-move-down" type="button">↓</button>
											<button class="button rgl-duplicate-row" type="button">Duplicate</button>
											<button class="button rgl-delete-row" type="button">Delete</button>
											<span class="rgl-accordion-icon">+</span>
										</div>
									</div>
									<div class="rgl-accordion-content">
										<input type="hidden" data-name="services[%d][id]" name="services[<?php echo esc_attr( $i ); ?>][id]" value="<?php echo esc_attr( $row['id'] ); ?>">
										<div class="rgl-grid-admin rgl-grid-admin--3">
											<p><label>Service Name<br><input class="rgl-item-name" type="text" data-name="services[%d][name]" name="services[<?php echo esc_attr( $i ); ?>][name]" value="<?php echo esc_attr( $row['name'] ); ?>"></label></p>
											<p><label>Duration (mins)<br><input type="number" min="1" step="1" data-name="services[%d][duration]" name="services[<?php echo esc_attr( $i ); ?>][duration]" value="<?php echo esc_attr( $row['duration'] ); ?>"></label></p>
											<p><label class="rgl-inline-toggle">Active <input type="checkbox" data-name="services[%d][active]" name="services[<?php echo esc_attr( $i ); ?>][active]" value="1" <?php checked( ! empty( $row['active'] ) ); ?>></label></p>
										</div>
									<div class="rgl-grid-admin rgl-grid-admin--2 rgl-content-fields" style="margin-top:12px;">
										<p class="rgl-full"><label>Description<br><textarea rows="4" data-name="services[%d][description]" name="services[<?php echo esc_attr( $i ); ?>][description]" placeholder="Shown in Elementor Loop Grids and Single Templates."><?php echo esc_textarea( isset( $row['description'] ) ? $row['description'] : '' ); ?></textarea></label></p>
										<p class="rgl-full"><label>What's discussed on the call (shown to customer on booking form)<br><textarea rows="3" data-name="services[%d][customer_description]" name="services[<?php echo esc_attr( $i ); ?>][customer_description]" placeholder="A short sentence or two telling the customer what this consultation covers. Shown beneath the service dropdown when they pick this option."><?php echo esc_textarea( isset( $row['customer_description'] ) ? $row['customer_description'] : '' ); ?></textarea></label></p>
										<p class="rgl-full"><label>Customer-message placeholder (optional pre-fill prompt)<br><textarea rows="2" data-name="services[%d][message_placeholder]" name="services[<?php echo esc_attr( $i ); ?>][message_placeholder]" placeholder="E.g. for engraving: 'If you have specific fonts or designs you wish to discuss, please email them to engraving&#64;titanjewellery.co.uk before the appointment so we have time to review.'"><?php echo esc_textarea( isset( $row['message_placeholder'] ) ? $row['message_placeholder'] : '' ); ?></textarea></label></p>
										<p class="rgl-full"><label>Email for quick questions about this service<br><input type="email" data-name="services[%d][quick_question_email]" name="services[<?php echo esc_attr( $i ); ?>][quick_question_email]" value="<?php echo esc_attr( isset( $row['quick_question_email'] ) ? $row['quick_question_email'] : '' ); ?>" placeholder="e.g. sales@titanjewellery.co.uk or engraving@titanjewellery.co.uk" style="width:100%;box-sizing:border-box;"></label><br><span style="color:#666;font-size:12px;">If set, the words "email us" in the service description become a clickable mailto link pointing here.</span></p>
										<?php $this->render_admin_item_taxonomy_custom_fields( self::TAX_REL_SERVICE, 'services', $i, isset( $row['taxonomy_fields'] ) ? $row['taxonomy_fields'] : array() ); ?>
										<div class="rgl-image-picker">
											<label>Image<br>
												<input type="hidden" class="rgl-image-id" data-name="services[%d][image_id]" name="services[<?php echo esc_attr( $i ); ?>][image_id]" value="<?php echo esc_attr( isset( $row['image_id'] ) ? absint( $row['image_id'] ) : 0 ); ?>">
												<input type="hidden" class="rgl-image-url" data-name="services[%d][image_url]" name="services[<?php echo esc_attr( $i ); ?>][image_url]" value="<?php echo esc_attr( isset( $row['image_url'] ) ? $row['image_url'] : '' ); ?>">
											</label>
											<div class="rgl-image-preview"><?php $img_url = ! empty( $row['image_id'] ) ? wp_get_attachment_image_url( absint( $row['image_id'] ), 'thumbnail' ) : ( isset( $row['image_url'] ) ? $row['image_url'] : '' ); if ( $img_url ) : ?><img src="<?php echo esc_url( $img_url ); ?>" alt="" style="max-width:120px;height:auto;display:block;margin-bottom:8px;"><?php endif; ?></div>
											<button class="button rgl-select-image" type="button">Choose Image</button>
											<button class="button rgl-remove-image" type="button">Remove</button>
										</div>
										<?php $this->render_admin_extra_image_pickers( 'services', $i, $row ); ?>
									</div>

										<div class="rgl-grid-admin">
											<p><label>Buffer (mins)<br><input type="number" min="0" step="1" data-name="services[%d][buffer]" name="services[<?php echo esc_attr( $i ); ?>][buffer]" value="<?php echo esc_attr( $row['buffer'] ); ?>"></label></p>
										<p><label>Slot offer interval (mins)<br><input type="number" min="0" step="5" data-name="services[%d][slot_offer_interval]" name="services[<?php echo esc_attr( $i ); ?>][slot_offer_interval]" value="<?php echo esc_attr( isset( $row['slot_offer_interval'] ) ? $row['slot_offer_interval'] : 0 ); ?>" placeholder="0 = use Duration"></label><br><span style="color:#666;font-size:12px;">Stride between slot start times. Leave 0 to use Duration + Buffer (legacy). Set 30 to offer slots every 30 mins regardless of footprint.</span></p>
										<p><label>Customer-facing duration (mins)<br><input type="number" min="0" step="5" data-name="services[%d][customer_facing_duration]" name="services[<?php echo esc_attr( $i ); ?>][customer_facing_duration]" value="<?php echo esc_attr( isset( $row['customer_facing_duration'] ) ? $row['customer_facing_duration'] : 0 ); ?>" placeholder="0 = use Duration"></label><br><span style="color:#666;font-size:12px;">What the customer sees in emails and on the summary. Leave 0 to match Duration. Set 15 if the actual call is 15 mins but Duration is 60 for blocking purposes.</span></p>
											<p><label>Minimum Notice (hours)<br><input type="number" min="0" step="1" data-name="services[%d][min_notice_hours]" name="services[<?php echo esc_attr( $i ); ?>][min_notice_hours]" value="<?php echo esc_attr( $row['min_notice_hours'] ); ?>"></label></p>
											<p><label>Max Days Ahead<br><input type="number" min="1" step="1" data-name="services[%d][max_days_ahead]" name="services[<?php echo esc_attr( $i ); ?>][max_days_ahead]" value="<?php echo esc_attr( $row['max_days_ahead'] ); ?>"></label></p>
										</div>

										<?php
										$row_interest_enabled = ! empty( $row['interests_enabled'] );
										$row_interest_headline = isset( $row['interests_headline'] ) ? (string) $row['interests_headline'] : '';
										$row_interest_max = isset( $row['interests_max'] ) ? absint( $row['interests_max'] ) : 0;
										$row_interest_opts = isset( $row['interest_options'] ) && is_array( $row['interest_options'] ) ? $row['interest_options'] : array();
										?>
										<div class="rgl-full rgl-interests-admin" style="margin-top:14px;border:1px solid #d0d5dd;border-radius:8px;padding:14px;">
											<strong>Customer Interests Checklist</strong>
											<p style="margin:6px 0 10px;color:#666;">Show a checkbox group on the booking form for this service (e.g. metals to compare, engraving styles, ring widths). Leave any option blank to hide that row.</p>
											<div class="rgl-grid-admin rgl-grid-admin--3">
												<p><label class="rgl-inline-toggle">Show on booking form <input type="checkbox" data-name="services[%d][interests_enabled]" name="services[<?php echo esc_attr( $i ); ?>][interests_enabled]" value="1" <?php checked( $row_interest_enabled ); ?>></label></p>
												<p><label>Headline shown to customer<br><input type="text" data-name="services[%d][interests_headline]" name="services[<?php echo esc_attr( $i ); ?>][interests_headline]" value="<?php echo esc_attr( $row_interest_headline ); ?>" placeholder="e.g. Which metals are you considering?"></label></p>
												<p><label>Maximum selections (0 = no limit)<br><input type="number" min="0" max="20" step="1" data-name="services[%d][interests_max]" name="services[<?php echo esc_attr( $i ); ?>][interests_max]" value="<?php echo esc_attr( $row_interest_max ); ?>"></label></p>
											</div>
											<div class="rgl-grid-admin rgl-grid-admin--3" style="margin-top:8px;">
												<?php for ( $opt_i = 0; $opt_i < 20; $opt_i++ ) :
													$opt_val = isset( $row_interest_opts[ $opt_i ] ) ? $row_interest_opts[ $opt_i ] : '';
												?>
												<p><label>Option <?php echo ( $opt_i + 1 ); ?><br><input type="text" data-name="services[%d][interest_options][<?php echo esc_attr( $opt_i ); ?>][label]" name="services[<?php echo esc_attr( $i ); ?>][interest_options][<?php echo esc_attr( $opt_i ); ?>][label]" value="<?php echo esc_attr( $opt_val ); ?>" placeholder="<?php echo $opt_i === 0 ? 'e.g. Titanium' : ''; ?>"></label></p>
												<?php endfor; ?>
											</div>
										</div>


										<div class="rgl-full" style="margin-top:14px;">
											<strong>Booking Summary Notes</strong>
											<p style="margin:6px 0 10px;color:#666;">Optional information shown to the customer on their booking summary page.</p>
											<p><label>Additional Notes<br><textarea rows="4" data-name="services[%d][booking_summary_notes]" name="services[<?php echo esc_attr( $i ); ?>][booking_summary_notes]" placeholder="Shown on the booking summary page."><?php echo esc_textarea( isset( $row['booking_summary_notes'] ) ? $row['booking_summary_notes'] : '' ); ?></textarea></label></p>
										</div>

										<div class="rgl-full rgl-legacy-service-availability" style="margin-top:14px;display:none;">
											<strong>Service Availability</strong><p style="margin:6px 0 0;color:#666;">Legacy service availability. Bookable days and times are now set once per team member under Team Member Overrides below.</p>
											<div class="rgl-days" style="margin-top:10px;">
												<?php foreach ( $days as $day_key => $day_label ) :
													$day_data = isset( $row_hours[ $day_key ] ) ? $row_hours[ $day_key ] : array( 'enabled' => 0, 'start' => '09:00', 'end' => '17:00' );
													?>
													<div class="rgl-day">
														<strong><?php echo esc_html( $day_label ); ?></strong>
														<p><label><input type="checkbox" data-name="services[%d][hours][<?php echo esc_attr( $day_key ); ?>][enabled]" name="services[<?php echo esc_attr( $i ); ?>][hours][<?php echo esc_attr( $day_key ); ?>][enabled]" value="1" <?php checked( ! empty( $day_data['enabled'] ) ); ?>> Available</label></p>
														<p><label>Start<br><input type="time" data-name="services[%d][hours][<?php echo esc_attr( $day_key ); ?>][start]" name="services[<?php echo esc_attr( $i ); ?>][hours][<?php echo esc_attr( $day_key ); ?>][start]" value="<?php echo esc_attr( $day_data['start'] ); ?>"></label></p>
														<p><label>End<br><input type="time" data-name="services[%d][hours][<?php echo esc_attr( $day_key ); ?>][end]" name="services[<?php echo esc_attr( $i ); ?>][hours][<?php echo esc_attr( $day_key ); ?>][end]" value="<?php echo esc_attr( $day_data['end'] ); ?>"></label></p>
													</div>
												<?php endforeach; ?>
											</div>
										</div>

										<div class="rgl-full" style="margin-top:14px;">
											<strong>Allowed Locations</strong>
											<p style="margin:6px 0 10px;color:#666;">Choose which locations can offer this service.</p>
											<div class="rgl-chip-list" style="margin-top:10px;">
												<?php $service_location_ids = $this->get_effective_location_ids_from_row( $row ); foreach ( $this->get_all_locations() as $location_row ) : ?>
													<label class="rgl-chip">
														<input class="rgl-service-location-checkbox" type="checkbox" data-name="services[%d][location_ids][]" name="services[<?php echo esc_attr( $i ); ?>][location_ids][]" value="<?php echo esc_attr( $location_row['id'] ); ?>" <?php checked( in_array( $location_row['id'], $service_location_ids, true ) ); ?>>
														<span><?php echo esc_html( $location_row['name'] ); ?></span>
													</label>
												<?php endforeach; ?>
											</div>
										</div>

										<div class="rgl-full" style="margin-top:14px;">
											<strong>Allowed Team Members</strong>
											<div class="rgl-chip-list" style="margin-top:10px;">
												<?php foreach ( $all_staff as $staff_row ) : ?>
													<label class="rgl-chip">
														<input class="rgl-service-staff-checkbox" type="checkbox" data-name="services[%d][staff_ids][]" name="services[<?php echo esc_attr( $i ); ?>][staff_ids][]" value="<?php echo esc_attr( $staff_row['id'] ); ?>" <?php checked( in_array( $staff_row['id'], $row['staff_ids'], true ) ); ?>>
														<span><?php echo esc_html( $staff_row['name'] ); ?></span>
													</label>
												<?php endforeach; ?>
											</div>
										</div>

										<div class="rgl-full rgl-location-staff-matrix" style="margin-top:14px;">
											<strong>Team Members by Contact Method</strong>
											<p style="margin:6px 0 10px;color:#666;">Choose which team members can offer this service through each contact method. Set their weekly availability once under Team Member Overrides below.</p>
											<?php
											$location_staff_matrix = isset( $row['location_staff_ids'] ) && is_array( $row['location_staff_ids'] ) ? $row['location_staff_ids'] : array();
											$location_staff_hours_matrix = isset( $row['location_staff_hours'] ) && is_array( $row['location_staff_hours'] ) ? $row['location_staff_hours'] : array();
											$service_location_ids = isset( $row['location_ids'] ) && is_array( $row['location_ids'] ) ? array_values( array_filter( array_map( 'sanitize_key', $row['location_ids'] ) ) ) : array();
											$service_staff_ids = isset( $row['staff_ids'] ) && is_array( $row['staff_ids'] ) ? array_values( array_filter( array_map( 'sanitize_key', $row['staff_ids'] ) ) ) : array();
											?>
											<?php foreach ( $this->get_all_locations() as $location_row ) : $matrix_location_allowed = in_array( $location_row['id'], $service_location_ids, true ); ?>
												<div class="rgl-rule-card rgl-location-staff-card <?php echo $matrix_location_allowed ? '' : 'is-hidden-by-service-location'; ?>" data-location-id="<?php echo esc_attr( $location_row['id'] ); ?>" style="margin-top:10px;<?php echo $matrix_location_allowed ? '' : 'display:none;'; ?>">
													<div class="rgl-rule-header"><div class="rgl-rule-title"><?php echo esc_html( $location_row['name'] ); ?></div></div>
													<?php $matrix_staff_ids = isset( $location_staff_matrix[ $location_row['id'] ] ) && is_array( $location_staff_matrix[ $location_row['id'] ] ) ? array_map( 'sanitize_key', $location_staff_matrix[ $location_row['id'] ] ) : array(); foreach ( $all_staff as $staff_row ) : $matrix_staff_allowed = in_array( $staff_row['id'], $service_staff_ids, true ); $is_pair_checked = $matrix_location_allowed && $matrix_staff_allowed && in_array( $staff_row['id'], $matrix_staff_ids, true ); ?>
														<div class="rgl-location-staff-pair <?php echo $matrix_staff_allowed ? '' : 'is-hidden-by-service-staff'; ?>" data-staff-id="<?php echo esc_attr( $staff_row['id'] ); ?>" style="margin-top:10px;<?php echo $matrix_staff_allowed ? '' : 'display:none;'; ?>">
															<label class="rgl-chip rgl-location-staff-chip" data-staff-id="<?php echo esc_attr( $staff_row['id'] ); ?>">
																<input class="rgl-location-staff-toggle" type="checkbox" data-name="services[%d][location_staff_ids][<?php echo esc_attr( $location_row['id'] ); ?>][]" name="services[<?php echo esc_attr( $i ); ?>][location_staff_ids][<?php echo esc_attr( $location_row['id'] ); ?>][]" value="<?php echo esc_attr( $staff_row['id'] ); ?>" <?php checked( $is_pair_checked ); ?> <?php disabled( ! $matrix_staff_allowed || ! $matrix_location_allowed ); ?>>
																<span><?php echo esc_html( $staff_row['name'] ); ?></span>
															</label>
															<div class="rgl-location-staff-hours <?php echo $is_pair_checked ? '' : 'is-not-needed'; ?>" style="margin:10px 0 0 8px;padding:10px;border-left:3px solid #e5e7eb;">
																<p style="margin:0 0 10px;color:#666;">Days/times for <?php echo esc_html( $staff_row['name'] ); ?> at <?php echo esc_html( $location_row['name'] ); ?>.</p>
																<div class="rgl-days">
																	<?php foreach ( $days as $day_key => $day_label ) : $day_data = isset( $location_staff_hours_matrix[ $location_row['id'] ][ $staff_row['id'] ][ $day_key ] ) ? $location_staff_hours_matrix[ $location_row['id'] ][ $staff_row['id'] ][ $day_key ] : array( 'enabled' => 0, 'start' => '09:00', 'end' => '17:00' ); ?>
																		<div class="rgl-day">
																			<strong><?php echo esc_html( $day_label ); ?></strong>
																			<p><label><input type="checkbox" data-name="services[%d][location_staff_hours][<?php echo esc_attr( $location_row['id'] ); ?>][<?php echo esc_attr( $staff_row['id'] ); ?>][<?php echo esc_attr( $day_key ); ?>][enabled]" name="services[<?php echo esc_attr( $i ); ?>][location_staff_hours][<?php echo esc_attr( $location_row['id'] ); ?>][<?php echo esc_attr( $staff_row['id'] ); ?>][<?php echo esc_attr( $day_key ); ?>][enabled]" value="1" <?php checked( ! empty( $day_data['enabled'] ) ); ?>> Available</label></p>
																			<p><label>Start<br><input type="time" data-name="services[%d][location_staff_hours][<?php echo esc_attr( $location_row['id'] ); ?>][<?php echo esc_attr( $staff_row['id'] ); ?>][<?php echo esc_attr( $day_key ); ?>][start]" name="services[<?php echo esc_attr( $i ); ?>][location_staff_hours][<?php echo esc_attr( $location_row['id'] ); ?>][<?php echo esc_attr( $staff_row['id'] ); ?>][<?php echo esc_attr( $day_key ); ?>][start]" value="<?php echo esc_attr( $day_data['start'] ); ?>"></label></p>
																			<p><label>End<br><input type="time" data-name="services[%d][location_staff_hours][<?php echo esc_attr( $location_row['id'] ); ?>][<?php echo esc_attr( $staff_row['id'] ); ?>][<?php echo esc_attr( $day_key ); ?>][end]" name="services[<?php echo esc_attr( $i ); ?>][location_staff_hours][<?php echo esc_attr( $location_row['id'] ); ?>][<?php echo esc_attr( $staff_row['id'] ); ?>][<?php echo esc_attr( $day_key ); ?>][end]" value="<?php echo esc_attr( $day_data['end'] ); ?>"></label></p>
																		</div>
																	<?php endforeach; ?>
																</div>
															</div>
														</div>
													<?php endforeach; ?>
												</div>
											<?php endforeach; ?>
										</div>

										<div class="rgl-full" style="margin-top:18px;">
											<strong>Team Member Availability and Overrides</strong>
											<p style="margin:6px 0 12px;color:#666;">Set each team member’s weekly availability here once. Duration, buffer and notice fields remain optional overrides.</p>
											<?php foreach ( $all_staff as $staff_row ) :
												$staff_rule = isset( $row_rules[ $staff_row['id'] ] ) ? $row_rules[ $staff_row['id'] ] : array(
													'duration'         => '',
													'buffer'           => '',
													'min_notice_hours' => '',
													'max_days_ahead'   => '',
													'hours'            => $this->default_hours(),
												);
												$staff_rule['slot_capacity'] = max( 1, absint( isset( $staff_rule['slot_capacity'] ) ? $staff_rule['slot_capacity'] : 1 ) );
												$staff_rule_hours = $this->get_admin_shared_hours_for_service_staff( $row, $staff_row );
												?>
												<div class="rgl-rule-card rgl-staff-rule" data-staff-id="<?php echo esc_attr( $staff_row['id'] ); ?>">
													<div class="rgl-rule-header">
														<div class="rgl-rule-title"><?php echo esc_html( $staff_row['name'] ); ?></div>
														<span class="rgl-accordion-icon">+</span>
													</div>
													<div class="rgl-rule-content">
														<div class="rgl-grid-admin">
															<p><label>Duration Override (mins)<br><input type="number" min="1" step="1" data-name="services[%d][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][duration]" name="services[<?php echo esc_attr( $i ); ?>][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][duration]" value="<?php echo esc_attr( $staff_rule['duration'] ); ?>" placeholder="Use service default"></label></p>
															<p><label>Buffer Override (mins)<br><input type="number" min="0" step="1" data-name="services[%d][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][buffer]" name="services[<?php echo esc_attr( $i ); ?>][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][buffer]" value="<?php echo esc_attr( $staff_rule['buffer'] ); ?>" placeholder="Use service default"></label></p>
															<p><label>Minimum Notice Override (hours)<br><input type="number" min="0" step="1" data-name="services[%d][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][min_notice_hours]" name="services[<?php echo esc_attr( $i ); ?>][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][min_notice_hours]" value="<?php echo esc_attr( $staff_rule['min_notice_hours'] ); ?>" placeholder="Use service default"></label></p>
															<p><label>Max Days Ahead Override<br><input type="number" min="1" step="1" data-name="services[%d][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][max_days_ahead]" name="services[<?php echo esc_attr( $i ); ?>][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][max_days_ahead]" value="<?php echo esc_attr( $staff_rule['max_days_ahead'] ); ?>" placeholder="Use service default"></label></p>
															<p><label>Capacity per Time Slot<br><input type="number" min="1" step="1" data-name="services[%d][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][slot_capacity]" name="services[<?php echo esc_attr( $i ); ?>][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][slot_capacity]" value="<?php echo esc_attr( $staff_rule['slot_capacity'] ); ?>" placeholder="1"></label><br><span style="color:#666;font-size:12px;">Use 1 for private appointments, or a higher number for classes, workshops, and group sessions.</span></p>
															</div>


														<?php $this->render_shared_staff_availability_fields( $i, $staff_row['id'], $staff_rule_hours ); ?>

														<div style="margin-top:12px;">
															<strong>Booking Summary Notes Override</strong>
															<p><label>Additional Notes<br><textarea rows="4" data-name="services[%d][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][booking_summary_notes]" name="services[<?php echo esc_attr( $i ); ?>][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][booking_summary_notes]" placeholder="Leave blank to use the service notes."><?php echo esc_textarea( isset( $staff_rule['booking_summary_notes'] ) ? $staff_rule['booking_summary_notes'] : '' ); ?></textarea></label></p>
														</div>
													</div>
												</div>
											<?php endforeach; ?>
										</div>

									</div>
								</div>
							<?php endfor; ?>
						</div>

						<script type="text/template" id="rgl-services-template">
							<div class="rgl-accordion-item" data-item="services">
								<div class="rgl-accordion-header">
									<div class="rgl-accordion-title-wrap">
										<strong class="rgl-accordion-title">New Service</strong>
										<span class="rgl-accordion-subtitle">Service defaults plus optional team member scheduling overrides</span>
										<div class="rgl-linked-summary">No team members linked.</div>
									</div>
									<div class="rgl-item-actions">
										<button class="button rgl-move-up" type="button">↑</button>
										<button class="button rgl-move-down" type="button">↓</button>
										<button class="button rgl-duplicate-row" type="button">Duplicate</button>
										<button class="button rgl-delete-row" type="button">Delete</button>
										<span class="rgl-accordion-icon">+</span>
									</div>
								</div>
								<div class="rgl-accordion-content">
									<input type="hidden" data-name="services[%d][id]" name="services[__INDEX__][id]" value="">
									<div class="rgl-grid-admin rgl-grid-admin--3">
										<p><label>Service Name<br><input class="rgl-item-name" type="text" data-name="services[%d][name]" name="services[__INDEX__][name]" value=""></label></p>
										<p><label>Duration (mins)<br><input type="number" min="1" step="1" data-name="services[%d][duration]" name="services[__INDEX__][duration]" value="30"></label></p>
										<p><label class="rgl-inline-toggle">Active <input type="checkbox" data-name="services[%d][active]" name="services[__INDEX__][active]" value="1" checked></label></p>
									</div>
									<div class="rgl-grid-admin rgl-grid-admin--2 rgl-content-fields" style="margin-top:12px;">
										<p class="rgl-full"><label>Description<br><textarea rows="4" data-name="services[%d][description]" name="services[__INDEX__][description]" placeholder="Shown in Elementor Loop Grids and Single Templates."></textarea></label></p>
										<p class="rgl-full"><label>What's discussed on the call (shown to customer on booking form)<br><textarea rows="3" data-name="services[%d][customer_description]" name="services[__INDEX__][customer_description]" placeholder="A short sentence or two telling the customer what this consultation covers."></textarea></label></p>
										<p class="rgl-full"><label>Customer-message placeholder (optional pre-fill prompt)<br><textarea rows="2" data-name="services[%d][message_placeholder]" name="services[__INDEX__][message_placeholder]" placeholder="E.g. 'Please email any fonts or designs to engraving&#64;titanjewellery.co.uk before the call.'"></textarea></label></p>
										<p class="rgl-full"><label>Email for quick questions about this service<br><input type="email" data-name="services[%d][quick_question_email]" name="services[__INDEX__][quick_question_email]" value="" placeholder="e.g. sales@titanjewellery.co.uk or engraving@titanjewellery.co.uk" style="width:100%;box-sizing:border-box;"></label><br><span style="color:#666;font-size:12px;">If set, the words "email us" in the service description become a clickable mailto link pointing here.</span></p>
										<div class="rgl-image-picker">
											<label>Image<br>
												<input type="hidden" class="rgl-image-id" data-name="services[%d][image_id]" name="services[__INDEX__][image_id]" value="0">
												<input type="hidden" class="rgl-image-url" data-name="services[%d][image_url]" name="services[__INDEX__][image_url]" value="">
											</label>
											<div class="rgl-image-preview"></div>
											<button class="button rgl-select-image" type="button">Choose Image</button>
											<button class="button rgl-remove-image" type="button">Remove</button>
										</div>
										<?php $this->render_admin_extra_image_pickers_template( 'services' ); ?>
									</div>
									<div class="rgl-grid-admin">
										<p><label>Buffer (mins)<br><input type="number" min="0" step="1" data-name="services[%d][buffer]" name="services[__INDEX__][buffer]" value="0"></label></p>
										<p><label>Slot offer interval (mins)<br><input type="number" min="0" step="5" data-name="services[%d][slot_offer_interval]" name="services[__INDEX__][slot_offer_interval]" value="0" placeholder="0 = use Duration"></label></p>
										<p><label>Customer-facing duration (mins)<br><input type="number" min="0" step="5" data-name="services[%d][customer_facing_duration]" name="services[__INDEX__][customer_facing_duration]" value="0" placeholder="0 = use Duration"></label></p>
										<p><label>Minimum Notice (hours)<br><input type="number" min="0" step="1" data-name="services[%d][min_notice_hours]" name="services[__INDEX__][min_notice_hours]" value="2"></label></p>
										<p><label>Max Days Ahead<br><input type="number" min="1" step="1" data-name="services[%d][max_days_ahead]" name="services[__INDEX__][max_days_ahead]" value="60"></label></p>
									</div>

									<div class="rgl-full rgl-interests-admin" style="margin-top:14px;border:1px solid #d0d5dd;border-radius:8px;padding:14px;">
										<strong>Customer Interests Checklist</strong>
										<p style="margin:6px 0 10px;color:#666;">Show a checkbox group on the booking form for this service (e.g. metals to compare, engraving styles, ring widths). Leave any option blank to hide that row.</p>
										<div class="rgl-grid-admin rgl-grid-admin--3">
											<p><label class="rgl-inline-toggle">Show on booking form <input type="checkbox" data-name="services[%d][interests_enabled]" name="services[__INDEX__][interests_enabled]" value="1"></label></p>
											<p><label>Headline shown to customer<br><input type="text" data-name="services[%d][interests_headline]" name="services[__INDEX__][interests_headline]" value="" placeholder="e.g. Which metals are you considering?"></label></p>
											<p><label>Maximum selections (0 = no limit)<br><input type="number" min="0" max="20" step="1" data-name="services[%d][interests_max]" name="services[__INDEX__][interests_max]" value="0"></label></p>
										</div>
										<div class="rgl-grid-admin rgl-grid-admin--3" style="margin-top:8px;">
											<?php for ( $opt_i = 0; $opt_i < 20; $opt_i++ ) : ?>
											<p><label>Option <?php echo ( $opt_i + 1 ); ?><br><input type="text" data-name="services[%d][interest_options][<?php echo esc_attr( $opt_i ); ?>][label]" name="services[__INDEX__][interest_options][<?php echo esc_attr( $opt_i ); ?>][label]" value="" placeholder="<?php echo $opt_i === 0 ? 'e.g. Titanium' : ''; ?>"></label></p>
											<?php endfor; ?>
										</div>
									</div>

									<div class="rgl-full" style="margin-top:14px;">
										<strong>Booking Summary Notes</strong>
										<p><label>Additional Notes<br><textarea rows="4" data-name="services[%d][booking_summary_notes]" name="services[__INDEX__][booking_summary_notes]" placeholder="Shown on the booking summary page."></textarea></label></p>
									</div>
									<div class="rgl-full rgl-legacy-service-availability" style="margin-top:14px;display:none;">
										<strong>Service Availability</strong>
										<div class="rgl-days" style="margin-top:10px;">
											<?php foreach ( $days as $day_key => $day_label ) : ?>
											<div class="rgl-day">
												<strong><?php echo esc_html( $day_label ); ?></strong>
												<p><label><input type="checkbox" data-name="services[%d][hours][<?php echo esc_attr( $day_key ); ?>][enabled]" name="services[__INDEX__][hours][<?php echo esc_attr( $day_key ); ?>][enabled]" value="1" <?php checked( in_array( $day_key, array( 'mon','tue','wed','thu','fri' ), true ) ); ?>> Available</label></p>
												<p><label>Start<br><input type="time" data-name="services[%d][hours][<?php echo esc_attr( $day_key ); ?>][start]" name="services[__INDEX__][hours][<?php echo esc_attr( $day_key ); ?>][start]" value="09:00"></label></p>
												<p><label>End<br><input type="time" data-name="services[%d][hours][<?php echo esc_attr( $day_key ); ?>][end]" name="services[__INDEX__][hours][<?php echo esc_attr( $day_key ); ?>][end]" value="<?php echo esc_attr( in_array( $day_key, array( 'sat','sun' ), true ) ? '13:00' : '17:00' ); ?>"></label></p>
											</div>
											<?php endforeach; ?>
										</div>
									</div>
									<div class="rgl-full" style="margin-top:14px;">
										<strong>Allowed Locations</strong>
										<p style="margin:6px 0 10px;color:#666;">Choose which locations can offer this service.</p>
										<div class="rgl-chip-list" style="margin-top:10px;">
											<?php foreach ( $this->get_all_locations() as $location_row ) : ?>
											<label class="rgl-chip">
												<input class="rgl-service-location-checkbox" type="checkbox" data-name="services[%d][location_ids][]" name="services[__INDEX__][location_ids][]" value="<?php echo esc_attr( $location_row['id'] ); ?>">
												<span><?php echo esc_html( $location_row['name'] ); ?></span>
											</label>
											<?php endforeach; ?>
										</div>
									</div>

									<div class="rgl-full" style="margin-top:14px;">
										<strong>Allowed Team Members</strong>
										<div class="rgl-chip-list" style="margin-top:10px;">
											<?php foreach ( $all_staff as $staff_row ) : ?>
											<label class="rgl-chip">
												<input class="rgl-service-staff-checkbox" type="checkbox" data-name="services[%d][staff_ids][]" name="services[__INDEX__][staff_ids][]" value="<?php echo esc_attr( $staff_row['id'] ); ?>">
												<span><?php echo esc_html( $staff_row['name'] ); ?></span>
											</label>
											<?php endforeach; ?>
										</div>
									</div>
									<div class="rgl-full rgl-location-staff-matrix" style="margin-top:14px;">
										<strong>Team Members by Contact Method</strong>
										<p style="margin:6px 0 10px;color:#666;">Choose which team members can offer this service through each contact method. Set their weekly availability once below.</p>
										<?php foreach ( $this->get_all_locations() as $location_row ) : ?>
											<div class="rgl-rule-card" style="margin-top:10px;">
												<div class="rgl-rule-header"><div class="rgl-rule-title"><?php echo esc_html( $location_row['name'] ); ?></div></div>
												<div class="rgl-chip-list" style="margin-top:10px;">
													<?php foreach ( $all_staff as $staff_row ) : ?>
														<label class="rgl-chip rgl-location-staff-chip">
															<input type="checkbox" data-name="services[%d][location_staff_ids][<?php echo esc_attr( $location_row['id'] ); ?>][]" name="services[__INDEX__][location_staff_ids][<?php echo esc_attr( $location_row['id'] ); ?>][]" value="<?php echo esc_attr( $staff_row['id'] ); ?>">
															<span><?php echo esc_html( $staff_row['name'] ); ?></span>
														</label>
													<?php endforeach; ?>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
									<div class="rgl-full" style="margin-top:18px;">
										<strong>Team Member Overrides</strong>
										<p style="margin:6px 0 12px;color:#666;">These only apply when filled in. Blank fields fall back to the service values above.</p>
										<?php foreach ( $all_staff as $staff_row ) : ?>
										<div class="rgl-rule-card rgl-staff-rule" data-staff-id="<?php echo esc_attr( $staff_row['id'] ); ?>">
											<div class="rgl-rule-header">
												<div class="rgl-rule-title"><?php echo esc_html( $staff_row['name'] ); ?></div>
												<span class="rgl-accordion-icon">+</span>
											</div>
											<div class="rgl-rule-content">
												<div class="rgl-grid-admin">
													<p><label>Duration Override (mins)<br><input type="number" min="1" step="1" data-name="services[%d][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][duration]" name="services[__INDEX__][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][duration]" value=""></label></p>
													<p><label>Buffer Override (mins)<br><input type="number" min="0" step="1" data-name="services[%d][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][buffer]" name="services[__INDEX__][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][buffer]" value=""></label></p>
													<p><label>Minimum Notice Override (hours)<br><input type="number" min="0" step="1" data-name="services[%d][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][min_notice_hours]" name="services[__INDEX__][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][min_notice_hours]" value=""></label></p>
													<p><label>Max Days Ahead Override<br><input type="number" min="1" step="1" data-name="services[%d][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][max_days_ahead]" name="services[__INDEX__][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][max_days_ahead]" value=""></label></p>
													<p><label>Capacity per Time Slot<br><input type="number" min="1" step="1" data-name="services[%d][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][slot_capacity]" name="services[__INDEX__][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][slot_capacity]" value="1" placeholder="1"></label><br><span style="color:#666;font-size:12px;">Use 1 for private appointments, or a higher number for classes, workshops, and group sessions.</span></p>
												</div>

														<?php $this->render_shared_staff_availability_fields( '__INDEX__', $staff_row['id'], $this->default_hours() ); ?>

														<div style="margin-top:12px;">
															<strong>Booking Summary Notes Override</strong>
															<p><label>Additional Notes<br><textarea rows="4" data-name="services[%d][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][booking_summary_notes]" name="services[__INDEX__][staff_rules][<?php echo esc_attr( $staff_row['id'] ); ?>][booking_summary_notes]" placeholder="Leave blank to use the service notes."></textarea></label></p>
														</div>
											</div>
										</div>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						</script>

						<p class="rgl-save-row"><button class="button button-primary" type="submit">Save Services</button></p>
					</form>
				<?php endif; ?>

				<?php if ( 'customer-data' === $tab ) :
					$cd_email   = isset( $_GET['cd_email'] ) ? sanitize_email( wp_unslash( $_GET['cd_email'] ) ) : '';
					$cd_results = '' !== $cd_email ? $this->get_bookings_for_email( $cd_email ) : array();
					$cd_timed_block = '' !== $cd_email ? $this->get_booking_block_for_email( $cd_email ) : false;
					$cd_legacy_block = '' !== $cd_email ? $this->email_is_legacy_blocked( $cd_email ) : false;
				?>
					<h2>Customer Data Tools</h2>
					<p style="color:#666;max-width:680px;">Look up every booking held against an email address. Use this to respond to a customer's request to see or delete their data. Export produces a file you can send them; delete permanently removes all their bookings and cannot be undone.</p>

					<form method="get" style="margin:16px 0;">
						<input type="hidden" name="page" value="appt-booker">
						<input type="hidden" name="tab" value="customer-data">
						<p style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
							<label for="cd_email"><strong>Customer email</strong></label>
							<input type="email" id="cd_email" name="cd_email" value="<?php echo esc_attr( $cd_email ); ?>" placeholder="name@example.com" style="min-width:280px;">
							<button class="button button-primary" type="submit">Search</button>
						</p>
					</form>

					<?php if ( '' !== $cd_email ) : ?>
						<?php if ( empty( $cd_results ) && ! $cd_timed_block && ! $cd_legacy_block ) : ?>
							<p>No bookings or future-booking blocks found for <strong><?php echo esc_html( $cd_email ); ?></strong>.</p>
						<?php else : ?>
							<p><strong><?php echo count( $cd_results ); ?></strong> booking<?php echo 1 === count( $cd_results ) ? '' : 's'; ?> found for <strong><?php echo esc_html( $cd_email ); ?></strong>.</p>
							<?php if ( $cd_timed_block || $cd_legacy_block ) : ?><p style="padding:10px 12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:6px;max-width:900px;"><strong>Future online-booking block:</strong> <?php echo $cd_timed_block ? esc_html( 'active until ' . wp_date( 'd-m-Y', absint( $cd_timed_block['expires_at'] ) ) ) : esc_html( 'active on the legacy/spam list with no automatic expiry' ); ?></p><?php endif; ?>
							<?php if ( ! empty( $cd_results ) ) : ?>
							<table class="widefat striped" style="margin:12px 0;max-width:900px;">
								<thead><tr><th>Reference</th><th>Date</th><th>Service</th><th>Status</th><th>Name</th></tr></thead>
								<tbody>
									<?php foreach ( $cd_results as $cd_b ) : ?>
										<tr>
											<td><?php echo esc_html( $cd_b->booking_reference ); ?></td>
											<td><?php echo esc_html( $this->format_booking_short_date( $cd_b ) ); ?></td>
											<td><?php echo esc_html( $cd_b->service_name ); ?></td>
											<td><?php echo esc_html( $this->get_booking_status_label( (string) $cd_b->booking_status ) ); ?></td>
											<td><?php echo esc_html( $cd_b->customer_name ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<?php endif; ?>
							<form method="post" style="display:flex;gap:8px;flex-wrap:wrap;">
								<?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?>
								<input type="hidden" name="cd_email" value="<?php echo esc_attr( $cd_email ); ?>">
								<button class="button" type="submit" name="rgl_booking_action" value="export_customer_data">Export this customer's data</button>
								<button class="button" type="submit" name="rgl_booking_action" value="delete_customer_data" onclick="return confirm('Permanently delete all bookings and future-booking blocks for <?php echo esc_js( $cd_email ); ?>? This cannot be undone.');" style="color:#b32d2e;">Delete all this customer's data</button>
							</form>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( 'content-research' === $tab ) :
					$cr_from = isset( $_GET['cr_from'] ) ? sanitize_text_field( wp_unslash( $_GET['cr_from'] ) ) : '';
					$cr_to   = isset( $_GET['cr_to'] ) ? sanitize_text_field( wp_unslash( $_GET['cr_to'] ) ) : '';
					if ( '' === $cr_from ) { $cr_from = gmdate( 'Y-m-d', strtotime( '-90 days' ) ); }
					if ( '' === $cr_to )   { $cr_to   = current_time( 'Y-m-d' ); }
					$cr_preview = $this->count_bookings_in_range( $cr_from, $cr_to );
				?>
					<h2>Content Research</h2>
					<p style="color:#666;max-width:720px;">Download a CSV of consultation notes (with no personal data) plus an analysis prompt. Paste both into ChatGPT or Claude to identify patterns worth turning into blog posts, buyer's guides or FAQ entries. <strong>Edit the prompt under Settings &rarr; Email notifications</strong> to refine the analysis over time.</p>
					<p style="color:#666;max-width:720px;">The export contains: date, service, communication method, topics of interest, the customer's pre-call message, and your post-call notes. <strong>No names, emails, phone numbers or booking references are included.</strong></p>

					<form method="get" style="margin:16px 0;padding:14px;background:#fafaf7;border:1px solid #e6e6e2;border-radius:8px;max-width:720px;">
						<input type="hidden" name="page" value="appt-booker">
						<input type="hidden" name="tab" value="content-research">
						<p style="display:flex;gap:14px;flex-wrap:wrap;align-items:end;margin:0;">
							<label>From<br><input type="date" name="cr_from" value="<?php echo esc_attr( $cr_from ); ?>"></label>
							<label>To<br><input type="date" name="cr_to" value="<?php echo esc_attr( $cr_to ); ?>"></label>
							<button class="button" type="submit">Preview range</button>
						</p>
					</form>

					<p><strong><?php echo (int) $cr_preview; ?></strong> consultation<?php echo 1 === $cr_preview ? '' : 's'; ?> in the range <?php echo esc_html( $cr_from ); ?> to <?php echo esc_html( $cr_to ); ?>.</p>

					<?php if ( $cr_preview > 0 ) : ?>
						<form method="post" style="margin-top:14px;">
							<?php wp_nonce_field( 'rgl_booking_admin_action', 'rgl_booking_nonce' ); ?>
							<input type="hidden" name="cr_from" value="<?php echo esc_attr( $cr_from ); ?>">
							<input type="hidden" name="cr_to" value="<?php echo esc_attr( $cr_to ); ?>">
							<button class="button button-primary" type="submit" name="rgl_booking_action" value="export_content_research">Download CSV + prompt (ZIP)</button>
						</form>
					<?php else : ?>
						<p style="color:#666;">Nothing to export for this range. Choose a wider date range or take more notes.</p>
					<?php endif; ?>
				<?php endif; ?>

			<script>
			(function($){
				$(document).on('click', '.rgl-select-image', function(e){
					e.preventDefault();
					if (typeof wp === 'undefined' || !wp.media) {
						alert('The WordPress Media Library is not available on this screen.');
						return;
					}
					var $box = $(this).closest('.rgl-image-picker');
					var frame = wp.media({ title: 'Choose Image', button: { text: 'Use this image' }, multiple: false });
					frame.on('select', function(){
						var attachment = frame.state().get('selection').first().toJSON();
						var thumb = attachment.url || '';
						if (attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url) {
							thumb = attachment.sizes.thumbnail.url;
						}
						$box.find('.rgl-image-id').val(attachment.id || 0);
						$box.find('.rgl-image-url').val(attachment.url || '');
						$box.find('.rgl-image-preview').html(thumb ? '<img src="'+thumb+'" alt="" style="max-width:120px;height:auto;display:block;margin-bottom:8px;">' : '');
					});
					frame.open();
				});
				$(document).on('click', '.rgl-remove-image', function(e){
					e.preventDefault();
					var $box = $(this).closest('.rgl-image-picker');
					$box.find('.rgl-image-id').val('0');
					$box.find('.rgl-image-url').val('');
					$box.find('.rgl-image-preview').empty();
				});
			})(jQuery);
			</script>

			</div>
			<?php
		}
	}
}

if ( ! function_exists( 'appt_booker_define_elementor_image_dynamic_tag_classes' ) ) {
	function appt_booker_define_elementor_image_dynamic_tag_classes() {
		if ( ! class_exists( '\Elementor\Core\DynamicTags\Data_Tag' ) ) {
			return;
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Image_Dynamic_Tag_Base' ) ) {
			abstract class Appt_Booker_Elementor_Image_Dynamic_Tag_Base extends \Elementor\Core\DynamicTags\Data_Tag {
				const TAG_NAME  = '';
				const TAG_TITLE = 'Appt Booker Image';
				const ID_KEY    = '';
				const URL_KEY   = '';

				public function __construct( $data = array() ) {
					parent::__construct( is_array( $data ) ? $data : array() );
				}

				public function get_name() {
					return 'appt-booker-' . static::TAG_NAME;
				}

				public function get_title() {
					return static::TAG_TITLE;
				}

				public function get_group() {
					return 'appt-booker';
				}

				public function get_categories() {
					if ( class_exists( '\Elementor\Modules\DynamicTags\Module' ) && defined( '\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY' ) ) {
						return array( \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY );
					}

					return array( 'image' );
				}

				private function get_candidate_post_ids( array $options = array() ) {
					$candidates = array();

					$add_candidate = function( $post_id ) use ( &$candidates ) {
						$post_id = absint( $post_id );
						if ( $post_id && ! in_array( $post_id, $candidates, true ) ) {
							$candidates[] = $post_id;
						}
					};

					foreach ( array( 'post_id', 'object_id', 'id' ) as $option_key ) {
						if ( isset( $options[ $option_key ] ) ) {
							$add_candidate( $options[ $option_key ] );
						}
					}

					if ( function_exists( 'get_queried_object_id' ) ) {
						$add_candidate( get_queried_object_id() );
					}

					global $post;
					if ( $post instanceof \WP_Post ) {
						$add_candidate( $post->ID );
					}

					if ( function_exists( 'get_the_ID' ) ) {
						$add_candidate( get_the_ID() );
					}

					foreach ( array( 'preview_id', 'p', 'page_id', 'post', 'elementor-preview' ) as $request_key ) {
						if ( isset( $_GET[ $request_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
							$add_candidate( wp_unslash( $_GET[ $request_key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						}
					}

					return $candidates;
				}

				private function post_has_appt_booker_image_meta( $post_id ) {
					$post_id = absint( $post_id );
					if ( ! $post_id ) {
						return false;
					}

					if ( '' !== static::ID_KEY && '' !== (string) get_post_meta( $post_id, static::ID_KEY, true ) ) {
						return true;
					}

					if ( '' !== static::URL_KEY && '' !== (string) get_post_meta( $post_id, static::URL_KEY, true ) ) {
						return true;
					}

					return false;
				}

				private function post_is_appt_booker_content_type( $post_id ) {
					$post_id = absint( $post_id );
					if ( ! $post_id || ! class_exists( 'Appt_Booker_Final' ) ) {
						return false;
					}

					return in_array(
						get_post_type( $post_id ),
						array( Appt_Booker_Final::CPT_LOCATION, Appt_Booker_Final::CPT_SERVICE, Appt_Booker_Final::CPT_TEAM ),
						true
					);
				}

				private function get_current_appt_booker_post_id( array $options = array() ) {
					$candidates = $this->get_candidate_post_ids( $options );

					foreach ( $candidates as $candidate_id ) {
						if ( $this->post_has_appt_booker_image_meta( $candidate_id ) ) {
							return absint( $candidate_id );
						}
					}

					foreach ( $candidates as $candidate_id ) {
						if ( $this->post_is_appt_booker_content_type( $candidate_id ) ) {
							return absint( $candidate_id );
						}
					}

					return ! empty( $candidates ) ? absint( $candidates[0] ) : 0;
				}

				public function get_value( array $options = array() ) {
					$post_id   = $this->get_current_appt_booker_post_id( $options );
					$image_id  = $post_id && '' !== static::ID_KEY ? absint( get_post_meta( $post_id, static::ID_KEY, true ) ) : 0;
					$image_url = $post_id && '' !== static::URL_KEY ? esc_url_raw( get_post_meta( $post_id, static::URL_KEY, true ) ) : '';

					if ( $image_id && '' === $image_url ) {
						$image_url = wp_get_attachment_image_url( $image_id, 'full' );
					}

					if ( ! $image_id && '' !== $image_url ) {
						$matched_id = attachment_url_to_postid( $image_url );
						if ( $matched_id ) {
							$image_id = absint( $matched_id );
						}
					}

					return array(
						'id'  => $image_id,
						'url' => $image_url,
					);
				}
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Location_Main_Image_Tag' ) ) {
			class Appt_Booker_Elementor_Location_Main_Image_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'location-main-image';
				const TAG_TITLE = 'Location Main Image';
				const ID_KEY    = 'appt_location_image_id';
				const URL_KEY   = 'appt_location_image_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Staff_Main_Image_Tag' ) ) {
			class Appt_Booker_Elementor_Staff_Main_Image_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'staff-main-image';
				const TAG_TITLE = 'Staff Main Image';
				const ID_KEY    = 'appt_staff_image_id';
				const URL_KEY   = 'appt_staff_image_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Service_Main_Image_Tag' ) ) {
			class Appt_Booker_Elementor_Service_Main_Image_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'service-main-image';
				const TAG_TITLE = 'Service Main Image';
				const ID_KEY    = 'appt_service_image_id';
				const URL_KEY   = 'appt_service_image_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Location_Extra_Image_1_Tag' ) ) {
			class Appt_Booker_Elementor_Location_Extra_Image_1_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'location-extra-image-1';
				const TAG_TITLE = 'Location Extra Image 1';
				const ID_KEY    = 'appt_location_extra_image_1_id';
				const URL_KEY   = 'appt_location_extra_image_1_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Location_Extra_Image_2_Tag' ) ) {
			class Appt_Booker_Elementor_Location_Extra_Image_2_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'location-extra-image-2';
				const TAG_TITLE = 'Location Extra Image 2';
				const ID_KEY    = 'appt_location_extra_image_2_id';
				const URL_KEY   = 'appt_location_extra_image_2_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Location_Extra_Image_3_Tag' ) ) {
			class Appt_Booker_Elementor_Location_Extra_Image_3_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'location-extra-image-3';
				const TAG_TITLE = 'Location Extra Image 3';
				const ID_KEY    = 'appt_location_extra_image_3_id';
				const URL_KEY   = 'appt_location_extra_image_3_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Location_Extra_Image_4_Tag' ) ) {
			class Appt_Booker_Elementor_Location_Extra_Image_4_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'location-extra-image-4';
				const TAG_TITLE = 'Location Extra Image 4';
				const ID_KEY    = 'appt_location_extra_image_4_id';
				const URL_KEY   = 'appt_location_extra_image_4_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Location_Extra_Image_5_Tag' ) ) {
			class Appt_Booker_Elementor_Location_Extra_Image_5_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'location-extra-image-5';
				const TAG_TITLE = 'Location Extra Image 5';
				const ID_KEY    = 'appt_location_extra_image_5_id';
				const URL_KEY   = 'appt_location_extra_image_5_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Staff_Extra_Image_1_Tag' ) ) {
			class Appt_Booker_Elementor_Staff_Extra_Image_1_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'staff-extra-image-1';
				const TAG_TITLE = 'Staff Extra Image 1';
				const ID_KEY    = 'appt_staff_extra_image_1_id';
				const URL_KEY   = 'appt_staff_extra_image_1_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Staff_Extra_Image_2_Tag' ) ) {
			class Appt_Booker_Elementor_Staff_Extra_Image_2_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'staff-extra-image-2';
				const TAG_TITLE = 'Staff Extra Image 2';
				const ID_KEY    = 'appt_staff_extra_image_2_id';
				const URL_KEY   = 'appt_staff_extra_image_2_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Staff_Extra_Image_3_Tag' ) ) {
			class Appt_Booker_Elementor_Staff_Extra_Image_3_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'staff-extra-image-3';
				const TAG_TITLE = 'Staff Extra Image 3';
				const ID_KEY    = 'appt_staff_extra_image_3_id';
				const URL_KEY   = 'appt_staff_extra_image_3_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Staff_Extra_Image_4_Tag' ) ) {
			class Appt_Booker_Elementor_Staff_Extra_Image_4_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'staff-extra-image-4';
				const TAG_TITLE = 'Staff Extra Image 4';
				const ID_KEY    = 'appt_staff_extra_image_4_id';
				const URL_KEY   = 'appt_staff_extra_image_4_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Staff_Extra_Image_5_Tag' ) ) {
			class Appt_Booker_Elementor_Staff_Extra_Image_5_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'staff-extra-image-5';
				const TAG_TITLE = 'Staff Extra Image 5';
				const ID_KEY    = 'appt_staff_extra_image_5_id';
				const URL_KEY   = 'appt_staff_extra_image_5_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Service_Extra_Image_1_Tag' ) ) {
			class Appt_Booker_Elementor_Service_Extra_Image_1_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'service-extra-image-1';
				const TAG_TITLE = 'Service Extra Image 1';
				const ID_KEY    = 'appt_service_extra_image_1_id';
				const URL_KEY   = 'appt_service_extra_image_1_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Service_Extra_Image_2_Tag' ) ) {
			class Appt_Booker_Elementor_Service_Extra_Image_2_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'service-extra-image-2';
				const TAG_TITLE = 'Service Extra Image 2';
				const ID_KEY    = 'appt_service_extra_image_2_id';
				const URL_KEY   = 'appt_service_extra_image_2_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Service_Extra_Image_3_Tag' ) ) {
			class Appt_Booker_Elementor_Service_Extra_Image_3_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'service-extra-image-3';
				const TAG_TITLE = 'Service Extra Image 3';
				const ID_KEY    = 'appt_service_extra_image_3_id';
				const URL_KEY   = 'appt_service_extra_image_3_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Service_Extra_Image_4_Tag' ) ) {
			class Appt_Booker_Elementor_Service_Extra_Image_4_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'service-extra-image-4';
				const TAG_TITLE = 'Service Extra Image 4';
				const ID_KEY    = 'appt_service_extra_image_4_id';
				const URL_KEY   = 'appt_service_extra_image_4_url';
			}
		}

		if ( ! class_exists( 'Appt_Booker_Elementor_Service_Extra_Image_5_Tag' ) ) {
			class Appt_Booker_Elementor_Service_Extra_Image_5_Tag extends Appt_Booker_Elementor_Image_Dynamic_Tag_Base {
				const TAG_NAME  = 'service-extra-image-5';
				const TAG_TITLE = 'Service Extra Image 5';
				const ID_KEY    = 'appt_service_extra_image_5_id';
				const URL_KEY   = 'appt_service_extra_image_5_url';
			}
		}

	}
}

if ( ! function_exists( 'appt_booker_final_boot' ) ) {
	function appt_booker_final_boot() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new Appt_Booker_Final();
		}
		return $instance;
	}
}

appt_booker_final_boot();
