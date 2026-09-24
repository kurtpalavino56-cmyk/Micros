<?php
    $badge = $course?->badge;
    $enabled = (bool) ($badge || old('badge_enabled', false));
    $badgeName = old('badge_name', $badge?->name ?? (($course?->title ?? 'Microcredential').' Badge'));
    $badgeLevel = old('badge_level', $badge?->badge_level ?? 'Bronze');
    $badgeDescription = old('badge_description', $badge?->description ?? '');
    $existingLogo = ($badge?->icon_url && ! str_starts_with($badge->icon_url, 'data:image/svg')) ? $badge->icon_url : null;
?>
<div class="inline-badge-builder">
    <div class="badge-settings">
        <label class="badge-toggle">
            <input type="hidden" name="badge_enabled" value="0">
            <input type="checkbox" name="badge_enabled" value="1" id="inlineBadgeEnabled" <?php if($enabled): echo 'checked'; endif; ?>>
            <span>Award a digital badge on official completion</span>
        </label>

        <div id="inlineBadgeFields" class="badge-fields" style="<?php echo \Illuminate\Support\Arr::toCssStyles(['display:none' => !$enabled]) ?>">
            <div class="badge-field-grid">
                <div class="field">
                    <label for="inlineBadgeName">Badge Name</label>
                    <input class="input" type="text" name="badge_name" id="inlineBadgeName" maxlength="120" value="<?php echo e($badgeName); ?>" placeholder="e.g. Web Development Fundamentals">
                </div>
                <div class="field">
                    <label for="inlineBadgeLevel">Badge Level</label>
                    <select class="select" name="badge_level" id="inlineBadgeLevel">
                        <?php $__currentLoopData = ['Bronze','Silver','Gold','Platinum']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lvl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($lvl); ?>" <?php if($badgeLevel === $lvl): echo 'selected'; endif; ?>><?php echo e($lvl); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
            <div class="field">
                <label for="inlineBadgeDescription">Badge Description</label>
                <textarea class="textarea" name="badge_description" id="inlineBadgeDescription" maxlength="400" rows="3" placeholder="Briefly describe what earning this badge represents."><?php echo e($badgeDescription); ?></textarea>
            </div>
            <div class="badge-logo-row">
                <div>
                    <label class="badge-logo-button">Choose Logo <input type="file" accept="image/jpeg,image/png,image/gif,image/webp" hidden onchange="inlinePickIcon(this)"></label>
                    <input type="hidden" name="badge_icon_base64" id="inlineBadgeIcon">
                    <p class="field-hint">Optional. The selected image appears in the badge.</p>
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
.badge-toggle{display:flex;align-items:center;gap:9px;font-size:13px;font-weight:700;color:#24346f;cursor:pointer}
.badge-toggle input[type=checkbox]{width:16px;height:16px;accent-color:#13176b}
.badge-fields{margin-top:16px;padding-top:16px;border-top:1px solid #e5e7eb}
.badge-field-grid{display:grid;grid-template-columns:1.3fr .7fr;gap:18px}
.badge-logo-row{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;border-top:1px solid #edf0f4;padding-top:16px}
.badge-logo-button{display:inline-flex;align-items:center;border:1px solid #bfc8d7;background:#fff;color:#26365f;border-radius:5px;padding:8px 12px;font-size:12px;font-weight:700;cursor:pointer}
.badge-logo-button:hover{border-color:#13176b;background:#f8f9fc}
.badge-preview-wrap>span{display:block;margin-bottom:5px;font-size:11px;font-weight:700;color:#667085}
.inline-badge-stage{width:150px;height:150px;display:flex;align-items:center;justify-content:center;border:1px solid #dfe4ec;background:#f8fafc;overflow:hidden}
.inline-badge-stage svg{width:142px;height:142px;display:block}
@media(max-width:760px){.badge-field-grid{grid-template-columns:1fr}.badge-logo-row{flex-direction:column}.badge-preview-wrap{align-self:flex-start}}
</style>
<script>
(function(){
    const LEVELS={
        Bronze:{shape:'circle',base:'#a45c22',mid:'#d98b46',light:'#f0b782',ring:'#7c4014',ink:'#4a2409',accent:'#ffe2c2'},
        Silver:{shape:'rosette',base:'#7d8aa2',mid:'#b8c3d4',light:'#e3e9f2',ring:'#5d6a80',ink:'#2f3949',accent:'#fff'},
        Gold:{shape:'shield',base:'#b8860b',mid:'#e3b23c',light:'#f7dc86',ring:'#8a6508',ink:'#4a3607',accent:'#fff6d8'},
        Platinum:{shape:'hex',base:'#2f3d80',mid:'#5468b8',light:'#9fb3e8',ring:'#1d2657',ink:'#151c42',accent:'#eaf0ff'}
    };
    let inlineLogoDataUrl=<?php echo json_encode($existingLogo, 15, 512) ?>;
    function wrap(text,perLine,max){const words=String(text||'').trim().split(/\s+/),lines=[];let cur='';for(const word of words){const test=cur?cur+' '+word:word;if(test.length>perLine&&cur){lines.push(cur);cur=word}else cur=test;if(lines.length===max)break}if(cur&&lines.length<max)lines.push(cur);return lines.length?lines:['']}
    function esc(t){return String(t==null?'':t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;')}
    function rosettePath(cx,cy,outer,inner,teeth){const pts=[];for(let i=0;i<teeth*2;i++){const a=Math.PI*2*i/(teeth*2)-Math.PI/2,r=i%2===0?outer:inner;pts.push((cx+r*Math.cos(a)).toFixed(1)+','+(cy+r*Math.sin(a)).toFixed(1))}return 'M'+pts.join('L')+'Z'}
    function shapePath(shape){if(shape==='shield')return 'M150,26 L262,66 L262,168 C262,232 205,272 150,296 C95,272 38,232 38,168 L38,66 Z';if(shape==='hex')return 'M150,22 L258,84 L258,208 L150,270 L42,208 L42,84 Z';if(shape==='rosette')return rosettePath(150,152,130,112,16);return null}
    window.renderBadge=function(){
        const name=document.getElementById('inlineBadgeName');const desc=document.getElementById('inlineBadgeDescription');const levelEl=document.getElementById('inlineBadgeLevel');const stage=document.getElementById('inlineBadgeStage');const hidden=document.getElementById('inlineBadgeIcon');
        if(!name||!desc||!levelEl||!stage||!hidden)return;
        const n=name.value.trim()||'Badge Name',d=desc.value.trim()||'Achievement description',level=levelEl.value||'Bronze',cfg=LEVELS[level]||LEVELS.Bronze;
        const outline=shapePath(cfg.shape);const body=outline?'<path d="'+outline+'" fill="url(#metal)" stroke="'+cfg.ring+'" stroke-width="6"/>':'<circle cx="150" cy="152" r="128" fill="url(#metal)" stroke="'+cfg.ring+'" stroke-width="6"/>';
        const inner=cfg.shape==='circle'?'<circle cx="150" cy="152" r="112" fill="none" stroke="'+cfg.accent+'" stroke-width="2" opacity=".55"/>':cfg.shape==='rosette'?'<circle cx="150" cy="152" r="102" fill="none" stroke="'+cfg.accent+'" stroke-width="2" opacity=".55"/>':'<path d="'+outline+'" fill="none" stroke="'+cfg.accent+'" stroke-width="2" opacity=".5" transform="translate(150,152) scale(.88) translate(-150,-152)"/>';
        let laurel='';if(cfg.shape==='shield'){for(let side=0;side<2;side++){const x=side?276:24;for(let i=0;i<5;i++){const y=120+i*26;laurel+='<ellipse cx="'+x+'" cy="'+y+'" rx="13" ry="7" fill="'+cfg.mid+'" opacity=".85" transform="rotate('+(side?-38:38)+' '+x+' '+y+')"/>'}}}
        const nameLines=wrap(n.toUpperCase(),16,3),descLines=wrap(d,30,2),nameStart=178-(nameLines.length-1)*11;
        const nameSvg=nameLines.map((l,i)=>'<text x="150" y="'+(nameStart+i*23)+'" text-anchor="middle" font-family="Segoe UI,Arial,sans-serif" font-size="19" font-weight="800" fill="'+cfg.ink+'">'+esc(l)+'</text>').join('');
        const descStart=nameStart+nameLines.length*23+4,descSvg=descLines.map((l,i)=>'<text x="150" y="'+(descStart+i*14)+'" text-anchor="middle" font-family="Segoe UI,Arial,sans-serif" font-size="10.5" fill="'+cfg.ink+'" opacity=".78">'+esc(l)+'</text>').join('');
        const logo=inlineLogoDataUrl?'<clipPath id="logoClip"><circle cx="150" cy="98" r="30"/></clipPath><circle cx="150" cy="98" r="32" fill="#fff" opacity=".95"/><image href="'+inlineLogoDataUrl+'" x="120" y="68" width="60" height="60" preserveAspectRatio="xMidYMid slice" clip-path="url(#logoClip)"/>':'<circle cx="150" cy="98" r="30" fill="'+cfg.accent+'" opacity=".9"/><path d="M150,80 l6.2,12.6 13.8,2 -10,9.8 2.4,13.8 -12.4,-6.6 -12.4,6.6 2.4,-13.8 -10,-9.8 13.8,-2 Z" fill="'+cfg.base+'"/>';
        const yearY=descStart+descLines.length*14+18;
        const svg='<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 320"><defs><linearGradient id="metal" x1="0" y1="0" x2=".6" y2="1"><stop offset="0%" stop-color="'+cfg.light+'"/><stop offset="45%" stop-color="'+cfg.mid+'"/><stop offset="100%" stop-color="'+cfg.base+'"/></linearGradient></defs>'+laurel+body+inner+logo+'<text x="150" y="140" text-anchor="middle" font-family="Segoe UI,Arial,sans-serif" font-size="9" font-weight="700" letter-spacing="2.4" fill="'+cfg.ink+'" opacity=".7">UPSKILL · PSU</text>'+nameSvg+descSvg+'<text x="150" y="'+yearY+'" text-anchor="middle" font-family="Segoe UI,Arial,sans-serif" font-size="17" font-weight="800" fill="'+cfg.ink+'">'+new Date().getFullYear()+'</text><text x="150" y="'+(yearY+16)+'" text-anchor="middle" font-family="Segoe UI,Arial,sans-serif" font-size="8.5" font-weight="700" letter-spacing="2" fill="'+cfg.ink+'" opacity=".65">'+esc(level.toUpperCase())+'</text></svg>';
        stage.innerHTML=svg;hidden.value='data:image/svg+xml;base64,'+btoa(unescape(encodeURIComponent(svg)));
    };
    window.inlinePickIcon=function(input){if(!input.files||!input.files[0])return;const reader=new FileReader();reader.onload=function(e){const img=new Image();img.onload=function(){const max=192;let w=img.width,h=img.height;if(w>h&&w>max){h=Math.round(h*max/w);w=max}else if(h>=w&&h>max){w=Math.round(w*max/h);h=max}const canvas=document.createElement('canvas');canvas.width=w;canvas.height=h;canvas.getContext('2d').drawImage(img,0,0,w,h);inlineLogoDataUrl=canvas.toDataURL('image/png');renderBadge()};img.src=e.target.result};reader.readAsDataURL(input.files[0])};
    const cb=document.getElementById('inlineBadgeEnabled'),fields=document.getElementById('inlineBadgeFields');
    if(cb)cb.addEventListener('change',function(){fields.style.display=this.checked?'block':'none';if(this.checked)renderBadge()});
    ['inlineBadgeName','inlineBadgeDescription','inlineBadgeLevel'].forEach(id=>{const el=document.getElementById(id);if(el){el.addEventListener('input',renderBadge);el.addEventListener('change',renderBadge)}});
    if(<?php echo e($enabled ? 'true' : 'false'); ?>)renderBadge();
})();
</script>
<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/components/inline-badge-builder.blade.php ENDPATH**/ ?>