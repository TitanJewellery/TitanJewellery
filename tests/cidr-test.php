<?php
function ip_in_cidr($ip,$cidr){
    if(false===strpos($cidr,'/')) return $ip===$cidr;
    list($subnet,$bits)=explode('/',$cidr,2); $bits=(int)$bits;
    $ipp=@inet_pton($ip); $sp=@inet_pton($subnet);
    if(false===$ipp||false===$sp) return false;
    if(strlen($ipp)!==strlen($sp)) return false;
    if($bits<0||$bits>strlen($ipp)*8) return false;
    $whole=intdiv($bits,8); $rest=$bits%8;
    if($whole>0 && 0!==substr_compare($ipp,substr($sp,0,$whole),0,$whole)) return false;
    if(0===$rest) return true;
    $mask=~((1<<(8-$rest))-1)&0xFF;
    return (ord($ipp[$whole])&$mask)===(ord($sp[$whole])&$mask);
}
$cases=array(
 array('173.245.48.1','173.245.48.0/20',true,'CF v4 first'),
 array('173.245.63.255','173.245.48.0/20',true,'CF v4 last'),
 array('173.245.64.0','173.245.48.0/20',false,'just past CF v4 range'),
 array('173.245.47.255','173.245.48.0/20',false,'just before CF v4 range'),
 array('103.21.244.10','103.21.244.0/22',true,'CF /22'),
 array('103.21.248.1','103.21.244.0/22',false,'past /22'),
 array('131.0.75.1','131.0.72.0/22',true,'CF /22 upper'),
 array('8.8.8.8','104.16.0.0/13',false,'google dns not CF'),
 array('104.16.0.1','104.16.0.0/13',true,'CF /13'),
 array('104.24.1.1','104.24.0.0/14',true,'CF /14'),
 array('2606:4700::1111','2606:4700::/32',true,'CF v6'),
 array('2001:4860:4860::8888','2606:4700::/32',false,'google v6 not CF'),
 array('2606:4700::1111','104.16.0.0/13',false,'v6 vs v4 family mismatch'),
 array('104.16.0.1','2606:4700::/32',false,'v4 vs v6 family mismatch'),
 array('192.168.1.1','162.158.0.0/15',false,'private not CF'),
 array('162.159.0.1','162.158.0.0/15',true,'CF /15 second half'),
 array('garbage','104.16.0.0/13',false,'invalid ip'),
);
$fail=0;
foreach($cases as $c){
  list($ip,$cidr,$want,$label)=$c;
  $got=ip_in_cidr($ip,$cidr);
  $ok=($got===$want);
  if(!$ok)$fail++;
  printf("%s  %-22s in %-20s => %-5s (want %-5s)  %s\n",$ok?'PASS':'FAIL',$ip,$cidr,var_export($got,true),var_export($want,true),$label);
}
echo $fail? "\n$fail FAILED\n" : "\nAll ".count($cases)." CIDR cases pass\n";
