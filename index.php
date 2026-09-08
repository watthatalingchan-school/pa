<?php require 'config.php'; if(me()){ header('Location: app.php'); exit; } $s = school(); ?>
<!DOCTYPE html><html lang="th"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>ระบบส่งเอกสาร PA | <?=htmlspecialchars($s['school_name'])?></title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['Sarabun','sans-serif']},colors:{primary:{DEFAULT:'#79131D',light:'#AE2433'}}}}}</script>
</head>
<body class="font-sans min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-[#FDF6F6] via-[#F7EEEF] to-[#EFE3E4]">

<div class="absolute inset-0 overflow-hidden pointer-events-none">
  <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-[#AE2433]/10 blur-3xl"></div>
  <div class="absolute -bottom-32 -right-16 w-96 h-96 rounded-full bg-[#79131D]/10 blur-3xl"></div>
</div>

<div class="relative w-full max-w-5xl bg-white/90 backdrop-blur rounded-3xl shadow-2xl shadow-primary/10 ring-1 ring-black/5 overflow-hidden grid md:grid-cols-2">

  <!-- ซ้าย: แบรนด์ -->
  <div class="p-10 bg-gradient-to-br from-primary to-primary-light text-white flex flex-col justify-center items-center text-center gap-5">
    <div class="w-28 h-28 rounded-2xl bg-white/15 ring-4 ring-white/25 flex items-center justify-center overflow-hidden">
      <?php if($s['logo']): ?>
        <img src="<?=htmlspecialchars($s['logo'])?>" class="w-full h-full object-contain p-2">
      <?php else: ?><i class="fa-solid fa-school text-5xl text-white/80"></i><?php endif; ?>
    </div>
    <div>
      <h1 class="text-2xl font-extrabold leading-tight">ระบบส่งเอกสาร PA</h1>
      <p class="text-white/80 text-sm mt-1">Performance Agreement</p>
      <p class="text-white/70 text-sm">(ข้อตกลงในการพัฒนางาน)</p>
    </div>
    <div class="w-16 h-px bg-white/30"></div>
    <div class="space-y-1">
      <p class="font-semibold"><?=htmlspecialchars($s['school_name'])?></p>
      <p class="text-white/75 text-sm"><?=htmlspecialchars($s['affiliation'])?></p>
    </div>
  </div>

  <!-- ขวา: ฟอร์ม -->
  <div class="p-10 flex flex-col justify-center">
    <h2 class="text-xl font-bold text-gray-800">เข้าสู่ระบบ</h2>
    <p class="text-sm text-gray-500 mt-1 mb-6">กรุณากรอกชื่อผู้ใช้และรหัสผ่านของท่าน</p>
    <form id="loginForm" class="space-y-4">
      <div>
        <label class="text-sm font-medium text-gray-600">ชื่อผู้ใช้</label>
        <div class="relative mt-1">
          <i class="fa-regular fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input name="username" required class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="username">
        </div>
      </div>
      <div>
        <label class="text-sm font-medium text-gray-600">รหัสผ่าน</label>
        <div class="relative mt-1">
          <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input name="password" type="password" id="pw" required class="w-full pl-11 pr-11 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition" placeholder="••••••••">
          <button type="button" onclick="tg()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary"><i id="eye" class="fa-regular fa-eye"></i></button>
        </div>
      </div>
      <button id="btn" class="w-full py-3 rounded-xl bg-gradient-to-r from-primary to-primary-light text-white font-semibold shadow-lg shadow-primary/25 hover:opacity-95 active:scale-[.99] transition flex items-center justify-center gap-2">
        <i class="fa-solid fa-right-to-bracket"></i> เข้าสู่ระบบ
      </button>
    </form>
  </div>
</div>

<script>
function tg(){const p=document.getElementById('pw'),e=document.getElementById('eye');
  p.type = p.type==='password'?'text':'password';
  e.className = p.type==='password'?'fa-regular fa-eye':'fa-regular fa-eye-slash';}

document.getElementById('loginForm').addEventListener('submit', async e=>{
  e.preventDefault();
  const btn=document.getElementById('btn'), html=btn.innerHTML;
  btn.disabled=true; btn.innerHTML='<i class="fa-solid fa-circle-notch fa-spin"></i> กำลังตรวจสอบ...';
  const res = await fetch('api.php?act=login',{method:'POST',body:new FormData(e.target)}).then(r=>r.json());
  if(res.ok){ location.href='app.php'; }
  else { btn.disabled=false; btn.innerHTML=html;
    Swal.fire({icon:'error',title:'เข้าสู่ระบบไม่สำเร็จ',text:res.msg,confirmButtonColor:'#79131D'}); }
});
</script>
</body></html>