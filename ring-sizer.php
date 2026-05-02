<?php
/**
 * Ring Sizer — Titan Jewellery
 * Drop into any PHP page or include with: <?php include 'ring-sizer.php'; ?>
 */
?>
<div class="tj-ring-sizer">
<style>
.tj-ring-sizer *, .tj-ring-sizer *::before, .tj-ring-sizer *::after { box-sizing: border-box; margin: 0; padding: 0; }

.tj-ring-sizer {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #0e0e0e;
  color: #f0f0f0;
  max-width: 440px;
  margin: 0 auto;
  padding: 24px 16px 48px;
  border-radius: 14px;
}

.tj-brand {
  font-size: 0.7rem;
  letter-spacing: 0.18em;
  color: #c9a96e;
  font-weight: 700;
  text-transform: uppercase;
  margin-bottom: 28px;
}

.tj-ring-sizer h2 { font-size: 1.35rem; font-weight: 700; margin-bottom: 6px; }

.tj-sub {
  font-size: 0.875rem;
  color: #888;
  line-height: 1.65;
}

.tj-step { display: none; flex-direction: column; gap: 20px; }
.tj-step.tj-active { display: flex; }

.tj-info-box {
  background: #181818;
  border: 1px solid #2a2a2a;
  border-radius: 10px;
  padding: 16px 18px;
}

.tj-info-box .tj-box-title {
  font-size: 0.72rem;
  font-weight: 700;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 10px;
}

