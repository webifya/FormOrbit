(function ($) {
    'use strict';

    const fieldLabels = {
        name: 'Name',
        text: 'Text',
        email: 'Email',
        textarea: 'Long text',
        select: 'Dropdown',
        radio: 'Radio',
        checkbox: 'Checkbox',
        number: 'Number',
        date: 'Date',
        time: 'Time',
        phone: 'Phone',
        url: 'Website',
        file: 'File upload',
        consent: 'Consent',
        poll: 'Poll question',
        quiz: 'Quiz question',
        rating: 'Rating',
        slider: 'Slider',
        hidden: 'Hidden field',
        html: 'HTML content',
        captcha: 'Security check',
        heading: 'Heading'
    };
    const choiceTypes = ['select', 'radio', 'checkbox', 'poll', 'quiz'];
    const fieldExtensions = {};
    let schema = [];
    let activeStage = 0;
    let selectedId = null;
    let dirty = false;
    let history = [];
    let historyIndex = -1;
    let historyTimer = null;

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function uid(prefix) {
        return prefix + '_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
    }

    function snapshot() {
        return JSON.stringify({ schema, activeStage, selectedId });
    }

    function updateHistoryButtons() {
        $('#webform-undo').prop('disabled', historyIndex <= 0);
        $('#webform-redo').prop('disabled', historyIndex < 0 || historyIndex >= history.length - 1);
    }

    function pushHistory() {
        const next = snapshot();
        if (history[historyIndex] === next) return;
        history = history.slice(0, historyIndex + 1);
        history.push(next);
        if (history.length > 60) history.shift();
        historyIndex = history.length - 1;
        updateHistoryButtons();
    }

    function scheduleHistory() {
        window.clearTimeout(historyTimer);
        historyTimer = window.setTimeout(pushHistory, 180);
    }

    function applyHistory(index) {
        if (!history[index]) return;
        const state = JSON.parse(history[index]);
        schema = state.schema;
        activeStage = Math.max(0, Math.min(state.activeStage, schema.length - 1));
        selectedId = state.selectedId;
        historyIndex = index;
        dirty = true;
        render();
        updateHistoryButtons();
    }

    function load() {
        const raw = $('#webform-schema').val();
        try {
            schema = raw ? JSON.parse(raw) : [];
        } catch (error) {
            schema = [];
        }
        if (!Array.isArray(schema) || !schema.length) {
            schema = [{ id: uid('stage'), title: 'Stage 1', fields: [] }];
        }
        pushHistory();
        render();
    }

    function selectedField() {
        return (schema[activeStage].fields || []).find((field) => field.id === selectedId);
    }

    function preview(field) {
        if (fieldExtensions[field.type] && typeof fieldExtensions[field.type].preview === 'function') {
            return fieldExtensions[field.type].preview(field, escapeHtml);
        }
        const placeholder = escapeHtml(field.placeholder || field.label || '');
        if (field.type === 'textarea') return `<span class="webform-preview-control webform-preview-textarea">${placeholder}</span>`;
        if (field.type === 'select') return `<span class="webform-preview-control">${escapeHtml((field.options || [])[0] || 'Select an option')} ▾</span>`;
        if (['radio', 'checkbox', 'poll', 'quiz'].includes(field.type)) {
            return `<span class="webform-preview-options">${(field.options || []).slice(0, 4).map((option) => `<i>○ ${escapeHtml(option)}</i>`).join('')}</span>`;
        }
        if (field.type === 'consent') return '<span class="webform-preview-control">□ Agreement checkbox</span>';
        if (field.type === 'rating') return '<span class="webform-preview-rating">★★★★★</span>';
        if (field.type === 'slider') return '<span class="webform-preview-control">●━━━━━━</span>';
        if (field.type === 'file') return '<span class="webform-preview-control">Choose file</span>';
        if (field.type === 'html') return `<span class="webform-preview-html">${escapeHtml($(field.html || '').text() || 'HTML content')}</span>`;
        if (field.type === 'heading') return '';
        if (field.type === 'captcha') return '<span class="webform-preview-control">Google reCAPTCHA</span>';
        if (field.type === 'hidden') return '<span class="webform-preview-control">Hidden value</span>';
        return `<span class="webform-preview-control">${placeholder}</span>`;
    }

    function fieldCard(field) {
        return `<div class="webform-field-card webform-field-card-${escapeHtml(field.type)} ${field.id === selectedId ? 'is-selected' : ''}" data-id="${escapeHtml(field.id)}">
            <span class="dashicons dashicons-menu webform-drag" aria-hidden="true"></span>
            <div class="webform-field-preview"><strong>${escapeHtml(field.label)}${field.required ? ' <em>*</em>' : ''}</strong>${preview(field)}</div>
            <span class="webform-type">${escapeHtml(fieldLabels[field.type] || field.type)}</span>
            <button type="button" class="webform-remove-field" title="Remove">×</button>
        </div>`;
    }

    function render() {
        if (activeStage >= schema.length) activeStage = schema.length - 1;
        $('#webform-stage-tabs').html(schema.map((stage, index) =>
            `<button type="button" class="webform-stage-tab ${index === activeStage ? 'is-active' : ''}" data-stage="${index}"><span>${escapeHtml(stage.title)}</span><span class="dashicons dashicons-edit webform-edit-stage" title="Rename stage"></span>${schema.length > 1 ? '<span class="webform-remove-stage" title="Remove stage">×</span>' : ''}</button>`
        ).join(''));
        const fields = schema[activeStage].fields || [];
        $('#webform-canvas').html(fields.length ? fields.map(fieldCard).join('') : '<div class="webform-drop-empty"><strong>Start building your form</strong><small>Add a field, then drag it into the order you want.</small><button type="button" class="button button-primary webform-open-field-picker">Add your first field</button></div>');
        $('#webform-canvas').sortable({
            items: '.webform-field-card',
            handle: '.webform-drag',
            update: function () {
                const order = $(this).children('.webform-field-card').map(function () { return $(this).data('id'); }).get();
                schema[activeStage].fields.sort((a, b) => order.indexOf(a.id) - order.indexOf(b.id));
                dirty = true;
                pushHistory();
            }
        });
        renderSettings();
    }

    function renderSettings() {
        const field = selectedField();
        if (!field) {
            $('#webform-field-settings').html('<p class="description">Select a field to edit its options.</p>');
            return;
        }
        const options = Object.entries(fieldLabels).map(([type, label]) => `<option value="${type}" ${field.type === type ? 'selected' : ''}>${escapeHtml(label)}</option>`).join('');
        const choices = choiceTypes.includes(field.type);
        $('#webform-field-settings').html(`
            <p class="description">Field ID: <code>${escapeHtml(field.id)}</code></p>
            <label>Field type<select data-field-type>${options}</select></label>
            <label>Label<input type="text" data-prop="label" value="${escapeHtml(field.label)}"></label>
            ${!['hidden', 'html', 'heading', 'consent', 'captcha'].includes(field.type) ? `<label class="webform-check"><input type="checkbox" data-prop="hide_label" ${field.hide_label ? 'checked' : ''}> Hide label on public form</label>` : ''}
            ${!['name', 'heading', 'consent', 'file', 'hidden', 'html', 'captcha', 'rating', 'slider'].includes(field.type) && !choices ? `<label>Placeholder<input type="text" data-prop="placeholder" value="${escapeHtml(field.placeholder || '')}"></label>` : ''}
            ${choices ? `<label>Options <small>One per line</small><textarea rows="6" data-prop="options">${escapeHtml((field.options || []).join('\n'))}</textarea></label>` : ''}
            ${['radio', 'checkbox', 'poll', 'quiz'].includes(field.type) ? `<label>Option columns<select data-prop="choice_columns">${[1, 2, 3, 4].map((column) => `<option value="${column}" ${Number(field.choice_columns || 1) === column ? 'selected' : ''}>${column}</option>`).join('')}</select></label>` : ''}
            ${field.type === 'quiz' ? `<label>Correct answer<select data-prop="correct_answer"><option value="">Choose answer</option>${(field.options || []).map((option) => `<option value="${escapeHtml(option)}" ${field.correct_answer === option ? 'selected' : ''}>${escapeHtml(option)}</option>`).join('')}</select></label><label>Points<input type="number" min="1" max="100" data-prop="points" value="${Number(field.points || 1)}"></label>` : ''}
            ${field.type === 'file' ? `<label>Allowed extensions<input type="text" data-prop="allowed_extensions" value="${escapeHtml(field.allowed_extensions || 'jpg,jpeg,png,pdf,doc,docx')}"></label><label>Maximum size (MB)<input type="number" min="1" max="20" data-prop="max_size" value="${Number(field.max_size || 5)}"></label>` : ''}
            ${field.type === 'hidden' ? `<label>Default value<input type="text" data-prop="default_value" value="${escapeHtml(field.default_value || '')}"></label>` : ''}
            ${field.type === 'html' ? `<label>Safe HTML content<textarea rows="8" data-prop="html">${escapeHtml(field.html || '')}</textarea></label>` : ''}
            ${field.type === 'textarea' ? `<label>Visible rows<input type="number" min="2" max="30" data-prop="rows" value="${Number(field.rows || 5)}"></label>` : ''}
            ${field.type === 'slider' ? `<label>Minimum<input type="number" data-prop="min" value="${Number(field.min || 0)}"></label><label>Maximum<input type="number" data-prop="max" value="${Number(field.max || 100)}"></label><label>Step<input type="number" min="0.01" step="0.01" data-prop="step" value="${Number(field.step || 1)}"></label>` : ''}
            ${!['heading', 'hidden', 'html'].includes(field.type) ? `<label class="webform-check"><input type="checkbox" data-prop="required" ${field.required ? 'checked' : ''}> Required field</label>` : ''}
        `);
        if (fieldExtensions[field.type] && typeof fieldExtensions[field.type].settings === 'function') {
            fieldExtensions[field.type].settings(field, $('#webform-field-settings'), escapeHtml);
        }
        document.dispatchEvent(new CustomEvent('formorbit:field-settings-rendered', {
            detail: { field, panel: document.getElementById('webform-field-settings') }
        }));
    }

    function addField(type) {
        if (type === 'page_break') {
            schema.push({ id: uid('stage'), title: `Stage ${schema.length + 1}`, fields: [] });
            activeStage = schema.length - 1;
            selectedId = null;
        } else if (fieldLabels[type]) {
            const extensionDefaults = fieldExtensions[type] && typeof fieldExtensions[type].defaults === 'function' ? fieldExtensions[type].defaults() : {};
            const field = Object.assign({
                id: uid('field'), type, label: fieldLabels[type], placeholder: '', hide_label: false,
                required: ['consent', 'captcha'].includes(type), options: choiceTypes.includes(type) ? ['Option 1', 'Option 2'] : [],
                choice_columns: 1, allowed_extensions: 'jpg,jpeg,png,pdf,doc,docx', max_size: 5,
                correct_answer: '', points: 1, default_value: '', html: '<p>Add your content here.</p>',
                rows: 5, min: 0, max: 100, step: 1,
                condition: { enabled: false, field_id: '', operator: 'equals', value: '' }
            }, extensionDefaults);
            schema[activeStage].fields.push(field);
            selectedId = field.id;
        }
        dirty = true;
        pushHistory();
        render();
        $('#webform-field-picker').removeClass('is-open').attr('aria-hidden', 'true');
    }

    $(document).on('click', '.webform-palette-item', function () { addField(String($(this).data('type'))); });
    $(document).on('click', '.webform-open-field-picker', function () { $('#webform-field-picker').addClass('is-open').attr('aria-hidden', 'false'); });
    $(document).on('click', '.webform-field-picker-close,.webform-field-picker-backdrop', function () { $('#webform-field-picker').removeClass('is-open').attr('aria-hidden', 'true'); });
    $(document).on('click', '.webform-field-card', function (event) { if ($(event.target).closest('.webform-remove-field').length) return; selectedId = String($(this).data('id')); render(); });
    $(document).on('click', '.webform-remove-field', function (event) { event.stopPropagation(); const id = String($(this).closest('.webform-field-card').data('id')); schema[activeStage].fields = schema[activeStage].fields.filter((field) => field.id !== id); if (selectedId === id) selectedId = null; dirty = true; pushHistory(); render(); });
    $(document).on('click', '.webform-stage-tab', function () { activeStage = Number($(this).data('stage')); selectedId = null; render(); });
    $(document).on('click', '.webform-edit-stage', function (event) { event.stopPropagation(); const index = Number($(this).closest('.webform-stage-tab').data('stage')); const title = window.prompt('Stage name', schema[index].title); if (title) { schema[index].title = title.trim(); dirty = true; pushHistory(); render(); } });
    $(document).on('click', '.webform-remove-stage', function (event) { event.stopPropagation(); const index = Number($(this).closest('.webform-stage-tab').data('stage')); if (schema.length > 1 && window.confirm('Remove this stage and its fields?')) { schema.splice(index, 1); activeStage = Math.max(0, activeStage - 1); selectedId = null; dirty = true; pushHistory(); render(); } });
    $('#webform-add-stage').on('click', function (event) { event.preventDefault(); schema.push({ id: uid('stage'), title: `Stage ${schema.length + 1}`, fields: [] }); activeStage = schema.length - 1; selectedId = null; dirty = true; pushHistory(); render(); });
    $(document).on('change', '[data-field-type]', function () { const field = selectedField(); const type = String($(this).val()); if (!field || !fieldLabels[type]) return; field.type = type; field.label = field.label || fieldLabels[type]; if (choiceTypes.includes(type) && !(field.options || []).length) field.options = ['Option 1', 'Option 2']; dirty = true; pushHistory(); render(); });
    $(document).on('input change', '#webform-field-settings [data-prop]', function (event) { const field = selectedField(); if (!field) return; const prop = String($(this).data('prop')); if (['required', 'hide_label'].includes(prop)) field[prop] = $(this).is(':checked'); else if (prop === 'options') field[prop] = String($(this).val()).split('\n').map((value) => value.trim()).filter(Boolean); else field[prop] = $(this).val(); dirty = true; scheduleHistory(); if (event.type === 'change') render(); });
    $('#webform-undo').on('click', function () { applyHistory(historyIndex - 1); });
    $('#webform-redo').on('click', function () { applyHistory(historyIndex + 1); });

    $('.webform-property-tabs button').on('click', function () {
        const panel = String($(this).data('panel'));
        $('.webform-property-tabs button').removeClass('is-active');
        $(this).addClass('is-active');
        $('.webform-property-panel').removeClass('is-active');
        $(`.webform-property-panel[data-panel="${panel}"]`).addClass('is-active');
    });

    $('#webform-save').on('click', function () {
        const button = $(this).prop('disabled', true);
        const settings = {
            success_message: $('#webform-success-message').val(), confirmation_type: $('#webform-confirmation-type').val(),
            notification_email: $('#webform-notification-email').val(), submit_label: $('#webform-submit-label').val(),
            redirect_url: $('#webform-redirect-url').val(), require_login: $('#webform-require-login').is(':checked'),
            submission_limit: $('#webform-submission-limit').val(), closed_message: $('#webform-closed-message').val(),
            style_preset: $('#webform-style-preset').val(), accent_color: $('#webform-accent-color').val(),
            button_text_color: $('#webform-button-text-color').val()
        };
        document.dispatchEvent(new CustomEvent('webform:collect-settings', { detail: settings }));
        $.post(WebformAdmin.ajaxUrl, {
            action: 'formorbit_save_form', nonce: WebformAdmin.nonce,
            form_id: $('#webform-id').val(), name: $('#webform-name').val(), schema: JSON.stringify(schema),
            settings: JSON.stringify(settings)
        }).done(function (response) {
            if (!response.success) return;
            $('#webform-id').val(response.data.id);
            dirty = false;
            if (window.history.replaceState) window.history.replaceState({}, '', WebformAdmin.formsUrl.replace('page=formorbit', `page=formorbit-builder&form_id=${response.data.id}`));
        }).always(function () { button.prop('disabled', false); });
    });

    $(document).on('click', '.webform-delete', function () {
        if (!window.confirm('Move this form to the trash?')) return;
        const row = $(this).closest('tr');
        $.post(WebformAdmin.ajaxUrl, { action: 'formorbit_delete_form', nonce: WebformAdmin.nonce, form_id: $(this).data('id') }).done(function (response) { if (response.success) row.remove(); });
    });

    $(document).on('click', '.webform-copy-shortcode', function () {
        const button = this;
        const text = String($(this).data('shortcode'));
        navigator.clipboard.writeText(text).then(function () { $(button).addClass('is-copied'); window.setTimeout(function () { $(button).removeClass('is-copied'); }, 1200); });
    });

    window.addEventListener('beforeunload', function (event) { if (dirty) { event.preventDefault(); event.returnValue = ''; } });
    window.FormOrbitBuilder = {
        registerFieldTypes(types) {
            Object.entries(types || {}).forEach(([type, definition]) => {
                fieldLabels[type] = definition.label || type;
                fieldExtensions[type] = definition;
            });
            render();
        },
        selectedField,
        render,
        markDirty() {
            dirty = true;
            scheduleHistory();
            render();
        }
    };
    load();
}(jQuery));
