# Grandfathered Access Command

The Grandfathered Access command allows you to manage grandfathered (free) access for Shopify stores via Telegram. This is useful for granting complimentary access to select customers.

## Usage

### Grant grandfathered access to a shop
```
/gf [shopname] [duration]
```

**Parameters:**
- `shopname`: The Shopify domain (e.g., mystore.myshopify.com)
- `duration`: The access duration (format: Nd for days, Nh for hours, Nm for minutes, or "permanent")

**Examples:**
- `/gf mystore.myshopify.com 7d` - Grant access for 7 days
- `/gf mystore.myshopify.com 12h` - Grant access for 12 hours
- `/gf mystore.myshopify.com 30m` - Grant access for 30 minutes
- `/gf mystore.myshopify.com permanent` - Grant permanent access

### List all grandfathered shops
```
/gf list
```

Shows all shops with grandfathered access, including their expiration dates and status.

### Revoke grandfathered access
```
/gf revoke [shopname]
```

**Parameters:**
- `shopname`: The Shopify domain to revoke access from

**Example:**
- `/gf revoke mystore.myshopify.com`

### Get help
```
/gf help
```

Shows detailed help information for the grandfathered access command.

## Duration Formats

- **Nd**: Days (e.g., 7d = 7 days)
- **Nh**: Hours (e.g., 12h = 12 hours)
- **Nm**: Minutes (e.g., 30m = 30 minutes)
- **permanent**: Never expires

## What Happens

### When Access is Granted
- The shop's `shopify_grandfathered` flag is set to `true`
- The `grandfather_access_valid_until` timestamp is set
- Admin receives a Telegram notification
- Client receives an email notification

### When Access is Revoked
- The shop's `shopify_grandfathered` flag is set to `false`
- The `grandfather_access_valid_until` is cleared
- Admin receives a Telegram notification
- Client receives an email notification

### Automatic Expiration
- A scheduled job runs daily at 2 AM to revoke expired access
- Expired shops automatically lose grandfathered status
- Both admin and client are notified via email

## Notes

- Grandfathered access bypasses normal subscription requirements
- Access can be revoked manually at any time using `/gf revoke`
- The list command shows expired access with a warning icon (⚠️)
- All actions are logged for audit purposes
