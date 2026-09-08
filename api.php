<?php
require 'config.php';
$act = $_REQUEST['act'] ?? '';

/* ---------- LOGIN ---------- */
if($act === 'login'){
  $u = trim($_POST['username'] ?? ''); $p = $_POST['password'] ?? '';
  $st = $pdo->prepare("SELECT * FROM users WHERE username=?"); $st->execute([$u]);
  $row = $st->fetch();
  if(!$row || !password_verify($p, $row['password'])) fail('ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
  unset($row['password']); $_SESSION['user'] = $row; ok();
}
if($act === 'logout'){ session_destroy(); ok(); }

if(!me()) fail('เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่');
$U = me();

switch($act){

/* ================= ปีงบประมาณ ================= */
case 'years':
  ok(['data'=>$pdo->query("SELECT * FROM fiscal_years ORDER BY year_name DESC")->fetchAll(),'current'=>currentYearId()]);

case 'year_save':
  only('admin');
  $id=$_POST['id']??''; $n=trim($_POST['year_name']??''); $cur=(int)($_POST['is_current']??0);
  if($n==='') fail('กรุณากรอกชื่อปีงบประมาณ');
  $st=$pdo->prepare("SELECT id FROM fiscal_years WHERE year_name=? AND id<>?"); $st->execute([$n,$id?:0]);
  if($st->fetch()) fail('มีปีงบประมาณนี้อยู่แล้ว');
  if($id){ $pdo->prepare("UPDATE fiscal_years SET year_name=? WHERE id=?")->execute([$n,$id]); }
  else { $pdo->prepare("INSERT INTO fiscal_years(year_name) VALUES(?)")->execute([$n]); $id=$pdo->lastInsertId(); }
  if($cur){ $pdo->query("UPDATE fiscal_years SET is_current=0"); $pdo->prepare("UPDATE fiscal_years SET is_current=1 WHERE id=?")->execute([$id]); }
  ok();

case 'year_current':
  only('admin');
  $pdo->query("UPDATE fiscal_years SET is_current=0");
  $pdo->prepare("UPDATE fiscal_years SET is_current=1 WHERE id=?")->execute([$_POST['id']]); ok();

case 'year_delete':
  only('admin');
  $pdo->prepare("DELETE FROM fiscal_years WHERE id=?")->execute([$_POST['id']]); ok();

/* ================= ข้อมูลโรงเรียน ================= */
case 'school_get': ok(['data'=>school()]);

case 'school_save':
  only('admin');
  $s = school(); $logo = up('logo','logo');
  if($logo) delFile($s['logo']); else $logo = $s['logo'];
  $pdo->prepare("UPDATE school SET school_name=?,affiliation=?,logo=? WHERE id=1")
      ->execute([trim($_POST['school_name']), trim($_POST['affiliation']), $logo]);
  ok();

/* ================= จัดการผู้ใช้งาน ================= */
case 'users':
  only('admin');
  $q = "%".($_POST['q']??'')."%"; $r = $_POST['role'] ?? '';
  $sql = "SELECT id,username,fullname,avatar,role FROM users WHERE role<>'admin' AND fullname LIKE ?";
  $p = [$q]; if($r){ $sql.=" AND role=?"; $p[]=$r; }
  $sql .= " ORDER BY FIELD(role,'director','deputy','teacher'), fullname";
  $st=$pdo->prepare($sql); $st->execute($p); ok(['data'=>$st->fetchAll()]);

case 'user_save':
  only('admin');
  $id=$_POST['id']??''; $un=trim($_POST['username']); $fn=trim($_POST['fullname']);
  $rl=$_POST['role']; $pw=$_POST['password']??'';
  if(!$un||!$fn) fail('กรอกข้อมูลให้ครบถ้วน');
  if(!in_array($rl,['director','deputy','teacher'])) fail('สิทธิ์ไม่ถูกต้อง');
  $st=$pdo->prepare("SELECT id FROM users WHERE username=? AND id<>?"); $st->execute([$un,$id?:0]);
  if($st->fetch()) fail('Username นี้ถูกใช้งานแล้ว กรุณาใช้ชื่ออื่น');
  $av = up('avatar','avatar');
  if($id){
    $old=$pdo->query("SELECT * FROM users WHERE id=".(int)$id)->fetch();
    if($av) delFile($old['avatar']); else $av=$old['avatar'];
    $sql="UPDATE users SET username=?,fullname=?,role=?,avatar=?".($pw?",password=?":"")." WHERE id=?";
    $p=[$un,$fn,$rl,$av]; if($pw) $p[]=password_hash($pw,PASSWORD_DEFAULT); $p[]=$id;
    $pdo->prepare($sql)->execute($p);
  } else {
    if(!$pw) fail('กรุณากำหนดรหัสผ่าน');
    $pdo->prepare("INSERT INTO users(username,password,fullname,role,avatar) VALUES(?,?,?,?,?)")
        ->execute([$un,password_hash($pw,PASSWORD_DEFAULT),$fn,$rl,$av]);
  }
  ok();

case 'user_delete':
  only('admin');
  $r=$pdo->query("SELECT * FROM users WHERE id=".(int)$_POST['id'])->fetch();
  if($r){ delFile($r['avatar']); $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$r['id']]); }
  ok();

