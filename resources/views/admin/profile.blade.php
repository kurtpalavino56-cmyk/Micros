{{--
    resources/views/admin/Profile.blade.php

    Admin > My Profile.

    Rebuilt to use the SAME design language as Faculty_Profile: identity
    card, panel sections, and a slide-in Edit Profile drawer with tabbed
    sections. The stylesheet below is the faculty one, so the two pages
    stay visually identical.

    Differences from the faculty page, all content rather than design:
      - name is edited as first / middle / last / suffix, because
        AdminController::updateProfile validates the parts separately
      - Account Settings holds USERNAME as well as email and password
      - no courses table or performance charts

    Expects: $user (UserPresenter::admin)
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Profile | Upskill</title>
    {{-- Browser tab icon (favicon) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/PSU-Logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/PSU-Logo.png') }}">
<style>

    :root{
        --navy:#13176b;
        --navy-deep:#0c0f4d;
        --gold:#dba617;
        --gold-dark:#c4930f;
        --cyan:#7fe9e3;
        --thumb:#d8e3f8;
        --ink:#13176b;
        --muted:#6b7280;
        --line:#e5e7eb;
        --green:#16a34a;
        --shadow: 0 10px 25px rgba(19,23,107,0.08);
    }
    *{box-sizing:border-box;}
    body{font-family:"Segoe UI", Roboto, Helvetica, Arial, sans-serif;color:var(--ink);margin:0;background:#fff;}
    a{text-decoration:none;color:inherit;}
    button{font-family:inherit;cursor:pointer;}

    /* Topbar */
    .topbar{background:var(--navy);display:flex;align-items:center;justify-content:space-between;padding:14px 28px;gap:20px;}
    .brand{display:flex;align-items:center;gap:14px;color:#fff;white-space:nowrap;}
    .brand .logo{width:46px;height:46px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .brand .logo img{width:100%;height:100%;object-fit:contain;border-radius:50%;padding:3px;}
    .brand h1{font-size:24px;letter-spacing:1px;margin:0;font-weight:800;}
    .nav-pills{display:flex;gap:8px;flex-wrap:wrap;margin-left:auto;align-items:center;}
    .nav-pills a{background:transparent;color:#fff;font-weight:700;padding:10px 18px;border-radius:10px;font-size:15px;transition:color .15s ease, background-color .15s ease;}
    .nav-pills a:hover{color:var(--gold);}
    .nav-pills a.is-active{color:var(--gold);background:rgba(255,255,255,0.08);}
    .search-box{display:flex;align-items:center;gap:10px;background:#fff;border-radius:999px;padding:10px 18px;min-width:240px;color:var(--muted);}
    .search-box input{border:none;outline:none;font-size:15px;width:100%;color:var(--ink);background:transparent;}
    .icon-cluster{display:flex;align-items:center;gap:14px;}
    .icon-circle{width:42px;height:42px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;box-shadow:0 6px 16px rgba(19,23,107,0.12);transition:transform .2s ease, box-shadow .2s ease, background-color .2s ease;}
    .icon-circle:hover{transform:translateY(-1px);box-shadow:0 10px 20px rgba(19,23,107,0.16);}
    .icon-circle svg{width:22px;height:22px;color:var(--navy);}
    .notification-btn{position:relative;width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg, #ffffff 0%, #eef4ff 100%);border:1px solid rgba(19,23,107,0.08);}
    .notification-btn:hover{background:linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);}
    .notification-btn svg{width:21px;height:21px;}
    .notification-badge{position:absolute;top:5px;right:5px;width:10px;height:10px;border-radius:50%;background:#ef4444;border:2px solid #fff;box-shadow:0 0 0 2px rgba(239,68,68,0.18);} 

    /* Layout */
    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}

    /* Sidebar */
    .sidebar{background:var(--navy);padding:26px 16px;display:flex;flex-direction:column;gap:6px;margin:24px 10px 24px 24px;border-radius:22px;box-shadow:0 16px 34px rgba(19,23,107,0.28);height:fit-content;position:sticky;top:20px;}
    .side-link{display:flex;align-items:center;gap:14px;padding:14px;border-radius:14px;font-weight:700;font-size:16px;color:#fff;transition:color .15s ease;}
    .side-link svg{width:26px;height:26px;flex-shrink:0;}
    .side-link.active{background:var(--cyan);color:var(--navy);}
    .side-icon-box{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(255,255,255,0.14);}
    .side-icon-box svg{color:#fff;width:22px;height:22px;transition:color .15s ease;}
    .side-link.active .side-icon-box{background:var(--navy);}
    .side-link.plain .side-icon-box{background:transparent;}
    .side-link.plain .side-icon-box svg{color:#fff;width:26px;height:26px;}
    /* hover: text + icon turn gold (non-active links) */
    .side-link:not(.active):hover{color:var(--gold);}
    .side-link:not(.active):hover .side-icon-box svg{color:var(--gold);}
    .side-divider{border:none;border-top:1px solid rgba(255,255,255,0.25);margin:18px 6px;}

    /* Main */
    .main{flex:1 1 auto;min-width:0;padding:28px 30px 60px;}
    /* Grid/flex children can't shrink below content unless min-width is
       reset — without these the page overflows at every width. */
    .layout > *,
    .two-col > *,
    .charts-grid > *,
    .profile-card > *{min-width:0;}
    .panel{min-width:0;}
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:28px;}
    .page-head h2{font-size:30px;margin:0 0 6px;color:var(--navy);}
    .page-head p{margin:0;color:var(--muted);font-size:15px;font-weight:600;}
    .page-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
    .btn-edit-profile{background:#fff;border:1.5px solid var(--line);color:var(--navy);font-weight:800;padding:12px 28px;border-radius:12px;font-size:15px;box-shadow:var(--shadow);transition:border-color .2s;}
    .btn-edit-profile:hover{border-color:var(--navy);}
    .btn-logout{background:#fff1f2;border:1.5px solid #fecdd3;color:#b91c1c;font-weight:800;padding:12px 28px;border-radius:12px;font-size:15px;box-shadow:var(--shadow);}
    .btn-logout:hover{background:#fee2e2;border-color:#f43f5e;}

    /* Panels (same pattern as dashboard side panels) */
    .panel{border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);overflow:hidden;margin-bottom:28px;background:#fff;}
    .panel-head{background:var(--gold);color:var(--navy);font-weight:800;font-size:20px;padding:14px 22px;display:flex;align-items:center;justify-content:space-between;gap:12px;}
    .panel-head .range{font-size:12px;font-weight:700;background:rgba(255,255,255,0.55);padding:6px 14px;border-radius:999px;}
    .panel-head .head-ico{display:flex;align-items:center;gap:10px;}
    .panel-head .head-ico svg{width:22px;height:22px;}
    .panel-body{padding:22px;}

    /* ── ① Profile identity card ──────────────────────────────────────── */
    .profile-card{border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);padding:24px;display:flex;align-items:flex-start;gap:24px;flex-wrap:wrap;margin-bottom:28px;}
    .avatar-ring{width:90px;height:90px;border-radius:50%;border:3px solid var(--navy);display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#fff;overflow:hidden;background-size:cover;background-position:center;}
    .avatar-ring svg{width:50px;height:50px;color:var(--navy);}
    .identity{flex:1 1 auto;min-width:0;}
    .identity h3{margin:0 0 12px;font-size:22px;color:var(--navy);display:flex;flex-wrap:wrap;align-items:center;gap:10px;line-height:1.25;}
    .identity-grid{display:flex;flex-wrap:wrap;gap:14px 28px;color:var(--muted);font-size:14px;}
    .id-item{display:flex;align-items:center;gap:6px;font-size:14px;color:var(--muted);min-width:0;overflow-wrap:anywhere;}
    .id-item svg{width:18px;height:18px;color:var(--navy);flex-shrink:0;}
    .role-pill{display:inline-block;background:var(--cyan);color:var(--navy);font-weight:800;font-size:12px;padding:5px 16px;border-radius:999px;white-space:nowrap;flex-shrink:0;}

    /* Two-column: About me | Settings */
    .two-col{display:grid;grid-template-columns:1fr 1fr;gap:28px;margin-bottom:0;}

    .about-row{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;padding:10px 0;border-bottom:1px dashed var(--line);}
    .about-row:last-child{border-bottom:none;}
    .about-label{display:flex;align-items:center;gap:10px;font-weight:800;font-size:14px;color:var(--navy);flex-shrink:0;}
    .about-label svg{width:18px;height:18px;flex-shrink:0;}
    .about-value{font-size:14px;font-weight:700;color:var(--ink);text-align:right;min-width:0;overflow-wrap:anywhere;}
    .about-value.link-blue{color:#1d4ed8;}
    .about-intro{font-size:12px;color:var(--muted);line-height:1.6;margin:0 0 14px;font-weight:600;}

    .set-row{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:12px 0;border-bottom:1px dashed var(--line);}
    .set-row:last-child{border-bottom:none;}
    .set-label{display:flex;align-items:center;gap:10px;font-weight:800;font-size:14px;color:var(--navy);flex-shrink:0;min-width:120px;}
    .set-label svg{width:18px;height:18px;flex-shrink:0;}
    .set-value{font-size:14px;font-weight:700;color:var(--ink);flex:1;min-width:120px;overflow-wrap:anywhere;}
    .btn-change{background:#fff;border:1.5px solid var(--navy);color:var(--navy);font-weight:700;padding:7px 20px;border-radius:999px;font-size:12px;}
    .set-select{flex:1;min-width:150px;border:1.5px solid #c9ccdb;border-radius:10px;padding:10px 12px;font-size:13px;font-weight:700;color:var(--ink);background:#fff;outline:none;font-family:inherit;}

    /* ── ② Performance + Activity charts ─────────────────────────────── */
    .charts-grid{display:grid;grid-template-columns:1fr 1fr;gap:28px;margin-bottom:28px;}

    .chart{display:flex;align-items:flex-end;gap:14px;height:190px;padding-top:26px;}
    .chart .col{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;gap:8px;min-width:0;}
    .chart .bar-wrap{width:100%;max-width:52px;display:flex;flex-direction:column;justify-content:flex-end;height:100%;position:relative;}
    .chart .bar{width:100%;border-radius:10px 10px 4px 4px;background:var(--navy);transition:opacity .15s ease;}
    .chart .bar:hover{opacity:.85;}
    .chart .bar.gold{background:var(--gold);}
    .chart .bar-value{position:absolute;top:-24px;left:50%;transform:translateX(-50%);font-size:11px;font-weight:800;color:var(--navy);background:var(--cyan);padding:3px 8px;border-radius:999px;white-space:nowrap;}
    .chart .axis-label{font-size:12px;font-weight:700;color:var(--muted);}

    /* ── ② Courses table ──────────────────────────────────────────────── */
    .filter-chip{background:#fff;border:1.5px solid var(--navy);color:var(--navy);font-weight:700;padding:7px 20px;border-radius:999px;font-size:12px;display:inline-flex;align-items:center;gap:8px;}
    .filter-chip svg{width:14px;height:14px;}
    .courses-table{width:100%;border-collapse:collapse;}
    .courses-table th{font-size:12px;font-weight:800;color:var(--muted);text-transform:none;text-align:left;padding:10px 12px;border-bottom:1.5px solid var(--line);white-space:nowrap;}
    .courses-table td{padding:16px 12px;border-bottom:1px solid var(--line);vertical-align:middle;font-size:14px;font-weight:700;color:var(--ink);}
    .courses-table tr:last-child td{border-bottom:none;}
    .tbl-course{display:flex;align-items:center;gap:14px;min-width:230px;}
    .tbl-thumb{width:52px;height:52px;border-radius:12px;background:var(--thumb);flex-shrink:0;background-size:cover;background-position:center;}
    .tbl-course h4{margin:0 0 4px;font-size:15px;color:var(--navy);}
    .tbl-sub{font-size:11px;color:var(--muted);font-weight:700;}
    .tbl-sub .cat{color:var(--gold-dark);}
    .rating{display:inline-flex;align-items:center;gap:6px;white-space:nowrap;}
    .rating svg{width:16px;height:16px;color:var(--gold);flex-shrink:0;}
    .progress-cell{display:flex;align-items:center;gap:10px;min-width:150px;}
    .progress-track{flex:1;height:8px;border-radius:999px;background:#e8eaf6;overflow:hidden;min-width:80px;}
    .progress-fill{height:100%;border-radius:999px;background:var(--navy);}
    .progress-pct{font-size:12px;font-weight:800;color:var(--navy);white-space:nowrap;}
    .status-pill{display:inline-block;font-size:12px;font-weight:700;padding:6px 18px;border-radius:999px;white-space:nowrap;}
    .status-pill.active{background:#86efac;color:#14532d;}
    .status-pill.archived{background:#e5e7eb;color:#374151;}
    .status-pill.draft{background:#fde68a;color:#713f12;}
    .empty-state{border:1px dashed var(--line);border-radius:16px;padding:30px;text-align:center;color:var(--muted);font-size:14px;}

    /* ===== EDIT PROFILE MODAL (design from Student_Profile) ===== */
    .modal-overlay{
        position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;
        display:flex;justify-content:flex-end;
        opacity:0;visibility:hidden;
        transition:opacity .3s ease,visibility .3s ease;
    }
    .modal-overlay.open{opacity:1;visibility:visible;}
    .modal-panel{
        width:520px;max-width:95vw;height:100vh;height:100dvh;max-height:100dvh;
        background:#fff;display:flex;flex-direction:column;
        box-shadow:-12px 0 50px rgba(19,23,107,.18);
        transform:translateX(100%);
        transition:transform .38s cubic-bezier(.16,1,.3,1);
        overflow:hidden;
    }
    .modal-overlay.open .modal-panel{transform:translateX(0);}
    .modal-header{
        padding:22px 28px 16px;border-bottom:1px solid var(--line);
        display:flex;align-items:center;justify-content:space-between;flex-shrink:0;
        background:#fff;
    }
    .modal-header-left{display:flex;align-items:center;gap:12px;}
    .modal-header-left svg{width:22px;height:22px;color:var(--navy);}
    .modal-header h3{color:var(--navy);margin:0;font-size:20px;font-weight:800;}
    .modal-header p{margin:2px 0 0;color:var(--muted);font-size:12px;}
    .modal-close{
        width:36px;height:36px;border-radius:50%;
        border:1.5px solid var(--line);background:#fff;
        color:var(--navy);font-size:22px;line-height:1;
        display:flex;align-items:center;justify-content:center;cursor:pointer;
        transition:background .2s,border-color .2s;flex-shrink:0;
    }
    .modal-close:hover{background:var(--line);}
    .modal-tabs{
        display:flex;gap:0;border-bottom:1px solid var(--line);
        padding:0 28px;flex-shrink:0;background:#fff;
    }
    .tab-btn{
        background:none;border:none;
        padding:13px 14px;font-size:13.5px;font-weight:700;
        color:var(--muted);cursor:pointer;
        border-bottom:3px solid transparent;margin-bottom:-1px;
        transition:color .2s,border-color .2s;white-space:nowrap;
    }
    .tab-btn.active{color:var(--navy);border-bottom-color:var(--navy);}
    .tab-btn:hover:not(.active){color:var(--navy);}
    #editProfileForm{flex:1 1 auto;min-height:0;display:flex;flex-direction:column;}
    .modal-body{flex:1 1 auto;min-height:0;overflow-y:auto;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;padding:24px 28px;scrollbar-width:thin;}
    .tab-content{display:none;}
    .tab-content.active{display:block;}
    .avatar-upload-area{
        display:flex;align-items:center;gap:18px;
        padding:18px;border:2px dashed var(--line);border-radius:14px;
        margin-bottom:24px;background:#fafbff;
    }
    .avatar-preview{
        width:70px;height:70px;border-radius:50%;border:3px solid var(--navy);
        display:flex;align-items:center;justify-content:center;
        background-size:cover;background-position:center;flex-shrink:0;
        overflow:hidden;background-color:#f0f3ff;
    }
    .avatar-preview svg{width:38px;height:38px;color:var(--navy);}
    .avatar-upload-text p{margin:0 0 8px;color:var(--muted);font-size:12.5px;line-height:1.5;}
    .btn-upload-avatar{
        background:var(--navy);color:#fff;border:none;border-radius:8px;
        padding:8px 16px;font-size:12.5px;font-weight:700;cursor:pointer;
        font-family:inherit;display:inline-flex;align-items:center;gap:6px;
        transition:background .2s;
    }
    .btn-upload-avatar:hover{background:var(--navy-deep);}
    .form-group{margin-bottom:18px;}
    .form-group label{display:block;font-weight:700;color:var(--navy);font-size:12.5px;margin-bottom:6px;letter-spacing:.3px;}
    .form-group input,.form-group select,.form-group textarea{
        width:100%;border:1.5px solid var(--line);border-radius:10px;
        padding:10px 13px;font-size:14px;font-family:inherit;
        color:var(--navy);background:#fff;outline:none;
        transition:border-color .2s,box-shadow .2s;
    }
    .form-group input:focus,.form-group select:focus,.form-group textarea:focus{
        border-color:var(--navy);box-shadow:0 0 0 3px rgba(19,23,107,.08);
    }
    .form-group textarea{resize:vertical;min-height:78px;line-height:1.5;}
    .form-group .hint{color:var(--muted);font-size:11.5px;margin:4px 0 0;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    .form-divider{border:none;border-top:1px solid var(--line);margin:22px 0;}
    .form-section-label{font-size:11px;font-weight:800;color:var(--muted);letter-spacing:1px;text-transform:uppercase;margin:0 0 14px;}
    .form-errors{
        background:#fef2f2;border:1px solid #fecaca;border-radius:10px;
        padding:12px 15px;margin-bottom:20px;color:#b91c1c;font-size:12.5px;line-height:1.6;
    }
    .form-errors ul{margin:0;padding-left:18px;}
    .pwd-wrap{position:relative;}
    .pwd-wrap input{padding-right:44px;}
    .pwd-toggle{
        position:absolute;right:12px;top:50%;transform:translateY(-50%);
        background:none;border:none;cursor:pointer;color:var(--muted);
        padding:0;display:flex;align-items:center;
    }
    .pwd-toggle svg{width:17px;height:17px;}
    .settings-info-box{
        background:#f0f3ff;border-radius:10px;padding:12px 15px;
        margin-bottom:20px;display:flex;gap:10px;align-items:flex-start;
    }
    .settings-info-box svg{width:16px;height:16px;color:var(--navy);flex-shrink:0;margin-top:1px;}
    .settings-info-box p{margin:0;color:var(--navy);font-size:12.5px;line-height:1.5;}
    .modal-footer{
        padding:16px 28px;border-top:1px solid var(--line);
        display:flex;gap:10px;justify-content:flex-end;flex-shrink:0;background:#fff;
    }
    .btn-cancel-modal{
        background:#fff;border:1.5px solid var(--line);color:var(--navy);
        font-weight:700;padding:10px 22px;border-radius:10px;font-size:14px;cursor:pointer;
        font-family:inherit;transition:border-color .2s;
    }
    .btn-cancel-modal:hover{border-color:var(--navy);}
    .btn-save{
        background:var(--navy);color:#fff;border:none;
        font-weight:700;padding:10px 26px;border-radius:10px;font-size:14px;cursor:pointer;
        font-family:inherit;transition:background .2s;display:flex;align-items:center;gap:7px;
    }
    .btn-save:hover{background:var(--navy-deep);}
    .btn-save svg{width:16px;height:16px;}

    /* ===== SUCCESS TOAST ===== */
    .toast{
        position:fixed;bottom:28px;right:28px;z-index:2000;
        background:var(--navy);color:#fff;
        display:flex;align-items:center;gap:12px;
        padding:14px 20px;border-radius:14px;
        box-shadow:0 8px 30px rgba(19,23,107,.25);
        font-size:14px;font-weight:700;
        transform:translateY(80px);opacity:0;
        transition:transform .4s cubic-bezier(.16,1,.3,1),opacity .4s ease;
        pointer-events:none;
    }
    .toast.show{transform:translateY(0);opacity:1;pointer-events:auto;}
    .toast svg{width:20px;height:20px;flex-shrink:0;color:#4ade80;}
    .toast-close{
        background:none;border:none;color:#fff;opacity:.7;
        font-size:18px;cursor:pointer;padding:0 0 0 8px;line-height:1;
    }

    @media (max-width:1180px){
        .two-col{grid-template-columns:1fr;}
        .charts-grid{grid-template-columns:1fr;}
    }
    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:column;position:static;margin:14px;border-radius:16px;}
        .courses-table-wrap{overflow-x:auto;}
    }

    /* ── Phone / tablet: same flexible treatment Student_Profile gets ─── */
    @media (max-width:768px){
        /* Nothing may push the page wider than the screen. Scoped to
           mobile because overflow-x on html/body would break the
           sticky sidebar at desktop widths. */
        html,body{overflow-x:hidden;}

        .main{padding:20px 16px 48px;}
        .topbar{padding:12px 16px;gap:12px;flex-wrap:wrap;}
        .brand h1{font-size:20px;}
        .search-box{min-width:0;flex:1;}
        .panel-head{font-size:17px;padding:12px 16px;}
        .panel-body{padding:18px;}
        .profile-card{padding:20px;gap:18px;}

        /* Charts breathe instead of squeezing bars to nothing */
        .chart{height:170px;gap:8px;}

        /* Courses table → stacked cards. No horizontal scrolling. */
        .courses-table-wrap{overflow-x:visible;}
        .courses-table,
        .courses-table tbody,
        .courses-table tr,
        .courses-table td{display:block;width:100%;}
        .courses-table thead{display:none;}
        .courses-table tr{
            border:1px solid var(--line);border-radius:14px;
            padding:14px;margin-bottom:14px;
        }
        .courses-table td{border-bottom:none;padding:7px 0;}
        .courses-table td[data-label]::before{
            content:attr(data-label);display:block;
            font-size:11px;font-weight:800;color:var(--muted);
            text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;
        }
        .courses-table td[data-label="Course"]::before{display:none;}
        .tbl-course{min-width:0;}
        .progress-cell{min-width:0;}

        /* Edit-profile modal: one field per row, full-bleed panel */
        .form-row{grid-template-columns:1fr;}
        .modal-panel{width:100%;max-width:100%;}
        .modal-body{padding:18px 16px;}
    }

    @media (max-width:520px){
        .main{padding:16px 12px 40px;}
        .panel-body{padding:16px;}
        .panel-head{font-size:16px;}
        .about-row{gap:2px;}
        .about-value{text-align:left;}
        .set-label{min-width:0;}
        .set-value{flex:1 1 100%;min-width:0;}
        .identity h3{gap:8px;}
        .avatar-ring{width:80px;height:80px;}
        .avatar-ring svg{width:44px;height:44px;}
        .identity h3{font-size:20px;}
        .identity-grid{gap:10px 18px;}
        .chart{height:150px;}
        .brand .logo{width:38px;height:38px;}
        .brand h1{font-size:18px;}
        .icon-cluster{gap:8px;}
    }

/* ── Canonical admin topbar ───────────────────────────────────────────── */
/* components/admin-sidebar supplies the authoritative geometry with
   !important; these are local fallbacks so the header still reads correctly
   if that partial is ever removed from this page. */
.topbar-brand{display:inline-flex;align-items:center;gap:12px;color:#fff;
    font-size:1.05rem;font-weight:800;letter-spacing:2px;text-transform:uppercase;
    /* Not a link here — no pointer affordance. */
    cursor:default;user-select:none;}
.brand-logo{width:34px;height:34px;border-radius:50%;background:#fff;
    display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;}
.brand-logo img{width:100%;height:100%;object-fit:contain;}
.topbar-right{display:flex;align-items:center;gap:10px;}
.avatar-btn{width:40px;height:40px;border-radius:50%;background-color:#fff;
    background-size:cover;background-position:center;overflow:hidden;
    color:var(--navy);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;}

/* ── Admin panel/card density ─────────────────────────────────────────── */
/* The faculty geometry is built for pages with charts and tables. These
   panels hold four short rows, so the default header height and the
   space-between that pushed the icon to the far edge both looked wrong. */
.panel-head{
    padding:11px 18px !important;
    justify-content:flex-start !important;
    gap:10px !important;
    font-size:16px !important;
}
.panel-head h4{margin:0;font-size:16px;font-weight:800;}
.panel-head .head-ico{width:26px;height:26px;flex-shrink:0;}
.panel-head .head-ico svg{width:16px;height:16px;}
.panel-body{padding:16px 18px !important;}

/* Identity card: the avatar and the details sat in a very tall band. */
.profile-card{padding:18px 22px !important;gap:20px !important;}
.avatar-ring{width:92px !important;height:92px !important;}
.identity h3{margin:0 0 8px !important;}
.identity-grid{gap:8px 18px !important;}

/* The brand is deliberately not a link on this page. */
.brand.static-brand { cursor: default; user-select: none; }
/* Inline validation errors, matching the faculty form-errors panel. */
.field-err{font-size:12.5px;font-weight:600;color:#b91c1c;background:#fff1f2;
    border:1px solid #fecdd3;border-radius:8px;padding:6px 10px;margin-top:6px;}
.hint-muted{font-size:12px;color:var(--muted);margin-top:6px;}
.btn-remove-avatar{background:#fff;border:1.5px solid #fecdd3;color:#b91c1c;
    border-radius:999px;padding:8px 16px;font-size:13px;font-weight:700;cursor:pointer;}
.btn-remove-avatar:hover{background:#fff1f2;}
</style>
</head>
<body>

{{-- ===== SUCCESS TOAST ===== --}}
@if (session('success'))
<div class="toast" id="successToast">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    {{ session('success') }}
    <button class="toast-close" onclick="document.getElementById('successToast').classList.remove('show')">&times;</button>
</div>
@endif
@include('components.authenticated-topbar', ['user' => auth()->user()])
<div class="layout">

    {{-- Admin sidebar — same collapsible sections as every other admin page --}}
{{-- Main content --}}
    <main class="main">

        <div class="page-head">
            <div>
                <h2>Admin Profile</h2>
                <p>Manage your administrative information and account details</p>
            </div>
            <div class="page-actions">
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button class="btn-logout" type="submit">Logout</button>
                </form>
                <button class="btn-edit-profile" type="button" onclick="openEditModal('personal')">Edit Profile</button>
            </div>
        </div>

        {{-- ══════════════ IDENTITY CARD ══════════════ --}}
        <section class="profile-card">
            <div class="avatar-ring"
                 @if($user->avatar_url ?? null) style="background-image:url('{{ $user->avatar_url }}')" @endif>
                @unless($user->avatar_url ?? null)
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                @endunless
            </div>
            <div class="identity">
                <h3>{{ $user->name ?? 'Administrator' }}<span class="role-pill">{{ $user->role ?? 'Administrator' }}</span></h3>
                <div class="identity-grid">
                    <span class="id-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        {{ $user->phone ?: '—' }}
                    </span>
                    <span class="id-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg>
                        {{ $user->email ?? '—' }}
                    </span>
                    <span class="id-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                        {{ $user->username ?: '—' }}
                    </span>
                    <span class="id-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $user->location ?: '—' }}
                    </span>
                    <span class="id-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M6 9h12"/><path d="M6 13h8"/></svg>
                        {{ $user->user_code ?? '—' }}
                    </span>
                </div>
            </div>
        </section>

        {{-- ══════════════ ABOUT + ACCOUNT ══════════════ --}}
        <div class="two-col">

            <section class="panel" style="margin-bottom:28px;">
                <div class="panel-head">
                    <span class="head-ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    </span>
                    <h4>About Me</h4>
                </div>
                <div class="panel-body">
                    <p class="about-intro">{{ $user->about ?: 'No summary provided yet.' }}</p>
                    <div class="about-row">
                        <span class="about-label">Bio</span>
                        <span class="about-value">{{ $user->bio ?: 'No bio provided yet.' }}</span>
                    </div>
                    <div class="about-row">
                        <span class="about-label">Role</span>
                        <span class="about-value">{{ $user->role ?? 'Administrator' }}</span>
                    </div>
                    <div class="about-row">
                        <span class="about-label">Member Since</span>
                        <span class="about-value">{{ optional($user->created_at)->format('M j, Y') ?? '—' }}</span>
                    </div>
                </div>
            </section>

            <section class="panel" style="margin-bottom:28px;">
                <div class="panel-head">
                    <span class="head-ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span>
                    <h4>Account</h4>
                </div>
                <div class="panel-body">
                    <div class="about-row">
                        <span class="about-label">Username</span>
                        <span class="about-value">{{ $user->username ?: '—' }}</span>
                    </div>
                    <div class="about-row">
                        <span class="about-label">Email</span>
                        <span class="about-value">{{ $user->email ?? '—' }}</span>
                    </div>
                    <div class="about-row">
                        <span class="about-label">Password</span>
                        <span class="about-value">••••••••</span>
                    </div>
                    <div class="about-row">
                        <span class="about-label">Status</span>
                        <span class="about-value">{{ ($user->is_active ?? true) ? 'Active' : 'Inactive' }}</span>
                    </div>
                </div>
            </section>

        </div>
    </main>
</div>

{{-- ══════════════ EDIT PROFILE DRAWER ══════════════ --}}
<div id="editModal" class="modal-overlay" onclick="handleOverlayClick(event)">
    <div class="modal-panel">

        <div class="modal-header">
            <div class="modal-header-left">
                <span class="head-ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                </span>
                <div>
                    <h3>Edit Profile</h3>
                    <p>Update your personal information</p>
                </div>
            </div>
            <button class="modal-close" type="button" onclick="closeEditModal()" aria-label="Close">&times;</button>
        </div>

        <div class="modal-tabs">
            <button class="tab-btn active" data-tab="personal" onclick="switchTab('personal')">Personal Info</button>
            <button class="tab-btn" data-tab="about" onclick="switchTab('about')">About Me</button>
            <button class="tab-btn" data-tab="settings" onclick="switchTab('settings')">Account Settings</button>
        </div>

        <form id="editProfileForm" action="{{ route('admin.profile.update') }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="modal-body">

                @if ($errors->any())
                    <div class="form-errors">
                        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                    </div>
                @endif

                {{-- ─────────── PERSONAL INFO ─────────── --}}
                <div id="tab-personal" class="tab-content active">

                    <div class="avatar-upload-area">
                        <div class="avatar-preview" id="avatarPreview"
                             @if($user->avatar_url ?? null) style="background-image:url('{{ $user->avatar_url }}')" @endif>
                            @unless($user->avatar_url ?? null)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>
                            @endunless
                        </div>
                        <div class="avatar-upload-text">
                            <label class="btn-upload-avatar">
                                Change Photo
                                <input type="file" name="avatar" accept="image/*" hidden onchange="previewAvatar(this)">
                            </label>
                            <button type="button" class="btn-remove-avatar" onclick="clearAvatar()">Remove</button>
                            <p class="hint">JPG or PNG. Resized automatically, so any size is fine.</p>
                        </div>
                        <input type="hidden" id="avatarBase64" name="avatar_base64" value="">
                        <input type="hidden" id="removeAvatar" name="remove_avatar" value="0">
                    </div>

                    <div class="form-divider"></div>

                    {{-- The admin record stores the name in parts. Editing a
                         single combined field folded middle_name and suffix
                         into last_name on every save. --}}
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input id="first_name" name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}" required>
                            @error('first_name')<div class="field-err">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input id="middle_name" name="middle_name" value="{{ old('middle_name', $user->middle_name ?? '') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input id="last_name" name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}" required>
                            @error('last_name')<div class="field-err">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="suffix">Suffix</label>
                            <input id="suffix" name="suffix" value="{{ old('suffix', $user->suffix ?? '') }}" placeholder="Jr., III, ...">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="09XX XXX XXXX">
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input id="location" name="location" value="{{ old('location', $user->location ?? '') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="role">Role / Title</label>
                        <input id="role" name="role" value="{{ old('role', $user->role ?? 'Administrator') }}">
                    </div>
                </div>

                {{-- ─────────── ABOUT ME ─────────── --}}
                <div id="tab-about" class="tab-content">
                    <div class="form-group">
                        <label for="about">About Me</label>
                        <textarea id="about" name="about" rows="4" placeholder="A short summary shown on your profile.">{{ old('about', $user->about ?? '') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" rows="4" placeholder="Anything else worth noting.">{{ old('bio', $user->bio ?? '') }}</textarea>
                    </div>
                </div>

                {{-- ─────────── ACCOUNT SETTINGS ─────────── --}}
                <div id="tab-settings" class="tab-content">

                    <div class="form-section-label">Sign-in Details</div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input id="username" name="username" value="{{ old('username', $user->username ?? '') }}" autocomplete="username" required>
                        <p class="hint-muted">Letters, numbers, dashes and underscores. You can sign in with this or your email.</p>
                        @error('username')<div class="field-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" required>
                        @error('email')<div class="field-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-divider"></div>

                    <div class="form-section-label">Change Password</div>
                    <p class="hint-muted" style="margin-bottom:14px;">Leave these blank to keep your current password.</p>

                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div style="position:relative;display:flex;align-items:center;">
                            <input id="current_password" name="current_password" type="password" autocomplete="current-password" placeholder="••••••••">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('current_password', this)" aria-label="Show password">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        @error('current_password')<div class="field-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div style="position:relative;display:flex;align-items:center;">
                            <input id="password" name="password" type="password" autocomplete="new-password" placeholder="At least 8 characters">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('password', this)" aria-label="Show password">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        @error('password')<div class="field-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password</label>
                        <div style="position:relative;display:flex;align-items:center;">
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Repeat the new password">
                            <button type="button" class="pwd-toggle" onclick="togglePwd('password_confirmation', this)" aria-label="Show password">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

            </div>{{-- /.modal-body --}}

            <div class="modal-footer">
                <button type="button" class="btn-cancel-modal" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>{{-- /.modal-overlay --}}

<script>
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

/* Avatar — resize client-side then store in hidden field (bypasses PHP upload limits) */
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const MAX = 400;
            let w = img.width, h = img.height;
            if (w > h) { if (w > MAX) { h = Math.round(h * MAX / w); w = MAX; } }
            else        { if (h > MAX) { w = Math.round(w * MAX / h); h = MAX; } }

            const canvas = document.createElement('canvas');
            canvas.width  = w;
            canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

            const preview = document.getElementById('avatarPreview');
            preview.style.backgroundImage = `url('${dataUrl}')`;
            preview.innerHTML = '';

            document.getElementById('avatarBase64').value = dataUrl;
            document.getElementById('removeAvatar').value = '0';
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
}

/* Remove the photo — falls back to the silhouette used when none is set. */
function clearAvatar() {
    const preview = document.getElementById('avatarPreview');
    preview.style.backgroundImage = '';
    preview.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"/></svg>';
    document.getElementById('avatarBase64').value = '';
    document.getElementById('removeAvatar').value = '1';
}

/* Keyboard: Esc closes modal */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeEditModal();
});

/* Re-open the drawer automatically if the save failed validation, on the
   tab that actually holds the offending field. */
@if ($errors->any())
openEditModal(@json(
    $errors->has('current_password') || $errors->has('password')
        || $errors->has('email') || $errors->has('username')
    ? 'settings' : 'personal'
));
@endif

/* ====== Success Toast ====== */
(function () {
    const toast = document.getElementById('successToast');
    if (!toast) return;
    requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
    setTimeout(() => toast.classList.remove('show'), 4000);
})();
</script>


{{-- ── Back to top (appears on long pages) ── --}}
<button id="back-to-top-btn" type="button" title="Back to top" aria-label="Back to top"
        onclick="window.scrollTo({top:0,behavior:'smooth'});">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>
<style>
    #back-to-top-btn{position:fixed;right:26px;bottom:26px;z-index:2000;width:48px;height:48px;border-radius:50%;border:none;background:#13176b;color:#fff;display:none;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 10px 22px rgba(19,23,107,.35);transition:transform .15s ease,background .2s ease;}
    #back-to-top-btn:hover{background:#dba617;transform:translateY(-3px);}
    #back-to-top-btn svg{width:22px;height:22px;}
</style>
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

<script>
    /* Collapsible sidebar sections — same helper the other admin pages use. */
    function toggleSection(id) {
        var el = document.getElementById(id);
        if (el) el.classList.toggle('open');
    }
</script>

{{-- Shared Admin sidebar styling (floating card + section pills) --}}
@include('components.admin-sidebar')

{{-- Shared responsiveness layer (drawer nav + grid stacking) --}}
@include('components.responsive')
</body>
</html>
