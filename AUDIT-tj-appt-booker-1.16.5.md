# TJ Appt Booker v1.16.5 — Code & Security Audit

**Package:** `tjapptbookerv1.16.5licensed.zip`
**Audit date:** 2026-07-25
**Scope:** full static review of all three PHP files (14,525 lines) plus the embedded front-end and admin JavaScript.

| File | Lines |
|---|---|
| `tj-appt-booker.php` (bootstrap) | 212 |
| `includes/class-appt-booker.php` (engine) | 13,864 |
| `includes/class-appt-booker-licence.php` | 449 |

`php -l` passes cleanly on all three files under PHP 8.4.

---

## Version confirmation

**Yes — this is 1.16.5**, but only partially.

| Location | Value |
|---|---|
| Plugin header `Version:` | `1.16.5` |
| `TJ_APPT_BOOKER_VERSION` | `1.16.5` |
| `Appt_Booker_Final::VERSION` (engine, line 385) | **`1.16.4`** |

See finding **F-04** — the stale engine constant is not cosmetic.

---

## Summary

The security posture is genuinely good. Every AJAX endpoint has a nonce check, every admin postback is gated on `manage_options` + nonce, every SQL statement is prepared, the public token endpoints use `wp_hash()` + `hash_equals()`, CSV exports are protected against formula injection, and output escaping is close to exhaustive (I found one unescaped `echo` and it was pre-escaped at assignment). **No SQL injection, no XSS, no authentication bypass, and no privilege escalation was found.**

What the plugin does have is a set of correctness, data-loss and resource-consumption defects, one of which (F-01) destroys admin configuration on a routine action.

| ID | Severity | Area | Issue |
|---|---|---|---|
| F-01 | **High** | Data loss | Saving the Settings tab silently wipes all 58 Form Style settings |
| F-02 | **High** | Correctness | Slot capacity is double-counted when shared-resource mode is on |
| F-03 | **Medium-High** | Correctness | BST double-timezone-offset makes the availability cut-off fire an hour early |
| F-04 | **Medium** | Release | Engine `VERSION` still `1.16.4` — stale asset cache-busting + wrong admin display |
| F-05 | **Medium** | DoS | Unauthenticated, unthrottled `rgl_booking_get_available_dates` issues hundreds of queries |
| F-06 | **Medium** | Performance | ~9 uncached schema-introspection queries on every engine-loading request |
| F-07 | **Medium** | Security | `HTTP_CF_CONNECTING_IP` trusted unconditionally → IP throttle bypass |
| F-08 | **Medium** | Security | Time-to-submit bot check is skipped entirely if the field is omitted |
| F-09 | **Medium** | Time bomb | Hardcoded UK bank holidays run out on 2029-12-26 |
| F-10 | **Medium** | Reliability | Cached pages serve stale nonces → silent booking failures |
| F-11 | **Low-Med** | Reliability | Enabling Turnstile without hand-adding the script rejects every booking |
| F-12 | **Low-Med** | Data | Retention deletion is ON by default while comments claim it is off |
| F-13 | **Low-Med** | Reliability | Missing licence file makes the booking form vanish with no message |
| F-14 | **Low** | Licensing | Domain binding matches any single host label |
| F-15 | **Low** | Misc | Six small defects (dead code, redundant condition, double-unslash, case handling) |

---

## High severity

### F-01 — Saving Settings wipes every Form Style setting

`class-appt-booker.php:8280-8360`

The `save_settings` branch builds a **brand new** `$settings` array from scratch and writes it with `update_option()`:

```php
if ( 'save_settings' === $action ) {
    $settings = array(
        'form_title' => ...,
        /* ~50 general keys, no style_* keys at all */
    );
    // ...notification toggles appended...
    update_option( self::OPTION_SETTINGS, $settings );   // line 8359 — full replace
```

General settings and Form Style settings share the single `rgl_booking_final_settings` option. `update_option()` replaces the option wholesale, and `get_settings()` (line 4494) back-fills anything missing from `default_settings()`. So every one of the 58 `style_*` keys silently reverts to factory defaults.

I diffed `default_settings()` against the keys `save_settings` writes. Exactly these are lost:

