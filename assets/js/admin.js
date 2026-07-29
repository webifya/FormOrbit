(function ($) {
    'use strict';

    const defaults = { text: 'Text', email: 'Email', textarea: 'Long text', select: 'Dropdown', radio: 'Radio', checkbox: 'Checkbox', number: 'Number', date: 'Date', time: 'Time', phone: 'Phone', url: 'Website', file: 'File upload', consent: 'I agree to the terms', poll: 'Poll question', quiz: 'Quiz question', rating: 'Rating', slider: 'Slider', hidden: 'Hidden field', html: 'HTML content', captcha: 'Security check', calculation: 'Calculation', signature: 'Signature', field_group: 'Field group', address: 'Address', repeater: 'Repeater', appointment: 'Appointment', nps: 'NPS score', currency: 'Currency', product: 'Product selector', price: 'Price', heading: 'Heading' };
    const fieldIcons = { '': ['No icon', 'dashicons-minus'], email: ['Email', 'dashicons-email-alt'], user: ['Person', 'dashicons-admin-users'], phone: ['Phone', 'dashicons-phone'], location: ['Location', 'dashicons-location-alt'], calendar: ['Calendar', 'dashicons-calendar-alt'], clock: ['Clock', 'dashicons-clock'], link: ['Link', 'dashicons-admin-links'], cart: ['Cart', 'dashicons-cart'], money: ['Payment', 'dashicons-money-alt'], heart: ['Heart', 'dashicons-heart'], star: ['Star', 'dashicons-star-filled'], shield: ['Security', 'dashicons-shield'], clipboard: ['Clipboard', 'dashicons-clipboard'], business: ['Business', 'dashicons-building'], edit: ['Signature', 'dashicons-edit'] };
    let schema = [];
    let activeStage = 0;
    let selectedId = null;
    let dirty = false;
    const proFieldTypes = WebformAdmin.proFieldTypes || [];

    function isLockedProField(field) {
        return !WebformAdmin.proActive && proFieldTypes.includes(field.type);
    }

    function previewWidth(value) {
        return ({ 'auto': 'auto', '100': '100%', '75': '74%', '50': '49%', '33': '32%' })[String(value || '100')] || '100%';
    }

    function uid(prefix) {
        return prefix + '_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
    }

    function load() {
        try { schema = JSON.parse($('#webform-schema').val() || '[]'); } catch (e) { schema = []; }
        if (!Array.isArray(schema) || !schema.length) schema = [{ id: uid('stage'), title: 'Stage 1', fields: [] }];
        render();
        renderFreeProCatalog();
    }

    function renderFreeProCatalog() {
        if (WebformAdmin.proInstalled || WebformAdmin.proStyling || $('.webform-free-pro-catalog').length) return;
        const fields = ['Calculation', 'Field group', 'E-signature', 'Address', 'Repeater', 'Appointment', 'NPS score', 'Currency', 'Product selector', 'Price', 'Advanced upload'];
        const integrations = {
            'Email marketing & CRM': ['Mailchimp', 'Brevo', 'ActiveCampaign', 'Kit', 'GetResponse', 'LeadConnector / GoHighLevel'],
            'Payments': ['Stripe', 'PayPal', 'Square', 'Bank transfer'],
            'Automation & documents': ['Zapier', 'Webhooks', 'PDF notifications']
        };
        $('.webform-pro-field-preview-only .webform-pro-field-list').html(fields.map(name => `<div><span class="dashicons dashicons-lock"></span>${name}</div>`).join(''));
        const catalog = `<h2>Integrations</h2>
            <p class="description">Connect form submissions to email marketing, payments, automation, and document tools with Webform Pro.</p>
            <div class="webform-free-pro-catalog">
                <span class="webform-pro-badge">WEBFORM PRO</span>
                ${Object.entries(integrations).map(([category, names]) => `<h3>${category}</h3><div class="webform-pro-catalog-grid">${names.map(name => `<span><i class="dashicons dashicons-lock"></i>${name}</span>`).join('')}</div>`).join('')}
                <a class="button button-primary webform-pro-catalog-button" href="https://www.webninjallc.com/product/webform-pro/?utm_source=webform-free&amp;utm_medium=builder&amp;utm_campaign=integrations" target="_blank" rel="noopener">Unlock integrations</a>
            </div>`;
        $('.webform-property-panel[data-panel="integrations"]').html(catalog);
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
            signature: '<div class="webform-preview-signature"><span>Sign here</span><svg viewBox="0 0 240 48" aria-hidden="true"><path d="M8 38c30-4 31-30 43-23 10 6-8 22-2 24 11 4 21-25 29-20 6 4-7 18 0 20 9 2 13-13 20-11 5 2 3 9 13 9 16 0 24-8 39-5"/></svg><small>Clear signature</small></div>',
            address: '<div class="webform-preview-address"><i>Street address</i><i>City</i><i>State / Province</i><i>Postal code</i><i>Country</i></div>',
            repeater: '<div class="webform-preview-repeater"><div><i></i><button type="button">×</button></div><div><i></i><button type="button">×</button></div><small>＋ Add another</small></div>',
            appointment: '<div class="webform-preview-control webform-preview-with-icon"><span>Select date and time</span><span class="dashicons dashicons-calendar-alt"></span></div>',
            nps: '<div class="webform-preview-nps"><div>' + Array.from({length: 11}, (_, index) => `<i>${index}</i>`).join('') + '</div><small><span>Not likely</span><span>Very likely</span></small></div>',
            currency: `<div class="webform-preview-control webform-preview-currency"><b>${escapeHtml(field.currency_symbol || '$')}</b><span>${placeholder || '0.00'}</span></div>`,
            product: `<div class="webform-preview-product">${(field.options || ['Standard|19.99','Premium|39.99']).map(option => { const parts = option.split('|'); return `<span><i></i><strong>${escapeHtml(parts[0])}</strong><em>${escapeHtml(parts[1] || '0.00')}</em></span>`; }).join('')}</div>`,
            price: `<div class="webform-preview-price"><span>${escapeHtml(field.label || 'Price')}</span><strong>${escapeHtml(field.currency_code || 'USD')} ${Number(field.price_amount || 0).toFixed(2)}</strong></div>`
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
            items: '.webform-field-card:not(.is-pro-locked)',
            handle: '.webform-drag',
            placeholder: 'webform-sort-placeholder',
            tolerance: 'pointer',
            forcePlaceholderSize: true,
            helper: 'clone',
            distance: 4,
            start: function (event, ui) {
                ui.placeholder.css({
                    flex: ui.item.css('flex'),
                    maxWidth: ui.item.css('max-width'),
                    minWidth: ui.item.css('min-width'),
                    width: ui.item.outerWidth(),
                    height: ui.item.outerHeight()
                });
                $(this).addClass('is-sorting');
            },
            stop: function () {
                $(this).removeClass('is-sorting');
            },
            update: function (event, ui) {
                const order = $(this).children('.webform-field-card').map(function () { return $(this).data('id'); }).get();
                schema[activeStage].fields.sort((a, b) => order.indexOf(a.id) - order.indexOf(b.id));
                const moved = schema[activeStage].fields.find(field => field.id === ui.item.data('id'));
                if (moved && WebformAdmin.proActive && !isLockedProField(moved)) {
                    moved.style = moved.style || {};
                    moved.style.width = 'auto';
                    const position = schema[activeStage].fields.indexOf(moved);
                    const left = ui.item.offset().left - $(this).offset().left;
                    moved.row_start = position > 0 && left <= Math.max(56, $(this).innerWidth() * 0.14);
                }
                dirty = true;
                render();
            }
        });
        renderSettings();
        refreshUserEmailFields();
    }

    function refreshUserEmailFields() {
        const select = $('#webform-user-notification-email-field');
        if (!select.length) return;
        const saved = String(select.val() || select.data('saved') || '');
        const emailFields = schema.flatMap(stage => stage.fields || []).filter(field => field.type === 'email');
        select.html('<option value="">First email field automatically</option>' + emailFields.map(field => `<option value="${escapeHtml(field.id)}">${escapeHtml(field.label)} (${escapeHtml(field.id)})</option>`).join(''));
        if (emailFields.some(field => field.id === saved)) select.val(saved);
        select.data('saved', select.val() || saved);
    }

    function fieldCard(field) {
        const typeName = (defaults[field.type] || field.type || 'Field').replace(' question', '');
        const locked = isLockedProField(field);
        const style = field.style || {};
        const cardStyle = `--preview-width:${previewWidth(style.width)};--preview-label:${escapeHtml(style.label_color || '#1d2327')};--preview-bg:${escapeHtml(style.background_color || '#ffffff')};--preview-text:${escapeHtml(style.text_color || '#3c434a')};--preview-radius:${Number(style.radius ?? 7)}px`;
        const widthMode = String(style.width || '100');
        const widthText = widthMode === 'auto' ? 'Fit' : `${widthMode}%`;
        const widthControls = WebformAdmin.proActive && !locked && field.id === selectedId ? `<div class="webform-card-widths" aria-label="Field layout"><button type="button" data-card-resize="-1" title="Make field narrower" aria-label="Make field narrower">−</button><button type="button" data-card-width="auto" class="${widthMode === 'auto' ? 'is-active' : ''}" title="Fit available row space" aria-label="Fit available row space"><span class="dashicons dashicons-editor-expand"></span>${widthText}</button><button type="button" data-card-resize="1" title="Make field wider" aria-label="Make field wider">＋</button><button type="button" data-card-row="1" class="${field.row_start ? 'is-active' : ''}" title="Start a new row" aria-label="Start a new row"><span class="dashicons dashicons-arrow-down-alt"></span></button></div>` : '';
        const selectedIcon = fieldIcons[field.icon] || fieldIcons[''];
        const iconPreview = WebformAdmin.proActive && field.icon ? `<span class="dashicons ${selectedIcon[1]} webform-builder-label-icon" aria-hidden="true"></span>` : '';
        return `${field.row_start ? '<span class="webform-row-break" aria-hidden="true"></span>' : ''}<div class="webform-field-card webform-field-card-${escapeHtml(field.type)} ${widthMode === 'auto' ? 'is-width-auto' : ''} ${locked ? 'is-pro-locked' : ''} ${field.id === selectedId ? 'is-selected' : ''}" data-id="${field.id}" style="${cardStyle}">
            <span class="dashicons ${locked ? 'dashicons-lock' : 'dashicons-menu'} webform-drag"></span>
            <div class="webform-field-preview"><strong>${iconPreview}${escapeHtml(field.label)}${field.required ? ' <em>*</em>' : ''}</strong>
            ${fieldPreview(field)}</div>
            ${widthControls}
            <span class="webform-type">${locked ? 'PRO LOCKED' : escapeHtml(typeName)}</span>
            ${locked ? '' : '<button class="webform-remove-field" title="Remove">×</button>'}
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
        if (isLockedProField(field)) {
            $('#webform-field-settings').html('<div class="webform-locked-field-message"><span class="dashicons dashicons-lock"></span><strong>Pro field unavailable</strong><p>This field and its settings are preserved. Activate Webform Pro with a valid license to edit or display it.</p></div>');
            return;
        }
        const choices = ['select', 'radio', 'checkbox', 'poll', 'quiz', 'product'].includes(field.type);
        const candidates = schema.flatMap(stage => stage.fields).filter(item => item.id !== field.id && item.type !== 'heading' && item.type !== 'file');
        const condition = field.condition || { enabled: false, field_id: '', operator: 'equals', value: '' };
        $('#webform-field-settings').html(`
            <p class="description">Field ID: <code>${escapeHtml(field.id)}</code></p>
            <label>Label<input type="text" data-prop="label" value="${escapeHtml(field.label)}"></label>
            ${WebformAdmin.proActive ? `<label>Field icon <small>Shown beside the label</small><select data-prop="icon">${Object.entries(fieldIcons).map(([value, icon]) => `<option value="${value}" ${String(field.icon || '') === value ? 'selected' : ''}>${icon[0]}</option>`).join('')}</select></label>` : ''}
            ${!['heading','consent','file','hidden','html','captcha','rating','slider'].includes(field.type) && !choices ? `<label>Placeholder<input type="text" data-prop="placeholder" value="${escapeHtml(field.placeholder || '')}"></label>` : ''}
            ${choices ? `<label>Options <small>One per line</small><textarea rows="6" data-prop="options">${escapeHtml((field.options || []).join('\n'))}</textarea></label>` : ''}
            ${field.type === 'quiz' ? `<label>Correct answer<select data-prop="correct_answer"><option value="">Choose answer</option>${(field.options || []).map(option => `<option value="${escapeHtml(option)}" ${field.correct_answer === option ? 'selected' : ''}>${escapeHtml(option)}</option>`).join('')}</select></label><label>Points<input type="number" min="1" max="100" data-prop="points" value="${Number(field.points || 1)}"></label>` : ''}
            ${field.type === 'file' ? `<label>Allowed extensions<input type="text" data-prop="allowed_extensions" value="${escapeHtml(field.allowed_extensions || 'jpg,jpeg,png,pdf,doc,docx')}"></label><label>Maximum size (MB)<input type="number" min="1" max="20" data-prop="max_size" value="${Number(field.max_size || 5)}"></label>` : ''}
            ${field.type === 'hidden' ? `<label>Default value<input type="text" data-prop="default_value" value="${escapeHtml(field.default_value || '')}"></label>` : ''}
            ${field.type === 'html' ? `<label>Safe HTML content<textarea rows="8" data-prop="html">${escapeHtml(field.html || '')}</textarea></label>` : ''}
            ${field.type === 'slider' ? `<label>Minimum<input type="number" data-prop="min" value="${Number(field.min ?? 0)}"></label><label>Maximum<input type="number" data-prop="max" value="${Number(field.max ?? 100)}"></label><label>Step<input type="number" min="0.01" step="0.01" data-prop="step" value="${Number(field.step || 1)}"></label>` : ''}
            ${field.type === 'calculation' ? `<label>Formula <small>Use field IDs in braces, for example {price} * {quantity}</small><input type="text" data-prop="formula" value="${escapeHtml(field.formula || '')}"></label><label>Decimal places<input type="number" min="0" max="6" data-prop="decimal_places" value="${Number(field.decimal_places ?? 2)}"></label>` : ''}
            ${field.type === 'field_group' ? `<label>Fields to group<input type="number" min="1" max="6" data-prop="group_count" value="${Number(field.group_count || 2)}"></label><label>Columns<input type="number" min="1" max="4" data-prop="group_columns" value="${Number(field.group_columns || 2)}"></label>` : ''}
            ${field.type === 'repeater' ? `<label>Minimum rows<input type="number" min="1" max="20" data-prop="repeater_min" value="${Number(field.repeater_min || 1)}"></label><label>Maximum rows<input type="number" min="1" max="50" data-prop="repeater_max" value="${Number(field.repeater_max || 10)}"></label><label>Add button text<input type="text" data-prop="repeater_button" value="${escapeHtml(field.repeater_button || 'Add another')}"></label>` : ''}
            ${field.type === 'appointment' ? `<label>Earliest date and time<input type="datetime-local" data-prop="min_date" value="${escapeHtml(field.min_date || '')}"></label><label>Latest date and time<input type="datetime-local" data-prop="max_date" value="${escapeHtml(field.max_date || '')}"></label>` : ''}
            ${field.type === 'currency' ? `<label>Currency symbol<input type="text" maxlength="5" data-prop="currency_symbol" value="${escapeHtml(field.currency_symbol || '$')}"></label><label>Minimum<input type="number" step="0.01" data-prop="min" value="${Number(field.min ?? 0)}"></label><label>Maximum<input type="number" step="0.01" data-prop="max" value="${Number(field.max ?? 999999999)}"></label>` : ''}
            ${field.type === 'product' ? `<p class="description">Enter each product as <code>Product name|19.99</code>.</p>` : ''}
            ${field.type === 'price' ? `<label>Amount<input type="number" min="0" step="0.01" data-prop="price_amount" value="${Number(field.price_amount || 0)}"></label><label>Currency<select data-prop="currency_code">${['USD','EUR','GBP','CAD','AUD','BDT'].map(code => `<option ${field.currency_code === code ? 'selected' : ''}>${code}</option>`).join('')}</select></label>` : ''}
            ${!['heading','hidden','html'].includes(field.type) ? `<label class="webform-check"><input type="checkbox" data-prop="required" ${field.required ? 'checked' : ''}> Required field</label>` : ''}
            ${field.type !== 'heading' && candidates.length ? `<hr><h3>Conditional display</h3>
                <label class="webform-check"><input type="checkbox" data-condition="enabled" ${condition.enabled ? 'checked' : ''}> Show this field conditionally</label>
                <div class="webform-condition-settings ${condition.enabled ? '' : 'is-hidden'}">
                    <label>When field<select data-condition="field_id"><option value="">Choose field</option>${candidates.map(item => `<option value="${item.id}" ${condition.field_id === item.id ? 'selected' : ''}>${escapeHtml(item.label)}</option>`).join('')}</select></label>
                    <label>Operator<select data-condition="operator">${[['equals','Equals'],['not_equals','Does not equal'],['contains','Contains'],['starts_with','Starts with'],['ends_with','Ends with'],['greater_than','Greater than'],['less_than','Less than'],['not_empty','Is not empty'],['empty','Is empty']].map(item => `<option value="${item[0]}" ${condition.operator === item[0] ? 'selected' : ''}>${item[1]}</option>`).join('')}</select></label>
                    <label>Value<input type="text" data-condition="value" value="${escapeHtml(condition.value || '')}"></label>
                </div>` : ''}
            <hr><h3>Field appearance ${WebformAdmin.proStyling ? '<span class="webform-pro-badge">PRO</span>' : ''}</h3>
            ${WebformAdmin.proStyling ? `<div class="webform-field-style-controls">
                <label>Field width<select data-field-style="width"><option value="auto" ${field.style?.width === 'auto' ? 'selected' : ''}>Auto — share row space</option><option value="100" ${(field.style?.width || '100') === '100' ? 'selected' : ''}>Full width</option><option value="75" ${field.style?.width === '75' ? 'selected' : ''}>75%</option><option value="50" ${field.style?.width === '50' ? 'selected' : ''}>Half width</option><option value="33" ${field.style?.width === '33' ? 'selected' : ''}>One third</option></select></label>
                <div class="webform-style-color-grid"><label>Label<input type="color" data-field-style="label_color" value="${escapeHtml(field.style?.label_color || '#1d2327')}"></label>${field.type !== 'heading' ? `<label>Field<input type="color" data-field-style="background_color" value="${escapeHtml(field.style?.background_color || '#ffffff')}"></label><label>Text<input type="color" data-field-style="text_color" value="${escapeHtml(field.style?.text_color || '#1d2327')}"></label>` : ''}</div>
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
        const choices = ['select', 'radio', 'checkbox', 'poll', 'quiz', 'product'].includes(type);
        const field = { id: uid('field'), type, label: defaults[type] || 'Field', icon: '', placeholder: '', required: ['consent','captcha','signature'].includes(type), options: choices ? (type === 'product' ? ['Standard|19.99', 'Premium|39.99'] : ['Option 1', 'Option 2']) : [], allowed_extensions: 'jpg,jpeg,png,pdf,doc,docx', max_size: 5, correct_answer: '', points: 1, default_value: '', html: '<p>Add your content here.</p>', min: 0, max: type === 'currency' ? 999999999 : 100, step: 1, formula: '', decimal_places: 2, group_count: 2, group_columns: 2, repeater_min: 1, repeater_max: 10, repeater_button: 'Add another', currency_symbol: '$', price_amount: 0, currency_code: 'USD', min_date: '', max_date: '', style: { width: WebformAdmin.proActive ? 'auto' : '100' }, condition: { enabled: false, field_id: '', operator: 'equals', value: '' } };
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
    function updateConfirmationOptions() {
        const type = $('#webform-confirmation-type').val() || 'message';
        $('.webform-confirmation-option').attr('hidden', true).filter('[data-confirmation-option="' + type + '"]').removeAttr('hidden');
    }
    $(document).on('change', '#webform-confirmation-type', updateConfirmationOptions);
    updateConfirmationOptions();
    $(document).on('click', '.webform-field-card', function (event) {
        if ($(event.target).closest('.webform-remove-field,.webform-card-widths').length) return;
        selectedId = $(this).data('id');
        render();
        $('.webform-property-tabs button[data-panel="field"]').trigger('click');
    });
    $(document).on('click', '.webform-card-widths button', function (event) {
        event.preventDefault();
        event.stopPropagation();
        const id = $(this).closest('.webform-field-card').data('id');
        const field = (schema[activeStage].fields || []).find(item => item.id === id);
        if (!field || !WebformAdmin.proActive) return;
        field.style = field.style || {};
        if ($(this).data('card-row')) {
            const index = schema[activeStage].fields.indexOf(field);
            field.row_start = index > 0 && !field.row_start;
            selectedId = id;
            dirty = true;
            render();
            return;
        }
        const direction = Number($(this).data('card-resize') || 0);
        if (direction) {
            const widths = ['33', '50', '75', '100'];
            const current = widths.indexOf(String(field.style.width || 'auto'));
            const next = current < 0 ? (direction > 0 ? 1 : 0) : Math.max(0, Math.min(widths.length - 1, current + direction));
            field.style.width = widths[next];
        } else {
            field.style.width = String($(this).data('card-width'));
        }
        selectedId = id;
        dirty = true;
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
    $(document).on('input change', '#webform-field-settings [data-field-style]', function () {
        const field = selectedField();
        if (!field || !WebformAdmin.proStyling) return;
        field.style = field.style || {};
        field.style[$(this).data('field-style')] = $(this).val();
        field.style.customized = true;
        dirty = true;
        const card = $(`.webform-field-card[data-id="${field.id}"]`)[0];
        if (!card) return;
        if ($(this).data('field-style') === 'width') card.style.setProperty('--preview-width', previewWidth($(this).val()));
        if ($(this).data('field-style') === 'label_color') card.style.setProperty('--preview-label', $(this).val());
        if ($(this).data('field-style') === 'background_color') card.style.setProperty('--preview-bg', $(this).val());
        if ($(this).data('field-style') === 'text_color') card.style.setProperty('--preview-text', $(this).val());
        if ($(this).data('field-style') === 'radius') card.style.setProperty('--preview-radius', `${Number($(this).val() || 0)}px`);
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
    const presetPalettes = {
        modern:{accent_color:'#6c4bd4',button_text_color:'#ffffff',text_color:'#1d2327',form_background:'#ffffff',field_background:'#ffffff',border_color:'#dfe1e6'}, minimal:{accent_color:'#222222',button_text_color:'#ffffff',text_color:'#242424',form_background:'#ffffff',field_background:'#ffffff',border_color:'#b8b8b8'}, rounded:{accent_color:'#6750c8',button_text_color:'#ffffff',text_color:'#282334',form_background:'#fbfaff',field_background:'#ffffff',border_color:'#d9d2ed'},
        elegant:{accent_color:'#8a6438',button_text_color:'#ffffff',text_color:'#302820',form_background:'#fffdf9',field_background:'#ffffff',border_color:'#dfd3c2'}, glass:{accent_color:'#6651c9',button_text_color:'#ffffff',text_color:'#25203a',form_background:'#f4f0ff',field_background:'#ffffff',border_color:'#d8cfef'}, dark:{accent_color:'#8b72ef',button_text_color:'#ffffff',text_color:'#f6f6f8',form_background:'#20202a',field_background:'#2c2c38',border_color:'#4a4a59'},
        corporate:{accent_color:'#183b63',button_text_color:'#ffffff',text_color:'#1d3249',form_background:'#f8fafc',field_background:'#ffffff',border_color:'#b9c8d8'}, editorial:{accent_color:'#7d3728',button_text_color:'#ffffff',text_color:'#29231e',form_background:'#fffdf8',field_background:'#fffdf8',border_color:'#8f8175'}, pastel:{accent_color:'#a84f83',button_text_color:'#ffffff',text_color:'#493746',form_background:'#fff7fb',field_background:'#ffffff',border_color:'#e8cadb'},
        contrast:{accent_color:'#000000',button_text_color:'#ffffff',text_color:'#000000',form_background:'#ffffff',field_background:'#ffffff',border_color:'#111111'}, compact:{accent_color:'#3858a6',button_text_color:'#ffffff',text_color:'#252a34',form_background:'#ffffff',field_background:'#ffffff',border_color:'#d5d9e0'}, spacious:{accent_color:'#4968b8',button_text_color:'#ffffff',text_color:'#293240',form_background:'#ffffff',field_background:'#ffffff',border_color:'#d8dde6'},
        neon:{accent_color:'#5fffe0',button_text_color:'#101018',text_color:'#f6f7ff',form_background:'#12121a',field_background:'#1c1c27',border_color:'#5fffe0'}, earthy:{accent_color:'#70613f',button_text_color:'#ffffff',text_color:'#3f392d',form_background:'#f4efe5',field_background:'#fffaf0',border_color:'#b9a788'}, luxury:{accent_color:'#c3a35c',button_text_color:'#171512',text_color:'#f7eed8',form_background:'#171512',field_background:'#211f1b',border_color:'#8d743e'}, playful:{accent_color:'#e14d72',button_text_color:'#ffffff',text_color:'#342e63',form_background:'#fff9e8',field_background:'#ffffff',border_color:'#342e63'}
    };
    function applyPresetPalette(key) {
        const palette = presetPalettes[key];
        if (!palette) return;
        Object.entries(palette).forEach(([name, value]) => { if (themeSelectors[name] && $(themeSelectors[name]).length) $(themeSelectors[name]).val(value); });
    }
    function updatePresetPreview() {
        const select = $('#webform-style-preset'), key = select.val() || 'modern', palette = presetPalettes[key] || presetPalettes.modern;
        $('.webform-preset-preview-form').attr('class', `webform-public webform-preset-preview-form webform-style-${key}`).css({'--wf-accent':palette.accent_color,'--wf-button-text':palette.button_text_color,'--wf-text':palette.text_color,'--wf-form-bg':palette.form_background,'--wf-field-bg':palette.field_background,'--wf-border':palette.border_color});
        $('#webform-preset-preview-title').text(select.find('option:selected').text().replace('🔒 ', ''));
    }
    $(document).on('change', '#webform-style-preset', function () { applyPresetPalette($(this).val()); updatePresetPreview(); dirty = true; });
    $(document).on('click', '.webform-preview-preset', function () { updatePresetPreview(); $('#webform-preset-preview-modal').removeAttr('hidden'); });
    $(document).on('click', '.webform-preset-preview-close,.webform-preset-preview-backdrop', function () { $('#webform-preset-preview-modal').attr('hidden', true); });
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
        if (window.tinyMCE && tinyMCE.get('webform-success-message')) tinyMCE.get('webform-success-message').save();
        if (window.tinyMCE && tinyMCE.get('webform-user-notification-body')) tinyMCE.get('webform-user-notification-body').save();
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
                confirmation_type: $('#webform-confirmation-type').val(),
                notification_email: $('#webform-notification-email').val(),
                submit_label: $('#webform-submit-label').val(),
                redirect_url: $('#webform-redirect-url').val(),
                webhook_url: $('#webform-webhook-url').val(),
                require_login: $('#webform-require-login').is(':checked'),
                submission_limit: $('#webform-submission-limit').val(),
                closed_message: $('#webform-closed-message').val(),
                open_at: $('#webform-open-at').val(),
                close_at: $('#webform-close-at').val(),
                per_user_limit: $('#webform-per-user-limit').val(),
                allowed_roles: $('[name="webform_allowed_roles"]:checked').map(function () { return $(this).val(); }).get(),
                hide_after_submit: $('#webform-hide-after-submit').is(':checked'),
                save_progress_enabled: $('#webform-save-progress-enabled').is(':checked'),
                save_progress_days: $('#webform-save-progress-days').val(),
                save_progress_label: $('#webform-save-progress-label').val(),
                user_notification_enabled: $('#webform-user-notification-enabled').is(':checked'),
                user_notification_email_field: $('#webform-user-notification-email-field').val(),
                user_notification_subject: $('#webform-user-notification-subject').val(),
                user_notification_body: $('#webform-user-notification-body').val(),
                user_notification_pdf: $('#webform-user-notification-pdf').is(':checked'),
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
                custom_css: $('#webform-custom-css').val(),
                pdf_notifications: $('#webform-pdf-notifications').is(':checked'),
                mailchimp_list: $('#webform-mailchimp-list').val(),
                brevo_list: $('#webform-brevo-list').val(),
                activecampaign_list: $('#webform-activecampaign-list').val(),
                leadconnector_enabled: $('#webform-leadconnector-enabled').is(':checked'),
                payment_provider: $('#webform-payment-provider').val()
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
    $(document).on('change', '#webform-require-login', function () {
        const enabled = $(this).is(':checked');
        $('#webform-role-controls').toggleClass('is-disabled', !enabled).find('input[type="checkbox"]').prop('disabled', !enabled);
    });
    $(document).on('change', '#webform-user-notification-enabled', function () {
        $('#webform-user-notification-options').toggleClass('is-disabled', !$(this).is(':checked'));
    });
    $(document).on('change', '#webform-save-progress-enabled', function () {
        $('#webform-save-progress-options').toggleClass('is-disabled', !$(this).is(':checked')).find('input').prop('disabled', !$(this).is(':checked'));
    });
    $(document).on('input change', '.webform-pro-style-controls input,.webform-pro-style-controls select,.webform-pro-style-controls textarea', function () { dirty = true; });
    $(document).on('input change', '.webform-confirmation-section input,.webform-confirmation-section select,.webform-confirmation-section textarea', function () { dirty = true; });
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
    let requestedPanel = new URLSearchParams(window.location.search).get('panel');
    if (requestedPanel === 'pdf') requestedPanel = 'confirmation';
    if (requestedPanel && ['field', 'confirmation', 'integrations', 'access', 'style'].includes(requestedPanel)) {
        $(`.webform-property-tabs button[data-panel="${requestedPanel}"]`).trigger('click');
    }
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
