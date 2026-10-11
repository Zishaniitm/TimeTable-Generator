(function () {
  var chips = document.getElementById('day-chips');
  var startSel = document.getElementById('start-hour');
  var endSel = document.getElementById('end-hour');
  var cap = document.getElementById('capacity');
  var saveBtn = document.getElementById('save-btn');
  var dlg = document.getElementById('confirm-dialog');

  function selectedDays() {
    return Array.prototype.map.call(chips.querySelectorAll('.chip.active'), function (c) { return Number(c.dataset.day); });
  }

  function updateCapacity() {
    var n = selectedDays().length * Math.max(0, Number(endSel.value) - Number(startSel.value));
    cap.textContent = 'That gives you ' + n + ' study slot' + (n === 1 ? '' : 's') + ' each week.';
  }

  chips.addEventListener('click', function (e) {
    var c = e.target.closest('.chip');
    if (!c) return;
    c.classList.toggle('active');
    updateCapacity();
  });
  startSel.addEventListener('change', updateCapacity);
  endSel.addEventListener('change', updateCapacity);

  saveBtn.addEventListener('click', function () {
    var days = selectedDays();
    if (!days.length) { toast('Pick at least one study day', 'error'); return; }
    if (Number(endSel.value) <= Number(startSel.value)) { toast('The end time must be after the start time', 'error'); return; }
    saveBtn.disabled = true;
    api('settings', { action: 'save', study_days: days, day_start_hour: Number(startSel.value), day_end_hour: Number(endSel.value) })
      .then(function () { toast('Settings saved', 'success'); })
      .catch(function (err) { toast(err.message || 'Could not save your settings', 'error'); })
      .then(function () { saveBtn.disabled = false; });
  });

  document.getElementById('delete-btn').addEventListener('click', function () { dlg.showModal(); });
  document.getElementById('cancel-delete').addEventListener('click', function () { dlg.close(); });
  document.getElementById('confirm-delete').addEventListener('click', function () {
    api('settings', { action: 'delete_account' })
      .then(function () { window.location.href = document.querySelector('meta[name="base-url"]').content + '/index.php'; })
      .catch(function () { dlg.close(); toast('Could not delete your account', 'error'); });
  });
})();
