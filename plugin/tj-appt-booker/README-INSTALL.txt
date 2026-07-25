TJ APPT BOOKER V1.16.5 - INSTALL, STAFF HELP & VERIFICATION GUIDE
======================================================

BUILT BY
--------
Jason Beer of Titan Jewellery
https://www.titanjewellery.co.uk/


WHAT THIS IS
------------
The former Code Snippets booking system has been converted into a standalone
WordPress plugin with a conditional-loading bootstrap.

This is the no-payment security build. PayPal, deposits, prices, payment
status fields, payment emails, payment AJAX routes and browser-supplied
financial values have been removed from the active plugin.

The engine loads on:
  - wp-admin requests
  - cron runs
  - booking AJAX actions beginning "rgl_booking_"
  - booking summary, cancellation, rescheduling, approval and ICS URLs
  - singular pages containing a booking shortcode, including Elementor widgets

ADMIN MENU BOOKING COUNT
------------------------
The bubble beside Appt-Booker shows future Confirmed and Pending approval
bookings that still need to be fulfilled. It is green when the next booking is
today, orange when the next booking is tomorrow, and red when the next booking
is further away. It excludes past appointments and any booking marked Cancelled,
Completed or No-show. The count and colour update on each admin page load.

It does not load on ordinary product, category, blog, cart, checkout or home
pages unless a booking shortcode is present. Those pages get no booking CSS,
JavaScript, datepicker or engine hooks.



REVIEW FIXES IN V1.11.1
-------------------------
- Elementor image tags now use 18 fixed concrete classes sharing one base class.
  This preserves saved tag identity when Elementor recreates tags at render time,
  without using eval().
- Public and admin add/edit/duplicate booking writes use the same MySQL advisory
  lock and repeat availability validation inside the lock.
- Editing a pending booking preserves Pending approval unless an administrator
  explicitly changes it. An explicit Pending -> Confirmed change sends the same
  approval notification and calendar attachment as the Approve action.
- dbDelta primary-key formatting corrected.
- Blocked-email and recent-booking responses now use the same generic wording.
- Schema column checks are cached for each PHP request to prevent repeated SHOW
  COLUMNS queries during availability calculations.

SECURITY CHANGES IN V1.11.0
---------------------------
- Removed the complete active payment and PayPal subsystem.
- Booking summary and ICS links now use a neutral signed booking-access token.
- Customer and research CSV exports protect against spreadsheet formula
  injection.
- Removed eval() from Elementor dynamic image tags. The corrected v1.12.2
  implementation uses fixed concrete tag classes with shared lookup logic.
- Added a short MySQL named lock around booking availability checks and writes.
  V1.11.1 extends this protection to admin add, edit and duplicate actions.
- New installations no longer create payment-related database columns.
- Existing payment columns on an upgraded database are deliberately left in
  place but are no longer read, written or displayed. They are not dropped
  automatically because destructive schema changes are unnecessary and risky.


V1.12.2 DEFENSIVE SCHEMA GUARDS
--------------------------------
- Approval from the signed admin email link resets the reminder marker only when
  the reminder_sent_at column exists.
- The admin quick status action applies the same guard before resetting the marker.
- This preserves approval/status changes even if a database host ever rejects the
  additive reminder column migration.


V1.13.0 CUSTOMER CHANGES, OUTCOMES AND REMINDER CONTROL
-------------------------------------------------------
- Customers can securely change a confirmed appointment from links in their
  confirmation, reminder, consultant-change email and booking summary.
- The signed change page keeps the existing booking reference, service,
  communication method and consultant. Only the date and time change.
- The old slot remains occupied until the replacement slot has been validated
  and written under the booking lock, so a failed change never loses the
  original appointment.
- Change links stop working at the configurable notice period and for cancelled,
  completed, no-show or pending bookings.
- Completed and No-show are available as admin statuses. Selecting either does
  not send a customer email.
