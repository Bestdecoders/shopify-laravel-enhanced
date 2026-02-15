# Discount Command

The Discount command allows you to manage discount coupons for Shopify stores via Telegram.

## Usage

### Create a new discount coupon
```
/discount create [code] [type] [value] [description]
```

**Parameters:**
- `code`: The coupon code (e.g., SAVE10)
- `type`: The discount type (percentage, fixed, free_days)
- `value`: The discount value (e.g., 10 for 10% off)
- `description`: A description of the coupon

**Examples:**
- `/discount create SAVE10 percentage 10 "10% off coupon"`
- `/discount create FIXED50 fixed 50 "$50 off coupon"`
- `/discount create FREE7 free_days 7 "7 free days"`

### Apply a discount to a user
```
/discount apply [user_shop_domain] [coupon_code]
```

**Parameters:**
- `user_shop_domain`: The Shopify domain (e.g., mystore.myshopify.com)
- `coupon_code`: The coupon code to apply

**Example:**
- `/discount apply mystore.myshopify.com SAVE10`

### List all active discount coupons
```
/discount list
```

### Show information about a specific coupon
```
/discount info [code]
```

**Parameters:**
- `code`: The coupon code to get information for

**Example:**
- `/discount info SAVE10`

### Get help
```
/discount help
```

## Discount Types

- **percentage**: Reduces the price by a percentage (e.g., 10% off)
- **fixed**: Reduces the price by a fixed amount (e.g., $50 off)
- **free_days**: Grants free subscription time (e.g., 7 free days)