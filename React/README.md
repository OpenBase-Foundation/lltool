# lltool

## Database support

This project is hard-wired to Supabase (Postgres + Supabase stack). No other database providers are supported.

## Supabase setup CLI

Run the interactive CLI to store your Supabase credentials in `.env`:

```
npm run setup
```

You will be prompted for:
- `VITE_SUPABASE_URL`
- `VITE_SUPABASE_ANON_KEY`
- `VITE_SUPABASE_SERVICE_ROLE_KEY` (optional, only for server-side scripts)

You can manage SQL migrations in the `supabase/` directory. Use the Supabase CLI to push any schema updates.
