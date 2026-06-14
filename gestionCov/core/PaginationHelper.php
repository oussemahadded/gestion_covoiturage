<?php

class PaginationHelper {
    public $totalRows;
    public $limit;
    public $currentPage;
    public $totalPages;
    public $offset;
    private $baseUrl;
    private $queryParams;
    private $pageParam;
    private $limitParam;

    public function __construct($totalRows, $defaultLimit = 10, $pageParam = 'page', $limitParam = 'limit') {
        $this->totalRows = $totalRows;
        $this->pageParam = $pageParam;
        $this->limitParam = $limitParam;
        
        // Parse current query params
        $this->queryParams = $_GET;
        
        // Determine limit
        $this->limit = isset($this->queryParams[$this->limitParam]) ? (int)$this->queryParams[$this->limitParam] : $defaultLimit;
        if (!in_array($this->limit, [10, 15, 25, 50, 100])) {
            $this->limit = $defaultLimit;
        }

        // Determine current page
        $this->currentPage = isset($this->queryParams[$this->pageParam]) ? (int)$this->queryParams[$this->pageParam] : 1;
        if ($this->currentPage < 1) $this->currentPage = 1;

        $this->totalPages = ceil($this->totalRows / $this->limit);
        if ($this->totalPages == 0) $this->totalPages = 1;

        if ($this->currentPage > $this->totalPages) {
            $this->currentPage = $this->totalPages;
        }

        $this->offset = ($this->currentPage - 1) * $this->limit;

        // Base URL
        $this->baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
    }

    public function getUrl($params) {
        $merged = array_merge($this->queryParams, $params);
        return $this->baseUrl . '?' . http_build_query($merged);
    }

    public function getSmartPages() {
        $pages = [];
        if ($this->totalPages <= 7) {
            for ($i = 1; $i <= $this->totalPages; $i++) $pages[] = $i;
        } else {
            $pages[] = 1;
            if ($this->currentPage > 3) $pages[] = '...';
            
            $start = max(2, $this->currentPage - 1);
            $end = min($this->totalPages - 1, $this->currentPage + 1);
            
            if ($this->currentPage == 1) $end = 3;
            if ($this->currentPage == $this->totalPages) $start = $this->totalPages - 2;

            for ($i = $start; $i <= $end; $i++) $pages[] = $i;
            
            if ($this->currentPage < $this->totalPages - 2) $pages[] = '...';
            $pages[] = $this->totalPages;
        }
        return $pages;
    }

    public function render() {
        $startItem = $this->totalRows > 0 ? $this->offset + 1 : 0;
        $endItem = min($this->offset + $this->limit, $this->totalRows);
        
        $html = '<div class="pagination-bar">';
        
        // Left Side
        $html .= '<div class="pagination-left">';
        
        // Limit Selector
        $html .= '<div class="limit-selector">';
        $html .= '<form method="GET" action="'.$this->baseUrl.'" class="limit-form">';
        // Preserve other GET params
        foreach ($this->queryParams as $k => $v) {
            if ($k !== $this->limitParam && $k !== $this->pageParam) {
                $html .= '<input type="hidden" name="'.htmlspecialchars($k).'" value="'.htmlspecialchars($v).'">';
            }
        }
        $html .= '<select name="'.$this->limitParam.'" onchange="this.form.submit()">';
        foreach ([10, 15, 25, 50, 100] as $opt) {
            $selected = $this->limit == $opt ? 'selected' : '';
            $html .= '<option value="'.$opt.'" '.$selected.'>'.$opt.'</option>';
        }
        $html .= '</select>';
        $html .= '</form>';
        $html .= '</div>';
        
        // Entry Count
        $html .= '<span class="entry-count">Affichage de '.$startItem.'–'.$endItem.' sur '.$this->totalRows.' entrées</span>';
        
        $html .= '</div>'; // End left side
        
        // Right Side
        $html .= '<div class="pagination-right">';
        
        // Mobile text: Page X sur Y
        $html .= '<span class="mobile-page-count">Page '.$this->currentPage.' sur '.$this->totalPages.'</span>';

        // Prev Button
        $prevDisabled = $this->currentPage <= 1 ? 'disabled' : '';
        $prevUrl = $this->currentPage > 1 ? $this->getUrl([$this->pageParam => $this->currentPage - 1]) : '#';
        $html .= '<a href="'.htmlspecialchars($prevUrl).'" class="btn-page btn-prev '.$prevDisabled.'">Précédent</a>';
        
        // Page Numbers (Desktop)
        $html .= '<div class="page-numbers">';
        foreach ($this->getSmartPages() as $p) {
            if ($p === '...') {
                $html .= '<span class="page-ellipsis">…</span>';
            } else {
                $active = $p == $this->currentPage ? 'active' : '';
                $url = $this->getUrl([$this->pageParam => $p]);
                $html .= '<a href="'.htmlspecialchars($url).'" class="btn-page '.$active.'">'.$p.'</a>';
            }
        }
        $html .= '</div>';
        
        // Next Button
        $nextDisabled = $this->currentPage >= $this->totalPages ? 'disabled' : '';
        $nextUrl = $this->currentPage < $this->totalPages ? $this->getUrl([$this->pageParam => $this->currentPage + 1]) : '#';
        $html .= '<a href="'.htmlspecialchars($nextUrl).'" class="btn-page btn-next '.$nextDisabled.'">Suivant</a>';
        
        $html .= '</div>'; // End right side
        
        $html .= '</div>'; // End pagination-bar
        
        return $html;
    }
}
