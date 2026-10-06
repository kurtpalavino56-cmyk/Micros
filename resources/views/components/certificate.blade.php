{{--
    resources/views/components/certificate.blade.php

    The certificate, in one place. Rendered by the faculty designer, the
    create-course preview and the public scanned-certificate overlay.

    Usage:
        @include('components.certificate', ['cert' => $preview])

    SIZING
    The sheet is laid out at a FIXED 1000 x 690 and scaled to its container
    with transform: scale(). An earlier version sized every element as a
    percentage of an aspect-ratio box, which let the type grow past the
    sheet — the footer fell off the bottom and the ribbon ran through the
    title. At a fixed size the layout is verified once and simply shrinks.
--}}
@php
    $c = $cert ?? [];
    $uid = 'cert' . substr(md5(uniqid('', true)), 0, 8);
    // The credential line is always the microcredential/course name.
    // The CERTIFICATE / of Completion heading is the document type, while
    // this line identifies the actual microcredential earned.
    $credentialLine = $c['course_title'] ?? $c['certificate_title'] ?? 'Course Title';
@endphp

<link href="https://fonts.googleapis.com/css2?family=UnifrakturMaguntia&family=Inter:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500;1,600&display=swap" rel="stylesheet">

<div class="psucert" id="{{ $uid }}">
  <div class="psucert__stage">
    <div class="psucert__sheet">

      {{-- Revoked marker: gated strictly on an explicit 'status' key,
           which only CertificateBuilder::pdfData() (issued-certificate
           view/download, public verification) sets. The three faculty/
           admin PREVIEW call sites all build their $cert array without a
           'status' key, so this never appears on an unissued preview. --}}
      @if (($c['status'] ?? null) === 'revoked')
        <div class="psucert__revoked">REVOKED</div>
      @endif

      <div class="psucert__corner psucert__corner--top"></div>
      <div class="psucert__corner psucert__corner--bottom"></div>

      <div class="psucert__crest">
        <img src="{{ asset('images/PSU-Logo.png') }}" alt="Pangasinan State University">
      </div>

      <div class="psucert__qr">
        @if (!empty($c['qr_url']))
          <img src="{{ $c['qr_url'] }}" alt="Verification QR code">
        @endif
        <span>Verify</span>
      </div>

      <div class="psucert__head">
        <div class="psucert__uni">Pangasinan State University</div>
        <div class="psucert__word">CERTIFICATE</div>
        <div class="psucert__of">of Completion</div>
      </div>

      <div class="psucert__body">
        <div class="psucert__awarded">This certificate is awarded to</div>
        <div class="psucert__name">{{ $c['student_name'] ?? 'Student Name' }}</div>
        <div class="psucert__for">for successfully completing the Microcredential</div>
        <div class="psucert__course">{{ $credentialLine }}</div>
        <div class="psucert__desc">
          @if (!empty($c['course_description']))
            {{ \Illuminate\Support\Str::limit($c['course_description'], 200) }}
          @else
            This certificate acknowledges that the above-named individual has demonstrated
            proficiency in the competencies covered by this micro-credential.
          @endif
        </div>
      </div>

      <div class="psucert__foot">
        <div class="psucert__meta">
          <div class="psucert__row">
            <span class="psucert__k">Date Completed</span>
            <span class="psucert__c">:</span>
            <span class="psucert__v">{{ $c['date_completed'] ?? 'On completion' }}</span>
          </div>
          <div class="psucert__row">
            <span class="psucert__k">Learning Hours</span>
            <span class="psucert__c">:</span>
            <span class="psucert__v">{{ $c['learning_hours'] ?: 'On completion' }}</span>
          </div>
          <div class="psucert__row">
            <span class="psucert__k">Credential Id</span>
            <span class="psucert__c">:</span>
            <span class="psucert__v">{{ $c['serial'] ?? 'Issued on completion' }}</span>
          </div>
        </div>

        <div class="psucert__sign">
          @if (!empty($c['signature_img']))
            <img class="psucert__sigimg" src="{{ $c['signature_img'] }}" alt="Signature">
          @elseif (!empty($c['signature_text']))
            <div class="psucert__sigtyped" style="font-family:{{ $c['signature_font'] ?? 'cursive' }};">
              {{ $c['signature_text'] }}
            </div>
          @else
            <div class="psucert__signone"></div>
          @endif
          <div class="psucert__sigrule"></div>
          <div class="psucert__signame">{{ $c['issuer_name'] ?? 'Course Publisher' }}</div>
          <div class="psucert__sigrole">{{ $c['issuer_role'] ?? 'Faculty' }}</div>
        </div>
      </div>

    </div>
  </div>
</div>

