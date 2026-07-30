(function ($) {
    'use strict';

    const defaults = { name: 'Name', text: 'Text', email: 'Email', textarea: 'Long text', select: 'Dropdown', radio: 'Radio', checkbox: 'Checkbox', number: 'Number', date: 'Date', time: 'Time', phone: 'Phone', url: 'Website', file: 'File upload', consent: 'I agree to the terms', poll: 'Poll question', quiz: 'Quiz question', rating: 'Rating', slider: 'Slider', hidden: 'Hidden field', html: 'HTML content', captcha: 'Security check', calculation: 'Calculation', signature: 'Signature', rich_text: 'Rich text', divider: 'Divider', field_group: 'Field group', address: 'Address', repeater: 'Repeater', appointment: 'Appointment', nps: 'NPS score', currency: 'Currency', product: 'Product selector', price: 'Price', heading: 'Heading' };
    const fieldIcons = {
        '': ['No icon', 'dashicons-minus'],
        user: ['Person', 'dashicons-admin-users'],
        groups: ['People', 'dashicons-groups'],
        email: ['Email', 'dashicons-email-alt'],
        phone: ['Phone', 'dashicons-phone'],
        location: ['Location', 'dashicons-location-alt'],
        home: ['Home', 'dashicons-admin-home'],
        business: ['Business', 'dashicons-building'],
        calendar: ['Calendar', 'dashicons-calendar-alt'],
        clock: ['Clock', 'dashicons-clock'],
        link: ['Link', 'dashicons-admin-links'],
        cart: ['Cart', 'dashicons-cart'],
        money: ['Payment', 'dashicons-money-alt'],
        portfolio: ['Portfolio', 'dashicons-portfolio'],
        clipboard: ['Clipboard', 'dashicons-clipboard'],
        document: ['Document', 'dashicons-media-document'],
        book: ['Book', 'dashicons-book'],
        edit: ['Signature', 'dashicons-edit'],
        upload: ['Upload', 'dashicons-upload'],
        download: ['Download', 'dashicons-download'],
        image: ['Image', 'dashicons-format-image'],
        camera: ['Camera', 'dashicons-camera'],
        video: ['Video', 'dashicons-video-alt3'],
        audio: ['Audio', 'dashicons-format-audio'],
        chart: ['Chart', 'dashicons-chart-bar'],
        trend: ['Trend', 'dashicons-chart-line'],
        ticket: ['Ticket', 'dashicons-tickets-alt'],
        heart: ['Heart', 'dashicons-heart'],
        star: ['Star', 'dashicons-star-filled'],
        shield: ['Security', 'dashicons-shield'],
        info: ['Information', 'dashicons-info-outline'],
        warning: ['Warning', 'dashicons-warning'],
        check: ['Approved', 'dashicons-yes-alt'],
        website: ['Website', 'dashicons-admin-site-alt3'],
        network: ['Network', 'dashicons-admin-multisite'],
        feedback: ['Message', 'dashicons-feedback'],
        food: ['Food', 'dashicons-food'],
        pets: ['Pets', 'dashicons-pets'],
        travel: ['Travel', 'dashicons-airplane'],
        contact: ['Contact card', 'dashicons-id-alt'],
        store: ['Store', 'dashicons-store'],
        tag: ['Tag', 'dashicons-tag'],
        calculator: ['Calculator', 'dashicons-calculator'],
        analytics: ['Analytics', 'dashicons-analytics'],
        pie: ['Pie chart', 'dashicons-chart-pie'],
        announcement: ['Announcement', 'dashicons-megaphone'],
        chat: ['Chat', 'dashicons-format-chat'],
        quote: ['Quote', 'dashicons-editor-quote'],
        help: ['Help', 'dashicons-editor-help'],
        idea: ['Idea', 'dashicons-lightbulb'],
        attachment: ['Attachment', 'dashicons-paperclip'],
        folder: ['Folder', 'dashicons-open-folder'],
        database: ['Database', 'dashicons-database'],
        cloud: ['Cloud', 'dashicons-cloud'],
        desktop: ['Desktop', 'dashicons-desktop'],
        laptop: ['Laptop', 'dashicons-laptop'],
        tablet: ['Tablet', 'dashicons-tablet'],
        mobile: ['Mobile', 'dashicons-smartphone'],
        settings: ['Settings', 'dashicons-admin-settings'],
        tools: ['Tools', 'dashicons-admin-tools'],
        lock: ['Lock', 'dashicons-lock'],
        coffee: ['Coffee', 'dashicons-coffee'],
        award: ['Award', 'dashicons-awards'],
        thumbs_up: ['Approval', 'dashicons-thumbs-up'],
        flag: ['Flag', 'dashicons-flag'],
        search: ['Search', 'dashicons-search'],
        forms: ['Form', 'dashicons-forms'],
        bell: ['Notification', 'dashicons-bell'],
        comments: ['Comments', 'dashicons-admin-comments'],
        archive: ['Archive', 'dashicons-archive'],
        category: ['Category', 'dashicons-category'],
        car: ['Vehicle', 'dashicons-car'],
        code: ['Code', 'dashicons-editor-code'],
        ordered_list: ['Numbered list', 'dashicons-editor-ol'],
        table: ['Table', 'dashicons-editor-table'],
        list: ['List', 'dashicons-editor-ul'],
        external: ['External link', 'dashicons-external'],
        marker: ['Map pin', 'dashicons-marker'],
        spreadsheet: ['Spreadsheet', 'dashicons-media-spreadsheet'],
        products: ['Products', 'dashicons-products'],
        api: ['API', 'dashicons-rest-api'],
        schedule: ['Schedule', 'dashicons-schedule'],
        share: ['Share', 'dashicons-share'],
        support: ['Support', 'dashicons-sos'],
        language: ['Language', 'dashicons-translation'],
        accessibility: ['Accessibility', 'dashicons-universal-access'],
        visible: ['Visible', 'dashicons-visibility'],
        whatsapp: ['WhatsApp', 'dashicons-whatsapp'],
        youtube: ['YouTube', 'dashicons-youtube'],
        community: ['Community', 'dashicons-buddicons-community'],
        activity: ['Activity', 'dashicons-buddicons-activity'],
        private_message: ['Private message', 'dashicons-buddicons-pm']
    };
    let schema = [];
    let activeStage = 0;
    let selectedId = null;
    let selectedChildIndex = null;
    let dirty = false;
    let richTextEditorFieldId = null;
    let previewStage = 0;
    let history = [];
    let historyIndex = -1;
    let historyTimer = null;
    let historyApplying = false;
    const proFieldTypes = WebformAdmin.proFieldTypes || [];
    const containerChildTypes = {
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
        consent: 'Consent',
        poll: 'Poll',
        quiz: 'Quiz',
        rating: 'Rating',
        slider: 'Slider',
        hidden: 'Hidden',
        html: 'HTML content',
        heading: 'Heading',
        rich_text: 'Rich text',
        divider: 'Divider',
        signature: 'E-signature',
        appointment: 'Appointment',
        nps: 'NPS score',
        currency: 'Currency',
        product: 'Product selector',
        price: 'Price',
        calculation: 'Calculation'
    };

    function isLockedProField(field) {
        return !WebformAdmin.proActive && proFieldTypes.includes(field.type);
    }

    function previewWidth(value) {
        return ({ 'auto': 'auto', '100': '100%', '75': '74%', '50': '49%', '33': '32%' })[String(value || '100')] || '100%';
    }

    function uid(prefix) {
        return prefix + '_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
    }

    function editorSnapshot() {
        syncRichTextEditor();
        const controls = {};
        $('.webform-property-panel input[id],.webform-property-panel select[id],.webform-property-panel textarea[id]').each(function () {
            if (this.id === 'webform-rich-text-content') return;
            controls[this.id] = this.type === 'checkbox' || this.type === 'radio' ? !!this.checked : $(this).val();
        });
        return {
            schema: JSON.parse(JSON.stringify(schema)),
            activeStage,
            selectedId,
            selectedChildIndex,
            name: $('#webform-name').val(),
            controls
        };
    }

    function updateHistoryButtons() {
        $('#webform-undo').prop('disabled', historyIndex <= 0);
        $('#webform-redo').prop('disabled', historyIndex < 0 || historyIndex >= history.length - 1);
    }

    function pushHistory() {
        if (historyApplying || !$('#webform-canvas').length) return;
        const snapshot = editorSnapshot();
        const serialized = JSON.stringify(snapshot);
        if (historyIndex >= 0 && history[historyIndex].serialized === serialized) {
            updateHistoryButtons();
            return;
        }
        history = history.slice(0, historyIndex + 1);
        history.push({ serialized, snapshot });
        if (history.length > 60) history.shift();
        historyIndex = history.length - 1;
        updateHistoryButtons();
    }

    function scheduleHistory(delay = 180) {
        if (historyApplying) return;
        window.clearTimeout(historyTimer);
        historyTimer = window.setTimeout(pushHistory, delay);
    }

    function applyHistory(index) {
        if (!history[index]) return;
        historyApplying = true;
        removeRichTextEditor();
        const snapshot = JSON.parse(JSON.stringify(history[index].snapshot));
        schema = snapshot.schema;
        activeStage = Math.min(snapshot.activeStage, schema.length - 1);
        selectedId = snapshot.selectedId;
        selectedChildIndex = Number.isInteger(snapshot.selectedChildIndex) ? snapshot.selectedChildIndex : null;
        $('#webform-name').val(snapshot.name);
        render();
        Object.entries(snapshot.controls || {}).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (!element) return;
            if (element.type === 'checkbox' || element.type === 'radio') element.checked = !!value;
            else $(element).val(value);
        });
        updateConfirmationOptions();
        updateRecaptchaPanels();
        $('#webform-role-controls').toggleClass('is-disabled', !$('#webform-require-login').is(':checked')).find('input[type="checkbox"]').prop('disabled', !$('#webform-require-login').is(':checked'));
        $('#webform-user-notification-options').toggleClass('is-disabled', !$('#webform-user-notification-enabled').is(':checked'));
        updatePresetPreview();
        historyIndex = index;
        historyApplying = false;
        dirty = true;
        updateHistoryButtons();
        if (!$('#webform-preset-preview-modal').prop('hidden')) renderRealPreview();
    }

    function load() {
        try { schema = JSON.parse($('#webform-schema').val() || '[]'); } catch (e) { schema = []; }
        if (!Array.isArray(schema) || !schema.length) schema = [{ id: uid('stage'), title: 'Stage 1', fields: [] }];
        render();
        renderFreeProCatalog();
        if (WebformAdmin.proActive) ensureIconGallery();
        history = [];
        historyIndex = -1;
        pushHistory();
    }

    function renderFreeProCatalog() {
        if (WebformAdmin.proInstalled || WebformAdmin.proStyling || $('.webform-free-pro-catalog').length) return;
        const fields = ['Calculation', 'Field group', 'E-signature', 'Rich text / contracts', 'Divider', 'Address', 'Repeater', 'Appointment', 'NPS score', 'Currency', 'Product selector', 'Price', 'Advanced upload'];
        const integrations = {
            'Email marketing & CRM': ['Mailchimp', 'Brevo', 'ActiveCampaign', 'Kit', 'GetResponse', 'LeadConnector / GoHighLevel'],
            'Payments': ['Stripe', 'PayPal', 'Square', 'Bank transfer'],
            'Automation & documents': ['Zapier', 'Webhooks', 'PDF notifications']
        };
        $('.webform-pro-field-preview-only .webform-pro-field-list').html(fields.map(name => `<div><span class="dashicons dashicons-lock"></span>${name}</div>`).join(''));
        const catalog = `<h2>Integrations</h2>
            <p class="description">Connect form submissions to email marketing, payments, automation, and document tools with FormOrbit Pro.</p>
            <div class="webform-free-pro-catalog">
                <span class="webform-pro-badge">FORMORBIT PRO</span>
                ${Object.entries(integrations).map(([category, names]) => `<h3>${category}</h3><div class="webform-pro-catalog-grid">${names.map(name => `<span><i class="dashicons dashicons-lock"></i>${name}</span>`).join('')}</div>`).join('')}
                <a class="button button-primary webform-pro-catalog-button" href="https://www.webninjallc.com/plugins/formorbit/?utm_source=formorbit-free&amp;utm_medium=builder&amp;utm_campaign=integrations" target="_blank" rel="noopener">Explore FormOrbit Pro</a>
            </div>`;
        $('.webform-property-panel[data-panel="integrations"]').html(catalog);
    }

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : value).html();
    }

    function createContainerChild(type, label) {
        const childType = containerChildTypes[type] ? type : 'text';
        const choices = ['select', 'radio', 'checkbox', 'poll', 'quiz', 'product'].includes(childType);
        return {
            id: uid('child'),
            type: childType,
            label: label || containerChildTypes[childType],
            placeholder: '',
            required: false,
            options: choices ? ['Option 1', 'Option 2'] : [],
            choice_columns: 1,
            child_width: '100',
            rows: 4,
            min: '',
            max: '',
            step: '1',
            default_value: '',
            html: childType === 'html' ? '<p>Add your content here.</p>' : '',
            rich_content: childType === 'rich_text' ? '<p>Add your formatted content here.</p>' : '',
            currency_symbol: '$',
            currency_code: 'USD',
            price_amount: 0,
            formula: ''
        };
    }

    function fieldToContainerChildren(source) {
        if (!source) return [];
        if (source.type === 'name') {
            return [createContainerChild('text', 'First name'), createContainerChild('text', 'Last name')];
        }
        if (source.type === 'address') {
            return ['Street address', 'City', 'State / Province', 'Postal code', 'Country'].map(label => createContainerChild('text', label));
        }
        if (!containerChildTypes[source.type]) return [];
        const child = JSON.parse(JSON.stringify(source));
        child.id = uid('child');
        child.child_width = ({'25':'25','33':'33','50':'50','66':'66','75':'75','100':'100'})[String(source.style?.width || '')] || '100';
        delete child.children;
        delete child.condition;
        delete child.row_start;
        delete child.style;
        return [child];
    }

    function defaultContainerChildren(type) {
        return type === 'repeater'
            ? [createContainerChild('text', 'Item'), createContainerChild('number', 'Quantity')]
            : [createContainerChild('text', 'First name'), createContainerChild('email', 'Email')];
    }

    function containerChildren(field) {
        return Array.isArray(field.children) ? field.children : [];
    }

    function containerFromElement(element) {
        const id = String($(element).closest('.webform-field-card').data('id') || $(element).closest('.webform-live-container').data('container-id') || '');
        return (schema[activeStage].fields || []).find(field => String(field.id) === id);
    }

    function containerChildPreview(child, index, editable) {
        const label = escapeHtml(child.label || containerChildTypes[child.type] || 'Field');
        const required = child.required ? '<em>*</em>' : '';
        let control = '<i class="webform-container-preview-input"></i>';
        if (child.type === 'textarea') control = '<i class="webform-container-preview-input is-textarea"></i>';
        if (child.type === 'select') control = '<i class="webform-container-preview-input is-select"></i>';
        if (['radio', 'checkbox', 'consent', 'poll', 'quiz', 'product'].includes(child.type)) control = `<i class="webform-container-preview-options">${(child.options || ['Option 1', 'Option 2']).slice(0, 2).map(option => `<span>○ ${escapeHtml(String(option).split('|')[0])}</span>`).join('') || '<span>□ Consent</span>'}</i>`;
        if (child.type === 'rating') control = '<i class="webform-container-preview-rating">★★★★★</i>';
        if (child.type === 'slider') control = '<i class="webform-container-preview-slider"><span></span></i>';
        if (['heading', 'html', 'rich_text'].includes(child.type)) control = `<i class="webform-container-preview-content">${child.type === 'heading' ? 'Section heading' : 'Formatted content'}</i>`;
        if (child.type === 'divider') control = '<i class="webform-container-preview-divider"></i>';
        if (child.type === 'signature') control = '<i class="webform-container-preview-signature">Sign here</i>';
        if (child.type === 'nps') control = '<i class="webform-container-preview-nps">0 · 1 · 2 · 3 · 4 · 5 · 6 · 7 · 8 · 9 · 10</i>';
        if (['price', 'calculation'].includes(child.type)) control = `<i class="webform-container-preview-value">${child.type === 'price' ? escapeHtml(child.currency_code || 'USD') + ' ' + Number(child.price_amount || 0).toFixed(2) : '0.00'}</i>`;
        if (child.type === 'hidden') control = '<i class="webform-container-preview-hidden">Hidden value</i>';
        const selected = editable && selectedChildIndex === index;
        return `<span class="webform-container-preview-child ${editable ? 'is-editable' : ''} ${selected ? 'is-child-selected' : ''}" data-child-index="${index}" role="button" tabindex="${editable ? '0' : '-1'}" aria-label="Edit ${label}" style="--webform-child-width:${Math.max(25, Math.min(100, Number(child.child_width || 100)))}%">
            ${editable ? '<span class="dashicons dashicons-menu webform-live-child-drag" title="Drag to reorder"></span>' : ''}
            ${editable ? `<input class="webform-live-child-label" value="${label}" aria-label="Child field label">` : `<small>${label} ${required}</small>`}
            ${control}
            ${editable ? `<span class="webform-live-child-actions"><button type="button" class="webform-live-child-width" title="Change child width" aria-label="Change ${label} width">${escapeHtml(child.child_width || '100')}%</button><button type="button" class="webform-live-eject-child" title="Drag or click to move to the main form" aria-label="Move ${label} to main form"><span class="dashicons dashicons-external"></span></button><button type="button" class="webform-live-remove-child" title="Delete child field" aria-label="Delete ${label}"><span class="dashicons dashicons-trash"></span></button></span>` : ''}
        </span>`;
    }

    function containerPreview(field) {
        const children = containerChildren(field);
        if (!children.length) {
            if (field.type === 'field_group') {
                return `<div class="webform-preview-group is-legacy" style="--preview-columns:${Math.max(1, Math.min(4, Number(field.group_columns || 2)))}">${Array.from({ length: Math.max(1, Math.min(6, Number(field.group_count || 2))) }, (_, index) => `<span><small>Legacy grouped field ${index + 1}</small><i></i></span>`).join('')}</div>`;
            }
            return '<div class="webform-preview-repeater is-legacy"><div><i></i><button type="button">×</button></div><small>Legacy single-value row</small></div>';
        }
        const columns = field.type === 'field_group' ? Math.max(1, Math.min(4, Number(field.group_columns || 2))) : Math.min(2, Math.max(1, children.length));
        const editable = WebformAdmin.proActive && field.id === selectedId;
        return `<div class="webform-preview-container webform-live-container webform-preview-container-${escapeHtml(field.type)} ${editable ? 'is-editable' : ''}" data-container-id="${escapeHtml(field.id)}" style="--preview-columns:${columns}">
            ${children.map((child, index) => containerChildPreview(child, index, editable)).join('')}
            ${editable ? '<button type="button" class="webform-live-container-drop"><span class="dashicons dashicons-move"></span><strong>Drop a field here</strong><small>or add a basic field</small></button>' : ''}
            ${field.type === 'repeater' ? `<span class="webform-container-preview-add">＋ ${escapeHtml(field.repeater_button || 'Add another row')}</span>` : ''}
        </div>`;
    }

    function initializeLiveContainers() {
        if (!WebformAdmin.proActive) return;
        $('.webform-live-container').each(function () {
            const container = $(this);
            const parentId = String(container.data('container-id') || '');
            const parent = (schema[activeStage].fields || []).find(field => field.id === parentId);
            if (!parent) return;
            if (container.hasClass('is-editable')) {
                container.sortable({
                    items: '.webform-container-preview-child',
                    handle: '.webform-live-child-drag',
                    placeholder: 'webform-live-child-placeholder',
                    tolerance: 'pointer',
                    distance: 3,
                    update: function () {
                        const current = containerChildren(parent);
                        const order = container.children('.webform-container-preview-child').map(function () { return Number($(this).data('child-index')); }).get();
                        const selectedChild = Number.isInteger(selectedChildIndex) ? current[selectedChildIndex] : null;
                        parent.children = order.map(index => current[index]).filter(Boolean);
                        selectedChildIndex = selectedChild ? parent.children.indexOf(selectedChild) : null;
                        dirty = true;
                        scheduleHistory(0);
                        render();
                    }
                });
            }
            if ($.fn.droppable) {
                container.droppable({
                    accept: function (draggable) {
                        const source = (schema[activeStage].fields || []).find(field => field.id === draggable.data('id'));
                        return source && source.id !== parent.id;
                    },
                    tolerance: 'pointer',
                    hoverClass: 'is-field-over',
                    drop: function (event, ui) {
                        ui.draggable.data('formorbit-container-drop', true);
                        const sourceId = String(ui.draggable.data('id') || '');
                        const fields = schema[activeStage].fields || [];
                        const source = fields.find(field => field.id === sourceId);
                        const additions = fieldToContainerChildren(source);
                        if (!source || !additions.length) {
                            const message = ['file', 'captcha'].includes(source?.type) ? 'Uploads and CAPTCHA stay top-level for secure processing.' : 'Groups, repeaters, and page breaks cannot be nested.';
                            container.addClass('is-drop-rejected').find('.webform-live-container-drop strong').text(message);
                            window.setTimeout(function () { container.removeClass('is-drop-rejected'); render(); }, 1800);
                            return;
                        }
                        if (containerChildren(parent).length + additions.length > 20) {
                            container.addClass('is-drop-rejected').find('.webform-live-container-drop strong').text('A container can hold up to 20 fields.');
                            window.setTimeout(function () { container.removeClass('is-drop-rejected'); render(); }, 1800);
                            return;
                        }
                        parent.children = containerChildren(parent).concat(additions);
                        schema[activeStage].fields = fields.filter(field => field.id !== sourceId);
                        selectedId = parent.id;
                        selectedChildIndex = parent.children.length - additions.length;
                        dirty = true;
                        scheduleHistory(0);
                        render();
                    }
                });
            }
        });
        if ($.fn.draggable && $.fn.droppable) {
            $('.webform-live-eject-child').draggable({
                appendTo: 'body',
                cursor: 'grabbing',
                cursorAt: { left: 15, top: 15 },
                distance: 4,
                helper: function () {
                    const child = $(this).closest('.webform-container-preview-child');
                    return child.clone().addClass('webform-child-drag-helper').css({
                        height: child.outerHeight(),
                        width: Math.min(320, child.outerWidth())
                    });
                },
                revert: 'invalid',
                start: function () {
                    $('#webform-canvas').addClass('is-child-drag-target');
                },
                stop: function () {
                    $('#webform-canvas').removeClass('is-child-drag-target');
                }
            });
            $('#webform-canvas').droppable({
                accept: '.webform-live-eject-child',
                greedy: true,
                tolerance: 'pointer',
                hoverClass: 'is-child-drag-over',
                drop: function (event, ui) {
                    const parent = containerFromElement(ui.draggable);
                    if (!parent) return;
                    const index = Number(ui.draggable.closest('[data-child-index]').data('child-index'));
                    moveContainerChildOut(parent, index);
                }
            });
        }
    }

    function childEditorMarkup(field, index) {
        const children = containerChildren(field);
        const child = children[index];
        if (!child) return '';
        const typeOptions = Object.entries(containerChildTypes).map(([value, label]) => `<option value="${value}" ${child && child.type === value ? 'selected' : ''}>${label}</option>`).join('');
        const choices = ['select', 'radio', 'checkbox', 'poll', 'quiz', 'product'].includes(child.type);
        return `<div class="webform-child-editor webform-container-child" data-child-index="${index}">
            <div class="webform-child-editor-heading">
                <div><span>CHILD FIELD ${index + 1} OF ${children.length}</span><h4>${escapeHtml(child.label || containerChildTypes[child.type] || 'Field')}</h4><p>${escapeHtml(field.label || (field.type === 'repeater' ? 'Repeater' : 'Field group'))}</p></div>
            </div>
            <div class="webform-child-editor-grid">
                <label>Field label<input type="text" data-child-prop="label" value="${escapeHtml(child.label || '')}" placeholder="Field label"></label>
                <label>Field type<select data-child-prop="type">${typeOptions}</select></label>
            </div>
            ${!['radio','checkbox','select','consent','hidden','poll','quiz','product','html','heading','rich_text','divider','price'].includes(child.type) ? `<label>Placeholder<input type="text" data-child-prop="placeholder" value="${escapeHtml(child.placeholder || '')}" placeholder="Optional helper text"></label>` : ''}
            ${choices ? `<label>Options <small>One per line</small><textarea rows="6" data-child-prop="options">${escapeHtml((child.options || []).join('\n'))}</textarea></label>` : ''}
            ${['radio','checkbox','poll','quiz'].includes(child.type) ? `<label>Option columns<select data-child-prop="choice_columns">${[1,2,3,4].map(column => `<option value="${column}" ${Number(child.choice_columns || 1) === column ? 'selected' : ''}>${column}</option>`).join('')}</select></label>` : ''}
            <label>Width inside group<select data-child-prop="child_width">${[['25','25%'],['33','33%'],['50','50%'],['66','66%'],['75','75%'],['100','100%']].map(option => `<option value="${option[0]}" ${String(child.child_width || '100') === option[0] ? 'selected' : ''}>${option[1]}</option>`).join('')}</select></label>
            ${child.type === 'textarea' ? `<label>Visible rows<input type="number" min="2" max="30" data-child-prop="rows" value="${Number(child.rows || 4)}"></label>` : ''}
            ${['number','slider','currency'].includes(child.type) ? `<div class="webform-child-editor-grid is-three-columns"><label>Minimum<input type="number" data-child-prop="min" value="${escapeHtml(child.min ?? '')}"></label><label>Maximum<input type="number" data-child-prop="max" value="${escapeHtml(child.max ?? '')}"></label><label>Step<input type="number" step="any" data-child-prop="step" value="${escapeHtml(child.step || '1')}"></label></div>` : ''}
            ${child.type === 'hidden' ? `<label>Default value<input type="text" data-child-prop="default_value" value="${escapeHtml(child.default_value || '')}"></label>` : ''}
            ${child.type === 'html' ? `<label>Safe HTML<textarea rows="8" data-child-prop="html">${escapeHtml(child.html || '')}</textarea></label>` : ''}
            ${child.type === 'rich_text' ? `<label>Formatted content<textarea rows="10" data-child-prop="rich_content">${escapeHtml(child.rich_content || '')}</textarea></label>` : ''}
            ${child.type === 'price' ? `<div class="webform-child-editor-grid"><label>Amount<input type="number" min="0" step="0.01" data-child-prop="price_amount" value="${Number(child.price_amount || 0)}"></label><label>Currency<input type="text" maxlength="3" data-child-prop="currency_code" value="${escapeHtml(child.currency_code || 'USD')}"></label></div>` : ''}
            ${child.type === 'currency' ? `<label>Currency symbol<input type="text" maxlength="5" data-child-prop="currency_symbol" value="${escapeHtml(child.currency_symbol || '$')}"></label>` : ''}
            ${child.type === 'calculation' ? `<label>Formula<input type="text" data-child-prop="formula" value="${escapeHtml(child.formula || '')}"></label>` : ''}
            ${!['heading','hidden','html','rich_text','divider','price','calculation'].includes(child.type) ? `<label class="webform-check"><input type="checkbox" data-child-prop="required" ${child.required ? 'checked' : ''}> Required field</label>` : ''}
            <div class="webform-child-editor-actions">
                <button type="button" class="button webform-child-move-out"><span class="dashicons dashicons-external"></span>Move to main form</button>
                <button type="button" class="button-link-delete webform-remove-container-child">Remove child field</button>
            </div>
        </div>`;
    }

    function ensureChildEditorModal() {
        if ($('#webform-child-editor-modal').length) return;
        $('body').append(`<section id="webform-child-editor-modal" class="webform-child-editor-modal" hidden aria-hidden="true">
            <button type="button" class="webform-child-editor-backdrop" aria-label="Close child field editor"></button>
            <div class="webform-child-editor-dialog" role="dialog" aria-modal="true" aria-labelledby="webform-child-editor-title">
                <header class="webform-child-editor-modal-head">
                    <div><span class="dashicons dashicons-edit" aria-hidden="true"></span><div><small>GROUP / REPEATER FIELD</small><h2 id="webform-child-editor-title">Edit child field</h2></div></div>
                    <button type="button" class="webform-child-editor-close" aria-label="Close child field editor">×</button>
                </header>
                <div class="webform-child-editor-modal-body"></div>
                <footer class="webform-child-editor-modal-footer"><span>Changes appear instantly in the live builder.</span><button type="button" class="button button-primary webform-child-editor-done">Done</button></footer>
            </div>
        </section>`);
    }

    function refreshChildEditorModal() {
        const field = selectedField();
        const modal = $('#webform-child-editor-modal');
        if (!field || selectedChildIndex === null || !containerChildren(field)[selectedChildIndex] || !modal.length) return;
        const child = containerChildren(field)[selectedChildIndex];
        modal.find('#webform-child-editor-title').text(`Edit ${child.label || containerChildTypes[child.type] || 'child field'}`);
        modal.find('.webform-child-editor-modal-body').html(childEditorMarkup(field, selectedChildIndex));
    }

    function openChildEditor(field, index) {
        if (!field || !containerChildren(field)[index]) return;
        selectedId = field.id;
        selectedChildIndex = index;
        render();
        ensureChildEditorModal();
        refreshChildEditorModal();
        $('#webform-child-editor-modal').removeAttr('hidden').attr('aria-hidden', 'false');
        $('body').addClass('webform-child-editor-open');
        $('.webform-property-tabs button[data-panel="field"]').trigger('click');
        window.setTimeout(function () { $('#webform-child-editor-modal [data-child-prop="label"]').trigger('focus'); }, 0);
    }

    function closeChildEditor() {
        $('#webform-child-editor-modal').attr('hidden', true).attr('aria-hidden', 'true');
        $('body').removeClass('webform-child-editor-open');
        $(`.webform-select-child[data-child-index="${selectedChildIndex}"]`).trigger('focus');
    }

    $(document).on('keydown', function (event) {
        const modal = document.getElementById('webform-child-editor-modal');
        if (event.key !== 'Tab' || !modal || modal.hidden) return;
        const focusable = Array.from(modal.querySelectorAll('button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[href],[tabindex]:not([tabindex="-1"])')).filter(element => element.offsetParent !== null);
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    function containerChildrenSettings(field) {
        const children = containerChildren(field);
        const legacy = !children.length;
        if (selectedChildIndex !== null && !children[selectedChildIndex]) selectedChildIndex = null;
        return `<div class="webform-container-field-settings">
            <div class="webform-container-field-heading"><div><h3>${field.type === 'repeater' ? 'Fields in each repeated row' : 'Fields inside this group'}</h3><p>Choose a field below or click it directly on the live form.</p></div>${children.length < 20 ? '<button type="button" class="button webform-add-container-child"><span class="dashicons dashicons-plus-alt2"></span>Add field</button>' : ''}</div>
            ${legacy ? `<div class="webform-container-legacy"><strong>Legacy ${field.type === 'repeater' ? 'single-field repeater' : 'field group'}</strong><p>Convert this container to edit multiple child fields.</p><button type="button" class="button button-primary webform-convert-container">Convert container</button></div>` : `<div class="webform-child-navigator" role="list" aria-label="Child fields">${children.map((item, index) => `<button type="button" class="webform-select-child ${selectedChildIndex === index ? 'is-active' : ''}" data-child-index="${index}" role="listitem" aria-haspopup="dialog"><span class="dashicons dashicons-${item.type === 'textarea' ? 'text' : item.type === 'email' ? 'email' : 'edit'}"></span><span><strong>${escapeHtml(item.label || containerChildTypes[item.type] || 'Field')}</strong><small>${escapeHtml(containerChildTypes[item.type] || item.type)}</small></span><span class="dashicons dashicons-arrow-right-alt2"></span></button>`).join('')}</div><p class="webform-container-builder-tip"><span class="dashicons dashicons-move"></span>Drag compatible fields from the main canvas into this container. Click any child field to edit it in a larger workspace.</p>`}
        </div>`;
    }

    function updateEmbedPanel(formId, shortcode) {
        const id = Number(formId || 0);
        if (!id) return;
        const embedShortcode = shortcode || `[formorbit id="${id}"]`;
        const phpEmbed = `<?php echo do_shortcode( '${embedShortcode}' ); ?>`;
        $('#webform-editor-shortcode').text(embedShortcode);
        $('#webform-editor-php').text(phpEmbed);
        $('#webform-open-embed').prop('hidden', false);
    }

    function copyToClipboard(button, text) {
        const value = String(text || '');
        if (!value) return;
        const label = $(button).find('.webform-copy-label');
        const originalLabel = label.text() || 'Copy';
        const originalTitle = button.getAttribute('title') || 'Copy';
        const originalAria = button.getAttribute('aria-label') || originalTitle;
        const copied = function () {
            $(button).addClass('is-copied').attr('title', 'Copied').attr('aria-label', 'Copied');
            if (label.length) label.text('Copied');
            window.setTimeout(function () {
                $(button).removeClass('is-copied').attr('title', originalTitle).attr('aria-label', originalAria);
                if (label.length) label.text(originalLabel);
            }, 1600);
        };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(value).then(copied).catch(function () {});
            return;
        }
        const input = document.createElement('textarea');
        input.value = value;
        input.setAttribute('readonly', '');
        input.style.position = 'fixed';
        input.style.opacity = '0';
        document.body.appendChild(input);
        input.select();
        try { document.execCommand('copy'); copied(); } catch (error) {}
        input.remove();
    }

    function ensureIconGallery() {
        if ($('#webform-icon-gallery').length) return;
        const icons = Object.entries(fieldIcons).map(([value, icon]) => `<button type="button" data-icon="${escapeHtml(value)}" data-label="${escapeHtml(icon[0].toLowerCase())}"><span class="dashicons ${escapeHtml(icon[1])}" aria-hidden="true"></span><span>${escapeHtml(icon[0])}</span></button>`).join('');
        $('body').append(`<div id="webform-icon-gallery" class="webform-icon-gallery" aria-hidden="true">
            <button type="button" class="webform-icon-gallery-backdrop" tabindex="-1" aria-label="Close icon gallery"></button>
            <div class="webform-icon-gallery-dialog" role="dialog" aria-modal="true" aria-labelledby="webform-icon-gallery-title">
                <div class="webform-icon-gallery-head"><div><h2 id="webform-icon-gallery-title">Choose a field icon</h2><p>Icons appear beside the field label.</p></div><button type="button" class="webform-icon-gallery-close" aria-label="Close">×</button></div>
                <label class="webform-icon-gallery-search"><span class="dashicons dashicons-search" aria-hidden="true"></span><span class="screen-reader-text">Search icons</span><input type="search" placeholder="Search icons…" autocomplete="off"></label>
                <div class="webform-icon-gallery-grid">${icons}</div>
                <p class="webform-icon-gallery-empty" hidden>No matching icons.</p>
            </div>
        </div>`);
    }

    function closeIconGallery() {
        $('#webform-icon-gallery').removeClass('is-open').attr('aria-hidden', 'true');
    }

    function safeRichPreview(value) {
        const container = document.createElement('div');
        container.innerHTML = String(value || '');
        container.querySelectorAll('script,style,iframe,object,embed,form,input,button').forEach(node => node.remove());
        container.querySelectorAll('*').forEach(node => Array.from(node.attributes).forEach(attribute => {
            if (/^on/i.test(attribute.name) || /^(?:javascript|data):/i.test(attribute.value)) node.removeAttribute(attribute.name);
        }));
        return container.innerHTML;
    }

    function realPreviewChild(child) {
        const label = escapeHtml(child.label || containerChildTypes[child.type] || 'Field');
        const required = child.required ? ' <em>*</em>' : '';
        const placeholder = escapeHtml(child.placeholder || '');
        if (child.type === 'hidden') return '';
        if (child.type === 'textarea') return `<label class="webform-container-child-field"><span>${label}${required}</span><textarea rows="${Number(child.rows || 4)}" placeholder="${placeholder}" readonly></textarea></label>`;
        if (child.type === 'select') return `<label class="webform-container-child-field"><span>${label}${required}</span><select disabled><option>${escapeHtml((child.options || [])[0] || 'Select an option')}</option></select></label>`;
        if (['radio', 'checkbox', 'poll', 'quiz', 'product'].includes(child.type)) return `<fieldset class="webform-container-child-field"><legend>${label}${required}</legend><div class="webform-container-choices">${(child.options || ['Option 1', 'Option 2']).map((option, index) => `<label><input type="${child.type === 'checkbox' ? 'checkbox' : 'radio'}" ${index === 0 ? 'checked' : ''} disabled> <span>${escapeHtml(String(option).replace('|', ' — '))}</span></label>`).join('')}</div></fieldset>`;
        if (child.type === 'consent') return `<label class="webform-container-child-field webform-container-consent"><input type="checkbox" disabled><span>${label}${required}</span></label>`;
        if (child.type === 'heading') return `<div class="webform-container-child-field"><h3>${label}</h3></div>`;
        if (child.type === 'html') return `<div class="webform-container-child-field">${safeRichPreview(child.html || '')}</div>`;
        if (child.type === 'rich_text') return `<div class="webform-container-child-field">${safeRichPreview(child.rich_content || '')}</div>`;
        if (child.type === 'divider') return '<div class="webform-container-child-field"><hr></div>';
        if (child.type === 'signature') return `<fieldset class="webform-container-child-field"><legend>${label}${required}</legend><div class="webform-real-signature"><span>Sign here</span></div></fieldset>`;
        if (['rating', 'nps'].includes(child.type)) return `<fieldset class="webform-container-child-field"><legend>${label}${required}</legend><div class="webform-container-choices">${Array.from({length:child.type === 'rating' ? 5 : 11},(_,index)=>`<label><input type="radio" disabled><span>${child.type === 'rating' ? '★' : index}</span></label>`).join('')}</div></fieldset>`;
        if (child.type === 'price') return `<div class="webform-container-child-field webform-field-price"><span>${label}</span><strong>${escapeHtml(child.currency_code || 'USD')} ${Number(child.price_amount || 0).toFixed(2)}</strong></div>`;
        const type = ({email:'email',number:'number',date:'date',time:'time',phone:'tel',url:'url',slider:'range',appointment:'datetime-local',currency:'number',calculation:'number'})[child.type] || 'text';
        return `<label class="webform-container-child-field"><span>${label}${required}</span><input type="${type}" placeholder="${placeholder}" readonly></label>`;
    }

    function realPreviewField(field) {
        if (isLockedProField(field)) return '';
        const label = escapeHtml(field.label || defaults[field.type] || 'Field');
        const labelHtml = `${field.icon && fieldIcons[field.icon] ? `<span class="dashicons ${escapeHtml(fieldIcons[field.icon][1])} webform-field-icon" aria-hidden="true"></span>` : ''}${label}${field.required ? ' <em>*</em>' : ''}`;
        const hiddenLabel = field.hide_label ? ' class="screen-reader-text"' : '';
        const placeholder = escapeHtml(field.placeholder || '');
        const choices = (field.options || ['Option 1', 'Option 2']).map((option, index) => `<label><input type="${field.type === 'checkbox' ? 'checkbox' : 'radio'}" ${index === 0 ? 'checked' : ''} disabled> ${escapeHtml(String(option).split('|')[0])}</label>`).join('');
        const style = field.style || {};
        const width = ({auto:'calc(50% - 1%)','100':'100%','75':'74%','50':'49%','33':'32%'})[String(style.width || '100')] || '100%';
        const wrapperStyle = `width:${width};${style.customized ? `--preview-label:${escapeHtml(style.label_color || '')};--preview-bg:${escapeHtml(style.background_color || '')};--preview-text:${escapeHtml(style.text_color || '')};--preview-radius:${Number(style.radius || 0)}px;` : ''}`;
        const start = field.row_start ? '<span class="webform-row-break" aria-hidden="true"></span>' : '';
        let markup = '';
        if (field.type === 'hidden') return '';
        if (field.type === 'html') markup = `<div class="webform-html">${safeRichPreview(field.html || '')}</div>`;
        else if (field.type === 'heading') markup = `<h3 class="webform-heading">${labelHtml}</h3>`;
        else if (field.type === 'rich_text') markup = `<div class="webform-rich-text-document"><div class="webform-rich-text-content">${safeRichPreview(field.rich_content || '')}</div></div>`;
        else if (field.type === 'divider') {
            const lineStyle = ['solid','dashed','dotted','double'].includes(field.divider_style) ? field.divider_style : 'solid';
            const alignment = ['left','center','right'].includes(field.divider_alignment) ? field.divider_alignment : 'center';
            const labelPosition = field.divider_label_position === 'inline' ? 'inline' : 'above';
            const dividerLabel = field.divider_show_label ? `<span class="webform-divider-label">${labelHtml}</span>` : '';
            const ruleMargin = alignment === 'left' ? '0 auto 0 0' : alignment === 'right' ? '0 0 0 auto' : '0 auto';
            const rule = `<div class="webform-divider-rule" style="width:${Number(field.divider_width || 100)}%;margin:${ruleMargin}"><hr style="border-top:${Number(field.divider_thickness || 1)}px ${lineStyle} ${escapeHtml(field.divider_color || '#dfe1e6')}">${labelPosition === 'inline' ? dividerLabel : ''}${labelPosition === 'inline' && field.divider_show_label ? `<hr style="border-top:${Number(field.divider_thickness || 1)}px ${lineStyle} ${escapeHtml(field.divider_color || '#dfe1e6')}">` : ''}</div>`;
            markup = `<div class="webform-divider webform-real-preview-divider webform-divider-label-${labelPosition}" style="margin-top:${Number(field.divider_margin_top || 0)}px;margin-bottom:${Number(field.divider_margin_bottom || 0)}px;padding-top:${Number(field.divider_padding_top || 0)}px;padding-bottom:${Number(field.divider_padding_bottom || 0)}px">${labelPosition === 'above' ? dividerLabel : ''}${rule}</div>`;
        }
        else if (field.type === 'textarea') markup = `<div class="webform-field"><label${hiddenLabel}>${labelHtml}</label><textarea rows="${Number(field.rows || 5)}" placeholder="${placeholder}" readonly></textarea></div>`;
        else if (field.type === 'select') markup = `<div class="webform-field"><label${hiddenLabel}>${labelHtml}</label><select disabled><option>${escapeHtml((field.options || [])[0] || 'Select an option')}</option></select></div>`;
        else if (['radio','checkbox','poll','quiz'].includes(field.type)) markup = `<fieldset class="webform-field"><legend${hiddenLabel}>${labelHtml}</legend><div class="webform-choices webform-choice-columns-${Number(field.choice_columns || 1)}">${choices}</div></fieldset>`;
        else if (field.type === 'consent') markup = `<div class="webform-field webform-field-consent"><label><input type="checkbox" disabled> ${labelHtml}</label></div>`;
        else if (field.type === 'name') markup = `<fieldset class="webform-field"><legend${hiddenLabel}>${labelHtml}</legend><div class="webform-name-fields"><label><span>First name</span><input readonly></label><label><span>Last name</span><input readonly></label></div></fieldset>`;
        else if (field.type === 'file') markup = `<div class="webform-field"><label${hiddenLabel}>${labelHtml}</label><input type="file" disabled><small>${escapeHtml(field.allowed_extensions || '')}</small></div>`;
        else if (field.type === 'rating') markup = `<fieldset class="webform-field"><legend${hiddenLabel}>${labelHtml}</legend><div class="webform-rating">${[5,4,3,2,1].map(value => `<label>★</label>`).join('')}</div></fieldset>`;
        else if (field.type === 'slider') markup = `<div class="webform-field"><label${hiddenLabel}>${labelHtml}</label><input type="range" min="${Number(field.min || 0)}" max="${Number(field.max || 100)}" disabled></div>`;
        else if (field.type === 'captcha') markup = `<div class="webform-field"><label${hiddenLabel}>${labelHtml}</label><div class="webform-preview-captcha"><i class="check"></i><span>I’m not a robot</span><span class="webform-preview-recaptcha"><span class="dashicons dashicons-update"></span><small>reCAPTCHA</small></span></div></div>`;
        else if (field.type === 'signature') markup = `<fieldset class="webform-field webform-field-signature"><legend${hiddenLabel}>${labelHtml}</legend><div class="webform-real-signature"><span>Sign here</span></div><button type="button" disabled>Clear signature</button></fieldset>`;
        else if (field.type === 'address') markup = `<fieldset class="webform-field webform-field-address"><legend${hiddenLabel}>${labelHtml}</legend><div class="webform-address-grid">${['Street address','City','State / Province','Postal code','Country'].map(name => `<label><span>${name}</span><input readonly></label>`).join('')}</div></fieldset>`;
        else if (field.type === 'field_group' && containerChildren(field).length) markup = `<fieldset class="webform-field webform-pro-field-group webform-pro-nested-group webform-group-columns-${Number(field.group_columns || 2)}"><legend${hiddenLabel}>${labelHtml}</legend><div class="webform-container-grid">${containerChildren(field).map(realPreviewChild).join('')}</div></fieldset>`;
        else if (field.type === 'repeater' && containerChildren(field).length) markup = `<fieldset class="webform-field webform-field-repeater webform-field-repeater-multi"><legend${hiddenLabel}>${labelHtml}</legend><div class="webform-repeater-rows"><div class="webform-repeater-row webform-repeater-multi-row"><div class="webform-repeater-row-head"><strong>Item 1</strong><button type="button" disabled>×</button></div><div class="webform-repeater-child-grid">${containerChildren(field).map(realPreviewChild).join('')}</div></div></div><button type="button" class="webform-repeater-add" disabled>${escapeHtml(field.repeater_button || 'Add another row')}</button></fieldset>`;
        else if (field.type === 'field_group') markup = `<fieldset class="webform-field webform-pro-field-group"><legend${hiddenLabel}>${labelHtml}</legend><p class="webform-preview-legacy-container">This legacy group will contain the next ${Math.max(1, Number(field.group_size || 2))} field${Number(field.group_size || 2) === 1 ? '' : 's'} on the published form.</p></fieldset>`;
        else if (field.type === 'repeater') markup = `<fieldset class="webform-field webform-field-repeater"><legend${hiddenLabel}>${labelHtml}</legend><div class="webform-repeater-rows"><div class="webform-repeater-row"><input type="text" placeholder="${placeholder}" readonly><button type="button" disabled>×</button></div></div><button type="button" class="webform-repeater-add" disabled>${escapeHtml(field.repeater_button || 'Add another row')}</button></fieldset>`;
        else if (field.type === 'page_break') markup = `<div class="webform-preview-page-break"><span></span><strong>Page break</strong><span></span></div>`;
        else if (field.type === 'nps') markup = `<fieldset class="webform-field webform-field-nps"><legend${hiddenLabel}>${labelHtml}</legend><div class="webform-nps-scale">${Array.from({length:11},(_,i)=>`<label><input type="radio" disabled><span>${i}</span></label>`).join('')}</div></fieldset>`;
        else if (field.type === 'product') markup = `<fieldset class="webform-field webform-field-product"><legend${hiddenLabel}>${labelHtml}</legend><div class="webform-product-options">${(field.options || []).map((option,index)=>{const parts=String(option).split('|');return `<label><input type="radio" ${index===0?'checked':''} disabled><span><strong>${escapeHtml(parts[0])}</strong><em>${escapeHtml(parts[1] || '0.00')}</em></span></label>`}).join('')}</div></fieldset>`;
        else if (field.type === 'price') markup = `<div class="webform-field webform-field-price"><span>${labelHtml}</span><strong>${escapeHtml(field.currency_code || 'USD')} ${Number(field.price_amount || 0).toFixed(2)}</strong></div>`;
        else if (field.type === 'calculation') markup = `<div class="webform-field webform-field-calculation"><label${hiddenLabel}>${labelHtml}</label><input value="${Number(0).toFixed(Number(field.decimal_places || 2))}" readonly></div>`;
        else {
            const type = ({email:'email',number:'number',date:'date',time:'time',phone:'tel',url:'url',appointment:'datetime-local',currency:'number'})[field.type] || 'text';
            markup = `<div class="webform-field webform-field-${escapeHtml(field.type)}"><label${hiddenLabel}>${labelHtml}</label><input type="${type}" placeholder="${placeholder}" readonly></div>`;
        }
        return `${start}<div class="webform-real-preview-field" data-field-id="${escapeHtml(field.id)}" style="${wrapperStyle}">${markup}</div>`;
    }

    function previewSettings() {
        const key = $('#webform-style-preset').val() || 'modern';
        const palette = presetPalettes[key] || presetPalettes.modern;
        return {
            key,
            name: $('#webform-style-preset option:selected').text().replace('🔒 ', ''),
            accent: $('#webform-accent-color').val() || palette.accent_color,
            buttonText: $('#webform-button-text-color').val() || palette.button_text_color,
            text: $('#webform-text-color').val() || palette.text_color,
            formBackground: $('#webform-form-background').val() || palette.form_background,
            fieldBackground: $('#webform-field-background').val() || palette.field_background,
            border: $('#webform-border-color').val() || palette.border_color,
            font: $('#webform-font-family').val() || 'inherit',
            fontSize: Number($('#webform-base-font-size').val() || 16),
            labelSize: Number($('#webform-label-font-size').val() || 14),
            maxWidth: Number($('#webform-form-max-width').val() || 720),
            spacing: Number($('#webform-field-spacing').val() || 20),
            fieldRadius: Number($('#webform-field-radius').val() || 7),
            buttonRadius: Number($('#webform-button-radius').val() || 7),
            buttonPadding: Number($('#webform-button-padding').val() || 11),
            submitAlignment: $('#webform-submit-alignment').val() || 'right',
            submitFontSize: Number($('#webform-submit-font-size').val() || 16),
            submitFontWeight: Number($('#webform-submit-font-weight').val() || 600),
            submitPaddingX: Number($('#webform-submit-padding-x').val() || 24),
            submitPaddingY: Number($('#webform-submit-padding-y').val() || 12),
            submitBorderWidth: Number($('#webform-submit-border-width').val() || 0),
            submitBackground: $('#webform-submit-background').val() || ($('#webform-accent-color').val() || palette.accent_color),
            submitHover: $('#webform-submit-hover-background').val() || '#5235b1',
            submitText: $('#webform-submit-text-color').val() || '#ffffff',
            submitBorder: $('#webform-submit-border-color').val() || ($('#webform-accent-color').val() || palette.accent_color)
        };
    }

    function previewCustomCss() {
        let css = String($('#webform-custom-css').val() || '').slice(0, 10000);
        css = css.replace(/\/\*[\s\S]*?\*\//g, '').replace(/@(?:import|charset|namespace)[^;]*;?/gi, '').replace(/url\s*\([^)]*\)/gi, '').replace(/(?:expression|javascript|vbscript|behavior|-moz-binding)\s*[:(]/gi, '').replace(/[<>]/g, '');
        let scoped = '';
        css.split('}').forEach(rule => {
            if (!rule.includes('{')) return;
            const parts = rule.split('{');
            const selectors = parts.shift().trim();
            const declarations = parts.join('{').trim();
            if (!selectors || !declarations || selectors.includes('@')) return;
            const safeSelectors = selectors.split(',').map(selector => selector.trim()).filter(selector => selector && !/(^|\s)(html|body|:root)(\s|$|[.#:\[])/i.test(selector)).map(selector => `#webform-real-preview ${selector}`);
            if (safeSelectors.length) scoped += `${safeSelectors.join(',')}{${declarations}}`;
        });
        let style = document.getElementById('webform-preview-custom-css');
        if (!style) {
            style = document.createElement('style');
            style.id = 'webform-preview-custom-css';
            document.getElementById('webform-preset-preview-modal')?.appendChild(style);
        }
        style.textContent = scoped;
    }

    function renderRealPreview() {
        syncRichTextEditor();
        const settings = previewSettings();
        previewStage = Math.min(previewStage, Math.max(0, schema.length - 1));
        const steps = schema.length > 1 ? `<div class="webform-progress"><div class="webform-progress-bar" style="width:${((previewStage + 1) / schema.length) * 100}%"></div></div><ol class="webform-steps">${schema.map((stage,index)=>`<li class="${index===previewStage?'is-active':index<previewStage?'is-complete':''}">${escapeHtml(stage.title)}</li>`).join('')}</ol>` : '';
        const stages = schema.map((stage,index)=>`<section class="webform-stage ${index===previewStage?'is-active':''}" ${index===previewStage?'':'hidden'}><h2>${escapeHtml(stage.title)}</h2>${(stage.fields || []).map(realPreviewField).join('') || '<p class="webform-preview-empty">This stage has no fields yet.</p>'}<div class="webform-actions">${index>0?'<button type="button" class="webform-preview-prev">Back</button>':''}${index<schema.length-1?'<button type="button" class="webform-preview-next">Continue</button>':`<button type="button" class="webform-submit">${escapeHtml($('#webform-submit-label').val() || 'Submit')}</button>`}</div></section>`).join('');
        $('#webform-real-preview').attr('class', `webform-public webform-preset-preview-form webform-style-${escapeHtml(settings.key)}`).css({
            '--wf-accent':settings.accent,'--wf-button-text':settings.buttonText,'--wf-text':settings.text,'--wf-form-bg':settings.formBackground,'--wf-field-bg':settings.fieldBackground,'--wf-border':settings.border,'--wf-font':settings.font,'--wf-font-size':`${settings.fontSize}px`,'--wf-label-size':`${settings.labelSize}px`,'--wf-max-width':`${settings.maxWidth}px`,'--wf-field-space':`${settings.spacing}px`,'--wf-field-radius':`${settings.fieldRadius}px`,'--wf-button-radius':`${settings.buttonRadius}px`,'--wf-button-padding':`${settings.buttonPadding}px ${settings.buttonPadding * 2}px`,'--wf-submit-align':settings.submitAlignment === 'left' ? 'flex-start' : settings.submitAlignment === 'center' ? 'center' : 'flex-end','--wf-submit-width':settings.submitAlignment === 'full' ? '100%' : 'auto','--wf-submit-bg':settings.submitBackground,'--wf-submit-hover':settings.submitHover,'--wf-submit-text':settings.submitText,'--wf-submit-font-size':`${settings.submitFontSize}px`,'--wf-submit-font-weight':settings.submitFontWeight,'--wf-submit-padding':`${settings.submitPaddingY}px ${settings.submitPaddingX}px`,'--wf-submit-border':`${settings.submitBorderWidth}px solid ${settings.submitBorder}`
        }).html(`${steps}${stages}`);
        previewCustomCss();
        $('#webform-preset-preview-title').text($('#webform-name').val() || 'Untitled form');
        $('#webform-preview-style-name').text(`${settings.name} · LIVE FORM PREVIEW`);
    }

    function previewOptions(field, control) {
        const options = field.options && field.options.length ? field.options : ['Option 1', 'Option 2'];
        return options.map((option, index) => `<span class="webform-preview-choice"><i class="${control}">${control === 'radio' && index === 0 ? '<b></b>' : control === 'check' && index === 0 ? '✓' : ''}</i>${escapeHtml(option)}</span>`).join('');
    }

    function dividerPreview(field) {
        const width = Math.max(10, Math.min(100, Number(field.divider_width || 100)));
        const thickness = Math.max(1, Math.min(12, Number(field.divider_thickness || 1)));
        const lineStyle = ['solid', 'dashed', 'dotted', 'double'].includes(field.divider_style) ? field.divider_style : 'solid';
        const alignment = ['left', 'center', 'right'].includes(field.divider_alignment) ? field.divider_alignment : 'center';
        const color = /^#[0-9a-f]{6}$/i.test(field.divider_color || '') ? field.divider_color : '#68707d';
        const showLabel = !!field.divider_show_label;
        const labelPosition = field.divider_label_position === 'inline' ? 'inline' : 'above';
        const label = showLabel ? `<small>${escapeHtml(field.label || 'Divider')}</small>` : '';
        return `<div class="webform-preview-divider is-${alignment} label-${labelPosition} ${showLabel ? 'has-label' : ''}" style="--divider-preview-width:${width}%;--divider-preview-thickness:${thickness}px;--divider-preview-style:${lineStyle};--divider-preview-color:${escapeHtml(color)}">${labelPosition === 'above' ? label : ''}<div><span></span>${labelPosition === 'inline' ? label : ''}<span></span></div></div>`;
    }

    function fieldPreview(field) {
        const placeholder = escapeHtml(field.placeholder || '');
        const option = escapeHtml((field.options || [])[0] || 'Choose an option');
        const value = escapeHtml(field.default_value || '');
        const previews = {
            name: '<div class="webform-preview-name"><span><small>First name</small><i></i></span><span><small>Last name</small><i></i></span></div>',
            text: `<div class="webform-preview-control"><span>${placeholder || 'Enter text'}</span></div>`,
            email: `<div class="webform-preview-control webform-preview-with-icon"><span class="dashicons dashicons-email-alt"></span><span>${placeholder || 'name@example.com'}</span></div>`,
            textarea: `<div class="webform-preview-control webform-preview-textarea" style="height:${Math.max(70, Math.min(240, Number(field.rows || 5) * 18))}px"><span>${placeholder || 'Enter a detailed response'}</span></div>`,
            select: `<div class="webform-preview-control webform-preview-select"><span>${option}</span><span class="dashicons dashicons-arrow-down-alt2"></span></div>`,
            radio: `<div class="webform-preview-choices webform-choice-columns-${Number(field.choice_columns || 1)}">${previewOptions(field, 'radio')}</div>`,
            checkbox: `<div class="webform-preview-choices webform-choice-columns-${Number(field.choice_columns || 1)}">${previewOptions(field, 'check')}</div>`,
            number: `<div class="webform-preview-control webform-preview-number"><span>${placeholder || '0'}</span><span class="webform-preview-steppers">⌃<br>⌄</span></div>`,
            date: `<div class="webform-preview-control webform-preview-with-icon"><span>${field.date_rule === 'future' ? 'Today or later' : field.date_rule === 'past' ? 'Today or earlier' : field.date_rule === 'custom' ? 'Custom date range' : 'yyyy-mm-dd'}</span><span class="dashicons dashicons-calendar-alt"></span></div>`,
            time: '<div class="webform-preview-control webform-preview-with-icon"><span>--:-- --</span><span class="dashicons dashicons-clock"></span></div>',
            phone: `<div class="webform-preview-control webform-preview-with-icon"><span class="dashicons dashicons-phone"></span><span>${placeholder || '(555) 123-4567'}</span></div>`,
            url: `<div class="webform-preview-control webform-preview-with-icon"><span class="dashicons dashicons-admin-links"></span><span>${placeholder || 'https://example.com'}</span></div>`,
            file: `<div class="webform-preview-file"><span class="webform-preview-file-button"><span class="dashicons dashicons-upload"></span>Choose file</span><span>No file chosen</span><small>${escapeHtml(field.allowed_extensions || 'jpg, png, pdf')} · up to ${Number(field.max_size || 5)} MB</small></div>`,
            consent: `<div class="webform-preview-consent"><i class="check"></i><span>${escapeHtml(field.label || 'I agree to the terms')}</span></div>`,
            poll: `<div class="webform-preview-choices webform-preview-poll webform-choice-columns-${Number(field.choice_columns || 1)}">${previewOptions(field, 'radio')}</div>`,
            quiz: `<div class="webform-preview-choices webform-preview-quiz webform-choice-columns-${Number(field.choice_columns || 1)}">${previewOptions(field, 'radio')}<small>${Number(field.points || 1)} point${Number(field.points || 1) === 1 ? '' : 's'}</small></div>`,
            rating: '<div class="webform-preview-rating" aria-label="Five star rating"><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>',
            slider: `<div class="webform-preview-slider"><div><span></span></div><small>${Number(field.min ?? 0)}</small><small>${Number(field.max ?? 100)}</small></div>`,
            hidden: `<div class="webform-preview-hidden"><span class="dashicons dashicons-hidden"></span><span>Hidden value</span><code>${value || 'Not set'}</code></div>`,
            html: `<div class="webform-preview-html"><span class="dashicons dashicons-editor-code"></span><div>${escapeHtml($('<div>').html(field.html || '').text() || 'Custom HTML content')}</div></div>`,
            captcha: '<div class="webform-preview-captcha"><i class="check"></i><span>I’m not a robot</span><span class="webform-preview-recaptcha"><span class="dashicons dashicons-update"></span><small>reCAPTCHA</small></span></div>',
            heading: `<div class="webform-preview-heading">${escapeHtml(field.label || 'Section heading')}</div>`,
            calculation: `<div class="webform-preview-calculation"><strong>${Number(0).toFixed(Math.max(0, Math.min(6, Number(field.decimal_places ?? 2))))}</strong><code>${escapeHtml(field.formula || 'Formula not configured')}</code></div>`,
            field_group: containerPreview(field),
            signature: '<div class="webform-preview-signature"><span>Sign here</span><svg viewBox="0 0 240 48" aria-hidden="true"><path d="M8 38c30-4 31-30 43-23 10 6-8 22-2 24 11 4 21-25 29-20 6 4-7 18 0 20 9 2 13-13 20-11 5 2 3 9 13 9 16 0 24-8 39-5"/></svg><small>Clear signature</small></div>',
            rich_text: `<div class="webform-preview-rich-text">${safeRichPreview(field.rich_content || '<h3>Agreement terms</h3><p>Add your contract or agreement content here.</p>')}</div>`,
            divider: dividerPreview(field),
            address: '<div class="webform-preview-address"><i>Street address</i><i>City</i><i>State / Province</i><i>Postal code</i><i>Country</i></div>',
            repeater: containerPreview(field),
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
                if (ui.item.data('formorbit-container-drop')) {
                    ui.item.removeData('formorbit-container-drop');
                    return;
                }
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
                scheduleHistory(0);
                render();
            }
        });
        initializeLiveContainers();
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
        const liveContainerTitle = ['field_group', 'repeater'].includes(field.type) && WebformAdmin.proActive && field.id === selectedId
            ? `<input class="webform-live-container-title" value="${escapeHtml(field.label)}" aria-label="${field.type === 'field_group' ? 'Group label' : 'Repeater label'}">${field.required ? ' <em>*</em>' : ''}`
            : `${iconPreview}${escapeHtml(field.label)}${field.required ? ' <em>*</em>' : ''}`;
        return `${field.row_start ? '<span class="webform-row-break" aria-hidden="true"></span>' : ''}<div class="webform-field-card webform-field-card-${escapeHtml(field.type)} ${widthMode === 'auto' ? 'is-width-auto' : ''} ${locked ? 'is-pro-locked' : ''} ${field.id === selectedId ? 'is-selected' : ''}" data-id="${field.id}" style="${cardStyle}">
            <span class="dashicons ${locked ? 'dashicons-lock' : 'dashicons-menu'} webform-drag"></span>
            <div class="webform-field-preview"><strong>${liveContainerTitle}</strong>
            ${fieldPreview(field)}</div>
            ${widthControls}
            <span class="webform-type">${locked ? 'PRO LOCKED' : escapeHtml(typeName)}</span>
            ${locked ? '' : '<button class="webform-remove-field" title="Remove">×</button>'}
        </div>`;
    }

    function selectedField() {
        return (schema[activeStage].fields || []).find(field => field.id === selectedId);
    }

    function removeContainerChild(parent, index) {
        if (!parent || !containerChildren(parent)[index]) return;
        parent.children = containerChildren(parent).filter((child, childIndex) => childIndex !== index);
        if (!parent.children.length) selectedChildIndex = null;
        else if (selectedChildIndex === index) selectedChildIndex = Math.min(index, parent.children.length - 1);
        else if (Number.isInteger(selectedChildIndex) && selectedChildIndex > index) selectedChildIndex -= 1;
        dirty = true;
        scheduleHistory(0);
        render();
    }

    function moveContainerChildOut(parent, index) {
        const child = parent && containerChildren(parent)[index];
        if (!child) return;
        const restored = JSON.parse(JSON.stringify(child));
        restored.id = uid('field');
        restored.style = { width: 'auto' };
        restored.condition = { enabled: false, field_id: '', operator: 'equals', value: '' };
        restored.row_start = false;
        parent.children = containerChildren(parent).filter((item, childIndex) => childIndex !== index);
        const parentIndex = schema[activeStage].fields.indexOf(parent);
        schema[activeStage].fields.splice(parentIndex + 1, 0, restored);
        selectedId = restored.id;
        selectedChildIndex = null;
        dirty = true;
        scheduleHistory(0);
        render();
    }

    function syncRichTextEditor() {
        if (!richTextEditorFieldId) return;
        const field = schema.flatMap(stage => stage.fields || []).find(item => item.id === richTextEditorFieldId);
        if (!field || field.type !== 'rich_text') return;
        const editor = window.tinymce && window.tinymce.get('webform-rich-text-content');
        const textarea = document.getElementById('webform-rich-text-content');
        if (editor) field.rich_content = editor.getContent();
        else if (textarea) field.rich_content = textarea.value;
    }

    function removeRichTextEditor() {
        syncRichTextEditor();
        if (window.wp && wp.editor && document.getElementById('webform-rich-text-content')) wp.editor.remove('webform-rich-text-content');
        richTextEditorFieldId = null;
    }

    function initializeRichTextEditor(field) {
        if (!window.wp || !wp.editor || !document.getElementById('webform-rich-text-content')) return;
        richTextEditorFieldId = field.id;
        wp.editor.initialize('webform-rich-text-content', {
            mediaButtons: true,
            quicktags: true,
            tinymce: {
                wpautop: true,
                height: 320,
                toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_more,fullscreen',
                toolbar2: 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                setup: function (editor) {
                    editor.on('change keyup undo redo', function () {
                        field.rich_content = editor.getContent();
                        dirty = true;
                        scheduleHistory();
                        const card = document.querySelector(`.webform-field-card[data-id="${field.id}"] .webform-preview-rich-text`);
                        if (card) card.innerHTML = safeRichPreview(field.rich_content);
                    });
                }
            }
        });
        $('#webform-rich-text-content').on('input change', function () {
            field.rich_content = $(this).val();
            dirty = true;
        });
    }

    function renderSettings() {
        removeRichTextEditor();
        const field = selectedField();
        if (!field) {
            $('#webform-field-settings').html('<p class="description">Select a field to edit its options.</p>');
            return;
        }
        if (isLockedProField(field)) {
            $('#webform-field-settings').html('<div class="webform-locked-field-message"><span class="dashicons dashicons-lock"></span><strong>Pro field unavailable</strong><p>This field and its settings are preserved. Activate FormOrbit Pro with a valid license to edit or display it.</p></div>');
            return;
        }
        const choices = ['select', 'radio', 'checkbox', 'poll', 'quiz', 'product'].includes(field.type);
        const candidates = schema.flatMap(stage => stage.fields).filter(item => item.id !== field.id && !['heading','file','html','rich_text','divider'].includes(item.type));
        const condition = field.condition || { enabled: false, field_id: '', operator: 'equals', value: '' };
        const selectedIcon = fieldIcons[field.icon] || fieldIcons[''];
        $('#webform-field-settings').html(`
            <div class="webform-field-quick-actions"><p class="description">Field ID: <code>${escapeHtml(field.id)}</code></p>${WebformAdmin.proActive ? '<button type="button" class="button webform-duplicate-field" title="Duplicate field" aria-label="Duplicate field"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span><span class="screen-reader-text">Duplicate field</span></button>' : ''}</div>
            <label>${field.type === 'field_group' ? 'Group label' : field.type === 'repeater' ? 'Repeater label' : 'Label'}<input type="text" data-prop="label" value="${escapeHtml(field.label)}"></label>
            ${!['hidden','html','rich_text','divider','heading','consent','captcha'].includes(field.type) ? `<label class="webform-check"><input type="checkbox" data-prop="hide_label" ${field.hide_label ? 'checked' : ''}> Hide label on public form <small>The label remains available to screen readers.</small></label>` : ''}
            ${['html','rich_text'].includes(field.type) ? '<p class="description">The editor label helps identify this element and is hidden on the public form.</p>' : ''}
            ${WebformAdmin.proActive && !['hidden','html','rich_text','divider'].includes(field.type) ? `<div class="webform-icon-property"><label>Field icon <small>Shown beside the label</small></label><button type="button" class="webform-open-icon-gallery"><span class="dashicons ${escapeHtml(selectedIcon[1])}" aria-hidden="true"></span><span>${escapeHtml(selectedIcon[0])}</span><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button></div>` : ''}
            ${!['name','heading','consent','file','hidden','html','rich_text','captcha','rating','slider'].includes(field.type) && !choices ? `<label>Placeholder<input type="text" data-prop="placeholder" value="${escapeHtml(field.placeholder || '')}"></label>` : ''}
            ${choices ? `<label>Options <small>One per line</small><textarea rows="6" data-prop="options">${escapeHtml((field.options || []).join('\n'))}</textarea></label>` : ''}
            ${['radio','checkbox','poll','quiz'].includes(field.type) ? `<label>Option columns <small>Automatically collapses on smaller screens.</small><select data-prop="choice_columns">${[1,2,3,4].map(column => `<option value="${column}" ${Number(field.choice_columns || 1) === column ? 'selected' : ''}>${column}</option>`).join('')}</select></label>` : ''}
            ${field.type === 'quiz' ? `<label>Correct answer<select data-prop="correct_answer"><option value="">Choose answer</option>${(field.options || []).map(option => `<option value="${escapeHtml(option)}" ${field.correct_answer === option ? 'selected' : ''}>${escapeHtml(option)}</option>`).join('')}</select></label><label>Points<input type="number" min="1" max="100" data-prop="points" value="${Number(field.points || 1)}"></label>` : ''}
            ${field.type === 'file' ? `<label>Allowed extensions<input type="text" data-prop="allowed_extensions" value="${escapeHtml(field.allowed_extensions || 'jpg,jpeg,png,pdf,doc,docx')}"></label><label>Maximum size (MB)<input type="number" min="1" max="20" data-prop="max_size" value="${Number(field.max_size || 5)}"></label>` : ''}
            ${field.type === 'hidden' ? `<label>Default value<input type="text" data-prop="default_value" value="${escapeHtml(field.default_value || '')}"></label>` : ''}
            ${field.type === 'html' ? `<label>Safe HTML content<textarea rows="8" data-prop="html">${escapeHtml(field.html || '')}</textarea></label>` : ''}
            ${field.type === 'rich_text' ? `<div class="webform-rich-text-editor"><label for="webform-rich-text-content">Contract or agreement content <small>Format text, add links, lists, tables, and media.</small></label><textarea id="webform-rich-text-content" rows="14">${escapeHtml(field.rich_content || '')}</textarea></div>` : ''}
            ${field.type === 'textarea' ? `<label>Visible rows <small>Adjust the starting height of the long-text box.</small><input type="number" min="2" max="30" data-prop="rows" value="${Math.max(2, Math.min(30, Number(field.rows || 5)))}"></label>` : ''}
            ${field.type === 'date' ? `<label>Allowed dates<select data-prop="date_rule"><option value="any" ${(field.date_rule || 'any') === 'any' ? 'selected' : ''}>Any date</option><option value="future" ${field.date_rule === 'future' ? 'selected' : ''}>Today and future dates</option><option value="past" ${field.date_rule === 'past' ? 'selected' : ''}>Today and past dates</option><option value="custom" ${field.date_rule === 'custom' ? 'selected' : ''}>Custom date range</option></select></label><div class="webform-date-custom-range" ${field.date_rule === 'custom' ? '' : 'hidden'}><label>Earliest date<input type="date" data-prop="date_min" value="${escapeHtml(field.date_min || '')}"></label><label>Latest date<input type="date" data-prop="date_max" value="${escapeHtml(field.date_max || '')}"></label></div>` : ''}
            ${field.type === 'phone' ? `<label>Default country<select data-prop="phone_country">${[['US','United States (+1)'],['CA','Canada (+1)'],['GB','United Kingdom (+44)'],['AU','Australia (+61)'],['BD','Bangladesh (+880)'],['IN','India (+91)'],['PK','Pakistan (+92)'],['AE','United Arab Emirates (+971)'],['SA','Saudi Arabia (+966)']].map(option => `<option value="${option[0]}" ${(field.phone_country || 'US') === option[0] ? 'selected' : ''}>${option[1]}</option>`).join('')}</select></label><label class="webform-check"><input type="checkbox" data-prop="phone_country_selector" ${field.phone_country_selector !== false ? 'checked' : ''}> Let visitors choose their country</label>` : ''}
            ${field.type === 'slider' ? `<label>Minimum<input type="number" data-prop="min" value="${Number(field.min ?? 0)}"></label><label>Maximum<input type="number" data-prop="max" value="${Number(field.max ?? 100)}"></label><label>Step<input type="number" min="0.01" step="0.01" data-prop="step" value="${Number(field.step || 1)}"></label>` : ''}
            ${field.type === 'calculation' ? `<div class="webform-calculation-builder"><label>Formula <small>Use field IDs in braces, for example <code>round({price} * {quantity}, 2)</code></small><textarea rows="4" data-prop="formula">${escapeHtml(field.formula || '')}</textarea></label><p class="description"><strong>Functions:</strong> sum, avg, min, max, round, ceil, floor, abs, sqrt, pow, clamp and if. Operators: + − × ÷ % ^ and comparisons.</p><div class="webform-calculation-field-chips">${candidates.map(item => `<button type="button" class="button webform-insert-calculation-field" data-field-reference="{${escapeHtml(item.id)}}">${escapeHtml(item.label || item.id)}</button>`).join('')}</div><label>Decimal places<input type="number" min="0" max="6" data-prop="decimal_places" value="${Number(field.decimal_places ?? 2)}"></label></div>` : ''}
            ${field.type === 'field_group' ? `${containerChildren(field).length ? '' : `<label>Legacy fields to group<input type="number" min="1" max="6" data-prop="group_count" value="${Number(field.group_count || 2)}"></label>`}<label>Columns<input type="number" min="1" max="4" data-prop="group_columns" value="${Number(field.group_columns || 2)}"></label>${containerChildrenSettings(field)}` : ''}
            ${field.type === 'repeater' ? `<label>Minimum rows<input type="number" min="1" max="20" data-prop="repeater_min" value="${Number(field.repeater_min || 1)}"></label><label>Maximum rows<input type="number" min="1" max="50" data-prop="repeater_max" value="${Number(field.repeater_max || 10)}"></label><label>Add row button text<input type="text" data-prop="repeater_button" value="${escapeHtml(field.repeater_button || 'Add another row')}"></label>${containerChildrenSettings(field)}` : ''}
            ${field.type === 'appointment' ? `<label>Earliest date and time<input type="datetime-local" data-prop="min_date" value="${escapeHtml(field.min_date || '')}"></label><label>Latest date and time<input type="datetime-local" data-prop="max_date" value="${escapeHtml(field.max_date || '')}"></label>` : ''}
            ${field.type === 'currency' ? `<label>Currency symbol<input type="text" maxlength="5" data-prop="currency_symbol" value="${escapeHtml(field.currency_symbol || '$')}"></label><label>Minimum<input type="number" step="0.01" data-prop="min" value="${Number(field.min ?? 0)}"></label><label>Maximum<input type="number" step="0.01" data-prop="max" value="${Number(field.max ?? 999999999)}"></label>` : ''}
            ${field.type === 'product' ? `<p class="description">Enter each product as <code>Product name|19.99</code>.</p>` : ''}
            ${field.type === 'price' ? `<label>Amount<input type="number" min="0" step="0.01" data-prop="price_amount" value="${Number(field.price_amount || 0)}"></label><label>Currency<select data-prop="currency_code">${['USD','EUR','GBP','CAD','AUD','BDT'].map(code => `<option ${field.currency_code === code ? 'selected' : ''}>${code}</option>`).join('')}</select></label>` : ''}
            ${field.type === 'divider' ? `<hr><div class="webform-divider-settings"><h3>Divider design <span class="webform-pro-badge">PRO</span></h3>
                <label class="webform-check"><input type="checkbox" data-prop="divider_show_label" ${field.divider_show_label ? 'checked' : ''}> Show label on public form</label>
                <label>Label placement<select data-prop="divider_label_position"><option value="above" ${(field.divider_label_position || 'above') === 'above' ? 'selected' : ''}>Above the line</option><option value="inline" ${field.divider_label_position === 'inline' ? 'selected' : ''}>Centered in the line</option></select></label>
                <div class="webform-divider-control-grid">
                    <label>Line style<select data-prop="divider_style">${[['solid','Solid'],['dashed','Dashed'],['dotted','Dotted'],['double','Double']].map(option => `<option value="${option[0]}" ${(field.divider_style || 'solid') === option[0] ? 'selected' : ''}>${option[1]}</option>`).join('')}</select></label>
                    <label>Alignment<select data-prop="divider_alignment">${[['left','Left'],['center','Center'],['right','Right']].map(option => `<option value="${option[0]}" ${(field.divider_alignment || 'center') === option[0] ? 'selected' : ''}>${option[1]}</option>`).join('')}</select></label>
                    <label>Line width <small>%</small><input type="number" min="10" max="100" data-prop="divider_width" value="${Math.max(10, Math.min(100, Number(field.divider_width || 100)))}"></label>
                    <label>Thickness <small>px</small><input type="number" min="1" max="12" data-prop="divider_thickness" value="${Math.max(1, Math.min(12, Number(field.divider_thickness || 1)))}"></label>
                    <label>Line color<input type="color" data-prop="divider_color" value="${escapeHtml(field.divider_color || '#dfe1e6')}"></label>
                    <label>Top margin <small>px</small><input type="number" min="0" max="100" data-prop="divider_margin_top" value="${Math.max(0, Math.min(100, Number(field.divider_margin_top ?? 10)))}"></label>
                    <label>Bottom margin <small>px</small><input type="number" min="0" max="100" data-prop="divider_margin_bottom" value="${Math.max(0, Math.min(100, Number(field.divider_margin_bottom ?? 10)))}"></label>
                    <label>Top padding <small>px</small><input type="number" min="0" max="80" data-prop="divider_padding_top" value="${Math.max(0, Math.min(80, Number(field.divider_padding_top || 0)))}"></label>
                    <label>Bottom padding <small>px</small><input type="number" min="0" max="80" data-prop="divider_padding_bottom" value="${Math.max(0, Math.min(80, Number(field.divider_padding_bottom || 0)))}"></label>
                </div>
                <label>Custom CSS class<input type="text" data-field-style="css_class" value="${escapeHtml(field.style?.css_class || '')}" placeholder="section-divider"></label>
            </div>` : ''}
            ${!['heading','hidden','html','rich_text','divider'].includes(field.type) ? `<label class="webform-check"><input type="checkbox" data-prop="required" ${field.required ? 'checked' : ''}> Required field</label>` : ''}
            ${field.type !== 'heading' && candidates.length ? `<hr><h3>Conditional display</h3>
                <label class="webform-check"><input type="checkbox" data-condition="enabled" ${condition.enabled ? 'checked' : ''}> Show this field conditionally</label>
                <div class="webform-condition-settings ${condition.enabled ? '' : 'is-hidden'}">
                    <label>When field<select data-condition="field_id"><option value="">Choose field</option>${candidates.map(item => `<option value="${item.id}" ${condition.field_id === item.id ? 'selected' : ''}>${escapeHtml(item.label)}</option>`).join('')}</select></label>
                    <label>Operator<select data-condition="operator">${[['equals','Equals'],['not_equals','Does not equal'],['contains','Contains'],['starts_with','Starts with'],['ends_with','Ends with'],['greater_than','Greater than'],['less_than','Less than'],['not_empty','Is not empty'],['empty','Is empty']].map(item => `<option value="${item[0]}" ${condition.operator === item[0] ? 'selected' : ''}>${item[1]}</option>`).join('')}</select></label>
                    <label>Value<input type="text" data-condition="value" value="${escapeHtml(condition.value || '')}"></label>
                </div>` : ''}
            ${field.type !== 'divider' ? `<hr><h3>Field appearance ${WebformAdmin.proStyling ? '<span class="webform-pro-badge">PRO</span>' : ''}</h3>
            ${WebformAdmin.proStyling ? `<div class="webform-field-style-controls">
                <label>Field width<select data-field-style="width"><option value="auto" ${field.style?.width === 'auto' ? 'selected' : ''}>Auto — share row space</option><option value="100" ${(field.style?.width || '100') === '100' ? 'selected' : ''}>Full width</option><option value="75" ${field.style?.width === '75' ? 'selected' : ''}>75%</option><option value="50" ${field.style?.width === '50' ? 'selected' : ''}>Half width</option><option value="33" ${field.style?.width === '33' ? 'selected' : ''}>One third</option></select></label>
                <div class="webform-style-color-grid">${field.type !== 'rich_text' ? `<label>Label<input type="color" data-field-style="label_color" value="${escapeHtml(field.style?.label_color || '#1d2327')}"></label>` : ''}${field.type !== 'heading' ? `<label>Field<input type="color" data-field-style="background_color" value="${escapeHtml(field.style?.background_color || '#ffffff')}"></label><label>Text<input type="color" data-field-style="text_color" value="${escapeHtml(field.style?.text_color || '#1d2327')}"></label>` : ''}</div>
                <label>Corner radius<input type="number" min="0" max="40" data-field-style="radius" value="${Number(field.style?.radius ?? 7)}"></label>
                <label>Custom CSS class<input type="text" data-field-style="css_class" value="${escapeHtml(field.style?.css_class || '')}" placeholder="featured-field"></label>
            </div>` : '<div class="webform-field-style-locked">🔒 Width, colors, corners, and custom classes are available in Pro.</div>'}` : ''}
        `);
        if (field.type === 'rich_text') window.setTimeout(function () { initializeRichTextEditor(field); }, 0);
    }

    function addField(type) {
        if (type === 'page_break') {
            schema.push({ id: uid('stage'), title: `Stage ${schema.length + 1}`, fields: [] });
            activeStage = schema.length - 1;
            selectedId = null;
            selectedChildIndex = null;
            dirty = true;
            render();
            return;
        }
        const choices = ['select', 'radio', 'checkbox', 'poll', 'quiz', 'product'].includes(type);
        const field = { id: uid('field'), type, label: defaults[type] || 'Field', icon: '', placeholder: '', hide_label: false, required: ['consent','captcha','signature'].includes(type), options: choices ? (type === 'product' ? ['Standard|19.99', 'Premium|39.99'] : ['Option 1', 'Option 2']) : [], choice_columns: 1, children: ['field_group', 'repeater'].includes(type) ? defaultContainerChildren(type) : [], allowed_extensions: 'jpg,jpeg,png,pdf,doc,docx', max_size: 5, correct_answer: '', points: 1, default_value: '', html: '<p>Add your content here.</p>', rich_content: type === 'rich_text' ? '<h3>Agreement terms</h3><p>Describe the terms, responsibilities, and conditions of this agreement.</p><p><strong>Acceptance:</strong> Add a Consent field and E-signature below this agreement.</p>' : '', rows: 5, date_rule: 'any', date_min: '', date_max: '', min: 0, max: type === 'currency' ? 999999999 : 100, step: 1, formula: '', decimal_places: 2, group_count: 2, group_columns: 2, repeater_min: 1, repeater_max: 10, repeater_button: 'Add another row', currency_symbol: '$', price_amount: 0, currency_code: 'USD', min_date: '', max_date: '', divider_show_label: false, divider_label_position: 'above', divider_style: 'solid', divider_alignment: 'center', divider_width: 100, divider_thickness: 1, divider_color: '#dfe1e6', divider_margin_top: 10, divider_margin_bottom: 10, divider_padding_top: 0, divider_padding_bottom: 0, style: { width: type === 'divider' ? '100' : (WebformAdmin.proActive ? 'auto' : '100') }, condition: { enabled: false, field_id: '', operator: 'equals', value: '' } };
        schema[activeStage].fields.push(field);
        selectedId = field.id;
        selectedChildIndex = ['field_group', 'repeater'].includes(type) ? 0 : null;
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
    $(document).on('keydown', function (event) {
        if (event.key !== 'Escape') return;
        closeFieldPicker();
        closeChildEditor();
    });
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
        if ($(event.target).closest('.webform-remove-field,.webform-card-widths,.webform-live-container,.webform-live-container-title').length) return;
        selectedId = $(this).data('id');
        selectedChildIndex = null;
        render();
        $('.webform-property-tabs button[data-panel="field"]').trigger('click');
    });
    $(document).on('click keydown', '.webform-container-preview-child', function (event) {
        if (event.type === 'keydown' && !['Enter', ' '].includes(event.key)) return;
        if ($(event.target).closest('input,button,.webform-live-child-drag').length) return;
        event.preventDefault();
        event.stopPropagation();
        const parent = containerFromElement(this);
        if (!parent || !WebformAdmin.proActive) return;
        openChildEditor(parent, Number($(this).data('child-index')));
    });
    $(document).on('click', '.webform-live-container', function (event) {
        if ($(event.target).closest('.webform-container-preview-child,.webform-live-container-drop,.webform-container-preview-add').length) return;
        event.preventDefault();
        event.stopPropagation();
        const parent = containerFromElement(this);
        if (!parent) return;
        selectedId = parent.id;
        selectedChildIndex = null;
        render();
        $('.webform-property-tabs button[data-panel="field"]').trigger('click');
    });
    $(document).on('click', '.webform-live-container-title', function (event) {
        event.stopPropagation();
        selectedChildIndex = null;
        renderSettings();
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
            selectedChildIndex = null;
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
        selectedChildIndex = null;
        dirty = true;
        render();
    });
    $(document).on('click', '.webform-remove-field', function () {
        const id = $(this).closest('.webform-field-card').data('id');
        schema[activeStage].fields = schema[activeStage].fields.filter(field => field.id !== id);
        dirty = true;
        if (selectedId === id) selectedId = null;
        selectedChildIndex = null;
        render();
    });
    $(document).on('click', '.webform-duplicate-field', function () {
        syncRichTextEditor();
        const field = selectedField();
        if (!field || !WebformAdmin.proActive) return;
        const duplicate = JSON.parse(JSON.stringify(field));
        duplicate.id = uid('field');
        if (Array.isArray(duplicate.children)) duplicate.children.forEach(child => { child.id = uid('child'); });
        duplicate.label = `${field.label || defaults[field.type] || 'Field'} copy`;
        duplicate.row_start = false;
        const index = schema[activeStage].fields.indexOf(field);
        schema[activeStage].fields.splice(index + 1, 0, duplicate);
        selectedId = duplicate.id;
        selectedChildIndex = null;
        dirty = true;
        render();
    });
    $(document).on('input change', '#webform-field-settings [data-prop]', function () {
        const field = selectedField();
        if (!field) return;
        const prop = $(this).data('prop');
        field[prop] = ['required', 'hide_label', 'divider_show_label', 'phone_country_selector'].includes(prop) ? $(this).is(':checked') : prop === 'options' ? $(this).val().split('\n').map(v => v.trim()).filter(Boolean) : $(this).val();
        dirty = true;
        const caret = this.selectionStart;
        $('#webform-canvas').html(schema[activeStage].fields.map(fieldCard).join(''));
        initializeLiveContainers();
        if (!['required', 'hide_label', 'divider_show_label', 'phone_country_selector'].includes(prop)) {
            const input = document.querySelector(`#webform-field-settings [data-prop="${prop}"]`);
            if (input && caret != null) input.setSelectionRange(caret, caret);
        }
    });
    $(document).on('click', '.webform-insert-calculation-field', function () {
        const textarea = document.querySelector('#webform-field-settings [data-prop="formula"]');
        if (!textarea) return;
        const reference = String($(this).data('field-reference') || '');
        const start = textarea.selectionStart == null ? textarea.value.length : textarea.selectionStart;
        const end = textarea.selectionEnd == null ? start : textarea.selectionEnd;
        textarea.value = textarea.value.slice(0, start) + reference + textarea.value.slice(end);
        textarea.selectionStart = textarea.selectionEnd = start + reference.length;
        $(textarea).trigger('input').trigger('focus');
    });
    $(document).on('click', '.webform-add-container-child', function () {
        const field = selectedField();
        if (!field || !['field_group', 'repeater'].includes(field.type)) return;
        field.children = containerChildren(field);
        if (field.children.length >= 20) return;
        field.children.push(createContainerChild('text'));
        selectedChildIndex = field.children.length - 1;
        dirty = true;
        openChildEditor(field, selectedChildIndex);
    });
    $(document).on('click', '.webform-live-container-drop', function (event) {
        event.preventDefault();
        event.stopPropagation();
        const field = containerFromElement(this);
        if (!field || !['field_group', 'repeater'].includes(field.type) || containerChildren(field).length >= 20) return;
        field.children = containerChildren(field).concat(createContainerChild('text'));
        selectedId = field.id;
        selectedChildIndex = field.children.length - 1;
        dirty = true;
        scheduleHistory(0);
        openChildEditor(field, selectedChildIndex);
    });
    $(document).on('input', '.webform-live-container-title', function (event) {
        event.stopPropagation();
        const field = selectedField();
        if (!field || !['field_group', 'repeater'].includes(field.type)) return;
        field.label = $(this).val();
        const settingsLabel = document.querySelector('#webform-field-settings [data-prop="label"]');
        if (settingsLabel) settingsLabel.value = field.label;
        dirty = true;
        scheduleHistory();
    });
    $(document).on('change', '.webform-live-container-title', function () { render(); });
    $(document).on('input', '.webform-live-child-label', function (event) {
        event.stopPropagation();
        const field = containerFromElement(this);
        const index = Number($(this).closest('[data-child-index]').data('child-index'));
        const child = field && containerChildren(field)[index];
        if (!child) return;
        selectedId = field.id;
        selectedChildIndex = index;
        child.label = $(this).val();
        const settingsLabel = document.querySelector('#webform-field-settings [data-child-prop="label"]');
        if (settingsLabel) settingsLabel.value = child.label;
        dirty = true;
        scheduleHistory();
    });
    $(document).on('focus', '.webform-live-child-label', function () {
        const field = containerFromElement(this);
        if (!field) return;
        selectedId = field.id;
        selectedChildIndex = Number($(this).closest('[data-child-index]').data('child-index'));
        $('.webform-container-preview-child').removeClass('is-child-selected');
        $(this).closest('.webform-container-preview-child').addClass('is-child-selected');
        renderSettings();
    });
    $(document).on('change', '.webform-live-child-label', function () { render(); });
    $(document).on('click', '.webform-live-remove-child', function (event) {
        event.preventDefault();
        event.stopPropagation();
        const field = containerFromElement(this);
        if (!field) return;
        const index = Number($(this).closest('[data-child-index]').data('child-index'));
        selectedId = field.id;
        removeContainerChild(field, index);
    });
    $(document).on('click', '.webform-live-child-width', function (event) {
        event.preventDefault();
        event.stopPropagation();
        const field = containerFromElement(this);
        const index = Number($(this).closest('[data-child-index]').data('child-index'));
        const child = field && containerChildren(field)[index];
        if (!child) return;
        const widths = ['25', '33', '50', '66', '75', '100'];
        const current = widths.indexOf(String(child.child_width || '100'));
        child.child_width = widths[(current + 1) % widths.length];
        selectedId = field.id;
        selectedChildIndex = index;
        dirty = true;
        scheduleHistory(0);
        render();
    });
    $(document).on('click', '.webform-live-eject-child', function (event) {
        event.preventDefault();
        event.stopPropagation();
        const parent = containerFromElement(this);
        if (!parent) return;
        const index = Number($(this).closest('[data-child-index]').data('child-index'));
        moveContainerChildOut(parent, index);
    });
    $(document).on('click', '.webform-remove-container-child', function () {
        const field = selectedField();
        if (!field) return;
        const index = Number($(this).closest('.webform-container-child').data('child-index'));
        closeChildEditor();
        removeContainerChild(field, index);
    });
    $(document).on('click', '.webform-child-move-out', function () {
        const field = selectedField();
        if (!field || selectedChildIndex === null) return;
        closeChildEditor();
        moveContainerChildOut(field, selectedChildIndex);
    });
    $(document).on('click', '.webform-select-child', function () {
        const field = selectedField();
        if (!field) return;
        openChildEditor(field, Number($(this).data('child-index')));
    });
    $(document).on('click', '.webform-child-editor-close,.webform-child-editor-backdrop,.webform-child-editor-done', closeChildEditor);
    $(document).on('click', '.webform-convert-container', function () {
        const field = selectedField();
        if (!field) return;
        if (field.type === 'field_group') {
            const fields = schema[activeStage].fields || [];
            const parentIndex = fields.indexOf(field);
            const supported = [];
            const removeIndexes = [];
            for (let index = parentIndex + 1; index < fields.length && supported.length < Number(field.group_count || 2); index++) {
                const source = fields[index];
                const additions = fieldToContainerChildren(source);
                if (!additions.length) continue;
                supported.push(...additions);
                removeIndexes.push(index);
            }
            field.children = supported.length ? supported : defaultContainerChildren(field.type);
            schema[activeStage].fields = fields.filter((item, index) => !removeIndexes.includes(index));
        } else {
            field.children = [createContainerChild('text', field.label ? `${field.label} item` : 'Item')];
        }
        selectedChildIndex = 0;
        dirty = true;
        openChildEditor(field, 0);
    });
    $(document).on('input change', '#webform-child-editor-modal [data-child-prop]', function () {
        const field = selectedField();
        if (!field) return;
        const index = Number($(this).closest('.webform-container-child').data('child-index'));
        const child = containerChildren(field)[index];
        if (!child) return;
        const prop = $(this).data('child-prop');
        if (prop === 'required') child[prop] = $(this).is(':checked');
        else if (prop === 'options') child[prop] = $(this).val().split('\n').map(value => value.trim()).filter(Boolean);
        else child[prop] = $(this).val();
        if (prop === 'type' && ['select', 'radio', 'checkbox', 'poll', 'quiz', 'product'].includes(child.type) && (!Array.isArray(child.options) || !child.options.length)) {
            child.options = child.type === 'product' ? ['Standard|19.99', 'Premium|39.99'] : ['Option 1', 'Option 2'];
        }
        dirty = true;
        scheduleHistory();
        if (prop === 'type') {
            render();
            refreshChildEditorModal();
            return;
        }
        if (prop === 'label') {
            $('#webform-child-editor-title').text(`Edit ${child.label || containerChildTypes[child.type] || 'child field'}`);
            $('#webform-child-editor-modal .webform-child-editor-heading h4').text(child.label || containerChildTypes[child.type] || 'Field');
        }
        $('#webform-canvas').html(schema[activeStage].fields.map(fieldCard).join(''));
        initializeLiveContainers();
    });
    $(document).on('change', '#webform-field-settings [data-prop="date_rule"]', function () {
        $('.webform-date-custom-range').prop('hidden', $(this).val() !== 'custom');
    });
    $(document).on('click', '.webform-open-icon-gallery', function () {
        ensureIconGallery();
        const field = selectedField();
        if (!field || !WebformAdmin.proActive) return;
        const gallery = $('#webform-icon-gallery');
        gallery.find('[data-icon]').removeClass('is-selected').filter(function () { return String($(this).data('icon')) === String(field.icon || ''); }).addClass('is-selected');
        gallery.find('.webform-icon-gallery-search input').val('').trigger('input');
        gallery.addClass('is-open').attr('aria-hidden', 'false');
        window.setTimeout(function () { gallery.find('.webform-icon-gallery-search input').trigger('focus'); }, 30);
    });
    $(document).on('click', '.webform-icon-gallery-close,.webform-icon-gallery-backdrop', closeIconGallery);
    $(document).on('input', '.webform-icon-gallery-search input', function () {
        const query = String($(this).val() || '').trim().toLowerCase();
        let visible = 0;
        $('#webform-icon-gallery [data-icon]').each(function () {
            const match = !query || String($(this).data('label') || '').includes(query);
            $(this).prop('hidden', !match);
            if (match) visible++;
        });
        $('.webform-icon-gallery-empty').prop('hidden', visible > 0);
    });
    $(document).on('click', '#webform-icon-gallery [data-icon]', function () {
        const field = selectedField();
        if (!field || !WebformAdmin.proActive) return;
        field.icon = String($(this).data('icon') || '');
        dirty = true;
        scheduleHistory(0);
        closeIconGallery();
        render();
    });
    $(document).on('keydown', function (event) {
        if (event.key === 'Escape' && $('#webform-icon-gallery').hasClass('is-open')) closeIconGallery();
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
            submit_alignment: $('#webform-submit-alignment').val(), submit_font_size: $('#webform-submit-font-size').val(), submit_font_weight: $('#webform-submit-font-weight').val(),
            submit_padding_x: $('#webform-submit-padding-x').val(), submit_padding_y: $('#webform-submit-padding-y').val(), submit_border_width: $('#webform-submit-border-width').val(),
            submit_background: $('#webform-submit-background').val(), submit_hover_background: $('#webform-submit-hover-background').val(), submit_text_color: $('#webform-submit-text-color').val(), submit_border_color: $('#webform-submit-border-color').val(),
            custom_css: $('#webform-custom-css').val()
        };
    }
    const themeSelectors = { style_preset: '#webform-style-preset', accent_color: '#webform-accent-color', button_text_color: '#webform-button-text-color', font_family: '#webform-font-family', base_font_size: '#webform-base-font-size', label_font_size: '#webform-label-font-size', text_color: '#webform-text-color', form_background: '#webform-form-background', field_background: '#webform-field-background', border_color: '#webform-border-color', form_max_width: '#webform-form-max-width', field_spacing: '#webform-field-spacing', field_radius: '#webform-field-radius', button_radius: '#webform-button-radius', button_padding: '#webform-button-padding', submit_alignment: '#webform-submit-alignment', submit_font_size: '#webform-submit-font-size', submit_font_weight: '#webform-submit-font-weight', submit_padding_x: '#webform-submit-padding-x', submit_padding_y: '#webform-submit-padding-y', submit_border_width: '#webform-submit-border-width', submit_background: '#webform-submit-background', submit_hover_background: '#webform-submit-hover-background', submit_text_color: '#webform-submit-text-color', submit_border_color: '#webform-submit-border-color', custom_css: '#webform-custom-css' };
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
        if (!$('#webform-preset-preview-modal').prop('hidden')) renderRealPreview();
    }
    $(document).on('change', '#webform-style-preset', function () { applyPresetPalette($(this).val()); updatePresetPreview(); dirty = true; });
    let previewReturnFocus = null;
    function openRealPreview() {
        previewReturnFocus = document.activeElement;
        previewStage = Math.min(activeStage, Math.max(0, schema.length - 1));
        renderRealPreview();
        $('#webform-preset-preview-modal').removeAttr('hidden').attr('aria-hidden', 'false');
        $('body').addClass('webform-preview-is-open');
        window.setTimeout(function () { $('.webform-preset-preview-close').trigger('focus'); }, 0);
    }
    function closeRealPreview() {
        $('#webform-preset-preview-modal').attr('hidden', true).attr('aria-hidden', 'true');
        $('body').removeClass('webform-preview-is-open');
        if (previewReturnFocus && typeof previewReturnFocus.focus === 'function') previewReturnFocus.focus();
        previewReturnFocus = null;
    }
    $(document).on('click', '.webform-preview-preset,#webform-open-preview', openRealPreview);
    $(document).on('click', '.webform-preset-preview-close,.webform-preset-preview-backdrop', closeRealPreview);
    $(document).on('click', '.webform-preview-next', function () { previewStage = Math.min(schema.length - 1, previewStage + 1); renderRealPreview(); });
    $(document).on('click', '.webform-preview-prev', function () { previewStage = Math.max(0, previewStage - 1); renderRealPreview(); });
    $(document).on('click', '[data-preview-device]', function () {
        $('[data-preview-device]').removeClass('is-active');
        $(this).addClass('is-active');
        $('.webform-preview-viewport').removeClass('is-desktop is-tablet is-mobile').addClass(`is-${$(this).data('preview-device')}`);
    });
    $(document).on('click', '#webform-undo', function () { applyHistory(historyIndex - 1); });
    $(document).on('click', '#webform-redo', function () { applyHistory(historyIndex + 1); });
    $(document).on('keydown', function (event) {
        const previewOpen = !$('#webform-preset-preview-modal').prop('hidden');
        if (event.key === 'Escape' && previewOpen) {
            event.preventDefault();
            closeRealPreview();
            return;
        }
        if (event.key === 'Tab' && previewOpen) {
            const focusable = Array.from(document.querySelectorAll('#webform-preset-preview-modal button:not([disabled]),#webform-preset-preview-modal [href],#webform-preset-preview-modal input:not([disabled]),#webform-preset-preview-modal select:not([disabled]),#webform-preset-preview-modal textarea:not([disabled]),#webform-preset-preview-modal [tabindex]:not([tabindex="-1"])')).filter(element => element.offsetParent !== null);
            if (focusable.length) {
                const first = focusable[0], last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
            }
        }
        const editable = $(event.target).is('input,textarea,select,[contenteditable="true"]');
        if (editable || !(event.ctrlKey || event.metaKey)) return;
        if (event.key.toLowerCase() === 'z') {
            event.preventDefault();
            applyHistory(historyIndex + (event.shiftKey ? 1 : -1));
        } else if (event.key.toLowerCase() === 'y') {
            event.preventDefault();
            applyHistory(historyIndex + 1);
        }
    });
    $(document).on('click', '#webform-apply-theme', function () {
        const theme = WebformAdmin.savedThemes[$('#webform-saved-theme').val()];
        if (!theme || !theme.settings) return;
        Object.entries(theme.settings).forEach(([key, value]) => { if (themeSelectors[key]) $(themeSelectors[key]).val(value); });
        dirty = true;
        scheduleHistory(0);
        $('.webform-theme-status').text('Theme applied. Save the form to keep it.');
    });
    $(document).on('click', '#webform-save-theme', function () {
        const name = window.prompt('Theme name');
        if (!name || !name.trim()) return;
        $.post(WebformAdmin.ajaxUrl, { action: 'formorbit_pro_save_theme', nonce: WebformAdmin.nonce, name: name.trim(), settings: JSON.stringify(currentThemeSettings()) }).done(function (response) {
            if (!response.success) return;
            WebformAdmin.savedThemes = response.data.themes;
            $('#webform-saved-theme').append(`<option value="${escapeHtml(response.data.id)}">${escapeHtml(name.trim())}</option>`).val(response.data.id);
            $('.webform-theme-status').text('Theme saved for reuse.');
            scheduleHistory(0);
        }).fail(function (xhr) { $('.webform-theme-status').text(xhr.responseJSON?.data?.message || 'Could not save theme.'); });
    });
    $(document).on('click', '#webform-delete-theme', function () {
        const id = $('#webform-saved-theme').val();
        if (!id || !window.confirm('Delete this saved theme?')) return;
        $.post(WebformAdmin.ajaxUrl, { action: 'formorbit_pro_delete_theme', nonce: WebformAdmin.nonce, theme_id: id }).done(function (response) {
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
        selectedChildIndex = null;
        render();
    });
    $(document).on('dblclick', '.webform-stage-tab', function () {
        const index = Number($(this).data('stage'));
        const title = window.prompt('Stage name', schema[index].title);
        if (title && title.trim()) { schema[index].title = title.trim(); dirty = true; scheduleHistory(0); render(); }
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
        selectedChildIndex = null;
        render();
    });
    $('#webform-add-stage').on('click', function () {
        schema.push({ id: uid('stage'), title: `Stage ${schema.length + 1}`, fields: [] });
        dirty = true;
        activeStage = schema.length - 1;
        selectedId = null;
        selectedChildIndex = null;
        render();
    });
    $('#webform-save').on('click', function () {
        if (window.tinyMCE && tinyMCE.get('webform-success-message')) tinyMCE.get('webform-success-message').save();
        if (window.tinyMCE && tinyMCE.get('webform-user-notification-body')) tinyMCE.get('webform-user-notification-body').save();
        syncRichTextEditor();
        const $button = $(this).prop('disabled', true);
        $('#webform-save-status').text('Saving…');
        const formSettings = {
            success_message: $('#webform-success-message').val(),
            confirmation_type: $('#webform-confirmation-type').val(),
            notification_email: $('#webform-notification-email').val(),
            submit_label: $('#webform-submit-label').val(),
            redirect_url: $('#webform-redirect-url').val(),
            require_login: $('#webform-require-login').is(':checked'),
            submission_limit: $('#webform-submission-limit').val(),
            closed_message: $('#webform-closed-message').val(),
            style_preset: $('#webform-style-preset').val(),
            accent_color: $('#webform-accent-color').val(),
            button_text_color: $('#webform-button-text-color').val()
        };
        document.dispatchEvent(new CustomEvent('webform:collect-settings', { detail: formSettings }));
        $.post(WebformAdmin.ajaxUrl, {
            action: 'formorbit_save_form',
            nonce: WebformAdmin.nonce,
            form_id: $('#webform-id').val(),
            name: $('#webform-name').val(),
            schema: JSON.stringify(schema),
            settings: JSON.stringify(formSettings)
        }).done(function (response) {
            if (!response.success) throw new Error(response.data && response.data.message);
            $('#webform-id').val(response.data.id);
            dirty = false;
            $('#webform-save-status').text('Saved');
            updateEmbedPanel(response.data.id, response.data.shortcode);
            window.history.replaceState({}, '', `admin.php?page=formorbit-builder&form_id=${response.data.id}`);
        }).fail(function (xhr) {
            const message = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data.message : 'Save failed.';
            $('#webform-save-status').text(message);
        }).always(function () { $button.prop('disabled', false); });
    });
    $('#webform-name,#webform-success-message,#webform-notification-email,#webform-submit-label,#webform-redirect-url,#webform-require-login,#webform-submission-limit,#webform-closed-message,#webform-style-preset,#webform-accent-color,#webform-button-text-color').on('input change', function () { dirty = true; });
    $(document).on('input change', '.webform-builder-wrap #webform-name,.webform-builder-wrap .webform-properties input,.webform-builder-wrap .webform-properties select,.webform-builder-wrap .webform-properties textarea', function () { scheduleHistory(); });
    $(document).on('click', '.webform-builder-wrap .webform-builder button,.webform-builder-wrap .webform-page-head button,.webform-builder-wrap .webform-field-picker button', function (event) {
        if ($(this).is('#webform-undo,#webform-redo,#webform-open-preview,#webform-open-embed,#webform-save,.webform-property-tabs button,.webform-device-switcher button,.webform-open-field-picker,.webform-field-picker-close')) return;
        if ($(this).is('.webform-stage-tab') && !$(event.target).closest('.webform-edit-stage,.webform-remove-stage').length) return;
        scheduleHistory(0);
    });
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
    $(document).on('input', '#webform-form-search', function () {
        const query = String($(this).val() || '').trim().toLowerCase();
        let visible = 0;
        $('.webform-forms-table tbody tr[data-form-title]').each(function () {
            const matches = !query || String($(this).data('form-title') || '').includes(query);
            $(this).toggle(matches);
            if (matches) visible += 1;
        });
        $('.webform-no-search-results').prop('hidden', visible !== 0);
    });
    $(document).on('click', '.webform-copy-shortcode', function () {
        const button = this;
        const shortcode = String($(button).data('shortcode') || '');
        copyToClipboard(button, shortcode);
    });
    $(document).on('click', '.webform-copy-embed', function () {
        const target = document.getElementById(String($(this).data('copy-target') || ''));
        copyToClipboard(this, target ? target.textContent : '');
    });
    let embedReturnFocus = null;
    function openEmbedDialog() {
        const panel = document.getElementById('webform-embed-panel');
        if (!panel || $('#webform-open-embed').prop('hidden')) return;
        embedReturnFocus = document.activeElement;
        panel.hidden = false;
        panel.setAttribute('aria-hidden', 'false');
        document.body.classList.add('webform-embed-is-open');
        const closeButton = panel.querySelector('.webform-editor-embed-close');
        if (closeButton) window.setTimeout(() => closeButton.focus(), 0);
    }
    function closeEmbedDialog() {
        const panel = document.getElementById('webform-embed-panel');
        if (!panel || panel.hidden) return;
        panel.hidden = true;
        panel.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('webform-embed-is-open');
        if (embedReturnFocus && typeof embedReturnFocus.focus === 'function') embedReturnFocus.focus();
        embedReturnFocus = null;
    }
    $(document).on('click', '#webform-open-embed', openEmbedDialog);
    $(document).on('click', '.webform-editor-embed-close,.webform-editor-embed-backdrop', closeEmbedDialog);
    $(document).on('keydown', function (event) {
        const panel = document.getElementById('webform-embed-panel');
        if (event.key === 'Escape' && panel && !panel.hidden) {
            event.preventDefault();
            closeEmbedDialog();
            return;
        }
        if (event.key === 'Tab' && panel && !panel.hidden) {
            const focusable = Array.from(panel.querySelectorAll('.webform-editor-embed-dialog button:not([disabled]),.webform-editor-embed-dialog [href],.webform-editor-embed-dialog input:not([disabled]),.webform-editor-embed-dialog select:not([disabled]),.webform-editor-embed-dialog textarea:not([disabled]),.webform-editor-embed-dialog [tabindex]:not([tabindex="-1"])')).filter(element => element.offsetParent !== null);
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });
    $(document).on('click', '.webform-delete', function () {
        if (!window.confirm('Move this form to the trash?')) return;
        const row = $(this).closest('tr');
        $.post(WebformAdmin.ajaxUrl, { action: 'formorbit_delete_form', nonce: WebformAdmin.nonce, form_id: $(this).data('id') }).done(function (response) {
            if (response.success) row.fadeOut(200, function () { row.remove(); });
        });
    });

    if ($('#webform-canvas').length) load();
})(jQuery);
