/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

/* Home presentation state only. Native handlers own every write and warning calculation. */
function trackTableHighlight() { return; }

document.addEventListener('DOMContentLoaded', function () {
    var queue = document.querySelector('[data-home-queue]');
    if (!queue) return;
    var params = new URLSearchParams(location.search);
    var storageKey = 'opencats-home:' + location.pathname + ':' + (params.get('a') || 'home') +
        ':' + (params.get('filter') || 'all') + ':' + (params.get('page') || '1');
    var complete = document.getElementById('homeTaskComplete');
    complete.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        if (!button || !button.dataset.homeTask) { event.preventDefault(); return; }
        complete.querySelector('[name="taskID"]').value = button.dataset.homeTask;
        complete.querySelector('[name="homeContext"]').value = button.dataset.homeContext;
        complete.querySelector('[data-home-task-title]').textContent = button.dataset.homeTitle;
    });
    complete.addEventListener('shown.bs.modal', function () {
        complete.querySelector('[data-bs-dismiss="modal"]').focus();
    });

    var chrome = document.querySelector('.home-chrome');
    if (chrome && chrome.querySelector('#headerBlock > div') && chrome.querySelector('#topRight') && chrome.querySelector('#header') && chrome.querySelector('#quickSearchForm')) {
        var headerBox = chrome.querySelector('#headerBlock > div');
        var navigation = chrome.querySelector('#header');
        var search = chrome.querySelector('#MRUPanel');
        var account = chrome.querySelector('#topRight');
        var navPlace = document.createComment('Native navigation position');
        var searchPlace = document.createComment('Native search position');
        navigation.before(navPlace);
        search.before(searchPlace);
        function layoutChrome() {
            var links = Array.from(navigation.querySelectorAll('#primary > li'));
            var navWidth = links.reduce(function (sum, item) { return sum + item.getBoundingClientRect().width; }, 0) + Math.max(0, links.length - 1) * 4 + 12;
            var accountWidth = Array.from(account.children).reduce(function (sum, item) { return sum + item.getBoundingClientRect().width; }, 0) + 16;
            var recent = search.querySelector('#MRUBlock');
            var hasRecent = recent && recent.textContent.trim();
            var required = 160 + navWidth + (hasRecent ? 0 : search.querySelector('form').getBoundingClientRect().width) + accountWidth + 48;
            var padding = parseFloat(getComputedStyle(headerBox).paddingInlineStart) * 2;
            var single = window.innerWidth >= 1400 && required <= chrome.clientWidth - padding;
            var focused = document.activeElement;
            if (single !== chrome.classList.contains('home-chrome-single')) {
                if (single) {
                    account.before(navigation);
                    if (!hasRecent) account.before(search);
                }
                else { navPlace.after(navigation); searchPlace.after(search); }
                chrome.classList.toggle('home-chrome-single', single);
                if (chrome.contains(focused)) focused.focus({preventScroll: true});
            }
        }
        new ResizeObserver(layoutChrome).observe(chrome);
        window.addEventListener('resize', layoutChrome);
        layoutChrome();
    }

    var workspace = document.querySelector('.home-workspace');
    var rail = document.getElementById('homeContextRail');
    var contexts = Array.from(document.querySelectorAll('[data-home-context]'));
    var activeContext = null;
    function contextRow(panel) { return document.getElementById(panel.dataset.homeRow); }
    function contextTrigger(panel) { return contextRow(panel).querySelector('[data-home-context-toggle]'); }
    function loadActivity(panel) {
        var section = panel.querySelector('[data-home-activity-url]');
        if (!section || section.dataset.homeActivityLoaded) return;
        section.dataset.homeActivityLoaded = '1';
        var content = section.querySelector('[data-home-activity-content]');
        content.setAttribute('aria-busy', 'true');
        content.textContent = 'Loading recorded Activity…';
        fetch(section.dataset.homeActivityUrl, {credentials: 'same-origin', headers: {'Accept': 'text/html'}})
            .then(function (response) {
                if (!response.ok || response.headers.get('X-Home-Activity') !== '1') throw new Error('Activity unavailable');
                return response.text();
            }).then(function (html) { content.innerHTML = html; })
            .catch(function () { content.textContent = 'Recent Activity unavailable. Use the linked record to view Activity.'; })
            .finally(function () { content.removeAttribute('aria-busy'); });
    }
    function layoutContext() {
        if (!workspace || !rail || !activeContext) return;
        var useRail = window.innerWidth >= 1400 && workspace.clientWidth - 376 >= 960;
        var destination = useRail ? rail : contextRow(activeContext);
        var focused = document.activeElement;
        var actions = activeContext.querySelector('nav[aria-label="Task actions"]');
        var facts = activeContext.querySelector('.home-context-facts');
        if (actions && facts) {
            // Keep reading/tab order aligned with the layout, using the original action nodes.
            if (useRail) {
                var anchor = activeContext.querySelector('.home-description') || facts;
                if (anchor.nextElementSibling !== actions) anchor.after(actions);
            } else if (facts.previousElementSibling !== actions) facts.before(actions);
        }
        if (useRail) rail.hidden = false;
        if (activeContext.parentElement !== destination) {
            destination.appendChild(activeContext);
        }
        rail.hidden = !useRail;
        workspace.classList.toggle('home-has-rail', useRail);
        if (activeContext.contains(focused)) focused.focus({preventScroll: true});
        loadActivity(activeContext);
    }
    function selectContext(panel) {
        contexts.forEach(function (other) {
            if (other !== panel && other.classList.contains('show')) bootstrap.Collapse.getOrCreateInstance(other, {toggle: false}).hide();
        });
        activeContext = panel;
        var row = contextRow(panel);
        row.classList.add('home-context-open');
        contextTrigger(panel).setAttribute('aria-expanded', 'true');
        contextTrigger(panel).querySelector('[data-home-context-label]').textContent = 'Context';
        row.querySelector('[data-home-context-jump]').hidden = false;
    }
    contexts.forEach(function (panel) {
        panel.addEventListener('show.bs.collapse', function () { selectContext(panel); });
        panel.addEventListener('shown.bs.collapse', function () {
            // A second trigger may be activated while the first is still expanding.
            if (panel !== activeContext) bootstrap.Collapse.getOrCreateInstance(panel, {toggle: false}).hide();
            else layoutContext();
        });
        panel.addEventListener('hide.bs.collapse', function () {
            if (panel.contains(document.activeElement)) contextTrigger(panel).focus({preventScroll: true});
            var row = contextRow(panel);
            row.classList.remove('home-context-open');
            contextTrigger(panel).querySelector('[data-home-context-label]').textContent = 'Context';
            row.querySelector('[data-home-context-jump]').hidden = true;
        });
        panel.addEventListener('hidden.bs.collapse', function () {
            contextRow(panel).appendChild(panel);
            if (activeContext === panel) {
                activeContext = null;
                workspace.classList.remove('home-has-rail');
                rail.hidden = true;
            }
        });
    });
    if (workspace && rail) new ResizeObserver(layoutContext).observe(workspace);
    window.addEventListener('resize', layoutContext);
    document.addEventListener('click', function (event) {
        var jump = event.target.closest('[data-home-context-jump]');
        if (!jump) return;
        event.preventDefault();
        document.getElementById(jump.hash.substring(1)).focus();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape' || event.defaultPrevented || document.querySelector('.modal.show, .dropdown-menu.show')) return;
        var panel = event.target.closest('[data-home-context].show');
        if (panel) { event.preventDefault(); bootstrap.Collapse.getOrCreateInstance(panel, {toggle: false}).hide(); }
    });

    function remember() {
        var open = Array.from(document.querySelectorAll('[data-home-context].show, #suppressedWarnings.show')).map(function (panel) { return panel.id; });
        try { sessionStorage.setItem(storageKey, JSON.stringify({open: open, scroll: window.scrollY})); } catch (error) { /* Storage is optional. */ }
    }
    var saved;
    try { saved = JSON.parse(sessionStorage.getItem(storageKey)); sessionStorage.removeItem(storageKey); } catch (error) { saved = null; }
    if (saved && Array.isArray(saved.open)) {
        saved.open.forEach(function (id) {
            var panel = document.getElementById(id);
            if (panel && panel.matches('[data-home-context], #suppressedWarnings')) {
                panel.classList.add('show');
                if (panel.matches('[data-home-context]')) { selectContext(panel); layoutContext(); }
                var trigger = document.querySelector('[aria-controls="' + panel.id + '"]');
                if (trigger) trigger.setAttribute('aria-expanded', 'true');
            }
        });
        if (Number.isFinite(saved.scroll)) window.scrollTo(0, saved.scroll);
    }
    var target = document.getElementById(location.hash.substring(1));
    if (target && target.id === 'suppressedWarnings') {
        bootstrap.Collapse.getOrCreateInstance(target, {toggle: false}).show();
    } else if (target && target.id.startsWith('priority-')) {
        var context = document.getElementById('context-' + target.id);
        if (context) bootstrap.Collapse.getOrCreateInstance(context, {toggle: false}).show();
        target.focus({preventScroll: true});
    }
    document.addEventListener('click', function (event) {
        if (event.target.closest('a')) remember();
    });
    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form.closest('#main, #homeTaskComplete, #warningReviewModal')) return;
        if (form.dataset.homeSubmitting === '1') { event.preventDefault(); return; }
        remember();
        form.dataset.homeSubmitting = '1';
        form.setAttribute('aria-busy', 'true');
        // Keep the submitter enabled: its name/value is part of native review POSTs.
        form.querySelectorAll('[type="submit"], button[name="reviewAction"]').forEach(function (button) { button.setAttribute('aria-disabled', 'true'); });
    });
    window.addEventListener('pagehide', remember);
});
window.addEventListener('pageshow', function (event) {
    // Returning from a native record action must re-read source facts, including BFCache restores.
    if (event.persisted && document.querySelector('[data-home-queue]')) location.reload();
});
