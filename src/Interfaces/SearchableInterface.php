<?php

declare(strict_types=1);

namespace App\Interfaces;

/**
 * Interface defining search result presentation contract.
 */
interface SearchableInterface
{
    /**
     * Get the primary display title for search results.
     */
    public function getSearchTitle(): string;

    /**
     * Get the descriptive subtitle or metadata for search results.
     */
    public function getSearchSubtitle(): string;

    /**
     * Get the URL link to the resource.
     */
    public function getSearchUrl(): string;
}