/* ================= โปรไฟล์ส่วนตัว ================= */
case 'profile_save':
  $un=trim($_POST['username']); $fn=trim($_POST['fullname']); $pw=$_POST['password']??'';
  if(!$un||!$fn) fail('กรอกข้อมูลให้ครบถ้วน');
  $st=$pdo->prepare("SELECT id FROM users WHERE username=? AND id<>?"); $st->execute([$un,$U['id']]);
  if($st->fetch()) fail('Username นี้ถูกใช้งานแล้ว กรุณาใช้ชื่ออื่น');
  $old=$pdo->query("SELECT * FROM users WHERE id=".(int)$U['id'])->fetch();
  $av=up('avatar','avatar'); if($av) delFile($old['avatar']); else $av=$old['avatar'];
  $sql="UPDATE users SET username=?,fullname=?,avatar=?".($pw?",password=?":"")." WHERE id=?";
  $p=[$un,$fn,$av]; if($pw) $p[]=password_hash($pw,PASSWORD_DEFAULT); $p[]=$U['id'];
  $pdo->prepare($sql)->execute($p);
  $n=$pdo->query("SELECT id,username,fullname,avatar,role FROM users WHERE id=".(int)$U['id'])->fetch();
  $_SESSION['user']=$n; ok(['user'=>$n]);

