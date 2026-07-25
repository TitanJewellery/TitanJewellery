<?php
require __DIR__.'/wp-shim.php';
// Licensed site: the engine asks these before rendering anything.
function tj_appt_licence_booking_form_allowed(){ return true; }
function tj_appt_licence_booking_date_allowed($d){ return true; }
function tj_appt_licence_booking_date_restricted($d){ return false; }
function tj_appt_licence_public_notice(){ return '<div>unlicensed</div>'; }
function tj_appt_licence_email_message($m,$h=true,$a='internal'){ return $m; }
require __DIR__.'/../plugin/tj-appt-booker/includes/class-appt-booker.php';
$b = appt_booker_final_boot();
$html = $b->render_shortcode(array());
$checks = array(
  'form element'            => strpos($html,'<form class="rgl-booking-form')!==false,
  'honeypot field'          => strpos($html,'rgl_booking_company')!==false,
  'timestamp field'         => strpos($html,'rgl_booking_form_loaded_at')!==false,
  'NEW signature field'     => strpos($html,'rgl_booking_form_sig')!==false,
  'signature has a value'   => (bool)preg_match('/name="rgl_booking_form_sig"[^>]*value="[a-f0-9]{16,}"/',$html),
  'no raw PHP leaked'       => strpos($html,'<?php')===false,
);
$fail=0;
foreach($checks as $k=>$v){ printf("%s  %s\n", $v?'PASS':'FAIL', $k); if(!$v)$fail++; }
echo "\nrendered ".strlen($html)." bytes of markup\n";
exit($fail?1:0);
