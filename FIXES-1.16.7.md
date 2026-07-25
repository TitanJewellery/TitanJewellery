# TJ Appt Booker 1.16.7 — audit fixes

Fixes for the findings in `AUDIT-tj-appt-booker-1.16.5.md`, plus a further
shared-resource defect found in review of the 1.16.6 build (F-16 below).
Fifteen of sixteen are resolved; F-14 is deliberately left alone (reason below).

**1.16.6 was never released — do not install it.** It fixed the capacity
double-count but left the opposite defect open (F-16), which could still put two
calls on the shared phone line. 1.16.7 supersedes it.

- Fixed plugin: `plugin/tj-appt-booker/`
- Regression suite: `tests/run-all.sh` — 78 assertions, no WordPress install needed
- No existing method was removed; ten were added

**Version is now 1.16.7.** Shipping changed code under an unchanged
version number is the exact problem F-04 describes. All three version sites now
agree: plugin header, `TJ_APPT_BOOKER_VERSION`, and `Appt_Booker_Final::VERSION`.

---

## Verification

The suite stubs the ~90 WordPress functions the engine touches (`tests/wp-shim.php`)
so the real class file can be loaded and driven under PHP CLI.

```
$ tests/run-all.sh
=== php -l ===
  OK    tj-appt-booker.php
  OK    class-appt-booker-licence.php
  OK    class-appt-booker.php

run-tests            PASS=23 FAIL=0   merge, holidays, signing, CF, memoisation, tz
test-settings        PASS=13 FAIL=0   settings merge, fail-closed bot check
test-smoke           PASS=6  FAIL=0   booking form renders
test-admin           PASS=18 FAIL=0   all admin tabs render, hostile sort input
test-shared-resource PASS=9  FAIL=0   single-phone rule vs capacity
test-shared-e2e      PASS=7  FAIL=0   same, driven through build_slots()
======================================================
TOTAL  PASS: 78   FAIL: 0
```

Each test was first run against the **original 1.16.5** to confirm it fails there.
Otherwise the tests prove nothing:

```
$ php test-settings-ORIGINAL.php
FAIL  style_button_background survived            got #111111
FAIL  style_button_text survived                  got Book Now
FAIL  style_form_border_color survived            got #cccccc
FAIL  style_typography_font_family survived       got ""
FAIL  no style key reverted to default            reverted: style_button_text, ...
FAIL  submission with NO timestamp field is blocked
FAIL  submission with timestamp 0 is blocked
FAIL  forged signature is blocked
```

Front-end JavaScript is extracted from its nowdoc and checked with `node --check`.

---

## What changed

### F-01 · Settings save wiped all Form Style values — **High**

`save_settings` built a fresh array and called `update_option()`, replacing an
option the Style tab also writes to. Now it merges onto current state:

```php
$settings = $this->get_settings();
$settings = array_merge( $settings, array( /* posted general fields */ ) );
```

Test walks all 58 `style_*` keys and asserts none reverted.

### F-02 · Slot capacity double-counted — **High**

`build_slots()` concatenated the per-combination list with the all-bookings list,
so a booking in both was summed twice by `add_slots_from_window()`.

New `merge_booking_occupancy()` keys rows on booking id. Both queries now select
`id`. It **combines** rather than picking a winner, because each view holds
something the other lacks — the per-combination row has the true `spaces_booked`
(the shared list hardcodes 1), the shared row has the end padded by the
cross-staff buffer. So: larger space count, later end.

```
capacity 6, one existing 3-space booking at 10:00
  before   rows=2  occupancy=4   remaining=2   <- wrong
  after    rows=1  occupancy=3   remaining=3
```

Applied to `build_slots()` and `build_slots_with_status()`.

### F-03 · BST double timezone offset — **Medium-High**

`wp_date( 'G', current_time('timestamp') )` applied the GMT offset twice, running
the 16:00 cut-off at 15:00 for the seven months of BST, and rolling the Friday
week boundary a day early between 23:00 and midnight.

Both sites now read the wall clock from `current_datetime()`. The suite prints
the discrepancy so it stays visible: at the time of writing, site hour 18 vs the
old reading of 19.

The other six `current_time('timestamp')` uses pair it with `gmdate()`/`strtotime()`,
which is the correct idiom, and were left alone.

### F-04 · Version constants disagreed — **Medium**

`VERSION` was `1.16.4` while the plugin shipped as 1.16.5. It drives asset
cache-busting, the admin header and the schema gate. All sites now read 1.16.6,
with a header note that they must stay in step.

