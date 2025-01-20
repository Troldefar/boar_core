<?php

namespace app\core\src\traits\controller;

use \app\core\src\exceptions\NotFoundException;
use \app\core\src\File;
use \app\core\src\http\View;

trait ControllerAssetTrait {

    private const VALID_ASSET_TYPES = ['js', 'css', 'meta'];
    private const VALID_ASSET_LOCATIONS = ['header', 'footer'];
    private const FORBIDDEN_ASSET = 'Forbidden asset type was provided';

    protected function getClientAssets() {
        return $this->clientAssets;
    }

    public function checkAssetLocation(string $type): void {
        if (in_array($type, $this::VALID_ASSET_LOCATIONS)) return;

        throw new \app\core\src\exceptions\ForbiddenException($this::FORBIDDEN_ASSET);
    }

    public function checkAssetType(string $type): void {
        if (in_array($type, $this::VALID_ASSET_TYPES)) return;

        throw new \app\core\src\exceptions\ForbiddenException($this::FORBIDDEN_ASSET);
    }    

    public function addScript(string|array $src) {
        if (is_string($src)) $src = (array)$src;

        $parent = app()->getParentController();

        array_map(function($file) use($parent) {
            return $parent->upsertData(File::JS_EXTENSION, File::buildScript($file));
        }, $src);
    }

    public function addStylesheet(string $src) {
        if (is_string($src)) $src = (array)$src;

        $parent = app()->getParentController();

        array_map(function($file) use($parent) {
            return $parent->upsertData(File::CSS_EXTENSION, File::buildStylesheet($file));
        }, $src);
    }

    public function getView(): string {
        return $this->view ?? View::INVALID_VIEW;
    }

    public function setAsPartialViewFile() {
        if (!file_exists($this->getView()))
            throw new NotFoundException('File not found', 404);

        extract($this->getData(), EXTR_SKIP);
        ob_start();
            require_once $this->getView();
        $this->data['partialView'] = ob_get_clean();
    }

    public function createPartialWithData(string $method, array $data = []) {
        return [$method => $data];
    }

    public function setView(string $view, string $dir = ''): void {
        $this->view = app()->getView()->getTemplatePath($view, $dir);
    }

    public function setLayout(string $layout): void {
        $this->layout = $layout;
    }

    public function getLayout(): string {
        return $this->layout;
    }

    public function setClientLayoutStructure(string $layout, string $view, array $data = []) {
        $this->setLayout($layout);
        $this->setFrontendTemplateAndData($view, [...$data]);
    }

    public function setFrontendTemplateAndData(string $templateFile, array $data = []) {
        $this->setData($data);
        $this->setView($templateFile);
    }

}