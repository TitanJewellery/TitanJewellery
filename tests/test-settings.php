<?php
require __DIR__.'/wp-shim.php';
require __DIR__.'/../plugin/tj-appt-booker/includes/class-appt-booker.php';
$booker = appt_booker_final_boot();
function call($o,$m,$a=array()){ $r=new ReflectionMethod($o,$m); $r->setAccessible(true); return $r->invokeArgs($o,$a); }
$pass=0;$fail=0;
function ok($c,$l,$x=''){ global $pass,$fail; if($c){$pass++;echo "PASS  $l\n";}else{$fail++;echo "FAIL  $l  $x\n";} }

echo "=== F-01: saving Settings must not wipe Form Style ===\n";
// Operator customises the form style.
$stored = call($booker,'get_settings');
$stored['style_button_background'] = '#c0392b';
$stored['style_button_text']       = 'Book My Consultation';
$stored['style_form_border_color'] = '#00ff99';
$stored['style_typography_font_family'] = 'Georgia, serif';
$stored['form_title'] = 'Old Title';
update_option(Appt_Booker_Final::OPTION_SETTINGS, $stored);

// Operator then saves the General Settings tab (which posts no style_* fields).
$GLOBALS['__is_admin']=true; $GLOBALS['__can']=true;
$_POST = array(
    'rgl_booking_action' => 'save_settings',
    'rgl_booking_nonce'  => 'x',
    'form_title'         => 'New Title',
    'success_message'    => 'Thanks!',
    'booking_prefix'     => 'APPT',
    'show_location_field'=> '1',
);
try { call($booker,'handle_admin_postbacks'); } catch (RuntimeException $e) { /* redirect */ }

$after = call($booker,'get_settings');
ok($after['form_title']==='New Title','the posted general field was saved','got '.$after['form_title']);
ok($after['style_button_background']==='#c0392b','style_button_background survived','got '.$after['style_button_background']);
ok($after['style_button_text']==='Book My Consultation','style_button_text survived','got '.$after['style_button_text']);
ok($after['style_form_border_color']==='#00ff99','style_form_border_color survived','got '.$after['style_form_border_color']);
ok($after['style_typography_font_family']==='Georgia, serif','style_typography_font_family survived','got "'.$after['style_typography_font_family'].'"');

// Every style key must be intact.
$styleKeys = call($booker,'style_setting_keys');
$defaults  = call($booker,'default_settings');
$reverted  = array();
foreach($styleKeys as $k){
    if(isset($stored[$k]) && $after[$k]!==$stored[$k]) $reverted[]=$k;
}
ok(empty($reverted), 'no style key reverted to default ('.count($styleKeys).' checked)', 'reverted: '.implode(', ',array_slice($reverted,0,6)));

echo "\n=== F-08: time-to-submit check fails closed ===\n";
$GLOBALS['__is_admin']=false; $GLOBALS['__can']=false; // not an admin, so anti-abuse applies
$s = call($booker,'get_settings'); $s['security_min_seconds_to_submit']=3; $s['security_throttle_seconds']=0;
update_option(Appt_Booker_Final::OPTION_SETTINGS,$s);
$reason='';

$_POST = array(); // bot omits the field entirely - the old code skipped the check
$r = new ReflectionMethod($booker,'request_is_abusive'); $r->setAccessible(true);
$args=array(&$reason); $blocked = $r->invokeArgs($booker,$args);
ok($blocked===true,'submission with NO timestamp field is blocked (was allowed before)');

$reason=''; $_POST = array('rgl_booking_form_loaded_at'=>'0'); $args=array(&$reason);
ok($r->invokeArgs($booker,$args)===true,'submission with timestamp 0 is blocked');

$reason=''; $_POST = array('rgl_booking_form_loaded_at'=>(string)(time()-600),'rgl_booking_form_sig'=>'forged'); $args=array(&$reason);
ok($r->invokeArgs($booker,$args)===true,'forged signature is blocked');

$reason=''; $ts=time()-600;
$_POST = array('rgl_booking_form_loaded_at'=>(string)$ts,'rgl_booking_form_sig'=>call($booker,'form_timestamp_signature',array($ts)));
$args=array(&$reason);
ok($r->invokeArgs($booker,$args)===false,'genuine signed timestamp, 10 min old, is allowed','reason: '.$reason);

$reason=''; $ts=time()-1;
$_POST = array('rgl_booking_form_loaded_at'=>(string)$ts,'rgl_booking_form_sig'=>call($booker,'form_timestamp_signature',array($ts)));
$args=array(&$reason);
ok($r->invokeArgs($booker,$args)===true,'genuine signature but submitted in 1s is blocked (too fast)');

$reason=''; $_POST = array('rgl_booking_company'=>'spam corp'); $args=array(&$reason);
ok($r->invokeArgs($booker,$args)===true,'honeypot still blocks');

echo "\n".str_repeat('-',60)."\nPASS: $pass   FAIL: $fail\n";
exit($fail?1:0);
