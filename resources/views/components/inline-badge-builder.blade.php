@php
    $badge = $course?->badge;
    $enabled = (bool) ($badge || old('badge_enabled', false));
    $badgeName = old('badge_name', $badge?->name ?? ($course?->title ?? 'Microcredential'));
    $badgeDescription = old('badge_description', $badge?->description ?? '');
    $existingLogo = ($badge?->icon_url && ! str_starts_with($badge->icon_url, 'data:image/svg')) ? $badge->icon_url : null;
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
                <small class="field-hint">Use the name of the microcredential or the specific skill achievement.</small>
            </div>
            <div class="field">
                <label for="inlineBadgeDescription">What this badge recognizes</label>
                <textarea class="textarea" name="badge_description" id="inlineBadgeDescription" maxlength="400" rows="3" placeholder="Describe the skills or outcomes a learner demonstrates to earn this badge.">{{ $badgeDescription }}</textarea>
            </div>
            <div class="badge-logo-row">
                <div>
                    <label class="badge-logo-button">Choose Logo <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" hidden onchange="inlinePickIcon(this)"></label>
                    <input type="hidden" name="badge_icon_base64" id="inlineBadgeIcon">
                    <p class="field-hint">Optional. The logo appears on the badge artwork.</p>
                </div>
                <div class="badge-preview-wrap">
                    <span>Preview</span>
                    <div class="inline-badge-stage" id="inlineBadgeStage"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.inline-badge-builder{border:1px solid #dfe4ec;background:#fff}
.badge-settings{padding:14px 16px}
.badge-toggle{display:flex;align-items:flex-start;gap:9px;font-size:13px;font-weight:700;color:#24346f;cursor:pointer;line-height:1.45}
.badge-toggle input[type=checkbox]{width:16px;height:16px;margin-top:1px;accent-color:#13176b;flex:none}
.badge-fields{margin-top:16px;padding-top:16px;border-top:1px solid #e5e7eb}
.badge-logo-row{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;border-top:1px solid #edf0f4;padding-top:16px}
.badge-logo-button{display:inline-flex;align-items:center;border:1px solid #bfc8d7;background:#fff;color:#26365f;border-radius:5px;padding:8px 12px;font-size:12px;font-weight:700;cursor:pointer}
.badge-logo-button:hover{border-color:#13176b;background:#f8f9fc}
.badge-preview-wrap>span{display:block;margin-bottom:5px;font-size:11px;font-weight:700;color:#667085}
.inline-badge-stage{width:150px;height:150px;display:flex;align-items:center;justify-content:center;border:1px solid #dfe4ec;background:#f8fafc;overflow:hidden}
.inline-badge-stage svg{width:142px;height:142px;display:block}
@media(max-width:760px){.badge-logo-row{flex-direction:column}.badge-preview-wrap{align-self:flex-start}}
</style>
<script>
(function(){
    let inlineLogoDataUrl=@json($existingLogo);
    function wrap(text,perLine,max){const words=String(text||'').trim().split(/\s+/),lines=[];let cur='';for(const word of words){const test=cur?cur+' '+word:word;if(test.length>perLine&&cur){lines.push(cur);cur=word}else cur=test;if(lines.length===max)break}if(cur&&lines.length<max)lines.push(cur);return lines.length?lines:['']}
    function esc(t){return String(t==null?'':t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;')}
    window.renderBadge=function(){
        const name=document.getElementById('inlineBadgeName'),desc=document.getElementById('inlineBadgeDescription'),stage=document.getElementById('inlineBadgeStage'),hidden=document.getElementById('inlineBadgeIcon');
        if(!name||!desc||!stage||!hidden)return;
        const n=name.value.trim()||'Achievement',d=desc.value.trim()||'Verified learning achievement';
        const nameLines=wrap(n,18,3),descLines=wrap(d,30,2),nameStart=177-(nameLines.length-1)*10;
        const nameSvg=nameLines.map((line,index)=>'<text x="150" y="'+(nameStart+index*21)+'" text-anchor="middle" font-family="Arial,sans-serif" font-size="18" font-weight="800" fill="#13176b">'+esc(line)+'</text>').join('');
        const descStart=nameStart+nameLines.length*21+3;
        const descSvg=descLines.map((line,index)=>'<text x="150" y="'+(descStart+index*13)+'" text-anchor="middle" font-family="Arial,sans-serif" font-size="10" fill="#344054">'+esc(line)+'</text>').join('');
        const logo=inlineLogoDataUrl
            ? '<defs><clipPath id="logoClip"><circle cx="150" cy="91" r="27"/></clipPath></defs><circle cx="150" cy="91" r="29" fill="#fff"/><image href="'+inlineLogoDataUrl+'" x="123" y="64" width="54" height="54" preserveAspectRatio="xMidYMid slice" clip-path="url(#logoClip)"/>'
            : '<circle cx="150" cy="91" r="28" fill="#f5c518"/><path d="M150 72l5.7 11.6 12.8 1.9-9.2 9 .2 12.9-11.5-6.1-11.5 6.1.2-12.9-9.2-9 12.8-1.9z" fill="#13176b"/>';
        const svg='<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300"><circle cx="150" cy="150" r="137" fill="#fff" stroke="#13176b" stroke-width="8"/><circle cx="150" cy="150" r="125" fill="none" stroke="#f5c518" stroke-width="4"/><text x="150" y="49" text-anchor="middle" font-family="Arial,sans-serif" font-size="10" font-weight="700" letter-spacing="2" fill="#13176b">UPSKILL · PSU</text>'+logo+nameSvg+descSvg+'</svg>';
        stage.innerHTML=svg;
        hidden.value='data:image/svg+xml;base64,'+btoa(unescape(encodeURIComponent(svg)));
    };
    window.inlinePickIcon=function(input){if(!input.files||!input.files[0])return;const reader=new FileReader();reader.onload=function(e){const img=new Image();img.onload=function(){const max=192;let w=img.width,h=img.height;if(w>h&&w>max){h=Math.round(h*max/w);w=max}else if(h>=w&&h>max){w=Math.round(w*max/h);h=max}const canvas=document.createElement('canvas');canvas.width=w;canvas.height=h;canvas.getContext('2d').drawImage(img,0,0,w,h);inlineLogoDataUrl=canvas.toDataURL('image/png');renderBadge()};img.src=e.target.result};reader.readAsDataURL(input.files[0])};
    const checkbox=document.getElementById('inlineBadgeEnabled'),fields=document.getElementById('inlineBadgeFields');
    if(checkbox)checkbox.addEventListener('change',function(){fields.style.display=this.checked?'block':'none';if(this.checked)renderBadge()});
    ['inlineBadgeName','inlineBadgeDescription'].forEach(id=>{const input=document.getElementById(id);if(input)input.addEventListener('input',renderBadge)});
    if({{ $enabled ? 'true' : 'false' }})renderBadge();
})();
</script>
