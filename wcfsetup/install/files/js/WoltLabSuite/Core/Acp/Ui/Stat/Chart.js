/**
 * Renders the daily statistics as a line chart with a table view.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "chart.js", "WoltLabSuite/Core/Api/Stats/GetStats", "WoltLabSuite/Core/Component/Chart/DateAdapter", "WoltLabSuite/Core/Date/Picker", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/StringUtil"], function (require, exports, tslib_1, chart_js_1, GetStats_1, DateAdapter_1, Picker_1, Language_1, StringUtil_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Picker_1 = tslib_1.__importDefault(Picker_1);
    // Validated as a set for both color schemes against the ACP content surface,
    // the order keeps adjacent series apart for color vision deficiencies.
    const PALETTE = {
        light: ["#2a78d6", "#eb6834", "#1baf7a", "#eda100", "#e87ba4", "#008300", "#6250d6", "#e34948"],
        dark: ["#3987e5", "#d95926", "#199e70", "#c98500", "#d55181", "#008300", "#9085e9", "#e66767"],
    };
    // Distinguishes series that share a color once there are more types than colors.
    const POINT_STYLES = ["circle", "rectRot", "triangle"];
    // Markers on every point turn into a solid band for long series.
    const MAX_POINTS_WITH_MARKERS = 60;
    const TIME_UNIT = {
        daily: { unit: false, minUnit: "day", tooltipFormat: "date" },
        // Chart.js never picks weeks for automatically sized ticks.
        weekly: { unit: "week", minUnit: "week", tooltipFormat: "date" },
        monthly: { unit: false, minUnit: "month", tooltipFormat: "month" },
        yearly: { unit: false, minUnit: "year", tooltipFormat: "year" },
    };
    let chart;
    let requestCounter = 0;
    function getColorScheme() {
        return document.documentElement.dataset.colorScheme === "dark" ? "dark" : "light";
    }
    function getToken(style, name) {
        return style.getPropertyValue(`--${name}`).trim().replace(/\s+/g, " ");
    }
    function getSlot(objectTypeID) {
        const checkboxes = Array.from(document.querySelectorAll('input[name="objectTypeID"]'));
        return Math.max(0, checkboxes.findIndex((checkbox) => checkbox.value === objectTypeID.toString()));
    }
    function applyTheme(chart) {
        const style = window.getComputedStyle(document.documentElement);
        const surface = getToken(style, "wcfContentContainerBackground");
        const text = getToken(style, "wcfContentText");
        const dimmedText = getToken(style, "wcfContentDimmedText");
        const grid = getToken(style, "wcfContentBorderInner");
        const font = {
            family: getToken(style, "wcfFontFamily"),
            size: parseInt(getToken(style, "wcfFontSizeSmall"), 10),
        };
        const palette = PALETTE[getColorScheme()];
        const { options } = chart;
        for (const axis of [options.scales.x, options.scales.y]) {
            axis.ticks.color = dimmedText;
            axis.ticks.font = font;
            axis.border.color = grid;
            axis.grid.color = grid;
        }
        options.plugins.legend.labels.color = text;
        options.plugins.legend.labels.font = font;
        options.plugins.tooltip.backgroundColor = getToken(style, "wcfTooltipBackground");
        options.plugins.tooltip.titleColor = getToken(style, "wcfTooltipText");
        options.plugins.tooltip.bodyColor = getToken(style, "wcfTooltipText");
        options.plugins.tooltip.titleFont = { ...font, weight: "bold" };
        options.plugins.tooltip.bodyFont = font;
        for (const dataset of chart.data.datasets) {
            const slot = getSlot(dataset.objectTypeID);
            const color = palette[slot % palette.length];
            dataset.borderColor = color;
            dataset.backgroundColor = color;
            dataset.pointBackgroundColor = color;
            dataset.pointBorderColor = surface;
            dataset.pointStyle = POINT_STYLES[Math.floor(slot / palette.length) % POINT_STYLES.length];
        }
    }
    function toTimestamp(date) {
        const [year, month, day] = date.split("-").map((part) => parseInt(part, 10));
        return Date.UTC(year, month - 1, day);
    }
    function createDatasets(series) {
        return series.map((entry) => {
            const pointRadius = entry.data.length > MAX_POINTS_WITH_MARKERS ? 0 : 5;
            return {
                objectTypeID: entry.objectTypeID,
                label: entry.label,
                data: entry.data.map(({ date, value }) => ({ x: toTimestamp(date), y: value })),
                borderWidth: 2,
                pointRadius,
                pointHoverRadius: 7,
                pointBorderWidth: 2,
                pointHitRadius: 8,
            };
        });
    }
    function createChart(container) {
        const canvas = document.createElement("canvas");
        canvas.setAttribute("aria-hidden", "true");
        container.append(canvas);
        const compactNumber = new Intl.NumberFormat(document.documentElement.lang, {
            notation: "compact",
            maximumFractionDigits: 1,
        });
        return new chart_js_1.Chart(canvas, {
            type: "line",
            data: { datasets: [] },
            options: {
                animation: window.matchMedia("(prefers-reduced-motion: reduce)").matches ? false : undefined,
                maintainAspectRatio: false,
                interaction: {
                    mode: "index",
                    intersect: false,
                },
                scales: {
                    x: {
                        type: "time",
                        adapters: {
                            date: { utc: true },
                        },
                        border: {},
                        grid: { display: false },
                        ticks: { maxRotation: 0 },
                    },
                    y: {
                        beginAtZero: true,
                        border: { display: false },
                        grid: {},
                        ticks: {
                            precision: 0,
                            callback: (value) => compactNumber.format(Number(value)),
                        },
                    },
                },
                plugins: {
                    legend: {
                        labels: {
                            usePointStyle: true,
                        },
                    },
                    tooltip: {
                        usePointStyle: true,
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${(0, StringUtil_1.formatNumeric)(context.parsed.y ?? 0)}`,
                        },
                    },
                },
            },
        });
    }
    function renderTable(table, series, tooltipFormat) {
        const adapter = new chart_js_1._adapters._date({ utc: true });
        const dates = Array.from(new Set(series.flatMap((entry) => entry.data.map(({ date }) => date)))).sort();
        const values = series.map((entry) => new Map(entry.data.map(({ date, value }) => [date, value])));
        const headerRow = document.createElement("tr");
        for (const [index, label] of [(0, Language_1.getPhrase)("wcf.global.date"), ...series.map((entry) => entry.label)].entries()) {
            const th = document.createElement("th");
            th.scope = "col";
            th.className = index === 0 ? "columnText" : "columnDigits";
            th.textContent = label;
            headerRow.append(th);
        }
        const tbody = document.createElement("tbody");
        for (const date of dates) {
            const row = document.createElement("tr");
            const dateCell = document.createElement("td");
            dateCell.className = "columnText";
            dateCell.textContent = adapter.format(toTimestamp(date), tooltipFormat);
            row.append(dateCell);
            for (const valuesByDate of values) {
                const td = document.createElement("td");
                td.className = "columnDigits";
                const value = valuesByDate.get(date);
                if (value !== undefined) {
                    td.textContent = (0, StringUtil_1.formatNumeric)(value);
                }
                row.append(td);
            }
            tbody.append(row);
        }
        const thead = document.createElement("thead");
        thead.append(headerRow);
        table.replaceChildren(thead, tbody);
    }
    async function refresh(container, noData, tableView) {
        const objectTypeIDs = Array.from(document.querySelectorAll('input[name="objectTypeID"]:checked'), (checkbox) => parseInt(checkbox.value, 10));
        if (objectTypeIDs.length === 0) {
            return;
        }
        const dateGrouping = document.querySelector('input[name="dateGrouping"]:checked')
            .value;
        const value = document.querySelector('input[name="value"]:checked').value;
        const requestID = ++requestCounter;
        const series = (await (0, GetStats_1.getStats)(Picker_1.default.getValue("startDate"), Picker_1.default.getValue("endDate"), objectTypeIDs, dateGrouping, value)).unwrap();
        // A newer request was started while this one was pending.
        if (requestID !== requestCounter) {
            return;
        }
        const hasData = series.length > 0;
        container.hidden = !hasData;
        tableView.hidden = !hasData;
        noData.hidden = hasData;
        if (!hasData) {
            return;
        }
        if (chart === undefined) {
            chart = createChart(container);
        }
        const { unit, minUnit, tooltipFormat } = TIME_UNIT[dateGrouping];
        const x = chart.options.scales.x;
        if (x.type === "time") {
            x.time = { ...x.time, unit, minUnit, tooltipFormat };
        }
        chart.data.datasets = createDatasets(series);
        applyTheme(chart);
        chart.update();
        renderTable(tableView.querySelector("table"), series, tooltipFormat);
    }
    function setup() {
        (0, DateAdapter_1.setup)();
        const container = document.getElementById("statChart");
        const noData = document.getElementById("statNoData");
        const tableView = document.getElementById("statTable");
        document.getElementById("statRefreshButton").addEventListener("click", () => {
            void refresh(container, noData, tableView);
            document.querySelector(".contentHeader > .contentTitle").scrollIntoView({ behavior: "smooth" });
        });
        new MutationObserver(() => {
            if (chart !== undefined) {
                applyTheme(chart);
                // Not `update("none")`, which keeps the previously resolved point colors.
                chart.update();
            }
        }).observe(document.documentElement, { attributeFilter: ["data-color-scheme"] });
        void refresh(container, noData, tableView);
    }
});
