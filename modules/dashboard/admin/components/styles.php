<?php
// Custom CSS for dashboard
?>
<style>
    .stats-card {
        transition: transform 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-5px);
    }

    .stats-icon {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stats-icon i {
        color: #fff;
        font-size: 1.8rem;
    }

    .stats-icon.purple {
        background: linear-gradient(45deg, #9694ff, #6e6cff);
    }

    .stats-icon.blue {
        background: linear-gradient(45deg, #57caeb, #3ac2e8);
    }

    .stats-icon.green {
        background: linear-gradient(45deg, #5ddab4, #3fd19e);
    }

    .stats-icon.red {
        background: linear-gradient(45deg, #ff7976, #ff5956);
    }

    .activity-item {
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
        padding: 1rem;
        margin-bottom: 0.5rem;
        border-radius: 0.5rem;
    }

    .activity-item.new {
        border-left-color: #3ac2e8;
        background-color: #f8f9fa;
    }

    .activity-item:hover {
        background-color: #f8f9fa;
    }

    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }

    .schedule-item {
        border-left: 4px solid #435ebe;
        background-color: #f8f9fa;
        margin-bottom: 0.75rem;
        padding: 1rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }

    .schedule-item:hover {
        transform: translateX(5px);
        background-color: #f1f1f1;
    }

    .weekly-stats {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .weekly-stats:hover {
        background: #f1f1f1;
    }

    .weekly-stats-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }

    .weekly-stats-icon i {
        color: #fff;
        font-size: 1.2rem;
    }

    .progress {
        height: 0.8rem;
        border-radius: 0.4rem;
    }

    .avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        overflow: hidden;
    }

    .avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>