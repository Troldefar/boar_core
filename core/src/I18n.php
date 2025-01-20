<?php

/**
|----------------------------------------------------------------------------
| Bootstrap Translations
|----------------------------------------------------------------------------
|
| @author RE_WEB
| @package app\core\src
|
*/

namespace app\core\src;

use \app\core\src\exceptions\NotFoundException;
use \app\models\LanguageModel;
use \app\models\TranslationModel;
use \app\core\src\miscellaneous\Hash;

final class I18n {

    protected int $languageID;

    public function __construct() {
        if (IS_CLI) return;

        $language = (new LanguageModel())->query()->select()->where(['code' => strtolower(app()->getSession()->get('language'))])->run('fetch');
        if (!$language->exists()) 
            throw new NotFoundException('Language was not found');

        $this->languageID = $language->key();
    }

    public function translate(string $toTranslate): string {
        if (!$toTranslate) return '';

        $translationExists = (new TranslationModel())->query()->select()->where(['LanguageID' => $this->languageID, 'Translation' => $toTranslate])->run('fetch');
        if ($translationExists->exists()) return $translationExists->get('TranslationHumanReadable');
        
        return $this->registerMissingTranslation($toTranslate);
    }

    public function registerMissingTranslation(string $missingTranslation) {
        (new TranslationModel())
            ->set([
                'Translation' => $missingTranslation, 
                'TranslationHumanReadable' => $missingTranslation, 
                'LanguageID' => $this->languageID, 
                'TranslationHash' => Hash::create()
            ])
            ->save();

        return $missingTranslation;
    }

    public function getCurrentLanguageObject() {
        return new LanguageModel($this->languageID);
    }
    
}