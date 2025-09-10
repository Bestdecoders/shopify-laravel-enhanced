<?php

/**
 * GraphQL Queries for Shopify Enhanced Package
 * 
 * This file contains all GraphQL queries and mutations used by the package.
 * These can be overridden in the main application's config if needed.
 */

return [
    
    /*
    |--------------------------------------------------------------------------
    | Shop Information Queries
    |--------------------------------------------------------------------------
    |
    | Queries to fetch shop information and basic data
    |
    */
    'shop' => [
        'info' => <<<GRAPHQL
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
        
        'details' => <<<GRAPHQL
            query {
                shop {
                    id
                    name
                    url
                    myshopifyDomain
                    currencyCode
                    primaryDomain {
                        url
                        host
                    }
                    contactEmail
                    email
                    phone
                    address {
                        address1
                        address2
                        city
                        province
                        country
                        zip
                    }
                    plan {
                        displayName
                        partnerDevelopment
                        shopifyPlus
                    }
                }
            }
        GRAPHQL,
    ],

    /*
    |--------------------------------------------------------------------------
    | Billing & Subscription Queries
    |--------------------------------------------------------------------------
    |
    | All billing-related GraphQL queries and mutations
    |
    */
    'billing' => [
        'create_recurring_charge' => <<<GRAPHQL
            mutation appSubscriptionCreate(\$name: String!, \$lineItems: [AppSubscriptionLineItemInput!]!, \$returnUrl: URL!, \$test: Boolean, \$trialDays: Int) {
                appSubscriptionCreate(
                    name: \$name
                    lineItems: \$lineItems
                    returnUrl: \$returnUrl
                    test: \$test
                    trialDays: \$trialDays
                ) {
                    userErrors {
                        field
                        message
                    }
                    confirmationUrl
                    appSubscription {
                        id
                        name
                        status
                        lineItems {
                            id
                            plan {
                                pricingDetails {
                                    ... on AppRecurringPricing {
                                        __typename
                                        price {
                                            amount
                                            currencyCode
                                        }
                                        interval
                                    }
                                }
                            }
                        }
                        test
                        trialDays
                        createdAt
                        updatedAt
                    }
                }
            }
        GRAPHQL,
        
        'create_usage_charge' => <<<GRAPHQL
            mutation appUsageRecordCreate(\$subscriptionLineItemId: ID!, \$price: MoneyInput!, \$description: String!) {
                appUsageRecordCreate(
                    subscriptionLineItemId: \$subscriptionLineItemId
                    price: \$price
                    description: \$description
                ) {
                    userErrors {
                        field
                        message
                    }
                    appUsageRecord {
                        id
                        description
                        price {
                            amount
                            currencyCode
                        }
                        createdAt
                    }
                }
            }
        GRAPHQL,
        
        'cancel_subscription' => <<<GRAPHQL
            mutation appSubscriptionCancel(\$id: ID!) {
                appSubscriptionCancel(id: \$id) {
                    userErrors {
                        field
                        message
                    }
                    appSubscription {
                        id
                        status
                        name
                        test
                        createdAt
                        updatedAt
                    }
                }
            }
        GRAPHQL,
        
        'get_app_subscriptions' => <<<GRAPHQL
            query {
                currentAppInstallation {
                    activeSubscriptions {
                        id
                        name
                        status
                        test
                        trialDays
                        currentPeriodEnd
                        createdAt
                        updatedAt
                        lineItems {
                            id
                            plan {
                                pricingDetails {
                                    ... on AppRecurringPricing {
                                        __typename
                                        price {
                                            amount
                                            currencyCode
                                        }
                                        interval
                                    }
                                    ... on AppUsagePricing {
                                        __typename
                                        balanceUsed {
                                            amount
                                            currencyCode
                                        }
                                        cappedAmount {
                                            amount
                                            currencyCode
                                        }
                                        terms
                                    }
                                }
                            }
                        }
                    }
                    allSubscriptions(first: 10) {
                        edges {
                            node {
                                id
                                name
                                status
                                test
                                createdAt
                            }
                        }
                    }
                }
            }
        GRAPHQL,
        
        'get_subscription_by_id' => <<<GRAPHQL
            query getSubscription(\$id: ID!) {
                node(id: \$id) {
                    ... on AppSubscription {
                        id
                        name
                        status
                        test
                        trialDays
                        currentPeriodEnd
                        createdAt
                        updatedAt
                        lineItems {
                            id
                            usageRecords(first: 10) {
                                edges {
                                    node {
                                        id
                                        description
                                        price {
                                            amount
                                            currencyCode
                                        }
                                        createdAt
                                    }
                                }
                            }
                            plan {
                                pricingDetails {
                                    ... on AppRecurringPricing {
                                        __typename
                                        price {
                                            amount
                                            currencyCode
                                        }
                                        interval
                                    }
                                    ... on AppUsagePricing {
                                        __typename
                                        balanceUsed {
                                            amount
                                            currencyCode
                                        }
                                        cappedAmount {
                                            amount
                                            currencyCode
                                        }
                                        terms
                                    }
                                }
                            }
                        }
                    }
                }
            }
        GRAPHQL,
        
        'get_app_installation' => <<<GRAPHQL
            query {
                currentAppInstallation {
                    id
                    app {
                        id
                        handle
                    }
                    launchUrl
                    uninstallUrl
                    activeSubscriptions {
                        id
                        name
                        status
                    }
                }
            }
        GRAPHQL,
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Queries
    |--------------------------------------------------------------------------
    |
    | Product-related GraphQL queries
    |
    */
    'product' => [
        'search' => <<<GRAPHQL
            query searchProducts(\$query: String, \$first: Int!, \$after: String) {
                products(first: \$first, after: \$after, query: \$query) {
                    pageInfo {
                        hasNextPage
                        hasPreviousPage
                        startCursor
                        endCursor
                    }
                    edges {
                        cursor
                        node {
                            id
                            title
                            handle
                            vendor
                            productType
                            status
                            createdAt
                            updatedAt
                            featuredImage {
                                id
                                url
                                altText
                                width
                                height
                            }
                            variants(first: 10) {
                                edges {
                                    node {
                                        id
                                        title
                                        price
                                        compareAtPrice
                                        availableForSale
                                        inventoryQuantity
                                        sku
                                        barcode
                                        weight
                                        weightUnit
                                    }
                                }
                            }
                            tags
                            seo {
                                title
                                description
                            }
                        }
                    }
                }
            }
        GRAPHQL,
        
        'by_id' => <<<GRAPHQL
            query getProduct(\$id: ID!) {
                product(id: \$id) {
                    id
                    title
                    handle
                    description
                    descriptionHtml
                    vendor
                    productType
                    status
                    createdAt
                    updatedAt
                    featuredImage {
                        id
                        url
                        altText
                        width
                        height
                    }
                    images(first: 10) {
                        edges {
                            node {
                                id
                                url
                                altText
                                width
                                height
                            }
                        }
                    }
                    variants(first: 50) {
                        edges {
                            node {
                                id
                                title
                                price
                                compareAtPrice
                                availableForSale
                                inventoryQuantity
                                sku
                                barcode
                                weight
                                weightUnit
                                selectedOptions {
                                    name
                                    value
                                }
                            }
                        }
                    }
                    options {
                        id
                        name
                        values
                    }
                    tags
                    seo {
                        title
                        description
                    }
                }
            }
        GRAPHQL,
        
        'create' => <<<GRAPHQL
            mutation productCreate(\$input: ProductInput!) {
                productCreate(input: \$input) {
                    product {
                        id
                        title
                        handle
                        status
                        createdAt
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL,
        
        'update' => <<<GRAPHQL
            mutation productUpdate(\$input: ProductInput!) {
                productUpdate(input: \$input) {
                    product {
                        id
                        title
                        handle
                        status
                        updatedAt
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL,
        
        'delete' => <<<GRAPHQL
            mutation productDelete(\$input: ProductDeleteInput!) {
                productDelete(input: \$input) {
                    deletedProductId
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL,
    ],

    /*
    |--------------------------------------------------------------------------
    | Customer Queries
    |--------------------------------------------------------------------------
    |
    | Customer-related GraphQL queries
    |
    */
    'customer' => [
        'search' => <<<GRAPHQL
            query searchCustomers(\$query: String, \$first: Int!, \$after: String) {
                customers(first: \$first, after: \$after, query: \$query) {
                    pageInfo {
                        hasNextPage
                        hasPreviousPage
                        startCursor
                        endCursor
                    }
                    edges {
                        cursor
                        node {
                            id
                            firstName
                            lastName
                            displayName
                            email
                            phone
                            createdAt
                            updatedAt
                            acceptsMarketing
                            state
                            tags
                            addresses {
                                id
                                firstName
                                lastName
                                company
                                address1
                                address2
                                city
                                province
                                country
                                zip
                                phone
                            }
                            orders(first: 5) {
                                edges {
                                    node {
                                        id
                                        name
                                        createdAt
                                        totalPrice
                                    }
                                }
                            }
                        }
                    }
                }
            }
        GRAPHQL,
        
        'by_id' => <<<GRAPHQL
            query getCustomer(\$id: ID!) {
                customer(id: \$id) {
                    id
                    firstName
                    lastName
                    displayName
                    email
                    phone
                    createdAt
                    updatedAt
                    acceptsMarketing
                    state
                    tags
                    note
                    addresses {
                        id
                        firstName
                        lastName
                        company
                        address1
                        address2
                        city
                        province
                        country
                        zip
                        phone
                        default
                    }
                    orders(first: 10) {
                        edges {
                            node {
                                id
                                name
                                createdAt
                                totalPrice
                                financialStatus
                                fulfillmentStatus
                            }
                        }
                    }
                }
            }
        GRAPHQL,
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Queries
    |--------------------------------------------------------------------------
    |
    | Order-related GraphQL queries
    |
    */
    'order' => [
        'search' => <<<GRAPHQL
            query searchOrders(\$query: String, \$first: Int!, \$after: String) {
                orders(first: \$first, after: \$after, query: \$query) {
                    pageInfo {
                        hasNextPage
                        hasPreviousPage
                        startCursor
                        endCursor
                    }
                    edges {
                        cursor
                        node {
                            id
                            name
                            createdAt
                            updatedAt
                            processedAt
                            totalPrice
                            subtotalPrice
                            totalTax
                            totalShippingPrice
                            currencyCode
                            financialStatus
                            fulfillmentStatus
                            customer {
                                id
                                displayName
                                email
                            }
                            lineItems(first: 10) {
                                edges {
                                    node {
                                        id
                                        title
                                        quantity
                                        originalUnitPrice
                                        variant {
                                            id
                                            title
                                            sku
                                        }
                                        product {
                                            id
                                            title
                                            handle
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        GRAPHQL,
        
        'by_id' => <<<GRAPHQL
            query getOrder(\$id: ID!) {
                order(id: \$id) {
                    id
                    name
                    createdAt
                    updatedAt
                    processedAt
                    totalPrice
                    subtotalPrice
                    totalTax
                    totalShippingPrice
                    currencyCode
                    financialStatus
                    fulfillmentStatus
                    note
                    email
                    phone
                    customer {
                        id
                        displayName
                        email
                        phone
                    }
                    shippingAddress {
                        firstName
                        lastName
                        company
                        address1
                        address2
                        city
                        province
                        country
                        zip
                        phone
                    }
                    billingAddress {
                        firstName
                        lastName
                        company
                        address1
                        address2
                        city
                        province
                        country
                        zip
                        phone
                    }
                    lineItems(first: 50) {
                        edges {
                            node {
                                id
                                title
                                quantity
                                originalUnitPrice
                                discountedUnitPrice
                                variant {
                                    id
                                    title
                                    sku
                                    barcode
                                }
                                product {
                                    id
                                    title
                                    handle
                                }
                            }
                        }
                    }
                    fulfillments {
                        id
                        status
                        createdAt
                        updatedAt
                        trackingCompany
                        trackingNumbers
                        trackingUrls
                    }
                }
            }
        GRAPHQL,
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Queries
    |--------------------------------------------------------------------------
    |
    | Webhook-related GraphQL queries
    |
    */
    'webhook' => [
        'list' => <<<GRAPHQL
            query {
                webhookSubscriptions(first: 50) {
                    edges {
                        node {
                            id
                            callbackUrl
                            topic
                            format
                            createdAt
                            updatedAt
                        }
                    }
                }
            }
        GRAPHQL,
        
        'create' => <<<GRAPHQL
            mutation webhookSubscriptionCreate(\$topic: WebhookSubscriptionTopic!, \$webhookSubscription: WebhookSubscriptionInput!) {
                webhookSubscriptionCreate(topic: \$topic, webhookSubscription: \$webhookSubscription) {
                    webhookSubscription {
                        id
                        callbackUrl
                        topic
                        format
                        createdAt
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL,
        
        'delete' => <<<GRAPHQL
            mutation webhookSubscriptionDelete(\$id: ID!) {
                webhookSubscriptionDelete(id: \$id) {
                    deletedWebhookSubscriptionId
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL,
    ],

    /*
    |--------------------------------------------------------------------------
    | Metafield Queries
    |--------------------------------------------------------------------------
    |
    | Metafield-related GraphQL queries
    |
    */
    'metafield' => [
        'create' => <<<GRAPHQL
            mutation metafieldsSet(\$metafields: [MetafieldsSetInput!]!) {
                metafieldsSet(metafields: \$metafields) {
                    metafields {
                        id
                        namespace
                        key
                        value
                        type
                        createdAt
                        updatedAt
                    }
                    userErrors {
                        field
                        message
                        code
                    }
                }
            }
        GRAPHQL,
        
        'delete' => <<<GRAPHQL
            mutation metafieldDelete(\$input: MetafieldDeleteInput!) {
                metafieldDelete(input: \$input) {
                    deletedId
                    userErrors {
                        field
                        message
                        code
                    }
                }
            }
        GRAPHQL,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Queries
    |--------------------------------------------------------------------------
    |
    | File and media upload GraphQL queries
    |
    */
    'files' => [
        'create_staged_upload' => <<<GRAPHQL
            mutation stagedUploadsCreate(\$input: [StagedUploadInput!]!) {
                stagedUploadsCreate(input: \$input) {
                    stagedTargets {
                        url
                        resourceUrl
                        parameters {
                            name
                            value
                        }
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL,
        
        'create_file' => <<<GRAPHQL
            mutation fileCreate(\$files: [FileCreateInput!]!) {
                fileCreate(files: \$files) {
                    files {
                        id
                        createdAt
                        updatedAt
                        ... on MediaImage {
                            image {
                                url
                                width
                                height
                            }
                        }
                    }
                    userErrors {
                        field
                        message
                        code
                    }
                }
            }
        GRAPHQL,
    ],
];