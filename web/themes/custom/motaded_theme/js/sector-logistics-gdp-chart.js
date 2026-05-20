/**
 * @file
 * GASTAT logistics — YoY GDP growth (%) vertical bar chart (SVG), supports negative values.
 */
(function (Drupal, once) {
  'use strict';

  /**
   * @param {string} raw
   * @returns {{series: Array<{year: number, value: number}>}|null}
   */
  function parsePayload(raw) {
    if (!raw) {
      return null;
    }
    try {
      const data = JSON.parse(raw);
      if (!data || !Array.isArray(data.series) || data.series.length === 0) {
        return null;
      }
      return data;
    }
    catch (e) {
      return null;
    }
  }

  /**
   * @param {SVGSVGElement} svg
   * @param {Array<{year: number, value: number}>} series
   */
  function renderBars(svg, series) {
    const years = series.map((p) => p.year);
    const vals = series.map((p) => p.value);
    const minRaw = Math.min(...vals, 0);
    const maxRaw = Math.max(...vals, 0);
    const span = maxRaw - minRaw;
    const padV = span === 0 ? 2 : span * 0.1;
    let yMin = minRaw - padV;
    let yMax = maxRaw + padV;
    if (yMin >= yMax) {
      yMax = yMin + 1;
    }

    const W = 880;
    const H = 300;
    const padL = 52;
    const padR = 20;
    const padT = 28;
    const padB = 48;
    const pw = W - padL - padR;
    const ph = H - padT - padB;
    const n = series.length;
    const barGap = 0.22;
    const slot = pw / Math.max(n, 1);
    const bw = slot * (1 - barGap);

    const yAt = (v) => padT + ph - ((v - yMin) / (yMax - yMin)) * ph;

    while (svg.firstChild) {
      svg.removeChild(svg.firstChild);
    }
    const ns = 'http://www.w3.org/2000/svg';

    const ticks = 5;
    for (let i = 0; i <= ticks; i += 1) {
      const tv = yMin + ((yMax - yMin) * i) / ticks;
      const yy = yAt(tv);
      const line = document.createElementNS(ns, 'line');
      line.setAttribute('x1', String(padL));
      line.setAttribute('x2', String(W - padR));
      line.setAttribute('y1', String(yy));
      line.setAttribute('y2', String(yy));
      line.setAttribute('class', 'sector-gdp-chart__grid');
      svg.appendChild(line);
      const text = document.createElementNS(ns, 'text');
      text.setAttribute('x', String(padL - 6));
      text.setAttribute('y', String(yy + 4));
      text.setAttribute('text-anchor', 'end');
      text.setAttribute('class', 'sector-gdp-chart__axis');
      text.textContent = `${Math.round(tv * 10) / 10}%`;
      svg.appendChild(text);
    }

    if (yMin <= 0 && yMax >= 0) {
      const yz = yAt(0);
      const zline = document.createElementNS(ns, 'line');
      zline.setAttribute('x1', String(padL));
      zline.setAttribute('x2', String(W - padR));
      zline.setAttribute('y1', String(yz));
      zline.setAttribute('y2', String(yz));
      zline.setAttribute('class', 'sector-gdp-chart__zero');
      svg.appendChild(zline);
    }

    vals.forEach((v, i) => {
      const x = padL + i * slot + (slot - bw) / 2;
      const y0 = yAt(0);
      const y1 = yAt(v);
      const top = Math.min(y0, y1);
      const h = Math.max(Math.abs(y0 - y1), 0.5);
      const rect = document.createElementNS(ns, 'rect');
      rect.setAttribute('x', String(x));
      rect.setAttribute('y', String(top));
      rect.setAttribute('width', String(bw));
      rect.setAttribute('height', String(h));
      rect.setAttribute('rx', '4');
      rect.setAttribute('class', 'sector-gdp-chart__bar' + (v < 0 ? ' sector-gdp-chart__bar--negative' : ''));
      const tip = document.createElementNS(ns, 'title');
      tip.textContent = `${years[i]}: ${v}%`;
      rect.appendChild(tip);
      svg.appendChild(rect);

      const lbl = document.createElementNS(ns, 'text');
      const lblY = v >= 0 ? top - 8 : top + h + 16;
      lbl.setAttribute('x', String(x + bw / 2));
      lbl.setAttribute('y', String(lblY));
      lbl.setAttribute('text-anchor', 'middle');
      lbl.setAttribute('class', 'sector-gdp-chart__value');
      lbl.textContent = `${Math.round(v * 10) / 10}%`;
      svg.appendChild(lbl);

      const xr = document.createElementNS(ns, 'text');
      xr.setAttribute('x', String(x + bw / 2));
      xr.setAttribute('y', String(H - 14));
      xr.setAttribute('text-anchor', 'middle');
      xr.setAttribute('class', 'sector-gdp-chart__axis sector-gdp-chart__axis--x');
      xr.textContent = String(years[i]);
      svg.appendChild(xr);
    });

    svg.setAttribute('viewBox', `0 0 ${W} ${H}`);
  }

  Drupal.behaviors.sectorLogisticsGdpChart = {
    attach(context) {
      once('sectorLogisticsGdpChart', '[data-sector-logistics-gdp-chart]', context).forEach((root) => {
        const payload = parsePayload(root.getAttribute('data-chart'));
        if (!payload) {
          return;
        }
        const svg = root.querySelector('.sector-gdp-chart__svg');
        if (!svg) {
          return;
        }
        renderBars(svg, payload.series);
      });
    },
  };
})(Drupal, once);
