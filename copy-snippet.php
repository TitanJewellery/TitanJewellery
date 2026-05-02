<?php
$snippet = <<<'ENDSNIPPET'
<?php
// WordPress Code Snippet — paste into Code Snippets > Add New > PHP snippet
// Shortcode: [titan_ring_sizer]

add_shortcode('titan_ring_sizer', 'titan_ring_sizer_render');

function titan_ring_sizer_render() {
    ob_start();
    ?>
    <div class="tj">

    <style>
    .tj *, .tj *::before, .tj *::after { box-sizing: border-box; margin: 0; padding: 0; }
    .tj {
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
    .tj h2 { font-size: 1.3rem; font-weight: 700; margin-bottom: 6px; color: #f0f0f0; }
    .tj-sub { font-size: 0.875rem; color: #888; line-height: 1.65; }
    .tj-step { display: none; flex-direction: column; gap: 18px; }
    .tj-step.tj-on { display: flex; }
    .tj-box {
        background: #181818;
        border: 1px solid #2a2a2a;
        border-radius: 10px;
        padding: 16px 18px;
    }
    .tj-box-title {
        font-size: 0.7rem; font-weight: 700; color: #777;
        text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 10px;
    }
    .tj-box ul { padding-left: 16px; }
    .tj-box li { font-size: 0.875rem; color: #aaa; line-height: 1.8; }
    .tj-field label {
        display: block; font-size: 0.75rem; color: #888;
        font-weight: 600; margin-bottom: 8px;
        letter-spacing: 0.05em; text-transform: uppercase;
    }
    .tj select {
        width: 100%; padding: 13px 40px 13px 14px;
        background: #181818; border: 1px solid #333;
        border-radius: 8px; color: #f0f0f0; font-size: 0.9rem; cursor: pointer;
        -webkit-appearance: none; appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' fill='none'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%23888' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 14px center;
    }
    .tj-btn {
        display: block; width: 100%; padding: 15px; border: none;
        border-radius: 8px; font-size: 0.95rem; font-weight: 700;
        cursor: pointer; letter-spacing: 0.04em; transition: opacity 0.15s; text-align: center;
    }
    .tj-btn:active { opacity: 0.75; }
    .tj-gold  { background: #c9a96e; color: #0e0e0e; }
    .tj-ghost { background: #1c1c1c; color: #777; border: 1px solid #2a2a2a; font-size: 0.85rem; padding: 11px; }
    .tj-cam-wrap {
        position: relative; width: 100%; aspect-ratio: 3/4;
        min-height: 300px;
        background: #000; border-radius: 14px; overflow: hidden;
    }
    .tj-cam-wrap video {
        position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
    }
    .tj-cam-wrap canvas {
        position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none;
    }
    .tj-status {
        font-size: 0.82rem; line-height: 1.55; padding: 10px 14px;
        border-radius: 8px; text-align: center;
    }
    .tj-st-scan   { background: #181818; color: #888;   border: 1px solid #2a2a2a; }
    .tj-st-found  { background: #0d1f0d; color: #6ecf7e; border: 1px solid #2a5a2a; }
    .tj-st-finger { background: #0d1520; color: #6ab0e8; border: 1px solid #1a4060; }
    .tj-st-err    { background: #1e0e0e; color: #d08080; border: 1px solid #5c2020; }
    .tj-result-hero { text-align: center; padding: 24px 0 16px; }
    .tj-size-badge { font-size: 7rem; font-weight: 800; color: #c9a96e; line-height: 1; }
    .tj-size-meta  { margin-top: 8px; font-size: 0.8rem; color: #555; }
    .tj-disclaimer {
        background: #141414; border: 1px solid #252525;
        border-radius: 10px; padding: 16px 18px;
        font-size: 0.78rem; color: #666; line-height: 1.85;
    }
    .tj-disclaimer strong { color: #888; }
    </style>

    <div class="tj-brand">Titan Jewellery</div>

    <!-- Step 1 -->
    <div class="tj-step tj-on" id="tj-s1">
        <div>
            <h2>Ring Sizer</h2>
            <p class="tj-sub">Point your camera at a credit card on a flat surface. It detects the card automatically, then measures your finger.</p>
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
                <option value="4:0">Up to 4mm &mdash; no size adjustment</option>
                <option value="6:0.2">5mm to 7mm &mdash; adds approx. half a size</option>
                <option value="8:0.4">8mm or wider &mdash; adds approx. one full size</option>
            </select>
        </div>
        <button class="tj-btn tj-gold" id="tj-start">Open Camera</button>
    </div>

    <!-- Step 2 -->
    <div class="tj-step" id="tj-s2">
        <div>
            <h2>Scan</h2>
            <div class="tj-status tj-st-scan" id="tj-status">Hold camera above a credit card on a flat surface</div>
        </div>
        <div class="tj-cam-wrap" id="tj-wrap">
            <video id="tj-vid" autoplay playsinline webkit-playsinline muted></video>
            <canvas id="tj-canvas"></canvas>
        </div>
        <div style="display:flex;gap:10px;">
            <button class="tj-btn tj-ghost" id="tj-back"   style="flex:1">Cancel</button>
            <button class="tj-btn tj-ghost" id="tj-manual" style="flex:2">Measure now</button>
        </div>
    </div>

    <!-- Step 3 -->
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
        <button class="tj-btn tj-gold" id="tj-retry" style="margin-top:6px;">Measure Again</button>
    </div>

    </div><!-- /.tj -->

    <script>
    (function () {
        'use strict';

        var CARD_W  = 85.60;
        var CARD_H  = 54.00;
        var CARD_AR = CARD_W / CARD_H;
        var DPR     = Math.min(window.devicePixelRatio || 1, 3);
        var FPS_MS  = 100;

        var SIZES = [
            ['A',   12.04], ['A½', 12.24], ['B',   12.45], ['B½', 12.65],
            ['C',   12.85], ['C½', 13.05], ['D',   13.26], ['D½', 13.46],
            ['E',   13.67], ['E½', 13.87], ['F',   14.07], ['F½', 14.27],
            ['G',   14.48], ['G½', 14.68], ['H',   14.88], ['H½', 15.09],
            ['I',   15.29], ['I½', 15.50], ['J',   15.70], ['J½', 15.90],
            ['K',   16.10], ['K½', 16.31], ['L',   16.51], ['L½', 16.71],
            ['M',   16.92], ['M½', 17.12], ['N',   17.32], ['N½', 17.52],
            ['O',   17.73], ['O½', 17.93], ['P',   18.14], ['P½', 18.34],
            ['Q',   18.54], ['Q½', 18.74], ['R',   18.95], ['R½', 19.15],
            ['S',   19.35], ['S½', 19.56], ['T',   19.76], ['T½', 19.96],
            ['U',   20.17], ['U½', 20.37], ['V',   20.57], ['V½', 20.77],
            ['W',   20.98], ['W½', 21.18], ['X',   21.39], ['X½', 21.59],
            ['Y',   21.79], ['Y½', 21.99], ['Z',   22.20],
            ['Z+1', 22.61], ['Z+2', 23.01], ['Z+3', 23.42],
        ];

        var phase      = 'idle';
        var stream     = null;
        var rafId      = null;
        var lastTick   = 0;
        var lockedCard = null;
        var cardBuf    = [];
        var fingerBuf  = [];

        var elS1     = document.getElementById('tj-s1');
        var elS2     = document.getElementById('tj-s2');
        var elS3     = document.getElementById('tj-s3');
        var elVid    = document.getElementById('tj-vid');
        var elCanvas = document.getElementById('tj-canvas');
        var elWrap   = document.getElementById('tj-wrap');
        var elStatus = document.getElementById('tj-status');

        function show(el) {
            [elS1, elS2, elS3].forEach(function (s) { s.classList.remove('tj-on'); });
            el.classList.add('tj-on');
        }
        function setStatus(cls, msg) {
            elStatus.className = 'tj-status ' + cls;
            elStatus.textContent = msg;
        }
        function getRingParams() {
            var parts = document.getElementById('tj-width').value.split(':');
            return { widthMm: parseFloat(parts[0]), sizeAdj: parseFloat(parts[1]) };
        }

        document.getElementById('tj-start') .addEventListener('click', openCamera);
        document.getElementById('tj-back')  .addEventListener('click', function () { closeCamera(); show(elS1); });
        document.getElementById('tj-retry') .addEventListener('click', function () { show(elS1); });
        document.getElementById('tj-manual').addEventListener('click', manualMeasure);

        function openCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Camera access is not supported in this browser. Please use Chrome or Safari.');
                return;
            }
            show(elS2);
            phase = 'scanning';
            lockedCard = null; cardBuf = []; fingerBuf = [];
            setStatus('tj-st-scan', 'Hold camera above a credit card on a flat surface');

            var attempts = [
                { audio: false, video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 } } },
                { audio: false, video: { facingMode: { ideal: 'environment' } } },
                { audio: false, video: { facingMode: 'environment' } },
                { audio: false, video: { width: { min: 640 }, height: { min: 480 } } },
                { audio: false, video: true },
            ];
            tryCamera(attempts, 0);
        }

        function tryCamera(attempts, i) {
            navigator.mediaDevices.getUserMedia(attempts[i])
                .then(function (s) {
                    stream = s;
                    elVid.srcObject = s;

                    var playPromise = elVid.play();
                    if (playPromise) playPromise.catch(function () {});

                    var started = false;
                    function onReady() {
                        if (started || !elVid.videoWidth) return;
                        started = true;
                        sizeCanvas();
                        startLoop();
                    }

                    elVid.addEventListener('loadedmetadata', onReady, { once: true });
                    elVid.addEventListener('canplay',        onReady, { once: true });

                    setTimeout(function () {
                        if (!started && elVid.readyState >= 1) onReady();
                        else if (!started && elVid.videoWidth) onReady();
                    }, 2500);
                })
                .catch(function () {
                    if (i + 1 < attempts.length) {
                        tryCamera(attempts, i + 1);
                    } else {
                        alert('Camera access failed. Please allow camera permission in your browser settings and try again.');
                        show(elS1);
                    }
                });
        }

        function closeCamera() {
            stopLoop();
            if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
            elVid.srcObject = null;
            phase = 'idle'; lockedCard = null; cardBuf = []; fingerBuf = [];
            elCanvas.getContext('2d').clearRect(0, 0, elCanvas.width, elCanvas.height);
        }

        function sizeCanvas() {
            elCanvas.width  = elWrap.clientWidth  * DPR;
            elCanvas.height = elWrap.clientHeight * DPR;
        }

        function startLoop() { if (!rafId) rafId = requestAnimationFrame(tick); }
        function stopLoop()  { if (rafId) { cancelAnimationFrame(rafId); rafId = null; } }

        function tick(now) {
            rafId = requestAnimationFrame(tick);
            if (now - lastTick < FPS_MS) return;
            lastTick = now;
            if (!elVid.videoWidth) return;
            processFrame();
        }

        function processFrame() {
            var cw = elCanvas.width, ch = elCanvas.height;
            var img = grabFrame(cw, ch);
            if (!img) return;

            if (phase === 'scanning') {
                var card = detectCard(img, cw, ch);
                cardBuf.push(card);
                if (cardBuf.length > 7) cardBuf.shift();
                drawScanOverlay(card);

                if (cardIsStable()) {
                    lockedCard = averageCards();
                    phase = 'card_found';
                    cardBuf = []; fingerBuf = [];
                    setStatus('tj-st-found', 'Card detected ✔  Now rest your ring finger across it');
                }

            } else if (phase === 'card_found') {
                var ring = getRingParams();
                var mm   = measureAtRingLine(img, cw, lockedCard, ring.widthMm);
                fingerBuf.push(mm);
                if (fingerBuf.length > 6) fingerBuf.shift();
                drawCardOverlay(lockedCard, ring.widthMm, mm);

                if (fingerIsStable()) commitResult();
            }
        }

        function grabFrame(cw, ch) {
            var cap = document.createElement('canvas');
            cap.width = cw; cap.height = ch;
            var ctx = cap.getContext('2d');
            var vW = elVid.videoWidth, vH = elVid.videoHeight;
            if (!vW || !vH) return null;

            var vAR = vW / vH, cAR = cw / ch;
            var rotated = (vAR > 1.2 && cAR < 0.9) || (vAR < 0.9 && cAR > 1.2);

            if (rotated) {
                ctx.save();
                ctx.translate(cw / 2, ch / 2);
                ctx.rotate(Math.PI / 2);
                var sc = Math.max(cw / vH, ch / vW);
                ctx.drawImage(elVid, -vW * sc / 2, -vH * sc / 2, vW * sc, vH * sc);
                ctx.restore();
            } else {
                var sx = 0, sy = 0, sw = vW, sh = vH;
                if (vAR > cAR) { sw = vH * cAR; sx = (vW - sw) / 2; }
                else           { sh = vW / cAR; sy = (vH - sh) / 2; }
                ctx.drawImage(elVid, sx, sy, sw, sh, 0, 0, cw, ch);
            }
            return cap.getContext('2d').getImageData(0, 0, cw, ch);
        }

        function detectCard(img, W, H) {
            var s = 5, dW = Math.floor(W / s), dH = Math.floor(H / s), d = img.data;

            function lum(px, py) {
                px = Math.max(0, Math.min(W-1, px)); py = Math.max(0, Math.min(H-1, py));
                var p = (py * W + px) * 4;
                return 0.299*d[p] + 0.587*d[p+1] + 0.114*d[p+2];
            }

            var rowP = new Float32Array(dH);
            for (var y = 1; y < dH-1; y++)
                for (var x = 0; x < dW; x++)
                    rowP[y] += Math.abs(lum(x*s, y*s+s) - lum(x*s, y*s-s));

            var colP = new Float32Array(dW);
            for (var x = 1; x < dW-1; x++)
                for (var y = 0; y < dH; y++)
                    colP[x] += Math.abs(lum(x*s+s, y*s) - lum(x*s-s, y*s));

            var hP = top2Peaks(rowP, Math.round(dH * 0.08));
            var vP = top2Peaks(colP, Math.round(dW * 0.08));
            if (!hP || !vP) return null;

            var y1=Math.min(hP[0],hP[1])*s, y2=Math.max(hP[0],hP[1])*s;
            var x1=Math.min(vP[0],vP[1])*s, x2=Math.max(vP[0],vP[1])*s;
            var cW=x2-x1, cH=y2-y1;

            if (cW < W*0.15 || cH < H*0.08 || cW > W*0.97 || cH > H*0.97) return null;

            var ar = cW / cH;
            if (Math.abs(ar-CARD_AR) > CARD_AR*0.28 && Math.abs(1/ar-CARD_AR) > CARD_AR*0.28) return null;

            if (cW < cH) {
                var t; t=x1;x1=y1;y1=t; t=x2;x2=y2;y2=t; cW=x2-x1; cH=y2-y1;
            }
            return { x:x1, y:y1, w:cW, h:cH };
        }

        function top2Peaks(arr, minSep) {
            var n=arr.length, v1=-1, i1=-1, v2=-1, i2=-1;
            for (var i=2; i<n-2; i++)
                if (arr[i]>v1 && arr[i]>=arr[i-1] && arr[i]>=arr[i+1]) { v1=arr[i]; i1=i; }
            if (i1<0) return null;
            for (var i=2; i<n-2; i++) {
                if (Math.abs(i-i1)<minSep) continue;
                if (arr[i]>v2 && arr[i]>=arr[i-1] && arr[i]>=arr[i+1]) { v2=arr[i]; i2=i; }
            }
            return (i2<0 || v2<v1*0.22) ? null : [i1, i2];
        }

        function cardIsStable() {
            var v = cardBuf.filter(Boolean);
            if (v.length < 5) return false;
            var avg = v.reduce(function(s,c){return s+c.w;},0)/v.length;
            return v.reduce(function(s,c){return s+Math.abs(c.w-avg);},0)/v.length / avg < 0.10;
        }

        function averageCards() {
            var v=cardBuf.filter(Boolean), n=v.length;
            return {
                x:v.reduce(function(s,c){return s+c.x;},0)/n,
                y:v.reduce(function(s,c){return s+c.y;},0)/n,
                w:v.reduce(function(s,c){return s+c.w;},0)/n,
                h:v.reduce(function(s,c){return s+c.h;},0)/n,
            };
        }

        function ringLineY(card, ringWidthMm) {
            var ringPx  = ringWidthMm * (card.h / CARD_H);
            var centre  = card.y + card.h * 0.78;
            return { top: centre - ringPx / 2, bot: centre + ringPx / 2 };
        }

        function measureAtRingLine(img, W, card, ringWidthMm) {
            var rl     = ringLineY(card, ringWidthMm);
            var scanL  = Math.round(card.x + card.w * 0.05);
            var scanR  = Math.round(card.x + card.w * 0.95);
            var scanT  = Math.round(rl.top);
            var scanB  = Math.round(rl.bot);
            var hits   = [];

            for (var row = scanT; row <= scanB; row += 2) {
                var w = rowWidth(img, row, scanL, scanR, W);
                if (w > 0) hits.push(w);
            }
            if (hits.length < 2) return null;
            hits.sort(function(a,b){return a-b;});
            var medPx  = hits[Math.floor(hits.length/2)];
            var diamMm = medPx * (CARD_W / card.w);
            return (diamMm >= 10 && diamMm <= 26) ? diamMm : null;
        }

        function rowWidth(img, row, left, right, W) {
            var d=img.data, len=right-left;
            var lum=new Float32Array(len);
            for (var i=0;i<len;i++) {
                var p=(row*W+left+i)*4;
                lum[i]=0.299*d[p]+0.587*d[p+1]+0.114*d[p+2];
            }
            var sm=new Float32Array(len);
            for (var i=2;i<len-2;i++) sm[i]=(lum[i-2]+lum[i-1]+lum[i]+lum[i+1]+lum[i+2])/5;
            var gr=new Float32Array(len);
            for (var i=1;i<len-1;i++) gr[i]=sm[i+1]-sm[i-1];
            var s=0;
            for (var i=10;i<len-10;i++) s+=Math.abs(gr[i]);
            var th=Math.max(5,(s/(len-20))*1.8);
            var lE=-1,lS=th,lSgn=0;
            for (var i=6;i<Math.round(len*0.55);i++) {
                var a=Math.abs(gr[i]); if(a>lS){lS=a;lE=i;lSgn=gr[i]>0?1:-1;}
            }
            var rE=-1,rS=th;
            for (var i=Math.round(len*0.45);i<len-6;i++) {
                var v=-lSgn*gr[i]; if(v>rS){rS=v;rE=i;}
            }
            if(lE<0||rE<0||rE<=lE) return 0;
            var w=rE-lE;
            return (w>=len*0.06 && w<=len*0.70) ? w : 0;
        }

        function fingerIsStable() {
            var v=fingerBuf.filter(Boolean);
            if(v.length<5) return false;
            var avg=v.reduce(function(s,x){return s+x;},0)/v.length;
            return v.reduce(function(s,x){return s+Math.abs(x-avg);},0)/v.length/avg < 0.07;
        }

        function medianFinger() {
            var v=fingerBuf.filter(Boolean).sort(function(a,b){return a-b;});
            return v[Math.floor(v.length/2)];
        }

        function manualMeasure() {
            if (phase === 'card_found' && lockedCard) {
                commitResult();
            } else {
                var last = cardBuf.filter(Boolean).pop();
                if (last) {
                    lockedCard = last;
                    var ring = getRingParams();
                    var img  = grabFrame(elCanvas.width, elCanvas.height);
                    if (img) {
                        var mm = measureAtRingLine(img, elCanvas.width, lockedCard, ring.widthMm);
                        if (mm) { fingerBuf = [mm,mm,mm,mm,mm]; commitResult(); return; }
                    }
                }
                setStatus('tj-st-err', 'Card not detected yet — point camera at your credit card first');
            }
        }

        function commitResult() {
            var mm   = medianFinger();
            var ring = getRingParams();
            var best = closestSize(mm + ring.sizeAdj);
            closeCamera();
            document.getElementById('tj-size').textContent = best[0];
            document.getElementById('tj-meta').textContent =
                'Finger ~' + mm.toFixed(1) + ' mm · Ring inner Ø ' + best[1].toFixed(2) + ' mm';
            show(elS3);
        }

        function drawScanOverlay(detected) {
            var ctx=elCanvas.getContext('2d'), W=elCanvas.width, H=elCanvas.height;
            ctx.clearRect(0,0,W,H);

            if (detected) {
                ctx.fillStyle='rgba(0,0,0,0.45)';
                ctx.fillRect(0,0,W,H);
                ctx.clearRect(detected.x,detected.y,detected.w,detected.h);
                ctx.strokeStyle='#f5c842';
                ctx.lineWidth=2.5*DPR;
                ctx.strokeRect(detected.x,detected.y,detected.w,detected.h);
            } else {
                var gw=W*0.80, gh=gw*(CARD_H/CARD_W), gx=(W-gw)/2, gy=(H-gh)/2;
                ctx.fillStyle='rgba(0,0,0,0.5)';
                ctx.fillRect(0,0,W,H);
                ctx.strokeStyle='rgba(255,255,255,0.35)';
                ctx.lineWidth=1.5*DPR;
                ctx.setLineDash([10*DPR,7*DPR]);
                ctx.strokeRect(gx,gy,gw,gh);
                ctx.setLineDash([]);
                ctx.fillStyle='rgba(255,255,255,0.5)';
                ctx.font=Math.round(10*DPR)+'px sans-serif';
                ctx.textAlign='center';
                ctx.fillText('Place a credit card in view', W/2, gy+gh+18*DPR);
            }
        }

        function drawCardOverlay(card, ringWidthMm, fingerMm) {
            var ctx=elCanvas.getContext('2d'), W=elCanvas.width, H=elCanvas.height;
            ctx.clearRect(0,0,W,H);
            ctx.fillStyle='rgba(0,0,0,0.40)';
            ctx.fillRect(0,0,W,H);
            ctx.clearRect(card.x,card.y,card.w,card.h);

            ctx.strokeStyle = fingerMm ? 'rgba(80,220,120,0.9)' : '#c9a96e';
            ctx.lineWidth   = 2.5*DPR;
            ctx.strokeRect(card.x, card.y, card.w, card.h);

            var lineL = card.x + card.w * 0.08;
            var lineR = card.x + card.w * 0.92;

            var wTop = card.y + card.h * 0.08;
            var wBot = card.y + card.h * 0.92;

            ctx.strokeStyle = 'rgba(255,255,255,0.75)';
            ctx.lineWidth   = 1.5*DPR;

            ctx.beginPath(); ctx.moveTo(lineL, wTop); ctx.lineTo(lineR, wTop); ctx.stroke();
            ctx.beginPath(); ctx.moveTo(lineL, wBot); ctx.lineTo(lineR, wBot); ctx.stroke();

            var tick = 6*DPR;
            [[lineL, wTop],[lineR, wTop],[lineL, wBot],[lineR, wBot]].forEach(function(pt) {
                ctx.beginPath(); ctx.moveTo(pt[0], pt[1]-tick); ctx.lineTo(pt[0], pt[1]+tick); ctx.stroke();
            });

            var rl = ringLineY(card, ringWidthMm);

            ctx.fillStyle = 'rgba(80,150,255,0.12)';
            ctx.fillRect(lineL, rl.top, lineR-lineL, rl.bot-rl.top);

            ctx.strokeStyle = 'rgba(80,160,255,0.9)';
            ctx.lineWidth   = 2*DPR;
            ctx.beginPath(); ctx.moveTo(lineL, rl.top); ctx.lineTo(lineR, rl.top); ctx.stroke();
            ctx.beginPath(); ctx.moveTo(lineL, rl.bot); ctx.lineTo(lineR, rl.bot); ctx.stroke();

            ctx.fillStyle  = 'rgba(80,160,255,0.9)';
            ctx.font       = 'bold '+Math.round(9*DPR)+'px sans-serif';
            ctx.textAlign  = 'center';
            ctx.fillText('RING POSITION', W/2, rl.top - 6*DPR);

            if (fingerMm) {
                setStatus('tj-st-finger', 'Finger detected — hold still…');
            }
        }

        function closestSize(diam) {
            var best=SIZES[0], bestD=Infinity;
            for (var i=0;i<SIZES.length;i++) {
                var d=Math.abs(diam-SIZES[i][1]); if(d<bestD){bestD=d;best=SIZES[i];}
            }
            return best;
        }

    }());
    </script>
    <?php
    return ob_get_clean();
}
ENDSNIPPET;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Copy Ring Sizer Snippet — Titan Jewellery</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #0e0e0e; color: #f0f0f0;
    padding: 20px; min-height: 100vh;
  }
  h1 { font-size: 1.1rem; color: #c9a96e; margin-bottom: 6px; letter-spacing: 0.05em; }
  p  { font-size: 0.85rem; color: #888; margin-bottom: 20px; line-height: 1.5; }
  #copy-btn {
    display: block; width: 100%; padding: 22px;
    background: #c9a96e; color: #0e0e0e;
    border: none; border-radius: 10px;
    font-size: 1.3rem; font-weight: 800;
    letter-spacing: 0.06em; cursor: pointer;
    margin-bottom: 16px; text-transform: uppercase;
  }
  #copy-btn.done { background: #4caf50; color: #fff; }
  #msg { text-align: center; font-size: 0.85rem; color: #4caf50; min-height: 22px; margin-bottom: 14px; }
  textarea {
    width: 100%; height: 60vh;
    background: #181818; color: #ccc;
    border: 1px solid #333; border-radius: 8px;
    font-family: 'Courier New', Courier, monospace;
    font-size: 0.72rem; padding: 14px; resize: vertical; line-height: 1.5;
  }
</style>
</head>
<body>
<h1>Titan Ring Sizer — Code Snippet</h1>
<p>Tap the button to copy the full PHP snippet, then paste into Code Snippets &rsaquo; Add New &rsaquo; PHP snippet.</p>
<button id="copy-btn" onclick="copyCode()">TAP TO COPY ALL CODE</button>
<div id="msg"></div>
<textarea id="code" readonly><?php echo htmlspecialchars($snippet); ?></textarea>
<script>
function copyCode(){
  var ta=document.getElementById('code');
  var btn=document.getElementById('copy-btn');
  var msg=document.getElementById('msg');
  if(navigator.clipboard&&navigator.clipboard.writeText){
    navigator.clipboard.writeText(ta.value).then(function(){done(btn,msg);}).catch(function(){fb(ta,btn,msg);});
  } else { fb(ta,btn,msg); }
}
function fb(ta,btn,msg){
  ta.select(); ta.setSelectionRange(0,999999);
  var ok=false; try{ok=document.execCommand('copy');}catch(e){}
  if(ok){done(btn,msg);}else{msg.style.color='#d08080';msg.textContent='Long-press the box, tap Select All, then Copy';}
}
function done(btn,msg){
  btn.textContent='COPIED!'; btn.classList.add('done');
  msg.style.color='#4caf50'; msg.textContent='Paste into Code Snippets > Add New > PHP snippet';
  setTimeout(function(){btn.textContent='TAP TO COPY ALL CODE';btn.classList.remove('done');msg.textContent='';},4000);
}
</script>
</body>
</html>
