# Review — Chat's reviewed 1.16.7 build

`tjapptbookerv1.16.7reviewed.zip`, checked against my 1.16.7 (commit `42dbbd2`)
and against the original 1.16.5.

## Verdict

**Nearly there. Do not ship it as-is if you use Capacity per Time Slot above 1.**

Chat re-implemented the shared-resource fix a different way. The new approach is
clean, and it does fix the exact case the previous reviewer reported. But it
closes only the *cross-combination* hole. Two *same-combination* overlap cases
remain, and they are the same class of defect: capacity above 1 still outvotes
the single-phone rule.

At Capacity per Time Slot = 1 — Titan's actual 1-to-1 configuration — **Chat's
build is fully correct**. The residual defect is latent unless you use the
group/workshop capacity feature.

| Check | Result |
|---|---|
| All three files lint clean under PHP 8.4 | Pass |
| No method removed vs 1.16.5 | Pass — nothing lost |
| All 1.16.6 audit fixes intact | Pass — 75/78 of my suite, the 3 misses being my own test artefact |
| Version strings consistent (1.16.7 everywhere) | Pass |
| Licence file untouched | Pass — byte-identical |
| READMEs | Byte-identical to mine |
| Reported cross-combination defect | **Fixed** |
| Same-combination overlap at capacity > 1 | **Still open** (2 cases) |

---

## What Chat changed

Engine is 59 lines shorter. Two substantive changes plus documentation.

**1. Different F-16 implementation.** My version added
`booking_is_same_session()` and threaded a `$shared_resource_mode` flag into
`add_slots_from_window()`. Chat removed both and instead tags rows in
`merge_booking_occupancy()`:

```php
// primary rows
$booking['blocks_shared_resource'] = 0;
// shared-only rows
$booking['blocks_shared_resource'] = 1;
```

then in the occupancy loop:

```php
if ( ! empty( $booking['blocks_shared_resource'] ) ) {
    $booked_spaces = $slot_capacity;   // fill the slot entirely
    break;
}
```

This is the "shared bookings must fill the selected slot's entire capacity"
option the previous reviewer suggested. It is a legitimate design and it is
simpler than mine — one flag, no extra parameter.

**2. De-versioned the bootstrap comments.** `tj-appt-booker.php` lines 22 and 75
now read "the current booking engine" rather than naming a version. **This is
better than what I did** and I have adopted it — a comment that names a version
is a comment that will go stale again.

**3. Changelog placement.** The 1.16.7 entry moved to sit after 1.16.6, which
matches the file's existing ascending order better than my placement. Also fine.

---

## The two cases still open

Both need shared-resource mode on *and* Capacity per Time Slot above 1.

### D — the cross-staff buffer is absorbed by spare capacity

Same combination, a call at 10:00–11:00, `shared_resource_buffer` 15 minutes, so
the merged row runs to 11:15. The 11:00 slot falls inside that tail — the line is
not free yet.

Chat's build tags that row `blocks_shared_resource = 0` (it is a primary row), so
it contributes 1 space against a capacity of 6 and **the 11:00 slot is offered
with "5 spaces left"**. The buffer is silently defeated, exactly as it was
before 1.16.6 — just now only within one combination.

### E — staggered overlap double-books the line

`slot_offer_interval` 30 minutes with a 60-minute duration, which the code
explicitly supports ("offer every 30 min even though the slot footprint is 60
min"). A call is booked 10:00–11:00. The 10:30 slot is a **different call** that
would overlap it.

Chat's build offers 10:30 with "5 spaces left". Two overlapping calls, one phone.

Both are the reported bug wearing a different hat: capacity is being allowed to
answer a question it should not have a vote on.

---

## Evidence

I first ran my existing suite against Chat's build and got 3 failures — but two
of those were **my test's fault**, not their code's: the unit test hand-builds
rows tagged `blocks_shared` (my flag name), which their code does not read. That
is not a fair test of a different implementation.

So I wrote `tests/test-shared-neutral.php`, which sets **no flags at all**. It
builds rows exactly as the two real queries produce them, pushes them through
whichever `merge_booking_occupancy()` the build has, then through
`add_slots_from_window()`, adapting to either function signature. It is fair to
both designs.

```
### Chat's reviewed build                 ### My 1.16.7
A foreign booking, capacity 2   PASS      A foreign booking, capacity 2   PASS
B foreign booking, capacity 6   PASS      B foreign booking, capacity 6   PASS
C group session 1 of 6          PASS      C group session 1 of 6          PASS
D cross-staff buffer            FAIL      D cross-staff buffer            PASS
    offered: "11:00 - 5 spaces left"
E staggered overlap             FAIL      E staggered overlap             PASS
    offered: "10:30 - 5 spaces left"
---- 3 passed, 2 failed ----              ---- 5 passed, 0 failed ----
```

Run it yourself against any build:

```
php tests/test-shared-neutral.php /path/to/tj-appt-booker
```

---

## Recommended resolution

Keep Chat's `blocks_shared_resource` tagging — it is the tidier of the two
designs — and add the missing condition. A booking only shares a slot's capacity
if it is genuinely the same call, which means the same combination **and** the
same start time. Anything else that overlaps is a different call.

In `add_slots_from_window()` and the matching loop in
`build_slots_with_status()`:

```php
foreach ( $existing as $booking ) {
    if ( $slot_start < $booking['end'] && $slot_end > $booking['start'] ) {
        // A foreign booking, or an overlapping call in this combination that
        // starts at a different time, occupies the whole line.
        if ( ! empty( $booking['blocks_shared_resource'] )
            || (int) $booking['start'] !== (int) $slot_start ) {
            $booked_spaces = $slot_capacity;
            break;
        }
        $booked_spaces += max( 1, absint( isset( $booking['spaces_booked'] ) ? $booking['spaces_booked'] : 1 ) );
    }
}
```

One added condition in each of two places. It needs the `$shared_resource_mode`
guard only if you want capacity-above-1 behaviour to stay unchanged when
shared-resource mode is off — worth keeping, since with the shared line disabled
there is no reason a staggered session cannot overlap.

That produces 5/5 on the neutral test while keeping Chat's simpler structure.

---

## Still needs a WordPress staging test

The shim suite cannot cover these. Unchanged from my earlier list:

- an ordinary booking end to end, including the confirmation email
- a booking from a page served out of cache (validates the nonce refresh)
- two overlapping bookings across different staff with shared-resource on
- **a capacity-2 service — the case at issue here**
- Turnstile on, with the site key set
- saving Settings, then re-checking Form Style
