<?php

namespace Bonnier\WP\Trapp\Admin\Polylang;

use Bonnier\WP\Trapp\Plugin;

class Events
{

    /**
     * Returned row from Trapp.
     *
     * @var object.
     */
    public $row;

    /**
     * WP_Post object of the saved post.
     *
     * @var object.
     */
    public $post;

    /**
     * Array of translations of the returned row.
     *
     * @var array.
     */
    public $rowTranslations = [];

    /**
     * Sets row and post to the object.
     *
     * @return void.
     */
    public function __construct($row, $post)
    {
        $this->row = $row;
        $this->post = $post;

        $this->setRowTranslations();
    }

    /**
     * Sets translations from row.
     *
     * @return void.
     */
    public function setRowTranslations() {
        foreach ($this->row->translations as $translation) {
            $id = $translation['id'];
            $locale = $translation['locale'];

            $this->rowTranslations[$locale] = $id;
        }
    }

    /**
     * Save languages from the returned row.
     *
     * @return void.
     */
    public function saveLanguages()
    {
        // TODO Find current pll translations and append the rowtranslations
        // If the Post does not already exist, create it
        // Create post possible save data from master hook and scheduled data via. hook.
        foreach ($this->row->translations as $translation) {
            $this->saveLanguage($translation);
        }

        $this->savePostLanguages();
    }

    public function saveLanguage($translation) {

    }

    public function savePostLanguages() {

    }

}
