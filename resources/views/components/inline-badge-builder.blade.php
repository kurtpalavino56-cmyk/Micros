@php
    $badge = $course?->badge;
    $enabled = (bool) ($badge || old('badge_enabled', false));
    $badgeName = old('badge_name', $badge?->name ?? ($course?->title ?? ''));
    $badgeDescription = old('badge_description', $badge?->description ?? '');
    $existingBadgeArtwork = null;
    $badgeShape = 'hexagon';

    if ($badge?->icon_url && str_starts_with($badge->icon_url, 'data:image/svg+xml;base64,')) {
        $existingBadgeArtwork = base64_decode(substr($badge->icon_url, strlen('data:image/svg+xml;base64,')), true) ?: null;
        if ($existingBadgeArtwork && preg_match('/data-badge-shape="(hexagon|shield|seal|ring)"/', $existingBadgeArtwork, $shapeMatch)) {
            $badgeShape = $shapeMatch[1];
        }
    }

    $badgeArtworkTemplates = [];
    foreach (['Beginner', 'Intermediate', 'Advanced'] as $level) {
        foreach (['hexagon' => '', 'shield' => '2', 'seal' => '3', 'ring' => '4'] as $shape => $suffix) {
            $badgeArtworkTemplates[$level][$shape] = file_get_contents(public_path('Images/badges/'.strtolower($level).$suffix.'.svg'));
        }
    }
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
                    <button class="badge-logo-button" type="button" id="openBadgeIconDrawer">Choose badge design</button>
                    <strong class="badge-selected-icon-label" id="badgeSelectedIconLabel"></strong>
                    <p class="field-hint">Choose the badge shape. Color follows the course level above.</p>
                </div>
                <div class="badge-preview-wrap">
                    <span>Preview · <strong id="badgePreviewLevel">{{ old('level', $course->level ?? 'Beginner') }}</strong></span>
                    <div class="inline-badge-stage" id="inlineBadgeStage" aria-label="Badge preview"></div>
                    <p class="badge-title-warning" id="badgeTitleWarning" role="status" hidden>Title is too long for this shape. Pick another shape or shorten the title.</p>
                </div>
            </div>
        </div>
    </div>

    <dialog class="badge-icon-drawer" id="badgeIconDrawer" aria-labelledby="badgeIconDrawerTitle">
        <div class="badge-icon-drawer__header">
            <div>
                <h3 id="badgeIconDrawerTitle">Choose a badge design</h3>
                <p>Select the shape for this badge. The color follows the course level.</p>
            </div>
            <button type="button" class="badge-icon-drawer__close" id="closeBadgeIconDrawer" aria-label="Close badge design picker">&times;</button>
        </div>
        <div class="badge-icon-options" role="radiogroup" aria-label="Badge shapes">
            @foreach (['hexagon' => 'Hexagon', 'shield' => 'Shield', 'seal' => 'Seal', 'ring' => 'Ring'] as $shape => $label)
                <button class="badge-icon-option" type="button" role="radio" aria-checked="false" data-badge-shape-option="{{ $shape }}">
                    <span class="badge-icon-option__preview" data-badge-mini="{{ $shape }}"></span>
                    <span>{{ $label }}</span>
                </button>
            @endforeach
        </div>
    </dialog>