<style>
.psucert{width:100%;font-family:'Inter','Segoe UI',Arial,sans-serif;}
.psucert__stage{position:relative;width:100%;overflow:hidden;}

.psucert__sheet{
    position:absolute;top:0;left:0;
    width:1000px;height:690px;
    transform-origin:top left;
    overflow:hidden;
    background:#fff;
    box-shadow:0 10px 32px rgba(11,27,69,.14);
}
.psucert__corner{position:absolute;width:150px;height:150px;z-index:1;pointer-events:none;}
.psucert__corner--top{top:0;left:0;background:#f4b900;clip-path:polygon(0 0,100% 0,0 100%);}
.psucert__corner--top::after{position:absolute;inset:0;background:#103a78;clip-path:polygon(0 0,78% 0,0 78%);content:"";}
.psucert__corner--bottom{right:0;bottom:0;background:#f4b900;clip-path:polygon(100% 0,100% 100%,0 100%);}
.psucert__corner--bottom::after{position:absolute;inset:0;background:#103a78;clip-path:polygon(100% 22%,100% 100%,22% 100%);content:"";}
.psucert__crest{position:absolute;top:22px;left:50%;z-index:3;width:64px;height:64px;transform:translateX(-50%);}
.psucert__crest img{width:100%;height:100%;object-fit:contain;border-radius:50%;background:#fff;}
.psucert__qr{position:absolute;top:36px;right:48px;width:112px;text-align:center;z-index:3;}
.psucert__qr img{width:112px;height:112px;display:block;background:#fff;padding:2px;}
.psucert__qr span{display:block;margin-top:4px;font-size:14px;color:#16357a;}
.psucert__head{position:absolute;top:93px;left:190px;right:190px;text-align:center;z-index:2;}
.psucert__uni{font-family:'UnifrakturMaguntia',Georgia,serif;font-size:25px;color:#0f1f4d;line-height:1.1;}
.psucert__word{font-weight:800;font-size:58px;letter-spacing:.025em;color:#f4b900;line-height:1.04;margin-top:18px;}
.psucert__of{font-weight:700;font-size:29px;letter-spacing:.22em;text-transform:uppercase;color:#103a78;line-height:1.1;margin-top:3px;}
.psucert__body{position:absolute;top:229px;left:95px;right:95px;text-align:center;z-index:2;}
.psucert__awarded{font-size:18px;color:#24436f;}
.psucert__name{font-weight:800;font-size:54px;color:#103a78;line-height:1.12;margin:2px 0 8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.psucert__for{font-style:italic;font-weight:500;font-size:18px;color:#24436f;margin-bottom:7px;}
.psucert__course{font-weight:800;font-size:28px;color:#1760d5;line-height:1.25;margin-bottom:12px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.psucert__desc{font-size:15px;line-height:1.5;color:#36577f;max-width:700px;margin:0 auto;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.psucert__foot{position:absolute;left:66px;right:66px;bottom:54px;display:flex;align-items:flex-end;justify-content:space-between;gap:36px;z-index:2;}
.psucert__meta{flex:1 1 56%;min-width:0;}
.psucert__row{display:flex;align-items:baseline;line-height:1.9;}
.psucert__k{font-weight:700;font-size:13px;letter-spacing:.12em;text-transform:uppercase;color:#103a78;width:172px;flex:0 0 172px;}
.psucert__c{font-size:14px;color:#103a78;width:20px;flex:0 0 20px;}
.psucert__v{font-size:14px;color:#36577f;overflow-wrap:anywhere;}
.psucert__sign{flex:0 1 300px;text-align:center;}
.psucert__sigimg{max-height:52px;max-width:250px;margin:0 auto;display:block;}
.psucert__sigtyped{font-size:34px;color:#12225c;line-height:1.1;height:52px;display:flex;align-items:flex-end;justify-content:center;}
.psucert__signone{height:52px;}
.psucert__sigrule{border-top:2px solid #103a78;margin:4px 0 5px;}
.psucert__signame{font-size:18px;font-weight:700;color:#103a78;line-height:1.25;}
.psucert__sigrole{font-size:16px;color:#36577f;line-height:1.35;}
.psucert__revoked{position:absolute;top:50%;left:50%;z-index:10;pointer-events:none;transform:translate(-50%,-50%) rotate(-18deg);font-weight:800;font-size:64px;letter-spacing:8px;color:rgba(161,29,29,.55);border:6px solid rgba(161,29,29,.55);padding:6px 30px;}
</style>

<script>
/* Scale the fixed 1000x690 sheet to whatever width the container gives it,
   and set the stage height to match so nothing is clipped. Runs on load and
   on resize; ResizeObserver also catches sidebars opening and closing. */
(function () {
    var root  = document.getElementById('{{ $uid }}');
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
