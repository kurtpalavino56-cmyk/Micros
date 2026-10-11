@php
    $badge = $course?->badge;
    $enabled = (bool) ($badge || old('badge_enabled', false));
    $badgeName = old('badge_name', $badge?->name ?? ($course?->title ?? ''));
    $badgeDescription = old('badge_description', $badge?->description ?? '');
    $badgeIconChoice = old('badge_icon_choice', 'psu');
    $existingBadgeArtwork = null;

    if ($badge?->icon_url && str_starts_with($badge->icon_url, 'data:image/svg+xml;base64,')) {
        $existingBadgeArtwork = base64_decode(substr($badge->icon_url, strlen('data:image/svg+xml;base64,')), true) ?: null;

        if ($existingBadgeArtwork && preg_match('/data-icon-choice="(psu|technology|business|design|data|web|achievement|custom)"/', $existingBadgeArtwork, $iconChoiceMatch)) {
            $badgeIconChoice = $iconChoiceMatch[1];
        }
    }

    $badgeArtworkTemplates = [
        'Beginner' => file_get_contents(public_path('Images/badges/beginner.svg')),
        'Intermediate' => file_get_contents(public_path('Images/badges/intermediate.svg')),
        'Advanced' => file_get_contents(public_path('Images/badges/advanced.svg')),
    ];
    $psuLogoDataUrl = 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('Images/badges/psu-logo-badge.png')));