/* ================= แดชบอร์ด ADMIN ================= */
case 'dash_admin':
  only('admin');
  $y = $_POST['year_id'] ?? '';
  $where = $y ? " AND s.year_id=".(int)$y : "";
  $cnt = function($type) use($pdo,$where){
    return (int)$pdo->query("SELECT COUNT(DISTINCT s.user_id) c FROM submissions s
      JOIN users u ON u.id=s.user_id WHERE s.doc_type='$type' $where")->fetch()['c'];
  };
  $teachers = (int)$pdo->query("SELECT COUNT(*) c FROM users WHERE role='teacher'")->fetch()['c'];
  $deputies = (int)$pdo->query("SELECT COUNT(*) c FROM users WHERE role='deputy'")->fetch()['c'];

  $years = $pdo->query("SELECT * FROM fiscal_years ".($y?"WHERE id=".(int)$y:"")." ORDER BY year_name")->fetchAll();
  $labels=[]; $d1=[];$d2=[];$d3=[];
  foreach($years as $yr){
    $labels[]='ปี '.$yr['year_name'];
    foreach([['pa1',&$d1],['annual',&$d2],['present',&$d3]] as $t){
      $st=$pdo->prepare("SELECT COUNT(*) c FROM submissions WHERE doc_type=? AND year_id=?");
      $st->execute([$t[0],$yr['id']]); $t[1][]=(int)$st->fetch()['c'];
    }
  }
  ok(['cards'=>['teachers'=>$teachers,'deputies'=>$deputies,'pa1'=>$cnt('pa1'),'annual'=>$cnt('annual'),'present'=>$cnt('present')],
      'chart'=>['labels'=>$labels,'pa1'=>$d1,'annual'=>$d2,'present'=>$d3]]);

/* ================= สรุปผลเอกสาร (ADMIN) ================= */
case 'summary':
  only('admin');
  $y = $_POST['year_id'] ?: currentYearId();
  $users = $pdo->query("SELECT id,fullname,role FROM users WHERE role IN('deputy','teacher')
                        ORDER BY FIELD(role,'deputy','teacher'), fullname")->fetchAll();
  $st=$pdo->prepare("SELECT * FROM submissions WHERE year_id=?"); $st->execute([$y]);
  $map=[]; foreach($st->fetchAll() as $r) $map[$r['user_id']][$r['doc_type']]=$r;
  foreach($users as &$u) $u['docs'] = $map[$u['id']] ?? [];
  ok(['data'=>$users]);

/* ================= ส่งงาน (ครู/รองฯ) ================= */
case 'my_subs':
  only(['teacher','deputy']);
  $st=$pdo->prepare("SELECT s.*, f.year_name FROM submissions s JOIN fiscal_years f ON f.id=s.year_id
                     WHERE s.user_id=? ORDER BY f.year_name DESC, FIELD(s.doc_type,'pa1','annual','present')");
  $st->execute([$U['id']]); ok(['data'=>$st->fetchAll()]);

case 'sub_save':
  only(['teacher','deputy']);
  $id=$_POST['id']??''; $y=(int)$_POST['year_id']; $t=$_POST['doc_type']; $link=trim($_POST['link_url']??'');
  if(!$y||!in_array($t,['pa1','annual','present'])) fail('กรุณาเลือกข้อมูลให้ครบถ้วน');
  $st=$pdo->prepare("SELECT id FROM submissions WHERE user_id=? AND year_id=? AND doc_type=? AND id<>?");
  $st->execute([$U['id'],$y,$t,$id?:0]);
  if($st->fetch()) fail('คุณส่งงานประเภทนี้ในปีงบประมาณนี้แล้ว กรุณาแก้ไขรายการเดิม');
  $f = up('file','docs');
  if($id){
    $old=$pdo->query("SELECT * FROM submissions WHERE id=".(int)$id." AND user_id=".(int)$U['id'])->fetch();
    if(!$old) fail('ไม่พบข้อมูล');
    if($f) delFile($old['file_path']); else $f=$old['file_path'];
    $pdo->prepare("UPDATE submissions SET year_id=?,doc_type=?,file_path=?,link_url=?,status='pending',reason=NULL WHERE id=?")
        ->execute([$y,$t,$f,$link?:null,$id]);
  } else {
    $pdo->prepare("INSERT INTO submissions(user_id,year_id,doc_type,file_path,link_url) VALUES(?,?,?,?,?)")
        ->execute([$U['id'],$y,$t,$f,$link?:null]);
  }
  ok();

case 'sub_delete':
  only(['teacher','deputy']);
  $r=$pdo->query("SELECT * FROM submissions WHERE id=".(int)$_POST['id']." AND user_id=".(int)$U['id'])->fetch();
  if($r){ delFile($r['file_path']); $pdo->prepare("DELETE FROM submissions WHERE id=?")->execute([$r['id']]); }
  ok();

/* ================= ตรวจงาน (ผู้อำนวยการ) ================= */
case 'review_list':
  only('director');
  $t=$_POST['doc_type']; $y=currentYearId();
  $users=$pdo->query("SELECT id,fullname,role FROM users WHERE role IN('deputy','teacher')
                      ORDER BY FIELD(role,'deputy','teacher'), fullname")->fetchAll();
  $st=$pdo->prepare("SELECT * FROM submissions WHERE year_id=? AND doc_type=?"); $st->execute([$y,$t]);
  $map=[]; foreach($st->fetchAll() as $r) $map[$r['user_id']]=$r;
  foreach($users as &$u) $u['sub'] = $map[$u['id']] ?? null;
  $yr=$pdo->query("SELECT year_name FROM fiscal_years WHERE id=".(int)$y)->fetch();
  ok(['data'=>$users,'year'=>$yr['year_name']??'-']);

case 'review_save':
  only('director');
  $s=$_POST['status']; $rs=trim($_POST['reason']??'');
  if(!in_array($s,['pending','approved','rejected'])) fail('สถานะไม่ถูกต้อง');
  if($s==='rejected' && $rs==='') fail('กรุณาระบุเหตุผลที่ไม่ผ่าน');
  $pdo->prepare("UPDATE submissions SET status=?, reason=? WHERE id=?")
      ->execute([$s, $s==='rejected'?$rs:null, (int)$_POST['id']]);
  ok();

/* ================= แดชบอร์ดผู้ใช้ ================= */
case 'dash_user':
  $y = currentYearId();
  $st=$pdo->prepare("SELECT * FROM submissions WHERE user_id=? AND year_id=?"); $st->execute([$U['id'],$y]);
  $m=[]; foreach($st->fetchAll() as $r) $m[$r['doc_type']]=$r;
  $yr=$pdo->query("SELECT year_name FROM fiscal_years WHERE id=".(int)$y)->fetch();
  $extra=[];
  if(role()==='director'){
    foreach(['pa1','annual','present'] as $t){
      $q=$pdo->prepare("SELECT
          SUM(status='pending') p, SUM(status='approved') a, SUM(status='rejected') r
          FROM submissions WHERE year_id=? AND doc_type=?");
      $q->execute([$y,$t]); $extra[$t]=$q->fetch();
    }
    $extra['total_user']=(int)$pdo->query("SELECT COUNT(*) c FROM users WHERE role IN('deputy','teacher')")->fetch()['c'];
  }
  ok(['docs'=>$m,'year'=>$yr['year_name']??'-','extra'=>$extra]);
}
fail('ไม่พบคำสั่ง');