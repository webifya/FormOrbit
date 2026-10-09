<?php
error_reporting(E_ALL & ~E_DEPRECATED);
set_error_handler(function($level,$message,$file,$line){if(error_reporting() & $level)throw new ErrorException($message,0,$level,$file,$line);return false;});
// Standalone behavioral regression harness. No production connections or database.
define('ABSPATH', __DIR__ . '/'); define('DAY_IN_SECONDS', 86400); define('HOUR_IN_SECONDS',3600); define('MINUTE_IN_SECONDS',60); define('MB_IN_BYTES',1048576);
define('WEBFORM_PATH', dirname(__DIR__, 2) . (is_dir(dirname(__DIR__, 2) . '/core') ? '/core/' : '/')); define('WEBFORM_URL','https://example.test/formorbit/'); define('WEBFORM_VERSION','6.0.0'); define('WEBFORM_FILE',WEBFORM_PATH.'formorbit.php');
$hooks=[]; $meta=[]; $admin=true;
function add_filter($tag,$cb,$priority=10,$args=1){global $hooks;$hooks[$tag][$priority][]=[$cb,$args];}
function add_action($tag,$cb,$priority=10,$args=1){add_filter($tag,$cb,$priority,$args);}
function remove_action($tag,$cb,$priority=10){global $removed_actions;$removed_actions[]=[$tag,$cb,$priority];}
function wp_unslash($value){return is_string($value)?stripslashes($value):$value;}
function apply_filters($tag,$value,...$args){global $hooks;$items=$hooks[$tag]??[];ksort($items);foreach($items as $group)foreach($group as [$cb,$count])$value=call_user_func_array($cb,array_slice(array_merge([$value],$args),0,$count));return $value;}
function do_action($tag,...$args){global $hooks;$items=$hooks[$tag]??[];ksort($items);foreach($items as $group)foreach($group as [$cb,$count])call_user_func_array($cb,array_slice($args,0,$count));}
function has_action($tag){global $hooks;return !empty($hooks[$tag]);}
function __($s,$d=''){return $s;} function esc_html__($s,$d=''){return htmlspecialchars($s,ENT_QUOTES);} function esc_html_e($s,$d=''){echo esc_html__($s);}
function esc_attr($s){return htmlspecialchars((string)$s,ENT_QUOTES);} function esc_html($s){return esc_attr($s);} function esc_textarea($s){return esc_attr($s);} function esc_url($s){return $s;} function esc_url_raw($s){return $s;}
function sanitize_key($s){return preg_replace('/[^a-z0-9_\-]/','',strtolower((string)$s));} function sanitize_html_class($s){return preg_replace('/[^a-zA-Z0-9_\-]/','',(string)$s);}
function sanitize_hex_color($s){return preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i',(string)$s)?$s:null;}
function sanitize_text_field($s){return is_array($s)?'':trim(strip_tags((string)$s));} function sanitize_textarea_field($s){return sanitize_text_field($s);} function wp_strip_all_tags($s){return strip_tags($s);} function wp_kses_post($s){return preg_replace('#<script.*?</script>#s','',(string)$s);} function wp_kses($s,$tags){return $s;}
function absint($s){return abs((int)$s);} function __return_true(){return true;} function get_option($s,$d=false){return $d;} function wp_json_encode($s,$f=0){return json_encode($s,$f);} function wp_roles(){return (object)['roles'=>['administrator'=>[],'subscriber'=>[]]];}
function get_post_meta($id,$key,$single=false){global $meta;return $meta[$id][$key]??'';} function update_post_meta($id,$key,$value){global $meta;$meta[$id][$key]=$value;}
function current_user_can(...$args){global $admin;return $admin;} function is_user_logged_in(){global $admin;return $admin;} function is_admin(){return false;} function admin_url($s){return 'https://example.test/wp-admin/'.$s;} function get_the_title($id){return 'Test form';}
function selected($a,$b,$echo=true){$s=(string)$a===(string)$b?' selected':'';if($echo)echo $s;return $s;} function checked($a,$b=true,$echo=true){return selected($a,$b,$echo);}
function add_shortcode(...$a){} function shortcode_atts($defaults,$atts,$s=''){return array_merge($defaults,$atts);} function get_post_type($id){return 'webform_form';} function get_post_status($id){return 'publish';} function get_post($id){return (object)['ID'=>$id,'post_type'=>'webform_form','post_title'=>'Test form','post_status'=>'publish'];}
function wp_enqueue_style(...$a){} function wp_enqueue_script(...$a){} function wp_localize_script(...$a){} function wp_create_nonce($s){return 'test';} function wp_parse_args($a,$b=[]){return array_merge($b,(array)$a);} function wp_nonce_field(...$a){} function wp_editor($text,$id,$args=[]){echo '<textarea id="'.esc_attr($id).'">'.esc_textarea($text).'</textarea>';}
function plugin_basename($s){return basename($s);} function current_time($s,$gmt=false){return time();} function wp_timezone(){return new DateTimeZone('Asia/Dhaka');} function wp_get_current_user(){return (object)['roles'=>['administrator']];}
function is_email($s){return is_string($s)&&filter_var($s,FILTER_VALIDATE_EMAIL);} function sanitize_email($s){return filter_var($s,FILTER_SANITIZE_EMAIL);} function wp_generate_password($length=12,...$args){return substr(bin2hex(random_bytes($length)),0,$length);} function post_type_exists($s){return in_array($s,['post','page']);}
function check($condition,$label){if(!$condition)throw new RuntimeException('FAIL: '.$label);echo 'PASS: '.$label."\n";}
require WEBFORM_PATH.'includes/formorbit-appearance.php';
require WEBFORM_PATH.'includes/formorbit-countries.php';
require WEBFORM_PATH.'includes/class-formorbit-fields.php';
require WEBFORM_PATH.'includes/class-formorbit-form-tools.php';
require WEBFORM_PATH.'includes/class-formorbit-drafts.php';
require WEBFORM_PATH.'includes/class-formorbit-documents.php';
require WEBFORM_PATH.'includes/class-formorbit-public.php';
require WEBFORM_PATH.'includes/class-formorbit-admin.php';
$fields=new FormOrbit_Fields();$tools=new FormOrbit_Form_Tools();$drafts=new FormOrbit_Drafts();new FormOrbit_Documents();$public=new Webform_Public();$editor=new Webform_Admin();
check(apply_filters('webform_can_export_forms',false)===true,'Free export enabled');
check(count(apply_filters('webform_allowed_field_types',[]))>=20,'Advanced fields available without Pro');
check(count(apply_filters('webform_style_presets',['modern'=>'Modern','minimal'=>'Minimal','rounded'=>'Rounded']))>=20,'All style presets available');
$templates=apply_filters('webform_templates',[]);check(count($templates)>=20&&!array_filter($templates,fn($t)=>!empty($t['pro'])),'All templates unlocked');
$settings=apply_filters('webform_sanitize_form_settings',[],['base_font_size'=>32,'label_font_size'=>30,'form_padding'=>500,'line_height'=>1.8,'custom_css'=>'input{color:red} @import "bad";','save_progress_enabled'=>true,'pdf_notifications'=>true,'user_notification_enabled'=>true]);
check($settings['base_font_size']===32&&$settings['label_font_size']===30&&$settings['form_padding']==80,'Typography and spacing saved and bounded');
check(!str_contains($settings['custom_css'],'@import'),'Unsafe CSS import stripped');
check($settings['save_progress_enabled']&&$settings['pdf_notifications']&&$settings['user_notification_enabled'],'General form controls available');
$sanitize=new ReflectionMethod(Webform_Admin::class,'sanitize_schema');$sanitize->setAccessible(true);
foreach (apply_filters('webform_allowed_field_types',[]) as $type) { $sanitize->invoke($editor,[['fields'=>[['type'=>$type,'label'=>$type]]]]); }
$schema=$sanitize->invoke($editor,[['id'=>'one','title'=>'One','fields'=>[['id'=>'a','type'=>'text','label'=>'Name','style'=>['width'=>'50']],['id'=>'group','type'=>'field_group','label'=>'People','children'=>[['id'=>'child','type'=>'email','label'=>'Email']]],['id'=>'divider','type'=>'divider','label'=>'Divider','divider_style'=>'dotted','divider_thickness'=>4]]]]);
check(count($schema[0]['fields'])===3&&count($schema[0]['fields'][1]['children'])===1,'Advanced schema and children preserved');
$meta[1]=['_webform_schema'=>$schema,'_webform_settings'=>$settings];
ob_start();$fields->style_controls($settings);$controls=ob_get_clean();check(str_contains($controls,'webform-custom-css')&&str_contains($controls,'webform-font-family'),'Design controls rendered');
ob_start();$tools->builder_settings_panel(1);$panel=ob_get_clean();check(str_contains($panel,'Mailchimp')&&str_contains($panel,'data-panel="integrations"'),'Free integration preview visible');
ob_start();$fields->custom_css(1,$settings);$css=ob_get_clean();check(str_contains($css,'dotted')&&str_contains($css,'#webform-1'),'Divider and custom CSS scoped');
$html=$public->shortcode(['id'=>1]);check(str_contains($html,'Edit this form'),'Admin edit link visible');
$admin=false;$html=$public->shortcode(['id'=>1]);check(!str_contains($html,'Edit this form'),'Visitor cannot see admin edit link');
$fn=new ReflectionMethod(FormOrbit_Drafts::class,'sanitize_draft_value');$fn->setAccessible(true);check($fn->invoke($drafts,[['name'=>'<b>A</b>']])[0]['name']==='A','Nested draft values retained and sanitized');
check(!apply_filters('formorbit_integrations_enabled',false),'Free does not activate Pro connectors');
$removed_actions=[];
$wp_filter=['admin_notices'=>(object)['callbacks'=>[10=>[['function'=>'unrelated_plugin_notice'],['function'=>[$editor,'review_notice']],['function'=>['Webform_Pro_Admin','license_notice']]]]]];
$_GET['page']='dashboard';$editor->suppress_editor_notices();check(count($removed_actions)===0,'Other admin screens retain notices');
$_GET['page']='formorbit-pro';$editor->suppress_editor_notices();check(count($removed_actions)===2,'FormOrbit hides third-party and review notices but preserves own actionable notices');
unset($_GET['page']);
$styled = $sanitize->invoke($editor,[['fields'=>[['id'=>'title','type'=>'heading','label'=>'Heading','style'=>['customized'=>true,'font_size'=>48,'font_family'=>'Georgia,serif','font_style'=>'italic','font_weight'=>'700']]]]]);
check($styled[0]['fields'][0]['style']['font_size']===48 && $styled[0]['fields'][0]['style']['font_family']==='Georgia,serif','Field typography survives saving');
$meta[2]=['_webform_schema'=>$styled,'_webform_settings'=>$settings];
ob_start();$fields->custom_css(2,$settings);$typography=ob_get_clean();
check(str_contains($typography,'font-size:48px') && str_contains($typography,'font-style:italic'),'Public heading typography emitted');
$vars=$fields->style_variables([],['style_preset'=>'luxury','text_color'=>'#1d2327','form_background'=>'#ffffff']);
check($vars['--wf-text']==='#1d2327' && $vars['--wf-form-bg']==='#ffffff','Preset preserves explicit customer colors');
$phone=$sanitize->invoke($editor,[['fields'=>[['id'=>'phone','type'=>'phone','label'=>'Phone','phone_country'=>'US','phone_country_selector'=>false]]]]);
$meta[3]=['_webform_schema'=>$phone,'_webform_settings'=>$settings];
$phoneHtml=$public->shortcode(['id'=>3]);
check(!str_contains($phoneHtml,'class="webform-phone-prefix"') && !str_contains($phoneHtml,'class="webform-phone-country"') && str_contains($phoneHtml,'data-default-country="US"'),'National phone display hides calling code');
if (is_file(dirname(WEBFORM_PATH).'/includes/class-formorbit-pro-admin.php')) {
    require dirname(WEBFORM_PATH).'/includes/class-formorbit-pro-admin.php';
    $license=new class {public $valid=false;public function is_valid(){return $this->valid;}};
    new Webform_Pro_Admin($license);
    check(!apply_filters('formorbit_integrations_enabled',false),'Inactive Pro keeps connectors locked');
    $license->valid=true;
    check(apply_filters('formorbit_integrations_enabled',false),'Active Pro enables connectors');
}
echo "All PHP behavioral checks passed.\n";
