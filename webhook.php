<?php
require 'config.php';
require 'lib_notify.php';

// รับข้อมูลที่ LINE ส่งเข้ามา (JSON)
$body = file_get_contents('php://input');
$data = json_decode($body, true);

// ถ้าไม่มีข้อมูล หรือไม่ใช่ event จาก LINE ให้จบการทำงาน
if(!$data || empty($data['events'])) { http_response_code(200); exit('OK'); }

foreach($data['events'] as $ev){
  // ดึง User ID ของ LINE
  $uid = $ev['source']['userId'] ?? null;
  if(!$uid) continue;

  // กรณีเพิ่มเพื่อนใหม่
  if($ev['type']==='follow'){
    linePush($uid, "👋 ยินดีต้อนรับสู่ระบบส่งเอกสาร PA\n\nกรุณาพิมพ์ \"รหัส 6 หลัก\" ที่ได้จากเมนู \"ข้อมูลส่วนตัว\" ในระบบ เพื่อรับการแจ้งเตือนผลการตรวจเอกสาร");
    continue;
  }

  // กรณีพิมพ์ข้อความ
  if($ev['type']==='message' && ($ev['message']['type']??'')==='text'){
    $txt = trim($ev['message']['text']);

    // ตรวจสอบว่าใช่รหัส 6 หลักที่ระบบสร้างไว้ไหม
    if(preg_match('/^\d{6}$/',$txt)){
      $st = $pdo->prepare("SELECT id,fullname FROM users WHERE line_link_code=?");
      $st->execute([$txt]); $u = $st->fetch();
      
      if($u){
        // ผูกบัญชี LINE กับ User ในระบบ
        $pdo->prepare("UPDATE users SET line_user_id=?, line_link_code=NULL WHERE id=?")
            ->execute([$uid,$u['id']]);
        linePush($uid, "✅ เชื่อมต่อสำเร็จ!\n\nสวัสดีคุณ {$u['fullname']}\nตอนนี้คุณจะได้รับการแจ้งเตือนผลการตรวจเอกสาร PA ผ่านช่องทางนี้ครับ");
      } else {
        linePush($uid, "❌ รหัสไม่ถูกต้องหรือหมดอายุ\nกรุณาสร้างรหัสใหม่จากเมนู \"ข้อมูลส่วนตัว\" ในระบบครับ");
      }
    } else {
      linePush($uid, "📋 ระบบส่งเอกสาร PA\n\nพิมพ์รหัส 6 หลักเพื่อผูกบัญชี\n(ดูรหัสได้ที่เมนู \"ข้อมูลส่วนตัว\" ในระบบ)");
    }
  }
}

http_response_code(200); 
echo 'OK';