(function () {
  'use strict';

  if (typeof dmDashboard === 'undefined') {
    return;
  }

  const state = {
    rows: Array.isArray(dmDashboard.rows) ? dmDashboard.rows.slice() : [],
    view: 'dashboard',
    query: '',
  };

  const columns = Array.isArray(dmDashboard.columns) ? dmDashboard.columns : [];
  const tipos = dmDashboard.tipos || {};
  const estados = dmDashboard.estados || {};
  const equipe = dmDashboard.equipe || {};
  const colCount = dmDashboard.colCount || columns.length || 1;
  const emailTipoMap = dmDashboard.emailTipoMap || {};
  const b2bEmailRules = dmDashboard.b2bEmailRules || {};
  const subtipoLabels = dmDashboard.subtipoLabels || {};
  const viewScenario = dmDashboard.viewScenario || '';
  const viewTipo = dmDashboard.viewTipo || '';
  const showReembolsos = !!dmDashboard.showReembolsos;
  const canal = dmDashboard.canal || 'b2b';
  const emailSendingDisabled = !!dmDashboard.emailSendingDisabled;
  const canDelete = !!dmDashboard.canDelete;

  const els = {
    search: document.getElementById('dm-search'),
    count: document.getElementById('dm-count'),
    tableBody: document.getElementById('dm-table-body'),
    reembolsosBody: document.getElementById('dm-reembolsos-body'),
    kanban: document.getElementById('dm-kanban'),
    tabs: document.querySelectorAll('.dm-tabs__btn'),
    views: {
      dashboard: document.getElementById('dm-view-dashboard'),
      kanban: document.getElementById('dm-view-kanban'),
      reembolsos: document.getElementById('dm-view-reembolsos'),
    },
  };

  function toast(message, isError) {
    let node = document.querySelector('.dm-toast');
    if (!node) {
      node = document.createElement('div');
      node.className = 'dm-toast';
      document.body.appendChild(node);
    }
    node.textContent = message;
    node.classList.toggle('is-error', !!isError);
    node.classList.add('is-visible');
    clearTimeout(node._timer);
    node._timer = setTimeout(() => node.classList.remove('is-visible'), 2200);
  }

  function formatDate(value) {
    if (!value) return '—';
    const d = value.slice(0, 10);
    const parts = d.split('-');
    if (parts.length !== 3) return value;
    return parts[2] + '/' + parts[1] + '/' + parts[0];
  }

  function toDateInput(value) {
    if (!value) return '';
    return value.slice(0, 10);
  }

  function escapeHtml(str) {
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function optionHtml(map, selected, emptyLabel) {
    let html = '<option value="">' + escapeHtml(emptyLabel || '—') + '</option>';
    Object.keys(map).forEach((key) => {
      const sel = String(selected) === String(key) ? ' selected' : '';
      html += '<option value="' + escapeHtml(key) + '"' + sel + '>' + escapeHtml(map[key]) + '</option>';
    });
    return html;
  }

  function rowValue(row, column) {
    let value = row[column.key];

    if ((value === '' || value === null || value === undefined) && column.fallback) {
      value = row[column.fallback];
    }

    if (Array.isArray(value)) {
      return value;
    }

    if (typeof value === 'boolean') {
      return value;
    }

    return value ?? '';
  }

  function renderFileLinks(urls, prefix) {
    if (!Array.isArray(urls) || !urls.length) {
      return '<span class="dm-muted">—</span>';
    }

    return (
      '<div class="dm-file-links">' +
      urls
        .map((url, index) => {
          if (!url) return '';
          const label = prefix + ' ' + (index + 1);
          return (
            '<a class="dm-file-link" href="' +
            escapeHtml(url) +
            '" target="_blank" rel="noopener noreferrer">' +
            escapeHtml(label) +
            '</a>'
          );
        })
        .join('') +
      '</div>'
    );
  }

  function renderReadonlyText(value) {
    if (!value) {
      return '<span class="dm-muted">—</span>';
    }

    return '<span class="dm-readonly">' + escapeHtml(value) + '</span>';
  }

  function rowSubtipoKey(row) {
    let subtipo = row.subtipo || '';
    if (!subtipo && row.tipo_pedido_form) {
      const match = row.tipo_pedido_form.match(/\s*[—–-]\s*(.+)$/);
      if (match) {
        subtipo = match[1].trim();
      }
    }

    if (!subtipo) {
      return '';
    }

    Object.keys(subtipoLabels).forEach((key) => {
      if (key === subtipo || subtipoLabels[key] === subtipo) {
        subtipo = key;
      }
    });

    const lower = subtipo.toLowerCase();
    if (lower.includes('falta')) return 'produto_falta';
    if (lower.includes('errado')) return 'produto_errado';
    if (lower.includes('excesso') || lower.includes('mais')) return 'produto_excesso';

    return subtipo;
  }

  function detectB2bScenario(row) {
    if (row.b2b_scenario && b2bEmailRules[row.b2b_scenario]) {
      return row.b2b_scenario;
    }

    const form = (row.tipo_pedido_form || '').toLowerCase();
    let tipoKey = row.tipo_pedido_key || '';

    if (!tipoKey) {
      if (form.includes('avaria')) tipoKey = 'avaria_cliente';
      else if (form.includes('excesso')) tipoKey = 'produto_excesso';
      else if (form.includes('erro')) tipoKey = 'erro_encomenda';
      else if (form.includes('outro')) tipoKey = 'outro';
    }

    const subtipoKey = rowSubtipoKey(row);

    if (tipoKey === 'avaria_cliente') {
      return 'b2b_avaria';
    }

    if (tipoKey === 'produto_excesso') {
      return 'b2b_produto_excesso';
    }

    if (tipoKey === 'outro') {
      return 'b2b_outro';
    }

    if (tipoKey === 'erro_encomenda' && subtipoKey) {
      if (subtipoKey === 'produto_falta') return 'b2b_produto_falta';
      if (subtipoKey === 'produto_errado') return 'b2b_produto_errado';
      if (subtipoKey === 'produto_excesso') return 'b2b_produto_excesso';
    }

    return row.b2b_scenario || '';
  }

  function b2bColumnApplies(row, column) {
    if (column.when && !row[column.when]) {
      return false;
    }

    if (column.when_not && row[column.when_not]) {
      return false;
    }

    return true;
  }

  function emailAppliesToRow(row, emailKey) {
    if (canal === 'b2b' && viewScenario) {
      const rules = b2bEmailRules[viewScenario] || [];

      for (let i = 0; i < rules.length; i++) {
        const rule = rules[i];
        if (rule.key !== emailKey) {
          continue;
        }
        if (rule.when && !row[rule.when]) {
          return false;
        }
        if (rule.when_not && row[rule.when_not]) {
          return false;
        }
        return true;
      }

      return false;
    }

    if (canal === 'b2c' && viewTipo) {
      const keys = emailTipoMap[viewTipo];
      if (!Array.isArray(keys)) {
        return false;
      }
      return keys.includes(emailKey);
    }

    const tipo = row.tipo_pedido || '';
    const keys = emailTipoMap[tipo];
    if (!Array.isArray(keys)) {
      return false;
    }
    return keys.includes(emailKey);
  }

  function renderEmailButton(row, column) {
    const emailKey = column.email_key || column.field || column.key;
    const sentKey = column.sent_key || emailKey + '_enviado';
    const sent = !!row[sentKey];

    if (sent) {
      return '<span class="dm-email-sent" title="Email já enviado">Enviado</span>';
    }

    if (!emailAppliesToRow(row, emailKey)) {
      return '<span class="dm-muted">—</span>';
    }

    return (
      '<button type="button" class="dm-btn-email" data-email-key="' +
      escapeHtml(emailKey) +
      '"' +
      (emailSendingDisabled
        ? ' disabled title="Envio de email desativado para a equipa"'
        : '') +
      '>Enviar</button>'
    );
  }

  function destroyEmailEditor() {
    if (window.wp && wp.editor && typeof wp.editor.remove === 'function') {
      wp.editor.remove('dm-email-modal-editor');
    }
  }

  function initEmailEditor(content) {
    destroyEmailEditor();

    const textarea = document.getElementById('dm-email-modal-editor');
    if (!textarea) {
      return;
    }

    textarea.value = content || '';

    if (window.wp && wp.editor && typeof wp.editor.initialize === 'function') {
      wp.editor.initialize('dm-email-modal-editor', {
        tinymce: {
          wpautop: true,
          toolbar1: 'formatselect,bold,italic,underline,bullist,numlist,link,unlink,undo,redo',
          toolbar2: '',
          height: 280,
        },
        quicktags: {
          buttons: 'strong,em,link,ul,ol,li,close',
        },
        mediaButtons: false,
      });
    }
  }

  function getEmailEditorContent() {
    if (window.tinymce) {
      const editor = tinymce.get('dm-email-modal-editor');
      if (editor && !editor.isHidden()) {
        editor.save();
      }
    }

    if (window.wp && wp.editor && typeof wp.editor.getContent === 'function') {
      const content = wp.editor.getContent('dm-email-modal-editor');
      if (content) {
        return content;
      }
    }

    const textarea = document.getElementById('dm-email-modal-editor');
    return textarea ? textarea.value : '';
  }

  function closeEmailModal() {
    const modal = document.getElementById('dm-email-modal');
    if (modal) {
      modal.hidden = true;
    }
    destroyEmailEditor();
    pendingEmailSend = null;
  }

  function ensureEmailModal() {
    let modal = document.getElementById('dm-email-modal');
    if (modal) {
      return modal;
    }

    modal = document.createElement('div');
    modal.id = 'dm-email-modal';
    modal.className = 'dm-modal';
    modal.hidden = true;
    modal.innerHTML =
      '<div class="dm-modal__backdrop" data-close-modal></div>' +
      '<div class="dm-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="dm-email-modal-title">' +
      '<div class="dm-modal__content">' +
      '<div class="dm-modal__header">' +
      '<h2 id="dm-email-modal-title" class="dm-modal__title">Confirmar envio de email</h2>' +
      '<p class="dm-modal__intro">Revise e edite o conteúdo antes de enviar. As alterações aplicam-se apenas a este envio.</p>' +
      '</div>' +
      '<div class="dm-modal__meta"><strong>Para:</strong> <span id="dm-email-modal-to"></span></div>' +
      '<div class="dm-modal__meta"><strong>Assunto:</strong> <span id="dm-email-modal-subject"></span></div>' +
      '<div class="dm-modal__editor-wrap">' +
      '<label for="dm-email-modal-editor" class="dm-modal__editor-label">Conteúdo do email</label>' +
      '<textarea id="dm-email-modal-editor" class="dm-modal__editor"></textarea>' +
      '</div>' +
      '<div class="dm-modal__actions">' +
      '<button type="button" class="button dm-modal__cancel" data-close-modal>Cancelar</button>' +
      '<button type="button" class="button button-primary dm-modal__confirm" id="dm-email-modal-send">Confirmar envio</button>' +
      '</div>' +
      '</div>' +
      '</div>';
    document.body.appendChild(modal);

    modal.querySelectorAll('[data-close-modal]').forEach((el) => {
      el.addEventListener('click', closeEmailModal);
    });

    return modal;
  }

  let pendingEmailSend = null;

  async function openEmailConfirm(postId, emailKey, button) {
    if (emailSendingDisabled) {
      toast('O envio de email pela equipa está desativado.', true);
      return;
    }

    const body = new FormData();
    body.append('action', 'dm_preview_email');
    body.append('nonce', dmDashboard.nonce);
    body.append('post_id', String(postId));
    body.append('email_key', emailKey);

    if (button) {
      button.disabled = true;
    }

    try {
      const res = await fetch(dmDashboard.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body,
      });
      const json = await res.json();
      if (!json.success) {
        throw new Error((json.data && json.data.message) || 'Erro ao preparar email');
      }

      const modal = ensureEmailModal();
      document.getElementById('dm-email-modal-to').textContent = json.data.to;
      document.getElementById('dm-email-modal-subject').textContent = json.data.subject;
      initEmailEditor(json.data.body || '');

      pendingEmailSend = { postId, emailKey, button };
      modal.hidden = false;

      const sendBtn = document.getElementById('dm-email-modal-send');
      sendBtn.onclick = () => confirmEmailSend();
    } catch (err) {
      toast(err.message || 'Erro ao preparar email', true);
    } finally {
      if (button) {
        button.disabled = false;
      }
    }
  }

  async function confirmEmailSend() {
    if (!pendingEmailSend) {
      return;
    }

    const { postId, emailKey, button } = pendingEmailSend;
    const sendBtn = document.getElementById('dm-email-modal-send');
    const emailBody = getEmailEditorContent().trim();

    if (!emailBody) {
      toast('O conteúdo do email não pode estar vazio.', true);
      return;
    }

    sendBtn.disabled = true;

    const body = new FormData();
    body.append('action', 'dm_send_email');
    body.append('nonce', dmDashboard.nonce);
    body.append('post_id', String(postId));
    body.append('email_key', emailKey);
    body.append('email_body', emailBody);

    try {
      const res = await fetch(dmDashboard.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body,
      });
      const json = await res.json();
      if (!json.success) {
        throw new Error((json.data && json.data.message) || 'Erro ao enviar email');
      }

      upsertRow(json.data.row);
      closeEmailModal();
      render();
      toast('Email enviado');
    } catch (err) {
      toast(err.message || 'Erro ao enviar email', true);
    } finally {
      sendBtn.disabled = false;
      if (button) {
        button.disabled = false;
      }
    }
  }

  function renderReadonlyMultiline(value) {
    if (!value) {
      return '<span class="dm-muted">—</span>';
    }

    return '<div class="dm-readonly dm-readonly--multiline">' + escapeHtml(value) + '</div>';
  }

  function renderReadonlySubtipo(row) {
    const raw = row.subtipo || '';
    let display = raw;

    if (!display && row.tipo_pedido_form) {
      const match = row.tipo_pedido_form.match(/\s*[—–-]\s*(.+)$/);
      if (match) {
        display = match[1].trim();
      }
    }

    Object.keys(subtipoLabels).forEach((key) => {
      if (key === display || subtipoLabels[key] === display) {
        display = subtipoLabels[key];
      }
    });

    if (!display) {
      return '<span class="dm-muted">—</span>';
    }

    return '<span class="dm-readonly">' + escapeHtml(display) + '</span>';
  }

  function renderCell(row, column) {
    if (column.type !== 'actions' && canal === 'b2b' && !b2bColumnApplies(row, column)) {
      return '<td><span class="dm-muted">—</span></td>';
    }

    const type = column.type;
    const field = column.field || column.key;
    const value = rowValue(row, column);

    switch (type) {
      case 'id':
        return (
          '<td><span class="dm-id">' + escapeHtml(value || '—') + '</span></td>'
        );

      case 'date':
        return '<td>' + escapeHtml(formatDate(value)) + '</td>';

      case 'readonly':
        return '<td>' + renderReadonlyText(value) + '</td>';

      case 'readonly_subtipo':
        return '<td>' + renderReadonlySubtipo(row) + '</td>';

      case 'readonly_multiline':
        return '<td>' + renderReadonlyMultiline(value) + '</td>';

      case 'files':
        return (
          '<td>' + renderFileLinks(value, column.files_prefix || 'Ficheiro') + '</td>'
        );

      case 'textarea':
        return (
          '<td><textarea class="dm-cell-textarea" data-field="' +
          escapeHtml(field) +
          '" rows="2">' +
          escapeHtml(value) +
          '</textarea></td>'
        );

      case 'text':
        const ph =
          column.placeholder ? ' placeholder="' + escapeHtml(column.placeholder) + '"' : '';
        return (
          '<td><input class="dm-cell-input" data-field="' +
          escapeHtml(field) +
          '" type="text" value="' +
          escapeHtml(value) +
          '"' +
          ph +
          (field === 'valor' && !column.placeholder ? ' placeholder="0,00"' : '') +
          '></td>'
        );

      case 'select_tipo':
        return (
          '<td><select class="dm-cell-select" data-field="' +
          escapeHtml(field) +
          '">' +
          optionHtml(tipos, value, 'Selecionar…') +
          '</select></td>'
        );

      case 'select_estado':
        return (
          '<td><select class="dm-cell-select" data-field="' +
          escapeHtml(field) +
          '">' +
          optionHtml(estados, value, 'Selecionar…') +
          '</select></td>'
        );

      case 'select_user':
        return (
          '<td><select class="dm-cell-select" data-field="' +
          escapeHtml(field) +
          '">' +
          optionHtml(equipe, value, 'Selecionar…') +
          '</select></td>'
        );

      case 'checkbox':
        return (
          '<td><input class="dm-check" data-field="' +
          escapeHtml(field) +
          '" type="checkbox"' +
          (row[column.key] ? ' checked' : '') +
          '></td>'
        );

      case 'email_button':
        return '<td>' + renderEmailButton(row, column) + '</td>';

      case 'date_input':
        return (
          '<td><input class="dm-cell-date" data-field="' +
          escapeHtml(field) +
          '" type="date" value="' +
          escapeHtml(toDateInput(value)) +
          '"></td>'
        );

      case 'days':
        return (
          '<td><span class="dm-days">' +
          escapeHtml(String(row.dias_em_processo ?? 0)) +
          '</span></td>'
        );

      case 'actions':
        return '<td class="dm-cell-actions">' + renderDeleteButton(row) + '</td>';

      default:
        return '<td><span class="dm-muted">—</span></td>';
    }
  }

  function renderTableRow(row) {
    return (
      '<tr data-post-id="' +
      row.post_id +
      '">' +
      columns.map((column) => renderCell(row, column)).join('') +
      '</tr>'
    );
  }

  function filteredRows() {
    const q = state.query.trim().toLowerCase();
    let rows = state.rows;

    if (state.view === 'reembolsos') {
      rows = rows.filter((r) => r.estado === 'aguardar_reembolso_financeiro');
    }

    if (!q) return rows;

    return rows.filter((r) => {
      const hay = [
        r.pedido_id,
        r.empresa,
        r.nif,
        r.contacto,
        r.telefone,
        r.tipo_pedido_form,
        r.subtipo,
        r.referencia,
        r.quantidade,
        r.descricao,
        r.motivo,
        r.marca,
        r.sku,
        r.num_encomenda,
        r.nome_cliente,
        r.email_cliente,
        r.observacoes,
        tipos[r.tipo_pedido] || '',
        estados[r.estado] || '',
      ]
        .join(' ')
        .toLowerCase();
      return hay.includes(q);
    });
  }

  function upsertRow(row) {
    const idx = state.rows.findIndex((r) => r.post_id === row.post_id);
    if (idx >= 0) {
      state.rows[idx] = row;
    } else {
      state.rows.unshift(row);
    }
  }

  async function saveField(postId, field, value, control) {
    if (control) {
      control.disabled = true;
    }

    const body = new FormData();
    body.append('action', 'dm_update_field');
    body.append('nonce', dmDashboard.nonce);
    body.append('post_id', String(postId));
    body.append('field', field);
    body.append('value', value);

    try {
      const res = await fetch(dmDashboard.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body,
      });
      const json = await res.json();
      if (!json.success) {
        throw new Error((json.data && json.data.message) || 'Erro ao guardar');
      }
      upsertRow(json.data.row);
      render();
      toast('Guardado');
    } catch (err) {
      toast(err.message || 'Erro ao guardar', true);
      render();
    } finally {
      if (control) {
        control.disabled = false;
      }
    }
  }

  function bindEmailButtons(root) {
    root.querySelectorAll('.dm-btn-email').forEach((btn) => {
      btn.addEventListener('click', () => {
        if (btn.disabled || emailSendingDisabled) {
          return;
        }
        const postId = Number(btn.closest('[data-post-id]').dataset.postId);
        const emailKey = btn.dataset.emailKey;
        openEmailConfirm(postId, emailKey, btn);
      });
    });
  }

  function renderDeleteButton(row, compact) {
    if (!canDelete) {
      return '';
    }

    const pedidoId = row.pedido_id || '';
    const extra = compact ? ' dm-btn-delete--icon' : '';
    const content = compact
      ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>'
      : 'Excluir';

    return (
      '<button type="button" class="dm-btn-delete' +
      extra +
      '" data-delete-pedido data-pedido-id="' +
      escapeHtml(pedidoId) +
      '" aria-label="Excluir pedido ' +
      escapeHtml(pedidoId || String(row.post_id)) +
      '">' +
      content +
      '</button>'
    );
  }

  let pendingDelete = null;

  function closeDeleteModal() {
    const modal = document.getElementById('dm-delete-modal');
    if (modal) {
      modal.hidden = true;
    }
    pendingDelete = null;
  }

  function ensureDeleteModal() {
    let modal = document.getElementById('dm-delete-modal');
    if (modal) {
      return modal;
    }

    modal = document.createElement('div');
    modal.id = 'dm-delete-modal';
    modal.className = 'dm-modal dm-modal--confirm';
    modal.hidden = true;
    modal.innerHTML =
      '<div class="dm-modal__backdrop" data-close-delete-modal></div>' +
      '<div class="dm-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="dm-delete-modal-title">' +
      '<div class="dm-modal__content">' +
      '<div class="dm-modal__header">' +
      '<h2 id="dm-delete-modal-title" class="dm-modal__title">Excluir pedido</h2>' +
      '<p class="dm-modal__intro" id="dm-delete-modal-text">Esta ação não pode ser revertida.</p>' +
      '</div>' +
      '<div class="dm-modal__actions">' +
      '<button type="button" class="button dm-modal__cancel" data-close-delete-modal>Cancelar</button>' +
      '<button type="button" class="button button-primary dm-modal__confirm dm-modal__confirm--danger" id="dm-delete-modal-confirm">Excluir</button>' +
      '</div>' +
      '</div>' +
      '</div>';
    document.body.appendChild(modal);

    modal.querySelectorAll('[data-close-delete-modal]').forEach((el) => {
      el.addEventListener('click', closeDeleteModal);
    });

    document.getElementById('dm-delete-modal-confirm').addEventListener('click', confirmDeletePedido);

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal && !modal.hidden) {
        closeDeleteModal();
      }
    });

    return modal;
  }

  function openDeleteConfirm(postId, pedidoId, button) {
    if (!canDelete) {
      return;
    }

    pendingDelete = { postId, button };
    const modal = ensureDeleteModal();
    const label = pedidoId ? ' o pedido ' + pedidoId : ' este pedido';
    document.getElementById('dm-delete-modal-text').textContent =
      'Tem a certeza de que deseja excluir' + label + '? Esta ação não pode ser revertida.';
    modal.hidden = false;
  }

  async function confirmDeletePedido() {
    if (!pendingDelete) {
      return;
    }

    const { postId, button } = pendingDelete;
    const confirmBtn = document.getElementById('dm-delete-modal-confirm');
    confirmBtn.disabled = true;
    if (button) {
      button.disabled = true;
    }

    const body = new FormData();
    body.append('action', 'dm_delete_pedido');
    body.append('nonce', dmDashboard.nonce);
    body.append('post_id', String(postId));

    try {
      const res = await fetch(dmDashboard.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body,
      });
      const json = await res.json();
      if (!json.success) {
        throw new Error((json.data && json.data.message) || 'Erro ao excluir');
      }

      state.rows = state.rows.filter((row) => Number(row.post_id) !== Number(postId));
      closeDeleteModal();
      render();
      toast('Pedido excluído');
    } catch (err) {
      toast(err.message || 'Erro ao excluir', true);
      if (button) {
        button.disabled = false;
      }
    } finally {
      confirmBtn.disabled = false;
    }
  }

  function bindDeleteButtons(root) {
    if (!root || !canDelete) {
      return;
    }

    root.querySelectorAll('[data-delete-pedido]').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const wrap = btn.closest('[data-post-id]');
        if (!wrap) {
          return;
        }
        const postId = Number(wrap.dataset.postId);
        const pedidoId = btn.dataset.pedidoId || '';
        openDeleteConfirm(postId, pedidoId, btn);
      });
    });
  }

  function bindCellEvents(root) {
    root.querySelectorAll('[data-field]').forEach((el) => {
      const postId = Number(el.closest('[data-post-id]').dataset.postId);
      const field = el.dataset.field;
      const eventName = el.type === 'checkbox' ? 'change' : el.tagName === 'SELECT' ? 'change' : 'change';

      if (el.tagName === 'TEXTAREA' || (el.tagName === 'INPUT' && el.type === 'text')) {
        let timer;
        el.addEventListener('input', () => {
          clearTimeout(timer);
          timer = setTimeout(() => {
            saveField(postId, field, el.value, el);
          }, 600);
        });
        el.addEventListener('blur', () => {
          clearTimeout(timer);
          saveField(postId, field, el.value, el);
        });
        return;
      }

      el.addEventListener(eventName, () => {
        const value = el.type === 'checkbox' ? (el.checked ? '1' : '0') : el.value;
        saveField(postId, field, value, el);
      });
    });
  }

  function renderTable() {
    if (!columns.length) {
      return;
    }

    const rows = filteredRows();
    els.count.textContent = String(rows.length);

    if (!rows.length) {
      els.tableBody.innerHTML =
        '<tr><td colspan="' +
        colCount +
        '"><div class="dm-empty">Nenhum pedido encontrado.</div></td></tr>';
      return;
    }

    els.tableBody.innerHTML = rows.map((row) => renderTableRow(row)).join('');
    bindCellEvents(els.tableBody);
    bindEmailButtons(els.tableBody);
    bindDeleteButtons(els.tableBody);
  }

  function renderReembolsos() {
    const rows = filteredRows();
    els.count.textContent = String(rows.length);

    const reembolsoCols = canDelete ? 7 : 6;

    if (!rows.length) {
      els.reembolsosBody.innerHTML =
        '<tr><td colspan="' +
        reembolsoCols +
        '"><div class="dm-empty">Sem reembolsos pendentes.</div></td></tr>';
      return;
    }

    els.reembolsosBody.innerHTML = rows
      .map((r) => {
        return (
          '<tr data-post-id="' +
          r.post_id +
          '">' +
          '<td><span class="dm-id">' +
          escapeHtml(r.pedido_id || '—') +
          '</span></td>' +
          '<td>' +
          escapeHtml(r.nome_cliente || '—') +
          '</td>' +
          '<td><span class="dm-pill dm-pill--estado-' +
          escapeHtml(r.estado) +
          '">' +
          escapeHtml(estados[r.estado] || r.estado) +
          '</span></td>' +
          '<td><input class="dm-cell-input" data-field="valor" type="text" value="' +
          escapeHtml(r.valor) +
          '"></td>' +
          '<td><input class="dm-cell-date" data-field="data_reembolso" type="date" value="' +
          escapeHtml(toDateInput(r.data_reembolso)) +
          '"></td>' +
          '<td><input class="dm-check" data-field="nota_credito_emitida" type="checkbox"' +
          (r.nota_credito_emitida ? ' checked' : '') +
          '></td>' +
          (canDelete ? '<td class="dm-cell-actions">' + renderDeleteButton(r) + '</td>' : '') +
          '</tr>'
        );
      })
      .join('');

    bindCellEvents(els.reembolsosBody);
    bindDeleteButtons(els.reembolsosBody);
  }

  function renderKanban() {
    const rows = filteredRows().filter((r) => !!r.estado);
    els.count.textContent = String(rows.length);

    const byEstado = {};
    Object.keys(estados).forEach((key) => {
      byEstado[key] = [];
    });
    rows.forEach((r) => {
      if (byEstado[r.estado]) {
        byEstado[r.estado].push(r);
      }
    });

    els.kanban.innerHTML = Object.keys(estados)
      .map((key) => {
        const cards = byEstado[key]
          .map((r) => {
            return (
              '<article class="dm-card" draggable="true" data-post-id="' +
              r.post_id +
              '">' +
              '<div class="dm-card__id">' +
              escapeHtml(r.pedido_id || '—') +
              '</div>' +
              '<p class="dm-card__title">' +
              escapeHtml(r.nome_cliente || 'Sem nome') +
              '</p>' +
              '<p class="dm-card__meta">' +
              escapeHtml(r.marca || '—') +
              ' · ' +
              escapeHtml(r.referencia || r.sku || '—') +
              '</p>' +
              '<div class="dm-card__footer">' +
              '<span class="dm-days">' +
              escapeHtml(String(r.dias_em_processo ?? 0)) +
              'd</span>' +
              '<span class="dm-card__meta">' +
              escapeHtml(formatDate(r.data_pedido)) +
              '</span>' +
              (canDelete ? renderDeleteButton(r, true) : '') +
              '</div>' +
              '</article>'
            );
          })
          .join('');

        return (
          '<div class="dm-kanban__col" data-estado="' +
          escapeHtml(key) +
          '">' +
          '<div class="dm-kanban__head">' +
          '<span class="dm-pill dm-pill--estado-' +
          escapeHtml(key) +
          '">' +
          escapeHtml(estados[key]) +
          '</span>' +
          '<span class="dm-kanban__count">' +
          byEstado[key].length +
          '</span>' +
          '</div>' +
          '<div class="dm-kanban__list" data-drop-estado="' +
          escapeHtml(key) +
          '">' +
          (cards || '<div class="dm-empty" style="padding:16px">Vazio</div>') +
          '</div>' +
          '</div>'
        );
      })
      .join('');

    bindKanbanDnD();
    bindDeleteButtons(els.kanban);
  }

  function bindKanbanDnD() {
    let draggedId = null;

    els.kanban.querySelectorAll('.dm-card').forEach((card) => {
      card.addEventListener('dragstart', () => {
        draggedId = Number(card.dataset.postId);
        card.classList.add('is-dragging');
      });
      card.addEventListener('dragend', () => {
        card.classList.remove('is-dragging');
        draggedId = null;
        els.kanban.querySelectorAll('.dm-kanban__list').forEach((list) => {
          list.classList.remove('is-dragover');
        });
      });
    });

    els.kanban.querySelectorAll('.dm-kanban__list').forEach((list) => {
      list.addEventListener('dragover', (e) => {
        e.preventDefault();
        list.classList.add('is-dragover');
      });
      list.addEventListener('dragleave', () => {
        list.classList.remove('is-dragover');
      });
      list.addEventListener('drop', (e) => {
        e.preventDefault();
        list.classList.remove('is-dragover');
        if (!draggedId) return;
        const estado = list.dataset.dropEstado;
        saveField(draggedId, 'estado', estado);
      });
    });
  }

  function render() {
    if (state.view === 'kanban') {
      renderKanban();
    } else if (state.view === 'reembolsos' && showReembolsos) {
      renderReembolsos();
    } else {
      renderTable();
    }
  }

  function setView(view) {
    state.view = view;
    els.tabs.forEach((btn) => {
      const active = btn.dataset.view === view;
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    Object.keys(els.views).forEach((key) => {
      const node = els.views[key];
      if (!node) return;
      if (key === view) {
        node.hidden = false;
        node.classList.add('is-active');
      } else {
        node.hidden = true;
        node.classList.remove('is-active');
      }
    });
    render();
  }

  els.tabs.forEach((btn) => {
    btn.addEventListener('click', () => setView(btn.dataset.view));
  });

  if (els.search) {
    els.search.addEventListener('input', () => {
      state.query = els.search.value;
      render();
    });
  }

    if (els.tableBody && columns.length) {
    bindCellEvents(els.tableBody);
    bindEmailButtons(els.tableBody);
    bindDeleteButtons(els.tableBody);
  }

  setView('dashboard');
})();
