<?php
/**
 * Shared Summary Cards Partial
 * Expects $summary_cards array: [['label' => '...', 'value' => '...', 'icon' => '...', 'color' => '...'], ...]
 */
?>
<div class="row g-3 mb-4 card-stats-row">
    <?php foreach ($summary_cards as $card): ?>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 summary-card-item">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div
                            class="stats-icon bg-<?= $card['color'] ?? 'primary' ?>-light text-<?= $card['color'] ?? 'primary' ?> rounded-3 p-3 me-3">
                            <i class="bi <?= $card['icon'] ?> fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 fw-normal">
                                <?= $card['label'] ?>
                            </h6>
                            <h4 class="mb-0 fw-bold card-stat-value">
                                <?= $card['value'] ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .card-stats-row .summary-card-item {
        transition: transform 0.2s ease, shadow 0.2s ease;
        border-radius: 12px;
    }

    .card-stats-row .summary-card-item:hover {
        transform: translateY(-5px);
    }

    .bg-primary-light {
        background-color: rgba(13, 110, 253, 0.1);
    }

    .bg-success-light {
        background-color: rgba(25, 135, 84, 0.1);
    }

    .bg-info-light {
        background-color: rgba(13, 202, 240, 0.1);
    }

    .bg-warning-light {
        background-color: rgba(255, 193, 7, 0.1);
    }

    .bg-danger-light {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .bg-dark-light {
        background-color: rgba(33, 37, 41, 0.1);
    }

    .stats-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
    }

    .card-stat-value {
        letter-spacing: -0.5px;
    }
</style>