(function () {
  var cfg = window.STUDYSYNC;
  var form = document.getElementById('add-form');
  var list = document.getElementById('subject-list');
  var swatches = document.getElementById('swatches');
  var genBtn = document.getElementById('generate-btn');
  var color = cfg.colors[0];

  if (swatches) {
    swatches.addEventListener('click', function (e) {
      var b = e.target.closest('.swatch');
      if (!b) return;
      color = b.dataset.color;
      swatches.querySelectorAll('.swatch').forEach(function (s) { s.classList.toggle('selected', s === b); });
    });
  }

  function selectSwatch(c) {
    color = c;
    if (swatches) {
      swatches.querySelectorAll('.swatch').forEach(function (s) { s.classList.toggle('selected', s.dataset.color === c); });
    }
  }

  function refreshSummary() {
    var items = list.querySelectorAll('.subject-item');
    var total = 0;
    items.forEach(function (li) { total += Number(li.dataset.hours); });
    var sumHours = document.getElementById('sum-hours');
    if (sumHours) sumHours.textContent = total;
    if (!items.length && !list.querySelector('.empty')) {
      var li = document.createElement('li');
      li.className = 'empty';
      li.textContent = 'No subjects yet. Add your first course to get started.';
      list.appendChild(li);
    }
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var name = form.name.value.trim();
      var hours = Number(form.hours.value);
      if (!name) return;
      api('subjects', { action: 'add', name: name, hours_per_week: hours, color: color })
        .then(function (res) {
          var empty = list.querySelector('.empty');
          if (empty) empty.remove();
          var li = document.createElement('li');
          li.className = 'subject-item';
          li.dataset.id = res.id;
          li.dataset.hours = hours;
          li.innerHTML = '<span class="dot"></span>' +
            '<span class="subject-name"></span>' +
            '<span class="badge-tag muted small push"></span>' +
            '<button type="button" class="btn btn-ghost btn-icon del-btn" title="Delete subject">\uD83D\uDDD1</button>';
          li.querySelector('.dot').style.background = color;
          li.querySelector('.subject-name').textContent = name;
          li.querySelector('.badge-tag').textContent = hours + ' h/week';
          li.querySelector('.del-btn').setAttribute('aria-label', 'Delete ' + name);
          list.appendChild(li);
          form.reset();
          form.hours.value = 3;
          var idx = (list.querySelectorAll('.subject-item').length) % cfg.colors.length;
          selectSwatch(cfg.colors[idx]);
          refreshSummary();
          toast('Subject added successfully! 📚', 'success');
        })
        .catch(function (err) { toast(err.message || 'Could not add that subject', 'error'); });
    });
  }

  if (list) {
    list.addEventListener('click', function (e) {
      var btn = e.target.closest('.del-btn');
      if (!btn) return;
      var li = btn.closest('.subject-item');
      var name = li.querySelector('.subject-name') ? li.querySelector('.subject-name').textContent : 'this subject';
      if (!confirm('Are you sure you want to remove "' + name + '"?')) return;
      api('subjects', { action: 'delete', id: Number(li.dataset.id) })
        .then(function () {
          li.remove();
          refreshSummary();
          toast('Subject removed', 'success');
        })
        .catch(function () { toast('Could not remove that subject', 'error'); });
    });
  }

  if (genBtn) {
    genBtn.addEventListener('click', function () {
      if (!list.querySelector('.subject-item')) { toast('Add at least one subject first', 'error'); return; }
      genBtn.disabled = true;
      genBtn.innerHTML = '<span>&#x21BB;</span> Generating...';
      api('timetable', { action: 'generate' })
        .then(function (res) {
          if (res.unplaced > 0) {
            toast('Timetable generated, but ' + res.unplaced + ' hour(s) didn\u2019t fit. Adjust days/hours in Settings.', 'warning');
          } else {
            toast('Timetable generated perfectly! 🎉', 'success');
          }
          setTimeout(function () { window.location.href = 'timetable.php'; }, 650);
        })
        .catch(function (err) {
          toast(err.message || 'Could not generate the timetable', 'error');
          genBtn.disabled = false;
          genBtn.innerHTML = 'Generate timetable';
        });
  });
  }

  /* Today's Schedule interactive toggle and undo */
  var todayList = document.getElementById('today-slots-list');
  if (todayList) {
    todayList.addEventListener('click', function (e) {
      var target = e.target;
      var item = target.closest('.today-slot-item');
      if (!item) return;

      var id = Number(item.dataset.id);
      var isDone = item.classList.contains('is-completed');
      var next = !isDone;

      api('timetable', { action: 'toggle', id: id, done: next })
        .then(function () {
          item.classList.toggle('is-completed', next);
          var checkBtn = item.querySelector('.today-check-btn');
          if (checkBtn) {
            checkBtn.classList.toggle('is-done', next);
            checkBtn.innerHTML = next ? '&#x2713;' : '<span class="circle-ring"></span>';
            checkBtn.setAttribute('title', next ? 'Completed (Click to undo)' : 'Click to complete');
          }
          if (next) {
            toast('Session marked complete! 🎯', 'success', {
              text: 'Undo',
              onClick: function () {
                api('timetable', { action: 'toggle', id: id, done: false }).then(function () {
                  item.classList.remove('is-completed');
                  if (checkBtn) {
                    checkBtn.classList.remove('is-done');
                    checkBtn.innerHTML = '<span class="circle-ring"></span>';
                  }
                  toast('Session marked incomplete (reverted) ↩', 'info');
                });
              }
            });
          } else {
            toast('Session marked incomplete (reverted) ↩', 'info');
          }
        })
        .catch(function () {
          toast('Could not update session status', 'error');
        });
    });
  }
})();
