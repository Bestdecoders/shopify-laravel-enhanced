# Help Command

The Help command provides information about all available Telegram commands.

## Usage

### List all commands
```
/help
```

Shows a list of all available commands with brief descriptions.

### Get help for a specific command
```
/help [command_name]
```

Shows detailed help information for the specified command.

**Parameters:**
- `command_name`: The name of the command to get help for

**Examples:**
- `/help discount`
- `/help ai`

### Alternative help syntax
```
/help [command_name] -h
```

Same as `/help [command_name]` - shows detailed help for the specified command.

**Examples:**
- `/help discount -h`
- `/help ai -h`

## Features

- Dynamically loads command list from configuration
- Reads command documentation from README files
- Falls back to docblock comments if no README exists
- Provides usage examples and parameter information
- Shows comprehensive help for each command

## Documentation Structure

Command documentation is stored in:
`storage/docs/telegram/commands/[command_name].md`

Each command can have its own detailed documentation file.