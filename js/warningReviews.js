/* One native Bootstrap modal, shared by personal warning review controls. */
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('warningReviewModal');
    if (!modal) return;
    modal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        if (!button || !button.dataset.review) { event.preventDefault(); return; }
        var values = JSON.parse(button.dataset.review);
        var action = button.dataset.reviewAction;
        var close = action === 'close';
        if ((action !== 'remind_later' && !close) || (close && values.warning_rule !== 'W07')) {
            event.preventDefault();
            return;
        }
        var form = modal.querySelector('form');
        form.reset();
        Object.keys(values).forEach(function (key) {
            var input = form.querySelector('input[name="' + key + '"]');
            if (input) input.value = values[key];
        });
        var bytes = new Uint8Array(16);
        window.crypto.getRandomValues(bytes);
        form.elements.requestID.value = Array.from(bytes, function (value) { return value.toString(16).padStart(2, '0'); }).join('');
        form.elements.reviewAction.value = action;
        modal.querySelector('#warningReviewDescription').textContent = button.dataset.reviewLabel;
        modal.querySelector('#warningReviewTitle').textContent = close ? 'Temporary Close' : 'Remind Later';
        modal.querySelector('[data-review-confirm]').textContent = close ? 'Confirm Temporary Close' : 'Confirm Remind Later';
        [['[data-review-reminder]', close], ['[data-review-close]', !close]].forEach(function (section) {
            var group = modal.querySelector(section[0]);
            group.hidden = section[1];
            group.querySelectorAll('input, select').forEach(function (input) { input.disabled = section[1]; });
        });
    });
    modal.addEventListener('shown.bs.modal', function () {
        modal.querySelector('select:not(:disabled)').focus();
    });
});
