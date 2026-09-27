(function () {
  'use strict';

  if (typeof dmSettingsAdmin === 'undefined') {
    return;
  }

  const feedback = document.getElementById('dm-settings-feedback');
  let feedbackTimer;

  function showFeedback(message, isError) {
    if (!feedback) {
      return;
    }

    feedback.hidden = false;
    feedback.textContent = message;
    feedback.className = 'dm-settings-feedback' + (isError ? ' is-error' : ' is-success');

    clearTimeout(feedbackTimer);
    feedbackTimer = setTimeout(function () {
      feedback.hidden = true;
    }, 2500);
  }

  document.querySelectorAll('.dm-toggle__input').forEach(function (input) {
    input.addEventListener('change', function () {
      const key = input.dataset.setting;
      if (!key) {
        return;
      }

      input.disabled = true;

      const body = new FormData();
      body.append('action', 'dm_save_setting');
      body.append('nonce', dmSettingsAdmin.nonce);
      body.append('key', key);
      body.append('value', input.checked ? '1' : '0');

      fetch(dmSettingsAdmin.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: body,
      })
        .then(function (res) {
          return res.json();
        })
        .then(function (json) {
          if (!json.success) {
            throw new Error((json.data && json.data.message) || 'Erro ao guardar.');
          }
          showFeedback((json.data && json.data.message) || 'Configuração guardada.');
        })
        .catch(function (err) {
          input.checked = !input.checked;
          showFeedback(err.message || 'Erro ao guardar.', true);
        })
        .finally(function () {
          input.disabled = false;
        });
    });
  });
})();