- The admin booking detail shows whether the reminder is scheduled, due, sent,
  skipped because the booking was made inside the reminder window, or unavailable.
- Admins can send or resend a reminder immediately from the booking detail and
  booking actions. Manual reminders are allowed even when automatic reminders
  are disabled, but only for future confirmed appointments.


V1.13.1-1.13.3 CUSTOMER ACTION BUTTON LAYOUT
----------------------------------------------
- Customer email actions use one button per line for consistent rendering on
  mobile and desktop email clients.
- Booking summary, appointment-change, cancellation and approval pages now use
  the same button height, spacing, typography and mobile stacking.
- Destructive actions appear last. On booking summaries the order is Google
  Calendar, iCalendar, Change appointment, then Cancel booking.
- On cancellation pages, Keep my booking appears before the final red
  confirmation button.


V1.13.8 OUTCOME AND RESCHEDULE SAFEGUARDS
------------------------------------------
- Completed and No-show can only be selected after the appointment start time.
  The rule is enforced server-side for add, edit, duplicate and quick-status
  actions, and future appointments no longer show the quick outcome buttons.
- Reschedule tokens include the booking's current date and time. A successful
  appointment change therefore invalidates the previous change link, while the
  new confirmation email supplies a fresh valid link.
- Changelog entries for the 1.13.4 and 1.13.6 mobile/Safari iterations have been
  restored for complete version history.

INSTALL STEPS
-------------
1. DEACTIVATE THE OLD SNIPPET FIRST
   In Code Snippets, deactivate the old Appt Booker snippet. Never leave the
   snippet and plugin active together.

2. UPLOAD THE PLUGIN
   WordPress Admin -> Plugins -> Add New -> Upload Plugin, then select the ZIP.

3. ACTIVATE TJ APPT BOOKER
   Activation runs the existing schema upgrade routine. Booking records,
   services, staff, locations and settings remain in the WordPress database.


VERIFICATION CHECKLIST
----------------------
[ ] WP Admin -> Appt-Booker appears and existing bookings are intact.
[ ] Services contains no price, deposit or PayPal settings.
[ ] Booking add/edit screens contain no payment controls.
[ ] Dashboard -> Upcoming Appointments renders.
[ ] Preview /booking/: the form renders and dates/times populate.
[ ] Submit 1 logged-out test booking and confirm the summary page opens.
[ ] Open the cancellation link from the customer email and test its confirm
    screen without cancelling a real appointment.
[ ] View source on /booking/: booking CSS/JS is present.
[ ] View source on a product page: there are no "rgl-booking" assets and no
    jQuery UI datepicker loaded by this plugin.
[ ] Export a test customer CSV containing a message beginning with = or + and
    confirm spreadsheet software displays it as text rather than a formula.
[ ] Try 2 near-simultaneous submissions for the same slot on staging. Only 1
    should save successfully.
[ ] Place Location Main Image and at least 1 Extra Image dynamic tag on an
    Elementor test template, save it, then view it logged out. Both must render.
[ ] Edit a Pending approval booking without changing its status. Confirm it
    remains pending and no calendar invite is sent.
[ ] Change a Pending approval booking to Confirmed in admin. Confirm the normal
    approval email and calendar attachment are sent.
[ ] Attempt an admin add or duplicate while the same slot is being claimed by a
    public submission. Only valid remaining capacity should be accepted.


NOTES
-----
- Deactivation clears the daily-summary, retention, reminder and queued
  consultant-change cron hooks. It does not delete booking data.
- The /service/, /team/ and /location/ front-end content types are not loaded on
  ordinary public requests. This matches the current Titan Jewellery setup,
  where those generated URLs are not used.
- The engine still performs its idempotent schema/version checks whenever the
  engine itself loads. The performance improvement comes from not loading the
  engine at all on unrelated front-end pages.
- A full WordPress staging test is still required after installation. Static
  analysis and PHP syntax checks cannot reproduce every theme, Elementor,
  database and mail configuration on the live site.



