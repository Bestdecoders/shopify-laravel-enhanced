# AI Chat Command

The AI Chat command allows you to interact with an AI assistant through Telegram.

## Usage

```
/ai [your question]
```

**Parameters:**
- `your question`: The question or message you want to ask the AI

**Examples:**
- `/ai What is the weather today?`
- `/ai How do I set up my Shopify store?`
- `/ai Explain how discounts work in Shopify`

## Features

- Conversational AI powered by OpenAI or Anthropic
- Context-aware responses based on previous interactions
- Support for various topics and queries
- Integration with the BrainService for intelligent responses

## Configuration

The AI service requires proper configuration in your `.env` file:
- `OPENAI_API_KEY` or `ANTHROPIC_API_KEY`
- `BRAIN_DEFAULT_PROVIDER` (openai or anthropic)
- `BRAIN_DEFAULT_MODEL` (e.g., gpt-4o-mini)

## Notes

- Responses are cached per user for contextual conversations
- The AI service respects privacy and doesn't store personal information
- Response length is limited to 500 tokens for optimal performance