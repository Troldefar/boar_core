<?php

namespace app\core\src\html\table;

class Pagination {

    private const PAGINATION_START_PAGE = 0;
    private const PAGINATION_ADDITIONAL_PAGE_DIVIDER = 2;
    private const MISSING_TABLE_CONFIG_ERROR_MESSAGE = 'Frontend table configurations is missing!';

    private int $pageIndex;
    private int $maxAllowedFrontendPages;
    private int $totalPaginationPagesNeeded;
    private string $replacedQueryParamaters;
    private array $pages;
    private \app\core\Application $app;

    public function __construct(private int $sqlDataQueryLength) {
        $this->app = app();
        $this->setup();
    }

    private function setup(): void {
        $this->checkTableConfigurations();
        $this->definePageIndex();
        $this->alterQueryParameters();
        $this->getPages();
    }

    private function checkTableConfigurations() {
        $tableConfigurations = $this->app->getConfig()->get('frontend')->table;
        if (!$tableConfigurations) throw new \app\core\src\exceptions\NotFoundException(self::MISSING_TABLE_CONFIG_ERROR_MESSAGE);

        $this->maxAllowedFrontendPages = $tableConfigurations->maximumPageInterval;
    }

    private function definePageIndex(): void {
        $queryArguments = $this->app->getRequest()->getCompleteRequestBody()->body;
        $this->pageIndex = !isset($queryArguments->page) ? 0 : (int)$queryArguments->page ?? 0;
    }

    private function alterQueryParameters(): void {
        $queryParameters = $this->app->getRequest()->getQueryString();
        $this->replacedQueryParamaters = '&' . preg_replace('/page=\d+&?/', '', $queryParameters);
    }

    private function getPages(): void {
        // In case its n.(n > 0) we have to allocate an additional page
        $this->totalPaginationPagesNeeded = (int)($this->sqlDataQueryLength / $this->maxAllowedFrontendPages);
        $this->pages = $this->calculatePages();
    }

    private function calculatePages(): array {
        $needsManyPages = $this->totalPaginationPagesNeeded > $this->maxAllowedFrontendPages;
        $pageDivision = $this->maxAllowedFrontendPages / self::PAGINATION_ADDITIONAL_PAGE_DIVIDER;
        $negativIndex = $this->pageIndex - $pageDivision;
        $positiveIndex = $this->pageIndex + $pageDivision;

        $firstVisuelPage = $needsManyPages ? ($negativIndex < self::PAGINATION_START_PAGE ? self::PAGINATION_START_PAGE : $negativIndex) : self::PAGINATION_START_PAGE;
        $lastVisualPage  = $needsManyPages ? ($positiveIndex > $this->totalPaginationPagesNeeded ? $this->totalPaginationPagesNeeded : $positiveIndex) : $this->totalPaginationPagesNeeded;

        for ($page = $firstVisuelPage; $page <= $lastVisualPage; $page++) $pages[] = $page;

        return $pages;
    }

    public function create(): string {
        if (empty($this->totalPaginationPagesNeeded)) return '';
        ob_start(); ?>
            <div class="card-footer border-0 p-0 mt-2">
				<nav aria-label="pagination">
					<ul class="pagination mb-0 d-flex justify-content-start">
                        <?php if($this->pageIndex > 0): ?>
                            <li class="page-item d-flex-center"><a class="page-link" href="?page=<?= (0) . $this->replacedQueryParamaters; ?>"><i class="fa-solid fa-angles-left"></i></a></li>
                            <li class="page-item d-flex-center"><a class="page-link" href="?page=<?= ($this->pageIndex - 1) . $this->replacedQueryParamaters; ?>"><i class="fa-solid fa-chevron-left"></i></a></li>
                        <?php endif; ?>
                            <?php foreach($this->pages as $page): ?>
                                <li class="page-item">
                                    <a class="page-link" <?= $page === $this->pageIndex ? 'style="color:red;font-weight:800;text-decoration:underline;"' : ''; ?> href="?page=<?= $page . $this->replacedQueryParamaters; ?>">
                                        <?= $page; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
						<?php if($this->pageIndex !== $this->totalPaginationPagesNeeded): ?>
                            <li class="page-item d-flex-center"><a class="page-link" href="?page=<?= ($this->pageIndex + 1) . $this->replacedQueryParamaters; ?>"><i class="fa-solid fa-chevron-right"></i></a></li>
                            <li class="page-item d-flex-center"><a class="page-link" href="?page=<?= ($this->totalPaginationPagesNeeded - 1) . $this->replacedQueryParamaters; ?>"><i class="fa-solid fa-angles-right"></i></a></li>
                        <?php endif; ?>
					</ul>
				</nav>
			</div>
		<?php return ob_get_clean();
	}
    
}