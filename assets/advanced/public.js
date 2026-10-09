(function () {
    'use strict';

    function fieldValue(form, id) {
        const inputs = Array.from(form.elements).filter(element => element.name === `fields[${id}]` || element.name === `fields[${id}][]`);
        return inputs.reduce((total, input) => {
            if ((input.type === 'checkbox' || input.type === 'radio') && !input.checked) return total;
            const raw = String(input.value || '');
            const candidate = raw.includes('|') ? raw.split('|').pop() : raw;
            const number = Number(candidate);
            return total + (Number.isFinite(number) ? number : 0);
        }, 0);
    }

    function evaluate(expression) {
        const tokens = expression.match(/\d*\.?\d+(?:e[+\-]?\d+)?|[a-z_][a-z0-9_]*|>=|<=|==|!=|&&|\|\||[()+\-*/%^,<>=!]/gi) || [];
        let position = 0;
        const peek = () => tokens[position];
        const take = value => peek() === value ? (position++, true) : false;
        function primary() {
            if (take('(')) {
                const value = comparison();
                take(')');
                return value;
            }
            const token = tokens[position++] || '0';
            if (!Number.isNaN(Number(token))) return Number(token);
            if (/^[a-z_]/i.test(token) && take('(')) {
                const args = [];
                if (peek() !== ')') {
                    do { args.push(comparison()); } while (take(','));
                }
                take(')');
                const name = token.toLowerCase();
                if (name === 'sum') return args.reduce((a, b) => a + b, 0);
                if (name === 'avg') return args.length ? args.reduce((a, b) => a + b, 0) / args.length : 0;
                if (name === 'min') return args.length ? Math.min(...args) : 0;
                if (name === 'max') return args.length ? Math.max(...args) : 0;
                if (name === 'round') return Number((args[0] || 0).toFixed(Math.max(0, Math.min(8, args[1] || 0))));
                if (name === 'ceil') return Math.ceil(args[0] || 0);
                if (name === 'floor') return Math.floor(args[0] || 0);
                if (name === 'abs') return Math.abs(args[0] || 0);
                if (name === 'sqrt') return Math.sqrt(Math.max(0, args[0] || 0));
                if (name === 'pow') return Math.pow(args[0] || 0, args[1] || 0);
                if (name === 'clamp') return Math.min(args[2] || 0, Math.max(args[1] || 0, args[0] || 0));
                if (name === 'if') return args[0] ? (args[1] || 0) : (args[2] || 0);
            }
            return 0;
        }
        function unary() {
            if (take('-')) return -unary();
            if (take('+')) return unary();
            if (take('!')) return unary() ? 0 : 1;
            return primary();
        }
        function power() {
            const left = unary();
            return take('^') ? Math.pow(left, power()) : left;
        }
        function term() {
            let value = power();
            while (['*', '/', '%'].includes(peek())) {
                const operator = tokens[position++];
                const right = power();
                value = operator === '*' ? value * right : operator === '/' ? (right === 0 ? 0 : value / right) : (right === 0 ? 0 : value % right);
            }
            return value;
        }
        function addition() {
            let value = term();
            while (['+', '-'].includes(peek())) {
                const operator = tokens[position++];
                const right = term();
                value = operator === '+' ? value + right : value - right;
            }
            return value;
        }
        function comparison() {
            let value = addition();
            while (['>', '<', '>=', '<=', '==', '!=', '&&', '||'].includes(peek())) {
                const operator = tokens[position++];
                const right = addition();
                if (operator === '>') value = value > right ? 1 : 0;
                else if (operator === '<') value = value < right ? 1 : 0;
                else if (operator === '>=') value = value >= right ? 1 : 0;
                else if (operator === '<=') value = value <= right ? 1 : 0;
                else if (operator === '==') value = value === right ? 1 : 0;
                else if (operator === '!=') value = value !== right ? 1 : 0;
                else if (operator === '&&') value = value && right ? 1 : 0;
                else value = value || right ? 1 : 0;
            }
            return Number.isFinite(value) ? value : 0;
        }
        return comparison();
    }

    function calculations(form) {
        form.querySelectorAll('[data-webform-calculation]').forEach(input => {
            const expression = input.dataset.webformCalculation.replace(/\{([a-zA-Z0-9_]+)\}/g, (match, id) => fieldValue(form, id));
            input.value = evaluate(expression).toFixed(Number(input.dataset.decimals || 2));
        });
    }

    function signature(canvas) {
        if (canvas.dataset.formorbitSignatureReady === '1') return;
        canvas.dataset.formorbitSignatureReady = '1';
        const context = canvas.getContext('2d');
        const target = document.getElementById(canvas.dataset.target);
        const field = canvas.closest('.webform-field-signature');
        const drawPanel = field && field.querySelector('.webform-signature-draw');
        const typePanel = field && field.querySelector('.webform-signature-type');
        const typed = field && field.querySelector('.webform-signature-typed');
        const typedPreview = field && field.querySelector('.webform-signature-typed-preview');
        let drawing = false;
        function point(event) {
            const rect = canvas.getBoundingClientRect();
            const source = event.touches ? event.touches[0] : event;
            return { x: (source.clientX - rect.left) * canvas.width / rect.width, y: (source.clientY - rect.top) * canvas.height / rect.height };
        }
        function start(event) { drawing = true; const p = point(event); context.beginPath(); context.moveTo(p.x, p.y); event.preventDefault(); }
        function move(event) { if (!drawing) return; const p = point(event); context.lineWidth = 2; context.lineCap = 'round'; context.strokeStyle = '#1d2327'; context.lineTo(p.x, p.y); context.stroke(); target.value = canvas.toDataURL('image/png'); event.preventDefault(); }
        function stop() { drawing = false; }
        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        window.addEventListener('mouseup', stop);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', move, { passive: false });
        canvas.addEventListener('touchend', stop);
        canvas.parentElement.querySelector('.webform-signature-clear').addEventListener('click', () => { context.clearRect(0, 0, canvas.width, canvas.height); target.value = ''; });
        if (typed) typed.addEventListener('input', function () {
            const value = typed.value.trim();
            if (typedPreview) typedPreview.textContent = value;
            context.clearRect(0, 0, canvas.width, canvas.height);
            if (!value) { target.value = ''; return; }
            context.fillStyle = '#1d2327';
            context.font = '46px cursive';
            context.textBaseline = 'middle';
            context.fillText(value, 24, canvas.height / 2, canvas.width - 48);
            target.value = canvas.toDataURL('image/png');
        });
        if (field) field.querySelectorAll('[data-signature-method]').forEach(function (button) {
            button.addEventListener('click', function () {
                const method = button.dataset.signatureMethod;
                field.querySelectorAll('[data-signature-method]').forEach(item => item.classList.toggle('is-active', item === button));
                if (drawPanel) drawPanel.hidden = method !== 'draw';
                if (typePanel) typePanel.hidden = method !== 'type';
                context.clearRect(0, 0, canvas.width, canvas.height);
                target.value = '';
                if (typed) typed.value = '';
                if (typedPreview) typedPreview.textContent = '';
                (method === 'type' ? typed : canvas)?.focus();
            });
        });
    }

    function groups(root) {
        root.querySelectorAll('.webform-field-group-marker').forEach(marker => {
            const group = document.createElement('fieldset');
            group.className = 'webform-pro-field-group ' + Array.from(marker.classList).filter(name => name !== 'webform-field-group-marker').join(' ');
            group.dataset.fieldId = marker.dataset.fieldId || '';
            group.style.setProperty('--webform-group-columns', marker.dataset.groupColumns || 2);
            const legend = document.createElement('legend');
            legend.textContent = marker.dataset.groupLabel || 'Group';
            group.appendChild(legend);
            let next = marker.nextElementSibling;
            const count = Number(marker.dataset.groupCount || 2);
            for (let index = 0; index < count && next; index++) {
                const moving = next;
                next = next.nextElementSibling;
                if (!moving.classList.contains('webform-actions')) group.appendChild(moving);
            }
            marker.replaceWith(group);
        });
    }

    function repeaters(root) {
        function renumberRows(field) {
            const rows = Array.from(field.querySelectorAll('.webform-repeater-row'));
            rows.forEach((row, index) => {
                const previous = row.dataset.rowIndex || String(index);
                row.dataset.rowIndex = String(index);
                const heading = row.querySelector('.webform-repeater-row-head strong');
                if (heading) heading.textContent = `Item ${index + 1}`;
                row.querySelectorAll('[name]').forEach(control => {
                    control.name = control.name.replace(new RegExp(`\\[${previous}\\](?=\\[)`), `[${index}]`);
                });
                row.querySelectorAll('[id]').forEach(control => {
                    const oldId = control.id;
                    const newId = oldId.replace(new RegExp(`-row-${previous}-`), `-row-${index}-`);
                    if (newId === oldId) return;
                    control.id = newId;
                    row.querySelectorAll('label[for]').forEach(label => {
                        if (label.htmlFor === oldId) label.htmlFor = newId;
                    });
                });
                row.querySelectorAll('.webform-signature-canvas[data-target]').forEach(canvas => {
                    const target = canvas.dataset.target.replace(new RegExp(`-row-${previous}-`), `-row-${index}-`);
                    if (target !== canvas.dataset.target) {
                        canvas.dataset.target = target;
                        delete canvas.dataset.formorbitSignatureReady;
                    }
                });
            });
        }

        function resetRow(row) {
            row.querySelectorAll('input,select,textarea').forEach(control => {
                if (control.type === 'checkbox' || control.type === 'radio') control.checked = false;
                else if (control.type === 'hidden') {
                    control.value = control.defaultValue;
                } else {
                    control.value = '';
                }
            });
        }

        root.addEventListener('click', function (event) {
            const field = event.target.closest('.webform-field-repeater');
            if (!field) return;
            const rows = field.querySelector('.webform-repeater-rows');
            if (event.target.closest('.webform-repeater-add')) {
                if (rows.children.length >= Number(field.dataset.max || 10)) return;
                const row = rows.firstElementChild.cloneNode(true);
                resetRow(row);
                rows.appendChild(row);
                renumberRows(field);
                row.querySelectorAll('.webform-signature-canvas').forEach(signature);
                const input = row.querySelector('input:not([type="hidden"]),select,textarea');
                if (input) input.focus();
            }
            if (event.target.closest('.webform-repeater-remove') && rows.children.length > Number(field.dataset.min || 1)) {
                event.target.closest('.webform-repeater-row').remove();
                renumberRows(field);
            }
            field.querySelector('.webform-repeater-add').disabled = rows.children.length >= Number(field.dataset.max || 10);
        });
    }

    function orderTotal(root, form) {
        const products = Array.from(form.querySelectorAll('.webform-field-product input[type="radio"]'));
        const prices = Array.from(form.querySelectorAll('.webform-field-price input[type="hidden"]'));
        const additions = Array.from(form.querySelectorAll('.webform-field-product_option select,.webform-field-shipping select'));
        const donations = Array.from(form.querySelectorAll('.webform-field-donation input[type="number"]'));
        const quantities = Array.from(form.querySelectorAll('.webform-field-quantity input[type="number"]'));
        if (!products.length && !prices.length && !additions.length && !donations.length) return;
        const actions = Array.from(form.querySelectorAll('.webform-actions')).pop();
        if (!actions) return;
        const total = document.createElement('div');
        total.className = 'webform-order-total';
        total.innerHTML = '<span>Order total</span><strong>USD 0.00</strong>';
        actions.parentNode.insertBefore(total, actions);
        function update() {
            let amount = prices.reduce((sum, input) => sum + (Number(input.value) || 0), 0);
            products.filter(input => input.checked).forEach(input => {
                const parts = input.value.split('|');
                amount += Number(parts[parts.length - 1]) || 0;
            });
            additions.forEach(input => { const parts = input.value.split('|'); amount += Number(parts[parts.length - 1]) || 0; });
            donations.forEach(input => { amount += Number(input.value) || 0; });
            const quantity = quantities.reduce((value, input) => value * Math.max(1, Number(input.value) || 1), 1);
            amount *= quantity;
            total.querySelector('strong').textContent = `USD ${amount.toFixed(2)}`;
            form.querySelectorAll('[data-formorbit-total]').forEach(field => { field.textContent = `${field.dataset.currency || 'USD'} ${amount.toFixed(2)}`; });
            total.hidden = amount <= 0;
        }
        form.addEventListener('change', update);
        form.addEventListener('input', update);
        update();
    }

    function restoreDraft(root, form) {
        const source = form.querySelector('.webform-draft-data');
        if (!source) return;
        let draft;
        try { draft = JSON.parse(source.textContent); } catch (error) { return; }
        Object.entries(draft.fields || {}).forEach(([id, value]) => {
            const repeater = form.querySelector(`.webform-field-repeater [name^="fields[${id}][]"],.webform-field-repeater [name^="fields[${id}]["]`)?.closest('.webform-field-repeater');
            if (repeater && Array.isArray(value)) {
                const add = repeater.querySelector('.webform-repeater-add');
                while (add && repeater.querySelectorAll('.webform-repeater-row').length < value.length) add.click();
            }
            function restoreValue(current, path) {
                if (current && typeof current === 'object' && !Array.isArray(current)) {
                    Object.entries(current).forEach(([key, childValue]) => restoreValue(childValue, `${path}[${key}]`));
                    return;
                }
                if (Array.isArray(current) && current.some(item => item && typeof item === 'object')) {
                    current.forEach((childValue, index) => restoreValue(childValue, `${path}[${index}]`));
                    return;
                }
                const controls = Array.from(form.elements).filter(element => element.name === path || element.name === `${path}[]`);
                controls.forEach(control => {
                    if (control.type === 'radio' || control.type === 'checkbox') control.checked = (Array.isArray(current) ? current : [current]).map(String).includes(String(control.value));
                    else control.value = Array.isArray(current) ? current.join(', ') : current;
                    control.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }
            restoreValue(value, `fields[${id}]`);
        });
        const stages = Array.from(root.querySelectorAll('.webform-stage'));
        const steps = Array.from(root.querySelectorAll('.webform-steps li'));
        const index = Math.min(stages.length - 1, Math.max(0, Number(draft.stage || 0)));
        stages.forEach((stage, stageIndex) => { stage.hidden = stageIndex !== index; stage.classList.toggle('is-active', stageIndex === index); });
        steps.forEach((step, stageIndex) => { step.classList.toggle('is-active', stageIndex === index); step.classList.toggle('is-complete', stageIndex < index); });
        const bar = root.querySelector('.webform-progress-bar');
        if (bar) bar.style.width = ((index + 1) / stages.length * 100) + '%';
        const message = form.querySelector('.webform-message');
        if (message) { message.className = 'webform-message is-success'; message.textContent = draft.message || 'Your saved progress has been restored.'; }
    }

    function saveProgress(root, form) {
        root.addEventListener('click', function (event) {
            const button = event.target.closest('.webform-save-progress');
            if (!button) return;
            const stage = button.closest('.webform-stage');
            const message = form.querySelector('.webform-message');
            const data = new FormData(form);
            data.set('action', 'formorbit_pro_save_progress');
            data.set('draft_stage', stage ? stage.dataset.stage || 0 : 0);
            data.set('current_url', window.location.href);
            button.disabled = true;
            if (message) { message.className = 'webform-message'; message.textContent = 'Saving progress…'; }
            const save = () => fetch(WebformPublic.ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin', cache: 'no-store' }).then(response => response.json());
            save().then(response => {
                if (response.success || !response.data || response.data.code !== 'expired_nonce') return response;
                return fetch(WebformPublic.ajaxUrl, {method:'POST', credentials:'same-origin', cache:'no-store', body:new URLSearchParams({action:'formorbit_submission_token',form_id:data.get('form_id')})})
                    .then(result => result.json()).then(result => {
                        if (!result.success || !result.data.nonce) throw new Error('Your session could not be refreshed. Please reload the page.');
                        data.set('nonce', result.data.nonce);
                        form.querySelector('[name="nonce"]').value = result.data.nonce;
                        return save();
                    });
            })
                .then(response => {
                    if (!response.success) throw new Error(response.data && response.data.message ? response.data.message : 'Progress could not be saved.');
                    const token = form.querySelector('[name="draft_token"]');
                    if (token) token.value = response.data.token;
                    if (message) {
                        message.className = 'webform-message is-success webform-draft-message';
                        message.textContent = response.data.message + ' ';
                        const link = document.createElement('a');
                        link.href = response.data.resume_url;
                        link.textContent = 'Copy or open resume link';
                        link.addEventListener('click', event => {
                            if (!navigator.clipboard) return;
                            event.preventDefault();
                            navigator.clipboard.writeText(response.data.resume_url).then(() => { link.textContent = 'Resume link copied'; });
                        });
                        message.appendChild(link);
                    }
                })
                .catch(error => { if (message) { message.className = 'webform-message is-error'; message.textContent = error.message; } })
                .finally(() => { button.disabled = false; });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.webform-public').forEach(root => {
            const form = root.querySelector('form');
            if (!form) return;
            groups(root);
            repeaters(root);
            orderTotal(root, form);
            saveProgress(root, form);
            form.querySelectorAll('.webform-signature-canvas').forEach(signature);
            form.addEventListener('input', () => calculations(form));
            form.addEventListener('change', () => calculations(form));
            calculations(form);
            restoreDraft(root, form);
        });
    });
})();
