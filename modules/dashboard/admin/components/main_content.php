<?php
// Calculate completion rate
$completionRate = ($appointmentStats['total_appointments'] > 0)
    ? round(($appointmentStats['completed'] / $appointmentStats['total_appointments']) * 100, 1)
    : 0;

// Prepare arrays for chart data
$dates = array();
$visits = array();
while ($row = mysqli_fetch_assoc($visitTrend)) {
    $dates[] = date('d/m', strtotime($row['date']));
    $visits[] = (int)$row['total_visits'];
}
?>

<div id="main-content">
    <div class="page-heading">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Selamat Datang Kembali</h3>
                <p class="text-subtitle text-muted">Statistik dan aktivitas klinik</p>
            </div>
        </div>
    </div>

    <div class="page-content">
        <section class="row">
            <?php include 'sections/statistics_cards.php'; ?>
            
            <div class="col-12 col-lg-8">
                <?php 
                include 'sections/visit_trend.php';
                include 'sections/today_schedule.php';
                include 'sections/weekly_stats.php';
                ?>
            </div>

            <div class="col-12 col-lg-4">
                <?php
                include 'sections/active_psychologists.php';
                include 'sections/service_statistics.php';
                include 'sections/recent_activities.php';
                ?>
            </div>
        </section>
    </div>
</div>