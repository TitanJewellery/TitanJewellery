<?php
/*
 * Implementation-neutral shared-resource test.
 *
 * Builds rows exactly as the two real queries do, pushes them through whichever
 * merge_booking_occupancy() the build under test has, and then through
 * add_slots_from_window(). No hand-set flags, so it is fair to either design.
 */
$target = isset($argv[1]) ? $argv[1] : __DIR__.'/../plugin/tj-appt-booker';
require __DIR__.'/wp-shim.php';
function tj_appt_licence_booking_form_allowed(){ return true; }
function tj_appt_licence_booking_date_allowed($d){ return true; }
function tj_appt_licence_booking_date_restricted($d){ return false; }
require $target.'/includes/class-appt-booker.php';
$b = appt_booker_final_boot();
function call($o,$m,$a=array()){ $r=new ReflectionMethod($o,$m); $r->setAccessible(true); return $r->invokeArgs($o,$a); }

// Row shapes exactly as produced by the two queries.
function primaryRow($id,$start,$end,$spaces){ return array('id'=>$id,'start'=>$start,'end'=>$end,'spaces_booked'=>$spaces); }
function sharedRow($id,$start,$end,$buffer){ return array('id'=>$id,'start'=>$start,'end'=>$end+$buffer,'spaces_booked'=>1); }

function slotOffered($b,$primary,$shared,$slotStart,$capacity){
    $existing = call($b,'merge_booking_occupancy',array($primary,$shared));
    $slots=array();
    $rm=new ReflectionMethod($b,'add_slots_from_window'); $rm->setAccessible(true);
    $n = $rm->getNumberOfParameters();
    $args=array(&$slots,'2026-08-05',$slotStart,$slotStart+60,60,60,$existing,0,array(),$capacity);
    if($n>=11){ $args[]=true; }          // build with the shared_resource_mode parameter
    $rm->invokeArgs($b,$args);
    return count($slots)>0 ? $slots[0] : null;
}

$pass=0;$fail=0;
function ok($c,$l,$x=''){ global $pass,$fail; if($c){$pass++;echo "PASS  $l\n";}else{$fail++;echo "FAIL  $l  $x\n";} }

echo "### ".basename(dirname($target))."/".basename($target)."  (VERSION ".Appt_Booker_Final::VERSION.")\n";

echo "\n A. foreign booking (other service/staff) at 10:00, target capacity 2\n";
$s = slotOffered($b, array(), array(sharedRow(42,600,660,0)), 600, 2);
ok($s===null,'10:00 closed', $s?('offered: '.$s['label']):'');

echo "\n B. foreign booking at 10:00, target capacity 6\n";
$s = slotOffered($b, array(), array(sharedRow(42,600,660,0)), 600, 6);
ok($s===null,'10:00 closed', $s?('offered: '.$s['label']):'');

echo "\n C. same-combination group session, 1 of 6 taken -> must stay open\n";
$s = slotOffered($b, array(primaryRow(7,600,660,1)), array(sharedRow(7,600,660,0)), 600, 6);
ok($s!==null && $s['spaces_remaining']===5,'10:00 open with 5 left',
   $s?('remaining '.$s['spaces_remaining']):'closed');

echo "\n D. cross-staff buffer: same combination 10:00-11:00, 15m buffer, capacity 6\n";
echo "    (11:00 falls inside the 11:15 buffer tail - the line is not free yet)\n";
$s = slotOffered($b, array(primaryRow(7,600,660,1)), array(sharedRow(7,600,660,15)), 660, 6);
ok($s===null,'11:00 closed by the buffer', $s?('offered: '.$s['label']):'');

echo "\n E. staggered overlap: 30-min offer interval, capacity 6\n";
echo "    10:00-11:00 booked; 10:30 is a DIFFERENT call that would overlap it\n";
$existing = call($b,'merge_booking_occupancy',array(array(primaryRow(7,600,660,1)),array(sharedRow(7,600,660,0))));
$slots=array();
$rm=new ReflectionMethod($b,'add_slots_from_window'); $rm->setAccessible(true);
$args=array(&$slots,'2026-08-05',630,690,60,60,$existing,0,array(),6);
if($rm->getNumberOfParameters()>=11){ $args[]=true; }
$rm->invokeArgs($b,$args);
ok(count($slots)===0,'10:30 closed (would double-book the line)',
   count($slots)?('offered: '.$slots[0]['label']):'');

echo "\n  ---- $pass passed, $fail failed ----\n\n";
exit($fail?1:0);
