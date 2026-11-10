import {
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    BarController,
    BarElement,
    CategoryScale,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
);

const charts = window.dashboardCharts;

if (charts) {
    Chart.defaults.font.family = "'Nunito', sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#78716c';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.boxWidth = 8;
    Chart.defaults.plugins.legend.labels.padding = 14;

    const palette = ['#eab308', '#f59e0b', '#ca8a04', '#facc15', '#84cc16', '#14b8a6'];

    const rupiah = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(value)}`;

    const compactRupiah = (value) =>
        `Rp ${new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 }).format(value)}`;

    const grid = { color: 'rgba(214, 211, 209, 0.55)' };

    const moneyTick = (value) => compactRupiah(value);

    const monthly = document.getElementById('chartMonthly');

    if (monthly) {
        new Chart(monthly, {
            type: 'bar',
            data: {
                labels: charts.monthly.labels,
                datasets: [
                    {
                        label: 'Pendapatan',
                        data: charts.monthly.revenue,
                        backgroundColor: 'rgba(234, 179, 8, 0.78)',
                        hoverBackgroundColor: '#ca8a04',
                        borderRadius: 6,
                        maxBarThickness: 24,
                        yAxisID: 'y',
                    },
                    {
                        type: 'line',
                        label: 'Pesanan',
                        data: charts.monthly.orders,
                        borderColor: '#b45309',
                        backgroundColor: '#b45309',
                        borderWidth: 2,
                        pointRadius: 2,
                        pointHoverRadius: 4,
                        tension: 0.35,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', align: 'end' },
                    tooltip: {
                        callbacks: {
                            label: (context) =>
                                context.dataset.yAxisID === 'y1'
                                    ? `${context.dataset.label}: ${context.parsed.y} pesanan`
                                    : `${context.dataset.label}: ${rupiah(context.parsed.y)}`,
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 6 } },
                    y: {
                        beginAtZero: true,
                        grid,
                        border: { display: false },
                        ticks: { callback: moneyTick },
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { display: false },
                        border: { display: false },
                        ticks: { precision: 0 },
                    },
                },
            },
        });
    }

    const yearly = document.getElementById('chartYearly');

    if (yearly) {
        new Chart(yearly, {
            type: 'bar',
            data: {
                labels: charts.yearly.labels,
                datasets: [
                    {
                        label: 'Pendapatan',
                        data: charts.yearly.revenue,
                        backgroundColor: 'rgba(245, 158, 11, 0.8)',
                        hoverBackgroundColor: '#d97706',
                        borderRadius: 6,
                        maxBarThickness: 36,
                        yAxisID: 'y',
                    },
                    {
                        type: 'line',
                        label: 'Pesanan',
                        data: charts.yearly.orders,
                        borderColor: '#a16207',
                        backgroundColor: '#a16207',
                        borderWidth: 2,
                        pointRadius: 2,
                        pointHoverRadius: 4,
                        tension: 0.35,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', align: 'end' },
                    tooltip: {
                        callbacks: {
                            label: (context) =>
                                context.dataset.yAxisID === 'y1'
                                    ? `${context.dataset.label}: ${context.parsed.y} pesanan`
                                    : `${context.dataset.label}: ${rupiah(context.parsed.y)}`,
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        grid,
                        border: { display: false },
                        ticks: { callback: moneyTick },
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { display: false },
                        border: { display: false },
                        ticks: { precision: 0 },
                    },
                },
            },
        });
    }

    const categories = document.getElementById('chartCategories');

    if (categories) {
        new Chart(categories, {
            type: 'bar',
            data: {
                labels: charts.categories.labels,
                datasets: [
                    {
                        label: 'Terjual (pcs)',
                        data: charts.categories.quantity,
                        backgroundColor: charts.categories.labels.map((_, index) => palette[index % palette.length]),
                        borderRadius: 6,
                        maxBarThickness: 22,
                    },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) =>
                                `${context.parsed.x} pcs • ${rupiah(charts.categories.revenue[context.dataIndex] ?? 0)}`,
                        },
                    },
                },
                scales: {
                    x: { beginAtZero: true, grid, border: { display: false }, ticks: { precision: 0 } },
                    y: { grid: { display: false }, ticks: { autoSkip: false } },
                },
            },
        });
    }

    const margin = document.getElementById('chartMargin');

    if (margin) {
        new Chart(margin, {
            type: 'bar',
            data: {
                labels: charts.margin.labels,
                datasets: [
                    {
                        label: 'Pendapatan produk',
                        data: charts.margin.revenue,
                        backgroundColor: 'rgba(245, 158, 11, 0.8)',
                        borderRadius: 5,
                        maxBarThickness: 18,
                    },
                    {
                        label: 'Modal',
                        data: charts.margin.cost,
                        backgroundColor: 'rgba(202, 138, 4, 0.75)',
                        borderRadius: 5,
                        maxBarThickness: 18,
                    },
                    {
                        label: 'Margin',
                        data: charts.margin.margin,
                        backgroundColor: 'rgba(16, 185, 129, 0.82)',
                        borderRadius: 5,
                        maxBarThickness: 18,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', align: 'end' },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${rupiah(context.parsed.y)}`,
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 6 } },
                    y: {
                        beginAtZero: true,
                        grid,
                        border: { display: false },
                        ticks: { callback: moneyTick },
                    },
                },
            },
        });
    }

    const forecast = document.getElementById('chartForecast');

    if (forecast) {
        new Chart(forecast, {
            type: 'line',
            data: {
                labels: charts.forecast.labels,
                datasets: charts.forecast.datasets.map((dataset, index) => ({
                    label: dataset.label,
                    data: dataset.data,
                    borderColor: palette[index % palette.length],
                    backgroundColor: palette[index % palette.length],
                    borderWidth: 2,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    tension: 0.35,
                    fill: false,
                    segment: {
                        borderDash: (context) =>
                            context.p0DataIndex >= dataset.historyCount - 1 ? [6, 4] : undefined,
                    },
                })),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', align: 'end' },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${context.parsed.y} pcs`,
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        grid,
                        border: { display: false },
                        ticks: { precision: 0 },
                    },
                },
            },
        });
    }
}
