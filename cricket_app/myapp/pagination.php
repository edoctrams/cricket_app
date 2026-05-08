<?php
function renderPagination($currentPage, $totalPages, $params = []) {
    if ($totalPages <= 1) {
        return;
    }

    $currentPage = max(1, min($currentPage, $totalPages));

    echo "<nav class='pagination' aria-label='Pagination'>";

    if ($currentPage > 1) {
        $params['page'] = $currentPage - 1;
        echo "<a href='?" . http_build_query($params) . "'>Previous</a>";
    } else {
        echo "<span class='disabled'>Previous</span>";
    }

    echo "<span class='page-status'>Page " . $currentPage . " of " . $totalPages . "</span>";

    if ($currentPage < $totalPages) {
        $params['page'] = $currentPage + 1;
        echo "<a href='?" . http_build_query($params) . "'>Next</a>";
    } else {
        echo "<span class='disabled'>Next</span>";
    }

    echo "</nav>";
}
?>
