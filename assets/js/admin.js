(function ($) {
    'use strict';

    const defaults = { text: 'Text', email: 'Email', textarea: 'Long text', select: 'Dropdown', radio: 'Radio', checkbox: 'Checkbox', number: 'Number', date: 'Date', time: 'Time', phone: 'Phone', url: 'Website', file: 'File upload', consent: 'I agree to the terms', poll: 'Poll question', quiz: 'Quiz question', rating: 'Rating', slider: 'Slider', hidden: 'Hidden field', html: 'HTML content', captcha: 'Security check', calculation: 'Calculation', signature: 'Signature', field_group: 'Field group', heading: 'Heading' };
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

    function previewOptions(field, control) {
        const options = field.options && field.options.length ? field.options : ['Option 1', 'Option 2'];
        return options.map((option, index) => `<span class="webform-preview-choice"><i class="${control}">${control === 'radio' && index === 0 ? '<b></b>' : control === 'check' && index === 0 ? '✓' : ''}</i>${escapeHtml(option)}</span>`).join('');
    }

    function fieldPreview(field) {
        const placeholder = escapeHtml(field.placeholder || '');
        const option = escapeHtml((field.options || [])[0] || 'Choose an option');
        const value = escapeHtml(field.default_value || '');
        const previews = {
            text: `<div class="webform-preview-control"><span>${placeholder || 'Enter text'}</span></div>`,
            email: `<div class="webform-preview-control webform-preview-with-icon"><span class="dashicons dashicons-email-alt"></span><span>${placeholder || 'name@example.com'}</span></div>`,
            textarea: `<div class="webform-preview-control webform-preview-textarea"><span>${placeholder || 'Enter a detailed response'}</span></div>`,
            select: `<div class="webform-preview-control webform-preview-select"><span>${option}</span><span class="dashicons dashicons-arrow-down-alt2"></span></div>`,
            radio: `<div class="webform-preview-choices">${previewOptions(field, 'radio')}</div>`,
            checkbox: `<div class="webform-preview-choices">${previewOptions(field, 'check')}</div>`,
            number: `<div class="webform-preview-control webform-preview-number"><span>${placeholder || '0'}</span><span class="webform-preview-steppers">⌃<br>⌄</span></div>`,
            date: '<div class="webform-preview-control webform-preview-with-icon"><span>yyyy-mm-dd</span><span class="dashicons dashicons-calendar-alt"></span></div>',
            time: '<div class="webform-preview-control webform-preview-with-icon"><span>--:-- --</span><span class="dashicons dashicons-clock"></span></div>',
            phone: `<div class="webform-preview-control webform-preview-with-icon"><span class="dashicons dashicons-phone"></span><span>${placeholder || '(555) 123-4567'}</span></div>`,
            url: `<div class="webform-preview-control webform-preview-with-icon"><span class="dashicons dashicons-admin-links"></span><span>${placeholder || 'https://example.com'}</span></div>`,
            file: `<div class="webform-preview-file"><span class="webform-preview-file-button"><span class="dashicons dashicons-upload"></span>Choose file</span><span>No file chosen</span><small>${escapeHtml(field.allowed_extensions || 'jpg, png, pdf')} · up to ${Number(field.max_size || 5)} MB</small></div>`,
            consent: `<div class="webform-preview-consent"><i class="check"></i><span>${escapeHtml(field.label || 'I agree to the terms')}</span></div>`,
            poll: `<div class="webform-preview-choices webform-preview-poll">${previewOptions(field, 'radio')}</div>`,
            quiz: `<div class="webform-preview-choices webform-preview-quiz">${previewOptions(field, 'radio')}<small>${Number(field.points || 1)} point${Number(field.points || 1) === 1 ? '' : 's'}</small></div>`,
            rating: '<div class="webform-preview-rating" aria-label="Five star rating"><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>',
            slider: `<div class="webform-preview-slider"><div><span></span></div><small>${Number(field.min ?? 0)}</small><small>${Number(field.max ?? 100)}</small></div>`,
            hidden: `<div class="webform-preview-hidden"><span class="dashicons dashicons-hidden"></span><span>Hidden value</span><code>${value || 'Not set'}</code></div>`,
            html: `<div class="webform-preview-html"><span class="dashicons dashicons-editor-code"></span><div>${escapeHtml($('<div>').html(field.html || '').text() || 'Custom HTML content')}</div></div>`,
            captcha: '<div class="webform-preview-captcha"><i class="check"></i><span>I’m not a robot</span><span class="webform-preview-recaptcha"><span class="dashicons dashicons-update"></span><small>reCAPTCHA</small></span></div>',
            heading: `<div class="webform-preview-heading">${escapeHtml(field.label || 'Section heading')}</div>`,
            calculation: `<div class="webform-preview-calculation"><strong>${Number(0).toFixed(Math.max(0, Math.min(6, Number(field.decimal_places ?? 2))))}</strong><code>${escapeHtml(field.formula || 'Formula not configured')}</code></div>`,
            field_group: `<div class="webform-preview-group" style="--preview-columns:${Math.max(1, Math.min(4, Number(field.group_columns || 2)))}">${Array.from({ length: Math.max(1, Math.min(6, Number(field.group_count || 2))) }, (_, index) => `<span><small>Grouped field ${index + 1}</small><i></i></span>`).join('')}</div>`,
            signature: '<div class="webform-preview-signature"><span>Sign here</span><svg viewBox="0 0 240 48" aria-hidden="true"><path d="M8 38c30-4 31-30 43-23 10 6-8 22-2 24 11 4 21-25 29-20 6 4-7 18 0 20 9 2 13-13 20-11 5 2 3 9 13 9 16 0 24-8 39-5"/></svg><small>Clear signature</small></div>'
        };
        return previews[field.type] || `<div class="webform-preview-control"><span>${placeholder || 'Field preview'}</span></div>`;
    }

    function render() {
        if (activeStage >= schema.length) activeStage = schema.length - 1;
        $('#webform-stage-tabs').html(schema.map((stage, i) =>
            `<button class="webform-stage-tab ${i === activeStage ? 'is-active' : ''}" data-stage="${i}"><span>${escapeHtml(stage.title)}</span><span class="dashicons dashicons-edit webform-edit-stage" title="Rename stage"></span>${schema.length > 1 ? '<span class="webform-remove-stage" title="Remove stage">×</span>' : ''}</button>`
        ).join(''));
        const fields = schema[activeStage].fields || [];
        $('#webform-canvas').html(fields.length ? fields.map(fieldCard).join('') : '<div class="webform-drop-empty"><span class="dashicons dashicons-layout"></span><strong>Start building your form</strong><small>Add a field, then drag it into the order you want.</small><button type="button" class="button button-primary webform-open-field-picker"><span class="dashicons dashicons-plus-alt2"></span>Add your first field</button></div>');
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
        const typeName = (defaults[field.type] || field.type || 'Field').replace(' question', '');
        const style = WebformAdmin.proStyling && field.style ? field.style : {};
        const cardStyle = `--preview-width:${Number(style.width || 100)}%;--preview-label:${escapeHtml(style.label_color || '#1d2327')};--preview-bg:${escapeHtml(style.background_color || '#ffffff')};--preview-text:${escapeHtml(style.text_color || '#3c434a')};--preview-radius:${Number(style.radius ?? 7)}px`;
        return `<div class="webform-field-card webform-field-card-${escapeHtml(field.type)} ${field.id === selectedId ? 'is-selected' : ''}" data-id="${field.id}" style="${cardStyle}">
            <span class="dashicons dashicons-menu webform-drag"></span>
            <div class="webform-field-preview"><strong>${escapeHtml(field.label)}${field.required ? ' <em>*</em>' : ''}</strong>
            ${fieldPreview(field)}</div>
            <span class="webform-type">${escapeHtml(typeName)}</span>
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
            <p class="description">Field ID: <code>${escapeHtml(field.id)}</code></p>
            <label>Label<input type="text" data-prop="label" value="${escapeHtml(field.label)}"></label>
            ${!['heading','consent','file','hidden','html','captcha','rating','slider'].includes(field.type) && !choices ? `<label>Placeholder<input type="text" data-prop="placeholder" value="${escapeHtml(field.placeholder || '')}"></label>` : ''}
            ${choices ? `<label>Options <small>One per line</small><textarea rows="6" data-prop="options">${escapeHtml((field.options || []).join('\n'))}</textarea></label>` : ''}
            ${field.type === 'quiz' ? `<label>Correct answer<select data-prop="correct_answer"><option value="">Choose answer</option>${(field.options || []).map(option => `<option value="${escapeHtml(option)}" ${field.correct_answer === option ? 'selected' : ''}>${escapeHtml(option)}</option>`).join('')}</select></label><label>Points<input type="number" min="1" max="100" data-prop="points" value="${Number(field.points || 1)}"></label>` : ''}
            ${field.type === 'file' ? `<label>Allowed extensions<input type="text" data-prop="allowed_extensions" value="${escapeHtml(field.allowed_extensions || 'jpg,jpeg,png,pdf,doc,docx')}"></label><label>Maximum size (MB)<input type="number" min="1" max="20" data-prop="max_size" value="${Number(field.max_size || 5)}"></label>` : ''}
            ${field.type === 'hidden' ? `<label>Default value<input type="text" data-prop="default_value" value="${escapeHtml(field.default_value || '')}"></label>` : ''}
            ${field.type === 'html' ? `<label>Safe HTML content<textarea rows="8" data-prop="html">${escapeHtml(field.html || '')}</textarea></label>` : ''}
            ${field.type === 'slider' ? `<label>Minimum<input type="number" data-prop="min" value="${Number(field.min ?? 0)}"></label><label>Maximum<input type="number" data-prop="max" value="${Number(field.max ?? 100)}"></label><label>Step<input type="number" min="0.01" step="0.01" data-prop="step" value="${Number(field.step || 1)}"></label>` : ''}
            ${field.type === 'calculation' ? `<label>Formula <small>Use field IDs in braces, for example {price} * {quantity}</small><input type="text" data-prop="formula" value="${escapeHtml(field.formula || '')}"></label><label>Decimal places<input type="number" min="0" max="6" data-prop="decimal_places" value="${Number(field.decimal_places ?? 2)}"></label>` : ''}
            ${field.type === 'field_group' ? `<label>Fields to group<input type="number" min="1" max="6" data-prop="group_count" value="${Number(field.group_count || 2)}"></label><label>Columns<input type="number" min="1" max="4" data-prop="group_columns" value="${Number(field.group_columns || 2)}"></label>` : ''}
            ${!['heading','hidden','html'].includes(field.type) ? `<label class="webform-check"><input type="checkbox" data-prop="required" ${field.required ? 'checked' : ''}> Required field</label>` : ''}
            ${field.type !== 'heading' && candidates.length ? `<hr><h3>Conditional display</h3>
                <label class="webform-check"><input type="checkbox" data-condition="enabled" ${condition.enabled ? 'checked' : ''}> Show this field conditionally</label>
                <div class="webform-condition-settings ${condition.enabled ? '' : 'is-hidden'}">
                    <label>When field<select data-condition="field_id"><option value="">Choose field</option>${candidates.map(item => `<option value="${item.id}" ${condition.field_id === item.id ? 'selected' : ''}>${escapeHtml(item.label)}</option>`).join('')}</select></label>
                    <label>Operator<select data-condition="operator">${[['equals','Equals'],['not_equals','Does not equal'],['contains','Contains'],['not_empty','Is not empty'],['empty','Is empty']].map(item => `<option value="${item[0]}" ${condition.operator === item[0] ? 'selected' : ''}>${item[1]}</option>`).join('')}</select></label>
                    <label>Value<input type="text" data-condition="value" value="${escapeHtml(condition.value || '')}"></label>
                </div>` : ''}
            <hr><h3>Field appearance ${WebformAdmin.proStyling ? '<span class="webform-pro-badge">PRO</span>' : ''}</h3>
            ${WebformAdmin.proStyling ? `<div class="webform-field-style-controls">
                <label>Field width<select data-field-style="width"><option value="100" ${(field.style?.width || '100') === '100' ? 'selected' : ''}>Full width</option><option value="75" ${field.style?.width === '75' ? 'selected' : ''}>75%</option><option value="50" ${field.style?.width === '50' ? 'selected' : ''}>Half width</option><option value="33" ${field.style?.width === '33' ? 'selected' : ''}>One third</option></select></label>
                <div class="webform-style-color-grid"><label>Label<input type="color" data-field-style="label_color" value="${escapeHtml(field.style?.label_color || '#1d2327')}"></label><label>Field<input type="color" data-field-style="background_color" value="${escapeHtml(field.style?.background_color || '#ffffff')}"></label><label>Text<input type="color" data-field-style="text_color" value="${escapeHtml(field.style?.text_color || '#1d2327')}"></label></div>
                <label>Corner radius<input type="number" min="0" max="40" data-field-style="radius" value="${Number(field.style?.radius ?? 7)}"></label>
                <label>Custom CSS class<input type="text" data-field-style="css_class" value="${escapeHtml(field.style?.css_class || '')}" placeholder="featured-field"></label>
            </div>` : '<div class="webform-field-style-locked">🔒 Width, colors, corners, and custom classes are available in Pro.</div>'}
        `);
    }

    function addField(type) {
        if (type === 'page_break') {
            schema.push({ id: uid('stage'), title: `Stage ${schema.length + 1}`, fields: [] });
            activeStage = schema.length - 1;
            selectedId = null;
            dirty = true;
            render();
            return;
        }
        const choices = ['select', 'radio', 'checkbox', 'poll', 'quiz'].includes(type);
        const field = { id: uid('field'), type, label: defaults[type] || 'Field', placeholder: '', required: ['consent','captcha','signature'].includes(type), options: choices ? ['Option 1', 'Option 2'] : [], allowed_extensions: 'jpg,jpeg,png,pdf,doc,docx', max_size: 5, correct_answer: '', points: 1, default_value: '', html: '<p>Add your content here.</p>', min: 0, max: 100, step: 1, formula: '', decimal_places: 2, group_count: 2, group_columns: 2, style: {}, condition: { enabled: false, field_id: '', operator: 'equals', value: '' } };
        schema[activeStage].fields.push(field);
        selectedId = field.id;
        dirty = true;
        render();
        $('.webform-property-tabs button[data-panel="field"]').trigger('click');
    }

    function openFieldPicker() {
        $('#webform-field-picker').addClass('is-open').attr('aria-hidden', 'false');
        $('body').addClass('webform-picker-open');
        window.setTimeout(function () { $('#webform-field-picker .webform-palette-item').first().trigger('focus'); }, 50);
    }
    function closeFieldPicker() {
        $('#webform-field-picker').removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('webform-picker-open');
    }
    $(document).on('click', '.webform-open-field-picker', openFieldPicker);
    $(document).on('click', '.webform-field-picker-close, .webform-field-picker-backdrop', closeFieldPicker);
    $(document).on('keydown', function (event) { if (event.key === 'Escape') closeFieldPicker(); });
    $(document).on('click', '.webform-palette-item', function () { addField($(this).data('type')); closeFieldPicker(); });
    function updateRecaptchaPanels() {
        const mode = $('#webform-recaptcha-mode').val() || 'enterprise';
        $('.webform-recaptcha-panel').attr('hidden', true).filter('[data-mode="' + mode + '"]').removeAttr('hidden');
    }
    $(document).on('change', '#webform-recaptcha-mode', updateRecaptchaPanels);
    updateRecaptchaPanels();
    $(document).on('click', '.webform-field-card', function (event) {
        if ($(event.target).closest('.webform-remove-field').length) return;
        selectedId = $(this).data('id');
        render();
        $('.webform-property-tabs button[data-panel="field"]').trigger('click');
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
    $(document).on('input change', '#webform-field-settings [data-field-style]', function () {
        const field = selectedField();
        if (!field || !WebformAdmin.proStyling) return;
        field.style = field.style || {};
        field.style[$(this).data('field-style')] = $(this).val();
        dirty = true;
    });
    $(document).on('click', '.webform-device-switcher button', function () {
        const device = $(this).data('device');
        $('.webform-device-switcher button').removeClass('is-active');
        $(this).addClass('is-active');
        $('.webform-canvas-panel').removeClass('webform-preview-desktop webform-preview-tablet webform-preview-mobile').addClass('webform-preview-' + device);
    });
    function currentThemeSettings() {
        return {
            style_preset: $('#webform-style-preset').val(), accent_color: $('#webform-accent-color').val(), button_text_color: $('#webform-button-text-color').val(),
            font_family: $('#webform-font-family').val(), base_font_size: $('#webform-base-font-size').val(), label_font_size: $('#webform-label-font-size').val(),
            text_color: $('#webform-text-color').val(), form_background: $('#webform-form-background').val(), field_background: $('#webform-field-background').val(),
            border_color: $('#webform-border-color').val(), form_max_width: $('#webform-form-max-width').val(), field_spacing: $('#webform-field-spacing').val(),
            field_radius: $('#webform-field-radius').val(), button_radius: $('#webform-button-radius').val(), button_padding: $('#webform-button-padding').val(),
            custom_css: $('#webform-custom-css').val()
        };
    }
    const themeSelectors = { style_preset: '#webform-style-preset', accent_color: '#webform-accent-color', button_text_color: '#webform-button-text-color', font_family: '#webform-font-family', base_font_size: '#webform-base-font-size', label_font_size: '#webform-label-font-size', text_color: '#webform-text-color', form_background: '#webform-form-background', field_background: '#webform-field-background', border_color: '#webform-border-color', form_max_width: '#webform-form-max-width', field_spacing: '#webform-field-spacing', field_radius: '#webform-field-radius', button_radius: '#webform-button-radius', button_padding: '#webform-button-padding', custom_css: '#webform-custom-css' };
    $(document).on('click', '#webform-apply-theme', function () {
        const theme = WebformAdmin.savedThemes[$('#webform-saved-theme').val()];
        if (!theme || !theme.settings) return;
        Object.entries(theme.settings).forEach(([key, value]) => { if (themeSelectors[key]) $(themeSelectors[key]).val(value); });
        dirty = true;
        $('.webform-theme-status').text('Theme applied. Save the form to keep it.');
    });
    $(document).on('click', '#webform-save-theme', function () {
        const name = window.prompt('Theme name');
        if (!name || !name.trim()) return;
        $.post(WebformAdmin.ajaxUrl, { action: 'webform_pro_save_theme', nonce: WebformAdmin.nonce, name: name.trim(), settings: JSON.stringify(currentThemeSettings()) }).done(function (response) {
            if (!response.success) return;
            WebformAdmin.savedThemes = response.data.themes;
            $('#webform-saved-theme').append(`<option value="${escapeHtml(response.data.id)}">${escapeHtml(name.trim())}</option>`).val(response.data.id);
            $('.webform-theme-status').text('Theme saved for reuse.');
        }).fail(function (xhr) { $('.webform-theme-status').text(xhr.responseJSON?.data?.message || 'Could not save theme.'); });
    });
    $(document).on('click', '#webform-delete-theme', function () {
        const id = $('#webform-saved-theme').val();
        if (!id || !window.confirm('Delete this saved theme?')) return;
        $.post(WebformAdmin.ajaxUrl, { action: 'webform_pro_delete_theme', nonce: WebformAdmin.nonce, theme_id: id }).done(function (response) {
            if (!response.success) return;
            WebformAdmin.savedThemes = response.data.themes;
            $('#webform-saved-theme option:selected').remove();
            $('.webform-theme-status').text('Theme deleted.');
        });
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
    $(document).on('click', '.webform-edit-stage', function (event) {
        event.stopPropagation();
        const index = Number($(this).closest('.webform-stage-tab').data('stage'));
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
                closed_message: $('#webform-closed-message').val(),
                style_preset: $('#webform-style-preset').val(),
                accent_color: $('#webform-accent-color').val(),
                button_text_color: $('#webform-button-text-color').val(),
                font_family: $('#webform-font-family').val(),
                base_font_size: $('#webform-base-font-size').val(),
                label_font_size: $('#webform-label-font-size').val(),
                text_color: $('#webform-text-color').val(),
                form_background: $('#webform-form-background').val(),
                field_background: $('#webform-field-background').val(),
                border_color: $('#webform-border-color').val(),
                form_max_width: $('#webform-form-max-width').val(),
                field_spacing: $('#webform-field-spacing').val(),
                field_radius: $('#webform-field-radius').val(),
                button_radius: $('#webform-button-radius').val(),
                button_padding: $('#webform-button-padding').val(),
                custom_css: $('#webform-custom-css').val()
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
    $('#webform-name,#webform-success-message,#webform-notification-email,#webform-submit-label,#webform-redirect-url,#webform-webhook-url,#webform-require-login,#webform-submission-limit,#webform-closed-message,#webform-style-preset,#webform-accent-color,#webform-button-text-color').on('input change', function () { dirty = true; });
    $(document).on('input change', '.webform-pro-style-controls input,.webform-pro-style-controls select,.webform-pro-style-controls textarea', function () { dirty = true; });
    $(document).on('click', '.webform-template-close,.webform-template-choice[href=\"#\"]', function (event) {
        event.preventDefault();
        $('#webform-template-modal').fadeOut(150);
    });
    $(document).on('click', '.webform-property-tabs button', function () {
        const panel = $(this).data('panel');
        $('.webform-property-tabs button').removeClass('is-active');
        $(this).addClass('is-active');
        $('.webform-property-panel').removeClass('is-active').filter(`[data-panel="${panel}"]`).addClass('is-active');
    });
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