### F-05 · Unauthenticated availability endpoint — **Medium**

`ajax_get_available_dates` is `nopriv` and its nonce is public, so it is
effectively open. Three changes:

- the day walk is clamped to `latest_bookable_date()` instead of always running
  `max_days_ahead` (60) iterations
- `date_is_globally_offerable()` gates each day before any DB work, so weekends,
  bank holidays and out-of-window dates cost nothing
- results cached in a transient for 60s, keyed on the filter combination

Safe to cache: `ajax_get_slots()` is authoritative per date, and `ajax_submit()`
re-validates the chosen slot under the write lock before inserting.

### F-06 · Schema introspection on every request — **Medium**

`table_exists()` ran `SHOW TABLES LIKE` uncached, and the constructor fanned out
to ~9 probes per request. It is now memoised per request (invalidated in
`install_schema()`), and `maybe_upgrade_schema()` returns immediately when the
stored version is current.

Test: 25 consecutive `table_exists()` calls issue 0 queries.

### F-07 · Spoofable Cloudflare header — **Medium**

`CF-Connecting-IP` was trusted unconditionally, so rotating a fake value defeated
the per-IP throttle for anything reaching the origin directly. Now only honoured
when `REMOTE_ADDR` is a published Cloudflare edge address, with IPv4 and IPv6
CIDR matching via `inet_pton` bit comparison.

17 CIDR boundary cases tested (range edges, family mismatches, malformed input).
Ranges are filterable through `tj_appt_booker_cloudflare_ip_ranges`.

### F-08 · Bot check was opt-out — **Medium**

The time-to-submit test only ran `if ( isset(...) )` and `if ( $loaded_at > 0 )`,
so omitting the field or sending `0` skipped it. It now fails closed, and the
timestamp is signed with `wp_hash()` so a bot cannot mint its own. Replay is
bounded at 30 days.

### F-09 · Bank holidays expired in 2029 — **Medium**

The hardcoded list ended `2029-12-26`; from 2030 the plugin would have offered
bookings on Christmas Day and stopped detecting long breaks.

Now computed on a rolling window (current year −1 to +3): Easter via
Meeus/Jones/Butcher, the three movable Mondays, and UK substitute-day rules for
New Year / Christmas / Boxing Day.

Verified to reproduce the original 2025–2029 list **exactly** — 40 dates, no
additions, no omissions — then to keep going correctly (2032 Christmas on a
Saturday takes Mon 27 / Tue 28; 2033 New Year on a Saturday takes Mon 3rd).

Filterable via `tj_appt_booker_bank_holidays` for one-off state holidays.

### F-10 · Cached pages served stale nonces — **Medium**

A page from a CDN or bfcache older than the nonce lifetime failed
`check_ajax_referer()` with a bare `-1` and no logging. New public
`rgl_booking_refresh_token` endpoint issues a fresh nonce plus signed timestamp;
the form refreshes both on load and on bfcache restore.

The endpoint has no nonce check by design — requiring a possibly-expired nonce to
refresh an expired nonce defeats the purpose. It returns exactly what rendering
the page returns.

### F-11 · Turnstile broke all bookings — **Low-Med**

The widget div was rendered but `api.js` never enqueued, so no token was produced
and every booking was rejected. The plugin now enqueues it when the setting is on
and a site key is present.

### F-12 · Retention comments contradicted the code — **Low-Med**

Comments said "off by default"; it is `=> 1` and force-enabled on upgrade.
Comments corrected to state plainly that it is on, irreversible, and that
blanking `customer_email` invalidates existing summary/cancel/reschedule links.
The delete is now batched at 500 rows.

### F-13 · Missing licence file failed silently — **Low-Med**

The shortcode returned an empty string with no admin notice. The bootstrap now
raises one, matching how a missing engine file is handled.

### F-16 · Shared phone allowed simultaneous calls at capacity > 1 — **High**

*Found in review of 1.16.6; present in 1.16.5 too, and not addressed by the
F-02 de-duplication.*

F-02 was one half of the problem. `get_all_bookings_for_day()` reports every
booking as `spaces_booked => 1`, and `add_slots_from_window()` only closed a slot
once the summed spaces reached capacity. So a booking on a *different* service,
location or team member consumed exactly one unit of the target slot's capacity
instead of blocking it.

With Capacity per Time Slot at 2, a Service B call at 10:00 left Service A's
10:00 slot showing **"1 space left"** — a second call bookable on a phone line
the code treats as single-occupancy. Higher capacity made it worse.

