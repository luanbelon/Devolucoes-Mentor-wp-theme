(function () {
    'use strict';

    const form = document.getElementById('form-devolucao');
    if (!form) return;

    const tipoPedido = document.getElementById('tipo_pedido');
    const subtipoWrapper = document.getElementById('subtipo-wrapper');
    const subtipo = document.getElementById('subtipo');
    const numEncomendaWrapper = document.getElementById('num-encomenda-wrapper');
    const numEncomenda = document.getElementById('num_encomenda');
    const btnSubmit = document.getElementById('btn-submit');

    /* ---- Subtipo toggle: só aparece em "Erro na encomenda" ---- */
    function toggleSubtipo() {
        const isErro = tipoPedido.value === 'erro_encomenda';

        subtipoWrapper.hidden = !isErro;
        subtipo.required = isErro;
        subtipo.disabled = !isErro;

        if (!isErro) {
            subtipo.selectedIndex = 0;
        }

        if (numEncomendaWrapper && numEncomenda) {
            numEncomendaWrapper.hidden = !isErro;
            numEncomenda.required = isErro;

            // Não usar disabled: campos disabled são omitidos do FormData e o nº não chega ao servidor.
            if (!isErro) {
                numEncomenda.value = '';
                numEncomenda.removeAttribute('name');
            } else {
                numEncomenda.setAttribute('name', 'num_encomenda');
            }
        }
    }

    tipoPedido.addEventListener('change', toggleSubtipo);
    toggleSubtipo();

    /* ---- File upload zones ---- */
    const uploadZones = document.querySelectorAll('.upload-zone');

    uploadZones.forEach(function (zone) {
        const input = zone.querySelector('.upload-zone__input');
        const preview = zone.querySelector('.upload-preview');
        const maxFiles = parseInt(zone.dataset.max, 10);
        let files = new DataTransfer();

        zone.addEventListener('dragover', function (e) {
            e.preventDefault();
            zone.classList.add('is-dragover');
        });

        zone.addEventListener('dragleave', function () {
            zone.classList.remove('is-dragover');
        });

        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            zone.classList.remove('is-dragover');
            addFiles(e.dataTransfer.files);
        });

        input.addEventListener('change', function () {
            addFiles(input.files);
        });

        function addFiles(newFiles) {
            for (let i = 0; i < newFiles.length; i++) {
                if (files.files.length >= maxFiles) break;
                files.items.add(newFiles[i]);
            }
            input.files = files.files;
            renderPreview();
        }

        function removeFile(index) {
            const updated = new DataTransfer();
            Array.from(files.files).forEach(function (file, i) {
                if (i !== index) updated.items.add(file);
            });
            files = updated;
            input.files = files.files;
            renderPreview();
        }

        function renderPreview() {
            preview.innerHTML = '';
            const fileList = files.files;

            if (fileList.length > 0) {
                zone.classList.add('has-files');
            } else {
                zone.classList.remove('has-files');
            }

            Array.from(fileList).forEach(function (file, index) {
                const item = document.createElement('div');
                item.className = 'upload-preview__item';

                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.className = 'upload-preview__thumb';
                    img.src = URL.createObjectURL(file);
                    img.alt = file.name;
                    item.appendChild(img);
                } else {
                    const iconDiv = document.createElement('div');
                    iconDiv.className = 'upload-preview__icon';
                    iconDiv.innerHTML = file.type.startsWith('video/')
                        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>'
                        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>';
                    item.appendChild(iconDiv);
                }

                const name = document.createElement('span');
                name.className = 'upload-preview__name';
                name.textContent = file.name;
                item.appendChild(name);

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'upload-preview__remove';
                removeBtn.setAttribute('aria-label', 'Remover ' + file.name);
                removeBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
                removeBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    removeFile(index);
                });
                item.appendChild(removeBtn);

                preview.appendChild(item);
            });
        }
    });

    /* ---- Form submission ---- */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const invalidFields = collectInvalidFields();
        if (invalidFields.length) {
            showErrorDialog(invalidFields);
            return;
        }

        setLoading(true);

        const formData = new FormData(form);
        formData.append('action', 'devolucoes_submit');
        formData.append('nonce', devolucoesMentor.nonce);

        fetch(devolucoesMentor.ajaxUrl, {
            method: 'POST',
            body: formData,
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                setLoading(false);
                if (data.success) {
                    resetFormState();
                    showSuccessDialog(data.data.message || 'Pedido submetido com sucesso! Entraremos em contacto em breve.');
                } else {
                    showServerError(data.data && data.data.message ? data.data.message : 'Ocorreu um erro. Tente novamente.');
                }
            })
            .catch(function () {
                setLoading(false);
                showDialog({
                    type: 'error',
                    title: 'Erro de ligação',
                    message: 'Verifique a sua internet e tente novamente.',
                });
            });
    });

    form.addEventListener('input', function (e) {
        clearFieldError(e.target);
    });

    form.addEventListener('change', function (e) {
        clearFieldError(e.target);
    });

    function setLoading(loading) {
        btnSubmit.disabled = loading;
        btnSubmit.classList.toggle('is-loading', loading);
    }

    function resetFormState() {
        form.reset();
        toggleSubtipo();
        clearErrors();
        document.querySelectorAll('.upload-preview').forEach(function (p) {
            p.innerHTML = '';
        });
        document.querySelectorAll('.upload-zone').forEach(function (z) {
            z.classList.remove('has-files');
        });
    }

    function collectInvalidFields() {
        const items = [];
        form.querySelectorAll(':invalid').forEach(function (el) {
            if (el.disabled || el.closest('[hidden]')) return;
            items.push({
                el: el,
                name: getFieldName(el),
                reason: getValidationReason(el),
            });
        });
        return items;
    }

    function getFieldName(el) {
        const field = el.closest('.form-field');
        if (field) {
            const label = field.querySelector('.form-label');
            if (label) {
                const clone = label.cloneNode(true);
                clone.querySelectorAll('.required, .form-hint-inline').forEach(function (n) {
                    n.remove();
                });
                return clone.textContent.replace(/\s+/g, ' ').trim();
            }
        }
        const checkbox = el.closest('.form-checkbox');
        if (checkbox) {
            const label = checkbox.querySelector('.form-checkbox__label');
            if (label) return label.textContent.trim();
        }
        return el.getAttribute('name') || 'Campo';
    }

    function getValidationReason(el) {
        if (el.validity.valueMissing) {
            return el.type === 'checkbox' ? 'confirmação obrigatória' : 'campo obrigatório';
        }
        if (el.validity.typeMismatch) {
            return el.type === 'email' ? 'email inválido' : 'formato inválido';
        }
        if (el.validity.patternMismatch) {
            return (el.name === 'nif') ? 'deve ter 9 dígitos' : 'formato inválido';
        }
        if (el.validity.rangeUnderflow) {
            return 'valor mínimo: ' + el.min;
        }
        return 'valor inválido';
    }

    function markInvalid(items) {
        items.forEach(function (item) {
            item.el.setAttribute('aria-invalid', 'true');
            const field = item.el.closest('.form-field');
            if (field) {
                field.classList.add('is-error');
                return;
            }
            const checkbox = item.el.closest('.form-checkbox');
            if (checkbox) checkbox.classList.add('is-error');
        });
    }

    function clearErrors() {
        form.querySelectorAll('[aria-invalid="true"]').forEach(function (el) {
            el.removeAttribute('aria-invalid');
        });
        form.querySelectorAll('.form-field.is-error').forEach(function (f) {
            f.classList.remove('is-error');
            const err = f.querySelector('.form-field__error');
            if (err) err.remove();
        });
        form.querySelectorAll('.form-checkbox.is-error').forEach(function (f) {
            f.classList.remove('is-error');
        });
    }

    function clearFieldError(el) {
        if (!el || !el.checkValidity) return;
        if (!el.checkValidity()) return;

        el.removeAttribute('aria-invalid');
        const field = el.closest('.form-field');
        if (field) {
            field.classList.remove('is-error');
            const err = field.querySelector('.form-field__error');
            if (err) err.remove();
        }
        const checkbox = el.closest('.form-checkbox');
        if (checkbox) checkbox.classList.remove('is-error');
    }

    function showErrorDialog(items) {
        const intro = items.length === 1
            ? 'Corrija o campo abaixo para continuar:'
            : 'Corrija os campos abaixo para continuar:';

        markInvalid(items);

        showDialog({
            type: 'error',
            title: 'Não foi possível submeter',
            message: intro,
            items: items,
            confirmLabel: 'Corrigir',
            onClose: function () {
                const first = items[0] && items[0].el;
                if (!first) return;
                first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                window.setTimeout(function () {
                    try { first.focus(); } catch (err) { /* ignore */ }
                }, 350);
            },
        });
    }

    function showSuccessDialog(message) {
        showDialog({
            type: 'success',
            title: 'Pedido submetido',
            message: message,
            confirmLabel: 'OK',
            onClose: function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
        });
    }

    function showServerError(message) {
        const mapped = mapServerError(message);
        if (mapped.length) markInvalid(mapped);

        showDialog({
            type: 'error',
            title: 'Não foi possível submeter',
            message: message,
            confirmLabel: mapped.length ? 'Corrigir' : 'OK',
            onClose: function () {
                if (!mapped.length) return;
                mapped[0].el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                window.setTimeout(function () {
                    try { mapped[0].el.focus(); } catch (err) { /* ignore */ }
                }, 350);
            },
        });
    }

    function mapServerError(message) {
        const text = (message || '').toLowerCase();
        const selectors = [];

        if (text.indexOf('nif') !== -1) selectors.push('#nif');
        if (text.indexOf('encomenda') !== -1) selectors.push('#num_encomenda');
        if (text.indexOf('detalhe do erro') !== -1) selectors.push('#subtipo');
        if (text.indexOf('declarações') !== -1 || text.indexOf('declaracoes') !== -1) {
            selectors.push('input[name="confirmacao_verdade"]', 'input[name="confirmacao_integridade"]');
        }
        if (text.indexOf('obrigatórios') !== -1 || text.indexOf('obrigatorios') !== -1) {
            return collectInvalidFields();
        }

        const items = [];
        selectors.forEach(function (selector) {
            const el = form.querySelector(selector);
            if (!el) return;
            items.push({ el: el, name: getFieldName(el), reason: '' });
        });
        return items;
    }

    const ICON_SUCCESS = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
    const ICON_ERROR = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';

    let dialogEl = null;
    let dialogOnClose = null;
    let lastFocus = null;

    function ensureDialog() {
        if (dialogEl) return dialogEl;

        dialogEl = document.createElement('div');
        dialogEl.className = 'form-dialog';
        dialogEl.hidden = true;
        dialogEl.innerHTML =
            '<div class="form-dialog__backdrop" data-dialog-close></div>' +
            '<div class="form-dialog__panel" role="dialog" aria-modal="true" aria-labelledby="form-dialog-title">' +
                '<div class="form-dialog__icon" aria-hidden="true"></div>' +
                '<h2 id="form-dialog-title" class="form-dialog__title"></h2>' +
                '<div id="form-dialog-body" class="form-dialog__body"></div>' +
                '<button type="button" class="form-dialog__btn" id="form-dialog-confirm">OK</button>' +
            '</div>';

        document.body.appendChild(dialogEl);

        dialogEl.addEventListener('click', function (e) {
            if (e.target.closest('[data-dialog-close]')) closeDialog();
        });

        document.getElementById('form-dialog-confirm').addEventListener('click', closeDialog);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && dialogEl && !dialogEl.hidden) {
                e.preventDefault();
                closeDialog();
            }
        });

        return dialogEl;
    }

    function showDialog(opts) {
        const dialog = ensureDialog();
        lastFocus = document.activeElement;
        dialogOnClose = typeof opts.onClose === 'function' ? opts.onClose : null;

        dialog.className = 'form-dialog is-' + (opts.type === 'success' ? 'success' : 'error');
        dialog.querySelector('.form-dialog__icon').innerHTML = opts.type === 'success' ? ICON_SUCCESS : ICON_ERROR;
        dialog.querySelector('.form-dialog__title').textContent = opts.title || '';

        const body = dialog.querySelector('.form-dialog__body');
        body.innerHTML = '';

        if (opts.message) {
            const p = document.createElement('p');
            p.textContent = opts.message;
            body.appendChild(p);
        }

        if (opts.items && opts.items.length) {
            const ul = document.createElement('ul');
            ul.className = 'form-dialog__list';
            opts.items.forEach(function (item) {
                const li = document.createElement('li');
                const strong = document.createElement('strong');
                strong.textContent = item.name;
                li.appendChild(strong);
                if (item.reason) {
                    li.appendChild(document.createTextNode(' — ' + item.reason));
                }
                ul.appendChild(li);
            });
            body.appendChild(ul);
        }

        const confirmBtn = document.getElementById('form-dialog-confirm');
        confirmBtn.textContent = opts.confirmLabel || 'OK';

        dialog.hidden = false;
        document.body.classList.add('form-dialog-open');
        confirmBtn.focus();
    }

    function closeDialog() {
        if (!dialogEl || dialogEl.hidden) return;
        dialogEl.hidden = true;
        document.body.classList.remove('form-dialog-open');

        const callback = dialogOnClose;
        dialogOnClose = null;

        if (callback) {
            callback();
            return;
        }

        if (lastFocus && typeof lastFocus.focus === 'function') {
            try { lastFocus.focus(); } catch (err) { /* ignore */ }
        }
    }
})();
