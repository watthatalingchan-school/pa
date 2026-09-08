<?php
/* ===== LINE Messaging API — Push Notification ===== */

function setting($key, $default=''){
  global $pdo;
  $st = $pdo->prepare("SELECT sval FROM settings WHERE skey=?");
  $st->execute([$key]); $r = $st->fetch();
  return $r ? $r['sval'] : $default;
}
function setSetting($key,$val){
  global $pdo;
  $pdo->prepare("INSERT INTO settings(skey,sval) VALUES(?,?) ON DUPLICATE KEY UPDATE sval=VALUES(sval)")
      ->execute([$key,$val]);
}

/** ส่งข้อความหาผู้ใช้ 1 คน */
function linePush($userId, $text){
  global $pdo;
  if(setting('line_enable')!=='1') return false;
  $token = trim(setting('line_token'));
  if(!$token || !$userId) return false;

  $payload = json_encode([
    'to' => $userId,
    'messages' => [['type'=>'text','text'=>mb_substr($text,0,4900)]]
  ], JSON_UNESCAPED_UNICODE);

  $ch = curl_init('https://api.line.me/v2/bot/message/push');
  curl_setopt_array($ch,[
    CURLOPT_POST=>true,
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_TIMEOUT=>10,
    CURLOPT_HTTPHEADER=>[
      'Content-Type: application/json; charset=utf-8',
      'Authorization: Bearer '.$token,
      'X-Line-Retry-Key: '.sprintf('%s-%s-%s-%s-%s',
        bin2hex(random_bytes(4)),bin2hex(random_bytes(2)),bin2hex(random_bytes(2)),
        bin2hex(random_bytes(2)),bin2hex(random_bytes(6)))
    ],
    CURLOPT_POSTFIELDS=>$payload
  ]);
  $res = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return ['ok'=>$code==200,'code'=>$code,'res'=>$res];
}

/** ส่งหาผู้ใช้ตาม user_id ในระบบ + บันทึก log */
function notifyUser($sysUserId, $text){
  global $pdo;
  $u = $pdo->query("SELECT line_user_id FROM users WHERE id=".(int)$sysUserId)->fetch();
  if(!$u || !$u['line_user_id']){
    $pdo->prepare("INSERT INTO notify_log(user_id,message,status) VALUES(?,?,'ไม่ได้ผูก LINE')")
        ->execute([$sysUserId,$text]);
    return false;
  }
  $r = linePush($u['line_user_id'], $text);
  $pdo->prepare("INSERT INTO notify_log(user_id,message,status) VALUES(?,?,?)")
      ->execute([$sysUserId,$text, ($r && $r['ok'])?'สำเร็จ':'ล้มเหลว']);
  return $r && $r['ok'];
}

/** ส่งหาผู้อำนวยการทุกคน */
function notifyDirectors($text){
  global $pdo;
  foreach($pdo->query("SELECT id FROM users WHERE role='director'")->fetchAll() as $d)
    notifyUser($d['id'], $text);
}

/** ข้อความแจ้งผลตรวจ */
function msgReview($fullname,$typeTh,$year,$status,$reason=''){
  $head = ['approved'=>"✅ ผลการตรวจ: ผ่าน",
           'rejected'=>"❌ ผลการตรวจ: ไม่ผ่าน",
           'pending' =>"🕓 สถานะเปลี่ยนเป็น: รอตรวจ"][$status];
  $m  = "📋 ระบบส่งเอกสาร PA\n";
  $m .= "──────────────\n";
  $m .= "เรียน $fullname\n\n";
  $m .= "📄 ประเภท: $typeTh\n";
  $m .= "📅 ปีงบประมาณ: $year\n";
  $m .= "$head\n";
  if($status==='rejected' && $reason) $m .= "\n💬 เหตุผล/ข้อเสนอแนะ:\n$reason\n\nกรุณาแก้ไขและส่งใหม่อีกครั้ง";
  if($status==='approved') $m .= "\nขอบคุณสำหรับความร่วมมือครับ 🙏";
  $m .= "\n\n🕐 ".date('d/m/Y H:i')." น.";
  return $m;
}

/** ข้อความแจ้งผู้อำนวยการเมื่อมีคนส่งงาน */
function msgSubmit($fullname,$roleTh,$typeTh,$year){
  return "🔔 มีเอกสารรอตรวจ\n──────────────\n"
       . "👤 $fullname ($roleTh)\n"
       . "📄 $typeTh\n📅 ปีงบประมาณ $year\n\n"
       . "กรุณาเข้าระบบเพื่อตรวจสอบ\n🕐 ".date('d/m/Y H:i')." น.";
}