/* Weekly timetable grid: render, drag-to-move/swap (Pointer Events: mouse + touch), mark done & undo. */
(function () {
  var data = window.STUDYSYNC;
  var subjects = {};
  data.subjects.forEach(function (s) { subjects[s.id] = s; });
  var slots = data.slots;

  var grid = document.getElementById('grid');
  var wrap = document.getElementById('grid-wrap');
  var empty = document.getElementById('empty');
  var legend = document.getElementById('legend');
  var regenBtn = document.getElementById('regen-btn');
  var statsBar = document.getElementById('tt-stats');

  function hoursList() {
    var set = {};
    for (var h = data.start; h < data.end; h++) set[h] = true;
    slots.forEach(function (s) { set[s.start_hour] = true; });
    return Object.keys(set).map(Number).sort(function (a, b) { return a - b; });
  }

  function slotAt(day, hour) {
    for (var i = 0; i < slots.length; i++) {
      if (slots[i].day_of_week === day && slots[i].start_hour === hour) return slots[i];
    }
    return null;
  }

  function updateStats() {
    if (!statsBar) return;
    var total = slots.length;
    var done = slots.filter(function (s) { return s.is_completed; }).length;
    var pct = total > 0 ? Math.round((done / total) * 100) : 0;
    
    var totalEl = document.getElementById('tt-total');
    var doneEl = document.getElementById('tt-done');
    var pctEl = document.getElementById('tt-pct');
    var barEl = document.getElementById('tt-progress-bar');
    
    if (totalEl) totalEl.textContent = total;
    if (doneEl) doneEl.textContent = done;
    if (pctEl) pctEl.textContent = pct + '%';
    if (barEl) barEl.style.width = pct + '%';
  }

  function render() {
    legend.innerHTML = '';
    data.subjects.forEach(function (s) {
      var span = document.createElement('span');
      span.className = 'legend-item';
      var dot = document.createElement('span');
      dot.className = 'dot';
      dot.style.background = s.color;
      span.appendChild(dot);
      span.appendChild(document.createTextNode(s.name));
      legend.appendChild(span);
    });

    if (!slots.length) {
      empty.hidden = false;
      wrap.hidden = true;
      if (statsBar) statsBar.hidden = true;
      return;
    }
    empty.hidden = true;
    wrap.hidden = false;
    if (statsBar) statsBar.hidden = false;
    updateStats();

    var days = data.days;
    grid.style.gridTemplateColumns = '76px repeat(' + days.length + ', minmax(0, 1fr))';
    grid.innerHTML = '<div class="tt-corner-cell"></div>';
    days.forEach(function (d) {
      var el = document.createElement('div');
      el.className = 'tt-day';
      el.innerHTML = '<span class="day-name">' + DAY_SHORT[d] + '</span>';
      grid.appendChild(el);
    });

    hoursList().forEach(function (hour) {
      var label = document.createElement('div');
      label.className = 'tt-hour';
      label.textContent = formatHour(hour);
      grid.appendChild(label);
      days.forEach(function (day) {
        var cell = document.createElement('div');
        cell.className = 'tt-cell';
        cell.dataset.day = day;
        cell.dataset.hour = hour;
        var slot = slotAt(day, hour);
        if (slot) cell.appendChild(buildSlot(slot));
        grid.appendChild(cell);
      });
    });
  }

  function buildSlot(slot) {
    var sub = subjects[slot.subject_id];
    var el = document.createElement('div');
    el.className = 'slot' + (slot.is_completed ? ' done' : '');
    el.dataset.id = slot.id;
    el.style.background = sub ? sub.color : 'var(--primary)';
    el.setAttribute('role', 'button');
    el.setAttribute('tabindex', '0');
    el.setAttribute('title', slot.is_completed ? 'Completed (Click to undo)' : 'Click to complete, or drag to move');

    var content = document.createElement('div');
    content.className = 'slot-content';

    var label = document.createElement('span');
    label.className = 'slot-label';
    label.textContent = sub ? sub.name : 'Study';
    content.appendChild(label);

    var timeBadge = document.createElement('span');
    timeBadge.className = 'slot-time';
    timeBadge.textContent = formatHour(slot.start_hour);
    content.appendChild(timeBadge);

    el.appendChild(content);

    // Dedicated Check / Undo toggle button
    var check = document.createElement('button');
    check.type = 'button';
    check.className = 'slot-check' + (slot.is_completed ? ' is-done' : '');
    check.innerHTML = slot.is_completed ? '&#x2713;' : '<span class="circle-ring"></span>';
    check.setAttribute('aria-label', slot.is_completed ? 'Undo session completion' : 'Mark session as completed');
    check.setAttribute('title', slot.is_completed ? 'Click to undo' : 'Mark as done');

    check.addEventListener('pointerdown', function (e) { e.stopPropagation(); });
    check.addEventListener('click', function (e) {
      e.stopPropagation();
      e.preventDefault();
      toggle(slot);
    });
    el.appendChild(check);

    // Keyboard support: Space or Enter toggles slot completion
    el.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggle(slot);
      }
    });

    el.addEventListener('pointerdown', function (e) { startDrag(e, slot, el); });
    return el;
  }

  function toggle(slot) {
    var next = !slot.is_completed;
    api('timetable', { action: 'toggle', id: slot.id, done: next })
      .then(function () {
        slot.is_completed = next;
        render();
        if (next) {
          toast('Session marked complete! 🎯', 'success', {
            text: 'Undo',
            onClick: function () {
              toggle(slot);
            }
          });
        } else {
          toast('Session marked incomplete (reverted) ↩', 'info');
        }
      })
      .catch(function () {
        toast('Could not update that session', 'error');
      });
  }

  /* ---- drag & drop with click detection ---- */
  var drag = null;

  function startDrag(e, slot, el) {
    if (e.button !== undefined && e.button !== 0) return;
    drag = {
      slot: slot,
      el: el,
      startX: e.clientX,
      startY: e.clientY,
      startTime: Date.now(),
      active: false,
      ghost: null,
      over: null
    };
    window.addEventListener('pointermove', onMove);
    window.addEventListener('pointerup', onUp);
    window.addEventListener('pointercancel', cancel);
  }

  function onMove(e) {
    if (!drag) return;
    if (!drag.active) {
      if (Math.hypot(e.clientX - drag.startX, e.clientY - drag.startY) < 6) return;
      drag.active = true;
      drag.el.classList.add('dragging');
      var g = document.createElement('div');
      g.className = 'drag-ghost';
      g.textContent = drag.el.querySelector('.slot-label').textContent;
      g.style.background = drag.el.style.background;
      document.body.appendChild(g);
      drag.ghost = g;
    }
    drag.ghost.style.left = e.clientX + 'px';
    drag.ghost.style.top = e.clientY + 'px';
    var cell = cellFromPoint(e.clientX, e.clientY);
    if (drag.over !== cell) {
      if (drag.over) drag.over.classList.remove('over');
      if (cell) cell.classList.add('over');
      drag.over = cell;
    }
  }

  function cellFromPoint(x, y) {
    var node = document.elementFromPoint(x, y);
    return node ? node.closest('.tt-cell') : null;
  }

  function cleanup() {
    window.removeEventListener('pointermove', onMove);
    window.removeEventListener('pointerup', onUp);
    window.removeEventListener('pointercancel', cancel);
    if (drag) {
      if (drag.ghost) drag.ghost.remove();
      if (drag.over) drag.over.classList.remove('over');
      drag.el.classList.remove('dragging');
    }
  }

  function cancel() { cleanup(); drag = null; }

  function onUp(e) {
    var d = drag;
    cleanup();
    drag = null;
    if (!d) return;

    // If pointer was pressed and released without dragging: user clicked the session card!
    if (!d.active) {
      var elapsed = Date.now() - d.startTime;
      var dist = Math.hypot(e.clientX - d.startX, e.clientY - d.startY);
      if (elapsed < 400 && dist < 6) {
        toggle(d.slot);
      }
      return;
    }

    if (!d.over) return;
    var day = Number(d.over.dataset.day), hour = Number(d.over.dataset.hour);
    var slot = d.slot;
    if (slot.day_of_week === day && slot.start_hour === hour) return;
    var target = slotAt(day, hour);
    api('timetable', { action: 'move', id: slot.id, day: day, hour: hour })
      .then(function () {
        if (target) { target.day_of_week = slot.day_of_week; target.start_hour = slot.start_hour; }
        slot.day_of_week = day; slot.start_hour = hour;
        render();
        toast(target ? 'Sessions swapped and saved' : 'Session moved and saved', 'success');
      })
      .catch(function (err) { toast(err.message || 'Could not save that change', 'error'); });
  }

  if (regenBtn) {
    regenBtn.addEventListener('click', function () {
      if (!data.subjects.length) { toast('Add subjects first', 'error'); return; }
      regenBtn.disabled = true;
      regenBtn.textContent = 'Regenerating\u2026';
      api('timetable', { action: 'generate' })
        .then(function (res) {
          if (res.unplaced > 0) toast(res.unplaced + ' hour(s) didn\u2019t fit your available time.', 'warning');
          else toast('Timetable regenerated successfully!', 'success');
          setTimeout(function () { window.location.reload(); }, 600);
        })
        .catch(function (err) {
          toast(err.message || 'Could not regenerate the timetable', 'error');
          regenBtn.disabled = false;
          regenBtn.textContent = 'Regenerate';
        });
    });
  }

  render();
})();
