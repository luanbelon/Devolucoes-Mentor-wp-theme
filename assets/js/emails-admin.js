(function () {
  'use strict';

  if (typeof dmEmailsAdmin === 'undefined') {
    return;
  }

  const feedback = document.getElementById('dm-test-email-feedback');
  const emailInput = document.getElementById('dm-test-email-to');
  const templateTestBtn = document.getElementById('dm-send-template-test');

  function showFeedback(message, isError) {
    if (!feedback) {
      return;
    }

    feedback.textContent = message;
    feedback.hidden = false;
    feedback.className = 'dm-emails-test__feedback' + (isError ? ' is-error' : ' is-success');
  }

  function getEditorContent(editorId) {
    if (window.tinymce) {
      const editor = tinymce.get(editorId);
      if (editor && !editor.isHidden()) {
        editor.save();
      }
    }

    const textarea = document.getElementById(editorId);
    return textarea ? textarea.value : '';
  }

  function setLoading(button, loading) {
    if (!button) {
      return;
    }

    button.disabled = loading;
    button.classList.toggle('is-loading', loading);
  }

  async function sendTestEmail(payload, button) {
    const to = (emailInput && emailInput.value.trim()) || dmEmailsAdmin.defaultEmail;

    if (!to) {
      showFeedback('Indique um email válido para receber o teste.', true);
      return;
    }

    const body = new FormData();
    body.append('action', 'dm_send_test_email');
    body.append('nonce', dmEmailsAdmin.nonce);
    body.append('to', to);

    Object.keys(payload).forEach(function (key) {
      body.append(key, payload[key]);
    });

    setLoading(button, true);
    if (feedback) {
      feedback.hidden = true;
    }

    try {
      const res = await fetch(dmEmailsAdmin.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: body,
      });
      const json = await res.json();

      if (!json.success) {
        throw new Error((json.data && json.data.message) || 'Erro ao enviar email de teste.');
      }

      showFeedback(json.data.message || 'Email de teste enviado.', false);
    } catch (err) {
      showFeedback(err.message || 'Erro ao enviar email de teste.', true);
    } finally {
      setLoading(button, false);
    }
  }

  if (templateTestBtn) {
    templateTestBtn.addEventListener('click', function () {
      sendTestEmail({ source: 'wrapper' }, templateTestBtn);
    });
  }

  const extraList = document.getElementById('dm-emails-extra-list');
  const addExtraBtn = document.getElementById('dm-add-email-interno');

  if (extraList && addExtraBtn) {
    addExtraBtn.addEventListener('click', function () {
      const row = document.createElement('div');
      row.className = 'dm-emails-extra__row';

      const input = document.createElement('input');
      input.type = 'email';
      input.className = 'dm-emails-subject';
      input.name = 'dm_emails_internos[]';
      input.placeholder = 'email@exemplo.com';

      const remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'button button-link-delete dm-emails-extra__remove';
      remove.textContent = 'Remover';

      row.appendChild(input);
      row.appendChild(remove);
      extraList.appendChild(row);
      input.focus();
    });

    extraList.addEventListener('click', function (event) {
      const button = event.target.closest('.dm-emails-extra__remove');
      if (!button) {
        return;
      }

      const row = button.closest('.dm-emails-extra__row');
      if (row) {
        row.remove();
      }
    });
  }

  document.querySelectorAll('.dm-btn-test-email').forEach(function (button) {
    button.addEventListener('click', function () {
      const subjectId = button.getAttribute('data-subject-id');
      const bodyId = button.getAttribute('data-body-id');
      const subjectField = subjectId ? document.getElementById(subjectId) : null;
      const subject = subjectField ? subjectField.value : '';
      const body = bodyId ? getEditorContent(bodyId) : '';

      if (!subject || !body.trim()) {
        showFeedback('Preencha o assunto e o corpo do email antes de enviar o teste.', true);
        return;
      }

      sendTestEmail(
        {
          source: 'custom',
          subject: subject,
          body: body,
        },
        button
      );
    });
  });
})();
