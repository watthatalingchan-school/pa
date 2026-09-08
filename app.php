<?php require 'config.php'; require_login(); $s=school(); $U=me(); ?>
<!DOCTYPE html><html lang="th"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>ระบบส่งเอกสาร PA | <?=htmlspecialchars($s['school_name'])?></title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{sans:['Sarabun','sans-serif']},colors:{primary:{DEFAULT:'#79131D',light:'#AE2433'}}}}}</script>
<style>
 body{font-family:'Sarabun',sans-serif}
 ::-webkit-scrollbar{width:8px;height:8px}::-webkit-scrollbar-thumb{background:#d9c6c8;border-radius:99px}
 .skel{background:linear-gradient(90deg,#eee 25%,#f5f5f5 37%,#eee 63%);background-size:400% 100%;animation:sk 1.2s infinite}
 @keyframes sk{0%{background-position:100% 50%}100%{background-position:0 50%}}
 .nav-item.active{background:linear-gradient(90deg,#79131D,#AE2433);color:#fff;box-shadow:0 8px 20px -8px rgba(121,19,29,.7)}
 .fade{animation:fd .35s ease}@keyframes fd{from{opacity:0;transform:translateY(8px)}to{opacity:1}}
</style>
</head>
<body class="bg-[#F7F5F5] font-sans">

<!-- LOADING OVERLAY -->
<div id="loader" class="hidden fixed inset-0 z-[9999] bg-white/60 backdrop-blur-sm flex items-center justify-center">
  <div class="w-14 h-14 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
</div>

<!-- SIDEBAR -->
<aside id="sidebar" class="fixed z-50 inset-y-0 left-0 w-72 bg-white border-r border-gray-100 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-300">
  <div class="p-5 flex items-center gap-3 border-b border-gray-100">
    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-primary-light flex items-center justify-center overflow-hidden shrink-0">
      <?php if($s['logo']): ?><img src="<?=htmlspecialchars($s['logo'])?>" class="w-full h-full object-contain p-1 bg-white">
      <?php else: ?><i class="fa-solid fa-school text-white"></i><?php endif; ?>
    </div>
    <div class="min-w-0">
      <p class="font-bold text-gray-800 leading-tight">ระบบส่งเอกสาร PA</p>
      <p class="text-[11px] text-gray-500 truncate"><?=htmlspecialchars($s['school_name'])?></p>
    </div>
  </div>

  <nav id="menu" class="flex-1 overflow-y-auto p-3 space-y-1"></nav>

  <div class="p-3 border-t border-gray-100">
    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50">
      <img id="sbAvatar" src="<?=$U['avatar']?htmlspecialchars($U['avatar']):'https://ui-avatars.com/api/?name='.urlencode($U['fullname']).'&background=79131D&color=fff'?>" class="w-10 h-10 rounded-full object-cover ring-2 ring-white shadow">
      <div class="min-w-0 flex-1">
        <p id="sbName" class="text-sm font-semibold text-gray-800 truncate"><?=htmlspecialchars($U['fullname'])?></p>
        <span class="text-[11px] px-2 py-0.5 rounded-full bg-primary/10 text-primary font-medium"><?=ROLE_TH[$U['role']]?></span>
      </div>
      <button onclick="doLogout()" class="w-9 h-9 rounded-lg bg-white text-red-500 hover:bg-red-500 hover:text-white transition shadow-sm" title="ออกจากระบบ">
        <i class="fa-solid fa-right-from-bracket"></i></button>
    </div>
  </div>
</aside>
<div id="backdrop" onclick="toggleMenu()" class="hidden fixed inset-0 bg-black/40 z-40 lg:hidden"></div>

<!-- MAIN -->
<div class="lg:ml-72 min-h-screen flex flex-col">
  <header class="sticky top-0 z-30 bg-white/80 backdrop-blur border-b border-gray-100 px-4 lg:px-8 py-3 flex items-center gap-3">
    <button onclick="toggleMenu()" class="lg:hidden w-10 h-10 rounded-xl bg-primary text-white shadow-lg shadow-primary/25"><i class="fa-solid fa-bars"></i></button>
    <div class="flex-1 min-w-0">
      <h1 id="pageTitle" class="font-bold text-gray-800 truncate">แดชบอร์ด</h1>
      <p id="pageSub" class="text-xs text-gray-400 truncate">ภาพรวมระบบ</p>
    </div>
  </header>

  <main id="content" class="flex-1 p-4 lg:p-8"></main>

  <footer class="px-4 lg:px-8 py-5 border-t border-gray-100 bg-white text-center text-xs text-gray-500">
    <i class="fa-regular fa-copyright"></i> ระบบส่งเอกสาร PA &nbsp;|&nbsp; <span class="font-semibold text-primary"><?=htmlspecialchars($s['school_name'])?></span>
  </footer>
</div>

<!-- MODAL -->
<div id="modal" class="hidden fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm p-4 overflow-y-auto">
  <div class="min-h-full flex items-center justify-center">
    <div id="modalBox" class="bg-white w-full max-w-lg rounded-2xl shadow-2xl fade"></div>
  </div>
</div>

<script>
/* ================= CORE ================= */
const ME = <?=json_encode(['id'=>$U['id'],'role'=>$U['role'],'fullname'=>$U['fullname'],'username'=>$U['username'],'avatar'=>$U['avatar']],JSON_UNESCAPED_UNICODE)?>;
const ROLE_TH={admin:'ผู้ดูแลระบบ',director:'ผู้อำนวยการ',deputy:'รองฯผู้อำนวยการ',teacher:'ครู'};
const TYPE_TH={pa1:'แบบข้อตกลง PA1',annual:'แบบรายงานสิ้นปี',present:'ไฟล์นำเสนอ'};
const $=s=>document.querySelector(s);
const esc=s=>(s??'').toString().replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const loader=(b)=>$('#loader').classList.toggle('hidden',!b);
const toast=(i,t)=>Swal.fire({toast:true,position:'top-end',icon:i,title:t,showConfirmButton:false,timer:2200,timerProgressBar:true});

async function api(act,data=null,silent=false){
  if(!silent) loader(true);
  const fd = data instanceof FormData ? data : new FormData();
  if(data && !(data instanceof FormData)) for(const k in data) fd.append(k,data[k]??'');
  try{
    const r = await fetch('api.php?act='+act,{method:'POST',body:fd});
    const j = await r.json();
    if(!j.ok && j.msg==='เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่'){ location.href='index.php'; }
    return j;
  }catch(e){ return {ok:false,msg:'เชื่อมต่อเซิร์ฟเวอร์ไม่สำเร็จ'} }
  finally{ if(!silent) loader(false); }
}
function toggleMenu(){ $('#sidebar').classList.toggle('-translate-x-full'); $('#backdrop').classList.toggle('hidden'); }
function openModal(html){ $('#modalBox').innerHTML=html; $('#modal').classList.remove('hidden'); }
function closeModal(){ $('#modal').classList.add('hidden'); $('#modalBox').innerHTML=''; }
$('#modal').addEventListener('click',e=>{ if(e.target.id==='modal') closeModal(); });

function doLogout(){
  Swal.fire({title:'ออกจากระบบ?',text:'คุณต้องการออกจากระบบใช่หรือไม่',icon:'question',showCancelButton:true,
    confirmButtonColor:'#79131D',cancelButtonColor:'#9ca3af',confirmButtonText:'ออกจากระบบ',cancelButtonText:'ยกเลิก'})
  .then(async r=>{ if(r.isConfirmed){ await api('logout'); location.href='index.php'; }});
}

/* ================= SKELETON ================= */
const skCards=(n=4)=>`<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">${'<div class="h-28 rounded-2xl skel"></div>'.repeat(n)}</div>`;
const skTable=(n=6)=>`<div class="bg-white rounded-2xl p-5 space-y-3">${'<div class="h-10 rounded-lg skel"></div>'.repeat(n)}</div>`;

/* ================= BADGE ================= */
function statusBadge(st){
  const m={pending:['รอตรวจ','bg-[#FFCA28]/20 text-[#F57C00] ring-[#FFCA28]/40','fa-clock'],
           approved:['ตรวจแล้ว','bg-[#26A69A]/15 text-[#26A69A] ring-[#26A69A]/30','fa-circle-check'],
           rejected:['ไม่ผ่าน','bg-[#EF5350]/15 text-[#D32F2F] ring-[#EF5350]/30','fa-circle-xmark']};
  const x=m[st]||['ยังไม่ส่ง','bg-gray-100 text-gray-500 ring-gray-200','fa-minus'];
  return `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold ring-1 ${x[1]}"><i class="fa-solid ${x[2]}"></i>${x[0]}</span>`;
}
const sentIcon=s=>s?'<span class="inline-flex w-8 h-8 items-center justify-center rounded-full bg-[#26A69A]/15 text-[#26A69A]"><i class="fa-solid fa-paper-plane"></i></span>'
                   :'<span class="inline-flex w-8 h-8 items-center justify-center rounded-full bg-gray-100 text-gray-400"><i class="fa-solid fa-ban"></i></span>';
const roleBadge=r=>`<span class="px-2.5 py-1 rounded-lg text-xs font-medium ${r==='deputy'?'bg-[#4A2C6D]/10 text-[#4A2C6D]':'bg-[#0288D1]/10 text-[#0288D1]'}">${ROLE_TH[r]}</span>`;

const fileBtn=(p)=>p?`<a href="${esc(p)}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#0288D1]/10 text-[#0288D1] hover:bg-[#0288D1] hover:text-white text-xs font-medium transition"><i class="fa-solid fa-file-lines"></i> เปิดไฟล์</a>`
  :'<span class="text-xs text-gray-300">—</span>';
const linkBtn=(l)=>l?`<a href="${esc(l)}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#AED581]/25 text-[#558B2F] hover:bg-[#AED581] text-xs font-medium transition"><i class="fa-solid fa-link"></i> เปิดลิงก์</a>`
  :'<span class="text-xs text-gray-300">—</span>';

/* ================= MENU ================= */
const MENUS={

 admin:[['dashboard','แดชบอร์ด','fa-chart-pie'],['school','ข้อมูลโรงเรียน','fa-school'],

        ['users','จัดการผู้ใช้งาน','fa-users-gear'],['years','จัดการปีงบประมาณ','fa-calendar-days'],

        ['summary','สรุปผลการส่งเอกสาร','fa-clipboard-list'],

        ['line','ตั้งค่าแจ้งเตือน LINE','fa-comment-dots']],

 director:[['dashboard','แดชบอร์ด','fa-chart-pie'],['profile','ข้อมูลส่วนตัว','fa-user-pen'],

        ['rv_pa1','ตรวจแบบข้อตกลง PA1','fa-file-signature'],['rv_annual','ตรวจแบบรายงานสิ้นปี','fa-file-invoice'],

        ['rv_present','ตรวจไฟล์นำเสนอ','fa-file-powerpoint'],

        ['summary','รายงานสรุป / Export','fa-file-export']],

 user:[['dashboard','แดชบอร์ด','fa-chart-pie'],['profile','ข้อมูลส่วนตัว','fa-user-pen'],

        ['submit','ส่งงานเอกสาร','fa-cloud-arrow-up']]

};
function buildMenu(){
  const list = ME.role==='admin'?MENUS.admin : ME.role==='director'?MENUS.director : MENUS.user;
  $('#menu').innerHTML = list.map(m=>`
    <a href="#${m[0]}" class="nav-item flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 hover:bg-primary/5 hover:text-primary transition" data-p="${m[0]}">
      <i class="fa-solid ${m[2]} w-5 text-center"></i><span>${m[1]}</span></a>`).join('');
}

/* ================= ROUTER ================= */
const TITLES={dashboard:['แดชบอร์ด','ภาพรวมระบบ'],school:['ข้อมูลโรงเรียน','แก้ไขข้อมูลพื้นฐานของโรงเรียน'],
 users:['จัดการผู้ใช้งาน','เพิ่ม ลบ แก้ไขผู้ใช้งานระบบ'],years:['จัดการปีงบประมาณ','กำหนดปีงบประมาณปัจจุบัน'],
 summary:['สรุปผลการส่งเอกสาร','ภาพรวมการส่งเอกสารรายบุคคล'],profile:['ข้อมูลส่วนตัว','แก้ไขข้อมูลบัญชีของคุณ'],
 submit:['ส่งงานเอกสาร','อัปโหลดเอกสารและแนบลิงก์'],rv_pa1:['ตรวจแบบข้อตกลง PA1','ตรวจสอบและให้ผลการตรวจ'],
 rv_annual:['ตรวจแบบรายงานสิ้นปี','ตรวจสอบและให้ผลการตรวจ'],rv_present:['ตรวจไฟล์นำเสนอ','ตรวจสอบและให้ผลการตรวจ']line:['ตั้งค่าแจ้งเตือน LINE','เชื่อมต่อ LINE Messaging API'],
};

async function route(){
  let p = location.hash.replace('#','') || 'dashboard';
  const allowed = (ME.role==='admin'?MENUS.admin:ME.role==='director'?MENUS.director:MENUS.user).map(m=>m[0]);
  if(!allowed.includes(p)) p='dashboard';
  document.querySelectorAll('.nav-item').forEach(a=>a.classList.toggle('active',a.dataset.p===p));
  $('#pageTitle').textContent=TITLES[p][0]; $('#pageSub').textContent=TITLES[p][1];
  if(window.innerWidth<1024 && !$('#sidebar').classList.contains('-translate-x-full')) toggleMenu();
  window.scrollTo({top:0,behavior:'smooth'});
  ({dashboard:pageDashboard,school:pageSchool,users:pageUsers,years:pageYears,summary:pageSummary,
    profile:pageProfile,submit:pageSubmit,rv_pa1:()=>pageReview('pa1'),rv_annual:()=>pageReview('annual'),
    rv_present:()=>pageReview('present')})[p]();
    rv_present:()=>pageReview('present'), line:pageLine)[p]();

}
window.addEventListener('hashchange',route);

/* ================= DASHBOARD ================= */
const card=(t,v,ic,color)=>`
 <div class="bg-white rounded-2xl p-5 shadow-sm ring-1 ring-black/5 hover:shadow-lg transition fade">
  <div class="flex items-start justify-between">
   <div><p class="text-sm text-gray-500">${t}</p><p class="text-3xl font-extrabold mt-1" style="color:${color}">${v}</p></div>
   <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background:${color}1a;color:${color}"><i class="fa-solid ${ic} text-lg"></i></div>
  </div></div>`;

async function pageDashboard(){
  $('#content').innerHTML = skCards()+'<div class="mt-6">'+skTable(5)+'</div>';
  if(ME.role==='admin') return dashAdmin();
  const r = await api('dash_user',null,true);
  const d = r.docs||{};
  const box = t=>{const s=d[t];
    return `<div class="bg-white rounded-2xl p-5 shadow-sm ring-1 ring-black/5 fade">
      <div class="flex items-center justify-between mb-3">
        <p class="font-semibold text-gray-700">${TYPE_TH[t]}</p>${statusBadge(s?s.status:null)}</div>
      <div class="flex gap-2">${fileBtn(s?s.file_path:null)}${linkBtn(s?s.link_url:null)}</div>
      ${s&&s.status==='rejected'?`<p class="mt-3 text-xs text-[#D32F2F] bg-[#EF5350]/10 rounded-lg p-2"><i class="fa-solid fa-triangle-exclamation"></i> ${esc(s.reason)}</p>`:''}
    </div>`};
  let extra='';
  if(ME.role==='director' && r.extra){
    const e=r.extra, tot=e.total_user;
    extra = `<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
      ${card('ผู้ใต้บังคับบัญชา',tot,'fa-users','#4A2C6D')}
      ${['pa1','annual','present'].map(t=>card(TYPE_TH[t]+' (รอตรวจ)',e[t].p||0,'fa-hourglass-half','#F57C00')).join('')}
    </div>`;
  }
  $('#content').innerHTML = `
   <div class="mb-6 bg-gradient-to-r from-primary to-primary-light rounded-2xl p-6 text-white shadow-lg shadow-primary/20 fade">
     <p class="text-white/80 text-sm">ยินดีต้อนรับ</p>
     <h2 class="text-2xl font-bold">${esc(ME.fullname)}</h2>
     <p class="text-white/80 text-sm mt-1"><i class="fa-regular fa-calendar"></i> ปีงบประมาณปัจจุบัน: <b>${esc(r.year)}</b></p>
   </div>
   ${extra}
   <h3 class="font-semibold text-gray-700 mb-3">สถานะการส่งเอกสารของคุณ</h3>
   <div class="grid gap-4 md:grid-cols-3">${['pa1','annual','present'].map(box).join('')}</div>`;
}

let chartRef=null;
async function dashAdmin(){
  const yr = await api('years',null,true);
  const cur = yr.current;
  $('#content').innerHTML = `
   <div class="bg-white rounded-2xl p-4 shadow-sm ring-1 ring-black/5 mb-6 flex flex-wrap items-center gap-3 fade">
     <label class="text-sm font-medium text-gray-600"><i class="fa-solid fa-filter text-primary"></i> กรองปีงบประมาณ</label>
     <select id="fy" class="px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm">
       <option value="">— ทุกปีงบประมาณ —</option>
       ${yr.data.map(y=>`<option value="${y.id}" ${y.id==cur?'selected':''}>ปี ${esc(y.year_name)}${y.is_current==1?' (ปัจจุบัน)':''}</option>`).join('')}
     </select>
   </div>
   <div id="cards">${skCards(5)}</div>
   <div class="bg-white rounded-2xl p-5 shadow-sm ring-1 ring-black/5 mt-6">
     <h3 class="font-semibold text-gray-700 mb-4"><i class="fa-solid fa-chart-column text-primary"></i> สถิติการส่งงานแยกตามปีงบประมาณ</h3>
     <div class="relative h-80"><canvas id="chart"></canvas></div>
   </div>`;
  $('#fy').addEventListener('change',loadDash);
  loadDash();
}
async function loadDash(){
  const y = $('#fy').value;
  $('#cards').innerHTML = skCards(5);
  const r = await api('dash_admin',{year_id:y},true);
  const c = r.cards;
  $('#cards').innerHTML = `<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    ${card('ครูทั้งหมด',c.teachers,'fa-chalkboard-user','#0288D1')}
    ${card('รองฯผู้อำนวยการ',c.deputies,'fa-user-tie','#4A2C6D')}
    ${card('ส่งแบบข้อตกลง PA1',c.pa1,'fa-file-signature','#26A69A')}
    ${card('ส่งแบบรายงานสิ้นปี',c.annual,'fa-file-invoice','#F57C00')}
    ${card('ส่งไฟล์นำเสนอ',c.present,'fa-file-powerpoint','#AE2433')}
  </div>`;
  if(chartRef) chartRef.destroy();
  chartRef = new Chart($('#chart'),{type:'bar',data:{labels:r.chart.labels,datasets:[
    {label:'แบบข้อตกลง PA1',data:r.chart.pa1,backgroundColor:'#79131D',borderRadius:8,maxBarThickness:42},
    {label:'แบบรายงานสิ้นปี',data:r.chart.annual,backgroundColor:'#FFCA28',borderRadius:8,maxBarThickness:42},
    {label:'ไฟล์นำเสนอ',data:r.chart.present,backgroundColor:'#26A69A',borderRadius:8,maxBarThickness:42}]},
    options:{responsive:true,maintainAspectRatio:false,
      plugins:{legend:{position:'bottom',labels:{font:{family:'Sarabun',size:13},usePointStyle:true,padding:18}}},
      scales:{y:{beginAtZero:true,ticks:{precision:0,font:{family:'Sarabun'}},grid:{color:'#f1f1f1'}},
              x:{ticks:{font:{family:'Sarabun'}},grid:{display:false}}}}});
}

/* ================= ข้อมูลโรงเรียน ================= */
async function pageSchool(){
  $('#content').innerHTML = skTable(5);
  const r = await api('school_get',null,true); const d=r.data;
  $('#content').innerHTML = `
   <form id="fSchool" class="bg-white rounded-2xl p-6 shadow-sm ring-1 ring-black/5 max-w-3xl fade space-y-5">
     <div class="flex flex-col items-center gap-3">
       <div class="w-32 h-32 rounded-2xl ring-4 ring-primary/10 bg-gray-50 flex items-center justify-center overflow-hidden">
         <img id="pvLogo" src="${d.logo?esc(d.logo):'https://ui-avatars.com/api/?name=S&background=79131D&color=fff'}" class="w-full h-full object-contain">
       </div>
       <label class="cursor-pointer px-4 py-2 rounded-xl bg-primary/10 text-primary text-sm font-medium hover:bg-primary hover:text-white transition">
         <i class="fa-solid fa-image"></i> เลือกโลโก้โรงเรียน
         <input type="file" name="logo" accept="image/*" class="hidden" onchange="preview(this,'pvLogo')"></label>
     </div>
     <div><label class="text-sm font-medium text-gray-600">ชื่อโรงเรียน</label>
       <input name="school_name" required value="${esc(d.school_name)}" class="mt-1 w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"></div>
     <div><label class="text-sm font-medium text-gray-600">ชื่อสังกัด</label>
       <input name="affiliation" required value="${esc(d.affiliation)}" class="mt-1 w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"></div>
     <button class="px-6 py-3 rounded-xl bg-gradient-to-r from-primary to-primary-light text-white font-semibold shadow-lg shadow-primary/25"><i class="fa-solid fa-floppy-disk"></i> บันทึกข้อมูล</button>
   </form>`;
  $('#fSchool').onsubmit = async e=>{ e.preventDefault();
    const r = await api('school_save', new FormData(e.target));
    r.ok ? Swal.fire({icon:'success',title:'บันทึกสำเร็จ',confirmButtonColor:'#79131D'}).then(()=>location.reload())
         : toast('error',r.msg); };
}
function preview(inp,id){ if(inp.files[0]) $('#'+id).src = URL.createObjectURL(inp.files[0]); }

/* ================= จัดการผู้ใช้งาน ================= */
async function pageUsers(){
  $('#content').innerHTML = skTable();
  $('#content').innerHTML = `
   <div class="flex flex-wrap gap-3 items-center justify-between mb-5 fade">
     <div class="flex flex-wrap gap-2">
       <input id="uq" placeholder="ค้นหาชื่อ-นามสกุล..." class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm w-56">
       <select id="ur" class="px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm outline-none focus:border-primary">
         <option value="">ทุกสิทธิ์</option><option value="director">ผู้อำนวยการ</option>
         <option value="deputy">รองฯผู้อำนวยการ</option><option value="teacher">ครู</option></select>
     </div>
     <button onclick="userModal()" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary to-primary-light text-white text-sm font-semibold shadow-lg shadow-primary/25"><i class="fa-solid fa-plus"></i> เพิ่มผู้ใช้งาน</button>
   </div>
   <div id="uList"></div>`;
  $('#uq').oninput = debounce(loadUsers,350); $('#ur').onchange = loadUsers;
  loadUsers();
}
function debounce(fn,ms){let t;return(...a)=>{clearTimeout(t);t=setTimeout(()=>fn(...a),ms)}}
async function loadUsers(){
  $('#uList').innerHTML = skTable();
  const r = await api('users',{q:$('#uq').value,role:$('#ur').value},true);
  $('#uList').innerHTML = `
   <div class="bg-white rounded-2xl shadow-sm ring-1 ring-black/5 overflow-hidden fade">
    <div class="overflow-x-auto"><table class="w-full text-sm min-w-[720px]">
     <thead class="bg-gradient-to-r from-primary to-primary-light text-white">
      <tr><th class="px-4 py-3 text-left w-16">ลำดับ</th><th class="px-4 py-3 text-left">ผู้ใช้งาน</th>
      <th class="px-4 py-3 text-left">Username</th><th class="px-4 py-3 text-left">สิทธิ์</th>
      <th class="px-4 py-3 text-center w-32">จัดการ</th></tr></thead>
     <tbody class="divide-y divide-gray-100">
      ${r.data.length?r.data.map((u,i)=>`<tr class="hover:bg-primary/5 transition">
        <td class="px-4 py-3 text-gray-400">${i+1}</td>
        <td class="px-4 py-3"><div class="flex items-center gap-3">
          <img loading="lazy" src="${u.avatar?esc(u.avatar):'https://ui-avatars.com/api/?name='+encodeURIComponent(u.fullname)+'&background=AE2433&color=fff'}" class="w-9 h-9 rounded-full object-cover">
          <span class="font-medium text-gray-700">${esc(u.fullname)}</span></div></td>
        <td class="px-4 py-3 text-gray-500">${esc(u.username)}</td>
        <td class="px-4 py-3">${roleBadge(u.role)}</td>
        <td class="px-4 py-3 text-center whitespace-nowrap">
          <button onclick='userModal(${JSON.stringify(u)})' class="w-9 h-9 rounded-lg bg-[#0288D1]/10 text-[#0288D1] hover:bg-[#0288D1] hover:text-white transition"><i class="fa-solid fa-pen"></i></button>
          <button onclick="delUser(${u.id},'${esc(u.fullname)}')" class="w-9 h-9 rounded-lg bg-[#EF5350]/10 text-[#D32F2F] hover:bg-[#EF5350] hover:text-white transition"><i class="fa-solid fa-trash"></i></button>
        </td></tr>`).join('')
      :`<tr><td colspan="5" class="py-14 text-center text-gray-400"><i class="fa-regular fa-folder-open text-4xl mb-2 block"></i>ไม่พบข้อมูลผู้ใช้งาน</td></tr>`}
     </tbody></table></div></div>`;
}
function userModal(u=null){
  openModal(`
   <form id="fUser" class="p-6 space-y-4">
    <div class="flex items-center justify-between mb-2">
      <h3 class="text-lg font-bold text-gray-800">${u?'แก้ไขผู้ใช้งาน':'เพิ่มผู้ใช้งาน'}</h3>
      <button type="button" onclick="closeModal()" class="w-9 h-9 rounded-lg hover:bg-gray-100 text-gray-400"><i class="fa-solid fa-xmark"></i></button></div>
    <input type="hidden" name="id" value="${u?u.id:''}">
    <div class="flex flex-col items-center gap-2">
      <img id="pvA" src="${u&&u.avatar?esc(u.avatar):'https://ui-avatars.com/api/?name='+encodeURIComponent(u?u.fullname:'U')+'&background=79131D&color=fff'}" class="w-24 h-24 rounded-full object-cover ring-4 ring-primary/10">
      <label class="cursor-pointer text-xs px-3 py-1.5 rounded-lg bg-primary/10 text-primary font-medium hover:bg-primary hover:text-white transition">
        <i class="fa-solid fa-camera"></i> รูปโปรไฟล์<input type="file" name="avatar" accept="image/*" class="hidden" onchange="preview(this,'pvA')"></label>
    </div>
    <div><label class="text-sm font-medium text-gray-600">ชื่อ-นามสกุล</label>
      <input name="fullname" required value="${u?esc(u.fullname):''}" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"></div>
    <div class="grid sm:grid-cols-2 gap-3">
      <div><label class="text-sm font-medium text-gray-600">Username</label>
        <input name="username" required value="${u?esc(u.username):''}" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"></div>
      <div><label class="text-sm font-medium text-gray-600">รหัสผ่าน ${u?'<span class="text-gray-400 text-xs">(เว้นว่าง = ไม่เปลี่ยน)</span>':''}</label>
        <input name="password" type="password" ${u?'':'required'} class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"></div>
    </div>
    <div><label class="text-sm font-medium text-gray-600">สิทธิ์การใช้งาน</label>
      <select name="role" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 outline-none focus:border-primary">
        <option value="director" ${u&&u.role==='director'?'selected':''}>ผู้อำนวยการ</option>
        <option value="deputy" ${u&&u.role==='deputy'?'selected':''}>รองฯผู้อำนวยการ</option>
        <option value="teacher" ${!u||u.role==='teacher'?'selected':''}>ครู</option></select></div>
    <div class="flex gap-2 pt-2">
      <button class="flex-1 py-3 rounded-xl bg-gradient-to-r from-primary to-primary-light text-white font-semibold"><i class="fa-solid fa-floppy-disk"></i> บันทึก</button>
      <button type="button" onclick="closeModal()" class="px-5 py-3 rounded-xl bg-gray-100 text-gray-600 font-medium">ยกเลิก</button>
    </div></form>`);
  $('#fUser').onsubmit = async e=>{ e.preventDefault();
    const r = await api('user_save', new FormData(e.target));
    if(r.ok){ closeModal(); toast('success','บันทึกข้อมูลสำเร็จ'); loadUsers(); }
    else Swal.fire({icon:'warning',title:'ไม่สามารถบันทึกได้',text:r.msg,confirmButtonColor:'#79131D'}); };
}
function delUser(id,name){
  Swal.fire({title:'ยืนยันการลบ?',html:`ต้องการลบผู้ใช้ <b>${name}</b> ใช่หรือไม่<br><span class="text-xs text-gray-400">ข้อมูลการส่งงานจะถูกลบด้วย</span>`,
    icon:'warning',showCancelButton:true,confirmButtonColor:'#D32F2F',cancelButtonColor:'#9ca3af',
    confirmButtonText:'ลบข้อมูล',cancelButtonText:'ยกเลิก'})
  .then(async r=>{ if(r.isConfirmed){ await api('user_delete',{id}); toast('success','ลบข้อมูลเรียบร้อย'); loadUsers(); }});
}

/* ================= ปีงบประมาณ ================= */
async function pageYears(){
  $('#content').innerHTML = skTable();
  const r = await api('years',null,true);
  $('#content').innerHTML = `
   <div class="flex justify-end mb-5 fade">
     <button onclick="yearModal()" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary to-primary-light text-white text-sm font-semibold shadow-lg shadow-primary/25"><i class="fa-solid fa-plus"></i> เพิ่มปีงบประมาณ</button></div>
   <div class="bg-white rounded-2xl shadow-sm ring-1 ring-black/5 overflow-hidden fade">
    <div class="overflow-x-auto"><table class="w-full text-sm min-w-[560px]">
     <thead class="bg-gradient-to-r from-primary to-primary-light text-white">
      <tr><th class="px-4 py-3 text-left w-16">ลำดับ</th><th class="px-4 py-3 text-left">ปีงบประมาณ</th>
      <th class="px-4 py-3 text-left">สถานะ</th><th class="px-4 py-3 text-center w-44">จัดการ</th></tr></thead>
     <tbody class="divide-y divide-gray-100">
      ${r.data.map((y,i)=>`<tr class="hover:bg-primary/5">
        <td class="px-4 py-3 text-gray-400">${i+1}</td>
        <td class="px-4 py-3 font-semibold text-gray-700">ปี ${esc(y.year_name)}</td>
        <td class="px-4 py-3">${y.is_current==1
          ?'<span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#26A69A]/15 text-[#26A69A] ring-1 ring-[#26A69A]/30"><i class="fa-solid fa-star"></i> ปีปัจจุบัน</span>'
          :`<button onclick="setCur(${y.id})" class="px-3 py-1 rounded-full text-xs bg-gray-100 text-gray-500 hover:bg-[#26A69A] hover:text-white transition">กำหนดเป็นปีปัจจุบัน</button>`}</td>
        <td class="px-4 py-3 text-center whitespace-nowrap">
          <button onclick='yearModal(${JSON.stringify(y)})' class="w-9 h-9 rounded-lg bg-[#0288D1]/10 text-[#0288D1] hover:bg-[#0288D1] hover:text-white transition"><i class="fa-solid fa-pen"></i></button>
          <button onclick="delYear(${y.id},'${esc(y.year_name)}')" class="w-9 h-9 rounded-lg bg-[#EF5350]/10 text-[#D32F2F] hover:bg-[#EF5350] hover:text-white transition"><i class="fa-solid fa-trash"></i></button>
        </td></tr>`).join('')}
     </tbody></table></div></div>`;
}
function yearModal(y=null){
  openModal(`<form id="fY" class="p-6 space-y-4">
    <h3 class="text-lg font-bold text-gray-800">${y?'แก้ไขปีงบประมาณ':'เพิ่มปีงบประมาณ'}</h3>
    <input type="hidden" name="id" value="${y?y.id:''}">
    <div><label class="text-sm font-medium text-gray-600">ชื่อปีงบประมาณ</label>
      <input name="year_name" required value="${y?esc(y.year_name):''}" placeholder="เช่น 2569" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"></div>
    <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_current" value="1" ${y&&y.is_current==1?'checked':''} class="w-4 h-4 accent-[#79131D]"> กำหนดเป็นปีงบประมาณปัจจุบัน</label>
    <div class="flex gap-2 pt-2">
      <button class="flex-1 py-3 rounded-xl bg-gradient-to-r from-primary to-primary-light text-white font-semibold">บันทึก</button>
      <button type="button" onclick="closeModal()" class="px-5 py-3 rounded-xl bg-gray-100 text-gray-600">ยกเลิก</button></div></form>`);
  $('#fY').onsubmit = async e=>{ e.preventDefault();
    const r = await api('year_save', new FormData(e.target));
    if(r.ok){ closeModal(); toast('success','บันทึกสำเร็จ'); pageYears(); } else toast('error',r.msg); };
}
async function setCur(id){ await api('year_current',{id}); toast('success','กำหนดปีปัจจุบันแล้ว'); pageYears(); }
function delYear(id,n){
  Swal.fire({title:'ยืนยันการลบ?',html:`ลบปีงบประมาณ <b>${n}</b>?<br><span class="text-xs text-gray-400">ข้อมูลการส่งงานในปีนี้จะถูกลบด้วย</span>`,
    icon:'warning',showCancelButton:true,confirmButtonColor:'#D32F2F',confirmButtonText:'ลบ',cancelButtonText:'ยกเลิก'})
  .then(async r=>{ if(r.isConfirmed){ await api('year_delete',{id}); toast('success','ลบเรียบร้อย'); pageYears(); }});
}

/* ================= สรุปผลเอกสาร ================= */
async function pageSummary(){
  $('#content').innerHTML = skTable();
  const yr = await api('years',null,true);
  $('#content').innerHTML = `
   <div class="bg-white rounded-2xl p-4 shadow-sm ring-1 ring-black/5 mb-5 flex flex-wrap items-center gap-3 fade">
     <label class="text-sm font-medium text-gray-600"><i class="fa-solid fa-filter text-primary"></i> ปีงบประมาณ</label>
     <select id="sy" class="px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 outline-none focus:border-primary text-sm">
       ${yr.data.map(y=>`<option value="${y.id}" ${y.id==yr.current?'selected':''}>ปี ${esc(y.year_name)}</option>`).join('')}
     </select>
     <div class="flex gap-2 ml-auto">
       <button onclick="exportFile('excel')" class="px-4 py-2.5 rounded-xl bg-[#26A69A] text-white text-sm font-semibold shadow-lg shadow-[#26A69A]/25 hover:opacity-90 transition">
         <i class="fa-solid fa-file-excel"></i> Export Excel</button>
       <button onclick="exportFile('pdf')" class="px-4 py-2.5 rounded-xl bg-[#D32F2F] text-white text-sm font-semibold shadow-lg shadow-[#D32F2F]/25 hover:opacity-90 transition">
         <i class="fa-solid fa-file-pdf"></i> Export PDF</button>
     </div>
   </div>
   <div id="sList"></div>`;
  $('#sy').onchange = loadSummary; loadSummary();
}

function exportFile(t){
  const y = $('#sy').value;
  if(t==='excel'){
    loader(true);
    location.href = `export.php?type=excel&year_id=${y}`;
    setTimeout(()=>{loader(false); toast('success','กำลังดาวน์โหลดไฟล์ Excel');}, 1200);
  } else {
    Swal.fire({icon:'info',title:'พิมพ์เป็น PDF',
      html:'ระบบจะเปิดหน้ารายงาน<br>กดปุ่ม <b>บันทึกเป็น PDF</b> แล้วเลือกปลายทาง <b>Save as PDF</b>',
      confirmButtonColor:'#79131D',confirmButtonText:'เปิดรายงาน'})
    .then(r=>{ if(r.isConfirmed) window.open(`export.php?type=pdf&year_id=${y}`,'_blank'); });
  }
}
async function loadSummary(){
  $('#sList').innerHTML = skTable();
  const r = await api('summary',{year_id:$('#sy').value},true);
  const cell = d => d ? `<div class="flex flex-col items-center gap-1.5">${statusBadge(d.status)}
      <div class="flex gap-1">${fileBtn(d.file_path)}${linkBtn(d.link_url)}</div></div>`
    : `<div class="flex justify-center">${statusBadge(null)}</div>`;
  $('#sList').innerHTML = `
   <div class="bg-white rounded-2xl shadow-sm ring-1 ring-black/5 overflow-hidden fade">
    <div class="overflow-x-auto"><table class="w-full text-sm min-w-[900px]">
     <thead class="bg-gradient-to-r from-primary to-primary-light text-white">
      <tr><th class="px-4 py-3 w-16">ลำดับ</th><th class="px-4 py-3 text-left">ชื่อ-นามสกุล</th>
      <th class="px-4 py-3 text-left">ตำแหน่ง</th><th class="px-4 py-3">แบบข้อตกลง PA1</th>
      <th class="px-4 py-3">แบบรายงานสิ้นปี</th><th class="px-4 py-3">ไฟล์นำเสนอ</th></tr></thead>
     <tbody class="divide-y divide-gray-100">
      ${r.data.length?r.data.map((u,i)=>`<tr class="hover:bg-primary/5">
        <td class="px-4 py-4 text-center text-gray-400">${i+1}</td>
        <td class="px-4 py-4 font-medium text-gray-700">${esc(u.fullname)}</td>
        <td class="px-4 py-4">${roleBadge(u.role)}</td>
        <td class="px-4 py-4">${cell(u.docs.pa1)}</td>
        <td class="px-4 py-4">${cell(u.docs.annual)}</td>
        <td class="px-4 py-4">${cell(u.docs.present)}</td></tr>`).join('')
      :`<tr><td colspan="6" class="py-14 text-center text-gray-400"><i class="fa-regular fa-folder-open text-4xl mb-2 block"></i>ไม่พบข้อมูล</td></tr>`}
     </tbody></table></div></div>`;
}

/* ================= โปรไฟล์ ================= */
function pageProfile(){
  $('#content').innerHTML = `
   <form id="fP" class="bg-white rounded-2xl p-6 shadow-sm ring-1 ring-black/5 max-w-2xl fade space-y-5">
     <div class="flex flex-col items-center gap-3">
       <img id="pvP" src="${ME.avatar?esc(ME.avatar):'https://ui-avatars.com/api/?name='+encodeURIComponent(ME.fullname)+'&background=79131D&color=fff'}" class="w-28 h-28 rounded-full object-cover ring-4 ring-primary/10">
       <label class="cursor-pointer px-4 py-2 rounded-xl bg-primary/10 text-primary text-sm font-medium hover:bg-primary hover:text-white transition">
         <i class="fa-solid fa-camera"></i> เปลี่ยนรูปโปรไฟล์
         <input type="file" name="avatar" accept="image/*" class="hidden" onchange="preview(this,'pvP')"></label>
       <span class="px-3 py-1 rounded-full text-xs bg-primary/10 text-primary font-medium">${ROLE_TH[ME.role]}</span>
     </div>
     <div><label class="text-sm font-medium text-gray-600">ชื่อ-นามสกุล</label>
       <input name="fullname" required value="${esc(ME.fullname)}" class="mt-1 w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"></div>
     <div class="grid sm:grid-cols-2 gap-4">
       <div><label class="text-sm font-medium text-gray-600">Username</label>
         <input name="username" required value="${esc(ME.username)}" class="mt-1 w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"></div>
       <div><label class="text-sm font-medium text-gray-600">รหัสผ่านใหม่ <span class="text-xs text-gray-400">(เว้นว่าง = ไม่เปลี่ยน)</span></label>
         <input name="password" type="password" class="mt-1 w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"></div>
     </div>
     <button class="px-6 py-3 rounded-xl bg-gradient-to-r from-primary to-primary-light text-white font-semibold shadow-lg shadow-primary/25"><i class="fa-solid fa-floppy-disk"></i> บันทึกการแก้ไข</button>
   </form><div id="lineBox"></div>`;   loadLineBox();
}
async function loadLineBox(){
  const r = await api('line_status',null,true);
  $('#lineBox').innerHTML = `
   <div class="bg-white rounded-2xl p-6 shadow-sm ring-1 ring-black/5 max-w-2xl mt-5 fade">
     <div class="flex items-center gap-3 mb-4">
       <div class="w-11 h-11 rounded-xl bg-[#06C755]/15 text-[#06C755] flex items-center justify-center"><i class="fa-brands fa-line text-xl"></i></div>
       <div class="flex-1"><h3 class="font-bold text-gray-800">แจ้งเตือนผ่าน LINE</h3>
         <p class="text-xs text-gray-400">รับแจ้งเตือนผลการตรวจเอกสารทันที</p></div>
       ${r.linked?'<span class="px-3 py-1 rounded-full text-xs font-semibold bg-[#26A69A]/15 text-[#26A69A]"><i class="fa-solid fa-circle-check"></i> เชื่อมต่อแล้ว</span>'
                 :'<span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">ยังไม่เชื่อมต่อ</span>'}
     </div>
     ${r.linked
       ? `<button onclick="unlinkLine()" class="px-5 py-2.5 rounded-xl bg-[#EF5350]/10 text-[#D32F2F] text-sm font-medium hover:bg-[#EF5350] hover:text-white transition"><i class="fa-solid fa-link-slash"></i> ยกเลิกการเชื่อมต่อ</button>`
       : `<button onclick="genCode()" class="px-5 py-2.5 rounded-xl bg-[#06C755] text-white text-sm font-semibold shadow-lg shadow-[#06C755]/25"><i class="fa-solid fa-qrcode"></i> ขอรหัสเชื่อมต่อ</button>`}
   </div>`;
}
async function genCode(){
  const r = await api('line_code');
  Swal.fire({title:'รหัสเชื่อมต่อของคุณ',
    html:`<div style="font-size:42px;font-weight:800;letter-spacing:8px;color:#79131D;margin:12px 0">${r.code}</div>
          <p style="font-size:14px;color:#666">1. เพิ่มเพื่อน LINE OA ของโรงเรียน<br>2. พิมพ์รหัสนี้ส่งเข้าแชท<br>3. ระบบจะเชื่อมต่อให้อัตโนมัติ</p>`,
    confirmButtonColor:'#06C755',confirmButtonText:'เข้าใจแล้ว'}).then(loadLineBox);
}
function unlinkLine(){
  Swal.fire({title:'ยกเลิกการเชื่อมต่อ?',text:'คุณจะไม่ได้รับการแจ้งเตือนผ่าน LINE อีก',icon:'warning',
    showCancelButton:true,confirmButtonColor:'#D32F2F',confirmButtonText:'ยกเลิกการเชื่อมต่อ',cancelButtonText:'ไม่'})
  .then(async x=>{ if(x.isConfirmed){ await api('line_unlink'); toast('success','ยกเลิกแล้ว'); loadLineBox(); }});
}
  $('#fP').onsubmit = async e=>{ e.preventDefault();
    const r = await api('profile_save', new FormData(e.target));
    if(r.ok){ Object.assign(ME,r.user); $('#sbName').textContent=r.user.fullname;
      if(r.user.avatar) $('#sbAvatar').src = r.user.avatar+'?t='+Date.now();
      Swal.fire({icon:'success',title:'บันทึกสำเร็จ',confirmButtonColor:'#79131D'}); }
    else Swal.fire({icon:'warning',title:'ไม่สามารถบันทึกได้',text:r.msg,confirmButtonColor:'#79131D'}); };
}

/* ================= ส่งงานเอกสาร ================= */
let YEARS=[];
async function pageSubmit(){
  $('#content').innerHTML = skTable();
  const yr = await api('years',null,true); YEARS = yr.data; window.CUR_Y = yr.current;
  $('#content').innerHTML = `
   <div class="flex justify-end mb-5 fade">
     <button onclick="subModal()" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary to-primary-light text-white text-sm font-semibold shadow-lg shadow-primary/25"><i class="fa-solid fa-cloud-arrow-up"></i> ส่งงานเอกสาร</button></div>
   <div id="subList"></div>`;
  loadSubs();
}
async function loadSubs(){
  $('#subList').innerHTML = skTable();
  const r = await api('my_subs',null,true);
  $('#subList').innerHTML = `
   <div class="bg-white rounded-2xl shadow-sm ring-1 ring-black/5 overflow-hidden fade">
    <div class="overflow-x-auto"><table class="w-full text-sm min-w-[980px]">
     <thead class="bg-gradient-to-r from-primary to-primary-light text-white">
      <tr><th class="px-4 py-3 w-16">ลำดับ</th><th class="px-4 py-3 text-left">ปีงบประมาณ</th>
      <th class="px-4 py-3 text-left">ประเภทส่งงาน</th><th class="px-4 py-3">ไฟล์เอกสาร</th>
      <th class="px-4 py-3">ลิงก์แนบ</th><th class="px-4 py-3">สถานะ</th>
      <th class="px-4 py-3 text-left">เหตุผล</th><th class="px-4 py-3 w-28">จัดการ</th></tr></thead>
     <tbody class="divide-y divide-gray-100">
      ${r.data.length?r.data.map((s,i)=>`<tr class="hover:bg-primary/5">
        <td class="px-4 py-3 text-center text-gray-400">${i+1}</td>
        <td class="px-4 py-3 font-medium text-gray-700">ปี ${esc(s.year_name)}</td>
        <td class="px-4 py-3 text-gray-600">${TYPE_TH[s.doc_type]}</td>
        <td class="px-4 py-3 text-center">${fileBtn(s.file_path)}</td>
        <td class="px-4 py-3 text-center">${linkBtn(s.link_url)}</td>
        <td class="px-4 py-3 text-center">${statusBadge(s.status)}</td>
        <td class="px-4 py-3 text-xs ${s.status==='rejected'?'text-[#D32F2F]':'text-gray-300'} max-w-[220px]">${s.reason?esc(s.reason):'—'}</td>
        <td class="px-4 py-3 text-center whitespace-nowrap">
          <button onclick='subModal(${JSON.stringify(s)})' class="w-9 h-9 rounded-lg bg-[#0288D1]/10 text-[#0288D1] hover:bg-[#0288D1] hover:text-white transition"><i class="fa-solid fa-pen"></i></button>
          <button onclick="delSub(${s.id})" class="w-9 h-9 rounded-lg bg-[#EF5350]/10 text-[#D32F2F] hover:bg-[#EF5350] hover:text-white transition"><i class="fa-solid fa-trash"></i></button>
        </td></tr>`).join('')
      :`<tr><td colspan="8" class="py-14 text-center text-gray-400"><i class="fa-regular fa-folder-open text-4xl mb-2 block"></i>ยังไม่มีรายการส่งงาน</td></tr>`}
     </tbody></table></div></div>`;
}
function subModal(s=null){
  openModal(`<form id="fS" class="p-6 space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-bold text-gray-800">${s?'แก้ไขการส่งงาน':'ส่งงานเอกสาร'}</h3>
      <button type="button" onclick="closeModal()" class="w-9 h-9 rounded-lg hover:bg-gray-100 text-gray-400"><i class="fa-solid fa-xmark"></i></button></div>
    ${s&&s.status==='rejected'?`<div class="p-3 rounded-xl bg-[#EF5350]/10 text-[#D32F2F] text-sm"><i class="fa-solid fa-triangle-exclamation"></i> <b>ไม่ผ่าน:</b> ${esc(s.reason)}<br><span class="text-xs">เมื่อแก้ไขและบันทึก สถานะจะกลับเป็น "รอตรวจ" อัตโนมัติ</span></div>`:''}
    <input type="hidden" name="id" value="${s?s.id:''}">
    <div><label class="text-sm font-medium text-gray-600">ปีงบประมาณ</label>
      <select name="year_id" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 outline-none focus:border-primary">
        ${YEARS.map(y=>`<option value="${y.id}" ${s? (s.year_id==y.id?'selected':'') : (y.id==window.CUR_Y?'selected':'')}>ปี ${esc(y.year_name)}</option>`).join('')}</select></div>
    <div><label class="text-sm font-medium text-gray-600">ประเภทส่งงาน</label>
      <select name="doc_type" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 outline-none focus:border-primary">
        ${Object.keys(TYPE_TH).map(k=>`<option value="${k}" ${s&&s.doc_type===k?'selected':''}>${TYPE_TH[k]}</option>`).join('')}</select></div>
    <div><label class="text-sm font-medium text-gray-600">ไฟล์เอกสาร <span class="text-xs text-gray-400">(ไม่บังคับ)</span></label>
      <input type="file" name="file" class="mt-1 w-full text-sm file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary file:font-medium cursor-pointer border border-gray-200 rounded-xl bg-gray-50 p-2">
      ${s&&s.file_path?`<p class="text-xs text-gray-400 mt-1">ไฟล์เดิม: <a href="${esc(s.file_path)}" target="_blank" class="text-[#0288D1] underline">เปิดดู</a></p>`:''}</div>
    <div><label class="text-sm font-medium text-gray-600">ลิงก์แนบ <span class="text-xs text-gray-400">(ไม่บังคับ)</span></label>
      <input name="link_url" type="url" placeholder="https://..." value="${s&&s.link_url?esc(s.link_url):''}" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"></div>
    <div class="flex gap-2 pt-2">
      <button class="flex-1 py-3 rounded-xl bg-gradient-to-r from-primary to-primary-light text-white font-semibold"><i class="fa-solid fa-paper-plane"></i> บันทึกและส่ง</button>
      <button type="button" onclick="closeModal()" class="px-5 py-3 rounded-xl bg-gray-100 text-gray-600">ยกเลิก</button></div></form>`);
  $('#fS').onsubmit = async e=>{ e.preventDefault();
    const r = await api('sub_save', new FormData(e.target));
    if(r.ok){ closeModal(); Swal.fire({icon:'success',title:'ส่งงานสำเร็จ',text:'สถานะ: รอตรวจ',confirmButtonColor:'#79131D'}); loadSubs(); }
    else Swal.fire({icon:'warning',title:'ไม่สำเร็จ',text:r.msg,confirmButtonColor:'#79131D'}); };
}
function delSub(id){
  Swal.fire({title:'ยืนยันการลบ?',text:'ต้องการลบรายการส่งงานนี้ใช่หรือไม่',icon:'warning',showCancelButton:true,
    confirmButtonColor:'#D32F2F',confirmButtonText:'ลบ',cancelButtonText:'ยกเลิก'})
  .then(async r=>{ if(r.isConfirmed){ await api('sub_delete',{id}); toast('success','ลบเรียบร้อย'); loadSubs(); }});
}

/* ================= ตรวจงาน (ผู้อำนวยการ) ================= */
async function pageReview(type){
  $('#content').innerHTML = skTable();
  const r = await api('review_list',{doc_type:type},true);
  $('#content').innerHTML = `
   <div class="mb-5 flex items-center gap-2 fade">
     <span class="px-4 py-2 rounded-xl bg-primary/10 text-primary text-sm font-semibold"><i class="fa-regular fa-calendar"></i> ปีงบประมาณปัจจุบัน: ${esc(r.year)}</span>
     <span class="px-4 py-2 rounded-xl bg-[#4A2C6D]/10 text-[#4A2C6D] text-sm font-semibold">${TYPE_TH[type]}</span></div>
   <div class="bg-white rounded-2xl shadow-sm ring-1 ring-black/5 overflow-hidden fade">
    <div class="overflow-x-auto"><table class="w-full text-sm min-w-[1000px]">
     <thead class="bg-gradient-to-r from-primary to-primary-light text-white">
      <tr><th class="px-4 py-3 w-16">ลำดับ</th><th class="px-4 py-3 text-left">ชื่อ-นามสกุล</th>
      <th class="px-4 py-3 text-left">ตำแหน่ง</th><th class="px-4 py-3">การส่ง</th>
      <th class="px-4 py-3">ไฟล์เอกสาร</th><th class="px-4 py-3">ลิงก์แนบ</th>
      <th class="px-4 py-3 w-56">สถานะการตรวจ</th></tr></thead>
     <tbody class="divide-y divide-gray-100">
      ${r.data.map((u,i)=>{ const s=u.sub;
        return `<tr class="hover:bg-primary/5">
        <td class="px-4 py-3 text-center text-gray-400">${i+1}</td>
        <td class="px-4 py-3 font-medium text-gray-700">${esc(u.fullname)}</td>
        <td class="px-4 py-3">${roleBadge(u.role)}</td>
        <td class="px-4 py-3 text-center">${sentIcon(!!s)}</td>
        <td class="px-4 py-3 text-center">${fileBtn(s?s.file_path:null)}</td>
        <td class="px-4 py-3 text-center">${linkBtn(s?s.link_url:null)}</td>
        <td class="px-4 py-3 text-center">${s?`
          <select onchange="reviewChange(this,${s.id},'${type}')" class="w-full px-3 py-2 rounded-xl border text-xs font-semibold outline-none
            ${s.status==='approved'?'bg-[#26A69A]/10 text-[#26A69A] border-[#26A69A]/30':s.status==='rejected'?'bg-[#EF5350]/10 text-[#D32F2F] border-[#EF5350]/30':'bg-[#FFCA28]/15 text-[#F57C00] border-[#FFCA28]/40'}">
            <option value="pending" ${s.status==='pending'?'selected':''}>รอตรวจ</option>
            <option value="approved" ${s.status==='approved'?'selected':''}>ตรวจแล้ว</option>
            <option value="rejected" ${s.status==='rejected'?'selected':''}>ไม่ผ่าน</option></select>
          ${s.status==='rejected'&&s.reason?`<p class="text-[11px] text-[#D32F2F] mt-1 text-left"><i class="fa-solid fa-comment-dots"></i> ${esc(s.reason)}</p>`:''}`
          :'<span class="text-xs text-gray-300">ยังไม่ส่งงาน</span>'}</td></tr>`}).join('')}
     </tbody></table></div></div>`;
}
async function reviewChange(sel,id,type){
  const v = sel.value;
  if(v === 'rejected'){
    const {value:reason} = await Swal.fire({title:'ระบุเหตุผลที่ไม่ผ่าน',input:'textarea',
      inputPlaceholder:'กรอกเหตุผล / ข้อเสนอแนะเพื่อให้ผู้ส่งแก้ไข...',showCancelButton:true,
      confirmButtonColor:'#D32F2F',confirmButtonText:'บันทึกผลการตรวจ',cancelButtonText:'ยกเลิก',
      inputValidator:v=>!v && 'กรุณาระบุเหตุผล'});
    if(!reason){ pageReview(type); return; }
    const r = await api('review_save',{id,status:v,reason});
    r.ok?toast('success','บันทึกผลการตรวจแล้ว'):toast('error',r.msg);
  } else {
    const r = await api('review_save',{id,status:v});
    r.ok?toast('success','บันทึกผลการตรวจแล้ว'):toast('error',r.msg);
  }
  pageReview(type);
}
/* ================= ตั้งค่า LINE ================= */
async function pageLine(){
  $('#content').innerHTML = skTable();
  const r = await api('line_get',null,true);
  const base = location.href.replace(/app\.php.*$/,'');
  $('#content').innerHTML = `
   <div class="grid lg:grid-cols-3 gap-5 fade">
    <div class="lg:col-span-2 space-y-5">
      <form id="fL" class="bg-white rounded-2xl p-6 shadow-sm ring-1 ring-black/5 space-y-5">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-[#06C755]/15 text-[#06C755] flex items-center justify-center"><i class="fa-brands fa-line text-2xl"></i></div>
          <div><h3 class="font-bold text-gray-800">LINE Messaging API</h3>
          <p class="text-xs text-gray-400">ใช้แทน LINE Notify ที่ปิดบริการแล้ว</p></div>
        </div>
        <label class="flex items-center justify-between p-4 rounded-xl bg-gray-50 cursor-pointer">
          <span class="text-sm font-medium text-gray-700">เปิดใช้งานการแจ้งเตือน</span>
          <input type="checkbox" name="enable" value="1" ${r.enable==='1'?'checked':''} class="w-5 h-5 accent-[#06C755]">
        </label>
        <div><label class="text-sm font-medium text-gray-600">Channel Access Token (Long-lived)</label>
          <textarea name="token" rows="4" placeholder="วาง Token จาก LINE Developers Console"
            class="mt-1 w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-xs font-mono break-all">${esc(r.token)}</textarea></div>
        <div><label class="text-sm font-medium text-gray-600">Webhook URL <span class="text-xs text-gray-400">(นำไปใส่ใน LINE Console)</span></label>
          <div class="mt-1 flex gap-2">
            <input id="wh" readonly value="${base}webhook.php" class="flex-1 px-4 py-3 rounded-xl border border-gray-200 bg-gray-100 text-xs font-mono">
            <button type="button" onclick="navigator.clipboard.writeText($('#wh').value);toast('success','คัดลอกแล้ว')" class="px-4 rounded-xl bg-primary/10 text-primary"><i class="fa-solid fa-copy"></i></button>
          </div></div>
        <div class="flex gap-2">
          <button class="px-6 py-3 rounded-xl bg-gradient-to-r from-primary to-primary-light text-white font-semibold"><i class="fa-solid fa-floppy-disk"></i> บันทึก</button>
          <button type="button" onclick="testLine()" class="px-6 py-3 rounded-xl bg-[#06C755] text-white font-semibold"><i class="fa-solid fa-paper-plane"></i> ทดสอบส่ง</button>
        </div>
      </form>

      <div class="bg-white rounded-2xl shadow-sm ring-1 ring-black/5 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-700"><i class="fa-solid fa-clock-rotate-left text-primary"></i> ประวัติการแจ้งเตือนล่าสุด</h3></div>
        <div class="overflow-x-auto max-h-80"><table class="w-full text-sm min-w-[540px]">
         <tbody class="divide-y divide-gray-100">
          ${r.log.length?r.log.map(l=>`<tr class="hover:bg-gray-50">
            <td class="px-4 py-3 text-gray-600 w-40">${esc(l.fullname||'-')}</td>
            <td class="px-4 py-3 text-xs text-gray-400">${esc(l.created_at)}</td>
            <td class="px-4 py-3 text-right"><span class="px-3 py-1 rounded-full text-xs font-semibold ${l.status==='สำเร็จ'?'bg-[#26A69A]/15 text-[#26A69A]':'bg-[#EF5350]/15 text-[#D32F2F]'}">${esc(l.status)}</span></td>
          </tr>`).join('')
          :'<tr><td class="py-12 text-center text-gray-400">ยังไม่มีประวัติ</td></tr>'}
         </tbody></table></div>
      </div>
    </div>

    <div class="space-y-4">
      ${card('ผูกบัญชี LINE แล้ว', r.linked+' / '+r.total, 'fa-link','#06C755')}
      <div class="bg-white rounded-2xl p-5 shadow-sm ring-1 ring-black/5 text-sm text-gray-600 space-y-3">
        <h4 class="font-bold text-gray-800">📌 ขั้นตอนตั้งค่า</h4>
        <ol class="list-decimal ml-4 space-y-1.5 text-xs leading-relaxed">
          <li>สมัคร <b>LINE Developers Console</b> → สร้าง Provider</li>
          <li>สร้าง Channel ประเภท <b>Messaging API</b></li>
          <li>แท็บ Messaging API → Issue <b>Channel Access Token</b> → คัดลอกมาวางด้านซ้าย</li>
          <li>ใส่ <b>Webhook URL</b> ด้านซ้าย → เปิด Use webhook</li>
          <li>ปิด <b>Auto-reply messages</b> และ Greeting messages</li>
          <li>ให้ทุกคนสแกน QR เพิ่มเพื่อน OA แล้วผูกบัญชีที่เมนู "ข้อมูลส่วนตัว"</li>
        </ol>
        <p class="text-[11px] text-[#F57C00] bg-[#FFCA28]/15 p-2.5 rounded-lg">⚠️ Webhook ต้องเป็น HTTPS เท่านั้น หากทดสอบในเครื่องให้ใช้ ngrok</p>
      </div>
    </div>
   </div>`;
  $('#fL').onsubmit = async e=>{ e.preventDefault();
    const fd = new FormData(e.target);
    if(!fd.get('enable')) fd.set('enable','0');
    const x = await api('line_save', fd);
    x.ok ? toast('success','บันทึกการตั้งค่าแล้ว') : toast('error',x.msg); };
}
async function testLine(){
  const r = await api('line_test');
  r.ok ? Swal.fire({icon:'success',title:'ส่งสำเร็จ',text:'กรุณาตรวจสอบข้อความใน LINE',confirmButtonColor:'#79131D'})
       : Swal.fire({icon:'error',title:'ส่งไม่สำเร็จ',text:r.msg,confirmButtonColor:'#79131D'});
}
/* ================= INIT ================= */
buildMenu(); route();
</script>
</body></html>