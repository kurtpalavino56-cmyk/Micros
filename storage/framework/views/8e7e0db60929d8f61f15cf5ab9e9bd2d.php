
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Upskill</title>
    
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('images/PSU-Logo.png')); ?>">
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
    .brand .logo svg{width:30px;height:30px;}
    .brand .logo img{width:100%;height:100%;object-fit:contain;border-radius:50%;padding:3px;}
    .brand h1{font-size:24px;letter-spacing:1px;margin:0;font-weight:800;}
    .nav-pills{display:flex;gap:8px;flex-wrap:wrap;margin-left:auto;align-items:center;}
    .nav-pills a{background:transparent;color:#fff;font-weight:700;padding:10px 18px;border-radius:10px;font-size:15px;transition:color .15s ease, background-color .15s ease;}
    .nav-pills a:hover{color:var(--gold);}
    .nav-pills a.is-active{color:var(--gold);background:rgba(255,255,255,0.08);}
    .icon-cluster{display:flex;align-items:center;gap:14px;}
    .icon-circle{width:42px;height:42px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .icon-circle svg{width:22px;height:22px;color:var(--navy);}

    /* Layout */
    .layout{display:grid;grid-template-columns:264px 1fr;min-height:calc(100vh - 74px);align-items:start;}

    /* Sidebar */
    .sidebar{background:var(--navy);padding:26px 16px;display:flex;flex-direction:column;gap:6px;margin:0 10px 24px 24px;border-radius:22px;box-shadow:0 16px 34px rgba(19,23,107,0.28);height:fit-content;position:sticky;top:0;}
    .side-link{display:flex;align-items:center;gap:14px;padding:14px;border-radius:14px;font-weight:700;font-size:16px;color:#fff;transition:color .15s ease;}
    /* hover: text + icon turn gold (non-active links) */
    .side-link:not(.active):hover{color:var(--gold);}
    .side-link:not(.active):hover .side-icon-box svg{color:var(--gold);}
    .side-link svg{width:26px;height:26px;flex-shrink:0;}
    .side-link.active{background:var(--cyan);color:var(--navy);}
    .side-icon-box{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .side-link.active .side-icon-box svg{color:var(--navy);width:26px;height:26px;}
    .side-link:not(.active) .side-icon-box svg{color:#fff;width:26px;height:26px;transition:color .15s ease;}

    /* Main */
    .main{padding:32px 36px 60px;}
    .page-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:28px;}
    .page-head h2{font-size:30px;margin:0 0 6px;color:var(--navy);}
    .page-head p{margin:0;color:var(--muted);font-size:15px;}
    .btn-outline{background:#fff;border:1.5px solid #c9ccdb;color:#9aa0b4;font-weight:600;padding:12px 24px;border-radius:10px;font-size:15px;}

    /* Stats */
    .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;margin-bottom:36px;}
    /* Gradient stat cards (same design as course Analytics) */
    .stat-card{position:relative;border-radius:18px;box-shadow:var(--shadow);padding:30px 22px;text-align:center;color:#fff;overflow:hidden;min-height:120px;display:flex;flex-direction:column;align-items:center;justify-content:center;}
    .stat-card.c-navy{background:linear-gradient(135deg,var(--navy) 0%,#2a30b0 100%);}
    .stat-card.c-gold{background:linear-gradient(135deg,var(--gold-dark) 0%,#f0c14b 100%);}
    .stat-card.c-cyan{background:linear-gradient(135deg,#2fb3ab 0%,var(--cyan) 100%);}
    .stat-card.c-deep{background:linear-gradient(135deg,var(--navy-deep) 0%,var(--navy) 100%);}
    .stat-top{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:14px;position:relative;z-index:1;}
    .stat-top svg{width:42px;height:42px;color:#fff;}
    .stat-top .num{font-size:44px;font-weight:800;color:#fff;line-height:1;}
    .stat-card .label{font-weight:800;font-size:17px;color:#fff;position:relative;z-index:1;}

    /* Content grid */
    .content-grid{display:grid;grid-template-columns:1fr 360px;gap:28px;}
    .courses-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;}
    .courses-head h3{font-size:24px;margin:0;color:var(--navy);}
    .courses-head .enroll-more{font-weight:700;color:var(--navy);font-size:17px;}

    .course-card{border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);padding:18px;display:flex;align-items:center;gap:18px;margin-bottom:20px;}
    .thumb{width:78px;height:78px;border-radius:12px;background:var(--thumb);flex-shrink:0;background-size:cover;background-position:center;}
    .course-info{flex:1;min-width:0;}
    .course-info h4{margin:0 0 4px;font-size:17px;color:var(--navy);}
    .course-info .cat{font-size:13px;color:var(--muted);margin-bottom:8px;}
    .progress-track{flex:1;height:7px;border-radius:999px;background:#e3e6f0;overflow:hidden;}
    .progress-fill{height:100%;background:var(--navy);border-radius:999px;}
    .pct{font-size:12px;color:var(--muted);margin-top:6px;}
    .btn-start{background:#fff;border:1.5px solid var(--navy);color:var(--navy);font-weight:700;padding:10px 22px;border-radius:10px;font-size:15px;flex-shrink:0;}
    .empty-state{border:1px dashed var(--line);border-radius:16px;padding:30px;text-align:center;color:var(--muted);}

    /* Side panels */
    .panel{border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);overflow:hidden;margin-bottom:24px;min-height:230px;}
    .panel-head{background:var(--gold);color:var(--navy);font-weight:800;font-size:22px;padding:14px 22px;display:flex;align-items:center;justify-content:space-between;}
    .panel-body{padding:18px 22px;color:var(--muted);font-size:14px;}
    .panel-body ul{margin:0;padding-left:18px;}
    .panel-body li{margin-bottom:8px;}

    @media (max-width:980px){
        .layout{grid-template-columns:1fr;}
        .sidebar{flex-direction:row;overflow-x:auto;position:static;margin:14px;border-radius:16px;}
        .stats{grid-template-columns:repeat(2,1fr);}
        .content-grid{grid-template-columns:1fr;}
    }

    /* ── Course areas (Not Yet Completed / Completed Courses) ── */
    .courses-area-title{margin:20px 0 12px;font-size:15px;font-weight:800;color:var(--navy);letter-spacing:.3px;}
    .empty-state-slim{padding:10px 2px 4px;}
    .btn-start.btn-completed{background:var(--green, #15803d);}

    /* ── Progress panel items ── */
    .prog-item{margin-bottom:16px;}
    .prog-item:last-child{margin-bottom:0;}
    .prog-item-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:6px;}
    .prog-item-title{font-weight:700;color:var(--navy);font-size:13px;}
    .prog-item-pct{font-weight:800;color:var(--gold-dark);font-size:13px;white-space:nowrap;}
    .prog-bar{height:8px;border-radius:999px;background:#e8ecf7;overflow:hidden;}
    .prog-bar-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--navy),#3b41c8);transition:width .4s ease;}
    .prog-bar-fill.prog-bar-done{background:linear-gradient(90deg,#1d9e6b,#34d399);}
    .prog-item-sub{margin-top:5px;font-size:11.5px;color:var(--muted);}

    /* ── About Me first-login modal ── */
    .am-overlay{position:fixed;inset:0;z-index:1000;background:rgba(12,15,77,.55);backdrop-filter:blur(3px);display:flex;align-items:center;justify-content:center;padding:20px;}
    .am-card{background:#fff;border-radius:20px;box-shadow:0 24px 60px rgba(12,15,77,.35);width:100%;max-width:520px;max-height:92vh;overflow-y:auto;}
    .am-head{background:linear-gradient(135deg,var(--navy),var(--navy-deep));color:#fff;padding:22px 26px;border-radius:20px 20px 0 0;}
    .am-head h3{margin:0;font-size:20px;letter-spacing:.3px;}
    .am-head p{margin:6px 0 0;font-size:13px;color:#c9cdf5;}
    .am-body{padding:22px 26px 26px;}
    .am-field{margin-bottom:14px;}
    .am-field label{display:block;font-size:12.5px;font-weight:700;color:var(--navy);margin-bottom:5px;}
    .am-field input,.am-field select,.am-field textarea{width:100%;border:1.5px solid var(--line);border-radius:10px;padding:9px 12px;font-size:13.5px;font-family:inherit;color:var(--ink);outline:none;transition:border-color .2s;}
    .am-field input:focus,.am-field select:focus,.am-field textarea:focus{border-color:var(--gold);}
    .am-field textarea{resize:vertical;min-height:64px;}
    .am-hint{font-size:11px;color:var(--muted);margin-top:4px;}
    .am-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .am-errors{background:#fde8e8;border:1px solid #f5b5b5;color:#b91c1c;border-radius:10px;padding:10px 14px;font-size:12.5px;margin-bottom:14px;}
    /* Per-field "Needs to Fill" hint */
    .am-needs{display:none;color:#b91c1c;font-size:12px;font-weight:700;margin-top:5px;}
    /* ── Skills pickers: mini button + floating modal ───────────────── */
    .skillpick{position:relative;}
    .skillpick-mini{
        display:inline-flex;align-items:center;gap:8px;
        background:#fff;border:1.5px solid var(--line);border-radius:999px;
        padding:9px 14px 9px 18px;font-size:13.5px;font-weight:700;color:#6b7280;
        cursor:pointer;transition:border-color .18s,color .18s,background .18s;
    }
    .skillpick-mini:hover{border-color:var(--navy);color:var(--navy);}
    .skillpick-mini svg{width:14px;height:14px;}
    .skillpick-mini.has-value{background:var(--navy);border-color:var(--navy);color:#fff;}

    .skillpick-backdrop{position:fixed;inset:0;z-index:1300;background:rgba(9,12,45,.45);}
    .skillpick-backdrop[hidden]{display:none;}

    .skillpick-pop{
        position:fixed;z-index:1301;top:50%;left:50%;transform:translate(-50%,-50%);
        width:min(460px,calc(100vw - 32px));max-height:min(82vh,640px);
        display:flex;flex-direction:column;
        background:#fff;border:1.5px solid #e6e3f7;border-radius:20px;
        box-shadow:0 30px 70px rgba(9,12,45,.38);padding:20px;
    }
    .skillpick-pop[hidden]{display:none;}

    .skillpick-hd{margin-bottom:12px;flex-shrink:0;}
    .skillpick-hd strong{display:block;font-size:15px;color:var(--navy);}
    .skillpick-hd span{display:block;font-size:11.5px;color:#8a8fa8;margin-top:2px;line-height:1.4;}

    .skillpick-search{position:relative;margin-bottom:10px;flex-shrink:0;}
    .skillpick-search svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#8a8fa8;}
    .skillpick-search input{width:100%;border:1.5px solid #e6e3f7;border-radius:999px;
        padding:9px 14px 9px 34px;font-size:13.5px;color:var(--ink);outline:none;}
    .skillpick-search input:focus{border-color:var(--navy);}

    .skillpick-count{display:flex;align-items:center;justify-content:space-between;gap:8px;
        font-size:11.5px;color:#8a8fa8;margin-bottom:8px;flex-shrink:0;}
    .skillpick-clearall{border:none;background:transparent;color:var(--navy);font-size:11.5px;
        font-weight:700;cursor:pointer;padding:0;}
    .skillpick-clearall:hover{text-decoration:underline;}

    .skillpick-list{flex:1 1 auto;min-height:0;overflow-y:auto;display:flex;
        flex-direction:column;gap:10px;padding:2px 2px 4px;}
    .skillpick-group[hidden]{display:none;}
    .skillpick-group-hd{font-size:10.5px;font-weight:800;letter-spacing:.6px;
        text-transform:uppercase;color:#a8adc4;margin:14px 0 8px 6px;}
    .skillpick-group:first-child .skillpick-group-hd{margin-top:2px;}
    .skillpick-empty{padding:18px 6px;text-align:center;color:#8a8fa8;font-size:12.5px;}

    .skillpick-opt{position:relative;display:flex;align-items:center;gap:12px;cursor:pointer;
        background:#fff;border:1px solid #eceaf8;border-radius:999px;padding:11px 16px;
        font-size:13.5px;color:var(--ink);transition:border-color .15s,box-shadow .15s;}
    .skillpick-opt:hover{border-color:#c9c4ee;}
    .skillpick-opt[hidden]{display:none;}
    .skillpick-opt input{position:absolute;opacity:0;pointer-events:none;}
    .skillpick-tick{width:19px;height:19px;border-radius:50%;flex-shrink:0;
        border:1.5px solid #d5d2e8;background:#fff;display:inline-flex;
        align-items:center;justify-content:center;transition:background .15s,border-color .15s;}
    .skillpick-tick svg{width:11px;height:11px;color:#fff;opacity:0;}
    .skillpick-opt.on{border-color:#c9c4ee;box-shadow:0 1px 4px rgba(19,23,107,.06);}
    .skillpick-opt.on .skillpick-tick{background:var(--navy);border-color:var(--navy);}
    .skillpick-opt.on .skillpick-tick svg{opacity:1;}
    .skillpick-name{font-weight:600;}

    .skillpick-ft{display:flex;gap:8px;justify-content:flex-end;margin-top:14px;
        flex-shrink:0;padding-top:14px;border-top:1px solid #eceaf8;}
    .skillpick-btn{border:1.5px solid var(--line);background:#fff;color:var(--navy);
        border-radius:10px;padding:8px 16px;font-size:13.5px;font-weight:700;cursor:pointer;}
    .skillpick-btn:hover{background:#f3f6ff;}
    .skillpick-btn.primary{background:var(--navy);border-color:var(--navy);color:#fff;}

    @media (max-width:520px){ .skillpick-pop{padding:16px;border-radius:16px;} }
    .am-field.invalid .am-needs{display:block;}
    .am-field.invalid input,
    .am-field.invalid select,
    .am-field.invalid textarea,
    .am-field.invalid .skillpick-mini{border-color:#dc2626 !important;background:#fff7f7;color:#b91c1c;}
    .am-actions{display:flex;gap:10px;margin-top:18px;}
    .am-save{flex:1;background:var(--gold);color:#fff;border:none;border-radius:12px;padding:12px;font-size:14px;font-weight:800;cursor:pointer;letter-spacing:.3px;transition:background .2s;}
    .am-save:hover{background:var(--gold-dark);}
    /* Skills checkbox dropdown */
    @media (max-width:560px){.am-row{grid-template-columns:1fr;}}
</style>

<style>
    :root {
        --pm-navy: #08245f;
        --pm-navy-deep: #061b49;
        --pm-blue: #2f68ba;
        --pm-blue-soft: #eef5ff;
        --pm-gold: #e5b43c;
        --pm-gold-soft: #fff5d8;
        --pm-ink: #14254b;
        --pm-muted: #6e7e9b;
        --pm-line: #e2e9f4;
        --pm-surface: #ffffff;
        --pm-canvas: #f5f8fd;
        --pm-shadow: 0 12px 32px rgba(8, 36, 95, .08);
        --pm-shadow-hover: 0 18px 40px rgba(8, 36, 95, .14);
    }
    html, body { background: var(--pm-canvas); color: var(--pm-ink); }
    body { font-family: 'Inter', 'Segoe UI', sans-serif; }
    a { color: inherit; }
    button, input { font: inherit; }

    .topbar {
        min-height: 76px; padding: 12px 30px; gap: 22px;
        background: rgba(255,255,255,.96); color: var(--pm-navy);
        border-bottom: 1px solid var(--pm-line);
        box-shadow: 0 6px 20px rgba(8,36,95,.06);
        position: sticky; top: 0; z-index: 1000;
        backdrop-filter: blur(16px);
    }
    .brand { gap: 12px; color: var(--pm-navy); }
    .brand .logo { width: 42px; height: 42px; border: 1px solid #dce6f4; box-shadow: 0 3px 12px rgba(8,36,95,.08); }
    .brand h1 { margin: 0; font-size: 21px; letter-spacing: .01em; font-weight: 800; }
    .brand-copy { display: flex; flex-direction: column; gap: 1px; }
    .brand-copy small { color: var(--pm-muted); font-size: 10px; letter-spacing: .04em; font-weight: 600; }
    .nav-pills { gap: 4px; }
    .nav-pills a { color: var(--pm-muted); padding: 9px 13px; border-radius: 9px; font-size: 13px; font-weight: 700; }
    .nav-pills a:hover, .nav-pills a.is-active { color: var(--pm-navy); background: var(--pm-blue-soft); }
    .top-search { display: flex; align-items: center; gap: 9px; width: min(290px, 25vw); margin-left: auto; padding: 10px 14px; border: 1px solid var(--pm-line); border-radius: 999px; color: var(--pm-muted); }
    .top-search svg { width: 17px; height: 17px; flex: 0 0 auto; }
    .top-search input { width: 100%; min-width: 0; border: 0; outline: 0; background: transparent; color: var(--pm-ink); font-size: 12px; }
    .top-search input::placeholder { color: #9aa8bd; }
    .icon-cluster { gap: 9px; }
    .icon-circle { width: 38px; height: 38px; border: 1px solid var(--pm-line); background: #fff; color: var(--pm-navy); transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
    .icon-circle:hover { transform: translateY(-2px); border-color: #b9cce8; box-shadow: 0 8px 18px rgba(8,36,95,.10); }
    .logout-btn { background: #fff5f5; border: 1px solid #f0cccc; color: #b84444; border-radius: 999px; padding: 8px 12px; font-weight: 700; cursor: pointer; }

    .layout { grid-template-columns: 244px minmax(0, 1fr); min-height: calc(100vh - 76px); }
    .sidebar { background: linear-gradient(180deg, var(--pm-navy) 0%, var(--pm-navy-deep) 100%); margin: 0; min-height: calc(100vh - 76px); border-radius: 0; padding: 24px 14px; box-shadow: 10px 0 28px rgba(8,36,95,.12); position: sticky; top: 76px; }
    .sidebar::after { content: ''; display: block; margin: auto 10px 6px; height: 130px; border-radius: 12px; background: linear-gradient(180deg, transparent, rgba(229,180,60,.17)), url('<?php echo e(asset('Images/PSU_Front_Building.jpg')); ?>') center/cover; opacity: .72; }
    .side-link { gap: 12px; padding: 12px 13px; border-radius: 11px; color: rgba(255,255,255,.78); font-size: 13px; font-weight: 600; transition: color .2s ease, background .2s ease, transform .2s ease; }
    .side-link:hover { color: #fff; background: rgba(255,255,255,.08); transform: translateX(2px); }
    .side-link.active { background: var(--pm-gold); color: var(--pm-navy); box-shadow: 0 7px 16px rgba(229,180,60,.18); }
    .side-icon-box { width: 30px; height: 30px; }
    .side-link svg { width: 22px; height: 22px; }
    .side-link.active .side-icon-box svg, .side-link:not(.active) .side-icon-box svg { color: currentColor; width: 22px; height: 22px; }

    .main { padding: 30px 34px 58px; min-width: 0; }
    .hero { position: relative; overflow: hidden; min-height: 190px; border-radius: 18px; padding: 30px 34px; margin-bottom: 22px; color: #fff; background: linear-gradient(100deg, rgba(8,36,95,.98) 0%, rgba(20,76,145,.90) 58%, rgba(20,76,145,.52) 100%), url('<?php echo e(asset('Images/PSU_Front_Building.jpg')); ?>') right center/cover; box-shadow: var(--pm-shadow); }
    .hero::after { content: ''; position: absolute; right: -5%; top: -80px; width: 320px; height: 320px; border: 1px solid rgba(255,255,255,.16); border-radius: 50%; box-shadow: 0 0 0 25px rgba(255,255,255,.04), 0 0 0 52px rgba(255,255,255,.03); }
    .hero-copy { position: relative; z-index: 1; max-width: 650px; }
    .eyebrow { margin: 0 0 7px; color: #f7d889; font-size: 12px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
    .hero h2 { margin: 0 0 8px; color: #fff; font-size: clamp(29px, 3.2vw, 44px); line-height: 1.08; letter-spacing: -.035em; }
    .hero p { max-width: 560px; margin: 0; color: rgba(255,255,255,.78); font-size: 14px; }
    .hero-meta { display: flex; gap: 9px; flex-wrap: wrap; margin-top: 17px; }
    .hero-meta span { border: 1px solid rgba(255,255,255,.19); background: rgba(255,255,255,.10); border-radius: 999px; padding: 6px 11px; font-size: 11px; font-weight: 700; }
    .hero-action { position: absolute; right: 31px; bottom: 28px; z-index: 2; background: var(--pm-gold); color: var(--pm-navy); border-radius: 999px; padding: 11px 17px; font-size: 12px; font-weight: 800; }

    .stats { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 22px; }
    .stat-card { min-height: 118px; padding: 18px; align-items: flex-start; justify-content: space-between; text-align: left; color: var(--pm-ink); background: var(--pm-surface); border: 1px solid var(--pm-line); border-radius: 14px; box-shadow: var(--pm-shadow); transition: transform .22s ease, box-shadow .22s ease; }
    .stat-card:hover { transform: translateY(-3px); box-shadow: var(--pm-shadow-hover); }
    .stat-card.c-navy, .stat-card.c-gold, .stat-card.c-cyan, .stat-card.c-deep { background: var(--pm-surface); color: var(--pm-ink); }
    .stat-top { justify-content: flex-start; width: 100%; margin: 0 0 9px; gap: 11px; }
    .stat-top svg { width: 35px; height: 35px; padding: 8px; border-radius: 50%; color: var(--pm-navy); background: var(--pm-blue-soft); }
    .c-gold .stat-top svg { color: #b98108; background: var(--pm-gold-soft); }
    .c-cyan .stat-top svg { color: #168b88; background: #e2f7f4; }
    .c-deep .stat-top svg { color: #4b65bc; background: #edf0ff; }
    .stat-top .num { color: var(--pm-navy); font-size: 30px; font-weight: 800; line-height: 1; }
    .stat-card .label { color: var(--pm-ink); font-size: 12px; font-weight: 800; }
    .stat-card::after { content: 'View details \203A'; color: var(--pm-muted); font-size: 10px; font-weight: 700; }

    .content-grid { grid-template-columns: minmax(0, 1.5fr) minmax(300px, .85fr); gap: 18px; }
    .courses-head { margin-bottom: 11px; }
    .courses-head h3 { color: var(--pm-navy); font-size: 21px; letter-spacing: -.02em; }
    .courses-head .enroll-more { color: var(--pm-blue); font-size: 12px; }
    .dashboard-chevron { display: inline-block; margin-left: 2px; font-size: 1.8em; line-height: .7; vertical-align: -0.08em; font-weight: 800; }
    .courses-area-title { color: var(--pm-navy); margin: 16px 0 9px; font-size: 11px; text-transform: uppercase; letter-spacing: .12em; }
    .course-card { margin-bottom: 10px; padding: 13px; gap: 13px; background: var(--pm-surface); border: 1px solid var(--pm-line); border-radius: 13px; box-shadow: 0 5px 18px rgba(8,36,95,.05); transition: transform .2s ease, box-shadow .2s ease; }
    .course-card:hover { transform: translateY(-2px); box-shadow: var(--pm-shadow); }
    .thumb { width: 72px; height: 62px; border-radius: 10px; background: linear-gradient(135deg, #dbeafe, #b9d4f4); }
    .course-info h4 { color: var(--pm-navy); font-size: 14px; }
    .course-info .cat { color: var(--pm-muted); margin-bottom: 7px; font-size: 10px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .progress-track { height: 7px; background: #e8eef7; }
    .progress-fill { background: linear-gradient(90deg, var(--pm-blue), #6ea6e8); }
    .pct { color: var(--pm-muted); margin-top: 5px; font-size: 10px; }
    .btn-start { background: var(--pm-blue); border: 1px solid var(--pm-blue); color: #fff; border-radius: 999px; padding: 8px 13px; font-size: 11px; font-weight: 800; }
    .btn-start:hover { background: var(--pm-navy); border-color: var(--pm-navy); color: #fff; }
    .btn-start.btn-completed { background: #168457; border-color: #168457; color: #fff; }
    .btn-start.btn-completed:hover { background: #106443; border-color: #106443; }

    .panel { margin-bottom: 14px; min-height: 0; overflow: hidden; border: 1px solid var(--pm-line); border-radius: 14px; background: var(--pm-surface); box-shadow: var(--pm-shadow); }
    .panel-head { padding: 14px 16px; background: #fff; color: var(--pm-navy); border-bottom: 1px solid var(--pm-line); font-size: 15px; }
    .panel-head a { color: var(--pm-blue) !important; font-size: 11px; }
    .panel-body { padding: 15px 16px; color: var(--pm-muted); font-size: 12px; }
    .prog-item { margin-bottom: 14px; }
    .prog-item-title { color: var(--pm-ink); font-size: 11px; }
    .prog-item-pct { color: var(--pm-blue); font-size: 11px; }
    .prog-bar { height: 7px; background: #eaf0f8; }
    .prog-bar-fill { background: linear-gradient(90deg, var(--pm-blue), #76b1ed); }
    .prog-bar-fill.prog-bar-done { background: linear-gradient(90deg, #1c9b67, #59c997); }
    .prog-item-sub { color: var(--pm-muted); font-size: 10px; }

    .featured-course { margin-bottom: 14px; padding: 18px; min-height: 205px; border-radius: 14px; color: #fff; background: linear-gradient(145deg, #08245f, #174d96); box-shadow: var(--pm-shadow); position: relative; overflow: hidden; }
    .featured-course::after { content: ''; position: absolute; right: -35px; top: -40px; width: 145px; height: 145px; border: 18px solid rgba(229,180,60,.78); transform: rotate(32deg); }
    .featured-course small { color: #f8d982; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    .featured-course h4 { position: relative; z-index: 1; margin: 24px 0 7px; color: #fff; font-size: 21px; line-height: 1.12; }
    .featured-course p { position: relative; z-index: 1; max-width: 280px; margin: 0 0 14px; color: rgba(255,255,255,.75); font-size: 11px; }
    .featured-course a { position: relative; z-index: 1; display: inline-block; border-radius: 999px; padding: 9px 14px; background: var(--pm-gold); color: var(--pm-navy); font-size: 11px; font-weight: 800; }

    @media (max-width: 1120px) { .top-search { width: 220px; } .stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 980px) { .layout { grid-template-columns: 1fr; } .sidebar { min-height: 0; position: static; flex-direction: row; overflow-x: auto; border-radius: 0; } .sidebar::after { display: none; } .content-grid { grid-template-columns: 1fr; } .hero-action { position: static; display: inline-block; margin-top: 16px; } }
    @media (max-width: 700px) { .topbar { padding: 11px 16px; } .top-search, .nav-pills { display: none; } .main { padding: 20px 16px 42px; } .hero { padding: 24px 22px; } .hero h2 { font-size: 29px; } .stats { gap: 9px; } .stat-card { min-height: 105px; padding: 13px; } .stat-top .num { font-size: 25px; } .course-card { align-items: flex-start; flex-wrap: wrap; } .course-info { min-width: calc(100% - 88px); } .btn-start { margin-left: 85px; } }
    @media (max-width: 480px) { .stats { grid-template-columns: 1fr 1fr; } .stat-card .label { font-size: 10px; } .hero-meta { display: none; } }
</style>
</head>
<body>

<?php echo $__env->make('components.student-navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="layout student-sidebar-layout">
    <?php echo $__env->make('components.student-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="main">
        <?php $featuredCourse = $inProgressCourses->first() ?? $completedCourses->first(); ?>
        <section class="hero">
            <div class="hero-copy">
                <p class="eyebrow">Continue your learning</p>
                <h2>Welcome back, <?php echo e($user->name ?? 'Student'); ?></h2>
                <p>Build in-demand skills, earn microcredentials, and take the next step in your Penn State journey.</p>
                <div class="hero-meta"><span>Student ID: <?php echo e($user->student_id ?? '—'); ?></span><span>Flexible learning</span><span>Recognized credentials</span></div>
            </div>
            <a href="<?php echo e(route('courses.browse')); ?>" class="hero-action">Explore courses</a>
        </section>

        <section class="stats" aria-label="Learning summary">
            <div class="stat-card c-navy"><div class="stat-top"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h12v20l-6-4-6 4V2z"/></svg><span class="num"><?php echo e($stats['active_courses'] ?? 0); ?></span></div><div class="label">Courses in progress</div></div>
            <div class="stat-card c-gold"><div class="stat-top"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg><span class="num"><?php echo e($stats['completed'] ?? 0); ?></span></div><div class="label">Completed credentials</div></div>
            <div class="stat-card c-cyan"><div class="stat-top"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5" stroke="currentColor" fill="none" stroke-width="2"/></svg><span class="num"><?php echo e($stats['badges_earned'] ?? 0); ?></span></div><div class="label">Skills and badges</div></div>
            <div class="stat-card c-deep"><div class="stat-top"><svg viewBox="0 0 24 24" fill="none"><path d="M12 2l3 6 7 1-5 5 1.5 7L12 17l-6.5 4L7 14 2 9l7-1 3-6z" fill="currentColor"/></svg><span class="num"><?php echo e($stats['certificates'] ?? 0); ?></span></div><div class="label">Certificates earned</div></div>
        </section>

        <div class="content-grid">
            <section class="courses-col">
                <div class="courses-head"><h3>Your course progress</h3><a href="<?php echo e(route('courses.browse')); ?>" class="enroll-more">View all courses <span class="dashboard-chevron">&rsaquo;</span></a></div>
                <?php $inProgressCourses = $inProgressCourses ?? collect(); $completedCourses = $completedCourses ?? collect(); ?>
                <?php if($inProgressCourses->isEmpty() && $completedCourses->isEmpty()): ?>
                    <div class="empty-state">You haven't enrolled in any courses yet.<br><a href="<?php echo e(route('courses.browse')); ?>" class="enroll-more">Browse courses to get started</a></div>
                <?php else: ?>
                    <?php $__empty_1 = true; $__currentLoopData = $inProgressCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="course-card"><div class="thumb" <?php if($course->thumbnail_url): ?> style="background-image:url('<?php echo e($course->thumbnail_url); ?>')" <?php endif; ?>></div><div class="course-info"><h4><?php echo e($course->title); ?></h4><?php if($course->category): ?><div class="cat"><?php echo e($course->category); ?></div><?php endif; ?><div class="progress-track"><div class="progress-fill" style="width:<?php echo e($course->progress_percent ?? 0); ?>%"></div></div><div class="pct"><?php echo e($course->progress_percent ?? 0); ?>% complete</div></div><a href="<?php echo e(route('courses.show', $course->id)); ?>"><button class="btn-start" type="button"><?php echo e(($course->progress_percent ?? 0) > 0 ? 'Continue' : 'Start'); ?></button></a></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <div class="empty-state empty-state-slim">Nothing in progress right now.</div> <?php endif; ?>
                    <?php if($completedCourses->isNotEmpty()): ?><h4 class="courses-area-title">Completed courses</h4><?php endif; ?>
                    <?php $__currentLoopData = $completedCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="course-card"><div class="thumb" <?php if($course->thumbnail_url): ?> style="background-image:url('<?php echo e($course->thumbnail_url); ?>')" <?php endif; ?>></div><div class="course-info"><h4><?php echo e($course->title); ?></h4><?php if($course->category): ?><div class="cat"><?php echo e($course->category); ?></div><?php endif; ?><div class="progress-track"><div class="progress-fill" style="width:100%"></div></div><div class="pct">Completed</div></div><a href="<?php echo e(route('courses.show', $course->id)); ?>"><button class="btn-start btn-completed" type="button">Review</button></a></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </section>

            <aside class="side-col">
                <?php if($featuredCourse): ?>
                <div class="featured-course"><small>Featured course</small><h4><?php echo e(\Illuminate\Support\Str::limit($featuredCourse->title, 46)); ?></h4><p>Keep building practical skills and move closer to your next microcredential.</p><a href="<?php echo e(route('courses.show', $featuredCourse->id)); ?>">Continue course <span class="dashboard-chevron">&rsaquo;</span></a></div>
                <?php endif; ?>
                <div class="panel"><div class="panel-head"><span>Learning progress</span><a href="<?php echo e(route('analytics.index')); ?>">View analytics <span class="dashboard-chevron">&rsaquo;</span></a></div><div class="panel-body"><?php $__empty_1 = true; $__currentLoopData = $progress ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="prog-item"><div class="prog-item-head"><span class="prog-item-title"><?php echo e(\Illuminate\Support\Str::limit($item->title, 26)); ?></span><span class="prog-item-pct"><?php echo e($item->progress_percent); ?>%</span></div><div class="prog-bar"><div class="prog-bar-fill<?php echo e($item->is_completed ? ' prog-bar-done' : ''); ?>" style="width: <?php echo e($item->progress_percent); ?>%;"></div></div><div class="prog-item-sub"><?php if($item->is_completed): ?> Completed <?php elseif($item->progress_percent > 0): ?> In progress · <?php echo e($item->completed_lessons); ?> lesson<?php echo e($item->completed_lessons === 1 ? '' : 's'); ?> done <?php else: ?> Not started yet <?php endif; ?></div></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p>No progress activity yet. Enroll in a course to start tracking your progress.</p><?php endif; ?></div></div>
                <?php if(($stackingFrameworks ?? collect())->isNotEmpty()): ?>
                    <div class="panel"><div class="panel-head"><span>Stacking progress</span><a href="<?php echo e(route('stacking.progress')); ?>">View all <span class="dashboard-chevron">&rsaquo;</span></a></div><div class="panel-body">
                        <?php $__currentLoopData = $stackingFrameworks->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stacking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <p style="margin:0 0 8px;font-weight:700;"><?php echo e(\Illuminate\Support\Str::limit($stacking['framework']->name, 28)); ?></p>
                            <p style="margin:0 0 12px;color:#718096;font-size:12px;"><?php echo e($stacking['completed_required_count']); ?> of <?php echo e($stacking['total_required_count']); ?> required microcredentials completed</p>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div></div>
                <?php endif; ?>
                <div class="panel"><div class="panel-head"><span>Badges earned</span><a href="<?php echo e(route('badges.index')); ?>">View all <span class="dashboard-chevron">&rsaquo;</span></a></div><div class="panel-body"><?php $__empty_1 = true; $__currentLoopData = $badges ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><p><?php echo e($badge->name ?? $badge); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p>No badges earned yet.</p><?php endif; ?></div></div>
            </aside>
        </div>
    </main>
</div>

<?php
    // Only this form's own validation failures should re-open it. Checking
    // $errors->any() re-opened the modal for completed profiles whenever any
    // other form in the app flashed an error.
    $aboutFields = ['date_of_birth', 'gender', 'education', 'bio', 'skills_have', 'skills_want'];
    $aboutHasErrors = $errors->hasAny($aboutFields);
?>

<?php if(($show_about_form ?? false) || $aboutHasErrors): ?>
<div class="am-overlay" id="about-me-overlay">
    <div class="am-card">
        <div class="am-head">
            <h3>About Me</h3>
            <p>Tell us a bit about yourself — this personalizes your course recommendations.</p>
        </div>
        <div class="am-body">
            <?php if($aboutHasErrors): ?>
                <div class="am-errors">
                    <strong>Please fix the following:</strong>
                    <ul style="margin:6px 0 0;padding-left:18px;">
                        <?php $__currentLoopData = $aboutFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aboutField): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = $errors->get($aboutField); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="POST" action="<?php echo e(route('profile.complete')); ?>"
                  id="aboutMeForm" novalidate>
                <?php echo csrf_field(); ?>
                <div class="am-row">
                    <div class="am-field">
                        <label for="am-dob">Date of Birth</label>
                        <input type="date" id="am-dob" name="date_of_birth" value="<?php echo e(old('date_of_birth')); ?>" max="<?php echo e(now()->toDateString()); ?>" required>
                        <p class="am-needs" data-for="date_of_birth">Needs to Fill</p>
                    </div>
                    <div class="am-field">
                        <label for="am-gender">Gender</label>
                        <select id="am-gender" name="gender" required>
                            <option value="" disabled <?php echo e(old('gender') ? '' : 'selected'); ?>>Select…</option>
                            <option value="Male" <?php echo e(old('gender') === 'Male' ? 'selected' : ''); ?>>Male</option>
                            <option value="Female" <?php echo e(old('gender') === 'Female' ? 'selected' : ''); ?>>Female</option>
                            <option value="Prefer not to say" <?php echo e(old('gender') === 'Prefer not to say' ? 'selected' : ''); ?>>Prefer not to say</option>
                        </select>
                        <p class="am-needs">Needs to Fill</p>
                    </div>
                </div>
                <div class="am-field">
                    <label for="am-education">Education</label>
                    <select id="am-education" name="education" required>
                        <option value="" disabled <?php echo e(old('education') ? '' : 'selected'); ?>>Select your highest attainment…</option>
                        <?php $__currentLoopData = ['Senior High School', 'High School Graduate', 'Vocational / Technical', 'Undergraduate (College)', "Bachelor's Degree", "Master's Degree", 'Doctorate / PhD']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($level); ?>" <?php echo e(old('education') === $level ? 'selected' : ''); ?>><?php echo e($level); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <p class="am-needs">Needs to Fill</p>
                </div>
                <div class="am-field">
                    <label for="am-bio">Bio</label>
                    <textarea id="am-bio" name="bio" required placeholder="A short introduction about yourself…"><?php echo e(old('bio')); ?></textarea>
                    <p class="am-needs">Needs to Fill</p>
                </div>
                
                <div class="am-field">
                    <label for="am-school">Last school, college, or university you attended</label>
                    <select id="am-school" name="school" required onchange="amSchoolChanged(this)">
                        <option value="" disabled <?php echo e(old('school') ? '' : 'selected'); ?>>Select your school…</option>
                        <?php $__currentLoopData = \App\Support\SchoolCatalog::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($school); ?>" <?php echo e(old('school') === $school ? 'selected' : ''); ?>><?php echo e($school); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e(\App\Support\SchoolCatalog::OTHER); ?>" <?php echo e(old('school') === \App\Support\SchoolCatalog::OTHER ? 'selected' : ''); ?>>Other (please specify)</option>
                    </select>
                    <input type="text" id="am-school-other" name="school_other"
                           placeholder="Type the name of your school"
                           value="<?php echo e(old('school_other')); ?>"
                           style="margin-top:8px;<?php echo e(old('school') === \App\Support\SchoolCatalog::OTHER ? '' : 'display:none;'); ?>">
                    <p class="am-needs">Needs to Fill</p>
                </div>
                <?php
                    $skillOptions = $skill_options ?? [
                        'HTML', 'CSS', 'JavaScript', 'Python', 'Java', 'PHP', 'SQL',
                        'Computer Networking', 'Video Editing', 'Photo Editing', 'Graphic Design',
                        'Data Analysis', 'Excel', 'Digital Marketing', 'Writing', 'Public Speaking',
                        'Communication', 'Leadership', 'Singing', 'Dancing', 'Drawing', 'Cooking',
                        'Hacking', 'Baking',
                    ];
                    $oldHave = (array) old('skills_have', []);
                    $oldWant = (array) old('skills_want', []);
                ?>
                
                <?php $__currentLoopData = [
                    ['key' => 'have', 'field' => 'skills_have',  'label' => 'Skills You already Have?',
                     'saved' => $oldHave, 'hint' => 'Tick all that apply. These will also appear on your Profile skills list.',
                     'title' => 'Skills you already have'],
                    ['key' => 'want', 'field' => 'skills_want', 'label' => 'Skills You want to learn',
                     'saved' => $oldWant, 'hint' => 'We use this to recommend the most suitable courses for you.',
                     'title' => 'Skills you want to learn'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $picker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="am-field">
                        <label><?php echo e($picker['label']); ?></label>

                        <div class="skillpick" data-picker="<?php echo e($picker['key']); ?>">
                            <button type="button"
                                    class="skillpick-mini <?php echo e(count($picker['saved']) ? 'has-value' : ''); ?>"
                                    onclick="openSkillPick('<?php echo e($picker['key']); ?>')">
                                <span class="skillpick-mini-label">
                                    <?php echo e(count($picker['saved']) ? count($picker['saved']) . ' selected' : 'None'); ?>

                                </span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                    <path d="M6 9l6 6 6-6"/>
                                </svg>
                            </button>

                            <div class="skillpick-backdrop" hidden
                                 onclick="cancelSkillPick('<?php echo e($picker['key']); ?>')"></div>

                            <div class="skillpick-pop" hidden role="dialog" aria-modal="true"
                                 aria-label="<?php echo e($picker['title']); ?>">
                                <div class="skillpick-hd">
                                    <strong><?php echo e($picker['title']); ?></strong>
                                    <span><?php echo e($picker['hint']); ?></span>
                                </div>

                                <div class="skillpick-search">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                        <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                                    </svg>
                                    <input type="text" autocomplete="off"
                                           placeholder="Search <?php echo e(count($skillOptions)); ?> skills…"
                                           oninput="filterSkillPick('<?php echo e($picker['key']); ?>', this.value)">
                                </div>

                                <div class="skillpick-count">
                                    <span class="skillpick-count-line">No skills selected</span>
                                    <button type="button" class="skillpick-clearall"
                                            onclick="clearSkillPick('<?php echo e($picker['key']); ?>')">Clear all</button>
                                </div>

                                <div class="skillpick-list">
                                    <?php $__currentLoopData = ($skill_groups ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $groupSkills): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="skillpick-group">
                                            <div class="skillpick-group-hd"><?php echo e($groupName); ?></div>
                                            <?php $__currentLoopData = $groupSkills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <label class="skillpick-opt <?php echo e(in_array($skill, $picker['saved']) ? 'on' : ''); ?>"
                                                       data-skill="<?php echo e(strtolower($skill)); ?>"
                                                       data-groupname="<?php echo e(strtolower($groupName)); ?>">
                                                    <input type="checkbox" name="<?php echo e($picker['field']); ?>[]"
                                                           value="<?php echo e($skill); ?>"
                                                           <?php if(in_array($skill, $picker['saved'])): echo 'checked'; endif; ?>>
                                                    <span class="skillpick-tick">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2">
                                                            <path d="M5 12.5l5 5 9-10"/>
                                                        </svg>
                                                    </span>
                                                    <span class="skillpick-name"><?php echo e($skill); ?></span>
                                                </label>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <div class="skillpick-empty" hidden>No skills match that search.</div>
                                </div>

                                <div class="skillpick-ft">
                                    <button type="button" class="skillpick-btn"
                                            onclick="cancelSkillPick('<?php echo e($picker['key']); ?>')">Cancel</button>
                                    <button type="button" class="skillpick-btn primary"
                                            onclick="saveSkillPick('<?php echo e($picker['key']); ?>')">Save</button>
                                </div>
                            </div>
                        </div>

                        <div class="am-hint"><?php echo e($picker['hint']); ?></div>
                        <p class="am-needs">Needs to Fill</p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <div class="am-actions">
                    <button type="submit" class="am-save">Save and Continue</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>


<script>
    /* ── Skills pickers ────────────────────────────────────────────────
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
       Manufacturing, Therapy) — keep both copies in step. */
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
</script>

<script>
    // Every About Me field is required. Native bubbles only surface one
    // field at a time and vanish on scroll, so mark each empty field with
    // its own "Needs to Fill" line instead.
    (function () {
        var form = document.getElementById('aboutMeForm');
        if (!form) return;

        function fieldOf(el) { return el.closest('.am-field'); }

        function markEmpty(wrap, empty) {
            if (!wrap) return;
            wrap.classList.toggle('invalid', empty);
        }

        function validate() {
            var firstBad = null;

            // Text, date, select and textarea inputs
            form.querySelectorAll('input[required], select[required], textarea[required]')
                .forEach(function (el) {
                    var empty = !String(el.value || '').trim();
                    markEmpty(fieldOf(el), empty);
                    if (empty && !firstBad) firstBad = el;
                });

            // The two skill pickers need at least one box ticked
            [['skills_have[]', 0], ['skills_want[]', 1]].forEach(function (pair) {
                var boxes = form.querySelectorAll('input[name="' + pair[0] + '"]');
                if (!boxes.length) return;
                var any = Array.prototype.some.call(boxes, function (b) { return b.checked; });
                var wrap = boxes[0].closest('.am-field');
                markEmpty(wrap, !any);
                if (!any && !firstBad) firstBad = boxes[0];
            });

            return firstBad;
        }

        form.addEventListener('submit', function (e) {
            var bad = validate();
            if (bad) {
                e.preventDefault();
                if (bad.focus) bad.focus();
                var wrap = bad.closest('.am-field');
                if (wrap && wrap.scrollIntoView) {
                    wrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });

        // Clear a field's warning as soon as it is filled in
        form.addEventListener('input',  function () { validate(); });
        form.addEventListener('change', function () { validate(); });

        // Saving a skills modal should clear its warning immediately.
        window.revalidateAboutMe = validate;
    })();
</script>


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

    /* School picker — reveal the "please specify" box only for Other.
       The box is also marked required only while visible, so a hidden
       empty field can never block the form from submitting. */
    function amSchoolChanged(sel) {
        var other = document.getElementById('am-school-other');
        if (!other) return;

        var isOther = sel.value === <?php echo json_encode(\App\Support\SchoolCatalog::OTHER, 15, 512) ?>;
        other.style.display = isOther ? 'block' : 'none';
        other.required = isOther;
        if (!isOther) other.value = '';
        else other.focus();
    }

    document.addEventListener('DOMContentLoaded', function () {
        var sel = document.getElementById('am-school');
        if (sel) amSchoolChanged(sel);
    });
</script>


    
    <?php echo $__env->make('components.responsive', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\Users\PaulV\Documents\MICROCREDENTIALS NEW ADDITIONS\UPSKILL - Microcredential Platform\resources\views/student/dashboard.blade.php ENDPATH**/ ?>