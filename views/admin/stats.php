<?php
require_once __DIR__ . '/../../middleware/Security.php';
Security::startSession();

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ../../index.php?action=login&error=' . urlencode('Bạn không có quyền truy cập trang quản trị.'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php?action=admin">
            <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
        </a>
        <div class="ms-auto d-flex align-items-center gap-2">
            <a href="index.php?action=admin" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Quay lại
            </a>
        </div>
    </div>
</nav>

<div class="container py-2 pb-5">
    <div class="mb-4">
        <h2 class="h3 fw-bold text-gray-800">Thống kê hệ thống</h2>
        <p class="text-muted">Doanh thu, trạng thái đơn hàng và sản phẩm bán chạy.</p>
    </div>

    <div id="statsAlert" class="alert alert-info" role="status">Đang tải dữ liệu thống kê…</div>

    <div class="row g-4 mb-4" id="statsSummary" hidden>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">Tổng số đơn hàng</div>
                    <div class="fs-3 fw-bold" id="summaryTotalOrders">0</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">Doanh thu (đơn đã hoàn thành)</div>
                    <div class="fs-3 fw-bold text-success" id="summaryTotalRevenue">0 đ</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4" id="statsCharts" hidden>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Doanh thu 7 ngày gần nhất</h6>
                    <canvas id="revenueChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Đơn hàng theo trạng thái</h6>
                    <canvas id="statusChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Top 5 sản phẩm bán chạy</h6>
                    <canvas id="topProductsChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    var alertBox = document.getElementById('statsAlert');
    var summaryBox = document.getElementById('statsSummary');
    var chartsBox = document.getElementById('statsCharts');

    function showError(message) {
        alertBox.hidden = false;
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = message;
    }

    function formatCurrency(value) {
        return Number(value).toLocaleString('vi-VN') + ' đ';
    }

    fetch('views/api/admin-stats.php', { headers: { 'Accept': 'application/json' } })
        .then(function (res) {
            if (!res.ok) { throw new Error('HTTP ' + res.status); }
            return res.json();
        })
        .then(function (payload) {
            if (!payload || payload.success !== true) {
                throw new Error(payload && payload.message ? payload.message : 'Dữ liệu không hợp lệ');
            }

            alertBox.hidden = true;
            summaryBox.hidden = false;
            chartsBox.hidden = false;

            document.getElementById('summaryTotalOrders').textContent = payload.summary.total_orders;
            document.getElementById('summaryTotalRevenue').textContent = formatCurrency(payload.summary.total_revenue);

            new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: payload.revenue_by_day.labels,
                    datasets: [{
                        label: 'Doanh thu (đ)',
                        data: payload.revenue_by_day.values,
                        borderColor: '#e11d2e',
                        backgroundColor: 'rgba(225,29,46,.15)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });

            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: payload.orders_by_status.labels,
                    datasets: [{
                        data: payload.orders_by_status.values,
                        backgroundColor: ['#f59e0b', '#3b82f6', '#8b5cf6', '#22c55e', '#ef4444']
                    }]
                },
                options: { responsive: true }
            });

            var topLabels = payload.top_products.labels;
            var topValues = payload.top_products.values;
            if (topLabels.length === 0) {
                var topCanvas = document.getElementById('topProductsChart');
                topCanvas.parentElement.insertAdjacentHTML(
                    'beforeend',
                    '<p class="text-muted mb-0">Chưa có đơn hàng hoàn thành nào để thống kê sản phẩm bán chạy.</p>'
                );
                topCanvas.remove();
            } else {
                new Chart(document.getElementById('topProductsChart'), {
                    type: 'bar',
                    data: {
                        labels: topLabels,
                        datasets: [{
                            label: 'Số lượng đã bán',
                            data: topValues,
                            backgroundColor: '#e11d2e'
                        }]
                    },
                    options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
                });
            }
        })
        .catch(function (error) {
            showError('Không thể tải dữ liệu thống kê: ' + error.message);
        });
})();
</script>
</body>
</html>