```
style_form_layout, style_typography_font_family,
style_form_title_{padding,color,background,font_size,font_weight,letter_spacing},
style_form_intro_{font_size,font_weight,letter_spacing},
style_form_{border_radius,border_width,border_color,padding,column_gap,row_gap},
style_field_label_{padding,color,background},
style_label_{border_radius,border_width,border_color,font_size,font_weight,letter_spacing},
style_field_{padding,border_radius,border_width,border_color,font_size,font_weight,letter_spacing},
style_button_{text,text_color,background,font_size,font_weight,letter_spacing,
              hover_text_color,hover_background,border_radius,border_width,
              border_color,padding,margin},
style_reset_button_{text,text_color,background,hover_text_color,hover_background,
                    font_size,font_weight,letter_spacing,border_radius,
                    border_width,border_color,padding}
```

**Reproduction:** Form Style tab → change the button colour → Save. Settings tab → change anything (or just click Save) → the button colour is back to `#111111`.

The Style tab does this correctly — `sanitize_style_settings( $_POST, $this->get_settings() )` (line 4394) merges onto existing values. The Settings tab does not.

**Fix** — merge instead of replace:

```php
$settings = array_merge( $this->get_settings(), array(
    'form_title' => ...,
    /* rest unchanged */
) );
update_option( self::OPTION_SETTINGS, $settings );
```

---

### F-02 — Slot capacity double-counted under shared-resource mode

`class-appt-booker.php:6008-6020` and `6115-6122`, consumed at `5364-5371` and `6180-6186`

`build_slots()` merges two booking lists:

```php
$existing = $this->get_existing_bookings_for_day( $location_id, $service_id, $staff_id, $date, ... );

if ( ! empty( $settings_for_slots['shared_resource_enabled'] ) ) {   // default: 1
    $shared = $this->get_all_bookings_for_day( $date, $exclude_booking_id );
    if ( ! empty( $shared ) ) {
        $existing = array_merge( $existing, $shared );
    }
}
```

`get_existing_bookings_for_day()` filters by location + service + staff. `get_all_bookings_for_day()` returns **every** booking on that date. A booking matching the current combination therefore appears in both arrays.

The inline comment says this is safe:

> `// Duplicates are harmless - the overlap test in add_slots_from_window is idempotent.`

That was true when the test was boolean. It is now a **sum** (line 5364-5369):

```php
$booked_spaces = 0;
foreach ( $existing as $booking ) {
    if ( $slot_start < $booking['end'] && $slot_end > $booking['start'] ) {
        $booked_spaces += max( 1, absint( $booking['spaces_booked'] ?? 1 ) );
    }
}
$remaining_spaces = max( 0, $slot_capacity - $booked_spaces );
```

There is a second error compounding it: `get_all_bookings_for_day()` hardcodes `'spaces_booked' => 1` (line 5854) regardless of the real quantity.

**Worked example** — a workshop with `slot_capacity = 6`, one existing booking of 3 spaces at 10:00:

| | Contributes |
|---|---|
| From `get_existing_bookings_for_day` | 3 (correct) |
| From `get_all_bookings_for_day` | 1 (hardcoded) |
| **Counted total** | **4** |
| Remaining shown | 2 |
| Remaining actual | 3 |

