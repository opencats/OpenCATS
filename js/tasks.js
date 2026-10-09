/* Shared contextual Task creation. Bootstrap owns the focus trap and keyboard dismissal. */
(function () {
    'use strict';

    let opening = false;

    function showNotice(panel, message, failed) {
        const notice = panel.querySelector('[data-task-notice]');
        notice.textContent = message;
        notice.classList.remove('d-none', 'alert-success', 'alert-danger');
        notice.classList.add(failed ? 'alert-danger' : 'alert-success');
    }

    function initialiseDates(container) {
        container.querySelectorAll('[data-task-date][data-date-valid="1"]').forEach(function (holder) {
            if (typeof DateInputForDOM !== 'function') return;
            const input = holder.querySelector('input');
            if (!input || input.type === 'hidden') return;
            const name = holder.dataset.dateName;
            const small = input.classList.contains('form-control-sm');
            const label = input.getAttribute('aria-label');
            holder.innerHTML = DateInputForDOM(name, false, holder.dataset.dateFormat, input.value, -1);
            holder.querySelectorAll('select, input[type="text"]').forEach(function (control) {
                control.classList.add(control.tagName === 'SELECT' ? 'form-select' : 'form-control');
                if (small) control.classList.add(control.tagName === 'SELECT' ? 'form-select-sm' : 'form-control-sm');
                const part = control.id.includes('_Month_') ? 'Month' : (control.id.includes('_Day_') ? 'Day' : 'Year');
                control.setAttribute('aria-label', label + ': ' + part);
            });
            const fieldLabel = container.querySelector('label[for="' + name + '"]');
            if (fieldLabel) fieldLabel.htmlFor = name + '_Month_ID';
        });
    }

    document.addEventListener('DOMContentLoaded', function () { initialiseDates(document); });

    document.addEventListener('click', async function (event) {
        const trigger = event.target.closest('[data-task-quick-add]');
        if (!trigger || !window.bootstrap) return;
        event.preventDefault();
        if (opening || document.getElementById('task-quick-add-modal')) return;
        opening = true;
        trigger.setAttribute('aria-busy', 'true');
        const panel = trigger.closest('[data-task-panel]');
        try {
            const response = await fetch(trigger.dataset.taskQuickAdd, {credentials: 'same-origin', cache: 'no-store'});
            if (!response.ok) throw new Error('Unable to open the Task form. Please reload the page.');
            if ((response.headers.get('content-type') || '').includes('application/json')) {
                const result = await response.json();
                throw new Error(result.message || 'Task access denied.');
            }
            const holder = document.createElement('div');
            holder.innerHTML = await response.text();
            const root = holder.querySelector('#task-quick-add-modal');
            if (!root) throw new Error('Your session may have expired. Reload the page to add a Task.');
            document.body.appendChild(root);
            initialiseDates(root);
            const modal = new bootstrap.Modal(root);
            const form = root.querySelector('form');
            const submit = form.querySelector('[type="submit"]');
            const errorBox = root.querySelector('[data-task-error]');
            let saving = false;

            root.addEventListener('shown.bs.modal', function () {
                form.elements.title.focus();
            });
            root.addEventListener('hide.bs.modal', function (event) {
                if (saving) event.preventDefault();
            });
            root.addEventListener('hidden.bs.modal', function () {
                modal.dispose();
                root.remove();
                trigger.focus();
            }, {once: true});

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                if (saving || !form.reportValidity()) return;
                saving = true;
                submit.disabled = true;
                form.setAttribute('aria-busy', 'true');
                errorBox.classList.add('d-none');
                try {
                    const response = await fetch(form.action, {
                        method: 'POST', body: new FormData(form), credentials: 'same-origin', cache: 'no-store'
                    });
                    if (!response.ok || !(response.headers.get('content-type') || '').includes('application/json')) {
                        throw new Error('Unable to confirm the save. Check your session and Task list before submitting again. Your entries have been kept.');
                    }
                    const result = await response.json();
                    if (!result.success) throw new Error(result.message || 'Unable to save the Task.');
                    if (typeof result.html === 'string') panel.querySelector('[data-task-rows]').innerHTML = result.html;
                    showNotice(panel, result.html === null ? 'Task saved. Reload this record to refresh its Task list.' : 'Task saved.', false);
                    saving = false;
                    modal.hide();
                } catch (error) {
                    errorBox.textContent = error.message || 'Unable to confirm the save. Check the Task list before submitting again.';
                    errorBox.classList.remove('d-none');
                    bootstrap.Collapse.getOrCreateInstance(root.querySelector('#quick-task-options'), {toggle: false}).show();
                    errorBox.focus();
                } finally {
                    saving = false;
                    submit.disabled = false;
                    form.removeAttribute('aria-busy');
                }
            });
            modal.show();
        } catch (error) {
            showNotice(panel, error.message || 'Unable to open the Task form.', true);
        } finally {
            opening = false;
            trigger.removeAttribute('aria-busy');
        }
    });
}());
