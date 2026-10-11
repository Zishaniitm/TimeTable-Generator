/* StudySync shared helpers: toasts with Undo + JSON API wrapper + Theme manager. Plain ES6, no dependencies. */
(function () {
  var meta = function (n) { var m = document.querySelector('meta[name="' + n + '"]'); return m ? m.content : ''; };
  var BASE = meta('base-url');

  /* Enhanced Toast with Undo action support */
  window.toast = function (message, type, action) {
    var box = document.getElementById('toasts');
    if (!box) return;
    var el = document.createElement('div');
    el.className = 'toast ' + (type || 'info');

    var content = document.createElement('div');
    content.className = 'toast-content';

    var msgSpan = document.createElement('span');
    msgSpan.className = 'toast-msg';
    msgSpan.textContent = message;
    content.appendChild(msgSpan);

    if (action && action.text && typeof action.onClick === 'function') {
      var actBtn = document.createElement('button');
      actBtn.type = 'button';
      actBtn.className = 'toast-action-btn';
      actBtn.innerHTML = '<span>&#x21A9;</span> ' + action.text;
      actBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        el.remove();
        action.onClick();
      });
      content.appendChild(actBtn);
    }

    el.appendChild(content);

    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'toast-close-btn';
    closeBtn.setAttribute('aria-label', 'Close');
    closeBtn.innerHTML = '&times;';
    closeBtn.addEventListener('click', function () { el.remove(); });
    el.appendChild(closeBtn);

    box.appendChild(el);

    var timeout = action ? 5500 : 3800;
    var timer = setTimeout(function () {
      el.style.opacity = '0';
      el.style.transform = 'translateY(8px)';
      el.style.transition = 'opacity .3s ease, transform .3s ease';
      setTimeout(function () { el.remove(); }, 320);
    }, timeout);

    el.addEventListener('mouseenter', function () { clearTimeout(timer); });
  };

  /** POST JSON to /api/<name>.php. Resolves with parsed JSON, rejects with Error(message). */
  window.api = function (name, payload) {
    return fetch(BASE + '/api/' + name + '.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': meta('csrf-token') },
      body: JSON.stringify(payload || {})
    }).then(function (res) {
      return res.json().catch(function () { return {}; }).then(function (data) {
        if (res.status === 401) { window.location.href = BASE + '/auth.php'; }
        if (!res.ok || data.error) throw new Error(data.error || 'Something went wrong');
        return data;
      });
    });
  };

  window.formatHour = function (hour) {
    return (((hour + 11) % 12) + 1) + ' ' + (hour >= 12 ? 'PM' : 'AM');
  };
  window.DAY_SHORT = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
  window.DAY_FULL  = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

  /* Theme Switcher */
  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    try { localStorage.setItem('studysync_theme', theme); } catch (e) {}
    var btns = document.querySelectorAll('.theme-toggle-btn');
    btns.forEach(function (btn) {
      btn.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
      var icon = btn.querySelector('.theme-icon');
      if (icon) {
        icon.textContent = theme === 'dark' ? '\u2600\uFE0F' : '\uD83C\uDF19';
      }
    });
  }

  window.toggleTheme = function () {
    var cur = document.documentElement.getAttribute('data-theme');
    if (!cur) {
      cur = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    var next = cur === 'dark' ? 'light' : 'dark';
    applyTheme(next);
  };

  // Wire up theme buttons on page load
  document.addEventListener('DOMContentLoaded', function () {
    var stored = null;
    try { stored = localStorage.getItem('studysync_theme'); } catch (e) {}
    if (stored) {
      applyTheme(stored);
    } else {
      var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      applyTheme(prefersDark ? 'dark' : 'light');
    }

    document.querySelectorAll('.theme-toggle-btn').forEach(function (b) {
      b.addEventListener('click', function (e) {
        e.preventDefault();
        window.toggleTheme();
      });
    });
  });
})();
