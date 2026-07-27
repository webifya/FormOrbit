(function ($) {
    'use strict';

    const defaults = { text: 'Text', email: 'Email', textarea: 'Long text', select: 'Dropdown', radio: 'Radio', checkbox: 'Checkbox', number: 'Number', date: 'Date', phone: 'Phone', url: 'Website', file: 'File upload', consent: 'I agree to the terms', poll: 'Poll question', quiz: 'Quiz question', heading: 'Heading' };
    let schema = [];
    let activeStage = 0;
    let selectedId = null;
    let dirty = false;

    function uid(prefix) {
        return prefix + '_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
    }

    function load() {
        try { schema = JSON.parse($('#webform-schema').val() || '[]'); } catch (e) { schema = []; }
        if (!Array.isArray(schema) || !schema.length) schema = [{ id: uid('stage'), title: 'Stage 1', fields: [] }];
        render();
    }

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : value).html();
    }

    function render() {
        if (activeStage >= schema.length) activeStage = schema.length - 1;
        $('#webform-stage-tabs').html(schema.map((stage, i) =>
            `<button class="webform-stage-tab ${i === activeStage ? 'is-active' : ''}" data-stage="${i}">${escapeHtml(stage.title)}${schema.length > 1 ? '<span class="webform-remove-stage" title="Remove stage">×</span>' : ''}</button>`
        ).join(''));
        const fields = schema[activeStage].fields || [];
        $('#webform-canvas').html(fields.length ? fields.map(fieldCard).join('') : '<div class="webform-drop-empty"><span class="dashicons dashicons-move"></span><strong>Drop fields here</strong><small>Or click a field in the left panel</small></div>');
        $('#webform-canvas').sortable({
            items: '.webform-field-card',
            handle: '.webform-drag',
            placeholder: 'webform-sort-placeholder',
            update: function () {
                const order = $(this).children('.webform-field-card').map(function () { return $(this).data('id'); }).get();
                schema[activeStage].fields.sort((a, b) => order.indexOf(a.id) - order.indexOf(b.id));
                dirty = true;
            }
        });
        renderSettings();
    }

    function fieldCard(field) {
        const options = (field.options || []).map(option => `<span>${escapeHtml(option)}</span>`).join('');
        return `<div class="webform-field-card ${field.id === selectedId ? 'is-selected' : ''}" data-id="${field.id}">
            <span class="dashicons dashicons-menu webform-drag"></span>
            <div class="webform-field-preview"><strong>${escapeHtml(field.label)}${field.required ? ' <em>*</em>' : ''}</strong>
            ${['select','radio','checkbox','poll','quiz'].includes(field.type) ? `<div class="webform-option-preview">${options}</div>` : field.type === 'heading' ? '' : `<div class="webform-input-preview">${escapeHtml(field.placeholder || '')}</div>`}</div>
            <span class="webform-type">${escapeHtml(field.type)}</span>
            <button class="webform-remove-field" title="Remove">×</button>
        </div>`;
    }

    function selectedField() {
        return (schema[activeStage].fields || []).find(field => field.id === selectedId);
    }

    function renderSettings() {
        const field = selectedField();
        if (!field) {
            $('#webform-field-settings').html('<p class="description">Select a field to edit its options.</p>');
            return;
        }
        const choices = ['select', 'radio', 'checkbox', 'poll', 'quiz'].includes(field.type);
        const candidates = schema.flatMap(stage => stage.fields).filter(item => item.id !== field.id && item.type !== 'heading' && item.type !== 'file');
        const condition = field.condition || { enabled: false, field_id: '', operator: 'equals', value: '' };
        $('#webform-field-settings').html(`
            <label>Label<input type="text" data-prop="label" value="${escapeHtml(field.label)}"></label>
            ${!['heading','consent','file'].includes(field.type) && !choices ? `<label>Placeholder<input type="text" data-prop="placeholder" value="${escapeHtml(field.placeholder || '')}"></label>` : ''}
            ${choices ? `<label>Options <small>One per line</small><textarea rows="6" data-prop="options">${escapeHtml((field.options || []).join('\n'))}</textarea></label>` : ''}
            ${field.type === 'quiz' ? `<label>Correct answer<select data-prop="correct_answer"><option value="">Choose answer</option>${(field.options || []).map(option => `<option value="${escapeHtml(option)}" ${field.correct_answer === option ? 'selected' : ''}>${escapeHtml(option)}</option>`).join('')}</select></label><label>Points<input type="number" min="1" max="100" data-prop="points" value="${Number(field.points || 1)}"></label>` : ''}
            ${field.type === 'file' ? `<label>Allowed extensions<input type="text" data-prop="allowed_extensions" value="${escapeHtml(field.allowed_extensions || 'jpg,jpeg,png,pdf,doc,docx')}"></label><label>Maximum size (MB)<input type="number" min="1" max="20" data-prop="max_size" value="${Number(field.max_size || 5)}"></label>` : ''}
            ${field.type !== 'heading' ? `<label class="webform-check"><input type="checkbox" data-prop="required" ${field.required ? 'checked' : ''}> Required field</label>` : ''}
            ${field.type !== 'heading' && candidates.length ? `<hr><h3>Conditional display</h3>
                <label class="webform-check"><input type="checkbox" data-condition="enabled" ${condition.enabled ? 'checked' : ''}> Show this field conditionally</label>
                <div class="webform-condition-settings ${condition.enabled ? '' : 'is-hidden'}">
                    <label>When field<select data-condition="field_id"><option value="">Choose field</option>${candidates.map(item => `<option value="${item.id}" ${condition.field_id === item.id ? 'selected' : ''}>${escapeHtml(item.label)}</option>`).join('')}</select></label>
                    <label>Operator<select data-condition="operator">${[['equals','Equals'],['not_equals','Does not equal'],['contains','Contains'],['not_empty','Is not empty'],['empty','Is empty']].map(item => `<option value="${item[0]}" ${condition.operator === item[0] ? 'selected' : ''}>${item[1]}</option>`).join('')}</select></label>
                    <label>Value<input type="text" data-condition="value" value="${escapeHtml(condition.value || '')}"></label>
                </div>` : ''}
        `);
    }

    function addField(type) {
        const choices = ['select', 'radio', 'checkbox', 'poll', 'quiz'].includes(type);
        const field = { id: uid('field'), type, label: defaults[type] || 'Field', placeholder: '', required: type === 'consent', options: choices ? ['Option 1', 'Option 2'] : [], allowed_extensions: 'jpg,jpeg,png,pdf,doc,docx', max_size: 5, correct_answer: '', points: 1, condition: { enabled: false, field_id: '', operator: 'equals', value: '' } };
        schema[activeStage].fields.push(field);
        selectedId = field.id;
        dirty = true;
        render();
    }

    $(document).on('click', '.webform-palette-item', function () { addField($(this).data('type')); });
    $(document).on('click', '.webform-field-card', function (event) {
        if ($(event.target).closest('.webform-remove-field').length) return;
        selectedId = $(this).data('id');
        render();
    });
    $(document).on('click', '.webform-remove-field', function () {
        const id = $(this).closest('.webform-field-card').data('id');
        schema[activeStage].fields = schema[activeStage].fields.filter(field => field.id !== id);
        dirty = true;
        if (selectedId === id) selectedId = null;
        render();
    });
    $(document).on('input change', '#webform-field-settings [data-prop]', function () {
        const field = selectedField();
        if (!field) return;
        const prop = $(this).data('prop');
        field[prop] = prop === 'required' ? $(this).is(':checked') : prop === 'options' ? $(this).val().split('\n').map(v => v.trim()).filter(Boolean) : $(this).val();
        dirty = true;
        const caret = this.selectionStart;
        $('#webform-canvas').html(schema[activeStage].fields.map(fieldCard).join(''));
        if (prop !== 'required') {
            const input = document.querySelector(`#webform-field-settings [data-prop="${prop}"]`);
            if (input && caret != null) input.setSelectionRange(caret, caret);
        }
    });
    $(document).on('input change', '#webform-field-settings [data-condition]', function () {
        const field = selectedField();
        if (!field) return;
        field.condition = field.condition || {};
        const prop = $(this).data('condition');
        field.condition[prop] = prop === 'enabled' ? $(this).is(':checked') : $(this).val();
        dirty = true;
        if (prop === 'enabled') $('.webform-condition-settings').toggleClass('is-hidden', !field.condition.enabled);
    });
    $(document).on('click', '.webform-stage-tab', function (event) {
        if ($(event.target).hasClass('webform-remove-stage')) return;
        activeStage = Number($(this).data('stage'));
        selectedId = null;
        render();
    });
    $(document).on('dblclick', '.webform-stage-tab', function () {
        const index = Number($(this).data('stage'));
        const title = window.prompt('Stage name', schema[index].title);
        if (title && title.trim()) { schema[index].title = title.trim(); dirty = true; render(); }
    });
    $(document).on('click', '.webform-remove-stage', function (event) {
        event.stopPropagation();
        const index = Number($(this).parent().data('stage'));
        if (schema[index].fields.length && !window.confirm('Remove this stage and all of its fields?')) return;
        schema.splice(index, 1);
        dirty = true;
        activeStage = Math.max(0, activeStage - 1);
        selectedId = null;
        render();
    });
    $('#webform-add-stage').on('click', function () {
        schema.push({ id: uid('stage'), title: `Stage ${schema.length + 1}`, fields: [] });
        dirty = true;
        activeStage = schema.length - 1;
        selectedId = null;
        render();
    });
    $('#webform-save').on('click', function () {
        const $button = $(this).prop('disabled', true);
        $('#webform-save-status').text('Saving…');
        $.post(WebformAdmin.ajaxUrl, {
            action: 'webform_save_form',
            nonce: WebformAdmin.nonce,
            form_id: $('#webform-id').val(),
            name: $('#webform-name').val(),
            schema: JSON.stringify(schema),
            settings: JSON.stringify({
                success_message: $('#webform-success-message').val(),
                notification_email: $('#webform-notification-email').val(),
                submit_label: $('#webform-submit-label').val(),
                redirect_url: $('#webform-redirect-url').val(),
                webhook_url: $('#webform-webhook-url').val(),
                require_login: $('#webform-require-login').is(':checked'),
                submission_limit: $('#webform-submission-limit').val(),
                closed_message: $('#webform-closed-message').val()
            })
        }).done(function (response) {
            if (!response.success) throw new Error(response.data && response.data.message);
            $('#webform-id').val(response.data.id);
            dirty = false;
            $('#webform-save-status').html(`Saved · <code>${escapeHtml(response.data.shortcode)}</code>`);
            window.history.replaceState({}, '', `admin.php?page=webform-builder&form_id=${response.data.id}`);
        }).fail(function (xhr) {
            const message = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data.message : 'Save failed.';
            $('#webform-save-status').text(message);
        }).always(function () { $button.prop('disabled', false); });
    });
    $('#webform-name,#webform-success-message,#webform-notification-email,#webform-submit-label,#webform-redirect-url,#webform-webhook-url,#webform-require-login,#webform-submission-limit,#webform-closed-message').on('input change', function () { dirty = true; });
    window.addEventListener('beforeunload', function (event) {
        if (!dirty) return;
        event.preventDefault();
        event.returnValue = '';
    });
    $(document).on('click', '.webform-delete', function () {
        if (!window.confirm('Move this form to the trash?')) return;
        const row = $(this).closest('tr');
        $.post(WebformAdmin.ajaxUrl, { action: 'webform_delete_form', nonce: WebformAdmin.nonce, form_id: $(this).data('id') }).done(function (response) {
            if (response.success) row.fadeOut(200, function () { row.remove(); });
        });
    });

    if ($('#webform-canvas').length) load();
})(jQuery);
