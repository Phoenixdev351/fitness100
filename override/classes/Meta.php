<?php
/**
 * Override Meta class to fix canonical tags
 * Fixes: Multiple canonical tags, pagination canonical, home page canonical
 * IMPORTANT: This prevents PrestaShop core from outputting canonical via $page.canonical
 * 
 * CRITICAL: PrestaShop might call this method multiple times or from different places
 * We return empty string to prevent ANY canonical generation
 * 
 * This override works for ALL page types (home, category, product, blog)
 */

class Meta extends MetaCore
{
    /**
     * Get canonical URL - ensures single canonical tag per page
     * Returns empty string to prevent PrestaShop from outputting canonical automatically
     * We handle canonical output manually in head.tpl
     * 
     * This method is called by PrestaShop core to populate $page.canonical
     * By returning empty string, we prevent PrestaShop from outputting canonical
     * 
     * CRITICAL: This is called for ALL page types including product pages
     */
    public function getCanonicalUrl()
    {
        // Return empty string to prevent PrestaShop core from outputting canonical
        // We output canonical manually in head.tpl with full control
        // This works for ALL page types (home, category, product, blog)
        return '';
    }
    
    /**
     * Override to prevent any canonical URL generation
     * Some PrestaShop versions might use different methods
     * This method might be called by PrestaShop core for product pages
     * 
     * CRITICAL: Product pages might use this method instead of getCanonicalUrl()
     */
    public function getCanonical($canonical_url = false)
    {
        // Return empty to prevent canonical output
        // This is called for product pages specifically
        return '';
    }
    
    /**
     * Override setCanonicalUrl to prevent setting canonical
     * PrestaShop might set canonical via this method
     * 
     * CRITICAL: Some PrestaShop versions set canonical via this method
     */
    public function setCanonicalUrl($canonical_url)
    {
        // Don't set canonical - we handle it manually in head.tpl
        // Return $this to maintain chainability
        return $this;
    }
}
