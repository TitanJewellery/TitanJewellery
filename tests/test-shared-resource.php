<?php
/*
 * Shared-resource single-phone rule.
 *
 * The engine models one shared phone line: at most one call at a time across
 * every service, location and staff member. Capacity per Time Slot exists for
 * group sessions - several people joining THE SAME call. The two must not be
 * confused: a booking on a different service/staff is a DIFFERENT call and must
 * close the slot outright, no matter how much capacity the slot has spare.
 */
require __DIR__.'/wp-shim.php';
function tj_appt_licence_booking_form_allowed(){ return true; }
function tj_appt_licence_booking_date_allowed($d){ return true; }
function tj_appt_licence_booking_date_restricted($d){ return false; }
require __DIR__.'/../plugin/tj-appt-booker/includes/class-appt-booker.php';
$b = appt_booker_final_boot();
function call($o,$m,$a=array()){ $r=new ReflectionMethod($o,$m); $r->setAccessible(true); return $r->invokeArgs($o,$a); }
$pass=0;$fail=0;
function ok($c,$l,$x=''){ global $pass,$fail; if($c){$pass++;echo "PASS  $l\n";}else{$fail++;echo "FAIL  $l  $x\n";} }

// Helper: run the slot-occupancy decision for a 10:00-11:00 slot (600-660).
function slotsFor($b,$existing,$capacity,$sharedMode){
    $slots=array();
    $args=array(&$slots,'2026-08-05',600,660,60,60,$existing,0,array(),$capacity,$sharedMode);
    $r=new ReflectionMethod($b,'add_slots_from_window'); $r->setAccessible(true);
    try { $r->invokeArgs($b,$args); }
    catch (ArgumentCountError $e) {          // pre-fix signature has no shared flag
        $args=array(&$slots,'2026-08-05',600,660,60,60,$existing,0,array(),$capacity);
        $r->invokeArgs($b,$args);
    }
    return $slots;
}

echo "=== the reported defect: foreign booking vs capacity 2 ===\n";
// Service B booked at 10:00 with a different staff member. Seen only by the
// shared-resource query, so spaces_booked is 1 and blocks_shared is set.
$foreign = array(array('id'=>42,'start'=>600,'end'=>660,'spaces_booked'=>1,'blocks_shared_resource'=>true));
$slots = slotsFor($b,$foreign,2,true);
ok(count($slots)===0,
   'capacity-2 Service A slot is CLOSED while the phone is on a Service B call',
   'slot was still offered: '.json_encode($slots));

$slots = slotsFor($b,$foreign,1,true);
ok(count($slots)===0,'capacity-1 slot is closed too (was already correct)');

$slots = slotsFor($b,$foreign,6,true);
ok(count($slots)===0,'capacity-6 slot is closed - capacity never outvotes the shared phone');

echo "\n=== group sessions must still work ===\n";
// Same combination, same start: these people are joining the SAME call.
$sameSession = array(array('id'=>7,'start'=>600,'end'=>660,'spaces_booked'=>2,'blocks_shared_resource'=>false));
$slots = slotsFor($b,$sameSession,6,true);
ok(count($slots)===1,'capacity-6 workshop with 2 booked is still offered');
ok(!empty($slots) && $slots[0]['spaces_remaining']===4,'4 spaces remaining',
   'got '.(!empty($slots)?$slots[0]['spaces_remaining']:'none'));

$full = array(array('id'=>7,'start'=>600,'end'=>660,'spaces_booked'=>6,'blocks_shared_resource'=>false));
ok(count(slotsFor($b,$full,6,true))===0,'a full workshop closes the slot');

echo "\n=== cross-staff buffer still enforced ===\n";
// Same combination but an EARLIER call, end padded by the 15-min buffer to 675.
// Slot 11:00 (660-720) overlaps that padding, and it is a different call.
$earlier = array(array('id'=>7,'start'=>600,'end'=>675,'spaces_booked'=>1,'blocks_shared_resource'=>false));
$slots=array();
$args=array(&$slots,'2026-08-05',660,720,60,60,$earlier,0,array(),6,true);
$r=new ReflectionMethod($b,'add_slots_from_window'); $r->setAccessible(true);
try { $r->invokeArgs($b,$args); } catch (ArgumentCountError $e) {
    $args=array(&$slots,'2026-08-05',660,720,60,60,$earlier,0,array(),6); $r->invokeArgs($b,$args); }
ok(count($slots)===0,'11:00 slot closed by the buffer tail of the 10:00 call, despite capacity 6');

echo "\n=== shared resource OFF: capacity behaves as before ===\n";
$other = array(array('id'=>42,'start'=>600,'end'=>660,'spaces_booked'=>1,'blocks_shared_resource'=>false));
$slots = slotsFor($b,$other,2,false);
ok(count($slots)===1,'with shared mode off, a capacity-2 slot with 1 booked stays open');
ok(!empty($slots) && $slots[0]['spaces_remaining']===1,'1 space remaining',
   'got '.(!empty($slots)?$slots[0]['spaces_remaining']:'none'));

echo "\n".str_repeat('-',60)."\nPASS: $pass   FAIL: $fail\n";
exit($fail?1:0);
