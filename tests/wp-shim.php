<?php
// Minimal WordPress shim: enough to load the engine class and poke at pure logic.
define('ABSPATH', __DIR__ . '/');
define('DAY_IN_SECONDS', 86400);
define('HOUR_IN_SECONDS', 3600);
define('MINUTE_IN_SECONDS', 60);
define('WEEK_IN_SECONDS', 604800);
define('ARRAY_A', 'ARRAY_A'); define('ARRAY_N', 'ARRAY_N'); define('OBJECT', 'OBJECT');
define('WP_TZ', 'Europe/London');
date_default_timezone_set('UTC');

$GLOBALS['__opts'] = array();
function get_option($k,$d=false){ return array_key_exists($k,$GLOBALS['__opts'])?$GLOBALS['__opts'][$k]:$d; }
function update_option($k,$v,$a=null){ $GLOBALS['__opts'][$k]=$v; return true; }
function delete_option($k){ unset($GLOBALS['__opts'][$k]); return true; }
function get_transient($k){ return false; }
function set_transient($k,$v,$t=0){ return true; }
function delete_transient($k){ return true; }
function add_action(){} function add_filter(){} function remove_filter(){} function add_shortcode(){}
function shortcode_atts($pairs,$atts,$sc=''){ $atts=(array)$atts; $out=array();
    foreach($pairs as $n=>$d){ $out[$n]=array_key_exists($n,$atts)?$atts[$n]:$d; } return $out; }
function get_the_ID(){ return 0; }
function wp_list_pluck($list,$field,$index=null){ $out=array();
    foreach((array)$list as $k=>$v){ $val = is_object($v)?(isset($v->$field)?$v->$field:null):(isset($v[$field])?$v[$field]:null);
        if(null===$index){ $out[]=$val; } else { $ik = is_object($v)?$v->$index:$v[$index]; $out[$ik]=$val; } }
    return $out; }
