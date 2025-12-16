import fs from 'fs';
import path from 'path';
import readline from 'readline';

const rl = readline.createInterface({
  input: process.stdin,
  output: process.stdout,
});

const envPath = path.resolve(process.cwd(), '.env');

const loadExistingEnv = () => {
  if (!fs.existsSync(envPath)) {
    return {};
  }

  return fs
    .readFileSync(envPath, 'utf-8')
    .split(/\r?\n/)
    .reduce((acc, line) => {
      const match = line.match(/^\s*([A-Z0-9_]+)\s*=\s*(.*)\s*$/);
      if (match) {
        acc[match[1]] = match[2];
      }
      return acc;
    }, {});
};

const ask = (question, defaultValue) =>
  new Promise((resolve) => {
    const suffix = defaultValue ? ` (${defaultValue})` : '';
    rl.question(`${question}${suffix}: `, (answer) => {
      resolve(answer.trim() || defaultValue || '');
    });
  });

const ensureValue = (value, name) => {
  if (!value) {
    console.error(`\n${name} is required. Run the setup again with a valid value.`);
    process.exit(1);
  }
  return value;
};

const persistEnv = (values) => {
  const header = [
    '# lltool Supabase configuration',
    '# Only Supabase is supported for this project.',
    '# Service role key is only needed for server-side scripts.',
    '',
  ].join('\n');

  const envLines = [
    `VITE_SUPABASE_URL=${values.VITE_SUPABASE_URL}`,
    `VITE_SUPABASE_ANON_KEY=${values.VITE_SUPABASE_ANON_KEY}`,
    values.VITE_SUPABASE_SERVICE_ROLE_KEY
      ? `VITE_SUPABASE_SERVICE_ROLE_KEY=${values.VITE_SUPABASE_SERVICE_ROLE_KEY}`
      : '# VITE_SUPABASE_SERVICE_ROLE_KEY=',
  ];

  fs.writeFileSync(envPath, `${header}${envLines.join('\n')}\n`, 'utf-8');
  console.log(`\nUpdated ${envPath} with Supabase credentials.`);
};

const main = async () => {
  console.log('lltool Supabase setup\n');
  console.log('Only Supabase is supported. You can manage migrations under the supabase/ directory.\n');

  const existingEnv = loadExistingEnv();

  const VITE_SUPABASE_URL = ensureValue(
    await ask('Supabase project URL', existingEnv.VITE_SUPABASE_URL),
    'Supabase project URL'
  );

  const VITE_SUPABASE_ANON_KEY = ensureValue(
    await ask('Supabase anon/public API key', existingEnv.VITE_SUPABASE_ANON_KEY),
    'Supabase anon key'
  );

  const VITE_SUPABASE_SERVICE_ROLE_KEY = await ask(
    'Supabase service role key (optional, never expose in frontend builds)',
    existingEnv.VITE_SUPABASE_SERVICE_ROLE_KEY
  );

  persistEnv({
    VITE_SUPABASE_URL,
    VITE_SUPABASE_ANON_KEY,
    VITE_SUPABASE_SERVICE_ROLE_KEY,
  });

  rl.close();
};

main().catch((err) => {
  console.error('\nFailed to run Supabase setup CLI:', err);
  rl.close();
  process.exit(1);
});

