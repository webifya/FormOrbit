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
    const conversionFamilies = [
        ['text', 'email', 'phone', 'url'],
        ['textarea', 'post_body', 'post_excerpt'],
        ['select', 'radio', 'checkbox'],
        ['poll', 'quiz'],
        ['number', 'currency', 'quantity', 'donation'],
        ['date', 'time', 'appointment'],
        ['rating', 'slider', 'nps'],
        ['field_group', 'repeater'],
        ['product', 'product_option', 'shipping']
    ];
    const fieldExtensions = {};
    const registeredFieldIcons = {};
    let layoutToolsEnabled = false;
    let fieldDropHandler = null;
    let sortablePointer = null;
    let externalDropHandled = false;
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

    function compatibleFieldTypes(type) {
        const family = conversionFamilies.find((types) => types.includes(type));
        return (family || [type]).filter((candidate) => fieldLabels[candidate]);
    }

    function countryName(code) {
        try { return new Intl.DisplayNames([document.documentElement.lang || 'en'], { type: 'region' }).of(code) || code; }
        catch (error) { return code; }
    }

    function phoneCountryOptions(selected) {
        return Object.entries(WebformAdmin.phoneCountries || {}).sort((a, b) => countryName(a[0]).localeCompare(countryName(b[0]))).map(([code, prefix]) => `<option value="${code}" ${code === selected ? 'selected' : ''}>${escapeHtml(countryName(code))} (${escapeHtml(prefix)})</option>`).join('');
    }

    function nameParts(format) {
        const formats = {
            full: ['Full name'], first: ['First name'], middle: ['Middle name'], last: ['Last name'],
            first_middle: ['First name', 'Middle name'], first_last: ['First name', 'Last name'],
            middle_last: ['Middle name', 'Last name'], first_middle_last: ['First name', 'Middle name', 'Last name']
        };
        return formats[format] || formats.first_last;
    }

    function preview(field) {
        if (fieldExtensions[field.type] && typeof fieldExtensions[field.type].preview === 'function') {
            return fieldExtensions[field.type].preview(field, escapeHtml);
        }
        const placeholder = escapeHtml(field.placeholder || field.label || '');
        if (!fieldLabels[field.type]) {
            return '<span class="webform-preview-unsupported">This field requires the plugin that created it.</span>';
        }
        if (field.type === 'name') {
            return `<span class="webform-preview-name webform-preview-name-${nameParts(field.name_format).length}">${nameParts(field.name_format).map((part) => `<i>${part}</i>`).join('')}</span>`;
        }
        if (field.type === 'textarea') return `<span class="webform-preview-control webform-preview-textarea">${placeholder}</span>`;
        if (field.type === 'select') return `<span class="webform-preview-control">${escapeHtml((field.options || [])[0] || 'Select an option')} ▾</span>`;
        if (['radio', 'checkbox', 'poll', 'quiz'].includes(field.type)) {
            const columns = Math.max(1, Math.min(4, Number(field.choice_columns || 1)));
            const marker = field.type === 'checkbox' ? '□' : '○';
            return `<span class="webform-preview-options webform-preview-choices webform-choice-columns-${columns}">${(field.options || []).slice(0, 8).map((option) => `<i>${marker} ${escapeHtml(option)}</i>`).join('')}</span>`;
        }
        if (field.type === 'consent') return `<span class="webform-preview-control">□ ${escapeHtml(field.consent_text || field.label || 'I agree')}</span>`;
        if (field.type === 'rating') return '<span class="webform-preview-rating">★★★★★</span>';
        if (field.type === 'slider') return '<span class="webform-preview-control">●━━━━━━</span>';
        if (field.type === 'file') return '<span class="webform-preview-control">Choose file</span>';
        if (field.type === 'html') return `<span class="webform-preview-html">${escapeHtml($(field.html || '').text() || 'HTML content')}</span>`;
        if (field.type === 'heading') return '';
        if (field.type === 'captcha') return '<span class="webform-preview-control">Google reCAPTCHA</span>';
        if (field.type === 'hidden') return '<span class="webform-preview-control">Hidden value</span>';
        const previewIcons = {
            email: '✉', number: '#', date: '▣', time: '◷', phone: '☎', url: '↗'
        };
        const icon = previewIcons[field.type] ? `<i class="webform-preview-icon">${previewIcons[field.type]}</i>` : '';
        return `<span class="webform-preview-control webform-preview-${escapeHtml(field.type)}">${placeholder}${icon}</span>`;
    }

    function fieldCard(field) {
        const width = field.style && field.style.width ? String(field.style.width) : '100';
        const widthValues = { auto: 'auto', 100: '100%', 75: '74%', 50: '49%', 33: '32%' };
        const widthStyle = layoutToolsEnabled ? ` style="--preview-width:${widthValues[width] || '100%'}"` : '';
        const widthClass = layoutToolsEnabled && width === 'auto' ? 'is-width-auto' : '';
        const iconClass = field.icon && registeredFieldIcons[field.icon] ? registeredFieldIcons[field.icon] : '';
        const icon = iconClass ? `<span class="dashicons ${escapeHtml(iconClass)} webform-field-icon" aria-hidden="true"></span>` : '';
        const layoutControls = layoutToolsEnabled && field.id === selectedId ? `<div class="webform-card-widths" role="group" aria-label="Field layout"><button type="button" data-card-resize="-1" title="Make field narrower" aria-label="Make field narrower">−</button><button type="button" data-card-width="auto" class="${width === 'auto' ? 'is-active' : ''}" title="Fill available row space"><span class="dashicons dashicons-editor-expand" aria-hidden="true"></span>${width === 'auto' ? 'Auto' : width + '%'}</button><button type="button" data-card-resize="1" title="Make field wider" aria-label="Make field wider">＋</button><button type="button" data-card-row="1" class="${field.row_start ? 'is-active' : ''}" title="Start a new row" aria-label="Start a new row"><span class="dashicons dashicons-arrow-down-alt" aria-hidden="true"></span></button></div>` : '';
        const duplicate = layoutToolsEnabled && field.id === selectedId ? '<button type="button" class="webform-duplicate-field" title="Duplicate field" aria-label="Duplicate field"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span></button>' : '';
        return `${field.row_start && layoutToolsEnabled ? '<span class="webform-row-break" aria-hidden="true"></span>' : ''}<div class="webform-field-card webform-field-card-${escapeHtml(field.type)} ${widthClass} ${field.id === selectedId ? 'is-selected' : ''}" data-id="${escapeHtml(field.id)}"${widthStyle}>
            <span class="dashicons dashicons-menu webform-drag" aria-hidden="true"></span>
            <div class="webform-field-preview" style="${fieldPreviewStyle(field)}"><strong>${icon}${escapeHtml(field.label)}${field.required ? ' <em>*</em>' : ''}</strong>${preview(field)}</div>
            <span class="webform-type">${escapeHtml(fieldLabels[field.type] || field.type)}</span>
            ${duplicate}
            <button type="button" class="webform-remove-field" title="Remove">×</button>
            ${layoutControls}
        </div>`;
    }

    function render() {
        if (activeStage >= schema.length) activeStage = schema.length - 1;
        $('#webform-stage-tabs').html(schema.map((stage, index) =>
            `<button type="button" class="webform-stage-tab ${index === activeStage ? 'is-active' : ''}" data-stage="${index}"><span>${escapeHtml(stage.title)}</span><span class="dashicons dashicons-edit webform-edit-stage" title="Rename stage"></span>${schema.length > 1 ? '<span class="webform-remove-stage" title="Remove stage">×</span>' : ''}</button>`
        ).join(''));
        $('#webform-canvas').html(schema.map((stage, index) => '<section class="formorbit-builder-stage" data-stage="' + index + '"><h3>' + escapeHtml(stage.title) + '</h3><div class="formorbit-stage-fields" data-stage="' + index + '">' + (stage.fields || []).map(fieldCard).join('') + '</div><button type="button" class="button formorbit-stage-add" data-stage="' + index + '">Add field</button></section>').join(''));
        $('.formorbit-stage-fields').sortable({
            connectWith: '.formorbit-stage-fields',
            items: '.webform-field-card',
            handle: '.webform-drag',
            tolerance: 'pointer',
            forcePlaceholderSize: true,
            placeholder: 'webform-sort-placeholder',
            start: function (event, ui) {
                activeStage = Number($(this).data('stage'));
                $(this).addClass('is-sorting');
                externalDropHandled = false;
                ui.placeholder.css({ width: ui.item.outerWidth(), height: ui.item.outerHeight() });
                ui.item.data('sort-original-style', ui.item.attr('style') || '');
                sortablePointer = { pageX: event.pageX, pageY: event.pageY };
            },
            sort: function (event, ui) {
                sortablePointer = { pageX: event.pageX, pageY: event.pageY };
                const canvas = $(this);
                const relativeX = Math.max(0, Math.min(1, (event.pageX - canvas.offset().left) / Math.max(1, canvas.innerWidth())));
                const columnIntent = relativeX > 0.14 && (relativeX <= 0.34 || relativeX >= 0.66);
                canvas.toggleClass('is-column-sorting', columnIntent);
                if (columnIntent) {
                    ui.item.css({ '--preview-width': '49%', 'flex-basis': '49%', 'max-width': '49%' });
                    ui.placeholder.addClass('is-column-preview').css('width', '49%');
                } else {
                    ui.item.attr('style', ui.item.data('sort-original-style'));
                    ui.placeholder.removeClass('is-column-preview').css('width', ui.item.outerWidth());
                }
                if (fieldDropHandler) fieldDropHandler({ phase: 'move', fieldId: String(ui.item.data('id')), pageX: event.pageX, pageY: event.pageY, stage: schema[activeStage] });
            },
            beforeStop: function (event, ui) {
                const pointer = sortablePointer || { pageX: event.pageX, pageY: event.pageY };
                externalDropHandled = !!(fieldDropHandler && fieldDropHandler({ phase: 'drop', fieldId: String(ui.item.data('id')), pageX: pointer.pageX, pageY: pointer.pageY, stage: schema[activeStage] }));
            },
            stop: function (event, ui) {
                $(this).removeClass('is-sorting is-column-sorting');
                if (ui && ui.item) ui.item.attr('style', ui.item.data('sort-original-style'));
                if (!externalDropHandled) {
                    // Commit connected lists together, after receive/update have finished.
                    const fields = new Map(schema.flatMap(stage => stage.fields || []).map(field => [field.id, field]));
                    $('.formorbit-stage-fields').each(function () {
                        schema[Number($(this).data('stage'))].fields = $(this).children('.webform-field-card').map(function () { return fields.get(String($(this).data('id'))); }).get().filter(Boolean);
                    });
                    activeStage = Number(ui.item.closest('.formorbit-stage-fields').data('stage'));
                }
                const pointer = sortablePointer || { pageX: event.pageX, pageY: event.pageY };
                if (!externalDropHandled && layoutToolsEnabled && ui && ui.item) {
                    const moved = schema[activeStage].fields.find((field) => field.id === String(ui.item.data('id')));
                    const position = schema[activeStage].fields.indexOf(moved);
                    if (moved) {
                        const target = ui.item.closest('.formorbit-stage-fields');
                        const canvasOffset = target.offset();
                        const pointerX = pointer.pageX || (canvasOffset.left + ui.item.position().left);
                        const relativeX = Math.max(0, Math.min(1, (pointerX - canvasOffset.left) / Math.max(1, target.innerWidth())));
                        moved.row_start = position > 0 && relativeX <= 0.14;
                        if (position > 0 && !moved.row_start && (relativeX <= 0.34 || relativeX >= 0.66)) {
                            moved.style = moved.style || {};
                            if (!moved.style.width || moved.style.width === '100') moved.style.width = '50';
                            const neighbour = schema[activeStage].fields[position - 1];
                            if (neighbour) {
                                neighbour.style = neighbour.style || {};
                                if (!neighbour.style.width || neighbour.style.width === '100') neighbour.style.width = '50';
                            }
                        }
                    }
                }
                dirty = true;
                externalDropHandled = false;
                sortablePointer = null;
                pushHistory();
                render();
            }
        });
        renderSettings();
        if (!$('#webform-live-preview-modal').prop('hidden')) renderLivePreview();
    }

    function renderSettings() {
        const field = selectedField();
        if (!field) {
            $('#webform-field-settings').html('<p class="description">Select a field to edit its options.</p>');
            return;
        }
        if (!fieldLabels[field.type] && !fieldExtensions[field.type]) {
            $('#webform-field-settings').html(`
                <div class="notice notice-warning inline webform-unsupported-notice">
                    <p><strong>Unsupported field type:</strong> ${escapeHtml(field.type)}</p>
                    <p>Activate the plugin that created this field, remove it, or convert it to a supported Free field.</p>
                </div>
                <label>Convert field to
                    <select data-field-type>
                        <option value="">Choose a field type</option>
                        ${Object.entries(fieldLabels).map(([type, label]) => `<option value="${type}">${escapeHtml(label)}</option>`).join('')}
                    </select>
                </label>
            `);
            return;
        }
        const compatibleTypes = compatibleFieldTypes(field.type);
        const options = compatibleTypes.map((type) => `<option value="${type}" ${field.type === type ? 'selected' : ''}>${escapeHtml(fieldLabels[type])}</option>`).join('');
        const choices = choiceTypes.includes(field.type);
        $('#webform-field-settings').html(`
            <p class="description">Field ID: <code>${escapeHtml(field.id)}</code></p>
            <label>Field type<select data-field-type ${compatibleTypes.length === 1 ? 'disabled' : ''}>${options}</select>${compatibleTypes.length > 1 ? '<small class="description">Only compatible field types are available so existing settings and values remain valid.</small>' : '<small class="description">This specialized field cannot be safely converted to another type.</small>'}</label>
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
            ${field.type === 'date' ? `<label>Date availability<select data-prop="date_rule"><option value="any" ${field.date_rule === 'any' || !field.date_rule ? 'selected' : ''}>Any date</option><option value="future" ${field.date_rule === 'future' ? 'selected' : ''}>Today and future dates</option><option value="past" ${field.date_rule === 'past' ? 'selected' : ''}>Today and past dates</option><option value="custom" ${field.date_rule === 'custom' ? 'selected' : ''}>Custom range</option></select></label><div class="webform-date-range" ${field.date_rule === 'custom' ? '' : 'hidden'}><label>Earliest date<input type="date" data-prop="date_min" value="${escapeHtml(field.date_min || '')}"></label><label>Latest date<input type="date" data-prop="date_max" value="${escapeHtml(field.date_max || '')}"></label></div>` : ''}
            ${field.type === 'phone' ? `<label>Default country<select data-prop="phone_country">${phoneCountryOptions(String(field.phone_country || 'US'))}</select></label><label class="webform-check"><input type="checkbox" data-prop="phone_country_selector" ${field.phone_country_selector !== false ? 'checked' : ''}> Let visitors choose a country code</label>` : ''}
            ${field.type === 'name' ? `<label>Name fields<select data-prop="name_format">${[['full','Full name (one field)'],['first','First name only'],['middle','Middle name only'],['last','Last name only'],['first_middle','First + middle'],['first_last','First + last'],['middle_last','Middle + last'],['first_middle_last','First + middle + last']].map(([value, label]) => `<option value="${value}" ${String(field.name_format || 'first_last') === value ? 'selected' : ''}>${label}</option>`).join('')}</select><small class="description">Choose exactly which name inputs visitors should complete.</small></label>` : ''}
            ${field.type === 'consent' ? `<label>Consent text<textarea rows="4" data-prop="consent_text">${escapeHtml(field.consent_text || field.label || 'I agree')}</textarea><small class="description">The complete statement visitors must accept.</small></label><label>Policy or terms URL <small>Optional</small><input type="url" data-prop="consent_url" value="${escapeHtml(field.consent_url || '')}" placeholder="https://example.com/privacy-policy"></label><label>Link text<input type="text" data-prop="consent_link_text" value="${escapeHtml(field.consent_link_text || 'Read the policy')}"></label>` : ''}
            ${field.type === 'slider' ? `<label>Minimum<input type="number" data-prop="min" value="${Number(field.min || 0)}"></label><label>Maximum<input type="number" data-prop="max" value="${Number(field.max || 100)}"></label><label>Step<input type="number" min="0.01" step="0.01" data-prop="step" value="${Number(field.step || 1)}"></label>` : ''}
            <label class="webform-check"><input type="checkbox" data-prop="row_start" ${field.row_start ? 'checked' : ''}> Start a new row before this field</label>
            ${!['heading', 'hidden', 'html'].includes(field.type) ? `<label class="webform-check"><input type="checkbox" data-prop="required" ${field.required ? 'checked' : ''}> Required field</label>` : ''}
            <details class="webform-conditional-settings" ${field.condition && field.condition.enabled ? 'open' : ''}>
                <summary>Conditional visibility</summary>
                <label class="webform-check"><input type="checkbox" data-condition-prop="enabled" ${field.condition && field.condition.enabled ? 'checked' : ''}> Show this field only when a rule matches</label>
                <div class="webform-condition-controls" ${field.condition && field.condition.enabled ? '' : 'hidden'}>
                    <label>Field<select data-condition-prop="field_id"><option value="">Choose field</option>${(schema[activeStage].fields || []).filter((candidate) => candidate.id !== field.id && fieldLabels[candidate.type]).map((candidate) => `<option value="${escapeHtml(candidate.id)}" ${field.condition && field.condition.field_id === candidate.id ? 'selected' : ''}>${escapeHtml(candidate.label)}</option>`).join('')}</select></label>
                    <label>Operator<select data-condition-prop="operator">${[['equals', 'Equals'], ['not_equals', 'Does not equal'], ['contains', 'Contains'], ['starts_with', 'Starts with'], ['ends_with', 'Ends with'], ['greater_than', 'Greater than'], ['less_than', 'Less than'], ['not_empty', 'Is not empty'], ['empty', 'Is empty']].map(([value, label]) => `<option value="${value}" ${field.condition && field.condition.operator === value ? 'selected' : ''}>${label}</option>`).join('')}</select></label>
                    <label class="webform-condition-value">Value<input type="text" data-condition-prop="value" value="${escapeHtml(field.condition && field.condition.value || '')}"></label>
                </div>
            </details>
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
                rows: 5, min: 0, max: 100, step: 1, name_format: 'first_last', consent_text: type === 'consent' ? 'I agree' : '', consent_url: '', consent_link_text: 'Read the policy',
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
    function activatePropertyPanel(panel) {
        $('.webform-property-tabs button').removeClass('is-active');
        $(`.webform-property-tabs button[data-panel="${panel}"]`).addClass('is-active');
        $('.webform-property-panel').removeClass('is-active');
        $(`.webform-property-panel[data-panel="${panel}"]`).addClass('is-active');
    }

    $(document).on('click', '.webform-field-card', function (event) {
        if ($(event.target).closest('.webform-remove-field,.webform-card-widths,.webform-duplicate-field').length) return;
        selectedId = String($(this).data('id'));
        activeStage = Number($(this).closest('.formorbit-stage-fields').data('stage'));
        render();
        activatePropertyPanel('field');
    });
    $(document).on('click', '.webform-remove-field', function (event) { event.stopPropagation(); const id = String($(this).closest('.webform-field-card').data('id')); schema[activeStage].fields = schema[activeStage].fields.filter((field) => field.id !== id); if (selectedId === id) selectedId = null; dirty = true; pushHistory(); render(); });
    $(document).on('click', '.webform-card-widths button', function (event) {
        event.stopPropagation();
        const field = selectedField();
        if (!field || !layoutToolsEnabled) return;
        field.style = field.style || {};
        if ($(this).is('[data-card-row]')) {
            const index = schema[activeStage].fields.indexOf(field);
            field.row_start = index > 0 && !field.row_start;
        } else if ($(this).is('[data-card-width]')) {
            field.style.width = String($(this).data('card-width'));
        } else {
            const widths = ['33', '50', '75', '100', 'auto'];
            const current = Math.max(0, widths.indexOf(String(field.style.width || '100')));
            const next = Math.max(0, Math.min(widths.length - 1, current + Number($(this).data('card-resize') || 0)));
            field.style.width = widths[next];
        }
        dirty = true;
        pushHistory();
        render();
    });
    $(document).on('click', '.webform-duplicate-field', function (event) {
        event.stopPropagation();
        const field = selectedField();
        if (!field || !layoutToolsEnabled) return;
        const duplicate = JSON.parse(JSON.stringify(field));
        duplicate.id = uid('field');
        function renewChildren(field) { (field.children || []).forEach(child => { child.id = uid('field'); renewChildren(child); }); }
        renewChildren(duplicate);
        duplicate.label = `${field.label} copy`;
        duplicate.row_start = false;
        const index = schema[activeStage].fields.indexOf(field);
        schema[activeStage].fields.splice(index + 1, 0, duplicate);
        selectedId = duplicate.id;
        dirty = true;
        pushHistory();
        render();
    });
    $(document).on('click', '.webform-stage-tab', function () { activeStage = Number($(this).data('stage')); selectedId = null; render(); });
    $(document).on('click', '.webform-edit-stage', function (event) { event.stopPropagation(); const index = Number($(this).closest('.webform-stage-tab').data('stage')); const title = window.prompt('Stage name', schema[index].title); if (title) { schema[index].title = title.trim(); dirty = true; pushHistory(); render(); } });
    $(document).on('click', '.webform-remove-stage', function (event) { event.stopPropagation(); const index = Number($(this).closest('.webform-stage-tab').data('stage')); if (schema.length > 1 && window.confirm('Remove this stage and its fields?')) { schema.splice(index, 1); activeStage = Math.max(0, activeStage - 1); selectedId = null; dirty = true; pushHistory(); render(); } });
    $('#webform-add-stage').on('click', function (event) { event.preventDefault(); schema.push({ id: uid('stage'), title: `Stage ${schema.length + 1}`, fields: [] }); activeStage = schema.length - 1; selectedId = null; dirty = true; pushHistory(); render(); });
    $(document).on('change', '[data-field-type]', function () { const field = selectedField(); const type = String($(this).val()); if (!field || !compatibleFieldTypes(field.type).includes(type)) return; field.type = type; if (choiceTypes.includes(type) && !(field.options || []).length) field.options = ['Option 1', 'Option 2']; dirty = true; pushHistory(); render(); });
    $(document).on('input change', '#webform-field-settings [data-prop]', function (event) { const field = selectedField(); if (!field) return; const prop = String($(this).data('prop')); if (['required', 'hide_label', 'row_start', 'phone_country_selector', 'address_country_enabled'].includes(prop)) field[prop] = $(this).is(':checked'); else if (prop === 'options') field[prop] = String($(this).val()).split('\n').map((value) => value.trim()).filter(Boolean); else field[prop] = $(this).val(); dirty = true; scheduleHistory(); if (event.type === 'change') render(); });
    $(document).on('input change', '#webform-field-settings [data-condition-prop]', function (event) {
        const field = selectedField();
        if (!field) return;
        field.condition = field.condition || { enabled: false, field_id: '', operator: 'equals', value: '' };
        const prop = String($(this).data('condition-prop'));
        field.condition[prop] = prop === 'enabled' ? $(this).is(':checked') : $(this).val();
        dirty = true;
        scheduleHistory();
        if (event.type === 'change') render();
    });
    $('#webform-undo').on('click', function () { applyHistory(historyIndex - 1); });
    $(document).on('click', '.formorbit-stage-add', function () {
        activeStage = Number($(this).data('stage'));
        $('.webform-open-field-picker').first().trigger('click');
    });
    $('#webform-redo').on('click', function () { applyHistory(historyIndex + 1); });

    $('.webform-device-switcher [data-device]').on('click', function () {
        const device = String($(this).data('device'));
        $('.webform-device-switcher [data-device]').removeClass('is-active');
        $(this).addClass('is-active');
        $('.webform-canvas-panel')
            .removeClass('webform-preview-tablet webform-preview-mobile')
            .addClass(device === 'desktop' ? '' : `webform-preview-${device}`);
    });

    function updateConfirmationOptions() {
        const type = String($('#webform-confirmation-type').val() || 'message');
        $('.webform-confirmation-option').attr('hidden', true);
        $(`.webform-confirmation-option[data-confirmation-option="${type}"]`).removeAttr('hidden');
    }

    $('#webform-confirmation-type').on('change', updateConfirmationOptions);
    updateConfirmationOptions();

    $('#webform-open-embed').on('click', function () {
        $('#webform-embed-panel').removeAttr('hidden').attr('aria-hidden', 'false');
    });
    $('.webform-editor-embed-close,.webform-editor-embed-backdrop').on('click', function () {
        $('#webform-embed-panel').attr({ hidden: true, 'aria-hidden': 'true' });
    });
    $('.webform-copy-embed').on('click', function () {
        const button = $(this);
        const text = $(`#${button.data('copy-target')}`).text();
        navigator.clipboard.writeText(text).then(function () {
            button.find('.webform-copy-label').text('Copied');
            window.setTimeout(function () { button.find('.webform-copy-label').text('Copy'); }, 1200);
        });
    });
    $('.webform-template-close,.webform-template-choice[href="#"]').on('click', function (event) {
        event.preventDefault();
        $('#webform-template-modal').remove();
    });

    $('.webform-property-tabs button').on('click', function () {
        activatePropertyPanel(String($(this).data('panel')));
    });

    let livePreviewStage = 0;
    function fieldPreviewStyle(field) {
        const style = field.style || {};
        if (!style.customized) return '';
        let css = '';
        if (Number(style.font_size)) css += 'font-size:' + Math.min(96, Math.max(10, Number(style.font_size))) + 'px;';
        if (['Arial,sans-serif','Georgia,serif','Verdana,sans-serif','monospace'].includes(style.font_family)) css += 'font-family:' + style.font_family + ';';
        if (['400','500','600','700','800'].includes(String(style.font_weight))) css += 'font-weight:' + style.font_weight + ';';
        if (style.font_style === 'italic') css += 'font-style:italic;';
        for (const [key, name] of Object.entries({label_color:'label',text_color:'text',background_color:'background'})) {
            if (/^#[0-9a-f]{3}([0-9a-f]{3})?$/i.test(style[key] || '')) css += '--local-'+name+':'+style[key]+';';
        }
        return css + '--local-radius:' + Math.min(40,Math.max(0,Number(style.radius)||0)) + 'px;';
    }
    function livePreviewField(field) {
        const width = {100:'100%',75:'74%',50:'49%',33:'32%',auto:'49%'}[(field.style || {}).width] || '100%';
        const label = field.hide_label ? '' : `<label>${escapeHtml(field.label)}${field.required ? ' <em>*</em>' : ''}</label>`;
        return `${field.row_start ? '<span class="webform-row-break"></span>' : ''}<div class="webform-live-preview-field webform-field-${escapeHtml(field.type)}" style="--live-field-width:${width};${fieldPreviewStyle(field)}">${label}${preview(field)}</div>`;
    }

    function renderLivePreview() {
        if (!schema.length) return;
        livePreviewStage = Math.max(0, Math.min(livePreviewStage, schema.length - 1));
        const stage = schema[livePreviewStage];
        const preset = $('#webform-style-preset').val() || 'modern';
        const palette = (WebformAdmin.stylePalettes || {})[preset] || {};
        const color = (id, key, fallback) => $(id).val() || palette[key] || fallback;
        const accent = color('#webform-accent-color','accent_color','#6c4bd4');
        const background = color('#webform-form-background','form_background','#ffffff');
        const text = color('#webform-text-color','text_color','#1d2327');
        const fieldBackground = color('#webform-field-background','field_background','#ffffff');
        const border = color('#webform-border-color','border_color','#dfe1e6');
        const submitText = $('#webform-submit-label').val() || 'Submit';
        const hideStages = $('#formorbit-hide-stage-name').is(':checked');
        const steps = schema.length > 1 && !hideStages ? `<ol class="webform-live-preview-steps">${schema.map((item, index) => `<li class="${index === livePreviewStage ? 'is-active' : index < livePreviewStage ? 'is-complete' : ''}">${escapeHtml(item.title)}</li>`).join('')}</ol>` : '';
        const actions = `<div class="webform-live-preview-actions">${livePreviewStage > 0 ? '<button type="button" data-live-preview-stage="prev">Back</button>' : ''}${livePreviewStage < schema.length - 1 ? '<button type="button" data-live-preview-stage="next">Continue</button>' : `<button type="button" class="is-submit">${escapeHtml(submitText)}</button>`}</div>`;
        $('#webform-live-preview-title').text($('#webform-name').val() || 'Untitled form');
        $('#webform-live-preview-form').attr('class', `webform-live-preview-form webform-style-${escapeHtml($('#webform-style-preset').val() || 'modern')}`).css({ '--live-accent': accent, '--live-background': background, '--live-text': text, '--live-field-background': fieldBackground, '--live-border': border, fontFamily: $('#webform-font-family').val() || 'inherit', fontSize: ($('#webform-base-font-size').val() || 16) + 'px', lineHeight: $('#webform-line-height').val() || 1.5, padding: ($('#webform-form-padding').val() || 0) + 'px', borderRadius: ($('#webform-form-radius').val() || 0) + 'px' }).html(`${steps}${hideStages ? '' : `<h3 style="font-size:${Number($('#formorbit-stage-font-size').val() || 24)}px">${escapeHtml(stage.title)}</h3>`}<div class="webform-live-preview-fields">${(stage.fields || []).map(livePreviewField).join('') || '<p>This stage has no fields.</p>'}</div>${actions}`);
        $('#webform-live-preview-form .webform-live-preview-field>label').css({fontSize: ($('#webform-label-font-size').val() || 14) + 'px', fontWeight: $('#webform-label-weight').val() || 600});
        $('#webform-live-preview-form .webform-live-preview-actions').css('justify-content', {left:'flex-start',center:'center',right:'flex-end'}[$('#webform-submit-alignment').val()] || 'center');
        $('#webform-live-preview-form .is-submit').css({background:$('#webform-submit-background').val() || accent,color:$('#webform-submit-text-color').val() || '#fff',fontSize:($('#webform-submit-font-size').val() || 16)+'px',fontWeight:$('#webform-submit-font-weight').val() || 600,borderRadius:($('#webform-button-radius').val() || 7)+'px',padding:($('#webform-submit-padding-y').val() || 12)+'px '+($('#webform-submit-padding-x').val() || 24)+'px',width:$('#webform-submit-alignment').val()==='full'?'100%':'auto'});
    }

    let previewTrigger = null;
    $(document).on('click', '.webform-live-preview-trigger', function (event) {
        event.preventDefault();
        previewTrigger = this;
        livePreviewStage = activeStage;
        renderLivePreview();
        $('#webform-live-preview-modal').appendTo('body').removeAttr('hidden').attr('aria-hidden', 'false');
        $('body').addClass('webform-live-preview-open');
        const close = document.querySelector('#webform-live-preview-modal .webform-live-preview-close');
        if (close) close.focus({preventScroll:true});
    });

    if (new URLSearchParams(window.location.search).get('preview') === '1') {
        window.setTimeout(function () {
            $('.webform-live-preview-trigger').first().trigger('click');
        }, 120);
    }
    $(document).on('click', '.webform-live-preview-close,.webform-live-preview-backdrop', function () {
        $('#webform-live-preview-modal').attr({ hidden: true, 'aria-hidden': 'true' });
        $('body').removeClass('webform-live-preview-open');
        if (previewTrigger) previewTrigger.focus({preventScroll:true});
    });
    $(document).on('keydown', '#webform-live-preview-modal', function (event) {
        if (event.key === 'Escape') $('.webform-live-preview-close').first().trigger('click');
    });
    $(document).on('click', '[data-live-preview-stage]', function () {
        livePreviewStage += $(this).data('live-preview-stage') === 'next' ? 1 : -1;
        renderLivePreview();
    });
    $(document).on('click', '[data-live-preview-device]', function () {
        $('[data-live-preview-device]').removeClass('is-active');
        $(this).addClass('is-active');
        $('.webform-live-preview-viewport').attr('class', `webform-live-preview-viewport is-${$(this).data('live-preview-device')}`);
    });
    $(document).on('change', '#webform-style-preset', function () {
        const palette = (WebformAdmin.stylePalettes || {})[$(this).val()];
        if (!palette) return;
        const controls = { accent_color: '#webform-accent-color', button_text_color: '#webform-button-text-color', text_color: '#webform-text-color', form_background: '#webform-form-background', field_background: '#webform-field-background', border_color: '#webform-border-color' };
        Object.entries(controls).forEach(([key, selector]) => { if (palette[key] && $(selector).length) $(selector).val(palette[key]); });
        $('#webform-submit-background,#webform-submit-hover-background').val(palette.accent_color);
        $('#webform-submit-text-color').val(palette.button_text_color);
        const preset = $(this).val();
        const square = ['minimal','classic','orbit-paper','orbit-terminal','orbit-editorial','orbit-brutalist'].includes(preset);
        const serif = ['elegant','classic','earthy','luxury','orbit-paper','orbit-editorial'].includes(preset);
        const defaults = {'font-family': serif ? 'Georgia,serif' : (preset === 'orbit-terminal' ? '"Courier New",monospace' : 'inherit'), 'base-font-size':16, 'label-font-size':14, 'heading-font-size':26, 'line-height':1.5, 'label-weight':600, 'form-padding':24, 'form-radius':square ? 0 : 16, 'field-radius':square ? 0 : 7, 'button-radius':square ? 0 : 7, 'control-padding':12, 'field-spacing':20};
        Object.entries(defaults).forEach(([key,value]) => $('#webform-' + key).val(value));
        $('input[type=color]').trigger('input');
        renderLivePreview();
    });
    $(document).on('input change', '#webform-name,#webform-style-preset,#webform-accent-color,#webform-button-text-color,#webform-submit-label,[id^="webform-"]', function () {
        if (!$('#webform-live-preview-modal').prop('hidden')) renderLivePreview();
    });

    $('#webform-save').on('click', function () {
        if (window.tinyMCE) window.tinyMCE.triggerSave();
        const unsupported = schema.flatMap((stage) => stage.fields || []).filter((field) => !fieldLabels[field.type] && !fieldExtensions[field.type]);
        if (unsupported.length) {
            window.alert('This form contains unsupported fields. Activate the plugin that created them, or convert/remove each highlighted field before saving.');
            return;
        }
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
            if (!response.success) { window.alert((response.data && response.data.message) || 'The form could not be saved. Please try again.'); return; }
            $('#webform-id').val(response.data.id);
            $('#webform-editor-shortcode').text(response.data.shortcode);
            $('#webform-editor-php').text('<?php echo do_shortcode( \'' + response.data.shortcode + '\' ); ?>');
            $('#webform-open-embed').removeAttr('hidden');
            dirty = false;
            if (window.history.replaceState) window.history.replaceState({}, '', WebformAdmin.formsUrl.replace('page=formorbit', `page=formorbit-builder&form_id=${response.data.id}`));
        }).fail(function () { window.alert('The form could not be saved. Check your connection and try again. Your edits are still here.'); }).always(function () { button.prop('disabled', false); });
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
        setActiveStage(index) { activeStage = index; },
        registerFieldTypes(types) {
            Object.entries(types || {}).forEach(([type, definition]) => {
                fieldLabels[type] = definition.label || type;
                fieldExtensions[type] = definition;
            });
            render();
        },
        enableLayoutTools() {
            layoutToolsEnabled = true;
            render();
        },
        registerFieldIcons(icons) {
            Object.assign(registeredFieldIcons, icons || {});
            render();
        },
        registerFieldDropHandler(handler) {
            fieldDropHandler = typeof handler === 'function' ? handler : null;
        },
        getSchema() {
            return schema;
        },
        getActiveStage() {
            return activeStage;
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
