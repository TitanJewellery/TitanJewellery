TJ APPOINTMENT BOOKER 1.16.4 - LICENSING
========================================

Product slug
------------
tj-appt-booker

Licence behaviour
-----------------
- No key or an invalid key: the public booking form is blocked.
- Active 30-day trial: normal booking operation until the trial expiry date.
- Expired trial: the public booking form is blocked immediately.
- Active annual licence: normal booking operation.
- Annual licence expiry: existing bookings and administration remain available.
- Appointment dates on or after one calendar month after annual expiry are
  limited to one booking per date until a renewed key is entered.
- Once the one restricted booking has been used, cancellation, rescheduling or
  deletion does not reopen that date.
- Existing bookings are never deleted or altered by licensing.
- After an annual licence expires, customer reminders, booking summaries,
  cancellation and existing booking management continue to work.
- A keyless installation or an expired trial cannot create or reschedule
  bookings.


Email branding after licensing enforcement
------------------------------------------
- Customer emails use a discreet powered-by Appointment Booker footer.
- Admin and staff emails use a prominent unlicensed-software warning.
- No branding appears during an active trial, active annual licence, annual
  grace period or annual warning period.

Admin warnings
--------------
- 30 days remaining: discreet notice.
- 7 days remaining: larger warning explaining the future restriction date.
- Expired annual licence: prominent warning explaining the restriction.
- Restriction active: large warning stating that appointment dates are limited
  to one booking per day and renewal removes the restriction.

Licence screen
--------------
WP Admin > Appt-Booker > Licence

Frozen format
-------------
Format 5, constant 125870, 100-character key, product slug tj-appt-booker.
Position 98 remains permanently reserved for the format digit.

Operational rule
----------------
Never create a fresh licence row for a live installation that has accepted a
higher serial. Renew or replace the existing licence so the serial continues.
