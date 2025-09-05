/**
 * Shopify App Extension JavaScript
 * This file contains JavaScript that will be injected into the Shopify admin or storefront
 */

(function() {
    'use strict';
    
    // Extension configuration - can be overridden by the app
    const ExtensionConfig = {
        debug: __DEVELOPMENT__,
        version: __EXTENSION_VERSION__,
        name: 'Shopify Enhanced Extension'
    };

    // Utility functions
    const Utils = {
        log: function(message, ...args) {
            if (ExtensionConfig.debug) {
                console.log(`[${ExtensionConfig.name}] ${message}`, ...args);
            }
        },
        
        error: function(message, ...args) {
            console.error(`[${ExtensionConfig.name}] ${message}`, ...args);
        },

        ready: function(callback) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', callback);
            } else {
                callback();
            }
        },

        // Safe way to access Shopify APIs
        getShopifyAPI: function() {
            return window.Shopify || {};
        },

        // Check if we're in Shopify admin
        isShopifyAdmin: function() {
            return window.location.hostname.includes('.myshopify.com') && 
                   window.location.pathname.includes('/admin');
        },

        // Check if we're in storefront
        isStorefront: function() {
            return !this.isShopifyAdmin() && typeof Shopify !== 'undefined';
        }
    };

    // Main extension class
    class ShopifyEnhancedExtension {
        constructor() {
            this.initialized = false;
            this.shopifyAPI = Utils.getShopifyAPI();
            
            Utils.log('Extension initializing...', {
                version: ExtensionConfig.version,
                location: window.location.href,
                isAdmin: Utils.isShopifyAdmin(),
                isStorefront: Utils.isStorefront()
            });
        }

        init() {
            if (this.initialized) {
                Utils.log('Extension already initialized');
                return;
            }

            try {
                // Initialize based on context
                if (Utils.isShopifyAdmin()) {
                    this.initAdmin();
                } else if (Utils.isStorefront()) {
                    this.initStorefront();
                } else {
                    Utils.log('Unknown Shopify context, skipping initialization');
                    return;
                }

                this.initialized = true;
                Utils.log('Extension initialized successfully');
                
                // Dispatch custom event for other scripts
                this.dispatchEvent('shopify-enhanced:ready', { extension: this });
                
            } catch (error) {
                Utils.error('Failed to initialize extension:', error);
            }
        }

        initAdmin() {
            Utils.log('Initializing admin extensions...');
            
            // Example: Add custom functionality to admin
            this.enhanceAdminInterface();
            
            // Listen for Shopify admin events if available
            this.setupAdminListeners();
        }

        initStorefront() {
            Utils.log('Initializing storefront extensions...');
            
            // Example: Add custom functionality to storefront
            this.enhanceStorefront();
            
            // Listen for Shopify storefront events
            this.setupStorefrontListeners();
        }

        enhanceAdminInterface() {
            // Add custom admin functionality here
            // This is where you'd add buttons, modify UI, etc.
            Utils.log('Admin interface enhanced');
        }

        enhanceStorefront() {
            // Add custom storefront functionality here
            // This could be product enhancements, checkout modifications, etc.
            Utils.log('Storefront enhanced');
        }

        setupAdminListeners() {
            // Listen for admin-specific events
            document.addEventListener('shopify:admin:ready', () => {
                Utils.log('Shopify admin is ready');
            });
        }

        setupStorefrontListeners() {
            // Listen for storefront-specific events
            if (this.shopifyAPI.theme) {
                document.addEventListener('shopify:section:load', () => {
                    Utils.log('Shopify section loaded');
                });
            }
        }

        // Helper method to dispatch custom events
        dispatchEvent(eventName, detail = {}) {
            const event = new CustomEvent(eventName, {
                detail,
                bubbles: true,
                cancelable: true
            });
            document.dispatchEvent(event);
        }

        // API methods that can be called by the Laravel app
        getConfig() {
            return ExtensionConfig;
        }

        updateConfig(newConfig) {
            Object.assign(ExtensionConfig, newConfig);
            Utils.log('Configuration updated:', ExtensionConfig);
        }
    }

    // Initialize when DOM is ready
    Utils.ready(() => {
        // Create global extension instance
        window.ShopifyEnhancedExtension = new ShopifyEnhancedExtension();
        window.ShopifyEnhancedExtension.init();
    });

    // Export for module systems
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = ShopifyEnhancedExtension;
    }
})();