.tj-info-box ul { padding-left: 16px; }
.tj-info-box li { font-size: 0.875rem; color: #aaa; line-height: 1.75; }

.tj-field label {
  display: block;
  font-size: 0.78rem;
  color: #888;
  font-weight: 600;
  margin-bottom: 8px;
  letter-spacing: 0.04em;
}

.tj-ring-sizer select {
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
}
.tj-btn:active { opacity: 0.75; }
.tj-btn-primary { background: #c9a96e; color: #0e0e0e; }
.tj-btn-ghost   { background: #1c1c1c; color: #777; border: 1px solid #2a2a2a; }

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

.tj-result-hero {
  text-align: center;
  padding: 24px 0 16px;
}

.tj-size-badge {
  font-size: 7rem;
  font-weight: 800;
  color: #c9a96e;
  line-height: 1;
}

.tj-size-meta {
  margin-top: 8px;
  font-size: 0.8rem;
  color: #555;
}

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
  <div class="tj-step tj-active" id="tj-s1">
    <div>
      <h2>Ring Sizer</h2>
      <p class="tj-sub">Use your phone camera and a credit card to estimate your ring size — no app download needed.</p>
    </div>

    <div class="tj-info-box">
      <div class="tj-box-title">What you'll need</div>
      <ul>
        <li>Any standard credit or debit card</li>
        <li>A flat, well-lit surface</li>
      </ul>
    </div>

    <div class="tj-field">
      <label for="tj-ringWidth">Ring width you're considering</label>
      <select id="tj-ringWidth">
        <option value="0">Up to 4mm — no size adjustment</option>
        <option value="0.2">5mm to 7mm — adds approx. half a size</option>
        <option value="0.4">8mm or wider — adds approx. one full size</option>
      </select>
    </div>

    <button class="tj-btn tj-btn-primary" id="tj-btnStart">Open Camera</button>
  </div>

  <!-- Step 2: Camera -->
  <div class="tj-step" id="tj-s2">
    <div>
      <h2>Align &amp; Capture</h2>
      <p class="tj-sub">Place your card flat on a table. Hold your phone above it, align the card to the gold rectangle, then rest your ring finger across the blue zone.</p>
    </div>

    <div class="tj-cam-wrap" id="tj-camWrap">
      <video id="tj-vid" autoplay playsinline muted></video>
      <canvas id="tj-guide"></canvas>
    </div>

    <div class="tj-err" id="tj-errMsg">
      Finger not detected. Check the card is aligned, your finger is flat across the blue zone, and the area is well lit — then try again.
    </div>

    <button class="tj-btn tj-btn-primary" id="tj-btnMeasure">Measure My Finger</button>
    <button class="tj-btn tj-btn-ghost" id="tj-btnBack">&#8592; Back</button>
  </div>

  <!-- Step 3: Result -->
  <div class="tj-step" id="tj-s3">
    <div class="tj-result-hero">
      <div class="tj-size-badge" id="tj-rSize">—</div>
      <div class="tj-size-meta" id="tj-rMeta"></div>
    </div>

    <div class="tj-disclaimer">
      <strong>Please note:</strong> This tool is intended to get you within range if you do not know your ring size.
      Tolerance can be +1 or &minus;1 size. We do not recommend having engraving carried out based on this sizing.
      Ring width has been taken into account in your result &mdash; wider bands require a larger size.
      For a guaranteed accurate fit, we recommend visiting a jeweller for professional sizing.
    </div>

    <button class="tj-btn tj-btn-primary" id="tj-btnRetry" style="margin-top:4px;">Measure Again</button>
  </div>
</div>

<script>
(function () {
  'use strict';

  var CARD_W_MM = 85.60;
  var CARD_H_MM = 54.00;
  var DPR = Math.min(window.devicePixelRatio || 1, 3);

  var UK_SIZES = [
    ['A',    12.04], ['A½', 12.24],
    ['B',    12.45], ['B½', 12.65],
    ['C',    12.85], ['C½', 13.05],
    ['D',    13.26], ['D½', 13.46],
    ['E',    13.67], ['E½', 13.87],
    ['F',    14.07], ['F½', 14.27],
    ['G',    14.48], ['G½', 14.68],
    ['H',    14.88], ['H½', 15.09],
    ['I',    15.29], ['I½', 15.50],
    ['J',    15.70], ['J½', 15.90],
    ['K',    16.10], ['K½', 16.31],
    ['L',    16.51], ['L½', 16.71],
    ['M',    16.92], ['M½', 17.12],
    ['N',    17.32], ['N½', 17.52],
    ['O',    17.73], ['O½', 17.93],
    ['P',    18.14], ['P½', 18.34],
    ['Q',    18.54], ['Q½', 18.74],
    ['R',    18.95], ['R½', 19.15],
    ['S',    19.35], ['S½', 19.56],
    ['T',    19.76], ['T½', 19.96],
    ['U',    20.17], ['U½', 20.37],
    ['V',    20.57], ['V½', 20.77],
    ['W',    20.98], ['W½', 21.18],
    ['X',    21.39], ['X½', 21.59],
    ['Y',    21.79], ['Y½', 21.99],
    ['Z',    22.20],
    ['Z+1',  22.61],
    ['Z+2',  23.01],
    ['Z+3',  23.42],
  ];

  var mediaStream = null;
  var guideRect   = null;

  var s1          = document.getElementById('tj-s1');
  var s2          = document.getElementById('tj-s2');
  var s3          = document.getElementById('tj-s3');
  var vid         = document.getElementById('tj-vid');
  var guideCanvas = document.getElementById('tj-guide');
  var errMsg      = document.getElementById('tj-errMsg');

  function show(step) {
    [s1, s2, s3].forEach(function (s) { s.classList.remove('tj-active'); });
    step.classList.add('tj-active');
  }

  document.getElementById('tj-btnStart').addEventListener('click', openCamera);
  document.getElementById('tj-btnMeasure').addEventListener('click', measure);
  document.getElementById('tj-btnBack').addEventListener('click', function () { stopCamera(); show(s1); });
  document.getElementById('tj-btnRetry').addEventListener('click', function () { show(s1); });

  function openCamera() {
    show(s2);
    errMsg.style.display = 'none';

    navigator.mediaDevices.getUserMedia({
      audio: false,
      video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 } }
    }).then(function (stream) {
      mediaStream = stream;
      vid.srcObject = stream;
      vid.addEventListener('loadedmetadata', initCanvas, { once: true });
    }).catch(function () {
      alert('Camera access is required. Please allow camera permission and try again.');
      show(s1);
    });
  }

  function initCanvas() {
    var wrap = document.getElementById('tj-camWrap');
    var cw = wrap.clientWidth  * DPR;
    var ch = wrap.clientHeight * DPR;
    guideCanvas.width  = cw;
    guideCanvas.height = ch;
    drawGuide(cw, ch);
  }

  function drawGuide(W, H) {
    var ctx = guideCanvas.getContext('2d');
    ctx.clearRect(0, 0, W, H);

    var gw = W * 0.80;
    var gh = gw * (CARD_H_MM / CARD_W_MM);
    var gx = (W - gw) / 2;
    var gy = (H - gh) / 2;
    guideRect = { x: gx, y: gy, w: gw, h: gh };

    ctx.fillStyle = 'rgba(0,0,0,0.55)';
    ctx.fillRect(0, 0, W, H);
    ctx.clearRect(gx, gy, gw, gh);

    ctx.strokeStyle = '#c9a96e';
    ctx.lineWidth = 2 * DPR;
    ctx.strokeRect(gx, gy, gw, gh);

    var bLen = 16 * DPR;
    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 2.5 * DPR;
    [[gx, gy, 1, 1], [gx+gw, gy, -1, 1], [gx, gy+gh, 1, -1], [gx+gw, gy+gh, -1, -1]].forEach(function (c) {
      ctx.beginPath();
      ctx.moveTo(c[0] + c[2] * bLen, c[1]);
      ctx.lineTo(c[0], c[1]);
      ctx.lineTo(c[0], c[1] + c[3] * bLen);
      ctx.stroke();
    });

    var szTop = gy + gh * 0.35;
    var szH   = gh * 0.30;
    ctx.strokeStyle = 'rgba(80,180,255,0.85)';
    ctx.lineWidth = 1.5 * DPR;
    ctx.setLineDash([7 * DPR, 5 * DPR]);
    ctx.strokeRect(gx + gw * 0.05, szTop, gw * 0.90, szH);
    ctx.setLineDash([]);

    var fs = Math.round(10.5 * DPR);
    ctx.textAlign = 'center';
    ctx.font = 'bold ' + fs + 'px -apple-system, sans-serif';
    ctx.fillStyle = 'rgba(80,180,255,0.9)';
    ctx.fillText('REST FINGER HERE', W / 2, szTop - 8 * DPR);
    ctx.fillStyle = 'rgba(255,255,255,0.7)';
    ctx.font = Math.round(10 * DPR) + 'px -apple-system, sans-serif';
    ctx.fillText('Align your card to the gold rectangle', W / 2, gy - 11 * DPR);
  }

  function measure() {
    errMsg.style.display = 'none';
    if (!guideRect || !vid.videoWidth) return;

    var wrap = document.getElementById('tj-camWrap');
    var cw = wrap.clientWidth  * DPR;
    var ch = wrap.clientHeight * DPR;

    var cap = document.createElement('canvas');
    cap.width = cw; cap.height = ch;
    var ctx = cap.getContext('2d');

    var vW = vid.videoWidth, vH = vid.videoHeight;
    var vAR = vW / vH, cAR = cw / ch;
    var sx = 0, sy = 0, sw = vW, sh = vH;
    if (vAR > cAR) { sw = vH * cAR; sx = (vW - sw) / 2; }
    else           { sh = vW / cAR; sy = (vH - sh) / 2; }
    ctx.drawImage(vid, sx, sy, sw, sh, 0, 0, cw, ch);

    var imgData  = ctx.getImageData(0, 0, cw, ch);
    var fingerMm = detectFinger(imgData, cw);

    if (!fingerMm) { errMsg.style.display = 'block'; return; }

    var adj  = parseFloat(document.getElementById('tj-ringWidth').value);
    var best = closestSize(fingerMm + adj);

    stopCamera();
    document.getElementById('tj-rSize').textContent = best[0];
    document.getElementById('tj-rMeta').textContent =
      'Finger ~' + fingerMm.toFixed(1) + ' mm · Ring inner Ø ' + best[1].toFixed(2) + ' mm';
    show(s3);
  }

  function detectFinger(imgData, canvasW) {
    var g = guideRect;
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

    var mmPerPx = CARD_W_MM / g.w;
    var diamMm  = widths[Math.floor(widths.length / 2)] * mmPerPx;
    return (diamMm >= 10 && diamMm <= 26) ? diamMm : null;
  }

  function rowFingerWidth(imgData, row, left, right, W) {
    var d = imgData.data, len = right - left;
    var lum = new Float32Array(len);

    for (var i = 0; i < len; i++) {
      var p = (row * W + left + i) * 4;
      lum[i] = 0.299 * d[p] + 0.587 * d[p+1] + 0.114 * d[p+2];
    }

    var sm = new Float32Array(len);
    for (var i = 2; i < len - 2; i++)
      sm[i] = (lum[i-2] + lum[i-1] + lum[i] + lum[i+1] + lum[i+2]) / 5;

    var gr = new Float32Array(len);
    for (var i = 1; i < len - 1; i++) gr[i] = sm[i+1] - sm[i-1];

    var sumAbs = 0;
    for (var i = 10; i < len - 10; i++) sumAbs += Math.abs(gr[i]);
    var thresh = Math.max(6, (sumAbs / (len - 20)) * 2.0);

    var lEdge = -1, lStr = thresh, lSign = 0;
    for (var i = 8; i < Math.round(len * 0.55); i++) {
      var a = Math.abs(gr[i]);
      if (a > lStr) { lStr = a; lEdge = i; lSign = gr[i] > 0 ? 1 : -1; }
    }

    var rEdge = -1, rStr = thresh;
    for (var i = Math.round(len * 0.45); i < len - 8; i++) {
      var val = -lSign * gr[i];
      if (val > rStr) { rStr = val; rEdge = i; }
    }

    if (lEdge < 0 || rEdge < 0 || rEdge <= lEdge) return 0;
    var width = rEdge - lEdge;
    return (width >= len * 0.08 && width <= len * 0.65) ? width : 0;
  }

  function closestSize(diam) {
    var best = UK_SIZES[0], bestD = Infinity;
    for (var i = 0; i < UK_SIZES.length; i++) {
      var d = Math.abs(diam - UK_SIZES[i][1]);
      if (d < bestD) { bestD = d; best = UK_SIZES[i]; }
    }
    return best;
  }

  function stopCamera() {
    if (mediaStream) { mediaStream.getTracks().forEach(function (t) { t.stop(); }); mediaStream = null; }
    vid.srcObject = null;
  }
}());
</script>