@endphp
<div class="inline-badge-builder">
    <div class="badge-settings">
        <label class="badge-toggle">
            <input type="hidden" name="badge_enabled" value="0">
            <input type="checkbox" name="badge_enabled" value="1" id="inlineBadgeEnabled" @checked($enabled)>
            <span>Award a digital badge when the microcredential is officially completed</span>
        </label>

        <div id="inlineBadgeFields" class="badge-fields" @style(['display:none' => !$enabled])>
            <div class="field">
                <label for="inlineBadgeName">Achievement name</label>
                <input class="input" type="text" name="badge_name" id="inlineBadgeName" maxlength="120" value="{{ $badgeName }}" placeholder="e.g. Web Development Fundamentals">
                <small class="field-hint">The badge design and level follow the course level above.</small>
            </div>
            <div class="field">
                <label for="inlineBadgeDescription">What this badge recognizes</label>
                <textarea class="textarea" name="badge_description" id="inlineBadgeDescription" maxlength="400" rows="3" placeholder="Describe the skills or outcomes a learner demonstrates to earn this badge.">{{ $badgeDescription }}</textarea>
            </div>
            <div class="badge-logo-row">
                <div class="badge-icon-controls">
                    <input type="hidden" name="badge_icon_base64" id="inlineBadgeIcon">
                    <input type="hidden" name="badge_icon_choice" id="inlineBadgeIconChoice" value="{{ $badgeIconChoice }}">
                    <button class="badge-logo-button" type="button" id="openBadgeIconDrawer">Choose badge icon</button>
                    <span class="badge-selected-icon-label" id="badgeSelectedIconLabel"></span>
                    <p class="field-hint">Choose the PSU logo or an icon that fits this achievement.</p>
                    <input type="file" accept="image/jpeg,image/png,image/webp" hidden id="badgeCustomIconFile">
                </div>
                <div class="badge-preview-wrap">
                    <span>Preview · <strong id="badgePreviewLevel">{{ old('level', $course->level ?? 'Beginner') }}</strong></span>
                    <div class="inline-badge-stage" id="inlineBadgeStage" aria-label="Badge preview"></div>
                </div>
            </div>
        </div>
    </div>

    <dialog class="badge-icon-drawer" id="badgeIconDrawer" aria-labelledby="badgeIconDrawerTitle">
        <div class="badge-icon-drawer__header">
            <div>
                <h3 id="badgeIconDrawerTitle">Choose a badge icon</h3>
                <p>Select the PSU logo or an icon that represents the course.</p>
            </div>
            <button type="button" class="badge-icon-drawer__close" id="closeBadgeIconDrawer" aria-label="Close icon picker">&times;</button>
        </div>
        <div class="badge-icon-options" role="group" aria-label="Available badge icons">
            <button class="badge-icon-option" type="button" data-badge-icon="psu"><span class="badge-icon-option__preview"><img src="{{ asset('Images/badges/psu-logo-badge.png') }}" alt=""></span><span>PSU logo</span></button>
            <button class="badge-icon-option" type="button" data-badge-icon="technology"><span class="badge-icon-option__preview"><svg viewBox="0 0 64 64" aria-hidden="true"><rect x="9" y="12" width="46" height="32" rx="4"/><path d="M5 51h54M23 44l-3 7m24-7 3 7M22 22l-6 6 6 6m20-12 6 6-6 6m-7-14-5 16"/></svg></span><span>Technology</span></button>
            <button class="badge-icon-option" type="button" data-badge-icon="business"><span class="badge-icon-option__preview"><svg viewBox="0 0 64 64" aria-hidden="true"><rect x="7" y="21" width="50" height="34" rx="4"/><path d="M23 21v-7h18v7M7 33h50M28 33v6h8v-6"/></svg></span><span>Business</span></button>
            <button class="badge-icon-option" type="button" data-badge-icon="design"><span class="badge-icon-option__preview"><svg viewBox="0 0 64 64" aria-hidden="true"><path d="M32 7a25 25 0 1 0 0 50h4a6 6 0 0 0 4-10 4 4 0 0 1 3-7h5a9 9 0 0 0 9-9A24 24 0 0 0 32 7Z"/><circle cx="19" cy="26" r="3"/><circle cx="29" cy="17" r="3"/><circle cx="42" cy="20" r="3"/><circle cx="47" cy="31" r="3"/></svg></span><span>Design</span></button>
            <button class="badge-icon-option" type="button" data-badge-icon="data"><span class="badge-icon-option__preview"><svg viewBox="0 0 64 64" aria-hidden="true"><path d="M10 54V34h11v20m5 0V23h11v31m5 0V11h11v43M6 54h52"/></svg></span><span>Data &amp; analytics</span></button>
            <button class="badge-icon-option" type="button" data-badge-icon="web"><span class="badge-icon-option__preview"><svg viewBox="0 0 64 64" aria-hidden="true"><path d="m23 18-14 14 14 14m18-28 14 14-14 14M37 12 27 52"/></svg></span><span>Web development</span></button>
            <button class="badge-icon-option" type="button" data-badge-icon="achievement"><span class="badge-icon-option__preview"><svg viewBox="0 0 64 64" aria-hidden="true"><path d="m32 6 8 17 19 3-14 13 4 19-17-9-17 9 4-19L5 26l19-3z"/></svg></span><span>Achievement</span></button>
        </div>
        <button class="badge-icon-upload" type="button" id="badgeIconUpload">Upload a custom icon <span>PNG, JPG, or WebP</span></button>
    </dialog>
