(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.webform-public').forEach(function (root) {
            const form = root.querySelector('form');
            const stages = Array.from(root.querySelectorAll('.webform-stage'));
            const steps = Array.from(root.querySelectorAll('.webform-steps li'));
            let current = 0;

            function show(index) {
                current = index;
                stages.forEach((stage, i) => stage.classList.toggle('is-active', i === index));
                steps.forEach((step, i) => {
                    step.classList.toggle('is-active', i === index);
                    step.classList.toggle('is-complete', i < index);
                });
                const bar = root.querySelector('.webform-progress-bar');
                if (bar) bar.style.width = ((index + 1) / stages.length * 100) + '%';
                root.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            function validate(stage) {
                let valid = true;
                stage.querySelectorAll('.webform-error').forEach(el => el.textContent = '');
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
                                    const input = form.querySelector(`[name^="fields[${CSS.escape(id)}]"]`);
                                    if (input) input.closest('.webform-field').querySelector('.webform-error').textContent = response.data.errors[id];
                                });
                            }
                            throw new Error(response.data && response.data.message ? response.data.message : 'Submission failed.');
                        }
                        form.reset();
                        stages.forEach(stage => stage.hidden = true);
                        message.classList.add('is-success');
                        message.textContent = response.data.message;
                    })
                    .catch(function (error) {
                        message.classList.add('is-error');
                        message.textContent = error.message;
                    })
                    .finally(function () { button.disabled = false; });
            });
            show(0);
        });
    });
})();
