<?php
require __DIR__.'/wp-shim.php';
require __DIR__.'/../plugin/tj-appt-booker/includes/class-appt-booker.php';

$booker = appt_booker_final_boot();
$r = new ReflectionClass($booker);
function call($obj,$m,$args=array()){
    $rm = new ReflectionMethod($obj,$m); $rm->setAccessible(true);
    return $rm->invokeArgs($obj,$args);
}
function prop($obj,$p){ $rp=new ReflectionProperty($obj,$p); $rp->setAccessible(true); return $rp->getValue($obj); }

$pass=0;$fail=0;
function ok($cond,$label,$extra=''){
    global $pass,$fail;
    if($cond){$pass++;echo "PASS  $label\n";}
    else{$fail++;echo "FAIL  $label  $extra\n";}
}

echo "=== F-02: merge_booking_occupancy (capacity double-count) ===\n";
// One booking id=7, 3 spaces, 10:00-11:00 (600-660) in the per-combination list.
$primary = array(array('id'=>7,'start'=>600,'end'=>660,'spaces_booked'=>3));
// Same booking seen by the shared-resource query: 1 space, end padded by 15m buffer.
$shared  = array(
    array('id'=>7,'start'=>600,'end'=>675,'spaces_booked'=>1),
    array('id'=>9,'start'=>780,'end'=>855,'spaces_booked'=>1), // someone else's booking
);
$merged = call($booker,'merge_booking_occupancy',array($primary,$shared));
ok(count($merged)===2,'duplicate collapsed to one row (+1 unrelated)','got '.count($merged));
$row7 = null; foreach($merged as $m){ if($m['id']===7) $row7=$m; }
ok($row7 && $row7['spaces_booked']===3,'true quantity (3) preserved, not overwritten by shared list 1',
   'got '.($row7?$row7['spaces_booked']:'null'));
ok($row7 && $row7['end']===675,'cross-staff buffer padding (end 675) preserved',
   'got '.($row7?$row7['end']:'null'));

// Occupancy sum at the 10:00 slot must be 3, not 4.
$occ=0; foreach($merged as $b){ if(600 < $b['end'] && 660 > $b['start']) $occ += $b['spaces_booked']; }
ok($occ===3,'occupancy at 10:00 sums to 3 (was 4 before the fix)','got '.$occ);
ok(max(0,6-$occ)===3,'capacity-6 workshop shows 3 remaining (was 2)','got '.max(0,6-$occ));

echo "\n=== F-09: bank holidays computed, not hardcoded ===\n";
$bh = call($booker,'get_uk_bank_holidays');
$thisYear = (int)current_datetime()->format('Y');
// The window rolls with the current year, so assert coverage relative to "now"
// rather than against fixed dates. The old hardcoded table could not do this.
$horizon = $thisYear + 3;
ok(in_array($horizon.'-12-25',$bh,true) || in_array($horizon.'-12-27',$bh,true),
   "Christmas $horizon is covered (3 years out, rolling)");
ok(!call($booker,'is_working_day',array($horizon.'-12-25')),"$horizon Christmas Day is not a working day");
$easter = call($booker,'easter_sunday',array($horizon));
$goodFri = gmdate('Y-m-d', strtotime('-2 days', strtotime($easter)));
$maundy  = gmdate('Y-m-d', strtotime('-3 days', strtotime($easter)));
ok(!call($booker,'is_working_day',array($goodFri)),"$horizon Good Friday ($goodFri) is not a working day");
ok(call($booker,'is_working_day',array($maundy)),"$horizon Maundy Thursday ($maundy) IS a working day");
ok(!call($booker,'is_working_day',array('2026-01-03')),'a Saturday is not a working day');
// Regression guard for the original defect: the horizon must always sit at
// least two years in the future, whatever year the code runs in.
$maxYear = 0; foreach($bh as $d){ $maxYear = max($maxYear,(int)substr($d,0,4)); }
ok($maxYear >= $thisYear + 2, "holiday horizon ($maxYear) is >= 2 years ahead of $thisYear");

echo "\n=== F-08: signed form timestamp ===\n";
$creds = call($booker,'get_public_form_credentials');
ok(!empty($creds['nonce']) && !empty($creds['signature']) && $creds['loadedAt']>0,'credentials issued');
$sig = call($booker,'form_timestamp_signature',array($creds['loadedAt']));
ok(hash_equals($sig,$creds['signature']),'signature verifies for its own timestamp');
$bad = call($booker,'form_timestamp_signature',array($creds['loadedAt']-60));
ok(!hash_equals($bad,$creds['signature']),'signature does NOT verify for a shifted timestamp (bot cannot forge)');

echo "\n=== F-07: Cloudflare header only trusted from Cloudflare ===\n";
$_SERVER['REMOTE_ADDR']='203.0.113.9';                 // not Cloudflare
$_SERVER['HTTP_CF_CONNECTING_IP']='1.2.3.4';           // spoofed
ok(call($booker,'get_client_ip_for_throttle')==='203.0.113.9','spoofed CF header ignored from non-CF peer');
$_SERVER['REMOTE_ADDR']='162.158.1.1';                 // genuine Cloudflare edge
ok(call($booker,'get_client_ip_for_throttle')==='1.2.3.4','CF header honoured from genuine CF peer');
$_SERVER['REMOTE_ADDR']='2606:4700::1';                // genuine CF IPv6
ok(call($booker,'get_client_ip_for_throttle')==='1.2.3.4','CF header honoured over IPv6 edge');
unset($_SERVER['HTTP_CF_CONNECTING_IP']);
$_SERVER['REMOTE_ADDR']='198.51.100.7';
ok(call($booker,'get_client_ip_for_throttle')==='198.51.100.7','falls back to REMOTE_ADDR');

echo "\n=== F-06: table_exists memoised ===\n";
$before = $GLOBALS['wpdb']->queries;
for($i=0;$i<25;$i++){ call($booker,'table_exists'); }
$delta = $GLOBALS['wpdb']->queries - $before;
ok($delta===0,'25 further table_exists() calls issue 0 queries','issued '.$delta);

echo "\n=== F-03: no double timezone offset ===\n";
$rm=new ReflectionMethod($booker,'earliest_bookable_date'); $rm->setAccessible(true);
$earliest = $rm->invoke($booker);
$rm2=new ReflectionMethod($booker,'latest_bookable_date'); $rm2->setAccessible(true);
$latest = $rm2->invoke($booker);
ok(preg_match('/^\d{4}-\d{2}-\d{2}$/',$earliest)===1,'earliest_bookable_date returns a date: '.$earliest);
ok(preg_match('/^\d{4}-\d{2}-\d{2}$/',$latest)===1,'latest_bookable_date returns a date: '.$latest);
ok($earliest <= $latest,'window is coherent (earliest <= latest)',"$earliest > $latest");
$site_hour = (int)current_datetime()->format('G');
$naive_hour = (int)wp_date('G', current_time('timestamp'));
echo "      site wall-clock hour = $site_hour ; old (double-offset) reading = $naive_hour\n";

echo "\n".str_repeat('-',60)."\n";
echo "PASS: $pass   FAIL: $fail\n";
exit($fail?1:0);
