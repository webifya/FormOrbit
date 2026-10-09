(function () {
    'use strict';

    const proFieldLabels = {
        calculation: 'Calculation', field_group: 'Field group', signature: 'E-signature',
        rich_text: 'Rich text / agreement', divider: 'Divider', address: 'Address', repeater: 'Repeater',
        appointment: 'Appointment', nps: 'NPS score', currency: 'Currency', product: 'Product selector',
        price: 'Price', quantity: 'Quantity', product_option: 'Product option', shipping: 'Shipping',
        donation: 'Donation', total: 'Order total', post_title: 'Post title', post_body: 'Post body',
        post_excerpt: 'Post excerpt', post_tags: 'Post tags', post_custom: 'Post custom field'
    };
    const fieldIcons = {
        '': ['No icon', 'dashicons-minus'], user: ['Person', 'dashicons-admin-users'], groups: ['People', 'dashicons-groups'],
        email: ['Email', 'dashicons-email-alt'], phone: ['Phone', 'dashicons-phone'], location: ['Location', 'dashicons-location-alt'],
        home: ['Home', 'dashicons-admin-home'], business: ['Business', 'dashicons-building'], calendar: ['Calendar', 'dashicons-calendar-alt'],
        clock: ['Clock', 'dashicons-clock'], link: ['Link', 'dashicons-admin-links'], cart: ['Cart', 'dashicons-cart'],
        money: ['Payment', 'dashicons-money-alt'], portfolio: ['Portfolio', 'dashicons-portfolio'], clipboard: ['Clipboard', 'dashicons-clipboard'],
        document: ['Document', 'dashicons-media-document'], book: ['Book', 'dashicons-book'], edit: ['Signature', 'dashicons-edit'],
        upload: ['Upload', 'dashicons-upload'], download: ['Download', 'dashicons-download'], image: ['Image', 'dashicons-format-image'],
        camera: ['Camera', 'dashicons-camera'], video: ['Video', 'dashicons-video-alt3'], audio: ['Audio', 'dashicons-format-audio'],
        chart: ['Chart', 'dashicons-chart-bar'], trend: ['Trend', 'dashicons-chart-line'], ticket: ['Ticket', 'dashicons-tickets-alt'],
        heart: ['Heart', 'dashicons-heart'], star: ['Star', 'dashicons-star-filled'], shield: ['Security', 'dashicons-shield'],
        info: ['Information', 'dashicons-info-outline'], warning: ['Warning', 'dashicons-warning'], check: ['Approved', 'dashicons-yes-alt'],
        website: ['Website', 'dashicons-admin-site-alt3'], network: ['Network', 'dashicons-admin-multisite'], feedback: ['Message', 'dashicons-feedback'],
        food: ['Food', 'dashicons-food'], pets: ['Pets', 'dashicons-pets'], travel: ['Travel', 'dashicons-airplane'],
        contact: ['Contact card', 'dashicons-id-alt'], store: ['Store', 'dashicons-store'], tag: ['Tag', 'dashicons-tag'],
        calculator: ['Calculator', 'dashicons-calculator'], analytics: ['Analytics', 'dashicons-analytics'], pie: ['Pie chart', 'dashicons-chart-pie'],
        announcement: ['Announcement', 'dashicons-megaphone'], chat: ['Chat', 'dashicons-format-chat'], quote: ['Quote', 'dashicons-editor-quote'],
        help: ['Help', 'dashicons-editor-help'], idea: ['Idea', 'dashicons-lightbulb'], attachment: ['Attachment', 'dashicons-paperclip'],
        folder: ['Folder', 'dashicons-open-folder'], database: ['Database', 'dashicons-database'], cloud: ['Cloud', 'dashicons-cloud'],
        desktop: ['Desktop', 'dashicons-desktop'], laptop: ['Laptop', 'dashicons-laptop'], tablet: ['Tablet', 'dashicons-tablet'],
        mobile: ['Mobile', 'dashicons-smartphone'], settings: ['Settings', 'dashicons-admin-settings'], tools: ['Tools', 'dashicons-admin-tools'],
        lock: ['Lock', 'dashicons-lock'], coffee: ['Coffee', 'dashicons-coffee'], award: ['Award', 'dashicons-awards'],
        thumbs_up: ['Approval', 'dashicons-thumbs-up'], flag: ['Flag', 'dashicons-flag'], search: ['Search', 'dashicons-search'],
        forms: ['Form', 'dashicons-forms'], bell: ['Notification', 'dashicons-bell'], comments: ['Comments', 'dashicons-admin-comments'],
        archive: ['Archive', 'dashicons-archive'], category: ['Category', 'dashicons-category'], car: ['Vehicle', 'dashicons-car'],
        code: ['Code', 'dashicons-editor-code'], ordered_list: ['Numbered list', 'dashicons-editor-ol'], table: ['Table', 'dashicons-editor-table'],
        list: ['List', 'dashicons-editor-ul'], external: ['External link', 'dashicons-external'], marker: ['Map pin', 'dashicons-marker'],
        spreadsheet: ['Spreadsheet', 'dashicons-media-spreadsheet'], schedule: ['Schedule', 'dashicons-schedule'], support: ['Support', 'dashicons-sos']
    };
    const paletteIcons = {
        calculation: 'dashicons-calculator', field_group: 'dashicons-grid-view', signature: 'dashicons-edit',
        rich_text: 'dashicons-editor-paste-word', divider: 'dashicons-minus', address: 'dashicons-location-alt',
        repeater: 'dashicons-list-view', appointment: 'dashicons-calendar-alt', nps: 'dashicons-chart-line',
        currency: 'dashicons-money-alt', product: 'dashicons-products', price: 'dashicons-tag', quantity: 'dashicons-editor-ol',
        product_option: 'dashicons-list-view', shipping: 'dashicons-car', donation: 'dashicons-heart', total: 'dashicons-cart',
        post_title: 'dashicons-heading', post_body: 'dashicons-editor-alignleft', post_excerpt: 'dashicons-editor-quote',
        post_tags: 'dashicons-tag', post_custom: 'dashicons-admin-generic'
    };

    function countryName(code) {
        try { return new Intl.DisplayNames([document.documentElement.lang || 'en'], { type: 'region' }).of(code) || code; }
        catch (error) { return code; }
    }
    const nestedFieldLabels = {
        name: 'Name', text: 'Text', email: 'Email', textarea: 'Long text', select: 'Dropdown', radio: 'Radio buttons',
        checkbox: 'Checkboxes', number: 'Number', date: 'Date', time: 'Time', phone: 'Phone', url: 'Website', file: 'File upload',
        consent: 'Consent', poll: 'Poll', quiz: 'Quiz', rating: 'Rating', slider: 'Slider', hidden: 'Hidden value', html: 'HTML',
        heading: 'Heading', calculation: 'Calculation', signature: 'E-signature', rich_text: 'Rich text / agreement', divider: 'Divider',
        address: 'Address', appointment: 'Appointment', nps: 'NPS score', currency: 'Currency', product: 'Product selector',
        price: 'Price', quantity: 'Quantity', product_option: 'Product option', shipping: 'Shipping', donation: 'Donation', total: 'Order total',
        post_title: 'Post title', post_body: 'Post body', post_excerpt: 'Post excerpt', post_tags: 'Post tags', post_custom: 'Post custom field'
    };

    function nestedTypeOptions(selected) {
        return Object.entries(nestedFieldLabels).map(([value, label]) => `<option value="${value}" ${value === selected ? 'selected' : ''}>${label}</option>`).join('');
    }

    function childEditor(child, index, escapeHtml) {
        const choiceType = ['select', 'radio', 'checkbox', 'poll', 'quiz', 'product', 'product_option', 'shipping'].includes(child.type);
        return `<details class="webform-pro-child" data-child-index="${index}" ${index === 0 ? 'open' : ''}>
            <summary class="webform-pro-child-head"><span class="dashicons dashicons-menu webform-pro-child-drag" title="Drag to reorder"></span><span><strong>${escapeHtml(child.label || nestedFieldLabels[child.type] || 'Field')}</strong><small>${escapeHtml(nestedFieldLabels[child.type] || child.type)}</small></span><button type="button" class="webform-remove-pro-child" aria-label="Remove ${escapeHtml(child.label || 'field')}"><span class="dashicons dashicons-trash"></span></button></summary>
            <div class="webform-pro-child-body">
            <label>Type<select data-pro-child="type">${nestedTypeOptions(child.type)}</select></label>
            <label>Label<input data-pro-child="label" value="${escapeHtml(child.label || '')}"></label>
            <label>Placeholder<input data-pro-child="placeholder" value="${escapeHtml(child.placeholder || '')}"></label>
            ${choiceType ? `<label>Options <small>One per line${['product', 'product_option', 'shipping'].includes(child.type) ? ', using Label|Price' : ''}</small><textarea rows="4" data-pro-child="options">${escapeHtml((child.options || []).join('\n'))}</textarea></label>` : ''}
            <div class="webform-pro-child-row"><label>Width<select data-pro-child="child_width"><option value="100" ${String(child.child_width || '100') === '100' ? 'selected' : ''}>Full</option><option value="75" ${String(child.child_width) === '75' ? 'selected' : ''}>75%</option><option value="66" ${String(child.child_width) === '66' ? 'selected' : ''}>Two thirds</option><option value="50" ${String(child.child_width) === '50' ? 'selected' : ''}>Half</option><option value="33" ${String(child.child_width) === '33' ? 'selected' : ''}>One third</option><option value="25" ${String(child.child_width) === '25' ? 'selected' : ''}>Quarter</option></select></label><label class="webform-pro-child-check"><input type="checkbox" data-pro-child="required" ${child.required ? 'checked' : ''}> Required</label></div>
            </div>
        </details>`;
    }

    function formulaHelp(field, escapeHtml) {
        const builder = window.FormOrbitBuilder;
        const stage = builder && builder.getSchema ? builder.getSchema()[builder.getActiveStage()] : null;
        const numericTypes = ['number', 'slider', 'rating', 'nps', 'currency', 'price', 'quantity', 'product', 'product_option', 'shipping', 'donation', 'total', 'calculation'];
        const tokens = ((stage && stage.fields) || []).filter(candidate => candidate.id !== field.id && numericTypes.includes(candidate.type));
        return `<div class="webform-formula-help"><p><strong>Formula builder</strong> Reference numeric fields by ID inside braces. Use <code>+ − * / % ( )</code>.</p>
            <div class="webform-formula-examples"><button type="button" data-formula-example="{quantity} * {price}">Quantity × price</button><button type="button" data-formula-example="{subtotal} + ({subtotal} * 0.10)">Add 10% tax</button><button type="button" data-formula-example="{hours} * {rate}">Hours × rate</button></div>
            ${tokens.length ? `<p class="webform-formula-token-label">Insert a field</p><div class="webform-formula-tokens">${tokens.map(candidate => `<button type="button" data-formula-token="{${escapeHtml(candidate.id)}}">${escapeHtml(candidate.label)} <code>{${escapeHtml(candidate.id)}}</code></button>`).join('')}</div>` : '<p class="description">Add a Number, Price, Quantity, or other numeric field to insert it here.</p>'}
            <p class="description">Example: <code>{quantity} * {unit_price}</code>. Missing or empty values are treated as zero.</p></div>`;
    }

    function registerBuilderFields() {
        if (!window.FormOrbitBuilder) return;
        const definitions = {};
        Object.entries(proFieldLabels).forEach(([type, label]) => {
            definitions[type] = {
                label,
                defaults() {
                    return {
                        formula: '', decimal_places: 2,
                        children: ['field_group', 'repeater'].includes(type) ? [{ id: `child_${Date.now()}`, type: 'text', label: 'Field', required: false, options: [] }] : [], group_columns: 2,
                        repeater_min: 1, repeater_max: 10, repeater_button: 'Add another row',
                        rich_content: '<p>Add your formatted agreement content here.</p>',
                        signature_mode: type === 'signature' ? 'both' : '', typed_signature_font: 'cursive',
                        address_country_enabled: type === 'address', address_default_country: 'US',
                        currency_symbol: '$', currency_code: 'USD', price_amount: 0,
                        min_date: '', max_date: '', post_type: 'post', post_status: 'draft',
                        post_meta_key: '', style: { width: '100' }
                    };
                },
                preview(field, escapeHtml) {
                    if (type === 'signature') return `<span class="webform-pro-field-preview">${field.signature_mode === 'type' ? 'Type your signature' : field.signature_mode === 'draw' ? 'Draw your signature' : 'Draw or type your signature'} __________________</span>`;
                    if (type === 'divider') return '<span class="webform-pro-field-preview"><hr></span>';
                    if (type === 'rich_text') return `<span class="webform-pro-field-preview">${escapeHtml(field.rich_content || 'Formatted agreement')}</span>`;
                    if (type === 'nps') return '<span class="webform-pro-field-preview">0 · 1 · 2 · 3 · 4 · 5 · 6 · 7 · 8 · 9 · 10</span>';
                    if (['price', 'total'].includes(type)) return `<span class="webform-pro-field-preview"><strong>${escapeHtml(field.currency_code || 'USD')} ${Number(field.price_amount || 0).toFixed(2)}</strong></span>`;
                    if (['field_group', 'repeater'].includes(type)) {
                        const children = field.children || [];
                        return `<span class="webform-pro-container-preview" data-container-id="${escapeHtml(field.id)}"><b>${type === 'repeater' ? 'Repeatable row' : `${Number(field.group_columns || 2)} column group`} <small>Drop fields here</small></b><span class="webform-nested-dropzone">${children.length ? children.map((child, index) => `<i class="webform-nested-child" data-child-index="${index}" style="--child-width:${escapeHtml(child.child_width || '100')}%"><span class="dashicons dashicons-menu"></span>${escapeHtml(child.label || child.type || 'Field')}</i>`).join('') : '<em>Drag a field into this box or add one in Field settings</em>'}</span></span>`;
                    }
                    if (type === 'address') return `<span class="webform-pro-container-preview"><b>Address</b><span><i>Street</i><i>City</i><i>State</i><i>Postal code</i>${field.address_country_enabled !== false ? '<i>Country ▾</i>' : ''}</span></span>`;
                    return `<span class="webform-pro-field-preview">${escapeHtml(field.placeholder || label)}</span>`;
                },
                settings(field, panel, escapeHtml) {
                    let html = '';
                    if (type === 'calculation') html += `${formulaHelp(field, escapeHtml)}<label>Formula<textarea rows="4" data-prop="formula" class="webform-formula-input" placeholder="{quantity} * {unit_price}">${escapeHtml(field.formula || '')}</textarea></label><label>Decimal places<input type="number" min="0" max="6" data-prop="decimal_places" value="${Number(field.decimal_places || 2)}"></label>`;
                    if (type === 'rich_text') html += `<label>Agreement content<textarea rows="10" data-prop="rich_content">${escapeHtml(field.rich_content || '')}</textarea></label>`;
                    if (type === 'signature') html += `<div class="webform-pro-guide"><strong>Signing method</strong><p>Draw captures handwriting on the signature pad. Type renders the visitor’s name as a signature image. “Draw or type” lets each visitor choose.</p></div><label>Allow visitors to<select data-prop="signature_mode"><option value="both" ${(field.signature_mode || 'both') === 'both' ? 'selected' : ''}>Draw or type</option><option value="draw" ${field.signature_mode === 'draw' ? 'selected' : ''}>Draw only</option><option value="type" ${field.signature_mode === 'type' ? 'selected' : ''}>Type only</option></select></label>`;
                    if (type === 'address') html += `<label class="webform-check"><input type="checkbox" data-prop="address_country_enabled" ${field.address_country_enabled !== false ? 'checked' : ''}> Include country selection</label><label>Default country<select data-prop="address_default_country">${Object.keys(window.WebformAdmin.phoneCountries || {}).sort().map(code => `<option value="${code}" ${String(field.address_default_country || 'US') === code ? 'selected' : ''}>${escapeHtml(countryName(code))}</option>`).join('')}</select></label>`;
                    if (type === 'repeater') html += `<label>Minimum rows<input type="number" min="1" max="20" data-prop="repeater_min" value="${Number(field.repeater_min || 1)}"></label><label>Maximum rows<input type="number" min="1" max="50" data-prop="repeater_max" value="${Number(field.repeater_max || 10)}"></label><label>Add-row label<input type="text" data-prop="repeater_button" value="${escapeHtml(field.repeater_button || 'Add another row')}"></label>`;
                    if (type === 'field_group') html += `<label>Columns<input type="number" min="1" max="4" data-prop="group_columns" value="${Number(field.group_columns || 2)}"></label>`;
                    if (['field_group', 'repeater'].includes(type)) html += `<div class="webform-pro-children"><div class="webform-pro-guide"><strong>Build the container</strong><p>Drag existing fields into the highlighted box on the canvas, or add them below. Drag child rows to reorder. Containers and page breaks cannot be nested.</p></div><h3>Child fields <small>${(field.children || []).length}</small></h3><div class="webform-pro-child-list">${(field.children || []).map((child, index) => childEditor(child, index, escapeHtml)).join('')}</div><button type="button" class="button webform-add-pro-child">Add child field</button></div>`;
                    if (type === 'appointment') html += `<div class="webform-pro-guide"><strong>Availability window</strong><p>Times use the WordPress site timezone. Leave either boundary blank when it should be unrestricted.</p></div><label>Earliest date and time<input type="datetime-local" data-prop="min_date" value="${escapeHtml(field.min_date || '')}"></label><label>Latest date and time<input type="datetime-local" data-prop="max_date" value="${escapeHtml(field.max_date || '')}"></label>`;
                    if (['currency', 'donation'].includes(type)) html += `<label>Currency symbol<input type="text" maxlength="5" data-prop="currency_symbol" value="${escapeHtml(field.currency_symbol || '$')}"></label>`;
                    if (['price', 'total', 'product', 'product_option', 'shipping', 'donation'].includes(type)) html += `<label>Currency code<input type="text" maxlength="3" data-prop="currency_code" value="${escapeHtml(field.currency_code || 'USD')}"></label>`;
                    if (type === 'price') html += `<label>Amount<input type="number" min="0" step="0.01" data-prop="price_amount" value="${Number(field.price_amount || 0)}"></label>`;
                    if (['product', 'product_option', 'shipping'].includes(type)) html += `<div class="webform-pro-guide"><strong>Priced options</strong><p>Enter one option per line as <code>Label|Price</code>, for example <code>Standard support|49.00</code>.</p></div><label>Options <small>One per line, using Label|Price</small><textarea rows="6" data-prop="options">${escapeHtml((field.options || []).join('\n'))}</textarea></label>`;
                    if (type === 'post_title') html += `<label>Content type<input type="text" data-prop="post_type" value="${escapeHtml(field.post_type || 'post')}"></label><label>Status<select data-prop="post_status"><option value="draft" ${field.post_status !== 'publish' ? 'selected' : ''}>Draft</option><option value="publish" ${field.post_status === 'publish' ? 'selected' : ''}>Publish</option></select></label>`;
                    if (type === 'post_custom') html += `<label>Meta key<input type="text" data-prop="post_meta_key" value="${escapeHtml(field.post_meta_key || '')}"></label>`;
                    if (html) panel.append(`<div class="webform-pro-field-settings">${html}</div>`);
                }
            };
        });
        window.FormOrbitBuilder.registerFieldTypes(definitions);
        if (typeof window.FormOrbitBuilder.enableLayoutTools === 'function') window.FormOrbitBuilder.enableLayoutTools();
        if (typeof window.FormOrbitBuilder.registerFieldIcons === 'function') {
            window.FormOrbitBuilder.registerFieldIcons(Object.fromEntries(Object.entries(fieldIcons).map(([key, value]) => [key, value[1]])));
        }
        if (typeof window.FormOrbitBuilder.registerFieldDropHandler === 'function') {
            window.FormOrbitBuilder.registerFieldDropHandler(handleContainerDrop);
        }
    }

    registerBuilderFields();

    function topLevelToChild(field) {
        const child = JSON.parse(JSON.stringify(field));
        delete child.condition;
        delete child.row_start;
        if (child.style && child.style.width) child.child_width = child.style.width === 'auto' ? '100' : child.style.width;
        delete child.style;
        return child;
    }

    function containerAtPoint(pageX, pageY, sourceId) {
        let match = null;
        document.querySelectorAll('.webform-nested-dropzone').forEach(function (zone) {
            const container = zone.closest('[data-container-id]');
            if (!container || String(container.dataset.containerId) === sourceId) return;
            const rect = zone.getBoundingClientRect();
            const left = rect.left + window.scrollX;
            const top = rect.top + window.scrollY;
            if (pageX >= left - 12 && pageX <= left + rect.width + 12 && pageY >= top - 12 && pageY <= top + rect.height + 12) match = zone;
        });
        return match;
    }

    function handleContainerDrop(details) {
        document.querySelectorAll('.webform-nested-dropzone.is-drop-ready').forEach(zone => zone.classList.remove('is-drop-ready'));
        const zone = containerAtPoint(details.pageX, details.pageY, details.fieldId);
        if (details.phase === 'move') {
            if (zone) zone.classList.add('is-drop-ready');
            return false;
        }
        if (!zone || details.phase !== 'drop') return false;
        const containerId = String(zone.closest('[data-container-id]').dataset.containerId || '');
        const fields = details.stage && details.stage.fields ? details.stage.fields : [];
        const fieldIndex = fields.findIndex(field => field.id === details.fieldId);
        const container = window.FormOrbitBuilder.getSchema().flatMap(stage => stage.fields || []).find(field => field.id === containerId);
        if (fieldIndex < 0 || !container || ['field_group', 'repeater', 'page_break'].includes(fields[fieldIndex].type)) return false;
        container.children = container.children || [];
        container.children.push(topLevelToChild(fields[fieldIndex]));
        fields.splice(fieldIndex, 1);
        return true;
    }

    function initializeContainerInteractions() {
        const builder = window.FormOrbitBuilder;
        const jq = window.jQuery;
        if (!builder || !builder.getSchema || !jq || !jq.fn.sortable) return;
        const stage = builder.getSchema()[builder.getActiveStage()];
        if (!stage) return;

        jq('.webform-nested-dropzone').each(function () {
            const zone = jq(this);
            const containerId = String(zone.closest('[data-container-id]').data('container-id') || '');
            zone.sortable({
                items: '.webform-nested-child',
                handle: '.dashicons-menu',
                tolerance: 'pointer',
                placeholder: 'webform-nested-placeholder',
                stop(event, ui) {
                    const container = builder.getSchema().flatMap(stage => stage.fields || []).find(field => field.id === containerId);
                    if (!container) return;
                    const rect = zone[0].getBoundingClientRect();
                    const x = event.pageX - window.scrollX, y = event.pageY - window.scrollY;
                    if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) {
                        const target = Array.from(document.querySelectorAll('.formorbit-stage-fields')).find(node => { const r=node.getBoundingClientRect(); return x>=r.left && x<=r.right && y>=r.top && y<=r.bottom; });
                        if (target) {
                            const child = container.children.splice(Number(ui.item.data('child-index')), 1)[0];
                            if (child) { child.style = {width: child.child_width || '100'}; child.row_start = true; builder.getSchema()[Number(target.dataset.stage)].fields.push(child); }
                            builder.markDirty();
                            return;
                        }
                    }
                    const order = zone.children('.webform-nested-child').map(function () { return Number(jq(this).data('child-index')); }).get();
                    container.children = order.map(index => container.children[index]).filter(Boolean);
                    builder.markDirty();
                }
            });
            zone.children('.webform-nested-child').each(function () {
                if (this.querySelector('.formorbit-child-remove')) return;
                this.insertAdjacentHTML('beforeend', '<button type="button" class="formorbit-child-edit" aria-label="Edit child field">Edit</button><button type="button" class="formorbit-child-out" aria-label="Move field to main form">↗</button><button type="button" class="formorbit-child-remove" aria-label="Remove child field">×</button>');
            });
        });

        const childList = jq('.webform-pro-child-list');
        if (childList.length) childList.sortable({
            items: '.webform-pro-child',
            handle: '.webform-pro-child-drag',
            tolerance: 'pointer',
            update() {
                const field = builder.selectedField();
                if (!field) return;
                const order = childList.children('.webform-pro-child').map(function () { return Number(jq(this).data('child-index')); }).get();
                field.children = order.map(index => field.children[index]).filter(Boolean);
                builder.markDirty();
            }
        });
    }

    document.addEventListener('click', function (event) {
        const child = event.target.closest('.webform-nested-child');
        if (!child || event.target.closest('.dashicons-menu')) return;
        const builder = window.FormOrbitBuilder;
        const card = child.closest('.webform-field-card');
        const stageIndex = Number(card.closest('.formorbit-stage-fields').dataset.stage);
        const container = builder.getSchema()[stageIndex].fields.find(field => field.id === card.dataset.id);
        const index = Number(child.dataset.childIndex);
        if (!container || !container.children[index]) return;
        event.preventDefault(); event.stopImmediatePropagation();
        builder.setActiveStage(stageIndex);
        if (event.target.closest('.formorbit-child-remove')) { container.children.splice(index,1); builder.markDirty(); return; }
        if (event.target.closest('.formorbit-child-out')) { const field=container.children.splice(index,1)[0]; field.style={width:field.child_width||'100'}; builder.getSchema()[stageIndex].fields.push(field); builder.markDirty(); return; }
        window.jQuery(card).trigger('click');
        const editor=document.querySelector('.webform-pro-child[data-child-index="'+index+'"]');
        if (editor) { editor.open=true; editor.scrollIntoView({block:'nearest'}); const label=editor.querySelector('[data-pro-child="label"]'); if(label)label.focus(); }
    }, true);

    document.addEventListener('formorbit:field-settings-rendered', function (event) {
        const field = event.detail && event.detail.field;
        const panel = event.detail && event.detail.panel;
        if (!field || !panel) return;
        field.style = field.style || {};
        if (!panel.querySelector('.webform-icon-property') && !['hidden', 'html', 'rich_text', 'divider'].includes(field.type)) {
            const selected = fieldIcons[field.icon] || fieldIcons[''];
            panel.insertAdjacentHTML('beforeend', `<div class="webform-icon-property"><label>Field icon <small>Shown beside the field label</small></label><button type="button" class="webform-open-icon-gallery"><span class="dashicons ${selected[1]}" aria-hidden="true"></span><span>${selected[0]}</span><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button></div>`);
        }
        if (!panel.querySelector('.webform-pro-layout-settings')) {
            panel.insertAdjacentHTML('beforeend', `<div class="webform-pro-layout-settings"><hr><h3>Advanced field layout</h3><label>Width<select data-pro-style="width"><option value="100" ${(field.style.width || '100') === '100' ? 'selected' : ''}>Full width</option><option value="75" ${field.style.width === '75' ? 'selected' : ''}>75%</option><option value="50" ${field.style.width === '50' ? 'selected' : ''}>Half width</option><option value="33" ${field.style.width === '33' ? 'selected' : ''}>One third</option><option value="auto" ${field.style.width === 'auto' ? 'selected' : ''}>Automatic</option></select></label><label>Custom CSS class<input data-pro-style="css_class" value="${String(field.style.css_class || '').replace(/[&<>"']/g, '')}"></label></div>`);
        }
        if (!panel.querySelector('.formorbit-field-design')) {
            const escape = value => String(value || '').replace(/[&<>"']/g, '');
            const textField = !['heading', 'divider', 'hidden'].includes(field.type);
            panel.insertAdjacentHTML('beforeend', '<details class="formorbit-field-design"><summary>Field appearance</summary><p class="description">Leave colors empty to inherit the form theme.</p><div class="webform-style-control-grid">' +
                '<label>Label color<input data-pro-style="label_color" placeholder="#RRGGBB" value="' + escape(field.style.customized ? field.style.label_color : '') + '"></label>' +
                (textField ? '<label>Text color<input data-pro-style="text_color" placeholder="#RRGGBB" value="' + escape(field.style.customized ? field.style.text_color : '') + '"></label><label>Background<input data-pro-style="background_color" placeholder="#RRGGBB" value="' + escape(field.style.customized ? field.style.background_color : '') + '"></label>' : '') +
                '<label>Corner radius<input type="number" data-pro-style="radius" min="0" max="40" value="' + Number(field.style.radius || 0) + '"></label></div></details>');
        }
        if (!panel.querySelector('.formorbit-field-typography') && !['hidden', 'divider', 'captcha'].includes(field.type)) {
            const options = (key, values) => values.map(([value, label]) => `<option value="${value}" ${String(field.style[key] || 'inherit') === value ? 'selected' : ''}>${label}</option>`).join('');
            panel.insertAdjacentHTML('beforeend', `<details class="formorbit-field-typography" open><summary>Typography</summary><div class="webform-style-control-grid"><label>Font family<select data-pro-style="font_family">${options('font_family', [['inherit','Form default'],['Arial,sans-serif','Arial'],['Georgia,serif','Georgia'],['Verdana,sans-serif','Verdana'],['monospace','Monospace']])}</select></label><label>Font size (px)<input data-pro-style="font_size" type="number" min="10" max="96" placeholder="Form default" value="${Number(field.style.font_size) || ''}"></label><label>Font weight<select data-pro-style="font_weight">${options('font_weight', [['inherit','Form default'],['400','Regular'],['500','Medium'],['600','Semibold'],['700','Bold'],['800','Extra bold']])}</select></label><label>Font style<select data-pro-style="font_style">${options('font_style', [['normal','Normal'],['italic','Italic']])}</select></label></div></details>`);
        }
        window.setTimeout(initializeContainerInteractions, 0);
    });

    function decoratePalette() {
        Object.entries(paletteIcons).forEach(([type, icon]) => {
            const target = document.querySelector(`.webform-palette-item[data-type="${type}"] .dashicons`);
            if (target) target.className = `dashicons ${icon}`;
        });
    }

    function ensureIconGallery() {
        if (document.getElementById('webform-icon-gallery')) return;
        const buttons = Object.entries(fieldIcons).map(([key, value]) => `<button type="button" data-icon="${key}" data-search="${value[0].toLowerCase()}"><span class="dashicons ${value[1]}" aria-hidden="true"></span><span>${value[0]}</span></button>`).join('');
        document.body.insertAdjacentHTML('beforeend', `<div id="webform-icon-gallery" class="webform-icon-gallery" aria-hidden="true"><button type="button" class="webform-icon-gallery-backdrop" tabindex="-1" aria-label="Close icon gallery"></button><div class="webform-icon-gallery-dialog" role="dialog" aria-modal="true" aria-labelledby="webform-icon-gallery-title"><div class="webform-icon-gallery-head"><div><h2 id="webform-icon-gallery-title">Choose a field icon</h2><p>Icons appear beside the field label.</p></div><button type="button" class="webform-icon-gallery-close" aria-label="Close">×</button></div><label class="webform-icon-gallery-search"><span class="dashicons dashicons-search" aria-hidden="true"></span><span class="screen-reader-text">Search icons</span><input type="search" placeholder="Search icons…" autocomplete="off"></label><div class="webform-icon-gallery-grid">${buttons}</div><p class="webform-icon-gallery-empty" hidden>No matching icons.</p></div></div>`);
    }

    function closeIconGallery() {
        const gallery = document.getElementById('webform-icon-gallery');
        if (gallery) {
            gallery.classList.remove('is-open');
            gallery.setAttribute('aria-hidden', 'true');
        }
    }

    document.addEventListener('change', function (event) {
        const builder = window.FormOrbitBuilder;
        if (!builder) return;
        const field = builder.selectedField();
        if (!field) return;
        const styleControl = event.target.closest('[data-pro-style]');
        if (styleControl) {
            field.style = field.style || {};
            if (['label_color','text_color','background_color'].includes(styleControl.dataset.proStyle) && styleControl.value && !/^#[0-9a-f]{6}$/i.test(styleControl.value)) { styleControl.setCustomValidity('Enter a six-digit hex color, such as #334155.'); styleControl.reportValidity(); return; }
            styleControl.setCustomValidity('');
            if (!['width','css_class'].includes(styleControl.dataset.proStyle)) {
                if (!field.style.customized) ['label_color','text_color','background_color'].forEach(key => { field.style[key] = ''; });
                field.style.customized = true;
            }
            field.style[styleControl.dataset.proStyle] = styleControl.value;
            builder.markDirty();
            return;
        }
        const childControl = event.target.closest('[data-pro-child]');
        if (childControl) {
            const row = childControl.closest('[data-child-index]');
            const child = (field.children || [])[Number(row.dataset.childIndex)];
            if (child) {
                const property = childControl.dataset.proChild;
                if (property === 'required') child[property] = childControl.checked;
                else if (property === 'options') child[property] = childControl.value.split(/\r?\n/).map(value => value.trim()).filter(Boolean);
                else child[property] = childControl.value;
            }
            builder.markDirty();
        }
    });

    document.addEventListener('input', function (event) {
        if (!event.target.matches('#webform-icon-gallery input[type="search"]')) return;
        const query = event.target.value.trim().toLowerCase();
        let visible = 0;
        document.querySelectorAll('#webform-icon-gallery [data-icon]').forEach(function (button) {
            const matches = !query || button.dataset.search.includes(query);
            button.hidden = !matches;
            if (matches) visible++;
        });
        const empty = document.querySelector('.webform-icon-gallery-empty');
        if (empty) empty.hidden = visible > 0;
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && document.getElementById('webform-icon-gallery')?.classList.contains('is-open')) {
            closeIconGallery();
        }
    });

    document.addEventListener('click', function (event) {
        const builder = window.FormOrbitBuilder;
        if (!builder) return;
        const field = builder.selectedField();
        if (!field) return;
        const formulaInsert = event.target.closest('[data-formula-example],[data-formula-token]');
        if (formulaInsert) {
            const textarea = document.querySelector('.webform-formula-input');
            if (!textarea) return;
            const value = formulaInsert.dataset.formulaExample || formulaInsert.dataset.formulaToken || '';
            if (formulaInsert.dataset.formulaExample) field.formula = value;
            else {
                const start = textarea.selectionStart || String(field.formula || '').length;
                const end = textarea.selectionEnd || start;
                field.formula = String(field.formula || '').slice(0, start) + value + String(field.formula || '').slice(end);
            }
            builder.markDirty();
            window.setTimeout(() => document.querySelector('.webform-formula-input')?.focus(), 0);
            return;
        }
        if (event.target.closest('.webform-open-icon-gallery')) {
            ensureIconGallery();
            const gallery = document.getElementById('webform-icon-gallery');
            gallery.classList.add('is-open');
            gallery.setAttribute('aria-hidden', 'false');
            gallery.querySelectorAll('[data-icon]').forEach(button => button.classList.toggle('is-selected', button.dataset.icon === String(field.icon || '')));
            window.setTimeout(() => gallery.querySelector('input').focus(), 20);
            return;
        }
        if (event.target.closest('.webform-icon-gallery-close,.webform-icon-gallery-backdrop')) {
            closeIconGallery();
            return;
        }
        const iconButton = event.target.closest('#webform-icon-gallery [data-icon]');
        if (iconButton) {
            field.icon = iconButton.dataset.icon;
            builder.markDirty();
            closeIconGallery();
            return;
        }
        if (event.target.closest('.webform-add-pro-child')) {
            field.children = field.children || [];
            field.children.push({ id: `child_${Date.now()}`, type: 'text', label: 'Field', required: false, options: [] });
            builder.markDirty();
        }
        const remove = event.target.closest('.webform-remove-pro-child');
        if (remove) {
            event.preventDefault();
            event.stopPropagation();
            const row = remove.closest('[data-child-index]');
            field.children.splice(Number(row.dataset.childIndex), 1);
            builder.markDirty();
        }
    });

    document.addEventListener('webform:collect-settings', function (event) {
        const settings = event.detail || {};
        const value = id => {
            const field = document.getElementById(id);
            return field ? field.value : '';
        };
        const checked = id => {
            const field = document.getElementById(id);
            return !!(field && field.checked);
        };
        settings.webhook_url = value('webform-webhook-url');
        settings.open_at = value('webform-open-at');
        settings.close_at = value('webform-close-at');
        settings.per_user_limit = value('webform-per-user-limit');
        settings.allowed_roles = Array.from(document.querySelectorAll('[name="webform_allowed_roles"]:checked')).map(field => field.value);
        settings.hide_after_submit = checked('webform-hide-after-submit');
        settings.save_progress_enabled = checked('webform-save-progress-enabled');
        settings.save_progress_days = value('webform-save-progress-days');
        settings.save_progress_label = value('webform-save-progress-label');
        settings.user_notification_enabled = checked('webform-user-notification-enabled');
        settings.user_notification_email_field = value('webform-user-notification-email-field');
        settings.user_notification_subject = value('webform-user-notification-subject');
        settings.user_notification_body = value('webform-user-notification-body');
        settings.user_notification_pdf = checked('webform-user-notification-pdf');
        settings.admin_sms_enabled = checked('webform-admin-sms-enabled');
        settings.user_sms_enabled = checked('webform-user-sms-enabled');
        settings.user_sms_phone_field = value('webform-user-sms-phone-field');
        settings.sms_message = value('webform-sms-message');
        settings.followup_enabled = checked('webform-followup-enabled');
        settings.followup_delay = value('webform-followup-delay');
        settings.followup_channel = value('webform-followup-channel');
        settings.followup_subject = value('webform-followup-subject');
        settings.followup_message = value('webform-followup-message');
        settings.font_family = value('webform-font-family');
        ['heading_font_size', 'line_height', 'form_padding', 'form_radius', 'control_padding', 'label_weight'].forEach(key => { settings[key] = value('webform-' + key.replaceAll('_', '-')); });
        settings.base_font_size = value('webform-base-font-size');
        settings.label_font_size = value('webform-label-font-size');
        settings.text_color = value('webform-text-color');
        settings.form_background = value('webform-form-background');
        settings.field_background = value('webform-field-background');
        settings.border_color = value('webform-border-color');
        settings.form_max_width = value('webform-form-max-width');
        settings.field_spacing = value('webform-field-spacing');
        settings.field_radius = value('webform-field-radius');
        settings.button_radius = value('webform-button-radius');
        settings.button_padding = value('webform-button-padding');
        settings.submit_alignment = value('webform-submit-alignment');
        settings.submit_font_size = value('webform-submit-font-size');
        settings.submit_font_weight = value('webform-submit-font-weight');
        settings.submit_padding_x = value('webform-submit-padding-x');
        settings.submit_padding_y = value('webform-submit-padding-y');
        settings.submit_border_width = value('webform-submit-border-width');
        settings.submit_background = value('webform-submit-background');
        settings.submit_hover_background = value('webform-submit-hover-background');
        settings.submit_text_color = value('webform-submit-text-color');
        settings.submit_border_color = value('webform-submit-border-color');
        settings.custom_css = value('webform-custom-css');
        settings.pdf_notifications = checked('webform-pdf-notifications');
        settings.mailchimp_list = value('webform-mailchimp-list');
        settings.brevo_list = value('webform-brevo-list');
        settings.activecampaign_list = value('webform-activecampaign-list');
        settings.leadconnector_enabled = checked('webform-leadconnector-enabled');
        settings.contact_integrations = {};
        document.querySelectorAll('[data-contact-provider]').forEach(function (route) {
            const enabled = route.querySelector('.webform-contact-enabled');
            const list = route.querySelector('.webform-contact-list');
            if (enabled && enabled.checked && list && list.value) settings.contact_integrations[route.dataset.contactProvider] = list.value;
        });
        settings.calendar_provider = value('webform-calendar-provider');
        settings.payment_provider = value('webform-payment-provider');
    });

    document.addEventListener('DOMContentLoaded', function () {
        const requireLogin = document.getElementById('webform-require-login');
        const roleControls = document.getElementById('webform-role-controls');
        const syncRoleControls = function () {
            if (!requireLogin || !roleControls) return;
            const enabled = requireLogin.checked;
            roleControls.classList.toggle('is-disabled', !enabled);
            roleControls.setAttribute('aria-disabled', enabled ? 'false' : 'true');
            roleControls.querySelectorAll('input').forEach(function (input) {
                input.tabIndex = enabled ? 0 : -1;
            });
        };
        if (requireLogin && roleControls) {
            requireLogin.addEventListener('change', syncRoleControls);
            roleControls.addEventListener('click', function (event) {
                if (!requireLogin.checked) event.preventDefault();
            });
            syncRoleControls();
        }

        const dependentControls = [
            ['webform-user-notification-enabled', 'webform-user-notification-options'],
            ['webform-save-progress-enabled', 'webform-save-progress-options'],
            ['webform-followup-enabled', 'webform-followup-options']
        ];
        dependentControls.forEach(function (pair) {
            const toggle = document.getElementById(pair[0]);
            const options = document.getElementById(pair[1]);
            if (!toggle || !options) return;
            const sync = function () {
                options.classList.toggle('is-disabled', !toggle.checked);
                options.setAttribute('aria-disabled', toggle.checked ? 'false' : 'true');
                options.querySelectorAll('input,select,textarea,button').forEach(function (control) {
                    control.disabled = !toggle.checked;
                });
                if (window.tinymce && pair[0] === 'webform-user-notification-enabled') {
                    const editor = window.tinymce.get('webform-user-notification-body');
                    if (editor && editor.getBody()) editor.getBody().setAttribute('contenteditable', toggle.checked ? 'true' : 'false');
                }
            };
            toggle.addEventListener('change', sync);
            sync();
        });
        const adminSms = document.getElementById('webform-admin-sms-enabled');
        const visitorSms = document.getElementById('webform-user-sms-enabled');
        const visitorSmsOptions = document.getElementById('webform-user-sms-options');
        const immediateSmsOptions = document.getElementById('webform-immediate-sms-options');
        const syncSmsOptions = function () {
            const visitorEnabled = !!(visitorSms && visitorSms.checked);
            const anyEnabled = visitorEnabled || !!(adminSms && adminSms.checked);
            [[visitorSmsOptions, visitorEnabled], [immediateSmsOptions, anyEnabled]].forEach(function (item) {
                if (!item[0]) return;
                item[0].classList.toggle('is-disabled', !item[1]);
                item[0].querySelectorAll('input,select,textarea').forEach(function (control) { control.disabled = !item[1]; });
            });
        };
        if (adminSms) adminSms.addEventListener('change', syncSmsOptions);
        if (visitorSms) visitorSms.addEventListener('change', syncSmsOptions);
        syncSmsOptions();

        document.querySelectorAll('.webform-property-panel input[type="color"]').forEach(function (input) {
            const label = input.closest('label');
            if (!label || input.closest('.webform-color-control')) return;
            label.classList.add('webform-color-setting');
            const control = document.createElement('span');
            control.className = 'webform-color-control';
            input.parentNode.insertBefore(control, input);
            control.appendChild(input);
            const value = document.createElement('code');
            value.className = 'webform-color-value';
            control.appendChild(value);
            const syncColorValue = function () { value.textContent = String(input.value || '').toUpperCase(); };
            input.addEventListener('input', syncColorValue);
            input.addEventListener('change', syncColorValue);
            syncColorValue();
        });
        decoratePalette();
        ensureIconGallery();
        const propertyTabs = document.querySelector('.webform-property-tabs');
        const propertyPanels = Array.from(document.querySelectorAll('.webform-properties > .webform-property-panel'));
        const synchronizePropertyNavigation = function (activePanel) {
            if (!propertyTabs) return;
            propertyTabs.setAttribute('role', 'tablist');
            propertyTabs.setAttribute('aria-label', 'Form editor settings');
            propertyTabs.querySelectorAll('button[data-panel]').forEach(function (button) {
                const panelName = button.dataset.panel;
                const panel = propertyPanels.find(candidate => candidate.dataset.panel === panelName);
                const selected = panelName === activePanel;
                button.setAttribute('role', 'tab');
                button.setAttribute('aria-selected', selected ? 'true' : 'false');
                button.setAttribute('tabindex', selected ? '0' : '-1');
                if (panel) {
                    panel.id = panel.id || `webform-property-panel-${panelName}`;
                    button.setAttribute('aria-controls', panel.id);
                    panel.setAttribute('role', 'tabpanel');
                    panel.setAttribute('aria-labelledby', button.id || `webform-property-tab-${panelName}`);
                    button.id = button.id || `webform-property-tab-${panelName}`;
                    panel.setAttribute('aria-hidden', selected ? 'false' : 'true');
                }
            });
        };

        if (propertyTabs) {
            const initiallyActive = propertyTabs.querySelector('button.is-active[data-panel]');
            synchronizePropertyNavigation(initiallyActive ? initiallyActive.dataset.panel : 'field');
            propertyTabs.addEventListener('click', function (event) {
                const button = event.target.closest('button[data-panel]');
                if (!button) return;
                window.requestAnimationFrame(function () {
                    synchronizePropertyNavigation(button.dataset.panel);
                });
            });
            propertyTabs.addEventListener('keydown', function (event) {
                if (!['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(event.key)) return;
                const buttons = Array.from(propertyTabs.querySelectorAll('button[data-panel]'));
                const current = buttons.indexOf(document.activeElement);
                if (current < 0) return;
                event.preventDefault();
                let next = current;
                if (event.key === 'Home') next = 0;
                else if (event.key === 'End') next = buttons.length - 1;
                else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') next = (current - 1 + buttons.length) % buttons.length;
                else next = (current + 1) % buttons.length;
                buttons[next].focus();
                buttons[next].click();
            });
            new MutationObserver(function () {
                const active = propertyTabs.querySelector('button.is-active[data-panel]');
                if (active && active.getAttribute('aria-selected') !== 'true') {
                    synchronizePropertyNavigation(active.dataset.panel);
                }
            }).observe(propertyTabs, { attributes: true, attributeFilter: ['class'], subtree: true });
        }

        const settingsPanel = document.querySelector('.webform-pro-settings-sections');
        if (settingsPanel) {
            const addSection = function (title, source, open) {
                if (!source) return;
                const section = document.createElement('details');
                section.className = 'webform-editor-settings-section';
                section.open = !!open;
                const summary = document.createElement('summary');
                summary.textContent = title;
                const body = document.createElement('div');
                body.className = 'webform-editor-settings-body';
                while (source.firstChild) body.appendChild(source.firstChild);
                section.appendChild(summary);
                section.appendChild(body);
                settingsPanel.appendChild(section);
                source.remove();
            };
            addSection('Appearance & submit button', document.querySelector('.webform-property-panel[data-panel="style"]'), true);
            addSection('Save & Continue', document.querySelector('.webform-save-continue-section'), false);
            addSection('Admin PDF notification', document.querySelector('.webform-admin-pdf-section'), false);
            const styleTab = document.querySelector('.webform-property-tabs button[data-panel="style"]');
            if (styleTab) styleTab.remove();
        }
        const wrapConfirmationSection = function (title, source) {
            if (!source || !source.parentNode) return;
            const details = document.createElement('details');
            details.className = 'webform-editor-settings-section webform-confirmation-workflow';
            const summary = document.createElement('summary');
            summary.textContent = title;
            source.parentNode.insertBefore(details, source);
            details.appendChild(summary);
            details.appendChild(source);
        };
        wrapConfirmationSection('Visitor email', document.querySelector('.webform-visitor-confirmation-section'));
        wrapConfirmationSection('SMS & follow-ups', document.querySelector('.webform-notification-workflows'));
        document.addEventListener('click', function (event) {
            const summary = event.target.closest('details.webform-editor-settings-section > summary');
            if (summary && summary.parentElement) summary.parentElement.dataset.webformUserToggle = 'true';
        });
        document.addEventListener('toggle', function (event) {
            const opened = event.target;
            if (!(opened instanceof HTMLDetailsElement) || !opened.open || !opened.classList.contains('webform-editor-settings-section')) return;
            const group = opened.closest('.webform-pro-settings-sections') || opened.closest('.webform-property-panel');
            if (!group) return;
            group.querySelectorAll('details.webform-editor-settings-section[open]').forEach(function (section) {
                if (section !== opened) section.open = false;
            });
            if (opened.dataset.webformUserToggle === 'true') {
                delete opened.dataset.webformUserToggle;
                window.requestAnimationFrame(function () {
                    window.requestAnimationFrame(function () {
                        opened.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                });
            }
        }, true);
        document.querySelectorAll('[data-contact-provider]').forEach(function (route) {
            const checkbox = route.querySelector('.webform-contact-enabled');
            const destination = route.querySelector('.webform-contact-destination');
            if (!checkbox || !destination) return;
            const sync = function () {
                destination.hidden = !checkbox.checked;
                if (checkbox.checked && !route.querySelector('.webform-contact-list').value) route.querySelector('.webform-contact-list').focus();
            };
            checkbox.addEventListener('change', sync);
            sync();
        });
        async function refreshLicenseCard() {
            const response = await fetch(window.location.href, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) throw new Error('Unable to refresh the license panel.');
            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const nextCard = page.querySelector('.webform-license-card');
            const currentCard = document.querySelector('.webform-license-card');
            if (nextCard && currentCard) currentCard.replaceWith(nextCard);
        }

        async function submitLicenseAction(form, action) {
            const message = form.querySelector('.webform-license-live-message');
            const data = new FormData(form);
            data.set('action', 'formorbit_pro_license');
            data.set('license_action', action || 'activate');
            form.classList.add('is-loading');
            form.setAttribute('aria-busy', 'true');
            if (message) message.textContent = 'Updating license status…';
            try {
                const response = await fetch(form.dataset.ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: data
                });
                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.data?.message || 'The license request could not be completed.');
                }
                await refreshLicenseCard();
                if (result.data?.valid) {
                    document.querySelectorAll('.webform-pro-license-notice').forEach(notice => notice.remove());
                }
            } catch (error) {
                form.classList.remove('is-loading');
                form.removeAttribute('aria-busy');
                if (message) message.textContent = error.message;
            }
        }

        document.addEventListener('submit', function (event) {
            const form = event.target.closest('.webform-license-form[data-ajax-url]');
            if (!form) return;
            event.preventDefault();
            const submitter = event.submitter || document.activeElement;
            submitLicenseAction(form, submitter && submitter.name === 'license_action' ? submitter.value : 'activate');
        });

        const licenseForm = document.querySelector('.webform-license-form[data-has-key="1"]');
        const inactiveLicense = document.querySelector('.webform-license-status.is-invalid');
        if (licenseForm && inactiveLicense) {
            let checkedAt = 0;
            try {
                checkedAt = Number(sessionStorage.getItem('formorbitLicenseCheckedAt') || 0);
            } catch (error) {
                checkedAt = 0;
            }
            if (Date.now() - checkedAt > 60000) {
                try {
                    sessionStorage.setItem('formorbitLicenseCheckedAt', String(Date.now()));
                } catch (error) {
                    // Continue without throttling when browser storage is disabled.
                }
                window.setTimeout(function () {
                    const currentForm = document.querySelector('.webform-license-form[data-has-key="1"]');
                    if (currentForm) submitLicenseAction(currentForm, 'check');
                }, 700);
            }
        }

        const type = document.getElementById('webform-integration-type');
        const provider = document.getElementById('webform-integration-provider');
        if (type && provider) {
            const panels = Array.from(document.querySelectorAll('.webform-pro-grid [data-category][data-provider]'));
            function providersFor(category) {
                return panels.filter(panel => panel.dataset.category === category);
            }
            function renderProviders() {
                const available = providersFor(type.value);
                provider.innerHTML = available.map(panel => `<option value="${panel.dataset.provider}">${panel.querySelector('legend').textContent}</option>`).join('');
                renderPanel();
            }
            function renderPanel() {
                panels.forEach(panel => {
                    panel.hidden = panel.dataset.category !== type.value || panel.dataset.provider !== provider.value;
                });
                const smsDefaults = document.getElementById('webform-sms-defaults');
                const activePanel = panels.find(panel => !panel.hidden);
                if (smsDefaults) {
                    smsDefaults.hidden = type.value !== 'sms' || !activePanel;
                    if (!smsDefaults.hidden) {
                        activePanel.appendChild(smsDefaults);
                        const activeProvider = smsDefaults.querySelector('[name="sms_provider"]');
                        if (activeProvider) activeProvider.value = provider.value;
                    }
                }
            }
            type.addEventListener('change', renderProviders);
            provider.addEventListener('change', renderPanel);
            renderProviders();
        }

        const supportForm = document.getElementById('formorbit-pro-support-form');
        if (supportForm) {
            const message = supportForm.querySelector('[name="message"]');
            const counter = supportForm.querySelector('.webform-support-character-count');
            const status = supportForm.querySelector('.webform-support-status');
            const submit = supportForm.querySelector('[type="submit"]');
            const captchaQuestion = supportForm.querySelector('.webform-support-captcha-question');
            const captchaToken = supportForm.querySelector('[name="captcha_token"]');
            const captchaAnswer = supportForm.querySelector('[name="captcha_answer"]');
            const updateCaptcha = (captcha) => {
                if (!captcha || !captcha.token || !captcha.question) return;
                if (captchaQuestion) captchaQuestion.textContent = captcha.question;
                if (captchaToken) captchaToken.value = captcha.token;
                if (captchaAnswer) captchaAnswer.value = '';
            };
            const updateCount = () => {
                if (counter && message) counter.textContent = `${message.value.length} / 8000`;
            };
            if (message) {
                message.addEventListener('input', updateCount);
                updateCount();
            }
            supportForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                if (!supportForm.reportValidity()) return;
                const data = new FormData(supportForm);
                submit.disabled = true;
                supportForm.classList.add('is-sending');
                status.className = 'webform-support-status';
                status.textContent = 'Sending securely…';
                try {
                    const response = await fetch(supportForm.dataset.ajaxUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: data
                    });
                    const result = await response.json();
                    if (result.data?.captcha) updateCaptcha(result.data.captcha);
                    if (!response.ok || !result.success) throw new Error(result.data?.message || 'The support request could not be sent.');
                    status.className = 'webform-support-status is-success';
                    status.textContent = result.data.message;
                    supportForm.querySelector('[name="subject"]').value = '';
                    supportForm.querySelector('[name="issue_url"]').value = '';
                    message.value = '';
                    supportForm.querySelector('[name="diagnostic_consent"]').checked = false;
                    updateCount();
                } catch (error) {
                    status.className = 'webform-support-status is-error';
                    status.textContent = error.message;
                } finally {
                    submit.disabled = false;
                    supportForm.classList.remove('is-sending');
                }
            });
        }
    });
})();