With `slot_capacity = 1` (Titan's own 1-to-1 calls) the bug is masked — both paths push the count to ≥1 and the slot closes either way. It only bites on the capacity feature, which is a first-class admin setting: *"Use 1 for private appointments, or a higher number for classes, workshops, and group sessions"* (`class-appt-booker.php:13242`).

`build_slots_with_status()` has the identical defect at line 6180-6186.

**Fix** — de-duplicate before summing, keyed on booking id (which means both queries need to select `id`):

```php
$by_id = array();
foreach ( $existing as $b ) { $by_id[ $b['id'] ] = $b; }   // real row wins
$existing = array_values( $by_id );
```

---

## Medium severity

### F-03 — Double timezone offset breaks the availability cut-off during BST

`class-appt-booker.php:5688-5689` and `5725-5727`

```php
$now_ts   = current_time( 'timestamp' );   // already UTC + gmt_offset
$now_hour = (int) wp_date( 'G', $now_ts ); // wp_date applies gmt_offset AGAIN
```

`current_time('timestamp')` returns `time() + gmt_offset*3600` — a shifted value, not a real UNIX timestamp. `wp_date()` expects a real timestamp and converts it into the site timezone. Feeding one to the other applies the offset twice.

For a UK site during British Summer Time (`gmt_offset = 1`, roughly late March to late October):

- At **15:30 local**, `$now_hour` evaluates to **16**, so the default 16:00 cut-off has already tripped. `earliest_bookable_date()` advances by an extra working day an hour early, every day, for seven months of the year.
- `latest_bookable_date()` (line 5725-5727) reads both `$dow` and `$hour` the same way. Between 23:00 and midnight local, `wp_date('N', $now_ts)` reports **tomorrow's** day number, so the Friday-cut-off week roll-over fires a day early once a week.

During GMT (`gmt_offset = 0`) both are correct, which is why this reads as an intermittent "the calendar closed early today" complaint rather than a reproducible bug.

**Fix** — use real timestamps with `wp_date`, or drop `wp_date` entirely:

```php
$now      = current_datetime();          // DateTimeImmutable in site tz
$now_hour = (int) $now->format( 'G' );
$dow      = (int) $now->format( 'N' );
```

The other four `current_time('timestamp')` sites (6028, 6125, 6601, 6610, 6829, 12075) all pair it with `gmdate()`/`strtotime()`, which is the correct idiom. Only the two `wp_date()` pairings are wrong.

---

### F-04 — Engine `VERSION` constant not bumped to 1.16.5

`class-appt-booker.php:385`

```php
const VERSION = '1.16.4';
```

Three consequences:

1. **Stale browser caches.** `self::VERSION` is the cache-buster for both front-end assets (`wp_register_style`/`wp_register_script`, lines 8886 and 8897). Returning visitors keep 1.16.4's inline CSS/JS after the 1.16.5 upgrade.
2. **Wrong version on screen.** The admin page header renders `Appt-Booker V.<?php echo esc_html( self::VERSION ); ?>` (line 10770) — it will read "V.1.16.4" on a 1.16.5 install, which makes support triage unreliable.
3. **Schema-upgrade gate.** `maybe_upgrade_schema()` (line 2683) compares the stored DB version against `self::VERSION`. Not harmful here (no schema change in 1.16.5) but it means the release-versioning and schema-versioning are coupled to a constant that was not maintained.

**Fix:** set it to `1.16.5`, and ideally derive it once — `define( 'TJ_APPT_BOOKER_VERSION', ... )` in the bootstrap is already the single source of truth.

---

### F-05 — Unauthenticated, unthrottled date-availability endpoint

`class-appt-booker.php:10259-10313`

`ajax_get_available_dates` is registered for `wp_ajax_nopriv_` and is guarded only by `check_ajax_referer( 'appt_booker_nonce', 'nonce' )` — a nonce that is printed publicly on every page containing the booking form, so it is freely obtainable.

The handler calls `build_available_dates_for_filters()`, which loops:

```php
for ( $offset = 0; $offset <= $max_days; $offset++ ) {   // $max_days defaults to 60
    foreach ( $candidates as $candidate ) {              // services × locations × staff
        $slots = $this->build_slots( ... );
```

Each `build_slots()` call that reaches the DB stage issues roughly 5-7 queries (`restricted_date_is_consumed`, `get_existing_bookings_for_day`, `get_all_bookings_for_day`, `daily_cap_reached` — each of which also calls the uncached `table_exists()`, see F-06).

Most of the 61 days fall outside the ~2-week booking window and short-circuit in `date_is_within_limits_for_rules()` before touching the DB. But the in-window days do not: with ~10 bookable days and a modest 3 services × 2 locations × 2 staff matrix, one request produces on the order of **600+ database queries**. The anti-abuse layer (`request_is_abusive()`) is only wired into `ajax_submit` — this endpoint has no honeypot, no throttle, no Turnstile.

A trivial loop against this endpoint is an effective database-exhaustion vector against the origin.

**Fix (in order of value):**
1. Cache the computed date list in a transient keyed on `md5(location|service|staff|allowed-ids)` for 60-300 seconds. Availability changes rarely; this collapses the cost to near zero for both attackers and real users.
2. Apply the existing per-IP throttle to the read endpoints, not just `ajax_submit`.
3. Clamp the loop to `min( $max_days, days_until( latest_bookable_date() ) )` — iterating 60 days when the window is 14 is pure waste.

---

### F-06 — Uncached schema introspection on every request

`class-appt-booker.php:4508-4513`, called from `2681-2691`

`table_exists()` runs a live `SHOW TABLES LIKE` every single time it is called, with no memoisation:

```php
private function table_exists() {
    global $wpdb;
    $table = $this->table;
    $found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
    return $found === $table;
}
```

The constructor calls `maybe_upgrade_schema()` unconditionally, which fans out to:

- 1 × `SHOW TABLES LIKE` (line 2683)
- 4 × `ensure_*_column()` → each calls its `*_column_exists()` → each does 1 × `SHOW TABLES LIKE` **plus** 1 × `SHOW COLUMNS FROM ... LIKE`

That is **9 schema-introspection queries before the request does any work**, on every admin page load, every cron run, every booking AJAX call, and every front-end page containing a booking shortcode. `table_exists()` is then called dozens more times across the request from `get_admin_bookings()`, `daily_cap_reached()`, `get_existing_bookings_for_day()`, etc.

The column results *are* memoised in `$this->column_exists_cache`; the table check is not.

**Fix:**

```php
private $table_exists_cache = null;

private function table_exists() {
    if ( null !== $this->table_exists_cache ) {
        return $this->table_exists_cache;
    }
    global $wpdb;
    $this->table_exists_cache = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $this->table ) ) === $this->table );
    return $this->table_exists_cache;
}
```

And gate `maybe_upgrade_schema()` on the version option so the four `ensure_*` probes only run when the stored DB version is behind:

```php
private function maybe_upgrade_schema() {
    $stored = (string) get_option( self::OPTION_DB_VERSION, '' );
    if ( version_compare( $stored, self::VERSION, '>=' ) && $this->table_exists() ) {
        return;   // nothing to do — skip all column probes
    }
    // ...existing body...
}
```

This is the single highest-leverage performance change in the plugin. Note that it interacts with F-04: the version gate only works if `VERSION` is actually maintained.

---

### F-07 — `HTTP_CF_CONNECTING_IP` trusted without verifying the request came from Cloudflare

`class-appt-booker.php:2352-2369`

```php
// Cloudflare provides the original visitor IP here. Trustworthy because
// the site is fronted by Cloudflare Pro.
if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
    $candidates[] = wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] );
}
```

The header is preferred over `REMOTE_ADDR` with no check that `REMOTE_ADDR` is in a Cloudflare range. `CF-Connecting-IP` is a client-supplied HTTP header like any other — Cloudflare overwrites it on requests that pass *through* Cloudflare, but anything reaching the origin directly can set it freely.

**Impact:** the per-IP submission throttle (`security_throttle_transient_key( $ip )`, default 60s) is defeated by rotating a fabricated header value on each request. That leaves the honeypot and the time-to-submit check (see F-08) as the only bot defences unless Turnstile is on.

**Exploitable only if** the origin is reachable without going through Cloudflare — direct-to-IP, a leaked origin hostname, a stale DNS record, or an unproxied subdomain. That is exactly the condition an attacker probes for, and the comment's assumption ("site is fronted by Cloudflare Pro") is a deployment property, not a code guarantee.

**Fix:**

```php
private function get_client_ip_for_throttle() {
    $remote = isset( $_SERVER['REMOTE_ADDR'] ) ? trim( (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

    // Only honour CF-Connecting-IP when the connecting peer really is Cloudflare.
    if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) && $this->ip_in_cloudflare_ranges( $remote ) ) {
        $cf = trim( (string) wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
        if ( filter_var( $cf, FILTER_VALIDATE_IP ) ) {
            return $cf;
        }
    }
    return filter_var( $remote, FILTER_VALIDATE_IP ) ? $remote : '';
}
```

Cloudflare publishes its ranges at `https://www.cloudflare.com/ips-v4` and `/ips-v6`. Alternatively, install Cloudflare's own `mod_remoteip`/`CF-Connecting-IP` restore at the web-server level and read only `REMOTE_ADDR` here.

---

### F-08 — Time-to-submit check is opt-out for the attacker

`class-appt-booker.php:2405-2417`

```php
if ( $min_seconds > 0 && isset( $_POST['rgl_booking_form_loaded_at'] ) ) {
    $loaded_at = absint( wp_unslash( $_POST['rgl_booking_form_loaded_at'] ) );
    if ( $loaded_at > 0 ) {
        $elapsed = time() - $loaded_at;
        if ( $elapsed < $min_seconds ) { /* block */ }
    }
}
```

Two guards make the check optional for anything not using a browser:

- `isset( $_POST['rgl_booking_form_loaded_at'] )` — omit the field and the entire block is skipped.
- `$loaded_at > 0` — send `rgl_booking_form_loaded_at=0` and it is skipped.

Any scripted submission that simply doesn't echo back the hidden field passes. There is also no upper bound, so a timestamp harvested weeks ago still validates, and no signing, so a bot can just send `time() - 3600`.

**Fix** — treat a missing/zero value as failure, and sign the timestamp so it cannot be fabricated:

```php
$loaded_at = isset( $_POST['rgl_booking_form_loaded_at'] ) ? absint( wp_unslash( $_POST['rgl_booking_form_loaded_at'] ) ) : 0;
$sig       = isset( $_POST['rgl_booking_form_sig'] ) ? sanitize_text_field( wp_unslash( $_POST['rgl_booking_form_sig'] ) ) : '';

if ( $min_seconds > 0 ) {
    if ( ! $loaded_at || ! hash_equals( wp_hash( 'rgl_form_ts|' . $loaded_at ), $sig ) ) {
        $reason = 'Please reload the page and try again.';
        return true;
    }
    $elapsed = time() - $loaded_at;
    if ( $elapsed < $min_seconds || $elapsed > DAY_IN_SECONDS ) {
        $reason = 'Please take a moment to review your booking before submitting.';
        return true;
    }
}
```

Emit the matching `rgl_booking_form_sig` hidden field alongside the timestamp at `class-appt-booker.php:10047`. Caveat: this makes the form non-cacheable in the same way the nonce already does — see F-10.

---

### F-09 — Hardcoded UK bank holidays expire on 2029-12-26

`class-appt-booker.php:5630-5648`

The list is a flat array covering 2025-2029 only. I spot-checked the substitute days and the data is correct (2026-12-28 for Saturday Boxing Day, 2027-12-27/28, 2028-01-03 for Saturday New Year's Day) — the problem is purely that it terminates.

From **1 January 2030** onwards `is_working_day()` returns `true` for every weekday, so:

- Bookings will be offered on Good Friday, Easter Monday, the May bank holidays, August bank holiday, Christmas Day and Boxing Day.
- `is_post_long_break_day()` stops detecting long breaks, so the "block the first working day after a 3+ day break" rule silently disables itself too.

There is no admin UI to extend the list, no admin notice as the horizon approaches, and nothing in the code that fails loudly. It will present as customers booking consultations on Christmas Day.

**Fix** — compute the movable feasts and make the list overridable:

```php
private function get_uk_bank_holidays() {
    $year  = (int) gmdate( 'Y' );
    $dates = array();

    for ( $y = $year - 1; $y <= $year + 3; $y++ ) {
        $easter = new DateTimeImmutable( '@' . easter_date( $y ) );   // ext-calendar
        $dates[] = $easter->modify( '-2 days' )->format( 'Y-m-d' );   // Good Friday
        $dates[] = $easter->modify( '+1 day' )->format( 'Y-m-d' );    // Easter Monday
        $dates[] = $this->uk_substitute( $y . '-01-01' );
        $dates[] = gmdate( 'Y-m-d', strtotime( "first monday of may $y" ) );
        $dates[] = gmdate( 'Y-m-d', strtotime( "last monday of may $y" ) );
        $dates[] = gmdate( 'Y-m-d', strtotime( "last monday of august $y" ) );
        $dates[] = $this->uk_substitute( $y . '-12-25' );
        $dates[] = $this->uk_substitute( $y . '-12-26' );
    }

    /** Allows one-off royal/state holidays to be added without a code change. */
    return apply_filters( 'tj_appt_booker_bank_holidays', array_values( array_unique( $dates ) ) );
}
```

`easter_date()` needs `ext-calendar`; if that is not guaranteed on the host, use the Anonymous Gregorian (Meeus/Jones/Butcher) algorithm inline. `uk_substitute()` rolls a weekend date forward to the next non-holiday weekday. The filter matters regardless — one-off holidays (coronations, state funerals) are announced with weeks of notice and cannot wait for a plugin release.

---

### F-10 — Cached pages serve stale nonces

`class-appt-booker.php:8904-8923`

```php
wp_localize_script( 'appt-booker', 'RGLBookingSystem', array(
    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
    'nonce'   => wp_create_nonce( 'appt_booker_nonce' ),
    ...
) );
```

The nonce is baked into the page HTML. WordPress nonces roll every 12 hours and expire at 24. A page served from Cloudflare's cache, a page-cache plugin, or a browser back-forward cache older than that carries a dead nonce, and `check_ajax_referer()` (which defaults to `$die = true`) responds with a bare `-1`.

The front-end JS has no 403/`-1` branch — it will surface a generic failure or hang. For a booking form on a Cloudflare-fronted site this is the single most likely cause of "the form just doesn't do anything" reports, and it is invisible to the admin because nothing is logged.

**Fix** — refresh the nonce at page load rather than trusting the cached value:

1. Register a `wp_ajax_nopriv_rgl_booking_nonce` endpoint that returns a fresh `appt_booker_nonce`.
2. On DOM-ready, fetch it and overwrite `RGLBookingSystem.nonce` before any other request.
3. Handle `-1` / `403` from every `$.post` by re-fetching the nonce once and retrying.

Alternatively, exclude booking-form URLs from page caching, but that surrenders the caching benefit the bootstrap was written to preserve.

---

## Low-medium severity

### F-11 — Turnstile requires a manual script tag or every booking is rejected

`class-appt-booker.php:10048-10050`, `2434-2441`, admin note at `12414`

When Turnstile is enabled the plugin renders the widget container:

```php
<div class="cf-turnstile" data-sitekey="<?php echo esc_attr( $settings['security_turnstile_site_key'] ); ?>"></div>
```

but never enqueues `https://challenges.cloudflare.com/turnstile/v0/api.js`. The admin screen tells the operator to add it to the theme header by hand. If they don't:

- The div stays empty, no `cf-turnstile-response` input is created.
- `request_is_abusive()` sees `'' === $token` and returns true.
- **Every booking is rejected** with "Security challenge failed. Please reload the page and try again."

A checkbox in the admin that silently disables the entire booking form unless an undocumented-in-place manual step is also performed is a foot-gun. The plugin should own the dependency:

```php
if ( ! empty( $settings['security_turnstile_enabled'] ) && ! empty( $settings['security_turnstile_site_key'] ) ) {
    wp_enqueue_script(
        'cf-turnstile',
        'https://challenges.cloudflare.com/turnstile/v0/api.js',
        array(), null, true
    );
}
```

(Add `async`/`defer` via the `script_loader_tag` filter.) A `wp_add_inline_script` guard that checks `window.turnstile` exists and warns in the console would also help diagnosis.

---

### F-12 — Retention deletion is enabled by default, contradicting its own comments

`class-appt-booker.php:2318-2321`, `2548-2565`, `6593-6595`

Three comments state retention is off by default:

- line 2318: `// Data retention (added). All off by default - nothing is removed until enabled.`
- line 6594: `return; // Off by default - never acts until switched on.`

The code says otherwise:

```php
'retention_enabled' => 1,   // line 2319
```

and `enable_retention_for_existing_installation()` (line 2548) force-writes `retention_enabled = 1` into the stored settings on upgrade, for installations that never opted in.

So out of the box, a daily 03:15 cron:

- **permanently deletes** every booking row with `created_at` older than **30 months** (`DELETE FROM ... WHERE created_at < %s`, line 6602)
- blanks `customer_email` and `customer_phone` on every row older than **6 months** (line 6611)

This may well be the intended GDPR behaviour for Titan, but the comments actively mislead anyone reading the code before enabling it, and the delete is unrecoverable. Two things worth doing:

1. Correct the comments so they describe what the code does.
2. Note that minimisation breaks previously issued links: `get_booking_access_token_for_booking()` (line 2836) hashes `customer_email`, so once it is blanked at 6 months, every summary / ICS / cancel / reschedule URL for that booking stops validating. That is probably desirable, but it should be a documented consequence rather than a surprise.

The `DELETE` is also unbatched. On a table with a large backlog the first sweep is one long-running statement; adding `LIMIT 500` and looping would be safer on shared hosting.

---

### F-13 — Missing licence file silently removes the booking form

`tj-appt-booker.php:59-61`, `class-appt-booker.php:5869-5871`, `9921-9924`

The bootstrap loads the licence file defensively:

```php
if ( is_readable( TJ_APPT_BOOKER_LICENCE ) ) {
    require_once TJ_APPT_BOOKER_LICENCE;
}
```

but the engine treats "function missing" as "unlicensed":

```php
private function licence_booking_form_allowed() {
    return function_exists( 'tj_appt_licence_booking_form_allowed' ) && tj_appt_licence_booking_form_allowed();
}
```

and the shortcode then renders nothing at all:

```php
if ( ! $this->licence_booking_form_allowed() ) {
    return function_exists( 'tj_appt_licence_public_notice' ) ? tj_appt_licence_public_notice() : '';
}
```

If the licence file is missing — a partial FTP upload, a security scanner quarantining it, a botched deploy — the booking form disappears from the page with **no output, no admin notice, and no log entry**. Compare the engine-missing path, which does raise an `admin_notices` error (`tj-appt-booker.php:76-81`).

**Fix:** mirror the engine's handling — if `TJ_APPT_BOOKER_LICENCE` is not readable, register an `admin_notices` callback saying so explicitly.

---

### F-14 — Licence domain binding matches any single host label

`class-appt-booker-licence.php:188-198`

```php
$host = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
foreach ( explode( '.', $host ) as $label ) {
    if ( $this->domain_digits( $label ) === $data['domain_digits'] ) {
        $domain_match = true;
        break;
    }
}
```

The key encodes the hash of a **single label**, and validation succeeds if *any* label of the host matches. A key issued for `titanjewellery` validates on `titanjewellery.co.uk`, but equally on `titanjewellery.example.net`, `shop.titanjewellery.io`, or any hostname containing that label anywhere. A key issued for a generic label would validate very widely.

This is a licensing-enforcement weakness rather than a site-security issue, and it is presumably a deliberate trade-off so that `example.com`, `www.example.com` and `staging.example.com` all work from one key. Worth knowing it is broader than that. Binding to the registrable domain (label + public suffix) rather than any label would keep the convenience without the wildcard.

Related, and inherent rather than a defect: offline licence validation is decidable entirely client-side, so it can be bypassed by editing the plugin. That is true of every offline scheme and is not worth engineering around.

---

### F-15 — Small defects

| Location | Issue |
|---|---|
| `class-appt-booker.php:6457` | `$plain_body` is built (a full plain-text alternative for the daily summary) and then never used — `send_mail()` is called with `$html` only. Either send it as a multipart alternative or delete it. |
| `class-appt-booker.php:6059` | `if ( 'block' === $exception['mode'] \|\| 'block' === $exception['mode'] \|\| 'custom' === $exception['mode'] )` — the first condition is duplicated. Harmless; the corrected two-clause version is at line 6155. |
| `class-appt-booker.php:8430-8437` | `json_decode( wp_unslash( $_POST['locations_payload'] ), true )` produces already-unslashed values, which are then passed to `sanitize_location_rows()` where `wp_unslash()` is applied a second time to every field. A legitimate backslash in an address or description is silently stripped. Skip the second unslash for the JSON path. |
| `class-appt-booker.php:6779` vs `6825` | `get_recent_no_show_count()` lowercases the email before comparing; `email_rate_limit_exceeded()` does not. Harmless under the default `utf8mb4_unicode_ci` collation, but it is a latent inconsistency if the column collation is ever changed. Normalise in one place. |
| `class-appt-booker.php:7825` | `isset( $sortable[ $_GET['orderby'] ] )` — if `orderby` arrives as an array, PHP evaluates this as `false` rather than erroring, so the whitelist holds and there is no injection. Still worth an explicit `is_string()` guard for clarity. |
| `class-appt-booker.php:8273` | `Content-Disposition: ... filename="content-research-{$cr_from}-to-{$cr_to}.zip"` interpolates the date range without validating it as a date. `sanitize_text_field()` collapses newlines so header injection is not possible, but the filename should be validated with the same `preg_match('/^\d{4}-\d{2}-\d{2}$/')` used elsewhere. |

---

## What was checked and found sound

Worth recording explicitly, because these are where booking plugins usually fail:

**SQL injection — none found.** Every one of the ~40 database calls uses `$wpdb->prepare()`. Dynamic `WHERE` clauses are assembled from placeholder fragments with the values passed as bound parameters (`get_admin_bookings()` line 7840-7876, `get_admin_calendar_bookings()` line 7896-7913, `get_existing_bookings_for_day()` line 5787-5802, `daily_cap_reached()` line 5952-5970). `ORDER BY` is the classic weak point and it is handled correctly — `$orderby` is resolved through a whitelist map (line 7816-7825) and `$order` is a ternary yielding only `ASC` or `DESC`. Table names come from `$wpdb->prefix`, never from input. `LIKE` filters are wrapped in `$wpdb->esc_like()`.

**XSS — none found.** I grepped for every `echo` outside an escaping function; the single hit (`$calendar_prev_url` / `$calendar_next_url`, line 12269/12271) is pre-escaped with `esc_url()` at assignment (line 12011-12012). The booking form (line 9969-10054) escapes every attribute and text node. Email HTML escapes every interpolated value; the one place that emits markup, `linkify_text()` (line 3090), calls `esc_html()` on the whole string *first* and only then substitutes anchors built with `esc_url()`/`esc_attr()`/`esc_html()` — the correct order. The front-end JS builds DOM with jQuery `.text()`/`.val()`; the single `.html()` call (line 9466) escapes its input manually first. `format_email_html()` (line 4067) does *not* escape, but it is only reachable via `send_mail( ..., $is_html = false )` and all six call sites pass `true`.

**Access control — sound.** All 7 public AJAX endpoints call `check_ajax_referer()` as their first statement. All admin postbacks are gated on `is_admin() && current_user_can('manage_options')` plus a nonce (line 8134-8142). The two diagnostic shortcodes check `is_user_logged_in() && current_user_can('manage_options')` (lines 860, 875). The dashboard widget and licence screen check capabilities.

**Token security — sound.** Booking summary, ICS, cancel, reschedule and approve tokens are all `wp_hash()` over booking-specific material and compared with `hash_equals()`, so there is no timing oracle and no IDOR — `booking_id` alone gets you a 404. Approve/reject tokens are bound to the action, so an approve link cannot be replayed as a reject. The reschedule token includes date and time, so it self-invalidates on use. State-changing requests require a POST with a fresh nonce on top of the token; the GET endpoints only render a confirmation page.

**Race conditions — handled.** Booking writes take a MySQL advisory lock (`GET_LOCK`, 5s timeout, line 6925) and re-read + re-validate the booking inside the lock before writing (line 3236-3242). `register_shutdown_function()` guarantees release. The reminder sweep uses an atomic claim (`UPDATE ... WHERE reminder_sent_at IS NULL`, line 6535) to avoid double-sends across overlapping cron runs.

**CSV injection — handled.** `csv_safe_value()` (line 8118) prefixes any value starting with `= + - @` or whitespace with an apostrophe, applied to every exported row.

**Mail — sound.** Headers are constructed from site options only, never from request data, so there is no header-injection surface. `send_mail()` scopes its `wp_mail_from` filters with `remove_filter()` immediately after the call rather than leaving them attached, and deliberately avoids touching PHPMailer internals so SMTP plugins keep control of delivery.

---

## Recommended order of work

**Before the next release**
1. F-01 — merge instead of replace in `save_settings` (data loss on a routine action)
2. F-04 — bump `VERSION` to `1.16.5`
3. F-02 — de-duplicate the merged booking list (only if slot capacity > 1 is in use)

**Next**
4. F-03 — replace `wp_date( $f, current_time('timestamp') )` with `current_datetime()`
5. F-06 — memoise `table_exists()` and gate `maybe_upgrade_schema()` on the version option
6. F-13 — admin notice when the licence file is unreadable
7. F-11 — enqueue the Turnstile script from the plugin
8. F-12 — correct the retention comments

**Then**
9. F-05 — transient-cache the available-dates response and clamp the day loop
10. F-07 — validate the peer IP before trusting `CF-Connecting-IP`
11. F-08 — sign the form timestamp and fail closed when it is absent
12. F-10 — refresh the nonce client-side on load
13. F-09 — compute bank holidays and add a filter (before 2029, but it needs to be on the roadmap now)
14. F-15 — the small items

None of F-01 through F-15 requires a database migration or a breaking change to the settings schema, and each is independently deployable.
