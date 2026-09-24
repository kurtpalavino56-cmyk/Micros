
<?php
    $c = $cert ?? [];
    $uid = 'cert' . substr(md5(uniqid('', true)), 0, 8);
    // The credential line is always the microcredential/course name.
    // The CERTIFICATE / of Completion heading is the document type, while
    // this line identifies the actual microcredential earned.
    $credentialLine = $c['course_title'] ?? $c['certificate_title'] ?? 'Course Title';
?>

<link href="https://fonts.googleapis.com/css2?family=UnifrakturMaguntia&family=Playfair+Display:ital,wght@0,700;0,900;1,600&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<div class="psucert" id="<?php echo e($uid); ?>">
  <div class="psucert__stage">
    <div class="psucert__sheet">

      
      <?php if(($c['status'] ?? null) === 'revoked'): ?>
        <div class="psucert__revoked">REVOKED</div>
      <?php endif; ?>

      <div class="psucert__rule psucert__rule--gold"></div>
      <div class="psucert__rule psucert__rule--navy"></div>

      <div class="psucert__ribbon">
        <img src="<?php echo e(asset('images/PSU-Logo.png')); ?>" alt="Pangasinan State University">
      </div>

      <div class="psucert__qr">
        <?php if(!empty($c['qr_url'])): ?>
          <img src="<?php echo e($c['qr_url']); ?>" alt="Verification QR code">
        <?php endif; ?>
        <span>Verify</span>
      </div>

      
      <div class="psucert__head">
        <div class="psucert__uni">Pangasinan State University</div>
        <div class="psucert__word">CERTIFICATE</div>
        <div class="psucert__of">of Completion</div>
      </div>

      <div class="psucert__body">
        <div class="psucert__awarded">This certificate is awarded to</div>
        <div class="psucert__name"><?php echo e($c['student_name'] ?? 'Student Name'); ?></div>
        <div class="psucert__for">for successfully completing the Microcredential</div>
        <div class="psucert__course"><?php echo e($credentialLine); ?></div>
        <div class="psucert__desc">
          <?php if(!empty($c['course_description'])): ?>
            <?php echo e(\Illuminate\Support\Str::limit($c['course_description'], 200)); ?>

          <?php else: ?>
            This certificate acknowledges that the above-named individual has demonstrated
            proficiency in the competencies covered by this micro-credential.
          <?php endif; ?>
        </div>
      </div>

      <div class="psucert__foot">
        <div class="psucert__meta">
          <div class="psucert__row">
            <span class="psucert__k">Date Completed</span>
            <span class="psucert__c">:</span>
            <span class="psucert__v"><?php echo e($c['date_completed'] ?? 'On completion'); ?></span>
          </div>
          <div class="psucert__row">
            <span class="psucert__k">Learning Hours</span>
            <span class="psucert__c">:</span>
            <span class="psucert__v"><?php echo e($c['learning_hours'] ?: 'On completion'); ?></span>
          </div>
          <div class="psucert__row">
            <span class="psucert__k">Credential Id</span>
            <span class="psucert__c">:</span>
            <span class="psucert__v"><?php echo e($c['serial'] ?? 'Issued on completion'); ?></span>
          </div>
        </div>

        <div class="psucert__divider"></div>

        <div class="psucert__sign">
          <?php if(!empty($c['signature_img'])): ?>
            <img class="psucert__sigimg" src="<?php echo e($c['signature_img']); ?>" alt="Signature">
          <?php elseif(!empty($c['signature_text'])): ?>
            <div class="psucert__sigtyped" style="font-family:<?php echo e($c['signature_font'] ?? 'cursive'); ?>;">
              <?php echo e($c['signature_text']); ?>

            </div>
          <?php else: ?>
            <div class="psucert__signone"></div>
          <?php endif; ?>
          <div class="psucert__sigrule"></div>
          <div class="psucert__signame"><?php echo e($c['issuer_name'] ?? 'Course Publisher'); ?></div>
          <div class="psucert__sigrole"><?php echo e($c['issuer_role'] ?? 'Faculty'); ?></div>
        </div>
      </div>

    </div>
  </div>
</div>

<style>
.psucert{width:100%;font-family:'Inter','Segoe UI',Arial,sans-serif;}
.psucert__stage{position:relative;width:100%;overflow:hidden;}

