<?php

return [
    'admin_email' => env('ADMIN_EMAIL', 'admin1@example.com'),
    'thanks_email_template' => 'emails.thanks',
    'user_model' => \App\Models\User::class, // Default User model
    'auto_register_install_job' => true,
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

];
