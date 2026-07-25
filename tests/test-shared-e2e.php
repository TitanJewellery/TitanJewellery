<?php
/*
 * End-to-end: the reviewer's exact scenario, driven through build_slots().
 *
 * Shared resource ON. Service A has Capacity per Time Slot = 2. Someone has
 * already booked Service B with a different team member at 10:00. The 10:00
 * Service A slot must NOT be offered - there is only one phone.
 */
require __DIR__.'/wp-shim.php';
function tj_appt_licence_booking_form_allowed(){ return true; }
function tj_appt_licence_booking_date_allowed($d){ return true; }
function tj_appt_licence_booking_date_restricted($d){ return false; }

// A wpdb that answers the two booking queries with canned rows.
class ScenarioWPDB extends FakeWPDB {
    public $foreign = array();   // bookings on other services/staff
    public $own     = array();   // bookings on the combination under test
    public function get_results($q,$m=null){
        $this->queries++;
        if (stripos($q,'FROM wp_rgl_booking_final') === false) return array();
        // get_existing_bookings_for_day() filters by location+service+staff.
        if (stripos($q,'location_id =') !== false) return $this->own;
        // get_all_bookings_for_day() is date-only.
        if (stripos($q,'booking_date =') !== false) return array_merge($this->own,$this->foreign);
        return array();
    }
}
$GLOBALS['wpdb'] = new ScenarioWPDB();
require __DIR__.'/../plugin/tj-appt-booker/includes/class-appt-booker.php';
$b = appt_booker_final_boot();
function call($o,$m,$a=array()){ $r=new ReflectionMethod($o,$m); $r->setAccessible(true); return $r->invokeArgs($o,$a); }
$pass=0;$fail=0;
function ok($c,$l,$x=''){ global $pass,$fail; if($c){$pass++;echo "PASS  $l\n";}else{$fail++;echo "FAIL  $l  $x\n";} }

// Pick a date inside the bookable window that is a working day.
$date = null;
for($i=1;$i<=30;$i++){
    $d = gmdate('Y-m-d', strtotime("+$i days"));
    if (call($b,'date_is_globally_offerable',array($d))) { $date = $d; break; }
}
if(!$date){ echo "could not find a bookable date\n"; exit(1); }
echo "using bookable date: $date\n\n";

$hours = array('mon'=>array('enabled'=>1,'start'=>'09:00','end'=>'17:00'));
foreach(array('tue','wed','thu','fri','sat','sun') as $d2) $hours[$d2]=array('enabled'=>1,'start'=>'09:00','end'=>'17:00');

$location = array('id'=>'phone','name'=>'Phone','active'=>1,'location_ids'=>array('phone'));
$serviceA = array('id'=>'svc_a','name'=>'Service A','active'=>1,'duration'=>60,'buffer'=>0,
    'location_ids'=>array('phone'),'staff_ids'=>array('stf_a'),'hours'=>$hours,
    'staff_rules'=>array('stf_a'=>array('duration'=>'','buffer'=>'','min_notice_hours'=>'','max_days_ahead'=>'','slot_capacity'=>2)));
$staffA   = array('id'=>'stf_a','name'=>'Alice','active'=>1,'location_ids'=>array('phone'),'hours'=>$hours);

// Register the fixture in the stored options: get_all_location_ids() and the
// service/staff eligibility checks read from there, not from the arrays passed in.
update_option(Appt_Booker_Final::OPTION_LOCATIONS, array($location));
update_option(Appt_Booker_Final::OPTION_STAFF,     array($staffA));
update_option(Appt_Booker_Final::OPTION_SERVICES,  array($serviceA));

$settings = call($b,'get_settings');
$settings['shared_resource_enabled']=1; $settings['shared_resource_buffer']=0; $settings['daily_booking_cap']=0;
update_option(Appt_Booker_Final::OPTION_SETTINGS,$settings);

function slotValues($b,$service,$staff,$date,$location){
    $s = call($b,'build_slots',array($service,$staff,$date,0,$location));
    return array_map(function($x){ return $x['value']; }, $s);
}

echo "=== baseline: nothing booked ===\n";
$GLOBALS['wpdb']->own=array(); $GLOBALS['wpdb']->foreign=array();
$v = slotValues($b,$serviceA,$staffA,$date,$location);
ok(in_array('10:00',$v,true),'10:00 offered when the line is free','got '.implode(',',$v));

echo "\n=== the reported defect ===\n";
// Service B, different staff, 10:00-11:00. Only the date-wide query sees it.
$GLOBALS['wpdb']->own = array();
$GLOBALS['wpdb']->foreign = array(array('id'=>'42','booking_time'=>'10:00:00','booking_end_time'=>'11:00:00','spaces_booked'=>'1'));
$v = slotValues($b,$serviceA,$staffA,$date,$location);
ok(!in_array('10:00',$v,true),
   'capacity-2 Service A no longer offers 10:00 while Service B holds the phone',
   'STILL OFFERED - slots: '.implode(',',$v));
ok(in_array('11:00',$v,true),'11:00 is still offered (line free again)','got '.implode(',',$v));

echo "\n=== group booking on the same session still sells its spare seat ===\n";
$GLOBALS['wpdb']->own = array(array('id'=>'7','booking_time'=>'10:00:00','booking_end_time'=>'11:00:00','spaces_booked'=>'1'));
$GLOBALS['wpdb']->foreign = array();
$s = call($b,'build_slots',array($serviceA,$staffA,$date,0,$location));
$ten = null; foreach($s as $x){ if($x['value']==='10:00') $ten=$x; }
ok($ten !== null,'10:00 still offered for the same capacity-2 session');
ok($ten && $ten['spaces_remaining']===1,'1 space remaining on that session',
   'got '.($ten?$ten['spaces_remaining']:'none'));

echo "\n=== that session filling up closes it ===\n";
$GLOBALS['wpdb']->own = array(array('id'=>'7','booking_time'=>'10:00:00','booking_end_time'=>'11:00:00','spaces_booked'=>'2'));
$v = slotValues($b,$serviceA,$staffA,$date,$location);
ok(!in_array('10:00',$v,true),'10:00 closed once both seats are taken','got '.implode(',',$v));

echo "\n=== shared resource OFF: foreign booking is irrelevant again ===\n";
$settings['shared_resource_enabled']=0;
update_option(Appt_Booker_Final::OPTION_SETTINGS,$settings);
$GLOBALS['wpdb']->own=array();
$GLOBALS['wpdb']->foreign=array(array('id'=>'42','booking_time'=>'10:00:00','booking_end_time'=>'11:00:00','spaces_booked'=>'1'));
$v = slotValues($b,$serviceA,$staffA,$date,$location);
ok(in_array('10:00',$v,true),'with shared mode off, another service does not block 10:00','got '.implode(',',$v));

echo "\n".str_repeat('-',60)."\nPASS: $pass   FAIL: $fail\n";
exit($fail?1:0);