</div>
<script>
(function(){
    const templates=@json($badgeArtworkTemplates);
    const psuLogo=@json($psuLogoDataUrl);
    const existingArtwork=@json($existingBadgeArtwork);
    const choice=document.getElementById('inlineBadgeIconChoice');
    const icons={
        psu:{label:'PSU logo'},
        technology:{label:'Technology',svg:'<rect x="9" y="12" width="46" height="32" rx="4"/><path d="M5 51h54M23 44l-3 7m24-7 3 7M22 22l-6 6 6 6m20-12 6 6-6 6m-7-14-5 16"/>'},
        business:{label:'Business',svg:'<rect x="7" y="21" width="50" height="34" rx="4"/><path d="M23 21v-7h18v7M7 33h50M28 33v6h8v-6"/>'},
        design:{label:'Design',svg:'<path d="M32 7a25 25 0 1 0 0 50h4a6 6 0 0 0 4-10 4 4 0 0 1 3-7h5a9 9 0 0 0 9-9A24 24 0 0 0 32 7Z"/><circle cx="19" cy="26" r="3"/><circle cx="29" cy="17" r="3"/><circle cx="42" cy="20" r="3"/><circle cx="47" cy="31" r="3"/>'},
        data:{label:'Data & analytics',svg:'<path d="M10 54V34h11v20m5 0V23h11v31m5 0V11h11v43M6 54h52"/>'},
        web:{label:'Web development',svg:'<path d="m23 18-14 14 14 14m18-28 14 14-14 14M37 12 27 52"/>'},
        achievement:{label:'Achievement',svg:'<path d="m32 6 8 17 19 3-14 13 4 19-17-9-17 9 4-19L5 26l19-3z"/>'},
        custom:{label:'Custom icon'}
    };
    let customIcon=null;
    const name=document.getElementById('inlineBadgeName'),description=document.getElementById('inlineBadgeDescription');
    const stage=document.getElementById('inlineBadgeStage'),output=document.getElementById('inlineBadgeIcon');
    const levelInput=document.getElementById('courseLevel'),drawer=document.getElementById('badgeIconDrawer');
    const fileInput=document.getElementById('badgeCustomIconFile');
    const safeText=(value)=>String(value??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&apos;');
    function courseLevel(){const level=(levelInput?.value||'Beginner').toLowerCase();return level==='advanced'?'Advanced':level==='intermediate'?'Intermediate':'Beginner'}
    function currentIcon(){
        const key=choice.value in icons?choice.value:'psu';
        if(key==='custom'&&customIcon)return '<image data-selected-icon="true" href="'+customIcon+'" x="156" y="57" width="88" height="88" preserveAspectRatio="xMidYMid meet"/>';
        if(key==='psu')return '<image data-selected-icon="true" href="'+psuLogo+'" x="155" y="56" width="90" height="90" preserveAspectRatio="xMidYMid meet"/>';
        return '<svg x="162" y="63" width="76" height="76" viewBox="0 0 64 64" fill="none" stroke="#123B7A" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">'+icons[key].svg+'</svg>';
    }
    function titleLines(value){
        const words=String(value||'').trim().split(/\s+/).filter(Boolean),lines=[];let line='';
        words.forEach((word)=>{const next=line?line+' '+word:word;if(next.length>20&&line){lines.push(line);line=word}else{line=next}});
        if(line)lines.push(line);
        if(lines.length>2){lines.length=2;lines[1]=lines[1].slice(0,21)+'…'}
        return lines.length?lines:['Achievement'];
    }
    function renderBadge(){
        if(!name||!description||!stage||!output)return;
        const level=courseLevel(),badgeName=name.value.trim()||document.getElementById('courseTitleInput')?.value.trim()||'Achievement';
        const source=new DOMParser().parseFromString(templates[level],'image/svg+xml').documentElement;
        const svg=document.createElementNS('http://www.w3.org/2000/svg','svg');
        svg.setAttribute('xmlns','http://www.w3.org/2000/svg');svg.setAttribute('viewBox','0 0 400 400');svg.setAttribute('role','img');
        svg.setAttribute('aria-label',level+' badge for '+badgeName);svg.setAttribute('data-icon-choice',choice.value);
        const iconBackground=level==='Beginner'?'#F7E8CC':level==='Intermediate'?'#F2CD75':'#C7D9F0';
        const wordmarkMain=level==='Intermediate'?'#FFFFFF':'#123B7A';
        const wordmarkAccent=level==='Intermediate'?'#F9C21B':level==='Beginner'?'#875400':'#005181';
        svg.insertAdjacentHTML('beforeend','<svg x="10" y="10" width="380" height="380" viewBox="0 0 400 400" aria-hidden="true"><path d="M52 153.5V116c0-4.4 2.4-8.5 6.2-10.7L185.2 34c9.4-5.4 20.9-5.4 30.3 0l126.3 71.3c3.8 2.2 6.2 6.3 6.2 10.7v37.5H52Z" fill="'+iconBackground+'"/></svg>');
        Array.from(source.children).filter((element)=>element.localName.toLowerCase()==='svg').forEach((element)=>svg.appendChild(document.importNode(element,true)));
        svg.insertAdjacentHTML('beforeend',currentIcon());
        const color=level==='Intermediate'?'#fff':'#3f4653';const lines=titleLines(badgeName),startY=lines.length===1?216:202;
        lines.forEach((line,index)=>svg.insertAdjacentHTML('beforeend','<text x="200" y="'+(startY+index*28)+'" text-anchor="middle" font-family="Arial,sans-serif" font-size="27" font-weight="700" fill="'+color+'">'+safeText(line)+'</text>'));
        svg.insertAdjacentHTML('beforeend','<text x="200" y="288" text-anchor="middle" font-family="Arial,sans-serif" font-size="20" letter-spacing=".2"><tspan font-weight="800" fill="'+wordmarkMain+'">PSU</tspan><tspan font-weight="600" fill="'+wordmarkAccent+'"> UpSkill</tspan></text><path d="M174 299H226" stroke="'+wordmarkAccent+'" stroke-width="2" stroke-linecap="round"/><image href="'+psuLogo+'" x="180" y="307" width="40" height="40" preserveAspectRatio="xMidYMid meet"/>');
        const artwork=svg.outerHTML;stage.innerHTML=artwork;
        output.value='data:image/svg+xml;base64,'+btoa(unescape(encodeURIComponent(artwork)));
        document.getElementById('badgePreviewLevel').textContent=level;
        document.getElementById('badgeSelectedIconLabel').textContent=icons[choice.value].label;
        document.querySelectorAll('[data-badge-icon]').forEach((button)=>button.setAttribute('aria-pressed',button.dataset.badgeIcon===choice.value?'true':'false'));
    }
    if(existingArtwork&&choice.value==='custom'){
        const source=new DOMParser().parseFromString(existingArtwork,'image/svg+xml');
        customIcon=source.querySelector('image[data-selected-icon="true"]')?.getAttribute('href')||null;
    }
    document.getElementById('openBadgeIconDrawer').addEventListener('click',()=>drawer.showModal());
    document.getElementById('closeBadgeIconDrawer').addEventListener('click',()=>drawer.close());
    drawer.addEventListener('click',(event)=>{if(event.target===drawer)drawer.close()});
    document.querySelectorAll('[data-badge-icon]').forEach((button)=>button.addEventListener('click',()=>{choice.value=button.dataset.badgeIcon;renderBadge();drawer.close()}));
    document.getElementById('badgeIconUpload').addEventListener('click',()=>{drawer.close();fileInput.click()});
    fileInput.addEventListener('change',()=>{
        const file=fileInput.files?.[0];if(!file)return;
        const reader=new FileReader();reader.onload=()=>{const image=new Image();image.onload=()=>{const canvas=document.createElement('canvas');canvas.width=128;canvas.height=128;const context=canvas.getContext('2d');context.clearRect(0,0,128,128);const scale=Math.min(128/image.width,128/image.height),width=image.width*scale,height=image.height*scale;context.drawImage(image,(128-width)/2,(128-height)/2,width,height);customIcon=canvas.toDataURL('image/png');choice.value='custom';renderBadge()};image.src=reader.result};reader.readAsDataURL(file);
    });
    const enabled=document.getElementById('inlineBadgeEnabled'),fields=document.getElementById('inlineBadgeFields');
    enabled.addEventListener('change',()=>{fields.style.display=enabled.checked?'block':'none';if(enabled.checked)renderBadge()});
    const courseTitleInput=document.getElementById('courseTitleInput');
    let badgeNameWasEdited=name.value.trim()!=='';
    if(!badgeNameWasEdited&&courseTitleInput){name.value=courseTitleInput.value}
    name.addEventListener('input',()=>{badgeNameWasEdited=true;renderBadge()});
    courseTitleInput?.addEventListener('input',()=>{if(!badgeNameWasEdited){name.value=courseTitleInput.value;renderBadge()}});
    description.addEventListener('input',renderBadge);levelInput?.addEventListener('change',renderBadge);
    if(@json($enabled))renderBadge();
})();
</script>
<style>
.inline-badge-builder{border:1px solid #dfe4ec;background:#fff}.badge-settings{padding:14px 16px}
.badge-toggle{display:flex;align-items:flex-start;gap:9px;font-size:13px;font-weight:700;color:#24346f;cursor:pointer;line-height:1.45}.badge-toggle input[type=checkbox]{width:16px;height:16px;margin-top:1px;accent-color:#13176b;flex:none}
.badge-fields{margin-top:16px;padding-top:16px;border-top:1px solid #e5e7eb}.badge-logo-row{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;border-top:1px solid #edf0f4;padding-top:16px}.badge-icon-controls{display:grid;justify-items:start;gap:7px}
.badge-logo-button{display:inline-flex;align-items:center;border:1px solid #bfc8d7;background:#fff;color:#26365f;border-radius:7px;padding:9px 13px;font-size:12px;font-weight:700;cursor:pointer}.badge-logo-button:hover{border-color:#13176b;background:#f8f9fc}.badge-selected-icon-label{font-size:12px;font-weight:700;color:#123b7a}
.badge-preview-wrap>span{display:block;margin-bottom:5px;font-size:11px;font-weight:700;color:#667085}.inline-badge-stage{width:220px;height:220px;display:flex;align-items:center;justify-content:center;overflow:hidden}.inline-badge-stage svg{width:100%;height:100%;display:block;filter:drop-shadow(0 5px 8px rgba(17,24,39,.12))}
.badge-icon-drawer{position:fixed;inset:0 0 0 auto;margin:0 0 0 auto;width:min(420px,100%);height:100dvh;max-height:none;border:0;border-radius:18px 0 0 18px;padding:24px;background:#fff;color:#111827;box-shadow:-14px 0 45px rgba(15,23,42,.2)}.badge-icon-drawer::backdrop{background:rgba(15,23,42,.42)}
.badge-icon-drawer__header{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:22px}.badge-icon-drawer__header h3{margin:0 0 5px;font-size:18px;color:#111827}.badge-icon-drawer__header p{margin:0;color:#6b7280;font-size:13px}.badge-icon-drawer__close{border:0;background:transparent;color:#6b7280;font-size:28px;line-height:1;cursor:pointer}
.badge-icon-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.badge-icon-option{display:grid;justify-items:center;gap:9px;padding:14px 8px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;color:#344054;font-size:12px;font-weight:700;cursor:pointer}.badge-icon-option:hover,.badge-icon-option[aria-pressed="true"]{border-color:#123b7a;background:#f2f6fc;color:#123b7a}
.badge-icon-option__preview{display:grid;place-items:center;width:54px;height:54px;border-radius:50%;background:#f5f7fa}.badge-icon-option__preview img{width:48px;height:48px;object-fit:contain}.badge-icon-option__preview svg{width:33px;height:33px;fill:none;stroke:#123b7a;stroke-width:3;stroke-linecap:round;stroke-linejoin:round}.badge-icon-option__preview svg circle{fill:#f9c21b;stroke:#123b7a}
.badge-icon-upload{display:flex;width:100%;justify-content:space-between;align-items:center;margin-top:18px;padding:13px 14px;border:1px dashed #cbd5e1;border-radius:10px;background:#fff;color:#123b7a;font-size:13px;font-weight:700;cursor:pointer}.badge-icon-upload span{font-size:11px;font-weight:500;color:#6b7280}
@media(max-width:760px){.badge-logo-row{flex-direction:column}.badge-preview-wrap{align-self:center}.badge-icon-drawer{border-radius:0;padding:20px}}
</style>
