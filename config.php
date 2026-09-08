<?php
session_start();
date_default_timezone_set('Asia/Bangkok');

define('DB_HOST','localhost');
define('DB_NAME','pa_system');
define('DB_USER','root');
define('DB_PASS','');

try {
  $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
} catch (Exception $e) { die('เชื่อมต่อฐานข้อมูลไม่สำเร็จ'); }

function school(){ global $pdo; return $pdo->query("SELECT * FROM school WHERE id=1")->fetch(); }
function me(){ return $_SESSION['user'] ?? null; }
function role(){ return me()['role'] ?? ''; }
function require_login(){ if(!me()){ header('Location: index.php'); exit; } }
function json($d){ header('Content-Type: application/json; charset=utf-8'); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }
function ok($d=[]){ json(array_merge(['ok'=>true],$d)); }
function fail($m){ json(['ok'=>false,'msg'=>$m]); }
function only($roles){ if(!in_array(role(),(array)$roles)) fail('ไม่มีสิทธิ์เข้าถึง'); }
function currentYearId(){ global $pdo; $r=$pdo->query("SELECT id FROM fiscal_years WHERE is_current=1 LIMIT 1")->fetch(); return $r?$r['id']:null; }

function up($key,$sub){
  if(empty($_FILES[$key]['name'])) return null;
  if($_FILES[$key]['error']!==UPLOAD_ERR_OK) return null;
  $ext = strtolower(pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION));
  $allow = ['pdf','doc','docx','ppt','pptx','xls','xlsx','jpg','jpeg','png','gif','webp','zip','rar'];
  if(!in_array($ext,$allow)) return null;
  $dir = __DIR__."/uploads/$sub";
  if(!is_dir($dir)) mkdir($dir,0777,true);
  $name = $sub.'_'.date('YmdHis').'_'.substr(md5(uniqid('',true)),0,8).'.'.$ext;
  return move_uploaded_file($_FILES[$key]['tmp_name'], "$dir/$name") ? "uploads/$sub/$name" : null;
}
function delFile($p){ if($p && file_exists(__DIR__.'/'.$p)) @unlink(__DIR__.'/'.$p); }

const ROLE_TH = ['admin'=>'ผู้ดูแลระบบ','director'=>'ผู้อำนวยการ','deputy'=>'รองฯผู้อำนวยการ','teacher'=>'ครู'];
const TYPE_TH = ['pa1'=>'แบบข้อตกลง PA1','annual'=>'แบบรายงานสิ้นปี','present'=>'ไฟล์นำเสนอ'];