REQUIRED CONTACT DETAILS (v1.12.1)
----------------------------------
Name, email address and phone number are always displayed and required on both
the public booking form and the manual/admin booking forms. The requirement is
also enforced server-side, so it cannot be bypassed by removing the browser's
HTML required attribute or posting directly to admin-ajax.php.

The former setting that could hide the phone field is no longer available. The
phone label remains editable under Settings.

Verification:
1. Try to submit the public form with each contact field blank in turn.
2. Confirm the browser blocks submission and the server rejects a direct request.
3. Confirm manual admin booking creation also requires all three fields.

AUTOMATIC REMINDERS AND CONSULTANT CHANGES (v1.12.0+)
-----------------------------------------------------
- Confirmed bookings can receive one automatic customer reminder. The default is
  24 hours before the appointment and can be changed under Appt-Booker > Settings.
- The reminder includes the current consultant, service, date, time and contact
  method. A prominent signed Cancel booking button is shown while online
  cancellation remains available.
- A booking made after its nominal reminder point is not sent an immediate
  reminder on top of its confirmation email.
- If the appointment date or time is changed, the reminder marker is reset so the
  updated appointment can receive a new reminder at the correct time.
- When a confirmed booking is reassigned, the customer is told who will now
  contact them. The new consultant receives the booking details and the previous
  consultant receives a removal notice. The emails do not disclose why the
  consultant changed.

ADDITIONAL STAGING TESTS
------------------------
1. Enable reminders and set the lead time temporarily to 1 hour. Create a
   confirmed test booking more than 1 hour ahead, then run the
   rgl_booking_reminder_sweep cron event and confirm one email is sent.
2. Run the sweep again and confirm no duplicate reminder is sent.
3. Confirm the reminder's Cancel booking button opens the confirmation page and
   requires a deliberate POST confirmation.
4. Change a confirmed booking from one consultant to another. Confirm the
   customer, new consultant and previous consultant receive the correct emails.
5. Edit a booking without changing consultant and confirm no consultant-change
   emails are sent.
6. Change the appointment date/time after a reminder and confirm reminder_sent_at
   is cleared and a later reminder can be sent for the new slot.


V1.13.0 STAGING TESTS
---------------------
1. Open a confirmed booking email and use Change appointment. Choose a new slot,
   submit it, and confirm the same booking reference now has the new date/time.
2. Confirm the original slot becomes available only after the change succeeds.
3. Try the same change link after marking the booking Completed, No-show or
   Cancelled and confirm it is refused.
4. Confirm customer, consultant and admin receive the appointment-changed email
   and that the customer email contains updated calendar links.
5. Re-open the old Change appointment link used before the successful change and
   confirm it now reports that the link is invalid or expired. Confirm the new
   email link still works.
6. Try to mark a future appointment Completed or No-show and confirm the action is
   refused. After its start time, confirm both outcome actions become available.
7. Mark one past booking Completed and one No-show. Confirm no customer email is sent
   and neither booking blocks future availability.
8. Open the admin booking detail and verify the reminder status and scheduled or
   sent timestamp are clear.
9. Use Send reminder now, confirm one email arrives, and confirm the sent time is
   recorded. Use Send reminder again to test a deliberate resend.
10. After an appointment has passed, mark it Completed, add post-conversation
   notes and save it. Confirm the notes save without the old slot being rejected.
11. Mark a past appointment No-show and confirm no customer email is sent.

V1.13.4-1.13.5 RESPONSIVE EMAIL DETAILS
-----------------------------------------
Version 1.13.4 first stacked labels above values on narrow screens. Version 1.13.5 then changed the email details to a true single-column structure in every email client because some iPhone mail clients ignored the responsive CSS. This prevents dates, times and communication-method values being squeezed into a narrow column.


