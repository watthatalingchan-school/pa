<?php
require 'config.php';
require_login();
if(!in_array(role(),['admin','director'])) die('ไม่มีสิทธิ์เข้าถึง');

$type = $_GET['type'] ?? 'excel';
$yid  = (int)($_GET['year_id'] ?? currentYearId());
$yr   = $pdo->query("SELECT year_name FROM fiscal_years WHERE id=$yid")->fetch();
$yearName = $yr['year_name'] ?? '-';
$s = school();

$users = $pdo->query("SELECT id,fullname,role FROM users WHERE role IN('deputy','teacher')
                      ORDER BY FIELD(role,'deputy','teacher'), fullname")->fetchAll();
$st = $pdo->prepare("SELECT * FROM submissions WHERE year_id=?"); $st->execute([$yid]);
$map=[]; foreach($st->fetchAll() as $r) $map[$r['user_id']][$r['doc_type']] = $r;

$ST_TH = ['pending'=>'รอตรวจ','approved'=>'ตรวจแล้ว','rejected'=>'ไม่ผ่าน'];
function cellText($d,$ST_TH){ return $d ? $ST_TH[$d['status']] : 'ยังไม่ส่ง'; }
function cellColor($d){
  if(!$d) return '#9e9e9e';
  return ['pending'=>'#F57C00','approved'=>'#26A69A','rejected'=>'#D32F2F'][$d['status']];
}

/* สรุปตัวเลข */
$sum = ['pa1'=>0,'annual'=>0,'present'=>0];
foreach($users as $u) foreach(['pa1','annual','present'] as $t)
  if(isset($map[$u['id']][$t])) $sum[$t]++;
$total = count($users);

/* ---------- EXCEL ---------- */
if($type==='excel'){
  $file = 'รายงานสรุปPA_ปี'.$yearName.'_'.date('dmY').'.xls';
  header('Content-Type: application/vnd.ms-excel; charset=utf-8');
  header('Content-Disposition: attachment; filename="'.$file.'"');
  header('Cache-Control: max-age=0');
  echo "\xEF\xBB\xBF"; // BOM กันภาษาไทยเพี้ยน
  ?>
  <table border="1" style="font-family:'TH Sarabun New',Tahoma;font-size:14pt;border-collapse:collapse">
    <tr><td colspan="6" align="center" style="font-size:18pt;font-weight:bold">รายงานสรุปการส่งเอกสาร PA</td></tr>
    <tr><td colspan="6" align="center" style="font-size:16pt"><?=htmlspecialchars($s['school_name'])?></td></tr>
    <tr><td colspan="6" align="center"><?=htmlspecialchars($s['affiliation'])?></td></tr>
    <tr><td colspan="6" align="center">ปีงบประมาณ <?=htmlspecialchars($yearName)?> | พิมพ์เมื่อ <?=date('d/m/Y H:i')?></td></tr>
    <tr><td colspan="6"></td></tr>
    <tr style="background:#79131D;color:#fff;font-weight:bold" align="center">
      <td>ลำดับ</td><td>ชื่อ-นามสกุล</td><td>ตำแหน่ง</td>
      <td>แบบข้อตกลง PA1</td><td>แบบรายงานสิ้นปี</td><td>ไฟล์นำเสนอ</td></tr>
    <?php foreach($users as $i=>$u): $d = $map[$u['id']] ?? []; ?>
    <tr>
      <td align="center"><?=$i+1?></td>
      <td><?=htmlspecialchars($u['fullname'])?></td>
      <td align="center"><?=ROLE_TH[$u['role']]?></td>
      <?php foreach(['pa1','annual','present'] as $t): ?>
      <td align="center" style="color:<?=cellColor($d[$t]??null)?>"><?=cellText($d[$t]??null,$ST_TH)?></td>
      <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
    <tr style="background:#f2e6e7;font-weight:bold">
      <td colspan="3" align="right">รวมส่งแล้ว (จาก <?=$total?> คน)</td>
      <td align="center"><?=$sum['pa1']?></td><td align="center"><?=$sum['annual']?></td><td align="center"><?=$sum['present']?></td>
    </tr>
  </table>
  <?php exit;
}

/* ---------- PDF (พิมพ์ผ่านเบราว์เซอร์ → Save as PDF) ---------- */
?>
<!DOCTYPE html><html lang="th"><head><meta charset="utf-8">
<title>รายงานสรุปการส่งเอกสาร PA ปี <?=htmlspecialchars($yearName)?></title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
<style>
 *{box-sizing:border-box}
 body{font-family:'Sarabun',sans-serif;margin:0;padding:28px;color:#222;font-size:14px;background:#fff}
 .head{text-align:center;border-bottom:3px solid #79131D;padding-bottom:14px;margin-bottom:18px}
 .head img{height:70px;margin-bottom:8px}
 .head h1{margin:4px 0;font-size:20px;color:#79131D}
 .head p{margin:2px 0;font-size:14px;color:#555}
 table{width:100%;border-collapse:collapse;font-size:13px}
 th{background:#79131D;color:#fff;padding:9px 6px;border:1px solid #79131D}
 td{padding:8px 6px;border:1px solid #ddd}
 tr:nth-child(even) td{background:#faf7f7}
 .c{text-align:center}
 .b{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600}
 .s-approved{background:#26A69A22;color:#00796B}
 .s-pending{background:#FFCA2833;color:#EF6C00}
 .s-rejected{background:#EF535022;color:#C62828}
 .s-none{background:#eee;color:#888}
 .sumbox{display:flex;gap:10px;margin:16px 0}
 .sumbox div{flex:1;border:1px solid #eee;border-radius:10px;padding:10px;text-align:center}
 .sumbox b{display:block;font-size:22px;color:#79131D}
 .foot{margin-top:26px;display:flex;justify-content:space-between;font-size:12px;color:#777}
 .sign{margin-top:50px;text-align:right;font-size:13px}
 .noprint{position:fixed;top:14px;right:14px;display:flex;gap:8px}
 .noprint button{padding:10px 18px;border:0;border-radius:10px;background:#79131D;color:#fff;font-family:'Sarabun';font-size:14px;cursor:pointer}
 .noprint a{padding:10px 18px;border-radius:10px;background:#eee;color:#444;text-decoration:none;font-size:14px}
 @media print{.noprint{display:none}body{padding:0}@page{size:A4 portrait;margin:14mm}}
</style></head><body>
<div class="noprint">
  <button onclick="window.print()">🖨️ บันทึกเป็น PDF</button>
  <a href="app.php#summary">ย้อนกลับ</a>
</div>

<div class="head">
  <?php if($s['logo']): ?><img src="<?=htmlspecialchars($s['logo'])?>"><?php endif; ?>
  <h1>รายงานสรุปการส่งเอกสาร PA</h1>
  <p>(Performance Agreement — ข้อตกลงในการพัฒนางาน)</p>
  <p><b><?=htmlspecialchars($s['school_name'])?></b> · <?=htmlspecialchars($s['affiliation'])?></p>
  <p>ปีงบประมาณ <?=htmlspecialchars($yearName)?></p>
</div>

<div class="sumbox">
  <div><b><?=$total?></b>บุคลากรทั้งหมด</div>
  <div><b><?=$sum['pa1']?></b>ส่ง PA1</div>
  <div><b><?=$sum['annual']?></b>ส่งรายงานสิ้นปี</div>
  <div><b><?=$sum['present']?></b>ส่งไฟล์นำเสนอ</div>
</div>

<table>
 <thead><tr>
  <th style="width:50px">ลำดับ</th><th>ชื่อ-นามสกุล</th><th style="width:120px">ตำแหน่ง</th>
  <th style="width:110px">แบบข้อตกลง PA1</th><th style="width:110px">แบบรายงานสิ้นปี</th><th style="width:110px">ไฟล์นำเสนอ</th>
 </tr></thead>
 <tbody>
 <?php foreach($users as $i=>$u): $d = $map[$u['id']] ?? []; ?>
  <tr>
   <td class="c"><?=$i+1?></td>
   <td><?=htmlspecialchars($u['fullname'])?></td>
   <td class="c"><?=ROLE_TH[$u['role']]?></td>
   <?php foreach(['pa1','annual','present'] as $t): $x=$d[$t]??null; ?>
   <td class="c"><span class="b s-<?=$x?$x['status']:'none'?>"><?=cellText($x,$ST_TH)?></span></td>
   <?php endforeach; ?>
  </tr>
 <?php endforeach; ?>
 </tbody>
</table>

<div class="sign">
  <p>ลงชื่อ ..................................................</p>
  <p>(...........................................................)</p>
  <p>ผู้อำนวยการ<?=htmlspecialchars($s['school_name'])?></p>
</div>

<div class="foot">
  <span>ระบบส่งเอกสาร PA · <?=htmlspecialchars($s['school_name'])?></span>
  <span>พิมพ์เมื่อ <?=date('d/m/Y H:i')?> น.</span>
</div>
</body></html>