<?php
date_default_timezone_set('UTC');
function easter_sunday($year){
    $year=(int)$year;$a=$year%19;$b=intdiv($year,100);$c=$year%100;$d=intdiv($b,4);$e=$b%4;
    $f=intdiv($b+8,25);$g=intdiv($b-$f+1,3);$h=(19*$a+$b-$d-$g+15)%30;$i=intdiv($c,4);$k=$c%4;
    $l=(32+2*$e+2*$i-$h-$k)%7;$m=intdiv($a+11*$h+22*$l,451);
    $month=intdiv($h+$l-7*$m+114,31);$day=(($h+$l-7*$m+114)%31)+1;
    return sprintf('%04d-%02d-%02d',$year,$month,$day);
}
function sub($date,$taken){
    $ts=strtotime($date);
    while((int)gmdate('N',$ts)>=6 || in_array(gmdate('Y-m-d',$ts),$taken,true)){ $ts=strtotime('+1 day',$ts); }
    return gmdate('Y-m-d',$ts);
}
function build($from,$to){
    $dates=array();
    for($year=$from;$year<=$to;$year++){
        $easter=strtotime(easter_sunday($year));
        $dates[]=sub($year.'-01-01',$dates);
        $dates[]=gmdate('Y-m-d',strtotime('-2 days',$easter));
        $dates[]=gmdate('Y-m-d',strtotime('+1 day',$easter));
        $dates[]=gmdate('Y-m-d',strtotime("first monday of may $year"));
        $dates[]=gmdate('Y-m-d',strtotime("last monday of may $year"));
        $dates[]=gmdate('Y-m-d',strtotime("last monday of august $year"));
        $dates[]=sub($year.'-12-25',$dates);
        $dates[]=sub($year.'-12-26',$dates);
    }
    $dates=array_values(array_unique($dates)); sort($dates); return $dates;
}
$original = array(
 '2025-01-01','2025-04-18','2025-04-21','2025-05-05','2025-05-26','2025-08-25','2025-12-25','2025-12-26',
 '2026-01-01','2026-04-03','2026-04-06','2026-05-04','2026-05-25','2026-08-31','2026-12-25','2026-12-28',
 '2027-01-01','2027-03-26','2027-03-29','2027-05-03','2027-05-31','2027-08-30','2027-12-27','2027-12-28',
 '2028-01-03','2028-04-14','2028-04-17','2028-05-01','2028-05-29','2028-08-28','2028-12-25','2028-12-26',
 '2029-01-01','2029-03-30','2029-04-02','2029-05-07','2029-05-28','2029-08-27','2029-12-25','2029-12-26',
);
sort($original);
$computed = build(2025,2029);
echo "original count: ".count($original)."  computed count: ".count($computed)."\n";
$missing = array_diff($original,$computed);
$extra   = array_diff($computed,$original);
echo "in original but NOT computed: ".($missing?implode(', ',$missing):'(none)')."\n";
echo "computed but NOT in original: ".($extra?implode(', ',$extra):'(none)')."\n";
echo ($original===$computed) ? "EXACT MATCH 2025-2029\n" : "MISMATCH\n";
echo "\n--- forward projection, 2030-2033 (previously unblocked) ---\n";
foreach(build(2030,2033) as $d){ echo "  $d  ".gmdate('D',strtotime($d))."\n"; }
