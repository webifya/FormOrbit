(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.webform-public').forEach(function (root) {
            const form = root.querySelector('form');
            const stages = Array.from(root.querySelectorAll('.webform-stage'));
            const steps = Array.from(root.querySelectorAll('.webform-steps li'));
            let current = 0;

            function fieldValue(id) {
                const elements = Array.from(form.elements).filter(element => element.name === `fields[${id}]` || element.name === `fields[${id}][]`);
                if (!elements.length) return '';
                if (['radio', 'checkbox'].includes(elements[0].type)) return elements.filter(element => element.checked).map(element => element.value).join(', ');
                return elements[0].value || '';
            }

            function applyConditions() {
                root.querySelectorAll('[data-condition]').forEach(function (field) {
                    let condition;
                    try { condition = JSON.parse(field.dataset.condition); } catch (error) { return; }
                    const actual = fieldValue(condition.field_id);
                    const expected = condition.value || '';
                    let visible = actual === expected;
                    if (condition.operator === 'not_equals') visible = actual !== expected;
                    if (condition.operator === 'contains') visible = actual.toLowerCase().includes(expected.toLowerCase());
                    if (condition.operator === 'starts_with') visible = actual.toLowerCase().startsWith(expected.toLowerCase());
                    if (condition.operator === 'ends_with') visible = actual.toLowerCase().endsWith(expected.toLowerCase());
                    if (condition.operator === 'greater_than') visible = Number(actual) > Number(expected);
                    if (condition.operator === 'less_than') visible = Number(actual) < Number(expected);
                    if (condition.operator === 'not_empty') visible = actual !== '';
                    if (condition.operator === 'empty') visible = actual === '';
                    field.hidden = !visible;
                    field.querySelectorAll('input,select,textarea').forEach(element => element.disabled = !visible);
                });
            }

            function show(index) {
                current = index;
                stages.forEach((stage, i) => {
                    stage.classList.toggle('is-active', i === index);
                    stage.hidden = i !== index;
                });
                steps.forEach((step, i) => {
                    step.classList.toggle('is-active', i === index);
                    step.classList.toggle('is-complete', i < index);
                    if (i === index) step.setAttribute('aria-current', 'step');
                    else step.removeAttribute('aria-current');
                });
                const bar = root.querySelector('.webform-progress-bar');
                if (bar) bar.style.width = ((index + 1) / stages.length * 100) + '%';
                const progress = root.querySelector('.webform-progress');
                if (progress) progress.setAttribute('aria-valuenow', index + 1);
                root.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            function validate(stage) {
                let valid = true;
                stage.querySelectorAll('.webform-error').forEach(el => el.textContent = '');
                stage.querySelectorAll('.webform-field-checkbox').forEach(function (field) {
                    const boxes = Array.from(field.querySelectorAll('input[type="checkbox"]'));
                    if (field.querySelector('[data-required="true"]') && !boxes.some(box => box.checked)) {
                        valid = false;
                        field.querySelector('.webform-error').textContent = 'Select at least one option.';
                    }
                });
                stage.querySelectorAll(':invalid').forEach(function (input) {
                    valid = false;
                    const field = input.closest('.webform-field');
                    if (field) field.querySelector('.webform-error').textContent = input.validationMessage;
                });
                if (!valid) stage.querySelector(':invalid').focus();
                return valid;
            }

            root.addEventListener('click', function (event) {
                if (event.target.closest('.webform-next') && validate(stages[current])) show(current + 1);
                if (event.target.closest('.webform-prev')) show(current - 1);
            });
            form.addEventListener('input', applyConditions);
            form.addEventListener('change', applyConditions);
            form.addEventListener('input', function (event) {
                if (event.target.type === 'range') {
                    const output = event.target.parentElement.querySelector('.webform-slider-value');
                    if (output) output.value = event.target.value;
                }
                if (event.target.classList.contains('webform-phone-number')) {
                    const country = event.target.closest('.webform-phone-control')?.querySelector('.webform-phone-country')?.value || event.target.closest('.webform-phone-control')?.dataset.defaultCountry || 'US';
                    const digits = event.target.value.replace(/\D/g, '').slice(0, 15);
                    if (country === 'US' || country === 'CA') {
                        event.target.value = digits.length > 6 ? `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6, 10)}` : (digits.length > 3 ? `(${digits.slice(0, 3)}) ${digits.slice(3)}` : digits);
                    } else {
                        event.target.value = digits.replace(/(\d{3})(?=\d)/g, '$1 ').trim();
                    }
                }
            });

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                if (!validate(stages[current])) return;
                const button = form.querySelector('.webform-submit');
                const message = form.querySelector('.webform-message');
                button.disabled = true;
                message.className = 'webform-message';
                message.textContent = 'Submitting…';
                fetch(WebformPublic.ajaxUrl, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
                    .then(response => response.json())
                    .then(function (response) {
                        if (!response.success) {
                            if (response.data && response.data.errors) {
                                Object.keys(response.data.errors).forEach(function (id) {
                                    const input = Array.from(form.elements).find(element => element.name === `fields[${id}]` || element.name === `fields[${id}][]`);
                                    if (input) input.closest('.webform-field').querySelector('.webform-error').textContent = response.data.errors[id];
                                });
                            }
                            throw new Error(response.data && response.data.message ? response.data.message : 'Submission failed.');
                        }
                        form.reset();
                        if (response.data.redirect_url) {
                            window.location.assign(response.data.redirect_url);
                            return;
                        }
                        stages.forEach(stage => stage.hidden = true);
                        message.classList.add('is-success');
                        if (response.data.message_html) message.innerHTML = response.data.message_html;
                        else message.textContent = response.data.message;
                        if (response.data.quiz) {
                            const quiz = document.createElement('strong');
                            quiz.className = 'webform-quiz-result';
                            quiz.textContent = `Score: ${response.data.quiz.score} / ${response.data.quiz.total}`;
                            message.appendChild(quiz);
                        }
                        (response.data.polls || []).forEach(function (poll) {
                            const result = document.createElement('div');
                            result.className = 'webform-poll-result';
                            const total = Object.values(poll.counts).reduce((sum, count) => sum + Number(count), 0);
                            const title = document.createElement('strong');
                            title.textContent = poll.label;
                            result.appendChild(title);
                            Object.keys(poll.counts).forEach(function (option) {
                                const line = document.createElement('span');
                                const percent = total ? Math.round(Number(poll.counts[option]) / total * 100) : 0;
                                line.textContent = `${option}: ${percent}% (${poll.counts[option]})`;
                                result.appendChild(line);
                            });
                            message.appendChild(result);
                        });
                    })
                    .catch(function (error) {
                        message.classList.add('is-error');
                        message.textContent = error.message;
                    })
                    .finally(function () { button.disabled = false; });
            });
            applyConditions();
            show(0);
        });
    });
})();
