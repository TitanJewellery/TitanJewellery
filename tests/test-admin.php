<?php
require __DIR__.'/wp-shim.php';
function tj_appt_licence_booking_form_allowed(){ return true; }
function tj_appt_licence_booking_date_allowed($d){ return true; }
function tj_appt_licence_booking_date_restricted($d){ return false; }
function tj_appt_licence_status(){ return array('licence_type'=>'annual','status'=>'active','expires_on'=>'2027-01-01','serial'=>1,'key_format_version'=>5,'days_left'=>200,'restriction_date'=>'2027-02-01'); }
require __DIR__.'/../plugin/tj-appt-booker/includes/class-appt-booker.php';
$b = appt_booker_final_boot();
$GLOBALS['__is_admin']=true; $GLOBALS['__can']=true;
$pass=0;$fail=0;
function ok($c,$l,$x=''){ global $pass,$fail; if($c){$pass++;echo "PASS  $l\n";}else{$fail++;echo "FAIL  $l  $x\n";} }

// F-15: hostile orderby must not reach the SQL.
foreach(array(
    array('orderby'=>'booking_date; DROP TABLE wp_users--'),
    array('orderby'=>array('a','b')),               // array injection attempt
    array('orderby'=>'customer_email'),             // not in the whitelist
    array('order'=>array('x')),                     // array in order
) as $bad){
    $_GET = $bad;
    try { $rows = (new ReflectionMethod($b,'get_admin_bookings'))->invoke(...[$b]); $threw=false; }
    catch (Throwable $e) { $threw=true; $err=$e->getMessage(); }
    ok(!$threw,'get_admin_bookings survives hostile input '.json_encode($bad), isset($err)?$err:'');
}
$_GET=array();

// Admin page must still render end to end.
ob_start();
try { $b->render_admin_page(); $err=null; } catch (Throwable $e) { $err=$e->getMessage(); }
$html = ob_get_clean();
ok($err===null,'render_admin_page() completes without error', (string)$err);
ok(strpos($html,'Appt-Booker V.1.16.6')!==false,'admin header shows the correct version');
ok(strpos($html,'<?php')===false,'no raw PHP leaked into admin output');
echo "\nbookings tab rendered ".strlen($html)." bytes\n";

// Each tab renders its own form, so check them on their own tabs.
foreach(array('settings'=>'save_settings','style'=>'save_style_settings',
              'locations'=>'save_locations','staff'=>'save_staff','services'=>'save_services') as $tab=>$act){
    $_GET = array('tab'=>$tab);
    ob_start();
    try { $b->render_admin_page(); $e2=null; } catch (Throwable $e) { $e2=$e->getMessage(); }
    $h = ob_get_clean();
    ok($e2===null && strpos($h,'name="rgl_booking_action" value="'.$act.'"')!==false,
       "tab '$tab' renders its $act form", (string)$e2);
    ok(strpos($h,'<?php')===false, "tab '$tab' leaks no raw PHP");
}
$_GET=array();
echo str_repeat('-',60)."\nPASS: $pass   FAIL: $fail\n";
exit($fail?1:0);
