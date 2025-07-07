<?php
// JavaScript for dashboard functionality
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize period buttons
    const periodButtons = document.querySelectorAll('.btn-group .btn');
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            periodButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Prepare visit trend data
    const visitData = {
        labels: <?= json_encode($dates) ?>,
        datasets: [{
            label: 'Total Kunjungan',
            data: <?= json_encode($visits) ?>,
            backgroundColor: 'rgba(67, 94, 190, 0.2)',
            borderColor: '#435ebe',
            tension: 0.4,
            fill: true,
            pointStyle: 'circle',
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    };

    // Create visit trend chart
    const ctx = document.getElementById('visitChart').getContext('2d');
    const visitChart = new Chart(ctx, {
        type: 'line',
        data: visitData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 8,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
});
</script>