V1.14.0 NO-SHOW SAFEGUARDS AND PRIVACY
----------------------------------------
- Default policy: 2 recorded no-shows within 180 days moves a new public booking to Pending approval.
- The booking is not rejected automatically. An administrator approves or rejects it.
- Admin booking details show the recent no-show count and whether a future-booking block is active.
- Block future bookings is a separate deliberate admin action. It does not change the booking or email the customer.
- Manual no-show blocks store a salted hash of the email address, not the address itself, and expire after 365 days by default.
- Customer-data deletion removes both booking records and matching future-booking blocks.
- The booking form and full customer emails include dynamic privacy wording explaining the attendance-history check and human review.
- Keep Titan Jewellery's main privacy notice consistent and record the chosen lawful basis and balancing assessment internally.
- Automatic retention is enabled by default. Version 1.14.1 also switches it on once for this existing pre-launch installation, using 6 months for contact-detail minimisation and 30 months for full record deletion. The setting remains editable afterwards.

STAGING TESTS
1. Mark 2 completed past test appointments for the same email as No-show.
2. Make a new logged-out booking with that email. It should save as Pending approval and send the pending emails.
3. Confirm the admin email explains the recent no-show count and still requires a human approve/reject action.
4. Approve it and confirm the normal approved email and calendar attachment arrive.
5. Open the booking detail and use Block future bookings. A new logged-out booking should receive the generic refusal message.
6. Use Allow future bookings and confirm the email can book again.
7. Export customer data and confirm block status/expiry appears. Delete customer data and confirm the block is removed.
8. Change the threshold/window settings and confirm the public privacy paragraph updates automatically.


V1.14.1 RETENTION ENABLED FOR EXISTING INSTALLATION
----------------------------------------------------
- On the first engine load after upgrading, automatic retention is switched on.
- Contact details are minimised after 6 months.
- The full booking record is deleted after 30 months.
- This is a one-time migration. Later changes made in Settings are respected.



V1.16.4 AUDIENCE-SPECIFIC UNLICENSED EMAIL FOOTERS
---------------------------------------------------
- Customer emails no longer display an unlicensed-software warning.
- When branding is required, customer emails show a discreet powered-by footer.
- Internal admin and staff emails retain a prominent unlicensed warning.
- Active trials, active annual licences, grace and warning periods remain unbranded.

V1.16.3 LICENCE ENTRY WORDING

- The licence entry screen now asks users to paste the complete key exactly as supplied.
- Customer-facing wording no longer explains that case or spacing is ignored.

V1.16.1 LICENSING WORDING CLARIFICATION
-----------------------------------------
- Existing booking management continues after an annual licence expires.
- A keyless installation or an expired trial cannot create or reschedule
  bookings.
- No booking enforcement logic or frozen licence constants changed in this
  release.


V1.16.0 BUILT-IN STAFF HELP AND AI GUIDANCE
---------------------------------------------
- The former legacy Instructions page has been replaced by an extensive Help tab.
- It covers the daily booking workflow, booking statuses, adding/editing/approving
  appointments, reminders, customer changes, consultant reassignment, no-shows,
  blocking, availability, privacy, retention, troubleshooting and advanced use.
- The Help tab contains a privacy-aware AI support prompt. Staff can copy either
  the prompt alone or a combined support pack containing the prompt and current
  guide, then paste it into ChatGPT or Claude.
- Staff are told to redact customer names, email addresses, phone numbers, booking
  references and private notes before sharing screenshots or text with an AI tool.
- The plugin header and admin help identify Jason Beer of Titan Jewellery as the
  builder.


V1.16.0 SHARED CONTACT-METHOD AVAILABILITY
-------------------------------------------
- Each service/team member has one weekly availability schedule shared by every allowed contact method.
- Phone, WhatsApp call and WhatsApp message no longer require duplicate time entry.
- Existing method-specific schedules are migrated once into the shared team member schedule.
- Settings now include a customer contact note for each method. Ordinary Phone methods default to a withheld-number message; enter the exact WhatsApp number for WhatsApp methods.
- The detailed no-show safeguard paragraph is no longer appended to the public booking privacy notice. The admin safeguards remain active.