/* Fixed design size. The script below scales this to the container. */
.psucert__sheet{
    position:absolute;top:0;left:0;
    width:1000px;height:690px;
    transform-origin:top left;
    background:#f6f8ff;
    border:14px solid transparent;
    background-image:
        linear-gradient(#f6f8ff,#f6f8ff),
        conic-gradient(from 210deg at 10% 90%,#166534 0deg,#1d4ed8 60deg,#1d4ed8 150deg,#eab308 230deg,#0f766e 300deg,#166534 360deg);
    background-origin:border-box;
    background-clip:padding-box,border-box;
    box-shadow:0 14px 40px rgba(11,58,143,.25);
}
.psucert__rule{position:absolute;pointer-events:none;}
.psucert__rule--gold{inset:14px;border:3px solid #d4a017;}
.psucert__rule--navy{inset:24px;border:2px solid #16357a;}

/* ── Ribbon: narrow and hard left, so the centred title clears it ── */
.psucert__ribbon{
    position:absolute;top:24px;left:56px;
    width:126px;height:212px;
    background:#1a52d6;
    clip-path:polygon(0 0,100% 0,100% 100%,50% 82%,0 100%);
    display:flex;align-items:flex-start;justify-content:center;
    padding-top:18px;z-index:3;
}
.psucert__ribbon img{width:96px;height:96px;object-fit:contain;border-radius:50%;background:#fff;}

.psucert__qr{position:absolute;top:56px;right:66px;width:104px;text-align:center;z-index:3;}
.psucert__qr img{width:104px;height:104px;display:block;background:#fff;padding:4px;}
.psucert__qr span{display:block;margin-top:6px;font-size:14px;color:#16357a;}

/* ── Header lane: starts right of the ribbon ── */
.psucert__head{position:absolute;top:44px;left:200px;right:190px;text-align:center;z-index:2;}
.psucert__uni{font-family:'UnifrakturMaguntia',serif;font-size:26px;color:#0f1f4d;line-height:1;}
.psucert__word{font-family:'Playfair Display',Georgia,serif;font-weight:900;font-size:52px;
    letter-spacing:.05em;color:#0f2461;line-height:1.1;margin-top:6px;}
.psucert__of{font-family:'Playfair Display',Georgia,serif;font-weight:700;font-size:30px;
    color:#d4a017;line-height:1.1;}

/* ── Body ── */
.psucert__body{position:absolute;top:198px;left:110px;right:110px;text-align:center;z-index:2;}
.psucert__awarded{font-size:18px;color:#22304f;}
.psucert__name{font-family:'Playfair Display',Georgia,serif;font-weight:700;font-size:54px;
    color:#12225c;line-height:1.12;margin:2px 0 2px;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.psucert__for{font-family:'Playfair Display',Georgia,serif;font-style:italic;font-weight:600;
    font-size:18px;color:#22304f;margin-bottom:6px;}
.psucert__course{font-weight:700;font-size:28px;color:#1a4fd6;line-height:1.25;margin-bottom:12px;
    display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.psucert__desc{font-size:15px;line-height:1.5;color:#22304f;max-width:660px;margin:0 auto;
    display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}

/* ── Footer, pinned clear of the bottom border ── */
.psucert__foot{position:absolute;left:110px;right:110px;bottom:52px;
    display:flex;align-items:flex-end;justify-content:center;gap:46px;z-index:2;}
.psucert__meta{flex:0 0 auto;}
.psucert__row{display:flex;align-items:baseline;line-height:1.9;}
.psucert__k{font-weight:700;font-size:15px;color:#12225c;width:150px;flex:0 0 150px;}
.psucert__c{font-size:15px;color:#12225c;width:18px;flex:0 0 18px;}
.psucert__v{font-size:15px;color:#12225c;white-space:nowrap;}
.psucert__divider{width:2px;align-self:stretch;background:#16357a;opacity:.45;}
.psucert__sign{flex:0 0 300px;text-align:center;}
.psucert__sigimg{max-height:52px;max-width:250px;margin:0 auto;display:block;}
.psucert__sigtyped{font-size:34px;color:#12225c;line-height:1.1;height:52px;
    display:flex;align-items:flex-end;justify-content:center;}
.psucert__signone{height:52px;}
.psucert__sigrule{border-top:2px solid #12225c;margin:4px 0 4px;}
.psucert__signame{font-family:'Playfair Display',Georgia,serif;font-size:20px;color:#12225c;line-height:1.25;}
.psucert__sigrole{font-family:'Playfair Display',Georgia,serif;font-size:18px;color:#12225c;line-height:1.25;}

.psucert__revoked{
    position:absolute;top:50%;left:50%;z-index:10;pointer-events:none;
    transform:translate(-50%,-50%) rotate(-18deg);
    font-family:'Playfair Display',Georgia,serif;font-weight:900;
    font-size:64px;letter-spacing:8px;color:rgba(161,29,29,.55);
    border:6px solid rgba(161,29,29,.55);padding:6px 30px;
}
</style>

<script>
/* Scale the fixed 1000x690 sheet to whatever width the container gives it,
   and set the stage height to match so nothing is clipped. Runs on load and
   on resize; ResizeObserver also catches sidebars opening and closing. */
(function () {
    var root  = document.getElementById('<?php echo e($uid); ?>');
    if (!root) return;
    var stage = root.querySelector('.psucert__stage');
    var sheet = root.querySelector('.psucert__sheet');
    var W = 1000, H = 690;

    function fit() {
        var w = root.clientWidth;
        if (!w) return;
        var s = w / W;
        sheet.style.transform = 'scale(' + s + ')';
        stage.style.height = (H * s) + 'px';
    }

    fit();
    window.addEventListener('resize', fit);
    if (window.ResizeObserver) new ResizeObserver(fit).observe(root);
    // Webfonts change nothing about the box, but images can arrive late.
    window.addEventListener('load', fit);
})();
</script>
<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/components/certificate.blade.php ENDPATH**/ ?>