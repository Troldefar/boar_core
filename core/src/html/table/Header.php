<?php

namespace app\core\src\html\table;

use \app\core\src\database\table\Table;

class Header {

    private \app\core\src\http\Request $request; 
    private const SORT_BY = '&sortBy=';
    private const ORDER_BY = '&orderBy=';
    private string $queryParameters;
    private string $orderBy;
    private string $sortBy;
    private string $page;


    public function __construct(private array $fields) {
        $this->request = app()->getRequest();
        $this->setup();
    }

    private function setup() {
        $this->queryParameters = $this->request->getQueryString();
        $this->orderBy = $this->request->getOrderBy() ?? '';
        $this->sortBy = $this->request->getSortOrder() ?? '';
        $this->page = $this->request->getPage() ?? '';
    }

    private function determineSortOrder(): string {
        return is_int(strpos($this->queryParameters, Table::SORT_DESC)) ? Table::SORT_ASC : Table::SORT_DESC;
    }
    
    private function getPage(): string {
        return ($this->page !== '' ? 'page='.$this->page : '');
    }

    private function alterQueryParameters(string $field): string {
        return $this->request->checkQueryStart() . $this->getPage() . $this->request->querySearchParamsAndValues() . self::SORT_BY . $field . self::ORDER_BY . $this->determineSortOrder();
    }

    public function create($includeHref = true): string {
        ob_start(); ?>
            <thead>
                <tr>
                    <?php foreach($this->fields as $key => $field): ?>
                        <th>
                            <a 
                                class="active-menu-item" <?= $this->sortBy === $field ? 'style="color:red;"' : ''; ?>
                                <?php if ($includeHref): ?> href="<?= empty($field) ? '#' : $this->alterQueryParameters($field); ?>" <?php endif; ?>
                            >
                                <?= is_int($key) ? '' : ths($key); ?>
                                <?= $this->sortBy === $field && $this->orderBy === 'ASC' ? '<i class="fa-solid fa-arrow-up"></i>' : ''; ?>
                                <?= $this->sortBy === $field && $this->orderBy === 'DESC' ? '<i class="fa-solid fa-arrow-down"></i>' : ''; ?>
                            </a>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
		<?php return ob_get_clean();
	}
    
}