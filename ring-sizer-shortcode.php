<?php
/**
 * Plugin Name: Titan Jewellery Ring Sizer
 * Description: Camera-based ring sizer. Use shortcode [titan_ring_sizer] on any page or product.
 * Version:     1.0.0
 * Author:      Titan Jewellery
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'titan_ring_sizer', 'titan_ring_sizer_render' );

function titan_ring_sizer_render() {
    ob_start();
    ?>
    <div class="tj-sizer">

    <style>
    .tj-sizer *, .tj-sizer *::before, .tj-sizer *::after { box-sizing: border-box; margin: 0; padding: 0; }
    .tj-sizer {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #0e0e0e;
        color: #f0f0f0;
        max-width: 440px;
        margin: 0 auto;
        padding: 28px 20px 48px;
        border-radius: 16px;
    }
    .tj-brand {
        font-size: 0.68rem;
        letter-spacing: 0.18em;
        color: #c9a96e;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 28px;
    }
    .tj-sizer h2 {
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: 6px;
        color: #f0f0f0;
    }
    .tj-sub {
        font-size: 0.875rem;
        color: #888;
        line-height: 1.65;
    }
    .tj-step { display: none; flex-direction: column; gap: 20px; }
    .tj-step.tj-on { display: flex; }
    .tj-box {
        background: #181818;
        border: 1px solid #2a2a2a;
        border-radius: 10px;
        padding: 16px 18px;
    }
    .tj-box-title {
        font-size: 0.7rem;
        font-weight: 700;
        color: #777;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 10px;
    }
    .tj-box ul { padding-left: 16px; }
    .tj-box li { font-size: 0.875rem; color: #aaa; line-height: 1.8; }
    .tj-field label {
        display: block;
        font-size: 0.75rem;
        color: #888;
        font-weight: 600;
        margin-bottom: 8px;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .tj-sizer select {
        width: 100%;
        padding: 13px 40px 13px 14px;
        background: #181818;
        border: 1px solid #333;
        border-radius: 8px;
        color: #f0f0f0;
        font-size: 0.9rem;
        cursor: pointer;
        -webkit-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' fill='none'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%23888' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
    }
    .tj-btn {
        display: block;
        width: 100%;
        padding: 15px;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        letter-spacing: 0.04em;
        transition: opacity 0.15s;
        text-align: center;
    }
    .tj-btn:active { opacity: 0.75; }
    .tj-btn-gold  { background: #c9a96e; color: #0e0e0e; }
    .tj-btn-ghost { background: #1c1c1c; color: #777; border: 1px solid #2a2a2a; }
    .tj-cam-wrap {
        position: relative;
        width: 100%;
        aspect-ratio: 9 / 16;
        background: #000;
        border-radius: 14px;
        overflow: hidden;
    }
    .tj-cam-wrap video {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .tj-cam-wrap canvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }
    .tj-err {
        display: none;
        padding: 12px 14px;
        background: #1e0e0e;
        border: 1px solid #5c2020;
        border-radius: 8px;
        font-size: 0.82rem;
        color: #d08080;
        line-height: 1.65;
    }
    .tj-result-hero { text-align: center; padding: 24px 0 16px; }
    .tj-size-badge {
        font-size: 7rem;
        font-weight: 800;
        color: #c9a96e;
        line-height: 1;
    }
    .tj-size-meta { margin-top: 8px; font-size: 0.8rem; color: #555; }
    .tj-disclaimer {
        background: #141414;
        border: 1px solid #252525;
        border-radius: 10px;
        padding: 16px 18px;
        font-size: 0.78rem;
        color: #666;
        line-height: 1.85;
    }
    .tj-disclaimer strong { color: #888; }
    </style>

    <div class="tj-brand">Titan Jewellery</div>

    <!-- Step 1: Setup -->
    <div class="tj-step tj-on" id="tj-s1">
        <div>
            <h2>Ring Sizer</h2>
            <p class="tj-sub">Use your phone camera and a credit card to estimate your ring size &mdash; no app download needed.</p>
        </div>
        <div class="tj-box">
            <div class="tj-box-title">What you&rsquo;ll need</div>
            <ul>
                <li>Any standard credit or debit card</li>
                <li>A flat, well-lit surface</li>
            </ul>
        </div>
        <div class="tj-field">
            <label for="tj-width">Ring width you&rsquo;re considering</label>
            <select id="tj-width">
                <option value="0">Up to 4mm &mdash; no size adjustment</option>
                <option value="0.2">5mm to 7mm &mdash; adds approx. half a size</option>
                <option value="0.4">8mm or wider &mdash; adds approx. one full size</option>
            </select>
        </div>
        <button class="tj-btn tj-btn-gold" id="tj-start">Open Camera</button>
    </div>

    <!-- Step 2: Camera -->
    <div class="tj-step" id="tj-s2">
        <div>
            <h2>Align &amp; Capture</h2>
            <p class="tj-sub">Place your card flat on a table. Hold your phone above it, align the card to the gold rectangle, then rest your ring finger flat across the blue zone.</p>
        </div>
        <div class="tj-cam-wrap" id="tj-cam-wrap">
            <video id="tj-vid" autoplay playsinline muted></video>
            <canvas id="tj-canvas"></canvas>
        </div>
        <div class="tj-err" id="tj-err">
            Finger not detected. Ensure the card is aligned to the gold rectangle, your finger is flat across the blue zone, and the area is well lit &mdash; then try again.
        </div>
        <button class="tj-btn tj-btn-gold"  id="tj-measure">Measure My Finger</button>
        <button class="tj-btn tj-btn-ghost" id="tj-back">&#8592; Back</button>
    </div>

    <!-- Step 3: Result -->
    <div class="tj-step" id="tj-s3">
        <div class="tj-result-hero">
            <div class="tj-size-badge" id="tj-size">—</div>
            <div class="tj-size-meta"  id="tj-meta"></div>
        </div>
        <div class="tj-disclaimer">
            <strong>Please note:</strong> This tool is intended to get you within range if you do not know your ring size.
            Tolerance can be +1 or &minus;1 size. We do not recommend having engraving carried out based on this sizing.
            Ring width has been taken into account in your result &mdash; wider bands require a larger size.
            For a guaranteed accurate fit, we recommend visiting a jeweller for professional sizing.
        </div>
        <button class="tj-btn tj-btn-gold" id="tj-retry" style="margin-top:6px;">Measure Again</button>
    </div>

    </div><!-- /.tj-sizer -->

    <script>
    (function () {
        'use strict';

        var CARD_W = 85.60; // credit card width in mm (ISO 7810 ID-1)
        var CARD_H = 54.00; // credit card height in mm
        var DPR    = Math.min(window.devicePixelRatio || 1, 3);

        // UK ring sizes: [label, inner diameter mm], full and half sizes A – Z+3
        var SIZES = [
            ['A',   12.04], ['A½', 12.24],
            ['B',   12.45], ['B½', 12.65],
            ['C',   12.85], ['C½', 13.05],
            ['D',   13.26], ['D½', 13.46],
            ['E',   13.67], ['E½', 13.87],
            ['F',   14.07], ['F½', 14.27],
            ['G',   14.48], ['G½', 14.68],
            ['H',   14.88], ['H½', 15.09],
            ['I',   15.29], ['I½', 15.50],
            ['J',   15.70], ['J½', 15.90],
            ['K',   16.10], ['K½', 16.31],
            ['L',   16.51], ['L½', 16.71],
            ['M',   16.92], ['M½', 17.12],
            ['N',   17.32], ['N½', 17.52],
            ['O',   17.73], ['O½', 17.93],
            ['P',   18.14], ['P½', 18.34],
            ['Q',   18.54], ['Q½', 18.74],
            ['R',   18.95], ['R½', 19.15],
            ['S',   19.35], ['S½', 19.56],
            ['T',   19.76], ['T½', 19.96],
            ['U',   20.17], ['U½', 20.37],
            ['V',   20.57], ['V½', 20.77],
            ['W',   20.98], ['W½', 21.18],
            ['X',   21.39], ['X½', 21.59],
            ['Y',   21.79], ['Y½', 21.99],
            ['Z',   22.20],
            ['Z+1', 22.61],
            ['Z+2', 23.01],
            ['Z+3', 23.42],
        ];

        var stream    = null;
        var guide     = null; // {x,y,w,h} in DPR canvas pixels

        var elS1      = document.getElementById('tj-s1');
        var elS2      = document.getElementById('tj-s2');
        var elS3      = document.getElementById('tj-s3');
        var elVid     = document.getElementById('tj-vid');
        var elCanvas  = document.getElementById('tj-canvas');
        var elErr     = document.getElementById('tj-err');

        function showStep(el) {
            [elS1, elS2, elS3].forEach(function (s) { s.classList.remove('tj-on'); });
            el.classList.add('tj-on');
        }

        document.getElementById('tj-start')  .addEventListener('click', startCamera);
        document.getElementById('tj-measure').addEventListener('click', measure);
        document.getElementById('tj-back')   .addEventListener('click', function () { stopCamera(); showStep(elS1); });
        document.getElementById('tj-retry')  .addEventListener('click', function () { showStep(elS1); });

        // ── Camera ──────────────────────────────────────────────────────────

        function startCamera() {
            showStep(elS2);
            elErr.style.display = 'none';

            navigator.mediaDevices.getUserMedia({
                audio: false,
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 } }
            })
            .then(function (s) {
                stream = s;
                elVid.srcObject = s;
                elVid.addEventListener('loadedmetadata', setupCanvas, { once: true });
            })
            .catch(function () {
                alert('Camera access is required. Please allow camera permission in your browser settings and try again.');
                showStep(elS1);
            });
        }

        function setupCanvas() {
            var wrap = document.getElementById('tj-cam-wrap');
            var cw   = wrap.clientWidth  * DPR;
            var ch   = wrap.clientHeight * DPR;
            elCanvas.width  = cw;
            elCanvas.height = ch;
            drawGuide(cw, ch);
        }

        function stopCamera() {
            if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
            elVid.srcObject = null;
        }

        // ── Guide overlay ────────────────────────────────────────────────────

        function drawGuide(W, H) {
            var ctx = elCanvas.getContext('2d');
            ctx.clearRect(0, 0, W, H);

            // Card rectangle centred in the frame
            var gw = W * 0.80;
            var gh = gw * (CARD_H / CARD_W);
            var gx = (W - gw) / 2;
            var gy = (H - gh) / 2;
            guide  = { x: gx, y: gy, w: gw, h: gh };

            // Dim everything outside the card
            ctx.fillStyle = 'rgba(0,0,0,0.55)';
            ctx.fillRect(0, 0, W, H);
            ctx.clearRect(gx, gy, gw, gh);

            // Gold card outline
            ctx.strokeStyle = '#c9a96e';
            ctx.lineWidth   = 2 * DPR;
            ctx.strokeRect(gx, gy, gw, gh);

            // White corner brackets
            var bl = 18 * DPR;
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth   = 2.5 * DPR;
            [[gx, gy, 1, 1], [gx+gw, gy, -1, 1], [gx, gy+gh, 1, -1], [gx+gw, gy+gh, -1, -1]].forEach(function (c) {
                ctx.beginPath();
                ctx.moveTo(c[0] + c[2] * bl, c[1]);
                ctx.lineTo(c[0], c[1]);
                ctx.lineTo(c[0], c[1] + c[3] * bl);
                ctx.stroke();
            });

            // Blue dashed scan zone where the finger should rest
            var szY = gy + gh * 0.35;
            var szH = gh * 0.30;
            ctx.strokeStyle = 'rgba(80,180,255,0.85)';
            ctx.lineWidth   = 1.5 * DPR;
            ctx.setLineDash([7 * DPR, 5 * DPR]);
            ctx.strokeRect(gx + gw * 0.05, szY, gw * 0.90, szH);
            ctx.setLineDash([]);

            // Labels
            ctx.textAlign = 'center';
            ctx.font      = 'bold ' + Math.round(10.5 * DPR) + 'px -apple-system, sans-serif';
            ctx.fillStyle = 'rgba(80,180,255,0.9)';
            ctx.fillText('REST FINGER HERE', W / 2, szY - 8 * DPR);

            ctx.fillStyle = 'rgba(255,255,255,0.7)';
            ctx.font      = Math.round(10 * DPR) + 'px -apple-system, sans-serif';
            ctx.fillText('Align your card to the gold rectangle', W / 2, gy - 11 * DPR);
        }

        // ── Measurement ──────────────────────────────────────────────────────

        function measure() {
            elErr.style.display = 'none';
            if (!guide || !elVid.videoWidth) return;

            var wrap = document.getElementById('tj-cam-wrap');
            var cw   = wrap.clientWidth  * DPR;
            var ch   = wrap.clientHeight * DPR;

            // Capture current frame into a canvas that matches the guide coordinate space
            var cap = document.createElement('canvas');
            cap.width  = cw;
            cap.height = ch;
            var ctx = cap.getContext('2d');

            // Replicate CSS object-fit:cover cropping so pixel coords align with the guide
            var vW = elVid.videoWidth, vH = elVid.videoHeight;
            var sx = 0, sy = 0, sw = vW, sh = vH;
            if (vW / vH > cw / ch) { sw = vH * (cw / ch); sx = (vW - sw) / 2; }
            else                   { sh = vW / (cw / ch);  sy = (vH - sh) / 2; }
            ctx.drawImage(elVid, sx, sy, sw, sh, 0, 0, cw, ch);

            var imgData  = ctx.getImageData(0, 0, cw, ch);
            var fingerMm = detectFinger(imgData, cw);

            if (!fingerMm) { elErr.style.display = 'block'; return; }

            var adj  = parseFloat(document.getElementById('tj-width').value);
            var best = closestSize(fingerMm + adj);

            stopCamera();
            document.getElementById('tj-size').textContent = best[0];
            document.getElementById('tj-meta').textContent =
                'Finger ~' + fingerMm.toFixed(1) + ' mm · Ring inner Ø ' + best[1].toFixed(2) + ' mm';
            showStep(elS3);
        }

        // ── Detection ────────────────────────────────────────────────────────

        function detectFinger(imgData, canvasW) {
            var g     = guide;
            var scanL = Math.round(g.x + g.w * 0.06);
            var scanR = Math.round(g.x + g.w * 0.94);
            var scanT = Math.round(g.y + g.h * 0.37);
            var scanB = Math.round(g.y + g.h * 0.63);
            var widths = [];

            for (var row = scanT; row <= scanB; row += 2) {
                var w = rowFingerWidth(imgData, row, scanL, scanR, canvasW);
                if (w > 0) widths.push(w);
            }

            if (widths.length < 4) return null;

            widths.sort(function (a, b) { return a - b; });
            var medPx  = widths[Math.floor(widths.length / 2)];
            var diamMm = medPx * (CARD_W / g.w);

            return (diamMm >= 10 && diamMm <= 26) ? diamMm : null;
        }

        function rowFingerWidth(imgData, row, left, right, W) {
            var d   = imgData.data;
            var len = right - left;

            // Luminance samples
            var lum = new Float32Array(len);
            for (var i = 0; i < len; i++) {
                var p = (row * W + left + i) * 4;
                lum[i] = 0.299 * d[p] + 0.587 * d[p + 1] + 0.114 * d[p + 2];
            }

            // 5-tap box smooth to reduce card texture / noise
            var sm = new Float32Array(len);
            for (var i = 2; i < len - 2; i++)
                sm[i] = (lum[i-2] + lum[i-1] + lum[i] + lum[i+1] + lum[i+2]) / 5;

            // Central-difference gradient
            var gr = new Float32Array(len);
            for (var i = 1; i < len - 1; i++) gr[i] = sm[i + 1] - sm[i - 1];

            // Adaptive edge threshold: 2× mean absolute gradient, floored at 6
            var sumAbs = 0;
            for (var i = 10; i < len - 10; i++) sumAbs += Math.abs(gr[i]);
            var thresh = Math.max(6, (sumAbs / (len - 20)) * 2.0);

            // Left edge: strongest gradient in left 55 % of scan span
            var lEdge = -1, lStr = thresh, lSign = 0;
            for (var i = 8; i < Math.round(len * 0.55); i++) {
                var a = Math.abs(gr[i]);
                if (a > lStr) { lStr = a; lEdge = i; lSign = gr[i] > 0 ? 1 : -1; }
            }

            // Right edge: strongest gradient of opposite sign in right 55 %
            var rEdge = -1, rStr = thresh;
            for (var i = Math.round(len * 0.45); i < len - 8; i++) {
                var val = -lSign * gr[i];
                if (val > rStr) { rStr = val; rEdge = i; }
            }

            if (lEdge < 0 || rEdge < 0 || rEdge <= lEdge) return 0;

            var width = rEdge - lEdge;
            return (width >= len * 0.08 && width <= len * 0.65) ? width : 0;
        }

        // ── Size lookup ──────────────────────────────────────────────────────

        function closestSize(diam) {
            var best = SIZES[0], bestD = Infinity;
            for (var i = 0; i < SIZES.length; i++) {
                var d = Math.abs(diam - SIZES[i][1]);
                if (d < bestD) { bestD = d; best = SIZES[i]; }
            }
            return best;
        }

    }());
    </script>
    <?php
    return ob_get_clean();
}
