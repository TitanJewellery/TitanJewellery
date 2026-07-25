#!/usr/bin/env bash
# Runs the regression suite for the 1.16.6 audit fixes against plugin/tj-appt-booker.
# Requires only a PHP CLI - wp-shim.php stubs the WordPress functions the engine touches.
set -u
cd "$(dirname "$0")"
total_pass=0; total_fail=0; lint_fail=0

echo "=== php -l ==="
for f in ../plugin/tj-appt-booker/tj-appt-booker.php ../plugin/tj-appt-booker/includes/*.php; do
  if out=$(php -l "$f" 2>&1); then printf '  OK    %s\n' "$(basename "$f")"
  else printf '  LINT  %s\n%s\n' "$(basename "$f")" "$out"; lint_fail=1; fi
done

echo
for t in run-tests test-settings test-smoke test-admin test-shared-resource test-shared-e2e; do
  echo "=== $t ==="
  out=$(php "$t.php" 2>&1); echo "$out"
  total_pass=$(( total_pass + $(echo "$out" | grep -c '^PASS') ))
  total_fail=$(( total_fail + $(echo "$out" | grep -c '^FAIL') ))
  echo
done

echo "======================================================"
echo "TOTAL  PASS: $total_pass   FAIL: $total_fail"
[ "$lint_fail" -eq 0 ] && [ "$total_fail" -eq 0 ] && { echo "ALL GREEN"; exit 0; }
echo "FAILURES PRESENT"; exit 1