</div>
<script>
(function () {
    const templates = @json($badgeArtworkTemplates);
    const logo = @json($psuLogoDataUrl);
    const existingArtwork = @json($existingBadgeArtwork);
    const currentShape = @json($badgeShape);
    const shapes = ['hexagon', 'shield', 'seal', 'ring'];
    const labels = {hexagon: 'Hexagon', shield: 'Shield', seal: 'Seal', ring: 'Ring'};
    const palette = {
        hexagon: {Beginner: {title:'#13176B', word:'#13176B', chip:'navy'}, Intermediate:{title:'#FFFFFF',word:'#FFFFFF',chip:'white'}, Advanced:{title:'#13176B',word:'#13176B',chip:'navy'}},
        shield: {Beginner:{title:'#FFFFFF',word:'#13176B',chip:'navy'},Intermediate:{title:'#13176B',word:'#FFFFFF',chip:'white'},Advanced:{title:'#FFFFFF',word:'#13176B',chip:'navy'}},
        seal: {Beginner:{title:'#13176B',word:'#13176B',chip:'navy'},Intermediate:{title:'#13176B',word:'#13176B',chip:'navy'},Advanced:{title:'#13176B',word:'#13176B',chip:'navy'}},
        ring: {Beginner:{title:'#13176B',word:'#13176B',chip:'navy'},Intermediate:{title:'#13176B',word:'#13176B',chip:'navy'},Advanced:{title:'#13176B',word:'#13176B',chip:'navy'}}
    };
    const name = document.getElementById('inlineBadgeName');
    const stage = document.getElementById('inlineBadgeStage');
    const output = document.getElementById('inlineBadgeIcon');
    const levelInput = document.getElementById('courseLevel');
    const drawer = document.getElementById('badgeIconDrawer');
    const warning = document.getElementById('badgeTitleWarning');
    const trigger = document.getElementById('openBadgeIconDrawer');
    let selectedShape = currentShape;
    let activeTrigger = null;
    let changed = false;
    const courseTitleInput = document.getElementById('courseTitleInput');
    let badgeNameWasEdited = name.value.trim() !== '';
    const levelNow = () => { const level = (levelInput?.value || 'Beginner').toLowerCase(); return level === 'advanced' ? 'Advanced' : level === 'intermediate' ? 'Intermediate' : 'Beginner'; };

    function nestedArtwork(level, shape) {
        const parsed = new DOMParser().parseFromString(templates[level][shape], 'image/svg+xml');
        const root = parsed.documentElement;
        const nested = Array.from(root.children).filter((element) => element.localName === 'svg');
        return nested.find((element) => element.getAttribute('selection') === 'true') || nested[nested.length - 1];
    }

    // ---- Badge layout ----
    // All numbers are in the 400x400 badge coordinate space, measured from the real templates.
    const layouts = {
        // band shapes: the title sits in a band; the wordmark and chip sit below it
        hexagon: {type: 'band', seal: {size: 88, y: 58}, band: {x: 57, y: 158, w: 286, h: 87}, maxLines: 3, floor: 26, word: 276, chip: 303},
        shield:  {type: 'band', seal: {size: 82, y: 62}, band: {x: 85, y: 153, w: 231, h: 81}, maxLines: 2, floor: 22, word: 266, chip: 296},
        // circle shapes: one centered stack (seal, title, wordmark, chip) kept inside a safe radius.
        // up/low = safe radius above/below the centre line (the seal template has a dotted ring in its lower half)
        seal:    {type: 'circle', cx: 200, cy: 200, up: 136, low: 120, seal: 84, shift: -4, maxLines: 3, floor: 26},
        ring:    {type: 'circle', cx: 200, cy: 199, up: 146, low: 146, seal: 92, shift: 0, maxLines: 3, floor: 26}
    };
    const FONT = 'Arial, Helvetica, sans-serif';
    const LH = 1.1, WORD_SIZE = 20, CHIP_SIZE = 16, CHIP_H = 26, WORD_H = 20;
    const GAP_SEAL = 12, GAP_WORD = 14, GAP_CHIP = 10, MAX_TITLE = 34, HARD_MIN = 20, BAND_PAD_X = 8, BAND_PAD_Y = 8;

    function bestPartition(context, words, count) {
        if (count > words.length) return null;
        let best = null;
        const walk = (start, lines) => {
            if (lines.length === count - 1) {
                const all = [...lines, words.slice(start).join(' ')];
                const widths = all.map((line) => context.measureText(line).width);
                const max = Math.max(...widths);
                const score = max + (max - Math.min(...widths)) * 0.35;
                if (!best || score < best.score) best = {lines: all, widths, max, score};
                return;
            }
            for (let end = start + 1; end <= words.length - (count - lines.length - 1); end++) walk(end, [...lines, words.slice(start, end).join(' ')]);
        };
        walk(0, []);
        return best;
    }

    function circleStack(L, size, part, scale, chipW, wordW) {
        const seal = Math.round(L.seal * scale);
        const lh = size * LH, titleH = part.lines.length * lh;
        const total = seal + GAP_SEAL + titleH + GAP_WORD + WORD_H + GAP_CHIP + CHIP_H;
        const sealTop = L.cy + (L.shift || 0) - total / 2;
        const titleTop = sealTop + seal + GAP_SEAL;
        const wordTop = titleTop + titleH + GAP_WORD;
        const chipTop = wordTop + WORD_H + GAP_CHIP;
        const inside = (x, y) => Math.hypot(x - 200, y - L.cy) <= (y < L.cy ? L.up : L.low);
        const rectInside = (w, y0, y1) => inside(200 - w / 2, y0) && inside(200 + w / 2, y0) && inside(200 - w / 2, y1) && inside(200 + w / 2, y1);
        let ok = true;
        for (let a = 0; a < 16; a++) {
            const t = (a / 16) * 2 * Math.PI;
            if (!inside(200 + Math.cos(t) * seal / 2, sealTop + seal / 2 + Math.sin(t) * seal / 2)) ok = false;
        }
        part.widths.forEach((width, i) => {
            const mid = titleTop + (i + 0.5) * lh;
            if (!rectInside(width + 8, mid - size * 0.5, mid + size * 0.5)) ok = false;
        });
        if (!rectInside(wordW + 8, wordTop, wordTop + WORD_H)) ok = false;
        if (!rectInside(chipW, chipTop, chipTop + CHIP_H)) ok = false;
        return {ok, seal, sealTop, titleTop, lh, wordCenter: wordTop + WORD_H / 2, chipCenter: chipTop + CHIP_H / 2};
    }

    function drawBadge(shape, level, title, target) {
        const L = layouts[shape];
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        svg.setAttribute('viewBox', '0 0 400 400');
        svg.setAttribute('role', 'img');
        svg.setAttribute('aria-label', level + ' ' + labels[shape] + ' badge for ' + title);
        svg.setAttribute('data-badge-shape', shape);
        const back = document.createElementNS(svg.namespaceURI, 'svg');
        back.setAttribute('x', '10'); back.setAttribute('y', '10'); back.setAttribute('width', '380'); back.setAttribute('height', '380'); back.setAttribute('viewBox', '0 0 400 400');
        if (shape === 'hexagon') {
            const fill = level === 'Beginner' ? '#F7E8CC' : level === 'Intermediate' ? '#F2CD75' : '#C7D9F0';
            const path = document.createElementNS(svg.namespaceURI, 'path');
            path.setAttribute('d', 'M52 153.5V116c0-4.4 2.4-8.5 6.2-10.7L185.2 34c9.4-5.4 20.9-5.4 30.3 0l126.3 71.3c3.8 2.2 6.2 6.3 6.2 10.7v37.5H52Z');
            path.setAttribute('fill', fill); back.appendChild(path);
        }
        back.appendChild(document.importNode(nestedArtwork(level, shape), true));
        svg.appendChild(back);

        const colors = palette[shape][level];
        const context = document.createElement('canvas').getContext('2d');
        context.font = '700 ' + CHIP_SIZE + 'px ' + FONT;
        const chipW = Math.max(96, Math.ceil(context.measureText(level).width) + 28);
        context.font = '700 ' + WORD_SIZE + 'px ' + FONT;
        const wordW = context.measureText('PSU UpSkill').width;
        const words = String(title || 'Achievement').trim().split(/\s+/).filter(Boolean);
        if (!words.length) words.push('Achievement');

        const attempt = (size, count, part) => {
            if (L.type === 'band') {
                const fits = part.max <= L.band.w - BAND_PAD_X * 2 && count * size * LH <= L.band.h - BAND_PAD_Y * 2;
                return fits ? {seal: L.seal.size, lh: size * LH} : null;
            }
            for (const scale of [1, 0.92, 0.85]) {
                const stack = circleStack(L, size, part, scale, chipW, wordW);
                if (stack.ok) return stack;
            }
            return null;
        };
        const passes = [{maxCount: Math.min(2, L.maxLines), min: L.floor}, {maxCount: L.maxLines, min: HARD_MIN}];
        let found = null;
        for (const pass of passes) {
            for (let size = MAX_TITLE; size >= pass.min && !found; size -= 0.5) {
                context.font = '700 ' + size + 'px ' + FONT;
                for (let count = 1; count <= pass.maxCount && !found; count++) {
                    const part = bestPartition(context, words, count);
                    const placed = part && attempt(size, count, part);
                    if (placed) found = {size, part, placed};
                }
            }
            if (found) break;
        }
        const tooLong = !found;
        if (!found) {
            context.font = '700 ' + HARD_MIN + 'px ' + FONT;
            const part = bestPartition(context, words, Math.min(L.maxLines, words.length));
            found = {size: HARD_MIN, part, placed: L.type === 'band' ? {seal: L.seal.size, lh: HARD_MIN * LH} : circleStack(L, HARD_MIN, part, 0.85, chipW, wordW)};
        }
        if (target === 'main') warning.hidden = !tooLong;

        const {size, part, placed} = found;
        const lh = placed.lh;
        let sealSize, sealTop, titleTop, wordBaseline, chipCenter, titleZone;
        if (L.type === 'band') {
            sealSize = L.seal.size; sealTop = L.seal.y;
            titleTop = L.band.y + (L.band.h - part.lines.length * lh) / 2;
            wordBaseline = L.word; chipCenter = L.chip;
            titleZone = [L.band.x, L.band.y, L.band.w, L.band.h];
        } else {
            sealSize = placed.seal; sealTop = placed.sealTop; titleTop = placed.titleTop;
            wordBaseline = placed.wordCenter + WORD_SIZE * 0.36; chipCenter = placed.chipCenter;
            titleZone = [L.cx - L.up, L.cy - L.up, L.up * 2, L.up * 2];
        }

        const icon = document.createElementNS(svg.namespaceURI, 'image');
        icon.setAttribute('href', logo); icon.setAttribute('x', 200 - sealSize / 2); icon.setAttribute('y', sealTop); icon.setAttribute('width', sealSize); icon.setAttribute('height', sealSize); icon.setAttribute('preserveAspectRatio', 'xMidYMid meet'); icon.dataset.badgeRole = 'seal';
        const sealZone = L.type === 'circle' ? [50, 50, 300, 300] : [24, 24, 352, 132];
        icon.dataset.zoneX = String(sealZone[0]); icon.dataset.zoneY = String(sealZone[1]); icon.dataset.zoneWidth = String(sealZone[2]); icon.dataset.zoneHeight = String(sealZone[3]);
        svg.appendChild(icon);

        part.lines.forEach((line, index) => {
            const text = document.createElementNS(svg.namespaceURI, 'text');
            text.setAttribute('x', 200); text.setAttribute('y', titleTop + (index + 0.5) * lh + size * 0.36); text.setAttribute('text-anchor', 'middle'); text.dataset.badgeRole = 'title';
            text.dataset.zoneX = String(titleZone[0]); text.dataset.zoneY = String(titleZone[1]); text.dataset.zoneWidth = String(titleZone[2]); text.dataset.zoneHeight = String(titleZone[3]);
            text.setAttribute('font-family', FONT); text.setAttribute('font-size', size); text.setAttribute('font-weight', '700'); text.setAttribute('fill', colors.title); text.textContent = line; svg.appendChild(text);
        });

        const word = document.createElementNS(svg.namespaceURI, 'text');
        word.setAttribute('x', 200); word.setAttribute('y', wordBaseline); word.setAttribute('text-anchor', 'middle'); word.dataset.badgeRole = 'wordmark';
        word.dataset.zoneX = String(40); word.dataset.zoneY = String(wordBaseline - 24); word.dataset.zoneWidth = '320'; word.dataset.zoneHeight = '30';
        word.setAttribute('font-family', FONT); word.setAttribute('font-size', WORD_SIZE); word.setAttribute('font-weight', '700'); word.setAttribute('fill', colors.word); word.textContent = 'PSU UpSkill'; svg.appendChild(word);

        const rect = document.createElementNS(svg.namespaceURI, 'rect');
        rect.dataset.badgeRole = 'chip'; rect.dataset.zoneX = String(200 - chipW / 2); rect.dataset.zoneY = String(chipCenter - CHIP_H / 2); rect.dataset.zoneWidth = String(chipW); rect.dataset.zoneHeight = String(CHIP_H);
        rect.setAttribute('x', 200 - chipW / 2); rect.setAttribute('y', chipCenter - CHIP_H / 2); rect.setAttribute('width', chipW); rect.setAttribute('height', CHIP_H); rect.setAttribute('rx', CHIP_H / 2);
        const darkChip = colors.chip === 'white';
        rect.setAttribute('fill', darkChip ? '#FFFFFF' : '#13176B'); rect.setAttribute('stroke', darkChip ? '#13176B' : '#FFFFFF'); rect.setAttribute('stroke-width', '1.5'); svg.appendChild(rect);
        const chipText = document.createElementNS(svg.namespaceURI, 'text');
        chipText.dataset.badgeRole = 'chip-text'; chipText.dataset.zoneX = String(200 - chipW / 2); chipText.dataset.zoneY = String(chipCenter - CHIP_H / 2); chipText.dataset.zoneWidth = String(chipW); chipText.dataset.zoneHeight = String(CHIP_H);
        chipText.setAttribute('x', 200); chipText.setAttribute('y', chipCenter + CHIP_SIZE * 0.36); chipText.setAttribute('text-anchor', 'middle');
        chipText.setAttribute('font-family', FONT); chipText.setAttribute('font-size', CHIP_SIZE); chipText.setAttribute('font-weight', '700'); chipText.setAttribute('fill', darkChip ? '#13176B' : '#FFFFFF'); chipText.textContent = level; svg.appendChild(chipText);
        return svg;
    }

    async function renderMain(persistArtwork = true) {
        await document.fonts.ready;
        const level = levelNow(); const title = name.value.trim() || document.getElementById('courseTitleInput')?.value.trim() || 'Achievement';
        const svg = drawBadge(selectedShape, level, title, 'main'); stage.replaceChildren(svg);
        if (persistArtwork) {
            output.value = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svg.outerHTML)));
            changed = true;
        }
        document.getElementById('badgePreviewLevel').textContent = level;
        document.getElementById('badgeSelectedIconLabel').textContent = labels[selectedShape];
        document.querySelectorAll('[data-badge-shape-option]').forEach((button) => button.setAttribute('aria-checked', String(button.dataset.badgeShapeOption === selectedShape)));
    }

    async function renderDrawer() {
        await document.fonts.ready;
        const level = levelNow(); const title = name.value.trim() || document.getElementById('courseTitleInput')?.value.trim() || 'Achievement';
        shapes.forEach((shape) => {
            const preview = document.querySelector('[data-badge-mini="' + shape + '"]');
            preview.replaceChildren(drawBadge(shape, level, title, 'mini'));
        });
        document.querySelectorAll('[data-badge-shape-option]').forEach((button) => button.setAttribute('aria-checked', String(button.dataset.badgeShapeOption === selectedShape)));
    }

    window.renderMicrocredentialBadgePng = async function (shape, level, title, size = 1200) {
        await document.fonts.ready;
        const svg = drawBadge(shape, level, title, 'export');
        svg.setAttribute('width', '400'); svg.setAttribute('height', '400');
        const source = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svg.outerHTML)));
        const image = new Image();
        image.src = source;
        await image.decode();
        const canvas = document.createElement('canvas'); canvas.width = size; canvas.height = size;
        canvas.getContext('2d').drawImage(image, 0, 0, size, size);
        return await new Promise((resolve, reject) => canvas.toBlob((blob) => blob ? resolve(blob) : reject(new Error('Unable to export badge PNG.')), 'image/png'));
    };

    const selectedLevel = levelNow();
    if (existingArtwork) {
        stage.innerHTML = existingArtwork;
        document.getElementById('badgeSelectedIconLabel').textContent = labels[selectedShape];
    } else if (@json($enabled)) {
        renderMain(false);
    }
    trigger.addEventListener('click', async () => { activeTrigger = document.activeElement; await renderDrawer(); drawer.showModal(); });
    drawer.addEventListener('click', (event) => { if (event.target === drawer) drawer.close(); });
    drawer.addEventListener('close', () => { if (activeTrigger) activeTrigger.focus(); });
    document.getElementById('closeBadgeIconDrawer').addEventListener('click', () => drawer.close());
    document.querySelectorAll('[data-badge-shape-option]').forEach((button) => button.addEventListener('click', () => {
        const nextShape = button.dataset.badgeShapeOption;
        if (nextShape !== selectedShape) {
            selectedShape = nextShape;
            renderMain();
        }
        drawer.close();
    }));
    if (!badgeNameWasEdited && courseTitleInput) {
        name.value = courseTitleInput.value;
    }
    name.addEventListener('input', () => { badgeNameWasEdited = true; renderMain(); });
    levelInput?.addEventListener('change', renderMain);
    courseTitleInput?.addEventListener('input', () => { if (!badgeNameWasEdited) { name.value = courseTitleInput.value; renderMain(); } });
    document.getElementById('inlineBadgeEnabled').addEventListener('change', (event) => {
        document.getElementById('inlineBadgeFields').style.display = event.target.checked ? 'block' : 'none';
        if (event.target.checked && !changed) renderMain();
    });
    if (selectedLevel !== levelNow()) renderMain();
})();
</script>
<style>
.inline-badge-builder{border:1px solid #dfe4ec;background:#fff}.badge-settings{padding:14px 16px}
.badge-toggle{display:flex;align-items:flex-start;gap:9px;font-size:13px;font-weight:700;color:#24346f;cursor:pointer;line-height:1.45}.badge-toggle input[type=checkbox]{width:16px;height:16px;margin-top:1px;accent-color:#13176b;flex:none}
.badge-fields{margin-top:16px;padding-top:16px;border-top:1px solid #e5e7eb}.badge-logo-row{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;border-top:1px solid #edf0f4;padding-top:16px}.badge-icon-controls{display:grid;justify-items:start;gap:7px}
.badge-logo-button{display:inline-flex;align-items:center;border:1px solid #bfc8d7;background:#fff;color:#26365f;border-radius:7px;padding:9px 13px;font-size:12px;font-weight:700;cursor:pointer}.badge-logo-button:hover{border-color:#13176b;background:#f8f9fc}.badge-logo-button:focus-visible,.badge-icon-option:focus-visible,.badge-icon-drawer__close:focus-visible{outline:3px solid #d9a900;outline-offset:3px}.badge-selected-icon-label{font-size:12px;font-weight:700;color:#13176b}
.badge-preview-wrap>span{display:block;margin-bottom:5px;font-size:11px;font-weight:700;color:#667085}.inline-badge-stage{width:220px;height:220px;display:flex;align-items:center;justify-content:center;overflow:hidden}.inline-badge-stage svg{width:100%;height:100%;display:block;filter:drop-shadow(0 5px 8px rgba(17,24,39,.12))}.badge-title-warning{max-width:260px;margin:6px 0 0;color:#8a4b00;font-size:11px;line-height:1.4}
.badge-icon-drawer{position:fixed;inset:0 0 0 auto;margin:0 0 0 auto;width:min(420px,100%);height:100dvh;max-height:none;border:0;border-radius:18px 0 0 18px;padding:24px;background:#fff;color:#111827;box-shadow:-14px 0 45px rgba(15,23,42,.2)}.badge-icon-drawer::backdrop{background:rgba(15,23,42,.42)}
.badge-icon-drawer__header{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:22px}.badge-icon-drawer__header h3{margin:0 0 5px;font-size:18px;color:#111827}.badge-icon-drawer__header p{margin:0;color:#6b7280;font-size:13px}.badge-icon-drawer__close{border:0;background:transparent;color:#6b7280;font-size:28px;line-height:1;cursor:pointer}
.badge-icon-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.badge-icon-option{display:grid;justify-items:center;gap:9px;padding:12px 8px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;color:#344054;font-size:12px;font-weight:700;cursor:pointer}.badge-icon-option:hover,.badge-icon-option[aria-checked="true"]{border-color:#123b7a;background:#f2f6fc;color:#123b7a}
.badge-icon-option__preview{display:grid;place-items:center;width:128px;height:128px;border-radius:12px;background:#f5f7fa}.badge-icon-option__preview svg{width:100%;height:100%;display:block}
@media(max-width:760px){.badge-logo-row{flex-direction:column}.badge-preview-wrap{align-self:center}.badge-icon-drawer{border-radius:0;padding:20px}}
</style>