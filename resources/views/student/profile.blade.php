{{--
    resources/views/student/Profile.blade.php

    Expected data from the controller, e.g.:

    return view('student.profile', [
        'user' => $user, // Auth::user() with the fields used below
        'stats' => [
            'courses_enrolled' => $coursesEnrolledCount,
            'badges_earned'    => $badgesEarnedCount,
            'certificates'     => $certificatesCount,
            'hours_learned'    => $hoursLearned,
        ],
        'progress' => [
            'completed' => $completedCoursesCount,
            'total'     => $enrolledCoursesCount,
        ],
        'achievements' => $achievements, // collection, can be empty
        'activities'   => $recentActivities, // collection, can be empty
    ]);

    $user is expected to expose:
        ->name, ->role, ->phone, ->email, ->joined_at (Carbon), ->location,
        ->avatar_url, ->about, ->date_of_birth, ->gender, ->education, ->bio,
        ->language, ->timezone

    Styling note: the page heading block (.profile-head) is intentionally
    left exactly as it was. Everything else uses the Inter-based card system.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<title>My Profile | Upskill</title>
<link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
<style>
    :root{
        /* Shared with the navigation / sidebar components */
        --navy:#13176b;
        --navy-deep:#0c0f4d;
        --gold:#dba617;
        --gold-dark:#c4930f;
        --cyan:#7fe9e3;
        --thumb:#d8e3f8;
        --ink:#13176b;
        --muted:#6b7280;
        --line:#e5e7eb;
        --shadow: 0 10px 25px rgba(19,23,107,0.08);

        /* Page tokens */
        --p-strong:#101828;
        --p-text:#344054;
        --p-muted:#667085;
        --p-faint:#98a2b3;
        --p-border:#e4e7ec;
        --p-border-input:#d0d5dd;
        --p-surface-alt:#f9fafb;
        --p-tint:#eef1fb;
        --p-accent:#172f94;
        --p-accent-hover:#112577;
        --p-focus:rgba(52,72,165,.16);
        --p-radius:8px;

        /* 60 / 30 / 10: white surfaces / blue bands / yellow accent */
        /* Header bands: sampled from the navbar */
        --p-band:#172f94;
        --p-gold:#f5c518;
        --p-gold-hover:#dba617;
    }
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI", Roboto, Helvetica, Arial, sans-serif;color:var(--ink);margin:0;background:#fff;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}

    /* ── Layout ──────────────────────────────────────────────── */
    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}
    .sidebar-progress{background:#fff;border:1px solid var(--line);border-radius:16px;padding:18px;margin-top:14px;box-shadow:var(--shadow);}
    .sidebar-progress .sp-label{color:var(--muted);font-size:13px;margin:0 0 6px;}
    .sidebar-progress .sp-value{color:var(--navy);font-size:22px;font-weight:800;margin:0 0 14px;}
    .sidebar-progress a{color:var(--navy);font-weight:700;font-size:14px;display:flex;align-items:center;gap:6px;}

    .main{min-width:0;padding:32px 36px 60px;}

    /* ── Page heading (unchanged on purpose) ─────────────────── */
    .profile-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:28px;}
    .profile-head h2{font-size:30px;margin:0 0 6px;color:var(--navy);}
    .profile-head p{margin:0;color:var(--muted);font-size:14px;}
    .btn-outline{background:#fff;border:1.5px solid var(--line);color:var(--navy);font-weight:700;padding:12px 24px;border-radius:10px;font-size:15px;}

    /* Everything below the heading, plus the drawer, toast and pickers, uses Inter */
    .profile-grid,.modal-overlay,.toast,.skillpick-pop{font-family:Inter,Arial,sans-serif;font-size:14px;line-height:1.5;color:var(--p-text);}
    .profile-grid button,.modal-overlay button,.modal-overlay input,.modal-overlay select,.modal-overlay textarea,.toast button,.skillpick-pop button,.skillpick-pop input,.profile-grid select{font-family:inherit;}

    .profile-grid{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:24px;align-items:start;}
    .profile-col,.profile-side{min-width:0;}

    /* ── Cards ───────────────────────────────────────────────── */
    .card-block{background:#fff;border:1px solid var(--p-border);border-radius:14px;box-shadow:0 1px 2px rgba(16,24,40,.04);padding:24px;margin-bottom:20px;overflow:hidden;}
    .section-title{display:flex;align-items:center;gap:10px;margin:0 0 16px;font-size:16px;font-weight:600;line-height:1.4;color:var(--p-strong);}
    .card-block>.section-title{margin:-24px -24px 20px;padding:14px 24px;background:var(--p-band);color:#fff;}
    .card-block>.section-title svg{color:#fff;}
    .section-title svg{width:20px;height:20px;flex-shrink:0;color:var(--navy);}

    /* ── Profile info card ───────────────────────────────────── */
    .card-block.profile-card{padding:0;overflow:hidden;}
    .profile-cover{height:104px;background:var(--p-band);}
    .profile-info{display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap;margin-top:-42px;padding:0 24px 24px;}
    .avatar-circle{width:96px;height:96px;border-radius:50%;border:4px solid #fff;box-shadow:0 0 0 1px var(--p-border),0 4px 12px rgba(16,24,40,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;background-color:var(--p-tint);background-size:cover;background-position:center;background-repeat:no-repeat;overflow:hidden;}
    .avatar-circle svg{width:48px;height:48px;color:var(--navy);}
    .profile-meta{flex:1 1 280px;min-width:0;padding-top:50px;}
    .profile-meta h3{margin:0 0 14px;font-size:22px;font-weight:700;line-height:1.3;letter-spacing:-.01em;color:var(--p-strong);}
    .meta-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px 24px;color:var(--p-text);font-size:14px;}
    .meta-row span{display:flex;align-items:flex-start;gap:10px;min-width:0;overflow-wrap:anywhere;}
    .meta-row svg{width:16px;height:16px;margin-top:3px;color:var(--navy);flex-shrink:0;}

    /* ── About me ────────────────────────────────────────────── */
    .about-text{margin:0 0 16px;max-width:70ch;font-size:14px;line-height:1.7;color:var(--p-text);}
    .detail-row{display:grid;grid-template-columns:150px minmax(0,1fr);gap:16px;padding:12px 0;border-top:1px solid var(--p-border);font-size:14px;}
    .detail-row:first-of-type{border-top:none;}
    .detail-row .label{color:var(--p-muted);font-weight:500;}
    .detail-row .value{color:var(--p-strong);font-weight:500;overflow-wrap:anywhere;}

    .skills-block{margin-top:12px;padding-top:18px;border-top:1px solid var(--p-border);display:flex;flex-direction:column;gap:18px;}
    .skills-title{display:block;margin-bottom:8px;font-size:13px;font-weight:600;color:var(--p-strong);}
    .skills-chips{display:flex;flex-wrap:wrap;gap:8px;}
    .skill-chip{display:inline-block;padding:4px 11px;border-radius:6px;font-size:13px;font-weight:500;line-height:1.5;}
    .skill-chip-have{background:var(--p-tint);color:var(--navy);border:1px solid #d5dbf3;}
    .skill-chip-want{background:#fff7e0;color:#8a6508;border:1px solid #f1deA0;}
    .skills-empty{font-size:13px;color:var(--p-faint);}

    /* ── Settings card ───────────────────────────────────────── */
    .settings-row{display:flex;align-items:center;gap:12px 16px;flex-wrap:wrap;padding:14px 0;border-top:1px solid var(--p-border);}
    .settings-row:first-of-type{border-top:none;padding-top:2px;}
    .settings-row:last-child{padding-bottom:0;}
    .settings-row .label{display:flex;align-items:center;gap:10px;flex:0 0 140px;font-size:14px;font-weight:600;color:var(--p-strong);}
    .settings-row .label svg{width:18px;height:18px;color:var(--navy);}
    .settings-row .value-text{flex:1;min-width:120px;font-size:14px;color:var(--p-text);overflow-wrap:anywhere;}
    .btn-change{flex-shrink:0;min-height:36px;padding:6px 16px;border:1px solid var(--p-border-input);border-radius:var(--p-radius);background:#fff;color:var(--p-text);font-size:13px;font-weight:600;transition:background .15s,border-color .15s;}
    .btn-change:hover{background:var(--p-surface-alt);border-color:var(--p-faint);}
    .settings-select{flex:1;min-width:150px;max-width:320px;min-height:40px;padding:8px 12px;border:1px solid var(--p-border-input);border-radius:var(--p-radius);background:#fff;color:var(--p-strong);font-size:14px;outline:none;}
    .settings-select:focus{border-color:#3448a5;box-shadow:0 0 0 3px var(--p-focus);}

    /* ── Right column ────────────────────────────────────────── */
    .quick-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-bottom:20px;}
    .quick-card{display:flex;flex-direction:column;align-items:flex-start;gap:12px;padding:16px;background:#fff;border:1px solid var(--p-border);border-radius:14px;box-shadow:0 1px 2px rgba(16,24,40,.04);font-size:14px;font-weight:600;line-height:1.3;color:var(--p-strong);transition:border-color .15s,box-shadow .15s;}
    .quick-card:hover{border-color:#b9c2ea;box-shadow:0 4px 14px rgba(19,23,107,.08);}
    .quick-card svg{width:38px;height:38px;padding:9px;border-radius:10px;background:var(--p-tint);color:var(--navy);flex-shrink:0;}

    .panel-card{background:#fff;border:1px solid var(--p-border);border-radius:14px;box-shadow:0 1px 2px rgba(16,24,40,.04);margin-bottom:20px;overflow:hidden;}
    .panel-card-head{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:14px 18px;background:var(--p-band);}
    .panel-card-head h4{display:flex;align-items:center;gap:8px;margin:0;font-size:15px;font-weight:600;color:#fff;}
    .panel-card-head h4 svg{width:18px;height:18px;color:#fff;}
    .panel-card-head a{font-size:13px;font-weight:600;color:#fff;opacity:.85;}
    .panel-card-head a:hover{opacity:1;}
    .panel-card-head a:hover{text-decoration:underline;}
    .panel-card-body{min-height:70px;padding:16px 18px;color:var(--p-muted);font-size:13px;line-height:1.6;}
    .panel-card-body p{margin:0 0 8px;color:var(--p-text);font-size:14px;}
    .panel-card-body p:last-child{margin-bottom:0;}

    /* Student ID */
    .student-id-body{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:0;}
    .student-id-number{font-size:22px;font-weight:600;letter-spacing:2px;color:var(--p-strong);font-variant-numeric:tabular-nums;overflow-wrap:anywhere;}
    .student-id-label{margin-top:2px;font-size:12px;color:var(--p-muted);letter-spacing:0;}
    .student-id-copy{flex-shrink:0;min-height:36px;padding:6px 16px;border:1px solid var(--p-border-input);border-radius:var(--p-radius);background:#fff;color:var(--p-text);font-size:13px;font-weight:600;transition:background-color .15s ease,color .15s ease,border-color .15s ease;}
    .student-id-copy:hover{background:var(--p-accent);border-color:var(--p-accent);color:#fff;}

    /* ── Edit Profile drawer ─────────────────────────────────── */
    .modal-overlay{position:fixed;inset:0;background:rgba(16,24,40,.5);z-index:1000;display:flex;justify-content:flex-end;opacity:0;visibility:hidden;transition:opacity .3s ease,visibility .3s ease;}
    .modal-overlay.open{opacity:1;visibility:visible;}
    .modal-panel{width:520px;max-width:95vw;height:100vh;height:100dvh;max-height:100dvh;background:#fff;display:flex;flex-direction:column;box-shadow:-12px 0 40px rgba(16,24,40,.2);transform:translateX(100%);transition:transform .38s cubic-bezier(.16,1,.3,1);overflow:hidden;}
    .modal-overlay.open .modal-panel{transform:translateX(0);}

    .modal-header{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-shrink:0;padding:20px 28px 16px;background:var(--p-band);}
    .modal-header-left{display:flex;align-items:center;gap:12px;}
    .modal-header-left svg{width:22px;height:22px;color:#fff;}
    .modal-header h3{margin:0;font-size:18px;font-weight:600;line-height:1.3;color:#fff;}
    .modal-header p{margin:2px 0 0;font-size:13px;color:rgba(255,255,255,.75);}
    .modal-close{width:36px;height:36px;flex-shrink:0;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.35);border-radius:var(--p-radius);background:transparent;color:#fff;font-size:22px;line-height:1;cursor:pointer;transition:background .15s,color .15s;}
    .modal-close:hover{background:rgba(255,255,255,.14);color:#fff;}

    .modal-tabs{display:flex;gap:4px;flex-shrink:0;padding:0 28px;background:#fff;border-bottom:1px solid var(--p-border);}
    .tab-btn{margin-bottom:-1px;padding:12px 14px;background:none;border:none;border-bottom:2px solid transparent;font-size:14px;font-weight:500;color:var(--p-muted);cursor:pointer;white-space:nowrap;transition:color .15s,border-color .15s;}
    .tab-btn.active{color:var(--navy);font-weight:600;border-bottom-color:var(--p-gold);}
    .tab-btn:hover:not(.active){color:var(--p-strong);}

    #editProfileForm{flex:1 1 auto;min-height:0;display:flex;flex-direction:column;}
    .modal-body{flex:1 1 auto;min-height:0;overflow-y:auto;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;padding:24px 28px;scrollbar-width:thin;}
    .tab-content{display:none;}
    .tab-content.active{display:block;}

    /* Avatar upload */
    .avatar-upload-area{display:flex;align-items:center;gap:18px;margin-bottom:24px;padding:16px;border:1px dashed var(--p-border-input);border-radius:12px;background:var(--p-surface-alt);}
    .avatar-preview{width:72px;height:72px;flex-shrink:0;border-radius:50%;border:3px solid #fff;box-shadow:0 0 0 1px var(--p-border);display:flex;align-items:center;justify-content:center;background-color:var(--p-tint);background-size:cover;background-position:center;overflow:hidden;}
    .avatar-preview svg{width:36px;height:36px;color:var(--navy);}
    .avatar-upload-text p{margin:0 0 10px;font-size:12px;line-height:1.55;color:var(--p-muted);}
    .btn-upload-avatar{display:inline-flex;align-items:center;gap:6px;min-height:36px;padding:7px 14px;border:1px solid var(--p-accent);border-radius:var(--p-radius);background:var(--p-accent);color:#fff;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s;}
    .btn-upload-avatar:hover{background:var(--p-accent-hover);}

    /* Form groups. Direct-child selectors keep these rules off the skill
       picker's own labels and inputs, so the picker needs no overrides. */
    .form-group{margin-bottom:18px;min-width:0;}
    .form-group>label{display:block;margin-bottom:6px;font-size:13px;font-weight:600;color:var(--p-text);}
    .form-group>input,.form-group>select,.form-group>textarea,.pwd-wrap input{
        display:block;width:100%;min-height:42px;padding:9px 13px;
        border:1px solid var(--p-border-input);border-radius:var(--p-radius);
        background:#fff;color:var(--p-strong);outline:none;font-size:14px;line-height:1.5;
        transition:border-color .15s,box-shadow .15s;
    }
    .form-group>input::placeholder,.form-group>textarea::placeholder,.pwd-wrap input::placeholder{color:var(--p-faint);}
    .form-group>input:focus,.form-group>select:focus,.form-group>textarea:focus,.pwd-wrap input:focus{border-color:#3448a5;box-shadow:0 0 0 3px var(--p-focus);}
    .form-group>textarea{resize:vertical;min-height:84px;}
    .form-group .hint{margin:6px 0 0;font-size:12px;line-height:1.5;color:var(--p-muted);}
    .form-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}
    .form-divider{border:none;border-top:1px solid var(--p-border);margin:22px 0;}
    .form-section-label{margin:0 0 14px;font-size:13px;font-weight:600;color:var(--p-strong);}

    .pwd-wrap{position:relative;}
    .pwd-wrap input{padding-right:44px;}
    .pwd-toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);display:flex;align-items:center;padding:0;background:none;border:none;color:var(--p-muted);cursor:pointer;}
    .pwd-toggle:hover{color:var(--p-strong);}
    .pwd-toggle svg{width:17px;height:17px;}

    .settings-info-box{display:flex;gap:10px;align-items:flex-start;margin-bottom:20px;padding:12px 14px;border-radius:10px;background:#f5f7ff;border:1px solid #dfe5fb;}
    .settings-info-box svg{width:16px;height:16px;margin-top:2px;color:var(--navy);flex-shrink:0;}
    .settings-info-box p{margin:0;font-size:13px;line-height:1.5;color:var(--p-text);}

    .modal-footer{display:flex;gap:10px;justify-content:flex-end;flex-shrink:0;padding:14px 28px;background:#fff;border-top:1px solid var(--p-border);}
    .btn-cancel,.btn-save{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:9px 22px;border-radius:var(--p-radius);font-size:14px;font-weight:600;cursor:pointer;transition:background .15s,border-color .15s;}
    .btn-cancel{background:#fff;border:1px solid var(--p-border-input);color:var(--p-text);}
    .btn-cancel:hover{background:var(--p-surface-alt);border-color:var(--p-faint);}
    .btn-save{background:var(--p-gold);border:1px solid var(--p-gold);color:var(--navy);font-weight:700;box-shadow:0 1px 2px rgba(16,24,40,.08);}
    .btn-save:hover{background:var(--p-gold-hover);border-color:var(--p-gold-hover);}
    .btn-save svg{width:16px;height:16px;}
    .btn-cancel:focus-visible,.btn-save:focus-visible,.btn-change:focus-visible,.student-id-copy:focus-visible,.btn-upload-avatar:focus-within,.modal-close:focus-visible,.tab-btn:focus-visible{outline:none;box-shadow:0 0 0 3px var(--p-focus);}

    /* ── Success toast ───────────────────────────────────────── */
    .toast{position:fixed;bottom:28px;right:28px;z-index:2000;display:flex;align-items:center;gap:12px;padding:13px 18px;border-radius:12px;background:var(--navy);color:#fff;box-shadow:0 8px 28px rgba(19,23,107,.28);font-size:14px;font-weight:500;transform:translateY(80px);opacity:0;transition:transform .4s cubic-bezier(.16,1,.3,1),opacity .4s ease;pointer-events:none;}
    .toast.show{transform:translateY(0);opacity:1;pointer-events:auto;}
    .toast svg{width:20px;height:20px;flex-shrink:0;color:#4ade80;}
    .toast-close{background:none;border:none;color:#fff;opacity:.7;font-size:18px;cursor:pointer;padding:0 0 0 8px;line-height:1;}
    .toast-close:hover{opacity:1;}

    /* ── Skill pickers (mini button + floating modal) ────────── */
    .skillpick{position:relative;display:block;margin-top:2px;}
    .skillpick-mini{display:inline-flex;align-items:center;gap:8px;min-height:40px;padding:8px 14px;border:1px solid var(--p-border-input);border-radius:var(--p-radius);background:#fff;color:var(--p-muted);font-size:14px;font-weight:500;cursor:pointer;transition:border-color .15s,color .15s,background .15s;}
    .skillpick-mini:hover{border-color:var(--p-faint);color:var(--p-strong);}
    .skillpick-mini svg{width:14px;height:14px;}
    .skillpick-mini.has-value{background:var(--p-accent);border-color:var(--p-accent);color:#fff;}

    .skillpick-backdrop{position:fixed;inset:0;z-index:1300;background:rgba(16,24,40,.5);}
    .skillpick-backdrop[hidden]{display:none;}
    .skillpick-pop{position:fixed;z-index:1301;top:50%;left:50%;transform:translate(-50%,-50%);width:min(480px,calc(100vw - 32px));max-height:min(82vh,640px);display:flex;flex-direction:column;padding:22px;background:#fff;border:1px solid var(--p-border);border-radius:14px;box-shadow:0 24px 56px rgba(16,24,40,.28);}
    .skillpick-pop[hidden]{display:none;}
    .skillpick-hd{margin-bottom:14px;flex-shrink:0;}
    .skillpick-hd strong{display:block;font-size:16px;font-weight:600;color:var(--p-strong);}
    .skillpick-hd span{display:block;margin-top:2px;font-size:13px;line-height:1.4;color:var(--p-muted);}

    .skillpick-search{position:relative;margin-bottom:10px;flex-shrink:0;}
    .skillpick-search svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:var(--p-muted);}
    .skillpick-search input{display:block;width:100%;min-height:42px;padding:9px 14px 9px 34px;border:1px solid var(--p-border-input);border-radius:var(--p-radius);color:var(--p-strong);font-size:14px;outline:none;}
    .skillpick-search input:focus{border-color:#3448a5;box-shadow:0 0 0 3px var(--p-focus);}

    .skillpick-count{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px;flex-shrink:0;font-size:12px;color:var(--p-muted);}
    .skillpick-clearall{padding:0;border:none;background:transparent;color:var(--p-accent);font-size:12px;font-weight:600;cursor:pointer;}
    .skillpick-clearall:hover{text-decoration:underline;}

    .skillpick-list{flex:1 1 auto;min-height:0;overflow-y:auto;display:flex;flex-direction:column;gap:6px;padding:2px 2px 4px;}
    .skillpick-group[hidden]{display:none;}
    .skillpick-group-hd{display:block;margin:14px 0 6px 4px;font-size:12px;font-weight:600;color:var(--p-muted);}
    .skillpick-group:first-child .skillpick-group-hd{margin-top:2px;}
    .skillpick-empty{padding:18px 6px;text-align:center;color:var(--p-muted);font-size:13px;}

    .skillpick-opt{position:relative;display:flex;align-items:center;gap:12px;margin-bottom:6px;padding:10px 14px;border:1px solid var(--p-border);border-radius:var(--p-radius);background:#fff;color:var(--p-text);font-size:14px;cursor:pointer;transition:border-color .15s,background .15s;}
    .skillpick-opt:hover{border-color:var(--p-faint);}
    .skillpick-opt[hidden]{display:none;}
    .skillpick-opt input{position:absolute;opacity:0;pointer-events:none;}
    .skillpick-tick{width:18px;height:18px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center;border:1.5px solid var(--p-border-input);border-radius:5px;background:#fff;transition:background .15s,border-color .15s;}
    .skillpick-tick svg{width:11px;height:11px;color:#fff;opacity:0;}
    .skillpick-opt.on{border-color:#aab4e6;background:#f5f7ff;}
    .skillpick-opt.on .skillpick-tick{background:var(--p-accent);border-color:var(--p-accent);}
    .skillpick-opt.on .skillpick-tick svg{opacity:1;}
    .skillpick-opt input:focus-visible+.skillpick-tick{box-shadow:0 0 0 3px var(--p-focus);}
    .skillpick-name{font-weight:500;}

    .skillpick-ft{display:flex;gap:8px;justify-content:flex-end;flex-shrink:0;margin-top:14px;padding-top:14px;border-top:1px solid var(--p-border);}
    .skillpick-btn{min-height:40px;padding:8px 16px;border:1px solid var(--p-border-input);border-radius:var(--p-radius);background:#fff;color:var(--p-text);font-size:14px;font-weight:600;cursor:pointer;}
    .skillpick-btn:hover{background:var(--p-surface-alt);}
    .skillpick-btn.primary{background:var(--p-accent);border-color:var(--p-accent);color:#fff;}
    .skillpick-btn.primary:hover{background:var(--p-accent-hover);}

    /* ── Back to top ─────────────────────────────────────────── */
    #back-to-top-btn{position:fixed;right:26px;bottom:26px;z-index:2000;width:44px;height:44px;border-radius:12px;border:none;background:#13176b;color:#fff;display:none;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 8px 20px rgba(19,23,107,.28);transition:transform .15s ease,background .2s ease;}
    #back-to-top-btn:hover{background:#dba617;transform:translateY(-2px);}
    #back-to-top-btn svg{width:20px;height:20px;}

    /* ── Responsive ──────────────────────────────────────────── */
    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .profile-grid{grid-template-columns:1fr;}
    }
    @media (max-width:640px){
        .card-block{padding:18px;}
        .card-block>.section-title{margin:-18px -18px 18px;padding:12px 18px;}
        .detail-row{grid-template-columns:1fr;gap:2px;}
        .settings-row .label{flex-basis:100%;}
        .settings-select{max-width:none;}
        .modal-panel{width:100%;max-width:100%;}
        .modal-header{padding:16px 18px 12px;}
        .modal-tabs{padding:0 18px;overflow-x:auto;}
        .modal-body{padding:18px;}
        .modal-footer{padding:14px 18px;}
        .form-row{grid-template-columns:1fr;gap:0;}
    }
    @media (max-width:520px){
        .profile-meta{padding-top:6px;}
        .skillpick-pop{padding:16px;border-radius:12px;}
    }
    @media (prefers-reduced-motion:reduce){
        *{transition:none!important;}
    }
</style>
</head>
<body>

{{-- ===== SUCCESS / ERROR TOAST ===== --}}
@if (session('success'))
<div class="toast" id="successToast">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    {{ session('success') }}
    <button class="toast-close" onclick="document.getElementById('successToast').classList.remove('show')">&times;</button>
</div>
@endif

@include('components.student-navigation')

<div class="layout student-sidebar-layout">

    {{-- Sidebar --}}
    @include('components.student-sidebar')

    {{-- Main content --}}
    <main class="main">

        <div class="profile-head student-page-heading">
            <div>
                <h2>My Profile</h2>
                <p>Manage your Personal Information and Account Preferences</p>
            </div>
            <button class="btn-outline" type="button" onclick="openEditModal('personal')">Edit Profile</button>
        </div>

        <div class="profile-grid">

            {{-- Left column --}}
            <div class="profile-col">

                {{-- Profile info card --}}
                <div class="card-block profile-card">
                    <div class="profile-cover" aria-hidden="true"></div>
                    <div class="profile-info">
                        <div class="avatar-circle" @if($user->avatar_url ?? null) style="background-image:url('{{ $user->avatar_url }}')" @endif>
                            @unless($user->avatar_url ?? null)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                            @endunless
                        </div>
                        <div class="profile-meta">
                            <h3>{{ $user->name ?? 'Student' }}</h3>
                            <div class="meta-row">
                                @if($user->role ?? null)
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/><path d="M6 12v5c0 1 3 3 6 3s6-2 6-3v-5"/></svg>
                                        {{ $user->role }}
                                    </span>
                                @endif
                                @if($user->phone ?? null)
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.8a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.84.57 2.8.7A2 2 0 0 1 22 16.92z"/></svg>
                                        {{ $user->phone }}
                                    </span>
                                @endif
                                @if($user->email ?? null)
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 6 10-6"/></svg>
                                        {{ $user->email }}
                                    </span>
                                @endif
                                @if($user->joined_at ?? null)
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                        Joined on {{ $user->joined_at->format('M d, Y') }}
                                    </span>
                                @endif
                                @if($user->location ?? null)
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        {{ $user->location }}
                                    </span>
                                @endif
                                @if($user->student_id ?? null)
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M6 9h12"/><path d="M6 13h8"/></svg>
                                        {{ $user->student_id }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- About me card --}}
                <div class="card-block">
                    <h4 class="section-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                        About me
                    </h4>
                    @if($user->about ?? null)
                        <p class="about-text">{{ $user->about }}</p>
                    @endif
                    @if($user->date_of_birth ?? null)
                        <div class="detail-row">
                            <span class="label">Date of birth</span>
                            <span class="value">{{ $user->date_of_birth }}</span>
                        </div>
                    @endif
                    @if($user->gender ?? null)
                        <div class="detail-row">
                            <span class="label">Gender</span>
                            <span class="value">{{ $user->gender }}</span>
                        </div>
                    @endif
                    @if($user->education ?? null)
                        <div class="detail-row">
                            <span class="label">Education</span>
                            <span class="value">{{ $user->education }}</span>
                        </div>
                    @endif
                    @if($user->school ?? null)
                        <div class="detail-row">
                            <span class="label">School</span>
                            <span class="value">{{ $user->school }}</span>
                        </div>
                    @endif
                    @if($user->bio ?? null)
                        <div class="detail-row">
                            <span class="label">Bio</span>
                            <span class="value">{{ $user->bio }}</span>
                        </div>
                    @endif

                    {{-- Skills list (from the About Me form) --}}
                    <div class="skills-block">
                        <div class="skills-group">
                            <span class="skills-title">Skills I have</span>
                            <div class="skills-chips">
                                @forelse ($user->skills_have ?? [] as $skill)
                                    <span class="skill-chip skill-chip-have">{{ $skill }}</span>
                                @empty
                                    <span class="skills-empty">No skills listed yet.</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="skills-group">
                            <span class="skills-title">Skills I want to learn</span>
                            <div class="skills-chips">
                                @forelse ($user->skills_want ?? [] as $skill)
                                    <span class="skill-chip skill-chip-want">{{ $skill }}</span>
                                @empty
                                    <span class="skills-empty">No learning goals listed yet.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Settings card --}}
                <div class="card-block">
                    <h4 class="section-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        Settings
                    </h4>
                    <div class="settings-row">
                        <span class="label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 6 10-6"/></svg>
                            Email
                        </span>
                        <span class="value-text">{{ $user->email ?? '' }}</span>
                        <button class="btn-change" type="button" onclick="openEditModal('settings')">Change</button>
                    </div>
                    <div class="settings-row">
                        <span class="label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Password
                        </span>
                        <span class="value-text">************</span>
                        <button class="btn-change" type="button" onclick="openEditModal('settings')">Change</button>
                    </div>
                    <div class="settings-row">
                        <span class="label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20z"/></svg>
                            Language
                        </span>
                        <select class="settings-select" name="language" aria-label="Language">
                            <option @selected(($user->language ?? 'English') === 'English')>English</option>
                            <option @selected(($user->language ?? '') === 'Filipino')>Filipino</option>
                        </select>
                    </div>
                    <div class="settings-row">
                        <span class="label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                            Time zone
                        </span>
                        <select class="settings-select" name="timezone" aria-label="Time zone">
                            @php
                                $tzLabels = [
                                    'Asia/Manila'      => '(GMT+8:00) Asia/Manila',
                                    'UTC'              => 'UTC',
                                    'America/New_York' => '(GMT-5:00) New York',
                                    'Europe/London'    => '(GMT+0:00) London',
                                ];
                                $currentTz = $user->timezone ?? 'Asia/Manila';
                            @endphp
                            @foreach($tzLabels as $val => $label)
                                <option value="{{ $val }}" @selected($currentTz === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>

            {{-- Right column --}}
            <div class="profile-side">

                <div class="quick-grid">
                    <a href="{{ route('courses.enrolled') }}" class="quick-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        Courses Enrolled
                    </a>
                    <a href="{{ route('badges.index') }}" class="quick-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3 6 7 1-5 5 1.5 7L12 17l-6.5 4L7 14 2 9l7-1 3-6z"/></svg>
                        Badges Earned
                    </a>
                    <a href="{{ route('certificates.index') }}" class="quick-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="5"/><path d="M8 13l-2 8 6-3 6 3-2-8"/></svg>
                        Certificates
                    </a>
                    <a href="{{ route('analytics.index') }}" class="quick-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                        Hours Learned
                    </a>
                </div>

                <div class="panel-card">
                    <div class="panel-card-head">
                        <h4>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21h8M12 17v4M7 4h10l-1 8a4 4 0 0 1-8 0L7 4z"/></svg>
                            Achievements
                        </h4>
                        <a href="{{ route('badges.index') }}">View all</a>
                    </div>
                    <div class="panel-card-body">
                        @forelse ($achievements ?? [] as $achievement)
                            <p>{{ $achievement }}</p>
                        @empty
                            No achievements to show yet.
                        @endforelse
                    </div>
                </div>

                <div class="panel-card">
                    <div class="panel-card-head">
                        <h4>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
                            Recent activities
                        </h4>
                        <a href="{{ route('analytics.index') }}">View all</a>
                    </div>
                    <div class="panel-card-body">
                        @forelse ($activities ?? [] as $activity)
                            <p>{{ $activity }}</p>
                        @empty
                            No recent activity yet.
                        @endforelse
                    </div>
                </div>

                {{-- ===== STUDENT ID ===== --}}
                @php
                    // The student's real ID number stored on the users table.
                    $studentId = $user->student_id ?? '-';
                @endphp
                <div class="panel-card">
                    <div class="panel-card-head">
                        <h4>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8" cy="11" r="2"/><path d="M5.5 17a3 3 0 0 1 5 0"/><path d="M14 9h5M14 13h5"/></svg>
                            Student ID
                        </h4>
                    </div>
                    <div class="panel-card-body student-id-body">
                        <div>
                            <div class="student-id-number">{{ $studentId }}</div>
                            <div class="student-id-label">Your unique student identification number</div>
                        </div>
                        <button type="button" class="student-id-copy" onclick="copyStudentId(this, '{{ $studentId }}')">Copy</button>
                    </div>
                </div>

            </div>

        </div>

    </main>
</div>

{{-- ===== EDIT PROFILE MODAL ===== --}}
<div id="editModal" class="modal-overlay" onclick="handleOverlayClick(event)">
    <div class="modal-panel" role="dialog" aria-modal="true" aria-label="Edit Profile">

        {{-- Header --}}
        <div class="modal-header">
            <div class="modal-header-left">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <div>
                    <h3>Edit profile</h3>
                    <p>Update your personal information</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeEditModal()" title="Close" aria-label="Close">&times;</button>
        </div>

        {{-- Tabs --}}
        <div class="modal-tabs">
            <button class="tab-btn active" data-tab="personal" onclick="switchTab('personal')">
                Personal Info
            </button>
            <button class="tab-btn" data-tab="about" onclick="switchTab('about')">
                About Me
            </button>
            <button class="tab-btn" data-tab="settings" onclick="switchTab('settings')">
                Account Settings
            </button>
        </div>

        {{-- Form --}}
        <form id="editProfileForm"
              action="{{ route('profile.update') }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="modal-body">

                {{-- TAB: Personal Info --}}
                <div id="tab-personal" class="tab-content active">

                    {{-- Avatar Upload --}}
                    <div class="avatar-upload-area">
                        <div class="avatar-preview" id="avatarPreview"
                            @if($user->avatar_url ?? null)
                                style="background-image:url('{{ $user->avatar_url }}')"
                            @endif>
                            @unless($user->avatar_url ?? null)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                            @endunless
                        </div>
                        <div class="avatar-upload-text">
                            <p>JPG, PNG or GIF - Up to 10 MB<br>Auto-resized to 400 x 400 px</p>
                            <label class="btn-upload-avatar">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                Upload Photo
                                {{-- No name="avatar": JS resizes and stores the image in the hidden field below --}}
                                <input type="file" accept="image/*" hidden onchange="previewAvatar(this)">
                            </label>
                            {{-- Client-resized base64 image sent here instead of raw file --}}
                            <input type="hidden" id="avatarBase64" name="avatar_base64" value="">
                        </div>
                    </div>

                    <p class="form-section-label">Basic information</p>

                    <div class="form-group">
                        <label for="edit_name">Full Name</label>
                        <input type="text" id="edit_name" name="name"
                            value="{{ old('name', $user->name ?? '') }}"
                            placeholder="Enter your full name">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_phone">Phone Number</label>
                            <input type="tel" id="edit_phone" name="phone"
                                value="{{ old('phone', $user->phone ?? '') }}"
                                placeholder="e.g. 09XXXXXXXXX">
                        </div>
                        <div class="form-group">
                            <label for="edit_location">Location</label>
                            <input type="text" id="edit_location" name="location"
                                value="{{ old('location', $user->location ?? '') }}"
                                placeholder="City, Province">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_role">Role / Title</label>
                        <input type="text" id="edit_role" name="role"
                            value="{{ old('role', $user->role ?? '') }}"
                            placeholder="e.g. Student, Developer">
                    </div>
                </div>

                {{-- TAB: About Me --}}
                <div id="tab-about" class="tab-content">

                    <div class="form-group">
                        <label for="edit_about">About Me</label>
                        <textarea id="edit_about" name="about" rows="3"
                            placeholder="Share a little about yourself...">{{ old('about', $user->about ?? '') }}</textarea>
                        <p class="hint">Brief description shown on your profile card.</p>
                    </div>

                    <hr class="form-divider">
                    <p class="form-section-label">Personal details</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_dob">Date of Birth</label>
                            <input type="date" id="edit_dob" name="date_of_birth"
                                value="{{ old('date_of_birth', isset($user->date_of_birth) ? \Carbon\Carbon::parse($user->date_of_birth)->format('Y-m-d') : '') }}">
                        </div>
                        <div class="form-group">
                            <label for="edit_gender">Gender</label>
                            <select id="edit_gender" name="gender">
                                <option value="">Select...</option>
                                <option value="Male"   @selected(($user->gender ?? '') === 'Male')>Male</option>
                                <option value="Female" @selected(($user->gender ?? '') === 'Female')>Female</option>
                                <option value="Other"  @selected(($user->gender ?? '') === 'Other')>Other</option>
                                <option value="Prefer not to say" @selected(($user->gender ?? '') === 'Prefer not to say')>Prefer not to say</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_education">Education</label>
                        <input type="text" id="edit_education" name="education"
                            value="{{ old('education', $user->education ?? '') }}"
                            placeholder="e.g. BS Information Technology">
                    </div>

                    <div class="form-group">
                        <label for="edit_school">Last school, college, or university you attended</label>
                        @php
                            $storedSchool   = old('school', \App\Support\SchoolCatalog::selectedOption($user->school ?? null));
                            $storedSchoolOther = old('school_other', \App\Support\SchoolCatalog::otherText($user->school ?? null));
                        @endphp
                        <select id="edit_school" name="school" onchange="editSchoolChanged(this)">
                            <option value="">Select your school...</option>
                            @foreach (\App\Support\SchoolCatalog::all() as $school)
                                <option value="{{ $school }}" {{ $storedSchool === $school ? 'selected' : '' }}>{{ $school }}</option>
                            @endforeach
                            <option value="{{ \App\Support\SchoolCatalog::OTHER }}" {{ $storedSchool === \App\Support\SchoolCatalog::OTHER ? 'selected' : '' }}>Other (please specify)</option>
                        </select>
                        <input type="text" id="edit_school_other" name="school_other"
                            value="{{ $storedSchoolOther }}"
                            placeholder="Type the name of your school"
                            style="margin-top:8px;{{ $storedSchool === \App\Support\SchoolCatalog::OTHER ? '' : 'display:none;' }}">
                    </div>

                    <div class="form-group">
                        <label for="edit_bio">Bio</label>
                        <textarea id="edit_bio" name="bio" rows="3"
                            placeholder="Share a short bio...">{{ old('bio', $user->bio ?? '') }}</textarea>
                    </div>

                {{-- Skills pickers: same floating, searchable modal used by
                     the About Me form and the faculty course form. Posting
                     arrays is fine: updateProfile()'s splitSkills() accepts
                     either an array or a comma-separated string. --}}
                @php
                    $haveSaved = old('skills_have', $user->skills_have ?? []);
                    $wantSaved = old('skills_want', $user->skills_want ?? []);
                    $haveSaved = is_array($haveSaved) ? $haveSaved : array_filter(array_map('trim', explode(',', $haveSaved)));
                    $wantSaved = is_array($wantSaved) ? $wantSaved : array_filter(array_map('trim', explode(',', $wantSaved)));
                @endphp

                @foreach ([
                    ['key' => 'have', 'field' => 'skills_have', 'label' => 'Skills I Have',
                     'saved' => $haveSaved, 'title' => 'Skills I have',
                     'hint' => 'Shown on your profile skills list.'],
                    ['key' => 'want', 'field' => 'skills_want', 'label' => 'Skills I Want to Learn',
                     'saved' => $wantSaved, 'title' => 'Skills I want to learn',
                     'hint' => 'Used to personalize your course recommendations.'],
                ] as $picker)
                    <div class="form-group">
                        <label>{{ $picker['label'] }}</label>

                        <input type="hidden" name="{{ $picker['field'] }}_submitted" value="1">

                        <div class="skillpick" data-picker="{{ $picker['key'] }}">
                            <button type="button"
                                    class="skillpick-mini {{ count($picker['saved']) ? 'has-value' : '' }}"
                                    onclick="openSkillPick('{{ $picker['key'] }}')">
                                <span class="skillpick-mini-label">
                                    {{ count($picker['saved']) ? count($picker['saved']) . ' selected' : 'None' }}
                                </span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                    <path d="M6 9l6 6 6-6"/>
                                </svg>
                            </button>

                            <div class="skillpick-backdrop" hidden
                                 onclick="cancelSkillPick('{{ $picker['key'] }}')"></div>

                            <div class="skillpick-pop" hidden role="dialog" aria-modal="true"
                                 aria-label="{{ $picker['title'] }}">
                                <div class="skillpick-hd">
                                    <strong>{{ $picker['title'] }}</strong>
                                    <span>{{ $picker['hint'] }}</span>
                                </div>

                                <div class="skillpick-search">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                        <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                                    </svg>
                                    <input type="text" autocomplete="off"
                                           placeholder="Search {{ count($skill_options ?? []) }} skills..."
                                           oninput="filterSkillPick('{{ $picker['key'] }}', this.value)">
                                </div>

                                <div class="skillpick-count">
                                    <span class="skillpick-count-line">No skills selected</span>
                                    <button type="button" class="skillpick-clearall"
                                            onclick="clearSkillPick('{{ $picker['key'] }}')">Clear all</button>
                                </div>

                                <div class="skillpick-list">
                                    @foreach (($skill_groups ?? []) as $groupName => $groupSkills)
                                        <div class="skillpick-group">
                                            <div class="skillpick-group-hd">{{ $groupName }}</div>
                                            @foreach ($groupSkills as $skill)
                                                <label class="skillpick-opt {{ in_array($skill, $picker['saved']) ? 'on' : '' }}"
                                                       data-skill="{{ strtolower($skill) }}"
                                                       data-groupname="{{ strtolower($groupName) }}">
                                                    <input type="checkbox" name="{{ $picker['field'] }}[]"
                                                           value="{{ $skill }}"
                                                           @checked(in_array($skill, $picker['saved']))>
                                                    <span class="skillpick-tick">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2">
                                                            <path d="M5 12.5l5 5 9-10"/>
                                                        </svg>
                                                    </span>
                                                    <span class="skillpick-name">{{ $skill }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endforeach
                                    <div class="skillpick-empty" hidden>No skills match that search.</div>
                                </div>

                                <div class="skillpick-ft">
                                    <button type="button" class="skillpick-btn"
                                            onclick="cancelSkillPick('{{ $picker['key'] }}')">Cancel</button>
                                    <button type="button" class="skillpick-btn primary"
                                            onclick="saveSkillPick('{{ $picker['key'] }}')">Save</button>
                                </div>
                            </div>
                        </div>

                        <p class="hint">{{ $picker['hint'] }}</p>
                    </div>
                @endforeach
                </div>

                {{-- TAB: Account Settings --}}
                <div id="tab-settings" class="tab-content">

                    <div class="settings-info-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p>Leave the password fields empty if you don't want to change your password.</p>
                    </div>

                    <p class="form-section-label">Email address</p>

                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email"
                            value="{{ old('email', $user->email ?? '') }}"
                            placeholder="you@example.com">
                        <p class="hint">This is used to log in and receive notifications.</p>
                    </div>

                    <hr class="form-divider">
                    <p class="form-section-label">Change password</p>

                    <div class="form-group">
                        <label for="edit_curr_pwd">Current Password</label>
                        <div class="pwd-wrap">
                            <input type="password" id="edit_curr_pwd" name="current_password" placeholder="Enter current password">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('edit_curr_pwd', this)" title="Show/hide" aria-label="Show or hide password">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_new_pwd">New Password</label>
                            <div class="pwd-wrap">
                                <input type="password" id="edit_new_pwd" name="password" placeholder="Min. 8 characters">
                                <button type="button" class="pwd-toggle" onclick="togglePwd('edit_new_pwd', this)" title="Show/hide" aria-label="Show or hide password">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_confirm_pwd">Confirm Password</label>
                            <div class="pwd-wrap">
                                <input type="password" id="edit_confirm_pwd" name="password_confirmation" placeholder="Repeat new password">
                                <button type="button" class="pwd-toggle" onclick="togglePwd('edit_confirm_pwd', this)" title="Show/hide" aria-label="Show or hide password">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr class="form-divider">
                    <p class="form-section-label">Preferences</p>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_language">Language</label>
                            <select id="edit_language" name="language">
                                <option value="English"  @selected(($user->language ?? 'English') === 'English')>English</option>
                                <option value="Filipino" @selected(($user->language ?? '') === 'Filipino')>Filipino</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_timezone">Time Zone</label>
                            <select id="edit_timezone" name="timezone">
                                <option value="Asia/Manila"      @selected(($user->timezone ?? 'Asia/Manila') === 'Asia/Manila')>(GMT+8:00) Asia/Manila</option>
                                <option value="UTC"              @selected(($user->timezone ?? '') === 'UTC')>UTC</option>
                                <option value="America/New_York" @selected(($user->timezone ?? '') === 'America/New_York')>(GMT-5:00) New York</option>
                                <option value="Europe/London"    @selected(($user->timezone ?? '') === 'Europe/London')>(GMT+0:00) London</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>{{-- /.modal-body --}}

            {{-- Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-save">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Changes
                </button>
            </div>

        </form>{{-- /form --}}
    </div>{{-- /.modal-panel --}}
</div>{{-- /.modal-overlay --}}

<script>
/* ====== Student ID copy ====== */
function copyStudentId(btn, id) {
    const done = () => {
        const original = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(() => { btn.textContent = original; }, 1500);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(id).then(done);
    } else {
        const ta = document.createElement('textarea');
        ta.value = id;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        done();
    }
}

/* ====== Edit Profile Modal ====== */
function openEditModal(tab) {
    document.getElementById('editModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    if (tab) switchTab(tab);
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
    document.body.style.overflow = '';
}
function handleOverlayClick(e) {
    if (e.target === document.getElementById('editModal')) closeEditModal();
}
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    const btn = document.querySelector(`.tab-btn[data-tab="${tab}"]`);
    const panel = document.getElementById(`tab-${tab}`);
    if (btn) btn.classList.add('active');
    if (panel) panel.classList.add('active');
}

/* Password show/hide */
function togglePwd(id, btn) {
    const inp = document.getElementById(id);
    const isText = inp.type === 'text';
    inp.type = isText ? 'password' : 'text';
    btn.innerHTML = isText
        ? `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`
        : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
}

/* Avatar: resize client-side then store in hidden field (bypasses PHP upload limits) */
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            // Resize to max 400 x 400 keeping aspect ratio
            const MAX = 400;
            let w = img.width, h = img.height;
            if (w > h) { if (w > MAX) { h = Math.round(h * MAX / w); w = MAX; } }
            else        { if (h > MAX) { w = Math.round(w * MAX / h); h = MAX; } }

            const canvas = document.createElement('canvas');
            canvas.width  = w;
            canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);

            // Compress to JPEG 85%: typically < 50 KB regardless of original size
            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

            // Live preview in modal
            const preview = document.getElementById('avatarPreview');
            preview.style.backgroundImage = `url('${dataUrl}')`;
            preview.innerHTML = '';

            // Put compressed base64 in hidden field so it's submitted with the form
            document.getElementById('avatarBase64').value = dataUrl;
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

/* Keyboard: Esc closes modal */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeEditModal();
});

/* ====== Success Toast ====== */
(function () {
    const toast = document.getElementById('successToast');
    if (!toast) return;
    // Show on next frame so CSS transition fires
    requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
    // Auto-dismiss after 4 seconds
    setTimeout(() => toast.classList.remove('show'), 4000);
})();
</script>


{{-- Back to top (appears on long pages) --}}
<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top"
        onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>
<script>
    (function () {
        var btn = document.getElementById('back-to-top-btn');
        if (!btn) return;
        function toggleBackToTop() {
            btn.style.display = (window.scrollY || document.documentElement.scrollTop) > 400 ? 'flex' : 'none';
        }
        window.addEventListener('scroll', toggleBackToTop, { passive: true });
        toggleBackToTop();
    })();
</script>

{{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
@include('components.responsive')
<script>
    /* ── Skills pickers ──────────────────────────────────────────────
       Two independent modals ("have" and "want") sharing one set of
       functions, keyed by data-picker. Cancel restores whatever was ticked
       when the modal opened, so it never loses an existing selection. */
    var _skillSnapshots = {};

    function skillRoot(key) {
        return document.querySelector('.skillpick[data-picker="' + key + '"]');
    }

    function skillBoxes(key) {
        var root = skillRoot(key);
        return root ? Array.prototype.slice.call(root.querySelectorAll('input[type="checkbox"]')) : [];
    }

    function syncSkillRows(key) {
        skillBoxes(key).forEach(function (box) {
            box.closest('.skillpick-opt').classList.toggle('on', box.checked);
        });
    }

    /* A few skills sit under two fields (Statistics, Logic, Policy,
       Manufacturing, Therapy): keep both copies in step. */
    function mirrorSkill(key, box) {
        skillBoxes(key).forEach(function (other) {
            if (other !== box && other.value === box.value) other.checked = box.checked;
        });
    }

    function refreshSkillLabel(key) {
        var root = skillRoot(key);
        if (!root) return;

        var picked = {};
        skillBoxes(key).forEach(function (b) { if (b.checked) picked[b.value] = true; });
        var count = Object.keys(picked).length;

        var mini  = root.querySelector('.skillpick-mini');
        var label = root.querySelector('.skillpick-mini-label');
        var line  = root.querySelector('.skillpick-count-line');

        if (label) label.textContent = count ? count + ' selected' : 'None';
        if (mini)  mini.classList.toggle('has-value', count > 0);
        if (line)  line.textContent = count
            ? count + (count === 1 ? ' skill selected' : ' skills selected')
            : 'No skills selected';
    }

    function filterSkillPick(key, term) {
        var root = skillRoot(key);
        if (!root) return;
        term = (term || '').trim().toLowerCase();

        var anyVisible = false;

        root.querySelectorAll('.skillpick-group').forEach(function (group) {
            var groupVisible = false;

            group.querySelectorAll('.skillpick-opt').forEach(function (opt) {
                var box     = opt.querySelector('input');
                var name    = opt.getAttribute('data-skill') || '';
                var gname   = opt.getAttribute('data-groupname') || '';
                var matches = !term
                    || name.indexOf(term) !== -1
                    || gname.indexOf(term) !== -1
                    || (box && box.checked);

                opt.hidden = !matches;
                if (matches) groupVisible = true;
            });

            group.hidden = !groupVisible;
            if (groupVisible) anyVisible = true;
        });

        var empty = root.querySelector('.skillpick-empty');
        if (empty) empty.hidden = anyVisible;
    }

    function openSkillPick(key) {
        var root = skillRoot(key);
        if (!root) return;

        _skillSnapshots[key] = skillBoxes(key).filter(function (b) { return b.checked; })
                                              .map(function (b) { return b.value; });

        root.querySelector('.skillpick-pop').hidden = false;
        root.querySelector('.skillpick-backdrop').hidden = false;
        document.body.style.overflow = 'hidden';

        var search = root.querySelector('.skillpick-search input');
        if (search) { search.value = ''; }
        filterSkillPick(key, '');
        syncSkillRows(key);
        refreshSkillLabel(key);
        if (search) search.focus();
    }

    function closeSkillPick(key) {
        var root = skillRoot(key);
        if (!root) return;
        root.querySelector('.skillpick-pop').hidden = true;
        root.querySelector('.skillpick-backdrop').hidden = true;
        document.body.style.overflow = '';
    }

    function saveSkillPick(key) {
        refreshSkillLabel(key);
        closeSkillPick(key);
        if (window.revalidateAboutMe) window.revalidateAboutMe();
    }

    function cancelSkillPick(key) {
        var root = skillRoot(key);
        if (!root || root.querySelector('.skillpick-pop').hidden) return;

        var snap = _skillSnapshots[key] || [];
        skillBoxes(key).forEach(function (box) {
            box.checked = snap.indexOf(box.value) !== -1;
        });
        syncSkillRows(key);
        refreshSkillLabel(key);
        closeSkillPick(key);
    }

    function clearSkillPick(key) {
        skillBoxes(key).forEach(function (b) { b.checked = false; });
        syncSkillRows(key);
        refreshSkillLabel(key);
    }

    document.addEventListener('DOMContentLoaded', function () {
        ['have', 'want'].forEach(function (key) {
            if (!skillRoot(key)) return;
            syncSkillRows(key);
            refreshSkillLabel(key);

            skillBoxes(key).forEach(function (box) {
                box.addEventListener('change', function () {
                    mirrorSkill(key, box);
                    syncSkillRows(key);
                    refreshSkillLabel(key);
                });
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            ['have', 'want'].forEach(function (key) {
                var root = skillRoot(key);
                if (root && !root.querySelector('.skillpick-pop').hidden) cancelSkillPick(key);
            });
        });
    });


    /* School picker: the "please specify" box only shows for Other. */
    function editSchoolChanged(sel) {
        var other = document.getElementById('edit_school_other');
        if (!other) return;
        var isOther = sel.value === @json(\App\Support\SchoolCatalog::OTHER);
        other.style.display = isOther ? 'block' : 'none';
        if (!isOther) other.value = '';
        else other.focus();
    }
</script>

</body>
</html>