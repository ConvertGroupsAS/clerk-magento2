<?php

namespace Clerk\Clerk\Model;

class Config
{
    /**
     * General configuration
     */
    const XML_PATH_PRIVATE_KEY = 'clerk/general/private_key';
    const XML_PATH_PUBLIC_KEY = 'clerk/general/public_key';

    /**
     * Product Synchronization configuration
     */
    const XML_PATH_PRODUCT_SYNCHRONIZATION_REAL_TIME_ENABLED = 'clerk/product_synchronization/use_realtime_updates';
    const XML_PATH_PRODUCT_SYNCHRONIZATION_FIELDS = 'clerk/product_synchronization/fields';
    const XML_PATH_PRODUCT_SYNCHRONIZATION_SALABLE_ONLY = 'clerk/product_synchronization/saleable_only';
    const XML_PATH_PRODUCT_SYNCHRONIZATION_VISIBILITY = 'clerk/product_synchronization/visibility';

    /**
     * Search configuration
     */
    const XML_PATH_SEARCH_ENABLED = 'clerk/search/enabled';
    const XML_PATH_SEARCH_TEMPLATE = 'clerk/search/template';

    /**
     * Live search configuration
     */
    const XML_PATH_LIVESEARCH_ENABLED = 'clerk/livesearch/enabled';
    const XML_PATH_LIVESEARCH_TEMPLATE = 'clerk/livesearch/template';

    /**
     * Powerstep configuration
     */
    const XML_PATH_POWERSTEP_ENABLED = 'clerk/powerstep/enabled';
    const XML_PATH_POWERSTEP_TEMPLATES = 'clerk/powerstep/templates';
}