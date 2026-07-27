(function ($) {
    'use strict';

    const defaults = { text: 'Text', email: 'Email', textarea: 'Long text', select: 'Dropdown', radio: 'Radio', checkbox: 'Checkbox', number: 'Number', date: 'Date', phone: 'Phone', heading: 'Heading' };
    let schema = [];
    let activeStage = 0;
    let selectedId = null;

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
            }
        });
        renderSettings();
    }

    function fieldCard(field) {
        const options = (field.options || []).map(option => `<span>${escapeHtml(option)}</span>`).join('');
        return `<div class="webform-field-card ${field.id === selectedId ? 'is-selected' : ''}" data-id="${field.id}">
            <span class="dashicons dashicons-menu webform-drag"></span>
            <div class="webform-field-preview"><strong>${escapeHtml(field.label)}${field.required ? ' <em>*</em>' : ''}</strong>
            ${['select','radio','checkbox'].includes(field.type) ? `<div class="webform-option-preview">${options}</div>` : field.type === 'heading' ? '' : `<div class="webform-input-preview">${escapeHtml(field.placeholder || '')}</div>`}</div>
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
        const choices = ['select', 'radio', 'checkbox'].includes(field.type);
        $('#webform-field-settings').html(`
            <label>Label<input type="text" data-prop="label" value="${escapeHtml(field.label)}"></label>
            ${field.type !== 'heading' && !choices ? `<label>Placeholder<input type="text" data-prop="placeholder" value="${escapeHtml(field.placeholder || '')}"></label>` : ''}
            ${choices ? `<label>Options <small>One per line</small><textarea rows="6" data-prop="options">${escapeHtml((field.options || []).join('\n'))}</textarea></label>` : ''}
            ${field.type !== 'heading' ? `<label class="webform-check"><input type="checkbox" data-prop="required" ${field.required ? 'checked' : ''}> Required field</label>` : ''}
        `);
    }

    function addField(type) {
        const choices = ['select', 'radio', 'checkbox'].includes(type);
        const field = { id: uid('field'), type, label: defaults[type] || 'Field', placeholder: '', required: false, options: choices ? ['Option 1', 'Option 2'] : [] };
        schema[activeStage].fields.push(field);
        selectedId = field.id;
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
        if (selectedId === id) selectedId = null;
        render();
    });
    $(document).on('input change', '#webform-field-settings [data-prop]', function () {
        const field = selectedField();
        if (!field) return;
        const prop = $(this).data('prop');
        field[prop] = prop === 'required' ? $(this).is(':checked') : prop === 'options' ? $(this).val().split('\n').map(v => v.trim()).filter(Boolean) : $(this).val();
        const caret = this.selectionStart;
        $('#webform-canvas').html(schema[activeStage].fields.map(fieldCard).join(''));
        if (prop !== 'required') {
            const input = document.querySelector(`#webform-field-settings [data-prop="${prop}"]`);
            if (input && caret != null) input.setSelectionRange(caret, caret);
        }
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
        if (title && title.trim()) { schema[index].title = title.trim(); render(); }
    });
    $(document).on('click', '.webform-remove-stage', function (event) {
        event.stopPropagation();
        const index = Number($(this).parent().data('stage'));
        if (schema[index].fields.length && !window.confirm('Remove this stage and all of its fields?')) return;
        schema.splice(index, 1);
        activeStage = Math.max(0, activeStage - 1);
        selectedId = null;
        render();
    });
    $('#webform-add-stage').on('click', function () {
        schema.push({ id: uid('stage'), title: `Stage ${schema.length + 1}`, fields: [] });
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
            settings: JSON.stringify({ success_message: $('#webform-success-message').val(), notification_email: $('#webform-notification-email').val() })
        }).done(function (response) {
            if (!response.success) throw new Error(response.data && response.data.message);
            $('#webform-id').val(response.data.id);
            $('#webform-save-status').html(`Saved · <code>${escapeHtml(response.data.shortcode)}</code>`);
            window.history.replaceState({}, '', `admin.php?page=webform-builder&form_id=${response.data.id}`);
        }).fail(function (xhr) {
            const message = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data.message : 'Save failed.';
            $('#webform-save-status').text(message);
        }).always(function () { $button.prop('disabled', false); });
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