The same flaw absorbed the cross-staff buffer: with capacity 6, the padding that
should have held the line clear after a call was simply counted as one space and
the next slot stayed open.

Capacity and the shared line answer different questions. Capacity is *how many
people can join this one call*; the shared line is *whether the phone is free at
all*. Capacity must never outvote it.

New `booking_is_same_session()` decides whether an overlapping booking is the
same call — it must have come from the per-combination query (not tagged
`blocks_shared` by the merge) **and** start at the same minute. Anything else is
a different call, and when shared-resource mode is on it closes the slot
outright:

```php
if ( $shared_resource_mode && ! $this->booking_is_same_session( $booking, $slot_start ) ) {
    $phone_in_use = true;
    break;
}
$booked_spaces += max( 1, absint( $booking['spaces_booked'] ) );
```

Requiring an equal start time also handles a staggered overlap produced by a
custom offer interval — two calls beginning 30 minutes apart in the same
combination are separate calls, not one session.

`build_slots_with_status()` mirrors the rule exactly, so the displayed grid can
never advertise a slot the booking path would refuse.

With the shared-resource setting **off**, behaviour is unchanged: capacity is
summed as before.

Covered by `test-shared-resource.php` (9 assertions, unit level) and
`test-shared-e2e.php` (7, driven through `build_slots()` against a stubbed
database). The e2e test separates all three builds cleanly:

```
1.16.5 (original)   PASS=4 FAIL=3   double-count AND single-phone both broken
1.16.6 (first pass) PASS=6 FAIL=1   double-count fixed, single-phone still broken
1.16.7 (this)       PASS=7 FAIL=0
```

### F-17 · Stale version strings — **Hygiene**

*Also found in review of 1.16.6.* `README-LICENSING.txt` said 1.16.4,
`README-INSTALL.txt` said v1.16.5, and `tj-appt-booker.php` lines 22 and 75 still
described the engine as v1.15.0. All now read 1.16.7. The dated `V1.16.x`
headings further down `README-INSTALL.txt` are a changelog and were left as-is.

### F-15 · Small defects — **Low**

Dead plain-text email body removed; duplicated `'block' === $mode` condition
fixed; locations JSON payload re-slashed so `sanitize_location_rows()` does not
strip legitimate backslashes; Content Research dates validated before reaching a
filename; admin sort whitelist rejects non-string input; rate-limit email
lowercased to match the other two lookups.

---

## Not changed

**F-14 — licence domain binding matches any host label.** A key for
`titanjewellery` validates on any hostname containing that label. Tightening it
to the registrable domain is a two-line change, but it would **invalidate keys
already issued** to customers whose host does not match under the stricter rule.
That is a commercial decision, not a code one. Say the word and I will implement
it behind a key-format bump so existing format-5 keys keep working.

Also unchanged, and worth stating: offline licence validation is decidable
client-side and can always be bypassed by editing the plugin. That is inherent to
offline licensing, not a defect.

---

## Deployment notes

1. No database migration. Schema is unchanged; `spaces_booked`, `customer_interests`,
   `cancellation_reason` and `reminder_sent_at` adds remain idempotent.
2. No settings migration. The merge fix is forward-only — it stops future saves
   destroying style values, but **cannot recover values already lost** to an
   earlier save. Check the Form Style tab after upgrading and re-enter anything
   that has reverted.
3. If you hand-added the Turnstile script to your theme header, remove it — the
   plugin enqueues it now, and two copies will log a console warning.
4. Clear any page cache after upgrading so the form picks up the new hidden
   signature field. It self-heals on load regardless, but this avoids a first-hit
   round trip.
5. Bank holidays now include the current year −1 through +3 and recompute
   automatically. Nothing to maintain annually.
6. **If you run Capacity per Time Slot above 1 with shared-resource mode on,
   expect fewer slots to be offered than before.** That is the F-16 fix working:
   those slots were being offered incorrectly. Availability at capacity 1 — the
   normal 1-to-1 configuration — is unchanged.

## Still to do before going live

These tests need a real WordPress staging site and cannot be covered by the PHP
shim suite:

- an ordinary booking end to end, including the confirmation email
- a booking from a page served out of cache (validates the F-10 nonce refresh)
- two overlapping bookings across different staff with shared-resource on
- a capacity-2 service, confirming a foreign booking now closes the slot
- Turnstile on, with the site key set, confirming a booking still completes
- saving the Settings tab and then re-checking the Form Style tab (F-01)
