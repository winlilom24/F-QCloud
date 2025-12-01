<?php
class Pagination {
    private $totalItems;
    private $itemsPerPage;
    private $currentPage;
    private $totalPages;

    public function __construct($totalItems, $itemsPerPage = 5, $currentPage = 1) {
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = max(1, (int)$currentPage);
        $this->totalPages = ceil($totalItems / $itemsPerPage);
    }

    public function getOffset() {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }

    public function getLimit() {
        return $this->itemsPerPage;
    }

    public function getCurrentPage() {
        return $this->currentPage;
    }

    public function getTotalPages() {
        return $this->totalPages;
    }

    public function hasPreviousPage() {
        return $this->currentPage > 1;
    }

    public function hasNextPage() {
        return $this->currentPage < $this->totalPages;
    }

    public function getPreviousPage() {
        return $this->currentPage - 1;
    }

    public function getNextPage() {
        return $this->currentPage + 1;
    }

    public function render($baseUrl = '') {
        if ($this->totalPages <= 1) {
            return '';
        }

        $html = '<div class="pagination">';

        // Previous button
        if ($this->hasPreviousPage()) {
            $html .= '<a href="' . $baseUrl . $this->getPreviousPage() . ')" class="pagination-btn pagination-prev">';
            $html .= '<i class="fa-solid fa-chevron-left"></i> Trước';
            $html .= '</a>';
        } else {
            $html .= '<span class="pagination-btn pagination-prev disabled">';
            $html .= '<i class="fa-solid fa-chevron-left"></i> Trước';
            $html .= '</span>';
        }

        // Page numbers
        $startPage = max(1, $this->currentPage - 2);
        $endPage = min($this->totalPages, $this->currentPage + 2);

        if ($startPage > 1) {
            $html .= '<a href="' . $baseUrl . '1)" class="pagination-btn">1</a>';
            if ($startPage > 2) {
                $html .= '<span class="pagination-dots">...</span>';
            }
        }

        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i == $this->currentPage) {
                $html .= '<span class="pagination-btn pagination-current">' . $i . '</span>';
            } else {
                $html .= '<a href="' . $baseUrl . $i . ')" class="pagination-btn">' . $i . '</a>';
            }
        }

        if ($endPage < $this->totalPages) {
            if ($endPage < $this->totalPages - 1) {
                $html .= '<span class="pagination-dots">...</span>';
            }
            $html .= '<a href="' . $baseUrl . $this->totalPages . ')" class="pagination-btn">' . $this->totalPages . '</a>';
        }

        // Next button
        if ($this->hasNextPage()) {
            $html .= '<a href="' . $baseUrl . $this->getNextPage() . ')" class="pagination-btn pagination-next">';
            $html .= 'Sau <i class="fa-solid fa-chevron-right"></i>';
            $html .= '</a>';
        } else {
            $html .= '<span class="pagination-btn pagination-next disabled">';
            $html .= 'Sau <i class="fa-solid fa-chevron-right"></i>';
            $html .= '</span>';
        }

        $html .= '</div>';

        return $html;
    }
}
?>