function maybe_unserialize($v){ return is_string($v)? (@unserialize($v) ?: $v) : $v; }
function wp_cache_get(){ return false; } function wp_cache_set(){ return true; }
function number_format_i18n($n,$d=0){ return number_format((float)$n,$d); }
function size_format($b,$d=0){ return $b.'B'; }
function human_time_diff($f,$t=null){ return '1 hour'; }
function wp_dropdown_categories(){} function get_edit_post_link(){ return ''; }
function wp_get_attachment_image_src(){ return false; }
function wp_get_attachment_url(){ return ''; }
function get_admin_page_title(){ return 'Appt Booker'; }
function checked_shim(){}
function get_term(){ return null; } function get_terms(){ return array(); }
function wp_get_object_terms(){ return array(); } function taxonomy_exists(){ return false; }
function post_type_exists(){ return false; }
function apply_filters($tag,$val){ return $val; }
function do_action(){}
function wp_next_scheduled(){ return time()+3600; }
function wp_schedule_event(){} function wp_clear_scheduled_hook(){}
function is_admin(){ return !empty($GLOBALS['__is_admin']); }
function wp_doing_ajax(){ return false; }
function is_user_logged_in(){ return !empty($GLOBALS['__can']); }
function current_user_can(){ return !empty($GLOBALS['__can']); }
function wp_timezone(){ return new DateTimeZone(WP_TZ); }
function current_datetime(){ return new DateTimeImmutable('now', wp_timezone()); }
function current_time($type,$gmt=0){
    if($type==='timestamp'||$type==='U'){
        $off = (new DateTime('now', wp_timezone()))->getOffset();
        return $gmt ? time() : time() + $off;
    }
    if($type==='mysql') return (new DateTime('now', wp_timezone()))->format('Y-m-d H:i:s');
    return (new DateTime('now', wp_timezone()))->format($type);
}
function wp_date($f,$ts=null,$tz=null){
    $ts = (null===$ts)? time() : $ts;
    $d = new DateTimeImmutable('@'.$ts);
    return $d->setTimezone($tz ?: wp_timezone())->format($f);
}
function wp_hash($data,$scheme='auth'){ return hash_hmac('md5',$data,'test-salt-'.$scheme); }
function hash_equals_shim($a,$b){ return hash_equals($a,$b); }
function sanitize_text_field($s){ if(is_array($s))return ''; $s=strip_tags((string)$s); return trim(preg_replace('/[\r\n\t ]+/',' ',$s)); }
function sanitize_textarea_field($s){ return is_array($s)?'':trim(strip_tags((string)$s)); }
function sanitize_key($s){ return is_array($s)?'':strtolower(preg_replace('/[^a-z0-9_\-]/i','',(string)$s)); }
function sanitize_email($s){ return is_array($s)?'':(string)filter_var((string)$s, FILTER_SANITIZE_EMAIL); }
function is_email($s){ return (bool)filter_var((string)$s, FILTER_VALIDATE_EMAIL); }
function sanitize_file_name($s){ return preg_replace('/[^A-Za-z0-9._-]/','-',(string)$s); }
function esc_html($s){ return htmlspecialchars((string)$s, ENT_QUOTES); }
function esc_attr($s){ return htmlspecialchars((string)$s, ENT_QUOTES); }
function esc_url($s){ return (string)$s; } function esc_url_raw($s){ return (string)$s; }
function esc_textarea($s){ return htmlspecialchars((string)$s, ENT_QUOTES); }
function esc_js($s){ return (string)$s; }
function wp_unslash($v){ return is_array($v)?array_map('wp_unslash',$v):stripslashes((string)$v); }
function wp_slash($v){ return is_array($v)?array_map('wp_slash',$v):addslashes((string)$v); }
function wp_strip_all_tags($s){ return strip_tags((string)$s); }
function wp_parse_args($a,$d){ return array_merge($d,(array)$a); }
function absint($v){ return abs((int)$v); }
function home_url($p=''){ return 'https://example.test'.$p; }
function admin_url($p=''){ return 'https://example.test/wp-admin/'.$p; }
function get_bloginfo($x){ return 'Test Site'; }
function wp_parse_url($u,$c=-1){ return parse_url($u,$c); }
function wp_generate_password($l=12,$s=true,$e=false){ return substr(str_shuffle(str_repeat('ABCDEFGHJKMNPQRSTUVWXYZ23456789',4)),0,$l); }
function wp_specialchars_decode($s,$q=null){ return htmlspecialchars_decode((string)$s,ENT_QUOTES); }
function wp_json_encode($v){ return json_encode($v); }
function wp_mail(){ return true; }
function wp_send_json_success($d=null){ throw new RuntimeException('json_success:'.json_encode($d)); }
function wp_send_json_error($d=null){ throw new RuntimeException('json_error:'.json_encode($d)); }
function check_ajax_referer(){ return true; }
function wp_verify_nonce(){ return 1; }
function wp_create_nonce($a='') { return 'nonce-'.md5((string)$a); }
function wp_nonce_field(){} function submit_button(){}
function register_activation_hook(){} function register_deactivation_hook(){}
function wp_register_style(){} function wp_enqueue_style(){} function wp_add_inline_style(){}
function wp_register_script(){} function wp_enqueue_script(){} function wp_add_inline_script(){} function wp_localize_script(){}
function wp_enqueue_media(){}
function add_submenu_page(){} function add_menu_page(){}
function get_post_meta(){ return ''; } function update_post_meta(){} function delete_post_meta(){}
function get_posts(){ return array(); } function get_post(){ return null; } function get_post_type(){ return ''; }
function get_queried_object_id(){ return 0; }
function register_post_type(){} function register_taxonomy(){}
function has_shortcode(){ return false; }
function is_singular(){ return false; }
function status_header(){} function nocache_headers(){} function wp_die($m=''){ throw new RuntimeException('wp_die'); }
function wp_safe_redirect($u=''){ throw new RuntimeException('redirect:'.$u); }
function add_query_arg($a,$u=''){ return $u.'?'.http_build_query((array)$a); }
function selected(){} function checked(){} function disabled(){}
function get_current_user_id(){ return 0; }
function wp_tempnam($x=''){ return tempnam(sys_get_temp_dir(),'tj'); }
function wp_remote_post(){ return new WP_Error('no','offline'); }
function wp_remote_retrieve_response_code(){ return 0; }
function wp_remote_retrieve_body(){ return ''; }
function is_wp_error($t){ return $t instanceof WP_Error; }
function __($s,$d=null){ return $s; } function esc_html__($s,$d=null){ return $s; }
function wpautop($s){ return $s; } function wp_kses_post($s){ return $s; }
function nl2br_shim($s){ return nl2br($s); }
class WP_Error {
    public $code, $message;
    public function __construct($c='',$m=''){ $this->code=$c; $this->message=$m; }
    public function get_error_message(){ return $this->message; }
    public function get_error_code(){ return $this->code; }
}
class WP_Post {}
class WP_Query {}
class FakeWPDB {
    public $prefix='wp_'; public $insert_id=1; public $queries=0;
    public function prepare($q,...$a){ if(count($a)===1&&is_array($a[0]))$a=$a[0];
        $q=str_replace(array('%s','%d'),array("'%s'",'%d'),$q); return vsprintf(str_replace(array("'%s'",'%d'),array("'%s'",'%d'),$q),array_map(function($x){return is_int($x)?$x:addslashes((string)$x);},$a)); }
    public function get_var($q){ $this->queries++; if(stripos($q,'SHOW TABLES')!==false) return 'wp_rgl_booking_final'; return 0; }
    public function get_results($q,$m=null){ $this->queries++; return array(); }
    public function get_row($q,$m=null){ $this->queries++; return null; }
    public function query($q){ $this->queries++; return 0; }
    public function insert($t,$d,$f=null){ $this->queries++; return 1; }
    public function update($t,$d,$w,$df=null,$wf=null){ $this->queries++; return 1; }
    public function delete($t,$w,$f=null){ $this->queries++; return 1; }
    public function get_charset_collate(){ return ''; }
    public function esc_like($s){ return addcslashes((string)$s,'_%\\'); }
}
$GLOBALS['wpdb'] = new FakeWPDB();
function dbDelta($sql){ return array(); }
