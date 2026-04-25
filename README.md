# Jam Notebook

Jam Notebook is a simple, server-rendered Laravel app for capturing and organizing musical ideas.

## Development (Laravel Sail)

Start Sail:

```bash
./vendor/bin/sail up -d
```

Run migrations:

```bash
./vendor/bin/sail artisan migrate
```

Run tests:

```bash
./vendor/bin/sail artisan test
```

Run coverage:

```bash
./vendor/bin/sail artisan test --coverage-clover=coverage.xml
```

Stop Sail:

```bash
./vendor/bin/sail down
```
