<?php
/**
 * Simple Pagination Helper (Phase 7)
 */
function render_pagination($current_page, $total_pages, $base_url) {
    if ($total_pages <= 1) return;

    echo '<div style="margin-top:1rem; display:flex; gap:4px; justify-content:center;">';

    for ($i = 1; $i <= $total_pages; $i++) {
        $active = $i == $current_page ? 'background:#1565C0;color:white;' : 'background:#f1f5f9;';
        echo '<a href="' . $base_url . '&page=' . $i . '" style="padding:6px 12px; border-radius:4px; text-decoration:none; ' . $active . '">' . $i . '</a>';
    }

    echo '</div>';
}
