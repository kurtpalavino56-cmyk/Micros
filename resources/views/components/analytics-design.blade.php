<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<style>
    body.analytics-shell {
        background: #f7f9fc;
        color: #17233c;
        font-family: Inter, "Segoe UI", Arial, sans-serif;
    }

    .analytics-shell .analytics-page,
    .analytics-shell .faculty-analytics-page {
        width: 100%;
        max-width: 1480px;
        margin-right: auto;
        margin-left: auto;
        color: #17233c;
        font-family: Inter, "Segoe UI", Arial, sans-serif;
    }

    .analytics-shell .analytics-page .page-header,
    .analytics-shell .analytics-page .analytics-header,
    .analytics-shell .faculty-analytics-page .page-head.faculty-page-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    .analytics-shell .analytics-page .page-title,
    .analytics-shell .analytics-page .analytics-header h1,
    .analytics-shell .faculty-analytics-page .page-head.faculty-page-heading h2.faculty-page-title {
        margin: 0;
        color: #13176b;
        font-family: "Plus Jakarta Sans", Inter, "Segoe UI", sans-serif;
        font-size: 28px;
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: -.02em;
    }

    .analytics-shell .analytics-page .page-subtitle,
    .analytics-shell .analytics-page .analytics-header .subtitle,
    .analytics-shell .faculty-analytics-page .page-head.faculty-page-heading .faculty-page-subtitle {
        margin: 6px 0 0;
        color: #667085;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.5;
    }

    .analytics-shell .analytics-page .header-actions {
        width: min(100%, 583px);
    }

    .analytics-shell .analytics-page .stat-card,
    .analytics-shell .analytics-page .card,
    .analytics-shell .analytics-page .stats-card,
    .analytics-shell .analytics-page .analytics-card,
    .analytics-shell .analytics-page .course-card,
    .analytics-shell .faculty-analytics-page .g-card,
    .analytics-shell .faculty-analytics-page .panel {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(19, 35, 76, .05);
    }

    .analytics-shell .faculty-analytics-page .g-card {
        min-height: 96px;
        align-items: flex-start;
        justify-content: center;
        padding: 18px 20px;
        color: #17233c;
        text-align: left;
    }

    .analytics-shell .faculty-analytics-page .g-card.navy,
    .analytics-shell .faculty-analytics-page .g-card.gold,
    .analytics-shell .faculty-analytics-page .g-card.cyan {
        background: #fff;
    }

    .analytics-shell .faculty-analytics-page .g-card .num {
        margin-bottom: 6px;
        color: #13176b;
        font-size: 30px;
        font-weight: 700;
    }

    .analytics-shell .faculty-analytics-page .g-card .label {
        color: #667085;
        font-size: 13px;
        font-weight: 500;
    }

    .analytics-shell .faculty-analytics-page .g-card .watermark {
        display: none;
    }

    .analytics-shell .faculty-analytics-page .stat-cards {
        gap: 14px;
        margin-bottom: 20px;
    }

    .analytics-shell .faculty-analytics-page .charts-grid {
        gap: 16px;
    }

    .analytics-shell .faculty-analytics-page .panel-head {
        padding: 14px 18px;
        border-bottom: 1px solid #e2e8f0;
        background: #fff;
        color: #17233c;
        font-size: 16px;
        font-weight: 700;
    }

    .analytics-shell .faculty-analytics-page .panel-head .range {
        padding: 5px 9px;
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        background: #f7f9fc;
        color: #667085;
        font-size: 12px;
        font-weight: 500;
    }

    .analytics-shell .faculty-analytics-page .panel-body {
        padding: 18px;
    }

    .analytics-shell .download-report,
    .analytics-shell .download-report-btn {
        display: inline-flex;
        min-height: 38px;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 8px 13px;
        border: 1px solid #13176b;
        border-radius: 8px;
        background: #13176b;
        color: #fff;
        font-family: Inter, "Segoe UI", Arial, sans-serif;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
        text-decoration: none;
        box-shadow: none;
        transition: background-color .15s ease, border-color .15s ease;
    }

    .analytics-shell .download-report:hover,
    .analytics-shell .download-report-btn:hover {
        transform: none;
        border-color: #202b91;
        background: #202b91;
        color: #fff;
    }

    .analytics-shell .analytics-page .filter-select,
    .analytics-shell .analytics-page .segment,
    .analytics-shell .analytics-page .period-switch button {
        min-height: 38px;
        border-radius: 7px;
        font-family: Inter, "Segoe UI", Arial, sans-serif;
        font-size: 12px;
        font-weight: 600;
    }

    .analytics-shell .analytics-page .period-switch {
        border-radius: 8px;
    }

    @media (max-width: 900px) {
        .analytics-shell .analytics-page .page-header,
        .analytics-shell .analytics-page .analytics-header,
        .analytics-shell .faculty-analytics-page .page-head.faculty-page-heading {
            align-items: flex-start;
            flex-direction: column;
        }

        .analytics-shell .analytics-page .header-actions {
            align-items: flex-start;
            width: 100%;
        }
    }

    @media (max-width: 520px) {
        .analytics-shell .analytics-page .page-title,
        .analytics-shell .analytics-page .analytics-header h1,
        .analytics-shell .faculty-analytics-page .page-head.faculty-page-heading h2.faculty-page-title {
            font-size: 24px;
        }
    }
</style>
