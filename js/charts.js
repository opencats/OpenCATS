/* OpenCATS shared Chart.js integration. See LICENSE.md. */
(function () {
    'use strict';
    const colours = ['#0d6efd', '#b65c00', '#198754'];

    function render(root) {
        root.querySelectorAll('.oc-chart[data-chart-state="pending"]').forEach(function (element) {
            if (element.closest('[hidden]')) return;
            const canvas = element.querySelector('canvas');
            if (!canvas) {
                element.dataset.chartState = 'empty';
                return;
            }
            if (!window.Chart) return;
            const plot = canvas.parentElement;
            try {
                const data = JSON.parse(element.querySelector('.oc-chart-data').textContent);
                const horizontal = data.horizontal;
                plot.style.height = horizontal ? Math.max(160, data.labels.length * 42 + 50) + 'px' : '240px';
                plot.hidden = false;
                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets.map(function (dataset, index) {
                            return Object.assign({}, dataset, {backgroundColor: colours[index % colours.length]});
                        })
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        indexAxis: horizontal ? 'y' : 'x',
                        plugins: {
                            legend: {display: data.datasets.length > 1, position: 'bottom'},
                            tooltip: {callbacks: {afterLabel: function (context) {
                                return data.percentages ? data.percentages[context.dataIndex] + '% ' + data.percentageLabel : '';
                            }}}
                        },
                        scales: {
                            [horizontal ? 'x' : 'y']: {beginAtZero: true, ticks: {precision: 0}},
                            [horizontal ? 'y' : 'x']: {grid: {display: false}, ticks: {
                                autoSkip: false, maxRotation: 0,
                                callback: function (value) {
                                    const label = String(this.getLabelForValue(value));
                                    // Wrap long categories instead of truncating business labels.
                                    const words = label.split(' '), lines = [''];
                                    const limit = horizontal ? 22 : 12;
                                    words.forEach(function (word) {
                                        const last = lines.length - 1;
                                        if (lines[last] && lines[last].length + word.length + 1 > limit) lines.push(word);
                                        else lines[last] += (lines[last] ? ' ' : '') + word;
                                    });
                                    return lines;
                                }
                            }}
                        }
                    }
                });
                element.querySelector('.oc-chart-status').hidden = true;
                const values = element.querySelector('details');
                if (values) values.open = false;
                element.dataset.chartState = 'ready';
            } catch (error) {
                const chart = Chart.getChart(canvas);
                if (chart) chart.destroy();
                plot.hidden = true;
                element.dataset.chartState = 'failed';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        render(document);
        let rangeRequest = 0;
        document.querySelectorAll('[data-chart-range]').forEach(function (control) {
            control.addEventListener('click', async function (event) {
                // Ordinary links remain the fallback when enhancement is unavailable.
                if (!window.fetch || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                event.preventDefault();
                const request = ++rangeRequest;
                const range = control.dataset.chartRange;
                const container = document.getElementById('homeGraph');
                container.setAttribute('aria-busy', 'true');
                try {
                    const response = await fetch(control.dataset.chartUrl, {credentials: 'same-origin'});
                    if (!response.ok) throw new Error('Range unavailable');
                    const markup = await response.text();
                    if (request !== rangeRequest) return;
                    const fragment = document.createElement('div');
                    fragment.innerHTML = markup;
                    // A redirected login/error page must use normal page navigation.
                    if (!fragment.querySelector('.oc-chart-data')) throw new Error('Range unavailable');
                    container.querySelectorAll('canvas').forEach(function (canvas) {
                        const chart = window.Chart && Chart.getChart(canvas);
                        if (chart) chart.destroy();
                    });
                    fragment.dataset.chartPeriod = range;
                    container.replaceChildren(fragment);
                    container.dataset.view = range;
                    document.querySelectorAll('[data-chart-range]').forEach(function (link) {
                        link.setAttribute('aria-current', link === control ? 'true' : 'false');
                        link.classList.toggle('active', link === control);
                    });
                    render(container);
                } catch (error) {
                    if (request === rangeRequest) window.location.href = control.href;
                } finally {
                    if (request === rangeRequest) container.removeAttribute('aria-busy');
                }
            });
        });
    });
    let printDetails = [];
    window.addEventListener('beforeprint', function () {
        printDetails = Array.from(document.querySelectorAll('.oc-chart-values')).filter(function (details) { return !details.open; });
        printDetails.forEach(function (details) { details.open = true; });
        if (window.Chart) Object.values(Chart.instances).forEach(function (chart) { chart.resize(); });
    });
    window.addEventListener('afterprint', function () {
        printDetails.forEach(function (details) { details.open = false; });
        printDetails = [];
        if (window.Chart) Object.values(Chart.instances).forEach(function (chart) { chart.resize(); });
    });
}());
