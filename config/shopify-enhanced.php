<?php

return [
    'admin_email' => env('ADMIN_EMAIL', 'admin@example.com'),
    'thanks_email_template' => 'emails.thanks',
    'user_model' => \App\Models\User::class, // Default User model
    'queries' => [
        'shop' => <<<GRAPHQL
            query {
                shop {
                    name
                    currencyCode
                    contactEmail
                    email
                    paymentSettings {
                        supportedDigitalWallets
                    }
                    plan {
                        displayName
                        partnerDevelopment
                        shopifyPlus
                    }
                }
            }
        GRAPHQL,
        'product' => [
            'search' => <<<GRAPHQL
                query (\$query: String, \$cursor: String, \$pageSize: Int!) {
                    products(first: \$pageSize, after: \$cursor, query: \$query) {
                        pageInfo {
                            hasNextPage
                            hasPreviousPage
                        }
                        edges {
                            node {
                                id
                                title
                                vendor
                                status
                                featuredImage {
                                    url
                                }
                                variants(first: 10) {
                                    edges {
                                        node {
                                            id
                                            title
                                            price
                                            availableForSale
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            GRAPHQL,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | After Authenticate Job
    |--------------------------------------------------------------------------
    |
    | The job that runs after a shop has been authenticated. This will fire
    | after every authentication attempt.
    |
    */
    'after_authenticate_job' => [
        'job' => Bestdecoders\ShopifyLaravelEnhanced\Jobs\AfterInstallJob::class,
        'inline' => true,
    ],
];
